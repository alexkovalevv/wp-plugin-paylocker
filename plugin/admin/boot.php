<?php
	#comp merge
	require PAYLOCKER_DIR . '/plugin/admin/pages/settings.php';
	require PAYLOCKER_DIR . '/plugin/admin/metaboxes/basic-options.php';
	require(PAYLOCKER_DIR . '/plugin/admin/activation.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/license-manager.php');
	//require(SOCIALLOCKER_DIR . '/plugin/admin/notices.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-premium-subscribe-list.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/admin-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/client-purchased-posts.php');
	require(PAYLOCKER_DIR . '/plugin/admin/pages/begin-subscribe.php');
	#endcomp

	add_filter('manage_users_columns', 'onp_pl_add_user_id_column');

	function onp_pl_add_user_id_column($columns)
	{
		$columns['user_paylocker'] = __('Платный контент', 'plugin-paylocker');

		return $columns;
	}

	function onp_pl_show_user_id_column_content($value, $column_name, $user_id)
	{
		global $wpdb;

		if( 'user_paylocker' == $column_name ) {

			$results = $wpdb->get_results("
				SELECT COUNT(*) as counts,table_payment_type as payment_type
				FROM {$wpdb->prefix}opanda_pl_transactions
				WHERE user_id='" . (int)$user_id . "' and transaction_status='finish'
				GROUP BY table_payment_type
			");

			$countPurchase = 0;
			$countSubscribes = 0;

			if( !empty($results) ) {
				foreach($results as $value) {
					if( $value->payment_type == 'subscribe' ) {
						$countSubscribes = $value->counts;
					} else {
						$countPurchase = $value->counts;
					}
				}
			}

			$purchasePageUrl = admin_url('edit.php?post_type=opanda-item&page=purchased_posts-paylocker&sort=user_id&user_id=' . $user_id);
			$subscribePageUrl = admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-paylocker&sort=user_id&user_id=' . $user_id);

			return '<a href="' . $purchasePageUrl . '" class="button button-default">' . sprintf(__('Покупки (%d)', 'plugin-paylocker'), $countPurchase) . '</a> ' . ' <a href="' . $subscribePageUrl . '" class="button button-default">' . sprintf(__('Подписки (%d)', 'plugin-paylocker'), $countSubscribes) . '</a>';
		}

		return $value;
	}

	add_action('manage_users_custom_column', 'onp_pl_show_user_id_column_content', 10, 3);

	/**
	 * Добавляем скрипты paylocker в превью
	 */
	function onp_pl_print_scripts_to_locker_preview()
	{
		?>
		<script type="text/javascript" src="<?php echo PAYLOCKER_URL; ?>/plugin/assets/js/migration-rus-to-en.010001.min.js"></script>
		<script type="text/javascript" src="<?php echo PAYLOCKER_URL; ?>/plugin/assets/js/paylocker.010001.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo PAYLOCKER_URL ?>/plugin/assets/css/paylocker.010001.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo PAYLOCKER_URL ?>/plugin/assets/css/migration-rus-to-en.010001.min.css">

		<script>
			(function($) {

				/**
				 * Моментальное обновления тарифных таблиц, без перезагрузки превью
				 */
				$.pandalocker.hooks.add('opanda-init', function(e, locker) {
					locker.update = function() {
						locker.runHook('paylocker-update-options');

						locker.defaultScreen.html('');
						locker._initGroups();

						// creates markup for buttons
						for( var i = 0; i < locker._groups.length; i++ ) {
							locker._groups[i].renderGroup(locker.defaultScreen);
						}

						locker.runHook('paylocker-updated');
					};
				});

				/**
				 * Обновляем размеры превью, после того, как замок был обновлен
				 */
				$.pandalocker.hooks.add('opanda-paylocker-updated', function() {
					window.alertFrameSize();
				});

			})(__$onp);
		</script>
	<?php
	}

	add_action('bizpanda_print_scripts_to_preview_head', 'onp_pl_print_scripts_to_locker_preview');

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

		return __('Платный контент', 'plugin-paylocker');
	}

	add_filter('bizpanda_menu_title', 'onp_pl_change_menu_title');

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

	add_filter('bizpanda_menu_icon', 'onp_pl_change_menu_icon');

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

	add_filter('bizpanda_shortcode_icon', 'onp_pl_change_shortcode_icon');

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

		return __('+ Добавить новый', 'plugin-paylocker');
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
		$labels['all_items'] = __('Все замки', 'plugin-paylocker');
		$labels['add_new'] = __('+ Добавить новый', 'plugin-paylocker');

		return $labels;
	}

	add_filter('bizpanda_items_lables', 'onp_pl_change_items_lables');

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
			'title' => __('Платный контент', 'plugin-paylocker'),
			'description' => __('<p>Этот плагин монетизирует ваш сайт за счет продажи вашего контента.</p><p>Имеет возможность платной подписки и разовых продаж.</p>', 'plugin-paylocker'),
			//'upgradeToPremium' => __('<p>A premium version of the plugin Social Locker.</p><p>7 Social Buttons, 5 Beautiful Themes, Blurring Effect, Countdown Timer, Close Cross and more!</p>', 'plugin-paylocker'),
			'url' => 'https://sociallocker.ru/',
			'tags' => array(),
			'pluginName' => 'paylocker'
		);

		return $items;
	}

	add_filter('bizpanda_register_plugins', 'onp_pl_register_plugin', 1);

	/**
	 * Регистрируем новый тип замка
	 * @param $items
	 * @return mixed
	 */
	function onp_pl_register_paylocker_item($items)
	{
		global $paylocker;

		$title = __('Платный контент', 'plugin-paylocker');

		$items['pay-locker'] = array(
			'name' => 'pay-locker',
			'type' => 'premium',
			'title' => $title,
			'help' => opanda_get_help_url('paylocker'),
			'description' => '<p>' . __('Этот тип замков предоставляет доступ к контенту, только для пользователей оформивших премиум подписку.', 'plugin-paylocker') . '</p>',
			'shortcode' => 'paylocker',
			'plugin' => $paylocker
		);

		return $items;
	}

	add_filter('bizpanda_items', 'onp_pl_register_paylocker_item', 1);

	/**
	 * Создаем доплнительные опции принудительно для всех платных замков
	 * @param $postId
	 */
	function onp_pl_save_post_callback($postId)
	{
		global $post;
		if( empty($post) || $post->post_type != 'opanda-item' ) {
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
			echo '<li>' . __('Скрыт для администратора: <strong>да</strong>', 'plugin-paylocker') . '</li>';
		} else if( $empty ) {
			echo '<li>—</li>';
		}
	}

	add_action('bizpanda_print_simple_visibility_options', 'onp_pl_print_simple_visibility_options', 10, 2);

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

	add_filter('bizpanda_pay-locker_type_metaboxes', 'onp_pl_metaboxes', 10, 1);

	function onp_pl_visability_option($options)
	{
		global $post;
		$lockerType = get_post_meta($post->ID, 'opanda_item', true);

		if( empty($lockerType) ) {
			return $options;
		}

		if( $lockerType == 'pay-locker' ) {
			foreach($options as $key => $option) {
				if( isset($option['id']) && $option['id'] == 'bp-simple-visibility-options' ) {
					unset($options[$key]['items']);
					$options[$key]['items'][] = array(
						'type' => 'checkbox',
						'way' => 'buttons',
						'name' => 'hide_for_admin',
						'title' => __('Скрыть для администраторов', 'plugin-paylocker'),
						'hint' => __('Если Вкл, то замок будет скрыт для администраторов.', 'plugin-paylocker'),
						'icon' => OPANDA_BIZPANDA_URL . '/assets/admin/img/member-icon.png',
						'default' => false
					);
				}
			}
		}

		return $options;
	}

	add_filter('bizpanda_visability_options', 'onp_pl_visability_option', 10, 1);

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
	}

	add_action('bizpanda_register_themes', 'opanda_register_paylocker_themes');

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

	/**
	 * Registers stats screens for Email Locker.
	 *
	 * @since 1.0.0
	 */
	function onp_pl_stats_screens($screens)
	{
		$screens = array(

			// The Summary Screen

			'summary' => array(
				'title' => __('<i class="fa fa-search"></i> Общая', 'plugin-paylocker'),
				'description' => __('Общая статистика по продажам и подпискам.', 'plugin-sociallocker'),
				'chartClass' => 'OPanda_Paylocker_Summary_StatsChart',
				'tableClass' => 'OPanda_Paylocker_Summary_StatsTable',
				'path' => PAYLOCKER_DIR . '/plugin/admin/stats/summary.php'
			),
			// The Channels Screen

			'earging' => array(
				'title' => __('<i class="fa fa-search-plus"></i> Заработано', 'plugin-sociallocker'),
				'description' => __('Сколько всего вы заработали на продаже контента.', 'plugin-sociallocker'),
				'chartClass' => 'OPanda_Paylocker_Earning_StatsChart',
				'tableClass' => 'OPanda_Paylocker_Earning_StatsTable',
				'path' => PAYLOCKER_DIR . '/plugin/admin/stats/earning.php'
			)
		);

		return $screens;
	}

	add_filter('bizpanda_pay-locker_stats_screens', 'onp_pl_stats_screens', 10, 1);
