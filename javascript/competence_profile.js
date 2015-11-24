(function($){
	$( window ).load(function() {
		$('.content_div').each(function(event){
			$(this).hide();
		});
	});
	
	$(document).on('click', '.togglefield', function(event){
			console.log('inhere');
			var $this = $(this);
			var parent = $this.parent();
			parent.find('.content_div').toggle();
	});

})(jQueryExacomp);
