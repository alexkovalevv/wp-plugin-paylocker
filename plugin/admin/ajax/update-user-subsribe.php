<?php
	/**
	 * Обновляет данные подписки пользователя
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 06.02.2017
	 * @version 1.0
	 */

	add_action('wp_ajax_onp_pl_update_user_premium', 'onp_pl_update_user_premium');

	function onp_pl_update_user_premium()
	{
		if( !current_user_can('administrator') ) {
			echo json_encode(array(
				'error' => __('У вас недостаточно прав для осуществления данного типа запросов.', 'bizpanda'),
				'error_code' => 'access_denied'
			));
			exit;
		}

		$lockerId = isset($_POST['lockerId'])
			? intval($_POST['lockerId'])
			: null;

		$userId = isset($_POST['userId'])
			? intval($_POST['userId'])
			: null;

		$expiredDays = isset($_POST['expiredDays'])
			? intval($_POST['expiredDays'])
			: null;

		if( is_null($lockerId) || is_null($userId) || is_null($expiredDays) ) {
			echo json_encode(array(
				'error' => __('Не передан один из обязательных атрибутов lockerId, userId, expiredDays', 'bizpanda'),
				'error_code' => 'empty_attribute'
			));
			exit;
		}

		if( $expiredDays === 0 ) {
			$expiredTime = time();
		} else {
			$expiredTime = strtotime("+" . $expiredDays . " day");
		}

		require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.paylocker-user.php');
		$paylocker_user = new OnpPl_PaylockerUser($userId);

		if( !$paylocker_user->updateSubscribe($expiredTime, $lockerId) ) {
			echo json_encode(array(
				'error' => __('Ошибка при обновлении данных пользователя.', 'bizpanda'),
				'error_code' => 'save_error'
			));
			exit;
		}

		echo json_encode(array('response' => 'success'));
		exit;
	}