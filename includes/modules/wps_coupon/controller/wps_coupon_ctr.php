<?php
class wps_coupon_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_COUPON_DIR;
	
	function __construct() {
		$this->template_dir = WPS_COUPON_PATH . WPS_COUPON_DIR . "/templates/";
		add_shortcode( 'wps_coupon', array($this, 'display_coupons') );
	}
	
	/** Load templates **/
	function get_template_part( $side, $slug, $name=null ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
	
		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $this->plugin_dirname . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
	
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
	
				if ( file_exists($this->template_dir . $template_name)) {
					$path = $this->template_dir . $template_name;
					break;
				}
			}
		}
	
		return $path;
	}

	function display_coupons() {
		$coupons_mdl = new wps_coupon_model();
		$coupons = $coupons_mdl->get_coupons();
		$output = $coupons_rows = '';
				
		if( !empty($coupons) ) {
			foreach( $coupons as $coupon ) {
				$coupon_individual_usage = get_post_meta( $coupon->ID, 'wpshop_coupon_individual_use', true );
				if( empty($coupon_individual_usage) || ( !empty($coupon_individual_usage) && in_array( get_current_user_id(), $coupon_individual_usage) ) ) {
					$coupon_code = get_post_meta( $coupon->ID, 'wpshop_coupon_code', true );
					$coupon_value = get_post_meta( $coupon->ID, 'wpshop_coupon_discount_value', true );
					$discount_type = get_post_meta( $coupon->ID, 'wpshop_coupon_discount_type', true );
					$coupon_date = get_post_meta( $coupon->ID, 'wpshop_coupon_expiry_date', true);
					$coupon_validity_date = ( !empty($coupon_date) ) ? $coupon_date : __( 'No validity date', 'wpshop');
					$coupon_value .= ( !empty($discount_type) && $discount_type == 'amount') ? wpshop_tools::wpshop_get_currency( false ) : '%';
					ob_start();
					require( $this->get_template_part( "frontend", "coupon") );
					$coupons_rows .= ob_get_contents();
					ob_end_clean();
				}
			}
			ob_start();
			require( $this->get_template_part( "frontend", "coupons") );
			$output .= ob_get_contents();
			ob_end_clean();
			
		}
		return $output;
	}		

}