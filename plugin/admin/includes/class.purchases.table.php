<?php

	class OnpPl_PurchasedPostTable extends WP_List_Table {

		public function __construct($options = array())
		{
			if( current_user_can('administrator') ) {
				$options['singular'] = __('Список покупок пользователей', 'plugin-paylocker');
				$options['plural'] = __('Список покупок пользователей', 'plugin-paylocker');
			} else {
				$options['singular'] = __('Мои покупки', 'plugin-paylocker');
				$options['plural'] = __('Мои покупки', 'plugin-paylocker');
			}

			$options['ajax'] = false;

			parent::__construct($options);
			$this->bulk_delete();
		}


		public function no_items()
		{
			if( current_user_can('administrator') ) {
				echo __('На вашем сайте еще не производились покупки.', 'plugin-paylocker');
			} else {
				echo __('Вы не приобрели еще ни одной статьи.', 'plugin-paylocker');
			}
		}

		/**
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns()
		{

			$columns = array(
				'avatar' => '',
				'post_title' => __('Заголовок записи', 'plugin-paylocker'),
				'transaction_id' => __('Транзакция платежа', 'plugin-paylocker'),
				'price' => __('Цена', 'plugin-paylocker'),
				'purchased_date' => __('Приобретен', 'plugin-paylocker')
			);

			if( current_user_can('administrator') ) {
				$columns['user_name'] = __('Имя пользователя', 'plugin-paylocker');
				$columns['locker_title'] = __('Заголовок замка', 'plugin-paylocker');
				$columns['actions'] = __('Действия', 'plugin-paylocker');
				$columns = array('cb' => '<input type="checkbox" />') + $columns;
			}

			return $columns;
		}

		public function get_bulk_actions()
		{
			$actions = array(
				'delete' => __('Удалить')
			);

			return $actions;
		}

		/**
		 * Checks and runs the bulk action 'delete'.
		 */
		public function bulk_delete()
		{
			$action = $this->current_action();

			if( 'delete' !== $action ) {
				return;
			}
			if( empty($_POST['onp_pl_products']) ) {
				return;
			}

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase.php');

			foreach($_POST['onp_pl_products'] as $productsIds) {
				$productsIds = explode('-', $productsIds);

				if( sizeof($productsIds) !== 3 ) {
					continue;
				}

				$purchase = OnpPl_Purchase::getInstance($productsIds[0], $productsIds[1], $productsIds[2]);

				if( $purchase && !$purchase->remove() ) {
					wp_die(__('Неизвестная ошибка! Не удалось удалить некоторые покупки.', 'plugin-paylocker'));
				}
			}
		}

		function prepare_items()
		{
			global $wpdb;

			// where

			$user_id = null;

			if( !current_user_can('administrator') ) {
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
			} else if( isset($_GET['sort']) ) {
				if( $_GET['sort'] === 'user_id' && isset($_GET['user_id']) ) {
					$user_id = (int)$_GET['user_id'];
				}
			}

			if( isset($_GET['s']) ) {
				$search = rtrim(trim(addcslashes(esc_sql($_GET['s']), '%_')));

				$result = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login='" . $search . "'");
				if( !empty($result) ) {
					$user_id = $result;
				} else {
					$this->items = array();

					return;
				}
			}

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase.php');
			$totalitems = OnpPl_Purchase::getCounts($user_id, 'all');

			$perpage = 20;

			$paged = !empty($_GET["paged"])
				? intval($_GET["paged"])
				: 1;
			if( empty($paged) || !is_numeric($paged) || $paged <= 0 ) {
				$paged = 1;
			}
			$totalpages = ceil($totalitems / $perpage);

			$offset = null;

			if( !empty($paged) && !empty($perpage) ) {
				$offset = ($paged - 1) * $perpage;
			}

			$this->set_pagination_args(array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			));

			$this->items = OnpPl_Purchase::getItems(array(
				'user_id' => $user_id,
			), array('order' => array('purchased_date' => 'DESC'), 'limit' => $perpage, 'offset' => $offset));
		}

		/**
		 * Shows a checkbox.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_cb($record)
		{
			echo sprintf('<input type="checkbox" name="onp_pl_products[]" value="%s" />', $record->user_id . '-' . $record->locker_id . '-' . $record->post_id);
		}

		/**
		 * Shows an avatar of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_avatar($record)
		{
			echo '<div class="onp-pl-avatar">' . get_the_post_thumbnail($record->post_id, array(40, 40)) . '</div>';
		}

		/**
		 * Колонка имя пользователя
		 * @param $record
		 */
		public function column_user_name($record)
		{
			$userdata = WP_User::get_data_by('ID', $record->user_id);

			$displayName = !empty($userdata->display_name)
				? $userdata->display_name
				: $userdata->user_login;

			$userProfileUrl = get_edit_user_link($record->user_id);

			echo sprintf(__('<a href="%s">%s</a>'), $userProfileUrl, $displayName);
		}

		/**
		 * Колонка заголовок записи
		 * @param $record
		 */
		public function column_post_title($record)
		{
			$output = '';

			$post = get_post($record->post_id);

			if( empty($post) ) {
				echo $output;

				return;
			}

			if( !empty($post) ) {
				if( current_user_can('administrator') ) {
					$postUrl = get_edit_post_link($post->ID);
				} else {
					$postUrl = get_permalink($post->ID);
				}
				$postTitle = $post->post_title;

				$output = '<a href="' . $postUrl . '"><strong>' . $postTitle . '</strong></a> ';

				if( !current_user_can('administrator') ) {
					$output .= '[<a style="color:#e91e63;" href="' . $postUrl . '">читать</a>]';
				}
			}

			echo $output;
		}

		public function column_transaction_id($record)
		{
			echo $record->transaction_id;
		}

		/**
		 * Колонка цена
		 * @param $record
		 */
		public function column_price($record)
		{
			$currency_code = get_option('opanda_pl_currency', 'USD');
			echo $record->price . onp_pl_get_currency_symbol($currency_code);
		}

		/**
		 * Колонка заголовок замка
		 * @param $record
		 */
		public function column_locker_title($record)
		{
			$output = '';

			$post = get_post($record->locker_id);

			if( !empty($post) ) {
				$postUrl = get_edit_post_link($post->ID);
				$postTitle = $post->post_title;

				$output = '<a href="' . $postUrl . '"><strong>' . $postTitle . '</strong></a>';
			}

			echo $output;
		}

		/**
		 * Колонка дыата покупки
		 * @param $record
		 */
		public function column_purchased_date($record)
		{
			echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $record->purchased_date + (get_option('gmt_offset') * 3600));
		}

		/**
		 * Колонка действий для манипуляции со списком покупок
		 * @param $record
		 */
		public function column_actions($record)
		{
			global $paylocker;

			$actions_url = admin_url('edit.php?post_type=opanda-item&page=purchased_posts-' . $paylocker->pluginName) . '&action=%s&user_id=' . $record->user_id . '&locker_id=' . $record->locker_id . '&post_id=' . $record->post_id . '';

			$delete_action_url = sprintf($actions_url, 'delete');
			$edit_action_url = sprintf($actions_url, 'edit');

			$button_template = '<a href="%s" class="button button-default"><i class="fa %s" aria-hidden="true"></i></a>';

			$button_delete = sprintf($button_template, $delete_action_url, 'fa-trash-o');
			$button_edit = sprintf($button_template, $edit_action_url, 'fa-pencil');

			echo $button_delete . ' ' . $button_edit;
		}
	}
	/*@mix:place*/