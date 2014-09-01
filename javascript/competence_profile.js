jQueryExacomp(function($){

	$('li.category').addClass('plusimageapply');
	$('li.category').children().addClass('selectedimage');
	$('li.category').children().hide();
	$('li.category').each(function(column) {
		$(this).click(function(event) {
			if (this == event.target) {
				if ($(this).is('.plusimageapply')) {
					$(this).children().show();
					$(this).removeClass('plusimageapply');
					$(this).addClass('minusimageapply');
				} else {
					$(this).children().hide();
					$(this).removeClass('minusimageapply');
					$(this).addClass('plusimageapply');
				}
			}
		});
	});
});