<?php

	#comp merge
	require(PAYLOCKER_DIR . '/plugin/admin/activation.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/license-manager.php');
	//require(SOCIALLOCKER_DIR . '/plugin/admin/notices.php');

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
		global $submenu, $paylocker;

		$detachMenuItems = array();

		if( isset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE]) ) {
			$bizpandaMenu = $submenu['edit.php?post_type=' . OPANDA_POST_TYPE];
			foreach($bizpandaMenu as $menuKey => $menuValue) {
				if( $menuValue[2] === 'admin_premium_subscribers-' . $paylocker->pluginName || $menuValue[2] === 'purchased_posts-' . $paylocker->pluginName ) {
					$detachMenuItems[] = $bizpandaMenu[$menuKey];
					unset($bizpandaMenu[$menuKey]);
				}
				if( !BizPanda::hasPlugin('optinpanda') && !BizPanda::hasPlugin('sociallocker') && $menuValue[2] === 'leads-bizpanda' ) {
					unset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey]);
				}
			}
			$submenu['edit.php?post_type=' . OPANDA_POST_TYPE] = array_slice($bizpandaMenu, 0, 2, true) + $detachMenuItems + array_slice($bizpandaMenu, 2, count($bizpandaMenu) - 2, true);
		}
		unset($menu[14]);

		return $menu;
	}

	add_filter('custom_menu_order', '__return_true');
	add_filter('menu_order', 'onp_pl_reposition_menu');

	// ---
	// Menu
	//
	/**
	 * Removes the default 'new item' from the admin menu to add own pgae 'new item' later.
	 *
	 * @see menu_order
	 * @since 1.0.0
	 */

	/*function opanda_remove_new_item($menu)
	{
		global $submenu, $paylocker;

		if( onp_lang('ru_RU') ) {
			//Отличнительный признак для меню лицензирования
			if( isset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE]) ) {
				foreach($submenu['edit.php?post_type=' . OPANDA_POST_TYPE] as $menuKey => $menuValue) {
					if( !BizPanda::isSinglePlugin() ) {
						if( $menuValue[2] === 'license-manager-optinpanda-rus' ) {
							$submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey][0] = $menuValue[0] . "<br>(Opt-In Panda)";
							$submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey][3] = $menuValue[3] . "<br>(Opt-In Panda)";
						} else if( $menuValue[2] === 'license-manager-sociallocker-rus' ) {
							$submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey][0] = $menuValue[0] . "<br>(Соц. замок)";
							$submenu['edit.php?post_type=' . OPANDA_POST_TYPE][$menuKey][3] = $menuValue[3] . "<br>(Соц. замок)";
						}
					}
				}
			}
		}

		if( !isset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE]) ) {
			return $menu;
		}
		unset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE][10]);

		return $menu;
	}*/

	/**
	 * Changes the menu title if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function paylocker_change_menu_title($title)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $title;
		}

		return __('Платный контент', 'bizpanda');
	}

	add_filter('opanda_menu_title', 'paylocker_change_menu_title');

	/**
	 * Changes the menu icon if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function paylocker_change_menu_icon($icon)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $icon;
		}

		return PAYLOCKER_URL . '/plugin/admin/assets/img/menu-icon.png';
	}

	add_filter('opanda_menu_icon', 'paylocker_change_menu_icon');

	/**
	 * Changes the shortcode icon if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function paylocker_change_shortcode_icon($icon)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $icon;
		}

		return PAYLOCKER_URL . '/plugin/admin/assets/img/shortcode-icon.png';
	}

	add_filter('opanda_shortcode_icon', 'paylocker_change_shortcode_icon');

	/**
	 * Changes the menu title of the page 'New Item' if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function paylocker_change_new_item_menu_title($title)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $title;
		}

		return __('+ New Locker', 'bizpanda');
	}

	add_filter('factory_menu_title_new-item-opanda', 'paylocker_change_new_item_menu_title');

	/**
	 * Changes labels of Panda Items if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 4.0.0
	 * @return mixed A set of new labels
	 */
	function paylocker_change_items_lables($labels)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $labels;
		}
		$labels['all_items'] = __('All Lockers', 'bizpanda');
		$labels['add_new'] = __('+ New Locker', 'bizpanda');

		return $labels;
	}

	add_filter('opanda_items_lables', 'paylocker_change_items_lables');

	/**
	 * Makes internal page "License Manager" for the Social Locker
	 *
	 * @since 1.0.0
	 * @return bool true
	 */
	function paylocker_make_internal_license_manager($internal)
	{

		if( onp_build('premium') ) {
			if( onp_license('free') ) {
				return $internal;
			}
		}

		if( BizPanda::isSinglePlugin() ) {
			return $internal;
		}

		return true;
	}

	add_filter('factory_page_is_internal_license-manager-paylocker-next', 'paylocker_make_internal_license_manager');
