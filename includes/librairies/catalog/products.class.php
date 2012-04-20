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
		global $mandatory_register_post_type_support;
		$options = get_option('wpshop_catalog_product_option', array());
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
			'supports' => array_merge($mandatory_register_post_type_support, !empty($options['wpshop_catalog_product_supported_element']) ? $options['wpshop_catalog_product_supported_element'] : array()),
			'public' => true,
			'has_archive' => true,
			'show_in_nav_menus' => true,
			// 'rewrite' => false,//	For information see below
			'taxonomies' => array(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES),
			'menu_icon' => WPSHOP_MEDIAS_URL . "icones/logo.png"
		));

		// add to our plugin init function
		global $wp_rewrite;
		/*	Slug url is set into option	*/
		$gallery_structure = (!empty($options['wpshop_catalog_product_slug']) ? $options['wpshop_catalog_product_slug'] : 'catalog') . '/%' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '%/%wpshop_product%';
		$wp_rewrite->add_rewrite_tag('%wpshop_product%', '([^/]+)', "wpshop_product=");
		$wp_rewrite->add_permastruct('wpshop_product', $gallery_structure, false);
	}
	
	/** Set the colums for the custom page
	 * @return array
	*/
	function product_edit_columns($columns){
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
	
	/** Content by colums for the custom page
	 * @return array
	*/
	function product_custom_columns($column){
		global $post;
		
		
		$product = self::get_product_data($post->ID);
		switch ($column) {
			case "product_price_ttc":
				if($product['product_price_ttc'])
					echo number_format($product['product_price_ttc'],2,'.', ' ').' EUR';
				else echo '<strong>-</strong>';
			break;
			
			case "product_stock":
				if($product['product_stock'])
					echo (int)$product['product_stock'].' '.__('unit(s)','wpshop');
				else echo '<strong>-</strong>';
			break;
			
			case "product_actions":
				$buttons = '<p>';
				// Voir la commande
				$buttons .= '<a class="button" href="'.admin_url('post.php?post='.$post->ID.'&action=edit').'">'.__('Edit', 'wpshop').'</a>';
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

		add_meta_box('wpshop_product_main_infos', __('Main information', 'wpshop'), array('wpshop_products', 'main_information_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high');
		add_meta_box('wpshop_related_products', __('Related products', 'wpshop'), array('wpshop_products', 'related_products_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'high');
		add_meta_box('wpshop_product_picture_management', __('Picture management', 'wpshop'), array('wpshop_products', 'meta_box_picture'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
		add_meta_box('wpshop_product_document_management', __('Document management', 'wpshop'), array('wpshop_products', 'meta_box_document'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'normal', 'default');
		// Actions
		add_meta_box('wpshop_product_actions', __('Actions', 'wpshop'), array('wpshop_products', 'product_actions_meta_box_content'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'side', 'default');

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
	
	/**
	*	Define the content of the product main information box
	*/
	function related_products_meta_box_content(){
		global $currentTabContent,$post;
		
		if(!empty($post->ID)) {
			$related_products_id = get_post_meta($post->ID, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
			if(!empty($related_products_id))
				$related_products_data = self::product_list($formated=false, $related_products_id);
		}
		
		echo '
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
			echo '<script type="text/javascript">jQuery(document).ready(function() {';
			foreach($related_products_data as $p) {
				echo 'jQuery("#demo-input-wpshop-theme").tokenInput("add", {id: '.$p->ID.', name: "'.$p->post_title.'"});';
			}
			echo '});</script>';
		}
	}


	/**
	* Traduit le shortcode et affiche les produits en relation demandé
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
		global $wpdb;
		$string = '';

		$product_id = !empty($atts['pid']) ? $atts['pid'] : get_the_ID();
		$display_mode = !empty($atts['display_mode']) && in_array($atts['display_mode'],array('list','grid')) ? $atts['display_mode'] : 'grid';
		$grid_element_nb_per_line = !empty($atts['grid_element_nb_per_line']) ? $atts['grid_element_nb_per_line'] : WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE;
		
		$pids = get_post_meta($product_id, WPSHOP_PRODUCT_RELATED_PRODUCTS, true);
		include_once(wpshop_display::get_template_file('product_related.tpl.php'));
		
		return $string;
	}
	
	function get_sorting_criteria() {
		global $wpdb;
		$data = array(array('code' => 'title', 'frontend_label' => __('Product name', 'wpshop')), array('code' => 'date', 'frontend_label' => __('Date added', 'wpshop')), array('code' => 'modified', 'frontend_label' => __('Date modified', 'wpshop')));
		$query = $wpdb->prepare('SELECT code, frontend_label FROM '.WPSHOP_DBT_ATTRIBUTE.' WHERE is_used_for_sort_by="yes"');
		$results = $wpdb->get_results($query, ARRAY_A);
		if(!empty($results))$data = array_merge($data, $results);
		return $data;
	}
	
	/**
	* Traduit le shortcode et affiche les produits demandé
	*
	* @param array $atts {
	*	limit : limite de résultats de la requete
	*	order : paramètre de tri
	*	sorting : sens du tri (asc, desc)
	*	type : type d'affichage (grid, list), seulement pour display=normal
	*	display : taille d'affichage, normal (gd format avec images) ou mini (petit format sans image)
	* }
	*
	* @return string
	*
	**/
	function wpshop_products_func($atts){
		global $wpdb, $wp_query;

		$have_results = false;
		$type = (empty($atts['type']) OR !in_array($atts['type'], array('grid','list'))) ? WPSHOP_DISPLAY_LIST_TYPE : $atts['type'];
		$pagination = isset($atts['pagination']) ? intval($atts['pagination']) : WPSHOP_ELEMENT_NB_PER_PAGE;
		$cid = !empty($atts['cid']) ? $atts['cid'] : 0;
		$pid = !empty($atts['pid']) ? $atts['pid'] : 0;
		$order_by_sorting = $atts['sorting']=='DESC'?'DESC':'ASC';
		$limit = isset($atts['limit']) ? intval($atts['limit']) : 0;
		
		$sorting_criteria = self::get_sorting_criteria();
		// If the order criteria isn't in the $sorting_criteria list we set it to null
		$bool = false;
		foreach($sorting_criteria as $sc) { if($atts['order'] == $sc['code']) $bool = true; }
		if(!$bool) $atts['order'] = null;
		
		// Display products which have att_name equal to att_value
		if(!empty($atts['att_name']) && !empty($atts['att_value'])) {
			
			$query = "SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code=%s";
			$data = (array)$wpdb->get_row($wpdb->prepare($query, $atts['att_name']));
		 
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
						if(in_array($data['frontend_input'], array('select','radio','checkbox'))) {
					 
								$query = $wpdb->prepare("
										SELECT ".$table_name.".entity_id FROM ".$table_name."
										LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
										LEFT JOIN ".WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS." AS ATT_OPT ON ".$table_name.".value = ATT_OPT.id
										WHERE ATT.code=%s AND ATT_OPT.value=%s", $atts['att_name'], $atts['att_value'] // force useless zero like 48.58000
								);
								$products = $wpdb->get_results($query);
							 
						}
						else {
					 
								$query = $wpdb->prepare("
										SELECT ".$table_name.".entity_id FROM ".$table_name."
										INNER JOIN ".WPSHOP_DBT_ATTRIBUTE." AS ATT ON ATT.id = ".$table_name.".attribute_id
										WHERE ATT.code=%s AND ".$table_name.".value=%s", $atts['att_name'], sprintf('%.5f', $atts['att_value']) // force useless zero like 48.58000
								);
								$products = $wpdb->get_results($query);
							 
						}
				} else return __('Incorrect shortcode','wpshop');
			} else return __('Incorrect shortcode','wpshop');
		 
			// Foreach on the found products
			if(!empty($products)) {
				$have_results = true;
				$current_position = 1;
				$string .= '<ul class="products_listing '. $type . '_' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE.' '. $type .'_mode clearfix" >';
				foreach($products as $p) {
					$string .= self::get_html_product($p->entity_id, $type, $current_position);
					$current_position++;
				}
				$string .= '</ul>';
			}
			else $string = __('No matches', 'wpshop');
		}
		else { // page par défaut
			$data = self::wpshop_get_product_by_criteria($atts['order'], $cid, $pid, $type, $order_by_sorting, 1, $pagination, $limit);
			if($data[0]) {
				$have_results = true;
				$string = $data[1];
			}
		}
		
		// if there are result to display
		if($have_results) {
		
			$sorting = '';
			if(empty($atts['sorting']) || ($atts['sorting'] != 'no')){
				ob_start();
				require(wpshop_display::get_template_file('product_listing_sorting.tpl.php'));
				$sorting = ob_get_contents();
				ob_end_clean();
			}
			
			$string = $sorting.'<div id="wpshop_product_container">'.$string.'</div>';
			
		}
		else {
			$string = __('There is nothing to output here', 'wpshop');
		}

		return do_shortcode($string);
	}
	
	/**
	
	*/
	function get_html_product($post_id, $display_type, $current_position) {
		$cats = get_the_terms($post_id, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
		$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
		return self::product_mini_output($post_id, $cat_id, $display_type, $current_position);
	}
	
	/**
	
	*/
	function wpshop_get_product_by_criteria($criteria=null, $cid=0, $pid=0, $display_type, $order='ASC', $page_number, $products_per_page=0, $nb_of_product_limit=0){
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
		
		if(!empty($pid)) {
			$pid = explode(',', $pid);
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
			$string .= '<ul class="products_listing '. $display_type . '_' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE.' '. $display_type .'_mode clearfix" >';
			while ($custom_query->have_posts()) : $custom_query->the_post(); 
				$string .= self::get_html_product(get_the_ID(), $display_type, $current_position++);
			endwhile;
			$string .= '</ul>';
				
			// --------------------- //
			// Pagination management //
			// --------------------- //
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
					WHERE 
						wp_wpshop__attribute_value_decimal.entity_id='.$product_id.' AND wp_wpshop__attribute.code="product_stock"
					LIMIT 1
				';
				$data = $wpdb->get_results($query);
				$value_id = $data[0]->value_id;
				// On met à jour le stock dans la base
				//$wpdb->query('UPDATE wp_wpshop__attribute_value_decimal SET wp_wpshop__attribute_value_decimal.value = '.wpshop_tools::wpshop_clean($newQty).' WHERE wp_wpshop__attribute_value_decimal.value_id='.$value_id);
				
				$update = $wpdb->update('wp_wpshop__attribute_value_decimal', array(
					'value' => wpshop_tools::wpshop_clean($newQty)
				), array(
					'value_id' => $$value_id
				));
			}
		}
	}
	
	/** Get the product data
	 * @return array or false
	*/
	function get_product_data($product_id) {
		global $wpdb;
		
		$query = '
			SELECT wp_posts.post_title, wp_posts.post_name FROM wp_posts
			WHERE 
				wp_posts.ID='.$product_id.' AND 
				wp_posts.post_type="wpshop_product" AND 
				wp_posts.post_status="publish"
			LIMIT 1
		';
		$products = $wpdb->get_results($query);

		if(!empty($products)) {
		
			$query = $wpdb->prepare("
			SELECT
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = '" . WPSHOP_PRODUCT_PRICE_HT . "') AS product_price_ht,
					
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = '" . WPSHOP_PRODUCT_PRICE_TTC . "') AS product_price_ttc,
					
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = 'product_stock') AS product_stock,
				
				(SELECT ATT_OPT.value FROM ".WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS." AS ATT_OPT WHERE id = (
					SELECT ATT_INT.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATT_INT
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_INT.attribute_id)
					WHERE ATT_INT.entity_id = %d AND ATT.code = '" . WPSHOP_PRODUCT_PRICE_TAX . "')) AS product_tax_rate,
					
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = '" . WPSHOP_PRODUCT_PRICE_TAX_AMOUNT . "') AS product_tax_amount,
				
				(SELECT ATT_VAR.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " AS ATT_VAR
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_VAR.attribute_id)
				WHERE ATT_VAR.entity_id = %d AND ATT.code = 'product_reference') AS product_reference,
				
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = 'cost_of_postage') AS product_shipping_cost,
				
				(SELECT ATT_OPT.value FROM ".WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS." AS ATT_OPT WHERE id = (
					SELECT ATT_INT.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATT_INT
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_INT.attribute_id)
					WHERE ATT_INT.entity_id = %d AND ATT.code = 'declare_new')) AS product_declare_new,
				
				(SELECT ATT_DATETIME.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATT_DATETIME
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DATETIME.attribute_id)
				WHERE ATT_DATETIME.entity_id = %d AND ATT.code = 'set_new_from') AS product_set_new_from,
				
				(SELECT ATT_DATETIME.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATT_DATETIME
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DATETIME.attribute_id)
				WHERE ATT_DATETIME.entity_id = %d AND ATT.code = 'set_new_to') AS product_set_new_to,
				
				(SELECT ATT_DEC.value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATT_DEC
					INNER JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATT ON (ATT.id = ATT_DEC.attribute_id)
				WHERE ATT_DEC.entity_id = %d AND ATT.code = 'product_weight') AS product_weight
			", $product_id, $product_id, $product_id, $product_id, $product_id, $product_id, $product_id, $product_id, $product_id, $product_id, $product_id);

			$data = $wpdb->get_results($query);

			return array(
				'post_name'=> $products[0]->post_name,
				'product_reference' => !empty($data[0]->product_reference) ? $data[0]->product_reference : 0,
				'product_name' => $products[0]->post_title,
				'product_price_ht' => !empty($data[0]->product_price_ht) ? $data[0]->product_price_ht : 0,
				'product_price_ttc' => !empty($data[0]->product_price_ttc) ? $data[0]->product_price_ttc : 0,
				'product_tax_rate' => !empty($data[0]->product_tax_rate) ? $data[0]->product_tax_rate : 0,
				'product_tax_amount' => !empty($data[0]->product_tax_amount) ? $data[0]->product_tax_amount : 0,
				'product_stock' => !empty($data[0]->product_stock) ? $data[0]->product_stock : 0,
				'product_shipping_cost' => !empty($data[0]->product_shipping_cost) ? $data[0]->product_shipping_cost : 0,
				'product_declare_new' => !empty($data[0]->product_declare_new) ? $data[0]->product_declare_new : 0,
				'product_set_new_from' => !empty($data[0]->product_set_new_from) ? $data[0]->product_set_new_from : 0,
				'product_set_new_to' => !empty($data[0]->product_set_new_to) ? $data[0]->product_set_new_to : 0,
				'product_weight' => !empty($data[0]->product_weight) ? $data[0]->product_weight : 0
			);
		}
		else return false;
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
		
		update_post_meta($new_pid, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $postmeta);
		update_post_meta($new_pid, WPSHOP_PRODUCT_RELATED_PRODUCTS, $related_products);
		
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
	* Retourne une liste de produit
	* @param boolean $formated : formatage du résultat oui/non
	* @param string $product_search : recherche demandée
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
		
		// Si le formatage est demandé
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
	* @param boolean $formated : formatage du résultat oui/non
	* @param string $product_search : recherche demandée
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
	
	/**
	* Retourne une liste de groupe d'attributs
	* @param boolean $formated : formatage du résultat oui/non
	* @param string $product_search : recherche demandée
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

			echo '
			<div><strong>'.__('Product shortcode').'</strong> - <a href="#" class="show-hide-shortcodes">Afficher</a>
				<div class="shortcodes_container wpshopHide"><br />
				
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
<a href="media-upload.php?post_id=' . $post->ID . '&amp;TB_iframe=1&amp;width=640&amp;height=566" class="thickbox clear" title="Manage Your Product Document" >' . __('Add documents for the document', 'wpshop' ) . '</a> (Seuls les documents <i>.pdf</i> seront pris en compte)
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
	function save_product_custom_informations(){
		global $wpdb;
		if(isset($_REQUEST[self::currentPageCode . '_attribute']) && (count($_REQUEST[self::currentPageCode . '_attribute']) > 0)){

			/*	Fill the product reference automatically if nothing is sent	*/
			if(trim($_REQUEST[self::currentPageCode . '_attribute']['varchar']['product_reference']) == ''){
				$query = $wpdb->prepare("SELECT MAX(ID) AS PDCT_ID FROM " . $wpdb->posts);
				$last_ref = $wpdb->get_var($query);
				$_REQUEST[self::currentPageCode . '_attribute']['varchar']['product_reference'] = WPSHOP_PRODUCT_REFERENCE_PREFIX . str_repeat(0, WPSHOP_PRODUCT_REFERENCE_PREFIX_NB_FILL) . $last_ref;
			}

			// Traduction des virgule en point pour la base de données!
			foreach($_REQUEST[self::currentPageCode . '_attribute']['decimal'] as $attributeName => $attributeValue){
				/*	Check the product price before saving into database	*/
				if((WPSHOP_PRODUCT_PRICE_PILOT == 'HT') && ($attributeName == WPSHOP_PRODUCT_PRICE_HT)){
					$ht_amount = str_replace(',', '.', $attributeValue);
					$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS . " WHERE id = %d", $_REQUEST[self::currentPageCode . '_attribute']['integer'][WPSHOP_PRODUCT_PRICE_TAX]);
					$tax_rate = 1 + ($wpdb->get_var($query) / 100);

					$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TTC] = $ht_amount * $tax_rate;
					$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TTC] - $ht_amount;
				}
				if((WPSHOP_PRODUCT_PRICE_PILOT == 'TTC') && ($attributeName == WPSHOP_PRODUCT_PRICE_TTC)){
					$ttc_amount = str_replace(',', '.', $attributeValue);
					$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS . " WHERE id = %d", $_REQUEST[self::currentPageCode . '_attribute']['integer'][WPSHOP_PRODUCT_PRICE_TAX]);
					$tax_rate = 1 + ($wpdb->get_var($query) / 100);

					$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_HT] = $ttc_amount / $tax_rate;
					$_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_TAX_AMOUNT] = $attributeValue - $_REQUEST[self::currentPageCode . '_attribute']['decimal'][WPSHOP_PRODUCT_PRICE_HT];
				}

				if(!is_array($attributeValue)){
					$_REQUEST[self::currentPageCode . '_attribute']['decimal'][$attributeName] = str_replace(',','.',$_REQUEST[self::currentPageCode . '_attribute']['decimal'][$attributeName]);
				}
			}

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
		
		/*	Update the related products list*/
		if(isset($_REQUEST['related_products_list'])) {
			$products_id = explode(',', $_REQUEST['related_products_list']);
			$products_id = array_unique($products_id);
			update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_RELATED_PRODUCTS, $products_id);
		}

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
		global $wp_query;
		$content = $attributeContentOutput = '';

		/*	Get the product thumbnail	*/
		if(has_post_thumbnail($product_id)){
			$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
			$productThumbnail = '<a href="' . $thumbnail_url[0] . '" id="product_thumbnail" >' . get_the_post_thumbnail($product_id, 'medium') . '</a>';
		}
		else{
			$productThumbnail = '<img src="' . WPSHOP_DEFAULT_PRODUCT_PICTURE . '" alt="product has no image" class="default_picture_thumbnail" />';
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
			if($picture_number > 0){
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
								<ul>
									'.$attributeSetContent['output'].'
								</ul>
							</div>
						';
				}
			}
			if($tab_list != ''){
			$attributeContentOutput = '
				<div id="wpshop_product_feature">
					<ul>' . $tab_list . '</ul>
					' . $content_list . '
				</div>';
			}

		}
		
		$product = self::get_product_data($product_id);
		$productPrice = $product['product_price_ttc'];
		$productStock = intval($product['product_stock']);
		$productCurrency = wpshop_tools::wpshop_get_currency();

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
			$product_excerpt = $product->post_excerpt;
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
		$productPrice = $product['product_price_ttc'];
		$productStock = intval($product['product_stock']);
		$productCurrency = wpshop_tools::wpshop_get_currency();
		$productCategory = get_the_category($product_id);
		$product_declare_new = $product['product_declare_new'];
		$product_set_new_from = substr($product['product_set_new_from'], 0, 10);
		$product_set_new_to = substr($product['product_set_new_to'], 0, 10);

		$current_time = substr(current_time('mysql', 0), 0, 10);
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
		}
		else{
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