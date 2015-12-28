<?php

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../../moodleblock.class.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/xmllib.php';


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
	const DB_ITEMEXAMPLE = 'block_exacompitemexample';
	const DB_SUBJECT_NIVEAU_MM = 'block_exacompsubjniveau_mm';
	
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
	const DATA_SOURCE_CUSTOM = 3;
	const EXAMPLE_SOURCE_TEACHER = 3;
	const EXAMPLE_SOURCE_USER = 4;
	
	const IMPORT_SOURCE_DEFAULT = 1;
	const IMPORT_SOURCE_SPECIFIC = 2;
	
	const CUSTOM_CREATED_DESCRIPTOR = 3;

	const EXAMPLE_STATE_NOT_SET = 0; // never used in weekly schedule, no evaluation
	const EXAMPLE_STATE_IN_POOL = 1; // planned to work with example -> example is in pool
	const EXAMPLE_STATE_IN_CALENDAR = 2; // example is in work -> in calendar
	const EXAMPLE_STATE_SUBMITTED = 3; //state 3 = submission for example / example closed (for submission no file upload etc is necessary) -> closed
	const EXAMPLE_STATE_EVALUATED_NEGATIV = 4; // evaluated -> only from teacher-> exacomp evaluation nE
	const EXAMPLE_STATE_EVALUATED_POSITIV = 5; //evaluated -> only from teacher -> exacomp evaluation > nE 
	const EXAMPLE_STATE_LOCKED_TIME = 9; //handled like example entry on calender, but represent locked time
	
	function init() {
		$this->title = get_string('pluginname', 'block_exacomp');
	}

	function applicable_formats() {
		// block can only be installed in courses
		return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
	}

	function get_content() {
		global $CFG, $USER, $COURSE, $usebadges;

		//does not work with global var, don't know why TODO
		$usebadges = get_config('exacomp', 'usebadges');

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

		if (empty($currentcontext)) {
			return $this->content;
		}

		$courseid = intval($COURSE->id);

		if(block_exacomp_is_skillsmanagement())
			$checkConfig = block_exacomp_is_configured($courseid);
		else
			$checkConfig = block_exacomp_is_configured();
		
		$has_data = \block_exacomp_data::has_data();

		$courseSettings = block_exacomp_get_settings_by_course($courseid);
		// this is an old setting
		// TODO: delete all occurences of usedetailpage in all files
		$usedetailpage = $courseSettings->usedetailpage;

		$ready_for_use = block_exacomp_is_ready_for_use($courseid);

		$de = false;
		$lang = current_language();
		if(isset($lang) && substr( $lang, 0, 2) === 'de'){
			$de = true;
		}

		$isTeacher = block_exacomp_is_teacher($currentcontext) && $courseid != 1;
		$isStudent = has_capability('block/exacomp:student', $currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $currentcontext);
		$isTeacherOrStudent = $isTeacher || $isStudent;
		// $lis = block_exacomp_is_altversion();

		if($checkConfig && $has_data){	//Modul wurde konfiguriert
			if ($isTeacherOrStudent && block_exacomp_is_activated($courseid)) {
				//Kompetenzraster
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_grid.php', array('courseid'=>$courseid)), get_string('tab_competence_grid', 'block_exacomp'), array('title'=>get_string('tab_competence_grid', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/examples_and_tasks.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
			}
			if ($isTeacherOrStudent && $ready_for_use) {
				//Kompetenzüberblick
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid)), get_string('tab_competence_overview', 'block_exacomp'), array('title' => get_string('tab_competence_overview', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/grid.png'), 'alt' => "", 'height' => 16, 'width' => '23'));

				if ($isTeacher || (block_exacomp_cross_subjects_exists() && block_exacomp_get_cross_subjects_by_course($courseid, $USER->id))) {
					// Cross subjects: always for teacher and for students if it there are cross subjects
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid' => $courseid)), get_string('tab_cross_subjects', 'block_exacomp'), array('title' => get_string('tab_cross_subjects', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt' => "", 'height' => 16, 'width' => '23'));
				}

				if (!$courseSettings->nostudents) {
					//Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
					if ($courseSettings->uses_activities && $usedetailpage) {
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_detail.php', array('courseid' => $courseid)), get_string('tab_competence_details', 'block_exacomp'), array('title' => get_string('tab_competence_details', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/detailed_view_of_competencies.png'), 'alt' => "", 'height' => 16, 'width' => 23));
					}

					//Kompetenzprofil
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/competence_profile.php', array('courseid' => $courseid)), get_string('tab_competence_profile', 'block_exacomp'), array('title' => get_string('tab_competence_profile', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/overview_of_competencies.png'), 'alt' => "", 'height' => 16, 'width' => 23));
				}

				if ($isTeacher && !$courseSettings->nostudents) {
					//Beispiel-Aufgaben
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/view_examples.php', array('courseid' => $courseid)), get_string('tab_examples', 'block_exacomp'), array('title' => get_string('tab_examples', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/area.png'), 'alt' => "", 'height' => 16, 'width' => 23));

					//Lernagenda
					//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
					//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
				}

				if (!$courseSettings->nostudents) {
					//Wochenplan
					$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $courseid)), get_string('tab_weekly_schedule', 'block_exacomp'), array('title' => get_string('tab_weekly_schedule', 'block_exacomp')));
					$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/assign_moodle_activities.png'), 'alt' => "", 'height' => 16, 'width' => 23));
				}

				if ($isTeacher && !$courseSettings->nostudents) {
					if ($courseSettings->useprofoundness) {
						$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/profoundness.php', array('courseid' => $courseid)), get_string('tab_profoundness', 'block_exacomp'), array('title' => get_string('tab_profoundness', 'block_exacomp')));
						$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt' => "", 'height' => 16, 'width' => 23));
					}

					//Meine Auszeichnungen
					//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
					//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
					//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					//}
				}
			}

			if ($isTeacher) {
				//Einstellungen
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid' => $courseid)), get_string('tab_teacher_settings', 'block_exacomp'), array('title' => get_string('tab_teacher_settings', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/subjects_topics.gif'), 'alt' => "", 'height' => 16, 'width' => 23));
			}
			if ($de && !block_exacomp_is_skillsmanagement()) {
				//Hilfe
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid' => $courseid)), get_string('tab_help', 'block_exacomp'), array('title' => get_string('tab_help', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/info.png'), 'alt' => "", 'height' => 16, 'width' => 23));
			}
		} else {
			if ($isTeacher && !has_capability('block/exacomp:admin', $globalcontext)){
				$this->content->items[] = get_string('admin_config_pending','block_exacomp');
				$this->content->icons[] = '';
			}
		}
		
		//if has_data && checkSubjects -> Modul wurde konfiguriert
		//else nur admin sieht block und hat nur den link Modulkonfiguration
		if (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement()) {
			//Admin sieht immer Modulkonfiguration
			//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
			if($has_data){
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_config.php', array('courseid'=>$courseid)), get_string('tab_admin_configuration', 'block_exacomp'), array('title'=>get_string('tab_admin_configuration', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/standardpreselect.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
			}

			// always show import/export
			$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string('tab_admin_import', 'block_exacomp'), array('title'=>get_string('tab_admin_import', 'block_exacomp')));
			$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/importexport.png'), 'alt'=>'', 'height'=>16, 'width'=>23));

			if(get_config('exacomp','external_trainer_assign') != false && has_capability('block/exacomp:assignstudents', $globalcontext)) {
				$this->content->items[]='<a title="' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '" href="' . $CFG->wwwroot . '/blocks/exacomp/externaltrainers.php?courseid=' . $COURSE->id . '">' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '</a>';
				$this->content->icons[]='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/personal.png" height="16" width="23" alt="'.get_string("block_exacomp_external_trainer_assign", "block_exacomp").'" />';
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
		global $xmlserverurl;
		$xmlserverurl = get_config('exacomp', 'xmlserverurl');

		mtrace('Exabis Competencies: cron job is running.');

		//import xml with provided server url
		if($xmlserverurl) {
			try {
				if (block_exacomp_data_importer::do_import_url($xmlserverurl, block_exacomp::IMPORT_SOURCE_DEFAULT)) {
					mtrace("import done");
					block_exacomp_settstamp();
				} else {
					mtrace("import failed: unknown error");
				}
			} catch (block_exacomp\exception $e) {
				mtrace("import failed: ".$e->getMessage());
			}
		}

		block_exacomp_perform_auto_test();

		return true;
	}
}