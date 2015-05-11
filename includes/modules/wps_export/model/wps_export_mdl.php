<?php
class wps_export_mdl {

	/**
	 * Get customers with a term: all, newsletters_site, newsletters_site_partners, date(between 2 dates) or if order is higher than.
	 * @param string $term
	 * @param price/date $dt1 may be null
	 * @param date $dt2 may be null
	 * @return array
	 */
	function get_customers($term, $dt1=null, $dt2=null) {
		global $wpdb;
		$users;
		switch ($term) {
			case 'users_all':
				$query = $wpdb->prepare( "SELECT ID AS USER_ID, '' AS POST_ID FROM {$wpdb->users} WHERE %d", 1);
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
			case 'customers_all':
				$query = $wpdb->prepare( "SELECT us.ID AS USER_ID, GROUP_CONCAT( ps.ID ) AS POST_ID FROM {$wpdb->users} us JOIN {$wpdb->posts} ps ON us.ID = ps.post_author AND ps.post_type = %s GROUP BY USER_ID", 'wpshop_shop_order' );
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
			case 'newsletters_site':
				$query = $wpdb->prepare( "SELECT user_id AS USER_ID, GROUP_CONCAT( ID ) AS POST_ID FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') ) GROUP BY USER_ID", 'wpshop_shop_order', 'user_preferences', 's:16:"newsletters_site";i:1;', 's:16:"newsletters_site";b:1;' );
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
			case 'newsletters_site_partners':
				$query = $wpdb->prepare( "SELECT user_id AS USER_ID, GROUP_CONCAT( ID ) AS POST_ID FROM {$wpdb->usermeta} JOIN {$wpdb->posts} ON post_author = user_id AND post_type = %s WHERE meta_key = %s AND ( meta_value LIKE ('%%%s%%') || meta_value LIKE ('%%%s%%') ) GROUP BY USER_ID", 'wpshop_shop_order', 'user_preferences', 's:25:"newsletters_site_partners";i:1;', 's:25:"newsletters_site_partners";b:1;' );
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
			case 'date':
				$query = $wpdb->prepare( "SELECT us.`ID` AS USER_ID, GROUP_CONCAT( ps.`ID` ) AS POST_ID FROM {$wpdb->users} us JOIN {$wpdb->posts} ps ON `post_author` = us.`ID` AND `post_type` = %s WHERE `user_registered` >= %s AND `user_registered` <= %s GROUP BY USER_ID", 'wpshop_shop_order', date("Y-m-j", strtotime($dt1)), date("Y-m-j", strtotime("+1 day", strtotime($dt2))) );
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
			case 'orders':
				$query = $wpdb->prepare( "SELECT us.ID AS USER_ID, GROUP_CONCAT( ps.ID ) AS POST_ID FROM {$wpdb->users} us JOIN {$wpdb->posts} ps ON us.ID = ps.post_author AND ps.post_type = %s GROUP BY USER_ID", 'wpshop_shop_order' );
				$list_users = $wpdb->get_results($query, OBJECT);
				break;
		}
		$users_array = array();
		if ( !empty( $list_users ) ) {
			$billing_address_indicator = get_option('wpshop_billing_address');
			$billing_address_indicator = $billing_address_indicator['choice'];
			foreach( $list_users as $user_post ) {
				if($term == 'orders') {
					$vuser = false;
				}
				$user = get_userdata( $user_post->USER_ID );
				$tmp_array = array();
				$last_name = get_user_meta( $user->ID, 'last_name', true );
				$first_name = get_user_meta( $user->ID, 'first_name', true );
				if( empty($last_name) )
					$last_name = $user->display_name;
				if( empty($first_name) )
					$first_name = '-';
				$tmp_array['name'] = $last_name;
				$tmp_array['first_name'] = $first_name;
				$tmp_array['email'] = $user->user_email;
				$tmp_array['tel'] = '';
				$result = wps_address::get_addresses_list($user->ID);
				if( !empty($result) && !empty($result[$billing_address_indicator]) ) {
					foreach($result[$billing_address_indicator] as $address_id => $address_data) {
						if( !empty($address_data['phone']) ) {
							$tmp_array['tel'] = $address_data['phone'];
						}
					}
				}
				$tmp_array['registered'] = date('d M Y H:i', strtotime($user->user_registered));
				$posts_id = explode(',', $user_post->POST_ID);
				$orders = get_posts( array(
						'include'			=>	$posts_id,
						'post_type'			=>	'wpshop_shop_order',
						'posts_per_page'	=>	-1
					));
				foreach( $orders as $order ) {
					if($term == 'orders') {
						$command = get_post_meta( $order->ID, '_order_postmeta', true );
						if( ( !empty($dt1) && !empty($command['order_grand_total']) && $command['order_grand_total'] >= $dt1 ) || ( !empty($dt2) && $dt2===true && $command['order_payment']['customer_choice']['method'] == 'free' ) ) {
							$vuser = true;
						}
					}
				}
				if($term != 'orders') {
					$users_array[] = $tmp_array;
				} elseif($vuser) {
					$users_array[] = $tmp_array;
				}
			}
		}
		return $users_array;
	}

	/**
	 * Get orders between 2 dates.
	 * @param string $term
	 * @param date $dt1
	 * @param date $dt2
	 * @return array
	 */
	function get_orders($term, $dt1=null, $dt2=null) {
		$commands_array = array();
		$orders = get_posts( array(
			'post_type' => 'wpshop_shop_order',
			'posts_per_page' => -1
		) );
		if ( !empty( $orders ) ) {
			foreach( $orders as $order ) {
				if( !empty($dt1) && strtotime($dt1) <= strtotime($order->post_date) && strtotime($order->post_date) <= strtotime("+1 day", strtotime($dt2)) ) {
					$user = get_userdata($order->post_author);
					$tmp_array = array();
					$tmp_array['name'] = get_user_meta( $user->ID, 'last_name', true );
					$tmp_array['first_name'] = get_user_meta( $user->ID, 'first_name', true );
					$tmp_array['email'] = $user->user_email;
					$tmp_array['tel'] = '';
					$order_info = get_post_meta( $order->ID, '_order_info', true);
					if( !empty($order_info['billing']['address']['phone']) ) {
						$tmp_array['tel'] .= $order_info['billing']['address']['phone'];
					}
					$tmp_array['date_order'] = '';
					if( !empty($order->post_date) ) {
						$tmp_array['date_order'] = mysql2date( get_option( 'date_format' ), $order->post_date, true );
					}
					$commands_array[] = $tmp_array;
				}
			}
		}
		return $commands_array;
	}

}