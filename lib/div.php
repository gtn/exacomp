<?php
function block_exacomp_get_examplelink($sourceid){
	global $DB,$CFG;

	$descriptor = $DB->get_record('block_exacompdescriptors',array("sourceid" => $sourceid));
	if($descriptor) {
		$examples=$DB->get_records('block_exacompdescrexamp_mm', array("descrid" => $descriptor->id));

		$returntext = "";
		foreach($examples as $example) {
			$e = $DB->get_record('block_exacompexamples',array("id"=>$example->exampid));
			if($e->task) {
				$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/pdfneu.gif" height="16" width="16" alt="Aufgabenstellung" />';
				$returntext .= '<a target="_blank" href="' . $e->task . '" onmouseover="Tip(\''.$e->title.'\')" onmouseout="UnTip()">'.$img.'</a> -';
			}
		}
	}
	if (!empty($returntext))
		return substr($returntext,0,-2);
	else
		return "";
}
function create_pulldown_array($items, $selectname, $anzeigefeld, $wert, $ka, $multiple, $optionvalue="id", $query_anhang="", $vergleichsfeld="id", $onchange="", $ka_wert="keine Auswahl", $joint="", $sqlu="") {

	$prefixid = "exacomp";
	if (count($items) > 0) {
		if ($multiple == "multiple") {
			$inhalt = '<select name=\'' . $selectname . '[]\' ' . $multiple . ' ' . $onchange . '>';
		}
		else
			$inhalt='<select name=\'' . $selectname . '\' ' . $multiple . ' ' . $onchange . '>';


		if ($ka)
			$inhalt.='<option value="-1">' . $ka_wert . '</option>';
		$wertvorher = "";
		$arranz = explode(",", $anzeigefeld);
		foreach ($items as $item) {
			$wertf = $item->$anzeigefeld . ' ';

			if ($wertf != $wertvorher) {//keine doppelten werte anzeigen
				$wertvorher = $wertf;
				$optionwert = $item->$optionvalue;

				$inhalt.='<option value="' . $optionwert . '"';
				if ($multiple == "multiple") {


					if (in_array($item->$vergleichsfeld, explode(",", $wert)))
						$inhalt.=' selected="selected"';
				}
				else {
					if ($wert == $item->$vergleichsfeld) {
						' selected="selected"';
					}
				}
				$inhalt.='>';
				$inhalt.=$wertf;

				$inhalt.='</option>';
			}
		}
		$inhalt.='</select>';
		return($inhalt);
	}
}

//create pulldown array
function block_exacomp_get_descriptors_by_course($courseid) {
	global $DB;
	//$possible = block_exacomp_get_possible_descritptors_by_course($courseid);
	$query = "SELECT d.title, d.id FROM {block_exacompdescriptors} d INNER JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid INNER JOIN {course_modules} a ON da.activityid=a.id WHERE a.course = :courseid GROUP BY d.id";
	$query.= " ORDER BY d.sorting";

	$descriptors = $DB->get_records_sql($query, array("courseid" => $courseid));
	if (!$descriptors) {
		$descriptors = array();
	}
	return $descriptors;
}

function block_exacomp_get_descriptors_by_course_ids($courseid) {
	global $DB;
	//$possible = block_exacomp_get_possible_descritptors_by_course($courseid);
	
	//da.id so query is unique
	$query = "select  da.id, d.id AS did FROM {block_exacompdescriptors} d INNER JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid INNER JOIN {course_modules} a ON da.activityid=a.id WHERE a.course = :courseid";
	$descriptorids = $DB->get_records_sql($query, array("courseid" => $courseid));
	
	$commaids= "";
	foreach($descriptorids as $descriptorid)
		$commaids .= $descriptorid->did.",";
	
	if (!$descriptorids) {
		return 0;
	}
	
	//cut of last ","
	return substr($commaids, 0, strlen($commaids)-1);
}


function block_exacomp_get_descritors_list($courseid,$onlywithactivitys=0) {
	global $CFG, $DB;
	$condition = array($courseid); 
	$query = "SELECT d.id,d.title,tp.title as topic, s.title as subject FROM {block_exacompdescriptors} d, {block_exacompcoutopi_mm} c, {block_exacompdescrtopic_mm} t, {block_exacomptopics} tp, {block_exacompsubjects} s
	WHERE d.id=t.descrid AND t.topicid = c.topicid AND t.topicid=tp.id AND tp.subjid = s.id AND c.courseid = ?";
	if ($onlywithactivitys==1){
		$descr=block_exacomp_get_descriptors_by_course_ids($courseid);
		if ($descr=="") $descr=0;
		$query.=" AND d.id IN (".$descr.")";
	}
	$query.= " ORDER BY s.title,tp.title,d.sorting";
	// echo $query;
	$descriptors = $DB->get_records_sql($query, $condition);
	if (!$descriptors) {
		$descriptors = array();
	}
	return $descriptors;
}

function block_exacomp_get_descriptors($activityid) {
	global $DB;
	$query = "SELECT descr.title,descr.id FROM {block_exacompdescriptors} descr INNER JOIN {block_exacompdescractiv_mm} mm  ON descr.id=mm.descrid INNER JOIN {course_modules} l ON l.id=mm.activityid ";
	$query.="WHERE l.id=?";
	$query.=" ORDER BY descr.sorting";
	
	$descriptors = $DB->get_records_sql($query, array($activityid));
	
	if (!$descriptors) {
		$descriptors = array();
	}
	return $descriptors;
}

function block_exacomp_get_activityid($activity) {
	global $DB;
	$query = "SELECT distinct cm.id FROM {course_modules} cm, {assignment} a, {modules} m WHERE cm.module = m.id AND m.name = 'assignment' AND cm.instance = ?";
	$id = $DB->get_record_sql($query, array($activity->id));
	return $id;
}

function block_exacomp_get_activities($descid, $courseid = null) { //alle assignments die einem bestimmten descriptor zugeordnet sind
	global $CFG, $DB;
	$query = "SELECT a.id,ass.grade, mm.activitytype FROM {block_exacompdescriptors} descr INNER JOIN {block_exacompdescractiv_mm} mm  ON descr.id=mm.descrid INNER JOIN {course_modules} a ON a.id=mm.activityid LEFT JOIN {assignment} ass ON ass.id=a.instance  ";
	$query.="WHERE descr.id=?";
	//echo $query;
	$condition = array($descid);
	if ($courseid){
		$query.=" AND a.course=?";
		$condition = array($descid, $courseid);
	}

	$activities = $DB->get_records_sql($query, $condition);
	if (!$activities) {
		$activities = array();
	}
	return $activities;
}

function block_exacomp_get_submissions($activityid) {
	global $DB;

	$submissions = $DB->get_records('assignment_submissions', array("assignment" => $activityid));
	if (!$submissions) {
		$submissions = array();
	}
	return $submissions;
}

function block_exacomp_get_competences($descriptorid, $courseid, $role = 1) {
	global $DB;
	$query = 'SELECT c.id,c.activityid, c.descid, c.userid, c.wert FROM {block_exacompdescuser_mm} c, {course_modules} a WHERE c.activityid = a.id AND a.course = ? AND c.descid = ? AND c.role = ?';

	$competences = $DB->get_records_sql($query, array($courseid, $descriptorid, $role));
	if (!$competences) {
		$competences = array();
	}
	return $competences;
}

function block_exacomp_get_genericcompetences($descriptorid, $courseid, $role = 1,$grading=1) {
	global $DB;
	$gut=ceil($grading/2);
	$query = "SELECT * FROM {block_exacompdescuser} WHERE descid=? AND courseid=? AND role=? AND wert<=?";
	$users = $DB->get_records_sql($query, array($descriptorid, $courseid, $role, $gut));
	return $users;
}

function block_exacomp_get_competences_by_descriptor($descriptorid, $courseid, $role) {
	global $DB;
	$query = 'SELECT c.id, c.descid, c.userid,c.wert, u.lastname, u.firstname FROM {block_exacompdescuser} c, {user} u WHERE c.descid =? AND c.courseid =? AND c.role =? AND c.reviewerid=u.id';

	$competences = $DB->get_records_sql($query, array($descriptorid, $courseid, $role));
	if (!$competences) {
		$competences = array();
	}
	return $competences;
}

function block_exacomp_get_assignments($courseid) { //alle assignments eines bestimmten kurses
	global $CFG, $DB;

	$assignments = $DB->get_records('course_modules', array("course" => $courseid));

	//Checken ob Aktivitätene auch Kompetenzen zugeordnet sind
	$returnassignments = array();
	foreach($assignments as $assignment) {
		$check = block_exacomp_get_descriptors($assignment->id,$courseid);
		if($check)
			$returnassignments[] = $assignment;
	}
	return $returnassignments;
}
function block_exacomp_get_activityurl($activity) {
	global $DB, $CFG;
	$mod = $DB->get_record('modules',array("id"=>$activity->module));
	if($mod->name == "assignment")
		return $CFG->wwwroot . '/mod/assignment/submissions.php?id=' . ($activity->id);
	else if($mod->name == "assign")
		return $CFG->wwwroot . '/mod/assign/view.php?id=' . $activity->id;
}
function print_descriptors($descriptors, $classprefix="ec") {
	foreach ($descriptors as $descriptor) {
		$content.='<p class="' . $classprefix . '_descriptor">' . $descriptor->title . '</p>';
	}
}

function get_descriptor_ids($activityid) {
	global $CFG;
	$query = "select GROUP_CONCAT(cast(descrid as char))  as descrids from " . $CFG->prefix . "block_exacompdescractiv_mm WHERE activityid=?";

	$str = get_record_sql($query, array(intval($activityid)));
	return $str->descrids;
}

function get_activity($activityid) {

	$DB->get_record('activity', array("id" => $activityid));
	if (!$activity) {
		$activity = array();
	}

	return $activity;
}

function block_exacomp_set_descractivitymm($descrlist, $activityid) {
	global $DB, $COURSE;
	$DB->delete_records('block_exacompdescractiv_mm', array("activityid" => $activityid));

	$descrarray = explode(",", $descrlist);
	$cmmod = $DB->get_record('course_modules',array("id"=>$activityid));
	$modulename = $DB->get_record('modules',array("id"=>$cmmod->module));
	$instance = get_coursemodule_from_id($modulename->name, $activityid);
	foreach ($descrarray as $descid) {
		$did = intval($descid);
		if ($did > 0) {
			$DB->insert_record('block_exacompdescractiv_mm', array("activityid" => $activityid, "descrid" => $did, "activitytype"=>$cmmod->module,"activitytitle"=>$instance->name,"coursetitle"=>$COURSE->shortname));
		}
	}
}

function block_exacomp_set_descusermm($values, $courseid, $reviewerid, $role) {
	global $DB, $CFG;
	if(strcmp("pgsql", $CFG->dbtype)==0) $query= 'DELETE FROM {block_exacompdescuser_mm} c USING {course_modules} a WHERE c.activityid=a.id AND a.course=? AND c.role = ?';
	else $query= 'DELETE c.* FROM {block_exacompdescuser_mm} c INNER JOIN {course_modules} a ON c.activityid=a.id WHERE a.course=? AND c.role =? ';
	$DB->Execute($query, array($courseid, $role));

	foreach ($values as $value) {
		$data = array(
				"activityid" => $value['activity'],
				"descid" => $value['desc'],
				"userid" => $value['user'],
				"wert" => $value['wert'],
				"reviewerid" => $reviewerid,
				"role" => $role
		);
		$DB->insert_record('block_exacompdescuser_mm', $data);
	}
}

function block_exacomp_set_descuser($values, $courseid, $reviewerid, $role) {
	global $DB;

	$DB->delete_records('block_exacompdescuser', array("courseid" => $courseid, "role" => $role));
	//print_r($values);
	foreach ($values as $value) {
		$data = array(
				"descid" => $value['desc'],
				"userid" => $value['user'],
				"reviewerid" => $reviewerid,
				"wert" => $value['wert'],
				"role" => $role,
				"courseid" => $courseid
		);
		$DB->insert_record('block_exacompdescuser', $data);
	}
}

function block_exacomp_isactivated($courseid) {
	global $DB;

	$topics = $DB->get_records('block_exacompcoutopi_mm', array("courseid" => $courseid));
	if (!empty($topics))
		return true;
	else
		return false;
}

function block_exacomp_print_header($role, $item_identifier, $sub_item_identifier = null) {
	if (!is_string($item_identifier)) {
		echo 'noch nicht unterstützt';
	}

	global $CFG, $COURSE;

	if ($role == 'admin') {
		$strbookmarks = get_string($item_identifier, "block_exacomp");
		$adminbookmarks = get_string('adminbread', "block_exacomp");

		// navigationspfad
		$navlinks = array();
		$navlinks[] = array('name' => $adminbookmarks, 'link' => "edit_config.php?courseid=" . $COURSE->id, 'type' => 'title');
		$nav_item_identifier = $item_identifier;

		$icon = $item_identifier;
		$currenttab = $item_identifier;

		// haupttabs
		$tabs = array();
		$tabs[] = new tabobject('admintabschooltype', $CFG->wwwroot . '/blocks/exacomp/edit_config.php?courseid=' . $COURSE->id, get_string("admintabschooltype", "block_exacomp"), '', true);
		$tabs[] = new tabobject('admintabimport', $CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $COURSE->id, get_string("admintabimport", "block_exacomp"), '', true);
		// tabs fuer das untermenue
		$tabs_sub = array();
		// ausgewaehlte tabs fuer untermenues
		$activetabsubs = Array();

		$item_name = get_string($nav_item_identifier, "block_exacomp");
		if ($item_name[0] == '[')
			$item_name = get_string($nav_item_identifier);
		$navlinks[] = array('name' => $item_name, 'link' => null, 'type' => 'misc');

		$navigation = build_navigation($navlinks);
		print_header_simple($item_name, $COURSE->fullname, $navigation, "", "", true);
		echo '<div id="exabis_competences_block">';
		print_tabs(array($tabs, $tabs_sub), $currenttab, null, $activetabsubs);
	}
	else if ($role == 'teacher') {
		$strbookmarks = get_string($item_identifier, "block_exacomp");
		$adminbookmarks = get_string('teacherbread', "block_exacomp");

		// navigationspfad
		$navlinks = array();
		$navlinks[] = array('name' => $adminbookmarks, 'link' => "edit_course.php?courseid=" . $COURSE->id, 'type' => 'title');
		$nav_item_identifier = $item_identifier;

		$icon = $item_identifier;
		$currenttab = $item_identifier;

		// haupttabs
		$tabs = array();
		$tabs[] = new tabobject('teachertabconfig', $CFG->wwwroot . '/blocks/exacomp/edit_course.php?courseid=' . $COURSE->id, get_string("teachertabconfig", "block_exacomp"), '', true);

		// Wenn der Kurs bereits aktiviert ist, alle Tabs anzeigen
		if (block_exacomp_isactivated($COURSE->id)) {
			$tabs[] = new tabobject('teachertabassignactivities', $CFG->wwwroot . '/blocks/exacomp/edit_activities.php?courseid=' . $COURSE->id, get_string("teachertabassignactivities", "block_exacomp"), '', true);
			$tabs[] = new tabobject('teachertabassigncompetences', $CFG->wwwroot . '/blocks/exacomp/assign_competences.php?courseid=' . $COURSE->id, get_string("teachertabassigncompetences", "block_exacomp"), '', true);
			$tabs[] = new tabobject('teachertabassigncompetencesdetail', $CFG->wwwroot . '/blocks/exacomp/edit_students.php?courseid=' . $COURSE->id, get_string("teachertabassigncompetencesdetail", "block_exacomp"), '', true);
			$tabs[] = new tabobject('teachertabassigncompetenceexamples', $CFG->wwwroot . '/blocks/exacomp/view_examples.php?courseid=' . $COURSE->id, get_string("teachertabassigncompetenceexamples", "block_exacomp"), '', true);
		}

		$tabs_sub = array();

		$activetabsubs = Array();

		if (strpos($item_identifier, 'bookmarks') === 0) {
			$activetabsubs[] = $item_identifier;
			$currenttab = 'bookmarks';

			// untermenue tabs hinzufuegen
			$tabs_sub['bookmarksall'] = new tabobject('bookmarksall', s($CFG->wwwroot . '/blocks/exaport/view_items.php?courseid=' . $COURSE->id),
					get_string("bookmarksall", "block_exaport"), '', true);
			$tabs_sub['bookmarkslinks'] = new tabobject('bookmarkslinks', s($CFG->wwwroot . '/blocks/exaport/view_items.php?courseid=' . $COURSE->id . '&type=link'),
					get_string("bookmarkslinks", "block_exaport"), '', true);
			$tabs_sub['bookmarksfiles'] = new tabobject('bookmarksfiles', s($CFG->wwwroot . '/blocks/exaport/view_items.php?courseid=' . $COURSE->id . '&type=file'),
					get_string("bookmarksfiles", "block_exaport"), '', true);
			$tabs_sub['bookmarksnotes'] = new tabobject('bookmarksnotes', s($CFG->wwwroot . '/blocks/exaport/view_items.php?courseid=' . $COURSE->id . '&type=note'),
					get_string("bookmarksnotes", "block_exaport"), '', true);

			if ($sub_item_identifier) {
				$navlinks[] = array('name' => get_string($item_identifier, "block_exaport"), 'link' => $tabs_sub[$item_identifier]->link, 'type' => 'misc');

				$nav_item_identifier = $sub_item_identifier;
			}
		}
		$item_name = get_string($nav_item_identifier, "block_exacomp");
		if ($item_name[0] == '[')
			$item_name = get_string($nav_item_identifier);
		$navlinks[] = array('name' => $item_name, 'link' => null, 'type' => 'misc');

		$navigation = build_navigation($navlinks);

		print_header_simple($item_name, $COURSE->fullname, $navigation, "", "", true);
		echo '<div id="exabis_competences_block">';
		print_tabs(array($tabs, $tabs_sub), $currenttab, null, $activetabsubs);
	}
	else if ($role == 'student') {
		$strbookmarks = get_string($item_identifier, "block_exacomp");
		$adminbookmarks = get_string('studentbread', "block_exacomp");

		// navigationspfad
		$navlinks = array();
		$navlinks[] = array('name' => $adminbookmarks, 'link' => "assign_competences.php?courseid=" . $COURSE->id, 'type' => 'title');

		$nav_item_identifier = $item_identifier;

		$icon = $item_identifier;
		$currenttab = $item_identifier;

		// haupttabs
		$tabs = array();
		$tabs[] = new tabobject('studenttabcompetences', $CFG->wwwroot . '/blocks/exacomp/assign_competences.php?courseid=' . $COURSE->id, get_string("studenttabcompetences", "block_exacomp"), '', true);
		$tabs[] = new tabobject('studenttabcompetencesdetail', $CFG->wwwroot . '/blocks/exacomp/evaluate_competences.php?courseid=' . $COURSE->id, get_string("studenttabcompetencesdetail", "block_exacomp"), '', true);
		$tabs[] = new tabobject('studenttabcompetencesoverview', $CFG->wwwroot . '/blocks/exacomp/view_competences.php?courseid=' . $COURSE->id, get_string("studenttabcompetencesoverview", "block_exacomp"), '', true);
		// tabs fuer das untermenue
		$tabs_sub = array();
		// ausgewaehlte tabs fuer untermenues
		$activetabsubs = Array();

		$item_name = get_string($nav_item_identifier, "block_exacomp");
		if ($item_name[0] == '[')
			$item_name = get_string($nav_item_identifier);
		$navlinks[] = array('name' => $item_name, 'link' => null, 'type' => 'misc');

		$navigation = build_navigation($navlinks);
		print_header_simple($item_name, $COURSE->fullname, $navigation, "", "", true);
		echo '<div id="exabis_competences_block">';
		print_tabs(array($tabs, $tabs_sub), $currenttab, null, $activetabsubs);
	}
}

function block_exacomp_get_edulevels() {
	global $DB;
	$levels = $DB->get_records('block_exacompedulevels');
	return $levels;
}

function block_exacomp_get_usercompetences($userid, $role=1, $courseid=null,$grading=1,$anzeige=0) {
	global $DB;
	$gut=ceil($grading/2);
	$descriptors = array();

	if($courseid)
		$descriptorids = $DB->get_records('block_exacompdescuser',array("userid"=>$userid, "role"=>$role, "courseid"=>$courseid));
	else
		$descriptorids = $DB->get_records('block_exacompdescuser',array("userid"=>$userid, "role"=>$role));
	if($anzeige){

	}
	foreach($descriptorids as $descriptorid) {
		 
		if ($descriptorid->wert <= $gut){
			$descriptor = $DB->get_record('block_exacompdescriptors',array("id"=>$descriptorid->descid));
			$descriptors[] = $descriptor;
		}
	}

	return $descriptors;
}
function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	$types = $DB->get_records('block_exacompschooltypes', array("elid" => $edulevel));
	return $types;
}

function block_exacomp_get_moodletypes($typeid) {
	global $DB;

	$res = $DB->get_record('block_exacompmdltype_mm', array("typeid" => $typeid));
	if ($res)
		return true;
	else
		return false;
}

function block_exacomp_set_mdltype($values) {
	global $DB;

	$DB->delete_records('block_exacompmdltype_mm');
	foreach ($values as $value) {
		$DB->insert_record('block_exacompmdltype_mm', array("typeid" => $value));
	}
}

function block_exacomp_set_coursetopics($courseid, $values) {
	global $DB;
	$DB->delete_records('block_exacompcoutopi_mm', array("courseid" => $courseid));
	if(isset($values)){
		foreach ($values as $value) {
			$DB->insert_record('block_exacompcoutopi_mm', array("courseid" => $courseid, "topicid" => $value));
		}
	}
}
function block_exacomp_set_bewertungsschema($courseid, $wert) {
	global $DB;
	$DB->delete_records('block_exacompsettings', array("course" => $courseid));
	if($wert>0){
		$DB->insert_record('block_exacompsettings', array("course" => $courseid, "grading" => $wert));
	}
}
function block_exacomp_getbewertungsschema ($courseid,$leerwert=1){
	global $DB;
	$rs = $DB->get_record('block_exacompsettings', array("course" => $courseid));
	if (!empty($rs)) return $rs->grading;
	else return $leerwert;
}

function block_exacomp_reset_coursetopics($courseid) {
	global $DB;

	block_exacomp_set_coursetopics($courseid, null);
}
function block_exacomp_get_subjects() {
	global $DB;
	$query = 'SELECT s.id, s.title FROM {block_exacompsubjects} s WHERE s.stid IN (SELECT t.typeid FROM {block_exacompmdltype_mm} t)';
	//echo $query;
	$subjects = $DB->get_records_sql($query);

	return $subjects;
}
function block_exacomp_get_possible_descritptors_by_course($courseid) {
	global $DB;
	$query = 'SELECT dt.descrid FROM {block_exacompdescrtopic_mm} dt, {block_exacompcoutopi_mm} ct WHERE dt.topicid = ct.topicid AND ct.courseid = ?';
	$possible = $DB->get_records_sql($query, array($courseid));
	$comma_separated = implode(",",$possible);

	return $comma_separated;
}
function block_exacomp_get_subjects_by_id($subids) {
	global $DB;
	$comma_separated = implode(",", $subids);
	$query = 'SELECT s.id, s.title FROM {block_exacompsubjects} s WHERE s.id IN (' . $comma_separated . ')';
	$subjects = $DB->get_records_sql($query);

	return $subjects;
}

function block_exacomp_get_topics($subjectid) {
	global $DB;
	$query = "SELECT * FROM {block_exacomptopics} WHERE subjid=?";
	$query.= " ORDER BY sorting";
	$topics = $DB->get_records_sql($query, array($subjectid));

	//$topics = $DB->get_records('block_exacomptopics', array("subjid" => $subjectid));
	return $topics;
}

function block_exacomp_check_topic_by_course($topicid, $courseid) {
	global $DB;
	$topics = $DB->get_records('block_exacompcoutopi_mm', array("courseid" => $courseid, "topicid" => $topicid));
	return $topics;
}

function block_exacomp_check_subject_by_course($subjectid, $courseid) {
	global $DB;
	$query = '  SELECT ct.topicid FROM {block_exacompcoutopi_mm} ct
	INNER JOIN {block_exacomptopics} t ON ct.topicid = t.id
	INNER JOIN {block_exacompsubjects} s ON t.subjid = s.id
	WHERE ct.courseid = ? AND s.id = ?';
	$subjects = $DB->get_records_sql($query, array($courseid, $subjectid));

	return $subjects;
}

function block_exacomp_get_activity_icon($descriptorid) {
	global $DB, $CFG, $COURSE;
	$count = false;
	$img = '<img src="' . $CFG->wwwroot . '/pix/t/adddir.png" height="16" width="16" alt="'.get_string("assigned_acitivities", "block_exacomp").'" />';

	$activities = $DB->get_records('block_exacompdescractiv_mm', array("descrid" => $descriptorid));
	$temp = get_string("descriptor_task", "block_exacomp").":<br><ul>";

	foreach ($activities as $activity) {
		$mod = $DB->get_record('modules',array("id"=>$activity->activitytype));
		$desc = get_coursemodule_from_id($mod->name, $activity->activityid);
		if(!$desc OR $desc->course != $COURSE->id)
			continue;

		$temp .= "<li>" . $desc->name . "</li>";

		$count = true;
	}
	$temp .= "</ul>";
	$text = $temp;

	if (!$count) {
		$text = get_string('descnoassignment', "block_exacomp");
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/application_delete.png" height="16" width="16" alt="'.get_string("no_assigned_acitivities", "block_exacomp").'" />';
	}
	$icon = new stdClass();
	$text = str_replace("\"","",$text);
	$text = str_replace("\'","",$text);
	$icon->text = $text;
	$icon->icon = $img;
	return $icon;
}

function block_exacomp_get_student_icon($activities, $student) {
	global $DB, $CFG;
	$count = false;
	$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/attach_2.png" height="16" width="16" alt="'.get_string("assigned_acitivities", "block_exacomp").'" />';
	$submitted = $student->firstname . get_string('usersubmitted', "block_exacomp") . "<br><ul>";

	foreach ($activities as $activity) {

		$mod = $DB->get_record('modules',array("id"=>$activity->activitytype));
		$act = get_coursemodule_from_id($mod->name, $activity->id);
		if(!$act)
			continue;

		//$submission = $DB->get_record('assignment_submissions', array("userid" => $student->id, "assignment" => $act->instance));
		$query = ($mod->name === "assign") ? "SELECT s.*, a.grade as agrade FROM {assign_submission} s INNER JOIN {assign} a ON s.assignment=a.id WHERE a.id=? AND s.userid=?" : "SELECT s.*, a.grade as agrade FROM {assignment_submissions} s INNER JOIN {assignment} a ON s.assignment=a.id WHERE a.id=? AND s.userid=?";
		$rs = $DB->get_records_sql($query, array($act->instance, $student->id));
		foreach($rs as $submission){//hoechstens 1 durchlauf moeglich
			if ($submission) {
				$submitted .= "<li>" . $act->name;
				if ($mod->name == "assignment" && $submission->grade>=0){
					$submitted .= " Bewertung: (" . $submission->grade . "/".$submission->agrade.")";
				} else if($mod->name == "assign") {
					$grade = $DB->get_record("assign_grades", array("assignment"=>$act->instance,"userid"=>$student->id));
					if($grade)
						$submitted .= " Bewertung: (" . round($grade->grade,2) . "/".$submission->agrade.")";
				}
				$submitted .= "</li>";
				$count = true;
			}
		}
	}
	$submitted .= "</ul>";
	if (!$count) {
		$submitted = $student->firstname . get_string('usernosubmission', "block_exacomp");
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" height="16" width="23" alt="'.get_string("assigned_acitivities", "block_exacomp").'" />';
	}

	$icon = new stdClass();
	$submitted = str_replace("\"","",$submitted);
	$submitted = str_replace("\'","",$submitted);
	$icon->text = $submitted;
	$icon->icon = $img;
	return $icon;
}
function block_exacomp_exaportexists()
{
	global $DB;
	$all_tables = $DB->get_tables();
	if (in_array("block_exaportview", $all_tables)) {
		return true;
	}else{
		return false;
	}
}
function block_exacomp_get_portfolio_icon($student, $descrid) {
	global $DB, $CFG;

	$rs = $DB->get_records_sql("SELECT i.name FROM {block_exaportitem} i, {block_exacompdescractiv_mm} da WHERE i.id=da.activityid AND da.activitytype=2000 AND da.descrid=? AND i.userid=?", array($descrid, $student->id));

	if(!$rs)
		return null;
	$submitted = $student->firstname . get_string('portfolio', "block_exacomp") . "<br><ul>";
	foreach($rs as $item) {
		$submitted .= "<li>".$item->name."</li>";
	}
	$submitted .= "</ul>";
	$submitted = str_replace("\"","",$submitted);
	$submitted = str_replace("\'","",$submitted);
	$icon = new stdClass();
	$icon->text = $submitted;
	$icon->icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/myportfolio.png" height="16" width="23" alt="'.get_string("assigned_acitivities", "block_exacomp").'" />';
	return $icon;
}
function block_exacomp_get_examples($courseid) {
	global $DB;
	$descriptors = block_exacomp_get_descritors_list($courseid);

	$data = array();
	foreach($descriptors as $descriptor) {
		$value = new stdClass();
		$value = $descriptor;

		$examples = array();
		$zuordnungen = $DB->get_records('block_exacompdescrexamp_mm',array("descrid"=>$descriptor->id));
		foreach($zuordnungen as $zuordnung) {
			$examples[] = $DB->get_record('block_exacompexamples',array("id"=>$zuordnung->exampid));
		}
		if($examples)
			$value->examples = $examples;

		$data[] = $value;
	}
	return $data;

}
function block_exacomp_get_ladebalken($courseid, $userid, $gesamt,$anteil=null,$grading=1) {
	global $DB;

	if(!$anteil) {
		$usercomp = block_exacomp_get_usercompetences($userid, 1, $courseid,$grading);
		$anteil = count($usercomp);
	}
	if ($gesamt==0) $percent=0;
	else $percent = round($anteil / $gesamt * 100,0);
	return "<div class='ladebalken' style=\"background:url('pix/balkenleer.png') no-repeat left center;\"><div style=\"background:url('pix/balkenfull.png') no-repeat left center; height:27px; width:".$percent."%;\"></div></div>";
}

function block_exacomp_get_descriptors_of_all_courses() {
	//kurse holen

	$courses = enrol_get_my_courses();
	$descs = array();
	foreach($courses as $course) {
		$current = $course;
		$current->descriptors = block_exacomp_get_descritors_list($course->id,1);  //alle desciptoren
		//$current->descriptors = block_exacomp_get_descriptors_by_course ($course->id); // nur descriptoren mit zugeordneten aufgaben
		$descs[] = $current;
	}
	return $descs;
}

function block_exacomp_build_comp_tree($courseid, $sort="tax") {
	global $DB;
	//to do: left
	//$sql = "SELECT e.id, d.title, t.title as topic, s.title as subject, e.title as example, tax.title as tax, e.task, e.externalurl, e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement FROM {block_exacompdescriptors} d, {block_exacomptopics} t, {block_exacompsubjects} s, {block_exacompdescrtopic_mm} dt, {block_exacompcoutopi_mm} ct, {block_exacompexamples} e LEFT JOIN {block_exacomptaxonomies} tax ON e.taxid=tax.id, {block_exacompdescrexamp_mm} de WHERE ct.courseid = ".$courseid." AND ct.topicid = t.id AND t.subjid = s.id AND d.id=dt.descrid AND dt.topicid=t.id AND de.descrid=d.id AND de.exampid=e.id GROUP BY e.id";
	$sql = "SELECT e.id, d.title, t.title as topic, s.title as subject, e.title as example, tax.title as tax, e.task, e.externalurl,
	e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement
	FROM {block_exacompexamples} e INNER JOIN {block_exacompdescrexamp_mm} de ON e.id=de.exampid
	INNER JOIN {block_exacompdescriptors} d ON d.id=de.descrid
	INNER JOIN {block_exacompdescrtopic_mm} dt ON d.id=dt.descrid
	INNER JOIN {block_exacomptopics} t ON dt.topicid=t.id ";
	if($courseid > 0){
		$sql.="INNER JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id ";
	}
	$sql.="INNER JOIN {block_exacompsubjects} s ON s.id=t.subjid
	LEFT JOIN {block_exacomptaxonomies} tax ON e.taxid=tax.id";
	$condition = null;
	if($courseid > 0){
		$sql.=" WHERE ct.courseid = ?";
		$condition = array($courseid);
	}
	//$sql.=" GROUP BY e.id";

	if($sort=="desc")
		$sql.=" ORDER BY s.title,t.title,d.sorting";
	else
		$sql.=" ORDER BY tax.title, s.title, t.title, d.sorting";

	/*	if($courseid == 0){
	 $sql = "SELECT e.id, d.title, t.title as topic, s.title as subject, e.title as example, e.task, e.externalurl, e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, ta.title as taxonomie FROM {block_exacompdescriptors} d, {block_exacomptopics} t, {block_exacompsubjects} s, {block_exacompdescrtopic_mm} dt, {block_exacompexamples} e, {block_exacompdescrexamp_mm} de, {block_exacomptaxonomies} ta WHERE t.subjid = s.id AND s.sourceid>9 AND s.sourceid<12 AND d.id=dt.descrid AND dt.topicid=t.id AND de.descrid=d.id AND de.exampid=e.id AND e.taxid = ta.id GROUP BY e.id";
	$sql.=" ORDER BY s.title,t.title,d.sorting";
	}*/

	$examples = $DB->get_records_sql($sql, $condition);

	$tree='<form name="treeform"><ul id="comptree" class="treeview">';
	$subject="";
	$topic="";
	$descriptor="";
	$tax="";
	$newtax=true;
	$newsub=true;
	$newtop=true;
	$newdesc=true;
	$index=0;

	if($sort == "desc") {
		foreach($examples as $example) {
			if($example->subject != $subject) {
				$subject = $example->subject;
				if(!$newsub)$tree.='</ul></li></ul></li></ul></li>';
				$tree.='<li>'.$subject;
				$tree.='<ul>';

				$newsub=false;
				$newtop=true;
			}
			if($example->topic != $topic) {
				$topic = $example->topic;
				if(!$newtop) $tree.='</ul></li></ul></li>';
				$tree.='<li>'.$topic;
				$tree.='<ul>';
				$newtop=false;
				$newdesc=true;
			}
			if($example->title != $descriptor) {
				$descriptor = $example->title;
				if(!$newdesc) $tree.='</ul></li>';
				$tree.='<li>'.$descriptor;
				$tree.='<ul>';
				$newdesc=false;
			}
			$text=$example->description;
			$text = str_replace("\"","",$text);
			$text = str_replace("\'","",$text);
			$text = str_replace("\n"," ",$text);
			$text = str_replace("\r"," ",$text);
			$text = str_replace(":","\:",$text);
			if($text)
				$tree.='<li><a onmouseover="Tip(\'' . $text . '\')" onmouseout="UnTip()">'.$example->example.'</a>';
			else
				$tree.='<li>'.$example->example;
			$tree.=block_exacomp_get_exampleicon($example);
			$tree.='</li>';
			$index++;
		}

	}
	else if($sort=="tax") {
		foreach($examples as $example) {
			if($example->tax != $tax) {
				$subject = ""; $topic = ""; $descriptor = "";
				$tax = $example->tax;
				if(!$newtax)$tree.='</ul></ul></li></ul></li></ul></li>';
				$tree.='<li>'.$tax;
				$tree.='<ul>';

				$newtax=false;
				$newsub=true;
			}
			if($example->subject != $subject) {
				$subject = $example->subject;
				if(!$newsub)$tree.='</ul></ul></li></ul></li>';
				$tree.='<li>'.$subject;
				$tree.='<ul>';

				$newsub=false;
				$newtop=true;
			}
			if($example->topic != $topic) {
				$topic = $example->topic;
				if(!$newtop) $tree.='</ul></li></ul></li>';
				$tree.='<li>'.$topic;
				$tree.='<ul>';
				$newtop=false;
				$newdesc=true;
			}
			if($example->title != $descriptor) {
				$descriptor = $example->title;
				if(!$newdesc) $tree.='</ul></li>';
				$tree.='<li>'.$descriptor;
				$tree.='<ul>';
				$newdesc=false;
			}
			$text=$example->description;
			$text = str_replace("\"","",$text);
			$text = str_replace("\'","",$text);
			$text = str_replace("\n"," ",$text);
			$text = str_replace("\r"," ",$text);
			$text = str_replace(":","\:",$text);
			if($text)
				$tree.='<li><a onmouseover="Tip(\'' . $text . '\')" onmouseout="UnTip()">'.$example->example.'</a>';
			else
				$tree.='<li>'.$example->example;
			$tree.=block_exacomp_get_exampleicon($example);
			$tree.='</li>';
			$index++;
		}
	}

	$tree.='</ul></li></ul></li></ul></li></ul></form>';

	return $tree;
}
function block_exacomp_get_exampleicon($example) {
	global $DB, $CFG;
	$icon="";
	if($example->task) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/pdf.gif" height="16" width="16" alt="'.get_string("assigned_example", "block_exacomp").'" />';
		$icon = '<a target="_blank" href="' . $example->task . '" onmouseover="Tip(\''.get_string("aufgabenstellung", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	} if($example->solution) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/pdf solution.gif" height="16" width="16" alt="'.get_string("assigned_example", "block_exacomp").'" />';
		$icon .= '<a target="_blank" href="' . $example->solution . '" onmouseover="Tip(\''.get_string("solution", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	}
	if($example->attachement) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/attach_2.png" height="16" width="16" alt="'.get_string("aufgabenstellung", "block_exacomp").'" />';
		$icon .= '<a target="_blank" href="' . $example->attachement . '" onmouseover="Tip(\''.get_string("anhang", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	}if($example->externaltask) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/link.png" height="16" width="16" alt="'.get_string("aufgabenstellung", "block_exacomp").'" />';
		$icon .= '<a target="_blank" href="' . $example->externaltask . '" onmouseover="Tip(\''.get_string("externe_aufgabenstellung", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	}
	if($example->externalurl) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/link.png" height="16" width="16" alt="'.get_string("assigned_example", "block_exacomp").'" />';
		$icon .= '<a target="_blank" href="' . $example->externalurl . '" onmouseover="Tip(\''.get_string("externe_aufgabenstellung", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	}
	if($example->completefile) {
		$img = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/folder.png" height="16" width="16" alt="'.get_string("assigned_example", "block_exacomp").'" />';
		$icon .= '<a target="_blank" href="' . $example->completefile . '" onmouseover="Tip(\''.get_string("gesamtbeispiel", "block_exacomp").'\')" onmouseout="UnTip()">'.$img.'</a>';
	}
	return $icon;
}
function block_exacomp_check_portfolio_competences($userid) {
	global $DB;
	$sql = "SELECT d.title, d.id, da.activityid, i.name FROM {block_exacompdescractiv_mm} da, {block_exacompdescriptors} d, {block_exaportitem} i WHERE da.descrid=d.id AND i.id =da.activityid AND da.activitytype=2000 and i.userid=?";
	$comps = $DB->get_records_sql($sql, array($userid));
	return $comps;
}
function block_exacomp_check_teacher_assign($descriptor,$userid, $role=1) {
	global $DB, $CFG;
	$rs = $DB->get_record('block_exacompdescuser_mm',array("userid"=>$userid,"activityid"=>$descriptor->activityid,"descid"=>$descriptor->id,"role"=>$role));
	if($rs)
		return '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" alt="" />';
	else
		return '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" alt="" />';
}
function block_exacomp_check_student_assign($descriptor,$userid) {
	return block_exacomp_check_teacher_assign($descriptor, $userid, 0);
}
?>