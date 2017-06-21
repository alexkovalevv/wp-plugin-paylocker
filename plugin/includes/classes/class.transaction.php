<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.offers.abstract.php');

	/**
	 * Класс отвечает за работу с транзакциями
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.12.2016
	 * @version 1.0
	 */
	class OnpPl_Transaction extends OnpPl_Offers {

		protected static $cache_group = 'transactions';
		protected static $db_table_name = PAYLOCKER_DB_TABLE_TRANSACTIONS;

		public $ID;
		public $post_id;
		public $table_payment_type;
		public $table_name;
		public $table_price;
		public $transaction_status;
		public $transaction_begin;
		public $transaction_end;

		/**
		 * Проверяем, установлен ли атрибует, перед заполнением данных экземпляра
		 * @param $attribute
		 * @param $value
		 * @throws Exception
		 */
		public function __set($attribute, $value)
		{
			$needed_properties = array(
				'user_id',
				'post_id',
				'locker_id',
				'table_price',
				'table_payment_type',
				'table_name'
			);

			if( in_array($attribute, $needed_properties) && empty($value) ) {
				throw new Exception(sprintf(__('Не установлен один из обязательных атрибутов %s', 'plugin-paylocker'), var_export($needed_properties, true)));
			}

			if( $attribute == 'transaction_id' ) {
				$attribute = 'ID';
			}
			$this->$attribute = $value;
		}

		public function __get($attribute)
		{
			return $this->$attribute;
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
		 * Возвращает экземпляр транзацкции
		 * @param $transaction_id
		 * @return OnpPl_Transaction|bool
		 */
		public static function getInstance($transaction_id)
		{
			global $wpdb;

			if( empty($transaction_id) ) {
				return false;
			}

			$transaction = wp_cache_get($transaction_id, 'paylocker_transaction');

			if( !$transaction ) {
				$transaction = $wpdb->get_row($wpdb->prepare("
	                SELECT *
	                FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_TRANSACTIONS . "
	                WHERE transaction_id = '%s'
	            ", $transaction_id));

				if( empty($transaction) ) {
					return false;
				}

				wp_cache_add($transaction->transaction_id, $transaction, 'paylocker_transaction');
			}

			return new OnpPl_Transaction($transaction);
		}

		/**
		 * Возвращает sql запрос для получения покупки
		 * @return string
		 */
		protected static function getInstanceSql()
		{
			global $wpdb;

			return "SELECT * FROM " . $wpdb->prefix . self::$db_table_name . " WHERE transaction_id = '%s'";
		}

		/**
		 * Возвращает sql запрос для получения количества покупок
		 * @param $where
		 * @return string
		 */
		public static function getCountsSql($where)
		{
			global $wpdb;

			return "SELECT COUNT(*) AS count, transaction_status AS segment
					FROM " . $wpdb->prefix . self::$db_table_name . " " . $where . " GROUP BY transaction_status";
		}

		/**
		 * Создает транзакцию платежа
		 * @param array $args
		 * @return bool
		 */
		public function create(array $args)
		{
			global $wpdb;

			if( empty($args) ) {
				return false;
			}

			$defaults = array(
				'transaction_id' => $this->generateGuid(),
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

			$data = wp_parse_args($args, $defaults);

			// Eliminate all values that are not a part of the object
			$object_vars = array_keys(get_object_vars($this));

			foreach($data as $key => $val) {
				if( $key == 'transaction_id' ) {
					$key = 'ID';
				}
				if( !in_array($key, $object_vars) ) {
					unset($data[$key]);
				}
			}

			$needed_vals = array('user_id', 'locker_id', 'table_payment_type', 'table_price', 'table_name');

			$allow_insert = true;
			foreach($needed_vals as $val) {
				if( !isset($data[$val]) || empty($data[$val]) ) {
					$allow_insert = false;
				}
			}

			if( !$allow_insert ) {
				return false;
			}

			$result = $wpdb->insert($wpdb->prefix . 'opanda_pl_transactions', $data, array(
				'%s', // transaction_id
				'%d', // user_id
				'%d', // locker_id
				'%d', // post_id
				'%s', // table_payment_type
				'%s', // table_name
				'%d', // table_price
				'%s', // transaction_status
				'%d', // transaction_begin
				'%d'  // transaction_end
			));

			if( $result ) {
				$this->setInstance($data);

				do_action('onp_pl_transaction_created', $result, $data, $this->ID);

				return $this->ID;
			}

			return false;
		}

		/**
		 * Завершает операцию оплаты
		 * @param string $transactionId
		 * @return array|bool|null|object|void
		 */
		public function finish()
		{
			if( $this->transaction_status === 'finish' ) {
				return false;
			}

			if( $this->updateStatus('finish') ) {
				$userId = (int)$this->user_id;

				if( $this->table_payment_type == 'subscribe' ) {
					$tables = onp_pl_get_pricing_tables($this->locker_id);

					if( empty($tables) ) {
						throw new Exception(__("Тарифных таблиц для выбранного замка не существует.", 'plugin-paylocker'));
					}

					$tableExpired = 0;

					foreach($tables as $tableName => $table) {
						if( $this->table_name == $tableName ) {
							$tableExpired = isset($table['expired'])
								? (int)$table['expired']
								: 0;
						}
					}

					if( empty($tableExpired) ) {
						throw new Exception(__("Не установлен переиод подписки", 'plugin-paylocker'));
					}

					require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.paylocker-user.php');

					$paylocker_user = new OnpPl_PaylockerUser($userId);

					if( !$paylocker_user->addSubscribe($tableExpired, $this->locker_id) ) {
						$this->cancel();
						throw new Exception(__("Ошибка обновления премиум подписки.", 'plugin-paylocker'));
					}
				} else if( $this->table_payment_type == 'purchase' ) {
					require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php');

					$purchase = new OnpPl_PurchasePosts($userId);

					if( !$purchase->createOrder($this->ID, $this->post_id, $this->locker_id, $this->table_price) ) {
						$this->cancel();
						throw new Exception(__("Ошибка создания покупки.", 'plugin-paylocker'));
					}
				} else {
					throw new Exception(__("Неизвестный тип услуги.", 'plugin-paylocker'));
				}
			} else {
				throw new Exception(__("Ошибка изменения статуса транзации.", 'plugin-paylocker'));
			}

			return true;
		}

		/**
		 * Отменяет транзакциюю
		 * @return bool
		 */
		public function cancel()
		{
			if( $this->transaction_status === 'cancel' ) {
				return false;
			}

			return $this->updateStatus('cancel');
		}

		/**
		 * Обновляет статус транзации
		 * @param string $status
		 * @return bool
		 */
		public function updateStatus($status)
		{
			global $wpdb;

			$result_update = $wpdb->update($wpdb->prefix . PAYLOCKER_DB_TABLE_TRANSACTIONS, array(
				'transaction_status' => $status
			), array(
				'transaction_id' => $this->ID
			), array('%s'), array('%s'));

			if( $result_update ) {
				wp_cache_delete($this->ID, 'paylocker_transaction');

				do_action('onp_pl_transaction_status_changed', $result_update, $status, $this->ID);

				return true;
			}

			return false;
		}

		/**
		 * Генерирует id транзакции
		 * @return string
		 */
		private function generateGuid()
		{
			if( function_exists('com_create_guid') === true ) {
				return strtolower(trim(com_create_guid(), '{}'));
			}

			return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
		}
	}