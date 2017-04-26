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
			<p><?php _e('Настройки платежей', 'bizpanda') ?></p>
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

			/*$options[] = array(
				'type' => 'dropdown',
				'name' => 'lang',
				'title' => __('Language of Buttons', 'bizpanda'),
				'data' => $languages,
				'default' => 'ru_RU',
				'hint' => __('Select the language that will be used for the social buttons in Social Lockers.', 'bizpanda')
			);*/

			/*$options[] = array(
				'type' => 'separator'
			);*/

			/*$options[] = array(
				'type' => 'checkbox',
				'way' => 'buttons',
				'name' => 'lazy',
				'title' => __('Lazy Loading', 'bizpanda'),
				'hint' => __('If on, creates social buttons only at the moment when the locker gets visible on the screen (for better performance).', 'bizpanda')
			);*/

			/*$options[] = array(
				'type' => 'separator'
			);*/

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_payment_form_receiver',
				'title' => __('ID кошелька в Яндекс деньги', 'bizpanda'),
				'hint' => __('Введите ваш ID кошелька в системе Яндекс денег', 'bizpanda')
			);

			/*$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_payment_form_success_url',
				'title' => __('Куда отправить пользователя после оплаты?', 'bizpanda'),
				'hint' => __('Введите Url страницы, на которую нужно отправить пользователя после оплаты.', 'bizpanda')
			);*/

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_payment_form_secret_code',
				'title' => __('Секретный код', 'bizpanda'),
				'hint' => __('Секретный код в яндекс деньги, для формирования хеш суммы.', 'bizpanda')
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_payment_form_terms',
				'title' => __('Правила и соглашения оплаты', 'bizpanda'),
				'hint' => __('Введите ссылку на страницу "Правила и соглашения оплаты", чтобы ознакомить пользователя с условиями приобретения и распространения купленного контента.', 'bizpanda')
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_alternate_payment_type_url',
				'title' => __('Альтернативный способ оплаты', 'bizpanda'),
				'hint' => __('Введите Url страницы с описанием альтернативного способа оплаты, на которую попадет пользователь, если нажмет на ссыку "Не подходит способ оплаты?"', 'bizpanda')
			);

			return $options;
		}
	}

/*@mix:place*/