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

class competence_grid extends base {
    public static function diggrplus_set_item_status_parameters() {
        return new external_function_parameters(array(
            'itemid' => new external_value(PARAM_INT, ''),
            'status' => new external_value(PARAM_TEXT, 'ENUM(inprogress, submitted, completed)'),
        ));
    }

    /**
     * set the item status
     *
     * @ws-type-write
     */
    public static function diggrplus_set_item_status(int $itemid, string $status_str) {
        global $DB, $USER;

        [
            'itemid' => $itemid,
            'status' => $status_str,
        ] = static::validate_parameters(static::diggrplus_set_item_status_parameters(), [
            'itemid' => $itemid,
            'status' => $status_str,
        ]);

        if (!in_array($status_str, ['inprogress', 'submitted', 'completed'])) {
            throw new \moodle_exception("invalid status '$status_str'");
        }

        $status = block_exacomp_convert_human_readable_item_status($status_str);

        $item = $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
        static::require_can_access_user($item->userid);

        // insert into block_exacompitem_mm
        $update = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array(
            'itemid' => $itemid,
        ));

        $update->datemodified = time();
        $update->status = $status;

        $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $update);

        return [
            "success" => true,
        ];
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_set_item_status_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL),
        ));
    }
}
