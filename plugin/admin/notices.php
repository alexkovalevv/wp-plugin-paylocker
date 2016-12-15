<?php
	if( onp_build('free') ) {
		// ------------------------------------------------------------------------------------------
		// Upgrade To Premium
		// ------------------------------------------------------------------------------------------
		/**
		 * Shows the trial and premium notices.
		 *
		 * @see   factory_notices
		 * @since 1.0.0
		 */
		function onp_sl_upgrade_to_premium_notices($notices)
		{
			global $sociallocker;
			$forceToShowNotices = defined('ONP_DEBUG_SL_OFFER_PREMIUM') && ONP_DEBUG_SL_OFFER_PREMIUM;
			if( (!$sociallocker->license || $sociallocker->license->build !== 'free' || $sociallocker->build !== "free") && !$forceToShowNotices ) {
				return $notices;
			}
			$alreadyActivated = get_option('onp_trial_activated_' . $sociallocker->pluginName, false);
			if( $alreadyActivated ) {
				$message = __('5 extra stunning themes, 8 social buttons, the blurring effect, advanced options, new features & updates every week, dedicated support and more.', 'bizpanda');
				$header = __('Drive more traffic and build quality followers with Social Locker Premium!', 'bizpanda');
				$url = onp_licensing_000_get_purchase_url($sociallocker);
			} else {
				$message = __('5 extra stunning themes, 8 social buttons, the blurring effect, advanced options, new features & updates every week, dedicated support and more. Drive more traffic and build quality followers with Social Locker Premium!', 'bizpanda');
				$header = __('Try the premium version for 7 days for free!', 'bizpanda');
				$url = onp_licensing_000_manager_link($sociallocker->pluginName, 'activateTrial', false);
			}
			$closed = get_option('factory_notices_closed', array());
			$lastCloase = isset($closed['onp-sl-offer-to-purchase'])
				? $closed['onp-sl-offer-to-purchase']['time']
				: 0;
			// shows every 10 days
			if( (time() - $lastCloase > 60 * 60 * 10) || $forceToShowNotices ) {
				if( onp_lang('en_US') ) {
					if( !$alreadyActivated ) {
						$notices[] = array(
							'id' => 'onp-sl-offer-to-purchase',
							'class' => 'call-to-action ',
							'icon' => 'fa fa-arrow-circle-o-up',
							'header' => '<span class="onp-hightlight">' . $header . '</span>',
							'message' => $message,
							'plugin' => $sociallocker->pluginName,
							'where' => array(
								'plugins',
								'dashboard',
								'edit'
							),
							// buttons and links
							'buttons' => array(
								array(
									'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Activate Premium', 'bizpanda'),
									'class' => 'button button-primary',
									'action' => $url
								),
								array(
									'title' => __('No, thanks, not now', 'bizpanda'),
									'class' => 'button',
									'action' => 'x'
								)
							)
						);
					} else {
						$notices[] = array(
							'id' => 'onp-sl-offer-to-purchase',
							'class' => 'call-to-action ',
							'icon' => 'fa fa-arrow-circle-o-up',
							'header' => '<span class="onp-hightlight">' . $header . '</span>',
							'message' => $message,
							'plugin' => $sociallocker->pluginName,
							'where' => array(
								'plugins',
								'dashboard',
								'edit'
							),
							// buttons and links
							'buttons' => array(
								array(
									'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Learn More & Upgrade', 'bizpanda'),
									'class' => 'button button-primary',
									'action' => $url
								),
								array(
									'title' => __('No, thanks, not now', 'bizpanda'),
									'class' => 'button',
									'action' => 'x'
								)
							)
						);
					}
				} else {
					if( !$alreadyActivated ) {
						$notices[] = array(
							'id' => 'onp-sl-offer-to-purchase',
							'class' => 'call-to-action ',
							'icon' => 'fa fa-arrow-circle-o-up',
							'header' => '<span class="onp-hightlight">' . $header . '</span>',
							'message' => $message,
							'plugin' => $sociallocker->pluginName,
							'where' => array(
								'plugins',
								'dashboard',
								'edit'
							),
							// buttons and links
							'buttons' => array(
								array(
									'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Activate Premium', 'bizpanda'),
									'class' => 'button button-primary',
									'action' => $url
								),
								array(
									'title' => __('No, thanks, not now', 'bizpanda'),
									'class' => 'button',
									'action' => 'x'
								)
							)
						);
					} else {
						$notices[] = array(
							'id' => 'onp-sl-offer-to-purchase',
							'class' => 'call-to-action ',
							'icon' => 'fa fa-arrow-circle-o-up',
							'header' => '<span class="onp-hightlight">' . $header . '</span>',
							'message' => $message,
							'plugin' => $sociallocker->pluginName,
							'where' => array(
								'plugins',
								'dashboard',
								'edit'
							),
							// buttons and links
							'buttons' => array(
								array(
									'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Learn More & Upgrade', 'bizpanda'),
									'class' => 'button button-primary',
									'action' => $url
								),
								array(
									'title' => __('No, thanks, not now', 'bizpanda'),
									'class' => 'button',
									'action' => 'x'
								)
							)
						);
					}
				}
			}

			return $notices;
		}

		add_filter('factory_notices_' . $sociallocker->pluginName, 'onp_sl_upgrade_to_premium_notices', 10, 2);
	}

	if( onp_lang('ru_RU') ) {
		if( onp_build('premium', 'ultimate') ) {
			/**
			 * Уведомление появляется, если не установлен токен доступа вконтакте.
			 * Так как пользователи не любят читать о нововведениях, нужно уведомить их,
			 * что для корректной работы кнопок вконтакте в мобильной версии и на русских доменах,
			 * необходим токен.
			 *
			 * @see   factory_notices
			 * @since 1.0.0
			 */
			function onp_sl_vk_access_token_info($notices)
			{
				global $sociallocker;
				$forceToShowNotices = defined('ONP_DEBUG_SL_OFFER_PREMIUM') && ONP_DEBUG_SL_OFFER_PREMIUM;
				$closed = get_option('factory_notices_closed', array());
				if( isset($closed['onp-sl-vk-access-token-info']) && !$forceToShowNotices ) {
					return $notices;
				}
				$notices[] = array(
					'id' => 'onp-sl-vk-access-token-info',
					'class' => 'alert alert-danger ',
					'header' => '<span class="onp-hightlight">' . __('Не установлен токен доступа Вконтакте!', 'bizpanda') . '</span>',
					'message' => __('<strong>Внимание!</strong> Начиная с версии плагина 4.3.8 вы должны использовать токен доступа Вконтакте, это необходимо для возможной работы кнопок Вконтакте на мобильных версиях и русских доменах.
Чтобы скрыть предупреждение, пожалуйста, установите токен доступа Вконтакте прямо сейчас. Вы можете прочитать инструкцию по получению токена доступа в разделе "Как использовать?".', 'bizpanda'),
					'plugin' => $sociallocker->pluginName,
					'where' => array(
						'plugins',
						'dashboard',
						'edit'
					),
					// buttons and links
					'buttons' => array(
						array(
							'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Перейти к социальным настройкам', 'bizpanda'),
							'class' => 'button btn-danger',
							'action' => opanda_get_settings_url('social')
						),
						array(
							'title' => '<i class="fa fa-times" aria-hidden="true"></i> ' . __('Я не буду использовать кнопки Вконтакте', 'bizpanda'),
							'class' => 'button factory-hide-noties-button',
							'action' => 'x'
						)
					)
				);

				return $notices;
			}

			$vkAcceessToken = get_option('opanda_vk_access_token');
			if( !$vkAcceessToken ) {
				add_filter('factory_notices_' . $sociallocker->pluginName, 'onp_sl_vk_access_token_info', 10, 2);
			}

			/**
			 * Уведомление появляется, если не установлен токен доступа вконтакте.
			 * Так как пользователи не любят читать о нововведениях, нужно уведомить их,
			 * что для корректной работы кнопок вконтакте в мобильной версии и на русских доменах,
			 * необходим токен.
			 *
			 * @see   factory_notices
			 * @since 1.0.0
			 */
			function onp_sl_youtube_client_id_info($notices)
			{
				global $sociallocker;
				$forceToShowNotices = defined('ONP_DEBUG_SL_OFFER_PREMIUM') && ONP_DEBUG_SL_OFFER_PREMIUM;
				$closed = get_option('factory_notices_closed', array());
				if( isset($closed['onp-sl-youtube-client-id-info']) && !$forceToShowNotices ) {
					return $notices;
				}
				$notices[] = array(
					'id' => 'onp-sl-youtube-client-id-info',
					'class' => 'alert alert-danger ',
					'header' => '<span class="onp-hightlight">' . __('Не установлен ID клиента в Google!', 'bizpanda') . '</span>',
					'message' => __('<strong>Внимание!</strong> Начиная с версии плагина 4.4.9 вы должны использовать ID клиента в Google, это необходимо для возможной работы кнопоки Youtube подписаться.
Чтобы скрыть предупреждение, пожалуйста, установите ID клиента в Google прямо сейчас. Вы можете прочитать инструкцию по получению ID клиента в Google в разделе "Как использовать?".', 'bizpanda'),
					'plugin' => $sociallocker->pluginName,
					'where' => array(
						'plugins',
						'dashboard',
						'edit'
					),
					// buttons and links
					'buttons' => array(
						array(
							'title' => '<i class="fa fa-arrow-circle-o-up"></i> ' . __('Перейти к социальным настройкам', 'bizpanda'),
							'class' => 'button btn-danger',
							'action' => opanda_get_settings_url('social')
						),
						array(
							'title' => '<i class="fa fa-times" aria-hidden="true"></i> ' . __('Я не буду использовать кнопку Youtube', 'bizpanda'),
							'class' => 'button factory-hide-noties-button',
							'action' => 'x'
						)
					)
				);

				return $notices;
			}

			$vkAcceessToken = get_option('opanda_google_client_id');
			if( !$vkAcceessToken ) {
				add_filter('factory_notices_' . $sociallocker->pluginName, 'onp_sl_youtube_client_id_info', 10, 2);
			}
		}

		/**
		 * Предупрежение о переходе на новую кнопку фейсбук.
		 *
		 * @see   factory_notices
		 * @since 1.0.0
		 */
		/*function onp_sl_onepress_info( $notices ) {
			global $sociallocker;
			$forceToShowNotices = defined( 'ONP_DEBUG_SL_OFFER_PREMIUM' ) && ONP_DEBUG_SL_OFFER_PREMIUM;
			$closed = get_option( 'factory_notices_closed', array() );

			if( isset( $closed['onp-sl-onepress-info1'] ) && !$forceToShowNotices ) {
				return $notices;
			}
			$notices[] = array(
				'id'      => 'onp-sl-onepress-info1',
				'class'   => 'alert alert-warning ',
				'header'  => '<span class="onp-hightlight">Запись на бета-тестирование Социального замка 5.0</span>',
				'message' => 'Наш проект постепенно переходит на мультиплатформенную ветку развития. Новая, более продвинутая версия плагина полностью перекочует на наши сервера.
				 Объем плагина для Wordpress уменьшится на 80% и практически полностью снимет с вашего сайта какую-либо нагрузку.
				 Вам не нужно будет вручную обновлять плагин, теперь это будет делать за вас наш менеджер обновлений.
				 Новая версия будет иметь множество возможностей, а так же решений старых проблем (например с кнопкой facebook). Запишитесь на бета-тестирование до 16 сентября и получите приятные бонусы за обнаруженные ошибки в новой версии.
				 <a href="https://vk.com/test_sociallocker_3" target="_blank">Хотите узнать о возможностях новой версии и записаться на тестирование?</a>',
				'plugin'  => $sociallocker->pluginName,
				'where'   => array(
					'plugins',
					'dashboard',
					'edit'
				),
				// buttons and links
				'buttons' => array(
					array(
						'title'  => __( 'Скрыть уведомление', 'bizpanda' ),
						'class'  => 'button factory-hide-noties-button',
						'action' => 'x'
					)
				)
			);

			return $notices;
		}

		add_filter( 'factory_notices_' . $sociallocker->pluginName, 'onp_sl_onepress_info', 10, 2 );*/
	}

	if( onp_build('free') ) {
		if( onp_lang('ru_RU') ) {
			// ------------------------------------------------------------------------------------------
			// Achievement Popups
			// ------------------------------------------------------------------------------------------
			/**
			 * Shows the popups offering to rate the plugin.
			 *
			 * @see   factory_notices
			 * @since 1.0.0
			 */
			function onp_sl_achievement_popups($notices)
			{
				global $sociallocker;
				$popup = new OnpSL_RateUs_Popup($sociallocker);
				if( !$popup->isVisible() ) {
					return $notices;
				}
				$notices[] = $popup->getData();

				return $notices;
			}

			add_filter('factory_notices_' . $sociallocker->pluginName, 'onp_sl_achievement_popups', 10, 2);

			/**
			 * A popup which controls of showing the offer to rate the plugin.
			 *
			 * @see   factory_notices
			 * @since 1.0.0
			 */
			class OnpSL_RateUs_Popup {

				public $min = 25;
				public $step = 25;

				public function __construct($plugin)
				{
					$this->plugin = $plugin;
					add_action('admin_enqueue_scripts', array(
						$this,
						'assets'
					));
				}

				/**
				 * Returns an ID of a popup.
				 */
				public function getId()
				{
					$action = $this->getAchievementAction();
					if( empty($action) ) {
						return false;
					}

					return 'onp-sl-' . $action;
				}

				/**
				 * Returns a current achievement action.
				 */
				public function getAchievementAction()
				{
					if( defined('ONP_SL_ACHIEVEMENT_ACTION') && ONP_SL_ACHIEVEMENT_ACTION ) {
						return ONP_SL_ACHIEVEMENT_ACTION;
					}
					$level = $this->getLevel();
					if( empty($level) ) {
						return false;
					}
					$value = $level['value'];
					if( $value < $this->min ) {
						return false;
					}
					$actions = get_option('onp_sl_achievement_popups', array());
					$action = false;
					if( !isset($actions['review']) ) {
						$action = 'review';
					} elseif( !isset($actions['subscription']) ) {
						if( $value >= $actions['review']['value'] + $this->step ) {
							$action = 'subscribe';
						}
					} elseif( !isset($actions['premium']) ) {
						if( $value >= $actions['subscription']['value'] + $this->step ) {
							$action = 'premium';
						}
					}
					if( $action && isset($_COOKIE['onp_sl_' . $action . '_closed']) ) {
						return false;
					}

					return $action;
				}

				/**
				 * A cache var for the method getLevel.
				 */
				protected $_level = false;

				/**
				 * Gets current level reached.
				 */
				public function getLevel()
				{
					if( defined('ONP_SL_ACHIEVEMENT_VALUE') && false !== ONP_SL_ACHIEVEMENT_VALUE ) {
						return array(
							'metric' => 'unlock-via-facebook-like',
							'value' => ONP_SL_ACHIEVEMENT_VALUE
						);
					}
					if( $this->_level !== false ) {
						return $this->_level;
					}
					$counts = $this->getUnlocksCountByButton();
					if( 'inf' == $counts ) {
						return false;
					}
					$result = array(
						'metric' => null,
						'value' => 0
					);
					foreach($counts as $name => $count) {
						if( $count < $this->min ) {
							continue;
						}
						if( $count > $result['value'] ) {
							$result = array(
								'metric' => $name,
								'value' => $count
							);
						}
					}
					if( $result['metric'] == null ) {
						$this->_level = null;
					} else {
						$this->_level = $result;
					}

					return $this->_level;
				}

				/**
				 * Returns true if the popup has to be shown now.
				 */
				public function isVisible()
				{
					$action = $this->getAchievementAction();
					if( 'review' == $action ) {
						return true;
					} else return false;
				}

				/**
				 * A cache var for the method getUnlocksCountByButton.
				 */
				protected $_unlocksCount = false;

				/**
				 * Returns the count of events required to access the count of received likes, tweets, emails etc.
				 */
				protected function getUnlocksCountByButton()
				{
					if( $this->_unlocksCount !== false ) {
						return $this->_unlocksCount;
					}
					$cache = get_site_transient('onp_sl_unlocks_count');
					if( $cache ) {
						$this->_unlocksCount = $cache;

						return $this->_unlocksCount;
					}
					global $wpdb;
					$metrics = array(
						'unlock-via-facebook-like',
						'unlock-via-facebook-share',
						'unlock-via-twitter-tweet',
						'unlock-via-twitter-follow',
						'unlock-via-linkedin-share',
						'unlock-via-google-plus',
						'unlock-via-google-share',
						'unlock-via-vk-like',
						'unlock-via-vk-share',
						'unlock-via-vk-subscribe',
						'unlock-via-mail-share',
						'unlock-via-ok-share',
						'email-received'
					);
					$value = intval($wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "opanda_stats_v2 WHERE metric_name='unlock'"));
					if( $value > 10000 ) {
						$this->_unlocksCount = 'inf';
						set_site_transient('onp_sl_unlocks_count', $this->_unlocksCount, 60 * 60 * 12);

						return $this->_unlocksCount;
					}
					$inClause = array();
					foreach($metrics as $metric)
						$inClause[] = "'$metric'";
					$inClause = implode(',', $inClause);
					$sql = "SELECT SUM(metric_value) as total_count, metric_name " . "FROM " . $wpdb->prefix . "opanda_stats_v2 " . "WHERE metric_name IN ($inClause) GROUP BY metric_name";
					$counts = $wpdb->get_results($sql, ARRAY_A);
					$result = array();
					foreach($counts as $row) {
						$result[$row['metric_name']] = $row['total_count'];
					}
					foreach($metrics as $metric) {
						if( !isset($result[$metric]) ) {
							$result[$metric] = 0;
						}
					}
					$this->_unlocksCount = $result;
					set_site_transient('onp_sl_unlocks_count', $this->_unlocksCount, 60 * 60 * 12);

					return $this->_unlocksCount;
				}

				/**
				 * Adds assets for the popup.
				 */
				public function assets($hook)
				{
					// sytles for the plugin notices
					if( $hook == 'index.php' || $hook == 'plugins.php' || $hook == 'edit.php' ) {
						wp_enqueue_style('sociallocker-notices', SOCIALLOCKER_URL . '/plugin/admin/assets/css/notices.010000.css');
						wp_enqueue_script('sociallocker-notices', SOCIALLOCKER_URL . '/plugin/admin/assets/js/notices.010000.js');
					}
				}

				/**
				 * Returns HTML message for the popup.
				 */
				public function getMessage()
				{
					$level = $this->getLevel();
					$value = floor($level['value'] / 5) * 5;
					$action = $this->getAchievementAction();
					switch( $level['metric'] ) {
						case 'unlock-via-facebook-like':
							$units = __('likes', 'bizpanda');
							$where = __('on Facebook', 'bizpanda');
							break;
						case 'unlock-via-facebook-share':
							$units = __('shares', 'bizpanda');
							$where = __('on Facebook', 'bizpanda');
							break;
						case 'unlock-via-twitter-tweets':
							$units = __('tweets', 'bizpanda');
							$where = __('on Twitter', 'bizpanda');
							break;
						case 'unlock-via-twitter-followers':
							$units = __('followers', 'bizpanda');
							$where = __('on Twitter', 'bizpanda');
							break;
						case 'unlock-via-linkedin-share':
							$units = __('shares', 'bizpanda');
							$where = __('on Linkedin', 'bizpanda');
							break;
						case 'unlock-via-google-plus':
							$units = __('pluses', 'bizpanda');
							$where = __('on Google', 'bizpanda');
							break;
						case 'unlock-via-vk-like':
							$units = __('likes', 'bizpanda');
							$where = __('on Vkontakte', 'bizpanda');
							break;
						case 'unlock-via-vk-share':
							$units = __('shares', 'bizpanda');
							$where = __('on Vkontakte', 'bizpanda');
							break;
						case 'unlock-via-vk-subscribe':
							$units = __('subscribes', 'bizpanda');
							$where = __('on Vkontakte', 'bizpanda');
							break;
						case 'unlock-via-mail-share':
							$units = __('shares', 'bizpanda');
							$where = __('on Mail', 'bizpanda');
							break;
						case 'unlock-via-ok-share':
							$units = __('shares', 'bizpanda');
							$where = __('on Odnoklassniki', 'bizpanda');
							break;
						case 'email-received':
							$units = __('emails', 'bizpanda');
							$where = __('collected', 'bizpanda');
							break;
					}
					if( 'email-received' == $level['metric'] ) {
						$description = sprintf(__('Congrats! You collected more %s emails via <strong>Social Locker</strong>.', 'bizpanda'), $value, $units);
					} else {
						$description = sprintf(__('Congrats! You gained more %s %s via <strong>Social Locker</strong>.', 'bizpanda'), $value, $units);
					}
					$premiumUrl = onp_sl_get_premium_url('achievements');
					ob_start();
					?>
					<div>
						<div class="onp-sl-achievement onp-sl-<?php echo $level['metric'] ?>">
							<span class="onp-sl-count"><?php _e('+', 'bizpanda') ?><?php echo $value ?></span>
                    <span class="onp-sl-count-explanation">
                        <span class="onp-sl-units"><?php echo $units ?></span><br/>
                        <span class="onp-sl-where"><?php echo $where ?></span>  
                    </span>
							<span class="onp-sl-exclamation"><?php _e('!', 'bizpanda') ?></span>
						</div>

						<?php if( 'review' == $action ) { ?>

							<div class="onp-sl-text">
								<p><?php echo $description ?></p>

								<p><?php _e('Please do us a BIG favor, give the plugin a 5-star rating on wordpress.org.', 'bizpanda') ?></p>
							</div>

							<div class="onp-sl-buttons">
								<?php if (!onp_lang('ru_RU')) { ?>
								<a href='https://wordpress.org/support/view/plugin-reviews/social-locker?filter=5'
								   target="_blank" class='onp-sl-button onp-sl-button-primary'
								   data-achievement-value="<?php echo $level['value'] ?>">
									<?php } else { ?>
									<a href='http://onepress-sociallocker.reformal.ru' target="_blank"
									   class='onp-sl-button onp-sl-button-primary'
									   data-achievement-value="<?php echo $level['value'] ?>">
										<?php } ?>
										<i class='fa fa-star'></i><?php _e('Sure, you deserved it!', 'bizpanda') ?>
									</a><br/>
									<a href='#' class='onp-sl-button-link'
									   data-achievement-value="<?php echo $level['value'] ?>">
										<?php _e('I already did', 'bizpanda') ?>
									</a>
									<a href='#' class='onp-sl-button-link'
									   data-achievement-value="<?php echo $level['value'] ?>">
										<?php _e('No, not good enough', 'bizpanda') ?>
									</a>
							</div>

						<?php } ?>

						<div class='onp-sl-status-bar'>
							<?php printf(__('Want more %s? Try the <a href="%s" target="_blank">premium version</a>.', 'bizpanda'), $units, $premiumUrl) ?>
						</div>
					</div>
					<?php
					$message = ob_get_contents();
					ob_end_clean();

					return $message;
				}

				/**
				 * Returns data for the popup.
				 */
				public function getData()
				{
					$data = array(
						'id' => $this->getId(),
						'class' => 'onp-sl-rateus-popup factory-fontawesome-000',
						'position' => 'popup',
						'layout' => 'custom',
						'close' => 'quick-hide',
						'message' => $this->getMessage(),
						'where' => array(
							'plugins',
							'dashboard',
							'edit'
						)
					);

					return $data;
				}
			}

			/**
			 * Handles an ajax request to hide a specified achievement popup.
			 */
			function onp_sl_hide_achievement()
			{
				$type = isset($_REQUEST['achievementType'])
					? $_REQUEST['achievementType']
					: null;
				$value = isset($_REQUEST['achievementValue'])
					? intval($_REQUEST['achievementValue'])
					: null;
				if( empty($type) || empty($value) ) {
					echo json_encode(array('error' => __('Invalid request type.', 'bizpanda')));
					exit;
				}
				$achievementPopups = get_option('onp_sl_achievement_popups', array());
				if( isset($achievementPopups[$type]) ) {
					return false;
				}
				$achievementPopups[$type] = array();
				$achievementPopups[$type]['value'] = $value;
				$achievementPopups[$type]['time'] = time();
				delete_option('onp_sl_achievement_popups');
				add_option('onp_sl_achievement_popups', $achievementPopups);
				exit;
			}

			add_action('wp_ajax_onp_sl_hide_achievement', 'onp_sl_hide_achievement');
		}
	}

	// ------------------------------------------------------------------------------------------
	// StyleRoller
	// ------------------------------------------------------------------------------------------

	if( !onp_build('free', 'ultimate') ) {

		/**
		 * Shows offers to purhcase the StyleRoller from time to time.
		 *
		 * @since 3.5.0
		 */
		function onp_sl_styleroller_notices($notices)
		{
			if( defined('ONP_SL_STYLER_PLUGIN_ACTIVE') ) {
				return $notices;
			}

			// show messages only for administrators
			if( !factory_000_is_administrator() ) {
				return $notices;
			}

			if( onp_license('free', 'trial') ) {
				return $notices;
			}

			global $sociallocker;
			$closed = get_option('factory_notices_closed', array());

			// leans when the premium versio was activated
			$premiumActivated = isset($sociallocker->license->data['Activated'])
				? $sociallocker->license->data['Activated']
				// for new users
				: 0;                                        // for old users

			$isNewUser = ($premiumActivated !== 0);
			$secondsInDay = 60 * 60 * 24;

			$inSeconds = time() - $premiumActivated;
			$inDays = $inSeconds / $secondsInDay;

			$forceToShow = defined('ONP_DEBUG_SHOW_STYLEROLLER_MESSAGE') && ONP_DEBUG_SHOW_STYLEROLLER_MESSAGE;
			$lang = $sociallocker->options['lang'];

			// offers a discount for new users who purchased the Social Locker a day ago
			if( ($isNewUser && $inDays >= 1 && $inDays <= 3 && !isset($closed['sociallocker-styleroller-after-a-day'])) || $forceToShow ) {

				$premiumActivated = $premiumActivated + 24 * 60 * 60;
				$expiresIn = ceil((3 - $inDays) * 24);

				$notices[] = array(
					'id' => 'sociallocker-styleroller-after-a-day',
					// content and color
					'class' => 'call-to-action sociallocker-styleroller-banner onp-sl-' . $lang,
					'header' => '<span class="onp-hightlight">' . sprintf(__('You\'ve got the %s discount on the StyleRoller Add-On!', 'bizpanda'), '40%') . '</span>' . sprintf(__('(Expires In %sh)', 'bizpanda'), $expiresIn),
					'message' => sprintf(__('<p>It\'s a day since you activated the Social Locker. We would like to make you a small gift, the %s discount on the StyleRoller Add-on. This is a time-limited offer which will be valid within 2 days.</p><p>The StyleRoller Add-on will help you to brand the Social Locker to fit the look and feel of your website, create your own unique attention-grabbing themes and, as a result, increase the number of likes and shares.</p>', 'bizpanda'), '40%'),
					'plugin' => $sociallocker->pluginName,
					'where' => array('plugins', 'dashboard', 'edit'),
					// buttons and links
					'buttons' => array(
						array(
							'class' => 'btn btn-primary',
							'title' => sprintf(__('Get StyleRoller For %s Off', 'bizpanda'), '40%'),
							'action' => $sociallocker->options['styleroller'] . '-special/?' . http_build_query(array(
									'onp_special' => md5($premiumActivated) . $premiumActivated,
									'onp_target' => base64_encode(get_site_url()),
									'utm_source' => 'plugin',
									'utm_medium' => 'styleroller-banner',
									'utm_campaign' => 'after-a-day'
								))
						),
						array(
							'title' => __('Hide this message', 'bizpanda'),
							'class' => 'btn btn-default',
							'action' => 'x'
						)
					)
				);
			}

			// offers a discount for new users who purchased the Social Locker a week ago
			if( ($isNewUser && $inDays >= 7 && $inDays <= 9 && !isset($closed['sociallocker-styleroller-after-a-week'])) || $forceToShow ) {

				$premiumActivated = $premiumActivated + 7 * 24 * 60 * 60;
				$expiresIn = ceil((9 - $inDays) * 24);

				$notices[] = array(
					'id' => 'sociallocker-styleroller-after-a-week',
					// content and color
					'class' => 'call-to-action sociallocker-styleroller-banner onp-sl-' . $lang,
					'icon' => 'fa fa-frown-o',
					'header' => '<span class="onp-hightlight">' . sprintf(__('Last Chance To Get StyleRoller For %s Off!', 'bizpanda'), '40%') . '</span>' . sprintf(__('(Expires In %sh)', 'bizpanda'), $expiresIn),
					'message' => sprintf(__('We have noticed you have been using the Social Locker already more than a week. Did you know what via the StyleRoller, an add-on for creating own attention-grabbing themes, you can improve conversions of your lockers by up to %s? Learn how, click the button below.', 'bizpanda'), '300%'),
					'plugin' => $sociallocker->pluginName,
					'where' => array('plugins', 'dashboard', 'edit'),
					// buttons and links
					'buttons' => array(
						array(
							'class' => 'btn btn-primary',
							'title' => sprintf(__('Get StyleRoller For s% Off', 'bizpanda'), '40%'),
							'action' => $sociallocker->options['styleroller'] . '-special/?' . http_build_query(array(
									'onp_special' => md5($premiumActivated) . $premiumActivated,
									'onp_target' => base64_encode(get_site_url()),
									'utm_source' => 'plugin',
									'utm_medium' => 'styleroller-banner',
									'utm_campaign' => 'after-a-week'
								))
						),
						array(
							'title' => __('Hide this message', 'bizpanda'),
							'class' => 'btn btn-default',
							'action' => 'x'
						)
					)
				);
			}

			// this banner only for old users
			if( (!$isNewUser) || $forceToShow ) {

				$firstShowTime = get_option('onp_sl_styleroller_firt_time', false);
				if( !$firstShowTime ) {
					$firstShowTime = time();
					update_option('onp_sl_styleroller_firt_time', time());
				}

				$inSeconds = time() - $firstShowTime;
				$inDays = $inSeconds / $secondsInDay;

				// this offer is available only 2 days
				if( ($inDays <= 2 && !isset($closed['sociallocker-styleroller-new-addon'])) || $forceToShow ) {

					$expiresIn = ceil((2 - $inDays) * 24);

					$notices[] = array(
						'id' => 'sociallocker-styleroller-new-addon',
						// content and color
						'class' => 'call-to-action sociallocker-styleroller-banner onp-sl-' . $lang,
						'icon' => 'fa fa-frown-o',
						'header' => '<span class="onp-hightlight">' . sprintf(__('You\'ve got the %s discount on the StyleRoller Add-On!', 'bizpanda'), '40%') . '</span>' . sprintf(__('(Expires In %sh)', 'bizpanda'), $expiresIn),
						'message' => sprintf(__('We would like to make you a small gift, the %s discount on the StyleRoller Add-on. This is a time-limited offer which will be valid within 2 days. The StyleRoller Add-on will help you to brand the Social Locker to fit the look and feel of your website, create your own unique attention-grabbing themes and, as a result, increase the number of likes and shares.', 'bizpanda'), '40%'),
						'plugin' => $sociallocker->pluginName,
						'where' => array('plugins', 'dashboard', 'edit'),
						// buttons and links
						'buttons' => array(
							array(
								'class' => 'btn btn-primary',
								'title' => sprintf(__('Get StyleRoller For %s Off', 'bizpanda'), '40%'),
								'action' => $sociallocker->options['styleroller'] . '-special/?' . http_build_query(array(
										'onp_special' => md5($firstShowTime) . $firstShowTime,
										'onp_target' => base64_encode(get_site_url()),
										'utm_source' => 'plugin',
										'utm_medium' => 'styleroller-banner',
										'utm_campaign' => 'new-addon'
									))
							),
							array(
								'title' => __('Hide this message', 'bizpanda'),
								'class' => 'btn btn-default',
								'action' => 'x'
							)
						)
					);
				}

				// this offer apperas after a week withing a day
				if( ($inDays >= 7 && $inDays <= 9 && !isset($closed['sociallocker-styleroller-new-addon-after-a-week'])) || $forceToShow ) {

					$firstShowTime = $firstShowTime + 7 * 24 * 60 * 60;
					$expiresIn = ceil((9 - $inDays) * 24);

					$notices[] = array(
						'id' => 'sociallocker-styleroller-new-addon-after-a-week',
						// content and color
						'class' => 'call-to-action sociallocker-styleroller-banner onp-sl-' . $lang,
						'icon' => 'fa fa-frown-o',
						'header' => '<span class="onp-hightlight">' . sprintf(__('Last Chance To Get StyleRoller For %s Off!', 'bizpanda'), '40%') . '</span>' . sprintf(__('(Expires In %sh)', 'bizpanda'), $expiresIn),
						'message' => sprintf(__('Did you know what via the StyleRoller, an add-on for creating own attention-grabbing themes for Social Locker, you can improve conversions of your lockers by up to %s? Click the button to learn more and get the discount.', 'bizpanda'), '300%'),
						'plugin' => $sociallocker->pluginName,
						'where' => array('plugins', 'dashboard', 'edit'),
						// buttons and links
						'buttons' => array(
							array(
								'class' => 'btn btn-primary',
								'title' => sprintf(__('Get StyleRoller For %s Off', 'bizpanda'), '40%'),
								'action' => $sociallocker->options['styleroller'] . '-special/?' . http_build_query(array(
										'onp_special' => md5($firstShowTime) . $firstShowTime,
										'onp_target' => base64_encode(get_site_url()),
										'utm_source' => 'plugin',
										'utm_medium' => 'styleroller-banner',
										'utm_campaign' => 'new-addon-after-a-week'
									))
							),
							array(
								'title' => __('Hide this message', 'bizpanda'),
								'class' => 'btn btn-default',
								'action' => 'x'
							)
						)
					);
				}
			}

			return $notices;
		}

		add_filter('factory_notices_' . $sociallocker->pluginName, 'onp_sl_styleroller_notices');

		/**
		 * Assets for the StyleRoller banner.
		 */
		function onp_sl_assets_for_styleroller_notices($hook)
		{

			// sytles for the plugin notices
			if( $hook == 'index.php' || $hook == 'plugins.php' || $hook == 'edit.php' ) {

				wp_enqueue_style('sociallocker-notices', SOCIALLOCKER_URL . '/plugin/admin/assets/css/notices.010000.css');
				wp_enqueue_script('sociallocker-notices', SOCIALLOCKER_URL . '/plugin/admin/assets/js/notices.010000.js');
			}
		}

		add_action('admin_enqueue_scripts', 'onp_sl_assets_for_styleroller_notices');
	}