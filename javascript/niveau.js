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
	function update() {
		var descriptor_type = $(':radio[name=descriptor_type]:checked').val();
		if (descriptor_type == 'new') {
			$('#fitem_id_descriptor_title').show();
			$('#fitem_id_descriptor_id').hide();
		} else {
			$('#fitem_id_descriptor_title').hide();
			$('#fitem_id_descriptor_id').show();
		}

		var niveau_type = $(':radio[name=niveau_type]:checked').val();
		if (niveau_type == 'new') {
			$('#fitem_id_niveau_title').show();
			$('#fitem_id_niveau_id').hide();
		} else {
			$('#fitem_id_niveau_title').hide();
			$('#fitem_id_niveau_id').show();
		}
	}
	
	$(function(){
		update();
		$(':radio[name=descriptor_type]').change(update);
		$(':radio[name=niveau_type]').change(update);
		
		/*
		$('#id_submitbutton').click(function(){
			$('#fitem_id_niveau_title')
		});
		*/
	});
})(jQueryExacomp);
