<?php
class wps_coupon_model {
	function __construct() {
		
	}
	
	function get_coupons() {
		$coupons = get_posts( array( 'post_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_COUPON, 'post_status' => 'publish' ) );
		return $coupons;
	}
}