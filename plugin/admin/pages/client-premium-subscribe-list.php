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
	class OnpPl_ClientPremiumSubscribersPage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuIcon = '\f321';

			if( current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_page_client_subscribe_list";
			} else {
				$this->capabilitiy = "read";
			}

			$this->id = "client_premium_subscribers";

			$this->menuTitle = __('Мои подписки', 'plugin-paylocker');

			parent::__construct($plugin);
		}

		public function assets($scripts, $styles)
		{
			$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.premium-subscribers.010000.css');
		}

		public function indexAction()
		{

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

				<p><?php _e('На этой странице вы можете посмотреть список всех ваших подписок.'); ?></p>
				<a href="<?= admin_url('admin.php?page=begin_subscribe-paylocker'); ?>" class="button button-primary">Оформить
					подписку</a>

				<form method="post" action="">
					<?php echo $table->display(); ?>
				</form>
			</div>
		<?php
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_ClientPremiumSubscribersPage');
/*@mix:place*/