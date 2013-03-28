<?php
function xmldb_block_exacomp_install() {
	global $CFG;
	require_once $CFG->dirroot . '/blocks/exacomp/lib/xmllib.php';
	
	block_exacomp_xml_do_import(null,1,1);
}