(function($){
	$(function(){
		var $form = $('#esajaxform');
		var $selected = $('#facet-selected');

		$form.find('input[type=checkbox]').change(function(){
			var url = $form.attr('action');

			$.getJSON(url, $form.serialize() + '&esasync=1', function(data){
				$selected.hide().find('.facet-item').hide();

				$.each(data.faceting, function(type){
					var container = $("#facet-" + type + '-available');
					var total = this.total;

					if(total > 0){
						container.show().find('.facet-item').hide();

						if(total > 1){
							$.each(this.available, function(slug){
								if(this.count > 1){
									var li = $("#facet-" + type + '-' + slug);

									li.find('.count').text('(' + this.count + ')').show();
									li.show();
								}
							});

							if(window.esselected && window.esselected.showEmpty == 1){
								container.find('.facet-empty').hide();
							}
						}else{
							if(window.esselected && window.esselected.showEmpty == 1){
								container.find('.facet-empty').show();
							}
						}

						$.each(this.selected, function(slug){
							var li = $("#facet-" + type + '-' + slug);

							li.find('.count').hide();
							
							if(!window.esselected || window.esselected.showEmpty != 1 || total > 1){
								li.show();
							}

							$("#facet-" + type + '-' + slug + '-selected').show();
							$selected.show();
						});
					}else{
						container.hide();
					}
				});

				$(window.esfaceting.replace).replaceWith(data.content);
			});
		});

		$selected.find('.facet-item a').click(function(){
			var selector = $(this).attr('href');

			$(selector).find('input').click();

			return false;
		});
	});
})(jQuery);