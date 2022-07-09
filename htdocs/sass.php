<?php

require __DIR__.'/../init.php';
global $config;

$name = $_REQUEST['name'] ?? '';
if (!$config['is_dev'] || !$name || !file_exists($path = ROOT.'/htdocs/scss/'.$name.'.scss')) {
    // logError(__FILE__.': access denied');
    http_response_code(403);
    exit;
}

// logInfo(__FILE__.': continuing, path='.$path);

$cmd = 'sassc -t expanded '.escapeshellarg($path);
$descriptorspec = [
   0 => ['pipe', 'r'], // stdin
   1 => ['pipe', 'w'], // stdout
   2 => ['pipe', 'w'], // stderr
];

$process = proc_open($cmd, $descriptorspec, $pipes, ROOT);
if (!is_resource($process)) {
    http_response_code(500);
    logError('could not open sassc process');
    exit;
}

$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);

$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);

$code = proc_close($process);
if ($code) {
    http_response_code(500);
    logError('sassc('.$path.') returned '.$code);
    logError($stderr);
    exit;
}

header('Content-Type: text/css');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

echo $stdout;
