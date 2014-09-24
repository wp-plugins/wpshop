<?php
class wps_orders_mdl{
	
	function __construct() {
		
	}
	
	function get_customer_orders( $customer_id ) {
		global $wpdb;
		$query = $wpdb->prepare( 'SELECT * FROM '.$wpdb->posts. ' WHERE post_author = %d AND post_type = %s ORDER BY ID DESC', $customer_id, WPSHOP_NEWTYPE_IDENTIFIER_ORDER );
		$orders = $wpdb->get_results( $query );
		
		return $orders;
	}
}