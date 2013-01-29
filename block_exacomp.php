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
        $this->version = 2013012900;
    }

    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.
    function has_config() {
        return false;
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

        $courseid = intval($COURSE->id);
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        //Adminbereich
        if (has_capability('block/exacomp:admin', $context)) {

            $this->content->text = '';
            $this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/config.gif" height="16" width="23" alt="' . get_string("adminnavconfig", "block_exacomp") . '" />';
            $this->content->text.='<a title="configuration" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_config.php?courseid=' . $courseid . '">' . get_string('adminnavconfig', 'block_exacomp') . '</a>';
            //$this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/databases.png" height="16" width="23" alt="' . get_string("adminnavimport", "block_exacomp") . '" />';
            //$this->content->text.='<a title="import" href="' . $CFG->wwwroot . '/blocks/exacomp/import.php">' . get_string('link_import', 'block_exacomp') . '</a>';
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
            $this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cog.png" height="16" width="23" alt="' . get_string("teachernavconfig", "block_exacomp") . '" />';
            $this->content->text.='<a title="edit course" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_course.php?courseid=' . $courseid . '">' . get_string('teachernavconfig', 'block_exacomp') . '</a>';
            if (block_exacomp_isactivated($courseid)) {
                //Kurs zugeordnet
                $this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/application_view_tile.png" height="16" width="23" alt="' . get_string("link_edit_activities", "block_exacomp") . '" />';
                $this->content->text.='<a title="edit" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_activities.php?courseid=' . $courseid . '">' . get_string('teachernavactivities', 'block_exacomp') . '</a>';
                $this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/group.png" height="16" width="23" alt="' . get_string("teachernavstudents", "block_exacomp") . '" />';
                $this->content->text.='<a title="assign studetns" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competences.php?courseid=' . $courseid . '">' . get_string('teachernavstudents', 'block_exacomp') . '</a>';
                $this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/page_white_stack.png" height="16" width="23" alt="' . get_string("teachertabassigncompetencesdetail", "block_exacomp") . '" />';
                $this->content->text.='<a title="assign studetns" href="' . $CFG->wwwroot . '/blocks/exacomp/edit_students.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetencesdetail', 'block_exacomp') . '</a>';
                $this->content->text.='<br /><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/doc_offlice.png" height="16" width="23" alt="' . get_string("teachertabassigncompetenceexamples", "block_exacomp") . '" />';
                $this->content->text.='<a title="assign studetns" href="' . $CFG->wwwroot . '/blocks/exacomp/view_examples.php?courseid=' . $courseid . '">' . get_string('teachertabassigncompetenceexamples', 'block_exacomp') . '</a>';
            }
        }
        //Schülerbereich
        if (has_capability('block/exacomp:student', $context) && $courseid != 1) {
            if (!empty($this->content->text))
                $this->content->text .= '<br />';
            if (block_exacomp_isactivated($courseid)) {
                $this->content->text.='<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/chart_bar.png" height="16" width="23" alt="' . get_string("studentnavcompetences", "block_exacomp") . '" />';
                $this->content->text.='<a title="evaluate competences" href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competences.php?courseid=' . $courseid . '">' . get_string('studentnavcompetences', 'block_exacomp') . '</a>';
            }
        }
        $renderer = $this->page->get_renderer('block_exacomp');
        //$this->content->text .= $renderer->settings_tree($this->page->settingsnav);
		//$this->content->text .= print_r($this->page->settingsnav,true);
	/*	$this->content->text .= '
		
		<ul class="block_tree list">
<li class="type_unknown depth_1 contains_branch">
<p class="tree_item branch navigation_node">
<a href="http://gtn02.gtn-solutions.com/moodle20/" title="Home">Home</a>
</p>
<ul>
<li class="type_setting depth_2 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/my/" title="My home">
</p>
</li>
<li id="yui_3_4_1_1_1330523496683_699" class="type_course depth_2 contains_branch collapsed">
<p id="yui_3_4_1_1_1330523496683_697" class="tree_item branch">
<span tabindex="0" title="gtn moodle development server">Site pages</span>
</p>
<ul>
<li class="type_custom depth_3 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/user/index.php?id=1" title="Participants">
<img class="smallicon navicon" src="http://gtn02.gtn-solutions.com/moodle20/theme/image.php?theme=arialist&image=i%2Fnavigationitem" title="moodle" alt="moodle">
Participants
</a>
</p>
</li>
<li class="type_custom depth_3 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/blog/index.php?courseid=0" title="Blogs">
<img class="smallicon navicon" src="http://gtn02.gtn-solutions.com/moodle20/theme/image.php?theme=arialist&image=i%2Fnavigationitem" title="moodle" alt="moodle">
Blogs
</a>
</p>
</li>
<li class="type_custom depth_3 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/notes/index.php?filtertype=course&filterselect=0" title="Notes">
<img class="smallicon navicon" src="http://gtn02.gtn-solutions.com/moodle20/theme/image.php?theme=arialist&image=i%2Fnavigationitem" title="moodle" alt="moodle">
Notes
</a>
</p>
</li>
<li class="type_custom depth_3 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/tag/search.php" title="Tags">
<img class="smallicon navicon" src="http://gtn02.gtn-solutions.com/moodle20/theme/image.php?theme=arialist&image=i%2Fnavigationitem" title="moodle" alt="moodle">
Tags
</a>
</p>
</li>
<li class="type_custom depth_3 item_with_icon">
<p class="tree_item leaf hasicon">
<a href="http://gtn02.gtn-solutions.com/moodle20/calendar/view.php?view=month" title="Calendar">
<img class="smallicon navicon" src="http://gtn02.gtn-solutions.com/moodle20/theme/image.php?theme=arialist&image=i%2Fnavigationitem" title="moodle" alt="moodle">
Calendar
</a>
</p>
</li>
<li class="type_unknown depth_3 collapsed contains_branch">
<p class="tree_item branch">
<a href="http://gtn02.gtn-solutions.com/moodle20/course/report.php?id=1" title="Reports">Reports</a>
</p>
<ul>
<li class="type_setting depth_4 item_with_icon">
<li class="type_setting depth_4 item_with_icon">
<li class="type_setting depth_4 item_with_icon">
</ul>
</li>
</ul>
</li></ul></li></ul>';*/
        return true;
    }

}

// Here's the closing curly bracket for the class definition
// and here's the closing PHP tag from the section above.
?>