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
$string['tab_help'] = 'Hilfe';

//Block Settings
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten';
$string['settings_alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['settings_alternativedatamodel_description'] = 'Anhaken f�r die Baden W&uuml;rttemberg Version';

//Learning agenda
$string['LA_MO'] = "Mo";
$string['LA_DI'] = "Di";
$string['LA_MI'] = "Mi";
$string['LA_DO'] = "Do";
$string['LA_FR'] = "Fr";
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
$string['importwebservice'] = 'Es besteht auch die Möglichkeit die Daten über ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['importdone'] = 'Die allgemeinen Bildungsstandards sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und wählen Sie anschließend im Tab Schultyp die anzuzeigenden Deskriptorenbereiche aus.';
$string['doimport'] = 'Allgemeine Bildungsstandards importieren';
$string['doimport_again'] = 'Allgemeine Bildungsstandards erneut importieren';
$string['doimport_own'] = 'Schulspezifische Bildungsstandards importieren';
$string['importsuccess'] = 'Daten erfolgreich importiert!';
$string['importsuccess_own'] = 'Eigene Daten erfolgreich importiert!';
$string['importfail'] = 'Es ist ein Fehler aufgetreten.';
$string['noxmlfile'] = 'Ein Import ist derzeit nicht möglich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';