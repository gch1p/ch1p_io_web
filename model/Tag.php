<?php

class Tag extends Model implements Stringable {

    const DB_TABLE = 'tags';

    public int $id;
    public string $tag;
    public int $postsCount;
    public int $visiblePostsCount;

    public function getUrl(): string {
        return '/'.$this->tag.'/';
    }

    public function getPostsCount(bool $is_admin): int {
        return $is_admin ? $this->postsCount : $this->visiblePostsCount;
    }

    public function __toString(): string {
        return $this->tag;
    }

}
