<?php
// defined('MOODLE_INTERNAL') || die();
//
// class badges_helper {
//     /**
//      * Awards a badge to a user.
//      *
//      * @param object $badge Badge object
//      * @param int $userid User to award badge to
//      * @param int $awarderid User who awards the badge (usually a teacher or system)
//      * @return void
//      */
//     public static function award_badge_to_user($badge, $userid, $awarderid) {
//         // TODO: maybe rework this to award it more directly, not manually.
//         // Accepts a badge object, user id, and awarder id (e.g. teacher or system)
//         $acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);
//         // Backwards compatibility: In Moodle 5.2, the global function process_manual_award() was
//         // moved into \core_badges\award_manager::process_manual_award() (MDL-83902).
//         // On Moodle 5.2+ we use the new class; on older Moodle versions we fall back to the global function.
//         if (class_exists('\\core_badges\\award_manager')) {
//             $awarded = \core_badges\award_manager::process_manual_award($userid, $awarderid, $acceptedroles[0], $badge->id);
//         } else {
//             $awarded = process_manual_award($userid, $awarderid, $acceptedroles[0], $badge->id);
//         }
//         if ($awarded) {
//             $data = new \stdClass();
//             $data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
//             $data->userid = $userid;
//             badges_award_handle_manual_criteria_review($data);
//         }
//     }
// }
//
