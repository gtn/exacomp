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

use block_exacomp\globals as g;

class block_exacomp_common_db_testcase extends advanced_testcase {
	protected function mock_setup() {
		global $DB;
	
		$this->resetAfterTest();

		// hide overwriting of $DB from eclipse
		$db = 'DB';
		${$db} = $this->getMock(get_class($DB));
	}

	public function test_update_record() {
		global $DB;
		$this->mock_setup();
	
		/* @var $DB PHPUnit_Framework_MockObject_MockObject */
		$DB->expects($this->at(0))
		->method('get_record')
		->with('table', ['id'=>1])
		->will($this->returnValue((object)['id'=>1, 'field'=>'original', 'someothervalue'=>123]));
		$DB->expects($this->at(1))
		->method('update_record')
		->with('table', (object)['id'=>1, 'field'=>'new']);
	
		$ret = g::$DB->update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new', 'someothervalue'=>123), $ret);
	}
	
	public function test_insert_or_update_record() {
		global $DB;
		$this->mock_setup();
		
		/* @var $DB PHPUnit_Framework_MockObject_MockObject */
		
		// simple update
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['id'=>1])
			->will($this->returnValue((object)['id'=>1, 'field'=>'original', 'someothervalue'=>123]));
		$DB->expects($this->at(1))
			->method('update_record')
			->with('table', (object)['id'=>1, 'field'=>'new']);
		
		$ret = g::$DB->insert_or_update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new', 'someothervalue'=>123), $ret);

		// simple insert
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['id'=>1])
			->will($this->returnValue(null));
		$DB->expects($this->at(1))
			->method('insert_record')
			->with('table', (object)['id'=>1, 'field'=>'new'])
			->will($this->returnValue(2));
		
		$ret = g::$DB->insert_or_update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>2, 'field'=>'new'), $ret);

		// update with new field value
		$DB->expects($this->at(0))
		->method('get_record')
		->with('table', ['field'=>'old'])
		->will($this->returnValue((object)['id'=>1, 'field'=>'old', 'someothervalue'=>123]));
		$DB->expects($this->at(1))
		->method('update_record')
		->with('table', (object)['id'=>1, 'field'=>'new']);
		
		$ret = g::$DB->insert_or_update_record('table', ['field'=>'new'], ['field'=>'old']);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new', 'someothervalue'=>123), $ret);
	
		// insert with new field value
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['field'=>'old'])
			->will($this->returnValue(null));
		$DB->expects($this->at(1))
			->method('insert_record')
			->with('table', (object)['field'=>'new'])
			->will($this->returnValue(2));
		
		$ret = g::$DB->insert_or_update_record('table', ['field'=>'new'], ['field'=>'old']);
		$this->assertEquals((object)array('id'=>2, 'field'=>'new'), $ret);
	}
}
