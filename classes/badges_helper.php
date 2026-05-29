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
//         if (process_manual_award($userid, $awarderid, $acceptedroles[0], $badge->id)) {
//             $data = new \stdClass();
//             $data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
//             $data->userid = $userid;
//             badges_award_handle_manual_criteria_review($data);
//         }
//     }
// }
//
