<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_exacomp\externallib;

defined('MOODLE_INTERNAL') || die();

use block_exacomp\descriptor;
use block_exacomp\example;
use block_exacomp\global_config;
use block_exacomp\printer;
use block_exacomp\subject;
use block_exacomp\topic;
use external_function_parameters;
use external_multiple_structure;
use external_value;

class reports extends base {
    public static function dakoraplus_create_report_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'studentids' => new external_multiple_structure(new external_value(PARAM_INT), '', VALUE_DEFAULT, []),
            'topicids' => new external_multiple_structure(new external_value(PARAM_INT)),
            'with_childdescriptors' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, false),
            'only_achieved_competencies' => new external_value(PARAM_BOOL, '', VALUE_DEFAULT, false),
            'time_from' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'time_to' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'output_style' => new external_value(PARAM_TEXT, 'ENUM(list, grid)'),
            'result_type' => new external_value(PARAM_TEXT, 'ENUM(html, pdf)'),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function dakoraplus_create_report(int $courseid, array $studentids, array $topicids, bool $with_childdescriptors, bool $only_achieved_competencies, int $time_from, int $time_to, string $output_style, string $result_type) {
        global $DB, $USER;

        [
            'courseid' => $courseid,
            'studentids' => $studentids,
            'topicids' => $topicids,
            'with_childdescriptors' => $with_childdescriptors,
            'only_achieved_competencies' => $only_achieved_competencies,
            'time_from' => $time_from,
            'time_to' => $time_to,
            'output_style' => $output_style,
            'result_type' => $result_type,
        ] = static::validate_parameters(static::dakoraplus_create_report_parameters(), [
            'courseid' => $courseid,
            'studentids' => $studentids,
            'topicids' => $topicids,
            'with_childdescriptors' => $with_childdescriptors,
            'only_achieved_competencies' => $only_achieved_competencies,
            'time_from' => $time_from,
            'time_to' => $time_to,
            'output_style' => $output_style,
            'result_type' => $result_type,
        ]);

        block_exacomp_require_teacher($courseid);

        if ($result_type == 'pdf') {
            $isPdf = true;
        } elseif ($result_type == 'html') {
            $isPdf = false;
        } else {
            throw new \moodle_exception("unknown result type '$result_type'");
        }

        $students = block_exacomp_get_students_by_course($courseid);
        if ($studentids) {
            $students = array_filter($students, function($student) use ($studentids) {
                return in_array($student->id, $studentids);
            });
        }

        if ($time_from || $time_to) {
            $only_achieved_competencies = true;
        }

        if ($output_style == 'list') {
            return self::dakoraplus_create_report_list($courseid, $students, $topicids, $with_childdescriptors, $only_achieved_competencies, $time_from, $time_to, $isPdf);
        } elseif ($output_style == 'grid') {
            return self::dakoraplus_create_report_grid($courseid, $students, $topicids, $with_childdescriptors, $only_achieved_competencies, $time_from, $time_to, $isPdf);
        } else {
            throw new \moodle_exception("output_style '$output_style' not supported");
        }
    }

    public static function dakoraplus_create_report_returns() {
        return new external_value(PARAM_RAW);
    }

    public static function dakoraplus_create_report_list(int $courseid, array $students, array $topicids, bool $with_childdescriptors, bool $only_achieved_competencies, int $time_from, int $time_to, bool $isPdf) {
        $filter = [
            'type' => 'students',
        ];
        @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
        if ($with_childdescriptors) {
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
        }
        @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['visible'] = true;
        @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active'] = true;


        // if ($isTeacher) {
        // } else {
        //     $students[$USER->id] = $coursestudents[$USER->id];
        // }

        $html = '';

        $has_output = false;

        $i = 0;
        foreach ($students as $student) {
            $i++;
            $studentid = $student->id;

            $subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

            $student = block_exacomp_get_user_information_by_course($student, $courseid);


            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $student, $courseid, $filter, $topicids) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);
                $item_type = $item::TYPE;

                if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                    $item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
                }

                if ($item_type == BLOCK_EXACOMP_TYPE_TOPIC) {
                    if ($topicids) {
                        if (!in_array($item->id, $topicids)) {
                            return false;
                        }
                    }
                }

                $item_scheme = block_exacomp_additional_grading($item_type, $courseid); //this has to be done AFTER specifying the item type of course, otherwise always the scheme of the parent descriptor will be taken

                $item_filter = (array)@$filter[$item_type];

                $item->visible = @$item_filter['visible'];

                if (!@$item_filter['active']) {
                    return false;
                }

                $filter_result = block_exacomp_group_reports_annex_result_filter_rules($item_type, $item_scheme, $filter, $eval);

                //                var_dump($filter_result);
                //                var_dump(@$filter['time']);
                //                die;

                if (!$filter_result) {
                    return false;
                }

                if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher < @$filter['time']['from']) {
                    $item->visible = false;
                }
                if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher > @$filter['time']['to']) {
                    $item->visible = false;
                }

                if ($item instanceof subject) {
                    $evalKey = 'subjects';
                } elseif ($item instanceof topic) {
                    $evalKey = 'topics';
                } elseif ($item instanceof descriptor) {
                    $evalKey = 'competencies';
                } elseif ($item instanceof example) {
                    $evalKey = 'examples';
                } else {
                    // should not happen
                    $evalKey = '';
                }

                $item->teachereval = $student->{$evalKey}->teacher[$item->id];
                $item->studenteval = $student->{$evalKey}->student[$item->id];
                $item->timestamp_teacher = $student->{$evalKey}->timestamp_teacher[$item->id];

                $walk_subs($level + 1);
            });

            ob_start();
            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0)
            use ($studentid, $courseid, $filter, $html, $isPdf, $only_achieved_competencies, $time_from, $time_to) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

                if (!$item->visible) {
                    // walk subs with same level
                    $walk_subs($level);
                    return;
                }

                //item_type is needed to distinguish between topics, parent descripors and child descriptors --> important for css-styling
                $item_type = $item::TYPE;

                $teachereval_smiley = function($id) {
                    if ($id === null) {
                        return;
                    } elseif ($id == 0) {
                        return ':-)';
                    } elseif ($id == 1) {
                        return ':-|';
                    } elseif ($id == 2) {
                        return ':-(';
                    }
                };

                ob_start();

                if ($item_type == BLOCK_EXACOMP_TYPE_SUBJECT) {
                    echo '<tr class="exarep_subject_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_TOPIC) {
                    echo '<tr class="exarep_topic_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level <= 2) {
                    $item_type = BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT; // ITEM_TYPE needs to be child or parent, not just Descriptor for block_exacomp_additional_grading to work
                    echo '<tr class="exarep_descriptor_parent_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level > 2) {
                    $item_type = BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD;
                    echo '<tr class="exarep_descriptor_child_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_EXAMPLE) {
                    echo '<tr class="exarep_example_row">';
                }

                // $item_scheme = block_exacomp_additional_grading($item_type, $courseid);

                // echo '<td class="exarep_descriptor" width="4%" style="white-space: nowrap;">' . $item->get_numbering() . '</td>';
                echo '<td class="exarep_descriptorText" width="70%">';
                echo '<table style="padding: 0 0 0 ' . ($level * 10) . 'px;"><tr>' .
                    '<td style="border: 0px solid white;">' .
                    '<table style="padding: 0"><tr><td style="border: 0px solid white; width: 8px;">&#8226;</td>' .
                    '<td style="border: 0px solid white" width="97%">' .
                    $item->title .
                    '</td></tr></table>' .
                    '</td></tr></table>';

                echo '</td>';

                // if (@$filter['time']['active']) {
                //     echo '<td width="5%" class="timestamp">' . ($eval->timestampteacher ? date('d.m.Y', $eval->timestampteacher) : '') . '</td>';
                //     //$html .= '<td class="timestamp">'.($eval->timestampteacher ? date('d.m.Y', $eval->timestampteacher) : '').'</td>';
                // }
                echo '<td class="exarep_studentAssessment" width="15%" style="text-align: center">' . global_config::get_student_eval_title_by_id($item->studenteval) . '</td>';
                echo '<td class="exarep_teacherAssessment" width="15%" style="text-align: center">' . $teachereval_smiley($item->teachereval) . '</td>';
                //				echo '<td class="exarep_exa_evaluation" width="10%" style="padding: 0 10px;">'.$eval->get_teacher_value_title().'</td>'; // remove? RW
                // echo '<td class="exarep_difficultyLevel" width="10%" style="padding: 0 10px;">' . $eval->get_evalniveau_title() . '</td>';
                echo '</tr>';

                $row_output = ob_get_clean();

                ob_start();
                $walk_subs($level + 1);
                $sub_output = ob_get_clean();;

                $filtered_time = ($time_from && $item->timestamp_teacher < $time_from) || ($time_to && $item->timestamp_teacher > $time_to);
                $filtered_achieved = ($only_achieved_competencies && $item->teachereval === null);
                if (!$sub_output && ($filtered_achieved || $filtered_time)) {
                    // ignore
                } else {
                    echo $row_output;
                    echo $sub_output;
                }
            });
            $output = ob_get_clean();

            if ($output) {
                $has_output = true;

                if ($i != 1) {
                    $html .= '<br pagebreak="true"/>';
                }
                $html .= '<h3>' . fullname($student) . '</h3>';
                $html .= '<table width="100%">';
                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th width="70%"></th>';
                // if (@$filter['time']['active']) {
                //     $html .= '<th width="5%" class="heading"></th>';
                // }
                $html .= '<th width="15%" style="text-align: center;">' . 'Schüler:in' . '</th>';
                $html .= '<th width="15%" style="text-align: center;">' . 'Lehrkraft' . '</th>';
                $html .= '</tr>';
                $html .= '</thead>';
                $html .= "<tbody>";
                $html .= $output;
                $html .= "</tbody>";
                $html .= '</table>';
            }
        }

        if (!$has_output) {
            // 			echo block_exacomp_get_string('no_entries_found');
            $html .= block_exacomp_get_string('no_entries_found');
        }

        if ($isPdf) {
            $pdf = printer::getPdfPrinter('P');

            $pdf->setStyle('
			* {
				font-size: 9pt;
			}
			h3 {
                font-size: 20pt;
            }
			div {
				padding: 0;
				margin: 0;
			}
			table td, table th {
				border: 0.2pt solid #111;
			}
			table {
				padding: 1px 5px 1px 5px; /* tcpdf only accepts padding on table tag, which gets applied to all cells */
			}
			tr.highlight {
				background-color: #e6e6e6;
			}
			th {
			    font-weight: bold;
				background-color: #e6e6e6;
			}
            ');

            $pdf->setHeaderMargin(5);
            $pdf->SetTopMargin(20);

            // $html_content = preg_replace('!<tr(\s|>)!i', '<tr nobr="true"$1', $html);
            $html_content = $html;

            $pdf->writeHTML($html_content);
            $pdf->Output('Bericht.pdf');
            exit;
        } else {
            return $html;
        }
    }

    public static function dakoraplus_create_report_grid(int $courseid, array $students, array $topicids, bool $with_childdescriptors, bool $only_achieved_competencies, int $time_from, int $time_to, bool $isPdf) {

        $subjectid = 0;
        $tree = block_exacomp_get_competence_tree($courseid, $subjectid, null, true, null, true, null, false, false, false, false, false);

        ob_start();
        $output_started = false;
        foreach ($students as $student) {
            $studentid = $student->id;

            $student = block_exacomp_get_user_information_by_course($student, $courseid);

            $print_eval_student = function($item) {
                $studenteval = trim(global_config::get_student_eval_title_by_id($item->studenteval)) ?: '-';
                return $studenteval;
            };

            $print_eval_teacher = function($item) {
                $teachereval_smiley = function($id) {
                    if ($id === null) {
                        return;
                    } elseif ($id == 0) {
                        return ':-)';
                    } elseif ($id == 1) {
                        return ':-|';
                    } elseif ($id == 2) {
                        return ':-(';
                    }
                };

                $teachereval = $teachereval_smiley($item->teachereval) ?: '-';
                return $teachereval;
            };

            $fill_eval = function($item) use ($student) {
                if ($item instanceof subject) {
                    $evalKey = 'subjects';
                } elseif ($item instanceof topic) {
                    $evalKey = 'topics';
                } elseif ($item instanceof descriptor) {
                    $evalKey = 'competencies';
                } elseif ($item instanceof example) {
                    $evalKey = 'examples';
                } else {
                    // should not happen
                    $evalKey = '';
                }

                $item->teachereval = $student->{$evalKey}->teacher[$item->id];
                $item->studenteval = $student->{$evalKey}->student[$item->id];
                $item->timestamp_teacher = $student->{$evalKey}->timestamp_teacher[$item->id];
            };

            $print_item = function($item, $level, $sub_output = '') use ($fill_eval, $print_eval_student, $print_eval_teacher, $student, $time_from, $time_to, $only_achieved_competencies) {
                $fill_eval($item);

                $filtered_time = ($time_from && $item->timestamp_teacher < $time_from) || ($time_to && $item->timestamp_teacher > $time_to);
                $filtered_achieved = ($only_achieved_competencies && $item->teachereval === null);
                if (!$sub_output && ($filtered_achieved || $filtered_time)) {
                    // ignore
                    return;
                }

                ob_start();
                echo '<table style="padding: 3px 0 3px ' . ($level * 10) . 'px"><tr>' .
                    '<td style="border: 0px solid white;">' .
                    '<table style="padding: 0"><tr><td style="border: 0px solid white; width: 8px;">&#8226;</td>' .
                    '<td style="border: 0px solid white" width="77%">' .
                    $item->title .
                    '</td><td style="border: 0px solid white; text-align: center;" width="10%">' .
                    $print_eval_student($item) .
                    '</td><td style="border: 0px solid white; text-align: center;" width="10%">' .
                    $print_eval_teacher($item) .
                    '</td></tr></table>' .
                    '</td></tr></table>';

                echo $sub_output;
                return ob_get_clean();
            };

            foreach ($tree as $subject) {
                // first check if any topics in this subject were selected
                $print_subject = false;
                foreach ($subject->topics as $topic) {
                    if (in_array($topic->id, $topicids)) {
                        $print_subject = true;
                    }
                }

                if (!$print_subject) {
                    continue;
                }

                $used_niveaus = $subject->used_niveaus;
                // kA wieso das niveau ein array ist, sollte aber ein object sein? -> konvertieren
                $used_niveaus = array_map(function($niveau) {
                    return (object)$niveau;
                }, $used_niveaus);

                if ($output_started) {
                    echo '<br pagebreak="true"/>';
                }
                $output_started = true;

                echo '<h3>' . fullname($student) . ' / ' . $subject->title . '</h3>';

                echo '<table>';
                echo '<thead><tr><th></th>';
                foreach ($used_niveaus as $niveau) {
                    echo "<th>{$niveau->title}</th>";
                }
                echo '</tr></thead>';
                echo '<tbody>';

                foreach ($subject->topics as $topic) {
                    if (!in_array($topic->id, $topicids)) {
                        continue;
                    }

                    $fill_eval($topic);

                    echo '<tr nobr="true"><td>';
                    echo '<table style="padding: 0"><tr>' .
                        '<td style="border: 0px solid white" width="77%">' .
                        $topic->title .
                        '</td><td style="border: 0px solid white; text-align: center;" width="10%">' .
                        $print_eval_student($topic) .
                        '</td><td style="border: 0px solid white; text-align: center;" width="10%">' .
                        $print_eval_teacher($topic) .
                        '</td></tr></table>';
                    echo "</td>";

                    foreach ($used_niveaus as $niveau) {
                        echo "<td>";

                        $descriptor_output = '';
                        foreach ($topic->descriptors as $descriptor) {
                            if ($descriptor->niveauid !== $niveau->id) {
                                continue;
                            }

                            if (!block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid, false)) {
                                continue;
                            }

                            $child_descriptor_output = '';

                            if ($with_childdescriptors) {
                                foreach ($descriptor->children as $child) {
                                    $examples_output = '';
                                    foreach ($child->examples as $example) {
                                        $examples_output .= $print_item($example, 2);
                                    }

                                    $child_descriptor_output .= $print_item($child, 1, $examples_output);
                                }
                            }

                            $examples_output = '';
                            foreach ($descriptor->examples as $example) {
                                $examples_output .= $print_item($example, 1);
                            }

                            $descriptor_output .= $print_item($descriptor, 0, $examples_output . $child_descriptor_output);
                        }

                        echo $descriptor_output ?: '-';

                        echo "</td>";
                    }

                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            }
        }
        $html = ob_get_clean();

        if ($isPdf) {
            $pdf = printer::getPdfPrinter('L');

            $pdf->setStyle('
			* {
				font-size: 9pt;
			}
			h3 {
                font-size: 20pt;
            }
			div {
				padding: 0;
				margin: 0;
			}
			table td, table th {
				border: 0.2pt solid #111;
			}
			table {
				padding: 1px 5px 1px 5px; /* tcpdf only accepts padding on table tag, which gets applied to all cells */
			}
			tr.highlight {
				background-color: #e6e6e6;
			}
			th {
			    font-weight: bold;
				background-color: #e6e6e6;
			}
            ');

            $pdf->setHeaderMargin(5);
            $pdf->SetTopMargin(20);

            // $html_content = preg_replace('!<tr(\s|>)!i', '<tr nobr="true"$1', $html);
            $html_content = $html;

            $pdf->writeHTML($html_content);
            $pdf->Output('Bericht.pdf');
            exit;
        } else {
            return $html;
        }
    }
}
