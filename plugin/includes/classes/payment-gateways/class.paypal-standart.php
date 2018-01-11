<?php
	/**
	 * Стандартная интеграции paypal
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 23.06.2017
	 * @version 1.0
	 */

	require_once(PAYLOCKER_DIR . '/plugin/includes/classes/payment-gateways/class.gateways.php');

	class OnpPl_PaymentGateWayPaypal extends OnpPl_PaymentGateWays {

		public function getPaymentUrl()
		{
			$paypal_bussines_email = get_option('opanda_pl_paypal_email', null);

			// Do nothing if the payment id wasn't sent
			if( empty($paypal_bussines_email) ) {
				return null;
			}

			// Set the notify URL
			$notify_url = admin_url('admin-ajax.php') . '/?action=onp_pl_paypal_ipn';

			if( get_option('opanda_pl_paypal_sandbox', false) ) {
				$paypal_link = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
			} else {
				$paypal_link = 'https://www.paypal.com/cgi-bin/webscr/?';
			}

			$redirect_url = 'https://sociallocker.ru';

			$paypal_args = array(
				'cmd' => '_xclick',
				'business' => trim($paypal_bussines_email),
				'email' => $this->user_email,
				'currency_code' => get_option('opanda_pl_currency', 'USD'),
				'amount' => $this->price,
				'tax' => 0,
				'custom' => $this->transaction_id,
				'notify_url' => $notify_url,
				'return' => add_query_arg(array(
					'transaction_id' => base64_encode($this->transaction_id),
					'status' => 'success'
				), $redirect_url),
				'bn' => 'Cozmoslabs_SP',
				'charset' => 'UTF-8'
			);

			$paypal_link .= http_build_query($paypal_args);

			return $paypal_link;
		}

		public function ipnHook()
		{
		}
	}