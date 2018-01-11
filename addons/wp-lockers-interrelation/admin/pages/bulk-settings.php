<?php
	/**
	 * Страница статистики подписок пользователей
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */

	/**
	 * Common Settings
	 */
	class OnpBp_AddonPremiumBulkLockPage extends OPanda_AdminPage {

		private $freePosts = 0;
		private $paidPosts = 0;

		public function __construct($plugin)
		{
			$this->menuPostType = OPANDA_POST_TYPE;

			if( !current_user_can('administrator') ) {
				$this->capabilitiy = "manage_bp_addon_premium_bulk_lock";
			}

			$this->id = "addon_premium_bulk_lock";

			$this->menuTitle = __('Премиум блокировка', 'bizpanda');

			parent::__construct($plugin);
		}

		public function assets($scripts, $styles)
		{
			//$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.premium-subscribers.010000.css');
			$this->scripts->add(OPANDA_SLA_PLUGIN_URL . '/admin/assets/js/page.premium-bulk-lock.js');

			$this->scripts->request('jquery');

			$this->scripts->request(array(
				'control.checkbox',
				'control.dropdown',
				'plugin.ddslick',
				'holder.more-link'
			), 'bootstrap');

			$this->styles->request(array(
				'bootstrap.core',
				'bootstrap.form-group',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox',
				'holder.more-link'
			), 'bootstrap');
		}

		public function indexAction()
		{
			global $bizpanda;

			$options[] = array(
				'type' => 'checkbox',
				'way' => 'buttons',
				'name' => 'available_premium_bulk_lock',
				'title' => __('Массовая блокировка премиум контента', 'bizpanda'),
				'default' => false,
				'hint' => __('Если Вкл. то все статьи на сайте будут заблокированы комбинацией социального замка и замка для платного контента.', 'bizpanda')
			);

			$options[] = array(
				'type' => 'separator'
			);
			$options[] = array(
				'type' => 'dropdown',
				'name' => 'sociallocker',
				'title' => __('Выберите социальный замок', 'bizpanda'),
				'hint' => __('Выберите социальный замок для первой комбинации', 'bizpanda'),
				'data' => array($this, 'getLockers')
			);
			$options[] = array(
				'type' => 'textbox',
				'name' => 'sl_frequency',
				'title' => __('Коэфицент показов(%)', 'bizpanda'),
				'default' => '40',
				'hint' => __('Установите коэфицент показов социального замка от 0 до 100%', 'bizpanda')
			);
			$options[] = array(
				'type' => 'separator'
			);
			$options[] = array(
				'type' => 'dropdown',
				'name' => 'paylocker',
				'title' => __('Выберите замок для платного контента', 'bizpanda'),
				'hint' => __('Выберите замок для платного контента первой комбинации', 'bizpanda'),
				'data' => array($this, 'getPayLockers')
			);
			$options[] = array(
				'type' => 'textbox',
				'name' => 'pl_frequency',
				'title' => __('Коэфицент показов(%)', 'bizpanda'),
				'default' => '60',
				'hint' => __('Установите коэфицент показов замка для платного контента от 0 до 100%', 'bizpanda')
			);
			$options[] = array(
				'type' => 'separator'
			);

			// creating a form

			$form = new FactoryForms000_Form(array(
				'scope' => 'onp_bp_addon',
				'name' => 'premium-bulk-lock'
			), $bizpanda);

			$form->setProvider(new FactoryForms000_OptionsValueProvider(array(
				'scope' => 'onp_bp_addon'
			)));

			$form->add($options);

			if( isset($_POST['save-action']) ) {
				$socialocker = isset($_POST['onp_bp_addon_sociallocker'])
					? $_POST['onp_bp_addon_sociallocker']
					: null;
				$paylocker = isset($_POST['onp_bp_addon_paylocker'])
					? $_POST['onp_bp_addon_paylocker']
					: null;

				if( empty($socialocker) || empty($paylocker) ) {
					if( empty($socialocker) ) {
						$redirectArgs['opanda_error_code'] = 'sociallocker_is_not_selected';
					} else {
						$redirectArgs['opanda_error_code'] = 'paylocker_is_not_selected';
					}

					return $this->redirectToAction('index', $redirectArgs);
				}

				if( $this->beforeSaveForm($form) ) {
					$form->save();

					$redirectArgs = array(
						'opanda_saved' => 1,
						'free_posts' => $this->freePosts,
						'paid_posts' => $this->paidPosts,
					);

					return $this->redirectToAction('index', $redirectArgs);
				}
			}

			$freePosts = isset($_GET['free_posts'])
				? (int)$_GET['free_posts']
				: 0;
			$paidPosts = isset($_GET['paid_posts'])
				? (int)$_GET['paid_posts']
				: 0;

			?>
			<div class="wrap ">
				<div class="factory-bootstrap-000">
					<h2>
						<?php _e('Настройка массовой блокировки контента', 'bizpanda') ?>
					</h2>

					<p><?php _e('На этой странице вы можете посмотреть список всех пользователей, которые имеют премиум подписку.'); ?></p>

					<form method="post" class="form-horizontal" action="">
						<?php if( isset($_GET['opanda_saved']) ) { ?>
							<div id="message" class="alert alert-success">
								<p><?php _e('Настройки успешно сохранены!', 'bizpanda') ?></p>

								<p><?php _e('Проиндексировано ' . $freePosts . ' бесплатных и ' . $paidPosts . ' платных записей.', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) && $_GET['opanda_error_code'] == 'sociallocker_is_not_selected' ) { ?>
							<div id="message" class="alert alert-danger">
								<p><?php _e('Возникла ошибка при сохранении данных! Вы должны выбрать (или создать) хотя бы один социальный замок, чтобы запустить процесс массовой блокировки.', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) && $_GET['opanda_error_code'] == 'paylocker_is_not_selected' ) { ?>
							<div id="message" class="alert alert-danger">
								<p><?php _e('Возникла ошибка при сохранении данных! Вы должны выбрать (или создать) хотя бы один замок для платного контента, чтобы запустить процесс массовой блокировки.', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) && $_GET['opanda_error_code'] == 'sl_bulk_lock_is_not_configurated' ) { ?>
							<div id="message" class="alert alert-danger">
								<p><?php _e('Возникла ошибка при сохранении данных! Пожалуйста, установите настройки массовой блокировки для выбранного вам социального замка.', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) && $_GET['opanda_error_code'] == 'pl_bulk_lock_is_not_configurated' ) { ?>
							<div id="message" class="alert alert-danger">
								<p><?php _e('Возникла ошибка при сохранении данных! Пожалуйста, установите настройки массовой блокировки для выбранного вами замка платного контента.', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<div style="padding-top: 10px;">
							<?php $form->html(); ?>
						</div>
						<div class="form-group form-horizontal">
							<label class="col-sm-2 control-label"> </label>

							<div class="control-group controls col-sm-10">
								<input name="save-action" class="btn btn-primary" type="submit" value="<?php _e('Сохранить настройки', 'bizpanda') ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php
		}

		public function beforeSaveForm($form)
		{
			$rawControlsData = $form->getControls();
			$controls = array();

			foreach($rawControlsData as $control) {
				$values = $control->getValuesToSave();

				foreach($values as $keyToSave => $valueToSave) {
					$controls[$keyToSave] = $valueToSave;
				}
			}

			$bulkLockers = get_option('onp_sl_bulk_lockers');

			if( !empty($bulkLockers) ) {
				if( isset($bulkLockers[$controls['sociallocker']]) ) {
					$sociallockerBulkSettings = $bulkLockers[$controls['sociallocker']];
				} else {
					return $this->redirectToAction('index', array('opanda_error_code' => 'sl_bulk_lock_is_not_configurated'));
				}

				if( isset($bulkLockers[$controls['paylocker']]) ) {
					$paylockerBulkSettings = $bulkLockers[$controls['paylocker']];
				} else {
					return $this->redirectToAction('index', array('opanda_error_code' => 'pl_bulk_lock_is_not_configurated'));
				}

				$slFrequency = isset($controls['sl_frequency'])
					? $controls['sl_frequency']
					: 0;
				$plFrequency = isset($controls['pl_frequency'])
					? $controls['pl_frequency']
					: 0;

				$sociallockerId = isset($controls['sociallocker'])
					? $controls['sociallocker']
					: null;

				$paylockerId = isset($controls['paylocker'])
					? $controls['paylocker']
					: null;

				$posts = get_posts(array(
					'numberposts' => -1,
					'meta_key' => 'onp_bp_addon_bulk_role_locker',
					'meta_query' => array(
						array(
							'key' => 'onp_bp_addon_bulk_sandbox',
							'compare' => 'NOT EXISTS',
							'value' => true,
						)
					),
					'post_type' => 'post'
				));

				if( !empty($posts) ) {
					foreach($posts as $post) {

						$rnd = rand(1, 100);

						if( $slFrequency > $rnd ) {
							$lockerId = $sociallockerId;
							$this->freePosts++;
						} else {
							$lockerId = $paylockerId;
							$this->paidPosts++;
						}
						update_post_meta($post->ID, 'onp_bp_addon_bulk_role_locker', $lockerId);
					}
				}

				return true;
			}
		}

		public function getRandomWeightedElement(array $weightedValues)
		{
			$rand = mt_rand(1, (int)array_sum($weightedValues));

			foreach($weightedValues as $key => $value) {
				$rand -= $value;
				if( $rand <= 0 ) {
					return $key;
				}
			}
		}

		/**
		 * Получает замка по установленному типу,
		 * по умолчанию социальные замки
		 * @param string $lockerType 'social-locker', 'pay-locker'
		 */
		public function getLockers($lockerType = 'social-locker')
		{
			$allLockers = get_posts(array(
				'post_type' => 'opanda-item',
				'post_status' => 'public',
				'numberposts' => -1,
				'meta_key' => 'opanda_item',
				'meta_value' => $lockerType,
			));

			$needLockers = array();
			foreach($allLockers as $locker) {
				$needLockers[] = array($locker->ID, $locker->post_title);
			}

			return $needLockers;
		}


		/**
		 * Получает все замки для платного контента
		 * @return array
		 */
		public function getPayLockers()
		{
			return $this->getLockers('pay-locker');
		}
	}

	global $bizpanda;

	FactoryPages000::register($bizpanda, 'OnpBp_AddonPremiumBulkLockPage');
/*@mix:place*/