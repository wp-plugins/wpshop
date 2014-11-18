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


}