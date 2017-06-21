<?php
	/**
	 * Запрос для получения данных о тарифных таблицах
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 10.01.2017
	 * @version 1.0
	 */

	add_action('wp_ajax_onp_pl_get_pricing_tables', 'onp_pl_get_pricing_tables_action');
	add_action('wp_ajax_nopriv_onp_pl_get_pricing_tables', 'onp_pl_get_pricing_tables_action');

	function onp_pl_get_pricing_tables_action()
	{
		$lockerId = isset($_POST['lockerId'])
			? $_POST['lockerId']
			: null;

		$tableType = isset($_POST['tableType'])
			? $_POST['tableType']
			: 'subscribe';

		if( empty($lockerId) ) {
			echo json_encode(array(
				'error' => __('Не передан обязательный атрибут lockerId', 'bizpanda'),
				'error_code' => 'empty_locker_id'
			));
			exit;
		}

		$tables = get_post_meta($lockerId, 'opanda_pricing_tables_data', true);

		if( empty($tables) ) {
			echo json_encode(array(
				'error' => __('[Внимание]: Тарифных таблиц не существует', 'bizpanda'),
				'error_code' => 'pricing_tables_not_found'
			));
			exit;
		}

		$result = array();
		foreach($tables as $tableName => $table) {
			if( strpos($tableName, 'separator') === false && $table['paymentType'] == $tableType ) {
				$result[] = array(
					$tableName,
					$table['header'],
					$table['price'],
				);
			}
		}

		echo json_encode($result);
		exit;
	}