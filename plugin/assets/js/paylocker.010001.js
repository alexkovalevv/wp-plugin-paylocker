/*!
 * Panda Lockers - v2.1.2, 2017-02-18 
 * for jQuery: http://onepress-media.com/plugin/social-locker-for-jquery/get 
 * for Wordpress: http://onepress-media.com/plugin/social-locker-for-wordpress/get 
 * 
 * Copyright 2016, OnePress, http://byonepress.com 
 * Help Desk: http://support.onepress-media.com/ 
*/

/**
 * Текст и перевод для дополнения paylocker
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.12.2016
 * @version 1.0
 *
 */

(function($) {
	'use strict';

	var messages = {
		pl_payment_form_header: 'Оплата премиум доступа',
		pl_payment_form_newuser_description: 'На ваш email адрес выслано письмо с интсрукциями по получению доступа к вашему аккаунту. Пожалуйста, завершите оплату.',
		pl_payment_form_subscription_description: 'После оплаты ваш аккаунт перейдет в статус премиум подписчика, все замки будут сняты.',
		pl_payment_form_purchase_description: 'После оплаты статьи, замок с нее будет снят. Ссылки на приобретенные вами статьи будет в личном кабинете пользователя.',
		pl_payment_form_subscribe_target: 'Оформление премиум подписки',
		pl_payment_form_purchase_target: 'Покупка одной статьи',
		pl_payment_form_target_label: 'Назначение платежа',
		pl_payment_form_price_label: 'Сумма',
		pl_payment_form_way_label: 'Способ оплаты',
		pl_payment_form_terms: 'C <a href="{%terms_url%}" target="_blank">условиями оплаты</a> ознакомлен',
		pl_payment_form_process: 'Пожалуйста, подождите... Мы проверяем ваш платеж.',

		pl_separatorText: '----- ИЛИ -----',
		pl_confirm_email_description: 'Пожалуйста, введите свой email. Мы создадим для вас аккаунт на нашем сайте или обновим премиум доступ у ранее созданного аккаунта.',
		pl_confirm_email_text_button: 'Подтвердить',
		pl_table_not_found: 'Вы не создали еще не одного тарифа. Пожалуйста, настройте тарифы в панели управления.',
		pl_ctable_header: 'Название тарифа',
		pl_ctable_price: 'Цена не установлена',
		pl_ctable_discount: 'СКИДКА {%discount%}',
		pl_ctable_description: 'Текст перед кнопкой',
		pl_ctable_button_text: 'Оформить подписку',
		pl_ctable_after_button_text: 'Текст после кнопки',
		// prompt reminde subscribe
		pl_prompt_reminde_subscribe_message: 'Вы начали, но не завершили подписку на премием доступ к контенту сайта!<br> Желаете перейти к оплате или отменить подписку?',
		pl_prompt_reminde_subscribe_button_yes: 'Перейти к оплате',
		pl_prompt_reminde_subscribe_button_no: 'Отменить подписку',
		// prompt email not exists
		pl_promt_email_not_exists: '[email] не зарегистрирован или вы ввели его с ошибкой. Создать новый аккаунт с этим email адресом и привязать к нему вашу покупку?',
		pl_promt_email_not_button_yes: 'Да, создать новый аккаунт',
		pl_promt_email_not_button_no: 'Отмена',

		pl_errors: {
			tarif_not_found: 'Тариф [{tarif_name}] не настроен.',
			payment_gateway_not_found: 'Платежный путь [{payment_gateway}] не существует.'
		}
	};

	$.pandalocker.lang = $.extend(true, messages, $.pandalocker.lang);

})(jQuery);
;
(function($) {
	'use strict';

	var group = $.pandalocker.tools.extend($.pandalocker.entity.group);

	/**
	 * Default options.
	 */
	group._defaults = {

		// an order of the buttons
		order: ["tables"],

		text: $.pandalocker.lang.subscription.defaultText

	};

	/**
	 * The name of the group.
	 */
	group.name = "pricing-tables";

	$.pandalocker.groups["pricing-tables"] = group;

})(jQuery);;
/**
 * Таблицы тарифов
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.12.2016
 * @version 1.0
 */

(function($) {
	'use strict';

	if( !$.pandalocker.controls["pricing-tables"] ) {
		$.pandalocker.controls["pricing-tables"] = {};
	}

	var table = $.pandalocker.tools.extend($.pandalocker.entity.actionControl);

	table.name = "tables";

	table.defaults = {
		ajaxUrl: null,
		paymentType: 'subscribe',
		paymentWay: 'screen',
		paymentRedirectUrl: null,
		paymentForms: {
			yandex: {}
		}
	};

	table.prepareOptions = function() {
		this.options = $.extend(true, this.defaults, this.options, this.locker.options.paylocker);

		this.redirectUrl = this.options.paymentRedirectUrl;
	};

	table.paymentProcess = function(transactionId, tableName, newUser) {
		if( !tableName || !transactionId ) {
			throw new Error('Не передан обязательный параметр tableName или transactionId');
		}

		var paymentFormOption = this.options.paymentForms.yandex;

		if( newUser ) {
			paymentFormOption.description = $.pandalocker.lang.pl_payment_form_newuser_description;
		} else if( this.options.paymentType == 'subscribe' ) {
			paymentFormOption.description = $.pandalocker.lang.pl_payment_form_subscription_description;
		} else if( this.options.paymentType == 'purchase' ) {
			paymentFormOption.description = $.pandalocker.lang.pl_payment_form_purchase_description;
		}
		paymentFormOption.targets = $.pandalocker.lang.pl_payment_form_purchase_target;

		if( this.options.paymentType == 'subscribe' ) {
			paymentFormOption.targets = $.pandalocker.lang.pl_payment_form_subscribe_target;
		}

		if( this.options[tableName].header ) {
			paymentFormOption.targets += ' «' + this.options[tableName].header + '»';
		}

		paymentFormOption.label = transactionId;
		paymentFormOption.sum = this.options[tableName].price;

		this.locker._showScreen('paylocker-yandex-form', paymentFormOption);

	};

	table.showScreenEmail = function(tableName) {
		var self = this;

		self.locker._showScreen('enter-email', {
			header: '',
			message: $.pandalocker.lang.pl_confirm_email_description,
			buttonTitle: $.pandalocker.lang.pl_confirm_email_text_button,
			callback: function(email) {
				$.pandalocker.tools.setStorage('opanda_email', email, 30);

				self.beginTransaction(tableName, {
					email: email
				}, false);
			}
		});

		var oldEmail = $.pandalocker.tools.getFromStorage('opanda_email'),
			emailForm = $('#onp-sl-input-email');

		if( emailForm.val() == '' && oldEmail ) {
			emailForm.val(oldEmail);
		}
	};

	table.beginTransaction = function(tableName, args) {
		var self = this;

		var ajaxUrl = this.locker.options.ajaxUrl || this.options.ajaxUrl;

		if( !ajaxUrl || !tableName ) {
			throw new Error('Не передан обязательный параметр proxy или tableName');
		}

		self.locker._showScreen('data-processing');

		var lockerOptions = window.bizpanda && window.bizpanda.lockerOptions[this.locker.options.id]
			? window.bizpanda.lockerOptions[this.locker.options.id]
			: {};

		if( !lockerOptions ) {
			self.locker._showScreen('paylocker-error');
			console && console.log('[Error]: Не переданы настройки замка');
		}

		var sendData = $.extend(true, {
			action: 'onp_pl_begin_transaction',
			locker_id: lockerOptions.lockerId,
			post_id: lockerOptions.postId,
			table_payment_type: this.options[tableName].paymentType,
			table_name: tableName,
			table_price: this.options[tableName].price,
			force_register_user: false
		}, args);

		var transaction = $.pandalocker.tools.getFromStorage('onp_pl_begin_transaction');

		if( transaction && transaction.table_name == sendData.table_name && transaction.locker_id == sendData.locker_id ) {
			sendData.transaction_id = transaction.transaction_id;
		}

		$.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxUrl,
			data: sendData,
			success: function(data) {

				console.log(data);

				if( !data || data && data.error ) {
					self.locker._showScreen('paylocker-error', {
						errorMessage: data.error
					});
					return;
				}

				if( data.warning && data.code == 'entry_email' ) {
					self.showScreenEmail(tableName);
					return;
				}

				if( data.warning && data.code == 'email_not_exists' ) {
					self.locker._showScreen('prompt', {
						textMessage: $.pandalocker.lang.pl_promt_email_not_exists,
						textButtonYes: $.pandalocker.lang.pl_promt_email_not_button_yes,
						textButtonNo: $.pandalocker.lang.pl_promt_email_not_button_no,
						callbackButtonYes: function() {
							sendData['force_register_user'] = true;
							self.beginTransaction(tableName, sendData);
						},
						callbackButtonNo: function() {
							self.showScreenEmail(tableName);

						}
					});
					return;
				}

				if( data.transaction_id ) {
					$.pandalocker.tools.setStorage('onp_pl_begin_transaction', {
						transaction_id: data.transaction_id,
						table_name: tableName,
						locker_id: sendData.locker_id
					}, 1);

					self.paymentProcess(data.transaction_id, tableName, data.newUser);
				}

			},

			error: function(response, type, errorThrown) {
				if( response && response.readyState < 4 ) {
					return;
				}

				self.locker._showScreen('paylocker-error');

				if( !console || !console.log ) {
					return;
				}
				console.log('Invalide ajax response:');
				console.log(response.responseText);
			}
		});
	};

	table.createTable = function(tableName, options) {
		var self = this;

		//this.locker._showScreen('paylocker-error');

		var ctableHeader = options.header || $.pandalocker.lang.pl_ctable_header,
			ctablePrice = options.price || $.pandalocker.lang.pl_ctable_price,
			ctableBefore_button_text = options.description || $.pandalocker.lang.pl_ctable_description,
			ctableButton_text = options.buttonText || $.pandalocker.lang.pl_ctable_button_text,
			ctableAfter_button_text = options.afterButtonText || $.pandalocker.lang.pl_ctable_after_button_text;

		var controlTable = $('<div class="onp-pl-control-table"></div>'),
			tableHeader = $('<h3 class="onp-pl-ctable-header ' + options.paymentType + '">' + ctableHeader + '</h3>'),
			tablePrice = $('<div class="onp-pl-ctable-price">' + ctablePrice + '<span class="onp-pl-ctable-currency">р.</span></div>'),
			beforeButtonText = $('<div class="onp-pl-ctable-before-button-text">' + ctableBefore_button_text + '</div>'),
			tableButton = $('<button class="onp-pl-ctable-button ' + options.paymentType + '">' + ctableButton_text + '</button>'),
			afterButtonText = $('<div class="onp-pl-ctable-after-button-text">' + ctableAfter_button_text + '</div>');

		controlTable.append(tableHeader)
			.append(tablePrice);

		controlTable.append(beforeButtonText)
			.append(tableButton)
			.append(afterButtonText);

		tableButton.click(function() {
			self.beginTransaction(tableName);

			var lockerContanier = $(this).closest('.onp-sl-paylocker');
			$('html, body').animate({
				scrollTop: lockerContanier.offset().top - 100
			}, 400);
		});

		return controlTable;
	};

	table.createSeparator = function() {
		var separator = $('<div class="onp-pl-control-separator">--- ИЛИ ---</div>');
		return separator;
	};

	table.render = function($holder) {
		var self = this;

		//this.targetsReminder();

		if( !this.groupOptions.orderTables.length ) {
			$holder.append('<div class="onp-pl-tables-not-found" style="color: #ff3100;font-weight: bold; text-align: center;">' +
			$.pandalocker.lang.pl_table_not_found +
			'</div>');
			return;
		}

		var wrapTables = $('<div class="onp-pl-tables-contanier"></div>');

		for( var i = 0; i < this.groupOptions.orderTables.length; i++ ) {
			var tableName = this.groupOptions.orderTables[i];

			if( !this.options[tableName] ) {
				this.showError('pricing-table', $.pandalocker.lang.pl_errors.tarif_not_found.replace('{tarif_name}', tableName));
				return;
			}

			if( this.options[tableName].itemType == 'separator' ) {
				var separator = table.createSeparator();
				wrapTables.append(separator);
			} else {
				var control = this.createTable(tableName, this.options[tableName]);
				wrapTables.append(control);
			}

		}

		$holder.append(wrapTables);

	};

	table.targetsReminder = function() {
		var self = this;
		var transaction = $.pandalocker.tools.getFromStorage('onp_pl_begin_transaction');
		if( transaction ) {
			this.locker._showScreen('prompt', {

				textMessage: $.pandalocker.lang.pl_prompt_reminde_subscribe_message,
				textButtonYes: $.pandalocker.lang.pl_prompt_reminde_subscribe_button_yes,
				textButtonNo: $.pandalocker.lang.pl_prompt_reminde_subscribe_button_no,

				callbackButtonYes: function() {
					self.paymentProcess(transaction.transaction_id, transaction.table_name);
					return false;
				},
				callbackButtonNo: function() {
					self.locker._showScreen('default');
					$.pandalocker.tools.removeStorage('onp_pl_begin_transaction');
				}
			});
			//$('.onp-sl-screen-default', this.locker.locker).show();
		}
	};

	$.pandalocker.controls["pricing-tables"]["tables"] = table;
})(jQuery);
;
/**
 * Аддон для создания платного контента
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 10.12.2016
 * @version 1.0
 */


(function($) {
	'use strict';

	if( !$.pandalocker.themes ) {
		$.pandalocker.themes = {};
	}

	// Theme: facebook popup

	$.pandalocker.themes['paylocker'] = {
		socialButtons: {
			layout: 'horizontal',
			counter: false,
			flip: false
		}
	};

	$.pandalocker.themes['blueberry'] = {
		socialButtons: {
			layout: 'horizontal',
			counter: false,
			flip: false
		}
	};

	$.pandalocker.hooks.add('opanda-filter-options', function(options, locker) {

		//console.log(options);

		if( options.paylocker ) {
			options.locker.close = false;
			options.locker.timer = 0;

			options.theme = {
				name: 'blueberry'
			};
		}

		return options;
	});

	// Добавляем ссылку на страницу помощь
	$.pandalocker.hooks.add('opanda-lock', function(e, locker) {
		if( locker.options.paylocker && locker.options.paylocker.helpUrl ) {
			$(locker.locker).prepend('<a class="onp-pl-help-link" href="' + locker.options.paylocker.helpUrl + '" target="_blank">Помощь</a>');
		}

		$(locker.locker).append('<div class="onp-pl-bottom-panel">' +
			'<a href="" class="onp-pl-login-link">Уже подписаны? Тогда войдите</a>' +
			'<a href="mailto:" class="onp-pl-mailto-link">Остались вопросы? Напишите нам.</a>' +
			'<div style="clear: both;"></div>' +
			'</div>'
		);
	});

	// Регистрируем экран с формой оплаты от яндекса
	$.pandalocker.hooks.add('opanda-init', function(e, locker) {

		locker._registerScreen('paylocker-success-payment', function($holder, options) {
			var screenHeaderEl = $('<div class="onp-pl-screen-header">Оплата успешно завершена!</div>');
			$holder.append(screenHeaderEl);
			var loginUrl = __paylocker && __paylocker.loginUrl;
			var screenTextEl = $('<div class="onp-pl-screen-text">Вам остался один шаг, <a href="' + loginUrl + '">авторизуйтесь</a> на нашем сайте, чтобы открыть замки. </div>');
			$holder.append(screenTextEl);
		});

		locker._registerScreen('paylocker-error', function($holder, options) {
			if( options && options.header ) {
				var screenHeaderEl = $('<div class="onp-pl-screen-header">' + options.header + '</div>');
				$holder.append(screenHeaderEl);
			}

			var screenText = (options && options.errorMessage) || 'Произошла неивестная ошибка во время выполнения запроса. Пожалуйста, свяжитесь с нашей <a href="#">службой поддеддержки</a>, чтобы решить эту проблему.',
				screenTextEl = $('<div class="onp-pl-screen-text">' + screenText + '</div>');

			$holder.append(screenTextEl);
		});

		// SCREEN: payment form
		locker._registerScreen('paylocker-yandex-form',
			function($holder, options) {

				var optionsDefault = {
					termsPageUrl: '#',
					alternatePaymentTypePageUrl: '#',
					receiver: null,
					label: null,
					sum: null,
					quickpay: 'shop',
					paymentTypeChoice: true,
					writer: 'seller',
					targets: '',
					targetsHint: '',
					buttonText: '01',
					successURL: ''
				};

				options = $.extend(true, optionsDefault, options);

				var iframeUrl = 'https://money.yandex.ru/quickpay/shop-widget';

				// todo: Доработать вывод ошибок

				if( !options.receiver || options.receiver == '' ) {
					setTimeout(function() {
						locker._showScreen('paylocker-error', {
							errorMessage: 'Не передан account id яндекс денег.'
						});
					}, 500);
					return;
				}

				if( !options.sum || options.sum == '' ) {
					setTimeout(function() {
						locker._showScreen('paylocker-error', {
							errorMessage: 'Не установлена сумма платежа.'
						});
					}, 500);
					return;
				}

				var screenHeaderText = options.header || $.pandalocker.lang.pl_payment_form_header,
					screenDescriptionText = options.description || $.pandalocker.lang.pl_payment_form_subscription_description;

				var screenHeader = '<h3 class="onp-pm-screen-header">' + screenHeaderText + '</h3>',
					screenDescription = '<div class="onp-pm-screen-text">' + screenDescriptionText + '</div>',
					formWrap = $('<div class="onp-pm-yandex-payment-form"></div>');

				var alternatePaymentTypeLink = options.alternatePaymentTypePageUrl
					? '<tr><td></td><td><a href="' + options.alternatePaymentTypePageUrl + '" class="onp-pm-pform-alternate-payment-type-link" target="_blank">Не подходит способ оплаты?</a></td></tr>'
					: '';

				var termsLink = $.pandalocker.lang.pl_payment_form_terms.replace("{%terms_url%}", options.termsPageUrl);

				var newPaymentForm = '<form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml" target="_blank">' +
					'<table class="onp-pm-pform-table"><tbody>' +
					'<tr><td>' + $.pandalocker.lang.pl_payment_form_target_label + ':</td><td><strong>' + options.targets + '</strong></td></tr>' +
					'<tr><td>' + $.pandalocker.lang.pl_payment_form_price_label + ':</td><td><strong>' + options.sum + ' руб.</strong></td></tr>' +
					'<tr><td>' + $.pandalocker.lang.pl_payment_form_way_label + ':</td><td>' +
					'<label><select name="paymentType">' +
					'<option value="AC" selected>Банковской картой</option>' +
					'<option value="PC">Яндекс.Деньгами</option>' +
					'</select></label>' +
					'</td></tr>' + alternatePaymentTypeLink +
					'</tbody></table>' +
					'<div class="onp-pm-pform-bottom">' +
					'<div class="onp-pm-pform-bottom-left-side"><label><input type="checkbox" class="onp-pm-payment-terms-checkbox" checked> ' + termsLink + ' </label></div>' +
					'<div class="onp-pm-pform-bottom-right-side"><input type="submit" class="onp-pm-payment-button" value="Перейти к оплате"></div>' +
					'</div>' +
					'<input type="hidden" name="receiver" value="' + options.receiver + '">		' +
					'<input type="hidden" name="label" value="' + options.label + '">' +
					'<input type="hidden" name="quickpay-form" value="' + options.quickpay + '">' +
					'<input type="hidden" name="targets" value="' + options.targets + '">' +
					'<input type="hidden" name="sum" value="' + options.sum + '" data-type="number">' +
					'</form>';

				if( screenHeaderText ) {
					$holder.append(screenHeader);
				}

				if( screenDescriptionText ) {
					$holder.append(screenDescription);
				}

				$holder.append(formWrap.append(newPaymentForm));

				var termsCheckbox = $('.onp-pm-payment-terms-checkbox', $holder),
					paymentButton = $('.onp-pm-payment-button', $holder);

				termsCheckbox.change(function() {
					if( $(this).is(':checked') ) {
						paymentButton.removeClass('disabled');
					} else {
						paymentButton.addClass('disabled');
					}
				});

				paymentButton.click(function() {
					if( $(this).hasClass('disabled') ) {
						return false;
					}

					locker._showScreen('data-processing', {
						screenText: $.pandalocker.lang.pl_payment_form_process
					});

					var ajaxUrl = locker.options.paylocker && locker.options.paylocker.ajaxUrl;

					if( !ajaxUrl ) {
						return;
					}

					var pullTimer = setInterval(function() {
						$.ajax({
							url: ajaxUrl,
							type: 'post',
							dataType: 'json',
							data: {
								action: 'onp_pl_check_transaction',
								transactionId: options.label
							},
							success: function(data, textStatus, jqXHR) {

								if( !data || data.error || data.transaction_status == 'cancel' ) {
									clearInterval(pullTimer);
									locker._showScreen('paylocker-error', {
										errorMessage: data.error
									});
								}

								if( data.transaction_status == 'finish' ) {
									clearInterval(pullTimer);
									locker._showScreen('paylocker-success-payment');
								}
							}
						});
					}, 10000);
				});
			}
		);

	});
})(jQuery);
