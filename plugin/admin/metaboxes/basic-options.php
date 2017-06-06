<?php
	/**
	 * Добавляем поля в базовый блок
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 06.02.2017
	 * @version 1.0
	 */

	function onp_pl_add_new_basic_options_text_section($options)
	{
		$options[] = array(
			'type' => 'textbox',
			'name' => 'locker_help_url',
			'title' => __('Ссылка на страницу справки', 'bizpanda'),
			'hint' => __('Если установлен адрес ссылки на страницу справки, то в углу замка появляется ссылка "Помощь" ведущая на установенный адрес.', 'bizpanda'),
		);

		return $options;
	}

	add_filter('opanda_basic_options_text_section', 'onp_pl_add_new_basic_options_text_section');

	function onp_pl_add_new_basic_options_style_section($options)
	{

		foreach($options as $key => $option) {
			if( $option['name'] == 'style' ) {
				unset($options[$key]);
			}
		}

		require_once OPANDA_BIZPANDA_DIR . '/includes/themes.php';

		$options[] = array(
			'type' => 'dropdown-and-colors',
			'hasHints' => true,
			'name' => 'style',
			'dropdown' => array(
				'data' => OPanda_ThemeManager::getThemes(OPanda_Items::getCurrentItemName(), 'dropdown'),
				'default' => 'default'
			),
			'colors' => array(
				/*'data' => array(
					array('default', '#75649b'),
					array('black', '#222'),
					array('light', '#fff3ce'),
					array('forest', '#c9d4be'),
				),*/
				'default' => 'default',
			),
			'title' => __('Theme', 'bizpanda'),
			'hint' => __('Select the most suitable theme.', 'bizpanda'),

		);

		return $options;
	}

	add_filter('opanda_basic_options_style_section', 'onp_pl_add_new_basic_options_style_section');

	/**
	 * @param Factory000_AssetsList $scripts
	 * @param Factory000_AssetsList $styles
	 */
	function onp_pl_add_scripts_to_page_edit_item($scripts, $styles)
	{
		$scripts->request(array(
			'control.dropdown-and-colors'
		), 'bootstrap');

		$styles->request(array(
			'control.dropdown',
			'control.radio-colors',
			'control.dropdown-and-colors'
		), 'bootstrap');
	}

	add_action('opanda_panda-item_edit_assets', 'onp_pl_add_scripts_to_page_edit_item', 10, 2);