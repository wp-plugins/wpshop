<?php
	if(!empty($pids) && ($pids[0] != '')){
		$cpt_re = 1;
		$string .= '<div id="related-products">';
		$string .= '<h2 class="love anchor-choix">' . __('You\'d love too', 'wpshop') . '</h2>';
		$string .= '<ul class="products_listing '. $type . '_' . $grid_element_nb_per_line .' '. $type .'_mode clearfix">';
		foreach($pids as $pid){
			$cats = get_the_terms($pid, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
			$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
			$string .= self::product_mini_output($pid, $cat_id, $type, $cpt_re, $grid_element_nb_per_line);
			$cpt_re++;
		}
		$string .= '</ul>';
		$string .= '<div class="cls"></div></div>';
	}
?>