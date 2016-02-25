<?php
// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
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

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016022301;		// The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2015051100;		// Requires this Moodle version 2.6
$plugin->component = 'block_exacomp'; 	// Full name of the plugin (used for diagnostics)
$plugin->cron = 259200; 				//259200sec = 3 days

$plugin->maturity = MATURITY_STABLE;
$plugin->release = 'v2.6.5-r1'; 			// This is our first release for Moodle 2.7.x branch.
