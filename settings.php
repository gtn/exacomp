<?php

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', get_string('settings_xmlserverurl', 'block_exacomp'),
		get_string('settings_configxmlserverurl', 'block_exacomp'), "", PARAM_URL));

$settings->add(new admin_setting_configcheckbox('exacomp/alternativedatamodel', get_string('settings_alternativedatamodel', 'block_exacomp'),
		get_string('settings_alternativedatamodel_description', 'block_exacomp'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/usedetailpage', get_string('settings_usedetailpage', 'block_exacomp'),
		get_string('settings_usedetailpage_description', 'block_exacomp'), 0, 1));

$settings->add(new admin_setting_configcheckbox('exacomp/autotest', get_string('settings_autotest', 'block_exacomp'), 
		get_string('settings_autotest_description', 'block_exacomp'), 0, 1));

$settings->add(new admin_setting_configtext('exacomp/testlimit', get_string('settings_testlimit', 'block_exacomp'), 
		get_string('settings_testlimit_description', 'block_exacomp'), 50, PARAM_INTEGER));
	
$settings->add(new admin_setting_configcheckbox('exacomp/usebadges', get_string('settings_usebadges', 'block_exacomp'), 
		get_string('settings_usebadges_description', 'block_exacomp'), 0, 1));