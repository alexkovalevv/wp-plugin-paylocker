<?php

	require PAYLOCKER_DIR . '/plugin/admin/pages/settings.php';
	require PAYLOCKER_DIR . '/plugin/admin/metaboxes/basic-options.php';

	#comp merge
	require(PAYLOCKER_DIR . '/plugin/admin/activation.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/license-manager.php');
	//require(SOCIALLOCKER_DIR . '/plugin/admin/notices.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/begin-subscribe.php');
	#endcomp

	/**
	 * Добавляем скрипты paylocker в превью
	 */
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
					unset($bizpandaMenu[$menuKey]);
				}
			}
			$submenu['edit.php?post_type=' . OPANDA_POST_TYPE] = array_slice($bizpandaMenu, 0, 2, true) + $detachMenuItems + array_slice($bizpandaMenu, 2, count($bizpandaMenu) - 2, true);
		}
		unset($menu[14]);

		return $menu;
	}

	add_filter('custom_menu_order', '__return_true');
	add_filter('menu_order', 'onp_pl_reposition_menu');

	/**
	 * Changes the menu title if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function onp_pl_change_menu_title($title)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $title;
		}

		return __('Платный контент', 'bizpanda');
	}

	add_filter('opanda_menu_title', 'onp_pl_change_menu_title');

	/**
	 * Changes the menu icon if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function onp_pl_change_menu_icon($icon)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $icon;
		}

		return PAYLOCKER_URL . '/plugin/admin/assets/img/menu-icon.png';
	}

	add_filter('opanda_menu_icon', 'onp_pl_change_menu_icon');

	/**
	 * Changes the shortcode icon if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function onp_pl_change_shortcode_icon($icon)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $icon;
		}

		return PAYLOCKER_URL . '/plugin/admin/assets/img/shortcode-icon.png';
	}

	add_filter('opanda_shortcode_icon', 'onp_pl_change_shortcode_icon');

	/**
	 * Changes the menu title of the page 'New Item' if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 1.0.0
	 * @return string A new menu title.
	 */
	function onp_pl_change_new_item_menu_title($title)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $title;
		}

		return __('+ New Locker', 'bizpanda');
	}

	add_filter('factory_menu_title_new-item-opanda', 'onp_pl_change_new_item_menu_title');

	/**
	 * Changes labels of Panda Items if the Social Locker is an only plugin installed from BizPanda.
	 *
	 * @since 4.0.0
	 * @return mixed A set of new labels
	 */
	function onp_pl_change_items_lables($labels)
	{
		if( !BizPanda::isSinglePlugin() ) {
			return $labels;
		}
		$labels['all_items'] = __('All Lockers', 'bizpanda');
		$labels['add_new'] = __('+ New Locker', 'bizpanda');

		return $labels;
	}

	add_filter('opanda_items_lables', 'onp_pl_change_items_lables');

	/**
	 * Makes internal page "License Manager" for the Social Locker
	 *
	 * @since 1.0.0
	 * @return bool true
	 */
	function onp_pl_make_internal_license_manager($internal)
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

	add_filter('factory_page_is_internal_license-manager-paylocker-next', 'onp_pl_make_internal_license_manager');

	function onp_pl_register_plugin($items)
	{
		$items[] = array(
			'name' => 'paylocker',
			'type' => 'premium',
			'title' => __('Платный контент', 'bizpanda'),
			'description' => __('<p>Helps to attract social traffic and improve spreading your content in social networks.</p><p>Also extends the Sign-In Locker by adding social actions you can set up to be performed.</p>', 'bizpanda'),
			'upgradeToPremium' => __('<p>A premium version of the plugin Social Locker.</p><p>7 Social Buttons, 5 Beautiful Themes, Blurring Effect, Countdown Timer, Close Cross and more!</p>', 'bizpanda'),
			'url' => 'https://sociallocker.ru/',
			'tags' => array(),
			'pluginName' => 'paylocker'
		);

		return $items;
	}

	add_filter('opanda_register_plugins', 'onp_pl_register_plugin', 1);

	/**
	 * Регистрируем новый тип замка
	 * @param $items
	 * @return mixed
	 */
	function onp_pl_register_paylocker_item($items)
	{
		global $paylocker;

		$title = __('Платный контент', 'bizpanda');

		$items['pay-locker'] = array(
			'name' => 'pay-locker',
			'type' => 'premium',
			'title' => $title,
			'help' => opanda_get_help_url('paylocker'),
			'description' => '<p>' . __('Этот тип замков предоставляет доступ к контенту, только для пользователей оформивших премиум подписку.', 'bizpanda') . '</p>',
			'shortcode' => 'paylocker',
			'plugin' => $paylocker
		);

		return $items;
	}

	add_filter('opanda_items', 'onp_pl_register_paylocker_item', 1);

	/**
	 * Создаем доплнительные опции принудительно для всех платных замков
	 * @param $postId
	 */
	function onp_pl_save_post_callback($postId)
	{
		global $post;
		if( $post->post_type != 'opanda-item' ) {
			return;
		}

		$lockerType = get_post_meta($postId, 'opanda_item', true);

		if( $lockerType != 'pay-locker' ) {
			return;
		}

		update_post_meta($postId, 'opanda_ajax', 1);
	}

	add_action('save_post', 'onp_pl_save_post_callback');

	/**
	 * Показывает условия видимости на странице списка замков
	 * @param $postId
	 * @param $empty
	 */
	function onp_pl_print_simple_visibility_options($postId, $empty)
	{
		$lockerType = get_post_meta($postId, 'opanda_item', true);
		$hideForAdmin = get_post_meta($postId, 'opanda_hide_for_admin', true);

		if( !empty($hideForAdmin) && $lockerType == 'pay-locker' ) {
			echo '<li>' . __('Скрыт для администратора: <strong>да</strong>', 'bizpanda') . '</li>';
		} else if( $empty ) {
			echo '<li>—</li>';
		}
	}

	add_action('opanda_print_simple_visibility_options', 'onp_pl_print_simple_visibility_options', 10, 2);

	/**
	 * Registers metaboxes for Social Locker.
	 *
	 * @see opanda_item_type_metaboxes
	 * @since 1.0.0
	 */

	function onp_pl_metaboxes($metaboxes)
	{
		$restructuringMetaboxes = array();
		foreach($metaboxes as $key => $metabox) {
			if( $metabox['class'] == 'OPanda_AdvancedOptionsMetaBox' /*|| $metabox['class'] == 'OPanda_VisabilityOptionsMetaBox'*/ ) {
				unset($metaboxes[$key]);
			} else {
				$restructuringMetaboxes[$key] = $metabox;
			}
		}

		/*$restructuringMetaboxes[] = array(
			'class' => 'Opanda_ThemeStyleMetabox',
			'path' => BIZPANDA_PAYLOCKER_DIR . '/admin/metaboxes/theme-style-options.php'
		);*/

		$restructuringMetaboxes[] = array(
			'class' => 'Opanda_PricingTablesMetabox',
			'path' => PAYLOCKER_DIR . '/plugin/admin/metaboxes/pricing-tables-options.php'
		);

		return $restructuringMetaboxes;
	}

	add_filter('opanda_pay-locker_type_metaboxes', 'onp_pl_metaboxes', 10, 1);

	function onp_pl_visability_option($options)
	{
		global $post;
		$lockerType = get_post_meta($post->ID, 'opanda_item', true);

		if( empty($lockerType) ) {
			return $options;
		}

		if( $lockerType == 'pay-locker' ) {
			foreach($options as $key => $option) {
				if( $option['id'] == 'bp-simple-visibility-options' ) {
					unset($options[$key]['items']);
					$options[$key]['items'][] = array(
						'type' => 'checkbox',
						'way' => 'buttons',
						'name' => 'hide_for_admin',
						'title' => __('Скрыть для администраторов', 'bizpanda'),
						'hint' => __('Если Вкл, то замок будет скрыт для администраторов.', 'bizpanda'),
						'icon' => OPANDA_BIZPANDA_URL . '/assets/admin/img/member-icon.png',
						'default' => false
					);
				}
			}
		}

		return $options;
	}

	add_filter('opanda_visability_options', 'onp_pl_visability_option', 10, 1);

	/**
	 * Registers default themes.
	 *
	 * We don't need to include the file containing the file OPanda_ThemeManager because this function will
	 * be called from the hook defined inside the class OPanda_ThemeManager.
	 *
	 * @see onp_sl_register_themes
	 * @see OPanda_ThemeManager
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function opanda_register_paylocker_themes()
	{
		OPanda_ThemeManager::registerTheme(array(
			'name' => 'default',
			'title' => 'Default',
			//'path' => OPANDA_BIZPANDA_DIR . '/themes/starter',
			'items' => array('pay-locker'),
			'colors' => array(
				array('default', '#75649b'),
				array('black', '#222'),
				array('light', '#fff3ce'),
				array('forest', '#c9d4be'),
			)
		));

		OPanda_ThemeManager::registerTheme(array(
			'name' => 'testest',
			'title' => 'TestTest',
			//'path' => OPANDA_BIZPANDA_DIR . '/themes/starter',
			'items' => array('pay-locker'),
			'colors' => array(
				array('default', '#75649b'),
				array('black', '#222')
			)
		));
	}

	add_action('onp_sl_register_themes', 'opanda_register_paylocker_themes');

	/**
	 * Registers the quick tags for the wp editors.
	 *
	 * @see admin_print_footer_scripts
	 * @since 1.0.0
	 */
	function opanda_quicktags_for_paylocker()
	{ ?>
		<script type="text/javascript">
			(function() {
				if( !window.QTags ) {
					return;
				}
				window.QTags.addButton('paylocker', 'paylocker', '[paylocker]', '[/paylocker]');
			}());
		</script>
	<?php
	}

	add_action('admin_print_footer_scripts', 'opanda_quicktags_for_paylocker');
