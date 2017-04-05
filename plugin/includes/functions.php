<?php
	/**
	 * Вспомогательные функции плагина
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 22.12.2016
	 * @version 1.0
	 */

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
		if( empty($lockerId) || empty($tableName) ) {
			return null;
		}

		$tables = get_post_meta($lockerId, 'opanda_pricing_tables_data', true);
		$tables = json_decode($tables, true);

		if( !isset($tables[$tableName]) ) {
			return null;
		}

		return $tables[$tableName];
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