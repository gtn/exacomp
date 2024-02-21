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
$string['exacomp:editingteacher'] = 'Editing teacher';
$string['exacomp:getfullcompetencegridforprofile'] = 'for WebService block_exacomp_get_fullcompetence_grid_for_profile';

//Cache definition
$string['cachedef_visibility_cache'] = 'Cache zur Performanceerhöhung von Sichtbarkeitsabfragen';

// === Admin Tabs ===
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Website-Einstellungen';
$string['tab_admin_configuration'] = 'Vorauswahl der Kompetenzraster';
$string['admin_config_pending'] = 'Vorauswahl der Kompetenzen durch den Administrator notwendig';
$string['tab_admin_taxonomies'] = 'Niveaustufen';

// === Teacher Tabs ===
$string['tab_teacher_settings'] = 'Kurs-Einstellungen';
$string['tab_teacher_settings_configuration'] = 'Einstellungen';
$string['tab_teacher_settings_selection_st'] = 'Schulart / Bezüge zum Bildungsplan';
$string['tab_teacher_settings_selection'] = 'Auswahl der Kompetenzbereiche';
$string['tab_teacher_settings_assignactivities'] = 'Moodle-Aktivitäten zuordnen';
$string['tab_teacher_settings_activitiestodescriptors'] = 'Moodle-Aktivitäten verknüpfen';
$string['tab_teacher_settings_questiontodescriptors'] = 'Test-Fragen verknüpfen';
$string['tab_teacher_settings_badges'] = 'Auszeichnungen bearbeiten';
$string['tab_teacher_settings_new_subject'] = 'Neues Kompetenzraster anlegen';
$string['tab_teacher_settings_taxonomies'] = 'Niveaustufen';
$string['tab_teacher_settings_taxonomies_help'] = 'Lernmaterialien als auch Kompetenzen können mit Niveaustufen versehen werden (üblicherweise im Kompetenzraster-Erfassungstool KOMET).</br>
Lernmaterialien und Kompetenzen können nach Niveaustufen gefiltert werden.</br>
Ein anderer Begriff für Niveaustufen ist Taxonomien - z.B. kann die Bloomsche Taxonomie für die Einstufung des Lernniveaus herangezogen werden (siehe <a href=\'https://de.wikipedia.org/wiki/Lernziel#Taxonomien\' target=\'_blank\'>https://de.wikipedia.org/wiki/Lernziel#Taxonomien</a>)
';
$string['tab_teacher_report_general'] = 'Berichte';
$string['tab_teacher_report_annex'] = 'Berichte ';
$string['tab_teacher_report_annex_title'] = 'Anlage zum Lernentwicklungsbericht';
$string['tab_teacher_report_profoundness'] = 'Grund- und Erweiterungskompetenzen ';
$string['tab_teacher_report_profoundness_title'] = 'Grund- und Erweiterungskompetenzen verwenden';
$string['create_html'] = 'Bericht im HTML-Format generieren (Voransicht)';
$string['create_docx'] = 'Bericht im docx-Format generieren';
$string['create_pdf'] = 'Bericht im pdf-Format generieren';
$string['create_html_report'] = 'Bericht im HTML-Format generieren';
$string['create_docx_report'] = 'Bericht im docx-Format generieren';
$string['create_pdf_report'] = 'Bericht im pdf-Format generieren';
$string['tab_teacher_report_annex_template'] = 'template docx';
$string['tab_teacher_report_annex_delete_template'] = 'löschen';

// === Student Tabs ===
$string['tab_student_all'] = 'Alle erworbenen Kompetenzen';

// === Generic Tabs (used by Teacher and Students) ===
$string['tab_competence_gridoverview'] = 'Übersicht';
$string['tab_competence_overview'] = 'Kompetenzraster';
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
$string['tab_group_reports'] = 'Berichte';
$string['assign_descriptor_to_crosssubject'] = 'Die Kompetenz "{$a}" den folgenden Themen zuordnen:';
$string['assign_descriptor_no_crosssubjects_available'] = 'Es sind keine Themen vorhanden, legen Sie welche an.';
$string['first_configuration_step'] = 'Der erste Konfigurationsschritt besteht darin, Daten in das Exabis Kompetenzraster Modul zu importieren.';
$string['second_configuration_step'] = 'Im zweiten Konfigurationsschritt müssen Kompetenzraster ausgewählt werden.';
$string['next_step'] = 'Dieser Konfigurationsschritt wurde abgeschlossen. Klicken Sie hier um zum Nächsten zu gelangen.';
$string['next_step_teacher'] = 'Die Konfiguration, die vom Administrator vorgenommen werden muss, ist hiermit abgeschlossen. Um mit der kursspezifischen Konfiguration fortzufahren klicken Sie hier.';
$string['teacher_first_configuration_step'] = 'Im ersten Konfigurationsschritt der Kurs-Kompetenzraster müssen einige generelle Einstellungen getroffen werden.';
$string['teacher_second_configuration_step'] = 'Im zweiten Konfigurationsschritt müssen Themenbereiche ausgewählt werden, mit denen Sie in diesem Kurs arbeiten möchten.';
$string['teacher_third_configuration_step'] = 'Im nächsten Schritt werden Moodle-Aktivitäten mit Kompetenzen assoziiert. ';
$string['teacher_third_configuration_step_link'] = '(Optional: Wenn Sie nicht mit Moodle-Aktivitäten arbeiten möchten, dann entfernen Sie das Häkchen "Ich möchte mit Moodle-Aktivitäten arbeiten" im Tab "Einstellungen".)';
$string['completed_config'] = 'Die Exabis Kompetenzraster Konfiguration wurde abgeschlossen.';
$string['optional_step'] = 'In Ihrem Kurs sind noch keine Teilnehmer/innen eingeschrieben, betätigen Sie diesen Link wenn Sie das jetzt machen möchten.';
$string['enrol_users'] = 'Schreiben sie Teilnehmer/innen ein, um Exacomp verwenden zu können.';
$string['next_step_first_teacher_step'] = 'Klicken Sie hier um zum nächsten Schritt zu gelangen.';

// === Block Settings ===
$string['settings_xmlserverurl'] = 'Server-URL';
$string['settings_configxmlserverurl'] = 'Url zu einer XML Datei, die verwendet wird, um die Daten aktuell zu halten';
$string['settings_autotest'] = 'Automatische Beurteilung durch Moodle-Aktivitäten';
$string['settings_autotest_description'] = 'Kompetenzen oder Aufgaben die mit Aktivitäten verbunden sind, gelten automatisch als erworben, wenn die in der Aktivität angegebenen Aktivitätsabschlusskriterien erfüllt sind.';
$string['settings_testlimit'] = 'Testlimit in %';
$string['settings_testlimit_description'] = 'Dieser Prozentwert muss erreicht werden, damit die Kompetenz als erworben gilt';
$string['settings_usebadges'] = 'Badges/Auszeichnungen verwenden';
$string['settings_usebadges_description'] = 'Anhaken um den Badges/Auszeichnungen Kompetenzen zuzuteilen';
$string['settings_interval'] = 'Einheitenlänge';
$string['settings_interval_description'] = 'Die Länge der Einheiten im Wochenplan in Minuten';
$string['settings_scheduleunits'] = 'Anzahl der Einheiten';
$string['settings_scheduleunits_description'] = 'Anzahl der Einheiten im Wochenplan';
$string['settings_schedulebegin'] = 'Beginn der Einheiten';
$string['settings_schedulebegin_description'] = 'Beginnzeitpunkt der ersten Einheit im Wochenplan. Format hh:mm';
$string['settings_description_nurmoodleunddakora'] = '<b>Nur Moodle und Dakora App</b>';
$string['settings_description_nurdakora'] = '<b>Nur Dakora App</b>';
$string['settings_description_nurdiggr'] = '<b>Nur Diggr+ und elove App</b>';
$string['settings_description_nurdakoraplus'] = '<b>Nur DakoraPlus App</b>';
$string['settings_admin_scheme'] = 'Vordefinierte Konfiguration';
$string['settings_admin_scheme_description'] = 'Beurteilungen können auf unterschiedlichem Niveau erfolgen.';
$string['settings_admin_scheme_none'] = 'keine Niveaus';
$string['settings_additional_grading'] = 'Angepasste Bewertung';
$string['settings_additional_grading_description'] = 'Bewertung für Teilkompetenzen und Lernmaterialien global auf "nicht erreicht(0)" - "vollständig erreicht(3)" beschränken';
$string['settings_periods'] = 'Einträge für Zeittafel';
$string['settings_periods_description'] = 'Der Wochenplan ist flexibel an jedes Stunden- und Pausenraster anpassbar. Verwenden Sie im Textblock für jeden Zeitblock eine neue Zeile. Es sind beliebige Texteinträge erlaubt, z.B. "1. Std" oder "07:30 - 09:00".';
$string['settings_heading_general'] = 'Allgemein';
$string['settings_heading_assessment'] = 'Beurteilung';
$string['settings_heading_visualisation'] = 'Darstellung';
$string['settings_heading_technical'] = 'Administratives';
$string['settings_heading_apps'] = 'Apps-Einstellungen';
$string['settings_new_app_login'] = 'SSO-App-Login verwenden';
$string['settings_dakora_teacher_link'] = 'Klicken Sie, um die Dakoralehrer festzulegen';
$string['settings_applogin_redirect_urls'] = 'Applogin Urls';
$string['settings_applogin_redirect_urls_description'] = '';
$string['settings_applogin_enabled'] = 'App-Login aktivieren';
$string['settings_applogin_enabled_description'] = 'Erlaubt den Login von Exabis Apps (Diggr+, Dakora, Dakora+, elove)';
$string['settings_setapp_enabled'] = 'SET-App Funktionen aktivieren';
$string['settings_setapp_enabled_description'] = 'Anlegen von Userkonten über App erlauben.';
$string['settings_sso_create_users'] = 'SSO: Neue Benutzer erstellen';
$string['settings_sso_create_users_description'] = '';
$string['settings_msteams_client_id'] = 'Diggr+ MS Teams App Client Id';
$string['settings_msteams_client_id_description'] = '';
$string['settings_msteams_client_secret'] = 'Diggr+ MS Teams App Client Secret';
$string['settings_msteams_client_secret_description'] = '';
$string['dakora_teachers'] = 'Dakoralehrer';
$string['settings_new_app_login_description'] = 'Der neue App-Login erlaubt Benutzern sich mit allen aktivierten Moodle Login-Plugins einzuloggen. Diese Einstellung ist nicht mit dem Gamification Plugin kompatibel.';
$string['settings_heading_performance'] = 'Performance';
$string['settings_heading_performance_description'] = 'Sollte sich die Kompetenzraster-Ansicht nur langsam aufbauen, können diese Einstellungen zur Lade-Optimierung verwendet werden.';
$string['settings_heading_scheme'] = 'Generisches Bewertungsschema';
$string['settings_assessment_are_you_sure_to_change'] = 'Wollen sie wirklich das Bewertungsschema ändern? Bestehende Bewertungen können verloren gehen oder ihre Aussagekraft verlieren';
$string['settings_assessment_scheme_0'] = 'Keines';
$string['settings_assessment_scheme_1'] = 'Noten';
$string['settings_assessment_scheme_2'] = 'Verbalisierung';
$string['settings_assessment_scheme_3'] = 'Punkte';
$string['settings_assessment_scheme_4'] = 'Ja/Nein';
$string['settings_assessment_diffLevel'] = 'Niveau';
$string['settings_assessment_SelfEval'] = 'Selbsteinschätzung';
$string['settings_assessment_target_example'] = 'Material';
$string['settings_assessment_target_childcomp'] = 'Teilkompetenz';
$string['settings_assessment_target_comp'] = 'Kompetenz';
$string['settings_assessment_target_topic'] = 'Kompetenzbereich';
$string['settings_assessment_target_subject'] = 'Fach';
$string['settings_assessment_target_theme'] = 'Thema (fachübergreifend)';
$string['settings_assessment_points_limit'] = 'Höchste Punkteanzahl';
$string['settings_assessment_points_limit_description'] = 'Bewertungsschema Punkte, die höchst mögliche Punkteanzahl die eingegeben werden kann.';
$string['settings_assessment_points_negativ'] = 'Negative Beurteilung Punkte';
$string['settings_assessment_points_negativ_description'] = 'Untergrenze (negative Beurteilung) des Punkte-Werts im Beurteilungs-Schema';
$string['settings_assessment_grade_limit'] = 'Höchste Note';
$string['settings_assessment_grade_limit_description'] = 'Bewertungsschema Note, die höchst mögliche Note die eingegeben werden kann.';
$string['settings_assessment_grade_negativ'] = 'Negative Beurteilung Noten';
$string['settings_assessment_grade_negativ_description'] = 'Untergrenze (negative Beurteilung) des Noten-Werts im Beurteilungs-Schema';
$string['settings_assessment_diffLevel_options'] = 'Niveau Werte';
$string['settings_assessment_diffLevel_options_description'] = 'Liste der möglichen Werte des Niveaus, z.B: G,M,E,Z';
$string['settings_assessment_diffLevel_options_default'] = 'G,M,E,Z';
$string['settings_assessment_verbose_options'] = 'Erreichungsgrad';
$string['settings_assessment_verbose_options_description'] = 'Liste der möglichen Werte der Verbalisierung, z.B: nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht';
$string['settings_assessment_verbose_options_default'] = 'nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht';
$string['settings_assessment_verbose_options_short'] = 'Verbalisierung Werte Abkürzung';
$string['settings_assessment_verbose_options_short_description'] = 'Abkürzung obiger verbalisierter Werte für die Auswertungen';
$string['settings_assessment_verbose_options_short_default'] = 'ne, te, üe, ve';
$string['settings_schoolname'] = 'Bezeichnung und Standort der Schule';
$string['settings_schoolname_description'] = '';
$string['settings_schoolname_default'] = 'Bezeichnung und Standort der Schule';
$string['settings_assessment_grade_verbose'] = 'Noten Verbalisierung';
$string['settings_assessment_grade_verbose_description'] = 'Verbalisierte Werte der Noten, kommagetrennt. Die Anzahl muß mit dem Wert "Höchste Note" oben übereinstimmen. z.B: sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend';
$string['settings_assessment_grade_verbose_default'] = 'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend';
$string['settings_assessment_grade_verbose_negative'] = 'Negative Beurteilung Verbalisierung';
$string['settings_assessment_grade_verbose_negative_description'] = 'Untergrenze (negative Beurteilung) der verbalisierten Beurteilung im Beurteilungs-Schema';
$string['use_grade_verbose_competenceprofile'] = 'Noten Verbalisierung Kompetenzprofil';
$string['use_grade_verbose_competenceprofile_descr'] = 'Noten Verbalisierung im Kompetenzprofil verwenden';
$string['settings_sourceId'] = 'Source ID';
$string['settings_sourceId_description'] = 'Automatisch generierte ID dieser Exacomp Installation. Diese kann nicht geändert werden';
$string['settings_admin_preconfiguration_none'] = 'Eigene Konfiguration';
$string['settings_default_de_value'] = 'DE value: ';
$string['settings_assessment_SelfEval_verboses'] = 'Werte für verbalisiertes Schüler/innen-Feedback';
$string['settings_assessment_SelfEval_verboses_long_columntitle'] = 'Lang';
$string['settings_assessment_SelfEval_verboses_short_columntitle'] = 'Kurz';
$string['settings_assessment_SelfEval_verboses_edit'] = 'Bearbeiten';
$string['settings_assessment_SelfEval_verboses_validate_error_long'] = 'Langformat: bis zu 4 Einträge, Trennzeichen Strichpunkt, max 20 Zeichen je Entrag (4 zum Kurzformat)';
$string['settings_addblock_to_newcourse'] = 'Block zu neuen Kursen automatisch hinzufügen';
$string['settings_addblock_to_newcourse_description'] = 'Der Block "Exabis Kompetenzraster" wird automatisch jedem neuen Kurs hinzugefügt. Die Position des Block hängt vom Moodle-Theme ab.';
$string['settings_addblock_to_newcourse_option_no'] = 'Nein';
$string['settings_addblock_to_newcourse_option_yes'] = 'Ja';
$string['settings_addblock_to_newcourse_option_left'] = 'to the Left region';
$string['settings_addblock_to_newcourse_option_right'] = 'to the Right region';
$string['settings_disable_js_assign_competencies'] = 'JS für Kompetenzraster-Übersicht deaktivieren.';
$string['settings_disable_js_assign_competencies_description'] = 'Bei langen Ladezeiten des Kompetenzrasters können zur Performance-Steigerung JS-Funktionen deaktiviert werden.';
$string['settings_disable_js_editactivities'] = 'JS für die Zuteilung von Moodle-Aktivitäten für Teilnehmer/innen deaktivieren';
$string['settings_disable_js_editactivities_description'] = 'Aktivieren, falls sich die Seite "Moodle-Aktivitäten zuteilen"  zu langsam aufbaut.';
$string['settings_example_autograding'] = 'übergeordnete Materialien automatische Beurteilung';
$string['settings_example_autograding_description'] = 'Wenn alle untergeordneten Aufgaben erledigt sind, soll das übergeordnete Material automatisch beurteilt werden.';
$string['settings_assessment_verbose_lowerisbetter'] = 'Niedriger Wert ist besser';
$string['settings_assessment_verbose_lowerisbetter_description'] = 'Je niedriger der Wert der Beurteilung umso besser.';

// === Unit Tests ===
$string['unittest_string'] = 'result_unittest_string';
$string['de:unittest_string2'] = 'result_unittest_string2';
$string['de:unittest_string3'] = 'unittest_string3';
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
$string['importinfo'] = 'Erstellen Sie Ihre eigenen Kompetenzraster auf <a target="_blank" href="https://comet.edustandards.org">wwww.edustandards.org</a>.';
$string['importwebservice'] = 'Es besteht auch die Möglichkeit die Daten über eine <a href="{$a}">Server-URL</a> aktuell zu halten.';
$string['import_max_execution_time'] = 'Wichtig: die aktuellen Servereinstellungen beschränken den Import auf {$a} Sekunden. Falls der Import-Vorgang länger dauert, wird dieser abgebrochen, es werden keine Daten importiert. Am Ausgabegerät wird in diesem Fall eine serverseitige Fehlermeldung ausgegeben (z.B. "500 Internal Server Error").';
$string['importdone'] = 'Die allgemeinen Kompetenzraster sind bereits importiert.';
$string['importpending'] = 'Bitte importieren Sie jetzt die allgemeinen Bildungsstandards und wählen Sie anschließend im Tab Bildungsstandard die anzuzeigenden Kompetenzbereiche aus.';
$string['doimport'] = 'Kompetenzraster importieren';
$string['doimport_again'] = 'Weitere Kompetenzraster importieren';
$string['doimport_own'] = 'Schulspezifische Bildungsstandards importieren';
$string['scheduler_import_settings'] = 'Settings for scheduler importing';
$string['delete_own'] = 'Schulspezifische Bildungsstandards löschen';
$string['delete_success'] = 'Schulspezifische Bildungsstandards wurden gelöscht';
$string['delete_own_confirm'] = 'Schulspezifische Bildungsstandards wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.';
$string['importsuccess'] = 'Daten erfolgreich importiert!';
$string['importsuccess_own'] = 'Eigene Daten erfolgreich importiert!';
$string['importfail'] = 'Es ist ein Fehler aufgetreten.';
$string['noxmlfile'] = 'Ein Import ist derzeit nicht möglich weil keine XML Datei vorhanden ist. Bitte hier die entsprechenden Standards downloaden und in das xml Verzeichnis des Blocks kopieren: <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Sie benutzen eine veraltete XML-Datei, bitte erstellen Sie sich eine neue Datei auf <a href="https://comet.edustandards.org">www.edustandards.org</a> oder laden Sie ein bestehendes XML von <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a> hoch.';
$string['do_demo_import'] = 'Importieren Sie einen Demodatensatz, um zu sehen wie Exabis Kompetenzraster funktioniert.';
$string['schedulerimport'] = 'Import von geplanten Aufgaben';
$string['add_new_importtask'] = 'Neue geplante Aufgabe hinzufügen';
$string['importtask_title'] = 'Title';
$string['importtask_link'] = 'Link to source';
$string['importtask_disabled'] = 'Disabled';
$string['importtask_all_subjects'] = 'Alle Bildungsstandard';
$string['dest_course'] = 'Ziel der importierten Aktivitäten';
$string['import_activities'] = 'Importieren Sie Aktivitäten vom Vorlagekurs in Ihren Kurs';
$string['download_activites'] = 'Download activities';

// === Configuration ===
$string['explainconfig'] = 'Um das Modul Exabis Kompetenzraster verwenden zu können, müssen hier die Kompetenzbereiche der Moodle-Instanz selektiert werden.';
$string['save_selection'] = 'Bestätigen';
$string['save_success'] = 'Änderungen erfolgreich übernommen';

// === Course-Configuration ===
$string['grading_scheme'] = 'Bewertungsschema';
$string['points_limit_forcourse'] = 'Höchste Punkteanzahl';
$string['uses_activities'] = 'Ich verwende Moodle Aktivitäten zur Beurteilung';
$string['show_all_descriptors'] = 'Alle Kompetenzen im Überblick anzeigen';
$string['useprofoundness'] = 'Grund- und Erweiterungskompetenzen verwenden';
$string['assessment_SelfEval_useVerbose'] = 'verbalisiertes Schüler/innen-Feedback';
$string['selfEvalVerbose.defaultValue_long'] = 'trifft nicht zu; trifft eher nicht zu; trifft eher zu; trifft zu';
$string['selfEvalVerbose.defaultValue_short'] = 'tn; ten; te; tz';
$string['selfEvalVerboseExample.defaultValue_long'] = 'nicht gelöst; mit Hilfe gelöst; selbstständig gelöst';
$string['selfEvalVerboseExample.defaultValue_short'] = 'ng; hg; sg';
$string['selfEvalVerbose.1'] = 'trifft nicht zu';
$string['selfEvalVerbose.2'] = 'trifft eher nicht zu';
$string['selfEvalVerbose.3'] = 'trifft eher zu';
$string['selfEvalVerbose.4'] = 'trifft zu';
$string['selfEvalVerboseExample.1'] = 'nicht gelöst';
$string['selfEvalVerboseExample.2'] = 'mit Hilfe gelöst';
$string['selfEvalVerboseExample.3'] = 'selbstständig gelöst';
$string['usetopicgrading'] = 'Beurteilung von Kompetenzbereichen ermöglichen';
$string['usesubjectgrading'] = 'Beurteilung von Fächern ermöglichen';
$string['usenumbering'] = 'Automatische Nummerierung im Kompetenzraster verwenden';
$string['usenostudents'] = 'Ohne Kursteilnehmer/innen arbeiten';
$string['usehideglobalsubjects'] = 'Überfachliche Kompetenzraster verbergen';
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
$string['example'] = 'Lernmaterial';
$string['sorting'] = 'Sortierung wählen: ';
$string['subject'] = 'Bildungsstandard';
$string['topic'] = 'Kompetenzbereich';
$string['taxonomies'] = 'Niveaustufen';
$string['show_all_course_examples'] = 'Lernmaterialien aus allen Kursen anzeigen';
$string['name_example'] = 'Name des Lernmaterials';
$string['timeframe_example'] = 'Zeitvorschlag';
$string['example_add_taxonomy'] = 'Neue Niveaustufe erstellen';
$string['comp_based'] = 'Nach Kompetenzen sortieren';
$string['examp_based'] = 'Nach Lernmaterialien sortieren';
$string['cross_based'] = 'für Themen';

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
$string['completefile'] = 'Gesamtbeispiel';
$string['hide_solution'] = 'Musterlösung verbergen';
$string['show_solution'] = 'Musterlösung anzeigen';
$string['hide_solution_disabled'] = 'Musterlösung ist bereits für alle Schüler/innen versteckt';
$string['submission'] = 'Abgabe';
$string['assignments'] = 'Moodle Aktivitäten';
$string['files'] = 'Dateien';
$string['link'] = 'Link';
$string['links'] = 'Links';
$string['dataerr'] = 'Es muss zumindest ein Link oder eine Datei hochgeladen werden!';
$string['linkerr'] = 'Bitte geben Sie einen korrekten Link ein!';
$string['isgraded'] = 'Die Aufgabe wurde bereits beurteilt und kann daher nicht mehr eingereicht werden.';
$string['allow_resubmission'] = 'Aufgabe zur erneuten Abgabe freigeben';
$string['allow_resubmission_info'] = 'Die Aufgabe wurde zur erneuten Abgabe freigegeben.';

// === Assign competencies ===
$string['header_edit_mode'] = 'Sie befinden sich im Bearbeitungsmodus';
$string['comp_-1'] = 'ohne Angabe';
$string['comp_0'] = 'nicht erreicht';
$string['comp_1'] = 'teilweise';
$string['comp_2'] = 'überwiegend';
$string['comp_3'] = 'vollständig';
$string['comp_-1_short'] = 'oA';
$string['comp_0_short'] = 'ne';
$string['comp_1_short'] = 'te';
$string['comp_2_short'] = 'üe';
$string['comp_3_short'] = 've';
$string['delete_confirmation'] = 'Soll "{$a}" wirklich gelöscht werden?';
$string['legend_activities'] = 'Moodle-Aktivitäten';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Keine Moodle-Aktivität/Quiz für diese Kompetenz abgegeben';
$string['legend_upload'] = 'Eigenes Lernmaterial hochladen';
$string['allniveaus'] = 'Alle Lernfortschritte';
$string['choosesubject'] = 'Kompetenzraster auswählen';
$string['choosetopic'] = 'Lernfortschritte auswählen';
$string['choosestudent'] = 'Kursteilnehmer/in auswählen: ';
$string['choose_student'] = 'Auswahl der Schüler/innen: ';
$string['choosedaterange'] = 'Betrachtungszeitraum auswählen: ';
$string['cleardaterange'] = 'Zurücksetzen';
$string['seperatordaterange'] = 'bis';
$string['own_additions'] = 'Schulische Ergänzung: ';
$string['delete_confirmation_descr'] = 'Soll die Kompetenz "{$a}" wirklich für alle Kurse gelöscht werden?';
$string['import_source'] = 'Importiert von: {$a}';
$string['local'] = 'Lokal';
$string['unknown_src'] = 'unbekannte Quelle';
$string['override_notice1'] = 'Dieser Eintrag wurde von ';
$string['override_notice2'] = ' bearbeitet. Wirklich ändern?';
$string['dismiss_gradingisold'] = 'Wollen Sie die Warnung ignorieren?';
$string['unload_notice'] = 'Die Seite wirklich verlassen? Ungespeicherte Änderungen gehen verloren.';
$string['example_sorting_notice'] = 'Bitte zuerst die aktuellen Bewertungen speichern';
$string['newsubmission'] = 'Erneute Abgabe';
$string['value_too_large'] = 'Fehler: Benotungen dürfen nicht größer als {limit} sein!';
$string['value_too_low'] = 'Fehler: Benotungen dürfen nicht kleiner als 1.0 sein!';
$string['value_not_allowed'] = 'Fehler: Benotungen müssen Zahlenwerte zwischen 1.0 und 6.0 sein';
$string['competence_locked'] = 'Beurteilung vorhanden oder Lernmaterial in Verwendung!';
$string['save_changes_competence_evaluation'] = 'Änderungen wurden gespeichert!';
// === Example Submission ===
$string['example_submission_header'] = 'Aufgabe {$a} bearbeiten';
$string['example_submission_info'] = 'Du bist dabei die Aufgabe "{$a}" zu bearbeiten. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.';
$string['topic_submission_info'] = 'Du bist dabei eine Abgabe zum Kompetenzbereich "{$a}" zu machen. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.';
$string['descriptor_submission_info'] = 'Du bist dabei eine Abgabe zur Kompetenz "{$a}" zu machen. Deine Abgabe landet im ePortfolio und kann dort von dir und deinem/r LehrerIn eingesehen werden.';
$string['example_submission_subject'] = 'Neue Abgabe';
$string['example_submission_message'] = 'Im Kurs {$a->course} wurde von Teilnehmer/in {$a->student} eine neue Abgabe eingereicht.';
$string['submissionmissing'] = 'Es müssen zumindest ein Link oder eine Datei abgegeben werden';
$string['associated_activities'] = 'Zugeordnete Moodle-Aktivitäten:';
$string['usernosubmission'] = 'Offene Moodle-Aktivitäten';
$string['grading'] = 'Bewertung';
$string['teacher_tipp'] = 'Tipp';
$string['teacher_tipp_1'] = 'Diese Kompetenz wurde bei ';
$string['teacher_tipp_2'] = ' Moodle-Aktivität(en) zugeordnet und bereits bei ';
$string['teacher_tipp_3'] = ' Moodle-Aktivität(en) in der Kompetenz-Detailansicht erfüllt.';
$string['print'] = 'Drucken';
$string['eportitems'] = 'Zu diesem Deskriptor eingereichte ePortfolio-Artefakte:';
$string['eportitem_shared'] = ' (geteilt)';
$string['eportitem_notshared'] = ' (nicht geteilt)';
$string['teachershortcut'] = 'L';
$string['studentshortcut'] = 'S';
$string['overview'] = 'Hier haben Sie einen Überblick über die Teilkompetenzen der ausgewählten Kompetenzen und die zugeordneten Aufgaben. Sie können das Erreichen der jeweiligen Teilkompetenz individuell bestätigen.';
$string['showevaluation'] = 'Um die Selbsteinschätzung einzusehen, klicken Sie <a href="{$a}">hier</a>';
$string['hideevaluation'] = 'Um die Selbsteinschätzung auszublenden, klicken Sie <a href="{$a}">hier</a>';
$string['showevaluation_student'] = 'Um die Einschätzung der TrainerInnen zu aktivieren, klicke <a href="{$a}">hier</a>.';
$string['hideevaluation_student'] = 'Um die Einschätzung der TrainerInnen zu deaktivieren, klicke <a href="{$a}">hier</a>.';
$string['columnselect'] = 'Spaltenauswahl';
$string['allstudents'] = 'Alle  Kursteilnehmer/innen';
$string['all_activities'] = 'Alle Aktivität/en';
$string['nostudents'] = 'Keine  Kursteilnehmer/innen';
$string['statistic'] = 'Gesamtübersicht';
$string['niveau'] = 'Lernfortschritt';
$string['niveau_short'] = 'LFS';
$string['competence_grid_niveau'] = 'Niveau';
$string['competence_grid_additionalinfo'] = 'Note';
$string['descriptor'] = 'Kompetenz';
$string['descriptor_child'] = 'Teilkompetenz';
$string['assigndone'] = 'Aufgabe erledigt: ';
$string['descriptor_categories'] = 'Niveaustufen bearbeiten: ';
$string['descriptor_add_category'] = 'Neue Niveaustufe hinzufügen: ';
$string['descriptor_categories_description'] = 'Wählen Sie hier Niveaustufe(n) für diese Kompetenz/dieses Lernmaterial aus. Sie können auch eine neue Niveaustufe hinzufügen oder dieses Feld freilassen.';

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
				gekennzeichnet werden. Keinenfalls müssen die Schüler/innen
				alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Schüler/innen
				müssen dann keine zugehörigen Lernmaterialien
				bearbeiten.';

// === Activities ===
$string['explaineditactivities_subjects'] = 'Hier können Sie den erstellten Aufgaben Kompetenzen zuordnen.';
$string['niveau_filter'] = 'Niveaus filtern';
$string['module_filter'] = 'Aktivitäten filtern';
$string['apply_filter'] = 'Filter anwenden';
$string['no_topics_selected'] = 'Konfiguration für Exabis Kompetenzraster wurde noch nicht abgeschlossen. Bitte wählen Sie zuerst Gegenstände aus, denen Sie dann Moodle-Aktivitäten zuordnen können.';
$string['no_activities_selected'] = 'Bitte ordnen Sie den erstellten Moodle-Aktivitäten Kompetenzen zu.';
$string['no_activities_selected_student'] = 'In diesem Bereich sind derzeit keine Daten vorhanden.';
$string['no_course_activities'] = 'In diesem Kurs wurden noch keine Moodle-Aktivitäten erstellt, klicken Sie hier um dies nun zu tun.';
$string['all_modules'] = 'Alle Aktivitäten';
$string['tick_some'] = 'Bitte treffen Sie eine Auswahl!';

// === Competence Grid ===
$string['infolink'] = 'Weiter Informationen: ';
$string['textalign'] = 'Textuelle Ausrichtung ändern';
$string['selfevaluation'] = 'Selbsteinschätzung';
$string['selfevaluation_short'] = 'SE';
$string['teacherevaluation_short'] = 'TE';
$string['teacherevaluation'] = 'Einschätzung des Beurteilenden';
$string['competencegrid_nodata'] = 'Sollte der Kompetenzraster leer sein, wurden für die Deskriptoren des ausgewählten Gegenstands keine Niveaus in den Daten definiert';
$string['statistic_type_descriptor'] = 'Wechsel zur Statistik der Teilkompetenzen';
$string['statistic_type_example'] = 'Wechsel zur Statistik der Aufgaben';
$string['reports'] = 'Berichte';
$string['newer_grading_tooltip'] = 'Überprüfen Sie die Beurteilung, da weitere Teilkompetenzen geändert wurden.';
$string['create_new_topic'] = 'Neuer Kompetenzbereich';
$string['create_new_area'] = 'Neuer Bereich';
$string['really_delete'] = 'Wirklich löschen?';
$string['add_niveau'] = 'Neuen Lernfortschritt hinzufügen';
$string['please_choose'] = 'Bitte wählen';
$string['please_choose_preselection'] = 'Bitte wählen sie die Raster von denen Sie etwas löschen wollen.';
$string['delete_niveau'] = 'Löschen hinzufügen';
$string['add_new_taxonomie'] = 'neue Niveaustufe hinzufügen';
$string['taxonomy_was_deleted'] = 'Niveaustufe was deleted';
$string['move_up'] = 'Move up';
$string['move_down'] = 'Move down';
$string['also_taxonomies_from_import'] = 'Niveaustufen aus Importen anzeigen';

// === Competence Profile ===
$string['name'] = 'Name';
$string['city'] = 'Wohnort';
$string['total'] = 'Gesamt';
$string['select_student'] = 'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Kompetenzprofil Sie sehen möchten.';
$string['my_comps'] = 'Meine Kompetenzen';
$string['my_badges'] = 'Meine Auszeichnungen';
$string['innersection1'] = 'Rasterübersicht';
$string['innersection2'] = 'Statistik';
$string['innersection3'] = 'Übersicht über die Kompetenzen und Aufgaben';
$string['childcompetencies_compProfile'] = 'Teilkompetenzen';
$string['materials_compProfile'] = 'Lernmaterialien';

// === Competence Profile Settings ===
$string['profile_settings_choose_courses'] = 'In Exabis Kompetenzraster beurteilen TrainerInnen den Kompetenzerwerb in unterschiedlichen Fachgebieten. Hier kann ausgewählt werden, welche Kurse im Kompetenzprofil aufscheinen sollen.';
$string['specificcontent'] = 'Schulbezogene Themenbereiche';
$string['topic_3dchart'] = '3D Diagramm';
$string['topic_3dchart_empty'] = 'Es liegen keine Beurteilungen für diesen Kompetenzbereich vor.';
// === Profoundness ===
$string['profoundness_description'] = 'Kompetenzbeschreibung';
$string['profoundness_basic'] = 'Grundkompetenz';
$string['profoundness_extended'] = 'Erweiterte Kompetenz';
$string['profoundness_mainly'] = 'Überwiegend erfüllt';
$string['profoundness_entirely'] = 'Zur Gänze erfüllt';

// === External trainer & eLove ===
$string['block_exacomp_external_trainer_assign_head'] = 'Zuteilung von externen Trainer/innen für Kursteilnehmer/innen ermöglichen';
$string['block_exacomp_external_trainer_assign_body'] = 'Erforderlich für die Benutzung der elove App';
$string['block_exacomp_dakora_language_file_head'] = 'Alternative Sprachdatei für DAKORA';
$string['block_exacomp_dakora_language_file_body'] = 'Verwenden Sie den <a href="https://exabis.at/sprachgenerator" target="_blank">Sprachengenerator</a> um eigene Sprachdateien zu erstellen';
$string['settings_dakora_timeout'] = 'Dakora Timeout (Sekunden)';
$string['settings_dakora_timeout_description'] = '';
$string['settings_dakora_url'] = 'Url zur Dakora-App';
$string['settings_dakora_url_description'] = '';
$string['settings_dakora_show_overview'] = 'Überblick anzeigen';
$string['settings_dakora_show_overview_description'] = '';
$string['settings_dakora_show_eportfolio'] = 'ePortfolio anzeigen';
$string['settings_dakora_show_eportfolio_description'] = '';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Selbsteinschätzung für Kursteilnehmer/innen in der elove App erlauben';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Externe TrainerIn zuordnen';
$string['block_exacomp_external_trainer'] = 'AusbilderIn: ';
$string['block_exacomp_external_trainer_student'] = 'Auszubildende: ';
$string['block_exacomp_external_trainer_allstudents'] = 'Alle Kursteilnehmer/innen';

// === Crosssubjects ===
$string['add_drafts_to_course'] = 'Ausgewählte Vorlagen im Kurs verwenden';
$string['crosssubject'] = 'Thema';
$string['help_crosssubject'] = 'Die Zusammenstellung des Themas erfolgt für die ganze Schule über den Reiter Kompetenzraster. Sie können hier kursspezifisch Kompetenzen, Teilkompetenzen und Lernmaterial ausblenden. Lernmaterial kann hier ergänzt werden.';
$string['description'] = 'Beschreibung';
$string['numb'] = 'Nummer';
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
$string['available_crosssubjects'] = 'Nicht freigegebene Kursthemen';
$string['crosssubject_drafts'] = 'Themenvorlagen';
$string['de:Freigegebene Kursthemen'] = 'Freigegebene Kursthemen';
$string['de:Freigabe bearbeiten'] = 'Freigabe bearbeiten';
$string['de:Kopie als Vorlage speichern'] = 'Kopie als Vorlage speichern';
$string['de:Vorlage verwenden'] = '';
$string['crosssubject_files'] = 'Materialien';
$string['new_niveau'] = 'neuer Lernfortschritt';
$string['groupcategory'] = 'Kategorie';
$string['new_column'] = 'neue Spalte';
$string['new_topic'] = 'neuer Kompetenzbereich';

// === Associations ===
$string['competence_associations'] = 'Verknüpfungen';
$string['competence_associations_explaination'] = 'Das Lernmaterial {$a} ist mit den folgenden Kompetenzen verknüpft:';

// === Weeky schedule ===
$string['weekly_schedule'] = 'Wochenplan';
$string['weekly_schedule_added'] = 'Die Aufgabe wurde in den Planungsspeicher im Wochenplan hinzugefügt.';
$string['weekly_schedule_already_exists'] = 'Die Aufgabe ist bereits im Planungsspeicher im Wochenplan.';
$string['select_student_weekly_schedule'] = 'Wählen Sie eine(n) Kursteilnehmer/in aus, dessen Wochenplan Sie sehen möchten.';
$string['example_pool'] = 'Planungsspeicher';
$string['example_pool_example_button'] = 'in den Planungsspeicher {$a->fullname}';
$string['example_pool_example_button_forall'] = 'in den Planungsspeicher aller Kursteilnehmer/innen';
$string['example_trash'] = 'Papierkorb';
$string['choosecourse'] = 'Kurs auswählen: ';
$string['choosecoursetemplate'] = 'Bitte wählen Sie den Kurs, in den die Moodle Aktivitäten des Kompetenzrasters importiert werden: ';
$string['weekly_schedule_added_all'] = 'Die Aufgabe wurde bei allen Kursteilnehmer/innen auf den Planungsspeicher im Wochenplan gelegt.';
$string['weekly_schedule_already_existing_for_one'] = 'Die Aufgabe ist bei mindestens einem/r Schüler/in bereits im Planungsspeicher im Wochenplan.';
$string['weekly_schedule_link_to_grid'] = 'Um den Planungsspeicher zu befüllen, wechseln Sie bitte in das Register Kompetenzraster.';
$string['pre_planning_storage'] = 'Planungsspeicher';
$string['pre_planning_storage_popup_button'] = 'Material verteilen';
$string['pre_planning_storage_example_button'] = 'in meinen Planungsspeicher';
$string['pre_planning_storage_added'] = 'Lernmaterial wurde zum Planungsspeicher hinzugefügt.';
$string['pre_planning_storage_already_contains'] = 'Lernmateriel bereits im Planungsspeicher enthalten.';
$string['save_pre_planning_selection'] = 'Ausgewählte Lernmaterialien auf den Wochenplan der ausgewählten Schüler/innen legen';
$string['empty_pre_planning_storage'] = 'Planungsspeicher leeren';
$string['noschedules_pre_planning_storage'] = 'Der Planungsspeicher ist leer. Bitte legen Sie über die Kompetenzraster neue Lernmaterialien in den Planungsspeicher.';
$string['empty_trash'] = 'Papierkorb leeren';
$string['empty_pre_planning_confirm'] = 'Auch Beispiele, die ein anderer Lehrer zum Planungsspeicher hinzugefügt hat, werden entfernt. Sind Sie sicher?';
$string['to_weekly_schedule'] = 'Zum Wochenplan';
$string['blocking_event'] = 'Sperrelement erstellen';
$string['blocking_event_title'] = 'Titel';
$string['blocking_event_create'] = 'Zum Planungsspeicher hinzufügen';
$string['weekly_schedule_disabled'] = 'Lernmaterial ist versteckt und kann nicht auf Wochenplan gelegt werden.';
$string['pre_planning_storage_disabled'] = 'Lernmaterial ist versteckt und kann nicht in den Planungsspeicher gelegt werden.';
$string['add_example_for_all_students_to_schedule'] = 'Achtung: Sie sind dabei Lernmaterialien für alle Schüler/innen auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.';
$string['add_example_for_group_to_schedule'] = 'Achtung: Sie sind dabei Lernmaterialien für die ausgewählte Gruppe auf deren Wochenplan zu legen. Dafür ist eine zusätzliche Bestätigung notwendig. Etwaige Änderungen können danach nur mehr auf den individuellen Plänen der jeweiligen Schüler vorgenommen werden.';
$string['add_example_for_all_students_to_schedule_confirmation'] = 'Sind Sie sicher, dass Sie die Lernmaterialien für alle Schüler/innen auf den Wochenplan legen möchten?';
$string['delete_ics_imports_confirmation'] = 'Sind Sie sicher, dass Sie die die von Ihnen importierten Termine für den ausgewählten Wochenplan entfernen möchten?';
$string['import_ics_loading_time'] = 'Importieren gestartet.';
$string['ics_provide_link_text'] = 'Bitte geben Sie einen Link an.';
$string['add_example_for_group_to_schedule_confirmation'] = 'Sind Sie sicher, dass Sie die Lernmaterialien für die ausgewählte Gruppe auf deren Wochenplan legen möchten?';
$string['participating_student'] = 'Kursteilnehmer/in';
$string['n1.unit'] = '1. Einheit:';
$string['n2.unit'] = '2. Einheit:';
$string['n3.unit'] = '3. Einheit:';
$string['n4.unit'] = '4. Einheit:';
$string['n5.unit'] = '5. Einheit:';
$string['n6.unit'] = '6. Einheit:';
$string['n7.unit'] = '7. Einheit:';
$string['n8.unit'] = '8. Einheit:';
$string['n9.unit'] = '9. Einheit:';
$string['n10.unit'] = '10. Einheit:';

// === Notifications ===
$string['notification_submission_subject'] = '{$a->site}: {$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht';
$string['notification_submission_subject_noSiteName'] = '{$a->student} hat eine Lösung zum Lernmaterial {$a->example} eingereicht';
$string['notification_submission_body'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href={$a->viewurl}{$a->example}</a> </br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_submission_body_noSiteName'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat die Aufgabe {$a->example} bearbeitet und am {$a->date} um {$a->time} hochgeladen. Die Abgabe kann im ePortfolio eingesehen werden: <a href={$a->viewurl}{$a->example}</a> </br></br>';
$string['notification_submission_context'] = 'Abgabe';
$string['notification_grading_subject'] = '{$a->site}: Neue Beurteilungen im Kurs {$a->course}';
$string['notification_grading_subject_noSiteName'] = 'Neue Beurteilungen im Kurs {$a->course}';
$string['notification_grading_body'] = 'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_grading_body_noSiteName'] = 'Lieber/Liebe {$a->receiver}, </br></br> Du hast im Kurs {$a->course} neue Beurteilungen von {$a->teacher} erhalten.</br></br>';
$string['notification_grading_context'] = 'Beurteilung';
$string['notification_self_assessment_subject'] = '{$a->site}: Neue Selbsteinschätzung im Kurs {$a->course}';
$string['notification_self_assessment_body'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_self_assessment_subject_noSiteName'] = 'Neue Selbsteinschätzung im Kurs {$a->course}';
$string['notification_self_assessment_body_noSiteName'] = 'Sehr geehrter/geehrte {$a->receiver}, </br></br> {$a->student} hat im Kurs {$a->course} neue Selbsteinschätzungen gemacht.</br></br>.';
$string['notification_self_assessment_context'] = 'Selbsteinschätzung';
$string['notification_example_comment_subject'] = '{$a->site}: Neuer Kommentar bei Aufgabe {$a->example}';
$string['notification_example_comment_body'] = 'Lieber/Liebe {$a->receiver}, </br></br> {$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_example_comment_subject_noSiteName'] = 'Neuer Kommentar bei Aufgabe {$a->example}';
$string['notification_example_comment_body_noSiteName'] = 'Lieber/Liebe {$a->receiver}, </br></br> {$a->teacher} hat im Kurs {$a->course} die Aufgabe {$a->example} kommentiert.</br></br>';
$string['notification_example_comment_context'] = 'Kommentar';
$string['notification_weekly_schedule_subject'] = '{$a->site}: Neue Aufgabe am Wochenplan';
$string['notification_weekly_schedule_subject_noSiteName'] = 'Neue Aufgabe am Wochenplan';
$string['notification_weekly_schedule_body'] = 'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br> Die Nachricht wurde generiert von der Moodle-Seite {$a->site}.';
$string['notification_weekly_schedule_body_noSiteName'] = 'Lieber/Liebe {$a->receiver}, </br></br>{$a->teacher} hat dir im Kurs {$a->course} eine neue Aufgabe auf den Wochenplan gelegt.</br></br>';
$string['notification_weekly_schedule_context'] = 'Wochenplan';
$string['inwork'] = '{$a->inWork}/{$a->total} Materialien in Arbeit';
$string['block_exacomp_notifications_head'] = 'Benachrichtigungen aktivieren';
$string['block_exacomp_notifications_body'] = 'Bei Aktionen wie einer Lernmaterialien-Einreichung oder einer Beurteilung werden Nachrichten an die zuständigen Benutzer gesendet.';
$string['block_exacomp_assign_activities_old_method_head'] = 'Tab Moodle-Aktivitäten zuordnen alt anzeigen';
$string['block_exacomp_assign_activities_old_method_body'] = 'Diese Funktionalität wird über den neuen Tab "Moodle-Aktivitäten verknüpfen" abgedeckt.';
$string['block_exacomp_disable_create_grid_head'] = '"Neues Kompetenzraster anlegen" deaktivieren';
$string['block_exacomp_disable_create_grid_body'] = 'The users will not be able to create new grids';
$string['distribute_weekly_schedule'] = 'Wochenplan verteilen';

// === Logging ===
$string['block_exacomp_logging_head'] = 'Logging aktivieren';
$string['block_exacomp_logging_body'] = 'Relevante Aktionen werden geloggt.';
$string['eventscompetenceassigned'] = 'Kompetenz zugeteilt';
$string['eventsexamplesubmitted'] = 'Aufgabe abgegeben';
$string['eventsexamplegraded'] = 'Aufgabe beurteilt';
$string['eventsexamplecommented'] = 'Aufgabe kommentiert';
$string['eventsexampleadded'] = 'Aufgabe zu Wochenplan hinzugefügt';
$string['eventsimportcompleted'] = 'Import durchgeführt';
$string['eventscrosssubjectadded'] = 'Thema freigegeben';

// === Message ===
$string['messagetocourse'] = 'Nachricht an alle Kursteilnehmer/innen senden';
$string['messageprovider:submission'] = 'Nachricht bei neuer Schülerabgabe';
$string['messageprovider:grading'] = 'Nachricht an bei neuer Note';
$string['messageprovider:self_assessment'] = 'Nachricht bei neuer Selbstbewertung';
$string['messageprovider:weekly_schedule'] = 'Lehrer/in fügt ein Beispiel in den Wochenplan ein';
$string['messageprovider:comment'] = 'Lehrer/in kommentiert ein Beispiel';
$string['description_example'] = 'Beschreibung / Schulbuchverweis';
$string['submit_example'] = 'Abgeben';
// === Webservice Status ===
$string['enable_rest'] = 'REST Protokoll nicht aktiviert';
$string['access_roles'] = 'Benutzerrollen mit Zugriff auf Webservices';
$string['no_permission'] = 'Berechtigung wurde nicht erteilt';
$string['no_permission_user'] = 'Berechtigung wurde für Authentifizierte/r Nutzer/in nicht erteilt';
$string['description_createtoken'] = 'Der Benutzerrolle "Authentifizierte/r Nutzer/in" zusätzliche Rechte erteilen: Website-Administration/Nutzer_innen/Rechte ändern/Rollen verwalten
4.1 Authentifizierte/r Nutzer/in wählen
4.2 Bearbeiten auswählen
4.3 Nach "createtoken" filtern
4.4 Moodle/webservice:createtoken erlauben';
$string['exacomp_not_found'] = 'Exacompservice nicht gefunden';
$string['exaport_not_found'] = 'Exaportservice nicht gefunden';
$string['no_external_trainer'] = 'Keine externen Trainer zugeteilt';
$string['periodselect'] = 'Auswahl des Eingabezeitraums';
$string['teacher'] = 'Lehrer';
$string['student'] = 'Schüler/in';
$string['timeline_available'] = 'Verfügbare';
// === Group Reports ===
$string['result'] = 'Ergebnis';
$string['evaluationdate'] = 'Bewertungsdatum';
$string['output_current_assessments'] = 'Ausgabe der jeweiligen Bewertungen';
$string['student_assessment'] = 'Selbsteinschätzung';
$string['teacher_assessment'] = 'Rückmeldung Lehrkraft';
$string['exa_evaluation'] = 'Lernmaterial Bewertung';
$string['difficulty_group_report'] = 'Niveau';
$string['no_entries_found'] = 'Keine Einträge gefunden';
$string['assessment_date'] = 'Bewertungsdatum';
$string['number_of_found_students'] = 'Anzahl gefundener Schüler';
$string['display_settings'] = 'Anzeigeoptionen';
$string['settings_explanation_tooltipp'] = 'Die Ergebnisse im Bericht werden durch die einzelnen Filter von
        oben nach unten reduziert, aber nicht von unten nach oben.
        Wenn z.B. als einziges Filterkriterium "Niveau G" bei den Kompetenzen
        ausgewählt ist, so werden
        - alle Bildungsstandards
        - alle Kompetenzbereiche
        - Kompetenzen gefiltert nach Beurteilung mit "Niveau G" und
        - Teilkompetenzen, die Kompetenzen Niveau G zugeordnet sind, angezeigt.';
$string['create_report'] = 'Bericht erstellen';
$string['students_competences'] = 'Schüler Kompetenzen';
$string['number_of_students'] = 'Schüler Anzahl';
$string['no_specification'] = 'noch keine Beurteilung';
$string['period'] = 'Zeitintervall';
$string['from'] = 'von';
$string['to'] = 'bis';
$string['report_type'] = 'Berichtsart';
$string['report_subject'] = 'Bildungsstandard/Raster';
$string['report_learniningmaterial'] = 'Lernmaterial';
$string['report_competencefield'] = 'Kompetenzbereich';
$string['all_students'] = 'Alle Schüler';
$string['export_all_standards'] = 'Alle Kompetenzraster dieser Moodle Instanz exportieren';
$string['exportieren'] = 'Exportieren';
$string['export_selective'] = 'Selektiver Export';
$string['select_all'] = 'alle wählen';
$string['deselect_all'] = 'alle abwählen';
$string['new'] = 'neu';
$string['import_used_preselected_from_previous'] = 'Falls eine XML-Datei bereits zuvor importiert worden ist, werden dieselben Voreinstellungen der Datenquelle verwendet';
$string['import_from_related_komet'] = 'Kompetenzraster aus zugehörigem KOMET jetzt importieren/aktualisieren';
$string['import_from_related_komet_help'] = 'Wenn die automatische Aktualisierung der Kompetenzraster über KOMET in den allgemeinen Einstellungen aktiviert ist, kann über diese Option diese Aktualisierung sofort durchgeführt werden.<br>
        Die automatische Aktualisierung erfolgt über Website-Administration - Plugins - Blöcke - Exabis Kompetenzraster: Server-URL';
$string['import_activate_scheduled_tasks'] = 'Aufgaben aktivieren';

// === API ====
$string['yes_no_No'] = 'Nein';
$string['yes_no_Yes'] = 'Ja';
$string['grade_Verygood'] = 'sehr gut';
$string['grade_good'] = 'gut';
$string['grade_Satisfactory'] = 'befriedigend';
$string['grade_Sufficient'] = 'ausreichend';
$string['grade_Deficient'] = 'mangelhaft';
$string['grade_Insufficient'] = 'ungenügend';
$string['import_select_file'] = 'Datei aussuchen:';
$string['import_selectgrids_needed'] = 'Auswahl der Gegenstände für den Import:';
$string['import_category_mapping_needed'] = 'Das importierte Kompetenzraster enthält ein anderes Niveaukonzept als an Ihrer Schule. Die entsprechenden Niveaueintragungen werden gelöscht. Sie können diese nachträglich selbst editieren.';
$string['import_category_mapping_column_xml'] = 'Niveau';
$string['import_category_mapping_column_exacomp'] = 'wird geändert in';
$string['import_category_mapping_column_level'] = 'Niveau';
$string['import_category_mapping_column_level_descriptor'] = 'Kompetenz / Teilkompetenz';
$string['import_category_mapping_column_level_example'] = 'Material';
$string['import_mapping_as_is'] = 'weiterhin so verwenden';
$string['import_mapping_delete'] = 'Delete';
$string['save'] = 'Speichern';
$string['add_competence_insert_learning_progress'] = 'Um eine Kompetenz einfügen zu können, müssen Sie zuerst einen Lernfortschritt auswählen oder hinzufügen!';
$string['delete_level_from_another_source'] = 'Importierter Kompetenzraster hat Inhalte einer anderen Quelle. Wenn hier gelöscht wird, wird auch von der anderen Quelle gelöscht! Nur löschen wenn Sie sicher sind!';
$string['delete_level_has_children_from_another_source'] = 'Importierter Kompetenzraster wurde in dieser Installation weiterbearbeitet. Diese Ergänzungen sollten vor dem Löschen ausgewiesen werden. Ansonsten werden potenziell auch die Inhalte anderer Raster gelöscht wenn sie diesen Raster löschen! ';
$string['delete_competency_that_has_gradings'] = 'Diese Kompetenz hat bereits Beurteilungen! Nur löschen wenn Sie sicher sind!';
$string['delete_competency_that_has_children_with_gradings'] = 'Darunterliegende Kompetenzen haben bereits Beurteilungen! Nur löschen wenn Sie sicher sind!';
$string['delete_competency_that_is_used_in_course'] = 'Achtung! Dieser Raster ist in folgenden Kursen in Verwendung: ';
$string['module_used_availabilitycondition_competences'] = 'Verknüpfte Exabis Kompetenzen automatisch erreichen, wenn die Bedingungen erfüllt sind.';
$string['use_isglobal'] = 'Überfachlicher Kurs';
$string['globalgradings'] = 'Überfachliche Bewertungen';
$string['assign_dakora_teacher'] = 'Lehrkraft für überfachliche Kompetenzen zuweisen';
$string['assign_dakora_teacher_link'] = 'Hier klicken um Lehrkraft für überfachliche Kompetenzen zuzuweisen';
$string['transferable_skills'] = 'Überfachliche Kompetenzen';

//Dakora strings
$string['dakora_string1'] = 'deutscher string1';
$string['dakora_string2'] = 'deutscher string2';
$string['dakora_string3'] = 'deutscher string3';
$string['dakora_niveau_after_descriptor_title'] = 'Niveau';
$string['active_show'] = 'aktiv (anzeigen)';
$string['donotleave_page_message'] = 'You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?';
$string['privacy:metadata:block_exacompcompuser'] = 'Storage for student evaluations';
$string['privacy:metadata:block_exacompcompuser:userid'] = 'Student who was evaluated';
$string['privacy:metadata:block_exacompcompuser:compid'] = 'Competence which was evaluated';
$string['privacy:metadata:block_exacompcompuser:reviewerid'] = 'Reviewer who evaluated';
$string['privacy:metadata:block_exacompcompuser:role'] = 'Role of reviewer who evaluated';
$string['privacy:metadata:block_exacompcompuser:courseid'] = 'Course';
$string['privacy:metadata:block_exacompcompuser:value'] = 'Result of evaluation';
$string['privacy:metadata:block_exacompcompuser:comptype'] = 'Type of evaluated competence';
$string['privacy:metadata:block_exacompcompuser:timestamp'] = 'Date of evaluation';
$string['privacy:metadata:block_exacompcompuser:additionalinfo'] = 'Result of evaluation';
$string['privacy:metadata:block_exacompcompuser:evalniveauid'] = 'Difficulty level of evaluation';
$string['privacy:metadata:block_exacompcompuser:gradingisold'] = 'is it old?';
$string['privacy:metadata:block_exacompcompuser:globalgradings'] = 'global value';
$string['privacy:metadata:block_exacompcompuser:gradinghistory'] = 'history of grading';
$string['privacy:metadata:block_exacompcompuser:personalisedtext'] = 'additional personalised text';
$string['privacy:metadata:block_exacompcmassign'] = 'Storage for auto grading mechanism: does not need to be exported';
$string['privacy:metadata:block_exacompcmassign:coursemoduleid'] = 'Course module id';
$string['privacy:metadata:block_exacompcmassign:userid'] = 'Student ids';
$string['privacy:metadata:block_exacompcmassign:timemodified'] = 'timestamp';
$string['privacy:metadata:block_exacompcmassign:relateddata'] = 'Data, related to the student';
$string['privacy:metadata:block_exacompexameval'] = 'Storage for student evaluations (examples)';
$string['privacy:metadata:block_exacompexameval:exampleid'] = 'Example';
$string['privacy:metadata:block_exacompexameval:courseid'] = 'Course';
$string['privacy:metadata:block_exacompexameval:studentid'] = 'Student who was evaluated';
$string['privacy:metadata:block_exacompexameval:teacher_evaluation'] = 'Evaluation value from teacher';
$string['privacy:metadata:block_exacompexameval:additionalinfo'] = 'Evaluation value from teacher (used for some types of assessment)';
$string['privacy:metadata:block_exacompexameval:teacher_reviewerid'] = 'Teacher who evaluated';
$string['privacy:metadata:block_exacompexameval:timestamp_teacher'] = 'Time of teacher evaluation';
$string['privacy:metadata:block_exacompexameval:student_evaluation'] = 'Self evaluation';
$string['privacy:metadata:block_exacompexameval:timestamp_student'] = 'Time of self evaluation';
$string['privacy:metadata:block_exacompexameval:evalniveauid'] = 'Niveau';
$string['privacy:metadata:block_exacompexameval:resubmission'] = 'resubmission is allowed/not allowed';
$string['privacy:metadata:block_exacompcrossstud_mm'] = 'Share crossubjects to the students';
$string['privacy:metadata:block_exacompcrossstud_mm:crosssubjid'] = 'Crossubject id';
$string['privacy:metadata:block_exacompcrossstud_mm:studentid'] = 'Student';
$string['privacy:metadata:block_exacompdescrvisibility'] = 'Visibility descriptors for users';
$string['privacy:metadata:block_exacompdescrvisibility:courseid'] = 'Course id';
$string['privacy:metadata:block_exacompdescrvisibility:descrid'] = 'Competence id';
$string['privacy:metadata:block_exacompdescrvisibility:studentid'] = 'Student';
$string['privacy:metadata:block_exacompdescrvisibility:visible'] = 'Visible marker';
$string['privacy:metadata:block_exacompexampvisibility'] = 'Visibility examples for users';
$string['privacy:metadata:block_exacompexampvisibility:courseid'] = 'Course id';
$string['privacy:metadata:block_exacompexampvisibility:exampleid'] = 'Material id';
$string['privacy:metadata:block_exacompexampvisibility:studentid'] = 'Student';
$string['privacy:metadata:block_exacompexampvisibility:visible'] = 'Visible marker';
$string['privacy:metadata:block_exacompexternaltrainer'] = 'External trainers for students';
$string['privacy:metadata:block_exacompexternaltrainer:trainerid'] = 'Trainer';
$string['privacy:metadata:block_exacompexternaltrainer:studentid'] = 'Student';
$string['privacy:metadata:block_exacompprofilesettings'] = 'which course to include in the competence profile';
$string['privacy:metadata:block_exacompprofilesettings:itemid'] = 'Course id';
$string['privacy:metadata:block_exacompprofilesettings:userid'] = 'Student';
$string['privacy:metadata:block_exacompprofilesettings:block'] = 'associated block: exacomp, exastud or exaport';
$string['privacy:metadata:block_exacompprofilesettings:feedback'] = 'verbal feedback should be displayed (for exastud reviews)';
$string['privacy:metadata:block_exacompschedule'] = 'examples, added to student\'s schedule list';
$string['privacy:metadata:block_exacompschedule:studentid'] = 'Student';
$string['privacy:metadata:block_exacompschedule:exampleid'] = 'Scheduled example';
$string['privacy:metadata:block_exacompschedule:creatorid'] = 'Creator of scheduled record';
$string['privacy:metadata:block_exacompschedule:timecreated'] = 'Time of creating record';
$string['privacy:metadata:block_exacompschedule:timemodified'] = 'Time of editing record';
$string['privacy:metadata:block_exacompschedule:courseid'] = 'Course';
$string['privacy:metadata:block_exacompschedule:sorting'] = 'Sorting of records';
$string['privacy:metadata:block_exacompschedule:start'] = 'Start time';
$string['privacy:metadata:block_exacompschedule:endtime'] = 'End time';
$string['privacy:metadata:block_exacompschedule:deleted'] = 'Marker of deleted record';
$string['privacy:metadata:block_exacompschedule:distributionid'] = 'distribution id';
$string['privacy:metadata:block_exacompschedule:source'] = 'S/T as a type';
$string['privacy:metadata:block_exacompsolutvisibility'] = 'which examplesolutions are visible';
$string['privacy:metadata:block_exacompsolutvisibility:courseid'] = 'Course id';
$string['privacy:metadata:block_exacompsolutvisibility:exampleid'] = 'Example id';
$string['privacy:metadata:block_exacompsolutvisibility:studentid'] = 'Student';
$string['privacy:metadata:block_exacompsolutvisibility:visible'] = 'visible marker';
$string['privacy:metadata:block_exacomptopicvisibility'] = 'which topics are visible';
$string['privacy:metadata:block_exacomptopicvisibility:courseid'] = 'Course id';
$string['privacy:metadata:block_exacomptopicvisibility:topicid'] = 'Topic id';
$string['privacy:metadata:block_exacomptopicvisibility:studentid'] = 'Student';
$string['privacy:metadata:block_exacomptopicvisibility:visible'] = 'visible marker';
$string['privacy:metadata:block_exacomptopicvisibility:niveauid'] = 'Niveau id';
$string['privacy:metadata:block_exacompcrosssubjects'] = 'Cross subjects, created by the user';
$string['privacy:metadata:block_exacompcrosssubjects:title'] = 'Title';
$string['privacy:metadata:block_exacompcrosssubjects:description'] = 'Description';
$string['privacy:metadata:block_exacompcrosssubjects:courseid'] = 'Course id';
$string['privacy:metadata:block_exacompcrosssubjects:creatorid'] = 'creator id';
$string['privacy:metadata:block_exacompcrosssubjects:shared'] = 'shared or not';
$string['privacy:metadata:block_exacompcrosssubjects:subjectid'] = 'related subject id';
$string['privacy:metadata:block_exacompcrosssubjects:groupcategory'] = 'group category';
$string['privacy:metadata:block_exacompglobalgradings'] = 'Global grade text for a subject/topic/competence';
$string['privacy:metadata:block_exacompglobalgradings:userid'] = 'Student id';
$string['privacy:metadata:block_exacompglobalgradings:compid'] = 'competence id';
$string['privacy:metadata:block_exacompglobalgradings:comptype'] = 'competence type: 0 - descriptor; 1 - topic';
$string['privacy:metadata:block_exacompglobalgradings:globalgradings'] = 'content of global grading';
$string['privacy:metadata:block_exacompwsdata'] = 'temporary data for webservices';
$string['privacy:metadata:block_exacompwsdata:token'] = 'token value';
$string['privacy:metadata:block_exacompwsdata:userid'] = 'User';
$string['privacy:metadata:block_exacompwsdata:data'] = 'data content';
$string['OR'] = 'ODER';
$string['AND'] = 'UND';
$string['AND teacherevaluation from'] = 'UND Lehrerbeurteilung von';
$string['report all educational standards'] = 'Alle Bildungsstandard/Raster welche folgenden Filterkriterien entsprechen: ';
$string['report all topics'] = 'Alle Kompetenzbereiche von Bildungsstandard/Rastern die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ';
$string['report all descriptor parents'] = 'Alle Kompetenzen von Kompetenzbereichen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ';
$string['report all descriptor children'] = 'Alle Teilkompetenzen von Kompetenzen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ';
$string['report all descriptor examples'] = 'Alle Lernmaterialien von Kompetenzbereichen, Kompetenzen und Teilkompetenzen die nicht gefiltert wurden UND folgenden Filterkriterien entsprechen: ';
$string['filterlogic'] = 'Filterkriterien: ';
$string['topic_description'] = 'Bezeichnung der ersten Zeile (z.B. Kompetenzbereich)';
$string['niveau_description'] = 'Bezeichnung der ersten Spalte (z.B. LFS 1)';
$string['descriptor_description'] = 'Eintrag der ersten Zelle (z.B. Kompetenzbeschreibung)';
$string['selectcourse_filter'] = 'Filter';
$string['selectcourse_filter_schooltype'] = 'Schulart';
$string['selectcourse_filter_onlyselected'] = 'Nur ausgewählte Raster anzeigen';
$string['selectcourse_filter_submit'] = 'Filter';
$string['selectcourse_filter_emptyresult'] = 'Keine Ergebnisse zu diesem Filter';
$string['descriptor_label'] = 'Kompetenztitel';
$string['export_password_message'] = 'Bitte notieren Sie sich das Passwort "<strong>{$a}</strong>", bevor Sie fortfahren.<br/><br/>
		Hinweis: Passwortgeschützte zip-Dateien können unter Windows zwar geöffnet werden, aber die Dateien innerhalb der Zip-Datei können nur mit einem externen Programm (z.B. 7-Zip) extrahiert werden.
		';
$string['settings_heading_security'] = 'Sicherheit';
$string['settings_heading_security_description'] = '';
$string['settings_example_upload_global'] = 'Materialien global hochladen';
$string['settings_example_upload_global_description'] = 'Von Lehrern hochgeladene Materialien sind global verfüger. Die Materialien sind damit auch in anderen Kursen mit dem gleichen Raster sichtbar.';
$string['settings_show_teacherdescriptors_global'] = 'Selbsterzeugte Kompetenzen global anzeigen';
$string['settings_show_teacherdescriptors_global_description'] = 'Von Lehrern erstellte Kompetenzen sind global verfüger. Die Kompetenzen sind damit auch in anderen Kursen mit dem gleichen Raster sichtbar.';
$string['settings_export_password'] = 'Sicherung von Kompetenzrastern mit Passwort schützen (AES-256 Verschlüsselung)';
$string['settings_export_password_description'] = '(Nur ab php Version 7.2 verfügbar)';
$string['pre_planning_materials_assigned'] = 'Ausgewählte Materialien wurden den ausgewählten Schülern/Gruppen zugeteilt.';
$string['grade_example_related'] = 'Verbundene Kompetenzen und Materialien bewerten.';
$string['freematerials'] = 'Freie Materialien';
$string['radargraphtitle'] = 'Netzdiagramm';
$string['radargrapherror'] = 'Der Radargraph kann nur bei 3-13 Achsen dargestellt werden';
$string['studentcomp'] = 'Laut Selbsteinschätzung erreichte Kompetenzen';
$string['teachercomp'] = 'Erreichte Kompetenzen';
$string['pendingcomp'] = 'Ausstehende Kompetenzen';
$string['topicgrading'] = 'Gesamtbewertung des Themas: ';
$string['import_ics_title'] = 'WebUntis-Import';
$string['hide_imports_checkbox_label'] = 'WebUntis Anzeigen: ';
$string['import_ics'] = 'Kalender importieren';
$string['delete_imports'] = 'Importierte Termine löschen';
$string['upload_ics_file'] = 'Datei auswählen: ';
$string['is_teacherexample'] = 'Lehrermaterial';
$string['delete...'] = 'Löschen...';
$string['data_imported_title'] = 'Daten jetzt importieren';
$string['competence_overview_teacher_short'] = 'L:';
$string['competence_overview_student_short'] = 'S:';
$string['filterClear'] = 'Filter löschen';
$string['editor'] = 'Überarbeitung durch';
$string['hide_for_all_students'] = 'für alle TN verstecken';
$string['tab_teacher_settings_course_assessment'] = 'Kursspezifische Beurteilung';
$string['course_assessment_config_infotext'] = 'Wählen Sie das gewünschte Beurteilungsschema aus.';
$string['course_assessment_use_global'] = 'Globale Beurteilungseinstellung nutzen';
$string['course_assessment_settings'] = 'Kursspezifische Beurteilung';
$string['close'] = 'Schließen';
$string['opencomps'] = 'Kompetenzen auswählen';
$string['expandcomps'] = 'Alle öffnen';
$string['contactcomps'] = 'Alle schließen';
$string['questlink'] = 'Fragen verknüpfen';
$string['select_subjects'] = 'Raster auswählen';
$string['overview_examples_report_title'] = 'Aufgabenübersicht zum Kompetenzerwerb';
$string['block_exacomp_link_to_dakora_app'] = 'zur Dakora-App';
$string['diggrapp_cannotcreatetoken'] = 'Can not have access to this moodle installation';
$string['grid_creating_is_disabled'] = 'Die Neuanlage von Rastern ist deaktiviert!';
$string['save_hvp_activity'] = 'HVP Aktivität speichern';
$string['edulevel_without_assignment_title'] = 'ohne feste Zuordnung';
$string['schooltype_without_assignment_title'] = 'ohne feste Zuordnung';
$string['please_select_topic_first'] = 'Bitte wählen Sie zuerst in der linken Leiste einen Kompetenzbereich aus';
$string['no_course_templates'] = 'Kann keinen Kurs finden, der als Vorlage verwendet werden kann';
