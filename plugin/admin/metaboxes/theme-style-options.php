<?php
	/**
	 * Добавляет метабокс с настройками стилей для темы
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 15.12.2016
	 * @version 1.0
	 */

	/**
	 * The class configure the metabox Social Options.
	 *
	 * @since 1.0.0
	 */
	class Opanda_ThemeStyleMetabox extends FactoryMetaboxes000_FormMetabox {

		/**
		 * A visible title of the metabox.
		 *
		 * Inherited from the class FactoryMetabox.
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $title;

		/**
		 * A prefix that will be used for names of input fields in the form.
		 *
		 * Inherited from the class FactoryFormMetabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $scope = 'opanda';

		/**
		 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 * Inherited from the class FactoryMetabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $priority = 'core';

		public $cssClass = 'factory-bootstrap-000 factory-fontawesome-000';

		public function __construct($plugin)
		{
			parent::__construct($plugin);

			$this->title = __('Настройка стиля для темы', 'bizpanda');
		}

		/**
		 * Configures a metabox.
		 */
		public function configure($scripts, $styles)
		{
			$this->styles->add(BIZPANDA_PAYLOCKER_URL . '/admin/assets/css/metaboxes.010000.css');
			$this->scripts->request(array(
				'control.color',
				'control.color-and-opacity',
				'plugin.iris',
				'plugin.color',
				'control.fonts',
				'plugin.chosen'
			), 'bootstrap');

			$this->styles->request(array(
				'control.color',
				'control.color-and-opacity',
				'control.fonts',
				'plugin.chosen'
			), 'bootstrap');
		}

		/**
		 * Includes scripts and styles for a metabox.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includeScriptsAndStyles()
		{
			global $post;

			if( !in_array($post->post_type, $this->postTypes) ) {
				return;
			}

			if( !$this->scripts->isEmpty('bootstrap') || !$this->styles->isEmpty('bootstrap') ) {
				add_action('factory_bootstrap_enqueue_scripts_' . $this->plugin->pluginName, array(
					$this,
					'actionAdminBootstrapScripts'
				));
			}

			if( $this->scripts->isEmpty() && $this->styles->isEmpty() ) {
				return;
			}

			$this->scripts->connect();
			$this->styles->connect();
		}

		/**
		 * Actions that includes registered fot this type scritps and styles.
		 * @global type $post
		 * @param type $hook
		 */
		public function actionAdminBootstrapScripts()
		{
			$this->scripts->connect('bootstrap');
			$this->styles->connect('bootstrap');
		}


		/**
		 * Configures a form that will be inside the metabox.
		 *
		 * @see FactoryMetaboxes000_FormMetabox
		 * @since 1.0.0
		 *
		 * @param FactoryForms000_Form $form A form object to configure.
		 * @return void
		 */
		public function form($form)
		{

			$tabs = array(
				'type' => 'tab',
				'align' => 'vertical',
				'class' => 'theme-style-tab',
				'items' => array()
			);

			$tabs['items'][] = array(
				'type' => 'tab-item',
				'name' => 'generals',
				'title' => __('Основные', 'bizpanda'),
				'items' => array(
					array(
						'type' => 'color',
						'name' => 'theme_bg_color',
						'default' => '#75649b',
						'title' => __('Цвет фона', 'bizpanda'),
						'hint' => __('Выберите цвет фона контейнера замка.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_text_style',
						'title' => __('Настройки текста', 'bizpanda'),
						'default' => array(
							'size' => '15',
							'color' => "#ffffff",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
				)
			);

			$tabs['items'][] = array(
				'type' => 'tab-item',
				'name' => 'theme_header',
				'title' => __('Верх замка', 'bizpanda'),
				'items' => array(
					array(
						'type' => 'color',
						'name' => 'theme_header_bg',
						'title' => __('Цвет фона заголовка', 'bizpanda'),
						'default' => '#3c2e4f',
						'hint' => __('Выберите цвет фона заголовка замка.', 'bizpanda'),
					),
					array(
						'type' => 'color',
						'name' => 'theme_header_border_color',
						'title' => __('Цвет границы', 'bizpanda'),
						'default' => '#d6bef7',
						'hint' => __('Выберите цвет нижней границы заголовка.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_header_text_style',
						'title' => __('Настройки текста', 'bizpanda'),
						'default' => array(
							'size' => '16',
							'color' => "#d6bef7",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
				)
			);

			$tabs['items'][] = array(
				'type' => 'tab-item',
				'name' => 'theme_table_purchase',
				'title' => __('Таблица покупки', 'bizpanda'),
				'items' => array(
					array(
						'type' => 'color',
						'name' => 'theme_purchase_table_header_bg',
						'default' => '#ffc107',
						'title' => __('Цвет заголовка', 'bizpanda'),
						'hint' => __('Выберите основной цвет таблицы.', 'bizpanda'),
					),
					array(
						'type' => 'color',
						'name' => 'theme_purchase_table_bg',
						'title' => __('Цвет фона', 'bizpanda'),
						'default' => '#fff',
						'hint' => __('Выберите цвет фона таблицы.', 'bizpanda'),
					),
					array(
						'type' => 'color',
						'name' => 'theme_purchase_table_button_bg',
						'title' => __('Цвет кнопки', 'bizpanda'),
						'default' => '#ffc107',
						'hint' => __('Выберите цвет фона кнопки.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_purchase_table_text_header',
						'title' => __('Настройки текста "Заголовок"', 'bizpanda'),
						'default' => array(
							'size' => '15',
							'color' => "#222",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_purchase_table_text_price',
						'title' => __('Настройки текста "Цена"', 'bizpanda'),
						'default' => array(
							'size' => '25',
							'color' => "#3c2e4f",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_purchase_table_text_description',
						'title' => __('Настройки текста "Описание"', 'bizpanda'),
						'default' => array(
							'size' => '13',
							'color' => "#111",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_purchase_table_text_button',
						'title' => __('Настройки текста кнопки', 'bizpanda'),
						'default' => array(
							'size' => '11',
							'color' => "#222",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
				)
			);

			$tabs['items'][] = array(
				'type' => 'tab-item',
				'name' => 'theme_table_subscribe',
				'title' => __('Таблица подписки', 'bizpanda'),
				'items' => array(
					array(
						'type' => 'color',
						'name' => 'theme_subscribe_table_header_bg',
						'default' => '#d6bef7',
						'title' => __('Цвет заголовка', 'bizpanda'),
						'hint' => __('Выберите основной цвет таблицы.', 'bizpanda'),
					),
					array(
						'type' => 'color',
						'name' => 'theme_subscribe_table_bg',
						'title' => __('Цвет фона', 'bizpanda'),
						'default' => '#fff',
						'hint' => __('Выберите цвет фона таблицы.', 'bizpanda'),
					),
					array(
						'type' => 'color',
						'name' => 'theme_subscribe_button_bg',
						'title' => __('Цвет кнопки', 'bizpanda'),
						'default' => '#3c2e4f',
						'hint' => __('Выберите цвет фона кнопки.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_subscribe_table_text_header',
						'title' => __('Настройки текста "Заголовок"', 'bizpanda'),
						'default' => array(
							'size' => '23',
							'color' => "#222",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_subscribe_table_text_price',
						'title' => __('Настройки текста "Цена"', 'bizpanda'),
						'default' => array(
							'size' => '25',
							'color' => "#3c2e4f",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_subscribe_table_text_description',
						'title' => __('Настройки текста "Описание"', 'bizpanda'),
						'default' => array(
							'size' => '13',
							'color' => "#111",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_subscribe_table_text_button',
						'title' => __('Настройки текста кнопки', 'bizpanda'),
						'default' => array(
							'size' => '11',
							'color' => "#222",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
				)
			);
			$tabs['items'][] = array(
				'type' => 'tab-item',
				'name' => 'theme_footer',
				'title' => __('Низ замка', 'bizpanda'),
				'items' => array(
					array(
						'type' => 'color',
						'name' => 'theme_footer_bg',
						'title' => __('Цвет кнопки', 'bizpanda'),
						'default' => '#3c2e4f',
						'hint' => __('Выберите цвет фона кнопки.', 'bizpanda'),
					),
					array(
						'type' => 'font',
						'name' => 'theme_footer_text',
						'title' => __('Настройки текста кнопки', 'bizpanda'),
						'default' => array(
							'size' => '11',
							'color' => "#222",
							'family' => '"Segoe UI", Frutiger, "Frutiger Linotype", "Dejavu Sans", "Helvetica Neue", Arial, sans-serif'
						),
						'hint' => __('Вы можете изменить размер текста, выбрать шрифт и цвет.', 'bizpanda'),
					),
				)
			);

			$form->add(array(
				$tabs
			));
		}
	}

	global $paylocker;

	FactoryMetaboxes000::register('Opanda_ThemeStyleMetabox', $paylocker);
	/*@mix:place*/
