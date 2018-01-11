<?php
	/**
	 * Plugin Name: Блокировщик для платного контента
	 * Plugin URI: https://sociallocker.org
	 * Description: Комбинирует работу социального замка и замка для платного контента.
	 * Author: Alex Kovalev <alex.kovalevv@gmail.com>
	 * Version: 1.0.1
	 * Author URI: http://byoneress.com/
	 */

	define('OPANDA_SLA_PLUGIN_URL', plugins_url(null, __FILE__));
	define('OPANDA_SLA_PLUGIN_DIR', dirname(__FILE__));

	function onp_bizpanda_addon_init()
	{
		if( defined('PAYLOCKER_PLUGIN_ACTIVE') || defined('OPTINPANDA_PLUGIN_ACTIVE') || defined('SOCIALLOCKER_PLUGIN_ACTIVE') ) {
			if( is_admin() ) {
				require_once(OPANDA_SLA_PLUGIN_DIR . '/admin/boot.php');
			}
			require_once(OPANDA_SLA_PLUGIN_DIR . '/includes/assets.php');

			$assetsManager = new OnpBp_AddonAssetsManager();
		}
	}

	add_action('init', 'onp_bizpanda_addon_init');



