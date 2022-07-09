<?php

class LangData implements ArrayAccess {

    private static ?LangData $instance = null;
    protected array $data = [];
    protected array $loaded = [];

    public static function getInstance(): static {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->load('en');
        }
        return self::$instance;
    }

    public function __invoke(string $key, ...$args) {
        $val = $this[$key];
        return empty($args) ? $val : sprintf($val, ...$args);
    }

    public function load(string $name) {
        if (array_key_exists($name, $this->loaded))
            return;

        $data = require_once ROOT."/lang/{$name}.php";
        $this->data = array_replace($this->data,
            $data);

        $this->loaded[$name] = true;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        logError(__METHOD__ . ': not implemented');
    }

    public function offsetExists($offset): bool {
        return isset($this->data[$offset]);
    }

    public function offsetUnset(mixed $offset): void {
        logError(__METHOD__ . ': not implemented');
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->data[$offset] ?? '{' . $offset . '}';
    }

    public function search(string $regexp): array|false {
        return preg_grep($regexp, array_keys($this->data));
    }

    // function plural(array $s, int $n, array $opts = []) {
    //     $opts = array_merge([
    //         'format' => true,
    //         'format_delim' => ' ',
    //         'lang' => 'en',
    //     ], $opts);
    //
    //     switch ($opts['lang']) {
    //         case 'ru':
    //             $n = $n % 100;
    //             if ($n > 19)
    //                 $n %= 10;
    //
    //             if ($n == 1) {
    //                 $word = 0;
    //             } else if ($n >= 2 && $n <= 4) {
    //                 $word = 1;
    //             } else if ($n == 0 && count($s) == 4) {
    //                 $word = 3;
    //             } else {
    //                 $word = 2;
    //             }
    //             break;
    //
    //         default:
    //             if (!$n && count($s) == 4) {
    //                 $word = 3;
    //             } else {
    //                 $word = (int)!!$n;
    //             }
    //             break;
    //     }
    //
    //     // if zero
    //     if ($word == 3)
    //         return $s[3];
    //
    //     if (is_callable($opts['format'])) {
    //         $num = $opts['format']($n);
    //     } else if ($opts['format'] === true) {
    //         $num = formatNumber($n, $opts['format_delim']);
    //     }
    //
    //     return sprintf($s[$word], $num);
    // }
    //
    // function formatNumber(int $num, string $delim = ' ', bool $short = false): string {
    //     if ($short) {
    //         if ($num >= 1000000)
    //             return floor($num / 1000000).'m';
    //         if ($num >= 1000)
    //             return floor($num / 1000).'k';
    //     }
    //     return number_format($num, 0, '.', $delim);
    // }
}
