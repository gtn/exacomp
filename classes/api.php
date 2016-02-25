<?php
// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../lib/lib.php';

use block_exacomp\globals as g;

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

	static function get_comp_tree_for_exaport($userid) {

		$achieved_descriptors = g::$DB->get_records_menu('block_exacompcompuser', array("userid" => g::$USER->id, "role" => 1 /*teacher*/), null, 'compid as id, compid');

		$walker = function($item) use (&$walker, $achieved_descriptors) {
			$subs = $item->get_subs();
			array_walk($subs, $walker);

			if ($item instanceof descriptor) {
				$item->achieved = in_array($item->id, $achieved_descriptors);
			} else {
				$item->achieved = null;
			}
		};

		$tree = db_layer_all_user_courses::create($userid)->get_subjects();

		// map to different datastructure for exastud
		array_walk($tree, $walker);

		return $tree;
	}
	
	static function delete_user_data($userid){
		global $DB;
		
		$DB->delete_records(\block_exacomp\DB_COMPETENCIES, array("userid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_COMPETENCIES_USER_MM, array("userid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_PROFILESETTINGS, array("userid"=>$userid));

		$DB->delete_records(\block_exacomp\DB_CROSSSTUD, array("studentid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_DESCVISIBILITY, array("studentid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_EXAMPLEEVAL, array("studentid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_EXAMPVISIBILITY, array("studentid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_EXTERNAL_TRAINERS, array("studentid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_SCHEDULE, array("studentid"=>$userid));

		$DB->delete_records(\block_exacomp\DB_CROSSSUBJECTS, array("creatorid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_EXAMPLES, array("creatorid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_SCHEDULE, array("creatorid"=>$userid));

		$DB->delete_records(\block_exacomp\DB_EXAMPLEEVAL, array("teacher_reviewerid"=>$userid));

		$DB->delete_records(\block_exacomp\DB_EXTERNAL_TRAINERS, array("trainerid"=>$userid));

		$DB->delete_records(\block_exacomp\DB_COMPETENCIES, array("reviewerid"=>$userid));
		$DB->delete_records(\block_exacomp\DB_COMPETENCIES_USER_MM, array("reviewerid"=>$userid));

		return true;
	}
}
