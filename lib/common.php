<?php

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

class exception extends \moodle_exception {
    function __construct($errorcode, $module='', $link='', $a=NULL, $debuginfo=null) {

        // try to get local error message (use namespace as $component)
        if (empty($module)) {
            if (get_string_manager()->string_exists($errorcode, __NAMESPACE__)) {
                $module = __NAMESPACE__;
            }
        }

        return parent::__construct($errorcode, $module, $link, $a, $debuginfo);
    }
}

class db {
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

class param {
    public static function clean_object($values, $definition) {
        // some value => type
        $ret = new \stdClass;
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

        $ret = array();
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

/**
 * Returns a localized string.
 * This method is neccessary because a project based evaluation is available in the current exastud
 * version, which requires a different naming.
 */
function get_string($identifier, $component = null, $a = null, $lazyload = false) {
    $manager = get_string_manager();

    if ($component === null)
        $component = __NAMESPACE__;

    if ($manager->string_exists($identifier, $component))
        return $manager->get_string($identifier, $component, $a);

    return $manager->get_string($identifier, '', $a);
}

function _t_check_identifier($string) {
    if (preg_match('!^([^:]+):(.*)$!', $string, $matches))
        return $matches;
    else
        return null;
}
function _t_parse_string($string, $a) {
    // copy from moodle/lib/classes/string_manager_standard.php
    // Process array's and objects (except lang_strings).
    if (is_array($a) or (is_object($a) && !($a instanceof lang_string))) {
        $a = (array)$a;
        $search = array();
        $replace = array();
        foreach ($a as $key => $value) {
            if (is_int($key)) {
                // We do not support numeric keys - sorry!
                continue;
            }
            if (is_array($value) or (is_object($value) && !($value instanceof lang_string))) {
                // We support just string or lang_string as value.
                continue;
            }
            $search[]  = '{$a->'.$key.'}';
            $replace[] = (string)$value;
        }
        if ($search) {
            $string = str_replace($search, $replace, $string);
        }
    } else {
        $string = str_replace('{$a}', (string)$a, $string);
    }
    
    return $string;
}
/*
 * translator function
 */
function t() {
    
    $origArgs = $args = func_get_args();
    
    $languagestrings = null;
    $identifier = '';
    $a = null;
    
    if (empty($args)) {
        print_error('no args');
    }
    
    $arg = array_shift($args);
    if (is_string($arg) && !_t_check_identifier($arg)) {
        $identifier = $arg;

        $arg = array_shift($args);
    }
    
    if ($arg === null) {
        // just id submitted
        $languagestrings = array();
    } elseif (is_array($arg)) {
        $languagestrings = $arg;
    } elseif (is_string($arg) && $matches = _t_check_identifier($arg)) {
        $languagestrings = array($matches[1] => $matches[2]);
    } else {
        print_error('wrong args: '.print_r($origArgs, true));
    }
    
    if (!empty($args)) {
        $a = array_shift($args);
    }
    
    // parse $languagestrings
    foreach ($languagestrings as $lang => $string) {
        if (is_number($lang)) {
            if ($matches = _t_check_identifier($string)) {
                $languagestrings[$matches[1]] = $matches[2];
            } else {
                print_error('wrong language string: '.$origArgs);
            }
        }
    }
    
    if (!empty($args)) {
        print_error('too many args: '.print_r($origArgs, true));
    }
    
    $lang = current_language();
    if (isset($languagestrings[$lang])) {
        return _t_parse_string($languagestrings[$lang], $a);
    } elseif ($languagestrings) {
        return _t_parse_string(reset($languagestrings), $a);
    } else {
        return get_string($identifier, null, $a);
    }
}
// tests:
/*
echo t('edit')."<br />";
echo t('de:xxx')."<br />";
echo t('id', 'de:xxx')."<br />";
echo t('id', ['de:xxx', 'en:yyy'])."<br />";
echo t('de:xxx {$a} xxx', 'arg')."<br />";
echo t('id', 'de:xxx {$a} xxx', 'arg')."<br />";
echo t('id', ['de:xxx {$a} xxx', 'en:xxx {$a} xxx'], 'arg')."<br />";
echo t('id', 'de:xxx {$a->arg} xxx', ['arg' => 'test'])."<br />";
echo t('id', ['de:xxx {$a->arg} xxx', 'en:xxx {$a->arg} xxx'], ['arg' => 'test'])."<br />";
exit;
/* */
