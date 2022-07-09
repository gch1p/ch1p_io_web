<?php

class admin {

    const SESSION_TIMEOUT = 86400 * 14;
    const COOKIE_NAME = 'admin_key';

    protected static ?bool $isAdmin = null;

    public static function isAdmin(): bool {
        if (is_null(self::$isAdmin))
            self::$isAdmin = self::_verifyKey();
        return self::$isAdmin;
    }

    protected static function _verifyKey(): bool {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $cookie = (string)$_COOKIE[self::COOKIE_NAME];
            if ($cookie !== self::getKey())
                self::unsetCookie();
            return true;
        }
        return false;
    }

    public static function checkPassword(string $pwd): bool {
        return salt_password($pwd) === config::get('admin_pwd');
    }

    protected static function getKey(): string {
        global $config;
        $admin_pwd_hash = config::get('admin_pwd');
        return salt_password("$admin_pwd_hash|{$_SERVER['REMOTE_ADDR']}");
    }

    public static function setCookie(): void {
        global $config;
        $key = self::getKey();
        setcookie(self::COOKIE_NAME, $key, time() + self::SESSION_TIMEOUT, '/', $config['cookie_host']);
    }

    public static function unsetCookie(): void {
        global $config;
        setcookie(self::COOKIE_NAME, '', 1, '/', $config['cookie_host']);
    }

    public static function logAuth(): void {
        getDb()->insert('admin_log', [
            'ts' => time(),
            'ip' => ip2ulong($_SERVER['REMOTE_ADDR']),
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }


}

