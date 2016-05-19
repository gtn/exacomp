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

class block_exacomp_common_param_testcase extends basic_testcase {
	public function test_clean_object() {
		$ret = block_exacomp\common\param::clean_object(['key'=>'asdf', 'more'=>'asdf'], ['key'=>PARAM_INT]);
		$this->assertEquals((object)['key'=>0], $ret);
		
		$ret = block_exacomp\common\param::clean_object(['key'=>'asdf', 'more'=>'asdf'], ['key'=>PARAM_TEXT]);
		$this->assertEquals((object)['key'=>'asdf'], $ret);
		
		$ret = block_exacomp\common\param::clean_object(['key'=>'asdf', 'more'=>'asdf'], ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals((object)['key'=>'asdf', 'missing'=>0], $ret);

		// not object
		$ret = block_exacomp\common\param::clean_object('something', ['key'=>PARAM_INT]);
		$this->assertEquals(null, $ret);

		// array(object) with faulty objects
		$ret = block_exacomp\common\param::clean_array(['key'=>'dffdf', ['x'=>'y', 'key'=>'123']], [(object)['key'=>PARAM_INT]]);
		$this->assertEquals([0=>(object)['key'=>123]], $ret);
		
		// object->object with faulty objects
		$ret = block_exacomp\common\param::clean_object(['obj'=>['x'=>'y', 'key'=>'123']], ['obj'=>(object)['key'=>PARAM_INT]]);
		$this->assertEquals((object)['obj'=>(object)['key'=>123]], $ret);
		$ret = block_exacomp\common\param::clean_object(['obj'=>'asf'], ['obj'=>(object)['key'=>PARAM_INT]]);
		$this->assertEquals((object)['obj'=>null], $ret);
	}

	public function test_clean_array() {
		$ret = block_exacomp\common\param::clean_array(['key'=>'asdf', 'more'=>'11'], [PARAM_TEXT=>PARAM_INT]);
		$this->assertEquals(['key'=>0, 'more'=>11], $ret);
	
		$ret = block_exacomp\common\param::clean_array(['3'=>'key', '10'=>'more'], [PARAM_INT=>PARAM_TEXT]);
		$this->assertEquals([3=>'key', 10=>'more'], $ret);
	
		$ret = block_exacomp\common\param::clean_array(['3'=>'key', '10'=>'more'], [PARAM_TEXT]);
		$this->assertEquals([0=>'key', 1=>'more'], $ret);

		$ret = block_exacomp\common\param::clean_array(['3'=>'key', '10'=>'more'], PARAM_TEXT);
		$this->assertEquals([0=>'key', 1=>'more'], $ret);
	}

	public function test_optional_array() {
		$_POST['testparam'] = ['3'=>'key', '10'=>'more'];
		$ret = block_exacomp\common\param::optional_array('testparam', [PARAM_INT=>PARAM_TEXT]);
		$this->assertEquals([3=>'key', 10=>'more'], $ret);
	
		unset($_POST['testparam']);
		$ret = block_exacomp\common\param::optional_array('testparam', [PARAM_INT=>PARAM_TEXT]);
		$this->assertEquals(array(), $ret);

		$_POST['testparam'] = ['3'=>'key', '10'=>'more'];
		$ret = block_exacomp\common\param::optional_array('testparam', PARAM_TEXT);
		$this->assertEquals([0=>'key', 1=>'more'], $ret);
	}
	
	public function test_required_array() {
		$_POST['testparam'] = ['3'=>'key', '10'=>'more'];
		$ret = block_exacomp\common\param::required_array('testparam', [PARAM_INT=>PARAM_TEXT]);
		$this->assertEquals([3=>'key', 10=>'more'], $ret);
	
		$_POST['testparam'] = ['3'=>'key', '10'=>'more'];
		$ret = block_exacomp\common\param::required_array('testparam', PARAM_TEXT);
		$this->assertEquals([0=>'key', 1=>'more'], $ret);

		try {
			unset($_POST['testparam']);
			block_exacomp\common\param::required_array('testparam', [PARAM_INT=>PARAM_TEXT]);
			$this->fail('exception expected');
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}
	}
	
	public function test_optional_object() {
		$_POST['testparam'] = ['key'=>'asdf', 'more'=>'asdf'];
		$ret = block_exacomp\common\param::optional_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals((object)['key'=>'asdf', 'missing'=>0], $ret);
		
		$_POST['testparam'] = 'asdf';
		$ret = block_exacomp\common\param::optional_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals(null, $ret);
		
		unset($_POST['testparam']);
		$ret = block_exacomp\common\param::optional_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals(null, $ret);
	}

	public function test_required_object() {
		$_POST['testparam'] = ['key'=>'asdf', 'more'=>'asdf'];
		$ret = block_exacomp\common\param::required_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals((object)['key'=>'asdf', 'missing'=>0], $ret);
		
		try {
			$_POST['testparam'] = 'test_required_json';
			$ret = block_exacomp\common\param::required_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
			$this->fail('exception expected');
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}

		try {
			unset($_POST['testparam']);
			$ret = block_exacomp\common\param::required_object('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
			$this->fail('exception expected');
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}
	}
	
	public function test_required_json() {
		$_POST['testparam'] = json_encode(['key'=>'asdf', 'more'=>'asdf']);
		$ret = block_exacomp\common\param::required_json('testparam', (object)['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
		$this->assertEquals((object)['key'=>'asdf', 'missing'=>0], $ret);
	
		$_POST['testparam'] = json_encode(['key'=>'asdf', 'more'=>'asdf']);
		$ret = block_exacomp\common\param::required_json('testparam', [PARAM_TEXT=>PARAM_TEXT]);
		$this->assertEquals(['key'=>'asdf', 'more'=>'asdf'], $ret);
	
		try {
			$_POST['testparam'] = 'wrong string value';
			$ret = block_exacomp\common\param::required_json('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
			$this->fail('exception expected');
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}

		try {
			unset($_POST['testparam']);
			$ret = block_exacomp\common\param::required_json('testparam', ['key'=>PARAM_TEXT, 'missing'=>PARAM_INT]);
			$this->fail('exception expected');
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}
	}
}
