<?php
use block_exacomp\data;
use block_exacomp\data_importer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class generalxml_upload_form extends moodleform {

    protected $_confirmationData = null;

    public function setConfirmationData($data = null) {
        $this->_definition_finalized = false; // activate new form elements
        $this->_confirmationData = $data;
    }

    function definition() {
        $mform = &$this->_form;

        $importtype = optional_param('importtype', 'normal', PARAM_TEXT);

        $this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
        $this->_form->_attributes['class'] = "mform exacomp_import";
        $check = data::has_data();

        if ($importtype == 'custom') {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport_own"));
        } else if ($importtype == 'scheduler') {
            $mform->addElement('header', 'comment', block_exacomp_get_string("scheduler_import_settings"));
        } else if ($check) {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport"));
        } else {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport_again"));
        }

        if ($importtype != 'scheduler') {
            $mform->addElement('filepicker', 'file', block_exacomp_get_string("file"), null);
            $mform->addRule('file', null, 'required', null, 'client');
            if (!data_importer::isTheTeacherImporting()) {
                // Teacher importing has preselected course always (see below hidden input)
                $mform->addElement('static', 'destination_text', '', block_exacomp_get_string("dest_course"));
                $mform->addElement('select', 'template', block_exacomp_get_string("choosecoursetemplate"), ['' => ''] + block_exacomp_get_course_names());
            }
        }
        if (data_importer::isTheTeacherImporting()) {
            // Add current course into 'template' for imported moodle activities
            $courseid = required_param('courseid', PARAM_INT);
            $mform->addElement('hidden', 'template', $courseid);
        }

        $mform->addElement('text', 'password', block_exacomp_trans([
                'de:Passwort der Zip-Datei',
                'en:Zip-File Password',
            ]) . ':');
        $mform->setType('password', PARAM_TEXT);
    }

    function definition_after_data() {
        global $CFG, $DB;
        //parent::definition_after_data();
        $forSchedulerTask = false;
        $mform =& $this->_form;

        if ($this->_confirmationData && is_array($this->_confirmationData)) {
            $data = $this->_confirmationData;
            if (isset($data['forSchedulerTask']) && $data['forSchedulerTask'] == true) {
                $forSchedulerTask = true;
            }
            switch ($data['result']) {
                case 'compareCategories':

                    $mform->addElement('html', '<input type="hidden" name="currentImportStep" value="compareCategories">');
                    $categoryMapping = data_importer::get_categorymapping_for_source($data['sourceId'], $forSchedulerTask);
                    // input form for comparing categories
                    $difflevels = block_exacomp_get_assessment_diffLevel_options_splitted();
                    $difflevels = array_combine($difflevels, $difflevels);
                    $difflevels = array(
                            '--as_is--' => block_exacomp_get_string('import_mapping_as_is'),
                            '--delete--' => block_exacomp_get_string('import_mapping_delete'),
                        ) + $difflevels;

                    $mform->addElement('html', '<table class="table">
                                                <thead><tr>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_xml") . '</td>
                                                    <td></td>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_exacomp") . '</td>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_level") . '</td>
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
                        $select = $mform->addElement('select', 'changeTo[' . $catId . ']', null, $difflevels);
                        if ($categoryMapping && array_key_exists($catId, $categoryMapping)) {
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
                    require_once(__DIR__ . '/import_selectgrid_checkbox.php');
                    MoodleQuickForm::registerElementType('import_selectgrid_checkbox', $CFG->dirroot . '/blocks/exacomp/classes/import_selectgrid_checkbox.php', 'block_exacomp_import_extraconfigcheckbox');
                    $currentAllGrids = block_exacomp\data_importer::get_selectedallgrids_for_source($data['sourceId'], $forSchedulerTask);
                    $mform->addElement('html', '<input type="hidden" name="currentImportStep" value="selectGrids">');
                    // if used preselected values - message
                    $currentSelected = block_exacomp\data_importer::get_selectedgrids_for_source($data['sourceId'], $forSchedulerTask);
                    if ($currentSelected || $currentAllGrids == 1) {
                        // additional condition: at least one subject is from this source
                        $subjectExisting = $DB->get_records('block_exacompsubjects', ['source' => $data['sourceId']]);
                        if ($subjectExisting) {
                            $mform->addElement('html', '<div class="alert alert-info small">' .
                                block_exacomp_get_string('import_used_preselected_from_previous') . '</div>');
                        }
                    }
                    // import all subjects
                    $params = array('class' => 'import-all-subjects');
                    if ($currentAllGrids == 1) {
                        $params['checked'] = 'checked';
                    }
                    $mform->addElement('html', '<strong>');
                    $mform->addElement('import_selectgrid_checkbox', 'selectedGridAll', '', block_exacomp_get_string('importtask_all_subjects'), $params);
                    $mform->addElement('html', '</strong>');
                    $mform->addElement('html', '<div id="import-subjects-list">');
                    // Add select/deselect buttons
                    $buttons = '<div><small>' .
                        '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="1">' . block_exacomp_get_string('select_all') . '</a>&nbsp;/&nbsp;' .
                        '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="0">' . block_exacomp_get_string('deselect_all') . '</a>' .
                        '</small></div>';
                    $currPath = '';
                    $mform->addElement('html', $buttons . '<ul class="exacomp_import_grids_list">');
                    $pathIndex = 0;
                    foreach ($data['list'] as $subjid => $subject) {
                        $path = (string)$subject->pathname;
                        if ($currPath != $path) {
                            $pathIndex++;
                            $mform->addElement('html', '</ul>');
                            $buttons = '<small>' .
                                '<a class="exacomp_import_select_sublist" data-targetList="' . $pathIndex . '" data-selected="1">' . block_exacomp_get_string('select_all') . '</a>&nbsp;/&nbsp;' .
                                '<a class="exacomp_import_select_sublist" data-targetList="' . $pathIndex . '" data-selected="0">' . block_exacomp_get_string('deselect_all') . '</a>' .
                                '</small>';
                            $mform->addElement('html', '<h4>' . $path . '&nbsp;' . $buttons . '</h4>' . "\r\n");
                            $mform->addElement('html', '<ul class="exacomp_import_grids_list" data-pathIndex="' . $pathIndex . '">');
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
                        $mform->addElement('import_selectgrid_checkbox', 'selectedGrid[' . $subjid . ']', $addText, $subject->title, $params);
                        $mform->addElement('html', '</li>');
                    }
                    $mform->addElement('html', '</ul>');
                    $mform->addElement('html', '</div');
                    break;
                case 'selectSchooltype':
                    // input form for comparing grid <-> schooltype
                    $mform->addElement('html', '<input type="hidden" name="currentImportStep" value="selectSchooltype">');

                    $schooltypeMapping = data_importer::get_schooltypemapping_for_source($data['sourceId'], $forSchedulerTask);

                    // get the only enabled school types
                    $schoolTypesTree = data_importer::getSchoolTypesTreeForTeacherImporting();

                    $schooltypesSelect = function($gridId, $current = null, $forAll = false) use ($schoolTypesTree) {
                        $select = '<select name="changeTo['.$gridId.']" class="exacomp-schooltype-grid-mapper'.($forAll ? '-for-all' : '').'">';
                        if ($forAll) {
                            $select .= '<option value="0" >' . block_exacomp_get_string('import_schooltype_mapping_for_all') . '</option>';
                        }
                        foreach ($schoolTypesTree as $levelKey => $levelData) {
                            $select .= '<optgroup label="' . htmlspecialchars($levelData['leveltitle']) . '">';
                            foreach ($levelData['schooltypes'] as $schooltype) {
                                $selected = '';
                                if ($current == $schooltype->id) {
                                    $selected = ' selected="selected" ';
                                }
                                $select .= '<option value="' . htmlspecialchars($schooltype->id) . '" '.$selected.'>' . htmlspecialchars($schooltype->title) . '</option>';
                            }
                            $select .= '</optgroup>';
                        }
                        $select .= '</select>';

                        return $select;
                    };

                    // the table with imported grids and proposition to choose the school type
                    $mform->addElement('html', '<table class="table">
                                                <thead><tr>
                                                    <td>' . block_exacomp_get_string("import_schooltype_mapping_column_grid") . '</td>
                                                    <td></td>
                                                    <td>' . block_exacomp_get_string("import_schooltype_mapping_column_schooltype") . '</td>
                                                </tr></thead><tbody>');
                    if ($data['list'] && count($data['list']) > 1) {
                        $mform->addElement('html', '<tr>');
                        $mform->addElement('html', '<td colspan="2"></td>');
                        $mform->addElement('html', '<td>');
                        // use for all selectbox:
                        $mform->addElement('html', $schooltypesSelect(0, null, true));
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '</tr>');
                    }
                    foreach ($data['list'] as $gridId => $gridTitle) {
                        $mform->addElement('html', '<tr><td>'.$gridTitle.'</td>');
                        $catId = intval($gridId);
                        $mform->addElement('html', '<td>&nbsp;&nbsp;&nbsp;</td>');
                        $mform->addElement('html', '<td>');
                        $current = null;
                        if ($schooltypeMapping && array_key_exists($gridId, $schooltypeMapping)) {
                            $current = @$schooltypeMapping[$gridId];
                        }
                        $select = $schooltypesSelect($gridId, $current);
                        $mform->addElement('html', $select);
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '</tr>');
                    }
                    $mform->addElement('html', '</tbody></table>');
                    break;
            }

            // hide general fields if it is a step of importing
            if (@$data['result']) {
                // file
                $fileElement = $mform->getElement('file');
                $currFileValue = $fileElement->_attributes['value'];
                $mform->removeElement('file');
                $mform->addElement('hidden', 'file');
                $mform->setType('file', PARAM_INT);
                $mform->setDefault('file', $currFileValue);
                // destination_text
                $mform->removeElement('destination_text');
                // course template
                $templateElement = $mform->getElement('template');
                $values = @$templateElement->_values;
                if ($values) {
                    $currTemplateValue = reset($values); // only one template!
                } else {
                    $currTemplateValue = '';
                }
                $mform->removeElement('template');
                $mform->addElement('hidden', 'template');
                $mform->setType('template', PARAM_INT);
                $mform->setDefault('template', $currTemplateValue);
                // zip password
                $passwordElement = $mform->getElement('password');
                $currPasswordValue = $passwordElement->_attributes['value'];
                $mform->removeElement('password');
                $mform->addElement('hidden', 'password');
                $mform->setType('password', PARAM_TEXT);
                $mform->setDefault('password', $currPasswordValue);
            }
        }

        if ($forSchedulerTask) {
            $this->add_action_buttons(false, block_exacomp_get_string('save')); // settings of scheduler importing
        } else {
            $this->add_action_buttons(false, block_exacomp_get_string('add')); // we need buttons to bottom. so it is here. usual importing
        }

    }

    function is_validated() {
        $this->_definition_finalized = true; // disable start of  definition_after_data()
        $result = parent::is_validated();
        $this->_definition_finalized = false; // activate new form elements
        return $result;
    }

}
