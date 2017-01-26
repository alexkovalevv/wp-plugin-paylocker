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
			this.payLockerId = $('#onp_pl_subscribe_locker').val();
			this.loadSelectOption = $('<option name="load">-------------------[Загрузка]-----------------</option>');
			this.errorSelectOption = $('<option name="none">[Ошибка]: ошибка ajax запроса</option>');

			this.setPricingTablesList();
			this.registerEvents();

			$('#onp_pl_subscribe_locker, #onp_pl_user_name').each(function() {
				if( $(this).hasClass('onp-pl-hide-control') ) {
					$(this).attr('disabled', true);
				}
			});
		},

		registerEvents: function() {
			var self = this;

			$('#onp_pl_subscribe_locker').change(function() {
				self.payLockerId = $(this).val();

				self.setPricingTablesList();
			});

			$('#onp_pl_table_name').change(function() {
				self.updateTablePrice();
			});

			$('#onp-pl-add-subscribe-button').click(function() {

				$('#onp_pl_subscribe_locker, #onp_pl_user_name').each(function() {
					if( $(this).hasClass('onp-pl-hide-control') ) {
						$(this).attr('disabled', false);
					}
				});

			});
		},

		updateTablePrice: function() {
			var tablePrice = $('#onp_pl_table_name').find('option:selected').data('price');
			$('.onp-pl-table-price-text').text(tablePrice);
		},

		setPricingTablesList: function() {
			var self = this;

			$('#onp_pl_table_name').html('');
			$('#onp_pl_table_name').append(this.loadSelectOption);
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

					$('#onp_pl_table_name').html('');

					if( !data || data.error ) {
						console && console.log(data.error);
						$('#onp_pl_table_name').append(self.errorSelectOption);
					}

					for( var i in data ) {
						$('#onp_pl_table_name').append('<option value="' + data[i][0] + '" data-price="' + data[i][2] + '">' + data[i][1] + '</option>');
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
