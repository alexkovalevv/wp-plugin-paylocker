<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.offers.php');

	/**
	 * Класс отвечает за формление разовой покупки статей или страниц
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_Purchase extends OnpPl_Offers {

		protected static $cache_group = 'purchase';
		protected static $db_table_name = PAYLOCKER_DB_TABLE_PURCHASES;

		public $post_id;
		public $price;
		public $transaction_id;
		public $purchased_date;

		/**
		 * Заполняет атрибуты классаs
		 * @param object $data
		 */
		protected function setInstance($data)
		{
			$default_data = array(
				'transaction_status' => 'waiting',
				'transaction_begin' => time(),
				'transaction_end' => time() + (3600 * 24)
			);

			$data = wp_parse_args($data, $default_data);

			parent::setInstance($data);
		}


		/**
		 * Проверяем, установлен ли атрибует, перед заполнением данных экземпляра
		 * @param $attribute
		 * @param $value
		 * @throws Exception
		 */
		public function __set($attribute, $value)
		{
			$needed_properties = array('user_id', 'post_id', 'locker_id', 'price', 'transaction_id', 'purchased_date');

			if( in_array($attribute, $needed_properties) && empty($value) ) {
				throw new Exception(sprintf(__('Не установлен один из обязательных атрибутов %s', 'plugin-paylocker'), var_export($needed_properties, true)));
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
		 * Возвращает экземпляр текущего класса
		 * @param int $user_id
		 * @param int $post_id
		 * @return bool|OnpPl_Purchase
		 */
		public static function getInstance($user_id, $locker_id, $post_id)
		{
			global $wpdb;

			if( empty($user_id) || empty($locker_id) || empty($post_id) ) {
				return false;
			}

			$item_data = self::instanceQuery($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . self::$db_table_name . "
                	WHERE user_id = '%d' AND locker_id='%d' AND post_id = '%d'", $user_id, $locker_id, $post_id));

			if( empty($item_data) ) {
				return false;
			}

			return new OnpPl_Purchase($item_data);
		}

		/**
		 * Возвращает sql запрос для получения количества покупок
		 * @param $where
		 * @return string
		 */
		public static function getCountsSql($where)
		{
			global $wpdb;

			return "SELECT COUNT(*) AS count, 'all' AS segment
					FROM " . $wpdb->prefix . self::$db_table_name . " " . $where . " GROUP BY segment";
		}

		/**
		 * Проверяет существует ли покупка или нет.
		 * @param $user_id
		 * @param $post_id
		 * @return bool
		 */
		public static function buyIsset($user_id, $locker_id, $post_id)
		{
			$purchase = self::getInstance($user_id, $locker_id, $post_id);

			return !empty($purchase);
		}

		/**
		 * Создает покупку
		 * @return bool
		 */
		public function create()
		{
			global $wpdb;

			if( self::buyIsset($this->user_id, $this->locker_id, $this->post_id) ) {
				return true;
			}

			$result = $wpdb->insert($wpdb->prefix . self::$db_table_name, array(
				'post_id' => $this->post_id,
				'user_id' => $this->user_id,
				'locker_id' => $this->locker_id,
				'price' => $this->price,
				'transaction_id' => $this->transaction_id,
				'purchased_date' => time()
			), array('%d', '%d', '%d', '%d', '%s', '%d'));

			if( $result ) {
				do_action('onp_pl_purchase_created', $this->post_id, $this->user_id, $this->locker_id);
			}

			return !empty($result);
		}

		/**
		 * Удаляет покупку и транзакцию покупки
		 * @return bool
		 */
		public function remove()
		{
			global $wpdb;

			$result = $wpdb->query($wpdb->prepare("
					DELETE FROM " . $wpdb->prefix . self::$db_table_name . "
					WHERE user_id='%d' AND locker_id='%d' AND post_id='%d'", $this->user_id, $this->locker_id, $this->post_id));

			if( !$result ) {
				return false;
			}

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

			$transaction = OnpPl_Transaction::getInstance($this->transaction_id);

			if( $transaction && !$transaction->remove() ) {
				return false;
			}

			delete_transient('onp_pl_' . static::$cache_group . '_count_');
			delete_transient('onp_pl_' . static::$cache_group . '_count_' . $this->user_id);

			do_action('onp_pl_purchase_removed', $this->user_id, $this->locker_id, $this->post_id);

			return true;
		}
	}