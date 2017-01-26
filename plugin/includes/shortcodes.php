<?php
	/**
	 * Управление шорткодами
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.01.2017
	 * @version 1.0
	 */
	function onp_pl_what_to_show($type, $lockerId)
	{
		$hideForAdmin = get_post_meta($lockerId, 'opanda_hide_for_admin', true);

		if( is_user_logged_in() && current_user_can('administrator') && $hideForAdmin ) {
			return 'content';
		}

		return 'locker';
	}

	add_filter('onp_sl_what_to_show', 'onp_pl_what_to_show', 10, 2);