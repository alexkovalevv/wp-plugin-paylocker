<?php

	/**
	 * Класс для работы с платными подписками
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_Subcribe {

		public $user_id;
		public $locker_id;
		public $expired_begin;
		public $expired_end;
		public $isActive;

		public function __set($attribute, $value)
		{
			if( ($attribute == 'user_id' || $attribute == 'locker_id') && empty($value) ) {
				throw new Exception(__('Не установлен один из обязательных атрибутов locker_id, user_id', 'plugin-paylocker'));
			}
			$this->$attribute = $value;
		}

		public function __get($attribute)
		{
			return $this->$attribute;
		}

		/**
		 * @param int $user_id
		 * @param int $locker_id
		 * @param wpdb $subscribe_info
		 * @throws Exception
		 */
		public function __construct($subscribe = null)
		{
			if( empty($subscribe) ) {
				return;
			}

			$this->setInstance($subscribe);

			$this->isActive = $this->expired_end < time();
		}

		/**
		 * Устанавливает данные подписки
		 * @param object|array $subscribe
		 * @throws Exception
		 */
		public function setInstance($subscribe)
		{
			if( is_object($subscribe) ) {
				$subscribe = get_object_vars($subscribe);
			}

			if( !is_array($subscribe) ) {
				throw new Exception(__('Атрибут transaction должен быть объектом или массивом.', 'plugin-paylocker'));
			}

			foreach($subscribe as $key => $value) {
				$this->$key = $value;
			}
		}

		/**
		 * Возвращает экземпляр подписки
		 * @param $user_id
		 * @param $locker_id
		 * @return bool|OnpPl_Subcribe
		 */
		public static function getInstance($user_id, $locker_id)
		{
			global $wpdb;

			if( empty($user_id) || empty($locker_id) ) {
				return false;
			}

			$subscribe = wp_cache_get($user_id . '-' . $locker_id, 'paylocker_subscribe');

			if( !$subscribe ) {
				$subscribe = $wpdb->get_row($wpdb->prepare("
	                SELECT *
	                FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS . "
	                WHERE user_id = '%d' and locker_id = '%d'", (int)$user_id, (int)$locker_id));

				if( empty($subscribe) ) {
					return false;
				}

				wp_cache_add($user_id . '-' . $locker_id, $subscribe, 'paylocker_subscribe');
			}

			return new OnpPl_Subcribe($subscribe);
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

		public static function getCountSubscribes($user_id = null, $filter = null)
		{
			global $wpdb;

			$counts = get_transient('onp_pl_subsribers_count_' . $user_id);

			if( !$counts ) {
				$counts = array();

				$where = '';
				if( !empty($user_id) ) {
					$where = 'WHERE user_id=' . (int)$user_id;
				}

				$result = $wpdb->get_results("
					  SELECT COUNT(*) AS count,
					  IF(expired_end > UNIX_TIMESTAMP(), 'active', 'expired')
					  AS segment
					  FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS . ' ' . $where . " GROUP BY segment", ARRAY_A);

				foreach($result as $items) {
					if( $items['segment'] == 'active' ) {
						$counts['active'] = (int)$items['count'];
					} else {
						$counts['expired'] = (int)$items['count'];
					}
				}

				$counts['all'] = array_sum($counts);

				set_transient('onp_pl_subsribers_count_' . $user_id, $counts, MINUTE_IN_SECONDS * 5);
			}

			if( !empty($filter) ) {
				return isset($counts[$filter])
					? $counts[$filter]
					: 0;
			}

			return $counts;
		}

		/**
		 * Получает доступные подписки пользователей
		 * @param array $args
		 * @param string $segment
		 * @param array $order
		 * @param int $limit
		 * @param int $offset
		 * @return mixed|void
		 */
		public static function getSubscribes(array $args = array(), $segment = 'all', array $order = array('expired_begin' => 'DESC'), $limit = null, $offset = null)
		{
			global $wpdb;

			$queryString = "SELECT * ";
			$queryString .= "FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS . " ";

			$where = array();

			if( $segment == 'active' ) {
				$where[] = "expired_end > UNIX_TIMESTAMP() ";
			} else if( $segment == 'expired' ) {
				$where[] = "expired_end < UNIX_TIMESTAMP() ";
			}

			if( !empty($args) ) {
				$allow_args = array('user_id', 'locker_id');

				foreach($args as $key => $value) {
					if( !in_array($key, $allow_args) || empty($value) ) {
						continue;
					}

					$where[] = $key . '=' . (int)$value;
				}
			}

			if( !empty($where) ) {
				$queryString .= 'WHERE ' . implode(' AND ', $where) . ' ';
			}

			if( !empty($order) ) {
				foreach($order as $order_table => $type) {
					$queryString .= 'ORDER BY ' . $order_table . ' ' . $type . ' ';
				}
			}

			if( !empty($limit) ) {
				$queryString .= 'LIMIT ';
				if( !empty($offset) ) {
					$queryString .= (int)$offset . ', ';
				}
				$queryString .= (int)$limit . ' ';
			}

			$subscribes = wp_cache_get(md5($queryString), 'paylocker_subscribe');

			if( !$subscribes ) {
				$results = $wpdb->get_results($queryString, ARRAY_A);

				$subscribes = array();

				if( !empty($results) ) {
					foreach($results as $key => $result) {
						$subscribes[] = new OnpPl_Subcribe($result);
					}

					wp_cache_add(md5($queryString), $subscribes, 'paylocker_subscribe', 60);
				}
			}

			return apply_filters('onp_pl_get_subscribes', $subscribes);
		}

		/**
		 * Получает транзацию последней подписки пользователя
		 * @param int $user_id
		 * @param int $locker_id
		 * @return array|bool|null|object|void
		 */
		public static function getLastSubscribe($user_id, $locker_id = null)
		{
			global $wpdb;

			if( empty($user_id) ) {
				return false;
			}

			$queryString = "
                SELECT * FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_TRANSACTIONS . "
                WHERE transaction_status='finish' AND user_id = '%d' ";

			if( !empty($locker_id) ) {
				$queryString .= "and locker_id = '%d' ";
			}

			$queryString .= "ORDER BY transaction_begin DESC";

			$transaction = wp_cache_get(md5($queryString), 'paylocker_subscribe');

			if( !$transaction ) {
				$transaction = $wpdb->get_row($wpdb->prepare($queryString, (int)$user_id, (int)$locker_id));

				wp_cache_add(md5($queryString), $transaction, 'paylocker_subscribe', 60);
			}

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

			return new OnpPl_Transaction($transaction);
		}

		/**
		 * Запускает задание на проверку истекших подписок пользователей
		 * Метод выполняет проверку и сброс подписки в случае, если она истекла.
		 */
		public static function runSheduleCheckPremium()
		{
			global $wpdb;

			$subscribes = $wpdb->get_results("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_subsribers
                WHERE expired_end < UNIX_TIMESTAMP()");

			if( empty($subscribes) ) {
				return null;
			}

			foreach($subscribes as $subscribe) {
				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');
				$premiumSubscriber = new OnpPl_PremiumSubscriber($subscribe->user_id);
				if( $premiumSubscriber->hasUserPremium($subscribe->locker_id) ) {
					$premiumSubscriber->resetUserPremium($subscribe->locker_id);
				}
			}
		}
	}
