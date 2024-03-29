<?php

	/**
	 * Абстрактный класс для покупок, подписок и транзакций
	 * Содержит общие методы и решения
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.12.2016
	 * @version 1.0
	 */
	abstract class OnpPl_Offers {

		protected static $cache_prefix = 'paylocker_';
		protected static $cache_group = null;
		protected static $db_table_name = null;

		public $user_id;
		public $locker_id;

		/**
		 * @param object $data
		 */
		public function __construct($data = null)
		{
			if( empty($data) ) {
				return false;
			}

			$this->setInstance($data);
		}

		/**
		 * Заполняет атрибуты классаs
		 * @param object $data
		 */
		protected function setInstance($data)
		{
			$data = wp_parse_args($data);

			if( !is_array($data) ) {
				throw new Exception(__('Атрибут data должен быть объектом или массивом.', 'plugin-paylocker'));
			}

			foreach($data as $key => $value) {
				$this->$key = $value;
			}
		}

		/**
		 * Получает имя кеш группы
		 * @return string
		 */
		protected static function getCacheGroupName()
		{
			return static::$cache_prefix . static::$cache_group;
		}

		public static function instanceQuery($sql)
		{
			global $wpdb;

			if( empty($sql) ) {
				return false;
			}

			$cache_key = md5($sql);

			$data = wp_cache_get($cache_key, self::getCacheGroupName());

			if( !$data ) {

				$data = $wpdb->get_row($sql);

				if( empty($data) ) {
					return false;
				}

				wp_cache_add($cache_key, $data, self::getCacheGroupName());
			}

			return $data;
		}

		/**
		 * Возвращает экземпляр текущего класса
		 * @param $data
		 * @return mixed
		 */
		protected static function fromClass($data)
		{
			$classname = __CLASS__;

			return new $classname($data);
		}


		/**
		 * Получает экземляр пользователя, который приобрел текущую подписку
		 * @return OnpPl_PaylockerUser
		 */
		public function getUser()
		{
			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.paylocker-user.php');

			return new OnpPl_PaylockerUser($this->user_id);
		}

		/**
		 * Получает количество элементов(покупки, транзакции, подписки)
		 * @param int $user_id
		 * @param string $filter
		 * @return array|int|mixed
		 */
		public static function getCounts($user_id = null, $filter = null)
		{
			global $wpdb;

			$counts = get_transient('onp_pl_' . static::$cache_group . '_count_' . $user_id);

			if( !$counts ) {
				$counts = array();

				$where = '';
				if( !empty($user_id) ) {
					$where = 'WHERE user_id=' . (int)$user_id;
				}

				$result = $wpdb->get_results(static::getCountsSql($where), ARRAY_A);

				foreach($result as $items) {
					$counts[$items['segment']] = (int)$items['count'];
				}

				$counts['all'] = array_sum($counts);

				set_transient('onp_pl_' . static::$cache_group . '_count_' . $user_id, $counts, MINUTE_IN_SECONDS * 5);
			}

			if( !empty($filter) ) {
				return isset($counts[$filter])
					? $counts[$filter]
					: 0;
			}

			return sizeof($counts) === 1
				? array_shift($counts)
				: $counts;
		}

		/**
		 * Получает элементы(покупки, транзакции, подписки), по установленным атрибутам
		 * @param array $args
		 * @param array $conditions
		 * @return array|bool|mixed
		 */
		public static function getItems(array $args = array(), array $conditions = array())
		{
			global $wpdb;

			$default_conditions = array(
				'order' => null,
				'limit' => null,
				'offset' => null
			);

			$conditions = wp_parse_args($conditions, $default_conditions);

			$queryString = "SELECT * ";
			$queryString .= "FROM " . $wpdb->prefix . static::$db_table_name . " ";

			$where = array();

			if( !empty($args) ) {
				$allow_args = array('user_id', 'locker_id', 'post_id');

				foreach($args as $key => $value) {

					if( is_array($value) && !empty($value) ) {
						if( sizeof($value) !== 2 || !is_string($value[0]) || !is_string($value[1]) ) {
							throw new Exception(__('Невозможно разобрать массив данных', 'plugin-paylocker'));
						}

						$where[] = $key . " " . $value[0] . " " . $value[1];
					} else {
						if( in_array($key, $allow_args) ) {
							$value = (int)$value;
						} else {
							$value = sanitize_text_field($value);
						}

						if( !empty($value) ) {
							$where[] = $key . "='" . $value . "'";
						}
					}
				}
			}

			if( !empty($where) ) {
				$queryString .= 'WHERE ' . implode(' AND ', $where) . ' ';
			}

			if( !empty($conditions['order']) ) {
				foreach($conditions['order'] as $order_table => $type) {
					$queryString .= 'ORDER BY ' . $order_table . ' ' . $type . ' ';
				}
			}

			if( !empty($conditions['limit']) ) {
				$queryString .= 'LIMIT ';
				if( !empty($conditions['offset']) ) {
					$queryString .= (int)$conditions['offset'] . ', ';
				}
				$queryString .= (int)$conditions['limit'] . ' ';
			}

			$transactions = wp_cache_get(md5($queryString), static::getCacheGroupName());

			if( !$transactions ) {
				$results = $wpdb->get_results($queryString, ARRAY_A);

				$transactions = array();

				if( !empty($results) ) {
					foreach($results as $key => $result) {
						$transactions[] = static::fromClass($result);
					}

					wp_cache_add(md5($queryString), $transactions, static::getCacheGroupName(), 60);
				}
			}

			return $transactions;
		}

		/**
		 * Возвращает sql запрос для получения количества покупок
		 * @param $where
		 * @return string
		 */
		protected static function getCountsSql($where)
		{
			return null;
		}
	}