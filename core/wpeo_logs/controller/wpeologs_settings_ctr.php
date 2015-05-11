<?php


/**
 * Settings controller file for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs_settings
 * @subpackage controller
 */

/**
 * Setting controller class for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs_settings
 * @subpackage controller
 */

if(!class_exists("wpeologs_settings_ctr")) {
	class wpeologs_settings_ctr extends wpeologs_ctr {
		
		/**
		 * __construct - get settings and add_action admin menu and ajax update_service
		 */
	  	function __construct() {
		    $this->get_settings();
		
		    /** Add page settings */
		    add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		
		    /** Ajax - create service */
		    add_action( 'wp_ajax_wpeo-update-service', array( &$this, 'ajax_update_service' ) );
	  	}
	
		/**
		 *	Wordpress hook - admin_menu - Add page settings
		 */
		public function admin_menu() {
			add_options_page( __('Logs', 'wpeologs-i18n'), __('Logs', 'wpeologs-i18n'), 'manage_options', 'wpeo-log', array( &$this, 'render_page') );
		}
	
	  	/**
		* render_page - Display the template
		*/
		public function render_page() {
		  	$array_size_format = $this->get_array_size_format();
		    $current_option = get_option('_wpeo_log_settings');
		    
		    require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/settings/render-page.tpl.php");
	  	}
	
	  	/**
	  	 * convert to - Convert format to "oc" or deconvert
	  	 * 
	  	 * @param float $input
	  	 * @param string $format
	  	 * @param boolean $convert
	  	 * @return float|number
	  	 */
	  	function convert_to($input, $format, $convert = true) {
			if($format == 'oc')
		    	return $input;
		
			$multiple = 0;
		
	   		if($format == 'ko')
	       		$multiple = 1024;
	      	else if($format == 'mo')
	        	$multiple = 1048576;
	      	else if($format == 'go')
	        	$multiple = 1073741824;
	
		 	if($convert)
		    	return $input * $multiple;
		   	else
		    	return $input / $multiple;
	 	 }
	  
	 	/**
	 	 * get_array_size_format
	 	 * 
	 	 * @return multitype:string
	 	 */ 
		function get_array_size_format() {
	 		return array('oc' => 'Octets', 'ko' => 'Ko', 'mo' => 'Mo', 'go' => 'Go');
		}
	
		/**
		 * ajax_update_service - update or create the service
		 * 
		 * return json_encode $response("slug", "render")
		 */
	  	function ajax_update_service() {
			header('Content-Type: application/json');
	
	    	$current_option = get_option('_wpeo_log_settings');
		
	    	$response = array();
	    
	    	// Prepare variable for template
	    	$service_slug = !empty($_POST['service_slug']) ? $_POST['service_slug'] : sanitize_title($_POST['service_name']);
	    
	    	// Add to option
	    	if(!empty($_POST['service_active'])) {
				$current_option['my_services'][$service_slug]['service_active'] = ($_POST['service_active'] == "true") ? 1 : 0;
				// For render
				$_POST['service_active'] = ($_POST['service_active'] == "true") ? 1 : 0;
	    	}
	    
	    	if(!empty($_POST['service_name']))
	    		$current_option['my_services'][$service_slug]['service_name'] = $_POST['service_name'];
	    
	    	if(!empty($_POST['service_size']))
	    		$current_option['my_services'][$service_slug]['service_size'] = $_POST['service_size'];
	    
	    	if(!empty($_POST['service_size_format']))
	    		$current_option['my_services'][$service_slug]['service_size_format'] = $_POST['service_size_format'];
	    
	   	 	if(!empty($_POST['service_file']))
	    		$current_option['my_services'][$service_slug]['service_file'] = $_POST['service_file'];
	    
	    	if(!empty($_POST['service_rotate'])) {
	    		$current_option['my_services'][$service_slug]['service_rotate'] = ($_POST['service_rotate'] == "true") ? 1 : 0;
	    		// For render
	    		$_POST['service_rotate'] = ($_POST['service_rotate'] == "true") ? 1 : 0;
	    	}
	    
	    	if(!empty($_POST['service_size']) && !empty($_POST['service_size_format'])) {
	    		$current_option['my_services'][$service_slug]['service_size'] = $this->convert_to($_POST['service_size'], $_POST['service_size_format']);
	    		$_POST['service_size'] = $this->convert_to($_POST['service_size'], $_POST['service_size_format']);
	    	}
	    
		    // Save option
		    update_option('_wpeo_log_settings', $current_option);
		    
		    $response['slug'] = $service_slug;
		    
		    $response['render'] = $this->display_model_service($service_slug, $_POST, true);
		    
		    wp_die(json_encode($response));
	  	}
	  
	  	/**
	  	 * display_model_service
	  	 * 
	  	 * @param string $service_slug
	  	 * @param array $array
	  	 * @param boolean $need_render
	  	 * @return string
	  	 */
	  	function display_model_service($service_slug, $array, $need_render = false) {
			$array_size_format = $this->get_array_size_format();
			
			if($need_render) {
				ob_start();
				require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/settings/models/service.tpl.php");
				return ob_get_clean();
			}
			else {
				require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/settings/models/service.tpl.php" );
			}
		}
	}
}
?>
