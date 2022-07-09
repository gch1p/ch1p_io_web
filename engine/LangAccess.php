<?php

interface LangAccess {

    public function lang(...$args): string;
    public function langRaw(string $key, ...$args);

}