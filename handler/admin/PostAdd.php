<?php

namespace handler\admin;

use csrf;
use posts;
use RedirectResponse;
use Response;

class PostAdd extends AutoAddOrEdit {

    public function get(): Response {
        return $this->_get_postAdd();
    }

    public function post(): Response {
        csrf::check('addpost');

        list($text, $title, $tags, $visible, $short_name)
            = $this->input('text, title, tags, b:visible, short_name');
        $tags = posts::splitStringToTags($tags);

        $error_code = null;

        if (!$title) {
            $error_code = 'no_title';
        } else if (!$text) {
            $error_code = 'no_text';
        } else if (empty($tags)) {
            $error_code = 'no_tags';
        } else if (empty($short_name)) {
            $error_code = 'no_short_name';
        }

        if ($error_code)
            return $this->_get_postAdd(
                text: $text,
                title: $title,
                tags: $tags,
                short_name: $short_name,
                error_code: $error_code
            );

        $id = posts::add([
            'title' => $title,
            'md' => $text,
            'visible' => (int)$visible,
            'short_name' => $short_name,
        ]);

        if (!$id)
            $this->_get_postAdd(
                text: $text,
                title: $title,
                tags: $tags,
                short_name: $short_name,
                error_code: 'db_err'
            );

        // set tags
        $post = posts::get($id);
        $tag_ids = posts::getTagIds($tags);
        $post->setTagIds($tag_ids);

        return new RedirectResponse($post->getUrl());
    }

}