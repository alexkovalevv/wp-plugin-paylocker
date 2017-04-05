/**
 * Страница статистики по платным подпискам пользователей
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 06.02.2017
 * @version 1.0
 */


(function($) {
	'use strict';
	window.__onp_pl_expired_timeout = 0;

	$('.onp-pl-plus-button').add('.onp-pl-minus-button').click(function(e) {
		var parent = $(this).closest('.column-expired_end'),
			expiresEl = parent.find('.onp-pl-expired-field'),
			expiredNumber = parseInt(expiresEl.val()),
			userId = parseInt(expiresEl.data('user-id')),
			lockerId = parseInt(expiresEl.data('locker-id'));

		if( !$(e.target).hasClass('onp-pl-minus-button') ) {
			expiresEl.val(++expiredNumber);
		} else {
			if( expiredNumber < 0 || expiredNumber === 0 ) {
				return;
			}

			expiresEl.val(--expiredNumber);
		}

		clearTimeout(window.__onp_pl_expired_timeout);

		window.__onp_pl_expired_timeout = setTimeout(function() {
			updateExpired(expiredNumber, userId, lockerId);
		}, 1000);
	});

	$('.onp-pl-expired-field').on('keyup', function() {
		if( !$.isNumeric($(this).val()) ) {
			$(this).val(0);
		}
	});

	$('.onp-pl-expired-field').on('blur', function() {
		var expiredDays = parseInt($(this).val()),
			userId = parseInt($(this).data('user-id')),
			lockerId = parseInt($(this).data('locker-id'));
		updateExpired(expiredDays, userId, lockerId);
	});

	function updateExpired(expiredDays, userId, lockerId) {
		var expiredFieldEl = $('input[data-locker-id="' + lockerId + '"][data-user-id="' + userId + '"].onp-pl-expired-field');
		expiredFieldEl.removeClass('onp-pl-field-success');

		$.ajax({
			url: ajaxurl,
			type: 'post',
			dataType: 'json',
			data: {
				action: 'onp_pl_update_user_premium',
				expiredDays: expiredDays,
				lockerId: lockerId,
				userId: userId
			},
			success: function(data, textStatus, jqXHR) {
				console.log(data);

				if( !data || data.error ) {
					console && console.log(data.error);
					expiredFieldEl.val(expiredFieldEl.data('default-expired'));
					expiredFieldEl.addClass('onp-pl-field-error');
					return false;
				}

				expiredFieldEl.attr('data-default-expired', expiredDays);
				expiredFieldEl.addClass('onp-pl-field-success');
				setTimeout(function() {
					expiredFieldEl.removeClass('onp-pl-field-success');
				}, 500);
			},
			error: function() {
				expiredFieldEl.val(expiredFieldEl.data('default-expired'));
				expiredFieldEl.addClass('onp-pl-field-error');
			}
		});
	}

})(jQuery);
