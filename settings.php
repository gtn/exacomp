<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/lib/exabis_special_id_generator.php';
require_once __DIR__.'/classes/data.php';
require_once __DIR__.'/lib/lib.php';

if (!class_exists('block_exacomp_admin_setting_source')) {
	// check needed, because moodle includes this file twice

	class block_exacomp_admin_setting_source extends admin_setting_configtext {
		public function validate($data) {
			$ret = parent::validate($data);
			if ($ret !== true) {
				return $ret;
			}
			
			if (empty($data)) {
				// no id -> id must always be set
				return false;
			}
			if (exabis_special_id_generator::validate_id($data)) {
				return true;
			} else {
				return 'wrong id';
				// return block_exacomp\get_string('validateerror', 'admin');
			}
		}
	}
	
	class block_exacomp_admin_setting_scheme extends admin_setting_configselect {
		public function write_setting($data) {
			global $DB;
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}
			
			set_config('use_eval_niveau', 1, 'exacomp');
			
			if($data == '1') $titles = array(1=>'G', 2=>'M', 3=>'E');
			elseif($data == '2') $titles = array(1=>'A', 2=>'B', 3=>'C');
			elseif($data=='3') $titles = array(1=>'1', 2=>'2', 3=>'3');
			else{
				set_config('use_eval_niveau', 0, 'exacomp');
				return '';
			}
			
			//fill table
			foreach($titles as $index => $title){
				$record = $DB->get_record(\block_exacomp\DB_EVALUATION_NIVEAU, array('id'=>$index));
				if(!$record){
					$entry = new stdClass();
					$entry->title = $title;
					$DB->insert_record(\block_exacomp\DB_EVALUATION_NIVEAU, $entry);
				}else{
					$record->title = $title;
					$DB->update_record(\block_exacomp\DB_EVALUATION_NIVEAU, $record);
				}
			}
			
			return '';
		}
	}
	
	class admin_setting_configcheckbox_grading extends admin_setting_configcheckbox {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
			 
			if($data != '0'){
				//ensure that value is 0-4 which is needed for new grading scheme
				foreach(block_exacomp_get_courseids() as $course){
					$course_settings = block_exacomp_get_settings_by_course($course);
					if($course_settings->grading != 3){ //change course grading
						$course_settings->grading = 3;
						$course_settings->filteredtaxonomies = json_encode($course_settings->filteredtaxonomies);
						block_exacomp_save_coursesettings($course, $course_settings);
					}
				}
			}
			return '';
		}
	}
}

// generate id if not set
block_exacomp\data::generate_my_source();

$settings->add(new admin_setting_configcheckbox('exacomp/autotest', block_exacomp\get_string('settings_autotest'),
	block_exacomp\get_string('settings_autotest_description'), 0, 1, 0));

$settings->add(new admin_setting_configtext('exacomp/testlimit', block_exacomp\get_string('settings_testlimit'),
	block_exacomp\get_string('settings_testlimit_description'), 50, PARAM_INTEGER));

$settings->add(new admin_setting_configcheckbox('exacomp/usebadges', block_exacomp\get_string('settings_usebadges'),
	block_exacomp\get_string('settings_usebadges_description'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/notifications', block_exacomp\get_string('block_exacomp_notifications_head'),
	block_exacomp\get_string('block_exacomp_notifications_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/useprofoundness', block_exacomp\get_string('useprofoundness'),
		'', 0));


$settings->add(new admin_setting_heading('exacomp/heading_evaluation', block_exacomp\trans(['de:Beurteilung', 'en:Evaluation']), ''));

$settings->add(new block_exacomp_admin_setting_scheme('exacomp/adminscheme', block_exacomp\get_string('settings_admin_scheme'),
	block_exacomp\get_string('settings_admin_scheme_description'), block_exacomp\get_string('settings_admin_scheme_none'), array(block_exacomp\get_string('settings_admin_scheme_none'), 'G/M/E', 'A/B/C', '*/**/***')));

$settings->add(new admin_setting_configcheckbox_grading('exacomp/additional_grading', block_exacomp\get_string('settings_additional_grading'),
	block_exacomp\get_string('settings_additional_grading_description'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/usetopicgrading', block_exacomp\get_string('usetopicgrading'),
	'', 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usesubjectgrading', block_exacomp\get_string('usesubjectgrading'), 
	'', 0));

$settings->add(new admin_setting_heading('exacomp/heading_display', block_exacomp\trans(['de:Anzeige', 'en:Display']), ''));

$settings->add(new admin_setting_configcheckbox('exacomp/usenumbering', block_exacomp\get_string('usenumbering'),
	'', 1));

$settings->add(new admin_setting_configcheckbox('exacomp/useniveautitleinprofile', block_exacomp\get_string('useniveautitleinprofile'),
	'', 1));
$settings->add(new admin_setting_configcheckbox('exacomp/usetimeline', block_exacomp\get_string('settings_usetimeline'),
		block_exacomp\get_string('settings_usetimeline_description'), 0));


$settings->add(new admin_setting_heading('exacomp/heading_weekly_schedule', block_exacomp\get_string('weekly_schedule'), ''));
$settings->add(new admin_setting_configtext('exacomp/scheduleinterval', block_exacomp\get_string('settings_interval'),
	block_exacomp\get_string('settings_interval_description'), 50, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/scheduleunits', block_exacomp\get_string('settings_scheduleunits'),
	block_exacomp\get_string('settings_scheduleunits_description'), 8, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/schedulebegin', block_exacomp\get_string('settings_schedulebegin'),
	block_exacomp\get_string('settings_schedulebegin_description'), "07:45", PARAM_TEXT));


$settings->add(new admin_setting_heading('exacomp/heading_data', block_exacomp\trans(['de:Technische Einstellungen', 'en:Technical Settings']), ''));

$settings->add(new admin_setting_configcheckbox('exacomp/logging', block_exacomp\get_string('block_exacomp_logging_head'),
	block_exacomp\get_string('block_exacomp_logging_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign', block_exacomp\get_string('block_exacomp_external_trainer_assign_head'),
	block_exacomp\get_string('block_exacomp_external_trainer_assign_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/elove_student_self_assessment', block_exacomp\get_string('block_exacomp_elove_student_self_assessment_head'),
		block_exacomp\get_string('block_exacomp_elove_student_self_assessment_body'), 0));

$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource', 'Source ID',
	block_exacomp\trans(['de:Automatisch generierte ID dieser Exacomp Installation. Diese kann nicht geändert werden', 'en:Automatically generated ID of this Exacomp installation. This ID can not be changed']), PARAM_TEXT));

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', block_exacomp\get_string('settings_xmlserverurl'),
	block_exacomp\get_string('settings_configxmlserverurl'), "", PARAM_URL));

