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

$string['exacomp:addinstance'] = 'afegir competències Exabis al curs';
$string['exacomp:myaddinstance'] = "afegir competències Exabis a l'inici";
$string['pluginname'] = 'Competències Exabis';


// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_import'] = 'Importar';
$string['tab_admin_configuration'] = 'Configuració';

//Teacher Tabs
$string['tab_teacher_settings'] = 'propietats';
$string['tab_teacher_settings_configuration'] = 'Configuració';
$string['tab_teacher_settings_selection_st'] = 'Tipus d\'escola';
$string['tab_teacher_settings_selection'] = 'selecció de matèrial';
$string['tab_teacher_settings_assignactivities'] = 'Assignar activitats';
//TODO
$string['tab_teacher_settings_badges'] = 'edit badges';

//Student Tabs
$string['tab_student_all'] = 'Totes les competències assolides';

//Generic Tabs (used by Teacher and Students)
$string['tab_competence_grid'] = 'graella de competències';
$string['tab_competence_overview'] = 'Resum de les competències';
$string['tab_examples'] = 'Exemples i tasques';
$string['tab_learning_agenda'] = 'agenda d\'aprenentatge';
//TODO
$string['tab_badges'] = 'my badges';
$string['tab_competence_profile'] = 'Perfil competencial';
//TODO
$string['tab_help'] = 'help';

//Block Settings
$string['settings_xmlserverurl'] = 'URL del servidor';
$string['settings_configxmlserverurl'] = 'URL a un arxiu XML, que s\'utilitza per mantenir actualitzades les entrades de la base de dades';
$string['settings_alternativedatamodel'] = 'Versió Baden W&uuml;rttemberg';
$string['settings_alternativedatamodel_description'] = 'Marca per utilitzar la versió Baden W&uuml;rttemberg';
//TODO
$string['settings_usedetailpage'] = 'Detaillierte Kompetenzansicht';
$string['settings_usedetailpage_description'] = 'Sch&uuml;ler hat Zugriff auf detaillierte Kompetenz&uuml;bersicht';
$string['settings_autotest'] = 'Automatische Tests';
$string['settings_autotest_description'] = 'Kompetenzen die mit Tests verbunden sind, werden automatisch &uuml;bernommen, wenn der Test abgegeben wurde';
$string['settings_testlimit'] = 'Testlimit';
$string['settings_testlimit_description'] = 'Dieser Prozentwert muss erreicht werden, damit der Test als bestanden gilt';
$string['settings_usebadges'] = 'Badges verwenden';
$string['settings_usebadges_description'] = 'Anhaken um Badges Kompetenzen zu zuteilen';

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
//TODO
$string['LA_no_learningagenda'] = 'no learning agenda';
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
//TODO
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';
$string['importwebservice'] = 'Es besteht auch die M&ouml;glichkeit die Daten &uuml;ber ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['importdone'] = 'Les dades ja s\'han importat des d\'xml';
$string['importpending'] = 'No s\'han importat les dades!';
$string['doimport'] = 'Importar descriptors';
$string['doimport_again'] = 'Tornar a importar descriptors';
$string['doimport_own'] = 'Importar descriptors individuals';
$string['importsuccess'] = 'Les dades s\'han importat correctament!';
$string['importsuccess_own'] = 'Les dades individuals s\'han importat correctament!';
$string['importfail'] = 'Hi ha hagut un error durant la importació';
//TODO
$string['noxmlfile'] = 'Ein Import ist derzeit nicht m&ouml;glich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';

//Configuration
$string['explainconfig'] = 'Per fer servir el mòdul de competències d\'exabis, cal triar el teu tipus d\'escola. Les dades necessàries s\'importaran des de l\'arxiu xml.';
$string['save_selection'] = 'guardar sel·lecció';
$string['save_success'] = 'els canvis s\'han aplicat';

//Course-Configuration
$string['grading_scheme'] = 'esquema d\'avaluació';
$string['uses_activities'] = 'treballo amb activitats';
$string['show_all_descriptors'] = 'mostra totes les descripcions en la visió de conjunt';
$string['show_all_examples'] = 'Show all examples in overview';

//Badges
//TODO
$string['mybadges'] = 'Meine Auszeichnungen';
$string['pendingbadges'] = 'Anstehende Auszeichnungen';
$string['no_badges_yet'] = "Keine Auszeichnungen verf&uuml;gbar";

//Examples
$string['sorting'] = 'selecciona manera d\'ordenar ';
$string['subject'] = 'assignatures';
$string['taxonomies'] = 'taxonomies';
//TODO
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
//TODO
$string['example_upload_header']  = 'Pujar la meva pròpia tasca o exemple';
$string['taxonomy'] = 'taxonomie';
$string['descriptors'] = 'competències';
//TODO
$string['descriptors_help'] = 'Es k&ouml;nnen mehrere Kompetenzen ausgew&auml;hlt werden.';
$string['filerequired'] = 'cal que pugis un arxiu!';
//TODO
$string['titlenotemtpy'] = 'Es muss ein Name eingegeben werden.';

//Assign competencies
$string['save_selection'] = 'guardar sel·lecció';
//TODO
$string['delete_confirmation'] = 'Soll das Beispiel wirklich gel&ouml;scht werden?';
$string['legend_activities'] = 'activitats';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'no s\'ha enviat res per a aquest descriptor/no s\'ha fet cap quiz';
$string['legend_upload'] = 'Pujar la meva pròpia tasca o exemple';

//Icons
$string['usersubmitted'] = ' ha enviat les tasques següents:';
$string['usersubmittedquiz'] = ' ha completat els jocs de preguntes següents:';
$string['usernosubmission'] = ' no ha enviat cap tasca ni jocs de preguntes associats a aquest descriptor';
$string['usernosubmission_topic'] = ' no ha enviat cap tasca ni jocs de preguntes associats a aquest theme';
$string['grading'] = ' avaluació: ';

$string['teachershortcut'] = 'P';
$string['studentshortcut'] = 'A';

$string['overview'] = 'Aquí hi ha una visió de conjunt de tots els alumnes, els descriptors i les tasques amb les quals han estat associats.';
$string['showevaluation'] = 'Per mostrar l\'autoavaluació dels alumnes, prem <a href="{$a}">aqui</a>';
$string['hideevaluation'] = 'Per ocultar l\'autoavaluació dels alumnes, prem <a href="{$a}">aqui</a>';
$string['columnselect'] = 'Selecció de columna de la taula';
$string['allstudents'] = 'Tots els alumnes';

//TODO
$string['assigndone'] = 'Aufgabe erledigt: ';
$string['assignmyself'] = 'selbst';
$string['assignlearningpartner'] = 'Lernpartner';
$string['assignlearningrgoup'] = 'Lerngruppe';
$string['assignteacher'] = 'Lehrkraft';
$string['assignfrom'] = 'von';
$string['assignuntil'] = 'bis';
$string['assignlearninggroup'] = 'Lerngruppe';

//Activities
$string['explaineditactivities_subjects'] = 'Aquí pots associar tasques amb descriptors.';
$string['column_setting'] = 'Oculta/mostra columnes';
$string['niveau_filter'] = "filtra nivells";
$string['module_filter'] = 'filter activities';
$string['apply_filter'] = 'apply filter';
