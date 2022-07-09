<?php

namespace ansi;

enum Color: int {
    case BLACK   = 0;
    case RED     = 1;
    case GREEN   = 2;
    case YELLOW  = 3;
    case BLUE    = 4;
    case MAGENTA = 5;
    case CYAN    = 6;
    case WHITE   = 7;
}

function wrap(string $text,
              ?Color $fg = null,
              ?Color $bg = null,
              bool $bold = false,
              bool $fg_bright = false,
              bool $bg_bright = false): string {
    $codes = [];
    if (!is_null($fg))
        $codes[] = $fg->value + ($fg_bright ? 90 : 30);
    if (!is_null($bg))
        $codes[] = $bg->value + ($bg_bright ? 100 : 40);
    if ($bold)
        $codes[] = 1;

    if (empty($codes))
        return $text;

    return "\033[".implode(';', $codes)."m".$text."\033[0m";
}