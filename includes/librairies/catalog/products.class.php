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
class wpshop_products
{
	/**
	*	Define the current entity code
	*/
	const currentPageCode = 'product';

	/**
	*	Call wordpress function that declare a new post type in order to define the product as wordpress post
	*
	*	@see register_post_type()
	*/
	function create_wpshop_products_type(){
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array(
			'labels' => array(
				'name' => __('Catalog', 'wpshop'),
				'singular_name' => __('Catalog', 'wpshop'),
				'add_new_item' => __('Add new product', 'wpshop'),
				'add_new' => __( 'Add new product', 'wpshop' ),
				'add_new_item' => __('Add new product', 'wpshop' ),
				'edit_item' => __('Edit product', 'wpshop' ),
				'new_item' => __('New product', 'wpshop' ),
				'view_item' => __('View product', 'wpshop' ),
				'search_items' => __('Search products', 'wpshop' ),
				'not_found' =>  __('No products found', 'wpshop' ),
				'not_found_in_trash' => __( 'No products found in Trash', 'wpshop' ),
				'parent_item_colon' => ''
			),
			'supports' => array('title', 'editor', 'excerpt'),
			'public' => true,
			'has_archive' => true,
			'show_in_nav_menus' => true,
			'rewrite' => array('slug' => 'catalog/%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%'),
			'taxonomies' => array(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES),
			'menu_icon' => WPSHOP_MEDIAS_URL . "icones/logo.png"
		));
	}


	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes(){
		global $post, $currentTabContent;

		add_meta_box('wpshop_product_main_infos', __('Main information', 'wpshop'), array('wpshop_products', 'main_information_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high');

		// add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');

		$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code('product'));
		if(count($attributeEntitySetList) == 1){
			$currentTabContent = wpshop_attributes::getAttributeFieldOutput($attributeEntitySetList[0]->id, self::currentPageCode, $post->ID);
			/*	Get all the other attribute set for hte current entity	*/
			if(isset($currentTabContent['box']) && count($currentTabContent['box']) > 0){
				foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
					add_meta_box('wpshop_product_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default', array('boxIdentifier' => $boxIdentifier));
				}
			}
		}
	}
	/**
	*	Define the content of the product main information box
	*/
	function main_information_meta_box_content(){
		global $currentTabContent;

		add_action('admin_footer', array('wpshop_init', 'admin_js_footer'));

		/*	Add the extra fields defined by the default attribute group in the general section	*/
			/*	Get the general attribute set for outputting the result	*/
		if(is_array($currentTabContent['generalTabContent'])){
				$the_form_general_content .= implode('
			', $currentTabContent['generalTabContent']);
			echo '<div class="wpshop_extra_field_container" >' . $the_form_general_content . '</div>';
		}
	}
	/**
	*	Define the metabox for managing products pictures
	*/
	function meta_box_picture($post, $metaboxArgs){
		global $post;

		echo '<a href="media-upload.php?post_id=' . $post->ID . '&type=image&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images">' . __( 'Product main picture', 'wpshop' ) . '</a>';
	}
	/**
	*	Define the content of the product main information box
	*/
	function meta_box_content($post, $metaboxArgs){
		global $currentTabContent;

		/*	Add the extra fields defined by the default attribute group in the general section	*/
		echo '<div class="wpshop_extra_field_container" >' . $currentTabContent['boxContent'][$metaboxArgs['args']['boxIdentifier']] . '</div>';
	}


	/**
	*	Save the different values for the attributes affected to the product
	*/
	function save_product_eav_informations(){
		if(isset($_REQUEST[self::currentPageCode . '_attribute']) && (count($_REQUEST[self::currentPageCode . '_attribute']) > 0)){
			/*	Save the attributes values into wpshop eav database	*/
			wpshop_attributes::saveAttributeForEntity($_REQUEST[self::currentPageCode . '_attribute'], wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $_REQUEST['post_ID'], get_locale());

			/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
			$productMetaDatas = array();
			foreach($_REQUEST[self::currentPageCode . '_attribute'] as $attributeType => $attributeValues){
				foreach($attributeValues as $attributeCode => $attributeValue){
					$productMetaDatas[$attributeCode] = $attributeValue;
				}
			}
			update_post_meta($_REQUEST['post_ID'], '_wpshop_product_metadata', serialize($productMetaDatas));
		}
		flush_rewrite_rules();
	}
	/**
	*	
	*/
	function set_product_permalink($permalink, $post, $leavename){

		global $wp_query;
		$product_category_slug = WPSHOP_UNCATEGORIZED_PRODUCT_SLUG;

		if ($post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) 
			return $permalink;

		$product_categories = wp_get_object_terms( $post->ID, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES );
		if(count($product_categories) == 1){/*	Product has only one category we get the only available slug	*/
			$product_category_slug = $product_categories[0]->slug;
		}
		else{																/*	Product has several categories choose the slug of the we want	*/
			$product_category_slugs = array();
			foreach($product_categories as $product_category){
				$product_category_slugs[] = $product_category->slug;
			}
			// echo '<pre>';print_r($product_category_slugs);echo '</pre>';
			$product_category_slug = 'product';
		}

		$permalink = str_replace('%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%', $product_category_slug, $permalink);
		return apply_filters('wpshop_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_permalink', $permalink, $post->ID );
	}

	/**
	*	Define output for product
	*
	*	@param mixed $initialContent The initial product content defined into wordpress basic admin interface
	*	@param integer $product_id The product identifier we want to get and output attribute for
	*
	*	@return mixed $content The content to add or to modify the product output in frontend
	*/
	function product_complete_sheet_output($initialContent, $product_id){
		$content = $attributeContentOutput = '';

		/*	Get the product thumbnail	*/
		if(has_post_thumbnail($product_id)){
			$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
			$productThumbnail = '<a href="' . $thumbnail_url[0] . '" id="product_thumbnail" >' . get_the_post_thumbnail($product_id, 'thumbnail') . '</a>';
		}
		else{
			$productThumbnail = '<img src="' . WPSHOP_DEFAULT_PRODUCT_PICTURE . '" alt="product has no image" class="default_picture_thumbnail" />';
		}

		/*	Get attachement file for the current product	*/
		$product_picture_galery = $product_document_galery = '';
		$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id));
		if(is_array($attachments) && (count($attachments) > 0)){
			$picture_number = $document_number = 0;
			foreach ($attachments as $attachment){
				if(is_int(strpos($attachment->post_mime_type, 'image/'))){
					$product_picture_galery .= '<li class="product_picture_item" ><a href="' . $attachment->guid . '" rel="appendix" >' . wp_get_attachment_image($attachment->ID, 'full') . '</a></li>';
					$picture_number++;
				}
				if(is_int(strpos($attachment->post_mime_type, 'application/pdf'))){
					$product_document_galery .= '<li class="product_document_item" ><a href="' . $attachment->guid . '" target="product_document" >' . wp_get_attachment_image($attachment->ID, 'full', 1) . '<br/><span>' . $attachment->post_name . '</span></a></li>';
					$document_number++;
				}
			}
			if($picture_number > 0){
				$product_picture_galery = '<h2 class="product_picture_galery_title" >' . __('Associated pictures', 'wpshop') . '</h2><ul class="product_picture_galery" >' . $product_picture_galery . '</ul>';
			}
			else{
				$product_picture_galery = '&nbsp;';
			}
			if($document_number > 0){
				$product_document_galery = '<h2 class="product_document_galery_title" >' . __('Associated document', 'wpshop') . '</h2><ul class="product_document_galery" >' . $product_document_galery . '</ul>';
			}
			else{
				$product_document_galery = '&nbsp;';
			}
		}

		/*	Get the different attribute affected to the product	*/
		$product_atribute_list = wpshop_attributes::getElementWithAttributeAndValue(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $product_id, get_locale(), '', 'frontend');
		if(is_array($product_atribute_list) && (count($product_atribute_list) > 0)){
			foreach($product_atribute_list[$product_id] as $attributeSetSectionName => $attributeSetContent){
				$attributeToShowNumber = 0;
				$attributeOutput = '';
				foreach($attributeSetContent['attributes'] as $attributeId => $attributeDefinition){
					/*	Check the value type to check if empty or not	*/
					if($attributeDefinition['data_type'] == 'int'){
						$attributeDefinition['value'] = (int)$attributeDefinition['value'];
					}
					elseif($attributeDefinition['data_type'] == 'decimal'){
						$attributeDefinition['value'] = (float)$attributeDefinition['value'];
					}

					/*	Output the field if the value is not null	*/
					if((trim($attributeDefinition['value']) != '') && ($attributeDefinition['value'] > '0')){
						$attribute_unit_list = '';
						if(($attributeDefinition['unit'] != '')){
							$attribute_unit_list = '&nbsp;(' . $attributeDefinition['unit'] . ')';
						}
						$attribute_value = $attributeDefinition['value'];
						if($attributeDefinition['data_type'] == 'datetime'){
							$attribute_value = mysql2date('d/m/Y', $attributeDefinition['value'], true);
						}
						$attributeOutput .= '<li><span class="' . self::currentPageCode . '_frontend_attribute_label ' . $attributeDefinition['attribute_code'] . '_label" >' . __($attributeDefinition['frontend_label'], 'wpshop') . '</span>&nbsp;:&nbsp;<span class="' . self::currentPageCode . '_frontend_attribute_value ' . $attributeDefinition['attribute_code']. '_value" >' . $attribute_value . $attribute_unit_list . '</span></li>';

						$attributeToShowNumber++;
					}
				}

				/*	If there are attribute to output add to the content	*/
				if($attributeToShowNumber > 0){
					$attributeContentOutput .= '
<fieldset class="' . self::currentPageCode . '_assf ' . self::currentPageCode . '_assf_' . $attributeSetContent['code'] . '" >
	<legend class="' . self::currentPageCode . '_assfl ' . self::currentPageCode . '_assff_' . $attributeSetContent['code'] . '" >' . __($attributeSetSectionName , 'wpshop') . '</legend>
	<ul class="' . self::currentPageCode . '_attribute_set_section ' . self::currentPageCode . '_attribute_set_section_' . $attributeSetContent['code'] . '" >' . $attributeOutput . '</ul>
</fieldset>';
				}
			}
		}

		/*	Include the product sheet template	*/
		ob_start();
		require_once(WPSHOP_TEMPLATES_DIR . 'product.tpl.php');
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	/**
	*	Display a product inot a list
	*/
	function product_mini_output($product_id, $category_id, $output_type = 'list'){
		$content = $product_information = '';

		/*	Get the product thumbnail	*/
		if(has_post_thumbnail($product_id)){
			$productThumbnail = get_the_post_thumbnail($product_id, 'thumbnail');
		}
		else{
			$productThumbnail = '<img src="' . WPSHOP_DEFAULT_PRODUCT_PICTURE . '" alt="product has no image" class="default_picture_thumbnail" />';
		}

		/*	Get the product information for output	*/
		$product = get_post($product_id);
		$product_title = $product->post_title;
		$product_link = get_term_link((int)$category_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) . '/' . $product->post_name;
		$product_more_informations = $product->post_content;

		/*	Make some treatment in case we are in grid mode	*/
		if($output_type == 'grid'){
			/*	Determine the width of a component in a line grid	*/
			$element_width = (100 / WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE);
			$item_width = (round($element_width) - 1) . '%';
		}

		/*	Include the product sheet template	*/
		ob_start();
		require(WPSHOP_TEMPLATES_DIR . 'product-mini-' . $output_type . '.tpl.php');
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	*	Get the products (post) of a given category
	*
	*	@param string $category_slug The category slug we want to get the product list for
	*
	*	@return mixed $widget_content The output for the product list
	*/
	function get_product_of_category($category_slug, $category_id){
		global $top_categories;
		$widget_content = '';

		$args = array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES => $category_slug);
		$products = get_posts($args);
		if(is_array($products) && (count($products) > 0)){
			foreach($products as $product){
				ob_start();
				include(WPSHOP_TEMPLATES_DIR . 'categories_products-widget.tpl.php');
				$widget_content .= ob_get_contents();
				ob_end_clean();
			}
		}

		echo $widget_content;
	}

}