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

$type = optional_param('type', 'student', PARAM_TEXT);

echo $output->header_v2('group_reports');

/*
?>
<form method="post">
	<input type="text" name="q" value="<?php p($q); ?>" />
	<input type="submit" name="Suchen" />
</form>
<?php

if (!$q) {
	exit;
}
*/

$teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items($courseid);
$eval_niveaus = \block_exacomp\global_config::get_evalniveaus(true);

$filter = (array)@$_REQUEST['filter'];

function block_exacomp_print_filter($input_type, $titleid) {
	global $filter, $teacher_eval_items;

	$inputs = \block_exacomp\global_config::get_allowed_inputs($input_type);

	if (!$inputs) {
		return;
	}

	$input_filter = (array)@$filter[$input_type];

	?>
	<div class="filter-group">
		<h3><label><input type="checkbox" name="filter[<?=$input_type?>][visible]" <?php if (@$input_filter['visible']) echo 'checked="checked"'; ?> class="filter-group-checkbox"/> <?=block_exacomp_get_string($titleid)?></label></h3>
		<?php if (!empty($inputs['evalniveauid'])) { ?>
			<div><span class="filter-title"><?=block_exacomp_get_string('competence_grid_niveau')?>:</span> <?php
				foreach ([0=>'ohne Angabe'] + \block_exacomp\global_config::get_evalniveaus() as $key => $value) {
					$checked = in_array($key, (array)@$input_filter['evalniveauid']) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.'][evalniveauid][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
				}
			?></div>
		<?php } ?>
		<?php if (!empty($inputs['additionalinfo'])) { ?>
			<div><span class="filter-title"><?=block_exacomp_get_string('competence_grid_additionalinfo')?>:</span>
				<input placeholder="von" size="3" name="filter[<?=$input_type?>][additionalinfo_from]" value="<?=s(@$input_filter['additionalinfo_from'])?>"/> -
				<input placeholder="bis" size="3" name="filter[<?=$input_type?>][additionalinfo_to]" value="<?=s(@$input_filter['additionalinfo_to'])?>"/>
			</div>
		<?php } ?>
		<?php if (!empty($inputs['teacher_evaluation'])) { ?>
			<div><span class="filter-title"><?=block_exacomp_get_string('competence_grid_niveau')?>:</span> <?php
				foreach ([0=>'ohne Angabe'] + $teacher_eval_items as $key => $value) {
					$checked = in_array($key, (array)@$input_filter['teacher_evaluation']) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.'][teacher_evaluation][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
				}
			?></div>
		<?php } ?>
		<?php if (!empty($inputs['student_evaluation'])) { ?>
			<div><span class="filter-title"><?=block_exacomp_get_string('selfevaluation')?>:</span> <?php
				foreach ([-1=>'ohne Angabe'] + \block_exacomp\global_config::get_student_eval_items(false) as $key => $value) {
					$checked = in_array($key, (array)@$input_filter['student_evaluation']) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.'][evalniveauid][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
				}
			?></div>
		<?php } ?>
	</div>
	<?php
}

?>
	<style>
		.block h2 {
			font-size: 130%;
			margin: 0;
			padding: 5px;
			line-height: 100%;
		}

		.block h3 {
			font-size: 110%;
			margin: 0;
			padding: 5px;
			line-height: 100%;
		}

		.block h3 * {
			font-weight: bold;
		}

		label {
			margin: 0;
			padding: 0;
			display: inline;
		}

		.filter-group div {
			display: none;
		}

		.filter-group.visible div {
			display: block;
			padding: 0 0 8px 25px;
		}
	</style>
	<script>
		function update() {
			$(':checkbox.filter-group-checkbox').each(function(){
				if ($(this).is(':checked')) {
					$(this).closest('.filter-group').addClass('visible');
				} else {
					$(this).closest('.filter-group').removeClass('visible');
				}
			});
		}

		$(document).on('change', ':checkbox.filter-group-checkbox', update);
		$(update);
	</script>
	<div class="block">
		<h2><?=block_exacomp_get_string('filter')?></h2>
		<form method="post">
		<?php
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_SUBJECT, 'subject');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_TOPIC, 'topic');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_DESCRIPTOR, 'descriptor');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD, 'descriptor_child');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_EXAMPLE, 'example');

			$input_type = 'time';
			$input_filter = (array)@$filter[$input_type];
			$titleid = 'choosedaterange';
			?>
			<div class="filter-group">
				<h3><label><input type="checkbox" name="filter[<?=$input_type?>][visible]" <?php if (@$input_filter['visible']) echo 'checked="checked"'; ?> class="filter-group-checkbox"/> Zeitintervall</label></h3>
				<div><span class="filter-title"></span>
					<input placeholder="von" size="3" name="filter[<?=$input_type?>][time_from]" value="<?=s(@$input_filter['time_from'])?>"/> -
					<input placeholder="bis" size="3" name="filter[<?=$input_type?>][time_to]" value="<?=s(@$input_filter['time_to'])?>"/>
				</div>
			</div>
			<input type="submit" value="Filter anwenden"/>
		</form>
	</div>
<?php

if ($type == 'student') {
	$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

	echo "<h2>Ergebnis:</h2>";

	function filter_tree(&$items, $studentid, $level = 0) {
		global $courseid, $filter;

		foreach ($items as $key=>$item) {
			$student_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $item::TYPE, $item->id);
			$teacher_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $item::TYPE, $item->id);

			// $item_filter = $filter[BLOCK_EXACOMP_TYPE_SUBJECT];
			if ($item instanceof \block_exacomp\subject && @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active']) {
				unset($items[$key]);
				continue;
			}
			if ($item instanceof \block_exacomp\topic && @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
				unset($items[$key]);
				continue;
			}
			if ($item instanceof \block_exacomp\descriptor && @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['active']) {
				unset($items[$key]);
				continue;
			}

			recurse_subs('filter_tree', $item, $level + 1);
		}
	}

	function print_tree($items, $studentid, $level = 0) {
		global $courseid;

		foreach ($items as $item) {

			$student_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $item::TYPE, $item->id);
			$teacher_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $item::TYPE, $item->id);

			/*
			if ($item instanceof \block_exacomp\topic) {
				print_tree($item->descriptors, $level + 1, $studentid);
			}
			if ($item instanceof \block_exacomp\descriptor) {
				print_tree($item->examples, $level + 1, $studentid);
				print_tree($item->children, $level + 1, $studentid);
			}
			if ($item instanceof \block_exacomp\example) {
			}
			*/

			echo '<tr>';
			echo '<td style="white-space: nowrap">'.$item->get_numbering();
			echo '<td style="padding-left: '.($level * 20).'px">'.$item->title;
			echo '<td style="padding: 0 10px;">'.($student_eval?$student_eval->get_value_title():'');
			echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->additionalinfo:'');
			echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->get_value_title():'');
			echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->get_evalniveau_title():'');

			recurse_subs('print_tee', $studentid, $level + 1);
		}
	}

	echo '<h3>Schüler 1</h3>';

	echo '<table border="1">';
	echo '<tr><th></th><th></th><th colspan="4">Ausgabe der jeweiligen Bewertungen</th>';

	$studentid = 3;

	function tree_walk(&$items, $callback) {
		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		foreach ($items as $key => $item) {
			$walk_subs = function() use ($item, $callback) {
				$args = func_get_args();

				if ($item instanceof \block_exacomp\subject) {
					call_user_func_array('tree_walk', array_merge([&$item->topics, $callback], $args));
				}
				if ($item instanceof \block_exacomp\topic) {
					call_user_func_array('tree_walk', array_merge([&$item->descriptors, $callback], $args));
				}
				if ($item instanceof \block_exacomp\descriptor) {
					call_user_func_array('tree_walk', array_merge([&$item->examples, $callback], $args));
					call_user_func_array('tree_walk', array_merge([&$item->children, $callback], $args));
				}
				if ($item instanceof \block_exacomp\example) {
				}
			};

			$ret = call_user_func_array($callback, array_merge([$walk_subs, $item], $args));

			if ($ret === false) {
				unset($items[$key]);
			}
		}
	}

	tree_walk($subjects, function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
		$student_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $item::TYPE, $item->id);
		$teacher_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $item::TYPE, $item->id);

		$item_type = $item::TYPE;
		if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level > 2) {
			$item_type = BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD;
		}
		$item_filter = (array)@$filter[$item_type];

		if (!@$item_filter['visible']) {
			return false;
		}
		if (@$item_filter['evalniveauid']) {
			$value = $teacher_eval ? $teacher_eval->evalniveauid : 0;
			if (!in_array($value, $item_filter['evalniveauid'])) {
				return false;
			}
		}
		if (@$item_filter['additionalinfo_from']) {
			$value = $teacher_eval ? $teacher_eval->additionalinfo : 0;
			if ($value < $item_filter['additionalinfo_from']) {
				return false;
			}
		}
		if (@$item_filter['additionalinfo_to']) {
			$value = $teacher_eval ? $teacher_eval->additionalinfo : 0;
			if ($value > $item_filter['additionalinfo_to']) {
				return false;
			}
		}

		$walk_subs($level+1);
	});
	tree_walk($subjects, function($walk_subs, $item, $level = 0) use ($studentid, $courseid) {
		$student_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $item::TYPE, $item->id);
		$teacher_eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $item::TYPE, $item->id);

		echo '<tr>';
		echo '<td style="white-space: nowrap">'.$item->get_numbering();
		echo '<td style="padding-left: '.($level * 20).'px">'.$item->title;
		echo '<td style="padding: 0 10px;">'.($student_eval?$student_eval->get_value_title():'');
		echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->additionalinfo:'');
		echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->get_value_title():'');
		echo '<td style="padding: 0 10px;">'.($teacher_eval?$teacher_eval->get_evalniveau_title():'');

		$walk_subs($level+1);
	});

	// filter_tree($subjects, $studentid);
	// print_tree($subjects, $studentid);
	echo '</table>';

	echo '<h3>Schüler 2</h3>....';
	echo '<h3>Schüler 3</h3>....';
	echo '<h3>Schüler 4</h3>....';
}

if ($type == 'student_counts') {
	$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

	echo "<h2>Ergebnis:</h2>";

	function print_tree($items, $level = 0) {
		foreach ($items as $item) {
			echo '<tr>';
			echo '<td style="white-space: nowrap">'.$item->get_numbering();
			echo '<td style="padding-left: '.($level * 20).'px">'.$item->title;
			echo '<td style="padding: 0 10px;">4';

			if ($item instanceof \block_exacomp\subject) {
				print_tree($item->topics, $level + 1);
			}
			if ($item instanceof \block_exacomp\topic) {
				print_tree($item->descriptors, $level + 1);
			}
			if ($item instanceof \block_exacomp\descriptor) {
				print_tree($item->examples, $level + 1);
				print_tree($item->children, $level + 1);
			}
			if ($item instanceof \block_exacomp\example) {
			}
		}
	}

	echo '<h3>Schüler 1</h3>';

	echo '<table>';
	echo '<tr><th></th><th></th><th colspan="3">Anzahl gefundener Schüler</th>';
	filter_tree($subjets);
	print_tree($subjects);
	echo '</table>';

	echo '<h3>Schüler 2</h3>....';
	echo '<h3>Schüler 3</h3>....';
	echo '<h3>Schüler 4</h3>....';
}
