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

class block_exacomp_services_testcase extends basic_testcase {
	public function test_service_definitions() {
		global $CFG; // needed in includes
		require __DIR__.'/../db/services.php';
		require __DIR__.'/../externallib.php';

		$this->assertNotEmpty($functions, 'no service definitions found');

		$class_methods = get_class_methods(block_exacomp_external::class);

		// don't use get_parent_class here, because maybe service class is overwritten
		$base_class_methods = get_class_methods('external_api');
		$class_methods = array_diff($class_methods, $base_class_methods);

		$class_methods = array_flip($class_methods);

		foreach ($functions as $function) {
			$f = $function['methodname'];
			$this->assertArrayHasKey($f, $class_methods, "function $f not found in class ".block_exacomp_external::class);
			unset($class_methods[$f]);

			$f = $function['methodname'].'_parameters';
			$this->assertArrayHasKey($f, $class_methods, "function $f not found in class ".block_exacomp_external::class);
			unset($class_methods[$f]);

			$f = $function['methodname'].'_returns';
			$this->assertArrayHasKey($f, $class_methods, "function $f not found in class ".block_exacomp_external::class);
			unset($class_methods[$f]);
		}

		$this->assertEmpty($class_methods,
			'these public webservice methods are defined, but not used anywhere? '.print_r($class_methods, true));
	}
}
