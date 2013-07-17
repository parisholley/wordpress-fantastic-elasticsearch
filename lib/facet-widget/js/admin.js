(function ($) {
	"use strict";
	$(function () {


		// update the list with available taxonomies whenever you change the post type
		$('.widget-liquid-right').on('change', '.tfw_post_type', function(){
			var widget = $(this).closest('div.widget');
  			wpWidgets.save(widget, 0, 1, 0);
		});

		// make the taxonomies lis sortable
		$('.widget-liquid-right').on('mouseenter', 'ul.taxonomies-admin-list', function() {
			$(this).sortable();
		});
});
	
}(jQuery));