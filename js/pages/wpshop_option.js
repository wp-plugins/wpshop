wpshop(document).ready(function(){


	function gerer_affichage_element (elt,test) {
		elt_display = '.'+elt.attr('id')+'_content';

		if(elt.is(':checked')){
			jQuery(elt_display).stop(true).fadeIn();
			if(test){
				alert('checked');
			}
		}else{
			jQuery(elt_display).stop(true).fadeOut();
			if(test){
				alert('unchecked'+elt_display);
			}
		}
		
	}


	jQuery("#options-tabs").tabs();
	jQuery("#options-tabs li a").click(function(){
		jQuery("#wpshop_option_form").attr("action", "options.php"+jQuery(this).attr("href"));
	});
	jQuery(".slider_variable").parent().parent().addClass('ui-slider-row');

	jQuery("#paymentByPaypal").change(function(){
		gerer_affichage_element(jQuery(this));
	})
	jQuery("#paymentByCheck").change(function(){
		gerer_affichage_element(jQuery(this));
	})

	jQuery("#wpshop_shipping_fees_freefrom_activation").change(function(){
		gerer_affichage_element(jQuery(this));
	})
	jQuery("#custom_shipping_active").change(function(){
		gerer_affichage_element(jQuery(this));
	});

	gerer_affichage_element(jQuery("#paymentByPaypal"));
	gerer_affichage_element(jQuery("#paymentByCheck"));
	//gerer_affichage_element(jQuery("#paymentByPaypal"),jQuery("#paymentByPaypal").parent().find('div'));
	//gerer_affichage_element(jQuery("#paymentByCheck"),jQuery("#paymentByCheck").parent().find('div'));
	//gerer_affichage_element(jQuery("#wpshop_shipping_fees_freefrom_activation"),jQuery("#slider-range_free_from").closest('tr'));


});