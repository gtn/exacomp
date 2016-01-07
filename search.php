<?php

namespace block_exacomp;

require_once __DIR__."/inc.php";

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
