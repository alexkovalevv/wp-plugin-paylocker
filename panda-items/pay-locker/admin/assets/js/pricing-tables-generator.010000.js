/**
 * Генератор тарифных таблиц
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright Alex Kovalev 16.12.2016
 * @version 1.0
 */


(function($) {
	'use strict';

	var pricingTableGenerator = {
		tablesOrder: [],

		init: function() {

			this.tableItemPrototype = $('.onp-pl-pg-tables-item-prototype').detach().clone();
			this.tableItemPrototype.removeClass('onp-pl-pg-tables-item-prototype');
			this.tableItemPrototype.removeAttr('style');

			this.tableSeparatorPrototype = $('.onp-pl-pg-tables-separator-prototype').detach().clone();
			this.tableSeparatorPrototype.removeClass('onp-pl-pg-tables-separator-prototype');
			this.tableSeparatorPrototype.removeAttr('style');

			this.loadTables();
			this.registerEvents();
			this.checkoutPaymentType();

		},

		registerEvents: function() {
			var self = this;

			$.bizpanda.filters.add('opanda-preview-options', function(options) {
				self.updateTablesData();
			});

			$(document).on('change', '.onp-pl-table-payment-type-control', function() {
				self.checkoutPaymentType();
			});

			$('.onp-pl-pg-create-table-button').click(function() {
				var newTable = self.tableItemPrototype.clone();
				$('.onp-pl-pg-tables').append(newTable);
				self.editTable(newTable);
				self.refreshTables();
				$('#onp-pl-pricing-tables-data').change();
				return false;
			});

			$('.onp-pl-pg-create-separator-button').click(function() {
				$('.onp-pl-pg-tables').append(self.tableSeparatorPrototype.clone());
				self.refreshTables();
				$('#onp-pl-pricing-tables-data').change();
				return false;
			});

			$(document).on('click', '.onp-pl-pg-delete-table-button', function() {
				$(this).closest('.onp-pl-pg-tables-item').remove();
				self.refreshTables();
				$('#onp-pl-pricing-tables-data').change();
				return false;
			});

			$(document).on('click', '.onp-pl-pg-edit-table-button', function() {
				self.editTable($(this).closest('.onp-pl-pg-tables-item'));
				return false;
			});

			//

			// make shortable
			$(".onp-pl-pg-tables").addClass("ui-sortable");
			$(".onp-pl-pg-tables").sortable({
				placeholder: "sortable-placeholder",
				opacity: 0.7,
				items: ".onp-pl-pg-tables-item",
				over: function(event, ui) {
					var el = $(event.toElement).closest('.onp-pl-pg-tables-item');
					if( el.hasClass('onp-pl-pg-tables-separator') ) {
						$('.sortable-placeholder').addClass('separator');
					}
				},
				update: function(event, ui) {
					$('#onp-pl-pricing-tables-data').change();
				}
			});
		},

		checkoutPaymentType: function() {
			var self = this;
			$('.onp-pl-table-payment-type-control').each(function() {
				var table = $(this).closest('.onp-pl-pg-tables-item');

				if( $(this).val() == 'subscribe' ) {
					table.find('.onp-pl-table-td-expired').fadeIn(function() {
						$(this).css({display: 'table-cell'});
					});
				} else {
					table.find('.onp-pl-table-td-expired').fadeOut();
				}
			});

		},

		editTable: function($handler) {
			if( !$handler.hasClass('onp-pl-edit') ) {
				$('.onp-pl-pg-edit-table-button').find('.fa').removeClass('fa-times').addClass('fa-cog');
				$('.onp-pl-pg-tables-item').removeClass('onp-pl-edit');
				$handler.addClass('onp-pl-edit');
				this.updateEditAreaSize();
				$handler.find('.onp-pl-pg-edit-table-button').find('.fa').removeClass('fa-cog').addClass('fa-times');

				$(".onp-pl-pg-tables").sortable("disable");
			} else {
				$handler.find('.onp-pl-pg-edit-table-button').find('.fa').removeClass('fa-times').addClass('fa-cog');
				$handler.removeClass('onp-pl-edit');
				this.updateEditAreaSize();
				$(".onp-pl-pg-tables").sortable("enable");
			}
		},

		updateEditAreaSize: function() {
			var self = this;
			var elHeight = $('.onp-pl-pg-tables-item.onp-pl-edit').find('.onp-pl-pg-table-form').outerHeight(true);
			if( elHeight ) {
				$('.onp-pl-pg-tables').height(elHeight + 50);
			} else {
				$('.onp-pl-pg-tables').css({height: "auto"});
			}

		},

		/**
		 * Refreshes the preview after short delay.
		 */
		refreshPreview: function(force) {
			var self = this;

			if( this.timerOn && !force ) {
				this.timerAgain = true;
				return;
			}

			this.timerOn = true;
			setTimeout(function() {
				if( self.timerAgain ) {
					self.timerAgain = false;
					self.refreshPreview(true);
				} else {
					self.timerAgain = false;
					self.timerOn = false;
					self.updateTablesData();
				}

			}, 1000);
		},

		refreshTables: function() {
			var tablesNotFoundSelector = $('.onp-pl-pg-tables-not-found'),
				tablesItemSelector = $('.onp-pl-pg-tables-item');

			if( !tablesItemSelector.length && !tablesNotFoundSelector.length ) {
				$('.onp-pl-pg-tables').before('<div class="onp-pl-pg-tables-not-found">' +
				'Вы не создали еще ни одной тарифной таблицы. Чтобы добавить новую талицу, нажмите кнопку "Добавить таблицу".' +
				'</div>');
			} else if( tablesNotFoundSelector.length ) {
				tablesNotFoundSelector.remove();
			}

			this.updateTablesData();
		},

		loadTables: function() {

			var tables = $('#onp-pl-pricing-tables-data').val();

			tables = tables.length ? JSON.parse(tables) : {};

			if( !Object.keys(tables).length ) {
				this.refreshTables();
			}

			for( var tableName in tables ) {
				if( !tables.hasOwnProperty(tableName) ) {
					continue;
				}

				var itemPrototype;

				if( tables[tableName].itemType == 'separator' ) {
					itemPrototype = this.tableSeparatorPrototype.clone();
				} else {
					itemPrototype = this.tableItemPrototype.clone();
				}

				for( var fieldName in tables[tableName] ) {
					if( !tables[tableName].hasOwnProperty(fieldName) ) {
						continue;
					}
					var controlClassName = '.onp-pl-table-' + this.destroyCamelCase(fieldName) + '-control';
					itemPrototype.find(controlClassName).val(tables[tableName][fieldName]);
				}

				itemPrototype.find('.onp-pl-pg-table-name').text(tables[tableName]['header']);
				var tableType = 'Покупка';
				if( tables[tableName]['paymentType'] === 'subscribe' ) {
					tableType = 'Подписка';
					itemPrototype.find('.onp-pl-pg-table-type').addClass('subscribe');
				}

				itemPrototype.find('.onp-pl-pg-table-price').text(tables[tableName]['price'] + ' руб.');
				itemPrototype.find('.onp-pl-pg-table-type').text(tableType);

				$('.onp-pl-pg-tables').append(itemPrototype);
			}

		},

		updateTablesData: function() {
			var self = this;

			this.tablesOrder = [];

			var tables = {};
			var tablesItemSelector = $('.onp-pl-pg-tables-item');

			tablesItemSelector.each(function(i) {
				var tableName, tableData, itemType = $(this).data('item-type');
				if( $(this).data('item-type') == 'separator' ) {
					tableData = {
						itemType: itemType
					};
					tableName = 'separator_' + i;
				} else {
					tableData = {
						header: $(this).find('.onp-pl-table-header-control').val(),
						itemType: $(this).data('item-type'),
						paymentType: $(this).find('.onp-pl-table-payment-type-control').val(),
						price: $(this).find('.onp-pl-table-price-control').val(),
						expired: $(this).find('.onp-pl-table-expired-control').val(),
						description: $(this).find('.onp-pl-table-description-control').val(),
						buttonText: $(this).find('.onp-pl-table-button-text-control').val(),
						afterButtonText: $(this).find('.onp-pl-table-after-button-text-control').val()
					};

					tableName = 'table_' + i;
				}
				/****/
				$(this).find('.onp-pl-pg-table-name').text(tableData['header']);
				var tableType = 'Покупка';
				$(this).find('.onp-pl-pg-table-type').removeClass('subscribe');
				if( tableData['paymentType'] === 'subscribe' ) {
					tableType = 'Подписка';
					$(this).find('.onp-pl-pg-table-type').addClass('subscribe');
				}

				$(this).find('.onp-pl-pg-table-price').text(tableData['price'] + ' руб.');
				$(this).find('.onp-pl-pg-table-type').text(tableType);
				/****/

				tables[tableName] = tableData;
				self.tablesOrder.push(tableName);
			});

			$('#onp-pl-pricing-tables-data').val(JSON.stringify(tables));
		},

		/**
		 * Converts string of the view 'fooBar' to 'foo-bar'.
		 * @param input
		 * @returns {XML|string|void}
		 */
		destroyCamelCase: function(input) {
			input.charAt(0).toLowerCase();
			return input.replace(/[A-Z]/g, function(match) {
				return '-' + match.toLowerCase();
			});
		}
	};

	$(document).ready(function() {
		pricingTableGenerator.init();
	});
})(jQuery);

