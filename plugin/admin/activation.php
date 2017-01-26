<?php

	/**
	 * The activator class performing all the required actions on activation.
	 *
	 * @see Factory000_Activator
	 * @since 1.0.0
	 */
	class Onp_Paylocker_Activation extends Factory000_Activator {

		/**
		 * Runs activation actions.
		 *
		 * @since 1.0.1
		 */
		public function activate()
		{
			$this->createTables();
			$this->setupLicense();

			// Add user role
			add_role('pl_premium_subscriber', __('Премиум подписчик', 'bizpanda'), array('read' => true));

			$this->addPost('onp_paylocker_defaul_id', array(
				'post_type' => OPANDA_POST_TYPE,
				'post_title' => __('Замок на "Платный контент"', 'bizpanda'),
				'post_name' => 'onp_paylocker_default'
			), array(
				'opanda_item' => 'pay-locker',
				'opanda_header' => __('Эта часть контента доступна только подписчикам', 'bizpanda'),
				'opanda_message' => __('Оформите подписку и вы получите, неограниченный доступ ко всем статьям.', 'bizpanda'),
				'opanda_style' => 'paylocker',
				'opanda_is_system' => 1,
				'opanda_is_default' => 1,
				'opanda_mobile' => 1
			));
		}

		/**
		 * Creates table required for the plugin.
		 *
		 * @since 1.0.0
		 */
		protected function createTables()
		{
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$transactions = "
				CREATE TABLE {$wpdb->prefix}opanda_pl_transactions (
				transaction_id varchar(60) NOT NULL,
				user_id int(11) UNSIGNED NOT NULL,
				locker_id int(11) UNSIGNED NOT NULL,
				post_id int(11) UNSIGNED DEFAULT NULL,
				table_payment_type varchar(15) NOT NULL,
				table_name varchar(30) NOT NULL,
				table_price int(11) NOT NULL,
				transaction_status ENUM('cancel','waiting','finish') NOT NULL DEFAULT 'waiting',
				transaction_begin int(11) NOT NULL,
				transaction_end int(11) DEFAULT NULL,
				PRIMARY KEY (transaction_id)
				)
				ENGINE = INNODB
				CHARACTER SET utf8
				COLLATE utf8_general_ci;
            );";
			dbDelta($transactions);

			$premiumUsers = "
				CREATE TABLE {$wpdb->prefix}opanda_pl_subsribers (
				  user_id int(11) NOT NULL,
				  locker_id int(11) NOT NULL,
				  expired_begin int(11) NOT NULL,
				  expired_end int(11) NOT NULL,
				  UNIQUE INDEX uq_pl_subscribers (user_id, locker_id)
				)
				ENGINE = INNODB
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";
			dbDelta($premiumUsers);

			$purchases = "
				CREATE TABLE {$wpdb->prefix}opanda_pl_purchased_posts (
				  post_id int(11) NOT NULL,
				  user_id int(11) NOT NULL,
				  locker_id int(11) NOT NULL,
				  price int(11) NOT NULL,
				  transaction_id varchar(60) NOT NULL,
				  purchased_date int(11) NOT NULL,
				  UNIQUE INDEX uq_wp_opanda_pl_purchased_posts (post_id, user_id, locker_id)
				)
				ENGINE = INNODB
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";
			dbDelta($purchases);
		}

		/**
		 * Setups the license.
		 *
		 * @since 1.0.0
		 */
		protected function setupLicense()
		{

			// sets the default licence
			// the default license is a license that is used when a license key is not activated

			if( onp_build('premium') ) {

				$this->plugin->license->setDefaultLicense(array(
					'Category' => 'free',
					'Build' => 'premium',
					'Title' => 'OnePress Zero License',
					'Description' => __('Please, activate the plugin to get started. Enter a key
                                    you received with the plugin into the form below.', 'bizpanda')
				));
			}

			if( onp_build('ultimate') ) {

				$this->plugin->license->setDefaultLicense(array(
					'Category' => 'free',
					'Build' => 'ultimate',
					'Title' => 'OnePress Zero License',
					'Description' => __('Please, activate the plugin to get started. Enter a key
                                    you received with the plugin into the form below.', 'bizpanda')
				));
			}

			if( onp_build('free') ) {

				$this->plugin->license->setDefaultLicense(array(
					'Category' => 'free',
					'Build' => 'free',
					'Title' => 'OnePress Public License',
					'Description' => __('Public License is a GPLv2 compatible license.
                                    It allows you to change this version of the plugin and to
                                    use the plugin free. Please remember this license
                                    covers only free edition of the plugin. Premium versions are
                                    distributed with other type of a license.', 'bizpanda')
				));
			}
		}
	}

	global $paylocker;

	$paylocker->registerActivation('Onp_Paylocker_Activation');