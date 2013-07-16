<?php
function xmldb_block_exacomp_install() {
	global $CFG;
	require_once $CFG->dirroot . '/blocks/exacomp/lib/xmllib.php';

	if(!file_exists($CFG->dirroot . '/blocks/exacomp/xml/exacomp_data.xml')) {
		$xmlserverurl = "https://raw.github.com/gtn/edustandards/master/austria/exacomp_data.xml";
		if($xmlserverurl) {
			$xml = file_get_contents($xmlserverurl);
			if($xml)
				file_put_contents($CFG->dirroot.'/blocks/exacomp/xml/exacomp_data.xml',$xml);
		}
	}
	block_exacomp_xml_do_import(null,1,1);
}