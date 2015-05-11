<?php
/**
 * Display log controller file for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs_display
 * @subpackage controller
 */

/**
 * Display log controller class for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs_display
 * @subpackage controller
 */

if(!class_exists("wpeologs_display_log_ctr")) {
	class wpeologs_display_log_ctr {
	
		/**
		 * __construct - add action admin_menu and ajax action for get_bulleted_list and render_csv
		 */
		function __construct() {
		    /** Add page settings */
		    add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		
		    /** Ajax */
		    add_action('wp_ajax_wpeo-get-bulleted-list', array( &$this, 'get_bulleted_list' )) ;
		    add_action('wp_ajax_wpeo-render-csv', array( &$this, 'render_csv' )) ;   
	  	}
		
	  	/**
	  	 * admin_menu - Add sub menu page tools.php
	  	 */
	  	public function admin_menu() {
	    	add_submenu_page( 'tools.php', __('Logs', 'wpeologs-i18n'), __('Logs', 'wpeologs-i18n'), 'manage_options', 'wpeo-log-page', array( &$this, 'render_page') );
	  	}
		
	  	/**
	  	 * render_page - open all folder in wpeologs directory and count critical, warning, and all data 
	  	 */
	  	public function render_page() {
	    	$upload_dir = wp_upload_dir();
	    	$upload_dir = $upload_dir['basedir'] . '/wpeologs';
	
	    	$array_file = array();
	
	    	/** Open all files */
		    if(file_exists($upload_dir)) {
			    if($folder = opendir($upload_dir)) {
			        while(false !== ($file = readdir($folder))) {
			          if('..' != $file && '.' != $file && '_wpeo-critical.csv' != $file && '_wpeo-warning.csv' != $file && !preg_match('#[0-9].csv$#', $file) )
			            $array_file[] = $file;
			        }
			    }
			    else {
			    	_e('The folder ' . $upload_dir['basedir'] . ' cannot be open', 'wpeologs-i18n');
		   		}
		    }
		    
		    /** Count all critical, warning, and data */
	    	$critical_error = $this->get_critical();
	   		$warning_error = $this->get_warning();
	   		$total_log = $this->get_all_number_file();
	    
	    	require(WPEO_LOGS_TEMPLATES_MAIN_DIR . 'backend/tools/render-page.tpl.php');
	  	}
	  
	  	/**
	  	 * get_critical - if file exist, count the number line or return 0
	  	 * 
	  	 * @return number
	  	 */
	  	public function get_critical() {
	  		$upload_dir = wp_upload_dir();
	  	
		  	if(file_exists($upload_dir['basedir'] . '/wpeologs/_wpeo-critical.csv')) {
		  		$file = file($upload_dir['basedir'] . '/wpeologs/_wpeo-critical.csv');
		  		return count($file);
		  	}
			return 0;  
	  	}
	  
	  	/**
	  	 * get_warning - if file exists, count the number line or return 0
	  	 * 
	  	 * @return number
	  	 */
	  	public function get_warning() {
		  	$upload_dir = wp_upload_dir();
		  	
		  	if(file_exists($upload_dir['basedir'] . '/wpeologs/_wpeo-warning.csv')) {
		  		$file = file($upload_dir['basedir'] . '/wpeologs/_wpeo-warning.csv');
		  		return count($file);
		  	}
		  	
		  	return 0;	
	 	 }
	  
	 	/**
	 	 * get_all_number_file - Count all number line in all files excepts _wpeo-critical.csv and _wpeo_warning.csv and return it
	 	 * 
	 	 * @return number
	 	 */
	 	public function get_all_number_file() {
		  	$upload_dir = wp_upload_dir();
		  	$upload_dir = $upload_dir['basedir'] . '/wpeologs';
		  	
		  	$count = 0;
		  	
		  	if(file_exists($upload_dir)) {
			  	if($folder = opendir($upload_dir)) {
			  		while(false !== ($file = readdir($folder))) {
			  			if('..' != $file && '.' != $file && '_wpeo-critical.csv' != $file && '_wpeo-warning.csv' != $file && !preg_match('#[0-9].csv$#', $file) ) {
				  			if(file_exists($upload_dir . '/' . $file)) {
				  				$file = file($upload_dir . '/' . $file);
				  				$count += count($file);
			  				}
			  			}
			  		}
			  	}
		  	}
		  	
		  	return $count;
	  	}
	  
		/**
	  	 * get_bulleted_list - Return the template with the bulleted list
		 */
	  	public function get_bulleted_list() {
		  	$upload_dir = wp_upload_dir();
		  	
		  	// Get the file_name
		  	$file_name_without_extension = explode('.', $_POST['current_file_name']);
		  	
		  	// Path to file
		  	$glob_file = $upload_dir['basedir'] . '/wpeologs/' . $file_name_without_extension[0];
		  	
		  	$array_glob_file = glob($glob_file . '*.csv');
		  	
		  	foreach($array_glob_file as &$glob_file) {
		  		$glob_file = explode('/', $glob_file);
		  		$glob_file = $glob_file[count($glob_file) - 1];
		  	}
		  	
		  	array_shift($array_glob_file);
		  	
		  	require(WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/bulleted-list.tpl.php" );
		  	
		  	wp_die();
	  	}
	
	  	/**
	  	* render_csv - Call by ajax, return the template with the table
	  	*/
	 	public function render_csv() {
		    $upload_dir = wp_upload_dir();
		    $dir_file = $upload_dir['basedir'] . '/wpeologs/' . $_POST['current_file_name'];
		
		    $file = file($dir_file);
		
		    // Remove the first case empty
		    array_shift($file);
		
		    if(!empty($_POST['get_archive']))
		      $file_name_without_extension = explode('.', $_POST['current_file_name']);
		    else
		      $file_name_without_extension = explode('.', $_POST['current_parent_name']);
		
		    $glob_file = $upload_dir['basedir'] . '/wpeologs/' . $file_name_without_extension[0];
		
		    // Get archive
		    $array_glob_file = glob($glob_file . '*.csv');
		
		    foreach($array_glob_file as &$glob_file) {
		      $glob_file = explode('/', $glob_file);
		      $glob_file = $glob_file[count($glob_file) - 1];
		    }
		
		    // Array slice
		    $count_csv_line = count($file);
		
		    // Prepare the array
		    foreach($file as &$array) {
		      $array = explode(wpeologs_ctr::$file_separator, $array);
		    }
		
		    // Check if post type or not for template
		    $file_name = explode('_', $file_name_without_extension[0]);
		
		    $post_types = get_post_types();
		
		    if(in_array(!empty($file_name[0]) ? $file_name[0] : $file_name, $post_types))
		      require(WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/render-table-post-log-data.tpl.php" );
		    else
		      require(WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/render-table-log-data.tpl.php" );
		
		    wp_die();
	  	}
	}
}
