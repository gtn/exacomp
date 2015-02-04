<?php
$services = array(
		'exacompservices' => array(                        //the name of the web service
				'functions' => array (
						'block_exacomp_get_courses',
						'block_exacomp_get_subjects',
						'block_exacomp_get_topics',
						'block_exacomp_get_subtopics',
						'block_exacomp_set_subtopic',
						'block_exacomp_get_competencies',
						'block_exacomp_set_competence',
						'block_exacomp_get_associated_content',
						'block_exacomp_get_assign_information',
						'block_exacomp_update_assign_submission',
						'block_exacomp_get_competence_by_id',
						'block_exacomp_get_topic_by_id',
						'block_exacomp_get_subtopics_by_topic',
                        'block_exacomp_get_examples_by_subtopic'), 	//web service functions of this service
				'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
				//into the administration
				'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
		)
);


$functions = array(
		'block_exacomp_get_courses' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_courses',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get courses with exacomp block instances.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		
		'block_exacomp_get_subjects' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subjects',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get active subjects for a given course. Will usualy return exactly 1 subject, if so no dropdown selection for the user is neccessary',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_topics' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_topics',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get topics for a given subject.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_subtopics' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subtopics',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopics for a given topic.',    //human readable description of the web service function
				'type'        => 'read',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_set_subtopic' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'set_subtopic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Set a users subtopic evaluation.',    //human readable description of the web service function
				'type'        => 'write',                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_competencies' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competencies',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopic competencies and teacher/student evaluation.',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_set_competence' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'set_competence',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Set a student evaluation for a particular competence',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_associated_content' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_associated_content',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get contents for a competence (exaport,example,assign)',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_assign_information' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_assign_information',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get information about a particular assign',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_update_assign_submission' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'update_assign_submission',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Updates info for particular assign',    //human readable description of the web service function
				'type'        => 'write'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_competence_by_id' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_competence_by_id',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get competence title',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		)
		,
		'block_exacomp_get_topic_by_id' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_topic_by_id',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get topic title',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_subtopics_by_topic' => array(         //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_subtopics_by_topic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get subtopics by topic id',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		),
		'block_exacomp_get_examples_by_subtopic' => array(     //web service function name
				'classname'   => 'block_exacomp_external',  //class containing the external function
				'methodname'  => 'get_examples_by_subtopic',          //external function name
				'classpath'   => 'blocks/exacomp/externallib.php',  //file containing the class/external function
				'description' => 'Get examples for subtopic',    //human readable description of the web service function
				'type'        => 'read'                  //database rights of the web service function (read, write)
		)
);