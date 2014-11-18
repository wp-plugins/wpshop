<?php
	/** Get hourly orders ***/
	class wps_statistics_mdl {
		function __construct() {
		
		}
		
		/**
		 * Returns Best sales products statistics data on a period
		 * @param string $begindate
		 * @param string $enddate
		 * @return array
		 */
		function wps_best_sales_datas( $begindate, $enddate) {
			global $wpdb;
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begindate.' 00:00:00', $enddate.' 23:59:59' );
			$orders = $wpdb->get_results( $query );
			if ( !empty($orders) ) {
				$products = array();
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					if( !empty($order_meta) && !empty($order_meta['order_items']) ) {
						foreach( $order_meta['order_items'] as $item_id => $item ) {
							if( !empty($item) && !empty($item['item_qty']) && !empty($item['item_id'])  ) {
								if( !empty($products[ $item['item_id'] ]) ) {
									$products[ $item['item_id'] ] += $item['item_qty'];
								}
								else {
									$products[ $item['item_id'] ] = $item['item_qty'];
								}
							}
						}
					}
				}
				return $products;
			}
		}
		
		/**
		 * Return most viewed products statistics
		 * @return array
		 */
		function wps_most_viewed_products_datas() {
			global $wpdb;
			$output = '';
			$query = $wpdb->prepare( 'SELECT post_id, meta_value FROM '. $wpdb->postmeta .', ' .$wpdb->posts. ' WHERE post_id = ID AND post_status = %s AND meta_key = %s ORDER BY CAST( meta_value AS SIGNED ) DESC LIMIT 8', 'publish', '_wpshop_product_view_nb');
			$products = $wpdb->get_results( $query );
			return $products;
		}
		
		/**
		 * Orders by month
		 * @return array
		 */
		function wps_orders_by_month() {
			$output = '';
			$order_recap = array();
			$orders = get_posts( array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'post_status' => 'publish') );
			if ( !empty($orders) ) {
				foreach( $orders as $order ) {
					$order_year = date( 'Y', strtotime( $order->post_date) );
					$order_month = date( 'n', strtotime( $order->post_date) );
					if ( empty($order_recap[$order_year]) ) {
						$order_recap[$order_year] = array();
					}
					$order_meta =  get_post_meta( $order->ID, '_order_postmeta', true );
					if ( !empty($order_meta) && !empty($order_meta['order_grand_total']) ) {
						if ( empty($order_recap[$order_year][ $order_month ]) ) {
							$order_recap[$order_year][ $order_month ] = $order_meta['order_grand_total'];
						}
						else {
							$order_recap[$order_year][ $order_month ] += $order_meta['order_grand_total'];
						}
					}
				}
			return $order_recap;
	
			}
		}
		
		/**
		 * Orders Status
		 * @return Array
		 */
		function wps_order_status( $begin_date, $end_date ) {
			global $wpdb;
			$output = '';
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begin_date.' 00:00:00', $end_date.' 23:59:59' );
			$orders = $wpdb->get_results( $query );
			if ( !empty($orders) ) {
				$orders_status = array();
				/** Collect datas **/
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					if ( !empty($order_meta) && !empty($order_meta['order_status']) ) {
						if ( !empty($orders_status[ $order_meta['order_status'] ]) ) {
							$orders_status[ $order_meta['order_status'] ]++;
						}
						else {
							$orders_status[ $order_meta['order_status'] ] = 1;
						}
					}
				}
			return $orders_status;
			}
		}
		
		/**
		 * Get Best customers between two dates
		 * @param string $begin_date
		 * @param string $end_date
		 * @return array
		 */
		function wps_best_customers( $begin_date, $end_date ) {
				global $wpdb;
		
				$output = '';
				$customer_recap = array();
		
				$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begin_date.' 00:00:00', $end_date.' 23:59:59' );
				$orders = $wpdb->get_results( $query );
		
				if ( !empty($orders) ) {
					foreach( $orders as $order ) {
						$order_meta = get_post_meta( $order->ID, '_order_postmeta', true);
						if ( !empty($order_meta) && !empty($order_meta['customer_id']) && !empty($order_meta['order_grand_total']) ) {
							/** Check if user is administrator **/
							$user_def = get_user_by( 'id', $order_meta['customer_id'] );
							$wps_statistics_exclude_customer = get_user_meta( $order_meta['customer_id'], 'wps_statistics_exclude_customer', true );
							$excluded_from_statistics = ( !empty($wps_statistics_exclude_customer) ) ? true : false;
	
	
							if ( !empty($user_def) && !empty($user_def->caps) && is_array($user_def->caps) && array_key_exists( 'customer', $user_def->caps) && $excluded_from_statistics === false ) {
								if ( empty($customer_recap[ $order_meta['customer_id'] ] ) ) {
									$customer_recap[ $order_meta['customer_id'] ] = $order_meta['order_grand_total'];
								}
								else {
									$customer_recap[ $order_meta['customer_id'] ] += $order_meta['order_grand_total'];
								}
							}
						}
					}
				return $customer_recap;
			}
		}
		
		
		
		
		function wps_get_orders_by_hours( $begindate, $enddate, $choosenday = '', $ajax_origin = false ) {
			global $wpdb;
			$begin_date = new DateTime( $begindate );
			$enddate = new DateTime( $enddate );
			$begindate = $begin_date->format('Y-m-d H:i:s');
			$enddate = $enddate->format('Y-m-d H:i:s');
			$query = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = '%s' AND post_date BETWEEN '%s' AND '%s'", "wpshop_shop_order", $begindate, $enddate);
			$resultarray = $wpdb->get_results($query);
			$datadate = array();
			foreach ($resultarray as $array){
				$date = new DateTime( $array->post_date );
				$day = $date->format('l');
				$day = strtolower($day);
				$choosenday = strtolower($choosenday);
				if( empty($choosenday) || ( !empty($choosenday) && $choosenday ==  $day ) ) {
					$hour = $date->format('G');
					if ( empty($datadate[$day])){
						if	(empty($datadate[$day][$hour])){
							$datadate[$day][$hour] = 1;	
						}
						else {
							$datadate[$day][$hour] += 1;
						}
					}
					else {
						if	(empty($datadate[$day][$hour])){
							$datadate[$day][$hour] = 1;	
						}
						else {
							$datadate[$day][$hour] += 1;
						}
					}
				}
			}
			return  $datadate;
		}
	}
	

// 	/**
// 	 * Get Customers account creation by month
// 	 * @return string
// 	 */
// 	function wps_customers_month() {
// 		$output = '';
// 		$customers_recap = array();
// 		$count_users = 0;
// 		$users = get_users();
// 		if ( !empty($users) ) {
// 			foreach( $users as $user ) {
// 				$user_year = date( 'Y', strtotime( $user->data->user_registered) );
// 				$user_month = date( 'n', strtotime( $user->data->user_registered) );
// 				if ( empty($customers_recap[$user_year]) ) {
// 					$customers_recap[$user_year] = array();
// 				}
// 				$user_role = $user->roles;
// 				if ( !empty($user_role) && in_array( 'customer', $user_role ) ) {
// 					if ( empty($customers_recap[$user_year][ $user_month ]) ) {
// 						$customers_recap[$user_year][ $user_month ] = 1;
// 					}
// 					else {
// 						$customers_recap[$user_year][ $user_month ]++;
// 					}
// 				}
// 			}
// 		return $customers_recap;
// 		}
// 	}
	