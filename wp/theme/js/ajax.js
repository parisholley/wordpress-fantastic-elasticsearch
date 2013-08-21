(function($){
	$(function(){
		var $form = $('#esajaxform');
		$form.find('input[type=checkbox]').change(function(){
			var url = $form.attr('action');

			$.getJSON(url, $form.serialize() + '&esasync=1', function(data){
				$.each(data.faceting, function(type){
					var container = $("#facet-" + type + '-available');

					if(this.total > 1){
						container.show().find('.facet-item').hide();

						$.each(this.available, function(slug){
							if(this.count > 1){
								var li = $("#facet-" + type + '-' + slug);

								li.find('.count').text('(' + this.count + ')').show();
								li.show();
							}
						});

						$.each(this.selected, function(slug){
							var li = $("#facet-" + type + '-' + slug);

							li.find('.count').hide();
							li.show();
						});
					}else{
						container.hide();
					}
				});

				$(window.esfaceting.replace).replaceWith(data.content);
			});
		});
	});
})(jQuery);