<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
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

// shown in admin plugin list
$string['pluginname'] = 'Exabis Competence Grid';
// shown in block title and all headers
$string['blocktitle'] = 'Competence Grid';
$string['exacomp:addinstance'] = 'Add a Exabis Competence Grid block';
$string['exacomp:myaddinstance'] = 'Add a Exabis Competence Grid block to my moodle';
$string['exacomp:teacher'] = 'overview of trainer actions in a course';
$string['exacomp:admin'] = 'overview of administrator actions in a course';
$string['exacomp:student'] = 'overview of student actions in a course';
$string['exacomp:use'] = 'use Exabis Competence Grid';
$string['exacomp:deleteexamples'] = 'delete examples';
$string['exacomp:assignstudents'] = 'Assign external trainers';


// === Admin Tabs ===
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Admin Settings';
$string['tab_admin_configuration'] = 'Standards pre-selection';
$string['admin_config_pending'] = 'Standards pre-selection needs to be performed by the Moodle administrator';


// === Teacher Tabs ===
$string['tab_teacher_settings'] = 'Settings';
$string['tab_teacher_settings_configuration'] = 'Configuration';
$string['tab_teacher_settings_selection_st'] = 'Schooltype selection';
$string['tab_teacher_settings_selection'] = 'Subject selection';
$string['tab_teacher_settings_assignactivities'] = 'Assign Moodle activities';
$string['tab_teacher_settings_badges'] = 'Edit badges';
$string['tab_teacher_settings_new_subject'] = 'Create new subject';

// === Student Tabs ===
$string['tab_student_all'] = 'All gained competencies';


// === Generic Tabs (used by Teacher and Students) ===
$string['tab_competence_grid'] = 'Reports';
$string['tab_competence_overview'] = 'Competence grid';
$string['tab_competence_details'] = 'Detailed competence-view';
$string['tab_examples'] = 'Examples and tasks';
$string['tab_badges'] = 'My badges';
$string['tab_competence_profile'] = 'Competence profile';
$string['tab_competence_profile_profile'] = 'Profile';
$string['tab_competence_profile_settings'] = 'Settings';
$string['tab_help'] = 'Help';
$string['tab_profoundness'] = 'Basic & Extended Competencies';
$string['tab_cross_subjects'] = 'Cross-Subjects';
$string['tab_cross_subjects_overview'] = 'Overview';
$string['tab_cross_subjects_course'] = 'Course Cross-Subjects';
$string['tab_weekly_schedule'] = 'Weekly Schedule';
$string['assign_descriptor_to_crosssubject'] = 'Assign the competence "{$a}" to the following Cross-Subjects:';
$string['assign_descriptor_no_crosssubjects_available'] = 'No Cross-Subjects are available.';
$string['first_configuration_step'] = 'The first step of the configuration is to import some data to Exabis Competence Grid.';
$string['second_configuration_step'] = 'In this configuration step you have to pre-select standards.';
$string['next_step'] = 'This configuration step has been completed. Click here to continue configuration.';
$string['next_step_teacher'] = 'The configuration that has to be done by the administrator is now completed. To continue with the course specific configuration click here.';
$string['teacher_first_configuration_step'] = 'The first step of course configuration is to adjust general settings for your course.';
$string['teacher_second_configuration_step'] = 'In the second configuration step topics to work with in this course have to be selected.';
$string['teacher_third_configuration_step'] = 'The next step is to associate Moodle activities with competencies ';
$string['teacher_third_configuration_step_link'] = '(Optional: if you don\'t want to work with activities untick the setting "I want to work with Moodle activities" in the tab "Configuration")';
$string['completed_config'] = 'The configuration of Exabis Competence Grid is completed.';
$string['optional_step'] = 'There are no participants in your course yet. If you want to enrol some please use this link.';
$string['next_step_first_teacher_step'] = 'Click here to continue configuration.';


// === Block Settings ===
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url to a xml file, which is used for keeping the database entries up to date';
$string['settings_autotest'] = 'Automatical gain of competence through quizzes';
$string['settings_autotest_description'] = 'Competences that are associated with quizzes are gained automatically if needed percentage of quiz is reached';
$string['settings_testlimit'] = 'Quiz-percentage needed to gain competence';
$string['settings_testlimit_description'] = 'This percentage has to be reached to gain the competence';
$string['settings_usebadges'] = 'Use badges';
$string['settings_usebadges_description'] = 'Check to associate badges with competences';
$string['settings_enableteacherimport'] = 'Use school specific standards';
$string['settings_enableteacherimport_description'] = 'Check to enable school specific standard import for trainers';
$string['settings_interval'] = 'Unit duration';
$string['settings_interval_description'] = 'Duration of the units in the schedule';
$string['settings_scheduleunits'] = 'Anmount of units';
$string['settings_scheduleunits_description'] = 'Amount of units in the schedule';
$string['settings_schedulebegin'] = 'Schedule begin';
$string['settings_schedulebegin_description'] = 'Begin time for the first unit in the schedule. Format hh:mm';
$string['settings_admin_scheme'] = 'Global grading level';
$string['settings_admin_scheme_description'] = 'Grading can be done on different levels.';
$string['settings_admin_scheme_none'] = 'no global levels';
$string['settings_additional_grading'] = 'Adapted grading';
$string['settings_additional_grading_description'] = 'Grading limited from "not gained(0)" - "completely gained(3)"';
$string['settings_usetimeline'] = 'Use Timeline in profile';
$string['settings_usetimeline_description'] = 'Chronological sequence of gained outcomes';
$string['timeline_teacher'] = 'T';
$string['timeline_student'] = 'S';
$string['timeline_total'] = 'Total';

// === Unit Tests ===
$string['unittest_string'] = 'result_unittest_string';
$string['de:unittest_string2'] = 'result_unittest_string2';
$string['de:unittest_string3'] = 'result_unittest_string3';
$string['de:unittest_param {$a} unittest_param'] = 'result_unittest_param {$a} result_unittest_param';
$string['de:unittest_param2 {$a->val} unittest_param2'] = 'result_unittest_param2 {$a->val} result_unittest_param2';


// === Learning agenda ===
$string['LA_MON'] = 'MON';
$string['LA_TUE'] = 'TUE';
$string['LA_WED'] = 'WED';
$string['LA_THU'] = 'THU';
$string['LA_FRI'] = 'FRI';
$string['LA_todo'] = 'What do I do?';
$string['LA_learning'] = 'What can I learn?';
$string['LA_student'] = 'S';
$string['LA_teacher'] = 'T';
$string['LA_assessment'] = 'assessment';
$string['LA_plan'] = 'working plan';
$string['LA_no_learningagenda'] = 'There is no learning agenda available for this week.';
$string['LA_no_student_selected'] = '-- no student selected --';
$string['LA_select_student'] = 'Please select a student to view his learning agenda.';
$string['LA_no_example'] = 'no example available';
$string['LA_backtoview'] = 'back to original view';
$string['LA_from_n'] = ' from ';
$string['LA_from_m'] = ' from ';
$string['LA_to'] = ' to ';
$string['LA_enddate'] = 'end date';
$string['LA_startdate'] = 'start date';


// === Help ===
$string['help_content'] = '<h1>Introduction Video</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';


// === Import ===
$string['importinfo'] = 'Please create your outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file.';
$string['importwebservice'] = 'It is possible to keep the data up to date via a <a href="{$a}">webservice</a>.';
$string['import_max_execution_time'] = 'Important: The current Serversettings limit the Import to run up to {$a} seconds. If the import takes longer no data will be imported and the browser may display "500 Internal Server Error".';
$string['importdone'] = 'data has already been imported from xml';
$string['importpending'] = 'no data has been imported yet!';
$string['doimport'] = 'Import outcomes/standards';
$string['doimport_again'] = 'Import additional outcomes/standards';
$string['doimport_own'] = 'Import individual outcomes/standards';
$string['delete_own'] = 'Delete individual outcomes/standards';
$string['delete_success'] = 'Individual outcomes/standards have been deleted';
$string['delete_own_confirm'] = 'Are you sure to delete the individual outcomes/standards?';
$string['importsuccess'] = 'data was successfully imported!';
$string['importsuccess_own'] = 'individual data was imported successfully!';
$string['importfail'] = 'an error has occured during import';
$string['noxmlfile'] = 'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.';
$string['oldxmlfile'] = 'You are using an outdated xml-file. Please create new outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';
$string['do_demo_import'] = 'import demo data to see how Exabis Competence Grid works.';


// === Configuration ===
$string['explainconfig'] = 'Your outcomes have already been imported. In this configuration you have to make the selection of the main standards you would like to use in this Moodle installation.';
$string['save_selection'] = 'Save selection';
$string['save_success'] = 'changes were successful';


// === Course-Configuration ===
$string['grading_scheme'] = 'grading scheme';
$string['uses_activities'] = 'I work with Moodle activites';
$string['show_all_descriptors'] = 'Show all outcomes in overview';
$string['show_all_examples'] = 'Show external examples for students';
$string['usedetailpage'] = 'Use detailed overview of competencies';
$string['useprofoundness'] = 'Use basic and extended competencies';
$string['usetopicgrading'] = 'Enable topic gradings';
$string['usesubjectgrading'] = 'Enable subject gradings';
$string['usenumbering'] = 'Enable automatic numbering in the competence grid';
$string['useniveautitleinprofile'] = 'Use level title in competence profile';
$string['usenostudents'] = 'Use without students';
$string['profoundness_0'] = 'not reached';
$string['profoundness_1'] = 'Partially gained';
$string['profoundness_2'] = 'Fully gained';
$string['filteredtaxonomies'] = 'Examples are filtered accordingly to the following taxonomies:';
$string['show_all_taxonomies'] = 'All taxonomies';
$string['warning_use_activities'] = 'Warning: you are now working with Moodle-activites that are associated with competences. Please verify that the same outcomes are used as before.';
$string['delete_unconnected_examples'] = 'If you are deselecting topics which are associated with examples used in the weekly schedule, these examples will be removed.';


// === Badges ===
$string['mybadges'] = 'My badges';
$string['pendingbadges'] = 'Pending badges';
$string['no_badges_yet'] = 'no badges available';
$string['description_edit_badge_comps'] = 'Here you can associate the selected badge with outcomes.';
$string['to_award'] = 'To award this badge in exacomp you have to configure competencies';
$string['to_award_role'] = 'To award this badge in exacomp you have to add the "Manual issue by role" criteria';
$string['ready_to_activate'] = 'This badge is ready to be activated: ';
$string['conf_badges'] = 'configure badges';
$string['conf_comps'] = 'configure competences';


// === Examples ===
$string['sorting'] = 'select sorting: ';
$string['subject'] = 'subjects';
$string['taxonomies'] = 'taxonomies';
$string['show_all_course_examples'] = 'Show examples from all courses';
$string['name_example'] = 'Name';
$string['comp_based'] = 'sort by competencies';
$string['examp_based'] = 'sort by examples';

// === Icons ===
$string['assigned_example'] = 'Assigned Example';
$string['task_example'] = 'Tasks';
$string['extern_task'] = 'External Task';
$string['total_example'] = 'Complete Example';


// === Example Upload ===
$string['example_upload_header'] = 'Upload my own task/example';
$string['taxonomy'] = 'Taxonomy';
$string['descriptors'] = 'Competencies';
$string['filerequired'] = 'A file must be selected.';
$string['titlenotemtpy'] = 'A name is required.';
$string['solution'] = 'Solution';
$string['hide_solution'] = 'Hide solution';
$string['show_solution'] = 'Show solution';
$string['hide_solution_disabled'] = 'The solution is already hidden for all students';
$string['submission'] = 'Submission';
$string['assignments'] = 'Assignments';
$string['files'] = 'Files';
$string['link'] = 'Link';
$string['dataerr'] = 'At least a link or a file are required!';
$string['linkerr'] = 'The given link is not valid!';
$string['isgraded'] = 'The example is already graded and therefore a submission is not possible anymore';
$string['allow_resubmission'] = 'Allow a new submission for this example';
$string['allow_resubmission_info'] = 'The example is now allowed to be resubmited.';


// === Assign competencies ===
$string['delete_confirmation'] = 'Do you really want to delete "{$a}"?';
$string['legend_activities'] = 'Moodle activities';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'No Moodle activities/quizzes have been submitted for this outcome';
$string['legend_upload'] = 'Upload your own task/example';
$string['allniveaus'] = 'All levels';
$string['choosesubject'] = 'Choose subject: ';
$string['choosetopic'] = 'Choose topic';
$string['choosestudent'] = 'Choose student: ';
$string['own_additions'] = 'Curricular additions: ';
$string['delete_confirmation_descr'] = 'Do you really want to delete the competence "{$a}"?';
$string['import_source'] = 'Imported from: {$a}';
$string['local'] = 'Local';
$string['unknown_src'] = 'Unknown source';
$string['override_notice'] = 'This entry was editied by someone else before. Continue?';
$string['unload_notice'] = 'Are you sure? Unsaved changes will be lost.';
$string['example_sorting_notice'] = 'Please save the changes first.';
$string['newsubmission'] = 'New Submission';
$string['value_too_large'] = 'Error: Values above 6.0 are not allowed';
$string['value_too_low'] = 'Error: Values below 1.0 are not allowed';
$string['value_not_allowed'] = 'Error: Values need to be numbers between 1.0 and 6.0';
$string['competence_locked'] = 'Evaluation exists or learning material is used';
// === Example Submission ===
$string['example_submission_header'] = 'Edit example {$a}';
$string['example_submission_info'] = 'You are about to edit the example "{$a}". Your submission will be saved in Exabis ePortfolio and Teachers can view it there.';
$string['example_submission_subject'] = 'New submission';
$string['example_submission_message'] = 'Student {$a->student} handed in a new submission in {$a->course}.';
$string['submissionmissing'] = 'Es müssen zumindest ein Link oder eine Datei abgegeben werden';
$string['usersubmitted'] = ' has submitted the following Moodle activities:';
$string['usersubmittedquiz'] = ' has done the following quizzes:';
$string['usernosubmission'] = ' has not yet submitted any Moodle activities or quizzes associated with this outcome';
$string['usernosubmission_topic'] = ' has not yet submitted any Moodle activities or quizzes associated with this topic';
$string['grading'] = ' Grading: ';
$string['teacher_tipp'] = 'tip';
$string['teacher_tipp_1'] = 'This competence has been associated with ';
$string['teacher_tipp_2'] = ' Moodle activities and has been reached with ';
$string['teacher_tipp_3'] = ' outcomes.';
$string['print'] = 'Print';
$string['eportitems'] = 'This participant has submitted the following ePortfolio artifacts: ';
$string['eportitem_shared'] = ' (shared)';
$string['eportitem_notshared'] = ' (not shared)';
$string['teachershortcut'] = 'T';
$string['studentshortcut'] = 'S';
$string['overview'] = 'This is an overview of all students and the course competencies.';
$string['showevaluation'] = 'To show self-assessment click <a href="{$a}">here</a>';
$string['hideevaluation'] = 'To hide self-assessment click <a href="{$a}">here</a>';
$string['showevaluation_student'] = 'To show trainer-assessment click <a href="{$a}">here</a>';
$string['hideevaluation_student'] = 'To hide trainer-assessment click <a href="{$a}">here</a>';
$string['columnselect'] = 'Column selection';
$string['allstudents'] = 'All students';
$string['nostudents'] = 'No students';
$string['statistic'] = 'Overview';
$string['niveau'] = 'Level';
$string['competence_grid_niveau'] = 'Level';
$string['descriptor'] = 'Competency';
$string['groupsize'] = 'Size of group: ';
$string['assigndone'] = 'task done: ';
$string['assignmyself'] = 'by myself';
$string['assignteacher'] = 'trainer';
$string['assignfrom'] = 'from';
$string['assignuntil'] = 'until';


// === metadata ===
$string['subject_singular'] = 'Field of competence';
$string['comp_field_idea'] = 'Skill';
$string['comp'] = 'Topic';
$string['progress'] = 'Progress';
$string['instruction'] = 'Instruction';
$string['instruction_content'] = 'This is an overview for learning resources that are associated with 
				standards and ticking off competencies for students. Students can
				assess their competencies. Moodle activities that were turned in by
				students are displayed with a red icon. ePortfolio-artifacts of students 
				are displayed in blue icons.';


// === Activities ===
$string['explaineditactivities_subjects'] = '';
$string['column_setting'] = 'hide/display columns';
$string['niveau_filter'] = 'Filter levels';
$string['module_filter'] = 'filter activities';
$string['apply_filter'] = 'apply filter';
$string['no_topics_selected'] = 'configuration of Exabis Competence Grid is not completed yet. please chose a topic that you would like to associate Moodle activities with';
$string['no_activities_selected'] = 'please associate Moodle activities with competences';
$string['no_activities_selected_student'] = 'There is no data available yet';
$string['no_course_activities'] = 'No Moodle activities found in this course - click here to create some.';
$string['all_modules'] = 'all activities';
$string['all_niveaus'] = 'All levels';
$string['tick_some'] = 'Please make a selection!';


// === Competence Grid ===
$string['infolink'] = 'Additional information: ';
$string['textalign'] = 'Switch text align';
$string['selfevaluation'] = 'Self assessment';
$string['teacherevaluation'] = 'Trainer assessment';
$string['competencegrid_nodata'] = 'In case the competency grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.';
$string['statistic_type_descriptor'] = 'Change to descriptor statistics';
$string['statistic_type_example'] = 'Change to example statistics';
$string['reports'] = 'Type of report';
$string['report_competence'] = 'Competencies';
$string['report_detailcompetence'] = 'Child Competencies';
$string['report_examples'] = 'Examples';


// === Detail view ===
$string['detail_description'] = 'Use Moodle activities to evaluate competencies.';


// === Competence Profile ===
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
$string['select_student'] = 'Please select a student first';
$string['my_comps'] = 'My Competencies';
$string['my_items'] = 'My artifacts';
$string['my_badges'] = 'My Badges';
$string['my_periods'] = 'My assessments';
$string['item_type'] = 'Type';
$string['item_link'] = 'Link';
$string['item_file'] = 'File';
$string['item_note'] = 'Note';
$string['item_title'] = 'Title';
$string['item_url'] = 'URL';
$string['period_reviewer'] = 'Reviewer';
$string['period_feedback'] = 'Feedback';
$string['January'] = 'January';
$string['February'] = 'February';
$string['March'] = 'March';
$string['April'] = 'April';
$string['May'] = 'May';
$string['June'] = 'June';
$string['July'] = 'July';
$string['August'] = 'August';
$string['September'] = 'September';
$string['October'] = 'October';
$string['November'] = 'November';
$string['December'] = 'December';
$string['oB'] = 'without evaluation';
$string['nE'] = 'not gained';


// === Competence Profile Settings ===
$string['profile_settings_showonlyreached'] = 'I only want to see already gained outcomes in my competence profile';
$string['profile_settings_choose_courses'] = 'Using Exabis Competence Grid trainers assess your competencies in various subjects. You can select which course to include in the competence profile.';
$string['profile_settings_useexaport'] = 'I want to see competencies used in Exabis ePortfolio within my profile.';
$string['profile_settings_choose_items'] = 'Exabis ePortfolio is used to document your competencies on your individual learning path. You can select which artifacts to include in the competence profile.';
$string['profile_settings_useexastud'] = 'I want to see evaluations from Exabis Student Review.';
$string['profile_settings_no_item'] = 'No Exabis ePortfolio item available, so there is nothing to display.';
$string['profile_settings_no_period'] = 'No review in a period in Exabis Student Review available.';
$string['profile_settings_usebadges'] = 'I want to see badges in my competence profile.';
$string['profile_settings_onlygainedbadges'] = 'I don\'t want to see pending badges.';
$string['profile_settings_badges_lineup'] = 'Badges settings';
$string['profile_settings_showallcomps'] = 'all my competencies';
$string['specificcontent'] = 'site-specific topics';
$string['specificsubject'] = 'site-specific subjects';


// === Profoundness ===
$string['profoundness_description'] = 'Description';
$string['profoundness_basic'] = 'Basic competence';
$string['profoundness_extended'] = 'Extended competence';
$string['profoundness_mainly'] = 'Mainly achieved';
$string['profoundness_entirely'] = 'Entirely achieved';


// === External trainer & eLove ===
$string['block_exacomp_external_trainer_assign_head'] = 'Allow assigning external trainers for students.';
$string['block_exacomp_external_trainer_assign_body'] = 'This is required for using the elove app.';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Allow self-assessment for students in the elove app';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Assign external trainers';
$string['block_exacomp_external_trainer'] = 'Trainer';
$string['block_exacomp_external_trainer_student'] = 'Student';
$string['block_exacomp_external_trainer_allstudents'] = 'All Students';


// === Crosssubjects ===
$string['empty_draft'] = 'New Cross-Subject';
$string['empty_draft_description'] = 'Create your own Cross-Subject - insert new description';
$string['add_drafts_to_course'] = 'Add drafts to course';
$string['crosssubject'] = 'Cross-Subject';
$string['student_name'] = 'Participant';
$string['help_crosssubject'] = 'The compilation of a subject is done for the whole Moodle installation (school) using the tab learning path. Here you can selectively deactivate course-specific competences, sub-competences and materials. Individual learning material can also be added. This is then automatically added to the learning paths.';
$string['description'] = 'Description';
$string['no_student'] = '-- no participant selected --';
$string['no_student_edit'] = 'edit mode - no participant';
$string['save_as_draft'] = 'Save Cross-Subject as draft';
$string['comps_and_material'] = 'outcomes and exercises';
$string['no_crosssubjs'] = 'No Cross-Subjects available.';
$string['delete_drafts'] = 'Delete selected drafts';
$string['share_crosssub'] = 'Share Cross-Subject with participants';
$string['share_crosssub_with_students'] = 'Share Cross-Subject "{$a}" with the following participants: ';
$string['share_crosssub_with_all'] = 'Share Cross-Subject "{$a}" with all participants: ';
$string['new_crosssub'] = 'Create new Cross-Subject';
$string['add_crosssub'] = 'Create Cross-Subject';
$string['nocrosssubsub'] = 'General Cross-Subjects';
$string['delete_crosssub'] = 'Delete Cross-Subject';
$string['confirm_delete'] = 'Do you really want to delete this Cross-Subject?';
$string['no_students_crosssub'] = 'No students are assigend to this Cross-Subject.';
$string['use_available_crosssub'] = 'Use draft for creating new Cross-Subject:';
$string['save_crosssub'] = 'Save changes';
$string['add_content_to_crosssub'] = 'The Cross-Subject is still empty.';
$string['add_descriptors_to_crosssub'] = 'Add descriptor to Cross-Subject';
$string['manage_crosssubs'] = 'Back to overview';
$string['show_course_crosssubs'] = 'Show used Cross-Subjects';
$string['existing_crosssub'] = 'existing cross subjects in this course';
$string['create_new_crosssub'] = 'Create new Cross-Subject';
$string['share_crosssub_for_further_use'] = 'Share the Cross-Subject with students.';
$string['available_crosssubjects'] = 'Available Cross Subjects';
$string['crosssubject_drafts'] = 'Cross-Subject Drafts';
$string['de:Freigegebene Kursthemen'] = 'Published Cross Subjects';
$string['de:Freigabe bearbeiten'] = 'Change Sharing';
$string['de:Kopie als Vorlage speichern'] = 'Create Copy as Draft';
$string['de:Vorlage verwenden'] = 'Use Draft';


// === Associations ===
$string['competence_associations'] = 'Associations';
$string['competence_associations_explaination'] = 'The material {$a} is associated wih the following standards:';


// === Weeky schedule ===
$string['weekly_schedule'] = 'Weekly schedule';
$string['weekly_schedule_added'] = 'Example added to the weekly schedule';
$string['weekly_schedule_already_exists'] = 'Example is already in the weekly schedule';
$string['select_student_weekly_schedule'] = 'Please select a student to view his/her weekly schedule.';
$string['example_pool'] = 'Example pool';
$string['example_trash'] = 'Trash bin';
$string['choosecourse'] = 'Select course: ';
$string['weekly_schedule_added_all'] = 'Example added to the weekly schedule of all students.';
$string['weekly_schedule_already_existing_for_one'] = 'Example has already been added to at least one student\'s weekly schedule.';
$string['weekly_schedule_link_to_grid'] = 'For adding examples to the schedule, please use the overview';
$string['pre_planning_storage'] = 'pre-planning storage';
$string['pre_planning_storage_added'] = 'Example added to the pre-planning storage.';
$string['pre_planning_storage_already_contains'] = 'Example is already in pre-planning storage.';
$string['save_pre_planning_selection'] = 'Add selected examples to weekly schedule of selected students';
$string['empty_pre_planning_storage'] = 'Empty pre-planning storage';
$string['noschedules_pre_planning_storage'] = 'Pre-planning storage has been emptied, use the competence grid to put new examples in the pre-planning storage.';
$string['empty_trash'] = 'Empty trash bin';
$string['empty_pre_planning_confirm'] = 'Examples added from all teachers are deleted, are you sure you want to do this?';
$string['to_weekly_schedule'] = 'To weekly schedule';
$string['blocking_event'] = 'Create blocking event';
$string['blocking_event_title'] = 'title';
$string['blocking_event_create'] = 'Add to pre-planning storage';


// === Notifications ===
$string['notification_submission_subject'] = '{$a->site}: {$a->student} submitted a solution for {$a->example}';
$string['notification_submission_body'] = 'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href="{$viewurl}">{$a->example}</a> </br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_submission_context'] = 'Submission';
$string['notification_grading_subject'] = '{$a->site}: New grading in course {$a->course}';
$string['notification_grading_body'] = 'Dear {$a->receiver}, </br></br>You have got new gradings in {$a->course} from {$a->teacher}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_grading_context'] = 'Grading';
$string['notification_self_assessment_subject'] = '{$a->site}: New self assessments in {$a->course}';
$string['notification_self_assessment_body'] = 'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} has new self assessments in {$a->course}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_self_assessment_context'] = 'Self assessment';
$string['notification_example_comment_subject'] = '{$a->site}: New comment for example {$a->example}';
$string['notification_example_comment_body'] = 'Dear {$a->receiver}, </br></br>{$a->teacher} commented in {$a->course} the example {$a->example}.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_example_comment_context'] = 'Comment';
$string['notification_weekly_schedule_subject'] = '{$a->site}: New example on the schedule';
$string['notification_weekly_schedule_body'] = 'Dear {$a->receiver}, </br></br>{$a->teacher} added an example in {$a->course} to your weekly schedule.</br></br> This message has been generated form moodle site {$a->site}.';
$string['notification_weekly_schedule_context'] = 'Weekly schedule';
$string['inwork'] = '{$a->inWork}/{$a->total} in work';
$string['block_exacomp_notifications_head'] = 'Notifications and Messages';
$string['block_exacomp_notifications_body'] = 'Users will get notified after relevant actions.';



// === Logging ===
$string['block_exacomp_logging_head'] = 'Logging';
$string['block_exacomp_logging_body'] = 'Relevant actions will get logged.';
$string['eventscompetenceassigned'] = 'Competence assigned';
$string['eventsexamplesubmitted'] = 'Example submitted';
$string['eventsexamplegraded'] = 'Example graded';
$string['eventsexamplecommented'] = 'Example commented';
$string['eventsexampleadded'] = 'Example added to weekly schedule';
$string['eventsimportcompleted'] = 'Import completed';
$string['eventscrosssubjectadded'] = 'Cross-Subject added';


// === Statistics ===
$string['process'] = 'State of process';
$string['niveauclass'] = 'Level classification';


// === Message ===
$string['messagetocourse'] = 'Nachricht an alle Kursteilnehmer/innen senden';
$string['messageprovider:submission'] = 'Notify teacher that a student has submitted an item';
$string['messageprovider:grading'] = 'Notify Student that a teacher graded competencies';
$string['messageprovider:self_assessment'] = 'Student assessed some own competencies';
$string['messageprovider:weekly_schedule'] = 'Teacher adds new example to weekly schedule';
$string['messageprovider:comment'] = 'Teacher comments an example';
$string['description_example'] = 'Description';
$string['requirements'] = 'Was du schon können solltest: ';
$string['forwhat'] = 'Wofür du das brauchst: ';
$string['howtocheck'] = 'Wie du dein Können prüfen kannst: ';
$string['reached_topic'] = 'Ich habe diese Kompetenz erreicht: ';
$string['submit_example'] = 'Submit';
