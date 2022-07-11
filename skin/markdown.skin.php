<?php

namespace skin\markdown;

function fileupload($ctx, $name, $direct_url, $note, $size) {
return <<<HTML
<div class="md-file-attach">
    <span class="md-file-attach-icon"></span><a href="{$direct_url}">{$name}</a>
    {$ctx->if_true($note, '<span class="md-file-attach-note">'.$note.'</span>')}
    <span class="md-file-attach-size">{$size}</span>
</div>
HTML;
}

function image($ctx,
               // options
               $align, $nolabel, $w, $padding_top, $may_have_alpha,
               // image data
               $direct_url, $url, $note) {
return <<<HTML
<div class="md-image align-{$align}">
    <div class="md-image-wrap" data-alpha="{$ctx->if_then_else($may_have_alpha, '1', '0')}">
        <a href="{$direct_url}">
            <div style="background: url('{$url}') no-repeat; background-size: contain; width: {$w}px; padding-top: {$padding_top}%;"></div>
        </a>
        {$ctx->if_true(
            $note != '' && !$nolabel, 
            '<div class="md-image-note">'.$note.'</div>'
        )}
    </div>
</div>
HTML;
}

function video($ctx, $url, $w, $h) {
return <<<HTML
<div class="md-video">
    <div class="md-video-wrap">
        <video src="{$url}" controls{$ctx->if_true($w, ' width="'.$w.'"')}{$ctx->if_true($h, ' height="'.$h.'"')}></video>
    </div>
</div>
HTML;
}