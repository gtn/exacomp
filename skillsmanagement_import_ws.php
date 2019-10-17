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

require __DIR__.'/inc.php';
require_once('lib/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$schooltypes = explode(',', required_param('schooltypes', PARAM_TAGLIST));
$xmlname = required_param('xmlname', PARAM_URL);
$lang = optional_param('lang', 'en', PARAM_TEXT);

global $DB;

$data = file_get_contents($xmlname);
\block_exacomp\data::prepare();
$success = block_exacomp\data_importer::do_import_string($data);
foreach($schooltypes as &$schooltype)
	$schooltype = $DB->get_field('block_exacompschooltypes','id',array('sourceid'=>$schooltype));

if($lang == "en") {
	$schooltypes[] = 1; //Social Competencies and Personal Competencies
	$schooltypes[] = 2;
}
else {
	$schooltypes[] = 3; //Soziale Kompetenzen, Personale Kompetenzen
	$schooltypes[] = 4;
}

block_exacomp_set_mdltype($schooltypes,$courseid);

$subjects = block_exacomp_get_subjects_for_schooltype($courseid);
$coursetopics = array();
foreach($subjects as $subject) {
	$topics = block_exacomp_get_all_topics($subject->id);
	foreach($topics as $topic)
		$coursetopics[] = $topic->id;
}
block_exacomp_set_coursetopics($courseid, $coursetopics, true);
