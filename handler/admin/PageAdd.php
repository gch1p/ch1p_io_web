<?php

namespace handler\admin;

use csrf;
use NotFoundException;
use pages;
use RedirectResponse;
use Response;

class PageAdd extends AutoAddOrEdit {

    public function get(): Response {
        list($name) = $this->input('short_name');
        $page = pages::getPageByName($name);
        if ($page)
            throw new NotFoundException();

        return $this->_get_pageAdd($name);
    }

    public function post(): Response {
        csrf::check('addpage');

        list($name) = $this->input('short_name');
        $page = pages::getPageByName($name);
        if ($page)
            throw new NotFoundException();

        $text = trim($_POST['text'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $error_code = null;

        if (!$title) {
            $error_code = 'no_title';
        } else if (!$text) {
            $error_code = 'no_text';
        }

        if ($error_code) {
            return $this->_get_pageAdd(
                name: $name,
                text: $text,
                title: $title,
                error_code: $error_code
            );
        }

        if (!pages::add([
            'short_name' => $name,
            'title' => $title,
            'md' => $text
        ])) {
            return $this->_get_pageAdd(
                name: $name,
                text: $text,
                title: $title,
                error_code: 'db_err'
            );
        }

        $page = pages::getPageByName($name);
        return new RedirectResponse($page->getUrl());
    }

}