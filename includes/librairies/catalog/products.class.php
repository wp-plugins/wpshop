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
 * This file contains the different methods for products management
 * @author Eoxia <dev@eoxia.com>
 * @version 1.1
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_products {
	/**
	*	Définition du code de la classe courante
	*/
	const currentPageCode = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
	const current_page_variation_code = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION;


	function add_product_to_current_order_interface() {
		$output  = '';
		$output .= self::products_list_js();
		$output .= '<input type="text" id="wps_order_search_product" />';
		echo $output;
		die();
	}

	function products_list_js () {
		global $wpdb;
		/** Create a JS Array of products **/
// 		$query = $wpdb->prepare('SELECT ID, post_title FROM ' .$wpdb->posts. '  WHERE post_type = %s AND post_status = %s', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish');
// 		$products_post = $wpdb->get_results( $query );

		$products_post = get_posts( array('post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'post_status' => 'publish', 'posts_per_page' => -1 ) );

		if ( !empty( $products_post ) ) {
			$products_js_array  = 'var products = [';
			foreach ( $products_post as $product ) {
				$barcode = get_post_meta( $product->ID, '_barcode', true);
				$products_js_array .= '{label:"#' .$product->ID. ' ' .str_replace('"', '', $product->post_title). ' - ' .( (!empty($barcode) ) ? $barcode : '' ). '", ';
				$products_js_array .= 'value:"' .$product->ID. '", ';
				$products_js_array .= ( !empty($barcode) ) ? 'desc:"' .$barcode. '"},' : '},';
			}
			$products_js_array .= '];';
		}
		$output = wpshop_display::display_template_element('wps_orders_products_list_js', array('PRODUCTS_JS_ARRAY' => $products_js_array) , array(), 'admin');
		return $output;
	}


	/**
	*	Déclaration des produits et variations en tant que "post" de wordpress
	*
	*	@see register_post_type()
	*/
	function create_wpshop_products_type() {

		/*	Définition des produits 	*/
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array(
			'labels' => array(
				'name'					=> __( 'Products', 'wpshop' ),
				'singular_name' 		=> __( 'Catalog', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new product', 'wpshop' ),
				'add_new' 				=> __( 'Add new product', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new product', 'wpshop' ),
				'edit_item' 			=> __( 'Edit product', 'wpshop' ),
				'new_item' 				=> __( 'New product', 'wpshop' ),
				'view_item' 			=> __( 'View product', 'wpshop' ),
				'search_items' 			=> __( 'Search products', 'wpshop' ),
				'not_found' 			=> __( 'No products found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No products found in Trash', 'wpshop' ),
				'parent_item_colon' 	=> ''
			),
			'supports' 				=> unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT),
			'public' 				=> true,
			'has_archive'			=> true,
			'show_in_nav_menus' 	=> true,
			// 'rewrite' 			=> false,	//	For information see below
			'taxonomies' 			=> array( WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES ),
			'menu_icon' 			=> WPSHOP_MEDIAS_URL . "icones/wpshop_menu_icons.png"
		));

		/*	Définition des variations de produit (Déclinaisons)	*/
		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, array(
			'labels'				=> array(
				'name' 					=> __( 'Variations', 'wpshop' ),
				'singular_name' 		=> __( 'Variation', 'wpshop' ),
				'add_new' 				=> __( 'Add Variation', 'wpshop' ),
				'add_new_item' 			=> __( 'Add New Variation', 'wpshop' ),
				'edit' 					=> __( 'Edit', 'wpshop' ),
				'edit_item' 			=> __( 'Edit Variation', 'wpshop' ),
				'new_item' 				=> __( 'New Variation', 'wpshop' ),
				'view' 					=> __( 'View Variation', 'wpshop' ),
				'view_item' 			=> __( 'View Variation', 'wpshop' ),
				'search_items' 			=> __( 'Search Variations', 'wpshop' ),
				'not_found' 			=> __( 'No Variations found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No Variations found in trash', 'wpshop' ),
				'parent_item_colon' 	=> ''
			),
			'supports' 				=> unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT),
			'public' 				=> true,
			'has_archive'			=> true,
			'show_in_nav_menus' 	=> false,
			'show_in_menu' 			=> 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,

			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'hierarchical' 			=> false,

// 			'public' 				=> true,
// 			'show_ui' 				=> false,
// 			'rewrite' 				=> false,
// 			'query_var'				=> true,
// 			'supports' 				=> array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
// 			'show_in_nav_menus' 	=> false
			)
		);

		// add to our plugin init function
		global $wp_rewrite;
		/*	Slug url is set into option	*/
		$options = get_option('wpshop_catalog_product_option', array());
		$gallery_structure = (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : 'catalog');
		$gallery_structure .= !empty($options['wpshop_catalog_product_slug_with_category']) ? '/%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%' : '';
		$gallery_structure .= '/%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%';
		$wp_rewrite->add_permastruct(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $gallery_structure, false);
		$wp_rewrite->add_rewrite_tag('%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%', '([^/]+)', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "=");
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		global $post, $currentTabContent;

		if(!empty($post->post_type) && ( ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) || ($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) ) ) {
			/*	Get the attribute set list for the current entity	*/
			$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
			/*	Check if the meta information of the current product already exists 	*/
			$post_attribute_set_id = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
			/*	Check if the product has been saved without meta information set	*/
			$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);

			/*	Check if an attribute has already been choosen for the curernt entity or if the user has to choose a entity set before continuing	*/
			if(((count($attributeEntitySetList) == 1) || ((count($attributeEntitySetList) > 1) && (($post_attribute_set_id > 0) || (isset($attribute_set_id->value) && ($attribute_set_id->value > 0)))))){
				if((count($attributeEntitySetList) == 1) || (($post_attribute_set_id <= 0) && ($attribute_set_id->value <= 0))){
					$post_attribute_set_id = $attributeEntitySetList[0]->id;
				}
				elseif(($post_attribute_set_id <= 0) && ($attribute_set_id->value > 0)){
					$post_attribute_set_id = $attribute_set_id->value;
				}

				$currentTabContent = wpshop_attributes::entities_attribute_box($post_attribute_set_id, self::currentPageCode, $post->ID);

				$fixed_box_exist = false;
				/*	Get all the other attribute set for hte current entity	*/
				if(isset($currentTabContent['box']) && count($currentTabContent['box']) > 0){
					foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
						if(!empty($currentTabContent['box'][$boxIdentifier.'_backend_display_type']) &&( $currentTabContent['box'][$boxIdentifier.'_backend_display_type'] == 'movable-tab')){
							add_meta_box('wpshop_product_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default', array('boxIdentifier' => $boxIdentifier));
						}
						else $fixed_box_exist = true;
					}
				}
				if ( $fixed_box_exist ) {
					add_meta_box('wpshop_product_fixed_tab', __('Product data', 'wpshop'), array('wpshop_products', 'product_data_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high', array('currentTabContent' => $currentTabContent));
					add_meta_box('wpshop_product_fixed_tab', __('Product data', 'wpshop'), array('wpshop_products', 'product_data_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'normal', 'high', array('currentTabContent' => $currentTabContent));
				}

				add_meta_box('wpshop_wpshop_variations', __('Product variation', 'wpshop'), array('wpshop_products', 'meta_box_variations'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				// Actions
				add_meta_box('wpshop_product_actions', __('Actions', 'wpshop'), array('wpshop_products', 'product_actions_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');

				/**	Product option	*/
				add_meta_box('wpshop_product_options', __('Otions', 'wpshop'), array('wpshop_products', 'product_options_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');
			}
			else if ( count($attributeEntitySetList) > 1 ) {
				$input_def['id'] = 'product_attribute_set_id';
				$input_def['name'] = 'product_attribute_set_id';
				$input_def['value'] = '';
				$input_def['type'] = 'select';
				$input_def['possible_value'] = $attributeEntitySetList;

				$input_def['value'] = '';
				foreach ($attributeEntitySetList as $set) {
					if( $set->default_set == 'yes' ) {
						$input_def['value'] = $set->id;
					}
				}

				$currentTabContent['boxContent']['attribute_set_selector'] = '
	<ul class="attribute_set_selector" >
		<li class="attribute_set_selector_title_select" ><label for="title" >' . __('Choose a title for your product', 'wpshop') . '</label></li>
		<li class="attribute_set_selector_group_selector" ><label for="' . $input_def['id'] . '" >' . __('Choose an attribute group for this product', 'wpshop') . '</label>&nbsp;'.wpshop_form::check_input_type($input_def, self::currentPageCode.'_attribute[integer]').'</li>
		<li class="attribute_set_selector_save_instruction" >' . __('Save the product with the "Save draft" button on the right side', 'wpshop') . '</li>
		<li class="attribute_set_selector_after_save_instruction" >' . __('Once the group chosen, the different attribute will be displayed here', 'wpshop') . '</li>
	</ul>';

				add_meta_box('wpshop_product_attribute_set_selector', __('Product attributes', 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high', array('boxIdentifier' => 'attribute_set_selector'));
			}

			add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
			add_meta_box('wpshop_product_document_management', __('Document management', 'wpshop'), array('wpshop_products', 'meta_box_document'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
		}

	}

	/**
	 * Add a box into product edition page for options on the product
	 *
	 * @param object $post
	 */
	function product_options_meta_box_content( $post ) {
		$output = '';

		$product_current_options = get_post_meta( $post->ID, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options', true);

		$tpl_component = array();
		$tpl_component['ADMIN_PRODUCT_OPTION_FOR_CART_AUTOADD_CHECKBOX_STATE'] = (!empty($product_current_options['cart']) && !empty($product_current_options['cart']['auto_add'])) ? ' checked="checked"' : '';
		$output .= wpshop_display::display_template_element('wpshop_admin_product_option_for_cart', $tpl_component, array('type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'id' => $post->ID), 'admin');

		echo $output;
	}

	/**
	 * Display the fixed box
	 */
	function product_data_meta_box($post, $metaboxArgs) {
		$output = '';

		$currentTabContent = $metaboxArgs['args']['currentTabContent'];

		echo '<div id="fixed-tabs" class="wpshop_tabs wpshop_detail_tabs wpshop_product_attribute_tabs" >
				<ul>';
		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<li><a href="#tabs-'.sanitize_title($boxIdentifier).'">'.__($boxTitle, 'wpshop').'</a></li>';
					}
				}
			}
		}
		echo '<li><a href="#tabs-product-related">'.__('Related products', 'wpshop').'</a></li>';
		echo '<li class="wpshop_product_data_display_tab" ><a href="#tabs-product-display">'.__('Product display', 'wpshop').'</a></li>';
		echo '</ul>';

		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<div id="tabs-'.sanitize_title($boxIdentifier).'">'.$currentTabContent['boxContent'][$boxIdentifier].'</div>';
					}
				}
			}
		}

		echo '<div id="tabs-product-related">' . self::related_products_meta_box_content($post) . '</div>';
		echo '<div id="tabs-product-display">' . self::product_frontend_display_config_meta_box($post) . '</div>';
		if (!empty($currentTabContent['boxMore'])) {
			echo $currentTabContent['boxMore'];
		}
		echo '</div>';

		echo $output;
	}

	/**
	 * Output the content for related product metabox
	 * @param object $post The current edited post
	 * @return string
	 */
	function related_products_meta_box_content( $post ) {
		$content = $existing_selection = '';

		if( !empty($post->ID) ) {
			$related_products_id = get_post_meta($post->ID, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
			if( !empty($related_products_id) && !empty($related_products_id[0]) ) {
				foreach ($related_products_id as $related_product_id) {
					$existing_selection .= '<option selected value="' . $related_product_id . '" >' . get_the_title($related_product_id) . '</option>';
				}
			}
		}

		$content = '<p>' . __('Type the begin of the product name in the field below in order to add it to the related product list', 'wpshop') . '</p>
			<select name="related_products_list[]" id="related_products_list" class="ajax_chosen_select" multiple >' . $existing_selection . '</select>
			<input type="hidden" id="wpshop_ajax_search_element_type" name="wpshop_ajax_search_element_type" value="' . $post->post_type . '" />
			<input type="hidden" id="wpshop_nonce_ajax_search" name="wpshop_nonce_ajax_search" value="' . wp_create_nonce("wpshop_element_search") . '" />';

		return $content;
	}

	/**
	 * Define the metabox content for the action box
	 * @param obejct $post The current element being edited
	 */
	function product_actions_meta_box_content( $post ) {
		$output = '';
		/*
		 * Template parameters
		*/
		$template_part = 'wpshop_duplicate_product';
		$tpl_component = array();
		$tpl_component['PRODUCT_ID'] = $post->ID;

		/*
		 * Build template
		*/
		$output = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');
		unset($tpl_component);

		echo $output;
	}

	/**
	 *	Define the metabox for managing products pictures
	 */
	function meta_box_picture($post, $metaboxArgs){
		global $post;
		$product_picture_galery_metabox_content = '';

		$product_picture_galery_metabox_content = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox" title="Manage Your Product Images" >' . __('Add pictures for the product', 'wpshop' ) . '</a>
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_picture" /></div>
<ul id="product_picture_list" class="product_attachment_list product_attachment_list_box_picture wpshop_cls" >' . self::product_attachement_by_type($post->ID, 'image/', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=gallery&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

		echo $product_picture_galery_metabox_content;
	}

	/**
	 *	Define the metabox for managing products documents
	 */
	function meta_box_document($post, $metaboxArgs){
		$output = '';

		$output = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox wpshop_cls" title="Manage Your Product Document" >' . __('Add documents for the document', 'wpshop' ) . '</a> (Seuls les documents <i>.pdf</i> seront pris en compte)
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_document" /></div>
<ul id="product_document_list" class="product_attachment_list product_attachment_list_box_document wpshop_cls" >' . self::product_attachement_by_type($post->ID, 'application/pdf', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=library&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

		echo $output;
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
	 * Define the metabox content for product custom display in product
	 * @param object $post The current element being edited
	 * @return string The metabox content
	 */
	function product_frontend_display_config_meta_box( $post ) {
		$content = '';

		$product_attribute_frontend_display_config = null;
		if( !empty($post->ID) ) {
			$product_attribute_frontend_display_config = get_post_meta($post->ID, WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);

			$extra_options = get_option('wpshop_extra_options', array());
			$column_count = (!empty($extra_options['WPSHOP_COLUMN_NUMBER_PRODUCT_EDITION_FOR_FRONT_DISPLAY'])?$extra_options['WPSHOP_COLUMN_NUMBER_PRODUCT_EDITION_FOR_FRONT_DISPLAY']:3);
			$attribute_list = wpshop_attributes::getElementWithAttributeAndValue(wpshop_entities::get_entity_identifier_from_code( self::currentPageCode ), $post->ID, WPSHOP_CURRENT_LOCALE);
			$column = 1;

			if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' ) {
				$sub_tpl_component = array();
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_NAME'] = __('Action on product', 'wpshop');

				$tpl_component = array();
				$tpl_component['ADMIN_ATTRIBUTE_LABEL'] = __('Add to cart button', 'wpshop');
				$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[product_action_button][add_to_cart]';
				$tpl_component['ADMIN_ATTRIBUTE_FD_ID'] = $post->ID . '_product_action_button_add_to_cart';
				$button_is_set_to_be_displayed = (WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no';
				$tpl_component['ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK'] = wpshop_attributes::check_attribute_display( $button_is_set_to_be_displayed, $product_attribute_frontend_display_config, 'product_action_button', 'add_to_cart', 'complete_sheet') ? ' checked="checked"' : '';
				$tpl_component['ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK'] = wpshop_attributes::check_attribute_display( $button_is_set_to_be_displayed, $product_attribute_frontend_display_config, 'product_action_button', 'add_to_cart', 'mini_output') ? ' checked="checked"' : '';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_CONTENT'] = wpshop_display::display_template_element('wpshop_admin_attr_config_for_front_display', $tpl_component, array(), 'admin');
				unset($tpl_component);

				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[product_action_button][add_to_cart]';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_ID'] = 'product_action_button_add_to_cart';
				$sub_tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_INPUT_CHECKBOX'] = '';
				$sub_content[1] = wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display', $sub_tpl_component, array(), 'admin');
			}

			if ( !empty($attribute_list[$post->ID]) && is_array($attribute_list[$post->ID]) ) {
				foreach ( $attribute_list[$post->ID] as $attribute_set_section_name => $attribute_set_section_content ) {
					if ( !isset($sub_content[$column]) ) {
						$sub_content[$column] = '';
					}

					$attribute_sub_output = '';
					foreach ( $attribute_set_section_content['attributes'] as $attribute_id => $attribute_def ) {
						if ( $attribute_def['attribute_code'] != 'product_attribute_set_id' ) {
							$tpl_component = array();
							$tpl_component['ADMIN_ATTRIBUTE_LABEL'] = $attribute_def['frontend_label'];
							$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[attribute][' . $attribute_def['attribute_code'] . ']';
							$tpl_component['ADMIN_ATTRIBUTE_FD_ID'] = $post->ID . '_' . $attribute_def['attribute_code'];
							$tpl_component['ADMIN_ATTRIBUTE_COMPLETE_SHEET_CHECK'] = wpshop_attributes::check_attribute_display( $attribute_def['is_visible_in_front'], $product_attribute_frontend_display_config, 'attribute', $attribute_def['attribute_code'], 'complete_sheet') ? ' checked="checked"' : '';
							$tpl_component['ADMIN_ATTRIBUTE_MINI_OUTPUT_CHECK'] = wpshop_attributes::check_attribute_display( $attribute_def['is_visible_in_front_listing'], $product_attribute_frontend_display_config, 'attribute', $attribute_def['attribute_code'], 'mini_output') ? ' checked="checked"' : '';
							$attribute_sub_output .= wpshop_display::display_template_element('wpshop_admin_attr_config_for_front_display', $tpl_component, array(), 'admin');
							unset($tpl_component);
						}
					}

					$tpl_component = array();
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_NAME'] = $attribute_set_section_name;
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_CONTENT'] = $attribute_sub_output;
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_NAME'] = self::currentPageCode . '_attr_frontend_display[attribute_set_section][' . $attribute_set_section_content['code'] . ']';
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_FD_ID'] = $attribute_set_section_content['code'];
					$ADMIN_ATTRIBUTE_SET_SECTION_COMPLETE_SHEET_CHECK = wpshop_attributes::check_attribute_display( $attribute_set_section_content['display_on_frontend'], $product_attribute_frontend_display_config, 'attribute_set_section', $attribute_set_section_content['code'], 'complete_sheet') ? ' checked="checked"' : '';
					$tpl_component['ADMIN_ATTRIBUTE_SET_SECTION_INPUT_CHECKBOX'] = '<input type="checkbox" name="' .  self::currentPageCode . '_attr_frontend_display[attribute_set_section][' . $attribute_set_section_content['code'] . '][complete_sheet]" id="' .  $attribute_set_section_content['code'] . '_complete_sheet" value="yes"' . $ADMIN_ATTRIBUTE_SET_SECTION_COMPLETE_SHEET_CHECK . ' /><label for="' .  $attribute_set_section_content['code'] . '_complete_sheet" >' . __('Display in product page', 'wpshop') . '</label>';
					$sub_content[$column] .= wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display', $tpl_component, array(), 'admin');
					$column++;
					if ( $column > $column_count ){
						$column = 1;
					}
				}
			}
			$tpl_component = array();
			$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT'] = '';
			for ( $i=1; $i<=$column_count; $i++ ) {
				if (!empty($sub_content[$i]))
					$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT'] .= '<div class="alignleft" >' . $sub_content[$i] . '</div>';
			}
			$tpl_component['ADMIN_ATTRIBUTE_FRONTEND_DISPLAY_CONTENT_CLASS'] = empty($product_attribute_frontend_display_config) ? ' class="wpshopHide" ' : '';
			$tpl_component['ADMIN_PRODUCT_ATTRIBUTE_FRONTEND_DISPLAY_MAIN_CHOICE_CHECK'] = empty($product_attribute_frontend_display_config) ? ' checked="checked"' : '';
			$tpl_component['ADMIN_ATTRIBUTE_FD_NAME'] = self::currentPageCode . '_attr_frontend_display';

			$content = wpshop_display::display_template_element('wpshop_admin_attr_set_section_for_front_display_default_choice',$tpl_component, array(), 'admin') . '<div class="wpshop_cls"></div>';
		}

		return $content;
	}

	/**
	 * Retrieve the attribute list used for sorting product into frontend listing
	 * @return array The attribute list to use for listing sorting
	 */
	function get_sorting_criteria() {
		global $wpdb;

		$data = array(array('code' => 'title', 'frontend_label' => __('Product name', 'wpshop')), array('code' => 'date', 'frontend_label' => __('Date added', 'wpshop')), array('code' => 'modified', 'frontend_label' => __('Date modified', 'wpshop')));

		$query = $wpdb->prepare('SELECT code, frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE is_used_for_sort_by="yes"', '');
		$results = $wpdb->get_results($query, ARRAY_A);
		if(!empty($results))$data = array_merge($data, $results);

		return $data;
	}

	function get_products_matching_attribute($attr_name, $attr_value) {
		global $wpdb;

		$products = array();
		$query = "SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code=%s";
		$data = (array)$wpdb->get_row($wpdb->prepare($query, $attr_name));

		if(!empty($data)) {
			if ($data['data_type_to_use'] == 'custom' ) {
				// Find which table to take
				if($data['data_type']=='datetime') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME; }
				elseif($data['data_type']=='decimal') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL; }
				elseif($data['data_type']=='integer') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER; }
				elseif($data['data_type']=='options') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS; }
				elseif($data['data_type']=='text') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT; }
				elseif($data['data_type']=='varchar') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR; }

				if(isset($table_name)) {
					// If the value is an id of a select, radio or checkbox
					if(in_array($data['backend_input'], array('select','multiple-select', 'radio','checkbox'))) {

						$query = $wpdb->prepare("
							SELECT ".$table_name.".entity_id FROM ".$table_name."
							LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
							LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS." AS ATT_OPT ON ".$table_name.".value = ATT_OPT.id
							WHERE ATT.code=%s AND ATT_OPT.value=%s", $attr_name, $attr_value
						);
						$data = $wpdb->get_results($query);

					}
					else {

						$query = $wpdb->prepare("
							SELECT ".$table_name.".entity_id FROM ".$table_name."
							INNER JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
							WHERE ATT.code=%s AND ".$table_name.".value=%s", $attr_name, sprintf('%.5f', $attr_value) // force useless zero like 48.58000
						);
						$data = $wpdb->get_results($query);

					}
				}
				else return __('Incorrect shortcode','wpshop');
			}
			elseif( $data['data_type_to_use'] == 'internal' )  {
				/** Check the ID of manufacturer **/
				$default_value = unserialize( $data['default_value'] );
				if( !empty($default_value) && !empty($default_value['default_value']) ) {
					$query = $wpdb->prepare( 'SELECT ID FROM '.$wpdb->posts. ' WHERE post_type = %s AND post_title=%s', $default_value['default_value'], $attr_value );
					$pid = $wpdb->get_var( $query );
					if ( !empty($pid) ) {
						$query = $wpdb->prepare( 'SELECT post_id AS entity_id FROM '.$wpdb->postmeta.' WHERE meta_key = %s AND meta_value = %s', '_'.$data['code'], $pid);
						$data = $wpdb->get_results( $query );
					}
				}
			}else return __('Incorrect shortcode','wpshop');

		} else return __('Incorrect shortcode','wpshop');

		if(!empty($data)) {
			foreach($data as $p) {
				$products[] = $p->entity_id;
			}
		}
		return $products;
	}

	/**
	 * Related product shortcode reader
	 *
	 * @param array $atts {
	 *	pid : Product idenfifier to get related element for
	 *	display_mode : The output mode if defined (grid || list)
	 * }
	 *
	 * @return string
	 *
	 */
	function wpshop_related_products_func($atts) {
		global $wp_query;

		$atts['product_type'] = 'related';
		if(empty($atts['pid'])) $atts['pid'] = $wp_query->posts[0]->ID;

		return self::wpshop_products_func($atts);
	}

	/**
	* Display a list of product from a shortcode
	*
	* @param array $atts {
	*	limit : The number of element to display
	*	order : The information to order list by
	*	sorting : List order (ASC | DESC)
	*	display : Display size (normal | mini)
	*	type : Display tyep (grid | list) only work with display=normal
	*	pagination : The number of element per page
	* }
	*
	* @return string
	*
	**/
	function wpshop_products_func($atts) {
		global $wpdb;
		global $wp_query;


		$have_results = false;
		$output_results = true;
		$type = ( empty($atts['type']) OR !in_array($atts['type'], array('grid','list')) ) ? WPSHOP_DISPLAY_LIST_TYPE : $atts['type'];
		$pagination = isset($atts['pagination']) ? intval($atts['pagination']) : WPSHOP_ELEMENT_NB_PER_PAGE;
		$cid = !empty($atts['cid']) ? $atts['cid'] : 0;
		$pid = !empty($atts['pid']) ? $atts['pid'] : 0;
		$order_by_sorting = (!empty($atts['sorting']) && ($atts['sorting'] == 'DESC')) ? 'DESC' : 'ASC';
		$limit = isset($atts['limit']) ? intval($atts['limit']) : 0;
		$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;
		$attr = '';

		$sorting_criteria = self::get_sorting_criteria();

		/** Get products which have att_name equal to att_value	*/
		if (!empty($atts['att_name']) && !empty($atts['att_value'])) {
			$attr = $atts['att_name'].':'.$atts['att_value'];

			$products = self::get_products_matching_attribute($atts['att_name'], $atts['att_value']);

			// Foreach on the found products
			if ( !empty($products) ) {
				$pid = implode(',',$products);
				if(empty($pid))$output_results = false;
			}
			else $output_results = false;
		}

		/** Get related products	*/
		if (!empty($atts['product_type'])) {
			switch ($atts['product_type']) {
				case 'related':
					$product_id = !empty($atts['pid']) ? (int)$atts['pid'] : get_the_ID();
					$type = !empty($atts['display_mode']) && in_array($atts['display_mode'],array('list','grid')) ? $atts['display_mode'] : WPSHOP_DISPLAY_LIST_TYPE;
					$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;

					$pids = get_post_meta((int)$product_id, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
					if ( !empty($pids) && !empty($pids[0]) ) {
						$pid = implode(',', $pids);
					}
					if(empty($pid) || $pid == $product_id) {
						$output_results = false;
					}

				break;
			}
		}

		/** Output all the products	*/
		if ( $output_results ) {
			$data = self::wpshop_get_product_by_criteria((!empty($atts['order']) ? $atts['order'] : (!empty($atts['creator']) ? ($atts['creator'] == 'current') : '')), $cid, $pid, $type, $order_by_sorting, 1, $pagination, $limit, $grid_element_nb_per_line);

			if ( $data[0] ) {
				$have_results = true;
				$string = $data[1];
			}
		}

		/** If there are result to display	*/
		if ( $have_results ) {
			$sorting = '';
			if ( !empty($pid) ) {
				$product_list = explode(',', $pid);
				if ( count($product_list) == 1 ) {
					$atts['sorting'] = 'no';
				}
			}

			/*
			 * Template parameters
			 */
			$template_part = 'product_listing_sorting';
			$tpl_component = array();

			/*
			 * Build template
			*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$sorting = ob_get_contents();
				ob_end_clean();
			}
			else {
				/*
				 * Display hidden field every times
				 */
				$sub_template_part = 'product_listing_sorting_hidden_field';
				$sub_tpl_component = array();
				$sub_tpl_component['DISPLAY_TYPE'] = $type;
				$sub_tpl_component['ORDER'] = $order_by_sorting;
				$sub_tpl_component['PRODUCT_NUMBER'] = $pagination;
				$sub_tpl_component['CURRENT_PAGE'] = 1;
				$sub_tpl_component['CATEGORY_ID'] = $cid;
				$sub_tpl_component['PRODUCT_ID'] = $pid;
				$sub_tpl_component['ATTR'] = $attr;
				$tpl_component['SORTING_HIDDEN_FIELDS'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component, array(), 'admin');
				unset($sub_tpl_component);

				if ( (!empty($sorting_criteria) && is_array($sorting_criteria)) ) {
					$sub_template_part = 'product_listing_sorting_criteria';
					$sub_tpl_component = array();
					$criteria = '';
					foreach($sorting_criteria as $c):
						$criteria .= '<option value="' . $c['code'] . '">' . __($c['frontend_label'],'wpshop') . '</option>';
					endforeach;
					$sub_tpl_component['SORTING_CRITERIA_LIST'] = $criteria;
					$tpl_component['SORTING_CRITERIA'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component);
					unset($sub_tpl_component);
				}

				if ( empty($atts['sorting']) || ( !empty($atts['sorting']) && ($atts['sorting'] != 'no') ) ) {
					$tpl_component['DISPLAY_TYPE_STATE_GRID'] = $type == 'grid' ?' active' : null;
					$tpl_component['DISPLAY_TYPE_STATE_LIST'] = $type == 'list' ?' active' : null;
					$sorting = wpshop_display::display_template_element($template_part, $tpl_component);
				}
				else if ( !empty($atts['sorting']) && ($atts['sorting'] == 'no') ) {
					$sub_template_part = 'product_listing_sorting_criteria_hidden';
					$sub_tpl_component = array();
					$sub_tpl_component['CRITERIA_DEFAULT'] = !empty($sorting_criteria[0]['code']) ? $sorting_criteria[0]['code'] : 'title';
					$tpl_component['SORTING_CRITERIA'] = wpshop_display::display_template_element($sub_template_part, $sub_tpl_component, array(), 'admin');
					unset($sub_tpl_component);

					$template_part = 'product_listing_sorting_hidden';
					$sorting = wpshop_display::display_template_element($template_part, $tpl_component, array(), 'admin');
				}
			}
			unset($tpl_component);

			if ( !empty( $atts) && !empty($atts['container']) && $atts['container'] == 'no') {
				$string = $sorting.'<div class="wpshop_product_container">'.$string.'</div>';
			}
			else {
				$string = '<div class="wpshop_products_block">'.$sorting.'<div class="wpshop_product_container">'.$string.'</div></div>';
			}
		}
		else if ( empty($atts['no_result_message']) || ($atts['no_result_message'] != 'no') ) {
			$string = __('There is nothing to output here', 'wpshop');
		}

		return do_shortcode($string);
	}

	function wpshop_get_product_by_criteria( $criteria = null, $cid=0, $pid=0, $display_type, $order='ASC', $page_number, $products_per_page=0, $nb_of_product_limit=0, $grid_element_nb_per_line=WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE ) {
		global $wpdb;
		$string = '<span id="wpshop_loading">&nbsp;</span>';
		$have_results = false;
		$display_type = (!empty($display_type) && in_array($display_type,array('grid','list'))) ? $display_type : 'grid';

		/** Check if Discount are activated */
		$discount_option = get_option( 'wpshop_catalog_product_option' );

		if ( $criteria == 'product_price' && !empty($discount_option) && !empty($discount_option['discount']) && $discount_option['discount'] == 'on') {
			$criteria = 'wpshop_displayed_price';
		}

		$query = array(
			 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
			 'order' => $order,
			 'posts_per_page' => $products_per_page,
			 'paged' => $page_number
		);

		// If the limit is greater than zero, hide pagination and change posts_per_page var
		if ( $nb_of_product_limit > 0 ) {
			$query['posts_per_page'] = $nb_of_product_limit;
			unset($query['paged']);
		}
		if( !empty($pid) ) {
			if(!is_array($pid)){
				$pid = explode(',', $pid);
			}

			$query['post__in'] = $pid;
		}
		if ( !empty($cid) ) {
			$cid = explode(',', $cid);
			$query['tax_query'] = array(array(
				'taxonomy' => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
				'field' => 'id',
				'terms' => $cid,
				'operator' => 'IN'
			));
		}
		if($criteria != null) {
			switch($criteria){
				case 'creator':
				case 'author':
					$query['author'] = get_current_user_id();
					break;
				case 'title':
				case 'date':
				case 'modified':
				case 'rand':
					$query['orderby'] = $criteria;
					break;
				default:
					if(!empty($pid)) {
						$post_meta = get_post_meta($pid, '_'.$criteria, true);
					}
					else{
						$check_meta = $wpdb->prepare("SELECT COUNT(meta_id) as meta_criteria FROM " . $wpdb->postmeta . " WHERE meta_key = %s", '_'.$criteria);
						$post_meta = $wpdb->get_var($check_meta);
					}
					if(!empty($post_meta)){
						$query['orderby'] = 'meta_value';
						$query['meta_key'] = '_'.$criteria;
					}
					break;
			}
		}
		else {
			$query['orderby'] = 'menu_order ID';
		}
		$post_per_page = $query['posts_per_page'];
		$total_products = ( !empty($query['post__in']) ) ? $query['post__in'] : 0;
		if ( !empty($pid) && !empty($query['post__in']) && count($query['post__in']) > $query['posts_per_page'] ) {
			$tmp_array = array();

			if ( empty($page_number) || $page_number == 1 ) {
				for( $i = 0; $i < $query['posts_per_page']; $i++ ) {
					$tmp_array[] = $query['post__in'][$i];
				}
			}
			else {
				$begin_number = ( ($page_number - 1) * $query['posts_per_page'] ) ;
				for( $i = $begin_number ; $i < $query['posts_per_page'] + $begin_number ; $i++ ) {
					if ( !empty($query['post__in'][$i]) ) {
						$tmp_array[] = $query['post__in'][$i];
					}
				}
			}
			unset( $query['post__in'] );
			$query['post__in'] = $tmp_array;
			$query['posts_per_page'] = -1;
		}

		$query['post_status'] = 'publish';

		$custom_query = new WP_Query( $query );

		if ( $custom_query->have_posts() ) {
			$have_results = true;

			// ---------------- //
			// Products listing //
			// ---------------- //
			$current_position = 1;
			$product_list = '';
			while ($custom_query->have_posts()) : $custom_query->the_post();
				$cats = get_the_terms(get_the_ID(), WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
				$cats = !empty($cats) ? array_values($cats) : array();
				$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
				$product_list .= self::product_mini_output(get_the_ID(), $cat_id, $display_type, $current_position, $grid_element_nb_per_line);
				$current_position++;
			endwhile;
			$tpl_component = array();
			$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $grid_element_nb_per_line : '') . ' '. $display_type .'_mode';
			$tpl_component['PRODUCT_LIST'] = $product_list;
			$tpl_component['CROSSED_OUT_PRICE'] = '';
			$tpl_component['LOW_STOCK_ALERT_MESSAGE'] = '';
			$string = wpshop_display::display_template_element('product_list_container', $tpl_component);

			// --------------------- //
			// Pagination management //
			// --------------------- //
			if($nb_of_product_limit==0) {

				$paginate = paginate_links(array(
					'base' => '%_%',
					'format' => '/?page_product=%#%',
					'current' => $page_number ,
					'total' => $custom_query->max_num_pages,
					'type' => 'array',
					'prev_next' => false
				));
				if(!empty($paginate)) {
					$string .= '<ul class="pagination">';
					foreach($paginate as $p) {
						$string .= '<li>'.$p.'</li>';
					}
					$string .= '</ul>';
				}
			}



			if ( !empty($pid) && !empty($query['post__in']) && count($total_products) > $post_per_page ) {
				$paginate = paginate_links(array(
						'base' => '%_%',
						'format' => '/?page_product=%#%',
						'current' => $page_number,
						'total' => ceil( count($total_products) / $post_per_page ) ,
						'type' => 'array',
						'prev_next' => false
				));
				if(!empty($paginate)) {
					$string .= '<ul class="pagination">';
					foreach($paginate as $p) {
						$string .= '<li>'.$p.'</li>';
					}
					$string .= '</ul>';
				}
			}


		}
		wp_reset_query(); // important

		return array($have_results, $string);
	}

	/**
	 * Update quantity for a product
	 * @param integer $product_id The product we want to update quantity for
	 * @param decimal $qty The new quantity
	 */
	function reduce_product_stock_qty($product_id, $qty) {
		global $wpdb;

		$product = self::get_product_data($product_id);
		if (!empty($product)) {
			$newQty = $product['product_stock']-$qty;
			if ($newQty >= 0) {
				$query = '
					SELECT wp_wpshop__attribute_value_decimal.value_id
					FROM wp_wpshop__attribute_value_decimal
					LEFT JOIN wp_wpshop__attribute ON wp_wpshop__attribute_value_decimal.attribute_id = wp_wpshop__attribute.id
					WHERE wp_wpshop__attribute_value_decimal.entity_id='.$product_id.' AND wp_wpshop__attribute.code="product_stock"
					LIMIT 1
				';
				$value_id = $wpdb->get_var($query);
				$update = $wpdb->update('wp_wpshop__attribute_value_decimal', array('value' => wpshop_tools::wpshop_clean($newQty)), array('value_id' => $value_id));
			}
			$product_meta = get_post_meta($product_id, '_wpshop_product_metadata', true);
			$product_meta['product_stock'] = $newQty;
			update_post_meta($product_id, '_wpshop_product_metadata', $product_meta);
		}
	}

	/**
	 * Retrieve an array with complete information about a given product
	 * @param integer $product_id
	 * @param boolean $for_cart_storage
	 * @return array Information about the product defined by first parameter
	 */
	function get_product_data( $product_id, $for_cart_storage = false, $post_status = '"publish"') {
		global $wpdb;
			$query = $wpdb->prepare('
			SELECT P.*, PM.meta_value AS attribute_set_id
			FROM '.$wpdb->posts.' AS P
				INNER JOIN '.$wpdb->postmeta.' AS PM ON (PM.post_id=P.ID)
			WHERE
				P.ID = %d
				AND ( (P.post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'") OR (P.post_type = "' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '") )
				AND P.post_status IN (' . $post_status . ')
				AND	PM.meta_key = "_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id"
			LIMIT 1
		', $product_id);

		$product = $wpdb->get_row($query);

		$product_data = array();
		$product_meta = array();

		if (!empty($product)) {
			$product_data['product_id'] = ( !empty($product->ID) ) ?  $product->ID : '';
			$product_data['post_name'] = ( !empty($product->post_name) ) ? $product->post_name : '';
			$product_data['product_name'] = ( !empty($product->post_title) ) ? $product->post_title : '';
			$product_data['post_title'] = ( !empty($product->post_title) ) ? $product->post_title : '';

			$product_data['product_author_id'] = ( !empty($product->post_author) ) ? $product->post_author : '';
			$product_data['product_date'] = ( !empty($product->post_date) ) ? $product->post_date : '';
			$product_data['product_content'] = ( !empty($product->post_content) ) ? $product->post_content : '';
			$product_data['product_excerpt'] = ( !empty($product->post_excerpt) ) ? $product->post_excerpt : '';

			$product_data['product_meta_attribute_set_id'] = ( !empty($product->attribute_set_id) ) ? $product->attribute_set_id : '';

			$data = wpshop_attributes::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT), $product->ID, WPSHOP_CURRENT_LOCALE, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			if ( !empty($data) ) {
				foreach ($data as $attribute) {
					$data_type = 'attribute_value_'.$attribute->data_type;
					$value = $attribute->$data_type;
					if (in_array($attribute->backend_input, array('select','multiple-select', 'radio','checkbox'))) {
						$value = wpshop_attributes::get_attribute_type_select_option_info($value, 'value');
					}

					/** Special traitment regarding attribute_code	*/
					switch($attribute->attribute_code) {
						case 'product_weight':
							$default_weight_unity = get_option('wpshop_shop_default_weight_unity');
							if ( !empty($default_weight_unity) ) {
								$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id = %d', $default_weight_unity);
								$unity = $wpdb->get_var($query);
								if ( $unity == 'kg') {
									$value *= 1000;
								}

							}
						break;
						default:
							$value = !empty($value) ? $value : 0;
						break;
					}
					$product_data[$attribute->attribute_code] = $value;

					if(!$for_cart_storage OR $for_cart_storage && $attribute->is_recordable_in_cart_meta == 'yes') {
						$meta = get_post_meta($product->ID, 'attribute_option_'.$attribute->attribute_code, true);
						if(!empty($meta)) {
							$product_meta[$attribute->attribute_code] = $meta;
						}
					}

					if ( ($attribute->is_visible_in_front == 'yes') && (!in_array($attribute->attribute_code, unserialize(WPSHOP_ATTRIBUTE_PRICES))) ) {
						$product_meta['attribute_visible'][$attribute->attribute_code] = $value;
					}
					if ( ($attribute->is_visible_in_front_listing == 'yes') && (!in_array($attribute->attribute_code, unserialize(WPSHOP_ATTRIBUTE_PRICES))) ) {
						$product_meta['attribute_visible_listing'][$attribute->attribute_code] = $value;
					}
				}
			}
			else {

			}

			/**	Get datas about product options	*/
			if ( $product->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) {
				$variation_details = get_post_meta($product->ID, '_wpshop_variations_attribute_def', true);

				foreach ( $variation_details as $attribute_code => $attribute_value) {
					$variation_id = $attribute_value;
					$attribute_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');

					$product_meta['variation_definition'][$attribute_code]['UNSTYLED_VALUE'] = stripslashes(wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', $attribute_definition->data_type_to_use, true));
					$product_meta['variation_definition'][$attribute_code]['NAME'] = $attribute_definition->frontend_label;
					switch( $attribute_definition->backend_input ) {
						case 'select':
						case 'multiple-select':
						case 'radio':
						case 'checkbox':
							$attribute_value = wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', $attribute_definition->data_type_to_use, true);
						break;
					}
					$product_meta['variation_definition'][$attribute_code]['VALUE'] = $attribute_value;
					$product_meta['variation_definition'][$attribute_code]['ID'] = $variation_id;
				}
			}

			$product_data['item_meta'] = !empty($product_meta) ? $product_meta : array();

			/** Get the display definition for the current product for checking custom display	*/
			$product_data['custom_display'] = get_post_meta($product_id, WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, true);
		}

		return $product_data;
	}

	/**
	 * Add a product into the db. This function is used for the EDI
	 * @param $name Name of the product
	 * @param $description Description of the product
	 * @param $attrs List of the attributes and values of the product
	 * @return boolean
	*/
	function addProduct($name, $description, $attrs=array()) {

		$new_product = wpshop_entities::create_new_entity(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $name, $description, $attrs);

		return $new_product[0];
	}

	/**
	 * Retrieve a product listing
	 * @param boolean $formated If the output have to be formated or not
	 * @param string $product_search Optionnal Define a search term for request
	 * @return object|string If $formated is set to true will display an html output with all product. Else return a wordpress database object with the product list
	 */
	function product_list($formated=false, $product_search=null) {
		global $wpdb;

		$query_extra_params = $query_extra_params_value = '';
		if( !empty($product_search) ) {
			$query_extra_params = " AND post_title LIKE '%%".$product_search."%%'";
			if ( is_array($product_search) ) {
				$query_extra_params = " AND ID IN (%s)";
				$query_extra_params_value = implode(",", $product_search);
			}
		}

		$query = $wpdb->prepare("SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type=%s AND post_status=%s" . $query_extra_params, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'publish', $query_extra_params_value);
		$data = $wpdb->get_results($query);

		/*
		 * Make some arangement on output if parameter is given
		 */
		if ( $formated ) {
			$product_string='';
			foreach ($data as $d) {
				$product_string .= '
					<li class="wpshop_shortcode_element_container wpshop_shortcode_element_container_product" >
						<input type="checkbox" class="wpshop_shortcode_element wpshop_shortcode_element_product" value="'.$d->ID.'" id="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'-'.$d->ID.'" name="products[]" /><label for="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'-'.$d->ID.'" > '.$d->post_title.'</label>
					</li>';
			}
		}

		return $formated ? $product_string : $data;
	}

	/**
	 * Enregistrement des données pour le produit
	 */
	function save_product_custom_informations( $post_id ) {
		global $wpdb;

		if ( !empty($_REQUEST['post_ID']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) ) {
			if ( !empty($_REQUEST[wpshop_products::currentPageCode . '_attribute']) ) {
				/*	Fill the product reference automatically if nothing is sent	*/
				if ( empty($_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference']) ) {
					$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts, '');
					$last_ref = $wpdb->get_var($query);
					$_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
				}
				else {
					/* Check if the product reference existing in the database */
					$ref = $_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'];
					$query = $wpdb->prepare("SELECT value_id FROM ".WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR." WHERE value = %s AND entity_id != %d AND entity_type_id = %d", $ref, $_REQUEST['post_ID'], wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					$existing_reference = $wpdb->get_var( $query );
					/* If this product reference exist -> Create a new product reference */
					if ( $wpdb->num_rows > 0 ) {
						$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts, '');
						$last_ref = $wpdb->get_var($query);
						$_REQUEST[wpshop_products::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
					}
				}

				/*	Save the attributes values into wpshop eav database	*/
				$update_from = !empty($_REQUEST[wpshop_products::currentPageCode . '_provenance']) ? $_REQUEST[wpshop_products::currentPageCode . '_provenance'] : '';
				$lang = WPSHOP_CURRENT_LOCALE;
				if ( !empty($_REQUEST['icl_post_language']) ) {
					$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", $_REQUEST['icl_post_language']);
					$lang = $wpdb->get_var($query);
				}
				wpshop_attributes::saveAttributeForEntity($_REQUEST[wpshop_products::currentPageCode . '_attribute'], wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $_REQUEST['post_ID'], $lang, $update_from);

				/*	Update product price looking for shop parameters	*/
				wpshop_products::calculate_price( $_REQUEST['post_ID'] );

				/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
				$productMetaDatas = array();
				foreach ( $_REQUEST[wpshop_products::currentPageCode . '_attribute'] as $attributeType => $attributeValues ) {
					foreach ( $attributeValues as $attributeCode => $attributeValue ) {
						if ( $attributeCode == 'product_attribute_set_id' ) {
							/*	Update the attribute set id for the current product	*/
							update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $attributeValue);
						}
						if ( $attributeType == 'decimal' ) {
							$attributeValue = str_replace(',', '.', $attributeValue);
						}
						if ( ($attributeType == 'integer') && !is_array($attributeValue) ) {
							$attributeValue = (int)$attributeValue;
						}
						$productMetaDatas[$attributeCode] = $attributeValue;
					}
				}
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
			}

			if ( !empty($_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']) && empty($_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']['default_config']) ) {
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_FRONT_DISPLAY_CONF, $_REQUEST[wpshop_products::currentPageCode . '_attr_frontend_display']);
			}
			else if ( $_REQUEST['action'] != 'autosave') {
				delete_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_FRONT_DISPLAY_CONF);
			}




			/**	Save product variation	*/
			if ( !empty($_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]) ) {
				foreach ( $_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION] as $variation_id => $variation_definition ) {
					foreach ( unserialize(WPSHOP_ATTRIBUTE_PRICES) as $price_attribute_code) {
						$price_attr_def = wpshop_attributes::getElement($price_attribute_code, "'valid'", 'code');
						if ( !empty($variation_definition) && !empty($variation_definition['attribute']) && is_object($price_attr_def) && !empty($variation_definition['attribute'][$price_attr_def->data_type]) && is_array($variation_definition['attribute'][$price_attr_def->data_type]) && !array_key_exists($price_attribute_code, $variation_definition['attribute'][$price_attr_def->data_type]) ) {
							$variation_definition['attribute'][$price_attr_def->data_type][$price_attribute_code] = !empty($_REQUEST[wpshop_products::currentPageCode . '_attribute'][$price_attr_def->data_type][$price_attribute_code]) ? $_REQUEST[wpshop_products::currentPageCode . '_attribute'][$price_attr_def->data_type][$price_attribute_code] : 0;
						}
					}
					$lang = WPSHOP_CURRENT_LOCALE;
					if ( !empty($_REQUEST['icl_post_language']) ) {
						$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", $_REQUEST['icl_post_language']);
						$lang = $wpdb->get_var($query);
					}
					wpshop_attributes::saveAttributeForEntity($variation_definition['attribute'], wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $variation_id, $lang);

					/**	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
					$variation_metadata = get_post_meta( $variation_id, '_wpshop_product_metadata', true);
					if ( !empty($variation_metadata) ) {
						$attributes_list = wpshop_attributes::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION),  $variation_id);
						if ( !empty($attributes_list)) {
							foreach ($attributes_list as $attribute) {
								$value_key = 'attribute_value_'.$attribute->data_type;
								$attributeValue = $attribute->$value_key;
								if ( $attribute->data_type == 'decimal' ) {
									$attributeValue = str_replace(',', '.', $attribute->$value_key);
								}
								if ( ($attribute->data_type == 'integer') && !is_array($attributeValue) ) {
									$attributeValue = (int)$attribute->$value_key;
								}
								$variation_metadata[$attribute->code] = $attribute->$value_key;
							}
						}
					}

					foreach ( $variation_definition['attribute'] as $attributeType => $attributeValues ) {
						foreach ( $attributeValues as $attributeCode => $attributeValue ) {
							if ( $attributeType == 'decimal' ) {
								$attributeValue = str_replace(',', '.', $attributeValue);
							}
							if ( ($attributeType == 'integer') && !is_array($attributeValue) ) {
								$attributeValue = (int)$attributeValue;
							}
							$variation_metadata[$attributeCode] = $attributeValue;
						}
					}
					update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $variation_metadata);


					/*	Update product price looking for shop parameters	*/
					wpshop_products::calculate_price( $variation_id );
				}
			}

			/*	Update the related products list*/
			if ( !empty($_REQUEST['related_products_list']) ) {
				update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS, $_REQUEST['related_products_list']);
			}
			else if ( $_REQUEST['action'] != 'autosave') {
				delete_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS);
			}


			/** Save the downloadable file **/
			/*
			if ( !empty( $_FILES['wpshop_file'] ) && $_FILES['wpshop_file'] != null ) {
				if(!is_dir(WPSHOP_UPLOAD_DIR)){
					mkdir(WPSHOP_UPLOAD_DIR, 0755, true);
				}

				$file = $_FILES['wpshop_file'];
				$tmp_name = $file['tmp_name'];
				$name = $file["name"];
				@move_uploaded_file($tmp_name, WPSHOP_UPLOAD_DIR.$name);

				$n = WPSHOP_UPLOAD_URL.'/'.$name;
				update_post_meta($_POST['post_ID'], 'attribute_option_is_downloadable_', array('file_url' => $n));
			}
			*/
			/*	Update product options	*/
			if ( !empty($_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['options']) ) {
				update_post_meta($_REQUEST['post_ID'], '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options', $_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT]['options']);
			}
			else if ( $_REQUEST['action'] != 'autosave') {
				delete_post_meta($_REQUEST['post_ID'], '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_options');
			}
		}

		flush_rewrite_rules();
	}

	/**
	 * Allows to define a specific permalink for each product by checking the parent categories
	 *
	 * @param mixed $permalink The actual permalink of the element
	 * @param object $post The post we want to set the permalink for
	 * @param void
	 *
	 * @return mixed The new permalink for the current element
	 */
	function set_product_permalink($permalink, $post, $unknown){
		global $wp_query;

		if ($post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)
			return $permalink;

		$product_categories = wp_get_object_terms( $post->ID, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES );

		if(count($product_categories) == 0){			/*	Product has only one category we get the only available slug	*/
			$product_category_slug = WPSHOP_UNCATEGORIZED_PRODUCT_SLUG;
		}
		elseif(count($product_categories) == 1){	/*	Product has only one category we get the only available slug	*/
			$product_category_slug = $product_categories[0]->slug;
		}
		else{																			/*	Product has several categories choose the slug of the we want	*/
			$product_category_slugs = array();
			foreach($product_categories as $product_category){
				$product_category_slugs[] = $product_category->slug;
			}
			$product_category_slug = self::currentPageCode;
		}

		$permalink = str_replace('%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%', $product_category_slug, $permalink);
		return apply_filters('wpshop_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_permalink', $permalink, $post->ID );
	}

	/**
	*	Get the aproduct attachement list for a given product and a given attachement type
	*
	*	@param string $attachement_type The attachement type we want to get for the product
	*
	*	@return mixed $product_attachement_list The attachement list for the current product and for the defined type
	*/
	function product_attachement_by_type($product_id, $attachement_type = 'image/', $url_on_click = ''){
		$product_attachement_list = '';

		$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id));
		if ( is_array( $attachments ) && ( count( $attachments ) > 0)  ) {
			$product_thumbnail = get_post_thumbnail_id($product_id);
			$attachmentsNumber = 0;
			foreach ( $attachments as $attachment ) {
				if ( is_int( strpos( $attachment->post_mime_type, $attachement_type ) ) ) {
					$url = $attachment->guid;
					$link_option = '';
					if ( $url_on_click != '' ) {
						$url = $url_on_click;
						$link_option = ' class="thickbox" ';
					}
					/*	Build the attachment output with the different parameters	*/
					$attachment_icon = 0;
					$attachement_more_informations = '';
					if ( $attachement_type == 'image/' ) {
						if ( $link_option == '' ) {
							$link_option = 'rel="appendix"';
						}
						$li_class = "product_picture_item";
						if ( $product_thumbnail == $attachment->ID ) {
							// $attachement_more_informations = '<br/><span class="product_thumbnail_indicator" >' . __('Product thumbnail', 'wpshop') . '</span>';
						}
					}
					else {
						if ( !empty ( $link_option ) ) {
							$link_option = 'target="product_document"';
						}
						$li_class = "product_document_item";
						$attachment_icon = 1;
						$attachement_more_informations = '<br/><span>' . $attachment->post_title . '</span>';
					}

					/*	Add the attchment to the list	*/
					$attachment_output = wp_get_attachment_image($attachment->ID, 'thumbnail', $attachment_icon);
					if ( !empty( $attachment_output ) ) {
						$product_attachement_list .= '<li class="' . $li_class . '" ><a href="' . $url . '" ' . $link_option . ' >' . $attachment_output . '</a>' . $attachement_more_informations . '<span class="delete_post_thumbnail" id="thumbnail_'.$attachment->ID.'"></span></li>';

						$attachmentsNumber++;
					}
				}
			}

			if($attachmentsNumber <= 0){
				$product_attachement_list .= '<li class="product_document_item" >' . __('No attachement were found for this product', 'wpshop') . '</li>';
			}
		}
		return $product_attachement_list;
	}

	/**
	*	Define output for product
	*
	*	@param mixed $initialContent The initial product content defined into wordpress basic admin interface
	*	@param integer $product_id The product identifier we want to get and output attribute for
	*
	*	@return mixed $content The content to add or to modify the product output in frontend
	*/
	function product_complete_sheet_output($initialContent, $product_id) {
		$content = $attributeContentOutput = '';

		/** Log number of view for the current product	*/
		$product_view_number = get_post_meta($product_id, WPSHOP_PRODUCT_VIEW_NB, true);
		$product_view_number++;
		update_post_meta($product_id, WPSHOP_PRODUCT_VIEW_NB, $product_view_number);

		/** Get product definition	*/
		$product = self::get_product_data($product_id);

		/** Get the product thumbnail	*/
		$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
		if ( has_post_thumbnail($product_id) ) {
			$thumbnail_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
			$tpl_component = array();
			$tpl_component['PRODUCT_THUMBNAIL_URL'] = $thumbnail_url[0];
			$tpl_component['PRODUCT_THUMBNAIL'] = get_the_post_thumbnail( $product_id, 'wpshop-product-galery' );
			$tpl_component['PRODUCT_THUMBNAIL_FULL'] = get_the_post_thumbnail( $product_id, 'full' );
			$image_attributes = wp_get_attachment_metadata( get_post_thumbnail_id() );
			if ( !empty($image_attributes) && !empty($image_attributes['sizes']) && is_array($image_attributes['sizes']) ) {
				foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
					$tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] = wp_get_attachment_image(get_post_thumbnail_id(), $size_name);
					$tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] = ( !empty( $tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] ) ) ? $tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] : WPSHOP_DEFAULT_PRODUCT_PICTURE;
				}
			}
			$productThumbnail = wpshop_display::display_template_element( 'product_thumbnail', $tpl_component );
			unset($tpl_component);
		}

		/**	Get attachement file for the current product	*/
		$product_picture_galery_content = $product_document_galery_content = '';
		$picture_number = $document_number = $index_li = 0;
		$attachments = get_posts( array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id) );
		if ( is_array($attachments) && (count($attachments) > 0) ) {
			$picture_increment = $document_increment = 1;
			foreach ($attachments as $attachment) {
				$tpl_component = array();
				$tpl_component['ATTACHMENT_ITEM_GUID'] = $attachment->guid;
				$tpl_component['ATTACHMENT_ITEM_TITLE'] = $attachment->post_title;
				if (is_int(strpos($attachment->post_mime_type, 'image/')) && ($attachment->ID != get_post_thumbnail_id())) {
					$tpl_component['ATTACHMENT_ITEM_TYPE'] = 'picture';
					$tpl_component['ATTACHMENT_ITEM_SPECIFIC_CLASS'] = (!($picture_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_picture_last' : '';
					$tpl_component['ATTACHMENT_ITEM_PICTURE'] = wp_get_attachment_image($attachment->ID, 'full');
					$image_attributes = wp_get_attachment_metadata( $attachment->ID );
					if ( !empty($image_attributes['sizes']) ) {
						foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
							$tpl_component['ATTACHMENT_ITEM_PICTURE_' . strtoupper($size_name)] = wp_get_attachment_image($attachment->ID, $size_name);
						}
					}
					else {
						$tpl_component['ATTACHMENT_ITEM_PICTURE_THUMBNAIL'] = wp_get_attachment_image($attachment->ID);
					}

					/** Template parameters	*/
					$template_part = 'product_attachment_item_picture';
					$tpl_component['PRODUCT_ID'] = $product_id;

					/** Build template	*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require_once(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$product_picture_galery_content .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$product_picture_galery_content .= wpshop_display::display_template_element($template_part, $tpl_component);
					}

					$index_li++;
					$picture_number++;
					$picture_increment++;
				}
				if (is_int(strpos($attachment->post_mime_type, 'application/pdf'))) {
					$tpl_component['ATTACHMENT_ITEM_TYPE'] = 'document';
					$tpl_component['ATTACHMENT_ITEM_SPECIFIC_CLASS'] = (!($document_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_document_last' : '';
					/** Template parameters	*/
					$template_part = 'product_attachment_item_document';
					$tpl_component['PRODUCT_ID'] = $product_id;

					/** Build template	*/
					$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
					if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
						/*	Include the old way template part	*/
						ob_start();
						require(wpshop_display::get_template_file($tpl_way_to_take[1]));
						$product_document_galery_content .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$product_document_galery_content .= wpshop_display::display_template_element($template_part, $tpl_component);
					}

					$document_number++;
					$document_increment++;
				}
				unset($tpl_component);
			}
		}

		$product_picture_galery = ($picture_number >= 1) ? self::display_attachment_gallery( 'picture', $product_picture_galery_content ) : '';
		$product_document_galery = ($document_number >= 1) ? self::display_attachment_gallery( 'document', $product_document_galery_content) : '';

		/**	Retrieve product attributes for output	*/
		$attributeContentOutput = wpshop_attributes::attribute_of_entity_to_tab( wpshop_entities::get_entity_identifier_from_code( self::currentPageCode ), $product_id, $product);

		/** Retrieve product price */
		$price_attribute = wpshop_attributes::getElement( 'product_price', "'valid'", 'code' );
		$price_display = wpshop_attributes::check_attribute_display( $price_attribute->is_visible_in_front, $product['custom_display'], 'attribute', 'product_price', 'complete_sheet');
		$productPrice = '';
		if ( $price_display ) {
			$productPrice = wpshop_prices::get_product_price($product, 'price_display', 'complete_sheet');
		}

		/** Check if there is at less 1 product in stock	*/
		$productStock = wpshop_cart::check_stock($product_id, 1);
		$productStock = $productStock===true ? 1 : null;

		/** Define "Add to cart" button	 */
		$add_to_cart_button_display_state = wpshop_attributes::check_attribute_display( ((WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no'), $product['custom_display'], 'product_action_button', 'add_to_cart', 'complete_sheet');
		$add_to_cart_button = $add_to_cart_button_display_state ? self::display_add_to_cart_button($product_id, $productStock, 'complete') : '';

		/** Define "Ask a quotation" button	*/
		$quotation_button = self::display_quotation_button($product_id, (!empty($product['quotation_allowed']) ? $product['quotation_allowed'] : null), 'complete');

		/** Template parameters	*/
		$template_part = 'product_complete_tpl';
		$tpl_component = array();
		$tpl_component['PRODUCT_VARIATIONS'] = wpshop_products::wpshop_variation($product_id);
		$tpl_component['PRODUCT_ID'] = $product_id;
		$tpl_component['PRODUCT_TITLE'] = $product['post_title'];
		$tpl_component['PRODUCT_THUMBNAIL'] = $productThumbnail;
		$tpl_component['PRODUCT_GALERY_PICS'] = $product_picture_galery;
		$tpl_component['PRODUCT_PRICE'] = $productPrice;
		$modules_option = get_option('wpshop_modules');
		$tpl_component['LOW_STOCK_ALERT_MESSAGE'] = '';
		if ( !empty($modules_option) && !empty($modules_option['wpshop_low_stock_alert']) && $modules_option['wpshop_low_stock_alert']['activated'] == 'on' ) {
			$tpl_component['LOW_STOCK_ALERT_MESSAGE'] = wpshop_low_stock_alert::display_alert_message ( $product_id );
		}

		$tpl_component['PRODUCT_INITIAL_CONTENT'] = $initialContent;
		$tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] = $add_to_cart_button;
		$tpl_component['PRODUCT_BUTTON_QUOTATION'] = $quotation_button;
		$tpl_component['PRODUCT_BUTTONS'] = $tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] . $tpl_component['PRODUCT_BUTTON_QUOTATION'];
		$tpl_component['PRODUCT_GALERY_DOCS'] = $product_document_galery;
		$tpl_component['PRODUCT_FEATURES'] = $attributeContentOutput;


		/** Build template	*/
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


	function product_mini_output($product_id, $category_id, $output_type = 'list', $current_item_position = 1, $grid_element_nb_per_line = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE) {
		$content = '';
		$product_information = $product_class = '';

		/** Get the product thumbnail	*/
		$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
		if(has_post_thumbnail($product_id)){
			$productThumbnail = get_the_post_thumbnail($product_id, 'thumbnail');
		}
		/** Get product definition	*/
		$product = self::get_product_data($product_id);

		/**	Get the product information for output	*/
		if ( !empty($product) ) {

			$product_title = $product['post_title'];
			$product_name = $product['post_name'];
			$product_link = get_permalink($product_id);
			$product_more_informations = $product['product_content'];
			$product_excerpt = '';//get_the_excerpt();

			if ( strpos($product['product_content'], '<!--more-->') ) {
				$post_content = explode('<!--more-->', $product['product_content']);
				$product_more_informations = $post_content[0];
			}

		}
		else {
			$productThumbnail = wpshop_display::display_template_element('product_thumbnail_default', array());
			$product_title = '<i>'.__('This product does not exist', 'wpshop').'</i>';
			$product_link = '';
			$product_more_informations = '';
			$product_excerpt = '';
		}

		/** Retrieve product price	*/
		$price_attribute = wpshop_attributes::getElement( 'product_price', "'valid'", 'code' );
		$price_display = wpshop_attributes::check_attribute_display( $price_attribute->is_visible_in_front_listing, $product['custom_display'], 'attribute', 'product_price', 'mini_output');
		$productPrice = '';
		if ( $price_display ) {
			$product_price_infos = get_post_meta( $product_id, '_wps_price_infos', true );

	 		if ( !empty($product_price_infos) ) {
	 			$tpl_component_price = array();
	 			/** Price piloting **/
	 			$price_ploting = get_option( 'wpshop_shop_price_piloting' );
	 			$tpl_component_price['CROSSED_OUT_PRICE'] = ( !empty($product_price_infos['CROSSED_OUT_PRICE']) ) ? ( ( !empty($product_price_infos['PRICE_FROM']) ) ? __('Price from', 'wpshop') . ' ' : '' ) . wpshop_display::display_template_element('product_price_template_crossed_out_price', array('CROSSED_OUT_PRICE_VALUE' => $product_price_infos['CROSSED_OUT_PRICE'])) : '';
	 			$tpl_component_price['PRODUCT_PRICE'] = ( empty($product_price_infos['CROSSED_OUT_PRICE']) && !empty($product_price_infos['PRICE_FROM'] ) ) ? __('Price from', 'wpshop') . ' ' . $product_price_infos['PRODUCT_PRICE'] : $product_price_infos['PRODUCT_PRICE'];
	 			$tpl_component_price['MESSAGE_SAVE_MONEY'] = $product_price_infos['MESSAGE_SAVE_MONEY'];
	 			$tpl_component_price['TAX_PILOTING'] = (!empty($price_ploting) && $price_ploting == 'HT' ) ? __('ET', 'wpshop') : ''; $product_price_infos['MESSAGE_SAVE_MONEY'];
	 			$productPrice = wpshop_display::display_template_element('product_price_template_mini_output', $tpl_component_price );
	 		}
			else {
				$productPrice = wpshop_prices::get_product_price($product, 'price_display', array('mini_output', $output_type) );
	 		}
		}

		/** Check if there is at less 1 product in stock	*/
		$productStock = wpshop_cart::check_stock($product_id, 1);
		$productStock = $productStock===true ? 1 : null;

		/** Define "Add to cart" button	*/
		$add_to_cart_button_display_state = ( !empty($product['custom_display']) ) ? wpshop_attributes::check_attribute_display( ((WPSHOP_DEFINED_SHOP_TYPE == 'sale') ? 'yes' : 'no'), $product['custom_display'], 'product_action_button', 'add_to_cart', 'mini_output') : null;
		$add_to_cart_button = (empty($add_to_cart_button_display_state) || ($add_to_cart_button_display_state === true) ? self::display_add_to_cart_button($product_id, $productStock, 'mini') : '');

		/** Define "Ask a quotation" button	*/
		$quotation_button = self::display_quotation_button($product_id, (!empty($product['quotation_allowed']) ? $product['quotation_allowed'] : null));
		$product_new_def = self::display_product_special_state('declare_new', $output_type, (!empty($product['declare_new']) ? $product['declare_new'] : 'no'), (!empty($product['set_new_from']) ? $product['set_new_from'] : ''), (!empty($product['set_new_to']) ? $product['set_new_to'] : ''));


		$product_new = $product_new_def['output'];
		$product_class .= $product_new_def['class'];

		$product_featured_def = self::display_product_special_state('highlight_product', $output_type, (!empty($product['highlight_product']) ? $product['highlight_product'] : 'no'), (!empty($product['highlight_from']) ? $product['highlight_from'] : ''), (!empty($product['highlight_to']) ? $product['highlight_to'] : ''));
		$product_featured = $product_featured_def['output'];
		$product_class .= $product_featured_def['class'];

		if ( !($current_item_position%$grid_element_nb_per_line) ) {
			$product_class .= ' wpshop_last_product_of_line';
		}

		if ( !empty($product['product_id']) ) {
			/** Template parameters	*/
			$template_part = 'product_mini_' . $output_type;
			$tpl_component = array();
			$tpl_component['PRODUCT_THUMBNAIL_MEDIUM'] = '<img src="'.WPSHOP_DEFAULT_PRODUCT_PICTURE.'" alt="" />';
			$tpl_component['PRODUCT_ID'] = $product_id;
			$tpl_component['PRODUCT_CLASS'] = $product_class;
			$tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] = $add_to_cart_button;
			$tpl_component['PRODUCT_BUTTON_QUOTATION'] = $quotation_button;
			$tpl_component['PRODUCT_BUTTONS'] = $tpl_component['PRODUCT_BUTTON_ADD_TO_CART'] . $tpl_component['PRODUCT_BUTTON_QUOTATION'];
			$tpl_component['PRODUCT_PRICE'] = $productPrice;
			$tpl_component['PRODUCT_PERMALINK'] = $product_link;
			$tpl_component['PRODUCT_TITLE'] = ( !empty($product_title) ) ? $product_title  : '';
			$tpl_component['PRODUCT_NAME'] = $product_name;
			$tpl_component['PRODUCT_DESCRIPTION'] = $product_more_informations;
			$tpl_component['PRODUCT_IS_NEW'] = $product_new;
			$tpl_component['PRODUCT_IS_FEATURED'] = $product_featured;
			$tpl_component['PRODUCT_EXTRA_STATE'] = $tpl_component['PRODUCT_IS_NEW'] . $tpl_component['PRODUCT_IS_FEATURED'];
			$tpl_component['PRODUCT_THUMBNAIL'] = $productThumbnail;
			if ( has_post_thumbnail($product_id) ) {
				$image_attributes = wp_get_attachment_metadata( get_post_thumbnail_id($product_id)  );
				if ( !empty($image_attributes) && !empty($image_attributes['sizes']) && is_array($image_attributes['sizes']) ) {
					foreach ( $image_attributes['sizes'] as $size_name => $size_def) {
						$tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] = wp_get_attachment_image(get_post_thumbnail_id($product_id), $size_name);
						$tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] = ( !empty( $tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] ) ) ? $tpl_component['PRODUCT_THUMBNAIL_' . strtoupper($size_name)] : WPSHOP_DEFAULT_PRODUCT_PICTURE;
					}
				}
			}
			$tpl_component['PRODUCT_EXCERPT'] = $product_excerpt;
			$tpl_component['PRODUCT_OUTPUT_TYPE'] = $output_type;

			/** Build template	*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/**	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$content = ob_get_contents();
				ob_end_clean();
			}
			else {
				$content = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);

		}
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
				if ( $product->post_status == "publish" ) {
					ob_start();
					require(wpshop_display::get_template_file('categories_products-widget.tpl.php'));
					$widget_content .= ob_get_contents();
					ob_end_clean();
				}
			}
		}

		echo $widget_content;
	}

	/**
	 *
	 * @param unknown_type $selected_product
	 * @return string
	 */
	function custom_product_list($selected_product = array()){
		global $wpdb;

		/*	Start the table definition	*/
		$tableId = 'wpshop_product_list';
		$tableTitles = array();
		$tableTitles[] = '';
		$tableTitles[] = __('Id', 'wpshop');
		$tableTitles[] = __('Quantity', 'wpshop');
		$tableTitles[] = __('Reference', 'wpshop');
		$tableTitles[] = __('Product name', 'wpshop');
		$tableTitles[] = __('Actions', 'wpshop');
		$tableTitles[] = __('Price', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_product_selector_column';
		$tableClasses[] = 'wpshop_product_identifier_column';
		$tableClasses[] = 'wpshop_product_quantity_column';
		$tableClasses[] = 'wpshop_product_sku_column';
		$tableClasses[] = 'wpshop_product_name_column';
		$tableClasses[] = 'wpshop_product_link_column';
		$tableClasses[] = 'wpshop_product_price_column';

		/*	Get post list	*/
		$has_result = false;
			$current_line_index = 0;
		$posts = query_posts(array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'posts_per_page' => -1
		));
		if(!empty($posts)){
			$has_result = true;
			foreach($posts as $post){
				$tableRowsId[$current_line_index] = 'product_' . $post->ID;

				$post_info = get_post_meta($post->ID, '_wpshop_product_metadata', true);

				unset($tableRowValue);
				$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '<input type="checkbox" name="wp_list_product[]" value="' . $post->ID . '" class="wpshop_product_cb_dialog" id="wpshop_product_cb_dialog_' . $post->ID . '" />');
				$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '<label for="wpshop_product_cb_dialog_' . $post->ID . '" >' . WPSHOP_IDENTIFIER_PRODUCT . $post->ID . '</label>');
				$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '<a href="#" class="order_product_action_button qty_change">-</a><input type="text" name="wpshop_pdt_qty[' . $post->ID  . ']" value="1" class="wpshop_order_product_qty" /><a href="#" class="order_product_action_button qty_change">+</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => ( !empty($post_info['product_reference']) ) ? $post_info['product_reference'] : '');
				$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => $post->post_title);
				$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '<a href="' . $post->guid . '" target="wpshop_product_view_product" target="wpshop_view_product" >' . __('View product', 'wpshop') . '</a><br/>
				<a href="' . admin_url('post.php?post=' . $post->ID  . '&action=edit') . '" target="wpshop_edit_product" >' . __('Edit product', 'wpshop') . '</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => __('Price ET', 'wpshop') . '&nbsp;:&nbsp;' . ( ( !empty( $post_info[WPSHOP_PRODUCT_PRICE_HT]) ) ? round($post_info[WPSHOP_PRODUCT_PRICE_HT],2) . '&nbsp;' . wpshop_tools::wpshop_get_currency() : '') . '<br/>' . __('Price ATI', 'wpshop') . '&nbsp;:&nbsp;' . ( ( !empty($post_info[WPSHOP_PRODUCT_PRICE_TTC]) ) ? round($post_info[WPSHOP_PRODUCT_PRICE_TTC],2) . '&nbsp;' . wpshop_tools::wpshop_get_currency() : ''));
				$tableRows[] = $tableRowValue;

				$current_line_index++;
			}
			wp_reset_query();
		}
		$posts = query_posts(array(
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'posts_per_page' => -1
		));
		if(!empty($posts)){
			$has_result = true;
			foreach($posts as $post){
				$tableRowsId[$current_line_index] = 'product_' . $post->ID;

				$post_info = get_post_meta($post->ID, '_wpshop_product_metadata', true);

				unset($tableRowValue);
				$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '<input type="checkbox" name="wp_list_product[]" value="' . $post->ID . '" class="wpshop_product_cb_dialog" id="wpshop_product_cb_dialog_' . $post->ID . '" />');
				$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '<label for="wpshop_product_cb_dialog_' . $post->ID . '" >' . WPSHOP_IDENTIFIER_PRODUCT . $post->ID . '</label>');
				$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '<a href="#" class="order_product_action_button qty_change">-</a><input type="text" name="wpshop_pdt_qty[' . $post->ID  . ']" value="1" class="wpshop_order_product_qty" /><a href="#" class="order_product_action_button qty_change">+</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => ( !empty($post_info['product_reference']) ) ? $post_info['product_reference'] : '');
				$parent_product = wpshop_products::get_parent_variation($post->ID);
				if ( !empty($parent_product) && !empty($parent_product['parent_post']) ) {
					$product_variations_postmeta = get_post_meta($post->ID,'_wpshop_variations_attribute_def', true);
					$query = $wpdb->prepare('SELECT frontend_label FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', key($product_variations_postmeta) );
					$option_name = $wpdb->get_var($query);
					$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $product_variations_postmeta[key($product_variations_postmeta)]);
					$option_value = $wpdb->get_var($query);
					$parent_post = $parent_product['parent_post'];
					$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => $parent_post->post_title. ' <br/>('. $option_name . ' : ' . $option_value. ')');
				}
				else {
					$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => $post->post_title);
				}

				$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '<a href="' . $post->guid . '" target="wpshop_product_view_product" target="wpshop_view_product" >' . __('View product', 'wpshop') . '</a><br/>
				<a href="' . admin_url('post.php?post=' . $post->ID  . '&action=edit') . '" target="wpshop_edit_product" >' . __('Edit product', 'wpshop') . '</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => __('Price ET', 'wpshop') . '&nbsp;:&nbsp;' . ( ( !empty( $post_info[WPSHOP_PRODUCT_PRICE_HT]) ) ? round($post_info[WPSHOP_PRODUCT_PRICE_HT],2) . '&nbsp;' . wpshop_tools::wpshop_get_currency() : '') . '<br/>' . __('Price ATI', 'wpshop') . '&nbsp;:&nbsp;' . ( ( !empty($post_info[WPSHOP_PRODUCT_PRICE_TTC]) ) ? round($post_info[WPSHOP_PRODUCT_PRICE_TTC],2) . '&nbsp;' . wpshop_tools::wpshop_get_currency() : ''));
				$tableRows[] = $tableRowValue;

				$current_line_index++;
			}
			wp_reset_query();
		}

		if (!$has_result) {
			$tableRowsId[] = 'no_product_found';
			unset($tableRowValue);
			$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => __('No element to ouput here', 'wpshop'));
			$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
		}

		return wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, '', false) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable( {
				"sPaginationType": "full_numbers",
				"iDisplayLength": 5
		});
	});
</script>';
	}

	/**
	 * Allows to manage output for special state for a product (New product/highlight product)
	 *
	 * @param string $special The type of special type we want to output
	 * @param string $output_type The current display type (used for product listing)
	 * @param string $special_state_def The value allowing to test if we have to display a special state for the product
	 * @param datetime $special_state_start The start date if applicable for the special state
	 * @param datetime $special_state_end The end date if applicable for the special state
	 *
	 * @return array $product_special_state The product special state
	 */
	function display_product_special_state($special, $output_type, $special_state_def, $special_state_start, $special_state_end) {
		$product_special_state = array();
		$product_special_state['output'] = $product_special_state['class'] = '';

		/** Get product special state definition	*/
		$special_state_def = !empty($special_state_def) ? $special_state_def : 'No';
		$special_state_start = !empty($special_state_start) ? substr($special_state_start, 0, 10) : null;
		$special_state_end = !empty($special_state_end) ? substr($special_state_end, 0, 10) : null;

		/** Get current time	*/
		$current_time = substr(current_time('mysql', 0), 0, 10);

		/** PRODUCT MARK AS NEW */
		$show_product_special_state = false;
		if ( (strtolower(__($special_state_def, 'wpshop')) === strtolower(__('Yes', 'wpshop')) ) &&
				(empty($special_state_start) || ($special_state_start == '0000-00-00') || ($special_state_start >= $current_time)) &&
				(empty($special_state_end) || ($special_state_end == '0000-00-00') || ($special_state_end <= $current_time)) ) {
			$show_product_special_state = true;
		}

		if ( $show_product_special_state ) {
			/** Check the type of special output needed	*/
			switch ( $special ) {
				case 'declare_new':
					$product_special_state['class'] = ' wpshop_product_is_new_' . $output_type;
					$template_part = 'product_is_new_sticker';
				break;

				case 'highlight_product':
					$product_special_state['class'] = ' wpshop_product_featured_' . $output_type;
					$template_part = 'product_is_featured_sticker';
				break;
			}

			/** Template parameters	*/
			$tpl_component = array();

			/** Build template		*/
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/**	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$product_special_state['output'] = ob_get_contents();
				ob_end_clean();
			}
			else {
				$product_special_state['output'] = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $product_special_state;
	}

	/**
	 * Prepare product price for saving and easier read later
	 *
	 * @param integer $element_id Identifier of current product
	 */
	function calculate_price( $element_id ) {
		global $wpdb;

		$query = $wpdb->prepare(
				"SELECT   ATTR_VAL.value_id AS id, ATTR_VAL.value, ATTR.id AS attribute_id, ATTR.code
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VAL ON ((ATTR_VAL.attribute_id = ATTR.id) AND (ATTR_VAL.entity_id = %d))
			WHERE ATTR.code IN ('" . implode("', '",  unserialize(WPSHOP_ATTRIBUTE_PRICES)) . "')
			UNION
			SELECT ATTR_OPT_VAL.id, ATTR_OPT_VAL.value, ATTR.id AS attribute_id, ATTR.code
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VAL ON ((ATTR_VAL.attribute_id = ATTR.id) AND (ATTR_VAL.entity_id = %d))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTR_OPT_VAL ON (ATTR_OPT_VAL.id = ATTR_VAL.value)
			WHERE ATTR.code IN ('" . implode("', '",  unserialize(WPSHOP_ATTRIBUTE_PRICES)) . "')",
				$element_id, $element_id
		);
		$element_prices = $wpdb->get_results($query);

		/**	Order results	*/
		$prices_attribute = array();
		foreach ( $element_prices as $element_price) {
			if ( empty($prices_attribute[$element_price->code]) || empty($prices_attribute[$element_price->code]->value) ) {
				$prices_attribute[$element_price->code] = $element_price;
			}
		}

		if ( !empty($prices_attribute) ) {
			/**	Get basic amount	*/
			$base_amount = $prices_attribute[constant('WPSHOP_PRODUCT_PRICE_' . WPSHOP_PRODUCT_PRICE_PILOT)]->value;

			/**	Get VAT rate	*/
			if ( !empty($prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]) ) {
				$tax_rate = 1 + ($prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]->value / 100);
				$defined_tax_rate = $prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]->value;
			}
			else {
				$query = $wpdb->prepare( "SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTR_OPT INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR ON (ATTR.default_value = ATTR_OPT.id) WHERE ATTR.code = %s", WPSHOP_PRODUCT_PRICE_TAX );
				$defined_tax_rate = $wpdb->get_var($query);
				$tax_rate = 1 + ($defined_tax_rate / 100);
			}

			$entityTypeId = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			$user_id = get_current_user_id();
			$language = WPSHOP_CURRENT_LOCALE;
			if ( !empty($_REQUEST['icl_post_language']) ) {
				$query = $wpdb->prepare("SELECT locale FROM " . $wpdb->prefix . "icl_locale_map WHERE code = %s", $_REQUEST['icl_post_language']);
				$language = $wpdb->get_var($query);
			}
			$unit_id = get_option('wpshop_shop_default_currency');

			/**	Check configuration to know how to make the calcul for the product	*/
			if ( WPSHOP_PRODUCT_PRICE_PILOT == 'HT' ) {
				$all_vat_include_price = ($base_amount * $tax_rate);
				$exclude_vat_price = $base_amount;
				if ( !empty($prices_attribute[WPSHOP_PRODUCT_PRICE_TTC]->id) ) {
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $all_vat_include_price), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TTC]->attribute_id));
				}
				else {
					$query_params = array(
						'value_id' => '',
						'entity_type_id' => $entityTypeId,
						'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TTC]->attribute_id,
						'entity_id' => $element_id,
						'unit_id' => $unit_id,
						'language' => $language,
						'user_id' => $user_id,
						'creation_date_value' => current_time('mysql', 0),
						'value' => $all_vat_include_price,
					);
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, $query_params);
				}
			}
			if ( WPSHOP_PRODUCT_PRICE_PILOT == 'TTC' ) {
				$all_vat_include_price = $base_amount;

				$exclude_vat_price = ($all_vat_include_price / $tax_rate);
				if ( !empty($prices_attribute[WPSHOP_PRODUCT_PRICE_HT]->id) ) {
					$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $exclude_vat_price), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_HT]->attribute_id));
				}
				else {
					$query_params = array(
						'value_id' => '',
						'entity_type_id' => $entityTypeId,
						'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_HT]->attribute_id,
						'entity_id' => $element_id,
						'unit_id' => $unit_id,
						'language' => $language,
						'user_id' => $user_id,
						'creation_date_value' => current_time('mysql', 0),
						'value' => $exclude_vat_price,
					);
					$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, $query_params);
				}
			}

			$vat_amount = $all_vat_include_price - $exclude_vat_price;
			if ( !empty($prices_attribute[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]->id) ) {
				$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('value' => $vat_amount), array('entity_id' => $element_id, 'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]->attribute_id));
			}
			else {
				$query_params = array(
					'value_id' => '',
					'entity_type_id' => $entityTypeId,
					'attribute_id' => $prices_attribute[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]->attribute_id,
					'entity_id' => $element_id,
					'unit_id' => $unit_id,
					'language' => $language,
					'user_id' => $user_id,
					'creation_date_value' => current_time('mysql', 0),
					'value' => $vat_amount,
				);
				$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, $query_params);
			}

			/**	Update the product meta infromation with the calculated prices	*/
			$product_postmeta = get_post_meta($element_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
			$product_postmeta[WPSHOP_PRODUCT_PRICE_TTC] = round($all_vat_include_price, 5);
			$product_postmeta[WPSHOP_PRODUCT_PRICE_HT] = round($exclude_vat_price,5);
			$product_postmeta[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = round($vat_amount, 5);
			$product_postmeta[WPSHOP_PRODUCT_PRICE_TAX] = $prices_attribute[WPSHOP_PRODUCT_PRICE_TAX]->id;

			update_post_meta($element_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $product_postmeta);
		}
	}

	/**
	 * Allows to get the good button for adding product to cart
	 *
	 * @param integer $product_id The product identifier
	 * @param boolean $productStock If there is the possibility to add the given product to the cart
	 *
	 * @return string $button The html output for the button
	 */
	function display_add_to_cart_button($product_id, $productStock, $output_type = 'mini') {
		$button = '';
		$attributes_frontend_display = get_post_meta( $product_id, '_wpshop_product_attributes_frontend_display', true );
		if ( WPSHOP_DEFINED_SHOP_TYPE == 'sale' && ( empty($attributes_frontend_display) || ( !empty($attributes_frontend_display) && !empty($attributes_frontend_display['product_action_button']) && !empty($attributes_frontend_display['product_action_button']['mini_output']) && $output_type == 'mini') || ( !empty($attributes_frontend_display) && !empty($attributes_frontend_display['product_action_button']) && !empty($attributes_frontend_display['product_action_button']['complete_sheet']) && $output_type == 'complete') ) ) {
			/*
			 * Check if current product has variation for button display
			 */
			$variations_exists = get_posts( array( 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION, 'post_parent' => $product_id) );
			$variations_list = ( !empty($variations_exists) && is_array( wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode)) ) ) ? true : false;

			/*
			 * Template parameters
			 */
			$template_part = ($variations_list && ($output_type == 'mini')) ? 'configure_product_button' : (!empty($productStock) ? 'add_to_cart_button' : 'unavailable_product_button');
			$tpl_component = array();
			$tpl_component['PRODUCT_ID'] = $product_id;
			$tpl_component['PRODUCT_PERMALINK'] = get_permalink($product_id);
			$tpl_component['PRODUCT_TITLE'] = get_the_title($product_id);

			/*
			 * Build template
			 */
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$button = ob_get_contents();
				ob_end_clean();
			}
			else {
				$button = wpshop_display::display_template_element($template_part, $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $product_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . 'output_type' => $output_type));
			}
			unset($tpl_component);
		}


		return $button;
	}

	/**
	 * Allows to get the good button for adding product to a quotation
	 *
	 * @param integer $product_id The product identifier
	 * @param boolean $product_quotation_state The state of the quotation addons
	 *
	 * @return string $button The html output for the button
	 */
	function display_quotation_button($product_id, $product_quotation_state,  $output_type = 'mini') {
		$quotation_button = '';

		if ( WPSHOP_ADDONS_QUOTATION && (!empty($product_quotation_state) && strtolower(__($product_quotation_state, 'wpshop')) == strtolower(__('Yes', 'wpshop'))) && (empty($_SESSION['cart']['cart_type']) || ($_SESSION['cart']['cart_type'] == 'quotation')) ) {
			$variations_list = ( is_array( wpshop_products::get_variation( $product_id ) ) && is_array( wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode)) ) ) ? array_merge( wpshop_products::get_variation( $product_id ), wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode) ) ) : array();
			/**
			 * Template parameters
			 */
			$template_part = (!empty($variations_list) && ($output_type == 'mini')) ? 'configure_quotation_button' : 'ask_quotation_button';
			$tpl_component = array();
			$tpl_component['PRODUCT_ID'] = $product_id;
			$tpl_component['PRODUCT_PERMALINK'] = get_permalink($product_id);
			$tpl_component['PRODUCT_TITLE'] = get_the_title($product_id);

			/**
			 * Build template
			 */
			$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
			if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
				/*	Include the old way template part	*/
				ob_start();
				require(wpshop_display::get_template_file($tpl_way_to_take[1]));
				$quotation_button = ob_get_contents();
				ob_end_clean();
			}
			else {
				$quotation_button = wpshop_display::display_template_element($template_part, $tpl_component);
			}
			unset($tpl_component);
		}

		return $quotation_button;
	}

	/**
	 * Return the output for a product attachement gallery (picture or document)
	 *
	 * @param string $attachement_type The type of attachement to output. allows to define with type of template to take
	 * @param string $content The gallery content build previously
	 *
	 * @return string The attachement gallery output
	 */
	function display_attachment_gallery( $attachement_type, $content ) {
		$galery_output = '';

		/*
		 * Get the template part for given galery type
		 */
		switch ( $attachement_type ) {
			case 'picture':
					$template_part = 'product_attachment_picture_galery';
				break;
			case 'document':
					$template_part = 'product_attachment_galery';
				break;
		}

		/*
		 * Template parameters
		 */
		$tpl_component = array();
		$tpl_component['PRODUCT_ATTACHMENT_OUTPUT_CONTENT'] = $content;
		$tpl_component['ATTACHMENT_ITEM_TYPE'] = $attachement_type;

		/*
		 * Build template
		 */
		$tpl_way_to_take = wpshop_display::check_way_for_template($template_part);
		if ( $tpl_way_to_take[0] && !empty($tpl_way_to_take[1]) ) {
			/*	Include the old way template part	*/
			ob_start();
			require(wpshop_display::get_template_file($tpl_way_to_take[1]));
			$galery_output = ob_get_contents();
			ob_end_clean();
		}
		else {
			$galery_output = wpshop_display::display_template_element($template_part, $tpl_component);
		}
		unset($tpl_component);

		return $galery_output;
	}

	/**
	 * Define the metabox to display in product edition page in backend
	 * @param object $post The current element displayed for edition
	 */
	function meta_box_variations( $post ) {
		$output = '';
		/*	Variations container	*/
		$tpl_component = array();
		$tpl_component['ADMIN_VARIATION_CONTAINER'] = self::display_variation_admin( $post->ID );
		$output .= wpshop_display::display_template_element('wpshop_admin_variation_metabox', $tpl_component, array(), 'admin');
		echo '<span class="wpshop_loading_ wpshopHide" ><img src="' . admin_url('images/loading.gif') . '" alt="loading picture" /></span>' . $output . '<div class="wpshop_cls" ></div>';
	}

	/**
	 * Call variation creation function with a list of defined variation
	 *
	 * @param array $possible_variations A list of variation to create for the current element
	 * @param integer $element_id The product we want to create variation for
	 *
	 * @return mixed The last created variation identifier
	 */
	function creation_variation_callback( $possible_variations, $element_id ) {
		/** Get existing variation	*/
		$existing_variations_in_db = wpshop_products::get_variation( $element_id );
		$existing_variations = array();
		if ( !empty($existing_variations_in_db) ) {
			foreach ( $existing_variations_in_db as $variations_def) {
				$existing_variations[] = $variations_def['variation_def'];
			}
		}
		/** New variation definition	*/
		$attribute_defining_variation = get_post_meta($element_id, '_wpshop_variation_defining', true);

		/**	Read possible values	*/
		foreach ( $possible_variations as $variation_definition) {
			if ( in_array($variation_definition, $existing_variations) ) {
				continue;
			}

			$attribute_to_set = array();
			foreach ( $variation_definition as $attribute_code => $attribute_selected_value ) {
				$attribute = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
				$attribute_to_set[$attribute->data_type][$attribute_code] = $attribute_selected_value;
				if ( empty($attribute_defining_variation['attributes']) || (!in_array($attribute_code, $attribute_defining_variation['attributes'])) ) {
					$attribute_defining_variation['attributes'][] = $attribute_code;
				}
			}
			$variation_id = wpshop_products::create_variation($element_id, $attribute_to_set);
		}
		update_post_meta($element_id, '_wpshop_variation_defining', $attribute_defining_variation );

		return $variation_id;
	}

	/**
	 * Create a new variation for product
	 *
	 * @param integer $head_product The product identifier to create the new variation for
	 * @param array $variation_attributes Attribute list for the variation
	 *
	 * @return mixed <number, WP_Error> The variation identifier or an error in case the creation was not succesfull
	 */
	function create_variation( $head_product, $variation_attributes ) {
		$variation = array(
			'post_title' => sprintf(__('Product %s variation %s', 'wpshop'), $head_product, get_the_title( $head_product )),
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_parent' => $head_product,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION
		);
		$variation_id = wp_insert_post( $variation );

		wpshop_attributes::saveAttributeForEntity($variation_attributes, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $variation_id, WPSHOP_CURRENT_LOCALE, '');

		/*	Update product price looking for shop parameters	*/
		wpshop_products::calculate_price( $variation_id );

		/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
		$productMetaDatas = array();
		foreach ( $variation_attributes as $attributeType => $attributeValues ) {
			foreach ( $attributeValues as $attributeCode => $attributeValue ) {
				if ( !empty($attributeValue) ) {
					$productMetaDatas[$attributeCode] = $attributeValue;
				}
			}
		}
		update_post_meta($variation_id, '_wpshop_variations_attribute_def', $productMetaDatas);
		update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
		update_post_meta($variation_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, get_post_meta($head_product, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true));

		return $variation_id;
	}

	/**
	 * Get variation list for a given product
	 *
	 * @param integer $head_product The product identifier to get the variation for
	 * @return object The variation list
	 */
	function get_variation( $head_product ) {
		$variations_output = null;
		$variations = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
			'post_parent' 	=> $head_product,
			'orderby' 		=> 'ID',
			'order' 		=> 'ASC',
			'posts_per_page'=> -1
		));

		if ( !empty( $variations ) ) {
			$head_wpshop_variation_definition = get_post_meta( $head_product, '_wpshop_variation_defining', true );

			foreach ( $variations as $post_def ) {
				$data = wpshop_attributes::get_attribute_list_for_item( wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $post_def->ID, WPSHOP_CURRENT_LOCALE, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );
				foreach ( $data as $content ) {
					$attribute_value = 'attribute_value_' . $content->data_type;
					if ( !empty($content->$attribute_value) ) {
						if ( !empty($head_wpshop_variation_definition['attributes']) && in_array($content->code, $head_wpshop_variation_definition['attributes']) ) {
							$variations_output[$post_def->ID]['variation_def'][$content->code] = $content->$attribute_value;
						}
						else {
							$variations_output[$post_def->ID]['variation_dif'][$content->code] = $content->$attribute_value;
						}
					}
				}
				$variations_output[$post_def->ID]['post'] = $post_def;
			}
		}
		wp_reset_query();
		return $variations_output;
	}

	/**
	 * Affichage des variations d'un produit dans l'administration
	 *
	 * @param integer $head_product L'identifiant du produit dont on veut afficher les variations
	 * @return string Le code html permettant l'affichage des variations dans l'interface d'édition du produit
	 */
	function display_variation_admin( $head_product ) {
		$output = '';
		$productCurrency = wpshop_tools::wpshop_get_currency();
		/*	Récupération de la liste des variations pour le produit en cours d'édition	*/
		$variations = self::get_variation( $head_product );

		/*	Affichage de la liste des variations pour le produit en cours d'édition	*/
		if ( !empty($variations) && is_array($variations) ) {
			$existing_variation_list = wpshop_display::display_template_element('wpshop_admin_existing_variation_controller', array(), array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product), 'admin');

			foreach ( $variations as $variation ) {
				$tpl_component = array();

				$tpl_component['ADMIN_EXISTING_VARIATIONS_CLASS'] = ' wpshop_variation_' . self::currentPageCode;
				$tpl_component['VARIATION_IDENTIFIER'] = $variation['post']->ID;
				$tpl_component['VARIATION_DETAIL'] = '  ';
				$tpl_component['VARIATION_DETAIL_PRICE'] = number_format(str_replace(',', '.',( ( !empty($variation['variation_dif']['product_price']) ) ? $variation['variation_dif']['product_price'] : 0)) , 2, '.', '').' '.$productCurrency;
				$post_obj = $variation['post'];

				$parent_product_infos = wpshop_products::get_parent_variation ( $post_obj->ID );
				if ( !empty($parent_product_infos) ) {
					$parent_post = $parent_product_infos['parent_post'];
					$product_option_postmeta = get_post_meta($parent_post->ID, '_wpshop_variation_defining', true);
					if ( !empty($product_option_postmeta['options']['price_behaviour']) && !empty($product_option_postmeta['options']['price_behaviour'][0]) && $product_option_postmeta['options']['price_behaviour'][0] == 'addition') {
						$product_price = ( ( !empty($variation['variation_dif']['product_price']) ) ? $variation['variation_dif']['product_price'] : 0 ) + $parent_product_infos['parent_post_meta']['product_price'];
						$tpl_component['VARIATION_DETAIL_SALE_PRICE_INDICATION'] = __('Variation price combined with the parent product price', 'wpshop');
					}
					else {
						$product_price = ( !empty($variation['variation_dif']['product_price']) ) ? $variation['variation_dif']['product_price'] : 0;
						$tpl_component['VARIATION_DETAIL_SALE_PRICE_INDICATION'] = __('Only variation\'s price is used', 'wpshop');
					}
					$product_price = number_format(str_replace(',', '.',$product_price), 2, '.', '').' '.$productCurrency;
					$tpl_component['VARIATION_DETAIL_SALE_PRICE'] = $product_price;
				}

				if ( !empty($variation['variation_def']) ) {
					foreach ( $variation['variation_def'] as $variation_key => $variation_value ) {
						if ( !empty($variation_value) ) {
							$attribute_def_for_variation = wpshop_attributes::getElement($variation_key, "'valid'", 'code');
							$tpl_component['VARIATION_DETAIL'] .= '<input type="hidden" name="' . self::current_page_variation_code . '[' . $variation['post']->ID . '][attribute][' . $attribute_def_for_variation->data_type . '][' . $variation_key . ']" value="' . $variation_value . '" />' . wpshop_display::display_template_element('wpshop_admin_variation_item_def_header', array('VARIATION_ATTRIBUTE_CODE' => $attribute_def_for_variation->frontend_label, 'VARIATION_ATTRIBUTE_CODE_VALUE' => stripslashes(wpshop_attributes::get_attribute_type_select_option_info($variation_value, 'label', $attribute_def_for_variation->data_type_to_use, true))), array(), 'admin');
						}
					}
				}
				$tpl_component['VARIATION_DETAIL'] = substr($tpl_component['VARIATION_DETAIL'], 0, -2);

				$tpl_component['ADMIN_VARIATION_SPECIFIC_DEFINITION_CONTAINER_CLASS'] = ' wpshopHide';
				$tpl_component['VARIATION_DEFINITION'] = wpshop_attributes::get_variation_attribute( array('input_class' => ' ', 'field_name' => wpshop_products::current_page_variation_code . '[' . $variation['post']->ID . ']','page_code' => self::current_page_variation_code, 'field_id' => self::current_page_variation_code . '_' . $variation['post']->ID, 'variation_dif_values' => (!empty($variation['variation_dif']) ? $variation['variation_dif'] : array())) );
				$tpl_component['VARIATION_DEFINITION_CONTENT'] = wpshop_display::display_template_element('wpshop_admin_variation_item_specific_def', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => $variation['post']->ID), 'admin');

				/*	Add the variation definition to output	*/
				$existing_variation_list .= wpshop_display::display_template_element('wpshop_admin_variation_item_def', $tpl_component, array(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT => $head_product, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION => $variation['post']->ID), 'admin');
			}

			$output .= wpshop_display::display_template_element('wpshop_admin_existing_variation_list', array('ADMIN_EXISTING_VARIATIONS_CONTAINER_CLASS' => '', 'ADMIN_EXISTING_VARIATIONS_CONTAINER' => $existing_variation_list), array(), 'admin');
			/*	Reset de la liste des résultats pour éviter les comportements indésirables	*/
			wp_reset_query();
		}
		else {
			$output = __('No variation found for this product. Please use button above for create one', 'wpshop');
		}

		return $output;
	}

	/**
	 * Retrieve and display the variation for a given product
	 * @param integer $product_id The product identifier to get variation for
	 */
	function wpshop_variation( $post_id = '', $from_admin = false, $order_id = '', $qty = 1 ) {
		global $wp_query;
		$output = '';

		$product_id = empty($post_id) ? $wp_query->post->ID : $post_id ;
		$wpshop_product_attributes_frontend_display = get_post_meta( $product_id, '_wpshop_product_attributes_frontend_display', true );
		$head_wpshop_variation_definition = get_post_meta( $product_id, '_wpshop_variation_defining', true );

		/**	Get attribute order for current product	*/
		$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($product_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
		$output_order = array();
		if ( count($product_attribute_order_detail) > 0 ) {
			if (!empty($product_attribute_order_detail) ) {
				foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
					foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
						if ( !empty($attribute_def->code) )
							$output_order[$attribute_def->code] = $position;
					}
				}
			}
		}

		$variations_params = array();
		$variation_attribute = array();
		$variation_attribute_ordered = array();
		$possible_values = array();
		$possible_values_for_selection_calculation = array();

		/*	Vérification de l'existence de déclinaison pour le produit	*/
		$wpshop_variation_list = self::get_variation( $product_id );
		if ( !empty($wpshop_variation_list) ) {
			foreach ($wpshop_variation_list as $variation) {
				if (!empty($variation['variation_def']) ) {
					$display_option = get_post_meta( $post_id, '_wpshop_product_attributes_frontend_display', true );
					foreach ( $variation['variation_def'] as $attribute_code => $attribute_value ) {
						if ( empty($display_option) || ( !empty($display_option['attribute']) && !empty($display_option['attribute'][$attribute_code]) && !empty($display_option['attribute'][$attribute_code]['complete_sheet']) ) ) {
							$tpl_component = array();

							$attribute_db_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
							$default_value_is_serial = false;
							$attribute_list_first_element = $attribute_db_definition->default_value;
							if ( !empty($attribute_db_definition->default_value) && ($attribute_db_definition->default_value == serialize(false) || @unserialize($attribute_db_definition->default_value) !== false) ) {
								$default_value_is_serial = true;
								$tmp_default_value = unserialize($attribute_db_definition->default_value);
								$attribute_list_first_element = !empty($tmp_default_value['field_options']['label_for_first_item']) ? $tmp_default_value['field_options']['label_for_first_item'] : null;
							}

							if ( $default_value_is_serial && !empty($attribute_list_first_element) && ($attribute_list_first_element != 'none') ) {
								$possible_values[$attribute_code][0][0] = ($default_value_is_serial && !empty($attribute_list_first_element) && ($attribute_list_first_element != 'none')) ? stripslashes( sprintf( $attribute_list_first_element, strtolower($attribute_db_definition->frontend_label)) ) : __('Choose a value', 'wpshop');
							}

							if ( !empty($attribute_value) && ($attribute_db_definition->data_type_to_use == 'custom')) {
								$tpl_component['VARIATION_VALUE'] = stripslashes(wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'label', 'custom'));
								$position = wpshop_attributes::get_attribute_type_select_option_info($attribute_value, 'position', 'custom');
							}
							else if ( !empty($attribute_value) && ($attribute_db_definition->data_type_to_use == 'internal')) {
								$post_def = get_post($attribute_value);
								$tpl_component['VARIATION_VALUE'] = stripslashes($post_def->post_title);
								$position = $post_def->menu_order;
							}

							if ( !empty($variation['variation_dif']) ) {
								foreach ( $variation['variation_dif'] as $attribute_dif_code => $attribute_dif_value) {
									$wpshop_prices_attributes = unserialize(WPSHOP_ATTRIBUTE_PRICES);
									$the_value = $attribute_dif_value;
									if ( in_array($attribute_dif_code, $wpshop_prices_attributes) ) {
										$the_value = wpshop_display::format_field_output('wpshop_product_price', $attribute_dif_value);
									}
									$tpl_component['VARIATION_DIF_' . strtoupper($attribute_dif_code)] = stripslashes($the_value);
								}
							}
							if ( !empty($attribute_value) ) {
								$possible_values[$attribute_code][$position][$attribute_value] = wpshop_display::display_template_element('product_variation_item_possible_values', $tpl_component, array('type' => 'attribute_for_variation', 'id' => $attribute_code));
								$possible_values_for_selection_calculation[$attribute_code][$attribute_value] = $tpl_component['VARIATION_VALUE'];
							}
							unset($tpl_component);
						}
					}
				}
			}

			$variation_tpl = array();
			if ( !empty($head_wpshop_variation_definition['attributes']) ) {

				foreach ( $head_wpshop_variation_definition['attributes'] as $attribute_code ) {
					$attribute_db_definition = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
					$attribute_display_state = wpshop_attributes::check_attribute_display( $attribute_db_definition->is_visible_in_front, $wpshop_product_attributes_frontend_display, 'attribute', $attribute_code, 'complete_sheet');

						$is_required = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_code, $head_wpshop_variation_definition['options']['required_attributes']) )) ) ? true : false;
						if ( !$is_required && $attribute_db_definition->is_required == 'yes' ) {
							$is_required = true;
						}

						$input_def = array();
						$input_def['type'] = $attribute_db_definition->frontend_input;
						$value = isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : (!empty($attribute_db_definition->default_value) ? $attribute_db_definition->default_value : null);
						if ( in_array($attribute_db_definition->frontend_input, array('radio', 'checkbox')) ) {
							unset($possible_values[$attribute_code][0]);
							$value = array($value);
						}
						$input_def['id'] = 'wpshop_variation_attr_' . $attribute_code;
						$input_def['name'] = $attribute_code;
						$real_possible_values = array();
						if ( !empty($possible_values[$attribute_code]) ) {
							ksort($possible_values[$attribute_code]);
							foreach ( $possible_values[$attribute_code] as $position => $def ) {
								foreach ( $def as $attribute_value => $attribute_value_output ) {
									$real_possible_values[$attribute_value] = $attribute_value_output;
								}
							}
						}
						$input_def['possible_value'] = $real_possible_values;
						$input_def['valueToPut'] = 'index';
						$input_def['value'] = $value;

						$input_def['options']['more_input'] = '';
						if ( !empty($possible_values_for_selection_calculation[$attribute_code]) ) {
							foreach ( $possible_values_for_selection_calculation[$attribute_code] as $value_id => $value ) {
								$input_def['options']['more_input'] .= '<input type="hidden" disabled="disabled" value="' . str_replace("\\", "", $value) . '" name="' . $input_def['id'] . '_current_value" id="' . $input_def['id'] . '_current_value_' . $value_id . '" />';
							}
						}

						$input_def['options']['label']['original'] = true;
						$input_def['option'] = ' class="wpshop_variation_selector_input' . ($is_required ? ' attribute_is_required_input attribute_is_required_input_' . $attribute_code . ' ' : '') . ( $attribute_db_definition->_display_informations_about_value == 'yes' ? ' wpshop_display_information_about_value' : '' ) . ' ' . (( is_admin() ) ? $attribute_db_definition->backend_css_class : $attribute_db_definition->frontend_css_class) . '" ';
						if ( !empty(  $real_possible_values ) ) {
							$tpl_component = array();
							$attribute_output_def['value'] = isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_code] : $input_def['value'];
							$tpl_component['VARIATION_INPUT'] = wpshop_form::check_input_type($input_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) . $input_def['options']['more_input'];
							$tpl_component['VARIATION_LABEL'] = ($is_required ? '<span class="attribute_is_required attribute_is_required_' . $attribute_code . '" >' . stripslashes($attribute_db_definition->frontend_label) . '</span> <span class="required" >*</span>' : stripslashes($attribute_db_definition->frontend_label) );
							$tpl_component['VARIATION_CODE'] = $attribute_code;
							$tpl_component['VARIATION_LABEL_HELPER'] = !empty($attribute_db_definition->frontend_help_message) ? ' title="' . $attribute_db_definition->frontend_help_message . '" ' : '';
							$tpl_component['VARIATION_LABEL_CLASS'] = !empty($attribute_db_definition->frontend_help_message) ? ' wpshop_att_variation_helper' : '';
							$tpl_component['VARIATION_IDENTIFIER'] = $input_def['id'];
							$tpl_component['VARIATION_PARENT_ID'] = $product_id;
							$tpl_component['VARIATION_PARENT_TYPE'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
							$tpl_component['VARIATION_CONTAINER_CLASS'] = ($is_required ? ' attribute_is_required_container attribute_is_required_container_' . $attribute_code : '') . ' wpshop_variation_' . $attribute_code . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_' . $product_id;
							$tpl_component['VARIATION_REQUIRED_INDICATION'] = ( $is_required ) ? __('Required variation', 'wpshop') : '';
							$variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_code)] = wpshop_display::display_template_element('product_variation_item', $tpl_component);
							$variation_attribute_ordered[$output_order[$attribute_code]] = $variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_code)];
						}

					$variation_attribute[] = $attribute_code;
				}
			}

		}
		$variation_tpl['VARIATION_FORM_ELEMENT_ID'] = $product_id;
		wp_reset_query();

		$attribute_defined_to_be_user_defined = wpshop_attributes::get_attribute_user_defined( array('entity_type_id' => self::currentPageCode) );
		if ( !empty($attribute_defined_to_be_user_defined) ) {
			foreach ( $attribute_defined_to_be_user_defined as $attribute_not_in_variation_but_user_defined ) {
				$is_required = ( (!empty($head_wpshop_variation_definition['options']) && !empty($head_wpshop_variation_definition['options']['required_attributes']) && ( in_array( $attribute_not_in_variation_but_user_defined->code, $head_wpshop_variation_definition['options']['required_attributes']) ))  && $attribute_not_in_variation_but_user_defined->is_required == 'yes' ) ? true : false;
				$attribute_display_state = wpshop_attributes::check_attribute_display( $attribute_not_in_variation_but_user_defined->is_visible_in_front, $wpshop_product_attributes_frontend_display, 'attribute', $attribute_not_in_variation_but_user_defined->code, 'complete_sheet');
				if ( /* $attribute_display_state &&  */array_key_exists($attribute_not_in_variation_but_user_defined->code, $output_order) && !in_array($attribute_not_in_variation_but_user_defined->code, $variation_attribute) && ($attribute_not_in_variation_but_user_defined->is_used_for_variation == 'no') ) {
					$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute_not_in_variation_but_user_defined, (is_array($head_wpshop_variation_definition) && isset($head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_not_in_variation_but_user_defined->code]) ? $head_wpshop_variation_definition['options']['attributes_default_value'][$attribute_not_in_variation_but_user_defined->code] : null ));

					$tpl_component = array();
					$attribute_output_def['option'] = ' class="wpshop_variation_selector_input' . ($is_required ? ' attribute_is_required_input attribute_is_required_input_' . $attribute_not_in_variation_but_user_defined->code : '') . ' ' . ( str_replace('"', '', str_replace('class="', '', $attribute_output_def['option'])) ) . ' ' . (( is_admin() ) ? $attribute_not_in_variation_but_user_defined->backend_css_class : $attribute_not_in_variation_but_user_defined->frontend_css_class) . '" ';
					$tpl_component['VARIATION_INPUT'] = wpshop_form::check_input_type($attribute_output_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '[free]') . $attribute_output_def['options'];
					$tpl_component['VARIATION_LABEL'] = ($is_required ? '<span class="attribute_is_required attribute_is_required_' . $attribute_not_in_variation_but_user_defined->code . '" >' . stripslashes($attribute_not_in_variation_but_user_defined->frontend_label) . '</span> <span class="required" >*</span>' : stripslashes($attribute_not_in_variation_but_user_defined->frontend_label) );
					$tpl_component['VARIATION_CODE'] = $attribute_not_in_variation_but_user_defined->code;
					$tpl_component['VARIATION_LABEL_HELPER'] = !empty($attribute_not_in_variation_but_user_defined->frontend_help_message) ? ' title="' . $attribute_not_in_variation_but_user_defined->frontend_help_message . '" ' : '';
					$tpl_component['VARIATION_LABEL_CLASS'] = !empty($attribute_not_in_variation_but_user_defined->frontend_help_message) ? ' wpshop_att_variation_helper' : '';
					$tpl_component['VARIATION_REQUIRED_INDICATION'] = ( $is_required ) ? __('Required variation', 'wpshop') : '';
					$tpl_component['VARIATION_IDENTIFIER'] = $attribute_output_def['id'];
					$tpl_component['VARIATION_PARENT_ID'] = $product_id;
					$tpl_component['VARIATION_PARENT_TYPE'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;
					$tpl_component['VARIATION_CONTAINER_CLASS'] = ($is_required ? ' attribute_is_required_container attribute_is_required_container_' . $attribute_not_in_variation_but_user_defined->code : '') . ' wpshop_variation_' . $attribute_not_in_variation_but_user_defined->code . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . ' wpshop_variation_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_' . $product_id;
					$variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_not_in_variation_but_user_defined->code)] = ($attribute_output_def['type'] != 'hidden') ? wpshop_display::display_template_element('product_variation_item', $tpl_component) : wpshop_form::check_input_type($attribute_output_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '[free]') . $attribute_output_def['options'];
					$variation_attribute_ordered[$output_order[$attribute_not_in_variation_but_user_defined->code]] = $variation_tpl['VARIATION_COMPLETE_OUTPUT_' . strtoupper($attribute_not_in_variation_but_user_defined->code)];
				}
			}
		}

		$variation_tpl['VARIATION_FORM_VARIATION_LIST'] = '';
		if ( !empty($variation_attribute_ordered) && is_array($variation_attribute_ordered) ) {
			ksort($variation_attribute_ordered);
			foreach ( $variation_attribute_ordered as $attribute_variation_to_output ) {
				$variation_tpl['VARIATION_FORM_VARIATION_LIST'] .= $attribute_variation_to_output;
			}
		}
		$variation_tpl['FROM_ADMIN_INDICATOR'] = $variation_tpl['ORDER_ID_INDICATOR'] = '';
		$variation['PRODUCT_ADDED_TO_CART_QTY'] = ( !empty( $qty ) ) ? $qty : 1;
		if ( $from_admin && !empty($order_id) ) {
			$variation_tpl['FROM_ADMIN_INDICATOR'] = '<input type="hidden" name="wps_orders_from_admin" value="1" />';
			$variation_tpl['ORDER_ID_INDICATOR'] = '<input type="hidden" name="wps_orders_order_id" value="' .$order_id. '" />';

		}
		$output = !empty($variation_tpl['VARIATION_FORM_VARIATION_LIST']) ? wpshop_display::display_template_element('product_variation_form', $variation_tpl) : '';

		return $output;
	}

	function get_parent_variation ( $variation_id ) {
		$result = array();
		if ( !empty($variation_id) ) {
			$variation_post = get_post( $variation_id);
			if ( !empty($variation_post) && !empty($variation_post->post_parent) ) {
				$result['parent_post'] = get_post($variation_post->post_parent);
				$result['parent_post_meta'] = get_post_meta($variation_post->post_parent, '_wpshop_product_metadata', true);
			}
		}
		return $result;
	}

	/**
	 * Display the current configuration for a given product
	 * @param array $shortcode_attribute Some parameters given by the shortcode for display
	 */
	function wpshop_product_variations_summary( $shortcode_attribute ) {
		global $wp_query;
		$output = '';

		if ( $wp_query->query_vars['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			$output .= wpshop_display::display_template_element('wpshop_product_configuration_summary', array('CURRENCY_SELECTOR' => wpshop_attributes_unit::wpshop_shop_currency_list_field()));
		}

		echo $output;
	}

	/**
	 * Display information for a given value of an attribute defined as an entity, when attribute option for detail view is set as true
	 *
	 * @param array $shortcode_attribute Some parameters given by the shortcode for display
	 */
	function wpshop_product_variation_value_detail( $shortcode_attribute ) {
		global $wp_query;
		if ( $wp_query->query_vars['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
			echo wpshop_display::display_template_element('wpshop_product_variation_value_detail_container', array());
		}
	}

	/**
	  * Build the product structure with variation for product choosed by the user into frontend sheet
	  *
	  * @param array $selected_variation THe list of variation choosed by the user in product frontend sheet
	  * @param integer $product_id The basic product choose by the user in frontend
	  *
	  * @return array The product list for adding to the cart build by variation priority
	  */
	function get_variation_by_priority( $selected_variation, $product_id, $add_to_cart_action = false ) {
		global $wpdb;
		$product_to_add_to_cart = array();
		$variation_attribute = array();
		$all_required_variations_selected = true;

		/** Check if all required variations are selected **/
		$required_attributes_list = wpshop_prices::check_required_attributes( $product_id );

		foreach( $selected_variation as $k => $value ) {
			if( $value == 0 && in_array($k, $required_attributes_list) ) {
				$all_required_variations_selected = false;
			}
		}



		if ( !empty( $selected_variation ) ) {
			/** Check the option parameter **/
			$product_variation_configuration = get_post_meta($product_id, '_wpshop_variation_defining', true);

			$priority = (!empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['priority'][0]) ) ?  $product_variation_configuration['options']['priority'][0] : 'combined';
			$product_to_add_to_cart[$product_id]['defined_variation_priority'] = 'combined';

			/**	Get combined variations	, Check if all variations are selected */
			$combined_variations = array();
			$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->postmeta . " AS P_META INNER JOIN " . $wpdb->posts . " as P ON ((P.ID = P_META.post_id) AND (P.post_parent = %d)) WHERE P_META.meta_key = '_wpshop_variations_attribute_def' AND P_META.meta_value = '" . serialize($selected_variation) . "'", $product_id);
			$combined_variation_id = $wpdb->get_var($query);
			if ( !empty($combined_variation_id) ) {
				$combined_variations[] = $combined_variation_id;
			}


			/**	Get single variations	*/
			$single_variations = array();
			foreach ( $selected_variation as $attribute_code => $attribute_value ) {
				if ( !empty($attribute_value) ) {
					$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->postmeta . " AS P_META INNER JOIN " . $wpdb->posts . " as P ON ((P.ID = P_META.post_id) AND (P.post_parent = %d)) WHERE P_META.meta_value = '" . serialize(array($attribute_code => $attribute_value)) . "'", $product_id);
					$single_variation_id = $wpdb->get_var($query);
					if ( !empty($single_variation_id) ) {
						$single_variations[] = $single_variation_id;
					}
				}
			}


			/** If all required attributes are not selected **/
			if ( !$all_required_variations_selected ) {
				if ( !$add_to_cart_action ) {
					$product_to_add_to_cart['text_from'] = 'on';
					/** Check the lower price **/
					$lower_price_variations = wpshop_prices::check_product_lower_price( $product_id );
					if ( !empty($lower_price_variations['variations']) && is_array($lower_price_variations['variations']) ) {
						foreach( $lower_price_variations['variations'] as $lower_price_variation ) {
							$product_to_add_to_cart[$product_id]['variations'][] = $lower_price_variation;
						}
					}
					$product_to_add_to_cart[$product_id]['variation_priority'] = ( !empty($lower_price_variations['variation_priority']) ) ? $lower_price_variations['variation_priority'] : '';
					$product_to_add_to_cart['display_lower_price'] = true;
				}
			}
			else {
				if ( ($priority == 'combined') && !empty($combined_variations) ) {
					foreach ( $combined_variations as $combined_variation_id ) {
						$product_to_add_to_cart[$product_id]['variations'][] = $combined_variation_id;
					}
					$product_to_add_to_cart[$product_id]['variation_priority'] = 'combined';
				}
				else if ( ($priority == 'combined') && empty($combined_variations) && !empty($single_variations) ) {
					foreach ( $single_variations as $single_variation_id ) {
						$product_to_add_to_cart[$product_id]['variations'][] = $single_variation_id;
					}
					$product_to_add_to_cart[$product_id]['variation_priority'] = 'single';
				}
				else if ( ($priority == 'single') && !empty($single_variations)) {
					foreach ( $single_variations as $single_variation_id ) {
						$product_to_add_to_cart[$product_id]['variations'][] = $single_variation_id;
					}
					$product_to_add_to_cart[$product_id]['variation_priority'] = 'single';
				}
				else if ( ($priority == 'single') && empty($single_variations) && !empty($combined_variations)) {
					foreach ( $combined_variations as $combined_variation_id ) {
						$product_to_add_to_cart[$product_id]['variations'][] = $combined_variation_id;
					}
					$product_to_add_to_cart[$product_id]['variation_priority'] = 'combined';
				}
				else {
					/** Check the lower price **/
					if ( !$add_to_cart_action ) {
						$lower_price_variations = wpshop_prices::check_product_lower_price( $product_id );
						if ( !empty($lower_price_variations['variations']) && is_array($lower_price_variations['variations']) ) {
							foreach( $lower_price_variations['variations'] as $lower_price_variation ) {
								$product_to_add_to_cart[$product_id]['variations'][] = $lower_price_variation;
							}

						}
						$product_to_add_to_cart[$product_id]['variation_priority'] = ( !empty($lower_price_variations['variation_priority']) ) ? $lower_price_variations['variation_priority'] : '';
						$product_to_add_to_cart['display_lower_price'] = true;

						/** Check Text From option **/
						if ( !empty($product_variation_configuration) && !empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['price_display']) && !empty($product_variation_configuration['options']['price_display']['text_from']) ) {
							$product_to_add_to_cart['text_from'] = 'on';
						}
					}
				}



				if ( !empty($selected_variation['free']) ) {
					foreach ( $selected_variation['free'] as $free_variation_code => $free_variation_value) {
						if ( !empty($free_variation_value) ) {
							$product_to_add_to_cart[$product_id]['free_variation'][$free_variation_code] = $free_variation_value;
						}
					}
				}
			}


			if ( empty($product_to_add_to_cart[$product_id]['variations']) && empty($product_to_add_to_cart[$product_id]['free_variation']) ) {
				$product_to_add_to_cart[$product_id]['variation_priority'] = 'simple';
			}

		}


		return $product_to_add_to_cart;
	}

	/**
	  * Return the good element price into cart from admin variation configuration for current product
	  *
	  * @param array $product_into_cart The complete product definition for cart and order
	  * @param array $product_variation Contain the list of selected variation choose by the client into product frontend sheet
	  * @param integer $head_product_id The basic product ht user choose variation for
	  * @param array $variations_options An array with the variation options
	  *
	  * @return array The complete product information for cart/order with the new prices defined by variations
	  */
	function get_variation_price_behaviour( $product_into_cart, $product_variation, $head_product_id, $variations_options ) {
		global $wpdb;

		$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
		if ( !empty($product_variation) ) {
			$product_variation_configuration = get_post_meta($head_product_id, '_wpshop_variation_defining', true);
			$price_behaviour = (!empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['price_behaviour'][0]) ) ?  $product_variation_configuration['options']['price_behaviour'][0] : 'replacement';

			$additionnal_price = $new_price = array();
			$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = 0;
			$new_price[WPSHOP_PRODUCT_PRICE_HT] = $new_price[WPSHOP_PRODUCT_PRICE_TTC] = $new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = 0;

			/** Single Products OR Products with many SINGLE variations **/
			if ( (count($product_variation) > 1) && ($variations_options['type'] == 'single') ) {
				$have_special_price = false;
				$product_into_cart['price_ht_before_discount'] = $product_into_cart['price_ttc_before_discount'] = 0;
				foreach ( $product_variation as $variation_id ) {
					$product_variation_def = wpshop_products::get_product_data($variation_id, true);
					$product_into_cart['item_meta']['variations'][$variation_id] = $product_variation_def;

					/** Check the Discount **/
					$discount_config = wpshop_prices::check_discount_for_product( $variation_id, $head_product_id );
					if ( !empty($discount_config) ) {

						if ( !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'special_price' ) {
							$have_special_price = true;
							$query = $wpdb->prepare( 'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $product_variation_def['tx_tva'] );
							$tx_tva = $wpdb->get_var( $query );

							$product_into_cart['special_price'] += $discount_config['value'];

							$product_into_cart['price_ht_before_discount'] +=  $product_variation_def[WPSHOP_PRODUCT_PRICE_HT];
							$product_into_cart['price_ttc_before_discount'] += $product_variation_def[WPSHOP_PRODUCT_PRICE_TTC];

							$new_price[WPSHOP_PRODUCT_PRICE_HT] += ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] : $discount_config['value'] / ( 1 + ( $product_variation_def['tx_tva'] / 100) );
							$new_price[WPSHOP_PRODUCT_PRICE_TTC] += ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] * ( 1 + ( $product_variation_def['tx_tva'] / 100) ) : $discount_config['value'];
							$new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] * ( $product_variation_def['tx_tva'] / 100) : ($discount_config['value'] / ( 1 + ( $product_variation_def['tx_tva'] / 100) ) * ( $product_variation_def['tx_tva'] / 100) );

						}
						elseif( !$have_special_price && !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'discount_amount' ) {
							$product_into_cart['discount_amount'] += $discount_config['value'];

							$product_into_cart['price_ht_before_discount'] += $product_variation_def[WPSHOP_PRODUCT_PRICE_HT];
							$product_into_cart['price_ttc_before_discount'] += $product_variation_def[WPSHOP_PRODUCT_PRICE_TTC];;

							$new_price[WPSHOP_PRODUCT_PRICE_HT] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] - $discount_config['value'] );
							$new_price[WPSHOP_PRODUCT_PRICE_TTC] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] - $discount_config['value'] ) * ( 1 + ( $product_variation_def['tx_tva'] / 100) );
							$new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] - $discount_config['value'] ) * ( $product_variation_def['tx_tva'] / 100);


						}
						elseif( !$have_special_price && !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'discount_rate' ) {
							$product_into_cart['discount_rate'] = ( !empty($product_into_cart['discount_rate']) ) ? ($product_into_cart['discount_rate'] + $discount_config['value']) / 2 : $discount_config['value'];

							$product_into_cart['price_ht_before_discount'] += $product_variation_def[WPSHOP_PRODUCT_PRICE_HT];
							$product_into_cart['price_ttc_before_discount'] += $product_variation_def[WPSHOP_PRODUCT_PRICE_TTC];


							$new_price[WPSHOP_PRODUCT_PRICE_HT] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] / ( 1 + ($discount_config['value'] / 100) ) );
							$new_price[WPSHOP_PRODUCT_PRICE_TTC] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] / ( 1 + ($discount_config['value'] / 100) ) ) * ( 1 + ( $product_variation_def['tx_tva'] / 100) );
							$new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += ( $product_variation_def[WPSHOP_PRODUCT_PRICE_HT] / ( 1 + ($discount_config['value'] / 100) ) ) * ( $product_variation_def['tx_tva'] / 100);

						}
					}

					$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] += ( !empty($new_price) && !empty($new_price[WPSHOP_PRODUCT_PRICE_HT]) ) ? $new_price[WPSHOP_PRODUCT_PRICE_HT] : $product_variation_def[WPSHOP_PRODUCT_PRICE_HT];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] += ( !empty($new_price) && !empty($new_price[WPSHOP_PRODUCT_PRICE_TTC]) ) ? $new_price[WPSHOP_PRODUCT_PRICE_TTC] : $product_variation_def[WPSHOP_PRODUCT_PRICE_TTC];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += ( !empty($new_price) && !empty($new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT]) ) ? $new_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] : $product_variation_def[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];


					if ( !empty($product_variation_def['item_meta']['variation_definition']) && is_array($product_variation_def['item_meta']['variation_definition']) ) {
						foreach ($product_variation_def['item_meta']['variation_definition'] as $attribute_variation_code => $attribute_variation_value ) {
							$product_into_cart['product_reference'] .= '#' . $variation_id;
						}
					}
				}
			}
			else {

				/** Combined Variations **/
				$head_product = wpshop_products::get_product_data($head_product_id, true);

				if ( ($product_into_cart['product_id'] == $head_product['product_id']) || count($product_variation) == 1 ) {
					$product_into_cart = wpshop_products::get_product_data($product_variation[0], true);
					if ( !empty($product_variation_configuration) && !empty($product_variation_configuration['options']) && !empty($product_variation_configuration['options']['price_behaviour']) && !empty($product_variation_configuration['options']['price_behaviour'][0]) && $product_variation_configuration['options']['price_behaviour'][0] == 'addition' ) {
						$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] += $head_product[WPSHOP_PRODUCT_PRICE_HT];
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] += $head_product[WPSHOP_PRODUCT_PRICE_TTC];
						$product_into_cart['tva'] += $head_product['tva'];
					}

				}


				/** Check the Discount **/
				$discount_config = wpshop_prices::check_discount_for_product( $product_into_cart['product_id'], $head_product_id );
				if ( !empty($discount_config) ) {
					$have_special_price = false;
					if ( !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'special_price' ) {
						$have_special_price = true;
						$product_into_cart['special_price'] = $discount_config['value'];

						$product_into_cart['price_ht_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_HT];
						$product_into_cart['price_ttc_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_TTC];

						$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] : $discount_config['value'] / ( 1 + ( $product_into_cart['tx_tva'] / 100) );
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] * ( 1 + ( $product_into_cart['tx_tva'] / 100) ) : $discount_config['value'];
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ?  $discount_config['value'] * ( $product_into_cart['tx_tva'] / 100) : ($discount_config['value'] / ( 1 + ( $product_into_cart['tx_tva'] / 100) ) * ( $product_into_cart['tx_tva'] / 100) );

					}
					elseif( !$have_special_price && !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'discount_amount' ) {
						$product_into_cart['discount_amount'] = $discount_config['value'];

						$product_into_cart['price_ht_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_HT];
						$product_into_cart['price_ttc_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_TTC];

						$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = ( $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] - $discount_config['value'] );

						$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = ( $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] ) * ( 1 + ( $product_into_cart['tx_tva'] / 100) );
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = ( $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] ) * ( $product_into_cart['tx_tva'] / 100);

					}
					elseif( !$have_special_price && !empty($discount_config) && !empty($discount_config['type']) && $discount_config['type'] == 'discount_rate' ) {
						$product_into_cart['discount_rate'] = ( !empty($product_into_cart['discount_rate']) ) ? ($product_into_cart['discount_rate'] + $discount_config['value']) / 2 : $discount_config['value'];

						$product_into_cart['price_ht_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_HT];
						$product_into_cart['price_ttc_before_discount'] = $product_into_cart[WPSHOP_PRODUCT_PRICE_TTC];

						$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = ( $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] / ( 1 + ($discount_config['value'] / 100) ) );
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] * ( 1 + ( $product_into_cart['tx_tva'] / 100) );
						$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $product_into_cart[WPSHOP_PRODUCT_PRICE_HT] * ( $product_into_cart['tx_tva'] / 100);

					}
				}
				if ( empty($price_behaviour) || $price_behaviour == 'replacement' ) {
					$additionnal_price[WPSHOP_PRODUCT_PRICE_HT] += $product_into_cart[WPSHOP_PRODUCT_PRICE_HT];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TTC] += $product_into_cart[WPSHOP_PRODUCT_PRICE_TTC];
					$additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += $product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
				}

				/*	Reinitialise basic information	*/
				if ( empty($product_variation_configuration) ) {
					$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = $head_product[WPSHOP_PRODUCT_PRICE_HT];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = $head_product[WPSHOP_PRODUCT_PRICE_TTC];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $head_product[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
					$product_into_cart['product_name'] = $head_product['product_name'];
					$product_into_cart['item_meta']['head_product'][$product_into_cart['product_id']] = $head_product_id;
				}
			}

			/*	If variation are existing we add the prices to the default price	*/
			if ( !empty($additionnal_price) ) {
				if ( $price_behaviour == 'addition' ) {
					$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] += $additionnal_price[WPSHOP_PRODUCT_PRICE_HT];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] += $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] += $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];

				}
				else {
					$product_into_cart[WPSHOP_PRODUCT_PRICE_HT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_HT];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TTC] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TTC];
					$product_into_cart[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $additionnal_price[WPSHOP_PRODUCT_PRICE_TAX_AMOUNT];
				}
			}
		}

		if( !empty($variations_options['text_from']) ) {
			$product_into_cart['text_from'] = 'on';
		}

		return $product_into_cart;
	}

	/**
	 * Read an array with product options chosen by the customer, order into an array regarding admin definition
	 *
	 * @param array $product_definition_value The array with the selected product option to ordered
	 * @param array $output_order The good order for attribute defined by administrator
	 * @param dtring $from_page A string allowing to take a specific template regarding the current page
	 *
	 * @return array The array containing all product options ordered as the admin configure it
	 */
	function get_selected_variation_display( $product_definition_value, $output_order, $from_page = null, $template_part = 'wpshop' ) {

		$variation_attribute_ordered = array();
		$variation_attribute_ordered['prices'] = array();
		$variation_attribute_ordered['attribute_list'] = array();

		if ( !empty( $product_definition_value['variation_definition'] ) ) {
			foreach ( $product_definition_value['variation_definition'] as $variation_attribute_code => $variation_attribute_detail ) {
				$variation_tpl_component = array();
				foreach ( $variation_attribute_detail as $info_name => $info_value) {
					$variation_tpl_component['VARIATION_' . strtoupper($info_name)] = in_array($info_name, unserialize(WPSHOP_ATTRIBUTE_PRICES)) ? wpshop_display::format_field_output('wpshop_product_price', $info_value)  : stripslashes($info_value);
				}
				$variation_tpl_component['VARIATION_ID'] = $variation_attribute_code;
				$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;
				$variation_attribute_ordered['attribute_list'][$output_order[$variation_attribute_code]] = wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component, array('page' => $from_page, 'type' => WPSHOP_DBT_ATTRIBUTE, 'id' => $variation_attribute_code), $template_part);

				unset($variation_tpl_component);
			}
		}

		if (!empty($product_definition_value['variations'])) {
			foreach ( $product_definition_value['variations'] as $variation_id => $variation_details ) {
				$variation_tpl_component = array();
				foreach ( $variation_details as $info_name => $info_value) {
					if ( $info_name != 'item_meta' ) {
						$variation_tpl_component['VARIATION_DETAIL_' . strtoupper($info_name)] = in_array($info_name, unserialize(WPSHOP_ATTRIBUTE_PRICES)) ? wpshop_display::format_field_output('wpshop_product_price', $info_value)  : stripslashes($info_value);
					}
				}
				foreach ( $variation_details['item_meta']['variation_definition'] as $variation_attribute_code => $variation_attribute_def ) {
					$variation_tpl_component['VARIATION_NAME'] = stripslashes($variation_attribute_def['NAME']);
					$variation_tpl_component['VARIATION_VALUE'] = stripslashes($variation_attribute_def['VALUE']);
					$variation_tpl_component['VARIATION_ID'] = $variation_id;
					$variation_tpl_component['VARIATION_ATT_CODE'] = $variation_attribute_code;

					$variation_attribute_ordered['prices'][$variation_attribute_code] = $variation_tpl_component['VARIATION_DETAIL_PRODUCT_PRICE'];
				}
				$variation_attribute_ordered['attribute_list'][$output_order[$variation_attribute_code]] = wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component, array('page' => $from_page, 'type' => WPSHOP_DBT_ATTRIBUTE, 'id' => $variation_attribute_code), $template_part);
				unset($variation_tpl_component);
			}
		}

		/**	Free Variation part	*/
		if ( !empty($product_definition_value['free_variation']) ) {
			foreach ( $product_definition_value['free_variation'] as $build_variation_key => $build_variation ) {
				if ( strpos($build_variation, '-_variation_val_-')) {
					$variation_definition = explode('-_variation_val_-', $build_variation);
					$attribute_code = $variation_definition[0];
					$attribute_selected_value = $variation_definition[1];
				}
				else {
					$attribute_code = $build_variation_key;
					$attribute_selected_value = $build_variation;
				}

				$free_variation_attribute_def = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
				$variation_tpl_component['VARIATION_NAME'] = stripslashes($free_variation_attribute_def->frontend_label);
				$value_to_outut = $attribute_selected_value;
				switch ( $free_variation_attribute_def->data_type ) {
					case 'datetime':
						$value_to_outut = mysql2date('d F Y', $attribute_selected_value, true);
					break;
				}
				$variation_tpl_component['VARIATION_VALUE'] = stripslashes($value_to_outut);
				$variation_tpl_component['VARIATION_ID'] = $attribute_code;
				$variation_tpl_component['VARIATION_ATT_CODE'] = $attribute_code;
				if ( !empty($value_to_outut) ) {
					$variation_attribute_ordered['attribute_list'][$output_order[$free_variation_attribute_def->code]] = wpshop_display::display_template_element('cart_variation_detail', $variation_tpl_component, array('page' => $from_page, 'type' => WPSHOP_DBT_ATTRIBUTE, 'id' => $attribute_code), $template_part);
				}
				unset($variation_tpl_component);
			}
		}

		return $variation_attribute_ordered;
	}



}