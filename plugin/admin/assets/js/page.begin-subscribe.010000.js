/**
 * Сценарий для страницы оформления подписки
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';

	var pageBeginSubscribe = {
		init: function() {
			this.payLockerId = $('#subscribe_locker').val();
			this.loadSelectOption = $('<option name="load">-------------------[Загрузка]-----------------</option>');
			this.errorSelectOption = $('<option name="none">[Ошибка]: ошибка ajax запроса</option>');

			this.setPricingTablesList();
			this.registerEvents();

			if( $('#subscribe_locker').hasClass('onp-pl-hide-control') ) {
				$('#subscribe_locker').attr('disabled', true);
			}
		},

		registerEvents: function() {
			var self = this;

			$('#onp-pl-start-payment-button').click(function() {

				$(this).attr('disabled', true).val('Идет перенаправление...');

				$.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'onp_pl_begin_transaction',
						locker_id: $('#subscribe_locker').val(),
						table_name: $('#table_name').val(),
						table_payment_type: 'subscribe',
						table_price: $('#table_name').find('option:selected').data('price')
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

			$('#subscribe_locker').change(function() {
				self.payLockerId = $(this).val();

				self.setPricingTablesList();
			});

			$('#table_name').change(function() {
				self.updateTablePrice();
			});
		},

		updateTablePrice: function() {
			var tablePrice = $('#table_name').find('option:selected').data('price');
			$('#sum').val(tablePrice);
			$('.onp-pl-table-price-text').text(tablePrice);
		},

		setPricingTablesList: function() {
			var self = this;

			$('#table_name').html('');
			$('#table_name').append(this.loadSelectOption);
			$('#onp-pl-start-payment-button').attr('disabled', true);

			$.ajax({
				url: ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'onp_pl_get_pricing_tables',
					lockerId: self.payLockerId
				},
				success: function(data, textStatus, jqXHR) {
					console.log(data);

					$('#table_name').html('');

					if( !data || data.error ) {
						console && console.log(data.error);
						$('#table_name').append(self.errorSelectOption);
					}

					for( var i in data ) {
						$('#table_name').append('<option value="' + data[i][0] + '" data-price="' + data[i][2] + '">' + data[i][1] + '</option>');
					}

					$('#onp-pl-start-payment-button').attr('disabled', false);
					self.updateTablePrice();
				}
			});
		}
	};

	$(function() {
		pageBeginSubscribe.init();
	});

})(jQuery);
