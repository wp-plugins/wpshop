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

		add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
		add_meta_box('wpshop_product_document_management', __('Document management', 'wpshop'), array('wpshop_products', 'meta_box_document'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');

		/*	Get the attribute set list for the current entity	*/
		$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
		/*	Check if the meta information of the current product already exists 	*/
		$post_attribute_set_id = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
		/*	Check if the product has been saved without meta information set	*/
		$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);

		/*	Check if an attribute has already been choosen for the curernt entity or if the user has to choose a entity set before continuing	*/
		if((count($attributeEntitySetList) == 1) || ((count($attributeEntitySetList) > 1) && (($post_attribute_set_id > 0) || (isset($attribute_set_id->value) && ($attribute_set_id->value > 0))))){
			if((count($attributeEntitySetList) == 1) || (($post_attribute_set_id <= 0) && ($attribute_set_id->value <= 0))){
				$post_attribute_set_id = $attributeEntitySetList[0]->id;
			}
			elseif(($post_attribute_set_id <= 0) && ($attribute_set_id->value > 0)){
				$post_attribute_set_id = $attribute_set_id->value;
			}
			$currentTabContent = wpshop_attributes::getAttributeFieldOutput($post_attribute_set_id, self::currentPageCode, $post->ID);
			/*	Get all the other attribute set for hte current entity	*/
			if(isset($currentTabContent['box']) && count($currentTabContent['box']) > 0){
				foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
					add_meta_box('wpshop_product_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default', array('boxIdentifier' => $boxIdentifier));
				}
			}
		}
		elseif(count($attributeEntitySetList) > 1){
			$input_def['id'] = 'product_attribute_set_id';
			$input_def['name'] = 'product_attribute_set_id';
			$input_def['value'] = '';
			$input_def['type'] = 'select';
			$input_def['possible_value'] = $attributeEntitySetList;
			$input_def['value'] = '';
			$currentTabContent['boxContent']['attribute_set_selector'] = '
<div class="attribute_set_selector" >
	<div class="wpshopRequired bold" >' . __('You have to choose one of existing attribute set. You won\'t be able to change it later.', 'wpshop') . '</div>
	<br/>
	<div class="bold" >' . __('Attributes associated to the selected set will be outputed when product will be saved', 'wpshop') . '</div>
	' . wpshop_form::check_input_type($input_def, self::currentPageCode) . '
</div>';

			add_meta_box('wpshop_product_attribute_set_selector', __('Attribute set for the current product', 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'high', array('boxIdentifier' => 'attribute_set_selector'));
		}
	}
	
	function wpshop_product_func($atts) {
		global $wpdb;
		//$args = array('taxonomy' => 'wpshop_product_category', 'type' => 'wpshop_product_category');
		//$categories = get_categories( $args );
		$query = 'SELECT wp_term_taxonomy.term_id
		FROM wp_term_taxonomy
		LEFT JOIN wp_term_relationships ON wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id
		WHERE wp_term_taxonomy.taxonomy="wpshop_product_category" AND wp_term_relationships.object_id='.$atts['pid'].'';
		$categories = $wpdb->get_results($query);
		if(!empty($categories)) {
			//return $categories[0]->term_id;
			return wpshop_products::product_mini_output($atts['pid'], $categories[0]->term_id, $atts['type']);
			//return wpshop_products::test($atts['pid'], $categories[0]->term_id, $atts['type']);
		}
		//return 'test produit<br />';
	}
	function test($a1, $a2, $a3) {
		return $a1.' '.$a2.' '.$a3;
	}
	
	function product_list($formated=false, $product_search=null) {
		global $wpdb;
		if(!empty($product_search)) {
			$query = '
				SELECT ID, post_title FROM '.$wpdb->prefix.'posts 
				WHERE 
					post_type="wpshop_product" 
					AND post_status="publish" 
					AND post_title LIKE "%'.$product_search.'%"
				';
		}
		else {
			$query = 'SELECT ID, post_title FROM '.$wpdb->prefix.'posts WHERE post_type="wpshop_product" AND post_status="publish"';
		}
		$data = $wpdb->get_results($query);
		
		// Si le formatage est demandé
		if($formated) {
			$product_string='';
			foreach($data as $d) {
				$product_string.= '<li><label><input type="checkbox" value="'.$d->ID.'" name="products[]" /> '.$d->post_title.'</label></li>';
			}
		}
		return $formated?$product_string:$data;
	}
	
	function product_list_attr($formated=false, $product_search=null) {
		global $wpdb;
		$query = '
		SELECT '.WPSHOP_DBT_ATTRIBUTE.'.data_type, '.WPSHOP_DBT_ATTRIBUTE.'.id AS id_attribut, '.$wpdb->prefix.'posts.post_title, '.$wpdb->prefix.'posts.ID, '.WPSHOP_DBT_ATTRIBUTE.'.frontend_label, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.value AS value_decimal, '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.value AS value_datetime, '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.value AS value_integer, '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.value AS value_text, '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.value AS value_varchar, '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.unit AS unit
		FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE.' ON '.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.' ON '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.attribute_id='.WPSHOP_DBT_ATTRIBUTE.'.id
			
			LEFT JOIN '.$wpdb->prefix.'posts ON (
				'.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.entity_id='.$wpdb->prefix.'posts.ID
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.entity_id='.$wpdb->prefix.'posts.ID
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.entity_id='.$wpdb->prefix.'posts.ID
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.entity_id='.$wpdb->prefix.'posts.ID
				OR '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.entity_id='.$wpdb->prefix.'posts.ID
			)
			
			LEFT JOIN '.WPSHOP_DBT_ATTRIBUTE_UNIT.' ON (
				'.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.'.unit_id 
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.'.unit_id
				OR '.WPSHOP_DBT_ATTRIBUTE_UNIT.'.id='.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.'.unit_id
			)
		WHERE 
			'.WPSHOP_DBT_ATTRIBUTE_DETAILS.'.status="valid"
			AND '.WPSHOP_DBT_ATTRIBUTE.'.status="valid"
			AND '.$wpdb->prefix.'posts.post_type="wpshop_product" AND '.$wpdb->prefix.'posts.post_status="publish"
			'.(!empty($product_search)?'AND '.$wpdb->prefix.'posts.post_title LIKE "%'.$product_search.'%"':null).'
		';
		$data = $wpdb->get_results($query);
		$products=array();
		foreach($data as $d) {
			if(!isset($products[$d->ID])) {
				$products[$d->ID]['id'] = $d->ID;
				$products[$d->ID]['name'] = $d->post_title;
			}
			$products[$d->ID]['attributs'][]=array(
				'id' => $d->id_attribut,
				'type' => $d->data_type,
				'label' => __($d->frontend_label, 'wpshop'), 
				'value' => $d->value_decimal.$d->value_datetime.$d->value_integer.$d->value_text.$d->value_varchar,
				'unit' => $d->unit
			);
		}
		unset($data);
		
		// Si le formatage est demandé
		if($formated) {
			$products_attr_string='';
			foreach($products as $p) {
				$products_attr_string .= '<li><b>'.$p['name'].'</b>
						<ul>';
				foreach($p['attributs'] as $p2) {
					$products_attr_string .= '<li><label><input type="checkbox" value="'.$p['id'].'-'.$p2['id'].'-'.$p2['type'].'" name="attributs[]" /> '.$p2['label'].'</label></li>';
				}
				$products_attr_string .= '</ul></li>';
			}
		}
		
		return $formated?$products_attr_string:$products;
	}
	
	
	function product_list_group_attr($formated=false, $product_search=null) {
		global $wpdb;
		$query = '
			SELECT '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.id, '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.code, '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.name
			FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.'
			WHERE '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.status="valid"
		';
		$data = $wpdb->get_results($query);
		
		// Si le formatage est demandé
		if($formated) {
			$products = self::product_list(false, $product_search);
			$groups_string='';
			foreach($products as $d) {
				$groups_string .= '<li><b>'.$d->post_title.'</b>
						<ul>';
				foreach($data as $g) {
					$groups_string .= '<li><label><input type="checkbox" value="'.$d->ID.'-'.$g->id.'" name="groups[]" /> '.__($g->name, 'wpshop').'</label></li>';
				}
				$groups_string .= '</ul></li>';
			}
		}
		
		return $formated?$groups_string:$data;
	}
	/**
	*	Define the content of the product main information box
	*/
	function main_information_meta_box_content(){
		global $currentTabContent,$post;

		add_action('admin_footer', array('wpshop_init', 'admin_js_footer'));

		/*	Add the extra fields defined by the default attribute group in the general section	*/
			/*	Get the general attribute set for outputting the result	*/
		if(is_array($currentTabContent['generalTabContent'])){
			$the_form_general_content .= implode('
			', $currentTabContent['generalTabContent']);

			$input_def['id'] = 'product_attribute_set_id';
			$input_def['name'] = 'product_attribute_set_id';
			$input_def['value'] = '';
			$input_def['type'] = 'hidden';
			$input_def['value'] = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
			if($input_def['value'] == ''){
				$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);
				if($attribute_set_id > 0){
					$input_def['value'] = $attribute_set_id->value;
				}
				else{
					$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
					$input_def['value'] = $attributeEntitySetList[0]->id;
				}
			}
			$the_form_general_content .= wpshop_form::check_input_type($input_def, self::currentPageCode);

			echo '<label>Code d\'insertion du produit</label> <code>[wpshop_product pid="'.$post->ID.'" type="list"]</code> ou <code>[wpshop_product pid="'.$post->ID.'" type="grid"]</code><br /><br />
			<div class="wpshop_extra_field_container" >' . $the_form_general_content . '</div>';
		}
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
<ul id="product_picture_list" class="product_attachment_list clear" >' . self::product_attachement_by_type($post->ID, 'image/', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=library&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".reload_box_attachment img").click(function(){
			jQuery(this).attr("src", "' . admin_url('images/loading.gif') . '");
			jQuery("#product_picture_list").load(WPSHOP_AJAX_FILE_URL,{
				"post": "true",
				"elementCode": "product_attachment",
				"elementIdentifier": "' . $post->ID . '",
				"elementType": "product",
				"attachement_type": "image/",
				"part_to_reload": "reload_box_picture"
			});
		});
	});
</script>';

		echo $product_picture_galery_metabox_content;
	}
	/**
	*	Define the metabox for managing products documents
	*/
	function meta_box_document($post, $metaboxArgs){
		global $post;
		$product_document_galery_metabox_content = '';

		$product_document_galery_metabox_content = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox clear" title="Manage Your Product Document" >' . __('Add documents for the document', 'wpshop' ) . '</a>
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_document" /></div>
<ul id="product_document_list" class="product_attachment_list clear" >' . self::product_attachement_by_type($post->ID, 'application/pdf', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=library&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".reload_box_attachment img").click(function(){
			jQuery(this).attr("src", "' . admin_url('images/loading.gif') . '");
			jQuery("#product_document_list").load(WPSHOP_AJAX_FILE_URL,{
				"post": "true",
				"elementCode": "product_attachment",
				"elementIdentifier": "' . $post->ID . '",
				"elementType": "product",
				"attachement_type": "application/pdf",
				"part_to_reload": "reload_box_document"
			});
		});
	});
</script>';

		echo $product_document_galery_metabox_content;
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
			update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
		}

		/*	Update the attribute set id for the current product	*/
		update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $_REQUEST[self::currentPageCode]['product_attribute_set_id']);

		flush_rewrite_rules();
	}
	/**
	*	Allows to define a specific permalink for each product by checking the parent categories
	*
	*	@param mixed $permalink The actual permalink of the element
	* @param object $post The post we want to set the permalink for
	*	@param void
	*
	*	@return mixed The new permalink for the current element
	*/
	function set_product_permalink($permalink, $post, $unknown){
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
		if(is_array($attachments) && (count($attachments) > 0)){
			$product_thumbnail = get_post_thumbnail_id($product_id);
			$attachmentsNumber = 0;
			foreach ($attachments as $attachment){
				if(is_int(strpos($attachment->post_mime_type, $attachement_type))){
					$url = $attachment->guid;
					$link_option = '';
					if($url_on_click != ''){
						$url = $url_on_click;
						$link_option = ' class="thickbox" ';
					}
					/*	Build the attachment output with the different parameters	*/
					$attachment_icon = 0;
					$attachement_more_informations = '';
					if($attachement_type == 'image/'){
						if($link_option == ''){
							$link_option = 'rel="appendix"';
						}
						$li_class = "product_picture_item";
						if($product_thumbnail == $attachment->ID){
							$attachement_more_informations = '<br/><span class="product_thumbnail_indicator" >' . __('Product thumbnail', 'wpshop') . '</span>';
						}
					}
					else{
						if($link_option == ''){
							$link_option = 'target="product_document"';
						}
						$li_class = "product_document_item";
						$attachment_icon = 1;
						$attachement_more_informations = '<br/><span>' . $attachment->post_title . '</span>';
					}

					/*	Add the attchment to the list	*/
					$product_attachement_list .= '<li class="' . $li_class . '" ><a href="' . $url . '" ' . $link_option . ' >' . wp_get_attachment_image($attachment->ID, 'full', $attachment_icon) . '</a>' . $attachement_more_informations . '</li>';
					$attachmentsNumber++;
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
					$product_document_galery .= '<li class="product_document_item" ><a href="' . $attachment->guid . '" target="product_document" >' . wp_get_attachment_image($attachment->ID, 'full', 1) . '<br/><span>' . $attachment->post_title . '</span></a></li>';
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
				
				$product_atribute_list[$product_id][$attributeSetSectionName]['count']=$attributeToShowNumber;
				$product_atribute_list[$product_id][$attributeSetSectionName]['output']=$attributeOutput;
			}
			
			// Gestion de l'affichage
			$tab_list = $content_list = '';
			foreach($product_atribute_list[$product_id] as $attributeSetSectionName => $attributeSetContent){
				if(!empty($attributeSetContent['count'])>0) {
						$tab_list .= '
						<li>
							<a href="#'.$attributeSetContent['code'].'">'.__($attributeSetSectionName, 'wpshop').'</a>
						</li>';
						$content_list .= '
							<div id="'.$attributeSetContent['code'].'">
								'.$attributeSetContent['output'].'
							</div>
						';
				}
			}
			if($tab_list != ''){
			$attributeContentOutput = '
				<div id="wpshopFormManagementContainer">
					<ul>' . $tab_list . '</ul>
					' . $content_list . '
				</div>';
			}

		}

		/*	Include the product sheet template	*/
		ob_start();
		require_once(wpshop_display::get_template_file('product.tpl.php'));
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
	/**
	*	Display a product not a list
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
		require(wpshop_display::get_template_file('product-mini-' . $output_type . '.tpl.php'));
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
				require(wpshop_display::get_template_file('categories_products-widget.tpl.php'));
				$widget_content .= ob_get_contents();
				ob_end_clean();
			}
		}

		echo $widget_content;
	}

}