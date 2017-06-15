/**
 * Подгружает необходимую информацию о тарифных таблицах
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 14.06.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	if( window.__paylocker === void 0 ) {
		window.__paylocker = {
			lang_interface: {
				loading: null
			}
		};
	}

	window.__paylocker.loadTablesInfo = {

		disabledElements: function(disabledElements) {
			disabledElements.each(function() {
				$(this).attr('disabled', true).addClass('onp-pl-hide-control');
			});
		},

		enabledElements: function(disabledElements) {
			disabledElements.each(function() {
				$(this).attr('disabled', false).removeClass('onp-pl-hide-control');
			});
		},

		updateTablePrice: function(priceElementSelector, changeTableSelector) {
			var changeTableElement = $(changeTableSelector),
				tablePrice = changeTableElement.find('option:selected').data('price');

			if( !tablePrice ) {
				tablePrice = 0;
			}

			if( $('input[type="hidden"][id="sum"]').length ) {
				$('#sum').val(tablePrice);
				console.log('ddd');
			}

			$(priceElementSelector).text(tablePrice);
		},

		updatePricingTablesList: function(changeTableSelector, findTablesFilters, callback) {
			var self = this,
				changeTableElement = $(changeTableSelector),
				loadingText = '-------------------[' + __paylocker.lang_interface.loading + ']-----------------',
				fieldOptionElement = $('<option></option>');

			changeTableElement.html('');
			changeTableElement.append(fieldOptionElement.attr('name', 'load').text(loadingText));

			var sendData = {
				action: 'onp_pl_get_pricing_tables'
			};

			sendData = $.extend(sendData, findTablesFilters);

			$.ajax({
				url: ajaxurl,
				type: 'post',
				dataType: 'json',
				data: sendData,
				success: function(data, textStatus, jqXHR) {
					changeTableElement.html('');

					if( !data || data.error ) {
						var defautErrorMessage = "[Error]: Unknown ajax error";
						if( data.error ) {
							console && console.log(data.error);
							defautErrorMessage = data.error;
						}

						changeTableElement.append(fieldOptionElement.attr('name', 'none').text(defautErrorMessage));

						callback && callback('error', data);
						return;
					}

					for( var i in data ) {
						if( !data.hasOwnProperty(i) ) {
							continue;
						}

						changeTableElement.append('<option value="' + data[i][0] + '" data-price="' + data[i][2] + '">' + data[i][1] + '</option>');
					}

					callback && callback('success', data);
				}
			});
		}
	};
})(jQuery);
