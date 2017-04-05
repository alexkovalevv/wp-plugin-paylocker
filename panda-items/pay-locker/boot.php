<?php

	define('BIZPANDA_PAYLOCKER_DIR', dirname(__FILE__));
	define('BIZPANDA_PAYLOCKER_URL', plugins_url(null, __FILE__));

	if( is_admin() ) {
		require BIZPANDA_PAYLOCKER_DIR . '/admin/boot.php';
	}

	/*function onp_pl_print_theme_style($lockerId, $options, $lockerName)
	{
		// Основные настройки
		$themeBgColor = opanda_get_item_option($lockerId, 'theme_bg_color', false, '#75649b');
		$themeTextFont = opanda_get_item_option($lockerId, 'theme_text_style__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themeTextSize = opanda_get_item_option($lockerId, 'theme_text_style__size', false, '15');
		$themeTextColor = opanda_get_item_option($lockerId, 'theme_text_style__color', false, '#ffffff');

		//Верх замка
		$themeHeaderBgColor = opanda_get_item_option($lockerId, 'theme_header_bg', false, '#3c2e4f');
		$themeHeaderBorderColor = opanda_get_item_option($lockerId, 'theme_header_border_color', false, '#d6bef7');
		$themeHeaderTextFont = opanda_get_item_option($lockerId, 'theme_header_text_style__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themeHeaderTextSize = opanda_get_item_option($lockerId, 'theme_header_text_style__size', false, '16');
		$themeHeaderTextColor = opanda_get_item_option($lockerId, 'theme_header_text_style__color', false, '#d6bef7');

		//Таблица покупки
		$themePurchaseTableHeaderBg = opanda_get_item_option($lockerId, 'theme_purchase_table_header_bg', false, '#ffc107');
		$themePurchaseTableBg = opanda_get_item_option($lockerId, 'theme_purchase_table_bg', false, '#fff');
		$themePurchaseTableButtonBg = opanda_get_item_option($lockerId, 'theme_purchase_table_button_bg', false, '#ffc107');

		$themePurchaseTableTextHeaderFont = opanda_get_item_option($lockerId, 'theme_purchase_table_text_header__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themePurchaseTableTextHeaderSize = opanda_get_item_option($lockerId, 'theme_purchase_table_text_header__size', false, '15');
		$themePurchaseTableTextHeaderColor = opanda_get_item_option($lockerId, 'theme_purchase_table_text_header__color', false, '#222');

		$themePurchaseTableTextPriceFont = opanda_get_item_option($lockerId, 'theme_purchase_table_text_price__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themePurchaseTableTextPriceSize = opanda_get_item_option($lockerId, 'theme_purchase_table_text_price__size', false, '25');
		$themePurchaseTableTextPriceColor = opanda_get_item_option($lockerId, 'theme_purchase_table_text_price__color', false, '#3c2e4f');

		$themePurchaseTableTextDescriptionFont = opanda_get_item_option($lockerId, 'theme_purchase_table_text_description__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themePurchaseTableTextDescriptionSize = opanda_get_item_option($lockerId, 'theme_purchase_table_text_description__size', false, '13');
		$themePurchaseTableTextDescriptionColor = opanda_get_item_option($lockerId, 'theme_purchase_table_text_description__color', false, '#111');

		$themePurchaseTableTextButtonFont = opanda_get_item_option($lockerId, 'theme_purchase_table_text_button__family', false, '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif');
		$themePurchaseTableTextButtonSize = opanda_get_item_option($lockerId, 'theme_purchase_table_text_button__size', false, '11');
		$themePurchaseTableTextButtonColor = opanda_get_item_option($lockerId, 'theme_purchase_table_text_button__color', false, '#222');

		$output = '<style>';
		// Основные настройки
		$output .= '#' . $lockerName . '{background: ' . $themeBgColor . ';}';
		$output .= '#' . $lockerName . ' .onp-sl-text p{
			color: ' . $themeTextColor . ';
			font: normal normal 400 ' . $themeTextSize . 'px ' . $themeTextFont . ';
		}';
		// Верх замка
		$output .= '#' . $lockerName . ' .onp-sl-header{
			font-family: ' . $themeHeaderTextFont . ';
			font-size: ' . $themeHeaderTextSize . 'px;
			color:' . $themeHeaderTextColor . ';
			border-color: ' . $themeHeaderBorderColor . ';
			background: ' . $themeHeaderBgColor . ';
		}';
		// Таблица покупки
		$output .= '#' . $lockerName . ' .onp-pl-control-table{
			background-color: ' . $themePurchaseTableBg . '
		}';
		$output .= '#' . $lockerName . ' .onp-pl-control-table .onp-pl-ctable-header.purchase{
			background-color: ' . $themePurchaseTableHeaderBg . '
			font-family: ' . $themePurchaseTableTextHeaderFont . ';
			font-size: ' . $themePurchaseTableTextHeaderSize . 'px;
			color:' . $themePurchaseTableTextHeaderColor . ';
		}';
		$output .= '#' . $lockerName . ' .onp-pl-control-table .onp-pl-ctable-price{
			font-family: ' . $themePurchaseTableTextPriceFont . ';
			font-size: ' . $themePurchaseTableTextPriceSize . 'px;
			color:' . $themePurchaseTableTextPriceColor . ';
		}';
		$output .= '#' . $lockerName . ' .onp-pl-control-table .onp-pl-ctable-before-button-text{
			font-family: ' . $themePurchaseTableTextDescriptionFont . ';
			font-size: ' . $themePurchaseTableTextDescriptionSize . 'px;
			color:' . $themePurchaseTableTextDescriptionColor . ';
		}';
		$output .= '#' . $lockerName . ' .onp-pl-control-table .onp-pl-ctable-button.purchase{
			background-color: ' . $themePurchaseTableButtonBg . '
			font-family: ' . $themePurchaseTableTextButtonFont . ';
			font-size: ' . $themePurchaseTableTextButtonSize . 'px;
			color:' . $themePurchaseTableTextButtonColor . ';
		}';

		$output .= '</style>';

		echo $output;
	}

	add_action('opanda_print_locker_assets', 'onp_pl_print_theme_style', 10, 3);
	add_action('opanda_print_batch_locker_assets', 'onp_pl_print_theme_style', 10, 3);*/

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
		$options['paylocker']['helpUrl'] = opanda_get_item_option($lockerId, 'locker_help_url');
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



