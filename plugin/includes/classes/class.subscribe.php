<?php

	/**
	 * Класс для работы с платными подписками
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_Subcribe {

		/*public function __construct($userId = null)
		{
		}*/

		/**
		 * Запускает задание на проверку истекших подписок пользователей
		 * Метод выполняет проверку и сброс подписки в случае, если она истекла.
		 */
		public function runSheduleCheckPremium()
		{
			$subscribes = $this->getAllExpiredSubscribes();

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

		/**
		 * Получает все истекшие платные подписки
		 * @return object|null
		 */
		public function getAllExpiredSubscribes()
		{
			global $wpdb;

			$results = $wpdb->get_results("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_subsribers
                WHERE expired_end < UNIX_TIMESTAMP()");

			if( empty($results) ) {
				return null;
			}

			return $results;
		}
	}
