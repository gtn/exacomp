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
$string['pluginname'] = 'Exabis Competencies';
 
$string['exacomp:addinstance'] = 'Exabis Competencies auf Kursseite anlegen';
$string['exacomp:myaddinstance'] = 'Exabis Competencies auf Startseite anlegen';
$string['exacomp:teacher'] = 'Übersicht der Lehrerfunktionen in einem Kurs';
$string['exacomp:admin'] = 'Übersicht der Administratorfunktionen in einem Kurs';
$string['exacomp:student'] = 'Übersicht der Teilnehmerfunktionen in einem Kurs';
$string['exacomp:use'] = 'Nutzung';
$string['exacomp:deleteexamples'] = 'Beispiele löschen';

// TABS and PAGE IDENTIFIERS
// Admin Tabs
$string['tab_admin_import'] = 'Import';
$string['tab_admin_configuration'] = 'Vorauswahl der Standards';

//Teacher Tabs
$string['tab_teacher_settings'] = 'Einstellungen';
$string['tab_teacher_settings_configuration'] = 'Konfiguration';
$string['tab_teacher_settings_selection_st'] = 'Schultyp-Auswahl';
$string['tab_teacher_settings_selection'] = 'Gegenstands-Auswahl';
$string['tab_teacher_settings_assignactivities'] = 'Moodle-Aktivit&auml;ten zuordnen';
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
$string['tab_skillmanagement'] = 'Kompetenzen-Verwaltung';
$string['tab_teacher_demo_settings'] = 'Mit Demo-Daten arbeiten';
$string['tab_profoundness'] = 'Grund/Erweiterungskompetenzen';

$string['first_configuration_step'] = 'Der erste Konfigurationsschritt besteht darin, Daten in das Exabis Competencies Modul zu importieren.';
$string['second_configuration_step'] = 'In diesem Konfigurationsschritt muss eine Vorauswahl für die Standards getroffen werden, damit das Modul verwendet werden kann. Diese Einstellungen sind unabh&auml;ngig vom Kurs für die gesamte Moodle-Installation g&uuml;ltig.';
$string['next_step'] = 'Dieser Konfigurationsschritt wurde abgeschlossen. Klicken Sie hier um zum N&auml;chsten zu gelangen.';
$string['next_step_teacher'] = 'Die Konfiguration, die vom Administrator vorgenommen werden muss, ist hiermit abgeschlossen. Um mit der kursspezifischen Konfiguration fortzufahren klicken Sie hier.';
$string['teacher_first_configuration_step'] = 'Im ersten Konfigurationsschritt der Kurs-Standards müssen einige generelle Einstellungen getroffen werden.';
$string['teacher_second_configuration_step'] = 'Im zweite Konfigurationsschritt müssen Themenbereiche ausgewählt werden mit denen Sie in diesem Kurs arbeiten möchten.';
$string['teacher_third_configuration_step'] = 'Im nächsten Schritt werden Moodle-Aktivit&auml;ten mit Kompetenzen assoziiert. '; 
$string['teacher_third_configuration_step_link'] = '(Optional: Wenn Sie nicht mit Moodle-Aktivit&auml;ten arbeiten m&ouml;chten, dann entfernen Sie das Häkchen "Ich m&ouml;chte mit Moodle-Aktivit&auml;ten arbeiten" im Tab "Konfiguration".)';
$string['completed_config'] = 'Die Exabis Competencies Konfiguration wurde abgeschlossen.';
$string['optional_step'] = 'In Ihrem Kurs sind noch keine Teilnehmer eingeschrieben, bet&auml;tigen Sie diesen Link wenn Sie das jetzt machen m&ouml;chten.';

//Block Settings
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten';
$string['settings_alternativedatamodel'] = 'Baden W&uuml;rttemberg Version';
$string['settings_alternativedatamodel_description'] = 'Anhaken f&uuml;r die Baden W&uuml;rttemberg Version';
$string['settings_autotest'] = 'Automatischer Kompetenzerwerb durch Tests';
$string['settings_autotest_description'] = 'Kompetenzen die mit Tests verbunden sind, gelten automatisch als erworben, wenn der angegebene Test-Prozentwert erreicht wurde';
$string['settings_testlimit'] = 'Testlimit in %';
$string['settings_testlimit_description'] = 'Dieser Prozentwert muss erreicht werden, damit die Kompetenz als erworben gilt';
$string['settings_usebadges'] = 'Badges/Auszeichnungen verwenden';
$string['settings_usebadges_description'] = 'Anhaken um den Badges/Auszeichnungen Kompetenzen zuzuteilen';
$string['settings_skillmanagement'] = 'Skills-Management verwenden';
$string['settings_skillmanagement_description'] = 'Anhaken um Skills-Management-Tool anstelle des Imports verwenden';
$string['settings_enableteacherimport'] = 'Schulspezifische Standards verwenden';
$string['settings_enableteacherimport_description'] = 'Anhaken um Lehrern/Kurstrainern zu erlauben, eigene, schulspezifische Standards zu importieren';

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
$string['LA_no_learningagenda'] = 'Es sind keine Lernagenden in der ausgew&auml;hlten Woche vorhanden.';
$string['LA_no_student_selected'] = '-- kein Kursteilnehmer ausgew&auml;hlt --';
$string['LA_select_student'] = 'W&auml;hlen Sie bitte einen Kursteilnehmer aus, um seine Lernagenda einzusehen.';
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

<h3>Die TeilnehmerInnen-Rolle</h3>Lernende k&ouml;nnen mit Hilfe von Exabis Competencies u.a. ihren Kompetenzerwerb selbst einsch&auml;tzen und die eigene Einsch$auml;tzung unkompliziert der des Trainers gegen&uuml;berstellen.
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
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a target="_blank" href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a target="_blank" href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch. Für österreichische Standards besuchen Sie bitte <a target="_blank" href="http://bist.edugroup.at">http://bist.edugroup.at</a>';
$string['importwebservice'] = 'Es besteht auch die M&ouml;glichkeit die Daten &uuml;ber ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['importdone'] = 'Die allgemeinen Bildungsstandards sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und w&auml;hlen Sie anschlie&szlig;end im Tab "Konfiguration" die anzuzeigenden Deskriptorenbereiche aus.';
$string['doimport'] = 'Allgemeine Bildungsstandards importieren';
$string['doimport_again'] = 'Allgemeine Bildungsstandards erneut importieren';
$string['doimport_own'] = 'Schulspezifische Bildungsstandards importieren';
$string['delete_own'] = 'Schulspezifische Bildungsstandards löschen';
$string['delete_success'] = 'Schulspezifische Bildungsstandards wurden gelöscht';
$string['delete_own_confirm'] = 'Schulspezifische Bildungsstandards wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.';
$string['importsuccess'] = 'Daten erfolgreich importiert!';
$string['importsuccess_own'] = 'Eigene Daten erfolgreich importiert!';
$string['importfail'] = 'Es ist ein Fehler aufgetreten.';
$string['noxmlfile'] = 'Ein Import ist derzeit nicht m&ouml;glich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';
$string['do_demo_import'] = 'Importieren Sie einen Demodatensatz, um zu sehen wie Exabis Competencies funktioniert.';

//Configuration
$string['explainconfig'] = '';
$string['save_selection'] = 'Auswahl speichern';
$string['save_success'] = '&Auml;nderungen erfolgreich &uuml;bernommen';

//Course-Configuration
$string['grading_scheme'] = 'Bewertungsschema';
$string['uses_activities'] = 'Ich verwende Moodle Aktivit&auml;ten zur Beurteilung';
$string['show_all_descriptors'] = 'Alle Deskriptoren im &Uuml;berblick anzeigen';
$string['show_all_examples'] = 'Externe Beispiele für Teilnehmer anzeigen';
$string['usedetailpage'] = 'Detaillierte Kompetenzansicht verwenden';
$string['useprofoundness'] = 'Erweiterungs- und Grundkompetenzen verwenden';
$string['profoundness_0'] = 'Nicht erreicht';
$string['profoundness_1'] = 'Zum Teil erreicht';
$string['profoundness_2'] = 'Erreicht';
$string['filteredtaxonomies'] = 'Beispiele werden anhand der ausgewählten Taxonomien verwendet:';
$string['show_all_taxonomies'] = 'Alle Taxonomien';
$string['warning_use_activities'] = 'Hinweis: Sie arbeiten jetzt mit Moodle-Aktivitäten die mit Kompetenzen verknüpft sind. Stellen Sie sicher, dass in diesem Kurs mit den selben Kompetenzen weitergearbeitet wird.';

//Badges
$string['mybadges'] = 'Meine Auszeichnungen';
$string['pendingbadges'] = 'Anstehende Auszeichnungen';
$string['no_badges_yet'] = "Keine Auszeichnungen verfügbar";
$string['description_edit_badge_comps'] = 'Hier können Sie der ausgewählten Auszeichnung Kompetenzen zuordnen.';
$string['to_award'] = 'Um diese Auszeichnung zu erwerben, müssen Kompetenzen zugeordnet werden.';
$string['to_award_role'] = 'Um diese Auszeichnung zu erwerben, müssen sie das "manuelle Verleihung" Kriterium hinzufügen.';
$string['ready_to_activate'] = 'Diese Auszeichnung kann aktiviert werden: ';
$string['conf_badges'] = 'Auszeichnungen konfigurieren';
$string['conf_comps'] = 'Kompetenzen zuordnen';

//Examples
$string['sorting'] = 'Sortierung w&auml;hlen: ';
$string['subject'] = 'Gegenst&auml;nde';
$string['taxonomies'] = 'Taxonomien';
$string['show_all_course_examples'] = 'Beispiele aus allen Kursen anzeigen';
$string['expandcomps'] = 'Alle &ouml;ffnen';
$string['contactcomps'] = 'Alle schlie&szlig;en';
$string['name_example'] = 'Name';

//Icons
$string['assigned_example'] = 'Assigned Example';
$string['task_example'] = 'Aufgabenstellung';
$string['solution_example'] = 'L&ouml;sung';
$string['attachement_example'] = 'Anhang';
$string['extern_task'] = 'Externe Aufgabenstellung';
$string['total_example'] = 'Gesamtbeispiel';

//Example Upload
$string['example_upload_header']  = 'Eigenes Beispiel hochladen';
$string['taxonomy'] = 'Taxonomie';
$string['descriptors'] = 'Kompetenzen';
$string['descriptors_help'] = 'Es k&ouml;nnen mehrere Kompetenzen ausgew&auml;hlt werden.';
$string['filerequired'] = 'Es muss eine Datei ausgew&auml;hlt sein.';
$string['titlenotemtpy'] = 'Es muss ein Name eingegeben werden.';
$string['lisfilename'] = 'Dateiname nach LS Vorgabe generieren';
$string['solution'] = 'Lösung';
$string['link'] = 'Link';
$string['dataerr'] = 'Es muss zumindest ein Link oder eine Datei hochgeladen werden!';
$string['linkerr'] = 'Bitte geben Sie einen korrekten Link ein!';

//Assign competencies
$string['save_selection'] = 'Auswahl speichern';
$string['delete_confirmation'] = 'Soll das Beispiel wirklich gelöscht werden?';
$string['legend_activities'] = 'Moodle-Aktivit&auml;ten';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Keine Moodle-Aktivit&auml;t/Quiz f&uuml;r diese Kompetenz abgegeben';
$string['legend_upload'] = 'Eigenes Beispiel hochladen';
$string['alltopics'] = 'Alle Teilbereiche';
$string['choosesubject'] = "Fach ausw&auml;hlen: ";
$string['choosetopic'] = "Teilkompetenzbereich/Leitidee ausw&auml;hlen";
$string['choosestudent'] = "Kursteilnehmer ausw&auml;hlen: ";
$string['selectall'] = 'Alle ausw&auml;hlen';

//Icons
$string['usersubmitted'] = ' hat folgende Moodle-Aktivit&auml;ten abgegeben:';
$string['usersubmittedquiz'] = ' hat folgende Tests durchgef&uuml;hrt:';
$string['usernosubmission'] = ' hat keine Moodle-Aufgaben zu diesem Deskriptor abgegeben und keinen Test durchgef&uuml;hrt.';
$string['usernosubmission_topic'] = ' hat keine Moodle-Aufgaben zu diesem Thema abgegeben und keinen Test durchgef&uuml;hrt.';
$string['grading'] = ' Bewertung: ';
$string['teacher_tipp'] = 'Tipp';
$string['teacher_tipp_1'] = 'Diese Kompetenz wurde bei ';
$string['teacher_tipp_2'] = ' Moodle-Aktivit&auml;t(en) zugeordnet und bereits bei ';
$string['teacher_tipp_3'] = ' Moodle-Aktivit&auml;t(en) in der Kompetenz-Detailansicht erf&uuml;llt.';

$string['eportitems'] = 'Dieser Teilnehmer hat folgende E-Portfolio-Artefakte zu diesem Deskriptor eingereicht: ';
$string['eportitem_shared'] = ' (geteilt)';
$string['eportitem_notshared'] = ' (nicht geteilt)';

$string['teachershortcut'] = 'L';
$string['studentshortcut'] = 'S';

$string['overview'] = 'Der Kompetenz-&Uuml;berblick listet Teilnehmer/innen und die im Kurs aktivierten Kompetenzen auf.';
$string['showevaluation'] = 'Um die Selbsteinsch&auml;tzung zu aktivieren, klicken Sie <a href="{$a}">hier</a>.';
$string['hideevaluation'] = 'Um die Selbsteinsch&auml;tzung zu deaktivieren, klicken Sie <a href="{$a}">hier</a>.';
$string['showevaluation_student'] = 'Um die Einsch&auml;tzung des Trainers zu aktivieren, klicke <a href="{$a}">hier</a>.';
$string['hideevaluation_student'] = 'Um die Einsch&auml;tzung des Trainers zu deaktivieren, klicke <a href="{$a}">hier</a>.';
$string['columnselect'] = 'Spaltenauswahl';
$string['allstudents'] = 'Alle  Kursteilnehmer';

$string['assigndone'] = 'Aufgabe erledigt: ';
$string['assignmyself'] = 'selbst';
$string['assignlearningpartner'] = 'Lernpartner';
$string['assignlearningrgoup'] = 'Lerngruppe';
$string['assignteacher'] = 'Trainer';
$string['assignfrom'] = 'von';
$string['assignuntil'] = 'bis';
$string['assignlearninggroup'] = 'Lerngruppe';

//metadata
$string['subject_singular'] = 'Schultyp';
$string['comp_field_idea'] = 'Fach';
$string['comp'] = 'Teilkompetenzbereich';
$string['progress'] = 'Fortschritt';
$string['instruction'] = 'Erläuterung';
$string['instruction_content'] = 'Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche 
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden. 
				Darüber hinaus können Sie das Erreichen der Teilkompetenzen eintragen. 
				Schüler/innen können sich darüber hinaus selbst einschätzen. Mit Kompetenzen 
				verknüpfte Moodle-Aufgaben werden bei Einreichung durch Schüler/innen
				mit rotem Icon dargestellt. Leistungsnachweise die eigeninitiativ über
				ePortfolio-Arbeit durch Schüler/innen beigesteuert werden, werden mit
				einem blauen Icon dargestellt.';

//Activities
$string['explaineditactivities_subjects'] = '';
$string['column_setting'] = 'Spalten aus/einblenden';
$string['niveau_filter'] = "Niveaus filtern";
$string['module_filter'] = 'Aktivit&auml;ten filtern';
$string['apply_filter'] = 'Filter anwenden';
$string['no_topics_selected'] = 'Konfiguration für Exabis Competencies wurde noch nicht abgeschlossen. Bitte w&auml;hlen Sie zuerst Gegenst&auml;nde aus, denen Sie dann Moodle-Aktivit&auml;ten zuordnen k&ouml;nnen.';
$string['no_activities_selected'] = 'Bitte ordnen Sie den erstellen Moodle-Aktivitäten Kompetenzen zu.';
$string['no_activities_selected_student'] = 'In diesem Bereich sind derzeit keine Daten vorhanden.';
$string['no_course_activities'] = 'In diesem Kurs wurden noch keine Moodle-Aktivit&auml;ten erstellt, klicken Sie hier um dies nun zu tun.';
$string['all_modules'] = 'Alle Aktivitäten';
$string['all_niveaus'] = 'Alle Niveaustufen';
$string['tick_some'] = 'Bitte treffen Sie eine Auswahl!';

//Competence Grid
$string['infolink'] = 'Weiter Informationen: ';
$string['textalign'] = "Textuelle Ausrichtung ändern";
$string['selfevaluation'] = 'Selbsteinschätzung';
$string['teacherevaluation'] = 'Einschätzung des Beurteilenden';
$string['competencegrid_nodata'] = 'Sollte der Kompetenzraster leer sein, wurden für die Deskriptoren des ausgewählten Gegenstands keine Niveaus in den Daten definiert';

//Detail view
$string['detail_description'] = 'Hier kann mit Hilfe von Aktivitäten eine Kompetenz beurteilt werden.';

//Competence Profile
$string['name'] = 'Name';
$string['city'] = 'Wohnort';
$string['course'] = 'Kurs';
$string['gained'] = 'Erreicht';
$string['total'] = 'Gesamt';
$string['allcourses'] = 'Alle Kurse';
$string['pendingcomp'] = 'Ausstehende Kompetenzen';
$string['teachercomp'] = 'Erreichte Kompetenzen';
$string['studentcomp'] = 'Laut Selbsteinschätzung erreichte Kompetenzen';
$string['radargrapherror'] = 'Der Radargraph kann nur bei 3-7 Achsen dargestellt werden';
$string['nodata'] = 'Es sind keine Daten vorhanden.';
$string['item_no_comps'] = 'Zu folgenden Artefakten wurden noch keine Kompetenzen zugeordnet:';
$string['select_student'] = 'Wählen Sie einen Kursteilnehmer aus, dessen Kompetenzprofil Sie sehen möchten.';
$string['my_comps'] = 'Meine Kompetenzen';
$string['my_items'] = 'Meine Artefakte';
$string['my_badges'] = 'Meine Auszeichnungen';
$string['my_periods'] = 'Meine Feedbacks';
$string['item_type'] = 'Typ';
$string['item_link'] = 'Link';
$string['item_file'] = 'Datei';
$string['item_note'] = 'Notiz';
$string['item_title'] = 'Titel';
$string['item_url'] = 'Url';
$string['item_link'] = 'Link';
$string['period_reviewer'] = 'Bewerter';
$string['period_feedback'] = 'Verbales Feedback';
$string['January'] = 'Jänner';
$string['February'] = 'Februar';
$string['March'] = 'März';
$string['April'] = 'April';
$string['May'] = 'Mai';
$string['June'] = 'Juni';
$string['July'] = 'Juli';
$string['August'] = 'August';
$string['September'] = 'September';
$string['October'] = 'Oktober';
$string['November'] = 'November';
$string['December'] = 'Dezember';

//Competence Profile Settings
$string['profile_settings_showonlyreached'] = 'Ich möchte in meinem Kompetenzprofil nur bereits erreichte Kompetenzen sehen.';
$string['profile_settings_choose_courses'] = 'In Exabis Competencies beurteilen Trainer/innen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgew&auml;hlt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.';
$string['profile_settings_useexaport'] = 'Ich möchte Kompetenzen, die in Exabis E-Portfolio verwendet werden in meinem Profil sehen.';
$string['profile_settings_choose_items'] = 'Exabis ePortfolio dokumentiert deinen Kompetenzerwerb außerhalb von Lehrern vorgegebenen Grenzen. Du kannst ausw&auml;hlen, welche Einträge im Kompetenzprofil aufscheinen sollen.';
$string['profile_settings_useexastud'] = 'Ich möchte Beurteilungen aus Exabis Student Review in meinem Profil sehen.';
$string['profile_settings_choose_periods'] = 'Exabis Student Review speichert Beurteilungen in verschiedenen Kategorien über mehrere Perioden hinweg. Es kann ausgew&auml;hlt werden, welche Perioden das Kompetenzprofil beinhalten soll.';
$string['profile_settings_no_item'] = 'Kein Exabis ePortfolio Artefakt vorhanden, somit kann nichts dargestellt werden.';
$string['profile_settings_no_period'] = 'Keine Beurteilung in einer Periode in Exabis Student Review vorhanden.';
$string['profile_settings_usebadges'] = 'Ich möchte im Kompetenzprofil auch meine Auszeichnungen sehen.';
$string['profile_settings_onlygainedbadges'] = 'Ich möchte nur Auszeichnungen sehen, die mir bereits verliehen wurden.';
$string['profile_settings_badges_lineup'] = 'Einstellungen zu Auszeichnungen';
$string['profile_settings_showallcomps'] = 'Alle meine Kompetenzen';

$string['specificcontent'] = 'Schulbezogene Themenbereiche';
$string['specificsubject'] = 'Schulbezogene Gegenstands-/Kompetenzbereiche';

//Profoundness
$string['profoundness_description'] = 'Kompetenzbeschreibung';
$string['profoundness_basic'] = 'Grundkompetenz';
$string['profoundness_extended'] = 'Erweiterte Kompetenz';
$string['profoundness_mainly'] = 'Überwiegend erfüllt';
$string['profoundness_entirely'] = 'Zur Gänze erfüllt';

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