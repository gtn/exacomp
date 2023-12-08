<?php

namespace core_question\local\bank;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/editlib.php');

use core_plugin_manager;
use qbank_columnsortorder\column_manager;


require_once('descriptor_link_column.php');
require_once('plugin_feature.php');

class exacomp_view extends view {


    public function __construct($contexts, $pageurl, $course, $cm = null) {
        parent::__construct($contexts, $pageurl, $course, $cm);
    }


    protected function get_question_bank_plugins(): array {
        $questionbankclasscolumns = [];
        $newpluginclasscolumns = [];
        $corequestionbankcolumns = [
            'checkbox_column',
            'question_type_column',
            'question_name_idnumber_tags_column',
            'edit_menu_column',
            'edit_action_column',
            'copy_action_column',
            'tags_action_column',
            'preview_action_column',
            'history_action_column',
            'delete_action_column',
            'export_xml_action_column',
            'question_status_column',
            'version_number_column',
            'creator_name_column',
            'comment_count_column',
            'descriptor_link_column',
        ];
        if (question_get_display_preference('qbshowtext', 0, PARAM_BOOL, new \moodle_url(''))) {
            $corequestionbankcolumns[] = 'question_text_row';
        }

        foreach ($corequestionbankcolumns as $fullname) {
            $shortname = $fullname;
            if (class_exists('core_question\\local\\bank\\' . $fullname)) {
                $fullname = 'core_question\\local\\bank\\' . $fullname;
                $questionbankclasscolumns[$shortname] = new $fullname($this);
            } else {
                $questionbankclasscolumns[$shortname] = '';
            }
        }
        $plugins = \core_component::get_plugin_list_with_class('qbank', 'plugin_feature', 'plugin_feature.php');
        $plugins["qbank_questiontodescriptor"] = "\qbank_questiontodescriptor\plugin_feature";
        foreach ($plugins as $componentname => $plugin) {
            $pluginentrypointobject = new $plugin();
            $plugincolumnobjects = $pluginentrypointobject->get_question_columns($this);
            // Don't need the plugins without column objects.
            if (empty($plugincolumnobjects)) {
                unset($plugins[$componentname]);
                continue;
            }
            foreach ($plugincolumnobjects as $columnobject) {
                $columnname = $columnobject->get_column_name();
                foreach ($corequestionbankcolumns as $key => $corequestionbankcolumn) {
                    if (!\core\plugininfo\qbank::is_plugin_enabled($componentname)) {
                        unset($questionbankclasscolumns[$columnname]);
                        continue;
                    }
                    // Check if it has custom preference selector to view/hide.
                    if ($columnobject->has_preference()) {
                        if (!$columnobject->get_preference()) {
                            continue;
                        }
                    }
                    if ($corequestionbankcolumn === $columnname) {
                        $questionbankclasscolumns[$columnname] = $columnobject;
                    } else {
                        // Any community plugin for column/action.
                        $newpluginclasscolumns[$columnname] = $columnobject;
                    }
                }
            }
        }

        // New plugins added at the end of the array, will change in sorting feature.
        foreach ($newpluginclasscolumns as $key => $newpluginclasscolumn) {
            $questionbankclasscolumns[$key] = $newpluginclasscolumn;
        }

        // Check if qbank_columnsortorder is enabled.
        if (array_key_exists('columnsortorder', core_plugin_manager::instance()->get_enabled_plugins('qbank'))) {
            $columnorder = new column_manager();
            $questionbankclasscolumns = $columnorder->get_sorted_columns($questionbankclasscolumns);
        }

        // Mitigate the error in case of any regression.
        foreach ($questionbankclasscolumns as $shortname => $questionbankclasscolumn) {
            if (empty($questionbankclasscolumn)) {
                unset($questionbankclasscolumns[$shortname]);
            }
        }

        $specialpluginentrypointobject = new \qbank_questiontodescriptor\plugin_feature();
        $specialplugincolumnobjects = $specialpluginentrypointobject->get_question_columns($this);
        $questionbankclasscolumns["descriptor_link_column"] = $specialplugincolumnobjects[0];

        return $questionbankclasscolumns;
    }

}
