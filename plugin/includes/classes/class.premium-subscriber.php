<?php

	/**
	 * Класс отвечает за оформление премиум подписки
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_PremiumSubscriber {

		public $userId;
		private $_userPremiumList = array();
		private $_userActivePremiumList = array();

		public function __construct($userId = null)
		{
			if( empty($userId) ) {
				$user = wp_get_current_user();
				$this->userId = $user->ID;
			} else {
				$this->userId = $userId;
			}

			if( empty($this->userId) ) {
				throw new Exception(__("Не передан обязательный атрибут userId", "bizpanda"));
			}
		}

		public function timeToDayFormat($expiresTime)
		{
			$expires = round(($expiresTime - time()) / 86400);
			if( $expires < 0 ) {
				$expires = 0;
			}

			return $expires;
		}

		/**
		 * Получает список всех подписок в базе данных
		 * @param int $userId если не установлен, то берется текущий ID пользователя
		 * @return array|null|object
		 */
		public function getUserPremiumList($active = false)
		{
			global $wpdb;

			if( !empty($this->_userPremiumList) && !$active ) {
				return $this->_userPremiumList;
			}

			if( !empty($this->_userActivePremiumList) && $active ) {
				return $this->_userActivePremiumList;
			}

			$results = $wpdb->get_results($wpdb->prepare("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_subsribers
                WHERE user_id = '%d'", $this->userId), ARRAY_A);

			if( empty($results) ) {
				return null;
			}

			foreach($results as $result) {
				$this->_userPremiumList[$result['locker_id']] = $result;
				if( $result['expired_end'] > time() ) {
					$this->_userActivePremiumList[$result['locker_id']] = $result;
				}
			}

			if( $active ) {
				return $this->_userActivePremiumList;
			}

			return $this->_userPremiumList;
		}

		/**
		 * Проверка наличия премиум
		 * @param null $lockerId
		 * @return bool
		 * @throws Exception
		 */
		public function checkUserPremium($lockerId = null)
		{
			if( empty($lockerId) ) {
				$premiumList = $this->getUserPremiumList(true);

				if( empty($premiumList) ) {
					return false;
				}

				return true;
			}

			$subscribe = $this->getUserPremiumInfo($lockerId);

			if( empty($subscribe) ) {
				return false;
			}

			if( $subscribe['expired_end'] < time() ) {
				return false;
			}

			return true;
		}

		/**
		 * Сбрасывает премиум пользователя
		 * @param $lockerId
		 * @return bool
		 */
		public function resetUserPremium($lockerId = null)
		{
			$user = new WP_User($this->userId);

			if( empty($lockerId) ) {
				$subscribes = $this->getUserPremiumList();
				if( empty($subscribes) ) {
					return;
				}
				foreach($subscribes as $subscribe) {
					if( $subscribe['expired_end'] < time() ) {
						$user->remove_cap('plocker_id_' . $subscribe['locker_id']);
					}
				}
				$user->remove_role("pl_premium_subscriber");

				return;
			}

			$user->remove_cap('plocker_id_' . $lockerId);

			if( !$this->checkUserPremium() ) {
				$user->remove_role("pl_premium_subscriber");
			}
		}

		/**
		 * Проверяет наличие хоть одной подписки у пользователя
		 * @param int $lockerId если не установлен, то проверяет любая подписки
		 * @param int $userId если не установлен, то берется текущий ID пользователя
		 * @throws Exception
		 */
		public function hasUserPremium($lockerId = null)
		{
			$user = new WP_User($this->userId);

			if( empty($lockerId) ) {
				return in_array("pl_premium_subscriber", $user->roles);
			}

			return in_array("pl_premium_subscriber", $user->roles) && $user->has_cap('plocker_id_' . $lockerId);
		}

		/**
		 * Получает информацию о подписке пользователя
		 * @param $lockerId
		 * @param null $userId
		 * @return array|null|object|void
		 * @throws Exception
		 */
		public function getUserPremiumInfo($lockerId)
		{
			global $wpdb;

			if( empty($lockerId) ) {
				throw new Exception(__("Не передан обязательный атрибут lockerId", "bizpanda"));
			}

			return $wpdb->get_row($wpdb->prepare("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_subsribers
                WHERE user_id = '%d' and locker_id = '%d'
            ", $this->userId, $lockerId), ARRAY_A);
		}

		/**
		 * Обновляет премиум подписку пользователя
		 * @param int $expired
		 * @param int $lockerId
		 * @return bool|false|int
		 */
		public function updateUserPremium($expired, $lockerId)
		{
			global $wpdb;

			if( empty($expired) || empty($lockerId) ) {
				return false;
			}

			return $wpdb->update("{$wpdb->prefix}opanda_pl_subsribers", array(
				'expired_end' => $expired
			), array('user_id' => $this->userId, 'locker_id' => $lockerId), array('%d'), array('%d', '%d'));
		}

		/**
		 * Добавляет премиум подписку для пользователя
		 * @param int $expired
		 * @param int $lockerId
		 * @return false|int
		 */
		public function addUserPremium($expired, $lockerId)
		{
			global $wpdb;

			if( empty($expired) || empty($lockerId) ) {
				return false;
			}

			$expired = intval($expired);
			$user = new WP_User($this->userId);

			if( !in_array("pl_premium_subscriber", $user->roles) ) {
				$user->add_role('pl_premium_subscriber');
			}

			if( !$user->has_cap('plocker_id_' . $lockerId) ) {
				$user->add_cap('plocker_id_' . $lockerId);
			}

			$subscribe = $this->getUserPremiumInfo($lockerId);

			if( empty($subscribe) ) {
				return $wpdb->insert($wpdb->prefix . 'opanda_pl_subsribers', array(
					'user_id' => $this->userId,
					'locker_id' => $lockerId,
					'expired_begin' => time(),
					'expired_end' => time() + ($expired * 86400)
				), array('%d', '%d', '%d', '%d'));
			} else {
				$updateExpiredBegin = time();
				$updateExpiredEnd = time() + ($expired * 86400);

				if( time() < $subscribe['expired_end'] ) {
					$updateExpiredBegin = $subscribe['expired_begin'];
					$updateExpiredEnd = ($subscribe['expired_end'] - time()) + (time() + ($expired * 86400));
				}

				return $wpdb->update("{$wpdb->prefix}opanda_pl_subsribers", array(
					'expired_begin' => $updateExpiredBegin,
					'expired_end' => $updateExpiredEnd
				), array('user_id' => $this->userId, 'locker_id' => $lockerId), array('%d', '%d'), array('%d'));
			}
		}
	}
