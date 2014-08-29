<?php
class wps_account_dashboard_ctr {
	function __construct() {
		add_shortcode( 'wps_account_dashboard', array( $this, 'display_account_dashboard') );
		add_shortcode( 'wps_messages', array( 'wpshop_messages', 'get_histo_messages_per_customer' ) );
		add_shortcode( 'wps_account_last_actions_resume', array($this, 'display_account_last_actions' ) );
	}
	
	function import_data( $part ) {
		$output = '';
		
		switch( $part ) {
			case 'account' : 
				$output  = '<div id="wps_account_informations_container">';
				$output .= do_shortcode('[wps_account_informations]');
				$output .= '</div>';
				$output .= do_shortcode( '[wps_orders_in_customer_account]');
			break;
			case 'address' : 
				$output .= do_shortcode( '[wps_addresses]' );
			break;
			case 'order' : 
				$output = do_shortcode( '[wps_orders_in_customer_account]' );
			break;
			case  'opinion' : 
				$output = do_shortcode( '[wps_opinion]' );
			break;
			case 'wishlist' : 
				$output = '<div class="wps-alert-info">' .__( 'This functionnality will be available soon', 'wpshop'). '</div>';
			break;
			case 'coupon' : 
				$output = do_shortcode( '[wps_coupon]' );
			break;
			case 'messages' : 
				$output = do_shortcode( '[wps_message_histo]' );
			break;
			default : 
				$output = do_shortcode('[wps_account_informations]');
			break;
			
		}
		if( get_current_user_id() == 0 ) {
			$output = do_shortcode( '[wpshop_login]' );
		}
		return $output;
	}
	
	function display_account_dashboard() {
		/** Support old checkout tunnel **/
		if( !empty($_GET['action']) ) {
			global $wpdb, $wpshop, $wpshop_account, $civility;
			$wpshop_account->managePost();
			if ($_GET['action'] == 'editAddress') {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
				
					$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->posts. ' WHERE ID = ' .$_GET['id']. ' AND post_parent = ' .get_current_user_id(). '', '');
					$post = $wpdb->get_row($query);
					if ( !empty($post) && !empty($post->ID) )
						$attribute_set_id = get_post_meta($post->ID, '_wpshop_address_attribute_set_id', true);
				
					if ( !empty($post)) {
						echo $wpshop_account->display_form_fields($attribute_set_id, $_GET['id']);
					}
					else {
						wpshop_tools::wpshop_safe_redirect( $_SERVER['HTTP_REFERER'] );
					}
				}
			}
			elseif ($_GET['action']=='editinfo_account' ) {
				$output = wpshop_display::display_template_element('wpshop_customer_account_infos_form', array('CUSTOMER_ACCOUNT_INFOS_FORM' => $wpshop_account->display_account_form('', 'complete'), 'CUSTOMER_ACCOUNT_INFOS_FORM_BUTTONS' => '<input type="submit" name="submitOrderInfos" value="' . __('Save my account informations','wpshop') . '" />', 'CUSTOMER_ACCOUNT_INFOS_FORM_NONCE' => wp_create_nonce('wpshop_customer_register')));
				echo $output;
			}
			elseif ($_GET['action'] == 'editinfo') {
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
				echo $output;
			}
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
					$tpl_component['ADDRESS_TYPE_CHOICE_FORM_ACTION'] = get_permalink(wpshop_tools::get_page_id( get_option('wpshop_myaccount_page_id') ) ) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id')) ), '?')===false ? '?' : '&') . 'action=add_address';
					$tpl_component['ADDRESS_TYPE_LISTING_INPUT'] = wpshop_form::check_input_type($input_def);
					$output = wpshop_display::display_template_element('wpshop_customer_new_addresse_type_choice_form', $tpl_component);
					unset($tpl_component);
					echo $output;
				}
				else {
					wpshop_tools::wpshop_safe_redirect( get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id( get_option('wpshop_myaccount_page_id')) ), '?')===false ? '?' : '&') . 'action=add_address' );
				}
			}
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
					
					echo $output;
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
		}
		else {
			$part = ( !empty($_GET['account_dashboard_part']) ) ? sanitize_title( $_GET['account_dashboard_part'] ) : 'account';
			$content = $this->import_data( $part );
			ob_start();
			$wps_account_ctr = new wps_account_ctr();
			require_once( $wps_account_ctr->get_template_part( "frontend", "account/account-dashboard") );
			$output = ob_get_contents();
			ob_end_clean();	
			echo $output;
		}
	}
	
	
	function display_account_last_actions() {
		global $wpdb;
		$output = '';
		$wps_account_ctr = new wps_account_ctr();
		$user_id = get_current_user_id();
		if( !empty($user_id) ) {
			$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $user_id );
			$orders = $wpdb->get_results( $query );
			if( !empty($orders) ) {
				$orders_list = '';
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					$order_number = ( !empty($order_meta) && !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : '';
					$order_date = ( !empty($order_meta) && !empty($order_meta['order_date']) ) ? mysql2date( get_option('date_format'), $order_meta['order_date'], true ) : '';
					$order_amount = ( !empty($order_meta) && !empty($order_meta['order_key']) ) ? wpshop_tools::formate_number( $order_meta['order_grand_total'] ).' '.wpshop_tools::wpshop_get_currency( false ) : '';
					$order_available_status = unserialize( WPSHOP_ORDER_STATUS );
					$order_status = ( !empty($order_meta) && !empty($order_meta['order_status']) ) ? __( $order_available_status[ $order_meta['order_status'] ], 'wpshop' ) : '';
					ob_start();
					require( $wps_account_ctr->get_template_part( "frontend", "account/account-dashboard-resume-element") );
					$orders_list .= ob_get_contents();
					ob_end_clean();
				}
					
				ob_start();
				require_once( $wps_account_ctr->get_template_part( "frontend", "account/account-dashboard-resume") );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
	}
}