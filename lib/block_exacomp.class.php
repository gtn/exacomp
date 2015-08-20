<?php

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../moodleblock.class.php';
require_once __DIR__ . '/lib.php';

class block_exacomp extends block_list {
    /**
     * DATABSE TABLE NAMES
     */
    const DB_SKILLS = 'block_exacompskills';
    const DB_NIVEAUS = 'block_exacompniveaus';
    const DB_TAXONOMIES = 'block_exacomptaxonomies';
    const DB_EXAMPLES = 'block_exacompexamples';
    const DB_EXAMPLEEVAL = 'block_exacompexameval';
    const DB_DESCRIPTORS = 'block_exacompdescriptors';
    const DB_DESCEXAMP = 'block_exacompdescrexamp_mm';
    const DB_EDULEVELS = 'block_exacompedulevels';
    const DB_SCHOOLTYPES = 'block_exacompschooltypes';
    const DB_SUBJECTS = 'block_exacompsubjects';
    const DB_TOPICS = 'block_exacomptopics';
    const DB_COURSETOPICS = 'block_exacompcoutopi_mm';
    const DB_DESCTOPICS = 'block_exacompdescrtopic_mm';
    const DB_CATEGORIES = 'block_exacompcategories';
    const DB_COMPETENCE_ACTIVITY = 'block_exacompcompactiv_mm';
    const DB_COMPETENCIES = 'block_exacompcompuser';
    const DB_COMPETENCIES_USER_MM = 'block_exacompcompuser_mm';
    const DB_SETTINGS = 'block_exacompsettings';
    const DB_MDLTYPES = 'block_exacompmdltype_mm';
    const DB_DESCBADGE = 'block_exacompdescbadge_mm';
    const DB_PROFILESETTINGS = 'block_exacompprofilesettings';
    const DB_CROSSSUBJECTS = 'block_exacompcrosssubjects';
    const DB_DESCCROSS = 'block_exacompdescrcross_mm';
    const DB_CROSSSTUD = 'block_exacompcrossstud_mm';
    const DB_DESCVISIBILITY = 'block_exacompdescrvisibility';
    const DB_DESCCAT = 'block_exacompdescrcat_mm';
    const DB_EXAMPTAX = 'block_exacompexampletax_mm';    
    const DB_DATASOURCES = 'block_exacompdatasources';
    const DB_SCHEDULE = 'block_exacompschedule';
    const DB_EXAMPVISIBILITY = 'block_exacompexampvisibility';
    
    /**
     * PLUGIN ROLES
     */
    const ROLE_TEACHER = 1;
    const ROLE_STUDENT = 0;
    
    /**
     * COMPETENCE TYPES
     */
    const TYPE_DESCRIPTOR = 0;
    const TYPE_TOPIC = 1;
    const TYPE_CROSSSUB = 2;
    
    const SETTINGS_MAX_SCHEME = 10;
    const EXAMPLE_SOURCE_TEACHER = 3;
    const EXAMPLE_SOURCE_USER = 4;
    
    const IMPORT_SOURCE_DEFAULT = 1;
    const IMPORT_SOURCE_SPECIFIC = 2;
    
    const CUSTOM_CREATED_DESCRIPTOR = 3;







    function init() {
        $this->title = get_string('pluginname', 'block_exacomp');
    }

    function get_content() {
        global $CFG, $USER, $COURSE, $DB, $OUTPUT, $version, $skillmanagement, $usebadges;

        //does not work with global var, don't know why TODO
        $usebadges = get_config('exacomp', 'usebadges');
        $skillmanagement = get_config('exacomp', 'skillmanagement');
        $version = get_config('exacomp', 'alternativedatamodel');

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->icons = array();
        $this->content->items = array();


        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);
        $globalcontext = context_system::instance();

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }

        $courseid = intval($COURSE->id);

        if($skillmanagement)
            $checkConfig = block_exacomp_is_configured($courseid);
        else
            $checkConfig = block_exacomp_is_configured();
        	
        $checkImport = $DB->get_records('block_exacompdescriptors');

        $courseSettings = block_exacomp_get_settings_by_course($courseid);
        $usedetailpage = $courseSettings->usedetailpage;
        $useactivities = $courseSettings->uses_activities;

        $ready_for_use = block_exacomp_is_ready_for_use($courseid);

        $de = false;
        $lang = current_language();
        if(isset($lang) && substr( $lang, 0, 2) === 'de'){
            $de = true;
        }

        //if use skill management
        if($skillmanagement && block_exacomp_is_teacher($currentcontext)){
            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/skillmanagement.php', array('courseid'=>$courseid)), get_string('tab_skillmanagement', 'block_exacomp'), array('title'=>get_string('tab_skillmanagement', 'block_exacomp')));
            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
        }

        //if checkImport && checkSubjects -> Modul wurde konfiguriert
        //else nur admin sieht block und hat nur den link Modulkonfiguration
        if((has_capability('block/exacomp:admin', $globalcontext))){	//Admin sieht immer Modulkonfiguration
            //Modulkonfiguration
            if(!$skillmanagement){
                //Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
                if($checkImport){
                    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_config.php', array('courseid'=>$courseid)), get_string('tab_admin_configuration', 'block_exacomp'), array('title'=>get_string('tab_admin_configuration', 'block_exacomp')));
                    $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
                }else{
                    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string('tab_admin_configuration', 'block_exacomp'), array('title'=>get_string('tab_admin_configuration', 'block_exacomp')));
                    $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/module_config.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
                }
                if(get_config('exacomp','external_trainer_assign') != false) {
                    $this->content->items[]='<a title="' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '" href="' . $CFG->wwwroot . '/blocks/exacomp/externaltrainers.php?courseid=' . $COURSE->id . '">' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '</a>';
                    $this->content->icons[]='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/personal.png" height="16" width="23" alt="'.get_string("block_exacomp_external_trainer_assign", "block_exacomp").'" />';
                }
            }
        }

        if($checkConfig && $checkImport){	//Modul wurde konfiguriert
            if (block_exacomp_is_teacher($currentcontext) && $courseid != 1){
                $crosssubs = block_exacomp_cross_subjects_exists()?block_exacomp_get_cross_subjects_by_course($courseid):false;
                //teacher LIS
                if($version){
                    if(block_exacomp_is_activated($courseid)){
                        //Kompetenzraster
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                    if($ready_for_use){
                        //Kompetenzüberblick
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));

                        //cross subjects
                        if($crosssubs)
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
                        else
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));

                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));

                        if($courseSettings->nostudents != 1) {
                            //Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
                            if ($courseSettings->uses_activities && $usedetailpage){
                                $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));
                                $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                            }

                            //Kompetenzprofil
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

                            //Beispiel-Aufgaben
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/view_examples.php', array('courseid'=>$courseid)), get_string('tab_examples', 'block_exacomp'), array('title'=>get_string('tab_examples', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/examples_and_tasks.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

                            //Lernagenda
                            //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
                            //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        
                            //Wochenplan
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid'=>$courseid)), get_string('tab_weekly_schedule', 'block_exacomp'), array('title'=>get_string('tab_weekly_schedule', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                       
                        }
                        //Meine Auszeichnungen
                        //if (block_exacomp_moodle_badges_enabled() && $usebadges) {
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        //}
                    }
                    //Einstellungen
                    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string('tab_teacher_settings', 'block_exacomp'), array('title'=>get_string('tab_teacher_settings', 'block_exacomp')));
                    $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subjects_topics.gif'), 'alt'=>"", 'height'=>16, 'width'=>23));

                    	
                    if($de && !$skillmanagement){
                        //Hilfe
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                }else{	//teacher !LIS
                    if($ready_for_use){
                        //Kompetenzüberblick
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));

                        //cross subjects
                        if($crosssubs)
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
                        else
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));

                        //Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
                        if ($courseSettings->uses_activities && $usedetailpage){
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        }
                    }
                    if(block_exacomp_is_activated($courseid)){
                        //Kompetenzraster
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                    if($ready_for_use && $courseSettings->nostudents != 1){
                        //Kompetenzprofil
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

                        //Beispiel-Aufgaben
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/view_examples.php', array('courseid'=>$courseid)), get_string('tab_examples', 'block_exacomp'), array('title'=>get_string('tab_examples', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/examples_and_tasks.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        	
                        //Lernagenda
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        	
                        //Wochenplan
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid'=>$courseid)), get_string('tab_weekly_schedule', 'block_exacomp'), array('title'=>get_string('tab_weekly_schedule', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                       
                        if($courseSettings->profoundness == 1) {
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/profoundness.php', array('courseid'=>$courseid)), get_string('tab_profoundness', 'block_exacomp'), array('title'=>get_string('tab_profoundness', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        }
                        //Meine Auszeichnungen
                        //if (block_exacomp_moodle_badges_enabled() && $usebadges) {
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        //}
                    }

                    $settings_string = get_string('tab_teacher_settings', 'block_exacomp');
                    //if($skillmanagement)
                    //$settings_string = get_string('tab_teacher_demo_settings', 'block_exacomp');
                    //Einstellungen
                    $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), $settings_string, array('title'=>get_string('tab_teacher_settings', 'block_exacomp')));
                    $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subjects_topics.gif'), 'alt'=>"", 'height'=>16, 'width'=>23));

                    if($de && !$skillmanagement){
                        //Hilfe
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                }
            }else if (has_capability('block/exacomp:student', $currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $currentcontext)){
                $crosssubs = block_exacomp_cross_subjects_exists()?block_exacomp_get_cross_subjects_by_course($courseid, $USER->id):false;
                //student LIS
                if($version){
                    if(block_exacomp_is_activated($courseid)){
                        //Kompetenzraster
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                    if($ready_for_use){
                        //Kompetenz�berblick
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
                        	
                        //Cross subjects
                        if($crosssubs){
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
                        }
                        if($courseSettings->nostudents != 1) {

                            //Kompetenz-Detailansicht
                            if ($courseSettings->uses_activities && $usedetailpage){
                                $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));
                                $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                            }

                            //Lernagenda
                            //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
                            //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

                            //Wochenplan
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid'=>$courseid)), get_string('tab_weekly_schedule', 'block_exacomp'), array('title'=>get_string('tab_weekly_schedule', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                       
                            //Kompetenzprofil
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        }
                        //Meine Auszeichnungen
                        //if (block_exacomp_moodle_badges_enabled() && $usebadges) {
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        //}
                    }
                    if($de && !$skillmanagement){
                        //Hilfe
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                }else{ //student !LIS
                    if($ready_for_use){
                        //Kompetenz�berblick
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid'=>$courseid)), get_string('tab_competence_overview','block_exacomp'), array('title'=>get_string('tab_competence_overview','block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));

                        //Cross subjects
                        if($crosssubs){
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid)), get_string('tab_cross_subjects','block_exacomp'), array('title'=>get_string('tab_cross_subjects','block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>'23'));
                        }

                        if($courseSettings->nostudents != 1) {
                            //Kompetenz-Detailansicht
                            if ($courseSettings->uses_activities && $usedetailpage){
                                $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid'=>$courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title'=>get_string('tab_competence_details', 'block_exacomp')));
                                $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                            }

                            //Kompetenzprofil
                            $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid'=>$courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title'=>get_string('tab_competence_profile', 'block_exacomp')));
                            $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/area.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                            	
                        }
                    }
                    if(block_exacomp_is_activated($courseid)){
                        //Kompetenzraster
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                    if($ready_for_use && $courseSettings->nostudents != 1){
                        //Lernagenda
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));

                        //Wochenplan
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid'=>$courseid)), get_string('tab_weekly_schedule', 'block_exacomp'), array('title'=>get_string('tab_weekly_schedule', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                       
                        //Meine Auszeichnungen
                        //if (block_exacomp_moodle_badges_enabled() && $usebadges) {
                        //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
                        //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                        //}
                    }
                    	
                    if($de && !$skillmanagement){
                        //Hilfe
                        $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid'=>$courseid)), get_string('tab_help', 'block_exacomp'), array('title'=>get_string('tab_help', 'block_exacomp')));
                        $this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/info.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                    }
                }
            }
        } else {
            if (block_exacomp_is_teacher($currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $globalcontext)){
                $this->content->items[] = get_string('admin_config_pending','block_exacomp');
                $this->content->icons[] = '';
            }
        }
        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }

    /**
     * This function is executed by the Moodle cron job.
     * It checks if an url for updating the data-xml file is specified and in this case
     * it tries to get the content and update the local xml.
     */
    public function cron() {
        global $COURSE, $DB, $xmlserverurl, $autotest, $testlimit;
        $xmlserverurl = get_config('exacomp', 'xmlserverurl');

        mtrace('Exabis Competencies: cron job is running.');

        //import xml with provided server url
        if($xmlserverurl) {
            $xml = file_get_contents($xmlserverurl);
            if($xml) {
                require_once dirname(__FILE__) . '/lib/xmllib.php';

                if(block_exacomp_data_importer::do_import_string($xml, block_exacomp::IMPORT_SOURCE_DEFAULT, true)) {
                    mtrace("import done");
                    block_exacomp_settstamp();
                }
                else mtrace("import failed");
            }
        }

        block_exacomp_perform_auto_test();

        return true;
    }
}