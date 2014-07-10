<?php

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', get_string('settings_xmlserverurl', 'block_exacomp'),
		get_string('settings_configxmlserverurl', 'block_exacomp'), "", PARAM_URL));

$settings->add(new admin_setting_configcheckbox('exacomp/alternativedatamodel', get_string('settings_alternativedatamodel', 'block_exacomp'),
		get_string('settings_alternativedatamodel_description', 'block_exacomp'), 0, 1, 0));
