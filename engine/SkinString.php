<?php

class SkinString implements Stringable {

    protected SkinStringModificationType $modType;

    public function __construct(protected string $string) {}

    public function setModType(SkinStringModificationType $modType) {
        $this->modType = $modType;
    }

    public function __toString(): string {
        return match ($this->modType) {
            SkinStringModificationType::HTML => htmlescape($this->string),
            SkinStringModificationType::URL => urlencode($this->string),
            SkinStringModificationType::JSON => json_encode($this->string, JSON_UNESCAPED_UNICODE),
            SkinStringModificationType::ADDSLASHES => addslashes($this->string),
            default => $this->string,
        };
    }

}