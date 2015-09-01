<?php

class block_exacomp_db_record {
    var $data = array();
    
    const TABLE = 'todo';
    
    function __construct($data) {
        $this->data = $data;
    }
    function __get($name) {
        if (property_exists($this->data, $name)) {
            return $this->data->$name;
        } elseif (($method = 'get_'.$name) && method_exists($this, $method)) {
            return $this->$method();
        } else {
            print_error("property not found ".get_class($this)."::$name");
        }
    }
    
    function __isset($name) {
        if (property_exists($this->data, $name)) {
            return true;
        } elseif (($method = 'get_'.$name) && method_exists($this, $method)) {
            return true;
        } else {
            return false;
        }
    }
    
    function __set($name, $value) {
        $this->data->$name = $value;
    }
    
    static function get($conditions, $fields='*') {
        return static::get_record($conditions, $fields);
    }
    
    static function get_record($conditions, $fields='*') {
        if (is_string($conditions) || is_int($conditions)) {
            // id
            $conditions = array('id' => $conditions);
        } elseif (is_object($conditions) || is_array($conditions)) {
            // ok
        } else {
            print_error('wrong fields');
        }
        
        $data = static::_get_record($conditions, $fields);
        
        if (!$data) return null;
        
        return static::create($data);
    }
    
    static function _get_record(array $conditions, $fields='*') {
        print_error('not implemented');
    }

    static function get_records(array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        $records = static::_get_records($conditions, $sort, $fields, $limitfrom, $limitnum);
        
        return static::create_records($records);
    }

    static function _get_records(array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        print_error('not implemented');
    }
    
    static function create_records($records) {
        $records = array_map([get_called_class(), 'create'], $records);
        return $records;
    }
    
    static function create($data) {
        $class = get_called_class();
        
        if ($data instanceof $class) {
            return $data;
        }

        return new $class($data);
    }
}

class block_exacomp_topic extends block_exacomp_db_record {
    const TABLE = block_exacomp::DB_TOPICS;
    
    static function _get_record(array $conditions, $fields='*') {
        global $DB;
        
        if (!$fields) {
            $fields = 'id, title, parentid, subjid';
        }
        
        return $DB->get_record(self::TABLE, $conditions, $fields);
    }
    
    static function get_records_by_subject($subjectid) {
        global $DB;
        
        return self::create_records($DB->get_records_sql('
                SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.numb
                FROM {'.block_exacomp::DB_SUBJECTS.'} s
                JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
                    -- only show active ones
                    WHERE s.id = ?
                ORDER BY t.id, t.sorting, t.subjid
                ', array($subjectid)));
    }

    static function _get_records(array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        global $DB;
        
        return $DB->get_records(self::TABLE, $conditions, $sort, $fields, $limitfrom, $limitnum);
    }

    function get_numbering() {
        $numbering = substr(block_exacomp_get_subject_by_id($this->subjid)->title, 0,1).'.';
        
        //topic
        $numbering .= $this->numb.'.';
        
        return $numbering;
    }
    
    function get_descriptors() {
        // a little hacky, but it's so
        $descriptors = block_exacomp_descriptor::get_records_by_subject($this->id);
        
        return array_filter($descriptors, function($descriptor) {
            return $descriptor->topicid == $this->id;
        });
    }
}

class block_exacomp_descriptor extends block_exacomp_db_record {
    static function get_records_by_subject($subjectid) {
        $records = self::create_records($descriptors = block_exacomp_get_descriptors(0, true, $subjectid));
        
        foreach ($records as $record) {
            $record->children = self::create_records($record->children);
        }
        
        return $records;
    }

    function get_numbering() {
        global $DB;
        $topic = $this->get_topic();

        $numbering = $topic->numbering;

        if($this->parentid == 0){
            //Descriptor im Topic
            $desctopicmm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$this->id, 'topicid'=>$topic->id));
            $numbering .= $desctopicmm->sorting;
        }else{
            //Parent-Descriptor im Topic
            $desctopicmm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$this->parentid, 'topicid'=>$topic->id));
            $numbering .= $desctopicmm->sorting.'.';
            
            $numbering .= $this->sorting;
        }
        
        return $numbering;
    }
    
    function get_topic() {
        if (!isset($this->topicid)) {
            // required that topicid is set
            print_error('no topic loaded');
        }
        
        return block_exacomp_topic::get($this->topicid);
    }
}