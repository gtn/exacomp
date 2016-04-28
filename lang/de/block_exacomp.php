<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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
$string['pluginname'] = 'Exabis Kompetenzraster';
// shown in block title and all headers
$string['blocktitle'] = 'Kompetenzraster';
$string['exacomp:addinstance'] = 'Exabis Competencies auf Kursseite anlegen';
$string['exacomp:myaddinstance'] = 'Exabis Competencies auf Startseite anlegen';
$string['exacomp:teacher'] = 'Übersicht der Lehrerfunktionen in einem Kurs';
$string['exacomp:admin'] = 'Übersicht der Administratorfunktionen in einem Kurs';
$string['exacomp:student'] = 'Übersicht der Teilnehmerfunktionen in einem Kurs';
$string['exacomp:use'] = 'Nutzung';
$string['exacomp:deleteexamples'] = 'Beispiele löschen';
$string['exacomp:assignstudents'] = 'Externe Trailer zuordnen';


// Admin Tabs
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Website-Einstellungen';
$string['tab_admin_configuration'] = 'Vorauswahl der Standards';
$string['admin_config_pending'] = 'Vorauswahl der Kompetenzen durch den Administrator notwendig';


// Teacher Tabs
$string['tab_teacher_settings'] = 'Kurs-Einstellungen';
$string['tab_teacher_settings_configuration'] = 'Einstellungen';
$string['tab_teacher_settings_selection_st'] = 'Schultyp-Auswahl';
$string['tab_teacher_settings_selection'] = 'Gegenstands-Auswahl';
$string['tab_teacher_settings_assignactivities'] = 'Moodle-Aktivit&auml;ten zuordnen';
$string['tab_teacher_settings_badges'] = 'Auszeichnungen bearbeiten';


// Student Tabs
$string['tab_student_all'] = 'Alle erworbenen Kompetenzen';


// Generic Tabs (used by Teacher and Students)
$string['tab_competence_grid'] = 'Berichte';
$string['tab_competence_overview'] = 'Kompetenzraster';
$string['tab_competence_details'] = 'Kompetenz-Detailansicht';
$string['tab_examples'] = 'Beispiele und Aufgaben';
$string['tab_learning_agenda'] = 'Wochenplan';
$string['tab_badges'] = 'Meine Auszeichnungen';
$string['tab_competence_profile'] = 'Kompetenzprofil';
$string['tab_competence_profile_profile'] = 'Profil';
$string['tab_competence_profile_settings'] = 'Einstellungen';
$string['tab_help'] = 'Hilfe';
$string['tab_teacher_demo_settings'] = 'Mit Demo-Daten arbeiten';
$string['tab_profoundness'] = 'Grund/Erweiterungskompetenzen';
$string['tab_cross_subjects'] = 'Themen';
$string['tab_cross_subjects_overview'] = 'Übersicht';
$string['tab_cross_subjects_course'] = 'Kursthemen';
$string['tab_weekly_schedule'] = 'Wochenplan';
$string['assign_descriptor_to_crosssubject'] = 'Die Teilkompetenz "{$a}" den folgenden Themen zuordnen:';
$string['assign_descriptor_no_crosssubjects_available'] = 'Es sind keine Themen vorhanden, legen Sie welche an.';
$string['first_configuration_step'] = 'Der erste Konfigurationsschritt besteht darin, Daten in das Exabis Competencies Modul zu importieren.';
$string['second_configuration_step'] = 'In diesem Konfigurationsschritt muss eine Vorauswahl für die Standards getroffen werden, damit das Modul verwendet werden kann. Diese Einstellungen sind unabh&auml;ngig vom Kurs für die gesamte Moodle-Installation g&uuml;ltig.';
$string['next_step'] = 'Dieser Konfigurationsschritt wurde abgeschlossen. Klicken Sie hier um zum N&auml;chsten zu gelangen.';
$string['next_step_teacher'] = 'Die Konfiguration, die vom Administrator vorgenommen werden muss, ist hiermit abgeschlossen. Um mit der kursspezifischen Konfiguration fortzufahren klicken Sie hier.';
$string['teacher_first_configuration_step'] = 'Im ersten Konfigurationsschritt der Kurs-Standards müssen einige generelle Einstellungen getroffen werden.';
$string['teacher_second_configuration_step'] = 'Im zweiten Konfigurationsschritt müssen Themenbereiche ausgewählt werden, mit denen Sie in diesem Kurs arbeiten möchten.';
$string['teacher_third_configuration_step'] = 'Im nächsten Schritt werden Moodle-Aktivit&auml;ten mit Kompetenzen assoziiert. ';
$string['teacher_third_configuration_step_link'] = '(Optional: Wenn Sie nicht mit Moodle-Aktivit&auml;ten arbeiten m&ouml;chten, dann entfernen Sie das Häkchen "Ich m&ouml;chte mit Moodle-Aktivit&auml;ten arbeiten" im Tab "Konfiguration".)';
$string['completed_config'] = 'Die Exabis Competencies Konfiguration wurde abgeschlossen.';
$string['optional_step'] = 'In Ihrem Kurs sind noch keine Teilnehmer/innen eingeschrieben, bet&auml;tigen Sie diesen Link wenn Sie das jetzt machen m&ouml;chten.';
$string['next_step_first_teacher_step'] = 'Klicken Sie hier um zum n&auml;chsten Schritt zu gelangen.';


// Block Settings
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten';
$string['settings_autotest'] = 'Automatischer Kompetenzerwerb durch Tests';
$string['settings_autotest_description'] = 'Kompetenzen die mit Tests verbunden sind, gelten automatisch als erworben, wenn der angegebene Test-Prozentwert erreicht wurde';
$string['settings_testlimit'] = 'Testlimit in %';
$string['settings_testlimit_description'] = 'Dieser Prozentwert muss erreicht werden, damit die Kompetenz als erworben gilt';
$string['settings_usebadges'] = 'Badges/Auszeichnungen verwenden';
$string['settings_usebadges_description'] = 'Anhaken um den Badges/Auszeichnungen Kompetenzen zuzuteilen';
$string['settings_enableteacherimport'] = 'Schulspezifische Standards verwenden';
$string['settings_enableteacherimport_description'] = 'Anhaken um LehrerInnen/KurstrainerInnen zu erlauben, eigene, schulspezifische Standards zu importieren';
$string['settings_interval'] = 'Einheitenlänge';
$string['settings_interval_description'] = 'Die Länge der Einheiten im Wochenplan in Minuten';
$string['settings_scheduleunits'] = 'Anzahl der Einheiten';
$string['settings_scheduleunits_description'] = 'Anzahl der Einheiten im Wochenplan';
$string['settings_schedulebegin'] = 'Beginn der Einheiten';
$string['settings_schedulebegin_description'] = 'Beginnzeitpunkt der ersten Einheit im Wochenplan. Format hh:mm';
$string['settings_admin_scheme'] = 'Globales Bewertungsschema';
$string['settings_admin_scheme_description'] = 'Wählen Sie ein kursübergreifendes Bewertungsschema, andernfalls kann der/die LehrerIn pro Kurs ein zahlen basiertes Bewertungsschema festlegen.';
$string['settings_admin_scheme_none'] = 'keine globale Bewertung';
$string['settings_additional_grading'] = 'Zus&auml;tzliche Bewertung';
$string['settings_additional_grading_description'] = 'Zus&auml;tzliche Bewertung für Kompetenzen/Lernmaterialien (0-100%)';
$string['settings_usetimeline'] = 'Timeline im Profil verwenden';
$string['settings_usetimeline_description'] = 'Zeitlichen Ablauf des Kompetenzerwerbes im Profil anzeigen';


// Learning agenda
$string['LA_MON'] = 'Mo';
$string['LA_TUE'] = 'Di';
$string['LA_WED'] = 'Mi';
$string['LA_THU'] = 'Do';
$string['LA_FRI'] = 'Fr';
$string['LA_todo'] = 'Was mache ich?';
$string['LA_learning'] = 'Was kann ich lernen?';
$string['LA_student'] = 'S';
$string['LA_teacher'] = 'L';
$string['LA_assessment'] = 'Einsch&auml;tzung';
$string['LA_plan'] = 'Arbeitsplan';
$string['LA_no_learningagenda'] = 'Es sind keine Lernagenden in der ausgew&auml;hlten Woche vorhanden.';
$string['LA_no_student_selected'] = '-- kein(e) Kursteilnehmer/in ausgew&auml;hlt --';
$string['LA_select_student'] = 'W&auml;hlen Sie bitte eine(n) Kursteilnehmer/in aus, um seine Lernagenda einzusehen.';
$string['LA_no_example'] = 'Kein Beispiel zugeordnet';
$string['LA_backtoview'] = 'Zur&uuml;ck zur Originalansicht';
$string['LA_from_n'] = ' von ';
$string['LA_from_m'] = ' vom ';
$string['LA_to'] = ' bis zum ';
$string['LA_enddate'] = 'Enddatum';
$string['LA_startdate'] = 'Startdatum';


// Help
$string['help_content'] = '<h1>Video zur Einf&uuml;hrung</h1>
Kompetenzorientierter Unterricht ist in aller Munde - und Moodle kann dabei mit Hilfe des Blockes exabis Competencies wertvolle Unterst&uuml;tzung leisten.
<a href="http://www.youtube.com/watch?v=FQtCrlSNUEQ">http://www.youtube.com/watch?v=FQtCrlSNUEQ</a>

<h3>Die AdministratorInnen-Rolle</h3>Der Administrator/Die Administratorin einer Moodle-Instanz muss bestimmte Vorarbeiten leisten, damit der Block exabis Competencies verwendet werden kann. Informationen dazu liefert dieses Video.
<a href="http://www.youtube.com/watch?v=a7h_8EtQM9A">http://www.youtube.com/watch?v=a7h_8EtQM9A</a>

<h3>Die TrainerInnen-Rolle</h3>Die Lehrenden k&ouml;nnen mit Hilfe des Blockes einem Kurs bestimmte Kompetenzen zuordnen und den Kompetenzerwerb anschlie&szlig;end auch bewerten.
<a href="http://www.youtube.com/watch?v=gxSrXa4Ynik">http://www.youtube.com/watch?v=gxSrXa4Ynik</a>

<h3>Die Teilnehmer/innen-Rolle</h3>Lernende k&ouml;nnen mit Hilfe von Exabis Competencies u.a. ihren Kompetenzerwerb selbst einsch&auml;tzen und die eigene Einsch$auml;tzung unkompliziert der TrainerInnen gegen&uuml;berstellen.
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


// Import
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a target="_blank" href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a target="_blank" href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch. Für österreichische Standards besuchen Sie bitte <a target="_blank" href="http://bist.edugroup.at">http://bist.edugroup.at</a>';
$string['importwebservice'] = 'Es besteht auch die M&ouml;glichkeit die Daten &uuml;ber ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['importdone'] = 'Die allgemeinen Bildungsstandards sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und w&auml;hlen Sie anschlie&szlig;end im Tab "Konfiguration" die anzuzeigenden Deskriptorenbereiche aus.';
$string['doimport'] = 'Bildungsstandards importieren';
$string['doimport_again'] = 'Weitere Bildungsstandards importieren';
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


// Configuration
$string['explainconfig'] = '';
$string['save_selection'] = 'Auswahl speichern';
$string['save_success'] = '&Auml;nderungen erfolgreich &uuml;bernommen';


// Course-Configuration
$string['grading_scheme'] = 'Bewertungsschema';
$string['uses_activities'] = 'Ich verwende Moodle Aktivit&auml;ten zur Beurteilung';
$string['show_all_descriptors'] = 'Alle Deskriptoren im &Uuml;berblick anzeigen';
$string['show_all_examples'] = 'Externe Beispiele für Kursteilnehmer/innen anzeigen';
$string['usedetailpage'] = 'Detaillierte Kompetenzansicht verwenden';
$string['useprofoundness'] = 'Grund- und Erweiterungskompetenzen verwenden';
$string['usetopicgrading'] = 'Beurteilung von Teilbereichen ermöglichen';
$string['usenumbering'] = 'Automatische Nummerierung im Kompetenzraster verwenden';
$string['useniveautitleinprofile'] = 'Im Kompetenzprofil den Lernfortschritt als Titel verwenden';
$string['usenostudents'] = 'Ohne Kursteilnehmer/innen arbeiten';
$string['profoundness_0'] = 'Nicht erreicht';
$string['profoundness_1'] = 'Zum Teil erreicht';
$string['profoundness_2'] = 'Erreicht';
$string['filteredtaxonomies'] = 'Beispiele werden anhand der ausgewählten Taxonomien verwendet:';
$string['show_all_taxonomies'] = 'Alle Taxonomien';
$string['warning_use_activities'] = 'Hinweis: Sie arbeiten jetzt mit Moodle-Aktivitäten die mit Kompetenzen verknüpft sind. Stellen Sie sicher, dass in diesem Kurs mit den selben Kompetenzen weitergearbeitet wird.';
$string['delete_unconnected_examples'] = 'Wenn Sie Themenbereiche abwählen, mit denen Beispiele verknüpft sind die noch am Wochenplan liegen, werden diese aus dem Wochenplan entfernt.';


// Badges
$string['mybadges'] = 'Meine Auszeichnungen';
$string['pendingbadges'] = 'Anstehende Auszeichnungen';
$string['no_badges_yet'] = 'Keine Auszeichnungen verfügbar';
$string['description_edit_badge_comps'] = 'Hier können Sie der ausgewählten Auszeichnung Kompetenzen zuordnen.';
$string['to_award'] = 'Um diese Auszeichnung zu erwerben, müssen Kompetenzen zugeordnet werden.';
$string['to_award_role'] = 'Um diese Auszeichnung zu erwerben, müssen sie das "manuelle Verleihung" Kriterium hinzufügen.';
$string['ready_to_activate'] = 'Diese Auszeichnung kann aktiviert werden: ';
$string['conf_badges'] = 'Auszeichnungen konfigurieren';
$string['conf_comps'] = 'Kompetenzen zuordnen';


// Examples
$string['sorting'] = 'Sortierung w&auml;hlen: ';
$string['subject'] = 'Gegenst&auml;nde';
$string['taxonomies'] = 'Taxonomien';
$string['show_all_course_examples'] = 'Beispiele aus allen Kursen anzeigen';
$string['expandcomps'] = 'Alle &ouml;ffnen';
$string['contactcomps'] = 'Alle schlie&szlig;en';
$string['name_example'] = 'Name';
$string['comp_based'] = 'Nach Kompetenzen sortieren';
$string['examp_based'] = 'Nach Lernmaterialien sortieren';


// Icons
$string['assigned_example'] = 'Zugeteiltes Beispiel';
$string['task_example'] = 'Aufgabenstellung';
$string['solution_example'] = 'L&ouml;sung';
$string['attachement_example'] = 'Anhang';
$string['extern_task'] = 'Externe Aufgabenstellung';
$string['total_example'] = 'Gesamtbeispiel';


// Example Upload
$string['example_upload_header'] = 'Eigenes Lernmaterial hochladen';
$string['taxonomy'] = 'Taxonomie';
$string['descriptors'] = 'Kompetenzen';
$string['descriptors_help'] = 'Es k&ouml;nnen mehrere Kompetenzen ausgew&auml;hlt werden.';
$string['filerequired'] = 'Es muss eine Datei ausgew&auml;hlt sein.';
$string['titlenotemtpy'] = 'Es muss ein Name eingegeben werden.';
$string['lisfilename'] = 'Dateiname nach LS Vorgabe generieren';
$string['solution'] = 'Musterlösung';
$string['submission'] = 'Abgabe';
$string['assignments'] = 'Kurs-Aufgaben';
$string['files'] = 'Dateien';
$string['link'] = 'Link';
$string['dataerr'] = 'Es muss zumindest ein Link oder eine Datei hochgeladen werden!';
$string['linkerr'] = 'Bitte geben Sie einen korrekten Link ein!';
$string['isgraded'] = 'Die Aufgabe wurde bereits beurteilt und kann daher nicht mehr eingereicht werden.';
$string['allow_resubmission'] = 'Aufgabe zur erneuten Abgabe freigeben';
$string['allow_resubmission_info'] = 'Die Aufgabe wurde zur erneuten Abgabe freigegeben.';


// Assign competencies
$string['delete_confirmation'] = 'Soll "{$a}" wirklich gelöscht werden?';
$string['legend_activities'] = 'Moodle-Aktivit&auml;ten';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Keine Moodle-Aktivit&auml;t/Quiz f&uuml;r diese Kompetenz abgegeben';
$string['legend_upload'] = 'Eigenes Beispiel hochladen';
$string['allniveaus'] = 'Alle Teilbereiche';
$string['choosesubject'] = 'Fach ausw&auml;hlen: ';
$string['choosetopic'] = 'Teilkompetenzbereich/Leitidee ausw&auml;hlen';
$string['choosestudent'] = 'Kursteilnehmer/in auswählen: ';
$string['own_additions'] = 'Schulische Ergänzung: ';
$string['delete_confirmation_descr'] = 'Soll die Kompetenz "{$a}" wirklich für alle Kurse gelöscht werden?';
$string['import_source'] = 'Importiert von: {$a}';
$string['local'] = 'Lokal';
$string['unknown_src'] = 'unbekannte Quelle';
$string['override_notice'] = 'Dieser Eintrag wurde von jemand anderem bearbeitet. Wirklich ändern?';
$string['unload_notice'] = 'Die Seite wirklich verlassen? Ungespeicherte Änderungen gehen verloren.';
$string['example_sorting_notice'] = 'Bitte zuerst die aktuellen Bewertungen speichern';
$string['newsubmission'] = 'Erneute Abgabe';


// Example Submission
$string['example_submission_header'] = 'Aufgabe {$a} bearbeiten';
$string['example_submission_info'] = 'Du bist dabei die Aufgabe "{$a}" zu bearbeiten. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.';
$string['example_submission_subject'] = 'Neue Abgabe';
$string['example_submission_message'] = 'Im Kurs {$a->course} wurde von Teilnehmer/in {$a->student} eine neue Abgabe eingereicht.';
$string['submissionmissing'] = 'Es müssen zumindest ein Link oder eine Datei abgegeben werden';
$string['usersubmitted'] = ' hat folgende Moodle-Aktivit&auml;ten abgegeben:';
$string['usersubmittedquiz'] = ' hat folgende Tests durchgef&uuml;hrt:';
$string['usernosubmission'] = ' hat keine Moodle-Aufgaben zu diesem Deskriptor abgegeben und keinen Test durchgef&uuml;hrt.';
$string['usernosubmission_topic'] = ' hat keine Moodle-Aufgaben zu diesem Thema abgegeben und keinen Test durchgef&uuml;hrt.';
$string['grading'] = ' Bewertung: ';
$string['teacher_tipp'] = 'Tipp';
$string['teacher_tipp_1'] = 'Diese Kompetenz wurde bei ';
$string['teacher_tipp_2'] = ' Moodle-Aktivit&auml;t(en) zugeordnet und bereits bei ';
$string['teacher_tipp_3'] = ' Moodle-Aktivit&auml;t(en) in der Kompetenz-Detailansicht erf&uuml;llt.';
$string['print'] = 'Drucken';
$string['eportitems'] = 'Diese/r Kursteilnehmer/in hat folgende ePortfolio-Artefakte zu diesem Deskriptor eingereicht: ';
$string['eportitem_shared'] = ' (geteilt)';
$string['eportitem_notshared'] = ' (nicht geteilt)';
$string['teachershortcut'] = 'L';
$string['studentshortcut'] = 'S';
$string['overview'] = 'Der Kompetenz-Überblick listet Kursteilnehmer/innen und die im Kurs aktivierten Kompetenzen auf.';
$string['showevaluation'] = 'Um die Selbsteinsch&auml;tzung zu aktivieren, klicken Sie <a href="{$a}">hier</a>.';
$string['hideevaluation'] = 'Um die Selbsteinsch&auml;tzung zu deaktivieren, klicken Sie <a href="{$a}">hier</a>.';
$string['showevaluation_student'] = 'Um die Einsch&auml;tzung der TrainerInnen zu aktivieren, klicke <a href="{$a}">hier</a>.';
$string['hideevaluation_student'] = 'Um die Einsch&auml;tzung der TrainerInnen zu deaktivieren, klicke <a href="{$a}">hier</a>.';
$string['columnselect'] = 'Spaltenauswahl';
$string['allstudents'] = 'Alle  Kursteilnehmer/innen';
$string['nostudents'] = 'Keine  Kursteilnehmer/innen';
$string['statistic'] = 'Gesamtübersicht';
$string['niveau'] = 'Lernfortschritt';
$string['competence_grid_niveau'] = 'Niveau';
$string['descriptor'] = 'Kompetenz';
$string['groupsize'] = 'Gruppengröße: ';
$string['assigndone'] = 'Aufgabe erledigt: ';
$string['assignmyself'] = 'selbst';
$string['assignlearningpartner'] = 'LernpartnerIn';
$string['assignlearningrgoup'] = 'Lerngruppe';
$string['assignteacher'] = 'TrainerIn';
$string['assignfrom'] = 'von';
$string['assignuntil'] = 'bis';
$string['assignlearninggroup'] = 'Lerngruppe';


// metadata
$string['subject_singular'] = 'Schultyp';
$string['comp_field_idea'] = 'Fach';
$string['comp'] = 'Teilkompetenzbereich';
$string['progress'] = 'Fortschritt';
$string['instruction'] = 'Erläuterung';
$string['instruction_content'] = 'Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche 
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden. 
				Darüber hinaus können Sie das Erreichen der Teilkompetenzen eintragen. 
				Kursteilnehmer/innen können sich darüber hinaus selbst einschätzen. Mit Kompetenzen
				verknüpfte Moodle-Aufgaben werden bei Einreichung durch Kursteilnehmer/innen
				mit rotem Icon dargestellt. Leistungsnachweise die eigeninitiativ über
				ePortfolio-Arbeit durch Kursteilnehmer/innen beigesteuert werden, werden mit
				einem blauen Icon dargestellt.';


// Activities
$string['explaineditactivities_subjects'] = '';
$string['column_setting'] = 'Spalten aus/einblenden';
$string['niveau_filter'] = 'Niveaus filtern';
$string['module_filter'] = 'Aktivit&auml;ten filtern';
$string['apply_filter'] = 'Filter anwenden';
$string['no_topics_selected'] = 'Konfiguration für Exabis Competencies wurde noch nicht abgeschlossen. Bitte w&auml;hlen Sie zuerst Gegenst&auml;nde aus, denen Sie dann Moodle-Aktivit&auml;ten zuordnen k&ouml;nnen.';
$string['no_activities_selected'] = 'Bitte ordnen Sie den erstellen Moodle-Aktivitäten Kompetenzen zu.';
$string['no_activities_selected_student'] = 'In diesem Bereich sind derzeit keine Daten vorhanden.';
$string['no_course_activities'] = 'In diesem Kurs wurden noch keine Moodle-Aktivit&auml;ten erstellt, klicken Sie hier um dies nun zu tun.';
$string['all_modules'] = 'Alle Aktivitäten';
$string['all_niveaus'] = 'Alle Niveaustufen';
$string['tick_some'] = 'Bitte treffen Sie eine Auswahl!';


// Competence Grid
$string['infolink'] = 'Weiter Informationen: ';
$string['textalign'] = 'Textuelle Ausrichtung ändern';
$string['selfevaluation'] = 'Selbsteinschätzung';
$string['teacherevaluation'] = 'Einschätzung des Beurteilenden';
$string['competencegrid_nodata'] = 'Sollte der Kompetenzraster leer sein, wurden für die Deskriptoren des ausgewählten Gegenstands keine Niveaus in den Daten definiert';
$string['statistic_type_descriptor'] = 'Wechsel zur Statistik der Teilkompetenzen';
$string['statistic_type_example'] = 'Wechsel zur Statistik der Aufgaben';
$string['reports'] = 'Berichte';
$string['report_competence'] = 'Kompetenzen';
$string['report_detailcompetence'] = 'Teilkompetenzen';
$string['report_examples'] = 'Lernmaterialien';


// Detail view
$string['detail_description'] = 'Hier kann mit Hilfe von Aktivitäten eine Kompetenz beurteilt werden.';


// Competence Profile
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
$string['select_student'] = 'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Kompetenzprofil Sie sehen möchten.';
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
$string['teacher_eval'] = 'Lehrerbewertung';
$string['student_eval'] = 'Schülerselbsteinschätzung';
$string['oB'] = 'ohne Bewertung';
$string['nE'] = 'nicht erreicht';


// Competence Profile Settings
$string['profile_settings_showonlyreached'] = 'Ich möchte in meinem Kompetenzprofil nur bereits erreichte Kompetenzen sehen.';
$string['profile_settings_choose_courses'] = 'In Exabis Competencies beurteilen TrainerInnen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgew&auml;hlt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.';
$string['profile_settings_useexaport'] = 'Ich möchte Kompetenzen, die in Exabis ePortfolio verwendet werden in meinem Profil sehen.';
$string['profile_settings_choose_items'] = 'Exabis ePortfolio dokumentiert deinen Kompetenzerwerb außerhalb von LehrerInnen vorgegebenen Grenzen. Du kannst ausw&auml;hlen, welche Einträge im Kompetenzprofil aufscheinen sollen.';
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


// Profoundness
$string['profoundness_description'] = 'Kompetenzbeschreibung';
$string['profoundness_basic'] = 'Grundkompetenz';
$string['profoundness_extended'] = 'Erweiterte Kompetenz';
$string['profoundness_mainly'] = 'Überwiegend erfüllt';
$string['profoundness_entirely'] = 'Zur Gänze erfüllt';


// External trainer & eLove
$string['block_exacomp_external_trainer_assign_head'] = 'Zuteilung von externen Trainer/innen für Kursteilnehmer/innen erlauben.';
$string['block_exacomp_external_trainer_assign_body'] = 'Erforderlich für die Benutzung der elove App.';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Selbsteinschätzung für Kursteilnehmer/innen in der elove App erlauben.';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Externe TrainerIn zuordnen';
$string['block_exacomp_external_trainer_assign_new'] = 'Neue Zuordnung: ';
$string['block_exacomp_external_trainer'] = 'AusbilderIn: ';
$string['block_exacomp_external_trainer_student'] = 'Auszubildende: ';
$string['block_exacomp_external_trainer_allstudents'] = 'Alle Kursteilnehmer/innen';


// Crosssubjects
$string['empty_draft'] = 'Neues Thema';
$string['empty_draft_description'] = 'Erstelle dein eigenes Thema - ändere die Beschreibung hier';
$string['add_drafts_to_course'] = 'Ausgewählte Vorlagen im Kurs verwenden';
$string['choosecrosssubject'] = 'Thema auswählen';
$string['crosssubject'] = 'Thema';
$string['student_name'] = 'Kursteilnehmer/in';
$string['help_crosssubject'] = 'Die Zusammenstellung des Themas erfolgt für die ganze Schule über den Reiter Kompetenzraster. Sie können hier kursspezifisch Kompetenzen, Teilkompetenzen und Lernmaterial ausblenden. Lernmaterial kann hier ergänzt werden. Dieses wird automatisch in die Lernwegeliste integriert.';
$string['description'] = 'Beschreibung';
$string['no_student'] = '-- kein(e) Kursteilnehmer/in ausgewählt --';
$string['no_student_edit'] = 'Editiermodus';
$string['save_as_draft'] = 'Thema als Vorlage speichern';
$string['comps_and_material'] = 'Kompetenzen und Lernmaterial';
$string['no_crosssubjs'] = 'In diesem Kurs gibt es noch kein Thema.';
$string['delete_drafts'] = 'Ausgewählte Vorlagen löschen';
$string['share_crosssub'] = 'Thema für Kursteilnehmer/innen freigeben';
$string['share_crosssub_with_students'] = 'Das Thema "{$a}" für folgende Kursteilnehmer/innen freigeben: ';
$string['share_crosssub_with_all'] = 'Das Thema "{$a}" für <b>alle</b> Kursteilnehmer/innen freigeben: ';
$string['new_crosssub'] = 'Eigenes Thema erstellen';
$string['add_crosssub'] = 'Thema erstellen';
$string['nocrosssubsub'] = 'Allgemeine Themen';
$string['delete_crosssub'] = 'Thema löschen';
$string['confirm_delete'] = 'Soll dieses Thema wirklich gelöscht werden?';
$string['no_students_crosssub'] = 'Es sind keine Kursteilnehmer/innen zu diesem Thema zugeteilt.';
$string['use_available_crosssub'] = 'Ein Thema aus einer Vorlage erstellen:';
$string['save_crosssub'] = 'Thema aktualisieren';
$string['add_content_to_crosssub'] = 'Das Thema ist noch nicht befüllt.';
$string['add_descriptors_to_crosssub'] = 'Teilkompetenz mit Thema verknüpfen';
$string['manage_crosssubs'] = 'Zurück zur Übersicht';
$string['show_course_crosssubs'] = 'Kurs-Themen ansehen';
$string['existing_crosssub'] = 'Vorhandene Themen in diesem Kurs';
$string['create_new_crosssub'] = 'Neues Thema erstellen';
$string['share_crosssub_for_further_use'] = 'Geben Sie das Thema an Kursteilnehmer/innen frei, um volle Funktionalität zu erhalten.';
$string['available_crosssubjects'] = 'Vorhandene Kursthemen';
$string['crosssubject_drafts'] = 'Themenvorlagen';
$string['de:Vorlage verwenden'] = '';
$string[''] = '';


// Associations
$string['competence_associations'] = 'Verknüpfungen';
$string['competence_associations_explaination'] = 'Das Lernmaterial {$a} ist mit den folgenden Kompetenzen verknüpft:';


// Weeky schedule
$string['weekly_schedule'] = 'Wochenplan';
$string['weekly_schedule_added'] = 'Die Aufgabe wurde in den Planungsspeicher im Wochenplan hinzugefügt.';
$string['weekly_schedule_already_exists'] = 'Die Aufgabe ist bereits im Planungsspeicher im Wochenplan.';
$string['select_student_weekly_schedule'] = 'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Wochenplan Sie sehen möchten.';
$string['example_pool'] = 'Planungsspeicher';
$string['example_trash'] = 'Papierkorb';
$string['choosecourse'] = 'Kurs auswählen: ';
$string['weekly_schedule_added_all'] = 'Die Aufgabe wurde bei allen Kursteilnehmer/innen auf den Planungsspeicher im Wochenplan gelegt.';
$string['weekly_schedule_already_existing_for_one'] = 'Die Aufgabe ist bei mindestens einem Sch&uuml;ler bereits im Planungsspeicher im Wochenplan.';
$string['weekly_schedule_link_to_grid'] = 'Um den Wochenplan zu befüllen in den Kompetenzraster wechseln';
$string['pre_planning_storage'] = 'Vorplanungsspeicher';
$string['pre_planning_storage_added'] = 'Lernmaterial wurde zum Vorplanungsspeicher hinzugefügt.';
$string['pre_planning_storage_already_contains'] = 'Lernmateriel bereits im Vorplanungsspeicher enthalten.';
$string['save_pre_planning_selection'] = 'Ausgewählte Beispiele auf den Wochenplan der ausgewählten Kursteilnehmer/innen legen';
$string['empty_pre_planning_storage'] = 'Vorplanungsspeicher leeren';
$string['noschedules_pre_planning_storage'] = 'Der Vorplanungsspeicher ist leer. Bitte legen Sie über die Kompetenzraster neue Lernmaterialien in den Vorplanungsspeicher.';
$string['empty_trash'] = 'Papierkorb leeren';
$string['to_weekly_schedule'] = 'Zum Wochenplan';
$string['blocking_event'] = 'Sperrelement erstellen';
$string['blocking_event_title'] = 'Titel';
$string['blocking_event_create'] = 'Zum Vorplanungsspeicher hinzufügen';


// Notifications
$string['notification_submission_subject'] = '{$a->student} hat eine Lösung zum Beispiel {$a->example} eingereicht.';
$string['notification_submission_body'] = '{$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href="{$viewurl}">{$a->example}</a>';
$string['notification_submission_context'] = 'Abgabe';
$string['notification_grading_subject'] = 'Neue Beurteilungen im Kurs {$a->course}';
$string['notification_grading_body'] = 'Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.';
$string['notification_grading_context'] = 'Beurteilung';
$string['notification_self_assessment_subject'] = 'Neue Selbsteinschätzung im Kurs {$a->course}';
$string['notification_self_assessment_body'] = '{$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.';
$string['notification_self_assessment_context'] = 'Selbsteinschätzung';
$string['notification_example_comment_subject'] = 'Neuer Kommentar bei Aufgabe {$a->example}';
$string['notification_example_comment_body'] = '{$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.';
$string['notification_example_comment_context'] = 'Kommentar';
$string['notification_weekly_schedule_subject'] = 'Neue Aufgabe am Wochenplan';
$string['notification_weekly_schedule_body'] = '{$a->teacher} hat dir im Kurs {$a->course} die Aufgabe {$a->example} auf den Wochenplan gelegt.';
$string['notification_weekly_schedule_context'] = 'Wochenplan';
$string['inwork'] = '{$a->inWork}/{$a->total} Materialien in Arbeit';
$string['block_exacomp_notifications_head'] = 'Mitteilungen und Benachrichtigungen';
$string['block_exacomp_notifications_body'] = 'Bei Aktionen wie einer Beispiel-Einreichung oder einer Beurteilung werden Nachrichten an die zuständigen Benutzer gesendet.';


// Logging
$string['block_exacomp_logging_head'] = 'Logging';
$string['block_exacomp_logging_body'] = 'Relevante Aktionen werden geloggt.';
$string['eventscompetenceassigned'] = 'Kompetenz zugeteilt';
$string['eventsexamplesubmitted'] = 'Aufgabe abgegeben';
$string['eventsexamplegraded'] = 'Aufgabe beurteilt';
$string['eventsexamplecommented'] = 'Aufgabe kommentiert';
$string['eventsexampleadded'] = 'Aufgabe zu Wochenplan hinzugefügt';
$string['eventsimportcompleted'] = 'Import durchgeführt';
$string['eventscrosssubjectadded'] = 'Thema freigegeben';


// === Statistics ===
$string['process'] = 'Bearbeitungsstand';
$string['niveauclass'] = 'Niveaueinstufung';


// === Message ===
$string['messagetocourse'] = 'Nachricht an alle Kursteilnehmer/innen senden';
$string['messageprovider:submission'] = 'Nachricht bei neuer Schülerabgabe';
$string['messageprovider:grading'] = 'Nachricht an bei neuer Note';
$string['messageprovider:self_assessment'] = 'Nachricht bei neuer Selbstbewertung';
$string['messageprovider:weekly_schedule'] = 'Lehrer/in fügt ein Beispiel in den Wochenplan ein';
$string['messageprovider:comment'] = 'Lehrer/in kommentiert ein Beispiel';


// load local langstrings
if (file_exists(__DIR__."/../../local.config/lang.".basename(__DIR__).".php")){
	require __DIR__."/../../local.config/lang.".basename(__DIR__).".php";
}
