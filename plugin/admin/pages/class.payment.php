<?php
	/**
	 * A class for the page providing the social settings.
	 *
	 * @author Paul Kashtanoff <paul@byonepress.com>
	 * @copyright (c) 2014, OnePress Ltd
	 *
	 * @package core
	 * @since 1.0.0
	 */

	/**
	 * The Social Settings
	 *
	 * @since 1.0.0
	 */
	class OPanda_PaymentSettings extends OPanda_Settings {

		public $id = 'payment';

		/**
		 * Shows the header html of the settings screen.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function header()
		{
			?>
			<p><?php _e('Настройки платежей', 'plugin-paylocker') ?></p>
		<?php
		}

		/**
		 * Returns subscription options.
		 *
		 * @since 1.0.0
		 * @return mixed[]
		 */
		public function getOptions()
		{

			/*@mix:place*/

			$options = array();

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'pl_currency',
				'title' => __('Выберите валюту', 'plugin-paylocker'),
				'data' => onp_pl_get_currencies(),
				'default' => 'USD',
				'hint' => __('Выберите валюту в которой обрабатывать платежи.', 'plugin-paylocker')
			);

			$options[] = array(
				'type' => 'separator'
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_payment_form_terms',
				'title' => __('Правила и соглашения оплаты', 'plugin-paylocker'),
				'hint' => __('Введите ссылку на страницу "Правила и соглашения оплаты", чтобы ознакомить пользователя с условиями приобретения и распространения купленного контента.', 'plugin-paylocker')
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_alternate_payment_type_url',
				'title' => __('Альтернативный способ оплаты', 'plugin-paylocker'),
				'hint' => __('Введите Url страницы с описанием альтернативного способа оплаты, на которую попадет пользователь, если нажмет на ссыку "Не подходит способ оплаты?"', 'plugin-paylocker')
			);

			$options[] = array(
				'type' => 'separator'
			);

			/*$options[] = array(
				'type' => 'checkbox',
				'way' => 'buttons',
				'name' => 'lazy',
				'title' => __('Lazy Loading', 'plugin-paylocker'),
				'hint' => __('If on, creates social buttons only at the moment when the locker gets visible on the screen (for better performance).', 'plugin-paylocker')
			);*/

			/*$options[] = array(
				'type' => 'separator'
			);*/

			/*$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'notify_subscribe_expire',
			'title' => __('Истекает платная подписка', 'plugin-paylocker'),
			'default' => false,
			'hint' => __('Есть Вкл., отправляет пользователю уведомление об истечении срока платной подписки.', 'plugin-paylocker'),
			'eventsOn' => array(
				'show' => '#opanda-notify-subscribe-expire-options'
			),
			'eventsOff' => array(
				'hide' => '#opanda-notify-subscribe-expire-options'
			)
		);*/

			$options[] = array(
				'type' => 'dropdown',
				'way' => 'buttons',
				'name' => 'pl_select_payment_gateway',
				'data' => array(
					array('paypal', '<i class="fa fa-paypal" aria-hidden="true"></i>' . __('Paypal', 'bizpanda')),
					array('yandex', '<i class="fa fa-rub" aria-hidden="true"></i>' . __('Yandex деньги', 'bizpanda'))
				),
				'title' => __('Выберите платежный модуль', 'bizpanda'),
				'hint' => __('Пожалуйста, выберите платежный модуль, для совершения платежей.', 'bizpanda'),
				'default' => 'yandex',
				'events' => array(
					'paypal' => array(
						'hide' => '#opanda-payment-gateway-yandex-money',
						'show' => '#opanda-payment-gateway-paypal'
					),
					'yandex' => array(
						'hide' => '#opanda-payment-gateway-paypal',
						'show' => '#opanda-payment-gateway-yandex-money'
					)
				)
			);

			$options[] = array(
				'type' => 'div',
				'id' => 'opanda-payment-gateway-paypal',
				'items' => array(
					array(
						'type' => 'checkbox',
						'way' => 'buttons',
						'name' => 'pl_paypal_sandbox',
						'title' => __('Режим отладки', 'plugin-paylocker'),
						'hint' => __('Если Вкл. оплата на paypal будет происходить в режиме откладки. Для оплаты платежей вы сможете использовать тестовые аккаунты paypal.', 'plugin-paylocker')
					),
					array(
						'type' => 'textbox',
						'name' => 'pl_paypal_email',
						'title' => __('Paypal email', 'plugin-paylocker'),
						'hint' => __('Введите ваш email аккаунта в paypal.', 'plugin-paylocker')
					)
				)
			);

			$options[] = array(
				'type' => 'div',
				'id' => 'opanda-payment-gateway-yandex-money',
				'items' => array(

					array(
						'type' => 'textbox',
						'name' => 'pl_payment_form_receiver',
						'title' => __('ID кошелька в Яндекс деньги', 'plugin-paylocker'),
						'hint' => __('Введите ваш ID кошелька в системе Яндекс денег', 'plugin-paylocker')
					),
					/*$options[] = array(
						'type' => 'textbox',
						'name' => 'pl_payment_form_success_url',
						'title' => __('Куда отправить пользователя после оплаты?', 'plugin-paylocker'),
						'hint' => __('Введите Url страницы, на которую нужно отправить пользователя после оплаты.', 'plugin-paylocker')
					);*/

					array(
						'type' => 'textbox',
						'name' => 'pl_payment_form_secret_code',
						'title' => __('Секретный код', 'plugin-paylocker'),
						'hint' => __('Секретный код в яндекс деньги, для формирования хеш суммы.', 'plugin-paylocker')
					)
				)
			);

			return $options;
		}
	}

/*@mix:place*/