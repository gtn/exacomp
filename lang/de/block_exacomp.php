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

$string['exacomp:addinstance'] = 'Exabis Competencies auf Kursseite anlegen';
$string['exacomp:myaddinstance'] = 'Exabis Competencies auf Startseite anlegen';
$string['pluginname'] = 'Exabis Competencies';

// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_import'] = 'Import';
$string['tab_admin_configuration'] = 'Konfiguration';

//Teacher Tabs
$string['tab_teacher_settings'] = 'Einstellungen';
$string['tab_teacher_settings_configuration'] = 'Konfiguration';
$string['tab_teacher_settings_selection_st'] = 'Schultyp-Auswahl';
$string['tab_teacher_settings_selection'] = 'Gegenstands-Auswahl';
$string['tab_teacher_settings_assignactivities'] = 'Aktivit&auml;ten zuordnen';
$string['tab_teacher_settings_badges'] = 'Auszeichnungen bearbeiten';

//Student Tabs
$string['tab_student_all'] = 'Alle erworbenen Kompetenzen';

//Generic Tabs (used by Teacher and Students)
$string['tab_competence_grid'] = 'Kompetenzraster';
$string['tab_competence_overview'] = 'Kompetenz-&Uuml;berblick';
$string['tab_competence_details'] = 'Kompetenz-Detailansicht';
$string['tab_examples'] = 'Beispiele und Aufgaben';
$string['tab_learning_agenda'] = 'Lernagenda';
$string['tab_badges'] = 'Meine Auszeichnungen';
$string['tab_competence_profile'] = 'Kompetenzprofil';
$string['tab_competence_profile_profile'] = 'Profil';
$string['tab_competence_profile_settings'] = 'Einstellungen';
$string['tab_help'] = 'Hilfe';
$string['tab_skillmanagement'] = 'Erstelle deine Kompetenzen';

//Block Settings
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten';
$string['settings_alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['settings_alternativedatamodel_description'] = 'Anhaken f&uuml;r die Baden W&uuml;rttemberg Version';
$string['settings_usedetailpage'] = 'Detaillierte Kompetenzansicht';
$string['settings_usedetailpage_description'] = 'Sch&uuml;ler hat Zugriff auf detaillierte Kompetenz&uuml;bersicht';
$string['settings_autotest'] = 'Automatische Tests';
$string['settings_autotest_description'] = 'Kompetenzen die mit Tests verbunden sind, werden automatisch &uuml;bernommen, wenn der Test abgegeben wurde';
$string['settings_testlimit'] = 'Testlimit';
$string['settings_testlimit_description'] = 'Dieser Prozentwert muss erreicht werden, damit der Test als bestanden gilt';
$string['settings_usebadges'] = 'Badges verwenden';
$string['settings_usebadges_description'] = 'Anhaken um Badges Kompetenzen zu zuteilen';
$string['settings_skillmanagement'] = 'Skillmanagement verwenden';
$string['settings_skillmanagement_description'] = 'Anhaken um Skillmanagement-Tool anstelle von Import verwenden';

//Learning agenda
$string['LA_MON'] = "Mo";
$string['LA_TUE'] = "Di";
$string['LA_WED'] = "Mi";
$string['LA_THU'] = "Do";
$string['LA_FRI'] = "Fr";
$string['LA_todo'] = "Was mache ich?";
$string['LA_learning'] = "Was kann ich lernen?";
$string['LA_student'] = "S";
$string['LA_teacher'] = "L";
$string['LA_assessment'] = "Einsch&auml;tzung";
$string['LA_plan'] = "Arbeitsplan";
$string['LA_no_learningagenda'] = 'Es sind keine Lernagenden in der ausgew&auml;hlten Woche vorhanden';
$string['LA_no_student_selected'] = '-- kein Sch&uuml;ler ausgew&auml;hlt --';
$string['LA_select_student'] = 'Sch&uuml;ler ausw&auml;hlen';
$string['LA_no_example'] = 'Kein Beispiel zugeordnet';
$string['LA_backtoview'] = 'Zur&uuml;ck zur Originalansicht';
$string['LA_from_n'] = ' von ';
$string['LA_from_m'] = ' vom ';
$string['LA_to'] = ' bis zum ';
$string['LA_enddate']='Enddatum';
$string['LA_startdate']='Startdatum';

//Help
$string['help_content'] = '<h1>Video zur Einf&uuml;hrung</h1>
Kompetenzorientierter Unterricht ist in aller Munde - und Moodle kann dabei mit Hilfe des Blockes exabis Competencies wertvolle Unterst&uuml;tzung leisten.
<a href="http://www.youtube.com/watch?v=FQtCrlSNUEQ">http://www.youtube.com/watch?v=FQtCrlSNUEQ</a>

<h3>Die AdministratorInnen-Rolle</h3>Der Administrator/Die Administratorin einer Moodle-Instanz muss bestimmte Vorarbeiten leisten, damit der Block exabis Competencies verwendet werden kann. Informationen dazu liefert dieses Video.
<a href="http://www.youtube.com/watch?v=a7h_8EtQM9A">http://www.youtube.com/watch?v=a7h_8EtQM9A</a>

<h3>Die TrainerInnen-Rolle</h3>Die Lehrenden k&ouml;nnen mit Hilfe des Blockes einem Kurs bestimmte Kompetenzen zuordnen und den Kompetenzerwerb anschlie&szlig;end auch bewerten.
<a href="http://www.youtube.com/watch?v=gxSrXa4Ynik">http://www.youtube.com/watch?v=gxSrXa4Ynik</a>

<h3>Die TeilnehmerInnen-Rolle</h3>Lernende k&ouml;nnen mit Hilfe von Exabis Competencies u.a. ihren Kompetenzerwerb selbst einsch&auml;tzen und die eigene Einsch$auml;tzung unkompliziert der der Lehrkraft gegen&uuml;berstellen.
<a href="http://www.youtube.com/watch?v=DdAOIiXXhZ8">http://www.youtube.com/watch?v=DdAOIiXXhZ8</a>

<h3>Das Bildungsstandards-Erfassungstool</h3>Mit Hilfe dieses Tools k&ouml;nnen Schulen erg&auml;nzend zu den &ouml;sterreichischen Bildungsstandards eigene Standards erstellen, erfassen und verwalten.
<a href="http://www.youtube.com/watch?v=CEfFjo-R558">http://www.youtube.com/watch?v=CEfFjo-R558</a>

<h3>ePOP</h3>Das "elektronische, pers&ouml;nlichkeitsorientierte Portfolio" bringt Portfolioarbeit mit Bildungsstandards aufs Smartphone.
<a href="http://www.youtube.com/watch?v=JLYrTuiil2E">http://www.youtube.com/watch?v=JLYrTuiil2E</a>

ePortfolio am Smartphone
Wie wird die App ePOP bedient? Dieses Video liefert einen kompakten &Uuml;berblick.
<a href="http://www.youtube.com/watch?v=v2UbS7sUaRI#t=127">http://www.youtube.com/watch?v=v2UbS7sUaRI#t=127</a>

ePOP-Kontinente:
ePOP erlaubt die individuelle Strukturierung der Inhalte.
<a href="http://www.youtube.com/watch?v=gwqVm5R1Dvo#t=54">http://www.youtube.com/watch?v=gwqVm5R1Dvo#t=54</a>
';

//Import
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';
$string['importwebservice'] = 'Es besteht auch die M&ouml;glichkeit die Daten &uuml;ber ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['importdone'] = 'Die allgemeinen Bildungsstandards sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und w&auml;hlen Sie anschlie&szlig;end im Tab Schultyp die anzuzeigenden Deskriptorenbereiche aus.';
$string['doimport'] = 'Allgemeine Bildungsstandards importieren';
$string['doimport_again'] = 'Allgemeine Bildungsstandards erneut importieren';
$string['doimport_own'] = 'Schulspezifische Bildungsstandards importieren';
$string['importsuccess'] = 'Daten erfolgreich importiert!';
$string['importsuccess_own'] = 'Eigene Daten erfolgreich importiert!';
$string['importfail'] = 'Es ist ein Fehler aufgetreten.';
$string['noxmlfile'] = 'Ein Import ist derzeit nicht m&ouml;glich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';

//Configuration
$string['explainconfig'] = 'Um das Modul exabis competences verwenden zu k&ouml;nnen, m&uuml;ssen hier die Schultypen der Moodle-Instanz selektiert werden.';
$string['save_selection'] = 'Auswahl speichern';
$string['save_success'] = '&Auml;nderungen erfolgreich &uuml;bernommen';

//Course-Configuration
$string['grading_scheme'] = 'Bewertungsschema';
$string['uses_activities'] = 'Ich verwende Moodle Aktivit&auml;ten zur Beurteilung';
$string['show_all_descriptors'] = 'Alle Deskriptoren im &Uuml;berblick anzeigen';
$string['show_all_examples'] = 'Externe Beispiele im &Uuml;berblick anzeigen';

//Badges
$string['mybadges'] = 'Meine Auszeichnungen';
$string['pendingbadges'] = 'Anstehende Auszeichnungen';
$string['no_badges_yet'] = "Keine Auszeichnungen verf&uuml;gbar";

//Examples
$string['sorting'] = 'Sortierung w&auml;hlen: ';
$string['subject'] = 'Gegenst&auml;nde';
$string['taxonomies'] = 'Taxonomien';
$string['show_all_course_examples'] = 'Beispiele aus allen Kursen anzeigen';
$string['expandcomps'] = 'Alle &ouml;ffnen';
$string['contactcomps'] = 'Alle schlie&szlig;en';
//Icons
$string['assigned_example'] = 'Assigned Example';
$string['task_example'] = 'Aufgabenstellung';
$string['solution_example'] = 'L&ouml;sung';
$string['attachement_example'] = 'Anhang';
$string['extern_task'] = 'Externe Aufgabenstellung';
$string['total_example'] = 'Gesamtbeispiel';
//Example Upload
$string['example_upload_header']  = 'Eigenes Beispiel raufladen';
$string['taxonomy'] = 'Taxonomie';
$string['descriptors'] = 'Kompetenzen';
$string['descriptors_help'] = 'Es k&ouml;nnen mehrere Kompetenzen ausgew&auml;hlt werden.';
$string['filerequired'] = 'Es muss eine Datei ausgew&auml;hlt sein.';
$string['titlenotemtpy'] = 'Es muss ein Name eingegeben werden.';
$string['lisfilename'] = 'Dateiname nach LS Vorgabe generieren';

//Assign competencies
$string['save_selection'] = 'Auswahl speichern';
$string['delete_confirmation'] = 'Soll das Beispiel wirklich gel&ouml;scht werden?';
$string['legend_activities'] = 'Aktivit&auml;ten';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Keine Aktivit&auml;t/Quiz f&uuml;r diese Kompetenz abgegeben';
$string['legend_upload'] = 'Eigenes Beispiel hochladen';
$string['alltopics'] = 'Alle Teilbereiche';
$string['choosesubject'] = "Fach auswählen: ";
$string['choosetopic'] = "Teilkompetenzbereich/Leitidee auswählen";
$string['choosestudent'] = "Schüler auswählen: ";
$string['selectall'] = 'Alle auswählen';

//Icons
$string['usersubmitted'] = ' hat folgende Aktivit&auml;ten abgegeben:';
$string['usersubmittedquiz'] = ' hat folgende Tests durchgef&uuml;hrt:';
$string['usernosubmission'] = ' hat keine Moodle-Aufgaben zu diesem Deskriptor abgegeben und keinen Test durchgef&uuml;hrt.';
$string['usernosubmission_topic'] = ' hat keine Moodle-Aufgaben zu diesem Thema abgegeben und keinen Test durchgef&uuml;hrt.';
$string['grading'] = ' Bewertung: ';

$string['teachershortcut'] = 'L';
$string['studentshortcut'] = 'S';

$string['overview'] = 'Der Kompetenz-&Uuml;berblick listet Sch&uuml;ler und die im Kurs aktivierten Kompetenzen auf.';
$string['showevaluation'] = 'Um die Selbsteinsch&auml;tzung zu aktivieren klicke <a href="{$a}">hier</a>';
$string['hideevaluation'] = 'Um die Selbsteinsch&auml;tzung zu deaktivieren klicke <a href="{$a}">hier</a>';
$string['columnselect'] = 'Spaltenauswahl';
$string['allstudents'] = 'Alle Sch&uuml;ler';

$string['assigndone'] = 'Aufgabe erledigt: ';
$string['assignmyself'] = 'selbst';
$string['assignlearningpartner'] = 'Lernpartner';
$string['assignlearningrgoup'] = 'Lerngruppe';
$string['assignteacher'] = 'Lehrkraft';
$string['assignfrom'] = 'von';
$string['assignuntil'] = 'bis';
$string['assignlearninggroup'] = 'Lerngruppe';

//Activities
$string['explaineditactivities_subjects'] = 'Hier k&ouml;nnen Sie den erstellten Aufgaben Kompetenzen zuordnen.';
$string['column_setting'] = 'Spalten aus/einblenden';
$string['niveau_filter'] = "Niveaus filtern";
$string['module_filter'] = 'Aktivit&auml;ten filtern';
$string['apply_filter'] = 'Filter anwenden';
$string['no_topics_selected'] = 'Konfiguration für Exabis Competencies wurde noch nicht abgeschlossen. Bitte w&auml;hlen Sie erst Gegenst&auml;nde aus, denen Sie dann Aktivit&auml;ten zuordnen k&ouml;nnen.';
$stinrg['no_activities_selected'] = 'Bitte ordnen Sie den erstellen Aufgaben Kompetenzen zu.';

//Competence Grid
$string['textalign'] = "Switch text align";
$string['selfevaluation'] = 'Selbsteinschätzung';
$string['teacherevaluation'] = 'Lehrereinschätzung';

//LIS Strings
if(get_config('exacomp','alternativedatamodel')) {
	/*langstrings for alternativedatamodel--*/
	if (file_exists($CFG->dirroot . '/blocks/exacomp/block_exacomp_overlaystatic.php')){
		require $CFG->dirroot . '/blocks/exacomp/block_exacomp_overlaystatic.php';
	}
	/*langstrings from other systems*/
	if (file_exists($CFG->dirroot . '/blocks/exacomp/block_exacomp_overlaydynamic.php')){
		$CFG->dirroot . '/blocks/exacomp/block_exacomp_overlaydynamic.php';
	}
}