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
			'title' => __('Настройки платежей', 'plugin-paylocker'),
			'class' => 'OPanda_PaymentSettings',
			'path' => PAYLOCKER_DIR . '/plugin/admin/pages/class.payment.php'
		);

		$screens = $updateScreens + $screens;

		return $screens;
	}

	add_filter('bizpanda_settings_screens', 'onp_pl_settings_screen');

	/**
	 * Добавляем настройки уведомлений
	 * @param $options
	 * @return array
	 */
	function onp_pl_notifications_settings($options)
	{
		global $paylocker;

		$wpEditorData = array();

		if( onp_lang('ru_RU') ) {
			$defaultSubscribeExpireThemplate = file_get_contents(PAYLOCKER_DIR . '/plugin/contents/subscribe-expire-notification-ru_RU.html');
		} else {
			$defaultSubscribeExpireThemplate = file_get_contents(PAYLOCKER_DIR . '/plugin/contents/subscribe-expire-notification.html');
		}

		$options[] = array(
			'type' => 'html',
			'html' => '<h4 style="font-size: 16px;padding-left: 60px; font-weight: bold;">' . __('Плагин', 'plugin-paylocker') . ': ' . $paylocker->pluginTitle . '</h4>',
		);

		$options[] = array(
			'type' => 'separator'
		);

		$options[] = array(
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
		);

		/*$options[] = array(
			'type' => 'checkbox',
			'way' => 'buttons',
			'name' => 'notify_subscribe_expire',
			'title' => __('Подписка истекла', 'plugin-paylocker'),
			'default' => false,
			'hint' => __('Есть Вкл., отправляет пользователю уведомление об истечении срока платной подписки.', 'plugin-paylocker'),
			'eventsOn' => array(
				'show' => '#opanda-notify-subscribe-expire-options'
			),
			'eventsOff' => array(
				'hide' => '#opanda-notify-subscribe-expire-options'
			)
		);;*/

		$options[] = array(
			'type' => 'div',
			'id' => 'opanda-notify-subscribe-expire-options',
			'items' => array(

				array(
					'type' => 'separator'
				),
				array(
					'type' => 'textbox',
					'name' => 'subscribe_expire_email_from',
					'default' => get_option('admin_email'),
					'title' => __('От кого', 'bizpanda'),
					'hint' => __('Какой email адрес должен быть указан для ответа на уведомление?', 'plugin-paylocker')
				),
				array(
					'type' => 'textbox',
					'name' => 'subscribe_expire_start',
					'default' => 5,
					'title' => __('Начать отправку за (дней) до окончания подписки.', 'bizpanda'),
					'hint' => __('Укажите за сколько дней до окончания подписки, нужно начать отправлять уведомления. Например: за 5 дней до окончания.', 'plugin-paylocker')
				),
				array(
					'type' => 'textbox',
					'name' => 'subscribe_expire_count',
					'default' => 1,
					'title' => __('Сколько отправить уведомлений?', 'bizpanda'),
					'hint' => __('Укажите число уведомлений, которые будут отправлены пользователю, пока он не продлит подписку или подписка полностью не истечет.', 'plugin-paylocker')
				),
				array(
					'type' => 'textbox',
					'name' => 'subscribe_expire_interval',
					'default' => 2,
					'title' => __('Интервал между уведомлениями', 'bizpanda'),
					'hint' => __('Укажите промежуток между отправкой уведомлений в днях.', 'plugin-paylocker')
				),
				array(
					'type' => 'textbox',
					'name' => 'subscribe_expire_email_subject',
					'default' => __('У вас заканчивается подписка на сайте {sitename}', 'plugin-paylocker'),
					'title' => __('Тема', 'plugin-paylocker'),
					'hint' => __('Тема уведомления. Поддерживаемые теги: {sitename}.', 'plugin-paylocker')
				),
				array(
					'type' => 'wp-editor',
					'name' => 'subscribe_expire_email_body',
					'data' => $wpEditorData,
					'title' => __('Message', 'bizpanda'),
					'hint' => __('Шаблон email сообщения. Поддерживаемые теги: {sitename}, {siteurl}, {subscribe_url}.', 'plugin-paylocker'),
					'tinymce' => array(
						'height' => 250,
						'content_css' => OPANDA_BIZPANDA_URL . '/assets/admin/css/tinymce.010000.css'
					),
					'default' => $defaultSubscribeExpireThemplate
				),
				array(
					'type' => 'separator'
				)
			)
		);

		return $options;
	}

	add_filter("bizpanda_notifications_settings", 'onp_pl_notifications_settings');

	/**
	 * Добавляем настройки текста на страницу локализации
	 * @param $options
	 * @return array
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

	add_filter('bizpanda_settings_text_optons', 'onp_pl_settings_text_optons');