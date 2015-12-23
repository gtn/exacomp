<?php

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

$searchResults = search_competence_grid($courseid, $q);
if (!(array)$searchResults) {
	echo 'keine Ergebnisse gefunden';
	exit;
}

echo "<h2>Ergebnis:</h2>";

foreach ($searchResults as $type=>$results) {
	foreach ($results as $object) {
		echo "$type => ".$object->title.'<br />';
	}
}


function search_competence_grid($courseid, $q) {
	$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

	$queryItems = preg_split('![\s,]+!', trim($q));
	foreach ($queryItems as &$q) {
		$q = \core_text::strtolower($q);
	}
	unset($q);

	$searchResults = (object)[
		'subjects' => [],
		'topics' => [],
		'descriptors' => [],
		'examples' => [],
	];
	$find = function($object) use ($queryItems) {
		foreach ($queryItems as $q) {
			$found = false;
			// for now, just search all fields for the search string
			foreach ($object->getData() as $value) {
				if (is_array($value) || is_object($value)) continue;

				if (\core_text::strpos(\core_text::strtolower($value), $q) !== false) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				return false;
			}
		}

		return true;
	};

	$find_example = function($example) use ($searchResults, $find) {
		if ($find($example)) {
			$searchResults->examples[$example->id] = $example;
		}
	};
	$find_descriptor = function($descriptor) use ($searchResults, $find, &$find_descriptor, $find_example) {
		if ($find($descriptor)) {
			$searchResults->descriptors[$descriptor->id] = $descriptor;
		}

		array_walk($descriptor->examples, $find_example);

		array_walk($descriptor->children, $find_descriptor);
	};
	$find_topic = function($topic) use ($searchResults, $find, $find_descriptor) {
		if ($find($topic)) {
			$searchResults->topics[$topic->id] = $topic;
		}

		array_walk($topic->descriptors, $find_descriptor);
	};
	$find_subject = function($subject) use ($searchResults, $find, $find_topic) {
		if ($find($subject)) {
			$searchResults->subjects[$subject->id] = $subject;
		}

		array_walk($subject->topics, $find_topic);
	};

	array_walk($subjects, $find_subject);

	$searchResults = (object)array_filter((array)$searchResults, function($tmp) { return !empty($tmp); });
	return $searchResults;
}
