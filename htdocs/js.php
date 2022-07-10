<?php

require __DIR__.'/../init.php';
global $config;

$name = $_REQUEST['name'] ?? '';

if (!$config['is_dev'] || !$name || !is_dir($path = ROOT.'/htdocs/js/'.$name)) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/javascript');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$files = scandir($path, SCANDIR_SORT_ASCENDING);
$first = true;
foreach ($files as $file) {
    if ($file == '.' || $file == '..')
        continue;
    // logDebug(__FILE__.': reading '.$path.'/'.$file);
    if (!$first)
        echo "\n";
    else
        $first = false;
    echo "/* $file */\n";
    if (readfile($path.'/'.$file) === false)
        logError(__FILE__.': failed to readfile('.$path.'/'.$file.')');
}
