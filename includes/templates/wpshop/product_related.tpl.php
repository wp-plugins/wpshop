<?php
	if(!empty($pids) && ($pids[0] != '')){
		$cpt_re = 1;
		$string .= '<div id="related-products">';
		$string .= '<h2 class="love anchor-choix">Vous aimerez surement</h2>';
		$string .= '<ul class="liste_produits love">';
		foreach($pids as $pid){
			$cats = get_the_terms($pid, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
			$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
			if($cpt_re<=3){
				if(!($cpt_re%3)){
					//echo('class="last"');

					$string .= '<li class="last">'.self::product_mini_output($pid, $cat_id, $display_mode).'</li>';
				}else{
					$string .= '<li>'.self::product_mini_output($pid, $cat_id, $display_mode).'</li>';
				}		
				$cpt_re++;
			}
		}
		$string .= '</ul>';
		$string .= '<div class="cls"></div></div>';
	}
?>