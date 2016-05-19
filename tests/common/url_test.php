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

use block_exacomp\common\url as url;

class block_exacomp_common_url_testcase extends basic_testcase {
	public function test_copy() {
		$url = new url('test.php', ['var'=>'original']);

		$urlCopy = $url->copy(['var'=>'copy']);
		
		// $urlCopy is a new object
		$this->assertFalse($url === $urlCopy);
		
		// with same path
		$this->assertEquals($url->get_path(), $urlCopy->get_path());
		
		// $url has param unchanged
		$this->assertEquals('original', $url->get_param('var'));
		
		// $urlCopy has new param
		$this->assertEquals('copy', $urlCopy->get_param('var'));
	}
	
	public function test_null_params() {
		// null with new url
		$url = new url('test.php', ['tmp1' => 'val1', 'var'=>'original', 'tmp2'=>'val2']);
		$urlNew = new url($url, ['var' => null]);
		$this->assertEquals('tmp1=val1&tmp2=val2', $urlNew->get_query_string(false));

		// changing query string
		$url = new url('test.php', ['tmp1' => 'val1', 'var'=>'original', 'tmp2'=>'val2']);
		$url->get_query_string(false, ['var' => null]);
		$this->assertEquals('tmp1=val1&tmp2=val2', $urlNew->get_query_string(false));

		// changing whole url
		$url = new url('test.php', ['tmp1' => 'val1', 'var'=>'original', 'tmp2'=>'val2']);
		$this->assertEquals('test.php?tmp1=val1&tmp2=val2', $url->out(false, ['var' => null]));
	}
}
