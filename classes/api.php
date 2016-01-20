<?php

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../lib/lib.php';

use \block_exacomp\globals as g;
use \block_exacomp;

class api {
	static function active() {
		// check if block is active
		if (!g::$DB->get_record('block',array('name'=>'exacomp', 'visible'=>1))) {
			return false;
		}
		
		return true;
	}

	static function get_active_comp_for_exaport_item($itemid) {
		return g::$DB->get_records_menu('block_exacompcompactiv_mm', array("activityid" => $itemid, "eportfolioitem" => 1), null, 'compid AS id, compid');
	}

	static function get_comp_tree_for_exastud($userid, $type) {

		if ($type == 'resume') {
			// filter only teacher accepted competencies (for resume)
			$filter_descriptors = g::$DB->get_records_menu('block_exacompcompuser', array("userid" => g::$USER->id, "role" => 1 /*teacher*/), null, 'compid as id, compid');
		} else {
			$filter_descriptors = false; // not set
		}

		$map_descriptor = function($descriptor) use (&$map_descriptor, $filter_descriptors) {
			$subs = array_filter(array_map($map_descriptor, $descriptor->children));

			if ($filter_descriptors !== false && !$subs && !in_array($descriptor->id, $filter_descriptors)) {
				// filtered
				return;
			}

			return (object)[
				'id' => $descriptor->id,
				'title' => $descriptor->title,
				'type' => 'item',
				'subs' => $subs,
			];
		};
		$map_topic = function($topic) use ($map_descriptor) {
			$subs = array_filter(array_map($map_descriptor, $topic->descriptors));
			if (!$subs) {
				// no subs, they are filtered -> ignore
				return;
			}
			return (object)[
				'title' => $topic->title,
				'type' => 'group',
				'subs' => $subs
			];
		};
		$map_subject = function($subject) use ($map_topic) {
			$subs = array_filter(array_map($map_topic, $subject->topics));
			if (!$subs) {
				// no subs, they are filtered -> ignore
				return;
			}
			return (object)[
				'title' => $subject->title,
				'type' => 'group',
				'subs' => $subs
			];
		};

		$tree = db_layer_all_user_courses::create($userid)->get_subjects();

		// map to different datastructure for exastud
		$result = array_filter(array_map($map_subject, $tree));

		return $result;
	}
	
	static function delete_user_data($userid){
		global $DB;
		
		$DB->delete_records(block_exacomp::DB_COMPETENCIES, array("userid"=>$userid));
		$DB->delete_records(block_exacomp::DB_COMPETENCIES_USER_MM, array("userid"=>$userid));
		$DB->delete_records(block_exacomp::DB_PROFILESETTINGS, array("userid"=>$userid));

		$DB->delete_records(block_exacomp::DB_CROSSSTUD, array("studentid"=>$userid));
		$DB->delete_records(block_exacomp::DB_DESCVISIBILITY, array("studentid"=>$userid));
		$DB->delete_records(block_exacomp::DB_EXAMPLEEVAL, array("studentid"=>$userid));
		$DB->delete_records(block_exacomp::DB_EXAMPVISIBILITY, array("studentid"=>$userid));
		$DB->delete_records('block_exacompexternaltrainer', array("studentid"=>$userid));
		$DB->delete_records(block_exacomp::DB_SCHEDULE, array("studentid"=>$userid));

		$DB->delete_records(block_exacomp::DB_CROSSSUBJECTS, array("creatorid"=>$userid));
		$DB->delete_records(block_exacomp::DB_EXAMPLES, array("creatorid"=>$userid));
		$DB->delete_records(block_exacomp::DB_SCHEDULE, array("creatorid"=>$userid));

		$DB->delete_records(block_exacomp::DB_EXAMPLEEVAL, array("teacher_reviewerid"=>$userid));

		$DB->delete_records('block_exacompexternaltrainer', array("trainerid"=>$userid));

		$DB->delete_records(block_exacomp::DB_COMPETENCIES, array("reviewerid"=>$userid));
		$DB->delete_records(block_exacomp::DB_COMPETENCIES_USER_MM, array("reviewerid"=>$userid));

		return true;
	}
}
