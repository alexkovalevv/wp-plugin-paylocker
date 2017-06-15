<?php
	/**
	 * Страница оформления подписки
	 *
	 * @author Alex Kovalev <alex.kovalevv@gmail.com>
	 * @copyright Alex Kovalev 25.12.2016
	 * @version 1.0
	 */

	/**
	 * Common Settings
	 */
	class OnpPl_BeginSubscribePage extends OPanda_AdminPage {

		public function __construct($plugin)
		{
			$this->menuTarget = 'client_premium_subscribers-paylocker';

			if( current_user_can('administrator') ) {
				$this->capabilitiy = "paylocker_view_page_begin_subscribe";
			} else {
				$this->capabilitiy = "read";
			}

			$this->id = "begin_subscribe";

			$this->menuTitle = __('Оформить подписку', 'plugin-paylocker');

			parent::__construct($plugin);
		}

		public function assets($scripts, $styles)
		{

			$this->scripts->request('jquery');

			$this->scripts->request(array(
				'control.checkbox',
				'control.dropdown',
				'plugin.ddslick',
				'holder.more-link'
			), 'bootstrap');

			$this->styles->request(array(
				'bootstrap.core',
				'bootstrap.form-group',
				'bootstrap.separator',
				'control.dropdown',
				'control.checkbox',
				'holder.more-link'
			), 'bootstrap');

			$this->styles->add(PAYLOCKER_URL . '/plugin/admin/assets/css/page.begin-subscribe.010000.css');

			if( !isset($_GET['payment_proccess']) ) {
				$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/load-tables-data.010000.js');
				$this->scripts->add(PAYLOCKER_URL . '/plugin/admin/assets/js/page.begin-subscribe.010000.js');
			}
		}

		public function indexAction()
		{
			global $bizpanda, $paylocker;

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'subscribe_locker',
				'title' => __('Выберите подписку', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужную вам подписку.', 'plugin-paylocker'),
				'cssClass' => isset($_GET['locker_id'])
					? 'onp-pl-hide-control'
					: '',
				'value' => isset($_GET['locker_id'])
					? $_GET['locker_id']
					: null,
				'data' => array($this, 'getLockers')
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'table_name',
				'title' => __('Выберите тариф', 'plugin-paylocker'),
				'hint' => __('Выберите из списка нужный вам тариф.', 'plugin-paylocker'),
				'data' => array()
			);

			$options[] = array(
				'type' => 'separator'
			);

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'paymentType',
				'title' => __('Выберите способ оплаты', 'plugin-paylocker'),
				'hint' => __('Выберите способ оплаты подписки.', 'plugin-paylocker'),
				'data' => array(
					array('AC', __('Банковские карты', 'plugin-paylocker')),
					array('PC', __('Яндекс деньги', 'plugin-paylocker'))
				)
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'receiver',
				'value' => get_option('opanda_pl_payment_form_receiver')
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'targets',
				'value' => get_option('opanda_res_pl_payment_form_target_subscribe', __('Оплата премиум подписки {order_id}', 'plugin-paylocker'))
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'label',
				'value' => ''
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'quickpay-form',
				'value' => 'shop'
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'sum',
				'value' => 0
			);
			$options[] = array(
				'type' => 'hidden',
				'name' => 'successURL',
				'value' => admin_url('admin.php?page=begin_subscribe-' . $paylocker->pluginName . '&payment_proccess=waiting')
			);

			$options[] = array(
				'type' => 'html',
				'html' => '<div class="form-group">
					        	<label class="col-sm-2 control-label">К оплате:</label>
					        	<div class="control-group col-sm-10"><string style="font-size: 25px;">
					        	<span class="onp-pl-table-price-text">0</span> руб.</string></div>
					        </div>'
			);

			// creating a form

			$form = new FactoryForms000_Form(array(
				'scope' => '',
				'name' => 'begin-subscribe'
			), $bizpanda);

			$form->setProvider(new FactoryForms000_OptionsValueProvider(array(
				'scope' => 'paylocker'
			)));

			$form->add($options);
			?>

			<script>
				if( window.__paylocker === void 0 ) {
					window.__paylocker = {};
				}
				__paylocker.lang_interface = {
					loading: '<?php _e('Загрузка', 'plugin-paylocker'); ?>',
					redirect: '<?php _e('Идет перенаправление...', 'plugin-paylocker'); ?>'
				};
			</script>

			<div class="wrap" id="onp-pl-begin-subscribe-page">
				<div class="factory-bootstrap-000">
					<script>
						window.beginSubscribePageUrl = '<?= admin_url('admin.php?page=begin_subscribe-paylocker'); ?>';
					</script>
					<?php if( !isset($_GET['payment_proccess']) ): ?>
						<h2>
							<?php _e('Оформление премиум подписки', 'plugin-paylocker') ?>
						</h2>

						<p><?php _e('На этой странице вы можете посмотреть список всех пользователей, которые имеют премиум подписку.'); ?></p>

						<form method="POST" id="onp-pl-payment-form" class="form-horizontal" action="https://money.yandex.ru/quickpay/confirm.xml">
							<?php if( isset($_GET['opanda_saved']) ) { ?>
								<div id="message" class="alert alert-success">
									<p><?php _e('Настройки успешно сохранены!', 'plugin-paylocker') ?></p>
								</div>
							<?php } ?>

							<?php if( isset($_GET['opanda_error_code']) && $_GET['opanda_error_code'] == 'sociallocker_is_not_selected' ) { ?>
								<div id="message" class="alert alert-danger">
									<p><?php _e('Возникла ошибка при сохранении данных! Вы должны выбрать (или создать) хотя бы один социальный замок, чтобы запустить процесс массовой блокировки.', 'plugin-paylocker') ?></p>
								</div>
							<?php } ?>

							<div style="padding-top: 10px;">
								<?php $form->html(); ?>
							</div>
							<div class="form-group form-horizontal">
								<label class="col-sm-2 control-label"> </label>

								<div class="control-group controls col-sm-10">
									<input id="onp-pl-start-payment-button" class="btn btn-primary" type="submit" value="<?php _e('Оплатить подписку', 'plugin-paylocker') ?>"/>
								</div>
							</div>
						</form>
					<?php else: ?>
						<div class="onp-pl-payment-proccess">
							<?php if( $_GET['payment_proccess'] == 'waiting' && isset($_GET['transaction_id']) && !empty($_GET['transaction_id']) ): ?>
								<script>
									(function($) {
										$(function() {
											setInterval(function() {
												$.ajax({
													url: '<?= admin_url('admin-ajax.php'); ?>',
													type: 'post',
													dataType: 'json',
													data: {
														action: 'onp_pl_check_transaction',
														transactionId: '<?= esc_html($_GET['transaction_id']); ?>'
													},
													success: function(data, textStatus, jqXHR) {
														console.log(data);

														if( !data || data.error || data.transaction_status == 'cancel' ) {
															window.location.href = window.beginSubscribePageUrl + '&payment_proccess=error' + (data.error_code
																? '&error_code=' + data.error_code
																: '');
														}

														if( data.transaction_status == 'finish' ) {
															window.location.href = window.beginSubscribePageUrl + '&payment_proccess=success';
														}
													}
												});
											}, 10000);
										});
									})(jQuery);
								</script>
								<div class="onp-pl-flat-box onp-pl-loader"><?php _e('Пожалуйста, подождите! Идет обработка
									платежа...', 'plugin-paylocker') ?>
								</div>
							<?php elseif ($_GET['payment_proccess'] == 'success'): ?>
								<div class="onp-pl-flat-box onp-pl-success"><?php _e('Спасибо, за оформление подписки. Ваш платеж
									успешно	завершен!', 'plugin-paylocker') ?><br/>
									<a href="<?= admin_url('admin.php?page=client_premium_subscribers-paylocker'); ?>"><?php _e('Перейти в мои подписки', 'plugin-paylocker') ?></a>.
								</div>
							<?php else: ?>
								<div class="onp-pl-flat-box onp-pl-error"><?php _e('Ошибка! Возникала неизвестная ошибка во время обработки платежа или платеж был отменен. В случае ошибки платежа, пожалуйста,
									свяжитесь с нашей службой поддержки.', 'plugin-paylocker') ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php
		}

		public function getLockers()
		{
			$args = array(
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'post_type' => 'opanda-item',
				'meta_key' => 'opanda_item',
				'meta_value' => 'pay-locker'
			);

			$paylockers = get_posts($args);

			$needLockers = array();
			foreach($paylockers as $locker) {
				$needLockers[] = array($locker->ID, $locker->post_title);
			}

			return $needLockers;
		}
	}

	global $paylocker;

	FactoryPages000::register($paylocker, 'OnpPl_BeginSubscribePage');
/*@mix:place*/