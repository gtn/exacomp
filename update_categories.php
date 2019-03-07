<?php
// This file is part of Exabis Competence Grid
//
// (c) 2019 GTN - Global Training Network GmbH <office@gtn-solutions.com>
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

require __DIR__.'/inc.php';
require_once __DIR__.'/update_categories_form.php';
// require_once __DIR__.'/example_upload_form.php';

$courseid = required_param('courseid', PARAM_INT);
$descrid = required_param('descrid', PARAM_INT);

require_login($courseid);
block_exacomp_require_teacher($context);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */ //why though? RW
$PAGE->set_url('/blocks/exacomp/update_categories.php', array('courseid' => $courseid));
$PAGE->set_title(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');


// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */
$categories = $DB->get_records_menu("block_exacompcategories",null,"","id, title");
$form = new block_exacomp_update_categories_form($_SERVER['REQUEST_URI'],
    array("descrid" => $descrid,"categories"=>$categories,"tree"=>$tree,"topicid"=>$topicid, "exampleid"=>$exampleid, "uses_activities" => $csettings->uses_activities, "activities" => $example_activities));

$form->display();

/* END CONTENT REGION */
echo $output->footer();