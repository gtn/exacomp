/**
 * 
 */
(function($){
	$( window ).load(function() {
		CollapsibleLists.apply();
		
		// reopen selected li
		$( "input[type=checkbox]" ).each(function( ) {
			if($(this).attr('checked')){
				$(this).parents('ul').addClass('collapsibleListOpen');
				$(this).parents('ul').removeClass('collapsibleListClosed');
				$(this).parents('ul').attr('style', 'display:block');
				
			}
		});
	});
})(jQueryExacomp);
