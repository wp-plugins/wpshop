<?php
/** 
 * All the databases functions for Wps_alert
 * @package File
 */

 
 /**
  * Class for database functions (Model)
  * @package Class
  */
	class wps_alert_mdl{
		/** 
		 * Get all order's date 
		 * @return array
		 */
		function get_post_date(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT (post_date) FROM {$wpdb->posts} WHERE post_type = %s ORDER BY post_date DESC", "wpshop_shop_order");
			$last_order_date = $wpdb->get_results($query);
			return ($last_order_date);
		}
		
		
		/** 
		 * Get order creation date
		 * @return array
		 */
		function get_creation_date(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT (post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s ", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			$creation_date = $wpdb->get_results($query);
			return ($creation_date);
		}
		
		/** 
		 * Get users emails
		 * @return array
		 */
		function get_user_mail(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT (user_email) FROM {$wpdb->users} ", '');
			$result = $wpdb->get_results($query);
			return($result);
		}
		
		/** 
		 * Get only admins emails
		 * @param array $userinfo All the selected user's informations
		 * @return array
		 */
		function get_admin_mail($userinfo){
			global $wpdb;
			$userinfo = $userinfo->user_email;
			$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->users} WHERE user_email = %s", $userinfo);
			$result = $wpdb->get_results($query);
			$usermeta = get_user_meta($result[0]->ID);
			return($usermeta[wp_capabilities][0]);
		}

		/** 
		 * Get the time average between orders
		 * @return integer
		 */
		function get_mail_interval(){
			$currentvalue = 24;
			$check = get_option('wpshop_alert_interval');
			if ($check == false){
				$check = update_option('wpshop_alert_interval', $currentvalue);
			}
			else{
				$currentvalue = $check;
			}
			return ($currentvalue);
		}
		
		/** 
		 * Get the duration choosen by the user to do the order average 
		 * @return integer
		 */
		function get_choosen_average(){
			$currentvalue = 168;
			$check = get_option('wpshop_alert_choosen_interval');
			if ($check == false){
				$check = update_option('wpshop_alert_choosen_interval', $currentvalue);
			}
			else{
				$currentvalue = $check;
			}
			return ($currentvalue);
		}
	}