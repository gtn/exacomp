<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


use block_exacomp\url;

defined('MOODLE_INTERNAL') || die;

require_once __DIR__ . '/inc.php';
require_once __DIR__ . '/lib/settings_helper.php';

// Generate id if not set.
block_exacomp\data::generate_my_source();

// Allgemein (General).
$settings->add(new admin_setting_heading('exacomp/heading_general', block_exacomp_get_string('settings_heading_general'), ''));
$settings->add(new admin_setting_configcheckbox('exacomp/autotest', block_exacomp_get_string('settings_autotest'),
    block_exacomp_get_string('settings_autotest_description'), 0, 1, 0));

$settings->add(new block_exacomp_link_to('exacomp/dakora_teacher',
    block_exacomp_get_string('assign_dakora_teacher'),
    '',
    '',
    url::create('/cohort/assign.php'),
    block_exacomp_get_string('assign_dakora_teacher_link'),
    ['id' => block_exacomp_get_dakora_teacher_cohort()->id],
    ['target' => '_blank'],
    true));

$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/testlimit', block_exacomp_get_string('settings_testlimit'),
    block_exacomp_get_string('settings_testlimit_description'), 50, PARAM_INT));

// Beurteilung (assessment).
$settings->add(new admin_setting_heading('exacomp/heading_assessment',
    block_exacomp_get_string('settings_heading_assessment'),
    ''));
$settings->add(new block_exacomp_admin_setting_preconfiguration('exacomp/assessment_preconfiguration',
    block_exacomp_get_string('settings_admin_scheme'),
    block_exacomp_get_string('settings_admin_scheme_description'),
    block_exacomp_get_string('settings_admin_scheme_none'),
    null));

$settings->add(new block_exacomp_assessment_configtable('exacomp/assessment_mapping', '', '', ''));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_points_limit',
    block_exacomp_get_string('settings_assessment_points_limit'),
    block_exacomp_get_string('settings_assessment_points_limit_description'),
    10, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_points_negativ',
    block_exacomp_get_string('settings_assessment_points_negativ'),
    block_exacomp_get_string('settings_assessment_points_negativ_description'),
    3, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_grade_limit',
    block_exacomp_get_string('settings_assessment_grade_limit'),
    block_exacomp_get_string('settings_assessment_grade_limit_description'),
    6, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_grade_negativ',
    block_exacomp_get_string('settings_assessment_grade_negativ'),
    block_exacomp_get_string('settings_assessment_grade_negativ_description'),
    5, PARAM_INT));
$verb_default = block_exacomp_get_string('settings_assessment_grade_verbose_default');
/*if (!$verb_default) { // lang files are not generated in first installation?
    $verb_default = 'very good, good, satisfactory, sufficient, deficient, insufficient'; // 'en' is default
    if (current_language() == 'de') {
        $verb_default = 'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend';
    }
}*/
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_grade_verbose',
    block_exacomp_get_string('settings_assessment_grade_verbose'),
    block_exacomp_get_string('settings_assessment_grade_verbose_description'),
    $verb_default,
    PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/use_grade_verbose_competenceprofile',
    block_exacomp_get_string('use_grade_verbose_competenceprofile'),
    block_exacomp_get_string('use_grade_verbose_competenceprofile_descr'),
    1));

$settings->add(new block_exacomp_admin_setting_diffLevelOptions('exacomp/assessment_diffLevel_options',
    block_exacomp_get_string('settings_assessment_diffLevel_options'),
    block_exacomp_get_string('settings_assessment_diffLevel_options_description'),
    block_exacomp_get_string('settings_assessment_diffLevel_options_default'),
    PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_verbose_options',
    block_exacomp_get_string('settings_assessment_verbose_options'),
    block_exacomp_get_string('settings_assessment_verbose_options_description'),
    block_exacomp_get_string('settings_assessment_verbose_options_default'),
    PARAM_TEXT));
$options = array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options())); // default options (next can be changed by JS)
// keys are from 0: 0,1,2...
$settings->add(new block_exacomp_admin_setting_verbose_negative('exacomp/assessment_verbose_negative',
    block_exacomp_get_string('settings_assessment_grade_verbose_negative'),
    block_exacomp_get_string('settings_assessment_grade_verbose_negative_description'),
    block_exacomp_get_assessment_verbose_negative_threshold(0), $options));
$settings->add(new admin_setting_configcheckbox('exacomp/assessment_verbose_lowerisbetter',
    block_exacomp_get_string('settings_assessment_verbose_lowerisbetter'),
    block_exacomp_get_string('settings_assessment_verbose_lowerisbetter_description'), 0));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_verbose_options_short',
    block_exacomp_get_string('settings_assessment_verbose_options_short'),
    block_exacomp_get_string('settings_assessment_verbose_options_short_description'),
    block_exacomp_get_string('settings_assessment_verbose_options_short_default'),
    PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/assessment_SelfEval_useVerbose',
    block_exacomp_get_string('assessment_SelfEval_useVerbose'),
    '', 0));
$settings->add(new block_exacomp_selfevaluation_configtable('exacomp/assessment_SelfEval_verboses',
    block_exacomp_get_string('settings_assessment_SelfEval_verboses'), '', '', ''));
$settings->add(new admin_setting_configcheckbox('exacomp/example_autograding',
    block_exacomp_get_string('settings_example_autograding'),
    block_exacomp_get_string('settings_example_autograding_description'), 0));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/useprofoundness',
    block_exacomp_get_string('useprofoundness'),
    '', 0));

$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/schoolname',
    block_exacomp_get_string('settings_schoolname'),
    block_exacomp_get_string('settings_schoolname_description'),
    block_exacomp_get_string('settings_schoolname_default'),
    PARAM_TEXT));

// Darstellung (visualisation).
$settings->add(new admin_setting_heading('exacomp/heading_visualisation',
    block_exacomp_get_string('settings_heading_visualisation'),
    ''));
$settings->add(new admin_setting_configcheckbox('exacomp/usenumbering',
    block_exacomp_get_string('usenumbering'),
    '', 1));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/scheduleinterval',
    block_exacomp_get_string('settings_interval'),
    block_exacomp_get_string('settings_interval_description'), 50, PARAM_INT));
$settings->add(new admin_setting_description("subheader1", "", block_exacomp_get_string('settings_description_nurdakoraplus')));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/scheduleunits',
    block_exacomp_get_string('settings_scheduleunits'),
    block_exacomp_get_string('settings_scheduleunits_description'), 8, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/schedulebegin',
    block_exacomp_get_string('settings_schedulebegin'),
    block_exacomp_get_string('settings_schedulebegin_description'), "07:45", PARAM_TEXT));
$settings->add(new admin_setting_description("subheader", "", block_exacomp_get_string('settings_description_nurmoodleunddakora')));
$settings->add(new admin_setting_configtextarea('exacomp/periods',
    block_exacomp_get_string('settings_periods'),
    block_exacomp_get_string('settings_periods_description'), ''));

// Administratives (technical).
$settings->add(new admin_setting_heading('exacomp/heading_technical',
    block_exacomp_get_string('settings_heading_technical'),
    ''));
$settings->add(new admin_setting_configcheckbox('exacomp/usebadges',
    block_exacomp_get_string('settings_usebadges'),
    block_exacomp_get_string('settings_usebadges_description'), 0, 1, 0));
$settings->add(new admin_setting_configcheckbox('exacomp/notifications',
    block_exacomp_get_string('block_exacomp_notifications_head'),
    block_exacomp_get_string('block_exacomp_notifications_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/logging',
    block_exacomp_get_string('block_exacomp_logging_head'),
    block_exacomp_get_string('block_exacomp_logging_body'), 0));
$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource',
    block_exacomp_get_string('settings_sourceId'),
    block_exacomp_get_string('settings_sourceId_description'),
    PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/xmlserverurl',
    block_exacomp_get_string('settings_xmlserverurl'),
    block_exacomp_get_string('settings_configxmlserverurl'), "", PARAM_URL));
$options = array('' => block_exacomp_get_string('settings_addblock_to_newcourse_option_no'),
    //BLOCK_POS_LEFT  => block_exacomp_get_string('settings_addblock_to_newcourse_option_left'),
    //BLOCK_POS_RIGHT => block_exacomp_get_string('settings_addblock_to_newcourse_option_right'),
    BLOCK_POS_LEFT => block_exacomp_get_string('settings_addblock_to_newcourse_option_yes'),
);
$settings->add(new admin_setting_configselect('exacomp/addblock_to_new_course',
    block_exacomp_get_string('settings_addblock_to_newcourse'),
    block_exacomp_get_string('settings_addblock_to_newcourse_description'), '', $options));
$settings->add(new admin_setting_configcheckbox('exacomp/assign_activities_old_method',
    block_exacomp_get_string('block_exacomp_assign_activities_old_method_head'),
    block_exacomp_get_string('block_exacomp_assign_activities_old_method_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/disable_create_grid',
    block_exacomp_get_string('block_exacomp_disable_create_grid_head'),
    block_exacomp_get_string('block_exacomp_disable_create_grid_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/show_teacherdescriptors_global',
    block_exacomp_get_string('settings_show_teacherdescriptors_global'),
    block_exacomp_get_string('settings_show_teacherdescriptors_global_description'), 1));


// Apps-Einstellungen (configuration for apps).
$settings->add(new admin_setting_heading('exacomp/heading_apps',
    block_exacomp_get_string('settings_heading_apps'),
    ''));

$settings->add(new admin_setting_configcheckbox('exacomp/new_app_login',
    block_exacomp_get_string('settings_new_app_login'),
    block_exacomp_get_string('settings_new_app_login_description'), 0));

$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/dakora_url',
    block_exacomp_get_string('settings_dakora_url'),
    block_exacomp_get_string('settings_dakora_url_description'),
    '',
    PARAM_TEXT));

$settings->add(new admin_setting_description("subheader2", "", block_exacomp_get_string('settings_description_nurdakora')));

$settings->add(new admin_setting_configstoredfile('exacomp/dakora_language_file',
    block_exacomp_get_string('block_exacomp_dakora_language_file_head'),
    block_exacomp_get_string('block_exacomp_dakora_language_file_body'),
    'exacomp_dakora_language_file',
    0,
    array('maxfiles' => 1, 'accepted_types' => array('.json'))));

$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/dakora_timeout',
    block_exacomp_get_string('settings_dakora_timeout'),
    block_exacomp_get_string('settings_dakora_timeout_description'),
    900, PARAM_INT));
$settings->add(new admin_setting_configcheckbox('exacomp/dakora_show_overview',
    block_exacomp_get_string('settings_dakora_show_overview'),
    block_exacomp_get_string('settings_dakora_show_overview_description'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/dakora_show_eportfolio',
    block_exacomp_get_string('settings_dakora_show_eportfolio'),
    block_exacomp_get_string('settings_dakora_show_eportfolio_description'), 1));

$settings->add(new admin_setting_description("subheader3", "", block_exacomp_get_string('settings_description_nurdiggr')));

$settings->add(new admin_setting_configcheckbox('exacomp/elove_student_self_assessment',
    block_exacomp_get_string('block_exacomp_elove_student_self_assessment_head'),
    block_exacomp_get_string('block_exacomp_elove_student_self_assessment_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign',
    block_exacomp_get_string('block_exacomp_external_trainer_assign_head'),
    block_exacomp_get_string('block_exacomp_external_trainer_assign_body'), 0));


// Performance (configuration for performance issues).
$settings->add(new admin_setting_heading('exacomp/heading_performance',
    block_exacomp_get_string('settings_heading_performance'),
    block_exacomp_get_string('settings_heading_performance_description')));
$settings->add(new admin_setting_configcheckbox('exacomp/disable_js_assign_competencies',
    block_exacomp_get_string('settings_disable_js_assign_competencies'),
    block_exacomp_get_string('settings_disable_js_assign_competencies_description'), 0, 1, 0));
$settings->add(new admin_setting_configcheckbox('exacomp/disable_js_edit_activities',
    block_exacomp_get_string('settings_disable_js_editactivities'),
    block_exacomp_get_string('settings_disable_js_editactivities_description'), 0, 1, 0));

$settings->add(new admin_setting_heading('exacomp/heading_security',
    block_exacomp_get_string('settings_heading_security'),
    block_exacomp_get_string('settings_heading_security_description')));
$settings->add(new admin_setting_configcheckbox('exacomp/export_password',
    block_exacomp_get_string('settings_export_password'),
    block_exacomp_get_string('settings_export_password_description'), 0, 1, 0));

$settings->add(new admin_setting_configtextarea('exacomp/applogin_redirect_urls',
    block_exacomp_get_string('settings_applogin_redirect_urls'),
    block_exacomp_get_string('settings_applogin_redirect_urls_description'), ''));

$settings->add(new admin_setting_configcheckbox('exacomp/applogin_enabled',
    block_exacomp_get_string('settings_applogin_enabled'),
    block_exacomp_get_string('settings_applogin_enabled_description'), 1));

$settings->add(new admin_setting_configcheckbox('exacomp/setapp_enabled',
    block_exacomp_get_string('settings_setapp_enabled'),
    block_exacomp_get_string('settings_setapp_enabled_description'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/sso_create_users',
    block_exacomp_get_string('settings_sso_create_users'),
    block_exacomp_get_string('settings_sso_create_users_description'), 0, 1, 0));

$settings->add(new admin_setting_configtext('exacomp/msteams_client_id',
    block_exacomp_get_string('settings_msteams_client_id'),
    block_exacomp_get_string('settings_msteams_client_id_description'), '', PARAM_TEXT));

$settings->add(new admin_setting_configtext('exacomp/msteams_client_secret',
    block_exacomp_get_string('settings_msteams_client_secret'),
    block_exacomp_get_string('settings_msteams_client_secret_description'), '', PARAM_TEXT));

$settings->add(new admin_setting_configcheckbox('exacomp/example_upload_global',
    block_exacomp_get_string('settings_example_upload_global'),
    block_exacomp_get_string('settings_example_upload_global_description'), 1));

// To delete?
//$settings->add(new block_exacomp_admin_setting_scheme('exacomp/adminscheme',
//        block_exacomp_get_string('settings_admin_scheme'),
//        block_exacomp_get_string('settings_admin_scheme_description'),
//        block_exacomp_get_string('settings_admin_scheme_none'),
//        array(block_exacomp_get_string('settings_admin_scheme_none'), 'G/M/E/Z', 'A/B/C', '*/**/***')));
/*$settings->add(new admin_setting_heading('exacomp/heading_data', '&nbsp;', ''));
$settings->add(new admin_setting_configcheckbox_grading('exacomp/additional_grading',
        block_exacomp_get_string('settings_additional_grading'),
	    block_exacomp_get_string('settings_additional_grading_description'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usetopicgrading',
            block_exacomp_get_string('usetopicgrading'),
	    '', 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usesubjectgrading',
        block_exacomp_get_string('usesubjectgrading'),
	    '', 0));*/

