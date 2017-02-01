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

namespace block_exacomp;
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';

use block_exacomp\globals as g;

class printer_TCPDF extends \TCPDF {
	private $_header = '';
	private $_style = '';

	public function __construct($orientation) {
		parent::__construct($orientation);
		$this->SetFont('helvetica', '', 9);
		$this->setHeaderFont(['helvetica', '', 9]);
	}

	private function _initPage() {
		if ($this->numpages == 0) {
			// at least one page
			$this->AddPage();
		}
	}

	public function setHeaderHTML($header) {
		$this->_header = $header;
	}

	public function setStyle($style) {
		$this->_style = $style;
	}

	public function Image($file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array()) {
		$args = func_get_args();

		// replace moodle image urls with local urls
		if (preg_match('!image.php/[^/]+/(?<component>[^/]+)/[^/]+/(?<imagename>.+)$!', $file, $matches)) {
			$path = g::$PAGE->theme->resolve_image_location($matches['imagename'], $matches['component']);
			$args[0] = $path;
		}

		return call_user_func_array(array('parent', __FUNCTION__), $args);
	}

	public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {
		$this->_initPage();

		$style = '';
		if ($this->_style) {
			$style = "<style> $this->_style </style>";
		}

		// remove input and select fields
		$html = preg_replace('!<input\s[^>]*type="text"[^>]*value="([^"]*)"[^>]*>!smiU', '$1', $html);
		$html = preg_replace_callback('!<select\s.*</select>!smiU', function($matches) {
			if (preg_match('!<option\s[^>]*selected="[^"]+"[^>]*>([^<]*)<!smiU', $matches[0], $subMatches)) {
				return $subMatches[1];
			}
			if (preg_match('!<option(\s[^>]*)?>([^<]*)<!smiU', $matches[0], $subMatches)) {
				return $subMatches[2];
			}

			return $matches[0];
		}, $html);

		return parent::writeHTML($style.$html, $ln, $fill, $reseth, $cell, $align);
	}

	public function Header() {
		if ($this->_header) {
			$this->writeHTML($this->_header);
		}
	}

	public function Footer() {
		return;
	}
}

class printer {
	static function competence_overview($selectedSubject, $selectedTopic, $selectedNiveau, $selectedStudent, $html_header, $html_tables) {
		$pdf = new printer_TCPDF('L');

		$pdf->setStyle('
			* {
				font-size: 9pt;
			}
			div {
				padding: 0;
				margin: 0;
			}
			table td {
				border: 0.2pt solid #555;
				margin: 40px;
			}
			table {
				padding: 1px 0 1px 1px; /* tcpdf only accepts padding on table tag, which gets applied to all cells */
			}
			
			.exabis_comp_info {
				background-color: #efefef;
			}
			.exabis_comp_top_name {
			}
			.exabis_comp_top_value {
				font-weight: bold;
			}
				
			tr.highlight {
				background-color: #e6e6e6;
			}
				');

		$pdf->setHeaderMargin(5);
		$pdf->SetTopMargin(40);

		foreach ($html_tables as $html_table) {
			// convert padding to spaces, because tcpdf doesn't support padding
			/*
			$html_table = preg_replace_callback('!rg2-level-([0-9]+).*rg2-indent[^>]+>(<[^>]*>)*(?=[^<])!sU', function($matches){
				return $matches[0].str_repeat('&nbsp;', max(0, $matches[1])*4); // .' level '.$matches[1];
			}, $html_table);
			*/
			// echo $html_table; exit;

			// add spacing for examples
			$html_table = preg_replace('!block_exacomp_example.*c1.*<div[^>]*>!isU', '$0&nbsp;&nbsp;&nbsp;&nbsp;', $html_table);

			// ersten beide zeilen in den header geben
			if (!preg_match('!<table.*<tbody>.*(<tr.*<tr.*</tr>)!isU', $html_table, $matches)) {
				die('error #gg98daa');
			}

			$html_table = str_replace($matches[1], '', $html_table);
			$html_table = str_replace('<tr ', '<tr nobr="true"', $html_table);

			$pdf->setHeaderHTML($html_header.$matches[0].'</table>');

			$pdf->AddPage();
			$pdf->writeHTML($html_table);
		}

		$pdf->Output();

		exit;
	}

	static function weekly_schedule($course, $student, $interval /* week or day */) {
		$first_day = optional_param('time', time(), PARAM_INT);
		if ($interval == 'week') {
			$first_day = block_exacomp_add_days($first_day, 1 - date('N', $first_day)); // get monday
			$day_cnt = 5;
		} elseif ($interval == 'day') {
			$first_day = block_exacomp_add_days($first_day, 0); // get midnight
			$day_cnt = 1;
		} else {
			print_error('wrong interval');
		}

		$days = [];

		function generate_day($day, $studentid) {
			$day->title = strftime('%a %d.%m.', $day->time);

			$examples = block_exacomp_get_examples_for_start_end_all_courses($studentid, $day->time, block_exacomp_add_days($day->time, 1) - 1);

			$examples = block_exacomp_get_json_examples($examples);
			$examples = array_map(function($o) {
				return (object)$o;
			}, $examples);

			foreach ($examples as $example) {
				// get data
				$example->descriptors = block_exacomp_get_descriptors_by_example($example->id);
				$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $studentid);

				// find start slot
				for ($i = 0; $i < count($day->slots); $i++) {
					if ($day->slots[$i]->start_time >= $example->start) {
						$example->start_slot = $i;
						$example->end_slot = $i;
						break;
					}
				}

				// find end slot
				for ($i = $example->start_slot; $i < count($day->slots); $i++) {
					if ($day->slots[$i]->start_time >= $example->end) {
						break;
					}
					$example->end_slot = $i;
				}

				$example->rowspan = $example->end_slot - $example->start_slot + 1;
			}

			// first sort by start time, then by duration (same as fullcalendar)
			usort($examples, function($a, $b) {
				return
					(
					$a->start <> $b->start
						? $a->start > $b->start
						: $a->rowspan < $b->rowspan
					) ? 1 : -1;
			});

			// init empty columns
			foreach ($day->slots as $slot) {
				$slot->cols = array_fill(0, 1000, false);
			}

			$day->colspan = 1; // the max colspan for this day
			foreach ($examples as $example) {
				for ($col_i = 0; $col_i < 1000; $col_i++) {
					// check if the event can be inserted into this column (all cells are free)
					$ok = true;
					for ($slot_i = $example->start_slot; $slot_i <= $example->end_slot; $slot_i++) {
						if ($day->slots[$slot_i]->cols[$col_i]) {
							$ok = false;
							break;
						}
					}

					// yes, can be inserted here
					if ($ok) {
						for ($slot_i = $example->start_slot; $slot_i <= $example->end_slot; $slot_i++) {
							$day->slots[$slot_i]->cols[$col_i] = true;
						}

						$day->slots[$example->start_slot]->cols[$col_i] = $example;
						$day->colspan = max($day->colspan, $col_i + 1);
						break;
					}

					// no -> continue to next column
				}
			}
		}

		for ($i = 0; $i < $day_cnt; $i++) {
			// build the day object
			$time = block_exacomp_add_days($first_day, $i);
			$days[$time] = (object)[
				'time' => $time,
				'slots' => array_map(function($x) {
					return (object)$x;
				}, block_exacomp_build_json_time_slots($time)),
			];

			// load the events and columns for this day
			generate_day($days[$time], $student->id);
		}


		// Instanciation of inherited class
		$pdf = new printer_TCPDF($interval == 'week' ? 'L' : null /* landscape for weekly print */);

		$pdf->setHeaderMargin(5);
		$pdf->SetTopMargin(25);

		$pdf->setStyle('
			* {
				font-size: 9pt;
			}
			.event-default {
				color: #fff;
				background-color: #026cc5;
			}
			.state4 {
				background-color: rgb(246, 46, 39);
			}

			.state3 {
				background-color: rgb(189, 189, 189);
			}

			.state5 {
				background-color: rgb(24, 164, 6);
			}

			.state9 {
				background-color: rgb(240, 230, 0);
			}
			.different-course {
				background-color: #acbcca;
			}
		');

		$header = '
			<table><tr>
				<td style="font-size: 12pt; font-weight: bold;" align="left">Wochenplan</td>
				<td style="font-size: 12pt; font-weight: bold;" align="right">Kursteilnehmer/in: '.fullname($student).'</td>
			</tr></table>
			&nbsp;<br />
			<table border="0.1" style="padding: 1px">';
		$header .= '<tr><td></td>';
		foreach ($days as $day) {
			$header .= '<td colspan="'.$day->colspan.'" align="center">'.$day->title.'</td>';
		}
		$header .= '</tr></table>';
		$pdf->setHeaderHTML($header);

		$tbl = '<table border="0.1" style="padding: 1px">';
		$color_i = 0;
		foreach (block_exacomp_build_json_time_slots() as $slot_i => $slot) {
			$tbl .= '<tr nobr="true"';
			if ($slot['name']) {
				$color_i++;
			}
			if ($color_i % 2) {
				$tbl .= ' style="background-color:#EEEEEE;"';
			}
			$tbl .= '><td>'.$slot["name"].'</td>';
			foreach ($days as $day) {
				for ($col_i = 0; $col_i < $day->colspan; $col_i++) {
					$example = $day->slots[$slot_i]->cols[$col_i];
					if (is_object($example)) {

						$class = 'event-default';
						if (!empty($example->state)) {
							$state_text = 'state'.$example->state; // TODO: change state text
							$class .= ' state'.$example->state;
						} elseif ($example->courseid != $course->id) {
							$state_text = '';
							$class .= ' different-course';
						} else {
							$state_text = '';
						}


						$course = get_course($example->courseid);
						$tbl .= '<td rowspan="'.$example->rowspan.'" class="'.$class.'">';
						// for now don't print state_text
						// if ($state_text) $tbl .= '<b>'.$state_text.':</b><br />';
						$tbl .= '<b>'.$course->shortname.':</b><br />';
						$tbl .= $example->title;

						if ($example->description) {
							$tbl .= '<br />'.$example->description;
						}

						if ($example->student_evaluation_title) {
							$tbl .= '<br />S: '.$example->student_evaluation_title;
						}

						$teacher_evaluation = [];
						if ($example->teacher_evaluation_title) {
							$teacher_evaluation[] = $example->teacher_evaluation_title;
						}
						if ($teacher_evaluation) {
							$tbl .= '<br />L: '.join(' / ', $teacher_evaluation);
						}
						/*if ($example->descriptors) {
							$tbl .= '<br />';
							foreach ($example->descriptors as $descriptor) {
								$tbl .= '<br />• '.$descriptor->title;
							}
						}*/

						$tbl .= '</td>';
					} else if (!$example) {
						$tbl .= '<td></td>';
					}
				}
			}
			$tbl .= '</tr>';
		}
		$tbl .= '</table>';

		// $tbl .= '<b>Legende:</b><br />';

		$pdf->writeHTML($tbl);

		$pdf->Output();
		exit;
	}
}
