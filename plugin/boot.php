<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/config.php');
	require_once(PAYLOCKER_DIR . '/plugin/includes/functions.php');
	require_once(PAYLOCKER_DIR . '/plugin/includes/shortcodes.php');
	require_once(PAYLOCKER_DIR . '/plugin/includes/assets.php');

	//require_once(PAYLOCKER_DIR . '/plugin/includes/classes/payment-gateways/class.paypal-standart.php');
	//$payment = new OnpPl_PaymentGateWayPaypal('fb7e4f81-bbea-418a-af7e-b8c5070efe26');

	//echo $payment->getPaymentUrl();

	//require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
	//require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');

	//require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.payment.abstract.php');

	//new ADAD();

	//require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase.php');
	//$purchases = OnpPl_Purchase::getInstance(1, 8);

	//$s = OnpPl_Purchase::getCount();
	//$s = OnpPl_Purchase::getItems();

	if( is_admin() ) {
		require_once(PAYLOCKER_DIR . '/plugin/admin/boot.php');
	}

	function onp_pl_cron_tasks_function()
	{
		// Выполняем задания по пользовательским уведомлениям
		require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.notification.php');
		$notification = new OnpPl_Notifications();
		$notification->runShedule();

		// Выполняем задание для проверки истекших премиум подписок
		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');

		OnpPl_Subcribe::runSheduleCheckPremium();
	}

	add_action('onp_pl_cron_tasks', 'onp_pl_cron_tasks_function');

	// todo: удалить после тестирования
	//header("Access-Control-Allow-Origin: *");

	// Фильтрация экшенов
	if( isset($_REQUEST['action']) ) {
		switch( $_REQUEST['action'] ) {
			case 'onp_pl_begin_transaction':
				require_once PAYLOCKER_DIR . '/plugin/admin/ajax/create-and-check-account.php';
				break;
			case 'onp_pl_payment_yandex_notification':
				require_once PAYLOCKER_DIR . '/plugin/admin/ajax/check-payments.php';
				break;
			case 'onp_pl_get_pricing_tables':
				require_once PAYLOCKER_DIR . '/plugin/admin/ajax/get-pricing-tables.php';
				break;
			case 'onp_pl_check_transaction':
				require_once PAYLOCKER_DIR . '/plugin/admin/ajax/check-transaction.php';
				break;
			case 'onp_pl_update_user_premium':
				require_once PAYLOCKER_DIR . '/plugin/admin/ajax/update-user-subsribe.php';
				break;
		}
	}

	/*if( is_user_logged_in() ) {
		// Проверяем подписку пользователя
		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');
		$premium = new OnpPl_PremiumSubscriber();

		if( !$premium->checkUserPremium() ) {
			$premium->resetUserPremium();
		}
	}*/

	function onp_pl_add_premium_counter_to_admin_bar($wp_admin_bar)
	{

		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');

		$current_user = wp_get_current_user();
		$roles = $current_user->roles;

		$args = array(
			'id' => 'onp-pl-premium-counter',
			'parent' => 'top-secondary',
			'title' => __('Приобрести премиум подписку', 'plugin-paylocker'),
			'href' => admin_url('admin.php?page=begin_subscribe-paylocker'),
			'meta' => array(
				'class' => 'onp-pl-buy-premium-bar-button'
			)
		);

		$premium = new OnpPl_PremiumSubscriber();

		if( $premium->hasUserPremium() ) {
			$userPremiumList = $premium->getUserPremiumList(true);
			foreach($userPremiumList as $userPremium) {
				$args['id'] = 'onp-pl-premium-counter-' . $userPremium['locker_id'];

				$subscribeTime = $premium->timeToDayFormat($userPremium['expired_end']);

				$args['title'] = sprintf(__('Премиум подписка истекает через <strong>%s</strong> дней', 'plugin-paylocker'), $subscribeTime);
				$args['href'] = admin_url('admin.php?page=client_premium_subscribers-paylocker');
				$args['meta']['class'] = 'onp-pl-premium-status-default';

				if( $subscribeTime <= 5 ) {
					$args['meta']['class'] = 'onp-pl-buy-premium-status-expired';
				}

				$wp_admin_bar->add_node($args);
			}
		} else {
			$wp_admin_bar->add_node($args);
		}

		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php');

		$countPurchasedPosts = 0;
		if( !empty($current_user->ID) ) {
			$countPurchasedPosts = OnpPl_PurchasePosts::getCount($current_user->ID);
		}

		$args = array(
			'id' => 'onp-pl-user-purchased-posts',
			'parent' => 'top-secondary',
			'title' => sprintf(__('Мои покупки (%s)', 'plugin-paylocker'), $countPurchasedPosts),
			'href' => admin_url('admin.php?page=user_orders-paylocker')
		);

		$wp_admin_bar->add_node($args);
	}

	if( !current_user_can('administrator') ) {
		add_action('admin_bar_menu', 'onp_pl_add_premium_counter_to_admin_bar', 90);
	}

	function onp_pl_add_style_for_admin_bar()
	{
		?>
		<style>
			.onp-pl-buy-premium-bar-button {
				background-color: #e91e63 !important;
			}

			.onp-pl-premium-status-default {
				background-color: #395a00 !important;
			}

			.onp-pl-buy-premium-status-expired {
				background-color: #f44336 !important;
			}
		</style>
	<?php
	}

	if( !current_user_can('administrator') ) {
		add_action('wp_head', 'onp_pl_add_style_for_admin_bar');
		add_action('admin_head', 'onp_pl_add_style_for_admin_bar');
	}

	/**
	 * Adds options to print at the frontend.
	 *
	 * @since 1.0.0
	 */
	function onp_paylocker_options($options, $lockerId)
	{
		global $post;

		$themeStyle = opanda_get_item_option($lockerId, 'style__dropdown');
		$themeColor = opanda_get_item_option($lockerId, 'style__colors');

		if( !empty($themeStyle) ) {
			$options['theme'] = $themeStyle;
		}

		if( !empty($themeColor) && $themeColor != 'default' ) {
			$options['theme'] .= '-' . $themeColor;
		}

		$options['groups'] = array(
			'order' => array('pricing-tables')
		);
		$options['paylocker'] = array();

		$tables = get_post_meta($lockerId, 'opanda_pricing_tables_data', true);

		$orderTables = array();

		if( !empty($tables) ) {
			foreach($tables as $tableName => $table) {
				$orderTables[] = $tableName;
			}
		}

		$options['paylocker']['ajaxUrl'] = admin_url('admin-ajax.php');
		$options['paylocker']['helpUrl'] = opanda_get_item_option($lockerId, 'locker_help_url');
		$options['paylocker']['supportUrl'] = 'mailto:' . get_bloginfo('admin_email');
		$options['paylocker']['loginUrl'] = wp_login_url();

		$options['paylocker']['paymentForms'] = array(
			'yandex' => array(
				'receiver' => opanda_get_option('pl_payment_form_receiver'),
				//'successURL' => opanda_get_option('pl_payment_form_success_url'),
				'termsPageUrl' => opanda_get_option('pl_payment_form_terms'),
				'alternatePaymentTypePageUrl' => opanda_get_option('pl_alternate_payment_type_url')
			)
		);
		$options['pricingTables']['orderTables'] = $orderTables;
		$options['pricingTables']['tables'] = $tables;

		$options['locker']['visibility'] = array(
			array(
				'conditions' => array(
					array(
						'type' => 'scope',
						'conditions' => array(
							array(
								'param' => 'user-paid-mode-l' . $lockerId,
								'operator' => 'equals',
								'type' => 'select',
								'value' => 'premium'
							)
						)
					)
				),
				'type' => 'hideif'
			)
		);

		return $options;
	}

	add_filter('bizpanda_pay-locker_item_options', 'onp_paylocker_options', 10, 2);

	/**
	 * Requests assets for email locker.
	 */
	function onp_pl_lockers_assets($lockerId, $options, $fromBody, $fromHeader)
	{
		OPanda_AssetsManager::requestLockerAssets();

		// Miscellaneous
		OPanda_AssetsManager::requestTextRes(array(
			'pl_payment_form_header',
			'pl_payment_form_description'
		));
	}

	add_action('bizpanda_request_assets_for_pay-locker', 'onp_pl_lockers_assets', 10, 4);

	/**
	 * A shortcode for the Social Locker
	 *
	 * @since 1.0.0
	 */
	class OPanda_PaylockerShortcode extends OPanda_LockerShortcode {

		/**
		 * Shortcode name
		 * @var string
		 */
		public $shortcodeName = array(
			'paylocker',
			'paylocker-1',
			'paylocker-2',
			'paylocker-3',
			'paylocker-4'
		);

		protected function getDefaultId()
		{
			return get_option('onp_paylocker_default_id');
		}
	}


	global $bizpanda;

	FactoryShortcodes000::register('OPanda_PaylockerShortcode', $bizpanda);


