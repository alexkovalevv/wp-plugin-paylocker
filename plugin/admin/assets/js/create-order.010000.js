/**
 * Код для формы оформления заказа в админ панели wordpress
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 31.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	$(function() {
		if( window.__paylocker !== void 0 && window.__paylocker.loadTablesInfo ) {

			var tablesInfo = window.__paylocker.loadTablesInfo;

			var changeLockerSelector = '#onp_pl_subscribe_locker',
				changeTableSelector = '#onp_pl_table_name',
				userNameSelector = '#onp_pl_user_name',
				saveButtonSelector = '#onp-pl-add-subscribe-button',
				disabledAllElements = $(userNameSelector).add(changeLockerSelector).add(saveButtonSelector).add(changeTableSelector),
				disabledSomeElements = $(userNameSelector).add(changeTableSelector).add(saveButtonSelector);

			tablesInfo.disabledElements(disabledAllElements);

			tablesInfo.updatePricingTablesList(changeTableSelector, {
				lockerId: $(changeLockerSelector).val(),
				tableType: 'purchase'
			}, function(status) {
				if( status == 'error' ) {
					tablesInfo.disabledElements(disabledSomeElements);
					tablesInfo.enabledElements($(changeLockerSelector));
				} else if( status == 'success' ) {
					tablesInfo.enabledElements(disabledAllElements);
				}
			});

			$(changeLockerSelector).change(function() {
				var payLockerId = $(this).val();

				tablesInfo.updatePricingTablesList(changeTableSelector, {
					lockerId: payLockerId,
					tableType: 'purchase'
				}, function(status) {
					if( status == 'error' ) {
						tablesInfo.disabledElements(disabledSomeElements);
						tablesInfo.enabledElements($(changeLockerSelector));
					} else if( status == 'success' ) {
						tablesInfo.enabledElements(disabledAllElements);
					}
				});
			});

			/*$(saveButtonSelector).click(function() {
			 tablesInfo.disabledElements(disabledAllElements);
			 });*/

		} else {
			throw new Error('[Error]: loadTablesInfo libs is not required.');
		}

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
})
(jQuery);