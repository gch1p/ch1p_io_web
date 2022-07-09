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

    public static function htmlRetinaFix(string $html): string {
        global $config;
        return preg_replace_callback(
            '/('.preg_quote($config['uploads_host'], '/').'\/\w{8}\/p)(\d+)x(\d+)(\.jpg)/',
            function($match) {
                return $match[1].(intval($match[2])*2).'x'.(intval($match[3])*2).$match[4];
            },
            $html
        );
    }

}