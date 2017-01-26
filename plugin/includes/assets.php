<?php
	/**
	 * Подключение внешних файлов и скриптов
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 06.01.2017
	 * @version 1.0
	 */

	function onp_pl_assets_script_visibility_vars()
	{
		global $post;

		wp_enqueue_script('paylocker-core', PAYLOCKER_URL . '/plugin/assets/js/paylocker.010001' . '.js', array('opanda-lockers'), false, true);

		if( !is_user_logged_in() ) {
			wp_enqueue_script('paylocker-helpers', PAYLOCKER_URL . '/plugin/assets/js/paylocker-helpers.010000.js', array(
				'opanda-lockers',
				'paylocker-core'
			), false, true);
		}

		wp_localize_script('paylocker-core', '__paylocker', array(
			'loginUrl' => site_url('wp-login.php'),
			'adminEmail' => get_option('admin_email')
		));

		wp_enqueue_style('paylocker-style', PAYLOCKER_URL . '/plugin/assets/css/paylocker.1.0.0.min.css');

		$rand = time();
		wp_enqueue_script('onp-pl-visibility-vars', site_url() . '?onp_pl_visibility_vars=' . $rand . '&post_id=' . $post->ID, array('opanda-lockers'), false, true);
	}

	add_action('wp_enqueue_scripts', 'onp_pl_assets_script_visibility_vars');

	function onp_pl_print_scripts_visibility_vars()
	{
		if( isset($_GET['onp_pl_visibility_vars']) ) {

			// Говорим, что этот javascript файл
			$expires_offset = 31536000; // 1 year
			header('Content-Type: application/x-javascript; charset=UTF-8');

			header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $expires_offset) . ' GMT');
			header("Cache-Control: public, max-age=$expires_offset");

			echo '!function(a){';

			$outPut = '';

			$args = array(
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'post_type' => 'opanda-item'
			);

			$lockers = get_posts($args);

			foreach($lockers as $locker) {
				$itemType = get_post_meta($locker->ID, 'opanda_item', true);

				if( $itemType == 'pay-locker' ) {
					$isUserPremium = false;

					if( is_user_logged_in() ) {
						$current_user = wp_get_current_user();

						require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.premium-subscriber.php');
						$premium = new OnpPl_PremiumSubscriber();

						$isUserPremium = $premium->hasUserPremium($locker->ID);

						if( !$isUserPremium && isset($_GET['post_id']) && !empty($_GET['post_id']) ) {
							require_once(PAYLOCKER_DIR . '/plugin/includes/classes/class.purchase-posts.php');

							$postId = intval($_GET['post_id']);

							if( empty($postId) ) {
								$outPut .= 'console && console.log("%c[Error]: Не передан обязательный параметр post_id", "color:red;");';
							} else {
								$isUserPremium = OnpPl_PurchasePosts::isPaidPost($current_user->ID, $postId);
							}
						}
					}

					$paidMode = $isUserPremium
						? 'premium'
						: 'free';

					$outPut .= 'a.pandalocker.services.visibilityProviders["user-paid-mode-l' . $locker->ID . '"]={getValue:function(){return"' . $paidMode . '"}};';
				}
			}

			$outPut = apply_filters('onp_paylocker_output_visibility_vars', $outPut);

			echo $outPut;

			echo '}(jQuery);';
			exit;
		}
	}

	add_action('template_redirect', 'onp_pl_print_scripts_visibility_vars');
	