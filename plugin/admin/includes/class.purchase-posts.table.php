<?php

	class OnpPl_PurchasedPostTable extends WP_List_Table {

		public function __construct($options = array())
		{
			if( current_user_can('administrator') ) {
				$options['singular'] = __('Список покупок пользователей', 'bizpanda');
				$options['plural'] = __('Список покупок пользователей', 'bizpanda');
			} else {
				$options['singular'] = __('Мои покупки', 'bizpanda');
				$options['plural'] = __('Мои покупки', 'bizpanda');
			}

			$options['ajax'] = false;

			parent::__construct($options);
		}


		public function no_items()
		{
			if( current_user_can('administrator') ) {
				echo __('На вашем сайте еще не производились покупки.', 'bizpanda');
			} else {
				echo __('Вы не приобрели еще ни одной статьи.', 'bizpanda');
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
				'price' => __('Цена', 'bizpanda'),
				'post_title' => __('Заголовок записи', 'bizpanda'),
				'purchased_date' => __('Приобретен', 'bizpanda')
			);

			if( current_user_can('administrator') ) {
				$columns['user_name'] = __('Имя пользователя', 'bizpanda');
				$columns['locker_title'] = __('Заголовок замка', 'bizpanda');
			}

			return $columns;
		}

		function prepare_items()
		{
			global $wpdb;

			$query = "SELECT * FROM {$wpdb->prefix}opanda_pl_purchased_posts";

			// where

			$where = array();

			if( !current_user_can('administrator') ) {
				$current_user = wp_get_current_user();
				$where[] = "user_id='" . $current_user->ID . "'";
			}

			if( !empty($where) ) {
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$query .= ' ORDER BY purchased_date DESC';

			$totalitems = $wpdb->query($query);
			$perpage = 20;

			$paged = !empty($_GET["paged"])
				? intval($_GET["paged"])
				: 1;
			if( empty($paged) || !is_numeric($paged) || $paged <= 0 ) {
				$paged = 1;
			}
			$totalpages = ceil($totalitems / $perpage);

			if( !empty($paged) && !empty($perpage) ) {
				$offset = ($paged - 1) * $perpage;
				$query .= ' LIMIT ' . (int)$offset . ',' . (int)$perpage;
			}

			$this->set_pagination_args(array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			));

			$this->items = $wpdb->get_results($query);
		}

		/**
		 * Shows an avatar of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_avatar($record)
		{
			if( current_user_can('administrator') ) {
				echo '<div class="onp-pl-avatar">' . get_avatar($record->user_id, 40) . '</div>';
			} else {
				echo '<div class="onp-pl-avatar">' . get_the_post_thumbnail($record->post_id, array(40, 40)) . '</div>';
			}
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

		/**
		 * Колонка цена
		 * @param $record
		 */
		public function column_price($record)
		{
			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
			$transaction = OnpPl_Transactions::getTransaction($record->transaction_id);

			if( empty($transaction) ) {
				return;
			}

			echo $transaction['table_price'] . ' руб.';
		}

		/**
		 * Колонка заголовок замка
		 * @param $record
		 */
		public function column_locker_title($record)
		{
			$output = '';

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
			$transaction = OnpPl_Transactions::getTransaction($record->transaction_id);

			if( empty($transaction) ) {
				return;
			}

			$post = get_post($transaction['locker_id']);

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
	}
	/*@mix:place*/