(function($){
	var datagrid = function(object, user_options) {
		var dg = this;
		this.el = $(object);
		this.table = $('<table class="magenta-datagrid" id="'+this.el.prop('id')+'" cellpadding="0" cellspacing="0" border="0" />');
		this.cols = 0;
		this.rows = 0;
		this.data = [];

		this.default_options = {
			key: 'id',
			data: [],
			fields: [],
			actions: [],
			options: true,
			load: function(){}
		};
		this.options = {};
		
		this.create = function() {
			this.options = $.extend(this.default_options, user_options);
			this.cols = this.options.fields.length;
			
			/** Construct headers **/
			var thead = $('<thead />');
			var header = $('<tr class="header" />').appendTo(thead);
			$.each(this.options.fields, function(){
				var width = this.width ? this.width : 'auto';
				$('<th key="'+this.key+'" width="'+width+'" />').html(this.label).appendTo(header);
			});
			thead.appendTo(this.table);

			/** Construct tbody **/
			this.tbody = $('<tbody />').appendTo(this.table);

			/** Construct tfoot **/
			this.tfoot = $('<tfoot />').appendTo(this.table);
			var footer = $('<tr class="options" />').appendTo(this.tfoot);
			var actions = $('<td colspan="'+this.cols+'" />').appendTo(footer);
			$.each(this.options.actions, function(){
				var action = $('<div class="action disabled"></div>');
				action.html(this.name);
				var obj = this;
				
				if (this.icon)
					action.addClass('icon').prepend($('<img class="icon" src="'+base_path+'img/icons/'+this.icon+'" alt="'+this.name+'" />'));
				
				action.mouseenter(function(){
					if ( ! $(this).hasClass('disabled'))
						$(this).addClass('over');
				}).mouseleave(function(){
					$(this).removeClass('over');
				}).click(function(ev){
					var el = $(ev.currentTarget);
					if (el.hasClass('disabled')) return false;
					
					if (obj.confirm) {
						var message = obj.message ? obj.message : _('Are you sure?');
						var conf = confirm(message);
						if ( ! conf) return false;
					}
					
					if (( ! obj.multiple || obj.multiple != true) && dg.getSelectedRows().length > 1) {
						alert(_('This action do not allow multiple selection'));
						return false;
					}
					
					if (obj.action) {
						obj.action(dg.getSelectedKeys(), dg);
						dg.change();
					} else if (obj.url) {
						obj.url = obj.url.replace('%i', dg.getSelectedIndexes());
						obj.url = obj.url.replace('%k', dg.getSelectedKeys());
						location.href = obj.url;
					}
				});
				
				action.appendTo(actions);
			});
			this.tfoot.appendTo(this.table);
			
			this.loadData();

			this.table.data('datagrid', this);
			this.el.replaceWith(this.table);
		}
		
		this.getSelectedRows = function() {
			var selectedRows = [];
			this.tbody.find('tr.selected').each(function(){
				selectedRows.push(this);
			});
			return selectedRows;
		}
		
		this.getSelectedKeys = function() {
			var selectedRows = this.getSelectedRows();
			var selectedIndex = '';
			$.each(selectedRows, function(){
				selectedIndex += $(this).attr('key')+',';
			});
			return selectedIndex.slice(0, -1);
		}
		
		this.getSelectedIndexes = function() {
			var selectedRows = this.getSelectedRows();
			var selectedIndex = '';
			$.each(selectedRows, function(){
				selectedIndex += $(this).index(this.tbody)+',';
			});
			return selectedIndex.slice(0, -1);
		}
		
		this.getRowForKey = function(key) {
			var row = this.tbody.find('tr[key='+key+']');
			if ( ! row) row = false;
			return row;
		}

		this.getIndexForKey = function(key) {
			var index = this.tbody.find('tr[key='+key+']').index();
			return index;
		}
		
		this.getRowAtIndex = function(index) {
			return $(this.tbody.find('tr')[index]);
		}
		
		this.getDataForKey = function(key) {
			var i = this.getIndexForKey(key);
			return this.data[i];
		}
		
		this.getDataForIndex = function(i) {
			return this.data[i];
		}
		
		this.setDataForKey = function(k, attr, value) {
			var i = this.getIndexForKey(k);
			this.data[i][attr] = value;
			
			return this.data[i];
		}
		
		this.reloadData = function() {
			this.tbody.html('');
			this.current_row = 0;
			this.options.data = array_values(this.data);
			this.data = [];
			this.loadData();
		}

		this.loadData = function() {
			$.each(this.options.data, function(i, d){
				dg.insertRow(d);
			});
			this.options.load(dg);
		}

		this.insertRow = function(data) {
			this.data.push(data);
			var css = (this.rows % 2) ? 'even' : 'odd';
			var row = $('<tr class="'+css+'" key='+data[this.options.key]+' />').appendTo(this.tbody);
			$.each(this.options.fields, function(){
				var field = $('<td />').appendTo(row);
				
				if ( ! this.type) this.type = null;
				if ( ! this.render) this.render = null;
				if ( ! this.params) this.params = null;
				if ( ! this.css) this.css = {};
			
				if ( ! this.editable) this.editable = false;
				if ( ! this.change) this.change = function(){console.log('changed')};

				var html = (typeof(data[this.key]) == 'undefined') ? '' : data[this.key];
				
				if (this.key.match(/\./)) {
					var key = this.key.split('.');
					var root = data;
					for (var i=0; i < key.length-1; i++) {
						root = root[key[i]];
					};
					html = root[key[key.length-1]];
				}
				
				if (this.type) {
					if (this.type == 'combo') {
						this.render = dg.renderers.combo;
						this.css.textAlign = 'center';
					} else if (this.type == 'currency') {
						this.render = dg.renderers.currency;
						this.css.textAlign = 'right';
					} else if (this.type == 'numeric') {
						this.render = dg.renderers.numeric;
						this.css.textAlign = 'right';
					} else if (this.type == 'decimal') {
						this.render = dg.renderers.decimal;
						this.css.textAlign = 'right';
					}
				}
				
				if (this.render) {
					html = this.render(data, this.key, this.params, dg);
				}
				
				field.html(html).css(this.css);
				
				if (this.editable) {
					var tmpfield = $('<td class="editing" />');
					var tmpinput = $('<input type="text" class="editable" />').appendTo(tmpfield);
					
					var cancel = $('<img src="'+base_path+'/img/icons/cross.png" />').appendTo(tmpfield);
					
					field.dblclick(function(){
						tmpinput.width(field.outerWidth()-30);
						$(field.parent()).addClass('editing');
						field.replaceWith(tmpfield);
						tmpinput.focus();
						
						$('html').bind('click', function(){
							cancel.trigger('click');
							$(this).unbind('click');
						});
						
					});
					
					cancel.click(function(){
						$(field.parent()).removeClass('editing');
						tmpfield.replaceWith(field);
						dg.reloadData();
					});
					
					var obj = this;
					tmpinput.keydown(function(ev){
						if (ev.keyCode == 13) {
							var response = obj.change(tmpinput.attr('value'), dg.data[row.index()], row.index(), dg);
							if (response) {
								if (typeof response == 'object') {
									dg.data[row.index()] = $.extend(dg.data[row.index()], response);
								} else {
									dg.data[row.index()][obj.key] = response;
								}
								dg.reloadData();
							} else {
								cancel.trigger('click');
							}
						}
					});
				};
			});

			row.mouseenter(function(){
				$(this).addClass('highlight');
			}).mouseleave(function(){
				$(this).removeClass('highlight');
			}).click(function(){
				$(this).toggleClass('selected');
				dg.change();
			});

			this.rows++;
			this.options.load(dg);
		}

		this.removeRow = function(index) {
			$(this.tbody.find('tr')[index]).remove();
			delete this.data[index];
			this.rows--;
			this.reloadData();
		}
		
		this.removeSelectedRows = function() {
			var keys = this.getSelectedKeys().split(',');
			for (var i=0; i <= keys.length; i++) {
				this.removeRow(this.getIndexForKey(keys[i]));
			};
		}
		
		this.change = function() {
			if (this.getSelectedRows().length > 0)
				this.tfoot.find('div.action').removeClass('disabled');
			else
				this.tfoot.find('div.action').addClass('disabled');
		}
		
		this.renderers = {
			numeric: function(data, key) {
				return parseInt(data[key]);
			},
			decimal: function(data, key) {
				var i = parseFloat(data[key]);
				return magenta.formaters.decimal(i);
			},
			combo: function(data, key) {
				var html = '';
				if (data[key] == 1)
					html = '<img src="'+base_path+'img/icons/tick.png" alt="'+_('Yes')+'" />';
				else
					html = '<img src="'+base_path+'img/icons/cross.png" alt="'+_('No')+'" />';
				return html;
			},
			currency: function(data, key, sign) {
				return magenta.formaters.currency(data[key], sign);
			}
		};

		this.create();
	};

	$.fn.datagrid = function(options) {
		var opts = options;
		
		if ($(this).data('datagrid'))
			var obj = $(this).data('datagrid');
		else {
			var options = typeof(opts) === 'object' ? opts : {};
			var obj = new datagrid(this, options);
			$(this).data('datagrid', obj);
		}
		return obj;
	}
})(jQuery);