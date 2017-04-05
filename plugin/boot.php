<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/functions.php');
	require_once(PAYLOCKER_DIR . '/plugin/includes/shortcodes.php');
	require_once(PAYLOCKER_DIR . '/plugin/includes/assets.php');

	if( is_admin() ) {
		require_once(PAYLOCKER_DIR . '/plugin/admin/boot.php');
	}

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

	if( is_user_logged_in() ) {
		// Проверяем подписку пользователя
		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');
		$premium = new OnpPl_PremiumSubscriber();

		if( !$premium->checkUserPremium() ) {
			$premium->resetUserPremium();
		}
	}

	function onp_pl_add_premium_counter_to_admin_bar($wp_admin_bar)
	{

		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');

		$current_user = wp_get_current_user();
		$roles = $current_user->roles;

		$args = array(
			'id' => 'onp-pl-premium-counter',
			'parent' => 'top-secondary',
			'title' => __('Приобрести премиум подписку', 'bizpanda'),
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

				$args['title'] = sprintf(__('Премиум подписка истекает через <strong>%s</strong> дней', 'bizpanda'), $subscribeTime);
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
			'title' => sprintf(__('Мои покупки (%s)', 'bizpanda'), $countPurchasedPosts),
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


