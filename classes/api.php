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

require_once __DIR__.'/../inc.php';

use block_exacomp\globals as g;

class api {
    
    const BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE = 0;
    const BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE = 1;
    const BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE = 2;
    const BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS = 3;
    const BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO = 4;
    
	static function active() {
		// check if block is active
		if (!g::$DB->get_record('block', array('name' => 'exacomp', 'visible' => 1))) {
			return false;
		}

		return true;
	}

	static function get_active_comps_for_exaport_item($itemid) {
		return g::$DB->get_records_sql("
			SELECT DISTINCT d.*
			-- distinct, because a descriptor can be in multiple courseids
			FROM {block_exacompdescriptors} d
			JOIN {block_exacompcompactiv_mm} compactiv ON compactiv.compid = d.id
			WHERE compactiv.eportfolioitem = 1 AND compactiv.activityid = ?
		", [$itemid]);
	}

	static function get_comp_tree_for_exaport($userid) {

		$achieved_descriptors = g::$DB->get_records_menu('block_exacompcompuser', [
			"userid" => g::$USER->id,
			"role" => 1, // teacher
			'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
			// distinct needed here, because different courses can have same competence achieved
		], null, 'DISTINCT compid as id, compid');

		$walker = function($item) use (&$walker, $achieved_descriptors) {
			$subs = $item->get_subs();
			array_walk($subs, $walker);

			if ($item instanceof descriptor) {
				$item->achieved = in_array($item->id, $achieved_descriptors);
			} else {
				$item->achieved = null;
			}
		};

		$tree = db_layer_all_user_courses::create($userid)->get_subjects();

		// map to different datastructure for exastud
		array_walk($tree, $walker);

		return $tree;
	}

	static function delete_user_data($userid) {
		global $DB;

		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCES, array("userid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("userid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_PROFILESETTINGS, array("userid" => $userid));

		$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_DESCVISIBILITY, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("studentid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array("studentid" => $userid));

		$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array("creatorid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLES, array("creatorid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array("creatorid" => $userid));

		$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("teacher_reviewerid" => $userid));

		$DB->delete_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array("trainerid" => $userid));

		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCES, array("reviewerid" => $userid));
		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("reviewerid" => $userid));

		return true;
	}

	static function delete_student_assessment_data_up_until($studentid, $time) {
		global $DB;

		// comp assessment
		$DB->delete_records_select(BLOCK_EXACOMP_DB_COMPETENCES,
			"userid=? AND timestamp<=?", [$studentid, $time]);

		// example teacher assessment
		$DB->delete_records_select(BLOCK_EXACOMP_DB_EXAMPLEEVAL,
			"studentid=? AND timestamp_teacher>0 AND timestamp_teacher<=?", [$studentid, $time]);

		// example self assessment, if timestamp_teacher>0 => der lehrer hat nach dem bildungsstandard noch bewertet
		// => das wollen wir dann nicht löschen
		$DB->delete_records_select(BLOCK_EXACOMP_DB_EXAMPLEEVAL,
			"studentid=? AND timestamp_teacher=0 AND timestamp_student<=?", [$studentid, $time]);

		return true;
	}

	static function get_subjects_with_grade_for_teacher_and_student($teacherid, $studentid) {
		$resultSubjects = [];

		//$courses = block_exacomp_get_teacher_courses($teacherid);
		$courses = block_exacomp_get_courses_of_teacher($teacherid);
		foreach ($courses as $courseid) {
			$subjects = db_layer_course::create($courseid)->get_subjects();
			foreach ($subjects as $subject) {
				$evaluation = block_exacomp_get_comp_eval_merged($courseid, $studentid, $subject);
				if ($evaluation->additionalinfo) {
					$resultSubjects[] = (object)[
						'title' => $subject->title,
						'additionalinfo' => $evaluation->additionalinfo,
						'niveau' => $evaluation->get_evalniveau_title(),
					];
				}
			}
		}

		return $resultSubjects;
	}

	static function get_comp_tree_for_exastud($studentid) {
	    
	    
		$subjects = db_layer_all_user_courses::create($studentid)->get_subjects();

		$niveau_titles = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU, [], '', 'id,title');

		// todo check timestamp for current semester

		// neue logik: weil für eine komepetenz in mehreren kursen eine bewertung abgegeben werden kann, wird hier nur die letzte bewertung ausgelesen.
		$records = g::$DB->get_recordset_sql("
			SELECT * FROM {".BLOCK_EXACOMP_DB_COMPETENCES."}
			WHERE userid=? AND role=? AND comptype IN (?,?)
			ORDER BY timestamp DESC", [$studentid, BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, BLOCK_EXACOMP_TYPE_TOPIC]);

		$niveaus_topics = [];
		$niveaus_competencies = [];
		$teacher_additional_grading_topics = [];
		$teacher_additional_grading_competencies = [];
		$teacher_additional_grading_topics_real = [];
		$teacher_additional_grading_competencies_real = [];
		
		$topic_scheme = block_exacomp_get_assessment_topic_scheme();
		$dicriptor_scheme = block_exacomp_get_assessment_comp_scheme();
		$short = false;
		if ($short) {
		    $options =  array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options_short()));
		}else {
		    $options =  array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options()));
		}

		foreach ($records as $record) {
			if ($record->comptype == BLOCK_EXACOMP_TYPE_TOPIC) {
			    if ($record->evalniveauid !== null && !isset($niveaus_topics[$record->compid])) {
			        $niveaus_topics[$record->compid] = $record->evalniveauid;
			    }
			    switch($topic_scheme){
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
			            if ($record->additionalinfo !== null && !isset($teacher_additional_grading_topics[$record->compid])) {
			                if (get_config('exacomp', 'use_grade_verbose_competenceprofile')) {
			                    if ($record->additionalinfo < 1.5) {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_Verygood');
			                    } else if ($record->additionalinfo < 2.5) {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_good');
			                    } else if ($record->additionalinfo < 3.5) {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_Satisfactory');
			                    } else if ($record->additionalinfo < 4.5) {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_Sufficient');
			                    } else if ($record->additionalinfo < 5.5) {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_Deficient');
			                    } else {
			                        $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('grade_Insufficient');
			                    }
			                } else {
			                    $teacher_additional_grading_topics[$record->compid] = $record->additionalinfo;
			                }
                            $teacher_additional_grading_topics_real[$record->compid] = $record->additionalinfo;
			            }
			            break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
			            if ($record->value !== null && !isset($teacher_additional_grading_topics[$record->compid])) {
			                $teacher_additional_grading_topics[$record->compid] = $options[$record->value];
                            $teacher_additional_grading_topics_real[$record->compid] = $options[$record->value];
			            }
                        break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
			            if ($record->value !== null && !isset($teacher_additional_grading_topics[$record->compid])) {
			                $teacher_additional_grading_topics[$record->compid] = $record->value;
                            $teacher_additional_grading_topics_real[$record->compid] = $record->value;
			            }
                        break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
			            if (!isset($teacher_additional_grading_topics[$record->compid])) {
			                if ($record->value == 0 || $record->value == null){
			                    $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('yes_no_No');
                                $teacher_additional_grading_topics_real[$record->compid] = 0;
			                }
			                if($record->value >= 1){
			                    $teacher_additional_grading_topics[$record->compid] = block_exacomp_get_string('yes_no_Yes');
                                $teacher_additional_grading_topics_real[$record->compid] = 1;
			                }
			            }
                        break;
			    }
			} else {
			    if ($record->evalniveauid !== null && !isset($niveaus_competencies[$record->compid])) {
			        $niveaus_competencies[$record->compid] = $record->evalniveauid;
			    }
			    switch($dicriptor_scheme){
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
			            if ($record->additionalinfo !== null && !isset($teacher_additional_grading_competencies[$record->compid])) {
			                    if (get_config('exacomp', 'use_grade_verbose_competenceprofile')) {
			                        if ($record->additionalinfo < 1.5) {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_Verygood');
			                        } else if($record->additionalinfo < 2.5) {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_good');
			                        } else if($record->additionalinfo < 3.5) {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_Satisfactory');
			                        } else if($record->additionalinfo < 4.5) {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_Sufficient');
			                        } else if($record->additionalinfo < 5.5) {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_Deficient');
			                        } else {
                                        $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('grade_Insufficient');
			                        }
			                    } else {
			                        $teacher_additional_grading_competencies[$record->compid] = $record->additionalinfo;
			                    }
                                $teacher_additional_grading_competencies_real[$record->compid] = $record->additionalinfo;
			            }
                        break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
			            if ($record->value !== null && !isset($teacher_additional_grading_competencies[$record->compid])) {
			                $teacher_additional_grading_competencies[$record->compid] = $options[$record->value];
                            $teacher_additional_grading_competencies_real[$record->compid] = $options[$record->value];
			            }
                        break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
			            if ($record->value !== null && !isset($teacher_additional_grading_competencies[$record->compid])) {
			                $teacher_additional_grading_competencies[$record->compid] = $record->value;
                            $teacher_additional_grading_competencies_real[$record->compid] = $record->value;
			            }
                        break;
			        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
			            if (!isset($teacher_additional_grading_competencies[$record->compid])) {
			                if($record->value == 0 || $record->value == null){
                                $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('yes_no_No');
                                $teacher_additional_grading_competencies_real[$record->compid] = 0;
			                }
			                if($record->value >= 1){
                                $teacher_additional_grading_competencies[$record->compid] = block_exacomp_get_string('yes_no_Yes');
                                $teacher_additional_grading_competencies_real[$record->compid] = 1;
			                }
			            }
                        break;
			    }
			}
		}

		/*
		$niveaus_topics = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("userid" => $studentid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => TYPE_TOPIC), '', 'compid as id, evalniveauid');
		$niveaus_competencies = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("userid" => $studentid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR), '', 'compid as id, evalniveauid');

		$teacher_additional_grading_topics = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES,array("userid" => $studentid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, additionalinfo');
		$teacher_additional_grading_competencies = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES,array("userid" => $studentid, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, additionalinfo');
		*/

		foreach ($subjects as $subject) {
			// echo $subject->title."<br/>\n";
			foreach ($subject->topics as $topic) {
				// echo 'x '.$topic->title.' '.(@$niveaus_topics[$topic->id])."<br/>\n";
				foreach ($topic->descriptors as $descriptor) {
					// echo 'x x '.$descriptor->title.' '.(@$niveaus_competencies[$descriptor->id])."<br/>\n";
					$descriptor->teacher_eval_niveau_text = @$niveau_titles[$niveaus_competencies[$descriptor->id]];
					if (isset($teacher_additional_grading_competencies[$descriptor->id])) {
						// \block_exacomp\global_config::get_teacher_eval_title_by_id
						//$descriptor->teacher_eval_additional_grading = \block_exacomp\global_config::get_additionalinfo_value_mapping($teacher_additional_grading_competencies[$descriptor->id]);
					    $descriptor->teacher_eval_additional_grading = $teacher_additional_grading_competencies[$descriptor->id];
                        //$descriptor->teacher_eval_additional_grading_real = $teacher_additional_grading_competencies_real[$descriptor->id];
					} else {
						$descriptor->teacher_eval_additional_grading = null;
						//$descriptor->teacher_eval_additional_grading_real = null;
					}

					if (!$descriptor->teacher_eval_niveau_text && !$descriptor->teacher_eval_additional_grading) {
						unset($topic->descriptors[$descriptor->id]);
					}
				}

				$topic->teacher_eval_niveau_text = @$niveau_titles[$niveaus_topics[$topic->id]];
				if (isset($teacher_additional_grading_topics[$topic->id])) {
					// \block_exacomp\global_config::get_teacher_eval_title_by_id
					//$topic->teacher_eval_additional_grading = \block_exacomp\global_config::get_additionalinfo_value_mapping($teacher_additional_grading_topics[$topic->id]);
				    $topic->teacher_eval_additional_grading = $teacher_additional_grading_topics[$topic->id];
				    //$topic->teacher_eval_additional_grading_real = $teacher_additional_grading_topics_real[$topic->id];
				} else {
					$topic->teacher_eval_additional_grading = null;
					//$topic->teacher_eval_additional_grading_real = null;
				}

				if (!$topic->descriptors && !$topic->teacher_eval_niveau_text && !$topic->teacher_eval_additional_grading) {
					unset($subject->topics[$topic->id]);
				}
			}

			if (!$subject->topics) {
				unset($subjects[$subject->id]);
			}
		}

		return $subjects;
	}
}
