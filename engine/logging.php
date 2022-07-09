<?php

require_once 'engine/ansi.php';
use \ansi\Color;
use function \ansi\wrap;

enum LogLevel {
    case ERROR;
    case WARNING;
    case INFO;
    case DEBUG;
}

function logDebug(...$args): void   { logging::logCustom(LogLevel::DEBUG, ...$args); }
function logInfo(...$args): void    { logging::logCustom(LogLevel::INFO, ...$args); }
function logWarning(...$args): void { logging::logCustom(LogLevel::WARNING, ...$args); }
function logError(...$args): void   { logging::logCustom(LogLevel::ERROR, ...$args); }

class logging {

    // private static $instance = null;

    protected static ?string $logFile = null;
    protected static bool $enabled = false;
    protected static int $counter = 0;

    /** @var ?callable $filter */
    protected static $filter = null;

    public static function setLogFile(string $log_file): void {
        self::$logFile = $log_file;
    }

    public static function setErrorFilter(callable $filter): void {
        self::$filter = $filter;
    }

    public static function disable(): void {
        self::$enabled = false;

        restore_error_handler();
        register_shutdown_function(function() {});
    }

    public static function enable(): void {
        self::$enabled = true;

        set_error_handler(function($no, $str, $file, $line) {
            if (is_callable(self::$filter) && !(self::$filter)($no, $file, $line, $str))
                return;

            self::write(LogLevel::ERROR, $str,
                errno: $no,
                errfile: $file,
                errline: $line);
        });

        register_shutdown_function(function() {
            if (!($error = error_get_last()))
                return;

            if (is_callable(self::$filter)
                && !(self::$filter)($error['type'], $error['file'], $error['line'], $error['message'])) {
                return;
            }

            self::write(LogLevel::ERROR, $error['message'],
                errno: $error['type'],
                errfile:  $error['file'],
                errline: $error['line']);
        });
    }

    public static function logCustom(LogLevel $level, ...$args): void {
        self::write($level, self::strVars($args));
    }

    protected static function write(LogLevel $level,
                                    string $message,
                                    ?int $errno = null,
                                    ?string $errfile = null,
                                    ?string $errline = null): void {

        // TODO test
        if (is_null(self::$logFile)) {
            fprintf(STDERR, __METHOD__.': logfile is not set');
            return;
        }

        $num = self::$counter++;
        $time = time();

        // TODO rewrite using sprintf
        $exec_time = strval(exectime());
        if (strlen($exec_time) < 6)
            $exec_time .= str_repeat('0', 6 - strlen($exec_time));

        // $bt = backtrace(2);

        $title = PHP_SAPI == 'cli' ? 'cli' : $_SERVER['REQUEST_URI'];
        $date = date('d/m/y H:i:s', $time);

        $buf = '';
        if ($num == 0) {
            $buf .= wrap(" $title ",
                fg: Color::WHITE,
                bg: Color::MAGENTA,
                fg_bright: true,
                bold: true);
            $buf .= wrap(" $date ", fg: Color::WHITE, bg: Color::BLUE, fg_bright: true);
            $buf .= "\n";
        }

        $letter = strtoupper($level->name[0]);
        $color = match ($level) {
            LogLevel::ERROR => Color::RED,
            LogLevel::INFO, LogLevel::DEBUG => Color::WHITE,
            LogLevel::WARNING => Color::YELLOW
        };

        $buf .= wrap($letter.wrap('='.wrap($num, bold: true)), fg: $color).' ';
        $buf .= wrap($exec_time, fg: Color::CYAN).' ';
        if (!is_null($errno)) {
            $buf .= wrap($errfile, fg: Color::GREEN);
            $buf .= wrap(':', fg: Color::WHITE);
            $buf .= wrap($errline, fg: Color::GREEN, fg_bright: true);
            $buf .= ' ('.self::getPhpErrorName($errno).') ';
        }

        $buf .= $message."\n";
        if (in_array($level, [LogLevel::ERROR, LogLevel::WARNING]))
            $buf .= backtrace(2)."\n";

        // TODO test
        $set_perm = !file_exists(self::$logFile);
        $f = fopen(self::$logFile, 'a');
        if (!$f) {
            fprintf(STDERR, __METHOD__.': failed to open file "'.self::$logFile.'" for writing');
            return;
        }

        fwrite($f, $buf);
        fclose($f);

        if ($set_perm)
            setperm($f);
    }

    protected static function getPhpErrorName(int $errno): string {
        static $errors = null;
        if (is_null($errors))
            $errors = array_flip(array_slice(get_defined_constants(true)['Core'], 0, 15, true));
        return $errors[$errno];
    }

    protected static function strVarDump($var, bool $print_r = false): string {
        ob_start();
        $print_r ? print_r($var) : var_dump($var);
        return trim(ob_get_clean());
    }

    protected static function strVars(array $args): string {
        $args = array_map(fn($a) => match (gettype($a)) {
            'string' => $a,
            'array', 'object' => self::strVarDump($a, true),
            default => self::strVarDump($a)
        }, $args);
        return implode(' ', $args);
    }

}

function backtrace(int $shift = 0): string {
    $bt = debug_backtrace();
    $lines = [];
    foreach ($bt as $i => $t) {
        if ($i < $shift)
            continue;

        if (!isset($t['file'])) {
            $lines[] = 'from ?';
        } else {
            $lines[] = 'from '.$t['file'].':'.$t['line'];
        }
    }
    return implode("\n", $lines);
}
