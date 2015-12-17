<?php

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../lib/lib.php';

use \block_exacomp\globals as g;

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

	static function get_comp_tree_for_exastud($type) {

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
			$subs = array_filter(array_map($map_topic, $subject->subs));
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

		$tree = block_exacomp_get_competence_tree(0, null, null, false, null);
		$result = array_filter(array_map($map_subject, $tree));

		return $result;
	}
	
	static function delete_user_data($user){
		global $DB;
		
		$result = $DB->delete_records('block_exacompcompuser', array("userid"=>$user));
		$result = $DB->delete_records('block_exacompcompuser_mm', array("userid"=>$user));
		$result = $DB->delete_records('block_exacompprofilesettings', array("userid"=>$user));
		
		$result = $DB->delete_records('block_exacompcrossstud_mm', array("studentid"=>$user));
		$result = $DB->delete_records('block_exacompdescrvisibility', array("studentid"=>$user));
		$result = $DB->delete_records('block_exacompexameval', array("studentid"=>$user));
		$result = $DB->delete_records('block_exacompexampvisibility', array("studentid"=>$user));
		$result = $DB->delete_records('block_exacompexternaltrainer', array("studentid"=>$user));
		$result = $DB->delete_records('block_exacompschedule', array("studentid"=>$user));
		
		$result = $DB->delete_records('block_exacompcrosssubjects', array("creatorid"=>$user));
		$result = $DB->delete_records('block_exacompexamples', array("creatorid"=>$user));
		$result = $DB->delete_records('block_exacompschedule', array("creatorid"=>$user));
		
		$result = $DB->delete_records('block_exacompexameval', array("teacher_reviewerid"=>$user));
		
		$result = $DB->delete_records('block_exacompexternaltrainer', array("trainerid"=>$user));
		
		$result = $DB->delete_records('block_exacompcompuser', array("reviewerid"=>$user));
		$result = $DB->delete_records('block_exacompcompuser_mm', array("reviewerid"=>$user));	
	}
}
