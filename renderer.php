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
 * @package	block_exacomp
 * @copyright  2013 gtn gmbh
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

define('STUDENTS_PER_COLUMN', 5);

require_once __DIR__."/lib/xmllib.php";

use \block_exacomp\globals as g;

class block_exacomp_renderer extends plugin_renderer_base {
	
	public function header($context=null, $courseid=0, $page_identifier="", $tabtree=true) {
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

		return
			parent::header().$extras.(($tabtree && $context)?parent::tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier):'').
			$this->print_wrapperdivstart();
	}
	
	public function footer() {
		return
			$this->print_wrapperdivend().
			parent::footer();
	}
	
	public function requires() {
		global $PAGE;
		
		// init default js / css
		block_exacomp_init_js_css();
		
		return $PAGE->requires;
	}
	
	public function pix($image, $alt=null, $attributes=array()) {
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
	
	public function local_pix_icon($image, $alt=null, $attributes=array()) {
		return $this->pix($image, $alt=null, $attributes + ['class'=>'smallicon']);
	}
	
	public function form_week_learningagenda($selectstudent,$action,$studentid, $view, $date = ''){
		global $COURSE, $CFG;

		if($view == 0){
			$content = html_writer::start_div('');
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

			$print_content = html_writer::link('javascript:window.print()', 
			html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt'=>'print')), array('class'=>'print'));
			$content .= html_writer::div(html_writer::tag('form', $print_content), 'competence_profile_printbox');
	
			/*$content .= html_writer::start_tag('div', array('align'=>"right"));
			$content .= html_writer::start_tag('a', array('href' => new moodle_url('/blocks/exacomp/learningagenda.php?courseid='.$COURSE->id.'&studentid='.$studentid.'&print=1&action='.$action)));
			$content .= html_writer::empty_tag('img', array('src'=>$CFG->wwwroot . '/blocks/exacomp/pix/view_print.png', 'alt'=>'print'));
			$content .= html_writer::end_tag('a');
			$content .= html_writer::end_tag('div');*/
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

		//erste Reihe->Überschriften
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
							if($task = block_exacomp_get_file_url($example, 'example_task'))
								$cell->text = html_writer::tag("p", html_writer::tag("b", $example->desc.": ").(($example->numb > 0) ? $example->schooltype.$example->numb : "")." "
										.html_writer::tag("a", $example->title, array("href"=>$task, "target"=>"_blank")).(($example->cat) ? " (".$example->cat.")" : ""));
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
							$grading = block_exacomp_get_grading_scheme($COURSE->id);
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

		//erste Reihe->Überschriften
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
							$grading=block_exacomp_get_grading_scheme($COURSE->id);
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
	public function print_subject_dropdown($schooltypetree, $selectedSubject, $studentid = 0) {
		global $PAGE;
		$content = get_string("choosesubject", "block_exacomp");
		$array = array();
		$options = array();
		
		foreach($schooltypetree as $schooltype){
			$options[$schooltype->title] = array();
			foreach($schooltype->subjects as $subject)
				$options[$schooltype->title][$subject->id] = $subject->title;
			
			$array[] = $options;
			$options = array();
		}
		
		$content .= html_writer::select($array, "lis_subjects",$selectedSubject, false,
				array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$studentid."&subjectid='+this.value;"));
		
		return $content;
	}
	/**
	 * Prints 2 select inputs for subjects and topics
	 */
	public function print_overview_dropdowns($schooltypetree, $selectedSubject, $selectedTopic, $students, $selectedStudent = 0, $isTeacher = false) {
		global $PAGE, $COURSE, $USER, $NG_PAGE;

		$content = "";
		$right_content = "";
		
		if (!$this->is_edit_mode()) {
			$right_content .= html_writer::empty_tag('input', array('type'=>'button', 'id'=>'print', 'value'=>\block_exacomp\trans('de:Drucken'), 'onclick' => "window.open(location.href+'&print=1');"));
		}
		
		if($isTeacher){
			if ($this->is_edit_mode()) {
				// display a hidden field? not needed, because the form never gets submitted (it's ajax)
				// $content .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'exacomp_competence_grid_select_student', 'value'=>$selectedStudent));
				$content .= '<h3>'.\block_exacomp\trans('de:Sie befinden sich im Bearbeiten Modus').'</h3>';
			} else {
				$content .= html_writer::empty_tag("br");
				$content .= get_string("choosestudent", "block_exacomp");
				$content .= block_exacomp_studentselector($students,$selectedStudent,$NG_PAGE->url, BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN);
			}

			if(!$this->is_edit_mode() && $selectedStudent != BLOCK_EXACOMP_SHOW_STATISTIC) {
				$right_content .= block_exacomp_get_message_icon($selectedStudent);
			}
			
			if ($this->is_edit_mode()) {
				$right_content .= html_writer::empty_tag('input', array('type'=>'button', 'id'=>'add_subject', 'value'=>\block_exacomp\trans('add_subject', 'de:Kompetenzraster anlegen'),
					'exa-type' => 'iframe-popup', 'exa-url' => "subject.php?courseid={$COURSE->id}&show=add"));
			}
			
			$url = new moodle_url('/blocks/exacomp/pre_planning_storage.php', array('courseid'=>$COURSE->id, 'creatorid'=>$USER->id));
			$right_content .= html_writer::tag('button', 
					html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'), 
						'title'=> get_string('pre_planning_storage', 'block_exacomp'))
					),
				array(
					'id'=>'pre_planning_storage_submit', 'name'=> 'pre_planning_storage_submit',
					'type'=>'button', /* browser default setting for html buttons is submit */
					'exa-type' => 'iframe-popup', 'exa-url' => $url->out(false)
				)
			);

			$right_content .= $this->print_edit_mode_button();
		} else {
			foreach(block_exacomp_get_teachers_by_course($COURSE->id) as $teacher) {
				$right_content .= block_exacomp_get_message_icon($teacher->id);
				
			}
		}
		$content .= html_writer::div($right_content, 'edit_buttons_float_right');
		
		return $content;
	}
	
	public function is_edit_mode() {
		return isset($this->editmode) && $this->editmode;
	}
	
	public function is_print_mode() {
		return isset($this->print) && $this->print;
	}
	
	public function print_edit_mode_button() {
		global $NG_PAGE;
		
		$url = new block_exacomp\url($NG_PAGE->url);

		$edit = $this->is_edit_mode();
		$url->param('editmode', !$edit);
		
		return html_writer::empty_tag('input', array('type'=>'button', 'id'=>'edit_mode_submit', 'name'=> 'edit_mode_submit', 'value'=>\block_exacomp\get_string(($edit) ? 'turneditingoff' : 'turneditingon'),
				 "onclick" => "document.location.href='".$url->out(false)."'"));
	}
	
	public function print_subjects_menu($subjects, $selectedSubject, $selectedTopic) {
		global $NG_PAGE, $CFG, $COURSE;

		$content = html_writer::start_div('subjects_menu');
		$content .= html_writer::start_tag('ul');

		foreach($subjects as $subject) {
			$extra = '';
			if ($this->is_edit_mode() && $subject->source == block_exacomp::DATA_SOURCE_CUSTOM) {
				$extra .= ' <img src="pix/edit.png" title="'.\block_exacomp\trans('edit').'" exa-type="iframe-popup" exa-url="subject.php?courseid='.$COURSE->id.'&id='.$subject->id.'" />';

			}
			$content .= html_writer::tag('li',
					html_writer::link(
						new block_exacomp\url($NG_PAGE->url, ['ng_subjectid' => $subject->id, 'topicid'=>BLOCK_EXACOMP_SHOW_ALL]),
						$subject->title.$extra, array('class' => (!$selectedTopic && $subject->id == $selectedSubject->id) ? 'type current' : 'type'))
			);

			foreach($subject->topics as $topic) {
				$extra = '';
				if ($this->is_edit_mode() && $topic->source == block_exacomp::DATA_SOURCE_CUSTOM) {
					$extra .= ' <img src="pix/edit.png" title="'.\block_exacomp\trans('edit').'" exa-type="iframe-popup" exa-url="topic.php?courseid='.$COURSE->id.'&id='.$topic->id.'" />';
				}

				$content .= html_writer::tag('li',
					html_writer::link(new block_exacomp\url($NG_PAGE->url, ['ng_subjectid' => $subject->id, 'topicid' => $topic->id]),
							block_exacomp_get_topic_numbering($topic).' '.$topic->title.$extra, array('class' => ($selectedTopic && $topic->id == $selectedTopic->id) ? 'current' : ''))
					);
			}
			   if ($this->is_edit_mode() && $subject->source == block_exacomp::DATA_SOURCE_CUSTOM) {
				// only if editing and if subject was added by teacher
				$content .= html_writer::tag('li',
					html_writer::link("topic.php?show=add&courseid={$COURSE->id}&subjectid={$subject->id}",
							"<img src=\"{$CFG->wwwroot}/pix/t/addfile.png\" /> ".
							\block_exacomp\trans('de:Neuer Kompetenzbereich'), array('exa-type' => 'iframe-popup'))
					);
			}
		
		}
		
		$content .= html_writer::end_tag('ul');
		$content .= html_writer::end_tag('div');		
		return $content;
	}
	public function print_niveaus_menu($niveaus,$selectedNiveau,$selectedTopic) {
		global $NG_PAGE, $CFG, $COURSE;
		
		$edit = $this->is_edit_mode();
		$studentid = optional_param('studentid', BLOCK_EXACOMP_SHOW_ALL_STUDENTS,PARAM_INT);
		//$subjectid = 
		
		$content = html_writer::start_div('niveaus_menu');
		$content .= html_writer::start_tag('ul');

		foreach ($niveaus as $niveau) {
			$title = isset($niveau->cattitle) ? $niveau->cattitle : $niveau->title;
			$subtitle = $niveau->get_subtitle($selectedTopic->subjid);
			$content .= html_writer::tag('li',
					html_writer::link(new block_exacomp\url($NG_PAGE->url, ['niveauid' => $niveau->id]),
							$title.($subtitle?'<span class="subtitle">'.$subtitle.'</span>':''), array('class' => ($niveau->id == $selectedNiveau->id) ? 'current' : '', 'title'=>$title.($subtitle?': '.$subtitle:'')))
					);
		}
		
		if ($this->is_edit_mode()) {
			// add niveau button
			$content .= html_writer::tag('li',
						html_writer::link("niveau.php?show=add&courseid={$COURSE->id}&topicid={$selectedTopic->id}",
								"<img src=\"{$CFG->wwwroot}/pix/t/addfile.png\" /> ".\block_exacomp\trans('de:Neuer Lernfortschritt'), array('exa-type' => 'iframe-popup'))
			);
		}
		
		$content .= html_writer::end_tag('ul');
		$content .= html_writer::end_tag('div');
		return $content;
	}
	public function print_overview_metadata_teacher($subject,$topic){

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_top';

		$rows = array();

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';
		
		$cell->text = html_writer::tag('b', get_string('instruction', 'block_exacomp'))
		.html_writer::tag('p', (!empty($subject->description) ? $subject->description. '<br/>' : '') . (!empty($topic->description) ? $topic->description : ''));

		$row->cells[] = $cell;
		$rows[] = $row;
		$table->data = $rows;

		$content = html_writer::table($table);
		$content .= html_writer::empty_tag('br');
		if(isset($subject->description) || isset($topic->description))
			return $content;
	}
	public function print_overview_metadata_student($subject, $topic, $topic_evaluation, $showevaluation, $scheme, $icon = null){
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_top';

		$rows = array();

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';
		$cell->text = html_writer::tag('b', get_string('requirements', 'block_exacomp'))
		.html_writer::tag('p', $topic->requirement);

		$row->cells[] = $cell;
		$rows[] = $row;

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';
		$cell->text = html_writer::tag('b', get_string('forwhat', 'block_exacomp'))
		.html_writer::tag('p', $topic->benefit);

		$row->cells[] = $cell;
		$rows[] = $row;

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';
		$cell->text = html_writer::tag('b', get_string('howtocheck', 'block_exacomp'))
		.html_writer::tag('p', $topic->knowledgecheck);

		/*
		$p_content = get_string('reached_topic', 'block_exacomp');

		if($scheme == 1)
			$p_content .= "S: " . html_writer::checkbox("topiccomp", 1, ((isset($topic_evaluation->student[$topic->id]))?true:false))
			." Bestätigung L: ".html_writer::checkbox("topiccomp", 1, ((isset($topic_evaluation->teacher[$topic->id]))?true:false), "", array("disabled"=>"disabled"));
		else{
			(isset($topic_evaluation->student[$topic->id]))?$value_student = $topic_evaluation->student[$topic->id] : $value_student = 0;
			(isset($topic_evaluation->teacher[$topic->id]))?$value_teacher = $topic_evaluation->teacher[$topic->id] : $value_teacher = 0;
				
			$p_content .= "S: " . html_writer::checkbox("topiccomp", $scheme, $value_student >= ceil($scheme/2))
			." Bestätigung L: ". $value_teacher;

		}
			
		if(isset($icon))
			$p_content .= " ".html_writer::span($icon->img, 'exabis-tooltip', array('title'=>s($icon->text)));
		$p_content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'topiccompid', 'value'=>$topic->id));
		$p_content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'subjectcompid', 'value'=>$subject->id));
			
		$cell->text .= html_writer::tag('p', $p_content);
		*/

		$row->cells[] = $cell;
		$rows[] = $row;

		$table->data = $rows;

		return html_writer::table($table).html_writer::empty_tag('br');
	}
	public function print_overview_metadata($schooltype, $subject, $descriptor, $cat){
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_info';

		$rows = array();

		$row = new html_table_row();

		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('subject_singular', 'block_exacomp'), 'exabis_comp_top_name')
		. html_writer::div($schooltype, 'exabis_comp_top_value');

		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('comp_field_idea', 'block_exacomp'), 'exabis_comp_top_name')
		. html_writer::div($subject ? (!empty($subject->numb)?$subject->numb." - ":'').$subject->title : '', 'exabis_comp_top_value');

		$row->cells[] = $cell;

		if ($descriptor) {
			$cell = new html_table_cell();
			$cell->text = html_writer::span(get_string('comp', 'block_exacomp'), 'exabis_comp_top_name')
			. html_writer::div($descriptor->title, 'exabis_comp_top_value');
	
			$row->cells[] = $cell;
		}

		if(block_exacomp_is_altversion()){
			$cell = new html_table_cell();
			$cell->text = html_writer::span(get_string('progress', 'block_exacomp'), 'exabis_comp_top_name')
			. html_writer::div($cat?$cat->title:'', 'exabis_comp_top_value');
	
			$row->cells[] = $cell;
	
			$cell = new html_table_cell();
			$cell->text = html_writer::span(get_string('tab_competence_overview', 'block_exacomp'), 'exabis_comp_top_name')
			. html_writer::div(substr($schooltype, 0,1).($subject?$subject->numb:'').(($cat && isset($cat->sourceid))?".".$cat->sourceid:''), 'exabis_comp_top_value');
	
			$row->cells[] = $cell;
		}
		$rows[] = $row;
		$table->data = $rows;

		$content = html_writer::table($table);

		return $content;
	}

	public function print_competence_grid_legend() {
		$content = html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceyellow");
		$content .= ' '.get_string("selfevaluation","block_exacomp").' ';
		$content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceok");
		$content .= ' '.get_string("teacherevaluation","block_exacomp").' ';
		return $content;
	}
	public function print_competence_grid_reports_dropdown() {
		global $PAGE;
		
		$options = array();
		
		$options[BLOCK_EXACOMP_REPORT1] = get_string("report_competence","block_exacomp");
		$options[BLOCK_EXACOMP_REPORT2] = get_string("report_detailcompetence","block_exacomp");
		$options[BLOCK_EXACOMP_REPORT3] = get_string("report_examples","block_exacomp");
		
		$url = new block_exacomp\url($PAGE->url);
		$url->param("subjectid",optional_param("subjectid", 0, PARAM_INT));
		$url->param("studentid",optional_param("studentid", 0, PARAM_INT));
		
		return get_string('reports','block_exacomp') . ": " . html_writer::select($options, "exacomp_competence_grid_report", optional_param("report", BLOCK_EXACOMP_REPORT1, PARAM_INT), true, array("data-url"=>$url));
		 
	}
	public function print_competence_overview_LIS_student_topics($subs, &$row, &$columns, &$column_count, $scheme, $profoundness = false){
		global $USER, $COURSE;
		$supported = block_exacomp_get_supported_modules();
		foreach($subs as $topic){
			if(isset($topic->subs))
				$this->print_competence_overview_LIS_student_topics($topic->subs);

			if(isset($topic->descriptors)){
				foreach($topic->descriptors as $descriptor){
					$cell = new html_table_cell();
					$cell->attributes['class'] = 'exabis_comp_top_student';
					$cell->attributes['title'] = $descriptor->title;
					$cell->text = $columns[$column_count].html_writer::empty_tag('br');

					$columns[$column_count] = new stdClass();
					$columns[$column_count]->descriptor = $descriptor->id;

					if($scheme == 1)
						$cell->text .= "L:".$this->generate_checkbox('data', $descriptor->id, 'competencies', $USER, "teacher", $scheme, true)
						.html_writer::empty_tag('br')
						."S:".$this->generate_checkbox('data', $descriptor->id, 'competencies', $USER, "student", $scheme);
					else
						$cell->text .= 'L:'.$this->generate_select('data', $descriptor->id, 'competencies', $USER, "teacher", $scheme, true,$profoundness)
						.html_writer::empty_tag('br')
						."S:".$this->generate_select('data', $descriptor->id, 'competencies', $USER,"student", $scheme,false,$profoundness);

					//$activities = block_exacomp_get_activities($descriptor->id, $COURSE->id);
					$cm_mm = block_exacomp_get_course_module_association($COURSE->id);
					$course_mods = get_fast_modinfo($COURSE->id)->get_cms();

					if(isset($data->cm_mm->competencies[$descriptor->id])) {
						$activities_student = array();
						foreach($cm_mm->competencies[$descriptor->id] as $cmid)
							$activities_student[] = $course_mods[$cmid];
						if($activities_student && $stdicon = block_exacomp_get_icon_for_user($activities_student, $USER, $supported)){
							$cell->text .= html_writer::empty_tag('br')
							.html_writer::tag('span', $stdicon->img, array('title'=>$stdicon->text, 'class'=>'exabis-tooltip'));
						}
					}
					$row->cells[] = $cell;
					$column_count++;
				}
			}
		}
	}
public function print_competence_grid($niveaus, $skills, $topics, $data, $selection = array(), $courseid = 0,$studentid=0) {
		global $CFG, $DB;

		$global_scheme = \block_exacomp\global_config::get_scheme_id();
		$global_scheme_values = \block_exacomp\global_config::get_scheme_items();


		$headFlag = false;

		$context = context_course::instance($courseid);
		$role = block_exacomp_is_teacher($context) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT;
		$editmode = (($studentid == 0 || $studentid == BLOCK_EXACOMP_SHOW_STATISTIC) && $role == block_exacomp::ROLE_TEACHER) ? true : false;

		$table = new html_table();
		$table->attributes['class'] = 'competence_grid';
		$head = array();

		$schema = ($courseid == 0) ? 1 : block_exacomp_get_grading_scheme($courseid);
		$satisfied = ceil($schema/2);
		
		$profoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;

		$spanningNiveaus = $DB->get_records(block_exacomp::DB_NIVEAUS,array('span' => 1));
		//calculate the col span for spanning niveaus
		$spanningColspan = block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus);
		$report = optional_param('report', BLOCK_EXACOMP_REPORT1, PARAM_INT);
		
		$rows = array();

		foreach($data as $skillid => $skill) {

			if(isset($skills[$skillid])) {
				$row = new html_table_row();
				$cell1 = new html_table_cell();
				$cell1->text = html_writer::tag("span",html_writer::tag("span",$skills[$skillid],array('class'=>'rotated-text__inner-header')),array('class'=>'rotated-text-header'));
				$cell1->attributes['class'] = 'skill';
				$cell1->rowspan = count($skill)+1;
				$row->cells[] = $cell1;
				//
				$rows[] = $row;

				if(!$headFlag)
					$head[] = "";
			}

			if(!$headFlag) {
				$head[] = "";
				$head = array_merge($head,array_diff_key($niveaus, $spanningNiveaus));
				$table->head = $head;
				$headFlag = true;
			}

			foreach($skill as $topicid => $topic) {
				$row = new html_table_row();

				$cell2 = new html_table_cell();
			   
				$cell2->text = html_writer::tag("span",html_writer::tag("span",block_exacomp_get_topic_numbering( block_exacomp_get_topic_by_id($topicid))." ".$topics[$topicid],array('class'=>'rotated-text__inner')),array('class'=>'rotated-text'));
				$cell2->attributes['class'] = 'topic';
				$row->cells[] = $cell2;

				foreach($niveaus as $niveauid => $niveau) {
					if(isset($data[$skillid][$topicid][$niveauid])) {
						$cell = new html_table_cell();
						$compdiv = "";
						$allTeachercomps = true;
						$allStudentcomps = true;
						foreach($data[$skillid][$topicid][$niveauid] as $descriptor) {
							$compString = "";
							
							if(!isset($descriptor->visible))
								$descriptor->visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible', array('courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>0));
							
							// Check visibility
							$descriptor_used = block_exacomp_descriptor_used($courseid, $descriptor, ($studentid != BLOCK_EXACOMP_SHOW_STATISTIC) ? $studentid : 0);
							$visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, ($studentid != BLOCK_EXACOMP_SHOW_STATISTIC) ? $studentid : 0);
							$visible_css = block_exacomp_get_descriptor_visible_css($visible, $role);

							$text = block_exacomp_get_descriptor_numbering($descriptor)." ".$descriptor->title;
							if(array_key_exists($descriptor->topicid, $selection)) {
								$text = html_writer::link(new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid"=>$courseid,"subjectid"=>$topicid,"topicid"=>$descriptor->id,"studentid"=>$studentid)),$text,array("id" => "competence-grid-link-".$descriptor->id,"class" => ($visible) ? '' : 'deactivated'));
							}

							if(isset($descriptor->children) && count($descriptor->children) > 0 && !block_exacomp_is_altversion()) {
								$children = '<ul class="childdescriptors">';
								foreach($descriptor->children as $child)
									$children .= '<li>' . block_exacomp_get_descriptor_numbering($descriptor)." ".$child->title . '</li>';
								$children .= '</ul>';
							}
							$compString .= $text;

							if(isset($descriptor->children) && count($descriptor->children) > 0 && !block_exacomp_is_altversion())
								$compString .= $children;

							$cssClass = "content";
							if($descriptor->parentid > 0)
								$cssClass .= ' child';
							
							if(isset($descriptor->teachercomp) && $descriptor->teachercomp)
								$cssClass = "contentok";
							
							// Check visibility
							/*
							if(!$descriptor_used && array_key_exists($descriptor->topicid, $selection) ){
								if($editmode || ($descriptor->visible == 1 && $role == block_exacomp::ROLE_TEACHER)){
									$compString .= $this->print_visibility_icon_descriptor($visible, $descriptor->id);
								}
							} */
							$compdiv .= html_writer::tag('div', $compString,array('class'=>$cssClass));
							
							if($report != BLOCK_EXACOMP_REPORT1)	
							if(array_key_exists($descriptor->topicid, $selection) && $visible && $studentid != 0) {
								
								$compdiv .= html_writer::start_div('crosssubjects');
								$table_head = new html_table_row();
								$table_head->attributes['class'] = 'statistic_head';
								
								$scheme = block_exacomp_get_grading_scheme($courseid);
								$table_head->cells[] = new html_table_cell("");
								if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
									$table_head->cells[] = new html_table_cell("&Sigma;");
								for($i=0;$i<=$scheme;$i++)
									$table_head->cells[] = new html_table_cell(($global_scheme==0)?$i:$global_scheme_values[$i]);
								$table_head->cells[] = new html_table_cell("oB");
								$table_head->cells[] = new html_table_cell("iA");
								if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
									$table_head->cells[] = new html_table_cell("Abschluss");
										
								$crossubject_statistic = new html_table();
								$crossubject_statistic_rows = array();
								$crossubject_statistic_rows[] = $table_head;
								
								$crosssubjects = block_exacomp_get_cross_subjects_for_descriptor($courseid, $descriptor->id);
								$statistic_type = ($report == BLOCK_EXACOMP_REPORT2) ? BLOCK_EXACOMP_DESCRIPTOR_STATISTIC : BLOCK_EXACOMP_EXAMPLE_STATISTIC;
									
								foreach($crosssubjects as $crosssubject) {
									if($statistic_type == BLOCK_EXACOMP_DESCRIPTOR_STATISTIC)
										list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_descriptor_statistic_for_crosssubject($courseid, $crosssubject->id, $studentid);
									else
										list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_example_statistic_for_crosssubject($courseid, $crosssubject->id, $studentid);
										
									$table_entry = new html_table_row();
									$table_entry->cells[] = new html_table_cell(html_writer::link(new moodle_url("/blocks/exacomp/cross_subjects.php", array("courseid" => $courseid, "crosssubjid" => $crosssubject->id)), $crosssubject->title, array('title' => get_string('crosssubject','block_exacomp'))));
									if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
										$table_entry->cells[] = new html_table_cell($total);
									foreach($gradings as $key => $grading)
										$table_entry->cells[] = new html_table_cell($grading);
									$table_entry->cells[] = new html_table_cell($notEvaluated);
									$table_entry->cells[] = new html_table_cell($inWork);
									if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
										$table_entry->cells[] = new html_table_cell($totalGrade);
									
									$crossubject_statistic_rows[] = $table_entry;
								}
								if($statistic_type == BLOCK_EXACOMP_DESCRIPTOR_STATISTIC)
									list($total, $gradings, $notEvaluated, $inWork,$totalGrade) = block_exacomp_get_descriptor_statistic($courseid, $descriptor->id, $studentid);
								else
									list($total, $gradings, $notEvaluated, $inWork,$totalGrade, $notInWork) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptor->id, $studentid);
										
								$table_entry = new html_table_row();
								$table_entry->cells[] = new html_table_cell("LWL " . block_exacomp_get_descriptor_numbering($descriptor));
								if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
									$table_entry->cells[] = new html_table_cell($total);
								foreach($gradings as $key => $grading)
									$table_entry->cells[] = new html_table_cell($grading);
								$table_entry->cells[] = new html_table_cell($notEvaluated);
								$table_entry->cells[] = new html_table_cell($inWork);
								if($studentid != BLOCK_EXACOMP_SHOW_STATISTIC)
									$table_entry->cells[] = new html_table_cell($totalGrade);
									
								$crossubject_statistic_rows[] = $table_entry;
								
								$crossubject_statistic->data = $crossubject_statistic_rows;
								$compdiv .= html_writer::table($crossubject_statistic);
								$compdiv .= html_writer::end_div();
							}
						}

						// apply colspan for spanning niveaus
						if(array_key_exists($niveauid,$spanningNiveaus)) {
							$cell->colspan = $spanningColspan;
						}
						
						$cell->text = $compdiv;
						$row->cells[] = $cell;
						
						// do not print other cells for spanning niveaus
						if(array_key_exists($niveauid,$spanningNiveaus))
							break;
						
					} else {
						$printCell = true;
						if(array_key_exists($niveauid,$spanningNiveaus))
							 $printCell = false;
						if($printCell)
							foreach(array_keys($data[$skillid][$topicid]) as $nid) {
								if(array_key_exists($nid,$spanningNiveaus)) {
									$printCell = false;
									break;
								}
							}
						if($printCell)
							$row->cells[] = "";
					}
						
				}
				$rows[] = $row;
			}
			//$rows[] = $row;
		}
		$table->data = $rows;

		return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
	}
	public function print_competence_overview_form_start($selectedTopic=null, $selectedSubject=null, $studentid=null, $editmode=null){
		global $PAGE, $COURSE;
		$url_params = array();
		$url_params['action'] = 'save';
		if(isset($selectedTopic))
			$url_params['topicid'] = $selectedTopic->id;
		if(isset($selectedSubject))
			$url_params['subjectid'] = $selectedSubject->id;
		if(isset($studentid))
			$url_params['studentid'] = $studentid;
		if(isset($editmode))
			$url_params['editmode'] = $editmode;
		
		$url = new moodle_url($PAGE->url, $url_params);
		return html_writer::start_tag('form',array('id'=>'assign-competencies', "action" => $url, 'method'=>'post'));
	}
	public function print_competence_overview_LIS_student($subjects, $courseid, $showevaluation, $scheme, $examples){
		global $USER, $DB, $PAGE, $COURSE;

		$columns = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rows = array();
		$row = new html_table_row();
		$row->attributes['class'] = 'highlight';

		$cell = new html_table_cell();
		$cell->colspan = 4;
		$cell->text = html_writer::tag('h5', \block_exacomp\trans('de:Teilkompetenzen'), array('style'=>'float:right;'));

		$row->cells[] = $cell;

		$column_count = 0;
		//print header
		foreach($subjects as $subject){
			$this->print_competence_overview_LIS_student_topics($subject->subs, $row, $columns, $column_count, $scheme, block_exacomp_get_settings_by_course($courseid)->useprofoundness);
		}
		$rows[] = $row;

		//print subheader
		if(!empty($examples)){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = html_writer::tag('b', 'Lernmaterialien');
			$row->cells[] = $cell;
	
			$cell = new html_table_cell();
			$row->cells[] = $cell;
	
			$cell = new html_table_cell();
			$cell->text = 'In Arbeit';
			$row->cells[] = $cell;
	
			$cell = new html_table_cell();
			$cell->text = 'abgeschlossen';
			$row->cells[] = $cell;
	
			$cell = new html_table_cell();
			$cell->colspan = $column_count;
			$row->cells[] = $cell;
	
			$rows[] = $row;
		}
	//print examples
		foreach($examples as $example){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = $example->title;

			//$img = html_writer::img('pix/i_11x11.png', 'Beispiel');
			$img = html_writer::tag('img','',array('src'=>'pix/i_11x11.png','alt'=>'Beispiel'));
			
			if($task = block_exacomp_get_file_url($example, 'example_task'))
				$cell->text .= html_writer::link($task, $img, array('target'=>'_blank'));
			elseif(isset($example->externalurl))
			$cell->text .= html_writer::link($example->externalurl, $img);

			$row->cells[] = $cell;

			$cell = new html_table_cell();
			$cell->text = (isset($example->tax))?$example->tax:'';

			$row->cells[] = $cell;

			$exampleInfo = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array("exampleid" => $example->id, "studentid" => $USER->id, "courseid" => $COURSE->id));
			
			$cell = new html_table_cell();
			//$cell->text = html_writer::img('pix/subjects_topics.gif', "edit", array('onclick'=>'AssignVisibility('.$example->id."2".')', 'style'=>'cursor:pointer;'));
			$cell->text = html_writer::tag('img','',array('src'=>'pix/subjects_topics.gif', 'alt'=>"edit", 'onclick'=>'AssignVisibility('.$example->id."2".')', 'style'=>'cursor:pointer;'));
			
			$dates = (isset($exampleInfo->starttime) && isset($exampleInfo->endtime))?date("d.m.Y", $exampleInfo->starttime)
			." - ".date("d.m.Y", $exampleInfo->endtime):"";
			$div_1 = html_writer::div($dates, '', array('id'=>'exabis_assign_student_data'.$example->id."2"));

			$cell->text .= $div_1;

			$content = get_string('assignfrom','block_exacomp');
			$content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][starttime]', 'disabled',
					'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));
			$content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deletestart'=>1)),
					html_writer::tag('img','',array('src'=>'pix/x_11x11.png','alt'=>'delete')));
			$content .= html_writer::empty_tag('br');
			$content .= get_string('assignuntil','block_exacomp');
			$content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][endtime]', 'disabled',
					'value' => (isset($exampleInfo->endtime) ? date("Y-m-d",$exampleInfo->endtime) : null)));
			$content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deleteend'=>1)),
					html_writer::tag('img','',array('src'=>'pix/x_11x11.png','alt'=>'delete')));

			$div_2 = html_writer::div($content, 'exabis_assign_student', array('id'=>'exabis_assign_student'.$example->id."2"));
			$cell->text .= $div_2;

			$row->cells[] = $cell;

			$cell = new html_table_cell();
			$options = array();
			$options['self'] = get_string('assignmyself','block_exacomp');
			$options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
			$options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
			$options['teacher'] = get_string('assignteacher','block_exacomp');

			//$cell->text = html_writer::img('pix/subjects_topics.gif', 'edit', array('onclick'=>'AssignVisibility('.$example->id."1".')', 'style'=>'cursor:pointer;'));
			$cell->text = html_writer::tag('img','',array('src' => 'pix/subjects_topics.gif', 'alt'=>'edit', 'onclick'=>'AssignVisibility('.$example->id."1".')', 'style'=>'cursor:pointer;'));
			
			$content = $this->generate_checkbox('dataexamples', $example->id, 'examples', $USER, "student", $scheme)
			. html_writer::select($options, 'dataexamples[' . $example->id . '][' . $USER->id . '][studypartner]', (isset($exampleInfo->studypartner) ? $exampleInfo->studypartner : null), false);

			$div_2 = html_writer::div($content, 'exabis_assign_student', array('id'=>'exabis_assign_student'.$example->id."1"));
			$cell->text .= $div_2;

			$row->cells[] = $cell;

			for($i=0; $i<$column_count; $i++){
				$cell = new html_table_cell();

				if(isset($example->descriptors[$columns[$i]->descriptor])){
					if(isset($exampleInfo->teacher_evaluation) && $exampleInfo->teacher_evaluation>0){
						$cell->attributes['class'] = 'exabis_comp_teacher_assigned';
						$cell->text = '';
						if(isset($exampleInfo->student_evaluation) && $exampleInfo->student_evaluation>0)
							$cell->text = " S ";
						$cell->text = " L: ".$exampleInfo->teacher_evaluation;
					}
					elseif(isset($exampleInfo->student_evaluation) && $exampleInfo->student_evaluation>0){
						$cell->attributes['class'] = 'exabis_comp_student_assigned';
						$cell->text = " S";
					}elseif(isset($exampleInfo->starttime) && time() > $exampleInfo->starttime){
						$cell->attributes['class'] = 'exabis_comp_student_started';
						$cell->text = " X";
					}else{
						$cell->attributes['class'] = 'exabis_comp_student_not';
						$cell->text = " X";
					}
				}
					
				$row->cells[] = $cell;

			}
			$rows[] = $row;
		}

		$table->data = $rows;

		$submit = html_writer::div(html_writer::empty_tag('input', array('name'=>'btn_submit', 'type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

		$script_content = 'function AssignVisibility(id)
		{
		if(document.getElementById("exabis_assign_student"+id).style.display!="inherit"){
		document.getElementById("exabis_assign_student"+id).style.display = "inherit";
		document.getElementById("exabis_assign_student_data"+id).style.display ="none";
	}else {
	document.getElementById("exabis_assign_student"+id).style.display = "none";
	document.getElementById("exabis_assign_student_data"+id).style.display ="inherit";
	}

	}';
		$script = html_writer::tag('script', $script_content, array('type'=>'text/javascript'));
		$innerdiv = html_writer::div($script.html_writer::table($table).$submit, 'exabis_comp_comp_table');
		$div = html_writer::div($innerdiv, "exabis_competencies_lis", array("id"=>"exabis_competences_block"));
		return $div.html_writer::end_tag('form');
		//return html_writer::tag('form', $div, array('id'=>'assign-competencies', 'action'=>new moodle_url($PAGE->url, array('courseid'=>$courseid, 'action'=>'save')), 'method'=>'post'));
	}

	public function print_profoundness($subjects, $courseid, $students, $role) {
		$table = new html_table();
		$rows = array();
		$table->attributes['class'] = 'exabis_comp_comp';
		
		// 1st header row
		$headerrow = new html_table_row();
		
		$cell = new html_table_cell();
		$cell->rowspan = 2;
		$cell->colspan = 3;
		$cell->text = get_string('profoundness_description','block_exacomp');
		$headerrow->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_basic','block_exacomp');
		$cell->colspan = 2;
		$headerrow->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_extended','block_exacomp');
		$cell->colspan = 2;
		$headerrow->cells[] = $cell;
		
		$rows[] = $headerrow;

		// 2nd header row
		$headerrow = new html_table_row();
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_mainly','block_exacomp');
		$headerrow->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_entirely','block_exacomp');
		$headerrow->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_mainly','block_exacomp');
		$headerrow->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = get_string('profoundness_entirely','block_exacomp');
		$headerrow->cells[] = $cell;
		
		$rows[] = $headerrow;
		
		if(block_exacomp_exaportexists())
			$eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
		else
			$eportfolioitems = array();
		
		foreach($subjects as $subject) {
			if(!$subject->subs)
				continue;
			
			/* TOPICS */
			//for every topic
			$data = (object)array(
					'rowgroup' => &$rowgroup,
					'courseid' => $courseid,
					'showevaluation' => 0,
					'role' => $role,
					'scheme' => 2,
					'profoundness' => 1,
					'cm_mm' => block_exacomp_get_course_module_association($courseid),
					'eportfolioitems' => $eportfolioitems,
					'exaport_exists'=>block_exacomp_exaportexists(),
					'course_mods' => get_fast_modinfo($courseid)->get_cms(),
					'selected_topicid' => null,
					'supported_modules'=>block_exacomp_get_supported_modules(),
					'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors
			);
			$this->print_topics($rows, 0, $subject->subs, $data, $students, '', true);
			$table->data = $rows;
		}
		
		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),'', array('id'=>'exabis_save_button'));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));
		
		return $table_html.html_writer::end_tag('form');
	}
	public function print_competence_overview($subjects, $courseid, $students, $showevaluation, $role, $scheme = 1, $lis_singletopic = false, $crosssubjs = false, $crosssubjid = 0, $statistic = false) {
		global $PAGE, $additional_grading;

		$rowgroup = ($lis_singletopic) ? null : 0;
		//$rowgroup=0;
		$table = new html_table();
		$rows = array();
		$studentsColspan = $showevaluation ? 2 : 1;
		if($additional_grading && ($showevaluation || $role = block_exacomp::ROLE_TEACHER)) $studentsColspan++;
		
		$table->attributes['class'] = 'exabis_comp_comp';

		if(block_exacomp_exaportexists())
			$eportfolioitems = block_exacomp_get_eportfolioitem_association($students);
		else
			$eportfolioitems = array();

		/* SUBJECTS */
		$first = true;
		$course_subs = block_exacomp_get_subjects_by_course($courseid);
		
		foreach($subjects as $subject) {
			if(!$subject->subs)
				continue;
				
			if($first){
				//for every subject
				$subjectRow = new html_table_row();
				$subjectRow->attributes['class'] = 'highlight';
	
				//subject-title
				$title = new html_table_cell();
				$title->colspan = 2;
				
				if($crosssubjs)
					$title->text = html_writer::tag("b", get_string('comps_and_material', 'block_exacomp'));
				else
					$title->text = html_writer::tag("b", $subject->title);
	
				$subjectRow->cells[] = $title;
			}
			
			$nivCell = new html_table_cell();
			$nivCell->text = \block_exacomp\get_string('competence_grid_niveau');

			if($first)
				$subjectRow->cells[] = $nivCell;
				
			$studentsCount = 0;
			
			if(!$statistic){
				foreach($students as $student) {
					$studentCell = new html_table_cell();
					$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
	
					$studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
					$studentCell->colspan = $studentsColspan;
					$studentCell->text = fullname($student);
					if (!$this->is_print_mode() && block_exacomp_exastudexists() && ($info = \block_exastud\api::get_student_review_link_info_for_teacher($student->id))) {
						$studentCell->text .= ' <a href="'.$info->url.'" title="'.\block_exacomp\trans('de:Überfachliche Bewertung').'" onclick="window.open(this.href,this.target,\'width=880,height=660,scrollbars=yes\'); return false;">'.'<img src="pix/review_student.png" />'.'</a>';
					}
	
					if ($this->is_print_mode()) {
						// zeilenumbruch im namen beim drucken: nur erstes leerzeichen durch <br> ersetzen
						$studentCell->text = preg_replace('!\s!', '<br />', $studentCell->text, 1);
					}
					
					if($first)
						$subjectRow->cells[] = $studentCell;
				}
			}else{
				$groupCell = new html_table_cell();
				$groupCell->text = get_string('groupsize', 'block_exacomp').count($students);

				if($first)
					$subjectRow->cells[] = $groupCell;
			}
			if($first)
				$rows[] = $subjectRow;

			if($showevaluation) {
				$studentsCount = 0;

				$evaluationRow = new html_table_row();
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = 3;
				$evaluationRow->cells[] = $emptyCell;

				if(!$statistic){
					foreach($students as $student) {
						$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
	
						$firstCol = new html_table_cell();
						$firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
						$secCol = new html_table_cell();
						$secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
	
						if($role == block_exacomp::ROLE_TEACHER) {
							$firstCol->text = get_string('studentshortcut','block_exacomp');
							$secCol->text = get_string('teachershortcut','block_exacomp');
							if($additional_grading) $secCol->colspan = 2;
						} else {
							$firstCol->text = get_string('teachershortcut','block_exacomp');
							if($additional_grading) $firstCol->colspan = 2;
							$secCol->text = get_string('studentshortcut','block_exacomp');
						}
	
						$evaluationRow->cells[] = $firstCol;
						$evaluationRow->cells[] = $secCol;
					}
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
					'profoundness' => block_exacomp_get_settings_by_course($courseid)->useprofoundness,
					'cm_mm' => block_exacomp_get_course_module_association($courseid),
					'eportfolioitems' => $eportfolioitems,
					'exaport_exists'=>block_exacomp_exaportexists(),
					'course_mods' => get_fast_modinfo($courseid)->get_cms(),
					'selected_topicid' => null,
					'supported_modules'=>block_exacomp_get_supported_modules(),
					'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors
			);
			$this->print_topics($rows, 0, $subject->subs, $data, $students, '', false, $this->is_edit_mode(), $statistic, $crosssubjs, $crosssubjid);
				
			
			$first = false;
		}
		//total evaluation crosssub row
		if($crosssubjs && !$this->is_edit_mode() && !$statistic){
			$student = array_values($students)[0];
			$studentid = $student->id;
	
			$totalRow = new html_table_row();
			$totalRow->attributes['class'] = 'highlight';
			$firstCol = new html_table_cell();
			$firstCol->text = get_string('total', 'block_exacomp');
			$totalRow->cells[] = $firstCol;
			
			$totalRow->cells[] = new html_table_cell();
			
			$nivCell = new html_table_cell();
			$nivCell->text = "";
			$totalRow->cells[] = $nivCell;
			
			$studentsCount = 0;
			foreach($students as $student){
				
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
				
				if($showevaluation){
					$evaluation = ($role == block_exacomp::ROLE_TEACHER) ? 'student' : 'teacher';
					
					$studentevalCol = new html_table_cell();
					$studentevalCol->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
					
					if($scheme == 1) {
						$studentevalCol->text = $this->generate_checkbox('datacrosssubs', $crosssubjid, 'crosssubs', $student, $evaluation, $scheme, true);
					}else{
						$studentevalCol->text = $this->generate_select('datacrosssubs', $crosssubjid, 'crosssubs', $student, $evaluation, $scheme, true);
					}
					
					if($role == block_exacomp::ROLE_STUDENT)
						$studentevalCol->colspan = 2;
						
					$totalRow->cells[] = $studentevalCol;
				}
			
				$evaluation = ($role == block_exacomp::ROLE_TEACHER) ? 'teacher' : 'student';
				
				$teacherevalCol = new html_table_cell();
				$teacherevalCol->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
				if($scheme == 1) {
					$teacherevalCol->text = $this->generate_checkbox('datacrosssubs', $crosssubjid, 'crosssubs', $student, $evaluation, $scheme, false);
				}else{
					$teacherevalCol->text = $this->generate_select('datacrosssubs', $crosssubjid, 'crosssubs', $student, $evaluation, $scheme, false);
				}
				
				if($role == block_exacomp::ROLE_TEACHER)
					$teacherevalCol->colspan = 2;
					
				$totalRow->cells[] = $teacherevalCol;
			}
			
			$rows[] = $totalRow;
		}
		
		$table->data = $rows;
		
		if ($this->is_print_mode()) {
			// private function
			$cell_width = function($row, $cell, $width) use ($rows) {
				$rows[$row]->cells[$cell]->attributes['width'] = $width.'%';
				// test print cell size
				//$rows[$row]->cells[$cell]->text = $width.' '.$rows[$row]->cells[$cell]->text;
			};
			// set table cell sizes for print mode
			$cnt = count($students);
			$cell_width(0, 0, 100-5-$cnt*12.5);
			$cell_width(0, 1, 5);
			for ($i = 0; $i < $cnt; $i++) {
				$cell_width(0, 2+$i, 12.5);
			}
			$cell_width(2, 0, 8);
			$cell_width(2, 1, 100-5-$cnt*12.5-8);
			$cell_width(2, 2, 5);
			for ($i = 0; $i < $cnt*3; $i++) {
				$cell_width(2, 3+$i, 12.5/3);
			}
		}
		
		$table_html = html_writer::table($table);
		
		if(count($rows) == 0 && $crosssubjs) {
			$table_html .= html_writer::div(get_string('add_content_to_crosssub','block_exacomp',(new moodle_url('assign_competencies.php',array('courseid'=>$courseid,'editmode'=>1)))->__toString()),
					"alert alert-warning");
		}
			
		$new = optional_param('new', false, PARAM_BOOL);
		
		if (!$this->is_print_mode()) {
			if($crosssubjs && $role == block_exacomp::ROLE_TEACHER && !$students) {
				$buttons = html_writer::tag("input", "", array("id"=>"btn_submit", "name" => "btn_submit", "type" => "submit", "value" => get_string("save_crosssub", "block_exacomp")));
				if (!$new) {
					$buttons .= html_writer::tag("input", "", array("id"=>"save_as_draft", "name" => "save_as_draft", "type" => "button", "value" => get_string("save_as_draft", "block_exacomp"))).
								html_writer::tag("input", "", array("id"=>"share_crosssub", "name"=>"share_crosssub", "type"=>"button", "value"=>get_string("share_crosssub", "block_exacomp"), 'exa-type'=>'iframe-popup', 'exa-url'=>'select_students.php?courseid='.$courseid.'&crosssubjid='.$crosssubjid)).
								html_writer::tag("input", "", array("id"=>"delete_crosssub", "name"=>"delete_crosssub", "type"=>"button", "value"=>get_string("delete_crosssub", "block_exacomp"), 'message'=>get_string('confirm_delete', 'block_exacomp')), array('id'=>'exabis_save_button'));
				}
			} else {
				$buttons = html_writer::tag("input", "", array("id"=>"btn_submit", "name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp")));
			}

			$table_html .= html_writer::div($buttons,'', array('id'=>'exabis_save_button'));
		
			$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));
			$table_html .= html_writer::end_tag('form');
		}
		
		return $table_html;
	}

	public function print_topics(&$rows, $level, $topics, &$data, $students, $rowgroup_class = '', $profoundness = false, $editmode = false, $statistic = false, $crosssubjs = false, $crosssubjid=0) {
		global $additional_grading;
		$topicparam = optional_param('topicid', 0, PARAM_INT);
		$padding = $level * 20 + 12;
		$evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";

		foreach($topics as $topic) {
			
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
			$studentsCount = 0;
			$studentsColspan = 1;

			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!block_exacomp_is_altversion()));

			if ($hasSubs && !is_null($data->rowgroup)) {
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
			$outputidCell->text = (block_exacomp_is_altversion()) ? block_exacomp_get_topic_numbering($topic->id) : '';
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			if(block_exacomp_is_altversion() && $topicparam == SHOW_ALL_NIVEAUS)
				$outputnameCell->text = html_writer::div($outputname,"desctitle");
			else
				$outputnameCell->text = html_writer::div((($outputid) ? ($outputid.': ') : '').$outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			$nivCell = new html_table_cell();
			$nivCell->text = "";

			$topicRow->cells[] = $nivCell;
			
			if(!$statistic){
				foreach($students as $student) {
					$studentCell = new html_table_cell();
					$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
					$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
					$studentCell->colspan = (!$profoundness) ? $studentsColspan : 4;
	
					$additional_grading_cell = new html_table_cell();
					$additional_grading_cell->text = "";
					$additional_grading_cell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
							
							 
					if((isset($data->cm_mm->topics[$topic->id]) || $data->showalldescriptors) && !$profoundness) {
						// SHOW EVALUATION
						if($data->showevaluation) {
							$studentCellEvaluation = new html_table_cell();
							$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
						}
	
						/*
						 * if scheme == 1: print checkbox
						* if scheme != 1, role = student, version = LIS
						*/
						if($data->scheme == 1 || ($data->scheme != 1 && $data->role == block_exacomp::ROLE_STUDENT && block_exacomp_is_altversion())) {
							if($data->showevaluation)
								$studentCellEvaluation->text = $this->generate_checkbox("datatopics", $topic->id,
										'topics', $student, ($evaluation == "teacher") ? "student" : "teacher",
										$data->scheme, true);
	
							$studentCell->text = $this->generate_checkbox("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme);
						}
						/*
						 * if scheme != 1, !version: print select
						* if scheme != 1, version = LIS, role = teacher
						*/
						elseif(!block_exacomp_is_altversion() || (block_exacomp_is_altversion() && $data->role == block_exacomp::ROLE_TEACHER)) {
							if($data->showevaluation)
								$studentCellEvaluation->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
	
							$studentCell->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme, false, $data->profoundness);
						}
	
	
						// ICONS
						if(isset($data->cm_mm->topics[$topic->id])) {
							//get CM instances
							$cm_temp = array();
							foreach($data->cm_mm->topics[$topic->id] as $cmid)
								$cm_temp[] = $data->course_mods[$cmid];
	
							$icon = block_exacomp_get_icon_for_user($cm_temp, $student, $data->supported_modules);
							$studentCell->text .= '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
						}
	
						// TIPP
						if(block_exacomp_set_tipp($topic->id, $student, 'activities_topics', $data->scheme)){
							$icon_img = html_writer::empty_tag('img', array('src'=>"pix/info.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
							$string = block_exacomp_get_tipp_string($topic->id, $student, $data->scheme, 'activities_topics', TYPE_TOPIC);
							$studentCell->text .= html_writer::span($icon_img, 'exabis-tooltip', array('title'=>$string));
	
						}
						if($data->showevaluation)
							$topicRow->cells[] = $studentCellEvaluation;
					}else{
						if($data->showevaluation)
							$topicRow->cells[] = new html_table_cell();
					}
					
					if($additional_grading && $data->showevaluation && $data->role == block_exacomp::ROLE_STUDENT)
						$topicRow->cells[] = $additional_grading_cell;
			
					$topicRow->cells[] = $studentCell;
							
					if($additional_grading && $data->role == block_exacomp::ROLE_TEACHER)
						$topicRow->cells[] = $additional_grading_cell;
					
				   
				}
			}else{
				$statCell = new html_table_cell();
				$statCell->text = "";

				$topicRow->cells[] = $statCell;
			}
			//do not display topic level for version
			// TODO: refactor, delete whole topic row logic?
			if(block_exacomp_is_altversion()) {
				$level--;				
				// $topicRow->style = "display:none;";
			} else {
				$rows[] = $topicRow;
			}

			if (!empty($topic->descriptors)) {
				$this->print_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic, false, true, $crosssubjid);
				$this->print_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic, true, true, $crosssubjid);
			}

			if (!empty($topic->subs)) {
				$this->print_topics($rows, $level+1, $topic->subs, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $crosssubjid);
			}

			if($editmode && !$crosssubjs) {
				// kompetenz hinzufuegen (nicht bei themen)
				$niveauid = optional_param('niveauid', SHOW_ALL_NIVEAUS, PARAM_INT);
				//do not set niveauid for new descriptor if "show all" niveaus is selected
				if($niveauid == SHOW_ALL_NIVEAUS)
					$niveauid = 0;
				
				$own_additionRow = new html_table_row();
				$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe highlight ' . $sub_rowgroup_class;
				
				$own_additionRow->cells[] = new html_table_cell();
				
				$cell = new html_table_cell();
				$cell->style = "padding-left: ". $padding."px";
				$cell->text = html_writer::empty_tag('input', array('exa-type'=>'new-descriptor', 'type'=>'textfield', 'placeholder'=>\block_exacomp\trans('de:[Neue Kompetenz]'), 'topicid'=>$topic->id, 'niveauid'=>$niveauid));
				$own_additionRow->cells[] = $cell;
				$own_additionRow->cells[] = new html_table_cell();
				$rows[] = $own_additionRow;
			}
		}
	}

	function print_descriptors(&$rows, $level, $descriptors, &$data, $students, $rowgroup_class, $profoundness = false, $editmode=false, $statistic=false, $custom_created_descriptors=false, $parent = false, $crosssubjid = 0) {
		global $NG_PAGE, $PAGE, $USER, $COURSE, $CFG, $DB, $additional_grading;

		$evaluation = ($data->role == block_exacomp::ROLE_TEACHER) ? "teacher" : "student";
		
		foreach($descriptors as $descriptor) {
			if (!$editmode) {
				if ($custom_created_descriptors) {
					continue;
				}
			} else {
				if(!$custom_created_descriptors && $descriptor->source != block_exacomp::CUSTOM_CREATED_DESCRIPTOR || ($custom_created_descriptors && $descriptor->source == block_exacomp::CUSTOM_CREATED_DESCRIPTOR)){
				} else {
					continue;
				}
			}
			$descriptor_in_crosssubj = ($crosssubjid <= 0) || array_key_exists($descriptor->id, block_exacomp_get_cross_subject_descriptors($crosssubjid));
		  
			//visibility
			//visible if 
			//		- visible in whole course 
			//	and - visible for specific student
			
			$one_student = false;
			$studentid = 0;
			if(!$editmode && count($students)==1){
				$studentid = array_values($students)[0]->id;
				$one_student = true;
			}
			$descriptor_used = block_exacomp_descriptor_used($data->courseid, $descriptor, $studentid);
			
			$visible = block_exacomp_is_descriptor_visible($data->courseid, $descriptor, $studentid, ($one_student||$data->role==block_exacomp::ROLE_STUDENT) );
			//echo $descriptor->visible . " / " . $visible . " <br/> ";
				
			if($data->role == block_exacomp::ROLE_TEACHER || $visible){
				$visible_css = block_exacomp_get_descriptor_visible_css($visible, $data->role);
				
				$checkboxname = "data";
				list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor, false, $parent);
				$studentsCount = 0;
	
				$padding = ($level) * 20 + 4;
	
				//if($descriptor->parentid > 0)
					//$padding += 20;
	
				if($descriptor->examples || (!is_null($data->rowgroup) && $parent)) {
					$data->rowgroup++;
					$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class.$visible_css;
					$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
				} else {
					$this_rowgroup_class = $rowgroup_class.$visible_css;
					$sub_rowgroup_class = '';
					
				}
				$descriptorRow = new html_table_row();
				
				
				$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
				if($parent)
					$descriptorRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';
					
				
				$exampleuploadCell = new html_table_cell();
				if($this->is_edit_mode() && !$this->is_print_mode() && $data->role == block_exacomp::ROLE_TEACHER && !$profoundness && $descriptor_in_crosssubj) {
					$exampleuploadCell->text = html_writer::link(
							new moodle_url('/blocks/exacomp/example_upload.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id,"topicid"=>$descriptor->topicid)),
							html_writer::empty_tag('img', array('src'=>'pix/upload_12x12.png', 'alt'=>'upload')),
							array("target" => "_blank", 'exa-type' => 'iframe-popup'));
				}
	
				$exampleuploadCell->text .= $outputid . block_exacomp_get_descriptor_numbering($descriptor);
	
				$descriptorRow->cells[] = $exampleuploadCell;
	
				$titleCell = new html_table_cell();
				
				if(($descriptor->examples || $descriptor->children || ($parent && $editmode)) && !is_null($data->rowgroup))
					$titleCell->attributes['class'] = 'rowgroup-arrow';
				$titleCell->style = "padding-left: ".$padding."px";
				$titleCell->text = html_writer::div(html_writer::tag('span', $outputname, array('title'=>get_string('import_source', 'block_exacomp').$this->print_source_info($descriptor->source))));
				
				//$titleCell->attributes['title'] = $this->print_statistic_table($data->courseid, $students, $descriptor, true, $data->scheme);
				 
				// EDIT MODE BUTTONS 
				if ($editmode){
					
					$titleCell->text .= html_writer::link(
							new moodle_url('/blocks/exacomp/select_crosssubjects.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id)),
							$this->pix_icon("i/withsubcat", get_string("crosssubject","block_exacomp")),
							array("target" => "_blank", 'exa-type' => 'iframe-popup'));
				}
				//if hidden in course, cannot be shown to one student
				if(!$this->is_print_mode() && !$descriptor_used){
					if($editmode || ($one_student && $descriptor->visible && $data->role == block_exacomp::ROLE_TEACHER)){
						$titleCell->text .= $this->print_visibility_icon_descriptor($visible, $descriptor->id);
					}
					if($editmode && $custom_created_descriptors){
						$titleCell->text .= html_writer::link('descriptor.php?courseid='.$COURSE->id.'&id='.$descriptor->id, $this->pix_icon("i/edit", get_string("edit")), array('exa-type' => 'iframe-popup', 'target'=>'_blank'));
						$titleCell->text .= html_writer::link("", $this->pix_icon("t/delete", get_string("delete")), array("onclick" => "if (confirm('" . get_string('delete_confirmation_descr','block_exacomp') . "')) block_exacomp.delete_descriptor(".$descriptor->id."); return false;"));
					}
				}
				/*if ($editmode) {
					$titleCell->text .= ' '.$this->print_source_info($descriptor->source);
				}*/
				
				$descriptorRow->cells[] = $titleCell;
				
				$nivCell = new html_table_cell();
				
				$nivText = [];
				foreach($descriptor->categories as $cat){
					$nivText[] = $cat->title;
				}
				$nivCell->text = join(' ', $nivText);
				$descriptorRow->cells[] = $nivCell;
						
				
				$visible_student = $visible;
				if(!$statistic){
					foreach($students as $student) {
						$icontext = "";
						//check reviewerid for teacher
						if($data->role == block_exacomp::ROLE_TEACHER)
							$reviewerid = $DB->get_field(block_exacomp::DB_COMPETENCIES,"reviewerid",array("userid" => $student->id, "compid" => $descriptor->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => block_exacomp::TYPE_DESCRIPTOR));
						
						//check visibility for every student in overview
						
						if(!$one_student && !$editmode)
							$visible_student = block_exacomp_is_descriptor_visible($data->courseid, $descriptor, $student->id);
								
						$studentCell = new html_table_cell();
						$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
						$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
		
						// SHOW EVALUATION
						if($data->showevaluation) {
							$studentCellEvaluation = new html_table_cell();
							$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
						}
		
						// ICONS
						if(isset($data->cm_mm->competencies[$descriptor->id])) {
							//get CM instances
							$cm_temp = array();
							foreach($data->cm_mm->competencies[$descriptor->id] as $cmid)
								$cm_temp[] = $data->course_mods[$cmid];
		
							$icon = block_exacomp_get_icon_for_user($cm_temp, $student, $data->supported_modules);
							$icontext = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
						}
						//EPORTFOLIOITEMS
						if($data->exaport_exists){
							if(isset($data->eportfolioitems[$student->id]) && isset($data->eportfolioitems[$student->id]->competencies[$descriptor->id])){
								$shared = false;
								$li_items = '';
								foreach($data->eportfolioitems[$student->id]->competencies[$descriptor->id]->items as $item){
									$li_item = $item->name;
									if($item->shared){
										$li_item .= get_string('eportitem_shared', 'block_exacomp');
										$shared = true;
									}
									else
										$li_item .=  get_string('eportitem_notshared', 'block_exacomp');
						
									$li_items .= html_writer::tag('li', $li_item);
								}
								$first_param = 'id';
								$second_param = $item->viewid;
								if($item->useextern){
									$second_param = $item->hash;
									$first_param = 'hash';
								}
								// link to view if only 1 item, else link to shared_views list
								if(count($data->eportfolioitems[$student->id]->competencies[$descriptor->id]->items) == 1)
									$link = new moodle_url('/blocks/exaport/shared_view.php', array('courseid'=>$COURSE->id, 'access'=>$first_param.'/'.$item->owner.'-'.$second_param));
								else
									$link = new moodle_url('/blocks/exaport/shared_views.php',array('courseid'=>$COURSE->id));
								
								if($shared)
									$img = html_writer::link($link, html_writer::empty_tag("img", array("src" => "pix/folder_shared.png","alt" => '')));
								//$img = html_writer::empty_tag("img", array("src" => "pix/folder_shared.png","alt" => ''));
								else
									$img = html_writer::empty_tag("img", array("src" => "pix/folder_notshared.png","alt" => ''));
									
								$text =  get_string('eportitems', 'block_exacomp').html_writer::tag('ul', $li_items);
						
								$eportfoliotext = '<span title="'.$text.'" class="exabis-tooltip">'.$img.'</span>';
							}else{
								$eportfoliotext = '';
							}
						}
						// TIPP
						if(block_exacomp_set_tipp($descriptor->id, $student, 'activities_competencies', $data->scheme)){
							$icon_img = html_writer::empty_tag('img', array('src'=>"pix/info.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
							$string = block_exacomp_get_tipp_string($descriptor->id, $student, $data->scheme, 'activities_competencies', TYPE_DESCRIPTOR);
							$tipptext = html_writer::span($icon_img, 'exabis-tooltip', array('title'=>$string));
						}
						
						if(!$profoundness) {
							/*
							 * if scheme == 1: print checkbox
							*/
							if($data->scheme == 1) {
								if($data->showevaluation)
									$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
			
								$studentCell->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme, ($visible_student)?false:true, null, ($data->role == block_exacomp::ROLE_TEACHER) ? $reviewerid : null);
							}
							/*
							 * if scheme != 1: print select
							*/
							else {
								if($data->showevaluation)
									$studentCellEvaluation->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
			
								$studentCell->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme, !$visible_student, $data->profoundness, ($data->role == block_exacomp::ROLE_TEACHER) ? $reviewerid : null);
							}
							
							
							// ICONS
							if(isset($icontext)) 
								$studentCell->text .= $icontext;
							
							//EPORTFOLIOITEMS
							if(isset($eportfoliotext))
								$studentCell->text .= $eportfoliotext;
							
							// TIPP
							if(isset($tipptext))
								$studentCell->text .= $tipptext;
			
							if($data->showevaluation)
								$descriptorRow->cells[] = $studentCellEvaluation;
								
							$additional_grading_cell = new html_table_cell();
							$params = array('name'=>'add-grading-'.$student->id.'-'.$descriptor->id, 'type'=>'text', 
								'maxlength'=>3, 'class'=>'percent-rating-text', 
								'value'=>(isset($student->competencies->teacher_additional_grading[$descriptor->id]) && 
									$student->competencies->teacher_additional_grading[$descriptor->id] != null)?
										$student->competencies->teacher_additional_grading[$descriptor->id]:"",
								'descrid'=>$descriptor->id, 'studentid'=>$student->id);
								
							if(!$visible_student || $data->role == block_exacomp::ROLE_STUDENT)
								$params['disabled'] = 'disabled';
							
							$additional_grading_cell->text = $parent ? ' <span class="percent-rating">'.html_writer::empty_tag('input', $params).'</span>' : "";
							$additional_grading_cell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;	
								
							if($additional_grading && $data->showevaluation && $data->role == block_exacomp::ROLE_STUDENT)
								$descriptorRow->cells[] = $additional_grading_cell;
			
							$descriptorRow->cells[] = $studentCell;
							
							if($additional_grading && $data->role == block_exacomp::ROLE_TEACHER)
								$descriptorRow->cells[] = $additional_grading_cell;
						} else {
							// ICONS
							if(isset($icontext))
								$titleCell->text .= $icontext;
								
							//EPORTFOLIOITEMS
							if(isset($eportfoliotext))
								$titleCell->text .= $eportfoliotext;
								
							// TIPP
							if(isset($tipptext))
								$titleCell->text .= $tipptext;
							
							$cell1 = new html_table_cell();
							$cell2 = new html_table_cell();
							$disabledCell = new html_table_cell();
							
							$cell1->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 1);
							$cell2->text = $this->generate_checkbox_profoundness($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, 2);
							$disabledCell->text = html_writer::checkbox("disabled", "",false,null,array("disabled"=>"disabled"));
							$disabledCell->attributes['class'] = 'disabled';
							
							if($descriptor->profoundness == 0) {
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
				}else{
					
					$statCell = new html_table_cell();
					$statCell->text = $this->print_statistic_table($data->courseid, $students, $descriptor, true, $data->scheme);
			
					$descriptorRow->cells[] = $statCell;
				}
	
				$rows[] = $descriptorRow;
	
				$checkboxname = "dataexamples";
	
				foreach($descriptor->examples as $example) {
					$example_used = block_exacomp_example_used($data->courseid, $example, $studentid);
				
					$visible_example = block_exacomp_is_example_visible($data->courseid, $example, $studentid);
					
					if ($data->role != block_exacomp::ROLE_TEACHER && !$visible_example) {
						// do not display
						continue;
					}
					
					$visible_example_css = block_exacomp_get_example_visible_css($visible_example, $data->role);
					
					$studentsCount = 0;
					$exampleRow = new html_table_row();
					$exampleRow->attributes['class'] = 'exabis_comp_aufgabe block_exacomp_example ' . $sub_rowgroup_class.$visible_example_css;
					$exampleRow->cells[] = new html_table_cell();
	
					$titleCell = new html_table_cell();
					$titleCell->style = "padding-left: ". ($padding + 20 )."px";
					$title = '';
					if ($author = $example->get_author()) $title .= get_string('author', 'repository').": ".$author."\n";
					$title .= strip_tags($example->description);
					$titleCell->text = html_writer::div(html_writer::tag('span', $example->title, array('title'=>$title)));
					
				   if(!$statistic && !$this->is_print_mode()){
						
						if ($editmode) {
							$titleCell->text .= '<span style="padding-right: 15px;" class="todo-change-stylesheet-icons">';
							
							if (block_exacomp_is_admin($COURSE->id) || (isset($example->creatorid) && $example->creatorid == $USER->id)) {
								$titleCell->text .= html_writer::link(
									new moodle_url('/blocks/exacomp/example_upload.php',array("courseid"=>$data->courseid,"descrid"=>$descriptor->id,"topicid"=>$descriptor->topicid,"exampleid"=>$example->id)),
									$this->pix_icon("i/edit", get_string("edit")),
									array("target" => "_blank", 'exa-type' => 'iframe-popup'));
							}

							if(!$example_used)
								$titleCell->text .= html_writer::link(new \block_exacomp\url('example_upload.php', ['delete' => $example->id, 'courseid'=>$COURSE->id, 'returnurl' => $NG_PAGE->url->out_as_local_url(false)]),
									$this->pix_icon("t/delete", get_string("delete")),
									array("onclick" => "return confirm('" . get_string('delete_confirmation','block_exacomp') . "')"));
							
							//print up & down icons
							$titleCell->text .= html_writer::link("#", $this->pix_icon("t/up", get_string('up')), array("id" => "example-up", "exampleid" => $example->id, "descrid" => $descriptor->id));
							$titleCell->text .= html_writer::link("#", $this->pix_icon("t/down", get_string('down')), array("id" => "example-down", "exampleid" => $example->id, "descrid" => $descriptor->id));

							$titleCell->text .= '</span>';
						}
						
						if (!$example_used && ($data->role == block_exacomp::ROLE_TEACHER) && ($editmode || (!$editmode && $one_student && block_exacomp_is_example_visible($data->courseid, $example, 0)))) {
							$titleCell->text .= $this->print_visibility_icon_example($visible_example, $example->id);
						/*
						} else {
							$titleCell->text .= '<span style="display: inline-block; width: 16px; margin-right: 4px;">&nbsp;</span>';
						*/
						}
						
						if ($url = block_exacomp_get_file_url($example, 'example_task')) {
							$titleCell->text .= html_writer::link($url, $this->local_pix_icon("filesearch.png", get_string('preview')), array("target" => "_blank"));
						}
						
						
						if($example->externalurl){
							$titleCell->text .= html_writer::link($example->externalurl, $this->local_pix_icon("globesearch.png", $example->externalurl),array("target" => "_blank"));
						}elseif($example->externaltask){
							$titleCell->text .= html_writer::link($example->externaltask, $this->local_pix_icon("globesearch.png", $example->externaltask),array("target" => "_blank"));
						}
						
						if ($url = block_exacomp_get_file_url($example, 'example_solution')) {
							$titleCell->text .= $this->print_example_solution_icon($url);
						}
						
						if ($this->is_print_mode()) {
							// no icons in print mode
						} else {
							if(!$example->externalurl && !$example->externaltask && !block_exacomp_get_file_url($example, 'example_solution') && !block_exacomp_get_file_url($example, 'example_task') && $example->description) 
								$titleCell->text .= $this->pix_icon("i/preview", $example->description);
							
							if($data->role == block_exacomp::ROLE_STUDENT) {
								$titleCell->text .= $this->print_schedule_icon($example->id, $USER->id, $data->courseid);
								
								$titleCell->text .= $this->print_submission_icon($data->courseid, $example->id, $USER->id);
									
								$titleCell->text .= $this->print_competence_association_icon($example->id, $data->courseid, false);
								
							} else if($data->role == block_exacomp::ROLE_TEACHER) {
								$studentid = block_exacomp_get_studentid(true);
								
								//auch für alle schüler auf wochenplan legen
								if(!$this->is_edit_mode()){
									$titleCell->text .= $this->print_schedule_icon($example->id, ($studentid)?$studentid:BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $data->courseid);
									
									if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
										$titleCell->text .= html_writer::link("#",
											html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'), 'title'=> get_string('pre_planning_storage', 'block_exacomp'))),
											array('exa-type' => 'add-example-to-schedule', 'exampleid' => $example->id, 'studentid' => 0, 'courseid' => $data->courseid));
		
									}
								}
								$titleCell->text .= $this->print_competence_association_icon($example->id, $data->courseid, $editmode);
							
							}
						}
						$titleCell->text .= '</span>';
						
						/*if ($editmode) {
							$titleCell->text .= ' '.$this->print_source_info($descriptor->source);
						}*/
						
						$titleCell->attributes['title'] = '';
						
						if(!empty($example->description))
							$titleCell->attributes['title'] .= $example->description;
						if(!empty($example->timeframe))
							$titleCell->attributes['title'] .= '&#013;' . $example->timeframe;
						if(!empty($example->tips))
							$titleCell->attributes['title'] .= '&#013;' . $example->tips;
						
					}
					$exampleRow->cells[] = $titleCell;
	
					$nivCell = new html_table_cell();
					
					$nivText = [];
					foreach($example->taxonomies as $tax){
						$nivText[] = $tax->title;
					}
					$nivCell->text = join(' ', $nivText);
					$exampleRow->cells[] = $nivCell;
					
					$visible_student_example = $visible_example;
					if(!$statistic){
						foreach($students as $student) {
							
							if(!$one_student && !$editmode)
								$visible_student_example = block_exacomp_is_example_visible($data->courseid, $example, $student->id);
						
							$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
							$studentCell = new html_table_cell();
							$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
		
							// SHOW EVALUATION
							if($data->showevaluation) {
								$studentCellEvaluation = new html_table_cell();
								$studentCellEvaluation->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
							}
		
							$studentCell->text = html_writer::empty_tag("input",array("type" => "hidden", "value" => 0, "name" => $checkboxname . "-" . $example->id . "-" . $student->id . "-" . (($evaluation == "teacher") ? "teacher" : "student")));
							/*
							 * if scheme == 1: print checkbox
							* if scheme != 1, role = student, version = LIS
							*/
							if($data->scheme == 1) {
								if($data->showevaluation)
									$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
									
								if($data->role == block_exacomp::ROLE_STUDENT) {
									$studentCell->text .= get_string('assigndone','block_exacomp');
									$studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, !$visible_student_example);
		
									//$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
								}
								else {
									$studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, !$visible_student_example);
								}
							}
							/*
							 * if scheme != 1, !version: print select
							* if scheme != 1, version = LIS, role = teacher
							*/
							else {
								if($data->showevaluation)
									$studentCellEvaluation->text = $this->generate_select($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->profoundness);
		
								$studentCell->text .= $this->generate_select($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme, !$visible_student_example, $data->profoundness);
		
								//if($data->role == block_exacomp::ROLE_STUDENT)
									//$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
							}

							if($data->showevaluation) {
								if ($data->role == block_exacomp::ROLE_TEACHER) {
									$studentCellEvaluation->text .= $this->print_submission_icon($data->courseid, $example->id, $student->id);
									$studentCellEvaluation->text .= $this->print_resubmission_icon($example->id, $student->id, $data->courseid);
								}

								$exampleRow->cells[] = $studentCellEvaluation;
							}
							
							$additional_grading_cell = new html_table_cell();
							$additional_grading_cell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
							//TODO: optimize get_field query for each student for each example
							$student_additional_grading = $DB->get_field(block_exacomp::DB_EXAMPLEEVAL, 'additionalinfo', array('studentid'=>$student->id,'exampleid'=>$example->id,'courseid'=>$data->courseid));
							// hide percent sign when text is empty
							// $student_additional_grading can also be a zero!
							if ($student_additional_grading !== false /* row not found */ && $student_additional_grading !== null /* not set */) {
								$student_additional_grading .= ' %'; 
							}
							if ($this->is_print_mode()) {
								$additional_grading_cell->text = $student_additional_grading; 
							} else {
								$additional_grading_cell->text = html_writer::empty_tag('input', array(
									'class'=>'percent-rating', 'type'=>'text', 'value'=>($student_additional_grading !== false) ? $student_additional_grading : null,
									'id'=>'additionalinfo-'.$student->id.'-'.$example->id.'-'.$descriptor->id,'exampleid'=>$example->id,'studentid'=>$student->id)
									+ (($visible_student_example && $data->role == block_exacomp::ROLE_TEACHER) ? [] : ['disabled'=>'disabled']));
							}
							
							if($additional_grading && $data->showevaluation && $data->role == block_exacomp::ROLE_STUDENT)
								$exampleRow->cells[] = $additional_grading_cell;
			
							 $exampleRow->cells[] = $studentCell;
							
							if($additional_grading && $data->role == block_exacomp::ROLE_TEACHER)
								$exampleRow->cells[] = $additional_grading_cell;   
						}
					}else{ 
						$statCell = new html_table_cell();
						$statCell->text = $this->print_statistic_table($data->courseid, $students, $example, false, $data->scheme);
						
		
						$exampleRow->cells[] = $statCell;
					}
					$rows[] = $exampleRow;
				}
				if (!empty($descriptor->children)) {
					$this->print_descriptors($rows, $level+1, $descriptor->children, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic);
				}
				//schulische ergänzungen und neue teilkompetenz
				if($editmode && $parent) {
					
					$own_additionRow = new html_table_row();
					$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class;
					$own_additionRow->cells[] = new html_table_cell();
					
					$cell = new html_table_cell();
					$cell->text = get_string('own_additions', 'block_exacomp');
					$own_additionRow->cells[] = $cell;
					
					$own_additionRow->cells[] = new html_table_cell();
					
					$rows[] = $own_additionRow;
					
					// is this was a bug? it's printed twice?
					// no, first print the imported descriptors, then print the user created ones
					$this->print_descriptors($rows, $level+1, $descriptor->children, $data, $students, $sub_rowgroup_class,$profoundness, $editmode, $statistic, true);
					
					$own_additionRow = new html_table_row();
					$own_additionRow->attributes['class'] = 'exabis_comp_aufgabe ' . $sub_rowgroup_class;
					
					$own_additionRow->cells[] = new html_table_cell();
					
					if($descriptor_in_crosssubj){
						$cell = new html_table_cell();
						$cell->style = "padding-left: ". ($padding + 20 )."px";
						$cell->text = html_writer::empty_tag('input', array('exa-type'=>'new-descriptor', 'name'=>'new_comp'.$descriptor->id, 'type'=>'textfield', 'placeholder'=>\block_exacomp\trans('de:[Neue Teilkompetenz]'), 'parentid'=>$descriptor->id));
						$own_additionRow->cells[] = $cell;
					}
					$own_additionRow->cells[] = new html_table_cell();
					$rows[] = $own_additionRow;
				}	
			}
		}
	}

	public function print_preview_icon($alt = null) {
		if($alt == null)
			$alt = get_string("preview");
		
		return html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/preview.png'), 'alt'=>$alt));
	}
	/*
	public function print_source_color($sourceid) {
		global $DB;
		
		if (!$sourceid) {
			return;
		} elseif ($sourceid == block_exacomp::EXAMPLE_SOURCE_TEACHER) {
			$color = '#FFFF00';
		} else {
			$cnt = $DB->get_field_sql("SELECT COUNT(*) FROM {block_exacompdatasources} WHERE id < ?", array($sourceid));
			$colors = array('#FF0000', '#00FF00', '#0000FF', '#FF00FF', '#00FFFF', '#800000', '#008000', '#000080', '#808000', '#800080', '#008080', '#C0C0C0', '#808080', '#9999FF', '#993366', '#FFFFCC', '#CCFFFF', '#660066', '#FF8080', '#0066CC', '#CCCCFF', '#000080');
			$color = $colors[$cnt%count($colors)];
		}

		return '<span style="border: 1px solid black; background: '.$color.'; margin-right: 5px;">&nbsp;&nbsp;&nbsp;</span>';
	}
	*/
	
	public function print_source_info($sourceid) {
		global $DB;
		$info="";
		if ($sourceid == block_exacomp::EXAMPLE_SOURCE_TEACHER) {
			$info = get_string('local', 'block_exacomp');
		} elseif ($sourceid && $source = $DB->get_record("block_exacompdatasources", array('id'=>$sourceid))) {
			$info = $source->name;
		}
		if(empty($info)) {
			$info = get_string('unknown_src', 'block_exacomp')." ($sourceid)";
		}
		   
		return $info;
	}

	public function print_sources() {
		global $courseid;
		
		$sources = block_exacomp_data::get_all_used_sources();
		
		if (!$sources) return;
		
		$ret = '<div>';
		foreach ($sources as $source) {
			$name = ($source->name ? $source->name : $source->source);
			$ret .= $this->box("Importierte Daten von \"$name\" ".html_writer::link(new moodle_url('/blocks/exacomp/source_delete.php', array('courseid'=>$courseid, 'action'=>'select', 'source'=>$source->id)), 
					"löschen"));
		}
		$ret .= '</div>';
		return $ret;
	}

	public function print_submission_icon($courseid, $exampleid, $studentid = 0) {
		global $CFG;
		
		if ($this->is_print_mode()) {
			return '';
		}
			
		$context = context_course::instance($courseid);
		$isTeacher = block_exacomp_is_teacher($context);
		
		if(!$isTeacher) {
			   //if student, check for existing item
			$itemExists = block_exacomp_get_current_item_for_example($studentid, $exampleid);
				
			return html_writer::link(
						new moodle_url('/blocks/exacomp/example_submission.php',array("courseid"=>$courseid,"exampleid"=>$exampleid)),
						$this->pix_icon((!$itemExists) ? "i/manual_item" : "i/reload", get_string('submission','block_exacomp')),
						array('exa-type' => 'iframe-popup'));
		}
		elseif($studentid) {
			//works only if exaport is installed
			if ($url = block_exacomp_get_viewurl_for_example($studentid,$exampleid)) {
				return html_writer::link($url,
					$this->pix_icon("i/manual_item", get_string("submission","block_exacomp"), null, array('style' => 'margin: 0 0 0 5px;')),
					array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
			}else{
				return "";
			}
		}
	}
	public function print_resubmission_icon($exampleid, $studentid, $courseid) {
		global $DB;
		
		$exameval = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL,array('exampleid'=>$exampleid,'studentid'=>$studentid,'courseid'=>$courseid));
		if(!$exameval || $exameval->resubmission)
			return "";
		else
			return html_writer::link(
				"#",
				$this->pix_icon("i/reload", get_string("allow_resubmission","block_exacomp")),
				array('exa-type' => 'allow-resubmission', 'exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
	}
	public function print_schedule_icon($exampleid, $studentid, $courseid) {
		return html_writer::link(
							"#",
							$this->pix_icon("e/insert_date", get_string("weekly_schedule","block_exacomp")),
							array('exa-type' => 'add-example-to-schedule', 'exampleid' => $exampleid, 'studentid' => $studentid, 'courseid' => $courseid));
	}
	public function print_competence_association_icon($exampleid, $courseid, $editmode) {
		return html_writer::link(
				new moodle_url('/blocks/exacomp/competence_associations.php',array("courseid"=>$courseid,"exampleid"=>$exampleid, "editmode"=>($editmode)?1:0)),
				 $this->pix_icon("e/insert_edit_link", get_string('competence_associations','block_exacomp')), array('exa-type' => 'iframe-popup'));
	}
	public function print_example_solution_icon($solution) {
		return html_writer::link($solution, $this->pix_icon("e/fullpage", get_string('solution','block_exacomp')) ,array("target" => "_blank"));
	}
	public function print_visibility_icon_descriptor($visible, $descriptorid) {
		if($visible)
			$icon = $this->pix_icon("i/hide", get_string("hide"));
		else
			$icon = $this->pix_icon("i/show", get_string("show"));
			
		return html_writer::link("", $icon, array('name' => 'hide-descriptor','descrid' => $descriptorid, 'id' => 'hide-descriptor', 'state' => ($visible) ? '-' : '+',
				'showurl' => $this->pix_url("i/hide"), 'hideurl' => $this->pix_url("i/show")
		));
		
	}
	public function print_visibility_icon_example($visible, $exampleid) {
		if($visible)
			$icon = $this->pix_icon("i/hide", get_string("hide"));
		else
			$icon = $this->pix_icon("i/show", get_string("show"));
			
		return html_writer::link("", $icon, array('name' => 'hide-example','exampleid' => $exampleid, 'id' => 'hide-example', 'state' => ($visible) ? '-' : '+',
				'showurl' => $this->pix_url("i/hide"), 'hideurl' => $this->pix_url("i/show")
		));
		
	}
	private function print_student_example_evaluation_form($exampleid, $studentid, $courseid) {
		global $DB;
		$exampleInfo = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array("exampleid" => $exampleid, "studentid" => $studentid, "courseid" => $courseid));
		$options = array();
		$options['self'] = get_string('assignmyself','block_exacomp');
		$options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
		$options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
		$options['teacher'] = get_string('assignteacher','block_exacomp');

		$content = html_writer::select($options, 'dataexamples-' . $exampleid . '-' . $studentid . '-studypartner', (isset($exampleInfo->studypartner) ? $exampleInfo->studypartner : null), false);

		$content .= get_string('assignfrom','block_exacomp');
		$content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples-' . $exampleid . '-' . $studentid . '-starttime', 'disabled',
				'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));

		$content .= get_string('assignuntil','block_exacomp');
		$content .= html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples-' . $exampleid . '-' . $studentid . '-endtime', 'disabled',
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
			$content .= html_writer::link('',
					($i*STUDENTS_PER_COLUMN+1).'-'.min($students, ($i+1)*STUDENTS_PER_COLUMN),
					array('class' => 'colgroup-button', 'exa-groupid'=>$i));
		}
		$content .= " " . html_writer::link('',
				get_string('allstudents','block_exacomp'),
				array('class' => 'colgroup-button colgroup-button-all', 'exa-groupid'=>-1));
		
		global $COURSE;
		if(block_exacomp_get_settings_by_course($COURSE->id)->nostudents) {
			$content .= " " . html_writer::link('',
				get_string('nostudents','block_exacomp'),
				array('class' => 'colgroup-button colgroup-button-no', 'exa-groupid'=>-2));
		}
		return html_writer::div($content,'spaltenbrowser');
	}
	public function print_student_evaluation($showevaluation, $isTeacher=true,$topic = SHOW_ALL_NIVEAUS,$subject=0, $studentid=0) {
		global $COURSE;

		$link = new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1"),'subjectid'=>$subject,'topicid'=>$topic, 'studentid'=>$studentid));
		$evaluation = $this->box_start();
		$evaluation .= get_string('overview','block_exacomp');
		$evaluation .= html_writer::empty_tag("br");
		if($isTeacher)	$evaluation .= ($showevaluation) ? get_string('hideevaluation','block_exacomp',$link->__toString()) : get_string('showevaluation','block_exacomp',$link->__toString());
		else $evaluation .= ($showevaluation) ? get_string('hideevaluation_student','block_exacomp',$link->__toString()) : get_string('showevaluation_student','block_exacomp',$link->__toString());

		$evaluation .= $this->box_end();

		return $evaluation;
	}
	public function print_overview_legend($teacher) {
		$legend = "";
		
		$legend .= html_writer::tag("img", "", array("src" => "pix/list_12x11.png", "alt" => get_string('legend_activities','block_exacomp')));
		$legend .= ' '.get_string('legend_activities','block_exacomp') . " - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/folder_fill_12x12.png", "alt" => get_string('legend_eportfolio','block_exacomp')));
		$legend .= ' '.get_string('legend_eportfolio','block_exacomp') . " - ";

		$legend .= html_writer::tag("img", "", array("src" => "pix/x_11x11.png", "alt" => get_string('legend_notask','block_exacomp')));
		$legend .= ' '.get_string('legend_notask','block_exacomp');

		if($teacher) {
			$legend .= " - ";
			$legend .= html_writer::tag("img", "", array("src" => "pix/upload_12x12.png", "alt" => get_string('legend_upload','block_exacomp')));
			$legend .= ' '.get_string('legend_upload','block_exacomp');
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
	public function generate_checkbox($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null, $reviewerid = null) {
		global $USER;
		
		$attributes = array();
		if($disabled)
			$attributes["disabled"] = "disabled";
		if($reviewerid && $reviewerid != $USER->id)
			$attributes["reviewerid"] = $reviewerid;
		
		$content = html_writer::checkbox(
				((isset($activityid)) ? 
						$name . '-' .$compid .'-' . $student->id .'-' . $activityid . '-' . $evaluation
						: $name . '-' . $compid . '-' . $student->id . '-' . $evaluation),
				$scheme,
				(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme/2), null,
				$attributes);
				
		return $content;
	}
	public function generate_checkbox_old($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null) {
		return html_writer::checkbox(
				((isset($activityid)) ?
						$name . '[' .$compid .'][' . $student->id .'][' . $activityid . '][' . $evaluation . ']'
						: $name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']'),
				$scheme,
				(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] >= ceil($scheme/2), null,
				(!$disabled) ? null : array("disabled"=>"disabled"));
	}
	public function generate_checkbox_profoundness($name, $compid, $type, $student, $evaluation, $scheme) {
		return html_writer::checkbox($name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']',
				$scheme,
				(isset($student->{$type}->{$evaluation}[$compid])) && $student->{$type}->{$evaluation}[$compid] == $scheme, null);
	}
	/**
	 * Used to generate a checkbox for ticking activities topics and competencies
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
	public function generate_checkbox_activities($name, $compid, $activityid, $type, $student, $evaluation, $scheme, $disabled = false) {
		return html_writer::checkbox(
				$name . '[' .$compid .'][' . $student->id .'][' . $activityid . '][' . $evaluation . ']', $scheme,
				(isset($student->{$type}->activities[$activityid]->{$evaluation}[$compid])) && $student->{$type}->activities[$activityid]->{$evaluation}[$compid] >= ceil($scheme/2),
				null, (!$disabled) ? null : array("disabled"=>"disabled"));
	}
	/**
	 * Used to generate a select for activities topics & competencies
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
	public function generate_select_activities($name, $compid, $activityid, $type, $student, $evaluation, $scheme, $disabled = false, $profoundness = false) {
		$options = array();
		for($i=0;$i<=$scheme;$i++)
			$options[] = (!$profoundness) ? $i : get_string('profoundness_'.$i,'block_exacomp');

		return html_writer::select(
				$options,
				$name . '[' . $compid . '][' . $student->id . '][' . $activityid . '][' . $evaluation . ']',
				(isset($student->{$type}->activities[$activityid]->{$evaluation}[$compid])) ? $student->{$type}->activities[$activityid]->{$evaluation}[$compid] : 0,
				false,(!$disabled) ? null : array("disabled"=>"disabled"));
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
	public function generate_select($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $profoundness = false, $reviewerid = null) {
		global $USER;

		// TODO: diese $scheme brauchen wir nicht mehr? einfach $options = $scheme_values?

		if(strcmp($evaluation, 'teacher')==0){
			$scheme_values = \block_exacomp\global_config::get_scheme_items();
			$options[-1] = ' ';
			for($i=0;$i<=$scheme;$i++) {
				$options[$i] = $scheme_values[$i];
			}
		}else{
			$scheme_values = \block_exacomp\global_config::get_student_scheme_items();

			$options[0] = '';
			$stars = '*';
			for($i=1; $i<=$scheme; $i++){
				$options[$i] = $scheme_values[$i];
			}
		}
		
		if ($this->is_print_mode()) {
			// in print mode return the text itself, no select
			$value = (isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : '';
			return !empty($options[$value]) ? $options[$value] : $value;
		}
		
		$attributes = array();
		if($disabled)
			$attributes["disabled"] = "disabled";
		if($reviewerid && $reviewerid != $USER->id)
			$attributes["reviewerid"] = $reviewerid;
		
		$attributes['exa-compid'] = $compid;
		$attributes['exa-userid'] = $student->id;
		$attributes['exa-evaluation'] = $evaluation;
		 
		return html_writer::select(
				$options,
				$name . '-' . $compid . '-' . $student->id . '-' . $evaluation,
				(isset($student->{$type}->{$evaluation}[$compid])) ? $student->{$type}->{$evaluation}[$compid] : -1,
				true,$attributes);
	}

	public function print_edit_config($data, $courseid, $fromimport=0){
		$header = html_writer::tag('p', $data->headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rows = array();

		$temp = false;
		foreach($data->levels as $levelstruct){
			if($levelstruct->level->source > 1 && $temp == false){
				// print table header for first source
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
			$cell->text = html_writer::tag('b', $levelstruct->level->title).' ('.$this->print_source_info($levelstruct->level->source).')';

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
		$innerdiv = html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

		$table->data = $rows;


		$div = html_writer::div(html_writer::tag('form', html_writer::table($table).$hiddenaction.$innerdiv, array('action'=>'edit_config.php?courseid='.$courseid.'&fromimport='.$fromimport, 'method'=>'post')), 'exabis_competencies_lis');


		$content = html_writer::tag("div", $header.$div, array("id"=>"exabis_competences_block"));

		return $content;
	}
	
	/**
	 * NOTICE: after adding new fields here, they also need to be added in course backup/restore and block_exacomp_get_settings_by_course() 
	 * @param unknown $settings
	 * @param unknown $courseid
	 * @param unknown $headertext
	 */
	public function print_edit_course($settings, $courseid, $headertext){
		global $DB;

		$global_scheme = \block_exacomp\global_config::get_scheme_id();

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$input_grading = "";
		if($global_scheme == 0)
			$input_grading = get_string('grading_scheme', 'block_exacomp').": &nbsp"
			.html_writer::empty_tag('input', array('type'=>'text', 'size'=>2, 'name'=>'grading', 'value'=>block_exacomp_get_grading_scheme($courseid)))
			.html_writer::empty_tag('br');

		$input_activities = html_writer::checkbox('uses_activities', 1, $settings->uses_activities == 1, get_string('uses_activities', 'block_exacomp'))
		.html_writer::empty_tag('br');

		$input_descriptors = html_writer::checkbox('show_all_descriptors',1,$settings->show_all_descriptors == 1, get_string('show_all_descriptors', 'block_exacomp'),($settings->uses_activities != 1) ? array("disabled" => "disabled") :  array())
		.html_writer::empty_tag('br');

		$input_examples = html_writer::checkbox('show_all_examples', 1, $settings->show_all_examples == 1, get_string('show_all_examples', 'block_exacomp'))
		.html_writer::empty_tag('br');

		$input_nostudents = html_writer::checkbox('nostudents', 1, $settings->nostudents==1, get_string('usenostudents', 'block_exacomp'))
		.html_writer::empty_tag('br');
		
		$alltax = array(SHOW_ALL_TAXONOMIES => get_string('show_all_taxonomies','block_exacomp'));
		$taxonomies = $DB->get_records_menu('block_exacomptaxonomies',null,'sorting','id,title');
		$taxonomies = $alltax + $taxonomies;
		$input_taxonomies = html_writer::empty_tag('br').html_writer::select($taxonomies, 'filteredtaxonomies[]',$settings->filteredtaxonomies,false,array('multiple'=>'multiple'));
		$input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save', 'admin')));

		$hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save_coursesettings'));

		$div = html_writer::div(html_writer::tag('form',
				$input_grading.$input_activities.$input_descriptors.$input_examples.$hiddenaction.$input_nostudents.$input_taxonomies.$input_submit,
				array('action'=>'edit_course.php?courseid='.$courseid, 'method'=>'post')), 'block_excomp_center');

		$content = html_writer::tag("div",$header.$div, array("id"=>"exabis_competences_block"));
			
		return $content;
	}

	public function print_my_badges($badges, $onlygained=false){
		$content = "";
		if($badges->issued){
			$content .= html_writer::tag('h4', get_string('my_badges', 'block_exacomp'));
			foreach ($badges->issued as $badge){
				$context = context_course::instance($badge->courseid);
				$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
				$img = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
				$innerdiv = html_writer::div($badge->name);
				$div = html_writer::div($img.$innerdiv, '', array('style'=>'padding:10px;'));
				$content .= $div;
			}

		}
		if(!$onlygained){
			if($badges->pending){
				$content .= html_writer::tag('h2', get_string('pendingbadges', 'block_exacomp'));
				foreach($badges->pending as $badge){
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
		}
		return html_writer::div($content, 'exacomp_profile_badges');
	}
	public function print_head_view_examples($sort, $show_all_examples, $url, $context){
		$content = html_writer::start_tag('script', array('type'=>'text/javascript', 'src'=>'javascript/wz_tooltip.js'));
		$content .= html_writer::end_tag('script');
		$content .= html_writer::start_tag('script', array('type'=>'text/javascript', 'src'=>'javascript/simpletreemenu.js'));
		$content .= html_writer::end_tag('script');
		$text_link1 = ($sort=="desc") ? html_writer::tag('b', get_string("subject", "block_exacomp")) : get_string("subject", "block_exacomp");
		$text_link2 = ($sort=="tax") ? html_writer::tag('b', get_string("taxonomies", "block_exacomp")) : get_string("taxonomies", "block_exacomp");
		$content .= get_string('sorting', 'block_exacomp')
		.html_writer::link($url.'&sort=desc', $text_link1)." "
		.html_writer::link($url.'&sort=tax', $text_link2);

		if(block_exacomp_is_teacher($context) || block_exacomp_is_admin($context)){
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
						$text = str_replace("'","",$text);
						$text = str_replace("\n"," ",$text);
						$text = str_replace("\r"," ",$text);
						$text = str_replace(":",":",$text);
							
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
		
		if($url = block_exacomp_get_file_url($example, 'example_task')) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'width'=>16, 'height'=>16));
			$icon .= html_writer::link($url, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('task_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		} 
		if($url = block_exacomp_get_file_url($example, 'example_solution')) {
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pdf solution.gif'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($url, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('solution_example', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->externaltask) {
			$example->externaltask = str_replace('&amp;','&',$example->externaltask);
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("task_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->externaltask, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->externalurl) {
			$example->externalurl = str_replace('&amp;','&',$example->externalurl);
			$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link.png'), 'alt'=>get_string("assigned_example", "block_exacomp"), 'height'=>16, 'width'=>16));
			$icon .= html_writer::link($example->externalurl, $img,
					array('target'=>'_blank', 'onmouseover'=>'Tip(\''.get_string('extern_task', 'block_exacomp').'\')', 'onmouseout'=>'UnTip()')).' ';
		}
		if($example->completefile) {
			$example->completefile = str_replace('&amp;','&',$example->completefile);
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
	public function print_courseselection($schooltypes, $topics_activ, $headertext){
		global $PAGE;

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rowgroup = 0;
		$rows = array();
		foreach($schooltypes as $schooltype){
			
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp highlight';
	
			$cell = new html_table_cell();
			$cell->text = html_writer::div(html_writer::tag('b', $schooltype->title).' ('.$this->print_source_info($schooltype->source).')');
			$cell->attributes['class'] = 'rowgroup-arrow';
					
			$cell->colspan = 3;
			$row->cells[] = $cell;
			
			$rows[] = $row;
					
			foreach($schooltype->subs as $subject){
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
				$cell->text = html_writer::div(html_writer::span($subject->title, 'rowgroup-arrow-highlight').
						// wenn different source than parent
						($subject->source != $schooltype->source ? ' ('.$this->print_source_info($subject->source).')' : ''));
				$cell->attributes['class'] = 'rowgroup-arrow rowgroup-arrow-styled';
				
				$cell->colspan = 2;
				$row->cells[] = $cell;
				
				$selectAllCell = new html_table_cell();
				$selectAllCell->text = html_writer::tag("a", \block_exacomp\get_string('selectallornone', 'form'),array("class" => "selectall"));
				$row->cells[] = $selectAllCell;

				$rows[] = $row;
				$this->print_topics_courseselection($rows, 0, $subject->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
				
			}
		}
		
		$table->data = $rows;


		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));
		$table_html .= html_writer::tag("input", "", array("name" => "action", "type" => "hidden", "value" => 'save'));
		
		return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url, "id" => "course-selection"));
	}
	public function print_descriptor_selection_export(){
		global $PAGE;
		
		$headertext = "Bitte wählen";
		$topics_activ = array();

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp rowgroup';
		$rowgroup = 0;
		$rows = array();
		
		$subjects = \block_exacomp\subject::get_objects();
		
		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp highlight rowgroup-level-0';
			
			$cell = new html_table_cell();
			$cell->text = html_writer::div('<input type="checkbox" name="subjects['.$subject->id.']" value="'.$subject->id.'" />'.html_writer::tag('b', $subject->title));
			$cell->attributes['class'] = 'rowgroup-arrow';
			$row->cells[] = $cell;
			$rows[] = $row;
			
			foreach ($subject->topics as $topic) {
				$padding = 20;
				
				$row = new html_table_row();
				$row->attributes['class'] = 'exabis_comp_teilcomp rowgroup-level-1';
				
				$cell = new html_table_cell();
				$cell->attributes['class'] = 'rowgroup-arrow';
				$cell->style = "padding-left: ".$padding."px";
				$cell->text = html_writer::div('<input type="checkbox" name="topics['.$topic->id.']" value="'.$topic->id.'" ">'.$topic->numbering.' '.$topic->title,"desctitle");
				$row->cells[] = $cell;
				
				$rows[] = $row;
				
				foreach($topic->descriptors as $descriptor){
					
					$padding = 40;
				
					$row = new html_table_row();
					$row->attributes['class'] = 'rowgroup-level-2';
					
					$cell = new html_table_cell();
					$cell->attributes['class'] = 'rowgroup-arrow';
					$cell->style = "padding-left: ".$padding."px";
					$cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title,"desctitle");
					$row->cells[] = $cell;
					
					$rows[] = $row;
					
					// child descriptors
					foreach($descriptor->children as $descriptor){
						
						$padding = 60;
					
						$row = new html_table_row();
						$row->attributes['class'] = 'rowgroup-level-3';
						
						$cell = new html_table_cell();
						$cell->attributes['class'] = 'rowgroup-arrow';
						$cell->style = "padding-left: ".$padding."px";
						$cell->text = html_writer::div('<input type="checkbox" name="descriptors['.$descriptor->id.']" value="'.$descriptor->id.'" />'.$descriptor->numbering.' '.$descriptor->title,"desctitle");
						$row->cells[] = $cell;
						
						$rows[] = $row;
					}
				}
			}
		}
		
		$table->data = $rows;


		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>'Exportieren')), '', array('id'=>'exabis_save_button'));

		return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action'=>'export_selected')), "id" => "course-selection"));
	}

	public function print_descriptor_selection_source_delete($source, $subjects){
		global $PAGE;
		
		$headertext = "Bitte wählen";
		$topics_activ = array();

		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp rowgroup';
		$rowgroup = 0;
		$rows = array();
		
		foreach ($subjects as $subject) {
			$row = new html_table_row();
			$row->attributes['class'] = 'exabis_comp_teilcomp highlight rowgroup-level-0';
			
			$cell = new html_table_cell();
			$cell->text = html_writer::div('<input type="checkbox" exa-name="subjects" value="'.$subject->id.'"'.(!$subject->can_delete?' disabled="disabled"':'').' />'.
					html_writer::tag('b', $subject->title));
			$cell->attributes['class'] = 'rowgroup-arrow';
			$row->cells[] = $cell;
			$rows[] = $row;
			
			foreach ($subject->topics as $topic) {
				$padding = 20;
				
				$row = new html_table_row();
				$row->attributes['class'] = 'exabis_comp_teilcomp rowgroup-level-1';
				
				$cell = new html_table_cell();
				$cell->attributes['class'] = 'rowgroup-arrow';
				$cell->style = "padding-left: ".$padding."px";
				$cell->text = html_writer::div('<input type="checkbox" exa-name="topics" value="'.$topic->id.'"'.(!$topic->can_delete?' disabled="disabled"':'').' />'.
						$topic->numbering.' '.$topic->title,"desctitle");
				$row->cells[] = $cell;
				
				$rows[] = $row;
				
				foreach($topic->descriptors as $descriptor){
					
					$padding = 40;
				
					$row = new html_table_row();
					$row->attributes['class'] = 'rowgroup-level-2';
					
					$cell = new html_table_cell();
					$cell->attributes['class'] = 'rowgroup-arrow';
					$cell->style = "padding-left: ".$padding."px";
					$cell->text = html_writer::div('<input type="checkbox" exa-name="descriptors" value="'.$descriptor->id.'"'.(!$descriptor->can_delete?' disabled="disabled"':'').' />'.
							$descriptor->numbering.' '.$descriptor->title,"desctitle");
					$row->cells[] = $cell;
					
					$rows[] = $row;
					
					// child descriptors
					foreach($descriptor->children as $child_descriptor){
						$padding = 60;
					
						$row = new html_table_row();
						$row->attributes['class'] = 'rowgroup-level-3';
						
						$cell = new html_table_cell();
						$cell->attributes['class'] = 'rowgroup-arrow';
						$cell->style = "padding-left: ".$padding."px";
						$cell->text = html_writer::div('<input type="checkbox" exa-name="descriptors" value="'.$child_descriptor->id.'"'.(!$child_descriptor->can_delete?' disabled="disabled"':'').' />'.
								$child_descriptor->numbering.' '.$child_descriptor->title,"desctitle");
						$row->cells[] = $cell;
						
						$rows[] = $row;

						// examples
						foreach($child_descriptor->examples as $example){
							$padding = 80;
						
							$row = new html_table_row();
							$row->attributes['class'] = 'rowgroup-level-4';
							
							$cell = new html_table_cell();
							$cell->attributes['class'] = 'rowgroup-arrow';
							$cell->style = "padding-left: ".$padding."px";
							$cell->text = html_writer::div('<input type="checkbox" exa-name="examples" value="'.$example->id.'"'.(!$example->can_delete?' disabled="disabled"':'').' />'.
									$example->numbering.' '.$example->title,"desctitle");
							$row->cells[] = $cell;
							
							$rows[] = $row;
						}
					}

					// examples
					foreach($descriptor->examples as $example){
						$padding = 60;
					
						$row = new html_table_row();
						$row->attributes['class'] = 'rowgroup-level-3';
						
						$cell = new html_table_cell();
						$cell->attributes['class'] = 'rowgroup-arrow';
						$cell->style = "padding-left: ".$padding."px";
						$cell->text = html_writer::div('<input type="checkbox" exa-name="examples" value="'.$example->id.'"'.(!$example->can_delete?' disabled="disabled"':'').' />'.
								$example->numbering.' '.$example->title,"desctitle");
						$row->cells[] = $cell;
						
						$rows[] = $row;
					}
				}
			}
		}
		
		$table->data = $rows;


		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>'Löschen')), '', array('id'=>'exabis_save_button'));

		return html_writer::tag("form", $header.$table_html, array("method" => "post", "action" => $PAGE->url->out(false, array('action'=>'delete_selected')), "id" => "exa-selector"));
	}
	
	public function print_topics_courseselection(&$rows, $level, $topics, &$rowgroup, $rowgroup_class = '', $topics_activ){
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
			//$topicRow->attributes['class'] = 'exabis_comp_teilcomp ' . $this_rowgroup_class . ' highlight';
			$topicRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
			$outputidCell = new html_table_cell();
			$outputidCell->text = $outputid;
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			$cell = new html_table_cell();
			$cell->text = html_writer::checkbox('topics['.$topic->id.']', $topic->id, !empty($topics_activ[$topic->id]), '', array('class'=>'topiccheckbox-'.$rowgroup));
			$topicRow->cells[] = $cell;

			$rows[] = $topicRow;

			if (!empty($topic->subs)) {
				$this->print_topics_courseselection($rows, $level+1, $topic->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
			}
		}
	}
	public function print_activity_legend($headertext){
		$header = html_writer::tag('p', $headertext).html_writer::empty_tag('br');

		return $header.html_writer::tag('p', get_string("explaineditactivities_subjects", "block_exacomp")).html_writer::empty_tag('br');

	}
	public function print_activity_footer($niveaus, $modules, $selected_niveaus=array(), $selected_modules=array()){
		global $PAGE;
		$content = '';

		$form_content = '';
		if(!empty($niveaus) && isset($niveaus)){
			$selected = '';
			if(empty($selected_niveaus) || in_array('0', $selected_niveaus))
				$selected = ' selected';
				
			$options = html_writer::tag('option'.$selected, get_string('all_niveaus', 'block_exacomp'), array('value'=>0));
			$has_niveaus = false;
			foreach($niveaus as $niveau){
				if($niveau){
					$selected = '';
					if(!empty($selected_niveaus) && in_array($niveau->id, $selected_niveaus))
						$selected = ' selected';
					$has_niveaus = true;
					$options .= html_writer::tag('option'.$selected, $niveau->title, array('value'=>$niveau->id));
				}
			}
			$select = html_writer::tag('select multiple', $options, array('name'=>'niveau_filter[]'));
			if($has_niveaus)
				$form_content .= html_writer::div(html_writer::tag('h5', get_string('niveau_filter', 'block_exacomp')).$select, '');
		}

		if(!empty($modules)){
			$selected = '';
			if(in_array('0', $selected_modules) || empty($selected_modules))
				$selected = ' selected';

			$options = html_writer::tag('option'.$selected, get_string('all_modules', 'block_exacomp'), array('value'=>0));
			foreach($modules as $module){
				$selected = '';
				if(in_array($module->id, $selected_modules))
					$selected = ' selected';
					
				$options .= html_writer::tag('option'.$selected, $module->name, array('value'=>$module->id));
			}
			$select = html_writer::tag('select multiple', $options, array('name'=>'module_filter[]'));
			$form_content .= html_writer::div(html_writer::tag('h5', get_string('module_filter', 'block_exacomp')).$select, '');
		}

		if(!empty($niveaus) || !empty($modules)){
			$form_content .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('apply_filter', 'block_exacomp')));
			$content .= html_writer::tag('form', $form_content, array('action'=>$PAGE->url.'&action=filter', 'method'=>'post'));
		}

		return $content;
	}
	public function print_activity_content($subjects, $modules, $courseid, $colspan){
		global $COURSE, $PAGE;

		$table = new html_table;
		$table->attributes['class'] = 'exabis_comp_comp';
		$table->attributes['id'] = 'comps';

		$rows = array();

		//print heading

		$row = new html_table_row();
		$row->attributes['class'] = 'heading r0';

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'category catlevel1';
		$cell->attributes['scope'] = 'col';
		$cell->text = html_writer::tag('h1', $COURSE->fullname);

		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->attributes['class'] = 'category catlevel1 bottom';
		$cell->attributes['scope'] = 'col';
		$cell->colspan = $colspan;
		//$cell->text = html_writer::link('#colsettings', get_string('column_setting', 'block_exacomp'))."&nbsp;&nbsp;"
		//.html_writer::link('#colsettings', get_string('niveau_filter', 'block_exacomp')).'&nbsp;&nbsp; ##file_module_selector###';

		$row->cells[] = $cell;
		$rows[] = $row;

		//print row with list of activities
		$row = new html_table_row();
		$cell = new html_table_cell();

		$row->cells[] = $cell;

		$modules_printed = array();

		foreach($modules as $module){
			$cell = new html_table_cell();
			$cell->attributes['class'] = 'ec_tableheadwidth';
			$cell->attributes['module-type'] = $module->modname;
			$cell->text = html_writer::link(block_exacomp_get_activityurl($module), $module->name);

			$row->cells[] = $cell;
		}

		$rows[] = $row;
		$rowgroup = 1;
		//print tree
		foreach($subjects as $subject){
			$row = new html_table_row();
			$row->attributes['class'] = 'ec_heading';
			$cell = new html_table_cell();
			$cell->colspan = $colspan;
			$cell->text = html_writer::tag('b', $subject->title);
			$row->cells[] = $cell;
			$rows[] = $row;
			$this->print_topics_activities($rows, 0, $subject->subs, $rowgroup, $modules);
		}
		$table->data = $rows;

		$table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
		$div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$div .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
		//$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		return html_writer::tag('form', $div, array('id'=>'edit-activities', 'action'=>$PAGE->url.'&action=save', 'method'=>'post'));

	}
	public function print_topics_activities(&$rows, $level, $topics, &$rowgroup, $modules, $rowgroup_class = '') {
		$padding = $level * 20 + 12;

		foreach($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic, true);
				
			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors));

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

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputid.$outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			foreach($modules as $module) {
				$moduleCell = new html_table_cell();
				$moduleCell->attributes['module-type='] = $module->modname;
				if(!block_exacomp_is_altversion())
				$moduleCell->text = html_writer::checkbox('topicdata[' . $module->id . '][' . $topic->id . ']', "", (in_array($topic->id, $module->topics))?true:false,'',array('class' => 'topiccheckbox'));
				$topicRow->cells[] = $moduleCell;
			}

			$rows[] = $topicRow;

			if (!empty($topic->descriptors)) {
				$this->print_descriptors_activities($rows, $level+1, $topic->descriptors, $rowgroup, $modules, $sub_rowgroup_class);
			}

			if (!empty($topic->subs)) {
				$this->print_topics_activities($rows, $level+1, $topic->subs, $rowgroup, $modules, $sub_rowgroup_class);
			}
		}
	}
	public function print_descriptors_activities(&$rows, $level, $descriptors, &$rowgroup, $modules, $rowgroup_class) {
		global $PAGE, $USER;

		foreach($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor,false,false);

			$padding = ($level) * 20 + 4;

			if($descriptor->parentid > 0)
				$padding += 20;
			
			$this_rowgroup_class = $rowgroup_class;

			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;

			$titleCell = new html_table_cell();
			$titleCell->style = "padding-left: ".$padding."px";
			$titleCell->text = html_writer::div($outputname);

			$descriptorRow->cells[] = $titleCell;

			foreach($modules as $module) {
				$moduleCell = new html_table_cell();
				$moduleCell->text = html_writer::checkbox('data[' . $module->id . '][' . $descriptor->id . ']', '', (in_array($descriptor->id, $module->descriptors))?true:false);
				$descriptorRow->cells[] = $moduleCell;
			}

			$rows[] = $descriptorRow;
		}
	}
	public function print_badge($badge, $descriptors, $context){
		global $CFG, $COURSE;;

		$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
		$content = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image'));
		$content .= html_writer::div($badge->name, '', array('style'=>'font-weight:bold;'));

		if($badge->is_locked())
			$content .= get_string('statusmessage_'.$badge->status, 'badges');
		elseif ($badge->status == BADGE_STATUS_ACTIVE){
			$content_form = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$badge->id))
			.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'lock', 'value'=>1))
			.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()))
			.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'return', 'value'=>new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id))))
			.html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('deactivate', 'badges')));

			$form = html_writer::tag('form', $content_form, array('method'=>'post', 'action'=>new moodle_url('/badges/action.php')));
				
			$content .= html_writer::div($form);
		}elseif(!$badge->has_manual_award_criteria()){
			$link = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), get_string('to_award_role', 'block_exacomp'));
			$content .= html_writer::div($link);
		}else{
			if(empty($descriptors)){
				$link = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), get_string('to_award', 'block_exacomp'));
				$content .= html_writer::div($link);
			}else{
				$content_form = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$badge->id))
				.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'activate', 'value'=>1))
				.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()))
				.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'return', 'value'=>new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id))))
				.get_string('ready_to_activate', 'block_exacomp')
				.html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('activate', 'badges')));
					
				$form = html_writer::tag('form', $content_form, array('method'=>'post', 'action'=>new moodle_url('/badges/action.php')));
				$content .= html_writer::div($form, '', array('style'=>'padding-bottom:20px;'));

				$link1 = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), get_string('conf_badges', 'block_exacomp') );
				$link2 = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), get_string('conf_comps', 'block_exacomp'));

				$content .= html_writer::div($link1.' / '.$link2);
			}
		}

		if($descriptors){
			$li_desc = '';
			foreach($descriptors as $descriptor){
				$li_desc .= html_writer::tag('li', $descriptor->title);
			}
			$content .= html_writer::tag('ul', $li_desc);
		}

		return html_writer::div($content, '', array('style'=>'padding:10px;'));
	}

	public function print_edit_badges($subjects, $badge){
		global $COURSE;
		$table = new html_table();
		$table->attributes['id'] = 'comps';
		$table->attributes['class'] = 'exabis_comp_comp';

		$rows = array();
		
		$rowgroup = 0;
		//print tree
		foreach($subjects as $subject){
			$row = new html_table_row();
			$row->attributes['class'] = 'ec_heading';
			$cell = new html_table_cell();
			//$cell->colspan = 2;
			$cell->text = html_writer::tag('b', $subject->title);
			$row->cells[] = $cell;
			
			$cell = new html_table_cell();
			$cell->attributes['class'] = 'ec_tableheadwidth';
			$cell->text = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), $badge->name);
			$row->cells[] = $cell;
			$rows[] = $row;
				
			$this->print_topics_badges($rows, 0, $subject->subs, $rowgroup, $badge);
		}

		$table->data = $rows;

		$table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
		$div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$div .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

		return html_writer::div(get_string('description_edit_badge_comps', 'block_exacomp'))
			.html_writer::empty_tag('br')
			.html_writer::tag('form', $div, array('id'=>'edit-activities','action'=> new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id, 'action'=>'save')), 'method'=>'post'));

	}
	public function print_topics_badges(&$rows, $level, $topics, &$rowgroup, $badge, $rowgroup_class = '') {
		$padding = $level * 20 + 12;

		foreach($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);

			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors));
				
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

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			$badgeCell = new html_table_cell();
			$topicRow->cells[] = $badgeCell;
				
			$rows[] = $topicRow;

			if (!empty($topic->descriptors)) {
				$this->print_descriptors_badges($rows, $level+1, $topic->descriptors, $rowgroup, $badge, $sub_rowgroup_class);
			}

			if (!empty($topic->subs)) {
				$this->print_topics_badges($rows, $level+1, $topic->subs, $rowgroup, $badge, $sub_rowgroup_class);
			}
		}
	}
	public function print_descriptors_badges(&$rows, $level, $descriptors, &$rowgroup, $badge, $rowgroup_class) {
		global $PAGE, $USER;

		foreach($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor,false,false);

			$padding = ($level) * 20 + 4;

			$this_rowgroup_class = $rowgroup_class;
				
			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;
				
			$titleCell = new html_table_cell();
			$titleCell->style = "padding-left: ".$padding."px";
			$titleCell->text = html_writer::div($outputname);

			$descriptorRow->cells[] = $titleCell;
				
			$badgeCell = new html_table_cell();
			$badgeCell->text = html_writer::checkbox('descriptors['.$descriptor->id.']', $descriptor->id, ((isset($badge->descriptors[$descriptor->id]))?true:false));
			$descriptorRow->cells[] = $badgeCell;
				
			$rows[] = $descriptorRow;
		}
	}
	public function print_no_topics_warning(){
		global $COURSE;
		return html_writer::link(new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$COURSE->id)), get_string("no_topics_selected", "block_exacomp"));
	}
	public function print_no_course_activities_warning(){
		global $COURSE;
		return html_writer::link(new moodle_url('/course/view.php', array('id'=>$COURSE->id, 'notifyeditingon'=>1)), get_string("no_course_activities", "block_exacomp"));
	}
	public function print_no_activities_warning($isTeacher = true){
		global $COURSE;
		if($isTeacher)
			return html_writer::link(new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$COURSE->id)), get_string("no_activities_selected", "block_exacomp"));
		else 
			return get_string("no_activities_selected_student", "block_exacomp");
	}
	function print_competence_profile_metadata($student) {
		$namediv = html_writer::div(html_writer::tag('b',$student->firstname . ' ' . $student->lastname)
				.html_writer::div(get_string('name', 'block_exacomp'), ''), '');

		$imgdiv = html_writer::div($this->user_picture($student,array("size"=>100)), '');

		(!empty($student->city))?$citydiv = html_writer::div($student->city
				.html_writer::div(get_string('city', 'block_exacomp'), ''), ''):$citydiv ='';
			
		return html_writer::div($namediv.$imgdiv.$citydiv, 'competence_profile_metadata clearfix');
	}
	
	function box_error($message) {
		if (!$message) {
			$message = get_string('unknownerror');
		} elseif ($message instanceof moodle_exception) {
			$message = $message->getMessage();
		}
		
		$message = get_string('error').': '.$message;
		return $this->notification($message);
	}
	
	function print_competene_profile_overview($student, $courses, $possible_courses, $badges, $exaport, $exaportitems, $exastud_competence_profile_data, $onlygainedbadges=false) {

		$table = $this->print_competence_profile_overview_table($student, $courses, $possible_courses);
		$overviewcontent = $table;
		//my badges
		if(!empty($badges))
			$overviewcontent .= html_writer::div($this->print_my_badges($badges, $onlygainedbadges), 'competence_profile_overview_badges');
		
		//my items
		if($exaport){
			$exaport_content = '';
			foreach($exaportitems as $item){
				$exaport_content .= html_writer::tag('li', html_writer::link('#'.$item->name.$item->id, $item->name));
			}
			$overviewcontent .= html_writer::div(html_writer::tag('h4', get_string('my_items', 'block_exacomp'))
				. html_writer::tag('ul',$exaport_content), 'competence_profile_overview_artefacts');
		}
		
		//my feedbacks
		if($exastud_competence_profile_data){
			$exastud_content  = '';
			foreach($exastud_competence_profile_data->periods as $period){
				$exastud_content .= html_writer::tag('li', html_writer::link('#'.$period->description.$period->id, $period->description));
			}
			$overviewcontent .= html_writer::div(html_writer::tag('h4', get_string('my_periods', 'block_exacomp'))
				. html_writer::tag('ul', $exastud_content), 'competence_profile_overview_feedback');
		}
		
		return html_writer::div($overviewcontent, 'competence_profile_overview clearfix');
	}
	function print_competence_profile_overview_table($student, $courses, $possible_courses){
		$total_total = 0;
		$total_reached = 0;
		$total_average = 0;

		$table = new html_table();
		$table->attributes['class'] = 'compstable flexible boxaligncenter generaltable';
		$rows = array();

		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->text = get_string('course', 'block_exacomp');
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = get_string('gained', 'block_exacomp');
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = get_string('total', 'block_exacomp');
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->text = '';
		$row->cells[] = $cell;
		$rows[] = $row;

		foreach($possible_courses as $course){
			$statistics = block_exacomp_get_course_competence_statistics($course->id, $student, block_exacomp_get_grading_scheme($course->id));
			//$pie_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id));

			if(array_key_exists($course->id, $courses)){
				$row = new html_table_row();
				$cell = new html_table_cell();
				$cell->text = html_writer::link('#'.$course->fullname.$course->id, $course->fullname);
				$row->cells[] = $cell;
					
				$cell = new html_table_cell();
				$cell->text = $statistics[1];
				$row->cells[] = $cell;
					
				$cell = new html_table_cell();
				$cell->text = $statistics[0];
				$row->cells[] = $cell;
					
				$perc_average = $statistics[0] > 0 ? $statistics[2]/$statistics[0]*100 : 0;
				$perc_reached = $statistics[0] > 0 ? $statistics[1]/$statistics[0]*100 : 0;
					
				$cell = new html_table_cell();
				//$cell->colspan = 4;
				$cell->text = html_writer::div(html_writer::div(
						html_writer::div('','lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 
						'lbmittelwertcontainer') . 
						html_writer::div('', 'ladebalkenstatus stripes', array('style'=>'width:'.$perc_reached.'%;')),
					'ladebalken');
						
				$row->cells[] = $cell;
				$rows[] = $row;
			}
				
			$total_total +=  $statistics[0];
			$total_reached += $statistics[1];
			$total_average += $statistics[2];
				
		}

		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->text = html_writer::link('#all_courses',get_string('allcourses', 'block_exacomp'));
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = $total_reached;
		$row->cells[] = $cell;

		$cell = new html_table_cell();
		$cell->text = $total_total;
		$row->cells[] = $cell;

		$perc_average = 0;
		$perc_reached = 0;
		if($total_total != 0){
			$perc_average = $total_average/$total_total*100;
			$perc_reached = $total_reached/$total_total*100;
		}
		$cell = new html_table_cell();
		$cell->text = html_writer::div(html_writer::div(
					html_writer::div('','lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 
					'lbmittelwertcontainer') . 
					html_writer::div('', 'ladebalkenstatus stripes', array('style'=>'width:'.$perc_reached.'%;')),
				'ladebalken');
						
		$row->cells[] = $cell;

		$rows[] = $row;
		$table->data = $rows;
		return html_writer::div(html_writer::tag('h4', get_string('my_comps', 'block_exacomp')).html_writer::table($table), 'competence_profile_overview_mycompetencies clearfix');;
	}

	function print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $courseid){

		$content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvas_doughnut".$courseid)),'piegraph',array("style" => "width:100%"));
		$content .= '
		<script>
		var pieChartData = [
		{
		value:'.$pendingcomp.',
		color:"#888888",
		highlight: "#3D3D3D",
		label: "'.get_string('pendingcomp', 'block_exacomp').'"
	},
	{
	value: '.$teachercomp.',
	color: "#48a53f",
	highlight: "#006532",
	label: "'.get_string('teachercomp', 'block_exacomp').'"
	},
	{
	value: '.$studentcomp.',
	color: "#f9b233",
	highlight: "#f39200",
	label: "'.get_string('studentcomp', 'block_exacomp').'"
	}
	];
		
	var ctx_d = document.getElementById("canvas_doughnut'.$courseid.'").getContext("2d");
	ctx_d.canvas.height = 120;
			
	window.myDoughnut = new Chart(ctx_d).Doughnut(pieChartData, {
	responsive: true
	});

	</script>
	';
		return $content;
	}
	function print_competence_profile_course($course, $student, $showall = true) {
		$scheme = block_exacomp_get_grading_scheme($course->id);
		$compTree = block_exacomp_get_competence_tree($course->id);
		
		//print heading
		$content = html_writer::tag("h4", html_writer::tag('a', $course->fullname, array('name'=>$course->fullname.$course->id)), array("class" => "competence_profile_coursetitle"));
		if(!$compTree) {
			$content .= html_writer::div(get_string("nodata","block_exacomp"),"error");
			return html_writer::div($content, 'competence_profile_coursedata');
		}
		//print graphs
		//$topics = block_exacomp_get_topics_for_radar_graph($course->id, $student->id);
		//$radar_graph = html_writer::div($this->print_radar_graph($topics,$course->id),"competence_profile_radargraph");

		//list($teachercomp,$studentcomp,$pendingcomp) = block_exacomp_get_competencies_for_pie_chart($course->id,$student, $scheme, 0, true);
		//$pie_graph = html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $course->id),"competence_profile_radargraph");
		
		//$total_comps = $teachercomp+$studentcomp+$pendingcomp;
		//$timeline_data= block_exacomp_get_timeline_data(array($course), $student, $total_comps);
		
		//if($timeline_data)
		  //  $timeline_graph =  html_writer::div($this->print_timeline_graph($timeline_data->x_values, $timeline_data->y_values_teacher, $timeline_data->y_values_student, $timeline_data->y_values_total, $course->id),"competence_profile_timelinegraph");
		//else
		   // $timeline_graph = "";
			
		//$content .= html_writer::div($radar_graph.$pie_graph.$timeline_graph, 'competence_profile_graphbox clearfix');
		//$content .= html_writer::div($this->print_radar_graph_legend(),"radargraph_legend");
			
		//print list
		$student = block_exacomp_get_user_information_by_course($student, $course->id);

		$items = false;
		// if($student != null && block_exacomp_get_profile_settings($student->id)->useexaport == 1) {
		//	$items = block_exacomp_get_exaport_items($student->id);
		//}
		$content .= $this->print_competence_profile_tree_v2($compTree,$course->id, $student,$scheme, false, $items);

		return html_writer::div($content,"competence_profile_coursedata");
	}

private function print_competence_profile_tree_v2($in, $courseid, $student = null,$scheme = 1, $showonlyreached = false, $eportfolioitems = false) {
		global $DB;
		if($student != null){
			$profile_settings = block_exacomp_get_profile_settings($student->id);
			$studentid= $student->id;
		}
		else 
			$studentid = 0;
		$showonlyreached_total = false;
		if($showonlyreached || ($student != null && $profile_settings->showonlyreached ==1))
			$showonlyreached_total = true;
		  
		$content="";	
		foreach($in as $subject){
			foreach($subject->subs as $topic){
				$fieldset_content = html_writer::tag('legend', block_exacomp_get_topic_numbering($topic).' '.$topic->title, array('class'=>'togglefield'));
				
				$desc_content = "";
				$niveaus = array();
				$student_eval = array();
				$teacher_eval = array();
				if(!empty($topic->descriptors))
				foreach($topic->descriptors as $descriptor){
					$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
					$content_div = html_writer::tag('span', (block_exacomp_is_altversion() && $niveau)?$niveau->title:$descriptor->title);
					$return = block_exacomp_calc_example_stat_for_profile($courseid, $descriptor, $student, $scheme, ((block_exacomp_is_altversion() && $niveau)?$niveau->title:$descriptor->title));
					
					$desc_content .= html_writer::div($content_div, 'compprof_barchart', array('id'=>'svgdesc'.$descriptor->id));
					
					$span_in_work = "";
					if($return->total > 0)
						$span_in_work = html_writer::tag('span', \block_exacomp\get_string('inwork', null, ['inWork' => $return->inWork, 'total' => $return->total]), array('class'=>"compprof_barchart_teacher"));
					
					$img_teacher = "";	
					if(isset($student->competencies->teacher[$descriptor->id])){
						$img_teacher_src =	 '/blocks/exacomp/pix/compprof_rating_teacher_'.$student->competencies->teacher[$descriptor->id].'.png';			
						$img_teacher = html_writer::empty_tag('img', array('src'=>new moodle_url($img_teacher_src)));
					}
					
					$span_teacher = html_writer::tag('span', get_string('teacher_eval', 'block_exacomp').": ".
						((isset($student->competencies->teacher[$descriptor->id]))?$img_teacher:'oB') . (isset($student->competencies->teacher_additional_grading[$descriptor->id])? " (".$student->competencies->teacher_additional_grading[$descriptor->id].") ":""), 
						array('class'=>"compprof_barchart_teacher"));
									   
					$img_student = "";
					if(isset($student->competencies->student[$descriptor->id])){
						$img_student_src = '/blocks/exacomp/pix/compprof_rating_student_'.$student->competencies->student[$descriptor->id].'.png';
						$img_student = html_writer::empty_tag('img', array('src'=>new moodle_url($img_student_src)));
					}				
					$span_student = html_writer::tag('span', get_string('student_eval', 'block_exacomp').": ".((isset($student->competencies->student[$descriptor->id]))?$img_student:'oB'), array('class'=>"compprof_barchart_teacher"));
						
					$desc_content .= html_writer::div($span_in_work.$span_teacher.$span_student, 'compprof_barchart_legend');		
					$return = block_exacomp_calc_example_stat_for_profile($courseid, $descriptor, $student, $scheme, ((block_exacomp_is_altversion() && $niveau)?$niveau->title:$descriptor->title));
					$desc_content .= html_writer::div(html_writer::tag('p', html_writer::empty_tag('span', array('id'=>'value'))), 'tooltip hidden', array('id'=>'tooltip'.$descriptor->id));
					
					$desc_content .= $this->print_example_stacked_bar($return->data, $descriptor->id);
					
					$niveaus[] = '"'.((block_exacomp_is_altversion() && $niveau)?$niveau->title:$descriptor->title).'"';
					$student_eval[] = (isset($student->competencies->student[$descriptor->id]))?$student->competencies->student[$descriptor->id]:0;
					$teacher_eval[] = (isset($student->competencies->teacher[$descriptor->id]))?$student->competencies->teacher[$descriptor->id]:0;
					
				}
				
				$div_content = "";
				if(count($niveaus)>2 && count($niveaus)<9){
					$radar_graph = html_writer::empty_tag('canvas', array('id'=>'canvas'.$topic->id, 'height'=>'450', 'width'=>'450'));
					$radar_graph .=  $this->print_radar_graph_topic(implode(",", $niveaus),implode(",", $teacher_eval),implode(",", $student_eval),'canvas'.$topic->id);
					$div_content = html_writer::div($radar_graph, 'radar_graph', array('style'=>'width:30%'));
					$div_content .= $this->print_radar_graph_legend();
				}
				
				$div_content .= $desc_content;
				
				$fieldset_content .= html_writer::div($div_content, 'content_div');
				$content .= html_writer::tag('fieldset', $fieldset_content, array('id'=>'topic_field'.$topic->id));
			}
		}
		
		return $content;
	}
	
	private function print_radar_graph_topic($labels, $data1, $data2, $canvasid){
		$global_scheme_values = \block_exacomp\global_config::get_scheme_items();

		return '<script>
		var radarChartData = {
			labels: ['.$labels.'],
			datasets: [
				{
					label: "Lehrerbeurteilung",
					fillColor: "rgba(72,165,63,0.2)",
					strokeColor: "rgba(72,165,63,1)",
					pointColor: "rgba(72,165,63,1)",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#fff",
					pointHighlightStroke: "rgba(151,187,205,1)",
					data: ['.$data1.']
				},
				{
					label: "Schülerbeurteilung",
					fillColor: "rgba(249,178,51,0.2)",
					strokeColor: "#f9b233",
					pointColor: "#f9b233",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#fff",
					pointHighlightStroke: "rgba(151,187,205,1)",
					data: ['.$data2.']
				}
			]
		};
	
		window.myRadar = new Chart(document.getElementById("'.$canvasid.'").getContext("2d")).Radar(radarChartData, {
			responsive: true,
			showScale: true,
			scaleShowLabels: true,
			scaleLabel: "<%if (value == 1){%><%=\''.$global_scheme_values[1].'\'%><%}%><%if (value == 2){%><%=\''.$global_scheme_values[2].'\'%> <%}%><%if (value == 3){%><%=\''.$global_scheme_values[3].'\'%><%}if(value>3){%><%=value%><%}%>",
			multiTooltipTemplate: "<%if (value == 1){%><%=\''.$global_scheme_values[1].'\'%><%}%><%if (value == 2){%><%=\''.$global_scheme_values[2].'\'%> <%}%><%if (value == 3){%><%=\''.$global_scheme_values[3].'\'%><%}%><%if (value == 0){%><%=\''.$global_scheme_values[0].'\'%><%}if(value>3){%><%=value%><%}%>",
			scaleLineColor: "rgba(0,0,0,.3)",
			angleLineColor : "rgba(0,0,0,.3)"
		});
	
		</script>';
	}
	
	private function print_example_stacked_bar($dataset, $descrid){
	return "<script>var margins = {
	top: 20,
	left: 10,
	right: 10,
	bottom: 0
},
width =200,
	height = 20,
	dataset =  ".$dataset.",
	series = dataset.map(function (d) {
		return d.name;
	}),
	dataset = dataset.map(function (d) {console.log(d);
		return d.data.map(function (o, i) {
			// Structure it so that your numeric
			// axis (the stacked amount) is y
			return {
				y: o.count,
				x: o.niveau,
	title: d.name
			};
		});
	}),
	stack = d3.layout.stack();

stack(dataset);

var dataset = dataset.map(function (group) {
	return group.map(function (d) {console.log(d);
		// Invert the x and y values, and y0 becomes x0
		return {
			x: d.y,
			y: d.x,
			x0: d.y0,
			title: d.title
		};
	});
}),
	svg = d3.select('#svgdesc".$descrid."')
		.append('svg')
		.attr('width', width + margins.left + margins.right)
		.attr('height', height + margins.top + margins.bottom)
		.append('g')
		.attr('transform', 'translate(' + margins.left + ',' + (margins.top + 5) + ')'),
	xMax = d3.max(dataset, function (group) {
		return d3.max(group, function (d) {
			return d.x + d.x0;
		});
	}),
	xScale = d3.scale.linear()
		.domain([0, xMax])
		.range([0, width]),
	niveaus = dataset[0].map(function (d) {
		return d.y;
	}),
	yScale = d3.scale.ordinal()
		.domain(niveaus)
		.rangeRoundBands([0, height], .1),
  
	colours = [\"#B8B894\", \"#990000\",  \"#00CC00\", \"#008F00\", \"#006D00\", \"#dddd22\", \"#ff0033\", \"#345678\"],
	
	groups = svg.selectAll('g')
		.data(dataset)
		.enter()
		.append('g')
		.style('fill', function (d, i) {
		return colours[i];
	}),
	rects = groups.selectAll('rect')
		.data(function (d) {
		return d;
	})
		.enter()
		.append('rect')
		.attr('x', function (d) {
		return xScale(d.x0);
	})
	   
		.attr('height', function (d) {
		return yScale.rangeBand();
	})
		.attr('width', function (d) {
		return xScale(d.x);
	})
		.on('mouseover', function (d) {console.log(d);
		var xPos = parseFloat(d3.select(this).attr('x')) / 2 + width / 2;
		var yPos = parseFloat(d3.select(this).attr('y')) + yScale.rangeBand() / 2;

		d3.select('#tooltip".$descrid."')
			.style('left', xPos + 'px')
			.style('top', yPos + 'px')
			.select('#value')
			.text(d.x+d.title);

		d3.select('#tooltip".$descrid."').classed('hidden', false);
	})
		.on('mouseout', function () {
		d3.select('#tooltip".$descrid."').classed('hidden', true);
	})
</script>";
	}		

	function print_radar_graph($records,$courseid) {
		global $CFG;
		
		if(count($records) >= 3 && count($records) <= 7) {

			$content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvasradar".$courseid)),"radargraph",array("style" => "height:100%"));
			$content .= '
			<script>
			var radarChartData = {
			labels: [';

			foreach($records as $record)
				$content .= '"'.$record->title.'",';

			$content .= '],
			datasets: [
			{
			label: "'.get_string("studentcomp","block_exacomp").'",
			fillColor: "rgba(249,178,51,0.2)",
			strokeColor: "#f9b233",
			pointColor: "#f9b233",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: [';

			foreach($records as $record)
				$content .= '"'.round($record->student, 2).'",';
			$content .= ']
			},
			{
			label: "'.get_string("teachercomp","block_exacomp").'",
			fillColor: "rgba(72,165,63,0.2)",
			strokeColor: "rgba(72,165,63,1)",
			pointColor: "rgba(72,165,63,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: [';

			foreach($records as $record)
				$content .= '"'.round($record->teacher, 2).'",';
			$content .=']
		}
		]
		};

		var ctx_r = document.getElementById("canvasradar'.$courseid.'").getContext("2d");
		ctx_r.canvas.height = 150;
		
		window.myRadar = new Chart(ctx_r).Radar(radarChartData, {
		responsive: true, multiTooltipTemplate: "<%= value %>"+"%"
		});
		
		</script>';
		} else {
			//print error
			$img = html_writer::div(html_writer::tag("img", "", array("src" => $CFG->wwwroot . "/blocks/exacomp/pix/graph_notavailable.png")));
			$content = html_writer::div($img . get_string("radargrapherror","block_exacomp"),"competence_profile_grapherror");
		}
		return $content;
	}
	public function print_radar_graph_legend() {
		$content = html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceyellow");
		$content .= ' '.get_string("studentcomp","block_exacomp").' ';
		$content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","competenceok");
		$content .= ' '.get_string("teachercomp","block_exacomp").' '.html_writer::empty_tag('br');
		return $content;
	}
	
	public function print_timeline_graph($x_values, $y_values1, $y_values2, $y_values3, $courseid){
		$content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvas_timeline".$courseid)),'timeline',array("style" => ""));
		$content .= '
		<script>
		var timelinedata = {
			labels: [';
			foreach($x_values as $val)
				$content .= '"'.$val.'",';

			$content .= '],
			datasets: [
			{
				label: "Teacher Timeline",
				fillColor: "rgba(72,165,63,0.2)",
					strokeColor: "rgba(72,165,63,1)",
				pointColor: "rgba(72,165,63,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(151,187,205,1)",
				data: [';
				foreach($y_values1 as $val)
					$content .= '"'.$val.'",';
	
				$content .= ']
			},
			{
				label: "Student Timeline",
				fillColor: "rgba(249,178,51,0.2)",
				strokeColor: "#f9b233",
				pointColor: "#f9b233",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(151,187,205,1)",
				data: [';
				foreach($y_values2 as $val)
					$content .= '"'.$val.'",';
	
				$content .= ']
			},
			{
				label: "All Competencies",
				fillColor: "rgba(220,220,220,0.2)",
				strokeColor: "rgba(220,220,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(220,220,220,1)",
				data: [';
				foreach($y_values3 as $val)
					$content .= '"'.$val.'",';
	
				$content .= ']
			}
		]
		};
			
		
		var ctx = document.getElementById("canvas_timeline'.$courseid.'").getContext("2d")
		ctx.canvas.height = 50;
		
		window.myTimeline = new Chart(ctx).Line(timelinedata, {
		responsive: true, bezierCurve : false
		});
	
		</script>
		';
		return $content;
	}
	public function print_profile_settings($courses, $settings, $usebadges, $exaport, $exastud, $exastud_periods){
		global $COURSE;
		$exacomp_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exacomp'));
		
		$exacomp_div_content .= html_writer::div(
				html_writer::checkbox('showonlyreached', 1, ($settings->showonlyreached==1), get_string('profile_settings_showonlyreached', 'block_exacomp')));

		$content_courses = html_writer::tag('p', get_string('profile_settings_choose_courses', 'block_exacomp'));
		foreach($courses as $course){
			$content_courses .= html_writer::checkbox('profile_settings_course[]', $course->id, (isset($settings->exacomp[$course->id])), $course->fullname);
			$content_courses .= html_writer::empty_tag('br');
		}
		$exacomp_div_content .= html_writer::div($content_courses);
		
		$exacomp_div_content .= html_writer::div(
				html_writer::checkbox('profile_settings_showallcomps', 1, ($settings->showallcomps==1), get_string('profile_settings_showallcomps', 'block_exacomp')));
		
		if($usebadges){
			$badge_div_content = html_writer::tag('h4', get_string('profile_settings_badges_lineup', 'block_exacomp'));
			$badge_div_content .= html_writer::div(
					html_writer::checkbox('usebadges', 1, ($settings->usebadges ==1), get_string('profile_settings_usebadges', 'block_exacomp')));
				
			$badge_div_content .= html_writer::checkbox('profile_settings_onlygainedbadges', 1, ($settings->onlygainedbadges==1), get_string('profile_settings_onlygainedbadges', 'block_exacomp'));
			$badge_div_content .= html_writer::empty_tag('br');
				
			$badges_div = html_writer::div($badge_div_content);
			$exacomp_div_content .= $badges_div;
		}
		$exacomp_div = html_writer::div($exacomp_div_content);

		$content = $exacomp_div;

		if($exaport){
			$exaport_items = block_exacomp_get_exaport_items();
			if(!empty($exaport_items)){
				$exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
				$exaport_div_content .= html_writer::div(
						html_writer::checkbox('useexaport', 1, ($settings->useexaport==1), get_string('profile_settings_useexaport', 'block_exacomp')));
					
				$exaport_div = html_writer::div($exaport_div_content);
				$content .= $exaport_div;
			}else{
				$exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
				$exaport_div_content .= get_string('profile_settings_no_item', 'block_exacomp');
				$exaport_div = html_writer::div($exaport_div_content);
				$content .= $exaport_div;
			}
		}

		if($exastud){
			$exastud_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exastud'));
			$exastud_div_content .= html_writer::div(
					html_writer::checkbox('useexastud', 1, ($settings->useexastud ==1), get_string('profile_settings_useexastud', 'block_exacomp')));
				
			if(!empty($exastud_periods)){
				$content_periods = html_writer::tag('p', get_string('profile_settings_choose_periods', 'block_exacomp'));

				foreach($exastud_periods as $period){
					$content_periods .= html_writer::checkbox('profile_settings_periods[]', $period->id, (isset($settings->exastud[$period->id])), $period->description);
					$content_periods .= html_writer::empty_tag('br');
				}
			}else{
				$content_periods = html_writer::tag('p', get_string('profile_settings_no_period', 'block_exacomp'));
			}
			$exastud_div_content .= html_writer::div($content_periods);

			$exastud_div = html_writer::div($exastud_div_content);
			$content .= $exastud_div;
		}


		$div = html_writer::div(html_writer::tag('form',
				$content
				. html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), 'exabis_save_button'),
				array('action'=>'competence_profile_settings.php?courseid='.$COURSE->id.'&action=save', 'method'=>'post')), 'block_excomp_center');

		return html_writer::tag("div", $div, array("id"=>"exabis_competences_block"));
	}
	public function print_competence_profile_exaport($settings, $user, $items){
		global $COURSE, $CFG, $USER;

		$header = html_writer::tag('h3', get_string('my_items', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));

		$content = '';
		//print items with comps
		$items_with_no_comps = false;
		foreach($items as $item){
			if($item->hascomps)
				$content .= $this->print_exaport_item($item, $user->id);
			else
				$items_with_no_comps = true;
		}

		if($items_with_no_comps){
			$content .= html_writer::tag('p', get_string('item_no_comps', 'block_exacomp'));
			foreach($items as $item){
				if($item->userid != $USER->id)
					$url = $CFG->wwwroot.'/blocks/exaport/shared_item.php?courseid='.$COURSE->id.'&access=portfolio/id/'.$userid.'&itemid='.$item->id.'&backtype=&att='.$item->attachment;
				else
					$url = new moodle_url('/blocks/exaport/item.php',array("courseid"=>$COURSE->id,"access"=>'portfolio/id/'.$userid,"id"=>$item->id,"sesskey"=>sesskey(),"action"=>"edit"));
				
				$li_items = '';
				if(!$item->hascomps){
					$li_items .= html_writer::tag('li', html_writer::link($url, $item->name,array('name'=>$item->name.$item->id)));
				}
				$content .= html_writer::tag('ul', $li_items);
			}
			$content = html_writer::div($content,'competence_profile_noassociation');
		}
		return $header.$content;
	}

	public function print_exaport_item($item, $userid){
		global $COURSE, $CFG, $DB, $USER;
		$content = html_writer::tag('h4', html_writer::tag('a', $item->name, array('name'=>$item->name.$item->id)), array('class'=>'competence_profile_coursetitle'));
		
		$table = new html_table();
		$table->attributes['class'] = 'compstable flexible boxaligncenter generaltable';
		$rows = array();
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'competence_tableright';
		$cell->text = get_string('item_type', 'block_exacomp').": "; 
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		if(strcmp($item->type, 'link')==0){
			$cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/link_32.png')))
				.get_string('item_link', 'block_exacomp');
		}elseif(strcmp($item->type, 'file')==0){
			$cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/file_32.png')))
				.get_string('item_file', 'block_exacomp');
		}elseif(strcmp($item->type, 'note')==0){
			$cell->text = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/note_32.png')))
				.get_string('item_note', 'block_exacomp');
		}
		$row->cells[] = $cell;
		$rows[] = $row;
		
		if(!empty($item->intro)){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->attributes['class'] = 'competence_tableright';
			$cell->text = get_string('item_title', 'block_exacomp').": "; 
			$row->cells[] = $cell;
			$cell = new html_table_cell();
			$cell->text = $item->intro;
			$row->cells[] = $cell;
			$rows[] = $row;
		}
		
		if(!empty($item->url)){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->attributes['class'] = 'competence_tableright';
			$cell->text = get_string('item_url', 'block_exacomp').": "; 
			$row->cells[] = $cell;
			$cell = new html_table_cell();
			$cell->text = $item->url;
			$row->cells[] = $cell;
			$rows[] = $row;
		}
		
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'competence_tableright';
		$cell->text = get_string('item_link', 'block_exacomp').": "; 
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		
		if($userid != $USER->id)
			$url = $CFG->wwwroot.'/blocks/exaport/shared_item.php?courseid='.$COURSE->id.'&access=portfolio/id/'.$userid.'&itemid='.$item->id.'&backtype=&att='.$item->attachment;
		else
			$url = new moodle_url('/blocks/exaport/item.php',array("courseid"=>$COURSE->id,"access"=>'portfolio/id/'.$userid,"id"=>$item->id,"sesskey"=>sesskey(),"action"=>"edit"));
		
		$cell->text = html_writer::link($url, $url);
		$row->cells[] = $cell;
		$rows[] = $row;
			
		$table->data = $rows;
		$content .= html_writer::table($table);
		
		// STANDARDS
		$allSubjects = block_exacomp_get_all_subjects();
		$allTopics = block_exacomp_get_all_topics();
		// 3. GET DESCRIPTORS
		$allDescriptors = $item->descriptors;
		$usedTopics = array();
		foreach ($allDescriptors as $descriptor) {
			$descriptor->topicid = $DB->get_field(block_exacomp::DB_DESCTOPICS, 'topicid', array('descrid' => $descriptor->id), IGNORE_MULTIPLE);
			$descriptor->tabletype = 'descriptor';
			// get descriptor topic
			if (empty($allTopics[$descriptor->topicid])) continue;
			$topic = $allTopics[$descriptor->topicid];
			$topic->descriptors[$descriptor->id] = $descriptor;
			$usedTopics[$topic->id] = $topic;
		}
		$subjects = array();
		
		foreach ($usedTopics as $topic) {
			$found = true;
			for ($i = 0; $i < 10; $i++) {
				if ($topic->parentid) {
					// parent is topic, find it
					if (empty($allTopics[$topic->parentid])) {
						$found = false;
						break;
					}
		
					// found it
					$allTopics[$topic->parentid]->subs[$topic->id] = $topic;
					$usedTopics[$topic->parentid] = $allTopics[$topic->parentid];
					// go up
					$topic = $allTopics[$topic->parentid];
				} else {
					// parent is subject, find it
					if (empty($allSubjects[$topic->subjid])) {
						$found = false;
						break;
					}
		
					// found: add it to the subject result
					$subject = $allSubjects[$topic->subjid];
					$subject->subs[$topic->id] = $topic;
					$subjects[$topic->subjid] = $subject;
		
					// top found
					break;
				}
			}
		}
		$list_descriptors = $this->print_competence_profile_tree($subjects, $COURSE->id);
		$list_heading = html_writer::tag('p', '<b>Verknüpfte Kompetenzen:</b>');
		
		return html_writer::div($content.$list_heading.$list_descriptors, 'competence_profile_artefacts');
	}
	public function print_competence_profile_exastud($profile_settings, $user){
		$exastud_periods = \block_exastud\api::get_student_periods_with_review();

		$output = '';

		foreach ($exastud_periods as $period) {
			if(isset($profile_settings->exastud[$period->id])){
				$output .= html_writer::tag('h4', html_writer::tag('a', $period->description, array('name'=>$period->description.$period->id)), array('class'=>'competence_profile_coursetitle'));
				$output .= \block_exastud\api::print_student_report($user->id, $period->id);
			}
		}

		if (!$output) {
			return;
		}

		$output = html_writer::div($output, 'competence_profile_exastud');
		$output = html_writer::tag('h3', get_string('my_periods', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle')).$output;

		return $output;
	}

	public function print_competence_profile_course_all($courses, $student){
		$subjects = block_exacomp_get_subjects_for_radar_graph($student->id);
		
		$content = html_writer::div(html_writer::tag('h4', html_writer::tag('a', get_string('profile_settings_showallcomps', 'block_exacomp'), array('name'=>'all_courses'))), 'competence_profile_coursetitle');
		
		if(!$subjects) {
			$content .= html_writer::div(get_string("nodata","block_exacomp"),"error");
			return $content;
		}
		
		$teachercomp = 0;
		$studentcomp = 0;
		$pendingcomp = 0;
		foreach($courses as $course){
			$course_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id), 0, true);
			$teachercomp += $course_data[0];
			$studentcomp += $course_data[1];
			$pendingcomp += $course_data[2];
		}
		
		//print graphs
		$radar_graph = html_writer::div($this->print_radar_graph($subjects, 0),"competence_profile_radargraph");

		$pie_graph = html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, 0),"competence_profile_radargraph");
		
		$total_comps = $teachercomp+$studentcomp+$pendingcomp;
		$timeline_data= block_exacomp_get_timeline_data($courses, $student, $total_comps);
		
		if($timeline_data)
			$timeline_graph =  html_writer::div($this->print_timeline_graph($timeline_data->x_values, $timeline_data->y_values_teacher, $timeline_data->y_values_student, $timeline_data->y_values_total, 0),"competence_profile_timelinegraph");
		else	
			$timeline_graph = "";
			
		$content .= html_writer::div($radar_graph.$pie_graph.$timeline_graph, 'competence_profile_graphbox clearfix');
		$content .= html_writer::div($this->print_radar_graph_legend(),"radargraph_legend");
			
		//print list
		foreach($courses as $course){
			$student = block_exacomp_get_user_information_by_course($student, $course->id);
			$scheme = block_exacomp_get_grading_scheme($course->id);
			$compTree = block_exacomp_get_competence_tree($course->id);
			$items = false;
			if($student != null && block_exacomp_get_profile_settings($student->id)->useexaport == 1) {
				$items = block_exacomp_get_exaport_items($student->id);
			}
			if($compTree)
				$content .= html_writer::tag('h4', $course->fullname) .
					$this->print_competence_profile_tree($compTree,$course->id, $student,$scheme, false, $items);
		}
		return html_writer::div($content,"competence_profile_coursedata");
	}
	public function print_wrapperdivstart(){
		  return html_writer::start_tag('div',array('id'=>'block_exacomp'));
  }
	public function print_wrapperdivend(){
		  return html_writer::end_tag('div');
	}
	public function print_profile_print_button(){
		$content = html_writer::link('javascript:window.print()',
				html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt'=>'print')), array('class'=>'print'));
		return html_writer::div(html_writer::tag('form', $content), 'competence_profile_printbox');
	}
	public function print_cross_subjects_drafts($subjects, $isAdmin=false){
		global $PAGE, $USER;
		
		$draft_content = html_writer::tag('h4', get_string('create_new_crosssub', 'block_exacomp'));
		$draft_content .= "<h5>" . get_string('use_available_crosssub','block_exacomp') . "</h5>";
		$drafts_exist = false;

		$draft_content .= html_writer::start_tag('ul', array("class"=>"collapsibleList"));
				
		foreach($subjects as $subject){
			if(isset($subject->crosssub_drafts)){
				$draft_content .= html_writer::start_tag('li');
				$draft_content .= $subject->title;
				
				$drafts_exist = true;
				$draft_content .= html_writer::start_tag('ul');
				
				//print_r($subject->crosssub_drafts);
				foreach($subject->crosssub_drafts as $draft){
					$text = $draft->description;
					$text = str_replace("\"","",$text);
					$text = str_replace("\'","",$text);
					$text = str_replace("\n"," ",$text);
					$text = str_replace("\r"," ",$text);
					$text = str_replace(":","\:",$text);

					$draft_content .= html_writer::start_tag('li');
					$draft_content .= html_writer::span(html_writer::checkbox('draft['.$draft->id.']', $draft->id, false, $draft->title), '', array('title'=>$text));
					$draft_content .= html_writer::end_tag('li');
				}
				$draft_content .= html_writer::end_tag('ul');
				$draft_content .= html_writer::end_tag('li');
			}
		}
		$draft_content .= html_writer::end_tag('ul');
		$submit = "";
		if($drafts_exist){
			$submit .= html_writer::empty_tag('input', array('name'=>'btn_submit', 'type'=>'submit', 'value'=>get_string('add_drafts_to_course', 'block_exacomp')));
			if($isAdmin) $submit .= html_writer::empty_tag('input', array('name'=>'delete_crosssubs', 'type'=>'submit', 'value'=>get_string('delete_drafts', 'block_exacomp')));
		}
		$submit .= html_writer::empty_tag('br');
		$submit .= html_writer::tag("h5", get_string('new_crosssub','block_exacomp'));
		$submit .= html_writer::empty_tag('input', array('name'=>'new_crosssub_overview', 'type'=>'submit', 'value'=>get_string('add_crosssub', 'block_exacomp')));
	
		$submit = html_writer::div($submit, ''); 
		$content = html_writer::tag('form', $draft_content.$submit, array('method'=>'post', 'action'=>$PAGE->url.'&action=save', 'name'=>'add_drafts_to_course'));
		
		return $content;
	}
	
	
	public function print_cross_subjects_form_start($selectedCrosssubject=null, $studentid=null){
		global $PAGE, $COURSE;
		$url_params = array();
		$url_params['action'] = 'save';
		if(isset($selectedCrosssubject))
			$url_params['crosssubjid'] = $selectedCrosssubject->id;
		if(isset($studentid))
			$url_params['studentid'] = $studentid;
				
		$url = new moodle_url($PAGE->url, $url_params);
		return html_writer::start_tag('form',array('id'=>'assign-competencies', "action" => $url, 'method'=>'post'));
	}
	
	public function print_dropdowns_cross_subjects($crosssubjects, $selectedCrosssubject, $students, $selectedStudent = BLOCK_EXACOMP_SHOW_ALL_STUDENTS, $isTeacher = false){
		global $PAGE, $COURSE, $USER;
		
		$content = html_writer::empty_tag("br");

		/*$content .= get_string("choosecrosssubject", "block_exacomp").': ';
		$options = array();
		foreach($crosssubjects as $crosssub)
			$options[$crosssub->id] = $crosssub->title;
		$content .= html_writer::select($options, "lis_crosssubs", $selectedCrosssubject, false,
				array("onchange" => "document.location.href='".$PAGE->url."&studentid=".$selectedStudent."&crosssubjid='+this.value;"));
*/
		if($isTeacher){
			//$content .= html_writer::empty_tag("br");
	
			$content .= get_string("choosestudent", "block_exacomp");
			$content .= block_exacomp_studentselector($students,$this->is_edit_mode()?BLOCK_EXACOMP_SHOW_ALL_STUDENTS:$selectedStudent,$PAGE->url."&crosssubjid=".$selectedCrosssubject,  ($students)?BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN:BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_EDITMODE);

			$right_content = html_writer::empty_tag('input', array('type'=>'button', 'id'=>'edit_crossubs', 'name'=> 'edit_crossubs', 'value' => get_string('manage_crosssubs','block_exacomp'),
				 "onclick" => "document.location.href='".(new moodle_url('/blocks/exacomp/cross_subjects_overview.php',array('courseid' => $COURSE->id)))->__toString()."'"));
			
			if($students){
				$url = new moodle_url('/blocks/exacomp/pre_planning_storage.php', array('courseid'=>$COURSE->id, 'creatorid'=>$USER->id));
				$right_content .= html_writer::tag('button', 
						html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/pre-planning-storage.png'), 
							'title'=> get_string('pre_planning_storage', 'block_exacomp'))
						),
					array(
						'id'=>'pre_planning_storage_submit', 'name'=> 'pre_planning_storage_submit',
						'type'=>'button', /* browser default setting for html buttons is submit */
						'exa-type' => 'iframe-popup', 'exa-url' => $url->out(false)
					)
				);
	
				
				$right_content .= $this->print_edit_mode_button();
				
			}
			$content .= html_writer::div($right_content, 'edit_buttons_float_right');
		
		}else{
			$right_content = html_writer::empty_tag('input', array('type'=>'button', 'id'=>'edit_crossubs', 'name'=> 'edit_crossubs', 'value' => get_string('manage_crosssubs','block_exacomp'),
				 "onclick" => "document.location.href='".(new moodle_url('/blocks/exacomp/cross_subjects_overview.php',array('courseid' => $COURSE->id)))->__toString()."'"));
			$content = html_writer::div($right_content, 'edit_buttons_float_right');
		}   
		return $content;
	}
	public function print_crosssub_subject_dropdown($crosssubject){
		$subjects = block_exacomp_get_subjects();
		$options = array();
		$options[0] = get_string('nocrosssubsub', 'block_exacomp');
		foreach($subjects as $subject){
			$options[$subject->id] = $subject->title;
		}
		return html_writer::select($options, "lis_crosssubject_subject", ($crosssubject)?$crosssubject->subjectid:0, false);
		
	}
	public function print_overview_metadata_cross_subjects($crosssubject, $isTeacher, $selectedStudent = null){
		global $DB;
		
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_info';

		$rows = array();

		$row = new html_table_row();

		$subject_title = get_string('nocrosssubsub', 'block_exacomp');
		if($crosssubject && $crosssubject->subjectid != 0){
			$subject = $DB->get_record(block_exacomp::DB_SUBJECTS, array('id'=>$crosssubject->subjectid));
			$subject_title = $subject->title;
		}
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('subject_singular', 'block_exacomp'), 'exabis_comp_top_name')
		. (($selectedStudent == 0)?$this->print_crosssub_subject_dropdown($crosssubject):html_writer::tag('b',$subject_title));

		$row->cells[] = $cell;

		$cell = new html_table_cell();
		
		if($selectedStudent == 0)
			$cell->text = html_writer::span(get_string('crosssubject', 'block_exacomp'), 'exabis_comp_top_name')
				. html_writer::empty_tag('input', array('type'=>'text', 'value'=>($crosssubject)?$crosssubject->title:'', 'name'=>'crosssub-title'));
		else 
			$cell->text = html_writer::span(get_string('crosssubject', 'block_exacomp'), 'exabis_comp_top_name')
				. html_writer::tag('div', ($crosssubject)?$crosssubject->title:'');
				
		$row->cells[] = $cell;
		
		$rows[] = $row;
		
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->colspan = (isset($selectedStudent))?3:2;
		if($selectedStudent == 0)
			$cell->text = html_writer::span(get_string('description', 'block_exacomp'), 'exabis_comp_top_name')
				. html_writer::empty_tag('input', array('type'=>'textarea', 'size'=>200, 'value'=>($crosssubject)?$crosssubject->description:'', 'name'=>'crosssub-description'));
		else
			 $cell->text = html_writer::span(get_string('description', 'block_exacomp'), 'exabis_comp_top_name')
				. html_writer::tag('b', ($crosssubject)?$crosssubject->description:'');
				
		$row->cells[] = $cell;  
		$rows[] = $row;
			
		if($isTeacher){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->colspan = 2;
			$cell->text = html_writer::span(get_string('tab_help', 'block_exacomp'), 'exabis_comp_top_name')
				. get_string('help_crosssubject', 'block_exacomp');	
			$row->cells[] = $cell;  
			$rows[] = $row;   
		}
		$table->data = $rows;

		$content = html_writer::table($table);

		return $content;
	}
	
	public function print_competence_based_list_tree($tree, $isTeacher, $editmode, $show_examples = true) {
		global $PAGE;
		
		$html_tree = "";
		$html_tree .= html_writer::start_tag("ul",array("class"=>"collapsibleList"));
		foreach($tree as $skey => $subject) {
			if($subject->associated == 1 || ($isTeacher && $editmode==1)){
				$html_tree .= html_writer::start_tag("li", array('class'=>($subject->associated == 1)?"associated":""));
				$html_tree .= $subject->title;
				
				if(!empty($subject->subs))
					$html_tree .= html_writer::start_tag("ul");
				
				foreach ( $subject->subs as $tkey => $topic ) {
					if($topic->associated == 1 || ($isTeacher && $editmode==1)){
						$html_tree .= html_writer::start_tag("li", array('class'=>($topic->associated == 1)?"associated":""));
						$html_tree .= block_exacomp_get_topic_numbering($topic->id).' '.$topic->title;
						
						if(!empty($topic->descriptors)) {
							$html_tree .= html_writer::start_tag("ul");
						
							foreach ( $topic->descriptors as $dkey => $descriptor ) {
								if($descriptor->associated == 1 || ($isTeacher && $editmode==1))
									$html_tree .= $this->print_competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples);
							}
						
							$html_tree .= html_writer::end_tag("ul");
						}
					}
					
				}
				if(!empty($subject->subs))
					$html_tree .= html_writer::end_tag("ul");
				
				$html_tree .= html_writer::end_tag("li");
			}
		}
		$html_tree .= html_writer::end_tag("ul");
		return html_writer::div($html_tree, "associated_div", array('id'=>"associated_div"));
	}
	
	private function print_competence_for_list_tree($descriptor, $isTeacher, $editmode, $show_examples) {
		$html_tree = html_writer::start_tag("li", array('class'=>($descriptor->associated == 1)?"associated":""));
		if($isTeacher && $editmode==1)
			$html_tree .= html_writer::checkbox("descriptor[]", $descriptor->id, ($descriptor->direct_associated==1)?true:false);
		
		$html_tree .= block_exacomp_get_descriptor_numbering($descriptor).' '.$descriptor->title;
			
		if($show_examples){
			if(!empty($descriptor->examples))
				$html_tree .= html_writer::start_tag("ul");
				
			foreach($descriptor->examples as $example) {
				if(!isset($example->associated)) $example->associated = 0;
				if($example->associated == 1 || ($isTeacher && $editmode==1)) {
					$exampleIcons = " ";
					
					if ($url = block_exacomp_get_file_url($example, 'example_task')) {
						$exampleIcons = html_writer::link($url, $this->pix_icon("i/preview", get_string("preview")),array("target" => "_blank"));
					}
					
					if($example->externalurl){
						$exampleIcons .= html_writer::link(str_replace('&amp;','&',$example->externalurl), $this->pix_icon("i/preview", $example->externalurl),array("target" => "_blank"));
					}elseif($example->externaltask){
						$exampleIcons.= html_writer::link(str_replace('&amp;','&',$example->externaltask), $this->pix_icon("i/preview", $example->externaltask),array("target" => "_blank"));
					}
					
					if ($url = block_exacomp_get_file_url($example, 'example_solution')) {
						$exampleIcons .= $this->print_example_solution_icon($url);
					}		

					$html_tree .= html_writer::tag("li", $example->title . $exampleIcons, array('class'=>($example->associated == 1)?"associated":""));
				}
			}
				
			if(!empty($descriptor->examples))
				$html_tree .= html_writer::end_tag("ul");
		}
		if(!empty($descriptor->children)) {
			$html_tree .= html_writer::start_tag("ul");
			
			foreach($descriptor->children as $child)
				if($child->associated == 1 || ($isTeacher && $editmode==1))
					$html_tree .= $this->print_competence_for_list_tree($child, $isTeacher, $editmode, $show_examples);
			
			$html_tree .= html_writer::end_tag("ul");
		}
		$html_tree .= html_writer::end_tag("li");
		
		return $html_tree;
	}
	function print_statistic_table($courseid, $students, $item, $descriptor=true, $scheme=1){
		$global_scheme = \block_exacomp\global_config::get_scheme_id();
		$global_scheme_values = \block_exacomp\global_config::get_scheme_items();

		if($descriptor)
			list($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
						$self_title, $student_oB_title, $student_iA_title, $teacher_title, 
						$teacher_oB_title, $teacher_iA_title) = block_exacomp_calculate_statistic_for_descriptor($courseid, $students, $item, $scheme);
		else
			list($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
						$self_title, $student_oB_title, $student_iA_title, $teacher_title, 
						$teacher_oB_title, $teacher_iA_title) = block_exacomp_calculate_statistic_for_example($courseid, $students, $item, $scheme);
			
		
		$table = new html_table();
		$table->attributes['class'] = 'statistic';
		$table->border = 3;
		
		$rows = array();
		
		$self_row_header = new html_table_row();
		$self_row_header->attributes['class'] = 'statistic_head';
		
		$empty_cell = new html_table_cell();
		$self_row_header->cells[] = $empty_cell;
		
		$empty_cell = new html_table_cell();
		$self_row_header->cells[] = $empty_cell;
		
		foreach($self as $self_key => $self_value){
			$cell = new html_table_cell();
			$cell->text = ($global_scheme==0)?$self_key:$global_scheme_values[$self_key];
			$self_row_header->cells[] = $cell;
		}
		
		$cell = new html_table_cell();
		$cell->text = "oB";
		$self_row_header->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = "iA";
		$self_row_header->cells[] = $cell;
		
		$rows[] = $self_row_header;
		
		$self_row = new html_table_row();
		$self_row->attributes['class'] = '';
		
		$cell = new html_table_cell();
		$cell->text = "S";
		$self_row->cells[] = $cell;
		
		$empty_cell = new html_table_cell();
		$self_row->cells[] = $empty_cell;
		
		foreach($self as $self_key => $self_value){
			$cell = new html_table_cell();
			$cell->text =($self_value>0)?html_writer::tag('span', $self_value, array('title'=>$self_title[$self_key])):$self_value;
			$self_row->cells[] = $cell;
		}
	
		$cell = new html_table_cell();
		$cell->text = ($student_oB>0)?html_writer::tag('span', $student_oB, array('title'=>$student_oB_title)):$student_oB;
		$self_row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = ($student_iA>0)?html_writer::tag('span', $student_iA, array('title'=>$student_iA_title)):$student_iA;
		$self_row->cells[] = $cell;
		
		$rows[] = $self_row;
		
		$teacher_row_header = new html_table_row();
		$teacher_row_header->attributes['class'] = 'statistic_head';
		
		$empty_cell = new html_table_cell();
		$teacher_row_header->cells[] = $empty_cell;
		
		foreach($teacher as $teacher_key => $teacher_value){
			$cell = new html_table_cell();
			$cell->text = ($global_scheme==0)?$teacher_key:$global_scheme_values[$teacher_key];
			$teacher_row_header->cells[] = $cell;
		}
		
		$cell = new html_table_cell();
		$cell->text = "oB";
		$teacher_row_header->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = "iA";
		$teacher_row_header->cells[] = $cell;

		$rows[] = $teacher_row_header;
		
		$teacher_row = new html_table_row();
		$teacher_row->attributes['class'] = '';
		
		$cell = new html_table_cell();
		$cell->text = "L";
		$teacher_row->cells[] = $cell;
		
		foreach($teacher as $teacher_key => $teacher_value){
			$cell = new html_table_cell();
			$cell->text = ($teacher_value>0)?html_writer::tag('span', $teacher_value, array('title'=>$teacher_title[$teacher_key])):$teacher_value;
			$teacher_row->cells[] = $cell;
		}
		
		$cell = new html_table_cell();
		$cell->text = ($teacher_oB>0)?html_writer::tag('span', $teacher_oB, array('title'=>$teacher_oB_title)):$teacher_oB;
		$teacher_row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = ($teacher_iA>0)?html_writer::tag('span', $teacher_iA, array('title'=>$teacher_iA_title)):$teacher_iA;
		$teacher_row->cells[] = $cell;

		$rows[] = $teacher_row;
		
		$table->data = $rows;
		return html_writer::table($table);
	}
	public function print_example_pool($examples=array()){
		$content = html_writer::tag('h4', get_string('example_pool', 'block_exacomp'));
	
		foreach($examples as $example){
			$content .= html_writer::div($example->title, 'fc-event', array('exampleid'=>$example->exampleid));
		}
	
		return html_writer::div($content, '', array('id'=>'external-events'));
	}

	
	public function print_side_wrap_weekly_schedule(){
		$pool = $this->print_example_pool();
		$calendar = html_writer::div('', '', array('id'=>'calendar'));
		$trash = $this->print_example_trash();
		$clear = html_writer::div('', '', array('style'=>'clear:both'));
		
		return html_writer::div($pool.$calendar.$trash.$clear, '', array('id'=>'wrap'));
	}
	
	public function print_example_trash($trash_examples = array(), $persistent_trash=true){
		$content = html_writer::tag('h4', get_string('example_trash', 'block_exacomp'));
		
		foreach($trash_examples as $example){
			$content .= html_writer::div($example->title, 'fc-event');
		}
	
		if($persistent_trash) $content .= html_writer::empty_tag('input', array('type'=>'button', 'id'=>'empty_trash', 'value'=>get_string('empty_trash', 'block_exacomp')));
		return html_writer::div($content, '', array('id'=>'trash'));
	}
	public function print_course_dropdown($selectedCourse){
		global $DB;
		$content = get_string("choosecourse", "block_exacomp");
		$options = array();
		
		$courses = block_exacomp_get_courseids();
		
		foreach($courses as $course){
			if(block_exacomp_course_has_examples($course)){
				$course_db = $DB->get_record('course', array('id'=>$course));
				$options[$course] = $course_db->fullname;
			}
		}
		
		$url = new block_exacomp\url(g::$PAGE->url, ['pool_course'=>null]);
		$content .= html_writer::select($options, "lis_courses",$selectedCourse, false,
				array("onchange" => "document.location.href='".$url->out()."&pool_course='+this.value;"));
		
		return $content;
	}
	
	public function print_view_example_header(){
		global $PAGE;
		$content = html_writer::tag('button', html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/withsubcat.png'), 
			'title'=> get_string('comp_based', 'block_exacomp'))).' '.get_string('comp_based', 'block_exacomp'), array('type'=>'button', 'id'=>'comp_based', 'name'=> 'comp_based', 'class'=>'view_examples_icon',
			"onclick" => "document.location.href='".$PAGE->url."&style=0';"));
			 
		$content .= html_writer::tag('button', html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/e/bullet_list.png'), 
			'title'=> get_string('examp_based', 'block_exacomp'))).' '.get_string('examp_based', 'block_exacomp'), array('type'=>'button', 'id'=>'examp_based', 'name'=> 'examp_based', 'class'=>'view_examples_icon',
			"onclick" => "document.location.href='".$PAGE->url."&style=1';"));
		
		return html_writer::div($content, '', array('id'=>'view_examples_header'));
	}
	
	public function print_example_based_list_tree($example, $tree, $isTeacher, $editmode, $showexamples = false){
		$html_tree = "";
		$html_tree .= html_writer::start_tag("ul",array("class"=>"collapsibleList"));
		
		$html_tree .= html_writer::start_tag("li", array('class'=>"associated"));
		
		$exampleIcons = " ";
		if ($url = block_exacomp_get_file_url($example, 'example_task')) {
			$exampleIcons = html_writer::link($url, $this->pix_icon("i/preview", get_string("preview")),array("target" => "_blank"));
		}
		 
		if($example->externalurl){
			$exampleIcons .= html_writer::link(str_replace('&amp;','&',$example->externalurl), $this->pix_icon("i/preview", $example->externalurl),array("target" => "_blank"));
		}elseif($example->externaltask){
			$exampleIcons.= html_writer::link(str_replace('&amp;','&',$example->externaltask), $this->pix_icon("i/preview", $example->externaltask),array("target" => "_blank"));
		}
		 
		if ($url = block_exacomp_get_file_url($example, 'example_solution')) {
			$exampleIcons .= $this->print_example_solution_icon($url);
		}
		
		$html_tree .= $example->title . $exampleIcons;
		
		$html_tree .= $this->print_competence_based_list_tree($tree, $isTeacher, $editmode, $showexamples);
		
		$html_tree .= html_writer::end_tag('li');
		$html_tree .= html_writer::end_tag('ul');
		return $html_tree;		
	}
	public function print_pre_planning_storage_students($students, $examples){
		global $COURSE;
	
		$content = html_writer::start_tag('ul');
		foreach($students as $student){
			$student_has_examples = false;
			foreach($student->pool_examples as $example){
				if(in_array($example->exampleid, $examples))
					$student_has_examples = true;
			}
			
			$content .= html_writer::start_tag('li', array('class'=>($student_has_examples)?'has_examples':''));
			$content .= html_writer::empty_tag('input', array('type'=>'checkbox', 'id'=>'student_examp_mm', 'studentid'=>$student->id));
			$content .= html_writer::link( new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid'=>$COURSE->id, 'studentid'=>$student->id)), 
				$student->firstname." ".$student->lastname, array('target'=>'_blank', 'title'=>get_string('to_weekly_schedule', 'block_exacomp')));
			$content .= html_writer::end_tag('li');
		}
		
		$content .= html_writer::end_tag('ul');
		$content .= html_writer::tag('span', html_writer::start_tag('fieldset', array('class'=>'gray')).html_writer::end_tag('fieldset').'Material aus Vorplanungsspeicher erhalten', array('class'=>'pre_planning_storage_legend_gray'));
		$content .= html_writer::tag('span', html_writer::start_tag('fieldset', array('class'=>'blue')).html_writer::end_tag('fieldset').'Noch kein Material erhalten', array('class'=>'pre_planning_storage_legend_blue'));
		return html_writer::div($content, 'external-students', array('id'=>'external-students'));
	}
	public function print_pre_planning_storage_pool(){
		$content = html_writer::tag('h4', get_string('example_pool', 'block_exacomp'));
	
		$content .= html_writer::tag('ul', '', array('id'=>'sortable'));
		return html_writer::div($content, 'external-events', array('id'=>'external-events'));
	}	
	
	public function print_lm_graph_legend() {
		$global_scheme = \block_exacomp\global_config::get_scheme_id();
		$global_scheme_values = \block_exacomp\global_config::get_scheme_items();

		$content = html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","lmoB");
		$content .= ' '.get_string("oB","block_exacomp").' ';

		if($global_scheme != 0){
			$content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","lmnE");
			$content .= ' '.get_string("nE","block_exacomp").' ';
			
			for($i=1;$i<=3;$i++){
				$content .= html_writer::span("&nbsp;&nbsp;&nbsp;&nbsp;","green".$i);
				$content .= ' '.$global_scheme_values[$i].' ';
			}
		}
		
		return $content;
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
	
	public function print_cross_subjects_list($course_crosssubs, $courseid, $isTeacher){
		$content = "<h4>" . get_string('existing_crosssub','block_exacomp') . "</h4>";
		
		if(empty($course_crosssubs))
			$content .= html_writer::div(get_string('no_crosssubjs', 'block_exacomp'), '');
			
		foreach($course_crosssubs as $crosssub){
			$content .= html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id)), $crosssub->title);
			if($isTeacher){
				$content .= html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'editmode'=>1)),$this->pix_icon("i/edit", get_string("edit")), array('class'=>'crosssub-icons'));
				$content .= html_writer::link('', $this->pix_icon("t/delete", get_string("delete")), array("onclick" => "if( confirm('".get_string('confirm_delete', 'block_exacomp')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;"), array('class'=>'crosssub-icons')); 
			}
			$content .= html_writer::empty_tag('br');
		}
		return $content;
	}
	public function print_create_blocking_event(){
		global $USER;
		
		$content = html_writer::tag('h4', get_string('blocking_event', 'block_exacomp'));
		$content .= html_writer::empty_tag('input', array('type'=>'text', 'id'=>'blocking_event_title', 'placeholder'=>get_string('blocking_event_title', 'block_exacomp')));
		$content .= html_writer::empty_tag('input', array('type'=>'button', 'id'=>'blocking_event_create', 'value'=>get_string('blocking_event_create', 'block_exacomp'), 'creatorid'=>$USER->id));
		return html_writer::div($content, '', array('id'=>'blocking_event'));
	}
}
