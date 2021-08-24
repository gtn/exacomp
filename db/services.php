<?php

$functions = array (
  'block_exacomp_get_courses' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_courses',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get courses with exacomp block instances.
get courses',
    'type' => 'read',
  ),
  'block_exacomp_get_examples_for_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_examples_for_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get examples for subtopic
Get examples',
    'type' => 'read',
  ),
  'block_exacomp_get_examples_for_subject_with_lfs_infos' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_examples_for_subject_with_lfs_infos',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get examples for subtopic
Get examples',
    'type' => 'read',
  ),
  'block_exacomp_get_example_by_id' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_example_by_id',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get example
Get example',
    'type' => 'read',
  ),
  'block_exacomp_get_descriptors_for_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_descriptors_for_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get desciptors for example
Get descriptors for example',
    'type' => 'read',
  ),
  'block_exacomp_get_descriptors_for_quiz' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_descriptors_for_quiz',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get desciptors for quiz
Get descriptors for quiz',
    'type' => 'read',
  ),
  'block_exacomp_get_user_role' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_user_role',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get role for user: 1=trainer 2=student',
    'type' => 'read',
  ),
  'block_exacomp_diggr_get_user_role' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggr_get_user_role',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get role for user: 1=trainer 2=student',
    'type' => 'read',
  ),
  'block_exacomp_get_external_trainer_students' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_external_trainer_students',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get external trainer\'s students
Get all students for an external trainer',
    'type' => 'read',
  ),
  'block_exacomp_get_subjects_for_user' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_subjects_for_user',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Subjects
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'diggrplus_get_subjects_and_topics_for_user' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_subjects_and_topics_for_user',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Subjects
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'diggrplus_get_niveaus_for_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_niveaus_for_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Subjects
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'block_exacomp_delete_item' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'delete_item',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delete a submitted and wrong item
Deletes one user item if it is not graded already',
    'type' => 'write',
  ),
  'block_exacomp_set_competence' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'set_competence',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Set a student evaluation for a particular competence
Set student evaluation',
    'type' => 'write',
  ),
  'block_exacomp_get_item_for_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_item_for_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Item
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'block_exacomp_get_competencies_for_upload' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_competencies_for_upload',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get competencetree
Get all available competencies',
    'type' => 'read',
  ),
  'block_exacomp_submit_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'submit_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Submit example
submit example for elove and diggr
Add item',
    'type' => 'read',
  ),
  'block_exacomp_create_or_update_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'create_or_update_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_create_or_update_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_create_or_update_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_grade_descriptor' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_grade_descriptor',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Grade a descriptor',
    'type' => 'write',
  ),
  'diggrplus_import_ms_teams_students' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_import_ms_teams_students',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'write',
  ),
  'block_exacomp_grade_item' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'grade_item',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Grade an item
grade an item',
    'type' => 'write',
  ),
  'block_exacomp_get_user_examples' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_user_examples',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples created by a specific user
grade an item',
    'type' => 'read',
  ),
  'block_exacomp_get_user_profile' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_user_profile',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get a list of courses with their competencies',
    'type' => 'read',
  ),
  'block_exacomp_update_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'update_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'update an example',
    'type' => 'write',
  ),
  'block_exacomp_delete_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'delete_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delete a custom item
delete example',
    'type' => 'write',
  ),
  'block_exacomp_get_competencies_by_topic' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_competencies_by_topic',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get competencies for a specific topic
Get all available competencies',
    'type' => 'read',
  ),
  'dakora_set_competence' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_competence',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set competence for student
Set a competence for a user',
    'type' => 'write',
  ),
  'dakora_get_courses' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_courses',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get courses for user for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_topics_by_course' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_topics_by_course',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get topics for course for dakora app associated with examples
get courses',
    'type' => 'read',
  ),
  'dakora_get_all_topics_by_course' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_all_topics_by_course',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get topics for course for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_descriptors' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptors',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors for topic for dakora app associated with examples
get descriptors for one topic, considering the visibility',
    'type' => 'read',
  ),
  'dakora_get_all_descriptors' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_all_descriptors',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors for topic for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_descriptor_children' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptor_children',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get children (childdescriptor and examples) for descriptor for dakora app (only childs associated with examples)
get courses',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_for_descriptor',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for descriptor for dakora app',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_with_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_for_descriptor_with_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for descriptor with additional grading information',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_for_crosssubject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_for_descriptor_for_crosssubject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for descriptor for dakora app',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_for_crosssubject_with_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_for_descriptor_for_crosssubject_with_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for descriptor with additional grading information',
    'type' => 'read',
  ),
  'dakora_get_example_overview' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_example_overview',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get example overview for dakora app',
    'type' => 'read',
  ),
  'diggrplus_get_example_overview' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_example_overview',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get example overview for dakora app',
    'type' => 'read',
  ),
  'dakora_add_example_to_learning_calendar' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_add_example_to_learning_calendar',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'add example to learning calendar for dakora
get courses',
    'type' => 'write',
  ),
  'dakora_get_descriptors_for_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptors_for_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors where example is associated
Get descriptors for example',
    'type' => 'read',
  ),
  'dakora_get_example_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_example_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get student and teacher evaluation for example
Get example grading for user',
    'type' => 'read',
  ),
  'dakora_get_topic_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_topic_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get student and teacher evaluation for topic
Get topic grading for user',
    'type' => 'read',
  ),
  'dakora_get_subject_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_subject_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get student and teacher evaluation for subject
Get subject grading for user',
    'type' => 'read',
  ),
  'dakora_get_user_role' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_user_role',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get user role 1= trainer, 2= student
return 1 for trainer
2 for student
0 if false',
    'type' => 'read',
  ),
  'dakora_get_students_and_groups_for_course' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_students_and_groups_for_course',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get list of students for course',
    'type' => 'read',
  ),
  'dakora_get_students_for_teacher' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_students_for_teacher',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get list of students that are enrolled in any course of a teacher',
    'type' => 'read',
  ),
  'dakora_get_teachers_for_student' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_teachers_for_student',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get list of teachers in any course of the student',
    'type' => 'read',
  ),
  'dakora_get_examples_pool' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_pool',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get list of examples for weekly schedule pool
Get examples for pool',
    'type' => 'read',
  ),
  'dakora_get_examples_trash' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_trash',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for trash bin
Get examples for trash',
    'type' => 'read',
  ),
  'dakora_set_example_time_slot' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_example_time_slot',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set start and end time for example
set example time slot',
    'type' => 'write',
  ),
  'dakora_remove_example_from_schedule' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_remove_example_from_schedule',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'remove example from weekly schedule
remove example from time slot',
    'type' => 'write',
  ),
  'dakora_add_examples_to_schedule_for_all' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_add_examples_to_schedule_for_all',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'add examples to the schedules of all course students
remove example from time slot',
    'type' => 'write',
  ),
  'dakora_get_examples_for_time_slot' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_for_time_slot',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for a special start to end period (e.g. day)
Get examples for time slot',
    'type' => 'read',
  ),
  'dakora_get_cross_subjects_by_course' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_cross_subjects_by_course',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get cross subjects for an user in course context (allways all crosssubjs, even if not associated)
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_descriptors_by_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptors_by_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors for a cross subject associated with examples
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_all_descriptors_by_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_all_descriptors_by_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors for a cross subject
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_descriptor_children_for_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptor_children_for_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get children in context of cross subject, associated with examples
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_all_descriptor_children_for_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_all_descriptor_children_for_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get children in context of cross subject
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_schedule_config' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_schedule_config',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get configuration options for schedule units
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_examples' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_pre_planning_storage_examples',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get examples for pre planning storage
get pre planning storage examples for current teacher',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_students' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_pre_planning_storage_students',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get students for pre planning storage
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_groups' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_pre_planning_storage_groups',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get students for pre planning storage
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_has_items_in_pre_planning_storage' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_has_items_in_pre_planning_storage',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'return 0 if no items, 1 otherwise
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_empty_pre_planning_storage' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_empty_pre_planning_storage',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delte all items from current pre planning storage
empty pre planning storage for current teacher',
    'type' => 'write',
  ),
  'dakora_add_example_to_pre_planning_storage' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_add_example_to_pre_planning_storage',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'add example to current pre planning storage
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_add_examples_to_students_schedule' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_add_examples_to_students_schedule',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'add examples from current pre planning storage to students weekly schedule
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_add_examples_to_selected_students_schedule' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_add_examples_to_selected_students_schedule',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'add examples from current pre planning storage to students weekly schedule
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_delete_examples_from_schedule' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_delete_examples_from_schedule',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'remove example from weekly schedule by teacherid and distribution id
used for \'undo\' button',
    'type' => 'write',
  ),
  'dakora_undo_examples_from_schedule' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_undo_examples_from_schedule',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'remove example from weekly schedule by teacherid and distribution id
used for \'undo\' button',
    'type' => 'write',
  ),
  'dakora_submit_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_submit_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'submit example solution
Add student submission to example.',
    'type' => 'write',
  ),
  'diggrplus_submit_item' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_submit_item',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_submit_item_comment' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_submit_item_comment',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_get_item_comments' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_item_comments',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_get_examples_and_items' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_examples_and_items',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Items
get all items AND examples for a competence
they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend',
    'type' => 'read',
  ),
  'diggrplus_get_teacher_examples_and_items' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_teacher_examples_and_items',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Items
get all items AND examples for a competence
they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend',
    'type' => 'read',
  ),
  'diggrplus_get_all_subjects_for_course_as_tree' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_all_subjects_for_course_as_tree',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get Subjects
get subjects from one user for one course',
    'type' => 'read',
  ),
  'diggrplus_get_user_info' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_user_info',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_request_external_file' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_request_external_file',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Load a file from an external Domain to prevent CORS when loading directly in the App',
    'type' => 'read',
  ),
  'diggrplus_grade_item' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_grade_item',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_get_competence_profile_statistic' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_competence_profile_statistic',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get competence statistic for profile',
    'type' => 'read',
  ),
  'diggrplus_get_descriptors_for_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_descriptors_for_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptors where example is associated
Get descriptors for example',
    'type' => 'read',
  ),
  'dakora_grade_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_grade_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'grade example solution
Add student submission to example.',
    'type' => 'write',
  ),
  'dakora_get_descriptors_details' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptors_details',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptor details incl. grading and children for many descriptors',
    'type' => 'read',
  ),
  'dakora_get_descriptor_details' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_descriptor_details',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get descriptor details incl. grading and children',
    'type' => 'read',
  ),
  'dakora_get_example_information' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_example_information',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get information and submission for example
get example with all submission details and gradings',
    'type' => 'read',
  ),
  'dakora_get_user_information' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_user_information',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get information about current user
get example with all submission details and gradings',
    'type' => 'read',
  ),
  'dakora_get_lang_information' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_lang_information',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Returns lang information from exacomp',
    'type' => 'read',
  ),
  'dakora_create_blocking_event' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_create_blocking_event',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'create a blocking event
Create a new blocking event',
    'type' => 'write',
  ),
  'dakora_get_examples_by_descriptor_and_grading' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_examples_by_descriptor_and_grading',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'returns examples for given descriptor and grading
Create a new blocking event',
    'type' => 'read',
  ),
  'dakora_allow_example_resubmission' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_allow_example_resubmission',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'allow student to resubmit example
Create a new blocking event',
    'type' => 'read',
  ),
  'dakora_get_competence_grid_for_profile' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_competence_grid_for_profile',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get grid for profile
Get competence grid for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_statistic' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_competence_profile_statistic',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get statistic in user and subject context
Get competence statistic for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_comparison' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_competence_profile_comparison',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get list for student and teacher comparison
Get competence comparison for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_topic_statistic' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_competence_profile_topic_statistic',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get data for 3D graph
Get competence statistic for topic in profile for 3D graph',
    'type' => 'read',
  ),
  'block_exacomp_is_elove_student_self_assessment_enabled' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'is_elove_student_self_assessment_enabled',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'check the corresponding config setting',
    'type' => 'read',
  ),
  'dakora_get_site_policies' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_site_policies',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_diggr_create_cohort' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggr_create_cohort',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create one or more cohorts',
    'type' => 'write',
  ),
  'block_exacomp_diggr_get_students_of_cohort' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggr_get_students_of_cohort',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create one or more cohorts',
    'type' => 'read',
  ),
  'block_exacomp_diggr_get_cohorts_of_trainer' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggr_get_cohorts_of_trainer',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create one or more cohorts',
    'type' => 'read',
  ),
  'dakora_get_evaluation_config' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_evaluation_config',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get evaluation configuration
get admin evaluation configurations',
    'type' => 'read',
  ),
  'dakora_get_config' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_config',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'dakora_get_courseconfigs' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_courseconfigs',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_login' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'login',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'webservice called through token.php',
    'type' => 'read',
  ),
  'dakora_set_descriptor_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_descriptor_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for descriptor',
    'type' => 'write',
  ),
  'dakora_set_example_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_example_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for example',
    'type' => 'write',
  ),
  'dakora_set_topic_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_topic_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_set_niveau_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_niveau_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_set_example_solution_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_example_solution_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for example solutions',
    'type' => 'write',
  ),
  'diggrplus_set_descriptor_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_set_descriptor_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for descriptor',
    'type' => 'write',
  ),
  'diggrplus_set_example_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_set_example_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for example',
    'type' => 'write',
  ),
  'diggrplus_set_topic_visibility' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_set_topic_visibility',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_create_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_create_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'create new crosssubject',
    'type' => 'write',
  ),
  'dakora_delete_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_delete_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delete cross subject',
    'type' => 'write',
  ),
  'dakora_edit_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_edit_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'edit existing crosssubject',
    'type' => 'write',
  ),
  'dakora_get_cross_subject_drafts' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_cross_subject_drafts',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get available drafts',
    'type' => 'read',
  ),
  'dakora_get_subjects' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_subjects',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get subjects',
    'type' => 'read',
  ),
  'dakora_get_students_for_cross_subject' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_get_students_for_cross_subject',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'get_students_for_crosssubject',
    'type' => 'read',
  ),
  'dakora_set_cross_subject_student' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_cross_subject_student',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set visibility for crosssubject and student',
    'type' => 'write',
  ),
  'dakora_set_cross_subject_descriptor' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_set_cross_subject_descriptor',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set descriptor crosssubject association',
    'type' => 'write',
  ),
  'dakora_dismiss_oldgrading_warning' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_dismiss_oldgrading_warning',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'set descriptor crosssubject association',
    'type' => 'write',
  ),
  'dakora_send_message' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_send_message',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'send message',
    'type' => 'write',
  ),
  'block_exacomp_update_descriptor_category' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'update_descriptor_category',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'update an descriptor category',
    'type' => 'write',
  ),
  'block_exacomp_get_url_preview' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'get_url_preview',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'gets title description and image of website',
    'type' => 'read',
  ),
  'dakora_competencegrid_overview' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_competencegrid_overview',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'view competence overview',
    'type' => 'read',
  ),
  'dakora_delete_custom_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'dakora_delete_custom_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delete example',
    'type' => 'write',
  ),
  'diggrplus_delete_custom_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_delete_custom_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'delete example',
    'type' => 'write',
  ),
  'diggrplus_get_course_schooltype_tree' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_course_schooltype_tree',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_set_active_course_topics' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_set_active_course_topics',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_get_config' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_get_config',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_annotate_example' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diggrplus_annotate_example',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'block_exacomp_diwipass_get_sections_with_materials' => 
  array (
    'classname' => 'block_exacomp_external',
    'methodname' => 'diwipass_get_sections_with_materials',
    'classpath' => 'blocks/exacomp/externallib.php',
    'description' => 'Get urls and resources per section for every course of current user',
    'type' => 'write',
  ),
  'diggrplus_v_edit_course' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_edit_course',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_create_or_update_student' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_create_or_update_student',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_delete_student' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_delete_student',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_get_student_by_id' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_get_student_by_id',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_get_student_grading_tree' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_get_student_grading_tree',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => 'Get competence statistic for profile',
    'type' => 'read',
  ),
  'diggrplus_v_save_student_grading' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_save_student_grading',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_v_print_student_grading_report' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_print_student_grading_report',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_v_get_course_edulevel_schooltype_tree' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrplus_v_get_course_edulevel_schooltype_tree',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_diggrv_create_course' => 
  array (
    'classname' => 'block_exacomp_external_diggrv',
    'methodname' => 'diggrv_create_course',
    'classpath' => 'blocks/exacomp/externallib.diggrv.php',
    'description' => '',
    'type' => 'read',
  ),
);

$services = array (
  'exacompservices' => 
  array (
    'requiredcapability' => '',
    'restrictedusers' => 0,
    'enabled' => 1,
    'shortname' => 'exacompservices',
    'functions' => 
    array (
      0 => 'block_exacomp_get_courses',
      1 => 'block_exacomp_get_examples_for_subject',
      2 => 'block_exacomp_get_examples_for_subject_with_lfs_infos',
      3 => 'block_exacomp_get_example_by_id',
      4 => 'block_exacomp_get_descriptors_for_example',
      5 => 'block_exacomp_get_descriptors_for_quiz',
      6 => 'block_exacomp_get_user_role',
      7 => 'block_exacomp_diggr_get_user_role',
      8 => 'block_exacomp_get_external_trainer_students',
      9 => 'block_exacomp_get_subjects_for_user',
      10 => 'diggrplus_get_subjects_and_topics_for_user',
      11 => 'diggrplus_get_niveaus_for_subject',
      12 => 'block_exacomp_delete_item',
      13 => 'block_exacomp_set_competence',
      14 => 'block_exacomp_get_item_for_example',
      15 => 'block_exacomp_get_competencies_for_upload',
      16 => 'block_exacomp_submit_example',
      17 => 'block_exacomp_create_or_update_example',
      18 => 'diggrplus_create_or_update_example',
      19 => 'diggrplus_grade_descriptor',
      20 => 'diggrplus_import_ms_teams_students',
      21 => 'block_exacomp_grade_item',
      22 => 'block_exacomp_get_user_examples',
      23 => 'block_exacomp_get_user_profile',
      24 => 'block_exacomp_update_example',
      25 => 'block_exacomp_delete_example',
      26 => 'block_exacomp_get_competencies_by_topic',
      27 => 'dakora_set_competence',
      28 => 'dakora_get_courses',
      29 => 'dakora_get_topics_by_course',
      30 => 'dakora_get_all_topics_by_course',
      31 => 'dakora_get_descriptors',
      32 => 'dakora_get_all_descriptors',
      33 => 'dakora_get_descriptor_children',
      34 => 'dakora_get_examples_for_descriptor',
      35 => 'dakora_get_examples_for_descriptor_with_grading',
      36 => 'dakora_get_examples_for_descriptor_for_crosssubject',
      37 => 'dakora_get_examples_for_descriptor_for_crosssubject_with_grading',
      38 => 'dakora_get_example_overview',
      39 => 'diggrplus_get_example_overview',
      40 => 'dakora_add_example_to_learning_calendar',
      41 => 'dakora_get_descriptors_for_example',
      42 => 'dakora_get_example_grading',
      43 => 'dakora_get_topic_grading',
      44 => 'dakora_get_subject_grading',
      45 => 'dakora_get_user_role',
      46 => 'dakora_get_students_and_groups_for_course',
      47 => 'dakora_get_students_for_teacher',
      48 => 'dakora_get_teachers_for_student',
      49 => 'dakora_get_examples_pool',
      50 => 'dakora_get_examples_trash',
      51 => 'dakora_set_example_time_slot',
      52 => 'dakora_remove_example_from_schedule',
      53 => 'dakora_add_examples_to_schedule_for_all',
      54 => 'dakora_get_examples_for_time_slot',
      55 => 'dakora_get_cross_subjects_by_course',
      56 => 'dakora_get_descriptors_by_cross_subject',
      57 => 'dakora_get_all_descriptors_by_cross_subject',
      58 => 'dakora_get_descriptor_children_for_cross_subject',
      59 => 'dakora_get_all_descriptor_children_for_cross_subject',
      60 => 'dakora_get_schedule_config',
      61 => 'dakora_get_pre_planning_storage_examples',
      62 => 'dakora_get_pre_planning_storage_students',
      63 => 'dakora_get_pre_planning_storage_groups',
      64 => 'dakora_has_items_in_pre_planning_storage',
      65 => 'dakora_empty_pre_planning_storage',
      66 => 'dakora_add_example_to_pre_planning_storage',
      67 => 'dakora_add_examples_to_students_schedule',
      68 => 'dakora_add_examples_to_selected_students_schedule',
      69 => 'dakora_delete_examples_from_schedule',
      70 => 'dakora_undo_examples_from_schedule',
      71 => 'dakora_submit_example',
      72 => 'diggrplus_submit_item',
      73 => 'diggrplus_submit_item_comment',
      74 => 'diggrplus_get_item_comments',
      75 => 'diggrplus_get_examples_and_items',
      76 => 'diggrplus_get_teacher_examples_and_items',
      77 => 'diggrplus_get_all_subjects_for_course_as_tree',
      78 => 'diggrplus_get_user_info',
      79 => 'diggrplus_request_external_file',
      80 => 'diggrplus_grade_item',
      81 => 'diggrplus_get_competence_profile_statistic',
      82 => 'diggrplus_get_descriptors_for_example',
      83 => 'dakora_grade_example',
      84 => 'dakora_get_descriptors_details',
      85 => 'dakora_get_descriptor_details',
      86 => 'dakora_get_example_information',
      87 => 'dakora_get_user_information',
      88 => 'dakora_get_lang_information',
      89 => 'dakora_create_blocking_event',
      90 => 'dakora_get_examples_by_descriptor_and_grading',
      91 => 'dakora_allow_example_resubmission',
      92 => 'dakora_get_competence_grid_for_profile',
      93 => 'dakora_get_competence_profile_statistic',
      94 => 'dakora_get_competence_profile_comparison',
      95 => 'dakora_get_competence_profile_topic_statistic',
      96 => 'block_exacomp_is_elove_student_self_assessment_enabled',
      97 => 'dakora_get_site_policies',
      98 => 'block_exacomp_diggr_create_cohort',
      99 => 'block_exacomp_diggr_get_students_of_cohort',
      100 => 'block_exacomp_diggr_get_cohorts_of_trainer',
      101 => 'dakora_get_evaluation_config',
      102 => 'dakora_get_config',
      103 => 'dakora_get_courseconfigs',
      104 => 'block_exacomp_login',
      105 => 'dakora_set_descriptor_visibility',
      106 => 'dakora_set_example_visibility',
      107 => 'dakora_set_topic_visibility',
      108 => 'dakora_set_niveau_visibility',
      109 => 'dakora_set_example_solution_visibility',
      110 => 'diggrplus_set_descriptor_visibility',
      111 => 'diggrplus_set_example_visibility',
      112 => 'diggrplus_set_topic_visibility',
      113 => 'dakora_create_cross_subject',
      114 => 'dakora_delete_cross_subject',
      115 => 'dakora_edit_cross_subject',
      116 => 'dakora_get_cross_subject_drafts',
      117 => 'dakora_get_subjects',
      118 => 'dakora_get_students_for_cross_subject',
      119 => 'dakora_set_cross_subject_student',
      120 => 'dakora_set_cross_subject_descriptor',
      121 => 'dakora_dismiss_oldgrading_warning',
      122 => 'dakora_send_message',
      123 => 'block_exacomp_update_descriptor_category',
      124 => 'block_exacomp_get_url_preview',
      125 => 'dakora_competencegrid_overview',
      126 => 'dakora_delete_custom_example',
      127 => 'diggrplus_delete_custom_example',
      128 => 'diggrplus_get_course_schooltype_tree',
      129 => 'diggrplus_set_active_course_topics',
      130 => 'diggrplus_get_config',
      131 => 'diggrplus_annotate_example',
      132 => 'block_exacomp_diwipass_get_sections_with_materials',
      133 => 'diggrplus_v_edit_course',
      134 => 'diggrplus_v_create_or_update_student',
      135 => 'diggrplus_v_delete_student',
      136 => 'diggrplus_v_get_student_by_id',
      137 => 'diggrplus_v_get_student_grading_tree',
      138 => 'diggrplus_v_save_student_grading',
      139 => 'diggrplus_v_print_student_grading_report',
      140 => 'diggrplus_v_get_course_edulevel_schooltype_tree',
      141 => 'block_exacomp_diggrv_create_course',
    ),
    'downloadfiles' => 1,
    'uploadfiles' => 1,
  ),
);

