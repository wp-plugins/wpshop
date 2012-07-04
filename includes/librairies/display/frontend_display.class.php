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

class wpshop_frontend_display{

	function products_page($content = ''){
		global $wp_query;
		$output = '';

		if(!empty($wp_query->post) && ($wp_query->post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)){
			return wpshop_products::product_complete_sheet_output($content, $wp_query->post->ID);
		}
		else{
			return $content;
		}
	}

}