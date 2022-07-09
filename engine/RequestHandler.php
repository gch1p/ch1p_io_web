<?php

class RequestHandler {

    public function __construct(
        protected Skin $skin,
        protected LangData $lang,
        protected array $routerInput
    ) {}

    public function beforeDispatch(): ?Response {
        return null;
    }

    public function get(): Response {
        throw new NotImplementedException();
    }

    public function post(): Response {
        throw new NotImplementedException();
    }

    public function input(string $input): array {
        $input = preg_split('/,\s+?/', $input, -1, PREG_SPLIT_NO_EMPTY);
        $ret = [];
        foreach ($input as $var) {
            if (($pos = strpos($var, ':')) !== false) {
                $type = InputType::from(substr($var, 0, $pos));
                $name = trim(substr($var, $pos+1));
            } else {
                $type = InputType::STRING;
                $name = $var;
            }

            $value = $this->routerInput[$name] ?? $_REQUEST[$name] ?? '';
            switch ($type) {
                case InputType::INT:
                    $value = (int)$value;
                    break;
                case InputType::FLOAT:
                    $value = (float)$value;
                    break;
                case InputType::BOOL:
                    $value = (bool)$value;
                    break;
            }

            $ret[] = $value;
        }
        return $ret;
    }

    protected function isRetina(): bool {
        return isset($_COOKIE['is_retina']) && $_COOKIE['is_retina'];
    }
}
