// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
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

if (block_exacomp.get_param('action') == 'share') {
	$(document).on('click', 'form#share input[name=share_all]', function(){
		// disable if checked
		$("input[name='studentids[]']").attr('disabled', this.checked);
	});
}

if (block_exacomp.get_param('action') == 'descriptor_selector') {
	$(function(){
		// disable subitems, which also prevents them from getting submitted
		$('table.rg2 :checkbox').click(function(){
			exabis_rg2.get_children(this, true).find(':checkbox')
				.prop('disabled', $(this).is(':checked'))
				.prop('checked', $(this).is(':checked'));
		});
		$('table.rg2 :checkbox:checked').triggerHandler('click');
	});
}
