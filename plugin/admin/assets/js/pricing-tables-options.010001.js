if( !window.bizpanda ) {
	window.bizpanda = {};
}
if( !window.bizpanda.pricingTablesOptions ) {
	window.bizpanda.pricingTablesOptions = {};
}

(function($) {

	window.bizpanda.pricingTablesOptions = {

		init: function() {
			var self = this;
			this.item = $('#opanda_item').val();

			$.bizpanda.filters.add('opanda-preview-options', function(options) {
				var extraOptions = self.getpricingTablesOptions();
				return $.extend(true, options, extraOptions);
			});

			$("iframe[name='preview']").load(function() {
				var iframe = $(this)[0].contentWindow;

				iframe.__$onp.pandalocker.hooks.add('opanda-paylocker-update-options', function(e, locker) {
					var extraOptions = self.getpricingTablesOptions();
					locker.options = $.extend(locker.options, extraOptions);
				});
			});
		},

		getpricingTablesOptions: function() {
			var tables = $('#onp-pl-pricing-tables-data').val();

			tables = JSON.parse(tables);

			var tablesOrder = [];

			for( var tablaName in tables ) {
				if( !tables.hasOwnProperty(tablaName) ) {
					continue;
				}
				tablesOrder.push(tablaName);
			}

			var options = {

				groups: {
					order: ['pricing-tables']
				},

				paylocker: {
					userLogin: false,
					// screen, page-redirect
					paymentForms: {
						yandex: {
							text: {
								header: 'Оплата премиум подписки',
								message: 'Если вы еще не зарегистрированы на нашем сайте, после оплаты подписки зайдите в свой почтовый ящик, чтобы получить доступы к вашему аккаунту.'
							},
							receiver: '410011242846510',
							targets: 'Тест тест',
							successURL: ''
						}
					}
				},

				pricingTables: {
					orderTables: tablesOrder,
					tables: tables
				}
			};

			$(document).trigger('onp-sl-filter-preview-options');

			if( window.bizpanda.pricingTablesOptions.filterOptions ) {
				options = window.bizpanda.pricingTablesOptions.filterOptions(options);
			}

			return options;
		}

		/*lockPremiumFeatures: function() {

		 if( $.inArray(this.item, ['social-locker', 'email-locker', 'signin-locker']) === -1 ) {
		 return;
		 }

		 $(".factory-tab-item.opanda-not-available").each(function() {

		 var $overlay = $("<div class='opanda-overlay'></div>");
		 var $note = $overlay.find(".opanda-premium-note");

		 $(this).append($overlay);
		 $(this).append($note);
		 });

		 return;
		 }*/

	};

	$(function() {
		window.bizpanda.pricingTablesOptions.init();
	});

})(jQuery);

