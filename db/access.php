<?php
/*
 * copyright exabis
 */

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
