<?php 
/**
 * Display file for Wps_Export
 */


/** 
 * Wp Export display class
 */
class wpexport_display{
	/**
	* Check and get the template file path to use for a given display part
	*
	* @uses locate_template()
	* @uses get_template_part()
	*
	* @param string $plugin_dir_name
	* @param string $plugin_template_dir
	* @param string $side The website part were the template will be displayed. Backend or frontend
	* @param string $slug The slug name for the generic template.
	* @param string $name The name of the specialised template.
	*
	* @return string The template file path to use
	*/
	static function get_template_part( $plugin_dir_name, $plugin_template_dir, $side, $slug, $name = "" ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
		
		/** Check if required template exists into current theme */
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $plugin_dir_name . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
		
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
				if ( file_exists( $plugin_template_dir . $template_name ) ) {
					$path = $plugin_template_dir . $template_name;
						break;
				}
			}
		}
		
		return $path;
	}	
}
