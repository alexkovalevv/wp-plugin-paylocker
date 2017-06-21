<?php

	require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.offers.abstract.php');

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
		 * Возвращает sql запрос для получения покупки
		 * @return string
		 */
		protected static function getInstanceSql()
		{
			global $wpdb;

			return "SELECT * FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_PURCHASES . "
                	WHERE user_id = '%d' and post_id = '%d'";
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
					FROM " . $wpdb->prefix . PAYLOCKER_DB_TABLE_PURCHASES . " " . $where . " GROUP BY segment";
		}
	}