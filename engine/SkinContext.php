<?php

class SkinContext extends SkinBase {

    protected string $ns;
    protected array $data = [];

    public function __construct(string $namespace) {
        $this->ns = $namespace;
        require_once ROOT.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).'.skin.php';
    }

    public function __call($name, array $arguments) {
        $plain_args = array_is_list($arguments);

        $fn = $this->ns.'\\'.$name;
        $refl = new ReflectionFunction($fn);
        $fparams = $refl->getParameters();
        assert(count($fparams) == count($arguments)+1, "$fn: invalid number of arguments (".count($fparams)." != ".(count($arguments)+1).")");

        foreach ($fparams as $n => $param) {
            if ($n == 0)
                continue; // skip $ctx

            $key = $plain_args ? $n-1 : $param->name;
            if (!$plain_args && !array_key_exists($param->name, $arguments)) {
                if (!$param->isDefaultValueAvailable())
                    throw new InvalidArgumentException('argument '.$param->name.' not found');
                else
                    continue;
            }

            if (is_string($arguments[$key]) || $arguments[$key] instanceof SkinString) {
                if (is_string($arguments[$key]))
                    $arguments[$key] = new SkinString($arguments[$key]);

                if (($pos = strpos($param->name, '_')) !== false) {
                    $mod_type = match(substr($param->name, 0, $pos)) {
                        'unsafe' => SkinStringModificationType::RAW,
                        'urlencoded' => SkinStringModificationType::URL,
                        'jsonencoded' => SkinStringModificationType::JSON,
                        'addslashes' => SkinStringModificationType::ADDSLASHES,
                        default => SkinStringModificationType::HTML
                    };
                } else {
                    $mod_type = SkinStringModificationType::HTML;
                }
                $arguments[$key]->setModType($mod_type);
            }
        }

        array_unshift($arguments, $this);
        return call_user_func_array($fn, $arguments);
    }

    public function &__get(string $name) {
        $fn = $this->ns.'\\'.$name;
        if (function_exists($fn)) {
            $f = [$this, $name];
            return $f;
        }

        if (array_key_exists($name, $this->data))
            return $this->data[$name];
    }

    public function __set(string $name, $value) {
        $this->data[$name] = $value;
    }

    public function if_not($cond, $callback, ...$args) {
        return $this->_if_condition(!$cond, $callback, ...$args);
    }

    public function if_true($cond, $callback, ...$args) {
        return $this->_if_condition($cond, $callback, ...$args);
    }

    public function if_admin($callback, ...$args) {
        return $this->_if_condition(admin::isAdmin(), $callback, ...$args);
    }

    public function if_dev($callback, ...$args) {
        global $config;
        return $this->_if_condition($config['is_dev'], $callback, ...$args);
    }

    public function if_then_else($cond, $cb1, $cb2) {
        return $cond ? $this->_return_callback($cb1) : $this->_return_callback($cb2);
    }

    public function csrf($key): string {
        return csrf::get($key);
    }

    protected function _if_condition($condition, $callback, ...$args) {
        if (is_string($condition) || $condition instanceof Stringable)
            $condition = (string)$condition !== '';
        if ($condition)
            return $this->_return_callback($callback, $args);
        return '';
    }

    protected function _return_callback($callback, $args = []) {
        if (is_callable($callback))
            return call_user_func_array($callback, $args);
        else if (is_string($callback))
            return $callback;
    }

    public function for_each(array $iterable, callable $callback) {
        $html = '';
        foreach ($iterable as $k => $v)
            $html .= call_user_func($callback, $v, $k);
        return $html;
    }

}
