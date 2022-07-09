<?php

namespace handler;
use posts;
use Response;
use SkinContext;

class RSS extends \RequestHandler {

    public function get(): Response {
        global $config;

        $items = array_map(fn(\Post $post) => [
            'title' => $post->title,
            'link' => $post->getUrl(),
            'pub_date' => date(DATE_RSS, $post->ts),
            'description' => $post->getDescriptionPreview(500),
        ], posts::getPosts(0, 20));

        $ctx = new SkinContext('\\skin\\rss');
        $body = $ctx->atom(
            title: ($this->lang)('site_title'),
            link: 'https://'.$config['domain'],
            rss_link: 'https://'.$config['domain'].'/feed.rss',
            items: $items);

        $response = new Response(200, $body);
        $response->addHeader('Content-Type: application/rss+xml; charset=utf-8');
        return $response;
    }

}