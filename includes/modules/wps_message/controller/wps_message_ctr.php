<?php
class wps_message_ctr {
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_MESSAGE_DIR;
	
	function __construct() {
		/** Template Load **/
		$this->template_dir = WPS_MESSAGE_PATH . WPS_MESSAGE_DIR . "/templates/";
		add_shortcode( 'wps_message_histo', array( $this, 'display_message_histo_per_customer') );
	}
	
	/** Load templates **/
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
	
	
	function display_message_histo_per_customer( $args, $customer_id = '' ) {
		$customer_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
		$message_id = ( !empty($args) && !empty($args['message_id']) ) ? $args['message_id'] : '';
		$message_elements = '';
		
		$wps_message_mdl = new wps_message_mdl();
		$messages_data = $wps_message_mdl->get_messages_histo( $message_id, $customer_id );

		ob_start();
		require( $this->get_template_part( "frontend", "messages") );
		$output .= ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	
}