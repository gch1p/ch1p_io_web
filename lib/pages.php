<?php

class pages {

    public static function add(array $data): ?int {
        $db = getDb();
        $data['ts'] = time();
        $data['html'] = markup::markdownToHtml($data['md']);
        if (!$db->insert('pages', $data))
            return null;
        return $db->insertId();
    }

    public static function delete(Page $page): void {
        getDb()->query("DELETE FROM pages WHERE short_name=?", $page->shortName);
    }

    public static function getPageByName(string $short_name): ?Page {
        $db = getDb();
        $q = $db->query("SELECT * FROM pages WHERE short_name=?", $short_name);
        return $db->numRows($q) ? new Page($db->fetch($q)) : null;
    }

    /**
     * @return Page[]
     */
    public static function getAll(): array {
        $db = getDb();
        return array_map('Page::create_instance', $db->fetchAll($db->query("SELECT * FROM pages")));
    }

}