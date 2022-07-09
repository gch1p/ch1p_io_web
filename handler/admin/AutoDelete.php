<?php

namespace handler\admin;

use csrf;
use NotFoundException;
use pages;
use posts;
use RedirectResponse;
use Response;

class AutoDelete extends AdminRequestHandler {

    public function get(): Response {
        list($name) = $this->input('short_name');

        $post = posts::getPostByName($name);
        if ($post) {
            csrf::check('delpost'.$post->id);
            posts::delete($post);
            return new RedirectResponse('/');
        }

        $page = pages::getPageByName($name);
        if ($page) {
            csrf::check('delpage'.$page->shortName);
            pages::delete($page);
            return new RedirectResponse('/');
        }

        throw new NotFoundException();
    }

}