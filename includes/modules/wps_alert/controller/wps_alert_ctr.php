<?php

/**
 * Main functions of Wps_Alert
 * @package File
 */
 
 /**
  * Controller class for wps_alert
  * @package Class
  */
class wps_alert_ctr{

	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_ALERT_DIR;

	/**
	 * Constructor
	 */
	function __construct(){
		/** Load the translation **/
		/** Add and show plugin menu **/
		add_action( 'admin_init', array(&$this, 'register_mysettings'));
		add_action( 'admin_menu', array( &$this, 'alert_menu_link'));
		/** Template Load **/
		$this->template_dir = WPS_ALERT_PATH . WPS_ALERT_DIR . "/templates/";
		add_thickbox();
		add_action( 'admin_enqueue_scripts', array( &$this, 'loadcss' ) );
		/** Template is loaded **/
	}

	
	/** 
	 * Register settings to get a settings page with Options API 
	 */
	function register_mysettings() {
		//register our settings
		add_settings_section('baw-settings-group', 'alert_section_setting', 'eg_setting_section_callback_function', 'Alerts Settings');
		add_settings_field('baw-settings-group', 'alert_field_setting', 'eg_setting_callback_function', 'Alerts Settings', 'alert_section_setting');
		register_setting( 'baw-settings-group', 'wpshop_alert_choosen_interval' );
		register_setting( 'baw-settings-group', 'wpshop_alert_interval' );
	}

	/**
	 * Callback function for setting page
	 */
	function eg_setting_section_callback_function(){
		
	}

	/**
	 * Callback function for setting page
	 */
	function eg_setting_callback_function(){
		
	}
	
	/** 
	 * Show the Settings page 
	 */
	function baw_settings_page() {
		require( $this->get_template_part("frontend", "menu_link_tpl.php") );
	}

	/** 
	 * Create a link in the admin menu 
	 */
	function alert_menu_link(){
		if (function_exists('add_options_page')) {
			$plugin_page_options = add_options_page('Alerts Settings', __('[WPSHOP] Alert Settings', 'wpshop') , 'administrator', WPS_ALERT_PATH. WPS_ALERT_DIR. '/templates/frontend/menu_link_tpl.php');
		}
	}

	/** 
	 * Contain the code of the plugin page 
	 */
	function wps_alert_link(){
		if (!current_user_can('administrator'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}	
	}
	
	/** 
	 * Function to load backend css 
	 */
	function loadcss() {
	$pathcss = WPS_ALERT_URL. WPS_ALERT_DIR. '/assets/css/backend.css';
	wp_register_style('wps_alert_backend_css', $pathcss);
	wp_enqueue_style('wps_alert_backend_css');
	}
	
	/** 
	 * Load templates 
	 * @param string $side Path
	 * @param string $slug The file name
	 * @param string $name Unused
	 * @return string
	 */
	function get_template_part( $side, $slug, $name=null ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
	
		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $this->plugin_dirname . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
		
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
				if ( file_exists($this->template_dir . $template_name)) {
					$path = $this->template_dir . $template_name;
					break;
				}
			}
		}
	
		return $path;
	}

	/** 
	 * Check and show if there is an alert 
	 * @return string
	 */ 
	function check_last_order(){
	$wps_alert_mdl = new wps_alert_mdl();
		$average_order = $this->get_order_average();
		$last_order_date = $wps_alert_mdl->get_post_date();
		if (!empty($last_order_date[0])){
			$last_order_date = strtotime($last_order_date[0]->post_date);
			if (get_option('last_order_opt') == false){
				$timestamp = time();
				update_option('last_order_opt', $timestamp);
			}
			else {
				$timestamp = time();
				$last_check_date = get_option('last_order_opt');
			}
			if ($timestamp - (3600 * $wps_alert_mdl->get_choosen_average()) > $last_order_date)
				$output .= $this->alert_detected($last_order_date, $last_check_date, $average_order);
			else
				$output .= $this->no_alert_detected($datesaved, $last_order_date);
		}
		else{
			ob_start();
			require( $this->get_template_part("frontend", "no_order_tpl") );
			$output .= ob_get_contents();
			ob_end_clean();	
		}
		return ($output);
	}

	/** 
	 * Code executed when an alert is detected 
	 * @param string $last_order_date The date of the last order
	 * @param string $last_check_date The date of the last order check
	 * @param integral $average_order Order average
	 * @return string
	 */
	function alert_detected($last_order_date, $last_check_date, $average_order){
	$wps_alert_mdl = new wps_alert_mdl();
		$check = 0;
		$timestamp = time();
		$alert_interval = $wps_alert_mdl->get_mail_interval();
		$last_order_date = date('Y-m-d H:i:s', $last_order_date);
		$datesaved = mysql2date('d / m / Y', $last_order_date); 
		ob_start();
		require( $this->get_template_part("frontend", "alert_tpl") );
		$output .= ob_get_contents();
		ob_end_clean();
		if (($timestamp - ($alert_interval * 3600)) > $last_check_date){
			$this->sendalertmail();
			update_option('last_order_opt', $timestamp);
		}
		return ($output);
	}

	/** 
	 * Code executed when there is no alert
	 * @param string $last_order_date The date of the last order
	 * @param string $datesaved The saved date of the last check
	 * @return string
	 */
	function no_alert_detected($datesaved, $last_order_date){
		ob_start();
		require( $this->get_template_part("frontend", "no_alert_tpl") );
		$output .= ob_get_contents();
		ob_end_clean();
		return ($output);
	}
	
	/** 
	 * Send an alert mail 
	 */
	function sendalertmail(){
	$wps_alert_mdl = new wps_alert_mdl();
		$maillist = array();
		$usertab = $wps_alert_mdl->get_user_mail();
		foreach ($usertab as $userlist){
			$check = $wps_alert_mdl->get_admin_mail($userlist);
			if (strpos($check, 'administrator') !== false){
				array_push($maillist, $userlist->user_email);
			}
		}
		$titlemail = __('[WPSHOP] Disturbing problem on your orders', 'wpsalert_i18n');
		$message .= __('There is a problem on your orders. Your recent orders did not reach your ordering average. Check your orders.', 'wpsalert_i18n');
		wp_mail($maillist, $titlemail, $message);
	}
	
	/** 
	 * Get orders daily average
	 * @return integral
	 */
	function get_order_average(){
		$wps_alert_mdl = new wps_alert_mdl();
		$all_dates = $wps_alert_mdl->get_post_date();
		$choosen_date = $wps_alert_mdl->get_choosen_average() * 3600;
		$timestamp = time();
		$create_date = strtotime($create_date[0]->post_date);
		/** Get the timestamp of the choosen date **/
		$choosen_time = $timestamp - $choosen_date;
		/** And then reduce the actual timestamp by the choosen date timestamp **/
		$duration_time = $timestamp - $choosen_time;
		$count = 0;
		$res = 0;
		foreach ($all_dates as $order_date){
			if (strtotime($order_date->post_date) > $choosen_time){
				$count++;
			}
		}
		if ($count == 0)
			$count = 1;
		$res = $duration_time / $count;
		$res = $res / 3600;
		$res = number_format($res, '2', '.', ',');
		return ($res);
	}

}
