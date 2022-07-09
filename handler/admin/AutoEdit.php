<?php

namespace handler\admin;

use csrf;
use pages;
use posts;
use Response;

class AutoEdit extends AutoAddOrEdit {

    public function get(): Response {
        list($short_name, $saved) = $this->input('short_name, b:saved');

        $post = posts::getPostByName($short_name);
        if ($post) {
            $tags = $post->getTags();
            return $this->_get_postEdit($post,
                tags: $post->getTags(),
                saved: $saved,
                title: $post->title,
                text: $post->md,
                visible: $post->visible,
                short_name: $post->shortName,
            );
        }

        $page = pages::getPageByName($short_name);
        if ($page) {
            return $this->_get_pageEdit($page,
                title: $page->title,
                text: $page->md,
                visible: $page->visible,
                saved: $saved,
            );
        }

        throw new \NotFoundException();
    }

    public function post(): Response {
        list($short_name) = $this->input('short_name');

        $post = posts::getPostByName($short_name);
        if ($post) {
            csrf::check('editpost'.$post->id);

            list($text, $title, $tags, $visible, $short_name)
                = $this->input('text, title, tags, b:visible, new_short_name');

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
                $this->_get_postEdit($post,
                    text: $text,
                    title: $title,
                    tags: $tags,
                    visible: $visible,
                    short_name: $short_name,
                    error_code: $error_code
                );

            $post->edit([
                'title' => $title,
                'md' => $text,
                'visible' => (int)$visible,
                'short_name' => $short_name
            ]);
            $tag_ids = posts::getTagIds($tags);
            $post->setTagIds($tag_ids);

            return new \RedirectResponse($post->getUrl().'edit/?saved=1');
        }

        $page = pages::getPageByName($short_name);
        if ($page) {
            csrf::check('editpage'.$page->shortName);

            list($text, $title, $visible, $short_name)
                = $this->input('text, title, b:visible, new_short_name');

            $text = trim($text);
            $title = trim($title);
            $error_code = null;

            if (!$title) {
                $error_code = 'no_title';
            } else if (!$text) {
                $error_code = 'no_text';
            } else if (!$short_name) {
                $error_code = 'no_short_name';
            }

            if ($error_code) {
                return $this->_get_pageEdit($page,
                    title: $title,
                    text: $text,
                    visible: $visible,
                    error_code: $error_code
                );
            }

            $page->edit([
                'title' => $title,
                'md' => $text,
                'visible' => (int)$visible,
                'short_name' => $short_name,
            ]);

            return new \RedirectResponse($page->getUrl().'edit/?saved=1');
        }

        throw new \NotFoundException();
    }

}