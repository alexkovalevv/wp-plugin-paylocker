<?php
	/**
	 * Проверка платежей
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 24.12.2016
	 * @version 1.0
	 */

	add_action('wp_ajax_onp_pl_payment_yandex_notification', 'onp_pl_payment_yandex_notification');
	add_action('wp_ajax_nopriv_onp_pl_payment_yandex_notification', 'onp_pl_payment_yandex_notification');

	function onp_pl_payment_yandex_notification()
	{
		//$secret = 'ogn2IrCu16fXwtUmGYbDxyVK';
		$secret = get_option('opanda_pl_payment_form_secret_code');

		$r = array(
			// p2p-incoming / card-incoming - с кошелька / с карты
			'notification_type' => $_POST['notification_type'],
			// Идентификатор операции в истории счета получателя.
			'operation_id' => $_POST['operation_id'],
			// Сумма, которая зачислена на счет получателя.
			'amount' => $_POST['amount'],
			// Сумма, которая списана со счета отправителя.
			'withdraw_amount' => $_POST['withdraw_amount'],
			// Код валюты — всегда 643 (рубль РФ согласно ISO 4217).
			'currency' => 643,
			// Дата и время совершения перевода.
			'datetime' => $_POST['datetime'],
			// Для переводов из кошелька — номер счета отправителя. Для переводов с произвольной карты — параметр содержит пустую строку.
			'sender' => $_POST['sender'],
			// Для переводов из кошелька — перевод защищен кодом протекции. Для переводов с произвольной карты — всегда false.
			'codepro' => $_POST['codepro'],
			// Метка платежа. Если ее нет, параметр содержит пустую строку.
			'label' => $_POST['label'],
			// SHA-1 hash параметров уведомления.
			'sha1_hash' => $_POST['sha1_hash']
		);

		// проверка хеш
		if( sha1($r['notification_type'] . '&' . $r['operation_id'] . '&' . $r['amount'] . '&' . $r['currency'] . '&' . $r['datetime'] . '&' . $r['sender'] . '&' . $r['codepro'] . '&' . $secret . '&' . $r['label']) != $r['sha1_hash'] ) {
			//header("HTTP/1.0 500 Internal Server Error");

			onp_pl_logging('yandex-payments', __('Верификация не пройдена. SHA1_HASH не совпадает.', 'plugin-paylocker'));
			onp_pl_logging('yandex-payments', $r);

			exit('Верификация не пройдена. SHA1_HASH не совпадает.'); // останавливаем скрипт. у вас тут может быть свой код.
		}

		// обработаем данные. нас интересует основной параметр label и withdraw_amount для получения денег без комиссии для пользователя.
		// либо если вы хотите обременить пользователя комиссией - amount, но при этом надо учесть, что яндекс на странице платежа будет писать "без комиссии".
		/*$r['amount'] = floatval($r['amount']);
		$r['withdraw_amount'] = floatval($r['withdraw_amount']);
		$r['label'] = intval($r['label']);*/ // здесь я у себя передаю id юзера, который пополняет счет на моем сайте. поэтому обрабатываю его intval

		require(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
		$transactionId = $r['label'];

		$transaction = OnpPl_Transaction::getInstance($transactionId);

		if( !empty($transaction) ) {
			try {
				$transaction->finish();
			} catch( Exception $e ) {
				onp_pl_logging('yandex-payments', $e->getMessage());
			}
		}

		exit;
	}