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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

/** Operations on Exaport's structured item content. */
class exaport_structured_content {

    /** Fail explicitly instead of using Exaport's legacy item-content API. */
    private static function require_api(array $functions): void {
        foreach ($functions as $function) {
            if (!function_exists($function)) {
                throw new \moodle_exception('Exaport is too old: the structured item-content API is required.');
            }
        }
    }

    /** Convert Exaport's flat structured-file projection to Exacomp's schema. */
    public static function student_files(\stdClass $item, array $content): array {
        self::require_api([
            'block_exaport_get_item_content_blocks',
            'block_exaport_get_item_content_files',
        ]);

        $storedfiles = [];
        foreach (block_exaport_get_item_content_blocks((int)$item->id) as $block) {
            if (($block->type ?? '') !== 'file') {
                continue;
            }
            foreach (block_exaport_get_item_content_files((int)$item->userid, (int)$block->id) as $storedfile) {
                $storedfiles[] = $storedfile;
            }
        }

        $files = array_values($content['files'] ?? []);
        if (count($storedfiles) !== count($files)) {
            throw new \coding_exception('Exaport structured file metadata does not match stored files.');
        }

        $result = [];
        foreach ($files as $index => $file) {
            $file = (array)$file;
            $storedfile = $storedfiles[$index];
            if ($storedfile->get_filename() !== (string)($file['filename'] ?? '') ||
                    $storedfile->get_mimetype() !== (string)($file['mimetype'] ?? '')) {
                throw new \coding_exception('Exaport structured file order does not match stored files.');
            }
            $fileid = (int)$storedfile->get_id();
            $result[] = [
                'id' => $fileid,
                'file' => (string)($file['url'] ?? $file['file'] ?? ''),
                'mimetype' => (string)($file['mimetype'] ?? ''),
                'filename' => (string)($file['filename'] ?? ''),
                'isimage' => $storedfile->is_valid_image(),
                'fileindex' => (string)$fileid,
            ];
        }
        return $result;
    }

    /** Replace or append structured content for an Exaport item. */
    public static function replace(\stdClass $item, $url, array $draftfiles, $replace = true): void {
        global $CFG;

        require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
        require_once $CFG->dirroot . '/blocks/exaport/inc.php';
        self::require_api([
            'block_exaport_delete_item_content',
            'block_exaport_create_link_content_block',
            'block_exaport_import_stored_file_into_content_block',
        ]);
        if ($replace) {
            block_exaport_delete_item_content($item);
        }
        if ($draftfiles) {
            foreach ($draftfiles as $draftfile) {
                block_exaport_import_stored_file_into_content_block($item, $draftfile);
            }
        } else if ($url !== null && $url !== '') {
            block_exaport_create_link_content_block($item->id, $item->name, $url);
        }
    }

    /** Delete selected files from authoritative structured file blocks. */
    public static function remove_files(\stdClass $item, array $fileids): void {
        if (!$fileids) {
            return;
        }
        self::require_api([
            'block_exaport_get_item_content_blocks',
            'block_exaport_get_item_content_files',
        ]);
        $fileids = array_fill_keys(array_filter(array_map(static function($fileid) {
            $fileid = (string)$fileid;
            return preg_match('/^[1-9][0-9]*$/D', $fileid) ? (int)$fileid : 0;
        }, $fileids)), true);
        if (!$fileids) {
            return;
        }
        foreach (block_exaport_get_item_content_blocks($item->id) as $block) {
            if ($block->type !== 'file') {
                continue;
            }
            foreach (block_exaport_get_item_content_files($item->userid, $block->id) as $file) {
                if (isset($fileids[$file->get_id()])) {
                    $file->delete();
                }
            }
        }
    }
}
