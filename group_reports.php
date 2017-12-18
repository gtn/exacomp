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

$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
$PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);

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

$filter = block_exacomp_group_reports_get_filter();
$extra = '<input type="hidden" name="action" value="search"/>';

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

		.filter-group .filter-group-body {
			display: none;
		}
		.filter-group.visible .filter-group-body {
			display: block;
		}
		.filter-group-body > div {
			padding: 0 0 8px 25px;
		}

		.range-inputs {
			display: none;
		}

		.filter-title {
			display: inline-block;
			width: 140px;
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
		<?php 
		  echo '<h2>'.block_exacomp_get_string('display_settings').'</h2>';
		  echo $output->group_report_filters('exacomp', $filter, '', $extra, $courseid); 
		?>
	</div>
<?php

if (optional_param('action', '', PARAM_TEXT) == 'search') {
    echo html_writer::tag('h2',block_exacomp_get_string('result'));

	block_exacomp_group_reports_result($filter);
}

echo $output->footer();
