<?php

$functions = array (
  'diggrplus_set_item_status' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\competence_grid',
    'methodname' => 'diggrplus_set_item_status',
    'description' => 'set the item status',
    'type' => 'write',
  ),
  'block_exacomp_get_courses' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_courses',
    'description' => 'Get courses with exacomp block instances.
get courses',
    'type' => 'read',
  ),
  'block_exacomp_get_examples_for_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_examples_for_subject',
    'description' => 'Get examples for subtopic
Get examples',
    'type' => 'read',
  ),
  'block_exacomp_get_examples_for_subject_with_lfs_infos' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_examples_for_subject_with_lfs_infos',
    'description' => 'Get examples for subtopic
Get examples',
    'type' => 'read',
  ),
  'block_exacomp_get_example_by_id' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_example_by_id',
    'description' => 'Get example
Get example',
    'type' => 'read',
  ),
  'block_exacomp_get_descriptors_for_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_descriptors_for_example',
    'description' => 'Get desciptors for example
Get descriptors for example',
    'type' => 'read',
  ),
  'block_exacomp_get_descriptors_for_quiz' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_descriptors_for_quiz',
    'description' => 'Get desciptors for quiz
Get descriptors for quiz',
    'type' => 'read',
  ),
  'block_exacomp_get_user_role' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_user_role',
    'description' => 'Get role for user: 1=trainer 2=student',
    'type' => 'read',
  ),
  'block_exacomp_diggr_get_user_role' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggr_get_user_role',
    'description' => 'Get role for user: 1=trainer 2=student',
    'type' => 'read',
  ),
  'block_exacomp_get_external_trainer_students' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_external_trainer_students',
    'description' => 'Get external trainer\'s students
Get all students for an external trainer',
    'type' => 'read',
  ),
  'block_exacomp_get_subjects_for_user' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_subjects_for_user',
    'description' => 'Get Subjects
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'diggrplus_get_subjects_and_topics_for_user' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_subjects_and_topics_for_user',
    'description' => 'Get Subjects
get subjects from one user for all his courses or for one specific course.',
    'type' => 'read',
  ),
  'diggrplus_get_niveaus_for_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_niveaus_for_subject',
    'description' => 'Get Subjects
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'block_exacomp_delete_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'delete_item',
    'description' => 'delete a submitted and wrong item
Deletes one user item if it is not graded already',
    'type' => 'write',
  ),
  'block_exacomp_set_competence' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'set_competence',
    'description' => 'Set a student evaluation for a particular competence
Set student evaluation',
    'type' => 'write',
  ),
  'block_exacomp_get_item_for_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_item_for_example',
    'description' => 'Get Item
get subjects from one user for all his courses',
    'type' => 'read',
  ),
  'block_exacomp_get_competencies_for_upload' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_competencies_for_upload',
    'description' => 'Get competencetree
Get all available competencies',
    'type' => 'read',
  ),
  'block_exacomp_submit_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'submit_example',
    'description' => 'Submit example
submit example for elove and diggr
Add item',
    'type' => 'read',
  ),
  'block_exacomp_create_or_update_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'create_or_update_example',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_create_or_update_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_create_or_update_example',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_grade_descriptor' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_grade_descriptor',
    'description' => 'Grade a descriptor',
    'type' => 'write',
  ),
  'diggrplus_grade_element' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_grade_element',
    'description' => 'Grade a element',
    'type' => 'write',
  ),
  'diggrplus_grade_competency' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_grade_competency',
    'description' => 'Grade a element',
    'type' => 'write',
  ),
  'diggrplus_get_all_competency_gradings' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_all_competency_gradings',
    'description' => 'Get all gradings in all courses',
    'type' => 'write',
  ),
  'diggrplus_msteams_import_students' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_msteams_import_students',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_msteams_get_access_token' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_msteams_get_access_token',
    'description' => '',
    'type' => 'write',
  ),
  'block_exacomp_grade_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'grade_item',
    'description' => 'Grade an item
grade an item',
    'type' => 'write',
  ),
  'block_exacomp_get_user_examples' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_user_examples',
    'description' => 'get examples created by a specific user
grade an item',
    'type' => 'read',
  ),
  'block_exacomp_get_user_profile' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_user_profile',
    'description' => 'get a list of courses with their competencies',
    'type' => 'read',
  ),
  'block_exacomp_update_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'update_example',
    'description' => 'update an example',
    'type' => 'write',
  ),
  'block_exacomp_delete_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'delete_example',
    'description' => 'delete a custom item
delete example',
    'type' => 'write',
  ),
  'block_exacomp_get_competencies_by_topic' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_competencies_by_topic',
    'description' => 'get competencies for a specific topic
Get all available competencies',
    'type' => 'read',
  ),
  'dakora_set_competence' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_competence',
    'description' => 'set competence for student
Set a competence for a user',
    'type' => 'write',
  ),
  'dakora_get_courses' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_courses',
    'description' => 'get courses for user for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_topics_by_course' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_topics_by_course',
    'description' => 'get topics for course for dakora app associated with examples
get courses',
    'type' => 'read',
  ),
  'dakora_get_all_topics_by_course' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_all_topics_by_course',
    'description' => 'get topics for course for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_descriptors' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptors',
    'description' => 'get descriptors for topic for dakora app associated with examples
get descriptors for one topic, considering the visibility',
    'type' => 'read',
  ),
  'dakora_get_all_descriptors' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_all_descriptors',
    'description' => 'get descriptors for topic for dakora app
get courses',
    'type' => 'read',
  ),
  'dakora_get_descriptor_children' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptor_children',
    'description' => 'get children (childdescriptor and examples) for descriptor for dakora app (only childs associated with examples)
get courses',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_for_descriptor',
    'description' => 'get examples for descriptor for dakora app',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_with_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_for_descriptor_with_grading',
    'description' => 'get examples for descriptor with additional grading information',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_for_crosssubject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_for_descriptor_for_crosssubject',
    'description' => 'get examples for descriptor for dakora app',
    'type' => 'read',
  ),
  'dakora_get_examples_for_descriptor_for_crosssubject_with_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_for_descriptor_for_crosssubject_with_grading',
    'description' => 'get examples for descriptor with additional grading information',
    'type' => 'read',
  ),
  'dakora_get_example_overview' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_example_overview',
    'description' => 'get example overview for dakora app',
    'type' => 'read',
  ),
  'diggrplus_get_example_overview' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_example_overview',
    'description' => 'get example overview for dakora app',
    'type' => 'read',
  ),
  'dakora_add_example_to_learning_calendar' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_add_example_to_learning_calendar',
    'description' => 'add example to learning calendar for dakora
get courses',
    'type' => 'write',
  ),
  'dakora_get_descriptors_for_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptors_for_example',
    'description' => 'get descriptors where example is associated
Get descriptors for example',
    'type' => 'read',
  ),
  'dakora_get_example_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_example_grading',
    'description' => 'get student and teacher evaluation for example
Get example grading for user',
    'type' => 'read',
  ),
  'dakora_get_topic_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_topic_grading',
    'description' => 'get student and teacher evaluation for topic
Get topic grading for user',
    'type' => 'read',
  ),
  'dakora_get_subject_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_subject_grading',
    'description' => 'get student and teacher evaluation for subject
Get subject grading for user',
    'type' => 'read',
  ),
  'dakora_get_user_role' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_user_role',
    'description' => 'get user role 1= trainer, 2= student
return 1 for trainer
2 for student
0 if false',
    'type' => 'read',
  ),
  'dakora_get_students_and_groups_for_course' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_students_and_groups_for_course',
    'description' => 'get list of students for course',
    'type' => 'read',
  ),
  'dakora_get_students_for_teacher' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_students_for_teacher',
    'description' => 'get list of students that are enrolled in any course of a teacher',
    'type' => 'read',
  ),
  'dakora_get_teachers_for_student' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_teachers_for_student',
    'description' => 'get list of teachers in any course of the student',
    'type' => 'read',
  ),
  'dakora_get_examples_pool' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_pool',
    'description' => 'get list of examples for weekly schedule pool
Get examples for pool',
    'type' => 'read',
  ),
  'dakora_get_examples_trash' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_trash',
    'description' => 'get examples for trash bin
Get examples for trash',
    'type' => 'read',
  ),
  'dakora_set_example_time_slot' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_example_time_slot',
    'description' => 'set start and end time for example
set example time slot',
    'type' => 'write',
  ),
  'dakora_remove_example_from_schedule' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_remove_example_from_schedule',
    'description' => 'remove example from weekly schedule
remove example from time slot',
    'type' => 'write',
  ),
  'dakora_add_examples_to_schedule_for_all' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_add_examples_to_schedule_for_all',
    'description' => 'add examples to the schedules of all course students
remove example from time slot',
    'type' => 'write',
  ),
  'dakora_get_examples_for_time_slot' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_for_time_slot',
    'description' => 'get examples for a special start to end period (e.g. day)
Get examples for time slot',
    'type' => 'read',
  ),
  'dakora_get_cross_subjects_by_course' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_cross_subjects_by_course',
    'description' => 'get cross subjects for an user in course context (allways all crosssubjs, even if not associated)
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_descriptors_by_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptors_by_cross_subject',
    'description' => 'get descriptors for a cross subject associated with examples
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_all_descriptors_by_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_all_descriptors_by_cross_subject',
    'description' => 'get descriptors for a cross subject
Get cross subjects',
    'type' => 'read',
  ),
  'dakora_get_descriptor_children_for_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptor_children_for_cross_subject',
    'description' => 'get children in context of cross subject, associated with examples
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_all_descriptor_children_for_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_all_descriptor_children_for_cross_subject',
    'description' => 'get children in context of cross subject
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_schedule_config' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_schedule_config',
    'description' => 'get configuration options for schedule units
get children for descriptor in cross subject context',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_examples' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_pre_planning_storage_examples',
    'description' => 'get examples for pre planning storage
get pre planning storage examples for current teacher',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_students' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_pre_planning_storage_students',
    'description' => 'get students for pre planning storage
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_get_pre_planning_storage_groups' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_pre_planning_storage_groups',
    'description' => 'get students for pre planning storage
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_has_items_in_pre_planning_storage' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_has_items_in_pre_planning_storage',
    'description' => 'return 0 if no items, 1 otherwise
get pre planning storage students for current teacher',
    'type' => 'read',
  ),
  'dakora_empty_pre_planning_storage' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_empty_pre_planning_storage',
    'description' => 'delte all items from current pre planning storage
empty pre planning storage for current teacher',
    'type' => 'write',
  ),
  'dakora_add_example_to_pre_planning_storage' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_add_example_to_pre_planning_storage',
    'description' => 'add example to current pre planning storage
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_add_examples_to_students_schedule' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_add_examples_to_students_schedule',
    'description' => 'add examples from current pre planning storage to students weekly schedule
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_add_examples_to_selected_students_schedule' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_add_examples_to_selected_students_schedule',
    'description' => 'add examples from current pre planning storage to students weekly schedule
add example to current pre planning storage',
    'type' => 'write',
  ),
  'dakora_delete_examples_from_schedule' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_delete_examples_from_schedule',
    'description' => 'remove example from weekly schedule by teacherid and distribution id
used for \'undo\' button',
    'type' => 'write',
  ),
  'dakora_undo_examples_from_schedule' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_undo_examples_from_schedule',
    'description' => 'remove example from weekly schedule by teacherid and distribution id
used for \'undo\' button',
    'type' => 'write',
  ),
  'dakora_submit_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_submit_example',
    'description' => 'submit example solution
Add student submission to example.',
    'type' => 'write',
  ),
  'diggrplus_submit_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_submit_item',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_submit_item_comment' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_submit_item_comment',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_get_item_comments' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_item_comments',
    'description' => 'Add studentsubmission  (exaportitem) to topic, descriptor or example',
    'type' => 'write',
  ),
  'diggrplus_get_examples_and_items' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_examples_and_items',
    'description' => 'Get Items
get all items AND examples for a competence
they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend',
    'type' => 'read',
  ),
  'diggrplus_get_teacher_examples_and_items' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_teacher_examples_and_items',
    'description' => 'Get Items
get all items AND examples for a competence
they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend',
    'type' => 'read',
  ),
  'diggrplus_get_all_subjects_for_course_as_tree' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_all_subjects_for_course_as_tree',
    'description' => 'Get Subjects
get subjects from one user for one course',
    'type' => 'read',
  ),
  'diggrplus_get_user_info' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_user_info',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_request_external_file' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_request_external_file',
    'description' => 'Load a file from an external Domain to prevent CORS when loading directly in the App',
    'type' => 'read',
  ),
  'diggrplus_grade_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_grade_item',
    'description' => 'teacher grades and item in diggrplus',
    'type' => 'write',
  ),
  'diggrplus_get_competence_profile_statistic' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_competence_profile_statistic',
    'description' => 'Get competence statistic for profile',
    'type' => 'read',
  ),
  'diggrplus_get_descriptors_for_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_descriptors_for_example',
    'description' => 'get descriptors where example is associated
Get descriptors for example',
    'type' => 'read',
  ),
  'dakora_grade_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_grade_example',
    'description' => 'grade example solution
Add student submission to example.',
    'type' => 'write',
  ),
  'dakora_get_descriptors_details' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptors_details',
    'description' => 'get descriptor details incl. grading and children for many descriptors',
    'type' => 'read',
  ),
  'dakora_get_descriptor_details' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_descriptor_details',
    'description' => 'get descriptor details incl. grading and children',
    'type' => 'read',
  ),
  'dakora_get_example_information' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_example_information',
    'description' => 'get information and submission for example
get example with all submission details and gradings',
    'type' => 'read',
  ),
  'dakora_get_user_information' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_user_information',
    'description' => 'get information about current user
get example with all submission details and gradings',
    'type' => 'read',
  ),
  'dakora_get_lang_information' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_lang_information',
    'description' => 'Returns lang information from exacomp',
    'type' => 'read',
  ),
  'dakora_create_blocking_event' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_create_blocking_event',
    'description' => 'create a blocking event
Create a new blocking event',
    'type' => 'write',
  ),
  'dakora_get_examples_by_descriptor_and_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_examples_by_descriptor_and_grading',
    'description' => 'returns examples for given descriptor and grading
Create a new blocking event',
    'type' => 'read',
  ),
  'dakora_allow_example_resubmission' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_allow_example_resubmission',
    'description' => 'allow student to resubmit example
Create a new blocking event',
    'type' => 'read',
  ),
  'dakora_get_competence_grid_for_profile' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_competence_grid_for_profile',
    'description' => 'get grid for profile
Get competence grid for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_statistic' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_competence_profile_statistic',
    'description' => 'get statistic in user and subject context
Get competence statistic for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_comparison' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_competence_profile_comparison',
    'description' => 'get list for student and teacher comparison
Get competence comparison for profile',
    'type' => 'read',
  ),
  'dakora_get_competence_profile_topic_statistic' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_competence_profile_topic_statistic',
    'description' => 'get data for 3D graph
Get competence statistic for topic in profile for 3D graph',
    'type' => 'read',
  ),
  'block_exacomp_is_elove_student_self_assessment_enabled' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'is_elove_student_self_assessment_enabled',
    'description' => 'check the corresponding config setting',
    'type' => 'read',
  ),
  'dakora_get_site_policies' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_site_policies',
    'description' => '',
    'type' => 'read',
  ),
  'dakora_get_example_h5p_activity_results' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_example_h5p_activity_results',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_diggr_create_cohort' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggr_create_cohort',
    'description' => 'Create one or more cohorts',
    'type' => 'write',
  ),
  'block_exacomp_diggr_get_students_of_cohort' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggr_get_students_of_cohort',
    'description' => 'Create one or more cohorts',
    'type' => 'read',
  ),
  'block_exacomp_diggr_get_cohorts_of_trainer' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggr_get_cohorts_of_trainer',
    'description' => 'Create one or more cohorts',
    'type' => 'read',
  ),
  'dakora_get_evaluation_config' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_evaluation_config',
    'description' => 'get evaluation configuration
get admin evaluation configurations',
    'type' => 'read',
  ),
  'dakora_get_config' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_config',
    'description' => '',
    'type' => 'read',
  ),
  'dakora_get_courseconfigs' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_courseconfigs',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_login' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'login',
    'description' => 'webservice called through token.php',
    'type' => 'read',
  ),
  'dakora_set_descriptor_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_descriptor_visibility',
    'description' => 'set visibility for descriptor',
    'type' => 'write',
  ),
  'dakora_set_example_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_example_visibility',
    'description' => 'set visibility for example',
    'type' => 'write',
  ),
  'dakora_set_topic_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_topic_visibility',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_set_niveau_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_niveau_visibility',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_set_example_solution_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_example_solution_visibility',
    'description' => 'set visibility for example solutions',
    'type' => 'write',
  ),
  'diggrplus_set_descriptor_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_set_descriptor_visibility',
    'description' => 'set visibility for descriptor',
    'type' => 'write',
  ),
  'diggrplus_set_example_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_set_example_visibility',
    'description' => 'set visibility for example',
    'type' => 'write',
  ),
  'diggrplus_set_topic_visibility' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_set_topic_visibility',
    'description' => 'set visibility for topic',
    'type' => 'write',
  ),
  'dakora_create_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_create_cross_subject',
    'description' => 'create new crosssubject',
    'type' => 'write',
  ),
  'dakora_delete_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_delete_cross_subject',
    'description' => 'delete cross subject',
    'type' => 'write',
  ),
  'dakora_edit_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_edit_cross_subject',
    'description' => 'edit existing crosssubject',
    'type' => 'write',
  ),
  'dakora_get_cross_subject_drafts' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_cross_subject_drafts',
    'description' => 'get available drafts',
    'type' => 'read',
  ),
  'dakora_get_subjects' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_subjects',
    'description' => 'get subjects',
    'type' => 'read',
  ),
  'dakora_get_students_for_cross_subject' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_get_students_for_cross_subject',
    'description' => 'get_students_for_crosssubject',
    'type' => 'read',
  ),
  'dakora_set_cross_subject_student' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_cross_subject_student',
    'description' => 'set visibility for crosssubject and student',
    'type' => 'write',
  ),
  'dakora_set_cross_subject_descriptor' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_set_cross_subject_descriptor',
    'description' => 'set descriptor crosssubject association',
    'type' => 'write',
  ),
  'dakora_dismiss_oldgrading_warning' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_dismiss_oldgrading_warning',
    'description' => 'set descriptor crosssubject association',
    'type' => 'write',
  ),
  'dakora_send_message' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_send_message',
    'description' => 'send message',
    'type' => 'write',
  ),
  'block_exacomp_update_descriptor_category' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'update_descriptor_category',
    'description' => 'update an descriptor category',
    'type' => 'write',
  ),
  'block_exacomp_get_url_preview' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_url_preview',
    'description' => 'gets title description and image of website',
    'type' => 'read',
  ),
  'dakora_competencegrid_overview' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_competencegrid_overview',
    'description' => 'view competence overview',
    'type' => 'read',
  ),
  'dakora_delete_custom_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakora_delete_custom_example',
    'description' => 'delete example',
    'type' => 'write',
  ),
  'diggrplus_delete_custom_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_delete_custom_example',
    'description' => 'delete example',
    'type' => 'write',
  ),
  'diggrplus_get_course_schooltype_tree' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_course_schooltype_tree',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_set_active_course_topics' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_set_active_course_topics',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_get_config' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_config',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_annotate_example' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_annotate_example',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_get_student_enrolcode' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_get_student_enrolcode',
    'description' => 'get active code for student enrollment',
    'type' => 'read',
  ),
  'diggrplus_create_student_enrolcode' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_create_student_enrolcode',
    'description' => 'Create new enrolcode and delete old ones',
    'type' => 'write',
  ),
  'diggrplus_enrol_by_enrolcode' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diggrplus_enrol_by_enrolcode',
    'description' => 'Use a QR-Code to enrol',
    'type' => 'write',
  ),
  'block_exacomp_diwipass_get_sections_with_materials' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'diwipass_get_sections_with_materials',
    'description' => 'Get urls and resources per section for every course of current user',
    'type' => 'write',
  ),
  'dakoraplus_get_example_and_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakoraplus_get_example_and_item',
    'description' => '',
    'type' => 'read',
  ),
  'dakoraplus_get_teacher_example_and_item' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakoraplus_get_teacher_example_and_item',
    'description' => '',
    'type' => 'read',
  ),
  'dakoraplus_save_coursesettings' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakoraplus_save_coursesettings',
    'description' => '',
    'type' => 'write',
  ),
  'dakoraplus_get_learning_diary' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakoraplus_get_learning_diary',
    'description' => '',
    'type' => 'read',
  ),
  'dakoraplus_save_learning_diary_entry' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'dakoraplus_save_learning_diary_entry',
    'description' => '',
    'type' => 'write',
  ),
  'block_exacomp_get_lang' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\externallib',
    'methodname' => 'get_lang',
    'description' => 'Get language definitions in json format for diggr-plus and dakora-plus apps',
    'type' => 'read',
  ),
  'diggrplus_learningpath_list' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_list',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_learningpath_details' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_details',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_learningpath_add' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_add',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_delete' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_delete',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_update' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_update',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_item_update' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_item_update',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_add_items' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_add_items',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_item_delete' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_item_delete',
    'description' => '',
    'type' => 'write',
  ),
  'diggrplus_learningpath_item_sorting' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\learningpaths',
    'methodname' => 'diggrplus_learningpath_item_sorting',
    'description' => '',
    'type' => 'write',
  ),
  'dakoraplus_create_report' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\reports',
    'methodname' => 'dakoraplus_create_report',
    'description' => '',
    'type' => 'read',
  ),
  'block_exacomp_get_fullcompetence_grid_for_profile' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\reports',
    'methodname' => 'get_fullcompetence_grid_for_profile',
    'description' => 'Returns full competence grid data for needed profile. (NO crossubjects data).
Useful in next HTML generation
Based and have similar output as in \'dakora_get_competence_grid_for_profile\', but right now is used only for skillswork needs',
    'type' => 'read',
  ),
  'diggrplus_v_edit_course' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_edit_course',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_create_or_update_student' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_create_or_update_student',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_delete_student' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_delete_student',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_get_student_by_id' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_get_student_by_id',
    'description' => 'Create an example or update it
create example',
    'type' => 'write',
  ),
  'diggrplus_v_get_student_grading_tree' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_get_student_grading_tree',
    'description' => 'Get competence statistic for profile',
    'type' => 'read',
  ),
  'diggrplus_v_save_student_grading' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_save_student_grading',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_v_print_student_grading_report' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_print_student_grading_report',
    'description' => '',
    'type' => 'read',
  ),
  'diggrplus_v_get_course_edulevel_schooltype_tree' => 
  array (
    'classname' => '\\block_exacomp\\externallib\\setapp',
    'methodname' => 'diggrplus_v_get_course_edulevel_schooltype_tree',
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
    'functions' => array_keys($functions),
    'downloadfiles' => 1,
    'uploadfiles' => 1,
  ),
);

