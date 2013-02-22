jQuery(document).ready(function(){
	if ( jQuery("#wpshop_low_stock_options_active").is(':checked') ) {
		jQuery("#low_stock_alert_configuration").show();
	}
	else {
		jQuery("#low_stock_alert_configuration").hide();
	}
	
	if ( jQuery("#wpshop_low_stock_alert_options_based_on_stock").is(':checked') ) {
		jQuery("#low_stock_alert_limit").show();
	}
	else {
		jQuery("#low_stock_alert_limit").hide();
	}
	
	jQuery("#wpshop_low_stock_options_active").live('click', function(){
		if ( jQuery("#wpshop_low_stock_options_active").is(':checked') ) {
			jQuery("#low_stock_alert_configuration").fadeIn('slow');
		}
		else {
			jQuery("#low_stock_alert_configuration").fadeOut('slow');;
		}
	});
	
	jQuery("#wpshop_low_stock_alert_options_based_on_stock").live('click', function(){
		if ( jQuery("#wpshop_low_stock_alert_options_based_on_stock").is(':checked') ) {
			jQuery("#low_stock_alert_limit").fadeIn('slow');
		}
	});
	
	jQuery("#wpshop_low_stock_alert_options_not_based_on_stock").live('click', function() {
		if ( jQuery("#wpshop_low_stock_alert_options_not_based_on_stock").is(':checked') ) {
			jQuery("#low_stock_alert_limit").fadeOut('slow');
		}
	});

});