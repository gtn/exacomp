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
	if ($oldversion < 2012100402) {
		$table = new xmldb_table('block_exacompedulevels');
		$field = new xmldb_field('source');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		$field = new xmldb_field('sourceid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		$table = new xmldb_table('block_exacompschooltypes');
		$field = new xmldb_field('source');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		$field = new xmldb_field('sourceid');
		$field->set_attributes(XMLDB_TYPE_INTEGER, 20, XMLDB_UNSIGNED, null, null, 0, null);

		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	}
	
	return $result;
}

?>