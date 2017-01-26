<?php
	/**
	 * Boots the code for the admin part of the Social Locker
	 *
	 * @since 1.0.0
	 * @package core
	 */

	require BIZPANDA_PAYLOCKER_DIR . '/admin/pages/settings.php';

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

	function onp_paylocker_metaboxes($metaboxes)
	{
		$restructuringMetaboxes = array();
		foreach($metaboxes as $key => $metabox) {
			if( $metabox['class'] == 'OPanda_AdvancedOptionsMetaBox' /*|| $metabox['class'] == 'OPanda_VisabilityOptionsMetaBox'*/ ) {
				unset($metaboxes[$key]);
			} else {
				$restructuringMetaboxes[$key] = $metabox;
			}
		}

		$restructuringMetaboxes[] = array(
			'class' => 'Opanda_PricingTablesMetabox',
			'path' => BIZPANDA_PAYLOCKER_DIR . '/admin/metaboxes/pricing-tables-options.php'
		);

		return $restructuringMetaboxes;
	}

	add_filter('opanda_pay-locker_type_metaboxes', 'onp_paylocker_metaboxes', 10, 1);

	function onp_paylocker_visability_option($options)
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

	add_filter('opanda_visability_options', 'onp_paylocker_visability_option', 10, 1);

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
			'name' => 'starter',
			'title' => 'Starter',
			'path' => OPANDA_BIZPANDA_DIR . '/themes/starter',
			'items' => array('pay-locker')
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