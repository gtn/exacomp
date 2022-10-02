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

/**
 * Based on moodle webservice/pluginfile.php
 * Only used for Diggr to allow viewing of hidden resources
 */

// Disable moodle specific debug messages and any errors in output.
if (!defined('NO_DEBUG_DISPLAY')) {
    define('NO_DEBUG_DISPLAY', true);
}

require_once(__DIR__ . '/inc.php');

global $DB, $CFG, $USER;

require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/lib/filelib.php');

if (empty($relativepath)) {
    $relativepath = get_file_argument();
}
$forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);
$preview = optional_param('preview', null, PARAM_ALPHANUM);
// Offline means download the file from the repository and serve it, even if it was an external link.
// The repository may have to export the file to an offline format.
$offline = optional_param('offline', 0, PARAM_BOOL);
$embed = optional_param('embed', 0, PARAM_BOOL);
//file_pluginfile($relativepath, $forcedownload, $preview, $offline, $embed); // This checks the visibility which need to be ignored

// The following is based on file_pluginfile() with the code only for the case of resources

// relative path must start with '/'
if (!$relativepath) {
    print_error('invalidargorconf');
} else if ($relativepath[0] != '/') {
    print_error('pathdoesnotstartslash');
}

// extract relative path components
$args = explode('/', ltrim($relativepath, '/'));

if (count($args) < 3) { // always at least context, component and filearea
    print_error('invalidarguments');
}

$contextid = (int)array_shift($args);
$component = clean_param(array_shift($args), PARAM_COMPONENT);
$filearea = clean_param(array_shift($args), PARAM_AREA);

list($context, $course, $cm) = get_context_info_array($contextid);

$fs = get_file_storage();

$sendfileoptions = ['preview' => $preview, 'offline' => $offline, 'embed' => $embed];

//--------------------------- if resource:

$modname = substr($component, 4);
if (!file_exists("$CFG->dirroot/mod/$modname/lib.php")) {
    send_file_not_found();
}

if ($context->contextlevel == CONTEXT_MODULE) {
    if ($cm->modname !== $modname) {
        // somebody tries to gain illegal access, cm type must match the component!
        send_file_not_found();
    }
}

if ($filearea === 'intro') {
    if (!plugin_supports('mod', $modname, FEATURE_MOD_INTRO, true)) {
        send_file_not_found();
    }

    // Require login to the course first (without login to the module).
    require_course_login($course, true);

    // Now check if module is available OR it is restricted but the intro is shown on the course page.
    $cminfo = cm_info::create($cm);
    if (!$cminfo->uservisible) {
        if (!$cm->showdescription || !$cminfo->is_visible_on_course_page()) {
            // Module intro is not visible on the course page and module is not available, show access error.
            require_course_login($course, true, $cminfo);
        }
    }

    // all users may access it
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
    if (!$file = $fs->get_file($context->id, 'mod_' . $modname, 'intro', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    // finally send the file
    send_stored_file($file, null, 0, false, $sendfileoptions);
}

//$filefunction = $component.'_pluginfile';
$filefunctionold = $modname . '_pluginfile'; // resource_pluginfile
//if (function_exists($filefunction)) {
//    // if the function exists, it must send the file and terminate. Whatever it returns leads to "not found"
//    $filefunction($course, $cm, $context, $filearea, $args, $forcedownload, $sendfileoptions);
//} else if (function_exists($filefunctionold)) {
// if the function exists, it must send the file and terminate. Whatever it returns leads to "not found"
$filefunctionold($course, $cm, $context, $filearea, $args, $forcedownload, $sendfileoptions);
//}

// based on resource_pluginfile from moodle/mod/resource/lib but without activity visible check
function resource_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Whithout the $cm this just checks if the user has access to this course.. that is enough
    require_course_login($course);

    if (!has_capability('mod/resource:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim("/$context->id/mod_resource/$filearea/0/$relativepath", '/');
    do {
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            if ($fs->get_file_by_hash(sha1("$fullpath/."))) {
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
                    break;
                }
            }
            $resource = $DB->get_record('resource', array('id' => $cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($resource->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/' . $relativepath, $cm->id, $cm->course, 'mod_resource', 'content', 0)) {
                return false;
            }
            // file migrate - update flag
            $resource->legacyfileslast = time();
            $DB->update_record('resource', $resource);
        }
    } while (false);

    // should we apply filters?
    $mimetype = $file->get_mimetype();
    if ($mimetype === 'text/html' or $mimetype === 'text/plain' or $mimetype === 'application/xhtml+xml') {
        $filter = $DB->get_field('resource', 'filterfiles', array('id' => $cm->instance));
        $CFG->embeddedsoforcelinktarget = true;
    } else {
        $filter = 0;
    }

    // finally send the file
    send_stored_file($file, null, $filter, $forcedownload, $options);
}

