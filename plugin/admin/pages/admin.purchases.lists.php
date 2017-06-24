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

			require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase.php';

			$count = OnpPl_Purchase::getCounts();

			if( empty($count) ) {
				$count = '0';
			}

			$this->menuTitle = sprintf(__('Покупки записей (%d)', 'plugin-paylocker'), $count);

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

			$this->styles->add(OPANDA_BIZPANDA_URL . '/assets/admin/css/libs/select2.css');

			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/select2.min.js');
			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/i18n/ru.js');

			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/load-tables-data.010000.js');
			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/create-order.010000.js');
		}

		public function indexAction()
		{
			global $paylocker;
			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.purchases.table.php');

			$table = new OnpPl_PurchasedPostTable(array('screen' => 'purchased-posts'));
			$table->prepare_items();

			?>


			<div class="wrap factory-fontawesome-000" id="onp-pl-purchased-posts-page">
				<h2>
					<?php _e('Список покупок пользователей', 'plugin-paylocker') ?>
				</h2>

				<p style="margin-top: 0px;"> <?php _e('На этой странице вы можете посмотреть покупки ваших пользователей.', 'plugin-paylocker'); ?></p>

				<div style="clear: both;">
					<a href="<?= admin_url('edit.php?post_type=opanda-item&page=purchased_posts-' . $paylocker->pluginName . '&action=createOrder'); ?>" class="button button-primary">
						<?php _e('Добавить покупку', 'plugin-paylocker'); ?>
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
				'title' => __('Выберите замок', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужный замок.', 'plugin-paylocker'),
				'data' => 'onp_pl_get_lockers_list'
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'table_name',
				'title' => __('Выберите тариф', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужную вам тарифную таблицу.', 'plugin-paylocker'),
				'data' => array()
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'selected_posts[]',
				'title' => __('ID записей', 'plugin-paylocker'),
				'hint' => __('Введите часть заголовка записи, чтобы быстро найти ее ID.', 'plugin-paylocker'),
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
				'title' => __('Типы записей' . '', 'plugin-paylocker'),
				'hint' => __('Выберите типы записей, в которых будет производится поиск ID.', 'plugin-paylocker'),
				'data' => $postTypesChecklist,
				'value' => 'post,page'
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'user_name',
				'title' => __('Имя пользователя' . '', 'plugin-paylocker'),
				'hint' => __('Введите логин пользователя, чтобы присвоить ему подписку. Например bredly122', 'plugin-paylocker')
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

				$locker_id = (int)$this->getQPost('onp_pl_subscribe_locker');
				$table_name = $this->getQPost('onp_pl_table_name', null);
				$selected_posts = $this->getQPost('onp_pl_selected_posts', array());
				$user_name = $this->getQPost('onp_pl_user_name', null);

				$args = array();

				if( !empty($locker_id) ) {
					$args['locker_id'] = $locker_id;
				} else {
					$this->throwError('locker_is_not_selected', $args);
				}

				if( empty($table_name) || $table_name == 'load' || $table_name == 'none' ) {
					$this->throwError('invalid_table_name', $args);
				}

				if( empty($selected_posts) ) {
					$this->throwError('invalid_selected_posts', $args);
				}

				if( !empty($user_name) ) {
					$args['user_name'] = $user_name;
				} else {
					$this->throwError('invalid_user_name', $args);
				}

				$current_user = get_user_by('login', $user_name);

				if( empty($current_user) ) {
					$this->throwError('user_not_found', $args);
				}

				$pricingTable = onp_pl_get_pricing_table($locker_id, $table_name);

				if( empty($pricingTable) ) {
					$this->throwError('save_error', $args);
				}

				require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');

				foreach($selected_posts as $postId) {
					try {
						$transaction = new OnpPl_Transaction(array(
							'user_id' => $current_user->ID,
							'locker_id' => $locker_id,
							'post_id' => $postId,
							'table_payment_type' => 'purchase',
							'table_name' => $table_name,
							'table_price' => $pricingTable['price']
						));

						$transaction_id = $transaction->create();

						if( empty($transaction_id) ) {
							$this->throwError('save_error', $args);
						}
						$transaction->finish();
					} catch( Exception $e ) {
						$this->throwError('save_error', $args);
					}
				}

				$this->redirectToAction('index');
				exit;
			}

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
						<?php _e('Добавление покупок для пользователя', 'plugin-paylocker') ?>
					</h2>

					<form method="POST" id="onp-pl-add-subscribe-form" class="form-horizontal" action="">
						<?php if( isset($_GET['opanda_saved']) ) { ?>
							<div id="message" class="alert alert-success">
								<p><?php _e('Подписка успешно добавлена!', 'plugin-paylocker') ?></p>
							</div>
						<?php } ?>

						<?php if( isset($_GET['opanda_error_code']) ): ?>
							<div id="message" class="alert alert-danger">
								<p>
									<?php echo $this->getErrorMessage($_GET['opanda_error_code']); ?>
								</p>
							</div>
						<?php endif; ?>


						<div style="padding-top: 10px;">
							<?php $form->html(); ?>
						</div>
						<div class="form-group form-horizontal">
							<label class="col-sm-2 control-label"> </label>

							<div class="control-group controls col-sm-10">
								<input id="onp-pl-add-subscribe-button" name="onp_pl_add_order" class="btn btn-primary" type="submit" value="<?php _e('Добавить покупку', 'plugin-paylocker') ?>"/>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?php
		}

		public function deleteAction()
		{
			$locker_id = isset($_GET['locker_id'])
				? $_GET['locker_id']
				: null;
			$user_id = isset($_GET['user_id'])
				? $_GET['user_id']
				: null;
			$post_id = isset($_GET['post_id'])
				? $_GET['post_id']
				: null;

			if( empty($locker_id) || empty($user_id) || empty($post_id) ) {
				wp_die(__('Ошибка! Не передан один из обязательных аргументов locker_id, user_id, post_id', 'plugin-paylocker'));
				exit;
			}

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase.php');
			$purchase = OnpPl_Purchase::getInstance($user_id, $locker_id, $post_id);

			// Покупка должна существовать до удаления, если нет то выводим ошибку
			if( empty($purchase) ) {
				throw new Exception(__('Покупка не найдена в базе данных', 'plugin-paylocker'));
			}

			if( $purchase && !$purchase->remove() ) {
				wp_die(__('Неизвестная ошибка! Не удалось удалить покупку.', 'plugin-paylocker'));
			}

			$this->redirectToAction('index');
			exit;
		}

		public function throwError($errorCode, $queryArgs = array(), $action = 'createOrder')
		{
			if( empty($errorCode) ) {
				throw new Exception(__('Не передан обязательный атрибут errorCode', 'plugin-paylocker'));
			}
			$this->redirectToAction($action, array_merge($queryArgs, array('opanda_error_code' => $errorCode)));
			exit;
		}

		public function getErrorMessage($code)
		{
			$errors = array(
				'default' => __('Возникла ошибка при сохранении данных!', 'plugin-paylocker'),
				'locker_is_not_selected' => __('Вы должны выбрать (или создать) хотя бы один замок для оформления подписки.', 'plugin-paylocker'),
				'invalid_table_name' => __('Вы должны выбрать тарифную таблицу, чтобы создать покупку. Если она не создана, то создайте ее в настройках замка' . '.', 'plugin-paylocker'),
				'invalid_selected_posts' => __('Вы должны выбрать, хотя бы одну запись (страницу) для оформления покупки.', 'plugin-paylocker'),
				'invalid_user_name' => __('Вы должны заполнить поле "Имя пользователя".', 'plugin-paylocker'),
				'user_not_found' => __('Пользователь с таким именем не найден.' . '.', 'plugin-paylocker'),
				'save_error' => __('Невозможно добавить подписку из-за неизвестной ошибки.' . '.', 'plugin-paylocker')
			);

			if( isset($errors[$code]) ) {
				return $errors[$code];
			}

			return $errors['default'];
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_AdminPurchasedPostsPage');
/*@mix:place*/