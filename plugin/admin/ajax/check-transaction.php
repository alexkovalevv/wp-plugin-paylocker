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
		$transactionId = isset($_POST['transactionId'])
			? $_POST['transactionId']
			: null;

		if( empty($transactionId) ) {
			echo json_encode(array('error' => 'Не передан Id транзации', 'error_code' => 'invalid_transaction_id'));
			exit;
		}

		require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php';

		$transaction = OnpPl_Transactions::getTransaction($transactionId);

		if( empty($transaction) ) {
			echo json_encode(array(
				'error' => 'Транзация не существует или устарела.',
				'error_code' => 'transaction_not_found'
			));
			exit;
		}

		echo json_encode(array('transaction_status' => $transaction['transaction_status']));
		exit;
	}
