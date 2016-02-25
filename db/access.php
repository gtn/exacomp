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

$capabilities = array(
		'block/exacomp:admin' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_COURSE,
				'legacy' => array(
						'manager' => CAP_ALLOW
				)
		),
		'block/exacomp:use' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_SYSTEM,
				'legacy' => array(
						'user' => CAP_ALLOW
				)
		),
		'block/exacomp:teacher' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_COURSE,
				'legacy' => array(
						'coursecreator' => CAP_ALLOW,
						'editingteacher' => CAP_ALLOW,
						'teacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				)
		),
		'block/exacomp:student' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_COURSE,
				'legacy' => array(
						'student' => CAP_ALLOW
				)
		),
		'block/exacomp:myaddinstance' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array(
						'user' => CAP_PREVENT
				),
				'clonepermissionsfrom' => 'moodle/my:manageblocks'
		),
		'block/exacomp:addinstance' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_BLOCK,
				'archetypes' => array(
						'editingteacher' => CAP_ALLOW,
						'manager' => CAP_ALLOW
				),
				'clonepermissionsfrom' => 'moodle/site:manageblocks'
		),
		'block/exacomp:deleteexamples' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_COURSE,
				'legacy' => array(
						'manager' => CAP_ALLOW
				)
		),
		'block/exacomp:assignstudents' => array(
				'captype' => 'write',
				'contextlevel' => CONTEXT_SYSTEM,
				'archetypes' => array(
						'manager' => CAP_ALLOW
				)
		)
);
