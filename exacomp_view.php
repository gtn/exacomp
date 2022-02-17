<?php

namespace core_question\bank;
use context;
use core_question\bank\search\category_condition;
use core_question\bank\search\hidden_condition;
use core_question\bank\search\tag_condition;
use moodle_url;
use question_edit_contexts;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once('descriptor_link_column.php');

class exacomp_view extends view {
    /** @var bool whether the quiz this is used by has been attemptd. */
    protected $quizhasattempts = false;
    /** @var stdClass the quiz settings. */
    protected $quiz = false;
    /** @var int The maximum displayed length of the category info. */
    const MAX_TEXT_LENGTH = 200;

    /**
     * Constructor
     *
     * @param question_edit_contexts $contexts
     * @param moodle_url $pageurl
     * @param stdClass $course course settings
     * @param stdClass $cm activity settings.
     * @param stdClass $quiz quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $cm) {
        parent::__construct($contexts, $pageurl, $course, $cm);
    }

    protected function wanted_columns() {
        global $CFG;

        if (empty($CFG->quizquestionbankcolumns)) {
            $quizquestionbankcolumns = array(
                'checkbox_column', 'question_type_column',
                'question_name_idnumber_tags_column', 'edit_menu_column',
                'edit_action_column', 'copy_action_column', 'tags_action_column',
                'preview_action_column', 'delete_action_column', 'export_xml_action_column',
                'creator_name_column', 'modifier_name_column', 'descriptor_link_column',
            );
        } else {
            $quizquestionbankcolumns = explode(',', $CFG->quizquestionbankcolumns);
        }

        foreach ($quizquestionbankcolumns as $fullname) {
            if (!class_exists($fullname)) {
                if (class_exists('mod_quiz\\question\\bank\\' . $fullname)) {
                    $fullname = 'mod_quiz\\question\\bank\\' . $fullname;
                } else if (class_exists('core_question\\bank\\' . $fullname)) {
                    $fullname = 'core_question\\bank\\' . $fullname;
                } else if (class_exists('question_bank_' . $fullname)) {
                    debugging('Legacy question bank column class question_bank_' .
                        $fullname . ' should be renamed to mod_quiz\\question\\bank\\' .
                        $fullname, DEBUG_DEVELOPER);
                    $fullname = 'question_bank_' . $fullname;
                } else {
                    throw new coding_exception("No such class exists: $fullname");
                }
            }
            $this->requiredcolumns[$fullname] = new $fullname($this);
        }
        return $this->requiredcolumns;
    }

    public function display($tabname, $page, $perpage, $cat,
        $recurse, $showhidden, $showquestiontext, $tagids = []) {
        global $PAGE, $CFG;

        if ($this->process_actions_needing_ui()) {
            return;
        }
        $editcontexts = $this->contexts->having_one_edit_tab_cap($tabname);
        list(, $contextid) = explode(',', $cat);
        $catcontext = context::instance_by_id($contextid);
        $thiscontext = $this->get_most_specific_context();
        // Category selection form.
        $this->display_question_bank_header();

        // Display tag filter if usetags setting is enabled.
        if ($CFG->usetags) {
            array_unshift($this->searchconditions,
                new tag_condition([$catcontext, $thiscontext], $tagids));
            $PAGE->requires->js_call_amd('core_question/edit_tags', 'init', ['#questionscontainer']);
        }

        array_unshift($this->searchconditions, new hidden_condition(!$showhidden));
        array_unshift($this->searchconditions, new category_condition(
            $cat, $recurse, $editcontexts, $this->baseurl, $this->course));
        $this->display_options_form($showquestiontext, '/blocks/exacomp/question_to_descriptors.php');

        // Continues with list of questions.
        $this->display_question_list($editcontexts,
            $this->baseurl, $cat, $this->cm,
            null, $page, $perpage, $showhidden, $showquestiontext,
            $this->contexts->having_cap('moodle/question:add'));

    }
}
