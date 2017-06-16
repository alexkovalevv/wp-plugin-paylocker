/**
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 17.01.2017
 * @version 1.0
 */


(function($) {
	'use strict';
	$.pandalocker.hooks.add('opanda-lock', function(e, locker) {
		$(locker.locker).attr('id', locker.options.id);

		if( $(locker.locker).hasClass('onp-sl-paylocker-mode') ) {
			if( !__paylocker ) {
				return;
			}

			$(locker.locker).find('.onp-pl-login-link').click(function() {

				var width = 550;
				var height = 420;

				var x = screen.width
					? (screen.width / 2 - width / 2 + $.pandalocker.tools.findLeftWindowBoundry())
					: 0;
				var y = screen.height
					? (screen.height / 2 - height / 2 + $.pandalocker.tools.findTopWindowBoundry())
					: 0;

				var winref = window.open(
					__paylocker.loginUrl,
					"PaylockerLogin",
					"width=" + width + ",height=" + height + ",left=" + x + ",top=" + y + ",resizable=yes,scrollbars=yes,status=yes"
				);

				// waiting until the window is closed
				var pollTimer = setInterval(
					function() {
						if( !winref || winref.closed !== false ) {
							clearInterval(pollTimer);
							window.location.reload();
						}

						if( winref.location.href == 'about:blank' || winref.location.href && winref.location.href.indexOf('wp-login.php') > -1 ) {
							return false;
						}

						winref.close();
						window.location.reload();
					}, 200
				);

				return false;

			});

		}
	});
})(jQuery);

