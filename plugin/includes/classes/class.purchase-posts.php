<?php

	/**
	 * Класс отвечает за формление разовой покупки статей или страниц
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */
	class OnpPl_PurchasePosts {

		public $userId;
		public $postId;

		public function __construct($userId)
		{
			if( empty($userId) || empty($userId) ) {
				throw new Exception(__("Не передан обязательный атрибут userId", 'plugin-paylocker'));
			}

			$this->userId = $userId;
		}

		public function getPaidPost($postId)
		{
			global $wpdb;

			if( empty($postId) ) {
				throw new Exception(__("Не передан обязательный атрибут postId", 'plugin-paylocker'));
			}

			$result = $wpdb->get_row($wpdb->prepare("
                SELECT *
                FROM {$wpdb->prefix}opanda_pl_purchased_posts
                WHERE user_id = '%d' and post_id = '%d'
            ", $this->userId, $postId), ARRAY_A);

			return $result;
		}

		public static function isPaidPost($userId, $postId)
		{
			$purchase = new self($userId);
			$result = $purchase->getPaidPost($postId);

			return !empty($result);
		}

		public function createOrder($transactionId, $postId, $lockerId, $price)
		{
			global $wpdb;

			if( empty($transactionId) || empty($postId) ) {
				return false;
			}

			if( self::isPaidPost($this->userId, $postId) ) {
				return true;
			}

			$result = $wpdb->insert($wpdb->prefix . 'opanda_pl_purchased_posts', array(
				'post_id' => $postId,
				'user_id' => $this->userId,
				'locker_id' => $lockerId,
				'price' => $price,
				'transaction_id' => $transactionId,
				'purchased_date' => time()
			), array('%d', '%d', '%d', '%d', '%s', '%d'));

			return !empty($result);
		}

		public static function getCount($user_id = null)
		{
			global $wpdb;

			$count = null;

			$where = '';
			if( !empty($user_id) ) {
				$where = " WHERE user_id = '" . $user_id . "'";
			}

			$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}opanda_pl_purchased_posts" . $where);

			return $count;
		}
	}