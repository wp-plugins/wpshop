<?php
/**
* Plugin installation file.
* 
*	This file contains the different methods called when plugin is actived and removed
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	Class defining the different method used when plugin is activated
* @package wpshop
* @subpackage librairies
*/
class wpshop_install
{

	function install() {
		global $wpdb;
		/* Vérification que toute les données nécessaires sont présentes dans la base de données */
		$required_data = get_option('wpshop_required_data_recorded', 0);
		if(empty($required_data)) {
			$options_required = array('wpshop_shop_default_currency','wpshop_billing_number_figures','wpshop_emails','wpshop_paymentMethod','wpshop_company_info','wpshop_paymentAddress','wpshop_paypalEmail','wpshop_paypalMode');
			$options = $wpdb->get_results('SELECT option_name FROM '.$wpdb->prefix.'options WHERE option_name LIKE "wpshop_%"', ARRAY_A);
			$options_recorded=array();
			$bool=true;
			foreach($options as $o) {
				$options_recorded[] = $o['option_name'];
			}
			foreach($options_required as $o) {
				if(!in_array($o,$options_recorded)) $bool=false;
			}
			if($bool) {
				update_option('wpshop_required_data_recorded', 1);
				$required_data=1;
			}
		}
		$current_db_version = get_option('wpshop_db_options', 0);
		// Si tout est OK on installe
		if($required_data && (empty($current_db_version) OR $current_db_version['db_version']==0)) {
			$options = array();
			$options['useSpecialPermalink']=true;
			$options['exampleProduct']=true;
			self::install_wpshop($options);
			wpshop_tools::wpshop_safe_redirect('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
		}
	}

	/**
	*	Function called when user save the option page on first plugin loading
	*
	*	@param array $options It contains the different informations sent by the user when click on save options button
	*/
	function install_wpshop($options){
		if(self::create_options($options)) {
			// Ajout de produits par default à titre d'exemple
			if($options['exampleProduct']) {
			
				$base_array = array(
					'post_author' => 1,
					'post_date' => date('Y-m-d H:i:s'), 'post_date_gmt' => date('Y-m-d H:i:s'),
					'post_password' => '',
					'post_modified' => date('Y-m-d H:i:s'), 'post_modified_gmt' => date('Y-m-d H:i:s'),
					'comment_count' => 0
				);
				
				// Produits à ajouter à la BDD
				$default_products = array(
					array('product_name' => 'Macbook Pro', 'product_thumbnail' => 'macbook_pro.jpg', 'product_metadata' => array('product_price' => '', 'product_stock' => '')),
					array('product_name' => 'Macbook Air', 'product_thumbnail' => 'macbook_air.jpg', 'product_metadata' => array('product_price' => '', 'product_stock' => '')),
					array('product_name' => 'iPod Touch', 'product_thumbnail' => 'ipod_touch.jpg', 'product_metadata' => array('product_price' => '', 'product_stock' => ''))
				);
				
				// Boucle sur les produits
				foreach($default_products as $p):
				
					// Enregistrement du produit dans la base de données
					$product_id = wp_insert_post(array_merge(array(
						'post_content' => 'Description du '.$p['product_name'],
						'post_title' => $p['product_name'],
						'post_status' => 'publish',
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_name' => sanitize_title($p['product_name']),
						'post_parent' => 0,
						'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT
					), $base_array));
					
					$filepath = plugins_url().'/'.WPSHOP_PLUGIN_DIR.'/medias/images/default_products/'.$p['product_thumbnail'];
					$upload_dir = wp_upload_dir();
					$filename = uniqid().'_'.$p['product_thumbnail'];
					$destination = date('Y').'/'.date('m').'/'.$filename;
					
					// Copie de l'image du produit
					copy($filepath, '../wp-content/uploads/'.$destination);
					
					// Ajout de l'image à la une du produit d'id=$product_id
					wp_insert_post(array_merge(array(
						'post_title' => sanitize_title($p['product_name']).'_thumbnail',
						'post_status' => 'inherit',
						'comment_status' => 'open',
						'ping_status' => 'open',
						'post_name' => sanitize_title($p['product_name']).'_thumbnail',
						'post_parent' => $product_id,
						'guid' => $upload_dir['baseurl'].'/'.$destination,
						'post_type' => 'attachment',
						'post_mime_type' => 'image/jpeg'
					), $base_array));
					
					update_post_meta($product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $p['product_metadata']);
				
				endforeach; // fin boucle produits
			}
			
			// Insertion dans la base de données des pages par défaut
			self::wpshop_insert_default_pages();
		}

		/*	Add the default database content (Attributes) even if the option are not saved successfully	*/
		self::update_wpshop();
	}

	function wpshop_insert_default_pages(){
		global $wpdb,$wp_rewrite;
		
		/*	if we will create any new pages we need to flush page cache */
		$page_creation = false;
		
		/* Default data array for add page */
		$default_add_post_array = array(
			'post_type' 	=>	'page',
			'comment_status'=>	'closed',
			'ping_status' 	=>	'closed',
			'post_status' 	=>	'publish',
			'post_author' 	=>	1,
			'menu_order'	=>	0
		);
		
		/*	Check if pages exists. If page does not exist so we create the page	*/
		
		/* CATALOG */
		$query = $wpdb->prepare("SELECT ID FROM ". $wpdb->posts . " WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_products%', 'revision');
		$product_page = $wpdb->get_var($query);
		if(empty($product_page))
		{
			/*	Get product option in order to create front product page with the goode name	*/
			$product_options = get_option(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

			/*	Create the default page for product in front	*/
			$products_page_id = wp_insert_post(array_merge(array(
				 'post_title' 	=>	__('Shop', 'wpshop'),
				 'post_name'		=>	$product_options['product_slug'],
				 'post_content' 	=>	'[wpshop_products]'
			),$default_add_post_array));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_product_page_id', $products_page_id);
			
			$page_creation = true;
		}
		
		/* BASKET to update to CART */
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_basket]%', 'revision');
		$cart_page_id = $wpdb->get_var($query);
		
		if(!empty($cart_page_id)) 
		{
			//$query = $wpdb->query("UPDATE ".$wpdb->posts." SET post_content = '[wpshop_cart]' WHERE ID=".$cart_page_id."");
			$query = $wpdb->update($wpdb->posts, array(
				'post_content' => '[wpshop_cart]'
			), array(
				'ID' => $cart_page_id
			));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_cart_page_id', $cart_page_id);
			$page_creation = true;
		}
		else {
			/* CART */
			$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_cart]%', 'revision');
			$cart_page = $wpdb->get_var($query);
			if(empty($cart_page))
			{
				/*	Create the default page for product in front	*/
				$cart_page_id = wp_insert_post(array_merge(array(
					 'post_title' 	=>	__('Cart', 'wpshop'),
					 'post_name'	=>	'cart',
					 'post_content' =>	'[wpshop_cart]'
				),$default_add_post_array));
				
				/* On enregistre l'ID de la page dans les options */
				add_option('wpshop_cart_page_id', $cart_page_id);
				$page_creation = true;
			}
		}
		
		/*	CHECKOUT	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_checkout]%', 'revision');
		$checkout_page = $wpdb->get_var($query);
		if(empty($checkout_page))
		{
			/*	Create the default page for product in front	*/
			$checkout_page_id = wp_insert_post(array_merge(array(
				 'post_title' 	=>	__('Checkout', 'wpshop'),
				 'post_name'	=>	'checkout',
				 'post_content' =>	'[wpshop_checkout]'
			),$default_add_post_array));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_checkout_page_id', $checkout_page_id);
			$page_creation = true;
		}
		
		/*	MY ACCOUNT	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_myaccount]%', 'revision');
		$myaccount_page = $wpdb->get_var($query);
		if(empty($myaccount_page))
		{
			/*	Create the default page for product in front	*/
			$myaccount_page_id = wp_insert_post(array_merge(array(
				 'post_title' 	=>	__('My account', 'wpshop'),
				 'post_name'	=>	'myaccount',
				 'post_content' =>	'[wpshop_myaccount]'
			),$default_add_post_array));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_myaccount_page_id', $myaccount_page_id);
			$page_creation = true;
		}
		
		/*	SIGNUP	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_signup]%', 'revision');
		$signup_page = $wpdb->get_var($query);
		if(empty($signup_page))
		{
			/*	Create the default page for product in front	*/
			$signup_page_id = wp_insert_post(array_merge(array(
				 'post_title' 	=>	__('Signup', 'wpshop'),
				 'post_name'	=>	'signup',
				 'post_content' =>	'[wpshop_signup]'
			),$default_add_post_array));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_signup_page_id', $signup_page_id);
			$page_creation = true;
		}
		
		/*	PAYMENTS RETURN	*/
		$query = $wpdb->prepare("SELECT ID FROM ".$wpdb->posts." WHERE post_content LIKE %s	AND post_type != %s", '%[wpshop_payment_result]%', 'revision');
		$payment_return_page = $wpdb->get_var($query);
		if(empty($payment_return_page))
		{
			/*	Create the default page for product in front	*/
			$payment_return_page_id = wp_insert_post(array_merge(array(
				 'post_title' 	=>	__('Payment return', 'wpshop'),
				 'post_name'	=>	'return',
				 'post_content' =>	'[wpshop_payment_result]'
			),$default_add_post_array));
			
			/* On enregistre l'ID de la page dans les options */
			add_option('wpshop_payment_return_page_id', $payment_return_page_id);
			$page_creation = true;
		}
		
		wp_cache_flush();
		
		/* If new page => empty cache */
		if($page_creation) {
			wp_cache_delete('all_page_ids', 'pages');
			$wp_rewrite->flush_rules();
		}
	}

	/**
	*	Add the different options into wordpress for our plugin
	*	@see install_wpshop()
	*/
	function create_options(){
	
		add_option('wpshop_db_options', array('db_version' => 0));
		add_option(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array('product_slug' => 'catalog'));
		add_option('wpshop_product_categories', array('product_categories_slug' => 'category'));
		add_option('wpshop_display_option', array('wpshop_display_list_type' => 'grid', 'wpshop_display_grid_element_number' => '3', 'wpshop_display_cat_sheet_output' => array('category_description', 'category_subcategory', 'category_subproduct')));
		if($options['useSpecialPermalink']) {
			update_option('permalink_structure', '/%postname%');
		}
		
		return true;
	}

	/**
	*	Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
	*/
	function update_wpshop_dev(){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		self::execute_operation_on_db_for_update('dev');
	}
	/**
	*	Method called when plugin is loaded for database update. This method allows to update the database structure, insert default content.
	*/
	function update_wpshop(){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$current_db_version = get_option('wpshop_db_options', 0);
		$current_db_version = $current_db_version['db_version'];

		$current_def_max_version = max(array_keys($wpshop_update_way));
		$new_version = $current_def_max_version + 1;
		$version_nb_delta = $current_def_max_version - $current_db_version;

		/*	Check if there are modification to do	*/
		if($current_def_max_version >= $current_db_version){
			/*	Check the lowest version of db to execute	*/
			$lowest_version_to_execute = $current_def_max_version - $version_nb_delta;

			for($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++){
				$do_changes = self::execute_operation_on_db_for_update($i);
			}
		}

		/*	Update the db version option value	*/
		// $do_changes = false;
		if($do_changes){
			$wpshop_db_options = array();
			$wpshop_db_options['db_version'] = $new_version;
			update_option('wpshop_db_options', $wpshop_db_options);
		}
	}
	/**
	*
	*/
	function execute_operation_on_db_for_update($i){
		global $wpdb, $wpshop_db_table, $wpshop_db_table_list, $wpshop_update_way, $wpshop_db_content_add, $wpshop_db_content_update, $wpshop_db_options_add, $wpshop_eav_content, $wpshop_eav_content_update, $wpshop_db_options_update, $wpshop_db_request;
		$do_changes = false;

		/*	Check if there are modification to do	*/
		if(isset($wpshop_update_way[$i])){
			/*	Check if there are modification to make on table	*/
			if(isset($wpshop_db_table_list[$i])){
				foreach($wpshop_db_table_list[$i] as $table_name){
					dbDelta($wpshop_db_table[$table_name]);
				}
				$do_changes = true;
			}

			/********************/
			/*		Insert data		*/
			/********************/
			/*	Options content	*/
			if(is_array($wpshop_db_options_add) && is_array($wpshop_db_options_add[$i]) && (count($wpshop_db_options_add[$i]) > 0)){
				foreach($wpshop_db_options_add[$i] as $option_name => $option_content){
					add_option($option_name, $option_content, '', 'yes');
				}
				$do_changes = true;
			}
			if(is_array($wpshop_db_options_update) && is_array($wpshop_db_options_update[$i]) && (count($wpshop_db_options_update[$i]) > 0)){
				foreach($wpshop_db_options_update[$i] as $option_name => $option_content){
					$option_current_content = get_option($option_name);
					foreach($option_content as $option_key => $option_value){
						$option_current_content[$option_key] = $option_value;
					}
					update_option($option_name, $option_current_content);
				}
				$do_changes = true;
			}

			/*	Eav content	*/
			if(is_array($wpshop_eav_content) && is_array($wpshop_eav_content[$i]) && (count($wpshop_eav_content[$i]) > 0)){
				/*	Create entities if entites are set to be created for the current version	*/
				if(is_array($wpshop_eav_content[$i]['entities']) && is_array($wpshop_eav_content[$i]['entities']) && (count($wpshop_eav_content[$i]['entities']) > 0)){
					foreach($wpshop_eav_content[$i]['entities'] as $entity_code => $entity_table){
						$wpdb->insert(WPSHOP_DBT_ENTITIES, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'code' => $entity_code, 'entity_table' => $entity_table));
					}
				}

				/*	Create attributes for a given entity if attributes are set to be created for current version	*/
				if(is_array($wpshop_eav_content[$i]['attributes']) && is_array($wpshop_eav_content[$i]['attributes']) && (count($wpshop_eav_content[$i]['attributes']) > 0)){
					foreach($wpshop_eav_content[$i]['attributes'] as $entity_code => $attribute_definition){
						foreach($attribute_definition as $attribute_def){
							$option_list_for_attribute = '';
							if(isset($attribute_def['frontend_input_values'])){
								$option_list_for_attribute = $attribute_def['frontend_input_values'];
								unset($attribute_def['frontend_input_values']);
							}

							/*	Get entity identifier from code	*/
							$attribute_def['entity_id'] = wpshop_entities::get_entity_identifier_from_code($entity_code);
							$attribute_def['status'] = $attribute_def['attribute_status'];
							unset($attribute_def['attribute_status']);
							$attribute_def['creation_date'] = current_time('mysql', 0);
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attribute_def);
							$new_attribute_id = $wpdb->insert_id;

							/*	Insert option values if there are some to add for the current attribute	*/
							if(($option_list_for_attribute != '') && (is_array($option_list_for_attribute))){
								foreach($option_list_for_attribute as $option_code => $option_value){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $new_attribute_id, 'label' => ((substr($option_code, 0, 2) != '__') ? $option_value : substr($option_code, 2)), 'value' => $option_value));
									if($option_code == $attribute_def['default_value']){
										$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $new_attribute_id, 'default_value' => $option_code));
									}
								}
							}
						}
					}
				}

				/*	Create attribute groups for a given entity if attributes groups are set to be created for current version	*/
				if(is_array($wpshop_eav_content[$i]['attribute_groups']) && is_array($wpshop_eav_content[$i]['attribute_groups']) && (count($wpshop_eav_content[$i]['attribute_groups']) > 0)){
					foreach($wpshop_eav_content[$i]['attribute_groups'] as $entity_code => $attribute_set){
						$entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

						if($entity_id > 0){
							foreach($attribute_set as $set_name => $set_groups){
								$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
								$attribute_set_id = $wpdb->get_var($query);
								if($attribute_set_id <= 0){
									$attribute_set_content = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name);
									if($set_name == 'default'){
										$attribute_set_content['default_set'] = 'yes';
									}
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, $attribute_set_content);
									$attribute_set_id = $wpdb->insert_id;
								}

								if($attribute_set_id > 0){
									foreach($set_groups as $set_group_infos){
										$set_group_infos_details = $set_group_infos['details'];
										unset($set_group_infos['details']);
										/*	Change an attribute set status if definition specify this param 	*/
										if(isset($set_group_infos['status'])){
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
										}
										$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
										$attribute_set_section_id = $wpdb->get_var($query);
										if($attribute_set_section_id <= 0){
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
											$new_set_section_infos['creation_date'] = current_time('mysql', 0);
											$new_set_section_infos['attribute_set_id'] = $attribute_set_id;
											$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
											$attribute_set_section_id = $wpdb->insert_id;
										}

										if(($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details) && (count($set_group_infos_details) > 0))){
											$query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
											$last_position = $wpdb->get_var($query);
											$position = (int)$last_position + 1;
											foreach($set_group_infos_details as $attribute_code){
												$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
												$attribute_id = $wpdb->get_var($query);
												if($attribute_id > 0){
													$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $attribute_id, 'position' => $position));
													$position++;
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$do_changes = true;
			}
			/*	Eav content update	*/
			if(is_array($wpshop_eav_content_update) && is_array($wpshop_eav_content_update[$i]) && (count($wpshop_eav_content_update[$i]) > 0)){
				/*	Update attributes fo a given entity if attributes are set to be updated for current version	*/
				if(is_array($wpshop_eav_content_update[$i]['attributes']) && (count($wpshop_eav_content_update[$i]['attributes']) > 0)){
					foreach($wpshop_eav_content_update[$i]['attributes'] as $entity_code => $attribute_definition){
						foreach($attribute_definition as $attribute_def){
							$option_list_for_attribute = '';
							if(isset($attribute_def['frontend_input_values'])){
								$option_list_for_attribute = $attribute_def['frontend_input_values'];
								unset($attribute_def['frontend_input_values']);
							}

							/*	Get entity identifier from code	*/
							$attribute_def['entity_id'] = wpshop_entities::get_entity_identifier_from_code($entity_code);
							$attribute_def['status'] = $attribute_def['attribute_status'];
							unset($attribute_def['attribute_status']);
							$attribute_def['last_update_date'] = current_time('mysql', 0);
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE, $attribute_def, array('code' => $attribute_def['code']));
							$attribute_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_def['code']));

							/*	Insert option values if there are some to add for the current attribute	*/
							if(($option_list_for_attribute != '') && (is_array($option_list_for_attribute))){
								foreach($option_list_for_attribute as $option_code => $option_label){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'attribute_id' => $attribute_id, 'label' => $option_label));
									if($option_code == $attribute_def['default_value']){
										$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $wpdb->insert_id), array('id' => $attribute_id, 'default_value' => $option_code));
									}
								}
							}
						}
					}
					$do_changes = true;
				}

				/*	Update attribute groups fo a given entity if attributes groups are set to be updated for current version	*/
				if(is_array($wpshop_eav_content_update[$i]['attribute_groups']) && is_array($wpshop_eav_content_update[$i]['attribute_groups']) && (count($wpshop_eav_content_update[$i]['attribute_groups']) > 0)){
					foreach($wpshop_eav_content_update[$i]['attribute_groups'] as $entity_code => $attribute_set){
						$entity_id = wpshop_entities::get_entity_identifier_from_code($entity_code);

						if($entity_id > 0){
							foreach($attribute_set as $set_name => $set_groups){
								$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE entity_id = %d AND name = LOWER(%s)", $entity_id, wpshop_tools::slugify($set_name, array('noAccent', 'noSpaces', 'lowerCase')));
								$attribute_set_id = $wpdb->get_var($query);
								if($attribute_set_id <= 0){
									$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_SET, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_id' => $entity_id, 'name' => $set_name));
									$attribute_set_id = $wpdb->insert_id;
								}

								if($attribute_set_id > 0){
									foreach($set_groups as $set_group_infos){
										$set_group_infos_details = $set_group_infos['details'];
										unset($set_group_infos['details']);
										/*	Change an attribute set status if definition specify this param 	*/
										if(isset($set_group_infos['status'])){
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_SET, array('last_update_date' => current_time('mysql', 0), 'status' => $set_group_infos['status']), array('id' => $attribute_set_id));
										}
										$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %d AND code = LOWER(%s)", $attribute_set_id, $set_group_infos['code']);
										$attribute_set_section_id = $wpdb->get_var($query);
										if($attribute_set_section_id <= 0){
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['status'] = (isset($new_set_section_infos['status']) ? $new_set_section_infos['status'] : 'valid');
											$new_set_section_infos['creation_date'] = current_time('mysql', 0);
											$new_set_section_infos['attribute_set_id'] = $attribute_set_id;
											$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos);
											$attribute_set_section_id = $wpdb->insert_id;
										}
										else{
											$new_set_section_infos = $set_group_infos;
											$new_set_section_infos['last_update_date'] = current_time('mysql', 0);
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_GROUP, $new_set_section_infos, array('id' => $attribute_set_section_id));
										}

										if(($attribute_set_section_id > 0) && (isset($set_group_infos_details) && is_array($set_group_infos_details))){
											if(count($set_group_infos_details) <= 0){
												$wpdb->update(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), array('entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id));
											}
											else{
												$query = $wpdb->prepare("SELECT MAX(position) AS position FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d", $entity_id, $attribute_set_id, $attribute_set_section_id);
												$last_position = $wpdb->get_var($query);
												$position = (int)$last_position + 1;
												foreach($set_group_infos_details as $attribute_code){
													$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s AND entity_id = %d", $attribute_code, $entity_id);
													$attribute_id = $wpdb->get_var($query);
													if($attribute_id > 0){
														$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $entity_id, 'attribute_set_id' => $attribute_set_id, 'attribute_group_id' => $attribute_set_section_id, 'attribute_id' => $attribute_id, 'position' => $position));
														$position++;
													}
												}
											}
										}
									}
								}
							}
						}
					}
					$do_changes = true;
				}
			}

			/*	Add datas	*/
			if(is_array($wpshop_db_content_add) && is_array($wpshop_db_content_add[$i]) && (count($wpshop_db_content_add[$i]) > 0)){
				foreach($wpshop_db_content_add[$i] as $table_name => $def){
					foreach($def as $information_index => $table_information){
						$wpdb->insert($table_name, $table_information, '%s');
						$do_changes = true;
					}
				}
			}

			/*	Add datas	*/
			if(is_array($wpshop_db_request) && is_array($wpshop_db_request[$i]) && (count($wpshop_db_request[$i]) > 0)){
				foreach($wpshop_db_request[$i] as $request){
					$query = $wpdb->prepare($request);
					$wpdb->query($query);
					$do_changes = true;
				}
			}

			/*	Update datas	*/
			if(is_array($wpshop_db_content_update) && is_array($wpshop_db_content_update[$i]) && (count($wpshop_db_content_update[$i]) > 0)){
				foreach($wpshop_db_content_update[$i] as $table_name => $def){
					foreach($def as $information_index => $table_information){
						$wpdb->update($table_name, $table_information['datas'], $table_information['where'], '%s', '%s');
						$do_changes = true;
					}
				}
			}
		}

		self::make_specific_operation_on_update($i);

		return $do_changes;
	}
	
	/**
	* Manage special operation on wpshop plugin update
	*/
	function make_specific_operation_on_update($version){
		global $wpdb;
		switch($version){
			case 3:
				self::wpshop_insert_default_pages();
				wp_cache_flush();
			break;
			case 6:
				self::wpshop_insert_default_pages();
				wp_cache_flush();
			break;
			case 8:
				/*	Update the product prices into database	*/
				$query = $wpdb->prepare("
SELECT 
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS product_price,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS price_ht,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tx_tva,
(SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s) AS tva", 'product_price', 'price_ht', 'tx_tva', 'tva');
				$product_prices = $wpdb->get_row($query);
				$tax_id = $wpdb->get_var($wpdb->prepare("SELECT ATT_OPT.id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUE_OPTIONS . " AS ATT_OPT WHERE attribute_id = %d AND value = '19.6'", $product_prices->tx_tva));
				$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " WHERE attribute_id = %d", $product_prices->product_price);
				$price_list = $wpdb->get_results($query);
				foreach($price_list as $existing_ttc_price){
					$tax_rate = 1.196;
					$price_ht = $existing_ttc_price->value / $tax_rate;
					$tax_amount = $existing_ttc_price->value - $price_ht;

					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->price_ht, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $price_ht, 'creation_date_value' => current_time('mysql', 0)));
					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tx_tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_id, 'creation_date_value' => current_time('mysql', 0)));
					$wpdb->replace(WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, array('entity_type_id' => $existing_ttc_price->entity_type_id, 'attribute_id' => $product_prices->tva, 'entity_id' => $existing_ttc_price->entity_id, 'unit_id' => $existing_ttc_price->unit_id, 'user_id' => $existing_ttc_price->user_id, 'language' => $existing_ttc_price->language, 'value' => $tax_amount, 'creation_date_value' => current_time('mysql', 0)));
				}

				/*	Update orders structure into database	*/
				$orders_id = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"');
				foreach($orders_id as $o){
					$myorder = get_post_meta($o->ID, '_order_postmeta', true);
					$neworder = array();
					$items = array();
					
					if(!isset($myorder['order_tva'])){
						$order_total_ht = 0;
						$order_total_ttc = 0;
						$order_tva = array('19.6'=>0);
						
						foreach($myorder['order_items'] as $item){
							/* item */
							$pu_ht = $item['cost']/1.196;
							$pu_tva = $item['cost']-$pu_ht;
							$total_ht = $pu_ht*$item['qty'];
							$tva_total_amount = $pu_tva*$item['qty'];
							$total_ttc = $item['cost']*$item['qty'];
							/* item */
							$order_total_ht += $total_ht;
							$order_total_ttc += $total_ttc;
							$order_tva['19.6'] += $tva_total_amount;
							
							$items[] = array(
								'item_id' => $item['id'],
								'item_ref' => 'Nc',
								'item_name' => $item['name'],
								'item_qty' => $item['qty'],

								'item_pu_ht' => number_format($pu_ht, 5, '.', ''),
								'item_pu_ttc' => number_format($item['cost'], 5, '.', ''),

								'item_ecotaxe_ht' => number_format(0, 5, '.', ''),
								'item_ecotaxe_tva' => 19.6,
								'item_ecotaxe_ttc' => number_format(0, 5, '.', ''),

								'item_discount_type' => 0,
								'item_discount_value' => 0,
								'item_discount_amount' => number_format(0, 5, '.', ''),

								'item_tva_rate' => 19.6,
								'item_tva_amount' => number_format($pu_tva, 5, '.', ''),

								'item_total_ht' => number_format($total_ht, 5, '.', ''),
								'item_tva_total_amount' => number_format($tva_total_amount, 5, '.', ''),
								'item_total_ttc' => number_format($total_ttc, 5, '.', '')
								/*'item_total_ttc_with_ecotaxe' => number_format($total_ttc, 5, '.', '')*/
							);
						}
						
						$neworder = array(
							'order_key' => $myorder['order_key'],
							'customer_id' => $myorder['customer_id'],
							'order_status' => $myorder['order_status'],
							'order_date' => $myorder['order_date'],
							'order_payment_date' => $myorder['order_payment_date'],
							'order_shipping_date' => $myorder['order_shipping_date'],
							'payment_method' => $myorder['payment_method'],
							'order_invoice_ref' => '',
							'order_currency' => $myorder['order_currency'],
							'order_total_ht' => $order_total_ht,
							'order_total_ttc' => $order_total_ttc,
							'order_grand_total' => $order_total_ttc,
							'order_shipping_cost' => number_format(0, 5, '.', ''),
							'order_tva' => array_map('number_format_hack', $order_tva),
							'order_items' => $items
						);
						/* Update the order postmeta */
						update_post_meta($o->ID, '_order_postmeta', $neworder);
					}
				}
				
				self::wpshop_insert_default_pages();
				wp_cache_flush();
			break;
			case 12:
				$query = $wpdb->prepare("SELECT ID FROM $wpdb->users");
				$user_list = $wpdb->get_results($query);
				foreach($user_list as $user){
					$user_first_name = get_user_meta($user->ID, 'first_name', true);
					$user_last_name = get_user_meta($user->ID, 'last_name', true);
					$shipping_info = get_user_meta($user->ID, 'shipping_info', true);

					if(($user_first_name == '') && ($shipping_info['first_name'] != '')){
						update_user_meta($user->ID, 'first_name', $tshipping_info['first_name']);
					}

					if(($user_last_name == '') && ($shipping_info['last_name'] != '')){
						update_user_meta($user->ID, 'last_name', $shipping_info['last_name']);
					}
				}

				/*	Update orders structure into database	*/
				$orders_id = $wpdb->get_results('SELECT ID FROM '.$wpdb->posts.' WHERE post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'"');
				foreach($orders_id as $o){
					$myorder = get_post_meta($o->ID, '_order_postmeta', true);
					if(!empty($myorder)){
						$new_items = array();
						foreach($myorder['order_items'] as $item){
							$new_items = $item;
							$new_items['item_discount_type'] = $item['item_discount_rate'];
							unset($new_items['item_discount_rate']);
							$new_items['item_discount_value'] = 0;
						}
						$myorder['order_items'] = $new_items;
						
						/* Update the order postmeta */
						update_post_meta($o->ID, '_order_postmeta', $myorder);
					}
				}

				/*	Delete useless database table	*/
				$query = $wpdb->prepare("DROP TABLE " . WPSHOP_DBT_CART);
				$wpdb->query($query);
				$query = $wpdb->prepare("DROP TABLE " . WPSHOP_DBT_CART_CONTENTS);
				$wpdb->query($query);
			break;
			case 13:
				$attribute_used_for_sort_by = wpshop_attributes::getElement('yes', "'valid', 'moderated', 'notused'", 'is_used_for_sort_by', true); 
				foreach($attribute_used_for_sort_by as $attribute){
					$data = query_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					foreach($data as $post){
						$postmeta = get_post_meta($post->ID, '_wpshop_product_metadata', true);
						if(!empty($postmeta[$attribute->code])) {
							update_post_meta($post->ID, '_'.$attribute->code, $postmeta[$attribute->code]);
						}
					}
					wp_reset_query();
				}
			break;
			case 17:
				$products = query_posts(array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)
				);
				$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " WHERE default_set = %s", 'yes');
				$default_attribute_set = $wpdb->get_var($query);
				foreach($products as $product){
					$p_att_set_id = get_post_meta($product->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
					if(empty($p_att_set_id)){
						/*	Update the attribute set id for the current product	*/
						update_post_meta($product->ID, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, $default_attribute_set);
					}
				}
				self::wpshop_insert_default_pages();
				wp_cache_flush();
			break;

			/*	Always add specific case before this bloc	*/
			case 'dev':
			break;
		}
	}


	/**
	*	Method called when deactivating the plugin
	*	@see register_deactivation_hook()
	*/
	function uninstall_wpshop(){
		global $wpdb;

		if(WPSHOP_DEBUG_ALLOW_DATA_DELETION && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_ALLOWED_IP))){
			$query = $wpdb->query("DROP TABLE `wp_wpshop__attribute`, `wp_wpshop__attributes_unit`, `wp_wpshop__attributes_unit_groups`, `wp_wpshop__attribute_set`, `wp_wpshop__attribute_set_section`, `wp_wpshop__attribute_set_section_details`, `wp_wpshop__attribute_value_datetime`, `wp_wpshop__attribute_value_decimal`, `wp_wpshop__attribute_value_integer`, `wp_wpshop__attribute_value_text`, `wp_wpshop__attribute_value_varchar`, `wp_wpshop__attribute_value__histo`, `wp_wpshop__cart`, `wp_wpshop__cart_contents`, `wp_wpshop__documentation`, `wp_wpshop__entity`, `wp_wpshop__historique`, `wp_wpshop__message`, `wp_wpshop__attribute_value_options`;");
			$query = $wpdb->query("DELETE FROM `wp_options` WHERE `option_name` LIKE '%wpshop%';");

			$wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_type LIKE 'wpshop_%';");
			$list = '  ';
			foreach($wpshop_products_posts as $post){
				$list .= "'" . $post->ID . "', ";
			}
			$list = substr($list, 0, -2);

			$wpshop_products_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_parent IN (" . $list . ");");
			$list_attachment = '  ';
			foreach($wpshop_products_posts as $post){
				$list_attachment .= "'" . $post->ID . "', ";
			}
			$list_attachment = substr($list_attachment, 0, -2);

			$query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->postmeta . " WHERE post_id IN (" . $list_attachment . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE ID IN (" . $list_attachment . ");");
			$query = $wpdb->query("DELETE FROM " . $wpdb->posts . " WHERE post_content LIKE '%wpshop%';");
		}

		/*	Unset administrator permission	*/
		$adminRole = get_role('administrator');
		foreach($adminRole->capabilities as $capabilityName => $capability){
			if(substr($capabilityName, 0, 7) == 'wpshop_'){
				if($adminRole->has_cap($capabilityName)){
					$adminRole->remove_cap($capabilityName);
				}
			}
		}

	}

}