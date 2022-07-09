<?php

namespace handler;

use admin;
use posts;

class Index extends \RequestHandler {

    public function get(): \Response {
        $posts = posts::getPosts(include_hidden: admin::isAdmin());
        $tags = posts::getAllTags(include_hidden: admin::isAdmin());

        $this->skin->title = "ch1p's Blog";
        $this->skin->setOptions(['dynlogo_enabled' => false]);
        return $this->skin->renderPage('main/index',
            posts: $posts,
            tags: $tags);
    }
}