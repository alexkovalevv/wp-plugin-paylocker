<?php
	/**
	 * Страница настроек для paylocker
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 20.12.2016
	 * @version 1.0
	 */

	/**
	 * Добавляем новые экраны на страницу общих настроек
	 * @param $screens
	 * @return mixed
	 */
	function onp_pl_settings_screen($screens)
	{
		//$isSociallocker = BizPanda::hasPlugin('sociallocker');
		//$isOptinPanda = BizPanda::hasPlugin('optinpanda');
		//$isSocial = BizPanda::hasFeature('social');

		$updateScreens['payment'] = array(
			'title' => 'Настройки платежей',
			'class' => 'OPanda_PaymentSettings',
			'path' => BIZPANDA_PAYLOCKER_DIR . '/admin/pages/class.payment.php'
		);

		$screens = $updateScreens + $screens;

		return $screens;
	}

	add_filter('opanda_settings_screens', 'onp_pl_settings_screen');

	/**
	 * Добавляем настройки текста на страницу локализации
	 * @param $options
	 * @return mixed
	 */
	function onp_pl_settings_text_optons($options)
	{
		if( !BizPanda::hasPlugin('sociallocker') && !BizPanda::hasPlugin('optinpanda') ) {
			$options = array();
		}

		$options[] = array(
			'type' => 'form-group',
			'name' => 'paylocker-payment-form',
			'title' => __('Форма оплаты', 'bizpanda'),
			'hint' => __('Текст расположенный на экране формы оплаты.', 'bizpanda'),
			'items' => array(

				array(
					'type' => 'textbox',
					'name' => 'res_pl_payment_form_header',
					'title' => __('Заголовок формы оплаты', 'bizpanda'),
					'hint' => __('Текст заголовка формы. Располагается в самом верху экрана.', 'bizpanda'),
					'default' => __('Оплата премиум подписки', 'bizpanda'),
				),
				array(
					'type' => 'textarea',
					'name' => 'res_pl_payment_form_description',
					'title' => __('Описание формы оплаты', 'bizpanda'),
					'hint' => __('Текст описания формы оплаты. Располагается сразу под заголовком формы.', 'bizpanda'),
					'default' => __('Если вы еще не зарегистрированы на нашем сайте, после оплаты подписки зайдите в свой почтовый ящик, чтобы получить доступы к вашему аккаунту.', 'bizpanda'),
				),
				array(
					'type' => 'textbox',
					'name' => 'res_pl_payment_form_target_subscribe',
					'title' => __('Назначение платежа для премиум подписки', 'bizpanda'),
					'hint' => __('Текст назначения платежа, в случае если пользователь приобретает подписку.', 'bizpanda'),
					'default' => __('Оплата премиум подписки {order_id}', 'bizpanda'),
				),
				array(
					'type' => 'textbox',
					'name' => 'res_pl_payment_form_target_purchase',
					'title' => __('Назначение платежа для покупки', 'bizpanda'),
					'hint' => __('Текст назначения платежа, в случае если пользователь делает разовую покупку.', 'bizpanda'),
					'default' => __('Оплата премиум статьи {order_id}', 'bizpanda'),
				)
			)
		);

		return $options;
	}

	add_filter('opanda_settings_text_optons', 'onp_pl_settings_text_optons');