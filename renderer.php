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

defined('MOODLE_INTERNAL') || die;

use block_exacomp\globals as g;

class block_exacomp_renderer extends plugin_renderer_base {

    protected $diffLevelExists = false;
    protected $useEvalNiveau = false;
    protected $reviewers = array();
    protected $exaportExists = false;

	const STUDENT_SELECTOR_OPTION_EDITMODE = 1;
	const STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN = 2;
	const STUDENT_SELECTOR_OPTION_COMPETENCE_GRID_DROPDOWN = 3;

    /**
     * block_exacomp_renderer constructor.
     */
    public function __construct(moodle_page $page, $target) {
        $this->diffLevelExists = block_exacomp_get_assessment_any_diffLevel_exist();
        $this->useEvalNiveau = block_exacomp_use_eval_niveau();
        $this->exaportExists = block_exacomp_exaportexists();
        parent::__construct($page, $target);
    }

    public function header_v2($page_identifier = "") {
		// g::$PAGE->show_tabtree
		return $this->header(block_exacomp_get_context_from_courseid(g::$COURSE->id), g::$COURSE->id, $page_identifier);
	}

	public function header($context = null, $courseid = 0, $page_identifier = "", $tabtree = null) {
		global $PAGE;

		block_exacomp_init_js_css();

		$extras = "";
		if ($PAGE->pagelayout == 'embedded') {
			ob_start();

			$title = $PAGE->heading ?: $PAGE->title;
			?>
			<script type="text/javascript">
							if (window.parent && window.parent.block_exacomp && window.parent.block_exacomp.last_popup) {
								// set popup title
								window.parent.block_exacomp.last_popup.set('headerContent', <?php echo json_encode($title); ?>);
							}
			</script>
			<style>
				body {
					/* because moodle embedded pagelayout always adds padding/margin on top */
					padding: 0 !important;
					margin: 0 !important;
				}
			</style>
			<?php

			if ($PAGE->heading) {
				?>
				<!--  moodle doesn't print a title for embedded layout -->
				<h2><?php echo $PAGE->heading; ?></h2>
				<?php
			}

			$extras .= ob_get_clean();
		}

		if ($tabtree === null) {
			$tabtree = $PAGE->pagelayout != 'embedded';
		}

		return
			parent::header().
			$extras.
			(($tabtree && $context) ? parent::tabtree(block_exacomp_build_navigation_tabs($context, $courseid), $page_identifier) : '').
			$this->wrapperdivstart();
	}

	public function footer() {
		return
			$this->wrapperdivend().
			parent::footer();
	}

	public function requires() {
		global $PAGE;

		// init default js / css
		block_exacomp_init_js_css();

		return $PAGE->requires;
	}

	public function pix($image, $alt = null, $attributes = array()) {
		$attributes += ["src" => g::$CFG->wwwroot.'/blocks/exacomp/pix/'.$image];
		if ($alt) {
			if (is_array($alt)) {
				$attributes += $alt;
			} else {
				$attributes += ["alt" => $alt];
			}
		}
		if (!empty($attributes['alt']) && !isset($attributes['title'])) {
			// wenn alt, aber title nicht gesetzt: verwendung isset weil leerer '' title bedeutet kein title
			$attributes['title'] = $attributes['alt'];
		}

		return html_writer::empty_tag("img", $attributes);
	}

	public function local_pix_icon($image, $alt = null, $attributes = array()) {
		return $this->pix($image, $alt, $attributes + ['class' => 'smallicon']);
	}

	public function subject_dropdown($schooltypetree, $selectedSubject) {
		$content = block_exacomp_get_string("choosesubject").': ';
		$array = array();
		$options = array();

		foreach ($schooltypetree as $schooltype) {
			$options[$schooltype->title] = array();
			foreach ($schooltype->subjects as $subject) {
				$options[$schooltype->title][$subject->id] = $subject->title;
			}

			$array[] = $options;
			$options = array();
		}

		$content .= html_writer::select($array, "lis_subjects", $selectedSubject, false,
			array("onchange" => "block_exacomp.set_location_params({ subjectid: this.value })"));

		return $content;
	}

	/**
	 * Prints 2 select inputs for subjects and topics
	 */
	public function overview_dropdowns($type, $students, $selectedStudent = -1, $isTeacher = false, $isEditingTeacher = true, $groups = null) {
		global $COURSE, $USER;

		$content = "";
		$right_content = "";

		if ($isTeacher) {
			if ($this->is_edit_mode()) {
				// display a hidden field? not needed, because the form never gets submitted (it's ajax)
				// $content .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'exacomp_competence_grid_select_student', 'value'=>$selectedStudent));
				$content .= '<h3>'.block_exacomp_get_string('header_edit_mode').'</h3>';
			} elseif ($students) {
				$content .= '<div style="padding-bottom: 5px;">';
				$content .= block_exacomp_get_string("choosestudent");
				$content .= $this->studentselector($students, $selectedStudent, static::STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN, $groups);

				$content .= '</div><div style="padding-bottom: 15px;">';

				//print date range picker
				$content .= block_exacomp_get_string("choosedaterange");
				$content .= $this->daterangepicker();

				$content .= '</div>';
			}

			if (!$this->is_edit_mode() && $students) {
				$right_content .= block_exacomp_get_message_icon($selectedStudent);
			}

			if ($this->is_edit_mode()) {
				//$right_content .= html_writer::empty_tag('input', array('type' => 'button', 'id' => 'add_subject', 'value' => block_exacomp_trans('add_subject', ['de:Kompetenzraster anlegen', 'en:Create competence grid']),
				//	'exa-type' => 'iframe-popup', 'exa-url' => "subject.php?courseid={$COURSE->id}&show=add"));
			}

			if ($students) {
				$url = new moodle_url('/blocks/exacomp/pre_planning_storage.php', array('courseid' => $COURSE->id, 'creatorid' => $USER->id));
				$right_content .= html_writer::tag('button',
					html_writer::empty_tag('img', ['src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png')]),
					array(
						'id' => 'pre_planning_storage_submit', 'name' => 'pre_planning_storage_submit',
						'title' => block_exacomp_get_string('pre_planning_storage'),
						'type' => 'button', /* browser default setting for html buttons is submit */
						'exa-type' => 'iframe-popup', 'exa-url' => $url->out(false),
                        'class' => 'btn btn-default',
					)
				);
			}

			if($isEditingTeacher){
			    $right_content .= $this->edit_mode_button(block_exacomp\url::create(g::$PAGE->url, ['editmode' => !$this->is_edit_mode()]));
			}

		} else {
			foreach (block_exacomp_get_teachers_by_course($COURSE->id) as $teacher) {
				$right_content .= block_exacomp_get_message_icon($teacher->id);

			}
		}

		if ($this->is_edit_mode()) {
			$print = false;
		} else {
			if ($type == 'assign_competencies') {
				$print = "window.open(location.href+'&print=1');";
			} else {
				$print = true;
			}
		}
		$content = $this->button_box($print, $right_content).$content;

		return $content;
	}

	public function is_edit_mode() {
		return !empty($this->editmode);
	}

	public function is_print_mode() {
		return !empty($this->print);
	}

	public function edit_mode_button($url) {
		$edit = $this->is_edit_mode();

		return html_writer::empty_tag('input', array('type' => 'button', 'id' => 'edit_mode_submit', 'name' => 'edit_mode_submit', 'value' => block_exacomp_get_string(($edit) ? 'turneditingoff' : 'turneditingon'),
			"exa-type" => 'link', 'exa-url' => $url, 'class' => 'btn btn-default'));
	}

	public function subjects_menu($subjects, $selectedSubject, $selectedTopic, $students = array(), $editmode = false) {
		global $CFG, $COURSE;

		$content = html_writer::start_div('subjects_menu');
		$content .= html_writer::start_tag('ul');


		foreach ($subjects as $subject) {
            $extra = '';
            if ($this->is_edit_mode() && $subject->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                $extra .= ' '.html_writer::span($this->pix_icon("i/edit", block_exacomp_get_string("edit")), null, ['exa-type' => "iframe-popup", 'exa-url' => 'subject.php?courseid='.$COURSE->id.'&id='.$subject->id]);
                $deleteUrl = html_entity_decode(new block_exacomp\url('subject.php', ['courseid' => $COURSE->id, 'id' => $subject->id, 'action' => 'delete', 'forward' => g::$PAGE->url]));
                $extra .= ' '.html_writer::span($this->pix_icon("i/delete", block_exacomp_get_string("delete")),
                        null, [
                            'title' => block_exacomp_get_string("delete"),
                            'onclick' => 'if (confirm(\''.block_exacomp_get_string('really_delete').'\')) { window.location.href = \''.$deleteUrl.'\'; return false;} else {return false;};'
                        ]);
            }



            $content .= html_writer::tag('li',
                html_writer::link(
                    new block_exacomp\url(g::$PAGE->url, ['subjectid' => $subject->id, 'topicid' => BLOCK_EXACOMP_SHOW_ALL_TOPICS, 'colgroupid' => optional_param('colgroupid', 0, PARAM_INT)]),
                    $subject->title.$extra, [
                    'class' => (!$selectedTopic && $subject->id == $selectedSubject->id) ? 'type current' : 'type',
                    'title' => (($author = $subject->get_author()) ? block_exacomp_get_string('author', 'repository').": ".$author : ''),
                ])
            );



            $studentid = 0;
            if (!$editmode && count($students) == 1) {
                $studentid = array_values($students)[0]->id;
            }

            foreach ($subject->topics as $topic) {
                if (block_exacomp_is_teacher() || block_exacomp_is_topic_visible($COURSE->id, $topic, g::$USER->id)) {
                    $extra = '';
                    if ($this->is_edit_mode() && $topic->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                        $extra .= ' '.html_writer::span($this->pix_icon("i/edit", block_exacomp_get_string("edit")), null, [
                                'exa-type' => "iframe-popup",
                                'exa-url' => 'topic.php?courseid='.$COURSE->id.'&id='.$topic->id,
                            ]);
                        $deleteUrl = html_entity_decode(new block_exacomp\url('topic.php', ['courseid' => $COURSE->id, 'id' => $topic->id, 'action' => 'delete', 'forward' => g::$PAGE->url.'&editmode=1']));
                        $extra .= ' '.html_writer::span($this->pix_icon("i/delete", block_exacomp_get_string("delete")),
                                null, [
                                    'title' => block_exacomp_get_string("delete"),
                                    'onclick' => 'if (confirm(\''.block_exacomp_get_string('really_delete').'\')) { window.location.href = \''.$deleteUrl.'\'; return false;} else {return false;};'
                                ]);
                    }

                    $content .= html_writer::tag('li', html_writer::link(new block_exacomp\url (g::$PAGE->url, [
                        'subjectid' => $subject->id,
                        'topicid' => $topic->id,
                        'colgroupid' => optional_param('colgroupid', 0, PARAM_INT),
                    ]), block_exacomp_get_topic_numbering($topic).' '.$topic->title.$extra, array(
                        'class' => ($selectedTopic && $topic->id == $selectedTopic->id) ? 'current' : '',
                        'title' => $topic->description,
                    )));
                }
            }
            if ($this->is_edit_mode() && $subject->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                // only if editing and if subject was added by teacher
                $content .= html_writer::tag('li', html_writer::link("topic.php?show=add&courseid={$COURSE->id}&subjectid={$subject->id}", "<img src=\"{$CFG->wwwroot}/pix/t/addfile.png\" /> ".block_exacomp_get_string('create_new_area'), array(
                    'exa-type' => 'iframe-popup',
                )));
            }
		}

		$content .= html_writer::end_tag('ul');
		$content .= html_writer::end_tag('div');

		return $content;
	}

	public function niveaus_menu($niveaus, $selectedNiveau, $selectedTopic) {
		global $CFG, $COURSE, $PAGE;

		$content = html_writer::start_div('niveaus_menu');
		$content .= html_writer::start_tag('ul');

		foreach ($niveaus as $niveau) {
			$title = isset($niveau->cattitle) ? $niveau->cattitle : $niveau->title;
			$subtitle = $selectedTopic ? $niveau->get_subtitle($selectedTopic->subjid) : null;

			$extra = '';
			if ($this->is_edit_mode() && $niveau->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                $deleteUrl = html_entity_decode(new block_exacomp\url('niveau.php', ['courseid' => $COURSE->id, 'id' => $niveau->id, 'action' => 'delete', 'forward' => g::$PAGE->url.'&editmode=1']));
                $extra .= '&nbsp;'.html_writer::span($this->pix_icon("i/delete", block_exacomp_get_string("delete")),
                                null, [
                                        'class' => 'niveau-button',
                                        'title' => block_exacomp_get_string("delete"),
                                        'onclick' => 'if (confirm(\''.block_exacomp_get_string('really_delete').'\')) { window.location.href = \''.$deleteUrl.'\'; return false;} else {return false;};'
                                ]);
				$extra .= ''.html_writer::span($this->pix_icon("i/edit", block_exacomp_get_string("edit")), null,
                                [   'class' => 'niveau-button',
                                    'exa-type' => "iframe-popup",
                                    'exa-url' => 'niveau.php?courseid='.$COURSE->id.'&id='.$niveau->id.'&backurl='.$PAGE->url
                                ]);
			}
            $titleForTitle = $title;
			if ($subtitle) {
                $titleForTitle .= ': '.$subtitle;
                $title .= '<span class="subtitle">'.$subtitle.'</span>';
            }
            if ($niveau->id == 99999999) {
                $title = '<span class="titleAll">'.$title.'</span>'; // Wrap title
            } else {
                $title = '<span class="title">'.$title.'</span>'; // Wrap title
            }
			$content .= html_writer::tag('li',
				html_writer::link(new block_exacomp\url(g::$PAGE->url, ['niveauid' => $niveau->id]),
					$title.$extra, array('class' => ($niveau->id == $selectedNiveau->id) ? 'current' : '', 'title' => $titleForTitle))
			);
		}

		if ($this->is_edit_mode()) {
			// add niveau button
			// nur erlauben, wenn auch ein einzelner topic ausgewählt wurde
			$addNiveauContent = "<span class=\"niveau-button-add\"><img src=\"{$CFG->wwwroot}/pix/t/addfile.png\" /></span>&nbsp;".'<span class="title">'.block_exacomp_trans(['de:Neue Spalte', 'en:new column']).'</span>';

			if ($selectedTopic) {
				$content .= html_writer::tag('li',
					html_writer::link("niveau.php?show=add&courseid={$COURSE->id}&topicid=".($selectedTopic ? $selectedTopic->id : BLOCK_EXACOMP_SHOW_ALL_TOPICS),
						$addNiveauContent, array('exa-type' => 'iframe-popup'))
				);
			} else {
				$content .= html_writer::tag('li',
					html_writer::link('javascript:void(0)',
						$addNiveauContent, array('onclick' => 'alert('.json_encode(block_exacomp_trans('de:Bitte wählen Sie zuerst in der linken Leiste einen Kompetenzbereich aus')).')'))
				);
			}
		}

		$content .= html_writer::end_tag('ul');
		$content .= html_writer::end_tag('div');

		return $content;
	}

	public function overview_metadata_teacher($subject, $topic) {

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_top';

		$rows = array();

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';

		$cell->text = html_writer::tag('b', block_exacomp_get_string('instruction'))
			.html_writer::tag('p', (!empty($subject->description) ? $subject->description.'<br/>' : '').(!empty($topic->description) ? $topic->description : ''));

		$row->cells[] = $cell;
		$rows[] = $row;
		$table->data = $rows;

		$content = html_writer::table($table);
		$content .= html_writer::empty_tag('br');
		if (isset($subject->description) || isset($topic->description)) {
			return $content;
		}
	}

	public function overview_metadata($schooltype, $subject, $descriptor, $cat) {
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_info';

		$rows = array();

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->text = html_writer::span(block_exacomp_get_string('subject_singular'), 'exabis_comp_top_name')
			.html_writer::div($schooltype, 'exabis_comp_top_value');

		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::span(block_exacomp_get_string('comp_field_idea'), 'exabis_comp_top_name')
			.html_writer::div($subject ? (!empty($subject->numb) ? $subject->numb." - " : '').$subject->title : '', 'exabis_comp_top_value');

		$row->cells[] = $cell;

		if ($descriptor) {
			$cell = new html_table_cell();
			$cell->text = html_writer::span(block_exacomp_get_string('comp'), 'exabis_comp_top_name')
				.html_writer::div($descriptor->title, 'exabis_comp_top_value');

			$row->cells[] = $cell;
		}

		if (block_exacomp_is_numbering_enabled()) {
			$cell = new html_table_cell();
			$cell->text = html_writer::span(block_exacomp_get_string('progress'), 'exabis_comp_top_name')
				.html_writer::div($cat ? $cat->title : '', 'exabis_comp_top_value');

			$row->cells[] = $cell;

			$cell = new html_table_cell();
			$cell->text = html_writer::span(block_exacomp_get_string('tab_competence_overview'), 'exabis_comp_top_name')
				.html_writer::div(substr($schooltype, 0, 1).($subject ? $subject->numb : '').(($cat && isset($cat->sourceid)) ? ".".$cat->sourceid : ''), 'exabis_comp_top_value');

			$row->cells[] = $cell;
		}
		$rows[] = $row;
		$table->data = $rows;

		$content = html_writer::table($table);

		return $content;
	}

	public function competence_grid($niveaus, $skills, $topics, $data, $selection = array(), $courseid = 0, $studentid = 0, $subjectid = 0) {
		global $DB;
		$headFlag = false;

		$context = context_course::instance($courseid);
		$role = block_exacomp_is_teacher($context) ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT;
		$editmode = (($studentid == 0) ? true : false);

		$table = new html_table();
		$table->attributes['class'] = 'competence_grid';
		$head = array();

		$scheme = ($courseid == 0) ? 1 : block_exacomp_get_grading_scheme($courseid);
		$scheme_values = \block_exacomp\global_config::get_teacher_eval_items($courseid);

		$satisfied = ceil($scheme / 2);

		$profoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;

		$spanningNiveaus = $DB->get_records(BLOCK_EXACOMP_DB_NIVEAUS, array('span' => 1));
		//calculate the col span for spanning niveaus
		$spanningColspan = block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus);

		$rows = array();

		foreach ($data as $skillid => $skill) {

			if (isset($skills[$skillid])) {
				$row = new html_table_row();
				$cell1 = new html_table_cell();
				$cell1->text = html_writer::tag("span", html_writer::tag("span", $skills[$skillid], array('class' => 'rotated-text__inner-header')), array('class' => 'rotated-text-header'));
				$cell1->attributes['class'] = 'skill';
				$cell1->rowspan = count($skill) + 1;
				$row->cells[] = $cell1;
				//
				$rows[] = $row;

				if (!$headFlag) {
					$head[] = "";
				}
			}

			if (!$headFlag) {
				$head[] = "";
				$head = array_merge($head, array_diff_key($niveaus, $spanningNiveaus));
				$table->head = $head;
				$headFlag = true;
			}

			foreach ($skill as $topicid => $topic) {
				$row = new html_table_row();

				$cell2 = new html_table_cell();

				$cell2->text = html_writer::tag("span", html_writer::tag("span", block_exacomp_get_topic_numbering(\block_exacomp\topic::get($topicid))." ".$topics[$topicid], array('class' => 'rotated-text__inner')), array('class' => 'rotated-text'));
				$cell2->attributes['class'] = 'topic';
				$cell2->rowspan = 2;
				$row->cells[] = $cell2;

				// Check visibility
				$topic_std = new stdClass();
				$topic_std->id = $topicid;

				$topic_visible = block_exacomp_is_topic_visible($courseid, $topic_std, $studentid);


				//make second row, spilt rows after topic title
				$row2 = new html_table_row();
				foreach ($niveaus as $niveauid => $niveau) {
					if (isset($data[$skillid][$topicid][$niveauid])) {
						$cell = new html_table_cell();
						$cell->attributes['class'] = 'tablecell';
						$compdiv = "";
						$allTeachercomps = true;
						$allStudentcomps = true;
						foreach ($data[$skillid][$topicid][$niveauid] as $descriptor) {
							$compString = "";

							// Check visibility
							$descriptor_used = block_exacomp_descriptor_used($courseid, $descriptor, $studentid);
							$visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
							$visible_css = block_exacomp_get_visible_css($visible, $role);

							$text = block_exacomp_get_descriptor_numbering($descriptor)." ".$descriptor->title;
							if (array_key_exists($descriptor->topicid, $selection)) {
								$text = html_writer::link(new moodle_url("/blocks/exacomp/assign_competencies.php",
									array("courseid" => $courseid,
										"topicid" => $topicid,
										"subjectid" => $subjectid,
										"niveauid" => $niveauid,
										"studentid" => $studentid)), $text, array("id" => "competence-grid-link-".$descriptor->id, "class" => ($visible && $topic_visible) ? '' : 'deactivated'));
							}

							$compString .= $text;

							$cssClass = "content";
							if ($descriptor->parentid > 0) {
								$cssClass .= ' child';
							}

							if (isset($descriptor->teachercomp) && $descriptor->teachercomp) {
								$evalniveau = $DB->get_record(BLOCK_EXACOMP_DB_COMPETENCES, array("compid" => $descriptor->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, 'courseid' => $courseid, 'userid' => $studentid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));
								if ($evalniveau->evalniveauid) {
									$compString .= html_writer::tag("p", block_exacomp_get_html_for_niveau_eval($evalniveau->evalniveauid));
								}

								$cssClass = "contentok";
							}

							$compdiv .= html_writer::tag('div', $compString, array('class' => $cssClass));
						}

						// apply colspan for spanning niveaus
						if (array_key_exists($niveauid, $spanningNiveaus)) {
							$cell->colspan = $spanningColspan;
						}

						$cell->text = $compdiv;
						$row->cells[] = $cell;


						// do not print other cells for spanning niveaus
						if (array_key_exists($niveauid, $spanningNiveaus)) {
							break;
						}

					} else {
						$printCell = true;
						if (array_key_exists($niveauid, $spanningNiveaus)) {
							$printCell = false;
						}
						if ($printCell) {
							foreach (array_keys($data[$skillid][$topicid]) as $nid) {
								if (array_key_exists($nid, $spanningNiveaus)) {
									$printCell = false;
									break;
								}
							}
						}
						if ($printCell) {
							$row->cells[] = "";
						}
					}

				}

				$rows[] = $row;
				$rows[] = $row2;
			}
			//$rows[] = $row;   //gehört das auskommentiert?
		}
		$table->data = $rows;

		return html_writer::tag("div", html_writer::table($table), array("id" => "exabis_competences_block"));
	}

	public function competence_overview_form_start($selectedTopic = null, $selectedSubject = null, $studentid = null, $editmode = null) {
		global $PAGE;
		$url_params = array();
		$url_params['action'] = 'save';
		if ($selectedTopic) {
			$url_params['topicid'] = $selectedTopic->id;
		}
		if ($selectedSubject) {
			$url_params['subjectid'] = $selectedSubject->id;
		}
		if (isset($studentid)) {
			$url_params['studentid'] = $studentid;
		}
		if (isset($editmode)) {
			$url_params['editmode'] = $editmode;
		}

		$url = new moodle_url($PAGE->url, $url_params);

		return html_writer::start_tag('form', array('id' => 'assign-competencies', "action" => $url, 'method' => 'post'));
	}

	public function profoundness($subjects, $courseid, $students, $role, $forReport = false) {
		$table = new html_table();
		$rows = array();
		$table->attributes['class'] = 'exabis_comp_comp rg2 exabis-tooltip rg2-opened-first';
		//$table->attributes['border'] = '1';
		//$table->width = '100%';

		// 1st header row
		$headerrow = new html_table_row();

		$cell = new html_table_cell();
		$cell->rowspan = 2;
        $cell->colspan = 3;
		$cell->text = block_exacomp_get_string('profoundness_description');
        $cell->attributes['width'] = '40%';
		$headerrow->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_basic');
        $cell->attributes['width'] = '30%';
		$cell->colspan = 2;
		$headerrow->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_extended');
        $cell->attributes['width'] = '30%';
		$cell->colspan = 2;
		$headerrow->cells[] = $cell;

		$rows[] = $headerrow;

		// 2nd header row
		$headerrow = new html_table_row();

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_mainly');
        $cell->attributes['width'] = '15%';
		$headerrow->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_entirely');
        $cell->attributes['width'] = '15%';
		$headerrow->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_mainly');
        $cell->attributes['width'] = '15%';
		$headerrow->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('profoundness_entirely');
        $cell->attributes['width'] = '15%';
		$headerrow->cells[] = $cell;

		$rows[] = $headerrow;

		if ($this->exaportExists) {
			$eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
		} else {
			$eportfolioitems = array();
		}

		foreach ($subjects as $subject) {
			if (!$subject->topics) {
				continue;
			}

			/* TOPICS */
			//for every topic
			$data = (object)array(
				'courseid' => $courseid,
				'rg2_level' => 0,
				'showevaluation' => 0,
				'role' => $role,
				'scheme' => 2,
				'profoundness' => 1,
				'cm_mm' => block_exacomp_get_course_module_association($courseid),
				'eportfolioitems' => $eportfolioitems,
				'exaport_exists' => $this->exaportExists,
				'course_mods' => get_fast_modinfo($courseid)->get_cms(),
				'selected_topicid' => null,
				'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors,
			);
			$this->topics($rows, 0, $subject->topics, $data, $students, true, false, 0, true, $forReport);
			$table->data = $rows;
		}

		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class" => "exabis_competencies_lis")), array("id" => "exabis_competences_block"));
		if (!$forReport) {
            $table_html .= html_writer::div(html_writer::tag("input", "",
                    array("id" => "btn_submit", "type" => "submit", "value" => block_exacomp_get_string("save_selection"))), '',
                    array('id' => 'exabis_save_button'));
        }

		return $table_html.html_writer::end_tag('form');
	}

	public function competence_overview($subjects, $courseid, $students, $showevaluation, $role, $scheme = 1, $singletopic = false, $crosssubjid = 0, $isEditingTeacher = true) {
		global $DB, $USER, $PAGE, $COURSE;

		$table = new html_table();
		$rows = array();
		$studentsColspan = $showevaluation ? 2 : 1;
		//if (block_exacomp_use_eval_niveau() && ($showevaluation || $role == BLOCK_EXACOMP_ROLE_TEACHER)) {
		if ($this->useEvalNiveau && $this->diffLevelExists) {
			$studentsColspan++;
		}




		$table->attributes['class'] = 'exabis_comp_comp rg2 exabis-tooltip competence-overview';
		if (get_config('exacomp', 'disable_js_assign_competencies') && optional_param('colgroupid', 0, PARAM_INT) == -1) { // if pressed show all columns
            $table->attributes['class'] .= ' show-all-colgroups ';
        }

        // in the future maybe use lscache or some other method?
		if ($crosssubjid) {
			$table->attributes['exa-rg2-storageid'] = 'cross_subject-'.$crosssubjid;
		} elseif (count($subjects) == 1) {
			$subject = reset($subjects);
			$table->attributes['exa-rg2-storageid'] = 'assign_competencies-'.'subject-'.$subject->id;
			if (count($subject->topics) == 1) {
				$topic = reset($subject->topics);
				$table->attributes['exa-rg2-storageid'] .= '-topic-'.$topic->id;
			}
		}

		if ($this->exaportExists) {
			$eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
		} else {
			$eportfolioitems = array();
		}


		/* SUBJECTS */
		$first = true;
		$course_subs = block_exacomp_get_subjects_by_course($courseid);
		$usesubjectgrading = block_exacomp_is_subjectgrading_enabled();

		foreach ($subjects as $subject) {
			if (!$subject->topics) {
				continue;
			}

			if ($first) {
				//for every subject
				$subjectRow = new html_table_row();
				$subjectRow->attributes['class'] = 'exahighlight';

				//subject-title
				$title = new html_table_cell();
				$title->colspan = 2;

				if ($crosssubjid) {
					$title->text = html_writer::tag("b", block_exacomp_get_string('comps_and_material'));
				} else {
					$title->text = ($usesubjectgrading) ? '' : html_writer::tag("b", $subject->title);
				}

				$title->print_width = 5 + 25;
				$subjectRow->cells[] = $title;
			}

			$nivCell = new html_table_cell();
			$nivCell->text = block_exacomp_get_string('competence_grid_niveau');
			$nivCell->print_width = 5;

			if ($first) {
				$subjectRow->cells[] = $nivCell;
			}

			$studentsCount = 0;

			foreach ($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

				$studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;
				$studentCell->print_width = (100 - (5 + 25 + 5)) / count($students);
				$studentCell->colspan = $studentsColspan;
				$studentCell->text = fullname($student);
				if (!$this->is_print_mode() && block_exacomp_is_exastud_installed() && ($info = \block_exastud\api::get_student_review_link_info_for_teacher($student->id))) {
					$studentCell->text .= ' <a href="'.$info->url.'" title="'.block_exacomp_trans('de:Überfachliche Bewertung').'" onclick="window.open(this.href,this.target,\'width=880,height=660,scrollbars=yes\'); return false;">'.'<img src="pix/review_student.png" />'.'</a>';
				}

				if ($this->is_print_mode()) {
					// zeilenumbruch im namen beim drucken: nur erstes leerzeichen durch <br> ersetzen
					$studentCell->text = preg_replace('!\s!', '<br />', $studentCell->text, 1);
				}

				if ($first) {
					$subjectRow->cells[] = $studentCell;
				}
			}

			if ($first) {
				$rows[] = $subjectRow;
			}

			if ($showevaluation) {
				$studentsCount = 0;

				$evaluationRow = new html_table_row();
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = 3;
				$evaluationRow->cells[] = $emptyCell;

				foreach ($students as $student) {
					$columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

					$firstCol = new html_table_cell();
					$firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;
					$secCol = new html_table_cell();
					$secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;

					if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
						$firstCol->text = block_exacomp_get_string('studentshortcut');
						$secCol->text = block_exacomp_get_string('teachershortcut');
						if ($this->useEvalNiveau && $this->diffLevelExists) {
							$secCol->colspan = 2;
						}
					} else {
						$firstCol->text = block_exacomp_get_string('teachershortcut');
						if ($this->useEvalNiveau && $this->diffLevelExists) {
							$firstCol->colspan = 2;
						}
						$secCol->text = block_exacomp_get_string('studentshortcut');
					}

					$evaluationRow->cells[] = $firstCol;
					$evaluationRow->cells[] = $secCol;
				}
				$rows[] = $evaluationRow;
			}
			//$rows[] = new html_table_row();
			//total evaluation crosssub row
			//if ($crosssubjid && !$this->is_edit_mode() && $students) {
            if ($first) {
                if ($crosssubjid) {
                    $profoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;
                    $evaluation = ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? "teacher" : "student";

                    $examples_crosssubj = block_exacomp_get_examples_for_crosssubject($crosssubjid);

                    $visible_css = block_exacomp_get_visible_css(true, $role);

                    $this_rg2_class = 'rg2-level-0'.$visible_css;
                    $sub_rg2_class = 'rg2-level-1';

                    $crosssubjectrow = new html_table_row();
                    $crosssubjectrow->attributes['class'] = $this_rg2_class.' exahighlight';
                    // $crosssubjectrow->attributes['exa-rg2-id'] = 'topic-'.$topic->id;

                    $studentsCount = 0;
                    $checkboxname = 'datacrosssubs'; //?
                    //$student = array_values($students)[0];

                    $totalRow = new html_table_row();
                    $totalRow->attributes['class'] = 'exahighlight';
                    $totalRow->attributes['class'] = $this_rg2_class.' exahighlight';

                    $firstCol = new html_table_cell();
                    if($this->is_edit_mode()){
                        $firstCol->text .= " ".html_writer::link(
                                new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $courseid, "crosssubjid" => $crosssubjid)),
                                html_writer::empty_tag('img', array('src' => 'pix/upload_12x12.png', 'alt' => 'upload')),
                                array("target" => "_blank", 'exa-type' => 'iframe-popup'));
                    }
                    $firstCol->text .= block_exacomp_get_string('total');

                    $totalRow->cells[] = $firstCol;

                    $outputnameCell = new html_table_cell();
                    $outputname =  block_exacomp_get_string('crosssubject_files');
                    $outputnameCell->text = html_writer::div($outputname);
                    $outputnameCell->attributes['class'] = 'rg2-arrow rg2-indent';
                    $totalRow->cells[] = $outputnameCell;
                    $totalRow->cells[] = new html_table_cell();

                    foreach ($students as $student) {
                        if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
                            $reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, "reviewerid", array("userid" => $student->id, "compid" => $crosssubjid, "courseid" => $courseid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB));
                            if ($reviewerid == $USER->id || $reviewerid == 0) {
                                $reviewerid = null;
                            }
                        }
                        $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                        $student_evaluation_cell = new html_table_cell();
                        $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                        $teacher_evaluation_cell = new html_table_cell();
                        $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                        $niveau_cell = new html_table_cell();
                        $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
                        $niveau_cell->attributes['exa-timestamp'] = isset($student->crosssubs->timestamp_teacher[$crosssubjid]) ? $student->crosssubs->timestamp_teacher[$crosssubjid] : 0;

                        if ($this->useEvalNiveau && block_exacomp_get_assessment_theme_diffLevel() == 1) {
                            $niveau_cell->text = $this->generate_niveau_select(
                                'niveau_crosssub',
                                $crosssubjid,
                                'crosssubs',
                                $student,
                                ($role == BLOCK_EXACOMP_ROLE_STUDENT ? true : false),
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                        } else {
                            $niveau_cell->text = '';
                        }

                        //Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
                        //the warning contains the name of the reviewer
                        if (isset($reviewerid) && $reviewerid > 0) {
                            if (!array_key_exists($reviewerid, $this->reviewers)) {
                                $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                                $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                                if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                                    $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
                                } else {
                                    $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                                    $reviewername = $reviewerTeacherUsername;
                                }
                                $this->reviewers[$reviewerid] = $reviewername;
                            } else {
                                $reviewername = $this->reviewers[$reviewerid];
                            }
                        } else {
                            $reviewername = '';
                        }
                        $params = array('name' => 'add-grading-'.$student->id.'-'.$crosssubjid, 'type' => 'text',
                            'maxlength' => 3, 'class' => 'percent-rating-text',
                            'value' => isset($student->crosssubs->teacher_additional_grading[$crosssubjid]) ?
                                block_exacomp_format_eval_value($student->crosssubs->teacher_additional_grading[$crosssubjid]) : "",
                            'exa-compid' => $crosssubjid, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_CROSSSUB,
                            'reviewername' => $reviewername);

                        if ($role == BLOCK_EXACOMP_ROLE_STUDENT  || !$isEditingTeacher) {
                            $params['disabled'] = 'disabled';
                        }

                        //student show evaluation
                        if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_CROSSSUB)) {
                            switch ($scheme) {
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                    $teacher_evaluation_cell->text = '';
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:  // Yes/No.
                                    $teacher_evaluation_cell->text = $this->generate_checkbox(
                                        $checkboxname,
                                        $crosssubjid,
                                        'crosssubs',
                                        $student,
                                        "teacher",
                                        1,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                        null,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                    $params['value'] = isset($student->crosssubs->teacher_additional_grading[$crosssubjid]) ?
                                        block_exacomp_format_eval_value($student->crosssubs->teacher_additional_grading[$crosssubjid]) : "";
                                    $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                    break;
                                default: // Lists.
                                    $teacher_evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $crosssubjid,
                                        'crosssubs',
                                        $student,
                                        "teacher",
                                        $scheme,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                        }

                        if (block_exacomp_get_assessment_theme_SelfEval() == 1) {
                            // Only emojis?
                            $student_evaluation_cell->text = $this->generate_select(
                                $checkboxname,
                                $crosssubjid,
                                'crosssubs',
                                $student,
                                "student",
                                $scheme,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                        }
                        $student_evaluation_cell->attributes['exa-timestamp'] = isset($student->crosssubs->timestamp_teacher[$crosssubjid]) ? $student->crosssubs->timestamp_teacher[$crosssubjid] : 0;

                        // Different order of options for students and teachers:
                        // Student
                        if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                            if ($this->useEvalNiveau && $showevaluation && $this->diffLevelExists) {
                                $totalRow->cells[] = $niveau_cell;
                            }
                            if ($showevaluation) {
                                $totalRow->cells[] = $teacher_evaluation_cell;
                            }
                            $totalRow->cells[] = $student_evaluation_cell;
                        } else { // Teacher
                            if ($showevaluation) {
                                $totalRow->cells[] = $student_evaluation_cell;
                            }
                            if ($this->useEvalNiveau && $this->diffLevelExists) {
                                $totalRow->cells[] = $niveau_cell;
                            }
                            $totalRow->cells[] = $teacher_evaluation_cell;
                        }

                    }

                    $rows[] = $totalRow;


                    /* CROSSSUBJECTS */
                    //crosssubjectfiles and total eval
                    $checkboxname = "dataexamples";
                    $example_scheme = block_exacomp_get_assessment_example_scheme();
                    foreach ($examples_crosssubj as $example) {
                        $example_used = block_exacomp_example_used($courseid, $example, $studentid);

                        $visible_example = block_exacomp_is_example_visible($courseid, $example, $studentid);
                        $visible_solution = block_exacomp_is_example_solution_visible($courseid, $example, $studentid);

                        if ($role != BLOCK_EXACOMP_ROLE_TEACHER && !$visible_example) {
                            // do not display
                            continue;
                        }

                        $visible_example_css = block_exacomp_get_visible_css($visible_example, $role);

                        $studentsCount = 0;
                        $exampleRow = new html_table_row();
                        $exampleRow->attributes['class'] = 'exabis_comp_aufgabe block_exacomp_example '.$sub_rg2_class.$visible_example_css;
                        $exampleRow->cells[] = new html_table_cell();


                        $title = [];

                        if ($author = $example->get_author()) {
                            $title[] = block_exacomp_get_string('author', 'repository').": ".$author;
                        }
                        if (trim(strip_tags($example->description))) {
                            $title[] = $example->description;
                        }
                        if (trim($example->timeframe)) {
                            $title[] = $example->timeframe;
                        }
                        if (trim($example->tips)) {
                            $title[] = $example->tips;
                        }

                        $title = join('<br />', $title);

                        $titleCell = new html_table_cell();
                        $titleCell->attributes['class'] = 'rg2-indent';
                        $titleCell->style = 'padding-left: 30px;';
                        $titleCell->text = html_writer::div(html_writer::tag('span', $example->title), '', ['title' => $title]);

                        if (!$this->is_print_mode()) {

                            if ($editmode) {
                                $titleCell->text .= '<span style="padding-right: 15px;" class="todo-change-stylesheet-icons">';



                                if (block_exacomp_is_admin($COURSE->id) || (isset($example->creatorid) && $example->creatorid == $USER->id)) {
                                    $titleCell->text .= html_writer::link(
                                    // 	                            new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $data->courseid, "descrid" => $descriptor->id, "topicid" => $descriptor->topicid, "exampleid" => $example->id)),
                                    // 	                            $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
                                    // 	                            array("target" => "_blank", 'exa-type' => 'iframe-popup'));

                                        new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $courseid, "crosssubjid" => $crosssubjid, "exampleid" => $example->id)),
                                        $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
                                        array("target" => "_blank", 'exa-type' => 'iframe-popup'));
                                }

                                if (!$example_used) {
                                    $titleCell->text .= html_writer::link(new \block_exacomp\url('example_upload.php', ['action' => 'delete', 'exampleid' => $example->id, 'courseid' => $COURSE->id, 'returnurl' => g::$PAGE->url->out_as_local_url(false)]),
                                        $this->pix_icon("t/delete", block_exacomp_get_string("delete")),
                                        array("onclick" => "return confirm(".json_encode(block_exacomp_get_string('delete_confirmation', null, $example->title)).")"));
                                }

                                //print up & down icons
                                $titleCell->text .= html_writer::link("#", $this->pix_icon("t/up", block_exacomp_get_string('up')), array("exa-type" => "example-sorting", 'exa-direction' => 'up', "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));
                                $titleCell->text .= html_writer::link("#", $this->pix_icon("t/down", block_exacomp_get_string('down')), array("exa-type" => "example-sorting", 'exa-direction' => 'down', "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));

                                $titleCell->text .= '</span>';
                            }

                            if ($isEditingTeacher && ($role == BLOCK_EXACOMP_ROLE_TEACHER) && ($editmode || (!$editmode && $one_student && block_exacomp_is_example_visible($courseid, $example, 0)))) {
                                if ($example_used) {
                                    $titleCell->text .= html_writer::span($this->local_pix_icon("visibility_lock.png", block_exacomp_get_string('competence_locked'), array('height' => '18')), 'imglocked', array('title' => block_exacomp_get_string('competence_locked')));

                                } else {
                                    $titleCell->text .= $this->visibility_icon_example($visible_example, $example->id);
                                }
                            }

                            if ($url = $example->get_task_file_url()) {
                                $numberOfFiles = block_exacomp_get_number_of_files($example, 'example_task');
                                for ($i = 0; $i < $numberOfFiles; $i++) {
                                    $url = $example->get_task_file_url($i);
                                    $titleCell->text .= html_writer::link($url, $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                                }
                            }


                            if ($example->externalurl) {
                                $titleCell->text .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                            }

                            if ($example->externaltask) {
                                $titleCell->text .= html_writer::link($example->externaltask,
                                        block_exacomp_get_example_icon_simple($this, $example, 'externaltask'),
                                        array("target" => "_blank"));
                            }

                            $solution_url = $example->get_solution_file_url();
                            if (!$solution_url && @$example->externalsolution) {
                                $solution_url = $example->externalsolution;
                            }
                            // Display Icons to hide/unhide example solution visibility
                            if ($isEditingTeacher && $solution_url && $role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                // If solution exists and teacher is in edit mode, display icon
                                if ($editmode) {
                                    $titleCell->text .= $this->visibility_icon_example_solution($visible_solution, $example->id);
                                } else if ($one_student && block_exacomp_is_example_visible($courseid, $example, 0)) {
                                    // If solution exists, but is globally hidden, hide/unhide is not possibly for a single student
                                    if (isset($example->solution_visible) && !$example->solution_visible) //display disabled icon
                                    {
                                        $titleCell->text .= $this->visibility_icon_example_solution_disabled();
                                    } else {
                                        $titleCell->text .= $this->visibility_icon_example_solution($visible_solution, $example->id);
                                    }
                                }

                            }

                            if (($role == BLOCK_EXACOMP_ROLE_TEACHER || $visible_solution) && $solution_url) {
                                $titleCell->text .= $this->example_solution_icon($solution_url);
                            }

                            if ($this->is_print_mode()) {
                                // no icons in print mode
                            } else {
                                if (!$example->externalurl && !$example->externaltask && !block_exacomp_get_file_url($example, 'example_solution') && !block_exacomp_get_file_url($example, 'example_task') && $example->description) {
                                    $titleCell->text .= $this->pix_icon("i/preview", $example->description);
                                }

                                if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                    $titleCell->text .= $this->schedule_icon($example->id, $USER->id, $courseid);

                                    $titleCell->text .= $this->submission_icon($courseid, $example->id, $USER->id);

                                    $titleCell->text .= $this->competence_association_icon($example->id, $courseid, false);

                                } else if ($isEditingTeacher && $role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                    $studentid = block_exacomp_get_studentid();

                                    //auch für alle schüler auf wochenplan legen
                                    if (!$this->is_edit_mode()) {
                                        if ($visible_example) { //prevent errors
                                            $titleCell->text .= $this->schedule_icon($example->id, ($studentid) ? $studentid : BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $courseid);

                                            $titleCell->text .= html_writer::link("#",
                                                html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'), 'title' => block_exacomp_get_string('pre_planning_storage'))),
                                                array('class' => 'add-to-preplanning', 'exa-type' => 'add-example-to-schedule', 'exampleid' => $example->id, 'studentid' => 0, 'courseid' => $courseid));
                                        }
                                    }
                                    $titleCell->text .= $this->competence_association_icon($example->id, $courseid, $editmode);

                                }else if ($role == BLOCK_EXACOMP_ROLE_TEACHER){
                                    $titleCell->text .= $this->competence_association_icon($example->id, $courseid, $editmode);
                                }
                            }
                            $titleCell->text .= '</span>';

                        }
                        $exampleRow->cells[] = $titleCell;

                        $nivCell = new html_table_cell();

                        $nivText = [];
                        // 	        foreach ($example->taxonomies as $tax) {
                        // 	            $nivText[] = $tax->title;
                        // 	        }
                        $nivCell->text = join(' ', $nivText);
                        $exampleRow->cells[] = $nivCell;

                        $visible_student_example = $visible_example;
                        foreach ($students as $student) {
                            $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                            // 			        if (!$one_student && $descriptor_parent_visible[$student->id] == false) {
                            // 			            $visible_student_example = false;
                            // 			        } elseif (!$one_student && !$editmode) {
                            $visible_student_example = block_exacomp_is_example_visible($courseid, $example, $student->id);
                            // 			        }

                            //check reviewerid for teacher
                            if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                $reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLEEVAL, "teacher_reviewerid", array("studentid" => $student->id, "exampleid" => $example->id, "courseid" => $courseid));
                                if ($reviewerid == $USER->id || $reviewerid == 0) {
                                    $reviewerid = null;
                                }
                            }

                            $student_evaluation_cell = new html_table_cell();
                            $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            $teacher_evaluation_cell = new html_table_cell();
                            $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;


                            $niveau_cell = new html_table_cell();
                            $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            //disable cell for crosssubjectfiles
                            if ($role == BLOCK_EXACOMP_ROLE_TEACHER && !$isEditingTeacher) {
                                $disableCell = true;
                            } else {
                                $disableCell = ($role == BLOCK_EXACOMP_ROLE_STUDENT) ? true : (($visible_student_example) ? false : true);
                            }


                            if ($this->useEvalNiveau && block_exacomp_get_assessment_example_diffLevel() == 1) {
                                $niveau_cell->text = $this->generate_niveau_select(
                                    'niveau_examples',
                                    $example->id,
                                    'examples',
                                    $student,
                                    $disableCell,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                            } else {
                                $niveau_cell->text = '';
                            }

                            $niveau_cell->attributes['exa-timestamp'] = isset($student->examples->timestamp_teacher[$example->id]) ? $student->examples->timestamp_teacher[$example->id] : 0;

                            $example_params = array(
                                'name' => 'dataexamples-'.$example->id.'-'.$student->id,
                                'type' => 'text',
                                'maxlength' => 3,
                                'class' => 'percent-rating-text',
                                'value' => isset($student->examples->teacher_additional_grading[$example->id]) ?
                                    block_exacomp_format_eval_value($student->examples->teacher_additional_grading[$example->id]) : "",
                                'exa-compid' => $example->id,
                                'exa-userid' => $student->id,
                                'exa-type' => BLOCK_EXACOMP_TYPE_EXAMPLE,
                                'reviewerid' => ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
                                'reviewername' => $reviewername,
                            );

                            if (!$visible_student_example || $role == BLOCK_EXACOMP_ROLE_STUDENT || !$isEditingTeacher) {
                                $example_params['disabled'] = 'disabled';
                            }

                            // student show evaluation
                            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_EXAMPLE)) {

                                switch ($example_scheme) {
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                        $teacher_evaluation_cell->text = '';
                                        break;
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                        $teacher_evaluation_cell->text = $this->generate_checkbox(
                                            $checkboxname,
                                            $example->id,
                                            'examples',
                                            $student,
                                            "teacher",
                                            1,
                                            ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            null,
                                            ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                        break;
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                        $example_params['value'] = isset($student->examples->teacher_additional_grading[$descriptor->id]) ?
                                            block_exacomp_format_eval_value($student->examples->teacher_additional_grading[$descriptor->id]) : "";
                                        $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $example_params).'</span>';
                                        break;
                                    default: // Lists.
                                        $teacher_evaluation_cell->text = $this->generate_select(
                                            $checkboxname,
                                            $example->id,
                                            'examples',
                                            $student,
                                            "teacher",
                                            $example_scheme,
                                            ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                            ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                }
                            }

                            if (block_exacomp_get_assessment_example_SelfEval() == 1) {
                                // Only emojis?
                                $student_evaluation_cell->text = $this->generate_select(
                                    $checkboxname,
                                    $example->id,
                                    'examples',
                                    $student,
                                    "student",
                                    $example_scheme,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            $student_evaluation_cell->attributes['exa-timestamp'] = isset($student->examples->timestamp_teacher[$example->id]) ? $student->examples->timestamp_teacher[$example->id] : 0;

                            // Different order of options for students and teachers:
                            // Student
                            //student & niveau & showevaluation
                            if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                if ($this->useEvalNiveau && $showevaluation && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                if (showevaluation) {
                                    $exampleRow->cells[] = $teacher_evaluation_cell;
                                }
                                $exampleRow->cells[] = $student_evaluation_cell;
                            } else { // Teacher
                                if (showevaluation) {
                                    $exampleRow->cells[] = $student_evaluation_cell;
                                }
                                if ($this->useEvalNiveau && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                $teacher_evaluation_cell->text .= $this->submission_icon($courseid, $example->id, $student->id);
                                $teacher_evaluation_cell->text .= $this->resubmission_icon($example->id, $student->id, $courseid);
                                $exampleRow->cells[] = $teacher_evaluation_cell;
                            }

                        }
                        if ($profoundness) {
                            $emptyCell = new html_table_cell();
                            $emptyCell->colspan = 7 - count($exampleRow->cells);
                            $exampleRow->cells[] = $emptyCell;
                        }

                        $rows[] = $exampleRow;
                    }
                }//end crosssubjectfiles and total evaluation

            }

            $profoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;
			$evaluation = ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? 'teacher' : 'student';
			if (!$crosssubjid) {
				$subjectRow = new html_table_row();
				$subjectRow->attributes['class'] = 'exahighlight';

				//subject-title
				$title = new html_table_cell();
				$title->colspan = 2;
				$title->print_width = 5 + 25;

				$title->text = html_writer::tag("b", $subject->title);

				$subjectRow->cells[] = $title;

				$nivCell = new html_table_cell();
				$nivCell->print_width = 5;
				$subjectRow->cells[] = $nivCell;


				$checkboxname = 'datasubjects';
				$studentsCount = 0;
				foreach ($students as $student) {
					if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
					    // TEACHER VIEW.
						$reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, "reviewerid", array("userid" => $student->id, "compid" => $subject->id, "courseid" => $courseid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT));
						if ($reviewerid == $USER->id || $reviewerid == 0) {
							$reviewerid = null;
						}

						$columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

						$self_evaluation_cell = new html_table_cell();
						$self_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

						$evaluation_cell = new html_table_cell();
						$evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

 						$niveau_cell = new html_table_cell();
 						$niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
 						$niveau_cell->attributes['exa-timestamp'] = isset($student->subjects->timestamp_teacher[$subject->id]) ? $student->subjects->timestamp_teacher[$subject->id] : 0;

 						if ($this->useEvalNiveau && block_exacomp_get_assessment_subject_diffLevel()) {
 					        $niveau_cell->text = $this->generate_niveau_select('niveau_subject',
                                                                                $subject->id,
                                                                                'subjects',
                                                                                $student,
                                                                                !$isEditingTeacher,
                                                                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
 						} else {
                            $niveau_cell->text = '';
                        }

 					    //Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
 					    //the warning contains the name of the reviewer
                        if (isset($reviewerid) && $reviewerid > 0) {
                            if (!array_key_exists($reviewerid, $this->reviewers)) {
                                $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                                $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                                if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                                    $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
                                } else {
                                    $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                                    $reviewername = $reviewerTeacherUsername;
                                }
                                $this->reviewers[$reviewerid] = $reviewername;
                            } else {
                                $reviewername = $this->reviewers[$reviewerid];
                            }
                        } else {
                            $reviewername = '';
                        }
 					    //echo '<pre>';print_r($student); echo '</pre>';
						$params = array('name' => 'add-grading-'.$student->id.'-'.$subject->id, 'type' => 'text',
							'maxlength' => 3, 'class' => 'percent-rating-text',
							'value' => isset($student->subjects->student_additional_grading[$subject->id]) ?
								block_exacomp_format_eval_value($student->subjects->student_additional_grading[$subject->id]) : "",
							'exa-compid' => $subject->id, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_SUBJECT,
							'reviewerid' => ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
						    'reviewername' => $reviewername);


						//student & niveau & showevaluation
						/*if (block_exacomp_use_eval_niveau() && $role == BLOCK_EXACOMP_ROLE_STUDENT && $showevaluation) {
							$subjectRow->cells[] = $niveau_cell;
						}*/

                        // has no sence? here $role is BLOCK_EXACOMP_ROLE_TEACHER. look above if().
						/*if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
							if (block_exacomp_additional_grading() && $role == BLOCK_EXACOMP_ROLE_STUDENT) {    //use parent grading
								$evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
							} else {    //use drop down/checkbox values
								if ($scheme == 1) {
									$evaluation_cell->text = $this->generate_checkbox($checkboxname, $subject->id, 'subjects', $student, ($evaluation == "teacher") ? "student" : "teacher", $scheme, true);
								} else {
									$evaluation_cell->text = $this->generate_select($checkboxname, $subject->id, 'subjects', $student, ($evaluation == "teacher") ? "student" : "teacher", $scheme, true, $profoundness, $reviewerid);
								}
							}
						} else {
							$evaluation_cell->text = "";
						}*/
						// Self evaluate from student.
                        $subjectscheme = block_exacomp_get_assessment_subject_scheme();
                        $evaluation_cell->text = '';

                        if (block_exacomp_get_assessment_subject_SelfEval() == 1) {
                            // the student can evaluate self only by emojis
                            /*if ($subjectscheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO) { // Yes/No.
                                $evaluation_cell->text = $this->generate_checkbox(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'student',
                                        $subjectscheme,
                                        true,
                                        null,
                                        $reviewerid);
                            } else if ($subjectscheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // Input.
                                $params['disabled'] = true;
                                $evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                            } else { // Lists.*/
                                $evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'student',
                                        $subjectscheme,
                                        true,
                                        $profoundness,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            //}
 /*                           if ($subjectscheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE) {
                                $evaluation_cell->text = $this->generate_checkbox($checkboxname, $subject->id, 'subjects', $student,
                                        ($evaluation == "teacher") ? "student" : "teacher", $subjectscheme, true);
                            } else {

                            }*/
                        }


						if ($showevaluation) {
							$subjectRow->cells[] = $evaluation_cell;
						}

						if ($this->useEvalNiveau && $role == BLOCK_EXACOMP_ROLE_TEACHER && $this->diffLevelExists) {
							$subjectRow->cells[] = $niveau_cell;
						}

						/*if ($scheme == 1) {
							$self_evaluation_cell->text = $this->generate_checkbox($checkboxname, $subject->id, 'subjects', $student, $evaluation, $scheme, false, null, ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
						} else {
							if (block_exacomp_additional_grading() && $role == BLOCK_EXACOMP_ROLE_TEACHER) {
								$self_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
							} else {
								$self_evaluation_cell->text = $this->generate_select($checkboxname, $subject->id, 'subjects', $student, $evaluation, $scheme, false, $profoundness, ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
							}
						}*/

                        if (/*$role == BLOCK_EXACOMP_ROLE_STUDENT || */!$isEditingTeacher) {
                            $params['disabled'] = 'disabled';
                        } else {
                            unset($params['disabled']);
                        }

                        switch ($subjectscheme) {
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                $self_evaluation_cell->text = '';
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                $self_evaluation_cell->text = $this->generate_checkbox(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        $evaluation,
                                        1,
                                        false,
                                        null,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                break;
						    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                $params['value'] = isset($student->subjects->teacher_additional_grading[$subject->id]) ?
                                        block_exacomp_format_eval_value($student->subjects->teacher_additional_grading[$subject->id]) : "";
                                $self_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                break;
                            default: // Lists.
                                $self_evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        $evaluation,
                                        $subjectscheme,
                                        false,
                                        $profoundness,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
						}

						$self_evaluation_cell->attributes['exa-timestamp'] = isset($student->subjects->timestamp_teacher[$subject->id]) ? $student->subjects->timestamp_teacher[$subject->id] : 0;


                        //check if subject and course have the "isglobal" flag set and if the globalgradings text is not empty
                        //check if teacher is dakorateacher


                        if(@$subject->isglobal && block_exacomp_get_settings_by_course($courseid)->isglobal && block_exacomp_is_dakora_teacher($USER->id)){
                            if(array_key_exists($subject->id, @$student->subjects->globalgradings) && @$student->subjects->globalgradings[$subject->id] != ""){
                                //Add the other globalgradings as tooltipp
                                $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  @$student->subjects->globalgradings[$subject->id]).' ', array('id' => 'globalgradings', 'descrid' => $subject->id, 'studentid' => $student->id));
                                $self_evaluation_cell->text .= $globalgradings;
                            }else{
                                //if the course is global, the subject is global and the teacher is a dakorateacher AND the teacher has not graded anything yet, then the other global gradings have to be found
//                                $globalgradings_text = block_exacomp_get_globalgradings_single($subject->id,$student->id,BLOCK_EXACOMP_TYPE_SUBJECT);
//                                if($globalgradings_text){
//                                    $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  $globalgradings_text).' ', array('id' => 'globalgradings', 'descrid' => $subject->id, 'studentid' => $student->id));
//                                    $self_evaluation_cell->text .= $globalgradings;
//                                }
                            }
                        }

//                        if(@$subject->isglobal
//                                && block_exacomp_get_settings_by_course($courseid)->isglobal
//                                && array_key_exists($subject->id, @$student->subjects->globalgradings)
//                                && @$student->subjects->globalgradings[$subject->id] != ""
//                                && block_exacomp_is_dakora_teacher($USER->id)){
//                            //Add the other globalgradings as tooltipp
//                            $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  @$student->subjects->globalgradings[$subject->id]).' ', array('id' => 'globalgradings', 'descrid' => $subject->id, 'studentid' => $student->id));
//                            $self_evaluation_cell->text .= $globalgradings;
//                        }


                        if(@$student->subjects->gradinghistory[$subject->id] && substr_count(@$student->subjects->gradinghistory[$subject->id],'<br>') > 1){
//                            $gradinghistory = html_writer::tag('p', block_exacomp_get_string('gradinghistory'), array('id' => 'gradinghistory', 'descrid' => $subject->id, 'studentid' => $student->id, 'title' => @$student->subjects->gradinghistory[$subject->id]));
                            $gradinghistory = html_writer::tag('b', ' '.$this->pix_icon("i/duration",  @$student->subjects->gradinghistory[$subject->id]).' ', array('id' => 'gradinghistory', 'descrid' => $subject->id, 'studentid' => $student->id));
                            $self_evaluation_cell->text .= $gradinghistory;
                        }


						$subjectRow->cells[] = $self_evaluation_cell;
					} else {
					    // STUDENT VIEW.
                        //$empty_cell = new html_table_cell();
                        //$empty_cell->colspan = 1 + ((block_exacomp_use_eval_niveau()) ? 1 : 0) + (($showevaluation) ? 1 : 0);
                        //$subjectRow->cells[] = $empty_cell;

                        $niveau_cell = new html_table_cell();
                        $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
                        $niveau_cell->attributes['exa-timestamp'] = isset($student->subjects->timestamp_teacher[$subject->id]) ? $student->subjects->timestamp_teacher[$subject->id] : 0;
                        if ($this->useEvalNiveau && block_exacomp_get_assessment_subject_diffLevel()) {
                            $niveau_cell->text = $this->generate_niveau_select('niveau_subject',
                                    $subject->id,
                                    'subjects',
                                    $student,
                                    !$isEditingTeacher,
                                    null);
                        } else {
                            $niveau_cell->text = '';
                        }

                        $evaluation_cell = new html_table_cell();
                        $evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                        $self_evaluation_cell = new html_table_cell();
                        $self_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                       $params = array('name' => 'add-grading-'.$student->id.'-'.$subject->id, 'type' => 'text',
                                'maxlength' => 3, 'class' => 'percent-rating-text',
                                'value' => isset($student->subjects->teacher_additional_grading[$subject->id]) ?
                                        block_exacomp_format_eval_value($student->subjects->teacher_additional_grading[$subject->id]) : "",
                                'exa-compid' => $subject->id, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_SUBJECT,
                                'disabled' => 'disabled');

                        //student & niveau & showevaluation
                        if ($this->useEvalNiveau && $showevaluation && $this->diffLevelExists) {
                            $subjectRow->cells[] = $niveau_cell;
                        }

                        $subjectscheme = block_exacomp_get_assessment_subject_scheme();

                        switch ($subjectscheme) {
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                $evaluation_cell->text = '';
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                $evaluation_cell->text = $this->generate_checkbox(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'teacher',
                                        1,
                                        true,
                                        null,
                                        null);
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                $evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                break;
                            default: // Lists.
                                $evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'teacher',
                                        $subjectscheme,
                                        true,
                                        $profoundness,
                                        null);
                        }

                        $evaluation_cell->attributes['exa-timestamp'] = isset($student->subjects->timestamp_teacher[$subject->id]) ? $student->subjects->timestamp_teacher[$subject->id] : 0;

                        if ($showevaluation) {
                            $subjectRow->cells[] = $evaluation_cell;
                        }

                        $self_evaluation_cell->text = '';
                        if (block_exacomp_get_assessment_subject_SelfEval() == 1) {
                            // self evalueation is only emojis
                           /* if ($subjectscheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO) { // Yes/No.
                                $self_evaluation_cell->text = $this->generate_checkbox(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'student',
                                        $subjectscheme,
                                        false,
                                        null,
                                        null);
                            } else if ($subjectscheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // Input.
                                unset($params['disabled']);
                                $params['value'] = isset($student->subjects->student_additional_grading[$subject->id]) ?
                                        block_exacomp_format_eval_value($student->subjects->student_additional_grading[$subject->id]) : "";
                                $self_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                            } else { // Lists. (emojis)*/
                                $self_evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $subject->id,
                                        'subjects',
                                        $student,
                                        'student',
                                        $subjectscheme,
                                        false,
                                        $profoundness,
                                        ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            //}
                        }

                        $subjectRow->cells[] = $self_evaluation_cell;
					}
				}

				if ($this->is_print_mode()) {
					$cnt = count($subjectRow->cells);

					for ($i = 2; $i < $cnt; $i++) {
						$subjectRow->cells[$i]->print_width = (100 - (5 + 25 + 5)) / ($cnt - 2);
					}
				}

				$rows[] = $subjectRow;
			}

			/* TOPICS */
			$topicsscheme = block_exacomp_get_assessment_topic_scheme();
			//for every topic
			$data = (object)array(
				'subject' => $subject,
				'rg2_level' => 0, // $singletopic ? -1 : 0,
				'courseid' => $courseid,
				'showevaluation' => $showevaluation,
				'role' => $role,
				'scheme' => $topicsscheme,
				'profoundness' => block_exacomp_get_settings_by_course($courseid)->useprofoundness,
				'cm_mm' => block_exacomp_get_course_module_association($courseid),
				'eportfolioitems' => $eportfolioitems,
				'exaport_exists' => $this->exaportExists,
				'course_mods' => get_fast_modinfo($courseid)->get_cms(),
				'selected_topicid' => null,
				'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors,
			);
// 			echo "<br>";
// 			echo "<br>";
// 			echo "<br>";
// 			echo "<br>";

			$row_cnt = count($rows); // save row count to calc print_width
			$this->topics($rows, 0, $subject->topics, $data, $students, false, $this->is_edit_mode(), $crosssubjid, $isEditingTeacher); //renders the topics

			if ($this->is_print_mode()) {
				$row = $rows[$row_cnt];
				$row->cells[0]->print_width = 5;
				$row->cells[1]->print_width = 25;
				$row->cells[2]->print_width = 5;
				$cnt = count($row->cells);

				for ($i = 3; $i < $cnt; $i++) {
					$row->cells[$i]->print_width = (100 - (5 + 25 + 5)) / ($cnt - 3);
				}
			}

			$first = false;
		}




		$table->data = $rows;

		if ($this->is_print_mode()) {
			// set cell print width
			foreach ($rows as $row) {
				foreach ($row->cells as $cell) {
					if (!empty($cell->print_width)) {
						$cell->attributes['width'] = $cell->print_width.'%';
						// testing:
						// $cell->text = $cell->print_width.' '.$cell->text;
					}
				}
			}
		}

		$table_html = html_writer::table($table);

		if (!$this->is_print_mode()) {
			if ($rows && !$this->is_edit_mode() && $students) {
				$buttons = html_writer::tag("input", "", array("id" => "btn_submit",
                                                                "name" => "btn_submit",
                                                                "type" => "submit",
                                                                "class" => "btn btn-default",
                                                                "value" => block_exacomp_get_string("save_selection")));
				$table_html .= html_writer::div($buttons, '', array('id' => 'exabis_save_button'));
			}

			$table_html .= html_writer::end_tag('form');
		}

		return $table_html;
	}

	public function topics(&$rows, $level, $topics, $data, $students, $profoundness = false, $editmode = false, $crosssubjid = 0, $isEditingTeacher = true, $forReport = false) {
		global $DB, $USER, $COURSE;
		$topicparam = optional_param('topicid', 0, PARAM_INT);
		if (block_exacomp_is_topicgrading_enabled() || count($topics) > 0 || $topicparam == BLOCK_EXACOMP_SHOW_ALL_TOPICS) {
			// display topic row
			$display_topic_header_row = true;
			$child_level = $level + 1;
		} else {
			$display_topic_header_row = false;
			$child_level = $level;
		}

		$evaluation = ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? "teacher" : "student";

		foreach ($topics as $topic) {
			$parent_visible = (count($students) > 0) ? array_combine(array_keys($students), array_fill(0, count($students), true)) : array();

			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
			$studentsCount = 0;
			$studentsColspan = 1;

			$studentid = 0;
			$one_student = false;

			if (!$editmode && count($students) == 1 && block_exacomp_get_studentid() != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
				$studentid = array_values($students)[0]->id;
				$one_student = true;
			}

			$visible = block_exacomp_is_topic_visible($data->courseid, $topic, $studentid);

			if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER || $visible) {
				$visible_css = block_exacomp_get_visible_css($visible, $data->role);

				$this_rg2_class = ($data->rg2_level >= 0 ? 'rg2-level-'.$data->rg2_level : '').' '.$visible_css;

				$topicRow = new html_table_row();
 				$topicRow->attributes['class'] = 'exabis_comp_teilcomp '.$this_rg2_class.' exahighlight';
 				$topicRow->attributes['exa-rg2-id'] = 'topic-'.$topic->id;

				$outputidCell = new html_table_cell();
				$outputidCell->text = (block_exacomp_is_numbering_enabled()) ? block_exacomp_get_topic_numbering($topic->id) : '';
                $outputidCell->attributes['width'] = '5%';
				$topicRow->cells[] = $outputidCell;

 				$outputnameCell = new html_table_cell();
 				$outputnameCell->attributes['class'] = 'rg2-arrow rg2-indent';

				$topic_used = block_exacomp_is_topic_used($data->courseid, $topic, $studentid);

				// display the hide/unhide icon only in editmode or iff only 1 student is selected
                // and only display it if you are an editing teacher
				if ($isEditingTeacher && !$forReport && ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) && ($editmode || (!$editmode && $one_student && block_exacomp_is_topic_visible($data->courseid, $topic, 0)))) {
					if ($topic_used) {
						$outputname .= html_writer::span($this->local_pix_icon("visibility_lock.png", block_exacomp_get_string('competence_locked'), array('height' => '18')), 'imglocked', array('title' => block_exacomp_get_string('competence_locked')));
					} else {
						$outputname .= $this->visibility_icon_topic($visible, $topic->id);
					}
				}

				// ICONS
				/*
				if(isset($data->cm_mm->topics[$topic->id])) {
					//get CM instances
					$cm_temp = array();
					foreach($data->cm_mm->topics[$topic->id] as $cmid)
						$cm_temp[] = $data->course_mods[$cmid];

					$icon = block_exacomp_get_icon_for_user($cm_temp, $student);
					$icontext = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
				}
				*/

				$outputnameCell->text = html_writer::div($outputname, "desctitle");
				if ($this->is_print_mode()) {
                    $outputnameCell->text = html_writer::div(str_repeat('&nbsp;', 3).$outputname, "desctitle");
                }
                $outputnameCell->attributes['width'] = '30%';
				$topicRow->cells[] = $outputnameCell;

				$nivCell = new html_table_cell();
				$nivCell->text = "";
                $nivCell->attributes['width'] = '5%';

				$topicRow->cells[] = $nivCell;

				if ($profoundness) {
					$statCell = new html_table_cell();
					$statCell->text = " ";
					$statCell->colspan = 4;
                    $statCell->attributes['width'] = '60%';

					$topicRow->cells[] = $statCell;
				//} elseif (block_exacomp_is_topicgrading_enabled()) {
				} else { // always display topic lines
					$checkboxname = 'datatopics';
					$visible_student = $visible;

					foreach ($students as $student) {
						if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
							$reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, "reviewerid", array("userid" => $student->id, "compid" => $topic->id, "courseid" => $data->courseid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC));
							if ($reviewerid == $USER->id || $reviewerid == 0) {
								$reviewerid = null;
							}
						}
						$columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

						if (!$one_student && !$editmode) {
							$visible_student = block_exacomp_is_topic_visible($data->courseid, $topic, $student->id, true);
						}

						if (!$visible_student) {
							$parent_visible[$student->id] = false;
						}

						//TODO evt. needed
						//$studentCell->colspan = (!$profoundness) ? $studentsColspan : 4;

						$student_evaluation_cell = new html_table_cell();
						$student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

						$teacher_evaluation_cell = new html_table_cell();
                        $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

						$niveau_cell = new html_table_cell();
						$niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
						$niveau_cell->attributes['exa-timestamp'] = isset($student->topics->timestamp_teacher[$topic->id]) ? $student->topics->timestamp_teacher[$topic->id] : 0;

						if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER && !$isEditingTeacher){
						    $disableCell = true;
						} else {
						    $disableCell = ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) ? true : (($visible_student) ? false : true);
						}

                        if ($this->useEvalNiveau && block_exacomp_get_assessment_topic_diffLevel() == 1) {
                            $niveau_cell->text = $this->generate_niveau_select(
                                    'niveau_topic',
                                    $topic->id,
                                    'topics',
                                    $student,
                                    $disableCell,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                        } else {
                            $niveau_cell->text = '';
                        }

					    // Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
					    // the warning contains the name of the reviewer
                        $reviewername = '';
                        if (isset($reviewerid) && $reviewerid) {
                            if (!array_key_exists($reviewerid, $this->reviewers)) {
                                $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                                $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                                $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                                if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                                    $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
                                } else {
                                    $reviewername = $reviewerTeacherUsername;
                                }
                                $this->reviewers[$reviewerid] = $reviewername;
                            } else {
                                $reviewername = $this->reviewers[$reviewerid];
                            }
                        }
						$params = array('name' => 'add-grading-'.$student->id.'-'.$topic->id, 'type' => 'text',
							'maxlength' => 3, 'class' => 'percent-rating-text',
							'value' => isset($student->topics->teacher_additional_grading[$topic->id]) ?
								block_exacomp_format_eval_value($student->topics->teacher_additional_grading[$topic->id]) : "",
							'exa-compid' => $topic->id, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_TOPIC,
							'reviewerid' => ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
						    'reviewername' => $reviewername,
						);

						if (!$visible_student || $data->role == BLOCK_EXACOMP_ROLE_STUDENT  || !$isEditingTeacher) {
							$params['disabled'] = 'disabled';
						}


						// student show evaluation
						if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC)) {
						    switch ($data->scheme) {
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                    $teacher_evaluation_cell->text = '';
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                    $teacher_evaluation_cell->text = $this->generate_checkbox(
                                            $checkboxname,
                                            $topic->id,
                                            'topics',
                                            $student,
                                            "teacher",
                                            1,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            null,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                    $params['value'] = isset($student->topics->teacher_additional_grading[$topic->id]) ?
                                            block_exacomp_format_eval_value($student->topics->teacher_additional_grading[$topic->id]) : "";
                                    $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                    break;
                                default: // Lists.
                                    $teacher_evaluation_cell->text = $this->generate_select(
                                            $checkboxname,
                                            $topic->id,
                                            'topics',
                                            $student,
                                            "teacher",
                                            $data->scheme,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            //check if subject and course have the "isglobal" flag set and if the globalgradings text is not empty
                            //check if teacher is dakorateacher
                            if(@$data->subject->isglobal && block_exacomp_get_settings_by_course($COURSE->id)->isglobal && block_exacomp_is_dakora_teacher($USER->id)){
                                if(array_key_exists($topic->id, @$student->topics->globalgradings) && @$student->topics->globalgradings[$topic->id] != ""){
                                    //Add the other globalgradings as tooltipp
                                    $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  @$student->topics->globalgradings[$topic->id]).' ', array('id' => 'globalgradings', 'descrid' => $topic->id, 'studentid' => $student->id));
                                    $teacher_evaluation_cell->text .= $globalgradings;
                                }else{
                                    //if the course is global, the subject is global and the teacher is a dakorateacher AND the teacher has not graded anything yet, then the other global gradings have to be found
//                                    $globalgradings_text = block_exacomp_get_globalgradings_single($topic->id,$student->id,BLOCK_EXACOMP_TYPE_TOPIC);
//                                    if($globalgradings_text){
//                                        $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  $globalgradings_text).' ', array('id' => 'globalgradings', 'descrid' => $topic->id, 'studentid' => $student->id));
//                                        $teacher_evaluation_cell->text .= $globalgradings;
//                                    }
                                }
                            }

                            if(@$student->topics->gradinghistory[$topic->id] && substr_count(@$student->topics->gradinghistory[$topic->id],'<br>') > 1){
                                $gradinghistory = html_writer::tag('b', ' '.$this->pix_icon("i/duration", @$student->topics->gradinghistory[$topic->id]).' ', array('id' => 'gradinghistory', 'descrid' => $topic->id, 'studentid' => $student->id));
                                $teacher_evaluation_cell->text .= $gradinghistory;
                            }
                        }

                        if (block_exacomp_get_assessment_topic_SelfEval() == 1) {
						    // Only emojis?
                            $student_evaluation_cell->text = $this->generate_select(
                                    $checkboxname,
                                    $topic->id,
                                    'topics',
                                    $student,
                                    "student",
                                    $data->scheme,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                        }
						$student_evaluation_cell->attributes['exa-timestamp'] = isset($student->topics->timestamp_teacher[$topic->id]) ? $student->topics->timestamp_teacher[$topic->id] : 0;



						// ICONS
						if (isset($icontext)) {
							$student_evaluation_cell->text .= $icontext;
						}

						// Different order of options for students and teachers:
                        // Student
                        if ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) {
                            if ($this->useEvalNiveau && $data->showevaluation && $this->diffLevelExists) {
                                $topicRow->cells[] = $niveau_cell;
                            }
                            if ($data->showevaluation) {
                                $topicRow->cells[] = $teacher_evaluation_cell;
                            }
                            $topicRow->cells[] = $student_evaluation_cell;
                        } else { // Teacher
                            if ($data->showevaluation) {
                                $topicRow->cells[] = $student_evaluation_cell;
                            }
                            if ($this->useEvalNiveau && $this->diffLevelExists) {
                                $topicRow->cells[] = $niveau_cell;
                            }
                            $topicRow->cells[] = $teacher_evaluation_cell;
                        }

					}
				}

				// display topic header in edit mode, else the add descriptor field won't show up
				if ($editmode || (!empty($topic->descriptors) && $display_topic_header_row)) {
					$rows[] = $topicRow;
				}

				$child_data = clone $data;
				$child_data->rg2_level += $child_level - $level;
				$child_data->scheme = block_exacomp_get_assessment_comp_scheme();

				if (!empty($topic->descriptors)) {

// 				    echo "<br>";
// 				    echo "<br>";
// 				    echo "<br>";
// 				    echo "<br>";

					$this->descriptors($rows, $child_level, $topic->descriptors, $child_data, $students, $profoundness, $editmode, false, true, $crosssubjid, $parent_visible, $isEditingTeacher, $forReport);
					$this->descriptors($rows, $child_level, $topic->descriptors, $child_data, $students, $profoundness, $editmode, true, true, $crosssubjid, $parent_visible, $isEditingTeacher, $forReport);
				}


				if ($editmode && !$crosssubjid) {
					// kompetenz hinzufuegen (nicht bei themen)
					$niveauid = optional_param('niveauid', BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, PARAM_INT);
					//do not set niveauid for new descriptor if "show all" niveaus is selected
					if ($niveauid == BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
						$niveauid = 0;
					}

					$own_additionRow = new html_table_row();
					$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe exahighlight rg2 rg2-level-'.$child_data->rg2_level;

					$own_additionRow->cells[] = new html_table_cell();

					$cell = new html_table_cell();
					$cell->attributes['class'] = 'rg2-indent';
					$cell->text = html_writer::empty_tag('input', array('exa-type' => 'new-descriptor', 'type' => 'text', 'placeholder' => block_exacomp_trans(['de:Neue Kompetenz', 'en:New competence']), 'topicid' => $topic->id, 'niveauid' => $niveauid));
					if ($niveauid) {
						$cell->text .= html_writer::empty_tag('input', array('exa-type' => 'new-descriptor', 'type' => 'button', 'value' => block_exacomp_get_string('add')));
					} else {
						$cell->text .= html_writer::empty_tag('input', array('type' => 'button', 'value' => block_exacomp_get_string('add'), 'onclick' => 'alert('.json_encode(block_exacomp_get_string('add_competence_insert_learning_progress')).')'));
					}
					$own_additionRow->cells[] = $cell;
					$own_additionRow->cells[] = new html_table_cell();
					$rows[] = $own_additionRow;
				}
			}
		}
	}

	function descriptors(&$rows, $level, $descriptors, $data, $students, $profoundness = false, $editmode = false, $custom_created_descriptors = false, $parent = false, $crosssubjid = 0, $parent_visible = array(), $isEditingTeacher = true, $forReport = false) {
		global $USER, $COURSE, $DB;


		$evaluation = ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? "teacher" : "student";

		$showstudents = block_exacomp_get_studentid();
		foreach ($descriptors as $descriptor) {
			$descriptor_parent_visible = $parent_visible;

			if (!$editmode) {
				if ($custom_created_descriptors) {
					continue;
				}
			} else {
				if (!$custom_created_descriptors && $descriptor->source != BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR || ($custom_created_descriptors && $descriptor->source == BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR)) {
				} else {
					continue;
				}
			}
			$descriptor_in_crosssubj = ($crosssubjid <= 0) || array_key_exists($descriptor->id, block_exacomp_get_descriptors_for_cross_subject($data->courseid, $crosssubjid));

			//visibility
			//visible if
			//		- visible in whole course
			//	and - visible for specific student

			$one_student = false;
			$studentid = 0;
			if (!$editmode && count($students) == 1 && $showstudents != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
				$studentid = array_values($students)[0]->id;
				$one_student = true;
			}
			$descriptor_used = block_exacomp_descriptor_used($data->courseid, $descriptor, $studentid);
			//TODO: if used, always visible?
			$visible = block_exacomp_is_descriptor_visible($data->courseid, $descriptor, $studentid);
            $reviewername = '';
			if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER || $visible) {
				$visible_css = block_exacomp_get_visible_css($visible, $data->role);

				$checkboxname = "datadescriptors";
				list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor, false, $parent);
				$studentsCount = 0;

				$this_rg2_class = ($data->rg2_level >= 0 ? 'rg2-level-'.$data->rg2_level : '').' '.$visible_css;
				$sub_rg2_class = 'rg2-level-'.($data->rg2_level + 1);

				$descriptorRow = new html_table_row();

				if ($forReport) {
				    $this_rg2_class .= ' open ';
                }

 				$descriptorRow->attributes['class'] = ' exabis_comp_aufgabe '.$this_rg2_class;
 				$descriptorRow->attributes['exa-rg2-id'] = 'descriptor-'.$descriptor->id;
				if ($parent) {
					$descriptorRow->attributes['class'] = ' exabis_comp_teilcomp '.$this_rg2_class.' exahighlight';
				}


				$exampleuploadCell = new html_table_cell();
				if ($this->is_edit_mode() && !$this->is_print_mode() && $data->role == BLOCK_EXACOMP_ROLE_TEACHER && !$profoundness && $descriptor_in_crosssubj) {
					$exampleuploadCell->text = html_writer::link(
						new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $data->courseid, "descrid" => $descriptor->id, "topicid" => $descriptor->topicid)),
						html_writer::empty_tag('img', array('src' => 'pix/upload_12x12.png', 'alt' => 'upload')),
						array("target" => "_blank", 'exa-type' => 'iframe-popup'));
				}

				$exampleuploadCell->text .= $outputid.block_exacomp_get_descriptor_numbering($descriptor);
                $exampleuploadCell->attributes['width'] = '5%';

				$descriptorRow->cells[] = $exampleuploadCell;

				$titleCell = new html_table_cell();

				$titleCell->attributes['class'] = ' rg2-indent';
				if (($descriptor->examples || $descriptor->children || ($parent && $editmode)) && ($data->rg2_level >= 0)) {
					$titleCell->attributes['class'] .= ' rg2-arrow';
				}

				$title = [];
				$title[] = block_exacomp_get_string('import_source', null, $this->source_info($descriptor->source));
				if (isset($data->subject) && $author = $data->subject->get_author()) {
					$title[] = block_exacomp_get_string('author', 'repository').": ".$author."\n";
				}

				$title = join('<br />', $title);
                if ($this->is_print_mode()) {
                    if ($parent) {
                        $outputname = str_repeat('&nbsp;', 6).$outputname;
                    } else {
                        $outputname = str_repeat('&nbsp;', 9).$outputname;
                    }
                }
				$titleCell->text = html_writer::div(html_writer::tag('span', $outputname.($title ? $this->pix_icon("i/info", $title) : '')), '');

				// EDIT MODE BUTTONS
				if ($editmode) {

					$titleCell->text .= html_writer::link(
						new moodle_url('/blocks/exacomp/select_crosssubjects.php', array("courseid" => $data->courseid, "descrid" => $descriptor->id)),
						$this->pix_icon("i/withsubcat", block_exacomp_get_string("crosssubject")),
						array("target" => "_blank", 'exa-type' => 'iframe-popup'));
				}
				//if hidden in course, cannot be shown to one student

				if (!$this->is_print_mode() && !$forReport) {
					if ($isEditingTeacher && ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) && ($editmode || (!$editmode && $one_student && block_exacomp_is_descriptor_visible($data->courseid, $descriptor, 0)))) {
						if ($descriptor_used) {
							$titleCell->text .= html_writer::span($this->local_pix_icon("visibility_lock.png", block_exacomp_get_string('competence_locked'), array('height' => '18')), 'imglocked', array('title' => block_exacomp_get_string('competence_locked')));

						} else {
							$titleCell->text .= $this->visibility_icon_descriptor($visible, $descriptor->id);
						}
					}
					if ($editmode && $custom_created_descriptors) {
						$titleCell->text .= html_writer::link('descriptor.php?courseid='.$COURSE->id.'&id='.$descriptor->id, $this->pix_icon("i/edit", block_exacomp_get_string("edit")), array('exa-type' => 'iframe-popup', 'target' => '_blank'));
						$titleCell->text .= html_writer::link("", $this->pix_icon("t/delete", block_exacomp_get_string("delete")), array("onclick" => "if (confirm(".json_encode(block_exacomp_get_string('delete_confirmation_descr', null, $descriptor->title)).")) block_exacomp.delete_descriptor(".$descriptor->id."); return false;"));
					}
				}
				/*if ($editmode) {
					$titleCell->text .= ' '.$this->source_info($descriptor->source);
				}*/

                $titleCell->attributes['width'] = '30%';
				$descriptorRow->cells[] = $titleCell;

				$nivCell = new html_table_cell();
                $nivCell->attributes['width'] = '5%';

				$nivText = [];

				foreach ($descriptor->categories as $cat) {
					$nivText[] = $cat->title;
				}
				$nivCell->text = join(' ', $nivText);

				//--------------
				if ($editmode) {
				    $nivCell->text .= html_writer::link(
				        new moodle_url('/blocks/exacomp/update_categories.php', array("courseid" => $data->courseid, "descrid" => $descriptor->id)),
				        $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
				        array("target" => "_blank", 'exa-type' => 'iframe-popup'));
				}
				//------------
				$descriptorRow->cells[] = $nivCell;

				foreach ($students as $student) {
				    //echo "GEHT IM EDITMODE NICHT HIER REIN";
					$visible_student = $visible;

					$icontext = "";
					//check reviewerid for teacher
                    $reviewerid = null;
					if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
						$reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, "reviewerid", array("userid" => $student->id, "compid" => $descriptor->id, "courseid" => $data->courseid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR));
						if ($reviewerid == $USER->id || $reviewerid == 0) {
							$reviewerid = null;
						}
					}

					//check visibility for every student in overview
					if (!$one_student && empty($parent_visible[$student->id])) {
						$visible_student = false;
					} elseif ($visible && !$one_student && !$editmode) {
						$visible_student = block_exacomp_is_descriptor_visible($data->courseid, $descriptor, $student->id);
						if (!$one_student) {
							$descriptor_parent_visible[$student->id] = $visible_student;
						}
					}
					//$studentCell = new html_table_cell();
					$columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);
					//$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;

					// SHOW EVALUATION
					/*if($data->showevaluation) {
						$studentCellEvaluation = new html_table_cell();
						$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
					}*/

					// ICONS
					if (isset($data->cm_mm->competencies[$descriptor->id])) {
						//get CM instances
						$cm_temp = array();
						foreach ($data->cm_mm->competencies[$descriptor->id] as $cmid) {
							$cm_temp[] = $data->course_mods[$cmid];
						}

						$icon = block_exacomp_get_icon_for_user($cm_temp, $student);
						$icontext = '<span data-tooltip-content="'.s($icon->text).'" class="exabis-tooltip">'.$icon->img.'</span>';
					}
					//EPORTFOLIOITEMS
					if ($data->exaport_exists) {
						if (isset($data->eportfolioitems[$student->id]) && isset($data->eportfolioitems[$student->id]->competencies[$descriptor->id])) {
							$shared = false;
							$li_items = '';
							foreach ($data->eportfolioitems[$student->id]->competencies[$descriptor->id]->items as $item) {
								$li_item = $item->name;
								if ($item->shared) {
									$li_item .= block_exacomp_get_string('eportitem_shared');
									$shared = true;
								} else {
									$li_item .= block_exacomp_get_string('eportitem_notshared');
								}

								$li_items .= html_writer::tag('li', $li_item);
							}
							$first_param = 'id';
							$second_param = $item->viewid;
							if ($item->useextern) {
								$second_param = $item->hash;
								$first_param = 'hash';
							}
							// link to view if only 1 item, else link to shared_views list
							if (count($data->eportfolioitems[$student->id]->competencies[$descriptor->id]->items) == 1) {
								$link = new moodle_url('/blocks/exaport/shared_view.php', array('courseid' => $COURSE->id, 'access' => $first_param.'/'.$item->owner.'-'.$second_param));
							} else {
								$link = new moodle_url('/blocks/exaport/shared_views.php', array('courseid' => $COURSE->id, 'userid' => $student->id, 'sort' => 'timemodified'));
							}

							if ($shared) {
								$img = html_writer::link($link, html_writer::empty_tag("img", array("src" => "pix/folder_shared.png", "alt" => '')));
							} //$img = html_writer::empty_tag("img", array("src" => "pix/folder_shared.png","alt" => ''));
							else {
								$img = html_writer::empty_tag("img", array("src" => "pix/folder_notshared.png", "alt" => ''));
							}

							$text = block_exacomp_get_string('eportitems').html_writer::tag('ul', $li_items);

							$eportfoliotext = '<span title="'.$text.'" class="exabis-tooltip">'.$img.'</span>';
						} else {
							$eportfoliotext = '';
						}
					}
					// TIPP
					if (block_exacomp_set_tipp($descriptor->id, $student, 'activities_competencies', $data->scheme)) {
						$icon_img = html_writer::empty_tag('img', array('src' => "pix/info.png", "alt" => block_exacomp_get_string('teacher_tipp')));
						$string = block_exacomp_get_tipp_string($descriptor->id, $student, $data->scheme, 'activities_competencies', BLOCK_EXACOMP_TYPE_DESCRIPTOR);
						$tipptext = html_writer::span($icon_img, 'exabis-tooltip', array('title' => $string));
					}

					if (!$profoundness) {

						$student_evaluation_cell = new html_table_cell();
                        $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

						$teacher_evaluation_cell = new html_table_cell();
                        $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

//                         if($descriptor->parentid == 0){ //if descriptor is parentdescriptor
//                             if(block_exacomp_is_descriptor_grading_old($descriptor->id,$student->id)){
//                                 $teacher_evaluation_cell->attributes['bgcolor'] = "red";
//                             }
//                         }

 						$niveau_cell = new html_table_cell();
 						$niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
 						$niveau_cell->attributes['exa-timestamp'] = isset($student->competencies->timestamp_teacher[$descriptor->id]) ? $student->competencies->timestamp_teacher[$descriptor->id] : 0;

						if($data->role == BLOCK_EXACOMP_ROLE_TEACHER && !$isEditingTeacher){
						    $disableCell = true;
						} else {
						    $disableCell = ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) ? true : (($visible_student) ? false : true);
						}

                        if (($this->useEvalNiveau && block_exacomp_get_assessment_comp_diffLevel() == 1 && $level == 1)
                            || ($this->useEvalNiveau && block_exacomp_get_assessment_childcomp_diffLevel() == 1) && $level == 2) {
                            $niveau_cell->text = $this->generate_niveau_select(
                                    'niveau_descriptor',
                                    $descriptor->id,
                                    'competencies',
                                    $student,
                                    $disableCell,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                        } else {
                            $niveau_cell->text = '';
                        }

					    // Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
					    // the warning contains the name of the reviewer
                        if (!array_key_exists($reviewerid, $this->reviewers)) {
                            $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                            $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                            if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                                $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
                            } else {
                                $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                                $reviewername = $reviewerTeacherUsername;
                            }
                            $this->reviewers[$reviewerid] = $reviewername;
                        } else {
                            $reviewername = $this->reviewers[$reviewerid];
                        }
						$params = array('name' => 'add-grading-'.$student->id.'-'.$descriptor->id, 'type' => 'text',
							'maxlength' => 3, 'class' => 'percent-rating-text',
							'value' => isset($student->competencies->teacher_additional_grading[$descriptor->id]) ?
								block_exacomp_format_eval_value($student->competencies->teacher_additional_grading[$descriptor->id]) : "",
							'exa-compid' => $descriptor->id, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
							'reviewerid' => ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
						    'reviewername' => $reviewername,
						);

						if (!$visible_student || $data->role == BLOCK_EXACOMP_ROLE_STUDENT || !$isEditingTeacher) {
							$params['disabled'] = 'disabled';
						}


                        // student show evaluation
                        if ((   block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR) && $level == 1)
                                || (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD) && $level == 2)) {
                            switch ($data->scheme) {
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                    $teacher_evaluation_cell->text = '';
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                    $teacher_evaluation_cell->text = $this->generate_checkbox(
                                            $checkboxname,
                                            $descriptor->id,
                                            'competencies',
                                            $student,
                                            "teacher",
                                            1,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            null,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                    $params['value'] = isset($student->competencies->teacher_additional_grading[$descriptor->id]) ?
                                            block_exacomp_format_eval_value($student->competencies->teacher_additional_grading[$descriptor->id]) : "";
                                    $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                    break;
                                default: // Lists.
                                    $teacher_evaluation_cell->text = $this->generate_select(  //hier wird die das Dropdownmenue fürs Grading erzeugt
                                            $checkboxname,
                                            $descriptor->id,
                                            'competencies',
                                            $student,
                                            "teacher",
                                            $data->scheme,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                            ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            if ($descriptor->parentid == 0){ //if descriptor is parentdescriptor
                                if(array_key_exists($descriptor->id, $student->competencies->gradingisold) && $student->competencies->gradingisold[$descriptor->id]){
                                    //Hackable????
                                    $gradingisoldwarning = html_writer::tag('a', '     !!!', array('id' => 'gradingisold_warning', 'descrid' => $descriptor->id, 'studentid' => $student->id, 'title' => block_exacomp_get_string('newer_grading_tooltip'),'class' => 'competencegrid_tooltip'));
                                    $teacher_evaluation_cell->text .= $gradingisoldwarning;
                                }
                            }

                            //check if subject and course have the "isglobal" flag set and if the globalgradings text is not empty
                            //check if teacher is dakorateacher
                             if(@$data->subject->isglobal && block_exacomp_get_settings_by_course($COURSE->id)->isglobal && block_exacomp_is_dakora_teacher($USER->id)){
                                if(array_key_exists($descriptor->id, @$student->competencies->globalgradings) && @$student->competencies->globalgradings[$descriptor->id] != ""){
                                    //Add the other globalgradings as tooltipp
                                    $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  @$student->competencies->globalgradings[$descriptor->id]).' ', array('id' => 'globalgradings', 'descrid' => $descriptor->id, 'studentid' => $student->id));
                                    $teacher_evaluation_cell->text .= $globalgradings;
                                }else{
                                    //if the course is global, the subject is global and the teacher is a dakorateacher AND the teacher has not graded anything yet, then the other global gradings have to be found
//                                    $globalgradings_text = block_exacomp_get_globalgradings_single($descriptor->id,$student->id,BLOCK_EXACOMP_TYPE_DESCRIPTOR);
//                                    if($globalgradings_text){
//                                        $globalgradings = html_writer::tag('b', ' '.$this->pix_icon("i/groupevent",  $globalgradings_text).' ', array('id' => 'globalgradings', 'descrid' => $descriptor->id, 'studentid' => $student->id));
//                                        $teacher_evaluation_cell->text .= $globalgradings;
//                                    }
                                }
                            }

                            if(@$student->competencies->gradinghistory[$descriptor->id] && substr_count(@$student->competencies->gradinghistory[$descriptor->id],'<br>') > 1){
                                $gradinghistory = html_writer::tag('b', ' '.$this->pix_icon("i/duration", @$student->competencies->gradinghistory[$descriptor->id]).' ', array('id' => 'gradinghistory', 'descrid' => $descriptor->id, 'studentid' => $student->id));
                                $teacher_evaluation_cell->text .= $gradinghistory;
                            }
                        }

                        if ((block_exacomp_get_assessment_comp_SelfEval() == 1 && $level == 1)
                            ||(block_exacomp_get_assessment_childcomp_SelfEval() == 1 && $level == 2)) {
                            // Only emojis?
                            $student_evaluation_cell->text = $this->generate_select(
                                    $checkboxname,
                                    $descriptor->id,
                                    'competencies',
                                    $student,
                                    "student",
                                    $data->scheme,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                    ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                        }
                        $student_evaluation_cell->attributes['exa-timestamp'] = isset($student->competencies->timestamp_teacher[$descriptor->id]) ? $student->competencies->timestamp_teacher[$descriptor->id] : 0;

						// ICONS
						if (isset($icontext)) {
                            $student_evaluation_cell->text .= $icontext;
						}

						//EPORTFOLIOITEMS
						if (isset($eportfoliotext)) {
                            $student_evaluation_cell->text .= $eportfoliotext;
						}

						// TIPP
						if (isset($tipptext)) {
                            $student_evaluation_cell->text .= $tipptext;
						}

                        // Different order of options for students and teachers:
                        // Student
                        //student & niveau & showevaluation
                        if ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) {
                            if ($this->useEvalNiveau && $data->showevaluation && $this->diffLevelExists) {
                                $descriptorRow->cells[] = $niveau_cell;
                            }
                            if ($data->showevaluation) {
                                $descriptorRow->cells[] = $teacher_evaluation_cell;
                            }
                            $descriptorRow->cells[] = $student_evaluation_cell;
                        } else { // Teacher
                            if ($data->showevaluation) {
                                $descriptorRow->cells[] = $student_evaluation_cell;
                            }
                            if ($this->useEvalNiveau && $this->diffLevelExists) {
                                $descriptorRow->cells[] = $niveau_cell;
                            }
                            $descriptorRow->cells[] = $teacher_evaluation_cell;
                        }

					} else {
                        $titleCell->attributes['width'] = '30%';
						// ICONS
						if (isset($icontext) && !$forReport) {
							$titleCell->text .= $icontext;
						}

						//EPORTFOLIOITEMS
						if (isset($eportfoliotext)) {
							$titleCell->text .= $eportfoliotext;
						}

						// TIPP
						if (isset($tipptext)) {
							$titleCell->text .= $tipptext;
						}

						$cell1 = new html_table_cell();
						$cell2 = new html_table_cell();
						$disabledCell = new html_table_cell();
						if (!$forReport) {
                            $cell1->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 1);
                            $cell2->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 2);
                            $disabledCell->text = html_writer::checkbox("disabled", "", false, null, array("disabled" => "disabled"));
						} else {
                            $checked1 = (isset($student->competencies->{$evaluation}[$descriptor->id])) && $student->competencies->{$evaluation}[$descriptor->id] == 1;
                            $checked2 = (isset($student->competencies->{$evaluation}[$descriptor->id])) && $student->competencies->{$evaluation}[$descriptor->id] == 2;
                            $cell1->attributes['align'] = 'center';
                            $cell2->attributes['align'] = 'center';
                            $cell1->attributes['class'] = 'marker';
                            $cell2->attributes['class'] = 'marker';
                            $cell1->attributes['width'] = '15%';
                            $cell2->attributes['width'] = '15%';
                            $cell1->text = ($checked1 ? 'x' : '');
                            $cell2->text = ($checked2 ? 'x' : '');
                            $disabledCell->text = '';
                        }
						$disabledCell->attributes['class'] = 'disabled';

						// loaded from the descriptor, this field is filled from the xml import
						if (!$descriptor->profoundness) {
							$descriptorRow->cells[] = $cell1;
							$descriptorRow->cells[] = $cell2;
							$descriptorRow->cells[] = $disabledCell;
							$descriptorRow->cells[] = $disabledCell;
						} else {
							$descriptorRow->cells[] = $disabledCell;
							$descriptorRow->cells[] = $disabledCell;
							$descriptorRow->cells[] = $cell1;
							$descriptorRow->cells[] = $cell2;
						}

					}
				}

				$rows[] = $descriptorRow;

				$checkboxname = "dataexamples";
                $example_scheme = block_exacomp_get_assessment_example_scheme();
                if (!$profoundness && !$forReport) { // TODO: does profoundness need examples?
                    foreach ($descriptor->examples as $example) {
                        $example_used = block_exacomp_example_used($data->courseid, $example, $studentid);

                        $visible_example = block_exacomp_is_example_visible($data->courseid, $example, $studentid);
                        $visible_solution = block_exacomp_is_example_solution_visible($data->courseid, $example, $studentid);

                        // hide ethema_issubcategory for students
                        if ($data->role != BLOCK_EXACOMP_ROLE_TEACHER && $example->ethema_issubcategory) {
                            continue;
                        }

                        if ($data->role != BLOCK_EXACOMP_ROLE_TEACHER && !$visible_example) {
                            // do not display
                            continue;
                        }

                        $visible_example_css = block_exacomp_get_visible_css($visible_example, $data->role);

                        $studentsCount = 0;
                        $exampleRow = new html_table_row();
                        $exampleRow->attributes['class'] =
                                'exabis_comp_aufgabe block_exacomp_example '.$sub_rg2_class.$visible_example_css;
                        $exampleRow->cells[] = new html_table_cell();

                        $title = [];

                        if ($author = $example->get_author()) {
                            $title[] = block_exacomp_get_string('author', 'repository').": ".$author;
                        }
                        if (trim(strip_tags($example->description))) {
                            $title[] = $example->description;
                        }
                        if (trim($example->timeframe)) {
                            $title[] = $example->timeframe;
                        }
                        if (trim($example->tips)) {
                            $title[] = $example->tips;
                        }

                        $title = join('<br />', $title);

                        $titleCell = new html_table_cell();
                        $titleCell->attributes['class'] = 'rg2-indent';
                        $titleCell->style = 'padding-left: 30px;';
                        $exampletitle = $example->title;
                        if ($this->is_print_mode()) {
                            $exampletitle = str_repeat('&nbsp;', 12).$exampletitle;
                        }
                        $titleCell->text = html_writer::div(html_writer::tag('span', $exampletitle), '', ['title' => $title]);

                        if (!$this->is_print_mode() && !$forReport) {

                            if ($editmode) {
                                //echo "HIER IST ER IM EDITMODE";
                                $titleCell->text .= '<span style="padding-right: 15px;" class="todo-change-stylesheet-icons">';

                                if (block_exacomp_is_admin($COURSE->id) ||
                                        (isset($example->creatorid) && $example->creatorid == $USER->id)) {
                                    $titleCell->text .= html_writer::link(
                                            new moodle_url('/blocks/exacomp/example_upload.php',
                                                    array("courseid" => $data->courseid, "descrid" => $descriptor->id,
                                                            "topicid" => $descriptor->topicid, "exampleid" => $example->id)),
                                            $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
                                            array("target" => "_blank", 'exa-type' => 'iframe-popup'));
                                }

                                if (!$example_used) {
                                    $titleCell->text .= html_writer::link(new \block_exacomp\url('example_upload.php',
                                            ['action' => 'delete', 'exampleid' => $example->id, 'courseid' => $COURSE->id,
                                                    'returnurl' => g::$PAGE->url->out_as_local_url(false)]),
                                            $this->pix_icon("t/delete", block_exacomp_get_string("delete")),
                                            array("onclick" => "return confirm(".
                                                    json_encode(block_exacomp_get_string('delete_confirmation', null,
                                                            $example->title)).")"));
                                }

                                //print up & down icons
                                $titleCell->text .= html_writer::link("#", $this->pix_icon("t/up", block_exacomp_get_string('up')),
                                        array("exa-type" => "example-sorting", 'exa-direction' => 'up',
                                                "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));
                                $titleCell->text .= html_writer::link("#",
                                        $this->pix_icon("t/down", block_exacomp_get_string('down')),
                                        array("exa-type" => "example-sorting", 'exa-direction' => 'down',
                                                "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));

                                $titleCell->text .= '</span>';
                            }

                            if ($isEditingTeacher && ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) && ($editmode ||
                                            (!$editmode && $one_student &&
                                                    block_exacomp_is_example_visible($data->courseid, $example, 0)))) {
                                if ($example_used) {
                                    $titleCell->text .= html_writer::span($this->local_pix_icon("visibility_lock.png",
                                            block_exacomp_get_string('competence_locked'), array('height' => '18')), 'imglocked',
                                            array('title' => block_exacomp_get_string('competence_locked')));

                                } else {
                                    $titleCell->text .= $this->visibility_icon_example($visible_example, $example->id);
                                }
                            }

                            if ($url = $example->get_task_file_url()) {
                                $numberOfFiles = block_exacomp_get_number_of_files($example, 'example_task');
                                for ($i = 0; $i < $numberOfFiles; $i++) {
                                    $url = $example->get_task_file_url($i);
                                    $titleCell->text .= html_writer::link($url,
                                            $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')),
                                            array("target" => "_blank"));
                                }
                            }

                            if ($example->externalurl) {
                                $titleCell->text .= html_writer::link($example->externalurl,
                                        $this->local_pix_icon("globesearch.png", block_exacomp_get_string('preview')),
                                        array("target" => "_blank"));
                            }

                            if ($example->externaltask) {
                                //$icontag = block_exacomp_get_example_icon_for_externals($this, $COURSE->id, $example->externaltask);
                                $titleCell->text .= html_writer::link($example->externaltask,
                                        block_exacomp_get_example_icon_simple($this, $example, 'externaltask'),
                                        array("target" => "_blank"));
                            }

                            $solution_url = $example->get_solution_file_url();
                            if (!$solution_url && @$example->externalsolution) {
                                $solution_url = $example->externalsolution;
                            }
                            // Display Icons to hide/unhide example solution visibility
                            if ($isEditingTeacher && $solution_url && $data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                // If solution exists and teacher is in edit mode, display icon
                                if ($editmode) {
                                    $titleCell->text .= $this->visibility_icon_example_solution($visible_solution, $example->id);
                                } else if ($one_student && block_exacomp_is_example_visible($data->courseid, $example, 0)) {
                                    // If solution exists, but is globally hidden, hide/unhide is not possibly for a single student
                                    if (isset($example->solution_visible) && !$example->solution_visible) //display disabled icon
                                    {
                                        $titleCell->text .= $this->visibility_icon_example_solution_disabled();
                                    } else {
                                        $titleCell->text .= $this->visibility_icon_example_solution($visible_solution,
                                                $example->id);
                                    }
                                }

                            }

                            if (($data->role == BLOCK_EXACOMP_ROLE_TEACHER || $visible_solution) && $solution_url) {
                                $titleCell->text .= $this->example_solution_icon($solution_url);
                            }

                            if ($this->is_print_mode()) {
                                // no icons in print mode
                            } else {
                                if (!$example->externalurl && !$example->externaltask &&
                                        !block_exacomp_get_file_url($example, 'example_solution') &&
                                        !block_exacomp_get_file_url($example, 'example_task') && $example->description) {
                                    $titleCell->text .= $this->pix_icon("i/preview", $example->description);
                                }

                                if ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                    $titleCell->text .= $this->schedule_icon($example->id, $USER->id, $data->courseid);

                                    $titleCell->text .= $this->submission_icon($data->courseid, $example->id, $USER->id);

                                    $titleCell->text .= $this->competence_association_icon($example->id, $data->courseid, false);

                                } else if ($isEditingTeacher && $data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                    $studentid = block_exacomp_get_studentid();

                                    //auch für alle schüler auf wochenplan legen
                                    if (!$this->is_edit_mode()) {
                                        if ($visible_example) { //prevent errors
                                            $titleCell->text .= $this->schedule_icon($example->id,
                                                    ($studentid) ? $studentid : BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $data->courseid);

                                            $titleCell->text .= html_writer::link("#",
                                                    html_writer::empty_tag('img',
                                                            array('src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'),
                                                                    'title' => block_exacomp_get_string('pre_planning_storage'))),
                                                    array('class' => 'add-to-preplanning', 'exa-type' => 'add-example-to-schedule',
                                                            'exampleid' => $example->id, 'studentid' => 0,
                                                            'courseid' => $data->courseid));
                                        }
                                    }
                                    $titleCell->text .= $this->competence_association_icon($example->id, $data->courseid,
                                            $editmode);

                                } else if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                    $titleCell->text .= $this->competence_association_icon($example->id, $data->courseid,
                                            $editmode);
                                }
                            }
                            $titleCell->text .= '</span>';

                        }
                        $exampleRow->cells[] = $titleCell;

                        $nivCell = new html_table_cell();

                        $nivText = [];
                        foreach ($example->taxonomies as $tax) {
                            $nivText[] = $tax->title;
                        }
                        $nivCell->text = join(' ', $nivText);
                        $exampleRow->cells[] = $nivCell;

                        $visible_student_example = $visible_example;
                        foreach ($students as $student) {
                            //echo "GEHT IM EDITMODE NICHT HIER REIN22222";
                            $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                            if (!$one_student && array_key_exists($student->id, $descriptor_parent_visible) &&
                                    $descriptor_parent_visible[$student->id] == false) {
                                $visible_student_example = false;
                            } else if (!$one_student && !$editmode) {
                                $visible_student_example =
                                        block_exacomp_is_example_visible($data->courseid, $example, $student->id);
                            }

                            //check reviewerid for teacher
                            if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                $reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLEEVAL, "teacher_reviewerid",
                                        array("studentid" => $student->id, "exampleid" => $example->id,
                                                "courseid" => $data->courseid));
                                if ($reviewerid == $USER->id || $reviewerid == 0) {
                                    $reviewerid = null;
                                }
                            }

                            $student_evaluation_cell = new html_table_cell();
                            $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            $teacher_evaluation_cell = new html_table_cell();
                            $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            $niveau_cell = new html_table_cell();
                            $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            //Disable niv cell for Examples
                            if ($data->role == BLOCK_EXACOMP_ROLE_TEACHER && !$isEditingTeacher) {
                                $disableCell = true;
                            } else {
                                //echo $visible_student_example;
                                $disableCell = ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) ? true :
                                        (($visible_student_example) ? false : true);
                            }

                            if ($this->useEvalNiveau && block_exacomp_get_assessment_example_diffLevel() == 1) {
                                $niveau_cell->text = $this->generate_niveau_select(
                                        'niveau_examples',
                                        $example->id,
                                        'examples',
                                        $student,
                                        $disableCell,
                                        ($data->role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                            } else {
                                $niveau_cell->text = '';
                            }

                            $niveau_cell->attributes['exa-timestamp'] = isset($student->examples->timestamp_teacher[$example->id]) ?
                                    $student->examples->timestamp_teacher[$example->id] : 0;

                            $example_params = array(
                                    'name' => 'dataexamples-'.$example->id.'-'.$student->id,
                                    'type' => 'text',
                                    'maxlength' => 3,
                                    'class' => 'percent-rating-text',
                                    'value' => isset($student->examples->teacher_additional_grading[$example->id]) ?
                                            block_exacomp_format_eval_value($student->examples->teacher_additional_grading[$example->id]) :
                                            "",
                                    'exa-compid' => $example->id,
                                    'exa-userid' => $student->id,
                                    'exa-type' => BLOCK_EXACOMP_TYPE_EXAMPLE,
                                    'reviewerid' => ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
                                    'reviewername' => $reviewername,
                            );

                            if (!$visible_student_example
                                    || $data->role == BLOCK_EXACOMP_ROLE_STUDENT
                                    || !$isEditingTeacher
                                    || block_exacomp_is_autograding_example($example->id)
                            ) {
                                $example_params['disabled'] = 'disabled';
                            }

                            //student show evaluation
                            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_EXAMPLE)) {
                                switch ($example_scheme) {
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE: // None.
                                        $teacher_evaluation_cell->text = '';
                                        break;
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO: // Yes/No.
                                        $teacher_evaluation_cell->text = $this->generate_checkbox(
                                                $checkboxname,
                                                $example->id,
                                                'examples',
                                                $student,
                                                "teacher",
                                                1,
                                                ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                                null,
                                                ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                        break;
                                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE: // Input.
                                        $example_params['value'] = isset($student->examples->teacher[$example->id]) ?
                                                block_exacomp_format_eval_value($student->examples->teacher[$example->id]) :
                                                ""; // TODO: may be $student->examples->teacher_additional_grading?
                                        $teacher_evaluation_cell->text =
                                                '<span class="percent-rating">'.html_writer::empty_tag('input', $example_params).
                                                '</span>';
                                        break;
                                    default: // Lists.
                                        $teacher_evaluation_cell->text = $this->generate_select(
                                                $checkboxname,
                                                $example->id,
                                                'examples',
                                                $student,
                                                "teacher",
                                                $example_scheme,
                                                ($data->role == BLOCK_EXACOMP_ROLE_TEACHER &&
                                                        @$example_params['disabled'] != 'disabled') ? false : true,
                                                ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                                ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                }
                            }

                            if (block_exacomp_get_assessment_example_SelfEval() == 1) {
                                // Only emojis?
                                $student_evaluation_cell->text = $this->generate_select(
                                        $checkboxname,
                                        $example->id,
                                        'examples',
                                        $student,
                                        "student",
                                        $example_scheme,
                                        ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                        ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $data->profoundness : null,
                                        ($data->role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            $student_evaluation_cell->attributes['exa-timestamp'] =
                                    isset($student->examples->timestamp_teacher[$example->id]) ?
                                            $student->examples->timestamp_teacher[$example->id] : 0;

                            // Different order of options for students and teachers:
                            // Student
                            //student & niveau & showevaluation
                            if ($data->role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                if ($this->useEvalNiveau && $data->showevaluation && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                if ($data->showevaluation) {
                                    $exampleRow->cells[] = $teacher_evaluation_cell;
                                }
                                $exampleRow->cells[] = $student_evaluation_cell;
                            } else { // Teacher
                                if ($data->showevaluation) {
                                    $exampleRow->cells[] = $student_evaluation_cell;
                                }
                                if ($this->useEvalNiveau && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                $teacher_evaluation_cell->text .= $this->submission_icon($data->courseid, $example->id,
                                        $student->id);
                                $teacher_evaluation_cell->text .= $this->resubmission_icon($example->id, $student->id,
                                        $data->courseid);
                                $exampleRow->cells[] = $teacher_evaluation_cell;
                            }

                        }
                        if ($profoundness) {
                            $emptyCell = new html_table_cell();
                            $emptyCell->colspan = 7 - count($exampleRow->cells);
                            $exampleRow->cells[] = $emptyCell;
                        }

                        $rows[] = $exampleRow;
                    }
                }
				$child_data = clone $data;
				$child_data->rg2_level++;
                $child_data->scheme = block_exacomp_get_assessment_childcomp_scheme();

				if (!empty($descriptor->children)) {
					$this->descriptors($rows, $level + 1, $descriptor->children, $child_data, $students, $profoundness, $editmode, false, false, $crosssubjid, $descriptor_parent_visible, $isEditingTeacher, $forReport);
				}
				//schulische ergänzungen und neue teilkompetenz
				if ($editmode && $parent && !$forReport) {

					$own_additionRow = new html_table_row();
					$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe '.$sub_rg2_class;
					$own_additionRow->cells[] = new html_table_cell();

					$cell = new html_table_cell();
					$cell->text = block_exacomp_get_string('own_additions');
					$own_additionRow->cells[] = $cell;

					$own_additionRow->cells[] = new html_table_cell();

					$rows[] = $own_additionRow;

					// is this was a bug? it's printed twice?
					// no, first print 	the imported descriptors, then print the user created ones
					$this->descriptors($rows, $level + 1, $descriptor->children, $child_data, $students, $profoundness, $editmode, true);

					$own_additionRow = new html_table_row();
					$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe '.$sub_rg2_class;

					$own_additionRow->cells[] = new html_table_cell();

					if ($descriptor_in_crosssubj) {
						$cell = new html_table_cell();
						$cell->attributes['class'] = 'rg2-indent';
						$cell->text = html_writer::empty_tag('input', array('exa-type' => 'new-descriptor', 'type' => 'text', 'placeholder' => block_exacomp_trans(['de:Neue Teilkompetenz', 'en:New child competence']), 'parentid' => $descriptor->id));
						$cell->text .= html_writer::empty_tag('input', array('exa-type' => 'new-descriptor', 'type' => 'button', 'value' => block_exacomp_get_string('add')));
						$own_additionRow->cells[] = $cell;
					}
					$own_additionRow->cells[] = new html_table_cell();
					$rows[] = $own_additionRow;
				}
			}
		}
	}

	public function example_based_overview($subjects, $courseid, $students, $showevaluation, $role, $scheme = 1, $singletopic = false, $crosssubjid = 0, $isEditingTeacher = true) {
        global $DB, $USER;

        $table = new html_table();
        $rows = array();
        $studentsColspan = $showevaluation ? 2 : 1;
        if ($this->useEvalNiveau && ($showevaluation || $role == BLOCK_EXACOMP_ROLE_TEACHER)) {
            $studentsColspan++;
        }

        $table->attributes['class'] = 'exabis_comp_comp rg2 exabis-tooltip';

        // in the future maybe use lscache or some other method?
        if ($crosssubjid) {
            $table->attributes['exa-rg2-storageid'] = 'cross_subject-'.$crosssubjid;
        } elseif (count($subjects) == 1) {
            $subject = reset($subjects);
            $table->attributes['exa-rg2-storageid'] = 'assign_competencies-'.'subject-'.$subject->id;
            if (count($subject->topics) == 1) {
                $topic = reset($subject->topics);
                $table->attributes['exa-rg2-storageid'] .= '-topic-'.$topic->id;
            }
        }

        if ($this->exaportExists) {
            $eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
        } else {
            $eportfolioitems = array();
        }




        /* SUBJECTS */
        $first = true;
        $course_subs = block_exacomp_get_subjects_by_course($courseid);
        $usesubjectgrading = block_exacomp_is_subjectgrading_enabled();

        foreach ($subjects as $subject) {
            if (!$subject->topics) {
                continue;
            }

            if ($first) {
                //for every subject
                $subjectRow = new html_table_row();
                $subjectRow->attributes['class'] = 'exahighlight';

                //subject-title
                $title = new html_table_cell();
                $title->colspan = 2;

                if ($crosssubjid) {
                    $title->text = html_writer::tag("b", block_exacomp_get_string('comps_and_material'));
                } else {
                    $title->text = ($usesubjectgrading) ? '' : html_writer::tag("b", $subject->title);
                }

                $title->print_width = 5 + 25;
                $subjectRow->cells[] = $title;
            }

            $nivCell = new html_table_cell();
            $nivCell->text = block_exacomp_get_string('competence_grid_niveau');
            $nivCell->print_width = 5;

            if ($first) {
                $subjectRow->cells[] = $nivCell;
            }

            $studentsCount = 0;

            foreach ($students as $student) {
                $studentCell = new html_table_cell();
                $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                $studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;
                $studentCell->print_width = (100 - (5 + 25 + 5)) / count($students);
                $studentCell->colspan = $studentsColspan;
                $studentCell->text = fullname($student);
                if (!$this->is_print_mode() && block_exacomp_is_exastud_installed() && ($info = \block_exastud\api::get_student_review_link_info_for_teacher($student->id))) {
                    $studentCell->text .= ' <a href="'.$info->url.'" title="'.block_exacomp_trans('de:Überfachliche Bewertung').'" onclick="window.open(this.href,this.target,\'width=880,height=660,scrollbars=yes\'); return false;">'.'<img src="pix/review_student.png" />'.'</a>';
                }

                if ($this->is_print_mode()) {
                    // zeilenumbruch im namen beim drucken: nur erstes leerzeichen durch <br> ersetzen
                    $studentCell->text = preg_replace('!\s!', '<br />', $studentCell->text, 1);
                }

                if ($first) {
                    $subjectRow->cells[] = $studentCell;
                }
            }

            if ($first) {
                $rows[] = $subjectRow;
            }

            if ($showevaluation) {
                $studentsCount = 0;

                $evaluationRow = new html_table_row();
                $emptyCell = new html_table_cell();
                $emptyCell->colspan = 3;
                $evaluationRow->cells[] = $emptyCell;

                foreach ($students as $student) {
                    $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                    $firstCol = new html_table_cell();
                    $firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;
                    $secCol = new html_table_cell();
                    $secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-'.$columnGroup;

                    if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
                        $firstCol->text = block_exacomp_get_string('studentshortcut');
                        $secCol->text = block_exacomp_get_string('teachershortcut');
                        if ($this->useEvalNiveau && $this->diffLevelExists) {
                            $secCol->colspan = 2;
                        }
                    } else {
                        $firstCol->text = block_exacomp_get_string('teachershortcut');
                        if ($this->useEvalNiveau && $this->diffLevelExists) {
                            $firstCol->colspan = 2;
                        }
                        $secCol->text = block_exacomp_get_string('studentshortcut');
                    }

                    $evaluationRow->cells[] = $firstCol;
                    $evaluationRow->cells[] = $secCol;
                }
                $rows[] = $evaluationRow;
            }
            //$rows[] = new html_table_row();
            //total evaluation crosssub row
            //if ($crosssubjid && !$this->is_edit_mode() && $students) {
            if ($first) {
                if ($crosssubjid) {
                    $profoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;
                    $evaluation = ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? "teacher" : "student";

                    $examples_crosssubj = block_exacomp_get_examples_for_crosssubject($crosssubjid);

                    $visible_css = block_exacomp_get_visible_css(true, $role);

                    $this_rg2_class = 'rg2-level-0'.$visible_css;
                    $sub_rg2_class = 'rg2-level-1';

                    $studentsCount = 0;
                    $checkboxname = 'datacrosssubs'; //?
                    //$student = array_values($students)[0];




                    foreach ($students as $student) {
                        if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
                            $reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, "reviewerid", array("userid" => $student->id, "compid" => $crosssubjid, "courseid" => $courseid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB));
                            if ($reviewerid == $USER->id || $reviewerid == 0) {
                                $reviewerid = null;
                            }
                        }
                        $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                        $student_evaluation_cell = new html_table_cell();
                        $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                        $teacher_evaluation_cell = new html_table_cell();
                        $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                        $niveau_cell = new html_table_cell();
                        $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;
                        $niveau_cell->attributes['exa-timestamp'] = isset($student->crosssubs->timestamp_teacher[$crosssubjid]) ? $student->crosssubs->timestamp_teacher[$crosssubjid] : 0;

                        if ($this->useEvalNiveau && block_exacomp_get_assessment_theme_diffLevel() == 1) {
                            $niveau_cell->text = $this->generate_niveau_select(
                                'niveau_crosssub',
                                $crosssubjid,
                                'crosssubs',
                                $student,
                                ($role == BLOCK_EXACOMP_ROLE_STUDENT ? true : false),
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                        } else {
                            $niveau_cell->text = '';
                        }

                        //Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
                        //the warning contains the name of the reviewer
                        if (isset($reviewerid) && $reviewerid > 0) {
                            if (!array_key_exists($reviewerid, $this->reviewers)) {
                                $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                                $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                                if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                                    $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
                                } else {
                                    $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                                    $reviewername = $reviewerTeacherUsername;
                                }
                                $this->reviewers[$reviewerid] = $reviewername;
                            } else {
                                $reviewername = $this->reviewers[$reviewerid];
                            }
                        } else {
                            $reviewername = '';
                        }
                        $params = array('name' => 'add-grading-'.$student->id.'-'.$crosssubjid, 'type' => 'text',
                            'maxlength' => 3, 'class' => 'percent-rating-text',
                            'value' => isset($student->crosssubs->teacher_additional_grading[$crosssubjid]) ?
                            block_exacomp_format_eval_value($student->crosssubs->teacher_additional_grading[$crosssubjid]) : "",
                            'exa-compid' => $crosssubjid, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_CROSSSUB,
                            'reviewername' => $reviewername);

                            if ($role == BLOCK_EXACOMP_ROLE_STUDENT  || !$isEditingTeacher) {
                                $params['disabled'] = 'disabled';
                            }

                            //student show evaluation
                            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_CROSSSUB)) {
                                if ($scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE) { // None.
                                    $teacher_evaluation_cell->text = '';
                                } else if ($scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO) { // Yes/No.
                                    $teacher_evaluation_cell->text = $this->generate_checkbox(
                                    $checkboxname,
                                    $crosssubjid,
                                    'crosssubs',
                                    $student,
                                    "teacher",
                                    1,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                    null,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                } else if ($scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // Input.
                                    $params['value'] = isset($student->crosssubs->teacher_additional_grading[$crosssubjid]) ?
                                    block_exacomp_format_eval_value($student->crosssubs->teacher_additional_grading[$crosssubjid]) : "";
                                    $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>';
                                } else { // Lists.
                                    $teacher_evaluation_cell->text = $this->generate_select(
                                    $checkboxname,
                                    $crosssubjid,
                                    'crosssubs',
                                    $student,
                                    "teacher",
                                    $scheme,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                }
                            }

                            if (block_exacomp_get_assessment_theme_SelfEval() == 1) {
                                // Only emojis?
                                $student_evaluation_cell->text = $this->generate_select(
                                $checkboxname,
                                $crosssubjid,
                                'crosssubs',
                                $student,
                                "student",
                                $scheme,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            $student_evaluation_cell->attributes['exa-timestamp'] = isset($student->crosssubs->timestamp_teacher[$crosssubjid]) ? $student->crosssubs->timestamp_teacher[$crosssubjid] : 0;

                            // Different order of options for students and teachers:
                            // Student
                            if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                if ($this->useEvalNiveau && $showevaluation && $this->diffLevelExists) {
                                    $totalRow->cells[] = $niveau_cell;
                                }
                                if ($showevaluation) {
                                    $totalRow->cells[] = $teacher_evaluation_cell;
                                }
                                $totalRow->cells[] = $student_evaluation_cell;
                            } else { // Teacher
                                if ($showevaluation) {
                                    $totalRow->cells[] = $student_evaluation_cell;
                                }
                                if ($this->useEvalNiveau && $this->diffLevelExists) {
                                    $totalRow->cells[] = $niveau_cell;
                                }
                                $totalRow->cells[] = $teacher_evaluation_cell;
                            }

                    }



                    /* CROSSSUBJECTS */
                    //crosssubjectfiles and total eval
                    $checkboxname = "dataexamples";
                    $example_scheme = block_exacomp_get_assessment_example_scheme();

                    //Get associated files
                    //get files from competencies that are added to this cross:
                    $examples_assoc_crosssubj = \block_exacomp\example::get_objects_sql("
                        SELECT DISTINCT e.*
                        FROM {".BLOCK_EXACOMP_DB_DESCCROSS."} dc
                            JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON dc.descrid = de.descrid
                            JOIN {".BLOCK_EXACOMP_DB_EXAMPLES."} e ON e.id = de.exampid
                        WHERE dc.crosssubjid = ?
                        ORDER BY e.title
                    ", [$crosssubjid]);

                    //get files from the childcompetencies of the competencies that are added
                    //get descriptor sand check if they are parents
                    //if they are parent --> get the examples of their children
                    $assoc_descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid);

                    foreach ($assoc_descriptors as $descriptor) {
                        if($descriptor->parentid == 0){
                            $childdescriptors = block_exacomp_get_child_descriptors($descriptor, $courseid);
                            foreach ($childdescriptors as $childdescriptor){
                                $examples_assoc_crosssubj = array_merge($examples_assoc_crosssubj,$childdescriptor->examples);
                            }
                        }
                    }

                    $examples_crosssubj = array_merge($examples_crosssubj, $examples_assoc_crosssubj);

                    foreach ($examples_crosssubj as $example) {
                        $example_used = block_exacomp_example_used($courseid, $example, $studentid);

                        $visible_example = block_exacomp_is_example_visible($courseid, $example, $studentid);
                        $visible_solution = block_exacomp_is_example_solution_visible($courseid, $example, $studentid);

                        if ($role != BLOCK_EXACOMP_ROLE_TEACHER && !$visible_example) {
                            // do not display
                            continue;
                        }

                        $visible_example_css = block_exacomp_get_visible_css($visible_example, $role);

                        $studentsCount = 0;
                        $exampleRow = new html_table_row();
//                         $exampleRow->attributes['class'] = 'exabis_comp_aufgabe block_exacomp_example '.$sub_rg2_class.$visible_example_css;
                        $exampleRow->cells[] = new html_table_cell();


                        $title = [];

                        if ($author = $example->get_author()) {
                            $title[] = block_exacomp_get_string('author', 'repository').": ".$author;
                        }
                        if (trim(strip_tags($example->description))) {
                            $title[] = $example->description;
                        }
                        if (trim($example->timeframe)) {
                            $title[] = $example->timeframe;
                        }
                        if (trim($example->tips)) {
                            $title[] = $example->tips;
                        }

                        $title = join('<br />', $title);

                        $titleCell = new html_table_cell();
                        $titleCell->attributes['class'] = 'rg2-indent';
                        $titleCell->style = 'padding-left: 30px;';
                        $titleCell->text = html_writer::div(html_writer::tag('span', $example->title), '', ['title' => $title]);

                        if (!$this->is_print_mode()) {

                            if ($editmode) {
                                $titleCell->text .= '<span style="padding-right: 15px;" class="todo-change-stylesheet-icons">';

                                if (block_exacomp_is_admin($COURSE->id) || (isset($example->creatorid) && $example->creatorid == $USER->id)) {
                                    $titleCell->text .= html_writer::link(
                                        // 	                            new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $data->courseid, "descrid" => $descriptor->id, "topicid" => $descriptor->topicid, "exampleid" => $example->id)),
                                        // 	                            $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
                                        // 	                            array("target" => "_blank", 'exa-type' => 'iframe-popup'));

                                        new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => $courseid, "crosssubjid" => $crosssubjid, "exampleid" => $example->id)),
                                        $this->pix_icon("i/edit", block_exacomp_get_string("edit")),
                                        array("target" => "_blank", 'exa-type' => 'iframe-popup'));
                                }

                                if (!$example_used) {
                                    $titleCell->text .= html_writer::link(new \block_exacomp\url('example_upload.php', ['action' => 'delete', 'exampleid' => $example->id, 'courseid' => $COURSE->id, 'returnurl' => g::$PAGE->url->out_as_local_url(false)]),
                                        $this->pix_icon("t/delete", block_exacomp_get_string("delete")),
                                        array("onclick" => "return confirm(".json_encode(block_exacomp_get_string('delete_confirmation', null, $example->title)).")"));
                                }

                                //print up & down icons
                                $titleCell->text .= html_writer::link("#", $this->pix_icon("t/up", block_exacomp_get_string('up')), array("exa-type" => "example-sorting", 'exa-direction' => 'up', "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));
                                $titleCell->text .= html_writer::link("#", $this->pix_icon("t/down", block_exacomp_get_string('down')), array("exa-type" => "example-sorting", 'exa-direction' => 'down', "exa-exampleid" => $example->id, "exa-descrid" => $descriptor->id));

                                $titleCell->text .= '</span>';
                            }

                            if ($isEditingTeacher && ($role == BLOCK_EXACOMP_ROLE_TEACHER) && ($editmode || (!$editmode && $one_student && block_exacomp_is_example_visible($courseid, $example, 0)))) {
                                if ($example_used) {
                                    $titleCell->text .= html_writer::span($this->local_pix_icon("visibility_lock.png", block_exacomp_get_string('competence_locked'), array('height' => '18')), 'imglocked', array('title' => block_exacomp_get_string('competence_locked')));

                                } else {
                                    $titleCell->text .= $this->visibility_icon_example($visible_example, $example->id);
                                }
                            }


                            if ($url = $example->get_task_file_url()) {
                                $numberOfFiles = block_exacomp_get_number_of_files($example, 'example_task');
                                for ($i = 0; $i < $numberOfFiles; $i++) {
                                    $url = $example->get_task_file_url($i);
                                    $titleCell->text .= html_writer::link($url, $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                                }
                            }


                            if ($example->externalurl) {
                                $titleCell->text .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                            }

                            if ($example->externaltask) {
                                $titleCell->text .= html_writer::link($example->externaltask,
                                        block_exacomp_get_example_icon_simple($this, $example, 'externaltask'),
                                        array("target" => "_blank"));
                            }

                            $solution_url = $example->get_solution_file_url();
                            if (!$solution_url && @$example->externalsolution) {
                                $solution_url = $example->externalsolution;
                            }
                            // Display Icons to hide/unhide example solution visibility
                            if ($isEditingTeacher && $solution_url && $role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                // If solution exists and teacher is in edit mode, display icon
                                if ($editmode) {
                                    $titleCell->text .= $this->visibility_icon_example_solution($visible_solution, $example->id);
                                } else if ($one_student && block_exacomp_is_example_visible($courseid, $example, 0)) {
                                    // If solution exists, but is globally hidden, hide/unhide is not possibly for a single student
                                    if (isset($example->solution_visible) && !$example->solution_visible) //display disabled icon
                                    {
                                        $titleCell->text .= $this->visibility_icon_example_solution_disabled();
                                    } else {
                                        $titleCell->text .= $this->visibility_icon_example_solution($visible_solution, $example->id);
                                    }
                                }

                            }

                            if (($role == BLOCK_EXACOMP_ROLE_TEACHER || $visible_solution) && $solution_url) {
                                $titleCell->text .= $this->example_solution_icon($solution_url);
                            }

                            if ($this->is_print_mode()) {
                                // no icons in print mode
                            } else {
                                if (!$example->externalurl && !$example->externaltask && !block_exacomp_get_file_url($example, 'example_solution') && !block_exacomp_get_file_url($example, 'example_task') && $example->description) {
                                    $titleCell->text .= $this->pix_icon("i/preview", $example->description);
                                }

                                if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                    $titleCell->text .= $this->schedule_icon($example->id, $USER->id, $courseid);

                                    $titleCell->text .= $this->submission_icon($courseid, $example->id, $USER->id);

                                    // 			                        $titleCell->text .= $this->competence_association_icon($example->id, $courseid, false);

                                } else if ($isEditingTeacher && $role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                    $studentid = block_exacomp_get_studentid();

                                    //auch für alle schüler auf wochenplan legen
                                    if (!$this->is_edit_mode()) {
                                        if ($visible_example) { //prevent errors
                                            $titleCell->text .= $this->schedule_icon($example->id, ($studentid) ? $studentid : BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $courseid);

                                            $titleCell->text .= html_writer::link("#",
                                                html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'), 'title' => block_exacomp_get_string('pre_planning_storage'))),
                                                array('class' => 'add-to-preplanning', 'exa-type' => 'add-example-to-schedule', 'exampleid' => $example->id, 'studentid' => 0, 'courseid' => $courseid));
                                        }
                                    }
                                    // 			                        $titleCell->text .= $this->competence_association_icon($example->id, $courseid, $editmode);

                                }else if ($role == BLOCK_EXACOMP_ROLE_TEACHER){
                                    // 			                        $titleCell->text .= $this->competence_association_icon($example->id, $courseid, $editmode);
                                }
                            }
                            $titleCell->text .= '</span>';

                        }
                        $exampleRow->cells[] = $titleCell;

                        $nivCell = new html_table_cell();

                        $nivText = [];
                        // 	        foreach ($example->taxonomies as $tax) {
                        // 	            $nivText[] = $tax->title;
                        // 	        }
                        $nivCell->text = join(' ', $nivText);
                        $exampleRow->cells[] = $nivCell;

                        $visible_student_example = $visible_example;
                        foreach ($students as $student) {
                            $columnGroup = floor($studentsCount++ / BLOCK_EXACOMP_STUDENTS_PER_COLUMN);

                            // 			        if (!$one_student && $descriptor_parent_visible[$student->id] == false) {
                            // 			            $visible_student_example = false;
                            // 			        } elseif (!$one_student && !$editmode) {
                            $visible_student_example = block_exacomp_is_example_visible($courseid, $example, $student->id);
                            // 			        }

                            //check reviewerid for teacher
                            if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
                                $reviewerid = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLEEVAL, "teacher_reviewerid", array("studentid" => $student->id, "exampleid" => $example->id, "courseid" => $courseid));
                                if ($reviewerid == $USER->id || $reviewerid == 0) {
                                    $reviewerid = null;
                                }
                            }

                            $student_evaluation_cell = new html_table_cell();
                            $student_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            $teacher_evaluation_cell = new html_table_cell();
                            $teacher_evaluation_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;


                            $niveau_cell = new html_table_cell();
                            $niveau_cell->attributes['class'] = 'colgroup colgroup-'.$columnGroup;

                            //disable cell for crosssubjectfiles
                            if($role == BLOCK_EXACOMP_ROLE_TEACHER && !$isEditingTeacher){
                                $disableCell = true;
                            }else{
                                $disableCell = ($role == BLOCK_EXACOMP_ROLE_STUDENT) ? true : (($visible_student_example) ? false : true);
                            }


                            if ($this->useEvalNiveau && block_exacomp_get_assessment_example_diffLevel() == 1) {
                                $niveau_cell->text = $this->generate_niveau_select(
                                    'niveau_examples',
                                    $example->id,
                                    'examples',
                                    $student,
                                    $disableCell,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER ? $reviewerid : null));
                            } else {
                                $niveau_cell->text = '';
                            }

                            $niveau_cell->attributes['exa-timestamp'] = isset($student->examples->timestamp_teacher[$example->id]) ? $student->examples->timestamp_teacher[$example->id] : 0;

                            $example_params = array('name' => 'dataexamples-'.$example->id.'-'.$student->id, 'type' => 'text',
                                'maxlength' => 3, 'class' => 'percent-rating-text',
                                'value' => isset($student->examples->teacher_additional_grading[$example->id]) ?
                                block_exacomp_format_eval_value($student->examples->teacher_additional_grading[$example->id]) : "",
                                'exa-compid' => $example->id, 'exa-userid' => $student->id, 'exa-type' => BLOCK_EXACOMP_TYPE_EXAMPLE,
                                'reviewerid' => ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null,
                                'reviewername' => $reviewername,
                            );

                            if (!$visible_student_example || $role == BLOCK_EXACOMP_ROLE_STUDENT || !$isEditingTeacher) {
                                $example_params['disabled'] = 'disabled';
                            }

                            //student show evaluation
                            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_EXAMPLE)) {

                                if ($example_scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE) { // None.
                                    $teacher_evaluation_cell->text = '';

                                } else if ($example_scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO) { // Yes/No.
                                    $teacher_evaluation_cell->text = $this->generate_checkbox(
                                    $checkboxname,
                                    $example->id,
                                    'examples',
                                    $student,
                                    "teacher",
                                    1,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                    null,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                } else if ($example_scheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // Input.
                                    $example_params['value'] = isset($student->examples->teacher[$example->id]) ?
                                    block_exacomp_format_eval_value($student->examples->teacher[$example->id]) : ""; // TODO: may be $student->examples->teacher_additional_grading?
                                    $teacher_evaluation_cell->text = '<span class="percent-rating">'.html_writer::empty_tag('input', $example_params).'</span>';
                                } else { // Lists.
                                    $teacher_evaluation_cell->text = $this->generate_select(
                                    $checkboxname,
                                    $example->id,
                                    'examples',
                                    $student,
                                    "teacher",
                                    $example_scheme,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? false : true,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                    ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                                }
                            }

                            if (block_exacomp_get_assessment_example_SelfEval() == 1) {
                                // Only emojis?
                                $student_evaluation_cell->text = $this->generate_select(
                                $checkboxname,
                                $example->id,
                                'examples',
                                $student,
                                "student",
                                $example_scheme,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? true : false,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $profoundness : null,
                                ($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $reviewerid : null);
                            }
                            $student_evaluation_cell->attributes['exa-timestamp'] = isset($student->examples->timestamp_teacher[$example->id]) ? $student->examples->timestamp_teacher[$example->id] : 0;

                            // Different order of options for students and teachers:
                            // Student
                            //student & niveau & showevaluation
                            if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
                                if ($this->useEvalNiveau && $showevaluation && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                if (showevaluation) {
                                    $exampleRow->cells[] = $teacher_evaluation_cell;
                                }
                                $exampleRow->cells[] = $student_evaluation_cell;
                            } else { // Teacher
                                if (showevaluation) {
                                    $exampleRow->cells[] = $student_evaluation_cell;
                                }
                                if ($this->useEvalNiveau && $this->diffLevelExists) {
                                    $exampleRow->cells[] = $niveau_cell;
                                }
                                $teacher_evaluation_cell->text .= $this->submission_icon($courseid, $example->id, $student->id);
                                $teacher_evaluation_cell->text .= $this->resubmission_icon($example->id, $student->id, $courseid);
                                $exampleRow->cells[] = $teacher_evaluation_cell;
                            }

                        }
                        if ($profoundness) {
                            $emptyCell = new html_table_cell();
                            $emptyCell->colspan = 7 - count($exampleRow->cells);
                            $exampleRow->cells[] = $emptyCell;
                        }

                        $rows[] = $exampleRow;
                    }
                }//end crosssubjectfiles and total evaluation

            }

        }

        $table->data = $rows;

        if ($this->is_print_mode()) {
            // set cell print width
            foreach ($rows as $row) {
                foreach ($row->cells as $cell) {
                    if (!empty($cell->print_width)) {
                        $cell->attributes['width'] = $cell->print_width.'%';
                        // testing:
                        // $cell->text = $cell->print_width.' '.$cell->text;
                    }
                }
            }
        }

        $table_html = html_writer::table($table);

        if (!$this->is_print_mode()) {
            if ($rows && !$this->is_edit_mode() && $students) {
                $buttons = html_writer::tag("input", "", array("id" => "btn_submit", "name" => "btn_submit", "type" => "submit", "value" => block_exacomp_get_string("save_selection")));
                $table_html .= html_writer::div($buttons, '', array('id' => 'exabis_save_button'));
            }

            $table_html .= html_writer::end_tag('form');
        }

        return $table_html;
    }



	public function preview_icon($alt = null) {
		if ($alt == null) {
			$alt = block_exacomp_get_string("preview");
		}

		return html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/preview.png'), 'alt' => $alt, 'title' => $alt));
	}

	public function source_info($sourceid) {
		global $DB;
		$info = "";
		if ($sourceid == BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER) {
			$info = block_exacomp_get_string('local');
		} elseif ($sourceid && $source = $DB->get_record(BLOCK_EXACOMP_DB_DATASOURCES, array('id' => $sourceid))) {
			$info = $source->name;
		}
		if (empty($info)) {
			$info = block_exacomp_get_string('unknown_src')." ($sourceid)";
		}

		return $info;
	}

	public function sources() {
		global $courseid;

		$sources = block_exacomp\data::get_all_used_sources();

		if (!$sources) {
			return;
		}

		$ret = '<div>';
		foreach ($sources as $source) {
			$name = ($source->name ? $source->name : $source->source);
			$ret .= $this->box("Importierte Daten von \"$name\" ".html_writer::link(new moodle_url('/blocks/exacomp/source_delete.php', array('courseid' => $courseid, 'action' => 'select', 'source' => $source->id)),
					"löschen"));
		}
		$ret .= '</div>';

		return $ret;
	}

	public function submission_icon($courseid, $exampleid, $studentid = 0) {
	    static $isTeacher;
		if ($this->is_print_mode() || !$this->exaportExists) {
			return '';
		}

		if ($isTeacher === null) {
            $context = context_course::instance($courseid);
            $isTeacher = block_exacomp_is_teacher($context);
        }

		if (!$isTeacher) {
			//if student, check for existing item
			$itemExists = block_exacomp_get_current_item_for_example($studentid, $exampleid);

			return html_writer::link(
				new moodle_url('/blocks/exacomp/example_submission.php', array("courseid" => $courseid, "exampleid" => $exampleid)),
				$this->pix_icon((!$itemExists) ? "i/manual_item" : "i/reload", block_exacomp_get_string('submission')),
				array('exa-type' => 'iframe-popup'));
		} elseif ($studentid) {
			//works only if exaport is installed
			if ($url = block_exacomp_get_viewurl_for_example($studentid, g::$USER->id, $exampleid)) {
				return html_writer::link($url,
					$this->pix_icon("i/folder", block_exacomp_get_string("submission"), null, array('style' => 'margin: 0 0 0 5px;')),
					array("target" => "_blank", 'exa-type' => 'iframe-popup'));
			} else {
				return "";
			}
		}
	}

	public function resubmission_icon($exampleid, $studentid, $courseid) {
		global $CFG, $DB;

		if (isset($CFG->block_exaport_app_alloweditdelete) && $CFG->block_exaport_app_alloweditdelete) {
			return "";
		}

		$exameval = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
		if (!$exameval || $exameval->resubmission) {
			return "";
		} else {
			return html_writer::link(
				"#",
				$this->pix_icon("i/reload", block_exacomp_get_string("allow_resubmission")),
				array('exa-type' => 'allow-resubmission', 'exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
		}
	}

	public function schedule_icon($exampleid, $studentid, $courseid) {
		return html_writer::link(
			"#",
			$this->pix_icon("e/insert_date", block_exacomp_get_string("example_pool")),
			array('class' => 'add-to-schedule', 'exa-type' => 'add-example-to-schedule', 'exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
	}

	public function competence_association_icon($exampleid, $courseid, $editmode) {
		return html_writer::link(
			new moodle_url('/blocks/exacomp/competence_associations.php', array("courseid" => $courseid, "exampleid" => $exampleid, "editmode" => ($editmode) ? 1 : 0)),
			$this->pix_icon("e/insert_edit_link", block_exacomp_get_string('competence_associations')), array('exa-type' => 'iframe-popup'));
	}

	public function topic_3dchart_icon($topicid, $userid, $courseid) {
		return html_writer::link(
			new moodle_url('/blocks/exacomp/3dchart.php', array("courseid" => $courseid, "userid" => $userid, "topicid" => $topicid)),
			$this->pix("compprofpie.png", block_exacomp_get_string('topic_3dchart')), array('exa-type' => 'iframe-popup', 'class' => 'compprofpie'));
	}

	public function example_solution_icon($solution) {
		return html_writer::link($solution, $this->pix_icon("e/fullpage", block_exacomp_get_string('solution')), array("target" => "_blank"));
	}

	public function visibility_icon_descriptor($visible, $descriptorid) {
		if ($visible) {
			$icon = $this->pix_icon("i/hide", block_exacomp_get_string("hide"));
		} else {
			$icon = $this->pix_icon("i/show", block_exacomp_get_string("show"));
		}

		return html_writer::link("", $icon, array('name' => 'hide-descriptor', 'descrid' => $descriptorid, 'id' => 'hide-descriptor', 'state' => ($visible) ? '-' : '+',
			'showurl' => $this->image_url("i/hide"), 'hideurl' => $this->image_url("i/show"),
		));

	}

	public function visibility_icon_topic($visible, $topicid) {
		if ($visible) {
			$icon = $this->pix_icon("i/hide", block_exacomp_get_string("hide"));
		} else {
			$icon = $this->pix_icon("i/show", block_exacomp_get_string("show"));
		}

		return html_writer::link("", $icon, array('class' => 'hide-topic', 'name' => 'hide-topic', 'topicid' => $topicid, 'id' => 'hide-topic', 'state' => ($visible) ? '-' : '+',
			'showurl' => $this->image_url("i/hide"), 'hideurl' => $this->image_url("i/show"),
		));

	}

	public function visibility_icon_example($visible, $exampleid) {
		if ($visible) {
			$icon = $this->pix_icon("i/hide", block_exacomp_get_string("hide"));
		} else {
			$icon = $this->pix_icon("i/show", block_exacomp_get_string("show"));
		}

		return html_writer::link("", $icon, array('name' => 'hide-example', 'exampleid' => $exampleid, 'id' => 'hide-example', 'state' => ($visible) ? '-' : '+',
			'showurl' => $this->image_url("i/hide"), 'hideurl' => $this->image_url("i/show"),
		));

	}

	public function visibility_icon_example_solution($visible, $exampleid) {
		if ($visible) {
			$icon = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/solution_visible.png'), 'alt' => block_exacomp_get_string("hide_solution"), 'title' => block_exacomp_get_string("hide_solution"), 'width' => '16'));
		} else {
			$icon = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/solution_hidden.png'), 'alt' => block_exacomp_get_string("show_solution"), 'title' => block_exacomp_get_string("show_solution"), 'width' => '16'));
		}

		return html_writer::link("", $icon, array('name' => 'hide-solution', 'exampleid' => $exampleid, 'id' => 'hide-solution', 'state' => ($visible) ? '-' : '+',
			'showurl' => new moodle_url('/blocks/exacomp/pix/solution_visible.png'), 'hideurl' => new moodle_url('/blocks/exacomp/pix/solution_hidden.png'),
		));

	}

	public function visibility_icon_example_solution_disabled() {
		return html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/locked.png'), 'alt' => block_exacomp_get_string("hide_solution_disabled"), 'title' => block_exacomp_get_string("hide_solution_disabled"), 'width' => '16'));
	}

	/**
	 *
	 * @param int $item_count Amount of students
     * @param string $scriptname
	 */
	public function students_column_selector($item_count, $scriptname = '') {
        global $COURSE;
		$content = html_writer::tag("b", block_exacomp_get_string('columnselect'));
		$usejs = false;
        $config_enabled = false;
        $lasttagattr = array();
        // differrent settings of column browser for different using
		switch ($scriptname) {
            case 'assign_competencies':
                if ($item_count < BLOCK_EXACOMP_STUDENTS_PER_COLUMN) {
                    return;
                }
                $items_per_column = BLOCK_EXACOMP_STUDENTS_PER_COLUMN;
                $config_enabled = get_config('exacomp', 'disable_js_assign_competencies'); // is this enabled in plugin settings?
                $script = '/blocks/exacomp/assign_competencies.php';
                $all_link_title = block_exacomp_get_string('allstudents');
                // insert all needed params!!!
                $urlparams = array(
                        'courseid' => g::$COURSE->id,
                        'studentid' => block_exacomp_get_studentid(),
                );
                if ($showeval = optional_param('showevaluation', true, PARAM_BOOL)) {
                    $urlparams['showevaluation'] = $showeval;
                }
                //if ($studentid = optional_param('studentid', 0, PARAM_INT)) {
                //    $urlparams['studentid'] = $studentid;
                //}
                if ($editmode = optional_param('editmode', 0, PARAM_BOOL)) {
                    $urlparams['editmode'] = $editmode;
                }
                if ($niveauid = optional_param('niveauid', BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, PARAM_INT)) {
                    $urlparams['niveauid'] = $niveauid;
                }
                if ($subjectid = optional_param('subjectid', 0, PARAM_INT)) {
                    $urlparams['subjectid'] = $subjectid;
                }
                if ($topicid = optional_param('topicid', 0, PARAM_INT)) {
                    $urlparams['topicid'] = $topicid;
                }
                if ($group = optional_param('group', 0, PARAM_INT)) {
                    $urlparams['group'] = $group;
                }
                break;
            case 'edit_activities':
                if ($item_count < BLOCK_EXACOMP_MODULES_PER_COLUMN) {
                    return;
                }
                $items_per_column = BLOCK_EXACOMP_MODULES_PER_COLUMN;
                $config_enabled = get_config('exacomp', 'disable_js_edit_activities'); // is this enabled in plugin settings?
                $script = '/blocks/exacomp/edit_activities.php';
                $all_link_title = block_exacomp_get_string('all_activities');
                // insert all needed params!!!
                $urlparams = array(
                        'courseid' => g::$COURSE->id,
                );
                break;
            case 'cross_subjects':
                // NO break!
                $all_link_title = block_exacomp_get_string('allstudents');
            default:
                $script = ''; // do not used
                $items_per_column = 3; // default, but not used!
                $usejs = true; // use JS anycase
        }
        $currentcolgroup = optional_param('colgroupid', 0, PARAM_INT);
		for ($i = 0; $i < ceil($item_count / $items_per_column); $i++) {
			$content .= " ";
			if (!$usejs && $config_enabled) {
			    $urlparams['colgroupid'] = $i;
                $groupurl = new moodle_url($script, $urlparams);
                $tagattrs = array('class' => ' colgroup-link ');
                if ($currentcolgroup == $i) {
                    $tagattrs['class'] .= ' current-colgroup ';
                }
                if (!isset($lasturl)) {
                    $urlparams['colgroupid'] = -1;
                    $lasturl = new moodle_url($script, $urlparams);
                    $lasttagattr = array('class' => ' colgroup-link ');
                    if ($currentcolgroup == -1) {
                        $lasttagattr['class'] .= ' current-colgroup ';
                    }
                }
            } else {
			    // JS used
                $groupurl = '';
                $tagattrs = array('class' => 'colgroup-button', 'exa-groupid' => $i);
                if (!isset($lasturl)) {
                    $lasturl = '';
                    $lasttagattr = array('class' => 'colgroup-button colgroup-button-all', 'exa-groupid' => -1);
                }
            }
            $content .= html_writer::link($groupurl,
                    ($i * $items_per_column + 1). '-' .min($item_count, ($i + 1) * $items_per_column),
                    $tagattrs);
		}
		$content .= " ".html_writer::link($lasturl, $all_link_title, $lasttagattr);

		if ($scriptname == 'assign_competencies' && block_exacomp_get_settings_by_course($COURSE->id)->nostudents) {
			$content .= " ".html_writer::link('',
					block_exacomp_get_string('nostudents'),
					array('class' => 'colgroup-button colgroup-button-no', 'exa-groupid' => -2));
		}

		return html_writer::div($content, 'spaltenbrowser');
	}

	public function student_evaluation($showevaluation, $isTeacher = true, $niveauid = SHOW_ALL_NIVEAUS, $subjectid = 0, $topicid = -1, $studentid = 0) {
		global $COURSE;

		$link = new moodle_url("/blocks/exacomp/assign_competencies.php", array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1"), 'niveauid' => $niveauid, 'topicid' => $topicid, 'studentid' => $studentid, 'subjectid' => $subjectid));
		$evaluation = $this->box_start();
		$evaluation .= block_exacomp_get_string('overview');
		$evaluation .= html_writer::empty_tag("br");
		if ($isTeacher) {
			$evaluation .= ($showevaluation) ? block_exacomp_get_string('hideevaluation', null, $link->__toString()) : block_exacomp_get_string('showevaluation', null, $link->__toString());
		} else {
			$evaluation .= ($showevaluation) ? block_exacomp_get_string('hideevaluation_student', null, $link->__toString()) : block_exacomp_get_string('showevaluation_student', null, $link->__toString());
		}

		$evaluation .= $this->box_end();

		return $evaluation;
	}

	public function overview_legend($teacher) {
		$legend = "";

		$legend .= html_writer::tag("img", "", array("src" => "pix/list_12x11.png", "alt" => block_exacomp_get_string('legend_activities')));
		$legend .= ' '.block_exacomp_get_string('legend_activities')." - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/folder_fill_12x12.png", "alt" => block_exacomp_get_string('legend_eportfolio')));
		$legend .= ' '.block_exacomp_get_string('legend_eportfolio')." - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/x_11x11.png", "alt" => block_exacomp_get_string('legend_notask')));
		$legend .= ' '.block_exacomp_get_string('legend_notask');

		if ($teacher) {
			$legend .= " - ";
			$legend .= html_writer::tag("img", "", array("src" => "pix/upload_12x12.png", "alt" => block_exacomp_get_string('legend_upload')));
			$legend .= ' '.block_exacomp_get_string('legend_upload');
		}

		return html_writer::div($legend, 'legend');
	}

	/**
	 * Used to generate a checkbox for ticking topics/competencies/examples
	 *
	 * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
	 * @param int $compid
	 * @param String $type comptencies or topics or examples
	 * @param stdClass $student
	 * @param String $evaluation teacher or student
	 * @param int $scheme grading scheme
	 * @param bool $disabled disabled becomes true for the "show evaluation" option
	 *
	 * @return String $checkbox html code for checkbox
	 */
	public function generate_checkbox($name, $compid, $type, $student, $evaluation, $value, $disabled = false, $activityid = null, $reviewerid = null) {
		global $USER;

		$attributes = array();
		if ($disabled) {
			$attributes["disabled"] = "disabled";
		}
		if ($reviewerid && $reviewerid != $USER->id) {
			$attributes["reviewerid"] = $reviewerid;
		}

		$attributes['exa-compid'] = $compid;
		$attributes['exa-userid'] = $student->id;
		$attributes['exa-evaluation'] = $evaluation;

		// $scheme <--> $value? TODO: check it
		/*$content = html_writer::checkbox(
			((isset($activityid)) ?
				$name.'-'.$compid.'-'.$student->id.'-'.$activityid.'-'.$evaluation
				: $name.'-'.$compid.'-'.$student->id.'-'.$evaluation),
			$scheme,
			(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme / 2), null,
			$attributes);*/
        $content = html_writer::checkbox(
			((isset($activityid)) ?
				$name.'-'.$compid.'-'.$student->id.'-'.$activityid.'-'.$evaluation
				: $name.'-'.$compid.'-'.$student->id.'-'.$evaluation),
			$value,
			(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] == 1, null,
			$attributes);

		return $content;
	}

	public function generate_checkbox_profoundness($name, $compid, $type, $student, $evaluation, $scheme) {

		$attributes = [];
		$attributes['exa-compid'] = $compid;
		$attributes['exa-userid'] = $student->id;
		$attributes['exa-evaluation'] = $evaluation;

		return html_writer::checkbox($name.'-'.$compid.'-'.$student->id.'-'.$evaluation,
			$scheme,
			(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] == $scheme, null, $attributes);
	}

	/**
	 * Used to generate a select for topics/competencies/examples values
	 *
	 * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
	 * @param int $compid
	 * @param String $type comptencies or topics or examples
	 * @param stdClass $student
	 * @param String $evaluation teacher or student
     * @param integer $scheme
	 * @param bool $disabled disabled becomes true for the "show evaluation" option
     * @param bool $profoundness
     * @param integer $reviewerid
	 *
	 * @return String $select html code for select
	 */
	public function generate_select($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $profoundness = false, $reviewerid = null) {
	    global $USER, $DB;

		//Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
		//the warning contains the name of the reviewer
        if (!array_key_exists($reviewerid, $this->reviewers)) {
            $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
            $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
            if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
            } else {
                $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                $reviewername = $reviewerTeacherUsername;
            }
            $this->reviewers[$reviewerid] = $reviewername;
        } else {
            $reviewername = $this->reviewers[$reviewerid];
        }


		// TODO: diese $scheme brauchen wir nicht mehr? einfach $options = $scheme_values?
		if (strcmp($evaluation, 'teacher') == 0) {
            $options = \block_exacomp\global_config::get_teacher_eval_items(0, false, $scheme);
        } else {
            $options = \block_exacomp\global_config::get_student_eval_items(true, $type);
		}
		if ($this->is_print_mode()) {
	        // schemes with possiblity '0' as value of selectbox
            $zeroposs = array(BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
            if (in_array($scheme, $zeroposs)) {
                $defval = 0;
            } else {
                $defval = '';
            }
			 // in print mode return the text itself, no select
			$value = (isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : $defval;
            // if no selected - empty: TODO: is it ok?
            return array_key_exists($value, $options) ? $options[$value] : '';
            //return !empty($options[$value]) ? $options[$value] : ''; //$value;
		}

		$attributes = array();
		if ($disabled) {
			$attributes["disabled"] = "disabled";
		}
		if ($reviewerid && $reviewerid != $USER->id) {
			$attributes["reviewerid"] = $reviewerid;
		}

		$attributes['exa-compid'] = $compid;
		$attributes['exa-userid'] = $student->id;
		$attributes['exa-evaluation'] = $evaluation;

		$attributes['reviewername'] = $reviewername;
		$attributes['autocomplete'] = 'off';

		return $this->select(
			$options,
			$name.'-'.$compid.'-'.$student->id.'-'.$evaluation,
			(isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : -1,
			true, $attributes);
	}

	public function generate_niveau_select($name, $compid, $type, $student, $disabled = false, $reviewerid = null) {
		global $USER, $DB;

		//Name of the reviewer. Needed to display a warning if someone else want's to grade something that has already been graded
        //the warning contains the name of the reviewer
        if (!array_key_exists($reviewerid, $this->reviewers)) {
            $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
            $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
            if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                $reviewername = $reviewerTeacherFirstname.' '.$reviewerTeacherLastname;
            } else {
                $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                $reviewername = $reviewerTeacherUsername;
            }
            $this->reviewers[$reviewerid] = $reviewername;
        } else {
            $reviewername = $this->reviewers[$reviewerid];
        }

		if ($this->useEvalNiveau) {
			$options = \block_exacomp\global_config::get_evalniveaus(true);

			$attributes = array();
			if ($disabled) {
				$attributes["disabled"] = "disabled";
			}
			if ($reviewerid && $reviewerid != $USER->id) {
				$attributes["reviewerid"] = $reviewerid;
			}

			$attributes['exa-compid'] = $compid;
			$attributes['exa-userid'] = $student->id;

			$attributes['reviewername'] = $reviewername;

			//$attributes['exa-evaluation'] = $evaluation;

			return $this->select(
				$options,
				$name.'-'.$compid.'-'.$student->id,
				(isset($student->{$type}->niveau[$compid])) ? $student->{$type}->niveau[$compid] : -1,
                true,
                $attributes);
		}

		return '';
	}

	public function edit_config($data, $courseid, $fromimport = 0) {
		$header = html_writer::tag('p', $data->headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rows = array();

		$temp = false;
		foreach ($data->levels as $levelstruct) {
			if ($levelstruct->level->source > 1 && $temp == false) {
				// print table header for first source
				$row = new html_table_row();
				$row->attributes['class'] = 'exahighlight';

				$cell = new html_table_cell();
				//$cell->attributes['class'] = 'category catlevel1';
				$cell->colspan = 2;
				$cell->text = html_writer::tag('h2', block_exacomp_get_string('specificcontent'));

				$row->cells[] = $cell;
				$rows[] = $row;
				$temp = true;
			}

			$row = new html_table_row();
			$row->attributes['class'] = 'exahighlight';

			$cell = new html_table_cell();
			$cell->colspan = 2;
			$cell->text = html_writer::tag('b', $levelstruct->level->title).' ('.$this->source_info($levelstruct->level->source).')';

			$row->cells[] = $cell;
			$rows[] = $row;

			foreach ($levelstruct->schooltypes as $schooltypestruct) {
				$row = new html_table_row();
				$cell = new html_table_cell();
				$cell->text = $schooltypestruct->schooltype->title;
				$row->cells[] = $cell;

				$cell = new html_table_cell();
				if ($schooltypestruct->ticked) {
					$cell->text = html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'data['.$schooltypestruct->schooltype->id.']', 'value' => $schooltypestruct->schooltype->id, 'checked' => 'checked'));
				} else {
					$cell->text = html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'data['.$schooltypestruct->schooltype->id.']', 'value' => $schooltypestruct->schooltype->id));
				}

				$row->cells[] = $cell;
				$rows[] = $row;
			}
		}

		$hiddenaction = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'save'));
		$innerdiv = html_writer::div(html_writer::empty_tag('input',
                array('type' => 'submit',
                        'class' => 'btn btn-default',
                        'value' => block_exacomp_get_string('save_selection'))),
                '',
                array('id' => 'exabis_save_button'));

		$table->data = $rows;


		$div = html_writer::div(html_writer::tag('form', html_writer::table($table).$hiddenaction.$innerdiv, array('action' => 'edit_config.php?courseid='.$courseid.'&fromimport='.$fromimport, 'method' => 'post')), 'exabis_competencies_lis');


		$content = html_writer::tag("div", $header.$div, array("id" => "exabis_competences_block"));

		return $content;
	}

	public function edit_taxonomies($courseid) {
	    $content = '';
        // local moodle taxonomies
        $tablecontent = '';
        $table = new html_table();
        $table->id = 'exacomp-table-taxonomies';
        $table->attributes['class'] = ' ';
        $table->attributes['border'] = 0;
        $rows = array();
        $taxonomies = block_exacomp_get_taxonomies(BLOCK_EXACOMP_DATA_SOURCE_CUSTOM);
        if ($taxonomies && count($taxonomies) > 0) {
            foreach ($taxonomies as $taxkey => $taxonomy) {
                $row = new html_table_row();
                $cell = new html_table_cell();
                //$cell->attributes['width'] = '50%';
                $cell->text = html_writer::empty_tag('input',
                                array('type' => 'text',
                                        'name' => 'data['.$taxonomy->id.']',
                                        'value' => $taxonomy->title,
                                        'class' => 'form-control '));
                $row->cells[] = $cell;
                // up/down buttons
                end($taxonomies);
                if ($taxkey !== key($taxonomies)) {
                    $row->cells[] = '<a href="'.$_SERVER['REQUEST_URI'].'&action=sorting&dir=down&taxid='.intval($taxonomy->id).'"                                     
                                    class="small">'
                            .html_writer::span($this->pix_icon("i/down", block_exacomp_get_string("move_down")))
                            .'</a>';
                } else {
                    $row->cells[] = '';
                }
                reset($taxonomies);
                if ($taxkey !== key($taxonomies)) {
                    $row->cells[] = '<a href="'.$_SERVER['REQUEST_URI'].'&action=sorting&dir=up&taxid='.intval($taxonomy->id).'"                                     
                                    class="small">'
                            .html_writer::span($this->pix_icon("i/up", block_exacomp_get_string("move_up")))
                            .'</a>';
                } else {
                    $row->cells[] = '';
                }
                // Delimeter
                $row->cells[] = '&nbsp;';
                // Delete button
                $row->cells[] = '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete&taxid='.intval($taxonomy->id).'"
                                    onclick="return confirm(\''.block_exacomp_get_string('really_delete').'\');"
                                    class="small">'
                                    .html_writer::span($this->pix_icon("i/delete", block_exacomp_get_string("delete")))
                                    .'</a>';
                $rows[] = $row;
            }
        } else {
            $row = new html_table_row();
            $row->cells[] = block_exacomp_get_string('no_entries_found');
            $rows[] = $row;
        }
        $table->data = $rows;
        $tablecontent = html_writer::table($table);

        $buttons = html_writer::tag('button',
                                    block_exacomp_get_string('add_new_taxonomie'),
                                    ['class' => 'btn btn-default',
                                    'id' => 'exacomp_add_taxonomy_button',
                                    'name' => 'add',
                                    'value' => 'add',
                                    'onclick' => 'return false;']);
        $buttons .= '&nbsp;'.html_writer::tag('button',
                                    block_exacomp_get_string('save'),
                                    ['type' => 'submit',
                                    'class' => 'btn btn-default',
                                    'name' => 'action',
                                    'value' => 'save']);
        $buttons = html_writer::div($buttons);
        $form = html_writer::div(
                    html_writer::tag('form',
                            $tablecontent.$buttons,
                            array('action' => 'edit_taxonomies.php?courseid='.$courseid,
                                    'method' => 'post',
                                    'class' => 'form-vertical')));

        $content .= $form;
	    return $content;
    }

    public function imported_taxonomies($courseid) {
	    global $CFG, $DB;
        $content = '';
        $taxonomies = $DB->get_records_sql("SELECT tax.*, ds.name as sourcename 
		                    FROM {".BLOCK_EXACOMP_DB_TAXONOMIES."} tax
		                    JOIN {".BLOCK_EXACOMP_DB_DATASOURCES."} ds ON ds.id = tax.source
		                    WHERE tax.source != ? 
		                    ORDER BY ds.name, tax.sorting", [BLOCK_EXACOMP_DATA_SOURCE_CUSTOM]);
        if ($taxonomies && count($taxonomies) > 0) {
            $content .= '<br />';
            $title = '<img class="collapsed_icon" src="'.$CFG->wwwroot.'/blocks/exastud/pix/collapsed.png" width="16" height="16" title="'.block_exacomp_get_string('collapse').'" />
                      <img class="expanded_icon" src="'.$CFG->wwwroot.'/blocks/exastud/pix/expanded.png" width="16" height="16" title="'.block_exacomp_get_string('collapse').'" style="display: none;"/>';
            $title .= block_exacomp_get_string('also_taxonomies_from_import');
            $content .= html_writer::tag('h4', $title, array('class' => 'exacomp-collapse-toggler', 'data-target' => 'taxonomies_from_import_list'));
            $currentsourse = '-.-.-';
            $table = new html_table();
            $rows = array();
            foreach ($taxonomies as $taxkey => $taxonomy) {
                if ($currentsourse != $taxonomy->sourcename) {
                    $currentsourse = $taxonomy->sourcename;
                    $row = new html_table_row();
                    $row->cells[] = html_writer::tag('strong', $currentsourse);
                    $rows[] = $row;
                }
                $row = new html_table_row();
                $row->cells[] = $taxonomy->title;
                $rows[] = $row;
            }
            $table->data = $rows;
            $taxonomieslist = html_writer::table($table);
            $content .= html_writer::div($taxonomieslist, '', ['id' => 'taxonomies_from_import_list', 'style' => 'display: none;']);
        }
        return $content;
    }

	/**
	 * NOTICE: after adding new fields here, they also need to be added in course backup/restore and block_exacomp_get_settings_by_course()
	 * @param unknown $settings
	 * @param unknown $courseid
	 * @param unknown $headertext
	 */
	public function edit_course($settings, $courseid, $headertext) {
		global $DB;

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$input_grading = "";

		//if (!block_exacomp_additional_grading() && !$settings->useprofoundness) {
        // if at least one level uses Points - we can set custom points limit for every course
        if (block_exacomp_additional_grading_used_type(BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS) && !$settings->useprofoundness) {
			$input_grading = block_exacomp_get_string('points_limit_forcourse').": &nbsp"
				.html_writer::empty_tag('input', array('type' => 'text', 'size' => 2, 'name' => 'grading', 'value' => $settings->grading))
				.html_writer::empty_tag('br');
		}

		$input_activities = html_writer::checkbox('uses_activities', 1, $settings->uses_activities == 1, '&nbsp;'.block_exacomp_get_string('uses_activities'))
			.html_writer::empty_tag('br');

		$input_descriptors = html_writer::checkbox('show_all_descriptors', 1, $settings->show_all_descriptors == 1, '&nbsp;'.block_exacomp_get_string('show_all_descriptors'), ($settings->uses_activities != 1) ? array("disabled" => "disabled") : array())
			.html_writer::empty_tag('br');

		$input_nostudents = html_writer::checkbox('nostudents', 1, $settings->nostudents == 1, '&nbsp;'.block_exacomp_get_string('usenostudents'))
			.html_writer::empty_tag('br');

        $input_isglobal = '';
		if (block_exacomp_is_dakora_teacher()) {
            $input_isglobal = html_writer::checkbox('isglobal', 1, $settings->isglobal == 1, '&nbsp;'.block_exacomp_get_string('use_isglobal'))
                .html_writer::empty_tag('br');
        }

        $input_hideglobalsubjects = html_writer::checkbox('hideglobalsubjects', 1, @$settings->hideglobalsubjects == 1, '&nbsp;'.block_exacomp_get_string('usehideglobalsubjects'))
            .html_writer::empty_tag('br');


		$input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('save', 'admin'), 'class' => 'btn btn-default'));

		$hiddenaction = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'save_coursesettings'));

		$div = html_writer::div(html_writer::tag('form',
			$input_grading.$input_activities.$input_descriptors./*$input_examples.*/$hiddenaction.$input_nostudents.$input_isglobal.$input_hideglobalsubjects.$input_submit,
			array('action' => 'edit_course.php?courseid='.$courseid, 'method' => 'post')), 'block_excomp_center');

		$content = html_writer::tag("div", $header.$div, array("id" => "exabis_competences_block"));

		return $content;
	}

	public function my_badges($badges, $onlygained = false) {
		$content = "";
		if ($badges->issued) {
			$content .= html_writer::tag('h4', block_exacomp_get_string('my_badges'));
			foreach ($badges->issued as $badge) {
				$context = context_course::instance($badge->courseid);
				$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
				$img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
				$innerdiv = html_writer::div($badge->name);
				$div = html_writer::div($img.$innerdiv, '', array('style' => 'padding:10px;'));
				$content .= $div;
			}

		}
		if (!$onlygained) {
			if ($badges->pending) {
				$content .= html_writer::tag('h2', block_exacomp_get_string('pendingbadges'));
				foreach ($badges->pending as $badge) {
					$context = context_course::instance($badge->courseid);
					$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
					$img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
					$innerdiv = html_writer::div($badge->name, "", array('style' => 'font-weight: bold;'));
					$innerdiv2 = "";
					if ($badge->descriptorStatus) {
						$innerdiv2_content = "";
						foreach ($badge->descriptorStatus as $descriptor) {
							$innerdiv2_content .= html_writer::div($descriptor, "", array('style' => 'padding: 3px 0'));
						}
						$innerdiv2 = html_writer::div($innerdiv2_content, "", array('style' => 'padding: 2px 10px'));
					}
					$div = html_writer::div($img.$innerdiv.$innerdiv2, '', array('style' => 'padding: 10px;'));
					$content .= $div;
				}
			}
		}

		return html_writer::div($content, 'exacomp_profile_badges');
	}

	public function courseselection($schooltypes, $topics_activ, $headertext) {
		global $PAGE, $COURSE;

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');
        $filterform = $this->courseselectionfilter($COURSE->id);

        if (count($schooltypes)) {

            $table = new html_table();
            $table->attributes['class'] = 'exabis_comp_comp rg2';

            $rows = array();
            foreach ($schooltypes as $schooltype) {

                $row = new html_table_row();
                $row->attributes['class'] = 'exabis_comp_teilcomp exahighlight';

                $cell = new html_table_cell();
                $cell->text =
                        html_writer::div(html_writer::tag('b', $schooltype->title).' ('.$this->source_info($schooltype->source).
                                ')');
                $cell->attributes['class'] = 'rg2-arrow';

                $cell->colspan = 3;
                $row->cells[] = $cell;

                $rows[] = $row;

                foreach ($schooltype->subjects as $subject) {
                    $this_rg2_class = 'rg2-level-0';

                    $row = new html_table_row();
                    $row->attributes['class'] = 'exabis_comp_teilcomp '.$this_rg2_class.' exahighlight';

                    $cell = new html_table_cell();
                    $cell->text = html_writer::div(html_writer::span($subject->title, 'rg2-arrow-highlight').
                            // wenn different source than parent
                            ($subject->source != $schooltype->source ? ' ('.$this->source_info($subject->source).')' : ''));
                    $cell->attributes['class'] = 'rg2-arrow rg2-arrow-styled';

                    $cell->colspan = 2;
                    $row->cells[] = $cell;

                    $selectAllCell = new html_table_cell();
                    $selectAllCell->text = html_writer::tag("a", block_exacomp_get_string('selectallornone', 'form'),
                            array("class" => "selectallornone"));
                    $row->cells[] = $selectAllCell;

                    $rows[] = $row;
                    $this->topics_courseselection($rows, 1, $subject->topics, $topics_activ);

                }
            }

            $table->data = $rows;

            $table_html = html_writer::tag("div",
                    html_writer::tag("div", html_writer::table($table), array("class" => "exabis_competencies_lis")),
                    array("id" => "exabis_competences_block"));
            $table_html .= html_writer::div(html_writer::empty_tag('input', array('type' => 'submit',
                    'class' => 'btn btn-default',
                    'value' => block_exacomp_get_string('save_selection'))),
                    '', array('id' => 'exabis_save_button'));
            $table_html .= html_writer::tag("input", "", array("name" => "action", "type" => "hidden", "value" => 'save'));

            $examples_on_schedule = block_exacomp_any_examples_on_schedule($COURSE->id);

            return $header.$filterform.html_writer::tag("form", $table_html,
                    array("method" => "post",
                            "action" => $PAGE->url,
                            "id" => "course-selection",
                            "examplesonschedule" => $examples_on_schedule,
                            'class' => 'checksaving_on_leavepage'));
        } else {
            return $filterform.block_exacomp_get_string('selectcourse_filter_emptyresult');
        }

	}

	public function courseselectionfilter($courseid) {
	    global $PAGE, $SESSION;
	    if (isset($SESSION->courseselection_filter)) {
            $currentFilter = $SESSION->courseselection_filter;
        } else {
            $currentFilter = array();
        }
        $filtercontent = html_writer::tag('h3', block_exacomp_get_string('selectcourse_filter'));
        $filtertable = new html_table();
        $filtertable->attributes['class'] .= ' exacomp-courseselect-filter ';
        $courseid = block_exacomp_is_skillsmanagement() ? $courseid : 0;
        // schooltype
        $row = new html_table_row();
        $schooltypes = block_exacomp_get_schooltypes_by_course($courseid);
        $options = $this->schooltype_options_for_courseselecting($courseid, $schooltypes);
        $currentSchoolType = array_key_exists('schooltype', $currentFilter) ? $currentFilter['schooltype'] : 0;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = html_writer::label(block_exacomp_get_string('selectcourse_filter_schooltype'), 'menufilter_schooltype');
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = html_writer::select($options, 'filter_schooltype', $currentSchoolType);
        $row->cells[] =  $cell;
        $filtertable->data[] = $row;
        // only selected
        $row = new html_table_row();
        $currentOnlySelected = array_key_exists('only_selected', $currentFilter) && $currentFilter['only_selected'] ? true : false;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = html_writer::label(block_exacomp_get_string('selectcourse_filter_onlyselected'), 'only_selected');
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = html_writer::checkbox('only_selected', '1', $currentOnlySelected, '', ['id' => 'only_selected']);
        $row->cells[] = $cell;
        $filtertable->data[] = $row;
        // submit filter
        $row = new html_table_row();
        $currentOnlySelected = false;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = '';
        $row->cells[] = $cell;
        $cell = new html_table_cell();
        $cell->attributes['class'] = ' no-border ';
        $cell->text = html_writer::tag('button',
                block_exacomp_get_string('selectcourse_filter_submit'),
                ['type' => 'submit', 'name' => 'filter_submit', 'value' => 'filter_submit', 'class' => 'btn btn-default']);
        $row->cells[] = $cell;
        $filtertable->data[] = $row;
        $filtercontent .= html_writer::tag('form',
                html_writer::table($filtertable),
                array("method" => "post",
                        "action" => $PAGE->url,
                        "id" => "course-selection-filter"));
        return $filtercontent;
    }

    public function schooltype_options_for_courseselecting($courseid, $schooltypes) {
	    global $CFG;
        $isEduvidual = false;
        // only for eduvidual!
        if (file_exists($CFG->dirroot.'blocks/eduvidual/block_eduvidual.php')) {
            $isEduvidual = true;
            $eduvidalDefaults = block_exacomp_eduvidual_defaultSchooltypes();
            $eduvidalTitles = array();
            foreach ($eduvidalDefaults as $edt) {
                if ($edt['realId']) {
                    $eduvidalTitles[$edt['realId']] = $edt['title'];
                }
            }
        }
        $options = array('' => '');
        foreach ($schooltypes as $st) {
            $title = $st->title;
            if ($isEduvidual) {
                if (array_key_exists($st->id, $eduvidalTitles)) {
                    $title = $eduvidalTitles[$st->id];
                }
            }
            $options[$st->id] = $title;
        }
        return $options;
    }


	public function descriptor_selection_export() {
		global $PAGE;

		$headertext = "Bitte wählen";

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp rg2';
		$rows = array();

		$subjects = \block_exacomp\subject::get_objects();

		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp exahighlight rg2-level-0';

			$cell = new html_table_cell();
			$cell->text = html_writer::div('<input type="checkbox" name="subjects['.$subject->id.']" value="'.$subject->id.'" />'.html_writer::tag('b', $subject->title));
			$cell->attributes['class'] = 'rg2-arrow';
			$row->cells[] = $cell;
			$rows[] = $row;

			foreach ($subject->topics as $topic) {
				$row = new html_table_row();
				$row->attributes['class'] = 'exabis_comp_teilcomp rg2-level-1';

				$cell = new html_table_cell();
				$cell->attributes['class'] = 'rg2-arrow rg2-indent';
				$cell->text = html_writer::div('<input type="checkbox" name="topics['.$topic->id.']" value="'.$topic->id.'" ">'.$topic->numbering.' '.$topic->title, "desctitle");
				$row->cells[] = $cell;

				$rows[] = $row;

				foreach ($topic->descriptors as $descriptor) {
					$row = new html_table_row();
					$row->attributes['class'] = 'rg2-level-2';

					$cell = new html_table_cell();
					$cell->attributes['class'] = 'rg2-arrow rg2-indent';
					$cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title, "desctitle");
					$row->cells[] = $cell;

					$rows[] = $row;

					// child descriptors
					foreach ($descriptor->children as $descriptor) {

						$row = new html_table_row();
						$row->attributes['class'] = 'rg2-level-3';

						$cell = new html_table_cell();
						$cell->attributes['class'] = 'rg2-arrow rg2-indent';
						$cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title, "desctitle");
						$row->cells[] = $cell;

						$rows[] = $row;
					}
				}
			}
		}

		$table->data = $rows;


		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class" => "exabis_competencies_lis")), array("id" => "exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type' => 'submit', 'value' => 'Exportieren')), '', array('id' => 'exabis_save_button'));

		return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action' => 'export_selected')), "id" => "course-selection"));
	}

	public function descriptor_selection_source_delete($source, $subjects) {
		global $PAGE;

		$headertext = "Bitte wählen";

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp rg2 exacomp_source_delete';
		$rows = array();

        $another_source_message = '<span class="exacomp-has-another-source">&nbsp;'.block_exacomp_get_string('delete_level_from_another_source').'</span>';
        $another_childern_source_message = '<span class="exacomp-has-another-source">&nbsp;'.block_exacomp_get_string('delete_level_has_children_from_another_source').'</span>';

		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp exahighlight rg2-level-0';

			$cell = new html_table_cell();
            if ($subject->has_another_source) {
                $notes = $another_childern_source_message;
            } else {
                $notes = '';
            }
			$cell->text = html_writer::div('<input type="checkbox" 
			                                        exa-name="subjects" 
                                                    id="subject_'.$subject->id.'" 
			                                        value="'.$subject->id.'"'.
                                                    (!$subject->can_delete ? ' class="be-sure"' : '').
                                                    //(!$subject->can_delete ? ' disabled="disabled"' : '').
                                            ' />'.
                                            html_writer::tag('b', $subject->title).$notes);
            $cell->attributes['class'] = 'rg2-arrow';
			$row->cells[] = $cell;
			$rows[] = $row;

			foreach ($subject->topics as $topic) {
				$row = new html_table_row();
				$row->attributes['class'] = 'exabis_comp_teilcomp rg2-level-1';

				$cell = new html_table_cell();
                $notes = '';
                if ($topic->another_source) {
                    $notes .= $another_source_message;
                }
                if (!$topic->another_source && $topic->has_another_source) {
                    $notes .= $another_childern_source_message;
                }
				$cell->attributes['class'] = 'rg2-arrow rg2-indent';
				$cell->text = html_writer::div('<input type="checkbox" 
				                                        exa-name="topics" 
				                                        id="topic_'.$topic->id.'"
				                                        value="'.$topic->id.'"'.
                                                        (!$topic->can_delete ? ' class="be-sure"' : '').
                                                        //(!$topic->can_delete ? ' disabled="disabled"' : '').
                                                ' />'.
					                            $topic->numbering.' '.$topic->title.$notes,
                                                "desctitle");
				$row->cells[] = $cell;

				$rows[] = $row;

				foreach ($topic->descriptors as $descriptor) {
					$row = new html_table_row();
					$row->attributes['class'] = 'rg2-level-2';

					$cell = new html_table_cell();
                    $notes = '';
                    if ($descriptor->another_source) {
                        $notes .= $another_source_message;
                    }
                    if (!$descriptor->another_source && $descriptor->has_another_source) {
                        $notes .= $another_childern_source_message;
                    }
					$cell->attributes['class'] = 'rg2-arrow rg2-indent';
					$cell->text = html_writer::div('<input type="checkbox" 
					                                        exa-name="descriptors" 
					                                        id="descriptor_'.$descriptor->id.'"
					                                        value="'.$descriptor->id.'"'.
                                                            (!$descriptor->can_delete ? ' class="be-sure"' : '').
                                                            //(!$descriptor->can_delete ? ' disabled="disabled"' : '').
                                                    ' />'.
						                            $descriptor->numbering.' '.$descriptor->title.$notes,
                                                    "desctitle");
					$row->cells[] = $cell;

					$rows[] = $row;

					// child descriptors
					foreach ($descriptor->children as $child_descriptor) {
						$row = new html_table_row();
						$row->attributes['class'] = 'rg2-level-3';

						$cell = new html_table_cell();
                        $notes = '';
                        if ($child_descriptor->another_source) {
                            $notes .= $another_source_message;
                        }
                        if (!$child_descriptor->another_source && $child_descriptor->has_another_source) {
                            $notes .= $another_childern_source_message;
                        }
						$cell->attributes['class'] = 'rg2-arrow rg2-indent';
						$cell->text = html_writer::div('<input type="checkbox" 
						                                        exa-name="descriptors" 
						                                        id="descriptor_'.$child_descriptor->id.'"
						                                        value="'.$child_descriptor->id.'"'.
                                                                (!$child_descriptor->can_delete ? ' class="be-sure"' : '').
                                                                //(!$child_descriptor->can_delete ? ' disabled="disabled"' : '').
                                                        ' />'.
                                                        $child_descriptor->numbering.' '.$child_descriptor->title.$notes,
                                                        "desctitle");
						$row->cells[] = $cell;

						$rows[] = $row;

						// examples
						foreach ($child_descriptor->examples as $example) {
							$row = new html_table_row();
							$row->attributes['class'] = 'rg2-level-4';

							$cell = new html_table_cell();
                            $notes = '';
                            if ($example->another_source) {
                                $notes .= $another_source_message;
                            }
							$cell->attributes['class'] = 'rg2-arrow rg2-indent';
							$cell->text = html_writer::div('<input type="checkbox" 
							                                        exa-name="examples" 
							                                        id="example_'.$example->id.'"
                                                                    value="'.$example->id.'"'.
                                                                    (!$example->can_delete ? ' class="be-sure"' : '').
                                                                    //(!$example->can_delete ? ' disabled="disabled"' : '').
                                                            ' />'.
								                            $example->numbering.' '.$example->title.$notes,
                                                            "desctitle");
							$row->cells[] = $cell;

							$rows[] = $row;
						}
					}

					// examples
					foreach ($descriptor->examples as $example) {
						$row = new html_table_row();
						$row->attributes['class'] = 'rg2-level-3';

						$cell = new html_table_cell();
                        $notes = '';
                        if ($example->another_source) {
                            $notes .= $another_source_message;
                        }
						$cell->attributes['class'] = 'rg2-arrow rg2-indent';
						$cell->text = html_writer::div('<input type="checkbox" 
						                                        exa-name="examples" 
						                                        id="example_'.$example->id.'"
                                                                value="'.$example->id.'"'.
                                                                (!$example->can_delete ? ' class="be-sure"' : '').
                                                                //(!$example->can_delete ? ' disabled="disabled"' : '').
                                                        ' />'.
							                            $example->numbering.' '.$example->title.$notes,
                                                        "desctitle");
						$row->cells[] = $cell;

						$rows[] = $row;
					}
				}
			}
		}

		$table->data = $rows;

		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class" => "exabis_competencies_lis")), array("id" => "exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input',
                array('type' => 'submit',
                        'value' => 'Löschen',
                        'class' => 'btn btn-danger',
                        'onClick' => 'return confirm(\''.block_exacomp_get_string('really_delete').'\');')),
                '', array('id' => 'exabis_save_button'));

		return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action' => 'delete_selected')), "id" => "exa-selector"));
	}

	public function topics_courseselection(&$rows, $level, $topics, $topics_activ) {
		foreach ($topics as $topic) {
			$this_rg2_class = 'rg2-level-'.$level;

			$topicRow = new html_table_row();
			$topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rg2_class . ' exahighlight';
			$topicRow->attributes['class'] = 'exabis_comp_aufgabe '.$this_rg2_class;
			$outputidCell = new html_table_cell();
			$outputidCell->text = $topic->get_numbering();
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rg2-arrow rg2-indent';
			$outputnameCell->text = html_writer::div($topic->title, "desctitle");
			$topicRow->cells[] = $outputnameCell;

			$cell = new html_table_cell();
            $cell->text = html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'topics['.$topic->id.']', 'value' => (0 - $topic->id)]);
            $cell->text .= html_writer::checkbox('topics['.$topic->id.']', $topic->id, !empty($topics_activ[$topic->id]), '');
			$topicRow->cells[] = $cell;

			$rows[] = $topicRow;

		}
	}

	public function activity_legend($headertext) {
		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		return $header.html_writer::tag('p', block_exacomp_get_string("explaineditactivities_subjects")).html_writer::empty_tag('br');

	}

	public function transfer_activities(){
	    global $PAGE;
	    $import_activities = '<p>'.block_exacomp_get_string("import_activities").'</p>';
	    $import_activities .= html_writer::select( ['' => ''] + get_all_template_courses_key_value(), "template", '', false, array('id' => 'template', 'style' => 'float:left'));
	    $import_activities .= html_writer::div(html_writer::empty_tag('input', array('type' => 'submit', 'value' => 'import', 'class' => 'btn btn-primary')), '', array('id' => 'import'));
	    $ret = html_writer::tag('form', $import_activities, array('id' => 'edit-activities', 'action' => $PAGE->url.'&action=import', 'method' => 'post'));
	    $ret .= '<br/>';
	    return $ret;
	}


	public function activity_content($subjects, $modules) {
		global $PAGE;

        $nojs = (bool)get_config('exacomp', 'disable_js_edit_activities');

		$colspan = (count($modules) + 2);

		$table = new html_table;
		$table->attributes['class'] = 'rg2 exabis_comp_comp';
		if (!$nojs) {
            $table->attributes['style'] = 'display: none'; // hide table first, show with javascript
        }
		$table->attributes['id'] = 'comps';

		$rows = array();

		//print row with list of activities
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->colspan = 2;

		$row->cells[] = $cell;

		//for exporting activities
		$row2 = new html_table_row();
		$cell2 = new html_table_cell();
		$cell2->colspan = 2;
		$row2->cells[] = $cell2;



		$headArr = array();
		$headArr[] = $cell;

		/*		$moduleNameLengths = array_map(function($m) {return strlen($m->name);}, $modules);
		 $maxLength = max($moduleNameLengths);*/


		foreach ($modules as $module) {
		    $cell = new html_table_cell();
		    $cell2 = new html_table_cell();
		    $moduleName = mb_strimwidth($module->name, 0, 33, "..."); // crop if > 30
		    $moduleLink = html_writer::link(block_exacomp_get_activityurl($module), $moduleName, ['title' => $module->name]);
		    if (count($modules) > 5) {
		        $cell->attributes['class'] .= ' verticalCell ';
		        $cell->text = '<div class="verticalText"><div class="verticalTextInner">'.$moduleLink.'</div></div>';
		    } else {
		        $cell->attributes['class'] .= ' ec_tableheadwidth ';
		        $cell->text = $moduleLink;
		    }
		    $cell->attributes['module-type'] = $module->modname;

		    $cell2->text ='<button class="activity-export-btn" value='.$module->id.'>A</button>';
		    $row->cells[] = $cell;
		    $row2->cells[] = $cell2;
		    $headArr[] = $cell;
		}
		$rows[] = $row2;
		$table->head = $headArr;


		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'ec_heading';
			$cell = new html_table_cell();
			$cell->colspan = count($modules) + 2;
			$cell->text = html_writer::tag('b', $subject->title);
			$row->cells[] = $cell;
			$rows[] = $row;
			$this->topics_activities($rows, 0, $subject->topics, $modules);
		}
		$table->data = $rows;

		$table_html = html_writer::div(html_writer::table($table), 'grade-report-grader floating-header');
		$div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class" => "exabis_competencies_lis")), array("id" => "exabis_competences_block"));
		$div .= html_writer::div(html_writer::empty_tag('input', array(
		                                    'type' => 'submit',
                                            'class' => 'btn btn-default',
                                            'value' => block_exacomp_get_string('save_selection'))),
                                    '', array('id' => 'exabis_save_button'));
        $div .= html_writer::tag('input', '', array("type" => "hidden", 'name' => 'action', 'value' => 'save'));

        if (!$nojs) {
            $js = '<script>
				block_exacomp.column_selector("table.exabis_comp_comp", {
					title_colspan: 2
				});
			</script>';
        } else {
            $js = '';
        }
        $pageurl = $PAGE->url;
		return $js.html_writer::tag('form',
                        $div,
                        array('id' => 'edit-activities',
                                //'action' => $PAGE->url.'&action=save', // adds '&amp' if the $PAGE->url has more than 1 param
                                'action' => $pageurl,
                                'method' => 'post',
                                'class' => 'checksaving_on_leavepage'));

	}

	public function topics_activities(&$rows, $level, $topics, $modules) {
		foreach ($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic, true);

			$this_rg2_class = 'rg2-level-'.$level;

			$topicRow = new html_table_row();
			$topicRow->attributes['class'] = 'exabis_comp_teilcomp '.$this_rg2_class.' exahighlight';

			$topicRow->cells[] = block_exacomp_get_topic_numbering($topic);

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rg2-arrow rg2-indent';
			$outputnameCell->text = html_writer::div($outputid.$outputname, "desctitle");
			$topicRow->cells[] = $outputnameCell;

			foreach ($modules as $module) {
				$moduleCell = new html_table_cell();
				$moduleCell->attributes['module-type='] = $module->modname;
				if (block_exacomp_is_topicgrading_enabled()) {
                    $moduleCell->text = html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'topicdata['.$module->id.']['.$topic->id.']', 'value' => 0]);
					$moduleCell->text .= html_writer::checkbox('topicdata['.$module->id.']['.$topic->id.']', "1", (in_array($topic->id, $module->topics)) ? true : false, '', array('class' => 'topiccheckbox'));
				}
				$topicRow->cells[] = $moduleCell;
			}

			$rows[] = $topicRow;

			if (!empty($topic->descriptors)) {
				$this->descriptors_activities($rows, $level + 1, $topic->descriptors, $modules);
			}
		}
	}

	public function descriptors_activities(&$rows, $level, $descriptors, $modules) {

		foreach ($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor, false, false);

			$this_rg2_class = 'rg2-level-'.$level;

			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe '.$this_rg2_class;

			$descriptorRow->cells[] = block_exacomp_get_descriptor_numbering($descriptor);

			$titleCell = new html_table_cell();
			$titleCell->attributes['class'] = 'rg2-arrow rg2-indent';
			$titleCell->text = html_writer::div($outputname);

			$descriptorRow->cells[] = $titleCell;

			foreach ($modules as $module) {
				$moduleCell = new html_table_cell();
                $moduleCell->text = html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'data['.$module->id.']['.$descriptor->id.']', 'value' => 0]);
				$moduleCell->text .= html_writer::checkbox('data['.$module->id.']['.$descriptor->id.']', '1', (in_array($descriptor->id, $module->descriptors)) ? true : false);
				$descriptorRow->cells[] = $moduleCell;
			}

			$rows[] = $descriptorRow;
		}
	}

	public function badge($badge, $descriptors, $context) {
		global $COURSE;

		$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
		$content = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
		$content .= html_writer::div($badge->name, '', array('style' => 'font-weight:bold;'));

		if ($badge->is_locked()) {
			$content .= block_exacomp_get_string('statusmessage_'.$badge->status, 'badges');
		} elseif ($badge->status == BADGE_STATUS_ACTIVE) {
			$content_form = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $badge->id))
				.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'lock', 'value' => 1))
				.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()))
				.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'return', 'value' => new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $COURSE->id))))
				.html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('deactivate', 'badges')));

			$form = html_writer::tag('form', $content_form, array('method' => 'post', 'action' => new moodle_url('/badges/action.php')));

			$content .= html_writer::div($form);
		} elseif (!$badge->has_manual_award_criteria()) {
			$link = html_writer::link(new moodle_url('/badges/edit.php', array('id' => $badge->id, 'action' => 'details')), block_exacomp_get_string('to_award_role'));
			$content .= html_writer::div($link);
		} else {
			if (empty($descriptors)) {
				$link = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $COURSE->id, 'badgeid' => $badge->id)), block_exacomp_get_string('to_award'));
				$content .= html_writer::div($link);
			} else {
				$content_form = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $badge->id))
					.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'activate', 'value' => 1))
					.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()))
					.html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'return', 'value' => new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $COURSE->id))))
					.block_exacomp_get_string('ready_to_activate')
					.html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('activate', 'badges')));

				$form = html_writer::tag('form', $content_form, array('method' => 'post', 'action' => new moodle_url('/badges/action.php')));
				$content .= html_writer::div($form, '', array('style' => 'padding-bottom:20px;'));

				$link1 = html_writer::link(new moodle_url('/badges/edit.php', array('id' => $badge->id, 'action' => 'details')), block_exacomp_get_string('conf_badges'));
				$link2 = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $COURSE->id, 'badgeid' => $badge->id)), block_exacomp_get_string('conf_comps'));

				$content .= html_writer::div($link1.' / '.$link2);
			}
		}

		if ($descriptors) {
			$li_desc = '';
			foreach ($descriptors as $descriptor) {
				$li_desc .= html_writer::tag('li', $descriptor->title);
			}
			$content .= html_writer::tag('ul', $li_desc);
		}

		return html_writer::div($content, '', array('style' => 'padding:10px;'));
	}

	public function edit_badges($subjects, $badge) {
		global $COURSE;
		$table = new html_table();
		$table->attributes['id'] = 'comps';
		$table->attributes['class'] = 'rg2 exabis_comp_comp';

		$rows = array();

		//print tree
		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'ec_heading';
			$cell = new html_table_cell();
			//$cell->colspan = 2;
			$cell->text = html_writer::tag('b', $subject->title);
			$row->cells[] = $cell;

			$cell = new html_table_cell();
			$cell->attributes['class'] = 'ec_tableheadwidth';
			$cell->text = html_writer::link(new moodle_url('/badges/edit.php', array('id' => $badge->id, 'action' => 'details')), $badge->name);
			$row->cells[] = $cell;
			$rows[] = $row;

			$this->topics_badges($rows, 0, $subject->topics, $badge);
		}

		$table->data = $rows;

		$table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
		$div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class" => "exabis_competencies_lis")), array("id" => "exabis_competences_block"));
		$div .= html_writer::div(html_writer::empty_tag('input', array('class' => 'btn btn-default', 'type' => 'submit', 'value' => block_exacomp_get_string('save_selection'))), '', array('id' => 'exabis_save_button'));

		return html_writer::div(block_exacomp_get_string('description_edit_badge_comps'))
			.html_writer::empty_tag('br')
			.html_writer::tag('form', $div, array('id' => 'edit-activities', 'action' => new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $COURSE->id, 'badgeid' => $badge->id, 'action' => 'save')), 'method' => 'post'));

	}

	public function topics_badges(&$rows, $level, $topics, $badge) {
		foreach ($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);

			$this_rg2_class = 'rg2-level-'.$level;

			$topicRow = new html_table_row();
			$topicRow->attributes['class'] = 'exabis_comp_teilcomp '.$this_rg2_class.' exahighlight';

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rg2-arrow rg2-indent';
			$outputnameCell->text = html_writer::div($outputname, "desctitle");
			$topicRow->cells[] = $outputnameCell;

			$badgeCell = new html_table_cell();
			$topicRow->cells[] = $badgeCell;

			$rows[] = $topicRow;
			if (!empty($topic->descriptors)) {
				$this->descriptors_badges($rows, $level + 1, $topic->descriptors, $badge);
			}

		}
	}

	public function descriptors_badges(&$rows, $level, $descriptors, $badge) {
		foreach ($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor, false, false);

			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe rg2-level-'.$level;

			$titleCell = new html_table_cell();
			$titleCell->attributes['class'] = 'rg2-arrow rg2-indent';
			$titleCell->text = html_writer::div($outputname);
			$descriptorRow->cells[] = $titleCell;

			$badgeCell = new html_table_cell();
			$badgeCell->text = html_writer::checkbox('descriptors['.$descriptor->id.']', $descriptor->id, ((isset($badge->descriptors[$descriptor->id])) ? true : false));
			$descriptorRow->cells[] = $badgeCell;

			$rows[] = $descriptorRow;
		}
	}

	public function no_topics_warning() {
		global $COURSE;

		return html_writer::link(new moodle_url('/blocks/exacomp/courseselection.php', array('courseid' => $COURSE->id)), block_exacomp_get_string("no_topics_selected"));
	}

	public function no_course_activities_warning() {
		global $COURSE;

		return html_writer::link(new moodle_url('/course/view.php', array('id' => $COURSE->id, 'notifyeditingon' => 1)), block_exacomp_get_string("no_course_activities"));
	}

	public function no_activities_warning($isTeacher = true) {
		global $COURSE;
		if ($isTeacher) {
			return html_writer::link(new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid' => $COURSE->id)), block_exacomp_get_string("no_activities_selected"));
		} else {
			return block_exacomp_get_string("no_activities_selected_student");
		}
	}

	/**
	 * PROFILRENDERER
	 * @param unknown $student
	 */
	function competence_profile_metadata($student) {
        if (!$student || !$student instanceof stdClass) {
            return '';
        }
	    if ($this->is_print_mode()) {
            $userPicWidth = 50;
            $namediv = html_writer::div(html_writer::tag('h2', $student->firstname.' '.$student->lastname), 'pdf-username', ['align' => 'top']);

            $imgdiv = html_writer::div($this->user_picture($student, array("size" => $userPicWidth)), '', ['align' => 'top']);

            (!empty($student->city)) ? $citydiv = html_writer::div($student->city, 'pdf-useraddress') : $citydiv = '';
            $table = new html_table ();
            $table->attributes['class'] = 'pdf-userdata';
            $table->attributes['border'] = 0;
            $rows = array();
            $row = new html_table_row ();
            $cell = new html_table_cell ();
            $cell->text = $imgdiv;
            $cell->attributes['width'] = $userPicWidth + 5;
            $cell->attributes['valign'] = 'top';
            $row->cells[] = $cell;
            $cell = new html_table_cell ();
            $cell->text = $namediv;
            $cell->attributes['valign'] = 'top';
            $row->cells[] = $cell;
            $rows[] = $row;
            $table->data = $rows;
            return html_writer::table($table);
        } else {
            $namediv = html_writer::div(html_writer::tag('b', $student->firstname.' '.$student->lastname)
                    .html_writer::div(block_exacomp_get_string('name'), ''), '');

            $imgdiv = html_writer::div($this->user_picture($student, array("size" => 100)), '');

            (!empty($student->city)) ? $citydiv = html_writer::div($student->city
                    .html_writer::div(block_exacomp_get_string('city'), ''), '') : $citydiv = '';

            return html_writer::div($namediv.$imgdiv.$citydiv, 'competence_profile_metadata clearfix');
        }
	}

	function box_error($message) {
		if (!$message) {
			$message = block_exacomp_get_string('unknownerror');
		} elseif ($message instanceof moodle_exception) {
			$message = $message->getMessage();
		}

		$message = block_exacomp_get_string('error').': '.$message;

		return $this->notification($message);
	}

    function competence_profile_crosssubject($course, $student, $showall = true, $max_scheme = 3, $crosssubj = null) {

    }

    //prints e.g. the statistics in the competence profile... NOT able to handle generic grading schemes yet
	function competence_profile_course($course, $student, $showall = true, $max_scheme = 3) {

	    $competence_tree = block_exacomp_get_competence_tree($course->id, null, null, false, null, true, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), false, false, false, false, false, false);

		$content = '';

		foreach ($competence_tree as $subject) {
			$content .= html_writer::tag("h4", $subject->title, array("class" => "competence_profile_coursetitle"));

			$innersection = html_writer::tag('legend', block_exacomp_get_string('innersection1'), array('class' => 'competence_profile_insectitle'));
			$innersection .= html_writer::tag('div', $this->competence_profile_grid($course->id, $subject, $student->id, $max_scheme), array('class' => 'container', 'id' => 'charts'));
			$content .= html_writer::tag('fieldset', $innersection, array('id' => 'toclose', 'name' => 'toclose', 'class' => ' competence_profile_innersection exa-collapsible exa-collapsible-open'));

			if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_SUBJECT)) { //prints the statistic
				$stat = block_exacomp_get_evaluation_statistic_for_subject($course->id, $subject->id, $student->id);
				$tables = array();
				$tables[] = $this->subject_statistic_table($course->id, $stat['descriptor_evaluations'], block_exacomp_get_string('descriptors'), block_exacomp_get_assessment_comp_diffLevel(),block_exacomp_get_assessment_comp_scheme()); //print competencies
				$tables[] = $this->subject_statistic_table($course->id, $stat['child_evaluations'], block_exacomp_get_string('childcompetencies_compProfile'), block_exacomp_get_assessment_childcomp_diffLevel(),block_exacomp_get_assessment_childcomp_scheme());
				if (block_exacomp_course_has_examples($course->id)) {
				    $tables[] = $this->subject_statistic_table($course->id, $stat['example_evaluations'], block_exacomp_get_string('materials_compProfile'), block_exacomp_get_assessment_example_diffLevel(),block_exacomp_get_assessment_example_scheme());
				}

				$innersection = html_writer::tag('legend', block_exacomp_get_string('innersection2'), array('class' => 'competence_profile_insectitle'));
				if ($this->is_print_mode()) {
				    $tempTable = new html_table();
				    $tempTable->attributes['class'] = 'statistictables';
				    $tempTable->attributes['exa-subjectid'] = $subject->id;
				    $tempTable->attributes['exa-courseid'] =  $course->id;
				    $row = new html_table_row();
				    foreach ($tables as $tableItem) {
				        $cell = new html_table_cell();
				        $cell->attributes['width'] = '33%';
				        $cell->text = $tableItem;
				        $row->cells[] = $cell;
                    }
                    $tempTable->data = array($row);
                    $innersection .= '<br>'.html_writer::table($tempTable);
                } else {
                    $innersection .= html_writer::tag('div', implode(' ', $tables),
                            array('class' => 'statistictables', 'exa-subjectid' => $subject->id, 'exa-courseid' => $course->id));
                }
				$content .= html_writer::tag('fieldset', $innersection, array('class' => ' competence_profile_innersection exa-collapsible'));

			}

			list($student, $subject) = block_exacomp_get_data_for_profile_comparison($course->id, $subject, $student);

			$innersection = html_writer::tag('legend', block_exacomp_get_string('innersection3'), array('class' => 'competence_profile_insectitle'));
			$innersection .= html_writer::tag('div', $this->comparison_table($course->id, $subject, $student), array('class' => 'comparisondiv'));
			$content .= html_writer::tag('fieldset', $innersection, array('class' => ' competence_profile_innersection exa-collapsible'));

            $innersection = '';
            if ($this->is_print_mode()) {
                $height = 300;
                $width = 600;
                $elementSrc = new moodle_url('/blocks/exacomp/pix/dynamic/timeline_competenceprofile.php',
                        [   'height' => $height,
                            'width' => $width,
                            'courseid' => $course->id,
                            'studentid' => $student->id
                        ]);
                $tempTable = new html_table();
                $tempTable->attributes['class'] = 'competence_profile_timelinegraph';
                $row = new html_table_row();
                $cell = new html_table_cell();
                //$cell->attributes['width'] = $width;
                $cell->attributes['align'] = 'left';
                $text = html_writer::div(block_exacomp_trans(['de:Zeitlicher Ablauf des Kompetenzerwerbs', 'en:Chronological sequence of gained outcomes']), 'competence_profile_insectitle');
                $text .= html_writer::img($elementSrc, '', ['width' => $width / 1.6, 'height' => $height / 1.6, 'border' => 0]); // TODO: why '/ 1.6' ?
                $cell->text = $text;
                $row->cells[] = $cell;
                $tempTable->data = array($row);
                $innersection .= html_writer::table($tempTable);
                //$innersection .= html_writer::div(html_writer::img($elementSrc, '',
                //        ['width' => $width, 'height' => $height, 'border' => 0]), 'competence_profile_timelinegraph');
            } else {
                $innersection = html_writer::tag('legend', block_exacomp_trans(['de:Zeitlicher Ablauf des Kompetenzerwerbs', 'en:Chronological sequence of gained outcomes']), array('class' => 'competence_profile_insectitle'));
                $innersection .= html_writer::div($this->timeline_graph($course, $student), "competence_profile_timelinegraph");
            }
			$content .= html_writer::tag('fieldset', $innersection, array('class' => ' competence_profile_innersection exa-collapsible'));
		}

		if (!$content) {
			return '';
		}

        /*if (!$this->is_print_mode()) {
            $content .= "<script> $('div[class=\"container\"]').each(function () {
                        $(this).find('canvas').each(function () {

					$(this).donut();
				});
                    });    </script>";
        }*/
		return html_writer::div($content, "competence_profile_coursedata");
	}

	private function competence_profile_grid($courseid, $subject, $studentid, $max_scheme) {
		global $DB, $CFG;

		$content = '';
        $columnscounter = 0;

		list ($course_subjects, $table_column, $table_header, $table_content) = block_exacomp_get_grid_for_competence_profile($courseid, $studentid, $subject->id);

        $spanning_niveaus = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_NIVEAUS, 'title', 'span=?', array(1));

		// calculate the col span for spanning niveaus
		$spanning_colspan = block_exacomp_calculate_spanning_niveau_colspan($table_header, $spanning_niveaus);

		$table = new html_table ();
		$table->attributes['class'] = 'compprofiletable flexible boxaligncenter generaltable';
		$rows = array();

		// header
		$row = new html_table_row ();
        $row->attributes['class'] = 'pdf-highlight';

		// first subject title cell
		$cell = new html_table_cell ();
		$cell->text = ''; // $table_content->subject_title;
		$row->cells[] = $cell;
        $columnscounter++; // topic title column

		// niveaus
		foreach ($table_header as $element) {
			if ($element->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {

				$cell = new html_table_cell ();
				$cell->text = $element->title;
				$cell->attributes['class'] = 'header';
				$row->cells[] = $cell;
                $columnscounter++;
			}
		}

		if (block_exacomp_is_topicgrading_enabled()) {

			$topic_eval_header = new html_table_cell ();
			$topic_eval_header->text = block_exacomp_get_string('total');
			$topic_eval_header->attributes['class'] = 'header';
			$row->cells[] = $topic_eval_header;
            $columnscounter++;
		}

		$rows[] = $row;

		$row = new html_table_row ();

		foreach ($table_content->content as $topic => $rowcontent) {

			$cell = new html_table_cell ();
			$cell->text = block_exacomp_get_topic_numbering($topic)." ".$table_column[$topic]->title;
			$cell->attributes['class'] = (($rowcontent->visible) ? '' : 'notvisible');
			$row->cells[] = $cell;

			// if the topic has span = 1 - we need to join cells to the one
            $cellSpannedContent = '';
            $isSpanTopic = $rowcontent->span;

			// competences
			foreach ($rowcontent->niveaus as $niveau => $element) {

				//if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
				//	$element->eval = \block_exacomp\global_config::get_additionalinfo_value_mapping($element->eval);
				//}
				$cell = new html_table_cell ();

				if ($element->show) {
                    $height = 50;
                    $width = 50;
                    $imgWidth = $width; $imgHeight = $height;
                    $image_resize = 20; // resize image if need border graph
                    $params = ['height' => $height,
                            'width' => $width,
                            'evalValue' => $element->eval,
                            'evalMax' => block_exacomp_get_assessment_max_value_by_level(BLOCK_EXACOMP_TYPE_DESCRIPTOR),
                            'niveauTitle' =>  block_exacomp_get_assessment_diffLevel(BLOCK_EXACOMP_TYPE_DESCRIPTOR) ? $element->evalniveau : '',
                            'diffLevel' => block_exacomp_get_assessment_diffLevel(BLOCK_EXACOMP_TYPE_DESCRIPTOR) ? 1 : 0,
                            'assessmentType' => block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR)
                    ];
                    // when the image must be resized (border graph is added)
                    if ((block_exacomp_get_assessment_comp_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE
                            && block_exacomp_get_assessment_comp_diffLevel()
                            && (isset($element->eval) && $element->eval > -1 && $element->evalniveau)
                            )
                        || (
                            (block_exacomp_get_assessment_comp_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE && $element->eval > 0
                                || block_exacomp_get_assessment_comp_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS && $element->eval > -1) // zero is possible
                            && block_exacomp_get_assessment_diffLevel(BLOCK_EXACOMP_TYPE_DESCRIPTOR)
                            && $element->evalniveau
                            )
                    ) {
                            $imgWidth = $width + $image_resize;
                            $imgHeight = $height + $image_resize;
                    }
                    if (block_exacomp_get_assessment_comp_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE) {
                        $verboseTitles = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options());
                        // use short: TODO: when we need to use long titles?
                        $verboseTitlesShort = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options_short());
                        if (array_key_exists($element->eval, $verboseTitles)) {
                            $stringValue = preg_replace('/\s+/u', '-', $verboseTitles[$element->eval]);
                            $params['stringValue'] = $stringValue;
                            $params['stringValueShort'] = $verboseTitlesShort[$element->eval];
                        } else {
                            $params['stringValue'] = '';
                            $params['stringValueShort'] = '';
                        }
                    }
                    if ($this->is_print_mode()) {
                        if ($element->visible && $rowcontent->visible) {
                            $elementSrc = new moodle_url('/blocks/exacomp/pix/dynamic/ppie.php', $params);
                            $cell->text = html_writer::img($elementSrc, '',
                                    ['width' => $imgWidth * 0.60, 'height' => $imgHeight * 0.60, 'border' => 0]);
                        } else {
                            $cell->text = '';
                        }
                    } else {
                        $elementSrc = new moodle_url('/blocks/exacomp/pix/dynamic/ppie.php', $params);
                        $cell->text = html_writer::img($elementSrc, '',
                                ['width' => $imgWidth, 'height' => $imgHeight, 'border' => 0]);
                        //$cell->text .= print_r($element, true);
                        /*$cell->text = html_writer::empty_tag('canvas', [
                                "id" => "chart"."-".$subject->id."-".$niveau,
                                "height" => $height,
                                "width" => $width,
                                "data-title" => $dataTitle,
                                "data-value" => $dataValue,
                                "data-valuemax" => $dataValueMax,
                        ]);*/
                    }
				}
                //$cell->text .= '<pre>'.print_r($element, true).'</pre>';

				$cell->attributes['class'] = (($element->visible && $rowcontent->visible) ? '' : 'notvisible');
				$cell->attributes['exa-timestamp'] = $element->timestamp;
                $cell->attributes['align'] = 'center';

				if (in_array($niveau, $spanning_niveaus)) {
					$cell->colspan = $spanning_colspan; // deprecated. Needed for support old data
				}

                if ($isSpanTopic) {
                    $cellSpannedContent .= $cell->text;
                } else {
                    $row->cells[] = $cell;
                }
			}

			if ($cellSpannedContent) {
			    $colspannedCell = new html_table_cell();
			    $colspannedCell->colspan = $spanning_colspan;
			    $colspannedCell->text = $cellSpannedContent;
			    $row->cells[] = $colspannedCell;
            }

			if (block_exacomp_is_topicgrading_enabled()) {

				if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // TODO: TOPIC?
					$rowcontent->topic_eval = \block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval);
				}

				$topic_eval_cell = new html_table_cell ();
                $height = 50;
                $width = 50;
                $imgWidth = $width; $imgHeight = $height;
                $image_resize = 20; // resize image if need border graph
                $params = ['height' => $height,
                        'width' => $width,
                        'evalValue' =>  $rowcontent->topic_eval,
                        'evalMax' => block_exacomp_get_assessment_max_value_by_level(BLOCK_EXACOMP_TYPE_TOPIC),
                        'niveauTitle' =>  block_exacomp_get_assessment_diffLevel(BLOCK_EXACOMP_TYPE_TOPIC) ? $rowcontent->topic_evalniveau : '',
                        'diffLevel' => block_exacomp_get_assessment_diffLevel(BLOCK_EXACOMP_TYPE_TOPIC) ? 1 : 0,
                        'assessmentType' => block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC)
                ];
                // when the image must be resized (border graph is added)
                if ((block_exacomp_get_assessment_topic_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE
                                && block_exacomp_get_assessment_topic_diffLevel()
                                && (isset($rowcontent->topic_eval) && $rowcontent->topic_eval > -1 && $rowcontent->topic_evalniveau)
                        )
                        || (
                                (block_exacomp_get_assessment_topic_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE
                                        || block_exacomp_get_assessment_topic_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS)
                                && block_exacomp_get_assessment_topic_diffLevel()
                                && $rowcontent->topic_eval
                                && $rowcontent->topic_eval > -1
                                && $rowcontent->topic_evalniveau
                        )
                ) {
                    $imgWidth = $width + $image_resize;
                    $imgHeight = $height + $image_resize;
                }
                if (block_exacomp_get_assessment_topic_scheme() == BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE) {
                    $verboseTitles = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options());
                    // use short: TODO: when we need to use long titles?
                    $verboseTitlesShort = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options_short());
                    if (isset($element->eval) && array_key_exists($element->eval, $verboseTitles)) {
                        $stringValue = preg_replace('/\s+/u', '-', $verboseTitles[$element->eval]);
                        $params['stringValue'] = $stringValue;
                        $params['stringValueShort'] = $verboseTitlesShort[$element->eval];
                    } else {
                        $params['stringValue'] = '';
                        $params['stringValueShort'] = '';
                    }
                }
                if ($this->is_print_mode()) {
                    if ($rowcontent->visible) {
                        $elementSrc = new moodle_url('/blocks/exacomp/pix/dynamic/ppie.php', $params);
                        //$elementSrc = $CFG->wwwroot.'/blocks/exacomp/pix/dynamic/ppie.php?height='.$height.'&width='.$width.'&value='.
                        //        $dataValue.'&title='.$dataTitle.'&valueMax='.$dataValueMax;
                        $topic_eval_cell->text = html_writer::img($elementSrc, '',
                                ['width' => $imgWidth * 0.60, 'height' => $imgHeight * 0.60, 'border' => 0]);
                    } else {
                        $topic_eval_cell->text = '';
                    }
                } else {
                    $elementSrc = new moodle_url('/blocks/exacomp/pix/dynamic/ppie.php', $params);
                    $topic_eval_cell->text = html_writer::img($elementSrc, '',
                            ['width' => $imgWidth, 'height' => $imgHeight, 'border' => 0]);
                    /*$topic_eval_cell->text = html_writer::empty_tag('canvas', [
                            "id" => "chart".$topic,
                            "height" => "50",
                            "width" => "50",
                            "data-title" => $rowcontent->topic_evalniveau,

                            "data-value" => $rowcontent->topic_eval,
                            "data-valuemax" => $max_scheme,
                    ]);*/
                }

				$topic_eval_cell->attributes['class'] = (($rowcontent->visible) ? '' : 'notvisible');
				$topic_eval_cell->attributes['exa-timestamp'] = $rowcontent->timestamp;
                $topic_eval_cell->attributes['align'] = 'center';

				$row->cells[] = $topic_eval_cell;
			}

			$rows[] = $row;

			$row = new html_table_row ();
		}

		if (block_exacomp_is_subjectgrading_enabled()) {

			$subject_empty_cell = new html_table_cell ();
			$subject_empty_cell->text = block_exacomp_get_string('total');
			//$subject_empty_cell->colspan = count($table_header);
            $subject_empty_cell->colspan = $columnscounter - 1; // except last column with total value
			$subject_empty_cell->attributes['class'] = 'header';

			$row->cells[] = $subject_empty_cell;
			$subject_eval_cell = new html_table_cell ();
			$subject_eval_cell->text = $table_content->subject_evalniveau.$table_content->subject_eval;
			$subject_eval_cell->attributes['class'] = 'header';
			$subject_eval_cell->attributes['exa-timestamp'] = $table_content->timestamp;
			$row->cells[] = $subject_eval_cell;
            $row->attributes['class'] = 'pdf-highlight';

			$rows[] = $row;
		}

		$table->data = $rows;

		$content .= html_writer::table($table);

		return html_writer::div($content, 'compprofile_grid');
	}

    //TODO: make generic
	function subject_statistic_table($courseid, $stat, $stat_title, $showdifflevel = true, $assessmentScheme ) {
		$content = '';

		$evaluation_niveaus = \block_exacomp\global_config::get_evalniveaus(true);
		$value_titles = \block_exacomp\global_config::get_teacher_eval_items($courseid, false,$assessmentScheme);
        $value_titles = array_filter($value_titles, 'strlen'); // remove empty, but except '0'
        $count_values = count($value_titles);
		$value_titles_long = \block_exacomp\global_config::get_teacher_eval_items($courseid, false);
        $value_titles_long = array_filter($value_titles_long, 'strlen');

		//first table for descriptor evaluation
		$table = new html_table();
		$table->attributes['class'] = ' flexible statistictable';
		$rows = array();

		//header
		$row = new html_table_row();

		//first subject title cell
		$cell = new html_table_cell();
		$cell->text = $stat_title;
		//$cell->colspan = count($value_titles);
        $cell->colspan = $count_values + 1;
		$cell->attributes['align'] = 'center';
		$row->cells[] = $cell;
		$rows[] = $row;

		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'cell-th';
		$row->cells[] = $cell;

		foreach ($value_titles as $key => $value) {
			//ohne beurteilung wird hier nicht angezeigt
			if ($key > -1) {
				$cell = new html_table_cell();
				$cell->text = $value;
				$cell->attributes['class'] = 'cell-th';
				$cell->attributes['title'] = $value_titles_long[$key];
                $cell->attributes['align'] = 'center';
				$row->cells[] = $cell;
			}
		}
		$rows[] = $row;

		// remove first empty title
		//array_shift($value_titles);

        // hide all diffLevels if it is not configured in the block settings
        if (!$showdifflevel) {
            $stat = array_intersect_key( $stat, array_flip( array(-1)) ); // leave only with key = '-1'
        }

		foreach ($stat as $niveau => $data) {
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = ($this->useEvalNiveau) ? @$evaluation_niveaus[$niveau] : '';
			$cell->attributes['class'] = 'cell-th';
            $cell->attributes['align'] = 'center';
			$row->cells[] = $cell;

			foreach ($value_titles as $key => $tmp) {
				$cell = new html_table_cell();
				$cell->text = (int)@$data[$key];
                $cell->attributes['align'] = 'center';
				$row->cells[] = $cell;
			}
			$rows[] = $row;
		}

		$table->data = $rows;

		$content .= html_writer::div(html_writer::table($table), 'stat_table');

		return $content;
	}

	/**
	 * @param $courseid
	 * @param \block_exacomp\subject $subject
	 * @param $student
	 * @return string
	 */
	private function comparison_table($courseid, $subject, $student) {
		$content = '';

		//first table for descriptor evaluation
		$table = new html_table();
		$table->attributes['class'] = ' flexible boxaligncenter comparisontable';
		$table->attributes['width'] = '100%';
		$rows = array();

		//header
		$row = new html_table_row();

		//first subject title cell
		$cell = new html_table_cell();
		$cell->text = $subject->title; //TODO not hardcoded
        $cell->attributes['class'] = 'col-title';
        $cell->attributes['width'] = '45%';
        if ($this->is_print_mode()) {
            $celltempNumb = new html_table_cell();
            $celltempNumb->attributes['class'] = 'col-numbering';
            $row->cells[] = $celltempNumb;
        } else {
            $cell->colspan = 2; //TODO not hardcoded
        }
        //$cell->header = true;
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('teacher_assessment');
        $cell->attributes['class'] = 'col-eval';
        $cell->attributes['align'] = 'center';
        //$cell->header = true;
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = block_exacomp_get_string('student_assessment');
        $cell->attributes['class'] = 'col-eval';
        $cell->attributes['align'] = 'center';
        //$cell->header = true;
		$row->cells[] = $cell;

		$rows[] = $row;

		foreach ($subject->subs as $topic) {
			$teacherEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id);
			$studentEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id);

            $row = new html_table_row();
			$row->attributes['class'] = 'comparison_topic';
			$cell = new html_table_cell();
			$cell->text = $topic->numbering;
			$cell->attributes['class'] = 'col-numbering';
			$row->cells[] = $cell;

			$cell = new html_table_cell();
            $cell->attributes['class'] = 'col-title';
			$cell->text = $topic->title
				.$this->topic_3dchart_icon($topic->id, $student->id, $courseid);
			$row->cells[] = $cell;

			$cell = new html_table_cell();

			if ($teacherEval) {
                $addtext = '';
                $cell->text = $teacherEval->get_evalniveau_title();
				switch (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC)) {
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                        $addtext = block_exacomp_format_eval_value($teacherEval->additionalinfo);
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                        $value = @$teacherEval->value === null ? -1 : @$teacherEval->value;
                        $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, true, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
                        if (isset($teacher_eval_items[$value])) {
                            $addtext = $teacher_eval_items[$value];
                        }
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                        $addtext = block_exacomp_format_eval_value($teacherEval->value);
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                        if ($teacherEval->value > 0) {
                            $addtext = 'X';
                        }
                        break;
                }
                $cell->text .= $addtext;
				$cell->attributes['exa-timestamp'] = $teacherEval->timestamp;
				$cell->attributes['class'] = 'col-eval';
				$cell->attributes['align'] = 'center';
			}

			$row->cells[] = $cell;

			$cell = new html_table_cell();
			$cell->text = $studentEval ? $studentEval->get_value_title() : '';
            $cell->attributes['class'] = 'col-eval';
            $cell->attributes['align'] = 'center';
			$row->cells[] = $cell;
			$rows[] = $row;

			foreach ($topic->descriptors as $descriptor) {
				$teacherEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
				$studentEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

				$row = new html_table_row();
				$row->attributes['class'] = 'comparison_desc';
				$cell = new html_table_cell();
				$cell->text = $descriptor->numbering;
                $cell->attributes['class'] = 'col-numbering';
				$row->cells[] = $cell;

				$cell = new html_table_cell();
				$cell->text = $descriptor->title;
                $cell->attributes['class'] = 'col-title';
				$row->cells[] = $cell;

				$cell = new html_table_cell();

				if ($teacherEval) {
					$cell->text = $teacherEval->get_evalniveau_title();
                    $addtext = '';
                    switch (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR)) {
                        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                            $addtext = block_exacomp_format_eval_value($teacherEval->additionalinfo);
                            break;
                        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                            $value = @$teacherEval->value === null ? -1 : @$teacherEval->value;
                            $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, true, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
                            if (isset($teacher_eval_items[$value])) {
                                $addtext = $teacher_eval_items[$value];
                            }
                            break;
                        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                            $addtext = block_exacomp_format_eval_value($teacherEval->value);
                            break;
                        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                            if ($teacherEval->value > 0) {
                                $addtext = 'X';
                            }
                            break;
                    }
                    $cell->text .= $addtext;
					$cell->attributes['exa-timestamp'] = $teacherEval->timestamp;
                    $cell->attributes['class'] = 'col-eval';
                    $cell->attributes['align'] = 'center';
				}
				$row->cells[] = $cell;

				$cell = new html_table_cell();
				$cell->text = $studentEval ? $studentEval->get_value_title() : '';
                $cell->attributes['class'] = 'col-eval';
                $cell->attributes['align'] = 'center';
				$row->cells[] = $cell;
				$rows[] = $row;

				$displayOrder = [
					'Bearbeitete Lernmaterialien' => function($example) {
						return in_array($example->state,
							[BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED, BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV, BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV]);
					},
					'Lernmaterialien in Arbeit' => function($example) {
						return (BLOCK_EXACOMP_EXAMPLE_STATE_IN_CALENDAR == $example->state);
					},
					'unbearbeitete zugewiesene Lernmaterialien' => function($example) {
						return (BLOCK_EXACOMP_EXAMPLE_STATE_IN_POOL == $example->state);
					},
				];

				foreach ($displayOrder as $title => $filter) {
					$examples = array_filter($descriptor->examples, $filter);

					if (!$examples) {
						continue;
					}

					$row = new html_table_row();
					$row->attributes['class'] = 'comparison_mat comparmat_mathead';
					$cell = new html_table_cell();
                    $cell->attributes['class'] = 'col-numbering';
					$row->cells[] = $cell;
					$cell = new html_table_cell();
					$cell->text = $title;
                    $cell->attributes['class'] = 'col-title';
					$cell->colspan = 3;
					$row->cells[] = $cell;
					$rows[] = $row;

					foreach ($examples as $example) {
						$teacherEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_EXAMPLE, $example->id);
						$studentEval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_EXAMPLE, $example->id);

						$row = new html_table_row();
						$row->attributes['class'] = 'comparison_mat';
						$cell = new html_table_cell();
                        $cell->attributes['class'] = 'col-numbering';
						$cell->text = '';
						$row->cells[] = $cell;

						$cell = new html_table_cell();
						$cell->text = $example->title;
                        $cell->attributes['class'] = 'col-title';
						$row->cells[] = $cell;

						$cell = new html_table_cell();
						if ($teacherEval) {
							$cell->text = $teacherEval->get_evalniveau_title().' '.$teacherEval->get_value_title();
                            $addtext = '';
                            switch (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_EXAMPLE)) {
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                                    $addtext = block_exacomp_format_eval_value($teacherEval->additionalinfo);
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                                    $value = @$teacherEval->value === null ? -1 : @$teacherEval->value;
                                    $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, false, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
                                    if (isset($teacher_eval_items[$value])) {
                                        $addtext =  $teacher_eval_items[$value];
                                    }
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                                    $addtext = block_exacomp_format_eval_value($teacherEval->value);
                                    break;
                                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                                    if ($teacherEval->value > 0) {
                                        $addtext = 'X';
                                    }
                                    break;
                            }
                            $cell->text .= " ".$addtext;
							$cell->attributes['exa-timestamp'] = $teacherEval->timestamp;
                            $cell->attributes['class'] = 'col-eval';
                            $cell->attributes['align'] = 'center';
						}
						$row->cells[] = $cell;

						$cell = new html_table_cell();
						$cell->text = $studentEval ? $studentEval->get_value_title() : '';
                        $cell->attributes['class'] = 'col-eval';
                        $cell->attributes['align'] = 'center';
						$row->cells[] = $cell;
						$rows[] = $row;
					}
				}
			}
		}

		$table->data = $rows;

		$content .= html_writer::div(html_writer::table($table), 'stat_table');

		return $content;
	}

	public function timeline_graph($course, $student, $returnData = false) {
		$timeline_data = block_exacomp_get_gained_competences($course, $student);

		list ($gained_competencies_teacher, $gained_competencies_student, $total_competencies) = $timeline_data;

		$max_timestamp = time();
		$min_timestamp = strtotime('yesterday', time());

		foreach (array_merge($gained_competencies_teacher, $gained_competencies_student) as $competence) {
			if ($competence->timestamp) {
				$min_timestamp = min($min_timestamp, $competence->timestamp);
			}
		}

		$time_diff = $max_timestamp - $min_timestamp;

		if ($time_diff < 28 * 60 * 60 * 24) { // Days
			$brackets = [];

			$today = strtotime('today', $min_timestamp);
			while ($today <= time()) {
				$next_day = strtotime('tomorrow', $today);

				$brackets[] = (object)[
					'timestamp' => $today,
					'timestamp_end' => $next_day,
					'title' => date('d.m.', $today),
				];
				$today = $next_day;
			}
		} else if ($time_diff < 6 * 30 * 60 * 60 * 24) { // weeks
			$brackets = [];

			$monday = strtotime('last monday', strtotime('tomorrow', $min_timestamp));
			while ($monday <= time()) {
				$next_monday = strtotime('next monday', $monday);
				$next_sunday = strtotime('yesterday', $next_monday);

				if (date('m', $monday) == date('m', $next_sunday)) {
					$title = date('d.', $monday)."-".date('d.m.', $next_sunday);
				} else {
					$title = date('d.m.', $monday).".-".date('d.m.', $next_sunday);
				}

				$brackets[] = (object)[
					'timestamp' => $monday,
					'timestamp_end' => $next_monday,
					'title' => $title,
				];
				$monday = $next_monday;
			}
		} else { // months
			$brackets = [];

			$first_day = strtotime('first day of this month', $min_timestamp);
			while ($first_day <= time()) {
				$next_first_day = strtotime('first day of next month', $first_day);

				$brackets[] = (object)[
					'timestamp' => $first_day,
					'timestamp_end' => $next_first_day,
					'title' => date('F', $first_day),
				];
				$first_day = $next_first_day;
			}
		}

		$y_labels = [];
		$y_values_teacher = [];
		$y_values_student = [];
		$y_values_total = [];

		foreach ($brackets as $bracket) {
			$bracket->gained_competencies_teacher = [];
			$bracket->gained_competencies_student = [];

			foreach ($gained_competencies_teacher as $key => $comp) {
				if ($comp->timestamp < $bracket->timestamp_end) {
					$bracket->gained_competencies_teacher[] = $comp;
				}
			}
			foreach ($gained_competencies_student as $key => $comp) {
				if ($comp->timestamp < $bracket->timestamp_end) {
					$bracket->gained_competencies_student[] = $comp;
				}
			}

			$y_labels[] = $bracket->title;
			$y_values_teacher[] = count($bracket->gained_competencies_teacher);
			$y_values_student[] = count($bracket->gained_competencies_student);
			$y_values_total[] = $total_competencies;
		}

		// Return data for graph. Use for another graph generator
		if ($returnData) {
            $result = array(
                    'teacher' => $y_values_teacher,
                    'student' => $y_values_student,
                    'total' => $y_values_total,
                    'labels' => $y_labels
            );
            return $result;
        }

		$canvas_id = "canvas_timeline".str_replace('.', '', microtime(true));
		$content = html_writer::div(html_writer::tag('canvas', '', array("id" => $canvas_id)), 'timeline', array("style" => ""));
		$content .= '
		<script>
		var timelinedata = {
			labels: '.json_encode($y_labels).',
			datasets: [
			{
				label: "'.block_exacomp_get_string("teacher").'",
				fillColor: "rgba(145, 253, 143, 0.2)",
				strokeColor: "#02a600",
				pointColor: "#02a600",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "#028400",
				data: '.json_encode($y_values_teacher).'
			},
			{
				label: "'.block_exacomp_get_string("student").'",
				fillColor: "rgba(149, 206, 255, 0.2)",
				strokeColor: "#0075dd",
				pointColor: "#0075dd",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "#015FB1",
				data: '.json_encode($y_values_student).'
			},
			{
				label: "'.block_exacomp_get_string("timeline_available").'",
				fillColor: "rgba(220,220,220,0.2)",
				strokeColor: "rgba(220,220,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(220,220,220,1)",
				data: '.json_encode($y_values_total).'
			}
		]
		};
			
		var ctx = document.getElementById('.json_encode($canvas_id).').getContext("2d")
		ctx.canvas.height = 300;
		ctx.canvas.width = 600;
		
		new Chart(ctx).Line(timelinedata, {
				responsive: false, // can\'t be responsive, because Graph.js 1.0.2 does not work with hidden divs
			bezierCurve : false
		});
	
		</script>
		';

		return $content;
	}

//	public function profile_settings($courses, $settings) {
//		global $COURSE;
//		$exacomp_div_content = html_writer::tag('h2', block_exacomp_get_string('blocktitle'));
//
//		$content_courses = html_writer::tag('p', block_exacomp_get_string('profile_settings_choose_courses'));
//		foreach ($courses as $course) {
//			$content_courses .= html_writer::checkbox('profile_settings_course[]', $course->id, (isset($settings->exacomp[$course->id])), $course->fullname);
//			$content_courses .= html_writer::empty_tag('br');
//		}
//		$exacomp_div_content .= html_writer::div($content_courses);
//
//		$exacomp_div = html_writer::div($exacomp_div_content);
//
//		$content = $exacomp_div;
//
//		$div = html_writer::div(html_writer::tag('form',
//			$content
//			.html_writer::div(html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('save_selection'))), 'exabis_save_button'),
//			array('action' => 'competence_profile_settings.php?courseid='.$COURSE->id.'&action=save', 'method' => 'post')), 'block_excomp_center');
//
//		return html_writer::tag("div", $div, array("id" => "exabis_competences_block"));
//	}

	public function wrapperdivstart() {
		return html_writer::start_tag('div', array('id' => 'block_exacomp'));
	}

	public function wrapperdivend() {
		return html_writer::end_tag('div');
	}

	public function button_box($print, $inner_content) {
		$content = '';
		if ($print) {
			$content .= html_writer::link('#',
				html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt' => 'print')),
				[
					'title' => block_exacomp_get_string('print'),
					'class' => 'print',
					'onclick' => (strlen($print) > 2 ? $print : 'window.print();').'; return false;',
				]);
		}
		$content .= $inner_content;

		return html_writer::div($content, 'button-box');
	}

	public function cross_subjects_drafts($subjects, $isAdmin = false) {
		global $PAGE;

		$draft_content = html_writer::tag('h4', block_exacomp_get_string('create_new_crosssub'));
		$draft_content .= "<h5>".block_exacomp_get_string('use_available_crosssub')."</h5>";
		$drafts_exist = false;

		$draft_content .= html_writer::start_tag('ul', array("class" => "exa-tree"));

		foreach ($subjects as $subject) {
			if (isset($subject->crosssub_drafts)) {
				$draft_content .= html_writer::start_tag('li');
				$draft_content .= $subject->title;

				$drafts_exist = true;
				$draft_content .= html_writer::start_tag('ul');

				//print_r($subject->crosssub_drafts);
				foreach ($subject->crosssub_drafts as $draft) {
					$text = $draft->description;
					$text = str_replace("\"", "", $text);
					$text = str_replace("\'", "", $text);
					$text = str_replace("\n", " ", $text);
					$text = str_replace("\r", " ", $text);
					$text = str_replace(":", "\:", $text);

					$draft_content .= html_writer::start_tag('li');
					$draft_content .= html_writer::span(html_writer::checkbox('draft['.$draft->id.']', $draft->id, false, $draft->title), '', array('title' => $text));
					$draft_content .= html_writer::end_tag('li');
				}
				$draft_content .= html_writer::end_tag('ul');
				$draft_content .= html_writer::end_tag('li');
			}
		}
		$draft_content .= html_writer::end_tag('ul');
		$submit = "";
		if ($drafts_exist) {
			$submit .= html_writer::empty_tag('input', array('name' => 'btn_submit', 'type' => 'submit', 'value' => block_exacomp_get_string('add_drafts_to_course')));
			if ($isAdmin) {
				$submit .= html_writer::empty_tag('input', array('name' => 'delete_crosssubs', 'type' => 'submit', 'value' => block_exacomp_get_string('delete_drafts')));
			}
		}
		$submit .= html_writer::empty_tag('br');
		$submit .= html_writer::tag("h5", block_exacomp_get_string('new_crosssub'));
		$submit .= html_writer::empty_tag('input', array('name' => 'new_crosssub_overview', 'type' => 'submit', 'value' => block_exacomp_get_string('add_crosssub')));

		$submit = html_writer::div($submit, '');
		$content = html_writer::tag('form', $draft_content.$submit, array('method' => 'post', 'action' => $PAGE->url.'&action=save', 'name' => 'add_drafts_to_course'));

		return $content;
	}

	/**
	 * @param \block_exacomp\cross_subject $cross_subject
	 * @param $students
	 * @param $selectedStudent
	 * @return string
	 * @throws coding_exception
	 */
	public function cross_subject_buttons($cross_subject, $students, $selectedStudent, $nostudents = false) {
		global $PAGE, $COURSE, $USER;

		$left_content = html_writer::start_tag("p");
		$right_content = '';

		if (!$cross_subject && $this->is_edit_mode()) {
			$left_content .= html_writer::tag("input", "", array("type" => "submit", "class" => 'allow-submit', "value" => block_exacomp_get_string("add_crosssub")));
		}
		if ($cross_subject && $this->is_edit_mode()) {
			$left_content .= html_writer::tag("input", "", array("type" => "submit",
                    "class" => 'allow-submit btn btn-default',
                    "value" => block_exacomp_get_string("save_crosssub")));
			$left_content .= html_writer::tag("input", "", array("type" => "button",
                    "value" => block_exacomp_get_string('add_descriptors_to_crosssub'),
                    'class' => 'btn btn-default',
                    'exa-type' => "iframe-popup",
                    'exa-url' => 'cross_subjects.php?courseid='.g::$COURSE->id.'&action=descriptor_selector&crosssubjid='.$cross_subject->id));

		}
		if ($cross_subject && !$this->is_edit_mode() && $cross_subject->has_capability(BLOCK_EXACOMP_CAP_MODIFY) && !$cross_subject->is_draft()) {
			if ($nostudents) {
				$left_content .= html_writer::tag("input", " ", array("type" => "button", "value" => block_exacomp_get_string("share_crosssub"), 'exa-type' => 'iframe-popup', 'exa-url' => 'cross_subjects.php?courseid='.g::$COURSE->id.'&crosssubjid='.$cross_subject->id.'&action=share'));
			}
			$left_content .= html_writer::tag("input", " ", array("type" => "button", "value" => block_exacomp_get_string("save_as_draft"), 'exa-type' => 'link', 'exa-url' => 'cross_subjects.php?courseid='.g::$COURSE->id.'&crosssubjid='.$cross_subject->id.'&action=save_as_draft'));

			//Add the two style buttons
			$page_url = html_entity_decode($PAGE->url);
			// remove existing 'style' parameter from GET
			$page_url = preg_replace('/([?&])style=[^&]+(&|$)/', '', $page_url);
			$left_content .= html_writer::tag('button', html_writer::empty_tag('img', array('src' => new moodle_url('/pix/i/withsubcat.png'),
			    'title' => block_exacomp_get_string('comp_based'))).' '.block_exacomp_get_string('comp_based'), array('type' => 'button', 'id' => 'comp_based', 'name' => 'comp_based', 'class' => 'view_examples_icon',
			        "onclick" => "document.location.href='".$page_url."&style=0';"));

			$left_content .= html_writer::tag('button', html_writer::empty_tag('img', array('src' => new moodle_url('/pix/e/bullet_list.png'),
			    'title' => block_exacomp_get_string('examp_based'))).' '.block_exacomp_get_string('examp_based'), array('type' => 'button', 'id' => 'examp_based', 'name' => 'examp_based', 'class' => 'view_examples_icon',
			        "onclick" => "document.location.href='".$page_url."&style=1';"));
		}
		$left_content .= html_writer::end_tag("p");

		// html_writer::tag("input", "", array("id"=>"delete_crosssub", "name"=>"delete_crosssub", "type"=>"button", "value"=>block_exacomp_get_string("delete_crosssub"), 'message'=>block_exacomp_get_string('confirm_delete')), array('id'=>'exabis_save_button'));
		if (!$this->is_edit_mode() && block_exacomp_is_teacher() && $students) {
			$left_content .= block_exacomp_get_string("choosestudent");
			$left_content .= $this->studentselector($students, $selectedStudent, ($students) ? static::STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN : static::STUDENT_SELECTOR_OPTION_EDITMODE);

			//print date range picker
			$left_content .= block_exacomp_get_string("choosedaterange");
			$left_content .= $this->daterangepicker();

			// print button
            $right_content .= html_writer::link('#',
                    html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt' => 'print')),
                    [
                            'title' => block_exacomp_get_string('print'),
                            'class' => 'print',
                            'onclick' => 'window.open(location.href+\'&print=1\'); return false;',
                    ]);
			$url = new moodle_url('/blocks/exacomp/pre_planning_storage.php', array('courseid' => $COURSE->id, 'creatorid' => $USER->id));
			$right_content .= html_writer::tag('button',
				html_writer::empty_tag('img', ['src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png')]),
				array(
					'id' => 'pre_planning_storage_submit', 'name' => 'pre_planning_storage_submit',
					'title' => block_exacomp_get_string('pre_planning_storage'),
					'type' => 'button', /* browser default setting for html buttons is submit */
					'exa-type' => 'iframe-popup', 'exa-url' => $url->out(false),
				)
			);
		}
		if ($cross_subject && $cross_subject->has_capability(BLOCK_EXACOMP_CAP_MODIFY)) {
			$right_content .= $this->edit_mode_button(block_exacomp\url::create(g::$PAGE->url, ['editmode' => !$this->is_edit_mode()]));
		}

		if (!block_exacomp_is_teacher()) { // add print button for student
            $right_content .= html_writer::link('#',
                    html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt' => 'print')),
                    [
                            'title' => block_exacomp_get_string('print'),
                            'class' => 'print',
                            'onclick' => 'window.open(location.href+\'&print=1\'); return false;',
                    ]);
        }
		$right_content .= html_writer::empty_tag('input', array('type' => 'button',
                                        'value' => block_exacomp_get_string('manage_crosssubs'),
			                            'class' => 'btn btn-default',
			                            "onclick" => "document.location.href='".block_exacomp\url::create('/blocks/exacomp/cross_subjects_overview.php',
                                                            array('courseid' => $COURSE->id))."'"));


		$content = '';
		$content .= $left_content;
		$content .= html_writer::div($right_content, 'edit_buttons_float_right');



		return html_writer::div($content, '', array('id' => 'exabis_save_button'));
	}

	public function crosssub_subject_dropdown($crosssubject) {
		$subjects = block_exacomp_get_subjects(g::$COURSE->id);
		$options = array();
		$options[0] = block_exacomp_get_string('nocrosssubsub');
		foreach ($subjects as $subject) {
			$options[$subject->id] = $subject->title;
		}

		return html_writer::select($options, "subjectid", ($crosssubject) ? $crosssubject->subjectid : 0, false);

	}

	public function overview_metadata_cross_subjects($crosssubject, $edit) {
		global $DB;

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_info';

		$rows = array();

		if ($edit) {
			$cellText = html_writer::empty_tag('input', array('type' => 'text', 'value' => ($crosssubject) ? $crosssubject->title : '', 'name' => 'title'));
		} else {
			$cellText = html_writer::tag('div', ($crosssubject) ? $crosssubject->title : '');
		}

		$rows[] = [html_writer::span(block_exacomp_get_string('crosssubject'), 'exabis_comp_top_name'), $cellText];

		$subject_title = block_exacomp_get_string('nocrosssubsub');
		if ($crosssubject && $crosssubject->subjectid) {
			$subject = $DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, array('id' => $crosssubject->subjectid));
			$subject_title = @$subject->title;
		}

		if ($edit) {
			$cellText = $this->crosssub_subject_dropdown($crosssubject);
		} else {
			$cellText = html_writer::tag('b', $subject_title);
		}

		$rows[] = [html_writer::span(block_exacomp_get_string('subject_singular'), 'exabis_comp_top_name'), $cellText];

        if ($edit) {
            $cellText = html_writer::empty_tag('input', array('type' => 'text', 'value' => ($crosssubject) ? $crosssubject->groupcategory : '', 'name' => 'groupcategory', 'list' => "dlist"));


            //Create datlist for the existing categories
            $groupcategories = block_exacomp_get_crosssubject_groupcategories();
            $datalist  = html_writer::start_tag('datalist', array('id' => 'dlist', 'display' => 'none'));
            foreach($groupcategories as $groupcategory){
                $datalist .= html_writer::empty_tag('option',  array('value'=>$groupcategory->groupcategory));
            }
            $datalist .= html_writer::end_tag('datalist');
            $cellText .= $datalist;
        } else {
            $cellText = html_writer::tag('b', ($crosssubject) ? $crosssubject->groupcategory : '');
        }

        $rows[] = [html_writer::span(block_exacomp_get_string('groupcategory'), 'exabis_comp_top_name'), $cellText];









		if ($edit) {
			$cellText = html_writer::empty_tag('input', array('type' => 'text', 'value' => ($crosssubject) ? $crosssubject->description : '', 'name' => 'description'));
		} else {
			$cellText = html_writer::tag('b', ($crosssubject) ? $crosssubject->description : '');
		}


		$rows[] = [html_writer::span(block_exacomp_get_string('description'), 'exabis_comp_top_name'), $cellText];

		if (block_exacomp_is_teacher() && !$this->is_print_mode()) {
			$rows[] = [html_writer::span(block_exacomp_get_string('tab_help'), 'exabis_comp_top_name'),
				block_exacomp_get_string('help_crosssubject')];
		}

// 		//Files
// 		$titleCell = new html_table_cell();
// 		if ($edit) {
// 	        $exampleuploadCell = new html_table_cell();
//             $exampleuploadCell->text = html_writer::link(
//             new moodle_url('/blocks/exacomp/example_upload.php', array("courseid" => 1, "crosssubjid" => $crosssubject->id)),
//             html_writer::empty_tag('img', array('src' => 'pix/upload_12x12.png', 'alt' => 'upload')),
//             array("target" => "_blank", 'exa-type' => 'iframe-popup'));

//             //$exampleuploadCell->text .= $outputid.block_exacomp_get_descriptor_numbering($descriptor);

// 		} else {
// // 		    $cellText = html_writer::tag('b', ($crosssubject) ? $crosssubject->description : '');
// // 		    $examples = block_exacomp_get_examples_for_crosssubject($crosssubject->id);

// 		}
// 		$rows[] = [html_writer::span(block_exacomp_get_string('files'), 'exabis_comp_top_name'), $exampleuploadCell];



		$table->data = $rows;

		$content = html_writer::table($table);

		return $content;
	}



	public function print_crosssubjects_and_examples($crosssubjects, $isTeacher, $editmode, $show_examples = true){
	    //hardcoded rg2-level-4 rg2 and rg2-level-3 rg2 classes... fix this!
	    $html_tree = "";
	    $html_tree .= html_writer::start_tag("ul", array("class" => "exa-tree ".($editmode ? 'exa-tree-reopen-checked' : 'exa-tree-open-all')));

	    foreach ($crosssubjects as $crosssubject) {
	        echo "i'm in";
// 	        $html_tree .= html_writer::start_tag("li",array('class' => ("rg2-level-4 rg2")));
	        $html_tree .= html_writer::start_tag("li");
            $html_tree .= $crosssubject->title;

            $examples = block_exacomp_get_examples_for_crosssubject($crosssubject->id);
            foreach ($examples as $example) {
                $exampleIcons = " ";
//                 if ($url = $example->get_task_file_url()) {
//                     $exampleIcons = html_writer::link($url, $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')), array(
//                         "target" => "_blank",
//                     )
//                         );
//                 } elseif ($example->externaltask) {
//                     $exampleIcons = html_writer::link($example->externaltask, $this->local_pix_icon("filesearch.png", $example->externaltask), array(

//                         "target" => "_blank",
//                     )
//                         );
//                 }

//                 if ($example->externalurl) {
//                     $exampleIcons .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", $example->externalurl), array(
//                         "target" => "_blank",
//                     )
//                         );
//                 }

//                 $visible_solution = block_exacomp_is_example_solution_visible(g::$COURSE->id, $example, g::$USER->id);
//                 if ($isTeacher || $visible_solution) {
//                     if ($url = $example->get_solution_file_url()) {
//                         $exampleIcons .= $this->example_solution_icon($url);
//                     } elseif ($example->externalsolution) {
//                         $exampleIcons .= html_writer::link($example->externalsolution, $this->pix_icon("e/fullpage", block_exacomp_get_string('solution')), array(
//                             "target" => "_blank",
//                         )
//                             );
//                     }
//                 }

//                 $html_tree .= html_writer::tag("li", $example->title.$exampleIcons);
                $html_tree .= html_writer::tag("li", $example->title.$exampleIcons);
             }
             $html_tree .= html_writer::end_tag("li");
	    }
	    $html_tree .= html_writer::end_tag("ul");

	    return html_writer::div($html_tree, "associated_div", array('id' => "associated_div"));
	}

	public function competence_based_list_tree($tree, $isTeacher, $editmode, $show_examples = true) {

		$html_tree = "";
		$html_tree .= html_writer::start_tag("ul", array("class" => "exa-tree ".($editmode ? 'exa-tree-reopen-checked' : 'exa-tree-open-all')));

		foreach ($tree as $skey => $subject) {
			if ($subject->associated == 1 || ($isTeacher && $editmode == 1)) {
				$html_tree .= html_writer::start_tag("li", array('class' => ($subject->associated == 1) ? "associated" : ""));
				$html_tree .= $subject->title;

				if (!empty($subject->topics)) {
					$html_tree .= html_writer::start_tag("ul");
				}

				foreach ($subject->topics as $tkey => $topic) {

					if ($topic->associated == 1 || ($isTeacher && $editmode == 1)) {
						$html_tree .= html_writer::start_tag("li", array('class' => ($topic->associated == 1) ? "associated" : ""));
						$html_tree .= block_exacomp_get_topic_numbering($topic->id).' '.$topic->title;

						if (!empty($topic->descriptors)) {
							$html_tree .= html_writer::start_tag("ul");

							foreach ($topic->descriptors as $dkey => $descriptor) {
								if ($descriptor->associated == 1 || ($isTeacher && $editmode == 1)) {
									$html_tree .= $this->competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples);
								}
							}

							$html_tree .= html_writer::end_tag("ul");
						}
					}

				}
				if (!empty($subject->topics)) {
					$html_tree .= html_writer::end_tag("ul");
				}

				$html_tree .= html_writer::end_tag("li");
			}
		}
		$html_tree .= html_writer::end_tag("ul");

		return html_writer::div($html_tree, "associated_div", array('id' => "associated_div"));
	}

	private function competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples) {

		$html_tree = html_writer::start_tag("li", array('class' => ($descriptor->associated == 1) ? "associated" : ""));
		$title = block_exacomp_get_descriptor_numbering($descriptor).' '.$descriptor->title;

		if ($isTeacher && $editmode == 1) {
			$html_tree .= html_writer::checkbox("descriptor[]", $descriptor->id, $descriptor->direct_associated, $title);
		} else {
			$html_tree .= $title;
		}


		$ul = false;
		if ($show_examples && $descriptor->direct_associated == 1 || !empty($descriptor->children)) {
			$html_tree .= html_writer::start_tag('ul');
			$ul = true;
		}

		if ($show_examples && $descriptor->direct_associated == 1) {

			foreach ($descriptor->examples as $example) {
				if (!isset($example->associated)) {
					$example->associated = 0;
				}
				if ($example->associated == 1 || ($isTeacher && $editmode == 1)) {
					$exampleIcons = " ";

                    if ($url = $example->get_task_file_url()) {
                        $numberOfFiles = block_exacomp_get_number_of_files($example, 'example_task');
                        for ($i = 0; $i < $numberOfFiles; $i++) {
                            $url = $example->get_task_file_url($i);
                            $exampleIcons .= html_writer::link($url, $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                        }
                    } elseif ($example->externaltask) {
						$exampleIcons = html_writer::link($example->externaltask,
                                block_exacomp_get_example_icon_simple($this, $example, 'externaltask', 'filesearch.png'),
                                array("target" => "_blank")
						);
					}

					if ($example->externalurl) {
						$exampleIcons .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", $example->externalurl), array(
								"target" => "_blank",
							)
						);
					}

					$visible_solution = block_exacomp_is_example_solution_visible(g::$COURSE->id, $example, g::$USER->id);
					if ($isTeacher || $visible_solution) {
						if ($url = $example->get_solution_file_url()) {
							$exampleIcons .= $this->example_solution_icon($url);
						} elseif ($example->externalsolution) {
							$exampleIcons .= html_writer::link($example->externalsolution, $this->pix_icon("e/fullpage", block_exacomp_get_string('solution')), array(
									"target" => "_blank",
								)
							);
						}
					}

					$html_tree .= html_writer::tag("li", $example->title.$exampleIcons, array('class' => ($example->associated == 1) ? "associated" : ""));
				}
			}
		}
		if (!empty($descriptor->children)) {
			foreach ($descriptor->children as $child) {
				if ($child->associated == 1 || ($isTeacher && $editmode == 1)) {
					$html_tree .= $this->competence_for_list_tree($child, $isTeacher, $editmode, $show_examples);
				}
			}
		}

		if ($ul) {
			$html_tree .= html_writer::end_tag('ul');
		}

		$html_tree .= html_writer::end_tag("li");

		return $html_tree;
	}

	public function example_pool($examples = array()) {

		$studentid = block_exacomp_get_studentid();
		if ($studentid > 0) {
			$content = html_writer::tag('h4', block_exacomp_get_string('example_pool'));
		} else {
			$content = html_writer::tag('h4', block_exacomp_get_string('pre_planning_storage'));
		}

		foreach ($examples as $example) {
			$content .= html_writer::div($example->title, 'fc-event', array('exampleid' => $example->exampleid));
		}

		return html_writer::div($content, '', array('id' => 'external-events'));
	}


	public function side_wrap_weekly_schedule() {
		$pool = $this->example_pool();

		$calendar = html_writer::div('', '', array('id' => 'calendar'));
		$trash = $this->example_trash();
		$clear = html_writer::div('', '', array('style' => 'clear:both'));

		return html_writer::div($pool.$calendar.$trash.$clear, '', array('id' => 'wrap'));
	}

	public function example_trash($trash_examples = array(), $persistent_trash = true) {
		$content = html_writer::tag('h4', block_exacomp_get_string('example_trash'));

		foreach ($trash_examples as $example) {
			$content .= html_writer::div($example->title, 'fc-event');
		}

		if ($persistent_trash) {
			$content .= html_writer::empty_tag('input', array('type' => 'button', 'id' => 'empty_trash', 'value' => block_exacomp_get_string('empty_trash')));
		}

		return html_writer::div($content, '', array('id' => 'trash'));
	}

	public function course_dropdown($selectedCourse) {
		global $DB;
		$content = block_exacomp_get_string("choosecourse");
		$options = array();

		$courses = block_exacomp_get_courseids();

		foreach ($courses as $course) {
			if (block_exacomp_course_has_examples($course)) {
				$course_db = $DB->get_record('course', array('id' => $course));
				$options[$course] = $course_db->fullname;
			}
		}

		$url = new block_exacomp\url(g::$PAGE->url, ['pool_course' => null]);
		$content .= html_writer::select($options, "lis_courses", $selectedCourse, false,
			array("onchange" => "document.location.href='".$url->out()."&pool_course='+this.value;"));

		return $content;
	}

	public function view_example_header($courseSettings, $style = 0) {
		global $PAGE, $DB;
		$page_url = html_entity_decode($PAGE->url);
		// remove existing 'style' parameter from GET
		$page_url = preg_replace('/([?&])style=[^&]+(&|$)/', '', $page_url);
		// sort by competencies
		$buttoncontent = html_writer::empty_tag('img',
                            array('src' => new moodle_url('/pix/i/withsubcat.png'), 'title' => block_exacomp_get_string('comp_based')));
        $buttoncontent .= ' '.block_exacomp_get_string('comp_based');
		$content = html_writer::tag('button', $buttoncontent,
                                array('type' => 'button',
                                        'id' => 'comp_based',
                                        'name' => 'comp_based',
                                        'class' => 'view_examples_icon btn btn-default',
                                        "onclick" => "document.location.href='".$page_url."&style=0';"));
        // sort by examples
        $buttoncontent = html_writer::empty_tag('img',
                            array('src' => new moodle_url('/pix/e/bullet_list.png'), 'title' => block_exacomp_get_string('examp_based')));
        $buttoncontent .= ' '.block_exacomp_get_string('examp_based');
		$content .= html_writer::tag('button', $buttoncontent,
                                array('type' => 'button',
                                        'id' => 'examp_based',
                                        'name' => 'examp_based',
                                        'class' => 'view_examples_icon btn btn-default',
			                            "onclick" => "document.location.href='".$page_url."&style=1';"));
		// For interdisciplinary subjects
		$buttoncontent = html_writer::empty_tag('img',
                            array('src' => new moodle_url('/pix/e/find_replace.png'), 'title' => block_exacomp_get_string('cross_based')));
        $buttoncontent .= ' '.block_exacomp_get_string('cross_based');
		$content .= html_writer::tag('button', $buttoncontent,
                                array('type' => 'button',
                                        'id' => 'cross_based',
                                        'name' => 'cross_based',
                                        'class' => 'view_examples_icon btn btn-default',
			                            "onclick" => "document.location.href='".$page_url."&style=2';"));

        if (!block_exacomp_is_skillsmanagement() && $style == 0) { // TODO: only for $style==0 ?
            $hidden = html_writer::empty_tag('input', ['name' => 'action', 'value' => 'save_filtersettings', 'type' => 'hidden']);
            $input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('filter'), 'class' => 'btn btn-default'));

            //$alltax = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES => block_exacomp_get_string('show_all_taxonomies'));
            //$taxonomies = $DB->get_records_menu('block_exacomptaxonomies', null, 'sorting', 'id, title');
            //$taxonomies = $alltax + $taxonomies;
            $taxonomies = $DB->get_records_sql("SELECT tax.*, ds.name as sourcename
		                    FROM {".BLOCK_EXACOMP_DB_TAXONOMIES."} tax
		                    LEFT JOIN {".BLOCK_EXACOMP_DB_DATASOURCES."} ds ON ds.id = tax.source		                     
		                    ORDER BY ds.name, tax.sorting");
            $input_taxonomies = html_writer::empty_tag('br');
            if ($taxonomies && count($taxonomies) > 0) {
                $currentsourse = '-.-.-';
                $firstgroup = false;
                $input_taxonomies .= '<select id="menufilteredtaxonomies" width="200" class="select custom-select menufilteredtaxonomies" multiple="multiple" name = "filteredtaxonomies[]">';
                foreach ($taxonomies as $taxonomy) {
                    if ($currentsourse != $taxonomy->sourcename) {
                        if ($firstgroup) {
                            $input_taxonomies .= '</optgroup>';
                        }
                        $currentsourse = $taxonomy->sourcename;
                        $input_taxonomies .= '<optgroup label="'.$currentsourse.'">';
                    }
                    $attributes = array();
                    $attributes['value'] = $taxonomy->id;
                    if (!is_array($courseSettings->filteredtaxonomies)) {
                        $courseSettings->filteredtaxonomies = array($courseSettings->filteredtaxonomies);
                    }
                    if (in_array($taxonomy->id, $courseSettings->filteredtaxonomies)) {
                        $attributes['selected'] = 'selected';
                    }
                    $input_taxonomies .= html_writer::tag('option',
                                            $taxonomy->title,
                                            $attributes);
                    $firstgroup = true;
                }
                $input_taxonomies .= '</select>';
            }

            /*$input_taxonomies .=html_writer::select($taxonomies,
                            'filteredtaxonomies[]',
                            $courseSettings->filteredtaxonomies,
                            false,
                            array('multiple' => 'multiple'));*/
            $input_taxonomies = html_writer::div(html_writer::tag('form', $hidden.$input_taxonomies.$input_submit,
                    array('action' => 'view_examples.php?courseid='.$courseSettings->courseid.'&style='.$style, 'method' => 'post')), 'block_exacomp_center');
        } else {
            $input_taxonomies = '';
        }
        $content .= $input_taxonomies;

		return html_writer::div($content, '', array('id' => 'view_examples_header'));
	}

	public function example_based_list_tree($examples, $tableid = '') {
		$isTeacher = block_exacomp_is_teacher();

		$content = '<table class="default-table" id="listTree'.$tableid.'">';

		$content .= '<tr><th>'.block_exacomp_get_string('example').'</th><th>'.block_exacomp_get_string('descriptors').'</th></tr>';

		foreach ($examples as $example) {
			$exampleIcons = " ";

            if ($url = $example->get_task_file_url()) {
                $numberOfFiles = block_exacomp_get_number_of_files($example, 'example_task');
                for ($i = 0; $i < $numberOfFiles; $i++) {
                    $url = $example->get_task_file_url($i);
                    $exampleIcons->text .= html_writer::link($url, $this->local_pix_icon("filesearch.png", block_exacomp_get_string('preview')), array("target" => "_blank"));
                }
  			} elseif ($example->externaltask) {

				$exampleIcons = html_writer::link($example->externaltask,
                        block_exacomp_get_example_icon_simple($this, $example, 'externaltask', 'filesearch.png'),
                        array("target" => "_blank"));
			}

			if ($example->externalurl) {

				$exampleIcons .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", $example->externalurl), array(
					"target" => "_blank",
				));
			}

			$visible_solution = block_exacomp_is_example_solution_visible(g::$COURSE->id, $example, g::$USER->id);
			if ($isTeacher || $visible_solution) {
				if ($url = $example->get_solution_file_url()) {
					$exampleIcons .= $this->example_solution_icon($url);
				} elseif ($example->externalsolution) {
					$exampleIcons .= html_writer::link($example->externalsolution, $this->pix_icon("e/fullpage", block_exacomp_get_string('solution')), array(
						"target" => "_blank",
					));
				}
			}

			$content .= '<tr><td>';

			$content .= $example->title.' '.$example->id.' '.$exampleIcons;

			$example_parent_names = block_exacomp_build_example_parent_names(g::$COURSE->id, $example->id);

			$content .= '</td><td>'.join('<br/>', array_map(function($names) {
					return '<span>'.join('</span><span> &#x25B8; ', $names).'</span>';
				}, $example_parent_names));

			$content .= '</td></tr>';
		}

		$content .= '</table>';

		return $content;
	}

    public function cross_based_list_tree($examples, $crosssubjectid = 0) {
	    return $this->example_based_list_tree($examples, $crosssubjectid);
    }

	public function pre_planning_storage_students($students, $examples, $groups) {
		global $COURSE;

		$content = html_writer::start_tag('ul');
		foreach($groups as $group){

		    $content .= html_writer::start_tag('li');
		    $content .= html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'group_examp_mm', 'groupid' => $group->id));
		    $content .= html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $COURSE->id, 'studentid' => 0)),
		        $group->name, array('target' => '_blank', 'title' => block_exacomp_get_string('to_weekly_schedule')));
		    $content .= html_writer::end_tag('li');
		}

		foreach ($students as $student) {
			$student_has_examples = false;
			foreach ($student->pool_examples as $example) {
				if (in_array($example->exampleid, $examples)) {
					$student_has_examples = true;
				}
			}

			$content .= html_writer::start_tag('li', array('class' => ($student_has_examples) ? 'has_examples' : ''));
			$content .= html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'student_examp_mm', 'studentid' => $student->id));
			$content .= html_writer::link(new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $COURSE->id, 'studentid' => $student->id)),
				$student->firstname." ".$student->lastname, array('target' => '_blank', 'title' => block_exacomp_get_string('to_weekly_schedule')));
			$content .= html_writer::end_tag('li');
		}

		$content .= html_writer::end_tag('ul');
		$content .= html_writer::tag('span', html_writer::start_tag('fieldset', array('class' => 'gray')).html_writer::end_tag('fieldset').block_exacomp_trans(['de:Material aus Vorplanungsspeicher erhalten', 'en:Examples from pre-planning storage']), array('class' => 'pre_planning_storage_legend_gray'));
		$content .= html_writer::tag('span', html_writer::start_tag('fieldset', array('class' => 'blue')).html_writer::end_tag('fieldset').block_exacomp_trans(['de:Noch kein Material erhalten', 'en:No examples received']), array('class' => 'pre_planning_storage_legend_blue'));

		return html_writer::div($content, 'external-students', array('id' => 'external-students'));
	}

	public function pre_planning_storage_pool() {
		$content = html_writer::tag('h4', block_exacomp_get_string('pre_planning_storage'));

		$content .= html_writer::tag('ul', '', array('id' => 'sortable'));

		return html_writer::div($content, 'external-events', array('id' => 'external-events'));
	}

	private function popup_result_header() {
		global $PAGE;

		if (!$PAGE->headerprinted) {
			// print header if not printed yet
			$PAGE->set_pagelayout('embedded');

			return $this->header();
		}

		return '';
	}

	public function popup_close() {

		ob_start();
		?>
		<script type="text/javascript">
					block_exacomp.popup_close();
		</script>
		<?php
		return $this->popup_result_header().ob_get_clean();
	}

	public function popup_close_and_reload() {
		ob_start();
		?>
		<script type="text/javascript">
					block_exacomp.popup_close_and_reload();
		</script>
		<?php
		return $this->popup_result_header().ob_get_clean();
	}

	public function popup_close_and_forward($url) {
		ob_start();
		?>
		<script type="text/javascript">
					block_exacomp.popup_close_and_forward(<?php echo json_encode($url); ?>);
		</script>
		<?php
		return $this->popup_result_header().ob_get_clean();
	}

	public function popup_close_and_notify($func) {
		ob_start();
		?>
		<script type="text/javascript">
					block_exacomp.popup_close_and_notify(<?php echo json_encode($func); ?>);
		</script>
		<?php
		return $this->popup_result_header().ob_get_clean();
	}

	public function cross_subjects_overview_student($course_crosssubs) {
		$content = "<h4>".block_exacomp_get_string('existing_crosssub')."</h4>";

		if (empty($course_crosssubs)) {
			$content .= html_writer::div(block_exacomp_get_string('no_crosssubjs'), '');
		}

		foreach ($course_crosssubs as $crosssub) {
			$content .= html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id)), $crosssub->title);
			$content .= html_writer::empty_tag('br');
		}

		return $content;
	}

	public function cross_subjects_overview_teacher($course_crosssubs) {
	    global $CFG;
		$content = '';
		$work_with_students = block_exacomp_get_settings_by_course(g::$COURSE->id)->work_with_students;

		$item_title_cell = new html_table_cell;
		// $item_title_cell->attributes['width'] = '90%';

		$content .= '<table width="100%" cellpadding="10" cellspacing="0"><tr>';
		$content .= '<td width="33%" style="vertical-align: top;">';

		$table = new html_table();
		$tmp = new html_table_cell($this->pix_icon('i/group', '').' '.block_exacomp_trans('de:Freigegebene Kursthemen'));
		$tmp->colspan = 2;
		$table->head = [$tmp];

		foreach ($course_crosssubs as $crosssub) {
			if (!$crosssub->is_shared()) {
				continue;
			}

			$title = clone $item_title_cell;
			$title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id)), $crosssub->title);
			$table->data[] = [
				$title,
				html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'editmode' => 1)), $this->pix_icon("i/edit", block_exacomp_get_string("edit")), array('class' => 'crosssub-icons')).
				(($work_with_students) ? html_writer::link('#', $this->pix_icon("i/enrolusers", block_exacomp_trans("de:Freigabe bearbeiten")), ['exa-type' => 'iframe-popup', 'exa-url' => 'cross_subjects.php?courseid='.g::$COURSE->id.'&crosssubjid='.$crosssub->id.'&action=share']) : '').
				html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'action' => 'save_as_draft')), $this->pix_icon("i/repository", block_exacomp_trans("de:Kopie als Vorlage speichern"))),
			];
		}

		if (!$table->data) {
			$table->data[] = [block_exacomp_get_string('no_crosssubjs')];
		}

		$content .= html_writer::table($table);

		$content .= '</td>';
		$content .= '<td width="33%" style="vertical-align: top;">';

		$table = new html_table();

        $icon = '<img src="'.$CFG->wwwroot.'/blocks/exacomp/pix/book_x_11x11.png'.'" class="icon" alt="" />';
		$tmp = new html_table_cell($icon.' '.block_exacomp_get_string('available_crosssubjects'));
		$tmp->colspan = 2;
		$table->head = [$tmp];

		foreach ($course_crosssubs as $crosssub) {
			if ($crosssub->is_shared()) {
				continue;
			}

			$title = clone $item_title_cell;
			$title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id)), $crosssub->title);
			$table->data[] = [
				$title,
				html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'editmode' => 1)), $this->pix_icon("i/edit", block_exacomp_get_string("edit"))).
				html_writer::link('#', $this->pix_icon("t/delete", block_exacomp_get_string("delete")), array("onclick" => "if( confirm('".block_exacomp_get_string('confirm_delete')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;")).
				(($work_with_students) ? html_writer::link('#', $this->pix_icon("i/enrolusers", block_exacomp_trans("de:Freigabe bearbeiten")), ['exa-type' => 'iframe-popup', 'exa-url' => 'cross_subjects.php?courseid='.g::$COURSE->id.'&crosssubjid='.$crosssub->id.'&action=share']) : '').
				html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'action' => 'save_as_draft')), $this->pix_icon("i/repository", block_exacomp_trans("de:Kopie als Vorlage speichern"))),
			];
		}

		if (!$table->data) {
			$table->data[] = [block_exacomp_get_string('no_crosssubjs')];
		}

		$content .= html_writer::table($table);

		$content .= html_writer::empty_tag('input', array('type' => 'button', 'value' => block_exacomp_get_string('create_new_crosssub'),
			"onclick" => "document.location.href='".block_exacomp\url::create('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => 0))->out(false)."'"));

		$content .= '</td>';
		$content .= '<td width="33%" style="vertical-align: top;">';

		$table = new html_table();
		$table->attributes['class'] = 'generaltable rg2';
		$tmp = new html_table_cell($this->pix_icon('i/repository', '').' '.block_exacomp_get_string('crosssubject_drafts'));
		$tmp->colspan = 2;
		$table->head = [$tmp];

		$subjects = block_exacomp_get_course_cross_subjects_drafts_sorted_by_subjects();

		foreach ($subjects as $subject) {
			$title = new html_table_cell;
			$title->text = '<div>'.$subject->title.'</div>';
			$title->attributes['class'] = 'rg2-indent rg2-arrow ';
			$title->colspan = 2;

			$row = new html_table_row([$title]);
			$row->attributes['class'] = 'rg2-level-0 rg2-highlight open';
			$row->attributes['exa-rg2-id'] = 'subject-'.$subject->id;
			$table->data[] = $row;

            $groupcategories = block_exacomp_get_crosssubject_groupcategories($subject->id);

            //look for the right category to store this crossubjectdraft into
            //if none has been stored there yet, write the category
            $crosssubj = null;
            foreach($groupcategories as $groupcategory){
                $title = new html_table_cell;
                $title->text = '<div>'.$groupcategory->groupcategory.'</div>';
                $title->attributes['class'] = 'rg2-indent rg2-arrow';
                $title->colspan = 2;

                $row = new html_table_row([$title]);
                $row->attributes['class'] = 'rg2-level-1 rg2-highlight open';
                $row->attributes['exa-rg2-id'] = 'subject-'.$subject->id;
                $table->data[] = $row;

                if ($crosssubj == null){
                    $crosssubj = array_shift($subject->cross_subject_drafts);
                }
                while($groupcategory->groupcategory == $crosssubj->groupcategory && $crosssubj->groupcategory != null){
//                    $crosssubj->creatorid = 3;
                    $title = clone $item_title_cell;
                    $title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssubj->id)), $crosssubj->title);
                    $row = new html_table_row([
                        $title,
                        ($crosssubj->has_capability(BLOCK_EXACOMP_CAP_MODIFY) ? html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssubj->id, 'editmode' => 1)), $this->pix_icon("i/edit", block_exacomp_get_string("edit"))) : '').
                        ($crosssubj->has_capability(BLOCK_EXACOMP_CAP_DELETE) ? html_writer::link('#', $this->pix_icon("t/delete", block_exacomp_get_string("delete")), array("onclick" => "if( confirm('".block_exacomp_get_string('confirm_delete')."')) block_exacomp.delete_crosssubj(".$crosssubj->id."); return false;")) : '').
                        html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssubj->id, 'action' => 'use_draft')), $this->pix_icon("e/copy", block_exacomp_trans("de:Vorlage verwenden"))),
                    ]);
                    $row->attributes['class'] = 'rg2-level-2';
                    $table->data[] = $row;
                    $crosssubj = array_shift($subject->cross_subject_drafts);
                }


//                $printeddraftscount = 0;
//                foreach ($subject->cross_subject_drafts as $crosssub) {
//                    if($groupcategory->groupcategory == $crosssub->groupcategory){
//                        echo "einmalRein  ";
//                        $title = clone $item_title_cell;
//                        $title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id)), $crosssub->title);
//                        $row = new html_table_row([
//                            $title,
//                            ($crosssub->has_capability(BLOCK_EXACOMP_CAP_MODIFY) ? html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'editmode' => 1)), $this->pix_icon("i/edit", block_exacomp_get_string("edit"))) : '').
//                            ($crosssub->has_capability(BLOCK_EXACOMP_CAP_DELETE) ? html_writer::link('#', $this->pix_icon("t/delete", block_exacomp_get_string("delete")), array("onclick" => "if( confirm('".block_exacomp_get_string('confirm_delete')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;")) : '').
//                            html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => g::$COURSE->id, 'crosssubjid' => $crosssub->id, 'action' => 'use_draft')), $this->pix_icon("e/copy", block_exacomp_trans("de:Vorlage verwenden"))),
//                        ]);
//                        $row->attributes['class'] = 'rg2-level-2';
//                        $table->data[] = $row;
//                        $printeddraftscount++;
//                    }else{
                        //performance... they should come ordered, so if the first does not fit, it will stop looking and start the next category
                        //also the the array gets reduced by the already printed drafts (no shift for every element because of performance)
//                        $subject->cross_subject_drafts = array_slice($subject->cross_subject_drafts,$printeddraftscount);
//                        break;
//
//                    }
//                }
            }
		}

		if (!$table->data) {
			$table->data[] = [block_exacomp_get_string('no_crosssubjs')];
		}

		$content .= html_writer::table($table);

		$content .= '</td>';
		$content .= '</tr></table>';

		return $content;
	}

	public function create_blocking_event() {
		global $USER;

		$content = html_writer::tag('h4', block_exacomp_get_string('blocking_event'));
		$content .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'blocking_event_title', 'placeholder' => block_exacomp_get_string('blocking_event_title')));
		$content .= html_writer::empty_tag('input', array('type' => 'button', 'id' => 'blocking_event_create', 'value' => block_exacomp_get_string('blocking_event_create'), 'creatorid' => $USER->id));

		return html_writer::div($content, '', array('id' => 'blocking_event'));
	}

	/**
	 * Generates html dropdown for students and groups
	 *
	 * @param array $students
	 * @param object $selected
	 * @param moodle_url $url
	 */
	function studentselector($students, $selected, $option = null, $groups = null) {
		$studentsAssociativeArray = array();
		$spacer = true;

		if ($option == static::STUDENT_SELECTOR_OPTION_EDITMODE) {
			$studentsAssociativeArray[0] = block_exacomp_get_string('no_student_edit');
		} elseif (!$option) {
			$studentsAssociativeArray[0] = block_exacomp_get_string('no_student');
			$spacer = false;
		}

		if ($option == static::STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN) {
			$studentsAssociativeArray[BLOCK_EXACOMP_SHOW_ALL_STUDENTS] = block_exacomp_get_string('allstudents');
		}

		// add a spacer line
		if ($studentsAssociativeArray && $spacer) {
			$studentsAssociativeArray["defaultstudent"] = '--------------------';
		}

		//add local groups:
        if (is_array($groups) && count($groups) > 0) {
            foreach ($groups as $group) {
                $studentsAssociativeArray[-($group->id + 1)] = $group->name;
            }
        }

        if (count($students) > 0) {
            foreach ($students as $student) {
                $studentsAssociativeArray[$student->id] = fullname($student);
            }
        }

		return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student', $selected, true,
			array("disabled" => $this->is_edit_mode() ? "disabled" : ""));
	}

	function daterangepicker() {
		return html_writer::tag('input', '', array('size' => '27', 'id' => 'daterangepicker', 'title' => block_exacomp_get_string("choosedaterange")))
			.' '.html_writer::tag('button', block_exacomp_get_string('cleardaterange'), array('id' => 'clear-range', 'class' => 'btn btn-default'));
	}

	/**
	 * own select function, because moodle select is quite slow and we output 1000 selects for all students
	 * @param array $options
	 * @param $name
	 * @param string $selected
	 * @param array $nothing
	 * @param array|null $attributes
	 * @return string
	 */
	function select(array $options, $name, $selected = '', $nothing = array('' => 'choosedots'), array $attributes = []) {
		if (empty($attributes['disabled'])) {
			unset($attributes['disabled']);
		}

		$optionsOutput = \Super\Cache::staticCallback([__CLASS__, __FUNCTION__], function($options, $selected) {
			$output = '';
			foreach ($options as $value => $label) {
				$attributes = array();
				$value = (string)$value;
				if ($value === $selected) {
					$attributes['selected'] = 'selected';
				}
				$attributes['value'] = $value;
				$output .= html_writer::tag('option', $label, $attributes);
			}

			return $output;
		}, [$options, $selected]);

		$attributesOutput = '';
		foreach ($attributes as $oName => $value) {
			$attributesOutput .= ' '.$oName.'="'.s($value).'"';
		}
		$output = '<select name="'.$name.'" '.$attributesOutput.'>'.$optionsOutput.'</select>';

		return $output;
	}

	/**
	 * in moodle33 pix_url was renamed to image_url
	 */
	public function image_url($imagename, $component = 'moodle') {
		if (method_exists(get_parent_class($this), 'image_url')) {
			return call_user_func_array(['parent', 'image_url'], func_get_args());
		} else {
			return call_user_func_array(['parent', 'pix_url'], func_get_args());
		}
	}

	function group_report_filters($type, $filter, $action, $extra = '', $courseid) {
		ob_start();
		?>

		<form method="post" action="<?php echo $action; ?>">
			<?php
			echo $extra;
			?>
			<div class="filter-group visible">
				<h3 class="filter-group-title"><label><?= block_exacomp_get_string('report_type')?></label></h3>
				<div class="filter-group-body">
					<div>
						<label><input type="radio" name="filter[type]" value="students" <?php if (@$filter['type'] == 'students') echo 'checked="checked"'; ?>/>
							<?php echo block_exacomp_get_string('students_competences'); ?></label>&nbsp;&nbsp;&nbsp;
						<?php
            			$studentsAssociativeArray = array();
            			$students=block_exacomp_get_students_by_course($courseid);
            			$studentsAssociativeArray[0] = block_exacomp_get_string('all_students');
            			//add local groups:
            			$groups = groups_get_all_groups($courseid);
            			foreach ($groups as $group){
            			    $studentsAssociativeArray[-($group->id+1)] = $group->name;
            			}
            			foreach ($students as $student) {
            			    $studentsAssociativeArray[$student->id] = fullname($student);
            			}
            			echo $this->select($studentsAssociativeArray,'filter[selectedStudentOrGroup]',block_exacomp_get_string('all_students'),true);
            			?>

						<br><label><input type="radio" name="filter[type]" value="student_counts" <?php if (@$filter['type'] == 'student_counts') echo 'checked="checked"'; ?>/>
							<?php echo block_exacomp_get_string('number_of_students'); ?></label>&nbsp;&nbsp;&nbsp;

					</div>
				</div>
			</div>
			<?php
            //Until here: display settings, type of report

			$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_SUBJECT, 'report_subject');
			$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_TOPIC, 'report_competencefield');
			$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT, 'descriptor');
			//$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 'descriptor');
			$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD, 'descriptor_child');
			$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_EXAMPLE, 'report_learniningmaterial');

			$input_type = 'time';
			$input_filter = (array)@$filter[$input_type];

			if ($periods = block_exacomp_get_exastud_periods_current_and_past_periods()) {
				$options = [];
				foreach ($periods as $period) {
					$options[$period->starttime.'-'.$period->endtime] = $period->description;
				}
				$period_select = html_writer::select($options, 'daterangeperiods', '', block_exacomp_get_string('periodselect'), []);
			} else {
				$period_select = '';
			}

			?>
			<div class="filter-group">
				<h3 class="filter-group-title">
					<label><input type="checkbox" name="filter[<?= $input_type ?>][active]" <?php if (@$input_filter['active']) {
							echo 'checked="checked"';
					} ?> class="filter-group-checkbox"/> <?= block_exacomp_get_string('period') ?></label></h3>
				<div class="filter-group-body"><span class="filter-title"></span>
					<?php echo $period_select; ?>
					<span class="range-inputs">
						<input placeholder=<?= block_exacomp_get_string('from') ?> size="3" data-exa-type="datetime" name="filter[<?= $input_type ?>][from]" value="<?= s(@$input_filter['from']) ?>"/> -
						<input placeholder=<?= block_exacomp_get_string('to') ?> size="3" data-exa-type="datetime" name="filter[<?= $input_type ?>][to]" value="<?= s(@$input_filter['to']) ?>"/>
					</span>
					<?php
						echo html_writer::tag('button', block_exacomp_get_string('cleardaterange'), array('id' => 'clear-range', 'type' => 'button', 'class' => 'btn btn-default'))
					?>
				</div>
			</div>
			<?php
			echo html_writer::empty_tag('input', array('type' => 'submit','id' => 'generate', 'value' => block_exacomp_get_string('create_report'), 'name' => 'generate', 'class' => 'btn btn-default'));
            echo '&nbsp;';
            echo html_writer::empty_tag('input', array('type' => 'submit','id' => 'formatPdf', 'value' => block_exacomp_get_string('create_pdf'), 'name' => 'formatPdf', 'class' => 'btn btn-default'));
			//echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => 'print report', 'name' => 'print'));
			 //echo '<input type="submit" value='.block_exacomp_get_string('create_report').'/>'
            ?>
		</form>
		<?php

		return ob_get_clean();
	}


    function group_report_annex_filters($type, $filter, $action, $extra = '', $courseid) {
        ob_start();
        ?>
        <form method="post" action="<?php echo $action; ?>">
            <?php
                echo $extra;
            ?>
            <div class="filter-group visible form-group row">
                <h3 class="filter-group-title"><label><?= block_exacomp_get_string('choose_student');?></label></h3>
                    <div class="filter-group-body">
                        <div>
                            <?php
                            $studentsAssociativeArray = array();
                            $students = block_exacomp_get_students_by_course($courseid);
                            $studentsAssociativeArray[0] = block_exacomp_get_string('all_students');
                            $groups = groups_get_all_groups($courseid);
                            foreach ($groups as $group){
                                $studentsAssociativeArray[-($group->id+1)] = $group->name;
                            }
                            foreach ($students as $student) {
                                $studentsAssociativeArray[$student->id] = fullname($student);
                            }
                            echo $this->select($studentsAssociativeArray, 'filter[selectedStudentOrGroup]', @$filter['selectedStudentOrGroup'], true, array('class' => 'form-control'));
                            ?>
                        </div>
                    </div>
            </div>
            <?php
                // Subject can only be chosen if the grading scheme configured is the same as topic.
                if (block_exacomp_get_assessment_subject_scheme() == block_exacomp_get_assessment_comp_scheme()) {
                    $this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_SUBJECT, 'report_subject');
                }
                // Topic can only be chosen if the grading scheme configured is the same as competence.
                if (block_exacomp_get_assessment_topic_scheme() == block_exacomp_get_assessment_comp_scheme()) {
                    $this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_TOPIC, 'report_competencefield');
                }
                $this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT, 'descriptor');
                //$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 'descriptor');
                // Child-competence can only be chosen if the grading scheme configured is the same as competence.
                if (block_exacomp_get_assessment_comp_scheme() == block_exacomp_get_assessment_childcomp_scheme()) {
                    $this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD, 'descriptor_child');
                }
                //$this->group_reports_print_filter($filter, BLOCK_EXACOMP_TYPE_EXAMPLE, 'report_learniningmaterial');

                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_html'), 'class' => 'btn btn-default'));
                echo '&nbsp;';
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_docx'), 'class' => 'btn btn-default', 'name' => 'formatDocx'));
                echo '&nbsp;';
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_pdf'), 'class' => 'btn btn-default', 'name' => 'formatPdf'));
            ?>
        </form>
        <?php
        return ob_get_clean();
    }

    function group_report_profoundness_filters($type, $filter, $action, $extra = '', $courseid) {
        ob_start();
        ?>
        <form method="post" action="<?php echo $action; ?>">
            <?php
                echo $extra;
            ?>
            <div class="filter-group visible form-group row">
                <h3 class="filter-group-title"><label><?= block_exacomp_get_string('choose_student');?></label></h3>
                    <div class="filter-group-body">
                        <div>
                            <?php
                            $studentsAssociativeArray = array();
                            $students = block_exacomp_get_students_by_course($courseid);
                            $studentsAssociativeArray[0] = block_exacomp_get_string('all_students');
                            $groups = groups_get_all_groups($courseid);
                            foreach ($groups as $group){
                                $studentsAssociativeArray[-($group->id+1)] = $group->name;
                            }
                            foreach ($students as $student) {
                                $studentsAssociativeArray[$student->id] = fullname($student);
                            }
                            echo $this->select($studentsAssociativeArray, 'filter[selectedStudentOrGroup]', @$filter['selectedStudentOrGroup'], true, array('class' => 'form-control'));
                            ?>
                        </div>
                    </div>
            </div>
            <?php
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_html_report'), 'class' => 'btn btn-default'));
                echo '&nbsp;';
                //echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_docx'), 'class' => 'btn btn-default', 'name' => 'formatDocx'));
                //echo '&nbsp;';
                echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('create_html_report'), 'class' => 'btn btn-default', 'name' => 'formatPdf'));
            ?>
        </form>
        <?php
        return ob_get_clean();
    }


	private function group_reports_print_filter($filter, $input_type, $titleid) {
        $gradingScheme = block_exacomp_additional_grading($input_type);
		$teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, false, $gradingScheme);

		$inputs = \block_exacomp\global_config::get_allowed_inputs($input_type);

		if (!$inputs || !in_array(true, $inputs)) {
			return;
		}

		$input_filter = (array)@$filter[$input_type];

		?>
		<div class="filter-group">
			<h3 class="filter-group-title">
				<label>
                    <input type="checkbox" name="filter[<?= $input_type ?>][visible]"
                            <?php
                                if (@$input_filter['visible']) {
                                    echo 'checked="checked"';
					            }
                            ?>
                           class="filter-group-checkbox"/> <?= block_exacomp_get_string($titleid) ?>
                </label>
            </h3>
			<div class="filter-group-body">
				<?php
//                    if(!empty($inputs['active'])){
//                        echo '<div class="filter-input-active">
//                                <span class="filter-title">'.block_exacomp_get_string('active_show').' :</span>';
//                        echo '<label>
//                                  <input type="checkbox" name="filter['.$input_type.'][active]"';
//                                        if (@$input_filter['active']) {
//                                            echo 'checked="checked"';
//                                        }
//                        echo  '</label>';
//                        echo '</div>';
//                    }

                    if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID])) {
					    echo '<div class="filter-input-difficulty-level">
                            <span class="filter-title">'.block_exacomp_get_string('competence_grid_niveau').':</span>';
                        foreach ([0 => block_exacomp_get_string('no_specification')] + \block_exacomp\global_config::get_evalniveaus() as $key => $value) {
						    $checked = in_array($key, (array)@$input_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID]) ? 'checked="checked"' : '';
                            echo '<label>
                                    <input type="checkbox" name="filter['.$input_type.']['.BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID.'][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                  </label>&nbsp;&nbsp;&nbsp;';
                                }
                        echo '</div>';
				    }
                    /*if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO])) {
                        echo '<div class="filter-input-grade">
                                <span class="filter-title">'.block_exacomp_get_string('competence_grid_additionalinfo').':</span>';
                        switch ($gradingScheme) {
                            // Input fields for Grade|Points
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                                echo '<input data-exa-type="float" placeholder='.block_exacomp_get_string('from').' size="3" name="filter['.$input_type.'][additionalinfo_from]" value="'.s(@$input_filter['additionalinfo_from']).'"/> -
                                      <input data-exa-type="float" placeholder='.block_exacomp_get_string('to').' size="3" name="filter['.$input_type.'][additionalinfo_to]" value="'.s(@$input_filter['additionalinfo_to']).'"/>';
                                break;
                            // Checkboxes for Verbose
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                                foreach ([-1 => block_exacomp_get_string('no_specification')] + $teacher_eval_items as $key => $value) {
                                    $checked = in_array($key, (array) @$input_filter[BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO]) ? 'checked="checked"' : '';
                                    echo '<label>
                                                <input type="checkbox" name="filter['.$input_type.'][additionalinfo][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                          </label>&nbsp;&nbsp;&nbsp;';
                                }
                                break;
                            // Checkboxes for Yes/No
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                                foreach (['1' => block_exacomp_get_string('yes'), '0' => block_exacomp_get_string('no')] as $key => $value) {
                                    $checked = in_array($key, (array) @$input_filter[BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO]) ? 'checked="checked"' : '';
                                    echo '<label>
                                                <input type="checkbox" name="filter['.$input_type.'][additionalinfo][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                          </label>&nbsp;&nbsp;&nbsp;';
                                }
                                break;
                            default:
                        }
                        echo '</div>';

                    }*/
                    if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
                        echo '<div class="filter-input-teacher-eval">
                            <span class="filter-title">'.block_exacomp_get_string('teacherevaluation').' :</span>';
                        switch ($gradingScheme) {
                            // Input fields for Grade|Points
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                                echo '<input data-exa-type="float" placeholder='.block_exacomp_get_string('from').' size="3" name="filter['.$input_type.'][teacherevaluation_from]" value="'.s(@$input_filter['teacherevaluation_from']).'"/> -
                                      <input data-exa-type="float" placeholder='.block_exacomp_get_string('to').' size="3" name="filter['.$input_type.'][teacherevaluation_to]" value="'.s(@$input_filter['teacherevaluation_to']).'"/>';
                                break;
                            // Checkboxes for Verbose|Yes/No
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                                foreach ([-1 => block_exacomp_get_string('no_specification')] + $teacher_eval_items as $key => $value) {
                                    $checked = in_array($key, (array) @$input_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) ? 'checked="checked"' : '';
                                    echo '<label>
                                             <input type="checkbox" name="filter['.$input_type.'][teacherevaluation][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                          </label>&nbsp;&nbsp;&nbsp;';
                                }
                                break;
                            // Checkboxes for Yes/No
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                                foreach (['1' => block_exacomp_get_string('yes'), '0' => block_exacomp_get_string('no')] as $key => $value) {
                                    $checked = in_array($key, (array) @$input_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) ? 'checked="checked"' : '';
                                    echo '<label>
                                                <input type="checkbox" name="filter['.$input_type.'][teacherevaluation][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                          </label>&nbsp;&nbsp;&nbsp;';
                                }
                                break;
                            default:
                        }
                        echo '</div>';
                    }
                    if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) {
                        echo '<div class="filter-input-student-eval">
                            <span class="filter-title">'.block_exacomp_get_string('selfevaluation').':</span>';
                        foreach ([0 => block_exacomp_get_string('no_specification')] + \block_exacomp\global_config::get_student_eval_items(false, $input_type) as $key => $value) {
                            $checked = in_array($key, (array)@$input_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) ? 'checked="checked"' : '';
                                echo '<label>
                                        <input type="checkbox" name="filter['.$input_type.']['.BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION.'][]" value="'.s($key).'" '.$checked.'/>  '.$value.'
                                      </label>&nbsp;&nbsp;&nbsp;';
                            }
                        echo '</div>';
				    }
				    ?>
			</div>
		</div>
		<?php
	}
}
