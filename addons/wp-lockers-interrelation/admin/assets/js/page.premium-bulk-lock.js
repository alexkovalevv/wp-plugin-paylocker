/**
 * Сценарий для страницы массовой блоикировки премием статей
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 08.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';
	var premiumBulkLock = {
		init: function() {
			$('#onp_bp_addon_sl_frequency, #onp_bp_addon_pl_frequency').keyup(function() {

				var elemType = $(this).attr('id') == 'onp_bp_addon_sl_frequency'
					? 'sociallocker'
					: 'paylocker';

				var elemVal = $(this).val();

				if( isNaN(elemVal) ) {
					$(this).val(0);
				}

				if( elemVal > 100 ) {
					elemVal = 100;
					$(this).val(100);
				}

				var sum = 100 - elemVal;

				if( elemType == 'sociallocker' ) {
					$('#onp_bp_addon_pl_frequency').val(sum);
					return;
				}

				$('#onp_bp_addon_sl_frequency').val(sum);
			});
		}
	};

	$(function() {
		premiumBulkLock.init();
	});

})(jQuery);
