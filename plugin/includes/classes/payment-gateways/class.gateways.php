<?php

	/**
	 * Общий класс интеграции с платжеными сервисами
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.06.2017
	 * @version 1.0
	 */
	abstract class OnpPl_PaymentGateWays {

		protected $data = array();

		public function __set($attr, $value)
		{
			if( $attr == 'transaction_id' ) {
				if( empty($value) ) {
					throw new Exception(__('Не установлен обязательный атрибут transaction_id'));
				}
				$this->data['transaction_id'] = $value;

				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.paylocker-user.php');

				$transaction = OnpPl_Transaction::getInstance($this->transaction_id);

				if( !$transaction ) {
					throw new Exception(__('Транзакции не существует.'));
				}

				$paylocker_user = new OnpPl_PaylockerUser($transaction->user_id);

				if( empty($paylocker_user->email) ) {
					throw new Exception(__('Пользователя не существует.'));
				}

				$this->data['user_email'] = $paylocker_user->email;
				$this->data['price'] = $transaction->table_price;
			}
		}

		public function __get($attr)
		{
			if( isset($this->data[$attr]) ) {
				return $this->data[$attr];
			}

			return null;
		}


		public function __construct($transaction_id)
		{
			$this->transaction_id = trim($transaction_id);
		}

		abstract public function getPaymentUrl();

		abstract public function ipnHook();
	}