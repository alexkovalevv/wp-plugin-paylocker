<?php

	class OnpPl_TransactionsTable extends WP_List_Table {

		public function __construct($options = array())
		{
			
			$options['singular'] = __('Транзакции', 'plugin-paylocker');
			$options['plural'] = __('Транзакции', 'plugin-paylocker');
			
			$options['ajax'] = false;

			parent::__construct($options);
		}


		public function no_items()
		{
			echo __('На вашем сайте еще не производились покупки.', 'plugin-paylocker');
		}

		/**
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns()
		{

			$columns = array(
				'user_id' => 'ID',
				'user_name' => __('Имя пользователя', 'plugin-paylocker'),
				'post_title' => __('Страница покупки', 'plugin-paylocker'),
				'locker_title' => __('Заголовок замка', 'plugin-paylocker'),
				'transaction_id' => __('ID транзакции', 'plugin-paylocker'),
				'transaction_status' => __('Статус', 'plugin-paylocker'),
				'price' => __('Цена', 'plugin-paylocker'),
				'transaction_begin' => __('Дата', 'plugin-paylocker')
			);

			return $columns;
		}

		public function get_views()
		{
			global $paylocker;

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
			$counts = OnpPl_Transaction::getCounts();

			$link = 'edit.php?post_type=' . OPANDA_POST_TYPE . '&page=transactions-' . $paylocker->pluginName;

			$currentStatus = isset($_GET['opanda_status'])
				? $_GET['opanda_status']
				: 'finish';

			if( !in_array($currentStatus, array('all', 'finish', 'waiting', 'cancel')) ) {
				$currentStatus = 'finish';
			}

			$items = array(
				'view-all' => array(
					'title' => __('Все', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'all', $link),
					'count' => isset($counts['all'])
						? $counts['all']
						: 0,
					'current' => $currentStatus == 'all'
				),
				'view-finish' => array(
					'title' => __('Завершены', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'finish', $link),
					'count' => isset($counts['finish'])
						? $counts['finish']
						: 0,
					'current' => $currentStatus == 'finish'
				),
				'view-waiting' => array(
					'title' => __('Ожидают', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'waiting', $link),
					'count' => isset($counts['waiting'])
						? $counts['waiting']
						: 0,
					'current' => $currentStatus == 'waiting'
				),
				'view-cancel' => array(
					'title' => __('Отменены', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'cancel', $link),
					'count' => isset($counts['cancel'])
						? $counts['cancel']
						: 0,
					'current' => $currentStatus == 'cancel'
				)
			);

			$views = array();
			foreach($items as $name => $data) {
				$views[$name] = "<a href='" . $data['link'] . "' class='" . ($data['current']
						? 'current'
						: '') . "'>" . $data['title'] . " <span class='count'>(" . number_format_i18n($data['count']) . ")</span></a>";
			}

			return $views;
		}

		function prepare_items()
		{
			global $wpdb;

			$user_id = null;

			if( isset($_GET['sort']) ) {
				if( $_GET['sort'] === 'user_id' && isset($_GET['user_id']) ) {
					$user_id = (int)$_GET['user_id'];
				}
			}

			$segment = 'finish';

			if( isset($_GET['opanda_status']) && !empty($_GET['opanda_status']) ) {
				if( in_array($_GET['opanda_status'], array('all', 'waiting', 'finish', 'cancel')) ) {
					$segment = $_GET['opanda_status'];
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

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.transaction.php');
			$totalitems = OnpPl_Transaction::getCounts($user_id, $segment);

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

			if( $segment == 'all' ) {
				$segment = null;
			}

			$this->items = OnpPl_Transaction::getItems(array(
				'user_id' => $user_id,
				'transaction_status' => $segment
			), array('order' => array('transaction_begin' => 'DESC'), 'limit' => $perpage, 'offset' => $offset));
		}

		public function search_box($text, $input_id)
		{
			if( !count($this->items) && !isset($_GET['s']) ) {
				return;
			}

			$postType = isset($_GET['post_type'])
				? htmlspecialchars($_GET['post_type'])
				: '';
			$page = isset($_GET['page'])
				? htmlspecialchars($_GET['page'])
				: '';

			$currentStatus = isset($_GET['opanda_status'])
				? htmlspecialchars($_GET['opanda_status'])
				: 'all';
			if( !in_array($currentStatus, array('all', 'confirmed', 'not-confirmed')) ) {
				$currentStatus = 'all';
			}

			$s = isset($_GET['s'])
				? htmlspecialchars($_GET['s'])
				: '';

			?>
			<form id="searchform" action method="GET">
				<?php if( isset($_GET['post_type']) ) : ?>
					<input type="hidden" name="post_type" value="<?php echo $postType ?>"><?php endif; ?>
				<?php if( isset($_GET['page']) ) : ?>
					<input type="hidden" name="page" value="<?php echo $page ?>"><?php endif; ?>
				<?php if( isset($_GET['opanda_status']) ) : ?>
					<input type="hidden" name="opanda_status" value="<?php echo $currentStatus ?>"><?php endif; ?>

				<p class="search-box">
					<label class="screen-reader-text" for="sa-search-input"><?php echo $text; ?></label>
					<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php echo $s ?>">
					<input type="submit" name="" id="search-submit" class="button" value="<?php echo $text; ?>">
				</p>
			</form>
		<?php
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
			if( current_user_can('administrator') ) {
				echo '<div class="onp-pl-avatar">' . get_avatar($record->user_id, 40) . '</div>';
			} else {
				echo '<div class="onp-pl-avatar">' . get_the_post_thumbnail($record->post_id, array(40, 40)) . '</div>';
			}
		}

		/**
		 * Shows an avatar of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_user_id($record)
		{
			echo $record->user_id;
		}

		/**
		 * Колонка имя пользователя
		 * @param $record
		 */
		public function column_user_name($record)
		{
			$userProfileUrl = get_edit_user_link($record->user_id);
			$paylocker_user = $record->getUser();

			echo sprintf(__('<a href="%s">%s</a>'), $userProfileUrl, $paylocker_user->display_name);
		}

		/**
		 * Колонка заголовок записи
		 * @param $record
		 */
		public function column_post_title($record)
		{
			$output = __('Нет страницы', 'plugin-paylocker');

			$post = get_post($record->post_id);

			if( empty($post) ) {
				echo $output;

				return;
			}

			if( !empty($post) ) {
				$postUrl = get_edit_post_link($post->ID);

				$postTitle = $post->post_title;

				$output = '<a href="' . $postUrl . '"><strong>' . $postTitle . '</strong></a> ';
			}

			echo $output;
		}

		/**
		 * Колонка цена
		 * @param $record
		 */
		public function column_price($record)
		{
			$currency_code = get_option('opanda_pl_currency', 'USD');
			echo $record->table_price . onp_pl_get_currency_symbol($currency_code);
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
		 * Колонка id транзакции
		 * @param $record
		 */
		public function column_transaction_id($record)
		{
			echo $record->ID;
		}

		/**
		 * Колонка id транзакции
		 * @param $record
		 */
		public function column_transaction_status($record)
		{
			$paymentStatusText = __('Завершен', 'plugin-paylocker');

			if( $record->transaction_status == 'waiting' ) {
				$paymentStatusText = __('В ожидании', 'plugin-paylocker');
			} else if( $record->transaction_status == 'cancel' ) {
				$paymentStatusText = __('Отменен', 'plugin-paylocker');
			}

			echo '<span class="onp-pl-payment-status-dot onp-pl-' . $record->transaction_status . '"></span>' . $paymentStatusText;
		}

		/**
		 * Колонка дыата покупки
		 * @param $record
		 */
		public function column_transaction_begin($record)
		{
			echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $record->transaction_begin + (get_option('gmt_offset') * 3600));
		}
	}
/*@mix:place*/