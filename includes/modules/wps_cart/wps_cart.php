<?php
/**
Plugin Name: Eoxia - Cart
Description: Manage a cart
Version: 1.0
Author: Eoxia
Author URI: http://eoxia.com/
*/
/**
 * Bootstrap file
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists('wps_cart') ) {
	/** Template Global vars **/
	DEFINE('WPS_CART_DIR', basename(dirname(__FILE__)));
	DEFINE('WPS_CART_PATH', str_replace( "\\", "/", str_replace( WPS_CART_DIR, "", dirname( __FILE__ ) ) ) );
	DEFINE('WPS_CART_URL', str_replace( str_replace( "\\", "/", ABSPATH), site_url() . '/', WPS_CART_PATH ) );
	
	class wps_cart {
		/**
		 * Define the main directory containing the template for the current plugin
		 * @var string
		 */
		private $template_dir;
		/**
		 * Define the directory name for the module in order to check into frontend
		 * @var string
		 */
		private $plugin_dirname = WPS_CART_DIR;
		
		function __construct() {
			/** Template Load **/
			$this->template_dir = WPS_CART_PATH . WPS_CART_DIR . "/templates/";
			/** WPShop Cart Shortcode **/
			add_shortcode( 'wps_cart', array( &$this, 'display_cart') );
			/** WPShop Mini Cart Shortcode **/
			add_shortcode( 'wps_mini_cart', array( &$this, 'display_mini_cart') );
			/** WPShop Resume Cart Shorcode **/
			add_shortcode( 'wps_resume_cart', array( &$this, 'display_resume_cart') );
			/** Apply Coupon Interface **/
			add_shortcode( 'wps_apply_coupon', array( &$this, 'display_apply_coupon_interface') );
			/** NUmeration Cart **/
			add_shortcode( 'wps-numeration-cart', array( &$this, 'display_wps_numeration_cart') );
			
			
			/** Add Javascript files */
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'wps_cart_js', plugins_url('templates/js/wps_cart.js', __FILE__) );
			
			/** Ajax Actions **/
			add_action( 'wp_ajax_wps_reload_cart', array( &$this, 'wps_reload_cart') );
			add_action( 'wp_ajax_nopriv_wps_reload_cart', array( &$this, 'wps_reload_cart') );
			
			add_action( 'wp_ajax_wps_reload_mini_cart', array( &$this, 'wps_reload_mini_cart') );
			add_action( 'wp_ajax_nopriv_wps_reload_mini_cart', array( &$this, 'wps_reload_mini_cart') );
			
			add_action( 'wp_ajax_wps_reload_summary_cart', array( &$this, 'wps_reload_summary_cart') );
			add_action( 'wp_ajax_nopriv_wps_reload_summary_cart', array( &$this, 'wps_reload_summary_cart') );
			
			add_action( 'wp_ajax_wps_apply_coupon', array( &$this, 'wps_apply_coupon') );
			add_action( 'wp_ajax_nopriv_wps_apply_coupon', array( &$this, 'wps_apply_coupon') );
			
			add_action( 'wp_ajax_wps_cart_pass_to_step_two', array( &$this, 'wps_cart_pass_to_step_two') );
			add_action( 'wp_ajax_nopriv_wps_cart_pass_to_step_two', array( &$this, 'wps_cart_pass_to_step_two') );
			
			add_action( 'wp_ajax_wps_empty_cart', array( &$this, 'wps_empty_cart') );
			add_action( 'wp_ajax_nopriv_wps_empty_cart', array( &$this, 'wps_empty_cart') );
			
			add_action('wsphop_options', array(&$this, 'declare_options'), 8);
		}
		
		
		public static function declare_options () {
			if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation')) ){
				register_setting('wpshop_options', 'wpshop_cart_option', array('wps_cart', 'wpshop_options_validate_cart_type'));
				add_settings_field('wpshop_cart_type', __('Which type of cart do you want to display', 'wpshop'), array('wps_cart', 'wpshop_cart_type_field'), 'wpshop_cart_info', 'wpshop_cart_info');
			}
		}
		
		function wpshop_options_validate_cart_type( $input ) {
			return $input;
		}
		
		function wpshop_cart_type_field() {
			$cart_option = get_option( 'wpshop_cart_option' );
			
			$output  = '<select name="wpshop_cart_option[cart_type]">';
			$output .= '<option value="simplified_ati" ' .( ( !empty($cart_option) && !empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'simplified_ati' ) ? 'selected="selected"' : ''). ' >' .__( 'Simplified cart ATI', 'wpshop'). '</option>';
			$output .= '<option value="simplified_et" ' .( ( !empty($cart_option) && !empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'simplified_et' ) ? 'selected="selected"' : ''). ' >' .__( 'Simplified cart ET', 'wpshop'). '</option>';
			$output .= '<option value="full_cart" ' .( ( !empty($cart_option) && !empty($cart_option['cart_type']) && $cart_option['cart_type'] == 'full_cart' ) ? 'selected="selected"' : ''). ' >' .__( 'Full cart', 'wpshop'). '</option>';
			$output .= '</select>';
			
			echo $output;
		}
		
		
		
		/** Display Cart **/
		function display_cart( $args ) {
			$cart_type = ( !empty($args) && !empty($args['cart_type']) ) ?  $args['cart_type']: '';
			$oid =  ( !empty($args) && !empty($args['oid']) ) ?  $args['oid'] : '';
			$output  = '<div id="wps_cart_container" class="wps-bloc-loader">';
			$output .= self::cart_content($cart_type, $oid);
			$output .= '</div>';
			return $output;
		}
		
		/** Cart Content **/
		function cart_content( $cart_type = '', $oid = '' ) {
			global $wpdb;
			$output = '';
			$account_origin = false;
			$cart_option = get_option( 'wpshop_cart_option' );
			$cart_option = ( !empty($cart_option) && !empty($cart_option['cart_type']) ) ? $cart_option['cart_type'] : 'simplified_ati';
			
			$price_piloting  = get_option( 'wpshop_shop_price_piloting' );
			
			$coupon_title = $coupon_value = '';
			$cart_content = ( !empty($_SESSION) && !empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
			if( !empty($oid) ) {
				$account_origin = true;
				$cart_content = get_post_meta( $oid, '_order_postmeta', true);
			}
			$currency = wpshop_tools::wpshop_get_currency( false );
			
			if ( !empty($cart_content) ) {
				$cart_items = ( !empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
				
				if ( !empty($cart_content['coupon_id']) ) {
					$coupon_title = get_the_title( $cart_content['coupon_id']);
					$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
				}
				
				if ( !empty($cart_items) ) {
					/** Total values **/
					$shipping_cost_et = ( !empty($cart_content['order_shipping_cost']) ) ? ( (!empty($price_piloting) && $price_piloting != 'HT') ? ( $cart_content['order_shipping_cost'] / ( 1 + ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) ) : $cart_content['order_shipping_cost'] ) : 0;
					$shipping_cost_vat = ( !empty( $shipping_cost_et) ) ? ( $shipping_cost_et * ( WPSHOP_VAT_ON_SHIPPING_COST / 100 ) ) : 0;
					$shipping_cost_ati = ( !empty($cart_content['order_shipping_cost']) ) ? ( (!empty($price_piloting) && $price_piloting != 'HT') ? $cart_content['order_shipping_cost'] : $cart_content['order_shipping_cost'] + $shipping_cost_vat ) : 0;
					$total_et = ( !empty( $cart_content['order_total_ht']) ) ? $cart_content['order_total_ht'] : 0;
					$order_totla_before_discount = ( !empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
					$order_amount_to_pay_now = wpshop_tools::formate_number( $cart_content['order_amount_to_pay_now'] );
					$total_ati = ( !empty( $order_amount_to_pay_now ) && !empty($oid) && $order_amount_to_pay_now > 0 ) ? $cart_content['order_amount_to_pay_now'] : ( (!empty($cart_content['order_grand_total']) ) ? $cart_content['order_grand_total'] : 0 );
					ob_start();
					require( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "cart/cart") );
					$output = ob_get_contents();
					ob_end_clean();
				}
				else {
					return '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';;
				}
			}
			else {
				return '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';;
			}
			return $output;
		}

		/** Display mini cart **/
		function display_mini_cart( $args ) {
			$total_cart_item = 0;
			$cart_content = ( !empty($_SESSION) && !empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
			$type = ( !empty($args) && !empty($args['type']) ) ? $args['type'] : '';
			
			
			if ( !empty($cart_content) ) {
				$cart_items = ( !empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
				/** Count items **/
				$total_cart_item = self::total_cart_items( $cart_items );
				$mini_cart_body = self::mini_cart_content( $type );
			}
			else {
				$mini_cart_body = __( 'Your cart is empty', 'wpshop' );
			}
			ob_start();
			if( !empty($type) && $type == 'fixed' ) {
				require_once(wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "mini-cart/fixed-mini-cart") );
			}
			else {
				require_once( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "mini-cart/mini-cart") );
			}
			
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
		
		/** Mini cart Content **/
		function mini_cart_content( $type = '') {
			$currency = wpshop_tools::wpshop_get_currency( false );
			$cart_content = ( !empty($_SESSION) && !empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
			$output = '';
			if ( !empty($cart_content) ) {
				$cart_items = ( !empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
				if ( !empty($cart_items) ) {
					if ( !empty($cart_content['coupon_id']) ) {
						$coupon_title = get_the_title( $cart_content['coupon_id']);
						$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
					}
					$order_total_before_discount = ( !empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
					$shipping_cost_ati = ( !empty($cart_content['order_shipping_cost']) ) ? $cart_content['order_shipping_cost'] : 0;
					$total_ati  = $total_cart = ( !empty($cart_content['order_amount_to_pay_now']) ) ? $cart_content['order_amount_to_pay_now'] : 0;
					
					ob_start();
					if( !empty($type) && $type == 'fixed' ) {
						require( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "mini-cart/fixed-mini-cart", "content") );
					}
					else {
						require( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "mini-cart/mini-cart", "content") );
					}
					$output = ob_get_contents();
					ob_end_clean();
				}
				else {
					$output = '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
				}
			}
			else {
				$output = '<div class="wps-alert-info">' . __( 'Your cart is empty', 'wpshop' ).'</div>';
			}
			return $output;
		}
		
		/** Resume Cart **/
		function display_resume_cart() {
			$cart_summary_content = self::resume_cart_content();
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "resume-cart/resume-cart") );
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
		
		/** Resume cart Content **/
		function resume_cart_content() {
			$output = '';
			$currency = wpshop_tools::wpshop_get_currency( false );
			$cart_content = ( !empty($_SESSION) && !empty($_SESSION['cart']) ) ? $_SESSION['cart'] : array();
			if ( !empty($cart_content) ) {
				$cart_items = ( !empty($cart_content['order_items']) ) ? $cart_content['order_items'] : array();
				if ( !empty($cart_items) ) {
					if ( !empty($cart_content['coupon_id']) ) {
						$coupon_title = get_the_title( $cart_content['coupon_id']);
						$coupon_value = wpshop_tools::formate_number( $cart_content['order_discount_amount_total_cart'] );
					}
					$order_total_before_discount = ( !empty($cart_content['order_grand_total_before_discount']) ) ? $cart_content['order_grand_total_before_discount'] : 0;
					$shipping_cost_ati = ( !empty($cart_content['order_shipping_cost']) ) ? $cart_content['order_shipping_cost'] : 0;
					$total_ati  = $total_cart = ( !empty($cart_content['order_amount_to_pay_now']) ) ? $cart_content['order_amount_to_pay_now'] : 0;
					ob_start();
					require_once( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "resume-cart/resume-cart", "content") );
					$output = ob_get_contents();
					ob_end_clean();
				}
				else {
					$resume_cart_body = '<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
				}
			}
			else {
				$resume_cart_body ='<div class="wps-alert-info">' .__( 'Your cart is empty', 'wpshop' ).'</div>';
			}
			return $output;
		}

		/**
		 * Count total items in cart
		 * @param array cart
		 * @return int total items
		 */
		function total_cart_items( $cart_items ) {
			$count = 0;
			if( !empty($cart_items) && is_array( $cart_items )) {
				foreach( $cart_items as $cart_item ) {
					$count += $cart_item['item_qty'];
				}
			}
			return $count;
		}
		
		/** Ajax action to reload cart **/
		function wps_reload_cart() {
			$result = self::cart_content();
			echo json_encode( array( 'response' => $result) );
			die();
		}
		
		/** Ajax action to reload mini cart */
		function wps_reload_mini_cart() {
			$result = self::mini_cart_content( sanitize_title( $_POST['type']) );
			$count_items = ( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items'])  ) ? self::total_cart_items( $_SESSION['cart']['order_items'] ) : 0;
			$free_shipping_alert = wpshop_tools::create_custom_hook('wpshop_free_shipping_cost_alert');
			
			echo json_encode( array( 'response' => $result, 'count_items' => $count_items, 'free_shipping_alert' => $free_shipping_alert) );
			die();
		}
		
		/**
		 * Display the number of products in cart
		 * @return string
		 */
		function display_wps_numeration_cart() {
			$cart_items = ( !empty($_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) ? $_SESSION['cart']['order_items'] : array();
			$total_cart_item = self::total_cart_items( $cart_items );
			
			ob_start();
			require_once(wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "cart/numeration-cart") );
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
		
		/** Ajax action to reload summary cart */
		function wps_reload_summary_cart() {
			$result = self::resume_cart_content();
			echo json_encode( array( 'response' => $result) );
			die();
		}
		
		/** Display Apply Coupon Interface **/
		function display_apply_coupon_interface() {
			$output = '';
			if ( !empty( $_SESSION) && !empty($_SESSION['cart']) && !empty($_SESSION['cart']['order_items']) ) {
				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_CART_DIR, $this->template_dir, "frontend", "coupon/apply_coupon") );
				$output = ob_get_contents();
				ob_end_clean();
			}
			return $output;
		}
		
		/** Ajax action to apply coupon **/
		function wps_apply_coupon() {
			$status = false; $response = '';
			$coupon = ( !empty($_POST['coupon_code']) ) ? wpshop_tools::varSanitizer( $_POST['coupon_code']) : null;
			if( !empty($coupon) ) {
				$wps_coupon_ctr = new wps_coupon_ctr();
				$result = $wps_coupon_ctr->applyCoupon($_REQUEST['coupon_code']);
				if ($result['status']===true) {
					$order = wpshop_cart::calcul_cart_information(array());
					wpshop_cart::store_cart_in_session($order);
					$status = true;
					$response = '<div class="wps-alert-success">' .__( 'The coupon has been applied', 'wpshop' ). '</div>';
				}
				else {
					$response = '<div class="wps-alert-error">' .$result['message']. '</div>';
				}
			}
			else {
				$response = '<div class="wps-alert-error">'.__( 'A coupon code is required', 'wpshop'). '</div>';
			}
			echo json_encode( array( 'status' => $status, 'response' => $response ) );
			die();
		}
		
		/**
		 * AJAX - Pass to step two in the Checkout tunnel
		 */
		function wps_cart_pass_to_step_two() {
			$status = false; $response = '';
			$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
			if( !empty($checkout_page_id) ) {
				$permalink_option = get_option( 'permalink_structure' );
				$step = ( get_current_user_id() != 0 ) ?  3 : 2;
				$response = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step='.$step;
				$status = true;
			}
			else {
				$response = '<div class="wps-alert-error">' .__( 'An error was occured, please retry later or contact the website administrator', 'wpshop' ). '</div>';
			}
			echo json_encode( array( 'status' => $status, 'response' => $response));
			die();
		}
	
		/**
		 * AJAX - Empty the cart
		 */
		function wps_empty_cart() {
			wpshop_cart::empty_cart();
			echo json_encode( array( 'status' => true) );
			die();
		}	
	}
}
if ( class_exists('wps_cart') ) {
	$wps_cart = new wps_cart();
}