<?php
require_once 'inc.php';
require_once('lib/lib.php');
require_once('lib/xmllib.php');

$courseid = required_param('courseid', PARAM_INT);
$schooltypes = explode(',', required_param('schooltypes', PARAM_TAGLIST));
$xmlname = required_param('xmlname', PARAM_URL);

global $DB;

$data = file_get_contents($xmlname);
$success = block_exacomp_xml_do_import($data);
foreach($schooltypes as &$schooltype)
	$schooltype = $DB->get_field('block_exacompschooltypes','id',array('sourceid'=>$schooltype));
block_exacomp_set_mdltype($schooltypes,$courseid);