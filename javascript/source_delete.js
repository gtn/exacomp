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

(function($){

	exabis_rg2.options.check_uncheck_parents_children = true;

	// merge input fields
	$(function(){
		// add extra fields
		$('form#exa-selector').submit(function(){
			var $form = $(this);

			// remove old fields
			$form.find('input[name=json_data]').remove();
			// readd
			var $json_data = $('<input type="hidden" name="json_data" />').appendTo($form);

			var data = {
				subjects:	$form.find('input[exa-name='+'subjects'   +']:checked').map(function(){return this.value;}).get(),
				topics:	  $form.find('input[exa-name='+'topics'	 +']:checked').map(function(){return this.value;}).get(),
				descriptors: $form.find('input[exa-name='+'descriptors'+']:checked').map(function(){return this.value;}).get(),
				examples:	$form.find('input[exa-name='+'examples'   +']:checked').map(function(){return this.value;}).get()
			};
			
			$json_data.val(JSON.stringify(data));
		});
	});
	
})(jQueryExacomp);
