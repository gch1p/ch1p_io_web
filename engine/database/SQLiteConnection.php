<?php

class SQLiteConnection extends CommonDatabase {

    const SCHEMA_VERSION = 0;

    protected SQLite3 $link;

    public function __construct(string $db_path) {
        $will_create = !file_exists($db_path);
        $this->link = new SQLite3($db_path);
        if ($will_create)
            setperm($db_path);
        $this->link->enableExceptions(true);
        $this->upgradeSchema();
    }

    protected function upgradeSchema() {
        $cur = $this->getSchemaVersion();
        if ($cur == self::SCHEMA_VERSION)
            return;

        if ($cur < 1) {
            // TODO
        }

        $this->syncSchemaVersion();
    }

    protected function getSchemaVersion() {
        return $this->link->query("PRAGMA user_version")->fetchArray()[0];
    }

    protected function syncSchemaVersion() {
        $this->link->exec("PRAGMA user_version=".self::SCHEMA_VERSION);
    }

    public function query(string $sql, ...$params): SQLite3Result {
        return $this->link->query($this->prepareQuery($sql, ...$params));
    }

    public function exec(string $sql, ...$params) {
        return $this->link->exec($this->prepareQuery($sql, ...$params));
    }

    public function querySingle(string $sql, ...$params) {
        return $this->link->querySingle($this->prepareQuery($sql, ...$params));
    }

    public function querySingleRow(string $sql, ...$params) {
        return $this->link->querySingle($this->prepareQuery($sql, ...$params), true);
    }

    public function insertId(): int {
        return $this->link->lastInsertRowID();
    }

    public function escape(string $s): string {
        return $this->link->escapeString($s);
    }

    public function fetch($q): ?array {
        // TODO: Implement fetch() method.
    }

    public function fetchAll($q): ?array {
        // TODO: Implement fetchAll() method.
    }

    public function fetchRow($q): ?array {
        // TODO: Implement fetchRow() method.
    }

    public function result($q, int $field = 0) {
        return $q?->fetchArray()[$field];
    }

    public function numRows($q): ?int {
        // TODO: Implement numRows() method.
    }
}