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
use PhpOffice\PhpWord\Escaper\RegExp;
use PhpOffice\PhpWord\Escaper\Xml;


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
				background-color: #593d1e;
			}
			.different-course {
				background-color: #acbcca;
			}
		');

		$header = '
			<table><tr>
				<td style="font-size: 12pt; font-weight: bold;" align="left">'.block_exacomp_get_string("weekly_schedule").'</td>
				<td style="font-size: 12pt; font-weight: bold;" align="right">'.block_exacomp_get_string("participating_student").': '.fullname($student).'</td>
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
			if(block_exacomp_get_string($slot['name'])!='[[]]'){
			    $tbl .= '><td>'.block_exacomp_get_string($slot['name']).'</td>';
			}else{
			    $tbl .= '><td></td>';
			}
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


    static function block_exacomp_generate_report_annex_docx($courseid, $dataRow) {
        global $CFG;
        $templateContents = '';
        $templateFile = __DIR__.'/../reports/tmpl_annex.docx';
        $resultFilename = 'gruppenbericht.docx';
        $fs = get_file_storage();
        $files = $fs->get_area_files($courseid, 'block_exacomp', 'report_annex', 0);
        foreach ($files as $f) {
            if (!$f->is_directory()) {
                $templateFile = $f->copy_content_to_temp();
                $resultFilename = $f->get_filename();
                //$templateContents = $f->get_content();
            }
        }

        if (!file_exists($templateFile)) {
            throw new \Exception("template 'tmpl_annex' not found");
        }

        \PhpOffice\PhpWord\Settings::setTempDir($CFG->tempdir);
        $templateProcessor = new \block_exacomp\TemplateProcessor($templateFile);

        $templateProcessor->duplicateDocumentBody(count($dataRow));
        $toDeleteBlocks = 0;
        foreach ($dataRow as $studentId => $reportData) {
            $templateProcessor->setValue('course', $reportData['courseData']->fullname, 1);
            $templateProcessor->setValue('student_name', fullname($reportData['studentData']), 1);
            $templateProcessor->setValue('name', fullname($reportData['studentData']), 1);
            $dateOfB = block_exacomp_get_date_of_birth($studentId);
            $templateProcessor->setValue('geburtsdatum', $dateOfB, 1);
            //$templateProcessor->setValue('courseid', $reportData['courseData']->id, 1);
            $templateProcessor->setValue('courseid', $reportData['courseData']->idnumber, 1);
            // subjects
            $subjectsCount = count($reportData['subjects']);
            $templateProcessor->cloneBlock('subjectif', $subjectsCount);
            // subject table data
            $subjectKeys = array_keys($reportData['subjects']);
            $lastSubjectKey = array_pop($subjectKeys);
            $subjectsCount = 0;
            foreach ($reportData['subjects'] as $subjKey => $subject) {
                $templateProcessor->setValue('subject', $subject->title, 1);
                // topics
                $subjectEntries = 0;
                foreach($subject->topics as $topic) {
                    $templateProcessor->cloneRowToEnd("topic");
                    $templateProcessor->cloneRowToEnd("descriptor");
                    if ($topic->evaluation->teacherevaluation > 0) {
                        $subjectEntries++;
                        $templateProcessor->setValue("topic", $topic->get_numbering().' '.$topic->title, 1);
                        $templateProcessor->setValue("n", $topic->evaluation->get_evalniveau_title(), 1);
                        $templateProcessor->setValue("nu", $topic->evaluation->teacherevaluation == 0 ? 'X' : '', 1);
                        $templateProcessor->setValue("ne", $topic->evaluation->teacherevaluation == 1 ? 'X' : '', 1);
                        $templateProcessor->setValue("tw", $topic->evaluation->teacherevaluation == 2 ? 'X' : '', 1);
                        $templateProcessor->setValue("ue", $topic->evaluation->teacherevaluation == 3 ? 'X' : '', 1);
                        $templateProcessor->setValue("ve", $topic->evaluation->teacherevaluation == 4 ? 'X' : '', 1);
                    } else {
                        $templateProcessor->deleteRow("topic");
                    }
                    // descriptors
                    foreach ($topic->descriptors as $descriptor) {
                        $templateProcessor->duplicateRow("descriptor");
                        if ($descriptor->evaluation->teacherevaluation > 0) {
                            $subjectEntries++;
                            $templateProcessor->setValue("descriptor", $descriptor->get_numbering().' '.$descriptor->title, 1);
                            $templateProcessor->setValue("n", $descriptor->evaluation->get_evalniveau_title(), 1);
                            $templateProcessor->setValue("nu", $descriptor->evaluation->teacherevaluation == 0 ? 'X' : '', 1);
                            $templateProcessor->setValue("ne", $descriptor->evaluation->teacherevaluation == 1 ? 'X' : '', 1);
                            $templateProcessor->setValue("tw", $descriptor->evaluation->teacherevaluation == 2 ? 'X' : '', 1);
                            $templateProcessor->setValue("ue", $descriptor->evaluation->teacherevaluation == 3 ? 'X' : '', 1);
                            $templateProcessor->setValue("ve", $descriptor->evaluation->teacherevaluation == 4 ? 'X' : '', 1);
                        } else {
                            //$toDeleteDesc++;
                            $templateProcessor->deleteRow("descriptor");
                        }
                    }
                    $templateProcessor->deleteRow("descriptor");

                }
                $templateProcessor->deleteRow("topic");
                $templateProcessor->deleteRow("descriptor");

                if ($subjectEntries > 0) {
                    $templateProcessor->setValue("message", '', 1);
                    $templateProcessor->cloneBlockOnlyFirst('subjectclean');
                    $subjectsCount++;
                } else {
                    $templateProcessor->setValue("message", '', 1);
                    $templateProcessor->replaceMarkerName('subjectclean', 'todelete', true);
                    $templateProcessor->replaceMarkerName('/subjectclean', '/todelete', true);
                    $toDeleteBlocks++;
                    if ($subjectsCount == 0 && $subjKey == $lastSubjectKey) { // Any graded subjects for student
                        // empty student
                    }
                }
            }
        }
        for ($i=0; $i<=$toDeleteBlocks; $i++) {
            $templateProcessor->replaceBlock('todelete', '');
        }
        //echo $templateProcessor->getDocumentMainPart(); exit;
        // save as a random file in temp file
        $temp_file = tempnam($CFG->tempdir, 'exacomp');
        $templateProcessor->saveAs($temp_file);
        require_once $CFG->dirroot.'/lib/filelib.php';
        send_temp_file($temp_file, $resultFilename);

    }

}

class Slice
{

    function __construct($string, $start, $end)
    {
        $this->before = substr($string, 0, $start);
        $this->slice = substr($string, $start, $end - $start);
        $this->after = substr($string, $end);
    }

    function get()
    {
        return $this->slice;
    }

    function set($value)
    {
        $this->slice = $value;

        return $this;
    }

    function join()
    {
        return $this->before . $this->slice . $this->after;
    }
}

class TemplateProcessor extends \PhpOffice\PhpWord\TemplateProcessor
{

    function getDocumentMainPart()
    {
        return $this->tempDocumentMainPart;
    }

    function setDocumentMainPart($part)
    {
        $this->tempDocumentMainPart = $part;
    }

    function setValues($data)
    {
        foreach ($data as $key => $value) {
            $this->setValue($key, $value);
            /*
             * $value = ;
             * $content = str_replace('{'.$key.'}', $value, $content);
             * $content = str_replace('>'.$key.'<', '>'.$value.'<', $content);
             */
        }
    }

    function setValue($search, $replace, $limit = self::MAXIMUM_REPLACEMENTS_DEFAULT)
    {
        $replace = $this->escape($replace);
        $replace = str_replace([
            "\r",
            "\n"
        ], [
            '',
            '</w:t><w:br/><w:t>'
        ], $replace);
        
        return $this->setValueRaw($search, $replace, $limit);
    }

    function setValueRaw($search, $replace, $limit = self::MAXIMUM_REPLACEMENTS_DEFAULT)
    {
        $oldEscaping = \PhpOffice\PhpWord\Settings::isOutputEscapingEnabled();
        
        // it's a raw value
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(false);
        
        $ret = parent::setValue($search, $replace, $limit);
        
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled($oldEscaping);
        
        return $ret;
    }

    function applyFilters($filters)
    {
        foreach ($filters as $filter) {
            $this->tempDocumentMainPart = $filter($this->tempDocumentMainPart);
        }
    }

    function applyFiltersAllParts($filters)
    {
        foreach ($filters as $filter) {
            $this->tempDocumentHeaders = $filter($this->tempDocumentHeaders);
            $this->tempDocumentMainPart = $filter($this->tempDocumentMainPart);
            $this->tempDocumentFooters = $filter($this->tempDocumentFooters);
        }
    }

    function replaceWords($data)
    {
        foreach ($data as $key => $value) {
            $this->tempDocumentMainPart = str_replace('>' . $key . '<', '>' . $value . '<', $this->tempDocumentMainPart);
        }
    }

    function check()
    {
        if (preg_match('!\\$(.*(>|{)(?<name>[a-z{}].*)<)!iU', $this->tempDocumentMainPart, $matches)) {
            throw new \Exception("fehler in variable ${matches['name']}");
        }
    }

    function tagPos($search)
    {
        if ('${' !== substr($search, 0, 2) && '}' !== substr($search, - 1)) {
            $search = '${' . $search . '}';
        }
        
        $tagPos = strpos($this->tempDocumentMainPart, $search);
        if (! $tagPos) {
            throw new \Exception("Can't find '$search'");
        }
        
        return $tagPos;
    }

    public function cloneBlockAndSetNewVarNames($blockname, $clones, $replace, $varname)
    {
        $clone = $this->cloneBlock($blockname, $clones, $replace);
        
        for ($i = 0; $i < $clones; $i ++) {
            $regExpEscaper = new RegExp();
            $this->tempDocumentMainPart = preg_replace($regExpEscaper->escape($clone), str_replace('${', '${' . $varname . $i . '-', $clone), $this->tempDocumentMainPart, 1);
        }
    }

    function cloneRowToEnd($search)
    {
        $tagPos = $this->tagPos($search);
        
        $rowStart = $this->findRowStart($tagPos);
        $rowEnd = $this->findRowEnd($tagPos);
        $xmlRow = $this->getSlice($rowStart, $rowEnd);
        
        $lastRowEnd = strpos($this->tempDocumentMainPart, '</w:tbl>', $tagPos);
        
        $result = $this->getSlice(0, $lastRowEnd);
        $result .= $xmlRow;
        $result .= $this->getSlice($lastRowEnd);
        
        $this->tempDocumentMainPart = $result;
    }

    function duplicateRow($search)
    {
        $tagPos = $this->tagPos($search);
        
        $rowStart = $this->findRowStart($tagPos);
        $rowEnd = $this->findRowEnd($tagPos);
        $xmlRow = $this->getSlice($rowStart, $rowEnd);
        
        $result = $this->getSlice(0, $rowEnd);
        $result .= $xmlRow;
        $result .= $this->getSlice($rowEnd);
        
        $this->tempDocumentMainPart = $result;
    }

    function deleteRow($search)
    {
        $this->cloneRow($search, 0);
    }

    /*
     * function strTagPos($string, $tag, $offset) {
     * $tagStart = strpos($string, '<'.$tag.' ', $offset);
     *
     * if (!$tagStart) {
     * $tagStart = strpos($string, '<'.$tag.'>', $string);
     * }
     * if (!$tagStart) {
     * throw new Exception('Can not find the start position of tag '.$tag.'.');
     * }
     *
     * return $tagStart;
     * }
     *
     * function strrTagPos($string, $tag, $offset) {
     * $tagStart = strrpos($this->tempDocumentMainPart, '<w:'.$tag.' ', ((strlen($this->tempDocumentMainPart) - $offset) * -1));
     *
     * if (!$tagStart) {
     * $tagStart = strrpos($this->tempDocumentMainPart, '<w:'.$tag.'>', ((strlen($this->tempDocumentMainPart) - $offset) * -1));
     * }
     * if (!$tagStart) {
     * throw new Exception('Can not find the start position of tag '.$tag.'.');
     * }
     *
     * return $tagStart;
     * }
     *
     * function findTagEnd($tag, $offset) {
     * $search = '</w:'.$tag.'>';
     *
     * return strpos($this->tempDocumentMainPart, $search, $offset) + strlen($search);
     * }
     */
    function splitByTag($string, $tag)
    {
        $rest = $string;
        $parts = [];
        
        while ($rest) {
            if (! preg_match('!^(?<before>.*)(?<tag><w:' . $tag . '[\s>].*</w:' . $tag . '>|<w:' . $tag . '(\s[^>]+)?/>)!Uis', $rest, $matches)) {
                $parts[] = $rest;
                break;
            }
            
            if ($matches['before']) {
                $parts[] = $matches['before'];
            }
            $parts[] = $matches['tag'];
            
            $rest = substr($rest, strlen($matches[0]));
        }
        
        return $parts;
    }

    function rfindTagStart($tag, $offset)
    {
        /*
         * if (!preg_match('!<w:'.$tag.'[\s>].*$!Uis', substr($this->tempDocumentMainPart, 0, $offset), $matches)) {
         * throw new \Exception('tagStart $tag not found');
         * }
         *
         * echo $offset - strlen($matches[0]);
         */
        $tagStart = strrpos($this->tempDocumentMainPart, '<w:' . $tag . ' ', ((strlen($this->tempDocumentMainPart) - $offset) * - 1));
        
        if (! $tagStart) {
            $tagStart = strrpos($this->tempDocumentMainPart, '<w:' . $tag . '>', ((strlen($this->tempDocumentMainPart) - $offset) * - 1));
        }
        if (! $tagStart) {
            throw new Exception('Can not find the start position of tag ' . $tag . '.');
        }
        
        return $tagStart;
    }

    function findTagEnd($tag, $offset)
    {
        $search = '</w:' . $tag . '>';
        
        return strpos($this->tempDocumentMainPart, $search, $offset) + strlen($search);
    }

    function slice($string, $start, $end)
    {
        return new Slice($string, $start, $end);
    }

    function duplicateCol($search, $numberOfCols = 1)
    {
        $tagPos = $this->tagPos($search);
        
        $table = $this->slice($this->tempDocumentMainPart, $this->rfindTagStart('tbl', $tagPos), $this->findTagEnd('tbl', $tagPos));
        
        $splits = static::splitByTag($table->get(), 'gridCol');
        
        preg_match('!(^.*w:w=")([0-9]+)(".*)$!', $splits[1], $firstCol);
        preg_match('!(^.*w:w=")([0-9]+)(".*)$!', $splits[2], $newCol);
        array_shift($firstCol);
        array_shift($newCol);
        
        $newWidth = $firstCol[1] - $newCol[1] * ($numberOfCols - 1);
        $firstCol[1] = $newWidth;
        
        $splits[1] = join('', $firstCol);
        $splits[2] = str_repeat($splits[2], $numberOfCols);
        
        $splits = static::splitByTag(join('', $splits), 'tc');
        
        $splits[1] = preg_replace('!(w:w=")[0-9]+!', '${1}' . $newWidth, $splits[1]);
        $splits[4] = preg_replace('!(w:w=")[0-9]+!', '${1}' . $newWidth, $splits[4]);
        
        $splits[2] = str_repeat($splits[2], $numberOfCols);
        $splits[5] = str_repeat($splits[5], $numberOfCols);
        
        $table->set(join('', $splits));
        
        $this->tempDocumentMainPart = $table->join();
    }

    function escape($str)
    {
        static $xmlEscaper = null;
        if (! $xmlEscaper) {
            $xmlEscaper = new Xml();
        }
        
        return $xmlEscaper->escape($str);
    }

    function updateFile($filename, $path)
    {
        return $this->zipClass->addFromString($filename, file_get_contents($path));
	}

	function duplicateDocumentBody($count = 1) {
        $startPos = strpos($this->tempDocumentMainPart, '<w:body>') + 8;
        $endPos = strpos($this->tempDocumentMainPart, '</w:body>');
        $body = $this->slice($this->tempDocumentMainPart, $startPos, $endPos);
        $bodyContent = $body->get();
        $result = '';
        for($i = 1; $i <= $count; $i++) {
            $result .= $bodyContent;
        }
        $body->set($result);
        $this->tempDocumentMainPart = $body->join();
    }

    public function cloneBlockOnlyFirst($blockname)
    {
        $startPos = strpos($this->tempDocumentMainPart, '${'.$blockname.'}');
        $endPos = strpos($this->tempDocumentMainPart, '${/'.$blockname.'}', $startPos) + 4 + strlen($blockname);
        $startPosContent = strpos($this->tempDocumentMainPart, '${'.$blockname.'}') + 3 + strlen($blockname);
        $endPosContent = strpos($this->tempDocumentMainPart, '${/'.$blockname.'}', $startPosContent);
        $content = substr($this->tempDocumentMainPart, $startPosContent, $endPosContent - $startPosContent);
        $this->tempDocumentMainPart = substr_replace($this->tempDocumentMainPart, $content, $startPos, $endPos - $startPos);
    }


    function replaceBlockOnlyFirst($blockname, $replacement) {
        preg_match(
                '/(<\?xml.*)(<w:p.*>\${' . $blockname . '}<\/w:.*?p>)(.*)(<w:p.*\${\/' . $blockname . '}<\/w:.*?p>)/is',
                $this->tempDocumentMainPart,
                $matches
        );

        if (isset($matches[3])) {
            //$pos1 = strpos($this->tempDocumentMainPart, '${'.$blockname);
            //$pos2 = strpos($this->tempDocumentMainPart, '${/'.$blockname);
            $pos1 = strpos($this->tempDocumentMainPart, $matches[2].$matches[3].$matches[4]);
            $pos2 = strpos($this->tempDocumentMainPart, $matches[4], $pos1);
            if ($pos1 !== false && $pos2 !== false) {
                $this->tempDocumentMainPart = substr_replace($this->tempDocumentMainPart, $replacement, $pos1, strlen($matches[2]) + strlen($matches[3]) + strlen($matches[4]));
                //$this->tempDocumentMainPart = substr_replace($this->tempDocumentMainPart, $replacement, $pos1, $pos2 - $pos1 + strlen($blockname) + 4);
            }
        }
    }

    function replaceMarkerName($blockname, $replacement, $onlyFirst = false) {
        $pos = strpos($this->tempDocumentMainPart, '${'.$blockname.'}');
        if ($pos !== false) {
            if ($onlyFirst) {
                $this->tempDocumentMainPart = substr_replace($this->tempDocumentMainPart, '${'.$replacement.'}', $pos, strlen($blockname) + 3);
                //$count = 1;
                //$this->tempDocumentMainPart = str_replace('${'.$blockname.'}', '${'.$replacement.'}', $this->tempDocumentMainPart, $count);
            } else {
                $this->tempDocumentMainPart = str_replace('${'.$blockname.'}', '${'.$replacement.'}', $this->tempDocumentMainPart);
            }
        }
    }

}

