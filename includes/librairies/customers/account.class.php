<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


/** Instantiate the class from the shortcode */
function wpshop_account_display_form() {
	global $wpdb, $wpshop, $wpshop_account, $civility;
	$output = '';
	$wpshop_account->managePost();

	$user_id = get_current_user_id();
	if ( !$user_id ) {
		echo $wpshop_account->display_login_form();
	}
	else {
		// Order status possibilities
		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		// Payment method possibilities
		$payment_method = array('paypal' => 'Paypal', 'check' => __('Check','wpshop'), 'cic' => __('Credit card','wpshop'));

		if (!empty($_GET['action'])) {

			/**	Edit personnal information	*/
			if ($_GET['action'] == 'editinfo') {
				$shipping_info = get_user_meta($user_id, 'shipping_info', true);
				$billing_info = get_user_meta($user_id, 'billing_info', true);
				$user_preferences = get_user_meta($user_id, 'user_preferences', true);

				/**	If there are existing addresses	*/
				if(!empty($shipping_info) && !empty($billing_info)) {
					/**	Add prefix for different address type	*/
					foreach($shipping_info as $k => $v):
						$shipping_info['shipping_'.$k] = $shipping_info[$k];
						unset($shipping_info[$k]);
					endforeach;

					foreach($billing_info as $k => $v):
						$billing_info['billing_'.$k] = $billing_info[$k];
						unset($billing_info[$k]);
					endforeach;
				}
				else {
					$shipping_info = $billing_info = array('first_name'=>null,'last_name'=>null,'address'=>null,'postcode'=>null,'city'=>null,'country'=>null);
				}

				$output = wpshop_display::display_template_element('wpshop_customer_addresses_form', array('CUSTOMER_ADDRESSES_FORM_CONTENT' => $wpshop_account->display_addresses_dashboard() . $wpshop_account ->display_commercial_newsletter_form(), 'CUSTOMER_ADDRESSES_FORM_BUTTONS' => '<input type="submit" name="submitbillingAndShippingInfo" id="submitbillingAndShippingInfo" value="' . __('Save','wpshop') . '" />'));
			}
			/**	Customer edit its addresses	*/
			elseif ($_GET['action']=='editinfo_account' ) {
				$output = wpshop_display::display_template_element('wpshop_customer_account_infos_form', array('CUSTOMER_ACCOUNT_INFOS_FORM' => $wpshop_account->display_account_form('', 'complete'), 'CUSTOMER_ACCOUNT_INFOS_FORM_BUTTONS' => '<input type="submit" name="submitOrderInfos" value="' . __('Save my account informations','wpshop') . '" />', 'CUSTOMER_ACCOUNT_INFOS_FORM_NONCE' => wp_create_nonce('wpshop_customer_register')));
			}
			// Edit an address
			elseif ($_GET['action'] == 'editAddress') {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE ID = ' .$_GET['id']. ' AND post_parent = ' .get_current_user_id(). '', '');
					$post = $wpdb->get_row($query);
					$attribute_set_id = get_post_meta($post->ID, '_wpshop_address_attribute_set_id', true);

					if ( !empty($post)) {
						echo $wpshop_account->display_form_fields($attribute_set_id, $_GET['id']);
					}
					else {
						wpshop_tools::wpshop_safe_redirect( $_SERVER['HTTP_REFERER'] );
					}
				}
			}
			// Choose the address type
			elseif( $_GET['action'] == 'choose_address' ) {
				$shipping_options = get_option('wpshop_shipping_address_choice');
				if ( !empty($shipping_options['activate']) ) {
					$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_name = "' .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. '" AND post_type = "' .WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES. '"', '');
					$entity_id = $wpdb->get_var($query);

					$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = ' .$entity_id. '', '');
					$content = $wpdb->get_results($query);

					$input_def['name'] = 'address_type';
					$input_def['id'] = 'address_type';
					$input_def['possible_value'] = $content;
					$input_def['type'] = 'select';
					$tpl_component = array();
					$tpl_component['ADDRESS_TYPE_CHOICE_FORM_ACTION'] = get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address';
					$tpl_component['ADDRESS_TYPE_LISTING_INPUT'] = wpshop_form::check_input_type($input_def);
					$output = wpshop_display::display_template_element('wpshop_customer_new_addresse_type_choice_form', $tpl_component);
					unset($tpl_component);
				}
				else {
					wpshop_tools::wpshop_safe_redirect( get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=add_address' );
				}
			}
			//Add a new address
			elseif ($_GET['action'] == 'add_address') {
				//Test if it's the first address of the user
				if ( isset($_GET['first']) ) {
					$billing_address_option = get_option('wpshop_billing_address');
					$shipping_address_option = get_option('wpshop_shipping_address_choice');

					$tpl_component = array();
					$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] = $wpshop_account->display_form_fields($billing_address_option['choice'], '', 'first');

					if ( $shipping_address_option['activate'] ) {
						$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '<p class="formField"><label><input type="checkbox" name="shiptobilling" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p>';
						$display = 'display:none;';
						$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '<div id="shipping_infos_bloc" style="'.$display.'">';
						$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= $wpshop_account->display_form_fields($shipping_address_option['choice'], '', 'first');
						$tpl_component['CUSTOMER_ADDRESSES_FORM_CONTENT'] .= '</div><br/>';
					}

					$tpl_component['CUSTOMER_ADDRESSES_FORM_BUTTONS'] = '<p class="formField"><input type="submit" name="submitbillingAndShippingInfo" id="submitbillingAndShippingInfo" value="' . __('Save','wpshop') . '" /></p>';
					$output = wpshop_display::display_template_element('wpshop_customer_addresses_form', $tpl_component);
					unset($tpl_component);
				}
				else {
					// Check if an address type was send for generate the form
					if ( !empty($_GET['type']) ) {
						$address_type = wpshop_tools::varSanitizer( $_GET['type'] );
					}
					else {
						$billing_option = get_option('wpshop_billing_address');
						$address_type = $billing_option['choice'];
					}
					$http_referer = ( !empty($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : '';
					$referer = ( !empty($_POST['referer']) ) ? $_POST['referer'] :  $http_referer;
					echo $wpshop_account->display_form_fields( $address_type, '', '', $referer );
				}
			}

			// --------------------------
			// Infos commande
			// --------------------------
			elseif ($_GET['action']=='order' && !empty($_GET['oid']) && is_numeric($_GET['oid'])) {
				$order_info = get_post_meta($_GET['oid'], '_order_postmeta', true);

				if(!empty($order_info) && $order_info['customer_id']==$user_id) {

					// Display the order's address infos
					$order_info = get_post_meta($_GET['oid'], '_order_info', true);

 					foreach ( $order_info as $key=>$address ) {
						if( !empty($address['address']) ) {
							echo '<div class="half">';
							echo '<h2>'.($key =='billing' ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop')).'</h2>';
							echo '<ul>';

							foreach ( $address['address'] as $attribute_code => $attribute_def) {
								$info = $attribute_def;
								if ($attribute_code == 'civility') {
									$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=' . $attribute_def . '', '');
									$info = $wpdb->get_var($query);
								}

								if( !empty($info) ) {
									echo wpshop_display::display_template_element('customer_address_display', array('CUSTOMER_ADDRESS_ELEMENT' => $info, 'CUSTOMER_ADDRESS_ELEMENT_KEY' => $attribute_code));
								}
							}

						echo '</ul>';
						echo '</div>';
						}
					}

					$shipping_option = get_option('wpshop_shipping_address_choice');

					// Donn�es commande
					$order = get_post_meta($_GET['oid'], '_order_postmeta', true);
					$currency = wpshop_tools::wpshop_get_currency();

					if(!empty($order)) {
						echo '<h2>'.__('Order details','wpshop').'</h2>';
						echo '<div class="order"><div>';
						echo __('Order number','wpshop').' : <strong>'.$order['order_key'].'</strong><br />';
						echo __('Date','wpshop').' : <strong>'.$order['order_date'].'</strong><br />';
						echo __('Total','wpshop').' : <strong>'.number_format($order['order_total_ttc'], 2, '.', '').' '.$currency.'</strong><br />';

						$sub_tpl_component = array();
						$sub_tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_INVOICE_REF'] = !empty($order['order_invoice_ref']) ? $order['order_invoice_ref'] : '';
						$sub_tpl_component['ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES'] = '';
						$sub_tpl_component['ADMIN_ORDER_INVOICE_DOWNLOAD_LINK'] = WPSHOP_TEMPLATES_URL . 'invoice.php?order_id=' . $_GET['oid'] . ( empty($order['order_invoice_ref']) ? '&invoice_ref=' . $order['order_invoice_ref'] : '');
						$order_invoice_download = !empty($order['order_invoice_ref']) ? wpshop_display::display_template_element('wpshop_admin_order_payment_received_invoice_download_links', $sub_tpl_component, array(), 'admin') : '';

						echo __('Status','wpshop').' : <strong><span class="status '.$order['order_status'].'">'.$order_status[$order['order_status']].'</span></strong> ' . $order_invoice_download . '<br />';

						$payment_list = wpshop_payment::display_payment_list($_GET['oid'], $order, false );
						if ( !empty($payment_list[0]) ) {
							echo __('Received payments', 'wpshop') . '<ul class="wpshop_order_received_payment_list" >
									' . $payment_list[0] . '
								</ul>';
							$waited_amount_sum = $payment_list[1];
							$received_amount_sum = $payment_list[2];
						}



						if (!empty($shipping_option['activate']) && $shipping_option['activate']) {
							echo __('Tracking number','wpshop').' : '.(empty($order['order_trackingNumber'])?__('none','wpshop'):'<strong>'.$order['order_trackingNumber'].'</strong>');
						}
						echo '<br /><br /><strong>'.__('Order content','wpshop').'</strong><br />';
						if(!empty($order['order_items'])){

							// Codes de t�l�chargement
							if(in_array($order['order_status'], array('completed', 'shipped'))) {
								$download_codes = get_user_meta($user_id, '_order_download_codes_'.$_GET['oid'], true);
							}

							foreach($order['order_items'] as $o) {
								// If the order is >= completed, so give the download link to the user
								if(!empty($download_codes[$o['item_id']])) {
									$link = '<a href="'.WPSHOP_URL.'/download_file.php?oid='.$_GET['oid'].'&amp;download='.$download_codes[$o['item_id']]['download_code'].'">'.__('Download','wpshop').'</a>';
								} else $link='';

								/**	Get attribute order for current product	*/
								$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($o['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
								$output_order = array();
								if ( count($product_attribute_order_detail) > 0 ) {
									foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
										foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
											if ( !empty($attribute_def->code) )
												$output_order[$attribute_def->code] = $position;
										}
									}
								}
								$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $o['item_meta'], $output_order, 'invoice_print', 'common');
								ksort($variation_attribute_ordered['attribute_list']);
								$product_details = '';
								foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
									$product_details .= $attribute_variation_to_output;
								}
								$product_details = !empty($product_details) ? '<ul>' . $product_details . '</ul>' : '';

								echo '<span class="right">'.number_format($o['item_total_ttc'], 2, '.', '').' '.$currency.'</span>'.$o['item_qty'].' x '.$o['item_name'].' '.$link.'<br />'.$product_details;
							}
							echo '<hr />';
							echo '<span class="right">'.number_format($order['order_total_ht'], 2, '.', '').' '.$currency.'</span>'.__('Total ET','wpshop').'<br />';
							echo '<span class="right">'.number_format(array_sum($order['order_tva']), 2, '.', '').' '.$currency.'</span>'.__('Taxes','wpshop').'<br />';


							if (!empty($shipping_option['activate']) && $shipping_option['activate']) {
								echo '<span class="right">'.(empty($order['order_shipping_cost'])?'<strong>'.__('Free','wpshop').'</strong>':number_format($order['order_shipping_cost'], 2, '.', '').' '.$currency).'</span>'.__('Shipping fee','wpshop').'<br />';
							}

							if(!empty($order['order_grand_total_before_discount']) && $order['order_grand_total_before_discount'] != $order['order_grand_total']){
								echo '
									'.__('Total ATI before discount','wpshop').'<span class="total_ttc right">'.number_format($order['order_grand_total_before_discount'],2).' '.$currency.'</span>
									<br />'.__('Discount','wpshop').'<span class="total_ttc right">- '.number_format($order['order_discount_amount_total_cart'],2).' '.$currency.'</span><br />
								';
							}

							echo '<span class="right"><strong>'.number_format($order['order_grand_total'], 2, '.', '').' '.$currency.'</strong></span>'.__('Total ATI','wpshop');
						}
						else{
							echo __('No product for this order', 'wpshop');
						}
						echo '</div></div>';

						/* If the payment is completed */
						if(in_array($order['order_status'], array('completed', 'shipped'))) {
							echo '<a href="' . get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=order&oid='.$_GET['oid'].'&download_invoice='.$_GET['oid'].'">'.__('Download the invoice','wpshop').'</a>';
						}
						else {
							//$available_payement_method = wpshop_payment::display_payment_methods_choice_form($_GET['oid']);
							//echo '<h2>'.__('Complete the order','wpshop').'</h2>' . $available_payement_method[0];
							//echo wpshop_display::display_template_element('wpshop_checkout_page_validation_button', array('CHECKOUT_PAGE_VALIDATION_BUTTON_TEXT' =>  __('Order', 'wpshop')));
						}
					}
					else echo __('No order', 'wpshop');
			    }
				else echo __('You don\'t have the right to access this order.', 'wpshop');
			}

			echo $output;
		}
		// --------------------------
		// DASHBOARD
		// --------------------------
		else {
			// Display the address infos Dashboard
			echo $wpshop_account->display_addresses_dashboard();

			echo '<h2>'.__('Your orders','wpshop').'</h2>';

			$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'" AND post_status = "publish" ORDER BY post_date DESC', '');
			$orders_id = $wpdb->get_results($query);

			if ( !empty($orders_id) ) {
				$order = array();
				foreach ($orders_id as $o) {

					$order_id = $o->ID;
					$o = get_post_meta($order_id, '_order_postmeta', true);
					if ( !empty($o) && !empty($o['order_currency'])) {
						$currency = wpshop_tools::wpshop_get_sigle($o['order_currency']);
					}
					if ( !empty($o['order_items']) && !empty($o['customer_id']) && ( $user_id == $o['customer_id'] ) ) {
						echo '<div class="order"><div>';
						echo __('Order number','wpshop').' : <strong>'.$o['order_key'].'</strong><br />';
						echo __('Date','wpshop').' : <strong>'.$o['order_date'].'</strong><br />';
						echo __('Total ATI','wpshop').' : <strong>'.number_format($o['order_grand_total'], 2, '.', '').' '.$currency.'</strong><br />';
						echo __('Status','wpshop').' : <strong><span class="status '.$o['order_status'].'">'.$order_status[$o['order_status']].'</span></strong><br />';
						echo '<a href="'.get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&amp;') . 'action=order&amp;oid='.$order_id.'" title="'.__('More info about this order...', 'wpshop').'">'.__('More info about this order...', 'wpshop').'</a>';
						echo '</div></div>';
					}
				}
			}
			else echo __('No order', 'wpshop');
		}
	}
}

/** Class wpshop_account */
class wpshop_account {

	var $login_fields = array();
	var $personal_info_fields = array();
	var $partial_personal_infos_fields = array();
	var $billing_fields = array();
	var $shipping_fields = array();

	/** Constructor of the class */
	function __construct() {
		global $wpdb;
		/** Check the account attribute set ID **/
		$query =  $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = %s AND post_name = %s', WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
		$customer_entity_post_id = $wpdb->get_var($query);
		
		$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $customer_entity_post_id);
		$customer_entity_id = $wpdb->get_var( $query );
		$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
		
		
		$user = wp_get_current_user();
		$current_item_edited = isset($user->ID) ? $user->ID : null;
		$address = array();
		
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
		if(!empty($productAttributeSetDetails)){
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if( !empty($_POST['submitOrderInfos']) && !empty($_POST['attribute'][$attribute->data_type][$attribute->code]) ) {
								$value = $_POST['attribute'][$attribute->data_type][$attribute->code];
							}
							else {
								$value = '';
								if ( $attribute->code != 'user_pass') {
									$code = $attribute->code;
									$value = $user->$code;
								}
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$this->personal_info_fields[$attribute->code] = $attribute_output_def;
							
							if ( !empty( $attribute_output_def['is_used_in_quick_add_form'] ) && $attribute_output_def['is_used_in_quick_add_form'] == 'yes') {
								$this->partial_personal_infos_fields[$attribute->code] = $attribute_output_def;
							}
						}
					}
				}
			}
		}
		
		add_action('wp_logout', array('wpshop_account', 'wpshop_logout'));
	}

	/** Traite les donnees reçus en POST
	 * @return void
	*/
	function managePost() {
		global $wpshop, $wpshop_account;
		$shipping_address_option = get_option('wpshop_shipping_address_choice');
		if( isset($_POST['submitbillingAndShippingInfo'])) {
			if (isset($_POST['shiptobilling']) && $_POST['shiptobilling'] == "on") {
				$wpshop_account->same_billing_and_shipping_address( $_POST['billing_address'], $_POST['shipping_address']);
			}
			foreach ( $_POST['attribute'] as $id_group => $attribute_group ) {
				$group = wpshop_address::get_addresss_form_fields_by_type ($id_group);
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$id_group], 'address_edition');
					}
				}
			}
			if( $validate ) {
				if ( !empty($_POST['billing_address']) ) {
					$wpshop_account->treat_forms_infos( $_POST['billing_address'] );
				}
				if( !empty($_POST['shipping_address']) && !empty($shipping_address_option['activate']) ) {
					$wpshop_account->treat_forms_infos( $_POST['shipping_address'] );
				}

			 	if(!empty($_GET['return']) && $_GET['return']=='checkout') {
			 		wpshop_tools::wpshop_safe_redirect($_POST['referer']);
			 	}
			 	else {
			 		wpshop_tools::wpshop_safe_redirect( $_POST['referer'] );
			 	}
			}
		}
		elseif(!empty($_GET['download_invoice'])) {
			$pdf = new wpshop_export_pdf();
			$pdf->invoice_export($_GET['download_invoice']);
		}
		// Test the infos if the account form was posted
		if ( isset($_POST['submitAccountInfo']) ) {
			if ( $wpshop->validateForm($this->personal_info_fields) ) {
				self::save_account_form( get_current_user_id() );
				wpshop_tools::wpshop_safe_redirect(get_permalink(get_option('wpshop_myaccount_page_id')));
			}
		}

		// if there is errors
		if($wpshop->error_count()>0) {
			echo $wpshop->show_messages();
			return false;
		}
		else {
			return true;
		}
	}

	/** Display the login form
	 * @return void
	*/
	function display_login_form() {
		$tpl_component = array();
		$output = wpshop_display::display_template_element('wpshop_login_form', $tpl_component);
		return $output;
	}

	function display_account_information( $user_id = 0) {
		$account_display = '';
		global $wpdb;

		$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_author = %d AND post_type = %s", $user_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
		$wpshop_customer_id = $wpdb->get_var($query);

		$attributes_set = wpshop_attributes_set::getElement('yes', "'valid'", 'is_default', '', wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS));
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
		if(!empty($productAttributeSetDetails)){
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						$user = get_userdata($user_id);
						if ( array_key_exists($attribute->code, $user->data) ) {
							$key = $attribute->code;
							$value = $user->data->$key;
						}
						else {
							$value = get_user_meta($user_id, $attribute->code, true);
						}
						$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
						if ( ($attribute_output_def['type'] != 'password') ) {
							/**	Get value from good place in case of country type input	*/
							if ( $attribute_output_def['frontend_verification'] == 'country' ) {
								$cournty_list = unserialize( WPSHOP_COUNTRY_LIST );
								$value = $cournty_list[$attribute_output_def['value']];
							}
							/**	Get value from good place in case of list element	*/
							else if ( in_array($attribute_output_def['type'], array('select', 'multiple-select', 'radio', 'checkbox')) ) {
								$value = wpshop_attributes::get_attribute_type_select_option_info($attribute_output_def['value'], 'value', $attribute_output_def['data_type_to_use']);
							}

							$input_tpl_component = array();
							$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = '';
							$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $attribute_output_def['label'];
							$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = '';
							$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = $value;
							$account_display .= wpshop_display::display_template_element('wpshop_account_form_input', $input_tpl_component);
							unset($input_tpl_component);
						}
					}
				}
			}
		}

		return $account_display;
	}

	/** Display the account form
	 * @return void
	 */
	function display_account_form( $first = '', $form_type = 'complete' ) {
		global $wpdb;
		$tpl_component = array();
		$tpl_component['ACCOUNT_FORM_FIELD'] = '';
		
		$infos_fields = ( !empty($form_type) && $form_type == 'complete') ? $this->personal_info_fields : $this->partial_personal_infos_fields;
		foreach ($infos_fields as $key => $field) {
				$template = 'wpshop_account_form_input';
				if ( $field['type'] == 'hidden' ) {
					$template = 'wpshop_account_form_hidden_input';
				}

				if ( $field['frontend_verification'] == 'country' ) {
					$field['type'] = 'select';
					$field['possible_value'] = array_merge(array('' => __('Choose a country')), unserialize(WPSHOP_COUNTRY_LIST));
					$field['valueToPut'] = 'index';
				}

				$attributeInputDomain = 'attribute[' . $field['data_type'] . ']';
				$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
				$input_tpl_component = array();
				$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
				$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $field['label'] . ($field['required'] == 'yes' ? ' <span class="required">*</span>' : '');
				$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
				$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . (( $field['data_type'] == 'datetime' ) ? $field['options'] : '');
				
				if ( $form_type == 'partial' && $field['is_used_in_quick_add_form'] == 'yes') {
					$tpl_component['ACCOUNT_FORM_FIELD'] .= wpshop_display::display_template_element($template, $input_tpl_component);
				}
				
				elseif ( $form_type == 'complete' )  {
					$tpl_component['ACCOUNT_FORM_FIELD'] .= wpshop_display::display_template_element($template, $input_tpl_component);
				}
				unset($input_tpl_component);


				if ( $form_type == 'complete' && $field['_need_verification'] == 'yes') {
					$field['name'] = $field['name'] . '2';
					$field['id'] = $field['id'] . '2';
					$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
					$input_tpl_component = array();
					$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf(__('Confirm %s', 'wpshop'), strtolower($field['label'])) . ($field['required'] == 'yes' ? ' <span class="required">*</span>' : '');
					$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . (( $field['data_type'] == 'datetime' ) ? $field['options'] : '');
					$tpl_component['ACCOUNT_FORM_FIELD'] .= wpshop_display::display_template_element($template, $input_tpl_component);
					unset($input_tpl_component);
				}

				$wpshop_billing_address = get_option('wpshop_billing_address');
				if ( $form_type == 'complete' && !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') && !empty($wpshop_billing_address['integrate_into_register_form_after_field']) && ($wpshop_billing_address['integrate_into_register_form_after_field'] == $key ) ) {
					$current_connected_user = null;
					if ( get_current_user_id() > 0 ) {
						$query = $wpdb->prepare ("SELECT *
							FROM " . $wpdb->posts . "
							WHERE post_type = '" .WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS. "'
							AND post_parent = %d
							ORDER BY ID
							LIMIT 1", get_current_user_id() );
						$current_connected_user = $wpdb->get_var($query);
					}
					$tpl_component['ACCOUNT_FORM_FIELD'] .= $this->display_form_fields( $wpshop_billing_address['choice'], $current_connected_user, 'first', '', array(), array('title' => false, 'address_title' => false, 'field_to_hide' => $wpshop_billing_address['integrate_into_register_form_matching_field']) );
					/** Integrate the shipping form */
					$shipping_address_option = get_option('wpshop_shipping_address_choice');

					if ( $shipping_address_option['activate'] ) {
						$tpl_component['ACCOUNT_FORM_FIELD'] .= '<p class="formField"><label><input type="checkbox" name="shiptobilling" checked="checked" /> '.__('Use as shipping information','wpshop').'</label></p>';
						$display = 'display:none;';
						$tpl_component['ACCOUNT_FORM_FIELD'] .= '<div id="shipping_infos_bloc" style="'.$display.'">';
						$tpl_component['ACCOUNT_FORM_FIELD'] .= self::display_form_fields($shipping_address_option['choice'], '', 'first');
						$tpl_component['ACCOUNT_FORM_FIELD'] .= '</div><br/>';
					}

				}
		}
		$tpl_component['PERSONAL_INFORMATIONS_FORM_TITLE'] = ( get_current_user_id() != 0 ) ? __('Personal information', 'wpshop') : __('Create your account', 'wpshop');
		$tpl_component['COMMERCIAL_NEWSLETTER_FORM'] = self::display_commercial_newsletter_form();

		$output = wpshop_display::display_template_element('wpshop_account_form', $tpl_component);
		$output .= '<input type="hidden" name="account_form_type" value="' .$form_type. '" />';
		return $output;
	}

	/** Display the commercial & newsletter form
	 * @return void
	 */
	function display_commercial_newsletter_form() {
		$output = '';

		$user_preferences = get_user_meta( get_current_user_id(), 'user_preferences', true );
		$tpl_component = array();
		$tpl_component['CUSTOMER_PREF_NEWSLETTER_SITE'] = ((!empty($user_preferences['newsletters_site']) && $user_preferences['newsletters_site']==1 OR !empty($_POST['newsletters_site'])) ? ' checked="checked"' : null);
		$tpl_component['CUSTOMER_PREF_NEWSLETTER_SITE_PARTNERS'] = ((!empty($user_preferences['newsletters_site_partners']) && $user_preferences['newsletters_site_partners']==1 OR !empty($_POST['newsletters_site_partners'])) ? ' checked="checked"' : null);

		return wpshop_display::display_template_element('wpshop_customer_preference_for_newsletter', $tpl_component);
	}

	/**
	 * Display the address Dashboard
	 *
	 * @return string The complete html output will links to edit customer account and addresses list
	 */
	function display_addresses_dashboard() {
		global $wpdb;
		$tpl_component = array();
		$tpl_component['ACCOUNT_LINK_ADDRESS_DASHBOARD'] = get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=editinfo_account';
		$tpl_component['LOGOUT_LINK_ADDRESS_DASHBOARD'] = wp_logout_url( get_permalink(get_option('wpshop_product_page_id')) );
		$address_dashboard = wpshop_display::display_template_element('link_head_addresses_dashboard', $tpl_component);
		unset($tpl_component);

		/**	Display billing addresses	*/
		$billing_option = get_option('wpshop_billing_address');
		$address_dashboard .= self::get_addresses_by_type( $billing_option['choice'], __('Billing address', 'wpshop') );

		/**	Display shipping addresses if activated into admin part	*/
		$shipping_address_option = get_option('wpshop_shipping_address_choice');
		if ( !empty($shipping_address_option['activate']) && ($shipping_address_option['activate'] == 'on')) {
			$shipping_partner_option = get_option('wpshop_shipping_partner_choice');
			if ( !empty($shipping_partner_option) && !empty($shipping_partner_option['activate']) && $shipping_partner_option['activate'] == 'on') {
			ob_start();
			do_shortcode('[wpshop_shipping_partners]');
			$address_dashboard_ = ob_get_contents();
			ob_end_clean();
			$address_dashboard .= $address_dashboard_;
			}
			else {
				$address_dashboard .= self::get_addresses_by_type( $shipping_address_option['choice'], __('Shipping address', 'wpshop') );
			}
		}
		/**	Add a last element for having a clear interface	*/
		$address_dashboard .= '<div class="wpshop_clear" ></div>';
		do_action( 'wpshop_account_custom_hook');
		return $address_dashboard;
	}



	/**
	 * Get all addresses for current customer for display
	 *
	 * @param integer $address_type_id The current identifier of address type -> attribute_set_id
	 * @param string $address_type A string allowing to display
	 *
	 * @return string The complete html output for customer addresses
	 */
	function get_addresses_by_type( $address_type_id, $address_type_title, $args = array() ) {
		global $wpdb;
		/**	Get current customer addresses list	*/
  		if ( is_admin() ) {
  			$post = get_post( $_GET['post']);
  			if ( !empty($post->post_parent) ) {
  				$customer_id = $post->post_parent;
  			}
  			else {
  				$customer_id = $post->post_author;
  			}
 		}
  		else {
			$customer_id = get_current_user_id();
 		}

		$query = $wpdb->prepare("
				SELECT ADDRESSES.ID
				FROM " . $wpdb->posts . " AS ADDRESSES
					INNER JOIN " . $wpdb->postmeta . " AS ADDRESSES_META ON (ADDRESSES_META.post_id = ADDRESSES.ID)
				WHERE ADDRESSES.post_type = %s
					AND ADDRESSES.post_parent = %d
				AND ADDRESSES_META.meta_key = %s
				AND ADDRESSES_META.meta_value = %d",
				WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $customer_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', $address_type_id);
		$addresses = $wpdb->get_results($query);
		$addresses_list = '';

		/**	Initialize	*/
		$tpl_component = array();
		$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = $address_type_title;
		$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
		$tpl_component['ADDRESS_BUTTONS'] = '';
		if( count($addresses) > 0 ) {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id;
		}
		else {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id .'&first';
		}
		$tpl_component['ADDRESS_TYPE'] = ( !empty($address_type_title) && ($address_type_title == __('Shipping address', 'wpshop'))) ? 'shipping_address' : 'billing_address';
		$tpl_component['ADD_NEW_ADDRESS_TITLE'] = sprintf(__('Add a new %s', 'wpshop'), $address_type_title);


		/**	Read customer list	*/
		if( count($addresses) > 0 ) {
			/**	Get the fields for addresses	*/
			$address_fields = wpshop_address::get_addresss_form_fields_by_type($address_type_id);
			$first = true;
			$tpl_component['ADDRESS_COMBOBOX_OPTION'] = '';
			$nb_of_addresses = 0;
			foreach ( $addresses as $address ) {
				// Display the addresses
				/** If there isn't address in SESSION we display the first address of list by default */
				if ( empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $first && !is_admin()) {
					$address_id = $address->ID;
					$_SESSION[$tpl_component['ADDRESS_TYPE']] = $address->ID;
				}
				else {
					$address_id = $_SESSION[$tpl_component['ADDRESS_TYPE']];
				}
				$address_selected_infos = get_post_meta($address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				$address_infos = get_post_meta($address->ID, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				
				if ( !empty($address_infos) ) {
					$tpl_component['ADDRESS_ID'] = $address->ID;
					/** If no address was selected, we select the first of the list **/
					$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = self::display_an_address($address_fields, $address_selected_infos, $address_id);
					$tpl_component['ADDRESS_BUTTONS'] .= wpshop_display::display_template_element('addresses_box_actions_button_edit', $tpl_component);
					$tpl_component['choosen_address_LINK_EDIT'] = get_permalink(get_option('wpshop_myaccount_page_id')) . (strpos(get_permalink(get_option('wpshop_myaccount_page_id')), '?')===false ? '?' : '&') . 'action=editAddress&amp;id='.$address->ID;
					$tpl_component['DEFAULT_ADDRESS_ID'] = $address_id;
					$tpl_component['ADRESS_CONTAINER_CLASS'] = ' wpshop_customer_adress_container_' . $address->ID;
					$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
					$tpl_component['ADDRESS_COMBOBOX_OPTION'] .= '<option value="' .$address->ID. '" ' .( ( !empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $_SESSION[$tpl_component['ADDRESS_TYPE']] == $address->ID) ? 'selected="selected"' : null). '>' . (!empty($address_infos['address_title']) ? $address_infos['address_title'] : $address_type_title) . '</option>';
					$nb_of_addresses++;
				}
				else {
					$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = '<span style="color:red;">'.__('No data','wpshop').'</span>';
				}
				$first = false;
			}
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			if ( !is_admin() ) {
				$tpl_component['ADDRESS_COMBOBOX'] = (!empty($tpl_component['ADDRESS_COMBOBOX_OPTION']) && ($nb_of_addresses > 1)) ? wpshop_display::display_template_element('addresses_type_combobox', $tpl_component) : '';
			}
		}
		else {
			$tpl_component['ADDRESS_ID'] = 0;
			$tpl_component['DEFAULT_ADDRESS_ID'] = 0;
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = sprintf( __('You don\'t have any %s, %splease create a new one%s', 'wpshop'), strtolower($address_type_title), '<a href="' . $tpl_component['ADD_NEW_ADDRESS_LINK'] . '" >', '</a>' );
		}

		$tpl_component['ADDRESS_BUTTONS'] .= wpshop_display::display_template_element('addresses_box_actions_button_new_address', $tpl_component);
		if ( !empty($args['only_display']) && ($args['only_display'] == 'yes') ) {
			$tpl_component['ADDRESS_BUTTONS'] = '';
		}

		$addresses_list .= wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);

		return $addresses_list;
	}

	/**
	 * Build and return output for an address
	 *
	 * @param array $address_fields The list of defined fields for the address type
	 * @param array $address_infos Informations about current address being edited
	 * @param integer $address_id The current address
	 *
	 * @return string The html output for the address
	 */
	function display_an_address ( $address_fields, $address_infos, $address_id = '' ) {
		global $wpdb;
		$address_list = '';
		if ( !empty($address_fields) && !empty($address_infos) ){
			foreach( $address_fields as $id_group => $group_fields) {
				foreach ($group_fields as $key => $fields) {
					foreach ( $fields['content'] as $attribute_code => $attribute_def) {
						$info = !empty($address_infos[$attribute_def['name']]) ? $address_infos[$attribute_def['name']] : '';
						if (($attribute_def['name'] == 'civility')) {
							if ( !empty($address_infos[$attribute_def['name']]) ) {
								$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=' . $address_infos[$attribute_def['name']] . '', '');
								$info = $wpdb->get_var($query);
							}
						}
						if ($attribute_def['name'] == 'country') {
							if ( !empty($info) ) {
								foreach (unserialize(WPSHOP_COUNTRY_LIST) as $key=>$value) {
									if ( $info == $key) {
										$info = $value;
									}
								}
							}
						}
						if ( !empty($info) ) {
							$address_list .= wpshop_display::display_template_element('display_address_line', array('CUSTOMER_ADDRESS_ELEMENT' => $info, 'CUSTOMER_ADDRESS_ELEMENT_KEY' => $attribute_def['name']));
						}
					}
				}
			}
		}
		
		return $address_list;
	}
	


	/**
	 * Display the differents forms fields
	 * @param string $type : Type of address
	 * @param string $first : Customer first address ?
	 * @param string $referer : Referer website page
	 * @param string $admin : Display this form in admin panel
	 */
	function display_form_fields($type, $id = '', $first = '', $referer = '', $special_values = array(), $options = array(), $display_for_admin = array() ) {
		global $wpshop, $wpshop_form, $wpdb;
		$choosen_address = get_option('wpshop_billing_address');
		$output_form_fields = '';

		if ( empty($type) ) {
			$type = $choosen_address['choice'];
		}
		$result = wpshop_address::get_addresss_form_fields_by_type($type, $id);
		if ( !empty($display_for_admin ) ) {
			foreach( $result[$type] as $k=>$group ) {
				foreach( $group as $key => $address['content']) {
					if ( is_array($result[$type][$k][$key]) ) {
						foreach($address['content']as $elem => $address_element) {
							if ( array_key_exists($address_element['name'], $display_for_admin) ) {
								if ( array_key_exists($result[$type][$k][$key][$elem]['name'], $display_for_admin) ) {
									$result[$type][$k][$key][$elem]['value'] = $display_for_admin[$result[$type][$k][$key][$elem]['name']];
								}
							}
						}
					}
				}
			}
		}
		
		$form = $result[$type];
		// Take the post id to make the link with the post meta of  address
		$values = array();
		// take the address informations
		$current_item_edited = !empty($id) ? (int)wpshop_tools::varSanitizer($id) : null;

		foreach ( $form as $group_id => $group_fields) {
			if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields .= '<h2>'.$group_fields['name'].'</h2>';
			foreach ( $group_fields['content'] as $key => $field) {
				if ( empty($options['field_to_hide']) || !is_array($options['field_to_hide']) || !in_array( $key, $options['field_to_hide'] ) ) {
					$attributeInputDomain = 'attribute[' . $type . '][' . $field['data_type'] . ']';
					// Test if there is POST var or if user have already fill his address infos and fill the fields with these infos
					if( !empty($_POST) ) {
						$referer = !empty($_POST['referer']) ? $_POST['referer'] : '';
						if ( !empty($form['id']) && !empty($field['name']) && isset($_POST[$form['id']."_".$field['name']]) ) {
							$value = $_POST[$form['id']."_".$field['name']];
						}
					}



 					// Fill Automaticly some fields when it's an address creation
					if ( !is_admin() && !empty($_GET['action']) && $_GET['action'] == 'add_address' ) {

						switch ( $field['name']) {
							case 'address_title' :
								$field['value'] = ( $type == $choosen_address['choice'] ) ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop');
							break;
							case 'address_last_name' :
								$usermeta_last_name = get_user_meta( get_current_user_id(), 'last_name', true);
								$field['value'] = ( !empty($usermeta_last_name) ) ? $usermeta_last_name :  '';
							break;
							case 'address_first_name' :
								$usermeta_first_name = get_user_meta( get_current_user_id(), 'first_name', true);
								$field['value'] = ( !empty($usermeta_first_name) ) ? $usermeta_first_name :  '';
							break;
							case 'address_user_email' :
								$user_infos = get_userdata( get_current_user_id() );
								$field['value'] = ( !empty($user_infos) && !empty($user_infos->user_email) ) ? $user_infos->user_email :  '';
							break;
							default :
								$field['value'] = '';
							break;
						}

					}

					if (empty($referer)) {
						$referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
					}
					if( $field['name'] == 'address_title' && !empty($first) && $type == __('Billing address', 'wpshop') ) {
						$value = __('Billing address', 'wpshop');
					}
					elseif( $field['name'] == 'address_title' && !empty($first) && $type == __('Shipping address', 'wpshop') ) {
						$value = __('Shipping address', 'wpshop');
					}

					if ( !empty($special_values[$field['name']]) ) {
						$field['value'] = $special_values[$field['name']];
					}

					$template = 'wpshop_account_form_input';
					if ( $field['type'] == 'hidden' ) {
						$template = 'wpshop_account_form_hidden_input';
					}

					if ( $field['frontend_verification'] == 'country' ) {
						$field['type'] = 'select';
						$field['possible_value'] = array_merge(array('' => __('Choose a country')), unserialize(WPSHOP_COUNTRY_LIST));
						$field['valueToPut'] = 'index';
					}

					$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
					$input_tpl_component = array();
					$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = $field['label'] . ( ( ($field['required'] == 'yes' && !is_admin()) || ($field['name'] == 'address_user_email' && is_admin()) ) ? ' <span class="required">*</span>' : '');
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
					$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain);
					$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
					unset($input_tpl_component);

					if ( $field['_need_verification'] == 'yes' && !is_admin() ) {
						$field['name'] = $field['name'] . '2';
						$field['id'] = $field['id'] . '2';
						$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
						$input_tpl_component = array();
						$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf( __('Confirm %s', 'wpshop'), strtolower($field['label']) ). ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . $field['options'];
						$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
						unset($input_tpl_component);
					}
				}
			}
		}

		if ( $type ==  $choosen_address['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="billing_address" value="'.$choosen_address['choice'].'" />';
		}
		$shipping_address_options = get_option('wpshop_shipping_address_choice');
		if ( $type ==  $shipping_address_options['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="shipping_address" value="' .$shipping_address_options['choice']. '" />';
		}
		$output_form_fields .= '<input type="hidden" name="edit_other_thing" value="'.false.'" /><input type="hidden" name="referer" value="'.$referer.'" />
								<input type="hidden" name="type_of_form" value="' .$type. '" /><input type="hidden" name="attribute[' .$type. '][item_id]" value="' .$current_item_edited. '" />';

		if ( !is_admin() && empty($first) ) $output_form_fields = wpshop_display::display_template_element('wpshop_customer_addresses_form', array('CUSTOMER_ADDRESSES_FORM_CONTENT' => $output_form_fields, 'CUSTOMER_ADDRESSES_FORM_BUTTONS' => '<input type="submit" name="submitbillingAndShippingInfo" value="' . __('Save','wpshop') . '" />'));
		return $output_form_fields;
	}

	/** Fill the shipping informations with the billing informations if user check there are same addresses
	 * @param int $billing_address_id
	 * @param int $shipping_address_id
	 * @return void
	 */
	function same_billing_and_shipping_address ($billing_address_id, $shipping_address_id) {
		if ( !empty($_POST) ) {
			$tableauGeneral =  $_POST;
		}
		else {
			$tableauGeneral = $_REQUEST;
		}


		// Create an array with the shipping address fields definition
		$shipping_fields = array();
		foreach ($tableauGeneral['attribute'][$shipping_address_id] as $key=>$attribute_group ) {
			if ( is_array($attribute_group) ) {
				foreach( $attribute_group as $field_name=>$value ) {
					$shipping_fields[] =  $field_name;
				}
			}
		}
		// Test if the billing address field exist in shipping form
		foreach ($tableauGeneral['attribute'][$billing_address_id] as $key=>$attribute_group ) {
			if (is_array($attribute_group) ) {
				foreach( $attribute_group as $field_name=>$value ) {
					if ( in_array($field_name, $shipping_fields) ) {
						if ($field_name == 'address_title') {
							$tableauGeneral['attribute'][$shipping_address_id][$key][$field_name] = __('Shipping address', 'wpshop');
						}
						else {
							$tableauGeneral['attribute'][$shipping_address_id][$key][$field_name] = $tableauGeneral['attribute'][$billing_address_id][$key][$field_name];
						}
					}
				}
			}
		}

		foreach ( $tableauGeneral as $key=>$value ) {
			if ( !empty($_POST) ) {
				$_POST[$key] = $value;
			}
			else {
				$_REQUEST[$key] = $value;
			}
		}

	}

	/** Treat the differents fields of form and classified them by form
	 * @return boolean
	 */
	function treat_forms_infos( $attribute_set_id ) {
		global $wpdb;
		$current_item_edited = !empty($_POST['attribute'][$attribute_set_id]['item_id']) ? (int)wpshop_tools::varSanitizer($_POST['attribute'][$attribute_set_id]['item_id']) : null;
		// Create or update the post address
		$post_parent = '';
		$post_author = get_current_user_id();
		if ( !empty($_REQUEST['user']['customer_id']) ) {
			$post_parent = $_REQUEST['user']['customer_id'];
			$post_author = $_REQUEST['user']['customer_id'];
		}
		elseif ( !empty($_REQUEST['post_ID']) ) {
			$post_parent = $_REQUEST['post_ID'];
		}
		else {
			$post_parent = get_current_user_id();
		}
		$post_address = array(
			'post_author' => $post_author,
			'post_title' => !empty($_POST['attribute'][$attribute_set_id]['varchar']['address_title']) ? $_POST['attribute'][$attribute_set_id]['varchar']['address_title'] : '',
			'post_status' => 'draft',
			'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
			'post_parent'=>	$post_parent
		);
		$_POST['edit_other_thing'] = true;

		if ( empty($current_item_edited) && (empty($_POST['current_attribute_set_id']) || $_POST['current_attribute_set_id'] != $attribute_set_id )) {
			$current_item_edited = wp_insert_post( $post_address );
			if ( is_admin()) {
				$_POST['attribute'][$attribute_set_id]['item_id'] = $current_item_edited;
			}
		}
		else {
			$post_address['ID'] = $current_item_edited;
			wp_update_post( $post_address );
		}

		//Update the post_meta of address
		update_post_meta($current_item_edited, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, $attribute_set_id);

		foreach ( $_POST['attribute'][ $attribute_set_id ] as $type => $type_content) {
			$attribute_not_to_do = array();
			if (is_array($type_content) ) {
				foreach ( $type_content as $code => $value) {
					$attribute_def = wpshop_attributes::getElement($code, "'valid'", 'code');
					if ( !empty($attribute_def->_need_verification) && $attribute_def->_need_verification == 'yes' ) {
						$code_verif = $code.'2';
						$attribute_not_to_do[] = $code_verif;
						if ( !empty($attributes[$code_verif] )) {
							unset($attributes[$code_verif]);
						}
					}
					if( !in_array($code, $attribute_not_to_do)) $attributes[$code] = $value;
				}
			}
		}

		//GPS coord
		$address = (!empty($attributes) ) ? $attributes['address']. ' ' .$attributes['postcode']. ' ' .$attributes['city'] : '';
		$gps_coord = wpshop_address::get_coord_from_address($address);
		$attributes['longitude'] = ( !empty($gps_coord['longitude']) ) ? $gps_coord['longitude'] : '';
		$attributes['latitude'] = ( !empty($gps_coord['latitude']) ) ? $gps_coord['latitude'] : '';

		$result = wpshop_attributes::setAttributesValuesForItem($current_item_edited, $attributes, false, '');
		$result['current_id'] = $current_item_edited;
		return $result;
	}


	/** Save the account informations
	 * @return void
	 */
	function save_account_form($user_id = null, $form_type='complete') {
		global $wpdb, $wpshop, $wpshop_account;

		$account_creation = false;
		if ( empty($user_id) ) {
			/** Create customer account */
			$reg_errors = new WP_Error();
			do_action('register_post', $_POST['attribute']['varchar']['user_email'], $_POST['attribute']['varchar']['user_email'], $reg_errors);
			$errors = apply_filters('registration_errors', $reg_errors, $_POST['attribute']['varchar']['user_email'], $_POST['attribute']['varchar']['user_email']);

			// if there are no errors, let's create the user account
			if ( !$reg_errors->get_error_code() ) {
				$account_creation = true;

				$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
				$user_pass = !empty($_POST['attribute']['varchar']['user_pass']) ? $_POST['attribute']['varchar']['user_pass'] : wp_generate_password( 12, false );
				$user_id = wp_create_user($user_name, $user_pass, $_POST['attribute']['varchar']['user_email']);
				if ( !is_int($user_id) ) {
					$wpshop->add_error(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'wpshop'), get_option('admin_email')));
					return false;
				}
				// Change role
				wp_update_user( array( 'ID' => $user_id, 'role' => 'customer' ) );


			}
			else {
				$wpshop->add_error($reg_errors->get_error_message());
				return false;
			}
		}

		if ( $user_id > 0 ) {
			$user_database_fields = wpshop_database::get_field_list($wpdb->users);
			foreach ( $user_database_fields as $user_database_field ) :
				$fields[] = $user_database_field->Field;
			endforeach;

			foreach ($this->personal_info_fields as $key => $field) :
				$this->posted[$key] = isset($_POST['attribute'][$field['data_type']][$key]) ? wpshop_tools::wpshop_clean($_POST['attribute'][$field['data_type']][$key]) : null;
				if ( !in_array($key, $fields) ) {
					update_user_meta( $user_id, $key, $this->posted[$key]  );
				}
				else {
					wp_update_user( array('ID' => $user_id, $key => $this->posted[$key]) );
				}
			endforeach;

			$_REQUEST['user']['customer_id'] = $user_id;

			if ( $form_type == 'complete' ) {
				$wpshop_billing_address = get_option('wpshop_billing_address');
				if ( !empty($wpshop_billing_address['integrate_into_register_form']) && ($wpshop_billing_address['integrate_into_register_form'] == 'yes') ) {
					self::treat_forms_infos( $wpshop_billing_address['choice'] );
					$shipping_address_option = get_option('wpshop_shipping_address_choice');
					if ( !empty($shipping_address_option) && $shipping_address_option['activate'] ) {
						if (isset($_POST['shiptobilling']) && $_POST['shiptobilling'] == "on") {
							self::same_billing_and_shipping_address($_POST['billing_address'], $_POST['shipping_address']);
							self::treat_forms_infos( $_POST['shipping_address'] );
						}
						else {
							self::treat_forms_infos( $_POST['shipping_address'] );
						}

					}
				}
			}

			if ($account_creation) {
				// Set the WP login cookie
				$secure_cookie = is_ssl() ? true : false;
				wp_set_auth_cookie($user_id, true, $secure_cookie);

				// Envoi du mail d'inscription
				wpshop_messages::wpshop_prepared_email($_POST['attribute']['varchar']['user_email'], 'WPSHOP_SIGNUP_MESSAGE', array(
					'customer_first_name' => ( !empty($_POST['attribute']['varchar']['first_name']) ) ? $_POST['attribute']['varchar']['first_name'] : '',
					'customer_last_name' => ( !empty($_POST['attribute']['varchar']['last_name']) ) ? $_POST['attribute']['varchar']['last_name'] : ''
				));
			}
			$user_preferences = array(
					'newsletters_site' => !empty($_POST['newsletters_site']) && $_POST['newsletters_site']=='on',
					'newsletters_site_partners' => !empty($_POST['newsletters_site_partners']) && $_POST['newsletters_site_partners']=='on'
			);
			update_user_meta($user_id, 'user_preferences', $user_preferences);
		}

		return array(true, $user_id, $form_type);
	}



	/** Return true if the login info is ok and not if not
	 * @return boolean
	*/
	function isRegistered($email_or_username, $password, $login=false) {

		global $wpshop;

		if(!empty($email_or_username)) {
			$user_data = get_user_by('email', $email_or_username);
			// Test connexion par identifiant et par email
			if(user_pass_ok($email_or_username, $password) OR user_pass_ok($user_data->user_login, $password)) {
				if($login) {
					$user_data = empty($user_data) ? get_user_by('login', $email_or_username) : $user_data;
					$user_id = $user_data->ID;
					$secure_cookie = is_ssl() ? true : false;
					// On connecte l'utilisateur
					wp_set_auth_cookie($user_id, true, $secure_cookie);
				}
				return true;
			} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		} else $wpshop->add_error(__('Incorrect login infos', 'wpshop'));
		return false;
	}

	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function display_customer_address($address_type = 'billing', $address_infos){
		global $civility;
		$user_address_output = '';

		$user_address_output .=  '<div class="half"><span >'.__(ucfirst(strtolower($address_type)),'wpshop').'</span><br /><br />';
		$user_address_output .=  (!empty($address_infos['civility']) ? __($civility[$address_infos['civility']], 'wpshop') : null).' <strong>'.$address_infos['first_name'].' '.$address_infos['last_name'].'</strong>';
		$user_address_output .=  empty($address_infos['company'])?'<br />':'<br/><i>' . __('Company', 'wpshop') . '</i>: '.$address_infos['company'].'<br />';
		$user_address_output .=  '<i>' . __('Email address', 'wpshop') . '</i>: '.(!empty($address_infos['email']) ? $address_infos['email'] : ' - ').'<br />';
		$user_address_output .=  '<i>' . __('Phone', 'wpshop') . '</i>: '.(!empty($address_infos['phone']) ? $address_infos['phone'] : ' - ').'<br />';
		$user_address_output .=  $address_infos['address'].'<br />';
		$user_address_output .=  $address_infos['postcode'].' '.$address_infos['city'].', '.$address_infos['country'];
		$user_address_output .=  '</div>';

		return $user_address_output;
	}

	/**
	*	Return output for customer adress
	*
	*	@param array $address_type The customer address stored into an array
	*
	*	@return string $user_address_output The html output for the customer address
	*/
	function edit_customer_address($address_type = 'Billing', $address_infos, $customer_id, $order_state = ''){
		global $civility, $customer_adress_information_field;
		$user_address_output = '';

		$user_info = null;
		if ( !empty( $customer_id ) ) {
			$user_info = get_userdata($customer_id);

			if(empty($address_infos['first_name'])){
				if(!empty($user_info->user_firstname)){
					$address_infos['first_name'] = $user_info->user_firstname;
				}else{
					$address_infos['first_name'] = $user_info->user_login;
				}
			}
			if(empty($address_infos['last_name'])){
				if(!empty($user_info->user_lastname)){
					$address_infos['last_name'] = $user_info->user_lastname;
				}
			}
			if(empty($address_infos['email'])){
				if(!empty($user_info->user_email )){
					$address_infos['email'] = $user_info->user_email ;
				}
			}
		}

		// R�cup�ration de la liste des champs concernant l'adresse des utilisateurs
		foreach ( $customer_adress_information_field as $input_identifier => $input_label ) {

			switch ( $input_identifier ) {
				case 'civility':
					$user_address_output .= '';
					if ( in_array( $order_state, array('awaiting_payment', '') ) ) {
						$user_address_output .= '
<p class="wpshop_customer_adress_edition_input_container wpshop_customer_adress_edition_input_container_' . $input_identifier . '">
	<label class="wpshop_customer_adress_edition_input_label" for="wpshop_customer_adress_edition_input_' . strtolower($address_type) . '_' . $input_identifier . '">' . __( $input_label, 'wpshop') . '</label>
	<select name="user[' . strtolower($address_type) . '_info][' . $input_identifier . ']" id="wpshop_customer_adress_edition_input_' . strtolower($address_type) . '_' . $input_identifier . '" class="wpshop_customer_adress_edition_input wpshop_customer_adress_edition_input_' . $input_identifier . '" >
		<option value="">' . __('Choose...', 'wpshop') . '</option>';
						if ( !empty( $civility ) ) {
							foreach ( $civility as $key => $civil ) {
								$selected = (!empty($address_infos['civility']) && ($address_infos['civility'] == $key) ? ' selected="selected" ' : '');
								$user_address_output .= '<option value="' . $key . '"' . $selected . '>' . __($civil, 'wpshop') . '</option>';
							}
						}
						$user_address_output .= '
	</select>
</p>';
					}
					else {
						$input_value = '';
						if ( !empty( $address_infos[$input_identifier] ) )
							$input_value = __($civility[$address_infos['civility']], 'wpshop');

						ob_start();
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input_read_only.tpl.php');
						$user_address_output .= ob_get_contents();
						ob_end_clean();
					}
				break;
				default:
					$input_options = '';
					$input_value = '';
					if ( !empty( $address_infos[$input_identifier] ) )
						$input_value = $address_infos[$input_identifier];

					ob_start();
					if ( in_array( $order_state, array('awaiting_payment', '') ) ) {
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input.tpl.php');
					}
					else {
						include(WPSHOP_TEMPLATES_DIR.'admin/customer_adress_input_read_only.tpl.php');
					}
					$user_address_output .= ob_get_contents();
					ob_end_clean();
				break;
			}

		}

		return $user_address_output;
	}
	
	/** Delete all WPSHOP's SESSION Vars */
	function wpshop_logout () {
		unset($_SESSION['cart']);
		unset($_SESSION['shipping_address']);
		unset($_SESSION['billing_address']);
		unset($_SESSION['order_id']);
		unset($_SESSION['coupon']);
	}

}

?>