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
	
	// Special Inputs
	$('input[magenta-type]').each(function(){
		var type = $(this).attr('magenta-type');

		if (type == 'datetime' || type == 'date') {
			$(this).hide();
			var d = new Date($(this).attr('value').replace(/\-/g, '/'));

			// Day
			var day = $('<select />').addClass('magenta-day');
			day.append($('<option />').html(_('Day')));
			for (var i=1; i <= 31; i++) {
				var selected = i == d.getDate() ? ' selected="selected"' : '';
				day.append($('<option'+selected+' />').attr('value', i).html(i));
			};

			// Month
			var month = $('<select />').addClass('magenta-month');
			var month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
			month.append($('<option />').html(_('Month')));
			for (var i=1; i <= 12; i++) {
				var selected = i == (d.getMonth() + 1) ? ' selected="selected"' : '';
				month.append($('<option'+selected+' />').attr('value', i).html(_(month_names[(i-1)])));
			};

			// Year
			var year = $('<select />').addClass('magenta-year');
			year.append($('<option />').html(_('Year')));
			var n = new Date();
			for (var i=1900; i <= n.getFullYear()+10; i++) {
				var selected = i == d.getFullYear() ? ' selected="selected"' : '';
				year.append($('<option'+selected+' />').attr('value', i).html(i));
			};

			// Hour
			var hour = $('<select />').addClass('magenta-hour');
			hour.append($('<option />').html(_('Hour')));
			for (var i=0; i <= 23; i++) {
				var selected = i == d.getHours() ? ' selected="selected"' : '';
				if (i.toString().length < 2) i = '0'+i.toString();
				hour.append($('<option'+selected+' />').attr('value', i).html(i));
			};

			// Min
			var min = $('<select />').addClass('mangeta-min');
			min.append($('<option />').html(_('Min')));
			for (var i=0; i <= 59; i++) {
				var selected = i == d.getMinutes() ? ' selected="selected"' : '';
				if (i.toString().length < 2) i = '0'+i.toString();
				min.append($('<option'+selected+' />').attr('value', i).html(i));
			};

			// Container
			var container = $('<div />').addClass('magenta-datetime-input');
			$(this).after(container);

			container.append(day).append(month).append(year);

			if (type == 'datetime')
				container.append(hour).append(min);
				
			$(this).parent().find('select').change(function(){
				var c = $(this).parent();
				var date_string = c.find('.magenta-year').attr('value')+'/'+c.find('.magenta-month').attr('value')+'/'+c.find('.magenta-day').attr('value');
				if (c.parent().find('input[magenta-type]').attr('magenta-type') == 'datetime')
					date_string += ' '+c.find('.magenta-hour').attr('value')+':'+c.find('.mangeta-min').attr('value');
					
				var d = new Date(date_string);
				if (d.getFullYear()) {
					c.parent().find('input').attr('value', magenta.formaters.mysql_date(d));
				}
			});
		}
	})
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
	},
	date: function(i) {
		i = i.replace(/\-/g, '/');
		var d = new Date(i);
		if ( ! d.getYear())
			return '-';
		return d.format(_('DateFormat'));
	},
	mysql_date: function(d) {
		return d.format('Y-m-d H:i:s');
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

/** Date.format **/
Date.prototype.format=function(format){var returnStr='';var replace=Date.replaceChars;for(var i=0;i<format.length;i++){var curChar=format.charAt(i);if(i-1>=0&&format.charAt(i-1)=="\\"){returnStr+=curChar}else if(replace[curChar]){returnStr+=replace[curChar].call(this)}else if(curChar!="\\"){returnStr+=curChar}}return returnStr};Date.replaceChars={shortMonths:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],longMonths:['January','February','March','April','May','June','July','August','September','October','November','December'],shortDays:['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],longDays:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],d:function(){return(this.getDate()<10?'0':'')+this.getDate()},D:function(){return Date.replaceChars.shortDays[this.getDay()]},j:function(){return this.getDate()},l:function(){return Date.replaceChars.longDays[this.getDay()]},N:function(){return this.getDay()+1},S:function(){return(this.getDate()%10==1&&this.getDate()!=11?'st':(this.getDate()%10==2&&this.getDate()!=12?'nd':(this.getDate()%10==3&&this.getDate()!=13?'rd':'th')))},w:function(){return this.getDay()},z:function(){var d=new Date(this.getFullYear(),0,1);return Math.ceil((this-d)/86400000)}, W:function(){var d=new Date(this.getFullYear(),0,1);return Math.ceil((((this-d)/86400000)+d.getDay()+1)/7)},F:function(){return Date.replaceChars.longMonths[this.getMonth()]},m:function(){return(this.getMonth()<9?'0':'')+(this.getMonth()+1)},M:function(){return Date.replaceChars.shortMonths[this.getMonth()]},n:function(){return this.getMonth()+1},t:function(){var d=new Date();return new Date(d.getFullYear(),d.getMonth(),0).getDate()},L:function(){var year=this.getFullYear();return(year%400==0||(year%100!=0&&year%4==0))},o:function(){var d=new Date(this.valueOf());d.setDate(d.getDate()-((this.getDay()+6)%7)+3);return d.getFullYear()},Y:function(){return this.getFullYear()},y:function(){return(''+this.getFullYear()).substr(2)},a:function(){return this.getHours()<12?'am':'pm'},A:function(){return this.getHours()<12?'AM':'PM'},B:function(){return Math.floor((((this.getUTCHours()+1)%24)+this.getUTCMinutes()/60+this.getUTCSeconds()/ 3600) * 1000/24)}, g:function(){return this.getHours()%12||12},G:function(){return this.getHours()},h:function(){return((this.getHours()%12||12)<10?'0':'')+(this.getHours()%12||12)},H:function(){return(this.getHours()<10?'0':'')+this.getHours()},i:function(){return(this.getMinutes()<10?'0':'')+this.getMinutes()},s:function(){return(this.getSeconds()<10?'0':'')+this.getSeconds()},u:function(){var m=this.getMilliseconds();return(m<10?'00':(m<100?'0':''))+m},e:function(){return"Not Yet Supported"},I:function(){return"Not Yet Supported"},O:function(){return(-this.getTimezoneOffset()<0?'-':'+')+(Math.abs(this.getTimezoneOffset()/60)<10?'0':'')+(Math.abs(this.getTimezoneOffset()/60))+'00'},P:function(){return(-this.getTimezoneOffset()<0?'-':'+')+(Math.abs(this.getTimezoneOffset()/60)<10?'0':'')+(Math.abs(this.getTimezoneOffset()/60))+':00'},T:function(){var m=this.getMonth();this.setMonth(0);var result=this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/,'$1');this.setMonth(m);return result},Z:function(){return-this.getTimezoneOffset()*60},c:function(){return this.format("Y-m-d\\TH:i:sP")},r:function(){return this.toString()},U:function(){return this.getTime()/1000}};

/** jQuery Colors Animation **/
(function(d){function i(){var b=d("script:first"),a=b.css("color"),c=false;if(/^rgba/.test(a))c=true;else try{c=a!=b.css("color","rgba(0, 0, 0, 0.5)").css("color");b.css("color",a)}catch(e){}return c}function g(b,a,c){var e="rgb"+(d.support.rgba?"a":"")+"("+parseInt(b[0]+c*(a[0]-b[0]),10)+","+parseInt(b[1]+c*(a[1]-b[1]),10)+","+parseInt(b[2]+c*(a[2]-b[2]),10);if(d.support.rgba)e+=","+(b&&a?parseFloat(b[3]+c*(a[3]-b[3])):1);e+=")";return e}function f(b){var a,c;if(a=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(b))c=
[parseInt(a[1],16),parseInt(a[2],16),parseInt(a[3],16),1];else if(a=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(b))c=[parseInt(a[1],16)*17,parseInt(a[2],16)*17,parseInt(a[3],16)*17,1];else if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(b))c=[parseInt(a[1]),parseInt(a[2]),parseInt(a[3]),1];else if(a=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(b))c=[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10),parseFloat(a[4])];return c}
d.extend(true,d,{support:{rgba:i()}});var h=["color","backgroundColor","borderBottomColor","borderLeftColor","borderRightColor","borderTopColor","outlineColor"];d.each(h,function(b,a){d.fx.step[a]=function(c){if(!c.init){c.a=f(d(c.elem).css(a));c.end=f(c.end);c.init=true}c.elem.style[a]=g(c.a,c.end,c.pos)}});d.fx.step.borderColor=function(b){if(!b.init)b.end=f(b.end);var a=h.slice(2,6);d.each(a,function(c,e){b.init||(b[e]={a:f(d(b.elem).css(e))});b.elem.style[e]=g(b[e].a,b.end,b.pos)});b.init=true}})(jQuery);