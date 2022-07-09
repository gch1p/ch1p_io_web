<?php

namespace handler\admin;

use Page;
use Post;
use Response;

abstract class AutoAddOrEdit extends AdminRequestHandler {

    public function beforeDispatch(): ?Response {
        $this->skin->setOptions([
            'full_width' => true,
            'no_footer' => true
        ]);
        return parent::beforeDispatch();
    }

    protected function _get_postAdd(
        string $title = '',
        string $text = '',
        ?array $tags = null,
        string $short_name = '',
        ?string $error_code = null
    ): Response {
        $this->skin->addLangKeys($this->lang->search('/^(err_)?blog_/'));
        $this->skin->title = $this->lang['blog_write'];
        return $this->skin->renderPage('admin/postForm',
            title: $title,
            text: $text,
            tags: $tags ? implode(', ', $tags) : '',
            short_name: $short_name,
            error_code: $error_code);
    }

    protected function _get_postEdit(
        Post $post,
        string $title = '',
        string $text = '',
        ?array $tags = null,
        bool $visible = false,
        string $short_name = '',
        ?string $error_code = null,
        bool $saved = false,
    ): Response {
        $this->skin->addLangKeys($this->lang->search('/^(err_)?blog_/'));
        $this->skin->title = ($this->lang)('blog_post_edit_title', $post->title);
        return $this->skin->renderPage('admin/postForm',
            is_edit: true,
            post_id: $post->id,
            post_url: $post->getUrl(),
            title: $title,
            text: $text,
            tags: $tags ? implode(', ', $tags) : '',
            visible: $visible,
            saved: $saved,
            short_name: $short_name,
            error_code: $error_code
        );
    }

    protected function _get_pageAdd(
        string $name,
        string $title = '',
        string $text = '',
        ?string $error_code = null
    ): Response {
        $this->skin->addLangKeys($this->lang->search('/^(err_)?pages_/'));
        $this->skin->title = ($this->lang)('pages_create_title', $name);
        return $this->skin->renderPage('admin/pageForm',
            short_name: $name,
            title: $title,
            text: $text,
            error_code: $error_code);
    }

    protected function _get_pageEdit(
        Page $page,
        string $title = '',
        string $text = '',
        bool $saved = false,
        bool $visible = false,
        ?string $error_code = null
    ): Response {
        $this->skin->addLangKeys($this->lang->search('/^(err_)?pages_/'));
        $this->skin->title = ($this->lang)('pages_page_edit_title', $page->shortName.'.html');
        return $this->skin->renderPage('admin/pageForm',
            is_edit: true,
            short_name: $page->shortName,
            title: $title,
            text: $text,
            visible: $visible,
            saved: $saved,
            error_code: $error_code);
    }

}