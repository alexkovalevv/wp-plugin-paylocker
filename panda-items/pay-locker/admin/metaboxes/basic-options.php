<?php
	/**
	 * Добавляем поля в базовый блок
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 06.02.2017
	 * @version 1.0
	 */

	function onp_pl_add_new_basic_options($options)
	{

		$options[] = array(
			'type' => 'textbox',
			'name' => 'locker_help_url',
			'title' => __('Ссылка на страницу справки', 'bizpanda'),
			'hint' => __('Если установлен адрес ссылки на страницу справки, то в углу замка появляется ссылка "Помощь" ведущая на установенный адрес.', 'bizpanda'),
		);

		return $options;
	}

	add_filter('opanda_text_options', 'onp_pl_add_new_basic_options');