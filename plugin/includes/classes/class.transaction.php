<?php

	/**
	 * Класс отвечает за работу с транзакциями
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.12.2016
	 * @version 1.0
	 */
	class OnpPl_Transactions {

		public static function getTransaction($transactionId)
		{
			global $wpdb;

			$transaction = $wpdb->get_row($wpdb->prepare("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_transactions
                WHERE transaction_id = '%s'
            ", $transactionId), ARRAY_A);

			if( empty($transaction) ) {
				return false;
			}

			return $transaction;
		}

		public static function beginTransaction(array $args)
		{
			global $wpdb;

			if( empty($args) ) {
				return false;
			}

			$toSave = array(
				'transaction_id' => self::generateGuid(),
				'user_id' => null,
				'locker_id' => null,
				'post_id' => null,
				'table_payment_type' => null,
				'table_name' => null,
				'table_price' => 0,
				'transaction_status' => 'waiting',
				'transaction_begin' => time(),
				'transaction_end' => time() + (3600 * 24)
			);

			$toSave = array_replace($toSave, $args);

			if( empty($toSave['user_id']) || empty($toSave['locker_id']) || empty($toSave['table_payment_type']) || empty($toSave['table_price']) || empty($toSave['table_name']) ) {
				return false;
			}

			$result = $wpdb->insert($wpdb->prefix . 'opanda_pl_transactions', $toSave, array(
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d'
			));

			return $result
				? $toSave
				: false;
		}

		/**
		 * Завершает операцию оплаты
		 * @param string $transactionId
		 * @return array|bool|null|object|void
		 */
		public static function finishTransaction($transactionId)
		{
			$transaction = self::getTransaction($transactionId);

			if( empty($transaction) ) {
				return false;
			}

			if( $transaction['status'] === 'finish' ) {
				return $transaction;
			}

			if( self::setStatus($transactionId, 'finish') ) {
				$userId = $transaction['user_id'];
				$tablePaymentType = $transaction['table_payment_type'];

				if( $tablePaymentType == 'subscribe' ) {
					$tables = get_post_meta($transaction['locker_id'], 'opanda_pricing_tables_data', true);
					$tables = json_decode($tables, true);

					$tableExpired = 0;

					foreach($tables as $tableName => $table) {
						if( $transaction['table_name'] == $tableName ) {
							$tableExpired = isset($table['expired'])
								? intval($table['expired'])
								: 0;
						}
					}

					if( empty($tableExpired) ) {
						return new WP_Error('finish-transaction', __("Не установлен переиод подписки", "bizpanda"));
					}

					require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');

					$premium = new OnpPl_PremiumSubscriber($userId);

					if( !$premium->updateUserPremium($tableExpired, $transaction['locker_id']) ) {
						self::transactionCancel($transactionId);
						throw new Exception(__("Ошибка обновления премиум подписки.", "bizpanda"));
					}
				} else if( $tablePaymentType == 'purchase' ) {
					require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php');

					$purchase = new OnpPl_PurchasePosts($userId);
					if( !$purchase->createOrder($transactionId, $transaction['post_id'], $transaction['locker_id'], $transaction['table_price']) ) {
						self::transactionCancel($transactionId);
						throw new Exception(__("Ошибка создания покупки.", "bizpanda"));
					}
				} else {
					throw new Exception(__("Неизвестный тип услуги.", "bizpanda"));
				}
			} else {
				throw new Exception(__("Ошибка изменения статуса транзации.", "bizpanda"));
			}

			return $transaction;
		}

		/**
		 * Отменяет транзакциюю в случае ошибки.
		 * @param $transactionId
		 */
		public static function transactionCancel($transactionId)
		{
			$transaction = self::getTransaction($transactionId);
			if( empty($transaction) ) {
				throw new Exception(__("Транзакция не найдена или ее время истекло.", "bizpanda"));
			}

			if( $transaction['transaction_status'] === 'cancel' ) {
				return $transaction;
			}

			return self::setStatus($transactionId, 'cancel');
		}

		/**
		 * Обновленяет статус транзакции
		 * @param $transactionId
		 * @param $status
		 * @return false|int
		 */
		private static function setStatus($transactionId, $status)
		{
			global $wpdb;

			return $wpdb->update("{$wpdb->prefix}opanda_pl_transactions", array('transaction_status' => $status), array('transaction_id' => $transactionId), array('%s'), array('%s'));
		}

		/**
		 * Генерирует id транзакции         *
		 * @param array $data
		 * @return string
		 */
		private static function generateGuid($data = array())
		{
			if( function_exists('com_create_guid') === true ) {
				return strtolower(trim(com_create_guid(), '{}'));
			}

			return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
		}
	}