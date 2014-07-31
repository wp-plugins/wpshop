<?php
if (is_admin()) require_once(ABSPATH . 'wp-includes/pluggable.php');
class exportclientctr{
	function __construct(){
		if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export1') {
			checkuserlist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export2') {
			checkalluserlist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export3') {
			exportorders();
		}		
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export4') {
			bestuserslist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export5') {
			ordernotcomplete();
		}
		add_action('add_meta_boxes', 'initmetabox');
		function initmetabox(){
			add_meta_box('exportmeta', 'Exportation Client', 'funcmeta', 'post');
		}
		add_action( 'admin_enqueue_scripts', array(&$this, 'scriptinclude'));
		DEFINE('WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE_OBJECT', __('Your order has been updated', 'wpshop'));
		DEFINE('WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE', __('Hello [customer_first_name] [customer_last_name], your order ([order_key]) has just been updated. A comment has been added:<br /><br />"[message]".<br /><br /> Thank you for your loyalty. Have a good day.', 'wpshop'));
	}
	function scriptinclude() {
		$path = plugin_dir_url( __FILE__ ).'assets/js/changebox.js';
		$path = str_replace('controller/', '', $path);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('wps_changebox', $path);
	}
}

