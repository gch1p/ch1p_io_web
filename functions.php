<?php

function htmlescape(string|array $s): string|array {
    if (is_array($s)) {
        foreach ($s as $k => $v) {
            $s[$k] = htmlescape($v);
        }
        return $s;
    }
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function strtrim(string $str, int $len, bool &$trimmed): string {
    if (mb_strlen($str) > $len) {
        $str = mb_substr($str, 0, $len);
        $trimmed = true;
    } else {
        $trimmed = false;
    }
    return $str;
}

function sizeString(int $size): string {
    $ks = array('B', 'KiB', 'MiB', 'GiB');
    foreach ($ks as $i => $k) {
        if ($size < pow(1024, $i + 1)) {
            if ($i == 0)
                return $size . ' ' . $k;
            return round($size / pow(1024, $i), 2).' '.$k;
        }
    }
    return $size;
}

function extension(string $name): string {
    $expl = explode('.', $name);
    return end($expl);
}

/**
 * @param string $filename
 * @return resource|bool
 */
function imageopen(string $filename) {
    $size = getimagesize($filename);
    $types = [
        1 => 'gif',
        2 => 'jpeg',
        3 => 'png'
    ];
    if (!$size || !isset($types[$size[2]]))
        return null;
    return call_user_func('imagecreatefrom'.$types[$size[2]], $filename);
}

function detect_image_type(string $filename) {
    $size = getimagesize($filename);
    $types = [
        1 => 'gif',
        2 => 'jpg',
        3 => 'png'
    ];
    if (!$size || !isset($types[$size[2]]))
        return false;
    return $types[$size[2]];
}

function transliterate(string $string): string {
    $roman = array(
        'Sch', 'sch', 'Yo', 'Zh', 'Kh', 'Ts', 'Ch', 'Sh', 'Yu', 'ya', 'yo',
        'zh', 'kh', 'ts', 'ch', 'sh', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E',
        'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
        '', 'Y', '', 'E', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'y', 'k',
        'l', 'm',  'n', 'o', 'p', 'r', 's', 't', 'u', 'f', '', 'y', '', 'e'
    );
    $cyrillic = array(
        'Щ', 'щ', 'Ё', 'Ж', 'Х', 'Ц', 'Ч', 'Ш', 'Ю', 'я', 'ё', 'ж', 'х', 'ц',
        'ч', 'ш', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К',
        'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ь', 'Ы', 'Ъ', 'Э',
        'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о',
        'п', 'р', 'с', 'т', 'у', 'ф', 'ь', 'ы', 'ъ', 'э'
    );
    return str_replace($cyrillic, $roman, $string);
}

/**
 * @param resource $img
 * @param ?int $w
 * @param ?int $h
 * @param ?int[] $transparent_color
 */
function imageresize(&$img, ?int $w = null, ?int $h = null, ?array $transparent_color = null) {
    assert(is_int($w) || is_int($h));

    $curw = imagesx($img);
    $curh = imagesy($img);

    if (!is_int($w) && is_int($h)) {
        $w = round($curw / ($curw / $w));
    } else if (is_int($w) && !is_int($h)) {
        $h = round($curh / ($curh / $h));
    }

    $img2 = imagecreatetruecolor($w, $h);
    if (is_array($transparent_color)) {
        list($r, $g, $b) = $transparent_color;
        $col = imagecolorallocate($img2,  $r, $g, $b);
        imagefilledrectangle($img2, 0, 0, $w, $h, $col);
    } else {
        imagealphablending($img2, false);
        imagesavealpha($img2, true);
        imagefilledrectangle($img2, 0, 0, $w, $h, imagecolorallocatealpha($img2, 255, 255, 255, 127));
    }

    imagecopyresampled($img2, $img, 0, 0, 0, 0, $w, $h, $curw, $curh);
    imagedestroy($img);

    $img = $img2;
}

function rrmdir(string $dir, bool $dont_delete_dir = false): bool {
    if (!is_dir($dir)) {
        logError('rrmdir: '.$dir.' is not a directory');
        return false;
    }

    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != '.' && $object != '..') {
            if (is_dir($dir.'/'.$object)) {
                rrmdir($dir.'/'.$object);
            } else {
                unlink($dir.'/'.$object);
            }
        }
    }

    if (!$dont_delete_dir)
        rmdir($dir);

    return true;
}

function ip2ulong(string $ip): int {
    return sprintf("%u", ip2long($ip));
}

function ulong2ip(int $ip): string {
    $long = 4294967295 - ($ip - 1);
    return long2ip(-$long);
}

function from_camel_case(string $s): string {
    $buf = '';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        if (!ctype_upper($s[$i])) {
            $buf .= $s[$i];
        } else {
            $buf .= '_'.strtolower($s[$i]);
        }
    }
    return $buf;
}

function to_camel_case(string $input, string $separator = '_'): string {
    return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
}

function str_replace_once(string $needle, string $replace, string $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos !== false)
        $haystack = substr_replace($haystack, $replace, $pos, strlen($needle));
    return $haystack;
}

function strgen(int $len): string {
    $buf = '';
    for ($i = 0; $i < $len; $i++) {
        $j = mt_rand(0, 61);
        if ($j >= 36) {
            $j += 13;
        } else if ($j >= 10) {
            $j += 7;
        }
        $buf .= chr(48 + $j);
    }
    return $buf;
}

function sanitize_filename(string $name): string {
    $name = mb_strtolower($name);
    $name = transliterate($name);
    $name = preg_replace('/[^\w\d\-_\s.]/', '', $name);
    $name = preg_replace('/\s+/', '_', $name);
    return $name;
}

function glob_escape(string $pattern): string {
    if (strpos($pattern, '[') !== false || strpos($pattern, ']') !== false) {
        $placeholder = uniqid();
        $replaces = array( $placeholder.'[', $placeholder.']', );
        $pattern = str_replace( array('[', ']', ), $replaces, $pattern);
        $pattern = str_replace( $replaces, array('[[]', '[]]', ), $pattern);
    }
    return $pattern;
}

/**
 * Does not support flag GLOB_BRACE
 *
 * @param string $pattern
 * @param int $flags
 * @return array
 */
function glob_recursive(string $pattern, int $flags = 0): array {
    $files = glob(glob_escape($pattern), $flags);
    foreach (glob(glob_escape(dirname($pattern)).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

function setperm(string $file): void {
    global $config;

    // chgrp
    $gid = filegroup($file);
    if ($gid != $config['group']) {
        if (!chgrp($file, $config['group'])) {
            logError(__FUNCTION__.": chgrp() failed on $file");
        }
    }

    // chmod
    $perms = fileperms($file);
    $need_perms = is_dir($file) ? $config['dirs_mode'] : $config['files_mode'];
    if (($perms & $need_perms) !== $need_perms) {
        if (!chmod($file, $need_perms)) {
            logError(__FUNCTION__.": chmod() failed on $file");
        }
    }
}

function salt_password(string $pwd): string {
    global $config;
    return hash('sha256', "{$pwd}|{$config['password_salt']}");
}

function exectime(?string $format = null) {
    $time = round(microtime(true) - START_TIME, 4);
    if (!is_null($format))
        $time = sprintf($format, $time);
    return $time;
}

function fullURL(string $url): string {
    global $config;
    return 'https://'.$config['domain'].$url;
}

function getDb(): SQLiteConnection|MySQLConnection|null {
    global $config;
    static $link = null;
    if (!is_null($link))
        return $link;

    switch ($config['db']['type']) {
        case 'mysql':
            $link = new MySQLConnection(
                $config['db']['host'],
                $config['db']['user'],
                $config['db']['password'],
                $config['db']['database']);
            if (!$link->connect()) {
                if (PHP_SAPI != 'cli') {
                    header('HTTP/1.1 503 Service Temporarily Unavailable');
                    header('Status: 503 Service Temporarily Unavailable');
                    header('Retry-After: 300');
                    die('database connection failed');
                } else {
                    fwrite(STDERR, 'database connection failed');
                    exit(1);
                }
            }
            break;

        case 'sqlite':
            $link = new SQLiteConnection($config['db']['path']);
            break;

        default:
            logError('invalid database type');
            break;
    }

    return $link;
}
