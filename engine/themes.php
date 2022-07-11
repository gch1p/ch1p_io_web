<?php

class themes {

    public static array $Themes = [
        'dark' => [
            'bg' => 0x222222,
            // 'alpha' => 0x303132,
            'alpha' => 0x222222,
        ],
        'light' => [
            'bg' => 0xffffff,
            // 'alpha' => 0xf2f2f2,
            'alpha' => 0xffffff,
        ]
    ];

    public static function getThemes(): array {
        return array_keys(self::$Themes);
    }

    public static function themeExists(string $name): bool {
        return array_key_exists($name, self::$Themes);
    }

    public static function getThemeAlphaColorAsRGB(string $name): array {
        $color = self::$Themes[$name]['alpha'];
        $r = ($color >> 16) & 0xff;
        $g = ($color >> 8) & 0xff;
        $b = $color & 0xff;
        return [$r, $g, $b];
    }

    public static function getUserTheme(): string {
        return ($_COOKIE['theme'] ?? 'auto');
    }

}