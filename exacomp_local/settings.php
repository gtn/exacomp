<?php
// This file is part of the LFB-BW plugin for Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die();

// Include a link to the view script in the admin pages
if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('server', new admin_externalpage('local_exacomp_local',
            get_string('exacomp_local:execute', 'local_exacomp_local'),
            new moodle_url('/local/exacomp_local/index.php?action=ws')));
    $ADMIN->add('server', new admin_externalpage('local_exacomp_local_comps',
    		get_string('exacomp_local:checkforcomp', 'local_exacomp_local'),
    		new moodle_url('/local/exacomp_local/index.php?action=comp')));
}
