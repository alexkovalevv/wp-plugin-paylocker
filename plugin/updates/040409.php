<?php

	/**
	 * Обновление деактивирует кнопку facebook подписаться, вместо нее активирует кнопку facebook мне нравится.
	 * Все данные из кнопки подписаться экспортируются в кнопку нравится. В версии 4.4.9 решено было удалить кнопку подписаться.
	 *
	 * @since 4.4.9
	 */
	class SocialLockerUpdate040409 extends Factory000_Update {

		public function install()
		{

			// Обновляем версию api facebook
			update_option('opanda_facebook_version', 'v2.8');

			// Удаляем кнопку подписаться и экспортируем данные в кнопку нравится
			$lockers = get_posts(array(
				'post_type' => OPANDA_POST_TYPE,
				'numberposts' => -1
			));

			foreach($lockers as $locker) {
				$itemType = get_post_meta($locker->ID, 'opanda_item', true);
				if( 'email-locker' === $itemType || 'signin-locker' === $itemType ) {
					continue;
				}

				$orders_buttons = get_post_meta($locker->ID, 'opanda_buttons_order', true);
				$fb_like_btn_url = get_post_meta($locker->ID, 'opanda_facebook_like_url', true);
				$fb_subscription_btn_url = get_post_meta($locker->ID, 'opanda_facebook_subscribe_url', true);
				$split_button = explode(',', $orders_buttons);

				if( in_array('facebook-subscribe', $split_button) && !in_array('facebook-like', $split_button) ) {
					$split_button[] = 'facebook-like';
					$fb_like_btn_url = $fb_subscription_btn_url;
					$fb_subscribe_key = array_search('facebook-subscribe', $split_button);
					unset($split_button[$fb_subscribe_key]);

					update_post_meta($locker->ID, 'opanda_facebook-like_available', 1);
					update_post_meta($locker->ID, 'opanda_facebook-like_available_is_active', 1);
					update_post_meta($locker->ID, 'opanda_facebook_like_url', $fb_like_btn_url);
				} else if( in_array('facebook-subscribe', $split_button) && in_array('facebook-like', $split_button) ) {
					$fb_subscribe_key = array_search('facebook-subscribe', $split_button);
					unset($split_button[$fb_subscribe_key]);
				}

				$save_order_format = implode(',', $split_button);

				update_post_meta($locker->ID, 'opanda_buttons_order', $save_order_format);
				update_post_meta($locker->ID, 'opanda_facebook-subscribe_available', 0);
				update_post_meta($locker->ID, 'opanda_facebook-subscribe_available_is_active', 0);
			}
		}
	}