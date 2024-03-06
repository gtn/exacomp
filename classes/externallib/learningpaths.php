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

namespace block_exacomp\externallib;

defined('MOODLE_INTERNAL') || die();

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;

class learningpaths extends base {
    public static function diggrplus_learningpath_list_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'studentid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_learningpath_list(int $courseid, int $studentid = 0) {
        global $DB, $USER;

        [
            'courseid' => $courseid,
            'studentid' => $studentid,
        ] = static::validate_parameters(static::diggrplus_learningpath_list_parameters(), [
            'courseid' => $courseid,
            'studentid' => $studentid,
        ]);

        $isTeacher = block_exacomp_is_teacher($courseid);
        if ($isTeacher) {
            if ($studentid) {
                static::require_can_access_user($studentid);
            }

            $learningpaths = $DB->get_records('block_exacomplps', [
                'courseid' => $courseid,
            ], 'title');
        } else {
            $studentid = $USER->id;

            $learningpaths = $DB->get_records('block_exacomplps', [
                'courseid' => $courseid,
                'visible' => 1,
            ], 'title');
        }


        if (!$studentid) {
            $students = block_exacomp_get_students_by_course($courseid);
            $studentids = array_column($students, 'id');
        } else {
            $studentids = [$studentid];
        }

        // calculate counts for a whole learningpath
        foreach ($learningpaths as $learningpath) {
            $learningpath->count_new = 0;
            $learningpath->count_inprogress = 0;
            $learningpath->count_completed = 0;
            $learningpath->count_submitted = 0;

            foreach ($studentids as $studentid) {
                // get all visible lpItems for only this student
                $lpItems = $DB->get_records_sql('
                        SELECT item.*, item.visible AS visibleall, item_stud.visible AS visiblestudent
                        FROM {block_exacomplp_items} item
                        LEFT JOIN {block_exacomplp_item_stud} item_stud ON item.id=item_stud.itemid AND item_stud.studentid=?
                        WHERE item.learningpathid=?
                            AND (item_stud.visible>0 OR (item_stud.visible IS NULL and item.visible>0))
                        ORDER BY item.sorting, item.id', [$studentid, $learningpath->id]);

                foreach ($lpItems as $lpItem) {
                    $item = block_exacomp_get_current_item_for_example($studentid, $lpItem->exampleid);
                    $status = block_exacomp_get_human_readable_item_status($item ? $item->status : null);
                    $learningpath->{"count_{$status}"}++;
                }
            }
        }

        return ['learningpaths' => $learningpaths,];
    }

    public static function diggrplus_learningpath_list_returns() {
        // neue Regelung: niemals auf der ersten Ebene nur ein Array zurückgeben (wegen Erweiterbarkeit)
        return new external_single_structure([
            'learningpaths' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT),
                    'courseid' => new external_value(PARAM_INT),
                    'title' => new external_value(PARAM_TEXT),
                    'description' => new external_value(PARAM_TEXT),
                    'visible' => new external_value(PARAM_BOOL),
                    'count_new' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
                    'count_inprogress' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
                    'count_completed' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
                    'count_submitted' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
                ])
            ),
        ]);
    }

    public static function diggrplus_learningpath_details_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT),
            'studentid' => new external_value(PARAM_INT),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_learningpath_details(int $id, int $studentid) {
        global $DB, $USER;

        [
            'id' => $id,
            'studentid' => $studentid,
        ] = static::validate_parameters(static::diggrplus_learningpath_details_parameters(), [
            'id' => $id,
            'studentid' => $studentid,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $id,
        ], '*', MUST_EXIST);

        $courseid = $learningpath->courseid;
        static::require_can_access_course($courseid);

        $isTeacher = block_exacomp_is_teacher($courseid);

        if ($isTeacher) {
            if ($studentid) {
                static::require_can_access_user($studentid);
            }
        } else {
            // security checks
            $studentid = $USER->id;
            if (!$learningpath->visible) {
                throw new invalid_parameter_exception ('Learningpath not visible');
            }
        }

        if ($isTeacher) {
            $lpItems = $DB->get_records_sql('
                SELECT item.*, item.visible AS visibleall, item_stud.visible AS visiblestudent
                FROM {block_exacomplp_items} item
                LEFT JOIN {block_exacomplp_item_stud} item_stud ON item.id=item_stud.itemid AND item_stud.studentid=?
                WHERE item.learningpathid=?
                ORDER BY item.sorting, item.id', [$studentid, $learningpath->id]);

            $students = block_exacomp_get_students_by_course($courseid);
        } else {
            $lpItems = $DB->get_records_sql('
                SELECT item.*, item.visible AS visibleall, item_stud.visible AS visiblestudent
                FROM {block_exacomplp_items} item
                LEFT JOIN {block_exacomplp_item_stud} item_stud ON item.id=item_stud.itemid AND item_stud.studentid=?
                WHERE item.learningpathid=?
                    AND (item_stud.visible OR (item_stud.visible IS NULL and item.visible))
                ORDER BY item.sorting, item.id', [$studentid, $learningpath->id]);

            $students = null;
        }

        foreach ($lpItems as $lpItem) {
            $example = $DB->get_record('block_exacompexamples', array('id' => $lpItem->exampleid));

            $example_data = current($DB->get_records_sql(
                "SELECT DISTINCT topic.title as topictitle
                        -- , topic.id as topicid, subj.title as subjecttitle, subj.id as subjectid
                        FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
                        JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
                        JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
                        JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON ct.topicid = topic.id
                        JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON det.descrid=d.id
                        WHERE ct.courseid = :courseid AND dex.exampid = :exampleid", ['courseid' => $courseid, 'exampleid' => $lpItem->exampleid], 0, 1));

            $lpItem->visiblestudent = $lpItem->visiblestudent ?? $lpItem->visibleall;
            $lpItem->exampletitle = $example->title;
            $lpItem->topictitle = $example_data->topictitle ?? '';

            $lpItem->count_new = 0;
            $lpItem->count_inprogress = 0;
            $lpItem->count_completed = 0;
            $lpItem->count_submitted = 0;

            if (!$studentid) {
                $lpItem->status = '';

                $lpItem->count_new = 0;
                $lpItem->count_inprogress = 0;
                $lpItem->count_completed = 0;
                $lpItem->count_submitted = 0;

                foreach ($students as $student) {
                    $item = block_exacomp_get_current_item_for_example($student->id, $lpItem->exampleid);
                    $status = block_exacomp_get_human_readable_item_status($item ? $item->status : null);
                    $lpItem->{"count_{$status}"}++;
                }
            } else {
                $item = block_exacomp_get_current_item_for_example($studentid, $lpItem->exampleid);
                $lpItem->status = block_exacomp_get_human_readable_item_status($item ? $item->status : null);
            }
        }

        $learningpath->items = $lpItems;

        return $learningpath;
    }

    public static function diggrplus_learningpath_details_returns() {
        // neue Regelung: niemals auf der ersten Ebene nur ein Array zurückgeben (wegen Erweiterbarkeit)
        return new external_single_structure([
            'id' => new external_value(PARAM_INT),
            'courseid' => new external_value(PARAM_INT),
            'title' => new external_value(PARAM_TEXT),
            'description' => new external_value(PARAM_TEXT),
            'visible' => new external_value(PARAM_BOOL),
            'items' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT),
                    'exampleid' => new external_value(PARAM_INT),
                    'exampletitle' => new external_value(PARAM_TEXT),
                    'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
                    'topictitle' => new external_value(PARAM_TEXT),
                    'sorting' => new external_value(PARAM_INT),
                    'visibleall' => new external_value(PARAM_BOOL),
                    'visiblestudent' => new external_value(PARAM_BOOL),
                    'count_new' => new external_value(PARAM_INT),
                    'count_inprogress' => new external_value(PARAM_INT),
                    'count_completed' => new external_value(PARAM_INT),
                    'count_submitted' => new external_value(PARAM_INT),
                ])
            ),
        ]);
    }


    public static function diggrplus_learningpath_add_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'title' => new external_value(PARAM_TEXT),
            'description' => new external_value(PARAM_TEXT),
            'visible' => new external_value(PARAM_BOOL),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_add(int $courseid, string $title, string $description, bool $visible) {
        global $DB;

        [
            'courseid' => $courseid,
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ] = static::validate_parameters(static::diggrplus_learningpath_add_parameters(), [
            'courseid' => $courseid,
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ]);

        block_exacomp_require_teacher($courseid);

        $id = $DB->insert_record('block_exacomplps', [
            'courseid' => $courseid,
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ]);

        return [
            'success' => true,
            'id' => $id,
        ];
    }

    public static function diggrplus_learningpath_add_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'id' => new external_value(PARAM_INT),
        ));
    }

    public static function diggrplus_learningpath_delete_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_delete(int $id) {
        global $DB;

        [
            'id' => $id,
        ] = static::validate_parameters(static::diggrplus_learningpath_delete_parameters(), [
            'id' => $id,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $id,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $DB->delete_records('block_exacomplps', [
            'id' => $id,
        ]);

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_delete_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_learningpath_update_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT),
            'title' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'description' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, null),
            'visible' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, null),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_update(int $id, ?string $title, ?string $description, ?bool $visible) {
        global $DB;

        [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ] = static::validate_parameters(static::diggrplus_learningpath_update_parameters(), [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $id,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $fields = [
            'title' => $title,
            'description' => $description,
            'visible' => $visible,
        ];
        foreach ($fields as $field => $value) {
            if ($value === null) {
                continue;
            }

            $learningpath->{$field} = $value;
        }

        $DB->update_record('block_exacomplps', $learningpath);

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_update_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_learningpath_item_update_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT),
            'studentid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'visibleall' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, null),
            'visiblestudent' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, null),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_item_update(int $id, int $studentid, ?bool $visibleall, ?bool $visiblestudent) {
        global $DB;

        [
            'id' => $id,
            'studentid' => $studentid,
            'visibleall' => $visibleall,
            'visiblestudent' => $visiblestudent,
        ] = static::validate_parameters(static::diggrplus_learningpath_item_update_parameters(), [
            'id' => $id,
            'studentid' => $studentid,
            'visibleall' => $visibleall,
            'visiblestudent' => $visiblestudent,
        ]);

        $learningpath_item = $DB->get_record('block_exacomplp_items', [
            'id' => $id,
        ], '*', MUST_EXIST);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $learningpath_item->learningpathid,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        if ($visiblestudent !== null) {
            if (!$studentid) {
                throw new invalid_parameter_exception ('studentid missing');
            }

            static::learningpath_item_visibility($learningpath_item, [$studentid], $visiblestudent);
        }
        if ($visibleall !== null) {
            $learningpath_item->visible = $visibleall;
            $DB->update_record('block_exacomplp_items', $learningpath_item);

            // alle alten visibilities löschen
            $DB->execute('UPDATE {block_exacomplp_item_stud} SET visible=null WHERE itemid=?', [
                $learningpath_item->id,
            ]);
        }

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_item_update_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_learningpath_add_items_parameters() {
        return new external_function_parameters(array(
            'learningpathid' => new external_value(PARAM_INT),
            'exampleids' => new external_multiple_structure(new external_value(PARAM_INT)),
            'studentids' => new external_multiple_structure(new external_value(PARAM_INT), '', VALUE_DEFAULT, []),
            // 'visibleall' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, true),
            // 'visiblestudent' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, true),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_add_items(int $learningpathid, array $exampleids, array $studentids = []) {
        global $DB;

        [
            'learningpathid' => $learningpathid,
            'exampleids' => $exampleids,
            'studentids' => $studentids,
            // 'visibleall' => $visibleall,
            // 'visiblestudent' => $visiblestudent,
        ] = static::validate_parameters(static::diggrplus_learningpath_add_items_parameters(), [
            'learningpathid' => $learningpathid,
            'exampleids' => $exampleids,
            'studentids' => $studentids,
            // 'visibleall' => $visibleall,
            // 'visiblestudent' => $visiblestudent,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $learningpathid,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $maxSort = $DB->get_field_select('block_exacomplp_items', 'MAX(sorting)', 'learningpathid=?', [$learningpathid]);

        $visibleall = !$studentids;
        $visiblestudent = !!$studentids;

        foreach ($exampleids as $exampleid) {
            $id = $DB->insert_record('block_exacomplp_items', [
                'learningpathid' => $learningpath->id,
                'exampleid' => $exampleid,
                'sorting' => $maxSort + 1,
                'visible' => $visibleall,
            ]);

            $learningpath_item = $DB->get_record('block_exacomplp_items', [
                'id' => $id,
            ], '*', MUST_EXIST);

            static::learningpath_item_visibility($learningpath_item, $studentids, $visiblestudent);
        }

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_add_items_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    private static function learningpath_item_visibility(object $learningpath_item, array $studentids, bool $visiblestudent) {
        global $DB;

        foreach ($studentids as $studentid) {
            $item_stud = $DB->get_record('block_exacomplp_item_stud', [
                'itemid' => $learningpath_item->id,
                'studentid' => $studentid,
            ]);
            if ($item_stud) {
                if ($learningpath_item->visible === $visiblestudent) {
                    $item_stud->visible = null;
                } else {
                    $item_stud->visible = $visiblestudent;
                }
                $DB->update_record('block_exacomplp_item_stud', $item_stud);
            } else {
                $DB->insert_record('block_exacomplp_item_stud', [
                    'itemid' => $learningpath_item->id,
                    'studentid' => $studentid,
                    'visible' => $visiblestudent,
                ]);
            }
        }
    }

    public static function diggrplus_learningpath_item_delete_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_item_delete(int $id) {
        global $DB;

        [
            'id' => $id,
        ] = static::validate_parameters(static::diggrplus_learningpath_item_delete_parameters(), [
            'id' => $id,
        ]);

        $learningpath_item = $DB->get_record('block_exacomplp_items', [
            'id' => $id,
        ], '*', MUST_EXIST);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $learningpath_item->learningpathid,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $DB->delete_records('block_exacomplp_items', [
            'id' => $learningpath_item->id,
        ]);
        $DB->delete_records('block_exacomplp_item_stud', [
            'itemid' => $learningpath_item->id,
        ]);

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_item_delete_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_learningpath_item_sorting_parameters() {
        return new external_function_parameters(array(
            'learningpathid' => new external_value(PARAM_INT),
            'itemids' => new external_multiple_structure(
                new external_value(PARAM_INT)
            ),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_item_sorting(int $learningpathid, array $itemids) {
        global $DB;

        [
            'learningpathid' => $learningpathid,
            'itemids' => $itemids,
        ] = static::validate_parameters(static::diggrplus_learningpath_item_sorting_parameters(), [
            'learningpathid' => $learningpathid,
            'itemids' => $itemids,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $learningpathid,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $lpItems = $DB->get_records('block_exacomplp_items', [
            'learningpathid' => $learningpath->id,
        ]);

        foreach ($itemids as $sorting => $itemid) {
            if (empty($lpItems[$itemid])) {
                // item not in this learningpath
                continue;
            }

            $DB->update_record('block_exacomplp_items', [
                'id' => $itemid,
                'sorting' => $sorting,
            ]);
        }

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_item_sorting_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }
}
