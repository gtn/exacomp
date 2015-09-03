<?php

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/lib/exabis_special_id_generator.php';
require_once __DIR__.'/lib/xmllib.php';

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
/*		
$settings->add(new admin_setting_configcheckbox('exacomp/skillmanagement', get_string('settings_skillmanagement', 'block_exacomp'),
		get_string('settings_skillmanagement_description', 'block_exacomp'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/enableteacherimport', get_string('settings_enableteacherimport', 'block_exacomp'),
		get_string('settings_enableteacherimport_description', 'block_exacomp'), 0, 1, 0));
*/
$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign', get_string('block_exacomp_external_trainer_assign_head', 'block_exacomp'),
        get_string('block_exacomp_external_trainer_assign_body', 'block_exacomp'), 0));

$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource', 'Source ID', "", PARAM_TEXT));

$settings->add(new admin_setting_configtext('exacomp/scheduleinterval', get_string('settings_interval','block_exacomp'), get_string('settings_interval_description','block_exacomp'), 50, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/scheduleunits', get_string('settings_scheduleunits','block_exacomp'), get_string('settings_scheduleunits_description','block_exacomp'), 8, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/schedulebegin', get_string('settings_schedulebegin','block_exacomp'), get_string('settings_schedulebegin_description','block_exacomp'), "07:45", PARAM_TEXT));
