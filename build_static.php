#!/usr/bin/env php8.1
<?php

function gethash(string $path): string {
    return substr(sha1(file_get_contents($path)), 0, 8);
}

function sassc(string $src_file, string $dst_file): int {
    $cmd = 'sassc -t compressed '.escapeshellarg($src_file).' '.escapeshellarg($dst_file);
    exec($cmd, $output, $code);
    return $code;
}

function clean_css(string $file) {
    $output = $file.'.out';
    if (file_exists($output))
        unlink($output);

    $cmd = ROOT.'/node_modules/clean-css-cli/bin/cleancss -O2 "all:on;mergeSemantically:on;restructureRules:on" '.escapeshellarg($file).' > '.escapeshellarg($output);
    system($cmd);

    if (file_exists($output)) {
        unlink($file);
        rename($output, $file);
    } else {
        fwrite(STDERR, "error: could not cleancss $file\n");
    }
}

function dark_diff(string $light_file, string $dark_file): void {
    $temp_output = $dark_file.'.diff';
    $cmd = ROOT.'/dark-theme-diff.js '.escapeshellarg($light_file).' '.$dark_file.' > '.$temp_output;
    exec($cmd, $output, $code);
    if ($code != 0) {
        fwrite(STDERR, "dark_diff failed with code $code\n");
        return;
    }

    unlink($dark_file);
    rename($temp_output, $dark_file);
}

require __DIR__.'/init.php';

function build_static(): void {
    $css_dir = ROOT.'/htdocs/css';
    $hashes = [];

    if (!file_exists($css_dir))
        mkdir($css_dir);

    // 1. scss -> css
    $themes = ['light', 'dark'];
    $entries = ['common', 'admin'];
    foreach ($themes as $theme) {
        foreach ($entries as $entry) {
            $input = ROOT.'/htdocs/scss/entries/'.$entry.'/'.$theme.'.scss';
            $output = $css_dir.'/'.$entry.($theme == 'dark' ? '_dark' : '').'.css';
            if (sassc($input, $output) != 0) {
                fwrite(STDERR, "error: could not compile entries/$entry/$theme.scss\n");
                continue;
            }

            // 1.1. apply clean-css optimizations and transformations
            clean_css($output);
        }
    }

    // 2. generate dark theme diff
    foreach ($entries as $entry) {
        $light_file = $css_dir.'/'.$entry.'.css';
        $dark_file = str_replace('.css', '_dark.css', $light_file);
        dark_diff($light_file, $dark_file);
    }

    // 3. calculate hashes
    foreach (['css', 'js'] as $type) {
        $reldir = ROOT.'/htdocs/';
        $entries = glob_recursive($reldir.$type.'/*.'.$type);
        if (empty($entries)) {
            continue;
        }
        foreach ($entries as $file) {
            $name = preg_replace('/^'.preg_quote($reldir, '/').'/', '', $file);
            $hashes[$name] = gethash($file);
        }
    }

    // 4. write config-static.php
    $scfg = "<?php\n\n";
    $scfg .= "return ".var_export($hashes, true).";\n";

    file_put_contents(ROOT.'/config-static.php', $scfg);
}

build_static();