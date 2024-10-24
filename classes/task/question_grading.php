<?php

namespace block_exacomp\task;

use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../inc.php';

class question_grading extends scheduled_task {
    public function get_name() {
        return block_exacomp_trans(['en:Question Grading']);
    }

    public function execute() {
        block_exacomp_perform_question_grading();
    }
}
