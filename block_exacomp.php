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
		$this->title = block_exacomp_get_string('blocktitle');
	}

	function applicable_formats() {
		// block can only be installed in courses
		return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
	}

	function get_content() {
		global $CFG, $USER, $COURSE;

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

		if (block_exacomp_is_skillsmanagement()) {
			$checkConfig = block_exacomp_is_configured($courseid);
		} else {
			$checkConfig = block_exacomp_is_configured();
		}

		$has_data = block_exacomp\data::has_data();

		$courseSettings = block_exacomp_get_settings_by_course($courseid);

		$ready_for_use = block_exacomp_is_ready_for_use($courseid);

		$de = false;
		$lang = current_language();
		if (isset($lang) && substr($lang, 0, 2) === 'de') {
			$de = true;
		}

		$isTeacher = block_exacomp_is_teacher($currentcontext) && $courseid != 1;
		$isStudent = has_capability('block/exacomp:student', $currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $currentcontext);
		$isTeacherOrStudent = $isTeacher || $isStudent;
		// $lis = block_exacomp_is_altversion();

		if ($checkConfig && $has_data) {    //Modul wurde konfiguriert

			if ($isTeacherOrStudent && $ready_for_use) {
				//Kompetenzüberblick
			    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/grid.png'.'" class="icon" alt="" />';
			    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_competence_overview').'" '.
			 			    ' href="'.$CFG->wwwroot.'/blocks/exacomp/assign_competencies.php?courseid='.$courseid.'">'.
			 			    $icon.block_exacomp_get_string('tab_competence_overview').'</a>';

				if ($isTeacher || block_exacomp_get_cross_subjects_by_course($courseid, $USER->id)) {
					// Cross subjects: always for teacher and for students if it there are cross subjects
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/detailed_view_of_competencies.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_cross_subjects').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/cross_subjects_overview.php?courseid='.$courseid.'">'.
								    $icon.block_exacomp_get_string('tab_cross_subjects').'</a>';
				}

				if (!$courseSettings->nostudents) {
					//Kompetenzprofil
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/overview_of_competencies.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_competence_profile').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/competence_profile.php?courseid='.$courseid.'">'.
								    $icon.block_exacomp_get_string('tab_competence_profile').'</a>';
				}

				if (!$courseSettings->nostudents) {
					//Beispiel-Aufgaben
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/area.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_examples').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/view_examples.php?courseid='.$courseid.'">'.
								    $icon.block_exacomp_get_string('tab_examples').'</a>';
					
					//Lernagenda
					//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), block_exacomp_get_string('tab_learning_agenda'), array('title'=>block_exacomp_get_string('tab_learning_agenda')));
					//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
				}

				if (!$courseSettings->nostudents) {
					//Wochenplan
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/assign_moodle_activities.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_weekly_schedule').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/weekly_schedule.php?courseid='.$courseid.'">'.
								    $icon.block_exacomp_get_string('tab_weekly_schedule').'</a>';
				}

				if ($isTeacher && !$courseSettings->nostudents) {
					if ($courseSettings->useprofoundness) {
					    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/subject.png'.'" class="icon" alt="" />';
					    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_profoundness').'" '.
									    ' href="'.$CFG->wwwroot.'/blocks/exacomp/profoundness.php?courseid='.$courseid.'">'.
									    $icon.block_exacomp_get_string('tab_profoundness').'</a>';
				}

					//Meine Auszeichnungen
					//if ($usebadges) {
					//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), block_exacomp_get_string('tab_badges'), array('title'=>block_exacomp_get_string('tab_badges')));
					//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
					//}
				}
			}

			if ($isTeacher) {
				//Einstellungen
			    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/subjects_topics.gif'.'" class="icon" alt="" />';
			    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_teacher_settings').'" '.
			 			    ' href="'.$CFG->wwwroot.'/blocks/exacomp/edit_course.php?courseid='.$courseid.'">'.
			 			    $icon.block_exacomp_get_string('tab_teacher_settings').'</a>';
				
				if (!$ready_for_use) {
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/subject.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_teacher_settings_new_subject').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/subject.php?courseid='.$courseid.'&embedded=false'.'">'.
								    $icon.block_exacomp_get_string('tab_teacher_settings_new_subject').'</a>';
				}
				if (get_config('exacomp', 'external_trainer_assign')) {
				    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/personal.png'.'" class="icon" alt="" />';
				    $this->content->items[] = '<a title="'.block_exacomp_get_string('block_exacomp_external_trainer_assign').'" '.
								    ' href="'.$CFG->wwwroot.'/blocks/exacomp/externaltrainers.php?courseid='.$courseid.'">'.
								    $icon.block_exacomp_get_string('block_exacomp_external_trainer_assign').'</a>';
				}
			}
			/*if ($de) {
				//Hilfe
				$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid' => $courseid)), block_exacomp_get_string('tab_help'), array('title' => block_exacomp_get_string('tab_help')));
				$this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/info.png'), 'alt' => "", 'height' => 16, 'width' => 23));
			}*/
		} else {
			if ($isTeacher && !has_capability('block/exacomp:admin', $globalcontext)) {
				$this->content->items[] = block_exacomp_get_string('admin_config_pending');
				//$this->content->icons[] = '';
			}
		}

		//if has_data && checkSubjects -> Modul wurde konfiguriert
		//else nur admin sieht block und hat nur den link Modulkonfiguration
		if (is_siteadmin() || (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement())) {
			//Admin sieht immer Modulkonfiguration
			//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
			if ($has_data) {
			    $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/standardpreselect.png'.'" class="icon" alt="" />';
			    $this->content->items[] = '<a title="'.block_exacomp_get_string('tab_admin_settings').'" '.
			 			    ' href="'.$CFG->wwwroot.'/blocks/exacomp/edit_config.php?courseid='.$courseid.'">'.
			 			    $icon.block_exacomp_get_string('tab_admin_settings').'</a>';
			}

			// always show import/export
			$icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/importexport.png'.'" class="icon" alt="" />';
			$this->content->items[] = '<a title="'.block_exacomp_get_string('tab_admin_import').'" '.
			 			' href="'.$CFG->wwwroot.'/blocks/exacomp/import.php?courseid='.$courseid.'">'.
			 			$icon.block_exacomp_get_string('tab_admin_import').'</a>';
		}

		return $this->content;
	}

	public function instance_allow_multiple() {
		return false;
	}

	function has_config() {
		return true;
	}
}
