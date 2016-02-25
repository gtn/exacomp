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

$(function(){
	$('input[name=uses_activities]').change(function(){
		if ($(this).is(':checked')) {
			$('input[name=show_all_descriptors]').prop('disabled', false);
		} else {
			$('input[name=show_all_descriptors]').prop('disabled', true).prop('checked', true);
		}
	}).triggerHandler('change');
});
