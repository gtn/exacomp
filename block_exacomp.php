<?php

require_once dirname(__FILE__) . '/lib/div.php';

class block_exacomp extends block_base {

	/*class block_exacomp extends block_list
	 $this->content->items[] = html_writer::tag('a', 'Menu Option 1', array('href' => 'some_file.php'));
	$this->content->icons[] = html_writer::empty_tag('img', array('src' => 'images/icons/1.gif', 'class' => 'icon'));
	$this->content->items[] = html_writer::tag('a', 'Menu Option 1', array('href' => 'some_file.php'));
	$this->content->icons[] = html_writer::empty_tag('img', array('src' => 'images/icons/1.gif', 'class' => 'icon'));
	*/
	function init() {
		$this->title = get_string('exabis_competences', 'block_exacomp');
	}

	function has_config() {
		return true;
	}

	function instance_can_be_docked() {
		return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
	}

	function get_required_javascript() {
		global $CFG;
		$arguments = array('id' => $this->instance->id, 'instance' => $this->instance->id, 'candock' => $this->instance_can_be_docked());
		$this->page->requires->yui_module(array('core_dock', 'moodle-block_navigation-navigation'), 'M.block_navigation.init_add_tree', array($arguments));
		user_preference_allow_ajax_update('docked_block_instance_'.$this->instance->id, PARAM_INT);
	}

	function get_content() {
		global $CFG, $COURSE, $USER;
		if ($this->content !== NULL) {
			return $this->content;
		}

		//CHECK CAPABILITYS
		$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

		$courseSettings = block_exacomp_coursesettings();

		$courseid = intval($COURSE->id);
		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();

		$version = get_config('exacomp', 'alternativedatamodel');
		
		/*
		//Adminbereich
		if ((has_capability('block/exacomp:admin', $context) && !$version)) {

			$this->content->text = '';
			$this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/module_config.png" height="16" width="23" alt="' . get_string("adminnavconfig", "block_exacomp") . '" />';
			$this->content->text.='<a title="' . get_string("adminnavconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_config.php?courseid=' . $courseid . '">' . get_string('adminnavconfig', 'block_exacomp') . '</a>';
			$this->content->footer = '';
		}elseif ((has_capability('block/exacomp:admin', $context) && $version)) {

			$this->content->text = '';
			$this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/module_config.png" height="16" width="23" alt="' . get_string("adminnavconfig", "block_exacomp") . '" />';
			$this->content->text.='<a title="' . get_string("adminnavconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $courseid . '">' . get_string('admintabimport', 'block_exacomp') . '</a>';
			$this->content->footer = '';
		}
		//Lehrerbereich
		//if (has_capability('block/exacomp:teacher', $context) && has_capability('moodle/course:update', $context) && $courseid != 1) {
		if (has_capability('block/exacomp:teacher', $context) && $courseid != 1) {

			if (!empty($this->content->text))
				$this->content->text .= '<br />';
			//Prüfen ob der Lehrer den Kurs bereits zugeordnet hat
			if (!block_exacomp_isactivated($courseid)) {
				//Kurs nicht zugeordnet
				$this->content->text .= get_string('configcourseonce', 'block_exacomp')."<br/>";
			}

			$configurl = (!get_config("exacomp","alternativedatamodel")) ? '/blocks/exacomp/edit_course.php?courseid=': '/blocks/exacomp/edit_config.php?courseid=';
			$this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/subjects_topics.gif" height="16" width="23" alt="' . get_string("teachernavconfig", "block_exacomp") . '" />';
			$this->content->text.='<a title="' . get_string("teachernavconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . $configurl . $courseid . '">' . get_string('teachernavconfig', 'block_exacomp') . '</a>';
			if (block_exacomp_isactivated($courseid)) {
				//Kurs zugeordnet
				if ($courseSettings->uses_activities)
				{
					$this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/assign_moodle_activities.png" height="16" width="23" alt="' . get_string("link_edit_activities", "block_exacomp") . '" />';
					$this->content->text.='<a title="' . get_string("link_edit_activities", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_activities.php?courseid=' . $courseid . '">' . get_string('teachernavactivities', 'block_exacomp') . '</a>';
				}
				$this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/overview_of_competencies.png" height="16" width="23" alt="' . get_string("teachernavstudents", "block_exacomp") . '" />';
				$this->content->text.='<a title="' . get_string("teachernavstudents", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '">' . get_string('teachernavstudents', 'block_exacomp') . '</a>';
				if ($courseSettings->uses_activities)
				{
					$this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/detailed_view_of_competencies.png" height="16" width="23" alt="' . get_string("teachertabassigncompetencesdetail", "block_exacomp") . '" />';
					$this->content->text.='<a title="' . get_string("teachertabassigncompetencesdetail", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_students.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetencesdetail', 'block_exacomp') . '</a>';
				}
				$this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/examples_and_tasks.png" height="16" width="23" alt="' . get_string("teachertabassigncompetenceexamples", "block_exacomp") . '" />';
				$this->content->text.='<a title="' . get_string("teachertabassigncompetenceexamples", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/view_examples.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetenceexamples', 'block_exacomp') . '</a>';
			}
		}
		//Schülerbereich
		if (has_capability('block/exacomp:student', $context) && $courseid != 1 && !has_capability('block/exacomp:admin', $context)) {
			if (!empty($this->content->text))
				$this->content->text .= '<br />';
			if (block_exacomp_isactivated($courseid)) {
				$this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/chart_bar.png" height="16" width="23" alt="' . get_string("studentnavcompetences", "block_exacomp") . '" />';
				$this->content->text.='<a title="' . get_string("studentnavcompetences", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '">' . get_string('studentnavcompetences', 'block_exacomp') . '</a>';
			}
		}
		*/
		$this->content->text .= block_exacomp::getIcons($context, $courseid, $courseSettings, $version);
		$renderer = $this->page->get_renderer('block_exacomp');

		return true;
	}
	function getIcons($context, $courseid, $courseSettings, $version) {
		global $CFG;
		$text = "";
		
		// ADMIN TABS:
		// Import
		if ((has_capability('block/exacomp:admin', $context))) {
		
			if(!$version) {
				$text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/module_config.png" height="16" width="23" alt="' . get_string("adminnavconfig", "block_exacomp") . '" />';
				$text.='<a title="' . get_string("adminnavconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_config.php?courseid=' . $courseid . '">' . get_string('adminnavconfig', 'block_exacomp') . '</a>';
			} else {
				$text ='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/module_config.png" height="16" width="23" alt="' . get_string("adminnavconfig", "block_exacomp") . '" />';
				$text.='<a title="' . get_string("adminnavconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $courseid . '">' . get_string('admintabimport', 'block_exacomp') . '</a>';
			}
		}
		
		if (has_capability('block/exacomp:teacher', $context) && $courseid != 1) {
		
			if (!empty($text))
				$text .= '<br />';
			
			//Check if already configured
			if (!block_exacomp_isactivated($courseid)) {
				$text .= get_string('configcourseonce', 'block_exacomp')."<br/>";
			}
			
			// SETTINGS
			$url = '/blocks/exacomp/edit_course.php?courseid=';
			$text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/subjects_topics.gif" height="16" width="23" alt="' . get_string("teachertabconfig", "block_exacomp") . '" />';
			$text.='<a title="' . get_string("teachertabconfig", "block_exacomp") . '" href="' . $CFG->wwwroot . $url . $courseid . '">' . get_string('teachertabconfig', 'block_exacomp') . '</a>';
			

			// SUBJECT SELECTION
			if($version) {
				$tab = get_string("admintabschooltype", "block_exacomp");
				$url = '/blocks/exacomp/edit_config.php?courseid=';
			}
			else {
				$url = '/blocks/exacomp/courseselection.php?courseid=';
				$tab = get_string("teachertabselection", "block_exacomp");
			}
			$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/subject.png" height="16" width="23" alt="' . $tab . '" />';
			$text.='<a title="' . $tab . '" href="' . $CFG->wwwroot . $url . $courseid . '">' . $tab . '</a>';
				
			if (block_exacomp_isactivated($courseid)) {

				if($version) {
					// TOPIC SELECTION
					$url = '/blocks/exacomp/courseselection.php?courseid=';
					$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/area.png" height="16" width="23" alt="' . get_string("teachertabselection", "block_exacomp") . '" />';
					$text.='<a title="' . get_string("teachertabselection", "block_exacomp") . '" href="' . $CFG->wwwroot . $url . $courseid . '">' . get_string('teachertabselection', 'block_exacomp') . '</a>';
				}
					
			if ($courseSettings->uses_activities)
			{
				$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/assign_moodle_activities.png" height="16" width="23" alt="' . get_string("link_edit_activities", "block_exacomp") . '" />';
				$text.='<a title="' . get_string("link_edit_activities", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_activities.php?courseid=' . $courseid . '">' . get_string('teachernavactivities', 'block_exacomp') . '</a>';
			}
			$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/grid.png" height="16" width="23" alt="' . get_string("teachertabcompetencegrid", "block_exacomp") . '" />';
			$text.='<a title="' . get_string("teachertabcompetencegrid", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/competence_grid.php?courseid=' . $courseid . '">' . get_string('teachertabcompetencegrid', 'block_exacomp') . '</a>';
				
			$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/overview_of_competencies.png" height="16" width="23" alt="' . get_string("teachernavstudents", "block_exacomp") . '" />';
			$text.='<a title="' . get_string("teachernavstudents", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '">' . get_string('teachernavstudents', 'block_exacomp') . '</a>';
			if ($courseSettings->uses_activities)
			{
				$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/detailed_view_of_competencies.png" height="16" width="23" alt="' . get_string("teachertabassigncompetencesdetail", "block_exacomp") . '" />';
				$text.='<a title="' . get_string("teachertabassigncompetencesdetail", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_students.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetencesdetail', 'block_exacomp') . '</a>';
			}
			$text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/examples_and_tasks.png" height="16" width="23" alt="' . get_string("teachertabassigncompetenceexamples", "block_exacomp") . '" />';
			$text.='<a title="' . get_string("teachertabassigncompetenceexamples", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/view_examples.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetenceexamples', 'block_exacomp') . '</a>';
		
			}
		}
		
		//Schülerbereich
		if (has_capability('block/exacomp:student', $context) && $courseid != 1 && !has_capability('block/exacomp:admin', $context)) {
			if (!empty($this->content->text))
				$this->content->text .= '<br />';
			if (block_exacomp_isactivated($courseid)) {
				$this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/chart_bar.png" height="16" width="23" alt="' . get_string("studentnavcompetences", "block_exacomp") . '" />';
				$this->content->text.='<a title="' . get_string("studentnavcompetences", "block_exacomp") . '" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '">' . get_string('studentnavcompetences', 'block_exacomp') . '</a>';
			}
		}
		
		return $text;
	}
	/**
	 * This function is executed by the Moodle cron job.
	 * It checks if an url for updating the data-xml file is specified and in this case
	 * it tries to get the content and update the local xml.
	 */
	public function cron() {

		$xmlserverurl = get_config('exacomp', 'xmlserverurl');
		if($xmlserverurl) {
			$xml = file_get_contents($xmlserverurl);
			if($xml) {
				file_put_contents(dirname(__FILE__).'/xml/exacomp_data.xml',$xml);
				require_once dirname(__FILE__) . '/lib/xmllib.php';

				if(block_exacomp_xml_do_import(null,1,1)) {
					mtrace("import done");
					block_exacomp_settstamp();
				}
				else mtrace("import failed");
			}
		}

		return true;
	}
}

// Here's the closing curly bracket for the class definition
// and here's the closing PHP tag from the section above.
?>
