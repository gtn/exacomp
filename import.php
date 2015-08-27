<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__)."/inc.php";
require_once dirname(__FILE__)."/lib/xmllib.php";

$de = false;
$lang = current_language();
if(isset($lang) && substr( $lang, 0, 2) === 'de'){
    $de = true;
}

if($de)
    define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/deutsch/exacomp_data.xml');
else 
    define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/english/exacomp_data.xml');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_system::instance();
$course_context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_import';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/import.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

$isAdmin = has_capability('block/exacomp:admin', $context);
block_exacomp_require_teacher($context);

$action = optional_param('action', "", PARAM_ALPHA);
$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = optional_param('importtype', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

$mform = new block_exacomp_generalxml_upload_form();

$importSuccess = false;
$importException = null;

try {
    if (($importtype == 'custom') && $data = $mform->get_file_content('file')) {
        $importSuccess = block_exacomp_data_importer::do_import_string($data, block_exacomp::IMPORT_SOURCE_SPECIFIC);
    } elseif ($isAdmin && ($importtype == 'normal') && $data = $mform->get_file_content('file')) {
        $importSuccess = block_exacomp_data_importer::do_import_string($data, block_exacomp::IMPORT_SOURCE_DEFAULT);
    } elseif ($isAdmin && ($importtype == 'demo')) {
        //do demo import
        
        $file = optional_param('file', DEMO_XML_PATH, PARAM_TEXT);
        
        if (!file_exists($file)) {
            die('xml not found');
        } else {
            if ($importSuccess = block_exacomp_data_importer::do_import_file($file, block_exacomp::IMPORT_SOURCE_DEFAULT, true)) {
                block_exacomp_settstamp();
            }
        }
    }
} catch (block_exacomp_exception $importException) {
}

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

$delete = false;
if(($isAdmin || block_exacomp_check_customupload()) && $action == 'delete') {
        block_exacomp_data::delete_source(required_param('source', PARAM_INT));
		$delete = true;
}

// build tab navigation & print header
echo $PAGE->get_renderer('block_exacomp')->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($course_context,$courseid), $page_identifier);
/* CONTENT REGION */

/* Admins are allowed to import data, or a special capability for custom imports */
if($isAdmin || block_exacomp_check_customupload()) {
    
    if($importtype) {
        if(($importtype=='normal') || ($importtype=='custom')){
            if ($mform->is_cancelled()) {
                redirect($PAGE->url);
            } else {
                if ($data = $mform->get_file_content('file')) {
                    if($importSuccess) {
                            $string = get_string('next_step', 'block_exacomp');
                            $url = 'edit_config.php';
                        
                        $html = get_string("importsuccess", "block_exacomp").html_writer::empty_tag('br');
                        if($isAdmin)
                            $html .= html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
                            .html_writer::link(new moodle_url($url, array('courseid'=>$courseid, 'fromimport'=>1)), $string);
                        
                        echo $OUTPUT->box($html);
                    } else {
                        echo $PAGE->get_renderer('block_exacomp')->box_error($importException);
                        $mform->display();
                    }
                } else {
                    echo $OUTPUT->box(get_string("importinfo", "block_exacomp"));
                    if($isAdmin) echo $OUTPUT->box(get_string("importwebservice", "block_exacomp",new moodle_url("/admin/settings.php", array('section'=>'blocksettingexacomp'))));
            
                    $mform->display();
                }
            }
        } elseif (($importtype=='demo')){
            if($importSuccess){
                if(!$version) $string = get_string('next_step', 'block_exacomp');
                else $string = get_string('next_step_teacher', 'block_exacomp');
                            
                echo $OUTPUT->box(get_string("importsuccess", "block_exacomp").html_writer::empty_tag('br')
                    .html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
                    .html_writer::link(new moodle_url('edit_config.php', array('courseid'=>$courseid, 'fromimport'=>1)), $string));
            }else{
                echo $OUTPUT->box(get_string("importfail", "block_exacomp"));
            }
        }
    } else {

        if (block_exacomp_data::has_old_data(block_exacomp::IMPORT_SOURCE_DEFAULT)) {
            if (!$isAdmin) {
                print_error('pls contact your admin');
            }
            
            echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), 'For the latest exacomp version you need to reimport global educational standards'));
        }
        elseif (block_exacomp_data::has_old_data(block_exacomp::IMPORT_SOURCE_SPECIFIC)) {
            if (!$isAdmin) {
                print_error('pls contact your admin');
            }
        
            echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'custom')), 'For the latest exacomp version you need to reimport school/company specific standards'));
        } else {
            $hasData = block_exacomp_data::has_data();
            
            if($delete)
                echo $OUTPUT->box(get_string("delete_success", "block_exacomp"));
            
            if ($isAdmin) {
                if ($hasData){
                    echo $OUTPUT->box(get_string("importdone", "block_exacomp"));
                    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport_again', 'block_exacomp')));

                    // custom import only of there is no old data anymore
                    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'custom')), get_string('doimport_own', 'block_exacomp')));
                } else {
                    // no data yet, allow import or import demo data
                    echo $OUTPUT->box(html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('first_configuration_step', 'block_exacomp'));
                    echo $OUTPUT->box(get_string("importpending", "block_exacomp"));
                    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport', 'block_exacomp')));
                    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'demo')), get_string('do_demo_import', 'block_exacomp')));
                }
            }
    
            // export
            if($hasData) {
                echo '<hr />';
                echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action'=>'export_all', 'courseid'=>$courseid)), 'Alle Kompetenzraster dieser Moodle Instanz exportieren'));
                echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action'=>'select', 'courseid'=>$courseid)), 'Selektiver Export'));
            }
            
            
            if ($isAdmin) {
                echo '<hr />';
                echo $PAGE->get_renderer('block_exacomp')->print_sources();
            }
        }
    }
}

/* END CONTENT REGION */
echo $PAGE->get_renderer('block_exacomp')->footer();
