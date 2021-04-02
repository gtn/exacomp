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

//TODO: find all activities which have competencies
//for these competencies: find out if thy are in local_komettranslator
//if they are, find the related descriptorid
//for each activityid call the function with the related descriptorids
//done
//next -> gradings

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
//    $descriptors = $DB->get_records_sql('
//            SELECT komet.id as komettransid, modcomp.competencyid as competencyid, descr.id as descrid, descr.title as descrtitle, descr.sourceid as descrsourceid
//			FROM {competency_modulecomp} modcomp
//            JOIN {local_komettranslator}  komet ON komet.internalid = modcomp.competencyid
//            JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} descr ON descr.sourceid = komet.itemid
//            JOIN {'.BLOCK_EXACOMP_DB_DATASOURCES.'} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
//            WHERE modcomp.cmid = ?
//			', array($module->moduleid));
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



