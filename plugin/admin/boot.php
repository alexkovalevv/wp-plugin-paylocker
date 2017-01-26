<?php

	#comp merge
	require(PAYLOCKER_DIR . '/plugin/admin/activation.php');

	// pages
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/begin-subscribe.php');

	function onp_pl_print_scripts_to_locker_preview()
	{
		?>
		<script type="text/javascript" src="<?php echo PAYLOCKER_URL; ?>/plugin/assets/js/paylocker.010001.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo PAYLOCKER_URL ?>/plugin/assets/css/paylocker.1.0.0.min.css">
	<?php
	}

	add_action('onp_sl_preview_head', 'onp_pl_print_scripts_to_locker_preview');

	/**
	 * Изменяем позицую для пунктов меню в админ панели
	 *
	 * @see menu_order
	 * @since 1.0.0
	 */
	function onp_pl_reposition_menu($menu)
	{
		global $submenu;

		$detachMenuItems = array();

		if( isset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE]) ) {
			$bizpandaMenu = $submenu['edit.php?post_type=' . OPANDA_POST_TYPE];
			foreach($bizpandaMenu as $menuKey => $menuValue) {
				/*if( $menuValue[2] === 'admin_premium_subscribers-paylocker-rus' || $menuValue[2] === 'purchased_posts-paylocker-rus' ) {
					$detachMenuItems[] = $bizpandaMenu[$menuKey];
					unset($bizpandaMenu[$menuKey]);
				}*/
				if( !BizPanda::hasPlugin('optinpanda') && !BizPanda::hasPlugin('sociallocker') && $menuValue[2] === 'leads-bizpanda' ) {
					unset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey]);
				}
			}
			//$submenu['edit.php?post_type=' . OPANDA_POST_TYPE] = array_slice($bizpandaMenu, 0, 2, true) + $detachMenuItems + array_slice($bizpandaMenu, 2, count($bizpandaMenu) - 2, true);
		}
		unset($menu[14]);

		return $menu;
	}

	add_filter('custom_menu_order', '__return_true');
	add_filter('menu_order', 'onp_pl_reposition_menu');

//require(SOCIALLOCKER_DIR . '/plugin/admin/notices.php');
//require(SOCIALLOCKER_DIR . '/plugin/admin/pages/license-manager.php');
