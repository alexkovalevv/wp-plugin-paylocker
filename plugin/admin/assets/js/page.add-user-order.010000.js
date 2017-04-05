/**
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 31.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	$(function() {
		var select2Config = {
			width: 600,
			ajax: {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				delay: 500,
				data: function(params) {
					var postTypes = [];
					$('input[name="onp_pl_searche_post_types[]"]').each(function() {
						if( $(this).is(':checked') ) {
							postTypes.push($(this).val());
						}
					});

					return {
						action: 'opanda_search_post',
						search_query: params.term, // search term
						post_types: postTypes
					};
				},
				processResults: function(data, params) {
					return {
						results: data
					};
				},
				cache: true
			},

			escapeMarkup: function(markup) {
				return markup;
			},
			minimumInputLength: 1,
			templateSelection: function(dataPost) {
				return dataPost.id || dataPost.text;
			}
		};

		// Только включенные страницы
		$('select[name="onp_pl_selected_posts[]"]').bizpanda_select2(select2Config);
	});
})(jQuery);
