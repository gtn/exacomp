<?php


namespace core_question\bank;
defined('MOODLE_INTERNAL') || die();

require_once('descriptor_link_column.php');

class exacomp_view extends \core_question\bank\view
{
    /** @var bool whether the quiz this is used by has been attemptd. */
    protected $quizhasattempts = false;
    /** @var \stdClass the quiz settings. */
    protected $quiz = false;
    /** @var int The maximum displayed length of the category info. */
    const MAX_TEXT_LENGTH = 200;

    /**
     * Constructor
     * @param \question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param \stdClass $course course settings
     * @param \stdClass $cm activity settings.
     * @param \stdClass $quiz quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $cm)
    {
        parent::__construct($contexts, $pageurl, $course, $cm);
    }

    protected function wanted_columns()
    {
        global $CFG;

        if (empty($CFG->quizquestionbankcolumns)) {
            $quizquestionbankcolumns = array(
                'checkbox_column', 'question_type_column',
                'question_name_idnumber_tags_column', 'edit_menu_column',
                'edit_action_column', 'copy_action_column', 'tags_action_column',
                'preview_action_column', 'delete_action_column', 'export_xml_action_column',
                'creator_name_column', 'modifier_name_column', 'descriptor_link_column'
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
}
