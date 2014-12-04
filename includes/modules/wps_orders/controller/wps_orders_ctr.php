<?php
class wps_orders_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_ORDERS_DIR;

		function __construct() {
			/** Template Load **/
			$this->template_dir = WPS_ORDERS_PATH . WPS_ORDERS_DIR . "/templates/";
			add_thickbox();
			/** Template Load **/
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_shortcode( 'order_customer_informations', array( &$this, 'display_order_customer_informations' ) );

			add_shortcode( 'wps_orders_in_customer_account', array($this, 'display_orders_in_account') );

			wp_enqueue_script('jquery');
			if ( is_admin() ) {
				wp_enqueue_script( 'wps_orders', WPS_ORDERS_URL.WPS_ORDERS_DIR.'/templates/backend/js/wps_orders.js' );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'wps_orders_scripts') );

			/** Ajax Actions **/
			add_action( 'wp_ajax_wps_add_product_to_quotation', array( &$this, 'wps_add_product_to_quotation') );
			add_action( 'wp_ajax_wps_change_product_list', array( &$this, 'wps_change_product_list') );

			add_action( 'wp_ajax_wps_orders_load_variations_container', array( &$this, 'wps_orders_load_variations_container') );
			add_action( 'wp_ajax_wps_order_refresh_in_admin', array( &$this, 'wps_order_refresh_in_admin') );

			add_action( 'wp_ajax_wps_orders_load_details', array( $this, 'wps_orders_load_details') );

			// Add a product sale historic in administration product panel
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes') );
			add_action( 'wp_ajax_wps_order_choose_customer', array( $this, 'wps_order_choose_customer' ) );
			
			
			add_thickbox();
		}

		function wps_orders_scripts() {
			wp_enqueue_script( 'wps_orders_frontend', WPS_ORDERS_URL.WPS_ORDERS_DIR.'/templates/js/wps-orders.js' );
		}

		/**
		 * Add Meta Boxes
		 */
		function add_meta_boxes() {
			/** Box  Order Payments **/
			add_meta_box('wpshop_order_payment',__('Order payment', 'wpshop'),array($this, 'display_order_payments_box'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low');
			/**	Box for customer order comment */
			add_meta_box('wpshop_order_customer_comment',__('Order customer comment', 'wpshop'),array( $this, 'order_customer_comment_box'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low');
			/** Historic sales **/
			add_meta_box('wpshop_product_order_historic', __('Sales informations', 'wpshop'), array( $this, 'meta_box_product_sale_informations'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'low');
			/**	Box with order customer information	*/
			add_meta_box('wpshop_order_customer_information_box',__('Customer information', 'wpshop'),array($this, 'display_order_customer_informations_in_administration'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low');
			/**	Box with the complete order content	*/
			add_meta_box('wpshop_product_list', __('Product List', 'wpshop'),array($this, 'wps_products_listing_for_quotation'),WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low');
			
		}
		
		
		function custom_template_load( $templates ) {
			include(WPS_ORDERS_BASE.'/templates/backend/main_elements.tpl.php');
			$wpshop_display = new wpshop_display();
			$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
			return $templates;
		}


		function display_order_customer_informations() {
			global $post_id; global $wpdb;
			$output = '';
			if ( !empty($post_id) ) {
				$order_postmeta = get_post_meta( $post_id, '_order_postmeta', true );
				$order_info = get_post_meta( $post_id, '_order_info', true );

				/** Check the order status **/
				if ( !empty($order_postmeta) ) {
					if ( !empty($order_postmeta['order_status'])  && $order_postmeta['order_status'] != 'awaiting_payment' ) {
						$output = wps_address::display_an_address( $order_info['billing']['address'] );
						$output .= wps_address::display_an_address( $order_info['shipping']['address'] );
					}
					else {
						$output = wps_address::display_an_address( $order_info['billing']['address'] );
					}
				}
			}
			else {
				/** Display  "Choose customer or create one" Interface **/
				$tpl_component = array();
				$args = array(
							'show_option_all'         => __('Choose a customer', 'wpshop'),
							'orderby'                 => 'display_name',
							'order'                   => 'ASC',
							'include'                 => null, // string
							'exclude'                 => null, // string
							'multi'                   => false,
							'show'                    => 'display_name',
							'echo'                    => false,
							'selected'                => false,
							'include_selected'        => false,
							'name'                    => 'user', // string
							'id'                      => null, // integer
							'class'                   => 'chosen_select', // string
							'blog_id'                 => $GLOBALS['blog_id'],
							'who'                     => null // string
						);
				$tpl_component['CUSTOMERS_LIST'] = wp_dropdown_users( $args );
				$output = wpshop_display::display_template_element('wps_orders_choose_customer_interface', $tpl_component, array(), 'admin');
			}
			return $output;
		}

		/** Letters Interface for products Quotation **/
		function get_letters_for_product_listing() {
			$letter_interface = '';
			$alphabet = array( __('ALL', 'wpshop' ), 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			foreach( $alphabet as $a ) {
				$tpl_component['LETTER'] = $a;
				$letter_interface .= wpshop_display::display_template_element('wps_orders_letter', $tpl_component, array(), 'admin');
				unset( $tpl_component );
			}
			return $letter_interface;
		}

		/**
		 * Display an lsiting of products to make quotation in backend
		 */
		function wps_products_listing_for_quotation() {
			global $post;
			$output = '';

			if ( !empty($post->ID) ) {
				$order_meta = get_post_meta( $post->ID, '_order_postmeta', true);

				if ( empty($order_meta) || (!empty($order_meta) && ( $order_meta['order_status'] == 'awaiting_payment' || $order_meta['order_status'] == 'partially_paid' )) ) {
					$output .= self::wps_generate_products_list_table_by_letter();
					$output .= self::get_letters_for_product_listing();
				}
				else {
					$output .= __('You can\'t add products to an order with this status', 'wpshop');
				}
			}
			else {
				$output .= self::wps_generate_products_list_table_by_letter();
				$output .= self::get_letters_for_product_listing();
			}

			echo $output;
		}

		/**
		 * Display an interface with letter to make a quick search in product listing
		 * @param string $letter
		 * @return string <string, string>
		 */
		function wps_generate_products_list_table_by_letter( $letter = 'A' ) {
			global $wpdb;
			if ( $letter ==  __('ALL', 'wpshop' ) ) {
				$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s  ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
			}
			else {
				$query = $wpdb->prepare( 'SELECT ID, post_title FROM ' .$wpdb->posts. ' WHERE post_status = %s AND post_type = %s AND post_title LIKE %s ORDER BY post_title ASC', 'publish', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $letter.'%');
			}
			$products = $wpdb->get_results( $query );

			if ( !empty($products) ) {
				$list = '';
				foreach( $products as $product ) {
					 $product_metadata = get_post_meta( $product->ID, '_wpshop_product_metadata', true );
					 $tpl_component = array();
					 $tpl_component['PRODUCT_ID'] = $product->ID;
					 $tpl_component['PRODUCT_PICTURE'] = get_the_post_thumbnail( $product->ID, 'thumbnail' );
					 $tpl_component['PRODUCT_REFERENCE'] = ( !empty( $product_metadata) && $product_metadata['product_reference']) ? $product_metadata['product_reference'] : '';
					 $tpl_component['PRODUCT_NAME'] = $product->post_title;
					 $product = wpshop_products::get_product_data($product->ID);
					 $tpl_component['PRODUCT_PRICE'] = wpshop_prices::get_product_price($product, 'price_display', array('mini_output', 'grid') );
					 $tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
					 $list .= wpshop_display::display_template_element('wps_orders_products_list_for_quotation_table_line', $tpl_component, array(), 'admin');
					 unset( $tpl_component );
				}
				$tpl_component['PRODUCTS_LIST'] = $list;
			}
			else {
				$tpl_component['PRODUCTS_LIST'] = sprintf(__('There is no products for the letter : %s', 'wpshop'), $letter);
			}
			$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
			$output = wpshop_display::display_template_element('wps_orders_products_list_for_quotation', $tpl_component, array(), 'admin');
			return $output;
		}


		/** Add product to quotation **/
		function wps_add_product_to_quotation() {
			$status = false; $result = '';
			$have_variations = false;

			$product_id = ( !empty($_POST['product_id']) ) ? wpshop_tools::varSanitizer( $_POST['product_id'] ) : null;
			$order_id = ( !empty($_POST['order_id']) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ) : null;
			$qty = ( !empty($_POST['qty']) && is_numeric( $_POST['qty']) ) ? wpshop_tools::varSanitizer( $_POST['qty'] ) : 1;

			if ( !empty($order_id)  && !empty($product_id) ) {
				/** Check if it's a product with variations **/
				$variations = wpshop_products::get_variation( $product_id );
				if ( !empty( $variations) ) {
					$have_variations = true;
				}
				else {
					$order_meta = get_post_meta($order_id, '_order_postmeta', true);

					$order_items = array();
					$order_items[$product_id]['product_id'] = $product_id;
					$order_items[$product_id]['product_qty'] = $qty;

					if( isset($order_meta['order_items']) && is_array($order_meta['order_items']) ) {
						foreach($order_meta['order_items'] as $product_in_order) {
							if(!isset($order_items[$product_in_order['item_id']])){
								$order_items[$product_in_order['item_id']]['product_id'] = $product_in_order['item_id'];
								$order_items[$product_in_order['item_id']]['product_qty'] = $product_in_order['item_qty'];
							}
							else{
								$order_items[$product_in_order['item_id']]['product_qty'] += $product_in_order['item_qty'];
							}
						}
					}

					$order_meta = wpshop_cart::calcul_cart_information($order_items);

					/*	Update order content	*/
					update_post_meta($order_id, '_order_postmeta', $order_meta);
					$result =''; //wpshop_orders::order_content( get_post($order_id) );

					$status = true;
				}

			}
			$response = array( 'status' => $status, 'response' => $result, 'product_with_variations' => $have_variations );
			echo json_encode( $response );
			die();
		}


		/** Reload the product List **/
		function wps_change_product_list() {
			$status = false; $result = '';
			$letter = ( !empty($_POST['letter']) ) ? wpshop_tools::varSanitizer($_POST['letter']) : '';
			if ( !empty($letter) ) {
				$result = self::wps_generate_products_list_table_by_letter( $letter );
				$status = true;
			}
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}

		function wps_orders_load_variations_container() {
			$product_id = $_GET['pid'];
			$order_id = $_GET['oid'];
			$quantity = $_GET['qty'];

			$response  = '<h2>'.__('Choose variations', 'wpshop').'</h2>';
			$response .= '<div class="wps_shipping_mode_configuration_part">';
			$response .= wpshop_products::wpshop_variation( $product_id, true, $order_id, $quantity );
			$response .= '</div>';
			$response .= '<center><input type="button" class="button-primary" value="'. __('Add product to order','wpshop').'" id="wps_order_product_with_variation" /></center> <img src="' .WPSHOP_LOADING_ICON. '" id="wps_orders_add_to_cart_variation_loader" alt="' .__('Loading', 'wpshop'). '" class="wpshopHide" />';
			echo $response;
			die();
		}

		function wps_order_refresh_in_admin() {
			$result = ''; $status = false;
			$order_id = ( !empty($_POST['order_id']) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ) : null;
			$product_to_delete = !empty( $_POST['product_to_delete']) ? wpshop_tools::varSanitizer( $_POST['product_to_delete'] ) : '';
			$product_to_update_qty = !empty( $_POST['product_to_update_qty']) ? $_POST['product_to_update_qty'] : array();
			$order_shipping_cost = !empty($_POST['order_shipping_cost']) ? wpshop_tools::varSanitizer( $_POST['order_shipping_cost'] ) : '';
			if ( !empty($order_id) ) {
				/** get post meta infos **/
				$order = get_post_meta( $order_id, '_order_postmeta', true );
				if ( !empty($order) ) {
					/** Check delete product **/
					if ( !empty($product_to_delete) && !empty($order['order_items']) && !empty($order['order_items'][$product_to_delete])  ) {
						unset( $order['order_items'][$product_to_delete] );
					}

					/** Check Update Qty **/
					if ( !empty($product_to_update_qty) && is_array($product_to_update_qty) ) {
						foreach( $product_to_update_qty as $product ) {
							$d = explode( '_x_', $product );
							if ( !empty($d[0]) && !empty($order['order_items'][ $d[0] ]) ){
								$order['order_items'][ $d[0] ]['item_qty'] = $d[1];
							}
						}
					}

					/** Update Shipping cost infos **/
					if ( !empty($order_shipping_cost) ) {
						$order['order_shipping_cost'] = $order_shipping_cost;
					}
					self::calcul_cart_informations( $order, $order_id );
					$result = wpshop_orders::order_content( get_post($order_id) );
					$status = true;

				}


			}
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}

		/**
		 * Recalculate the cart informations
		 */
		function calcul_cart_informations( $order, $order_id = '' ) {
			$price_piloting = get_option( 'wpshop_shop_price_piloting' );
			if ( !empty( $order) ) {
				$total_ht = $total_ttc = 0;
				$order_tva = array();
				if ( !empty($order['order_items']) ) {
					foreach( $order['order_items'] as $k=>$item ) {
						/** Product Price **/
						$order['order_items'][$k]['item_total_ht'] = $item['item_pu_ht'] * $item['item_qty'];
						$order['order_items'][$k]['item_total_ttc'] = $order['order_items'][$k]['item_total_ht'] * ( 1 + ( $item['item_tva_rate'] / 100 ) );
						$order['order_items'][$k]['item_tva_amount'] = $order['order_items'][$k]['item_total_ht'] * ( $item['item_tva_rate'] / 100 );

						$total_ht += $order['order_items'][$k]['item_total_ht'];
						$total_ttc += $order['order_items'][$k]['item_total_ttc'];
						/** TVA **/
						if ( empty( $order_tva[ $item['item_tva_rate'] ] ) ) {
							$order_tva[ $item['item_tva_rate'] ] = $order['order_items'][$k]['item_tva_amount'];
						}
						else {
							$order_tva[ $item['item_tva_rate'] ] += $order['order_items'][$k]['item_tva_amount'];
						}
					}
				}
				/** Order Shipping cost **/
				if ( !empty($price_piloting) && $price_piloting == 'HT' ) {
					$order_tva[ 'VAT_shipping_cost' ] = $order['order_shipping_cost'] * ( WPSHOP_VAT_ON_SHIPPING_COST / 100 );
				}

				/** TVA Amount **/
				$total_tva = 0;
				foreach( $order_tva as $tva ) {
					$total_tva += $tva;
				}
				$order['order_tva'] = $order_tva;

				/** Total amounts **/
				$order['order_total_ht'] = $total_ht;
				$order['order_total_ttc'] = $total_ttc;

				$order['order_grand_total_before_discount'] = $total_ht + $total_tva + $order['order_shipping_cost'];
				$order['order_grand_total'] = $order['order_grand_total_before_discount'];
				$order['order_amount_to_pay_now'] = $order['order_grand_total'];

				/** Save datas **/
				if ( !empty($order_id) ) {
					update_post_meta( $order_id, '_order_postmeta', $order );
				}
				else {
					/** Store cart in session **/
					$_SESSION['cart'] = $order;
				}

			}
		}

		/**
		 * Display orders in customer account
		 * @param integer $customer_id
		 * @return string
		 */	
		function display_orders_in_account( $customer_id = '' ) {
			$output = '';
			$customer_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
			$from_admin = ( !empty($customer_id) ) ? true : false;
			$wps_orders_mdl = new wps_orders_mdl();
			$orders = $wps_orders_mdl->get_customer_orders( $customer_id );

			// Display orders
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "frontend", "orders_list_in_account") );
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		/**
		 * Display Customer comments on order in administration panel
		 * @param object $order
		 */
		function order_customer_comment_box( $order ) {
			global $wpdb;
			$output = '';
			if ( !empty($order) && !empty($order->ID) ) {
				$query = $wpdb->prepare('SELECT post_excerpt FROM ' .$wpdb->posts. ' WHERE ID = %d', $order->ID);
				$comment = $wpdb->get_var( $query );
				require_once( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "customer_comment_on_order_box") );
			}
		}
		
		
		/**
		 * AJAX - Load order details in customer account
		 */
		function wps_orders_load_details() {
			$order_id = ( !empty($_POST['order_id']) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ) : '';
			$user_id = get_current_user_id();
			$status = false; $result = '';
			if( !empty($order_id) ) {
				$order = get_post( $order_id );
				$order_infos = get_post_meta( $order_id, '_order_postmeta', true );
				$order_key = ( !empty($order_infos['order_key']) ) ? $order_infos['order_key'] : '-';
				if( !empty($order) && !empty($user_id) && $order->post_type == WPSHOP_NEWTYPE_IDENTIFIER_ORDER && $order->post_author == $user_id ) {
					$result = do_shortcode( '[wps_cart cart_type="summary" oid="' .$order_id. '"]' );
					$status = true;
				}
			}
			echo json_encode( array( 'status' => $status, 'title' => sprintf( __( 'Order n° %s details', 'wpshop' ), $order_key ), 'content' => $result ) );
			wp_die();
		}
		
		/**
		 * Display an order historic of product in administration product panel
		 */
		function meta_box_product_sale_informations () {
			global $post;
			$product_id = $post->ID;
			$variations = wpshop_products::get_variation( $product_id );
			$order_status = unserialize( WPSHOP_ORDER_STATUS );
			$color_label = array( 'awaiting_payment' => 'jaune', 'canceled' => 'rouge', 'partially_paid' => 'orange', 'incorrect_amount' => 'orange', 'denied' => 'rouge', 'shipped' => 'bleu', 'payment_refused' => 'rouge', 'completed' => 'vert', 'refunded' => 'rouge');
			// Get datas
			$sales_informations = array();
			/** Query **/
			$data_to_compare = '"item_id";s:' .strlen($product_id). ':"' .$product_id. '";';
			$query_args = array( 'posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'meta_query' => array( array('key' => '_order_postmeta', 'value' => $data_to_compare, 'compare' => 'LIKE') ) );
			$orders = new WP_Query( $query_args );
			if ( !empty($orders) && !empty($orders->posts) ) {
				foreach( $orders->posts as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					$order_info = get_post_meta( $order->ID, '_order_info', true );
					$sales_informations[] = array(
							'order_key' => ( !empty($order_meta) && !empty($order_meta['order_key']) ) ? $order_meta['order_key'] : '',
							'order_date' => ( !empty($order_meta) && !empty($order_meta['order_date']) ) ? $order_meta['order_date'] : '',
							'customer_firstname' => ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['address_first_name']) ) ? $order_info['billing']['address']['address_first_name'] : '',
							'customer_name' => ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['address_last_name']) ) ? $order_info['billing']['address']['address_last_name'] : '',
							'customer_email' => ( !empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['address_user_email']) ) ? $order_info['billing']['address']['address_user_email'] : '',
							'order_id' => $order->ID,
							'order_status' => $order_meta['order_status']
					);
				}
			}
			// Display results
			require_once( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "product_order_historic") );
		}
		
		/**
		 * Choose customer in order administration panel
		 */
		function display_order_customer_informations_in_administration() {
			global $post_id;
			$output = '';
			// Check if post is an order
			if( !empty($post_id) && get_post_type( $post_id ) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {
				$order_metadata = get_post_meta( $post_id, '_order_postmeta', true );
				$order_infos = get_post_meta( $post_id, '_order_info', true );

				// Customer informations data
				$wps_account = new wps_account_ctr();
				$customer_id = ( !empty($order_metadata['customer_id']) ) ? $order_metadata['customer_id'] : '';
				$customer_datas = $wps_account->display_account_informations($customer_id);
				
				// Billing datas
				$billing_infos = ( !empty($order_infos) && !empty($order_infos['billing']) && !empty($order_infos['billing']['address']) ) ? $order_infos['billing']['address'] : ''; 
				$billing_address_content = wps_address::display_an_address( $billing_infos, '', $order_infos['billing']['id'] );
				
				// Shipping datas
				$shipping_infos = ( !empty($order_infos) && !empty($order_infos['shipping']) && !empty($order_infos['shipping']['address']) ) ? $order_infos['shipping']['address'] : '';
				$shipping_address_id =  ( !empty($order_infos) && !empty($order_infos['shipping']) && !empty($order_infos['shipping']['id']) ) ? $order_infos['shipping']['id'] : '';
				$shipping_address_content = wps_address::display_an_address( $shipping_infos, '', $shipping_address_id );
				
				require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "wps_order_customer_informations") );
			}
			else {
				$wps_customer = new wps_customer_ctr(); 
				$customer_lists = $wps_customer->custom_user_list();
				require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "wps_order_choose_customer_inferface") );
			}
			
		}

		/**
		 * AJAX - Choose customer to create order
		 */
		function wps_order_choose_customer() {
			$status = false; $billing_data = $shipping_data = '';
			$customer_id = ( !empty($_POST['customer_id']) ) ? intval( $_POST['customer_id'] ): null;
			if( !empty($customer_id) ) {
				$wps_address = new wps_address();
				$billing_option = get_option( 'wpshop_billing_address' ); 
				$shipping_option = get_option( 'wpshop_shipping_address_choice' ); 
				$billing_option = $billing_option['choice'];
				$customer_addresses_list = $wps_address->get_addresses_list( $customer_id );
				$status = true;
				$billing_data = '<div class="wps-alert-info">' .sprintf( __( 'No Billing address created, <a href="%s" title="' .__( 'Create a new billing address', 'wpshop' ). '" class="thickbox">create one</a>', 'wpshop' ),admin_url( 'admin-ajax.php' ). '?action=wps-add-an-address-in-admin&address_type='.$billing_option.'&customer_id='.$customer_id.'&height=600' ). '</div>';
				
				if( !empty($shipping_option) && !empty($shipping_option['activate']) ) {
					$shipping_option = $shipping_option['choice'];
					$shipping_data = '<div class="wps-alert-info">' .sprintf( __( 'No shipping address created, <a href="%s" title="' .__( 'Create a new shipping address', 'wpshop' ). '" class="thickbox">create one</a>', 'wpshop' ),admin_url( 'admin-ajax.php' ). '?action=wps-add-an-address-in-admin&address_type='.$shipping_option.'&customer_id='.$customer_id.'&height=600' ). '</div>';
				}
				
				if( !empty($customer_addresses_list) ) {
					foreach( $customer_addresses_list as $address_type => $customer_addresses ) {
						if( $billing_option == $address_type ) {
							$billing_data = $wps_address->display_address_in_administration( $customer_addresses, $address_type );
						}
						else {
							$shipping_data = $wps_address->display_address_in_administration( $customer_addresses, $address_type );
						}
					}
				}
			}
			echo json_encode( array( 'status' => $status, 'billing_data' => $billing_data, 'shipping_data' => $shipping_data) );
			wp_die();
		}
		
		function display_order_payments_box( $order ) {
			$order_status = unserialize(WPSHOP_ORDER_STATUS);
			$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);
			
			require( wpshop_tools::get_template_part( WPS_ORDERS_DIR, $this->template_dir, "backend", "wps_order_payment_box") );
		}
}