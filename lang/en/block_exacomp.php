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
 * @copyright Florian Jungwirth <fjungwirth@gtn-solutions.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['exacomp:addinstance'] = 'Add a exabis competencies block';
$string['exacomp:myaddinstance'] = 'Add a exabis competencies block to my moodle';
$string['pluginname'] = 'Exabis Competencies';

// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_import'] = 'Import';
$string['tab_admin_configuration'] = 'Configuration';

//Teacher Tabs
$string['tab_teacher_settings'] = 'Settings';
$string['tab_teacher_settings_configuration'] = 'Configuration';
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
$string['tab_competence_profile_profile'] = 'Profile';
$string['tab_competence_profile_settings'] = 'Settings';
$string['tab_help'] = 'Help';
$string['tab_skillmanagement'] = 'manage your competencies';

$string['next_step'] = 'continue configuration';

//Block Settings 
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url to a xml file, which is used for keeping the database entries up to date';
$string['settings_alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['settings_alternativedatamodel_description'] = 'Tick to use Baden W&uuml;rttemberg Version';
$string['settings_autotest'] = 'automatical gain of competence through quizzes';
$string['settings_autotest_description'] = 'competences that are associated with quizzes are gained automatically if needed percentage of quiz is reached';
$string['settings_testlimit'] = 'quiz-percentage needed to gain competence';
$string['settings_testlimit_description'] = 'this percentage has to be reached to gain the competence';
$string['settings_usebadges'] = 'use badges';
$string['settings_usebadges_description'] = 'check to associate badges with competences';
$string['settings_skillmanagement'] = 'use skills-management';
$string['settings_skillmanagement_description'] = 'check to use skills-management-tool instead of import';

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
$string['LA_no_learningagenda'] = 'There is no learning agenda available for this week.';
$string['LA_no_student_selected'] = '-- no student selected --';
$string['LA_select_student'] = 'Please select a student to view his learning agenda.';
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
$string['importinfo'] = 'Please create your outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file.';
$string['importwebservice'] = 'It is possible to keep the data up to date via a <a href="{$a}">webservice</a>.';
$string['importdone'] = 'data has already been imported from xml';
$string['importpending'] = 'no data has been imported yet!';
$string['doimport'] = 'import outcomes/standards';
$string['doimport_again'] = 're-import outcomes/standards';
$string['doimport_own'] = 'import individual outcomes/standards';
$string['importsuccess'] = 'data was successfully imported!';
$string['importsuccess_own'] = 'individual data was imported successfully!';
$string['importfail'] = 'an error has occured during import';
$string['noxmlfile'] = 'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.';
$string['oldxmlfile'] = 'You are using an outdated xml-file. Please create new outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';

//Configuration
$string['explainconfig'] = 'Your outcomes have already been imported. In this configuration you have to make the selection of main outcomes you would like to use in this Moodle installation.';
$string['save_selection'] = 'save selection';
$string['save_success'] = 'changes were successful';

//Course configuratin
$string['grading_scheme'] = 'grading scheme';
$string['uses_activities'] = 'I work with Moodle activites';
$string['show_all_descriptors'] = 'Show all outcomes in overview';
$string['show_all_examples'] = 'Show all external examples in overview';
$string['usedetailpage'] = 'Use detailed overview of competencies';
//Badges
$string['mybadges'] = 'My badges';
$string['pendingbadges'] = 'Pending badges';
$string['no_badges_yet'] = "no badges available";

//Examples
$string['sorting'] = 'select sorting: ';
$string['subject'] = 'subjects';
$string['taxonomies'] = 'taxonomies';
$string['show_all_course_examples'] = 'Show examples from all courses';
$string['expandcomps'] = 'expand all';
$string['contactcomps'] = 'contract all';
//Icons
$string['assigned_example'] = 'Assigned Example';
$string['task_example'] = 'Tasks';
$string['solution_example'] = 'Solution';
$string['attachement_example'] = 'Attachement';
$string['extern_task'] = 'External Task';
$string['total_example'] = 'Complete Example';

//Example Upload
$string['example_upload_header']  = 'Upload my own task/example';
$string['taxonomy'] = 'taxonomy';
$string['descriptors'] = 'outcomes/standards';
$string['descriptors_help'] = 'You can select multible outcomes/standards';
$string['filerequired'] = 'A file must be selected.';
$string['titlenotemtpy'] = 'A name is required.';
$string['lisfilename'] = 'Use LIS filename template';

//Assign competencies
$string['save_selection'] = 'Save selection';
$string['delete_confirmation'] = 'Do you really want to delete this example?';
$string['legend_activities'] = 'activities';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'no moodle-task/quiz has been submitted for this descriptor';
$string['legend_upload'] = 'Upload your own task/example';
$string['alltopics'] = 'All topics';
$string['choosesubject'] = "Choose subject: ";
$string['choosetopic'] = "Choose topic";
$string['choosestudent'] = "Choose student: ";
$string['selectall'] = 'Select all';

//Icons
$string['usersubmitted'] = ' has submitted the following tasks:';
$string['usersubmittedquiz'] = ' has done the following quizzes:';
$string['usernosubmission'] = ' has not yet submitted any moodle-tasks or quizzes associated with this descriptor';
$string['usernosubmission_topic'] = ' has not yet submitted any moodle-tasks or quizzes associated with this topic';
$string['grading'] = ' Grading: ';
$string['teacher_tipp'] = 'tip';
$string['teacher_tipp_description'] = 'This competence has been reached through an activity.';

$string['teachershortcut'] = 'T';
$string['studentshortcut'] = 'S';

$string['overview'] = 'This is an overview of all students and the course competencies.';
$string['showevaluation'] = 'To show self-assessment click <a href="{$a}">here</a>';
$string['hideevaluation'] = 'To hide self-assessment click <a href="{$a}">here</a>';
$string['columnselect'] = 'Column selection';
$string['allstudents'] = 'All students';

$string['assigndone'] = 'task done: ';
$string['assignmyself'] = 'by myself';
$string['assignlearningpartner'] = 'peer-to-peer';
$string['assignlearninggroup'] = 'peer group';
$string['assignteacher'] = 'teacher';
$string['assignfrom'] = 'from';
$string['assignuntil'] = 'until';

//Activities
$string['explaineditactivities_subjects'] = 'Here you can associate tasks with competencies.';
$string['column_setting'] = 'hide/display columns';
$string['niveau_filter'] = "filter levels";
$string['module_filter'] = 'filter activities';
$string['apply_filter'] = 'apply filter';
$string['no_topics_selected'] = 'configuration of exabis competencies is not completed yet. please chose a topic that you would like to associate activities with';
$string['no_activities_selected'] = 'please associate activities with competences';

//Competence Grid
$string['textalign'] = "Switch text align";
$string['selfevaluation'] = 'Self assessment';
$string['teacherevaluation'] = 'Teacher assessment';
$string['competencegrid_nodata'] = 'In case the competency grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.';

//Detail view
$string['detail_description'] = 'Use activities to evaluate competencies.';

//Competence Profile
$string['name'] = 'Name';
$string['city'] = 'City';
$string['course'] = 'Course';
$string['gained'] = 'gained';
$string['total'] = 'total';
$string['allcourses'] = 'all courses';
$string['pendingcomp'] = 'pending competencies';
$string['teachercomp'] = 'gained competencies';
$string['studentcomp'] = 'self evaluated competencies';
$string['radargrapherror'] = 'Radargraph can only be displayed with 3-7 axis';
$string['nodata'] = 'There is no data do display';
$string['item_no_comps'] = 'There are no outcomes assigned to the following items: ';

//Competence Profile Settings
$string['profile_settings_showonlyreached'] = 'I only want to see already gained outcomes in my competence profile';
$string['profile_settings_choose_courses'] = 'Using Exabis Competencies teachers assess your competencies in various subjects. You can select which course to include in the competence profile.';
$string['profile_settings_useexaport'] = 'I want to see competencies used in Exabis ePortfolio within my profile.';
$string['profile_settings_choose_items'] = 'Exabis ePortfolio is used to document your competencies on your individual learning path. You can select which artefacts to include in the competence profile.';
$string['profile_settings_useexastud'] = 'I want to see evaluations from Exabis Student Review.';
$string['profile_settings_choose_periods'] = 'Exabis Student Review stores reviews in various categories over several periods. You can select which periods to include in the competence profile.';
$string['profile_settings_no_item'] = 'No Exabis ePortfolio item available.';
$string['profile_settings_no_period'] = 'No review in a period in Exabis Student Review available.';

//LIS metadata
$string['subject_singular'] = 'Fach';
$string['comp_field_idea'] = 'Kompetenzbereich/Leitidee';
$string['comp'] = 'Kompetenz';
$string['progress'] = 'Lernfortschritt';
$string['instruction'] = 'Anleitung';
$string['instruction_content'] = 'Hier k&ouml;nnen Sie für Ihre Lerngruppen / Klasse vermerken, welche
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden.
				Dar&uuml;ber hinaus können Sie das Erreichen der Teilkompetenzen
				eintragen. Je nach Konzept der Schule kann die Bearbeitung des
				Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz
				markiert oder die Qualit&auml;t der Bearbeitung / der Kompetenzerreichung
				gekennzeichnet werden. Keinenfalls müssen die Sch&uuml;lerinnen und
				Sch&uuml;ler alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Sch&uuml;lerinnen
				und Sch&uuml;ler m&uuml;ssen dann keine zugeh&ouml;rigen Lernmaterialien
				bearbeiten.';
$string['requirements'] = 'Was du schon k&ouml;nnen solltest: ';
$string['forwhat'] = 'Wof&uuml;r du das brauchst: ';
$string['howtocheck'] = 'Wie du dein K&ouml;nnen pr&uuml;fen kannst: ';
$string['reached_topic'] = 'Ich habe diese Kompetenz erreicht: ';