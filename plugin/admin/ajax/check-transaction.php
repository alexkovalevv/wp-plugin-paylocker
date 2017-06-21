<?php
	/**
	 * Проверка тнранзакции оплаты
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 13.01.2017
	 * @version 1.0
	 */

	add_action('wp_ajax_onp_pl_check_transaction', 'onp_pl_check_transaction');
	add_action('wp_ajax_nopriv_onp_pl_check_transaction', 'onp_pl_check_transaction');

	function onp_pl_check_transaction()
	{
		$transaction_id = isset($_POST['transactionId'])
			? $_POST['transactionId']
			: null;

		if( empty($transaction_id) ) {
			$error = json_encode(array(
				'error' => __('Не передан Id транзации', 'plugin-paylocker'),
				'error_code' => 'invalid_transaction_id'
			));
			onp_pl_logging('ajax-errors', $error);
			echo $error;
			exit;
		}

		require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php';

		$transaction = OnpPl_Transaction::getInstance($transaction_id);

		if( empty($transaction) ) {
			$error = json_encode(array(
				'error' => __('Транзация не существует или устарела.', 'plugin-paylocker'),
				'error_code' => 'transaction_not_found'
			));
			onp_pl_logging('ajax-errors', $error);
			echo $error;
			exit;
		}

		echo json_encode(array('transaction_status' => $transaction->transaction_status));
		exit;
	}
