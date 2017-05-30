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

echo $output->header_v2('tab_group_reports');

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

if (!$filter) {
	// default filter
	@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
	@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
	@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
}

// active means, we also have to loop over those items
if (@$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['visible']) {
	@$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active'] = true;
}
if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active']) {
	@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
}
if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
	@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
}
if (@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active']) {
	@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
}
if (@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
	@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
}

function block_exacomp_print_filter($input_type, $titleid) {
	global $filter, $teacher_eval_items;

	$inputs = \block_exacomp\global_config::get_allowed_inputs($input_type);

	if (!$inputs) {
		return;
	}

	$input_filter = (array)@$filter[$input_type];

	?>
	<div class="filter-group">
		<h3>
			<label><input type="checkbox" name="filter[<?= $input_type ?>][visible]" <?php if (@$input_filter['visible']) {
					echo 'checked="checked"';
				} ?> class="filter-group-checkbox"/> <?= block_exacomp_get_string($titleid) ?></label></h3>
		<?php if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID])) { ?>
			<div><span class="filter-title"><?= block_exacomp_get_string('competence_grid_niveau') ?>:</span> <?php
				foreach ([0 => 'ohne Angabe'] + \block_exacomp\global_config::get_evalniveaus() as $key => $value) {
					$checked = in_array($key, (array)@$input_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID]) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.']['.BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID.'][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
				}
				?></div>
		<?php } ?>
		<?php if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO])) { ?>
			<div><span class="filter-title"><?= block_exacomp_get_string('competence_grid_additionalinfo') ?>:</span>
				<input placeholder="von" size="3" name="filter[<?= $input_type ?>][additionalinfo_from]" value="<?= s(@$input_filter['additionalinfo_from']) ?>"/> -
				<input placeholder="bis" size="3" name="filter[<?= $input_type ?>][additionalinfo_to]" value="<?= s(@$input_filter['additionalinfo_to']) ?>"/>
			</div>
		<?php } ?>
		<?php if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) { ?>
			<div><span class="filter-title"><?= block_exacomp_get_string('teacherevaluation') ?>:</span> <?php
				foreach ([-1 => 'ohne Angabe'] + $teacher_eval_items as $key => $value) {
					$checked = in_array($key, (array)@$input_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.']['.BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION.'][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
				}
				?></div>
		<?php } ?>
		<?php if (!empty($inputs[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) { ?>
			<div><span class="filter-title"><?= block_exacomp_get_string('selfevaluation') ?>:</span> <?php
				foreach ([0 => 'ohne Angabe'] + \block_exacomp\global_config::get_student_eval_items(false) as $key => $value) {
					$checked = in_array($key, (array)@$input_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) ? 'checked="checked"' : '';
					echo '<label><input type="checkbox" name="filter['.$input_type.']['.BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION.'][]" value="'.s($key).'" '.$checked.'/>  '.$value.'</label>&nbsp;&nbsp;&nbsp;';
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
				$(':checkbox.filter-group-checkbox').each(function () {
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
		<h2><?= block_exacomp_trans('de:Anzeigeoption') ?></h2>
		<form method="post">
			<?php
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_SUBJECT, 'subject');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_TOPIC, 'topic');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT, 'descriptor');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD, 'descriptor_child');
			block_exacomp_print_filter(BLOCK_EXACOMP_TYPE_EXAMPLE, 'example');

			$input_type = 'time';
			$input_filter = (array)@$filter[$input_type];
			$titleid = 'choosedaterange';
			?>
			<div class="filter-group">
				<h3>
					<label><input type="checkbox" name="filter[<?= $input_type ?>][active]" <?php if (@$input_filter['active']) {
							echo 'checked="checked"';
						} ?> class="filter-group-checkbox"/> Zeitintervall TODO</label></h3>
				<div><span class="filter-title"></span>
					<input placeholder="von" size="3" name="filter[<?= $input_type ?>][from]" value="<?= s(@$input_filter['from']) ?>"/> -
					<input placeholder="bis" size="3" name="filter[<?= $input_type ?>][to]" value="<?= s(@$input_filter['to']) ?>"/>
					<select>
						<option>Eingabezeitraum</option>
					</select>
				</div>
			</div>
			<input type="submit" value="Filter anwenden"/>
		</form>
	</div>
<?php

function block_exacomp_tree_walk(&$items, $callback) {
	$args = func_get_args();
	array_shift($args);
	array_shift($args);

	foreach ($items as $key => $item) {
		$walk_subs = function() use ($item, $callback) {
			global $filter;

			$args = func_get_args();

			if ($item instanceof \block_exacomp\subject && @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->topics, $callback], $args));
			}
			if ($item instanceof \block_exacomp\topic && @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->descriptors, $callback], $args));
			}
			if ($item instanceof \block_exacomp\descriptor && @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->examples, $callback], $args));
			}
			if ($item instanceof \block_exacomp\descriptor && @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->children, $callback], $args));
			}
		};

		$ret = call_user_func_array($callback, array_merge([$walk_subs, $item], $args));

		if ($ret === false) {
			unset($items[$key]);
		}
	}
}

if ($type == 'student') {
	$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

	echo "<h2>Ergebnis:</h2>";

	foreach (block_exacomp_get_students_by_course($courseid) as $student) {
		echo '<h3>'.fullname($student).'</h3>';

		$studentid = $student->id;

		block_exacomp_tree_walk($subjects, function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
			$eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item::TYPE, $item->id);

			$item_type = $item::TYPE;
			if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
				$item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
			}

			$item_filter = (array)@$filter[$item_type];

			$item->visible = @$item_filter['visible'];

			if (!@$item_filter['active']) {
				return false;
			}

			if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID]) {
				$value = @$eval->evalniveauid ?: 0;
				if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID])) {
					/*
					$item->visible = false;
					return;
					*/
					return false;
				}
			}
			if (@$item_filter['additionalinfo_from']) {
				$value = @$eval->additionalinfo ?: 0;
				if ($value < $item_filter['additionalinfo_from']) {
					return false;
				}
			}
			if (@$item_filter['additionalinfo_to']) {
				$value = @$eval->additionalinfo ?: 0;
				if ($value > $item_filter['additionalinfo_to']) {
					return false;
				}
			}

			if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) {
				$value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
				if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
					return false;
				}
			}
			if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) {
				$value = @$eval->studentevaluation ?: 0;
				if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) {
					return false;
				}
			}

			if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher < @$filter['time']['from']) {
				$item->visible = false;
			}
			if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher > @$filter['time']['to']) {
				$item->visible = false;
			}

			$walk_subs($level + 1);
		});

		ob_start();
		block_exacomp_tree_walk($subjects, function($walk_subs, $item, $level = 0) use ($studentid, $courseid) {
			$eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item::TYPE, $item->id);

			if (!$item->visible) {
				// walk subs with same level
				$walk_subs($level);

				return;
			}

			echo '<tr>';
			echo '<td style="white-space: nowrap">'.$item->get_numbering();
			echo '<td style="padding-left: '.($level * 20).'px">'.$item->title;
			echo '<td style="padding: 0 10px;">'.$eval->get_student_value_title();
			echo '<td style="padding: 0 10px;">'.$eval->additionalinfo;
			echo '<td style="padding: 0 10px;">'.$eval->get_teacher_value_title();
			echo '<td style="padding: 0 10px;">'.$eval->get_evalniveau_title();

			$walk_subs($level + 1);
		});
		$output = ob_get_clean();

		if (!$output) {
			echo 'Keine Einträge gefunden';
		} else {
			echo '<table border="1" width="100%">';
			echo '<tr><th></th><th></th><th colspan="4">Ausgabe der jeweiligen Bewertungen</th>';
			echo $output;
			echo '</table>';
		}
	}
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
