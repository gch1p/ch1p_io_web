<?php

namespace skin\base;

use admin;
use RequestDispatcher;

function layout($ctx, $title, $unsafe_body, $static, $meta, $js, $opts, $exec_time, $unsafe_lang, $theme) {
global $config;
$app_config = json_encode([
    'domain' => $config['domain'],
    'devMode' => $config['is_dev'],
    'cookieHost' => $config['cookie_host'],
]);

return <<<HTML
<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="/favicon.ico?4" type="image/x-icon">
        <link rel="alternate" type="application/rss+xml" href="/feed.rss">
        <title>{$title}</title>
        <script type="text/javascript">window.appConfig = {$app_config};</script>
        {$ctx->renderMeta($meta)}
        {$ctx->renderStatic($static, $theme)}
    </head>
    <body{$ctx->if_true($opts['full_width'], ' class="full-width"')}>
        {$ctx->renderHeader($theme, renderLogo($ctx, $opts['logo_path_map'], $opts['logo_link_map']))}
        <div class="page-content base-width">
            <div class="page-content-inner">{$unsafe_body}</div>
        </div>
       {$ctx->renderScript($js, $unsafe_lang, $opts['dynlogo_enabled'])}
    </body>
</html>
<!--
    exec time: {$exec_time}s
    looking for sources? check out https://git.ch1p.io/ch1p_io_web.git
-->
HTML;
}

function renderScript($ctx, $unsafe_js, $unsafe_lang, $enable_dynlogo) {
global $config;

$styles = json_encode($ctx->styleNames);
if ($config['is_dev'])
    $versions = '{}';
else {
    $versions = [];
    foreach ($config['static'] as $name => $v) {
        list($type, $bname) = getStaticNameParts($name);
        $versions[$type][$bname] = $v;
    }
    $versions = json_encode($versions);
}

return <<<HTML
<script type="text/javascript">
StaticManager.init({$styles}, {$versions});
{$ctx->if_true($unsafe_js, '(function(){'.$unsafe_js.'})();')}
{$ctx->if_true($unsafe_lang, 'extend(__lang, '.$unsafe_lang.');')}
{$ctx->if_true($enable_dynlogo, 'DynamicLogo.init();')}
ThemeSwitcher.init();
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

function renderStatic($ctx, $static, $theme) {
    global $config;
    $html = [];
    $dark = $theme == 'dark';
    $ctx->styleNames = [];
    foreach ($static as $name) {
        // javascript
        if (str_starts_with($name, 'js/'))
            $html[] = jsLink($name);

        // css
        else if (str_starts_with($name, 'css/')) {
            $html[] = cssLink($name, 'light', $style_name);
            $ctx->styleNames[] = $style_name;

            if ($dark)
                $html[] = cssLink($name, 'dark', $style_name);
            else if (!$config['is_dev'])
                $html[] = cssPrefetchLink($style_name.'_dark');
        }
        else
            logError(__FUNCTION__.': unexpected static entry: '.$name);
    }
    return implode("\n", $html);
}

function jsLink(string $name): string {
    global $config;
    list (, $bname) = getStaticNameParts($name);
    if ($config['is_dev']) {
        $href = '/js.php?name='.urlencode($bname).'&amp;v='.time();
    } else {
        $href = '/dist-js/'.$bname.'.js?'.getStaticVersion($name);
    }
    return '<script src="'.$href.'" type="text/javascript"></script>';
}

function cssLink(string $name, string $theme, &$bname = null): string {
    global $config;

    list(, $bname) = getStaticNameParts($name);

    if ($config['is_dev']) {
        $href = '/sass.php?name='.urlencode($bname).'&amp;theme='.$theme.'&amp;v='.time();
    } else {
        $version = getStaticVersion('css/'.$bname.($theme == 'dark' ? '_dark' : '').'.css');
        $href = '/dist-css/'.$bname.($theme == 'dark' ? '_dark' : '').'.css?'.$version;
    }

    $id = 'style_'.$bname;
    if ($theme == 'dark')
        $id .= '_dark';

    return '<link rel="stylesheet" id="'.$id.'" type="text/css" href="'.$href.'">';
}

function cssPrefetchLink(string $name): string {
$url = '/dist-css/'.$name.'.css?'.getStaticVersion('css/'.$name.'.css');
return <<<HTML
<link rel="prefetch" href="{$url}" />
HTML;
}

function getStaticNameParts(string $name): array {
    $dname = dirname($name);
    $bname = basename($name);
    if (($pos = strrpos($bname, '.'))) {
        $ext = substr($bname, $pos+1);
        $bname = substr($bname, 0, $pos);
    } else {
        $ext = '';
    }
    return [$dname, $bname, $ext];
}

function getStaticVersion(string $name): string {
    global $config;
    if ($config['is_dev'])
        return time();
    if (str_starts_with($name, '/')) {
        logWarning(__FUNCTION__.': '.$name.' starts with /');
        $name = substr($name, 1);
    }
    return $config['static'][$name] ?? 'notfound';
}

function renderHeader($ctx, $theme, $unsafe_logo_html) {
$items = [
    ['url' => 'javascript:void(0)', 'label' => $theme, 'label_id' => 'theme-switcher-label', 'theme_switcher' => true],
    ['url' => '/', 'label' => 'blog'],
    ['url' => '/projects/', 'label' => 'projects'],
    ['url' => 'https://git.ch1p.io/?s=idle', 'label' => 'git'],
    ['url' => '/misc/', 'label' => 'misc'],
    ['url' => '/contacts/', 'label' => 'contacts'],
];
if (\admin::isAdmin())
    $items[] = ['url' => '/admin/', 'label' => 'admin'];

// here, items are rendered using for_each, so that there are no gaps (whitespaces) between tags

return <<<HTML
<div class="head base-width">
    <div class="head-inner clearfix">
        <div class="head-logo">{$unsafe_logo_html}</div>
        <div class="head-items clearfix">
            {$ctx->for_each($items, fn($item) => $ctx->renderHeaderItem($item['url'], $item['label'], $item['label_id'], $item['theme_switcher']))}
        </div>
    </div>
</div>
HTML;
}

function renderHeaderItem($ctx, $url, $label, $label_id, $is_theme_switcher) {
return <<<HTML
<a class="head-item{$ctx->if_true($is_theme_switcher, ' is-theme-switcher')}" href="{$url}"{$ctx->if_true($is_theme_switcher, ' onclick="return ThemeSwitcher.next(event)"')}>
    <span>
        {$ctx->if_true($is_theme_switcher, '<span class="moon-icon">'.$ctx->renderMoonIcon().'</span>')}
        <span{$ctx->if_true($label_id, ' id="'.$label_id.'"')}>{$label}</span>
    </span>
</a>
HTML;
}

// TODO rewrite this fcking crap
function renderLogo($ctx, array $path_map = [], array $link_map = []): string {
    $uri = RequestDispatcher::path();

    if (!admin::isAdmin()) {
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
        $html .= str_repeat('</span>', $close_tags).' '.$prompt_sign.' <span class="head-logo-cd">cd <span id="head_cd_text">~</span> <span class="head-logo-enter"><span class="head-logo-enter-icon">'.enterIcon().'</span>Enter</span></span></a>';

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

function enterIcon() {
return <<<SVG
<svg width="12" height="7" viewBox="0 0 9.6 5.172" xmlns="http://www.w3.org/2000/svg">
    <path d="M.4 2.586l2.779 2.8.648-.654-1.667-1.68H9.2V.253h-.926V2.12H2.16L3.827.44 3.18-.214z"/>
</svg>
SVG;
}

function renderMoonIcon($ctx) {
return <<<SVG
<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg"><path d="M14.54 10.37a5.4 5.4 0 01-6.91-6.91.59.59 0 00-.74-.75 6.66 6.66 0 00-2.47 1.54 6.6 6.6 0 1010.87 6.86.59.59 0 00-.75-.74zm-1.61 2.39a5.44 5.44 0 01-7.69-7.69 5.58 5.58 0 011-.76 6.55 6.55 0 007.47 7.47 5.15 5.15 0 01-.78.98z" fill-rule="evenodd" /></svg>
SVG;
}