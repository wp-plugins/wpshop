<?php
/**
 * Plugin Name: WP-Shop-billing-module
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: Wpshop module allowing to manage invoice for the different orders
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Billing module bootstrap file
 *
 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 */

/** Check if the plugin version is defined. If not defined script will be stopped here */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

/**	Check if billing class does not exist before creating the class	*/
if ( !class_exists("wpshop_modules_billing") ) {

	/**
	 * Billing module utilities definition
	 *
	 * @author Alexandre Techer - Eoxia dev team <dev@eoxia.com>
	 * @version 0.1
	 * @package includes
	 * @subpackage modules
	 */
	class wpshop_modules_billing {

		/**
		 * Create a new instance for the current module - Billing
		 */
		function __construct() {
			/**	Add custom template for current module	*/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );

			/**	In case wpshop is set on sale mode and not on view catalog only, Ad billign options	*/
			if ((WPSHOP_DEFINED_SHOP_TYPE == 'sale')
					&& !isset($_POST['wpshop_shop_type'])
						|| (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation'))
					&& !isset($_POST['old_wpshop_shop_type'])
						|| (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))) {

				/**	Add module option to wpshop general options	*/
				add_filter('wpshop_options', array(&$this, 'add_options'), 9);
				add_action('wsphop_options', array(&$this, 'declare_options'), 8);
			}
		}

		/**
		 * Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/common/main_elements.tpl.php');
			$wpshop_template = $tpl_element;
			/*	Get custom frontend template	*/
			if ( is_file(get_stylesheet_directory() . '/wpshop/common/main_elements.tpl.php') ) {
				require_once(get_stylesheet_directory() . '/wpshop/common/main_elements.tpl.php');
				if (!empty($tpl_element))
					$wpshop_template['common']['custom'] = ($tpl_element);unset($tpl_element);
			}
			$wpshop_display = new wpshop_display();
			$templates = $wpshop_display->add_modules_template_to_internal( $wpshop_template, $templates );

			unset($tpl_element);

			return $templates;
		}

		/**
		 * Declare option groups for the module
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
		 * Declare the different options in groups for the module
		 */
		function declare_options() {
			add_settings_section('wpshop_billing_info','<span class="dashicons dashicons-admin-generic"></span>'. __('Billing settings', 'wpshop'), array(&$this, 'billing_options_main_explanation'), 'wpshop_billing_info');

			register_setting('wpshop_options', 'wpshop_billing_number_figures', array(&$this, 'wpshop_options_validate_billing_number_figures'));
			add_settings_field('wpshop_billing_number_figures', __('Number of figures', 'wpshop'), array(&$this, 'wpshop_billing_number_figures_field'), 'wpshop_billing_info', 'wpshop_billing_info');

			register_setting('wpshop_options', 'wpshop_billing_address', array(&$this, 'wpshop_billing_address_validator'));
			add_settings_field('wpshop_billing_address_choice', __('Billing address choice', 'wpshop'), array(&$this, 'wpshop_billing_address_choice_field'), 'wpshop_billing_info', 'wpshop_billing_info');
			add_settings_field('wpshop_billing_address_include_into_register', '', array(&$this, 'wpshop_billing_address_include_into_register_field'), 'wpshop_billing_info', 'wpshop_billing_info');

			$quotation_option = get_option( 'wpshop_addons' );
			if ( !empty($quotation_option) && !empty($quotation_option['WPSHOP_ADDONS_QUOTATION']) && !empty($quotation_option['WPSHOP_ADDONS_QUOTATION']['activate']) ) {
				add_settings_section('wpshop_quotation_info', '<span class="dashicons dashicons-clipboard"></span>' .__('Quotation settings', 'wpshop'), array(&$this, 'quotation_options_main_explanation'), 'wpshop_billing_info');

				register_setting('wpshop_options', 'wpshop_quotation_validate_time', array(&$this, 'wpshop_options_validate_quotation_validate_time'));
				add_settings_field('wpshop_quotation_validate_time', __('Quotation validate time', 'wpshop'), array(&$this, 'wpshop_quotation_validate_time_field'), 'wpshop_billing_info', 'wpshop_quotation_info');
				$payment_option = get_option('wps_payment_mode');
				if ( !empty($payment_option) && !empty($payment_option['mode']) && !empty($payment_option['mode']['banktransfer']) && !empty($payment_option['mode']['banktransfer']['active']) ) {
					register_setting('wpshop_options', 'wpshop_paymentMethod_options[banktransfer][add_in_quotation]', array(&$this, 'wpshop_options_validate_wpshop_bic_to_quotation'));
					add_settings_field('wpshop_paymentMethod_options[banktransfer][add_in_quotation]', __('Add your BIC to your quotations', 'wpshop'), array(&$this, 'wpshop_bic_to_quotation_field'), 'wpshop_billing_info', 'wpshop_quotation_info');
				}

			}

		}

		function wpshop_options_validate_wpshop_bic_to_quotation ($input) {
			return $input;
		}

		function wpshop_bic_to_quotation_field () {
			$add_quotation_option = get_option('wpshop_paymentMethod_options');
			$output = '<input type="checkbox" name="wpshop_paymentMethod_options[banktransfer][add_in_quotation]" id="wpshop_paymentMethod_options[banktransfer][add_in_quotation]"  ' .( ( !empty($add_quotation_option) && !empty($add_quotation_option['banktransfer']) && !empty($add_quotation_option['banktransfer']['add_in_quotation']) ) ? 'checked="checked"' : ''). ' />';
			echo $output;
		}

		function billing_options_main_explanation() {

		}
		function quotation_options_main_explanation() {

		}

		function wpshop_options_validate_quotation_validate_time ($input) {
			return $input;
		}

		function wpshop_billing_number_figures_field() {
			$wpshop_billing_number_figures = get_option('wpshop_billing_number_figures');
			$readonly = !empty($wpshop_billing_number_figures) ? 'readonly="readonly"': null;
			if(empty($wpshop_billing_number_figures)) $wpshop_billing_number_figures=5;

			echo '<input name="wpshop_billing_number_figures" type="text" value="'.$wpshop_billing_number_figures.'" '.$readonly.' />
		<a href="#" title="'.__('Number of figures to make appear on invoices','wpshop').'" class="wpshop_infobulle_marker">?</a>';
		}
		
		function wpshop_options_validate_billing_number_figures( $input ) {
			return $input;
		}
		
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
		
		function wpshop_billing_address_include_into_register_field() {

		}

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
		 * Generate output for an invoice
		 *
		 * @param integer $order_id
		 * @param string $invoice_ref
		 *
		 * @return string The invoice output in case no error is found. The error in other case
		 */
		function generate_html_invoice($order_id, $invoice_ref ) {
			global $wpdb;
			if ( !empty($order_id) ) {
				$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
				
				$discounts_exists = false;
				
				$is_quotation = ( empty($order_postmeta['order_key']) && !empty($order_postmeta['order_temporary_key']) ) ? true : false;
				/** Check if it's a partial payment bill **/
				$is_partial_payment = ( ( !empty($invoice_ref) && !empty($order_postmeta['order_invoice_ref'] ) && $order_postmeta['order_status'] == 'partially_paid' && !empty($order_postmeta['order_invoice_ref']) && $order_postmeta['order_invoice_ref'] != $invoice_ref) || ( empty($invoice_ref) && $order_postmeta['order_status'] == 'partially_paid' ) || ( !empty($invoice_ref) && !empty($order_postmeta['order_invoice_ref']) && $order_postmeta['order_invoice_ref'] != $invoice_ref) ) ? true : false;

				
				
				/** Check it is a shipping slip **/
				$bon_colisage = ( !empty($_GET['bon_colisage']) ) ? true : false;
				
				if ( !empty($order_postmeta) ) {
					$tpl_component = array();
					
					/** Billing Header **/
					//Logo
					$logo_options = get_option('wpshop_logo');
					$tpl_component['INVOICE_LOGO'] = ( !empty($logo_options) ) ? '<img src="' .$logo_options .'" alt="" />' : '';
					
					// Title
					$tpl_component['INVOICE_TITLE'] = ( $is_quotation ) ?  __('Quotation', 'wpshop') :  ( ($is_partial_payment) ? __('Bill payment', 'wpshop') : __('Invoice', 'wpshop') );
					if ( $bon_colisage ) {
						$tpl_component['INVOICE_TITLE'] = __( 'Products List', 'wpshop' );
					}
					
					$tpl_component['INVOICE_ORDER_INVOICE_REF'] = ( !empty($invoice_ref) ) ? $invoice_ref : $order_postmeta['order_invoice_ref'];
					if ( $bon_colisage ) {
						$tpl_component['INVOICE_ORDER_INVOICE_REF'] = '';
					}
					$tpl_component['INVOICE_ORDER_KEY_INDICATION'] = ( $is_quotation ) ? sprintf( __('Ref. %s', 'wpshop'),$order_postmeta['order_temporary_key']) : sprintf( __('Order n. %s', 'wpshop'), $order_postmeta['order_key'] ); 
					$tpl_component['INVOICE_ORDER_DATE_INDICATION'] = ( $is_quotation ) ? sprintf( __('Quotation date %s', 'wpshop'), $order_postmeta['order_date'] ) : sprintf( __('Order date %s', 'wpshop'), $order_postmeta['order_date'] );
					
					/** Validate period for Quotation **/
					if ( $is_quotation ) {
						$quotation_validate_period = self::quotation_validate_period( $order_postmeta['order_date'] );
					}
					else {
						$tpl_component['INVOICE_VALIDATE_TIME'] = '';
					}
					
					$tpl_component['AMOUNT_INFORMATION'] = ( !$bon_colisage ) ? sprintf( __('Amount are shown in %s', 'wpshop'), wpshop_tools::wpshop_get_currency( true ) ) : '';
					
					

					// Sender & receiver addresses
					$tpl_component['INVOICE_SENDER'] = self::generate_invoice_sender_part( );
					$tpl_component['INVOICE_RECEIVER'] = self::generate_receiver_part( $order_id, $bon_colisage );
					
					
					
					
					/** Items Tab **/
					$order_tva = array();
					if ( $bon_colisage ) {
						$tpl_component['INVOICE_HEADER'] = wpshop_display::display_template_element('bon_colisage_row_header', array(), array(), 'common');
					}
					else {
						$tpl_component['INVOICE_HEADER'] = wpshop_display::display_template_element('invoice_row_header', array(), array(), 'common');
						if ( !$is_quotation ) {
							/** Check if products have discounts **/
							if ( !empty($order_postmeta['order_items']) && !$is_partial_payment ) {
								foreach( $order_postmeta['order_items'] as $item_id => $item ) {
									if ( !empty($item['item_global_discount_value']) || !empty($item['item_unit_discount_value']) ) {
										$discounts_exists = true;
									}
								}
							}
							
							if ( $discounts_exists ) {
								$tpl_component['INVOICE_HEADER'] = wpshop_display::display_template_element('invoice_row_header_with_discount', array(), array(), 'common');
							}
							
						}
					}
					
					$total_discounted = 0;
					$tpl_component['INVOICE_ROWS'] = '';
					
					if( !$is_partial_payment ) {
						if ( !empty($order_postmeta['order_items']) ) {
							foreach( $order_postmeta['order_items'] as $item_id => $item ) {
								$sub_tpl_component = array();
								
								$barcode = get_post_meta( $item['item_id'], '_barcode', true );
								if ( empty($barcode) ) {
									$product_metadata = get_post_meta( $item['item_id'], '_wpshop_product_metadata', true );
									$barcode = ( !empty($product_metadata) && !empty($product_metadata['barcode']) ) ? $product_metadata['barcode'] : '';
										
									if( empty($barcode) ) {
										$product_entity = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
										$att_def = wpshop_attributes::getElement( 'barcode', '"valid"', 'code' );
										$query = $wpdb->prepare( 'SELECT value FROM ' .$wpdb->prefix. 'wpshop__attribute_value_'.$att_def->data_type. ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d', $product_entity, $att_def->id, $item['item_id'] );
										$barcode = $wpdb->get_var( $query );
									}
								}
								$sub_tpl_component['INVOICE_ROW_ITEM_BARCODE'] = ( !empty($barcode) ) ? $barcode : '-';
								
								$sub_tpl_component['INVOICE_ROW_ITEM_REF'] = $item['item_ref'];
								
								/** Item name **/
								$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = $item['item_name'];
								/**	Get attribute order for current product	*/
								$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
								$output_order = array();
								if ( count($product_attribute_order_detail) > 0 ) {
									foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
										foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
											if ( !empty($attribute_def->code) )
												$output_order[$attribute_def->code] = $position;
										}
									}
								}
								$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item['item_meta'], $output_order, 'invoice_print', 'common');
								ksort($variation_attribute_ordered['attribute_list']);
								$detail_tpl_component['CART_PRODUCT_MORE_INFO'] = '';
								foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
									$detail_tpl_component['CART_PRODUCT_MORE_INFO'] .= $attribute_variation_to_output;
								}
								$sub_tpl_component['INVOICE_ROW_ITEM_DETAIL'] = !empty($detail_tpl_component['CART_PRODUCT_MORE_INFO']) ? wpshop_display::display_template_element('invoice_row_item_detail', $detail_tpl_component, array(), 'common') : '';
								unset( $detail_tpl_component );
								
								$sub_tpl_component['INVOICE_ROW_ITEM_QTY'] = $item['item_qty'];
								$sub_tpl_component['INVOICE_ROW_ITEM_PU_HT'] = ( ( !empty($item['item_pu_ht_before_discount']) ) ? number_format( $item['item_pu_ht_before_discount'], 2, '.', '' ) : number_format( $item['item_pu_ht'], 2, '.', '' ) );
								$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] =  ( !empty($item['item_discount_value']) )  ?  number_format($item['item_discount_value'], 2, '.', '') : number_format(0, 2, '.', '' );
								$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] =  number_format( ($item['item_pu_ht'] * $item['item_qty']), 2, '.', '' );
								/** TVA **/
								$sub_tpl_component['INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT'] = number_format($item['item_tva_total_amount'], 2, '.', '' );
								$sub_tpl_component['INVOICE_ROW_ITEM_TVA_RATE'] = $item['item_tva_rate'];
								
								
								$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = number_format( $item['item_total_ttc'], 2, '.', '' );
								
								
								/** Checking Rate amount **/
								 $checking = self::check_product_price( $item['item_total_ht'],  $item['item_total_ttc'], $item['item_tva_total_amount'], $item['item_tva_rate'], $item['item_id'], $invoice_ref, $order_id );
								
								 if( !$checking ) {
								 	return __('Invoice cannot be generate because an error was found. The website administrator has been warned.', 'wpshop');
								 }
								
								if ( $bon_colisage ) {
									$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('bon_colisage_row', $sub_tpl_component, array(), 'common');
								}
								else {
									if ( $discounts_exists ) {
										$discounted_total_per_item = $item['item_total_ht'];
										/** Unit Discount **/
										if ( !empty($item['item_unit_discount_amount']) && !empty($item['item_unit_discount_value']) ) {
											$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_AMOUNT'] = number_format( $item['item_unit_discount_amount'], 2, '.', '');
											$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_VALUE'] = number_format( $item['item_unit_discount_value'], 2, '.', '');
											$discounted_total_per_item = $discounted_total_per_item - $item['item_unit_discount_amount'];
										}
										else {
											$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_AMOUNT'] = number_format( 0, 2, '.', '');
											$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_VALUE'] = number_format( 0, 2, '.', '');
										}
										
										/** Global Discount **/
										if ( !empty($item['item_global_discount_amount']) && !empty($item['item_global_discount_value']) ) {
											$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_AMOUNT'] = number_format( $item['item_global_discount_amount'], 2, '.', '');
											$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_VALUE'] = number_format( $item['item_global_discount_value'], 2, '.', '');
											$discounted_total_per_item = $discounted_total_per_item - $item['item_global_discount_amount'];
										}
										else {
											$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_AMOUNT'] = number_format( 0, 2, '.', '');
											$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_VALUE'] = number_format( 0, 2, '.', '');
										}
										
										$total_discounted += $discounted_total_per_item;
										/** Total HT Discounted **/
										$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNTED_HT_TOTAL'] = number_format( $discounted_total_per_item, 2, '.', '');
										
										$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row_with_discount', $sub_tpl_component, array(), 'common');
									}
									else {
										$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $sub_tpl_component, array(), 'common');
									}
								}
								unset( $sub_tpl_component );
								
								/** Check TVA **/
								if ( empty($order_tva[ $item['item_tva_rate'] ]) ) {
									$order_tva[ $item['item_tva_rate'] ] = $item['item_tva_total_amount'];
								}
								else {
									$order_tva[ $item['item_tva_rate'] ] += $item['item_tva_total_amount'];
								}

							}
						}
						
						
						
						
						/** Display Partials payments **/
						$total_partial_payment = 0; $last_payment = 0;
						if ( !empty($order_postmeta['order_payment']) && !empty($order_postmeta['order_payment']['received']) && !$bon_colisage ) {
							foreach( $order_postmeta['order_payment']['received'] as $received_payment ) {
								if ( !empty($received_payment['invoice_ref']) && $received_payment['invoice_ref'] != $order_postmeta['order_invoice_ref'] ) {
									$sub_tpl_component = array();
									$sub_tpl_component['INVOICE_ROW_ITEM_REF'] = $received_payment['invoice_ref'];
									/** Item name **/
									$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = sprintf( __('Partial payment on order %1$s', 'wpshop'), $order_postmeta['order_key'], __( $received_payment['method'], 'wpshop'), $received_payment['payment_reference']);
									$sub_tpl_component['INVOICE_ROW_ITEM_DETAIL'] = '';
									$sub_tpl_component['INVOICE_ROW_ITEM_QTY'] = 1;
									$sub_tpl_component['INVOICE_ROW_ITEM_PU_HT'] = '-' . number_format( $received_payment['received_amount'], 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] =  number_format(0, 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = '-' . number_format( $received_payment['received_amount'], 2, '.', '' );
									/** TVA **/
									
									
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT'] = number_format(0, 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_RATE'] = 0;
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = '-' . number_format( $received_payment['received_amount'], 2, '.', '' );
									
									if ( $discounts_exists ) {
										$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNTED_HT_TOTAL'] = '-'.number_format( $received_payment['received_amount'], 2, '.', '');
										$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_AMOUNT'] = number_format( 0, 2, '.', '');
										$sub_tpl_component['INVOICE_ROW_ITEM_UNIT_DISCOUNT_VALUE'] = number_format( 0, 2, '.', '');
										$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_AMOUNT'] = number_format( 0, 2, '.', '');
										$sub_tpl_component['INVOICE_ROW_ITEM_GLOBAL_DISCOUNT_VALUE'] = number_format( 0, 2, '.', '');
										
										$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row_with_discount', $sub_tpl_component, array(), 'common');
									}
									else {
										$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $sub_tpl_component, array(), 'common');
									}
									unset( $sub_tpl_component );
									$total_partial_payment += ( !empty($received_payment['received_amount']) ) ? $received_payment['received_amount'] : 0;
								}
								else {
									$last_payment += ( !empty($received_payment['received_amount']) ) ? $received_payment['received_amount'] : 0;
								}
								
							}
						}
					}
					else {
						/** Display Partials payments **/
						$total_partial_payment = 0; $last_payment = 0;
						if ( !empty($order_postmeta['order_payment']) && !empty($order_postmeta['order_payment']['received']) && !$bon_colisage ) {
							foreach( $order_postmeta['order_payment']['received'] as $received_payment ) {
								if ( !empty($received_payment['invoice_ref']) && !empty($invoice_ref) && $received_payment['invoice_ref'] == $invoice_ref ) {
									$sub_tpl_component = array();
									$sub_tpl_component['INVOICE_ROW_ITEM_REF'] = $received_payment['invoice_ref'];
									/** Item name **/
									$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = sprintf( __('Partial payment on order %1$s', 'wpshop'), $order_postmeta['order_key'], __( $received_payment['method'], 'wpshop'), $received_payment['payment_reference']);
									$sub_tpl_component['INVOICE_ROW_ITEM_DETAIL'] = '';
									$sub_tpl_component['INVOICE_ROW_ITEM_QTY'] = 1;
									$sub_tpl_component['INVOICE_ROW_ITEM_PU_HT'] = number_format( $received_payment['received_amount'], 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] = number_format(0, 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = number_format( $received_payment['received_amount'], 2, '.', '' );
									/** TVA **/
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_TOTAL_AMOUNT'] = number_format(0, 2, '.', '' );
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_RATE'] = 0;
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = number_format( $received_payment['received_amount'], 2, '.', '' );
									$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $sub_tpl_component, array(), 'common');
									unset( $sub_tpl_component );
									$total_partial_payment += $received_payment['received_amount'];
								}
							}
						}
					}
					
						
					
					
					/** Summary of order **/
					$summary_tpl_component = array();
					$tpl_component['INVOICE_SUMMARY_PART'] = $summary_tpl_component['INVOICE_SUMMARY_TAXES'] = '';
					if ( !$bon_colisage ) {
						if ( !empty( $order_tva ) ) {
							foreach( $order_tva as $tax_rate => $tax_amount ) {
								if ( $tax_amount > 0 ) {
									$tax_rate = ( !empty($tax_rate) && $tax_rate == 'VAT_shipping_cost' ) ?  __('on Shipping cost', 'wpshop').' '.WPSHOP_VAT_ON_SHIPPING_COST : $tax_rate;
									$sub_tpl_component['SUMMARY_ROW_TITLE'] = sprintf( __('Total taxes amount %1$s', 'wpshop'), $tax_rate . '%' );
									$sub_tpl_component['SUMMARY_ROW_VALUE'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount) . ' ' . wpshop_tools::wpshop_get_currency();
									$summary_tpl_component['INVOICE_SUMMARY_TAXES'] .= wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
									unset( $sub_tpl_component );
								}
								elseif( $is_partial_payment ) {
									$tax_rate = 0;
									$tax_amount = number_format( 0, 2, ',', '') . ' ' . wpshop_tools::wpshop_get_currency();
									$sub_tpl_component['SUMMARY_ROW_TITLE'] = sprintf( __('Total taxes amount %1$s', 'wpshop'), $tax_rate . '%' );
									$sub_tpl_component['SUMMARY_ROW_VALUE'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount) . ' ' . wpshop_tools::wpshop_get_currency();
									$summary_tpl_component['INVOICE_SUMMARY_TAXES'] .= wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
									unset( $sub_tpl_component );
								}
							}
						}
						
						/** If Discount Exist **/
						if ( !empty($total_discounted) && $discounts_exists ) {
							$sub_tpl_component['SUMMARY_ROW_TITLE'] = __('Discounted Total ET', 'wpshop');
							$sub_tpl_component['SUMMARY_ROW_VALUE'] = number_format( $total_discounted, 2, '.', '') . ' ' . wpshop_tools::wpshop_get_currency();
							$summary_tpl_component['INVOICE_SUMMARY_TOTAL_DISCOUNTED'] = wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
							unset( $sub_tpl_component );
						}
						else {
							$summary_tpl_component['INVOICE_SUMMARY_TOTAL_DISCOUNTED'] = '';
						}
						
						$summary_tpl_component['INVOICE_ORDER_SHIPPING_COST'] = ( $is_partial_payment ) ? number_format( 0, 2, ',', '') : number_format( ( (!empty($order_postmeta['order_shipping_cost']) ) ? $order_postmeta['order_shipping_cost'] : 0 ), 2, ',', '' );
						
						$summary_tpl_component['INVOICE_ORDER_GRAND_TOTAL'] = ( $is_partial_payment ) ?  number_format($total_partial_payment, 2, ',', '' ) : number_format( $order_postmeta['order_grand_total'] - $total_partial_payment , 2, ',', '' );
						$summary_tpl_component['INVOICE_ORDER_TOTAL_HT'] = ( $is_partial_payment ) ?  number_format($total_partial_payment, 2, ',', '' ) : number_format( $order_postmeta['order_total_ht'], 2, ',', '' );
						
						$summary_tpl_component['TOTAL_BEFORE_DISCOUNT'] = number_format($order_postmeta['order_grand_total_before_discount'], 2, ',', '');
						
						
						
						
						/** Amount paid **/
						$sub_tpl_component['SUMMARY_ROW_TITLE'] = __('Amount already paid', 'wpshop');
						$sub_tpl_component['SUMMARY_ROW_VALUE'] = ( $is_partial_payment ) ?  number_format($total_partial_payment, 2, ',', '' ). ' ' . wpshop_tools::wpshop_get_currency() : number_format($last_payment, 2, ',', '') . ' ' . wpshop_tools::wpshop_get_currency();
						$summary_tpl_component['INVOICE_SUMMARY_MORE'] = wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
						unset( $sub_tpl_component );
						
						
						/** If Discount Exist **/
						if ( !empty($order_postmeta['coupon_id']) && !empty($order_postmeta['order_discount_value']) ) {
							$tpl_discount_component = array();
							$tpl_discount_component['DISCOUNT_VALUE'] = ($order_postmeta['order_discount_type'] == 'percent') ? number_format($order_postmeta['order_discount_amount_total_cart'], 2, ',', '') : number_format($order_postmeta['order_discount_value'], 2, ',', '');
						
							$tpl_discount_component['TOTAL_BEFORE_DISCOUNT'] = number_format($order_postmeta['order_grand_total_before_discount'], 2, ',', '');
							$summary_tpl_component['INVOICE_ORDER_DISCOUNT'] = wpshop_display::display_template_element('invoice_discount_part', $tpl_discount_component, array(), 'common');
							unset( $tpl_discount_component );
						}
						else {
							$summary_tpl_component['INVOICE_ORDER_DISCOUNT'] = '';
						}
						
						$tpl_component['INVOICE_SUMMARY_PART'] = wpshop_display::display_template_element('invoice_summary_part', $summary_tpl_component, array(), 'common');
						unset( $summary_tpl_component );
					}
					
					
					/** IBAN Include on quotation **/
					if ( $is_quotation ) {
						/** If admin want to include his IBAN to quotation */
						$iban_options = get_option('wpshop_paymentMethod_options');
						$payment_options = get_option('wps_payment_mode');
						if ( !empty($payment_options) && !empty($payment_options['mode']) && !empty($payment_options['mode']['banktransfer']) && $payment_options['mode']['banktransfer']['active'] == 'on' ) {
							if ( !empty($iban_options) && !empty($iban_options['banktransfer']) && !empty($iban_options['banktransfer']['add_in_quotation']) ) {
								$tpl_component['IBAN_INFOS']  = __('Payment by Bank Transfer on this bank account', 'wpshop'). ' : <br/>';
								$tpl_component['IBAN_INFOS'] .= __('Bank name', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['bank_name']) ) ? $iban_options['banktransfer']['bank_name'] : ''). '<br/>';
								$tpl_component['IBAN_INFOS'] .= __('IBAN', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['iban']) ) ? $iban_options['banktransfer']['iban'] : ''). '<br/>';
								$tpl_component['IBAN_INFOS'] .= __('BIC/SWIFT', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['bic']) ) ? $iban_options['banktransfer']['bic'] : ''). '<br/>';
								$tpl_component['IBAN_INFOS'] .= __('Account owner name', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['accountowner']) ) ? $iban_options['banktransfer']['accountowner'] : ''). '<br/>';
							}
						}
					}
					else {
						$tpl_component['IBAN_INFOS'] = '';
					}
					
					
					/** Received payements **/
					if ( !$is_partial_payment && !$bon_colisage ) {
						$tpl_component['RECEIVED_PAYMENT'] = self::generate_received_payment_part( $order_id );		
					}	
					else {
						$tpl_component['RECEIVED_PAYMENT'] = '';
					}		
					
					
					/** Invoice footer **/
					$tpl_component['INVOICE_FOOTER'] = self::generate_footer_invoice();

					$output = wpshop_display::display_template_element('invoice_page_content', $tpl_component, array(), 'common');
				}
				else {
					$output =  __('No order information has been found', 'wpshop');
				}
			}
			else {
				$output =  __('You requested a page that does not exist anymore. Please verify your request or ask the site administrator', 'wpshop');
			}
			return $output;
		}
		
		
		/** 
		 * Return the payment list part 
		 * @param integer $order_id
		 * @return string
		 */
		function generate_received_payment_part( $order_id ) {
			$output = '';
			$tpl_component = array();
			$tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'] = '';
			if ( !empty($order_id) ) {
				$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
				if ( !empty($order_postmeta['order_payment']) && !empty($order_postmeta['order_payment']['received']) ) {
					foreach( $order_postmeta['order_payment']['received'] as $payment ) {
						if ( !empty($payment) ) {
							$sub_tpl_component = array();
							$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_RECEIVED_AMOUNT'] = ( !empty($payment['received_amount']) ) ? number_format( $payment['received_amount'], 2, ',', '' ) : 0;
							$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_DATE'] = (!empty($payment['date']) ) ? $payment['date'] : '';
							$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_METHOD'] = ( !empty($payment['method']) ) ? __($payment['method'], 'wpshop' ) : '';
							$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_PAYMENT_REFERENCE'] = ( !empty($payment['payment_reference']) ) ? $payment['payment_reference'] : '';
							$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_INVOICE_REF'] = ( !empty($payment['invoice_ref']) ) ? $payment['invoice_ref'] : '';
							$tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'] .= wpshop_display::display_template_element('received_payment_row', $sub_tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
						}
					}
				}
				$output = wpshop_display::display_template_element('received_payment', $tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
				unset( $tpl_component );
			} 
			return $output;
		}
		
		
		/** Return the validity period of a quotation **/
		function quotation_validate_period( $quotation_date ) {
			$quotation_options = get_option('wpshop_quotation_validate_time');
			if ( !empty($quotation_options) && !empty($quotation_options['number']) && !empty($quotation_options['time_type']) ) {
				$timestamp_quotation = strtotime($quotation_date);
				$timestamp_validity_date_quotation = 0;
				$query = '';
				$date = '';
				global $wpdb;
				switch ( $quotation_options['time_type'] ) {
					case 'day' :
						$query = $wpdb->prepare("SELECT DATE_ADD('" . $quotation_date . "', INTERVAL " .$quotation_options['number']. " DAY) ");
						break;
					case 'month' :
						$query = $wpdb->prepare("SELECT DATE_ADD('" . $quotation_date . "', INTERVAL " .$quotation_options['number']. " MONTH) ");
						break;
					case 'year' :
						$query = $wpdb->prepare("SELECT DATE_ADD('" . $quotation_date . "', INTERVAL " .$quotation_options['number']. " YEAR) ");
						break;
					default :
						$query = $wpdb->prepare("SELECT DATE_ADD('" . $quotation_date . "', INTERVAL 15 DAY) ");
						break;
				}
				if ( $query != null) {
					$date = mysql2date('d F Y', $wpdb->get_var($query), true);
				}
				return  sprintf( __('Quotation validity date %s', 'wpshop'), $date ) ;
			}
		}

		
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
		
		function generate_invoice_sender_part() {
			$output ='';
			$company = get_option('wpshop_company_info', array());
			$emails = get_option('wpshop_emails', array());
			if ( !empty($company) ) {
				$tpl_component['COMPANY_EMAIL'] = ( !empty($emails) && !empty($emails['contact_email']) ) ? $emails['contact_email'] : '';
				$tpl_component['COMPANY_WEBSITE'] = site_url();
				foreach ( $company as $company_info_key => $company_info_value ) {
						
					switch ($company_info_key) {
						case 'company_rcs' :
							$data = ( !empty($company_info_value) ) ? __('RCS', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_capital' :
							$data = ( !empty($company_info_value) ) ? __('Capital', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_siren' :
							$data = ( !empty($company_info_value) ) ? __('SIREN', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_siret' :
							$data = ( !empty($company_info_value) ) ? __('SIRET', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_tva_intra' :
							$data = ( !empty($company_info_value) ) ? __('TVA Intracommunautaire', 'wpshop').' : '.$company_info_value : '';
							break;
						default :
							$data = $company_info_value;
							break;
					}
					$tpl_component[ strtoupper( $company_info_key) ] = $data;
				}
				$output = wpshop_display::display_template_element('invoice_sender_formatted_address', $tpl_component, array(), 'common');
			}
			return $output;
		}

		
		function generate_receiver_part( $order_id, $bon_colisage = false ) {
			$output = '';
			$order_customer_postmeta = get_post_meta($order_id, '_order_info', true);
			$order_postmeta = get_post_meta( $order_id, '_order_postmeta', true );
			$address_info = ( $bon_colisage ) ? ( ( !empty($order_customer_postmeta['shipping']) && !empty($order_customer_postmeta['shipping']['address']) && is_array($order_customer_postmeta['shipping']['address']) )  ? $order_customer_postmeta['shipping']['address'] : array() ) : ( ( !empty($order_customer_postmeta['billing']) && !empty($order_customer_postmeta['billing']['address']) && is_array($order_customer_postmeta['billing']['address']) )  ? $order_customer_postmeta['billing']['address'] : array() );
			
				
			if ( !empty($order_customer_postmeta) && !empty($address_info) ) {
				$default_address_attributes = array('CIVILITY', 'ADDRESS_LAST_NAME', 'ADDRESS_FIRST_NAME', 'ADDRESS', 'POSTCODE', 'CITY', 'STATE', 'COUNTRY', 'PHONE', 'ADDRESS_USER_EMAIL', 'COMPANY');
				foreach ( $default_address_attributes as $default_address_attribute ) {
					$tpl_component[$default_address_attribute] = '';
				}
					
				foreach ( $address_info as $order_customer_info_key => $order_customer_info_value ) {
					$tpl_component[strtoupper($order_customer_info_key)] = '';
					if ( $order_customer_info_key == 'civility') {
						global $wpdb;
						$query = $wpdb->prepare('SELECT value FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id= %d', $order_customer_info_value);
						$civility = $wpdb->get_var($query);
						$tpl_component[strtoupper($order_customer_info_key)] = (!empty($civility) ) ? $civility : '';
					}
					else if( $order_customer_info_key == 'country') {
						foreach (unserialize(WPSHOP_COUNTRY_LIST) as $key=>$value) {
							if ( $order_customer_info_value == $key) {
								$tpl_component[strtoupper($order_customer_info_key)] = $value;
							}
						}
					}
					elseif( $order_customer_info_key == 'phone' ){
						$tpl_component[strtoupper($order_customer_info_key)] = (!empty($order_customer_info_value) ) ? __('Phone', 'wpshop').' : '.$order_customer_info_value : '';
					}
					else {
						$tpl_component[strtoupper($order_customer_info_key)] = (!empty($order_customer_info_value) ) ? $order_customer_info_value : '';
					}
				}
			
				if ( empty($tpl_component['PHONE']) ) {
					$tpl_component['PHONE'] = ( !empty( $order_customer_postmeta['billing']['address']['phone']) ) ? __('Phone', 'wpshop').' : '.$order_customer_postmeta['billing']['address']['phone'] : '';
				}
				
				if ( empty($tpl_component['ADDRESS_USER_EMAIL']) || ( empty($tpl_component['ADDRESS_USER_EMAIL']) && $bon_colisage ) ){
					$user_info = get_userdata( $order_postmeta['customer_id'] );
					$tpl_component['ADDRESS_USER_EMAIL'] = ( !empty($user_info) && !empty($user_info->user_email) ) ? $user_info->user_email : '';
				}
				
				
				
				$output = wpshop_display::display_template_element('invoice_receiver_formatted_address', $tpl_component, array(), 'common');
			}
			return $output;
		}


		function generate_footer_invoice(){
			$output ='';
			$company = get_option('wpshop_company_info', array());
			$emails = get_option('wpshop_emails', array());
			if ( !empty($company) ) {
				$tpl_component['COMPANY_EMAIL'] = ( !empty($emails) && !empty($emails['contact_email']) ) ? $emails['contact_email'] : '';
				$tpl_component['COMPANY_WEBSITE'] = site_url();
				foreach ( $company as $company_info_key => $company_info_value ) {
			
					switch ($company_info_key) {
						case 'company_rcs' :
							$data = ( !empty($company_info_value) ) ? __('RCS', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_capital' :
							$data = ( !empty($company_info_value) ) ? __('Capital', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_siren' :
							$data = ( !empty($company_info_value) ) ? __('SIREN', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_siret' :
							$data = ( !empty($company_info_value) ) ? __('SIRET', 'wpshop').' : '.$company_info_value : '';
							break;
						case 'company_tva_intra' :
							$data = ( !empty($company_info_value) ) ? __('TVA Intracommunautaire', 'wpshop').' : '.$company_info_value : '';
							break;
						default :
							$data = $company_info_value;
							break;
					}
					$tpl_component[ strtoupper( $company_info_key) ] = $data;
				}
				$output = wpshop_display::display_template_element('invoice_footer', $tpl_component, array(), 'common');
			}
			return $output;
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
// 				exit( $message );
				wp_mail( $wpshop_email_option['contact_email'], __('Error on invoice generation', 'wpshop') , $message, $headers);
			}
		}
		
	}

}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_modules_billing") ) {
	$wpshop_modules_billing = new wpshop_modules_billing();
}