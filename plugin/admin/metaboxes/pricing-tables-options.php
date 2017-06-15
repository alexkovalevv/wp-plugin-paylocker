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

			$this->title = __('Настройка тарифных таблиц', 'plugin-paylocker');
		}

		/**
		 * Configures a metabox.
		 */
		public function configure($scripts, $styles)
		{
			$styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/pricing-tables.010000.css');
			$scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/plugin.autoexpand.js');
			$scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/pricing-tables-generator.010000.js');
			$scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/pricing-tables-options.010001.js');
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
			$tablesDataString = json_encode($tablesData, JSON_HEX_TAG);
			$tablesDataString = htmlspecialchars($tablesDataString);
			?>
			<div class="onp-pl-pricing-table-generator">
				<input type="hidden" name="opanda_pricing_tables_data" id="onp-pl-pricing-tables-data" value="<?= $tablesDataString ?>"/>

				<div class="onp-pl-pg-tables">
					<div class="onp-pl-pg-tables-item onp-pl-pg-tables-separator onp-pl-pg-tables-separator-prototype" data-item-type="separator" style="display: none;">
						<a href="#" class="btn btn-default onp-pl-pg-delete-table-button">
							<i class="fa fa-trash" aria-hidden="true"></i>
						</a>
						<?php _e('--- ИЛИ ---', 'plugin-paylocker') ?>
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
								<td><?php _e('Заголовок', 'plugin-paylocker') ?>:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-header-control form-control" value="Новый тариф">
								</td>
							</tr>
							<tr>
								<td><?php _e('Тип таблицы', 'plugin-paylocker') ?>:</td>
								<td colspan="3">
									<select class="onp-pl-table-payment-type-control form-control">
										<option value="subscribe"><?php _e('Подписка', 'plugin-paylocker') ?></option>
										<option value="purchase"><?php _e('Разовая покупка', 'plugin-paylocker') ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Цена:</td>
								<td><input type="number" class="onp-pl-table-price-control form-control" value="0"></td>
								<td class="onp-pl-table-td-expired"><?php _e('Период (дней)', 'plugin-paylocker') ?>:
								</td>
								<td class="onp-pl-table-td-expired">
									<input type="number" class="onp-pl-table-expired-control form-control" value="365">
								</td>
							</tr>
							<tr>
								<td><?php _e('Описание', 'plugin-paylocker') ?>:</td>
								<td colspan="3">
									<textarea class="onp-pl-table-description-control form-control" cols="20"></textarea>
								</td>
							</tr>
							<tr>
								<td><?php _e('Текст кнопки', 'plugin-paylocker') ?>:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-button-text-control form-control" value="Оформить подписку">
								</td>
							</tr>
							<tr>
								<td><?php _e('Текст после кнопки', 'plugin-paylocker') ?>:</td>
								<td colspan="3">
									<input type="text" class="onp-pl-table-after-button-text-control form-control"></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<a class="btn btn-default onp-pl-pg-create-table-button" href="#">
					<i class="fa fa-plus"></i> <?php _e('Добавить таблицу', 'plugin-paylocker') ?>
				</a>
				<a class="btn btn-default onp-pl-pg-create-separator-button" href="#">
					<i class="fa fa-plus"></i> <?php _e('Добавить разделитель', 'plugin-paylocker') ?>
				</a>
			</div>
		<?php
		}

		public function onSavingForm($postId)
		{
			if( isset($_POST['opanda_pricing_tables_data']) && !empty($_POST['opanda_pricing_tables_data']) ) {

				$tablesData = json_decode(stripslashes($_POST['opanda_pricing_tables_data']), JSON_HEX_QUOT);

				if( !empty($tablesData) ) {
					foreach($tablesData as $tableName => $table) {
						$tablesData[$tableName]['header'] = wp_kses($table['header'], 'strip');
						$tablesData[$tableName]['price'] = (int)$table['price'];
						$tablesData[$tableName]['expired'] = (int)$table['expired'];
						$tablesData[$tableName]['description'] = wp_kses($table['description'], 'post');
						$tablesData[$tableName]['buttonText'] = wp_kses($table['buttonText'], 'strip');
						$tablesData[$tableName]['afterButtonText'] = wp_kses($table['afterButtonText'], 'strip');
					}
				}

				update_post_meta($postId, 'opanda_pricing_tables_data', $tablesData);
			}
		}
	}

	global $paylocker;

	FactoryMetaboxes000::register('Opanda_PricingTablesMetabox', $paylocker);
/*@mix:place*/
