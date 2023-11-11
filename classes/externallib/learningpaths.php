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
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_learningpath_list(int $courseid) {
        global $DB;

        [
            'courseid' => $courseid,
        ] = static::validate_parameters(static::diggrplus_learningpath_list_parameters(), [
            'courseid' => $courseid,
        ]);

        $isTeacher = block_exacomp_is_teacher($courseid);
        if ($isTeacher) {
            $learningpaths = $DB->get_records('block_exacomplps', [
                'courseid' => $courseid,
            ], 'title');
        } else {
            $learningpaths = $DB->get_records('block_exacomplps', [
                'courseid' => $courseid,
                'visible' => 1,
            ], 'title');
        }

        return [
            'learningpaths' => $learningpaths,
        ];
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

        if (!$isTeacher) {
            // security checks
            $studentid = $USER->id;
            if (!$learningpath->visible) {
                throw new invalid_parameter_exception ('Learningpath not visible');
            }
        }

        if ($isTeacher) {
            $items = $DB->get_records_sql('
                SELECT item.*, item.visible AS visibleall, item_stud.visible AS visiblestudent
                FROM {block_exacomplp_items} item
                LEFT JOIN {block_exacomplp_item_stud} item_stud ON item.id=item_stud.itemid AND item_stud.studentid=?
                WHERE item.learningpathid=?
                ORDER BY item.sorting, item.id', [$studentid, $learningpath->id]);

            $students = block_exacomp_get_students_by_course($courseid);
        } else {
            $items = $DB->get_records_sql('
                SELECT item.*, item.visible AS visibleall, item_stud.visible AS visiblestudent
                FROM {block_exacomplp_items} item
                LEFT JOIN {block_exacomplp_item_stud} item_stud ON item.id=item_stud.itemid AND item_stud.studentid=?
                WHERE item.learningpathid=?
                    AND (item_stud.visible OR (item_stud.visible IS NULL and item.visible))
                ORDER BY item.sorting, item.id', [$studentid, $learningpath->id]);

            $students = null;
        }

        foreach ($items as $lpItem) {
            $example_and_item = externallib::dakoraplus_get_example_and_item($lpItem->exampleid, $courseid);

            $lpItem->visiblestudent = $lpItem->visiblestudent ?? $lpItem->visibleall;
            $lpItem->exampletitle = $example_and_item->example->title;
            $lpItem->topictitle = $example_and_item->topictitle;

            if (!$studentid) {
                $lpItem->status = '';

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

                $lpItem->count_inprogress = 0;
                $lpItem->count_completed = 0;
                $lpItem->count_submitted = 0;
            }
        }

        $learningpath->items = $items;

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
                    'id' => new external_value (PARAM_INT),
                    'exampleid' => new external_value (PARAM_INT),
                    'exampletitle' => new external_value (PARAM_TEXT),
                    'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
                    'topictitle' => new external_value (PARAM_TEXT),
                    'sorting' => new external_value (PARAM_INT),
                    'visibleall' => new external_value (PARAM_BOOL),
                    'visiblestudent' => new external_value (PARAM_BOOL),
                    'count_inprogress' => new external_value (PARAM_INT),
                    'count_completed' => new external_value (PARAM_INT),
                    'count_submitted' => new external_value (PARAM_INT),
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_learningpath_item_add_parameters() {
        return new external_function_parameters(array(
            'learningpathid' => new external_value(PARAM_INT),
            'exampleid' => new external_value(PARAM_INT),
            // 'studentid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
            // 'visibleall' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, null),
            // 'visiblestudent' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, null),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_learningpath_item_add(int $learningpathid, int $exampleid /* , $studentid, $visibleall, $visiblestudent */) {
        global $DB;

        [
            'learningpathid' => $learningpathid,
            'exampleid' => $exampleid,
        ] = static::validate_parameters(static::diggrplus_learningpath_item_add_parameters(), [
            'learningpathid' => $learningpathid,
            'exampleid' => $exampleid,
        ]);

        $learningpath = $DB->get_record('block_exacomplps', [
            'id' => $learningpathid,
        ], '*', MUST_EXIST);

        block_exacomp_require_teacher($learningpath->courseid);

        $maxSort = $DB->get_field_select('block_exacomplp_items', 'MAX(sorting)', 'learningpathid=?', [$learningpathid]);

        $DB->insert_record('block_exacomplp_items', [
            'learningpathid' => $learningpath->id,
            'exampleid' => $exampleid,
            'sorting' => $maxSort + 1,
            'visible' => true,
        ]);

        return [
            'success' => true,
        ];
    }

    public static function diggrplus_learningpath_item_add_returns() {
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
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

        $items = $DB->get_records('block_exacomplp_items', [
            'learningpathid' => $learningpath->id
        ]);

        foreach ($itemids as $sorting => $itemid) {
            if (empty($items[$itemid])) {
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
        return new external_single_structure (array(
            'success' => new external_value (PARAM_BOOL, 'status'),
        ));
    }
}
