#!/usr/bin/env php8.1
<?php

function gethash(string $path): string {
    return substr(sha1(file_get_contents($path)), 0, 8);
}

function sassc(string $src_dir, string $dst_dir, string $file): int {
    $cmd = 'sassc -t expanded '.escapeshellarg($src_dir.'/'.$file).' '.escapeshellarg($dst_dir.'/'.preg_replace('/\.scss$/', '.css', $file));
    exec($cmd, $output, $code);
    return $code;
}

require __DIR__.'/init.php';
global $config;

function build_static(): void {
    $css_dir = ROOT.'/htdocs/css';
    $hashes = [];

    if (!file_exists($css_dir))
        mkdir($css_dir);

    $files = ['common-bundle.scss', 'admin.scss'];
    foreach ($files as $file) {
        if (sassc(ROOT.'/htdocs/scss', $css_dir, $file) != 0)
            fwrite(STDERR, "error: could not compile $file\n");
    }

    foreach (['css', 'js'] as $type) {
        $reldir = ROOT.'/htdocs/';
        $files = glob_recursive($reldir.$type.'/*.'.$type);
        if (empty($files)) {
            continue;
        }
        foreach ($files as $file) {
            $name = preg_replace('/^'.preg_quote($reldir, '/').'/', '', $file);
            $hashes[$name] = gethash($file);
        }
    }

    $scfg = "<?php\n\n";
    $scfg .= "return ".var_export($hashes, true).";\n";

    file_put_contents(ROOT.'/config-static.php', $scfg);
}

build_static();