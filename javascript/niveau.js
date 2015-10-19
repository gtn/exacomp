(function($){
	function update() {
		var descriptor_type = $(':radio[name=descriptor_type]:checked').val();
		if (descriptor_type == 'new') {
			$('#fitem_id_descriptor_title').show();
			$('#fitem_id_descriptor_id').hide();
		} else {
			$('#fitem_id_descriptor_title').hide();
			$('#fitem_id_descriptor_id').show();
		}

		var niveau_type = $(':radio[name=niveau_type]:checked').val();
		if (niveau_type == 'new') {
			$('#fitem_id_niveau_title').show();
			$('#fitem_id_niveau_id').hide();
		} else {
			$('#fitem_id_niveau_title').hide();
			$('#fitem_id_niveau_id').show();
		}
	}
	
	$(function(){
		update();
		$(':radio[name=descriptor_type]').change(update);
		$(':radio[name=niveau_type]').change(update);
		
		/*
		$('#id_submitbutton').click(function(){
			$('#fitem_id_niveau_title')
		});
		*/
	});
})(jQueryExacomp);