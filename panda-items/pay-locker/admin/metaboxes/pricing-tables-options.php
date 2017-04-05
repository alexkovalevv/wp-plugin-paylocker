<?php
	/**
	 * Добавляет метабокс с настройками тарифных таблиц
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 15.12.2016
	 * @version 1.0
	 */

	/**
	 * The class configure the metabox Social Options.
	 *
	 * @since 1.0.0
	 */
	class Opanda_PricingTablesMetabox extends FactoryMetaboxes000_FormMetabox {

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

			$this->title = __('Настройка тарифных таблиц', 'bizpanda');
		}

		/**
		 * Configures a metabox.
		 */
		public function configure($scripts, $styles)
		{
			$styles->add(BIZPANDA_PAYLOCKER_URL . '/admin/assets/css/pricing-tables.010000.css');
			$scripts->add(BIZPANDA_PAYLOCKER_URL . '/admin/assets/js/pricing-tables-generator.010000.js');
			$scripts->add(BIZPANDA_PAYLOCKER_URL . '/admin/assets/js/pricing-tables-options.010001.js');
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
			$form->add(array(
				array(
					'type' => 'html',
					'html' => array($this, 'pricingTableGenerator')
				)
			));
		}


		public function pricingTableGenerator()
		{
			global $post;
			$tablesData = get_post_meta($post->ID, 'opanda_pricing_tables_data', true);
			$tablesData = esc_html($tablesData);
			?>
			<div class="onp-pl-pricing-table-generator">
				<input type="hidden" name="opanda_pricing_tables_data" id="onp-pl-pricing-tables-data" value="<?= $tablesData ?>"/>

				<div class="onp-pl-pg-tables">
					<div class="onp-pl-pg-tables-item onp-pl-pg-tables-separator onp-pl-pg-tables-separator-prototype" data-item-type="separator" style="display: none;">
						<a href="#" class="btn btn-default onp-pl-pg-delete-table-button">
							<i class="fa fa-trash" aria-hidden="true"></i>
						</a>
						--- ИЛИ ---
						<div class="onp-pl-pg-move-table"></div>
					</div>
					<div class="onp-pl-pg-tables-item onp-pl-pg-tables-item-prototype" data-item-type="table" style="display: none;">
						<a href="#" class="btn btn-default onp-pl-pg-edit-table-button"><i class="fa fa-cog" aria-hidden="true"></i></a>
						<a href="#" class="btn btn-default onp-pl-pg-delete-table-button"><i class="fa fa-trash" aria-hidden="true"></i></a>

						<div class="onp-pl-pg-move-table"></div>
						<div class="onp-pl-pg-table-preview">
							<span class="onp-pl-pg-table-name"></span>
							<span class="onp-pl-pg-table-price"></span>
							<span class="onp-pl-pg-table-type"></span>
						</div>
						<table class="onp-pl-pg-table-form">
							<tbody>
							<tr>
								<td>Заголовок:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-header-control form-control" value="Новый тариф">
								</td>
							</tr>
							<tr>
								<td>Тип таблицы:</td>
								<td colspan="3">
									<select class="onp-pl-table-payment-type-control form-control">
										<option value="subscribe">Подписка</option>
										<option value="purchase">Разовая покупка</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Цена:</td>
								<td><input type="number" class="onp-pl-table-price-control form-control" value="0"></td>
								<td class="onp-pl-table-td-expired">Период (дней):</td>
								<td class="onp-pl-table-td-expired">
									<input type="number" class="onp-pl-table-expired-control form-control" value="365">
								</td>
							</tr>
							<tr>
								<td>Описание:</td>
								<td colspan="3">
									<textarea class="onp-pl-table-description-control form-control" cols="20" rows="3"></textarea>
								</td>
							</tr>
							<tr>
								<td>Текст кнопки:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-button-text-control form-control" value="Оформить подписку">
								</td>
							</tr>
							<tr>
								<td>Текст после кнопки:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-after-button-text-control form-control"></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<a class="btn btn-default onp-pl-pg-create-table-button" href="#">
					<i class="fa fa-plus"></i> Добавить таблицу
				</a>
				<a class="btn btn-default onp-pl-pg-create-separator-button" href="#">
					<i class="fa fa-plus"></i> Добавить разделитель
				</a>
			</div>
		<?php
		}

		public function onSavingForm($postId)
		{
			if( isset($_POST['opanda_pricing_tables_data']) && !empty($_POST['opanda_pricing_tables_data']) ) {
				//$tablesData = sanitize_text_field($_POST['opanda_pricing_tables_data']);
				$tablesData = wp_kses($_POST['opanda_pricing_tables_data'], array(
					'a' => array(
						'href' => true,
						'title' => true,
					),
					'p' => array(),
					'br' => array(),
					'em' => array(),
					'strong' => array(),
				));
				update_post_meta($postId, 'opanda_pricing_tables_data', $tablesData);
			}
		}
	}

	global $paylocker;

	FactoryMetaboxes000::register('Opanda_PricingTablesMetabox', $paylocker);
/*@mix:place*/
