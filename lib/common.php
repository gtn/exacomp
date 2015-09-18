<?php

class block_exacomp_exception extends moodle_exception {
    function __construct($errorcode, $module='', $link='', $a=NULL, $debuginfo=null) {

        // try to get exacomp error message
        if (empty($module)) {
            if (get_string_manager()->string_exists($errorcode, 'block_exacomp')) {
                $module = 'block_exacomp';
            }
        }

        return parent::__construct($errorcode, $module, $link, $a, $debuginfo);
    }
}

class block_exacomp_db {
    public static function update_record($table, $where, $data = array()) {
        global $DB;

        $where = (array)$where;
        $data = (array)$data;

        if ($dbItem = $DB->get_record($table, $where)) {
            if ($data) {
                $data['id'] = $dbItem->id;
                $DB->update_record($table, $data);
            }

            return (object)($data + $where);
        }

        return null;
    }

    public static function insert_or_update_record($table, $where, $data = array()) {
        global $DB;

        $where = (array)$where;
        $data = (array)$data;

        if ($dbItem = $DB->get_record($table, $where)) {
            if ($data) {
                $data['id'] = $dbItem->id;
                $DB->update_record($table, $data);
            }
        } else {
            $data = $data + $where;
            $id = $DB->insert_record($table, $data);
            $data['id'] = $id;
        }

        return (object)$data;
    }
}

class block_exascomp_param {
    public static function clean_object($values, $definition) {
        // some value => type
        $ret = new stdClass;
        $values = (object)$values;

        foreach ($definition as $key => $valueType) {
            $value = isset($values->$key) ? $values->$key : null;
            if (is_object($valueType)) {
                $ret->$key = static::clean_object($value, $valueType);
            } elseif (is_array($valueType)) {
                $ret->$key = static::clean_array($value, $valueType);
            } else {
                $ret->$key = clean_param($value, $valueType);
            }
        }

        return $ret;
    }

    public static function clean_array($values, $definition) {

        if (count($definition) != 1) {
            print_error('no array definition');
        }

        $keyType = key($definition);
        $valueType = reset($definition);

        if ($keyType !== PARAM_INT && $keyType !== PARAM_TEXT) {
            print_error('wrong key type: '.$keyType);
        }

        if (is_array($valueType)) {
            foreach ($values as $key=>$value) {
                $ret[clean_param($key, $keyType)] = static::clean_array($value, $valueType);
            }
        } elseif (is_object($valueType)) {
            foreach ($values as $key=>$value) {
                $ret[clean_param($key, $keyType)] = static::clean_object($value, $valueType);
            }
        } else {
            foreach ($values as $key=>$value) {
                $ret[clean_param($key, $keyType)] = clean_param($value, $valueType);
            }
        }

        return $ret;
    }

    public static function get_param($parname) {
        // POST has precedence.
        if (isset($_POST[$parname])) {
            return $_POST[$parname];
        } else if (isset($_GET[$parname])) {
            return $_GET[$parname];
        } else {
            return null;
        }
    }

    public static function optional_array($parname, array $definition) {
        $param = static::get_param($parname);

        if ($param === null) {
            return array();
        } else {
            return static::clean_array($param, $definition);
        }
    }

    public static function required_array($parname, array $definition) {
        $param = static::get_param($parname);

        if ($param === null) {
            print_error('param not found: '.$parname);
        } else {
            return static::clean_array($param, $definition);
        }
    }
}

