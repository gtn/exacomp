/**
 * 
 */
(function($){
	$(function() {
		// reopen selected li
		$( ".add_open" ).each(function( ) {
			$(this).addClass('collapsibleListOpen');
			$(this).removeClass('collapsibleListClosed');
		});
		
		$(".add_style").each(function( ){
			$(this).attr('style', 'display:block');
		});
	});
})(jQueryExacomp);
