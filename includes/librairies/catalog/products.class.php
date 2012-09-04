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

/*	Vérification de l'inclusion correcte du fichier => Interdiction d'acceder au fichier directement avec l'url	*/
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
class wpshop_products {
	/**
	*	Définition du code de la classe courante
	*/
	const currentPageCode = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT;

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
		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
			array(
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
				'public' 				=> true,
				'show_ui' 				=> false,
				'publicly_queryable' 	=> false,
				'exclude_from_search' 	=> true,
				'hierarchical' 			=> false,
				'rewrite' 				=> false,
				'query_var'				=> true,
				'supports' 				=> array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
				'show_in_nav_menus' 	=> false
			)
		);

		// add to our plugin init function
		global $wp_rewrite;
		/*	Slug url is set into option	*/
		$options = get_option('wpshop_catalog_product_option', array());
		$gallery_structure = (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : 'catalog') . '/%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%/%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%';
		$wp_rewrite->add_rewrite_tag('%' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '%', '([^/]+)', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . "=");
		$wp_rewrite->add_permastruct(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, $gallery_structure, false);
	}

	/** 
	 * Set the colums for the custom page
	 * @return array
	*/
	function product_edit_columns($columns, $post){
	  $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Product name', 'wpshop'),
		'product_price_ttc' => __('Price', 'wpshop'),
		'product_stock' => __('Stock', 'wpshop'),
		'date' => __('Date', 'wpshop'),
		'product_actions' => __('Actions', 'wpshop')
	  );

	  return $columns;
	}

	/** 
	 * Content by colums for the custom page
	 * @return array
	*/
	function product_custom_columns($column, $post_identifier){
		$product = self::get_product_data($post_identifier);

		switch ($column) {
			case "product_price_ttc":
				if(!empty($product[WPSHOP_PRODUCT_PRICE_TTC]))
					echo number_format($product[WPSHOP_PRODUCT_PRICE_TTC],2,'.', ' ').' EUR';
				else echo '<strong>-</strong>';
			break;

			case "product_stock":
				if(!empty($product['product_stock']))
					echo (int)$product['product_stock'].' '.__('unit(s)','wpshop');
				else echo '<strong>-</strong>';
			break;

			case "product_actions":
				$buttons = '<p>';
				// Voir le produit
				$buttons .= '<a class="button" href="'.admin_url('post.php?post='.$post_identifier.'&action=edit').'">'.__('Edit', 'wpshop').'</a>';
				$buttons .= '</p>';
				echo $buttons;
			break;
		  }
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes(){
		global $post, $currentTabContent;

		if(!empty($post->post_type) && $post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) {

			/*	Get the attribute set list for the current entity	*/
			$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
			/*	Check if the meta information of the current product already exists 	*/
			$post_attribute_set_id = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
			/*	Check if the product has been saved without meta information set	*/
			$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);

			/*	Check if an attribute has already been choosen for the curernt entity or if the user has to choose a entity set before continuing	*/
			if(($post->post_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) && ((count($attributeEntitySetList) == 1) || ((count($attributeEntitySetList) > 1) && (($post_attribute_set_id > 0) || (isset($attribute_set_id->value) && ($attribute_set_id->value > 0)))))){
				if((count($attributeEntitySetList) == 1) || (($post_attribute_set_id <= 0) && ($attribute_set_id->value <= 0))){
					$post_attribute_set_id = $attributeEntitySetList[0]->id;
				}
				elseif(($post_attribute_set_id <= 0) && ($attribute_set_id->value > 0)){
					$post_attribute_set_id = $attribute_set_id->value;
				}
				$currentTabContent = wpshop_attributes::getAttributeFieldOutput($post_attribute_set_id, self::currentPageCode, $post->ID);

				$fixed_box_exist = false;
				/*	Get all the other attribute set for hte current entity	*/
				if(isset($currentTabContent['box']) && count($currentTabContent['box']) > 0){
					foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
						if(!empty($currentTabContent['box'][$boxIdentifier.'_backend_display_type']) &&( $currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='movable-tab')){
							add_meta_box('wpshop_product_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_products', 'meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default', array('boxIdentifier' => $boxIdentifier));
						}
						else $fixed_box_exist = true;
					}
				}
				if($fixed_box_exist) {
					add_meta_box('wpshop_product_fixed_tab', __('Product data', 'wpshop'), array('wpshop_products', 'product_data_meta_box'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high', array('currentTabContent' => $currentTabContent));
				}

				if ( WPSHOP_STAT_PRICE ) {
					add_meta_box('wpshop_product_price_stats', __('Product price statistic', 'wpshop'), array('wpshop_products', 'meta_box_stat_price'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				}
// 				add_meta_box('wpshop_product_variations', __('Product variation', 'wpshop'), array('wpshop_products', 'meta_box_variations'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				add_meta_box('wpshop_product_document_management', __('Document management', 'wpshop'), array('wpshop_products', 'meta_box_document'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
				// Actions
				add_meta_box('wpshop_product_actions', __('Actions', 'wpshop'), array('wpshop_products', 'product_actions_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');
			}
			elseif(count($attributeEntitySetList) > 1){
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
		}
	}

	/** Display the fixed box
	*/
	function product_data_meta_box($post, $metaboxArgs) {

		$currentTabContent = $metaboxArgs['args']['currentTabContent'];

		echo '<div id="fixed-tabs" class="wpshop_tabs wpshop_detail_tabs wpshop_product_attribute_tabs" >
				<ul>';
		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<li><a href="#tabs-'.$boxIdentifier.'">'.__($boxTitle, 'wpshop').'</a></li>';
					}
				}
			}
		}
		echo '<li><a href="#tabs-product-related">'.__('Related products', 'wpshop').'</a></li>';
		echo '</ul>';

		if(!empty($currentTabContent['box'])){
			foreach($currentTabContent['box'] as $boxIdentifier => $boxTitle){
				if(!empty($currentTabContent['boxContent'][$boxIdentifier])) {
					if($currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='fixed-tab') {
						echo '<div id="tabs-'.$boxIdentifier.'">'.$currentTabContent['boxContent'][$boxIdentifier].'</div>';
					}
				}
			}
		}
		
		echo '<div id="tabs-product-related">'.self::related_products_meta_box_content().'</div>';
		if (!empty($currentTabContent['boxMore'])) {
			echo $currentTabContent['boxMore'];
		}
		echo '</div>';
	}

	/**
	*	Define the content of the product main information box
	*/
	function related_products_meta_box_content(){
		global $currentTabContent,$post;
		
		$content='';

		if(!empty($post->ID)) {
			$related_products_id = get_post_meta($post->ID, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
			if(!empty($related_products_id))
				$related_products_data = self::product_list($formated=false, $related_products_id);
		}

		$content = __('Type the begin of the product name in the field below in order to add it to the related product list', 'wpshop') . '<br/><br/>
			<input type="text" id="demo-input-wpshop-theme" name="blah2" />
			<input type="hidden" id="related_products_list" name="related_products_list" value="" />
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#demo-input-wpshop-theme").tokenInput(WPSHOP_AJAX_FILE_URL, {
					theme: "wpshop"
				});
			});
			</script>
		';

		// Si la liste n'est pas vide
		if(!empty($related_products_data)) {
			$content .= '<script type="text/javascript">jQuery(document).ready(function() {';
			foreach($related_products_data as $p) {
				$content .= 'jQuery("#demo-input-wpshop-theme").tokenInput("add", {id: '.$p->ID.', name: "'.$p->post_title.'"});';
			}
			$content .= '});</script>';
		}
		
		return $content;
	}


	/**
	* Traduit le shortcode et affiche les produits en relation demand�
	*
	* @param array $atts {
	*	pid : id du produit en question
	*	display_mode : type d'affichage (grid ou list)
	* }
	*
	* @return string
	*
	**/
	function wpshop_related_products_func($atts) {
		$atts['product_type'] = 'related';
		return self::wpshop_products_func($atts);
	}

	function get_sorting_criteria() {
		global $wpdb;
		$data = array(array('code' => 'title', 'frontend_label' => __('Product name', 'wpshop')), array('code' => 'date', 'frontend_label' => __('Date added', 'wpshop')), array('code' => 'modified', 'frontend_label' => __('Date modified', 'wpshop')));
		$query = $wpdb->prepare('SELECT code, frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE is_used_for_sort_by="yes"');
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
			// Find which table to take
			if($data['data_type']=='datetime') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME; }
			elseif($data['data_type']=='decimal') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL; }
			elseif($data['data_type']=='integer') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER; }
			elseif($data['data_type']=='options') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS; }
			elseif($data['data_type']=='text') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT; }
			elseif($data['data_type']=='varchar') { $table_name = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR; }

			if(isset($table_name)) {
				// If the value is an id of a select, radio or checkbox
				if(in_array($data['backend_input'], array('select','radio','checkbox'))) {

					$query = $wpdb->prepare("
						SELECT ".$table_name.".entity_id FROM ".$table_name."
						LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
						LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS." AS ATT_OPT ON ".$table_name.".value = ATT_OPT.id
						WHERE ATT.code=%s AND ATT_OPT.value=%s", $attr_name, $attr_value // force useless zero like 48.58000
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
			} else return __('Incorrect shortcode','wpshop');
		} else return __('Incorrect shortcode','wpshop');

		if(!empty($data)) {
			foreach($data as $p) {
				$products[] = $p->entity_id;
			}
		}
		return $products;
	}

	/**
	* Traduit le shortcode et affiche les produits demand�
	*
	* @param array $atts {
	*	limit : limite de r�sultats de la requete
	*	order : param�tre de tri
	*	sorting : sens du tri (asc, desc)
	*	type : type d'affichage (grid, list), seulement pour display=normal
	*	display : taille d'affichage, normal (gd format avec images) ou mini (petit format sans image)
	* }
	*
	* @return string
	*
	**/
	function wpshop_products_func($atts){
		global $wpdb, $wp_query, $wpshop_shop_type;

		$have_results = false;
		$output_results = true;
		$type = (empty($atts['type']) OR !in_array($atts['type'], array('grid','list'))) ? WPSHOP_DISPLAY_LIST_TYPE : $atts['type'];
		$pagination = isset($atts['pagination']) ? intval($atts['pagination']) : WPSHOP_ELEMENT_NB_PER_PAGE;
		$cid = !empty($atts['cid']) ? $atts['cid'] : 0;
		$pid = !empty($atts['pid']) ? $atts['pid'] : 0;
		$order_by_sorting = !empty($atts['sorting']) && $atts['sorting']=='DESC'?'DESC':'ASC';
		$limit = isset($atts['limit']) ? intval($atts['limit']) : 0;
		$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;
		$attr='';

		$sorting_criteria = self::get_sorting_criteria();
		// If the order criteria isn't in the $sorting_criteria list we set it to null
		$bool = false;
		foreach($sorting_criteria as $sc) { if(!empty($atts['order']) && !empty($sc['code']) && $atts['order'] == $sc['code']) $bool = true; }
		if(!$bool) $atts['order'] = null;

		// Get products which have att_name equal to att_value
		if(!empty($atts['att_name']) && !empty($atts['att_value'])) {

			$attr = $atts['att_name'].':'.$atts['att_value'];

			$products = self::get_products_matching_attribute($atts['att_name'], $atts['att_value']);

			// Foreach on the found products
			if(!empty($products)) {
				$pid = implode(',',$products);
				if(empty($pid))$output_results = false;
			} else $output_results = false;
		}

		// Get related products
		if(!empty($atts['product_type'])){
			switch($atts['product_type']){
				case 'related':
					$product_id = !empty($atts['pid']) ? $atts['pid'] : get_the_ID();
					$type = !empty($atts['display_mode']) && in_array($atts['display_mode'],array('list','grid')) ? $atts['display_mode'] : WPSHOP_DISPLAY_LIST_TYPE;
					$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;

					$pids = get_post_meta($product_id, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
					$pid = implode(',', $pids);
					if(empty($pid))$output_results = false;
				break;
			}
		}

		//	Output all the products
		if($output_results){
			$data = self::wpshop_get_product_by_criteria($atts['order'], $cid, $pid, $type, $order_by_sorting, 1, $pagination, $limit, $grid_element_nb_per_line);
			if($data[0]) {
				$have_results = true;
				$string = $data[1];
			}
		}

		// if there are result to display
		if($have_results) {

			$sorting = '';
			ob_start();
			require(wpshop_display::get_template_file('product_listing_sorting.tpl.php'));
			$sorting = ob_get_contents();
			ob_end_clean();

			$string = '<div class="wpshop_products_block">'.$sorting.'<div class="wpshop_product_container">'.$string.'</div></div>';
		}
		else {
			$string = __('There is nothing to output here', 'wpshop');
		}

		return do_shortcode($string);
	}

	/**

	*/
	function get_html_product($post_id, $display_type, $current_position, $grid_element_nb_per_line=WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE) {
		$cats = get_the_terms($post_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
		$cats = !empty($cats) ? array_values($cats) : array();
		$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
		return self::product_mini_output($post_id, $cat_id, $display_type, $current_position, $grid_element_nb_per_line);
	}

	/**

	*/
	function wpshop_get_product_by_criteria($criteria=null, $cid=0, $pid=0, $display_type, $order='ASC', $page_number, $products_per_page=0, $nb_of_product_limit=0, $grid_element_nb_per_line=WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE){
		global $wpdb;

		$string = '<span id="wpshop_loading">&nbsp;</span>';
		$have_results = false;
		$display_type = (!empty($display_type) && in_array($display_type,array('grid','list'))) ? $display_type : 'grid';

		$query = array(
		 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
		 'order' => $order,
		 'posts_per_page' => $products_per_page,
		 'paged' => $page_number
		);

		// If the limit is greater than zero, hide pagination and change posts_per_page var
		if($nb_of_product_limit>0) {
			$query['posts_per_page'] = $nb_of_product_limit;
			unset($query['paged']);
		}
		if(!empty($pid)) {
			if(!is_array($pid)){
				$pid = explode(',', $pid);
			}
			$query['post__in'] = $pid;
		}
		if(!empty($cid)) {
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
				case 'title':
				case 'date':
				case 'modified':
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

		$custom_query = new WP_Query($query);

		if($custom_query->have_posts()) {

			$have_results = true;

			// ---------------- //
			// Products listing //
			// ---------------- //
			$current_position = 1;
			$string .= '<div class="container_product_listing" ><ul class="products_listing '. $display_type . '_' . $grid_element_nb_per_line.' '. $display_type .'_mode clearfix" >';
			while ($custom_query->have_posts()) : $custom_query->the_post();
				$string .= self::get_html_product(get_the_ID(), $display_type, $current_position++, $grid_element_nb_per_line);
			endwhile;
			$string .= '</ul></div>';

			// --------------------- //
			// Pagination management //
			// --------------------- //
			if($nb_of_product_limit==0) {
				$paginate = paginate_links(array(
					'base' => '#',
					'current' => $page_number,
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
		}
		wp_reset_query(); // important

		return array($have_results, $string);
	}

	/** Reduce the product qty to the qty given in the arguments
	 * @return array
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
		}
	}

	function get_product_data($product_id, $for_cart_storage=false) {
		global $wpdb;

		$query = '
			SELECT P.ID, P.post_title, P.post_name, PM.meta_value AS attribute_set_id
			FROM '.$wpdb->posts.' AS P
				INNER JOIN '.$wpdb->postmeta.' AS PM ON (PM.post_id=P.ID)
			WHERE
				P.ID='.$product_id.' AND
				P.post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" AND
				P.post_status="publish" AND
				PM.meta_key = "_wpshop_product_attribute_set_id"
			LIMIT 1
		';
		$product = $wpdb->get_row($query);

		$product_data = array();
		$product_meta = array();

		if(!empty($product)) {
			$product_data['product_id'] = $product->ID;
			$product_data['post_name'] = $product->post_name;
			$product_data['product_name'] = $product->post_title;
			$product_data['product_meta_attribute_set_id'] = $product->attribute_set_id;

			$data = wpshop_attributes::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $product->ID, get_locale());
			foreach($data as $attribute){
				$data_type = 'attribute_value_'.$attribute->data_type;
				$value = $attribute->$data_type;
				if($attribute->backend_input == 'select' || $attribute->backend_input == 'multiple-select'){
					$value = wpshop_attributes::get_attribute_type_select_option_info($value, 'value');
				}

				// Special traitment regarding attribute_code
				switch($attribute->attribute_code) {
					case 'product_weight':
						$value *= 1000;
					break;
					default:
						$value = !empty($value) ? $value : 0;
					break;
				}
				$product_data[$attribute->attribute_code] = $value;

				if(!$for_cart_storage OR $for_cart_storage && $attribute->is_recordable_in_cart_meta=='yes') {
					$meta = get_post_meta($product->ID, 'attribute_option_'.$attribute->attribute_code, true);
					if(!empty($meta)) {
						$product_meta[$attribute->attribute_code] = $meta;
					}
				}
			}
			$product_data['item_meta'] = !empty($product_meta) ? $product_meta : array();
		}

		return $product_data;
	}

	function duplicate_the_product($pid) {
		global $wpdb;

		// Get the product post info
		$query_posts = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$pid);
		$data_posts = $wpdb->get_row($query_posts,ARRAY_A);
		$data_posts['ID'] = NULL;
		$data_posts['post_date'] = date('Y-m-d H:i:s');
		$data_posts['post_date_gmt'] = date('Y-m-d H:i:s');
		$data_posts['post_modified'] = date('Y-m-d H:i:s');
		$data_posts['post_modified_gmt'] = date('Y-m-d H:i:s');
		$data_posts['guid'] = NULL;

		// Get others features like thumbnails
		$query_posts_more = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_parent='.$pid.' AND post_type="attachment"');
		$data_posts_more = $wpdb->get_results($query_posts_more,ARRAY_A);

		// Postmeta
		$postmeta = get_post_meta($pid,WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
		$related_products = get_post_meta($pid,WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
		// Datetime
		$query_eav_datetime = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME.' WHERE entity_id='.$pid);
		$data_eav_datetime = $wpdb->get_results($query_eav_datetime,ARRAY_A);
		// Decimal
		$query_eav_decimal = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL.' WHERE entity_id='.$pid);
		$data_eav_decimal = $wpdb->get_results($query_eav_decimal,ARRAY_A);
		// Integer
		$query_eav_integer = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' WHERE entity_id='.$pid);
		$data_eav_integer = $wpdb->get_results($query_eav_integer,ARRAY_A);
		// Options
		$query_eav_options = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS.' WHERE entity_id='.$pid);
		$data_eav_options = $wpdb->get_results($query_eav_options,ARRAY_A);
		// Text
		$query_eav_text = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT.' WHERE entity_id='.$pid);
		$data_eav_text = $wpdb->get_results($query_eav_text,ARRAY_A);
		// Varchar
		$query_eav_varchar = $wpdb->prepare('SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR.' WHERE entity_id='.$pid);
		$data_eav_varchar = $wpdb->get_results($query_eav_varchar,ARRAY_A);

		$wpdb->insert($wpdb->prefix.'posts', $data_posts);
		$new_pid = $wpdb->insert_id;

		// Update the post_name to avoid duplicated product name
		$post_name = $data_posts['post_name'].$new_pid;
		$wpdb->update($wpdb->posts, array('post_name'=>$post_name), array('ID'=>$new_pid));

		// Replace the old product id by the new one
		foreach($data_posts_more as $k=>$v) {
			$data_posts_more[$k]['ID'] = NULL;
			$data_posts_more[$k]['post_parent'] = $new_pid;
			$data_posts_more[$k]['post_date'] = date('Y-m-d H:i:s');
			$data_posts_more[$k]['post_date_gmt'] = date('Y-m-d H:i:s');
			$data_posts_more[$k]['post_modified'] = date('Y-m-d H:i:s');
			$data_posts_more[$k]['post_modified_gmt'] = date('Y-m-d H:i:s');
			$wpdb->insert($wpdb->prefix.'posts', $data_posts_more[$k]);
		}

		//update_post_meta($new_pid, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $postmeta);
		//update_post_meta($new_pid, WPSHOP_PRODUCT_RELATED_PRODUCTS, $related_products);
		$query = $wpdb->prepare('SELECT meta_key, meta_value FROM '.$wpdb->postmeta.' WHERE post_id='.$pid);
		$post_meta = $wpdb->get_results($query,ARRAY_A);
		if(!empty($post_meta)) {
			foreach($post_meta as $p) {
				update_post_meta($new_pid, $p['meta_key'], $p['meta_value']);
			}
		}

		// Replace the old product id by the new one
		foreach($data_eav_datetime as $k=>$v) {
			$data_eav_datetime[$k]['value_id'] = NULL;
			$data_eav_datetime[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, $data_eav_datetime[$k]);
		}
		foreach($data_eav_decimal as $k=>$v) {
			$data_eav_decimal[$k]['value_id'] = NULL;
			$data_eav_decimal[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, $data_eav_decimal[$k]);
		}
		foreach($data_eav_integer as $k=>$v) {
			$data_eav_integer[$k]['value_id'] = NULL;
			$data_eav_integer[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, $data_eav_integer[$k]);
		}
		foreach($data_eav_options as $k=>$v) {
			$data_eav_options[$k]['value_id'] = NULL;
			$data_eav_options[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, $data_eav_options[$k]);
		}
		foreach($data_eav_text as $k=>$v) {
			$data_eav_text[$k]['value_id'] = NULL;
			$data_eav_text[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, $data_eav_text[$k]);
		}
		foreach($data_eav_varchar as $k=>$v) {
			$data_eav_varchar[$k]['value_id'] = NULL;
			$data_eav_varchar[$k]['entity_id'] = $new_pid;
			$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, $data_eav_varchar[$k]);
		}
	}

	/**
	 * Add a product into the db. This function is used for the EDI
	 * @param $name Name of the product
	 * @param $description Description of the product
	 * @param $attrs List of the attributes and values of the product
	 * @return boolean
	*/
	function addProduct($name, $description, $attrs=array()) {

		// On r�cup�re l'ID de l'utilisateur si il est connect�
		$user_id = function_exists('is_user_logged_in') && is_user_logged_in() ? get_current_user_id() : 'NaN';

		$product = array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
			'post_title' => $name,
			'post_status' => 'publish',
			'post_excerpt' => $description,
			'post_content' => $description,
			'post_author' => $user_id,
			'comment_status' => 'closed'
		);

		// Nouveau produit
		$product_id = wp_insert_post($product);

		/*	Update the attribute set id for the current product	*/
		$default_id = wpshop_attributes_set::getElement('yes', "'valid'", 'is_default');
		update_post_meta($product_id, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $default_id->id);

		return wpshop_attributes::setAttributesValuesForItem($product_id, $attrs, true);
	}

	/**
	* Retourne une liste de produit
	* @param boolean $formated : formatage du r�sultat oui/non
	* @param string $product_search : recherche demand�e
	* @return mixed
	**/
	function product_list($formated=false, $product_search=null) {
		global $wpdb;
		if(!empty($product_search)) {
			if(is_array($product_search)) {
				$query = '
					SELECT ID, post_title FROM '.$wpdb->prefix.'posts
					WHERE post_type="wpshop_product" AND post_status="publish" AND ID IN ('.implode(",",$product_search).')
				';
			}
			else {
			$query = '
					SELECT ID, post_title FROM '.$wpdb->prefix.'posts
					WHERE post_type="wpshop_product" AND post_status="publish" AND post_title LIKE "%'.$product_search.'%"
				';
			}
		}
		else {
			$query = 'SELECT ID, post_title FROM '.$wpdb->prefix.'posts WHERE post_type="wpshop_product" AND post_status="publish"';
		}
		$data = $wpdb->get_results($query);

		// Si le formatage est demand�
		if($formated) {
			$product_string='';
			foreach($data as $d) {
				$product_string.= '<li><label><input type="checkbox" value="'.$d->ID.'" name="products[]" /> '.$d->post_title.'</label></li>';
			}
		}
		return $formated?$product_string:$data;
	}

	/**
	* Retourne une liste d'attributs pour chaque produit
	* @param boolean $formated : formatage du r�sultat oui/non
	* @param string $product_search : recherche demand�e
	* @return mixed
	**/
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
			AND '.$wpdb->prefix.'posts.post_type="'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" AND '.$wpdb->prefix.'posts.post_status="publish"
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

		// Si le formatage est demand�
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

	/**
	* Retourne une liste de groupe d'attributs
	* @param boolean $formated : formatage du r�sultat oui/non
	* @param string $product_search : recherche demand�e
	* @return mixed
	**/
	function product_list_group_attr($formated=false, $product_search=null) {
		global $wpdb;
		$query = '
			SELECT '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.id, '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.code, '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.name
			FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.'
			WHERE '.WPSHOP_DBT_ATTRIBUTE_GROUP.'.status="valid"
		';
		$data = $wpdb->get_results($query);

		// Si le formatage est demand�
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
	*	Define the content of the product actions
	*/
	function product_actions_meta_box_content(){
		global $currentTabContent,$post;

		echo '<input type="hidden" name="pid" value="'.$post->ID.'" /><a class="button" href="#" id="duplicate_the_product">'.__('Duplicate the product', 'wpshop').'</a>';
	}

	/**
	*	Define the content of the product main information box
	*/
	function main_information_meta_box_content(){
		global $currentTabContent,$post;


		/*	Add the extra fields defined by the default attribute group in the general section	*/
			/*	Get the general attribute set for outputting the result	*/
		if(isset($currentTabContent['generalTabContent']) && is_array($currentTabContent['generalTabContent'])){
			$the_form_general_content = implode('
			', $currentTabContent['generalTabContent']);

			$input_def['id'] = 'product_attribute_set_id';
			$input_def['name'] = 'product_attribute_set_id';
			$input_def['value'] = '';
			$input_def['type'] = 'hidden';
			$input_def['value'] = get_post_meta($post->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
			if(empty($input_def['value'])){
				$attribute_set_id = wpshop_attributes::get_attribute_value_content('product_attribute_set_id', $post->ID, self::currentPageCode);
				if(!empty($attribute_set_id->value)){
					$input_def['value'] = $attribute_set_id->value;
				}
				else{
					$attributeEntitySetList = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode));
					$input_def['value'] = $attributeEntitySetList[0]->id;
				}
			}

			$the_form_general_content .= wpshop_form::check_input_type($input_def, self::currentPageCode);

			echo '
			<div><strong>'.__('Product shortcode').'</strong> - <a href="#" class="show-hide-shortcodes">'.__('Afficher', 'wpshop').'</a>
				<div class="wpshop_product_shortcode_display_container wpshopHide"><br />

					<label>'.__('Product insertion code', 'wpshop').'</label>
					<code>[wpshop_product pid="'.$post->ID.'" type="list"]</code> '.__('or', 'wpshop').' <code>[wpshop_product pid="'.$post->ID.'" type="grid"]</code> '.__('or', 'wpshop').'<br /><br />

					<label>'.__('Product insertion PHP code', 'wpshop').'</label>
					<code>&lt;?php echo do_shortcode(\'[wpshop_product pid="'.$post->ID.'" type="list"]\'); ?></code> '.__('or', 'wpshop').' <code>&lt;?php echo do_shortcode(\'[wpshop_product pid="'.$post->ID.'" type="grid"]\'); ?></code>

				</div>
			</div><br />
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
<ul id="product_picture_list" class="product_attachment_list product_attachment_list_box_picture clear" >' . self::product_attachement_by_type($post->ID, 'image/', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=gallery&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

		echo $product_picture_galery_metabox_content;
	}

	/**
	*	Define the metabox for managing products documents
	*/
	function meta_box_document($post, $metaboxArgs){
		$output = '';

		$output = '
<a href="media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox clear" title="Manage Your Product Document" >' . __('Add documents for the document', 'wpshop' ) . '</a> (Seuls les documents <i>.pdf</i> seront pris en compte)
<div class="alignright reload_box_attachment" ><img src="' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png" alt="' . __('Reload the box', 'wpshop') . '" title="' . __('Reload the box', 'wpshop') . '" class="reload_attachment_box" id="reload_box_document" /></div>
<ul id="product_document_list" class="product_attachment_list product_attachment_list_box_document clear" >' . self::product_attachement_by_type($post->ID, 'application/pdf', 'media-upload.php?post_id=' . $post->ID . '&amp;tab=library&amp;TB_iframe=1&amp;width=640&amp;height=566') . '</ul>';

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
	 * Définition de la metabox permettant de gérer les déclinaisons (variations) d'un type d'élément
	 * 
	 * @param object $post Les informations complètes concernant le post en cours d'édition
	 * @param array $metaboxArgs La liste des paramètres permettant de personnaliser l'affichage de la metabox
	 */
	function meta_box_variations($post, $metaboxArgs) {
		$output = '';

		/* Récupération de la liste des attributs disponible pour la création des variations */
		$attribute_list = wpshop_attributes::getElement('yes', "'valid'", 'is_user_defined', true);
		if (!empty($attribute_list)) {
			$output .= __('Select attribute for new variation creation', 'wpshop');
			$output .= '<ul class="wpshop_list_of_attribute_for_variation" >';
			foreach ($attribute_list as $attribute) {
				if( !in_array($attribute->code, unserialize(WPSHOP_VARIATION_ATTRIBUTE_TO_HIDE)) && in_array($attribute->backend_input, array('select', 'multiple-select')) ){
					$output .= '<li><input type="radio" name="wpshop_attribute_to_use_for_variation" value="' . $attribute->code . '" id="' . $attribute->code . '" />&nbsp;<label for="' . $attribute->code . '" >' . __($attribute->frontend_label, 'wpshop') . '</label></li>';
				}
			}
			$output .= '</ul>';

			/*<input type="button" class="button-secondary alignright product_variation_button product_variation_button_duplicate" id="wpshop_variation_duplicate_<?php echo $variation_id; ?>" value="<?php _e('Duplicate variation', 'wpshop'); ?>" />*/

			/*	Ajout d'un bouton permettant d'ajouter des variations aux produits	*/
			$output .= '<input class="button-secondary alignright" type="button" value="' . __('Add a variation','wpshop') . '" id="wpshop_dialog_new_variation_button" />';
	
			/*	Conteneur pour les différentes variations du produit	*/
			$output .= '<div class="clear wpshop_separator" ></div><div class="clear wpshop_product_variations" >' . self::display_variation_admin( $post->ID ) . '</div>';
		}
		else {
			$output .= sprintf(__('No attribute are defined for being used in variation. You can define this parameter in options tab in attribute edition page by checking "%s" box', 'wpshop'), __('is_user_defined', 'wpshop'));
		}

		echo $output . '<div class="clear" ></div>';
	}
	/**	 
	 * Création d'une déclinaison (variation) pour un produit
	 * 
	 * @param integer $head_product L'identifiant du produit a partir duquel la déclinaison sera créée
	 * @param array $variation_attributes La liste des attributs permettant de créer la variation
	 * @return mixed <number, WP_Error> L'identifiant de la déclinaison si elle a bien été créée, une erreur dans le cas contraire
	 */
	function create_variation ($head_product, $variation_attributes) {
		$variation = array(
			'post_title' => sprintf(__('Product %s variation', 'wpshop'), $head_product),
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_parent' => $head_product,
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION
		);
		$variation_id = wp_insert_post( $variation );

		update_post_meta($variation_id, WPSHOP_PRODUCT_VARIATION_DEF_META_KEY, array_flip($variation_attributes));

		return $variation_id;
	}
	/**
	 * Récupération de la liste des variations pour un produit donné
	 * 
	 * @param integer $head_product
	 * @return object La liste contenant les variations pour le produit sélectionné
	 */
	function get_variation ( $head_product ) {
		$variations = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION,
			'post_parent' 	=> $head_product,
			'orderby' 		=> 'menu_order',
			'order' 		=> 'ASC'
		));

		return $variations;
	}
	/**
	 * Affichage des variations d'un produit dans l'administration
	 * 
	 * @param integer $head_product L'identifiant du produit dont on veut afficher les variations
	 * @return string Le code html permettant l'affichage des variations dans l'interface d'édition du produit
	 */
	function display_variation_admin ( $head_product ) {
		$output = '';

		/*	Récupération de la liste des variations pour le produit en cours d'édition	*/
		$variations = self::get_variation($head_product);

		/*	Affichage de la liste des variations pour le produit en cours d'édition	*/
		if ( !empty($variations) && is_array($variations) ) {
			foreach ( $variations as $variation ) {
				$variation_list = '';
				$variation_id = $variation->ID;
				$variation_def = get_post_meta($variation->ID, WPSHOP_PRODUCT_VARIATION_DEF_META_KEY, true);

				if ( !empty($variation_def) && is_array($variation_def) ) {
					unset($input_def);$input_def=array();
					$input_def['label'] = __('Variation value for : %s', 'wpshop');
					$input_def['type'] = 'select';

					foreach ($variation_def as $attribute_code => $variation_value) {
						$input_def['valueToPut'] = 'index';
						$attribute = wpshop_attributes::getElement($attribute_code, '"valid"', 'code');
						$input_def['name'] = $attribute_code;

						$input_def_possible_value = wpshop_attributes::get_select_output($attribute, array('variation', $head_product));
						$input_def['possible_value'] = $input_def_possible_value['possible_value'];
						$input_def['value'] = !empty($variation_value['value']) ? $variation_value['value'] : null;

						$variation_list .= '<label for="' . $input_def['name'] . '" >' . sprintf($input_def['label'], $attribute->frontend_label) . '</label>' . wpshop_form::check_input_type($input_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '[' . $variation->ID . ']');
					}
					if(!empty($variation_list))$variation_list = ' - ' . $variation_list;
				}

				ob_start();
				include(WPSHOP_TEMPLATES_DIR.'admin/admin_product_variation_display.tpl.php');
				$output .= ob_get_contents();
				ob_end_clean();
			}
			/*	Reset de la liste des résultats pour éviter les comportements indésirables	*/
			wp_reset_query();
		}
		else {
			$output = __('No variation found for this product. Please use button above for create one', 'wpshop');
		}

		return $output;
	}

	/**
	 * Définition de la metabox permettant de gérer la partie statistique concernant l'élément
	 * 
	 * @param object $post Les informations complètes concernant le post en cours d'édition
	 * @param array $metaboxArgs La liste des paramètres permettant de personnaliser l'affichage de la metabox
	 */
	function meta_box_stat_price($post, $metaboxArgs){
		$output = '';

		echo $output;
	}

	/**
	 * Enregistrement des données pour le produit
	 */
	function save_product_custom_informations() {
		global $wpdb;

		if(!empty($_REQUEST[self::currentPageCode . '_attribute']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)){

			/*	Fill the product reference automatically if nothing is sent	*/
			if ( empty($_REQUEST[self::currentPageCode . '_attribute']['varchar']['product_reference']) ) {
				$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts);
				$last_ref = $wpdb->get_var($query);
				$_REQUEST[self::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
			}

			/* Traduction des virgule en point pour la base de donnees	*/
			if ( !empty($_REQUEST[self::currentPageCode . '_attribute']['decimal']) ) {
				foreach($_REQUEST[self::currentPageCode . '_attribute']['decimal'] as $attributeName => $attributeValue){
					/*	Check the product price before saving into database	*/
					if((WPSHOP_PRODUCT_PRICE_PILOT == 'HT') && ($attributeName == WPSHOP_PRODUCT_PRICE_HT)){
						$ht_amount = str_replace(',', '.', $attributeValue);
						$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE id = %d", $_REQUEST[self::currentPageCode . '_attribute']['integer'][WPSHOP_PRODUCT_PRICE_TAX]);
						$tax_rate = 1 + ($wpdb->get_var($query) / 100);


						$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TTC] = $ht_amount * $tax_rate;
						$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TTC] - $ht_amount;
					}
					if((WPSHOP_PRODUCT_PRICE_PILOT == 'TTC') && ($attributeName == WPSHOP_PRODUCT_PRICE_TTC)){
						$ttc_amount = str_replace(',', '.', $attributeValue);
						$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE id = %d", $_REQUEST[self::currentPageCode . '_attribute']['integer'][WPSHOP_PRODUCT_PRICE_TAX]);
						$tax_rate = 1 + ($wpdb->get_var($query) / 100);

						$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_HT] = $ttc_amount / $tax_rate;
						$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $attributeValue - $_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_HT];
					}

					if ( !is_array($attributeValue) ) {
						$_REQUEST[self::currentPageCode . '_attribute']['decimal'][$attributeName] = str_replace(',','.',$_REQUEST[self::currentPageCode . '_attribute']['decimal'][$attributeName]);
					}
				}
			}

			/*	Save the attributes values into wpshop eav database	*/
			wpshop_attributes::saveAttributeForEntity($_REQUEST[self::currentPageCode . '_attribute'], wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $_REQUEST['post_ID'], get_locale());

			/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
			$productMetaDatas = array();
			foreach($_REQUEST[self::currentPageCode . '_attribute'] as $attributeType => $attributeValues){
				foreach($attributeValues as $attributeCode => $attributeValue){
					if ( $attributeCode == 'product_attribute_set_id' ) {
						/*	Update the attribute set id for the current product	*/
						update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $attributeValue);
					}
					$productMetaDatas[$attributeCode] = $attributeValue;
				}
			}
			update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);

		}

		/*	Enregistrement des variations pour le produit en cours d'édition	*/
		if ( !empty($_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION]) ) {
			foreach ( $_REQUEST[WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION] as $variation_id => $variation_definition ) {
				$variation_complete = array();
				foreach ( $variation_definition as $attribute_code => $variation_chosen_value ) {
					$variation_complete[$attribute_code]['value'] = $variation_chosen_value;
					$attribute_infos = wpshop_attributes::getElement($attribute_code, "'valid'", 'code');
					if($attribute_infos->data_type_to_use == 'custom'){
						$variation_complete[$attribute_code]['label'] = wpshop_attributes::get_attribute_type_select_option_info($variation_chosen_value, 'label');
					}
					elseif($attribute_infos->data_type_to_use == 'internal'){
						$variation_complete[$attribute_code]['label'] = get_the_title($variation_chosen_value);
					}
					update_post_meta($variation_id, WPSHOP_PRODUCT_VARIATION_DEF_META_KEY, $variation_complete);
	
					/*	Save the attributes values into wpshop eav database	*/
					wpshop_attributes::saveAttributeForEntity($_REQUEST[self::currentPageCode . '_attribute'], wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $_REQUEST['post_ID'], get_locale());
				}
			}
		}

		/*	Update the related products list*/
		if ( !empty($_REQUEST['related_products_list']) ) {
			$products_id = explode(',', $_REQUEST['related_products_list']);
			$products_id = array_unique($products_id);
			update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS, $products_id);
		}

		flush_rewrite_rules();
	}

	function filter_data_saving($post_data) {
		if ( ($post_data['post_type'] === WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT) && ($post_data['post_status'] == 'publish') ) {
			$post_data['post_status'] = 'draft';

			add_filter('redirect_post_location', array('wpshop_products', 'filter_data_redirection'), 34070);
		}

		return $post_data;
	}
	function filter_data_redirection($location) {
		remove_filter('redirect_post_location', __FUNCTION__, 34070);
		$location = add_query_arg('message', 34070, $location);

		return $location;
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
	function product_complete_sheet_output($initialContent, $product_id){
		global $wp_query, $wpshop_shop_type;
		$content = $attributeContentOutput = '';

		/*	Get the product thumbnail	*/
		if(has_post_thumbnail($product_id)){
			$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
			$productThumbnail = '<a href="'.$thumbnail_url[0].'" id="product_thumbnail" class="MYCLASS28951784" title="Title">'.get_the_post_thumbnail($product_id, 'medium').'</a>';
		}
		else{
			$productThumbnail = '<img src="'.WPSHOP_DEFAULT_PRODUCT_PICTURE.'" alt="product has no image" class="default_picture_thumbnail" />';
		}

		/*	Get attachement file for the current product	*/
		$product_picture_galery = $product_document_galery = '';
		$attachments = get_posts(array('post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => $product_id));
		if(is_array($attachments) && (count($attachments) > 0)){
			$picture_number = $document_number = 0;
			$picture_increment = $document_increment = 1;
			foreach ($attachments as $attachment){
				if(is_int(strpos($attachment->post_mime_type, 'image/'))){
					$product_picture_item_class = (!($picture_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_picture_last' : '';
					/*	Include the product sheet template	*/
					ob_start();
					require(wpshop_display::get_template_file('product_attachment_picture_line.tpl.php'));
					$product_attachment_main_galery = ob_get_contents();
					ob_end_clean();
					$product_picture_galery .= $product_attachment_main_galery;

					$picture_number++;
					$picture_increment++;
				}
				if(is_int(strpos($attachment->post_mime_type, 'application/pdf'))){
					$product_document_item_class = (!($document_increment%WPSHOP_DISPLAY_GALLERY_ELEMENT_NUMBER_PER_LINE)) ? 'wpshop_gallery_document_last' : '';
					/*	Include the product sheet template	*/
					ob_start();
					require(wpshop_display::get_template_file('product_attachment_document_line.tpl.php'));
					$product_attachment_main_galery = ob_get_contents();
					ob_end_clean();
					$product_document_galery .= $product_attachment_main_galery;
					$document_number++;
					$document_increment++;
				}
			}
			if($picture_number > 1){
				$product_gallery_main_title = __('Associated pictures', 'wpshop');
				$gallery_type = 'product_picture';
				$gallery_content = $product_picture_galery;
				/*	Include the product sheet template	*/
				ob_start();
				require(wpshop_display::get_template_file('product_picture_galery.tpl.php'));
				$product_attachment_main_galery = ob_get_contents();
				ob_end_clean();
				$product_picture_galery = $product_attachment_main_galery;
			}
			else{
				$product_picture_galery = '&nbsp;';
			}
			if($document_number > 0){
				$gallery_type = 'product_document';
				$product_gallery_main_title = __('Associated document', 'wpshop');
				$gallery_content = $product_document_galery;
				/*	Include the product sheet template	*/
				unset($product_attachment_main_galery);
				ob_start();
				require(wpshop_display::get_template_file('product_document_library.tpl.php'));
				$product_attachment_main_galery = ob_get_contents();
				ob_end_clean();
				$product_document_galery = $product_attachment_main_galery;
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
					if(is_array($attributeDefinition['value']) OR ((trim($attributeDefinition['value']) != '') && ($attributeDefinition['value'] > '0'))){
						$attribute_unit_list = '';
						if(($attributeDefinition['unit'] != '')){
							$attribute_unit_list = '&nbsp;(' . $attributeDefinition['unit'] . ')';
						}
						$attribute_value = $attributeDefinition['value'];
						if($attributeDefinition['data_type'] == 'datetime'){
							$attribute_value = mysql2date('d/m/Y', $attributeDefinition['value'], true);
						}
						if($attributeDefinition['backend_input'] == 'select'){
							$attribute_value = wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value'], 'label');
						}
						// Manage differently if its an array of values or not
						if($attributeDefinition['backend_input'] == 'multiple-select'){
							$attribute_value = '';
							if(is_array($attributeDefinition['value'])) {
								foreach($attributeDefinition['value'] as $v) {
									$attribute_value .= ', '.wpshop_attributes::get_attribute_type_select_option_info($v, 'label');
								}
							}
							else $attribute_value = ', '.wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value']);
							$attribute_value = substr($attribute_value,2);
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
						ob_start();
						require(wpshop_display::get_template_file('product-attribute-front-display-tabs.tpl.php'));
						$tab_list .= ob_get_contents();
						ob_end_clean();

						ob_start();
						require(wpshop_display::get_template_file('product-attribute-front-display-tabs-content.tpl.php'));
						$content_list .= ob_get_contents();
						ob_end_clean();
				}
			}
			if ($tab_list != '') {
				ob_start();
				require(wpshop_display::get_template_file('product-attribute-front-display-main-container.tpl.php'));
				$attributeContentOutput = ob_get_contents();
				ob_end_clean();
			}

		}

		$productCurrency = wpshop_tools::wpshop_get_currency();
		$product = self::get_product_data($product_id);
		$productPrice = isset( $product[WPSHOP_PRODUCT_PRICE_TTC] ) ? wpshop_tools::price($product[WPSHOP_PRODUCT_PRICE_TTC]).' '.$productCurrency : __('Unknown price','wpshop');

		// Check if there is at less 1 product in stock
		$productStock = wpshop_cart::check_stock($product_id,1);
		$productStock = $productStock===true ? 1 : 0;

		// Add to cart button
		$add_to_cart_button='';
		if (!empty($wpshop_shop_type) && ($wpshop_shop_type == 'sale')) {
			if (!empty($productStock)) {
				ob_start();
				require(wpshop_display::get_template_file('available_product_button.tpl.php'));
				$add_to_cart_button = ob_get_contents();
				ob_end_clean();
			}
			else {
				ob_start();
				require(wpshop_display::get_template_file('not_available_product_button.tpl.php'));
				$add_to_cart_button = ob_get_contents();
				ob_end_clean();
			}
		}
		// Quotation button
		if (!empty($product['quotation_allowed']) && $product['quotation_allowed']=='yes') {
			ob_start();
			require(wpshop_display::get_template_file('quotation_button.tpl.php'));
			$quotation_button = ob_get_contents();
			ob_end_clean();
		}
		else {
			$quotation_button = '';
		}

		/*	Get the different attribute affected to the product	*/
		$product_attributes = array();
		$product_atribute_list = wpshop_attributes::getElementWithAttributeAndValue(wpshop_entities::get_entity_identifier_from_code(self::currentPageCode), $product_id, get_locale());
		if(!empty($product_atribute_list)){
			foreach($product_atribute_list[$product_id] as $attributeSetSectionName => $attributeSetContent){
				foreach($attributeSetContent['attributes'] as $attributeId => $attributeDefinition){
					$attribute_value = $attributeDefinition['value'];
					if($attributeDefinition['data_type'] == 'datetime'){
						$attribute_value = mysql2date('d/m/Y', $attributeDefinition['value'], true);
					}
					if($attributeDefinition['backend_input'] == 'select'){
						$attribute_value = wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value'], 'label');
					}
					// Manage differently if its an array of values or not
					if($attributeDefinition['backend_input'] == 'multiple-select'){
						$attribute_value = '';
						if(is_array($attributeDefinition['value'])) {
							foreach($attributeDefinition['value'] as $v) {
								$attribute_value .= ', '.wpshop_attributes::get_attribute_type_select_option_info($v, 'label');
							}
						}
						else $attribute_value = ', '.wpshop_attributes::get_attribute_type_select_option_info($attributeDefinition['value'], 'label');
						$attribute_value = substr($attribute_value,2);
					}
					$product_attributes[$attributeDefinition['attribute_code']] = $attribute_value;
				}
			}
		}

		/*	Vérification de l'existence de déclinaison pour le produit	*/
		$variations_params = array();
		$variation_output = '';
		$product_variation_list = self::get_variation($product_id);
		if ( !empty($product_variation_list) ) {
			foreach ($product_variation_list as $variation) {
				$variation_def = get_post_meta($variation->ID, WPSHOP_PRODUCT_VARIATION_DEF_META_KEY, true);
				$variations_params[$variation->ID] = $variation_def;
			}

			if ( !empty($variations_params) ) {
				$possible_values = array();
				foreach ($variations_params as $variation_id => $variation) {
					foreach ($variation as $variation_attribute => $variation_attribute_value) {
						if ( empty($possible_values[$variation_attribute]) ) {
							$possible_values[$variation_attribute]['values'][] = __('Choose a value', 'wpshop');
						}
						$attribute_info = wpshop_attributes::getElement($variation_attribute, "'valid'", 'code');
						$possible_values[$variation_attribute]['values'][$variation_id] = $variation_attribute_value['label'];
						$possible_values[$variation_attribute]['label'] = $attribute_info->frontend_label;
					}
				}
				if ( !empty($possible_values) ) {
					foreach ($possible_values as $attributes => $values) {
						$input_def['id'] = WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION . '_' . $attributes;
						$input_def['name'] = $attributes;
						$input_def['value'] = '';
						$input_def['type'] = 'select';
						$input_def['possible_value'] = $values['values'];
						$input_def['valueToPut'] = 'index';
						$input_def['option'] = ' class="wpshop_variation_selector_input" ';
			
						$variation_output .= '<p class="wpshop_variation_selector wpshop_variation_selector_' . $attributes . '" ><label for="" >' . $values['label'] . '</label>' . wpshop_form::check_input_type($input_def, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION) . '</p>';
					}
				}
			}
			$initialContent = $variation_output . $initialContent;
		}
		wp_reset_query();

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
	function product_mini_output($product_id, $category_id, $output_type = 'list', $current_item_position = 1, $grid_element_nb_per_line = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE){
		global $wpshop_shop_type;
		$content = $product_information = '';
		$product_class = '';

		/*	Get the product thumbnail	*/
		if(has_post_thumbnail($product_id)){
			$productThumbnail = get_the_post_thumbnail($product_id, 'thumbnail');
		}
		else $productThumbnail = '<img src="' . WPSHOP_DEFAULT_PRODUCT_PICTURE . '" alt="product has no image" class="default_picture_thumbnail" />';

		/*	Get the product information for output	*/
		$product = get_post($product_id);
		if(!empty($product)) {
			$product_title = $product->post_title;
			// if($category_id==0)
				// $product_link = 'catalog/product/' . $product->post_name;
			// else $product_link = get_term_link((int)$category_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) . '/' . $product->post_name;
			$product_link = get_permalink($product_id);
			$product_more_informations = $product->post_content;
			$product_excerpt = get_the_excerpt();
			if(strpos($product->post_content, '<!--more-->')){
				$post_content = explode('<!--more-->', $product->post_content);
				$product_more_informations = $post_content[0];
			}
		}
		else{
			$productThumbnail = '<img src="' . WPSHOP_PRODUCT_NOT_EXIST . '" alt="product has no image" class="default_picture_thumbnail" />';
			$product_title = '<i>'.__('This product does not exist', 'wpshop').'</i>';
			$product_link = '';
			$product_more_informations = '';
			$product_excerpt = '';
		}

		$product = self::get_product_data($product_id);
		$productPrice = isset($product[WPSHOP_PRODUCT_PRICE_TTC]) ? $product[WPSHOP_PRODUCT_PRICE_TTC] : 0;
		//$productStock = intval($product['product_stock']);

		// Check if there is at less 1 product in stock
		$productStock = wpshop_cart::check_stock($product_id,1);
		$productStock = $productStock===true ? 1 : 0;

		$productCurrency = wpshop_tools::wpshop_get_currency();
		$productCategory = get_the_category($product_id);


		// Add to cart button
		$add_to_cart_button='';
		if (!empty($wpshop_shop_type) && ($wpshop_shop_type == 'sale')) {
			if (!empty($productStock)) {
				ob_start();
				require(wpshop_display::get_template_file('available_product_button.tpl.php'));
				$add_to_cart_button = ob_get_contents();
				ob_end_clean();
			}
			else {
				ob_start();
				require(wpshop_display::get_template_file('not_available_product_button.tpl.php'));
				$add_to_cart_button = ob_get_contents();
				ob_end_clean();
			}
		}
		// Quotation button
		if (!empty($product['quotation_allowed']) && $product['quotation_allowed']=='yes') {
			ob_start();
			require(wpshop_display::get_template_file('quotation_button.tpl.php'));
			$quotation_button = ob_get_contents();
			ob_end_clean();
		}
		else {
			$quotation_button = '';
		}

		$product_declare_new = !empty($product['declare_new']) ? $product['declare_new'] : 'No';
		$product_set_new_from = !empty($product['set_new_from']) ? substr($product['set_new_from'], 0, 10) : null;
		$product_set_new_to = !empty($product['set_new_to']) ? substr($product['set_new_to'], 0, 10) : null;

		$product_featured = !empty($product['highlight_product']) ? $product['highlight_product'] : 'No';
		$product_set_featured_from = !empty($product['highlight_from']) ? substr($product['highlight_from'], 0, 10) : null;
		$product_set_featured_to = !empty($product['highlight_to']) ? substr($product['highlight_to'], 0, 10) : null;

		$current_time = substr(current_time('mysql', 0), 0, 10);

		/** PRODUCT MARK AS NEW */
		$show_new_product = false;
		if(($product_declare_new === 'Yes') &&
			(empty($product_set_new_from) || ($product_set_new_from == '0000-00-00') || ($product_set_new_from >= $current_time)) &&
			(empty($product_set_new_to) || ($product_set_new_to == '0000-00-00') || ($product_set_new_to <= $current_time))){
			$show_new_product = true;
		}
		$product_new = '';
		if($show_new_product){
			$product_class .= ' wpshop_product_is_new_' . $output_type;
			ob_start();
			require(wpshop_display::get_template_file('product-is-new.tpl.php'));
			$product_new = ob_get_contents();
			ob_end_clean();
		}

		/** PRODUCT FEATURED */
		$show_product_featured = false;
		if(($product_featured === 'Yes') &&
			(empty($product_set_featured_from) || ($product_set_featured_from == '0000-00-00') || ($product_set_featured_from >= $current_time)) &&
			(empty($product_set_featured_to) || ($product_set_featured_to == '0000-00-00') || ($product_set_featured_to <= $current_time))){
			$show_product_featured = true;
		}
		$product_featured = '';
		if($show_product_featured){
			$product_class .= ' wpshop_product_featured_' . $output_type;
			ob_start();
			require(wpshop_display::get_template_file('product-is-featured.tpl.php'));
			$product_featured = ob_get_contents();
			ob_end_clean();
		}


		if(!($current_item_position%$grid_element_nb_per_line)){
			$product_class .= ' wpshop_last_product_of_line';
		}

		/*	Make some treatment in case we are in grid mode	*/
		if($output_type == 'grid'){
			/*	Determine the width of a component in a line grid	*/
			$element_width = (100 / $grid_element_nb_per_line);
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

	/**
	*
	*/
	function custom_product_list($selected_product = array()){
		global $wpdb;

		/*	Start the table definition	*/
		$tableId = 'wpshop_product_list';
		$tableTitles = array();
		$tableTitles[] = '';
		$tableTitles[] = __('Id', 'wpshop');
		$tableTitles[] = __('Lastname', 'wpshop');
		$tableTitles[] = __('Firstname', 'wpshop');
		$tableTitles[] = __('Subscription date', 'wpshop');
		$tableTitles[] = __('Billing address', 'wpshop');
		$tableTitles[] = __('Shipping address', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_product_selector_column';
		$tableClasses[] = 'wpshop_product_identifier_column';
		$tableClasses[] = 'wpshop_product_quantity_column';
		$tableClasses[] = 'wpshop_product_sku_column';
		$tableClasses[] = 'wpshop_product_name_column';
		$tableClasses[] = 'wpshop_product_link_column';
		$tableClasses[] = 'wpshop_product_price_column';

		/*	Get post list	*/
		$posts = query_posts(array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT
		));
		if(!empty($posts)){
			$current_line_index = 0;
			foreach($posts as $post){
				$tableRowsId[$current_line_index] = 'product_' . $post->ID;

				$post_info = get_post_meta($post->ID, '_wpshop_product_metadata', true);

				unset($tableRowValue);
				$tableRowValue[] = array('class' => 'wpshop_product_selector_cell', 'value' => '<input type="checkbox" name="wp_list_product[]" value="' . $post->ID . '" class="wpshop_product_cb_dialog" id="wpshop_product_cb_dialog_' . $post->ID . '" />');
				$tableRowValue[] = array('class' => 'wpshop_product_identifier_cell', 'value' => '<label for="wpshop_product_cb_dialog_' . $post->ID . '" >' . WPSHOP_IDENTIFIER_PRODUCT . $post->ID . '</label>');
				$tableRowValue[] = array('class' => 'wpshop_product_quantity_cell', 'value' => '<a href="#" class="order_product_action_button qty_change">-</a><input type="text" name="wpshop_pdt_qty[' . $post->ID  . ']" value="1" class="wpshop_order_product_qty" /><a href="#" class="order_product_action_button qty_change">+</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_sku_cell', 'value' => $post_info['product_reference']);
				$tableRowValue[] = array('class' => 'wpshop_product_name_cell', 'value' => $post->post_title);
				$tableRowValue[] = array('class' => 'wpshop_product_link_cell', 'value' => '<a href="' . $post->guid . '" target="wpshop_product_view_product" target="wpshop_view_product" >' . __('View product', 'wpshop') . '</a><br/>
		<a href="' . admin_url('post.php?post=' . $post->ID  . '&action=edit') . '" target="wpshop_edit_product" >' . __('Edit product', 'wpshop') . '</a>');
				$tableRowValue[] = array('class' => 'wpshop_product_price_cell', 'value' => __('Price ET', 'wpshop') . '&nbsp;:&nbsp;' . $post_info[WPSHOP_PRODUCT_PRICE_HT] . '&nbsp;' . wpshop_tools::wpshop_get_currency() . '<br/>' . __('Price ATI', 'wpshop') . '&nbsp;:&nbsp;' . $post_info[WPSHOP_PRODUCT_PRICE_TTC] . '&nbsp;' . wpshop_tools::wpshop_get_currency());
				$tableRows[] = $tableRowValue;

				$current_line_index++;
			}
			wp_reset_query();
		}
		else{
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
		jQuery("#' . $tableId . '").dataTable({
			"bLengthChange": false,
			"bSort": false,
			"bInfo": false
		});
	});
</script>';
	}


}

?>