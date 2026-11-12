<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp\local;

defined('MOODLE_INTERNAL') || die();

/** Exacomp stays usable without Exaport, but pauses with an installed Exaport migration. */
final class migration_pause {
    public static function is_pending(): bool {
        return class_exists('\\block_exaport\\local\\item_content_migration') &&
            \block_exaport\local\item_content_migration::is_pending();
    }

    public static function require_ready(): void {
        if (self::is_pending() && !deferred_events::is_replaying()) {
            throw new \moodle_exception('itemcontentmigrationpending', 'block_exacomp');
        }
    }
}
