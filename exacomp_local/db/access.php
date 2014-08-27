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

$capabilities = array(
    // Can run admin scripts
    'local/exacomp_local:execute' => array(
         'captype' => 'write',
         'riskbitmask' => RISK_MANAGETRUST|RISK_DATALOSS|RISK_CONFIG|RISK_PERSONAL|RISK_SPAM|RISK_XSS,
         'contextlevel' => CONTEXT_SYSTEM,
         'legacy' => array() // Only site admins can run this
    )
);