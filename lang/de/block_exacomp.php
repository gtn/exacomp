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
$string['pluginname'] = 'Exabis Kompetenzraster';
// shown in block title and all headers
$string['blocktitle'] = 'Kompetenzraster';
$string['exacomp:addinstance'] = 'Exabis Competence Grid auf Kursseite anlegen';
$string['exacomp:myaddinstance'] = 'Exabis Competence Grid auf Startseite anlegen';
$string['exacomp:teacher'] = 'Übersicht der Lehrerfunktionen in einem Kurs';
$string['exacomp:admin'] = 'Übersicht der Administratorfunktionen in einem Kurs';
$string['exacomp:student'] = 'Übersicht der Teilnehmerfunktionen in einem Kurs';
$string['exacomp:use'] = 'Nutzung';
$string['exacomp:deleteexamples'] = 'Lernmaterialien löschen';
$string['exacomp:assignstudents'] = 'Externe Trailer zuordnen';


// === Admin Tabs ===
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Website-Einstellungen';
$string['tab_admin_configuration'] = 'Vorauswahl der Standards';
$string['admin_config_pending'] = 'Vorauswahl der Kompetenzen durch den Administrator notwendig';


// === Teacher Tabs ===
$string['tab_teacher_settings'] = 'Kurs-Einstellungen';
$string['tab_teacher_settings_configuration'] = 'Einstellungen';
$string['tab_teacher_settings_selection_st'] = 'Bildungsstandard-Auswahl';
$string['tab_teacher_settings_selection'] = 'Auswahl der Kompetenzbereiche';
$string['tab_teacher_settings_assignactivities'] = 'Moodle-Aktivitäten zuordnen';
$string['tab_teacher_settings_badges'] = 'Auszeichnungen bearbeiten';
$string['tab_teacher_settings_new_subject'] = 'Neuen Kompetenzraster anlegen';

// === Student Tabs ===
$string['tab_student_all'] = 'Alle erworbenen Kompetenzen';


// === Generic Tabs (used by Teacher and Students) ===
$string['tab_competence_grid'] = 'Berichte';
$string['tab_competence_overview'] = 'Kompetenzraster';
$string['tab_competence_details'] = 'Moodle Aktivitäten';
$string['tab_examples'] = 'Lernmaterialien';
$string['tab_badges'] = 'Meine Auszeichnungen';
$string['tab_competence_profile'] = 'Kompetenzprofil';
$string['tab_competence_profile_profile'] = 'Profil';
$string['tab_competence_profile_settings'] = 'Einstellungen';
$string['tab_help'] = 'Hilfe';
$string['tab_profoundness'] = 'Grund/Erweiterungskompetenzen';
$string['tab_cross_subjects'] = 'Themen';
$string['tab_cross_subjects_overview'] = 'Übersicht';
$string['tab_cross_subjects_course'] = 'Kursthemen';
$string['tab_weekly_schedule'] = 'Wochenplan';
$string['assign_descriptor_to_crosssubject'] = 'Die Teilkompetenz "{$a}" den folgenden Themen zuordnen:';
$string['assign_descriptor_no_crosssubjects_available'] = 'Es sind keine Themen vorhanden, legen Sie welche an.';
$string['first_configuration_step'] = 'Der erste Konfigurationsschritt besteht darin, Daten in das Exabis Kompetenzraster Modul zu importieren.';
$string['second_configuration_step'] = 'Im zweiten Konfigurationsschritt müssen Bildungsstandards ausgewählt werden.';
$string['next_step'] = 'Dieser Konfigurationsschritt wurde abgeschlossen. Klicken Sie hier um zum Nächsten zu gelangen.';
$string['next_step_teacher'] = 'Die Konfiguration, die vom Administrator vorgenommen werden muss, ist hiermit abgeschlossen. Um mit der kursspezifischen Konfiguration fortzufahren klicken Sie hier.';
$string['teacher_first_configuration_step'] = 'Im ersten Konfigurationsschritt der Kurs-Standards müssen einige generelle Einstellungen getroffen werden.';
$string['teacher_second_configuration_step'] = 'Im zweiten Konfigurationsschritt müssen Themenbereiche ausgewählt werden, mit denen Sie in diesem Kurs arbeiten möchten.';
$string['teacher_third_configuration_step'] = 'Im nächsten Schritt werden Moodle-Aktivitäten mit Kompetenzen assoziiert. ';
$string['teacher_third_configuration_step_link'] = '(Optional: Wenn Sie nicht mit Moodle-Aktivitäten arbeiten möchten, dann entfernen Sie das Häkchen "Ich möchte mit Moodle-Aktivitäten arbeiten" im Tab "Konfiguration".)';
$string['completed_config'] = 'Die Exabis Kompetenzraster Konfiguration wurde abgeschlossen.';
$string['optional_step'] = 'In Ihrem Kurs sind noch keine Teilnehmer/innen eingeschrieben, betätigen Sie diesen Link wenn Sie das jetzt machen möchten.';
$string['next_step_first_teacher_step'] = 'Klicken Sie hier um zum nächsten Schritt zu gelangen.';


// === Block Settings ===
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
$string['settings_admin_scheme'] = 'Globales Bewertungsniveau';
$string['settings_admin_scheme_description'] = 'Beurteilungen können auf unterschiedlichem Niveau erfolgen.';
$string['settings_admin_scheme_none'] = 'keine Niveaus';
$string['settings_additional_grading'] = 'Angepasste Bewertung';
$string['settings_additional_grading_description'] = 'Bewertung für Teilkompetenzen und Lernmaterialien global auf "nicht erreicht(0)" - "vollständig erreicht(3)" beschränken';
$string['settings_usetimeline'] = 'Timeline im Profil verwenden';
$string['settings_usetimeline_description'] = 'Zeitlichen Ablauf des Kompetenzerwerbes im Profil anzeigen';
$string['settings_periods'] = 'Einträge für Zeittafel';
$string['settings_periods_description'] = 'Der Wochenplan ist flexibel an jedes Stunden- und Pausenraster anpassbar. Verwenden Sie im Textblock für jeden Zeitblock eine neue Zeile. Es sind beliebige Texteinträge erlaubt, z.B. "1. Std" oder "07:30 - 09:00".';
$string['timeline_teacher'] = 'L';
$string['timeline_student'] = 'S';
$string['timeline_total'] = 'Verfügbare';

// === Unit Tests ===
$string['unittest_string'] = 'result_unittest_string';
$string['de:unittest_string2'] = 'result_unittest_string2';
$string['de:unittest_param {$a} unittest_param'] = 'result_unittest_param {$a} result_unittest_param';
$string['de:unittest_param2 {$a->val} unittest_param2'] = 'result_unittest_param2 {$a->val} result_unittest_param2';


// === Learning agenda ===
$string['LA_MON'] = 'Mo';
$string['LA_TUE'] = 'Di';
$string['LA_WED'] = 'Mi';
$string['LA_THU'] = 'Do';
$string['LA_FRI'] = 'Fr';
$string['LA_todo'] = 'Was mache ich?';
$string['LA_learning'] = 'Was kann ich lernen?';
$string['LA_student'] = 'S';
$string['LA_teacher'] = 'L';
$string['LA_assessment'] = 'Einschätzung';
$string['LA_plan'] = 'Arbeitsplan';
$string['LA_no_learningagenda'] = 'Es sind keine Lernagenden in der ausgewählten Woche vorhanden.';
$string['LA_no_student_selected'] = '-- kein(e) Kursteilnehmer/in ausgewählt --';
$string['LA_select_student'] = 'Wählen Sie bitte eine(n) Kursteilnehmer/in aus, um seine Lernagenda einzusehen.';
$string['LA_no_example'] = 'Kein Lernmaterial zugeordnet';
$string['LA_backtoview'] = 'Zurück zur Originalansicht';
$string['LA_from_n'] = ' von ';
$string['LA_from_m'] = ' vom ';
$string['LA_to'] = ' bis zum ';
$string['LA_enddate'] = 'Enddatum';
$string['LA_startdate'] = 'Startdatum';


// === Help ===
$string['help_content'] = '<h1>Video zur Einführung</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';


// === Import ===
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a target="_blank" href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a target="_blank" href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch. Für österreichische Standards besuchen Sie bitte <a target="_blank" href="http://bist.edugroup.at">http://bist.edugroup.at</a>';
$string['importwebservice'] = 'Es besteht auch die Möglichkeit die Daten über ein <a href="{$a}">Webservice</a> aktuell zu halten.';
$string['import_max_execution_time'] = 'Wichtig: Die aktuellen Servereinstellung beschränken den Import auf {$a} Sekunden. Falls der Import länger dauert, wird er abgebrochen und es werden keine Daten importiert. Am Bildschirm wird in diesem Fall eine Sever Fehlermeldung (wie z.B. "500 Internal Server Error") angezeigt.';
$string['importdone'] = 'Die allgemeinen Bildungsstandards sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und wählen Sie anschließend im Tab Bildungsstandard die anzuzeigenden Lernlistenbereiche aus.';
$string['doimport'] = 'Bildungsstandards importieren';
$string['doimport_again'] = 'Weitere Bildungsstandards importieren';
$string['doimport_own'] = 'Schulspezifische Bildungsstandards importieren';
$string['delete_own'] = 'Schulspezifische Bildungsstandards löschen';
$string['delete_success'] = 'Schulspezifische Bildungsstandards wurden gelöscht';
$string['delete_own_confirm'] = 'Schulspezifische Bildungsstandards wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.';
$string['importsuccess'] = 'Daten erfolgreich importiert!';
$string['importsuccess_own'] = 'Eigene Daten erfolgreich importiert!';
$string['importfail'] = 'Es ist ein Fehler aufgetreten.';
$string['noxmlfile'] = 'Ein Import ist derzeit nicht möglich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';
$string['do_demo_import'] = 'Importieren Sie einen Demodatensatz, um zu sehen wie Exabis Kompetenzraster funktioniert.';


// === Configuration ===
$string['explainconfig'] = 'Um das Modul exabis competences verwenden zu können, müssen hier die Kompetenzbereiche der Moodle-Instanz selektiert werden.';
$string['save_selection'] = 'Auswahl speichern';
$string['save_success'] = 'Änderungen erfolgreich übernommen';


// === Course-Configuration ===
$string['grading_scheme'] = 'Bewertungsschema';
$string['uses_activities'] = 'Ich verwende Moodle Aktivitäten zur Beurteilung';
$string['show_all_descriptors'] = 'Alle Lernlisten im Überblick anzeigen';
$string['show_all_examples'] = 'Externe Lernmaterialien für Kursteilnehmer/innen anzeigen';
$string['usedetailpage'] = 'Detaillierte Kompetenzansicht verwenden';
$string['useprofoundness'] = 'Grund- und Erweiterungskompetenzen verwenden';
$string['usetopicgrading'] = 'Beurteilung von Kompetenzbereichen ermöglichen';
$string['usesubjectgrading'] = 'Beurteilung von Fächern ermöglichen';
$string['usenumbering'] = 'Automatische Nummerierung im Kompetenzraster verwenden';
$string['useniveautitleinprofile'] = 'Im Kompetenzprofil den Lernfortschritt als Titel verwenden';
$string['usenostudents'] = 'Ohne Kursteilnehmer/innen arbeiten';
$string['profoundness_0'] = 'Nicht erreicht';
$string['profoundness_1'] = 'Zum Teil erreicht';
$string['profoundness_2'] = 'Erreicht';
$string['filteredtaxonomies'] = 'Lernmaterialien werden anhand der ausgewählten Taxonomien verwendet:';
$string['show_all_taxonomies'] = 'Alle Taxonomien';
$string['warning_use_activities'] = 'Hinweis: Sie arbeiten jetzt mit Moodle-Aktivitäten die mit Kompetenzen verknüpft sind. Stellen Sie sicher, dass in diesem Kurs mit den selben Kompetenzen weitergearbeitet wird.';
$string['delete_unconnected_examples'] = 'Wenn Sie Themenbereiche abwählen, mit denen Lernmaterialien verknüpft sind die noch am Wochenplan liegen, werden diese aus dem Wochenplan entfernt.';


// === Badges ===
$string['mybadges'] = 'Meine Auszeichnungen';
$string['pendingbadges'] = 'Anstehende Auszeichnungen';
$string['no_badges_yet'] = 'Keine Auszeichnungen verfügbar';
$string['description_edit_badge_comps'] = 'Hier können Sie der ausgewählten Auszeichnung Kompetenzen zuordnen.';
$string['to_award'] = 'Um diese Auszeichnung zu erwerben, müssen Kompetenzen zugeordnet werden.';
$string['to_award_role'] = 'Um diese Auszeichnung zu erwerben, müssen sie das "manuelle Verleihung" Kriterium hinzufügen.';
$string['ready_to_activate'] = 'Diese Auszeichnung kann aktiviert werden: ';
$string['conf_badges'] = 'Auszeichnungen konfigurieren';
$string['conf_comps'] = 'Kompetenzen zuordnen';


// === Examples ===
$string['sorting'] = 'Sortierung wählen: ';
$string['subject'] = 'Kompetenzbereiche';
$string['taxonomies'] = 'Niveaustufen';
$string['show_all_course_examples'] = 'Lernmaterialien aus allen Kursen anzeigen';
$string['name_example'] = 'Name';
$string['comp_based'] = 'Nach Kompetenzen sortieren';
$string['examp_based'] = 'Nach Lernmaterialien sortieren';

// === Icons ===
$string['assigned_example'] = 'Zugeteiltes Lernmaterial';
$string['task_example'] = 'Aufgabenstellung';
$string['extern_task'] = 'Externe Aufgabenstellung';
$string['total_example'] = 'Gesamtmaterial';


// === Example Upload ===
$string['example_upload_header'] = 'Eigenes Lernmaterial hochladen';
$string['taxonomy'] = 'Niveaustufe';
$string['descriptors'] = 'Kompetenzen';
$string['filerequired'] = 'Es muss eine Datei ausgewählt sein.';
$string['titlenotemtpy'] = 'Es muss ein Name eingegeben werden.';
$string['solution'] = 'Musterlösung';
$string['hide_solution'] = 'Musterlösung verbergen';
$string['show_solution'] = 'Musterlösung anzeigen';
$string['hide_solution_disabled'] = 'Musterlösung ist bereits für alle Schüler versteckt';
$string['submission'] = 'Abgabe';
$string['assignments'] = 'Moodle Aktivitäten';
$string['files'] = 'Dateien';
$string['link'] = 'Link';
$string['dataerr'] = 'Es muss zumindest ein Link oder eine Datei hochgeladen werden!';
$string['linkerr'] = 'Bitte geben Sie einen korrekten Link ein!';
$string['isgraded'] = 'Die Aufgabe wurde bereits beurteilt und kann daher nicht mehr eingereicht werden.';
$string['allow_resubmission'] = 'Aufgabe zur erneuten Abgabe freigeben';
$string['allow_resubmission_info'] = 'Die Aufgabe wurde zur erneuten Abgabe freigegeben.';


// === Assign competencies ===
$string['delete_confirmation'] = 'Soll "{$a}" wirklich gelöscht werden?';
$string['legend_activities'] = 'Moodle-Aktivitäten';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Keine Moodle-Aktivität/Quiz für diese Kompetenz abgegeben';
$string['legend_upload'] = 'Eigenes Lernmaterial hochladen';
$string['allniveaus'] = 'Alle Lernfortschritte';
$string['choosesubject'] = 'Kompetenzbereich auswählen';
$string['choosetopic'] = 'Lernfortschritte auswählen';
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
$string['value_too_large'] = 'Fehler: Benotungen dürfen nicht größer als 6.0 sein!';
$string['value_too_low'] = 'Fehler: Benotungen dürfen nicht kleiner als 1.0 sein!';
$string['value_not_allowed'] = 'Fehler: Benotungen müssen Zahlenwerte zwischen 1.0 und 6.0 sein';
$string['competence_locked'] = 'Beurteilung vorhanden oder Lernmaterial in Verwendung!';
// === Example Submission ===
$string['example_submission_header'] = 'Aufgabe {$a} bearbeiten';
$string['example_submission_info'] = 'Du bist dabei die Aufgabe "{$a}" zu bearbeiten. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.';
$string['example_submission_subject'] = 'Neue Abgabe';
$string['example_submission_message'] = 'Im Kurs {$a->course} wurde von Teilnehmer/in {$a->student} eine neue Abgabe eingereicht.';
$string['submissionmissing'] = 'Es müssen zumindest ein Link oder eine Datei abgegeben werden';
$string['usersubmitted'] = ' hat folgende Moodle-Aktivitäten abgegeben:';
$string['usersubmittedquiz'] = ' hat folgende Tests durchgeführt:';
$string['usernosubmission'] = ' hat keine Moodle-Aufgaben zu dieser Lernliste abgegeben und keinen Test durchgeführt.';
$string['usernosubmission_topic'] = ' hat keine Moodle-Aufgaben zu dieser Teilkompetenz abgegeben und keinen Test durchgeführt.';
$string['grading'] = ' Bewertung: ';
$string['teacher_tipp'] = 'Tipp';
$string['teacher_tipp_1'] = 'Diese Kompetenz wurde bei ';
$string['teacher_tipp_2'] = ' Moodle-Aktivität(en) zugeordnet und bereits bei ';
$string['teacher_tipp_3'] = ' Moodle-Aktivität(en) in der Kompetenz-Detailansicht erfüllt.';
$string['print'] = 'Drucken';
$string['eportitems'] = 'Diese/r Kursteilnehmer/in hat folgende ePortfolio-Artefakte zu diesem Deskriptor eingereicht: ';
$string['eportitem_shared'] = ' (geteilt)';
$string['eportitem_notshared'] = ' (nicht geteilt)';
$string['teachershortcut'] = 'L';
$string['studentshortcut'] = 'S';
$string['overview'] = 'Hier haben Sie einen Überblick über die Teilkompetenzen der ausgewählten Lernwegeliste und die zugeordneten Aufgaben. Sie können das Erreichen der jeweiligen Teilkompetenz individuell bestätigen.';
$string['showevaluation'] = 'Um die Selbsteinschätzung einzusehen, klicken Sie <a href="{$a}">hier</a>';
$string['hideevaluation'] = 'Um die Selbsteinschätzung auszublenden, klicken Sie <a href="{$a}">hier</a>';
$string['showevaluation_student'] = 'Um die Einschätzung der TrainerInnen zu aktivieren, klicke <a href="{$a}">hier</a>.';
$string['hideevaluation_student'] = 'Um die Einschätzung der TrainerInnen zu deaktivieren, klicke <a href="{$a}">hier</a>.';
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
$string['assignteacher'] = 'TrainerIn';
$string['assignfrom'] = 'von';
$string['assignuntil'] = 'bis';
$string['comp_-1'] = 'ohne Angabe';
$string['comp_0'] = 'nicht erreicht';
$string['comp_1'] = 'teilweise';
$string['comp_2'] = 'überwiegend';
$string['comp_3'] = 'vollständig';
// === metadata ===
$string['subject_singular'] = 'Fach';
$string['comp_field_idea'] = 'Kompetenzbereich/Leitidee';
$string['comp'] = 'Kompetenz';
$string['progress'] = 'Lernfortschritt';
$string['instruction'] = 'Anleitung';
$string['instruction_content'] = 'Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche 
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden. 
				Darüber hinaus können Sie das Erreichen der Teilkompetenzen
				eintragen. Je nach Konzept der Schule kann die Bearbeitung des
				Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz
				markiert oder die Qualität der Bearbeitung / der Kompetenzerreichung
				gekennzeichnet werden. Keinenfalls müssen die Schülerinnen und
				Schüler alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Schülerinnen
				und Schüler müssen dann keine zugehörigen Lernmaterialien
				bearbeiten.';


// === Activities ===
$string['explaineditactivities_subjects'] = 'Hier können Sie den erstellten Aufgaben Lernlisten zuordnen.';
$string['column_setting'] = 'Spalten aus/einblenden';
$string['niveau_filter'] = 'Niveaus filtern';
$string['module_filter'] = 'Aktivitäten filtern';
$string['apply_filter'] = 'Filter anwenden';
$string['no_topics_selected'] = 'Konfiguration für Exabis Kompetenzraster wurde noch nicht abgeschlossen. Bitte wählen Sie zuerst Gegenstände aus, denen Sie dann Moodle-Aktivitäten zuordnen können.';
$string['no_activities_selected'] = 'Bitte ordnen Sie den erstellen Moodle-Aktivitäten Kompetenzen zu.';
$string['no_activities_selected_student'] = 'In diesem Bereich sind derzeit keine Daten vorhanden.';
$string['no_course_activities'] = 'In diesem Kurs wurden noch keine Moodle-Aktivitäten erstellt, klicken Sie hier um dies nun zu tun.';
$string['all_modules'] = 'Alle Aktivitäten';
$string['all_niveaus'] = 'Alle Niveaustufen';
$string['tick_some'] = 'Bitte treffen Sie eine Auswahl!';


// === Competence Grid ===
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


// === Detail view ===
$string['detail_description'] = 'Hier kann mit Hilfe von Aktivitäten eine Kompetenz beurteilt werden.';


// === Competence Profile ===
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
$string['oB'] = 'ohne Bewertung';
$string['nE'] = 'nicht erreicht';


// === Competence Profile Settings ===
$string['profile_settings_showonlyreached'] = 'Ich möchte in meinem Kompetenzprofil nur bereits erreichte Kompetenzen sehen.';
$string['profile_settings_choose_courses'] = 'In Exabis Kompetenzraster beurteilen TrainerInnen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgewählt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.';
$string['profile_settings_useexaport'] = 'Ich möchte Kompetenzen, die in Exabis ePortfolio verwendet werden in meinem Profil sehen.';
$string['profile_settings_choose_items'] = 'Exabis ePortfolio dokumentiert deinen Kompetenzerwerb außerhalb von LehrerInnen vorgegebenen Grenzen. Du kannst auswählen, welche Einträge im Kompetenzprofil aufscheinen sollen.';
$string['profile_settings_useexastud'] = 'Ich möchte Beurteilungen aus Exabis Student Review in meinem Profil sehen.';
$string['profile_settings_no_item'] = 'Kein Exabis ePortfolio Artefakt vorhanden, somit kann nichts dargestellt werden.';
$string['profile_settings_no_period'] = 'Keine Beurteilung in einer Periode in Exabis Student Review vorhanden.';
$string['profile_settings_usebadges'] = 'Ich möchte im Kompetenzprofil auch meine Auszeichnungen sehen.';
$string['profile_settings_onlygainedbadges'] = 'Ich möchte nur Auszeichnungen sehen, die mir bereits verliehen wurden.';
$string['profile_settings_badges_lineup'] = 'Einstellungen zu Auszeichnungen';
$string['profile_settings_showallcomps'] = 'Alle meine Kompetenzen';
$string['specificcontent'] = 'Schulbezogene Themenbereiche';
$string['specificsubject'] = 'Schulbezogene Gegenstands-/Kompetenzbereiche';


// === Profoundness ===
$string['profoundness_description'] = 'Kompetenzbeschreibung';
$string['profoundness_basic'] = 'Grundkompetenz';
$string['profoundness_extended'] = 'Erweiterte Kompetenz';
$string['profoundness_mainly'] = 'Überwiegend erfüllt';
$string['profoundness_entirely'] = 'Zur Gänze erfüllt';


// === External trainer & eLove ===
$string['block_exacomp_external_trainer_assign_head'] = 'Zuteilung von externen Trainer/innen für Kursteilnehmer/innen erlauben.';
$string['block_exacomp_external_trainer_assign_body'] = 'Erforderlich für die Benutzung der elove App.';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Selbsteinschätzung für Kursteilnehmer/innen in der elove App erlauben.';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Externe TrainerIn zuordnen';
$string['block_exacomp_external_trainer'] = 'AusbilderIn: ';
$string['block_exacomp_external_trainer_student'] = 'Auszubildende: ';
$string['block_exacomp_external_trainer_allstudents'] = 'Alle Kursteilnehmer/innen';


// === Crosssubjects ===
$string['empty_draft'] = 'Neues Thema';
$string['empty_draft_description'] = 'Erstelle dein eigenes Thema - ändere die Beschreibung hier';
$string['add_drafts_to_course'] = 'Ausgewählte Vorlagen im Kurs verwenden';
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
$string['add_descriptors_to_crosssub'] = 'Kompetenzen mit Thema verknüpfen';
$string['manage_crosssubs'] = 'Zurück zur Übersicht';
$string['show_course_crosssubs'] = 'Kurs-Themen ansehen';
$string['existing_crosssub'] = 'Vorhandene Themen in diesem Kurs';
$string['create_new_crosssub'] = 'Neues Thema erstellen';
$string['share_crosssub_for_further_use'] = 'Geben Sie das Thema an Kursteilnehmer/innen frei, um volle Funktionalität zu erhalten.';
$string['available_crosssubjects'] = 'Vorhandene Kursthemen';
$string['crosssubject_drafts'] = 'Themenvorlagen';
$string['de:Vorlage verwenden'] = '';


// === Associations ===
$string['competence_associations'] = 'Verknüpfungen';
$string['competence_associations_explaination'] = 'Das Lernmaterial {$a} ist mit den folgenden Kompetenzen verknüpft:';


// === Weeky schedule ===
$string['weekly_schedule'] = 'Wochenplan';
$string['weekly_schedule_added'] = 'Die Aufgabe wurde in den Planungsspeicher im Wochenplan hinzugefügt.';
$string['weekly_schedule_already_exists'] = 'Die Aufgabe ist bereits im Planungsspeicher im Wochenplan.';
$string['select_student_weekly_schedule'] = 'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Wochenplan Sie sehen möchten.';
$string['example_pool'] = 'Planungsspeicher';
$string['example_trash'] = 'Papierkorb';
$string['choosecourse'] = 'Kurs auswählen: ';
$string['weekly_schedule_added_all'] = 'Die Aufgabe wurde bei allen Kursteilnehmer/innen auf den Planungsspeicher im Wochenplan gelegt.';
$string['weekly_schedule_already_existing_for_one'] = 'Die Aufgabe ist bei mindestens einem Schüler bereits im Planungsspeicher im Wochenplan.';
$string['weekly_schedule_link_to_grid'] = 'Um den Planungsspeicher zu befüllen in den Kompetenzraster wechseln';
$string['pre_planning_storage'] = 'Vorplanungsspeicher';
$string['pre_planning_storage_added'] = 'Lernmaterial wurde zum Vorplanungsspeicher hinzugefügt.';
$string['pre_planning_storage_already_contains'] = 'Lernmateriel bereits im Vorplanungsspeicher enthalten.';
$string['save_pre_planning_selection'] = 'Ausgewählte Lernmaterialien auf den Wochenplan der ausgewählten Schüler/innen legen';
$string['empty_pre_planning_storage'] = 'Vorplanungsspeicher leeren';
$string['noschedules_pre_planning_storage'] = 'Der Vorplanungsspeicher ist leer. Bitte legen Sie über die Kompetenzraster neue Lernmaterialien in den Vorplanungsspeicher.';
$string['empty_trash'] = 'Papierkorb leeren';
$string['empty_pre_planning_confirm'] = 'Auch Beispiele, die ein anderer Lehrer zum Vorplanungsspeicher hinzugefügt hat, werden entfernt. Sind Sie sicher?';
$string['to_weekly_schedule'] = 'Zum Wochenplan';
$string['blocking_event'] = 'Sperrelement erstellen';
$string['blocking_event_title'] = 'Titel';
$string['blocking_event_create'] = 'Zum Vorplanungsspeicher hinzufügen';
$string['weekly_schedule_disabled'] = 'Lernmaterial ist versteckt und kann nicht auf Wochenplan gelegt werden.';
$string['pre_planning_storage_disabled'] = 'Lernmaterial ist versteckt und kann nicht in den Vorplanungsspeicher gelegt werden.';
$string['add_example_for_all_students_to_schedule'] = 'Achtung: Sie sind dabei Lernmaterialien für alle Schüler auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.';
$string['add_example_for_all_students_to_schedule_confirmation'] = 'Sind Sie sicher, dass Sie die Lernmaterialien für alle Schüler auf den Wochenplan legen möchten?';

// === Notifications ===
$string['notification_submission_subject'] = '{$a->site}: {$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht';
$string['notification_submission_body'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href="{$viewurl}">{$a->example}</a> </br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_submission_context'] = 'Abgabe';
$string['notification_grading_subject'] = '{$a->site}: Neue Beurteilungen im Kurs {$a->course}';
$string['notification_grading_body'] = 'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_grading_context'] = 'Beurteilung';
$string['notification_self_assessment_subject'] = '{$a->site}: Neue Selbsteinschätzung im Kurs {$a->course}';
$string['notification_self_assessment_body'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_self_assessment_context'] = 'Selbsteinschätzung';
$string['notification_example_comment_subject'] = '{$a->site}: Neuer Kommentar bei Aufgabe {$a->example}';
$string['notification_example_comment_body'] = 'Lieber/Liebe {$a->receiver}, </br></br> {$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_example_comment_context'] = 'Kommentar';
$string['notification_weekly_schedule_subject'] = '{$a->site}: Neue Aufgabe am Wochenplan';
$string['notification_weekly_schedule_body'] = 'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_weekly_schedule_context'] = 'Wochenplan';
$string['inwork'] = '{$a->inWork}/{$a->total} Materialien in Arbeit';
$string['block_exacomp_notifications_head'] = 'Mitteilungen und Benachrichtigungen';
$string['block_exacomp_notifications_body'] = 'Bei Aktionen wie einer Lernmaterialien-Einreichung oder einer Beurteilung werden Nachrichten an die zuständigen Benutzer gesendet.';



// === Logging ===
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
$string['description_example'] = 'Beschreibung / Schulbuchverweis';
$string['requirements'] = 'Was du schon können solltest: ';
$string['forwhat'] = 'Wofür du das brauchst: ';
$string['howtocheck'] = 'Wie du dein Können prüfen kannst: ';
$string['reached_topic'] = 'Ich habe diese Kompetenz erreicht: ';
$string['submit_example'] = 'Abgeben';
// === Webservice Status ===
$string['enable_rest'] = 'REST Protokoll nicht aktiviert';
$string['access_roles'] = 'Benutzerrollen mit Zugriff auf Webservices';
$string['no_permission'] = 'Berechtigung wurde nicht erteilt';
$string['description_createtoken'] = 'Der Benutzerrolle "Authentifizierte/r Nutzer/in" zusätzliche Rechte erteilen: Website-Administration/Nutzer_innen/Rechte ändern/Rollen verwalten
4.1 Authentifizierte/r Nutzer/in wählen
4.2 Bearbeiten auswählen
4.3 Nach "createtoken" filtern
4.4 Moodle/webservice:createtoken erlauben';
$string['exacomp_not_found'] = 'Exacompservice nicht gefunden';
$string['exaport_not_found'] = 'Exaportservice nicht gefunden';
$string['no_external_trainer'] = 'Keine externen Trainer zugeteilt';
