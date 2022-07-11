<?php

namespace handler;

use admin;
use NotFoundException;
use pages;
use Post;
use posts;
use RedirectResponse;
use RequestHandler;
use Response;
use Tag;

class Auto extends RequestHandler {

    public function get(): Response {
        list($name) = $this->input('name');
        if ($name == 'coreboot-mba51-flashing')
            return new RedirectResponse('/coreboot-mba52-flashing/', 301);

        if (is_numeric($name)) {
            $post = posts::get((int)$name);
        } else {
            $post = posts::getPostByName($name);
        }
        if ($post)
            return $this->getPost($post);

        $tag = posts::getTag($name);
        if ($tag)
            return $this->getTag($tag);

        $page = pages::getPageByName($name);
        if ($page)
            return $this->getPage($page);

        if (admin::isAdmin()) {
            $this->skin->title = $name;
            return $this->skin->renderPage('admin/pageNew',
                short_name: $name);
        }

        throw new NotFoundException();
    }

    public function getPost(Post $post): Response {
        global $config;

        if (!$post->visible && !admin::isAdmin())
            throw new NotFoundException();

        $tags = $post->getTags();

        $s = $this->skin;
        $s->meta[] = ['property' => 'og:title', 'content' => $post->title];
        $s->meta[] = ['property' => 'og:url', 'content' => fullURL($post->getUrl())];
        if (($img = $post->getFirstImage()) !== null)
            $s->meta[] = ['property' => 'og:image', 'content' => $img->getDirectUrl()];
        $s->meta[] = [
            'name' => 'description',
            'property' => 'og:description',
            'content' => $post->getDescriptionPreview(155)
        ];

        $s->title = $post->title;

        return $s->renderPage('main/post',
            title: $post->title,
            id: $post->id,
            unsafe_html: $post->getHtml($this->isRetina(), \themes::getUserTheme()),
            date: $post->getFullDate(),
            tags: $tags,
            visible: $post->visible,
            url: $post->getUrl(),
            email: $config['admin_email'],
            urlencoded_reply_subject: 'Re: '.$post->title);
    }

    public function getTag(Tag $tag): Response {
        $tag = posts::getTag($tag);
        if (!admin::isAdmin() && !$tag->visiblePostsCount)
            throw new NotFoundException();

        $count = posts::getPostsCountByTagId($tag->id, admin::isAdmin());
        $posts = $count ? posts::getPostsByTagId($tag->id, admin::isAdmin()) : [];

        $this->skin->title = '#'.$tag->tag;
        return $this->skin->renderPage('main/tag',
            count: $count,
            posts: $posts,
            tag: $tag->tag);
    }

    public function getPage(\Page $page): Response {
        if (!admin::isAdmin() && !$page->visible)
            throw new NotFoundException();

        $this->skin->title = $page ? $page->title : '???';
        return $this->skin->renderPage('main/page',
            unsafe_html: $page->getHtml($this->isRetina(), \themes::getUserTheme()),
            page_url: $page->getUrl(),
            short_name: $page->shortName);
    }

}