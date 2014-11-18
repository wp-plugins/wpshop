<?php
class wps_billing {
	function __construct() {
		//In case wpshop is set on sale mode and not on view catalog only, Ad billign options
		if ((WPSHOP_DEFINED_SHOP_TYPE == 'sale')
				&& !isset($_POST['wpshop_shop_type'])
				|| (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))
				&& !isset($_POST['old_wpshop_shop_type'])
				|| (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))) {
		
			/**	Add module option to wpshop general options	*/
			add_filter('wpshop_options', array($this, 'add_options'), 9);
			add_action('wsphop_options', array($this, 'declare_options'), 8);
		}
	}
	
	/**
	 * OPTIONS - Add Billing options part to WPShop options panel
	 * @param array $option_group : Option  group definition
	 * @return array
	 */
	function add_options( $option_group ) {
		$option_group['wpshop_billing_info'] =
		array(	'label' => __('Billing', 'wpshop'),
				'subgroups' => array(
						'wpshop_billing_info' => array('class' => ' wpshop_admin_box_options_billing'),
				),
		);
		
		return $option_group;
	}
	
	/**
	 * OPTIONS - Declare the different options in groups for the billing module
	 */
	function declare_options() {
		add_settings_section('wpshop_billing_info', __('Billing settings', 'wpshop'), array(&$this, 'billing_options_main_explanation'), 'wpshop_billing_info');
	
		register_setting('wpshop_options', 'wpshop_billing_number_figures', array(&$this, 'wpshop_options_validate_billing_number_figures'));
		add_settings_field('wpshop_billing_number_figures', __('Number of figures', 'wpshop'), array(&$this, 'wpshop_billing_number_figures_field'), 'wpshop_billing_info', 'wpshop_billing_info');
	
		register_setting('wpshop_options', 'wpshop_billing_address', array(&$this, 'wpshop_billing_address_validator'));
		add_settings_field('wpshop_billing_address_choice', __('Billing address choice', 'wpshop'), array(&$this, 'wpshop_billing_address_choice_field'), 'wpshop_billing_info', 'wpshop_billing_info');
		add_settings_field('wpshop_billing_address_include_into_register', '', array(&$this, 'wpshop_billing_address_include_into_register_field'), 'wpshop_billing_info', 'wpshop_billing_info');
	
		$quotation_option = get_option( 'wpshop_addons' );
		if ( !empty($quotation_option) && !empty($quotation_option['WPSHOP_ADDONS_QUOTATION']) && !empty($quotation_option['WPSHOP_ADDONS_QUOTATION']['activate']) ) {
			add_settings_section('wpshop_quotation_info', __('Quotation settings', 'wpshop'), array(&$this, 'quotation_options_main_explanation'), 'wpshop_billing_info');
	
			register_setting('wpshop_options', 'wpshop_quotation_validate_time', array(&$this, 'wpshop_options_validate_quotation_validate_time'));
			add_settings_field('wpshop_quotation_validate_time', __('Quotation validate time', 'wpshop'), array(&$this, 'wpshop_quotation_validate_time_field'), 'wpshop_billing_info', 'wpshop_quotation_info');
			$payment_option = get_option('wps_payment_mode');
			if ( !empty($payment_option) && !empty($payment_option['mode']) && !empty($payment_option['mode']['banktransfer']) && !empty($payment_option['mode']['banktransfer']['active']) ) {
				register_setting('wpshop_options', 'wpshop_paymentMethod_options[banktransfer][add_in_quotation]', array(&$this, 'wpshop_options_validate_wpshop_bic_to_quotation'));
				add_settings_field('wpshop_paymentMethod_options[banktransfer][add_in_quotation]', __('Add your BIC to your quotations', 'wpshop'), array(&$this, 'wpshop_bic_to_quotation_field'), 'wpshop_billing_info', 'wpshop_quotation_info');
			}
	
		}
	
	}
	
	/** 
	 * OPTIONS - Billing part
	 */
	function billing_options_main_explanation() {
	
	}
	
	/**
	 * OPTIONS - Quotation part
	 */
	function quotation_options_main_explanation() {
	
	}
	
	/**
	 * OPTIONS - Validate wpshop Bico in quotation option
	 * @param string $input
	 * @return string
	 */
	function wpshop_options_validate_wpshop_bic_to_quotation ($input) {
		return $input;
	}

	/**
	 * OPTIONS - Add Bic in quotation field on options panel
	 */
	function wpshop_bic_to_quotation_field () {
		$add_quotation_option = get_option('wpshop_paymentMethod_options');
		$output = '<input type="checkbox" name="wpshop_paymentMethod_options[banktransfer][add_in_quotation]" id="wpshop_paymentMethod_options[banktransfer][add_in_quotation]"  ' .( ( !empty($add_quotation_option) && !empty($add_quotation_option['banktransfer']) && !empty($add_quotation_option['banktransfer']['add_in_quotation']) ) ? 'checked="checked"' : ''). ' />';
		echo $output;
	}

	/**
	 * OPTIONS - Validate time quotation option
	 * @param string $input
	 * @return string
	 */
	function wpshop_options_validate_quotation_validate_time ($input) {
		return $input;
	}
	
	/**
	 * OPTIONS - Validate time quotation option fields
	 */
	function wpshop_quotation_validate_time_field () {
		$quotation_option = get_option('wpshop_quotation_validate_time');
		$output  = '<input type="text" name="wpshop_quotation_validate_time[number]" id="wpshop_quotation_validate_time[number]" style="width:50px;" value="' .( ( !empty($quotation_option) && !empty($quotation_option['number']) ) ? $quotation_option['number'] : null ). '" />';
		$output .= '<select name="wpshop_quotation_validate_time[time_type]" id="wpshop_quotation_validate_time[time_type]">';
		$output .= '<option value="day" ' .( (  !empty($quotation_option) && !empty($quotation_option['time_type']) &&  $quotation_option['time_type'] == 'day') ? 'selected="selected"' : ''). '>' .__('Days', 'wpshop'). '</option>';
		$output .= '<option value="month" ' .( (  !empty($quotation_option) && !empty($quotation_option['time_type']) &&  $quotation_option['time_type'] == 'month') ? 'selected="selected"' : ''). '>' .__('Months', 'wpshop'). '</option>';
		$output .= '<option value="year" ' .( (  !empty($quotation_option) && !empty($quotation_option['time_type']) &&  $quotation_option['time_type'] == 'year') ? 'selected="selected"' : ''). '>' .__('Years', 'wpshop'). '</option>';
		$output .= '</select>';
		echo $output;
	}
	
	/**
	 * OPTIONS - Billing number figures field
	 */
	function wpshop_billing_number_figures_field() {
		$wpshop_billing_number_figures = get_option('wpshop_billing_number_figures');
		$readonly = !empty($wpshop_billing_number_figures) ? 'readonly="readonly"': null;
		if(empty($wpshop_billing_number_figures)) $wpshop_billing_number_figures=5;
	
		echo '<input name="wpshop_billing_number_figures" type="text" value="'.$wpshop_billing_number_figures.'" '.$readonly.' />
					<a href="#" title="'.__('Number of figures to make appear on invoices','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	
	/**
	 * OPTIONS - Validate billing number figures
	 * @param string $input
	 * @return string
	 */
	function wpshop_options_validate_billing_number_figures( $input ) {
		return $input;
	}
	
	/**
	 * OPTIONS - Billing address validator option
	 * @param string $input
	 * @return string
	 */
	function wpshop_billing_address_validator( $input ){
		global $wpdb;
		$t = wps_address::get_addresss_form_fields_by_type ( $input['choice'] );
	
		$the_code = '';
		foreach( $t[$input['choice']] as $group_id => $group_def ) {
			if ( !empty($input['integrate_into_register_form_matching_field']) && !empty($input['integrate_into_register_form_matching_field']['user_email']) && array_key_exists( $input['integrate_into_register_form_matching_field']['user_email'], $group_def['content']) ) {
				$the_code = $group_def['content'][$input['integrate_into_register_form_matching_field']['user_email']]['name'];
				continue;
			}
		}
		$the_code;
	
		if ( !empty($input['integrate_into_register_form']) && $input['integrate_into_register_form'] == 'yes' ) {
			if ( !empty($input['integrate_into_register_form_matching_field']) && !empty($input['integrate_into_register_form_matching_field']['user_email']) && $the_code == 'address_user_email') {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('_need_verification' => 'no'), array('code' => $the_code));
			}
		}
			
		$billing_option = get_option( 'wpshop_billing_address' );
		if( !empty($billing_option) && !empty( $billing_option['display_model'] ) ) {
			$input['display_model'] = $billing_option['display_model'];
		}
			
		return $input;
	}
	
	/**
	 * OPTIONS - Billing address choice field
	 */
	function wpshop_billing_address_choice_field() {
		global $wpdb;
		$output = '';
	
		$wpshop_billing_address = get_option('wpshop_billing_address');
	
		$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
		$entity_id = $wpdb->get_var($query);
	
		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
		$content = $wpdb->get_results($query);
	
		/*	Field for billing address type choice	*/
		$input_def['name'] = 'wpshop_billing_address[choice]';
		$input_def['id'] = 'wpshop_billing_address_choice';
		$input_def['possible_value'] = $content;
		$input_def['type'] = 'select';
		$input_def['value'] = $wpshop_billing_address['choice'];
		$output .= '<div>' .wpshop_form::check_input_type($input_def). '</div>';
	
		/*	Field for integrate billign form into register form	*/
		$input_def = array();
		$input_def['name'] = 'wpshop_billing_address[integrate_into_register_form]';
		$input_def['id'] = 'wpshop_billing_address_integrate_into_register_form';
		$input_def['possible_value'] = array( 'yes' => __('Integrate billing form into register form', 'wpshop') );
		$input_def['valueToPut'] = 'index';
		$input_def['options']['label']['original'] = true;
		$input_def['option'] = ' class="wpshop_billing_address_integrate_into_register_form" ';
		$input_def['type'] = 'checkbox';
		$input_def['value'] = array( !empty($wpshop_billing_address['integrate_into_register_form']) ? $wpshop_billing_address['integrate_into_register_form'] : '' );
		$output .= '
					<div class="wpshop_include_billing_form_into_register_container" >
						' .wpshop_form::check_input_type($input_def). '
						<input type="hidden" name="wpshop_ajax_integrate_billin_into_register" id="wpshop_ajax_integrate_billin_into_register" value="' . wp_create_nonce('wpshop_ajax_integrate_billin_into_register') . '" />
						<input type="hidden" name="wpshop_include_billing_form_into_register_where_value" id="wpshop_include_billing_form_into_register_where_value" value="' . (!empty($wpshop_billing_address['integrate_into_register_form_after_field']) ? $wpshop_billing_address['integrate_into_register_form_after_field'] : '') . '" />
						<div class="wpshop_include_billing_form_into_register_where" ></div>
					</div>';
	
		echo $output;
	}
	
	
	/**
	 * Generate a new invoice number
	 *
	 * @param integer $order_id The order identifier we want to generate the new invoice number for
	 *
	 * @return string The new invoice number
	 */
	function generate_invoice_number( $order_id ) {
		/**	Get configuration about the number of figure dor invoice number	*/
			
		$number_figures = get_option('wpshop_billing_number_figures', false);
	
		/** If the number doesn't exist, we create a default one */
		if(!$number_figures) {
			update_option('wpshop_billing_number_figures', 5);
		}
	
		/** sleep my script, SLEEP I SAY ! **/
		$rand_time = rand( 1000, 200000 );
		usleep( $rand_time );
		/** GET UP !! **/
			
		/**	Get last invoice number	*/
		$billing_current_number = get_option('wpshop_billing_current_number', false);
	
		/** If the counter doesn't exist, we initiate it */
		if (!$billing_current_number) {
			$billing_current_number = 1;
		}
		else {
			$billing_current_number++;
		}
			
		/** Check if number exists **/
		$billing_current_number_checking = get_option('wpshop_billing_current_number', false);
		if ( $billing_current_number_checking == $billing_current_number ) {
			$billing_current_number++;
		}
			
		update_option('wpshop_billing_current_number', $billing_current_number);
			
		/**	Create the new invoice number with all parameters viewed above	*/
		$invoice_ref = WPSHOP_BILLING_REFERENCE_PREFIX . ((string)sprintf('%0'.$number_figures.'d', $billing_current_number));
	
		return $invoice_ref;
	}

	/**
	 * Check product price
	 * @param float $price_ht
	 * @param float $price_ati
	 * @param float $tva_amount
	 * @param float $tva_rate
	 * @param id $product_id
	 * @param string $invoice_ref
	 */
	function check_product_price( $price_ht, $price_ati, $tva_amount, $tva_rate, $product_id, $invoice_ref, $order_id ) {
		$checking = true;
		$error_percent =  1;
		/** Check VAT Amount **/
			
		$tva_amount = number_format( $tva_amount, 2, '.', '' );
		$price_ht = number_format( $price_ht, 2, '.', '' );
		$price_ati = number_format( $price_ati, 2, '.', '' );
		$checked_tva_amount = number_format( $price_ati / ( 1 + ($tva_rate / 100) ), 2, '.', '' );
		$checked_tva_amount = $price_ati - $checked_tva_amount;
		if ( ( $checked_tva_amount < ($tva_amount / ( 1 + ($error_percent / 100) ) ) ) || ( $checked_tva_amount > ($tva_amount * (1 + ($error_percent / 100) ) ) )  ) {
			$error_infos = array();
			$error_infos['real_datas']['price_ati'] =  $price_ati;
			$error_infos['real_datas']['price_ht'] =  $price_ht;
			$error_infos['real_datas']['tva_amount'] =  $tva_amount;
	
			$error_infos['corrected_data'] = $checked_tva_amount;
			self::invoice_error_check_administrator( $invoice_ref, __('VAT error', 'wpshop'), $product_id, $order_id, $error_infos );
			$checking = false;
		}
	
		/** Check price ati **/
		$checked_price_ati =  $price_ht * ( 1 + ( $tva_rate / 100) );
		if ( ( $checked_price_ati < ($price_ati /( 1 + ($error_percent / 100) ) ) ) || ( $checked_price_ati > ($price_ati * (1 + ($error_percent / 100)) ) )  ) {
			self::invoice_error_check_administrator( $invoice_ref, __('ATI Price error', 'wpshop'), $product_id, $order_id );
			$checking = false;
		}
		return $checking;
	}
	
	/**
	 * Alert administrator when have invoice error
	 * @param string $invoice_ref
	 * @param string $object
	 * @param unknown_type $product_id
	 */
	function invoice_error_check_administrator( $invoice_ref, $object, $product_id, $order_id, $errors_infos = array() ) {
		$wpshop_email_option = get_option( 'wpshop_emails');
		if ( !empty($wpshop_email_option) && !empty($wpshop_email_option['contact_email']) ) {
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=UTF-8\r\n";
			$headers .= 'From: '.get_bloginfo('name').' <'.$wpshop_email_option['noreply_email'].'>' . "\r\n";
			$message  = '<b>'.__('Error type', 'wpshop').' : </b>'.$object.'<br/>';
			$message .= '<b>'.__( 'Product', 'wpshop').' : </b>'.get_the_title(  $product_id ).'<br/>';
			$message .= '<b>'.__( 'Invoice ref', 'wpshop').' : </b>'.$invoice_ref.'<br/>';
			$message .= '<b>'.__( 'Order ID', 'wpshop').' : </b>'.$order_id.'<br/>';
	
			if ( !empty($errors_infos) && !empty($errors_infos['real_datas']) ) {
				$message .='<b>' .__( 'Bad datas', 'wpshop').' :</b> <ul>';
				foreach( $errors_infos['real_datas'] as $k => $errors_info ) {
					$message .= '<li><b>'.$k.' : </b>'.$errors_info.'</li>';
				}
				$message .= '</ul>';
				if ( !empty($errors_infos['corrected_data']) ) {
					$message .= '<b>' .__( 'Good value', 'wpshop' ).' : </b>'.$errors_infos['corrected_data'];
				}
			}
			wp_mail( $wpshop_email_option['contact_email'], __('Error on invoice generation', 'wpshop') , $message, $headers);
		}
	}
	
	/**
	 * Generate invoice to be attached to confirmation total payment mail
	 */
	function generate_invoice_for_email ( $order_id, $invoice_ref = '' ) {
		/** Generate the PDF file for the invoice **/
		$is_ok = false;
		if ( !empty($invoice_ref) ) {
			require_once(WPSHOP_LIBRAIRIES_DIR.'HTML2PDF/html2pdf.class.php');
			try {
				$html_content =  wpshop_modules_billing::generate_html_invoice( $order_id, $invoice_ref );
				$html_content = wpshop_display::display_template_element('invoice_page_content_css', array(), array(), 'common') . '<page>' . $html_content . '</page>';
				$html2pdf = new HTML2PDF('P', 'A4', 'fr');
	
				$html2pdf->setDefaultFont('Arial');
				$html2pdf->writeHTML($html_content);
				$html2pdf->Output(WPSHOP_UPLOAD_DIR.$invoice_ref.'.pdf', 'F');
				$is_ok = true;
			}
			catch (HTML2PDF_exception $e) {
				echo $e;
				exit;
			}
		}
		return ( $is_ok ) ? WPSHOP_UPLOAD_DIR.$invoice_ref.'.pdf' : '';
	}

	
	
	
	function wpshop_billing_address_include_into_register_field() {
	
	}
}