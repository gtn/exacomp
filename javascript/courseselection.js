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

(function($){
	exabis_rg2.options.reopen_checked = true;

	$(function(){
		// submit warning message
		var $form = $('#course-selection');
		
		$form.submit(function(){
			if($form.attr("examplesonschedule")>0){
				return confirm(M.util.get_string('delete_unconnected_examples', 'block_exacomp'));
			}
		});
	
	});

})(jQueryExacomp);

