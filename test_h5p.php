<?php

namespace core_h5p\external;

use core_h5p\external;

global $CFG;

require __DIR__ . '/inc.php';
require_once $CFG->libdir . '/externallib.php';
require_once $CFG->dirroot . '/mod/assign/locallib.php';
require_once $CFG->dirroot . '/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot . '/lib/filelib.php';

$filename = 'arithmetic-quiz-4-4.h5p';

$url = \moodle_url::make_pluginfile_url(
    1,
    'core_h5p',
    'export',
    0,
    '/',
    $filename
);

// Call the WS.
$result = external::get_trusted_h5p_file($url->out(false), 0, 0, 1, 0);
$result = \external_api::clean_returnvalue(external::get_trusted_h5p_file_returns(), $result);
var_dump($result);


