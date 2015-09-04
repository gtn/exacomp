<?php

class block_exacomp_db_record {
    protected $funcData = null;
    protected $data = null;
    
    const TABLE = 'todo';
    
    function __construct($data) {
        $this->funcData = (object)array();
        $this->data = (object)array();
        
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        
        /*
        global $xcounts;
        $xcounts[get_called_class()."_cnt"]++;
        $xcounts[get_called_class()][$data->id]++;
        $this->debug = array_merge(array(), debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        */
    }
    function &__get($name) {
        if (property_exists($this->data, $name)) {
            return $this->data->$name;
        } elseif (property_exists($this->funcData, $name)) {
            return $this->funcData->$name;
        } elseif (($method = 'get_'.$name) && method_exists($this, $method)) {
            // store
            $this->funcData->$name = $this->$method();
            // return 
            return $this->funcData->$name;
        } else {
            print_error("property not found ".get_class($this)."::$name");
        }
    }
    
    function __isset($name) {
        if (property_exists($this->data, $name)) {
            return isset($this->data->$name);
        } elseif (property_exists($this->funcData, $name)) {
            return isset($this->funcData->$name);
        } elseif (($method = 'get_'.$name) && method_exists($this, $method)) {
            // store
            $this->funcData->$name = $this->$method();
            return isset($this->funcData->$name);
        } else {
            return false;
        }
    }
    
    function __set($name, $value) {
        if (($method = 'set_'.$name) && method_exists($this, $method)) {
            $ret = $this->$method($value);
            if ($ret !== null) {
                $this->funcData->$name = $ret;
            }
        } else {
            $this->data->$name = $value;
        }
    }
    public function __unset($name) {
        unset($this->data->$name);
        unset($this->funcData->$name);
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
        global $DB;
        
        return $DB->get_records(static::TABLE, $conditions, $sort, $fields, $limitfrom, $limitnum);
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

class block_exacomp_subject extends block_exacomp_db_record {
    const TABLE = block_exacomp::DB_SUBJECTS;

    function get_topics() {
        $topics = block_exacomp_topic::get_records_by_subject($this->id);

        array_walk($topics, function($topic) {
            $topic->subject = $this;
        });
        
        return $topics;
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
                SELECT t.id, t.title, t.parentid, t.subjid, t.source, t.numb
                FROM {'.block_exacomp::DB_SUBJECTS.'} s
                JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
                    -- only show active ones
                    WHERE s.id = ?
                ORDER BY t.id, t.sorting, t.subjid
                ', array($subjectid)));
    }

    function get_numbering() {
        if (!isset($this->subject)) {
            print_r($this->debug);
            var_dump($this);
            die('subj');
        }
        
        $numbering = $this->subject->titleshort.'.';
        
        //topic
        $numbering .= $this->numb.'.';
        
        return $numbering;
    }
    
    function get_descriptors() {
        // a little hacky, but it's so
        static $subjectDescriptors = array();
        if (!isset($subjectDescriptors[$this->subjid])) {
            $subjectDescriptors[$this->subjid] = block_exacomp_descriptor::get_records_by_subject($this->subjid);
        }
        
        $descriptors = array_filter($subjectDescriptors[$this->subjid], function($descriptor) {
            return $descriptor->topicid == $this->id;
        });
        
        array_walk($descriptors, function($descriptor) {
            $descriptor->topic = $this;
            array_walk($descriptor->children, function($descriptor) {
                $descriptor->topic = $this;
            });
        });
        
        return $descriptors;
    }
}

class block_exacomp_descriptor extends block_exacomp_db_record {
    const TABLE = block_exacomp::DB_DESCRIPTORS;
    
    static function get_records_by_subject($subjectid) {
        $records = self::create_records(block_exacomp_get_descriptors(0, true, $subjectid));
        
        return $records;
    }

    function get_numbering() {
        global $DB;
        $topic = $this->topic;

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
    
    function set_examples($examples) {
        $this->funcData->examples = block_exacomp_example::create_records($examples);

        // set descriptor in example
        array_walk($this->funcData->examples, function($example) {
            $example->descriptor = $this;
        });
    }
    
    function set_children($children) {
        $this->funcData->children = self::create_records($children);
    }
}

class block_exacomp_example extends block_exacomp_db_record {
    const TABLE = block_exacomp::DB_EXAMPLES;
    
    function get_numbering() {
        if (!isset($this->descriptor)) {
            // required that descriptor is set
            print_error('no descriptor loaded');
        }
        
        return $this->descriptor->numbering;
    }
}
