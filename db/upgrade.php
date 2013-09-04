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
        $table->add_field('title', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
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
	
	return $result;
}
