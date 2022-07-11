<?php

namespace skin\main;

// index page
// ----------

function index($ctx, array $posts, array $tags) {
    return empty($posts) ? $ctx->indexEmtpy() : $ctx->indexBlog($posts);
}

function indexEmtpy($ctx): string {
return <<<HTML
<div class="empty">
    {$ctx->lang('blog_no')}
    {$ctx->if_admin('<a href="/blog/write/">'.$ctx->lang('write').'</a>')}
</div>
HTML;
}

function indexBlog($ctx, array $posts): string {
return <<<HTML
<div class="blog-list">
    <div class="blog-list-title">
        all posts
        {$ctx->if_admin(
            '<span>
                <a href="/write/">new</a>
                <a href="/uploads/">uploads</a>
            </span>'
        )}
    </div>
    {$ctx->indexPostsTable($posts)}
</div>
HTML;
}

function indexPostsTable($ctx, array $posts): string {
$ctx->year = 3000;
return <<<HTML
<div class="blog-list-table-wrap">
    <table class="blog-list-table" width="100%" cellspacing="0" cellpadding="0">
        {$ctx->for_each($posts, fn($post) => $ctx->indexPostRow(
            $post->getYear(),
            $post->visible,
            $post->getDate(),
            $post->getUrl(),
            $post->title
        ))}
    </table>
</div>
HTML;
}

function indexPostRow($ctx, $year, $is_visible, $date, $url, $title): string {
return <<<HTML
{$ctx->if_true($ctx->year > $year, $ctx->indexYearLine, $year)}
<tr class="blog-item-row{$ctx->if_not($is_visible, ' ishidden')}">
    <td class="blog-item-date-cell">
        <span class="blog-item-date">{$date}</span>
    </td>
    <td class="blog-item-title-cell">
        <a class="blog-item-title" href="{$url}">{$title}</a>
    </td>
</tr>
HTML;
}

function indexYearLine($ctx, $year): string {
$ctx->year = $year;
return <<<HTML
<tr class="blog-item-row-year">
    <td class="blog-item-date-cell"><span>{$year}</span></td>
    <td></td>
</tr>
HTML;
}


// contacts page
// -------------

function contacts($ctx, $email) {
return <<<HTML
<table class="contacts" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" style="line-height: 170%; padding-bottom: 18px;">
            <div>Feel free to contact me by any of the following means:</div>
        </td>
    </tr>
    <tr>
        <td class="label">Email:</td>
        <td class="value">
            <a href="mailto:{$email}">{$email}</a>
            <div class="note">Please use <a href="/openpgp-pubkey.txt?1">PGP</a>.</div>
        </td>
    </tr>
    <tr>
        <td class="label">Telegram:</td>
        <td class="value">
            <a href="https://t.me/eacces">@eacces</a>
            <div class="note">Please use Secret Chats.</div>
        </td>
    </tr>
    <tr>
        <td class="label">Libera.Chat:</td>
        <td class="value"><span>ch1p</span></td>
    </tr>
</table>
HTML;

}


// any page
// --------

function page($ctx, $page_url, $short_name, $unsafe_html) {
return <<<HTML
<div class="page">
    {$ctx->if_admin($ctx->pageAdminLinks, $page_url, $short_name)}
    <div class="blog-post-text">{$unsafe_html}</div>
</div>
HTML;
}

function pageAdminLinks($ctx, $url, $short_name) {
return <<<HTML
<div class="page-edit-links">
    <a href="{$url}edit/">{$ctx->lang('edit')}</a>
    <a href="{$url}delete/?token={$ctx->csrf('delpage'.$short_name)}" onclick="return confirm('{$ctx->lang('pages_page_delete_confirmation')}')">{$ctx->lang('delete')}</a>
</div>
HTML;

}


// post page
// ---------

function post($ctx, $id, $title, $unsafe_html, $date, $visible, $url, $tags, $email, $urlencoded_reply_subject) {
return <<<HTML
<div class="blog-post">
    <div class="blog-post-title">
        <h1>{$title}</h1>
        <div class="blog-post-date">
            {$ctx->if_not($visible, '<b>'.$ctx->lang('blog_post_hidden').'</b> |')}
            {$date}
            {$ctx->if_admin($ctx->postAdminLinks, $url, $id)}
        </div>
        <div class="blog-post-tags clearfix">
            {$ctx->for_each($tags, fn($tag) => $ctx->postTag($tag->getUrl(), $tag->tag))}
        </div>
    </div>
    <div class="blog-post-text">{$unsafe_html}</div>
</div>
<div class="blog-post-comments">
    {$ctx->langRaw('blog_comments_text', $email, $urlencoded_reply_subject)}
</div>
HTML;
}

function postAdminLinks($ctx, $url, $id) {
return <<<HTML
<a href="{$url}edit/">{$ctx->lang('edit')}</a>
<a href="{$url}delete/?token={$ctx->csrf('delpost'.$id)}" onclick="return confirm('{$ctx->lang('blog_post_delete_confirmation')}')">{$ctx->lang('delete')}</a>
HTML;
}

function postTag($ctx, $url, $name) {
return <<<HTML
<a href="{$url}"><span>#</span>{$name}</a>
HTML;

}


// tag page
// --------

function tag($ctx, $count, $posts, $tag) {
if (!$count)
    return <<<HTML
    <div class="empty">
        {$ctx->lang('blog_tag_not_found')}
    </div>
HTML;

return <<<HTML
<div class="blog-list">
    <div class="blog-list-title">#{$tag}</div>
    {$ctx->indexPostsTable($posts)}
</div>
HTML;
}