/**
 * Сценарий для страницы оформления подписки
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	if( window.__paylocker === void 0 ) {
		window.__paylocker = {
			lang_interface: {
				loading: null,
				redirect: null
			}
		};
	}

	$(function() {
		if( window.__paylocker !== void 0 && window.__paylocker.loadTablesInfo ) {

			var tablesInfo = window.__paylocker.loadTablesInfo;

			var changeLockerSelector = '#subscribe_locker',
				changeTableSelector = '#table_name',
				priceElementSelector = '.onp-pl-table-price-text',
				paymentType = '#paymentType',
				saveButtonSelector = '#onp-pl-start-payment-button',
				disabledAllElements = $(paymentType).add(changeLockerSelector).add(saveButtonSelector).add(changeTableSelector),
				disabledSomeElements = $(paymentType).add(changeTableSelector).add(saveButtonSelector);

			tablesInfo.disabledElements(disabledAllElements);

			tablesInfo.updatePricingTablesList(changeTableSelector, {
				lockerId: $(changeLockerSelector).val()
			}, function(status) {
				if( status == 'error' ) {
					tablesInfo.disabledElements(disabledSomeElements);
					tablesInfo.enabledElements($(changeLockerSelector));
				} else if( status == 'success' ) {
					tablesInfo.enabledElements(disabledAllElements);
				}

				tablesInfo.updateTablePrice(priceElementSelector, changeTableSelector);
			});

			$(changeLockerSelector).change(function() {
				var payLockerId = $(this).val();

				tablesInfo.updatePricingTablesList(changeTableSelector, {
					lockerId: payLockerId
				}, function(status) {
					if( status == 'error' ) {
						tablesInfo.disabledElements(disabledSomeElements);
						tablesInfo.enabledElements($(changeLockerSelector));
					} else if( status == 'success' ) {
						tablesInfo.enabledElements(disabledAllElements);
					}

					tablesInfo.updateTablePrice(priceElementSelector, changeTableSelector);
				});
			});

			$(changeTableSelector).change(function() {
				tablesInfo.updateTablePrice(priceElementSelector, changeTableSelector);
			});

			$(saveButtonSelector).click(function() {
				tablesInfo.disabledElements(disabledAllElements);

				$(this).val(window.__paylocker.lang_interface.redirect);

				$.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'onp_pl_begin_transaction',
						locker_id: $('#subscribe_locker').val(),
						table_name: $(changeTableSelector).val(),
						table_payment_type: 'subscribe',
						table_price: $(changeTableSelector).find('option:selected').data('price')
					},
					success: function(data, textStatus, jqXHR) {

						if( !data || data.error || !data.transaction_id ) {
							console && console.log(data.error);

							if( window.beginSubscribePageUrl ) {
								window.location.href = window.beginSubscribePageUrl + '&payment_proccess=error' +
								(data.error_code
									? '&error_code=' + data.error_code
									: '');
							}
						}

						$('#label').val(data.transaction_id);
						$('#onp-pl-payment-form').submit();
					}
				});

				return false;
			});

		} else {
			throw new Error('[Error]: loadTablesInfo libs is not required.');
		}
	});

})(jQuery);
