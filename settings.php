<?php

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', get_string('xmlserverurl', 'block_exacomp'),
		get_string('configxmlserverurl', 'block_exacomp'), "", PARAM_URL));