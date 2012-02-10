/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

// Centre un �l�ment sur la page
jQuery.fn.center = function () {
	this.css("top", ( jQuery(window).height() - this.height() ) / 2 + "px");
	this.css("left", ( jQuery(window).width() - this.width() ) / 2 + "px");
	return this;
}

/*	Check all event on page load	*/
wpshop(document).ready(function(){
	jQuery("#wpshopFormManagementContainer").tabs();
	/*	Define the tools for the widget containing the different categories and products	*/
	wpshop(".wpshop_open_category").click(function(){
		widget_menu_animation(wpshop(this));
	});

	wpshop("a[rel=appendix]").fancybox({
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	wpshop("a#product_thumbnail").fancybox({
		'titleShow'     : false
	});
	
	jQuery('input[name=addToCart]').click(function(){
		var element = jQuery(this).parent();
		var pid = jQuery('input[name=product_id]', element).val();
		jQuery('.loading', element).removeClass('success error');
		jQuery('.loading', element).css('display', 'inline');
		jQuery.getJSON(WPSHOP_AJAX_URL, { post: "true", elementCode: "ajax_cartAction", action: "addProduct", pid: pid },
			function(data){
				if(data[0]) {
					jQuery('.loading', element).addClass('success');
					jQuery('body').append('<div class="superBackground"></div><div class="popupAlert">'+data[1]+'</div>');
					jQuery('.popupAlert').center();
				}
				else {
					jQuery('.loading', element).addClass('error');
					alert(data[1]);
				}
			}
		);
		return false;
	});
	
	// Ferme la boite de dialogue
	jQuery("input.closeAlert").live('click', function(){
		jQuery('.superBackground').remove();
		jQuery('.popupAlert').remove();
	});
	
	jQuery('a.remove').click(function(){
		jQuery(this).addClass('loading');
		var element = jQuery(this).parent().parent();
		var pid = element.attr('id').substr(8);
		updateQty(element, pid, 0);
		return false;
	});
	
	jQuery('input[name=productQty]').change(function(){
		var input = jQuery(this);
		var element = input.parent().parent();
		var pid = element.attr('id').substr(8);
		var qty = input.val();
		updateQty(element, pid, qty);
		return false;
	});
	
	jQuery('a.productQtyChange').click(function(){
		var a = jQuery(this);
		var element = a.parent().parent();
		var input = jQuery('input[name=productQty]',element);
		var pid = element.attr('id').substr(8);
		if(a.html()=='+')
			var qty = parseInt(input.val())+1;
		else var qty = parseInt(input.val())-1;
		updateQty(element, pid, qty);
		return false;
	});
	
	function updateQty(element, pid, qty) {
		qty = qty<0 ? 0 : qty;
		jQuery('input[name=productQty]',element).val(qty);
		jQuery('a.remove',element).addClass('loading');
		jQuery.get(WPSHOP_AJAX_URL, { post: "true", elementCode: "ajax_cartAction", action: "setProductQty", pid: pid, qty: qty },
			function(data){
				if(data=='success') {
					if(qty<=0){
						// Suppression de l'�l�ment
						element.fadeOut(250,function(){
							element.remove();
							// Si le tableau est vide, on affiche que le panier est vide
							if(jQuery("table#cartContent tbody tr").length==0) {
								jQuery("table#cartContent").fadeOut(250,function(){
									jQuery(this).remove();
									jQuery("div.cart").html(jQuery("input[name=emptyCartSentence]").val());
								});
							}
						});
					}
					else {
						// On place la nouvelle valeur dans le champ de s�curit�
						jQuery('input[name=currentProductQty]',element).val(qty);
						jQuery('a.remove',element).removeClass('loading');
						jQuery('td.total_price_ht span',element).html((jQuery('input[name=product_price_ht]',element).val()*jQuery('input[name=productQty]',element).val()).toFixed(2)+' EUR');
						jQuery('td.total_price_ttc span',element).html((jQuery('input[name=product_price_ttc]',element).val()*jQuery('input[name=productQty]',element).val()).toFixed(2)+' EUR');
					}
					updateTotal();
				}
				else {
					jQuery('a.remove',element).removeClass('loading');
					// On remet la valeur initiale
					jQuery('input[name=productQty]',element).val(jQuery('input[name=currentProductQty]',element).val());
					alert(data);
				}
			}
		);
	}
	
	function get_float_value(element) {
		return parseFloat(element.html().slice(0,-4));
	}
	function updateTotal() {
		var tab = new Array();
		var total_ht=0;
		var total_ttc=0;
		var tax_rate=0;
		var tax_total_amount=0;
		var order_shipping_cost=0;
		var product_qty=0;
		jQuery('table#cartContent tbody tr').each(function(){
			product_qty = jQuery('input[name=productQty]',this).val();
			tax_rate = jQuery('input[name=product_tax_rate]',this).val();
			tax_total_amount = jQuery('input[name=product_tax_amount]',this).val() * product_qty;
			total_ht += jQuery('input[name=product_price_ht]',this).val() * product_qty;
			total_ttc += jQuery('input[name=product_price_ttc]',this).val() * product_qty;
			order_shipping_cost += jQuery('input[name=product_shipping_cost]',this).val() * product_qty;
			
			if(tab[tax_rate] != undefined) {
				tab[tax_rate] += tax_total_amount;
			}
			else {
				tab[tax_rate] = tax_total_amount;
			}
		});
		total_ht = total_ht.toFixed(2)+' EUR';
		total_ttc = (total_ttc + order_shipping_cost).toFixed(2)+' EUR';
		order_shipping_cost = order_shipping_cost.toFixed(2)+' EUR';
		
		jQuery('div.cart span.total_ht').html(total_ht).stop().effect("highlight", {}, 3000);
		jQuery('div.cart span.total_ttc').html(total_ttc).stop().effect("highlight", {}, 3000);
		jQuery('div.cart div#order_shipping_cost span').html(order_shipping_cost).stop().effect("highlight", {}, 3000);
		
		for(var i in tab) {
			var element = jQuery('div.cart div#tax_total_amount_'+i.replace('.','_'));
			tab[i] = tab[i].toFixed(2);
			// On ne met � jour que ce qui a chang�
			if(tab[i] != get_float_value(jQuery('span',element))) {
				jQuery('span',element).html(tab[i]+' EUR').stop().effect("highlight", {}, 3000);
				//alert('div.cart div#tax_total_amount_'+i.replace('.','_'));
				if(parseFloat(tab[i]) == 0) {
					// On supprime l'�l�ment
					//element.remove();
					element.fadeOut(250,function(){jQuery(this).remove();});
				}
			}
		}
	}
	
	jQuery('a.checkoutForm_login').click(function(){
		if(jQuery('#register').css('display')=='block'){
			var elementToShow = '#login';var elementToHide = '#register';
			var infosToShow = '#infos_login';var infosToHide = '#infos_register';
		}
		else {
			var elementToShow = '#register';var elementToHide = '#login';
			var infosToShow = '#infos_register';var infosToHide = '#infos_login';
		}
		jQuery(infosToShow).show(); jQuery(infosToHide).hide();
		jQuery(elementToHide).fadeOut(250,function(){
			jQuery(elementToShow).fadeIn(250);
		});
		return false;
	});
	
	jQuery('input[type=checkbox][name=shiptobilling]').click(function(){
		if (jQuery(this).attr('checked')=='checked') {
			jQuery('#shipping_infos_bloc').fadeOut(250);
		}
		else jQuery('#shipping_infos_bloc').fadeIn(250);
	});
	
	jQuery('table.blockPayment').click(function() {
		jQuery('table.blockPayment').removeClass('active');
		jQuery('table.blockPayment input[type=radio]').attr('checked', false);
		jQuery(this).addClass('active');
		jQuery('input[type=radio]',this).attr('checked', true);
	});

	/*	Allows to fill the installation form without having to type anything	*/
	jQuery(".fill_form_checkout_for_test").click(function(){
		jQuery("input[name=account_first_name]").val("Test firstname");
		jQuery("input[name=account_last_name]").val("Test lastname");
		jQuery("input[name=account_company]").val("Test company");
		jQuery("input[name=account_email]").val("dev@eoxia.com");
		jQuery("input[name=account_password_1]").val("a");
		jQuery("input[name=account_password_2]").val("a");
		jQuery("input[name=billing_address]").val("5 bis rue du pont de lattes");
		jQuery("input[name=billing_postcode]").val("34000");
		jQuery("input[name=billing_city]").val("Montpellier");
		jQuery("input[name=billing_country]").val("France");
	});
});

/**
*	Define the function allowing to open or close the widget menu
*/
function widget_menu_animation(current_element){
	current_category = current_element.attr("id").replace("wpshop_open_category_", "");
	if(current_element.hasClass("wpshop_category_closed")){
		current_element.removeClass("wpshop_category_closed");
		current_element.addClass("wpshop_category_opened");
		wpshop(".wpshop_category_sub_content_" + current_category).slideDown();
	}
	else{
		current_element.removeClass("wpshop_category_opened");
		current_element.addClass("wpshop_category_closed");
		wpshop(".wpshop_category_sub_content_" + current_category).slideUp();
	}
}


/*
 * jQuery UI Effects 1.8.16
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/
 */
jQuery.effects||function(f,j){function m(c){var a;if(c&&c.constructor==Array&&c.length==3)return c;if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(c))return[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10)];if(a=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(c))return[parseFloat(a[1])*2.55,parseFloat(a[2])*2.55,parseFloat(a[3])*2.55];if(a=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(c))return[parseInt(a[1],
16),parseInt(a[2],16),parseInt(a[3],16)];if(a=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(c))return[parseInt(a[1]+a[1],16),parseInt(a[2]+a[2],16),parseInt(a[3]+a[3],16)];if(/rgba\(0, 0, 0, 0\)/.exec(c))return n.transparent;return n[f.trim(c).toLowerCase()]}function s(c,a){var b;do{b=f.curCSS(c,a);if(b!=""&&b!="transparent"||f.nodeName(c,"body"))break;a="backgroundColor"}while(c=c.parentNode);return m(b)}function o(){var c=document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle,
a={},b,d;if(c&&c.length&&c[0]&&c[c[0]])for(var e=c.length;e--;){b=c[e];if(typeof c[b]=="string"){d=b.replace(/\-(\w)/g,function(g,h){return h.toUpperCase()});a[d]=c[b]}}else for(b in c)if(typeof c[b]==="string")a[b]=c[b];return a}function p(c){var a,b;for(a in c){b=c[a];if(b==null||f.isFunction(b)||a in t||/scrollbar/.test(a)||!/color/i.test(a)&&isNaN(parseFloat(b)))delete c[a]}return c}function u(c,a){var b={_:0},d;for(d in a)if(c[d]!=a[d])b[d]=a[d];return b}function k(c,a,b,d){if(typeof c=="object"){d=
a;b=null;a=c;c=a.effect}if(f.isFunction(a)){d=a;b=null;a={}}if(typeof a=="number"||f.fx.speeds[a]){d=b;b=a;a={}}if(f.isFunction(b)){d=b;b=null}a=a||{};b=b||a.duration;b=f.fx.off?0:typeof b=="number"?b:b in f.fx.speeds?f.fx.speeds[b]:f.fx.speeds._default;d=d||a.complete;return[c,a,b,d]}function l(c){if(!c||typeof c==="number"||f.fx.speeds[c])return true;if(typeof c==="string"&&!f.effects[c])return true;return false}f.effects={};f.each(["backgroundColor","borderBottomColor","borderLeftColor","borderRightColor",
"borderTopColor","borderColor","color","outlineColor"],function(c,a){f.fx.step[a]=function(b){if(!b.colorInit){b.start=s(b.elem,a);b.end=m(b.end);b.colorInit=true}b.elem.style[a]="rgb("+Math.max(Math.min(parseInt(b.pos*(b.end[0]-b.start[0])+b.start[0],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[1]-b.start[1])+b.start[1],10),255),0)+","+Math.max(Math.min(parseInt(b.pos*(b.end[2]-b.start[2])+b.start[2],10),255),0)+")"}});var n={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,
0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,
211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]},q=["add","remove","toggle"],t={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};f.effects.animateClass=function(c,a,b,
d){if(f.isFunction(b)){d=b;b=null}return this.queue(function(){var e=f(this),g=e.attr("style")||" ",h=p(o.call(this)),r,v=e.attr("class");f.each(q,function(w,i){c[i]&&e[i+"Class"](c[i])});r=p(o.call(this));e.attr("class",v);e.animate(u(h,r),{queue:false,duration:a,easing:b,complete:function(){f.each(q,function(w,i){c[i]&&e[i+"Class"](c[i])});if(typeof e.attr("style")=="object"){e.attr("style").cssText="";e.attr("style").cssText=g}else e.attr("style",g);d&&d.apply(this,arguments);f.dequeue(this)}})})};
f.fn.extend({_addClass:f.fn.addClass,addClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{add:c},a,b,d]):this._addClass(c)},_removeClass:f.fn.removeClass,removeClass:function(c,a,b,d){return a?f.effects.animateClass.apply(this,[{remove:c},a,b,d]):this._removeClass(c)},_toggleClass:f.fn.toggleClass,toggleClass:function(c,a,b,d,e){return typeof a=="boolean"||a===j?b?f.effects.animateClass.apply(this,[a?{add:c}:{remove:c},b,d,e]):this._toggleClass(c,a):f.effects.animateClass.apply(this,
[{toggle:c},a,b,d])},switchClass:function(c,a,b,d,e){return f.effects.animateClass.apply(this,[{add:a,remove:c},b,d,e])}});f.extend(f.effects,{version:"1.8.16",save:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.data("ec.storage."+a[b],c[0].style[a[b]])},restore:function(c,a){for(var b=0;b<a.length;b++)a[b]!==null&&c.css(a[b],c.data("ec.storage."+a[b]))},setMode:function(c,a){if(a=="toggle")a=c.is(":hidden")?"show":"hide";return a},getBaseline:function(c,a){var b;switch(c[0]){case "top":b=
0;break;case "middle":b=0.5;break;case "bottom":b=1;break;default:b=c[0]/a.height}switch(c[1]){case "left":c=0;break;case "center":c=0.5;break;case "right":c=1;break;default:c=c[1]/a.width}return{x:c,y:b}},createWrapper:function(c){if(c.parent().is(".ui-effects-wrapper"))return c.parent();var a={width:c.outerWidth(true),height:c.outerHeight(true),"float":c.css("float")},b=f("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0}),
d=document.activeElement;c.wrap(b);if(c[0]===d||f.contains(c[0],d))f(d).focus();b=c.parent();if(c.css("position")=="static"){b.css({position:"relative"});c.css({position:"relative"})}else{f.extend(a,{position:c.css("position"),zIndex:c.css("z-index")});f.each(["top","left","bottom","right"],function(e,g){a[g]=c.css(g);if(isNaN(parseInt(a[g],10)))a[g]="auto"});c.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})}return b.css(a).show()},removeWrapper:function(c){var a,b=document.activeElement;
if(c.parent().is(".ui-effects-wrapper")){a=c.parent().replaceWith(c);if(c[0]===b||f.contains(c[0],b))f(b).focus();return a}return c},setTransition:function(c,a,b,d){d=d||{};f.each(a,function(e,g){unit=c.cssUnit(g);if(unit[0]>0)d[g]=unit[0]*b+unit[1]});return d}});f.fn.extend({effect:function(c){var a=k.apply(this,arguments),b={options:a[1],duration:a[2],callback:a[3]};a=b.options.mode;var d=f.effects[c];if(f.fx.off||!d)return a?this[a](b.duration,b.callback):this.each(function(){b.callback&&b.callback.call(this)});
return d.call(this,b)},_show:f.fn.show,show:function(c){if(l(c))return this._show.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="show";return this.effect.apply(this,a)}},_hide:f.fn.hide,hide:function(c){if(l(c))return this._hide.apply(this,arguments);else{var a=k.apply(this,arguments);a[1].mode="hide";return this.effect.apply(this,a)}},__toggle:f.fn.toggle,toggle:function(c){if(l(c)||typeof c==="boolean"||f.isFunction(c))return this.__toggle.apply(this,arguments);else{var a=k.apply(this,
arguments);a[1].mode="toggle";return this.effect.apply(this,a)}},cssUnit:function(c){var a=this.css(c),b=[];f.each(["em","px","%","pt"],function(d,e){if(a.indexOf(e)>0)b=[parseFloat(a),e]});return b}});f.easing.jswing=f.easing.swing;f.extend(f.easing,{def:"easeOutQuad",swing:function(c,a,b,d,e){return f.easing[f.easing.def](c,a,b,d,e)},easeInQuad:function(c,a,b,d,e){return d*(a/=e)*a+b},easeOutQuad:function(c,a,b,d,e){return-d*(a/=e)*(a-2)+b},easeInOutQuad:function(c,a,b,d,e){if((a/=e/2)<1)return d/
2*a*a+b;return-d/2*(--a*(a-2)-1)+b},easeInCubic:function(c,a,b,d,e){return d*(a/=e)*a*a+b},easeOutCubic:function(c,a,b,d,e){return d*((a=a/e-1)*a*a+1)+b},easeInOutCubic:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a+b;return d/2*((a-=2)*a*a+2)+b},easeInQuart:function(c,a,b,d,e){return d*(a/=e)*a*a*a+b},easeOutQuart:function(c,a,b,d,e){return-d*((a=a/e-1)*a*a*a-1)+b},easeInOutQuart:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a+b;return-d/2*((a-=2)*a*a*a-2)+b},easeInQuint:function(c,a,b,
d,e){return d*(a/=e)*a*a*a*a+b},easeOutQuint:function(c,a,b,d,e){return d*((a=a/e-1)*a*a*a*a+1)+b},easeInOutQuint:function(c,a,b,d,e){if((a/=e/2)<1)return d/2*a*a*a*a*a+b;return d/2*((a-=2)*a*a*a*a+2)+b},easeInSine:function(c,a,b,d,e){return-d*Math.cos(a/e*(Math.PI/2))+d+b},easeOutSine:function(c,a,b,d,e){return d*Math.sin(a/e*(Math.PI/2))+b},easeInOutSine:function(c,a,b,d,e){return-d/2*(Math.cos(Math.PI*a/e)-1)+b},easeInExpo:function(c,a,b,d,e){return a==0?b:d*Math.pow(2,10*(a/e-1))+b},easeOutExpo:function(c,
a,b,d,e){return a==e?b+d:d*(-Math.pow(2,-10*a/e)+1)+b},easeInOutExpo:function(c,a,b,d,e){if(a==0)return b;if(a==e)return b+d;if((a/=e/2)<1)return d/2*Math.pow(2,10*(a-1))+b;return d/2*(-Math.pow(2,-10*--a)+2)+b},easeInCirc:function(c,a,b,d,e){return-d*(Math.sqrt(1-(a/=e)*a)-1)+b},easeOutCirc:function(c,a,b,d,e){return d*Math.sqrt(1-(a=a/e-1)*a)+b},easeInOutCirc:function(c,a,b,d,e){if((a/=e/2)<1)return-d/2*(Math.sqrt(1-a*a)-1)+b;return d/2*(Math.sqrt(1-(a-=2)*a)+1)+b},easeInElastic:function(c,a,b,
d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);return-(h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g))+b},easeOutElastic:function(c,a,b,d,e){c=1.70158;var g=0,h=d;if(a==0)return b;if((a/=e)==1)return b+d;g||(g=e*0.3);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);return h*Math.pow(2,-10*a)*Math.sin((a*e-c)*2*Math.PI/g)+d+b},easeInOutElastic:function(c,a,b,d,e){c=1.70158;var g=
0,h=d;if(a==0)return b;if((a/=e/2)==2)return b+d;g||(g=e*0.3*1.5);if(h<Math.abs(d)){h=d;c=g/4}else c=g/(2*Math.PI)*Math.asin(d/h);if(a<1)return-0.5*h*Math.pow(2,10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)+b;return h*Math.pow(2,-10*(a-=1))*Math.sin((a*e-c)*2*Math.PI/g)*0.5+d+b},easeInBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*(a/=e)*a*((g+1)*a-g)+b},easeOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;return d*((a=a/e-1)*a*((g+1)*a+g)+1)+b},easeInOutBack:function(c,a,b,d,e,g){if(g==j)g=1.70158;
if((a/=e/2)<1)return d/2*a*a*(((g*=1.525)+1)*a-g)+b;return d/2*((a-=2)*a*(((g*=1.525)+1)*a+g)+2)+b},easeInBounce:function(c,a,b,d,e){return d-f.easing.easeOutBounce(c,e-a,0,d,e)+b},easeOutBounce:function(c,a,b,d,e){return(a/=e)<1/2.75?d*7.5625*a*a+b:a<2/2.75?d*(7.5625*(a-=1.5/2.75)*a+0.75)+b:a<2.5/2.75?d*(7.5625*(a-=2.25/2.75)*a+0.9375)+b:d*(7.5625*(a-=2.625/2.75)*a+0.984375)+b},easeInOutBounce:function(c,a,b,d,e){if(a<e/2)return f.easing.easeInBounce(c,a*2,0,d,e)*0.5+b;return f.easing.easeOutBounce(c,
a*2-e,0,d,e)*0.5+d*0.5+b}})}(jQuery);
/*
 * jQuery UI Effects Highlight 1.8.16
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Effects/Highlight
 *
 * Depends:
 *	jquery.effects.core.js
 */
(function(b){b.effects.highlight=function(c){return this.queue(function(){var a=b(this),e=["backgroundImage","backgroundColor","opacity"],d=b.effects.setMode(a,c.options.mode||"show"),f={backgroundColor:a.css("backgroundColor")};if(d=="hide")f.opacity=0;b.effects.save(a,e);a.show().css({backgroundImage:"none",backgroundColor:c.options.color||"#ffff99"}).animate(f,{queue:false,duration:c.duration,easing:c.options.easing,complete:function(){d=="hide"&&a.hide();b.effects.restore(a,e);d=="show"&&!b.support.opacity&&
this.style.removeAttribute("filter");c.callback&&c.callback.apply(this,arguments);a.dequeue()}})})}})(jQuery);