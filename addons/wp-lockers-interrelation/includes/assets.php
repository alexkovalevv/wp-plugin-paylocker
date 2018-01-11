<?php


	/**
	 * Класс управляет подключением скриптов замков
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 08.01.2017
	 * @version 1.0
	 */
	class OnpBp_AddonAssetsManager {

		public function __construct()
		{
			// Очищаем старые фильтры для страниц, чтобы определить свои
			if( !is_admin() ) {
				add_action('the_post', array($this, 'clearPostFilters'));
				add_filter('the_content', array($this, 'addLockerShortcodes'), 1);
			}

			add_filter('bizpanda_social-locker_item_options', array($this, 'addConditionsToSocialLocker'), 10, 2);
			add_filter('onp_paylocker_output_visibility_vars', array($this, 'addVisabilityVars'), 10, 1);
		}

		public function clearPostFilters()
		{
			remove_filter('the_content', 'OPanda_AssetsManager::addSocialLockerShortcodes', 1);
		}

		public function addVisabilityVars($output)
		{
			$paidMode = 'free';

			if( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				$roles = $current_user->roles;

				$paidMode = in_array('pl_premium_subscriber', $roles)
					? 'premium'
					: 'free';
			}

			$output .= 'a.pandalocker.services.visibilityProviders["user-paid-mode"]={getValue:function(){return"' . $paidMode . '"}};';

			return $output;
		}


		public function addConditionsToSocialLocker($options, $lockerId)
		{
			$options['locker']['visibility'] = array(

				array(
					'conditions' => array(
						array(
							'type' => 'scope',
							'conditions' => array(
								array(
									'param' => 'user-paid-mode',
									'operator' => 'equals',
									'type' => 'select',
									'value' => 'premium'
								)
							)
						)
					),
					'type' => 'hideif'
				)
			);

			return $options;
		}

		/**
		 * Вставляет шорткоды замков в записи
		 * @param $content
		 * @return string
		 */
		public function addLockerShortcodes($content)
		{
			// todo: баг блокировкой контента в превью статьи
			if( !is_singular() ) {
				return $content;
			}

			require_once(OPANDA_BIZPANDA_DIR . '/includes/assets.php');

			$bulkLockers = get_option('onp_sl_bulk_lockers', array());

			if( empty($bulkLockers) ) {
				return $content;
			}

			global $bizpanda;

			$shortcodeEnds = array();
			$bulkIndex = 0;

			$ignoredShortcodes = array();

			foreach($bulkLockers as $id => $options) {

				if( !in_array($options['way'], array('skip-lock', 'more-tag')) ) {
					continue;
				}

				if( !OPanda_AssetsManager::isPageSelected($id, $options) ) {
					continue;
				}

				if( OPanda_AssetsManager::isPageExcluded($id, $options) ) {
					continue;
				}

				$isPremiumBulkLock = get_option('onp_bp_addon_available_premium_bulk_lock', false);

				if( $isPremiumBulkLock ) {
					if( $this->isBulkSandbox(get_the_ID()) ) {
						$this->moveOutBulkSandBox(get_the_ID());
					}

					$premiumBulkRole = $this->getPremiumBulkRole(get_the_ID());

					if( empty($premiumBulkRole) ) {
						$premiumBulkRole = $this->setPremiumBulkRole(get_the_ID());
					}

					if( $premiumBulkRole != $id ) {
						continue;
					}
				}

				$lockerStatus = get_post_status($id);
				if( 'publish' !== $lockerStatus ) {
					continue;
				}

				$itemType = get_post_meta($id, 'opanda_item', true);

				if( 'pay-locker' == $itemType && !BizPanda::hasPlugin('paylocker') ) {
					continue;
				}

				if( 'social-locker' == $itemType && !BizPanda::hasPlugin('sociallocker') ) {
					continue;
				}

				if( 'email-locker' == $itemType && !BizPanda::hasPlugin('optinpanda') ) {
					continue;
				}

				$bulkIndex++;

				switch( $itemType ) {
					case 'pay-locker':
						$shortcodeName = 'paylocker-bulk-' . $bulkIndex;
						break;
					case 'email-locker':
						$shortcodeName = 'emaillocker-bulk-' . $bulkIndex;
						break;
					case 'signin-locker':
						$shortcodeName = 'signinlocker-bulk-' . $bulkIndex;
						break;
					default:
						$shortcodeName = 'sociallocker-bulk-' . $bulkIndex;
						break;
				}

				$shortcode = new OPanda_LockerShortcode($bizpanda);
				add_shortcode($shortcodeName, array($shortcode, 'render'));

				if( $options['way'] == 'skip-lock' ) {
					if( $options['skip_number'] == 0 ) {
						;
						$content = "[$shortcodeName id='$id']" . $content;

						if( !isset($shortcodeEnds[0]) ) {
							$shortcodeEnds[0] = array();
						}
						$shortcodeEnds[0][] = "[/$shortcodeName]";
					} else {
						$counter = 0;
						$offset = 0;

						while( preg_match('/[^\s]+((<\/p>)|(\n\r){2,}|(\r\n){2,}|(\n){2,}|(\r){2,})/i', $content, $matches, PREG_OFFSET_CAPTURE, $offset) ) {
							$counter++;
							$offset = $matches[0][1] + strlen($matches[0][0]);

							if( $counter == $options['skip_number'] ) {

								$beforeShortcode = substr($content, 0, $offset);
								$insideShortcode = substr($content, $offset);

								$content = OPanda_AssetsManager::normilizerMarkup($beforeShortcode, $insideShortcode, "[$shortcodeName id='$id']", "");

								if( !isset($shortcodeEnds[$offset]) ) {
									$shortcodeEnds[$offset] = array();
								}
								$shortcodeEnds[$offset][] = "[/$shortcodeName]";

								break;
							}
						}
					}
				} elseif( $options['way'] == 'more-tag' && is_singular($options['post_types']) ) {
					global $post;

					$label = '<span id="more-' . $post->ID . '"></span>';
					$pos = strpos($content, $label);
					if( $pos === false ) {
						return $content;
					}

					$offset = $pos + strlen($label);
					if( substr($content, $offset, 4) == '</p>' ) {
						$offset += 4;
					}

					$content = substr($content, 0, $offset) . "[$shortcodeName id='$id']" . substr($content, $offset);

					if( !isset($shortcodeEnds[$offset]) ) {
						$shortcodeEnds[$offset] = array();
					}
					$shortcodeEnds[$offset][] = "[/$shortcodeName]";
				}
			}

			if( !empty($shortcodeEnds) ) {

				krsort($shortcodeEnds);

				foreach($shortcodeEnds as $shortcodeEndItem) {
					foreach($shortcodeEndItem as $shortcodeEnd) {
						$content .= $shortcodeEnd;
					}
				}
			}

			return $content;
		}

		public function getPremiumBulkRole($postId)
		{
			if( empty($postId) ) {
				return false;
			}

			return get_post_meta($postId, 'onp_bp_addon_bulk_role_locker', true);
		}


		public function isBulkSandbox($postId)
		{
			$isBulkSandBox = get_post_meta($postId, 'onp_bp_addon_bulk_sandbox', true);

			return !empty($isBulkSandBox);
		}

		public function moveOutBulkSandBox($postId)
		{
			$bulkSandBoxExpired = get_post_meta($postId, 'onp_bp_addon_bulk_sandbox', true);

			$currentTime = time();
			if( $bulkSandBoxExpired < $currentTime ) {
				delete_post_meta($postId, 'onp_bp_addon_bulk_sandbox');

				$lockerId = $this->getLockersFrequencyResult();

				if( empty($lockerId) ) {
					return false;
				}

				update_post_meta($postId, 'onp_bp_addon_bulk_role_locker', $lockerId);
			}
		}

		/**
		 * Присваивает отвественный замок для выбранной записи
		 * @param $postId
		 * @return bool|mixed|void
		 */
		public function setPremiumBulkRole($postId)
		{
			if( empty($postId) ) {
				return false;
			}

			$post = get_post($postId);

			if( empty($post) ) {
				return false;
			}

			$lockerId = $this->getLockersFrequencyResult();

			if( empty($lockerId) ) {
				return false;
			}

			$postPublishTime = (strtotime($post->post_date) + 604800);
			$isNewPost = $postPublishTime > time();

			$sandBoxtime = array('604800', '1209600', '2678400');
			$rnd = array_rand($sandBoxtime);

			if( $isNewPost ) {
				update_post_meta($postId, 'onp_bp_addon_bulk_sandbox', $sandBoxtime[$rnd] + time());
				$lockerId = get_option('onp_bp_addon_paylocker');
			}

			if( empty($lockerId) ) {
				return false;
			}

			update_post_meta($postId, 'onp_bp_addon_bulk_role_locker', $lockerId);

			return $lockerId;
		}

		/**
		 * Возвращает случайно выбранный id замка по установленному коэфиценту.
		 * @return bool|mixed|void
		 */
		public function getLockersFrequencyResult()
		{
			$sociallockerId = get_option('onp_bp_addon_sociallocker');
			$slFrequency = get_option('onp_bp_addon_sl_frequency');
			$paylockerId = get_option('onp_bp_addon_paylocker');

			if( empty($sociallockerId) || empty($paylockerId) || empty($slFrequency) ) {
				return false;
			}

			$rnd = rand(1, 100);

			if( $slFrequency > $rnd ) {
				$lockerId = $sociallockerId;
			} else {
				$lockerId = $paylockerId;
			}

			return $lockerId;
		}
	}