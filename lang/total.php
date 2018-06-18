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

return [
	// shown in admin plugin list
	'pluginname' => [
		'Exabis Kompetenzraster',
		'Exabis Competence Grid',
	],
	// shown in block title and all headers
	'blocktitle' => [
		'Kompetenzraster',
		'Competence Grid',
	],
	'exacomp:addinstance' => [
		'Exabis Competence Grid auf Kursseite anlegen',
		'Add a Exabis Competence Grid block',
	],
	'exacomp:myaddinstance' => [
		'Exabis Competence Grid auf Startseite anlegen',
		'Add a Exabis Competence Grid block to my moodle',
	],
	'exacomp:teacher' => [
		'Übersicht der Lehrerfunktionen in einem Kurs',
		'overview of trainer actions in a course',
	],
	'exacomp:admin' => [
		'Übersicht der Administratorfunktionen in einem Kurs',
		'overview of administrator actions in a course',
	],
	'exacomp:student' => [
		'Übersicht der Teilnehmerfunktionen in einem Kurs',
		'overview of student actions in a course',
	],
	'exacomp:use' => [
		'Nutzung',
		'use Exabis Competence Grid',
	],
	'exacomp:deleteexamples' => [
		'Lernmaterialien löschen',
		'delete examples',
	],
	'exacomp:assignstudents' => [
		'Externe Trailer zuordnen',
		'Assign external trainers',
	],

	//Cache definition
	'cachedef_visibility_cache' => [
		'Cache zur Performanceerhöhung von Sichtbarkeitsabfragen',
		'Cache to improve performance while checking visibilities',
	],

	// === Admin Tabs ===
	'tab_admin_import' => [
		'Import/Export',
		'Import/Export',
	],
	'tab_admin_settings' => [
		'Website-Einstellungen',
		'Admin Settings',
	],
	'tab_admin_configuration' => [
		'Vorauswahl der Standards',
		'Standards pre-selection',
	],
	'admin_config_pending' => [
		'Vorauswahl der Kompetenzen durch den Administrator notwendig',
		'Standards pre-selection needs to be performed by the Moodle administrator',
	],


	// === Teacher Tabs ===
	'tab_teacher_settings' => [
		'Kurs-Einstellungen',
		'Settings',
	],
	'tab_teacher_settings_configuration' => [
		'Einstellungen',
		'Configuration',
	],
	'tab_teacher_settings_selection_st' => [
		'Bildungsstandard-Auswahl',
		'Schooltype selection',
	],
	'tab_teacher_settings_selection' => [
		'Auswahl der Kompetenzbereiche',
		'Subject selection',
	],
	'tab_teacher_settings_assignactivities' => [
		'Moodle-Aktivitäten zuordnen',
		'Assign Moodle activities',
	],
	'tab_teacher_settings_badges' => [
		'Auszeichnungen bearbeiten',
		'Edit badges',
	],
	'tab_teacher_settings_new_subject' => [
		'Neuen Kompetenzraster anlegen',
		'Create new subject',
	],

	'tab_teacher_report_general' => [
		'General report',
		'General report',
	],
	'tab_teacher_report_annex' => [
		'Zeugnisse ',
		'Annex',
	],
	'tab_teacher_report_annex_title' => [
		'Anlage zum Lernentwicklungsbericht',
		'Annex to the learning development report',
	],
	'create_html' => [
		'Zeugnis im HTML-Format generieren (Voransicht)',
		'generate HTML preview',
	],
	'create_docx' => [
		'Zeugnis im docx-Format generieren',
		'generate docx',
	],
	'tab_teacher_report_annex_template' => [
		'template docx',
		'template docx',
	],
	'tab_teacher_report_annex_delete_template' => [
		'löschen',
		'delete',
	],


	// === Student Tabs ===
	'tab_student_all' => [
		'Alle erworbenen Kompetenzen',
		'All gained competencies',
	],


	// === Generic Tabs (used by Teacher and Students) ===
	'tab_competence_grid' => [
		'Berichte',
		'Reports',
	],
	'tab_competence_overview' => [
		'Kompetenzraster',
		'Competence grid',
	],
	'tab_examples' => [
		'Lernmaterialien',
		'Examples and tasks',
	],
	'tab_badges' => [
		'Meine Auszeichnungen',
		'My badges',
	],
	'tab_competence_profile' => [
		'Kompetenzprofil',
		'Competence profile',
	],
	'tab_competence_profile_profile' => [
		'Profil',
		'Profile',
	],
	'tab_competence_profile_settings' => [
		'Einstellungen',
		'Settings',
	],
	'tab_help' => [
		'Hilfe',
		'Help',
	],
	'tab_profoundness' => [
		'Grund/Erweiterungskompetenzen',
		'Basic & Extended Competencies',
	],
	'tab_cross_subjects' => [
		'Themen',
		'Interdisciplinary Subjects',
	],
	'tab_cross_subjects_overview' => [
		'Übersicht',
		'Overview',
	],
	'tab_cross_subjects_course' => [
		'Kursthemen',
		'Course Interdisciplinary Subjects',
	],
	'tab_weekly_schedule' => [
		'Wochenplan',
		'Weekly Schedule',
	],
	'tab_group_reports' => [
		'Gruppenberichte',
		'Group Reports',
	],
	'assign_descriptor_to_crosssubject' => [
		'Die Teilkompetenz "{$a}" den folgenden Themen zuordnen:',
		'Assign the competence "{$a}" to the following interdisciplinary subjects:',
	],
	'assign_descriptor_no_crosssubjects_available' => [
		'Es sind keine Themen vorhanden, legen Sie welche an.',
		'No interdisciplinary subjects are available.',
	],
	'first_configuration_step' => [
		'Der erste Konfigurationsschritt besteht darin, Daten in das Exabis Kompetenzraster Modul zu importieren.',
		'The first step of the configuration is to import some data to Exabis Competence Grid.',
	],
	'second_configuration_step' => [
		'Im zweiten Konfigurationsschritt müssen Bildungsstandards ausgewählt werden.',
		'In this configuration step you have to pre-select standards.',
	],
	'next_step' => [
		'Dieser Konfigurationsschritt wurde abgeschlossen. Klicken Sie hier um zum Nächsten zu gelangen.',
		'This configuration step has been completed. Click here to continue configuration.',
	],
	'next_step_teacher' => [
		'Die Konfiguration, die vom Administrator vorgenommen werden muss, ist hiermit abgeschlossen. Um mit der kursspezifischen Konfiguration fortzufahren klicken Sie hier.',
		'The configuration that has to be done by the administrator is now completed. To continue with the course specific configuration click here.',
	],
	'teacher_first_configuration_step' => [
		'Im ersten Konfigurationsschritt der Kurs-Standards müssen einige generelle Einstellungen getroffen werden.',
		'The first step of course configuration is to adjust general settings for your course.',
	],
	'teacher_second_configuration_step' => [
		'Im zweiten Konfigurationsschritt müssen Themenbereiche ausgewählt werden, mit denen Sie in diesem Kurs arbeiten möchten.',
		'In the second configuration step topics to work with in this course have to be selected.',
	],
	'teacher_third_configuration_step' => [
		'Im nächsten Schritt werden Moodle-Aktivitäten mit Kompetenzen assoziiert. ',
		'The next step is to associate Moodle activities with competencies ',
	],
	'teacher_third_configuration_step_link' => [
		'(Optional: Wenn Sie nicht mit Moodle-Aktivitäten arbeiten möchten, dann entfernen Sie das Häkchen "Ich möchte mit Moodle-Aktivitäten arbeiten" im Tab "Einstellungen".)',
		'(Optional: if you don\'t want to work with activities untick the setting "I want to work with Moodle activities" in the tab "Configuration")',
	],
	'completed_config' => [
		'Die Exabis Kompetenzraster Konfiguration wurde abgeschlossen.',
		'The configuration of Exabis Competence Grid is completed.',
	],
	'optional_step' => [
		'In Ihrem Kurs sind noch keine Teilnehmer/innen eingeschrieben, betätigen Sie diesen Link wenn Sie das jetzt machen möchten.',
		'There are no participants in your course yet. If you want to enrol some please use this link.',
	],
	'next_step_first_teacher_step' => [
		'Klicken Sie hier um zum nächsten Schritt zu gelangen.',
		'Click here to continue configuration.',
	],


	// === Block Settings ===
	'settings_xmlserverurl' => [
		'Server-URL',
		'Server-URL',
	],
	'settings_configxmlserverurl' => [
		'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten',
		'Url to a xml file, which is used for keeping the database entries up to date',
	],
	'settings_autotest' => [
		'Automatischer Kompetenzerwerb durch Tests',
		'Automatical gain of competence through quizzes',
	],
	'settings_autotest_description' => [
		'Kompetenzen die mit Tests verbunden sind, gelten automatisch als erworben, wenn der angegebene Test-Prozentwert erreicht wurde',
		'Competences that are associated with quizzes are gained automatically if needed percentage of quiz is reached',
	],
	'settings_testlimit' => [
		'Testlimit in %',
		'Quiz-percentage needed to gain competence',
	],
	'settings_testlimit_description' => [
		'Dieser Prozentwert muss erreicht werden, damit die Kompetenz als erworben gilt',
		'This percentage has to be reached to gain the competence',
	],
	'settings_usebadges' => [
		'Badges/Auszeichnungen verwenden',
		'Use badges',
	],
	'settings_usebadges_description' => [
		'Anhaken um den Badges/Auszeichnungen Kompetenzen zuzuteilen',
		'Check to associate badges with competences',
	],
	'settings_interval' => [
		'Einheitenlänge',
		'Unit duration',
	],
	'settings_interval_description' => [
		'Die Länge der Einheiten im Wochenplan in Minuten',
		'Duration of the units in the schedule',
	],
	'settings_scheduleunits' => [
		'Anzahl der Einheiten',
		'Anmount of units',
	],
	'settings_scheduleunits_description' => [
		'Anzahl der Einheiten im Wochenplan',
		'Amount of units in the schedule',
	],
	'settings_schedulebegin' => [
		'Beginn der Einheiten',
		'Schedule begin',
	],
	'settings_schedulebegin_description' => [
		'Beginnzeitpunkt der ersten Einheit im Wochenplan. Format hh:mm',
		'Begin time for the first unit in the schedule. Format hh:mm',
	],
	'settings_admin_scheme' => [
		'Globales Bewertungsniveau',
		'Global difficulty level',
	],
	'settings_admin_scheme_description' => [
		'Beurteilungen können auf unterschiedlichem Niveau erfolgen.',
		'Grading can be done on different difficulty levels.',
	],
	'settings_admin_scheme_none' => [
		'keine Niveaus',
		'no global difficulty levels',
	],
	'settings_additional_grading' => [
		'Angepasste Bewertung',
		'Adapted grading',
	],
	'settings_additional_grading_description' => [
		'Bewertung für Teilkompetenzen und Lernmaterialien global auf "nicht erreicht(0)" - "vollständig erreicht(3)" beschränken',
		'Grading limited from "not gained(0)" - "completely gained(3)"',
	],
	'settings_periods' => [
		'Einträge für Zeittafel',
		'Timetable entries',
	],
	'settings_periods_description' => [
		'Der Wochenplan ist flexibel an jedes Stunden- und Pausenraster anpassbar. Verwenden Sie im Textblock für jeden Zeitblock eine neue Zeile. Es sind beliebige Texteinträge erlaubt, z.B. "1. Std" oder "07:30 - 09:00".',
		'Weekly schedule can be adapted to any timetable. Use one row in the text area for each time entry. You can use any format you like, e.g. "1st hour" or "07:30 - 09:00".',
	],

	// === Unit Tests ===
	'unittest_string' => [
		'result_unittest_string',
		'result_unittest_string',
	],
	'de:unittest_string2' => [
		'result_unittest_string2',
		'result_unittest_string2',
	],
	'de:unittest_string3' => [
		null,
		'result_unittest_string3',
	],
	'de:unittest_param {$a} unittest_param' => [
		'result_unittest_param {$a} result_unittest_param',
		'result_unittest_param {$a} result_unittest_param',
	],
	'de:unittest_param2 {$a->val} unittest_param2' => [
		'result_unittest_param2 {$a->val} result_unittest_param2',
		'result_unittest_param2 {$a->val} result_unittest_param2',
	],


	// === Learning agenda ===
	'LA_MON' => [
		'Mo',
		'MON',
	],
	'LA_TUE' => [
		'Di',
		'TUE',
	],
	'LA_WED' => [
		'Mi',
		'WED',
	],
	'LA_THU' => [
		'Do',
		'THU',
	],
	'LA_FRI' => [
		'Fr',
		'FRI',
	],
	'LA_todo' => [
		'Was mache ich?',
		'What do I do?',
	],
	'LA_learning' => [
		'Was kann ich lernen?',
		'What can I learn?',
	],
	'LA_student' => [
		'S',
		'S',
	],
	'LA_teacher' => [
		'L',
		'T',
	],
	'LA_assessment' => [
		'Einschätzung',
		'assessment',
	],
	'LA_plan' => [
		'Arbeitsplan',
		'working plan',
	],
	'LA_no_learningagenda' => [
		'Es sind keine Lernagenden in der ausgewählten Woche vorhanden.',
		'There is no learning agenda available for this week.',
	],
	'LA_no_student_selected' => [
		'-- kein(e) Kursteilnehmer/in ausgewählt --',
		'-- no student selected --',
	],
	'LA_select_student' => [
		'Wählen Sie bitte eine(n) Kursteilnehmer/in aus, um seine Lernagenda einzusehen.',
		'Please select a student to view his learning agenda.',
	],
	'LA_no_example' => [
		'Kein Lernmaterial zugeordnet',
		'no example available',
	],
	'LA_backtoview' => [
		'Zurück zur Originalansicht',
		'back to original view',
	],
	'LA_from_n' => [
		' von ',
		' from ',
	],
	'LA_from_m' => [
		' vom ',
		' from ',
	],
	'LA_to' => [
		' bis zum ',
		' to ',
	],
	'LA_enddate' => [
		'Enddatum',
		'end date',
	],
	'LA_startdate' => [
		'Startdatum',
		'start date',
	],


	// === Help ===
	'help_content' => [
		'<h1>Video zur Einführung</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
',
		'<h1>Introduction Video</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
',
	],


	// === Import ===
	'importinfo' => [
		'Erstellen Sie Ihre eigenen Kompetenzen/Standards auf <a target="_blank" href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a target="_blank" href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch. Für österreichische Standards besuchen Sie bitte <a target="_blank" href="http://bist.edugroup.at">http://bist.edugroup.at</a>',
		'Please create your outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file.',
	],
	'importwebservice' => [
		'Es besteht auch die Möglichkeit die Daten über ein <a href="{$a}">Webservice</a> aktuell zu halten.',
		'It is possible to keep the data up to date via a <a href="{$a}">webservice</a>.',
	],
	'import_max_execution_time' => [
		'Wichtig: Die aktuellen Servereinstellung beschränken den Import auf {$a} Sekunden. Falls der Import länger dauert, wird er abgebrochen und es werden keine Daten importiert. Am Bildschirm wird in diesem Fall eine Sever Fehlermeldung (wie z.B. "500 Internal Server Error") angezeigt.',
		'Important: The current Serversettings limit the Import to run up to {$a} seconds. If the import takes longer no data will be imported and the browser may display "500 Internal Server Error".',
	],
	'importdone' => [
		'Die allgemeinen Bildungsstandards sind bereits importiert.',
		'data has already been imported from xml',
	],
	'importpending' => [
		'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und wählen Sie anschließend im Tab Bildungsstandard die anzuzeigenden Lernlistenbereiche aus.',
		'no data has been imported yet!',
	],
	'doimport' => [
		'Bildungsstandards importieren',
		'Import outcomes/standards',
	],
	'doimport_again' => [
		'Weitere Bildungsstandards importieren',
		'Import additional outcomes/standards',
	],
	'doimport_own' => [
		'Schulspezifische Bildungsstandards importieren',
		'Import individual outcomes/standards',
	],
	'delete_own' => [
		'Schulspezifische Bildungsstandards löschen',
		'Delete individual outcomes/standards',
	],
	'delete_success' => [
		'Schulspezifische Bildungsstandards wurden gelöscht',
		'Individual outcomes/standards have been deleted',
	],
	'delete_own_confirm' => [
		'Schulspezifische Bildungsstandards wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.',
		'Are you sure to delete the individual outcomes/standards?',
	],
	'importsuccess' => [
		'Daten erfolgreich importiert!',
		'data was successfully imported!',
	],
	'importsuccess_own' => [
		'Eigene Daten erfolgreich importiert!',
		'individual data was imported successfully!',
	],
	'importfail' => [
		'Es ist ein Fehler aufgetreten.',
		'an error has occured during import',
	],
	'noxmlfile' => [
		'Ein Import ist derzeit nicht möglich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>',
		'There is no data available to import. Please visit <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a> to download the required outcomes to the blocks xml directory.',
	],
	'oldxmlfile' => [
		'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="http://www.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.',
		'You are using an outdated xml-file. Please create new outcomes/standards at <a href="http://www.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.',
	],
	'do_demo_import' => [
		'Importieren Sie einen Demodatensatz, um zu sehen wie Exabis Kompetenzraster funktioniert.',
		'import demo data to see how Exabis Competence Grid works.',
	],


	// === Configuration ===
	'explainconfig' => [
		'Um das Modul exabis competences verwenden zu können, müssen hier die Kompetenzbereiche der Moodle-Instanz selektiert werden.',
		'Your outcomes have already been imported. In this configuration you have to make the selection of the main standards you would like to use in this Moodle installation.',
	],
	'save_selection' => [
		'Auswahl speichern',
		'Save selection',
	],
	'save_success' => [
		'Änderungen erfolgreich übernommen',
		'changes were successful',
	],


	// === Course-Configuration ===
	'grading_scheme' => [
		'Bewertungsschema',
		'grading scheme',
	],
	'uses_activities' => [
		'Ich verwende Moodle Aktivitäten zur Beurteilung',
		'I work with Moodle activites',
	],
	'show_all_descriptors' => [
		'Alle Lernlisten im Überblick anzeigen',
		'Show all outcomes in overview',
	],
	'show_all_examples' => [
		'Externe Lernmaterialien für Kursteilnehmer/innen anzeigen',
		'Show external examples for students',
	],
	'useprofoundness' => [
		'Grund- und Erweiterungskompetenzen verwenden',
		'Use basic and extended competencies',
	],
	'usetopicgrading' => [
		'Beurteilung von Kompetenzbereichen ermöglichen',
		'Enable topic gradings',
	],
	'usesubjectgrading' => [
		'Beurteilung von Fächern ermöglichen',
		'Enable subject gradings',
	],
	'usenumbering' => [
		'Automatische Nummerierung im Kompetenzraster verwenden',
		'Enable automatic numbering in the competence grid',
	],
	'usenostudents' => [
		'Ohne Kursteilnehmer/innen arbeiten',
		'Use without students',
	],
	'profoundness_0' => [
		'Nicht erreicht',
		'not reached',
	],
	'profoundness_1' => [
		'Zum Teil erreicht',
		'Partially gained',
	],
	'profoundness_2' => [
		'Erreicht',
		'Fully gained',
	],
	'filteredtaxonomies' => [
		'Lernmaterialien werden anhand der ausgewählten Taxonomien verwendet:',
		'Examples are filtered accordingly to the following taxonomies:',
	],
	'show_all_taxonomies' => [
		'Alle Taxonomien',
		'All taxonomies',
	],
	'warning_use_activities' => [
		'Hinweis: Sie arbeiten jetzt mit Moodle-Aktivitäten die mit Kompetenzen verknüpft sind. Stellen Sie sicher, dass in diesem Kurs mit den selben Kompetenzen weitergearbeitet wird.',
		'Warning: you are now working with Moodle-activites that are associated with competences. Please verify that the same outcomes are used as before.',
	],
	'delete_unconnected_examples' => [
		'Wenn Sie Themenbereiche abwählen, mit denen Lernmaterialien verknüpft sind die noch am Wochenplan liegen, werden diese aus dem Wochenplan entfernt.',
		'If you are deselecting topics which are associated with examples used in the weekly schedule, these examples will be removed.',
	],


	// === Badges ===
	'mybadges' => [
		'Meine Auszeichnungen',
		'My badges',
	],
	'pendingbadges' => [
		'Anstehende Auszeichnungen',
		'Pending badges',
	],
	'no_badges_yet' => [
		'Keine Auszeichnungen verfügbar',
		'no badges available',
	],
	'description_edit_badge_comps' => [
		'Hier können Sie der ausgewählten Auszeichnung Kompetenzen zuordnen.',
		'Here you can associate the selected badge with outcomes.',
	],
	'to_award' => [
		'Um diese Auszeichnung zu erwerben, müssen Kompetenzen zugeordnet werden.',
		'To award this badge in exacomp you have to configure competencies',
	],
	'to_award_role' => [
		'Um diese Auszeichnung zu erwerben, müssen sie das "manuelle Verleihung" Kriterium hinzufügen.',
		'To award this badge in exacomp you have to add the "Manual issue by role" criteria',
	],
	'ready_to_activate' => [
		'Diese Auszeichnung kann aktiviert werden: ',
		'This badge is ready to be activated: ',
	],
	'conf_badges' => [
		'Auszeichnungen konfigurieren',
		'configure badges',
	],
	'conf_comps' => [
		'Kompetenzen zuordnen',
		'configure competences',
	],


	// === Examples ===
	'example' => [
		'Lernmaterial',
		'Example',
	],
	'sorting' => [
		'Sortierung wählen: ',
		'select sorting: ',
	],
	'subject' => [
		'Bildungsstandard',
		'subject',
	],
	'topic' => [
		'Kompetenzbereich',
		'Topic',
	],
	'taxonomies' => [
		'Niveaustufen',
		'taxonomies',
	],
	'show_all_course_examples' => [
		'Lernmaterialien aus allen Kursen anzeigen',
		'Show examples from all courses',
	],
	'name_example' => [
		'Name',
		'Name',
	],
	'comp_based' => [
		'Nach Kompetenzen sortieren',
		'sort by competencies',
	],
	'examp_based' => [
		'Nach Lernmaterialien sortieren',
		'sort by examples',
	],

	// === Icons ===
	'assigned_example' => [
		'Zugeteiltes Lernmaterial',
		'Assigned Example',
	],
	'task_example' => [
		'Aufgabenstellung',
		'Tasks',
	],
	'extern_task' => [
		'Externe Aufgabenstellung',
		'External Task',
	],
	'total_example' => [
		'Gesamtmaterial',
		'Complete Example',
	],


	// === Example Upload ===
	'example_upload_header' => [
		'Eigenes Lernmaterial hochladen',
		'Upload my own task/example',
	],
	'taxonomy' => [
		'Niveaustufe',
		'Taxonomy',
	],
	'descriptors' => [
		'Kompetenzen',
		'Competencies',
	],
	'filerequired' => [
		'Es muss eine Datei ausgewählt sein.',
		'A file must be selected.',
	],
	'titlenotemtpy' => [
		'Es muss ein Name eingegeben werden.',
		'A name is required.',
	],
	'solution' => [
		'Musterlösung',
		'Solution',
	],
	'hide_solution' => [
		'Musterlösung verbergen',
		'Hide solution',
	],
	'show_solution' => [
		'Musterlösung anzeigen',
		'Show solution',
	],
	'hide_solution_disabled' => [
		'Musterlösung ist bereits für alle Schüler/innen versteckt',
		'The solution is already hidden for all students',
	],
	'submission' => [
		'Abgabe',
		'Submission',
	],
	'assignments' => [
		'Moodle Aktivitäten',
		'Assignments',
	],
	'files' => [
		'Dateien',
		'Files',
	],
	'link' => [
		'Link',
		'Link',
	],
	'dataerr' => [
		'Es muss zumindest ein Link oder eine Datei hochgeladen werden!',
		'At least a link or a file are required!',
	],
	'linkerr' => [
		'Bitte geben Sie einen korrekten Link ein!',
		'The given link is not valid!',
	],
	'isgraded' => [
		'Die Aufgabe wurde bereits beurteilt und kann daher nicht mehr eingereicht werden.',
		'The example is already graded and therefore a submission is not possible anymore',
	],
	'allow_resubmission' => [
		'Aufgabe zur erneuten Abgabe freigeben',
		'Allow a new submission for this example',
	],
	'allow_resubmission_info' => [
		'Die Aufgabe wurde zur erneuten Abgabe freigegeben.',
		'The example is now allowed to be resubmited.',
	],


	// === Assign competencies ===
	'header_edit_mode' => [
		'Sie befinden sich im Bearbeitungsmodus',
		'Editing mode is turned on',
	],
	'comp_-1' => [
		'ohne Angabe',
		'no information',
	],
	'comp_0' => [
		'nicht erreicht',
		'not gained',
	],
	'comp_1' => [
		'teilweise',
		'partly',
	],
	'comp_2' => [
		'überwiegend',
		'mostly',
	],
	'comp_3' => [
		'vollständig',
		'completely',
	],
	'comp_-1_short' => [
		'oA',
		'ni',
	],
	'comp_0_short' => [
		'ne',
		'ng',
	],
	'comp_1_short' => [
		'te',
		'pg',
	],
	'comp_2_short' => [
		'üe',
		'mg',
	],
	'comp_3_short' => [
		've',
		'cg',
	],
	'delete_confirmation' => [
		'Soll "{$a}" wirklich gelöscht werden?',
		'Do you really want to delete "{$a}"?',
	],
	'legend_activities' => [
		'Moodle-Aktivitäten',
		'Moodle activities',
	],
	'legend_eportfolio' => [
		'ePortfolio',
		'ePortfolio',
	],
	'legend_notask' => [
		'Keine Moodle-Aktivität/Quiz für diese Kompetenz abgegeben',
		'No Moodle activities/quizzes have been submitted for this outcome',
	],
	'legend_upload' => [
		'Eigenes Lernmaterial hochladen',
		'Upload your own task/example',
	],
	'allniveaus' => [
		'Alle Lernfortschritte',
		'All difficulty levels',
	],
	'choosesubject' => [
		'Kompetenzbereich auswählen',
		'Choose subject: ',
	],
	'choosetopic' => [
		'Lernfortschritte auswählen',
		'Choose topic',
	],
	'choosestudent' => [
		'Kursteilnehmer/in auswählen: ',
		'Choose student: ',
	],
	'choose_student' => [
		'Auswahl der Schüler/innen: ',
		'Choose students: ',
	],
	'choosedaterange' => [
		'Betrachtungszeitraum auswählen: ',
		'Pick a date range: ',
	],
	'cleardaterange' => [
		'Zurücksetzen',
		'Clear range',
	],
	'seperatordaterange' => [
		'bis',
		'to',
	],
	'own_additions' => [
		'Schulische Ergänzung: ',
		'Curricular additions: ',
	],
	'delete_confirmation_descr' => [
		'Soll die Kompetenz "{$a}" wirklich für alle Kurse gelöscht werden?',
		'Do you really want to delete the competence "{$a}"?',
	],
	'import_source' => [
		'Importiert von: {$a}',
		'Imported from: {$a}',
	],
	'local' => [
		'Lokal',
		'Local',
	],
	'unknown_src' => [
		'unbekannte Quelle',
		'Unknown source',
	],
	'override_notice1' => [
		'Dieser Eintrag wurde von ',
		'This entry was editied by ',
	],
    'override_notice2' => [
        ' bearbeitet. Wirklich ändern?',
        ' before. Continue?',
    ],
	'unload_notice' => [
		'Die Seite wirklich verlassen? Ungespeicherte Änderungen gehen verloren.',
		'Are you sure? Unsaved changes will be lost.',
	],
	'example_sorting_notice' => [
		'Bitte zuerst die aktuellen Bewertungen speichern',
		'Please save the changes first.',
	],
	'newsubmission' => [
		'Erneute Abgabe',
		'New Submission',
	],
	'value_too_large' => [
		'Fehler: Benotungen dürfen nicht größer als 6.0 sein!',
		'Error: Values above 6.0 are not allowed',
	],
	'value_too_low' => [
		'Fehler: Benotungen dürfen nicht kleiner als 1.0 sein!',
		'Error: Values below 1.0 are not allowed',
	],
	'value_not_allowed' => [
		'Fehler: Benotungen müssen Zahlenwerte zwischen 1.0 und 6.0 sein',
		'Error: Values need to be numbers between 1.0 and 6.0',
	],
	'competence_locked' => [
		'Beurteilung vorhanden oder Lernmaterial in Verwendung!',
		'Evaluation exists or learning material is used',
	],
    'save_changes_competence_evaluation' => [
        '�nderungen wurden gespeichert!',
        'Changes were saved',
    ],
	// === Example Submission ===
	'example_submission_header' => [
		'Aufgabe {$a} bearbeiten',
		'Edit example {$a}',
	],
	'example_submission_info' => [
		'Du bist dabei die Aufgabe "{$a}" zu bearbeiten. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.',
		'You are about to edit the example "{$a}". Your submission will be saved in Exabis ePortfolio and Teachers can view it there.',
	],
	'example_submission_subject' => [
		'Neue Abgabe',
		'New submission',
	],
	'example_submission_message' => [
		'Im Kurs {$a->course} wurde von Teilnehmer/in {$a->student} eine neue Abgabe eingereicht.',
		'Student {$a->student} handed in a new submission in {$a->course}.',
	],
	'submissionmissing' => [
		'Es müssen zumindest ein Link oder eine Datei abgegeben werden',
		'Es müssen zumindest ein Link oder eine Datei abgegeben werden',
	],
	'associated_activities' => [
		'Zugeordnete Moodle-Aktivitäten:',
		'Associated Moodle Activities:',
	],
	'usernosubmission' => [
		'Offene Moodle-Aktivitäten',
		'{$a} has not yet submitted any Moodle activities or quizzes associated with this outcome',
	],
	'grading' => [
		'Bewertung',
		'Grading',
	],
	'teacher_tipp' => [
		'Tipp',
		'tip',
	],
	'teacher_tipp_1' => [
		'Diese Kompetenz wurde bei ',
		'This competence has been associated with ',
	],
	'teacher_tipp_2' => [
		' Moodle-Aktivität(en) zugeordnet und bereits bei ',
		' Moodle activities and has been reached with ',
	],
	'teacher_tipp_3' => [
		' Moodle-Aktivität(en) in der Kompetenz-Detailansicht erfüllt.',
		' outcomes.',
	],
	'print' => [
		'Drucken',
		'Print',
	],
	'eportitems' => [
		'Zu diesem Deskriptor eingereichte ePortfolio-Artefakte:',
		'Submitted ePortfolio artifacts:',
	],
	'eportitem_shared' => [
		' (geteilt)',
		' (shared)',
	],
	'eportitem_notshared' => [
		' (nicht geteilt)',
		' (not shared)',
	],
	'teachershortcut' => [
		'L',
		'T',
	],
	'studentshortcut' => [
		'S',
		'S',
	],
	'overview' => [
		'Hier haben Sie einen Überblick über die Teilkompetenzen der ausgewählten Lernwegeliste und die zugeordneten Aufgaben. Sie können das Erreichen der jeweiligen Teilkompetenz individuell bestätigen.',
		'This is an overview of all students and the course competencies.',
	],
	'showevaluation' => [
		'Um die Selbsteinschätzung einzusehen, klicken Sie <a href="{$a}">hier</a>',
		'To show self-assessment click <a href="{$a}">here</a>',
	],
	'hideevaluation' => [
		'Um die Selbsteinschätzung auszublenden, klicken Sie <a href="{$a}">hier</a>',
		'To hide self-assessment click <a href="{$a}">here</a>',
	],
	'showevaluation_student' => [
		'Um die Einschätzung der TrainerInnen zu aktivieren, klicke <a href="{$a}">hier</a>.',
		'To show trainer-assessment click <a href="{$a}">here</a>',
	],
	'hideevaluation_student' => [
		'Um die Einschätzung der TrainerInnen zu deaktivieren, klicke <a href="{$a}">hier</a>.',
		'To hide trainer-assessment click <a href="{$a}">here</a>',
	],
	'columnselect' => [
		'Spaltenauswahl',
		'Column selection',
	],
	'allstudents' => [
		'Alle  Kursteilnehmer/innen',
		'All students',
	],
	'nostudents' => [
		'Keine  Kursteilnehmer/innen',
		'No students',
	],
	'statistic' => [
		'Gesamtübersicht',
		'Overview',
	],
	'niveau' => [
		'Lernfortschritt',
		'Difficulty Level',
	],
	'niveau_short' => [
		'LFS',
		'Level',
	],
	'competence_grid_niveau' => [
		'Niveau',
		'difficulty Level',
	],
	'competence_grid_additionalinfo' => [
		'Note',
		'grade',
	],
	'descriptor' => [
		'Kompetenz',
		'competence',
	],
	'descriptor_child' => [
		'Teilkompetenz',
		'sub competence',
	],
	'assigndone' => [
		'Aufgabe erledigt: ',
		'task done: ',
	],
	// === metadata ===
	'subject_singular' => [
		'Fach',
		'Field of competence',
	],
	'comp_field_idea' => [
		'Kompetenzbereich/Leitidee',
		'Skill',
	],
	'comp' => [
		'Kompetenz',
		'Topic',
	],
	'progress' => [
		'Lernfortschritt',
		'Progress',
	],
	'instruction' => [
		'Anleitung',
		'Instruction',
	],
	'instruction_content' => [
		'Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche 
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden. 
				Darüber hinaus können Sie das Erreichen der Teilkompetenzen
				eintragen. Je nach Konzept der Schule kann die Bearbeitung des
				Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz
				markiert oder die Qualität der Bearbeitung / der Kompetenzerreichung
				gekennzeichnet werden. Keinenfalls müssen die Schüler/innen 
				alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Schüler/innen
				müssen dann keine zugehörigen Lernmaterialien
				bearbeiten.',
		'This is an overview for learning resources that are associated with 
				standards and ticking off competencies for students. Students can
				assess their competencies. Moodle activities that were turned in by
				students are displayed with a red icon. ePortfolio-artifacts of students 
				are displayed in blue icons.',
	],


	// === Activities ===
	'explaineditactivities_subjects' => [
		'Hier können Sie den erstellten Aufgaben Lernlisten zuordnen.',
		'',
	],
	'niveau_filter' => [
		'Niveaus filtern',
		'Filter difficulty levels',
	],
	'module_filter' => [
		'Aktivitäten filtern',
		'filter activities',
	],
	'apply_filter' => [
		'Filter anwenden',
		'apply filter',
	],
	'no_topics_selected' => [
		'Konfiguration für Exabis Kompetenzraster wurde noch nicht abgeschlossen. Bitte wählen Sie zuerst Gegenstände aus, denen Sie dann Moodle-Aktivitäten zuordnen können.',
		'configuration of Exabis Competence Grid is not completed yet. please chose a topic that you would like to associate Moodle activities with',
	],
	'no_activities_selected' => [
		'Bitte ordnen Sie den erstellten Moodle-Aktivitäten Kompetenzen zu.',
		'please associate Moodle activities with competences',
	],
	'no_activities_selected_student' => [
		'In diesem Bereich sind derzeit keine Daten vorhanden.',
		'There is no data available yet',
	],
	'no_course_activities' => [
		'In diesem Kurs wurden noch keine Moodle-Aktivitäten erstellt, klicken Sie hier um dies nun zu tun.',
		'No Moodle activities found in this course - click here to create some.',
	],
	'all_modules' => [
		'Alle Aktivitäten',
		'all activities',
	],
	'tick_some' => [
		'Bitte treffen Sie eine Auswahl!',
		'Please make a selection!',
	],


	// === Competence Grid ===
	'infolink' => [
		'Weiter Informationen: ',
		'Additional information: ',
	],
	'textalign' => [
		'Textuelle Ausrichtung ändern',
		'Switch text align',
	],
	'selfevaluation' => [
		'Selbsteinschätzung',
		'self assessment',
	],
	'selfevaluation_short' => [
		'SE',
		'SA',
	],
	'teacherevaluation' => [
		'Einschätzung des Beurteilenden',
		'trainer assessment',
	],
	'competencegrid_nodata' => [
		'Sollte der Kompetenzraster leer sein, wurden für die Deskriptoren des ausgewählten Gegenstands keine Niveaus in den Daten definiert',
		'In case the competence grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.',
	],
	'statistic_type_descriptor' => [
		'Wechsel zur Statistik der Teilkompetenzen',
		'Change to descriptor statistics',
	],
	'statistic_type_example' => [
		'Wechsel zur Statistik der Aufgaben',
		'Change to example statistics',
	],
	'reports' => [
		'Berichte',
		'Type of report',
	],


	// === Competence Profile ===
	'name' => [
		'Name',
		'Name',
	],
	'city' => [
		'Wohnort',
		'City',
	],
	'total' => [
		'Gesamt',
		'total',
	],
	'select_student' => [
		'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Kompetenzprofil Sie sehen möchten.',
		'Please select a student first',
	],
	'my_comps' => [
		'Meine Kompetenzen',
		'My Competencies',
	],
	'my_badges' => [
		'Meine Auszeichnungen',
		'My Badges',
	],
	'innersection1' => [
		'Rasterübersicht',
		'Grid view',
	],
	'innersection2' => [
		'Statistik',
		'Statistics',
	],
	'innersection3' => [
		'Übersicht über die Kompetenzen und Aufgaben',
		'Comparison: Teacher-Student',
	],
	// === Competence Profile Settings ===
	'profile_settings_choose_courses' => [
		'In Exabis Kompetenzraster beurteilen TrainerInnen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgewählt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.',
		'Using Exabis Competence Grid trainers assess your competencies in various subjects. You can select which course to include in the competence profile.',
	],
	'specificcontent' => [
		'Schulbezogene Themenbereiche',
		'site-specific topics',
	],

	'topic_3dchart' => [
		'3D Diagramm',
		'3D Chart',
	],

	'topic_3dchart_empty' => [
		'Es liegen keine Beurteilungen für diesen Kompetenzbereich vor.',
		'No gradings available',
	],
	// === Profoundness ===
	'profoundness_description' => [
		'Kompetenzbeschreibung',
		'Description',
	],
	'profoundness_basic' => [
		'Grundkompetenz',
		'Basic competence',
	],
	'profoundness_extended' => [
		'Erweiterte Kompetenz',
		'Extended competence',
	],
	'profoundness_mainly' => [
		'Überwiegend erfüllt',
		'Mainly achieved',
	],
	'profoundness_entirely' => [
		'Zur Gänze erfüllt',
		'Entirely achieved',
	],


	// === External trainer & eLove ===
	'block_exacomp_external_trainer_assign_head' => [
		'Zuteilung von externen Trainer/innen für Kursteilnehmer/innen erlauben.',
		'Allow assigning external trainers for students.',
	],
	'block_exacomp_external_trainer_assign_body' => [
		'Erforderlich für die Benutzung der elove App.',
		'This is required for using the elove app.',
	],
	'block_exacomp_elove_student_self_assessment_head' => [
		'Selbsteinschätzung für Kursteilnehmer/innen in der elove App erlauben.',
		'Allow self-assessment for students in the elove app',
	],
	'block_exacomp_elove_student_self_assessment_body' => [
		'',
		'',
	],
	'block_exacomp_external_trainer_assign' => [
		'Externe TrainerIn zuordnen',
		'Assign external trainers',
	],
	'block_exacomp_external_trainer' => [
		'AusbilderIn: ',
		'Trainer',
	],
	'block_exacomp_external_trainer_student' => [
		'Auszubildende: ',
		'Student',
	],
	'block_exacomp_external_trainer_allstudents' => [
		'Alle Kursteilnehmer/innen',
		'All Students',
	],


	// === Crosssubjects ===
	'add_drafts_to_course' => [
		'Ausgewählte Vorlagen im Kurs verwenden',
		'Add drafts to course',
	],
	'crosssubject' => [
		'Thema',
		'Interdisciplinary Subject',
	],
	'help_crosssubject' => [
		'Die Zusammenstellung des Themas erfolgt für die ganze Schule über den Reiter Kompetenzraster. Sie können hier kursspezifisch Kompetenzen, Teilkompetenzen und Lernmaterial ausblenden. Lernmaterial kann hier ergänzt werden. Dieses wird automatisch in die Lernwegeliste integriert.',
		'The compilation of a subject is done for the whole Moodle installation (school) using the tab learning path. Here you can selectively deactivate course-specific competences, sub-competences and materials. Individual learning material can also be added. This is then automatically added to the learning paths.',
	],
	'description' => [
		'Beschreibung',
		'Description',
	],
	'numb' => [
		'Nummer',
		'Number',
	],
	'no_student' => [
		'-- kein(e) Kursteilnehmer/in ausgewählt --',
		'-- no participant selected --',
	],
	'no_student_edit' => [
		'Editiermodus',
		'edit mode - no participant',
	],
	'save_as_draft' => [
		'Thema als Vorlage speichern',
		'Save interdisciplinary subject as draft',
	],
	'comps_and_material' => [
		'Kompetenzen und Lernmaterial',
		'outcomes and exercises',
	],
	'no_crosssubjs' => [
		'In diesem Kurs gibt es noch kein Thema.',
		'No interdisciplinary subjects available.',
	],
	'delete_drafts' => [
		'Ausgewählte Vorlagen löschen',
		'Delete selected drafts',
	],
	'share_crosssub' => [
		'Thema für Kursteilnehmer/innen freigeben',
		'Share interdisciplinary subject with participants',
	],
	'share_crosssub_with_students' => [
		'Das Thema "{$a}" für folgende Kursteilnehmer/innen freigeben: ',
		'Share interdisciplinary subject "{$a}" with the following participants: ',
	],
	'share_crosssub_with_all' => [
		'Das Thema "{$a}" für <b>alle</b> Kursteilnehmer/innen freigeben: ',
		'Share interdisciplinary subject "{$a}" with all participants: ',
	],
	'new_crosssub' => [
		'Eigenes Thema erstellen',
		'Create new interdisciplinary subject',
	],
	'add_crosssub' => [
		'Thema erstellen',
		'Create interdisciplinary subject',
	],
	'nocrosssubsub' => [
		'Allgemeine Themen',
		'General Interdisciplinary Subjects',
	],
	'delete_crosssub' => [
		'Thema löschen',
		'Delete interdisciplinary subject',
	],
	'confirm_delete' => [
		'Soll dieses Thema wirklich gelöscht werden?',
		'Do you really want to delete this interdisciplinary subject?',
	],
	'no_students_crosssub' => [
		'Es sind keine Kursteilnehmer/innen zu diesem Thema zugeteilt.',
		'No students are assigend to this interdisciplinary subject.',
	],
	'use_available_crosssub' => [
		'Ein Thema aus einer Vorlage erstellen:',
		'Use draft for creating new interdisciplinary subject:',
	],
	'save_crosssub' => [
		'Thema aktualisieren',
		'Save changes',
	],
	'add_content_to_crosssub' => [
		'Das Thema ist noch nicht befüllt.',
		'The interdisciplinary subject is still empty.',
	],
	'add_descriptors_to_crosssub' => [
		'Kompetenzen mit Thema verknüpfen',
		'Add descriptor to interdisciplinary subject',
	],
	'manage_crosssubs' => [
		'Zurück zur Übersicht',
		'Back to overview',
	],
	'show_course_crosssubs' => [
		'Kurs-Themen ansehen',
		'Show used interdisciplinary subjects',
	],
	'existing_crosssub' => [
		'Vorhandene Themen in diesem Kurs',
		'existing cross subjects in this course',
	],
	'create_new_crosssub' => [
		'Neues Thema erstellen',
		'Create new interdisciplinary subject',
	],
	'share_crosssub_for_further_use' => [
		'Geben Sie das Thema an Kursteilnehmer/innen frei, um volle Funktionalität zu erhalten.',
		'Share the interdisciplinary subject with students.',
	],
	'available_crosssubjects' => [
		'Vorhandene Kursthemen',
		'Available Cross Subjects',
	],
	'crosssubject_drafts' => [
		'Themenvorlagen',
		'Interdisciplinary Subject Drafts',
	],
	'de:Freigegebene Kursthemen' => [
		null,
		'Published Cross Subjects',
	],
	'de:Freigabe bearbeiten' => [
		null,
		'Change Sharing',
	],
	'de:Kopie als Vorlage speichern' => [
		null,
		'Create Copy as Draft',
	],
	'de:Vorlage verwenden' => [
		'',
		'Use Draft',
	],


	// === Associations ===
	'competence_associations' => [
		'Verknüpfungen',
		'Associations',
	],
	'competence_associations_explaination' => [
		'Das Lernmaterial {$a} ist mit den folgenden Kompetenzen verknüpft:',
		'The material {$a} is associated wih the following standards:',
	],


	// === Weeky schedule ===
	'weekly_schedule' => [
		'Wochenplan',
		'Weekly schedule',
	],
	'weekly_schedule_added' => [
		'Die Aufgabe wurde in den Planungsspeicher im Wochenplan hinzugefügt.',
		'Example added to the weekly schedule',
	],
	'weekly_schedule_already_exists' => [
		'Die Aufgabe ist bereits im Planungsspeicher im Wochenplan.',
		'Example is already in the weekly schedule',
	],
	'select_student_weekly_schedule' => [
		'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Wochenplan Sie sehen möchten.',
		'Please select a student to view his/her weekly schedule.',
	],
	'example_pool' => [
		'Planungsspeicher',
		'Example pool',
	],
	'example_trash' => [
		'Papierkorb',
		'Trash bin',
	],
	'choosecourse' => [
		'Kurs auswählen: ',
		'Select course: ',
	],
	'weekly_schedule_added_all' => [
		'Die Aufgabe wurde bei allen Kursteilnehmer/innen auf den Planungsspeicher im Wochenplan gelegt.',
		'Example added to the weekly schedule of all students.',
	],
	'weekly_schedule_already_existing_for_one' => [
		'Die Aufgabe ist bei mindestens einem/r Schüler/in bereits im Planungsspeicher im Wochenplan.',
		'Example has already been added to at least one student\'s weekly schedule.',
	],
	'weekly_schedule_link_to_grid' => [
		'Um den Planungsspeicher zu befüllen in den Kompetenzraster wechseln',
		'For adding examples to the schedule, please use the overview',
	],
	'pre_planning_storage' => [
		'Vorplanungsspeicher',
		'Pre-planning storage',
	],
	'pre_planning_storage_added' => [
		'Lernmaterial wurde zum Vorplanungsspeicher hinzugefügt.',
		'Example added to the pre-planning storage.',
	],
	'pre_planning_storage_already_contains' => [
		'Lernmateriel bereits im Vorplanungsspeicher enthalten.',
		'Example is already in pre-planning storage.',
	],
	'save_pre_planning_selection' => [
		'Ausgewählte Lernmaterialien auf den Wochenplan der ausgewählten Schüler/innen legen',
		'Add selected examples to weekly schedule of selected students',
	],
	'empty_pre_planning_storage' => [
		'Vorplanungsspeicher leeren',
		'Empty pre-planning storage',
	],
	'noschedules_pre_planning_storage' => [
		'Der Vorplanungsspeicher ist leer. Bitte legen Sie über die Kompetenzraster neue Lernmaterialien in den Vorplanungsspeicher.',
		'Pre-planning storage has been emptied, use the competence grid to put new examples in the pre-planning storage.',
	],
	'empty_trash' => [
		'Papierkorb leeren',
		'Empty trash bin',
	],
	'empty_pre_planning_confirm' => [
		'Auch Beispiele, die ein anderer Lehrer zum Vorplanungsspeicher hinzugefügt hat, werden entfernt. Sind Sie sicher?',
		'Examples added from all teachers are deleted, are you sure you want to do this?',
	],
	'to_weekly_schedule' => [
		'Zum Wochenplan',
		'To weekly schedule',
	],
	'blocking_event' => [
		'Sperrelement erstellen',
		'Create blocking event',
	],
	'blocking_event_title' => [
		'Titel',
		'title',
	],
	'blocking_event_create' => [
		'Zum Vorplanungsspeicher hinzufügen',
		'Add to pre-planning storage',
	],
	'weekly_schedule_disabled' => [
		'Lernmaterial ist versteckt und kann nicht auf Wochenplan gelegt werden.',
		'Hidden example can not be added to weekly schedule',
	],
	'pre_planning_storage_disabled' => [
		'Lernmaterial ist versteckt und kann nicht in den Vorplanungsspeicher gelegt werden.',
		'Hidden example can not be added to pre-planning storage.',
	],
	'add_example_for_all_students_to_schedule' => [
		'Achtung: Sie sind dabei Lernmaterialien für alle Schüler/innen auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.',
		'Attention: Here you can add examples to the schedules of all students. This requires extra confirmation.',
	],
	'add_example_for_all_students_to_schedule_confirmation' => [
		'Sind Sie sicher, dass Sie die Lernmaterialien für alle Schüler/innen auf den Wochenplan legen möchten?',
		'You are about to add the examples to the schedules of all students, do you want to continue?',
	],
    'participating_student' => [
        'Kursteilnehmer/in',
        'student',
    ],   
    'n1.unit' => [
        '1. Einheit:',
        '1. unit',
    ],
    'n2.unit' => [
        '2. Einheit:',
        '2. unit',
    ],
    'n3.unit' => [
        '3. Einheit:',
        '3. unit',
    ],
    'n4.unit' => [
        '4. Einheit:',
        '4. unit',
    ],
    'n5.unit' => [
        '5. Einheit:',
        '5. unit',
    ],
    'n6.unit' => [
        '6. Einheit:',
        '6. unit',
    ],
    'n7.unit' => [
        '7. Einheit:',
        '7. unit',
    ],
    'n8.unit' => [
        '8. Einheit:',
        '8. unit',
    ],
    'n9.unit' => [
        '9. Einheit:',
        '9. unit',
    ],
    'n10.unit' => [
        '10. Einheit:',
        '10. unit',
    ],

	// === Notifications ===
	'notification_submission_subject' => [
		'{$a->site}: {$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht',
		'{$a->site}: {$a->student} submitted a solution for {$a->example}',
	],
	'notification_submission_body' => [
		'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href="{$viewurl}">{$a->example}</a> </br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
		'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href="{$viewurl}">{$a->example}</a> </br></br> This message has been generated form moodle site {$a->site}.',
	],
	'notification_submission_context' => [
		'Abgabe',
		'Submission',
	],
	'notification_grading_subject' => [
		'{$a->site}: Neue Beurteilungen im Kurs {$a->course}',
		'{$a->site}: New grading in course {$a->course}',
	],
	'notification_grading_body' => [
		'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
		'Dear {$a->receiver}, </br></br>You have got new gradings in {$a->course} from {$a->teacher}.</br></br> This message has been generated form moodle site {$a->site}.',
	],
	'notification_grading_context' => [
		'Beurteilung',
		'Grading',
	],
	'notification_self_assessment_subject' => [
		'{$a->site}: Neue Selbsteinschätzung im Kurs {$a->course}',
		'{$a->site}: New self assessments in {$a->course}',
	],
	'notification_self_assessment_body' => [
		'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
		'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} has new self assessments in {$a->course}.</br></br> This message has been generated form moodle site {$a->site}.',
	],
	'notification_self_assessment_context' => [
		'Selbsteinschätzung',
		'self assessment',
	],
	'notification_example_comment_subject' => [
		'{$a->site}: Neuer Kommentar bei Aufgabe {$a->example}',
		'{$a->site}: New comment for example {$a->example}',
	],
	'notification_example_comment_body' => [
		'Lieber/Liebe {$a->receiver}, </br></br> {$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
		'Dear {$a->receiver}, </br></br>{$a->teacher} commented in {$a->course} the example {$a->example}.</br></br> This message has been generated form moodle site {$a->site}.',
	],
	'notification_example_comment_context' => [
		'Kommentar',
		'Comment',
	],
	'notification_weekly_schedule_subject' => [
		'{$a->site}: Neue Aufgabe am Wochenplan',
		'{$a->site}: New example on the schedule',
	],
	'notification_weekly_schedule_body' => [
		'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
		'Dear {$a->receiver}, </br></br>{$a->teacher} added an example in {$a->course} to your weekly schedule.</br></br> This message has been generated form moodle site {$a->site}.',
	],
	'notification_weekly_schedule_context' => [
		'Wochenplan',
		'Weekly schedule',
	],
	'inwork' => [
		'{$a->inWork}/{$a->total} Materialien in Arbeit',
		'{$a->inWork}/{$a->total} in work',
	],
	'block_exacomp_notifications_head' => [
		'Mitteilungen und Benachrichtigungen',
		'Notifications and Messages',
	],
	'block_exacomp_notifications_body' => [
		'Bei Aktionen wie einer Lernmaterialien-Einreichung oder einer Beurteilung werden Nachrichten an die zuständigen Benutzer gesendet.',
		'Users will get notified after relevant actions.',
	],


	// === Logging ===
	'block_exacomp_logging_head' => [
		'Logging',
		'Logging',
	],
	'block_exacomp_logging_body' => [
		'Relevante Aktionen werden geloggt.',
		'Relevant actions will get logged.',
	],
	'eventscompetenceassigned' => [
		'Kompetenz zugeteilt',
		'Competence assigned',
	],
	'eventsexamplesubmitted' => [
		'Aufgabe abgegeben',
		'Example submitted',
	],
	'eventsexamplegraded' => [
		'Aufgabe beurteilt',
		'Example graded',
	],
	'eventsexamplecommented' => [
		'Aufgabe kommentiert',
		'Example commented',
	],
	'eventsexampleadded' => [
		'Aufgabe zu Wochenplan hinzugefügt',
		'Example added to weekly schedule',
	],
	'eventsimportcompleted' => [
		'Import durchgeführt',
		'Import completed',
	],
	'eventscrosssubjectadded' => [
		'Thema freigegeben',
		'Interdisciplinary subject added',
	],

	// === Message ===
	'messagetocourse' => [
		'Nachricht an alle Kursteilnehmer/innen senden',
		'Send message to all students',
	],
	'messageprovider:submission' => [
		'Nachricht bei neuer Schülerabgabe',
		'Notify teacher that a student has submitted an item',
	],
	'messageprovider:grading' => [
		'Nachricht an bei neuer Note',
		'Notify Student that a teacher graded competencies',
	],
	'messageprovider:self_assessment' => [
		'Nachricht bei neuer Selbstbewertung',
		'Student assessed some own competencies',
	],
	'messageprovider:weekly_schedule' => [
		'Lehrer/in fügt ein Beispiel in den Wochenplan ein',
		'Teacher adds new example to weekly schedule',
	],
	'messageprovider:comment' => [
		'Lehrer/in kommentiert ein Beispiel',
		'Teacher comments an example',
	],


	'description_example' => [
		'Beschreibung / Schulbuchverweis',
		'Description',
	],
	'submit_example' => [
		'Abgeben',
		'Submit',
	],
	// === Webservice Status ===
	'enable_rest' => [
		'REST Protokoll nicht aktiviert',
		'REST Protocol not enabled',
	],
	'access_roles' => [
		'Benutzerrollen mit Zugriff auf Webservices',
		'Roles with webservice access',
	],
	'no_permission' => [
		'Berechtigung wurde nicht erteilt',
		'Permissions not set',
	],
	'description_createtoken' => [
		'Der Benutzerrolle "Authentifizierte/r Nutzer/in" zusätzliche Rechte erteilen: Website-Administration/Nutzer_innen/Rechte ändern/Rollen verwalten
4.1 Authentifizierte/r Nutzer/in wählen
4.2 Bearbeiten auswählen
4.3 Nach "createtoken" filtern
4.4 Moodle/webservice:createtoken erlauben',
		'Grant additional permission to the role "authenticated user" at: Site administration/Users/Permissions/Define roles
4.1 Select Authenticated User
4.2 Click on "Edit"
4.3 Filter for createtoken
4.4 Allow moodle/webservice:createtoken',
	],
	'exacomp_not_found' => [
		'Exacompservice nicht gefunden',
		'Exacompservice not found',
	],
	'exaport_not_found' => [
		'Exaportservice nicht gefunden',
		'Exaportservice not found',
	],
	'no_external_trainer' => [
		'Keine externen Trainer zugeteilt',
		'No external trainers assigned',
	],
	'periodselect' => [
		'Auswahl des Eingabezeitraums',
		'Select Period',
	],

	'teacher' => [
		'Lehrer',
		'Teacher',
	],
	'student' => [
		'Schüler/in',
		'Student',
	],
	'timeline_available' => [
		'Verfügbare',
		'Available',
	],
	// === Group Reports ===
    'result' => [
        'Ergebnis',
        'result',
    ],
    'evaluationdate'=> [
        'Bewertungsdatum',
        'evaluation Date',
    ],
    'output_current_assessments' => [ 
        'Ausgabe der jeweiligen Bewertungen',
        'output of current assessments',
    ],
    'student_assessment' => [
        'Schülerbewertung',
        'students\' assessment',
    ],
    'teacher_assessment' => [
        'Lehrerbewertung',
        'teachers\' assessment',
    ],
    'exa_evaluation' => [
        'Lernmaterial Bewertung',
        'learning material',
    ],
    'difficulty_group_report' => [
        'Niveau',
        'difficulty level',
    ],'no_entries_found' => [
        'Keine Einträge gefunden',
        'no entries found',
    ],'assessment_date' => [
        'Bewertungsdatum',
        'assessment date',
    ],'number_of_found_students' => [
        'Anzahl gefundener Schüler',
        'number of found students',
    ],'display_settings' => [
        'Anzeigeoptionen',
        'display settings',
    ],'create_report' => [
        'Bericht erstellen',
        'generate report',
    ],'students_competences' => [
        'Schüler Kompetenzen',
        'students\' competences',
    ],'number_of_students' => [
        'Schüler Anzahl',
        'number of students',
    ],'no_specification' => [
        'ohne Angabe',
        'not specified',
    ],'period' => [
        'Zeitintervall',
        'time interval',
    ],'from' => [
        'von',
        'from',
    ],'to' => [
        'bis',
        'to',
    ],'report_type' => [
        'Report Typ',
        'type of report',
    ],'report_subject' => [
        'Bildungsstandard',
        'educational standard',
    ],'report_learniningmaterial' => [
        'Lernmaterial',
        'learning material',
    ],'report_competencefield' => [
        'Kompetenzbereich',
        'competence field',
    ], 'all_students' => [
        'Alle Schüler',
        'all students',
    ]
];
