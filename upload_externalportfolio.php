<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/* this file is used for elove app to save items in mahara */

defined('MOODLE_INTERNAL') || die();
@$filename && @$CFG || die();

$file_params = array();
$file_params['component'] = 'user';
$file_params['filearea'] = 'private';
$file_params['filename'] = $filename;
$file_params['filepath'] = '/';
$file_params['itemid'] = 0;

if (!file_exists($CFG->dirroot . '/mnet/xmlrpc/client.php')) {
    die();
}

require_once($CFG->dirroot . '/mnet/xmlrpc/client.php');

// Request for token
// ���� ����� �� ���� �����������. ������ ��� ������� ������������ ��������. ����� ���������� !!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
$client = new mnet_xmlrpc_client();
$client->set_method('portfolio/mahara/lib.php/send_content_intent');
// get user from exaport token
// $user_id = $DB->get_record('user', array('id' => $USER->id));
$client->add_param($USER->username);
// Get Mahara portfolio plugin config
// Or from url: portinstanceid; or form table 'portfolio_instance' by plugin name 'mahara'
$portfolioinstanceid = optional_param('pfinstid', 0, PARAM_INT); // The instance of configured portfolio plugin.
if ($portfolioinstanceid > 0) {
    //
} else {
    $temp_arr = $DB->get_record('portfolio_instance', array('plugin' => 'mahara', 'visible' => 1), '*', IGNORE_MULTIPLE); // ONLY ONE RECORD !!!!
    $portfolioinstanceid = $temp_arr->id;
}
// Create array of plugin params
if (isset($portfolioinstanceid)) {
    $portfolioinstanceconf = array();
    $temp_arr = $DB->get_record('portfolio_instance_config', array('instance' => $portfolioinstanceid, 'name' => 'mnethostid'));
    $portfolioinstanceconf['mnet_host'] = $temp_arr->value;
}
// mnet_host for portfolio plugin
$hostrecord = $DB->get_record('mnet_host', array('id' => $portfolioinstanceconf['mnet_host']));  // Mahara MNET HOST
// mnethost object for connect
$mnethost = new mnet_peer();
$mnethost->set_wwwroot($hostrecord->wwwroot);

// Get token from host
if (!$client->send($mnethost)) {
    foreach ($client->error as $errormessage) {
        list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
        $message .= "ERROR $code:<br/>$errormessage<br/>";
    }
    $message .= print_r($message, true);
}
// we should get back... the send type and a shared token
$response = (object) $client->response;

// ����� � ����������� - ��� UNZIP
// $result_querystring = $message .  print_r($mnethost, true);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// ����� ���������, ����� ����������� ��� ��� � ������ !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//return array("message"=>$response->token);
if (empty($response->sendtype) || empty($response->token)) {
    throw new portfolio_export_exception($this->get('exporter'), 'senddisallowed', 'portfolio_mahara');
}
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

// token received
$portfoliotoken = $response->token;

// file transfer request.
$client = new mnet_xmlrpc_client();
$client->set_method('portfolio/mahara/lib.php/send_content_ready');
$client->add_param($portfoliotoken);
$client->add_param($USER->username);
// resolve format: file
$client->add_param('file');

$fs = get_file_storage();

// Export one file
// Need to create fake exporter class and insert this to database. So we shoud work with Mahara
require_once($CFG->libdir . '/portfoliolib.php');
require_once($CFG->libdir . '/portfolio/plugin.php');
require_once($CFG->libdir . '/portfolio/exporter.php');
require_once($CFG->dirroot . '/mnet/lib.php');
require_once("$CFG->dirroot/blocks/exaport/locallib.php");

// insert record to the 'portfolio_tempdata'. Get ID, later we will change some data in this record
$r = (object) array(
    'expirytime' => time() + (60 * 60 * 24),
    'userid' => $USER->id,
    'instance' => $portfolioinstanceid,
);
$exporter_id = $DB->insert_record('portfolio_tempdata', $r);

// fileID
$context = context_user::instance($USER->id);
$filefromdatabase = $fs->get_file($context->id, $file_params['component'], $file_params['filearea'],
    $file_params['itemid'], $file_params['filepath'], $file_params['filename']);
$filename = $filefromdatabase->get_filename();
// Create copy of exporting file with other params (temporary)
$file_params = array(
    'contextid' => 1, //SYSCONTEXTID,
    'component' => 'portfolio',
    'filearea' => 'exporter',
    'itemid' => $exporter_id,
    'filepath' => '/',
    'filename' => $filename,
);
$filecopy = $fs->create_file_from_storedfile($file_params, $filefromdatabase->get_id());
$totalsize = 0;
// get files manifest
$filesmanifest[$filecopy->get_contenthash()] = array(
    'filename' => $filecopy->get_filename(),
    'sha1' => $filecopy->get_contenthash(),
    'size' => $filecopy->get_filesize(),
);
$totalsize += $filecopy->get_filesize();
// create zip for transfer. Mahara will be unzip uploaded file. So need zipped file
$zipper = new zip_packer();
if ($newfile = $zipper->archive_to_storage(
    array($filecopy->get_filepath() . $filecopy->get_filename() => $filecopy),
    1, //$contextid,
    'portfolio', //$component,
    'exporter', //$filearea,
    $exporter_id, //$itemid,
    '/final/', //$filepath,
    'portfolio-export.zip', //$filename,
    $USER->id //$this->user->id
)) {
    $zipfile = $newfile;
}

// next lines will emulate portfolio instance and exporter. This objects need to store in the database.
// Mahara will connect to Moodle for a file. And table 'portfolio_tempdata' need to store this objects
$instance = portfolio_instance($portfolioinstanceid);
$instance->set('file', $zipfile);
// export settings
$export_congif = array(
    'format' => 'file',
);
$instance->set_export_config($export_congif);

// emulate a caller object
$callbackargs = array();
$caller = new exaport_portfolio_caller($callbackargs);
$caller->set('user', $USER);
//$tid = $zipfile->get_id();
$tid = $filecopy->get_id();
$caller->set('fileid', $tid);

// emulate an exporter object
$callbackcomponent = 'block_exaport';
$emulate_exporter = new portfolio_exporter($instance, $caller, $callbackcomponent);
$emulate_exporter->singlefile = $filecopy;
$emulate_exporter->set('user', $USER);

// update portfolio_tempdata record with true emulate object ($emulate_exporter)
$r = (object) array(
    'id' => $exporter_id,
    'data' => base64_encode(serialize($emulate_exporter)),
);
$exporter_id_update = $DB->update_record('portfolio_tempdata', $r);

// Create record in 'portfolio_mahara_queue' - Mahara will be find there token and id of portfolio_tempdata
$r = (object) array(
    'transferid' => $exporter_id,
    'token' => $portfoliotoken,
);
$queue_id = $DB->insert_record('portfolio_mahara_queue', $r);

$client->add_param(array(
    'filesmanifest' => $filesmanifest,
    'zipfilesha1' => $zipfile->get_contenthash(),
    'zipfilesize' => $zipfile->get_filesize(),
    'totalsize' => $totalsize,
));
// wait or not  0 - wait; 1 - do not wait
$client->add_param(1);

// This params do not need, but they are. Reset these
$client->mnet->keypair = array();

// Send general request to Mahara
if (!$client->send($mnethost)) {
    foreach ($client->error as $errormessage) {
        list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
        $message .= "  ERROR $code:<br/>$errormessage<br/> ";
    }
    echo $message;
}
// we should get back...  an ok and a status
// either we've been waiting a while and mahara has fetched the file or has queued it.
$response = (object) $client->response;
if (!$response->status) {
    echo 'failed to ping!!!!!!!!!!!!';
}
if ($response->type == 'queued') {
    echo 'WAS QUEUED ! Really this is error for us!';
}
$result_querystring = '';
if (isset($response->querystring)) {
    $result_querystring = $hostrecord->wwwroot . '/artefact/file/download.php' . $response->querystring;
    $maharaexport_success = true;
} else {
    $result_querystring = 'ERROR: no result link to uploaded file';
    $maharaexport_success = false;
}
// Clear temporary data
$DB->delete_records('portfolio_mahara_queue', array('transferid' => $exporter_id));
$DB->delete_records('portfolio_log', array('tempdataid' => $exporter_id));

//print_r($result_querystring);
/**/
