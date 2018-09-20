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

    class block_exacomp_admin_setting_extraconfigtext extends admin_setting_configtext {

        private $lang = 'de';

        public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype = PARAM_RAW, $size = null) {
            $this->lang = current_language();
            parent::__construct($name, $visiblename, $description, $defaultsetting, $paramtype, $size);
        }

        public function get_setting() {
            $get = parent::get_setting();
            // Different conversions for different parameters
            $paramname = $this->name;
            switch ($paramname) {
                case 'assessment_grade_verbose':
                case 'assessment_verbose_options':
                    $copyofget = trim($get);
                    $get = json_decode($get, true);
                    if (json_last_error() && $copyofget != '') {
                        return $copyofget; // return string if it is not json data
                    }
                    if (isset($get[$this->lang])) {
                        $get = $get[$this->lang];
                    } else {
                        $get = '';
                    }
                    break;
                default:
            }
            return $get;
        }

        public function write_setting($data) {
            // Different parameters can have different data convertion
            $paramname = $this->name;
            switch ($paramname) {
                case 'assessment_grade_verbose':
                case 'assessment_verbose_options':
                    $olddata = get_config('exacomp', $paramname);
                    $copyofold = trim($olddata);
                    $olddata = json_decode($olddata, true);
                    if (json_last_error() && $copyofold != '') { // Old data is not json
                        $olddata['de'] = $copyofold;
                    }
                    $olddata[$this->lang] = $data;
                    if (!isset($olddata['de'])) { // It is possible if the admin works only with EN
                        $olddata['de'] = $data;
                    }
                    $data = json_encode($olddata);
                    break;
                default:
            }
            $ret = parent::write_setting($data);

            if ($ret != '') {
                return $ret;
            }

            return '';
        }

        public function output_html($data, $query='') {
            $output = parent::output_html($data, $query);
            $preconfigparameters = block_exacomp_get_preconfigparameters_list();
            if (in_array($this->name, $preconfigparameters)) {
                $ispreconfig = get_config('exacomp', 'assessment_preconfiguration');
                // Add needed element attributes for work with preconfiguration.
                $doc = new DOMDocument();
                $doc->loadHTML(utf8_decode($output), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $selector = new DOMXPath($doc);
                foreach($selector->query('//input') as $e ) {
                    $e->setAttribute("class", $e->getAttribute('class').' exacomp_forpreconfig');
                    if ($ispreconfig > 0) {
                        $e->setAttribute('readOnly', 'readonly');
                    }
                }
                $output = $doc->saveHTML($doc->documentElement);
            }
            // add mesage about default (DE) value if the user uses not DE interface language
            if ($this->lang != 'de') { // only for NON DE
                switch ($this->name) {
                    case 'assessment_grade_verbose':
                    case 'assessment_verbose_options':
                        $doc = new DOMDocument();
                        $doc->loadHTML(utf8_decode($output), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                        $selector = new DOMXPath($doc);
                        $message = block_exacomp_get_string('settings_default_de_value').call_user_func('block_exacomp_get_'.$this->name, 'de');
                        $br = $doc->createElement('br');
                        foreach($selector->query('//*[@name="s_exacomp_'.$this->name.'"]') as $e ) {
                            $span = $doc->createElement('span', $message);
                            $span->setAttribute('class', 'text-info');
                            $e->parentNode->insertBefore($br, $e->nextSibling);
                            $e->parentNode->insertBefore($span, $e->nextSibling);
                        }
                        $output = $doc->saveHTML($doc->documentElement);
                }
            }
            return $output;
        }
    }

    class block_exacomp_admin_setting_extraconfigcheckbox extends admin_setting_configcheckbox {

        public function output_html($data, $query='') {
            $output = parent::output_html($data, $query);
            $preconfigparameters = block_exacomp_get_preconfigparameters_list();
            if (in_array($this->name, $preconfigparameters)) {
                $ispreconfig = get_config('exacomp', 'assessment_preconfiguration');
                // Add needed element attributes for work with preconfiguration.
                $doc = new DOMDocument();
                $doc->loadHTML(utf8_decode($output), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $selector = new DOMXPath($doc);
                foreach($selector->query('//input') as $e ) {
                    $e->setAttribute("class", $e->getAttribute('class').' exacomp_forpreconfig');
                    if ($ispreconfig > 0) {
                        $e->setAttribute('readOnly', 'readonly');
                    }
                }
                $output = $doc->saveHTML($doc->documentElement);
            }
            return $output;
        }
    }
	class block_exacomp_admin_setting_diffLevelOptions extends block_exacomp_admin_setting_extraconfigtext {
		public function write_setting($data) {
			
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			block_exacomp_update_evaluation_niveau_tables($data, 'niveau');
			return '';
		}
	}
	class block_exacomp_admin_setting_source extends block_exacomp_admin_setting_extraconfigtext {
		public function validate($data) {
			$ret = parent::validate($data);
			if ($ret !== true) {
				return $ret;
			}
			
			if (empty($data)) {
				// No id -> id must always be set.
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
	
/*	class block_exacomp_admin_setting_scheme extends admin_setting_configselect {

        public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			//block_exacomp_update_evaluation_niveau_tables();

			return '';
		}

        public function output_html($data, $query='') {
            $output = parent::output_html($data, $query);
            $preconfigparameters = block_exacomp_get_preconfigparameters_list();
            if (in_array($this->name, $preconfigparameters)) {
                $ispreconfig = get_config('exacomp', 'assessment_preconfiguration');
                // Add needed element attributes for work with preconfiguration.
                $doc = new DOMDocument();
                $doc->loadHTML(utf8_decode($output), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                $selector = new DOMXPath($doc);
                foreach($selector->query('//select') as $e ) {
                    $e->setAttribute("class", $e->getAttribute('class').' exacomp_forpreconfig');
                    if ($ispreconfig > 0) {
                        $options = $e->getElementsByTagName('option');
                        foreach($options as $o ) {
                            $o->setAttribute('disabled', 'disabled');
                        }
                    }
                }
                $output = $doc->saveHTML($doc->documentElement);
            }
            return $output;
        }
	}*/
	
	class block_exacomp_admin_setting_preconfiguration extends admin_setting_configselect {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			//block_exacomp_update_evaluation_niveau_tables();

			return '';
		}

        public function load_choices() {
            $choices = array('0' => block_exacomp_get_string('settings_admin_preconfiguration_none'));
            $xmlarray = block_exacomp_read_preconfigurations_xml();
            if ($xmlarray && is_array($xmlarray)) {
                foreach ($xmlarray as $key => $config) {
                    $choices[$key] = $config['name'];
                }
            }
            $this->choices = $choices;
            return true;
        }

        public function output_html($data, $query='') {
		    $output = parent::output_html($data, $query);
		    // Add onChange on input element.
            $doc = new DOMDocument();
            $doc->loadHTML(utf8_decode($output), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $selector = new DOMXPath($doc);
            foreach($selector->query('//select') as $e ) {
                $e->setAttribute("onChange", "setupPreconfiguration(this);");
            }
            $output = $doc->saveHTML($doc->documentElement);
		    // Add JS code, generated from settings_preconfiguration.xml.
		    $output .= '<script>';
		    $xmlarray = block_exacomp_read_preconfigurations_xml();
		    if ($xmlarray && is_array($xmlarray)) {
		        // Get all parameters from XML. XML can has different sets of parameters
                $configparameters = array();
                foreach ($xmlarray as $id => $config) {
                    unset($config['name']);
                    $configparameters = array_unique(array_merge($configparameters, array_keys($config)));
                }
                $output .= "var preconfigurations = [];\r\n";
                foreach ($xmlarray as $key => $config) {
                    foreach ($configparameters as $param) {
                        if (!key_exists($param, $config)) {
                            $config[$param] = '';
                        }
                    }
                    $output .= 'preconfigurations['.$key.'] = \''.json_encode($config).'\';'."\r\n";
                }
                $output .= 'function setupPreconfiguration(select) {
                    // Enable all elements before any doings
                    var elementsList = document.getElementsByClassName(\'exacomp_forpreconfig\');
                    for (var i = 0, length = elementsList.length; i < length; i++) {
                        if (elementsList[i].type.toLowerCase() == \'checkbox\') {
                            elementsList[i].onclick = null;
                            elementsList[i].onkeydown = null;
                        };
                        if (elementsList[i].tagName.toLowerCase() == \'select\') {
                            var options = elementsList[i].options;
                            for (var j = 0, lopt = options.length; j < lopt; j++) {                                                
                                options[j].removeAttribute(\'disabled\');                                                                                                                                        
                            }
                        };
                        elementsList[i].removeAttribute("readOnly");
                        elementsList[i].removeAttribute("disabled");
                        elementsList[i].style.opacity = 1;
                    }
		            var selectedValue = select.value;
                    if (selectedValue > 0) {
                        var preconfigData = preconfigurations[selectedValue];                        
                        var preconfigObject = JSON.parse(preconfigData);
                        for (var property in preconfigObject) {
                            if (preconfigObject.hasOwnProperty(property)) {                                
                                switch(property) {
                                    case \'assessment_example_scheme\':
                                    case \'assessment_example_diffLevel\':
                                    case \'assessment_example_SelfEval\':
                                    case \'assessment_childcomp_scheme\':
                                    case \'assessment_childcomp_diffLevel\':
                                    case \'assessment_childcomp_SelfEval\':
                                    case \'assessment_comp_scheme\':
                                    case \'assessment_comp_diffLevel\':
                                    case \'assessment_comp_SelfEval\':
                                    case \'assessment_topic_scheme\':
                                    case \'assessment_topic_diffLevel\':
                                    case \'assessment_topic_SelfEval\':
                                    case \'assessment_subject_scheme\':
                                    case \'assessment_subject_diffLevel\':
                                    case \'assessment_subject_SelfEval\':
                                    case \'assessment_theme_scheme\':
                                    case \'assessment_theme_diffLevel\':
                                    case \'assessment_theme_SelfEval\':
                                        var spl = property.split(\'_\');
                                        var target = spl[1];
                                        var prop = spl[2];
                                        var inputname = \'s_exacomp_assessment_mapping[\'+target+\'][\'+prop+\']\';
                                        break;
                                    case \'assessment_points_limit\':
                                    case \'assessment_grade_limit\':
                                    case \'assessment_diffLevel_options\':
                                        var inputname = \'s_exacomp_\'+property;
                                        break;
                                    default:
                                        var inputname = \'s_exacomp_\'+property;
                                        break;                                      
                                }
                                var inputvalue = preconfigObject[property];
                                var elementsList = document.getElementsByName(inputname);                                
                                for (var i = 0, length = elementsList.length; i < length; i++) {
                                    var tag = elementsList[i].tagName.toLowerCase();
                                    var elementType = elementsList[i].type.toLowerCase();
                                    switch (tag) {
                                        case \'input\':
                                                switch (elementType) {
                                                    case \'radio\':
                                                        if (elementsList[i].value == inputvalue) {
                                                            elementsList[i].checked = true;                                                      
                                                        } else {
                                                            elementsList[i].checked = false;
                                                            elementsList[i].disabled = \'disabled\';
                                                        }
                                                        break;
                                                    case \'checkbox\':                                                        
                                                        if (inputvalue == 1) {
                                                            elementsList[i].checked = true;                                                      
                                                        } else {
                                                            elementsList[i].checked = false;
                                                        }
                                                        elementsList[i].onclick = function () { return false; };
                                                        elementsList[i].onkeydown = function () { return false; };
                                                        elementsList[i].style.opacity = 0.5;                                                        
                                                        break;
                                                    case \'text\':
                                                        elementsList[i].value = inputvalue;
                                                        elementsList[i].readOnly = true;
                                                        break;
                                                }
                                                break;
                                        case \'select\':
                                                elementsList[i].value = inputvalue;
                                                var options = elementsList[i].options;
                                                for (var j = 0, lopt = options.length; j < lopt; j++) {                                                
                                                    if (options[j].value != inputvalue) {
                                                        options[j].disabled = \'disabled\';                                                                                                        
                                                    }
                                                }
                                                break;
                                        case \'textarea\':
                                                elementsList[i].value = inputvalue;
                                                elementsList[i].readOnly = true;
                                                break;
                                    }
                                }                                
                            }
                        }
                    } else {
                        // Doings if selected "no preconfigarate"                                              
                    }
		        }
		    ';
            }
            $output .= '</script>';
		    return $output;
        }

	}
	
	class block_exacomp_grading_schema extends admin_setting_configselect {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
		   
			if ($ret != '') {
				return $ret;
			}

			//block_exacomp_update_evaluation_niveau_tables();

			return '';
		}
	}
	
	
	class admin_setting_configcheckbox_grading extends admin_setting_configcheckbox {
		public function write_setting($data) {
			$ret = parent::write_setting($data);
			 
			if($data != '0'){
				// Ensure that value is 0-4 which is needed for new grading scheme.
				foreach(block_exacomp_get_courseids() as $course){
					$course_settings = block_exacomp_get_settings_by_course($course);
					if($course_settings->grading != 3){ // Change course grading.
						$course_settings->grading = 3;
						$course_settings->filteredtaxonomies = json_encode($course_settings->filteredtaxonomies);
						block_exacomp_save_coursesettings($course, $course_settings);
					}
					
					// Map subject, topic, crosssubject, descriptor grading to grade.
					block_exacomp_map_value_to_grading($course);
				}
			}
			return '';
		}
	}

	// Table with Evaluation settings
    class block_exacomp_assessment_configtable extends admin_setting {

	    private $targets = array('example', 'childcomp', 'comp', 'topic', 'subject', 'theme');
	    private $params = array('scheme', 'diffLevel', 'SelfEval');
	    private $schemescount = 4; // Look also language settings: lang/total.php.
        private $ispreconfig = 0; // Need for check using of preconfiguration.

        public function __construct($name, $visiblename, $description, $defaultsetting) {
            // Default assessment settings.
            if ($defaultsetting == '') {
                $assessmentdefault = array();
                foreach ($this->targets as $target) {
                    foreach ($this->params as $param) {
                        switch ($param) {
                            case 'scheme': $value = 0; break;
                            case 'diffLevel': $value = 1; break;
                            case 'SelfEval': $value = 1; break;
                            default: $value = 1; break;
                        }
                        $assessmentdefault[$target][$param] = $value;
                    }
                }
            }
            parent::__construct($name, $visiblename, $description, $assessmentdefault);
        }

        public function get_setting() {
            $result = array();
            foreach ($this->targets as $target) {
                foreach ($this->params as $param) {
                    $targetparam = 'assessment_'.$target.'_'.$param;
                    $value = $this->config_read($targetparam);
                    if ($value !== null) {
                        $result[$target][$param] = $value;
                    } else {
                        $result[$target][$param] = $this->defaultsetting[$target][$param];
                    };
                }
            }
            $this->ispreconfig = $this->config_read('assessment_preconfiguration');
            return $result;
        }

        public function write_setting($data) {
            if(!is_array($data)) {
                $data = $this->defaultsetting;
            }
            $result = '';
            foreach ($data as $target => $parameters) {
                foreach ($parameters as $param => $value) {
                    if (!$this->config_write('assessment_'.$target.'_'.$param, trim($value))) {
                        $result = get_string('errorsetting', 'admin');
                    }
                }
            }
            //block_exacomp_update_evaluation_niveau_tables();
            return $result;
        }

        public function output_html($data, $query='') {
            $return = '';
            $table = new html_table();
            $table->head = array('');
            // Add Schemes.
            foreach ($this->params as $key => $param) {
                // Key 0: scheme
                // Key 1: diffLevel
                // Key 2: SelfEval.
                if ($key == 0) {
                    // Schemescount with ZERO.
                    for($i = 0; $i <= $this->schemescount; $i++) {
                        $table->head[] = block_exacomp_get_string('settings_assessment_'.$param.'_'.$i);
                    }
                } else {
                    $table->head[] = block_exacomp_get_string('settings_assessment_'.$param);
                }
            }
            // Targets:
            foreach ($this->targets as $key => $target) {
                $row = new html_table_row();
                $row->cells[] = new html_table_cell(block_exacomp_get_string('settings_assessment_target_'.$target));
                // Schemes.
                for($i = 0; $i <= $this->schemescount; $i++) {
                    $id = $this->get_id().'_'.$target.'_scheme_'.$i;
                    $name = $this->get_full_name().'['.$target.'][scheme]';
                    $schemeradioattributes = array(
                        'type' => 'radio',
                        'id' => $id,
                        'name' => $name,
                        'value' => $i,
                        'class' => 'exacomp_forpreconfig'
                    );
                    if ($data[$target]['scheme'] == $i) {
                        $schemeradioattributes['checked'] = 'checked';
                    }
                    if ($this->ispreconfig > 0) {
                        $schemeradioattributes['disabled'] = true;
                    }
                    $cell = new html_table_cell(html_writer::empty_tag('input', $schemeradioattributes));
                    $cell->attributes['align'] = 'center';
                    $row->cells[] = $cell;
                }
                // Params diffLevel and SelfEval.
                $otherparams = array_slice($this->params, 1);

                foreach ($otherparams as $key => $paramname) {
                    $id = $this->get_id().'_'.$target.'_'.$paramname.'_'.$key;
                    $name = $this->get_full_name().'['.$target.']['.$paramname.']';
                    // We need "0" for non-checked checkboxes before checkbox element.
                    $hiddeninput = html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $name, 'value' => '0'));
                    $checkboxattributes = array(
                            'id' => $id,
                            'class' => 'exacomp_forpreconfig'
                    );
                    if ($this->ispreconfig > 0) {
                        $checkboxattributes['style'] = 'opacity: 0.5;';
                        $checkboxattributes['onClick'] = 'return false;';
                        $checkboxattributes['onKeydown'] = 'return false;';
                    }
                    $checkbox = html_writer::checkbox($name,
                            '1',
                            $data[$target][$paramname],
                            '',
                            $checkboxattributes);
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
            $doc->loadHTML(utf8_decode($template), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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

// Generate id if not set.
block_exacomp\data::generate_my_source();

// Allgemein (General).
$settings->add(new admin_setting_heading('exacomp/heading_general', block_exacomp_get_string('settings_heading_general'), ''));
$settings->add(new admin_setting_configcheckbox('exacomp/autotest', block_exacomp_get_string('settings_autotest'),
	    block_exacomp_get_string('settings_autotest_description'), 0, 1, 0));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/testlimit', block_exacomp_get_string('settings_testlimit'),
	    block_exacomp_get_string('settings_testlimit_description'), 50, PARAM_INT));

// Beurteilung (assessment).
$settings->add(new admin_setting_heading('exacomp/heading_assessment',
        block_exacomp_get_string('settings_heading_assessment'),
        ''));
$settings->add(new block_exacomp_admin_setting_preconfiguration('exacomp/assessment_preconfiguration',
        block_exacomp_get_string('settings_admin_scheme'),
        block_exacomp_get_string('settings_admin_scheme_description'),
        block_exacomp_get_string('settings_admin_scheme_none'),
        null ));
        
$settings->add(new block_exacomp_assessment_configtable('exacomp/assessment_mapping', '', '', ''));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_points_limit',
        block_exacomp_get_string('settings_assessment_points_limit'),
        block_exacomp_get_string('settings_assessment_points_limit_description'),
        20, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_grade_limit',
        block_exacomp_get_string('settings_assessment_grade_limit'),
        block_exacomp_get_string('settings_assessment_grade_limit_description'),
        20, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_grade_verbose',
        block_exacomp_get_string('settings_assessment_grade_verbose'),
        block_exacomp_get_string('settings_assessment_grade_verbose_description'),
        block_exacomp_get_string('settings_assessment_grade_verbose_default'),
        PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/use_grade_verbose_competenceprofile',
        block_exacomp_get_string('use_grade_verbose_competenceprofile'),
        block_exacomp_get_string('use_grade_verbose_competenceprofile_descr'), 1));

$settings->add(new block_exacomp_admin_setting_diffLevelOptions('exacomp/assessment_diffLevel_options',
        block_exacomp_get_string('settings_assessment_diffLevel_options'),
        block_exacomp_get_string('settings_assessment_diffLevel_options_description'),
        block_exacomp_get_string('settings_assessment_diffLevel_options_default'),
        PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_verbose_options',
        block_exacomp_get_string('settings_assessment_verbose_options'),
        block_exacomp_get_string('settings_assessment_verbose_options_description'),
        block_exacomp_get_string('settings_assessment_verbose_options_default'),
        PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/assessment_verbose_options_short',
        block_exacomp_get_string('settings_assessment_verbose_options_short'),
        block_exacomp_get_string('settings_assessment_verbose_options_short_description'),
        block_exacomp_get_string('settings_assessment_verbose_options_short_default'),
        PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/useprofoundness',
        block_exacomp_get_string('useprofoundness'),
        '', 0));
$settings->add(new block_exacomp_admin_setting_extraconfigcheckbox('exacomp/assessment_SelfEval_useVerbose',
        block_exacomp_get_string('assessment_SelfEval_useVerbose'),
        '', 0));

// Darstellung (visualisation).
$settings->add(new admin_setting_heading('exacomp/heading_visualisation',
        block_exacomp_get_string('settings_heading_visualisation'),
        ''));
$settings->add(new admin_setting_configcheckbox('exacomp/usenumbering',
        block_exacomp_get_string('usenumbering'),
        '', 1));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/scheduleinterval',
        block_exacomp_get_string('settings_interval'),
        block_exacomp_get_string('settings_interval_description'), 50, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/scheduleunits',
        block_exacomp_get_string('settings_scheduleunits'),
        block_exacomp_get_string('settings_scheduleunits_description'), 8, PARAM_INT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/schedulebegin',
        block_exacomp_get_string('settings_schedulebegin'),
        block_exacomp_get_string('settings_schedulebegin_description'), "07:45", PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('exacomp/periods',
        block_exacomp_get_string('settings_periods'),
        block_exacomp_get_string('settings_periods_description'),''));

// Administratives (technical).
$settings->add(new admin_setting_heading('exacomp/heading_technical',
        block_exacomp_get_string('settings_heading_technical'),
        ''));
$settings->add(new admin_setting_configcheckbox('exacomp/usebadges',
        block_exacomp_get_string('settings_usebadges'),
    	block_exacomp_get_string('settings_usebadges_description'), 0, 1, 0));
$settings->add(new admin_setting_configcheckbox('exacomp/notifications',
        block_exacomp_get_string('block_exacomp_notifications_head'),
    	block_exacomp_get_string('block_exacomp_notifications_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/logging',
        block_exacomp_get_string('block_exacomp_logging_head'),
        block_exacomp_get_string('block_exacomp_logging_body'), 0));
$settings->add(new block_exacomp_admin_setting_source('exacomp/mysource',
        block_exacomp_get_string('settings_sourceId'),
        block_exacomp_get_string('settings_sourceId_description'),
        PARAM_TEXT));
$settings->add(new block_exacomp_admin_setting_extraconfigtext('exacomp/xmlserverurl',
        block_exacomp_get_string('settings_xmlserverurl'),
        block_exacomp_get_string('settings_configxmlserverurl'), "", PARAM_URL));

// Apps-Einstellungen (configuration for apps).
$settings->add(new admin_setting_heading('exacomp/heading_apps',
        block_exacomp_get_string('settings_heading_apps'),
        ''));
$settings->add(new admin_setting_configcheckbox('exacomp/elove_student_self_assessment',
        block_exacomp_get_string('block_exacomp_elove_student_self_assessment_head'),
        block_exacomp_get_string('block_exacomp_elove_student_self_assessment_body'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/external_trainer_assign',
        block_exacomp_get_string('block_exacomp_external_trainer_assign_head'),
        block_exacomp_get_string('block_exacomp_external_trainer_assign_body'), 0));


// To delete?
//$settings->add(new block_exacomp_admin_setting_scheme('exacomp/adminscheme',
//        block_exacomp_get_string('settings_admin_scheme'),
//        block_exacomp_get_string('settings_admin_scheme_description'),
//        block_exacomp_get_string('settings_admin_scheme_none'),
//        array(block_exacomp_get_string('settings_admin_scheme_none'), 'G/M/E/Z', 'A/B/C', '*/**/***')));
/*$settings->add(new admin_setting_heading('exacomp/heading_data', '&nbsp;', ''));
$settings->add(new admin_setting_configcheckbox_grading('exacomp/additional_grading',
        block_exacomp_get_string('settings_additional_grading'),
	    block_exacomp_get_string('settings_additional_grading_description'), 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usetopicgrading',
            block_exacomp_get_string('usetopicgrading'),
	    '', 0));
$settings->add(new admin_setting_configcheckbox('exacomp/usesubjectgrading',
        block_exacomp_get_string('usesubjectgrading'),
	    '', 0));*/


