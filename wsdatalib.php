<?php

require_once __DIR__."/../../config.php";

class block_exacomp_ws_datahandler {

    static $TABLENAME = 'block_exacompwsdata';
    protected $token = null;

    public function __construct($token = null) {
        if ($token) {
            $this->setToken($token);
        }
        $this->cleanTable();
    }

    // token must be in {external_tokens}
    protected function checkRealToken() {
        global $DB;
        if (!$DB->record_exists('external_tokens', ['token' => $this->token])) {
            block_exacomp_print_error('badtokenforwsdata', 'block_exacomp');
        }
    }

    public function setToken($token) {
        global $DB;
        $this->token = $token;
        $this->checkRealToken();
        if (!$DB->record_exists(self::$TABLENAME, ['token' => $this->token])) {
            $userid = $DB->get_field('external_tokens', 'userid', ['token' => $this->token]);
            $data = new \stdClass();
            $data->token = $this->token;
            $data->userid = $userid;
            $data->data = '';
            $DB->insert_record(self::$TABLENAME, $data);
        }
    }

    public function getAllWsData() {
        global $DB;
        $this->checkRealToken();
        return unserialize($DB->get_field(self::$TABLENAME, 'data', ['token' => $this->token]));
    }

    public function saveAllWsData($data) {
        global $DB;
        $this->checkRealToken();
        return $DB->execute('UPDATE {'.self::$TABLENAME.'} SET data = ? WHERE token = ?', [serialize($data), $this->token]);
    }

    public function setParam($paramName = null, $paramValue = null) {
        $this->checkRealToken();
        if ($paramName) {
            $allData = $this->getAllWsData();
            $allData[$paramName] = $paramValue;
            $this->saveAllWsData($allData);
        }
    }

    public function getParam($paramName = null) {
        $this->checkRealToken();
        if ($paramName) {
            $allData = $this->getAllWsData();
            if (array_key_exists($paramName, $allData)) {
                return $allData[$paramName];
            }
        }
        return null;
    }

    public function cleanParam($paramName = null) {
        $this->checkRealToken();
        if ($paramName) {
            $allData = $this->getAllWsData();
            if (array_key_exists($paramName, $allData)) {
                unset($allData[$paramName]);
                $this->saveAllWsData($allData);
            }
        }
    }

    public function cleanAll() {
        global $DB;
        $this->checkRealToken();
        return $DB->execute('UPDATE {'.self::$TABLENAME.'} SET data = ? WHERE token = ?', ['', $this->token]);
    }

    // cleaning of non actual tokens
    protected function cleanTable() {
        global $DB;
        // clean database from deleted data
        // all records which is not actual token will be deleted
        $cleanSql = 'DELETE FROM {'.self::$TABLENAME.'} 
                        WHERE token NOT IN (SELECT token FROM {external_tokens})';
        $DB->execute($cleanSql);
    }

}



