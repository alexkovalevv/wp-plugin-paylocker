<?php
	/**
	 * Contains functions, hooks and classes required for activating the plugin.
	 *
	 * @author Paul Kashtanoff <pavelkashtanoff@gmail.com>
	 * @copyright (c) 2014, OnePress
	 *
	 * @since 4.0.0
	 * @package sociallocker
	 */

	/**
	 * Changes the text of the button which is shown after the success activation of the plugin.
	 *
	 * @since 3.1.0
	 * @return string
	 */
	function sociallocker_license_manager_success_button()
	{
		return __('Learn how to use the plugin <i class="fa fa-lightbulb-o"></i>', 'bizpanda');
	}

	add_action('onp_license_manager_success_button_' . $sociallocker->pluginName, 'sociallocker_license_manager_success_button');

	/**
	 * Returns an URL where we should redirect a user to, after the success activation of the plugin.
	 *
	 * @since 3.1.0
	 * @return string
	 */
	function sociallocker_license_manager_success_redirect()
	{
		return opanda_get_admin_url('how-to-use', array('onp_sl_page' => 'sociallocker'));
	}

	add_action('onp_license_manager_success_redirect_' . $sociallocker->pluginName, 'sociallocker_license_manager_success_redirect');


	/**
	 * The activator class performing all the required actions on activation.
	 *
	 * @see Factory000_Activator
	 * @since 1.0.0
	 */
	class SocialLocker_Activation extends Factory000_Activator {

		/**
		 * Runs activation actions.
		 *
		 * @since 1.0.1
		 */
		public function activate()
		{
			$this->setupLicense();

			$early_activate = get_option('opanda_tracking', false);
			$isLicense =  get_option('onp_license_sociallocker-rus', false);

			if( onp_build('free') ) {
				if( !$early_activate ) {
					factory_000_set_lazy_redirect(opanda_get_admin_url('how-to-use', array('opanda_page' => 'optinpanda')));
				}
			}

			if( $early_activate && $isLicense ) {
				factory_000_set_lazy_redirect(opanda_get_admin_url('how-to-use', array('onp_sl_page' => 'sociallocker-last-updates')));
			}

			$input_popup_theme_styles = get_option('opanda_sr_styles_input-popup');

			if( empty($input_popup_theme_styles) ) {
				update_option('opanda_sr_styles_input-popup', array(
					'default' => array(
						'profile_title' => 'Facebook',
					),
					'582ada608d07b' => array(
						'profile_title' => 'Вконтакте',
						'profile_title_is_active' => 1,
						'style_cache' => '.p582ada608d07b.onp-sl-input-popup .onp-sl-text{background-color: rgba(91,122,168,1);}.p582ada608d07b.onp-sl-input-popup .onp-sl-strong{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 25px; color: #ffffff; text-shadow:none;}.p582ada608d07b.onp-sl-input-popup .onp-sl-text .onp-sl-strong:before, .p582ada608d07b.onp-sl-input-popup .onp-sl-text .onp-sl-strong:after{background-image: url("");}.p582ada608d07b.onp-sl-input-popup .onp-sl-text .onp-sl-message{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 16px; color: #ffffff; text-shadow:none;}.p582ada608d07b.onp-sl.onp-sl-input-popup .onp-sl-social-buttons .onp-sl-control{background-color: rgba(255,255,255,0.01);}.p582ada608d07b.onp-sl-input-popup .onp-sl-thanks-link{border: none; background: none;}',
						'style_cache_is_active' => 1,
						'style_fonts' => 'false',
						'style_fonts_is_active' => 1,
						'background_type' => 'color',
						'background_type_is_active' => 1,
						'background_color__color' => '#5b7aa8',
						'background_color__opacity' => '100',
						'background_color_is_active' => 1,
						'background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'background_gradient_is_active' => 0,
						'background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'background_image__color' => '',
						'background_image_is_active' => 0,
						'header_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'header_text__size' => '25',
						'header_text__color' => '#ffffff',
						'header_text_is_active' => 1,
						'message_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'message_text__size' => '15',
						'message_text__color' => '#ffffff',
						'message_text_is_active' => 1,
						'container_paddings' => '20px 0px 40px 40px',
						'container_paddings_is_active' => 1,
						'after_header_margin' => '0',
						'after_header_margin_is_active' => 1,
						'after_message_margin' => '5',
						'after_message_margin_is_active' => 1,
						'bottom_background_type' => 'bottom_color',
						'bottom_background_type_is_active' => 1,
						'button_layer_background_color__color' => '#ffffff',
						'button_layer_background_color__opacity' => '1',
						'button_layer_background_color_is_active' => 1,
						'button_layer_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'button_layer_background_gradient_is_active' => 0,
						'button_layer_paddings' => '10px 10px 10px 10px',
						'button_layer_paddings_is_active' => 1,
						'bottom_background_color__color' => '#f9f9f9',
						'bottom_background_color__opacity' => '100',
						'bottom_background_color_is_active' => 1,
						'bottom_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'bottom_background_gradient_is_active' => 0,
						'bottom_background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'bottom_background_image__color' => '',
						'bottom_background_image_is_active' => 0,
						'bottom_link_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_link_text__size' => '15',
						'bottom_link_text__color' => '#a2a0a0',
						'bottom_link_text_is_active' => 1,
						'bottom_link_underline' => 0,
						'bottom_link_underline_is_active' => 1,
						'bottom_timer_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_timer_text__size' => '15',
						'bottom_timer_text__color' => '#a2a0a0',
						'bottom_timer_text_is_active' => 1,
						'bottom_container_paddings' => '10px 20px 10px 20px',
						'bottom_container_paddings_is_active' => 1,
						'theme_id' => 'input-popup',
						'style_id' => '582ada608d07b',
					),
					'582ada9787b85' => array(
						'profile_title' => 'Одноклассники',
						'profile_title_is_active' => 1,
						'style_cache' => '.p582ada9787b85.onp-sl-input-popup .onp-sl-text{background-color: rgba(238,130,8,1);}.p582ada9787b85.onp-sl-input-popup .onp-sl-strong{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 25px; color: #ffffff; text-shadow:none;}.p582ada9787b85.onp-sl-input-popup .onp-sl-text .onp-sl-strong:before, .p582ada9787b85.onp-sl-input-popup .onp-sl-text .onp-sl-strong:after{background-image: url("");}.p582ada9787b85.onp-sl-input-popup .onp-sl-text .onp-sl-message{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 16px; color: #ffffff; text-shadow:none;}.p582ada9787b85.onp-sl.onp-sl-input-popup .onp-sl-social-buttons .onp-sl-control{background-color: rgba(255,255,255,0.01);}.p582ada9787b85.onp-sl-input-popup .onp-sl-thanks-link{border: none; background: none;}',
						'style_cache_is_active' => 1,
						'style_fonts' => 'false',
						'style_fonts_is_active' => 1,
						'background_type' => 'color',
						'background_type_is_active' => 1,
						'background_color__color' => '#ee8208',
						'background_color__opacity' => '100',
						'background_color_is_active' => 1,
						'background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'background_gradient_is_active' => 0,
						'background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'background_image__color' => '',
						'background_image_is_active' => 0,
						'header_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'header_text__size' => '25',
						'header_text__color' => '#ffffff',
						'header_text_is_active' => 1,
						'message_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'message_text__size' => '15',
						'message_text__color' => '#ffffff',
						'message_text_is_active' => 1,
						'container_paddings' => '20px 0px 40px 40px',
						'container_paddings_is_active' => 1,
						'after_header_margin' => '0',
						'after_header_margin_is_active' => 1,
						'after_message_margin' => '5',
						'after_message_margin_is_active' => 1,
						'bottom_background_type' => 'bottom_color',
						'bottom_background_type_is_active' => 1,
						'button_layer_background_color__color' => '#ffffff',
						'button_layer_background_color__opacity' => '1',
						'button_layer_background_color_is_active' => 1,
						'button_layer_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'button_layer_background_gradient_is_active' => 0,
						'button_layer_paddings' => '10px 10px 10px 10px',
						'button_layer_paddings_is_active' => 1,
						'bottom_background_color__color' => '#f9f9f9',
						'bottom_background_color__opacity' => '100',
						'bottom_background_color_is_active' => 1,
						'bottom_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'bottom_background_gradient_is_active' => 0,
						'bottom_background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'bottom_background_image__color' => '',
						'bottom_background_image_is_active' => 0,
						'bottom_link_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_link_text__size' => '15',
						'bottom_link_text__color' => '#a2a0a0',
						'bottom_link_text_is_active' => 1,
						'bottom_link_underline' => 0,
						'bottom_link_underline_is_active' => 1,
						'bottom_timer_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_timer_text__size' => '15',
						'bottom_timer_text__color' => '#a2a0a0',
						'bottom_timer_text_is_active' => 1,
						'bottom_container_paddings' => '10px 20px 10px 20px',
						'bottom_container_paddings_is_active' => 1,
						'theme_id' => 'input-popup',
						'style_id' => '582ada9787b85',
					),
					'582adad2b2732' => array(
						'profile_title' => 'Twitter',
						'profile_title_is_active' => 1,
						'style_cache' => '.p582adad2b2732.onp-sl-input-popup .onp-sl-text{background-color: rgba(65,171,225,1);}.p582adad2b2732.onp-sl-input-popup .onp-sl-strong{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 25px; color: #ffffff; text-shadow:none;}.p582adad2b2732.onp-sl-input-popup .onp-sl-text .onp-sl-strong:before, .p582adad2b2732.onp-sl-input-popup .onp-sl-text .onp-sl-strong:after{background-image: url("");}.p582adad2b2732.onp-sl-input-popup .onp-sl-text .onp-sl-message{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 16px; color: #ffffff; text-shadow:none;}.p582adad2b2732.onp-sl.onp-sl-input-popup .onp-sl-social-buttons .onp-sl-control{background-color: rgba(255,255,255,0.01);}',
						'style_cache_is_active' => 1,
						'style_fonts' => 'false',
						'style_fonts_is_active' => 1,
						'background_type' => 'color',
						'background_type_is_active' => 1,
						'background_color__color' => '#41abe1',
						'background_color__opacity' => '100',
						'background_color_is_active' => 1,
						'background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'background_gradient_is_active' => 0,
						'background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'background_image__color' => '',
						'background_image_is_active' => 0,
						'header_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'header_text__size' => '25',
						'header_text__color' => '#ffffff',
						'header_text_is_active' => 1,
						'message_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'message_text__size' => '15',
						'message_text__color' => '#ffffff',
						'message_text_is_active' => 1,
						'container_paddings' => '20px 0px 40px 40px',
						'container_paddings_is_active' => 1,
						'after_header_margin' => '0',
						'after_header_margin_is_active' => 1,
						'after_message_margin' => '5',
						'after_message_margin_is_active' => 1,
						'bottom_background_type' => 'bottom_color',
						'bottom_background_type_is_active' => 1,
						'button_layer_background_color__color' => '#ffffff',
						'button_layer_background_color__opacity' => '1',
						'button_layer_background_color_is_active' => 1,
						'button_layer_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'button_layer_background_gradient_is_active' => 0,
						'button_layer_paddings' => '10px 10px 10px 10px',
						'button_layer_paddings_is_active' => 1,
						'bottom_background_color__color' => '#f9f9f9',
						'bottom_background_color__opacity' => '100',
						'bottom_background_color_is_active' => 1,
						'bottom_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'bottom_background_gradient_is_active' => 0,
						'bottom_background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'bottom_background_image__color' => '',
						'bottom_background_image_is_active' => 0,
						'bottom_link_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_link_text__size' => '15',
						'bottom_link_text__color' => '#a2a0a0',
						'bottom_link_text_is_active' => 1,
						'bottom_link_underline' => 0,
						'bottom_link_underline_is_active' => 1,
						'bottom_timer_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_timer_text__size' => '15',
						'bottom_timer_text__color' => '#a2a0a0',
						'bottom_timer_text_is_active' => 1,
						'bottom_container_paddings' => '10px 20px 10px 20px',
						'bottom_container_paddings_is_active' => 1,
						'theme_id' => 'input-popup',
						'style_id' => '582adad2b2732',
					),
					'582adb06b3d49' => array(
						'profile_title' => 'Youtube',
						'profile_title_is_active' => 1,
						'style_cache' => '.p582adb06b3d49.onp-sl-input-popup .onp-sl-text{background-color: rgba(218,38,37,1);}.p582adb06b3d49.onp-sl-input-popup .onp-sl-strong{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 25px; color: #ffffff; text-shadow:none;}.p582adb06b3d49.onp-sl-input-popup .onp-sl-text .onp-sl-strong:before, .p582adb06b3d49.onp-sl-input-popup .onp-sl-text .onp-sl-strong:after{background-image: url("");}.p582adb06b3d49.onp-sl-input-popup .onp-sl-text .onp-sl-message{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 16px; color: #ffffff; text-shadow:none;}.p582adb06b3d49.onp-sl.onp-sl-input-popup .onp-sl-social-buttons .onp-sl-control{background-color: rgba(255,255,255,0.01);}.p582adb06b3d49.onp-sl-input-popup .onp-sl-thanks-link{border: none; background: none;}',
						'style_cache_is_active' => 1,
						'style_fonts' => 'false',
						'style_fonts_is_active' => 1,
						'background_type' => 'color',
						'background_type_is_active' => 1,
						'background_color__color' => '#da2625',
						'background_color__opacity' => '100',
						'background_color_is_active' => 1,
						'background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'background_gradient_is_active' => 0,
						'background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'background_image__color' => '',
						'background_image_is_active' => 0,
						'header_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'header_text__size' => '25',
						'header_text__color' => '#ffffff',
						'header_text_is_active' => 1,
						'message_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'message_text__size' => '15',
						'message_text__color' => '#ffffff',
						'message_text_is_active' => 1,
						'container_paddings' => '20px 0px 40px 40px',
						'container_paddings_is_active' => 1,
						'after_header_margin' => '0',
						'after_header_margin_is_active' => 1,
						'after_message_margin' => '5',
						'after_message_margin_is_active' => 1,
						'bottom_background_type' => 'bottom_color',
						'bottom_background_type_is_active' => 1,
						'button_layer_background_color__color' => '#ffffff',
						'button_layer_background_color__opacity' => '1',
						'button_layer_background_color_is_active' => 1,
						'button_layer_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'button_layer_background_gradient_is_active' => 0,
						'button_layer_paddings' => '10px 10px 10px 10px',
						'button_layer_paddings_is_active' => 1,
						'bottom_background_color__color' => '#f9f9f9',
						'bottom_background_color__opacity' => '100',
						'bottom_background_color_is_active' => 1,
						'bottom_background_gradient' => '{\\"filldirection\\":\\"top\\",\\"color_points\\":[\\"#1bbc9d 0% 1\\",\\"#16a086 100% 1\\"]}',
						'bottom_background_gradient_is_active' => 0,
						'bottom_background_image__url' => 'http://sociallocker.dev/wp-content/plugins/wp-plugin-sociallocker/addons/styleroller-addon/assets/img/patterns/abstract/1brickwall/1brickwall.png',
						'bottom_background_image__color' => '',
						'bottom_background_image_is_active' => 0,
						'bottom_link_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_link_text__size' => '15',
						'bottom_link_text__color' => '#a2a0a0',
						'bottom_link_text_is_active' => 1,
						'bottom_link_underline' => 0,
						'bottom_link_underline_is_active' => 1,
						'bottom_timer_text__family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
						'bottom_timer_text__size' => '15',
						'bottom_timer_text__color' => '#a2a0a0',
						'bottom_timer_text_is_active' => 1,
						'bottom_container_paddings' => '10px 20px 10px 20px',
						'bottom_container_paddings_is_active' => 1,
						'theme_id' => 'input-popup',
						'style_id' => '582adb06b3d49',
					),
				), true);
			}
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

	$sociallocker->registerActivation('SocialLocker_Activation');