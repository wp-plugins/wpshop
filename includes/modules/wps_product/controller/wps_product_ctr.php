<?php
class wps_product_ctr {

	function __construct() {
		add_shortcode( 'wps_product_caracteristics', array( $this, 'display_product_caracteristics_tab' ) );
		add_shortcode( 'wps_product_discount_chip', array( $this, 'display_discount_chip' ) );
	}

	/**
	 * CORE - Install all extra-modules in "Modules" folder
	 */
	function install_modules() {
		/**	Define the directory containing all exrta-modules for current plugin	*/
		$module_folder = WPS_PRODUCT_PATH . '/modules/';

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' ) {
					$child_folder_content = scandir( $module_folder . $folder );
					if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						include( $f );
					}
				}
			}
		}
	}

	/**
	 * Display Product's caracteristics tab in complete product sheet
	 * @param array $args
	 * @return string
	 */
	function display_product_caracteristics_tab( $args ) {
		$output = '';
		if( !empty($args) && !empty($args['pid']) ) {
			$wps_product_mdl = new wps_product_mdl();
			$product_atts_def = $wps_product_mdl->get_product_atts_def( $args['pid'] );
			if( !empty($product_atts_def) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "frontend", "product_caracteristics_tab") );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
	}

	/**
	 * Display Discount Chip
	 * @param array $args
	 * @return string
	 */
	function display_discount_chip( $args ) {
		$output = '';
		if( !empty($args) && !empty($args['pid']) ) {
			$wps_price = new wpshop_prices();
			$discount_data = $wps_price->check_discount_for_product( $args['pid'] );
			if( !empty($discount_data) ) {
				ob_start();
				require( wpshop_tools::get_template_part( WPS_PRODUCT_DIR, WPS_PRODUCT_TEMPLATES_MAIN_DIR, "frontend", "product_discount_chip") );
				$output = ob_get_contents();
				ob_end_clean();
			}
		}
		return $output;
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
		// Checking if combined variation ID exist and it is a simple option
		if( !empty($combined_variation_id) && ( strpos($combined_variation_id, '__') !== false ) ) {
			$var_id = explode( '__', $combined_variation_id);
			$combined_variation_id = $var_id[1];
		}


		if ( !empty($combined_variation_id) ) {

			$variation_metadata = get_post_meta( $combined_variation_id, '_wpshop_product_metadata', true );
			if ( isset($variation_metadata['product_stock']) ) {
				$product_id = $combined_variation_id;
			}
		}
		$product_data = wpshop_products::get_product_data($product_id);

		if(!empty($product_data)) {
			$manage_stock = !empty($product_data['manage_stock']) ? $product_data['manage_stock'] : '';

			$product_post_type = get_post_type( $product_id );

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

}