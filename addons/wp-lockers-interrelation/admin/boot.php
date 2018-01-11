<?php
	/**
	 * Общие сценарии для админ панели
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 08.01.2017
	 * @version 1.0
	 */
	require_once(OPANDA_SLA_PLUGIN_DIR . '/admin/pages/bulk-settings.php');

	/**
	 * Сортируем пользователей по дате регистрации
	 * @param $query
	 */
	function onp_bizpanda_addon_order_users_by_date_registered($query)
	{
		global $pagenow;
		if( !is_admin() || 'users.php' !== $pagenow ) {
			return;
		}
		$query->query_orderby = 'ORDER BY user_registered DESC';
	}

	add_action('pre_user_query', 'onp_bizpanda_addon_order_users_by_date_registered');

	/**
	 * Изменяем позицую для пунктов меню в админ панели
	 *
	 * @see menu_order
	 * @since 1.0.0
	 */
	function onp_bizpanda_addon_reposition_menu($menu)
	{
		global $submenu;

		$detachMenuItems = array();

		if( isset($submenu['edit.php?post_type=' . OPANDA_POST_TYPE]) ) {
			$bizpandaMenu = $submenu['edit.php?post_type=' . OPANDA_POST_TYPE];
			foreach($bizpandaMenu as $menuKey => $menuValue) {
				if( $menuValue[2] === 'addon_premium_bulk_lock-bizpanda' ) {
					$detachMenuItems[] = $bizpandaMenu[$menuKey];
					unset($bizpandaMenu[$menuKey]);
				}
			}

			$submenu['edit.php?post_type=' . OPANDA_POST_TYPE] = array_slice($bizpandaMenu, 0, 2, true) + $detachMenuItems + array_slice($bizpandaMenu, 2, count($bizpandaMenu) - 2, true);
		}

		return $menu;
	}

	//add_filter('custom_menu_order', '__return_true');
	//add_filter('menu_order', 'onp_bizpanda_addon_reposition_menu');

	/**
	 * Добавляем колонку с информацией о блокировке статьи
	 * в таблицу записей. Состояние записи может быть (карантин, платная, бесплатная)
	 *
	 * @access public
	 * @param Array $columns The existing columns
	 * @return Array $filtered_columns The filtered columns
	 */
	function onp_bp_addon_event_modify_columns($columns)
	{
		$new_columns = array(
			'onp_bp_addon_post_premium_state' => 'Статус записи'
		);

		$filtered_columns = array_merge($columns, $new_columns);

		return $filtered_columns;
	}

	add_filter('manage_posts_columns', 'onp_bp_addon_event_modify_columns');
	//add_filter('manage_pages_columns', 'onp_bp_addon_event_modify_columns');

	function onp_bp_addon_revealid_id_column_content($column, $porstId)
	{
		if( 'onp_bp_addon_post_premium_state' == $column ) {
			$sandbox = get_post_meta($porstId, 'onp_bp_addon_bulk_sandbox', true);
			if( empty($sandbox) ) {
				$bulkRoleLocker = get_post_meta($porstId, 'onp_bp_addon_bulk_role_locker', true);
				if( !empty($bulkRoleLocker) ) {
					$lockerType = get_post_meta($bulkRoleLocker, 'opanda_item', true);
					if( $lockerType == 'pay-locker' ) {
						echo 'Платная запись';
					} else {
						echo 'Бесплатная запись';
					}
				} else {
					echo 'Не проиндексирована';
				}
			} else {
				echo 'Карантин (' . round(($sandbox - time()) / 86400) . ' дней)';
			}
		}
	}

	add_action('manage_posts_custom_column', 'onp_bp_addon_revealid_id_column_content', 10, 2);
	//add_action('manage_pages_custom_column', 'onp_bp_addon_revealid_id_column_content', 10, 2);
