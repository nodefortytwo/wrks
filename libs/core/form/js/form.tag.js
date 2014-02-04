(function($) {
	$(document).ready(function() {
		$.ajax('/tags').success(function(data){
			$('.tagbox input').select2(data);
		})
		
	});
})(jQuery);