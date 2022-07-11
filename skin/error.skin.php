<?php

namespace skin\error;

use Stringable;

function forbidden($ctx, $message) {
    return $ctx->common(403, 'Forbidden', $message);
}

function not_found($ctx, $message) {
    return $ctx->common(404, 'Not Found', $message);
}

function unauthorized($ctx, $message) {
    return $ctx->common(401, 'Unauthorized', $message);
}

function not_implemented($ctx, $message) {
    return $ctx->common(501, 'Not Implemented', $message);
}

function common($ctx,
                int $code,
                string|Stringable $title,
                string|Stringable|null $message = null) {
return <<<HTML
<html>
    <head><title>$code $title</title></head>
    <body>
        <center><h1>$code $title</h1></center>
        <hr>
        {$ctx->if_true($message, 
            '<p align="center">'.$message.'</p>'
        )}
    </body>
</html>
HTML;

}