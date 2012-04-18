<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
require_once('../../wpshop.php');
require_once(WPSHOP_INCLUDES_DIR . 'include.php');

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

$q = strtolower($_GET["term"]);
if (!$q) return;

$items = array();

$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->users);
$user_list = $wpdb->get_results($query);
$output_search = '';
if(!empty($user_list)){
	foreach($user_list as $user){
		$user_info = get_userdata($user->ID);
		$user_name = 'U' . $user->ID . ' - ' . (isset($user_info->user_lastname) && ($user_info->user_lastname != '') ? $user_info->user_lastname . ' ' : '') . (isset($user_info->user_firstname) && ($user_info->user_firstname != '') ? $user_info->user_firstname : $user_info->user_nicename);
		$items[$user_name] = $user->ID;
	}
}

$found_result = false;
if(!empty($items)){
	$output_search = '[';
	foreach ($items as $key=>$value){
		if (strpos(strtolower($key), $q) !== false){
			// echo "$key|$value\n";
			$found_result = true;

			$output_search .= '{"id": "' . $value . '", "label": "' . $key . '", "value": "' . $value . '"}, ';
		}
	}
	$output_search = substr($output_search, 0, -2) . ']';
}

echo $output_search;
?>