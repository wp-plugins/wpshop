<?php
/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Cart
 *
 * The WPShop cart class handles the cart process, collecting products data and calcul total
 *
 * @class 		wpwhop_cart
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/** Display the cart */
function wpshop_display_cart() {
	global $wpshop_cart;
	echo $wpshop_cart->display_cart();
}
/** Display the mini cart */
function wpshop_display_mini_cart() {
	global $wpshop_cart;
	$wpshop_cart->display_mini_cart();
}

class wpshop_cart {

	/** Constructor of the class */
	function __construct() {
		add_shortcode( 'wps_cart_summary', array( &$this, 'get_cart_summary' ) );

		if(empty($_SESSION['cart'])) {
			$_SESSION['cart'] = $this->load_cart_from_db();
			$_SESSION['coupon'] = 0;
		}
	}

	/** Reload the cart from the database and return it
	 * @return array
	*/
	function load_cart_from_db() {
		$cart = array();
		if(get_current_user_id())
			$cart = self::get_persistent_cart();

		return $cart;
	}

	/**
	 * Store the cart in the user session
	 */
	function store_cart_in_session($cart) {
		$_SESSION['cart'] = $cart;
	}

	/**
	 * Save the persistent cart when updated
	 */
	function get_persistent_cart() {
		if(get_current_user_id())
			$cart = get_user_meta(get_current_user_id(), '_wpshop_persistent_cart', true);
		return empty($cart) ? array() : $cart;
	}

	/**
	 * Save the persistent cart when updated
	 */
	function persistent_cart_update() {
		if(get_current_user_id())
			update_user_meta( get_current_user_id(), '_wpshop_persistent_cart', array(
				'cart' => $_SESSION['cart'],
			));
	}

	/**
	 * Apply a vouncher on a cart
	 */
	function get_coupon_data() {
		global $wpdb;

		if(!empty($_SESSION['cart']['coupon_id'])) {
			$query = $wpdb->prepare('SELECT meta_key, meta_value FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $_SESSION['cart']['coupon_id']);
			$coupons = $wpdb->get_results($query, ARRAY_A);
			$coupon = array();
			$coupon['coupon_id'] = $_SESSION['cart']['coupon_id'];
			foreach($coupons as $coupon_info){
				$coupon[$coupon_info['meta_key']] = $coupon_info['meta_value'];
			}
			return $coupon;
		}
		return array();
	}


	/**
	*	Store a product list and informations about thus list pricing into an array in order to save a new order
	*
	*	@param array|object $product_list The product's list to make operation on
	*
	*	@return array $cart_infos An array containing the different product infos and the different pricing information about the cart/order
	*/
	function calcul_cart_information($product_list, $custom_order_information = '', $cart_rule = array(), $current_cart = array() ) {
		global $wpdb, $wpshop_payment;
		$cart_infos = array();
		/** Price piloting **/
		$price_piloting = get_option( 'wpshop_shop_price_piloting' );

		$cart_infos = $current_cart;
		$cart_items = ( !empty($current_cart) && !empty($current_cart['order_items'])) ? $current_cart['order_items'] : array();

		/*	Amount vars	*/
		$order_total_ht = 0;
		$order_total_ttc = 0;
		$order_tva = array();

		/* Shipping vars */
		$total_weight = 0;
		$nb_of_items = 0;
		$order_shipping_cost_by_article = 0;

		/* Discount vars */
		$order_discount_rate = 0;
		$order_discount_amount = 0;
		$order_items_discount_amount = 0;
		$order_total_discount_amount = 0;

		if ( !empty($product_list) ) {
			foreach ( $product_list as $product_id => $d ) {
				$product_key = $product_id;
				if ( is_array($d) ) {
					$product_id = $d['product_id'];
					$product_qty = ( !empty($cart_items[$product_key]) && !empty($cart_items[$product_key]['item_qty']) ) ?  $cart_items[$product_key]['item_qty'] + $d['product_qty'] : $d['product_qty'];
					$product_variation = !empty($d['product_variation']) ? $d['product_variation'] : null;
				}
				else {
					$product_id = $d->product_id;
					$product_qty = $d->product_qty;
					$product_variation = !empty($d->product_variation) ? $d->product_variation : null;
				}
				$head_product_id = $d['product_id'];

				if ( !empty($product_variation) && ( count($product_variation) == 1 ) /*&& ($d['product_variation_type'] != 'single')*/ ) {
					$product_id = $product_variation[0];
				}

				$product = wpshop_products::get_product_data($d['product_id'], true);

				$the_product = array_merge( array(
					'product_id'	=> $d['product_id'],
					'product_qty' 	=> $product_qty
				), $product);

				/*	Add variation to product into cart for storage	*/
				if ( !empty($product_variation) ) {
					$the_product = wpshop_products::get_variation_price_behaviour( $the_product, $product_variation, $head_product_id, array('type' => $d['product_variation_type']) );
				}

				$pid = $d['product_id'];

				if ( !empty( $d['free_variation'] ) ) {
					$the_product['item_meta']['free_variation'] = $d['free_variation'];
					$pid = $the_product['product_id'];
				}
				if ( !isset($the_product['product_qty']) ){
					$the_product['product_qty'] = $product_qty;
				}

				/** Check parent if this is a variation **/
				if( get_post_type( $the_product['product_id'] )  == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
					$parent_def = wpshop_products::get_parent_variation ( $the_product['product_id'] );
					if( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
						$variation_def = get_post_meta( $parent_def['parent_post']->ID, '_wpshop_variation_defining', true );
						$parent_meta = $parent_def['parent_post_meta'];
						if( !empty($variation_def) && !empty($variation_def['options']) && !empty($variation_def['options']['priority']) && in_array('combined', $variation_def['options']['priority'] ) && !empty($variation_def['options']['price_behaviour']) && in_array( 'addition', $variation_def['options']['price_behaviour']) && !empty($variation_def['attributes']) && count($variation_def['attributes']) > 1 ) {
							$the_product['product_price'] += number_format( str_replace( ',', '.', $parent_meta['product_price'] ), 2, '.', '' );
							$the_product['price_ht'] += number_format( str_replace( ',', '.',$parent_meta['price_ht']) , 2, '.', '' );
							$the_product['tva'] += number_format( str_replace( ',', '.', $parent_meta['tva']) , 2, '.', '' );
						}
					}
				}
				
				$cart_items[$product_key] = wpshop_orders::add_product_to_order($the_product);
				/* Shipping var */
				$total_weight += !empty($product['product_weight']) ? $product['product_weight'] * $product_qty : 0;
				$nb_of_items += $product_qty;

				if ( !empty($price_piloting) && $price_piloting == 'HT') {
					$cart_items[$product_key]['item_total_ht'] = number_format( $cart_items[$product_key]['item_pu_ht'], 2, '.', '') * $cart_items[$product_key]['item_qty'];
					$cart_items[$product_key]['item_tva_total_amount'] = number_format( ( $cart_items[$product_key]['item_total_ht'] * ( $cart_items[$product_key]['item_tva_rate'] / 100 ) ), 2, '.', '' );
					$cart_items[$product_key]['item_total_ttc'] = number_format( ($cart_items[$product_key]['item_total_ht'] + $cart_items[$product_key]['item_tva_total_amount']), 2, '.', '' );
				}
				else {
					$cart_items[$product_key]['item_total_ttc'] = number_format( $cart_items[$product_key]['item_pu_ttc'], 2, '.', '') * $cart_items[$product_key]['item_qty'];
					$cart_items[$product_key]['item_total_ht'] = number_format( $cart_items[$product_key]['item_total_ttc'] / ( 1 + ( $cart_items[$product_key]['item_tva_rate'] / 100 ) ), 2, '.', '');
					$cart_items[$product_key]['item_tva_total_amount'] = number_format( ( $cart_items[$product_key]['item_total_ttc'] - $cart_items[$product_key]['item_total_ht'] ), 2, '.', '');
				}
				
				
				$order_total_ht += $cart_items[$product_key]['item_total_ht'];
				$order_total_ttc += $cart_items[$product_key]['item_total_ttc'];

			}
			$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ? $order_total_ht : $order_total_ttc;

			if (!empty($this)) {
				$cart_weight = wpshop_shipping::calcul_cart_weight( $cart_items );
				$total_shipping_cost_for_products = wpshop_shipping::calcul_cart_items_shipping_cost( $cart_items );
				$cart_infos['order_shipping_cost'] = wpshop_shipping::get_shipping_cost(count($cart_items), $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight);
			}
			else {
				$cart_weight = wpshop_shipping::calcul_cart_weight( $cart_items );
				$total_shipping_cost_for_products = wpshop_shipping::calcul_cart_items_shipping_cost( $cart_items );
				$cart_infos['order_shipping_cost'] = wpshop_shipping::get_shipping_cost(count($cart_items), $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight);
			}
			if ( isset($custom_order_information['custom_shipping_cost']) && ($custom_order_information['custom_shipping_cost']>=0) ) {
				$cart_infos['order_shipping_cost'] = $custom_order_information['custom_shipping_cost'];
			}

			$query = $wpdb->prepare("SELECT post_id, meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = %s ", '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options');
			$post_list_with_options = $wpdb->get_results($query);
			if ( !empty($post_list_with_options) && !empty($cart_items) ) {
				foreach ( $post_list_with_options as $product_info) {
					$product_meta = unserialize($product_info->meta_value);
					if ( !empty($product_meta['cart']) && !empty($product_meta['cart']['auto_add']) && ($product_meta['cart']['auto_add'] == 'yes') && empty($cart_items[$product_info->post_id]) ) {
						$product = wpshop_products::get_product_data($product_info->post_id, true, '"publish", "draft"');
						$the_product = array_merge( array(
							'product_id'	=> $product_info->post_id,
							'product_qty' 	=> 1
						), $product);

						$nb_of_items++;

						$cart_items[$product_info->post_id] = wpshop_orders::add_product_to_order($the_product);
						
						/* item */
						$order_total_ht += $cart_items[$product_info->post_id]['item_total_ht'];
						$order_total_ttc += $cart_items[$product_info->post_id]['item_total_ttc'];

						/* Si le taux n'existe pas, on l'ajoute */
						if ( !empty($product[WPSHOP_PRODUCT_PRICE_TAX]) ) {
							if ( !empty($order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]]) ) {
								$order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]] += $cart_items[$product_info->post_id]['item_tva_total_amount'];
							}
							else{
								$order_tva[(string)$product[WPSHOP_PRODUCT_PRICE_TAX]] = $cart_items[$product_info->post_id]['item_tva_total_amount'];
							}
						}
					}
				}
			}
			
		}
		else {
			
			if ( !empty($_SESSION['cart']) ) {
				/** Calcul items line **/
				$order_total_ht = $discounted_amount_total_ht = $order_total_ttc = 0;
				$order_tva = array();
				$count_products = 0;

				if( !empty($_SESSION['cart']['order_items']) && is_array($_SESSION['cart']['order_items']) ) {
					foreach( $_SESSION['cart']['order_items'] as $k => $item ) {
						if ( !empty($price_piloting) && $price_piloting == 'HT') {
							$_SESSION['cart']['order_items'][$k]['item_total_ht'] = number_format( $_SESSION['cart']['order_items'][$k]['item_pu_ht'], 2, '.', '') * $_SESSION['cart']['order_items'][$k]['item_qty'];
							$_SESSION['cart']['order_items'][$k]['item_tva_total_amount'] = number_format( ( $_SESSION['cart']['order_items'][$k]['item_total_ht'] * ( $_SESSION['cart']['order_items'][$k]['item_tva_rate'] / 100 ) ), 2, '.', '' );
							$_SESSION['cart']['order_items'][$k]['item_total_ttc'] = number_format( ($_SESSION['cart']['order_items'][$k]['item_total_ht'] + $_SESSION['cart']['order_items'][$k]['item_tva_total_amount']), 2, '.', '' );
						}
						else {
							$_SESSION['cart']['order_items'][$k]['item_total_ttc'] = number_format( $_SESSION['cart']['order_items'][$k]['item_pu_ttc'], 2, '.', '') * $_SESSION['cart']['order_items'][$k]['item_qty'];
							$_SESSION['cart']['order_items'][$k]['item_total_ht'] = number_format( $_SESSION['cart']['order_items'][$k]['item_total_ttc'] / ( 1 + ( $_SESSION['cart']['order_items'][$k]['item_tva_rate'] / 100 ) ), 2, '.', ''); 
							$_SESSION['cart']['order_items'][$k]['item_tva_total_amount'] = number_format( ( $_SESSION['cart']['order_items'][$k]['item_total_ttc'] - $_SESSION['cart']['order_items'][$k]['item_total_ht'] ), 2, '.', '');
						}
						$order_total_ht += $_SESSION['cart']['order_items'][$k]['item_total_ht'];
						/** check if global discount exist **/
						if( !empty($_SESSION['cart']['pos_global_discount']) ) {
							$_SESSION['cart']['order_items'][$k]['item_global_discount_value'] = $_SESSION['cart']['pos_global_discount'];
							$_SESSION['cart']['order_items'][$k]['item_global_discount_type'] = 'percent';
							$_SESSION['cart']['order_items'][$k]['item_global_discount_amount'] = number_format( $_SESSION['cart']['order_items'][$k]['item_total_ht'], 2, '.', '') * ( number_format( $_SESSION['cart']['order_items'][$k]['item_global_discount_value'], 2, '.', '') / 100 );
							/** Recalcul TVA Amount & Total TTC **/
							$_SESSION['cart']['order_items'][$k]['item_total_ttc'] = ( ( number_format( $_SESSION['cart']['order_items'][$k]['item_total_ht'], 2, '.', '') - number_format( $_SESSION['cart']['order_items'][$k]['item_global_discount_amount'], 2, '.', '') ) * ( 1 + ( number_format($_SESSION['cart']['order_items'][$k]['item_tva_rate'], 2, '.', '') / 100 )) );
							$_SESSION['cart']['order_items'][$k]['item_tva_total_amount'] = ( number_format($_SESSION['cart']['order_items'][$k]['item_total_ht'], 2, '.', '') -  number_format( $_SESSION['cart']['order_items'][$k]['item_global_discount_amount'], 2, '.', '') ) * (  number_format($_SESSION['cart']['order_items'][$k]['item_tva_rate'], 2, '.', '') / 100 );

						}

						/** Check if unit discount exists **/
						if ( !empty($_SESSION['cart']['order_items'][$k]['item_unit_discount_value']) && !empty($_SESSION['cart']['order_items'][$k]['item_unit_discount_type']) ) {
							$_SESSION['cart']['order_items'][$k]['item_unit_discount_amount'] = number_format( $_SESSION['cart']['order_items'][$k]['item_total_ht'], 2, '.', '')  * ( number_format( $_SESSION['cart']['order_items'][$k]['item_unit_discount_value'], 2, '.', '') / 100 );
						}

						$order_total_ttc +=  number_format($_SESSION['cart']['order_items'][$k]['item_total_ttc'], 2, '.', '');
					}
				}
				/** Calcul cart summary **/
				$cart_items = (!empty($_SESSION['cart']['order_items']) ) ? $_SESSION['cart']['order_items'] : null;
				$cart_weight = wpshop_shipping::calcul_cart_weight( $cart_items );
				$total_shipping_cost_for_products = wpshop_shipping::calcul_cart_items_shipping_cost( $cart_items );

				$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT=='HT' ? $order_total_ht : $order_total_ttc;
				if( empty( $_SESSION['wpshop_pos_addon']) ) {
					$cart_infos['order_shipping_cost'] =  number_format( wpshop_shipping::get_shipping_cost($count_products, $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight ), 2, '.', '');
				}
				else {
					$cart_infos['order_shipping_cost'] = 0;
				}
				if ( isset($custom_order_information['custom_shipping_cost']) && ($custom_order_information['custom_shipping_cost']>=0) ) {
					$cart_infos['order_shipping_cost'] = number_format( $custom_order_information['custom_shipping_cost'], 2, '.', '');
				}

				$cart_infos['order_total_ht_before_discount'] = number_format($discounted_amount_total_ht, 2, '.', '');



			}
		}




		$cart_infos['order_items'] = ( ( !empty($cart_items) ) ? $cart_items : '');
		$cart_infos['order_total_ht'] = $cart_infos['order_total_ttc'] = 0;
		$cart_infos['order_tva'] = $order_tva = array();
		/** Recalculate cart infos **/
		if ( !empty($cart_infos['order_items'] ) ) {
			foreach( $cart_infos['order_items'] as $item_id => $item ) {
				$cart_infos['order_total_ht'] += number_format($item['item_total_ht'], 2, '.', '');
				$cart_infos['order_total_ttc'] += number_format( $item['item_total_ttc'], 2, '.', '');

				if ( empty($order_tva[(string)$item['item_tva_rate']]) ) {
					$order_tva[(string)$item['item_tva_rate']] =  number_format( $item['item_tva_total_amount'], 2, '.', '');
				}
				else {
					$order_tva[(string)$item['item_tva_rate']] +=  number_format( $item['item_tva_total_amount'], 2, '.', '');
				}
			}

		}

		/** Recalculate shipping cost **/
		$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ? $cart_infos['order_total_ht'] : $cart_infos['order_total_ttc'];
		$cart_weight = wpshop_shipping::calcul_cart_weight( $cart_infos['order_items'] );
		$total_shipping_cost_for_products = wpshop_shipping::calcul_cart_items_shipping_cost( $cart_infos['order_items'] );
		$cart_infos['order_shipping_cost'] = wpshop_shipping::get_shipping_cost(count($cart_infos['order_items']), $total_cart_ht_or_ttc_regarding_config, $total_shipping_cost_for_products, $cart_weight);
		

		/** E.T Shipping Cost **/
		$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
		/** Test if the Price piloting is E.T **/
		if ( !empty($price_piloting_option) && $price_piloting_option == 'HT') {
			/** Calculate The VAT On Shipping Cost **/
			$shipping_cost_tva = ( !empty($cart_infos['order_shipping_cost']) ) ? ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) * number_format(  $cart_infos['order_shipping_cost'], 2, '.', '') : 0;
			$vat_test = (!empty($order_tva['VAT_shipping_cost']) ) ? (float)$order_tva['VAT_shipping_cost'] : 0;
			if ( empty($order_tva['VAT_shipping_cost']) || (  number_format($shipping_cost_tva,  2, '.', '') != number_format($vat_test, 2, '.', '') ) ) {
				$order_tva['VAT_shipping_cost'] = $shipping_cost_tva;
				$total_tva = 0;
				if ( !empty($order_tva) ) {
					foreach ( $order_tva as $tva ) {
						$total_tva += $tva;
					}
				}
				$cart_infos['order_total_ttc'] = number_format( ($cart_infos['order_total_ht'] +  $total_tva) , 2, '.', '');
			}
		}

		$cart_infos['order_grand_total_before_discount'] = number_format( $cart_infos['order_total_ttc'] + ( ( !empty($cart_infos['order_shipping_cost']) ) ? $cart_infos['order_shipping_cost'] : 0), 2, '.', '');
		$cart_infos['order_grand_total'] = number_format( $cart_infos['order_grand_total_before_discount'], 2, '.', '');

		$cart_infos['order_amount_to_pay_now'] = number_format( $cart_infos['order_grand_total'], 2, '.', '');

		if( is_array($order_tva)) {
			ksort($order_tva);
			$cart_infos['order_tva'] = array_map('number_format_hack', $order_tva);

		}
		else {
			$cart_infos['order_tva'] = array();
		}
		$cart_infos['order_temporary_key'] = NULL;
		$cart_infos['order_old_shipping_cost'] = 0;
		$cart_infos['shipping_is_free'] = false;


		/**	Apply the coupon	*/
		$coupon = self::get_coupon_data();
		$cart_infos['coupon_id'] = !empty($coupon['coupon_id']) ? $coupon['coupon_id'] : 0;
		/** Check if there is a amount limitation **/
		$amount_limit_verification = false;
		if ( !empty($coupon['wpshop_coupon_minimum_amount']) ) {
			$limitation_infos = unserialize( $coupon['wpshop_coupon_minimum_amount'] );
			if ( !empty($limitation_infos['amount']) ) {
				if ( empty($limitation_infos['shipping_rule']) || ( !empty($limitation_infos['shipping_rule']) && $limitation_infos['shipping_rule'] == 'shipping_cost') ) {
					if ( ( $cart_infos['order_grand_total'] >= $limitation_infos['amount'] )  ) {
						$amount_limit_verification = true;
					}
				}
				elseif( !empty($limitation_infos['shipping_rule']) && $limitation_infos['shipping_rule'] == 'no_shipping_cost' ) {
					if ( ( $cart_infos['order_total_ttc'] >= $limitation_infos['amount'] )  ) {
						$amount_limit_verification = true;
					}
				}
			}
			else {
				$amount_limit_verification = true;
			}
		}
		else {
			$amount_limit_verification = true;
		}

		if (!empty($coupon['wpshop_coupon_discount_value']) && $amount_limit_verification ) {
			$cart_infos['order_discount_type'] = $coupon['wpshop_coupon_discount_type'];
			$cart_infos['order_discount_value'] = $coupon['wpshop_coupon_discount_value'];

			switch ($coupon['wpshop_coupon_discount_type']) {
				case 'amount':
					$cart_infos['order_discount_amount_total_cart'] = number_format( str_replace( ',', '.', $coupon['wpshop_coupon_discount_value'] ), 2, '.', '');
				break;
				case 'percent':
					$cart_infos['order_discount_amount_total_cart'] = number_format( $cart_infos['order_grand_total'], 2, '.', '') * ( number_format( str_replace( ',', '.', $coupon['wpshop_coupon_discount_value']), 2, '.', '') / 100);
				break;
			}
			$cart_infos['order_grand_total'] -= number_format( $cart_infos['order_discount_amount_total_cart'], 2, '.', '');
			$cart_infos['order_amount_to_pay_now'] = number_format( $cart_infos['order_grand_total'], 2, '.', '');
			$cart_infos['order_discount_amount_total_items'] = 0;
		}


		/**	Apply partial amount on the current order	*/
		$partial_payment = $wpshop_payment->partial_payment_calcul( $cart_infos['order_grand_total'] );
		if ( !empty($partial_payment['amount_to_pay']) ) {
			unset($partial_payment['display']);
			$cart_infos['order_partial_payment'] = number_format( $partial_payment['amount_to_pay'], 2, '.', '');
			$cart_infos['order_amount_to_pay_now'] = number_format( $partial_payment['amount_to_pay'], 2, '.', '');
		}

		if (empty($cart_infos['order_items'])) {
			$cart_infos = array();
		}

		if (isset($_SESSION['cart']['cart_type'])) {
			$cart_infos['cart_type'] = $_SESSION['cart']['cart_type'];
		}

		$cart_infos = apply_filters( 'wps_extra_calcul_in_cart', $cart_infos, $_SESSION );
		return $cart_infos;
	}

	/**
	 * Delete the persistent cart
	 */
	function persistent_cart_destroy() {
		delete_user_meta( get_current_user_id(), '_wpshop_persistent_cart' );
	}

	/**
	 * Check if there is enough stock for asked product if manage stock option is checked
	 *
	 * @param integer $product_id The product we have to check the stock for
	 * @param unknown_type $cart_asked_quantity The quantity the end user want to add to the cart
	 *
	 * @return boolean|string  If there is enough sotck or if the option for managing stock is set to false return OK (true) In the other case return an alert message for the user
	 */
	function check_stock($product_id, $cart_asked_quantity, $combined_variation_id = '') {

		/** Check if variation exists **/
		if ( !empty($combined_variation_id) ) {
			/** Check if variation check stocks **/
			$variation_metadata = get_post_meta( $combined_variation_id, '_wpshop_product_metadata', true );
			if ( isset($variation_metadata['product_stock']) ) {
				$product_id = $combined_variation_id;
			}
		}

		$product_data = wpshop_products::get_product_data($product_id);
		if(!empty($product_data)) {
			$manage_stock = !empty($product_data['manage_stock']) ? $product_data['manage_stock'] : '';
			/** Check if product is variation or parent */
			$product_post_type = get_post_type( $product_id );
			/** If is variation check if parent manage stocks **/
			if ( $product_post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION ) {
				$parent_def = wpshop_products::get_parent_variation( $product_id );
				if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
					$parent_post = $parent_def['parent_post'];
					$parent_product_data = wpshop_products::get_product_data($parent_post->ID);
					$manage_stock = !empty($parent_product_data['manage_stock']) ? $parent_product_data['manage_stock'] : '';
				}
			}
			$manage_stock_is_activated = (!empty($manage_stock) && ( strtolower(__($manage_stock, 'wpshop')) == strtolower(__('Yes', 'wpshop')) )) ? true : false;
			$the_qty_is_in_stock = ( !empty($product_data['product_stock']) && $product_data['product_stock'] >= $cart_asked_quantity ) ? true : false ;

			if (($manage_stock_is_activated && $the_qty_is_in_stock) OR !$manage_stock_is_activated) {
				return true;
			}
			else {
				return __('You cannot add that amount to the cart since there is not enough stock.', 'wpshop');
			}
		}
		return false;
	}

	/**
	 * Change the product quantity into the cart
	 *
	 * @param integer $product_id The product identifier to change quantity for. Allow to check if the product is in cart again/if the roduct has enough stock
	 * @param float $quantity The asked quantity
	 *
	 * @return mixed If an error occured return a alert message. In the other case if the quantity is correctly set return true
	 */
	function set_product_qty($product_id, $quantity) {
		if ( !empty($_SESSION['cart']['order_items'][$product_id]) ) {
			/** Check the stock **/
			$return = self::check_stock($_SESSION['cart']['order_items'][$product_id]['item_id'] , $quantity);
			if($return !== true) return $return;
			$global_discount = $_SESSION['cart']['order_items'][$product_id]['item_qty'] = $quantity;

			/** If Qty = 0 Delete the product **/
			if ( $quantity == 0 ) {
				unset( $_SESSION['cart']['order_items'][$product_id] );
			}

			$order = self::calcul_cart_information( array() );

			self::store_cart_in_session($order);
			$_SESSION['pos_global_discount'] = $global_discount;

			if (get_current_user_id()) {
				self::persistent_cart_update();
			}
		}
		else {
			return __('This product does not exist in the cart.', 'wpshop');
		}
		return 'success';
	}

	/**
	 * Check if the cart is empty or not
	 *
	 * @return boolean The state of the cart
	 */
	function is_empty() {
		$cart = (array)$_SESSION['cart'];

		return empty($cart);
	}

	/**
	 * Empty the current existing cart
	 *
	 * @return void
	 */
	function empty_cart() {
		unset($_SESSION['cart']);
		self::persistent_cart_destroy();

		return;
	}

	/**
	 * Display the cart as a widget. Called with a shortcode
	 *
	 * @return string The "mini" cart content
	 */
	function display_mini_cart() {
		/*
		 * Template parameters
		*/
		$template_part = 'mini_cart_container';
		$tpl_component = array();
		$tpl_component['CART_MINI_CONTENT'] = self::mini_cart_content();
		$tpl_component['FREE_SHIPPING_COST_ALERT'] = wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert');
		$mini_cart = wpshop_display::display_template_element($template_part, $tpl_component);

		echo $mini_cart;
	}

	/**
	 * Generate output for the cart widget
	 *
	 * @return string $mini_cart_content The cart content
	 */
	function mini_cart_content() {
		$mini_cart_content = '';
		$tpl_component = array();
		$cart = ( !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ) ? $_SESSION['cart'] : null;
		$cpt=0;
		if (!empty($cart['order_items'])) {
			foreach ($cart['order_items'] as $item) {
				$cpt += $item['item_qty'];
			}
		}
		if ( $cpt == 0 ) {
			$mini_cart_content = wpshop_display::display_template_element( 'wpshop_empty_mini_cart', array() );
		}
		else {
			$cart_link = get_permalink( wpshop_tools::get_page_id(get_option('wpshop_cart_page_id')) );
			$currency = wpshop_tools::wpshop_get_currency();

			/*
			 * Template parameters
			 */
			$template_part = 'mini_cart_content';

			$tpl_component['PDT_CPT'] = $cpt;
			$tpl_component['CART_TOTAL_AMOUNT'] = number_format($cart['order_grand_total'],2);
			$tpl_component['FREE_SHIPPING_COST_ALERT'] = wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert');
			/*
			 * Build template
			 */
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require_once(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$mini_cart_content = ob_get_contents();
				ob_end_clean();
			}
			else {
				$mini_cart_content = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $mini_cart_content;
	}

	/**
	 * Display the cart content
	 *
	 * @param boolean $hide_button Optionnal Allows to specify if the cart must be displayed completly or just the content,not form / buttons
	 * @param array $order Optionnal An array with the content of a given order. Allows to display a different order that is stored in customer session
	 * @param string $from Optionnal Allows to define if the function is called from frontend aprt or from backend part
	 *
	 * @return string
	 */
	function display_cart($hide_button=false, $order=array(), $from='') {
		global $wpdb;
		$cart = empty($order) ? $_SESSION['cart'] : $order;
		/**	Check cart type	*/
		$cart_type = (!empty($cart['cart_type']) && $cart['cart_type']=='quotation') ? 'quotation' : 'cart';
		$cartContent = $cart_output = '';
		if( !empty($cart['order_items']) ) {
			if ( !is_admin() ) {
				$order = self::calcul_cart_information( array() );
				self::store_cart_in_session($order);
				$cart = $_SESSION['cart'];
			}
			if(!empty($cart['order_items']['item_id'])) {
				$tpl_component = array();

				$tpl_component['CART_LINE_ITEM_ID'] = $cart['order_items']['item_id'];
				$tpl_component['CART_LINE_ITEM_QTY'] = $cart['order_items']['item_qty'];
				$tpl_component['CART_LINE_ITEM_LINK'] = get_permalink($cart['order_items']['item_id']);
				$tpl_component['CART_LINE_ITEM_NAME'] = $cart['order_items']['item_name'];
				$tpl_component['CART_LINE_ITEM_PUHT'] = ( !empty($cart['order_items']['item_pu_ht_before_discount']) ) ? wpshop_tools::formate_number($cart['order_items']['item_pu_ht_before_discount']) : wpshop_tools::formate_number($cart['order_items']['item_pu_ht']);
				$tpl_component['CART_LINE_ITEM_DISCOUNT_AMOUNT'] = ( !empty($cart['order_items']['item_discount_amount']) )  ? wpshop_tools::formate_number($cart['order_items']['item_discount_amount']) : wpshop_tools::formate_number(0);
				$tpl_component['CART_LINE_ITEM_TPHT'] =  wpshop_tools::formate_number( $cart['order_items']['item_total_ht'] );
				//$tpl_component['CART_LINE_ITEM_TPTTC'] = sprintf('%0.2f', $cart['order_items']['item_pu_ttc']*$b['item_qty']);
				$tpl_component['CART_LINE_ITEM_TPTTC'] = wpshop_tools::formate_number( $cart['order_items']['item_total_ttc']);

				$tpl_component['CART_LINE_ITEM_QTY_'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_qty_content', $tpl_component) : $cart['order_items']['item_qty'];
				$tpl_component['CART_LINE_ITEM_REMOVER'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_line_remove', $tpl_component) : '';

				$cartContent .= wpshop_display::display_template_element('cart_line', $tpl_component);
			}
			else{
				$product_list_for_details_replacement = array();
				$product_details_replacement = array();
				foreach($cart['order_items'] as $product_key => $b) :
					$product_img = '<img src="' .WPSHOP_DEFAULT_PRODUCT_PICTURE. '" alt="no picture" />';
					$current_post_type = get_post_type( $b['item_id'] );
					$is_variation = get_post_meta($b['item_id'], '_wpshop_variations_attribute_def', true);
					$product_name = $b['item_name'];
					$item_link =  get_permalink($b['item_id']);
					$product_img = get_the_post_thumbnail( $b['item_id'], 'thumbnail');
					if ( !empty($is_variation) ) {
						$parent_product = wpshop_products::get_parent_variation($b['item_id']);
						if ( !empty($parent_product) && !empty($parent_product['parent_post']) ) {
							$parent_post = $parent_product['parent_post'];
							$product_name = $parent_post->post_title;
							$item_link = get_permalink($parent_post->ID);
							$product_img = get_the_post_thumbnail( $parent_post->ID, 'thumbnail');
						}
					}

					if ( !empty( $current_post_type ) ) {
						$tpl_component = array();
						$tpl_component['CART_LINE_ITEM_ID'] = $product_key;
						$tpl_component['CART_LINE_ITEM_QTY'] = $b['item_qty'];
						$tpl_component['CART_LINE_ITEM_PICTURE'] = $product_img;
						$tpl_component['CART_LINE_ITEM_LINK'] = $item_link;
						$tpl_component['CART_LINE_ITEM_NAME'] = $product_name;
						$tpl_component['CART_LINE_ITEM_PUHT'] = ( !empty($b['item_pu_ht_before_discount']) )  ? wpshop_tools::formate_number($b['item_pu_ht_before_discount']) : wpshop_tools::formate_number($b['item_pu_ht']);
						$tpl_component['CART_LINE_ITEM_DISCOUNT_AMOUNT'] = ( !empty($b['item_discount_amount']) )  ? wpshop_tools::formate_number($b['item_discount_amount']) : wpshop_tools::formate_number(0);
						$tpl_component['CART_LINE_ITEM_TPHT'] = wpshop_tools::formate_number( $b['item_total_ht'] );
						$tpl_component['CART_LINE_ITEM_TPTTC'] = wpshop_tools::formate_number( $b['item_total_ttc']);
						$tpl_component['CART_PRODUCT_NAME'] = wpshop_display::display_template_element('cart_product_name', $tpl_component);

						$tpl_component['CART_LINE_ITEM_QTY_'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_qty_content', $tpl_component) : $b['item_qty'];
						$tpl_component['CART_LINE_ITEM_REMOVER'] = empty($cart['order_invoice_ref']) ? wpshop_display::display_template_element('cart_line_remove', $tpl_component) : '';

						$post_meta = get_post_meta($b['item_id'], '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options', true);
						if ( !empty($post_meta['cart']) && !empty($post_meta['cart']['auto_add']) && ($post_meta['cart']['auto_add'] == 'yes')) {
							$tpl_component['CART_LINE_ITEM_QTY_'] = 1;
							$tpl_component['CART_LINE_ITEM_REMOVER'] = '';
							$tpl_component['CART_PRODUCT_NAME'] = wpshop_tools::trunk($product_name, 30);
						}



						/**	Get attribute order for current product	*/
						$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($b['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
						$output_order = array();
						if ( count($product_attribute_order_detail) > 0  && is_array($product_attribute_order_detail) ) {
							foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
								foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
									if ( !empty($attribute_def->code) )
										$output_order[$attribute_def->code] = $position;
								}
							}
						}
						$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $b['item_meta'], $output_order, 'cart' );
						ksort($variation_attribute_ordered['attribute_list']);
						$tpl_component['CART_PRODUCT_MORE_INFO'] = '';
						foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
							$tpl_component['CART_PRODUCT_MORE_INFO'] .= $attribute_variation_to_output;
						}

						$cartContent .= wpshop_display::display_template_element('cart_line', $tpl_component);
						$product_list_for_details_replacement[] = $b['item_id'];
					}
				endforeach;

				$variation_details = array();
				foreach ( $product_list_for_details_replacement as $product_id) {
					$variation_details['CART_PRODUCT_MORE_INFO_' . $product_id] = '';
					if ( array_key_exists($product_id, $product_details_replacement) ) {
						foreach ( $product_details_replacement[$product_id] as $variation_detail ) {
							$variation_details['CART_PRODUCT_MORE_INFO_' . $product_id] .= $variation_detail;
						}
					}
				}

				$cartContent = wpshop_display::feed_template($cartContent, $variation_details);
				/** Check if there is a gift product **/
				$cartContent = wpshop_cart_rules::add_gift_product_to_cart ( $cartContent, $order );
			}
			/**	In case we are in admin part, display a link allowing admin to add a product to the current order	*/
			if ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) {
				$cartContent .= '
					<tr>
						<td colspan="2" style="text-align : left !important;"><input type="button" id="order_new_product_add_opener" value="' . __('Add a product to the current order', 'wpshop') . '"  class="button-primary" /></td>
						<td colspan="4">&nbsp;</td>
					</tr>';
			}

			/**	If cart is not empty display different summary lines at cart bottom	*/
			if ( !empty($cartContent) ) {
				$tpl_component = array();

				$tva_string = '';
				if ( !empty($cart['order_tva']) ) {
					foreach ( $cart['order_tva'] as $k => $v ) {
						if ( !empty($k) ) {
							if ( $k == 'VAT_shipping_cost') {
								$k = __('on Shipping cost', 'wpshop').' '.WPSHOP_VAT_ON_SHIPPING_COST;
							}
							$tva_string .= wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '" id="tax_total_amount_' . str_replace(".","_",$k),'CART_SUMMARY_TITLE' => __('Tax','wpshop') . ' ' . $k . '%', 'CART_SUMMARY_AMOUNT' => wpshop_tools::formate_number($v), 'CART_SUMMARY_AMOUNT_CLASS' => ''));
						}
					}
				}

				$tpl_component['CART_OUTPUT'] = wpshop_display::display_template_element('cart_table_def', array('CART_TABLE_COLUMN_DEF' => wpshop_display::display_template_element('cart_table_column_def', array()), 'CART_CONTENT' => $cartContent));
				$tpl_component['CART_PRICE_ET'] = wpshop_tools::formate_number($cart['order_total_ht']);
				$tpl_component['CART_TAXES'] = $tva_string;

				$shipping_cost_from_option = get_option( 'wpshop_shipping_cost_from' );
				$shipping_cost_from = ( empty($_SESSION['shipping_address']) && (float)$cart['order_shipping_cost'] > 0 && !empty($shipping_cost_from_option) ) ? '<span class="wps_shipping_cost_from">'.__('From', 'wpshop').'</span> ': '';
				$tpl_component['CART_SHIPPING_COST'] = ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) ? '<input type="text" class="wpshop_order_shipping_cost_custom_admin" value="' . number_format($cart['order_shipping_cost'], 2, '.', '') . '" />' : $shipping_cost_from.wpshop_tools::formate_number($cart['order_shipping_cost']);

				$tpl_component['CART_DISCOUNT_SUMMARY'] = '';
				if(!empty($cart['order_grand_total_before_discount']) && $cart['order_grand_total_before_discount'] != $cart['order_grand_total']){
					$tpl_component['CART_DISCOUNT_SUMMARY'] = wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '','CART_SUMMARY_TITLE' => __('Total ATI before discount','wpshop'), 'CART_SUMMARY_AMOUNT' => wpshop_tools::formate_number($cart['order_grand_total_before_discount']), 'CART_SUMMARY_AMOUNT_CLASS' => ' total_ttc_before_discount'));
					$discount_title = ( !empty($cart['coupon_id']) ? ' ('.get_the_title( $cart['coupon_id'] ).')' : '');
					$tpl_component['CART_DISCOUNT_SUMMARY'] .= wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => '','CART_SUMMARY_TITLE' => __('Discount','wpshop').$discount_title, 'CART_SUMMARY_AMOUNT' => wpshop_tools::formate_number( ( (!empty($cart['order_discount_amount_total_cart'])) ? $cart['order_discount_amount_total_cart'] : 0 ) ), 'CART_SUMMARY_AMOUNT_CLASS' => ' discount_amount'));
				}

				$tpl_component['CART_TOTAL_ATI'] = wpshop_tools::formate_number($cart['order_grand_total']);


				/** Display Coupon **/
				$tpl_component['CART_DISCOUNT_SUMMARY'] = '';
				if( !empty($cart) && !empty($cart['coupon_id']) ) {
					$coupon_id = $cart['coupon_id'];
					$coupon_value = $cart['order_discount_value'];
					$tpl_component['CART_DISCOUNT_SUMMARY'] = '<div id="order_coupon_summary" >'. __('Discount','wpshop'). ' (' .get_the_title($coupon_id) . ') : <span class="right">' .number_format( $coupon_value, 2, '.', '' ). ' '.wpshop_tools::wpshop_get_currency( false ). '</span></div>';
				}

				/**	Do treatment on partial amount for current order	*/
				$tpl_component['CART_PARTIAL_PAYMENT'] = '';
				if ( !empty($cart['order_partial_payment']) ) {
					$wps_partial_payment_data = get_option( 'wpshop_payment_partial' );
					$partial_payment_informations = $wps_partial_payment_data['for_all'];
					$tpl_component['CART_PARTIAL_PAYMENT'] = wpshop_display::display_template_element('cart_summary_line_content', array('CART_SUMMARY_LINE_SPECIFIC' => ' wpshop_partial_amount_to_pay','CART_SUMMARY_TITLE' => sprintf(__('Payable now %s','wpshop'), '(' . $partial_payment_informations['value'] . ( ( !empty($partial_payment_informations['type']) && $partial_payment_informations['type'] == 'percentage' ) ? '%': wpshop_tools::wpshop_get_currency( false ) ) . ')'), 'CART_SUMMARY_AMOUNT' => number_format( $cart['order_partial_payment'], 2, '.', '' ), 'CART_SUMMARY_AMOUNT_CLASS' => ' partial_amount_to_pay'));
				}


				$tpl_component['CART_VOUNCHER'] = '';
				$tpl_component['CART_EMPTY_BUTTON'] = '';
				$tpl_component['CART_BUTTONS'] = '';
				if ( $from != 'admin' ) {
					/**	Check if vouncher there are existing vouncher	*/
					$existing_vouncher = query_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'post_per_page' => '-1') );
					if ( !empty($existing_vouncher) && ((count($existing_vouncher) > 1) || ((count($existing_vouncher) == 1) && ($existing_vouncher[0]->ID != $cart['coupon_id']))) ) {
						$tpl_component['CART_VOUNCHER'] = wpshop_display::display_template_element('cart_vouncher_part', array());
					}
					wp_reset_query();

					if ( !$hide_button ) {
						/**	Display button to validate cart / button to empty cart	*/
						if ( ($cart_type == 'quotation') ) {
							$tpl_component['CART_BUTTONS'] = wpshop_display::display_template_element('cart_quotation_buttons', array() );
						}
						else {
							$tpl_component['CART_BUTTONS'] = wpshop_display::display_template_element('cart_buttons', array() );
						}

					}
				}
				$tpl_component['CART_FREE_SHIPPING_COST_ALERT'] = wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert');
				$cart_output .= (empty($hide_button) ? '<form action="'.self::get_checkout_url().'" method="post">' : '') . '<input type="hidden" name="wpshop_cart_hide_button_current_state" id="wpshop_cart_hide_button_current_state" value="' . $hide_button . '" />' . wpshop_display::display_template_element('cart_main_page', $tpl_component) . (empty($hide_button) ? '</form>' : '');
// 				if( !empty($hide_button) ) {
// 					$cart_output .=  '<form action="'.self::get_checkout_url().'" method="post"><input type="hidden" name="wpshop_cart_hide_button_current_state" id="wpshop_cart_hide_button_current_state" value="' . $hide_button . '" />' . wpshop_display::display_template_element('cart_main_page', $tpl_component) .'</form>';
// 				}
// 				else {
// 					$cart_output = wpshop_display::display_template_element('cart_main_page', $tpl_component);
// 					if( empty($from) || ( !empty($from) && $from != 'ajax_request') ) {
// 						$cart_output .= wpshop_display::display_template_element('cart_container', array('CART_CONTENT', $cart_output) );
// 					}
// 				}
			}
		}
		else if ( ($from == 'admin') && empty($cart['order_invoice_ref']) ) {
			//$cart_output .= '<div class="cart"><a href="#" id="order_new_product_add_opener" >' . __('Add a product to the current order', 'wpshop') . '</a></div>';
		}
		else $cart_output .= '<div class="cart">'.__('Your cart is empty.','wpshop').'</div>';

		return $cart_output;
	}

	/**
     * Check if product is in the cart and return cart item key
     * @param int $product_id
     * @return int|null
     */
	function find_product_in_cart($product_id) {
		if(!empty($_SESSION['cart']['order_items'])) {
			foreach ($_SESSION['cart']['order_items'] as $cart_item_key => $cart_item) :
				if ($cart_item['item_id'] == $product_id) :
					return $cart_item_key;
				endif;
			endforeach;
		}
    return NULL;
  }

	/**
	 * Add a product to the cart
	 * @param   string	product_id	contains the id of the product to add to the cart
	 * @param   string	quantity	contains the quantity of the item to add
	 */
function add_to_cart( $product_list, $quantity, $type='normal', $extra_params=array(), $from_admin = '' ) {
		global $wpdb;
		/** Check if a cart already exist. If there is already a cart that is not the same type (could be a cart or a quotation)	*/
		if ( empty( $from_admin ) ){
			if(isset($_SESSION['cart']['cart_type']) && $type != $_SESSION['cart']['cart_type'] ) {
				return __('You have another element type into your cart. Please finalize it by going to cart page.', 'wpshop');
			}
			else {
				$_SESSION['cart']['cart_type'] = $type;
			}
		}
		$order_meta = $_SESSION['cart'];
		$order_items = array();

		foreach ($product_list as $pid => $product_more_content) {
			if ( count($product_list) == 1 ) {
				if ($quantity[$pid] < 1) $quantity[$pid] = 1;
				$product = wpshop_products::get_product_data($product_more_content['id']);
				/** Check if the selected product exist	*/
				if ( $product === false ) return __('This product does not exist', 'wpshop');

				/** Get information about the product price	*/
				$product_price_check = wpshop_prices::get_product_price($product, 'check_only');
				if ( $product_price_check !== true ) return $product_price_check;

				$the_quantity = 1;

				if ( !empty($product_more_content['defined_variation_priority']) && $product_more_content['defined_variation_priority'] == 'combined' && !empty($product_more_content['variations']) && !empty($product_more_content['variations'][0]) ) {
					/** Get the asked quantity for each product and check if there is enough stock	*/
					$the_quantity = /*!empty($_SESSION['cart']['order_items'][$product_more_content['variations'][0]]) ? $quantity[$pid] + $_SESSION['cart']['order_items'][$product_more_content['variations'][0]]['item_qty'] : */ $quantity[$pid];
				}
				else {
					/** Get the asked quantity for each product and check if there is enough stock	*/
					$the_quantity = /*!empty($_SESSION['cart']['order_items'][$pid]) ? $quantity[$pid]+$_SESSION['cart']['order_items'][$pid]['item_qty'] : */ $quantity[$pid];
				}

				$quantity[$pid] = $the_quantity;

				$variation_id = 0;
				if ( !empty($product_more_content) && !empty($product_more_content['variations']) && !empty($product_more_content['variations'][0]) && !empty($product_more_content['defined_variation_priority']) && $product_more_content['defined_variation_priority'] == 'combined' ){
					$variation_id = $product_more_content['variations'][0];
				}
				$quantity_to_check = ( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) && !empty($_SESSION['cart']['order_items'][$pid]) && !empty($_SESSION['cart']['order_items'][$pid]['item_qty'])  ) ? $_SESSION['cart']['order_items'][$pid]['item_qty'] + $the_quantity : $the_quantity;
				$product_stock = self::check_stock($product_more_content['id'], $quantity_to_check, $variation_id );
				if ( $product_stock !== true ) {
					return $product_stock;
				}
			}

			$order_items[$pid]['product_id'] = $product_more_content['id'];
			$order_items[$pid]['product_qty'] = $quantity[$pid];

			/** For product with variation	*/
			$order_items[$pid]['product_variation_type'] = !empty( $product_more_content['variation_priority']) ? $product_more_content['variation_priority'] : '';
			$order_items[$pid]['free_variation'] = !empty($product_more_content['free_variation']) ? $product_more_content['free_variation'] : '';
			$order_items[$pid]['product_variation'] = '';
			if ( !empty($product_more_content['variations']) ) {
				foreach ( $product_more_content['variations'] as $variation_id) {
					$order_items[$pid]['product_variation'][] = $variation_id;
				}
			}
		}

		$current_cart = ( !empty( $order_meta )) ? $order_meta : array();
		$order = self::calcul_cart_information($order_items, $extra_params, '', $current_cart );

		self::store_cart_in_session($order);

		/** Store the cart into database for connected user */
		if ( get_current_user_id() ) {
			self::persistent_cart_update();
		}

		return 'success';
	}

	/**
	 * Gets the url to the checkout page
	 * @return void
	 */
	function get_checkout_url() {
		$checkout_page_id = get_option('wpshop_checkout_page_id');
		if ($checkout_page_id) :
			return get_permalink( wpshop_tools::get_page_id( $checkout_page_id ) );
		endif;
	}

	function get_cart_summary() {
		$output = '';
		$tpl_component = $sub_tpl_component = array();
 		$tpl_component['TOTAL_CART_AMOUNT'] = ( !empty( $_SESSION['cart']['order_grand_total_before_discount'] ) )? number_format( $_SESSION['cart']['order_grand_total_before_discount'], 2, '.', '') : 0;
 		$tpl_component['CART_DISCOUNT'] = ( !empty( $_SESSION['cart']['order_grand_total_before_discount'] ) && !empty( $_SESSION['cart']['order_grand_total'] )) ? number_format( $_SESSION['cart']['order_grand_total_before_discount'] - $_SESSION['cart']['order_grand_total'], 2, '.', '') : 0;
 		$tpl_component['SHIPPING_COST'] = ( !empty( $_SESSION['cart']['order_shipping_cost'] ) ) ?  number_format($_SESSION['cart']['order_shipping_cost'] , 2, '.', ''): 0;
 		$tpl_component['ORDER_AMOUNT'] = ( !empty( $_SESSION['cart']['order_amount_to_pay_now'] ) ) ?  number_format($_SESSION['cart']['order_amount_to_pay_now'], 2, '.', '') : 0;

		$output = wpshop_display::display_template_element('wps_cart_summary', $tpl_component );
		unset( $tpl_component);
		return $output;
	}


}