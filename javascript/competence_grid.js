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
			document.location.href = this.getAttribute('data-url') + '&report='+this.value;
		});
	});
})(jQueryExacomp);
