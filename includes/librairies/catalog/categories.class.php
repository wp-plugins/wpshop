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

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

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
	* Retourne une liste de cat�gorie
	* @param boolean $formated : formatage du r�sultat oui/non
	* @param string $product_search : recherche demand�e
	* @return mixed
	**/
	function product_list_cats($formated=false, $product_search=null) {
		$where  = array('hide_empty' => false);
		if(!empty($product_search))
			$where = array_merge($where, array('name__like'=>$product_search));

		$data = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, $where);
		$cats=array();
		foreach($data as $d){
			$cats[$d->term_id] = $d->name;
		}

		// Si le formatage est demand�
		if($formated) {
			if(!empty($cats)):
				$cats_string='';
				foreach($cats as $key=>$value) {
					$cats_string.= '
					<li><input type="checkbox" class="wpshop_shortcode_element wpshop_shortcode_element_categories" value="'.$key.'" id="'.WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES.'-'.$key.'" name="cats[]" /><label for="'.WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES.'-'.$key.'" > '.$value.'</label></li>';
				}
			endif;
		}
		return $formated?$cats_string:$cats;
	}

	/**
	*	Call wordpress function that declare a new term type in order to define the product as wordpress term (taxonomy)
	*/
	public static function create_product_categories(){
		$options = get_option('wpshop_catalog_categories_option', null);
		register_taxonomy(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT), array(
			'labels' => array(
				'name' => __('WPshop categories', 'wpshop'),
				'singular_name' => __('WPshop category', 'wpshop'),
				'add_new_item' => __('Add new wpshop category', 'wpshop'),
				'add_new' => _x( 'Add new', 'admin menu: add new wpshop category', 'wpshop'),
				'add_new_item' => __('Add new wpshop category', 'wpshop'),
				'edit_item' => __('Edit wpshop category', 'wpshop'),
				'new_item' => __('New wpshop category', 'wpshop'),
				'view_item' => __('View wpshop category', 'wpshop' ),
				'search_items' => __('Search wpshop categories', 'wpshop'),
				'not_found' =>  __('No wpshop categories found', 'wpshop'),
				'not_found_in_trash' => __('No wpshop categories found in trash', 'wpshop'),
				'parent_item_colon' => '',
				'menu_name' => __('WPshop Categories', 'wpshop')
			),
			'rewrite' => array('slug' => !empty($options['wpshop_catalog_categories_slug']) ? $options['wpshop_catalog_categories_slug'] : WPSHOP_CATALOG_PRODUCT_NO_CATEGORY, 'with_front' => false,'hierarchical' => true),
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
	*	@param array $instance The current instance of the widget, allows to get the different selected parameters
	*
	* @return mixed $widget_content The widget content build from option
	*/
	function category_tree_output($category_id = 0, $instance) {
		global $category_has_sub_category;

		$widget_content = '';
		$category_tree = wpshop_categories::category_tree($category_id);
		if((!isset($instance['wpshop_widget_categories']) && !isset($instance['show_all_cat'])) || ($instance['show_all_cat'] == 'yes')){
			$categories = get_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES, 'hide_empty=0&parent=' . $category_id);
			if(count($categories) > 0){
				foreach($categories as $category){
					ob_start();
					require(wpshop_display::get_template_file('categories-widget.tpl.php'));
					$widget_content .= ob_get_contents();
					ob_end_clean();
				}
				$category_has_sub_category = true;
			}
			else{
				$category_has_sub_category = false;
			}
		}

		return $widget_content;
	}


	/**
	*	Add additionnal fields to the category edition form
	*/
	function category_edit_fields(){
		$category_id = wpshop_tools::varSanitizer($_REQUEST["tag_ID"]);
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
		$tpl_component = array();
		$category_thumbnail_preview = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		/*	Check if there is already a picture for the selected category	*/

		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'thumbnail', false, array('class' => 'category_thumbnail_preview') );
			$category_thumbnail_preview = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		}
		

		$tpl_component['CATEGORY_DELETE_PICTURE_BUTTON'] = '';
		if( !empty($category_meta_information) && !empty($category_meta_information['wpshop_category_picture']) ) {
			$tpl_component['CATEGORY_DELETE_PICTURE_BUTTON'] = '<a href="#" role="button" id="wps-delete-category-picture" class="wps-bton-second-mini-rounded">' .__( 'Delete the category picture', 'wpshop' ). '</a> ';
		}
		$tpl_component['CATEGORY_PICTURE_ID'] = ( ( !empty($category_meta_information['wpshop_category_picture']) ) ? $category_meta_information['wpshop_category_picture'] : '' );
		
		$tpl_component['CATEGORY_THUMBNAIL_PREVIEW'] = $category_thumbnail_preview;
		if(isset($_GET['tag_ID'])){
			$tpl_component['CATEGORY_TAG_ID'] = $_GET['tag_ID'];
			$tpl_component['CATEGORY_FILTERABLE_ATTRIBUTES'] = '';
			$wpshop_category_products = wpshop_categories::get_product_of_category( $_GET['tag_ID'] );
			$filterable_attributes_list = array();
			foreach ( $wpshop_category_products as $wpshop_category_product ) {
				$elementId = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
				if ( !empty($elementId) ) {
					$product_attributes = wpshop_attributes::get_attribute_list_for_item($elementId, $wpshop_category_product);
					if ( !empty($product_attributes) ) {
						foreach ( $product_attributes as $key => $product_attribute ) {
							if ( !empty($product_attribute) && !empty($product_attribute->is_filterable) && strtolower(__($product_attribute->is_filterable, 'wpshop')) == strtolower(__('Yes', 'wpshop')) ) {
								if  ( !array_key_exists($product_attribute->attribute_id, $filterable_attributes_list) ) {
									$filterable_attributes_list[$product_attribute->attribute_id] = $product_attribute;
									$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_ID'] =  $product_attribute->attribute_id;
									$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_NAME'] =  __($product_attribute->frontend_label, 'wpshop');
									if ( !empty($category_meta_information) && !empty($category_meta_information['wpshop_category_filterable_attributes']) && array_key_exists($product_attribute->attribute_id, $category_meta_information['wpshop_category_filterable_attributes']) ) {
										$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_CHECKED'] = 'checked="checked"';
									}
									else {
										$sub_tpl_component['CATEGORY_FILTERABLE_ATTRIBUTE_CHECKED'] = '';
									}

									$tpl_component['CATEGORY_FILTERABLE_ATTRIBUTES'] .= wpshop_display::display_template_element('wpshop_category_filterable_attribute_element', $sub_tpl_component, array(), 'admin');
									unset($sub_tpl_component);
								}
							}
						}
					}
				}
			}
		 }
		 else {
		 	$tpl_component['CATEGORY_TAG_ID'] = 1;
		 }
		 $output = wpshop_display::display_template_element('wpshop_category_edit_interface_admin', $tpl_component, array(), 'admin');
		 echo $output;
	}

	/**
	*	Save the different extra fields added for the plugin
	*
	*	@param integer $category_id The category identifier we want to save extra fields for
	*	@param mixed $tt_id
	*
	*	@return void
	*/
	function category_fields_saver($category_id, $tt_id){
		global $wpdb;
		$category_meta = array();
		$category_option = get_option( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);

		if ( !empty($_POST['wps_category_picture_id']) ) {
			$attach_id = intval( $_POST['wps_category_picture_id'] );
			$category_option['wpshop_category_picture'] = $attach_id;
		}

		if ( !empty($_POST['filterable_attribute_for_category']) && is_array($_POST['filterable_attribute_for_category']) ) {
			$category_option = get_option( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
			$category_option['wpshop_category_filterable_attributes'] = $_POST['filterable_attribute_for_category'];
		}
		else {
			$category_option['wpshop_category_filterable_attributes'] = array();
		}
		update_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id, $category_option);

		/** Update filter values **/
		$wpshop_filter_search = new wps_filter_search();
		$wpshop_filter_search->stock_values_for_attribute( array($category_id) );
	}
	
	/**
	*	Add extra column to categories listing interface
	*
	*	@param array $columns Actual columns to add extra columns to
	*
	*	@return array $columns The new array with additionnal colu
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
	*	Define the content of extra columns to add to categories listing interface
	*/
	function category_manage_columns_content($string, $column_name, $category_id){
		$category_meta_information = get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category_id);
		$category_thumbnail_preview = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		/*	Check if there is already a picture for the selected category	*/
		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'thumbnail', false, array('class' => 'category_thumbnail_preview') );
			$category_thumbnail_preview = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail_preview" />';
		}
		$category = get_term_by('id', $category_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
		$name = $category->name;

		$image = $category_thumbnail_preview;
    	return $image;
	}


	/**
	*	Display a category in a list
	*
	*	@param object $category The category definition
	*	@param string $output_type The output type defined from plugin option
	*
	*	@return mixed $content Output the category list
	*/
	function category_mini_output($category, $output_type = 'list'){
		$content = '';
		/*	Get the different informations for output	*/
		$category_meta_information = ( !empty($category) && !empty($category->term_id) ) ? get_option(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '_' . $category->term_id) : '';
		$categoryThumbnail = '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail" />';
		/*	Check if there is already a picture for the selected category	*/
		if ( !empty($category_meta_information['wpshop_category_picture']) ) {
			$image_post = wp_get_attachment_image( $category_meta_information['wpshop_category_picture'], 'thumbnail', false, array('class' => 'category_thumbnail') );
			$categoryThumbnail = ( !empty($image_post) ) ? $image_post : '<img src="' .WPSHOP_DEFAULT_CATEGORY_PICTURE. '" alt="No picture" class="category_thumbnail" />';
		}


		$category_title = ( !empty($category) && !empty($category->name) ) ? $category->name : '';
		$category_more_informations = ( !empty($category) && !empty($category->description) ) ? $category->description : '';
		$category_link = ( !empty($category) && !empty($category->term_id) ) ?  get_term_link((int)$category->term_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) : '';

		/*	Make some treatment in case we are in grid mode	*/
		if($output_type == 'grid'){
			/*	Determine the width of a component in a line grid	*/
			$element_width = (100 / WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE);
			$item_width = (round($element_width) - 1) . '%';
		}

		/*
		 * Template parameters
		 */
		$template_part = 'category_mini_' . $output_type;
		$tpl_component = array();
		$tpl_component['CATEGORY_LINK'] = $category_link;
		$tpl_component['CATEGORY_THUMBNAIL'] = $categoryThumbnail;
		$tpl_component['CATEGORY_TITLE'] = $category_title;
		$tpl_component['CATEGORY_DESCRIPTION'] = $category_more_informations;
		$tpl_component['ITEM_WIDTH'] = $item_width;
		$tpl_component['CATEGORY_ID'] = ( !empty($category) && !empty($category->term_id) ) ? $category->term_id : '';
		$tpl_component['CATEGORY_DISPLAY_TYPE'] = $output_type;

		/*
		 * Build template
		 */
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$content = ob_get_contents();
			ob_end_clean();
		}
		else {
			$content = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $content;
	}

	/**
	* Traduit le shortcode et affiche une cat�gorie
	* @param array $atts : tableau de param�tre du shortcode
	* @return mixed
	**/
	function wpshop_category_func($atts) {
		global $wpdb;
		$string = '';
		if ( !empty($atts['cid']) ) {
			$atts['type'] = (!empty($atts['type']) && in_array($atts['type'],array('grid','list'))) ? $atts['type'] : 'grid';

			$cat_list = explode(',', $atts['cid']);

			if ( (count($cat_list) > 1) || ( !empty($atts['display']) && ($atts['display'] == 'only_cat') ) ) {
				$string .= '
					<div class="wpshop_categories_' . $atts['type'] . '" >';
					foreach( $cat_list as $cat_id ){
						$sub_category_def = get_term($cat_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
						$string .= wpshop_categories::category_mini_output($sub_category_def, $atts['type']);
					}
				$string .= '
					</div>';
			}
			else {
				$sub_category_def = get_term($atts['cid'], WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);

				if ( empty($atts['display']) || ($atts['display'] != 'only_products') ){
					$string .= wpshop_categories::category_mini_output($sub_category_def, $atts['type']);
					$string .= '
					<div class="category_product_' . $atts['type'] . '" >
						<h2 class="category_content_part_title" >'.__('Category\'s product list', 'wpshop').'</h2>';
				}

				$string .= wpshop_products::wpshop_products_func($atts);

				if ( empty($atts['display']) || ($atts['display'] != 'only_products') ){
					$string .= '</div>';
				}
			}
		}
		else {
			$string .= __('No categories found for display', 'wpshop');
		}

		return do_shortcode($string);
	}

	function get_product_of_category( $category_id ) {
		$product_id_list = array();
		if ( !empty($category_id) ) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT T.* FROM " . $wpdb->term_relationships . " AS T INNER JOIN " . $wpdb->posts . " AS P ON ((P.ID = T.object_id) AND (P.post_status = %s)) WHERE T.term_taxonomy_id = %d ", 'publish', $category_id);
			$relationships = $wpdb->get_results($query);
			if ( !empty($relationships) ) {
				foreach ( $relationships as $relationship ) {
					$product_id_list[] = $relationship->object_id;
				}
			}
		}
		return $product_id_list;
	}


}