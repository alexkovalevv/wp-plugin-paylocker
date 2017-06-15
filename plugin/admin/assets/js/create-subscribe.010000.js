/**
 * Сценарий для страницы оформления подписки
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	$(function() {
		if( window.__paylocker !== void 0 && window.__paylocker.loadTablesInfo ) {

			var tablesInfo = window.__paylocker.loadTablesInfo;

			var changeLockerSelector = '#onp_pl_subscribe_locker',
				changeTableSelector = '#onp_pl_table_name',
				priceElementSelector = '.onp-pl-table-price-text',
				userNameSelector = '#onp_pl_user_name',
				saveButtonSelector = '#onp-pl-add-subscribe-button',
				disabledAllElements = $(userNameSelector).add(changeLockerSelector).add(saveButtonSelector).add(changeTableSelector),
				disabledSomeElements = $(userNameSelector).add(changeTableSelector).add(saveButtonSelector);

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

		} else {
			throw new Error('[Error]: loadTablesInfo libs is not required.');
		}

	});

})(jQuery);