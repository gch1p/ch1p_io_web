#!/usr/bin/env php8.1
<?php

namespace cli_util;

use cli;
use posts;
use uploads;
use pages;
use config;

require_once __DIR__.'/init.php';

$cli = new cli(__NAMESPACE__);
$cli->run();

function admin_reset(): void {
    $pwd1 = cli::silentInput("New password: ");
    $pwd2 = cli::silentInput("Again: ");

    if ($pwd1 != $pwd2)
        cli::die("Passwords do not match");

    if (trim($pwd1) == '')
        cli::die("Password can not be empty");

    if (!config::set('admin_pwd', salt_password($pwd1)))
        cli::die("Database error");
}

function admin_check(): void {
    $pwd = config::get('admin_pwd');
    echo is_null($pwd) ? "Not set" : $pwd;
    echo "\n";
}

function blog_erase(): void {
    $db = getDb();
    $tables = ['posts', 'posts_tags', 'tags'];
    foreach ($tables as $t) {
        $db->query("TRUNCATE TABLE $t");
    }
}

function tags_recount(): void {
    $tags = posts::getAllTags(true);
    foreach ($tags as $tag)
        posts::recountPostsWithTag($tag->id);
}

function posts_html(): void {
    $kw = ['include_hidden' => true];
    $posts = posts::getPosts(0, posts::getPostsCount(...$kw), ...$kw);
    foreach ($posts as $p) {
        $p->updateHtml();
        $p->updateText();
    }
}

function posts_images(): void {
    $kw = ['include_hidden' => true];
    $posts = posts::getPosts(0, posts::getPostsCount(...$kw), ...$kw);
    foreach ($posts as $p) {
        $p->updateImagePreviews(true);
    }
}

function pages_html(): void {
    $pages = pages::getAll();
    foreach ($pages as $p) {
        $p->updateHtml();
    }
}

function add_files_to_uploads(): void {
    $path = cli::input('Enter path: ');
    if (!file_exists($path))
        cli::die("file $path doesn't exists");
    $name = basename($path);
    $ext = extension($name);
    $id = uploads::add($path, $name, '');
    echo "upload id: $id\n";
}
