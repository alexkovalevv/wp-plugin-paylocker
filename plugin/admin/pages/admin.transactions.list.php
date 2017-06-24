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
	class OnpPl_AdminTransactionsPage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuPostType = OPANDA_POST_TYPE;

			if( !current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_transactions";
			}

			$this->id = "transactions";

			require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php';
			$count = OnpPl_Transaction::getCounts(null, 'all');

			if( empty($count) ) {
				$count = 0;
			}

			$this->menuTitle = sprintf(__('Платежи (%d)', 'plugin-paylocker'), $count);

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
			/*$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.begin-subscribe.010000.css');

			$this->styles->add(OPANDA_BIZPANDA_URL . '/assets/admin/css/libs/select2.css');

			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/select2.min.js');
			$this->scripts->add(OPANDA_BIZPANDA_URL . '/assets/admin/js/libs/select2/i18n/ru.js');

			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/load-tables-data.010000.js');
			$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/create-order.010000.js');*/
		}

		public function indexAction()
		{
			global $paylocker;

			if( !class_exists('WP_List_Table') ) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.transactions.table.php');

			$table = new OnpPl_TransactionsTable();
			$table->prepare_items();

			?>


			<div class="wrap factory-fontawesome-000" id="onp-pl-transactions-page">
				<h2>
					<?php _e('Отчет по проведенным платежам', 'plugin-paylocker') ?>
				</h2>

				<p style="margin-top: 0px;"> <?php _e('На этой странице вы можете посмотреть покупки ваших пользователей.', 'plugin-paylocker'); ?></p>

				<?php
					$table->search_box(__('Найти пользователя', 'plugin-paylocker'), 's');
					$table->views();
				?>

				<form method="post" action="">
					<?php echo $table->display(); ?>
				</form>
			</div>
			<?php

			OPanda_Leads::updateCount();
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_AdminTransactionsPage');
/*@mix:place*/