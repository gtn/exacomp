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

/**
 * exacomp block rendrer
 *
 * @package    block_exacomp
 * @copyright  2013 gtn gmbh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

define('STUDENTS_PER_COLUMN', 5);

class block_exacomp_renderer extends plugin_renderer_base {
	public function form_week_learningagenda($selectstudent,$action,$studentid, $view, $date = ''){
		global $COURSE, $CFG;

		if($view == 0){
			$content = html_writer::start_div('', array('align'=>'center'));
			$content .= html_writer::start_tag('div',array('style'=>'width:400px;'));
			$content .= $selectstudent;
			$content .= html_writer::start_tag('form', array('id'=>"calendar", 'method'=>"POST", 'action'=>new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid)));
			$content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&action='.($action-1))));
			$content .= html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/bwd_16x16.png', 'alt' => 'bwd', 'height' => '16', 'width'=>'16'));
			$content .= ' ';
			$content .= html_writer::end_tag('a');
			$content .= html_writer::start_tag('input', array('id'=>"calendarinput", 'value' => $date, 'class'=>"datepicker", 'type'=>"text", 'name'=>"calendarinput",
					'onchange'=>"this.form.submit();", 'readonly'));
			$content .= html_writer::end_tag('input');
			$content .= ' ';
			$content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&action='.($action+1))));
			$content .= html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/fwd_16x16.png', 'alt' => 'fwd', 'height' => '16', 'width'=>'16'));
			$content .= html_writer::end_tag('a');
			$content .= html_writer::end_tag('div');
			$content .= html_writer::end_tag('form');
				

			$content .= html_writer::start_tag('div', array('align'=>"right"));
			$content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&print=1&action='.$action)));
			$content .= html_writer::empty_tag('img', array('src'=>$CFG->wwwroot . '/blocks/exacomp/pix/view_print.png', 'alt'=>'print'));
			$content .= html_writer::end_tag('a');
			$content .= html_writer::end_tag('div');
			$content .= html_writer::end_div();
		} else {
			$content = html_writer::start_tag('div', array('id'=>'linkback', 'align'=>"right"));
			$content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&print=0&action='.$action)));
			$content .= html_writer::tag('p',get_string('LA_backtoview', 'block_exacomp'));
			$content .= html_writer::end_tag('a');
			$content .= html_writer::end_tag('div');
		}
		return $content;
	}
	public function render_learning_agenda($data, $wochentage){
		global $CFG, $COURSE;


		//header
		$table = new html_table();
		$table->attributes['class'] = 'lernagenda';
		$table->border = 3;
		$head = array();

		$cellhead1 = new html_table_cell();
		$cellhead1->text = html_writer::tag("p", get_string('LA_plan', 'block_exacomp'));
		//$cellhead1->colspan = 4;
		//without column "Was kann ich lernen"
		$cellhead1->colspan = 4;
		$head[] = $cellhead1;

		$cellhead2 = new html_table_cell();
		$cellhead2->text = html_writer::tag("p", get_string('LA_assessment', 'block_exacomp'));
		$cellhead2->colspan = 2;
		$head[] = $cellhead2;

		$table->head = $head;

		$rows = array();

		//erste Reihe->�berschriften
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->text = "";
		$cell->colspan = 2;
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_todo', 'block_exacomp'));
		$row->cells[] = $cell;

		//$cell = new html_table_cell();
		//$cell->text = html_writer::tag("p", get_string('learning', 'block_exacomp'));
		//$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_enddate', 'block_exacomp'));
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_student', 'block_exacomp'));
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_teacher', 'block_exacomp'));
		$row->cells[] = $cell;

		$rows[] = $row;

		foreach($data as $day=>$daydata){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = html_writer::tag("p", $day.": ".$daydata['date']);

			$cell->rowspan = count($daydata, COUNT_RECURSIVE)-count($daydata);
			$row->cells[] = $cell;

			foreach($daydata as $subject=>$subjectdata){
				if(strcmp($subject,'date')!=0){
					if(strcmp($subject, 'no example available')!=0){
						$cell = new html_table_cell();
						$cell->text = html_writer::tag("p",$subject);
						$cell->rowspan = count($subjectdata);

						$row->cells[] = $cell;
						foreach($subjectdata as $example){
							$cell = new html_table_cell();
							if(isset($example->task))
								$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
										.html_writer::tag("a", $example->title, array("href"=>$example->task, "target"=>"_blank")).(($example->cat) ? " (".$example->cat.")" : ""));
							elseif(isset($example->externalurl))
							$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
									.html_writer::tag("a", $example->title, array("href"=>$example->externalurl, "target"=>"_blank")).(($example->cat) ? " (".$example->cat.")" : ""));
							else
								$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
										.$example->title.(($example->cat) ? " (".$example->cat.")" : ""));

							$row->cells[] = $cell;

							$cell = new html_table_cell();
							$cell->text = date("d.m.y", $example->enddate);
							$row->cells[] = $cell;
							$cell = new html_table_cell();
							$grading=getgrading($COURSE->id);
							if($grading == 1){
								if($example->evaluate == 1){
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
								}
								else{
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
								}
							}else{
								if($example->evaluate > 0)
									$cell->text =	$example->evaluate;
								else
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
							}
							$row->cells[] = $cell;

							$cell = new html_table_cell();
							if($example->tevaluate == 1){
								$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
							}
							else{
								$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
							}
							$row->cells[] = $cell;

							$rows[] = $row;
							$row = new html_table_row();
						}
					}else{
						$cell = new html_table_cell();
						$cell->text = html_writer::tag("p",get_string('LA_no_example', 'block_exacomp'));
						$cell->colspan = 5;
						$row->cells[] = $cell;
						$rows[] = $row;
						$row = new html_table_row();
					}
				}
			}
		}

		$table->data = $rows;

		return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
	}

	public function print_view_learning_agenda($data, $studentname){
		global $CFG, $COURSE;

		//header
		$table = new html_table();
		$table->attributes['class'] = 'lernagenda';
		$table->attributes['border'] = 1;
		$table->attributes['style'] = 'padding:5px; table-layout:inherit';

		$head = array();

		$cellhead1 = new html_table_cell();
		$cellhead1->text = html_writer::tag("p", get_string('LA_plan', 'block_exacomp').
				get_string('LA_from_n', 'block_exacomp').$studentname.get_string('LA_from_m', 'block_exacomp').
				$data[get_string('LA_MON', 'block_exacomp')]['date'].get_string('LA_to', 'block_exacomp').$data[get_string('LA_FRI', 'block_exacomp')]['date']);
		//$cellhead1->colspan = 4;
		//without column "Was kann ich lernen"
		$cellhead1->colspan = 4;
		$head[] = $cellhead1;

		$cellhead2 = new html_table_cell();
		$cellhead2->text = html_writer::tag("p", get_string('LA_assessment', 'block_exacomp'));
		$cellhead2->colspan = 2;
		$head[] = $cellhead2;

		$table->head = $head;

		$rows = array();

		//erste Reihe->�berschriften
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->text = "";
		$cell->colspan = 2;
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_todo', 'block_exacomp'));
		$row->cells[] = $cell;

		//$cell = new html_table_cell();
		//$cell->text = html_writer::tag("p", get_string('learning', 'block_exacomp'));
		//$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_enddate', 'block_exacomp'));
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_student', 'block_exacomp'));
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = html_writer::tag("p", get_string('LA_teacher', 'block_exacomp'));
		$row->cells[] = $cell;

		$rows[] = $row;

		foreach($data as $day=>$daydata){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = html_writer::tag("p", $day.": ".$daydata['date']);

			$cell->rowspan = count($daydata, COUNT_RECURSIVE)-count($daydata);
			$row->cells[] = $cell;

			foreach($daydata as $subject=>$subjectdata){
				if(strcmp($subject,'date')!=0){
					if(strcmp($subject, 'no example available')!=0){
						$cell = new html_table_cell();
						$cell->text = html_writer::tag("p",$subject);
						$cell->rowspan = count($subjectdata);

						$row->cells[] = $cell;
						foreach($subjectdata as $example){
							$cell = new html_table_cell();
							$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.":")." ".(($example->numb > 0) ? $example->schooltype.$example->numb : "")." ".$example->title. (($example->cat) ? " (".$example->cat.")" : ""));
							$row->cells[] = $cell;

							$cell = new html_table_cell();
							$cell->text = date("d.m.y", $example->enddate);
							$row->cells[] = $cell;

							$cell = new html_table_cell();
							$grading=getgrading($COURSE->id);
							if($grading == 1){
								if($example->evaluate == 1){
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
								}
								else{
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
								}
							}else{
								if($example->evaluate > 0)
									$cell->text = $example->evaluate;
								else
									$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
							}
							$row->cells[] = $cell;

							$cell = new html_table_cell();
							if($example->tevaluate == 1){
								$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/ok_16x16.png', 'alt' => '1', 'height' => '16', 'width'=>'16'));
							}
							else{
								$cell->text = html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/blocks/exacomp/pix/del_16x16.png', 'alt' => '0', 'height' => '16', 'width'=>'16'));
							}
							$row->cells[] = $cell;

							$rows[] = $row;
							$row = new html_table_row();
						}
					}else{
						$cell = new html_table_cell();
						$cell->text = html_writer::tag("p",get_string('LA_no_example', 'block_exacomp'));
						$cell->colspan = 5;
						$row->cells[] = $cell;
						$rows[] = $row;
						$row = new html_table_row();
					}
				}
			}
		}

		$table->data = $rows;

		$content = html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
		return $content;
	}
	public function print_competence_overview($subjects, $courseid, $students, $showevaluation, $role, $scheme = 1) {
		global $PAGE;

		$rowgroup = 0;
		$table = new html_table();
		$rows = array();
		$studentsColspan = $showevaluation ? 2 : 1;
		$table->attributes['class'] = 'exabis_comp_comp';

		/* SUBJECTS */
		foreach($subjects as $subject) {
			if(!$subject->subs)
				continue;

			//for every subject
			$subjectRow = new html_table_row();
			$subjectRow->attributes['class'] = 'highlight';

			//subject-title
			$title = new html_table_cell();
			$title->colspan = 2;
			$title->text = html_writer::tag("b", $subject->title);

			$subjectRow->cells[] = $title;

			$studentsCount = 0;

			foreach($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);

				$studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
				$studentCell->colspan = $studentsColspan;
				$studentCell->text = fullname($student);

				$subjectRow->cells[] = $studentCell;
			}
			$rows[] = $subjectRow;

			if($showevaluation) {
				$evaluationRow = new html_table_row();
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = 2;
				$evaluationRow->cells[] = $emptyCell;

				if($role == ROLE_TEACHER) {
					$firstCol = get_string('studentshortcut','block_exacomp');
					$secCol = get_string('teachershortcut','block_exacomp');
				} else {
					$firstCol = get_string('teachershortcut','block_exacomp');
					$secCol = get_string('studentshortcut','block_exacomp');
				}
				foreach($students as $student) {
					$evaluationRow->cells[] = $firstCol;
					$evaluationRow->cells[] = $secCol;
				}
				$rows[] = $evaluationRow;
			}

			/* TOPICS */
			//for every topic
			$data = (object)array(
					'rowgroup' => &$rowgroup,
					'courseid' => $courseid,
					'showevaluation' => $showevaluation,
					'role' => $role,
					'scheme' => $scheme,
					'cm_mm' => block_exacomp_get_course_module_association($courseid),
					'course_mods' => get_fast_modinfo($courseid)->get_cms()
			);
			$this->print_topics($rows, 0, $subject->subs, $data, $students);
			$table->data = $rows;
		}

		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp")));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		return html_writer::tag("form", $table_html, array("id" => "assign-competencies", "method" => "post", "action" => $PAGE->url . "&action=save"));
	}

	public function print_topics(&$rows, $level, $topics, &$data, $students, $rowgroup_class = '') {
		global $version;

		//$padding = ($version) ? ($level-1)*20 :  ($level-2)*20+12;
		$padding = $level * 20 + 12;
		$evaluation = ($data->role == ROLE_TEACHER) ? "teacher" : "student";

		foreach($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
			$studentsCount = 0;
			$studentsColspan = 1;

			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!get_config('exacomp','alternativedatamodel') || (get_config('exacomp','alternativedatamodel') && $topicid == LIS_SHOW_ALL_TOPICS)));

			if ($hasSubs) {
				$data->rowgroup++;
				$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
				$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
			} else {
				$this_rowgroup_class = $rowgroup_class;
				$sub_rowgroup_class = '';
			}

			$topicRow = new html_table_row();
			$topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

			$outputidCell = new html_table_cell();
			$outputidCell->text = $outputid;
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			foreach($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
				$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
				$studentCell->colspan = $studentsColspan;

				// SHOW EVALUATION
				if($data->showevaluation) {
					$studentCellEvaluation = new html_table_cell();
					$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
				}

				/*
				 * if scheme == 1: print checkbox
				* if scheme != 1, role = student, version = LIS
				*/
				if($data->scheme == 1 || ($data->scheme != 1 && $data->role == ROLE_STUDENT && $version)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_checkbox("datatopics", $topic->id, 'topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

					$studentCell->text = $this->generate_checkbox("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme);
				}
				/*
				 * if scheme != 1, !version: print select
				* if scheme != 1, version = LIS, role = teacher
				*/
				elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

					$studentCell->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, $evaluation);
				}


				// ICONS
				if(isset($data->cm_mm->topics[$topic->id])) {
					//get CM instances
					$cm_temp = array();
					foreach($data->cm_mm->topics[$topic->id] as $cmid)
						$cm_temp[] = $data->course_mods[$cmid];

					$icon = block_exacomp_get_icon_for_user($cm_temp, $student);
					$studentCell->text .= '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
				}

				if($data->showevaluation)
					$topicRow->cells[] = $studentCellEvaluation;

				$topicRow->cells[] = $studentCell;
			}

			$rows[] = $topicRow;

			if (!empty($topic->descriptors)) {
				$this->print_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class);
			}

			if (!empty($topic->subs)) {
				$this->print_topics($rows, $level+1, $topic->subs, $data, $students, $sub_rowgroup_class);
			}
		}
	}

	function print_descriptors(&$rows, $level, $descriptors, &$data, $students, $rowgroup_class) {
		global $version, $PAGE, $USER;

		$evaluation = ($data->role == ROLE_TEACHER) ? "teacher" : "student";

		foreach($descriptors as $descriptor) {
			$checkboxname = ($version) ? "dataexamples" : "data";
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor);
			$studentsCount = 0;

			$padding = ($level) * 20 + 4;

			if($descriptor->examples) {
				$data->rowgroup++;
				$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
				$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
			} else {
				$this_rowgroup_class = $rowgroup_class;
			}
			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
			$exampleuploadCell = new html_table_cell();
			if($data->role == ROLE_TEACHER) {
				$exampleuploadCell->text = html_writer::link(
						new moodle_url('/blocks/exacomp/example_upload.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id,"topicid"=>$descriptor->topicid)),
						html_writer::empty_tag('img', array('src'=>'pix/upload_12x12.png', 'alt'=>'upload')),
						array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
			}
			$exampleuploadCell->text .= $outputid;

			$descriptorRow->cells[] = $exampleuploadCell;

			$titleCell = new html_table_cell();
			if($descriptor->examples)
				$titleCell->attributes['class'] = 'rowgroup-arrow';
			$titleCell->style = "padding-left: ".$padding."px";
			$titleCell->text = html_writer::div($outputname);

			$descriptorRow->cells[] = $titleCell;

			foreach($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
				$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;

				// SHOW EVALUATION
				if($data->showevaluation) {
					$studentCellEvaluation = new html_table_cell();
					$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
				}
				/*
				 * if scheme == 1: print checkbox
				* if scheme != 1, role = student, version = LIS
				*/
				if($data->scheme == 1 || ($data->scheme != 1 && $data->role == ROLE_STUDENT && $version)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

					$studentCell->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme);
				}
				/*
				 * if scheme != 1, !version: print select
				* if scheme != 1, version = LIS, role = teacher
				*/
				elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

					$studentCell->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, $evaluation);
				}

				// ICONS
				if(isset($data->cm_mm->competencies[$descriptor->id])) {
					//get CM instances
					$cm_temp = array();
					foreach($data->cm_mm->competencies[$descriptor->id] as $cmid)
						$cm_temp[] = $data->course_mods[$cmid];

					$icon = block_exacomp_get_icon_for_user($cm_temp, $student);
					$studentCell->text .= '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
				}
				if($data->showevaluation)
					$descriptorRow->cells[] = $studentCellEvaluation;

				$descriptorRow->cells[] = $studentCell;
			}

			$rows[] = $descriptorRow;

			$studentsCount = 0;
			$checkboxname = "dataexamples";

			foreach($descriptor->examples as $example) {
				$exampleRow = new html_table_row();
				$exampleRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class;
				$exampleRow->cells[] = new html_table_cell();

				$titleCell = new html_table_cell();
				$titleCell->style = "padding-left: ". ($padding + 20 )."px";
				$titleCell->text = $example->title;

				if(isset($example->creatorid) && $example->creatorid == $USER->id) {
					$titleCell->text .= html_writer::link($PAGE->url . "&delete=" . $example->id, html_writer::empty_tag("img", array("src" => "pix/x_11x11_redsmall.png", "alt" => "Delete", "onclick" => "return confirm('" . get_string('delete_confirmation','block_exacomp') . "')")));
				}

				if($example->task)
					$titleCell->text .= html_writer::link($example->task, html_writer::empty_tag('img', array('src'=>'pix/i_11x11.png', 'alt'=>'link')),array("target" => "_blank"));
				if($example->externalurl)
					$titleCell->text .= html_writer::link($example->externalurl, html_writer::empty_tag('img', array('src'=>'pix/i_11x11.png', 'alt'=>'link')),array("target" => "_blank"));

				$exampleRow->cells[] = $titleCell;


				foreach($students as $student) {
					$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
					$studentCell = new html_table_cell();
					$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;

					// SHOW EVALUATION
					if($data->showevaluation) {
						$studentCellEvaluation = new html_table_cell();
						$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
					}

					/*
					 * if scheme == 1: print checkbox
					* if scheme != 1, role = student, version = LIS
					*/
					if($data->scheme == 1 || ($data->scheme != 1 && $data->role == ROLE_STUDENT && $version)) {
						if($data->showevaluation)
							$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
							
						if($data->role == ROLE_STUDENT) {
							$studentCell->text = get_string('assigndone','block_exacomp');
							$studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme);
							
							$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
						}
						else
							$studentCell->text = $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme);
					}
					/*
					 * if scheme != 1, !version: print select
					* if scheme != 1, version = LIS, role = teacher
					*/
					elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
						if($data->showevaluation)
							$studentCellEvaluation->text = $this->generate_select($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

						$studentCell->text = $this->generate_select($checkboxname, $example->id, 'examples', $student, $evaluation);
					}

					if($data->showevaluation)
						$exampleRow->cells[] = $studentCellEvaluation;
					$exampleRow->cells[] = $studentCell;
				}

				$rows[] = $exampleRow;
			}
		}
	}

	private function print_student_example_evaluation_form($exampleid, $studentid, $courseid) {
		global $DB;
		$exampleInfo = $DB->get_record(DB_EXAMPLEEVAL, array("exampleid" => $exampleid, "studentid" => $studentid, "courseid" => $courseid));
		$options = array();
		$options['self'] = get_string('assignmyself','block_exacomp');
		$options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
		$options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
		$options['teacher'] = get_string('assignteacher','block_exacomp');

		$content = html_writer::select($options, 'dataexamples[' . $exampleid . '][' . $studentid . '][studypartner]', (isset($exampleInfo->studypartner) ? $exampleInfo->studypartner : null), false);

		$content .= get_string('assignfrom','block_exacomp');
		$content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $exampleid . '][' . $studentid . '][starttime]', 'readonly' => 'readonly',
				'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));

		$content .= get_string('assignuntil','block_exacomp');
		$content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $exampleid . '][' . $studentid . '][endtime]', 'readonly' => 'readonly',
				'value' => (isset($exampleInfo->endtime) ? date("Y-m-d",$exampleInfo->endtime) : null)));

		return $content;
	}

	/**
	 *
	 * @param int $students Amount of students
	 */
	public function print_column_selector($students) {
		if($students < STUDENTS_PER_COLUMN)
			return;

		$content = html_writer::tag("b", get_string('columnselect','block_exacomp'));
		for($i=0; $i < ceil($students / STUDENTS_PER_COLUMN); $i++) {
			$content .= " ";
			$content .= html_writer::link('javascript:Exacomp.onlyShowColumnGroup('.$i.');',
					($i*STUDENTS_PER_COLUMN+1).'-'.min($students, ($i+1)*STUDENTS_PER_COLUMN),
					array('class' => 'colgroup-button colgroup-button-'.$i));
		}
		$content .= " " . html_writer::link('javascript:Exacomp.onlyShowColumnGroup(null);',
				get_string('allstudents','block_exacomp'),
				array('class' => 'colgroup-button colgroup-button-all'));

		return html_writer::div($content,'spaltenbrowser');
	}
	public function print_student_evaluation($showevaluation) {
		global $OUTPUT,$COURSE;

		$link = new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1")));
		$evaluation = $OUTPUT->box_start();
		$evaluation .= get_string('overview','block_exacomp');
		$evaluation .= html_writer::empty_tag("br");
		$evaluation .= ($showevaluation) ? get_string('hideevaluation','block_exacomp',$link->__toString()) : get_string('showevaluation','block_exacomp',$link->__toString());
		$evaluation .= $OUTPUT->box_end();

		return $evaluation;
	}
	public function print_overview_legend($teacher) {
		$legend = html_writer::tag("img", "", array("src" => "pix/list_12x11.png", "alt" => get_string('legend_activities','block_exacomp')));
		$legend .= get_string('legend_activities','block_exacomp') . " - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/folder_fill_12x12.png", "alt" => get_string('legend_eportfolio','block_exacomp')));
		$legend .= get_string('legend_eportfolio','block_exacomp') . " - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/x_11x11.png", "alt" => get_string('legend_notask','block_exacomp')));
		$legend .= get_string('legend_notask','block_exacomp');

		if($teacher) {
			$legend .= " - ";
			$legend .= html_writer::tag("img", "", array("src" => "pix/upload_12x12.png", "alt" => get_string('legend_upload','block_exacomp')));
			$legend .= get_string('legend_upload','block_exacomp');
		}

		return $legend;
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
	public function generate_checkbox($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false) {
		return html_writer::checkbox(
				$name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']',
				$scheme,
				(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme/2), null, (!$disabled) ? null : array("disabled"=>"disabled"));
	}

	/**
	 * Used to generate a select for topics/competencies/examples values
	 *
	 * @param String $name name of the checkbox: data for competencies, dataexamples for examples, datatopic for topics
	 * @param int $compid
	 * @param String $type comptencies or topics or examples
	 * @param stdClass $student
	 * @param String $evaluation teacher or student
	 * @param bool $disabled disabled becomes true for the "show evaluation" option
	 *
	 * @return String $select html code for select
	 */
	public function generate_select($name, $compid, $type, $student, $evaluation, $disabled = false) {
		$options = array();
		for($i=0;$i<=$scheme;$i++)
			$options[] = $i;

		return html_writer::select(
				$options,
				$checkboxname . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']',
				(isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : 0,
				false,(!$disabled) ? null : array("disabled"=>"disabled"));
	}

	public function print_edit_config($data, $courseid){
		global $OUTPUT;

		$header = html_writer::label($data->headertext, '').html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rows = array();

		$temp = false;
		foreach($data->levels as $levelstruct){
			if($levelstruct->level->source > 1 && $temp == false){
				$row = new html_table_row();
				$row->attributes['class'] = 'highlight';

				$cell = new html_table_cell();
				//$cell->attributes['class'] = 'category catlevel1';
				$cell->colspan = 2;
				$cell->text = html_writer::tag('h2', get_string('specificcontent', 'block_exacomp'));
					
				$row->cells[] = $cell;
				$rows[] = $row;
				$temp = true;
			}

			$row = new html_table_row();
			$row->attributes['class'] = 'highlight';
				
			$cell = new html_table_cell();
			$cell->colspan = 2;
			$cell->text = html_writer::tag('b', $levelstruct->level->title);

			$row->cells[] = $cell;
			$rows[] = $row;

			foreach($levelstruct->schooltypes as $schooltypestruct){
				$row = new html_table_row();
				$cell = new html_table_cell();
				$cell->text = $schooltypestruct->schooltype->title;
				$row->cells[] = $cell;
					
				$cell = new html_table_cell();
				if($schooltypestruct->ticked){
					$cell->text = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'data['.$schooltypestruct->schooltype->id.']', 'value'=>$schooltypestruct->schooltype->id, 'checked'=>'checked'));
				}else{
					$cell->text = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'data['.$schooltypestruct->schooltype->id.']', 'value'=>$schooltypestruct->schooltype->id));
				}

				$row->cells[] = $cell;
				$rows[] = $row;
			}
		}

		$hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save'));
		$hiddenaction .= html_writer::empty_tag('br');
		$innerdiv = html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))));

		$table->data = $rows;


		$div = html_writer::div(html_writer::tag('form', html_writer::table($table).$hiddenaction.$innerdiv, array('action'=>'edit_config.php?courseid='.$courseid, 'method'=>'post')), 'exabis_competencies_lis');


		$content = html_writer::tag("div", $header.$div, array("id"=>"exabis_competences_block"));

		return $content;
	}
	public function print_edit_course($settings, $action, $courseid){
		$saved = "";
		if ($action == 'save_coursesettings')
			$saved = html_writer::label(get_string("save_success", "block_exacomp"), "").html_writer::empty_tag('br');
			
		$input_grading = get_string('grading_scheme', 'block_exacomp').": &nbsp"
		.html_writer::empty_tag('input', array('type'=>'text', 'size'=>2, 'name'=>'grading', 'value'=>block_exacomp_get_grading_scheme($courseid)))
		.html_writer::empty_tag('br');

		$checkbox = html_writer::checkbox('uses_activities', 1, $settings->uses_activities == 1);

		$input_activities = $checkbox.get_string('uses_activities', 'block_exacomp')
		.html_writer::empty_tag('br');

		$checkbox = html_writer::checkbox('show_all_descriptors',1,$settings->show_all_descriptors == 1);
		$input_descriptors = $checkbox.get_string('show_all_descriptors', 'block_exacomp')
		.html_writer::empty_tag('br');
			
		$checkbox = html_writer::checkbox('show_all_examples', 1, $settings->show_all_examples == 1);
		
		$input_examples = $checkbox.get_string('show_all_examples', 'block_exacomp')
		.html_writer::empty_tag('br');
			
		$input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save', 'admin')));

		$hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save_coursesettings'));

		$div = html_writer::div(html_writer::tag('form',
				$saved.$input_grading.$input_activities.$input_descriptors.$input_examples.$hiddenaction.$input_submit,
				array('action'=>'edit_course.php?courseid='.$courseid, 'method'=>'post')), 'block_excomp_center');

		$content = html_writer::tag("div", $div, array("id"=>"exabis_competences_block"));
			
		return $content;
	}

	public function print_my_badges($badges){
		$content = "";
		if($badges->issued){
			$content .= html_writer::tag('h2', get_string('mybadges', 'block_exacomp'));
			foreach ($badges->issues as $badge){
				$context = context_course::instance($badge->courseid);
				$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
				$img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
				$innerdiv = html_writer::div($badge->name,"", array('style'=>'font-weight:bold;'));
				$div = html_writer::div($img.$innerdiv, '', array('style'=>'padding:10px;'));
				$content .= $div;
			}

		}
		if($badges->pending){
			$content .= html_writer::tag('h2', get_string('pendingbadges', 'block_exacomp'));
			foreach($badges as $badge){
				$context = context_course::instance($badge->courseid);
				$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
				$img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
				$innerdiv = html_writer::div($badge->name, "", array('style'=>'font-weight: bold;'));
				$innerdiv2 = "";
				if($badge->descriptorStatus){
					$innerdiv2_content = "";
					foreach($badge->descriptorStatus as $descriptor){
						$innerdiv2_content .= html_writer::div($descriptor, "", array('style'=>'padding: 3px 0'));
					}
					$innerdiv2 = html_writer::div($innerdiv2_content, "", array('style'=>'padding: 2px 10px'));
				}
				$div = html_writer::div($img.$innerdiv.$innerdiv2, '', array('style'=>'padding: 10px;'));
				$content .= $div;
			}
		}

		return $content;
	}

	public function print_head_view_examples($sort, $show_all_examples, $url, $context){
		$text_link1 = ($sort=="desc") ? html_writer::tag('b', get_string("subject", "block_exacomp")) : get_string("subject", "block_exacomp");
		$text_link2 = ($sort=="tax") ? html_writer::tag('b', get_string("taxonomies", "block_exacomp")) : get_string("taxonomies", "block_exacomp");
		$content = get_string('sorting', 'block_exacomp')
		.html_writer::link($url.'&sort=desc', $text_link1)." "
		.html_writer::link($url.'&sort=tax', $text_link2);

		if(has_capability('block/exacomp:teacher', $context) OR has_capability('block/exacomp:admin', $context)){
			$input = '';
			if($show_all_examples != 0)
				$input = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'showallexamples_check', 'value'=>1, 'onClick'=>'showallexamples_form.submit();', 'checked'=>'checked'));
			else
				$input = html_writer::empty_tag('input', array('type'=>'checkbox', 'name'=>'showallexamples_check', 'value'=>1, 'onClick'=>'showallexamples_form.submit();'));

			$input .= get_string('show_all_course_examples', 'block_exacomp');

			$content .= html_writer::tag('form', $input, array('method'=>'post', 'name'=>'showallexamples_form'));
		}
		$div_exabis_competences_block = html_writer::start_div('', array('id'=>'exabis_competences_block'));
		return $div_exabis_competences_block.$content;
	}

	public function print_tree_head(){
		$content = html_writer::empty_tag('br').html_writer::empty_tag('br');
		$content .= html_writer::link("javascript:ddtreemenu.flatten('comptree', 'expand')", get_string("expandcomps", "block_exacomp"));
		$content .=' | ';
		$content .= html_writer::link("javascript:ddtreemenu.flatten('comptree', 'contact')", get_string("contactcomps", "block_exacomp"));
		return $content;
	}

	public function print_tree_view_examples_desc($tree, $do_form = true){
		$li_subjects = '';
		foreach($tree as $subject){
			$subject_example_content = (empty($subject->numb) || $subject->numb==0)? '' : $subject->numb;
			$li_topics = '';
				
			$li_topics = $this->print_tree_view_examples_desc_rec_topic($subject->subs, $subject_example_content);
				
			$ul_topics = html_writer::tag('ul', $li_topics);
			$li_subjects .= html_writer::tag('li', $subject->title
					.$ul_topics);
		}

		$conditions = null;
		if($do_form)
			$conditions = array('id'=>'comptree', 'class'=>'treeview');
			
		$ul_subjects = html_writer::tag('ul', $li_subjects, $conditions);

		if($do_form)
			$content = html_writer::tag('form', $ul_subjects, array('name'=>'treeform'));
		else
			$content = $ul_subjects;

		return $content;
	}

	public function print_tree_view_examples_desc_rec_topic($subs, $subject_example_content){
		$li_topics = '';
		foreach($subs as $topic){
			$topic_example_content = (empty($topic->cat)) ? '' : '('.$topic->cat.')';
			$li_descriptors = '';
			if(isset($topic->descriptors)){
				foreach($topic->descriptors as $descriptor){
					$li_examples = '';
					foreach($descriptor->examples as $example){
						//create description for on mouse over
						$text=$example->description;
						$text = str_replace("\"","",$text);
						$text = str_replace("\'","",$text);
						$text = str_replace("\n"," ",$text);
						$text = str_replace("\r"," ",$text);
						$text = str_replace(":","\:",$text);
							
						$example_content = '';

						$inner_example_content = $subject_example_content .
						' ' . $example->title . ' ' .
						$topic_example_content;

						//if text is set, on mouseover is enabled, other wise just inner_example_content is displayed
						if($text)
							$example_content = html_writer::tag('a',
									$inner_example_content,
									array('onmouseover'=>'Tip(\''.$text.'\')', 'onmouseout'=>'UnTip()'));
						else
							$example_content = $inner_example_content;
							
						$icons = $this->example_tree_get_exampleicon($example);

						$li_examples .= html_writer::tag('li', $example_content.$icons);
					}
					$ul_examples = html_writer::tag('ul', $li_examples);
					$li_descriptors .= html_writer::tag('li', $descriptor->title
							.$ul_examples);
				}
			}
			$ul_descriptors = html_writer::tag('ul', $li_descriptors);
				
			$ul_subs = '';
			if(isset($topic->subs)){
				$li_subs = $this->print_tree_view_examples_desc_rec_topic($topic->subs, $subject_example_content);
				$ul_subs .= html_writer::tag('ul', $li_subs);
			}
				
			$li_topics .= html_writer::tag('li', $topic->title
					.$ul_descriptors.$ul_subs);
				
		}
		return $li_topics;
	}
	public function example_tree_get_exampleicon($example) {
		$icon="";
		if($example->task) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'width'=>16, 'height'=>16));
			$icon .= html_writer::link($example->task, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('task_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		} if($example->solution) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf solution.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->solution, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('solution_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->attachement) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/attach_2.png'), 'alt'=>get_string("task_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->attachement, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('attachement_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}if($example->externaltask) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("task_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->externaltask, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->externalurl) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->externalurl, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->completefile) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/folder.png'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->completefile, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('total_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		return $icon;
	}

	public function print_tree_view_examples_tax($tree){
		$li_taxonomies = '';
		foreach($tree as $taxonomy){
			$ul_subjects = $this->print_tree_view_examples_desc($taxonomy->subs, false);
			$li_taxonomies .= html_writer::tag('li', $taxonomy->title->title
					.$ul_subjects);
		}

		$ul_taxonomies = html_writer::tag('ul', $li_taxonomies, array('id'=>'comptree', 'class'=>'treeview'));
		$content = html_writer::tag('form', $ul_taxonomies, array('name'=>'treeform'));
		return $content;
	}

	public function print_foot_view_examples(){
		$content = html_writer::tag('script', 'ddtreemenu.createTree("comptree", true)', array('type'=>'text/javascript'));
		return $content.html_writer::end_div();
	}
	public function print_courseselection($tree){
		global $PAGE;
		
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rowgroup = 0;
		$rows = array();
		foreach($tree as $subject){
			
			$hasSubs = !empty($subject->subs);
				
			if ($hasSubs) {
				$rowgroup++;
				$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup;
				$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup;
			} else {
				$this_rowgroup_class = $rowgroup_class;
				$sub_rowgroup_class = '';
			}
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';
			
			$cell = new html_table_cell();
			$cell->text = html_writer::div(html_writer::tag('b', $subject->title));
			$cell->attributes['class'] = 'rowgroup-arrow';
			$cell->colspan = 3;
			$row->cells[] = $cell;
			
			$rows[] = $row;
			$this->print_topics_courseselection($rows, 0, $subject->subs, $rowgroup, $sub_rowgroup_class);
		}
		
		$table->data = $rows;
		
		
		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		return html_writer::tag("form", $table_html, array("method" => "post", "action" => $PAGE->url . "&action=save", "id" => "course-selection"));
	}
	public function print_topics_courseselection(&$rows, $level, $topics, &$rowgroup, $rowgroup_class = ''){
		global $version;

		$padding = $level * 20 + 12;
		
		foreach($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);

			$hasSubs = !empty($topic->subs);
			
			if ($hasSubs) {
				$rowgroup++;
				$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$rowgroup.' '.$rowgroup_class;
				$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$rowgroup.' '.$rowgroup_class;
			} else {
				$this_rowgroup_class = $rowgroup_class;
				$sub_rowgroup_class = '';
			}

			$topicRow = new html_table_row();
			$topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';

			$outputidCell = new html_table_cell();
			$outputidCell->text = $outputid;
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;
			
			$cell = new html_table_cell();
			$cell->text = html_writer::checkbox('data['.$topic->id.']', $topic->id, ($topic->checked)?true:false, '', array('class'=>'topiccheckbox'));
			$topicRow->cells[] = $cell;
			
			$rows[] = $topicRow;
			
			if (!empty($topic->subs)) {
				$this->print_topics_courseselection($rows, $level+1, $topic->subs, $rowgroup, $sub_rowgroup_class);
			}
		}
	}

}
?>