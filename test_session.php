<?php

require_once __DIR__."/../../config.php";

class dakoraVariableWs {

    protected $sessionHandler = null;
    protected $sessionHandlerName = '';
    protected $sessionId = null;
    static $sessionPartName = 'dakora_variables'; // for using as $_SESSION['SESSION']->dakora_variables

    public function __construct() {
        global $CFG, $DB;
        // get session id
        if (array_key_exists('MoodleSession', $_COOKIE)) {
            $currentSession = $_COOKIE['MoodleSession'];
        } else {
            $currentSession = '---'; // no moodle session. Will use empty session
        }

        if (\core\session\manager::session_exists($currentSession)) {
            $currentSession = $currentSession;
        } else {
            \core\session\manager::init_empty_session();
            $currentSession = session_id();
        }
        $this->sessionId = $currentSession;

        // get session handler class
        if (!empty($CFG->session_handler_class)) {
            $class = $CFG->session_handler_class;
        } else if (!empty($CFG->dbsessions) and $DB->session_lock_supported()) {
            $class = '\core\session\database';
        } else {
            $class = '\core\session\file';
            //$class = '\core\session\database';
        }
        /** @var core\session\database $sessionHandler */
        $this->sessionHandler = new $class();
        if (!$this->sessionHandler->session_exists($this->sessionId)) {
            throw new exception('dakorasessionhandlerproblem', 'error');
        }
        $this->sessionHandlerName = $class;
        if (!isset($_SESSION['SESSION']->{self::$sessionPartName})) {
            $_SESSION['SESSION']->{self::$sessionPartName} = array();
        }


    }

    public function saveData($name, $value) {
        switch ($this->sessionHandlerName) {
            case '\core\session\file':
            case '\core\session\database':
                $_SESSION['SESSION']->{self::$sessionPartName}[$name] = $value;
                break;
            /*case '\core\session\database':
                $_SESSION['SESSION']->{self::$sessionPartName}[$name] = $value;
                //$this->sessionHandler->handler_write($this->sessionId,  serialize($_SESSION));
                break;*/
        }
    }

    public function readData($name) {
        $result = null;
        switch ($this->sessionHandlerName) {
            case '\core\session\file':
            case '\core\session\database':
                if ($_SESSION['SESSION']->{self::$sessionPartName} && array_key_exists($name, $_SESSION['SESSION']->{self::$sessionPartName})) {
                    $result = $_SESSION['SESSION']->{self::$sessionPartName}[$name];
                }
                break;
            /*case '\core\session\database':
                $data = unserialize($this->sessionHandler->handler_read($this->sessionId));
                if (array_key_exists($name, $data['SESSION'])) {
                    $result = $data[$name];
                }
                break;*/
        }
        return $result;
    }

    public function clean($name) {
        if ($_SESSION['SESSION']->{self::$sessionPartName}) {
            unset($_SESSION['SESSION']->{self::$sessionPartName}[$name]);
        }
    }

}

// example of using
/*$action = required_param('action', PARAM_RAW); // set, get, delete
$varName = required_param('var_name', PARAM_RAW); // variable name
$varValue = optional_param('var_value', '', PARAM_RAW); // variable value

$ws = new dakoraVariableWs();

switch ($action) {
    case 'set':
        $ws->saveData($varName, $varValue);
        break;
    case 'delete':
        echo $ws->clean($varName);
        break;
    case 'get':
        echo $ws->readData($varName);
        exit; // for ajax?
        break;
}

echo 'OK';  // for ajax?
exit;*/



