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

require_once __DIR__."/../../inc.php";


/**
 * Structure step to restore one choice activity
 */
class restore_exacomp_activity_structure_step extends restore_activity_structure_step {
    
    protected function define_structure() {
        
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
        
        $paths[] = new restore_path_element('exacomp', '/activity/choice');
        $paths[] = new restore_path_element('exacomp_option', '/activity/choice/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('exacomp_answer', '/activity/exacomp/answers/answer');
        }
        
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }
    
    protected function process_activity($data) {
        global $DB;
        
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        
        // insert the choice record
        $newitemid = $DB->insert_record('choice', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    
    protected function after_execute() {
        // Add choice related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_choice', 'intro', null);
    }
}