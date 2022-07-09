<?php

namespace handler\admin;

use Response;

class MarkdownPreview extends AdminRequestHandler {

    public function post(): Response {
        list($md, $title, $use_image_previews) = $this->input('md, title, b:use_image_previews');

        $html = \markup::markdownToHtml($md, $use_image_previews);

        $ctx = new \SkinContext('\\skin\\admin');
        $html = $ctx->markdownPreview(
            unsafe_html: $html,
            title: $title
        );
        return new \AjaxOkResponse(['html' => $html]);
    }

}