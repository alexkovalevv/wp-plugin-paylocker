<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.offers.php');

	/**
	 * Класс для работы с платными подписками
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_Subcribe extends OnpPl_Offers {

		protected static $cache_group = 'subscribe';
		protected static $db_table_name = PAYLOCKER_DB_TABLE_SUBSCRIBERS;

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
			parent::__construct($subscribe);

			$this->isActive = $this->expired_end < time();
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

			$item_data = self::instanceQuery($wpdb->prepare("
	                SELECT * FROM " . $wpdb->prefix . self::$db_table_name . "
	                WHERE user_id = '%d' and locker_id = '%d'", $user_id, $locker_id));

			if( empty($item_data) ) {
				return false;
			}

			return new OnpPl_Subcribe($item_data);
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
		 * Возвращает sql запрос для получения количества покупок
		 * @param $where
		 * @return string
		 */
		public static function getCountsSql($where)
		{
			global $wpdb;

			return "SELECT COUNT(*) AS count,
					IF(expired_end > UNIX_TIMESTAMP(), 'active', 'expired')
					AS segment
					FROM " . $wpdb->prefix . self::$db_table_name . ' ' . $where . " GROUP BY segment";
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
