<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp\local;

defined('MOODLE_INTERNAL') || die();

/** Preserve Exacomp observers while ordinary Moodle activity continues during migration. */
final class deferred_events {
    private const EVENTS = [
        '\\core\\event\\course_created' => 'course_created',
        '\\core\\event\\course_module_completion_updated' => 'course_module_completion_updated',
        '\\core\\event\\course_module_updated' => 'course_module_updated',
        '\\core\\event\\course_module_deleted' => 'course_module_deleted',
        '\\mod_quiz\\event\\attempt_submitted' => 'attempt_submitted',
    ];
    private static ?array $snapshots = null;

    public static function is_replaying(): bool {
        return self::$snapshots !== null;
    }

    private static function lock(): \core\lock\lock {
        $lock = \core\lock\lock_config::get_lock_factory('block_exacomp')->get_lock('migration_events', 10);
        if (!$lock) {
            throw new \moodle_exception('itemcontentmigrationbusy', 'block_exacomp');
        }
        return $lock;
    }

    /** Return true when this observer was durably deferred, before any normal Exacomp writes. */
    public static function defer(\core\event\base $event): bool {
        global $DB, $USER;
        if (self::is_replaying() || !migration_pause::is_pending()) {
            return false;
        }
        $lock = self::lock();
        try {
            if (!migration_pause::is_pending()) {
                return false;
            }
            $data = $event->get_data();
            if (!isset(self::EVENTS[$data['eventname']])) {
                throw new \coding_exception('Unsupported deferred Exacomp observer');
            }
            $snapshots = [];
            $tables = [
                'course_created' => 'course',
                'course_module_completion_updated' => 'course_modules_completion',
                'course_module_updated' => 'course_modules',
            ];
            $method = self::EVENTS[$data['eventname']];
            if (isset($tables[$method])) {
                $table = $tables[$method];
                $snapshots[$table][$event->objectid] = $event->get_record_snapshot($table, $event->objectid);
            }
            if ($method === 'course_module_completion_updated') {
                $snapshots['completionquiz'] = self::completion_quiz(
                    $snapshots['course_modules_completion'][$event->objectid], $event->relateduserid);
            } else if ($method === 'attempt_submitted') {
                $snapshots['quizattempt'] = self::quiz_attempt($event->objectid);
            } else if ($method === 'course_module_updated') {
                $snapshots['students'] = self::course_student_ids($event->courseid);
            }
            if (in_array($method, ['course_module_completion_updated', 'attempt_submitted'], true)) {
                $snapshots['admingrading'] = self::admingrading($event->courseid, (int)($USER->id ?? 0));
            }
            $DB->insert_record('block_exacompdeferredevent', (object)[
                'payload' => json_encode(['formatversion' => 1, 'data' => $data, 'snapshots' => $snapshots,
                    'actorid' => (int)($USER->id ?? 0)], JSON_THROW_ON_ERROR),
                'timecreated' => time(),
            ]);
            return true;
        } finally {
            $lock->release();
        }
    }

    /** Restored Moodle events cannot expose snapshots; retain only the snapshots these observers need. */
    public static function snapshot(\core\event\base $event, string $table, int $id) {
        if (self::is_replaying()) {
            $record = self::$snapshots[$table][$id] ?? false;
            return $record ? (object)$record : false;
        }
        return $event->get_record_snapshot($table, $id);
    }

    public static function course_student_ids(int $courseid): array {
        return self::is_replaying() && isset(self::$snapshots['students']) ? self::$snapshots['students'] :
            array_map(static fn($student): int => (int)$student->id, \block_exacomp\permissions::get_course_students($courseid));
    }

    public static function admingrading(int $courseid, int $actorid): bool {
        return self::is_replaying() && isset(self::$snapshots['admingrading']) ? self::$snapshots['admingrading'] : !block_exacomp_is_teacher($courseid, $actorid);
    }

    /** Preserve the grade at the event, even if the quiz is regraded before replay. */
    public static function completion_quiz($completion, int $userid): array {
        global $DB;
        if (self::is_replaying()) {
            $data = self::$snapshots['completionquiz'];
            return ['quiz' => $data['quiz'] ? (object)$data['quiz'] : false,
                'grade' => $data['grade'] ? (object)$data['grade'] : false];
        }
        $module = $DB->get_record('course_modules', ['id' => $completion->coursemoduleid, 'module' => 17]);
        $quiz = $module ? $DB->get_record('quiz', ['id' => $module->instance]) : false;
        $grade = $quiz ? $DB->get_record('quiz_grades', ['quiz' => $quiz->id, 'userid' => $userid]) : false;
        return ['quiz' => $quiz, 'grade' => $grade];
    }

    /** Retain the existing query/projection, including the original question grades and descriptor identities. */
    public static function quiz_attempt(int $attemptid): array {
        global $DB;
        if (self::is_replaying()) {
            $data = self::$snapshots['quizattempt'];
            $data['record'] = $data['record'] ? (object)$data['record'] : false;
            $data['questions'] = array_map(static fn($row) => (object)$row, $data['questions']);
            foreach ($data['descriptors'] as &$rows) {
                $rows = array_map(static fn($row) => (object)$row, $rows);
            }
            return $data;
        }
        $record = $DB->get_record('quiz_attempts', ['id' => $attemptid], 'uniqueid, userid');
        $questions = $record ? $DB->get_records_sql("SELECT attempts.id, attempts.questionid, attempts.maxmark,
                step.fraction, attempts.timemodified
              FROM {question_attempts} attempts
              JOIN {question_attempt_steps} step ON attempts.id = step.questionattemptid
             WHERE (step.state = 'gradedright' OR step.state = 'gradedwrong') AND questionusageid = ?",
            [$record->uniqueid]) : [];
        $descriptors = [];
        foreach ($questions as $question) {
            $descriptors[$question->questionid] = $DB->get_records('block_exacompdescrquest_mm', ['questid' => $question->questionid]);
        }
        return ['record' => $record, 'questions' => $questions, 'descriptors' => $descriptors];
    }

    /** Replay in order, with each observer's database changes and journal deletion in one transaction. */
    public static function drain(int $limit): bool {
        global $DB, $USER, $CFG;
        $records = $DB->get_records('block_exacompdeferredevent', null, 'id ASC', '*', 0, $limit);
        if ($records) {
            require_once($CFG->dirroot . '/blocks/exacomp/classes/observer.php');
        }
        foreach ($records as $record) {
            $payload = json_decode($record->payload, true, 512, JSON_THROW_ON_ERROR);
            $data = $payload['data'];
            $method = self::EVENTS[$data['eventname']] ?? null;
            if (!$method || !($event = \core\event\base::restore($data, []))) {
                throw new \coding_exception('Invalid deferred Exacomp event ' . $record->id);
            }
            $actor = clone $USER;
            $transaction = $DB->start_delegated_transaction();
            try {
                self::$snapshots = $payload['snapshots'];
                // Observers use $USER to distinguish teacher actions from student actions.
                $actorid = $payload['actorid'] ?? $data['userid'];
                \core\session\manager::set_user($actorid ? \core_user::get_user($actorid, '*', MUST_EXIST) : (object)['id' => 0]);
                \block_exacomp_observer::$method($event);
                $DB->delete_records('block_exacompdeferredevent', ['id' => $record->id]);
                $transaction->allow_commit();
            } catch (\Throwable $exception) {
                $transaction->rollback(new \coding_exception(
                    "Deferred Exacomp event {$record->id} ({$method}) failed", $exception->getMessage()));
            } finally {
                self::$snapshots = null;
                \core\session\manager::set_user($actor);
            }
        }
        return !$DB->record_exists('block_exacompdeferredevent', []);
    }

    /** Atomically stop deferring only when the last event has been applied. */
    public static function finish(callable $finish): bool {
        global $DB;
        $lock = self::lock();
        try {
            if ($DB->record_exists('block_exacompdeferredevent', [])) {
                return false;
            }
            $finish();
            return true;
        } finally {
            $lock->release();
        }
    }
}
