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
	class OnpPl_AdminPremiumSubscribersPage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuPostType = OPANDA_POST_TYPE;

			if( !current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_page_admin_subscribe_list";
			}

			$this->id = "admin_premium_subscribers";

			$count = $this->getCount();
			if( empty($count) ) {
				$count = '0';
			}

			$this->menuTitle = sprintf(__('Подписки (%d)', 'plugin-paylocker'), $count);

			parent::__construct($plugin);
		}

		public function assets($scripts, $styles)
		{
			$this->scripts->request('jquery');

			$this->scripts->request(array(
				'control.checkbox',
				'control.dropdown',
				'bootstrap.datepicker'
			), 'bootstrap');

			$this->styles->request(array(
				'bootstrap.core',
				'bootstrap.form-group',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox',
				'bootstrap.datepicker'
			), 'bootstrap');

			$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.premium-subscribers.010000.css');
			$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.begin-subscribe.010000.css');
			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/load-tables-data.010000.js');
			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/create-subscribe.010000.js');
			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/page.admin-premium-subscribers.010000.js');
		}

		public function getCount($cache = true)
		{
			global $wpdb;

			$count = null;

			if( $cache ) {
				$count = get_transient('onp_pl_subsribers_count');
				if( $count === '0' || !empty($count) ) {
					return intval($count);
				}
			}

			$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}opanda_pl_subsribers");

			if( !empty($count) ) {
				set_transient('onp_pl_subsribers_count', $count, 60 * 5);
			}

			return $count;
		}

		public function indexAction()
		{
			global $paylocker;

			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.premium-subscribers.table.php');

			$table = new OnpPl_PremiumSubsribersTable(array('screen' => 'onp-pl-premium-subscribers'));
			$table->prepare_items();

			?>
			<div class="wrap factory-fontawesome-000" id="onp-pl-premium-subscribers-page">
				<h2>
					<?php _e('Список премиум подписок', 'plugin-paylocker') ?>
				</h2>

				<p><?php _e('На этой странице вы можете посмотреть список всех пользователей, которые имеют премиум подписку.'); ?></p>

				<div style="clear: both;">
					<a href="<?= admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName) . '&action=addPremium'; ?>" class="button button-primary">
						Добавить подписку
					</a>
				</div>

				<?php
					$table->search_box(__('Найти пользователя', 'plugin-paylocker'), 's');
					$table->views();
				?>

				<form method="post" action="">
					<?php echo $table->display(); ?>
				</form>
			</div>
		<?php
		}

		public function addPremiumAction()
		{
			global $bizpanda;

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'subscribe_locker',
				'title' => __('Выберите подписку*', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужную вам подписку.', 'plugin-paylocker'),
				'cssClass' => isset($_GET['locker_id'])
					? 'onp-pl-hide-control'
					: '',
				'value' => isset($_GET['locker_id'])
					? $_GET['locker_id']
					: null,
				'data' => array($this, 'getLockers')
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'table_name',
				'title' => __('Выберите тариф*', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужный вам тариф.', 'plugin-paylocker'),
				'data' => array()
			);

			$userName = '';
			if( isset($_GET['user_id']) && !empty($_GET['user_id']) ) {
				$userId = intval($_GET['user_id']);
				$current_user = get_user_by('id', $userId);
				if( !empty($current_user) ) {
					$userName = $current_user->data->user_login;
				}
			}

			$options[] = array(
				'type' => 'textbox',
				'name' => 'user_name',
				'title' => __('Имя пользователя*' . '', 'plugin-paylocker'),
				'hint' => __('Введите логин пользователя, чтобы присвоить ему подписку. Например bredly122', 'plugin-paylocker'),
				'value' => $userName,
				'cssClass' => !empty($userName)
					? 'onp-pl-hide-control'
					: '',
			);

			$options[] = array(
				'type' => 'html',
				'html' => '<div class="form-group">
					        	<label class="col-sm-2 control-label">Цена:</label>
					        	<div class="control-group col-sm-10"><string style="font-size: 25px;">
					        	<span class="onp-pl-table-price-text">0</span> руб.</string></div>
					        </div>'
			);

			// creating a form
			$form = new FactoryForms000_Form(array(
				'scope' => 'onp_pl',
				'name' => 'onp_pl_add_user_premium'
			), $bizpanda);

			$form->setProvider(new FactoryForms000_OptionsValueProvider(array(
				'scope' => 'paylocker'
			)));

			$form->add($options);

			$this->saveUserPremium();

			?>
			
			<script>
				if( window.__paylocker === void 0 ) {
					window.__paylocker = {};
				}
				__paylocker.lang_interface = {
					loading: '<?php _e('Загрузка', 'plugin-paylocker'); ?>'
				};
			</script>

			<div class="wrap" id="onp-pl-begin-subscribe-page">
				<div class="factory-bootstrap-000">
					<h2>
						<?php _e('Добавление подписки для пользователя', 'plugin-paylocker') ?>
					</h2>

					<p><?php _e('На этой странице вы можете посмотреть список всех пользователей, которые имеют премиум подписку.'); ?></p>

					<form method="POST" id="onp-pl-add-subscribe-form" class="form-horizontal" action="">
						<?php if( isset($_GET['opanda_saved']) ) { ?>
							<div id="message" class="alert alert-success">
								<p><?php _e('Подписка успешно добавлена!', 'plugin-paylocker') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) ): ?>
							<div id="message" class="alert alert-danger">
								<p>
									<?php _e('Возникла ошибка при сохранении данных!', 'plugin-paylocker'); ?>
									<?php if( $_GET['opanda_error_code'] == 'locker_is_not_selected' ): ?>
										<?php _e('Вы должны выбрать (или создать) хотя бы один замок для оформления подписки.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_user_name' ): ?>
										<?php _e('Вы должны заполнить поле "Имя пользователя"' . '.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_table_expired' ): ?>
										<?php _e('Не установлен период подписки или он равняется нулю.' . '.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'user_not_found' ): ?>
										<?php _e('Пользователь с таким именем не найден.' . '.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'save_error' ): ?>
										<?php _e('Невозможно добавить подписку из-за неизвестной ошибки.' . '.', 'plugin-paylocker') ?>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>


						<div style="padding-top: 10px;">
							<?php $form->html(); ?>
						</div>
						<div class="form-group form-horizontal">
							<label class="col-sm-2 control-label"> </label>

							<div class="control-group controls col-sm-10">
								<input id="onp-pl-add-subscribe-button" name="onp_pl_add_subscribe" class="btn btn-primary" type="submit" value="<?php _e('Добавить подписку', 'plugin-paylocker') ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php
		}

		public function saveUserPremium()
		{
			global $paylocker;

			if( isset($_POST['onp_pl_subscribe_locker']) ) {
				$lockerId = isset($_POST['onp_pl_subscribe_locker'])
					? $_POST['onp_pl_subscribe_locker']
					: null;
				$tableName = isset($_POST['onp_pl_table_name'])
					? $_POST['onp_pl_table_name']
					: null;
				$userName = isset($_POST['onp_pl_user_name'])
					? $_POST['onp_pl_user_name']
					: null;

				$url = 'edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName . '&action=addPremium';

				if( !empty($lockerId) ) {
					$url .= '&locker_id=' . $lockerId;
				} else {
					wp_redirect($url . '&opanda_error_code=locker_is_not_selected');
					exit;
				}

				if( !empty($userName) ) {
					$url .= '&user_name=' . $userName;
				} else {
					wp_redirect($url . '&opanda_error_code=invalid_user_name');
					exit;
				}

				$table = onp_pl_get_pricing_table($lockerId, $tableName);

				$tableExpired = isset($table['expired'])
					? intval($table['expired'])
					: 0;

				if( empty($tableExpired) ) {
					wp_redirect($url . '&opanda_error_code=invalid_table_expired');
				}

				$current_user = get_user_by('login', $userName);

				if( empty($current_user) ) {
					wp_redirect($url . '&opanda_error_code=user_not_found');
					exit;
				}

				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

				$transaction = OnpPl_Transactions::beginTransaction(array(
					'user_id' => $current_user->ID,
					'locker_id' => $lockerId,
					'post_id' => 0,
					'table_payment_type' => $table['paymentType'],
					'table_name' => $tableName,
					'table_price' => $table['price'],
				));

				if( empty($transaction) ) {
					wp_redirect($url . '&opanda_error_code=transaction_id_not_created');
					exit;
				}

				try {
					OnpPl_Transactions::finishTransaction($transaction['transaction_id']);
					wp_redirect(admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName));
					exit;
				} catch( Exception $e ) {
					wp_redirect($url . '&opanda_error_code=save_error');
					exit;
				}
			}
		}

		public function editAction()
		{
			global $paylocker, $bizpanda, $wpdb;

			$lockerId = isset($_REQUEST['locker_id'])
				? intval($_REQUEST['locker_id'])
				: null;

			$userId = isset($_REQUEST['user_id'])
				? intval($_REQUEST['user_id'])
				: null;

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'subscribe_locker',
				'title' => __('Выберите подписку*', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужную вам подписку.', 'plugin-paylocker'),
				'cssClass' => !empty($lockerId)
					? 'onp-pl-hide-control'
					: '',
				'value' => $lockerId,
				'data' => array($this, 'getLockers')
			);

			$userName = '';
			if( !empty($userId) ) {
				$current_user = get_user_by('id', $userId);
				if( !empty($current_user) ) {
					$userName = $current_user->data->user_login;
				} else {
					wp_die(__('Ошибка! Пользователь не найден.', 'plugin-paylocker'));
					exit;
				}
			}

			if( empty($userId) || empty($lockerId) ) {
				wp_die(__('Ошибка! Не переданы обязательные параметры locker_id или user_id', 'plugin-paylocker'));
				exit;
			}

			// Выполняем запрос, чтобы получить данные по подписке
			$subscribeExpiredEnd = $wpdb->get_var("
					SELECT expired_end FROM {$wpdb->prefix}opanda_pl_subsribers
					WHERE user_id='{$userId}' AND locker_id='{$lockerId}'");

			if( empty($subscribeExpiredEnd) ) {
				wp_die(__('Ошибка! Подписка не найдена.', 'plugin-paylocker'));
				exit;
			}

			$options[] = array(
				'type' => 'textbox',
				'name' => 'expired_end',
				'title' => __('Подписка истекает' . '', 'plugin-paylocker'),
				'hint' => __('Выберите дату, когда истекает подписка.', 'plugin-paylocker'),
				'value' => date('d.m.Y', $subscribeExpiredEnd),
				'htmlAttrs' => array(
					'data-provide' => 'datepicker-inline',
					'data-date-language' => 'ru',
					'data-date-autoclose' => 'true'
				)
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'user_name',
				'title' => __('Имя пользователя*' . '', 'plugin-paylocker'),
				'hint' => __('Введите логин пользователя, чтобы присвоить ему подписку. Например bredly122', 'plugin-paylocker'),
				'value' => $userName,
				'cssClass' => !empty($userName)
					? 'onp-pl-hide-control'
					: ''
			);

			// creating a form
			$form = new FactoryForms000_Form(array(
				'scope' => 'onp_pl',
				'name' => 'onp_pl_add_user_premium'
			), $bizpanda);

			$form->setProvider(new FactoryForms000_OptionsValueProvider(array(
				'scope' => 'paylocker'
			)));

			$form->add($options);

			if( isset($_POST['onp_pl_edit_subscribe']) ) {
				$exiredTime = isset($_POST['onp_pl_expired_end'])
					? strtotime($_POST['onp_pl_expired_end'])
					: null;

				$url = 'edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName . '&action=edit&locker_id=' . $lockerId . '&user_id=' . $userId;

				if( empty($exiredTime) ) {
					wp_redirect($url . '&opanda_error_code=invalid_subscribe_date');
					exit;
				}

				if( empty($userId) || empty($lockerId) ) {
					wp_die(__('Ошибка! Не переданы обязательные параметры locker_id или user_id', 'plugin-paylocker'));
					exit;
				}

				$result = $wpdb->update("{$wpdb->prefix}opanda_pl_subsribers", array(
					'expired_end' => $exiredTime
				), array('user_id' => $userId, 'locker_id' => $lockerId), array('%d'), array('%d', '%d'));

				if( !$result ) {
					wp_redirect($url . '&opanda_error_code=unexpected_error');
					exit;
				}

				wp_redirect($url . '&opanda_saved=1');
				exit;
			}
			?>
			<div class="wrap" id="onp-pl-begin-subscribe-page">
				<div class="factory-bootstrap-000">
					<h2>
						<?php _e('Редактирование подписки пользователя', 'plugin-paylocker') ?>
					</h2>

					<p><?php _e('На этой странице вы можете откредактировать подписку пользователя.'); ?></p>

					<form method="POST" id="onp-pl-add-subscribe-form" class="form-horizontal" action="">
						<?php if( isset($_GET['opanda_saved']) ) { ?>
							<div id="message" class="alert alert-success">
								<p><?php _e('Подписка успешно добавлена!', 'plugin-paylocker') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) ): ?>
							<div id="message" class="alert alert-danger">
								<p>
									<?php _e('Возникла ошибка при обновлении данных!', 'plugin-paylocker'); ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_subscribe_date' ): ?>
										<?php _e('Не установлена дата окончания платной подписки.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'incorrect_subscribe_date' ): ?>
										<?php _e('Введен некорректный формат даты.', 'plugin-paylocker') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'unexpected_error' ): ?>
										<?php _e('Неизвестная ошибка.', 'plugin-paylocker') ?>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>


						<div style="padding-top: 10px;">
							<?php $form->html(); ?>
						</div>
						<div class="form-group form-horizontal">
							<label class="col-sm-2 control-label"> </label>

							<div class="control-group controls col-sm-10">
								<input id="onp-pl-add-subscribe-button" name="onp_pl_edit_subscribe" class="btn btn-primary" type="submit" value="<?php _e('Обновить подписку', 'plugin-paylocker') ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php
		}

		public function getLockers()
		{
			$args = array(
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'post_type' => 'opanda-item',
				'meta_key' => 'opanda_item',
				'meta_value' => 'pay-locker'
			);

			$paylockers = get_posts($args);

			$needLockers = array();
			foreach($paylockers as $locker) {
				$needLockers[] = array($locker->ID, $locker->post_title);
			}

			return $needLockers;
		}
	}


	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_AdminPremiumSubscribersPage');
/*@mix:place*/