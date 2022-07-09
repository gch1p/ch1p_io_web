<?php

class Post extends Model {

    const DB_TABLE = 'posts';

    public int $id;
    public string $title;
    public string $md;
    public string $html;
    public string $text;
    public int $ts;
    public int $updateTs;
    public bool $visible;
    public string $shortName;

    public function edit(array $data) {
        $cur_ts = time();
        if (!$this->visible && $data['visible'])
            $data['ts'] = $cur_ts;

        $data['update_ts'] = $cur_ts;

        if ($data['md'] != $this->md) {
            $data['html'] = \markup::markdownToHtml($data['md']);
            $data['text'] = \markup::htmlToText($data['html']);
        }

        parent::edit($data);
        $this->updateImagePreviews();
    }

    public function updateHtml() {
        $html = \markup::markdownToHtml($this->md);
        $this->html = $html;

        getDb()->query("UPDATE posts SET html=? WHERE id=?", $html, $this->id);
    }

    public function updateText() {
        $html = \markup::markdownToHtml($this->md);
        $text = \markup::htmlToText($html);
        $this->text = $text;

        getDb()->query("UPDATE posts SET text=? WHERE id=?", $text, $this->id);
    }

    public function getDescriptionPreview(int $len): string {
        if (mb_strlen($this->text) >= $len)
            return mb_substr($this->text, 0, $len-3).'...';
        return $this->text;
    }

    public function getFirstImage(): ?Upload {
        if (!preg_match('/\{image:([\w]{8})/', $this->md, $match))
            return null;
        return uploads::getByRandomId($match[1]);
    }

    public function getUrl(): string {
        return $this->shortName != '' ? "/{$this->shortName}/" : "/{$this->id}/";
    }

    public function getDate(): string {
        return date('j M', $this->ts);
    }

    public function getYear(): int {
        return (int)date('Y', $this->ts);
    }

    public function getFullDate(): string {
        return date('j F Y', $this->ts);
    }

    public function getUpdateDate(): string {
        return date('j M', $this->updateTs);
    }

    public function getFullUpdateDate(): string {
        return date('j F Y', $this->updateTs);
    }

    public function getHtml(bool $retina): string {
        $html = $this->html;
        if ($retina)
            $html = markup::htmlRetinaFix($html);
        return $html;
    }

    public function isUpdated(): bool {
        return $this->updateTs && $this->updateTs != $this->ts;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array {
        $db = getDb();
        $q = $db->query("SELECT tags.* FROM posts_tags
            LEFT JOIN tags ON tags.id=posts_tags.tag_id
            WHERE posts_tags.post_id=?
            ORDER BY posts_tags.tag_id", $this->id);
        return array_map('Tag::create_instance', $db->fetchAll($q));
    }

    /**
     * @return int[]
     */
    public function getTagIds(): array {
        $ids = [];
        $db = getDb();
        $q = $db->query("SELECT tag_id FROM posts_tags WHERE post_id=? ORDER BY tag_id", $this->id);
        while ($row = $db->fetch($q)) {
            $ids[] = (int)$row['tag_id'];
        }
        return $ids;
    }

    public function setTagIds(array $new_tag_ids) {
        $cur_tag_ids = $this->getTagIds();
        $add_tag_ids = array_diff($new_tag_ids, $cur_tag_ids);
        $rm_tag_ids = array_diff($cur_tag_ids, $new_tag_ids);

        $db = getDb();
        if (!empty($add_tag_ids)) {
            $rows = [];
            foreach ($add_tag_ids as $id)
                $rows[] = ['post_id' => $this->id, 'tag_id' => $id];
            $db->multipleInsert('posts_tags', $rows);
        }

        if (!empty($rm_tag_ids))
            $db->query("DELETE FROM posts_tags WHERE post_id=? AND tag_id IN(".implode(',', $rm_tag_ids).")", $this->id);

        $upd_tag_ids = array_merge($new_tag_ids, $rm_tag_ids);
        $upd_tag_ids = array_unique($upd_tag_ids);
        foreach ($upd_tag_ids as $id)
            posts::recountPostsWithTag($id);
    }

    /**
     * @param bool $update Whether to overwrite preview if already exists
     * @return int
     */
    public function updateImagePreviews(bool $update = false): int {
        $images = [];
        if (!preg_match_all('/\{image:([\w]{8}),(.*?)}/', $this->md, $matches))
            return 0;

        for ($i = 0; $i < count($matches[0]); $i++) {
            $id = $matches[1][$i];
            $w = $h = null;
            $opts = explode(',', $matches[2][$i]);
            foreach ($opts as $opt) {
                if (strpos($opt, '=') !== false) {
                    list($k, $v) = explode('=', $opt);
                    if ($k == 'w')
                        $w = (int)$v;
                    else if ($k == 'h')
                        $h = (int)$v;
                }
            }
            $images[$id][] = [$w, $h];
        }

        if (empty($images))
            return 0;

        $images_affected = 0;
        $uploads = uploads::getUploadsByRandomId(array_keys($images), true);
        foreach ($uploads as $u) {
            foreach ($images[$u->randomId] as $s) {
                list($w, $h) = $s;
                list($w, $h) = $u->getImagePreviewSize($w, $h);
                if ($u->createImagePreview($w, $h, $update)) {
                    $images_affected++;
                }
            }
        }

        return $images_affected;
    }

}
