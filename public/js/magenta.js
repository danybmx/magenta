var base_path = $('base').attr('href');
var magenta = {};

$().ready(function(){
	// Buttons
	magenta.buttonize();
	
	// Errors
	$('.magenta-error').css({
		marginTop: -35,
		opacity: 0
	}).animate({
		marginTop: 10,
		opacity: 1
	}).delay(5000).animate({
		marginTop: -35,
		opacity: 0
	}, function(){
		$(this).remove();
	})

	// Tooltips
	$('[magenta-tooltip]').each(function(){
		var el = $(this);
		var tooltip = el.attr('magenta-tooltip');
		$(this).mouseenter(function(){
			var pos = $(this).position();
			$('<div class="magenta-tooltip" />').html(tooltip).appendTo($('body')).delay(100, function(){
				$(this).css({
					left: pos.left + ((el.outerWidth() - $(this).width()) / 2),
					top: pos.top - el.height()
				})
			});
		}).mouseleave(function(){
			$('div.magenta-tooltip').remove();
		})
	});
});

function _(s) {
	if (typeof(i18n) != 'undefined' && i18n[s]) {
		return i18n[s];
	} else {
		return s;
	}
}

function rand() {
	return parseInt(Math.random()*9999)
}

function array_values (input) {
	var tmp_arr = [], key = '';

	if (input && typeof input === 'object' && input.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
		return input.values();
	}

	for (key in input) {
		tmp_arr[tmp_arr.length] = input[key];
	}

	return tmp_arr;
}

magenta.buttonize = function(){
	$('.magenta-button').each(function(){
		var el = $(this);
		if (el.data('buttonized')) return el;
		
		if (el.prop('tagName') == 'INPUT' || el.prop('tagName') == 'BUTTON') {
			var style = (el.attr('style')) ? ' style="'+el.attr('style')+'"' : '';
			var icon = el.attr('icon') ? ' icon="'+el.attr('icon')+'"' : '';
			var button = $('<a class="magenta-button"'+icon+' '+style+'>'+el.attr('value')+'</div>');
			var obj = this;
			button.click(function(){$(obj).trigger('click')});
			el.css({visibility: 'hidden', position: 'absolute'});
			el.before(button);
			el = button;
		}
		
		if (el.attr('icon')) {
			el.addClass('icon');
			var image = el.attr('icon');
			el.prepend($('<img src="'+base_path+'img/'+image+'" class="icon" />'));
		}

		el.data('buttonized', true)
		
		el.mouseenter(function(){
			el.addClass('over');
		}).mouseleave(function(){
			el.removeClass('over');
		})
	});
}

magenta.formaters = {
	decimal: function(i){
		if(isNaN(i)) { i = 0.00; }
		
		var minus = '';
		if(i < 0) { minus = '-'; }
		i = Math.abs(i);
		i = parseInt((i + .005) * 100);
		i = i / 100;
		s = new String(i);
		
		if(s.indexOf('.') < 0) { s += '.00'; }
		if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
		s = minus + s;
		s = s.replace('.', ',');
		
		return s;
	},
	currency: function(i, s){
		if (s == undefined) s = '&euro;';
		return this.decimal(i)+s;
	}
};

magenta.execute = function(script) {
	eval(script);
}

$.fn.extend({
	center: function(){
		$(this).each(function(){
			$(this).css({
				top: '50%',
				left: '50%',
				marginLeft: - $(this).outerWidth() / 2,
				marginTop: - $(this).outerHeight() / 2,
				position: 'fixed'
			})
		})
	},
	highlight: function(color, time, times) {
		color = color ? color : '#AFA';
		time = time ? time : 2000;
		times = times ? times : 1;

		$(this).each(function(){
			var old_bg = $(this).css('backgroundColor');
			var new_bg = color;
			var callback = function(){};
			for (var i = 0; i < times; i++) {
				if (i == times - 1) {
					callback = function() {
						$(this).css('backgroundColor', '');
					}
				}

				$(this).animate({
					backgroundColor: new_bg
				}, 'fast').animate({
					backgroundColor: old_bg
				}, time, callback);
			}
		});
	}
});

function ucfirst (str) {
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}

/** jQuery Colors Animation **/
(function(d){function i(){var b=d("script:first"),a=b.css("color"),c=false;if(/^rgba/.test(a))c=true;else try{c=a!=b.css("color","rgba(0, 0, 0, 0.5)").css("color");b.css("color",a)}catch(e){}return c}function g(b,a,c){var e="rgb"+(d.support.rgba?"a":"")+"("+parseInt(b[0]+c*(a[0]-b[0]),10)+","+parseInt(b[1]+c*(a[1]-b[1]),10)+","+parseInt(b[2]+c*(a[2]-b[2]),10);if(d.support.rgba)e+=","+(b&&a?parseFloat(b[3]+c*(a[3]-b[3])):1);e+=")";return e}function f(b){var a,c;if(a=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(b))c=
[parseInt(a[1],16),parseInt(a[2],16),parseInt(a[3],16),1];else if(a=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(b))c=[parseInt(a[1],16)*17,parseInt(a[2],16)*17,parseInt(a[3],16)*17,1];else if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(b))c=[parseInt(a[1]),parseInt(a[2]),parseInt(a[3]),1];else if(a=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(b))c=[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10),parseFloat(a[4])];return c}
d.extend(true,d,{support:{rgba:i()}});var h=["color","backgroundColor","borderBottomColor","borderLeftColor","borderRightColor","borderTopColor","outlineColor"];d.each(h,function(b,a){d.fx.step[a]=function(c){if(!c.init){c.a=f(d(c.elem).css(a));c.end=f(c.end);c.init=true}c.elem.style[a]=g(c.a,c.end,c.pos)}});d.fx.step.borderColor=function(b){if(!b.init)b.end=f(b.end);var a=h.slice(2,6);d.each(a,function(c,e){b.init||(b[e]={a:f(d(b.elem).css(e))});b.elem.style[e]=g(b[e].a,b.end,b.pos)});b.init=true}})(jQuery);