<?php
/**
 * Main controller file for product into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Main controller class for product into point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_pos_addon_order {

	/**
	 * Call the different element to instanciate the product module
	 */
	function __construct() {
		/**	Call dashboard metaboxes	*/
		add_action( 'admin_init', array( $this, 'dashboard_metaboxes' ) );

		/**	Point d'accroche AJAX / AJAX listeners	*/
		/**	Affichage du content de la commande / Display the order content	*/
		add_action( 'wp_ajax_wps-pos-display-order-content', array(&$this, 'wps_pos_order_content' ) );
		/**	Affichage du formulaire de finalisation de la commande / Display order finalization form	*/
		add_action( 'wp_ajax_wpspos-finalize-order', array( &$this, 'wps_pos_finalize_order' ) );
		/**	Save order into database	*/
		add_action( 'wp_ajax_wpspos-finish-order', array( &$this, 'wps_pos_process_checkout' ) );
	}

	/**
	 * WP CUSTOM HOOK - Call metaboxes for POS addon dashboard
	 */
	function dashboard_metaboxes() {
		/**	Create metaboxes for upper area	*/

		/**	Create metaboxes for left side	*/

		/**	Create metaboxes for right side	*/
		add_meta_box( 'wpspos-dashboard-order-metabox', '<span class="dashicons dashicons-cart"></span> ' . __( 'Order summary', 'wps-pos-i18n' ), array( $this, 'dashboard_order_metabox' ), 'wpspos-dashboard', 'wpspos-dashboard-right' );
	}

	/**
	 * WP CUSTOM METABOX - Display a custom metabox for order summary output
	 */
	function dashboard_order_metabox() {
		require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/order', 'metabox', 'order' ) );
	}

	/**
	 * DISPLAY - Generate the order summary
	 *
	 * @return string The summary of created order
	 */
	function display_wps_pos_order_content() {
		global $wpdb;
		$cart_option = get_option('wpshop_cart_option', array());

		$result = $selected_variation_list = '';
		$price_piloting_option = get_option('wpshop_shop_price_piloting');
		if ( !empty( $_SESSION['cart'] ) ) {
			/* if ( empty( $_SESSION[ 'cart' ][ 'customer_id' ]) ) {
				$result = __( 'No customer selected for the order, please choose a customer from left hand side list for continue this order', 'wps-pos-i18n' );
			}
			else  */if ( !empty( $_SESSION['cart']['order_items'] ) ) {
				/**	Get current order content	*/
				$cart_content = $_SESSION['cart'];
				$order_items = $cart_content['order_items'];

				/**	In case there are item into order, include the order content display	*/
				require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/order', 'order', 'content' ) );
			}
			else {
				$result = __( 'The order is currently empty', 'wps-pos-i18n' );
			}
		}

		return $result;
	}

	/**
	 * AJAX - Affiche le contenu de la commande actuelle / Display current order content
	 */
	function wps_pos_order_content() {
		wp_die( $this->display_wps_pos_order_content() );
	}

	/**
	 * AJAX - Affiche l'interface de finalisation de commande
	 */
	function wps_pos_finalize_order() {
		/**	Check if there is a cart	*/
		if ( !empty( $_SESSION['cart']['order_items'] ) ) {

			/**	Get current order content	*/
			$current_order_id = ( !empty( $_GET ) && !empty( $_GET[ 'order_id' ] ) && is_int( (int)$_GET[ 'order_id' ] ) ) ? (int)$_GET[ 'order_id' ] : null;
			if ( !empty( $current_order_id) ) {
				$cart_content = get_post_meta( $current_order_id, '_order_postmeta', true );
			}
			else {
				$cart_content = $_SESSION['cart'];
			}
			$order_items = $cart_content['order_items'];

			/**	In case there are item into order, include the order content display	*/
			require( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/order', 'finalization' ) );
		}
		else {
			$result = __( 'The order is currently empty', 'wps-pos-i18n' );
		}
		wp_die();
	}

	/**
	 * AJAX - Traite la commande / Process checkout
	 */
	function wps_pos_process_checkout() {
		$status = false;
		$output = $message = '';

		$order_id = ( !empty( $_POST['order_id'] ) ) ? wpshop_tools::varSanitizer( $_POST['order_id'] ) : null;
		$payment_method = ( !empty( $_POST['wpspos-payment-method']) ) ? wpshop_tools::varSanitizer( $_POST['wpspos-payment-method'] ) : null;
		$customer_id =  ( !empty( $_SESSION[ 'cart' ][ 'customer_id' ] ) ) ? wpshop_tools::varSanitizer( $_SESSION[ 'cart' ][ 'customer_id' ] ) : null;
		$payment_amount = ( !empty( $_POST['wps-pos-total-order-amount'] ) ) ? wpshop_tools::varSanitizer( $_POST['wps-pos-total-order-amount'] ) : null;
		$received_payment_amount = ( !empty( $_POST['wpspos-order-received-amount'] ) ) ? wpshop_tools::varSanitizer( $_POST['wpspos-order-received-amount'] ) : $payment_amount;

		if ( !empty( $customer_id )  ) {
			if ( !empty( $payment_method ) ) {
				$_SESSION['cart']['shipping_method'] = __('Point of sale method', 'wpshop');
				$_SESSION['shipping_method'] = __('Point of sale method', 'wpshop');
				
				if ( empty($order_id) ) {
					$order_id = wpshop_checkout::process_checkout( $payment_method, '', $customer_id, $_SESSION['billing_address'], $_SESSION['shipping_address']);
					wp_update_post( array('ID' => $order_id, 'post_parent' => get_current_user_id() ) );
				}

				if ( !empty( $order_id ) ) {
					$status = true;

					if ( !empty( $received_payment_amount ) ) {
						$params_array = array(
							'method' 			=> $payment_method,
							'waited_amount' 	=> $payment_amount,
							'status' 			=> 'payment_received' ,
							'author' 			=> $customer_id,
							'payment_reference' => '',
							'date' 				=> current_time('mysql', 0),
							'received_amount' 	=> ( ( 'money' == $payment_method ) && ( number_format( (float)$received_payment_amount, 2, '.', '') > number_format( (float)$payment_amount, 2, '.', '') ) ) ? $payment_amount : $received_payment_amount ,
						);
						wpshop_payment::check_order_payment_total_amount( $order_id, $params_array, 'completed' );
					}

					/**	Get order content	*/
					$order_postmeta = get_post_meta( $order_id, '_order_postmeta', true );

					ob_start();
					require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend/order', 'order', 'complete') );
					$output = ob_get_contents();
					ob_end_clean();

					/**	Empty the cart	*/
					if ( !empty( $order_postmeta ) && !empty( $order_postmeta['order_status'] ) && ( 'completed' ==  $order_postmeta['order_status'] ) ) {
						$wps_cart = new wps_cart();
						$wps_cart->empty_cart();
					}

					$message = __( 'Order have been saved', 'wps-pos-i18n' );
				}
				else {
					$message = __( 'No order have been found', 'wps-pos-i18n' );
				}
			}
			else {
				$message = __( 'Please choose a payment method for order', 'wps-pos-i18n' );
			}
		}
		else {
			$message = __( 'No customer has been selected for current order', 'wps-pos-i18n' );
		}

		wp_die( json_encode( array( 'status' => $status, 'output' => $output, 'message' => $message, ) ) );
	}

}

?>