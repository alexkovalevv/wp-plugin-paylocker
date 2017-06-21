<?php

	class OnpPl_PremiumSubsribersTable extends WP_List_Table {

		public function __construct($options = array())
		{

			$options['singular'] = __('Список премиум подписок', 'plugin-paylocker');
			$options['plural'] = __('Список премиум подписок', 'plugin-paylocker');
			$options['ajax'] = false;

			parent::__construct($options);
			//$this->bulk_deactivate();
		}

		public function get_views()
		{
			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');
			$counts = OnpPl_Subcribe::getCountSubscribes();

			$link = 'edit.php?post_type=' . OPANDA_POST_TYPE . '&page=admin_premium_subscribers-paylocker';

			$currentStatus = isset($_GET['opanda_status'])
				? $_GET['opanda_status']
				: 'all';
			if( !in_array($currentStatus, array('all', 'active', 'expired')) ) {
				$currentStatus = 'all';
			}

			$items = array(
				'view-all' => array(
					'title' => __('Все', 'plugin-paylocker'),
					'link' => $link,
					'count' => isset($counts['all'])
						? $counts['all']
						: 0,
					'current' => $currentStatus == 'all'
				),
				'view-active' => array(
					'title' => __('Активных', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'active', $link),
					'count' => isset($counts['active'])
						? $counts['active']
						: 0,
					'current' => $currentStatus == 'active'
				),
				'view-expired' => array(
					'title' => __('Истекших', 'plugin-paylocker'),
					'link' => add_query_arg('opanda_status', 'expired', $link),
					'count' => isset($counts['expired'])
						? $counts['expired']
						: 0,
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

			$user_id = null;

			if( !current_user_can('administrator') ) {
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
			} else if( isset($_GET['sort']) ) {
				if( $_GET['sort'] === 'user_id' && isset($_GET['user_id']) ) {
					$user_id = (int)$_GET['user_id'];
				}
			}

			$segment = 'all';

			if( isset($_GET['opanda_status']) && $_GET['opanda_status'] == 'active' ) {
				$segment = 'active';
			}

			if( isset($_GET['opanda_status']) && $_GET['opanda_status'] == 'expired' ) {
				$segment = 'expired';
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

			require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.subscribe.php');
			$totalitems = OnpPl_Subcribe::getCountSubscribes($user_id, $segment);

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

			$this->items = OnpPl_Subcribe::getSubscribes(array(
				'user_id' => $user_id
			), $segment, array('expired_begin' => 'DESC'), $perpage, $offset);
		}

		public function no_items()
		{
			if( !isset($_GET['s']) ) {
				echo __('Не один из пользователей еще не приобрел премиум подписку. ', 'plugin-paylocker');
			} else {
				echo __('По вашему запросу ничего не найдено.', 'plugin-paylocker');
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
				$columns['user_name'] = __('Имя пользователя', 'plugin-paylocker');
				$columns['prolongations'] = __('Статистика', 'plugin-paylocker');
			}

			$columns['locker_title'] = __('Название подписки', 'plugin-paylocker');
			$columns['expired_begin'] = __('Дата активации', 'plugin-paylocker');
			$columns['expired_end'] = __('Заканчивается через (дней)', 'plugin-paylocker');

			if( current_user_can('administrator') ) {
				if( get_option('opanda_notify_subscribe_expire', false) ) {
					$columns['notify'] = __('Уведомления', 'plugin-paylocker');
				}
			} else {
				$columns['actions'] = __('Действия', 'plugin-paylocker');
			}

			/*if( current_user_can('administrator') ) {

			}*/

			return $columns;
		}

		/**
		 * Generates content for a single row of the table
		 *
		 * @since 3.1.0
		 * @access public
		 *
		 * @param object $item The current item
		 */
		public function single_row($record)
		{
			$subscribeTime = $this->convertTimetoDays($record->expired_end);
			$class = '';

			if( $subscribeTime < 7 && $subscribeTime > 0 ) {
				$class = ' class="onp-pl-soon-expired-row"';
			}

			if( $subscribeTime < 0 || $subscribeTime === 0 ) {
				$class = ' class="onp-pl-already-expired-row"';
			}

			echo '<tr' . $class . '>';
			$this->single_row_columns($record);
			echo '</tr>';
		}


		/**
		 * Shows a checkbox.
		 *
		 * @since 1.0.7
		 * @return void
		 */
		public function column_cb($record)
		{
			echo sprintf('<input type="checkbox" name="onp_pl_premium_subscribers[]" value="%s" />', $record->user_id);
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
			$userProfileUrl = get_edit_user_link($record->user_id);
			$paylocker_user = $record->getUser();

			echo sprintf(__('<a href="%s">%s</a>'), $userProfileUrl, $paylocker_user->display_name);
		}

		public function column_prolongations($record)
		{
			$paylocker_user = $record->getUser();
			$spending = $paylocker_user->getTotalSpendingBySubscribe($record->locker_id);

			$currency_code = get_option('opanda_pl_currency', 'USD');
			$spending = $spending . onp_pl_get_currency_symbol($currency_code);

			echo 'Подписка продлена: ' . $paylocker_user->getProlangationCounts($record->locker_id) . ' <br>Всего потрачено: ' . $spending;
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

			$last_transaction_title = '';

			$paylocker_user = $record->getUser();
			$transaction = $paylocker_user->getLastSubscribe();

			if( !empty($transaction) ) {
				$pricing_table = onp_pl_get_pricing_table($record->locker_id, $transaction->table_name);

				if( !empty($pricing_table) ) {
					$last_transaction_title .= '<b>' . __('Последний использованный тариф') . ':</b><br>';
					$last_transaction_title .= '<i style="color: #999">+' . $pricing_table['expired'] . ' ' . __('дней', 'plugin-paylocker');
					$last_transaction_title .= ' (' . $pricing_table['header'] . ')</i>';
				}
			}

			echo $output . '<br>' . $last_transaction_title;
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
			$subscribeTime = $this->convertTimetoDays($record->expired_end);
			$expiredEndDate = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $record->expired_end + (get_option('gmt_offset') * 3600));

			if( current_user_can('administrator') ) {
				$paylocker_user = $record->getUser();

				if( $paylocker_user->hasCaps($record->locker_id) ) {
					echo '<input type="text" data-user-id="' . $record->user_id . '" data-locker-id="' . $record->locker_id . '" data-default-expired="' . $subscribeTime . '" class="onp-pl-expired-field" value="' . $subscribeTime . '">';
					echo '<a href="#" class="button button-default onp-pl-offset-left-10 onp-pl-x25-button onp-pl-plus-button">+</button>';
					echo '<a href="#" class="button button-default onp-pl-x25-button onp-pl-minus-button">-</button>';
				} else {
					echo '<span class="onp-pl-payment-status-dot onp-pl-cancel" title="' . sprintf(__('подписка истекла %s', 'plugin-paylocker'), $expiredEndDate) . '"></span>' . __('подписка истекла', 'plugin-paylocker');
				}
			} else {
				if( $subscribeTime ) {
					echo sprintf(__('через %d дней', 'plugin-paylocker'), $subscribeTime);
				} else {
					echo '<span class="onp-pl-payment-status-dot onp-pl-cancel" title="' . sprintf(__('подписка истекла %s', 'plugin-paylocker'), $expiredEndDate) . '"></span>' . __('подписка истекла', 'plugin-paylocker');
				}
			}
		}

		public function column_actions($record)
		{
			global $paylocker;
			if( !current_user_can('administrator') ) {
				echo '<a href="' . admin_url('admin.php?page=begin_subscribe-' . $paylocker->pluginName . '&locker_id=' . $record->locker_id) . '" class="button button-default">' . __('Продлить подписку', 'plugin-paylocker') . '</a>';
			} else {
				//echo '<a href="' . admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName) . '&action=edit&locker_id=' . $record->locker_id . '&user_id=' . $record->user_id . '" class="button button-primary">Редактировать</a> ';
				//echo '<a href="' . admin_url('edit.php?post_type=opanda-item&page=admin_premium_subscribers-' . $paylocker->pluginName) . '&action=deactivate&locker_id=' . $record->locker_id . '&user_id=' . $record->user_id . '" class="button button-default">Деактивировать</a>';
			}
		}

		public function column_notify($record)
		{
			global $wpdb;

			if( current_user_can('administrator') ) {
				$result = $wpdb->get_results("
					SELECT notifications, last_notification_time
					FROM {$wpdb->prefix}opanda_pl_notifications
					WHERE user_id = '{$record->user_id}' and locker_id='{$record->locker_id} LIMIT 1'
				");

				if( empty($result) ) {
					return __('Не требуется.', 'plugin-paylocker');
				}

				$notificationsCount = empty($result[0]->notifications)
					? 0
					: $result[0]->notifications;

				$possibleCount = (int)trim(get_option('opanda_subscribe_expire_count'));
				$notifyInterval = (int)trim(get_option('opanda_subscribe_expire_interval'));

				if( empty($possibleCount) || empty($notifyInterval) ) {
					return __('уведомление не может быть отправлено, настройте интервал и количество уведомлений', 'plugin-paylocker');
				}

				if( $possibleCount <= $notificationsCount ) {
					$nextNotification = __('завершено', 'plugin-paylocker');
				} else {
					$timeLeft = $result[0]->last_notification_time - strtotime("-{$notifyInterval} days");

					if( $timeLeft < 0 ) {
						$nextNotification = __('процесс отправки', 'plugin-paylocker');
					} else {

						$nextNotification = sprintf(__('следующее через %s', 'plugin-paylocker'), gmdate("H:i:s", $timeLeft));
					}
				}

				return $notificationsCount . ' <strong>(' . $nextNotification . ')</strong>';
			}

			return false;
		}

		public function convertTimetoDays($expired)
		{
			return ($expired - time()) > 0
				? round(($expired - time()) / 86400)
				: 0;
		}
	}
/*@mix:place*/