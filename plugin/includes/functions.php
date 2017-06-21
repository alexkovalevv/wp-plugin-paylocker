<?php
	/**
	 * Вспомогательные функции плагина
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 22.12.2016
	 * @version 1.0
	 */

	/**
	 * Логирование ошибок в файл
	 * используется для сложных в отладке областей плагина.
	 * @param string $group_name название группы ошибок. Используется в имени файла.
	 * @param $errors
	 */
	function onp_pl_logging($group_name, $errors)
	{
		if( empty($group_name) ) {
			$group_name = 'log';
		}

		$backtrace = debug_backtrace();

		$log_text = "Get errors\n";
		$log_text .= "--------------------------------------\n";
		$log_text .= $backtrace[0]['file'] . "\n";
		$log_text .= "in line " . $backtrace[0]['line'] . "\n";
		$log_text .= "--------------------------------------\n";
		if( is_object($errors) || is_array($errors) ) {
			$log_text .= var_export($errors, true);
		}
		$log_text .= "\n--------------------------------------\n";

		$file_path = PAYLOCKER_DIR . '/logs/' . $group_name . '.log';

		if( file_exists($file_path) && filesize($file_path) > 10000000 ) {
			unlink($file_path);
		}
		$file_log = fopen($file_path, 'a+');
		fputs($file_log, $log_text . "\n");
		fclose($file_log);
	}

	/**
	 * Функции возвращает список замков по установленным параметрам
	 * @param $type
	 */
	function onp_pl_get_lockers_list($type = 'pay-locker')
	{
		$args = array(
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_type' => 'opanda-item',
			'meta_key' => 'opanda_item',
			'meta_value' => 'pay-locker'
		);

		$paylockers = get_posts($args);

		$needLockers = array();
		foreach($paylockers as $locker) {
			$needLockers[] = array($locker->ID, $locker->post_title);
		}

		return $needLockers;
	}

	/**
	 * Получает информацию о тарифной таблице
	 * @param int $lockerId
	 * @param string $tableName
	 * @return array|null
	 */
	function onp_pl_get_pricing_table($lockerId, $tableName)
	{
		$tables = onp_pl_get_pricing_tables($lockerId);

		if( empty($tableName) || !isset($tables[$tableName]) ) {
			return null;
		}

		return $tables[$tableName];
	}

	/**
	 * Функция получает все тарифные таблицы по ID замка
	 * @param int $lockerId
	 * @return mixed|null
	 */
	function onp_pl_get_pricing_tables($lockerId)
	{
		if( empty($lockerId) ) {
			return null;
		}

		return get_post_meta($lockerId, 'opanda_pricing_tables_data', true);
	}

	/**
	 * Returns the translated role of the current user.
	 * No role, get false.
	 *
	 * @return string The translated name of the current role.
	 **/
	function onp_pl_get_current_user_role()
	{
		global $wp_roles;

		$current_user = wp_get_current_user();
		$roles = $current_user->roles;
		$role = array_shift($roles);

		return isset($wp_roles->role_names[$role])
			? translate_user_role($wp_roles->role_names[$role])
			: false;
	}

	/**
	 * Генерирует имя пользователя из email
	 * @param $email
	 * @return bool|string
	 */
	function onp_pl_generate_username($email)
	{
		$parts = explode('@', $email);
		if( count($parts) < 2 ) {
			return false;
		}

		$username = $parts[0];
		if( !username_exists($username) ) {
			return $username;
		}

		$index = 0;

		while( true ) {
			$index++;
			$username = $parts[0] . $index;

			if( !username_exists($username) ) {
				return $username;
			}
		}
	}

	if( !function_exists('array_replace') ) {
		function array_replace(array &$array, array &$array1)
		{
			$args = func_get_args();
			$count = func_num_args();

			for($i = 0; $i < $count; ++$i) {
				if( is_array($args[$i]) ) {
					foreach($args[$i] as $key => $val) {
						$array[$key] = $val;
					}
				} else {
					trigger_error(__FUNCTION__ . '(): Argument #' . ($i + 1) . ' is not an array', E_USER_WARNING);

					return null;
				}
			}

			return $array;
		}
	}
	
	/*
	 * Returns an array with the currency codes and their names	 *
	 * @return array
	 */
	function onp_pl_get_currencies()
	{
		
		$currencies = array(
			array(
				'USD',
				__('US Dollar', 'plugin-paylocker')
			),
			array(
				'EUR',
				__('Euro', 'plugin-paylocker')
			),
			array(
				'GBP',
				__('Pound Sterling', 'plugin-paylocker')
			),
			array(
				'CAD',
				__('Canadian Dollar', 'plugin-paylocker')
			),
			array(
				'AUD',
				__('Australian Dollar', 'plugin-paylocker')
			),
			array(
				'BRL',
				__('Brazilian Real', 'plugin-paylocker')
			),
			array(
				'CZK',
				__('Czech Koruna', 'plugin-paylocker')
			),
			array(
				'DKK',
				__('Danish Krone', 'plugin-paylocker')
			),
			array(
				'HKD',
				__('Hong Kong Dollar', 'plugin-paylocker')
			),
			array(
				'HUF',
				__('Hungarian Forint', 'plugin-paylocker')
			),
			array(
				'ILS',
				__('Israeli New Sheqel', 'plugin-paylocker')
			),
			array(
				'JPY',
				__('Japanese Yen', 'plugin-paylocker')
			),
			array(
				'MYR',
				__('Malaysian Ringgit', 'plugin-paylocker')
			),
			array(
				'MXN',
				__('Mexican Peso', 'plugin-paylocker')
			),
			array(
				'NOK',
				__('Norwegian Krone', 'plugin-paylocker')
			),
			array(
				'NZD',
				__('New Zealand Dollar', 'plugin-paylocker')
			),
			array(
				'PHP',
				__('Philippine Peso', 'plugin-paylocker')
			),
			array(
				'PLN',
				__('Polish Zloty', 'plugin-paylocker')
			),
			array(
				'RUB',
				__('Russian Ruble', 'plugin-paylocker')
			),
			array(
				'SGD',
				__('Singapore Dollar', 'plugin-paylocker')
			),
			array(
				'SEK',
				__('Swedish Krona', 'plugin-paylocker')
			),
			array(
				'CHF',
				__('Swiss Franc', 'plugin-paylocker')
			),
			array(
				'TWD',
				__('Taiwan New Dollar', 'plugin-paylocker')
			),
			array(
				'THB',
				__('Thai Baht', 'plugin-paylocker')
			),
			array(
				'TRY',
				__('Turkish Lira', 'plugin-paylocker')
			)
		);
		
		return apply_filters('onp_paylocker_currencies', $currencies);
	}

	/*
     * Given a currency code returns a string with the currency symbol as HTML entity
     * @return string
     */
	function onp_pl_get_currency_symbol($currency_code)
	{

		$currencies = apply_filters('onp_paylocker_currency_symbols', array(
			'AED' => '&#1583;.&#1573;', // ?
			'AFN' => '&#65;&#102;',
			'ALL' => '&#76;&#101;&#107;',
			'AMD' => '',
			'ANG' => '&#402;',
			'AOA' => '&#75;&#122;', // ?
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&#402;',
			'AZN' => '&#1084;&#1072;&#1085;',
			'BAM' => '&#75;&#77;',
			'BBD' => '&#36;',
			'BDT' => '&#2547;', // ?
			'BGN' => '&#1083;&#1074;',
			'BHD' => '.&#1583;.&#1576;', // ?
			'BIF' => '&#70;&#66;&#117;', // ?
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => '&#36;&#98;',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTN' => '&#78;&#117;&#46;', // ?
			'BWP' => '&#80;',
			'BYR' => '&#112;&#46;',
			'BZD' => '&#66;&#90;&#36;',
			'CAD' => '&#36;',
			'CDF' => '&#70;&#67;',
			'CHF' => '&#67;&#72;&#70;',
			'CLF' => '', // ?
			'CLP' => '&#36;',
			'CNY' => '&#165;',
			'COP' => '&#36;',
			'CRC' => '&#8353;',
			'CUP' => '&#8396;',
			'CVE' => '&#36;', // ?
			'CZK' => '&#75;&#269;',
			'DJF' => '&#70;&#100;&#106;', // ?
			'DKK' => '&#107;&#114;',
			'DOP' => '&#82;&#68;&#36;',
			'DZD' => '&#1583;&#1580;', // ?
			'EGP' => '&#163;',
			'ETB' => '&#66;&#114;',
			'EUR' => '&#8364;',
			'FJD' => '&#36;',
			'FKP' => '&#163;',
			'GBP' => '&#163;',
			'GEL' => '&#4314;', // ?
			'GHS' => '&#162;',
			'GIP' => '&#163;',
			'GMD' => '&#68;', // ?
			'GNF' => '&#70;&#71;', // ?
			'GTQ' => '&#81;',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => '&#76;',
			'HRK' => '&#107;&#110;',
			'HTG' => '&#71;', // ?
			'HUF' => '&#70;&#116;',
			'IDR' => '&#82;&#112;',
			'ILS' => '&#8362;',
			'INR' => '&#8377;',
			'IQD' => '&#1593;.&#1583;', // ?
			'IRR' => '&#65020;',
			'ISK' => '&#107;&#114;',
			'JEP' => '&#163;',
			'JMD' => '&#74;&#36;',
			'JOD' => '&#74;&#68;', // ?
			'JPY' => '&#165;',
			'KES' => '&#75;&#83;&#104;', // ?
			'KGS' => '&#1083;&#1074;',
			'KHR' => '&#6107;',
			'KMF' => '&#67;&#70;', // ?
			'KPW' => '&#8361;',
			'KRW' => '&#8361;',
			'KWD' => '&#1583;.&#1603;', // ?
			'KYD' => '&#36;',
			'KZT' => '&#1083;&#1074;',
			'LAK' => '&#8365;',
			'LBP' => '&#163;',
			'LKR' => '&#8360;',
			'LRD' => '&#36;',
			'LSL' => '&#76;', // ?
			'LTL' => '&#76;&#116;',
			'LVL' => '&#76;&#115;',
			'LYD' => '&#1604;.&#1583;', // ?
			'MAD' => '&#1583;.&#1605;.', //?
			'MDL' => '&#76;',
			'MGA' => '&#65;&#114;', // ?
			'MKD' => '&#1076;&#1077;&#1085;',
			'MMK' => '&#75;',
			'MNT' => '&#8366;',
			'MOP' => '&#77;&#79;&#80;&#36;', // ?
			'MRO' => '&#85;&#77;', // ?
			'MUR' => '&#8360;', // ?
			'MVR' => '.&#1923;', // ?
			'MWK' => '&#77;&#75;',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => '&#77;&#84;',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => '&#67;&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#65020;',
			'PAB' => '&#66;&#47;&#46;',
			'PEN' => '&#83;&#47;&#46;',
			'PGK' => '&#75;', // ?
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PYG' => '&#71;&#115;',
			'QAR' => '&#65020;',
			'RON' => '&#108;&#101;&#105;',
			'RSD' => '&#1044;&#1080;&#1085;&#46;',
			'RUB' => '&#1088;&#1091;&#1073;',
			'RWF' => '&#1585;.&#1587;',
			'SAR' => '&#65020;',
			'SBD' => '&#36;',
			'SCR' => '&#8360;',
			'SDG' => '&#163;', // ?
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&#163;',
			'SLL' => '&#76;&#101;', // ?
			'SOS' => '&#83;',
			'SRD' => '&#36;',
			'STD' => '&#68;&#98;', // ?
			'SVC' => '&#36;',
			'SYP' => '&#163;',
			'SZL' => '&#76;', // ?
			'THB' => '&#3647;',
			'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
			'TMT' => '&#109;',
			'TND' => '&#1583;.&#1578;',
			'TOP' => '&#84;&#36;',
			'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => '',
			'UAH' => '&#8372;',
			'UGX' => '&#85;&#83;&#104;',
			'USD' => '&#36;',
			'UYU' => '&#36;&#85;',
			'UZS' => '&#1083;&#1074;',
			'VEF' => '&#66;&#115;',
			'VND' => '&#8363;',
			'VUV' => '&#86;&#84;',
			'WST' => '&#87;&#83;&#36;',
			'XAF' => '&#70;&#67;&#70;&#65;',
			'XCD' => '&#36;',
			'XDR' => '',
			'XOF' => '',
			'XPF' => '&#70;',
			'YER' => '&#65020;',
			'ZAR' => '&#82;',
			'ZMK' => '&#90;&#75;', // ?
			'ZWL' => '&#90;&#36;',
		));

		$currency_symbol = (isset($currencies[$currency_code])
			? $currencies[$currency_code]
			: $currency_code);

		return $currency_symbol;
	}