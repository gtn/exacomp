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

require __DIR__.'/inc.php';

class block_exacomp_common_functions_testcase extends basic_testcase {
	public function test_t() {
		global $SESSION;
		$SESSION->forcelang = 'de';
		
		$this->assertEquals('Some String', block_exacomp\common\trans('Some String'));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('de:xxx {$a} xxx', 'arg'));
		$this->assertEquals('xxx', block_exacomp\common\trans('de:xxx'));
		$this->assertEquals('xxx', block_exacomp\common\trans('id', 'de:xxx'));
		$this->assertEquals('xxx', block_exacomp\common\trans('id', ['de:xxx', 'en:yyy']));
		$this->assertEquals('yyy', block_exacomp\common\trans('id', ['en:xxx', 'de:yyy']));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('de:xxx {$a} xxx', 'arg'));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('id', 'de:xxx {$a} xxx', 'arg'));
		$this->assertEquals('xxx arg xxx', block_exacomp\common\trans('id', ['de:xxx {$a} xxx', 'en:xxx {$a} xxx'], 'arg'));
		$this->assertEquals('xxx test xxx', block_exacomp\common\trans('id', 'de:xxx {$a->arg} xxx', ['arg' => 'test']));
		$this->assertEquals('xxx test xxx', block_exacomp\common\trans('id', ['de:xxx {$a->arg} xxx', 'en:yyy {$a->arg} yyy'], ['arg' => 'test']));

		// other language
		$this->assertEquals('asdf', block_exacomp\common\trans('id', 'fr:asdf'));
	}
}
