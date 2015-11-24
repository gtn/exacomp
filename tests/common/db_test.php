<?php

require_once __DIR__.'/inc.php';

class block_exacomp_common_db_testcase extends advanced_testcase {
	protected function mock_setup() {
		global $DB;
	
		$this->resetAfterTest();
	
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
		->will($this->returnValue((object)['id'=>1, 'field'=>'original']));
		$DB->expects($this->at(1))
		->method('update_record')
		->with('table', ['id'=>1, 'field'=>'new']);
	
		$ret = block_exacomp\common\db::update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new'), $ret);
	}
	
	public function test_insert_or_update_record() {
		global $DB;
		$this->mock_setup();
		
		/* @var $DB PHPUnit_Framework_MockObject_MockObject */
		
		// simple update
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['id'=>1])
			->will($this->returnValue((object)['id'=>1, 'field'=>'original']));
		$DB->expects($this->at(1))
			->method('update_record')
			->with('table', ['id'=>1, 'field'=>'new']);
		
		$ret = block_exacomp\common\db::insert_or_update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new'), $ret);

		// simple insert
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['id'=>1])
			->will($this->returnValue(null));
		$DB->expects($this->at(1))
			->method('insert_record')
			->with('table', ['id'=>1, 'field'=>'new'])
			->will($this->returnValue(2));
		
		$ret = block_exacomp\common\db::insert_or_update_record('table', ['field'=>'new'], ['id'=>1]);
		$this->assertEquals((object)array('id'=>2, 'field'=>'new'), $ret);

		// update with new field value
		$DB->expects($this->at(0))
		->method('get_record')
		->with('table', ['field'=>'old'])
		->will($this->returnValue((object)['id'=>1, 'field'=>'old']));
		$DB->expects($this->at(1))
		->method('update_record')
		->with('table', ['id'=>1, 'field'=>'new']);
		
		$ret = block_exacomp\common\db::insert_or_update_record('table', ['field'=>'new'], ['field'=>'old']);
		$this->assertEquals((object)array('id'=>1, 'field'=>'new'), $ret);
	
		// insert with new field value
		$DB->expects($this->at(0))
			->method('get_record')
			->with('table', ['field'=>'old'])
			->will($this->returnValue(null));
		$DB->expects($this->at(1))
			->method('insert_record')
			->with('table', ['field'=>'new'])
			->will($this->returnValue(2));
		
		$ret = block_exacomp\common\db::insert_or_update_record('table', ['field'=>'new'], ['field'=>'old']);
		$this->assertEquals((object)array('id'=>2, 'field'=>'new'), $ret);
	}
}