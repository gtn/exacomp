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

class block_exacomp_common_functions_testcase extends basic_testcase {
	public function test_trans() {
		global $SESSION;

		$SESSION->forcelang = 'de';

		try {
			block_exacomp\common\trans('Some String');
			$this->fail("exception expected, because not a valid string eg. 'de:Some String'");
		} catch (moodle_exception $e) {
			$this->assertTrue(true);
		}

		// string
		$this->assertEquals('xxx', block_exacomp\common\trans('de:xxx'));
		$this->assertEquals('xxx', block_exacomp\common\trans('id', 'de:xxx'));

		// param
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('id', 'de:xxx {$a} xxx', 'arg'));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('de:xxx {$a} xxx', 'arg'));

		// multiple langauges
		$this->assertEquals('xxx', block_exacomp\common\trans(['de:xxx', 'en:yyy']));
		$this->assertEquals('yyy', block_exacomp\common\trans(['en:xxx', 'de:yyy']));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans(['de:xxx {$a} xxx', 'en:yyy {$a} yyy'], 'arg'));
		$this->assertEquals('xxx test xxx', block_exacomp\common\trans('de:xxx {$a->arg} xxx', ['arg' => 'test']));
		$this->assertEquals('xxx test xxx', block_exacomp\common\trans(['de:xxx {$a->arg} xxx', 'en:yyy {$a->arg} yyy'], ['arg' => 'test']));

		// other language
		$this->assertEquals('asdf', block_exacomp\common\trans('fr:asdf'));

		// fallback to language file
		$this->assertEquals('result_unittest_string', block_exacomp\common\trans('unittest_string'));

		// can't translate same language
		$this->assertEquals('unittest_string2', block_exacomp\common\trans('de:unittest_string2'));

		// if language definition is null, then use original language string
		$this->assertEquals('unittest_string3', block_exacomp\common\trans('de:unittest_string3'));

		$SESSION->forcelang = 'en';

		// with params
		$this->assertEquals('result_unittest_param x result_unittest_param',
			block_exacomp\common\trans('de:unittest_param {$a} unittest_param', 'x'));
		$this->assertEquals('result_unittest_param2 x result_unittest_param2',
			block_exacomp\common\trans('de:unittest_param2 {$a->val} unittest_param2', ['val' => 'x']));
	}
}
