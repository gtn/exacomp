<?php

namespace block_exacomp\task;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../../inc.php';

class question_grading extends \core\task\scheduled_task
{
    public function get_name() {
        return block_exacomp_trans(['en:Question Grading']);
    }

    public function execute() {
        block_exacomp_perform_question_grading();
    }
}
