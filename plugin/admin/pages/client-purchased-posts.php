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
	class OnpPl_ClientPurchasedPostsPage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuIcon = '\f174';
			if( current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_page_client_purchase_posts";
			} else {
				$this->capabilitiy = "read";
			}
			$this->id = "user_orders";

			require_once PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php';

			$current_user = wp_get_current_user();
			$count = OnpPl_PurchasePosts::getCount($current_user->ID);

			if( empty($count) ) {
				$count = '0';
			}

			$this->menuTitle = sprintf(__('Покупки (%d)', 'bizpanda'), $count);

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

			require_once(PAYLOCKER_DIR . '/plugin/admin/includes/class.purchase-posts.table.php');

			$table = new OnpPl_PurchasedPostTable(array('screen' => 'purchased-posts'));
			$table->prepare_items();

			?>
			<div class="wrap factory-fontawesome-000" id="onp-pl-purchased-posts-page">
				<h2>
					<?php _e('Мои покупки', 'bizpanda') ?>
				</h2>

				<p style="margin-top: 0px;"> <?php _e('На этой странице вы можете посмотреть ваши покупки на сайте.', 'bizpanda'); ?></p>

				<form method="post" action="">
					<?php echo $table->display(); ?>
				</form>
			</div>
			<?php

			OPanda_Leads::updateCount();
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_ClientPurchasedPostsPage');
/*@mix:place*/