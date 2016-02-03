(function($){
	$(function() {
		$('.content_div').hide();
	});
	
	$(document).on('click', '.togglefield', function(event){
			console.log('inhere');
			var $this = $(this);
			var parent = $this.parent();
			parent.find('.content_div').toggle();
	});

})(jQueryExacomp);
