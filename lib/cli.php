<?php

class cli {

    protected ?array $commandsCache = null;

    public function __construct(
        protected string $ns
    ) {}

    protected function usage($error = null): void {
        global $argv;

        if (!is_null($error))
            echo "error: {$error}\n\n";

        echo "Usage: $argv[0] COMMAND\n\nCommands:\n";
        foreach ($this->getCommands() as $c)
            echo "    $c\n";

        exit(is_null($error) ? 0 : 1);
    }

    public function getCommands(): array {
        if (is_null($this->commandsCache)) {
            $funcs = array_filter(get_defined_functions()['user'], fn(string $f) => str_starts_with($f, $this->ns));
            $funcs = array_map(fn(string $f) => str_replace('_', '-', substr($f, strlen($this->ns.'\\'))), $funcs);
            $this->commandsCache = array_values($funcs);
        }
        return $this->commandsCache;
    }

    public function run(): void {
        global $argv, $argc;

        if (PHP_SAPI != 'cli')
            cli::die('SAPI != cli');

        if ($argc < 2)
            $this->usage();

        $func = $argv[1];
        if (!in_array($func, $this->getCommands()))
            self::usage('unknown command "'.$func.'"');

        $func = str_replace('-', '_', $func);
        call_user_func($this->ns.'\\'.$func);
    }

    public static function input(string $prompt): string {
        echo $prompt;
        $input = substr(fgets(STDIN), 0, -1);
        return $input;
    }

    public static function silentInput(string $prompt = ''): string {
        echo $prompt;
        system('stty -echo');
        $input = substr(fgets(STDIN), 0, -1);
        system('stty echo');
        echo "\n";
        return $input;
    }

    public static function die($error): void {
        fwrite(STDERR, "error: {$error}\n");
        exit(1);
    }

}