<?php

namespace skin\rss;

function atom($ctx, $title, $link, $rss_link, $items) {
return <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
  <channel>
    <title>{$title}</title>
    <link>{$link}</link>
    <description/>
    <atom:link href="{$rss_link}" rel="self" type="application/rss+xml"/>
    {$ctx->for_each($items, fn($item) => $ctx->item(...$item))}
  </channel>
</rss>
HTML;
}

function item($ctx, $title, $link, $pub_date, $description) {
return <<<HTML
<item>
  <title>{$title}</title>
  <link>{$link}</link>
  <pubDate>{$pub_date}</pubDate>
  <description>{$description}</description>
</item>
HTML;
}