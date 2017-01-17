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

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$PAGE->set_url($_SERVER['REQUEST_URI']);
$output = block_exacomp_get_renderer();

$q = trim(optional_param('q', '', PARAM_RAW));

echo $output->header();

?>
<form method="post">
	<input type="text" name="q" value="<?php p($q); ?>" />
	<input type="submit" name="Suchen" />
</form>
<?php

if (!$q) {
	exit;
}

	$subjects = search_competence_grid($courseid, $q);
if (!$subjects) {
	echo 'keine Ergebnisse gefunden';
	exit;
}

echo "<h2>Ergebnis:</h2>";

function print_tree($items, $level = 0) {
	foreach ($items as $item) {
		echo str_repeat('&nbsp;&nbsp;&nbsp;', $level).$item->title.'<br />';

		if ($item instanceof subject) {
			print_tree($item->topics, $level+1);
		}
		if ($item instanceof topic) {
			print_tree($item->descriptors, $level+1);
		}
		if ($item instanceof descriptor) {
			print_tree($item->examples, $level+1);
			print_tree($item->children, $level+1);
		}
		if ($item instanceof example) {
		}
	}
}

print_tree($subjects);
