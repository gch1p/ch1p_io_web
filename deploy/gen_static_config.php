#!/usr/bin/env php8.1
<?php

require __DIR__.'/../init.php';

if ($argc <= 1) {
    usage();
    exit(1);
}

$input_dir = null;

array_shift($argv);
while (count($argv) > 0) {
    switch ($argv[0]) {
        case '-i':
            array_shift($argv);
            $input_dir = array_shift($argv);
            break;

        default:
            cli::die('unsupported argument: '.$argv[0]);
    }
}

if (is_null($input_dir))
    cli::die("input directory has not been specified");

$hashes = [];
foreach (['css', 'js'] as $type) {
    $entries = glob_recursive($input_dir.'/dist-'.$type.'/*.'.$type);
    if (empty($entries)) {
        cli::error("warning: no files found in $input_dir/dist-$type");
        continue;
    }

    foreach ($entries as $file)
        $hashes[$type.'/'.basename($file)] = get_hash($file);
}

echo "<?php\n\n";
echo "return ".var_export($hashes, true).";\n";

function usage(): void {
    global $argv;
    echo <<<EOF
usage: {$argv[0]} [OPTIONS]

Options:
    -i  input htdocs directory

EOF;
}

function get_hash(string $path): string {
    return substr(sha1(file_get_contents($path)), 0, 8);
}