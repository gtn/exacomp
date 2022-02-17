<?php

defined('MOODLE_INTERNAL') || die();

require_once("HTML/QuickForm/input.php");
require_once("HTML/QuickForm/checkbox.php");
require_once($CFG->libdir . '/formslib.php');

class block_exacomp_import_extraconfigcheckbox extends HTML_QuickForm_checkbox {

    public function __construct($elementName = null, $elementLabel = null, $text = '', $attributes = null) {
        parent::__construct($elementName, $elementLabel, $text, $attributes);
    }

    public function toHtml() {
        return '<input type="hidden" value="0" name="' . $this->_attributes['name'] . '" />
                <label for="' . $this->getAttribute('id') . '">' . '<input' . $this->_getAttrString($this->_attributes) . ' />&nbsp;' .
            ($this->_label ? '<span class="badge badge-warning">' . $this->_label . '</span>' : '') .
            $this->_text . '</label>';
    }
}

class exacomp_import_MoodleQuickForm_Renderer extends MoodleQuickForm_Renderer {

    public function __construct() {
        parent::__construct();
        $this->_elementTemplates['default'] = "\n\t\t" . '<div id="{id}" class="fitem {advanced} fitem_{typeclass} {emptylabel} {class}" {aria-live}>{element}</div>';
    }
}

$GLOBALS['_HTML_QuickForm_default_renderer'] = new exacomp_import_MoodleQuickForm_Renderer();

