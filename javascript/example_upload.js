/**
 * 
 */
(function($){
	$(function() {
		// reopen selected li
		$( "input[type=checkbox]" ).each(function( ) {
			if($(this).attr('checked')){
				$(this).parents('ul')
					.addClass('collapsibleListOpen')
					.removeClass('collapsibleListClosed')
					.show();
				$(this).closest('li').parents('li').addClass('collapsibleListOpen').removeClass('collapsibleListClosed');
			}
		});
		
	});
	
})(jQueryExacomp);
