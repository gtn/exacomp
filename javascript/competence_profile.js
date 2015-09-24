/**
 * 
 */
(function($){
	$(document).ready(function() {
	    $('toggle_field').click(function() {
	        var $this = $(this);
	        var parent = $this.parent();
	        var contents = parent.contents().not(this);
	        if (contents.length > 0) {
	            $this.data("contents", contents.remove());
	        } else {
	            $this.data("contents").appendTo(parent);
	        }
	        return false;
	    });
	});

})(jQueryExacomp);
