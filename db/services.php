<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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

$services = array(
	'exacompservices' => array(
		'requiredcapability' => '',
		'restrictedusers' => 0,
		'enabled' => 1,
		'shortname' => 'exacompservices',
		'functions' => [],
	)
);

$functions = [];

call_user_func(function() use (&$functions, &$services) {
	$definitions = [
		[ 'block_exacomp_get_courses', 'read', 'Get courses with exacomp block instances.' ],
		[ 'block_exacomp_create_example', 'write', 'Create an example' ],
		[ 'block_exacomp_delete_example', 'write', 'delete a custom item' ],
		[ 'block_exacomp_delete_item', 'write', 'delete a submitted and wrong item' ],
		// [ 'block_exacomp_set_competence', 'write', 'Set a student evaluation for a particular competence' ],
		[ 'block_exacomp_get_competencies_by_topic', 'read', 'get competencies for a specific topic' ],
		[ 'block_exacomp_get_competencies_for_upload', 'read', 'Get competencetree' ],
		[ 'block_exacomp_get_descriptors_for_example', 'read', 'Get desciptors for example' ],
		[ 'block_exacomp_get_example_by_id', 'read', 'Get example' ],
		[ 'block_exacomp_get_examples_for_subject', 'read', 'Get examples for subtopic' ],
		[ 'block_exacomp_get_external_trainer_students', 'read', 'Get external trainer\'s students' ],
		[ 'block_exacomp_get_item_for_example', 'read', 'Get Item' ],
		[ 'block_exacomp_get_subjects_for_user', 'read', 'Get Subjects' ],
		[ 'block_exacomp_get_user_examples', 'read', 'get examples created by a specific user' ],
		[ 'block_exacomp_get_user_profile', 'read', 'get a list of courses with their competencies' ],
		[ 'block_exacomp_get_user_role', 'read', 'Get role for user: 1=trainer 2=student' ],
		[ 'block_exacomp_grade_item', 'write', 'Grade an item' ],
		[ 'block_exacomp_submit_example', 'read', 'Submit example' ],
		[ 'block_exacomp_update_example', 'write', 'update an example' ],
		[ 'dakora_add_example_to_learning_calendar', 'write', 'add example to learning calendar for dakora' ],
		[ 'dakora_add_example_to_pre_planning_storage', 'write', 'add example to current pre planning storage' ],
		[ 'dakora_add_examples_to_students_schedule', 'write', 'add examples from current pre planning storage to students weekly schedule' ],
		[ 'dakora_allow_example_resubmission', 'read', 'allow student to resubmit example' ],
		[ 'dakora_create_blocking_event', 'write', 'create a blocking event' ],
		[ 'dakora_empty_pre_planning_storage', 'write', 'delte all items from current pre planning storage' ],
		[ 'dakora_get_admin_grading_scheme', 'read', 'get admin grading scheme' ],
		[ 'dakora_get_all_descriptor_children_for_cross_subject', 'read', 'get children in context of cross subject' ],
		[ 'dakora_get_all_descriptors_by_cross_subject', 'read', 'get descriptors for a cross subject' ],
		[ 'dakora_get_all_descriptors', 'read', 'get descriptors for topic for dakora app' ],
		[ 'dakora_get_all_topics_by_course', 'read', 'get topics for course for dakora app' ],
		[ 'dakora_get_competence_grid_for_profile', 'read', 'get grid for profile' ],
		[ 'dakora_get_competence_profile_for_topic', 'read', 'get competence profile for current topic' ],
		[ 'dakora_get_courses', 'read', 'get courses for user for dakora app' ],
		[ 'dakora_get_cross_subjects_by_course', 'read', 'get cross subjects for an user in course context (allways all crosssubjs, even if not associated)' ],
		[ 'dakora_get_descriptor_children_for_cross_subject', 'read', 'get children in context of cross subject, associated with examples' ],
		[ 'dakora_get_descriptor_children', 'read', 'get children (childdescriptor and examples) for descriptor for dakora app (only childs associated with examples)' ],
		[ 'dakora_get_descriptor_details', 'read', 'get descriptor details incl. grading and children' ],
		[ 'dakora_get_descriptors_by_cross_subject', 'read', 'get descriptors for a cross subject associated with examples' ],
		[ 'dakora_get_descriptors_for_example', 'read', 'get descriptors where example is associated' ],
		[ 'dakora_get_descriptors', 'read', 'get descriptors for topic for dakora app associated with examples' ],
		[ 'dakora_get_example_grading', 'read', 'get student and teacher evaluation for example' ],
		[ 'dakora_get_example_information', 'read', 'get information and submission for example' ],
		[ 'dakora_get_example_overview', 'read', 'get example overview for dakora app' ],
		[ 'dakora_get_examples_by_descriptor_and_grading', 'read', 'returns examples for given descriptor and grading' ],
		[ 'dakora_get_examples_for_descriptor_for_crosssubject_with_grading', 'read', 'get examples for descriptor with additional grading information' ],
		[ 'dakora_get_examples_for_descriptor_for_crosssubject', 'read', 'get examples for descriptor for dakora app' ],
		[ 'dakora_get_examples_for_descriptor_with_grading', 'read', 'get examples for descriptor with additional grading information' ],
		[ 'dakora_get_examples_for_descriptor', 'read', 'get examples for descriptor for dakora app' ],
		[ 'dakora_get_examples_for_time_slot', 'read', 'get examples for a special start to end period (e.g. day)' ],
		[ 'dakora_get_examples_pool', 'read', 'get list of examples for weekly schedule pool' ],
		[ 'dakora_get_examples_trash', 'read', 'get examples for trash bin' ],
		[ 'dakora_get_pre_planning_storage_examples', 'read', 'get examples for pre planning storage' ],
		[ 'dakora_get_pre_planning_storage_students', 'read', 'get students for pre planning storage' ],
		[ 'dakora_get_schedule_config', 'read', 'get configuration options for schedule units' ],
		[ 'dakora_get_students_for_course', 'read', 'get list of students for course' ],
		[ 'dakora_get_topics_by_course', 'read', 'get topics for course for dakora app associated with examples' ],
		[ 'dakora_get_user_information', 'read', 'get information about current user' ],
		[ 'dakora_get_user_role', 'read', 'get user role 1= trainer, 2= student' ],
		[ 'dakora_grade_example', 'write', 'grade example solution' ],
		[ 'dakora_has_items_in_pre_planning_storage', 'read', 'return 0 if no items, 1 otherwise' ],
		[ 'dakora_remove_example_from_schedule', 'write', 'remove example from weekly schedule' ],
		[ 'dakora_set_example_time_slot', 'write', 'set start and end time for example' ],
		[ 'dakora_submit_example', 'write', 'submit example solution' ],
		[ 'dakora_set_competence', 'write', 'set competence for student'],
	];

	foreach ($definitions as $definition) {
		$functions[$definition[0]] = [                             // web service function name
				'classname'   => 'block_exacomp_external',         // class containing the external function
				'methodname'  => str_replace('block_exacomp_', '', $definition[0]), // external function name, strip block_exacomp_ for function name
				'classpath'   => 'blocks/exacomp/externallib.php', // file containing the class/external function
				'description' => $definition[2],	               // human readable description of the web service function
				'type'		  => $definition[1],	               // database rights of the web service function (read, write)
		];

		$services['exacompservices']['functions'][] = $definition[0];
	}
});
