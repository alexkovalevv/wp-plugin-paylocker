<?php
	/**
	 * Страница статистики покупок пользователей
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */

	/**
	 * Common Settings
	 */
	class OnpPl_AdminPurchasedPostsPage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuPostType = OPANDA_POST_TYPE;

			if( !current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_orders";
			}

			$this->id = "purchased_posts";

			require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php';

			$count = OnpPl_PurchasePosts::getCount();

			if( empty($count) ) {
				$count = '0';
			}

			$this->menuTitle = sprintf(__('Покупки записей (%d)', 'bizpanda'), $count);

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

			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/create-order.010000.js');

			$this->styles->add(OPANDA_BIZPANDA_URL . '/assets/admin/css/libs/select2.css');

			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/select2.min.js');
			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/i18n/ru.js');

			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/page.add-user-order.010000.js');
		}

		public function indexAction()
		{
			global $paylocker;
			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.purchase-posts.table.php');

			$table = new OnpPl_PurchasedPostTable(array('screen' => 'purchased-posts'));
			$table->prepare_items();

			?>
			<div class="wrap factory-fontawesome-000" id="onp-pl-purchased-posts-page">
				<h2>
					<?php _e('Список покупок пользователей', 'bizpanda') ?>
				</h2>

				<p style="margin-top: 0px;"> <?php _e('На этой странице вы можете посмотреть покупки ваших пользователей.', 'bizpanda'); ?></p>

				<div style="clear: both;">
					<a href="<?= admin_url('edit.php?post_type=opanda-item&page=purchased_posts-' . $paylocker->pluginName . '&action=createOrder'); ?>" class="button button-primary">
						Добавить покупку
					</a>
				</div>
				<form method="post" action="">
					<?php echo $table->display(); ?>
				</form>
			</div>
			<?php

			OPanda_Leads::updateCount();
		}

		public function createOrderAction()
		{
			global $bizpanda;

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'subscribe_locker',
				'title' => __('Выберите замок', 'bizpanda'),
				'hint' => __('Выберите из списка нужный замок.', 'bizpanda'),
				'data' => 'onp_pl_get_lockers_list'
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'table_name',
				'title' => __('Выберите тариф', 'bizpanda'),
				'hint' => __('Выберите из списка нужную вам тарифную таблицу.', 'bizpanda'),
				'data' => array()
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'selected_posts[]',
				'title' => __('ID записей', 'bizpanda'),
				'hint' => __('Введите часть заголовка записи, чтобы быстро найти ее ID.', 'bizpanda'),
				'htmlAttrs' => array(
					'multiple' => 'multiple'
				),
				'data' => array()
			);

			$postTypes = get_post_types(array('public' => true), 'objects');
			$postTypesChecklist = array();

			foreach($postTypes as $postTypeName => $postType) {
				$postTypesChecklist[] = array(
					$postTypeName,
					$postType->label
				);
			}

			$options[] = array(
				'type' => 'list',
				'way' => 'checklist',
				'name' => 'searche_post_types',
				'title' => __('Типы записей' . '', 'bizpanda'),
				'hint' => __('Выберите типы записей, в которых будет производится поиск ID.', 'bizpanda'),
				'data' => $postTypesChecklist,
				'value' => 'post,page'
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'user_name',
				'title' => __('Имя пользователя' . '', 'bizpanda'),
				'hint' => __('Введите логин пользователя, чтобы присвоить ему подписку. Например bredly122', 'bizpanda')
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

			if( isset($_POST['onp_pl_add_order']) ) {

				$lockerId = isset($_POST['onp_pl_subscribe_locker'])
					? $_POST['onp_pl_subscribe_locker']
					: null;
				$tableName = isset($_POST['onp_pl_table_name'])
					? $_POST['onp_pl_table_name']
					: null;
				$selectedPosts = isset($_POST['onp_pl_selected_posts'])
					? $_POST['onp_pl_selected_posts']
					: array();
				$userName = isset($_POST['onp_pl_user_name'])
					? $_POST['onp_pl_user_name']
					: null;

				$args = array();

				if( !empty($lockerId) ) {
					$args['locker_id'] = $lockerId;
				} else {
					$this->throwError('locker_is_not_selected', $args);
				}

				if( empty($tableName) || $tableName == 'load' || $tableName == 'none' ) {
					$this->throwError('invalid_table_name', $args);
				}

				if( empty($selectedPosts) ) {
					$this->throwError('invalid_selected_posts', $args);
				}

				if( !empty($userName) ) {
					$args['user_name'] = $userName;
				} else {
					$this->throwError('invalid_user_name', $args);
				}

				$current_user = get_user_by('login', $userName);

				if( empty($current_user) ) {
					$this->throwError('user_not_found', $args);
				}

				$pricingTable = onp_pl_get_pricing_table($lockerId, $tableName);

				if( empty($pricingTable) ) {
					$this->throwError('save_error', $args);
				}

				require(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

				foreach($selectedPosts as $postId) {
					$transaction = OnpPl_Transactions::beginTransaction(array(
						'user_id' => $current_user->ID,
						'locker_id' => $lockerId,
						'post_id' => $postId,
						'table_payment_type' => 'purchase',
						'table_name' => $tableName,
						'table_price' => $pricingTable['price']
					));

					if( !isset($transaction['transaction_id']) || empty($transaction['transaction_id']) ) {
						$this->throwError('save_error', $args);
					}
					OnpPl_Transactions::finishTransaction($transaction['transaction_id']);
				}

				$this->redirectToAction('index');
				exit;
			}

			?>
			<div class="wrap" id="onp-pl-begin-subscribe-page">
				<div class="factory-bootstrap-000">
					<h2>
						<?php _e('Добавление покупок для пользователя', 'bizpanda') ?>
					</h2>

					<form method="POST" id="onp-pl-add-subscribe-form" class="form-horizontal" action="">
						<?php if( isset($_GET['opanda_saved']) ) { ?>
							<div id="message" class="alert alert-success">
								<p><?php _e('Подписка успешно добавлена!', 'bizpanda') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) ): ?>
							<div id="message" class="alert alert-danger">
								<p>
									<?php _e('Возникла ошибка при сохранении данных!', 'bizpanda'); ?>
									<?php if( $_GET['opanda_error_code'] == 'locker_is_not_selected' ): ?>
										<?php _e('Вы должны выбрать (или создать) хотя бы один замок для оформления подписки.', 'bizpanda') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_table_name' ): ?>
										<?php _e('Вы должны выбрать тарифную таблицу, чтобы создать покупку. Если она не создана, то создайте ее в настройках замка' . '.', 'bizpanda') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_selected_posts' ): ?>
										<?php _e('Вы должны выбрать, хотя бы одну запись (страницу) для оформления покупки.', 'bizpanda') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'invalid_user_name' ): ?>
										<?php _e('Вы должны заполнить поле "Имя пользователя".', 'bizpanda') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'user_not_found' ): ?>
										<?php _e('Пользователь с таким именем не найден.' . '.', 'bizpanda') ?>
									<?php endif; ?>
									<?php if( $_GET['opanda_error_code'] == 'save_error' ): ?>
										<?php _e('Невозможно добавить подписку из-за неизвестной ошибки.' . '.', 'bizpanda') ?>
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
								<input id="onp-pl-add-subscribe-button" name="onp_pl_add_order" class="btn btn-primary" type="submit" value="<?php _e('Добавить покупку', 'bizpanda') ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php
		}

		public function deleteAction()
		{
			global $wpdb;

			$lockerId = isset($_GET['locker_id'])
				? $_GET['locker_id']
				: null;
			$userId = isset($_GET['user_id'])
				? $_GET['user_id']
				: null;
			$postId = isset($_GET['post_id'])
				? $_GET['post_id']
				: null;
			$transactionId = isset($_GET['transaction_id'])
				? $_GET['transaction_id']
				: null;

			if( empty($lockerId) || empty($userId) || empty($postId) || empty($transactionId) ) {
				wp_die(__('Ошибка! Не передан один из обязательных аргументов lockerId, userId, postId, transactionId.', 'bizpanda'));
				exit;
			}

			$sql = $wpdb->prepare("
	            DELETE FROM {$wpdb->prefix}opanda_pl_purchased_posts
	            WHERE user_id = %d and locker_id = %d and post_id = %d
	        ", $userId, $lockerId, $postId);

			$wpdb->query($sql);

			$sql = $wpdb->prepare("
	            DELETE FROM {$wpdb->prefix}opanda_pl_transactions
	            WHERE transaction_id = %s
	        ", $transactionId);

			$wpdb->query($sql);

			$this->redirectToAction('index');
			exit;
		}

		public function throwError($errorCode, $queryArgs = array(), $action = 'createOrder')
		{
			if( empty($errorCode) ) {
				throw new Exception('Не передан обязательный атрибут errorCode');
			}
			$this->redirectToAction($action, array_merge($queryArgs, array('opanda_error_code' => $errorCode)));
			exit;
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_AdminPurchasedPostsPage');
/*@mix:place*/