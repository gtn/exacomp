<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp\local;

defined('MOODLE_INTERNAL') || die();

/** Compatibility boundary for Exacomp's optional Exaport integration. */
final class exaport_compatibility {
    /** Matching Exaport release with guarded optional integration. */
    public const MIN_VERSION = 2026101903;

    /**
     * Require an upgraded Exaport and its complete content API before any side effects.
     *
     * This is only called by integration workflows; Exacomp can be installed and
     * used without Exaport. Checking the installed version also rejects new PHP
     * files whose database migration has not finished yet.
     */
    public static function require_available(): void {
        global $CFG;

        $library = $CFG->dirroot . '/blocks/exaport/lib/lib.php';
        if ((int)get_config('block_exaport', 'version') < self::MIN_VERSION || !is_readable($library)) {
            throw new \moodle_exception('exaportupdaterequired', 'block_exacomp', '', self::MIN_VERSION);
        }

        require_once($library);
        foreach ([
            'block_exaport_get_item_content_webservice_data',
            'block_exaport_get_item_content_blocks',
            'block_exaport_get_item_content_block',
            'block_exaport_get_item_content_files',
            'block_exaport_create_link_content_block',
            'block_exaport_import_stored_file_into_content_block',
            'block_exaport_delete_item_content',
            'block_exaport_delete_item_content_block',
            'block_exaport_delete_item',
        ] as $function) {
            if (!function_exists($function)) {
                throw new \moodle_exception('exaportupdaterequired', 'block_exacomp', '', self::MIN_VERSION);
            }
        }
    }
}
