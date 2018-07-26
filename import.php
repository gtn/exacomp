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

require __DIR__.'/inc.php';

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
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

$isAdmin = has_capability('block/exacomp:admin', $context);
block_exacomp_require_teacher($context);

$action = optional_param('action', "", PARAM_ALPHA);
$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = optional_param('importtype', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

require_once $CFG->libdir . '/formslib.php';


class generalxml_upload_form extends \moodleform {

    protected $_confirmationData = null;

    public function setConfirmationData($data = null) {
        $this->_definition_finalized = false; // activate new form elements
        $this->_confirmationData = $data;
    }

	function definition() {
		$mform = & $this->_form;

		$importtype = optional_param('importtype', 'normal', PARAM_TEXT);

		$this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
		$this->_form->_attributes['class'] = "mform exacomp_import";
		$check = \block_exacomp\data::has_data();
		if($importtype == 'custom') {
			$mform->addElement('header', 'comment', block_exacomp_get_string("doimport_own"));
		}
		elseif($check){
			$mform->addElement('header', 'comment', block_exacomp_get_string("doimport"));
		} else
			$mform->addElement('header', 'comment', block_exacomp_get_string("doimport_again"));

		$mform->addElement('filepicker', 'file', block_exacomp_get_string("file"),null);
		$mform->addRule('file', null, 'required', null, 'client');

	}

	function definition_after_data() {
        global $CFG;
        //parent::definition_after_data();
        $mform =& $this->_form;
        if ($this->_confirmationData && is_array($this->_confirmationData)) {
            $data = $this->_confirmationData;
            switch ($data['result']) {
                case 'compareCategories':
                    $categoryMapping = \block_exacomp\data_importer::get_categorymapping_for_source($data['sourceId']);
                    // input form for comparing categories
                    //print_r(block_exacomp_get_assessment_diffLevel_options());
                    $difflevels = preg_split( "/[\s*,\s*]*,+[\s*,\s*]*/", block_exacomp_get_assessment_diffLevel_options());
                    $difflevels = array_combine($difflevels, $difflevels);

                    $mform->addElement('html', '<table class="table">
                                                <thead><tr>
                                                    <td>'.block_exacomp_get_string("import_category_mapping_column_xml").'</td>
                                                    <td></td>
                                                    <td>'.block_exacomp_get_string("import_category_mapping_column_exacomp").'</td>
                                                    <td>'.block_exacomp_get_string("import_category_mapping_column_level").'</td>
                                                </tr></thead><tbody>');
                    $mform->addElement('html', '');
                    $mform->addElement('html', '');
                    $mform->addElement('html', '');

                    foreach ($data['list'] as $catItem) {
                        $catId = intval($catItem->attributes()->id);
                        $mform->addElement('html', '<tr>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', $catItem->title);
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', '&nbsp;&rarr;&nbsp;');
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $select = $mform->addElement('select', 'changeTo['.$catId.']', null, $difflevels);
                        if (array_key_exists($catId, $categoryMapping)) {
                            $select->setSelected($categoryMapping[$catId]);
                        }
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', ($catItem->lvl == 5 ? block_exacomp_get_string("import_category_mapping_column_level_descriptor") : block_exacomp_get_string("import_category_mapping_column_level_example")));
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '</tr>');
                    }
                    $mform->addElement('html', '</tbody></table>');
                    break;
                case 'selectGrids':
                    require_once(__DIR__.'/classes/import_selectgrid_checkbox.php');
                    MoodleQuickForm::registerElementType('import_selectgrid_checkbox', $CFG->dirroot.'/blocks/exacomp/classes/import_selectgrid_checkbox.php', 'block_exacomp_import_extraconfigcheckbox');
                    // if used preselected values - message
                    $currentSelected = block_exacomp\data_importer::get_selectedgrids_for_source($data['sourceId']);
                    if ($currentSelected) {
                        $mform->addElement('html', '<div class="alert alert-info small">'.block_exacomp_get_string('import_used_preselected_from_previous').'</div>');
                    }
                    // Add select/deselect buttons
                    $buttons = '<div><small>'.
                            '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="1">'.block_exacomp_get_string('select_all').'</a>&nbsp;/&nbsp;'.
                            '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="0">'.block_exacomp_get_string('deselect_all').'</a>'.
                            '</small></div>';
                    $currPath = '';
                    $mform->addElement('html', $buttons.'<ul class="exacomp_import_grids_list">');
                    $pathIndex = 0;
                    foreach ($data['list'] as $subjid => $subject) {
                        $path = (string)$subject->pathname;
                        if ($currPath != $path) {
                            $pathIndex++;
                            $mform->addElement('html', '</ul>');
                            $buttons = '<small>'.
                                        '<a class="exacomp_import_select_sublist" data-targetList="'.$pathIndex.'" data-selected="1">'.block_exacomp_get_string('select_all').'</a>&nbsp;/&nbsp;'.
                                        '<a class="exacomp_import_select_sublist" data-targetList="'.$pathIndex.'" data-selected="0">'.block_exacomp_get_string('deselect_all').'</a>'.
                                        '</small>';
                            $mform->addElement('html', '<h4>'.$path.'&nbsp;'.$buttons.'</h4>'."\r\n");
                            $mform->addElement('html', '<ul class="exacomp_import_grids_list" data-pathIndex="'.$pathIndex.'">');
                            $currPath = $subject->pathname;
                        }
                        $mform->addElement('html', '<li>');
                        $params = array();
                        if ($subject->selected) {
                            $params['checked'] = 'checked';
                        }
                        $addText = '';
                        if ($subject->newForSelected) {
                            $addText = block_exacomp_get_string('new');
                        }
                        $mform->addElement('import_selectgrid_checkbox', 'selectedGrid['.$subjid.']', $addText, $subject->title, $params);
                        $mform->addElement('html', '</li>');
                    }
                    $mform->addElement('html', '</ul>');
                    break;
            }

        }
        $this->add_action_buttons(false, block_exacomp_get_string('add')); // we need buttons to bottom. so it is here

    }

    function is_validated() {
        $this->_definition_finalized = true; // disable start of  definition_after_data()
        $result = parent::is_validated();
        $this->_definition_finalized = false; // activate new form elements
        return $result;
    }

}

$mform = new generalxml_upload_form();

$importSuccess = false;
$importException = null;

\block_exacomp\data::prepare();

try {
    // check category renaming
	if (($importtype == 'custom') && $data = $mform->get_file_content('file')) {
		$importSuccess = block_exacomp\data_importer::do_import_string($data, BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC);
	} elseif ($isAdmin && ($importtype == 'normal') && $data = $mform->get_file_content('file')) {
		$importSuccess = block_exacomp\data_importer::do_import_string($data, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT);
	} elseif ($isAdmin && ($importtype == 'demo')) {
		//do demo import
		
		// TODO: catch exception
		$file = optional_param('file', DEMO_XML_PATH, PARAM_TEXT);
		if ($importSuccess = block_exacomp\data_importer::do_import_url($file, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT)) {
			block_exacomp_settstamp();
		}
	}
	
	if ($importSuccess) {
		\block_exacomp\event\import_completed::log(['objectid' => $courseid, 'courseid' => $courseid]);
	}

} catch (block_exacomp\import_exception $importException) {
}

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
$pagenode->make_active();

$delete = false;
if(($isAdmin || block_exacomp_check_customupload()) && $action == 'delete') {
		block_exacomp\data::delete_source(required_param('source', PARAM_INT));
		$delete = true;
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);
/* CONTENT REGION */

/* Admins are allowed to import data, or a special capability for custom imports */
if($isAdmin || block_exacomp_check_customupload()) {
	
	if ($importtype) {
	    switch ($importtype) {
            case 'normal':
            case 'custom':
                if ($mform->is_cancelled()) {
                    redirect($PAGE->url);
                } else {
                    if ($data = $mform->get_file_content('file')) {
                        if ($importSuccess) {
                            if ($importSuccess === true) {
                                $string = block_exacomp_get_string('next_step');
                                $url = 'edit_config.php';

                                $html = block_exacomp_get_string("importsuccess").html_writer::empty_tag('br');
                                if ($isAdmin) {
                                    $html .= html_writer::empty_tag('img', array(
                                                    'src' => new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt' => '',
                                                    'width' => '60px',
                                                    'height' => '60px'))
                                            .html_writer::link(new moodle_url($url,
                                                    array('courseid' => $courseid, 'fromimport' => 1)),
                                                    $string);
                                }

                                echo $OUTPUT->box($html);
                            } else if (is_array($importSuccess)){
                                // no errors for now, but the user needs to configure importing
                                switch ($importSuccess['result']) {
                                    case 'compareCategories':
                                        $html = block_exacomp_get_string("import_category_mapping_needed");
                                        break;
                                    case 'selectGrids':
                                        $html = block_exacomp_get_string("import_category_selectgrids_needed");
                                        break;
                                }
                                echo $OUTPUT->box($html, 'alert alert-warning');
                                $mform->setConfirmationData($importSuccess);
                                $mform->display();
                            }
                        } else {
                            echo block_exacomp_get_renderer()->box_error($importException);
                            $mform->display();
                        }
                    } else {
                        echo $OUTPUT->box(block_exacomp_get_string("importinfo"));
                        if ($isAdmin) {
                            echo $OUTPUT->box(block_exacomp_get_string("importwebservice", null,
                                    (string) new moodle_url("/admin/settings.php", array('section' => 'blocksettingexacomp'))));
                        }
                        @set_time_limit(0);
                        $max_execution_time = (int)ini_get('max_execution_time');
                        if ($max_execution_time && $max_execution_time < 60*5) {
                            echo '<h3>'.block_exacomp_get_string("import_max_execution_time", null, $max_execution_time).'</h3>';
                        }

                        $mform->display();
                    }
                }
                break;
            case 'demo':
                if($importSuccess){
                    $string = block_exacomp_get_string('next_step');

                    echo $OUTPUT->box(block_exacomp_get_string("importsuccess").html_writer::empty_tag('br')
                        .html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
                        .html_writer::link(new moodle_url('edit_config.php', array('courseid'=>$courseid, 'fromimport'=>1)), $string));
                }else{
                    echo $OUTPUT->box(block_exacomp_get_string("importfail"));
                    echo block_exacomp_get_renderer()->box_error($importException);
                }
                break;
		} // switch
	} else {

		if (block_exacomp\data::has_old_data(BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT)) {
			if (!$isAdmin) {
				print_error('pls contact your admin');
			}
			
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                                                array('courseid'=>$courseid,
                                                        'importtype'=>'normal')),
                                                'For the latest exacomp version you need to reimport global educational standards'));
		} elseif (block_exacomp\data::has_old_data(BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC)) {
			if (!$isAdmin) {
				print_error('pls contact your admin');
			}
		
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                                                array('courseid'=>$courseid,
                                                        'importtype'=>'custom')),
                                                'For the latest exacomp version you need to reimport school/company specific standards'));
		} else {
			$hasData = block_exacomp\data::has_data();
			
			if ($delete) {
                echo $OUTPUT->box(block_exacomp_get_string("delete_success"));
            }
			if ($isAdmin) {
				if ($hasData) {
					echo $OUTPUT->box(block_exacomp_get_string("importdone"));
					echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                                                        array('courseid'=>$courseid,
                                                                'importtype'=>'normal')),
                                                        block_exacomp_get_string('doimport_again')));
				} else {
					// no data yet, allow import or import demo data
					echo $OUTPUT->box(html_writer::empty_tag('img',
                                        array('src' => new moodle_url('/blocks/exacomp/pix/one_admin.png'),
                                                'alt'=>'',
                                                'width'=>'60px',
                                                'height'=>'60px')
                                  ).block_exacomp_get_string('first_configuration_step'));
					echo $OUTPUT->box(block_exacomp_get_string("importpending"));
					echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                                        array('courseid'=>$courseid,
                                                'importtype'=>'normal')),
                                        block_exacomp_get_string('doimport')));
					//echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'demo')), block_exacomp_get_string('do_demo_import')));
				}
			}
	
			// export
			if($hasData) {
				echo '<hr />';
				echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action'=>'export_all', 'courseid'=>$courseid)), block_exacomp_get_string("export_all_standards")));
				echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action'=>'select', 'courseid'=>$courseid)), block_exacomp_get_string("export_selective")));
			}
			
			
			if ($isAdmin) {
				echo '<hr />';
				echo $output->sources();
			}
		}
	}
}

/* END CONTENT REGION */
echo $output->footer();

