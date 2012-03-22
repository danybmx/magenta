(function($){
	var resizer = function(object, user_options) {
		var rz = this;
		this.element = $(object);

		var default_options = {
			min: false,
			max: false,
			initial: false,
			overlay: true,
			constrain: false,
			success: function(){},
			cancel: function(){}
		};
		this.options = $.extend(default_options, user_options);

		if (this.options.overlay)
			$('#overlay').fadeIn();

		this.element.load(function(){
			rz.render()
		});

		this.render = function(){
			this.container = $('<div />').addClass('magenta-resizer').append(this.element).appendTo($('body')).fadeIn();
			
			this.zone = $('<div />').addClass('zone').appendTo(this.container).css({
				height: this.element.height(),
				width: this.element.width(),
				top: (this.container.outerHeight() - this.container.height()) / 2,
				left: (this.container.outerWidth() - this.container.width()) / 2
			});

			if ( ! this.options.initial && this.options.min)
				this.options.initial = this.options.min;

			this.box = $('<div />').addClass('box').css({
				width: this.options.initial.x,
				height: this.options.initial.y
			}).appendTo(this.zone);

			var drag = this.box.draggable({
				containment: this.zone
			});

			var resize = this.box.resizable({
				containment: this.zone,
				aspectRatio: this.options.constrain ? this.options.min.x+'/'+this.options.min.y : false,
				minWidth: this.options.min ? this.options.min.x : 0,
				maxWidth: this.options.max ? this.options.max.x : this.zone.width(),
				minHeight: this.options.min ? this.options.min.y : 0,
				maxHeight: this.options.max ? this.options.max.y : this.zone.height(),
			});

			this.toolbar = $('<div />').addClass('toolbar').appendTo(this.container);
			
			this.successButton = $('<a />').addClass('magenta-button').attr('icon', 'icons/small_tick.png').html(_('Accept')).click(function(){rz.success(rz)}).appendTo(this.toolbar)
			this.cancelButton = $('<a />').addClass('magenta-button').attr('icon', 'icons/small_cross.png').html(_('Cancel')).click(function(){rz.cancel(rz)}).appendTo(this.toolbar)

			magenta.buttonize();

			this.container.center();
		}

		this.prepareData = function(){
			return {
				x: this.box.position().left,
				y: this.box.position().top,
				w: this.box.width(),
				h: this.box.height(),
				image: this.element.attr('src')
			}
		}

		this.cancel = function() {
			if (this.options.cancel(rz) !== false)
				this.close();
		}

		this.success = function() {
			if (this.options.success(this.prepareData(), rz) !== false)
				this.close();
		}

		this.close = function() {
			if (this.options.overlay)
				$('#overlay').fadeOut();

			this.container.fadeOut(this.container.remove);
		}
	}

	$.fn.resizer = function(options) {
		var opts = options;
		
		if ($(this).data('resizer'))
			var obj = $(this).data('resizer');
		else {
			var options = typeof(opts) === 'object' ? opts : {};
			var obj = new resizer(this, options);
			$(this).data('resizer', obj);
		}
		return obj;
	}
})(jQuery);