<?php

/** 
 * Main functions of Export module for WPShop
 * @package File
 */
 
if (is_admin()) require_once(ABSPATH . 'wp-includes/pluggable.php');
/**
 * Export client controller
 * @package Class
 */
class exportclientctr{

	/**
	 * Template directory
	 */
	private $template_dir;
	
	/**
	 * Plugin directory
	 */
	private $plugin_dirname = WPS_EXPORT_DIR;
	
	/**
	* Constructor
	*/
	function __construct(){
		/** Module init **/
		add_action('add_meta_boxes', array(&$this, 'initmetabox'));
		add_action( 'admin_enqueue_scripts', array(&$this, 'scriptinclude'));
		$this->template_dir = WPS_EXPORT_PATH . WPS_EXPORT_DIR . "/templates/";
		DEFINE('WPSHOP_EXPORT_UPDATE_PRIVATE_MESSAGE_OBJECT', __('Your order has been updated', 'wpsexport_i18n'));
		DEFINE('WPSHOP_EXPORT_UPDATE_PRIVATE_MESSAGE', __('Hello [customer_first_name] [customer_last_name], your order ([order_key]) has just been updated. A comment has been added:<br /><br />"[message]".<br /><br /> Thank you for your loyalty. Have a good day.', 'wpsexport_i18n'));
		
		/** Check list **/
		$this->checkfunctionlist();
		}
	
		/** Create Metabox **/
		function initmetabox(){
			add_meta_box('exportmeta', 'Exportation Client', 'funcmeta', 'post');
		}
	
	/** 
	 * Check list to know which function call 
	 */
	function checkfunctionlist(){
		$exportclientmdl = new exportclientmdl();
		if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export1') {
			$exportclientmdl->checkuserlist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export2') {
			$exportclientmdl->checkalluserlist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export3') {
			$exportclientmdl->exportorders();
		}		
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export4') {
			$exportclientmdl->bestuserslist();
		}
		else if ( !empty($_GET['userlist']) && $_GET['userlist'] == 'export5') {
			$exportclientmdl->ordernotcomplete();
		}
	}
	/**
	 * Include all the scripts
	 */
	function scriptinclude() {
		$path = plugin_dir_url( __FILE__ ).'assets/js/changebox.js';
		$path = str_replace('controller/', '', $path);
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('wps_changebox', $path);
	}

	/** 
	 * Display the plugin box on the dashboard 
	 * @return string
	 */
	function display_export_box(){
		ob_start();
		require( $this->get_template_part("frontend", "wps_export_tpl") );
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
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
		
}

