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

class block_exacomp_param {
    public static function clean_object($values, $definition) {
        // some value => type
        $ret = new stdClass;
        $values = (object)$values;

        foreach ($definition as $key => $valueType) {
            $value = isset($values->$key) ? $values->$key : null;
            
            $ret->$key = static::clean($value, $valueType);
        }

        return $ret;
    }

    public static function clean_array($values, $definition) {

        if (count($definition) != 1) {
            print_error('no array definition');
        }

        $keyType = key($definition);
        $valueType = reset($definition);
        
        // allow clean_array(PARAM_TEXT): which means PARAM_INT=>PARAM_TEXT
        if ($keyType === 0) {
            $keyType = PARAM_INT;
        }

        if ($keyType !== PARAM_INT && $keyType !== PARAM_TEXT) {
            print_error('wrong key type: '.$keyType);
        }

        foreach ($values as $key=>$value) {
            $ret[clean_param($key, $keyType)] = static::clean($value, $valueType);
        }

        return $ret;
    }
    
    public static function clean($value, $definition) {
        if (is_object($definition)) {
            return static::clean_object($value, $definition);
        } elseif (is_array($definition)) {
            return static::clean_array($value, $definition);
        } else {
            return clean_param($value, $definition);
        }
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
    
    public static function required_json($parname, $definition = null) {
        $data = required_param($parname, PARAM_RAW);
        
        $data = json_decode($data, true);
        if ($data === null) {
            print_error('missingparam', '', '', $parname);
        }
        
        if ($definition === null) {
            return $data;
        } else {
            return static::clean($data, $definition);
        }
    }
}
