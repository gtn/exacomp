<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/inc.php';

function block_exacomp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
//  Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
	if ($context->contextlevel != CONTEXT_COURSE) {
		return false; 
	}
 
	// Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
	require_login($course, true, $cm);
 
	// Check the relevant capabilities - these may vary depending on the filearea being accessed.

	// Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
	$itemid = array_shift($args); // The first item in the $args array.

	// Extract the filename / filepath from the $args array.
	$filename = array_pop($args); // The last item in the $args array.

	if ($filearea == 'example_task') {
		$example = block_exacomp\example::get($itemid);
		if (!$example) {
			throw new block_exacomp_permission_exception('file not found');
		}
		$example->require_capability(BLOCK_EXACOMP_CAP_VIEW);

		$file = block_exacomp_get_file($example, $filearea);
		if (!$file) {
			return false;
		}

		$options['filename'] = $filename;
	} elseif ($filearea == 'example_solution') {
		// actually all users are allowed to see the solution
		/*
		if (!block_exacomp_is_teacher($context)) {
			return false;
		}
		*/

		$example = block_exacomp\example::get($itemid);
		if (!$example) {
			throw new block_exacomp_permission_exception('file not found');
		}
		$example->require_capability(BLOCK_EXACOMP_CAP_VIEW);

		$file = block_exacomp_get_file($example, $filearea);
		if (!$file) {
			return false;
		}

		$options['filename'] = $filename;
	} else {
		// wrong filearea
		return false;
	}

	/*
	// Use the itemid to retrieve any relevant data records and perform any security checks to see if the
	// user really does have access to the file in question.
 
	if (!$args) {
		$filepath = '/'; // $args is empty => the path is '/'
	} else {
		$filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
	}

	// Retrieve the file from the Files API.
	$fs = get_file_storage();
	$file = $fs->get_file(context_system::instance()->id, 'block_exacomp', $filearea, $itemid, $filepath, $filename);

	if (!$file) {
		echo context_system::instance()->id.", $filearea, $itemid, $filepath, $filename";
		return false; // The file does not exist.
	}
	*/
	
	// We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering. 
	// From Moodle 2.3, use send_stored_file instead.
	send_stored_file($file, 0, 0, $forcedownload, $options);
	exit;
}
