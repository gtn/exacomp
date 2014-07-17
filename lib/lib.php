<?php
// DB TABLE NAMES
define('DB_SKILLS', 'block_exacompskills');
define('DB_NIVEAUS', 'block_exacompniveaus');
define('DB_TAXONOMIES', 'block_exacomptaxonomies');
define('DB_EXAMPLES', 'block_exacompexamples');
define('DB_DESCRIPTORS', 'block_exacompdescriptors');
define('DB_DESCEXAMP', 'block_exacompdescrexamp_mm');
define('DB_EDULEVELS', 'block_exacompedulevels');
define('DB_SCHOOLTYPES', 'block_exacompschooltypes');
define('DB_SUBJECTS', 'block_exacompsubjects');
define('DB_TOPICS', 'block_exacomptopics');
define('DB_COURSETOPICS', 'block_exacompcoutopi_mm');
define('DB_DESCTOPICS', 'block_exacompdescrtopic_mm');
define('DB_CATEGORIES', 'block_exacompcategories');
define('DB_COMPETENCE_ACTIVITY', 'block_exacompcompactiv_mm');
define('DB_COMPETENCIES', 'block_exacompcompuser');
define('DB_SETTINGS', 'block_exacompsettings');
define('DB_MDLTYPES', 'block_exacompmdltype_mm');

// ROLE CONSTANTS
define('ROLE_TEACHER', 1);
define('ROLE_STUDENT', 0);

// DB COMPETENCE TYPE CONSTANTS
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);

// SETTINGS
define('SETTINGS_MAX_SCHEME', 10);

$version = get_config('exacomp','alternativedatamodel');
define("LIS_SHOW_ALL_TOPICS",99999999);

function block_exacomp_init_js_css(){
	global $PAGE, $CFG;
	$PAGE->requires->css('/blocks/exacomp/styles.css');
	$PAGE->requires->css('/blocks/exacomp/css/jquery-ui.css');
	$PAGE->requires->js('/blocks/exacomp/javascript/jquery.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/jquery-ui.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);

	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css'))
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js'))
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);

}

/**
 * Gets all subjects that are used in a particular course.
 *
 * @param int $courseid
 * @param int $subjectid this parameter is only used to check if a subject is in use in a course
 *
 */
function block_exacomp_get_subjects_by_course($courseid, $subjectid = null) {
	global $DB;

	$sql = 'SELECT s.id, s.title, s.number, "subject" as type
	FROM {'.DB_SUBJECTS.'} s
	JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id ';

	if($courseid>0)
		$sql .= 'JOIN {'.DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ';

	$sql .=	 ($subjectid == null ? '' : '
			-- only show active ones
			WHERE s.id = ?
			').'
			GROUP BY s.id
			ORDER BY s.stid, s.title
			';

	$subjects = $DB->get_records_sql($sql, array($courseid,$subjectid));

	return $subjects;
}

/**
 * Gets all topics, or all topics from a particular subject if given
 *
 * @param int $subjectid
 */
function block_exacomp_get_all_topics($subjectid = null) {
	global $DB;

	$topics = $DB->get_records_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, "topic" as type, t.catid, cat.title as cat
			FROM {'.DB_SUBJECTS.'} s
			JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
			LEFT JOIN {'.DB_CATEGORIES.'} cat ON t.catid = cat.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			ORDER BY t.sorting
			', array($subjectid));

	return $topics;
}

/**
 * Cheks if a competence is associated to any activity in a particular course
 *
 * @param int $compid
 * @param int $comptype
 * @param int $courseid
 * @return boolean
 */
function block_exacomp_check_activity_association($compid, $comptype, $courseid) {
	global $DB;

	$cms = get_course_mods($courseid);
	foreach($cms as $cm) {
		if($DB->record_exists(DB_COMPETENCE_ACTIVITY, array("compid"=>$compid,"comptype"=>$comptype,"activityid"=>$cm->id)))
			return true;
	}

	return false;
}

/**
 * Gets settings for the current course
 * @param int$courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	global $DB, $COURSE;

	if (!$courseid)
		$courseid = $COURSE->id;

	$settings = $DB->get_record(DB_SETTINGS, array("courseid" => $courseid));

	if (empty($settings)) $settings = new stdClass;
	if (empty($settings->grading)) $settings->grading = 1;
	if (!isset($settings->uses_activities)) $settings->uses_activities = get_config("exacomp","alternativedatamodel") ? 0 : 1;
	if (!$settings->uses_activities) $settings->show_all_examples = 1;
	elseif (!isset($settings->show_all_examples)) $settings->show_all_examples = 0;
	if (!$settings->uses_activities) $settings->show_all_descriptors = 1;
	elseif (!isset($settings->show_all_descriptors)) $settings->show_all_descriptors = 0;

	return $settings;
}

function block_exacomp_get_descriptors_by_course($courseid) {
	global $DB;
	$course='';

	$sql = 'SELECT d.id, d.title, t.id AS topicid, "descriptor" as type
	FROM {block_exacompsubjects} s
	JOIN {block_exacomptopics} t ON t.subjid = s.id ';

	if($courseid>0){
		$sql .=	'JOIN {block_exacompcoutopi_mm} topmm ON topmm.topicid=t.id AND topmm.courseid=? ';
		$course = 'AND a.course=? ';
	}

	$sql .=	'JOIN {block_exacompdescrtopic_mm} desctopmm ON desctopmm.topicid=t.id
	JOIN {block_exacompdescriptors} d ON desctopmm.descrid=d.id
	'.(block_exacomp_coursesettings()->show_all_descriptors ? '' : '
			JOIN {block_exacompcompactiv_mm} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.$course);

	$sql .=	' GROUP BY d.id
	ORDER BY d.sorting
	';

	if($courseid>0)
		$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid));
	else
		$descriptors = $DB->get_records_sql($sql);

	return $descriptors;
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree_by_course($courseid, $subjectid = null) {
	global $DB;

	$allSubjects = block_exacomp_get_subjects_by_course($courseid, $subjectid);
	$allTopics = block_exacomp_get_all_topics($subjectid);

	$subjects = array();
	//subjectid is not null iff lis version is used
	if($subjectid != null) {
		foreach ($allTopics as $topic) {
			if(block_exacomp_check_activity_association($topic->id, TYPE_TOPIC, $courseid)) {
				// found: add it to the subject result, even if no descriptor from the topic is used
				$subject = $allSubjects[$topic->subjid];
				$subject->subs[$topic->id] = $topic;
				$subjects[$topic->subjid] = $subject;
			}
		}
	}

	$allDescriptors = block_exacomp_get_descriptors_by_course($courseid);

	foreach ($allDescriptors as $descriptor) {

		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) continue;
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;

		// find all parent topics
		$found = true;
		for ($i = 0; $i < 10; $i++) {
			if ($topic->parentid) {
				// parent is topic, find it
				if (empty($allTopics[$topic->parentid])) {
					$found = false;
					break;
				}

				// found it
				$allTopics[$topic->parentid]->subs[$topic->id] = $topic;

				// go up
				$topic = $allTopics[$topic->parentid];
			} else {
				// parent is subject, find it
				if (empty($allSubjects[$topic->subjid])) {
					$found = false;
					break;
				}

				// found: add it to the subject result
				$subject = $allSubjects[$topic->subjid];
				$subject->subs[$topic->id] = $topic;
				$subjects[$topic->subjid] = $subject;

				// top found
				break;
			}
		}

		// if parent not found (error), skip it
		if (!$found) continue;

		$descriptor->examples = $DB->get_records_sql(
				"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
				e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
				FROM {" . DB_EXAMPLES . "} e
				JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
				LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id
				ORDER BY tax.title", array($descriptor->id));
	}

	return $subjects;
}

function block_exacomp_get_students_by_course($courseid) {
	$context = context_course::instance($courseid);
	return get_role_users(5, $context);
}

function block_exacomp_get_teachers_by_course($courseid) {
	$context = context_course::instance($courseid);
	return get_role_users(array(1,2,3,4), $context);
}

/**
 * Returns all the import information for a particular user in the given course about his competencies, topics and example evaluation values
 *
 * It returns user objects in the following format
 * 		$user
 * 			->competencies
 * 				->teacher[competenceid] = competence value
 * 				->self[competenceid] = competence value
 * 			->topics
 * 				->teacher
 * 				->self
 *
 * @param sdtClass $user
 * @param int $courseid
 * @return stdClass $ser
 */
function block_exacomp_get_user_information_by_course($user, $courseid) {
	// get student competencies
	$user = block_exacomp_get_user_competencies_by_course($user, $courseid);
	// get student topics
	$user = block_exacomp_get_user_topics_by_course($user, $courseid);
	// get student examples

	return $user;
}

/**
 * This method returns all user competencies for a particular user in the given course

 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_competencies_by_course($user, $courseid) {
	global $DB;
	$user->competencies = new stdClass();
	$user->competencies->teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->self = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');

	return $user;
}

/**
 *  This method returns all user topics for a particular user in the given course
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_topics_by_course($user, $courseid) {
	global $DB;

	$user->topics = new stdClass();
	$user->topics->teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->self = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');

	return $user;
}

function block_exacomp_get_user_examples_by_course($user, $courseid) {

}

function block_exacomp_build_navigation_tabs($context,$courseid) {
	global $DB;
	$version = get_config('exacomp', 'alternativedatamodel');
	$courseSettings = block_exacomp_coursesettings();

	if($version)
		$checkConfig = block_exacomp_is_configured($courseid);
	else
		$checkConfig = block_exacomp_is_configured();
		
	$checkImport = $DB->get_records('block_exacompdescriptors');

	$rows = array();

	if (has_capability('block/exacomp:teacher', $context)) {
		if($checkConfig && $checkImport || $version && $checkImport){
				
			$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
			$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/edit_students.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
			$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
			$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));
			$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
			$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
			$settings = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));
				
			$settings->subtree = array();
			$settings->subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"));
				
			if($version){
				$settings->subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings_selection_st','block_exacomp'));
			}

			$settings->subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"));
				
			if (block_exacomp_is_activated($courseid)) {
				if ($courseSettings->uses_activities)
					$settings->subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"));
			}

			if (block_exacomp_moodle_badges_enabled()) {
				$settings->subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"));
			}

			$rows[] = $settings;
		}
		if(has_capability('block/exacomp:admin', $context)){
			$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));
			if(!$version && $checkImport)
				$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
		}

		if($checkConfig && $checkImport || $version && $checkImport)
			$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));

	}elseif (has_capability('block/exacomp:student', $context)) {
		if($checkConfig && $checkImport || $version && $checkImport){
			$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
			$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/edit_students.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
			$rows[] = new tabobject('tab_student_all', new moodle_url('/blocks/exacomp/all_gained_competencies_course_based.php',array("courseid"=>$courseid)),get_string('tab_student_all','block_exacomp'));
			$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
			$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
			$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
			$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
		}
	}

	return $rows;
}

function block_exacomp_studentselector($students,$selected,$url){
	global $CFG;

	$studentsAssociativeArray = array();
	$studentsAssociativeArray[0]=get_string('LA_no_student_selected', "block_exacomp");
	foreach($students as $student) {
		$studentsAssociativeArray[$student->id] = fullname($student);
	}
	return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student',$selected,true,
			array("onchange"=>"document.location.href='".$url."&studentid='+this.value;"));
}

function block_exacomp_check_customupload() {
	$context = context_system::instance();

	foreach (get_user_roles($context) as $role) {
		if($role->shortname == "exacompcustomupload")
			return true;
	}

	return false;
}

function block_exacomp_coursesettings($courseid = 0) {
	global $DB, $COURSE;

	if (!$courseid)
		$courseid = $COURSE->id;

	$rs = $DB->get_record(DB_SETTINGS, array("courseid" => $courseid));

	if (empty($rs)) $rs = new stdClass;
	if (empty($rs->grading)) $rs->grading = 1;
	if (!isset($rs->uses_activities)) $rs->uses_activities = get_config("exacomp","alternativedatamodel") ? 0 : 1;
	if (!$rs->uses_activities) $rs->show_all_examples = 1;
	elseif (!isset($rs->show_all_examples)) $rs->show_all_examples = 0;
	if (!$rs->uses_activities) $rs->show_all_descriptors = 1;
	elseif (!isset($rs->show_all_descriptors)) $rs->show_all_descriptors = 0;

	return $rs;
}
function block_exacomp_get_edulevels() {
	global $DB;
	return $DB->get_records(DB_EDULEVELS,null,'source');
}

function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	return $DB->get_records(DB_SCHOOLTYPES, array("elid" => $edulevel));
}
function block_exacomp_get_mdltypes($typeid, $courseid = 0) {
	global $DB;

	return $DB->get_record(DB_MDLTYPES, array("stid" => $typeid, "courseid" => $courseid));
}
function block_exacomp_set_mdltype($values, $courseid = 0) {
	global $DB;

	$DB->delete_records(DB_MDLTYPES,array("courseid"=>$courseid));
	foreach ($values as $value) {
		$DB->insert_record(DB_MDLTYPES, array("stid" => intval($value),"courseid" => $courseid));
	}
}
/*
 * check if configuration is already finished
* configuration is finished if schooltype is selected for course(LIS)/moodle(normal)
*/
function block_exacomp_is_configured($courseid=0){
	global $DB;

	return $DB->get_record(DB_MDLTYPES, array("courseid"=>$courseid));
}
function block_exacomp_moodle_badges_enabled() {
	global $CFG;

	// since moodle 2.5 it has badges functionality
	return (version_compare($CFG->release, '2.5') >= 0);
}
function block_exacomp_save_coursesettings($courseid, $settings) {
	global $DB;

	$DB->delete_records(DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > SETTINGS_MAX_SCHEME) $settings->grading = SETTINGS_MAX_SCHEME;

	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(DB_SETTINGS, $settings);
}
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(DB_COURSETOPICS, array("courseid" => $courseid));
}
function block_exacomp_get_grading_scheme($courseid) {
	global $DB;
	$settings = block_exacomp_get_settings_by_course($courseid);
	return $settings->grading;
}

function block_exacomp_get_output_fields($topic) {
	global $version;

	if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $topic->title, $matches)) {
		$output_id = $matches[1];
		$output_title = $matches[2];
	} else {
		$output_id = '';
		$output_title = $topic->title;
	}
	if($version && $topic->id == LIS_SHOW_ALL_TOPICS)
		$output_id = $DB->get_field(DB_CATEGORIES, 'title', array("id"=>$topic->cat));

	return array($output_id, $output_title);
}

function block_exacomp_award_badges($courseid, $userid=null) {
	global $DB, $USER;

	// only award if badges are enabled
	if (!block_exacomp_moodle_badges_enabled()) return;

	$users = get_enrolled_users(context_course::instance($courseid));
	if ($userid) {
		if (!isset($users[$userid])) {
			return;
		}

		// only award for this user
		$users = array(
				$userid => $users[$userid]
		);
	}
	$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) continue;

		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {block_exacompdescriptors} d
				JOIN {block_exacompdescbadge_mm} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;

		foreach ($users as $user) {
			if ($badge->is_issued($user->id)) {
				// skip, already issued
				continue;
			}

			$usercompetences = block_exacomp_get_usercompetences($user->id, $role=1, $courseid);
			$allFound = true;
			foreach ($descriptors as $descriptor) {
				if (isset($usercompetences[$descriptor->id])) {
					// found
				} else {
					// missing
					$allFound = false;
					break;
				}
			}

			// some are missing
			if (!$allFound) continue;

			// has all required competencies
			$acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);
			if (process_manual_award($user->id, $USER->id, $acceptedroles[0], $badge->id))  {
				// If badge was successfully awarded, review manual badge criteria.
				$data = new stdClass();
				$data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
				$data->userid = $user->id;
				badges_award_handle_manual_criteria_review($data);
			} else {
				echo 'error';
			}
		}
	}
}
function block_exacomp_get_all_user_badges($userid = null) {
	global $USER;

	if ($userid == null) $userid = $USER->id;

	$records = badges_get_user_badges($userid);

	return $records;
}

function block_exacomp_get_user_badges($courseid, $userid) {
	global $CFG, $DB;

	$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

	$result = (object)array(
			'issued' => array(),
			'pending' => array()
	);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) continue;

		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {block_exacompdescriptors} d
				JOIN {block_exacompdescbadge_mm} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;

		$badge->descriptorStatus = array();

		$usercompetences = block_exacomp_get_usercompetences($userid, $role=1, $courseid);

		foreach ($descriptors as $descriptor) {
			if (isset($usercompetences[$descriptor->id])) {
				$badge->descriptorStatus[] = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" style="vertical-align: text-bottom" />'.$descriptor->title;
			} else {
				$badge->descriptorStatus[] = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" style="vertical-align: text-bottom" />'.$descriptor->title;
			}
		}
			
		if ($badge->is_issued($userid)) {
			$result->issued[$badge->id] = $badge;
		} else {
			$result->pending[$badge->id] = $badge;
		}
	}

	return $result;
}

function block_exacomp_build_example_tree_desc($courseid){
	global $DB;

	$tree = block_exacomp_get_competence_tree_by_course($courseid);

	foreach($tree as $subject){
		$subject_has_examples = false;
		foreach($subject->subs as $topic){
			$topic_has_examples = false;
			foreach($topic->descriptors as $descriptor){
				$records = $DB->get_records('block_exacompdescrexamp_mm', array('descrid'=>$descriptor->id));
				if(!$records)
					unset($topic->descriptors[$descriptor->id]);
				else{
					$subject_has_examples = true;
					$topic_has_examples = true;
					$descriptor->examples = array();
					foreach($records as $record){
						$example = $DB->get_record('block_exacompexamples', array('id'=>$record->exampid));
						$descriptor->examples[$example->id]=$example;
					}
				}
			}
			if(!$topic_has_examples)
				unset($subject->subs[$topic->id]);
			$topic_has_examples = false;
		}
		if(!$subject_has_examples)
			unset($tree[$subject->id]);

		$subject_has_examples = false;
	}

	return $tree;
}

function block_exacomp_build_example_tree_tax($courseid){
	$tree = block_exacomp_build_example_tree_desc($courseid);
	
	$taxonomies = block_exacomp_get_taxonomies($tree);
	
	//append subjects to taxonomies
	foreach($taxonomies as $taxonomy){
		foreach($tree as $subject){
			foreach($subject->subs as $topic){
				foreach($topic->descriptors as $descriptor){
					foreach($descriptor->examples as $example){
						if($taxonomy->id == $example->taxid){
							if(!isset($taxonomy->subjects))
								$taxonomy->subjects = array();
							
							if(!isset($taxonomy->subjects[$subject->id])){
								$taxonomy->subjects[$subject->id] = new stdClass();
								$taxonomy->subjects[$subject->id]->id = $subject->id;
								$taxonomy->subjects[$subject->id]->title = $subject->title;
								$taxonomy->subjects[$subject->id]->number = $subject->number;
								$taxonomy->subjects[$subject->id]->subs = array();
							}
							
							if(!isset($taxonomy->subjects[$subject->id]->subs[$topic->id])){
								$taxonomy->subjects[$subject->id]->subs[$topic->id] = new stdClass();
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->id = $topic->id;
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->title = $topic->title;
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->cat = $topic->cat;
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors = array();
							}
							
							if(!isset($taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id])){
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id] = new stdClass();
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id]->id = $descriptor->id;
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id]->title = $descriptor->title;
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id]->examples = array();
							}
							
							if(!isset($taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id]->examples[$example->id])){
								$taxonomy->subjects[$subject->id]->subs[$topic->id]->descriptors[$descriptor->id]->examples[$example->id] = $example;
							}
								
						}
					}
				}
			}
		}
	}

	return $taxonomies;
}
function block_exacomp_get_taxonomies($tree){
	global $DB;
	
	$taxonomies = array();
	
	foreach($tree as $subject){
		foreach($subject->subs as $topic){
			foreach($topic->descriptors as $descriptor){
				foreach($descriptor->examples as $example){
					if($example->taxid > 0 && !in_array($example->taxid, $taxonomies)){
						$taxonomy = new stdClass();
						$taxonomy->id = $example->taxid;
						$taxonomy->title = $DB->get_record(DB_TAXONOMIES, array('id'=>$example->taxid), $fields='title');
						$taxonomies[$example->taxid]= $taxonomy;
					}
				}
			}
		}
	}
	return $taxonomies;
}