(function($){

	exabis_rg2.options.check_uncheck_parents_children = true;

	// merge input fields
	$(function(){
		// add extra fields
		$('form#exa-selector').submit(function(){
			var $form = $(this);

			// remove old fields
			$form.find('input[name=json_data]').remove();
			// readd
			var $json_data = $('<input type="hidden" name="json_data" />').appendTo($form);

			var data = {
				subjects:	$form.find('input[exa-name='+'subjects'   +']:checked').map(function(){return this.value;}).get(),
				topics:	  $form.find('input[exa-name='+'topics'	 +']:checked').map(function(){return this.value;}).get(),
				descriptors: $form.find('input[exa-name='+'descriptors'+']:checked').map(function(){return this.value;}).get(),
				examples:	$form.find('input[exa-name='+'examples'   +']:checked').map(function(){return this.value;}).get()
			};
			
			$json_data.val(JSON.stringify(data));
		});
	});
	
})(jQueryExacomp);
