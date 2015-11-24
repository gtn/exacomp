<?php

require_once __DIR__.'/inc.php';

class block_exacomp_common_moodle_url_testcase extends basic_testcase {
	public function test_copy() {
		$url = new block_exacomp\common\url('test.php', ['var'=>'original']);

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
}