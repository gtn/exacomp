<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
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

require __DIR__.'/inc.php';

require_once('/Applications/MAMP/htdocs/simpletest/autorun.php');

class TestOfLib extends UnitTestCase {
	private $courseid;

	function setUp() {
		\block_exacomp\data::prepare();
		// 1. remove DB
		block_exacomp_truncate_all_data();
		// 2. import XML
		$data = file_get_contents("exacomp_data.xml");
		block_exacomp\data_importer::do_import($data);
		// 3. specify course id of the Moodle course where the block is used
		$this->courseid = 2;
		
		block_exacomp_set_mdltype(array(1));
	}
	
	/**
	 * In this test case topics from 1 Subject are selected. No activities are used
	 * 
	 * course selection: 	1 subjects
	 * assign activities: 	-
	 */
	function testGetSubjectsByCourse1() {
		// serialized value of the topic selection for the course
		$topics = 'a:6:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		
		$subjects = block_exacomp_get_subjects_by_course($this->courseid, true);
		
		$this->assertEqual(count($subjects), 1);
	}
	
	/**
	 * In this test case topics from 2 Subjects are selected. No activities are used
	 * 
	 * course selection: 	2 subjects
	 * assign activities: 	-
	 */
	function testGetSubjectsByCourse2() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		 
		$subjects = block_exacomp_get_subjects_by_course($this->courseid, true);
		 
		$this->assertEqual(count($subjects), 2);
	}
	
	/**
	 * In this test case topics from 2 Subjects are selected, and one descriptor is associated with acitvities
	 * 
	 * course selection: 	2 subjects
	 * assign activities: 	1 subject
	 */
	function testGetSubjectsByCourse3() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		 
		$subjects = block_exacomp_get_subjects_by_course($this->courseid, false);
	
		$this->assertEqual(count($subjects), 1);
	}
	
	/**
	 * In this test case topics from 2 Subjects are selected, and one topic is associated with acitvities
	 *
	 * course selection: 	2 subjects
	 * assign activities: 	1 subject
	 */
	function testGetSubjectsByCourse4() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$subjects = block_exacomp_get_subjects_by_course($this->courseid, false);
	
		$this->assertEqual(count($subjects), 1);
	}
	
	/**
	 * In this test case 8 topics from 2 subjects are selected. Subject 1 has 6 topics. No actvities are used
	 */
	function testGetTopicsBySubject1() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		
		$topics = block_exacomp_get_topics_by_subject($this->courseid, 1, true);
		
		$this->assertEqual(count($topics), 6);
	}
	
	/**
	 * In this test case 8 topics from 2 subjects are selected. Subject 1 has 6 topics. Actvities are used, and 3 topics should be returned
	 */
	function testGetTopicsBySubject2() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		 
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		
		$topics = block_exacomp_get_topics_by_subject($this->courseid, 1);
		 
		$this->assertEqual(count($topics), 1);
	}
	
	/**
	 * In this test case 1 descriptor is associated with an activity, therefore 1 topic should be returned
	 * 
	 * 1 DESCRIPTOR
	 */
	function testGetTopicsByCourse1() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		 
		$topics = block_exacomp_get_topics_by_course($this->courseid);
	
		$this->assertEqual(count($topics), 1);
	}
	
	/**
	 * In this test case 1 topic is associated with an activity, therefore 1 topic should be returned
	 * 
	 * 1 TOPIC
	 */
	function testGetTopicsByCourse2() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$topics = block_exacomp_get_topics_by_course($this->courseid);
	
		$this->assertEqual(count($topics), 1);
	}
	
	/**
	 * 1 TOPIC, 1 TOPIC WITH PARENT, 1 DESCRIPTOR BUT WITHIN SELECTED TOPIC
	 * RETURNED: 2
	 */
	function testGetTopicsByCourse3() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		
		$activities = 'a:1:{i:2;a:2:{i:1;s:0:"";i:8;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$topics = block_exacomp_get_topics_by_course($this->courseid);
	
		$this->assertEqual(count($topics), 2);
	}
	
	/**
	 * 1 TOPIC, 1 DESCRIPTOR
	 * RETURNED: 2
	 */
	function testGetTopicsByCourse4() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		 
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$topics = block_exacomp_get_topics_by_course($this->courseid);
	
		$this->assertEqual(count($topics), 2);
	}
	
	/**
	 * 1 DESCRIPTOR
	 * RETURNED: 1
	 */
	function testGetDescriptorsByCourse1() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:1:{i:2;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		
		$descriptors = block_exacomp_get_descriptors($this->courseid);
	
		$this->assertEqual(count($descriptors), 1);
	}
	/**
	 * 1 DESCRIPTOR to 2 ACTIVITIES
	 * RETURNED: 1
	 */
	function testGetDescriptorsByCourse2() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:2:{i:2;a:1:{i:12;s:0:"";}i:3;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
		 
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		 
		$descriptors = block_exacomp_get_descriptors($this->courseid);
	
		$this->assertEqual(count($descriptors), 1);
	}
	/**
	 * 2 DESCRIPTOR to 2 ACTIVITIES
	 * RETURNED: 2
	 */
	function testGetDescriptorsByCourse3() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:3:{i:2;a:1:{i:12;s:0:"";}i:3;a:1:{i:12;s:0:"";}i:4;a:1:{i:15;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$descriptors = block_exacomp_get_descriptors($this->courseid);
	
		$this->assertEqual(count($descriptors), 2);
	}
	/**
	 * Topic with Parent and Descriptors
	 */
	function testGetCompetenceTree1() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		
		$activities = 'a:1:{i:2;a:1:{i:8;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		
		$data = block_exacomp_get_competence_tree($this->courseid);
		
		$this->assertEqual(count($data), 1);
	}
	/**
	 * Topic with Descriptors but no parent topic
	 */
	function testGetCompetenceTree2() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		 
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		 
		$data = block_exacomp_get_competence_tree($this->courseid);
		 
		$this->assertEqual(count($data), 1);
	}
	/**
	 * Topic and Descriptors from other topic, 2 expected
	 */
	function testGetCompetenceTree3() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:2:{i:2;a:1:{i:12;s:0:"";}i:3;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data[1]->subs), 2);
	}
	/**
	 * Only Descriptor from one topic, 1 expected
	 */
	function testGetCompetenceTree4() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:2:{i:2;a:1:{i:12;s:0:"";}i:3;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data[1]->subs), 1);
	}
	/**
	 * Descriptors from 2 topics, 2 expected
	 */
	function testGetCompetenceTree5() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:2:{i:2;a:2:{i:1;s:0:"";i:12;s:0:"";}i:3;a:1:{i:12;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data[1]->subs), 2);
	}
	/**
	 * Descriptors from a topic and the topic itself
	 */
	function testGetCompetenceTree6() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:3:{i:2;a:1:{i:1;s:0:"";}i:3;a:1:{i:2;s:0:"";}i:4;a:1:{i:3;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data[1]->subs), 1);
	}
	/**
	 * Descriptors from 2 topics from 2 different subjects
	 */
	function testGetCompetenceTree7() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = 'a:3:{i:2;a:3:{i:1;s:0:"";i:22;s:0:"";i:23;s:0:"";}i:3;a:1:{i:2;s:0:"";}i:4;a:1:{i:3;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$activities = 'a:1:{i:2;a:1:{i:1;s:0:"";}}';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
		 
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data), 2);
	}
	/**
	 * no activities selected
	 */
	function testGetCompetenceTree8() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
	
		$activities = '';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
	
		$activities = '';
		block_exacomp_save_competences_activities(unserialize($activities), $this->courseid, BLOCK_EXACOMP_TYPE_TOPIC);
	
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data), 0);
	}
	/**
	 * show all descriptors
	 */
	function testGetCompetenceTree9() {
		// serialized value of the topic selection for the course
		$topics = 'a:8:{i:1;s:1:"1";i:6;s:1:"6";i:8;s:1:"8";i:9;s:1:"9";i:7;s:1:"7";i:2;s:1:"2";i:13;s:2:"13";i:14;s:2:"14";}';
		$topicData = unserialize($topics);
		block_exacomp_set_coursetopics($this->courseid, $topicData);
		$settings = block_exacomp_get_settings_by_course($this->courseid);
		$settings->show_all_descriptors = true;
		block_exacomp_save_coursesettings($this->courseid, $settings);
	
		$data = block_exacomp_get_competence_tree($this->courseid);
	
		$this->assertEqual(count($data[1]->subs) + count($data[2]->subs), 4);
	}
}
