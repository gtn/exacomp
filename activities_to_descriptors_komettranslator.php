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

require __DIR__ . '/inc.php';

//find all activities which have competencies
//for these competencies: find out if thy are in local_komettranslator
//if they are, find the related descriptorid
//for each activityid call the function with the related descriptorids
//done
//next -> gradings


create_related_examples();
block_exacomp_grade_descriptors_by_related_moodlecomp();

// create the examples based on the implicit relation that exists because of the moodlecomp to exacompdescriptor relation.
function create_related_examples(){
    global $DB;
    //relate the modules(activities) to descriptors -> create examples
    //First, get all the activityids that are relevant: all activities that have any competency where the competency exists in local_komettranslator
    $modules = $DB->get_records_sql('
            SELECT modcomp.cmid as moduleid
			FROM {competency_modulecomp} modcomp
            JOIN {local_komettranslator} komet ON komet.internalid = modcomp.competencyid
			');

    //Now we have every relevant module
    //for each module: get the competencies and thereby the descriptors
    foreach ($modules as $module) {
        // the join on source makes sure, that descriptors with the same sourceid but from different sources, are handled correctly
//            $descriptors = $DB->get_records_sql('
//                    SELECT komet.id as komettransid, modcomp.competencyid as competencyid, descr.id as descrid, descr.title as descrtitle, descr.sourceid as descrsourceid
//        			FROM {competency_modulecomp} modcomp
//                    JOIN {local_komettranslator}  komet ON komet.internalid = modcomp.competencyid
//                    JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} descr ON descr.sourceid = komet.itemid
//                    JOIN {'.BLOCK_EXACOMP_DB_DATASOURCES.'} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
//                    WHERE modcomp.cmid = ?
//        			', array($module->moduleid));
        //I keep this for debugging, but I actually only need an array of descriptorids of the local exacomp descriptors

        $descriptors = $DB->get_records_sql('
            SELECT descr.id as descrid
			FROM {competency_modulecomp} modcomp
            JOIN {local_komettranslator}  komet ON komet.internalid = modcomp.competencyid
            JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} descr ON descr.sourceid = komet.itemid
            JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
            WHERE modcomp.cmid = ?
			', array($module->moduleid));
        $descriptors = array_keys($descriptors); //the keys are the descriptorids, which is what I need
        block_exacomp_relate_example_to_activity(2, $module->moduleid, $descriptors);
    }
}




//TODO: if a competency is REMOVED from a module the example should be removed as well... right?

//TODO: safe the time of the last update somewhere. I can then only walk through the NEW gradings and NEW relations => better performance

//TODO: what about topics... actually, there are no examples for topics in eacomp. Grading will still be used


//competency_usercomp contains the gradings. or competency_usercompcourse
function block_exacomp_grade_descriptors_by_related_moodlecomp(){
    global $DB;

    //get all graded competencies that are graded and exist in local_komettranslator and are thereby relevant
    $competencies = $DB->get_records_sql('
            SELECT usercompcourse.competencyid as compid
			FROM {competency_usercompcourse} usercompcourse
            JOIN {local_komettranslator} komet ON komet.internalid = usercompcourse.competencyid
            WHERE usercompcourse.proficiency IS NOT NULL
			');

    foreach ($competencies as $competency){
        //JOIN {course_modules} cmod ON cmod.id = modcomp.cmid could be used to find the course => but there is a table competency_usercompCOURSE which solves this already
        $descriptorGradings = $DB->get_records_sql('
            SELECT descr.id as descrid, usercompcourse.courseid as courseid, usercompcourse.userid as userid, usercompcourse.proficiency as proficiency
			FROM {local_komettranslator} komet
            JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} descr ON descr.sourceid = komet.itemid
            JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
            JOIN {competency_usercompcourse} usercompcourse ON usercompcourse.competencyid = komet.internalid
            WHERE komet.internalid = ?
			', array($competency->compid));

        $topicGradings = $DB->get_records_sql('
            SELECT topic.id as topicid, usercompcourse.courseid as courseid, usercompcourse.userid as userid, usercompcourse.proficiency as proficiency
			FROM {local_komettranslator} komet
            JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} topic ON topic.sourceid = komet.itemid
            JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = topic.source AND datasrc.source = komet.sourceid)
            JOIN {competency_usercompcourse} usercompcourse ON usercompcourse.competencyid = komet.internalid
            WHERE komet.internalid = ?
			', array($competency->compid));

        //most of the time there will be only one descriptor/topic per id. But if there are different datasources there can be more than one time the same "itemid" in the local_komettranslator table
        foreach ($descriptorGradings as $grading){
//            block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult) --> FOR NOW: use Dichotom hardcoded => proficiency
            block_exacomp_set_user_competence($grading->userid, $grading->descrid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $grading->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading->proficiency);
        }

        foreach ($topicGradings as $grading){
            block_exacomp_set_user_competence($grading->userid, $grading->topicid, BLOCK_EXACOMP_TYPE_TOPIC, $grading->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading->proficiency);
        }
    }
}

