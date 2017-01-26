<?php

	class OnpPl_PremiumSubsribersTable extends WP_List_Table {

		public function __construct($options = array())
		{

			$options['singular'] = __('Список премиум подписок', 'bizpanda');
			$options['plural'] = __('Список премиум подписок', 'bizpanda');
			$options['ajax'] = false;

			parent::__construct($options);
			//$this->bulk_deactivate();
		}

		public function get_views()
		{
			$counts = $this->getCountSegments();

			$link = 'edit.php?post_type=' . OPANDA_POST_TYPE . '&page=admin_premium_subscribers-paylocker';

			$currentStatus = isset($_GET['opanda_status'])
				? $_GET['opanda_status']
				: 'all';
			if( !in_array($currentStatus, array('all', 'active', 'expired')) ) {
				$currentStatus = 'all';
			}

			$items = array(
				'view-all' => array(
					'title' => __('Все', 'bizpanda'),
					'link' => $link,
					'count' => $counts['all'],
					'current' => $currentStatus == 'all'
				),
				'view-active' => array(
					'title' => __('Активных', 'bizpanda'),
					'link' => add_query_arg('opanda_status', 'active', $link),
					'count' => $counts['active'],
					'current' => $currentStatus == 'active'
				),
				'view-expired' => array(
					'title' => __('Истекших', 'bizpanda'),
					'link' => add_query_arg('opanda_status', 'expired', $link),
					'count' => $counts['expired'],
					'current' => $currentStatus == 'expired'
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

		public function getCountSegments()
		{
			global $wpdb;

			$counts = array();

			$result = $wpdb->get_results("
			  SELECT COUNT(*) AS count,
			  IF(expired_end > UNIX_TIMESTAMP(), 'active', 'expired')
			  AS segment
			  FROM wp_opanda_pl_subsribers GROUP BY segment", ARRAY_A);

			foreach($result as $items) {
				if( $items['segment'] == 'active' ) {
					$counts['active'] = (int)$items['count'];
				} else {
					$counts['expired'] = (int)$items['count'];
				}
			}

			$counts['all'] = array_sum($counts);

			return $counts;
		}

		/*public function get_bulk_actions()
		{
			$actions = array(
				'deactivate' => __('Сбросить премиум')
			);

			return $actions;
		}*/

		/**
		 * Checks and runs the bulk action 'delete'.
		 */
		public function bulk_deactivate()
		{

			$action = $this->current_action();
			if( 'deactivate' !== $action ) {
				return;
			}
			if( empty($_POST['onp_pl_premium_subscribers']) ) {
				return;
			}

			/*$ids = array();
			foreach( $_POST['onp_pl_premium_subscribers'] as $subscriberId ) {
				$ids[] = intval( $leadId );
			}

			global $wpdb;
			$wpdb->query("DELETE FROM {$wpdb->prefix}opanda_leads WHERE ID IN (" . implode(',', $ids) . ")");*/
			//$wpdb->query("DELETE FROM {$wpdb->prefix}opanda_leads_fields WHERE lead_id IN (" . implode(',', $ids) . ")");*/
		}

		function prepare_items()
		{
			global $wpdb;

			$query = "SELECT * FROM {$wpdb->prefix}opanda_pl_subsribers";

			// where

			$where = array();

			if( !current_user_can('administrator') ) {
				$current_user = wp_get_current_user();
				$where[] = "user_id='" . $current_user->ID . "'";
			}

			if( isset($_GET['opanda_status']) && $_GET['opanda_status'] == 'active' ) {
				$where[] = 'expired_end > ' . time();
			}

			if( isset($_GET['opanda_status']) && $_GET['opanda_status'] == 'expired' ) {
				$where[] = 'expired_end < ' . time();
			}

			if( isset($_GET['s']) ) {

				$search = trim(addcslashes(esc_sql($_GET['s']), '%_'));

				$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}users WHERE user_login LIKE '%" . $search . "%'", ARRAY_A);

				$usersIdStr = '';
				foreach($results as $result) {
					$usersIdStr .= "'" . $result['ID'] . "',";
				}
				$usersIdStr = rtrim($usersIdStr, ',');
				$where[] = "user_id in (" . $usersIdStr . ")";
			}

			if( !empty($where) ) {
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$query .= ' ORDER BY expired_begin DESC';

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

		public function no_items()
		{
			if( !isset($_GET['s']) ) {
				echo __('Не один из пользователей еще не приобрел премиум подписку. ', 'bizpanda');
			} else {
				echo __('По вашему запросу ничего не найдено.', 'bizpanda');
			}
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
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns()
		{

			if( current_user_can('administrator') ) {
				//$columns['cb'] = '<input type="checkbox" />';
				$columns['avatar'] = '';
				$columns['user_name'] = __('Имя пользователя', 'bizpanda');
			}

			$columns['locker_title'] = __('Название подписки', 'bizpanda');
			$columns['expired_begin'] = __('Дата активации', 'bizpanda');
			$columns['expired_end'] = __('Заканчивается', 'bizpanda');

			//if( !current_user_can('administrator') ) {
			$columns['actions'] = '';

			//}

			return $columns;
		}

		/**
		 * Shows a checkbox.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_cb($record)
		{
			echo sprintf('<input type="checkbox" name="onp_pl_premium_subscribers[]" value="%s" />', $record->ID);
		}

		/**
		 * Shows an avatar of the lead.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_avatar($record)
		{
			echo '<div class="onp-pl-avatar">' . get_avatar($record->user_id, 40) . '</div>';
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
		 * Колонка заголовок замка
		 * @param $record
		 */
		public function column_locker_title($record)
		{
			$post = get_post($record->locker_id);

			$output = '';

			if( !empty($post) ) {
				$postUrl = get_edit_post_link($post->ID);
				$postTitle = $post->post_title;

				$output = '<a href="' . $postUrl . '"><strong>' . $postTitle . '</strong></a>';
			}

			echo $output;
		}

		/**
		 * Дата оформления
		 * @param $record
		 */
		public function column_expired_begin($record)
		{
			echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $record->expired_begin + (get_option('gmt_offset') * 3600));
		}

		/**
		 * Дата завершения
		 * @param $record
		 */
		public function column_expired_end($record)
		{
			$subscribeTime = round(($record->expired_end - time()) / 86400);

			if( $subscribeTime < 0 ) {
				$subscribeTime = 0;
			}

			if( current_user_can('administrator') ) {
				if( $subscribeTime ) {
					echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $record->expired_end + (get_option('gmt_offset') * 3600));
				} else {
					echo '<span style="color:red;">' . __('подписка истекла', 'bizpanda') . '</span>';
				}
			} else {

				if( $subscribeTime ) {
					echo sprintf(__('через %d дней', 'bizpanda'), $subscribeTime);
				} else {
					echo '<span style="color:red;">' . __('подписка истекла', 'bizpanda');
				}
			}
		}

		public function column_actions($record)
		{
			global $paylocker;
			if( !current_user_can('administrator') ) {
				echo '<a href="' . admin_url('admin.php?page=begin_subscribe-paylocker&locker_id=' . $record->locker_id) . '" class="button button-default">Продлить подписку</a>';
			} else {
				echo '<a href="' . admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName) . '&action=edit&locker_id=' . $record->locker_id . '&user_id=' . $record->user_id . '" class="button button-primary">Редактировать</a> ';
				echo '<a href="' . admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName) . '&action=deactivate&locker_id=' . $record->locker_id . '&user_id=' . $record->user_id . '" class="button button-default">Деактивировать</a>';
			}
		}
	}
/*@mix:place*/