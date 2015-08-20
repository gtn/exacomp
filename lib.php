<?php

// this file needs to be here!

function block_exacomp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
//  Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false; 
    }
 
    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);
 
    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    
    if ($filearea == 'example_task') {
        // everybody can see the task
    } elseif ($filearea == 'example_solution') {
        // only teachers can see the solution
        if (!block_exacomp_is_teacher($context)) {
            return false;
        }
    } else {
        // wrong filearea
        return false;
    }
 
    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.
 
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.
 
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
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
 
    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering. 
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
    exit;
}

