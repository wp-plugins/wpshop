jQuery('#titlediv, #postdivrich, #postdiv').prependTo('#wpshop_product_main_infos .inside');
jQuery('body #ed_toolbar').hide();
jQuery('#quicktags #ed_toolbar').show();


/*	Start product page edition part	*/
if(wp_version >= "3.1"){
	wpshop(".wpshop_input_datetime").datepicker();
	wpshop(".wpshop_input_datetime").datepicker("option", "dateFormat", "yy-mm-dd");
	wpshop(".wpshop_input_datetime").datepicker("option", "changeMonth", true);
	wpshop(".wpshop_input_datetime").datepicker("option", "changeYear", true);
	wpshop(".wpshop_input_datetime").datepicker("option", "navigationAsDateFormat", true);
}
