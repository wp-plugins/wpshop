<?php

		function get_post_date(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT (post_date) FROM {$wpdb->posts} WHERE post_type = %s ORDER BY post_date DESC", "wpshop_shop_order");
			$last_order_date = $wpdb->get_results($query);
			return ($last_order_date);
		}
		
		function get_creation_date(){
			global $wpdb;
			$query = $wpdb->prepare("SELECT DISTINCT (post_date) FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s ", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			$creation_date = $wpdb->get_results($query);
			return ($creation_date);
		}