<?php

	define('BIZPANDA_PAYLOCKER_DIR', dirname(__FILE__));
	define('BIZPANDA_PAYLOCKER_URL', plugins_url(null, __FILE__));

	if( is_admin() ) {
		require BIZPANDA_PAYLOCKER_DIR . '/admin/boot.php';
	}

	/**
	 * Adds options to print at the frontend.
	 *
	 * @since 1.0.0
	 */
	function onp_paylocker_options($options, $lockerId)
	{
		global $post;

		$options['groups'] = array('pricing-tables');
		$options['paylocker'] = array();

		$tables = opanda_get_item_option($lockerId, 'pricing_tables_data');
		$tables = json_decode($tables, true);

		$orderTables = array();

		foreach($tables as $tableName => $table) {
			$orderTables[] = $tableName;
		}
		$options['paylocker']['ajaxUrl'] = admin_url('admin-ajax.php');
		$options['paylocker']['paymentForms'] = array(
			'yandex' => array(
				'receiver' => opanda_get_option('pl_payment_form_receiver'),
				'successURL' => opanda_get_option('pl_payment_form_success_url'),
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

	add_filter('opanda_pay-locker_item_options', 'onp_paylocker_options', 10, 2);

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

	add_action('opanda_request_assets_for_pay-locker', 'onp_pl_lockers_assets', 10, 4);

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
			return get_option('onp_paylocker_defaul_id');
		}
	}


	global $bizpanda;

	FactoryShortcodes000::register('OPanda_PaylockerShortcode', $bizpanda);



