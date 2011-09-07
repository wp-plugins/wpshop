<?php
/**
* Plugin tools librairies file.
* 
*	This file contains the different common tools used in all the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

class wpshop_frontend_display
{

	function products_page($content = ''){
		global $wp_query;
		$output = '';

		if (preg_match( "/\[wpshop_product_page\]/", $content )){
			remove_filter('the_content', 'wpautop');

			ob_start();
			include_once(WPSHOP_TEMPLATES_DIR . 'products.tpl.php');
			$output .= ob_get_contents();
			ob_end_clean();
			$output = str_replace( '$', '\$', $output );

			return preg_replace( "/(<p>)*\[wpshop_product_page\](<\/p>)*/", $output, $content );
		}
		elseif(is_archive()){
			remove_filter('the_content', 'wpautop');
			return wpshop_frontend_display::products_page('[wpshop_product_page]');
		}
		elseif($wp_query->post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT){
			return wpshop_products::product_complete_sheet_output($content, $wp_query->post->ID);
		}
		else{
			return $content;
		}
	}

}