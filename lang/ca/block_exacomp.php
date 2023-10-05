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

$string['exacomp:addinstance'] = 'afegir competències Exabis al curs';
$string['exacomp:myaddinstance'] = "afegir competències Exabis a l'inici";
$string['pluginname'] = 'Competències Exabis';


// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_settings'] = 'Importar';
$string['tab_admin_configuration'] = 'Configuració';

//Teacher Tabs
$string['tab_teacher_settings'] = 'propietats';
$string['tab_teacher_settings_configuration'] = 'Configuració';
$string['tab_teacher_settings_selection_st'] = 'Tipus d\'escola';
$string['tab_teacher_settings_selection'] = 'selecció de matèrial';
$string['tab_teacher_settings_assignactivities'] = 'Assignar activitats';
$string['tab_teacher_settings_badges'] = 'edit badges';

//Student Tabs
$string['tab_student_all'] = 'Totes les competències assolides';

//Generic Tabs (used by Teacher and Students)
$string['tab_competence_overview'] = 'graella de competències';
$string['tab_competence_overview'] = 'Resum de les competències';
//TODO $string['tab_competence_details'] = 'Detailed competence-view';
$string['tab_examples'] = 'Exemples i tasques';
$string['tab_learning_agenda'] = 'agenda d\'aprenentatge';
$string['tab_badges'] = 'my badges';
$string['tab_competence_profile'] = 'Perfil competencial';
$string['tab_competence_profile_profile'] = 'Perfil';
$string['tab_competence_profile_settings'] = 'Propietats';
$string['tab_help'] = 'help';

//Block Settings
$string['settings_xmlserverurl'] = 'URL del servidor';
$string['settings_usedetailpage_description'] = 'Use competences details overview';
$string['settings_autotest'] = 'Automatic test evalutaion';
$string['settings_autotest_description'] = 'Students reach competenes automatically if a test is completed';
$string['settings_testlimit'] = 'Test limit';
$string['settings_testlimit_description'] = 'Students have to reach this limit to gain competences';
$string['settings_usebadges'] = 'Use Badges';
$string['settings_usebadges_description'] = 'Work with Badges associated with competences';

//Learning agenda
$string['LA_MON'] = "DL";
$string['LA_TUE'] = "DM";
$string['LA_WED'] = "DC";
$string['LA_THU'] = "DJ";
$string['LA_FRI'] = "DV";
$string['LA_todo'] = "Quines són les següents tasques?";
$string['LA_learning'] = "Què puc aprendre?";
$string['LA_student'] = "A";
$string['LA_teacher'] = "P";
$string['LA_assessment'] = "avaluació";
$string['LA_plan'] = "Pla de treball";
$string['LA_no_learningagenda'] = 'no learning agenda';
$string['LA_no_student_selected'] = '-- no student selected --';
$string['LA_select_student'] = 'select student';
$string['LA_no_example'] = 'no example available';
$string['LA_backtoview'] = 'back to original view';
$string['LA_from_n'] = ' from ';
$string['LA_from_m'] = ' from ';
$string['LA_to'] = ' to ';
$string['LA_enddate'] = 'end date';
$string['LA_startdate'] = 'start date';

//Help
$string['help_content'] = '<h1>Introduction Video</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';

//Import
$string['importinfo'] = 'Please create your outcomes/standards at <a target="_blank" href="https://comet.edustandards.org">www.edustandards.org</a>';
$string['importwebservice'] = 'It is possible to keep the data up to date via a <a href="{$a}">webservice</a>.';
$string['importdone'] = 'Les dades ja s\'han importat des d\'xml';
$string['importpending'] = 'No s\'han importat les dades!';
$string['doimport'] = 'Importar descriptors';
$string['doimport_again'] = 'Tornar a importar descriptors';
$string['doimport_own'] = 'Importar descriptors individuals';
$string['importsuccess'] = 'Les dades s\'han importat correctament!';
$string['importsuccess_own'] = 'Les dades individuals s\'han importat correctament!';
$string['importfail'] = 'Hi ha hagut un error durant la importació';
$string['noxmlfile'] = 'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.';
$string['oldxmlfile'] = 'You are using an outdated xml-file. Please create new outcomes/standards at <a href="https://comet.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.';

//Configuration
$string['explainconfig'] = 'Per fer servir el mòdul de competències d\'exabis, cal triar el teu tipus d\'escola. Les dades necessàries s\'importaran des de l\'arxiu xml.';
$string['save_selection'] = 'guardar sel·lecció';
$string['save_success'] = 'els canvis s\'han aplicat';

//Course-Configuration
$string['grading_scheme'] = 'esquema d\'avaluació';
$string['uses_activities'] = 'treballo amb activitats';
$string['show_all_descriptors'] = 'mostra totes les descripcions en la visió de conjunt';

//Badges
$string['mybadges'] = 'My badges';
$string['pendingbadges'] = 'Pending badges';
$string['no_badges_yet'] = "no badges available";

//Examples
$string['sorting'] = 'selecciona manera d\'ordenar ';
$string['subject'] = 'assignatures';
$string['taxonomies'] = 'taxonomies';
$string['show_all_course_examples'] = 'Show Examples from all courses';
$string['expandcomps'] = 'expandir tot';
$string['contactcomps'] = 'contraure tot';
//Icons
$string['assigned_example'] = 'Exemple assignat';
$string['task_example'] = 'Tasques';
$string['solution_example'] = 'Solució';
$string['attachement_example'] = 'Arxiu adjunt';
$string['extern_task'] = 'Tasca externa';
$string['total_example'] = 'Exemple complert';

//Example Upload
$string['example_upload_header'] = 'Pujar la meva pròpia tasca o exemple';
$string['taxonomy'] = 'taxonomie';
$string['descriptors'] = 'competències';
$string['descriptors_help'] = 'You can select multible competencies';
$string['filerequired'] = 'cal que pugis un arxiu!';
$string['titlenotemtpy'] = 'A name is required.';

//Assign competencies
$string['save_selection'] = 'guardar sel·lecció';
$string['delete_confirmation'] = 'Do you really want to delete "{$a}"?';
$string['legend_activities'] = 'activitats';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'no s\'ha enviat res per a aquest descriptor/no s\'ha fet cap quiz';
$string['legend_upload'] = 'Pujar la meva pròpia tasca o exemple';

//Icons
$string['usersubmitted'] = '{$a} ha enviat les tasques següents:';
$string['usernosubmission'] = '{$a} no ha enviat cap tasca ni jocs de preguntes associats a aquest descriptor';
$string['grading'] = 'avaluació';

$string['teachershortcut'] = 'P';
$string['studentshortcut'] = 'A';

$string['overview'] = 'Aquí hi ha una visió de conjunt de tots els alumnes, els descriptors i les tasques amb les quals han estat associats.';
$string['showevaluation'] = 'Per mostrar l\'autoavaluació dels alumnes, prem <a href="{$a}">aqui</a>';
$string['hideevaluation'] = 'Per ocultar l\'autoavaluació dels alumnes, prem <a href="{$a}">aqui</a>';
$string['columnselect'] = 'Selecció de columna de la taula';
$string['allstudents'] = 'Tots els alumnes';

$string['assigndone'] = 'Exercise done: ';
$string['assignmyself'] = 'myself';
$string['assignlearningpartner'] = 'learning partner';
$string['assignlearninggroup'] = 'learning group';
$string['assignteacher'] = 'teacher';
$string['assignfrom'] = 'from';
$string['assignuntil'] = 'until';

//Activities
$string['explaineditactivities_subjects'] = 'Aquí pots associar tasques amb descriptors.';
$string['column_setting'] = 'Oculta/mostra columnes';
$string['niveau_filter'] = "filtra nivells";
$string['module_filter'] = 'filter activities';
$string['apply_filter'] = 'apply filter';
$string['no_topics_selected'] = 'Configuration for Exabis Competences hasn\'t been completed. Please select topics first.';
$string['no_activities_selected'] = 'Please associate tasks with competencies.';

//Competence Grid
$string['textalign'] = "Switch text align";
$string['selfevaluation'] = 'Self assessment';
$string['teacherevaluation'] = 'Teacher assessment';
$string['competencegrid_nodata'] = 'In case the competence grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.';

//Detail view
$string['detail_description'] = 'Use activities to evaluate competencies.';

