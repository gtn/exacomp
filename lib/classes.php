<?php

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

use block_exacomp\globals as g;
use \block_exacomp;

class db_layer {

	public $courseid = 0;
	public $showalldescriptors = true;
	public $showallexamples = true;
	public $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES);
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

	static function create() {
		$args = func_get_args();

		$class = get_called_class();
		$reflection = new \ReflectionClass($class);
		return $reflection->newInstanceArgs($args);
	}

	function get_descriptors_for_topic($topic) {
		$descriptors = array_filter($this->get_descriptor_records_for_subject($topic->subjid), function($descriptor) use ($topic) {
			return $descriptor->topicid == $topic->id;
		});

		$descriptors = descriptor::create_objects($descriptors, [ 'topic' => $topic ], $this);

		return $descriptors;
	}

	function get_descriptor_records_for_subject($subjectid) {
		static $subjectDescriptors = array();
		if (isset($subjectDescriptors[$subjectid])) {
			return $subjectDescriptors[$subjectid];
		}

		global $DB;

		if (!$this->courseid) {
			$this->showalldescriptors = true;
			$this->showonlyvisible = false;
			$this->mindvisibility = false;
		}
		if(!$this->showalldescriptors)
			$this->showalldescriptors = block_exacomp_get_settings_by_course($this->courseid)->show_all_descriptors;


		$sql = 'SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.source, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.profoundness, d.parentid, n.sorting niveau, dvis.visible as visible, d.sorting '
					.' FROM {'.block_exacomp::DB_TOPICS.'} t '
							.(($this->courseid>0)?' JOIN {'.block_exacomp::DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') :'')
							.' JOIN {'.block_exacomp::DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
									.' JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0 '
											.' -- left join, because courseid=0 has no descvisibility!
		LEFT JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?'
						.($this->showonlyvisible?' AND dvis.visible = 1 ':'')
						.' LEFT JOIN {'.block_exacomp::DB_NIVEAUS.'} n ON d.niveauid = n.id '
								.($this->showalldescriptors ? '' : '
			JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($this->courseid>0)?'AND a.course=?':''))
					.' ORDER BY d.sorting';

			$descriptors = $DB->get_records_sql($sql, array($this->courseid, $this->courseid, $this->courseid, $this->courseid));

		$subjectDescriptors[$subjectid] = $descriptors;

		return $descriptors;
	}

	function get_examples($descriptor) {
		$dummy = $descriptor->getData();
		block_exacomp_get_examples_for_descriptor($dummy, $this->filteredtaxonomies, $this->showallexamples, $this->courseid, false, false);

		return example::create_objects($dummy->examples, array(
			'descriptor' => $descriptor
		), $this);
	}

	function get_child_descriptors($parent) {
		global $DB;

		if (!$this->courseid) {
			$this->showalldescriptors = true;
			$this->showonlyvisible = false;
			$this->mindvisibility = false;
		}
		if(!$this->showalldescriptors)
			$this->showalldescriptors = block_exacomp_get_settings_by_course($this->courseid)->show_all_descriptors;

		$sql = 'SELECT d.id, d.title, d.niveauid, d.source, \'descriptor\' as tabletype, '.$parent->topicid.' as topicid, d.profoundness, d.parentid, '.
				($this->mindvisibility?'dvis.visible as visible, ':'').' d.sorting
			FROM {'.block_exacomp::DB_DESCRIPTORS.'} d '
						.($this->mindvisibility ? 'JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
								.($this->showonlyvisible? 'AND dvis.visible=1 ':'') : '');

		/* activity association only for parent descriptors
		 .($this->showalldescriptors ? '' : '
		 JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
		 JOIN {course_modules} a ON da.activityid=a.id '.(($this->courseid>0)?'AND a.course=?':''));
		*/
		$sql .= ' WHERE d.parentid = ?';

		$params = array();
		if($this->mindvisibility)
			$params[] = $this->courseid;

		$params[]= $parent->id;
		//$descriptors = $DB->get_records_sql($sql, ($this->showalldescriptors) ? array($parent->id) : array($this->courseid,$parent->id));
		$descriptors = $DB->get_records_sql($sql, $params);

		$descriptors = descriptor::create_objects($descriptors, array(
			'parent' => $parent,
			'topic' => $parent->topic
		), $this);

		return $descriptors;
	}

	function get_subjects() {
		return $this->assignDbLayer(subject::get_objects());
	}

	function get_subjects_for_source($source) {
		$subjects = $this->get_subjects();
		// $subjects = array_values($subjects);
		// $subjects = array($subjects[10]); // , $subjects[1]);

		// check delete
		foreach ($subjects as $subject) {
			$subject->can_delete = ($subject->source == $source);

			foreach ($subject->topics as $topic) {
				$topic->can_delete = ($topic->source == $source);

				foreach($topic->descriptors as $descriptor){
					$descriptor->can_delete = ($descriptor->source == $source);

					// child descriptors
					foreach($descriptor->children as $child_descriptor){
						$child_descriptor->can_delete = ($child_descriptor->source == $source);

						$examples = array();
						foreach ($child_descriptor->examples as $example){
							$example->can_delete = ($example->source == $source);
							if (!$example->can_delete) {
								$child_descriptor->can_delete = false;
							}

							if ($example->source != $source) {
								unset($child_descriptor->examples[$example->id]);
							}
						}
						$child_descriptor->examples = $examples;

						if (!$child_descriptor->can_delete) {
							$descriptor->can_delete = false;
						}
						if ($child_descriptor->source != $source && empty($child_descriptor->examples)) {
							unset($descriptor->children[$child_descriptor->id]);
						}
					}

					foreach ($descriptor->examples as $example){
						$example->can_delete = ($example->source == $source);
						if (!$example->can_delete) {
							$descriptor->can_delete = false;
						}
						if ($example->source != $source) {
							unset($descriptor->examples[$example->id]);
						}
						if ($descriptor->source == $source || !empty($descriptor->examples)) {
							unset($descriptor->children[$descriptor->id]);
						}
					}

					if (!$descriptor->can_delete) {
						$topic->can_delete = false;
					}
					if ($descriptor->source != $source && empty($descriptor->examples)) {
						unset($topic->descriptors[$descriptor->id]);
					}
				}

				if (!$topic->can_delete) {
					$subject->can_delete = false;
				}
				if ($topic->source != $source && empty($topic->descriptors)) {
					unset($subject->topics[$topic->id]);
				}
			}

			if ($subject->source != $source && empty($subject->topics)) {
				unset($subjects[$subject->id]);
			}
		}

		return $subjects;
	}

	function get_topics_for_subject($subject) {
		global $DB;

		return topic::create_objects($DB->get_records_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, t.source, t.numb
			FROM {'.block_exacomp::DB_SUBJECTS.'} s
			JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
				-- only show active ones
				WHERE s.id = ?
			ORDER BY t.id, t.sorting, t.subjid
		', array($subject->id)), array(
			'subject' => $subject
		), $this);
	}

	function assignDbLayer(array $objects) {
		array_walk($objects, function($object) {
			$object->setDbLayer($this);
		});

		return $objects;
	}

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

class db_layer_course extends db_layer {
	public $courseid = 0;
	public $showalldescriptors = false;
	public $showallexamples = false;
	public $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES);
	public $showonlyvisible = false;
	public $mindvisibility = true;

	function __construct($courseid) {
		$this->courseid = $courseid;
	}

	function get_subjects() {
		return subject::create_objects(block_exacomp_get_subjects_by_course($this->courseid), null, $this);
	}
}

class db_record {
	/**
	 * @var object
	 */
	protected $data = null;
	/**
	 * @var db_layer
	 */
	protected $dbLayer = null;

	const TABLE = 'todo';

	public function __construct($data, db_layer $dbLayer = null) {
		$this->data = (object)[];

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

	public function getData() {
		return clone $this->data;
	}

	public function &__get($name) {
		if (($method = 'get_'.$name) && method_exists($this, $method)) {
			$ret = $this->$method();

			// check if __get is recursively called at the same property
			if (property_exists($this, $name)) {
				// the property exists now -> error
				print_error('property set on object!');
			}

			return $ret;
		} elseif (property_exists($this->data, $name)) {
			return $this->data->$name;
		} elseif (($method = 'fill_'.$name) && method_exists($this, $method)) {
			$this->data->$name = $this->$method();

			// check if __get is recursively called at the same property
			if (property_exists($this, $name)) {
				// the property exists now -> error
				print_error('property set on object!');
			}

			return $this->data->$name;
		} else {
			print_error("property not found ".get_class($this)."::$name");
		}
	}

	public function __isset($name) {
		if (($method = 'get_'.$name) && method_exists($this, $method)) {
			$this->__get($name);
		} elseif (property_exists($this->data, $name)) {
			// ok
		} elseif (($method = 'fill_'.$name) && method_exists($this, $method)) {
			$this->__get($name);
		} else {
			return false;
		}

		return isset($this->data->$name);
	}

	public function __set($name, $value) {
		if (($method = 'set_'.$name) && method_exists($this, $method)) {
			$this->$method($value);

			// check if __set is recursively called at the same property
			if (property_exists($this, $name)) {
				// the property exists now -> error
				print_error('property set on object!');
			}

		} else {
			$this->data->$name = $value;
		}
	}
	public function __unset($name) {
		unset($this->data->$name);
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

		return $this->id = $DB->insert_record(static::TABLE, $this->data);
	}

	public function update($data = null) {
		return $this->update_record($data);
	}

	// just update the record
	public function update_record($data = null) {
		global $DB;

		if (!isset($this->id)) {
			throw new block_exacomp\exception('id not set');
		}

		if ($data === null) {
			die('TODO: testing');
			// update all my data
			return $DB->update_record(static::TABLE, $this->data);
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
			throw new block_exacomp\exception('id not set');
		}
		return $DB->delete_records(static::TABLE, array('id' => $this->id));
	}

	static function get($conditions, $fields=null, $strictness=null) {
		if (is_string($conditions) || is_int($conditions)) {
			// id
			$conditions = array('id' => $conditions);
		} elseif (is_object($conditions) || is_array($conditions)) {
			// ok
		} else {
			print_error('wrong fields');
		}

		$data = static::get_record($conditions, $fields, $strictness);

		if (!$data) return null;

		return static::create($data);
	}

	static function get_record(array $conditions, $fields=null, $strictness=null) {
		global $DB;

		// allow to just pass strictness
		if ($strictness === null && in_array($fields, array(IGNORE_MISSING, IGNORE_MULTIPLE, MUST_EXIST), true)) {
			$strictness = $fields;
			$fields = null;
		}
		if ($fields === null) $fields = '*';
		if ($strictness === null) $strictness = IGNORE_MISSING;

		return $DB->get_record(static::TABLE, $conditions, $fields, $strictness);
	}

	static function get_objects(array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
		return static::create_objects(static::get_records($conditions, $sort, $fields, $limitfrom, $limitnum));
	}

	static function get_records(array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
		return g::$DB->get_records(static::TABLE, $conditions, $sort, $fields, $limitfrom, $limitnum);
	}

	static function get_objects_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {
		return static::create_objects(static::get_records_sql($sql, $params, $limitfrom, $limitnum));
	}

	static function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0) {
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
			$data->setDbLayer($dbLayer);
			return $data;
		}

		return new static($data, $dbLayer);
	}
}

class subject extends db_record {
	const TABLE = block_exacomp::DB_SUBJECTS;

	function fill_topics() {
		return $this->dbLayer->get_topics_for_subject($this);
	}
}

class topic extends db_record {
	const TABLE = block_exacomp::DB_TOPICS;

	function get_numbering() {
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
	}

	function fill_descriptors() {
		return $this->dbLayer->get_descriptors_for_topic($this);
	}
}

class descriptor extends db_record {
	const TABLE = block_exacomp::DB_DESCRIPTORS;

	function init() {
		if (!isset($this->data->parent)) {
			$this->data->parent = null;
		}
	}

	function get_numbering() {
		global $DB;
		$topic = $this->topic;
		if (!$topic) {
			var_dump($this);
		}
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
		if (isset($this->data->topic)) {
			return $this->data->topic;
		}

		if (!isset($this->topicid)) {
			// required that topicid is set
			print_error('no topic loaded');
		}

		die('no');

		return topic::get($this->topicid);
	}

	static function insertInCourse($courseid, $data) {
		global $DB;

		$descriptor = static::create($data);
		$parent_descriptor = isset($descriptor->parentid) ? descriptor::get($descriptor->parentid) : null;
		$topic = isset($descriptor->topicid) ? topic::get($descriptor->topicid) : null;

		if ($parent_descriptor) {
		   $descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$parent_descriptor->id));
		   $topicid = $descriptor_topic_mm->topicid;

		   $parent_descriptor->topicid = $topicid;
		   $siblings = block_exacomp_get_child_descriptors($parent_descriptor, $courseid);
		} elseif ($topic) {
		   $topicid = $topic->id;
		   $descriptor->parentid = 0;

		   // TODO
		   $siblings = block_exacomp_get_descriptors_by_topic($courseid, $topicid);
		} else {
		   print_error('parentid or topicid not submitted');
		}

		// get $max_sorting
		$max_sorting = $siblings ? max(array_map(function($x) { return $x->sorting; }, $siblings)) : 0;

		$descriptor->source = block_exacomp::CUSTOM_CREATED_DESCRIPTOR;
		$descriptor->sorting = $max_sorting + 1;
		$descriptor->insert();

		$visibility = new \stdClass();
		$visibility->courseid = $courseid;
		$visibility->descrid = $descriptor->id;
		$visibility->studentid = 0;
		$visibility->visible = 1;

		$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $visibility);

		//topic association
		$childdesctopic_mm = new \stdClass();
		$childdesctopic_mm->topicid = $topicid;
		$childdesctopic_mm->descrid = $descriptor->id;

		$DB->insert_record(block_exacomp::DB_DESCTOPICS, $childdesctopic_mm);

		return $descriptor;
	}


	function set_categories($categories) {
		global $DB;

		// read current
		$to_delete = $current = $DB->get_records_menu(block_exacomp::DB_DESCCAT, array('descrid' => $this->id), null, 'catid, id');

		// add new ones
		foreach ($categories as $id) {
			if (!isset($current[$id])) {
				$DB->insert_record(block_exacomp::DB_DESCCAT, array('descrid' => $this->id, 'catid' => $id));
			} else {
				unset($to_delete[$id]);
			}
		}

		// delete old ones
		$DB->delete_records_list(block_exacomp::DB_DESCCAT, 'id', $to_delete);
	}

	function fill_category_ids() {
		global $DB;
		return $DB->get_records_menu(block_exacomp::DB_DESCCAT, array('descrid' => $this->id), null, 'catid, catid AS tmp');
	}

	function fill_children() {
		return $this->dbLayer->get_child_descriptors($this);
	}

	function fill_examples() {
		return $this->dbLayer->get_examples($this);
	}
}

class example extends db_record {
	const TABLE = block_exacomp::DB_EXAMPLES;

	function get_numbering() {
		if (!isset($this->descriptor)) {
			// required that descriptor is set
			print_error('no descriptor loaded');
		}

		return $this->descriptor->numbering;
	}

	function get_author() {
		if ($this->data->creatorid && $user = g::$DB->get_record('user', ['id' => $this->data->creatorid])) {
			return fullname($user);
		} else {
			return $this->data->author;
		}
	}
}

class niveau extends db_record {
	const TABLE = block_exacomp::DB_NIVEAUS;

	function get_subtitle($subjectid) {
		return g::$DB->get_field(block_exacomp::DB_SUBJECT_NIVEAU_MM, 'subtitle', ['subjectid' => $subjectid, 'niveauid' => $this->id]); // none for now
	}
}
