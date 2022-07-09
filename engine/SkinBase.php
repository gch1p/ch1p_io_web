<?php

class SkinBase implements LangAccess {

    protected static LangData $ld;

    public static function __constructStatic(): void {
        self::$ld = LangData::getInstance();
    }

    public function lang(...$args): string {
        return htmlescape($this->langRaw(...$args));
    }

    public function langRaw(string $key, ...$args) {
        $val = self::$ld[$key];
        return empty($args) ? $val : sprintf($val, ...$args);
    }

}

SkinBase::__constructStatic();