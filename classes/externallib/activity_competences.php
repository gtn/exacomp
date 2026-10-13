<?php
// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify it under the
// GNU General Public License as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.

namespace block_exacomp\externallib;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use invalid_parameter_exception;

defined('MOODLE_INTERNAL') || die();

// Moodle 4.2+ uses autoloaded core_external classes. Retain the branch's
// Moodle 3.11 minimum without loading the legacy alias file on newer Moodle.
if (!class_exists('\\core_external\\external_api')) {
    require_once($CFG->libdir . '/externallib.php');
    foreach (['external_api', 'external_function_parameters', 'external_multiple_structure',
            'external_single_structure', 'external_value', 'restricted_context_exception'] as $classname) {
        class_alias($classname, 'core_external\\' . $classname);
    }
}

/**
 * Teacher-only, additive activity/material relation contracts.
 *
 * @package block_exacomp
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */
class activity_competences extends \core_external\external_api {
    /** Validate the most specific context and the course-level teacher permission. */
    private static function authorize($courseid, $cmid = null) {
        global $DB;
        if ($courseid <= 0) {
            throw new invalid_parameter_exception('A positive courseid is required');
        }
        $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($courseid);
        if ($cmid !== null) {
            if ($cmid <= 0) {
                throw new invalid_parameter_exception('A positive cmid is required');
            }
            get_coursemodule_from_id(null, $cmid, $courseid, false, MUST_EXIST);
            self::validate_context(\context_module::instance($cmid));
        } else {
            self::validate_context($context);
        }
        require_capability('block/exacomp:teacher', $context);
    }

    /** All directly configured descriptors and descendants, with no visibility/material filters. */
    private static function course_descriptors($courseid) {
        global $DB;
        $records = $DB->get_records_sql('SELECT DISTINCT d.*
              FROM {block_exacompdescriptors} d
              JOIN {block_exacompdescrtopic_mm} dt ON dt.descrid = d.id
              JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = dt.topicid
             WHERE ct.courseid = ? ORDER BY d.id', [$courseid]);
        $frontier = array_keys($records);
        while ($frontier) {
            $children = $DB->get_records_list('block_exacompdescriptors', 'parentid', $frontier, 'id');
            $frontier = [];
            foreach ($children as $id => $child) {
                if (!isset($records[$id])) {
                    $records[$id] = $child;
                    $frontier[] = $id;
                }
            }
        }
        ksort($records);
        return $records;
    }

    public static function get_course_competences_parameters() {
        return new external_function_parameters(['courseid' => new external_value(PARAM_INT, 'Course ID')]);
    }

    /**
     * Read the complete configured course competence tree.
     * @ws-type-read
     * @ws-capabilities block/exacomp:teacher
     */
    public static function get_course_competences($courseid) {
        global $DB;
        $params = self::validate_parameters(self::get_course_competences_parameters(), ['courseid' => $courseid]);
        self::authorize($params['courseid']);
        $result = [];
        foreach (self::course_descriptors($params['courseid']) as $record) {
            $niveau = $record->niveauid ? $DB->get_record('block_exacompniveaus', ['id' => $record->niveauid]) : false;
            $result[] = ['id' => $record->id, 'parentid' => (int)$record->parentid,
                'title' => $record->title, 'niveauid' => (int)$record->niveauid,
                'niveau' => $niveau ? json_encode($niveau) : 'null', 'metadata' => json_encode($record)];
        }
        return $result;
    }

    public static function get_course_competences_returns() {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Descriptor ID'),
            'parentid' => new external_value(PARAM_INT, 'Parent ID, zero for root'),
            'title' => new external_value(PARAM_RAW, 'Stored title'),
            'niveauid' => new external_value(PARAM_INT, 'Level ID, zero if absent'),
            'niveau' => new external_value(PARAM_RAW, 'JSON level record or null'),
            'metadata' => new external_value(PARAM_RAW, 'JSON complete stored descriptor'),
        ]));
    }

    public static function get_activity_competences_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /** Preserve raw values, nulls and every relation type in the inspection snapshot. */
    private static function snapshot($courseid, $cmid) {
        global $DB;
        // cmid determines ownership. Include inconsistent legacy courseid values in reads,
        // but reject them as write targets rather than silently hiding or repairing them.
        $examples = $DB->get_records('block_exacompexamples', ['activityid' => $cmid], 'id');
        $materials = [];
        foreach ($examples as $example) {
            $relations = $DB->get_records('block_exacompdescrexamp_mm', ['exampid' => $example->id], 'id');
            $links = [];
            foreach ($relations as $relation) {
                $descriptorid = self::descriptor_id($relation);
                $descriptor = $descriptorid ? $DB->get_record('block_exacompdescriptors', ['id' => $descriptorid]) : false;
                $links[] = ['id' => $relation->id, 'descriptorid' => $descriptorid,
                    'record' => json_encode($relation), 'descriptor' => $descriptor ? json_encode($descriptor) : 'null'];
            }
            $visibility = $DB->get_records('block_exacompexampvisibility', ['exampleid' => $example->id], 'id');
            $solutionvisibility = $DB->get_records('block_exacompsolutvisibility', ['exampleid' => $example->id], 'id');
            $files = $DB->get_records_select('files',
                'component = ? AND contextid = ? AND itemid = ? AND filearea IN (?, ?, ?)',
                ['block_exacomp', \context_system::instance()->id, $example->id,
                    'example_task', 'example_solution', 'example_completefile'], 'id',
                'id, contextid, filearea, itemid, filepath, filename, filesize, contenthash, pathnamehash');
            $materials[] = ['id' => $example->id, 'metadata' => json_encode($example),
                'relations' => $links, 'visibility' => json_encode(array_values($visibility)),
                'solutionvisibility' => json_encode(array_values($solutionvisibility)),
                'files' => json_encode(array_values($files))];
        }
        return ['courseid' => $courseid, 'cmid' => $cmid,
            'status' => count($materials) == 0 ? 'missing' : (count($materials) == 1 ? 'unique' : 'ambiguous'),
            'materials' => $materials];
    }

    /** Both legacy descriptor columns and generic 'descr' links represent descriptor membership. */
    private static function descriptor_id($relation) {
        if (empty($relation->table_foreign) || $relation->table_foreign === 'descr') {
            return (int)($relation->descrid > 0 ? $relation->descrid : $relation->id_foreign);
        }
        return 0;
    }

    /**
     * Inspect every material and relation for a course activity.
     * @ws-type-read
     * @ws-capabilities block/exacomp:teacher
     */
    public static function get_activity_competences($courseid, $cmid) {
        $params = self::validate_parameters(self::get_activity_competences_parameters(), compact('courseid', 'cmid'));
        self::authorize($params['courseid'], $params['cmid']);
        return self::snapshot($params['courseid'], $params['cmid']);
    }

    public static function get_activity_competences_returns() {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'status' => new external_value(PARAM_ALPHA, 'missing, unique or ambiguous'),
            'materials' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Material ID'),
                'metadata' => new external_value(PARAM_RAW, 'JSON complete stored material'),
                'relations' => new external_multiple_structure(new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Relation row ID'),
                    'descriptorid' => new external_value(PARAM_INT, 'Descriptor ID, zero for other relation types'),
                    'record' => new external_value(PARAM_RAW, 'JSON complete stored relation'),
                    'descriptor' => new external_value(PARAM_RAW, 'JSON descriptor record or null'),
                ])),
                'visibility' => new external_value(PARAM_RAW, 'JSON all stored material visibility rows'),
                'solutionvisibility' => new external_value(PARAM_RAW, 'JSON all stored solution visibility rows'),
                'files' => new external_value(PARAM_RAW, 'JSON stored material file metadata; no download URLs'),
            ])),
        ]);
    }

    public static function plan_activity_competences_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'exampleid' => new external_value(PARAM_INT, 'Explicit material ID'),
            'descriptorids' => new external_multiple_structure(new external_value(PARAM_INT, 'Requested descriptor ID')),
        ]);
    }

    /** Validate the entire request before any insert. */
    private static function plan($courseid, $cmid, $exampleid, $descriptorids) {
        global $DB;
        $snapshot = self::snapshot($courseid, $cmid);
        if ($snapshot['status'] !== 'unique' || $snapshot['materials'][0]['id'] != $exampleid) {
            throw new invalid_parameter_exception('Activity must have exactly one matching material with the requested exampleid');
        }
        $example = $DB->get_record('block_exacompexamples', ['id' => $exampleid], '*', MUST_EXIST);
        if ((int)$example->courseid !== (int)$courseid) {
            throw new invalid_parameter_exception('Material courseid does not match the activity course');
        }
        $allowed = self::course_descriptors($courseid);
        $existing = array_column($snapshot['materials'][0]['relations'], 'descriptorid');
        $present = $missing = [];
        foreach (array_unique($descriptorids) as $id) {
            if ($id <= 0 || !isset($allowed[$id])) {
                throw new invalid_parameter_exception('Descriptor is not in the configured course competence tree: ' . $id);
            }
            if (in_array($id, $existing)) {
                $present[] = $id;
            } else {
                $missing[] = $id;
            }
        }
        sort($present);
        sort($missing);
        return ['existingids' => $present, 'missingids' => $missing, 'addedids' => [], 'state' => $snapshot];
    }

    /**
     * Plan explicitly requested activity competence additions without writes.
     * @ws-type-read
     * @ws-capabilities block/exacomp:teacher
     */
    public static function plan_activity_competences($courseid, $cmid, $exampleid, $descriptorids) {
        $params = self::validate_parameters(self::plan_activity_competences_parameters(),
            compact('courseid', 'cmid', 'exampleid', 'descriptorids'));
        self::authorize($params['courseid'], $params['cmid']);
        return self::plan($params['courseid'], $params['cmid'], $params['exampleid'], $params['descriptorids']);
    }

    public static function plan_activity_competences_returns() {
        return new external_single_structure([
            'existingids' => new external_multiple_structure(new external_value(PARAM_INT, 'Requested link already present')),
            'missingids' => new external_multiple_structure(new external_value(PARAM_INT, 'Requested link missing at planning time')),
            'addedids' => new external_multiple_structure(new external_value(PARAM_INT, 'Link added by this call')),
            'state' => self::get_activity_competences_returns(),
        ]);
    }

    public static function add_activity_competences_parameters() {
        $parameters = self::plan_activity_competences_parameters();
        $parameters->keys['confirm'] = new external_value(PARAM_BOOL, 'Must be true to authorize additions', VALUE_DEFAULT, false);
        return $parameters;
    }

    /**
     * Add only explicitly confirmed missing activity competence links.
     * @ws-type-write
     * @ws-capabilities block/exacomp:teacher
     */
    public static function add_activity_competences($courseid, $cmid, $exampleid, $descriptorids, $confirm = false) {
        global $DB;
        $params = self::validate_parameters(self::add_activity_competences_parameters(),
            compact('courseid', 'cmid', 'exampleid', 'descriptorids', 'confirm'));
        self::authorize($params['courseid'], $params['cmid']);
        if (!$params['confirm']) {
            throw new invalid_parameter_exception('Explicit confirm=true is required; use plan_activity_competences for a dry run');
        }
        // Serialize requests to this API across workers; recheck membership inside the lock.
        $factory = \core\lock\lock_config::get_lock_factory('block_exacomp_activity_competences');
        $lock = $factory->get_lock('activity:' . $params['cmid'], 10);
        if (!$lock) {
            throw new \moodle_exception('locktimeout');
        }
        try {
            $result = self::plan($params['courseid'], $params['cmid'], $params['exampleid'], $params['descriptorids']);
            $transaction = $DB->start_delegated_transaction();
            foreach ($result['missingids'] as $id) {
                $DB->insert_record('block_exacompdescrexamp_mm', (object)[
                    'exampid' => $params['exampleid'], 'descrid' => $id, 'sorting' => 0,
                ]);
            }
            $result['addedids'] = $result['missingids'];
            $result['state'] = self::snapshot($params['courseid'], $params['cmid']);
            $transaction->allow_commit();
            return $result;
        } catch (\Throwable $e) {
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            throw $e;
        } finally {
            $lock->release();
        }
    }

    public static function add_activity_competences_returns() {
        return self::plan_activity_competences_returns();
    }
}
