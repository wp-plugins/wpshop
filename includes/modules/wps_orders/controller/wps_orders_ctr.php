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

			/** Template Load **/
// 			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );

			add_shortcode( 'order_customer_informations', array( &$this, 'display_order_customer_informations' ) );
			add_shortcode( 'wps_orders_in_customer_account', array($this, 'display_orders_in_account') );

			add_action( 'wp_enqueue_scripts', array( $this, 'wps_orders_scripts') );
			/**	Include the different javascript	*/
			add_action( 'admin_init', array(&$this, 'admin_js') );


			/** Ajax Actions **/
			add_action( 'wp_ajax_wps_add_product_to_quotation', array( &$this, 'wps_add_product_to_quotation') );
// 			add_action( 'wp_ajax_wps_change_product_list', array( &$this, 'wps_change_product_list') );

// 			add_action( 'wp_ajax_wps_orders_load_variations_container', array( &$this, 'wps_orders_load_variations_container') );
// 			add_action( 'wp_ajax_wps_order_refresh_in_admin', array( &$this, 'wps_order_refresh_in_admin') );

			add_action( 'wp_ajax_wps_orders_load_details', array( $this, 'wps_orders_load_details') );

			// Add a product sale historic in administration product panel
			add_action( 'wp_ajax_wps_order_choose_customer', array( $this, 'wps_order_choose_customer' ) );
			
			/** For delete order */
			add_action( 'wp_ajax_wps_delete_order', array( $this, 'wps_delete_order' ) );

		}

		/**
		 * Include stylesheets
		 */
		function admin_js() {
			add_thickbox();
		}


		/**
		 * Add scripts
		 */
		function wps_orders_scripts() {
			wp_enqueue_script( 'wps_orders_fronend', WPS_ORDERS_URL.WPS_ORDERS_DIR.'/assets/frontend/js/wps_orders.js' );
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
		 *	Build an array with the different items to add to an order
		 *
		 *	@param array $products The item list to add to the order
		 *
		 *	@return array $item_list The item to add to order
		 */
		function add_product_to_order( $product ) {
			global $wpdb;

			if( !empty($product) && empty($product['price_ttc_before_discount']) && empty($product['price_ht_before_discount']) ) {
				$price_infos = wpshop_prices::check_product_price( $product, true );
				$product['price_ht'] = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ?  $price_infos['discount']['discount_et_price'] : $price_infos['et'];
				$product['product_price'] = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ? $price_infos['discount']['discount_ati_price'] : $price_infos['ati'];
				$product['tva'] = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ? $price_infos['discount']['discount_tva'] : $price_infos['tva'];
			}

			$price_piloting = get_option( 'wpshop_shop_price_piloting' );

			if ( !empty($price_piloting) && $price_piloting == 'HT') {
				$total_ht = $product['price_ht'] * $product['product_qty'];
				$tva_total_amount = $total_ht * ( $product['tx_tva'] / 100 );
				$total_ttc = $total_ht + $tva_total_amount;
			}
			else {
				$total_ttc = $product['product_price'] * $product['product_qty'];
				$total_ht  = $total_ttc / ( 1 + ( $product['tx_tva'] / 100 ) );
				$tva_total_amount = $total_ttc - $total_ht;
			}

			$tva = !empty($product[WPSHOP_PRODUCT_PRICE_TAX]) ? $product[WPSHOP_PRODUCT_PRICE_TAX] : null;

			$item_discount_type = $item_discount_value = $item_discount_amount = 0;

			$d_amount = !empty( $product ) && !empty( $product[ 'discount_amount' ] ) ? wpshop_tools::formate_number( $product['discount_amount'] ) : null;
			$d_rate = !empty( $product ) && !empty( $product[ 'discount_rate' ] ) ? wpshop_tools::formate_number( $product['discount_rate'] ) : null;
			$d_special = !empty( $product ) && !empty( $product[ 'special_price' ] ) ? wpshop_tools::formate_number( $product['special_price'] ) : null;

			if( !empty($d_amount) ) {
				$item_discount_type = 'discount_amount';
				$item_discount_amount = $product['discount_amount'];
				$item_discount_value = $product['discount_amount'];
			}
			elseif(!empty($d_rate)) {
				$item_discount_type = 'discount_rate';
				$item_discount_amount = $product['discount_rate'];
				$item_discount_value = $product['discount_rate'];
			}
			elseif(!empty($d_special)) {
				$item_discount_type = 'special_price';
				$item_discount_amount = $product['special_price'];
				$item_discount_value = $product['special_price'];
			}

			$item = array(
					'item_id' => $product['product_id'],
					'item_ref' => !empty($product['product_reference']) ? $product['product_reference'] : null,
					'item_name' => !empty($product['product_name']) ? $product['product_name'] : 'wpshop_product_' . $product['product_id'],
					'item_qty' => $product['product_qty'],
					'item_pu_ht' => $product['price_ht'],
					'item_pu_ttc' => $product['product_price'],
					'item_ecotaxe_ht' => 0,
					'item_ecotaxe_tva' => 19.6,
					'item_ecotaxe_ttc' => 0,
					'item_discount_type' => $item_discount_type,
					'item_discount_value' => $item_discount_value,
					'item_discount_amount' => $item_discount_amount,
					'item_tva_rate' => $tva,
					'item_tva_amount' => $product['tva'],
					'item_total_ht' => $total_ht,
					'item_tva_total_amount' => $tva_total_amount,
					'item_total_ttc' => $total_ttc,
					'item_meta' => !empty($product['item_meta']) ? $product['item_meta'] : array()
			);

			$array_not_to_do = array(WPSHOP_PRODUCT_PRICE_HT,WPSHOP_PRODUCT_PRICE_TTC,WPSHOP_PRODUCT_PRICE_TAX_AMOUNT,'product_qty',WPSHOP_PRODUCT_PRICE_TAX,'product_id','product_reference','product_name','variations');

			if(!empty($product['item_meta'])) {
				foreach($product['item_meta'] as $key=>$value) {
					if( !isset($item['item_'.$key]) && !in_array($key, $array_not_to_do) && !empty($product[$key]) ) {
						$item['item_'.$key] = $product[$key];
					}
				}
			}

			/** Check if it's a variation product **/
			if ( !empty($product) && !empty( $product['item_meta']) && !empty($product['item_meta']['variations']) ) {
				foreach( $product['item_meta']['variations'] as $k => $variation ) {
					$product_variation_def = get_post_meta( $k, '_wpshop_variations_attribute_def', true);
					if ( !empty($product_variation_def) ) {
						foreach( $product_variation_def as $attribute_code => $variation_id ) {
							$variation_attribute_def = wpshop_attributes::getElement( $attribute_code, '"valid"', 'code' );
							if ( !empty($variation_attribute_def) ) {
								$item['item_meta']['variation_definition'][$attribute_code]['NAME'] = $variation_attribute_def->frontend_label;
								if ( $variation_attribute_def->data_type_to_use == 'custom' ) {
									$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=%d', $variation_id);
									$variation_name = $wpdb->get_var( $query );
								}
								else {
									$variation_post = get_post( $variation_id );
									$variation_name = $variation_post->post_title;
								}
								$item['item_meta']['variation_definition'][$attribute_code]['UNSTYLED_VALUE'] = $variation_name;
								$item['item_meta']['variation_definition'][$attribute_code]['VALUE'] = $variation_name;
							}
						}
					}
				}
			}
			else {
				/** Check if it's product with one variation **/
				$product_variation_def = get_post_meta( $product['product_id'], '_wpshop_variations_attribute_def', true);

				if ( !empty($product_variation_def) ) {
					foreach( $product_variation_def as $attribute_code => $variation_id ) {
						$variation_attribute_def = wpshop_attributes::getElement( $attribute_code, '"valid"', 'code' );
						if ( !empty($variation_attribute_def) ) {
							$item['item_meta']['variation_definition'][$attribute_code]['NAME'] = $variation_attribute_def->frontend_label;
							if ( $variation_attribute_def->data_type_to_use == 'custom' ) {
								$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id=%d', $variation_id);
								$variation_name = $wpdb->get_var( $query );
							}
							else {
								$variation_post = get_post( $variation_id );
								$variation_name = $variation_post->post_title;
							}
							$item['item_meta']['variation_definition'][$attribute_code]['UNSTYLED_VALUE'] = $variation_name;
							$item['item_meta']['variation_definition'][$attribute_code]['VALUE'] = $variation_name;
						}
					}
				}
			}
			return $item;
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
				$customer_addresses_list = wps_address::get_addresses_list( $customer_id );
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

		/**
		 * AJAX - Delete order by order_id
		 */
		public function wps_delete_order() {
			/** Delete the order */
			wp_trash_post($_POST['order_id']);
			
			wp_die();
		}

}