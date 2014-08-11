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
	public function print_subject_dropdown($subjects, $selectedSubject, $studentid = 0) {
		global $PAGE;
		$content = get_string("choosesubject", "block_exacomp");
		$options = array();
		foreach($subjects as $subject)
			$options[$subject->id] = $subject->title;
		
		$content .= html_writer::select($options, "lis_subjects",$selectedSubject, false,
				array("onchange" => "document.location.href='".$PAGE->url. ($studentid > 0 ? "&studentid=".$studentid : "") ."&subjectid='+this.value;"));
		return $content;
	}
	/**
	 * Prints 2 select inputs for subjects and topics
	 */
	public function print_lis_dropdowns($subjects, $topics, $selectedSubject, $selectedTopic) {
		global $PAGE;

		$content = $this->print_subject_dropdown($subjects, $selectedSubject);
		$content .= html_writer::empty_tag("br");

		$content .= get_string("choosetopic", "block_exacomp").': ';
		$options = array();
		foreach($topics as $topic)
			$options[$topic->id] = (isset($topic->cattitle)?$topic->cattitle.": " :" ")  . $topic->title;
		$content .= html_writer::select($options, "lis_topics", $selectedTopic, false,
				array("onchange" => "document.location.href='".$PAGE->url."&subjectid=".$selectedSubject."&topicid='+this.value;"));

		return $content;
	}
	public function print_lis_metadata_teacher(){
	
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_top';
		
		$rows = array();
		
		$row = new html_table_row();
		
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'comp_grey_97';
		$cell->text = html_writer::tag('b', get_string('instruction', 'block_exacomp'))
			.html_writer::tag('p', get_string('instruction_content', 'block_exacomp'));
		
		$row->cells[] = $cell;
		$rows[] = $row;
		$table->data = $rows;
		
		$content = html_writer::table($table);
		$content .= html_writer::empty_tag('br');
		
		return $content;
	}
	public function print_lis_metadata_student($subject, $topic, $topic_evaluation, $showevaluation, $scheme, $icon = null){
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
			
		$p_content = get_string('reached_topic', 'block_exacomp');
		
		if($scheme == 1)
			$p_content .= "S: " . html_writer::checkbox("topiccomp", 1, ((isset($topic_evaluation->student[$topic->id]))?true:false))
				." Bestätigung L: ".html_writer::checkbox("topiccomp", 1, ((isset($topic->evaluation->teacher[$topic->id]))?true:false), "", array("disabled"=>"disabled"));
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
		
		$row->cells[] = $cell;
		$rows[] = $row;
		
		$table->data = $rows;
		
		return html_writer::table($table).html_writer::empty_tag('br');
	}
	public function print_lis_metadata($schooltype, $subject, $topic, $cat){
		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_info';
		
		$rows = array();
		
		$row = new html_table_row();
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('subject_singular', 'block_exacomp'), 'exabis_comp_top_small')
			. html_writer::tag('b', $schooltype);
		
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('comp_field_idea', 'block_exacomp'), 'exabis_comp_top_small')
			. html_writer::tag('b', $subject->numb." - ".$subject->title);
		
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('comp', 'block_exacomp'), 'exabis_comp_top_small')
			. html_writer::tag('b', $topic->title);
		
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('progress', 'block_exacomp'), 'exabis_comp_top_small')
			. html_writer::tag('b', (($cat)?$cat->title:''));
		
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = html_writer::span(get_string('tab_competence_overview', 'block_exacomp'), 'exabis_comp_top_small')
			. html_writer::tag('b', substr($schooltype, 0,1).$subject->numb.(($cat)?".".$cat->sourceid:''));
		
		$row->cells[] = $cell;
		
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
	public function print_competence_overview_LIS_student_topics($subs, &$row, &$columns, &$column_count, $scheme){
		global $USER, $COURSE;
		foreach($subs as $topic){
			if(isset($topic->subs))
				print_competence_overview_LIS_student_topics($topic->subs);
				
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
						$cell->text .= 'L:'.$this->generate_select('data', $descriptor->id, 'competencies', $USER, "teacher", $scheme, true)
						.html_writer::empty_tag('br')
						."S:".$this->generate_select('data', $descriptor->id, 'competencies', $USER,"student", $scheme);

					//$activities = block_exacomp_get_activities($descriptor->id, $COURSE->id);
					$cm_mm = block_exacomp_get_course_module_association($COURSE->id);
					$course_mods = get_fast_modinfo($COURSE->id)->get_cms();
		
					if(isset($data->cm_mm->competencies[$descriptor->id])) {
						$activities_student = array();
						foreach($cm_mm->competencies[$descriptor->id] as $cmid)
							$activities_student[] = $course_mods[$cmid];
						if($activities_student && $stdicon = block_exacomp_get_icon_for_user($activities_student, $USER)){
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
		global $CFG;
	
		$headFlag = false;
	
		$context = context_course::instance($courseid);
	
		$table = new html_table();
		$table->attributes['class'] = 'competence_grid';
		$head = array();
	
		$schema = ($courseid == 0) ? 1 : block_exacomp_get_grading_scheme($courseid);
		$satisfied = ceil($schema/2);
	
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
				$head = array_merge($head,$niveaus);
				$table->head = $head;
				$headFlag = true;
			}
	
			foreach($skill as $topicid => $topic) {
				$row = new html_table_row();
	
				$cell2 = new html_table_cell();
				$cell2->text = html_writer::tag("span",html_writer::tag("span",$topics[$topicid],array('class'=>'rotated-text__inner')),array('class'=>'rotated-text'));
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
							if (has_capability('block/exacomp:teacher', $context)) {
								if(isset($descriptor->teachercomp) && array_key_exists($descriptor->id, $selection)) {
									$compString .= "L: ";
									if($schema == 1) {
										$compString .= html_writer::checkbox("data[".$descriptor->id."][".$studentid."][teacher]", 1,$descriptor->teachercomp).'&nbsp; ';
										$compString .= " S: ". html_writer::checkbox("studentdata[".$topicid."][".$descriptor->id."]", 1,($descriptor->studentcomp >= $satisfied),"",array("disabled"=>"disabled")).'&nbsp; ';
									}else {
										$options = array();
										for($i=0;$i<=$schema;$i++)
											$options[] = $i;
	
											$name = "data[".$descriptor->id."][".$studentid."][teacher]";
											$compString .= html_writer::select($options, $name, $descriptor->teachercomp, false);
	
											//$compString .= "&nbsp;S: " . html_writer::select($options,"student".$name, $descriptor->studentcomp,false,array("disabled"=>"disabled")).'&nbsp; ';
											$compString .= "&nbsp;S: " . html_writer::checkbox("student".$name, 0,$descriptor->studentcomp >= $satisfied,"",array("disabled"=>"disabled")).'&nbsp; ';
									}
									}
										
								} else if(has_capability('block/exacomp:student', $context) && array_key_exists($descriptor->id, $selection)) {
								$compString.="S: ";
								if($schema == 1) {
								$compString .= html_writer::checkbox("data[".$descriptor->id."][".$studentid."][student]", 1,$descriptor->studentcomp).'&nbsp; ';
									
								$compString .= "&nbsp;L: " . html_writer::checkbox("studentdata[".$studentid."][".$descriptor->id."]", 0,$descriptor->teachercomp >= $satisfied,"",array("disabled"=>"disabled")).'&nbsp; ';
								} else {
								$options = array();
								for($i=0;$i<=$schema;$i++)
									$options[] = $i;
										
									$name = "data[".$topicid."][".$descriptor->id."][student]";
									//$compString .= html_writer::select($options, $name, $descriptor->studentcomp, false);
									$compString .= html_writer::checkbox("data[".$descriptor->id."][".$studentid."]", $schema,$descriptor->studentcomp).'&nbsp; ';
	
									$compString .= "&nbsp;L: " . (($descriptor->teachercomp) ? $descriptor->teachercomp : 0);
								}
	
									
								}
								if(isset($descriptor->icon))
									$compString .= $descriptor->icon;
	
								$text = "<br />".$descriptor->title;
								if(array_key_exists($descriptor->id, $selection)) {
									$text = html_writer::link(new moodle_url("/blocks/exacomp/assign_competencies.php",array("courseid"=>$courseid,"subjectid"=>$topicid,"topicid"=>$descriptor->id)),$text);
								}
	
								if(isset($descriptor->examples)) {
									$text .= '<br/>';
									foreach($descriptor->examples as $example) {
										$img = '<img src="pix/i_11x11.png" alt="Beispiel" />';
										if($example->task)
											$text .= "<a target='_blank' alt='".$example->title."' title='".$example->title."' href='".$example->task."'>".$img."</a>";
										if($example->externalurl)
											$text .= "<a target='_blank' alt='".$example->title."' title='".$example->title."' href='".$example->externalurl."'>".$img."</a>";
									}
								}
								$compString .= $text;
	
								/*else {
								 if(isset($descriptor->teachercomp) && $descriptor->teachercomp)
									//$compString .= "T";
								$cssClass = "teachercomp";
								}
							 //if(isset($descriptor->studentcomp) && $descriptor->studentcomp)
									//	$compString .= "S";
								*/
	
								if(count($data[$skillid][$topicid][$niveauid]) > 1)
									$compString .= html_writer::tag("hr","");
	
								if(!isset($descriptor->teachercomp))
									$allTeachercomps = false;
								if(!isset($descriptor->studentcomp) || isset($descriptor->teachercomp))
									$allStudentcomps = false;
	
								$cssClass = (isset($descriptor->teachercomp) && $descriptor->teachercomp >= $satisfied) ? "content competenceok" : ((isset($descriptor->studentcomp) && $descriptor->studentcomp >= $satisfied) ? "content competenceyellow" : "content ");
								$compdiv .= html_writer::tag('div', $compString,array('class'=>$cssClass));
						}
	
						if(count($data[$skillid][$topicid][$niveauid]) == 1)
							$cell->attributes['class'] = $cssClass;
						else if($allStudentcomps)
							$cell->attributes['class'] = "content competenceyellow";
						else if($allTeachercomps)
							$cell->attributes['class'] = "content competenceok";
	
						$cell->text = $compdiv;
	
						$row->cells[] = $cell;
					} else
						$row->cells[] = "";
				}
				$rows[] = $row;
			}
			//$rows[] = $row;
		}
		$table->data = $rows;
	
		return html_writer::tag("div", html_writer::table($table), array("id"=>"exabis_competences_block"));
	}
	public function print_competence_overview_form_start($selectedTopic=null, $selectedSubject=null){
		global $PAGE, $COURSE;
		$url_params = array();
		$url_params['action'] = 'save';
		if(isset($selectedTopic))	
			$url_params['topicid'] = $selectedTopic->id;
		if(isset($selectedSubject))
			$url_params['subjectid'] = $selectedSubject->id;
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
		$cell->text = html_writer::tag('h1', 'Teilkompetenzen', array('style'=>'float:right;'));

		$row->cells[] = $cell;

		$column_count = 0;
		//print header
		foreach($subjects as $subject){
			$this->print_competence_overview_LIS_student_topics($subject->subs, $row, $columns, $column_count, $scheme);
		}
		$rows[] = $row;

		//print subheader
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

		//print examples
		foreach($examples as $example){
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = $example->title;
				
			$img = html_writer::img('pix/i_11x11.png', 'Beispiel');
			if(isset($example->task))
				$cell->text .= html_writer::link($example->task, $img, array('target'=>'_blank'));
			elseif(isset($example->externalurl))
			$cell->text .= html_writer::link($example->externalurl, $img);
				
			$row->cells[] = $cell;
				
			$cell = new html_table_cell();
			$cell->text = (isset($example->tax))?$example->tax:'';
				
			$row->cells[] = $cell;
				
			$exampleInfo = $DB->get_record(DB_EXAMPLEEVAL, array("exampleid" => $example->id, "studentid" => $USER->id, "courseid" => $COURSE->id));
				
			$cell = new html_table_cell();
			$cell->text = html_writer::img('pix/subjects_topics.gif', "edit", array('onclick'=>'AssignVisibility('.$example->id."2".')', 'style'=>'cursor:pointer;'));
				
			$dates = (isset($exampleInfo->starttime) && isset($exampleInfo->endtime))?date("d.m.Y", $exampleInfo->starttime)
			." - ".date("d.m.Y", $exampleInfo->endtime):"";
			$div_1 = html_writer::div($dates, '', array('id'=>'exabis_assign_student_data'.$example->id."2"));
				
			$cell->text .= $div_1;
				
			$content = get_string('assignfrom','block_exacomp');
			$content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][starttime]', 'readonly' => 'readonly',
					'value' => (isset($exampleInfo->starttime) ? date("Y-m-d",$exampleInfo->starttime) : null)));
			$content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deletestart'=>1)),
					html_writer::img('pix/x_11x11.png', 'delete'));
			$content .= html_writer::empty_tag('br');
			$content .= get_string('assignuntil','block_exacomp');
			$content .= ' '.html_writer::empty_tag('input', array('class' => 'datepicker', 'type' => 'text', 'name' => 'dataexamples[' . $example->id . '][' . $USER->id . '][endtime]', 'readonly' => 'readonly',
					'value' => (isset($exampleInfo->endtime) ? date("Y-m-d",$exampleInfo->endtime) : null)));
			$content .= ' '.html_writer::link(new moodle_url($PAGE->url, array('exampleid'=>$example->id, 'deleteend'=>1)),
					html_writer::img('pix/x_11x11.png', 'delete'));

			$div_2 = html_writer::div($content, 'exabis_assign_student', array('id'=>'exabis_assign_student'.$example->id."2"));
			$cell->text .= $div_2;
				
			$row->cells[] = $cell;
				
			$cell = new html_table_cell();
			$options = array();
			$options['self'] = get_string('assignmyself','block_exacomp');
			$options['studypartner'] = get_string('assignlearningpartner','block_exacomp');
			$options['studygroup'] = get_string('assignlearninggroup','block_exacomp');
			$options['teacher'] = get_string('assignteacher','block_exacomp');

			$cell->text = html_writer::img('pix/subjects_topics.gif', 'edit', array('onclick'=>'AssignVisibility('.$example->id."1".')', 'style'=>'cursor:pointer;'));
				
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
				$studentsCount = 0;

				$evaluationRow = new html_table_row();
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = 2;
				$evaluationRow->cells[] = $emptyCell;

				foreach($students as $student) {
					$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
						
					$firstCol = new html_table_cell();
					$firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
					$secCol = new html_table_cell();
					$secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
						
					if($role == ROLE_TEACHER) {
						$firstCol->text = get_string('studentshortcut','block_exacomp');
						$secCol->text = get_string('teachershortcut','block_exacomp');
					} else {
						$firstCol->text = get_string('teachershortcut','block_exacomp');
						$secCol->text = get_string('studentshortcut','block_exacomp');
					}
						
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
					'course_mods' => get_fast_modinfo($courseid)->get_cms(),
					'selected_topicid' => null,
					'showalldescriptors' => block_exacomp_get_settings_by_course($courseid)->show_all_descriptors
			);
			$this->print_topics($rows, 0, $subject->subs, $data, $students);
			$table->data = $rows;
		}

		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),'', array('id'=>'exabis_save_button'));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		return $table_html.html_writer::end_tag('form');
		
		//return html_writer::tag("form", $table_html, array("id" => "assign-competencies", "method" => "post", "action" => $PAGE->url . "&action=save"));
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

			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!$version || ($version && $topic->id == LIS_SHOW_ALL_TOPICS)));

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
			($version)?$outputidCell->text = $outputid:$outputidCell->text='';
			$topicRow->cells[] = $outputidCell;

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputid.$outputname,"desctitle");
			$topicRow->cells[] = $outputnameCell;

			foreach($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
				$studentCell->attributes['class'] = 'colgroup colgroup-' . $columnGroup;
				$studentCell->colspan = $studentsColspan;

				if(isset($data->cm_mm->topics[$topic->id]) || $data->showalldescriptors) {
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
						$studentCellEvaluation->text = $this->generate_checkbox("datatopics", $topic->id,
								'topics', $student, ($evaluation == "teacher") ? "student" : "teacher",
								$data->scheme, true);

					$studentCell->text = $this->generate_checkbox("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme);
				}
				/*
				 * if scheme != 1, !version: print select
				* if scheme != 1, version = LIS, role = teacher
				*/
				elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

					$studentCell->text = $this->generate_select("datatopics", $topic->id, 'topics', $student, $evaluation, $data->scheme);
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

				// TIPP
				if(isset($student->activities_topics->teacher[$topic->id])){
					$icon_img = html_writer::empty_tag('img', array('src'=>"pix/tipp.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
					$studentCell->text .= html_writer::span($icon_img, 'exabis-tooltip', array('title'=>get_string('teacher_tipp_description', 'block_exacomp')));
				}
				
				if($data->showevaluation)
					$topicRow->cells[] = $studentCellEvaluation;
				}
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
			$checkboxname = "data";
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

					$studentCell->text = $this->generate_select($checkboxname, $descriptor->id, 'competencies', $student, $evaluation, $data->scheme);
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
				
				// TIPP
				if(isset($student->activities_competencies->teacher[$descriptor->id])){
					$icon_img = html_writer::empty_tag('img', array('src'=>"pix/tipp.png", "alt"=>get_string('teacher_tipp', 'block_exacomp')));
					$studentCell->text .= html_writer::span($icon_img, 'exabis-tooltip', array('title'=>get_string('teacher_tipp_description', 'block_exacomp')));
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

					$studentCell->text = html_writer::empty_tag("input",array("type" => "hidden", "value" => 0, "name" => $checkboxname . "[" . $example->id . "][" . $student->id . "][" . (($evaluation == "teacher") ? "teacher" : "student") . "]"));
					/*
					 * if scheme == 1: print checkbox
					* if scheme != 1, role = student, version = LIS
					*/
					if($data->scheme == 1 || ($data->scheme != 1 && $data->role == ROLE_STUDENT && $version)) {
						if($data->showevaluation)
							$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);
							
						if($data->role == ROLE_STUDENT) {
							$studentCell->text .= get_string('assigndone','block_exacomp');
							$studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme);
								
							$studentCell->text .= $this->print_student_example_evaluation_form($example->id, $student->id, $data->courseid);
						}
						else
							$studentCell->text .= $this->generate_checkbox($checkboxname, $example->id, 'examples', $student, $evaluation, $data->scheme);
					}
					/*
					 * if scheme != 1, !version: print select
					* if scheme != 1, version = LIS, role = teacher
					*/
					elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
						if($data->showevaluation)
							$studentCellEvaluation->text = $this->generate_select($checkboxname, $example->id, 'examples', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true);

						$studentCell->text .= $this->generate_select($checkboxname, $example->id, 'examples', $student, $evaluation);
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

		return html_writer::tag("p", $legend);
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
	public function generate_checkbox($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null) {
		return html_writer::checkbox(
				((isset($activityid))?$name . '[' .$compid .'][' . $student->id .'][' . $evaluation . '][' . $activityid . ']'
				:$name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']'),
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
	public function generate_select($name, $compid, $type, $student, $evaluation, $scheme, $disabled = false, $activityid = null) {
		$options = array();
		for($i=0;$i<=$scheme;$i++)
			$options[] = $i;

		return html_writer::select(
				$options,
				((isset($activityid))? $name . '[' . $compid . '][' . $student->id . '][' . $evaluation . '][' . $activityid . ']'
				: $name . '[' . $compid . '][' . $student->id . '][' . $evaluation . ']'),
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
		$innerdiv = html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));

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

		$input_activities = html_writer::checkbox('uses_activities', 1, $settings->uses_activities == 1, get_string('uses_activities', 'block_exacomp'))
			.html_writer::empty_tag('br');

		$input_descriptors = html_writer::checkbox('show_all_descriptors',1,$settings->show_all_descriptors == 1, get_string('show_all_descriptors', 'block_exacomp'))
			.html_writer::empty_tag('br');
		
		$input_examples = html_writer::checkbox('show_all_examples', 1, $settings->show_all_examples == 1, get_string('show_all_examples', 'block_exacomp'))
			.html_writer::empty_tag('br');

		$input_detailpage = html_writer::checkbox('usedetailpage', 1, $settings->usedetailpage==1, get_string('usedetailpage', 'block_exacomp'))
			.html_writer::empty_tag('br');
			
		$input_submit = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save', 'admin')));

		$hiddenaction = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'save_coursesettings'));

		$div = html_writer::div(html_writer::tag('form',
				$saved.$input_grading.$input_activities.$input_descriptors.$input_examples.$input_detailpage.$hiddenaction.$input_submit,
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

		return $content;
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
	public function print_courseselection($tree, $subjects, $topics_activ){
		global $PAGE;

		$table = new html_table();
		$table->attributes['class'] = 'exabis_comp_comp';
		$rowgroup = 0;
		$rows = array();
		foreach($tree as $subject){
			if(isset($subjects[$subject->id])){
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
				$this->print_topics_courseselection($rows, 0, $subject->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
			}
		}

		$table->data = $rows;


		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		return html_writer::tag("form", $table_html, array("method" => "post", "action" => $PAGE->url . "&action=save", "id" => "course-selection"));
	}
	public function print_topics_courseselection(&$rows, $level, $topics, &$rowgroup, $rowgroup_class = '', $topics_activ){
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
			$cell->text = html_writer::checkbox('data['.$topic->id.']', $topic->id, ((isset($topics_activ[$topic->id]))?true:false), '', array('class'=>'topiccheckbox'));
			$topicRow->cells[] = $cell;
				
			$rows[] = $topicRow;
				
			if (!empty($topic->subs)) {
				$this->print_topics_courseselection($rows, $level+1, $topic->subs, $rowgroup, $sub_rowgroup_class, $topics_activ);
			}
		}
	}
	public function print_activity_legend(){
		return html_writer::label(get_string("explaineditactivities_subjects", "block_exacomp"), '').html_writer::empty_tag('br');
		
	}
	public function print_activity_footer($niveaus, $modules, $selected_niveaus=array(), $selected_modules=array()){
		global $PAGE;
		$content = '';
		
		$form_content = '';
		if(!empty($niveaus)){
			$selected = '';
			if(in_array('0', $selected_niveaus) || empty($selected_niveaus))
				$selected = ' selected';
			
			$options = html_writer::tag('option'.$selected, 'all niveaus', array('value'=>0));
			foreach($niveaus as $niveau){
				$selected = '';
				if(in_array($niveau->id, $selected_niveaus))
					$selected = ' selected';
					
				$options .= html_writer::tag('option'.$selected, $niveau->title, array('value'=>$niveau->id));
			}
			$select = html_writer::tag('select multiple', $options, array('name'=>'niveau_filter[]'));
			$form_content .= html_writer::div(html_writer::tag('h3', get_string('niveau_filter', 'block_exacomp')).$select, '');
		}

		if(!empty($modules)){
			$selected = '';
			if(in_array('0', $selected_modules) || empty($selected_modules))
				$selected = ' selected';
				
			$options = html_writer::tag('option'.$selected, 'all modules', array('value'=>0));
			foreach($modules as $module){
				$selected = '';
				if(in_array($module->id, $selected_modules))
					$selected = ' selected';
					
				$options .= html_writer::tag('option'.$selected, $module->name, array('value'=>$module->id));
			}
			$select = html_writer::tag('select multiple', $options, array('name'=>'module_filter[]'));
			$form_content .= html_writer::div(html_writer::tag('h3', get_string('module_filter', 'block_exacomp')).$select, '');
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
			$cell->text = html_writer::tag('h4', $subject->title);
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
				$moduleCell->text = html_writer::checkbox('topicdata[' . $module->id . '][' . $topic->id . ']', "", (in_array($topic->id, $module->topics))?true:false);

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
		global $version, $PAGE, $USER;

		foreach($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor);

			$padding = ($level) * 20 + 4;

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
			$link = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), 'To award this badge in exacomp you have to add the "Manual issue by role" criteria');
			$content .= html_writer::div($link);
		}else{
			if(empty($descriptors)){
				$link = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), 'To award this badge in exacomp you have to configure competencies');
				$content .= html_writer::div($link);
			}else{
				$content_form = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$badge->id))
					.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'activate', 'value'=>1))
					.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()))
					.html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'return', 'value'=>new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id))))
					.'This badge is ready to be activated: '
					.html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('activate', 'badges')));
					
				$form = html_writer::tag('form', $content_form, array('method'=>'post', 'action'=>new moodle_url('/badges/action.php')));
				$content .= html_writer::div($form, '', array('style'=>'padding-bottom:20px;'));
				
				$link1 = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), 'configure badges' );
				$link2 = html_writer::link(new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id)), 'configure competences');
				
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
		$row = new html_table_row();
		$row->attributes['class'] = 'heading r0';
		
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'category catlevel1';
		$cell->attributes['scope'] = 'col';
		$cell->colspan = 2;
		$cell->text = html_writer::tag('h2', $COURSE->fullname);
		
		$row->cells[] = $cell;
		
		$rows[] = $row;
		
		$row = new html_table_row();
		$cell = new html_table_cell();
		$row->cells[] = $cell;
		$cell = new html_table_cell();
		$cell->attributes['class'] = 'ec_tableheadwidth';
		$cell->text = html_writer::link(new moodle_url('/badges/edit.php', array('id'=>$badge->id, 'action'=>'details')), $badge->name);
		$row->cells[] = $cell;
		$rows[] = $row;
		
		$rowgroup = 0;
		//print tree
		foreach($subjects as $subject){
			$row = new html_table_row();
			$row->attributes['class'] = 'ec_heading';
			$cell = new html_table_cell();
			$cell->colspan = 2;
			$cell->text = html_writer::tag('h4', $subject->title);
			$row->cells[] = $cell;
			$rows[] = $row;
			
			$this->print_topics_badges($rows, 0, $subject->subs, $rowgroup, $badge);
		}
		
		$table->data = $rows;
		
		$table_html = html_writer::div(html_writer::table($table), 'grade-report-grader');
		$div = html_writer::tag("div", html_writer::tag("div", $table_html, array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$div .= html_writer::div(html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp'))), '', array('id'=>'exabis_save_button'));
		
		return html_writer::tag('form', $div, array('id'=>'edit-activities','action'=> new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$COURSE->id, 'badgeid'=>$badge->id, 'action'=>'save')), 'method'=>'post'));
		
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
		global $version, $PAGE, $USER;

		foreach($descriptors as $descriptor) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor);

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
		return html_writer::label(get_string("no_topics_selected", "block_exacomp"), '');
	}
	public function print_no_activities_warning(){
		return html_writer::label(get_string("no_activities_selected", "block_exacomp"), '');
	}
	public function print_detail_legend($showevaluation){
		global $OUTPUT, $COURSE;

		$link = new moodle_url("/blocks/exacomp/competence_detail.php",array("courseid" => $COURSE->id, "showevaluation" => (($showevaluation) ? "0" : "1")));
		$evaluation = $OUTPUT->box_start();
		$evaluation .= get_string('detail_description','block_exacomp');
		$evaluation .= html_writer::empty_tag("br");
		$evaluation .= ($showevaluation) ? get_string('hideevaluation','block_exacomp',$link->__toString()) : get_string('showevaluation','block_exacomp',$link->__toString());
		$evaluation .= $OUTPUT->box_end();

		return $evaluation;
	}
	public function print_detail_content($activities, $courseid, $students, $showevaluation, $role, $scheme = 1){
		global $PAGE;

		$rowgroup = 0;
		$table = new html_table();
		$rows = array();
		$studentsColspan = $showevaluation ? 2 : 1;
		$colspan = 0;
		$table->attributes['class'] = 'exabis_comp_comp';

		/* SUBJECTS */
		foreach($activities as $activity){
			$activityRow = new html_table_row();
			$activityRow->attributes['class'] = 'highlight';
			
			$title = new html_table_cell();
			$title->text = html_writer::tag('b', $activity->title);
			
			$activityRow->cells[] = $title;
			
			$studentsCount = 0;
	
			foreach($students as $student) {
				$studentCell = new html_table_cell();
				$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);

				$studentCell->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
				$studentCell->colspan = $studentsColspan;
				$studentCell->text = fullname($student);

				$activityRow->cells[] = $studentCell;
			}
			
			$colspan = $studentsCount;
			
			$rows[] = $activityRow;
		
			if($showevaluation) {
				$studentsCount = 0;

				$evaluationRow = new html_table_row();
				$emptyCell = new html_table_cell();
				$evaluationRow->cells[] = $emptyCell;

				foreach($students as $student) {
					$columnGroup = floor($studentsCount++ / STUDENTS_PER_COLUMN);
						
					$firstCol = new html_table_cell();
					$firstCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
					$secCol = new html_table_cell();
					$secCol->attributes['class'] = 'exabis_comp_top_studentcol colgroup colgroup-' . $columnGroup;
						
					if($role == ROLE_TEACHER) {
						$firstCol->text = get_string('studentshortcut','block_exacomp');
						$secCol->text = get_string('teachershortcut','block_exacomp');
					} else {
						$firstCol->text = get_string('teachershortcut','block_exacomp');
						$secCol->text = get_string('studentshortcut','block_exacomp');
					}
						
					$evaluationRow->cells[] = $firstCol;
					$evaluationRow->cells[] = $secCol;
				}
				$rows[] = $evaluationRow;
				$colspan += $studentsCount;
			}
			
			foreach($activity->subs as $subject) {
				if(!$subject->subs)
					continue;
	
				//for every subject
				$subjectRow = new html_table_row();
				$subjectRow->attributes['class'] = 'highlight';
	
				//subject-title
				$title = new html_table_cell();
				$title->text = html_writer::tag('b',$subject->title);
	
				$subjectRow->cells[] = $title;
	
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = $colspan;
				
				$subjectRow->cells[] = $emptyCell;
				
				$rows[] = $subjectRow;
	
				/* TOPICS */
				//for every topic
				$data = (object)array(
						'rowgroup' => &$rowgroup,
						'courseid' => $courseid,
						'showevaluation' => $showevaluation,
						'role' => $role,
						'scheme' => $scheme,
						'activityid' => $activity->id,
						'selected_topicid' => null
				);
				$this->print_detail_topics($rows, 0, $subject->subs, $data, $students, $colspan);
				$table->data = $rows;
			}
		}
		$table_html = html_writer::tag("div", html_writer::tag("div", html_writer::table($table), array("class"=>"exabis_competencies_lis")), array("id"=>"exabis_competences_block"));
		$table_html .= html_writer::div(html_writer::tag("input", "", array("name" => "btn_submit", "type" => "submit", "value" => get_string("save_selection", "block_exacomp"))),' ', array('id'=>'exabis_save_button'));
		$table_html .= html_writer::tag("input", "", array("name" => "open_row_groups", "type" => "hidden", "value" => (optional_param('open_row_groups', "", PARAM_TEXT))));

		
		
		return html_writer::tag("form", $table_html, array("id" => "competence-detail", "method" => "post", "action" => new moodle_url($PAGE->url, array('action'=>'save'))));
		
	}
	public function print_detail_topics(&$rows, $level, $topics, &$data, $students, $colspan, $rowgroup_class = '') {
		global $version;

		//$padding = ($version) ? ($level-1)*20 :  ($level-2)*20+12;
		$padding = $level * 20 + 12;
		$evaluation = ($data->role == ROLE_TEACHER) ? "teacher" : "student";

		foreach($topics as $topic) {
			list($outputid, $outputname) = block_exacomp_get_output_fields($topic);
			$studentsColspan = 1;

			$hasSubs = (!empty($topic->subs) || !empty($topic->descriptors) && (!$version || ($version && $topic->id == LIS_SHOW_ALL_TOPICS)));

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

			$outputnameCell = new html_table_cell();
			$outputnameCell->attributes['class'] = 'rowgroup-arrow';
			$outputnameCell->style = "padding-left: ".$padding."px";
			$outputnameCell->text = html_writer::div($outputid.$outputname,"desctitle");
			
			$topicRow->cells[] = $outputnameCell;

			if($topic->used){
				$studentsCount = 0;
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
							$studentCellEvaluation->text = $this->generate_checkbox("datatopics", $topic->id,
									'activities_topics', $student, ($evaluation == "teacher") ? "student" : "teacher",
									$data->scheme, true, $data->activityid);
	
						$studentCell->text = $this->generate_checkbox("datatopics", $topic->id, 'activities_topics', $student, $evaluation, $data->scheme, false, $data->activityid);
					}
					/*
					 * if scheme != 1, !version: print select
					* if scheme != 1, version = LIS, role = teacher
					*/
					elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
						if($data->showevaluation)
							$studentCellEvaluation->text = $this->generate_select("datatopics", $topic->id, 'activities_topics', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->activityid);
	
						$studentCell->text = $this->generate_select("datatopics", $topic->id, 'activities_topics', $student, $evaluation, $data->scheme, false, $data->activityid);
					}
	
					if($data->showevaluation)
						$topicRow->cells[] = $studentCellEvaluation;
	
					$topicRow->cells[] = $studentCell;
				}
			}
			else{
				$emptyCell = new html_table_cell();
				$emptyCell->colspan = $colspan;
				$topicRow->cells[] = $emptyCell;
			}

			$rows[] = $topicRow;

			if (!empty($topic->descriptors)) {
				$this->print_detail_descriptors($rows, $level+1, $topic->descriptors, $data, $students, $sub_rowgroup_class);
			}

			if (!empty($topic->subs)) {
				$this->print_detail_topics($rows, $level+1, $topic->subs, $data, $students, $sub_rowgroup_class);
			}
		}
	}

	function print_detail_descriptors(&$rows, $level, $descriptors, &$data, $students, $rowgroup_class) {
		global $version, $PAGE, $USER;

		$evaluation = ($data->role == ROLE_TEACHER) ? "teacher" : "student";

		foreach($descriptors as $descriptor) {
			$checkboxname = "data";
			list($outputid, $outputname) = block_exacomp_get_output_fields($descriptor);
			$studentsCount = 0;

			$padding = ($level) * 20 + 4;

			$this_rowgroup_class = $rowgroup_class;
			
			$descriptorRow = new html_table_row();
			$descriptorRow->attributes['class'] = 'exabis_comp_aufgabe ' . $this_rowgroup_class;

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
						$studentCellEvaluation->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'activities_competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true, $data->activityid);

					$studentCell->text = $this->generate_checkbox($checkboxname, $descriptor->id, 'activities_competencies', $student, $evaluation, $data->scheme, false, $data->activityid);
				}
				/*
				 * if scheme != 1, !version: print select
				* if scheme != 1, version = LIS, role = teacher
				*/
				elseif(!$version || ($version && $data->role == ROLE_TEACHER)) {
					if($data->showevaluation)
						$studentCellEvaluation->text = $this->generate_select($checkboxname, $descriptor->id, 'activities_competencies', $student, ($evaluation == "teacher") ? "student" : "teacher", $data->scheme, true,  $data->activityid);

					$studentCell->text = $this->generate_select($checkboxname, $descriptor->id, 'activities_competencies', $student, $evaluation, $data->scheme, false, $data->activityid);
				}

				if($data->showevaluation)
					$descriptorRow->cells[] = $studentCellEvaluation;

				$descriptorRow->cells[] = $studentCell;
			}

			$rows[] = $descriptorRow;
		}
	}
	function print_competence_profile_metadata($student) {
		global $OUTPUT;
		
		$namediv = html_writer::div(html_writer::tag('b',$student->firstname . ' ' . $student->lastname)
			.html_writer::div(get_string('name', 'block_exacomp'), ''), '');
		
		$imgdiv = html_writer::div($OUTPUT->user_picture($student,array("size"=>100)), '');
		
		(!empty($student->city))?$citydiv = html_writer::div($student->city
			.html_writer::div(get_string('city', 'block_exacomp'), ''), ''):$citydiv ='';
			
		return html_writer::div($namediv.$imgdiv.$citydiv, 'competence_profile_metadata');
	}
function print_competene_profile_overview($student, $courses) {
		
		$overviewcontent = html_writer::tag('h2', 'Übersicht');
		$table = $this->print_competence_profile_overview_table($student, $courses);	
		$overviewcontent .= $table;
		
		$teachercomp = 0;
		$studentcomp = 0;
		$pendingcomp = 0;
		foreach($courses as $course){
			$course_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id));
			$teachercomp += $course_data[0];
			$studentcomp += $course_data[1];
			$pendingcomp += $course_data[2];
		}
		$subjects = block_exacomp_get_subjects_for_radar_graph($student->id);
		$overviewcontent .= html_writer::div($this->print_radar_graph($subjects,0),"competence_profile_radargraph");
		$overviewcontent .= html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, 0),"competence_profile_piegraph");
		
		return html_writer::div($overviewcontent, 'competence_profile_overview');
	}
	function print_competence_profile_overview_table($student, $courses){
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
		
		foreach($courses as $course){
			$statistics = block_exacomp_get_course_competence_statistics($course->id, $student, block_exacomp_get_grading_scheme($course->id));
			$pie_data = block_exacomp_get_competencies_for_pie_chart($course->id, $student, block_exacomp_get_grading_scheme($course->id));
			
			$row = new html_table_row();
			$cell = new html_table_cell();
			$cell->text = $course->fullname;
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
			$cell->text = html_writer::div(
				html_writer::div(html_writer::div('', 'lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 'lbmittelwertcontainer')
				.html_writer::div('', '', array('style'=>'background:url(\'pix/balkenfull.png\') no-repeat left center; height:27px; width:'.$perc_reached.'%;')), 
				'ladebalken', array('style'=>'background:url(\'pix/balkenleer.png\') no-repeat left center;'));
			$row->cells[] = $cell;
			
			$total_total +=  $statistics[0];
			$total_reached += $statistics[1];
			$total_average += $statistics[2];
			
			$rows[] = $row;
		}
		
		$row = new html_table_row();
		$cell = new html_table_cell();
		$cell->text = get_string('allcourses', 'block_exacomp');
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = $total_reached;
		$row->cells[] = $cell;
		
		$cell = new html_table_cell();
		$cell->text = $total_total;
		$row->cells[] = $cell;
		
		$perc_average = $total_average/$total_total*100;
		$perc_reached = $total_reached/$total_total*100;
		$cell = new html_table_cell();
		$cell->text = $cell->text = html_writer::div(
				html_writer::div(html_writer::div('', 'lbmittelwert', array('style'=>'width:'.$perc_average.'%;')), 'lbmittelwertcontainer')
				.html_writer::div('', '', array('style'=>'background:url(\'pix/balkenfull.png\') no-repeat left center; height:27px; width:'.$perc_reached.'%;')), 
				'ladebalken', array('style'=>'background:url(\'pix/balkenleer.png\') no-repeat left center;'));
			
		$row->cells[] = $cell;
	
		$rows[] = $row;
		$table->data = $rows;
		return html_writer::table($table);
	}
	
	function print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $courseid){
		
		$height = $width = 150;
		$content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvas_doughnut".$courseid, "height" => $height, "width" => $width)),'piegraph',array("style" => "width:15%"));
		$content .= '
			<script>
			var pieChartData = [
			{
				value:'.$pendingcomp.',
				color:"#F7464A",
      	 	 	highlight: "#FF5A5E",
        		label: "'.get_string('pendingcomp', 'block_exacomp').'"
			},
			{
				value: '.$teachercomp.',
        		color: "#46BFBD",
        		highlight: "#5AD3D1",
        		label: "'.get_string('teachercomp', 'block_exacomp').'"
			},
			{
				value: '.$studentcomp.',
        		color: "#FDB45C",
        		highlight: "#FFC870",
        		label: "'.get_string('studentcomp', 'block_exacomp').'"
			}
			];
			
			window.myDoughnut = new Chart(document.getElementById("canvas_doughnut'.$courseid.'").getContext("2d")).Doughnut(pieChartData, {
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
		$content = html_writer::tag("h3", $course->fullname, array("class" => "competence_profile_coursetitle"));
		if(!$compTree) {
			$content .= html_writer::div(get_string("nodata","block_exacomp"),"error");			
			return $content;
		}
		//print graphs
		$topics = block_exacomp_get_topics_for_radar_graph($course->id, $student->id);
		$content .= html_writer::div($this->print_radar_graph($topics,$course->id),"competence_profile_radargraph");

		list($teachercomp,$studentcomp,$pendingcomp) = block_exacomp_get_competencies_for_pie_chart($course->id,$student, $scheme);
		$content .= html_writer::div($this->print_pie_graph($teachercomp, $studentcomp, $pendingcomp, $course->id),"competence_profile_radargraph");
		//print list
		
		$student = block_exacomp_get_user_information_by_course($student, $course->id);

		$content .= $this->print_competence_profile_tree($compTree,$student,$scheme);
		
		return html_writer::div($content,"competence_profile_coursedata");
	}
	
	private function print_competence_profile_tree($in,$student,$scheme = 1) {
		$content = "<ul>";
		foreach($in as $v) {
			$class = 'competence_profile_' . $v->tabletype;
			if($v->tabletype == "topic" && isset($student->topics->teacher[$v->id]) && $student->topics->teacher[$v->id] >= ceil($scheme/2)) 
				$class .= " reached";
			if($v->tabletype == "descriptor" && isset($student->competencies->teacher[$v->id]) && $student->competencies->teacher[$v->id] >= ceil($scheme/2))
				$class .= " reached";
			
			$content .= '<li class="'.$class.'">' . $v->title	 . '</li>';
			if( isset($v->subs) && is_array($v->subs)) $content .= $this->print_competence_profile_tree($v->subs, $student,$scheme);
			if( isset($v->descriptors) && is_array($v->descriptors)) $content .= $this->print_competence_profile_tree($v->descriptors, $student,$scheme);
		}
		$content .= "</ul>";
		return $content;
	}
	function print_radar_graph($records,$courseid) {
	
		if(count($records) >= 3 && count($records) <= 7) {
				
			$height = $width = 450;
			$content = html_writer::div(html_writer::empty_tag("canvas",array("id" => "canvasradar".$courseid, "height" => $height, "width" => $width)),"radargraph",array("style" => "width:40%"));
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
			fillColor: "rgba(220,220,220,0.2)",
			strokeColor: "rgba(220,220,220,1)",
			pointColor: "rgba(220,220,220,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(220,220,220,1)",
			data: [';
	
			foreach($records as $record)
				$content .= '"'.$record->student.'",';
			$content .= ']
			},
			{
			label: "'.get_string("teachercomp","block_exacomp").'",
			fillColor: "rgba(151,187,205,0.2)",
			strokeColor: "rgba(151,187,205,1)",
			pointColor: "rgba(151,187,205,1)",
			pointStrokeColor: "#fff",
			pointHighlightFill: "#fff",
			pointHighlightStroke: "rgba(151,187,205,1)",
			data: [';
	
			foreach($records as $record)
				$content .= '"'.$record->teacher.'",';
			$content .=']
		}
		]
		};
	
		window.myRadar = new Chart(document.getElementById("canvasradar'.$courseid.'").getContext("2d")).Radar(radarChartData, {
		responsive: true
		});
		
		</script>';
		} else {
			//print error
			$content = html_writer::div(get_string("radargrapherror","block_exacomp"),"competence_profile_grapherror");
		}
		return $content;
	}
	
	public function print_profile_settings($courses, $settings, $exaport, $exastud, $exaport_items, $exastud_periods){
		global $COURSE;
		$exacomp_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exacomp'));
		$exacomp_div_content .= html_writer::div(
			html_writer::checkbox('showonlyreached', 1, ($settings->showonlyreached==1), get_string('profile_settings_showonlyreached', 'block_exacomp')));
		
		$content_courses = html_writer::label(get_string('profile_settings_choose_courses', 'block_exacomp'), '');
		foreach($courses as $course){
			$content_courses .= html_writer::checkbox('profile_settings_course[]', $course->id, (isset($settings->exacomp[$course->id])), $course->fullname);
		}
		$exacomp_div_content .= html_writer::div($content_courses);
		$exacomp_div = html_writer::div($exacomp_div_content);
		
		$content = $exacomp_div;
		
		if($exaport){
			$exaport_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exaport'));
			$exaport_div_content .= html_writer::div(
				html_writer::checkbox('useexaport', 1, ($settings->useexaport==1), get_string('profile_settings_useexaport', 'block_exacomp')));
			
			if($settings->useexaport == 1){
				$content_items = html_writer::label(get_string('profile_settings_choose_items', 'block_exacomp'), '');
				foreach($exaport_items as $item){
					$content_items .= html_writer::checkbox('profile_settings_items[]', $item->id, (isset($settings->exaport[$item->id])), $item->name);
				}
				$exacomp_div_content .= html_writer::div($content_items);
			}
			
			$exaport_div = html_writer::div($exaport_div_content);
			$content .= $exaport_div;
		}
		
		if($exastud){
			$exastud_div_content = html_writer::tag('h2', get_string('pluginname', 'block_exastud'));
			$exasutd_div_content .= html_writer::div(
				html_writer::checkbox('useexastud', 1, ($settings->useexastud ==1), get_string('profile_settings_useexastud', 'block_exacomp')));
			
			if($settings->useexastud == 1){
				$content_periods = html_writer::label(get_string('profile_settings_choose_periods', 'block_exacomp'), '');
				foreach($exastud_periods as $period){
					$content_periods .= html_writer::checkbox('profile_settings_periods[]', $period->id, (isset($settings->exastud[$period->id])), $period->title);
				}
				$exastud_div_content .= html_writer::div($content_periods);
			}
			$exastud_div = html_writer::div($exastud_div_content);
			$content .= $exastud_div;
		}
		
		$content .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp')));

		$div = html_writer::div(html_writer::tag('form',
				$content,
				array('action'=>'competence_profile_settings.php?courseid='.$COURSE->id, 'method'=>'post')), 'block_excomp_center');

		return html_writer::tag("div", $div, array("id"=>"exabis_competences_block"));
	}
}
?>