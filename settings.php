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

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/lib/exabis_special_id_generator.php';
require_once __DIR__.'/inc.php';

if (!class_exists('block_exacomp_admin_setting_source')) {
	// check needed, because moodle includes this file twice

	class block_exacomp_admin_setting_source extends admin_setting_configtext {
		public function validate($data) {
			$ret = parent::validate($data);
			if ($ret !== true) {
				return $ret;
			}
			
			if (empty($data)) {
				// no id -> id must always be set
				return false;
			}
			if (exabis_special_id_generator::validate_id($data)) {
				return true;
			} else {
				return 'wrong id';
				// return block_exacomp_get_string('validateerror', 'admin');
			}
		}
	}
	
	class block_exacomp_admin_setting_scheme extends admin_setting_configselect {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			block_exacomp_update_evaluation_niveau_tables();

			return '';
		}
	}
	
	class block_exacomp_grading_schema extends admin_setting_configselect {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			block_exacomp_update_evaluation_niveau_tables();

			return '';
		}
	}
	
	
	class admin_setting_configcheckbox_grading extends admin_setting_configcheckbox {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
			 
			if($data != '0'){
				//ensure that value is 0-4 which is needed for new grading scheme
				foreach(block_exacomp_get_courseids() as $course){
					$course_settings = block_exacomp_get_settings_by_course($course);
					if($course_settings->grading != 3){ //change course grading
						$course_settings->grading = 3;
						$course_settings->filteredtaxonomies = json_encode($course_settings->filteredtaxonomies);
						block_exacomp_save_coursesettings($course, $course_settings);
					}
					
					//map subject, topic, crosssubject, descriptor grading to grade
					block_exacomp_map_value_to_grading($course);
				}
			}
			return '';
		}
	}

	// Table with Evaluation settings
    class block_exacomp_grading_configtable extends admin_setting {

	    private $targets = array('example', 'childcompetence', 'competence', 'topic', 'subject', 'crosssubject');
	    private $params = array('schema', 'useDifficultylevel', 'useStudentSelfEvaluation');
	    private $schemascount = 3;

        public function __construct($name, $visiblename, $description, $defaultsetting) {
            // Default grading settings.
            if ($defaultsetting == '') {
                $gradingdefault = array();
                foreach ($this->targets as $target) {
                    foreach ($this->params as $param) {
                        switch ($param) {
                            case 'schema': $value = 0; break;
                            case 'useDifficultylevel': $value = 1; break;
                            case 'useStudentSelfEvaluation': $value = 1; break;
                            default: $value = 1; break;
                        }
                        $gradingdefault[$target][$param] = $value;
                    }
                }
            }
            parent::__construct($name, $visiblename, $description, $gradingdefault);
        }

        public function get_setting() {
            $result = array();
            foreach ($this->targets as $target) {
                foreach ($this->params as $param) {
                    $targetparam = 'grading_'.$target.'_'.$param;
                    $value = $this->config_read($targetparam);
                    if ($value !== null) {
                        $result[$target][$param] = $value;
                    } else {
                        $result[$target][$param] = $this->defaultsetting[$target][$param];
                    };
                }
            }
            return $result;
        }

        public function write_setting($data) {
            if(!is_array($data)) {
                $data = $this->defaultsetting;
            }
            $result = '';
            foreach ($data as $target => $parameters) {
                foreach ($parameters as $param => $value) {
                    if (!$this->config_write('grading_'.$target.'_'.$param, trim($value))) {
                        $result = get_string('errorsetting', 'admin');
                    }
                }
            }
            block_exacomp_update_evaluation_niveau_tables();
            return $result;
        }

        public function output_html($data, $query='') {
            $return = '';
            $table = new html_table();
            $table->head = array('');
            // Add Schemas.
            foreach ($this->params as $key => $param) {
                // Key 0: schema
                // Key 1: useDifficultylevel
                // Key 2: useStudentSelfEvaluation
                if ($key == 0) {
                    // Schemascount with ZERO.
                    for($i = 0; $i <= $this->schemascount; $i++) {
                        $table->head[] = block_exacomp_get_string('settings_grading_'.$param.'_'.$i);
                    }
                } else {
                    $table->head[] = block_exacomp_get_string('settings_grading_'.$param);
                }
            }
            // Targets:
            foreach ($this->targets as $key => $target) {
                $row = new html_table_row();
                $row->cells[] = new html_table_cell(block_exacomp_get_string('settings_grading_target_'.$target));
                // Schemas.
                for($i = 0; $i <= $this->schemascount; $i++) {
                    $id = $this->get_id().'_'.$target.'_schema_'.$i;
                    $name = $this->get_full_name().'['.$target.'][schema]';
                    $schemaradioattributes = array(
                        'type' => 'radio',
                        'id' => $id,
                        'name' => $name,
                        'value' => $i
                    );
                    if ($data[$target]['schema'] == $i) {
                        $schemaradioattributes['checked'] = 'checked';
                    }
                    $cell = new html_table_cell(html_writer::empty_tag('input', $schemaradioattributes));
                    $cell->attributes['align'] = 'center';
                    $row->cells[] = $cell;
                }
                // Params useDifficultylevel and useStudentSelfEvaluation.
                $otherparams = array_slice($this->params, 1);

                foreach ($otherparams as $key => $paramname) {
                    $id = $this->get_id().'_'.$target.'_'.$paramname.'_'.$key;
                    $name = $this->get_full_name().'['.$target.']['.$paramname.']';
                    // We need "0" for non-checked checkboxes before checkbox element.
                    $hiddeninput = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name, 'value' => '0'));
                    $checkbox = html_writer::checkbox($name,
                            '1',
                            $data[$target][$paramname],
                            '',
                            array('id' => $id));
                    $cell = new html_table_cell($hiddeninput.$checkbox);
                    $cell->attributes['align'] = 'center';
                    $row->cells[] = $cell;
                }
                $table->data[] = $row;
            }
            $return .= html_writer::table($table);
            // Get standard settings parameters template.
            $template = format_admin_setting($this, $this->visiblename, $return,
                    $this->description, true, '', '', $query);
            // Hide some html for better view of this settings.
            $doc = new DOMDocument();
            $doc->loadHTML($template);
            $selector = new DOMXPath($doc);
            // Delete div with classes.
            $deletedivs = array('form-label', 'form-defaultinfo');
            foreach ($deletedivs as $deletediv) {
                foreach($selector->query('//div[contains(attribute::class, "'.$deletediv.'")]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
            }
            // Change col-sm-9 -> col-sm-12 if it is here.
            $template = $doc->saveHTML($doc->documentElement);
            $template = str_replace('col-sm-9', 'col-sm-12', $template);
            return $template;
        }

    }
	
}

// generate id if not set
block_exacomp\data::generate_my_source();

$settings->add(new admin_setting_configcheckbox('exacomp/autotest', block_exacomp_get_string('settings_autotest'),
	block_exacomp_get_string('settings_autotest_description'), 0, 1, 0));

$settings->add(new admin_setting_configtext('exacomp/testlimit', block_exacomp_get_string('settings_testlimit'),
	block_exacomp_get_string('settings_testlimit_description'), 50, PARAM_INTEGER));

$settings->add(new admin_setting_configcheckbox('exacomp/usebadges', block_exacomp_get_string('settings_usebadges'),
	block_exacomp_get_string('settings_usebadges_description'), 0, 1, 0));

$settings->add(new admin_setting_configcheckbox('exacomp/notifications', block_exacomp_get_string('block_exacomp_notifications_head'),
	block_exacomp_get_string('block_exacomp_notifications_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/useprofoundness', block_exacomp_get_string('useprofoundness'),
		'', 0));

$settings->add(new admin_setting_heading('exacomp/heading_evaluation_new', block_exacomp_get_string('settings_grading_schema'), ''));

$settings->add(new block_exacomp_grading_configtable('exacomp/grading_mapping', '', '', ''));

$settings->add(new admin_setting_heading('exacomp/heading_evaluation', block_exacomp_trans(['de:Beurteilung Alt', 'en:Evaluation old']), ''));

$settings->add(new block_exacomp_admin_setting_scheme('exacomp/adminscheme', block_exacomp_get_string('settings_admin_scheme'),
	block_exacomp_get_string('settings_admin_scheme_description'), block_exacomp_get_string('settings_admin_scheme_none'), array(block_exacomp_get_string('settings_admin_scheme_none'), 'G/M/E/Z', 'A/B/C', '*/**/***')));

$settings->add(new admin_setting_configcheckbox_grading('exacomp/additional_grading', block_exacomp_get_string('settings_additional_grading'),
	block_exacomp_get_string('settings_additional_grading_description'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/usetopicgrading', block_exacomp_get_string('usetopicgrading'),
	'', 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usesubjectgrading', block_exacomp_get_string('usesubjectgrading'),
	'', 0));

$settings->add(new admin_setting_heading('exacomp/heading_display', block_exacomp_trans(['de:Anzeige', 'en:Display']), ''));

$settings->add(new admin_setting_configcheckbox('exacomp/usenumbering', block_exacomp_get_string('usenumbering'),
	'', 1));


$settings->add(new admin_setting_heading('exacomp/heading_weekly_schedule', block_exacomp_get_string('weekly_schedule'), ''));
$settings->add(new admin_setting_configtext('exacomp/scheduleinterval', block_exacomp_get_string('settings_interval'),
	block_exacomp_get_string('settings_interval_description'), 50, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/scheduleunits', block_exacomp_get_string('settings_scheduleunits'),
	block_exacomp_get_string('settings_scheduleunits_description'), 8, PARAM_INT));
$settings->add(new admin_setting_configtext('exacomp/schedulebegin', block_exacomp_get_string('settings_schedulebegin'),
	block_exacomp_get_string('settings_schedulebegin_description'), "07:45", PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('exacomp/periods', block_exacomp_get_string('settings_periods'),block_exacomp_get_string('settings_periods_description'),''));
 


$settings->add(new admin_setting_heading('exacomp/heading_data', block_exacomp_trans(['de:Technische Einstellungen', 'en:Technical Settings']), ''));

$settings->add(new admin_setting_configcheckbox('exacomp/logging', block_exacomp_get_string('block_exacomp_logging_head'),
	block_exacomp_get_string('block_exacomp_logging_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign', block_exacomp_get_string('block_exacomp_external_trainer_assign_head'),
	block_exacomp_get_string('block_exacomp_external_trainer_assign_body'), 0));

$settings->add(new admin_setting_configcheckbox('exacomp/elove_student_self_assessment', block_exacomp_get_string('block_exacomp_elove_student_self_assessment_head'),
		block_exacomp_get_string('block_exacomp_elove_student_self_assessment_body'), 0));

$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource', 'Source ID',
	block_exacomp_trans(['de:Automatisch generierte ID dieser Exacomp Installation. Diese kann nicht geändert werden', 'en:Automatically generated ID of this Exacomp installation. This ID can not be changed']), PARAM_TEXT));

$settings->add(new admin_setting_configtext('exacomp/xmlserverurl', block_exacomp_get_string('settings_xmlserverurl'),
	block_exacomp_get_string('settings_configxmlserverurl'), "", PARAM_URL));


