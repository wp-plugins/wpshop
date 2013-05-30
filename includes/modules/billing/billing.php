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
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
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
				$payment_option = get_option('wpshop_paymentMethod');
				if ( !empty($payment_option) && !empty($payment_option['banktransfer']) && $payment_option['banktransfer'] == 'on') {
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
			$t = wpshop_address::get_addresss_form_fields_by_type ( $input['choice'] );

			$the_code = '';
			foreach( $t[$input['choice']] as $group_id => $group_def ) {
				if ( array_key_exists( $input['integrate_into_register_form_matching_field']['user_email'], $group_def['content']) ) {
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

			/**	Get last invoice number	*/
			$billing_current_number = get_option('wpshop_billing_current_number', false);

			/** If the counter doesn't exist, we initiate it */
			if (!$billing_current_number) {
				$billing_current_number = 1;
			}
			else {
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
		function generate_html_invoice( $order_id, $invoice_ref ) {
			/**	Order reading	*/
			$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
			$is_quotation = ( empty($order_postmeta['order_key']) && !empty($order_postmeta['order_temporary_key']) ) ? true : false;
			$tpl_component = array();
			$no_invoice_found = true;

			/** Header Common infos */
			$logo_options = get_option('wpshop_logo');
			$tpl_component['INVOICE_SUMMARY_MORE'] = '';
			$tpl_component['INVOICE_LOGO'] = ( !empty($logo_options) ) ? '<img src="' .$logo_options .'" alt="" />' : '';
			$tpl_component['INVOICE_ORDER_KEY_INDICATION'] = sprintf( __('Order n. %s', 'wpshop'), $order_postmeta['order_key'] );
			$tpl_component['INVOICE_ORDER_DATE_INDICATION'] = sprintf( __('Order date %s', 'wpshop'), '{WPSHOP_INVOICE_ORDER_DATE}') ;
			$tpl_component['INVOICE_VALIDATE_TIME'] = '';

			$tpl_component['INVOICE_ORDER_SHIPPING_COST'] = round($order_postmeta['order_shipping_cost'], 2);

			$tpl_component['IBAN_INFOS'] = '';



			/** If it's a quotation */
			if ( $is_quotation ) {
				$no_invoice_found = false;
				$tpl_component['INVOICE_TITLE'] = __('Quotation', 'wpshop');
				$tpl_component['INVOICE_ORDER_INVOICE_REF'] =  sprintf( __('Ref. %s', 'wpshop'),$order_postmeta['order_temporary_key']);
				$tpl_component['INVOICE_ORDER_KEY_INDICATION'] = '';
				$tpl_component['INVOICE_ORDER_DATE_INDICATION'] = sprintf( __('Quotation date %s', 'wpshop'), '{WPSHOP_INVOICE_ORDER_DATE}') ;
				$quotation_options = get_option('wpshop_quotation_validate_time');
				$quotation_date = $order_postmeta['order_date'];

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
					$tpl_component['INVOICE_VALIDATE_TIME'] = sprintf( __('Quotation validity date %s', 'wpshop'), $date ) ;
					/** If admin want to include his IBAN to quotation */
					$iban_options = get_option('wpshop_paymentMethod_options');
					$payment_options = get_option('wpshop_paymentMethod');
					if ( !empty($payment_options) && !empty($payment_options['banktransfer']) && $payment_options['banktransfer'] == 'on' ) {
						if ( !empty($iban_options) && !empty($iban_options['banktransfer']) && !empty($iban_options['banktransfer']['add_in_quotation']) ) {
							$tpl_component['IBAN_INFOS']  = __('Payment by Bank Transfer on this bank account', 'wpshop'). ' : <br/>';
							$tpl_component['IBAN_INFOS'] .= __('Bank name', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['bank_name']) ) ? $iban_options['banktransfer']['bank_name'] : ''). '<br/>';
							$tpl_component['IBAN_INFOS'] .= __('IBAN', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['iban']) ) ? $iban_options['banktransfer']['iban'] : ''). '<br/>';
							$tpl_component['IBAN_INFOS'] .= __('BIC/SWIFT', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['bic']) ) ? $iban_options['banktransfer']['bic'] : ''). '<br/>';
							$tpl_component['IBAN_INFOS'] .= __('Account owner name', 'wpshop'). ' : '.( (!empty($iban_options['banktransfer']['accountowner']) ) ? $iban_options['banktransfer']['accountowner'] : ''). '<br/>';
						}
					}
				}


			/**	Add invoice lines	*/
					$tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'] = '';
					$tpl_component['INVOICE_ROWS'] = '';
					$tpl_component['INVOICE_HEADER'] = '';
					if ( !empty($order_postmeta['order_items']) ) {
						foreach ( $order_postmeta['order_items'] as $item_id => $item_content ) {
							foreach ( $item_content as $key => $value ) {
								if ( !is_array($value) ) {
									$the_value = $value;
									if ( strpos($key, 'ht') || strpos($key, 'ttc') || strpos($key, 'amount') || strpos($key, 'tax') ) {
										$the_value = number_format($value, 2);
										$the_value = wpshop_display::format_field_output('wpshop_product_price', $the_value);
									}
									if ( strtoupper($key) == 'ITEM_REF' ) {
										$the_value = wordwrap($the_value, 14, "<br/>", true);
									}
									$tpl_component['INVOICE_ROW_' . strtoupper($key)] = $the_value;

									if ( $key == 'item_pu_ht') {
										if ( !empty($item_content['item_pu_ht_before_discount']) ) {
											$tpl_component['INVOICE_ROW_' . strtoupper($key)] = number_format($item_content['item_pu_ht_before_discount'], 2, ',', '.');
										}
									}
									/**	Get attribute order for current product	*/
									$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
									$output_order = array();
									if ( count($product_attribute_order_detail) > 0 ) {
										if ( !empty($product_attribute_order_detail) ) {
											foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
												foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
													if ( !empty($attribute_def->code) )
														$output_order[$attribute_def->code] = $position;
												}
											}
										}
									}
									$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item_content['item_meta'], $output_order, 'invoice_print', 'common');
									ksort($variation_attribute_ordered['attribute_list']);
									$tpl_component['CART_PRODUCT_MORE_INFO'] = '';
									foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
										$tpl_component['CART_PRODUCT_MORE_INFO'] .= $attribute_variation_to_output;
									}
									$tpl_component['INVOICE_ROW_ITEM_DETAIL'] = !empty($tpl_component['CART_PRODUCT_MORE_INFO']) ? wpshop_display::display_template_element('invoice_row_item_detail', $tpl_component, array(), 'common') : '';
								}
							}
							$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $tpl_component, array(), 'common');
						}
						$tpl_component['INVOICE_HEADER'] .= wpshop_display::display_template_element('invoice_row_header', $tpl_component, array(), 'common');
					}

			}

			if ( !empty($order_postmeta) ) {
				$tax_rate_to_take = 0;
				if ( !empty($order_postmeta['order_tva']) ) {
					foreach ( $order_postmeta['order_tva'] as $tax_rate => $tax_total_amount) {
						$tax_rate_to_take = $tax_rate;
						continue;
					}
				}

				$tpl_component['RECEIVED_PAYMENT'] = '';

				/**	In case the request is a partial payment invoice	*/
				$is_partial_payment_invoice = false;
				if ( !empty($invoice_ref) && !empty($order_postmeta['order_payment']) ) {
					if ( !empty($order_postmeta['order_payment']['received']) ) {
						$partial_payment = array();
						foreach ( $order_postmeta['order_payment']['received'] as $payment_key => $payment_content ) {
							if ( in_array($invoice_ref, $payment_content) && (empty($order_postmeta['order_invoice_ref']) || (!empty($order_postmeta['order_invoice_ref']) && ($order_postmeta['order_invoice_ref'] != $invoice_ref)))) {
								$partial_payment = $payment_content;
								continue;
							}
						}
						if ( !empty($partial_payment) ) {
							$tpl_component['INVOICE_TITLE'] = sprintf( __('Bill payment', 'wpshop'), $invoice_ref, $order_id );
							if (!empty($_GET['bon_colisage']) ) {
								$tpl_component['INVOICE_ORDER_INVOICE_REF'] = '';
							}
							else {
								$tpl_component['INVOICE_ORDER_INVOICE_REF'] = sprintf( __('Ref. %s', 'wpshop'),$invoice_ref);
							}
							$is_partial_payment_invoice = true;
							$no_invoice_found = false;

							$partial_payment_et_price = ( $partial_payment['received_amount'] / ( 1 + ($tax_rate_to_take/100) ) );
							$tax_amount = $partial_payment['received_amount'] - $partial_payment_et_price;

							/**	Add invoice lines	*/
							$sub_tpl_component = array();
							$sub_tpl_component['INVOICE_ROW_ITEM_REF'] = '-';
							$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = sprintf( __('Partial payment on order %1$s', 'wpshop'), $order_postmeta['order_key'], __( $payment_content['method'], 'wpshop'), $payment_content['payment_reference']);
							$sub_tpl_component['INVOICE_ROW_ITEM_QTY'] = 1;
							$sub_tpl_component['INVOICE_ROW_ITEM_PU_HT'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment_et_price);
							//$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] = wpshop_display::format_field_output('wpshop_product_price', 24.90);
							$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment_et_price);
							$sub_tpl_component['INVOICE_ROW_ITEM_TVA_AMOUNT'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount);
							$sub_tpl_component['INVOICE_ROW_ITEM_TVA_RATE'] = $tax_rate_to_take;
							$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = $partial_payment['received_amount'];
							$sub_tpl_component['INVOICE_ROW_ITEM_DETAIL'] = '';
							$tpl_component['INVOICE_ROWS'] = wpshop_display::display_template_element('invoice_row', $sub_tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
							$tpl_component['INVOICE_HEADER'] = wpshop_display::display_template_element('invoice_row_header', $tpl_component, array(), 'common');
						}
					}
				}

				/**	If the request is about a complete invoice	*/
				if ( !$is_partial_payment_invoice && !empty($order_postmeta['order_invoice_ref']) ) {
					$tpl_component['INVOICE_ORDER_KEY_INDICATION'] = sprintf( __('Order n. %s', 'wpshop'), $order_postmeta['order_key'] );
					$tpl_component['INVOICE_TITLE'] = ( $is_quotation ) ?  __('quotation', 'wpshop') : sprintf( __('Invoice', 'wpshop'), $invoice_ref, $order_id );
					$tpl_component['INVOICE_TITLE'] = ( !empty($_GET['bon_colisage']) ? __('Products List', 'wpshop') : $tpl_component['INVOICE_TITLE']);
					$no_invoice_found = false;

					/**	Add invoice lines	*/
					$tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'] = '';
					$tpl_component['INVOICE_ROWS'] = '';
					$tpl_component['INVOICE_HEADER'] = '';

					if ( !empty($order_postmeta['order_items']) ) {

						foreach ( $order_postmeta['order_items'] as $item_id => $item_content ) {
							foreach ( $item_content as $key => $value ) {
								if ( !is_array($value) ) {
									$the_value = $value;
									if ( strpos($key, 'ht') || strpos($key, 'ttc') || strpos($key, 'amount') || strpos($key, 'tax') ) {
										$the_value = number_format($value, 2);
										$the_value = wpshop_display::format_field_output('wpshop_product_price', $the_value);
									}
									if ( strtoupper($key) == 'ITEM_REF' ) {
										$the_value = wordwrap($the_value, 14, "<br/>", true);
									}
									if ( strtoupper($key) == 'ITEM_NAME' ) {
										$is_variation = get_post_meta($item_id, '_wpshop_variations_attribute_def', true);
										if ( !empty($is_variation) ) {
											$parent_product = wpshop_products::get_parent_variation($item_id);
											if ( !empty($parent_product) && !empty($parent_product['parent_post']) ) {
												$parent_post = $parent_product['parent_post'];
												$the_value = $parent_post->post_title;
											}
										}
									}
									$tpl_component['INVOICE_ROW_' . strtoupper($key)] = $the_value;
									if ( $key == 'item_pu_ht') {
										if ( !empty($item_content['item_pu_ht_before_discount']) ) {
											$tpl_component['INVOICE_ROW_' . strtoupper($key)] = number_format($item_content['item_pu_ht_before_discount'], 2, ',', '.');
										}
									}

									/**	Get attribute order for current product	*/
									$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
									$output_order = array();
									if ( count($product_attribute_order_detail) > 0 ) {
										foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
											foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
												if ( !empty($attribute_def->code) )
													$output_order[$attribute_def->code] = $position;
											}
										}
									}
									$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item_content['item_meta'], $output_order, 'invoice_print', 'common');
									ksort($variation_attribute_ordered['attribute_list']);
									$tpl_component['CART_PRODUCT_MORE_INFO'] = '';
									foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
										$tpl_component['CART_PRODUCT_MORE_INFO'] .= $attribute_variation_to_output;
									}
									$tpl_component['INVOICE_ROW_ITEM_DETAIL'] = !empty($tpl_component['CART_PRODUCT_MORE_INFO']) ? wpshop_display::display_template_element('invoice_row_item_detail', $tpl_component, array(), 'common') : '';
								}
							}
							if( !empty($_GET['bon_colisage']) ) {
								$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('bon_colisage_row', $tpl_component, array(), 'common');
							}
							else {
								$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $tpl_component, array(), 'common');
							}
							/** Check if there is a gift product */
							if ( !empty($order_postmeta) && !empty($order_postmeta['cart_rule']) && !empty($order_postmeta['cart_rule']['discount_type']) && $order_postmeta['cart_rule']['discount_type'] == 'gift_product') {
								$product = get_post( $order_postmeta['cart_rule']['discount_value'] );
								$option_name = '';
								if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
									$parent_product_infos = wpshop_products::get_parent_variation ( $product->ID );
									if ( !empty($parent_product_infos) && !empty($parent_product_infos['parent_post']) ) {
										$parent_post_infos = $parent_product_infos['parent_post'];
										$product_title = $parent_post_infos->post_title;

										$product_options = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);
										if ( !empty($product_options) && is_array($product_options) ) {
											$option_name = '';
											foreach( $product_options as $k=>$product_option) {
												$query = $wpdb->prepare('SELECT frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE code = %s', $k);
												$option_name .= $wpdb->get_var($query).' ';
												$query = $wpdb->prepare('SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE id= %d', $product_option);
												$option_name .=  $wpdb->get_var($query).' ';
											}
											$discount_value = $product_title ;
										}

									}
								}
								else {
									$discount_value = $product->post_title;
								}
								$tpl_component['INVOICE_ROW_ITEM_NAME'] = $discount_value.' ('.__('Gift product', 'wpshop').')';
								$tpl_component['INVOICE_ROW_ITEM_DETAIL'] = $option_name;
								$tpl_component['INVOICE_ROW_ITEM_REF'] = '';
								$tpl_component['INVOICE_ROW_ITEM_QTY'] = 1;
								if ( empty($_GET['bon_colisage'])) {
									$tpl_component['INVOICE_ROW_ITEM_PU_HT'] = number_format(0, 2);
									$tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] = number_format(0, 2);
									$tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = number_format(0, 2);
									$tpl_component['INVOICE_ROW_ITEM_TVA_AMOUNT'] = number_format(0, 2);
									$tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] =  number_format(0, 2);
									$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $tpl_component, array(), 'common');
								}
								else {
									$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('bon_colisage_row', $tpl_component, array(), 'common');
								}


							}
						}
						if( !empty($_GET['bon_colisage']) ) {
							$tpl_component['INVOICE_HEADER'] .= wpshop_display::display_template_element('bon_colisage_row_header', $tpl_component, array(), 'common');
						}
						else {
							$tpl_component['INVOICE_HEADER'] .= wpshop_display::display_template_element('invoice_row_header', $tpl_component, array(), 'common');
						}


					}

					/**	Add the different payment to the global invoice	*/
					$tpl_component['ALREADY_RECEIVED_AMOUNT'] = 0;
					$tpl_component['UNSTYLED_ALREADY_RECEIVED_AMOUNT'] = 0;
					if ( !empty($order_postmeta['order_payment']['received']) ) {
						foreach ( $order_postmeta['order_payment']['received'] as $payment_key => $payment_content ) {
							$payment_exist = false;
							if ( !empty($payment_content) && !empty($payment_content['status']) && ($payment_content['status'] == 'payment_received')) {
								if ( !empty($payment_content['invoice_ref']) && $payment_content['invoice_ref'] != $order_postmeta['order_invoice_ref']) {
									$sub_tpl_component = array();

									$partial_payment_et_price = ( $payment_content['received_amount'] / ( 1 + ($tax_rate_to_take/100)) );
									$tax_amount = $payment_content['received_amount'] - $partial_payment_et_price;

									$sub_tpl_component['INVOICE_ROW_ITEM_REF'] = $payment_content['invoice_ref'];
									$sub_tpl_component['INVOICE_ROW_ITEM_NAME'] = sprintf( __('Partial payment on order %1$s', 'wpshop'), $order_postmeta['order_key'], __( $payment_content['method'], 'wpshop'), $payment_content['payment_reference']);
									$sub_tpl_component['INVOICE_ROW_ITEM_QTY'] = 1;
									$sub_tpl_component['INVOICE_ROW_ITEM_PU_HT'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment_et_price);
									//$sub_tpl_component['INVOICE_ROW_ITEM_DISCOUNT_AMOUNT'] = wpshop_display::format_field_output('wpshop_product_price', 24.90);
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_HT'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment_et_price);
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_AMOUNT'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount);
									$sub_tpl_component['INVOICE_ROW_ITEM_TVA_RATE'] = $tax_rate_to_take;
									$sub_tpl_component['INVOICE_ROW_ITEM_TOTAL_TTC'] = '-' . $payment_content['received_amount'];

									$sub_tpl_component['CART_PRODUCT_MORE_INFO'] = sprintf( __('Paid by %1$s (ref. %2$s)', 'wpshop'), __( $payment_content['method'], 'wpshop'), $payment_content['payment_reference'], mysql2date('d/m/Y', $payment_content['date'], true));
									$sub_tpl_component['INVOICE_ROW_ITEM_DETAIL'] = !empty($sub_tpl_component['CART_PRODUCT_MORE_INFO']) ? wpshop_display::display_template_element('invoice_row_item_detail', $sub_tpl_component, array(), 'common') : '';

									$tpl_component['ALREADY_RECEIVED_AMOUNT'] += wpshop_display::format_field_output('wpshop_product_price', $payment_content['received_amount']);
									$tpl_component['UNSTYLED_ALREADY_RECEIVED_AMOUNT'] += $payment_content['received_amount'];
									$tpl_component['INVOICE_ROWS'] .= wpshop_display::display_template_element('invoice_row', $sub_tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
									unset($sub_tpl_component);
								}
								else {
									$tpl_component['ALREADY_RECEIVED_AMOUNT'] += $payment_content['received_amount'];
									$tpl_component['UNSTYLED_ALREADY_RECEIVED_AMOUNT'] += $payment_content['received_amount'];
								}
							$payment_exist = true;
							}
								if ($payment_exist && empty($_GET['bon_colisage'])) {
									$sub_tpl_component = array();
									$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_INVOICE_REF'] = '';
									foreach ( $payment_content as $payment_content_key => $payment_content_value ) {
										if ( strpos($payment_content_key, 'amount') ) {
											$payment_content_value = wpshop_display::format_field_output('wpshop_product_price', $payment_content_value) . ' ' . wpshop_tools::wpshop_get_currency();
										}
										elseif($payment_content_key == 'date') {
											$payment_content_value = mysql2date('d/m/Y H:i:s', $payment_content_value, true);
										}

										$sub_tpl_component['INVOICE_RECEIVED_PAYMENT_' . strtoupper($payment_content_key)] = ( !empty($payment_content_value) ) ? $payment_content_value : '';
									}
									$tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'] .= wpshop_display::display_template_element('received_payment_row', $sub_tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
									unset($sub_tpl_component);

							}

						}
						if ( !empty($tpl_component['ORDER_RECEIVED_PAYMENT_ROWS'])) {
						$tpl_component['RECEIVED_PAYMENT'] = wpshop_display::display_template_element('received_payment', $tpl_component, array('type' => 'invoice_line', 'id' => 'partial_payment'), 'common');
						}
						else {
							$tpl_component['RECEIVED_PAYMENT'] = '';
						}
					}

					$tpl_component['INVOICE_DUE_AMOUNT'] = $order_postmeta['order_grand_total'] - $tpl_component['UNSTYLED_ALREADY_RECEIVED_AMOUNT'];

					$sub_tpl_component = array();
					$sub_tpl_component['SUMMARY_ROW_TITLE'] = __('Amount already paid', 'wpshop');
					$sub_tpl_component['SUMMARY_ROW_VALUE'] = number_format($tpl_component['ALREADY_RECEIVED_AMOUNT'], 2, ',', '') . ' ' . wpshop_tools::wpshop_get_currency();
					$tpl_component['INVOICE_SUMMARY_MORE'] = wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');

					$sub_tpl_component = array();
					$sub_tpl_component['SUMMARY_ROW_TITLE'] = __('Due amount', 'wpshop');
					$sub_tpl_component['SUMMARY_ROW_VALUE'] = number_format($tpl_component['INVOICE_DUE_AMOUNT'],2, ',', '.') . ' ' . wpshop_tools::wpshop_get_currency();
					$tpl_component['INVOICE_SUMMARY_MORE'] .= wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
				}
				else {
					if ( !empty ($partial_payment) && empty($_GET['bon_colisage']) ) {
						$tpl_component['INVOICE_TITLE_PAGE_'] = sprintf( __('Bill payment %1$s for order %2$s', 'wpshop'), $partial_payment['invoice_ref'], $order_id);
						$tpl_component['INVOICE_SUMMARY_MORE'] = '';
						$tpl_component['INVOICE_SUMMARY_TAXES'] = '';

						$partial_payment_et_price = $partial_payment['received_amount'] / ( 1 + ($tax_rate_to_take/100));
						$tax_amount = $partial_payment['received_amount']  - $partial_payment_et_price;

						$tpl_component['INVOICE_ORDER_TOTAL_HT'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment_et_price);
						$tpl_component['INVOICE_ORDER_GRAND_TOTAL'] = wpshop_display::format_field_output('wpshop_product_price', $partial_payment['received_amount'] );
						$tpl_component['INVOICE_SUMMARY_TAXES'] = $partial_payment_et_price;
					}
					//$sub_tpl_component = array();
					//$sub_tpl_component['SUMMARY_ROW_TITLE'] = sprintf( __('Total taxes amount %1$s', 'wpshop'), $tax_rate . '%' );
					//$sub_tpl_component['SUMMARY_ROW_VALUE'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount) . ' ' . wpshop_tools::wpshop_get_currency();
					//$tpl_component['INVOICE_SUMMARY_TAXES'] = wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
				}

				/**	Fill the template with all existing key if not an array	*/
				if ( !empty($order_postmeta) ) {
					foreach ( $order_postmeta as $meta_key => $meta_value ) {
						if ( !is_array($meta_value) && !isset($tpl_component['INVOICE_' . strtoupper($meta_key)]) ) {
							if ( strpos($meta_key, 'ht') || strpos($meta_key, 'ttc') || strpos($meta_key, 'amount') || strpos($meta_key, 'tax') || strpos($meta_key, 'total') ) {
								$meta_value = number_format($meta_value, 2);
								$meta_value = wpshop_display::format_field_output('wpshop_product_price', $meta_value);
							}
							else if( strpos($meta_key, 'date') ) {
								$meta_value = mysql2date( 'd F Y H:i:s', $meta_value);
							}
							elseif( $meta_key == 'order_invoice_ref' && !empty($_GET['bon_colisage']) ) {
								$meta_value = '' ;
							}
							$tpl_component['INVOICE_' . strtoupper($meta_key)] = $meta_value;

						}
						else if (( $meta_key == 'order_tva' ) && (empty($tpl_component['INVOICE_SUMMARY_TAXES']))) {
							$tpl_component['INVOICE_SUMMARY_TAXES'] = '';
							foreach( $meta_value as $tax_rate => $tax_amount ){
								if ( !isset($tpl_component['INVOICE_SUMMARY_TAX_RATE_' . strtoupper( sanitize_title($tax_rate) )]) ) {
									$sub_tpl_component = array();
									$sub_tpl_component['SUMMARY_ROW_TITLE'] = sprintf( __('Total taxes amount %1$s', 'wpshop'), ( ($tax_rate == 'VAT_shipping_cost' ) ? __('on Shipping cost', 'wpshop').' '.WPSHOP_VAT_ON_SHIPPING_COST : $tax_rate ). '%' );
									$sub_tpl_component['SUMMARY_ROW_VALUE'] = wpshop_display::format_field_output('wpshop_product_price', $tax_amount) . ' ' . wpshop_tools::wpshop_get_currency();
									$tpl_component['INVOICE_SUMMARY_TAX_RATE_' . strtoupper( sanitize_title($tax_rate) )] = wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
									$tpl_component['INVOICE_SUMMARY_TAXES'] .= wpshop_display::display_template_element('invoice_summary_row', $sub_tpl_component, array(), 'common');
								}
							}
						}
					}
				}

				/**	Add information about company doing the invoice	*/
				$tpl_component['INVOICE_SENDER'] = '';
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
					$tpl_component['INVOICE_SENDER'] = wpshop_display::display_template_element('invoice_sender_formatted_address', $tpl_component, array(), 'common');
				}

				/**	Add information about the customer that will receive the invoice	*/
				$tpl_component['INVOICE_RECEIVER'] = '';
				$order_customer_postmeta = get_post_meta($order_id, '_order_info', true);
				if ( !empty($order_customer_postmeta) && !empty($order_customer_postmeta['billing']['address']) ) {
					$tpl_component['CIVILITY'] = $tpl_component['STATE'] = $tpl_component['COUNTRY'] = $tpl_component['PHONE'] = '';
					foreach ( $order_customer_postmeta['billing']['address'] as $order_customer_info_key => $order_customer_info_value ) {
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
						elseif( $order_customer_info_key == 'phone'){
							$tpl_component[strtoupper($order_customer_info_key)] = (!empty($order_customer_info_value) ) ? __('Phone', 'wpshop').' : '.$order_customer_info_value : '';
						}
						else {
							$tpl_component[strtoupper($order_customer_info_key)] = (!empty($order_customer_info_value) ) ? $order_customer_info_value : '';
						}
					}

					$tpl_component['INVOICE_RECEIVER'] = wpshop_display::display_template_element('invoice_receiver_formatted_address', $tpl_component, array(), 'common');
				}
			}

			$tpl_component ['INVOICE_FOOTER'] = wpshop_display::display_template_element('invoice_footer', $tpl_component, array(), 'common');
			/**	Output invoice	*/
			if ( !$no_invoice_found ) {
				if ( empty( $_GET['bon_colisage']) ) {
					$tpl_component['INVOICE_SUMMARY_PART'] = wpshop_display::display_template_element('invoice_summary_part', $tpl_component, array(), 'common');
					$tpl_component['AMOUNT_INFORMATION'] = sprintf( __('Amount are shown in %s', 'wpshop'), wpshop_tools::wpshop_get_currency( true ) );
				}
				else {
					$tpl_component['AMOUNT_INFORMATION'] = '';
					$tpl_component['INVOICE_SUMMARY_PART'] = '';
				}
				return wpshop_display::display_template_element('invoice_page_content', $tpl_component, array(), 'common');
			}
			else {
				return __('You requested a page that does not exist anymore. Please verify your request or ask the site administrator', 'wpshop');
			}
		}
	}

}

/**	Instanciate the module utilities if not	*/
if ( class_exists("wpshop_modules_billing") ) {
	$wpshop_modules_billing = new wpshop_modules_billing();
}

?>