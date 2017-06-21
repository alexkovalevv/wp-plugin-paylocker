<?php
	
	/**
	 * Класс управления пользователем paylocker.
	 * Отвечает за установку и проверку прав, добавления, удаления или
	 * обновления платных подписок. Получения информации о подписках и
	 * покупках пользователя.
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_PaylockerUser {

		const USER_SUBSCRIPTION_ROLE_NAME = 'pl_premium_subscriber';
		const USER_SUBSCRIPTION_CAPS_PREFIX = 'pl_level_id_';
		
		public $ID;
		public $email;
		public $registered;
		public $login;
		public $url;
		public $status;
		public $display_name;
		public $caps;
		public $roles;
		public $all_caps;
		public $wp_user;

		protected $subscribes = array();
		protected $purchases = array();

		public function __construct($ID = null)
		{
			// Если ID не передан, то берем ID текущего пользователя.
			if( empty($ID) ) {
				$user = wp_get_current_user();
				$this->ID = (int)$user->ID;
			} else {
				$this->ID = (int)$ID;
			}
			
			if( empty($this->ID) ) {
				throw new Exception(__("Не передан обязательный атрибут ID", 'plugin-paylocker'));
			}
			
			$this->wp_user = new WP_User($this->ID);

			$this->email = $this->wp_user->user_email;
			$this->registered = $this->wp_user->user_registered;
			$this->login = $this->wp_user->user_login;
			$this->url = $this->wp_user->user_url;
			$this->status = $this->wp_user->user_status;
			$this->display_name = $this->wp_user->display_name;
			$this->caps = $this->wp_user->caps;
			$this->roles = $this->wp_user->roles;
			$this->all_caps = $this->wp_user->allcaps;
		}

		/**
		 * Проверяет является ли пользователь подписчиком
		 * @param null $lockerId
		 * @return bool
		 */
		public function isSubscribe($lockerId = null)
		{
			// в тех случаях, если нужно знать о наличии хотя бы одной подписки
			if( empty($lockerId) ) {
				$subscribes = $this->getActiveSubscribes();

				if( empty($subscribes) ) {
					return false;
				}

				return true;
			}

			$subscribe = $this->getSubscribe($lockerId);

			if( empty($subscribe) ) {
				return false;
			}

			return $subscribe->isActive;
		}

		/**
		 * Проверяет наличие хоть одной подписки у пользователя
		 * @param int $lockerId если не установлен, то проверяет любая подписки
		 * @param int $userId если не установлен, то берется текущий ID пользователя
		 * @return bool
		 */
		public function hasRolePremium()
		{
			return in_array(self::USER_SUBSCRIPTION_ROLE_NAME, $this->roles);
		}

		/**
		 * Проверяет наличие прав у пользователя
		 * @param int $lockerId
		 * @return bool
		 */
		public function hasCaps($lockerId)
		{
			return $this->wp_user->has_cap(self::USER_SUBSCRIPTION_CAPS_PREFIX . (int)$lockerId);
		}

		public function getTransactions($locker_id = null, $status = null)
		{
			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');

			return OnpPl_Transaction::getTransactions(array(
				'user_id' => $this->ID,
				'locker_id' => $locker_id,
				'transaction_status' => $status
			));
		}

		public function getProlangationCounts($locker_id)
		{
			if( empty($locker_id) ) {
				return null;
			}

			$transactions = $this->getTransactions($locker_id, 'finish');

			return sizeof($transactions) - 1;
		}


		public function getTotalSpendingBySubscribe($locker_id)
		{
			if( empty($locker_id) ) {
				return null;
			}

			$transactions = $this->getTransactions($locker_id, 'finish');

			if( empty($transactions) ) {
				return 0;
			}

			$sum = 0;
			foreach($transactions as $transaction) {
				$sum += $transaction->table_price;
			}

			return $sum;
		}


		/**
		 * Получает транзацию последней подписки пользователя
		 * @return array|null|object|void
		 */
		public function getLastSubscribe($locker_id = null)
		{
			try {
				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');

				return OnpPl_Subcribe::getLastSubscribe($this->ID, $locker_id);
			} catch( Exception $e ) {
				return false;
			}
		}

		/**
		 * Получает экземпляр подписки пользователя
		 * @param $locker_id
		 * @return OnpPl_Subcribe|null
		 */
		public function getSubscribe($locker_id)
		{
			if( empty($locker_id) ) {
				return false;
			}

			try {
				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');

				return OnpPl_Subcribe::getInstance($this->ID, $locker_id);
			} catch( Exception $e ) {
				return false;
			}
		}

		/**
		 * Получает все подписки пользователя
		 * @return array
		 */
		public function getSubscribes()
		{
			if( !empty($this->subscribes) ) {
				$this->subscribes = OnpPl_Subcribe::getSubscribes(array('user_id' => $this->ID));
			}

			return $this->subscribes;
		}

		/**
		 * Получает активные подписки пользователя
		 * @return array
		 */
		public function getActiveSubscribes()
		{
			$result = array();

			foreach($this->getSubscribes() as $subscribe) {
				if( $subscribe->expired_end < time() ) {
					$result[] = $subscribe;
				}
			}

			return $this->subscribes;
		}

		/**
		 * Получает все истекшие подписки пользователя
		 * @return array
		 */
		public function getExpiredSubscribes()
		{
			$result = array();

			foreach($this->getSubscribes() as $subscribe) {
				if( $subscribe->expired_end > time() ) {
					$result[] = $subscribe;
				}
			}

			return $this->subscribes;
		}

		/**
		 * Получает количество подписок пользователя
		 * @return int
		 */
		public function getCountSubscribes()
		{
			$subscribes = $this->getSubscribes();

			return count($subscribes);
		}

		/**
		 * Обновляет премиум подписку пользователя
		 * @param int $expired
		 * @param int $lockerId
		 * @return bool|false|int
		 */
		public function updateSubscribe($expired, $lockerId)
		{
			global $wpdb;

			if( empty($expired) || empty($lockerId) ) {
				return false;
			}

			return $wpdb->update($wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS, array(
				'expired_end' => $expired
			), array('user_id' => $this->ID, 'locker_id' => $lockerId), array('%d'), array('%d', '%d'));
		}

		/**
		 * Добавляет премиум подписку для пользователя
		 * @param int $expired
		 * @param int $lockerId
		 * @return false|int
		 */
		public function addSubscribe($expired, $lockerId)
		{
			global $wpdb;

			if( empty($expired) || empty($lockerId) ) {
				return false;
			}

			$expired = intval($expired);

			if( !$this->hasRolePremium() ) {
				$this->wp_user->add_role(self::USER_SUBSCRIPTION_ROLE_NAME);
			}

			if( !$this->hasCaps($lockerId) ) {
				$this->wp_user->add_cap(self::USER_SUBSCRIPTION_CAPS_PREFIX . $lockerId);
			}

			$subscribe = $this->getSubscribe($lockerId);

			if( empty($subscribe) ) {
				return $wpdb->insert($wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS, array(
					'user_id' => $this->ID,
					'locker_id' => $lockerId,
					'expired_begin' => time(),
					'expired_end' => time() + ($expired * 86400)
				), array('%d', '%d', '%d', '%d'));
			} else {
				$updateExpiredBegin = time();
				$updateExpiredEnd = time() + ($expired * 86400);

				if( time() < $subscribe->expired_end ) {
					$updateExpiredBegin = $subscribe->expired_begin;
					$updateExpiredEnd = ($subscribe->expired_end - time()) + (time() + ($expired * 86400));
				}

				return $wpdb->update($wpdb->prefix . PAYLOCKER_DB_TABLE_SUBSCRIBERS, array(
					'expired_begin' => $updateExpiredBegin,
					'expired_end' => $updateExpiredEnd
				), array('user_id' => $this->ID, 'locker_id' => $lockerId), array('%d', '%d'), array('%d'));
			}
		}
	}
