<?php
	/*	Define default classes	*/
	$category_class = 'wpshop_top_category';
	$category_container_class = 'wpshop_hide';
	$category_title_class = '';
	$category_state_class = 'ui-icon wpshop_category_closed';

	global $wp_query;

	/*	Check if the we are on a term page (category)	*/
	if(isset($wp_query->get_queried_object()->term_id) && ($wp_query->get_queried_object()->term_id > 0)){
		/*	Check if the current item we are adding into the menu is the item we are on	*/
		if($wp_query->get_queried_object()->term_id == $category->term_id){
			$category_title_class = 'wpshop_current_item';
			$category_state_class = 'ui-icon wpshop_category_opened';
			$category_container_class = '';
		}

		if(is_array($category_tree[$category->term_id]['children_category'])){
			if(in_array($wp_query->get_queried_object()->term_id, $category_tree[$category->term_id]['children_category'])){
				$category_state_class = 'ui-icon wpshop_category_opened';
				$category_container_class = '';
			}
		}
	}

	/*	Check if the we are on a product page	*/
	if(isset($wp_query->get_queried_object()->ID) && ($wp_query->get_queried_object()->ID > 0)){
		if(is_array($category_tree[$category->term_id]['children_product'])){
			if(in_array($wp_query->get_queried_object()->ID, $category_tree[$category->term_id]['children_product'])){
				$category_state_class = 'ui-icon wpshop_category_opened';
				$category_container_class = '';
			}
		}
	}

	if($category->parent != 0){
		$category_class = 'wpshop_sub_category wpshop_sub_category' . $category->parent;
		if($wp_query->get_queried_object()->term_id == $category->parent){
			$category_container_class = '';
		}
	}

	$link = get_term_link((int)$category->term_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

	if(is_array($category_tree[$category->term_id])){
		if((!is_array($category_tree[$category->term_id]['children_category']) || (count($category_tree[$category->term_id]['children_category']) <= 0))
			&& (!is_array($category_tree[$category->term_id]['children_product']) || (count($category_tree[$category->term_id]['children_product']) <= 0))){
			$category_state_class = 'wpshop_category_empty';
		}
	}

?>
<ul class="wpshop_categories_widget <?php echo $category_class; ?>" id="wpshop_categories_widget_<?php echo $category->term_id; ?>" >
	<li>
		<span id="wpshop_open_category_<?php echo $category->term_id; ?>" class="wpshop_open_category <?php echo $category_state_class; ?>" >&nbsp;</span><a class="widget_category_title <?php echo $category_title_class; ?>" href="<?php echo $link; ?>" ><?php echo esc_html($category->name); ?></a>
		<div class="wpshop_category_sub_content_<?php echo $category->term_id; ?> <?php echo $category_container_class; ?>" >
<?php
		/*	Get the sub categories of the current category	*/
		echo wpshop_categories::category_tree_output($category->term_id);

		/*	Get the product of the current category	if the current category has no sub category*/
		global $category_has_sub_category;
		if(!$category_has_sub_category){
			wpshop_products::get_product_of_category($category->slug, $category->term_id);
		}
?>
		</div>
	</li>
</ul>