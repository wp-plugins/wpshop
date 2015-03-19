<?php
/**
 * Manage Customer administration functions
 * @author ALLEGRE Jérôme - EOXIA
 */
class wps_customer_admin {
	function __construct() {
		// Template loading
		$this->template_dir = WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/";
		// WP General actions
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes') );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts') );
		// Ajax Actions
		add_action( 'wp_ajax_wps_order_refresh_customer_informations', array( $this, 'wps_order_refresh_customer_informations') );
		add_action( 'wp_ajax_wps_load_customer_creation_form_in_admin', array( $this, 'wps_load_customer_creation_form_in_admin') );
		add_action( 'wp_ajax_wps_order_refresh_customer_list', array( $this, 'wps_order_refresh_customer_list') );
	}

	/**
	 * Add meta Boxes
	 */
	function add_meta_boxes() {
		/**	Box with order customer information	*/
		add_meta_box('wpshop_order_customer_information_box', '<span class="dashicons dashicons-businessman"></span> '.__('Customer information', 'wpshop'),array($this, 'display_order_customer_informations_in_administration'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'high');
	}

	/**
	 * Add Scripts
	 */
	function add_scripts() {
		wp_enqueue_script( 'wps_customer_admin_js', WPS_ACCOUNT_URL . '/' . WPS_ACCOUNT_DIR . '/assets/backend/js/wps_customer_backend.js', '', WPSHOP_VERSION );
	}

	/**
	 * Display the Customer informations in order back-office panel
	 */
	function display_order_customer_informations_in_administration() {
		global $post_id;

		// Customer List
		$wps_customer = new wps_customer_ctr();
	
		// Check if post is an order
		if( !empty($post_id) && get_post_type( $post_id ) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {
			// Order informations
			$order_metadata = get_post_meta( $post_id, '_order_postmeta', true );
			$order_infos = get_post_meta( $post_id, '_order_info', true );
						
			if( !empty($order_metadata['customer_id']) ) {
				$customer_lists = $wps_customer->custom_user_list( array( 'name'=>'user[customer_id]', 'id' => 'user_customer_id' ), ( ( !empty($order_metadata['customer_id']) ) ? $order_metadata['customer_id'] : '' ) );
				// Selected customer informations
				$wps_account = new wps_account_ctr();
				$customer_id = ( !empty($order_metadata['customer_id']) ) ? $order_metadata['customer_id'] : '';
				$customer_datas = $wps_account->display_account_informations($customer_id);

				// Selected customer address informations
				// Billing datas
				$billing_address_option = get_option( 'wpshop_billing_address' );
				$billing_address_option = ( !empty($billing_address_option) && !empty($billing_address_option['choice']) ) ? $billing_address_option['choice'] : '';

				// Shipping datas
				$shipping_address_content = '';
				$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
				
				if( ( !empty($order_metadata) && !empty($order_metadata['order_status']) && $order_metadata['order_status'] == 'awaiting_payment' ) || empty($order_metadata) || empty($order_metadata['order_status']) ) {
					$wps_address = new wps_address();
					$addresses = $wps_address->display_addresses_interface( $customer_id, true );	
				}
				else {
					$wps_address_admin = new wps_address_admin();
					$addresses = $wps_address_admin->display_customer_address_in_order( $customer_id, $post_id, $billing_address_option );
					if( !empty($shipping_address_option['activate']) ) {
						$addresses .= $wps_address_admin->display_customer_address_in_order( $customer_id, $post_id, $shipping_address_option['choice'] );
					}
				}
			}
			else {
				$customer_lists = $wps_customer->custom_user_list( array( 'name'=>'user[customer_id]', 'id' => 'user_customer_id' ));
			}
		}
		else {
			$customer_lists = $wps_customer->custom_user_list( array( 'name'=>'user[customer_id]', 'id' => 'user_customer_id' ) );
		}
		require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir, "backend", "customer-informations/wps_order_customer_informations") );
	}

	/**
	 * AJAX - Customer creation form
	 */
	function wps_load_customer_creation_form_in_admin() {
		echo do_shortcode( '[wps_signup display="admin"]' );
		wp_die();
	}
	
	/**
	 * AJAX - Refresh customer informations
	 */
	function wps_order_refresh_customer_informations() {
		$status = false; $account = $addresses = '';
		$customer_id = ( !empty($_POST['customer_id']) ) ? intval($_POST['customer_id']) : null;
		$order_id = ( !empty($_POST['order_id']) ) ? intval($_POST['order_id']) : null;
		if( !empty($customer_id) ) {
			$order_metadata = get_post_meta( $order_id, '_order_postmeta', true );
			$order_infos = get_post_meta( $order_id, '_order_info', true );

			// Selected customer informations
			$wps_account = new wps_account_ctr();
			$account = $wps_account->display_account_informations($customer_id);
			
			$wps_address = new wps_address();
			$addresses = $wps_address->display_addresses_interface( $customer_id, true, $order_id );
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'account' => $account, 'addresses' => $addresses ) );
		wp_die();
	}

	/**
	 * AJAX - Reload Customer list
	 */
	function wps_order_refresh_customer_list() {
		$status = false; $response = '';
		$customer_id = ( !empty($_POST['customer_id']) ) ? intval( $_POST['customer_id'] ) : null; 
		if( !empty($customer_id) ) {
			$wps_customer = new wps_customer_ctr();
			$response = $wps_customer->custom_user_list( array( 'name'=>'user[customer_id]', 'id' => 'user_customer_id' ), $customer_id );
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		wp_die();
	}
}