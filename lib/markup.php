<?php

class markup {

    public static function markdownToHtml(string $md, bool $use_image_previews = true): string {
        $pd = new MyParsedown(useImagePreviews: $use_image_previews);
        return $pd->text($md);
    }

    public static function htmlToText(string $html): string {
        $text = html_entity_decode(strip_tags($html));
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);
        $text = preg_replace("/(\r?\n){2,}/", "\n\n", $text);
        return $text;
    }

    public static function htmlImagesFix(string $html, bool $is_retina, string $user_theme): string {
        global $config;
        $is_dark_theme = $user_theme === 'dark';
        return preg_replace_callback(
            '/('.preg_quote($config['uploads_host'], '/').'\/\w{8}\/)([ap])(\d+)x(\d+)(\.jpg)/',
            function($match) use ($is_retina, $is_dark_theme) {
                $mult = $is_retina ? 2 : 1;
                $is_alpha = $match[2] == 'a';
                return $match[1].$match[2].(intval($match[3])*$mult).'x'.(intval($match[4])*$mult).($is_alpha && $is_dark_theme ? '_dark' : '').$match[5];
            },
            $html
        );
    }

}