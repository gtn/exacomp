<?php
function xmldb_block_exacomp_upgrade($oldversion) {
	global $DB,$CFG;
	$dbman = $DB->get_manager();
	$result=true;
	
	/// Add a new column newcol to the mdl_question_myqtype
	if ($oldversion < 2012021606) {
			
		$table = new xmldb_table('block_exacompdescuser_mm');
		$field_wert = new xmldb_field('wert');
		$field_wert->set_attributes(XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, null, null, 1, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated

		// Conditionally launch add temporary fields
		if (!$dbman->field_exists($table, $field_wert)) {
			$dbman->add_field($table, $field_wert);
		}
		////
		$table = new xmldb_table('block_exacompdescuser');
		$field_wert = new xmldb_field('wert');
		$field_wert->set_attributes(XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, null, null, 1, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated

		// Conditionally launch add temporary fields
		if (!$dbman->field_exists($table, $field_wert)) {
			$dbman->add_field($table, $field_wert);
		}

		$table = new xmldb_table('block_exacompsettings');

		// Adding fields to table role_reassign_rules
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null,null);
		$table->add_field('course', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null,null);
		$table->add_field('grading', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1,null);
			
		// Adding keys to table role_reassign_rules
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

		// Conditionally launch create table for role_reassign_rules
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}


			
		//upgrade_block_savepoint(true, 2009011700, 'block_desp');
	}
	if ($oldversion < 2012051002) {
		$table = new xmldb_table('block_exacompschooltypes');
		$field_wert = new xmldb_field('isoez');
		$field_wert->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, 0, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated

		// Conditionally launch add temporary fields
		if (!$dbman->field_exists($table, $field_wert)) {
			$dbman->add_field($table, $field_wert);
		}

		$table = new xmldb_table('block_exacomptopics');
		$field_wert = new xmldb_field('description');
		$field_wert->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated

		// Conditionally launch add temporary fields
		if (!$dbman->field_exists($table, $field_wert)) {
			$dbman->add_field($table, $field_wert);
		}
		////
	}
	if ($oldversion < 2012071300) {
		$table = new xmldb_table('block_exacompsubjects');
		$field = new xmldb_field('source');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, null, null, 1, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	}
	if ($oldversion < 2012091801) {
		$table = new xmldb_table('block_exacompsubjects');
		$field = new xmldb_field('titleshort');
		$field->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('titleshort');
		$field->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		$table = new xmldb_table('block_exacompexamples');
		$field = new xmldb_field('titleshort');
		$field->set_attributes(XMLDB_TYPE_CHAR, '200', null, null, null, null, null); // [XMLDB_ENUM, null,] Moodle 2.x deprecated
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	}
	if ($oldversion < 2012101202) {
		$table = new xmldb_table('block_exacompedulevels');
		$field = new xmldb_field('source');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
			$sql='UPDATE {block_exacompedulevels} SET source=1';
			$DB->Execute($sql);
		}
		$field = new xmldb_field('sourceid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
			$sql='UPDATE {block_exacompedulevels} SET sourceid=id';
			$DB->Execute($sql);
		}

		$table = new xmldb_table('block_exacompschooltypes');
		$field = new xmldb_field('source');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
			$sql='UPDATE {block_exacompschooltypes} SET source=1';
			$DB->Execute($sql);
		}
		$field = new xmldb_field('sourceid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
			$sql='UPDATE {block_exacompschooltypes} SET sourceid=id';
			$DB->Execute($sql);
		}
	}
	if ($oldversion < 2012101203) {
		$table = new xmldb_table('block_exacompexamples');
		$field = new xmldb_field('iseditable');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	}
	if ($oldversion < 2012121100) {

		// Define field id to be added to block_exacompprofilesettings
		$table = new xmldb_table('block_exacompprofilesettings');
		$field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
		$field2 = new xmldb_field('block', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'id');
		$field3 = new xmldb_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'block');
		$field4 = new xmldb_field('feedback', XMLDB_TYPE_BINARY, null, null, XMLDB_NOTNULL, null, null, 'itemid');

		$table->addField($field);
		$table->addField($field2);
		$table->addField($field3);
		$table->addField($field4);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

		if(!$dbman->table_exists($table))
			$dbman->create_table($table);

		// Conditionally launch add field id
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
			$dbman->add_field($table, $field2);
			$dbman->add_field($table, $field3);
			$dbman->add_field($table, $field4);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2012121100, 'exacomp');
	}
	if ($oldversion < 2012121101) {

		// Define field userid to be added to block_exacompprofilesettings
		$table = new xmldb_table('block_exacompprofilesettings');
		$field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'feedback');

		// Conditionally launch add field userid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2012121101, 'exacomp');
	}

	if ($oldversion < 2013011500) {

		// Define field userid to be added to block_exacompprofilesettings
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('activities', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null);

		// Conditionally launch add field userid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013011500, 'exacomp');
	}

	if ($oldversion < 2013030800) {

		// Define field userid to be added to block_exacompprofilesettings
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('tstamp', XMLDB_TYPE_INTEGER, 20, null, null, null, null, null);

		// Conditionally launch add field userid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013030800, 'exacomp');
	}

	if ($oldversion < 2013042413) {

		$table = new xmldb_table('block_exacompniveaus');
		$field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
		$field2 = new xmldb_field('sorting', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');
		$field3 = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'sorting');
		$field4 = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'title');
		$field5 = new xmldb_field('source',XMLDB_TYPE_INTEGER, 10, null, null, null, 0, null);
		$field6 = new xmldb_field('sourceid',XMLDB_TYPE_INTEGER, 20, null, null, null, 0, null);


		$table->addField($field);
		$table->addField($field2);
		$table->addField($field3);
		$table->addField($field4);
		$table->addField($field5);
		$table->addField($field6);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

		if(!$dbman->table_exists($table))
			$dbman->create_table($table);


		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013042413, 'exacomp');
	}

	if ($oldversion < 2013070400) {

		// Define field courseid to be added to block_exacompmdltype_mm
		$table = new xmldb_table('block_exacompmdltype_mm');
		$field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '20', null, null, null, '0', 'typeid');

		// Conditionally launch add field courseid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013070400, 'exacomp');
	}

	if ($oldversion < 2013070904) {

		// Define table block_exacompexameval to be created
		$table = new xmldb_table('block_exacompexameval');

		// Adding fields to table block_exacompexameval
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('descrexamp_mm_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('studentid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('teacher_evaluation', XMLDB_TYPE_INTEGER, '8', null, null, null, null);
		$table->add_field('teacher_reviewerid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
		$table->add_field('student_evaluation', XMLDB_TYPE_INTEGER, '8', null, null, null, null);
		$table->add_field('starttime', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('endtime', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('studypartner', XMLDB_TYPE_TEXT, null, null, null, null, null);

		// Adding keys to table block_exacompexameval
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

		// Conditionally launch create table for block_exacompexameval
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013070904, 'exacomp');
	}

	if ($oldversion < 2013071200) {

		// Define field uses_activities to be added to block_exacompsettings
		$table = new xmldb_table('block_exacompsettings');

		$field = new xmldb_field('uses_activities', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'tstamp');
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		$field = new xmldb_field('show_all_descriptors', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'uses_activities');
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}

		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013071200, 'exacomp');
	}

	if($oldversion < 2013071600) {
		// Define field cat to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('cat', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'titleshort');
		 
		// Conditionally launch add field cat
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field requirement to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('requirement', XMLDB_TYPE_TEXT, null, null, null, null, null, 'cat');
		 
		// Conditionally launch add field requirement
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field benefit to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('benefit', XMLDB_TYPE_TEXT, null, null, null, null, null, 'requirement');
		 
		// Conditionally launch add field benefit
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field knowledgecheck to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('knowledgecheck', XMLDB_TYPE_TEXT, null, null, null, null, null, 'benefit');
		 
		// Conditionally launch add field knowledgecheck
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field ataxonomie to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('ataxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'knowledgecheck');
		 
		// Conditionally launch add field ataxonomie
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field btaxonomie to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('btaxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'ataxonomie');
		 
		// Conditionally launch add field btaxonomie
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		 
		// Define field ctaxonomie to be added to block_exacomptopics
        $table = new xmldb_table('block_exacomptopics');
        $field = new xmldb_field('ctaxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'btaxonomie');

        // Conditionally launch add field ctaxonomie
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field dtaxonomie to be added to block_exacomptopics
        $table = new xmldb_table('block_exacomptopics');
        $field = new xmldb_field('dtaxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'ctaxonomie');
        
        // Conditionally launch add field dtaxonomie
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field etaxonomie to be added to block_exacomptopics
        $table = new xmldb_table('block_exacomptopics');
        $field = new xmldb_field('etaxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'dtaxonomie');
        
        // Conditionally launch add field etaxonomie
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field ftaxonomie to be added to block_exacomptopics
        $table = new xmldb_table('block_exacomptopics');
        $field = new xmldb_field('ftaxonomie', XMLDB_TYPE_TEXT, null, null, null, null, null, 'etaxonomie');
        
        // Conditionally launch add field ftaxonomie
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field number to be added to block_exacompsubjects
        $table = new xmldb_table('block_exacompsubjects');
        $field = new xmldb_field('number', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'titleshort');
        
        // Conditionally launch add field number
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field cat to be added to block_exacompsubjects
        $table = new xmldb_table('block_exacompsubjects');
        $field = new xmldb_field('cat', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'number');
        
        // Conditionally launch add field cat
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field exampletext to be added to block_exacompdescriptors
        $table = new xmldb_table('block_exacompdescriptors');
        $field = new xmldb_field('exampletext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'source');
        
        // Conditionally launch add field exampletext
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field comment to be added to block_exacompdescriptors
        $table = new xmldb_table('block_exacompdescriptors');
        $field = new xmldb_field('additionalinfo', XMLDB_TYPE_TEXT, null, null, null, null, null, 'exampletext');
        
        // Conditionally launch add field comment
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
		 
        // Define table block_exacompcategories to be created
        $table = new xmldb_table('block_exacompcategories');
        
        // Adding fields to table block_exacompcategories
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lvl', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        
        // Adding keys to table block_exacompcategories
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Conditionally launch create table for block_exacompcategories
        if (!$dbman->table_exists($table)) {
        	$dbman->create_table($table);
        }
        
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013071600, 'exacomp');
	}
	if ($oldversion < 2013071801) {
	
		 // Define field sourceid to be added to block_exacompcategories
        $table = new xmldb_table('block_exacompcategories');
        $field = new xmldb_field('sourceid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, 'parentid');

        // Conditionally launch add field sourceid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013071801, 'exacomp');
	}
	
	if ($oldversion < 2013071900) {
	
		// Rename field exampleid on table block_exacompexameval to NEWNAMEGOESHERE
		$table = new xmldb_table('block_exacompexameval');
		$field = new xmldb_field('descrexamp_mm_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'id');
	
		if($dbman->field_exists($table, $field)) {
			// Launch rename field exampleid
			$dbman->rename_field($table, $field, 'exampleid');
		}
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013071900, 'exacomp');
		
	}
	
	if ($oldversion < 2013090500) {
	
		// Define field parentid to be added to block_exacomptopics
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'ftaxonomie');
	
		// Conditionally launch add field parentid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013090500, 'exacomp');
	}
	
	if ($oldversion < 2013091000) {
	
		// Define field creatorid to be added to block_exacompexamples
		$table = new xmldb_table('block_exacompexamples');
		$field = new xmldb_field('creatorid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'iseditable');
	
		// Conditionally launch add field creatorid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013091000, 'exacomp');
	}
	
	global $DB;
	// BEWERTUNGSSCHEMA UMDREHEN
	if($oldversion < 2013092602) {
		
		$coursegradings = $DB->get_records_menu("block_exacompsettings",null,"","course,grading");
		$competencies = $DB->get_records("block_exacompdescuser");
		foreach($competencies as $competence) {
			if(isset($coursegradings[$competence->courseid]) && $coursegradings[$competence->courseid] > 1) {
				$competence->wert = ($coursegradings[$competence->courseid] + 1) - $competence->wert;
				$DB->update_record("block_exacompdescuser", $competence);
			}
		}
		
		$competencies = $DB->get_records_sql("
				SELECT c.*, cm.course as courseid FROM {block_exacompdescuser_mm} c
				JOIN {course_modules} cm ON c.activityid = cm.id
				");
		foreach($competencies as $competence) {
			if(isset($coursegradings[$competence->courseid]) && $coursegradings[$competence->courseid] > 1) {
				$competence->wert = ($coursegradings[$competence->courseid] + 1) - $competence->wert;
				$DB->update_record("block_exacompdescuser_mm", $competence);
			}
		}
	}
	
	if ($oldversion < 2013100400) {
	
		// Define field sourceid to be added to block_exacompniveaus
        $table = new xmldb_table('block_exacompniveaus');
        $field = new xmldb_field('sourceid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'parent');

        // Conditionally launch add field sourceid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	
        // Define field source to be added to block_exacompniveaus
        $table = new xmldb_table('block_exacompniveaus');
        $field = new xmldb_field('source', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'sourceid');
        
        // Conditionally launch add field source
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013100400, 'exacomp');
	}
	
	if ($oldversion < 2013100900) {
	
		// Define field show_all_examples to be added to block_exacompsettings
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('show_all_examples', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'show_all_descriptors');
	
		// Conditionally launch add field show_all_examples
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013100900, 'exacomp');
	}
	
	if ($oldversion < 2013102501) {
	
		// Define table block_exacomptopicuser to be created
		$table = new xmldb_table('block_exacomptopicuser');
	
		// Adding fields to table block_exacomptopicuser
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('topicid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('role', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
		$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('wert', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, null);
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('subjid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table block_exacomptopicuser
		$table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for block_exacomptopicuser
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013102501, 'exacomp');
	}
	if ($oldversion < 2013102502) {
	
		// Define field subjid to be added to block_exacomptopicuser
		$table = new xmldb_table('block_exacomptopicuser');
		$field = new xmldb_field('subjid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
	
		// Conditionally launch add field subjid
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// exacomp savepoint reached
		upgrade_block_savepoint(true, 2013102502, 'exacomp');
	}
	
    if ($oldversion < 2013121000) {

        // Define table block_exacompdescbadge_mm to be created.
        $table = new xmldb_table('block_exacompdescbadge_mm');

        // Adding fields to table block_exacompdescbadge_mm.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('descid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('badgeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table block_exacompdescbadge_mm.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_exacompdescbadge_mm.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Exacomp savepoint reached.
        upgrade_block_savepoint(true, 2013121000, 'exacomp');
    }
	
	if ($oldversion < 2014031301) {
			$table = new xmldb_table('block_exacompsubjects');
			$field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'titleshort');	
					// Conditionally launch add field subjid
					if (!$dbman->field_exists($table, $field)) {
						$dbman->add_field($table, $field);
					}
					
			$table = new xmldb_table('block_exacompdescriptors');
			$field = new xmldb_field('profoundness', XMLDB_TYPE_INTEGER, '10', 0, null, null, null, 'niveauid');
					// Conditionally launch add field subjid
					if (!$dbman->field_exists($table, $field)) {
						$dbman->add_field($table, $field);
					}
		        // Exacomp savepoint reached.
	       upgrade_block_savepoint(true, 2014031301, 'exacomp');
	}
	
	if ($oldversion < 2014031302) {
			$table = new xmldb_table('block_exacompschooltypes');
			$field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'title');	
					// Conditionally launch add field subjid
					if (!$dbman->field_exists($table, $field)) {
						$dbman->add_field($table, $field);
					}
					
			$table = new xmldb_table('block_exacompexamples');
			$field = new xmldb_field('tstamp', XMLDB_TYPE_INTEGER, '20', 0, null, null, null, 'creatorid');
					// Conditionally launch add field subjid
					if (!$dbman->field_exists($table, $field)) {
						$dbman->add_field($table, $field);
					}
		        // Exacomp savepoint reached.
	       upgrade_block_savepoint(true, 2014031302, 'exacomp');
	}
 	if ($oldversion < 2014031303) {	
			$table = new xmldb_table('block_exacompexamples');
			$field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', 0, null, null, null, 'tstamp');
					// Conditionally launch add field subjid
					if (!$dbman->field_exists($table, $field)) {
						$dbman->add_field($table, $field);
					}

        // Exacomp savepoint reached.
        upgrade_block_savepoint(true, 2014031303, 'exacomp');
 	}

 	if ($oldversion < 2014041400) {
	 	
 		// Define field parent to be added to block_exacomptaxonomies.
 		$table = new xmldb_table('block_exacomptaxonomies');
	 		
 		$field = new xmldb_field('sourceid', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'title');
	 		
 		// Conditionally launch add field sourceid.
 		if (!$dbman->field_exists($table, $field)) {
 			$dbman->add_field($table, $field);
 		}
	 		
 		$field = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'sourceid');
	 	
 		// Conditionally launch add field parent.
 		if (!$dbman->field_exists($table, $field)) {
 			$dbman->add_field($table, $field);
 		}
 	
 		$table = new xmldb_table('block_exacompexamples');
 		
 		$key = new xmldb_key('taxid', XMLDB_KEY_FOREIGN, array('taxid'));
 		// Launch drop key primary.
 		$dbman->drop_key($table, $key);
 		$field = new xmldb_field('taxid', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'description');
 		
 		// Launch change of nullability for field taxid.
 		$dbman->change_field_notnull($table, $field);
 		
 		upgrade_block_savepoint(true, 2014041400, 'exacomp');
 	}
 	
 	if ($oldversion < 2014041401) {
 	
 		// Define field id to be added to block_exacompdescriptors.
 		$table = new xmldb_table('block_exacompdescriptors');
 		$field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
 	
 		// Conditionally launch add field id.
 		if (!$dbman->field_exists($table, $field)) {
 			$dbman->add_field($table, $field);
 		}
 	
 		// Exacomp savepoint reached.
 		upgrade_block_savepoint(true, 2014041401, 'exacomp');
 	}
 	
 	if ($oldversion < 2014042600) {
	 	
		// Define field descriptorassociation to be added to block_exacompdescractiv_mm.
		$table = new xmldb_table('block_exacompdescractiv_mm');
		$field = new xmldb_field('descriptorassociation', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'coursetitle');

		// Conditionally launch add field descriptorassociation.
	 	if (!$dbman->field_exists($table, $field)) {
	 		$dbman->add_field($table, $field);
	 	}
	 		 		// Exacomp savepoint reached.
	 	upgrade_block_savepoint(true, 2014042600, 'exacomp');
	 	
 	}
	if ($oldversion < 2014042900) {
 	
 		// Define table block_exacomptopicuser_mm to be created.
 		$table = new xmldb_table('block_exacomptopicuser_mm');
 	
 		// Adding fields to table block_exacomptopicuser_mm.
 		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
 		$table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
 		$table->add_field('topicid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
 		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
 		$table->add_field('reviewerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
 		$table->add_field('role', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '1');
 		$table->add_field('activitytype', XMLDB_TYPE_INTEGER, '10', null, null, null, '1');
 		$table->add_field('wert', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
 	
 		// Adding keys to table block_exacomptopicuser_mm.
 		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
 	
 		// Conditionally launch create table for block_exacomptopicuser_mm.
 		if (!$dbman->table_exists($table)) {
 			$dbman->create_table($table);
 		}
	 	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014042900, 'exacomp');
	}
	 	 
	if ($oldversion < 2014050900) { 	
	 	// Define field restorelink to be added to block_exacompexamples.
	 	$table = new xmldb_table('block_exacompexamples');
	 	$field = new xmldb_field('restorelink', XMLDB_TYPE_TEXT, null, null, null, null, null, 'parentid');

	 	// Conditionally launch add field restorelink.
	 	if (!$dbman->field_exists($table, $field)) {
	 		$dbman->add_field($table, $field);
	 	}
	 		
	 	$field = new xmldb_field('metalink', XMLDB_TYPE_TEXT, null, null, null, null, null, 'restorelink');
	 		
	 	// Conditionally launch add field metalink.
	 	if (!$dbman->field_exists($table, $field)) {
	 		$dbman->add_field($table, $field);
	 	}
	 		
	 	$field = new xmldb_field('packagelink', XMLDB_TYPE_TEXT, null, null, null, null, null, 'metalink');
	 		
	 	// Conditionally launch add field packagelink.
	 	if (!$dbman->field_exists($table, $field)) {
	 		$dbman->add_field($table, $field);
	 	}
	 	
	 	// Exacomp savepoint reached.
	 	upgrade_block_savepoint(true, 2014050900, 'exacomp');
	 }
	 	
	//exacomp next generation -> some major changes in db
	if($oldversion < 2014082601){
		/* block_exacomptopics, change field cat to catid, add key catid */
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('cat', XMLDB_TYPE_INTEGER, '11');
		$dbman->rename_field($table, $field, 'catid');
	
		$key = new xmldb_key('catid', XMLDB_KEY_FOREIGN, array('catid'), 'block_exacompcategories', array('id'));
		$dbman->add_key($table, $key);
		
		
		/* block_exacompsubjects, change field cat to catid, add key catid */
		$table = new xmldb_table('block_exacompsubjects');
		$field = new xmldb_field('cat', XMLDB_TYPE_INTEGER, '11');
		$dbman->rename_field($table, $field, 'catid');
	
		$field = new xmldb_field('number', XMLDB_TYPE_INTEGER, '11');
		$dbman->rename_field($table, $field, 'numb');
		
		$key = new xmldb_key('catid', XMLDB_KEY_FOREIGN, array('catid'), 'block_exacompcategories', array('id'));
		$dbman->add_key($table, $key);
		
		/* block_exacompexamples, create new foreign key creatorid */
		$table = new xmldb_table('block_exacompexamples');
		$key = new xmldb_key('creatorid', XMLDB_KEY_FOREIGN, array('creatorid'), 'user', array('id'));
		$dbman->add_key($table, $key);
		
		$field = new xmldb_field('restorelink', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('metalink', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('packagelink', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		/* block_exacomptaxonomies, rename field parent to parentid */
		$table = new xmldb_table('block_exacomptaxonomies');
		$field = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
		$dbman->rename_field($table, $field, 'parentid');
		
		$field = new xmldb_field('title', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		/* block_exacompdescractiv_mm */
		$table = new xmldb_table('block_exacompdescractiv_mm');
		
		//rename table
		$dbman->rename_table($table, 'block_exacompcompactiv_mm');
		$table = new xmldb_table('block_exacompcompactiv_mm');
		
		//delete key activityid, activitytype and descid
		$key = new xmldb_key('descrid', XMLDB_KEY_FOREIGN, array('descrid'), 'block_exacompdescriptors', array('id'));
        $dbman->drop_key($table, $key);
        
		$key = new xmldb_key('activityid', XMLDB_KEY_FOREIGN, array('activityid'), 'activity', array('id'));
		$dbman->drop_key($table, $key);
		
		$key = new xmldb_key('activitytype', XMLDB_KEY_FOREIGN, array('activitytype'), 'modules', array('id'));
		$dbman->drop_key($table, $key);
		
		//rename field descrid to compid
		$field = new xmldb_field('descrid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
		$dbman->rename_field($table, $field, 'compid');
		
		//rename field descriptorassociation and change default value
		$field = new xmldb_field('descriptorassociation', XMLDB_TYPE_INTEGER, '1', null, null, null, '1');
		$dbman->rename_field($table, $field, 'comptype');
		$field = new xmldb_field('comptype', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		$dbman->change_field_default($table, $field);
		
		$records = $DB->get_records('block_exacompcompactiv_mm');
		foreach($records as $record){
			if($record->comptype == 1)
				$record->comptype = 0 ;
			else if($record->comptype == 0)
				$record->comptype = 1;
				
			$DB->update_record('block_exacompcompactiv_mm', $record);
		}
		
		//add field eportfolioitem 
		$field = new xmldb_field('eportfolioitem', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		$dbman->add_field($table, $field);
		
		//traverse through entries, if activitytype = 2000, set eportfolioitem = 1
		$records = $DB->get_records('block_exacompcompactiv_mm');
		
		foreach($records as $record){
			if($record->activitytype == 2000){
				$record->eportfolioitem = 1;
				$DB->update_record('block_exacompcompactiv_mm', $record);
			}
		}
		
		//delete field activitytype
		$field = new xmldb_field('activitytype', XMLDB_TYPE_INTEGER, '10', null, null, null, '1');
		$dbman->drop_field($table, $field);
		
		//change type of activity- and coursetitle
		$field = new xmldb_field('activitytitle', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('coursetitle', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
		
		
		/* block_exacompdescuser_mm */
		$table = new xmldb_table('block_exacompdescuser_mm');
		
		//rename table
		$dbman->rename_table($table, 'block_exacompcompuser_mm');
		$table = new xmldb_table('block_exacompcompuser_mm');
		
		//delete key activityid and descid
		$key = new xmldb_key('descid', XMLDB_KEY_FOREIGN, array('descid'), 'block_exacompdescriptors', array('id'));
        $dbman->drop_key($table, $key);
        
		$key = new xmldb_key('activityid', XMLDB_KEY_FOREIGN, array('activityid'), 'assignment', array('id'));
		$dbman->drop_key($table, $key);
		
		//rename field descid to compid
		$field = new xmldb_field('descid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
		$dbman->rename_field($table, $field, 'compid');
		
		//rename field wert to value
		$field = new xmldb_field('wert', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
    	$dbman->rename_field($table, $field, 'value');
    	
    	//add field comptype
    	$field = new xmldb_field('comptype', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		$dbman->add_field($table, $field);
		
		//add field eportfolioitem
		$field = new xmldb_field('eportfolioitem', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		$dbman->add_field($table, $field);
		
		//add field timestamp
		$field = new xmldb_field('timestamp', XMLDB_TYPE_INTEGER, '20');
		$dbman->add_field($table, $field);
		
		//traverse through entries, if activitytype = 2000, set eportfolioitem = 1
		$records = $DB->get_records('block_exacompcompuser_mm');
		
		foreach($records as $record){
			if($record->activitytype == 2000){
				$record->eportfolioitem = 1;
				$DB->update_record('block_exacompcompuser_mm', $record);
			}
		}
		
		//delete field activitytype
		$field = new xmldb_field('activitytype', XMLDB_TYPE_INTEGER, '20', null, null, null, '1');
		$dbman->drop_field($table, $field);
		
		
		/* block_exacompmdltype_mm */
		$table = new xmldb_table('block_exacompmdltype_mm');
		
		//drop key typeid
    	$key = new xmldb_key('typeid', XMLDB_KEY_FOREIGN, array('typeid'), 'block_exacompschooltypes', array('id'));
    	$dbman->drop_key($table, $key);
    	
		//rename fiels typeid to stid
		$field = new xmldb_field('typeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
    	$dbman->rename_field($table, $field, 'stid');
    	
    	//add key stid and courseid
    	$key = new xmldb_key('stid', XMLDB_KEY_FOREIGN, array('stid'), 'block_exacompschooltypes', array('id'));
    	$dbman->add_key($table, $key);
    	
    	$key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
    	$dbman->add_key($table, $key);
		
    	
    	/* block_exacompdescuser */
    	$table = new xmldb_table('block_exacompdescuser');
    	
    	//rename table
    	$dbman->rename_table($table, 'block_exacompcompuser');
    	$table = new xmldb_table('block_exacompcompuser');
    	
    	//delete key descid
		$key = new xmldb_key('descid', XMLDB_KEY_FOREIGN, array('descid'), 'block_exacompdescriptors', array('id'));
        $dbman->drop_key($table, $key);
        
    	//rename field descid to compid
    	$field = new xmldb_field('descid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
		$dbman->rename_field($table, $field, 'compid');
		
		//rename field wert to value
		$field = new xmldb_field('wert', XMLDB_TYPE_INTEGER, '5', null, null, null);
    	$dbman->rename_field($table, $field, 'value');
    	
    	//add field comptype
    	$field = new xmldb_field('comptype', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		$dbman->add_field($table, $field);
		
		//add field timestamp
		$field = new xmldb_field('timestamp', XMLDB_TYPE_INTEGER, '20');
		$dbman->add_field($table, $field);
	
		//add key courseid
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
       	$dbman->add_key($table, $key);
       	
       	
       	/* block_exacompsettings */
        $table = new xmldb_table('block_exacompsettings');
        
        //add field usedetailpage
        $field = new xmldb_field('usedetailpage', XMLDB_TYPE_INTEGER, '1');
		$dbman->add_field($table, $field);
        
         //drop key course
        $key = new xmldb_key('course', XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));
    	$dbman->drop_key($table, $key);
        
        //rename field course to courseid
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL);
        $dbman->rename_field($table, $field, 'courseid');
        
        //add key courseid
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $dbman->add_key($table, $key);
        
        $field = new xmldb_field('activities', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
        
        /* block_exacompprofilesettings */
  		$table = new xmldb_table('block_exacompprofilesettings');
  		
  		//add key userid
  		$key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
     	$dbman->add_key($table, $key);
     	
     	$field = new xmldb_field('block', XMLDB_TYPE_CHAR, '1333');
		$dbman->change_field_type($table, $field);
        
     	
     	/* block_exacompniveaus */
     	$table = new xmldb_table('block_exacompniveaus');
     	
     	//rename parent to parentid
     	$field = new xmldb_field('parent', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
     	$dbman->rename_field($table, $field, 'parentid');
     	
     	
     	/* block_exacompexameval */
     	$table = new xmldb_table('block_exacompexameval');
     	
     	//change type of fields starttime and endtime
     	$field = new xmldb_field('starttime', XMLDB_TYPE_INTEGER, '20');
     	$dbman->change_field_type($table, $field);
     	
     	$field = new xmldb_field('endtime', XMLDB_TYPE_INTEGER, '20');
     	$dbman->change_field_type($table, $field);
     	
     	//add key studentid, exampleid, courseid and teacher_reviewerid
     	$key = new xmldb_key('studentid', XMLDB_KEY_FOREIGN, array('studentid'), 'user', array('id'));
     	$dbman->add_key($table, $key);
     	
     	$key = new xmldb_key('exampleid', XMLDB_KEY_FOREIGN, array('exampleid'), 'block_exacompexamples', array('id'));
     	$dbman->add_key($table, $key);
     	
     	$key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
     	$dbman->add_key($table, $key);
     	
     	$key = new xmldb_key('teacher_reviewerid', XMLDB_KEY_FOREIGN, array('teacher_reviewerid'), 'user', array('id'));
     	$dbman->add_key($table, $key);
     	
     	$field = new xmldb_field('studypartner', XMLDB_TYPE_CHAR, '100');
		$dbman->change_field_type($table, $field);
     	
     	/* block_exacompdescbadge_mm */
     	$table = new xmldb_table('block_exacompdescbadge_mm');
     	
     	// Conditionally launch create table for block_exacompdescbadge_mm.
     	if (!$dbman->table_exists($table)) {
     		// Adding fields to table block_exacompdescbadge_mm.
     		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
     		$table->add_field('descid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
     		$table->add_field('badgeid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
     		
     		// Adding keys to table block_exacompdescbadge_mm.
     		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
     		
     		$dbman->create_table($table);
     	}
     	
     	//add key descid and badgeid
     	$key = new xmldb_key('descid', XMLDB_KEY_FOREIGN, array('descid'), 'block_exacompdescriptors', array('id'));
        $dbman->add_key($table, $key);
        
        $key = new xmldb_key('badgeid', XMLDB_KEY_FOREIGN, array('badgeid'), 'badge', array('id'));
        $dbman->add_key($table, $key);
        
        /* block_exacomptopicuser */
        //transfer data from block_exacomptopicuser to block_exacompcompuser and delete table block_exacomptopicuser
        $result = $DB->get_records('block_exacomptopicuser');
        
        foreach($result as $record){
        	$insert = new stdClass();
        	$insert->userid = $record->userid; 
        	$insert->compid = $record->topicid;
        	$insert->reviewerid = $record->reviewerid;
        	$insert->role = $record->role;
        	$insert->courseid = $record->courseid;
        	$insert->value = $record->wert;
        	$insert->comptype = 1;
        	
        	$DB->insert_record('block_exacompcompuser', $insert);
        }
        
       // $table = new xmldb_table('block_exacomptopicuser');
       // $dbman->drop_table($table);
        
        
        /* block_exacomptopicuser_mm */
        //transfer data from block_exacomptopicuser_mm to block_exacompcompuser_mm and delete table block_exacomptopicuser_mm
        $results = $DB->get_records('block_exacomptopicuser_mm');
        
        foreach($result as $record){
        	$insert = new stdClass();
        	$insert->activityid = $record->activityid;
        	$insert->compid = $record->topicid;
        	$insert->userid = $record->userid;
        	$insert->reviewerid = $record->reviewerid;
        	$insert->role = $record->role;
        	$insert->value = $record->wert;
        	$insert->comptype = 1;
        	if($record->activitytype == 2000)
        		$insert->eportfolioitem = 1;
        	else 
        		$insert->eportfolioitem = 0;
        }
        
       // $table = new xmldb_table('block_exacomptopicuser_mm');
       // $dbman->drop_table($table); 
        
        /*block_exacompdescriptors */
        
       	$table = new xmldb_table('block_exacompdescriptors');
        $field = new xmldb_field('title', XMLDB_TYPE_CHAR, '1333');
     	$dbman->change_field_type($table, $field);
	
        upgrade_block_savepoint(true, 2014082601, 'exacomp');
	}
	
	if ($oldversion < 2014082710) {
	
		// Define field sourceid to be added to block_exacompskills.
        $table = new xmldb_table('block_exacompskills');
        $field = new xmldb_field('sourceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'title');

        // Conditionally launch add field sourceid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	
        $field = new xmldb_field('source', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'sourceid');
        
        // Conditionally launch add field source.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        // Define field source to be added to block_exacomptaxonomies.
        $table = new xmldb_table('block_exacomptaxonomies');
        $field = new xmldb_field('source', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'parentid');
        
        // Conditionally launch add field source.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
        $table = new xmldb_table('block_exacompcategories');
        $field = new xmldb_field('source', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '1', 'sourceid');
        
        // Conditionally launch add field source.
        if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
        
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014082710, 'exacomp');
	}
	if ($oldversion < 2014090800) {
	
		// Changing precision of field title on table block_exacomptopics to (1333).
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('title', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'sorting');
	
		// Launch change of precision for field title.
		$dbman->change_field_precision($table, $field);
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014090800, 'exacomp');
	}
	if ($oldversion < 2014090900) {
		$table = new xmldb_table('block_exacompsubjects');
		$field = new xmldb_field('description', XMLDB_TYPE_TEXT, null);
		if (!$dbman->field_exists($table, $field)) {
        	$dbman->add_field($table, $field);
        }
		// Define field infolink to be added to block_exacompsubjects.
		$table = new xmldb_table('block_exacompsubjects');
		$field = new xmldb_field('infolink', XMLDB_TYPE_CHAR, '400', null, null, null, null, 'description');
	
		// Conditionally launch add field infolink.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014090900, 'exacomp');
	}
	if ($oldversion < 2014092500) {
	
		$url = $DB->get_record('config_plugins',array('plugin'=>'exacomp','name'=>'xmlserverurl'));
		if($url->value == 'https://raw.githubusercontent.com/gtn/edustandards/master/austria/exacomp_data.xml') {
			$url->value = 'https://raw.githubusercontent.com/gtn/edustandards/master/austria/exacomp_data_v2.xml';
			$DB->update_record('config_plugins',$url);
		}
			
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014092500, 'exacomp');
	}
	if ($oldversion < 2014092600) {
		//change type of text fields
		$table = new xmldb_table('block_exacomptopics');
		
		$field = new xmldb_field('requirement', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('benefit', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('knowledgecheck', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('ataxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('btaxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('ctaxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('dtaxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('etaxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('ftaxonomie', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		/*block_exacompdescriptors */
		
		$table = new xmldb_table('block_exacompdescriptors');
		
		$field = new xmldb_field('exampletext', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		$field = new xmldb_field('additionalinfo', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		/*block_exacompschooltypes */
		
		$table = new xmldb_table('block_exacompschooltypes');
		$field = new xmldb_field('description', XMLDB_TYPE_TEXT, null);
		$dbman->change_field_type($table, $field);
		
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014092600, 'exacomp');
	}
	
	if ($oldversion < 2014100800) {
	
		// Changing precision of field title on table block_exacomptopics to (1333).
		$table = new xmldb_table('block_exacomptopics');
		$field = new xmldb_field('title', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'sorting');
	
		// Launch change of precision for field title.
		$dbman->change_field_precision($table, $field);
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014100800, 'exacomp');
	}
	if ($oldversion < 2014111100) {
	
		// Define field profoundness to be added to block_exacompsettings.
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('profoundness', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0', 'usedetailpage');
	
		// Conditionally launch add field profoundness.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014111100, 'exacomp');
	}
	if($oldversion < 2014111400){
		// Changing nullability of field profoundness on table block_exacompdescriptors to not null.
		$table = new xmldb_table('block_exacompdescriptors');
		$field = new xmldb_field('profoundness', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'additionalinfo');
	
		$dbman->drop_field($table, $field);
		
		$field = new xmldb_field('profoundness', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0', 'additionalinfo');
		$dbman->add_field($table, $field);
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014111400, 'exacomp');
	}
	if ($oldversion < 2014112001) {
	
		// Define field filteredtaxonomies to be added to block_exacompsettings.
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('filteredtaxonomies', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, '["100000000"]', 'profoundness');	
		
		// Conditionally launch add field filteredtaxonomies.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014112001, 'exacomp');
	}
	if ($oldversion < 2014112401) {
	
		// Define field filteredtaxonomies to be added to block_exacompsettings.
		$table = new xmldb_table('block_exacompsettings');
		$field = new xmldb_field('filteredtaxonomies', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, '["100000000"]', 'profoundness');
	
		$dbman->drop_field($table, $field);
		
		// Conditionally launch add field filteredtaxonomies.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Exacomp savepoint reached.
		upgrade_block_savepoint(true, 2014112401, 'exacomp');
	}
	if ($oldversion < 2015012700) {
	
	    // Define field parentid to be added to block_exacompdescriptors.
	    $table = new xmldb_table('block_exacompdescriptors');
	    $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'profoundness');
	
	    // Conditionally launch add field parentid.
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	
	    // Exacomp savepoint reached.
	    upgrade_block_savepoint(true, 2015012700, 'exacomp');
	}
	if ($oldversion < 2015012701) {
	
	    $table = new xmldb_table('block_exacompdescriptors');
	    $field = new xmldb_field('epop', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0', 'profoundness');
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	    $table = new xmldb_table('block_exacompsubjects');
	    $field = new xmldb_field('epop', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0', 'infolink');
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	    $table = new xmldb_table('block_exacomptopics');
	    $field = new xmldb_field('epop', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0','parentid');
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	    $table = new xmldb_table('block_exacompexamples');
	    $field = new xmldb_field('epop', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0','packagelink');
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	    $table = new xmldb_table('block_exacompschooltypes');
	    $field = new xmldb_field('epop', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, '0','description');
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	
	    // Exacomp savepoint reached.
	    upgrade_block_savepoint(true, 2015012701, 'exacomp');
	}
	
	if ($oldversion < 2015021903) {
	
	    // Define table block_exaportlovevet to be created.
	    $table = new xmldb_table('block_exacompitemexample');
	
	    // Adding fields to table block_exaportlovevet.
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	    $table->add_field('exampleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('datemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
	    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
	    $table->add_field('teachervalue', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
	    $table->add_field('studentvalue', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
	
	    // Adding keys to table block_exaportlovevet.
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
	    // Conditionally launch create table for block_exaportlovevet.
	    if (!$dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	
	    // Define table block_exaportexternaltrainer to be created.
	    $table = new xmldb_table('block_exacompexternaltrainer');
	
	    // Adding fields to table block_exaportexternaltrainer.
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	    $table->add_field('trainerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
	
	    // Adding keys to table block_exaportexternaltrainer.
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
	    // Conditionally launch create table for block_exaportexternaltrainer.
	    if (!$dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	
	    // Exaport savepoint reached.
	    upgrade_block_savepoint(true, 2015021903, 'exacomp');
	}
	if($oldversion < 2015031502){
	    global $DB;
	    // Define table block_exacompcrosssubjects to be created.
	    $table = new xmldb_table('block_exacompcrosssubjects');
	    
	    // Adding fields to table block_exacompcrosssubjects.
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	    $table->add_field('title', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('sorting', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
	    $table->add_field('sourceid', XMLDB_TYPE_INTEGER, '10', null, null, null);
	    $table->add_field('source', XMLDB_TYPE_INTEGER, '4', null, null, null, '1');
	    $table->add_field('description', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
	    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
	    $table->add_field('creatorid', XMLDB_TYPE_INTEGER, '5', null, null, null, '0');
	
	    // Adding keys to table block_exacompcrosssubjects.
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	    $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
	    $table->add_key('creatorid', XMLDB_KEY_FOREIGN, array('creatorid'), 'user', array('id'));

	    // Conditionally launch create table for block_exacompcrosssubjects.
	    if (!$dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	    
	    // Define table block_exacompcrosssubjects to be created.
	    $table = new xmldb_table('block_exacompdescrcross_mm');
	    
	    // Adding fields to table block_exacompdescrcross_mm.
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	    $table->add_field('descrid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('crosssubjid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
	
	    // Adding keys to table block_exacompcdescrross_mm.
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	    $table->add_key('descrid', XMLDB_KEY_FOREIGN, array('descrid'), 'block_exacompdescriptors', array('id'));
	    $table->add_key('crosssubjid', XMLDB_KEY_FOREIGN, array('crosssubjid'), 'block_exacompcrosssubjects', array('id'));

	    // Conditionally launch create table for block_exacompdescrcros_mm.
	    if (!$dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	    
	    // Exaport savepoint reached.
	    upgrade_block_savepoint(true, 2015031502, 'exacomp');
	}
	
	if ($oldversion < 2015032500) {
	
	    // Define field nostudents to be added to block_exacompsettings.
	    $table = new xmldb_table('block_exacompsettings');
	    $field = new xmldb_field('nostudents', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'filteredtaxonomies');
	
	    // Conditionally launch add field nostudents.
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
	
	    // Exacomp savepoint reached.
	    upgrade_block_savepoint(true, 2015032500, 'exacomp');
	}
	if($oldversion < 2015041500){
		 $table = new xmldb_table('block_exacompcrosssubjects');
		 $field = new xmldb_field('shared', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
		 
		//add field shared
	    if (!$dbman->field_exists($table, $field)) {
	        $dbman->add_field($table, $field);
	    }
		
		// Define table block_exacompcrosssubjects to be created.
	    $table = new xmldb_table('block_exacompcrossstud_mm');
	    
	    // Adding fields to table block_exacompdescrcross_mm.
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	    $table->add_field('crosssubjid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
	    $table->add_field('studentid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
	
	    // Adding keys to table block_exacompcdescrross_mm.
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	    $table->add_key('crosssubjid', XMLDB_KEY_FOREIGN, array('crosssubjid'), 'block_exacompcrosssubjects', array('id'));
	    $table->add_key('studentid', XMLDB_KEY_FOREIGN, array('studentid'), 'user', array('id'));
	    

	    // Conditionally launch create table for block_exacompdescrcros_mm.
	    if (!$dbman->table_exists($table)) {
	        $dbman->create_table($table);
	    }
	        // Exaport savepoint reached.
	    upgrade_block_savepoint(true, 2015041500, 'exacomp');
	}
	return $result;
}
