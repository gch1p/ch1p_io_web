<?php

class config {

    public static function get(string $key) {
        $db = getDb();
        $q = $db->query("SELECT value FROM config WHERE name=?", $key);
        if (!$db->numRows($q))
            return null;
        return $db->result($q);
    }

    public static function mget($keys) {
        $map = [];
        foreach ($keys as $key) {
            $map[$key] = null;
        }
        
        $db = getDb();
        $keys = array_map(fn($s) => $db->escape($s), $keys);

        $q = $db->query("SELECT * FROM config WHERE name IN('".implode("','", $keys)."')");
        while ($row = $db->fetch($q))
            $map[$row['name']] = $row['value'];

        return $map;
    }

    public static function set($key, $value) {
        $db = getDb();
        return $db->query("REPLACE INTO config (name, value) VALUES (?, ?)", $key, $value);
    }

    public static function mset($map) {
        $rows = [];
        foreach ($map as $name => $value) {
            $rows[] = [
                'name' => $name,
                'value' => $value
            ];
        }
        $db = getDb();
        return $db->multipleReplace('config', $rows);
    }

}
