<?php
class wps_customer_mdl {
	function __construct() {
		
	}
	
	/**
	 * Return users list
	 * @return array
	 */
	function getUserList() {
		global $wpdb;
	
		$query = "SELECT USERS.ID, USERS.user_login, USERS.user_email FROM " . $wpdb->users . " AS USERS";
		$userList = $wpdb->get_results($query);
	
		return $userList;
	}
}