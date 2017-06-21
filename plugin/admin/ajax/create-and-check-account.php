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
		// ID замка
		$locker_id = isset($_POST['locker_id']) && !empty($_POST['locker_id'])
			? intval($_POST['locker_id'])
			: null;

		// ID записи в которой происходит выбранный процесс
		$post_id = isset($_POST['post_id']) && !empty($_POST['post_id'])
			? intval($_POST['post_id'])
			: 0;

		// Тип платежа(подписка, покупка)
		$payment_type = isset($_POST['table_payment_type']) && !empty($_POST['table_payment_type'])
			? $_POST['table_payment_type']
			: null;

		// Имя тарифной таблицы
		$pricing_table_name = isset($_POST['table_name']) && !empty($_POST['table_name'])
			? $_POST['table_name']
			: null;

		// Цена
		$price = isset($_POST['table_price']) && !empty($_POST['table_price'])
			? intval($_POST['table_price'])
			: null;

		// Email адрес пользователя
		$email = isset($_POST['email']) && !empty($_POST['email'])
			? $_POST['email']
			: null;

		// ID транзакции
		$transaction_id = isset($_POST['transaction_id']) && !empty($_POST['transaction_id'])
			? $_POST['transaction_id']
			: false;

		// Принудительная регистрация пользователя
		$force_register_user = isset($_POST['force_register_user']) && !empty($_POST['force_register_user'])
			? $_POST['force_register_user']
			: false;
		
		if( is_string($force_register_user) ) {
			$force_register_user = $force_register_user == 'true' || $force_register_user == '1'
				? true
				: false;
		}
		
		require(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

		// Проверяем транзакцию на ее актуальность
		if( !empty($transaction_id) ) {
			try {
				$transaction = OnpPl_Transaction::getInstance($transaction_id);
				if( !empty($transaction) && $transaction->transaction_status == 'waiting' ) {
					echo json_encode(array('transaction_id' => $transaction_id));
					exit;
				}
			} catch( Exception $e ) {
				$error = json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
				onp_pl_logging('ajax-errors', $error);
				echo $error;
				exit;
			}
		}

		// Если пользователь авторизован и у него нет транзакции,
		// оформляем тразакцию на авторизованного пользователя
		if( is_user_logged_in() ) {
			
			if( !function_exists('wp_get_current_user') ) {
				return false;
			}
			
			try {
				$user = wp_get_current_user();
				$userId = $user->ID;
				
				$transaction = new OnpPl_Transaction();
				$transaction_id = $transaction->create(array(
					'user_id' => $userId,
					'locker_id' => $locker_id,
					'post_id' => $post_id,
					'table_payment_type' => $payment_type,
					'table_name' => $pricing_table_name,
					'table_price' => $price,
				));
				
				if( empty($transaction_id) ) {
					$error = json_encode(array('error' => __('Ошибка при создании транзакции платежа.', 'plugin-paylocker')));
					onp_pl_logging('ajax-errors', $error);
					echo $error;
					exit;
				}
				
				echo json_encode(array('transaction_id' => $transaction_id));
			} catch( Exception $e ) {
				$error = json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
				onp_pl_logging('ajax-errors', $error);
				echo $error;
			}
			exit;
		}
		
		$email = trim(sanitize_text_field($email));

		// Запрос пользователю на предоставление email адреса
		if( empty($email) ) {
			echo json_encode(array(
				'warning' => __('Необходимо указать email пользователя.', 'bizpanda'),
				'code' => 'entry_email'
			));
			exit;
		}

		// Это новый пользователь?
		$newUser = false;

		// Если email нет в базе данных, создаем нового пользователя
		if( !email_exists($email) ) {
			if( !$force_register_user ) {
				echo json_encode(array(
					'warning' => __('Email пользователя не зарегистрирован.', 'bizpanda'),
					'code' => 'email_not_exists',
					'email' => $email
				));
				exit;
			}
			
			$username = onp_pl_generate_username($email);
			$random_password = wp_generate_password($length = 12, false);
			
			$userId = wp_create_user($username, $random_password, $email);
			$newUser = true;
			
			if( empty($userId) || !is_int($userId) ) {
				$error = json_encode(array('error' => __('Не удалось создать аккаунт пользователя', 'bizpanda')));
				onp_pl_logging('ajax-errors', $error);
				echo $error;
			}
			
			wp_new_user_notification($userId, $random_password);
		} else {
			// Если email уже зарегистрирован, но пользователь не авторизован,
			// берем его ID по email
			$user = get_user_by('email', $email);
			$userId = $user->ID;
			$newUser = false;
		}

		// Создаем транзакцию платежа на нового или старого пользователя.
		try {
			$transaction = new OnpPl_Transaction();
			$transaction_id = $transaction->create(array(
				'user_id' => $userId,
				'locker_id' => $locker_id,
				'post_id' => $post_id,
				'table_payment_type' => $payment_type,
				'table_name' => $pricing_table_name,
				'table_price' => $price,
			));
			
			if( empty($transaction_id) ) {
				$error = json_encode(array('error' => __('Ошибка при создании транзакции платежа.', 'plugin-paylocker')));
				onp_pl_logging('ajax-errors', $error);
				echo $error;
				exit;
			}
			
			echo json_encode(array('new_user' => $newUser, 'transaction_id' => $transaction_id));
		} catch( Exception $e ) {
			$error = json_encode(array('error' => $e->getMessage(), 'code' => 'critical_error'));
			onp_pl_logging('ajax-errors', $error);
			echo $error;
		}
		exit;
	}
