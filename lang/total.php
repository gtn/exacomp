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

return  [
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
	'tab_competence_details' => [
		'Moodle Aktivitäten',
		'Detailed competence-view',
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
		'Cross-Subjects',
	],
	'tab_cross_subjects_overview' => [
		'Übersicht',
		'Overview',
	],
	'tab_cross_subjects_course' => [
		'Kursthemen',
		'Course Cross-Subjects',
	],
	'tab_weekly_schedule' => [
		'Wochenplan',
		'Weekly Schedule',
	],
	'assign_descriptor_to_crosssubject' => [
		'Die Teilkompetenz "{$a}" den folgenden Themen zuordnen:',
		'Assign the competence "{$a}" to the following Cross-Subjects:',
	],
	'assign_descriptor_no_crosssubjects_available' => [
		'Es sind keine Themen vorhanden, legen Sie welche an.',
		'No Cross-Subjects are available.',
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
		'(Optional: Wenn Sie nicht mit Moodle-Aktivitäten arbeiten möchten, dann entfernen Sie das Häkchen "Ich möchte mit Moodle-Aktivitäten arbeiten" im Tab "Konfiguration".)',
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
	'settings_enableteacherimport' => [
		'Schulspezifische Standards verwenden',
		'Use school specific standards',
	],
	'settings_enableteacherimport_description' => [
		'Anhaken um LehrerInnen/KurstrainerInnen zu erlauben, eigene, schulspezifische Standards zu importieren',
		'Check to enable school specific standard import for trainers',
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
		'Global grading level',
	],
	'settings_admin_scheme_description' => [
		'Beurteilungen können auf unterschiedlichem Niveau erfolgen.',
		'Grading can be done on different levels.',
	],
	'settings_admin_scheme_none' => [
		'keine Niveaus',
		'no global levels',
	],
	'settings_additional_grading' => [
		'Angepasste Bewertung',
		'Adapted grading',
	],
	'settings_additional_grading_description' => [
		'Bewertung für Teilkompetenzen und Lernmaterialien global auf "nicht erreicht(0)" - "vollständig erreicht(3)" beschränken',
		'Grading limited from "not gained(0)" - "completely gained(3)"',
	],
	'settings_usetimeline' => [
		'Timeline im Profil verwenden',
		'Use Timeline in profile',
	],
	'settings_usetimeline_description' => [
		'Zeitlichen Ablauf des Kompetenzerwerbes im Profil anzeigen',
		'Chronological sequence of gained outcomes',
	],
	'timeline_teacher' => [
		'L',				
		'T',
	],
	'timeline_student' => [
			'S',
			'S',
	],
	'timeline_total' => [
			'Verfügbare',
			'Total',
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
	'usedetailpage' => [
		'Detaillierte Kompetenzansicht verwenden',
		'Use detailed overview of competencies',
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
	'useniveautitleinprofile' => [
		'Im Kompetenzprofil den Lernfortschritt als Titel verwenden',
		'Use level title in competence profile',
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
	'sorting' => [
		'Sortierung wählen: ',
		'select sorting: ',
	],
	'subject' => [
		'Kompetenzbereiche',
		'subjects',
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
		'All levels',
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
	'override_notice' => [
		'Dieser Eintrag wurde von jemand anderem bearbeitet. Wirklich ändern?',
		'This entry was editied by someone else before. Continue?',
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
	'usersubmitted' => [
		' hat folgende Moodle-Aktivitäten abgegeben:',
		' has submitted the following Moodle activities:',
	],
	'usersubmittedquiz' => [
		' hat folgende Tests durchgeführt:',
		' has done the following quizzes:',
	],
	'usernosubmission' => [
		' hat keine Moodle-Aufgaben zu dieser Lernliste abgegeben und keinen Test durchgeführt.',
		' has not yet submitted any Moodle activities or quizzes associated with this outcome',
	],
	'usernosubmission_topic' => [
		' hat keine Moodle-Aufgaben zu dieser Teilkompetenz abgegeben und keinen Test durchgeführt.',
		' has not yet submitted any Moodle activities or quizzes associated with this topic',
	],
	'grading' => [
		' Bewertung: ',
		' Grading: ',
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
		'Diese/r Kursteilnehmer/in hat folgende ePortfolio-Artefakte zu diesem Deskriptor eingereicht: ',
		'This participant has submitted the following ePortfolio artifacts: ',
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
		'Level',
	],
	'competence_grid_niveau' => [
		'Niveau',
		'Level',
	],
	'descriptor' => [
		'Kompetenz',
		'Competency',
	],
	'groupsize' => [
		'Gruppengröße: ',
		'Size of group: ',
	],
	'assigndone' => [
		'Aufgabe erledigt: ',
		'task done: ',
	],
	'assignmyself' => [
		'selbst',
		'by myself',
	],
	'assignteacher' => [
		'TrainerIn',
		'trainer',
	],
	'assignfrom' => [
		'von',
		'from',
	],
	'assignuntil' => [
		'bis',
		'until',
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
				gekennzeichnet werden. Keinenfalls müssen die Schülerinnen und
				Schüler alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Schülerinnen
				und Schüler müssen dann keine zugehörigen Lernmaterialien
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
	'column_setting' => [
		'Spalten aus/einblenden',
		'hide/display columns',
	],
	'niveau_filter' => [
		'Niveaus filtern',
		'Filter levels',
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
		'Bitte ordnen Sie den erstellen Moodle-Aktivitäten Kompetenzen zu.',
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
	'all_niveaus' => [
		'Alle Niveaustufen',
		'All levels',
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
		'Self assessment',
	],
	'teacherevaluation' => [
		'Einschätzung des Beurteilenden',
		'Trainer assessment',
	],
	'competencegrid_nodata' => [
		'Sollte der Kompetenzraster leer sein, wurden für die Deskriptoren des ausgewählten Gegenstands keine Niveaus in den Daten definiert',
		'In case the competency grid is empty the outcomes for the chosen subject were not assigned to a level in the datafile. This can be fixed by associating outcomes with levels at www.edustandards.org and re-importing the xml-file.',
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
	'report_competence' => [
		'Kompetenzen',
		'Competencies',
	],
	'report_detailcompetence' => [
		'Teilkompetenzen',
		'Child Competencies',
	],
	'report_examples' => [
		'Lernmaterialien',
		'Examples',
	],


	// === Detail view ===
	'detail_description' => [
		'Hier kann mit Hilfe von Aktivitäten eine Kompetenz beurteilt werden.',
		'Use Moodle activities to evaluate competencies.',
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
	'course' => [
		'Kurs',
		'Course',
	],
	'gained' => [
		'Erreicht',
		'gained',
	],
	'total' => [
		'Gesamt',
		'total',
	],
	'allcourses' => [
		'Alle Kurse',
		'all courses',
	],
	'pendingcomp' => [
		'Ausstehende Kompetenzen',
		'pending competencies',
	],
	'teachercomp' => [
		'Erreichte Kompetenzen',
		'gained competencies',
	],
	'studentcomp' => [
		'Laut Selbsteinschätzung erreichte Kompetenzen',
		'self evaluated competencies',
	],
	'radargrapherror' => [
		'Der Radargraph kann nur bei 3-7 Achsen dargestellt werden',
		'Radargraph can only be displayed with 3-7 axis',
	],
	'nodata' => [
		'Es sind keine Daten vorhanden.',
		'There is no data do display',
	],
	'item_no_comps' => [
		'Zu folgenden Artefakten wurden noch keine Kompetenzen zugeordnet:',
		'There are no outcomes assigned to the following items: ',
	],
	'select_student' => [
		'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Kompetenzprofil Sie sehen möchten.',
		'Please select a student first',
	],
	'my_comps' => [
		'Meine Kompetenzen',
		'My Competencies',
	],
	'my_items' => [
		'Meine Artefakte',
		'My artifacts',
	],
	'my_badges' => [
		'Meine Auszeichnungen',
		'My Badges',
	],
	'my_periods' => [
		'Meine Feedbacks',
		'My assessments',
	],
	'item_type' => [
		'Typ',
		'Type',
	],
	'item_link' => [
		'Link',
		'Link',
	],
	'item_file' => [
		'Datei',
		'File',
	],
	'item_note' => [
		'Notiz',
		'Note',
	],
	'item_title' => [
		'Titel',
		'Title',
	],
	'item_url' => [
		'Url',
		'URL',
	],
	'period_reviewer' => [
		'Bewerter',
		'Reviewer',
	],
	'period_feedback' => [
		'Verbales Feedback',
		'Feedback',
	],
	'January' => [
		'Jänner',
		'January',
	],
	'February' => [
		'Februar',
		'February',
	],
	'March' => [
		'März',
		'March',
	],
	'April' => [
		'April',
		'April',
	],
	'May' => [
		'Mai',
		'May',
	],
	'June' => [
		'Juni',
		'June',
	],
	'July' => [
		'Juli',
		'July',
	],
	'August' => [
		'August',
		'August',
	],
	'September' => [
		'September',
		'September',
	],
	'October' => [
		'Oktober',
		'October',
	],
	'November' => [
		'November',
		'November',
	],
	'December' => [
		'Dezember',
		'December',
	],
	'oB' => [
		'ohne Bewertung',
		'without evaluation',
	],
	'nE' => [
		'nicht erreicht',
		'not gained',
	],


	// === Competence Profile Settings ===
	'profile_settings_showonlyreached' => [
		'Ich möchte in meinem Kompetenzprofil nur bereits erreichte Kompetenzen sehen.',
		'I only want to see already gained outcomes in my competence profile',
	],
	'profile_settings_choose_courses' => [
		'In Exabis Kompetenzraster beurteilen TrainerInnen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgewählt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.',
		'Using Exabis Competence Grid trainers assess your competencies in various subjects. You can select which course to include in the competence profile.',
	],
	'profile_settings_useexaport' => [
		'Ich möchte Kompetenzen, die in Exabis ePortfolio verwendet werden in meinem Profil sehen.',
		'I want to see competencies used in Exabis ePortfolio within my profile.',
	],
	'profile_settings_choose_items' => [
		'Exabis ePortfolio dokumentiert deinen Kompetenzerwerb außerhalb von LehrerInnen vorgegebenen Grenzen. Du kannst auswählen, welche Einträge im Kompetenzprofil aufscheinen sollen.',
		'Exabis ePortfolio is used to document your competencies on your individual learning path. You can select which artifacts to include in the competence profile.',
	],
	'profile_settings_useexastud' => [
		'Ich möchte Beurteilungen aus Exabis Student Review in meinem Profil sehen.',
		'I want to see evaluations from Exabis Student Review.',
	],
	'profile_settings_no_item' => [
		'Kein Exabis ePortfolio Artefakt vorhanden, somit kann nichts dargestellt werden.',
		'No Exabis ePortfolio item available, so there is nothing to display.',
	],
	'profile_settings_no_period' => [
		'Keine Beurteilung in einer Periode in Exabis Student Review vorhanden.',
		'No review in a period in Exabis Student Review available.',
	],
	'profile_settings_usebadges' => [
		'Ich möchte im Kompetenzprofil auch meine Auszeichnungen sehen.',
		'I want to see badges in my competence profile.',
	],
	'profile_settings_onlygainedbadges' => [
		'Ich möchte nur Auszeichnungen sehen, die mir bereits verliehen wurden.',
		'I don\'t want to see pending badges.',
	],
	'profile_settings_badges_lineup' => [
		'Einstellungen zu Auszeichnungen',
		'Badges settings',
	],
	'profile_settings_showallcomps' => [
		'Alle meine Kompetenzen',
		'all my competencies',
	],
	'specificcontent' => [
		'Schulbezogene Themenbereiche',
		'site-specific topics',
	],
	'specificsubject' => [
		'Schulbezogene Gegenstands-/Kompetenzbereiche',
		'site-specific subjects',
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
	'empty_draft' => [
		'Neues Thema',
		'New Cross-Subject',
	],
	'empty_draft_description' => [
		'Erstelle dein eigenes Thema - ändere die Beschreibung hier',
		'Create your own Cross-Subject - insert new description',
	],
	'add_drafts_to_course' => [
		'Ausgewählte Vorlagen im Kurs verwenden',
		'Add drafts to course',
	],
	'crosssubject' => [
		'Thema',
		'Cross-Subject',
	],
	'student_name' => [
		'Kursteilnehmer/in',
		'Participant',
	],
	'help_crosssubject' => [
		'Die Zusammenstellung des Themas erfolgt für die ganze Schule über den Reiter Kompetenzraster. Sie können hier kursspezifisch Kompetenzen, Teilkompetenzen und Lernmaterial ausblenden. Lernmaterial kann hier ergänzt werden. Dieses wird automatisch in die Lernwegeliste integriert.',
		'The compilation of a subject is done for the whole Moodle installation (school) using the tab learning path. Here you can selectively deactivate course-specific competences, sub-competences and materials. Individual learning material can also be added. This is then automatically added to the learning paths.',
	],
	'description' => [
		'Beschreibung',
		'Description',
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
		'Save Cross-Subject as draft',
	],
	'comps_and_material' => [
		'Kompetenzen und Lernmaterial',
		'outcomes and exercises',
	],
	'no_crosssubjs' => [
		'In diesem Kurs gibt es noch kein Thema.',
		'No Cross-Subjects available.',
	],
	'delete_drafts' => [
		'Ausgewählte Vorlagen löschen',
		'Delete selected drafts',
	],
	'share_crosssub' => [
		'Thema für Kursteilnehmer/innen freigeben',
		'Share Cross-Subject with participants',
	],
	'share_crosssub_with_students' => [
		'Das Thema "{$a}" für folgende Kursteilnehmer/innen freigeben: ',
		'Share Cross-Subject "{$a}" with the following participants: ',
	],
	'share_crosssub_with_all' => [
		'Das Thema "{$a}" für <b>alle</b> Kursteilnehmer/innen freigeben: ',
		'Share Cross-Subject "{$a}" with all participants: ',
	],
	'new_crosssub' => [
		'Eigenes Thema erstellen',
		'Create new Cross-Subject',
	],
	'add_crosssub' => [
		'Thema erstellen',
		'Create Cross-Subject',
	],
	'nocrosssubsub' => [
		'Allgemeine Themen',
		'General Cross-Subjects',
	],
	'delete_crosssub' => [
		'Thema löschen',
		'Delete Cross-Subject',
	],
	'confirm_delete' => [
		'Soll dieses Thema wirklich gelöscht werden?',
		'Do you really want to delete this Cross-Subject?',
	],
	'no_students_crosssub' => [
		'Es sind keine Kursteilnehmer/innen zu diesem Thema zugeteilt.',
		'No students are assigend to this Cross-Subject.',
	],
	'use_available_crosssub' => [
		'Ein Thema aus einer Vorlage erstellen:',
		'Use draft for creating new Cross-Subject:',
	],
	'save_crosssub' => [
		'Thema aktualisieren',
		'Save changes',
	],
	'add_content_to_crosssub' => [
		'Das Thema ist noch nicht befüllt.',
		'The Cross-Subject is still empty.',
	],
	'add_descriptors_to_crosssub' => [
		'Kompetenzen mit Thema verknüpfen',
		'Add descriptor to Cross-Subject',
	],
	'manage_crosssubs' => [
		'Zurück zur Übersicht',
		'Back to overview',
	],
	'show_course_crosssubs' => [
		'Kurs-Themen ansehen',
		'Show used Cross-Subjects',
	],
	'existing_crosssub' => [
		'Vorhandene Themen in diesem Kurs',
		'existing cross subjects in this course',
	],
	'create_new_crosssub' => [
		'Neues Thema erstellen',
		'Create new Cross-Subject',
	],
	'share_crosssub_for_further_use' => [
		'Geben Sie das Thema an Kursteilnehmer/innen frei, um volle Funktionalität zu erhalten.',
		'Share the Cross-Subject with students.',
	],
	'available_crosssubjects' => [
		'Vorhandene Kursthemen',
		'Available Cross Subjects',
	],
	'crosssubject_drafts' => [
		'Themenvorlagen',
		'Cross-Subject Drafts',
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
		'Die Aufgabe ist bei mindestens einem Schüler bereits im Planungsspeicher im Wochenplan.',
		'Example has already been added to at least one student\'s weekly schedule.',
	],
	'weekly_schedule_link_to_grid' => [
		'Um den Wochenplan zu befüllen in den Kompetenzraster wechseln',
		'For adding examples to the schedule, please use the overview',
	],
	'pre_planning_storage' => [
		'Vorplanungsspeicher',
		'pre-planning storage',
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


	// === Notifications ===
	'notification_submission_subject' => [
		'{$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht.',
		'{$a->student} submitted a solution for {$a->example}.',
	],
	'notification_submission_body' => [
		'{$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href="{$viewurl}">{$a->example}</a>',
		'{$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href="{$viewurl}">{$a->example}</a>',
	],
	'notification_submission_context' => [
		'Abgabe',
		'Submission',
	],
	'notification_grading_subject' => [
		'Neue Beurteilungen im Kurs {$a->course}',
		'New grading in course {$a->course}',
	],
	'notification_grading_body' => [
		'Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.',
		'You have got new gradings in {$a->course} from {$a->teacher}.',
	],
	'notification_grading_context' => [
		'Beurteilung',
		'Grading',
	],
	'notification_self_assessment_subject' => [
		'Neue Selbsteinschätzung im Kurs {$a->course}',
		'New self assessments in {$a->course}',
	],
	'notification_self_assessment_body' => [
		'{$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.',
		'{$a->student} has new self assessments in {$a->course}.',
	],
	'notification_self_assessment_context' => [
		'Selbsteinschätzung',
		'Self assessment',
	],
	'notification_example_comment_subject' => [
		'Neuer Kommentar bei Aufgabe {$a->example}',
		'New comment for example {$a->example}',
	],
	'notification_example_comment_body' => [
		'{$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.',
		'{$a->teacher} commented in {$a->course} the example {$a->example}.',
	],
	'notification_example_comment_context' => [
		'Kommentar',
		'Comment',
	],
	'notification_weekly_schedule_subject' => [
		'Neue Aufgabe am Wochenplan',
		'New example on the schedule',
	],
	'notification_weekly_schedule_body' => [
		'{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.',
		'{$a->teacher} added an example in {$a->course} to your weekly schedule.',
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
		'Cross-Subject added',
	],


	// === Statistics ===
	'process' => [
		'Bearbeitungsstand',
		'State of process',
	],
	'niveauclass' => [
		'Niveaueinstufung',
		'Level classification',
	],


	// === Message ===
	'messagetocourse' => [
		'Nachricht an alle Kursteilnehmer/innen senden',
		'Nachricht an alle Kursteilnehmer/innen senden',
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
	'requirements' => [
		'Was du schon können solltest: ',
		'Was du schon können solltest: ',
	],
	'forwhat' => [
		'Wofür du das brauchst: ',
		'Wofür du das brauchst: ',
	],
	'howtocheck' => [
		'Wie du dein Können prüfen kannst: ',
		'Wie du dein Können prüfen kannst: ',
	],
	'reached_topic' => [
		'Ich habe diese Kompetenz erreicht: ',
		'Ich habe diese Kompetenz erreicht: ',
	],
	'submit_example' => [
		'Abgeben',
		'Submit'
	],
];