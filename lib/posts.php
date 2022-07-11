<?php

class posts {

    public static function getPostsCount(bool $include_hidden = false): int {
        $db = getDb();
        $sql = "SELECT COUNT(*) FROM posts";
        if (!$include_hidden) {
            $sql .= " WHERE visible=1";
        }
        return (int)$db->result($db->query($sql));
    }

    public static function getPostsCountByTagId(int $tag_id, bool $include_hidden = false): int {
        $db = getDb();
        if ($include_hidden) {
            $sql = "SELECT COUNT(*) FROM posts_tags WHERE tag_id=?";
        } else {
            $sql = "SELECT COUNT(*) FROM posts_tags
                LEFT JOIN posts ON posts.id=posts_tags.post_id
                WHERE posts_tags.tag_id=? AND posts.visible=1";
        }
        return (int)$db->result($db->query($sql, $tag_id));
    }

    /**
     * @return Post[]
     */
    public static function getPosts(int $offset = 0, int $count = -1, bool $include_hidden = false): array {
        $db = getDb();
        $sql = "SELECT * FROM posts";
        if (!$include_hidden)
            $sql .= " WHERE visible=1";
        $sql .= " ORDER BY ts DESC";
        if ($offset != 0 && $count != -1)
            $sql .=  "LIMIT $offset, $count";
        $q = $db->query($sql);
        return array_map('Post::create_instance', $db->fetchAll($q));
    }

    /**
     * @return Post[]
     */
    public static function getPostsByTagId(int $tag_id, bool $include_hidden = false): array {
        $db = getDb();
        $sql = "SELECT posts.* FROM posts_tags
            LEFT JOIN posts ON posts.id=posts_tags.post_id
            WHERE posts_tags.tag_id=?";
        if (!$include_hidden)
            $sql .= " AND posts.visible=1";
        $sql .= " ORDER BY posts.ts DESC";
        $q = $db->query($sql, $tag_id);
        return array_map('Post::create_instance', $db->fetchAll($q));
    }

    public static function add(array $data = []): int|bool {
        $db = getDb();

        $html = \markup::markdownToHtml($data['md']);
        $text = \markup::htmlToText($html);

        $data += [
            'ts' => time(),
            'html' => $html,
            'text' => $text,
        ];

        if (!$db->insert('posts', $data))
            return false;

        $id = $db->insertId();

        $post = posts::get($id);
        $post->updateImagePreviews();

        return $id;
    }

    public static function delete(Post $post): void {
        $tags = $post->getTags();

        $db = getDb();
        $db->query("DELETE FROM posts WHERE id=?", $post->id);
        $db->query("DELETE FROM posts_tags WHERE post_id=?", $post->id);

        foreach ($tags as $tag)
            self::recountPostsWithTag($tag->id);
    }

    public static function getTagIds(array $tags): array {
        $found_tags = [];
        $map = [];

        $db = getDb();
        $q = $db->query("SELECT id, tag FROM tags
            WHERE tag IN ('".implode("','", array_map(function($tag) use ($db) { return $db->escape($tag); }, $tags))."')");
        while ($row = $db->fetch($q)) {
            $found_tags[] = $row['tag'];
            $map[$row['tag']] = (int)$row['id'];
        }

        $notfound_tags = array_diff($tags, $found_tags);
        if (!empty($notfound_tags)) {
            foreach ($notfound_tags as $tag) {
                $db->insert('tags', ['tag' => $tag]);
                $map[$tag] = $db->insertId();
            }
        }

        return $map;
    }

    public static function get(int $id): ?Post {
        $db = getDb();
        $q = $db->query("SELECT * FROM posts WHERE id=?", $id);
        return $db->numRows($q) ? new Post($db->fetch($q)) : null;
    }

    public static function getPostByName(string $short_name): ?Post {
        $db = getDb();
        $q = $db->query("SELECT * FROM posts WHERE short_name=?", $short_name);
        return $db->numRows($q) ? new Post($db->fetch($q)) : null;
    }

    public static function getPostsById(array $ids, bool $flat = false): array {
        if (empty($ids)) {
            return [];
        }

        $db = getDb();
        $posts = array_fill_keys($ids, null);

        $q = $db->query("SELECT * FROM posts WHERE id IN(".implode(',', $ids).")");

        while ($row = $db->fetch($q)) {
            $posts[(int)$row['id']] = new Post($row);
        }

        if ($flat) {
            $list = [];
            foreach ($ids as $id) {
                $list[] = $posts[$id];
            }
            unset($posts);
            return $list;
        }

        return $posts;
    }

    public static function getAllTags(bool $include_hidden = false): array {
        $db = getDb();
        $field = $include_hidden ? 'posts_count' : 'visible_posts_count';
        $q = $db->query("SELECT * FROM tags WHERE $field > 0 ORDER BY $field DESC, tag");
        return array_map('Tag::create_instance', $db->fetchAll($q));
    }

    public static function getTag(string $tag): ?Tag {
        $db = getDb();
        $q = $db->query("SELECT * FROM tags WHERE tag=?", $tag);
        return $db->numRows($q) ? new Tag($db->fetch($q)) : null;
    }

    /**
     * @param int $tag_id
     */
    public static function recountPostsWithTag($tag_id) {
        $db = getDb();
        $count = $db->result($db->query("SELECT COUNT(*) FROM posts_tags WHERE tag_id=?", $tag_id));
        $vis_count = $db->result($db->query("SELECT COUNT(*) FROM posts_tags
            LEFT JOIN posts ON posts.id=posts_tags.post_id
            WHERE posts_tags.tag_id=? AND posts.visible=1", $tag_id));
        $db->query("UPDATE tags SET posts_count=?, visible_posts_count=? WHERE id=?",
            $count, $vis_count, $tag_id);
    }

    public static function splitStringToTags(string $tags): array {
        $tags = trim($tags);
        if ($tags == '') {
            return [];
        }

        $tags = preg_split('/,\s+/', $tags);
        $tags = array_filter($tags, function($tag) { return trim($tag) != ''; });
        $tags = array_map('trim', $tags);
        $tags = array_map('mb_strtolower', $tags);

        return $tags;
    }

}
