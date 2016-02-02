<?php

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/lib/exabis_special_id_generator.php';
require_once __DIR__.'/lib/xmllib.php';
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
				// return get_string('validateerror', 'admin');
			}
		}
	}
	
	class block_exacomp_admin_setting_scheme extends admin_setting_configselect {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}
			
			if($data != '0'){
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
block_exacomp_data::generate_my_source();

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', get_string('settings_xmlserverurl', 'block_exacomp'),
		get_string('settings_configxmlserverurl', 'block_exacomp'), "", PARAM_URL));

$settings->add(new admin_setting_configcheckbox('exacomp/alternativedatamodel', get_string('settings_alternativedatamodel', 'block_exacomp'),
		get_string('settings_alternativedatamodel_description', 'block_exacomp'), 0, 1, 0));
		
$settings->add(new admin_setting_configcheckbox('exacomp/autotest', get_string('settings_autotest', 'block_exacomp'), 
		get_string('settings_autotest_description', 'block_exacomp'), 0, 1, 0));

$settings->add(new admin_setting_configtext('exacomp/testlimit', get_string('settings_testlimit', 'block_exacomp'), 
		get_string('settings_testlimit_description', 'block_exacomp'), 50, PARAM_INTEGER));
	
$settings->add(new admin_setting_configcheckbox('exacomp/usebadges', get_string('settings_usebadges', 'block_exacomp'), 
		get_string('settings_usebadges_description', 'block_exacomp'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/skillsmanagement', get_string('settings_skillsmanagement', 'block_exacomp'),
		get_string('settings_skillsmanagement_description', 'block_exacomp'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign', get_string('block_exacomp_external_trainer_assign_head', 'block_exacomp'),
		get_string('block_exacomp_external_trainer_assign_body', 'block_exacomp'), 0));

$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource', 'Source ID', "", PARAM_TEXT));

$settings->add(new admin_setting_configtext('exacomp/scheduleinterval', get_string('settings_interval','block_exacomp'), get_string('settings_interval_description','block_exacomp'), 50, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/scheduleunits', get_string('settings_scheduleunits','block_exacomp'), get_string('settings_scheduleunits_description','block_exacomp'), 8, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/schedulebegin', get_string('settings_schedulebegin','block_exacomp'), get_string('settings_schedulebegin_description','block_exacomp'), "07:45", PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('exacomp/notifications', get_string('block_exacomp_notifications_head', 'block_exacomp'),
		get_string('block_exacomp_notifications_body', 'block_exacomp'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/logging', get_string('block_exacomp_logging_head', 'block_exacomp'),
		get_string('block_exacomp_logging_body', 'block_exacomp'), 0));
		
$settings->add(new block_exacomp_admin_setting_scheme('exacomp/adminscheme', get_string('settings_admin_scheme', 'block_exacomp'),
		get_string('settings_admin_scheme_description', 'block_exacomp'), get_string('settings_admin_scheme_none', 'block_exacomp'), array(get_string('settings_admin_scheme_none', 'block_exacomp'), 'G/M/E', 'A/B/C', '*/**/***')));

$settings->add(new admin_setting_configcheckbox('exacomp/additional_grading', get_string('settings_additional_grading', 'block_exacomp'), 
		get_string('settings_additional_grading_description', 'block_exacomp'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/useprofoundness', get_string('useprofoundness', 'block_exacomp'),
		'' /* \block_exacomp\trans('en:todo') */, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/usetopicgrading', get_string('usetopicgrading', 'block_exacomp'),
		'' /* \block_exacomp\trans('en:todo') */, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/usenumbering', get_string('usenumbering', 'block_exacomp'),
		'' /* \block_exacomp\trans('en:todo') */, 1));
