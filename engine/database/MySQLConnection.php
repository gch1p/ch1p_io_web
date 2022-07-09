<?php

class MySQLConnection extends CommonDatabase {

    protected ?mysqli $link = null;

    public function __construct(
        protected string $host,
        protected string $user,
        protected string $password,
        protected string $database) {}

    public function __destruct() {
        if ($this->link)
            $this->link->close();
    }

    public function connect(): bool {
        $this->link = new mysqli();
        return !!$this->link->real_connect($this->host, $this->user, $this->password, $this->database);
    }

    public function query(string $sql, ...$args): mysqli_result|bool {
        $sql = $this->prepareQuery($sql, ...$args);
        $q = $this->link->query($sql);
        if (!$q)
            logError(__METHOD__.': '.$this->link->error."\n$sql\n".backtrace(1));
        return $q;
    }

    public function fetch($q): ?array {
        $row = $q->fetch_assoc();
        if (!$row) {
            $q->free();
            return null;
        }
        return $row;
    }

    public function fetchAll($q): ?array {
        if (!$q)
            return null;
        $list = [];
        while ($f = $q->fetch_assoc()) {
            $list[] = $f;
        }
        $q->free();
        return $list;
    }

    public function fetchRow($q): ?array {
        return $q?->fetch_row();
    }

    public function result($q, $field = 0) {
        return $q?->fetch_row()[$field];
    }

    public function insertId(): int {
        return $this->link->insert_id;
    }

    public function numRows($q): ?int {
        return $q?->num_rows;
    }

    // public function affectedRows() {
    //     return $this->link->affected_rows;
    // }
    //
    // public function foundRows() {
    //     return $this->fetch($this->query("SELECT FOUND_ROWS() AS `count`"))['count'];
    // }

    public function escape(string $s): string {
        return $this->link->real_escape_string($s);
    }

}
