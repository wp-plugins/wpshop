<?php
/**
* Products management method file
* 
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_categories
{

	/**
	*	Call wordpress function that declare a new term type in order to define the product as wordpress term (taxonomy)
	*/
	function create_product_categories()
	{
		register_taxonomy(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT), array(
			'labels' => array(
				'name' => __('Categories', 'wpshop'),
				'singular_name' => __('Category', 'wpshop'),
				'add_new_item' => __('Add new category', 'wpshop'),
				'add_new' => _x( 'Add new', 'admin menu: add new category', 'wpshop'),
				'add_new_item' => __('Add new category', 'wpshop'),
				'edit_item' => __('Edit category', 'wpshop'),
				'new_item' => __('New category', 'wpshop'),
				'view_item' => __('View category', 'wpshop' ),
				'search_items' => __('Search categories', 'wpshop'),
				'not_found' =>  __('No categories found', 'wpshop'),
				'not_found_in_trash' => __('No categories found in trash', 'wpshop'),
				'parent_item_colon' => '',
				'menu_name' => __('Categories', 'wpshop')
			),
			'rewrite' => array('slug' => 'catalog', 'with_front' => false),
			'hierarchical' => true,
			'public' => true,
			'show_in_nav_menus' => true
		));
	}

	/**
	*	Build a complete tree with the categories. Contains the complete tree for a given category and a children list for easy checking
	*
	*	@param integer $category_id The category identifier we want to get the tree element for
	*
	*	@return array $categories_list An array ordered by category with its children
	*/
	function category_tree($category_id = 0){
		$categories_list = array();

		$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'hide_empty=0&parent=' . $category_id);
		if(count($categories) > 0){
			foreach($categories as $category){
				/*	If necessary un-comment this line in order to get the complete tree for the category	*/
				// $categories_list[$category->term_id]['children_tree'] = self::category_tree($category->term_id);
				$categories_list[$category->term_id]['children_category'] = get_term_children($category->term_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

				/*	Get the product list for the category	*/
				$products = get_posts(array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES => $category->slug));
				foreach($products as $product){
					$categories_list[$category->term_id]['children_product'][] = $product->ID;
				}
			}
		}

		return $categories_list;
	}
	/**
	*	Get the sub categories of a given category
	*
	*	@param integer $parent_category The main category we want to have the sub categories for
	*/
	function category_tree_output($category_id = 0){
		global $category_has_sub_category;

		$widget_content = '';

		$category_tree = wpshop_categories::category_tree($category_id);

		$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'hide_empty=0&parent=' . $category_id);
		if(count($categories) > 0){
			foreach($categories as $category){
				ob_start();
				include(WPSHOP_TEMPLATES_DIR . 'categories-widget.tpl.php');
				$widget_content .= ob_get_contents();
				ob_end_clean();
			}
			$category_has_sub_category = true;
		}
		else{
			$category_has_sub_category = false;
		}

		return $widget_content;
	}


	/**
	*	Add additionnal fields to the category creation form
	*/
	function category_add_fields(){

	/*	Category picture	*/
?>
<div class="form-field">  
	<label for="wpshop_category_picture"><?php _e('Category\'s thumbnail', 'wpshop'); ?></label>  
	<input type="file" name="wpshop_category_picture" id="wpshop_category_picture" value="" />
	<p><?php _e('The thumbnail for the category', 'wpshop'); ?></p>  
</div>
<?php
	}
	/**
	*	Add additionnal fields to the category edition form
	*/
	function category_edit_fields(){
		$category_id = wpshop_tools::varSanitizer($_REQUEST["tag_ID"]);
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);

	/*	Category picture	*/
?>
<tr class="form-field">  
	<th scope="row" valign="top"><label for="wpshop_category_picture"><?php _e('Category\'s thumbnail', 'wpshop'); ?></label></th>  
	<td>
<?php
		$category_thumbnail_preview = WPSHOP_DEFAULT_CATEGORY_PICTURE;
		/*	Check if there is already a picture for the selected category	*/
		if(!empty($category_meta_information['wpshop_category_picture']) && is_file(WPSHOP_UPLOAD_DIR . $category_meta_information['wpshop_category_picture'])){
			$category_thumbnail_preview = WPSHOP_UPLOAD_URL . $category_meta_information['wpshop_category_picture'];
		}
?>
		<div class="clear" >
			<div class="alignleft" ><img src="<?php echo $category_thumbnail_preview; ?>" alt="category img preview" class="category_thumbnail_preview" /></div>
			<div class="category_new_picture_upload" ><?php _e('If you want to change the current picture choose a new file', 'wpshop'); ?>&nbsp:&nbsp;<input type="file" name="wpshop_category_picture" id="wpshop_category_picture" value="" /></div>
		</div>
		<div class="clear description" ><?php _e('The thumbnail for the category', 'wpshop'); ?></span>
	</td>  
</tr>
<?php
	}
	/**
	*	
	*/
	function category_fields_saver($category_id, $tt_id){
		global $wpdb;
		$category_meta = array();
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);

		/* Start category picture upload and treatment	*/
		$category_meta['wpshop_category_picture'] = $category_meta_information['wpshop_category_picture'];
		if(!empty($_FILES['wpshop_category_picture']) && preg_match( "/\.(" . WPSHOP_AUTHORIZED_PICS_EXTENSIONS . "){1}$/i", $_FILES['wpshop_category_picture']['name'])){
			/*	Check if destination directory exist and create it if it does not exist	*/
			$category_picture_dir = WPSHOP_UPLOAD_DIR . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '/' . $category_id . '/';
			if(!is_dir($category_picture_dir)){
				mkdir($category_picture_dir, 0755, true);
			}

			/*	Start send picture treatment	*/
			$new_image_path = $category_picture_dir . basename($_FILES['wpshop_category_picture']['name']);
			move_uploaded_file($_FILES['wpshop_category_picture']['tmp_name'], $new_image_path);
			$stat = stat( dirname( $new_image_path ) );
			$perms = $stat['mode'] & 0000666;
			@chmod( $new_image_path, $perms );	
			$wpshop_category_picture = $wpdb->escape( $_FILES['wpshop_category_picture']['name'] );

			$category_meta['wpshop_category_picture'] = str_replace(WPSHOP_UPLOAD_DIR, '', $category_picture_dir) . $wpshop_category_picture;
		}

		update_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id, $category_meta);
	}
	/**
	*	
	*/
	function category_manage_columns($columns){
    unset( $columns["cb"] );
    
    $custom_array = array(
        'cb' => '<input type="checkbox" />',
        'wpshop_category_thumbnail' => __('Thumbnail', 'wpshop')
    );
    
    $columns = array_merge( $custom_array, $columns );
    
    return $columns;
	}
	/**
	*	
	*/
	function category_manage_columns_content($string, $column_name, $category_id){
		global $wpdb;

		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
		$category_thumbnail_preview = WPSHOP_DEFAULT_CATEGORY_PICTURE;
		/*	Check if there is already a picture for the selected category	*/
		if(!empty($category_meta_information['wpshop_category_picture']) && is_file(WPSHOP_UPLOAD_DIR . $category_meta_information['wpshop_category_picture'])){
			$category_thumbnail_preview = WPSHOP_UPLOAD_URL . $category_meta_information['wpshop_category_picture'];
		}
		$category = get_term_by('id', $category_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
		$name = $category->name;

		$image = '<img src="' . $category_thumbnail_preview . '" title="' . $name . '" alt="' . $name . '" class="category_thumbnail_preview" />';

    return $image;
	}


	/**
	*	Display a category in a list
	*/
	function category_mini_output($category, $output_type = 'list'){
		$content = '';

		/*	Get the different informations for output	*/
		$categoryThumbnail = '<img src="' . WPSHOP_DEFAULT_CATEGORY_PICTURE . '" alt="category has no picture" class="category_thumbnail" />';
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category->term_id);
		if(!empty($category_meta_information['wpshop_category_picture']) && is_file(WPSHOP_UPLOAD_DIR . $category_meta_information['wpshop_category_picture'])){
			$categoryThumbnail = '<img src="' . WPSHOP_UPLOAD_URL . $category_meta_information['wpshop_category_picture'] . '" alt="' . $category->name . ' picture" class="category_thumbnail" />';
		}
	
		$category_title = $category->name;
		$category_more_informations = $category->description;
		$category_link = get_term_link((int)$category->term_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

		/*	Make some treatment in case we are in grid mode	*/
		if($output_type == 'grid'){
			/*	Determine the width of a component in a line grid	*/
			$element_width = (100 / WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE);
			$item_width = (round($element_width) - 1) . '%';
		}

		/*	Include the category sheet template	*/
		ob_start();
		require(WPSHOP_TEMPLATES_DIR . 'category-mini-' . $output_type . '.tpl.php');
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}


	/**
	*	Allows to switch easyly between the archive template and a normal page template in order to output a category.
	*/
	function category_template_switcher($template){
		/*	Check if the current template page contains the "archive" word in order to change it into "page"	*/
		if(strpos($template, 'archive') !== false){
			return str_ireplace('archive', 'page', $template);
		}
		else{
			return $template;
		}
	}

}