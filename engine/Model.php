<?php

enum Type {
    case STRING;
    case INTEGER;
    case FLOAT;
    case ARRAY;
    case BOOLEAN;
    case JSON;
    case SERIALIZED;
}

abstract class Model {

    const DB_TABLE = null;
    const DB_KEY = 'id';

    protected static array $SpecCache = [];

    public static function create_instance(...$args) {
        $cl = get_called_class();
        return new $cl(...$args);
    }

    public function __construct(array $raw) {
        if (!isset(self::$SpecCache[static::class])) {
            list($fields, $model_name_map, $db_name_map) = static::get_spec();
            self::$SpecCache[static::class] = [
                'fields' => $fields,
                'model_name_map' => $model_name_map,
                'db_name_map' => $db_name_map
            ];
        }

        foreach (self::$SpecCache[static::class]['fields'] as $field)
            $this->{$field['model_name']} = self::cast_to_type($field['type'], $raw[$field['db_name']]);

        if (is_null(static::DB_TABLE))
            trigger_error('class '.get_class($this).' doesn\'t have DB_TABLE defined');
    }

    public function edit(array $fields) {
        $db = getDb();

        $model_upd = [];
        $db_upd = [];

        foreach ($fields as $name => $value) {
            $index = self::$SpecCache[static::class]['db_name_map'][$name] ?? null;
            if (is_null($index)) {
                logError(__METHOD__.': field `'.$name.'` not found in '.static::class);
                continue;
            }

            $field = self::$SpecCache[static::class]['fields'][$index];
            switch ($field['type']) {
                case Type::ARRAY:
                    if (is_array($value)) {
                        $db_upd[$name] = implode(',', $value);
                        $model_upd[$field['model_name']] = $value;
                    } else {
                        logError(__METHOD__.': field `'.$name.'` is expected to be array. skipping.');
                    }
                    break;

                case Type::INTEGER:
                    $value = (int)$value;
                    $db_upd[$name] = $value;
                    $model_upd[$field['model_name']] = $value;
                    break;

                case Type::FLOAT:
                    $value = (float)$value;
                    $db_upd[$name] = $value;
                    $model_upd[$field['model_name']] = $value;
                    break;

                case Type::BOOLEAN:
                    $db_upd[$name] = $value ? 1 : 0;
                    $model_upd[$field['model_name']] = $value;
                    break;

                case Type::JSON:
                    $db_upd[$name] = json_encode($value, JSON_UNESCAPED_UNICODE);
                    $model_upd[$field['model_name']] = $value;
                    break;

                case Type::SERIALIZED:
                    $db_upd[$name] = serialize($value);
                    $model_upd[$field['model_name']] = $value;
                    break;

                default:
                    $value = (string)$value;
                    $db_upd[$name] = $value;
                    $model_upd[$field['model_name']] = $value;
                    break;
            }
        }

        if (!empty($db_upd) && !$db->update(static::DB_TABLE, $db_upd, static::DB_KEY."=?", $this->get_id())) {
            logError(__METHOD__.': failed to update database');
            return;
        }

        if (!empty($model_upd)) {
            foreach ($model_upd as $name => $value)
                $this->{$name} = $value;
        }
    }

    public function get_id() {
        return $this->{to_camel_case(static::DB_KEY)};
    }

    public function as_array(array $fields = [], array $custom_getters = []): array {
        if (empty($fields))
            $fields = array_keys(static::$SpecCache[static::class]['db_name_map']);

        $array = [];
        foreach ($fields as $field) {
            if (isset($custom_getters[$field]) && is_callable($custom_getters[$field])) {
                $array[$field] = $custom_getters[$field]();
            } else {
                $array[$field] = $this->{to_camel_case($field)};
            }
        }

        return $array;
    }

    protected static function cast_to_type(Type $type, $value) {
        switch ($type) {
            case Type::BOOLEAN:
                return (bool)$value;

            case Type::INTEGER:
                return (int)$value;

            case Type::FLOAT:
                return (float)$value;

            case Type::ARRAY:
                return array_filter(explode(',', $value));

            case Type::JSON:
                $val = json_decode($value, true);
                if (!$val)
                    $val = null;
                return $val;

            case Type::SERIALIZED:
                $val = unserialize($value);
                if ($val === false)
                    $val = null;
                return $val;

            default:
                return (string)$value;
        }
    }

    protected static function get_spec(): array {
        $rc = new ReflectionClass(static::class);
        $props = $rc->getProperties(ReflectionProperty::IS_PUBLIC);

        $list = [];
        $index = 0;

        $model_name_map = [];
        $db_name_map = [];

        foreach ($props as $prop) {
            if ($prop->isStatic())
                continue;

            $name = $prop->getName();
            if (str_starts_with($name, '_'))
                continue;

            $type = $prop->getType();
            $phpdoc = $prop->getDocComment();

            $mytype = null;
            if (!$prop->hasType() && !$phpdoc)
                $mytype = Type::STRING;
            else {
                $typename = $type->getName();
                switch ($typename) {
                    case 'string':
                        $mytype = Type::STRING;
                        break;
                    case 'int':
                        $mytype = Type::INTEGER;
                        break;
                    case 'float':
                        $mytype = Type::FLOAT;
                        break;
                    case 'array':
                        $mytype = Type::ARRAY;
                        break;
                    case 'bool':
                        $mytype = Type::BOOLEAN;
                        break;
                }

                if ($phpdoc != '') {
                    $pos = strpos($phpdoc, '@');
                    if ($pos === false)
                        continue;

                    if (substr($phpdoc, $pos+1, 4) == 'json')
                        $mytype = Type::JSON;
                    else if (substr($phpdoc, $pos+1, 5) == 'array')
                        $mytype = Type::ARRAY;
                    else if (substr($phpdoc, $pos+1, 10) == 'serialized')
                        $mytype = Type::SERIALIZED;
                }
            }

            if (is_null($mytype))
                logError(__METHOD__.": ".$name." is still null in ".static::class);

            $dbname = from_camel_case($name);
            $list[] = [
                'type' => $mytype,
                'model_name' => $name,
                'db_name' => $dbname
            ];

            $model_name_map[$name] = $index;
            $db_name_map[$dbname] = $index;

            $index++;
        }

        return [$list, $model_name_map, $db_name_map];
    }

}
