<?php

namespace skin\base;

function layout($ctx, $title, $unsafe_body, $static, $meta, $js, $opts, $exec_time, $unsafe_lang) {
return <<<HTML
<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="/favicon.ico?4" type="image/x-icon">
        <link rel="alternate" type="application/rss+xml" href="/feed.rss">
        <title>{$title}</title>
        {$ctx->renderMeta($meta)}
        {$ctx->renderStatic($static)}
    </head>
    <body{$ctx->if_true($opts['full_width'], ' class="full-width"')}>
        {$ctx->renderHeader(renderLogo($ctx, $opts['logo_path_map'], $opts['logo_link_map']))}
        <div class="page-content base-width">
            <div class="page-content-inner">{$unsafe_body}</div>
        </div>
        {$ctx->if_true($js != '' || !empty($lang) || $opts['dynlogo_enabled'],
            $ctx->renderScript, $js, $unsafe_lang, $opts['dynlogo_enabled'])} 
    </body>
</html>
<!-- exec time: {$exec_time}s -->
HTML;
}

function renderScript($ctx, $unsafe_js, $unsafe_lang, $enable_dynlogo) {
return <<<HTML
<script type="text/javascript">
{$ctx->if_true($unsafe_js, '(function(){'.$unsafe_js.'})();')}
{$ctx->if_true($unsafe_lang, 'extend(__lang, '.$unsafe_lang.');')}
{$ctx->if_true($enable_dynlogo, 'DynamicLogo.init();')}
</script>
HTML;
}

function renderMeta($ctx, $meta) {
    if (empty($meta))
        return '';
    return implode('', array_map(function(array $item): string {
        $s = '<meta';
        foreach ($item as $k => $v)
            $s .= ' '.htmlescape($k).'="'.htmlescape($v).'"';
        $s .= '>';
        return $s;
    }, $meta));
}

function renderStatic($ctx, $static) {
    global $config;
    $html = [];
    foreach ($static as $name) {
        // list($name, $options) = $item;
        $version = $config['is_dev'] ? time() : $config['static'][substr($name, 1)] ?? 'notfound';
        if (str_ends_with($name, '.js'))
            $html[] = jsLink($name, $version);
        else if (str_ends_with($name, '.css'))
            $html[] = cssLink($name, $version/*, $options*/);
    }
    return implode("\n", $html);
}

function renderHeader($ctx, $unsafe_logo_html) {
    return <<<HTML
<div class="head base-width">
    <div class="head-inner clearfix">
        <div class="head-logo">{$unsafe_logo_html}</div>
        <div class="head-items clearfix">
            <a class="head-item" href="/"><span><span>blog</span></span></a>
            <a class="head-item" href="/projects/"><span><span>projects</span></span></a>
            <a class="head-item" href="https://git.ch1p.io/?s=idle"><span><span>git</span></span></a>
            <a class="head-item" href="/misc/"><span><span>misc</span></span></a>
            <a class="head-item" href="/contacts/"><span><span>contacts</span></span></a>
            {$ctx->if_admin('<a class="head-item" href="/admin/"><span><span>admin</span></span></a>')}
        </div>
    </div>
</div>
HTML;
}

// TODO rewrite this fcking crap
function renderLogo($ctx, array $path_map = [], array $link_map = []): string {
    $uri = \RequestDispatcher::path();

    if (!\admin::isAdmin()) {
        $prompt_sign = '<span class="head-logo-dolsign">$</span>';
    } else {
        $prompt_sign = '<span class="head-logo-dolsign is_root">#</span>';
    }

    if ($uri == '/') {
        $html = '<span class="head-logo-path">/home/'.$ctx->lang('ch1p').'</span> '.$prompt_sign;
    } else {
        $uri_len = strlen($uri);

        $html = '<a href="/" id="head_dyn_link">';
        $close_tags = 0;

        $path_parts = [];
        $path_links = [];

        $last_pos = 0;
        $cur_path = '';
        while ($last_pos < $uri_len) {
            $first = $last_pos === 0;
            $end = false;

            $pos = strpos($uri, '/', $last_pos);
            if ($pos === false || $pos == $uri_len-1) {
                $pos = $uri_len-1;
                $end = true;
            }

            $part = substr($uri, $last_pos, $pos - $last_pos + 1);
            $cur_path .= $part;

            if ($end) {
                if (substr($part, -1) == '/')
                    $part = substr($part, 0, strlen($part)-1);
                $cur_path = '/';
                $html .= str_repeat('</span>', $close_tags-1);
                $close_tags = 1;
            }

            $span_class = 'head-logo-path';
            if ($first) {
                $span_class .= ' alwayshover';
            } else if ($end) {
                $span_class .= ' neverhover';
            }

            $html .= '<span class="'.$span_class.'" data-url="$[['.count($path_links).']]">${{'.count($path_parts).'}}';
            $path_parts[] = ($first ? '~' : '').$part;
            $path_links[] = $cur_path;

            $last_pos = $pos + 1;
            $close_tags++;
        }
        $html .= str_repeat('</span>', $close_tags).' '.$prompt_sign.' <span class="head-logo-cd">cd <span id="head_cd_text">~</span> <span class="head-logo-enter"><span class="head-logo-enter-icon"></span>Enter</span></span></a>';

        for ($i = count($path_parts)-1, $j = 0; $i >= 0; $i--, $j++) {
            if (isset($path_map[$j])) {
                $tmp = htmlescape(strtrim($path_map[$j], 40, $trimmed));
                if ($trimmed)
                    $tmp .= '&#8230;';
                $tmp_html = '<span class="head-logo-path-mapped">'.$tmp.'</span>';
                if ($j > 0)
                    $tmp_html .= '/';
                $html = str_replace_once('${{'.$i.'}}', $tmp_html, $html);
            } else {
                $html = str_replace_once('${{'.$i.'}}', $path_parts[$i], $html);
            }

            if (isset($link_map[$j])) {
                $html = str_replace_once('$[['.$i.']]', $link_map[$j], $html);
            } else {
                $html = str_replace_once('$[['.$i.']]', $path_links[$i], $html);
            }
        }
    }

    return $html;
}

function jsLink(string $name, $version = null): string {
    if ($version !== null)
        $name .= '?'.$version;
    return '<script src="'.$name.'" type="text/javascript"></script>';
}

function cssLink(string $name, $version = null/*, $options = null*/): string {
    global $config;
    if ($config['is_dev']) {
        $bname = basename($name);
        if (($pos = strrpos($bname, '.')))
            $bname = substr($bname, 0, $pos);
        $href = '/sass.php?name='.urlencode($bname);
    } else {
        $href = $name.($version !== null ? '?'.$version : '');
    }
    $s = '<link rel="stylesheet" type="text/css" href="'.$href.'"';
    // if (!is_null($options))
    //     $s .= ' media="'.$options.'"';
    $s .= '>';
    return $s;
}