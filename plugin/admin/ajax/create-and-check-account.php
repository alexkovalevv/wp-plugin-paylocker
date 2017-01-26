<?php
	/**
	 * Создание и проверка аккаунта пользователя.
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 22.12.2016
	 * @version 1.0
	 */

	add_action('wp_ajax_onp_pl_begin_transaction', 'onp_pl_begin_transaction');
	add_action('wp_ajax_nopriv_onp_pl_begin_transaction', 'onp_pl_begin_transaction');

	function onp_pl_begin_transaction()
	{
		$lockerId = isset($_POST['locker_id']) && !empty($_POST['locker_id'])
			? intval($_POST['locker_id'])
			: null;

		$postId = isset($_POST['post_id']) && !empty($_POST['post_id'])
			? intval($_POST['post_id'])
			: 0;

		$paymentType = isset($_POST['table_payment_type']) && !empty($_POST['table_payment_type'])
			? $_POST['table_payment_type']
			: null;

		$pricingTableName = isset($_POST['table_name']) && !empty($_POST['table_name'])
			? $_POST['table_name']
			: null;

		$price = isset($_POST['table_price']) && !empty($_POST['table_price'])
			? intval($_POST['table_price'])
			: null;

		$email = isset($_POST['email']) && !empty($_POST['email'])
			? $_POST['email']
			: null;

		$transactionId = isset($_POST['transaction_id']) && !empty($_POST['transaction_id'])
			? $_POST['transaction_id']
			: false;

		$forceRegisterUser = isset($_POST['force_register_user']) && !empty($_POST['force_register_user'])
			? $_POST['force_register_user']
			: false;

		if( is_string($forceRegisterUser) ) {
			$forceRegisterUser = $forceRegisterUser == 'true' || $forceRegisterUser == '1'
				? true
				: false;
		}

		require(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

		if( !empty($transactionId) ) {
			try {
				$transaction = OnpPl_Transactions::getTransaction($transactionId);
				if( !empty($transaction) && $transaction['transaction_status'] == 'waiting' ) {
					echo json_encode(array('transaction_id' => $transactionId));
					//OnpPl_Transactions::finishTransaction($transaction['transaction_id']);
					exit;
				}
			} catch( Exception $e ) {
				echo json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
			}
		}

		if( is_user_logged_in() ) {

			if( !function_exists('wp_get_current_user') ) {
				return false;
			}

			try {
				$user = wp_get_current_user();
				$userId = $user->ID;

				$transaction = OnpPl_Transactions::beginTransaction(array(
					'user_id' => $userId,
					'locker_id' => $lockerId,
					'post_id' => $postId,
					'table_payment_type' => $paymentType,
					'table_name' => $pricingTableName,
					'table_price' => $price,
				));

				if( empty($transaction) ) {
					echo json_encode(array('error' => 'Ошибка при создании транзакции платежа.'));
					exit;
				}

				echo json_encode(array('transaction_id' => $transaction['transaction_id']));
				//OnpPl_Transactions::finishTransaction($transaction['transaction_id']);
			} catch( Exception $e ) {
				echo json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
			}
			exit;
		}

		$email = trim(sanitize_text_field($email));

		if( empty($email) ) {
			echo json_encode(array(
				'warning' => __('Необходимо указать email пользователя.', 'bizpanda'),
				'code' => 'entry_email'
			));
			exit;
		}

		$newUser = false;

		if( !email_exists($email) ) {

			if( !$forceRegisterUser ) {
				echo json_encode(array(
					'warning' => __('Email пользователя не зарегистрирован.', 'bizpanda'),
					'code' => 'email_not_exists'
				));
				exit;
			}

			$username = onp_pl_generate_username($email);
			$random_password = wp_generate_password($length = 12, false);

			$userId = wp_create_user($username, $random_password, $email);
			$newUser = true;

			if( empty($userId) || !is_int($userId) ) {
				echo json_encode(array('error' => __('Не удалось создать аккаунт пользователя', 'bizpanda')));
			}

			wp_new_user_notification($userId, $random_password);
		} else {
			$user = get_user_by('email', $email);
			$userId = $user->ID;
			$newUser = false;
		}

		try {
			$transaction = OnpPl_Transactions::beginTransaction(array(
				'user_id' => $userId,
				'locker_id' => $lockerId,
				'post_id' => $postId,
				'table_payment_type' => $paymentType,
				'table_name' => $pricingTableName,
				'table_price' => $price,
			));

			if( empty($transaction) ) {
				echo json_encode(array('error' => 'Ошибка при создании транзакции платежа.'));
				exit;
			}

			echo json_encode(array('new_user' => $newUser, 'transaction_id' => $transaction['transaction_id']));
		} catch( Exception $e ) {
			echo json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
		}
		exit;
	}
