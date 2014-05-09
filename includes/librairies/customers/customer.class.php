<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


class wpshop_customer{

	/**
	* Constructor of the class
	*/
	function __construct() {
		if ( !empty($_GET['download_users']) ) {
			wpshop_customer::download_newsletters_users( $_GET['download_users'] );
		}
	}

	function getUserList() {
		global $wpdb;

		$query = "SELECT USERS.ID, USERS.user_login, USERS.user_email FROM " . $wpdb->users . " AS USERS";
		$userList = $wpdb->get_results($query);

		return $userList;
	}

	/**
	* Return a list of customer
	*/
	function custom_user_list($customer_list_params = array('name'=>'user[customer_id]', 'id'=>'user_customer_id'), $selected_user = "", $multiple = false, $disabled = false) {
		$content_output = '';

		// USERS
		$users = wpshop_customer::getUserList();
		$select_users = '';
		foreach($users as $user) {
			if ($user->ID != 1) {
				$select_users .= '<option value="'.$user->ID.'"' . ( ( !$multiple ) && ( $selected_user == $user->ID ) ? ' selected="selected"' : '') . ' >'.$user->user_login.' ('.$user->user_email.')</option>';
			}
		}
		$content_output = '
		<select name="' . $customer_list_params['name'] . '" id="' . $customer_list_params['id'] . '" data-placeholder="' . __('Choose a customer', 'wpshop') . '" class="chosen_select"' . ( $multiple ? ' multiple="multiple" ' : '') . '' . ( $disabled ? ' disabled="disabled" ' : '') . '>
			<option value="0" ></option>
			'.$select_users.'
		</select>';

		return $content_output;
	}

	/**
	 *
	 */
	public static function customer_action_on_plugin_init() {
		global $wpdb;
		$user_meta_for_wpshop = array('metaboxhidden_'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

		/*	Get user list from user meta	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->users, '');
		$user_list = $wpdb->get_results($query);

		/*	Get the different meta needed for user in wpshop	*/
		foreach ($user_list as $user) {
			/*	Check if meta exist for each user	*/
			foreach($user_meta_for_wpshop as $meta_to_check){
				$query = $wpdb->prepare("SELECT meta_value FROM ".$wpdb->usermeta." WHERE user_id=%d AND meta_key=%s", $user->ID, $meta_to_check);
				$meta_value = $wpdb->get_var($query);
				if(empty($meta_value)){
					update_user_meta($user->ID, $meta_to_check, unserialize(WPSHOP_PRODUCT_HIDDEN_METABOX));
				}
			}
		}
		return;
	}

	
	function download_newsletters_users( $users_preference_indicator ) {
		require (ABSPATH . WPINC . '/pluggable.php');
		$current_user_def = wp_get_current_user();
		if( !empty($current_user_def) && $current_user_def->ID != 0 && array_key_exists('administrator', $current_user_def->caps) && is_admin() ) {
			$users = get_users();
			$users_array = array();
			if ( !empty( $users ) ) {
				foreach( $users as $user ) {
					$user_preference = get_user_meta( $user->ID, 'user_preferences', true );
					if(  !empty($user_preference) && !empty($user_preference[ $users_preference_indicator ]) ) {
						$tmp_array = array();
						$tmp_array['name'] = get_user_meta( $user->ID, 'last_name', true );
						$tmp_array['first_name'] = get_user_meta( $user->ID, 'first_name', true );
						$tmp_array['email'] = $user->user_email;
							
						$users_array[] = $tmp_array;
					}
				}
			}
			$fp = fopen('newsletter_contacts_' .$users_preference_indicator. '.csv', 'w');
			$filename = 'newsletter_contacts_' .$users_preference_indicator. '.csv';
			foreach ($users_array as $fields) {
				fputcsv($fp, $fields);
			}
			
			fclose($fp);
			header("Content-type: application/force-download");
			header("Content-Disposition: attachment; filename=".$filename);
			readfile($filename);
			
			unlink( $filename );
		}
	}
	
	
}