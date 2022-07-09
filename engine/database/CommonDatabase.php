<?php

abstract class CommonDatabase {

    abstract public function query(string $sql, ...$args);
    abstract public function escape(string $s): string;
    abstract public function fetch($q): ?array;
    abstract public function fetchAll($q): ?array;
    abstract public function fetchRow($q): ?array;
    abstract public function result($q, int $field = 0);
    abstract public function insertId(): ?int;
    abstract public function numRows($q): ?int;

    protected function prepareQuery(string $sql, ...$args): string {
        global $config;
        if (!empty($args)) {
            $mark_count = substr_count($sql, '?');
            $positions = array();
            $last_pos = -1;
            for ($i = 0; $i < $mark_count; $i++) {
                $last_pos = strpos($sql, '?', $last_pos + 1);
                $positions[] = $last_pos;
            }
            for ($i = $mark_count - 1; $i >= 0; $i--) {
                $arg_val = $args[$i];
                if (is_null($arg_val)) {
                    $v = 'NULL';
                } else {
                    $v = '\''.$this->escape($arg_val) . '\'';
                }
                $sql = substr_replace($sql, $v, $positions[$i], 1);
            }
        }
        if (!empty($config['db']['log']))
            logDebug(__METHOD__.': ', $sql);
        return $sql;
    }

    public function insert(string $table, array $fields) {
        return $this->performInsert('INSERT', $table, $fields);
    }

    public function replace(string $table, array $fields) {
        return $this->performInsert('REPLACE', $table, $fields);
    }

    protected function performInsert(string $command, string $table, array $fields) {
        $names = [];
        $values = [];
        $count = 0;
        foreach ($fields as $k => $v) {
            $names[] = $k;
            $values[] = $v;
            $count++;
        }

        $sql = "{$command} INTO `{$table}` (`" . implode('`, `', $names) . "`) VALUES (" . implode(', ', array_fill(0, $count, '?')) . ")";
        array_unshift($values, $sql);

        return $this->query(...$values);
    }

    public function update(string $table, array $rows, ...$cond) {
        $fields = [];
        $args = [];
        foreach ($rows as $row_name => $row_value) {
            $fields[] = "`{$row_name}`=?";
            $args[] = $row_value;
        }
        $sql = "UPDATE `$table` SET ".implode(', ', $fields);
        if (!empty($cond)) {
            $sql .= " WHERE ".$cond[0];
            if (count($cond) > 1)
                $args = array_merge($args, array_slice($cond, 1));
        }
        return $this->query($sql, ...$args);
    }

    public function multipleInsert(string $table, array $rows) {
        list($names, $values) = $this->getMultipleInsertValues($rows);
        $sql = "INSERT INTO `{$table}` (`".implode('`, `', $names)."`) VALUES ".$values;
        return $this->query($sql);
    }

    public function multipleReplace(string $table, array $rows) {
        list($names, $values) = $this->getMultipleInsertValues($rows);
        $sql = "REPLACE INTO `{$table}` (`".implode('`, `', $names)."`) VALUES ".$values;
        return $this->query($sql);
    }

    protected function getMultipleInsertValues(array $rows): array {
        $names = [];
        $sql_rows = [];
        foreach ($rows as $i => $fields) {
            $row_values = [];
            foreach ($fields as $field_name => $field_val) {
                if ($i == 0) {
                    $names[] = $field_name;
                }
                $row_values[] = $this->escape($field_val);
            }
            $sql_rows[] = "('".implode("', '", $row_values)."')";
        }
        return [$names, implode(', ', $sql_rows)];
    }

}