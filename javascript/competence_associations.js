/**
 * 
 */
(function($){
	$(function() {
		// reopen selected li
		/*
		$( "input[type=checkbox]" ).each(function( ) {
			if($(this).attr('checked')){
				$(this).parents('ul').addClass('collapsibleListOpen');
				$(this).parents('ul').removeClass('collapsibleListClosed');
				$(this).parents('ul').attr('style', 'display:block');
				
			}
		});
		*/

		// start with open list
		$('.collapsibleList li:has(li)').addClass('collapsibleListOpen').removeClass('collapsibleListClosed');
		$('.collapsibleList ul').css('display', 'block');
	});
})(jQueryExacomp);
