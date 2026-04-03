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
$string['pluginname'] = 'Grille de compétences Exabis';
// shown in block title and all headers
$string['blocktitle'] = 'Grille de compétences';
$string['exacomp:addinstance'] = 'Ajouter un bloc Grille de compétences Exabis sur la page de cours';
$string['exacomp:myaddinstance'] = 'Ajouter un bloc Grille de compétences Exabis sur la page d\'accueil';
$string['exacomp:teacher'] = 'Vue d\'ensemble des fonctions d\'enseignant dans un cours';
$string['exacomp:admin'] = 'Vue d\'ensemble des fonctions d\'administrateur dans un cours';
$string['exacomp:student'] = 'Vue d\'ensemble des fonctions des participants dans un cours';
$string['exacomp:use'] = 'Utilisation';
$string['exacomp:deleteexamples'] = 'Supprimer les ressources pédagogiques';
$string['exacomp:assignstudents'] = 'Attribuer des formateurs externes';
$string['exacomp:editingteacher'] = 'Enseignant éditeur';
$string['exacomp:getfullcompetencegridforprofile'] = 'pour le service Web block_exacomp_get_fullcompetence_grid_for_profile';

//Cache definition
$string['cachedef_visibility_cache'] = 'Cache pour améliorer les performances lors de la vérification des visibilités';

// === Admin Tabs ===
$string['tab_admin_import'] = 'Import/Export';
$string['tab_admin_settings'] = 'Paramètres du site';
$string['tab_admin_configuration'] = 'Présélection des grilles de compétences';
$string['admin_config_pending'] = 'La présélection des compétences par l\'administrateur est nécessaire';
$string['tab_admin_taxonomies'] = 'Niveaux de difficulté';

// === Teacher Tabs ===
$string['tab_teacher_import'] = 'Import';
$string['tab_teacher_settings'] = 'Paramètres du cours';
$string['tab_teacher_settings_configuration'] = 'Paramètres';
$string['tab_teacher_settings_selection_st'] = 'Type d\'établissement / Références au programme d\'études';
$string['tab_teacher_settings_selection'] = 'Sélection des domaines de compétences';
$string['tab_teacher_settings_assignactivities'] = 'Attribuer les activités Moodle';
$string['tab_teacher_settings_activitiestodescriptors'] = 'Lier les activités Moodle';
$string['tab_teacher_settings_questiontodescriptors'] = 'Lier les questions de test';
$string['tab_teacher_settings_badges'] = 'Modifier les badges';
$string['tab_teacher_settings_new_subject'] = 'Créer une nouvelle grille de compétences';
$string['tab_teacher_settings_taxonomies'] = 'Niveaux de difficulté';
$string['tab_teacher_settings_taxonomies_help'] = 'Les ressources pédagogiques ainsi que les compétences peuvent être associées à des niveaux de difficulté (généralement dans l\'outil de saisie de grilles de compétences KOMET).</br>
Les ressources pédagogiques et les compétences peuvent être filtrées par niveaux de difficulté.</br>
Un autre terme pour les niveaux de difficulté est taxonomies - par exemple, la taxonomie de Bloom peut être utilisée pour classifier le niveau d\'apprentissage (voir <a href=\'https://fr.wikipedia.org/wiki/Taxonomie_de_Bloom\' target=\'_blank\'>https://fr.wikipedia.org/wiki/Taxonomie_de_Bloom</a>)
';
$string['tab_teacher_report_general'] = 'Rapports';
$string['tab_teacher_report_annex'] = 'Rapports ';
$string['tab_teacher_report_annex_title'] = 'Annexe au rapport de développement de l\'apprentissage';
$string['tab_teacher_report_profoundness'] = 'Compétences de base et avancées ';
$string['tab_teacher_report_profoundness_title'] = 'Utiliser les compétences de base et avancées';
$string['create_html'] = 'Générer le rapport au format HTML (aperçu)';
$string['create_docx'] = 'Générer le rapport au format docx';
$string['create_pdf'] = 'Générer le rapport au format pdf';
$string['create_html_report'] = 'Générer le rapport au format HTML';
$string['create_docx_report'] = 'Générer le rapport au format docx';
$string['create_pdf_report'] = 'Générer le rapport au format pdf';
$string['tab_teacher_report_annex_template'] = 'modèle docx';
$string['tab_teacher_report_annex_delete_template'] = 'supprimer';

// === Student Tabs ===
$string['tab_student_all'] = 'Toutes les compétences acquises';

// === Generic Tabs (used by Teacher and Students) ===
$string['tab_competence_gridoverview'] = 'Vue d\'ensemble';
$string['tab_competence_overview'] = 'Grille de compétences';
$string['tab_examples'] = 'Ressources pédagogiques';
$string['tab_badges'] = 'Mes badges';
$string['tab_competence_profile'] = 'Profil de compétences';
$string['tab_competence_profile_profile'] = 'Profil';
$string['tab_competence_profile_settings'] = 'Paramètres';
$string['tab_help'] = 'Aide';
$string['tab_profoundness'] = 'Compétences de base/avancées';
$string['tab_cross_subjects'] = 'Thèmes';
$string['tab_cross_subjects_overview'] = 'Vue d\'ensemble';
$string['tab_cross_subjects_course'] = 'Thèmes du cours';
$string['tab_weekly_schedule'] = 'Plan hebdomadaire';
$string['tab_group_reports'] = 'Rapports';
$string['assign_descriptor_to_crosssubject'] = 'Attribuer la compétence "{$a}" aux thèmes suivants :';
$string['assign_descriptor_no_crosssubjects_available'] = 'Aucun thème n\'est disponible, veuillez en créer.';
$string['first_configuration_step'] = 'La première étape de configuration consiste à importer des données dans le module Grille de compétences Exabis.';
$string['second_configuration_step'] = 'Dans la deuxième étape de configuration, les grilles de compétences doivent être sélectionnées.';
$string['next_step'] = 'Cette étape de configuration a été complétée. Cliquez ici pour passer à la suivante.';
$string['next_step_teacher'] = 'La configuration qui doit être effectuée par l\'administrateur est maintenant terminée. Pour continuer avec la configuration spécifique au cours, cliquez ici.';
$string['teacher_first_configuration_step'] = 'Dans la première étape de configuration des grilles de compétences du cours, certains paramètres généraux doivent être définis.';
$string['teacher_second_configuration_step'] = 'Dans la deuxième étape de configuration, les domaines thématiques avec lesquels vous souhaitez travailler dans ce cours doivent être sélectionnés.';
$string['teacher_third_configuration_step'] = 'L\'étape suivante consiste à associer les activités Moodle aux compétences. ';
$string['teacher_third_configuration_step_link'] = '(Optionnel : Si vous ne souhaitez pas travailler avec des activités Moodle, décochez la case "Je souhaite travailler avec des activités Moodle" dans l\'onglet "Paramètres".)';
$string['completed_config'] = 'La configuration de la Grille de compétences Exabis est terminée.';
$string['optional_step'] = 'Aucun participant n\'est encore inscrit dans votre cours. Cliquez sur ce lien si vous souhaitez le faire maintenant.';
$string['enrol_users'] = 'Inscrivez des participants pour pouvoir utiliser Exacomp.';
$string['next_step_first_teacher_step'] = 'Cliquez ici pour passer à l\'étape suivante.';

// === Block Settings ===
$string['settings_xmlserverurl'] = 'URL du serveur';
$string['settings_configxmlserverurl'] = 'URL vers un fichier XML utilisé pour maintenir les données à jour';
$string['settings_autotest'] = 'Évaluation automatique par les activités Moodle';
$string['settings_autotest_description'] = 'Les compétences ou tâches associées à des activités sont automatiquement considérées comme acquises lorsque les critères d\'achèvement de l\'activité spécifiés sont remplis.';
$string['settings_testlimit'] = 'Limite de test en %';
$string['settings_testlimit_description'] = 'Ce pourcentage doit être atteint pour que la compétence soit considérée comme acquise';
$string['settings_usebadges'] = 'Utiliser les badges/distinctions';
$string['settings_usebadges_description'] = 'Cochez pour attribuer des compétences aux badges/distinctions';
$string['settings_interval'] = 'Durée des unités';
$string['settings_interval_description'] = 'La durée des unités dans le plan hebdomadaire en minutes';
$string['settings_scheduleunits'] = 'Nombre d\'unités';
$string['settings_scheduleunits_description'] = 'Nombre d\'unités dans le plan hebdomadaire';
$string['settings_schedulebegin'] = 'Début des unités';
$string['settings_schedulebegin_description'] = 'Heure de début de la première unité dans le plan hebdomadaire. Format hh:mm';
$string['settings_description_nurmoodleunddakora'] = '<b>Uniquement Moodle et l\'application Dakora</b>';
$string['settings_description_nurdakora'] = '<b>Uniquement l\'application Dakora</b>';
$string['settings_description_nurdiggr'] = '<b>Uniquement les applications Diggr+ et elove</b>';
$string['settings_description_nurdakoraplus'] = '<b>Uniquement l\'application DakoraPlus</b>';
$string['settings_admin_scheme'] = 'Configuration prédéfinie';
$string['settings_admin_scheme_description'] = 'Les évaluations peuvent être effectuées à différents niveaux.';
$string['settings_admin_scheme_none'] = 'aucun niveau';
$string['settings_additional_grading'] = 'Évaluation adaptée';
$string['settings_additional_grading_description'] = 'Limiter globalement l\'évaluation des sous-compétences et des ressources pédagogiques de "non atteint(0)" à "complètement atteint(3)"';
$string['settings_periods'] = 'Entrées pour l\'emploi du temps';
$string['settings_periods_description'] = 'Le plan hebdomadaire peut être adapté de manière flexible à n\'importe quel emploi du temps. Utilisez une nouvelle ligne dans le bloc de texte pour chaque période. Toutes les entrées textuelles sont autorisées, par exemple "1ère heure" ou "07:30 - 09:00".';
$string['settings_heading_general'] = 'Général';
$string['settings_heading_assessment'] = 'Évaluation';
$string['settings_heading_visualisation'] = 'Présentation';
$string['settings_heading_technical'] = 'Administration';
$string['settings_heading_apps'] = 'Paramètres des applications';
$string['settings_new_app_login'] = 'Utiliser la connexion SSO-App';
$string['settings_dakora_teacher_link'] = 'Cliquez pour définir les enseignants Dakora';
$string['settings_applogin_redirect_urls'] = 'URLs de connexion à l\'application';
$string['settings_applogin_redirect_urls_description'] = '';
$string['settings_applogin_enabled'] = 'Activer la connexion à l\'application';
$string['settings_applogin_enabled_description'] = 'Autorise la connexion depuis les applications Exabis (Diggr+, Dakora, Dakora+, elove)';
$string['settings_setapp_enabled'] = 'Activer les fonctions de l\'application SET';
$string['settings_setapp_enabled_description'] = 'Autoriser la création de comptes utilisateur via l\'application.';
$string['settings_sso_create_users'] = 'SSO : Créer de nouveaux utilisateurs';
$string['settings_sso_create_users_description'] = '';
$string['settings_msteams_client_id'] = 'ID client de l\'application MS Teams Diggr+';
$string['settings_msteams_client_id_description'] = '';
$string['settings_msteams_client_secret'] = 'Secret client de l\'application MS Teams Diggr+';
$string['settings_msteams_client_secret_description'] = '';
$string['dakora_teachers'] = 'Enseignants Dakora';
$string['settings_new_app_login_description'] = 'La nouvelle connexion d\'application permet aux utilisateurs de se connecter avec tous les plugins de connexion Moodle activés. Ce paramètre n\'est pas compatible avec le plugin de gamification.';
$string['settings_heading_performance'] = 'Performance';
$string['settings_heading_performance_description'] = 'Si la vue de la grille de compétences se charge lentement, ces paramètres peuvent être utilisés pour optimiser le chargement.';
$string['settings_heading_scheme'] = 'Schéma d\'évaluation générique';
$string['settings_assessment_are_you_sure_to_change'] = 'Voulez-vous vraiment modifier le schéma d\'évaluation ? Les évaluations existantes peuvent être perdues ou leur pertinence peut être altérée';
$string['settings_assessment_scheme_0'] = 'Aucun';
$string['settings_assessment_scheme_1'] = 'Notes';
$string['settings_assessment_scheme_2'] = 'Verbalisation';
$string['settings_assessment_scheme_3'] = 'Points';
$string['settings_assessment_scheme_4'] = 'Oui/Non';
$string['settings_assessment_diffLevel'] = 'Niveau';
$string['settings_assessment_SelfEval'] = 'Auto-évaluation';
$string['settings_assessment_target_example'] = 'Matériel';
$string['settings_assessment_target_childcomp'] = 'Sous-compétence';
$string['settings_assessment_target_comp'] = 'Compétence';
$string['settings_assessment_target_topic'] = 'Domaine de compétence';
$string['settings_assessment_target_subject'] = 'Matière';
$string['settings_assessment_target_theme'] = 'Thème (interdisciplinaire)';
$string['settings_assessment_points_limit'] = 'Nombre de points maximum';
$string['settings_assessment_points_limit_description'] = 'Schéma d\'évaluation points, le nombre maximum de points pouvant être saisi.';
$string['settings_assessment_points_negativ'] = 'Évaluation négative points';
$string['settings_assessment_points_negativ_description'] = 'Limite inférieure (évaluation négative) de la valeur des points dans le schéma d\'évaluation';
$string['settings_assessment_grade_limit'] = 'Note maximale';
$string['settings_assessment_grade_limit_description'] = 'Schéma d\'évaluation note, la note maximale pouvant être saisie.';
$string['settings_assessment_grade_negativ'] = 'Évaluation négative notes';
$string['settings_assessment_grade_negativ_description'] = 'Limite inférieure (évaluation négative) de la valeur des notes dans le schéma d\'évaluation';
$string['settings_assessment_diffLevel_options'] = 'Valeurs de niveau';
$string['settings_assessment_diffLevel_options_description'] = 'Liste des valeurs possibles du niveau, par exemple : G,M,E,Z';
$string['settings_assessment_diffLevel_options_default'] = 'G,M,E,Z';
$string['settings_assessment_verbose_options'] = 'Degré d\'atteinte';
$string['settings_assessment_verbose_options_description'] = 'Liste des valeurs possibles de verbalisation, par exemple : non atteint, partiellement atteint, majoritairement atteint, complètement atteint';
$string['settings_assessment_verbose_options_default'] = 'non atteint, partiellement atteint, majoritairement atteint, complètement atteint';
$string['settings_assessment_verbose_options_short'] = 'Abréviation des valeurs de verbalisation';
$string['settings_assessment_verbose_options_short_description'] = 'Abréviation des valeurs verbalisées ci-dessus pour les évaluations';
$string['settings_assessment_verbose_options_short_default'] = 'na, pa, ma, ca';
$string['settings_schoolname'] = 'Désignation et emplacement de l\'établissement';
$string['settings_schoolname_description'] = '';
$string['settings_schoolname_default'] = 'Désignation et emplacement de l\'établissement';
$string['settings_assessment_grade_verbose'] = 'Verbalisation des notes';
$string['settings_assessment_grade_verbose_description'] = 'Valeurs verbalisées des notes, séparées par des virgules. Le nombre doit correspondre à la valeur "Note maximale" ci-dessus. Par exemple : très bien, bien, satisfaisant, suffisant, insuffisant, très insuffisant';
$string['settings_assessment_grade_verbose_default'] = 'très bien, bien, satisfaisant, suffisant, insuffisant, très insuffisant';
$string['settings_assessment_grade_verbose_negative'] = 'Verbalisation de l\'évaluation négative';
$string['settings_assessment_grade_verbose_negative_description'] = 'Limite inférieure (évaluation négative) de l\'évaluation verbalisée dans le schéma d\'évaluation';
$string['use_grade_verbose_competenceprofile'] = 'Verbalisation des notes dans le profil de compétences';
$string['use_grade_verbose_competenceprofile_descr'] = 'Utiliser la verbalisation des notes dans le profil de compétences';
$string['settings_sourceId'] = 'ID source';
$string['settings_sourceId_description'] = 'ID générée automatiquement de cette installation Exacomp. Celle-ci ne peut pas être modifiée';
$string['settings_admin_preconfiguration_none'] = 'Configuration personnalisée';
$string['settings_default_de_value'] = 'Valeur DE : ';
$string['settings_assessment_SelfEval_verboses'] = 'Valeurs pour le retour verbalisé des élèves';
$string['settings_assessment_SelfEval_verboses_long_columntitle'] = 'Long';
$string['settings_assessment_SelfEval_verboses_short_columntitle'] = 'Court';
$string['settings_assessment_SelfEval_verboses_edit'] = 'Modifier';
$string['settings_assessment_SelfEval_verboses_validate_error_long'] = 'Format long : jusqu\'à 4 entrées, séparateur point-virgule, max 20 caractères par entrée (4 pour le format court)';
$string['settings_addblock_to_newcourse'] = 'Ajouter automatiquement le bloc aux nouveaux cours';
$string['settings_addblock_to_newcourse_description'] = 'Le bloc "Grille de compétences Exabis" sera automatiquement ajouté à chaque nouveau cours. La position du bloc dépend du thème Moodle.';
$string['settings_addblock_to_newcourse_option_no'] = 'Non';
$string['settings_addblock_to_newcourse_option_yes'] = 'Oui';
$string['settings_addblock_to_newcourse_option_left'] = 'vers la région de gauche';
$string['settings_addblock_to_newcourse_option_right'] = 'vers la région de droite';
$string['settings_disable_js_assign_competencies'] = 'Désactiver JS pour la vue d\'ensemble de la grille de compétences.';
$string['settings_disable_js_assign_competencies_description'] = 'En cas de longs temps de chargement de la grille de compétences, les fonctions JS peuvent être désactivées pour améliorer les performances.';
$string['settings_disable_js_editactivities'] = 'Désactiver JS pour l\'attribution des activités Moodle aux participants';
$string['settings_disable_js_editactivities_description'] = 'Activer si la page "Attribuer les activités Moodle" se charge trop lentement.';
$string['settings_example_autograding'] = 'Évaluation automatique des matériaux parents';
$string['settings_example_autograding_description'] = 'Lorsque toutes les tâches subordonnées sont terminées, le matériel parent doit être évalué automatiquement.';
$string['settings_assessment_verbose_lowerisbetter'] = 'Une valeur inférieure est meilleure';
$string['settings_assessment_verbose_lowerisbetter_description'] = 'Plus la valeur de l\'évaluation est basse, mieux c\'est.';

// === Unit Tests ===
$string['unittest_string'] = 'result_unittest_string';
$string['de:unittest_string2'] = 'result_unittest_string2';
$string['de:unittest_string3'] = 'unittest_string3';
$string['de:unittest_param {$a} unittest_param'] = 'result_unittest_param {$a} result_unittest_param';
$string['de:unittest_param2 {$a->val} unittest_param2'] = 'result_unittest_param2 {$a->val} result_unittest_param2';

// === Learning agenda ===
$string['LA_MON'] = 'Lun';
$string['LA_TUE'] = 'Mar';
$string['LA_WED'] = 'Mer';
$string['LA_THU'] = 'Jeu';
$string['LA_FRI'] = 'Ven';
$string['LA_todo'] = 'Que fais-je ?';
$string['LA_learning'] = 'Qu\'est-ce que je peux apprendre ?';
$string['LA_student'] = 'É';
$string['LA_teacher'] = 'E';
$string['LA_assessment'] = 'Évaluation';
$string['LA_plan'] = 'Plan de travail';
$string['LA_no_learningagenda'] = 'Il n\'y a pas d\'agenda d\'apprentissage disponible pour la semaine sélectionnée.';
$string['LA_no_student_selected'] = '-- aucun participant sélectionné --';
$string['LA_select_student'] = 'Veuillez sélectionner un participant pour consulter son agenda d\'apprentissage.';
$string['LA_no_example'] = 'Aucune ressource pédagogique attribuée';
$string['LA_backtoview'] = 'Retour à la vue d\'origine';
$string['LA_from_n'] = ' de ';
$string['LA_from_m'] = ' du ';
$string['LA_to'] = ' jusqu\'au ';
$string['LA_enddate'] = 'Date de fin';
$string['LA_startdate'] = 'Date de début';

// === Help ===
$string['help_content'] = '<h1>Vidéo d\'introduction</h1>
<iframe width="640" height="360" src="//www.youtube.com/embed/EL4Vb3_17EM?feature=player_embedded" frameborder="0" allowfullscreen></iframe>
';

// === Import ===
$string['importinfo'] = 'Créez vos propres grilles de compétences sur <a target="_blank" href="https://comet.edustandards.org">www.edustandards.org</a>.';
$string['importwebservice'] = 'Il est également possible de maintenir les données à jour via une <a href="{$a}">URL de serveur</a>.';
$string['import_max_execution_time'] = 'Important : les paramètres actuels du serveur limitent l\'importation à {$a} secondes. Si le processus d\'importation dure plus longtemps, il sera interrompu et aucune donnée ne sera importée. Dans ce cas, un message d\'erreur côté serveur sera affiché sur l\'appareil de sortie (par exemple "500 Internal Server Error").';
$string['importdone'] = 'Les grilles de compétences générales ont déjà été importées.';
$string['importpending'] = 'Veuillez maintenant importer les standards éducatifs généraux et sélectionner ensuite les domaines de compétences à afficher dans l\'onglet Standards éducatifs.';
$string['doimport'] = 'Importer une grille de compétences';
$string['doimport_again'] = 'Importer d\'autres grilles de compétences';
$string['doimport_own'] = 'Importer des standards éducatifs spécifiques à l\'établissement';
$string['scheduler_import_settings'] = 'Paramètres pour l\'importation planifiée';
$string['delete_own'] = 'Supprimer les standards éducatifs spécifiques à l\'établissement';
$string['delete_success'] = 'Les standards éducatifs spécifiques à l\'établissement ont été supprimés';
$string['delete_own_confirm'] = 'Voulez-vous vraiment supprimer les standards éducatifs spécifiques à l\'établissement ? Cette étape ne peut pas être annulée.';
$string['importsuccess'] = 'Données importées avec succès !';
$string['importsuccess_own'] = 'Données personnelles importées avec succès !';
$string['importfail'] = 'Une erreur s\'est produite.';
$string['noxmlfile'] = 'Un import n\'est actuellement pas possible car aucun fichier XML n\'est disponible. Veuillez télécharger les standards correspondants ici et les copier dans le répertoire xml du bloc : <a href="https://github.com/gtn/edustandards">https://github.com/gtn/edustandards</a>';
$string['oldxmlfile'] = 'Vous utilisez un fichier XML obsolète. Veuillez créer un nouveau fichier sur <a href="https://comet.edustandards.org">www.edustandards.org</a> ou télécharger un XML existant depuis <a href="http://www.github.com/gtn/edustandards">github.com/gtn/edustandards</a>.';
$string['do_demo_import'] = 'Importez un jeu de données de démonstration pour voir comment fonctionne la Grille de compétences Exabis.';
$string['schedulerimport'] = 'Importation de tâches planifiées';
$string['add_new_importtask'] = 'Ajouter une nouvelle tâche planifiée';
$string['importtask_title'] = 'Titre';
$string['importtask_link'] = 'Lien vers la source';
$string['importtask_disabled'] = 'Désactivé';
$string['importtask_all_subjects'] = 'Tous les standards éducatifs';
$string['dest_course'] = 'Destination des activités importées';
$string['import_activities'] = 'Importez des activités du cours modèle dans votre cours';
$string['download_activites'] = 'Télécharger les activités';

// === Configuration ===
$string['explainconfig'] = 'Pour pouvoir utiliser le module Grille de compétences Exabis, les domaines de compétences de l\'instance Moodle doivent être sélectionnés ici.';
$string['save_selection'] = 'Confirmer';
$string['save_success'] = 'Modifications enregistrées avec succès';

// === Course-Configuration ===
$string['grading_scheme'] = 'Schéma d\'évaluation';
$string['points_limit_forcourse'] = 'Nombre de points maximum';
$string['uses_activities'] = 'J\'utilise les activités Moodle pour l\'évaluation';
$string['show_all_descriptors'] = 'Afficher toutes les compétences dans la vue d\'ensemble';
$string['useprofoundness'] = 'Utiliser les compétences de base et avancées';
$string['assessment_SelfEval_useVerbose'] = 'Retour verbalisé des élèves';
$string['selfEvalVerbose.defaultValue_long'] = 'ne s\'applique pas ; ne s\'applique plutôt pas ; s\'applique plutôt ; s\'applique';
$string['selfEvalVerbose.defaultValue_short'] = 'nsp ; nspp ; sp ; sa';
$string['selfEvalVerboseExample.defaultValue_long'] = 'non résolu ; résolu avec aide ; résolu de manière autonome';
$string['selfEvalVerboseExample.defaultValue_short'] = 'nr ; ra ; rs';
$string['selfEvalVerbose.1'] = 'ne s\'applique pas';
$string['selfEvalVerbose.2'] = 's\'applique plutôt pas';
$string['selfEvalVerbose.3'] = 's\'applique plutôt';
$string['selfEvalVerbose.4'] = 's\'applique';
$string['selfEvalVerboseExample.1'] = 'non résolu';
$string['selfEvalVerboseExample.2'] = 'résolu avec aide';
$string['selfEvalVerboseExample.3'] = 'résolu de manière autonome';
$string['usetopicgrading'] = 'Activer l\'évaluation des domaines de compétences';
$string['usesubjectgrading'] = 'Activer l\'évaluation des matières';
$string['usenumbering'] = 'Utiliser la numérotation automatique dans la grille de compétences';
$string['usenostudents'] = 'Travailler sans participants';
$string['usehideglobalsubjects'] = 'Masquer les grilles de compétences transversales';
$string['profoundness_0'] = 'Non atteint';
$string['profoundness_1'] = 'Partiellement atteint';
$string['profoundness_2'] = 'Atteint';
$string['filteredtaxonomies'] = 'Les ressources pédagogiques sont utilisées selon les taxonomies sélectionnées :';
$string['show_all_taxonomies'] = 'Toutes les taxonomies';
$string['warning_use_activities'] = 'Remarque : vous travaillez maintenant avec des activités Moodle liées à des compétences. Assurez-vous de continuer à travailler avec les mêmes compétences dans ce cours.';
$string['delete_unconnected_examples'] = 'Si vous désélectionnez des domaines thématiques auxquels sont liées des ressources pédagogiques qui se trouvent encore dans le plan hebdomadaire, celles-ci seront retirées du plan hebdomadaire.';

// === Badges ===
$string['mybadges'] = 'Mes badges';
$string['pendingbadges'] = 'Badges en attente';
$string['no_badges_yet'] = 'Aucun badge disponible';
$string['description_edit_badge_comps'] = 'Ici, vous pouvez attribuer des compétences au badge sélectionné.';
$string['to_award'] = 'Pour obtenir ce badge, des compétences doivent être attribuées.';
$string['to_award_role'] = 'Pour obtenir ce badge, vous devez ajouter le critère "attribution manuelle".';
$string['ready_to_activate'] = 'Ce badge peut être activé : ';
$string['conf_badges'] = 'Configurer les badges';
$string['conf_comps'] = 'Attribuer les compétences';

// === Examples ===
$string['example'] = 'Ressource pédagogique';
$string['sorting'] = 'Choisir le tri : ';
$string['subject'] = 'Standard éducatif';
$string['topic'] = 'Domaine de compétence';
$string['taxonomies'] = 'Niveaux de difficulté';
$string['show_all_course_examples'] = 'Afficher les ressources pédagogiques de tous les cours';
$string['name_example'] = 'Nom de la ressource pédagogique';
$string['timeframe_example'] = 'Suggestion de durée';
$string['example_add_taxonomy'] = 'Créer un nouveau niveau de difficulté';
$string['comp_based'] = 'Trier par compétences';
$string['examp_based'] = 'Trier par ressources pédagogiques';
$string['cross_based'] = 'pour les thèmes';

// === Icons ===
$string['assigned_example'] = 'Ressource pédagogique attribuée';
$string['task_example'] = 'Tâche';
$string['extern_task'] = 'Tâche externe';
$string['total_example'] = 'Matériel complet';

// === Example Upload ===
$string['example_upload_header'] = 'Téléverser sa propre ressource pédagogique';
$string['taxonomy'] = 'Niveau de difficulté';
$string['descriptors'] = 'Compétences';
$string['filerequired'] = 'Un fichier doit être sélectionné.';
$string['titlenotemtpy'] = 'Un nom doit être saisi.';
$string['solution'] = 'Solution modèle';
$string['completefile'] = 'Exemple complet';
$string['hide_solution'] = 'Masquer la solution modèle';
$string['show_solution'] = 'Afficher la solution modèle';
$string['hide_solution_disabled'] = 'La solution modèle est déjà masquée pour tous les élèves';
$string['submission'] = 'Rendu';
$string['assignments'] = 'Activités Moodle';
$string['files'] = 'Fichiers';
$string['link'] = 'Lien';
$string['links'] = 'Liens';
$string['dataerr'] = 'Au moins un lien ou un fichier doit être téléversé !';
$string['linkerr'] = 'Veuillez saisir un lien correct !';
$string['isgraded'] = 'La tâche a déjà été évaluée et ne peut donc plus être soumise.';
$string['allow_resubmission'] = 'Autoriser une nouvelle soumission de la tâche';
$string['allow_resubmission_info'] = 'La tâche a été autorisée pour une nouvelle soumission.';

// === Assign competencies ===
$string['header_edit_mode'] = 'Vous êtes en mode édition';
$string['comp_-1'] = 'sans indication';
$string['comp_0'] = 'non atteint';
$string['comp_1'] = 'partiellement';
$string['comp_2'] = 'majoritairement';
$string['comp_3'] = 'complètement';
$string['comp_-1_short'] = 'si';
$string['comp_0_short'] = 'na';
$string['comp_1_short'] = 'pa';
$string['comp_2_short'] = 'ma';
$string['comp_3_short'] = 'ca';
$string['delete_confirmation'] = 'Voulez-vous vraiment supprimer "{$a}" ?';
$string['legend_activities'] = 'Activités Moodle';
$string['legend_eportfolio'] = 'ePortfolio';
$string['legend_notask'] = 'Aucune activité Moodle/quiz soumis pour cette compétence';
$string['legend_upload'] = 'Téléverser sa propre ressource pédagogique';
$string['allniveaus'] = 'Tous les progrès d\'apprentissage';
$string['choosesubject'] = 'Sélectionner une grille de compétences';
$string['choosetopic'] = 'Sélectionner les progrès d\'apprentissage';
$string['choosestudent'] = 'Sélectionner un participant : ';
$string['choose_student'] = 'Sélection des élèves : ';
$string['choosedaterange'] = 'Sélectionner une période d\'observation : ';
$string['cleardaterange'] = 'Réinitialiser';
$string['seperatordaterange'] = 'à';
$string['own_additions'] = 'Complément scolaire : ';
$string['delete_confirmation_descr'] = 'Voulez-vous vraiment supprimer la compétence "{$a}" pour tous les cours ?';
$string['import_source'] = 'Importé de : {$a}';
$string['local'] = 'Local';
$string['unknown_src'] = 'source inconnue';
$string['override_notice1'] = 'Cette entrée a été modifiée par ';
$string['override_notice2'] = '. Vraiment modifier ?';
$string['dismiss_gradingisold'] = 'Voulez-vous ignorer l\'avertissement ?';
$string['unload_notice'] = 'Voulez-vous vraiment quitter la page ? Les modifications non enregistrées seront perdues.';
$string['example_sorting_notice'] = 'Veuillez d\'abord enregistrer les évaluations actuelles';
$string['newsubmission'] = 'Nouvelle soumission';
$string['value_too_large'] = 'Erreur : les évaluations ne doivent pas être supérieures à {limit} !';
$string['value_too_low'] = 'Erreur : les évaluations ne doivent pas être inférieures à 1.0 !';
$string['value_not_allowed'] = 'Erreur : les évaluations doivent être des valeurs numériques entre 1.0 et 6.0';
$string['competence_locked'] = 'Évaluation existante ou ressource pédagogique en cours d\'utilisation !';
$string['save_changes_competence_evaluation'] = 'Les modifications ont été enregistrées !';
// === Example Submission ===
$string['example_submission_header'] = 'Modifier la tâche {$a}';
$string['example_submission_info'] = 'Tu es en train de traiter la tâche "{$a}". Ta soumission sera enregistrée dans l\'ePortfolio et pourra y être consultée par toi et ton enseignant(e).';
$string['topic_submission_info'] = 'Tu es en train de faire une soumission pour le domaine de compétence "{$a}". Ta soumission sera enregistrée dans l\'ePortfolio et pourra y être consultée par toi et ton enseignant(e).';
$string['descriptor_submission_info'] = 'Tu es en train de faire une soumission pour la compétence "{$a}". Ta soumission sera enregistrée dans l\'ePortfolio et pourra y être consultée par toi et ton enseignant(e).';
$string['example_submission_subject'] = 'Nouvelle soumission';
$string['example_submission_message'] = 'Dans le cours {$a->course}, une nouvelle soumission a été soumise par le participant {$a->student}.';
$string['submissionmissing'] = 'Au moins un lien ou un fichier doit être soumis';
$string['associated_activities'] = 'Activités Moodle associées :';
$string['usernosubmission'] = 'Activités Moodle ouvertes';
$string['grading'] = 'Évaluation';
$string['teacher_tipp'] = 'Conseil';
$string['teacher_tipp_1'] = 'Cette compétence a été attribuée à ';
$string['teacher_tipp_2'] = ' activité(s) Moodle et a déjà été remplie dans ';
$string['teacher_tipp_3'] = ' activité(s) Moodle dans la vue détaillée de la compétence.';
$string['print'] = 'Imprimer';
$string['eportitems'] = 'Artefacts ePortfolio soumis pour ce descripteur :';
$string['eportitem_shared'] = ' (partagé)';
$string['eportitem_notshared'] = ' (non partagé)';
$string['teachershortcut'] = 'E';
$string['studentshortcut'] = 'É';
$string['overview'] = 'Vous avez ici un aperçu des sous-compétences des compétences sélectionnées et des tâches attribuées. Vous pouvez confirmer individuellement l\'atteinte de chaque sous-compétence.';
$string['showevaluation'] = 'Pour consulter l\'auto-évaluation, cliquez <a href="{$a}">ici</a>';
$string['hideevaluation'] = 'Pour masquer l\'auto-évaluation, cliquez <a href="{$a}">ici</a>';
$string['showevaluation_student'] = 'Pour activer l\'évaluation des formateurs, clique <a href="{$a}">ici</a>.';
$string['hideevaluation_student'] = 'Pour désactiver l\'évaluation des formateurs, clique <a href="{$a}">ici</a>.';
$string['columnselect'] = 'Sélection de colonnes';
$string['allstudents'] = 'Tous les participants';
$string['all_activities'] = 'Toutes les activités';
$string['nostudents'] = 'Aucun participant';
$string['statistic'] = 'Vue d\'ensemble générale';
$string['niveau'] = 'Progrès d\'apprentissage';
$string['niveau_short'] = 'PA';
$string['competence_grid_niveau'] = 'Niveau';
$string['competence_grid_additionalinfo'] = 'Note';
$string['descriptor'] = 'Compétence';
$string['descriptor_child'] = 'Sous-compétence';
$string['assigndone'] = 'Tâche terminée : ';
$string['descriptor_categories'] = 'Modifier les niveaux de difficulté : ';
$string['descriptor_add_category'] = 'Ajouter un nouveau niveau de difficulté : ';
$string['descriptor_categories_description'] = 'Sélectionnez ici le(s) niveau(x) de difficulté pour cette compétence/cette ressource pédagogique. Vous pouvez également ajouter un nouveau niveau de difficulté ou laisser ce champ vide.';

// === metadata ===
$string['subject_singular'] = 'Matière';
$string['comp_field_idea'] = 'Domaine de compétence/Idée directrice';
$string['comp'] = 'Compétence';
$string['progress'] = 'Progrès d\'apprentissage';
$string['instruction'] = 'Instructions';
$string['instruction_content'] = 'Vous pouvez noter ici pour vos groupes d\'apprentissage / classe quelles
				ressources pédagogiques ont été traitées et quelles preuves d\'apprentissage ont été fournies.
				De plus, vous pouvez enregistrer l\'atteinte des sous-compétences.
				Selon le concept de l\'établissement, le traitement de la
				ressource pédagogique / l\'atteinte d\'une sous-compétence peut être
				marqué par une croix ou la qualité du traitement / de l\'atteinte de la compétence
				peut être indiquée. En aucun cas les élèves ne doivent
				traiter tous les matériaux. Si une (sous-)compétence
				est déjà acquise, cela peut être noté ici. Les élèves
				n\'ont alors pas besoin de traiter les ressources pédagogiques
				associées.';

// === Activities ===
$string['explaineditactivities_subjects'] = 'Vous pouvez attribuer des compétences aux tâches créées ici.';
$string['niveau_filter'] = 'Filtrer les niveaux';
$string['module_filter'] = 'Filtrer les activités';
$string['apply_filter'] = 'Appliquer le filtre';
$string['no_topics_selected'] = 'La configuration de la Grille de compétences Exabis n\'est pas encore terminée. Veuillez d\'abord sélectionner des objets auxquels vous pourrez ensuite attribuer des activités Moodle.';
$string['no_activities_selected'] = 'Veuillez attribuer des compétences aux activités Moodle créées.';
$string['no_activities_selected_student'] = 'Aucune donnée n\'est actuellement disponible dans cette zone.';
$string['no_course_activities'] = 'Aucune activité Moodle n\'a encore été créée dans ce cours, cliquez ici pour le faire maintenant.';
$string['all_modules'] = 'Toutes les activités';
$string['tick_some'] = 'Veuillez faire une sélection !';

// === Competence Grid ===
$string['infolink'] = 'Plus d\'informations : ';
$string['textalign'] = 'Modifier l\'alignement du texte';
$string['selfevaluation'] = 'Auto-évaluation';
$string['selfevaluation_short'] = 'AE';
$string['teacherevaluation_short'] = 'EE';
$string['teacherevaluation'] = 'Évaluation de l\'évaluateur';
$string['competencegrid_nodata'] = 'Si la grille de compétences est vide, aucun niveau n\'a été défini dans les données pour les descripteurs de l\'objet sélectionné';
$string['statistic_type_descriptor'] = 'Passer aux statistiques des sous-compétences';
$string['statistic_type_example'] = 'Passer aux statistiques des tâches';
$string['reports'] = 'Rapports';
$string['newer_grading_tooltip'] = 'Vérifiez l\'évaluation, car d\'autres sous-compétences ont été modifiées.';
$string['create_new_topic'] = 'Nouveau domaine de compétence';
$string['create_new_area'] = 'Nouveau domaine';
$string['really_delete'] = 'Vraiment supprimer ?';
$string['add_niveau'] = 'Ajouter un nouveau progrès d\'apprentissage';
$string['please_choose'] = 'Veuillez choisir';
$string['please_choose_preselection'] = 'Veuillez choisir les grilles desquelles vous souhaitez supprimer quelque chose.';
$string['delete_niveau'] = 'Ajouter suppression';
$string['add_new_taxonomie'] = 'ajouter un nouveau niveau de difficulté';
$string['taxonomy_was_deleted'] = 'Le niveau de difficulté a été supprimé';
$string['move_up'] = 'Déplacer vers le haut';
$string['move_down'] = 'Déplacer vers le bas';
$string['also_taxonomies_from_import'] = 'Afficher les niveaux de difficulté des imports';

// === Profil de compétences ===
$string['name'] = 'Nom';
$string['city'] = 'Lieu de résidence';
$string['total'] = 'Total';
$string['select_student'] = 'Sélectionnez un(e) participant(e) dont vous souhaitez voir le profil de compétences.';
$string['my_comps'] = 'Mes compétences';
$string['my_badges'] = 'Mes badges';
$string['innersection1'] = 'Vue d\'ensemble de la grille';
$string['innersection2'] = 'Statistiques';
$string['innersection3'] = 'Vue d\'ensemble des compétences et des tâches';
$string['childcompetencies_compProfile'] = 'Sous-compétences';
$string['materials_compProfile'] = 'Ressources pédagogiques';

// === Paramètres du profil de compétences ===
$string['profile_settings_choose_courses'] = 'Dans la Grille de compétences Exabis, les formateurs évaluent l\'acquisition de compétences dans différents domaines. Vous pouvez sélectionner ici les cours qui doivent apparaître dans le profil de compétences.';
$string['specificcontent'] = 'Domaines thématiques liés à l\'établissement';
$string['topic_3dchart'] = 'Diagramme 3D';
$string['topic_3dchart_empty'] = 'Aucune évaluation n\'est disponible pour ce domaine de compétence.';
// === Approfondissement ===
$string['profoundness_description'] = 'Description de la compétence';
$string['profoundness_basic'] = 'Compétence de base';
$string['profoundness_extended'] = 'Compétence étendue';
$string['profoundness_mainly'] = 'Majoritairement rempli';
$string['profoundness_entirely'] = 'Entièrement rempli';

// === Formateur externe & eLove ===
$string['block_exacomp_external_trainer_assign_head'] = 'Permettre l\'attribution de formateurs externes pour les participants';
$string['block_exacomp_external_trainer_assign_body'] = 'Requis pour l\'utilisation de l\'application elove';
$string['block_exacomp_dakora_language_file_head'] = 'Fichier de langue alternatif pour DAKORA';
$string['block_exacomp_dakora_language_file_body'] = 'Utilisez le <a href="https://exabis.at/sprachgenerator" target="_blank">générateur de langue</a> pour créer vos propres fichiers de langue';
$string['settings_dakora_timeout'] = 'Délai d\'attente Dakora (secondes)';
$string['settings_dakora_timeout_description'] = '';
$string['settings_dakora_url'] = 'URL vers l\'application Dakora';
$string['settings_dakora_url_description'] = '';
$string['settings_dakora_show_overview'] = 'Afficher la vue d\'ensemble';
$string['settings_dakora_show_overview_description'] = '';
$string['settings_dakora_show_eportfolio'] = 'Afficher l\'ePortfolio';
$string['settings_dakora_show_eportfolio_description'] = '';
$string['block_exacomp_elove_student_self_assessment_head'] = 'Autoriser l\'auto-évaluation pour les participants dans l\'application elove';
$string['block_exacomp_elove_student_self_assessment_body'] = '';
$string['block_exacomp_external_trainer_assign'] = 'Attribuer un formateur externe';
$string['block_exacomp_external_trainer'] = 'Formateur : ';
$string['block_exacomp_external_trainer_student'] = 'Apprenant : ';
$string['block_exacomp_external_trainer_allstudents'] = 'Tous les participants';

// === Thèmes transversaux ===
$string['add_drafts_to_course'] = 'Utiliser les modèles sélectionnés dans le cours';
$string['crosssubject'] = 'Thème';
$string['help_crosssubject'] = 'La composition du thème se fait pour l\'ensemble de l\'établissement via l\'onglet Grille de compétences. Vous pouvez masquer ici de manière spécifique au cours des compétences, des sous-compétences et du matériel pédagogique. Du matériel pédagogique peut être ajouté ici.';
$string['description'] = 'Description';
$string['numb'] = 'Numéro';
$string['no_student'] = '-- aucun(e) participant(e) sélectionné(e) --';
$string['no_student_edit'] = 'Mode édition';
$string['save_as_draft'] = 'Enregistrer le thème comme modèle';
$string['comps_and_material'] = 'Compétences et ressources pédagogiques';
$string['no_crosssubjs'] = 'Il n\'y a pas encore de thème dans ce cours.';
$string['delete_drafts'] = 'Supprimer les modèles sélectionnés';
$string['share_crosssub'] = 'Partager le thème avec les participants';
$string['share_crosssub_with_students'] = 'Partager le thème "{$a}" avec les participants suivants : ';
$string['share_crosssub_with_all'] = 'Partager le thème "{$a}" avec <b>tous</b> les participants : ';
$string['new_crosssub'] = 'Créer son propre thème';
$string['add_crosssub'] = 'Créer un thème';
$string['nocrosssubsub'] = 'Thèmes généraux';
$string['delete_crosssub'] = 'Supprimer le thème';
$string['confirm_delete'] = 'Voulez-vous vraiment supprimer ce thème ?';
$string['no_students_crosssub'] = 'Aucun participant n\'est attribué à ce thème.';
$string['use_available_crosssub'] = 'Créer un thème à partir d\'un modèle :';
$string['save_crosssub'] = 'Mettre à jour le thème';
$string['add_content_to_crosssub'] = 'Le thème n\'est pas encore rempli.';
$string['add_descriptors_to_crosssub'] = 'Lier les compétences avec le thème';
$string['manage_crosssubs'] = 'Retour à la vue d\'ensemble';
$string['show_course_crosssubs'] = 'Voir les thèmes du cours';
$string['existing_crosssub'] = 'Thèmes existants dans ce cours';
$string['create_new_crosssub'] = 'Créer un nouveau thème';
$string['share_crosssub_for_further_use'] = 'Partagez le thème avec les participants pour obtenir toutes les fonctionnalités.';
$string['available_crosssubjects'] = 'Thèmes de cours non partagés';
$string['crosssubject_drafts'] = 'Modèles de thèmes';
$string['de:Freigegebene Kursthemen'] = 'Thèmes de cours partagés';
$string['de:Freigabe bearbeiten'] = 'Modifier le partage';
$string['de:Kopie als Vorlage speichern'] = 'Enregistrer une copie comme modèle';
$string['de:Vorlage verwenden'] = '';
$string['crosssubject_files'] = 'Matériaux';
$string['new_niveau'] = 'nouveau progrès d\'apprentissage';
$string['groupcategory'] = 'Catégorie';
$string['new_column'] = 'nouvelle colonne';
$string['new_topic'] = 'nouveau domaine de compétence';

// === Associations ===
$string['competence_associations'] = 'Liens';
$string['competence_associations_explaination'] = 'La ressource pédagogique {$a} est liée aux compétences suivantes :';

// === Plan hebdomadaire ===
$string['weekly_schedule'] = 'Plan hebdomadaire';
$string['weekly_schedule_added'] = 'La tâche a été ajoutée au stockage de planification dans le plan hebdomadaire.';
$string['weekly_schedule_already_exists'] = 'La tâche est déjà dans le stockage de planification du plan hebdomadaire.';
$string['select_student_weekly_schedule'] = 'Sélectionnez un(e) participant(e) dont vous souhaitez voir le plan hebdomadaire.';
$string['example_pool'] = 'Stockage de planification';
$string['example_pool_example_button'] = 'dans le stockage de planification {$a->fullname}';
$string['example_pool_example_button_forall'] = 'dans le stockage de planification de tous les participants';
$string['example_trash'] = 'Corbeille';
$string['choosecourse'] = 'Sélectionner un cours : ';
$string['choosecoursetemplate'] = 'Veuillez sélectionner le cours dans lequel les activités Moodle de la grille de compétences seront importées : ';
$string['weekly_schedule_added_all'] = 'La tâche a été ajoutée au stockage de planification dans le plan hebdomadaire de tous les participants.';
$string['weekly_schedule_already_existing_for_one'] = 'La tâche est déjà dans le stockage de planification du plan hebdomadaire d\'au moins un(e) élève.';
$string['weekly_schedule_link_to_grid'] = 'Pour remplir le stockage de planification, veuillez passer à l\'onglet Grille de compétences.';
$string['pre_planning_storage'] = 'Stockage de planification';
$string['pre_planning_storage_popup_button'] = 'Distribuer le matériel';
$string['pre_planning_storage_example_button'] = 'dans mon stockage de planification';
$string['pre_planning_storage_added'] = 'La ressource pédagogique a été ajoutée au stockage de planification.';
$string['pre_planning_storage_already_contains'] = 'Ressource pédagogique déjà contenue dans le stockage de planification.';
$string['save_pre_planning_selection'] = 'Placer les ressources pédagogiques sélectionnées dans le plan hebdomadaire des élèves sélectionnés';
$string['empty_pre_planning_storage'] = 'Vider le stockage de planification';
$string['noschedules_pre_planning_storage'] = 'Le stockage de planification est vide. Veuillez ajouter de nouvelles ressources pédagogiques au stockage de planification via les grilles de compétences.';
$string['empty_trash'] = 'Vider la corbeille';
$string['empty_pre_planning_confirm'] = 'Les exemples ajoutés par un autre enseignant au stockage de planification seront également supprimés. Êtes-vous sûr ?';
$string['to_weekly_schedule'] = 'Vers le plan hebdomadaire';
$string['blocking_event'] = 'Créer un élément de blocage';
$string['blocking_event_title'] = 'Titre';
$string['blocking_event_create'] = 'Ajouter au stockage de planification';
$string['weekly_schedule_disabled'] = 'La ressource pédagogique est masquée et ne peut pas être placée dans le plan hebdomadaire.';
$string['pre_planning_storage_disabled'] = 'La ressource pédagogique est masquée et ne peut pas être placée dans le stockage de planification.';
$string['add_example_for_all_students_to_schedule'] = 'Attention : vous êtes sur le point de placer des ressources pédagogiques dans le plan hebdomadaire de tous les élèves. Une confirmation supplémentaire est nécessaire. Les modifications éventuelles ne pourront ensuite être apportées que sur les plans individuels des élèves respectifs.';
$string['add_example_for_group_to_schedule'] = 'Attention : vous êtes sur le point de placer des ressources pédagogiques dans le plan hebdomadaire du groupe sélectionné. Une confirmation supplémentaire est nécessaire. Les modifications éventuelles ne pourront ensuite être apportées que sur les plans individuels des élèves respectifs.';
$string['add_example_for_all_students_to_schedule_confirmation'] = 'Êtes-vous sûr de vouloir placer les ressources pédagogiques dans le plan hebdomadaire de tous les élèves ?';
$string['delete_ics_imports_confirmation'] = 'Êtes-vous sûr de vouloir supprimer les rendez-vous que vous avez importés pour le plan hebdomadaire sélectionné ?';
$string['import_ics_loading_time'] = 'Importation démarrée.';
$string['ics_provide_link_text'] = 'Veuillez fournir un lien.';
$string['add_example_for_group_to_schedule_confirmation'] = 'Êtes-vous sûr de vouloir placer les ressources pédagogiques dans le plan hebdomadaire du groupe sélectionné ?';
$string['participating_student'] = 'Participant';
$string['n1.unit'] = '1ère unité :';
$string['n2.unit'] = '2ème unité :';
$string['n3.unit'] = '3ème unité :';
$string['n4.unit'] = '4ème unité :';
$string['n5.unit'] = '5ème unité :';
$string['n6.unit'] = '6ème unité :';
$string['n7.unit'] = '7ème unité :';
$string['n8.unit'] = '8ème unité :';
$string['n9.unit'] = '9ème unité :';
$string['n10.unit'] = '10ème unité :';

// === Notifications ===
$string['notification_submission_subject'] = '{$a->site} : {$a->student} a soumis une solution pour la ressource pédagogique {$a->example}';
$string['notification_submission_subject_noSiteName'] = '{$a->student} a soumis une solution pour la ressource pédagogique {$a->example}';
$string['notification_submission_body'] = 'Madame, Monsieur {$a->receiver}, </br></br> {$a->student} a traité la tâche {$a->example} et l\'a téléversée le {$a->date} à {$a->time}. Le rendu peut être consulté dans l\'ePortfolio : <a href={$a->viewurl}{$a->example}</a> </br></br> Ce message a été généré par le site Moodle {$a->site}.';
$string['notification_submission_body_noSiteName'] = 'Madame, Monsieur {$a->receiver}, </br></br> {$a->student} a traité la tâche {$a->example} et l\'a téléversée le {$a->date} à {$a->time}. Le rendu peut être consulté dans l\'ePortfolio : <a href={$a->viewurl}{$a->example}</a> </br></br>';
$string['notification_submission_context'] = 'Rendu';
$string['notification_grading_subject'] = '{$a->site} : Nouvelles évaluations dans le cours {$a->course}';
$string['notification_grading_subject_noSiteName'] = 'Nouvelles évaluations dans le cours {$a->course}';
$string['notification_grading_body'] = 'Cher/Chère {$a->receiver}, </br></br> Tu as reçu de nouvelles évaluations dans le cours {$a->course} de {$a->teacher}.</br></br> Ce message a été généré par le site Moodle {$a->site}.';
$string['notification_grading_body_noSiteName'] = 'Cher/Chère {$a->receiver}, </br></br> Tu as reçu de nouvelles évaluations dans le cours {$a->course} de {$a->teacher}.</br></br>';
$string['notification_grading_context'] = 'Évaluation';
$string['notification_self_assessment_subject'] = '{$a->site} : Nouvelle auto-évaluation dans le cours {$a->course}';
$string['notification_self_assessment_body'] = 'Madame, Monsieur {$a->receiver}, </br></br> {$a->student} a effectué de nouvelles auto-évaluations dans le cours {$a->course}.</br></br> Ce message a été généré par le site Moodle {$a->site}.';
$string['notification_self_assessment_subject_noSiteName'] = 'Nouvelle auto-évaluation dans le cours {$a->course}';
$string['notification_self_assessment_body_noSiteName'] = 'Madame, Monsieur {$a->receiver}, </br></br> {$a->student} a effectué de nouvelles auto-évaluations dans le cours {$a->course}.</br></br>.';
$string['notification_self_assessment_context'] = 'Auto-évaluation';
$string['notification_example_comment_subject'] = '{$a->site} : Nouveau commentaire sur la tâche {$a->example}';
$string['notification_example_comment_body'] = 'Cher/Chère {$a->receiver}, </br></br> {$a->teacher} a commenté la tâche {$a->example} dans le cours {$a->course}.</br></br> Ce message a été généré par le site Moodle {$a->site}.';
$string['notification_example_comment_subject_noSiteName'] = 'Nouveau commentaire sur la tâche {$a->example}';
$string['notification_example_comment_body_noSiteName'] = 'Cher/Chère {$a->receiver}, </br></br> {$a->teacher} a commenté la tâche {$a->example} dans le cours {$a->course}.</br></br>';
$string['notification_example_comment_context'] = 'Commentaire';
$string['notification_weekly_schedule_subject'] = '{$a->site} : Nouvelle tâche dans le plan hebdomadaire';
$string['notification_weekly_schedule_subject_noSiteName'] = 'Nouvelle tâche dans le plan hebdomadaire';
$string['notification_weekly_schedule_body'] = 'Cher/Chère {$a->receiver}, </br></br>{$a->teacher} t\'a ajouté une nouvelle tâche dans le plan hebdomadaire du cours {$a->course}.</br></br> Ce message a été généré par le site Moodle {$a->site}.';
$string['notification_weekly_schedule_body_noSiteName'] = 'Cher/Chère {$a->receiver}, </br></br>{$a->teacher} t\'a ajouté une nouvelle tâche dans le plan hebdomadaire du cours {$a->course}.</br></br>';
$string['notification_weekly_schedule_context'] = 'Plan hebdomadaire';
$string['inwork'] = '{$a->inWork}/{$a->total} matériaux en cours';
$string['block_exacomp_notifications_head'] = 'Activer les notifications';
$string['block_exacomp_notifications_body'] = 'Des messages seront envoyés aux utilisateurs concernés lors d\'actions telles que la soumission de ressources pédagogiques ou une évaluation.';
$string['block_exacomp_assign_activities_old_method_head'] = 'Afficher l\'ancien onglet Attribuer des activités Moodle';
$string['block_exacomp_assign_activities_old_method_body'] = 'Cette fonctionnalité est couverte par le nouvel onglet "Lier les activités Moodle".';
$string['block_exacomp_disable_create_grid_head'] = 'Désactiver "Créer une nouvelle grille de compétences"';
$string['block_exacomp_disable_create_grid_body'] = 'Les utilisateurs ne pourront pas créer de nouvelles grilles';
$string['distribute_weekly_schedule'] = 'Distribuer le plan hebdomadaire';

// === Journalisation ===
$string['block_exacomp_logging_head'] = 'Activer la journalisation';
$string['block_exacomp_logging_body'] = 'Les actions pertinentes seront enregistrées.';
$string['eventscompetenceassigned'] = 'Compétence attribuée';
$string['eventsexamplesubmitted'] = 'Tâche soumise';
$string['eventsexamplegraded'] = 'Tâche évaluée';
$string['eventsexamplecommented'] = 'Tâche commentée';
$string['eventsexampleadded'] = 'Tâche ajoutée au plan hebdomadaire';
$string['eventsimportcompleted'] = 'Import effectué';
$string['eventscrosssubjectadded'] = 'Thème partagé';

// === Message ===
$string['messagetocourse'] = 'Envoyer un message à tous les participants';
$string['messageprovider:submission'] = 'Message lors d\'un nouveau rendu d\'élève';
$string['messageprovider:grading'] = 'Message lors d\'une nouvelle note';
$string['messageprovider:self_assessment'] = 'Message lors d\'une nouvelle auto-évaluation';
$string['messageprovider:weekly_schedule'] = 'L\'enseignant ajoute un exemple dans le plan hebdomadaire';
$string['messageprovider:comment'] = 'L\'enseignant commente un exemple';
$string['description_example'] = 'Description / Référence au manuel scolaire';
$string['submit_example'] = 'Soumettre';
// === Statut du service web ===
$string['enable_rest'] = 'Protocole REST non activé';
$string['access_roles'] = 'Rôles d\'utilisateur avec accès aux services web';
$string['no_permission'] = 'Autorisation non accordée';
$string['no_permission_user'] = 'Autorisation non accordée pour l\'utilisateur authentifié';
$string['description_createtoken'] = 'Accorder des droits supplémentaires au rôle d\'utilisateur "Utilisateur authentifié" : Administration du site/Utilisateurs/Modifier les droits/Gérer les rôles
4.1 Sélectionner Utilisateur authentifié
4.2 Sélectionner Modifier
4.3 Filtrer par "createtoken"
4.4 Autoriser moodle/webservice:createtoken';
$string['exacomp_not_found'] = 'Service Exacomp introuvable';
$string['exaport_not_found'] = 'Service Exaport introuvable';
$string['no_external_trainer'] = 'Aucun formateur externe attribué';
$string['periodselect'] = 'Sélection de la période de saisie';
$string['teacher'] = 'Enseignant';
$string['student'] = 'Élève';
$string['role:teacher'] = '';
$string['role:teachers'] = '';
$string['role:student'] = '';
$string['role:students'] = '';
$string['timeline_available'] = 'Disponible';
// === Rapports de groupe ===
$string['result'] = 'Résultat';
$string['evaluationdate'] = 'Date d\'évaluation';
$string['output_current_assessments'] = 'Affichage des évaluations respectives';
$string['student_assessment'] = 'Auto-évaluation';
$string['teacher_assessment'] = 'Retour de l\'enseignant';
$string['exa_evaluation'] = 'Évaluation de la ressource pédagogique';
$string['difficulty_group_report'] = 'Niveau';
$string['no_entries_found'] = 'Aucune entrée trouvée';
$string['assessment_date'] = 'Date d\'évaluation';
$string['number_of_found_students'] = 'Nombre d\'élèves trouvés';
$string['display_settings'] = 'Options d\'affichage';
$string['settings_explanation_tooltipp'] = 'Les résultats du rapport sont réduits par les filtres individuels de
        haut en bas, mais pas de bas en haut.
        Si, par exemple, le seul critère de filtre "Niveau G" est
        sélectionné pour les compétences, alors :
        - tous les standards éducatifs
        - tous les domaines de compétences
        - les compétences filtrées par évaluation avec "Niveau G" et
        - les sous-compétences attribuées aux compétences de niveau G seront affichées.';
$string['create_report'] = 'Créer un rapport';
$string['students_competences'] = 'Compétences des élèves';
$string['number_of_students'] = 'Nombre d\'élèves';
$string['no_specification'] = 'pas encore d\'évaluation';
$string['period'] = 'Intervalle de temps';
$string['from'] = 'de';
$string['to'] = 'à';
$string['report_type'] = 'Type de rapport';
$string['report_subject'] = 'Standard éducatif/Grille';
$string['report_learniningmaterial'] = 'Ressource pédagogique';
$string['report_competencefield'] = 'Domaine de compétence';
$string['all_students'] = 'Tous les élèves';
$string['export_all_standards'] = 'Exporter toutes les grilles de compétences de cette instance Moodle';
$string['exportieren'] = 'Exporter';
$string['export_selective'] = 'Export sélectif';
$string['select_all'] = 'tout sélectionner';
$string['deselect_all'] = 'tout désélectionner';
$string['new'] = 'nouveau';
$string['import_used_preselected_from_previous'] = 'Si un fichier XML a déjà été importé auparavant, les mêmes préréglages de la source de données seront utilisés';
$string['import_from_related_komet'] = 'Importer/mettre à jour maintenant la grille de compétences depuis KOMET associé';
$string['import_from_related_komet_help'] = 'Si la mise à jour automatique des grilles de compétences via KOMET est activée dans les paramètres généraux, cette mise à jour peut être effectuée immédiatement via cette option.<br>
        La mise à jour automatique se fait via Administration du site - Plugins - Blocs - Grille de compétences Exabis : URL du serveur';
$string['import_activate_scheduled_tasks'] = 'Activer les tâches';

// === API ====
$string['yes_no_No'] = 'Non';
$string['yes_no_Yes'] = 'Oui';
$string['grade_Verygood'] = 'très bien';
$string['grade_good'] = 'bien';
$string['grade_Satisfactory'] = 'satisfaisant';
$string['grade_Sufficient'] = 'suffisant';
$string['grade_Deficient'] = 'insuffisant';
$string['grade_Insufficient'] = 'très insuffisant';
$string['import_select_file'] = 'Sélectionner le fichier :';
$string['import_selectgrids_needed'] = 'Sélectionner les matières pour l\'importation :';
$string['import_category_mapping_needed'] = 'La grille de compétences importée contient un concept de niveau différent de celui de votre école. Les entrées de niveau correspondantes seront supprimées. Vous pouvez les modifier vous-même par la suite.';
$string['import_category_mapping_column_xml'] = 'Niveau';
$string['import_category_mapping_column_exacomp'] = 'sera modifié en';
$string['import_category_mapping_column_level'] = 'Niveau';
$string['import_category_mapping_column_level_descriptor'] = 'Compétence / Sous-compétence';
$string['import_category_mapping_column_level_example'] = 'Matériel';
$string['import_mapping_as_is'] = 'continuer à utiliser tel quel';
$string['import_mapping_delete'] = 'Supprimer';
$string['import_selectschooltypes_needed'] = 'Veuillez attribuer la ou les grilles importées à un type d\'école.';
$string['import_schooltype_mapping_column_grid'] = 'Grille';
$string['import_schooltype_mapping_column_schooltype'] = 'Attribuer le type d\'école';
$string['import_schooltype_mapping_for_all'] = 'attribuer ce type d\'école à toutes les grilles importées';
$string['import_teacher_next_step'] = 'L\'importation est terminée. <a href="{$a->url}" title="{$a->title}">Cliquez ici pour activer la grille dans votre cours.</a>';
$string['save'] = 'Enregistrer';
$string['add_competence_insert_learning_progress'] = 'Pour insérer une compétence, vous devez d\'abord sélectionner ou ajouter un niveau de difficulté !';
$string['delete_level_from_another_source'] = 'La grille de compétences importée contient des contenus d\'une autre source. Si vous supprimez ici, cela sera également supprimé de l\'autre source ! Ne supprimez que si vous êtes sûr !';
$string['delete_level_has_children_from_another_source'] = 'La grille de compétences importée a été modifiée dans cette installation. Ces ajouts doivent être identifiés avant la suppression. Sinon, les contenus d\'autres grilles seront potentiellement supprimés si vous supprimez cette grille !';
$string['delete_competency_that_has_gradings'] = 'Cette compétence a déjà des évaluations ! Ne supprimez que si vous êtes sûr !';
$string['delete_competency_that_has_children_with_gradings'] = 'Les sous-compétences de cette compétence ont déjà des évaluations ! Ne supprimez que si vous êtes sûr !';
$string['delete_competency_that_is_used_in_course'] = 'Attention ! Cette grille est utilisée dans les cours suivants : ';
$string['module_used_availabilitycondition_competences'] = 'Atteindre automatiquement les compétences Exabis liées lorsque les conditions sont remplies.';
$string['use_isglobal'] = 'Cours transversal';
$string['globalgradings'] = 'Évaluations transversales';
$string['assign_dakora_teacher'] = 'Attribuer un enseignant pour les compétences transversales';
$string['assign_dakora_teacher_link'] = 'Cliquez ici pour attribuer un enseignant pour les compétences transversales';
$string['transferable_skills'] = 'Compétences transversales';

//Dakora strings
$string['dakora_string1'] = 'chaîne française1';
$string['dakora_string2'] = 'chaîne française2';
$string['dakora_string3'] = 'chaîne française3';
$string['dakora_niveau_after_descriptor_title'] = 'Niveau';
$string['active_show'] = 'actif (afficher)';
$string['donotleave_page_message'] = 'Vous avez des modifications non enregistrées sur cette page. Voulez-vous quitter cette page et perdre vos modifications ou rester sur cette page ?';
$string['privacy:metadata:block_exacompcompuser'] = 'Stockage des évaluations des étudiants';
$string['privacy:metadata:block_exacompcompuser:userid'] = 'Étudiant qui a été évalué';
$string['privacy:metadata:block_exacompcompuser:compid'] = 'Compétence qui a été évaluée';
$string['privacy:metadata:block_exacompcompuser:reviewerid'] = 'Évaluateur qui a évalué';
$string['privacy:metadata:block_exacompcompuser:role'] = 'Rôle de l\'évaluateur qui a évalué';
$string['privacy:metadata:block_exacompcompuser:courseid'] = 'Cours';
$string['privacy:metadata:block_exacompcompuser:value'] = 'Résultat de l\'évaluation';
$string['privacy:metadata:block_exacompcompuser:comptype'] = 'Type de compétence évaluée';
$string['privacy:metadata:block_exacompcompuser:timestamp'] = 'Date de l\'évaluation';
$string['privacy:metadata:block_exacompcompuser:additionalinfo'] = 'Résultat de l\'évaluation';
$string['privacy:metadata:block_exacompcompuser:evalniveauid'] = 'Niveau de difficulté de l\'évaluation';
$string['privacy:metadata:block_exacompcompuser:gradingisold'] = 'est-elle ancienne ?';
$string['privacy:metadata:block_exacompcompuser:globalgradings'] = 'valeur globale';
$string['privacy:metadata:block_exacompcompuser:gradinghistory'] = 'historique de notation';
$string['privacy:metadata:block_exacompcompuser:personalisedtext'] = 'texte personnalisé supplémentaire';
$string['privacy:metadata:block_exacompcmassign'] = 'Stockage pour le mécanisme de notation automatique : ne nécessite pas d\'exportation';
$string['privacy:metadata:block_exacompcmassign:coursemoduleid'] = 'Identifiant du module de cours';
$string['privacy:metadata:block_exacompcmassign:userid'] = 'Identifiants des étudiants';
$string['privacy:metadata:block_exacompcmassign:timemodified'] = 'horodatage';
$string['privacy:metadata:block_exacompcmassign:relateddata'] = 'Données liées à l\'étudiant';
$string['privacy:metadata:block_exacompexameval'] = 'Stockage des évaluations des étudiants (exemples)';
$string['privacy:metadata:block_exacompexameval:exampleid'] = 'Exemple';
$string['privacy:metadata:block_exacompexameval:courseid'] = 'Cours';
$string['privacy:metadata:block_exacompexameval:studentid'] = 'Étudiant qui a été évalué';
$string['privacy:metadata:block_exacompexameval:teacher_evaluation'] = 'Valeur d\'évaluation de l\'enseignant';
$string['privacy:metadata:block_exacompexameval:additionalinfo'] = 'Valeur d\'évaluation de l\'enseignant (utilisée pour certains types d\'évaluation)';
$string['privacy:metadata:block_exacompexameval:teacher_reviewerid'] = 'Enseignant qui a évalué';
$string['privacy:metadata:block_exacompexameval:timestamp_teacher'] = 'Heure de l\'évaluation de l\'enseignant';
$string['privacy:metadata:block_exacompexameval:student_evaluation'] = 'Auto-évaluation';
$string['privacy:metadata:block_exacompexameval:timestamp_student'] = 'Heure de l\'auto-évaluation';
$string['privacy:metadata:block_exacompexameval:evalniveauid'] = 'Niveau';
$string['privacy:metadata:block_exacompexameval:resubmission'] = 'la re-soumission est autorisée/non autorisée';
$string['privacy:metadata:block_exacompcrossstud_mm'] = 'Partager les sujets transversaux avec les étudiants';
$string['privacy:metadata:block_exacompcrossstud_mm:crosssubjid'] = 'Identifiant du sujet transversal';
$string['privacy:metadata:block_exacompcrossstud_mm:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompdescrvisibility'] = 'Descripteurs de visibilité pour les utilisateurs';
$string['privacy:metadata:block_exacompdescrvisibility:courseid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacompdescrvisibility:descrid'] = 'Identifiant de compétence';
$string['privacy:metadata:block_exacompdescrvisibility:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompdescrvisibility:visible'] = 'Marqueur de visibilité';
$string['privacy:metadata:block_exacompexampvisibility'] = 'Exemples de visibilité pour les utilisateurs';
$string['privacy:metadata:block_exacompexampvisibility:courseid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacompexampvisibility:exampleid'] = 'Identifiant du matériel';
$string['privacy:metadata:block_exacompexampvisibility:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompexampvisibility:visible'] = 'Marqueur de visibilité';
$string['privacy:metadata:block_exacompexternaltrainer'] = 'Formateurs externes pour les étudiants';
$string['privacy:metadata:block_exacompexternaltrainer:trainerid'] = 'Formateur';
$string['privacy:metadata:block_exacompexternaltrainer:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompprofilesettings'] = 'quel cours inclure dans le profil de compétences';
$string['privacy:metadata:block_exacompprofilesettings:itemid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacompprofilesettings:userid'] = 'Étudiant';
$string['privacy:metadata:block_exacompprofilesettings:block'] = 'bloc associé : exacomp, exastud ou exaport';
$string['privacy:metadata:block_exacompprofilesettings:feedback'] = 'le retour verbal doit être affiché (pour les évaluations exastud)';
$string['privacy:metadata:block_exacompschedule'] = 'exemples, ajoutés à la liste de planification de l\'étudiant';
$string['privacy:metadata:block_exacompschedule:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompschedule:exampleid'] = 'Exemple planifié';
$string['privacy:metadata:block_exacompschedule:creatorid'] = 'Créateur de l\'enregistrement planifié';
$string['privacy:metadata:block_exacompschedule:timecreated'] = 'Heure de création de l\'enregistrement';
$string['privacy:metadata:block_exacompschedule:timemodified'] = 'Heure de modification de l\'enregistrement';
$string['privacy:metadata:block_exacompschedule:courseid'] = 'Cours';
$string['privacy:metadata:block_exacompschedule:sorting'] = 'Tri des enregistrements';
$string['privacy:metadata:block_exacompschedule:start'] = 'Heure de début';
$string['privacy:metadata:block_exacompschedule:endtime'] = 'Heure de fin';
$string['privacy:metadata:block_exacompschedule:deleted'] = 'Marqueur d\'enregistrement supprimé';
$string['privacy:metadata:block_exacompschedule:distributionid'] = 'identifiant de distribution';
$string['privacy:metadata:block_exacompschedule:source'] = 'S/T comme type';
$string['privacy:metadata:block_exacompsolutvisibility'] = 'quelles solutions d\'exemples sont visibles';
$string['privacy:metadata:block_exacompsolutvisibility:courseid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacompsolutvisibility:exampleid'] = 'Identifiant de l\'exemple';
$string['privacy:metadata:block_exacompsolutvisibility:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacompsolutvisibility:visible'] = 'marqueur visible';
$string['privacy:metadata:block_exacomptopicvisibility'] = 'quels sujets sont visibles';
$string['privacy:metadata:block_exacomptopicvisibility:courseid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacomptopicvisibility:topicid'] = 'Identifiant du sujet';
$string['privacy:metadata:block_exacomptopicvisibility:studentid'] = 'Étudiant';
$string['privacy:metadata:block_exacomptopicvisibility:visible'] = 'marqueur visible';
$string['privacy:metadata:block_exacomptopicvisibility:niveauid'] = 'Identifiant du niveau';
$string['privacy:metadata:block_exacompcrosssubjects'] = 'Sujets transversaux, créés par l\'utilisateur';
$string['privacy:metadata:block_exacompcrosssubjects:title'] = 'Titre';
$string['privacy:metadata:block_exacompcrosssubjects:description'] = 'Description';
$string['privacy:metadata:block_exacompcrosssubjects:courseid'] = 'Identifiant du cours';
$string['privacy:metadata:block_exacompcrosssubjects:creatorid'] = 'identifiant du créateur';
$string['privacy:metadata:block_exacompcrosssubjects:shared'] = 'partagé ou non';
$string['privacy:metadata:block_exacompcrosssubjects:subjectid'] = 'identifiant de la matière associée';
$string['privacy:metadata:block_exacompcrosssubjects:groupcategory'] = 'catégorie de groupe';
$string['privacy:metadata:block_exacompglobalgradings'] = 'Texte de note globale pour une matière/sujet/compétence';
$string['privacy:metadata:block_exacompglobalgradings:userid'] = 'Identifiant de l\'étudiant';
$string['privacy:metadata:block_exacompglobalgradings:compid'] = 'identifiant de compétence';
$string['privacy:metadata:block_exacompglobalgradings:comptype'] = 'type de compétence : 0 - descripteur ; 1 - sujet';
$string['privacy:metadata:block_exacompglobalgradings:globalgradings'] = 'contenu de la notation globale';
$string['privacy:metadata:block_exacompwsdata'] = 'données temporaires pour les services web';
$string['privacy:metadata:block_exacompwsdata:token'] = 'valeur du jeton';
$string['privacy:metadata:block_exacompwsdata:userid'] = 'Utilisateur';
$string['privacy:metadata:block_exacompwsdata:data'] = 'contenu des données';
$string['OR'] = 'OU';
$string['AND'] = 'ET';
$string['AND teacherevaluation from'] = 'ET évaluation de l\'enseignant de';
$string['report all educational standards'] = 'Tous les standards éducatifs/grilles qui correspondent aux critères de filtre suivants : ';
$string['report all topics'] = 'Tous les domaines de compétences des standards éducatifs/grilles qui n\'ont pas été filtrés ET correspondent aux critères de filtre suivants : ';
$string['report all descriptor parents'] = 'Toutes les compétences des domaines de compétences qui n\'ont pas été filtrés ET correspondent aux critères de filtre suivants : ';
$string['report all descriptor children'] = 'Toutes les sous-compétences des compétences qui n\'ont pas été filtrées ET correspondent aux critères de filtre suivants : ';
$string['report all descriptor examples'] = 'Tous les matériels pédagogiques des domaines de compétences, compétences et sous-compétences qui n\'ont pas été filtrés ET correspondent aux critères de filtre suivants : ';
$string['filterlogic'] = 'Critères de filtre : ';
$string['topic_description'] = 'Désignation de la première ligne (par ex. domaine de compétences)';
$string['niveau_description'] = 'Désignation de la première colonne (par ex. LFS 1)';
$string['descriptor_description'] = 'Entrée de la première cellule (par ex. description de compétence)';
$string['selectcourse_filter'] = 'Filtre';
$string['selectcourse_filter_schooltype'] = 'Type d\'école';
$string['selectcourse_filter_onlyselected'] = 'Afficher uniquement les grilles sélectionnées';
$string['selectcourse_filter_submit'] = 'Filtrer';
$string['selectcourse_filter_emptyresult'] = 'Aucun résultat pour ce filtre';
$string['descriptor_label'] = 'Titre de compétence';
$string['export_password_message'] = 'Veuillez noter le mot de passe "<strong>{$a}</strong>" avant de continuer.<br/><br/>
		Remarque : Les fichiers zip protégés par mot de passe peuvent être ouverts sous Windows, mais les fichiers à l\'intérieur du fichier zip ne peuvent être extraits qu\'avec un programme externe (par ex. 7-Zip).
		';
$string['settings_heading_security'] = 'Sécurité';
$string['settings_heading_security_description'] = '';
$string['settings_example_upload_global'] = 'Téléchargement global de matériels';
$string['settings_example_upload_global_description'] = 'Les matériels téléchargés par les enseignants sont disponibles globalement. Les matériels sont ainsi également visibles dans d\'autres cours avec la même grille.';
$string['settings_show_teacherdescriptors_global'] = 'Afficher globalement les compétences créées par soi-même';
$string['settings_show_teacherdescriptors_global_description'] = 'Les comp��tences créées par les enseignants sont disponibles globalement. Les compétences sont ainsi également visibles dans d\'autres cours avec la même grille.';
$string['settings_teacher_can_import_grid'] = 'L\'enseignant peut télécharger des grilles de compétences';
$string['settings_teacher_can_import_grid_description'] = '';
$string['settings_export_password'] = 'Protection par mot de passe (cryptage AES-256) pour la sauvegarde des grilles de compétences';
$string['settings_export_password_description'] = '(Disponible uniquement à partir de la version php 7.2)';
$string['pre_planning_materials_assigned'] = 'Les matériels sélectionnés ont été attribués aux étudiants/groupes sélectionnés.';
$string['grade_example_related'] = 'Évaluer les compétences et matériels liés.';
$string['freematerials'] = 'Matériels libres';
$string['radargraphtitle'] = 'Diagramme en radar';
$string['radargrapherror'] = 'Le graphique radar ne peut être affiché qu\'avec 3 à 13 axes';
$string['studentcomp'] = 'Compétences atteintes selon l\'auto-évaluation';
$string['teachercomp'] = 'Compétences atteintes';
$string['pendingcomp'] = 'Compétences en attente';
$string['topicgrading'] = 'Évaluation globale du sujet : ';
$string['import_ics_title'] = 'Import WebUntis';
$string['hide_imports_checkbox_label'] = 'Afficher WebUntis : ';
$string['import_ics'] = 'Importer le calendrier';
$string['delete_imports'] = 'Supprimer les rendez-vous importés';
$string['upload_ics_file'] = 'Sélectionner le fichier : ';
$string['is_teacherexample'] = 'Matériel de l\'enseignant';
$string['delete...'] = 'Supprimer...';
$string['display'] = 'afficher';
$string['data_imported_title'] = 'Importer les données maintenant';
$string['competence_overview_teacher_short'] = 'E :';
$string['competence_overview_student_short'] = 'É :';
$string['filterClear'] = 'Effacer le filtre';
$string['editor'] = 'Révisé par';
$string['hide_for_all_students'] = 'masquer pour tous les participants';
$string['tab_teacher_settings_course_assessment'] = 'Évaluation spécifique au cours';
$string['tab_teacher_settings_gridimport'] = 'Import de grille de compétences';
$string['course_assessment_config_infotext'] = 'Sélectionnez le schéma d\'évaluation souhaité.';
$string['course_assessment_use_global'] = 'Utiliser les paramètres d\'évaluation globaux';
$string['course_assessment_settings'] = 'Évaluation spécifique au cours';
$string['close'] = 'Fermer';
$string['opencomps'] = 'Sélectionner les compétences';
$string['expandcomps'] = 'Tout ouvrir';
$string['contactcomps'] = 'Tout fermer';
$string['questlink'] = 'Associer les questions';
$string['select_subjects'] = 'Sélectionner les grilles';
$string['overview_examples_report_title'] = 'Aperçu des tâches pour l\'acquisition de compétences';
$string['block_exacomp_link_to_dakora_app'] = 'vers l\'application Dakora';
$string['diggrapp_cannotcreatetoken'] = 'Impossible d\'accéder à cette installation moodle';
$string['grid_creating_is_disabled'] = 'La création de nouvelles grilles est désactivée !';
$string['save_hvp_activity'] = 'Enregistrer l\'activité HVP';
$string['edulevel_without_assignment_title'] = 'sans attribution fixe';
$string['schooltype_without_assignment_title'] = 'sans attribution fixe';
$string['please_select_topic_first'] = 'Veuillez d\'abord sélectionner un domaine de compétences dans la barre de gauche';
$string['no_course_templates'] = 'Impossible de trouver un cours pouvant être utilisé comme modèle';
$string['preselect_delete_subject_because_it_is_disabled'] = 'Présélectionné pour la suppression car cette matière est désactivée dans Komet';
$string['preselect_delete_subject_because_it_was_not_imported_in_last_import'] = 'Présélectionné pour la suppression car cette matière n\'était plus présente dans la dernière importation, mais est encore utilisée dans des cours dans cette installation Moodle, ou a des évaluations, ou contient des contenus d\'autres sources.';
$string['delete_grids_missing_from_xmlserverurl'] = 'Supprimer les données lors de la synchronisation avec xmlserverurl';
$string['delete_grids_missing_from_xmlserverurl_description'] = 'Toutes les grilles qui ne sont pas présentes dans Komet (xmlserverurl) seront supprimées, sauf si elles sont déjà en cours d\'utilisation.';
$string['no_examples_in_this_grid'] = 'Il n\'y a pas de matériels pédagogiques dans cette grille';
$string['source_delete_info'] = 'Données à supprimer, importées depuis <strong>"{$a}"</strong>, ';
