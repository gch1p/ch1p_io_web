<?php

namespace handler;

class PostId extends \RequestHandler {

    public function get(): \Response {
        list($post_id) = $this->input('i:id');

        $post = posts_getPost($post_id);
        if (!$post || (!$post->visible && !\admin::isAdmin()))
            throw new \NotFoundException();

        if ($post->shortName != '')
            return new \RedirectResponse($post->getUrl());

        throw new \NotFoundException();
    }

}