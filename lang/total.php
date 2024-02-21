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
    'exacomp:editingteacher' => [
        'Editing teacher',
        'Editing teacher',
    ],
    'exacomp:getfullcompetencegridforprofile' => [
        'for WebService block_exacomp_get_fullcompetence_grid_for_profile',
        'for WebService block_exacomp_get_fullcompetence_grid_for_profile',
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
        'Vorauswahl der Kompetenzraster',
        'Competence grids pre-selection',
    ],
    'admin_config_pending' => [
        'Vorauswahl der Kompetenzen durch den Administrator notwendig',
        'Competence grids pre-selection needs to be performed by the Moodle administrator',
    ],
    'tab_admin_taxonomies' => [
        'Niveaustufen',
        'Difficulty levels',
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
        'Schulart / Bezüge zum Bildungsplan',
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
    'tab_teacher_settings_activitiestodescriptors' => [
        'Moodle-Aktivitäten verknüpfen',
        'Relate Moodle activities',
    ],
    'tab_teacher_settings_questiontodescriptors' => [
        'Test-Fragen verknüpfen',
        'Relate Quiz questions',
    ],
    'tab_teacher_settings_badges' => [
        'Auszeichnungen bearbeiten',
        'Edit badges',
    ],
    'tab_teacher_settings_new_subject' => [
        'Neues Kompetenzraster anlegen',
        'Create new subject',
    ],
    'tab_teacher_settings_taxonomies' => [
        'Niveaustufen',
        'Difficulty levels',
    ],
    'tab_teacher_settings_taxonomies_help' => [
        'Lernmaterialien als auch Kompetenzen können mit Niveaustufen versehen werden (üblicherweise im Kompetenzraster-Erfassungstool KOMET).</br>
Lernmaterialien und Kompetenzen können nach Niveaustufen gefiltert werden.</br>
Ein anderer Begriff für Niveaustufen ist Taxonomien - z.B. kann die Bloomsche Taxonomie für die Einstufung des Lernniveaus herangezogen werden (siehe <a href=\'https://de.wikipedia.org/wiki/Lernziel#Taxonomien\' target=\'_blank\'>https://de.wikipedia.org/wiki/Lernziel#Taxonomien</a>)
',
        'Both learning materials and competencies can be provided with difficulty levels (usually done in the KOMET competence grid recording tool).</br>
         Learning materials can be filtered according to difficulty levels.</br>
         Another term for difficulty levels is taxonomies - e.g. Bloom\'s taxonomy can be used to classify the learning level (see <a href=\'https://en.wikipedia.org/wiki/Bloom%27s_taxonomy\' target=\'_blank\'>https://en.wikipedia.org/wiki/Bloom%27s_taxonomy</a>)
 ',
    ],

    'tab_teacher_report_general' => [
        'Berichte',
        'General report',
    ],
    'tab_teacher_report_annex' => [
        'Berichte ',
        'Annex',
    ],
    'tab_teacher_report_annex_title' => [
        'Anlage zum Lernentwicklungsbericht',
        'Annex to the learning development report',
    ],
    'tab_teacher_report_profoundness' => [
        'Grund- und Erweiterungskompetenzen ',
        'Basic and extended competencies',
    ],
    'tab_teacher_report_profoundness_title' => [
        'Grund- und Erweiterungskompetenzen verwenden',
        'Basic and extended competencies',
    ],
    'create_html' => [
        'Bericht im HTML-Format generieren (Voransicht)',
        'generate HTML preview',
    ],
    'create_docx' => [
        'Bericht im docx-Format generieren',
        'generate docx',
    ],
    'create_pdf' => [
        'Bericht im pdf-Format generieren',
        'generate pdf',
    ],
    'create_html_report' => [
        'Bericht im HTML-Format generieren',
        'generate HTML preview',
    ],
    'create_docx_report' => [
        'Bericht im docx-Format generieren',
        'generate docx',
    ],
    'create_pdf_report' => [
        'Bericht im pdf-Format generieren',
        'generate pdf',
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
    'tab_competence_gridoverview' => [
        'Übersicht',
        'Overview',
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
        'Berichte',
        'Group Reports',
    ],
    'assign_descriptor_to_crosssubject' => [
        'Die Kompetenz "{$a}" den folgenden Themen zuordnen:',
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
        'Im zweiten Konfigurationsschritt müssen Kompetenzraster ausgewählt werden.',
        'In this configuration step you have to pre-select competence grids.',
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
        'Im ersten Konfigurationsschritt der Kurs-Kompetenzraster müssen einige generelle Einstellungen getroffen werden.',
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
    'enrol_users' => [
        'Schreiben sie Teilnehmer/innen ein, um Exacomp verwenden zu können.',
        'Enrol users to be able to use exacomp.',
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
        'Automatische Beurteilung durch Moodle-Aktivitäten',
        'Automatical grading through Moodle-activities',
    ],
    'settings_autotest_description' => [
        'Kompetenzen oder Aufgaben die mit Aktivitäten verbunden sind, gelten automatisch als erworben, wenn die in der Aktivität angegebenen Aktivitätsabschlusskriterien erfüllt sind.',
        'Competences or Assignments that are associated with activities are gained automatically if the completion requirements of the activity are met. ',
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
        'Connect to Moodle badges',
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
        'Amount of units',
    ],
    'settings_scheduleunits_description' => [
        'Anzahl der Einheiten im Wochenplan',
        'Amount of units in the schedule',
    ],
    'settings_schedulebegin' => [
        'Beginn der Einheiten',
        'Begin of schedule',
    ],
    'settings_schedulebegin_description' => [
        'Beginnzeitpunkt der ersten Einheit im Wochenplan. Format hh:mm',
        'Begin time for the first unit in the schedule. Format hh:mm',
    ],
    'settings_description_nurmoodleunddakora' => [
        '<b>Nur Moodle und Dakora App</b>',
        '<b>Only Moodle and Dakora App</b>',
    ],
    'settings_description_nurdakora' => [
        '<b>Nur Dakora App</b>',
        '<b>Only Dakora App</b>',
    ],
    'settings_description_nurdiggr' => [
        '<b>Nur Diggr+ und elove App</b>',
        '<b>Only Diggr+ and elove App</b>',
    ],
    'settings_description_nurdakoraplus' => [
        '<b>Nur DakoraPlus App</b>',
        '<b>Only DakoraPlus App</b>',
    ],
    'settings_admin_scheme' => [
        'Vordefinierte Konfiguration',
        'Predefined configuration',
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
    'settings_heading_general' => [
        'Allgemein',
        'General',
    ],
    'settings_heading_assessment' => [
        'Beurteilung',
        'Assessment',
    ],
    'settings_heading_visualisation' => [
        'Darstellung',
        'Visualisation',
    ],
    'settings_heading_technical' => [
        'Administratives',
        'Administrative',
    ],
    'settings_heading_apps' => [
        'Apps-Einstellungen',
        'Configuration for apps',
    ],
    'settings_new_app_login' => [
        'SSO-App-Login verwenden',
        'Use SSO-App-Login',
    ],
    'settings_dakora_teacher_link' => [
        'Klicken Sie, um die Dakoralehrer festzulegen',
        'Click to assign the dakorateachers',
    ],
    'settings_applogin_redirect_urls' => [
        'Applogin Urls',
        'Applogin Urls',
    ],
    'settings_applogin_redirect_urls_description' => [
        '',
        '',
    ],
    'settings_applogin_enabled' => [
        'App-Login aktivieren',
        'Enable App Login',
    ],
    'settings_applogin_enabled_description' => [
        'Erlaubt den Login von Exabis Apps (Diggr+, Dakora, Dakora+, elove)',
        'Allows the login from Exabis Apps (Diggr+, Dakora, Dakora+, elove)',
    ],
    'settings_setapp_enabled' => [
        'SET-App Funktionen aktivieren',
        'Enable SET-App Functions',
    ],
    'settings_setapp_enabled_description' => [
        'Anlegen von Userkonten über App erlauben.',
        'Allow creating users in the App.',
    ],
    'settings_sso_create_users' => [
        'SSO: Neue Benutzer erstellen',
        'SSO: Create new Users',
    ],
    'settings_sso_create_users_description' => [
        '',
        '',
    ],
    'settings_msteams_client_id' => [
        'Diggr+ MS Teams App Client Id',
        'Diggr+ MS Teams App Client Id',
    ],
    'settings_msteams_client_id_description' => [
        '',
        '',
    ],
    'settings_msteams_client_secret' => [
        'Diggr+ MS Teams App Client Secret',
        'Diggr+ MS Teams App Client Secret',
    ],
    'settings_msteams_client_secret_description' => [
        '',
        '',
    ],
    'dakora_teachers' => [
        'Dakoralehrer',
        'Dakorateachers',
    ],
    'settings_new_app_login_description' => [
        'Der neue App-Login erlaubt Benutzern sich mit allen aktivierten Moodle Login-Plugins einzuloggen. Diese Einstellung ist nicht mit dem Gamification Plugin kompatibel.',
        'The new App-Login allows users to login with all activated Moodle Login plugins. This setting is not compatible with the gamification plugin.',
    ],
    'settings_heading_performance' => [
        'Performance',
        'Performance',
    ],
    'settings_heading_performance_description' => [
        'Sollte sich die Kompetenzraster-Ansicht nur langsam aufbauen, können diese Einstellungen zur Lade-Optimierung verwendet werden.',
        'Try to change these parameters if some pages work very slow. Can be changed some visuality/usability',
    ],
    'settings_heading_scheme' => [
        'Generisches Bewertungsschema',
        'Generic assessment scheme',
    ],
    'settings_assessment_are_you_sure_to_change' => [
        'Wollen sie wirklich das Bewertungsschema ändern? Bestehende Bewertungen können verloren gehen oder ihre Aussagekraft verlieren',
        'Do you really want to change grading schema? Existing gradings can get lost or get wrong values',
    ],
    'settings_assessment_scheme_0' => [
        'Keines',
        'None',
    ],
    'settings_assessment_scheme_1' => [
        'Noten',
        'Grade',
    ],
    'settings_assessment_scheme_2' => [
        'Verbalisierung',
        'Verbose',
    ],
    'settings_assessment_scheme_3' => [
        'Punkte',
        'Points',
    ],
    'settings_assessment_scheme_4' => [
        'Ja/Nein',
        'Yes/No',
    ],
    'settings_assessment_diffLevel' => [
        'Niveau',
        'Global assessment level',
    ],
    'settings_assessment_SelfEval' => [
        'Selbsteinschätzung',
        'Student assessment',
    ],
    'settings_assessment_target_example' => [
        'Material',
        'Material',
    ],
    'settings_assessment_target_childcomp' => [
        'Teilkompetenz',
        'Child competence',
    ],
    'settings_assessment_target_comp' => [
        'Kompetenz',
        'Competence',
    ],
    'settings_assessment_target_topic' => [
        'Kompetenzbereich',
        'Topic',
    ],
    'settings_assessment_target_subject' => [
        'Fach',
        'Subject',
    ],
    'settings_assessment_target_theme' => [
        'Thema (fachübergreifend)',
        'Theme (interdisciplinary)',
    ],
    'settings_assessment_points_limit' => [
        'Höchste Punkteanzahl',
        'Highest value for Points',
    ],
    'settings_assessment_points_limit_description' => [
        'Bewertungsschema Punkte, die höchst mögliche Punkteanzahl die eingegeben werden kann.',
        'assessment scheme points, limit for input',
    ],
    'settings_assessment_points_negativ' => [
        'Negative Beurteilung Punkte',
        'Fail value for Points',
    ],
    'settings_assessment_points_negativ_description' => [
        'Untergrenze (negative Beurteilung) des Punkte-Werts im Beurteilungs-Schema',
        'assessment scheme point value, when the student fails the grading',
    ],
    'settings_assessment_grade_limit' => [
        'Höchste Note',
        'Highest value for grade',
    ],
    'settings_assessment_grade_limit_description' => [
        'Bewertungsschema Note, die höchst mögliche Note die eingegeben werden kann.',
        'assessment scheme grade, limit for input',
    ],
    'settings_assessment_grade_negativ' => [
        'Negative Beurteilung Noten',
        'Fail value for grade',
    ],
    'settings_assessment_grade_negativ_description' => [
        'Untergrenze (negative Beurteilung) des Noten-Werts im Beurteilungs-Schema',
        'assessment scheme grade value, when the student fails the grading',
    ],
    'settings_assessment_diffLevel_options' => [
        'Niveau Werte',
        'Difficulty Level Options',
    ],
    'settings_assessment_diffLevel_options_description' => [
        'Liste der möglichen Werte des Niveaus, z.B: G,M,E,Z',
        'list of difficultiy Levels, i.e. G,M,E,Z',
    ],
    'settings_assessment_diffLevel_options_default' => [
        'G,M,E,Z',
        'G,M,E,Z',
    ],
    'settings_assessment_verbose_options' => [
        'Erreichungsgrad',
        'verbose Options (EN)',
    ],
    'settings_assessment_verbose_options_description' => [
        'Liste der möglichen Werte der Verbalisierung, z.B: nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht',
        'list of verbose Options, i.e. not gained, partly gained, mostly gained, completely gained',
    ],
    'settings_assessment_verbose_options_default' => [
        'nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht',
        'not gained, partly gained, mostly gained, completely gained',
    ],
    'settings_assessment_verbose_options_short' => [
        'Verbalisierung Werte Abkürzung',
        'verbose Options (EN) short',
    ],
    'settings_assessment_verbose_options_short_description' => [
        'Abkürzung obiger verbalisierter Werte für die Auswertungen',
        'list of verbose Options, i.e. not gained, partly gained, mostly gained, completely gained',
    ],
    'settings_assessment_verbose_options_short_default' => [
        'ne, te, üe, ve',
        'ng, pg, mg, cg',
    ],
    'settings_schoolname' => [
        'Bezeichnung und Standort der Schule',
        'Name and address of school',
    ],
    'settings_schoolname_description' => [
        '',
        '',
    ],
    'settings_schoolname_default' => [
        'Bezeichnung und Standort der Schule',
        'Name and address of school',
    ],
    'settings_assessment_grade_verbose' => [
        'Noten Verbalisierung',
        'verbalized grades (EN)',
    ],
    'settings_assessment_grade_verbose_description' => [
        'Verbalisierte Werte der Noten, kommagetrennt. Die Anzahl muß mit dem Wert "Höchste Note" oben übereinstimmen. z.B: sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend',
        'Verbalized values of the grades, separated by commas. The number must match the "highest grade" value above. e.g .: very good, good, satisfactory, sufficient, deficient, insufficient',
    ],
    'settings_assessment_grade_verbose_default' => [
        'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend',
        'very good, good, satisfactory, sufficient, deficient, insufficient',
    ],
    'settings_assessment_grade_verbose_negative' => [
        'Negative Beurteilung Verbalisierung',
        'Fail verbalized grade (EN)',
    ],
    'settings_assessment_grade_verbose_negative_description' => [
        'Untergrenze (negative Beurteilung) der verbalisierten Beurteilung im Beurteilungs-Schema',
        'assessment scheme grade verbose value, when the student fails the grading',
    ],
    'use_grade_verbose_competenceprofile' => [
        'Noten Verbalisierung Kompetenzprofil',
        'grades verbose competence profile ',
    ],
    'use_grade_verbose_competenceprofile_descr' => [
        'Noten Verbalisierung im Kompetenzprofil verwenden',
        'use grades verbose in competence profile',
    ],
    'settings_sourceId' => [
        'Source ID',
        'Source ID',
    ],
    'settings_sourceId_description' => [
        'Automatisch generierte ID dieser Exacomp Installation. Diese kann nicht geändert werden',
        'Automatically generated ID of this Exacomp installation. This ID can not be changed',
    ],
    'settings_admin_preconfiguration_none' => [
        'Eigene Konfiguration',
        'No preconfiguration',
    ],
    'settings_default_de_value' => [
        'DE value: ',
        'DE value: ',
    ],
    'settings_assessment_SelfEval_verboses' => [
        'Werte für verbalisiertes Schüler/innen-Feedback',
        'Verboses for self evaluations',
    ],
    'settings_assessment_SelfEval_verboses_long_columntitle' => [
        'Lang',
        'Long',
    ],
    'settings_assessment_SelfEval_verboses_short_columntitle' => [
        'Kurz',
        'Short',
    ],
    'settings_assessment_SelfEval_verboses_edit' => [
        'Bearbeiten',
        'Edit verboses',
    ],
    'settings_assessment_SelfEval_verboses_validate_error_long' => [
        'Langformat: bis zu 4 Einträge, Trennzeichen Strichpunkt, max 20 Zeichen je Entrag (4 zum Kurzformat)',
        'Long titles: up to 4 entries, delimiter ";", maximum 20 characters per entry (4 for short form)',
    ],/*
    'settings_assessment_SelfEval_verboses_validate_error_short' => [
        'Short titles: up to 4 entries, delimiter ; maximum 3 characters per entry',
        'Short titles: up to 4 entries, delimiter ; maximum 3 characters per entry',
    ],*/
    'settings_addblock_to_newcourse' => [
        'Block zu neuen Kursen automatisch hinzufügen',
        'Add block to new courses',
    ],
    'settings_addblock_to_newcourse_description' => [
        'Der Block "Exabis Kompetenzraster" wird automatisch jedem neuen Kurs hinzugefügt. Die Position des Block hängt vom Moodle-Theme ab.',
        'The block "Exabis competence Grid" will be added to every new course automatically. Position of inserted block depends on selected Moodle theme',
    ],
    'settings_addblock_to_newcourse_option_no' => [
        'Nein',
        'No',
    ],
    'settings_addblock_to_newcourse_option_yes' => [
        'Ja',
        'Yes',
    ],
    'settings_addblock_to_newcourse_option_left' => [
        'to the Left region',
        'to the Left region',
    ],
    'settings_addblock_to_newcourse_option_right' => [
        'to the Right region',
        'to the Right region',
    ],
    'settings_disable_js_assign_competencies' => [
        'JS für Kompetenzraster-Übersicht deaktivieren.',
        'Disable JS in students selection in "Competence grid" page',
    ],
    'settings_disable_js_assign_competencies_description' => [
        'Bei langen Ladezeiten des Kompetenzrasters können zur Performance-Steigerung JS-Funktionen deaktiviert werden.',
        'If "Competence grid" has a long page generation time. This checkbox can solve this problem',
    ],
    'settings_disable_js_editactivities' => [
        'JS für die Zuteilung von Moodle-Aktivitäten für Teilnehmer/innen deaktivieren',
        'Disable JS in students selection in "Assign Moodle activities" page',
    ],
    'settings_disable_js_editactivities_description' => [
        'Aktivieren, falls sich die Seite "Moodle-Aktivitäten zuteilen"  zu langsam aufbaut.',
        'If "Assign Moodle activities" (in settings) has a long page generation time. This checkbox can solve this problem',
    ],
    'settings_example_autograding' => [
        'übergeordnete Materialien automatische Beurteilung',
        'automatic assessment of parent materials',
    ],
    'settings_example_autograding_description' => [
        'Wenn alle untergeordneten Aufgaben erledigt sind, soll das übergeordnete Material automatisch beurteilt werden.',
        'When all child examples have been graded, the parent material should be assessed automatically.',
    ],
    'settings_assessment_verbose_lowerisbetter' => [
        'Niedriger Wert ist besser',
        'Lower value is better',
    ],
    'settings_assessment_verbose_lowerisbetter_description' => [
        'Je niedriger der Wert der Beurteilung umso besser.',
        'The lower the Assessment, the better.',
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
        'Erstellen Sie Ihre eigenen Kompetenzraster auf <a target="_blank" href="https://comet.edustandards.org">wwww.edustandards.org</a>.',
        'Please create your competence grids at <a target="_blank" href="https://comet.edustandards.org">www.edustandards.org</a>.',
    ],
    'importwebservice' => [
        'Es besteht auch die Möglichkeit die Daten über eine <a href="{$a}">Server-URL</a> aktuell zu halten.',
        'It is possible to keep the data up to date via a <a href="{$a}">Server-URL</a>.',
    ],
    'import_max_execution_time' => [
        'Wichtig: die aktuellen Servereinstellungen beschränken den Import auf {$a} Sekunden. Falls der Import-Vorgang länger dauert, wird dieser abgebrochen, es werden keine Daten importiert. Am Ausgabegerät wird in diesem Fall eine serverseitige Fehlermeldung ausgegeben (z.B. "500 Internal Server Error").',
        'Important: The current Serversettings limit the Import to run up to {$a} seconds. If the import takes longer no data will be imported and the browser may display "500 Internal Server Error".',
    ],
    'importdone' => [
        'Die allgemeinen Kompetenzraster sind bereits importiert.',
        'data has already been imported from xml',
    ],
    'importpending' => [
        'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und wählen Sie anschließend im Tab Bildungsstandard die anzuzeigenden Kompetenzbereiche aus.',
        'no data has been imported yet!',
    ],
    'doimport' => [
        'Kompetenzraster importieren',
        'Import competence grid',
    ],
    'doimport_again' => [
        'Weitere Kompetenzraster importieren',
        'Import additional outcomes/competence grids',
    ],
    'doimport_own' => [
        'Schulspezifische Bildungsstandards importieren',
        'Import individual outcomes/standards',
    ],
    'scheduler_import_settings' => [
        'Settings for scheduler importing',
        'Settings for scheduler importing',
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
        'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="https://comet.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.',
        'You are using an outdated xml-file. Please create new outcomes/standards at <a href="https://comet.edustandards.org">www.edustandards.org</a> or visit <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> to download an available xml file to the blocks xml directory.',
    ],
    'do_demo_import' => [
        'Importieren Sie einen Demodatensatz, um zu sehen wie Exabis Kompetenzraster funktioniert.',
        'import demo data to see how Exabis Competence Grid works.',
    ],
    'schedulerimport' => [
        'Import von geplanten Aufgaben',
        'Scheduler import tasks',
    ],
    'add_new_importtask' => [
        'Neue geplante Aufgabe hinzufügen',
        'Add new import task',
    ],
    'importtask_title' => [
        'Title',
        'Title',
    ],
    'importtask_link' => [
        'Link to source',
        'Link to source',
    ],
    'importtask_link' => [
        'Link to source',
        'Link to source',
    ],
    'importtask_disabled' => [
        'Disabled',
        'Disabled',
    ],
    'importtask_all_subjects' => [
        'Alle Bildungsstandard',
        'All Subjects',
    ],
    'dest_course' => [
        'Ziel der importierten Aktivitäten',
        'Destination of imported activities',
    ],
    'import_activities' => [
        'Importieren Sie Aktivitäten vom Vorlagekurs in Ihren Kurs',
        'Import activities of a template course into your course',
    ],
    'download_activites' => [
        'Download activities',
        'Download activities',
    ],

    // === Configuration ===
    'explainconfig' => [
        'Um das Modul Exabis Kompetenzraster verwenden zu können, müssen hier die Kompetenzbereiche der Moodle-Instanz selektiert werden.',
        'Your outcomes have already been imported. In this configuration you have to make the selection of the main competence grids you would like to use in this Moodle installation.',
    ],
    'save_selection' => [
        'Bestätigen',
        'Confirm',
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
    'points_limit_forcourse' => [
        'Höchste Punkteanzahl',
        'Highest value for Points',
    ],
    'uses_activities' => [
        'Ich verwende Moodle Aktivitäten zur Beurteilung',
        'I work with Moodle activites',
    ],
    'show_all_descriptors' => [
        'Alle Kompetenzen im Überblick anzeigen',
        'Show all outcomes in overview',
    ],
    'useprofoundness' => [
        'Grund- und Erweiterungskompetenzen verwenden',
        'Use basic and extended competencies',
    ],
    'assessment_SelfEval_useVerbose' => [
        'verbalisiertes Schüler/innen-Feedback',
        'verbose feedback options for students',
    ],
    'selfEvalVerbose.defaultValue_long' => [
        'trifft nicht zu; trifft eher nicht zu; trifft eher zu; trifft zu',
        'does not apply; rather not true; rather applies; true',
    ],
    'selfEvalVerbose.defaultValue_short' => [
        'tn; ten; te; tz',
        'na; rnt; ra; t',
    ],
    'selfEvalVerboseExample.defaultValue_long' => [
        'nicht gelöst; mit Hilfe gelöst; selbstständig gelöst',
        'unsolved; solved with help; solved independently',
    ],
    'selfEvalVerboseExample.defaultValue_short' => [
        'ng; hg; sg',
        'un; sh; si',
    ],
    'selfEvalVerbose.1' => [
        'trifft nicht zu',
        'does not apply',
    ],
    'selfEvalVerbose.2' => [
        'trifft eher nicht zu',
        'rather not true',
    ],
    'selfEvalVerbose.3' => [
        'trifft eher zu',
        'rather applies',
    ],
    'selfEvalVerbose.4' => [
        'trifft zu',
        'true',
    ],
    'selfEvalVerboseExample.1' => [
        'nicht gelöst',
        'unsolved',
    ],
    'selfEvalVerboseExample.2' => [
        'mit Hilfe gelöst',
        'solved with help',
    ],
    'selfEvalVerboseExample.3' => [
        'selbstständig gelöst',
        'solved independently',
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
    'usehideglobalsubjects' => [
        'Überfachliche Kompetenzraster verbergen',
        'Hide global subjects',
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
        'Name des Lernmaterials',
        'Name',
    ],
    'timeframe_example' => [
        'Zeitvorschlag',
        'Timeframe',
    ],
    'example_add_taxonomy' => [
        'Neue Niveaustufe erstellen',
        'Add new taxonomy',
    ],
    'comp_based' => [
        'Nach Kompetenzen sortieren',
        'sort by competencies',
    ],
    'examp_based' => [
        'Nach Lernmaterialien sortieren',
        'sort by examples',
    ],
    'cross_based' => [
        'für Themen',
        'For Interdisciplinary Subjects',
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
    'completefile' => [
        'Gesamtbeispiel',
        'Complete file',
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
    'links' => [
        'Links',
        'Links',
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
        'Kompetenzraster auswählen',
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
    'dismiss_gradingisold' => [
        'Wollen Sie die Warnung ignorieren?',
        'Do you want to dismiss this warning?',
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
        'Fehler: Benotungen dürfen nicht größer als {limit} sein!',
        'Error: Values above {limit} are not allowed',
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
        'Änderungen wurden gespeichert!',
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
    'topic_submission_info' => [
        'Du bist dabei eine Abgabe zum Kompetenzbereich "{$a}" zu machen. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.',
        'You are to add a submission to the topic "{$a}". Your submission will be saved in Exabis ePortfolio and Teachers can view it there.',
    ],
    'descriptor_submission_info' => [
        'Du bist dabei eine Abgabe zur Kompetenz "{$a}" zu machen. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.',
        'You are about to add a submission to the descriptor "{$a}". Your submission will be saved in Exabis ePortfolio and Teachers can view it there.',
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
        'At least one link or file must be submitted',
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
        'Hier haben Sie einen Überblick über die Teilkompetenzen der ausgewählten Kompetenzen und die zugeordneten Aufgaben. Sie können das Erreichen der jeweiligen Teilkompetenz individuell bestätigen.',
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
    'all_activities' => [
        'Alle Aktivität/en',
        'All activities',
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
        'child competence',
    ],
    'assigndone' => [
        'Aufgabe erledigt: ',
        'task done: ',
    ],
    'descriptor_categories' => [
        'Niveaustufen bearbeiten: ',
        'Edit difficulty level: ',
    ],
    'descriptor_add_category' => [
        'Neue Niveaustufe hinzufügen: ',
        'Add new difficulty level: ',
    ],
    'descriptor_categories_description' => [
        'Wählen Sie hier Niveaustufe(n) für diese Kompetenz/dieses Lernmaterial aus. Sie können auch eine neue Niveaustufe hinzufügen oder dieses Feld freilassen.',
        'Choose the difficulty level for this (sub)competency/learning material. You can also add a new difficulty level or choose to not select a difficulty level.',
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
        'Hier können Sie den erstellten Aufgaben Kompetenzen zuordnen.',
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
    'teacherevaluation_short' => [
        'TE',
        'TA',
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
    'newer_grading_tooltip' => [
        'Überprüfen Sie die Beurteilung, da weitere Teilkompetenzen geändert wurden.',
        'Grading may not be up to date. </br> A childdescriptor has been graded.',
    ],
    'create_new_topic' => [
        'Neuer Kompetenzbereich',
        'New topic',
    ],
    'create_new_area' => [
        'Neuer Bereich',
        'New area',
    ],
    'really_delete' => [
        'Wirklich löschen?',
        'Are you sure you want to delete the selected items?',
    ],
    'add_niveau' => [
        'Neuen Lernfortschritt hinzufügen',
        'Add niveau',
    ],
    'please_choose' => [
        'Bitte wählen',
        'Please select',
    ],
    'please_choose_preselection' => [
        'Bitte wählen sie die Raster von denen Sie etwas löschen wollen.',
        'Please select the subjects you want to delete from.',
    ],
    'delete_niveau' => [
        'Löschen hinzufügen',
        'Delete niveau',
    ],
    'add_new_taxonomie' => [
        'neue Niveaustufe hinzufügen',
        'Add a new difficulty level',
    ],
    'taxonomy_was_deleted' => [
        'Niveaustufe was deleted',
        'Difficulty level was deleted',
    ],
    'move_up' => [
        'Move up',
        'Move up',
    ],
    'move_down' => [
        'Move down',
        'Move down',
    ],
    'also_taxonomies_from_import' => [
        'Niveaustufen aus Importen anzeigen',
        'There are also difficulty levels from import',
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
    'childcompetencies_compProfile' => [
        'Teilkompetenzen',
        'Child competencies',
    ],
    'materials_compProfile' => [
        'Lernmaterialien',
        'Materials',
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
        'Zuteilung von externen Trainer/innen für Kursteilnehmer/innen ermöglichen',
        'Allow assigning of external trainers for students.',
    ],
    'block_exacomp_external_trainer_assign_body' => [
        'Erforderlich für die Benutzung der elove App',
        'This is required for using the elove app.',
    ],
    'block_exacomp_dakora_language_file_head' => [
        'Alternative Sprachdatei für DAKORA',
        'Custom language file for Dakora',
    ],
    'block_exacomp_dakora_language_file_body' => [
        'Verwenden Sie den <a href="https://exabis.at/sprachgenerator" target="_blank">Sprachengenerator</a> um eigene Sprachdateien zu erstellen',
        'Use <a href="https://exabis.at/sprachgenerator" target="_blank">language generator</a> for creating custom language file',
    ],
    'settings_dakora_timeout' => [
        'Dakora Timeout (Sekunden)',
        'Dakora Timeout (Seconds)',
    ],
    'settings_dakora_timeout_description' => [
        '',
        '',
    ],
    'settings_dakora_url' => [
        'Url zur Dakora-App',
        'Url to Dakora-App',
    ],
    'settings_dakora_url_description' => [
        '',
        '',
    ],
    'settings_dakora_timeout_description' => [
        '',
        '',
    ],
    'settings_dakora_show_overview' => [
        'Überblick anzeigen',
        'show overview',
    ],
    'settings_dakora_show_overview_description' => [
        '',
        '',
    ],
    'settings_dakora_show_eportfolio' => [
        'ePortfolio anzeigen',
        'show ePortfolio',
    ],
    'settings_dakora_show_eportfolio_description' => [
        '',
        '',
    ],
    'block_exacomp_elove_student_self_assessment_head' => [
        'Selbsteinschätzung für Kursteilnehmer/innen in der elove App erlauben',
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
        'Die Zusammenstellung des Themas erfolgt für die ganze Schule über den Reiter Kompetenzraster. Sie können hier kursspezifisch Kompetenzen, Teilkompetenzen und Lernmaterial ausblenden. Lernmaterial kann hier ergänzt werden.',
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
        'Add competence to interdisciplinary subject',
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
        'Nicht freigegebene Kursthemen',
        'Unpublished Interdisciplinary Subjects',
    ],
    'crosssubject_drafts' => [
        'Themenvorlagen',
        'Interdisciplinary Subject Drafts',
    ],
    'de:Freigegebene Kursthemen' => [
        null,
        'Published Interdisciplinary Subjects',
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
    'crosssubject_files' => [
        'Materialien',
        'crosssubject files',
    ],
    'new_niveau' => [
        'neuer Lernfortschritt',
        'new learning progress',
    ],
    'groupcategory' => [
        'Kategorie',
        'Category',
    ],
    'new_column' => [
        'neue Spalte',
        'new column',
    ],
    'new_topic' => [
        'neuer Kompetenzbereich',
        'new Topic',
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
    'example_pool_example_button' => [
        'in den Planungsspeicher {$a->fullname}',
        'in the example pool for {$a->fullname}',
    ],
    'example_pool_example_button_forall' => [
        'in den Planungsspeicher aller Kursteilnehmer/innen',
        'in the example pool for all course participants',
    ],
    'example_trash' => [
        'Papierkorb',
        'Trash bin',
    ],
    'choosecourse' => [
        'Kurs auswählen: ',
        'Select course: ',
    ],
    'choosecoursetemplate' => [
        'Bitte wählen Sie den Kurs, in den die Moodle Aktivitäten des Kompetenzrasters importiert werden: ',
        'Select course to import moodle activities from competence grid to: ',
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
        'Um den Planungsspeicher zu befüllen, wechseln Sie bitte in das Register Kompetenzraster.',
        'For adding examples to the schedule, please use the overview',
    ],
    'pre_planning_storage' => [
        'Planungsspeicher',
        'Planning storage',
    ],
    'pre_planning_storage_popup_button' => [
        'Material verteilen',
        'Planning storage',
    ],
    'pre_planning_storage_example_button' => [
        'in meinen Planungsspeicher',
        'in my planning storage',
    ],
    'pre_planning_storage_added' => [
        'Lernmaterial wurde zum Planungsspeicher hinzugefügt.',
        'Example added to the planning storage.',
    ],
    'pre_planning_storage_already_contains' => [
        'Lernmateriel bereits im Planungsspeicher enthalten.',
        'Example is already in planning storage.',
    ],
    'save_pre_planning_selection' => [
        'Ausgewählte Lernmaterialien auf den Wochenplan der ausgewählten Schüler/innen legen',
        'Add selected examples to weekly schedule of selected students',
    ],
    'empty_pre_planning_storage' => [
        'Planungsspeicher leeren',
        'Empty planning storage',
    ],
    'noschedules_pre_planning_storage' => [
        'Der Planungsspeicher ist leer. Bitte legen Sie über die Kompetenzraster neue Lernmaterialien in den Planungsspeicher.',
        'Pplanning storage has been emptied, use the competence grid to put new examples in the planning storage.',
    ],
    'empty_trash' => [
        'Papierkorb leeren',
        'Empty trash bin',
    ],
    'empty_pre_planning_confirm' => [
        'Auch Beispiele, die ein anderer Lehrer zum Planungsspeicher hinzugefügt hat, werden entfernt. Sind Sie sicher?',
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
        'Zum Planungsspeicher hinzufügen',
        'Add to planning storage',
    ],
    'weekly_schedule_disabled' => [
        'Lernmaterial ist versteckt und kann nicht auf Wochenplan gelegt werden.',
        'Hidden example can not be added to weekly schedule',
    ],
    'pre_planning_storage_disabled' => [
        'Lernmaterial ist versteckt und kann nicht in den Planungsspeicher gelegt werden.',
        'Hidden example can not be added to planning storage.',
    ],
    'add_example_for_all_students_to_schedule' => [
        'Achtung: Sie sind dabei Lernmaterialien für alle Schüler/innen auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.',
        'Attention: Here you can add examples to the schedules of all students. This requires extra confirmation.',
    ],
    'add_example_for_group_to_schedule' => [
        'Achtung: Sie sind dabei Lernmaterialien für die ausgewählte Gruppe auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.',
        'Attention: Here you can add examples to the schedules of all students of the selected group. This requires extra confirmation.',
    ],
    'add_example_for_all_students_to_schedule_confirmation' => [
        'Sind Sie sicher, dass Sie die Lernmaterialien für alle Schüler/innen auf den Wochenplan legen möchten?',
        'You are about to add the examples to the schedules of all students, do you want to continue?',
    ],
    'delete_ics_imports_confirmation' => [
        'Sind Sie sicher, dass Sie die die von Ihnen importierten Termine für den ausgewählten Wochenplan entfernen möchten?',
        'You are about to remove your imported tasks for this weekly schedule, do you want to continue?',
    ],
    'import_ics_loading_time' => [
        'Importieren gestartet.',
        'Importing started.',
    ],
    'ics_provide_link_text' => [
        'Bitte geben Sie einen Link an.',
        'Please provide a link.',
    ],
    'add_example_for_group_to_schedule_confirmation' => [
        'Sind Sie sicher, dass Sie die Lernmaterialien für die ausgewählte Gruppe auf deren Wochenplan legen möchten?',
        'You are about to add the examples to the schedules of all students of this group, do you want to continue?',
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
    'notification_submission_subject_noSiteName' => [
        '{$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht',
        '{$a->student} submitted a solution for {$a->example}',
    ],
    'notification_submission_body' => [
        'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href={$a->viewurl}{$a->example}</a> </br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
        'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href={$a->viewurl}>{$a->example}</a> </br></br> This message has been generated form moodle site {$a->site}.',
    ],
    'notification_submission_body_noSiteName' => [
        'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href={$a->viewurl}{$a->example}</a> </br></br>',
        'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} submitted {$a->example} on {$a->date} at {$a->time}. The submission can be seen in ePortfolio: <a href={$a->viewurl}>{$a->example}</a> </br></br>',
    ],
    'notification_submission_context' => [
        'Abgabe',
        'Submission',
    ],
    'notification_grading_subject' => [
        '{$a->site}: Neue Beurteilungen im Kurs {$a->course}',
        '{$a->site}: New grading in course {$a->course}',
    ],
    'notification_grading_subject_noSiteName' => [
        'Neue Beurteilungen im Kurs {$a->course}',
        'New grading in course {$a->course}',
    ],
    'notification_grading_body' => [
        'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
        'Dear {$a->receiver}, </br></br>You have got new gradings in {$a->course} from {$a->teacher}.</br></br> This message has been generated form moodle site {$a->site}.',
    ],
    'notification_grading_body_noSiteName' => [
        'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br>',
        'Dear {$a->receiver}, </br></br>You have got new gradings in {$a->course} from {$a->teacher}.</br></br>',
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
    'notification_self_assessment_subject_noSiteName' => [
        'Neue Selbsteinschätzung im Kurs {$a->course}',
        'New self assessments in {$a->course}',
    ],
    'notification_self_assessment_body_noSiteName' => [
        'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.</br></br>.',
        'Dear Mr./Ms. {$a->receiver}, </br></br> {$a->student} has new self assessments in {$a->course}.</br></br>',
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
    'notification_example_comment_subject_noSiteName' => [
        'Neuer Kommentar bei Aufgabe {$a->example}',
        'New comment for example {$a->example}',
    ],
    'notification_example_comment_body_noSiteName' => [
        'Lieber/Liebe {$a->receiver}, </br></br> {$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.</br></br>',
        'Dear {$a->receiver}, </br></br>{$a->teacher} commented in {$a->course} the example {$a->example}.</br></br>',
    ],
    'notification_example_comment_context' => [
        'Kommentar',
        'Comment',
    ],
    'notification_weekly_schedule_subject' => [
        '{$a->site}: Neue Aufgabe am Wochenplan',
        '{$a->site}: New example on the schedule',
    ],
    'notification_weekly_schedule_subject_noSiteName' => [
        'Neue Aufgabe am Wochenplan',
        'New example on the schedule',
    ],
    'notification_weekly_schedule_body' => [
        'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.',
        'Dear {$a->receiver}, </br></br>{$a->teacher} added an example in {$a->course} to your weekly schedule.</br></br> This message has been generated form moodle site {$a->site}.',
    ],
    'notification_weekly_schedule_body_noSiteName' => [
        'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br>',
        'Dear {$a->receiver}, </br></br>{$a->teacher} added an example in {$a->course} to your weekly schedule.</br></br>',
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
        'Benachrichtigungen aktivieren',
        'activate notifications',
    ],
    'block_exacomp_notifications_body' => [
        'Bei Aktionen wie einer Lernmaterialien-Einreichung oder einer Beurteilung werden Nachrichten an die zuständigen Benutzer gesendet.',
        'Users will get notified after relevant actions.',
    ],
    'block_exacomp_assign_activities_old_method_head' => [
        'Tab Moodle-Aktivitäten zuordnen alt anzeigen',
        'Show Tab "Assign Moodle activities" old',
    ],
    'block_exacomp_assign_activities_old_method_body' => [
        'Diese Funktionalität wird über den neuen Tab "Moodle-Aktivitäten verknüpfen" abgedeckt.',
        'This Tab was default replaced with Tab "Relate Moodle activities"',
    ],
    'block_exacomp_disable_create_grid_head' => [
        '"Neues Kompetenzraster anlegen" deaktivieren',
        'Disable grid creating',
    ],
    'block_exacomp_disable_create_grid_body' => [
        'The users will not be able to create new grids',
        'The users will not be able to create new grids',
    ],
    'distribute_weekly_schedule' => [
        'Wochenplan verteilen',
        'Distribute weekly schedule',
    ],

    // === Logging ===
    'block_exacomp_logging_head' => [
        'Logging aktivieren',
        'Activate logging',
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
    'no_permission_user' => [
        'Berechtigung wurde für Authentifizierte/r Nutzer/in nicht erteilt',
        'Permissions not set for role "authenticated user"',
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
    'evaluationdate' => [
        'Bewertungsdatum',
        'evaluation Date',
    ],
    'output_current_assessments' => [
        'Ausgabe der jeweiligen Bewertungen',
        'output of current assessments',
    ],
    'student_assessment' => [
        'Selbsteinschätzung',
        'students\' assessment',
    ],
    'teacher_assessment' => [
        'Rückmeldung Lehrkraft',
        'teachers\' assessment',
    ],
    'exa_evaluation' => [
        'Lernmaterial Bewertung',
        'learning material',
    ],
    'difficulty_group_report' => [
        'Niveau',
        'difficulty level',
    ],
    'no_entries_found' => [
        'Keine Einträge gefunden',
        'no entries found',
    ],
    'assessment_date' => [
        'Bewertungsdatum',
        'assessment date',
    ],
    'number_of_found_students' => [
        'Anzahl gefundener Schüler',
        'number of found students',
    ],
    'display_settings' => [
        'Anzeigeoptionen',
        'display settings',
    ],
    'settings_explanation_tooltipp' => [
        'Die Ergebnisse im Bericht werden durch die einzelnen Filter von
        oben nach unten reduziert, aber nicht von unten nach oben.
        Wenn z.B. als einziges Filterkriterium "Niveau G" bei den Kompetenzen
        ausgewählt ist, so werden
        - alle Bildungsstandards
        - alle Kompetenzbereiche
        - Kompetenzen gefiltert nach Beurteilung mit "Niveau G" und
        - Teilkompetenzen, die Kompetenzen Niveau G zugeordnet sind, angezeigt.',
        'The results of the group report are reduced by the filters from top down but not from the bottom up.
         E.g. if a single filter "difficulty level G" at the competences is active then this will be the output:
        - all educational standards
        - all competence fields
        - competences filtered by the difficulty level G and
        - child competences of the competences that have difficulty level G.',
    ],
    'create_report' => [
        'Bericht erstellen',
        'generate report',
    ],
    'students_competences' => [
        'Schüler Kompetenzen',
        'students\' competences',
    ],
    'number_of_students' => [
        'Schüler Anzahl',
        'number of students',
    ],
    'no_specification' => [
        'noch keine Beurteilung',
        'no assessments',
    ],
    'period' => [
        'Zeitintervall',
        'time interval',
    ],
    'from' => [
        'von',
        'from',
    ],
    'to' => [
        'bis',
        'to',
    ],
    'report_type' => [
        'Berichtsart',
        'type of report',
    ],
    'report_subject' => [
        'Bildungsstandard/Raster',
        'educational standard',
    ],
    'report_learniningmaterial' => [
        'Lernmaterial',
        'learning material',
    ],
    'report_competencefield' => [
        'Kompetenzbereich',
        'competence field',
    ],
    'all_students' => [
        'Alle Schüler',
        'all students',
    ],
    'export_all_standards' => [
        'Alle Kompetenzraster dieser Moodle Instanz exportieren',
        'Export all competence grids of this Moodle installation',
    ],
    'exportieren' => [
        'Exportieren',
        'Export',
    ],
    'export_selective' => [
        'Selektiver Export',
        'Select competence grids for export',
    ],
    'select_all' => [
        'alle wählen',
        'select all',
    ],
    'deselect_all' => [
        'alle abwählen',
        'deselect all',
    ],
    'new' => [
        'neu',
        'new',
    ],
    'import_used_preselected_from_previous' => [
        'Falls eine XML-Datei bereits zuvor importiert worden ist, werden dieselben Voreinstellungen der Datenquelle verwendet',
        'If an XML has been imported previously, these values are preselected',
    ],
    'import_from_related_komet' => [
        'Kompetenzraster aus zugehörigem KOMET jetzt importieren/aktualisieren',
        'Import/update grids from related KOMET immediately',
    ],
    'import_from_related_komet_help' => [
        'Wenn die automatische Aktualisierung der Kompetenzraster über KOMET in den allgemeinen Einstellungen aktiviert ist, kann über diese Option diese Aktualisierung sofort durchgeführt werden.<br>
        Die automatische Aktualisierung erfolgt über Website-Administration - Plugins - Blöcke - Exabis Kompetenzraster: Server-URL',
        'If the automatic update of competence grids via KOMET is activated in the background via general settings, the update can be run immediately.<br>
        Automatic update can be set via Site administration - Plugins - Blocks - Exabis Competence Grid: Server-URL',
    ],
    'import_activate_scheduled_tasks' => [
        'Aufgaben aktivieren',
        'Activate these tasks',
    ],

    // === API ====
    'yes_no_No' => [
        'Nein',
        'No',
    ],
    'yes_no_Yes' => [
        'Ja',
        'Yes',
    ],
    'grade_Verygood' => [
        'sehr gut',
        'very good',
    ],
    'grade_good' => [
        'gut',
        'good,',
    ],
    'grade_Satisfactory' => [
        'befriedigend',
        'satisfactory',
    ],
    'grade_Sufficient' => [
        'ausreichend',
        'sufficient',
    ],
    'grade_Deficient' => [
        'mangelhaft',
        'deficient',
    ],
    'grade_Insufficient' => [
        'ungenügend',
        'insufficient',
    ],
    'import_select_file' => [
        'Datei aussuchen:',
        'Select file:',
    ],
    'import_selectgrids_needed' => [
        'Auswahl der Gegenstände für den Import:',
        'Select subjects for importing:',
    ],
    'import_category_mapping_needed' => [
        'Das importierte Kompetenzraster enthält ein anderes Niveaukonzept als an Ihrer Schule. Die entsprechenden Niveaueintragungen werden gelöscht. Sie können diese nachträglich selbst editieren.',
        'Grading scheme from XML is different with exacomp scheme. Please configure right correlations and try to import again:',
    ],
    'import_category_mapping_column_xml' => [
        'Niveau',
        'XML title',
    ],
    'import_category_mapping_column_exacomp' => [
        'wird geändert in',
        'Exacomp difflevel title',
    ],
    'import_category_mapping_column_level' => [
        'Niveau',
        'Level',
    ],
    'import_category_mapping_column_level_descriptor' => [
        'Kompetenz / Teilkompetenz',
        'Competence / Child competence',
    ],
    'import_category_mapping_column_level_example' => [
        'Material',
        'Material',
    ],
    'import_mapping_as_is' => [
        'weiterhin so verwenden',
        'Use as is',
    ],
    'import_mapping_delete' => [
        'Delete',
        'Delete',
    ],
    'save' => [
        'Speichern',
        'Save',
    ],
    'add_competence_insert_learning_progress' => [
        'Um eine Kompetenz einfügen zu können, müssen Sie zuerst einen Lernfortschritt auswählen oder hinzufügen!',
        'To insert a new competence, you must first select or add a difficulty level!',
    ],
    'delete_level_from_another_source' => [
        'Importierter Kompetenzraster hat Inhalte einer anderen Quelle. Wenn hier gelöscht wird, wird auch von der anderen Quelle gelöscht! Nur löschen wenn Sie sicher sind!',
        'Content from another source. If you delete them here, they will be deleted from the other source as well! Only delete if you are sure!',
    ],
    'delete_level_has_children_from_another_source' => [
        'Importierter Kompetenzraster wurde in dieser Installation weiterbearbeitet. Diese Ergänzungen sollten vor dem Löschen ausgewiesen werden. Ansonsten werden potenziell auch die Inhalte anderer Raster gelöscht wenn sie diesen Raster löschen! ',
        'Has children from another source! If you do not remove the children first and delete this subject, you will also delete from the other source!',
    ],
    'delete_competency_that_has_gradings' => [
        'Diese Kompetenz hat bereits Beurteilungen! Nur löschen wenn Sie sicher sind!',
        'This competence already has gradings! Only delete if you are sure!',
    ],
    'delete_competency_that_has_children_with_gradings' => [
        'Darunterliegende Kompetenzen haben bereits Beurteilungen! Nur löschen wenn Sie sicher sind!',
        'Children of this competence already have gradings! Only delete if you are sure!',
    ],
    'delete_competency_that_is_used_in_course' => [
        'Achtung! Dieser Raster ist in folgenden Kursen in Verwendung: ',
        'Warning! This grid is used in the following courses: ',
    ],

    'module_used_availabilitycondition_competences' => [
        'Verknüpfte Exabis Kompetenzen automatisch erreichen, wenn die Bedingungen erfüllt sind.',
        'Grant related exabis competencies when condition is met',
    ],
    'use_isglobal' => [
        'Überfachlicher Kurs',
        'global course',
    ],
    'globalgradings' => [
        'Überfachliche Bewertungen',
        'global gradings',
    ],
    'assign_dakora_teacher' => [
        'Lehrkraft für überfachliche Kompetenzen zuweisen',
        'assign teacher for interdisciplinary subjects',
    ],
    'assign_dakora_teacher_link' => [
        'Hier klicken um Lehrkraft für überfachliche Kompetenzen zuzuweisen',
        'Click here to assign teacher for interdisciplinary subjects',
    ],
    'transferable_skills' => [
        'Überfachliche Kompetenzen',
        'Transferable skills',
    ],

    //Dakora strings
    'dakora_string1' => [
        'deutscher string1',
        'english string1',
    ],
    'dakora_string2' => [
        'deutscher string2',
        'english string2',
    ],
    'dakora_string3' => [
        'deutscher string3',
        'english string3',
    ],
    'dakora_niveau_after_descriptor_title' => [
        'Niveau',
        'Level',
    ],

    'active_show' => [
        'aktiv (anzeigen)',
        'active (show them)',
    ],
    'donotleave_page_message' => [
        'You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?',
        'You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?',
    ],

    'privacy:metadata:block_exacompcompuser' => [
        'Storage for student evaluations',
        'Storage for student evaluations',
    ],
    'privacy:metadata:block_exacompcompuser:userid' => [
        'Student who was evaluated',
        'Student who was evaluated',
    ],
    'privacy:metadata:block_exacompcompuser:compid' => [
        'Competence which was evaluated',
        'Competence which was evaluated',
    ],
    'privacy:metadata:block_exacompcompuser:reviewerid' => [
        'Reviewer who evaluated',
        'Reviewer who evaluated',
    ],
    'privacy:metadata:block_exacompcompuser:role' => [
        'Role of reviewer who evaluated',
        'Role of reviewer who evaluated',
    ],
    'privacy:metadata:block_exacompcompuser:courseid' => [
        'Course',
        'Course',
    ],
    'privacy:metadata:block_exacompcompuser:value' => [
        'Result of evaluation',
        'Result of evaluation',
    ],
    'privacy:metadata:block_exacompcompuser:comptype' => [
        'Type of evaluated competence',
        'Type of evaluated competence',
    ],
    'privacy:metadata:block_exacompcompuser:timestamp' => [
        'Date of evaluation',
        'Date of evaluation',
    ],
    'privacy:metadata:block_exacompcompuser:additionalinfo' => [
        'Result of evaluation',
        'Result of evaluation',
    ],
    'privacy:metadata:block_exacompcompuser:evalniveauid' => [
        'Difficulty level of evaluation',
        'Difficulty level of evaluation',
    ],
    'privacy:metadata:block_exacompcompuser:gradingisold' => [
        'is it old?',
        'is it old?',
    ],
    'privacy:metadata:block_exacompcompuser:globalgradings' => [
        'global value',
        'global value',
    ],
    'privacy:metadata:block_exacompcompuser:gradinghistory' => [
        'history of grading',
        'history of grading',
    ],
    'privacy:metadata:block_exacompcompuser:personalisedtext' => [
        'additional personalised text',
        'additional personalised text',
    ],

    'privacy:metadata:block_exacompcmassign' => [
        'Storage for auto grading mechanism: does not need to be exported',
        'Storage for auto grading mechanism: does not need to be exported',
    ],
    'privacy:metadata:block_exacompcmassign:coursemoduleid' => [
        'Course module id',
        'Course module id',
    ],
    'privacy:metadata:block_exacompcmassign:userid' => [
        'Student ids',
        'Student ids',
    ],
    'privacy:metadata:block_exacompcmassign:timemodified' => [
        'timestamp',
        'timestamp',
    ],
    'privacy:metadata:block_exacompcmassign:relateddata' => [
        'Data, related to the student',
        'Data, related to the student',
    ],

    'privacy:metadata:block_exacompexameval' => [
        'Storage for student evaluations (examples)',
        'Storage for student evaluations (examples)',
    ],
    'privacy:metadata:block_exacompexameval:exampleid' => [
        'Example',
        'Example',
    ],
    'privacy:metadata:block_exacompexameval:courseid' => [
        'Course',
        'Course',
    ],
    'privacy:metadata:block_exacompexameval:studentid' => [
        'Student who was evaluated',
        'Student who was evaluated',
    ],
    'privacy:metadata:block_exacompexameval:teacher_evaluation' => [
        'Evaluation value from teacher',
        'Evaluation value from teacher',
    ],
    'privacy:metadata:block_exacompexameval:additionalinfo' => [
        'Evaluation value from teacher (used for some types of assessment)',
        'Evaluation value from teacher (used for some types of assessment)',
    ],
    'privacy:metadata:block_exacompexameval:teacher_reviewerid' => [
        'Teacher who evaluated',
        'Teacher who evaluated',
    ],
    'privacy:metadata:block_exacompexameval:timestamp_teacher' => [
        'Time of teacher evaluation',
        'Time of teacher evaluation',
    ],
    'privacy:metadata:block_exacompexameval:student_evaluation' => [
        'Self evaluation',
        'Self evaluation',
    ],
    'privacy:metadata:block_exacompexameval:timestamp_student' => [
        'Time of self evaluation',
        'Time of self evaluation',
    ],
    'privacy:metadata:block_exacompexameval:evalniveauid' => [
        'Niveau',
        'Niveau',
    ],
    'privacy:metadata:block_exacompexameval:resubmission' => [
        'resubmission is allowed/not allowed',
        'resubmission is allowed/not allowed',
    ],

    'privacy:metadata:block_exacompcrossstud_mm' => [
        'Share crossubjects to the students',
        'Share crossubjects to the students',
    ],
    'privacy:metadata:block_exacompcrossstud_mm:crosssubjid' => [
        'Crossubject id',
        'Crossubject id',
    ],
    'privacy:metadata:block_exacompcrossstud_mm:studentid' => [
        'Student',
        'Student',
    ],

    'privacy:metadata:block_exacompdescrvisibility' => [
        'Visibility descriptors for users',
        'Visibility descriptors for users',
    ],
    'privacy:metadata:block_exacompdescrvisibility:courseid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacompdescrvisibility:descrid' => [
        'Competence id',
        'Competence id',
    ],
    'privacy:metadata:block_exacompdescrvisibility:studentid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacompdescrvisibility:visible' => [
        'Visible marker',
        'Visible marker',
    ],

    'privacy:metadata:block_exacompexampvisibility' => [
        'Visibility examples for users',
        'Visibility examples for users',
    ],
    'privacy:metadata:block_exacompexampvisibility:courseid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacompexampvisibility:exampleid' => [
        'Material id',
        'Material id',
    ],
    'privacy:metadata:block_exacompexampvisibility:studentid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacompexampvisibility:visible' => [
        'Visible marker',
        'Visible marker',
    ],

    'privacy:metadata:block_exacompexternaltrainer' => [
        'External trainers for students',
        'External trainers for students',
    ],
    'privacy:metadata:block_exacompexternaltrainer:trainerid' => [
        'Trainer',
        'Trainer',
    ],
    'privacy:metadata:block_exacompexternaltrainer:studentid' => [
        'Student',
        'Student',
    ],

    'privacy:metadata:block_exacompprofilesettings' => [
        'which course to include in the competence profile',
        'which course to include in the competence profile',
    ],
    'privacy:metadata:block_exacompprofilesettings:itemid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacompprofilesettings:userid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacompprofilesettings:block' => [
        'associated block: exacomp, exastud or exaport',
        'associated block: exacomp, exastud or exaport',
    ],
    'privacy:metadata:block_exacompprofilesettings:feedback' => [
        'verbal feedback should be displayed (for exastud reviews)',
        'verbal feedback should be displayed (for exastud reviews)',
    ],

    'privacy:metadata:block_exacompschedule' => [
        'examples, added to student\'s schedule list',
        'examples, added to student\'s schedule list',
    ],
    'privacy:metadata:block_exacompschedule:studentid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacompschedule:exampleid' => [
        'Scheduled example',
        'Scheduled example',
    ],
    'privacy:metadata:block_exacompschedule:creatorid' => [
        'Creator of scheduled record',
        'Creator of scheduled record',
    ],
    'privacy:metadata:block_exacompschedule:timecreated' => [
        'Time of creating record',
        'Time of creating record',
    ],
    'privacy:metadata:block_exacompschedule:timemodified' => [
        'Time of editing record',
        'Time of editing record',
    ],
    'privacy:metadata:block_exacompschedule:courseid' => [
        'Course',
        'Course',
    ],
    'privacy:metadata:block_exacompschedule:sorting' => [
        'Sorting of records',
        'Sorting of records',
    ],
    'privacy:metadata:block_exacompschedule:start' => [
        'Start time',
        'Start time',
    ],
    'privacy:metadata:block_exacompschedule:endtime' => [
        'End time',
        'End time',
    ],
    'privacy:metadata:block_exacompschedule:deleted' => [
        'Marker of deleted record',
        'Marker of deleted record',
    ],
    'privacy:metadata:block_exacompschedule:distributionid' => [
        'distribution id',
        'distribution id',
    ],
    'privacy:metadata:block_exacompschedule:source' => [
        'S/T as a type',
        'S/T as a type',
    ],

    'privacy:metadata:block_exacompsolutvisibility' => [
        'which examplesolutions are visible',
        'which examplesolutions are visible',
    ],
    'privacy:metadata:block_exacompsolutvisibility:courseid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacompsolutvisibility:exampleid' => [
        'Example id',
        'Example id',
    ],
    'privacy:metadata:block_exacompsolutvisibility:studentid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacompsolutvisibility:visible' => [
        'visible marker',
        'visible marker',
    ],

    'privacy:metadata:block_exacomptopicvisibility' => [
        'which topics are visible',
        'which topics are visible',
    ],
    'privacy:metadata:block_exacomptopicvisibility:courseid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacomptopicvisibility:topicid' => [
        'Topic id',
        'Topic id',
    ],
    'privacy:metadata:block_exacomptopicvisibility:studentid' => [
        'Student',
        'Student',
    ],
    'privacy:metadata:block_exacomptopicvisibility:visible' => [
        'visible marker',
        'visible marker',
    ],
    'privacy:metadata:block_exacomptopicvisibility:niveauid' => [
        'Niveau id',
        'Niveau id',
    ],

    'privacy:metadata:block_exacompcrosssubjects' => [
        'Cross subjects, created by the user',
        'Cross subjects, created by the user',
    ],
    'privacy:metadata:block_exacompcrosssubjects:title' => [
        'Title',
        'Title',
    ],
    'privacy:metadata:block_exacompcrosssubjects:description' => [
        'Description',
        'Description',
    ],
    'privacy:metadata:block_exacompcrosssubjects:courseid' => [
        'Course id',
        'Course id',
    ],
    'privacy:metadata:block_exacompcrosssubjects:creatorid' => [
        'creator id',
        'creator id',
    ],
    'privacy:metadata:block_exacompcrosssubjects:shared' => [
        'shared or not',
        'shared or not',
    ],
    'privacy:metadata:block_exacompcrosssubjects:subjectid' => [
        'related subject id',
        'related subject id',
    ],
    'privacy:metadata:block_exacompcrosssubjects:groupcategory' => [
        'group category',
        'group category',
    ],

    'privacy:metadata:block_exacompglobalgradings' => [
        'Global grade text for a subject/topic/competence',
        'Global grade text for a subject/topic/competence',
    ],
    'privacy:metadata:block_exacompglobalgradings:userid' => [
        'Student id',
        'Student id',
    ],
    'privacy:metadata:block_exacompglobalgradings:compid' => [
        'competence id',
        'competence id',
    ],
    'privacy:metadata:block_exacompglobalgradings:comptype' => [
        'competence type: 0 - descriptor; 1 - topic',
        'competence type: 0 - descriptor; 1 - topic',
    ],
    'privacy:metadata:block_exacompglobalgradings:globalgradings' => [
        'content of global grading',
        'content of global grading',
    ],

    'privacy:metadata:block_exacompwsdata' => [
        'temporary data for webservices',
        'temporary data for webservices',
    ],
    'privacy:metadata:block_exacompwsdata:token' => [
        'token value',
        'token value',
    ],
    'privacy:metadata:block_exacompwsdata:userid' => [
        'User',
        'User',
    ],
    'privacy:metadata:block_exacompwsdata:data' => [
        'data content',
        'data content',
    ],
    'OR' => [
        'ODER',
        'OR',
    ],
    'AND' => [
        'UND',
        'AND',
    ],
    'AND teacherevaluation from' => [
        'UND Lehrerbeurteilung von',
        'teacherevaluation from',
    ],
    'to' => [
        'bis',
        'to',
    ],
    'report all educational standards' => [
        'Alle Bildungsstandard/Raster welche folgenden Filterkriterien entsprechen: ',
        'All educational standards which pass following filters: ',
    ],
    'report all topics' => [
        'Alle Kompetenzbereiche von Bildungsstandard/Rastern die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ',
        'All competence fields of educational standards that have not been filtered AND pass following filters: ',
    ],
    'report all descriptor parents' => [
        'Alle Kompetenzen von Kompetenzbereichen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ',
        'All competences of competence fields that have not been filtered AND pass following filters: ',
    ],
    'report all descriptor children' => [
        'Alle Teilkompetenzen von Kompetenzen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ',
        'All child competences of competences that have not been filtered AND pass following filters: ',
    ],
    'report all descriptor examples' => [
        'Alle Lernmaterialien von Kompetenzbereichen, Kompetenzen und Teilkompetenzen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ',
        'All examples of competence fields, competences and child competences that have not been filtered AND pass following filters: ',
    ],
    'filterlogic' => [
        'Filterkriterien: ',
        'Filterlogic: ',
    ],
    'topic_description' => [
        'Bezeichnung der ersten Zeile (z.B. Kompetenzbereich)',
        'Create your first topic:',
    ],
    'niveau_description' => [
        'Bezeichnung der ersten Spalte (z.B. LFS 1)',
        'Create your first Difficulty Level:',
    ],
    'descriptor_description' => [
        'Eintrag der ersten Zelle (z.B. Kompetenzbeschreibung)',
        'Create your first descriptor:',
    ],
    'selectcourse_filter' => [
        'Filter',
        'Filter',
    ],
    'selectcourse_filter_schooltype' => [
        'Schulart',
        'Schooltype',
    ],
    'selectcourse_filter_onlyselected' => [
        'Nur ausgewählte Raster anzeigen',
        'Show only selected grids',
    ],
    'selectcourse_filter_submit' => [
        'Filter',
        'Filter',
    ],
    'selectcourse_filter_emptyresult' => [
        'Keine Ergebnisse zu diesem Filter',
        'Nothing to show',
    ],
    'descriptor_label' => [
        'Kompetenztitel',
        'Competency title',
    ],
    'export_password_message' => [
        'Bitte notieren Sie sich das Passwort "<strong>{$a}</strong>", bevor Sie fortfahren.<br/><br/>
		Hinweis: Passwortgeschützte zip-Dateien können unter Windows zwar geöffnet werden, aber die Dateien innerhalb der Zip-Datei können nur mit einem externen Programm (z.B. 7-Zip) extrahiert werden.
		',
        'Please remember the password "<strong>{$a}</strong>" before proceeding',
    ],
    'settings_heading_security' => [
        'Sicherheit',
        'Security',
    ],
    'settings_heading_security_description' => [
        '',
        '',
    ],
    'settings_example_upload_global' => [
        'Materialien global hochladen',
        'Global material upload',
    ],
    'settings_example_upload_global_description' => [
        'Von Lehrern hochgeladene Materialien sind global verfüger. Die Materialien sind damit auch in anderen Kursen mit dem gleichen Raster sichtbar.',
        'Materials uploaded by a teacher are available globally. If the same grid is used ina  different course, the material will be visible.',
    ],
    'settings_show_teacherdescriptors_global' => [
        'Selbsterzeugte Kompetenzen global anzeigen',
        'Global teacher competences',
    ],
    'settings_show_teacherdescriptors_global_description' => [
        'Von Lehrern erstellte Kompetenzen sind global verfüger. Die Kompetenzen sind damit auch in anderen Kursen mit dem gleichen Raster sichtbar.',
        'Competences created by a teacher are available globally. If the same grid is used in a different course, the competence will be visible.',
    ],
    'settings_export_password' => [
        'Sicherung von Kompetenzrastern mit Passwort schützen (AES-256 Verschlüsselung)',
        'Passwort protection (AES-256 encryption) for competence grid export',
    ],
    'settings_export_password_description' => [
        '(Nur ab php Version 7.2 verfügbar)',
        '(Only available from php version 7.2 on)',
    ],
    'pre_planning_materials_assigned' => [
        'Ausgewählte Materialien wurden den ausgewählten Schülern/Gruppen zugeteilt.',
        'Selected materials were assigned to the selected students / groups.',
    ],
    'grade_example_related' => [
        'Verbundene Kompetenzen und Materialien bewerten.',
        'Assess related competences and descriptors',
    ],
    'freematerials' => [
        'Freie Materialien',
        'free materials',
    ],
    'radargraphtitle' => [
        'Netzdiagramm',
        'Radar Graph',
    ],
    'radargrapherror' => [
        'Der Radargraph kann nur bei 3-13 Achsen dargestellt werden',
        'Radargraph can only be displayed with 3-13 axis',
    ],
    'studentcomp' => [
        'Laut Selbsteinschätzung erreichte Kompetenzen',
        'self evaluated competencies',
    ],
    'teachercomp' => [
        'Erreichte Kompetenzen',
        'gained competencies',
    ],
    'pendingcomp' => [
        'Ausstehende Kompetenzen',
        'pending competencies',
    ],
    'topicgrading' => [
        'Gesamtbewertung des Themas: ',
        'Total topic grading: ',
    ],
    'import_ics_title' => [
        'WebUntis-Import',
        'WebUntis import',
    ],
    'hide_imports_checkbox_label' => [
        'WebUntis Anzeigen: ',
        'Show WebUntis: ',
    ],
    'import_ics' => [
        'Kalender importieren',
        'import calendar',
    ],
    'delete_imports' => [
        'Importierte Termine löschen',
        'delete my imports',
    ],
    'upload_ics_file' => [
        'Datei auswählen: ',
        'Choose file: ',
    ],
    'is_teacherexample' => [
        'Lehrermaterial',
        'Is the teacher\'s example',
    ],
    'delete...' => [
        'Löschen...',
        'Delete...',
    ],
    'data_imported_title' => [
        'Daten jetzt importieren',
        'Import data immediately',
    ],
    'competence_overview_teacher_short' => [
        'L:',
        'T:',
    ],
    'competence_overview_student_short' => [
        'S:',
        'S:',
    ],
    'filterClear' => [
        'Filter löschen',
        'Clear filter',
    ],
    'editor' => [
        'Überarbeitung durch',
        'Edited by',
    ],
    'hide_for_all_students' => [
        'für alle TN verstecken',
        'Hide for all stundents',
    ],
    'tab_teacher_settings_course_assessment' => [
        'Kursspezifische Beurteilung',
        'Course assessment',
    ],
    'course_assessment_config_infotext' => [
        'Wählen Sie das gewünschte Beurteilungsschema aus.',
        'Choose an assessment scheme',
    ],
    'course_assessment_use_global' => [
        'Globale Beurteilungseinstellung nutzen',
        'Use global assessment settings',
    ],
    'course_assessment_settings' => [
        'Kursspezifische Beurteilung',
        'Course assessment',
    ],
    'close' => [
        'Schließen',
        'Close',
    ],
    'opencomps' => [
        'Kompetenzen auswählen',
        'Choose your competences',
    ],
    'expandcomps' => [
        'Alle öffnen',
        'Expand all',
    ],
    'contactcomps' => [
        'Alle schließen',
        'Contract all',
    ],
    'questlink' => [
        'Fragen verknüpfen',
        'Relate questions',
    ],
    'select_subjects' => [
        'Raster auswählen',
        'Select Subjects',
    ],
    'overview_examples_report_title' => [
        'Aufgabenübersicht zum Kompetenzerwerb',
        'Overview of the examples',
    ],
    'block_exacomp_link_to_dakora_app' => [
        'zur Dakora-App',
        'to Dakora-App',
    ],
    'diggrapp_cannotcreatetoken' => [
        'Can not have access to this moodle installation',
        'Can not have access to this moodle installation',
    ],
    'grid_creating_is_disabled' => [
        'Die Neuanlage von Rastern ist deaktiviert!',
        'Grid creation is disabled!',
    ],
    'save_hvp_activity' => [
        'HVP Aktivität speichern',
        'Save HVP activity',
    ],
    'edulevel_without_assignment_title' => [
        'ohne feste Zuordnung',
        'without a specific assignment',
    ],
    'schooltype_without_assignment_title' => [
        'ohne feste Zuordnung',
        'without a specific assignment',
    ],
    'please_select_topic_first' => [
        'Bitte wählen Sie zuerst in der linken Leiste einen Kompetenzbereich aus',
        'Please first select a topic of competence in the left bar',
    ],
    'no_course_templates' => [
        'Kann keinen Kurs finden, der als Vorlage verwendet werden kann',
        'Can not find any course to use as a template',
    ],
];
