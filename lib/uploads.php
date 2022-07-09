<?php

class uploads {

    protected static $allowedExtensions = [
        'jpg', 'png', 'git', 'mp4', 'mp3', 'ogg', 'diff', 'txt', 'gz', 'tar',
        'icc', 'icm', 'patch', 'zip', 'brd', 'pdf', 'lua', 'xpi', 'rar', '7z',
        'tgz', 'bin', 'py', 'pac',
    ];

    public static function getCount(): int {
        $db = getDb();
        return (int)$db->result($db->query("SELECT COUNT(*) FROM uploads"));
    }

    public static function isExtensionAllowed(string $ext): bool {
        return in_array($ext, self::$allowedExtensions);
    }

    public static function add(string $tmp_name, string $name, string $note): ?int {
        global $config;

        $name = sanitize_filename($name);
        if (!$name)
            $name = 'file';

        $random_id = self::getNewRandomId();
        $size = filesize($tmp_name);
        $is_image = detect_image_type($tmp_name) !== false;
        $image_w = 0;
        $image_h = 0;
        if ($is_image) {
            list($image_w, $image_h) = getimagesize($tmp_name);
        }

        $db = getDb();
        if (!$db->insert('uploads', [
            'random_id' => $random_id,
            'ts' => time(),
            'name' => $name,
            'size' => $size,
            'image' => (int)$is_image,
            'image_w' => $image_w,
            'image_h' => $image_h,
            'note' => $note,
            'downloads' => 0,
        ])) {
            return null;
        }

        $id = $db->insertId();

        $dir = $config['uploads_dir'].'/'.$random_id;
        $path = $dir.'/'.$name;

        mkdir($dir);
        chmod($dir, 0775); // g+w

        rename($tmp_name, $path);
        chmod($path, 0664); // g+w

        return $id;
    }

    public static function delete(int $id): bool {
        $upload = self::get($id);
        if (!$upload)
            return false;

        $db = getDb();
        $db->query("DELETE FROM uploads WHERE id=?", $id);

        rrmdir($upload->getDirectory());
        return true;
    }

    /**
     * @return Upload[]
     */
    public static function getAll(): array {
        $db = getDb();
        $q = $db->query("SELECT * FROM uploads ORDER BY id DESC");
        return array_map('Upload::create_instance', $db->fetchAll($q));
    }

    public static function get(int $id): ?Upload {
        $db = getDb();
        $q = $db->query("SELECT * FROM uploads WHERE id=?", $id);
        if ($db->numRows($q)) {
            return new Upload($db->fetch($q));
        } else {
            return null;
        }
    }

    /**
     * @param string[] $ids
     * @param bool $flat
     * @return Upload[]
     */
    public static function getUploadsByRandomId(array $ids, bool $flat = false): array {
        if (empty($ids)) {
            return [];
        }

        $db = getDb();
        $uploads = array_fill_keys($ids, null);

        $q = $db->query("SELECT * FROM uploads WHERE random_id IN('".implode('\',\'', array_map([$db, 'escape'], $ids))."')");

        while ($row = $db->fetch($q)) {
            $uploads[$row['random_id']] = new Upload($row);
        }

        if ($flat) {
            $list = [];
            foreach ($ids as $id) {
                $list[] = $uploads[$id];
            }
            unset($uploads);
            return $list;
        }

        return $uploads;
    }

    public static function getByRandomId(string $random_id): ?Upload {
        $db = getDb();
        $q = $db->query("SELECT * FROM uploads WHERE random_id=? LIMIT 1", $random_id);
        if ($db->numRows($q)) {
            return new Upload($db->fetch($q));
        } else {
            return null;
        }
    }

    protected static function getNewRandomId(): string {
        $db = getDb();
        do {
            $random_id = strgen(8);
        } while ($db->numRows($db->query("SELECT id FROM uploads WHERE random_id=?", $random_id)) > 0);
        return $random_id;
    }

}
