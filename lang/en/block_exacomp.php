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

/**
 * Strings for component 'block_exacomp', language 'en'
 *
 * @package   block_exacomp
 * @copyright Florian Jungwirth <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['exacomp:addinstance'] = 'Add a exabis competencies block';
$string['exacomp:myaddinstance'] = 'Add a exabis competencies block to my moodle';
$string['pluginname'] = 'exabis competencies';

// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_import'] = 'Import';
$string['tab_admin_configuration'] = 'Configuration';

//Teacher Tabs
$string['tab_teacher_settings'] = 'Settings';
$string['tab_teacher_settings_configuration'] = 'configuration';
$string['tab_teacher_settings_selection_st'] = 'Schooltype selection';
$string['tab_teacher_settings_selection'] = 'Subject selection';
$string['tab_teacher_settings_assignactivities'] = 'Assign activities';
$string['tab_teacher_settings_badges'] = 'Edit badges';

//Student Tabs
$string['tab_student_all'] = 'All gained competencies';

//Generic Tabs (used by Teacher and Students)
$string['tab_competence_grid'] = 'Competence grid';
$string['tab_competence_overview'] = 'Overview of competencies';
$string['tab_competence_details'] = 'Detailed competence-view';
$string['tab_examples'] = 'Examples and tasks';
$string['tab_learning_agenda'] = 'Learning agenda';
$string['tab_badges'] = 'My badges';
$string['tab_competence_profile'] = 'Competence profile';
$string['tab_help'] = 'Help';

//Block Settings 
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url to a xml file, which is used for keeping the database entries up to date';
$string['settings_alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['settings_alternativedatamodel_description'] = 'Tick to use Baden W&uuml;rttemberg Version';

//Learning agenda
$string['LA_MON'] = "MON";
$string['LA_TUE'] = "TUE";
$string['LA_WED'] = "WED";
$string['LA_THU'] = "THU";
$string['LA_FRI'] = "FRI";
$string['LA_todo'] = "What do I do?";
$string['LA_learning'] = "What can I learn?";
$string['LA_student'] = "S";
$string['LA_teacher'] = "T";
$string['LA_assessment'] = "assessment";
$string['LA_plan'] = "working plan";
//$string['LA_allstudents'] = 'Alle Sch�ler';
$string['LA_no_learningagenda'] = 'no learning agenda available';
$string['LA_no_student_selected'] = '-- no student selected --';
$string['LA_select_student'] = 'select student';
$string['LA_no_example'] = 'no example available';
$string['LA_backtoview'] = 'back to original view';
$string['LA_from_n'] = ' from ';
$string['LA_from_m'] = ' from ';
$string['LA_to'] = ' to ';
$string['LA_enddate']='end date';
$string['LA_startdate']='start date';

//Help
$string['help_content'] = '<h1>Introduction Video</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';

//Import
$string['importinfo'] = 'Please create your outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';
$string['importwebservice'] = 'It is possible to keep the data up to date via a <a href="{$a}">webservice</a>.';
$string['importdone'] = 'data has already been imported from xml';
$string['importpending'] = 'no data has been imported yet!';
$string['doimport'] = 'import descriptors';
$string['doimport_again'] = 're-import descriptors';
$string['doimport_own'] = 'import individual descriptors';
$string['importsuccess'] = 'data was successfully imported!';
$string['importsuccess_own'] = 'individual data was imported successfully!';
$string['importfail'] = 'an error has occured during import';
$string['noxmlfile'] = 'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.';
$string['oldxmlfile'] = 'You are using an outdated xml-file. Please create new outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';

//Configuration
$string['explainconfig'] = 'In order to use the module exabis competencies you have to choose your schooltype - the appropriate data will then be imported from the xml-file.';
$string['save_selection'] = 'save selection';
$string['save_success'] = 'changes were successful';

//Course configuratin
$string['grading_scheme'] = 'grading scheme';
$string['uses_activities'] = 'I work with activites';
$string['show_all_descriptors'] = 'Show all outcomes in overview';
$string['show_all_examples'] = 'Show all examples in overview';

//Badges
$string['mybadges'] = 'My badges';
$string['pendingbadges'] = 'Pending badges';
$string['no_badges_yet'] = "no badges available";