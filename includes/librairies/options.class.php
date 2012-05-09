<?php
/**
* Plugin option manager
* 
* Define the different method to manage the different options into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different options into the plugin
* @package wpshop
* @subpackage librairies
*/

/** Stocke les erreurs de saisies */
$options_errors = array();

class wpshop_options
{
	/**
	*	Declare the different options for the plugin	
	*/
	function add_options() {
		global $wpshop_display_option;

		/* Display	*/
		register_setting('wpshop_options', 'wpshop_display_option', array('wpshop_display_options', 'part_validator'));
		$wpshop_display_option = get_option('wpshop_display_option');
			add_settings_section('wpshop_display_options_sections', __('Display options', 'wpshop'), array('wpshop_display_options', 'part_explanation'), 'wpshop_display_option');
				/*	Add the different field option	*/
				add_settings_field('wpshop_display_cat_sheet_output', __('Display type for category page', 'wpshop'), array('wpshop_display_options', 'wpshop_display_cat_sheet_output'), 'wpshop_display_option', 'wpshop_display_options_sections');		
				add_settings_field('wpshop_display_list_type', __('Display type for element list', 'wpshop'), array('wpshop_display_options', 'wpshop_display_list_type'), 'wpshop_display_option', 'wpshop_display_options_sections');		
				add_settings_field('wpshop_display_grid_element_number', __('Number of element by line for grid mode', 'wpshop'), array('wpshop_display_options', 'wpshop_display_grid_element_number'), 'wpshop_display_option', 'wpshop_display_options_sections');
				add_settings_field('wpshop_display_element_per_page', __('Number of element per page', 'wpshop'), array('wpshop_display_options', 'wpshop_display_element_per_page'), 'wpshop_display_option', 'wpshop_display_options_sections');
				add_settings_field('wpshop_display_reset_template_element', __('Reset template file', 'wpshop'), array('wpshop_display_options', 'wpshop_display_reset_template_element'), 'wpshop_display_option', 'wpshop_display_options_sections');		

		/* Catalog */
		/* Product */
		register_setting('wpshop_options', 'wpshop_catalog_product_option', array('wpshop_options', 'wpshop_options_validate_catalog_product_option'));
			add_settings_section('wpshop_catalog_product_section', __('Products', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_product_option');
				add_settings_field('wpshop_catalog_product_slug', __('Products common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_product_slug_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
				add_settings_field('wpshop_catalog_product_supported_element', __('Product supported element', 'wpshop'), array('wpshop_options', 'wpshop_catalog_product_supported_element_field'), 'wpshop_catalog_product_option', 'wpshop_catalog_product_section');
		/* Categories */
		register_setting('wpshop_options', 'wpshop_catalog_categories_option', array('wpshop_options', 'wpshop_options_validate_catalog_categories_option'));
			add_settings_section('wpshop_catalog_categories_section', __('Categories', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_catalog_categories_option');
				add_settings_field('wpshop_catalog_categories_slug', __('Categories common rewrite param', 'wpshop'), array('wpshop_options', 'wpshop_catalog_categories_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');
				add_settings_field('wpshop_catalog_no_category_slug', __('Default category slug for unassociated product', 'wpshop'), array('wpshop_options', 'wpshop_catalog_no_category_slug_field'), 'wpshop_catalog_categories_option', 'wpshop_catalog_categories_section');

		/* Company */
		add_settings_section('wpshop_company_info', __('Company info', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_company_info');
			register_setting('wpshop_options', 'wpshop_company_info', array('wpshop_options', 'wpshop_options_validate_company_info'));
			add_settings_field('wpshop_company_legal_statut', __('Legal status', 'wpshop'), array('wpshop_options', 'wpshop_company_legal_statut_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_capital', __('Capital', 'wpshop'), array('wpshop_options', 'wpshop_company_capital_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_name', __('Company name', 'wpshop'), array('wpshop_options', 'wpshop_company_name_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_street', __('Street', 'wpshop'), array('wpshop_options', 'wpshop_company_street_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_postcode', __('Postcode', 'wpshop'), array('wpshop_options', 'wpshop_company_postcode_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_city', __('City', 'wpshop'), array('wpshop_options', 'wpshop_company_city_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_country', __('Country', 'wpshop'), array('wpshop_options', 'wpshop_company_country_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_tva_intra', __('TVA Intracommunautaire', 'wpshop'), array('wpshop_options', 'wpshop_company_tva_intra_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_telephone', __('Phone', 'wpshop'), array('wpshop_options', 'wpshop_company_phone_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_rcs', __('RCS', 'wpshop'), array('wpshop_options', 'wpshop_company_rcs_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_siret', __('SIRET', 'wpshop'), array('wpshop_options', 'wpshop_company_siret_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_siren', __('SIREN', 'wpshop'), array('wpshop_options', 'wpshop_company_siren_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_fax', __('Fax', 'wpshop'), array('wpshop_options', 'wpshop_company_fax_field'), 'wpshop_company_info', 'wpshop_company_info');
			add_settings_field('wpshop_company_member_of_a_approved_management_center', __('Member of a management center', 'wpshop'), array('wpshop_options', 'wpshop_company_member_of_a_approved_management_center_field'), 'wpshop_company_info', 'wpshop_company_info');
		
		/* Payments */
		add_settings_section('wpshop_paymentMethod', __('Payment method', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_paymentMethod');
			register_setting('wpshop_options', 'wpshop_paymentMethod', array('wpshop_options', 'wpshop_options_validate_paymentMethod'));
			
			add_settings_field('wpshop_payment_return', __('Payments return', 'wpshop'), array('wpshop_options', 'wpshop_payment_return_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			add_settings_field('wpshop_payment_paypal', __('Paypal payment', 'wpshop'), array('wpshop_options', 'wpshop_paypal_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			add_settings_field('wpshop_payment_checks', __('Checks payment', 'wpshop'), array('wpshop_options', 'wpshop_checks_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			if(WPSHOP_PAYMENT_METHOD_CIC) add_settings_field('wpshop_payment_cic', __('CIC payment', 'wpshop'), array('wpshop_options', 'wpshop_cic_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
			
			register_setting('wpshop_options', 'wpshop_paymentAddress', array('wpshop_options', 'wpshop_options_validate_paymentAddress'));
			register_setting('wpshop_options', 'wpshop_paypalEmail', array('wpshop_options', 'wpshop_options_validate_paypalEmail'));
			register_setting('wpshop_options', 'wpshop_paypalMode', array('wpshop_options', 'wpshop_options_validate_paypalMode'));
			if(WPSHOP_PAYMENT_METHOD_CIC) register_setting('wpshop_options', 'wpshop_cmcic_params', array('wpshop_options', 'wpshop_options_validate_cmcic_params'));
			register_setting('wpshop_options', 'wpshop_payment_return_url', array('wpshop_options', 'wpshop_options_validate_return_url'));
			
			register_setting('wpshop_options', 'wpshop_shop_default_currency', array('wpshop_options', 'wpshop_options_validate_default_currency'));
			add_settings_field('wpshop_shop_default_currency', __('Currency', 'wpshop'), array('wpshop_options', 'wpshop_shop_default_currency_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
		
		/* Billing */
		add_settings_section('wpshop_billing_info', __('Billing settings', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_billing_info');
			register_setting('wpshop_options', 'wpshop_billing_number_figures', array('wpshop_options', 'wpshop_options_validate_billing_number_figures'));
			add_settings_field('wpshop_billing_number_figures', __('Number of figures', 'wpshop'), array('wpshop_options', 'wpshop_billing_number_figures_field'), 'wpshop_billing_info', 'wpshop_billing_info');
		
		/* Emails */
		add_settings_section('wpshop_emails', __('Email addresses', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_emails');
			register_setting('wpshop_options', 'wpshop_emails', array('wpshop_options', 'wpshop_options_validate_emails'));
			add_settings_field('wpshop_noreply_email', __('Mails answers address email', 'wpshop'), array('wpshop_options', 'wpshop_noreply_email_field'), 'wpshop_emails', 'wpshop_emails');
			add_settings_field('wpshop_contact_email', __('Contact email', 'wpshop'), array('wpshop_options', 'wpshop_contact_email_field'), 'wpshop_emails', 'wpshop_emails');
			
		/* Messages */
		add_settings_section('wpshop_messages', __('Messages', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_messages');
			// Object
			register_setting('wpshop_options', 'WPSHOP_SIGNUP_MESSAGE_OBJECT', array('wpshop_options', 'wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE_OBJECT'));
			add_settings_field('WPSHOP_SIGNUP_MESSAGE_OBJECT', __('Signup - Object', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_SIGNUP_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
			// Message
			register_setting('wpshop_options', 'WPSHOP_SIGNUP_MESSAGE', array('wpshop_options', 'wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE'));
			add_settings_field('WPSHOP_SIGNUP_MESSAGE', __('Signup - Message', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_SIGNUP_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
			
			// Object
			register_setting('wpshop_options', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_options', 'wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'));
			add_settings_field('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', __('Order confirmation - Object', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
			// Message
			register_setting('wpshop_options', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('wpshop_options', 'wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE'));
			add_settings_field('WPSHOP_ORDER_CONFIRMATION_MESSAGE', __('Order confirmation - Message', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
			
			// Object
			register_setting('wpshop_options', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_options', 'wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'));
			add_settings_field('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Payment confirmation - Object', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
			// Message
			register_setting('wpshop_options', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('wpshop_options', 'wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'));
			add_settings_field('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', __('Payment confirmation - Message', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
			
			// Object
			register_setting('wpshop_options', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_options', 'wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'));
			add_settings_field('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', __('Others payment confirmation - Object', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
			// Message
			register_setting('wpshop_options', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('wpshop_options', 'wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'));
			add_settings_field('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', __('Others payment confirmation - Message', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');
			
			// Object
			register_setting('wpshop_options', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', array('wpshop_options', 'wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'));
			add_settings_field('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', __('Shipping confirmation - Object', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT_field'), 'wpshop_messages', 'wpshop_messages');
			// Message
			register_setting('wpshop_options', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', array('wpshop_options', 'wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'));
			add_settings_field('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', __('Shipping confirmation - Message', 'wpshop'), array('wpshop_options', 'wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_field'), 'wpshop_messages', 'wpshop_messages');

		/* Shipping section */
		add_settings_section('wpshop_shipping_rules', __('Shipping', 'wpshop'), array('wpshop_options', 'plugin_section_text'), 'wpshop_shipping_rules');
			register_setting('wpshop_options', 'wpshop_shipping_rules', array('wpshop_options', 'wpshop_options_validate_shipping_rules'));
			add_settings_field('wpshop_shipping_rule_by_min_max', __('Min-Max shipping fees', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_by_min_max_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_from', __('Free from', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_free_from_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_shipping', __('Set shipping as free', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_free_shipping'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_shipping_from_date', '', array('wpshop_options', 'wpshop_shipping_rule_free_shipping_from_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			add_settings_field('wpshop_shipping_rule_free_shipping_to_date', '', array('wpshop_options', 'wpshop_shipping_rule_free_shipping_to_date'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_weight', __('By weight', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_by_weight_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_percent', __('By percent', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_by_percent_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');
			//add_settings_field('wpshop_shipping_rule_by_nb_of_items', __('By number of items', 'wpshop'), array('wpshop_options', 'wpshop_shipping_rule_by_nb_of_items_field'), 'wpshop_shipping_rules', 'wpshop_shipping_rules');

		flush_rewrite_rules();
	}
	
	// Common section description
	function plugin_section_text() {
		echo '';
	}
	
	function wpshop_shipping_rule_by_min_max_field() {
		$id = 1;
		$currency_code = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$rules = get_option('wpshop_shipping_rules',array());
		if(empty($rules)) $rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		
		echo '<script type="text/javascript">
		wpshop(document).ready(function(){
			jQuery("#slider-range_min_max").slider({
				range: true,
				min: 0,
				max: 100,
				values: [ '.$rules['min_max']['min'].', '.$rules['min_max']['max'].' ],
				slide: function( event, ui ) {
					jQuery("#amount_min").val(ui.values[0]+" '.$currency_code.'");
					jQuery("#amount_max").val(ui.values[1]+" '.$currency_code.'");
				}
			});
			jQuery("#amount_min").val("'.$rules['min_max']['min'].'"+" '.$currency_code.'");
			jQuery("#amount_max").val("'.$rules['min_max']['max'].'"+" '.$currency_code.'");
		});
		</script>
		<input type="text" id="amount_min" name="wpshop_shipping_rules[min_max][min]" style="float:left;width:98px;" />
		<input type="text" id="amount_max" name="wpshop_shipping_rules[min_max][max]" style="float:left;width:99px;" />
		<div id="slider-range_min_max" style="width:500px;margin:7px 0 0 10px;" class="slider_variable"></div>';
	}
	function wpshop_shipping_rule_free_from_field() {
		$currency_code = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$rules = get_option('wpshop_shipping_rules',array());
		$activated = true;
		
		$default_rules = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
		if(empty($rules)) $rules = $default_rules;
		elseif($rules['free_from']==-1) { $rules['free_from']=$default_rules['free_from']; $activated=false; }
		
		echo '<script type="text/javascript">
		wpshop(document).ready(function(){
			jQuery("#slider-range_free_from").slider({
				min: 0,
				max: 200,
				values: ['.$rules['free_from'].'],
				slide: function( event, ui ) {
					jQuery("#amount_free_from").val(ui.values[ 0 ]+" '.$currency_code.' ('.WPSHOP_PRODUCT_PRICE_PILOT.')");
				}
			});
			jQuery("#amount_free_from").val("'.$rules['free_from'].'"+" '.$currency_code.' ('.WPSHOP_PRODUCT_PRICE_PILOT.')");
			// Disabled/Enabled the slider when input is clicked
			jQuery("input[name=free_from_active]").click(function() {
				var disabled = jQuery( "#slider-range_free_from" ).slider( "option", "disabled" );
				if(disabled) {
					jQuery("#slider-range_free_from").slider("option","disabled", false);
					jQuery("#amount_free_from").prop("disabled", false);
				}
				else {
					jQuery("#slider-range_free_from").slider("option","disabled", true);
					jQuery("#amount_free_from").prop("disabled", true);
				}
			});
		});
		</script>
		<label style="float:right;"><input type="checkbox" name="free_from_active" '.($activated?'checked="checked"':null).' /> '.__('Active','wpshop').'</label>
		<input type="text" id="amount_free_from" name="wpshop_shipping_rules[free_from]" style="width:200px;float:left;" />
		<div id="slider-range_free_from" style="width:500px;margin:7px 0 0 10px;" class="slider_variable"></div>';
		
		if(!$activated) {
			echo '<script type="text/javascript">wpshop(document).ready(function(){jQuery("#slider-range_free_from").slider("option","disabled", true);jQuery("#amount_free_from").prop("disabled", true);});</script>';
		}
	}

	function wpshop_shipping_rule_free_shipping() {
		$rules = get_option('wpshop_shipping_rules',array());
		
		echo '<input type="checkbox" id="wpshop_shipping_rule_free_shipping" ' . (isset($rules['wpshop_shipping_rule_free_shipping']) && ($rules['wpshop_shipping_rule_free_shipping']) ? ' checked="checked" ' : '') . ' name="wpshop_shipping_rules[wpshop_shipping_rule_free_shipping]" />';
		/* '<br/>
		' . __('If you want to set free shipping for a given period, specify date below', 'wpshop') . '<br/>
		' . __('Free shipping from', 'wpshop') . '&nbsp;<input type="text" id="wpshop_shipping_rule_free_shipping_from_date" value="' . $rules['wpshop_shipping_rule_free_shipping_from_date'] . '" name="wpshop_shipping_rules[wpshop_shipping_rule_free_shipping_from_date]" />&nbsp;&nbsp;' . __('Free shipping to', 'wpshop') . '&nbsp;<input type="text" id="wpshop_shipping_rule_free_shipping_to_date" value="' . $rules['wpshop_shipping_rule_free_shipping_to_date'] . '" name="wpshop_shipping_rules[wpshop_shipping_rule_free_shipping_to_date]" />
		<script type="text/javascript" >
			wpshop(document).ready(function(){
				jQuery("#wpshop_shipping_rule_free_shipping_from_date").datepicker("option", "dateFormat", "yy-mm-dd");
				jQuery("#wpshop_shipping_rule_free_shipping_to_date").datepicker();
			});
		</script>'; */
	}
	function wpshop_shipping_rule_free_shipping_from_date() {
	}
	function wpshop_shipping_rule_free_shipping_to_date() {
	}
	
	function wpshop_shipping_rule_by_weight_field() {
		$currency_code = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="1" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_weight]" cols="80" rows="4">'.$rules['by_weight'].'</textarea><br />'.__('Example','wpshop').' : 500:5.45,1000:7.20,2000:10.30<br />'.__('Means','wpshop').' : 0 <= Weight < 500 (g) => 5.45 '.$currency_code.' etc..';
	}
	
	function wpshop_shipping_rule_by_percent_field() {
		$currency_code = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="2" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_percent]" cols="80" rows="4">'.$rules['by_percent'].'</textarea><br />'.__('Example','wpshop').' : 100:8,200:6,300:4<br />'.__('Means','wpshop').' : 0 <= Amount < 100 ('.$currency_code.') => Shipping = 8% etc..';
	}
	
	function wpshop_shipping_rule_by_nb_of_items_field() {
		$currency_code = get_option('wpshop_shop_default_currency',WPSHOP_SHOP_DEFAULT_CURRENCY);
		$rules = get_option('wpshop_shipping_rules',array());
		echo '<input type="text" name="priority[]" value="3" style="float:right;width:50px;" />';
		echo '<textarea name="wpshop_shipping_rules[by_nb_of_items]" cols="80" rows="4">'.$rules['by_nb_of_items'].'</textarea><br />'.__('Example','wpshop').' : 5:10,10:12,20:15<br />'.__('Means','wpshop').' : 0 <= Number of items < 5 (items) => 10 '.$currency_code.' etc..';
	}
	
	function wpshop_options_validate_shipping_rules($input) {
		$min = preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['min_max']['min']);
		$max = preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['min_max']['max']);

		$new_input['min_max'] = array('min'=>$min,'max'=>$max);
		if(isset($_POST['free_from_active']) && $_POST['free_from_active']=='on')
			$new_input['free_from'] = preg_replace('#\D*?(\d+(\.\d+)?)\D*#', '$1', $input['free_from']);
		else $new_input['free_from'] = -1;

		$new_input['wpshop_shipping_rule_free_shipping'] = $input['wpshop_shipping_rule_free_shipping'];
		//add_settings_error( 'fields_main_input', 'texterror', 'Incorrect value entered!', 'error' );
		
		return $new_input;
	}
	
	/* ------------------------------ */
	/* --------- CATALOG INFO ------- */
	/* ------------------------------ */
	function wpshop_catalog_product_slug_field(){
		$options = get_option('wpshop_catalog_product_option');
		echo '<input type="text" name="wpshop_catalog_product_option[wpshop_catalog_product_slug]" value="' . (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : WPSHOP_CATALOG_PRODUCT_SLUG) . '" />';
	}
	function wpshop_catalog_product_supported_element_field(){
		$output = '';
		$options = get_option('wpshop_catalog_product_option');
		global $register_post_type_support, $mandatory_register_post_type_support;

		foreach($register_post_type_support as $supported_element){
			$checkbox_state = (!empty($options['wpshop_catalog_product_supported_element']) && (in_array($supported_element, $options['wpshop_catalog_product_supported_element']))) ? ' checked="checked" ' : ' ';
			$checkbox_state = in_array($supported_element, $mandatory_register_post_type_support) ? ' checked="checked" disabled="disabled" ' : $checkbox_state;
			$output .= '<div class="wpshop_register_post_type_input_container" ><input type="checkbox" value="' . $supported_element . '"' . $checkbox_state . 'name="wpshop_catalog_product_option[wpshop_catalog_product_supported_element][]" id="id_' . $supported_element . '" /><label class="wpshop_catalog_option" for="id_' . $supported_element . '" >' . __($supported_element, 'wpshop') . '</label></div>';
		}
		echo $output;
	}
	function wpshop_catalog_categories_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_categories_slug]" value="' . (!empty($options['wpshop_catalog_categories_slug']) ? $options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_CATEGORIES_SLUG) . '" />';
	}
	function wpshop_catalog_no_category_slug_field(){
		$options = get_option('wpshop_catalog_categories_option');
		echo '<input type="text" name="wpshop_catalog_categories_option[wpshop_catalog_no_category_slug]" value="' . (!empty($options['wpshop_catalog_no_category_slug']) ? $options['wpshop_catalog_no_category_slug'] : WPSHOP_CATALOG_PRODUCT_NO_CATEGORY) . '" />';
	}
	/* Processing */
	function wpshop_options_validate_catalog_product_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}
	function wpshop_options_validate_catalog_categories_option($input){
		foreach($input as $option_key => $option_value){
			switch($option_key){
				default:
					$new_input[$option_key] = $option_value;
				break;
			}
		}

		return $new_input;
	}


	/* ------------------------------ */
	/* --------- COMPANY INFO ------- */
	/* ------------------------------ */
	function wpshop_company_legal_statut_field() {
		$options = get_option('wpshop_company_info');
		
		$legal_status = array(
			'autoentrepreneur' => 'Auto-Entrepreneur',
			'eurl' => 'EURL',
			'sarl' => 'SARL',
			'sa' => 'SA',
			'sas' => 'SAS',
		);
		$select_legal_statut = '<select name="wpshop_company_info[company_legal_statut]">';
		foreach($legal_status as $key=>$value) {
			$selected = $options['company_legal_statut']==$key ? ' selected="selected"' : null;
			$select_legal_statut .= '<option value="'.$key.'"'.$selected.'>'.__($value,'wpshop').'</option>';
		}
		$select_legal_statut .= '</select>';
		echo $select_legal_statut;
	}
	function wpshop_company_capital_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_capital]" type="text" value="'.$options['company_capital'].'" />';
	}
	function wpshop_company_name_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_name]" type="text" value="'.$options['company_name'].'" />';
	}
	function wpshop_company_street_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_street]" type="text" value="'.$options['company_street'].'" />';
	}
	function wpshop_company_postcode_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_postcode]" type="text" value="'.$options['company_postcode'].'" />';
	}
	function wpshop_company_city_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_city]" type="text" value="'.$options['company_city'].'" />';
	}
	function wpshop_company_country_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_country]" type="text" value="'.$options['company_country'].'" />';
	}
	function wpshop_company_tva_intra_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_tva_intra]" type="text" value="'.$options['company_tva_intra'].'" />';
	}
	function wpshop_company_phone_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_phone]" type="text" value="'.$options['company_phone'].'" />';
	}
	function wpshop_company_rcs_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_rcs]" type="text" value="'.$options['company_rcs'].'" />';
	}
	function wpshop_company_siret_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_siret]" type="text" value="'.$options['company_siret'].'" />';
	}
	function wpshop_company_siren_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_siren]" type="text" value="'.$options['company_siren'].'" />';
	}
	function wpshop_company_fax_field() {
		$options = get_option('wpshop_company_info');
		echo '<input name="wpshop_company_info[company_fax]" type="text" value="'.$options['company_fax'].'" />';
	}
	function wpshop_company_member_of_a_approved_management_center_field() {
		$options = get_option('wpshop_company_info');
		$checked = $options['company_member_of_a_approved_management_center'] ? 'checked="checked"' : null;
		echo '<input name="wpshop_company_info[company_member_of_a_approved_management_center]" type="checkbox" '.$checked.' /> '.__('Member of an approved management center, accepting as such payments by check.', 'wpshop');
	}
	/* Processing */
	function wpshop_options_validate_company_info($input) {
		if(isset($input['company_member_of_a_approved_management_center']) && $input['company_member_of_a_approved_management_center']=='on') {
			$input['company_member_of_a_approved_management_center'] = 1;
		}
		return $input;
	}
	
	/* -------------------------------- */
	/* --------- PAYMENT METHOD ------- */
	/* -------------------------------- */
	function wpshop_paymentByPaypal_field() {
		echo '';
	}
	function wpshop_payment_return_field() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		echo '<label class="simple_right">'.__('Payment return url','wpshop').'</label> <input name="wpshop_payment_return_url" type="text" value="'.$url.'" /><br /><b>'.__('This page is use in order to notify the customer that its order has been recorded or cancelled.','wpshop').'</b>';
	}
	
	function wpshop_paypal_field() {
		$options = get_option('wpshop_paymentMethod');
		$paypalEmail = get_option('wpshop_paypalEmail');
		$paypalMode = get_option('wpshop_paypalMode',0);
		
		echo '<input type="checkbox" name="wpshop_paymentMethod[paypal]" id="paymentByPaypal" '.($options['paypal']?'checked="checked"':null).' /> '.__('Allow <strong>Paypal</strong>', 'wpshop').'<br />
			<label class="simple_right">'.__('Business email','wpshop').'</label> <input name="wpshop_paypalEmail" type="text" value="'.$paypalEmail.'" /><br />
			<label class="simple_right">'.__('Mode','wpshop').'</label>
			<select name="wpshop_paypalMode">
				<option value="normal"'.(($paypalMode=='sandbox') ? null : ' selected="selected"').'>Classique</option>
				<option value="sandbox"'.(($paypalMode=='sandbox') ? ' selected="selected"' : null).'>Sandbox</option>
			</select>
		';
	}
	function wpshop_checks_field() {
		$options = get_option('wpshop_paymentMethod');
		$company = get_option('wpshop_paymentAddress');
		
		echo '<input type="checkbox" name="wpshop_paymentMethod[checks]" id="paymentByPaypal" '.($options['checks']?'checked="checked"':null).' /> '.__('Allow <strong>Checks</strong>', 'wpshop').'<br />';
		echo '<label class="simple_right">'.__('Company name', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_name]" type="text" value="'.$company['company_name'].'" /><br />';
		echo '<label class="simple_right">'.__('Street', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_street]" type="text" value="'.$company['company_street'].'" /><br />';
		echo '<label class="simple_right">'.__('Postcode', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_postcode]" type="text" value="'.$company['company_postcode'].'" /><br />';
		echo '<label class="simple_right">'.__('City', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_city]" type="text" value="'.$company['company_city'].'" /><br />';
		echo '<label class="simple_right">'.__('Country', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_country]" type="text" value="'.$company['company_country'].'" />';
	}
	
	function wpshop_cic_field() {
		$cmcic_params = get_option('wpshop_cmcic_params', array());
		
		echo '<label class="simple_right">'.__('Key', 'wpshop').'</label> <input name="wpshop_cmcic_params[cle]" type="text" value="'.$cmcic_params['cle'].'" /><br />';
		echo '<label class="simple_right">'.__('TPE', 'wpshop').'</label> <input name="wpshop_cmcic_params[tpe]" type="text" value="'.$cmcic_params['tpe'].'" /><br />';
		echo '<label class="simple_right">'.__('Version', 'wpshop').'</label> <input name="wpshop_cmcic_params[version]" type="text" value="'.$cmcic_params['version'].'" /> => 3.0<br />';
		echo '<label class="simple_right">'.__('Serveur', 'wpshop').'</label> <input name="wpshop_cmcic_params[serveur]" type="text" value="'.$cmcic_params['serveur'].'" /><br />';
		echo '<label class="simple_right">'.__('Company code', 'wpshop').'</label> <input name="wpshop_cmcic_params[codesociete]" type="text" value="'.$cmcic_params['codesociete'].'" /><br />';
		//echo '<label class="simple_right">'.__('URL success', 'wpshop').'</label> <input name="wpshop_cmcic_params[urlok]" type="text" value="'.$cmcic_params['urlok'].'" /><br />';
		//echo '<label class="simple_right">'.__('URL cancel', 'wpshop').'</label> <input name="wpshop_cmcic_params[urlko]" type="text" value="'.$cmcic_params['urlko'].'" />';
	}
	
	function wpshop_shop_default_currency_field() {
		
		$wpshop_shop_currencies = get_option('wpshop_shop_currencies', unserialize(WPSHOP_SHOP_CURRENCIES));
		$current_currency = get_option('wpshop_shop_default_currency');
		
		$currencies_options = '';
		foreach($wpshop_shop_currencies as $k => $v) {
			$currencies_options .= '<option value="'.$k.'"'.(($k==$current_currency) ? ' selected="selected"' : null).'>'.$k.' ('.$v.')</option>';
		}
		echo '<select name="wpshop_shop_default_currency">'.$currencies_options.'</select>';
	}
	function wpshop_options_validate_default_currency($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paymentMethod($input) {
		if($input['paypal']=='on')$input['paypal']=true;
		if($input['checks']=='on')$input['checks']=true;
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paymentAddress($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalEmail($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalMode($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_cmcic_params($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_return_url($input) {
		return $input;
	}
	
	/* ------------------------- */
	/* --------- BILLING ------- */
	/* ------------------------- */
	function wpshop_billing_number_figures_field() {
		$wpshop_billing_number_figures = get_option('wpshop_billing_number_figures');
		$readonly = !empty($wpshop_billing_number_figures) ? 'readonly="readonly"': null;
		if(empty($wpshop_billing_number_figures)) $wpshop_billing_number_figures=5;
		
		echo '<input name="wpshop_billing_number_figures" type="text" value="'.$wpshop_billing_number_figures.'" '.$readonly.' />';
	}
	function wpshop_options_validate_billing_number_figures($input) {return $input;}
	
	/* ------------------------ */
	/* --------- EMAILS ------- */
	/* ------------------------ */
	function wpshop_noreply_email_field() {
		$admin_email = get_bloginfo('admin_email');
		$emails = get_option('wpshop_emails', null);
		$email = empty($emails['noreply_email']) ? $admin_email : $emails['noreply_email'];
		echo '<input name="wpshop_emails[noreply_email]" type="text" value="'.$email.'" />';
	}
	function wpshop_contact_email_field() {
		$admin_email = get_bloginfo('admin_email');
		$emails = get_option('wpshop_emails', null);
		$email = empty($emails['contact_email']) ? $admin_email : $emails['contact_email'];
		echo '<input name="wpshop_emails[contact_email]" type="text" value="'.$email.'" />';
	}
	function wpshop_options_validate_emails($input) {return $input;}
	
	/* -------------------------- */
	/* --------- MESSAGES ------- */
	/* -------------------------- */
	
	/* WPSHOP_SIGNUP_MESSAGE */
	function wpshop_WPSHOP_SIGNUP_MESSAGE_OBJECT_field() {
		$object = get_option('WPSHOP_SIGNUP_MESSAGE_OBJECT', null);
		$object = empty($object) ? WPSHOP_SIGNUP_MESSAGE_OBJECT : $object;
		echo '<input name="WPSHOP_SIGNUP_MESSAGE_OBJECT" type="text" value="'.$object.'" />';
	}
	function wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_SIGNUP_MESSAGE_field() {
		$message = get_option('WPSHOP_SIGNUP_MESSAGE', null);
		$message = empty($message) ? WPSHOP_SIGNUP_MESSAGE : $message;
		echo '<textarea name="WPSHOP_SIGNUP_MESSAGE" type="text" cols="80" rows="4">'.$message.'</textarea>';
	}
	function wpshop_options_validate_WPSHOP_SIGNUP_MESSAGE($input) {return $input;}
	
	/* WPSHOP_SIGNUP_MESSAGE */
	function wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT_field() {
		$object = get_option('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT', null);
		$object = empty($object) ? WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT : $object;
		echo '<input name="WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT" type="text" value="'.$object.'" />';
	}
	function wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_ORDER_CONFIRMATION_MESSAGE_field() {
		$message = get_option('WPSHOP_ORDER_CONFIRMATION_MESSAGE', null);
		$message = empty($message) ? WPSHOP_ORDER_CONFIRMATION_MESSAGE : $message;
		echo '<textarea name="WPSHOP_ORDER_CONFIRMATION_MESSAGE" type="text" cols="80" rows="4">'.$message.'</textarea>';
	}
	function wpshop_options_validate_WPSHOP_ORDER_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE */
	function wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field() {
		$object = get_option('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', null);
		$object = empty($object) ? WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT : $object;
		echo '<input name="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" type="text" value="'.$object.'" />';
	}
	function wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_field() {
		$message = get_option('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', null);
		$message = empty($message) ? WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE : $message;
		echo '<textarea name="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE" type="text" cols="80" rows="4">'.$message.'</textarea>';
	}
	function wpshop_options_validate_WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE */
	function wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT_field() {
		$object = get_option('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT', null);
		$object = empty($object) ? WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT : $object;
		echo '<input name="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" type="text" value="'.$object.'" />';
	}
	function wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_field() {
		$message = get_option('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', null);
		$message = empty($message) ? WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE : $message;
		echo '<textarea name="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE" type="text" cols="80" rows="4">'.$message.'</textarea>';
	}
	function wpshop_options_validate_WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE($input) {return $input;}
	
	/* WPSHOP_SHIPPING_CONFIRMATION_MESSAGE */
	function wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT_field() {
		$object = get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT', null);
		$object = empty($object) ? WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT : $object;
		echo '<input name="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT" type="text" value="'.$object.'" />';
	}
	function wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT($input) {return $input;}
	function wpshop_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_field() {
		$message = get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', null);
		$message = empty($message) ? WPSHOP_SHIPPING_CONFIRMATION_MESSAGE : $message;
		echo '<textarea name="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE" type="text" cols="80" rows="4">'.$message.'</textarea>';
	}
	function wpshop_options_validate_WPSHOP_SHIPPING_CONFIRMATION_MESSAGE($input) {return $input;}

	/**
	*
	*/
	function option_main_page(){
		global $options_errors;
		if(WPSHOP_DEBUG_MODE && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_ALLOWED_IP))){
			echo '<span class="fill_form_for_test" >Fill the form for test</span>';
		}
?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php echo __('WP-Shop options', 'wpshop'); ?></h2>
			
			<div id="options-tabs">
				<ul>
					<li><a href="#wpshop_general_option"><?php echo __('General', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_display_option"><?php echo __('Display', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_shipping_option"><?php echo __('Shipping', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_catalog_option"><?php echo __('Catalog', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_payments_option"><?php echo __('Payments', 'wpshop'); ?></a></li>
					<li><a href="#wpshop_emails_option"><?php echo __('Emails', 'wpshop'); ?></a></li>
				</ul>
				
				<form action="options.php" method="post">
				
				<div id="wpshop_general_option">	
						<div class="option_bloc"><?php do_settings_sections('wpshop_company_info'); ?></div>
						<div class="option_bloc"><?php do_settings_sections('wpshop_billing_info'); ?></div>
						<?php settings_fields('wpshop_options'); ?>
				</div>
			
				<div id="wpshop_display_option">
						<div class="option_bloc"><?php  do_settings_sections('wpshop_display_option'); ?></div>
				</div>
				
				<div id="wpshop_shipping_option">
						<div class="option_bloc"><?php do_settings_sections('wpshop_shipping_rules'); ?></div>
				</div>
				
				<div id="wpshop_catalog_option">
						<div class="option_bloc"><?php do_settings_sections('wpshop_catalog_product_option'); ?></div>
						<div class="option_bloc"><?php do_settings_sections('wpshop_catalog_categories_option'); ?></div>
				</div>
				
				<div id="wpshop_payments_option">
					<div class="option_bloc"><?php do_settings_sections('wpshop_paymentMethod'); ?></div>
				</div>
				
				<div id="wpshop_emails_option">
					<div class="option_bloc"><?php do_settings_sections('wpshop_emails'); ?></div>
					<div class="option_bloc"><?php do_settings_sections('wpshop_messages'); ?></div>
				</div>
				
				<?php if(current_user_can('wpshop_edit_options')): ?>
							<p class="submit">
								<input class="button-primary" name="Submit" type="submit" value="<?php echo __('Save Changes','wpshop'); ?>" />
							</p>
						<?php endif; ?>
						
			</form>
		</div>
<?php
		
	}

}


/**
* Define the different method to manage the different product options
* @package wpshop
* @subpackage librairies
*/
class wpshop_display_options
{
	/**
	*	Add an explanation on the option part
	*/
	function part_explanation(){
		
	}
	/**
	*	Add option validation for current option part
	*/
	function part_validator($input){
		$newinput['wpshop_display_list_type'] = $input['wpshop_display_list_type'];
		if($input['wpshop_display_grid_element_number'] < WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE){
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE;
		}
		elseif($input['wpshop_display_grid_element_number'] > WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE){
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE;
		}
		$newinput['wpshop_display_grid_element_number'] = $input['wpshop_display_grid_element_number'];
		$newinput['wpshop_display_cat_sheet_output'] = $input['wpshop_display_cat_sheet_output'];
		$newinput['wpshop_display_reset_template_element'] = $input['wpshop_display_reset_template_element'];
		$newinput['wpshop_display_element_per_page'] = $input['wpshop_display_element_per_page'];

		return $newinput;
	}

	/**
	*	Add the option field to choose how to display category page
	*/
	function wpshop_display_cat_sheet_output(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_cat_sheet_output';

		if(current_user_can('wpshop_edit_options')){
			$content = array('category_description', 'category_subcategory', 'category_subproduct');
			$option_field_output = '';
			foreach($content as $content_definition){
				$current_value = (is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) && in_array($content_definition, $wpshop_display_option['wpshop_display_cat_sheet_output'])) || !is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) ? $content_definition : '';

				switch($content_definition){
					case 'category_description':
					{
						$field_label = __('Display product category description', 'wpshop');
					}
					break;
					case 'category_subcategory':
					{
						$field_label = __('Display sub categories listing', 'wpshop');
					}
					break;
					case 'category_subproduct':
					{
						$field_label = __('Display products listing', 'wpshop');
					}
					break;
					default:
					{
						$field_label = __('Nothing defined here', 'wpshop');
					}
					break;
				}
				$option_field_output .= wpshop_form::form_input_check('wpshop_display_option[' . $field_identifier . '][]', $field_identifier . '_' . $content_definition, $content_definition, $current_value, 'checkbox') . '<label for="' . $field_identifier . '_' . $content_definition . '" >' . $field_label . '</label><br/>';
			}
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose ho to output element list grid or list
	*/
	function wpshop_display_list_type(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_list_type';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input_select('wpshop_display_option[' . $field_identifier . ']', $field_identifier, array('grid' => __('Grid', 'wpshop'), 'list' => __('List', 'wpshop')), $wpshop_display_option[$field_identifier], '', 'index');
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose how many element to output when grid mode is selected
	*/
	function wpshop_display_grid_element_number(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_grid_element_number';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = '<div id="' . $field_identifier . 'slider" class="slider_variable"></div>
			' . wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'text', ' readonly="readonly" class="sliderValue" ') . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $field_identifier . 'slider").slider({
			value:' . ($wpshop_display_option[$field_identifier] <= 0 ? WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE : $wpshop_display_option[$field_identifier]) . ',
			min: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE . ',
			max: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE . ',
			step: 1,
			slide: function(event, ui) {
				jQuery("#' . $field_identifier . '").val(ui.value);
			}
		});
		jQuery("#' . $field_identifier . '").val(jQuery("#' . $field_identifier . 'slider").slider("value"));
	});
</script>';
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose how many element to output per page in product listing
	*/
	function wpshop_display_element_per_page(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_element_per_page';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, !empty($wpshop_display_option[$field_identifier]) ? $wpshop_display_option[$field_identifier] : 20, 'text');
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose how many element to output when grid mode is selected
	*/
	function wpshop_display_reset_template_element(){
		$option_field_output = '';
		global $wpshop_display_option, $current_user;
		$field_identifier = 'wpshop_display_reset_template_element';

		$last_reset_infos = '&nbsp;';
		if($wpshop_display_option[$field_identifier] != ''){
			$infos = explode('dateofreset', $wpshop_display_option[$field_identifier]);
			if($infos[0] > 0){
				$user_first_name = get_user_meta($infos[0], 'first_name', true);
				$user_first_name = ($user_first_name != '') ? $user_first_name : get_userdata($infos[0])->user_nicename;
				$user_last_name = get_user_meta($infos[0], 'last_name', true);
				$user_last_name = ($user_last_name != '') ? $user_last_name :'';
				$last_reset_infos = sprintf(__('Last template reset was made by %s on %s', 'wpshop'), $user_first_name . '&nbsp;' . $user_last_name, mysql2date('d/m/Y H:i', $infos[1], true));
			}
		}

		if(current_user_can('wpshop_edit_options')){
			/*	Allows to specify a given list of file to overwrite in template	*/
			$option_field_output .= '<div id="wpshop_option_tpl_updater_display" >' . __('Choose the different template files to update', 'wpshop') . '</div>
			<div class="wpshopHide" id="wpshop_option_tpl_updater" >
				<div><span id="tpl_updater_check_all" class="tpl_updater_mass_action" >' . __('Check all', 'wpshop') . '</span>&nbsp;/&nbsp;<span id="tpl_updater_uncheck_all" class="tpl_updater_mass_action" >' . __('Uncheck all', 'wpshop') . '</span></div>
				' . wpshop_display::list_template_files(WPSHOP_TEMPLATES_DIR . 'wpshop') . '
			</div><br/>';

			$option_field_output .= wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'hidden', ' readonly="readonly" ') . '
<input type="button" value="' . __('Reset template file with default plugin file', 'wpshop') . '" name="reset_template_file" id="reset_template_file" class="button-secondary" /><div id="last_reset_infos" >' . $last_reset_infos . '</div>
<script type="text/javascript">
	wpshop(document).ready(function(){
		jQuery("#wpshop_option_tpl_updater_display").click(function(){
			jQuery("#wpshop_option_tpl_updater").toggle();
		});
		jQuery("#tpl_updater_check_all").click(function(){
			jQuery(".template_file_to_replace_checkbox").each(function(){
				jQuery(this).prop("checked", true);
			});
		});
		jQuery("#tpl_updater_uncheck_all").click(function(){
			jQuery(".template_file_to_replace_checkbox").each(function(){
				jQuery(this).prop("checked", false);
			});
		});
		jQuery("#reset_template_file").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('All modification applied to template file will be lost!\r\n\r\nAre you sure you want to reset template?', 'wpshop') . '"))){
				jQuery("#' . $field_identifier . '").val("' . $current_user->ID . 'dateofreset' . date('Y-m-d H:i:s') . '");
				var tpl_file_list = "";
				jQuery(".template_file_to_replace_checkbox").each(function(){
					if(jQuery(this).is(":checked")){
						tpl_file_list += jQuery(this).val() + "!#!";
					}
				});
				jQuery("#last_reset_infos").load(WPSHOP_AJAX_FILE_URL, {
					"post": "true",
					"elementCode": "templates",
					"action": "reset_template_files",
					"tpl_file_list": tpl_file_list,
					"reset_info": jQuery("#' . $field_identifier . '").val()
				});
			}
		});
	});
</script>';
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}

}
