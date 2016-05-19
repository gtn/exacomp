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

(function($) {

	$(document).on('click', '.switchtextalign', function(){
		if( $( "span[class='rotated-text']" ).length > 0 ) {
		$( "span[class='rotated-text']" ).attr("class","rotated-text-disabled");
		$( "span[class='rotated-text__inner']" ).attr("class","rotated-text__inner_disabled");
		} else {
			$( "span[class='rotated-text-disabled']" ).attr("class","rotated-text");
			$( "span[class='rotated-text__inner_disabled']" ).attr("class","rotated-text__inner");
		}
	});
	
	// student selector
	$(function(){
		$('select[name=exacomp_competence_grid_report]').change(function(){
			block_exacomp.set_location_params({ report: this.value });
		});
	});
})(jQueryExacomp);
