<?php

class csrf {

    public static function check(string $key): void {
        $user_csrf = self::get($key);
        $sent_csrf = $_REQUEST['token'] ?? '';

        if ($sent_csrf != $user_csrf)
            throw new ForbiddenException("csrf error");
    }

    public static function get(string $key): string {
        return self::getToken($_SERVER['REMOTE_ADDR'], $key);
    }

    protected static function getToken(string $user_token, string $key): string {
        global $config;
        return substr(sha1($config['csrf_token'].$user_token.$key), 0, 20);
    }

}