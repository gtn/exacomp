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
	function update() {
		var descriptor_type = $(':radio[name=descriptor_type]:checked').val();
		if (descriptor_type == 'new') {
			// moodle 30: still needed?
			$('#fitem_id_descriptor_title').show();
			$('#fitem_id_descriptor_id').hide();
			// moodle 33
			$(':input[name=descriptor_title]').closest('.fitem').show();
			$(':input[name=descriptor_id]').closest('.fitem').hide();
		} else {
			// moodle 30: still needed?
			$('#fitem_id_descriptor_title').hide();
			$('#fitem_id_descriptor_id').show();
			// moodle 33
			$(':input[name=descriptor_title]').closest('.fitem').hide();
			$(':input[name=descriptor_id]').closest('.fitem').show();
		}

		// 09.04.2019 disabled
		/*var niveau_type = $(':radio[name=niveau_type]:checked').val();
		if (niveau_type == 'new') {
			// moodle 30: still needed?
			$('#fitem_id_niveau_title').show();
			$('#fitem_id_niveau_numb').show();
			$('#fitem_id_niveau_id').hide();
			// moodle 33
			$(':input[name=niveau_title]').closest('.fitem').show();
			$(':input[name=niveau_numb]').closest('.fitem').show();
			$(':input[name=niveau_id]').closest('.fitem').hide();
		} else {
			// moodle 30: still needed?
			$('#fitem_id_niveau_title').hide();
			$('#fitem_id_niveau_numb').hide();
			$('#fitem_id_niveau_id').show();
			// moodle 33
			$(':input[name=niveau_title]').closest('.fitem').hide();
			$(':input[name=niveau_numb]').closest('.fitem').hide();
			$(':input[name=niveau_id]').closest('.fitem').show();
		}*/
	}
	
	$(function(){
		update();
		$(':radio[name=descriptor_type]').change(update);
		$(':radio[name=niveau_type]').change(update);
	});
})(jQueryExacomp);
