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

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../moodleblock.class.php';
require_once __DIR__.'/inc.php';

class block_exacomp extends block_list {
	function init() {
		$this->title = \block_exacomp\get_string('blocktitle');
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
		
		$has_data = block_exacomp\data::has_data();

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

				if (!$courseSettings->nostudents) {
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
			
				if (get_config('exacomp','external_trainer_assign')) {
					$this->content->items[]='<a title="' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '" href="' . $CFG->wwwroot . '/blocks/exacomp/externaltrainers.php?courseid=' . $COURSE->id . '">' . get_string('block_exacomp_external_trainer_assign', 'block_exacomp') . '</a>';
					$this->content->icons[]='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/personal.png" height="16" width="23" alt="'.get_string("block_exacomp_external_trainer_assign", "block_exacomp").'" />';
				}
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
		if (is_siteadmin() || (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement())) {
			//Admin sieht immer Modulkonfiguration
			//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
			if($has_data){
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/edit_config.php', array('courseid'=>$courseid)), get_string('tab_admin_settings', 'block_exacomp'), array('title'=>get_string('tab_admin_settings', 'block_exacomp')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/standardpreselect.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
			}

			// always show import/export
			$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string('tab_admin_import', 'block_exacomp'), array('title'=>get_string('tab_admin_import', 'block_exacomp')));
			$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/importexport.png'), 'alt'=>'', 'height'=>16, 'width'=>23));
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

		mtrace('Exabis Competence Grid: cron job is running.');

		//import xml with provided server url
		if($xmlserverurl) {
			try {
				require_once __DIR__."/classes/data.php";

				if (block_exacomp\data_importer::do_import_url($xmlserverurl, \block_exacomp\IMPORT_SOURCE_DEFAULT)) {
					mtrace("import done");
					block_exacomp_settstamp();
				} else {
					mtrace("import failed: unknown error");
				}
			} catch (block_exacomp\moodle_exception $e) {
				mtrace("import failed: ".$e->getMessage());
			}
		}

		block_exacomp_perform_auto_test();

		return true;
	}
}
