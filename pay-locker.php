<?php
	/**
	 * Plugin Name: {comp:paylocker}
	 * Plugin URI: {comp:pluginUrl}
	 * Description: {comp:description}
	 * Author: Alex Kovalevv <alex.kovalevv@gmail.com>
	 * Version: 1.0.2
	 * Author URI: http://byonepress.com
	 */

	// ---
	// Constatns & Resources
	//

	if( defined('PAYLOCKER_PLUGIN_ACTIVE') ) {
		return;
	}
	define('PAYLOCKER_PLUGIN_ACTIVE', true);

	#comp remove
	// the following constants are used to debug features of diffrent builds
	// on developer machines before compiling the plugin

	// build: free, premium, ultimate
	if( !defined('BUILD_TYPE') ) {
		define('BUILD_TYPE', 'premium');
	}
	// language: en_US, ru_RU
	if( !defined('LANG_TYPE') ) {
		define('LANG_TYPE', 'ru_RU');
	}
	// license: free, paid
	if( !defined('LICENSE_TYPE') ) {
		define('LICENSE_TYPE', 'paid');
	}

	// wordpress language
	if( !defined('WPLANG') ) {
		define('WPLANG', LANG_TYPE);
	}

	if( !defined('ONP_DEBUG_NETWORK_DISABLED') ) {

		define('ONP_DEBUG_NETWORK_DISABLED', false);
		define('ONP_DEBUG_CHECK_UPDATES', false);
	}

	if( !defined('ONP_DEBUG_TRIAL_EXPIRES') ) {

		define('ONP_DEBUG_TRIAL_EXPIRES', false);
		define('ONP_DEBUG_SHOW_BINDING_MESSAGE', false);
		define('ONP_DEBUG_SHOW_STYLEROLLER_MESSAGE', false);
		define('ONP_DEBUG_SL_OFFER_PREMIUM', false);

		// review, subscribe, premium
		define('ONP_SL_ACHIEVEMENT_ACTION', false);
		define('ONP_SL_ACHIEVEMENT_VALUE', false);

		// skip trial
		define('ONP_SL_DEBUG_SKIP_TRIAL', false);
	}
	#endcomp

	define('PAYLOCKER_DIR', dirname(__FILE__));
	define('PAYLOCKER_URL', plugins_url(null, __FILE__));

	#comp remove
	// the compiler library provides a set of functions like onp_build and onp_license
	// to check how the plugin work for diffrent builds on developer machines

	require(PAYLOCKER_DIR . '/bizpanda/libs/onepress/compiler/boot.php');
	#endcomp

	// ---
	// BizPanda Framework
	//

	// inits bizpanda and its items
	require(PAYLOCKER_DIR . '/bizpanda/connect.php');
	define('PAYLOCKER_BIZPANDA_VERSION', 123);

	/**
	 * Fires when the BizPanda connected.
	 */
	function onp_pl_init_bizpanda($activationHook = false)
	{
		/**
		 * Displays a note about that it's requited to update other plugins.
		 */
		if( !$activationHook && !bizpanda_validate(PAYLOCKER_BIZPANDA_VERSION, 'Paylocker') ) {
			return;
		}

		if( onp_lang('en_US') ) {
			load_textdomain('plugin-paylocker', PAYLOCKER_DIR . '/langs/paylocker-' . LANG_TYPE . '.mo');
		}

		// enabling features the plugin requires

		BizPanda::enableFeature('lockers');
		BizPanda::enableFeature('payment');

		// creating the plugin object
		global $paylocker;

		$paylocker = new Factory000_Plugin(__FILE__, array(
			'name' => 'paylocker',
			'title' => __('Платный контент', 'plugin-paylocker'),
			'version' => '1.0.2',
			'assembly' => BUILD_TYPE,
			'lang' => LANG_TYPE,
			'api' => 'http://api.byonepress.com/1.1/',
			'premium' => 'http://api.byonepress.com/public/1.0/get/?product=sociallocker-next',
			'styleroller' => 'http://sociallocker.org/styleroller',
			'account' => 'http://accounts.byonepress.com/',
			'updates' => PAYLOCKER_DIR . '/plugin/updates/',
			'tracker' => /*@var:tracker*/
				'0900124461779baebd4e030b813535ac'/*@*/,
			'childPlugins' => array('bizpanda')
		));

		BizPanda::registerPlugin($paylocker, 'paylocker', 'premium');

		// requires factory modules
		$paylocker->load(array(
			array('bizpanda/libs/factory/bootstrap', 'factory_bootstrap_000', 'admin'),
			array('bizpanda/libs/factory/notices', 'factory_notices_000', 'admin'),
			array('bizpanda/libs/onepress/api', 'onp_api_000'),
			//array('bizpanda/libs/onepress/licensing', 'onp_licensing_000'),
			array('bizpanda/libs/onepress/updates', 'onp_updates_000')
		));

		require(PAYLOCKER_DIR . '/plugin/boot.php');
	}

	add_action('bizpanda_init', 'onp_pl_init_bizpanda');

	/**
	 * Activates the plugin.
	 *
	 * TThe activation hook has to be registered before loading the plugin.
	 * The deactivateion hook can be registered in any place (currently in the file plugin.class.php).
	 */
	function onp_pl_activation()
	{
		// if the old version of the bizpanda which doesn't contain the function bizpanda_connect has been loaded,
		// ignores activation, the message suggesting to upgrade the plugin will be appear instead
		if( !function_exists('bizpanda_connect') ) {
			return;
		}

		// if the bizpanda has been already connected, inits the plugin manually
		if( defined('OPANDA_ACTIVE') ) {
			onp_pl_init_bizpanda(true);
		} else bizpanda_connect();

		global $paylocker;
		$paylocker->activate();
	}

	register_activation_hook(__FILE__, 'onp_pl_activation');

	/**
	 * Displays a note about that it's requited to update other plugins.
	 */
	if( is_admin() && defined('OPANDA_ACTIVE') ) {
		bizpanda_validate(PAYLOCKER_BIZPANDA_VERSION, 'Paylocker');
	}

	// todo: удалить дополнение для публичной версии
	require(PAYLOCKER_DIR . '/addons/wp-lockers-interrelation/lockers-interrelation.php');
