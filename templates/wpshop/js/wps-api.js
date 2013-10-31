	function wps_modal(content, title){ 
		jQuery(document).load( MODAL_URL, function(response, status, xhr) {
		  if (status == "error") {		   
		  } else {
			  jQuery('body').append(response);
			  jQuery('#wps-modal-overlay').addClass('wps-modal-opened');
			  jQuery('#wps-modal-container').addClass('wps-modal-opened');		  	
			  jQuery('#wps-modal-body').html(content);		
			  jQuery('#wps-modal-header h3').html(title);	
		  }
		});		
	}
	function wps_modal_closer(){
		jQuery('#wps-modal-overlay').removeClass('wps-modal-opened');
		jQuery('#wps-modal-container').removeClass('wps-modal-opened');
		setTimeout(function() {
		jQuery('#wps-modal-overlay').remove();
		jQuery('#wps-modal-container').remove();
		}, wps_speed_slideUpDown);
	}

jQuery(document).ready(function(wpsjq) {

	////////////////////////////////////////////////////////////////////////////////////////////////////////// VARIABLES

	
	

	/* wps-modal *************************************************************************************/


	
	
	
	wpsjq(document).on('click','.wps-modal-opener',function(e){
		e.preventDefault();
		wps_modal('contenu lorem ipsum');
	});
	wpsjq(document).on('click','.wps-modal-close',function(e){
		e.preventDefault();
		wps_modal_closer();
	});
	/*
	wpsjq(document).on('click','#wps-modal-container',function(e){
		e.preventDefault();
		wps_modal_closer();
	});
	*/
	wpsjq(document).on('click','#wps-modal-overlay',function(e){
		e.preventDefault();
		wps_modal_closer();
	});
	wpsjq(document).keyup(function(e) {
	  if (e.keyCode == 27) {
	  	e.preventDefault();
		wps_modal_closer();
	  } 
	});

	/* ************************************************************************************************/

	/* wps-ui-tab *************************************************************************************/

	function wps_ui_tab_action(wps_tab,init){
		wpsjq(wps_tab).find('> div > div').hide();
		var wps_tab_item = wpsjq(wps_tab).find('> ul .wps-current-tab a').attr('data-toogle');
		if(init){
			wpsjq(wps_tab).find('> div .'+wps_tab_item).stop('true').show();
		}else{
			wpsjq(wps_tab).find('> div .'+wps_tab_item).stop('true').slideDown(wps_speed_slideUpDown);
		}
	}
	function wps_ui_tab_init(){
		wpsjq( '.wps-ui-tab' ).each(function( index ) {
		  wps_ui_tab_action(wpsjq(this),true);
		});
	}

	wps_ui_tab_init();

	wpsjq('.wps-ui-tab').on('click','> ul li a',function(e){
		e.preventDefault();
		wpsjq(this).closest('.wps-ui-tab').find('> ul li').removeClass('wps-current-tab');
		wpsjq(this).parent().addClass('wps-current-tab');
		wps_ui_tab_action(wpsjq(this).closest('.wps-ui-tab'));
	})

	/* ************************************************************************************************/

	/* wps-ui-tab *************************************************************************************/

	function wps_ui_accordion_action(wps_accordion,init){		
		
		if(init) {
			wpsjq(wps_accordion).find('> div > div').hide();
			wpsjq(wps_accordion).find('> div > div:first-child').show();
			wpsjq(wps_accordion).find('.wps-current-accordion').find('div').stop(true).show();
		} else {
			
			wpsjq(wps_accordion).find('> div > div').stop(true).slideUp(wps_speed_slideUpDown);
			wpsjq(wps_accordion).find('> div > div:first-child').stop(true).show();
			wpsjq(wps_accordion).find('.wps-current-accordion').find('div').stop(true).slideDown(wps_speed_slideUpDown);
		}
	}
	function wps_ui_accordion_init(){
		wpsjq( '.wps-ui-accordion' ).each(function( index ) {
		  wps_ui_accordion_action(wpsjq(this),true);
		});
	}

	wps_ui_accordion_init();

	wpsjq('.wps-ui-accordion').on('click','> div > div:first-child a',function(e){
		e.preventDefault();
		wpsjq(this).closest('.wps-ui-accordion').find('> div').removeClass('wps-current-accordion');
		wpsjq(this).parent().parent().addClass('wps-current-accordion');
		wps_ui_accordion_action(wpsjq(this).closest('.wps-ui-accordion'));
	})

	/* **************************************************************************************************/

	/* .wps-alert ***************************************************************************************/

	wpsjq('.wps-alert').on('click','.wps-close',function(e){
		e.preventDefault();
		wpsjq(this).parent().stop(true).slideUp(wps_speed_slideUpDown);
	})

	/* **************************************************************************************************/

	/* wps-list radio ***********************************************************************************/

	function wps_list_opener(item){ 
		item.parent().find('.wps-form-list-content').stop(true).slideUp(wps_speed_slideUpDown);
		item.find('.wps-form-list-content').stop(true).slideDown(wps_speed_slideUpDown);
	}

	wpsjq('.wps-form-list').on('click','li',function(e){
		e.preventDefault();
		wpsjq(this).parent().find('li').removeClass('wps-list-open');
		wpsjq(this).addClass('wps-list-open');
		wpsjq(this).find('input[type=radio]').attr('checked',true);
		wps_list_opener(wpsjq(this));
	});

	/* **************************************************************************************************/

	var wps_check_h_taskbars = 0;


	/* wps-taskbar-sticker ******************************************************************************/

	function init_wps_taskbar(){
		wpsjq('.wps-taskbar-sticker').wrap('<div class="wps-taskbar-sticker-container" />');
		wps_check_h_taskbars += wpsjq('.wps-taskbar-sticker-container').height();
	}

	function launch_wps_taskbar(){
		init_wps_taskbar();
		var iw = wpsjq('.wps-taskbar-sticker-container').width();
		var ih = wpsjq('.wps-taskbar-sticker-container').height();

		wpsjq(window).resize(function() {
		  	iw = wpsjq('.wps-taskbar-sticker-container').width();
			ih = wpsjq('.wps-taskbar-sticker-container').height();
		 	size_taskbar();
		});

		wpsjq(document).on('scroll',function(e){
			var tb = wpsjq('.wps-taskbar-sticker');
			var s = wpsjq(document).scrollTop();
			var tby = tb.offset().top;
			if(!(tb.hasClass( 'wps-sticked-taskbar' ))) {
				if (s > tby) {
					tb.addClass('wps-sticked-taskbar');
				} else {
					tb.removeClass('wps-sticked-taskbar');
				};
			}
			size_taskbar();
			pc = wpsjq('.wps-taskbar-sticker-container').offset().top;
			pt = wpsjq('.wps-taskbar-sticker').offset().top;
			if(pt-pc <= 0){
				tb.removeClass('wps-sticked-taskbar');
			}			
		});

		function size_taskbar(){
			wpsjq('.wps-taskbar-sticker').css('width',iw);
			wpsjq('.wps-taskbar-sticker-container').css('height',ih)
		}
	}
	if (wpsjq('.wps-taskbar-sticker').length) {
		launch_wps_taskbar();
	}	
	
	/* wps-sidebar-sticker ******************************************************************************/

	var wps_sticked_sidebar_init_offset = wps_check_h_taskbars+40;
	var wps_size_container;
	var wps_screen_size_checker = true;
	var target = wpsjq('.wps-sidebar-sticker');
	function init_wps_stiker_sidebar(){
		wpsjq('.wps-sidebar-sticker').wrap('<div class="wps-sidebar-sticker-container" />');
		wps_size_container = wpsjq('.wps-sidebar-sticker-container').width();
	}
	function launch_wps_sticker_sidebar(){
		init_wps_stiker_sidebar();		
		wpsjq(document).on('scroll',function(e){
			wps_postion_sticked_sidebar();
		});
	}
	function wps_postion_sticked_sidebar(){
		wps_size_container = wpsjq('.wps-sidebar-sticker-container').width();
		var th = target.height();
		var ch = target.parent().parent().parent().height();
		var tpy = target.offset().top
		var cpy = target.parent().offset().top
		var s = wpsjq(document).scrollTop();

		if((th >= wpsjq(window).height()-(200+wps_check_h_taskbars) ) || wpsjq('body').hasClass('wps-mobil')) {
			wps_screen_size_checker = false;
		}else{
			wps_screen_size_checker = true;
		}
		if(wps_screen_size_checker == true){
			if(!(target.hasClass( 'wps-sticked-sidebar' )) && !(target.hasClass( 'wps-sticked-bottom-sidebar' ))) {
				if (s > tpy-wps_sticked_sidebar_init_offset) {
					target.addClass('wps-sticked-sidebar');
					target.css('top',wps_sticked_sidebar_init_offset);					
				};				
			}
			if(tpy < cpy){
				target.removeClass('wps-sticked-sidebar');
			}						
		}else{
			target.removeClass('wps-sticked-sidebar');
		}
		target.css('width',wps_size_container);
	}
	wpsjq(window).resize(function() {		
		wps_postion_sticked_sidebar();		
	});
	if (wpsjq('.wps-sidebar-sticker').length) {
		launch_wps_sticker_sidebar();
	}	 



	/* **************************************************************************************************/

	/* wps_check_mobil size for JS***********************************************************************/

	function wps_check_mobil(){
		var b = wpsjq(document);	
		var bw = b.width();
		if(bw >= 780 ){
			wpsjq('body').removeClass('wps-mobil');
		}else {
			wpsjq('body').addClass('wps-mobil');
		}
	}

	wpsjq(window).resize(function() {
		wps_check_mobil();
		_wps_check_mobil_filters();
	});
	wps_check_mobil();


	/* **************************************************************************************************/

	/* wps-filters **************************************************************************************/

	var tf = wpsjq('.wps-filter-aside');

	function wps_filter_openclose(){
		if(tf.hasClass('wps-current-open')){
			tf.removeClass('wps-current-open');
			tf.find('.wps-filters-body').slideUp();

		}else{
			tf.addClass('wps-current-open');
			tf.find('.wps-filters-body').slideDown();
		}
	}
	if(tf.length){
		wpsjq('.wps-filter-aside .wps-filters-header').on('click','a',function(e){
			e.preventDefault();
			wps_filter_openclose();
		});
	}
	function _wps_check_mobil_filters (){
		if(!(wpsjq('body').hasClass('wps-mobil'))){
		}
	}

	/* **************************************************************************************************/

	/* UI slider */
	
	function wps_create_slider_ui(c){
		var range_min = c.attr('data-range-min');
		var range_max = c.attr('data-range-max');
		var v_min = c.attr('data-min');
		var v_max = c.attr('data-max');
		if(( range_min != undefined ) && ( range_max != undefined )) {
			if( v_min == undefined ){
				v_min = range_min;
			}
			if( v_max == undefined ){
				v_max = range_max;
			}
			c.wrap('<div class="wps-slider-ui-container" >');
			c.before('<span class="wps-slider-ui-field wps-slider-ui-field-for">'+v_min+'</span>');
			c.after('<span class="wps-slider-ui-field wps-slider-ui-field-to">'+v_max+'</span>');
			c.after('<input type="hidden" class="wps-slider-ui-field-min" value="'+v_min+'">');
			c.after('<input type="hidden" class="wps-slider-ui-field-max" value="'+v_max+'">');
			c.noUiSlider({
			    range: [range_min, range_max]
			   ,start: [v_min, v_max]
			   ,handles: 2
			   ,slide: function(){
			      var values = wpsjq(this).val();
			      var min = Math.round(values[0]);
			      var max = Math.round(values[1]);
			      c.parent().find('.wps-slider-ui-field-for').text(min);
			      c.parent().find('.wps-slider-ui-field-to').text(max);
			      c.parent().find('.wps-slider-ui-field-min').attr('value',min);
			      c.parent().find('.wps-slider-ui-field-max').attr('value',max);
			   }
			});
		}
	}

	if(wpsjq('.wps-slider-ui').length){
		wpsjq('.wps-slider-ui').each(function( index ) {
		  wps_create_slider_ui(wpsjq(this));
		});
	}

	/* Toogle filter groups */

	function wps_filter_toogle_animate (c){
		if(c.hasClass('wps-filter-group-open')){
			c.find('.wps-filter-body').slideDown(wps_speed_slideUpDown);

		}else {
			c.find('.wps-filter-body').slideUp(wps_speed_slideUpDown);
		}
	}

	function wps_create_filter_toogle(c){

		c.find('.wps-filter-header h3').append('<span class="wps-plus">+</span><span class="wps-moins">-</span>');
		c.find('.wps-filter-header h3').wrapInner('<a href="#"/>');
		
		c.on('click','.wps-filter-header h3 a',function(e){
			e.preventDefault();
			c.toggleClass('wps-filter-group-open');
			wps_filter_toogle_animate(c);
		})
		wps_filter_toogle_animate(c);
	}

	if(wpsjq('.wps-filter-group-toogle').length){
		wpsjq('.wps-filter-group-toogle').each(function( index ) {
		  wps_create_filter_toogle(wpsjq(this));
		});
	}

	/* **************************************************************************************************/

	/* wps-tool-bar */

	function wps_toolbar_action(wps_toolbar,init){		
		var wps_toolbar_item = wpsjq(wps_toolbar).find('.wps-toolbar-header ul .wps-toolbar-current a').attr('data-toogle');
		if(init){
			wpsjq(wps_toolbar).find('.wps-toolbar-body > div').hide();
			wpsjq(wps_toolbar).find('.wps-toolbar-body > .'+wps_toolbar_item).stop('true').show();
		}else{
			wpsjq(wps_toolbar).find('.wps-toolbar-body > div').slideUp(wps_speed_slideUpDown);
			wpsjq(wps_toolbar).find('.wps-toolbar-body > .'+wps_toolbar_item).stop('true').slideDown(wps_speed_slideUpDown);
		}
	}
	function wps_toolbar_closer(wl){
		var wps_toolbar_item = wl.attr('data-toogle');
		wl.parent().removeClass('wps-toolbar-current');
		wl.closest('.wps-toolbar').find('.wps-toolbar-body .'+wps_toolbar_item).stop(true).slideUp(wps_speed_slideUpDown);
	}
	function wps_toolbar_init(){
		wps_toolbar_action(wpsjq( '.wps-toolbar' ),true);
	}

	wps_toolbar_init();

	wpsjq('.wps-toolbar').on('click','.wps-toolbar-header ul li a',function(e){
		e.preventDefault();
		if(!(wpsjq(this).parent().hasClass('wps-toolbar-current'))){
			wpsjq(this).closest('.wps-toolbar-header').find('> ul li').removeClass('wps-toolbar-current');
			wpsjq(this).parent().addClass('wps-toolbar-current');
			wps_toolbar_action(wpsjq(this).closest('.wps-toolbar'));			
		} else {
			wps_toolbar_closer(wpsjq(this));
		}
		
	})
	wpsjq('.wps-toolbar').on('click','.wps-close',function(){
		var c = wpsjq(this).closest('.wps-toolbar-body').parent().find('.wps-toolbar-header > ul li');
		c.removeClass('wps-toolbar-current');
		c.closest('.wps-toolbar').find('.wps-toolbar-body > div').slideUp(wps_speed_slideUpDown);
	})

	/* **************************************************************************************************/


	/* Multi select */	

	/*function wps_check_tags(c){
		var title = c.text();
		var value = c.val();
		console.log(title);
		console.log(value);
		c.closest('.wps-multi-select-tagmode-container').find('.wps-multi-select-tagmode-tags').append('<span class="wps-multi-select-tag">'+title+'<button type="button" class="wps-close"></button></span>');
		//t = v.closest('.wps-multi-select-tagmode-container');
		//console.log(t);
	}

	function wps_create_multiselect_tagmode(c){

		c.wrap('<div class="wps-multi-select-tagmode-container" />');
		c.after('<div class="wps-multi-select-tagmode-tags"></div>');

		c.change(function() {
			var v = wpsjq(this).find("option:selected");
			//var t = wpsjq(this).find("option:selected");
			wps_check_tags(v);
			//t.detach();
			//t.attr('disabled','disabled');

		  	//alert(v);
		});


	}

	if(wpsjq('.wps-multi-select-tagmode').length){
		wpsjq('.wps-multi-select-tagmode').each(function( index ) {
		  wps_create_multiselect_tagmode(wpsjq(this));
		});
	}*/

	/* */


});