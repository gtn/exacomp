<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../inc.php';

use block_exacomp\globals as g;
use Super\Cache;

class db_layer {

    public $courseid = 0;
    public $showalldescriptors = true;
    public $showallexamples = true;
    public $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES);
    public $showonlyvisible = false;
    public $mindvisibility = false;

    /**
     * @return db_layer
     */
    static function get() {
        static $default = null;

        $args = func_get_args();
        if ($args) {
            print_error('no args allowed in get');
        }

        if ($default === null) {
            $default = new static();
        }

        return $default;
    }

    /**
     * @return static
     */
    static function create() {
        $args = func_get_args();

        $class = get_called_class();
        $reflection = new \ReflectionClass($class);

        return $reflection->newInstanceArgs($args);
    }

    function get_descriptors_for_topic(topic $topic) {
        $descriptors = $this->get_descriptor_records_for_topic($topic);

        $descriptors = descriptor::create_objects($descriptors, ['topic' => $topic], $this);

        return $descriptors;
    }

    function get_descriptor_records_for_topic(topic $topic) {
        static $topicDescriptors = array();
        if (isset($topicDescriptors[$topic->id])) {
            return $topicDescriptors[$topic->id];
        }

        if (!$this->courseid) {
            $this->showalldescriptors = true;
            $this->showonlyvisible = false;
            $this->mindvisibility = false;
        }
        if (!$this->showalldescriptors) {
            $this->showalldescriptors = block_exacomp_get_settings_by_course($this->courseid)->show_all_descriptors;
        }

        $sql = "
			SELECT DISTINCT d.id, d.title, d.source, d.sourceid, d.niveauid, desctopmm.topicid, d.profoundness, d.parentid, d.sorting,
				n.sorting AS niveau_sorting, n.numb AS niveau_numb, n.title AS niveau_title, dvis.visible as visible, d.author, d.editor, d.creatorid as descriptor_creatorid
			FROM {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} desctopmm
			JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON desctopmm.descrid=d.id AND d.parentid=0
			-- left join, because courseid=0 has no descvisibility!
			LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?
			LEFT JOIN {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n ON d.niveauid = n.id
			" . ($this->showalldescriptors ? "" : "
				JOIN {" . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . "} ca ON d.id=ca.compid AND ca.comptype=" . BLOCK_EXACOMP_TYPE_DESCRIPTOR . "
				AND ca.activityid IN (" . block_exacomp_get_allowed_course_modules_for_course_for_select($this->courseid) . ")
			") . "
			WHERE desctopmm.topicid = ?
			" . ($this->showonlyvisible ? " AND (dvis.visible = 1 OR dvis.visible IS NULL)" : "");

        $descriptors = g::$DB->get_records_sql($sql, [$this->courseid, $topic->id]);

        block_exacomp_sort_items($descriptors, ['niveau_' => BLOCK_EXACOMP_DB_NIVEAUS, BLOCK_EXACOMP_DB_DESCRIPTORS]);

        $topicDescriptors[$topic->id] = $descriptors;

        return $descriptors;
    }

    function get_examples(descriptor $descriptor) {
        $dummy = $descriptor->get_data();
        block_exacomp_get_examples_for_descriptor($dummy, $this->filteredtaxonomies, $this->showallexamples, $this->courseid, true, $this->showonlyvisible);

        return example::create_objects($dummy->examples, array(
            'descriptor' => $descriptor,
        ), $this);
    }

    function get_child_descriptors(descriptor $parent) {
        global $DB;

        if (!$this->courseid) {
            $this->showalldescriptors = true;
            $this->showonlyvisible = false;
            $this->mindvisibility = false;
        }
        if (!$this->showalldescriptors) {
            $this->showalldescriptors = block_exacomp_get_settings_by_course($this->courseid)->show_all_descriptors;
        }

        $sql = 'SELECT d.id, d.title, d.niveauid, d.source, d.sourceid, ' . $parent->topicid . ' as topicid, d.profoundness, d.parentid, ' .
            ($this->mindvisibility ? 'dvis.visible as visible, ' : '') . ' d.sorting, d.author, d.editor, d.creatorid as descriptor_creatorid
			FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d '
            . ($this->mindvisibility ? 'JOIN {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
                . ($this->showonlyvisible ? 'AND dvis.visible=1 ' : '') : '');

        /* activity association only for parent descriptors
         .($this->showalldescriptors ? '' : '
         JOIN {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.BLOCK_EXACOMP_TYPE_DESCRIPTOR.'
         JOIN {course_modules} a ON da.activityid=a.id '.(($this->courseid>0)?'AND a.course=?':''));
        */
        $sql .= ' WHERE d.parentid = ?';

        $params = array();
        if ($this->mindvisibility) {
            $params[] = $this->courseid;
        }

        $params[] = $parent->id;
        //$descriptors = $DB->get_records_sql($sql, ($this->showalldescriptors) ? array($parent->id) : array($this->courseid,$parent->id));
        $descriptors = $DB->get_records_sql($sql, $params);

        $descriptors = descriptor::create_objects($descriptors, array(
            'parent' => $parent,
            'topic' => $parent->topicid,
        ), $this);

        return $descriptors;
    }

    /**
     * @return subject[]
     */
    function get_subjects() {
        return $this->init_objects(subject::get_objects());
    }

    /**
     * @return topic[]
     */
    function get_topics() {
        $subs = [];
        foreach ($this->get_subjects() as $sub) {
            $subs += $sub->subs;
        }

        return $subs;
    }

    /**
     * @return descriptor[]
     */
    function get_descriptor_parents() {
        $subs = [];
        foreach ($this->get_topics() as $sub) {
            $subs += $sub->subs;
        }

        return $subs;
    }

    function get_topics_for_subject(subject $subject) {
        $topics = topic::get_objects(['subjid' => $subject->id]);

        $this->init_objects($topics, ['subject' => $subject]);

        return block_exacomp_sort_items($topics, BLOCK_EXACOMP_DB_TOPICS);
    }

    function set_object_datas(array $objects, array $data) {
        foreach ($objects as $o) {
            foreach ($data as $key => $value) {
                $o->$key = $value;
            }
        }

        return $objects;
    }

    function init_objects(array $objects, array $data = []) {

        foreach ($objects as $o) {
            $o->setDbLayer($this);
        }

        $this->set_object_datas($objects, $data);

        return $objects;
    }

    /**
     * @param string $class
     * @param db_record[] $records
     * @param array $data
     * @return array
     */
    function create_objects($class, array $records, $data = array()) {
        $objects = array();
        array_walk($records, function($record) use ($class, &$objects, $data) {
            if ($data) {
                foreach ($data as $key => $value) {
                    $record->$key = $value;
                }
            }

            if ($record instanceof $class) {
                // already object
                $objects[$record->id] = $record;
                $objects[$record->id]->setDbLayer($this);
            } else {
                // create object
                if ($object = $class::create($record, $this)) {
                    $objects[$object->id] = $object;
                }
            }
        });

        return $objects;
    }
}

class db_layer_whole_moodle extends db_layer {
    function get_subjects_for_source($source, $subjects_preselection = null) {
        global $DB;
        $subjects = $this->get_subjects();
        // $subjects = array_values($subjects);
        // $subjects = array($subjects[10]); // , $subjects[1]);

        if ($subjects_preselection != -1) {
            foreach ($subjects as $subject) {
                if (empty($subjects_preselection[$subject->id])) {
                    unset($subjects[$subject->id]);
                }
            }
        }

        // check delete
        foreach ($subjects as $subject) {
            // filter subjects by source. Other levels will be from needed subject trees
            // before it was a simpossibble to delete subject/topic/..., but unclear why.
            // now it is impossible to delete, but with the message about 'children from another source'
            if ($subject->source != $source) {
                unset($subjects[$subject->id]);
                continue;
            }
            $subject->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $subject->id, 'comptype' => BLOCK_EXACOMP_TYPE_SUBJECT));
            $subject->can_delete = ($subject->source == $source) && !$subject->gradings;
            $subject->has_another_source = false;
            $subject->has_gradings = false; // has_another_source/has_gradings means, that for a lower level e.g. topics  $topic->another_source/$topic->gradings is true
            $subject->used_in_courses = [];


            foreach ($subject->topics as $topic) {
                $topic->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $topic->id, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC));
                $topic->can_delete = ($topic->source == $source) && !$topic->gradings;
                $topic->another_source = (!($topic->source == $source));
                $topic->has_another_source = false;
                $topic->has_gradings = false;

                // find out in which courses this topic is used ==> add this info to the subject to make a warning
                $topic->used_in_courses = [];
                $used_in_courses = $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS, array('topicid' => $topic->id));
                foreach ($used_in_courses as $used_in_course) {
                    $topic->used_in_courses[] = $used_in_course->courseid;
                }


                foreach ($topic->descriptors as $descriptor) {
                    $descriptor->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $descriptor->id, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));
                    $descriptor->can_delete = ($descriptor->source == $source) && !$descriptor->gradings;
                    $descriptor->another_source = (!($descriptor->source == $source));
                    $descriptor->has_another_source = false;
                    $descriptor->has_gradings = false;


                    // child descriptors
                    foreach ($descriptor->children as $child_descriptor) {
                        $child_descriptor->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $child_descriptor->id, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));
                        $child_descriptor->can_delete = ($child_descriptor->source == $source) && !$child_descriptor->gradings;
                        $child_descriptor->another_source = (!($child_descriptor->source == $source));
                        $child_descriptor->has_another_source = false;
                        $child_descriptor->has_gradings = false;

                        //						//$examples = array();
                        foreach ($child_descriptor->examples as $example) {
                            $example->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('exampleid' => $example->id));
                            $example->can_delete = ($example->source == $source) && !$example->gradings;
                            $example->another_source = (!($example->source == $source));
                            if (!$example->can_delete) {
                                $child_descriptor->can_delete = false;
                            }
                            if ($example->another_source) {
                                $child_descriptor->has_another_source = true;
                            }
                            if ($example->gradings) {
                                $descriptor->has_gradings = true;
                            }
                            //if ($example->source != $source) {
                            //	unset($child_descriptor->examples[$example->id]);
                            //}
                        }
                        //$child_descriptor->examples = $examples; // RW 2022.01.04 this would just set the examples of every child descriptor to an empty array... why did we have this line??

                        if (!$child_descriptor->can_delete) {
                            $descriptor->can_delete = false;
                        }
                        if ($child_descriptor->another_source || $child_descriptor->has_another_source) {
                            $descriptor->has_another_source = true;
                        }
                        if ($child_descriptor->gradings || $child_descriptor->has_gradings) {
                            $descriptor->has_gradings = true;
                        }
                        //if ($child_descriptor->source != $source && empty($child_descriptor->examples)) {
                        //	unset($descriptor->children[$child_descriptor->id]);
                        //}
                    }

                    foreach ($descriptor->examples as $example) {
                        $example->gradings = $DB->record_exists(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('exampleid' => $example->id));
                        $example->can_delete = ($example->source == $source) && !$example->gradings;
                        $example->another_source = (!($example->source == $source));
                        if (!$example->can_delete) {
                            $descriptor->can_delete = false;
                        }
                        if ($example->another_source) {
                            $descriptor->has_another_source = true;
                        }
                        if ($example->gradings) {
                            $descriptor->has_gradings = true;
                        }
                        //if ($example->source != $source) {
                        //	unset($descriptor->examples[$example->id]);
                        //}
                        //if ($descriptor->source == $source || !empty($descriptor->examples)) {
                        //	unset($descriptor->children[$descriptor->id]);
                        //}
                    }

                    if (!$descriptor->can_delete) {
                        $topic->can_delete = false;
                    }
                    if ($descriptor->another_source || $descriptor->has_another_source) {
                        $topic->has_another_source = true;
                    }
                    if ($descriptor->gradings || $descriptor->has_gradings) {
                        $topic->has_gradings = true;
                    }
                    //if ($descriptor->source != $source && empty($descriptor->examples)) {
                    //	unset($topic->descriptors[$descriptor->id]);
                    //}
                }

                if (!$topic->can_delete) {
                    $subject->can_delete = false;
                }
                if ($topic->another_source || $topic->has_another_source) {
                    $subject->has_another_source = true;
                }
                if ($topic->gradings || $topic->has_gradings) {
                    $subject->has_gradings = true;
                }

                if ($topic->used_in_courses) {
                    $subject->used_in_courses = array_unique(array_merge($topic->used_in_courses, $subject->used_in_courses));
                }
                //if ($topic->source != $source && empty($topic->descriptors)) {
                //	unset($subject->topics[$topic->id]);
                //}
            }

            //			if ($subject->source != $source && empty($subject->topics)) {
            //				unset($subjects[$subject->id]);
            //			}
        }
        return $subjects;
    }

    /**
     * @param $source
     * @return subject[]
     * Showing all the subjects of a source can become a huge task on large systems ==> Show only the subject, but not the topics etc in the first step
     */
    function get_subjects_preselection_for_source($source) {
        $subjects = $this->get_subjects();
        // $subjects = array_values($subjects);
        // $subjects = array($subjects[10]); // , $subjects[1]);

        // could do this with an sql statement instead of loop
        foreach ($subjects as $subject) {
            if ($subject->source != $source) {
                unset($subjects[$subject->id]);
            }
        }
        return $subjects;
    }
}

class db_layer_course extends db_layer {
    public $courseid = 0;
    public $userid = 0;
    public $showalldescriptors = false;
    public $showallexamples = true;
    public $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES);
    public $showonlyvisible = false;
    public $mindvisibility = true;

    function __construct($courseid, $userid = null) {
        $this->courseid = $courseid;
        $this->userid = $userid ?: g::$USER->id;

        if (!block_exacomp_is_teacher($courseid, $this->userid)) {
            $this->showonlyvisible = true;
        }

        $this->showalldescriptors = /* $this->showalldescriptors || */
            block_exacomp_get_settings_by_course($this->courseid)->show_all_descriptors;
    }

    /**
     * @return subject[]
     */
    function get_subjects() {
        return subject::create_objects(block_exacomp_get_subjects_by_course($this->courseid, $this->showalldescriptors), null, $this);
    }

    function get_subject($subjectid) {
        $subjects = $this->get_subjects();

        return isset($subjects[$subjectid]) ? $subjects[$subjectid] : null;
    }

    function filter_user_visibility($items) {
        if (!$this->showonlyvisible) {
            return $items;
        }

        foreach ($items as $key => $item) {
            if ($item instanceof topic) {
                if (!block_exacomp_is_topic_visible($this->courseid, $item, $this->userid)) {
                    unset($items[$key]);
                }
            }
            if ($item instanceof descriptor) {
                if (!block_exacomp_is_descriptor_visible($this->courseid, $item, $this->userid)) {
                    unset($items[$key]);
                }
            }
            if ($item instanceof example) {
                if (!block_exacomp_is_example_visible($this->courseid, $item, $this->userid)) {
                    unset($items[$key]);
                }
            }
        }

        return $items;
    }

    function get_topics_for_subject(subject $subject) {
        $items = topic::create_objects(block_exacomp_get_topics_by_subject($this->courseid, $subject->id, false, $this->showonlyvisible), null, $this);

        return $this->filter_user_visibility($items);
    }

    function get_descriptors_for_topic(topic $topic) {
        $items = parent::get_descriptors_for_topic($topic);

        return $this->filter_user_visibility($items);

    }

    function get_child_descriptors(descriptor $parent) {
        $items = parent::get_child_descriptors($parent);

        return $this->filter_user_visibility($items);
    }

    function get_examples(descriptor $descriptor) {
        $items = parent::get_examples($descriptor);

        return $this->filter_user_visibility($items);
    }
}

class db_layer_student extends db_layer_course {
    public $showonlyvisible = true;
}

class db_layer_all_user_courses extends db_layer_student {

    var $userid;

    function __construct($userid) {
        $this->userid = $userid;
    }

    function get_subjects() {
        $user_courses = block_exacomp_get_exacomp_courses($this->userid);
        $subjects = array();

        foreach ($user_courses as $course) {
            $courseSubjects = db_layer_course::create($course->id)->get_subjects();

            foreach ($courseSubjects as $courseSubject) {
                if (!isset($subjects[$courseSubject->id])) {
                    $subjects[$courseSubject->id] = $courseSubject;
                }

                foreach ($courseSubject->topics as $topic) {
                    if (!isset($subjects[$courseSubject->id]->topics[$topic->id])) {
                        $subjects[$courseSubject->id]->topics[$topic->id] = $topic;
                    }

                    foreach ($topic->descriptors as $descriptor) {
                        if (!isset($subjects[$courseSubject->id]->topics[$topic->id]->descriptors[$descriptor->id])) {
                            $subjects[$courseSubject->id]->topics[$topic->id]->descriptors[$descriptor->id] = $descriptor;
                        }
                    }
                }
            }
        }

        return $subjects;
        // subject::create_objects(block_exacomp_get_subjects_by_course(2), null, $this);
    }
}

/**
 * /**
 * Class db_record
 *
 * @package block_exacomp
 * @property int $id
 */
class db_record {
    /**
     * @var db_layer
     */
    protected $dbLayer = null;

    public $id;

    const TABLE = 'unknown_table';
    const TYPE = '';
    /**
     * null = not set => error
     * false = not set = no subs
     * string = name of subs
     */
    const SUBS = null;

    public function __construct($data = [], db_layer $dbLayer = null) {
        if ($dbLayer) {
            $this->setDbLayer($dbLayer);
        } else {
            $this->setDbLayer(db_layer::get());
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->init();

        /*
        global $xcounts;
        $xcounts[get_called_class()."_cnt"]++;
        $xcounts[get_called_class()][$data->id]++;
        */
        // $this->debug = print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true)."\n".print_r(array_keys((array)$data), true);
    }

    public function init() {
    }

    public function get_data() {
        $data = (object)[];
        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $data->{$prop->getName()} = $prop->getValue($this);
        }

        return $data;
    }

    public function toArray() {
        return (array)$this->get_data();
    }

    public function &__get($name) {
        static::check_property_name($name);

        if (($method = 'get_' . $name) && method_exists($this, $method)) {
            @$ret =& $this->$method();

            // check if __get is recursively called at the same property
            /*
            if (property_exists($this, $name)) {
                // the property exists now -> error
                throw new \coding_exception("property '$name' set on object!");
            }
            */

            return $ret;
        } else if (($method = 'fill_' . $name) && method_exists($this, $method)) {
            $this->$name = $this->$method();

            // check if __get is recursively called at the same property
            /*
            if (property_exists($this, $name)) {
                // the property exists now -> error
                throw new \coding_exception("property '$name' set on object!");
            }
            */

            return $this->$name;
        } else {
            throw new \coding_exception("property not found " . get_class($this) . "::$name");
        }
    }

    public function __isset($name) {
        static::check_property_name($name);

        // TODO: wird das noch benötigt?
        //if (($method = 'get_'.$name) && method_exists($this, $method)) {
        //	return true; // $this->__get($name) !== null;
        if (($method = 'fill_' . $name) && method_exists($this, $method)) {
            return true; // $this->__get($name) !== null;
        } else {
            return false;
        }

        // return isset($this->$name);
    }

    public function __set($name, $value) {
        static::check_property_name($name);

        if (($method = 'set_' . $name) && method_exists($this, $method)) {
            $this->$method($value);

            // check if __set is recursively called at the same property
            /*
            if (property_exists($this, $name)) {
                // the property exists now -> error
                print_error('property set on object!');
            }
            */

        } else {
            if (method_exists($this, 'get_' . $method)) {
                throw new \coding_exception("set '$name' not allowed, because there is a get_$name function! ");
            }

            $this->$name = $value;
        }
    }

    public function __unset($name) {
        static::check_property_name($name);

        unset($this->$name);
    }

    protected function check_property_name($name) {
        if ($name[0] == '_') {
            throw new \coding_exception('wrong property name ' . $name);
        }
    }

    public function setDbLayer(db_layer $dbLayer) {
        $this->dbLayer = $dbLayer;
    }

    // delete this node and all subnodes
    public function insert() {
        return $this->insert_record();
    }

    // just delete the record
    public function insert_record() {
        global $DB;

        return $this->id = $DB->insert_record(static::TABLE, $this->get_data());
    }

    public function update($data = null) {
        return $this->update_record($data);
    }

    // just update the record
    public function update_record($data = null) {
        global $DB;

        if (!isset($this->id)) {
            throw new moodle_exception('id not set');
        }

        if ($data === null) {
            die('TODO: testing');
            // update all my data
            // return $DB->update_record(static::TABLE, $this);
        }

        $data = (array)$data;
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $data['id'] = $this->id;

        return $DB->update_record(static::TABLE, $data);
    }

    // delete this node and all subnodes
    public function delete() {
        return $this->delete_record();
    }

    // just delete the record
    public function delete_record() {
        global $DB;

        if (!isset($this->id)) {
            throw new moodle_exception('id not set');
        }

        return $DB->delete_records(static::TABLE, array('id' => $this->id));
    }

    public function &get_subs() {
        if (static::SUBS === false) {
            $subs = [];

            return $subs;
        }
        if (!static::SUBS) {
            throw new \coding_exception('const SUBS not set');
        }
        $tmp =& $this->{static::SUBS};

        return $tmp;
    }

    public function set_subs($value) {
        if (!static::SUBS) {
            throw new \coding_exception('const SUBS not set');
        }
        $this->{static::SUBS} = $value;
    }

    public function has_capability($cap) {
        return block_exacomp_has_item_capability($cap, $this);
    }

    public function require_capability($cap) {
        return block_exacomp_require_item_capability($cap, $this);
    }

    /**
     * @param mixed $conditions can be an
     *            * (string,int)id OR (object,array)conditions, to load that record from the database
     *            * OR db_record, which would just be returned
     * @param null $fields
     * @param null $strictness
     * @return static
     * @throws \coding_exception
     */
    static function get($conditions, $fields = null, $strictness = null) {
        if ($conditions === null) {
            return null;
        } else if (is_scalar($conditions)) {
            if (!$conditions) {
                return null;
            }

            // id
            $conditions = array('id' => $conditions);
        } else if (is_object($conditions)) {
            if ($conditions instanceof static) {
                // if db_record object is passed, just return it
                // no loading from db needed
                return $conditions;
            } else if ($conditions instanceof \stdClass) {
                $conditions = (array)$conditions;
                if (!$conditions) {
                    print_error('wrong fields');
                }
            } else {
                throw new \coding_exception('Wrong class for $conditions expected "' . get_called_class() . '" got "' . get_class($conditions) . '"');
            }
        } else if (is_array($conditions)) {
            // ok
        } else {
            print_error('wrong fields');
        }

        $data = static::get_record($conditions, $fields, $strictness);

        if (!$data) {
            return null;
        }

        return static::create($data);
    }

    /**
     * @param $o
     * @return static
     */
    static function to_object($o) {
        if (is_object($o)) {
            if ($o instanceof static) {
                return $o;
            } else {
                return static::create($o);
            }
        } else if (is_scalar($o)) {
            // it's id
            return static::get($o);
        } else {
            throw new moodle_exception('wrong parameter');
        }
    }

    static function get_record(array $conditions, $fields = null, $strictness = null) {
        global $DB;

        // allow to just pass strictness
        if ($strictness === null && in_array($fields, array(IGNORE_MISSING, IGNORE_MULTIPLE, MUST_EXIST), true)) {
            $strictness = $fields;
            $fields = null;
        }
        if ($fields === null) {
            $fields = '*';
        }
        if ($strictness === null) {
            $strictness = IGNORE_MISSING;
        }

        return $DB->get_record(static::TABLE, $conditions, $fields, $strictness);
    }

    /**
     * @param array|null $conditions
     * @param string $sort
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     * @return static[]
     */
    static function get_objects(array $conditions = null, $sort = '', $fields = '*', $limitfrom = 0, $limitnum = 0) {
        return static::create_objects(static::get_records($conditions, $sort, $fields, $limitfrom, $limitnum));
    }

    static function get_records(array $conditions = null, $sort = '', $fields = '*', $limitfrom = 0, $limitnum = 0) {
        return g::$DB->get_records(static::TABLE, $conditions, $sort, $fields, $limitfrom, $limitnum);
    }

    /**
     * @param $sql
     * @param array|null $params
     * @param int $limitfrom
     * @param int $limitnum
     * @return static[]
     */
    static function get_objects_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0) {
        return static::create_objects(static::get_records_sql($sql, $params, $limitfrom, $limitnum));
    }

    static function get_records_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0) {
        return g::$DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    static function create_objects($records, $data = [], db_layer $dbLayer = null) {
        if (!$dbLayer) {
            $dbLayer = db_layer::get();
        }

        return $dbLayer->create_objects(get_called_class(), $records, $data);
    }

    static function create($data = [], db_layer $dbLayer = null) {
        if ($data instanceof static) {
            if ($dbLayer) {
                $data->setDbLayer($dbLayer);
            }

            return $data;
        }

        return new static($data, $dbLayer);
    }
}

/**
 * Class subject
 *
 * @property topic[] $topics
 */
class subject extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_SUBJECTS;
    const TYPE = BLOCK_EXACOMP_TYPE_SUBJECT;
    const SUBS = 'topics';

    protected function fill_topics() {
        return $this->dbLayer->get_topics_for_subject($this);
    }

    /**
     * maybe there is a special implementation
     *
     * @return string
     */
    function get_author() {
        return $this->author;
    }

    function get_editor() {
        return $this->editor;
    }

    function get_numbering() {
        return '';
    }

    /**
     * @return niveau[] this is not the whole niveau record, but only some attributes of it!
     */
    function get_used_niveaus(): array {
        $used_niveaus = [];

        foreach ($this->subs as $topic) {
            foreach ($topic->subs as $descriptor) {
                $used_niveaus[$descriptor->niveauid] = (object)["id" => $descriptor->niveauid, "title" => $descriptor->niveau_title, "numb" => $descriptor->niveau_numb, "sorting" => $descriptor->niveau_sorting];
            }
        }

        block_exacomp_sort_items($used_niveaus, BLOCK_EXACOMP_DB_NIVEAUS);

        return $used_niveaus;
    }
}

/**
 * Class topic
 *
 * @property descriptor[] $descriptors
 */
class topic extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_TOPICS;
    const TYPE = BLOCK_EXACOMP_TYPE_TOPIC;
    const SUBS = 'descriptors';

    // why not using lib.php block_exacomp_get_topic_numbering??
    // because it is faster this way (especially for export etc where whole competence tree is read)
    function get_numbering() {
        return block_exacomp_get_topic_numbering($this);
        /*
        if (!isset($this->subject)) {
            echo 'no subject!';
            var_dump($this);
            print_r($this->debug);
            die('subj');
        }

        if ($this->subject->titleshort) {
            $numbering = $this->subject->titleshort.'.';
        } else {
            $numbering = $this->subject->title[0].'.';
        }

        //topic
        $numbering .= $this->numb.'.';

        return $numbering;
        */
    }

    protected function fill_descriptors() {
        return $this->dbLayer->get_descriptors_for_topic($this);
    }

    function get_subject() {
        if (isset($this->subject)) {
            return $this->subject;
        } else {
            return \block_exacomp\subject::get($this->subjid);
        }
    }

    function get_author() {
        return $this->author;
    }

    function get_editor() {
        return $this->editor;
    }

    /**
     * @return niveau[] this is not the whole niveau record, but only some attributes of it!
     */
    function get_used_niveaus(): array {
        $used_niveaus = [];

        foreach ($this->subs as $descriptor) {
            $used_niveaus[$descriptor->niveauid] = (object)["id" => $descriptor->niveauid, "title" => $descriptor->niveau_title, "numb" => $descriptor->niveau_numb, "sorting" => $descriptor->niveau_sorting];
        }

        block_exacomp_sort_items($used_niveaus, BLOCK_EXACOMP_DB_NIVEAUS);

        return $used_niveaus;
    }
}

/**
 * @property example[] $examples
 */
class descriptor extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_DESCRIPTORS;
    const TYPE = BLOCK_EXACOMP_TYPE_DESCRIPTOR;
    const SUBS = 'children';

    var $parent;
    var $topicid;

    function init() {
        if (!isset($this->parent)) {
            $this->parent = null;
        }
    }

    function get_numbering($reloadTopic = false) {
        return block_exacomp_get_descriptor_numbering($this, $reloadTopic);
    }

    function get_niveau() {
        return \block_exacomp\niveau::get($this->niveauid);
    }

    function get_detailedtype() {
        return $this->parentid ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
    }

    static function insertInCourse($courseid, $data) {
        global $DB, $USER;

        $descriptor = static::create($data);
        $parent_descriptor = isset($descriptor->parentid) ? descriptor::get($descriptor->parentid) : null;
        $topic = isset($descriptor->topicid) ? topic::get($descriptor->topicid) : null;

        $topicid = null;
        if ($parent_descriptor) {
            $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $parent_descriptor->id));
            $topicid = $descriptor_topic_mm->topicid;

            $parent_descriptor->topicid = $topicid;
            $siblings = block_exacomp_get_child_descriptors($parent_descriptor, $courseid);
        } else if ($topic) {
            $topicid = $topic->id;
            $descriptor->parentid = 0;

            // TODO
            $siblings = block_exacomp_get_descriptors_by_topic($courseid, $topicid);
        } else {
            throw new moodle_exception('parentid or topicid not submitted');
        }

        // get $max_sorting
        $max_sorting = $siblings ? max(array_map(function($x) {
            return $x->sorting;
        }, $siblings)) : 0;

        $descriptor->source = BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR;
        $descriptor->sorting = $max_sorting + 1;
        $descriptor->creatorid = g::$USER->id;
        $descriptor->author = fullname($USER);
        $descriptor->editor = fullname($USER);
        $descriptor->insert();
        //topic association
        $childdesctopic_mm = new \stdClass();
        $childdesctopic_mm->topicid = $topicid;
        $childdesctopic_mm->descrid = $descriptor->id;

        $DB->insert_record(BLOCK_EXACOMP_DB_DESCTOPICS, $childdesctopic_mm);

        // other courses
        $otherCourseids = block_exacomp_get_courseids_by_descriptor($descriptor->id);

        // add myself (should be in there anyway)
        if (!in_array($courseid, $otherCourseids)) {
            $otherCourseids[] = $courseid;
        }

        foreach ($otherCourseids as $otherCourseid) {
            $visibility = new \stdClass();
            $visibility->courseid = $otherCourseid;
            $visibility->descrid = $descriptor->id;
            $visibility->studentid = 0;
            $visibility->visible = 1;

            $DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $visibility);
        }

        return $descriptor;
    }

    function store_categories($categories) {
        global $DB;

        // read current
        $to_delete = $current = $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCCAT, array('descrid' => $this->id), null, 'catid, id');

        // add new ones
        if (is_array($categories)) {
            foreach ($categories as $id) {
                if (!isset($current[$id])) {
                    $DB->insert_record(BLOCK_EXACOMP_DB_DESCCAT, array('descrid' => $this->id, 'catid' => $id));
                } else {
                    unset($to_delete[$id]);
                }
            }
        }

        // delete old ones
        $DB->delete_records_list(BLOCK_EXACOMP_DB_DESCCAT, 'id', $to_delete);
    }

    protected function fill_category_ids() {
        global $DB;

        return $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCCAT, array('descrid' => $this->id), null, 'catid, catid AS tmp');
    }

    protected function fill_children() {
        if ($this->parentid) {
            // already is child
            return [];
        } else {
            return $this->dbLayer->get_child_descriptors($this);
        }
    }

    protected function fill_examples() {
        return $this->dbLayer->get_examples($this);
    }

    protected function fill_categories() {
        return block_exacomp_get_categories_for_descriptor($this);
    }

    function get_author() {
        return $this->author;
    }

    function get_editor() {
        return $this->editor;
    }
}

class example extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_EXAMPLES;
    const TYPE = BLOCK_EXACOMP_TYPE_EXAMPLE;
    const SUBS = false;

    function get_numbering() {
        if (!isset($this->descriptor)) {
            return null;
        }

        //return $this->descriptor->get_numbering();
        return block_exacomp_get_descriptor_numbering($this->descriptor);
    }

    function get_author() {
        if ($this->get_author_origin()) {
            return $this->get_author_origin();
        }
        if ($this->creatorid && $user = g::$DB->get_record('user', ['id' => $this->creatorid])) {
            return fullname($user);
        } else {
            return $this->author;
        }
    }

    function get_editor() {
        return $this->editor;
    }

    function get_author_origin() {
        return $this->author_origin;
    }

    function get_task_file_url($position = 0) {
        // get from filestorage
        $file = block_exacomp_get_file($this, 'example_task');
        if (!$file) {
            return null;
        }

        $filename = (($numbering = $this->get_numbering()) ? $numbering . '_' : '') .
            $this->title .
            '_' . trans(['de:Aufgabe', 'en:Task']) .
            '.' . preg_replace('!^.*\.!', '', $file->get_filename());

        $url = \moodle_url::make_pluginfile_url(block_exacomp_get_context_from_courseid(g::$COURSE->id)->id, $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $filename);
        $url->param('position', $position);
        return $url;
    }

    function get_solution_file_url() {
        // get from filestorage
        $file = block_exacomp_get_file($this, 'example_solution');
        if (!$file) {
            return null;
        }

        $filename = (($numbering = $this->get_numbering()) ? $numbering . '_' : '') .
            $this->title .
            '_' . trans(['de:Lösung', 'en:Solution']) .
            '.' . preg_replace('!^.*\.!', '', $file->get_filename());

        return \moodle_url::make_pluginfile_url(block_exacomp_get_context_from_courseid(g::$COURSE->id)->id, $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $filename);
    }

    function get_completefile_file_url() {
        // get from filestorage
        $file = block_exacomp_get_file($this, 'example_completefile');
        if (!$file) {
            return null;
        }

        $filename = (($numbering = $this->get_numbering()) ? $numbering . '_' : '') .
            $this->title .
            '_' . trans(['de:Gesamtbeispiel', 'en:Complete file']) .
            '.' . preg_replace('!^.*\.!', '', $file->get_filename());

        return \moodle_url::make_pluginfile_url(block_exacomp_get_context_from_courseid(g::$COURSE->id)->id, $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $filename);
    }
}

class niveau extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_NIVEAUS;

    function get_subtitle($subjectid) {
        return g::$DB->get_field(BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM, 'subtitle', ['subjectid' => $subjectid, 'niveauid' => $this->id]); // none for now
    }
}

class cross_subject extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_CROSSSUBJECTS;
    const TYPE = BLOCK_EXACOMP_TYPE_CROSSSUB;
    const SUBS = false;

    function is_draft() {
        return !$this->courseid;
    }

    function is_shared() {
        if ($this->is_draft()) {
            return false;
        }
        if ($this->shared) {
            return true;
        }

        return g::$DB->record_exists(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $this->id));
    }
}

class global_config {

    /**
     * Returns all values used for examples and child-descriptors
     *
     * @param integer $courseid
     * @param bool $short
     * @param integer $scheme
     * @return array
     */
    static function get_teacher_eval_items($courseid = 0, $short = false, $scheme = null) {
        return Cache::staticCallback([__CLASS__, __FUNCTION__], function($courseid = 0, $short = false, $scheme = null) {

            $result = array();
            if (!$scheme) {
                $scheme = block_exacomp_get_assessment_subject_scheme($courseid);
            }
            switch ($scheme) {
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                    // Options from plugin settings: 0, 1... -> assessment_points_limit.
                    $result = array('-1' => '') + range(0, block_exacomp_get_assessment_points_limit(null, $courseid));
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                    $result = array(-1 => '');
                    // Options from plugin settings: assessment_grade_verbose.
                    if ($short) {
                        $options = array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options_short(null, $courseid)));
                    } else {
                        $options = array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options(null, $courseid)));
                    }
                    //$options = array_reverse($options);
                    $result = $result + $options;
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                    // Options from plugin settings: 0, 1... ->  assessment_grade_limit.
                    $result = array('0' => '') + range(0, block_exacomp_get_assessment_grade_limit($courseid));
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                    // Options from plugin settings: assessment_grade_limit.
                    $result = range(0, 1);
                    break;
                default:
                    // Old code!
                    // if additional_grading is set, use global value scheme
                    if (block_exacomp_additional_grading(null, $courseid)) {
                        if ($short) {
                            return array(
                                -1 => block_exacomp_get_string('comp_-1_short'),
                                0 => block_exacomp_get_string('comp_0_short'),
                                1 => block_exacomp_get_string('comp_1_short'),
                                2 => block_exacomp_get_string('comp_2_short'),
                                3 => block_exacomp_get_string('comp_3_short'),
                            );
                        }

                        return array(
                            -1 => block_exacomp_get_string('comp_-1'),
                            0 => block_exacomp_get_string('comp_0'),
                            1 => block_exacomp_get_string('comp_1'),
                            2 => block_exacomp_get_string('comp_2'),
                            3 => block_exacomp_get_string('comp_3'),
                        );
                    } // else use value scheme set in the course (old ?)
                    else {
                        // TODO: add settings to g::$COURSE?
                        $course_grading = block_exacomp_get_settings_by_course(($courseid == 0) ? g::$COURSE->id : $courseid)->grading;

                        $values = array(-1 => ' ');
                        $values += range(0, $course_grading);

                        return $values;
                    }
            }
            return $result;

        }, func_get_args());
    }

    /**
     * Returns title for one value
     *
     * @param id $id
     */
    static function get_teacher_eval_title_by_id($id) {
        if ($id === null || $id < 0) {
            return ' ';
        }

        return @static::get_teacher_eval_items()[$id];
    }

    /**
     * Returns all values used for examples and child-descriptors
     *
     * @param bool $include_empty
     * @param integer $level
     * @param bool $short
     * @return array
     */
    static function get_student_eval_items($include_empty = false, $level = BLOCK_EXACOMP_TYPE_SUBJECT, $short = false, $courseid = 0) {
        return Cache::staticCallback([__CLASS__, __FUNCTION__, func_get_args()], function() use ($include_empty, $level, $short, $courseid) {
            // if additional_grading is set, use global value scheme

            if ($include_empty) {
                $values = [0 => ''];
            } else {
                $values = [];
            }
            //if (block_exacomp_additional_grading($scheme)) {  // TODO !!!! only subject now !!!!
            /*
                3 => '😊',
                2 => '😔',
                1 => '😓',
            */

            $useEval = get_config('exacomp', 'assessment_SelfEval_useVerbose');
            $target = 'subject'; // For default.
            if ($useEval) {
                // different for different levels
                // use integer and string variants
                switch (true) { // strange switch because $level can have different types
                    case $level === 'crosssubs':
                    case $level === BLOCK_EXACOMP_TYPE_CROSSSUB:
                    case $level === BLOCK_EXACOMP_TYPE_CROSSSUB . '': // also it is possible as string id
                    case $level === 'subjects':
                    case $level === BLOCK_EXACOMP_TYPE_SUBJECT:
                    case $level === BLOCK_EXACOMP_TYPE_SUBJECT . '':
                    case $level === 'topics':
                    case $level === BLOCK_EXACOMP_TYPE_TOPIC:
                    case $level === BLOCK_EXACOMP_TYPE_TOPIC . '':
                    case $level === 'competencies':
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR:
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR . '':
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT:
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT . '':
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD:
                    case $level === BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD . '':
                        $target = 'comp';
                        /*return $values + [
                                    4 => block_exacomp_get_string('selfEvalVerbose.4'), //not generic yet because not requested from customer
                                    3 => block_exacomp_get_string('selfEvalVerbose.3'),
                                    2 => block_exacomp_get_string('selfEvalVerbose.2'),
                                    1 => block_exacomp_get_string('selfEvalVerbose.1'),
                            ];*/
                        break;
                    case $level === 'examples':
                    case $level === BLOCK_EXACOMP_TYPE_EXAMPLE:
                    case $level === BLOCK_EXACOMP_TYPE_EXAMPLE . '':
                        $target = 'example';
                        /*return $values + [
                                        3 => block_exacomp_get_string('selfEvalVerboseExample.3'), //not generic yet because not requested from customer
                                        2 => block_exacomp_get_string('selfEvalVerboseExample.2'),
                                        1 => block_exacomp_get_string('selfEvalVerboseExample.1'),
                                ];*/
                        break;
                }
                if ($short) {
                    $paramtype = 'short';
                } else {
                    $paramtype = 'long';
                }
                $verbosesstring = block_exacomp_get_assessment_selfEval_verboses($target, $paramtype, null, $courseid);
                if (!$verbosesstring) { // If no any value in the settings.
                    $verbosesstring = block_exacomp_get_string('selfEvalVerbose' . ($target == 'example' ? 'Example' : '') . '.defaultValue_' . $paramtype);
                }
                $result = $values;
                $valuesadd = array_map('trim', explode(';', $verbosesstring));
                $i = 1;
                foreach ($valuesadd as $add) {
                    $result[$i] = $add;
                    $i++;
                }
                return $result;
            } else {
                return $values + [
                        3 => ':-)', //not generic yet because not requested from customer
                        2 => ':-|',
                        1 => ':-(',
                    ];
            }
            //} // else use value scheme set in the course
            // now only emojis ?
            /* else {
                 // TODO: add settings to g::$COURSE?
                 $course_grading = block_exacomp_get_settings_by_course(g::$COURSE->id)->grading;

                 return $values + range(1, $course_grading);
             }*/
        });
    }

    /**
     * Returns title for one value
     *
     * @param id $id
     */
    static function get_student_eval_title_by_id($id, $type = BLOCK_EXACOMP_TYPE_SUBJECT, $courseid = 0) {
        if ($id === null || $id < 0) {
            return ' ';
        }
        return @static::get_student_eval_items(false, $type, null, $courseid)[$id];
    }

    /**
     * Returns all evaluation niveaus, specified by the admin
     */
    static function get_evalniveaus($include_empty = false, $courseid = 0) {
        //		static $values;
        //
        //		if ($values === null) {
        //			$values = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU, null, '', 'id,title');
        //		}
        //
        //		$ret = $values;
        //		if ($include_empty) {
        //			$ret = [0 => ''] + $ret;
        //		}
        // TODO: why would we use this table? We have the same information in the config_plugins table... 2021_07_21 RW

        // Instead: use this way, just like for all the other admin settings
        $ret = block_exacomp_get_assessment_diffLevel_options_splitted($courseid);
        if ($include_empty) {
            $ret = [0 => ''] + $ret;
        }

        return $ret;
    }

    /**
     * Returns all diffLevel_options, specified by the admin
     * deprecated ?
     */
    /*	static function get_diffLevel_options($include_empty = false) {
            static $values;

            if ($values === null) {
                $values = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU,  array('option_type' => 'diffLevel_options'), '', 'id,title');
            }

            $ret = $values;
            if ($include_empty) {
                $ret = [0 => ''] + $ret;
            }

            return $ret;
        }*/

    /**
     * Returns all evaluation verbose_options, specified by the admin
     * deprecated ???  or at least not used as planned
     * the verbose options are saved in mdl_config_plugins as a string, instead of in the mdl_block_exacompeval_niveau table
     */
    /*	static function get_verbose_options($include_empty = false) {
            static $values;
            if ($values === null) {
                $values = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU, array('option_type' => 'verbose_options'), '', 'id,title');
            }

            $ret = $values;
            if ($include_empty) {
                $ret = [0 => ''] + $ret;
            }

            return $ret;
        }*/

    /**
     * Returns title for one evaluation niveau
     *
     * @param id $id
     */
    static function get_evalniveau_title_by_id($id) {
        return @static::get_evalniveaus()[$id];
    }

    // 	/**
    // 	 * Maps gradings (1.0 - 6.0) to 0-3 values
    // 	 *
    // 	 * @param double $additionalinfo
    // 	 */ deprecated     was used to map to verbose
    // 	static function get_additionalinfo_value_mapping($additionalinfo) {
    // 		if (!$additionalinfo) {
    // 			return -1;
    // 		}

    // 		$mapping = array(6.0, 4.8, 3.5, 2.2);
    // 		$value = -1;

    // 		foreach ($mapping as $k => $v) {
    // 			if ($additionalinfo > $v) {
    // 				break;
    // 			}
    // 			$value = $k;
    // 		}

    // 		return $value;
    // 	}

    /**
     * Maps float gradings to int gradings
     *
     * @param double $additionalinfo
     */
    static function get_additionalinfo_value_mapping($additionalinfo) {
        if (!$additionalinfo) {
            return -1;
        }

        $value = round($additionalinfo);

        return $value;
    }

    /**
     * Maps 0-3 values to gradings (1.0 - 6.0)
     *
     * @param int $value
     */
    static function get_value_additionalinfo_mapping($value) {
        if (!$value) {
            return -1;
        }

        $mapping = array(6.0, 4.4, 2.7, 1.0);

        return $mapping[$value];
    }

    /**
     * return range of gradings to value mapping
     *
     * @param int $value
     */
    static function get_values_additionalinfo_mapping() {
        return array(6.0, 4.4, 2.7, 1.0);
    }

    static function get_allowed_inputs($detailedcomptype) {
        $inputs = [
            BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION => false,
            BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION => false,
            BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO => false,
            BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID => false,
        ];
        if (block_exacomp_get_assessment_diffLevel($detailedcomptype)) {
            $inputs[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID] = true;
        }
        if (block_exacomp_get_assessment_SelfEval($detailedcomptype)) {
            $inputs[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION] = true;
        }
        if (block_exacomp_additional_grading($detailedcomptype)) {
            $inputs[BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO] = true;
            $inputs[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION] = true;

        }

        return $inputs;
    }

    static function is_input_allowed($detailedcomptype, $input) {
        return !empty(static::get_allowed_inputs($detailedcomptype)[$input]);
    }
}

class comp_eval extends db_record {
    const TABLE = BLOCK_EXACOMP_DB_COMPETENCES;

    public $id;
    public $courseid;
    public $userid;
    public $comptype;
    public $compid;

    public $value;
    public $role;
    public $reviewerid;
    public $evalniveauid;
    public $additionalinfo;
    public $timestamp;
    public $globalgradings;

    function get_value_title() {
        if ($this->role == BLOCK_EXACOMP_ROLE_STUDENT) {
            return global_config::get_student_eval_title_by_id($this->value, $this->comptype);
        } else if ($this->role == BLOCK_EXACOMP_ROLE_TEACHER) {
            if ($this->comptype == BLOCK_EXACOMP_TYPE_EXAMPLE || $this->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                return global_config::get_teacher_eval_title_by_id($this->value);
            }
        }

        return null;
    }

    function get_evalniveau_title() {
        return global_config::get_evalniveau_title_by_id($this->evalniveauid);
    }
}

class comp_eval_merged {
    public $teacherevalid;
    public $studentevalid;
    public $courseid;
    public $userid;
    public $comptype;
    public $compid;
    public $teacherevaluation;
    public $studentevaluation;
    public $additionalinfo;
    public $evalniveauid;
    public $teacherreviewerid;
    public $timestampteacher;
    public $timestampstudent;

    private $detailed_comptype;

    function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $courseid
     * @param $studentid
     * @param db_record $item
     * @return static
     */
    static function get($courseid, $studentid, $item) {
        $compid = $item->id;
        $comptype = $item::TYPE;

        $student_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $comptype, $compid);
        $teacher_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $comptype, $compid);

        // always return an eval, even though none is entered
        return new static([
            'teacherevalid' => @$teacher_eval->id,
            'studentevalid' => @$student_eval->id,
            'courseid' => $courseid,
            'userid' => $studentid,
            'comptype' => $comptype,
            'compid' => $compid,

            'teacherevaluation' => @$teacher_eval->value,
            'studentevaluation' => @$student_eval->value,
            'additionalinfo' => @$teacher_eval->additionalinfo,
            'evalniveauid' => @$teacher_eval->evalniveauid,
            'teacherreviewerid' => @$teacher_eval->reviewerid,
            'timestampteacher' => @$teacher_eval->timestamp,
            'timestampstudent' => @$student_eval->timestamp,
        ]);
    }

    function get_detailed_comptype() {
        if (!$this->detailed_comptype) {
            if ($this->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                $descriptor = \block_exacomp\descriptor::get($this->compid);
                if ($descriptor) {
                    $this->detailed_comptype = $descriptor->get_detailedtype();
                }
            } else {
                $this->detailed_comptype = $this->comptype;
            }
        }

        return $this->detailed_comptype;
    }

    function get_teacher_value_title() {
        if (\block_exacomp\global_config::is_input_allowed($this->get_detailed_comptype(), BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION)) {
            return global_config::get_teacher_eval_title_by_id($this->teacherevaluation);
        }
    }

    function get_student_value_title($type = BLOCK_EXACOMP_TYPE_SUBJECT) {
        if (\block_exacomp\global_config::is_input_allowed($this->get_detailed_comptype(), BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION)) {
            return global_config::get_student_eval_title_by_id($this->studentevaluation, $type);
        }
    }

    function get_evalniveau_title() {
        if (\block_exacomp\global_config::is_input_allowed($this->get_detailed_comptype(), BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID)) {
            return global_config::get_evalniveau_title_by_id($this->evalniveauid);
        }
    }

    function get_student_value_pic_url() {
        // only for non Verbose self evalueation!
        if (!get_config('exacomp', 'assessment_SelfEval_useVerbose')) {
            if (\block_exacomp\global_config::is_input_allowed($this->get_detailed_comptype(), BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION)) {
                if ($this->studentevaluation > 0) {
                    return '/blocks/exacomp/pix/compprof_rating_student_' . $this->studentevaluation . '.png';
                }
            }
        }
        return null;
    }

}
