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
	return $result;
}

?>