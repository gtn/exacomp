<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // usemoodleroot
    $options = array(
        'all'    => get_string('allcourses', 'block_course_list'),
        'own' => get_string('owncourses', 'block_course_list')
    );

    $settings->add(new admin_setting_configmulticheckbox(
               'competences',
               get_string('adminview', 'block_course_list'),
               'comp',
               'all',
               $options));
    

}