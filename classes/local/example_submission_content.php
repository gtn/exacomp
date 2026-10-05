<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Select and store structured content for an example submission.
 */
final class example_submission_content {

    /**
     * Resolve the submitted URL or draft file using the example-submission rules.
     *
     * @param int $userid
     * @param int $draftitemid
     * @param string $url
     * @return \stdClass
     */
    public static function select($userid, $draftitemid, $url) {
        $type = 'file';
        if (!empty($url)) {
            $url = (filter_var($url, FILTER_VALIDATE_URL) == true) ? $url : 'http://' . $url;
            $type = 'url';
        }

        $draftfiles = [];
        if (!empty($draftitemid)) {
            $usercontext = \context_user::instance($userid);
            $draftfiles = \get_file_storage()->get_area_files($usercontext->id, 'user', 'draft',
                $draftitemid, 'filepath ASC, filename ASC, id ASC', false);
        }
        if (!empty($draftitemid) && !$draftfiles) {
            throw new \moodle_exception('No uploaded file was found for this submission.');
        }

        return (object)[
            'type' => $type,
            'url' => $url,
            'draftfiles' => $draftfiles,
        ];
    }

    /**
     * Store the selected content in Exaport's structured item-content area.
     *
     * @param \stdClass $item
     * @param string $name
     * @param \stdClass $selection
     */
    public static function store(\stdClass $item, $name, \stdClass $selection) {
        if (!empty($selection->url)) {
            \block_exaport_create_link_content_block($item->id, $name, $selection->url);
        }
        foreach ($selection->draftfiles as $draftfile) {
            \block_exaport_import_stored_file_into_content_block($item, $draftfile);
        }
    }
}
