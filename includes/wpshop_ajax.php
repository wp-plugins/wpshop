<?php
/**
* Gestion des requetes ajax pour le plugin
* 
* @author Eoxia <dev@eoxia.com>
* @version 1.3.2.3
* @package wpshop
* @subpackage includes
*/

/*	Vérification de l'inclusion correcte du fichier => Interdiction d'acceder au fichier directement avec l'url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/*	Produits	*/
	/**
	 * Contenu de la boite de dialogue permettant de sélectionner les éléments contenu dans un type de variation
	 */
	function ajax_add_new_variation() {
		check_ajax_referer( 'wpshop_variation_creation', 'wpshop_ajax_nonce' );

		$attributes_for_variation = isset($_POST['checkboxes']) ? ($_POST['checkboxes']) : null;
		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
	
		$variation_id = wpshop_products::create_variation($current_post_id, $attributes_for_variation);

		$output = wpshop_products::display_variation_admin($current_post_id);

		echo $output;
		die();
	}
	add_action('wp_ajax_add_new_variation', 'ajax_add_new_variation');

	/**
	 * Dupliquer une variation existante
	 */
	function ajax_duplicate_variation() {
		check_ajax_referer( 'wpshop_variation_duplication', 'wpshop_ajax_nonce' );

		$current_post_id = isset($_POST['current_post_id']) ? wpshop_tools::varSanitizer($_POST['current_post_id']) : null;
		$attributes_for_variation = get_post_meta($current_post_id, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT_VARIATION);

		$variation_id = wpshop_products::create_variation($current_post_id, $attributes_for_variation);

		$output = wpshop_products::display_variation_admin($current_post_id);
		
		echo $output;
		die();
	}
	add_action('wp_ajax_duplicate_variation', 'ajax_duplicate_variation');

	/**
	 * Suppression d'une variation de produit
	 */
	function ajax_delete_variation() {
		check_ajax_referer( 'wpshop_delete_variation', 'wpshop_ajax_nonce' );

		$result = true;

		$current_post_id = isset($_POST['current_post_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_post_id'])) : null;
		$result = wp_delete_post($current_post_id, false);

		echo json_encode(array($result, $current_post_id));
		die();
	}
	add_action('wp_ajax_delete_variation', 'ajax_delete_variation');

	/**
	 * Supprime une image associée à un produit
	 */
	function ajax_delete_product_thumbnail() {
		check_ajax_referer( 'wpshop_delete_product_thumbnail', 'wpshop_ajax_nonce' );

		$bool = false;
		$attachement_id = isset($_POST['attachement_id']) ? intval(wpshop_tools::varSanitizer($_POST['attachement_id'])) : null;

		if ( !empty($attachement_id) ) {
			$deletion_result = wp_delete_attachment($attachement_id, false);
			$bool = !empty($deletion_result);
		}

		echo json_encode(array($bool, $attachement_id));
		die();
	}
	add_action('wp_ajax_delete_product_thumbnail', 'ajax_delete_product_thumbnail');
	/**
	 * Recharge le conteneur des fichiers attachés à un produit
	 */
	function ajax_reload_attachment_boxes () {
		check_ajax_referer( 'wpshop_reload_product_attachment_part', 'wpshop_ajax_nonce' );

		$bool = false;
		$current_post_id = isset($_POST['current_post_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_post_id'])) : null;
		$attachement_type_list = array('reload_box_document' => 'application/pdf', 'reload_box_picture' => 'image/');
		$part_to_reload = isset($_POST['part_to_reload']) ? wpshop_tools::varSanitizer($_POST['part_to_reload']) : null;
		$attachement_type = $attachement_type_list[$part_to_reload];

		echo json_encode(array(wpshop_products::product_attachement_by_type($current_post_id, $attachement_type, 'media-upload.php?post_id=' . $current_post_id . '&amp;tab=library&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566'), $part_to_reload));
		die();
	}
	add_action('wp_ajax_reload_product_attachment', 'ajax_reload_attachment_boxes');


/*	Valeurs des attributs de type liste déroulantes	*/
	/**
	 * Ajout d'une nouvelle valeur pour un attribut de type liste deroulante
	 *
	 * @return string Le conteneur sous forme html de la nouvelle valeur
	 */
	function ajax_new_option_for_select_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_creation', 'wpshop_ajax_nonce' );

		global $wpdb;
	
		$option_id=$option_default_value=$option_value_id=$options_value='';
		$attribute_identifier = isset($_GET['attribute_identifier']) ? wpshop_tools::varSanitizer($_GET['attribute_identifier']) : '0';
		$option_name=(!empty($_REQUEST['attribute_new_label']) ? $_REQUEST['attribute_new_label'] : '');
		$options_value=sanitize_title($option_name);
	
		/*	Verification de l'inexistence de la valeur entree	*/
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", $option_name, $options_value, $attribute_identifier);
		$existing_values = $wpdb->get_results($query);
	
		/*	Affichage du contenu si la valeur n'existe pas	*/
		if( count($existing_values) <= 0 ) {
			ob_start();
			include(WPSHOP_TEMPLATES_DIR.'admin/attribute_option_value.tpl.php');
			$output = ob_get_contents();
			ob_end_clean();
	
			echo json_encode(array(true, str_replace('optionsUpdate', 'options', $output)));
		}
		else {
			echo json_encode(array(false, __('The value you entered already exist', 'wpshop')));
		}
		die();
	}
	add_action('wp_ajax_new_option_for_select', 'ajax_new_option_for_select_callback');

	/**
	 * Ajout une valeur a une liste d'option pour un attribut directement depuis l'interface d'edition d'un produit
	 */
	function ajax_new_option_for_select_from_product_edition_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_creation', 'wpshop_ajax_nonce' );

		global $wpdb;
		$result = '';

		$item_in_edition = isset($_POST['item_in_edition']) ? intval(wpshop_tools::varSanitizer($_POST['item_in_edition'])) : '0';
		$attribute_code = isset($_POST['attribute_code']) ? wpshop_tools::varSanitizer($_POST['attribute_code']) : '0';

		/*	Vérification du type de valeur utilisé pour cet attribut	*/
		$type = 'custom';
		$real_attr_code = str_replace('custom_', '', $attribute_code);
		if (substr($attribute_code, 0, 9) == 'internal_') {
			$type = 'internal';
			$real_attr_code = str_replace('internal_', '', $attribute_code);		
		}
		$attribute = wpshop_attributes::getElement($real_attr_code, "'valid'", 'code');
		$attribute_options_label = isset($_POST['attribute_new_label']) ? wpshop_tools::varSanitizer($_POST['attribute_new_label']) : null;
		$attribute_options_value = sanitize_title($attribute_options_label);

		if ($type == 'custom') {
			/*	Verification de l'inexistence de la valeur entree	*/
			$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE (label = %s OR value = %s) AND attribute_id = %d AND status = 'valid'", str_replace(",", ".", $attribute_options_label), $attribute_options_value, $attribute->id);
			$existing_values = $wpdb->get_results($query);
		
			/*	Si la valeur est inexistante alors on la cree sinon on retourne une erreur	*/
			if( count($existing_values) <= 0 ) {
				$result_status = true;
				$position = 1;
				/*	Recuperation de la position de la derniere valeur pour ajouter la nouvelle a la fin de la liste	*/
				$query = $wpdb->prepare("SELECT position FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d", $attribute->id);
				$position = $wpdb->get_var($query);
		
				/*	Creation de la nouvelle valeur	*/
				$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $attribute->id, 'position' => $position, 'label' => str_replace(",", ".", $attribute_options_label), 'value' => $attribute_options_value));
				$new_option_id = $wpdb->insert_id;
			}
			else {
				$result_status = false;
				$result = __('This value already exist for this attribute', 'wpshop');
			}
		}
		else {
			/*	Verification de l'inexistence de la valeur entree	*/
			$query = $wpdb->prepare("SELECT * FROM " . $wpdb->posts . " WHERE post_title = %s AND post_status = 'publish'", $attribute_options_label);
			$existing_values = $wpdb->get_results($query);

			/*	Si la valeur est inexistante alors on la cree sinon on retourne une erreur	*/
			if ( count($existing_values) <= 0 ) {
				$result_status = true;
				/*	Creation de l'entité produit dans la table des posts	*/
				$new_post = array(
					'post_title' 	=> $attribute_options_label,
					'post_name' 	=> $attribute_options_value,
					'post_status' 	=> 'publish',
					'post_type' 	=> $attribute->default_value
				);
				$new_option_id = wp_insert_post($new_post);
				$input_def['valueToPut'] = 'index';
			}
			else {
				$result_status = false;
				$result = __('This value already exist for this attribute', 'wpshop');
			}
		}

		if ($result_status) {
			/*	Recuperation de la liste des valeurs pour l'attribut en cours puis affichage de la liste deroulante	*/
			$currentPageCode = wpshop_products::currentPageCode;
			$input_def['option'] = ' class="wpshop_product_attribute_' . $attribute->code . ' alignleft chosen_select" ';
			$attributeInputDomain = $currentPageCode . '_attribute[' . $attribute->data_type . ']';
			$input_def['id'] = $currentPageCode . '_' . $item_in_edition . '_attribute_' . $attribute->id;
			$input_def['intrinsec'] = $attribute->is_intrinsic;
			$input_def['name'] = $attribute->code;
			$input_def['type'] = $attribute->backend_input;
			$input_def['value'] = $new_option_id;
			$select_display = wpshop_attributes::get_select_output($attribute);
			$input_def['possible_value'] = $select_display['possible_value'];
			$result = wpshop_form::check_input_type($input_def, $attributeInputDomain) . $select_display['more_input'];	
		}
	
		echo json_encode(array($result_status, $result, $real_attr_code));
		die();
	}
	add_action('wp_ajax_new_option_for_select_from_product_edition', 'ajax_new_option_for_select_from_product_edition_callback');

	/**
	 * Supprime une valeur de la liste pour les attributs de type liste deroulante avec valeur personnalisee
	 */
	function ajax_delete_option_for_select_callback() {
		check_ajax_referer( 'wpshop_new_option_for_attribute_deletion', 'wpshop_ajax_nonce' );

		$attribute_value_id = isset($_POST['attribute_value_id']) ? wpshop_tools::varSanitizer($_POST['attribute_value_id']) : '0';
	
		$result_status = false;
		$result = __('An error occured while deleting selected value', 'wpshop');
		if (!empty($attribute_value_id)) :
		$action_result = wpshop_database::update(array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), $attribute_value_id, WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS);
		if ($action_result == 'done') :
		$result_status = true;
		$result = "#att_option_div_container_" . $attribute_value_id;
		endif;
		endif;
	
		echo json_encode(array($result_status, $result));
		die();
	}
	add_action('wp_ajax_delete_option_for_select', 'ajax_delete_option_for_select_callback');

/*	Attributs	*/
	/**
	 * Lecture des differents types de champs possible pour les attributs
	 */
	function ajax_attribute_output_type_callback() {
		check_ajax_referer( 'wpshop_attribute_output_type_selection', 'wpshop_ajax_nonce' );

		$data_type_to_use = isset($_GET['data_type_to_use']) ? str_replace('_data', '', wpshop_tools::varSanitizer($_GET['data_type_to_use'], '')) : 'custom';
		$current_type = isset($_GET['current_type']) ? wpshop_tools::varSanitizer($_GET['current_type']) : 'short_text';
		$elementIdentifier = isset($_GET['elementIdentifier']) ? intval( wpshop_tools::varSanitizer($_GET['elementIdentifier'])) : null;
		$the_input = __('An error occured while getting field type', 'wpshop');
		$input_def = array();
		$input_def['name'] = 'default_value';
		$input_def['id'] = 'wpshop_attributes_edition_table_field_id_default_value';
		$input_label=__('Default value', 'wpshop');
	
		switch($current_type){
			case 'short_text':
			case 'float_field':
				$input_def['type'] = 'text';
				$input_def['value'] = '';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
			case 'select':
			case 'multiple-select':
				$input_label=__('Options list for attribute', 'wpshop');
				$the_input = wpshop_attributes::get_select_options_list($elementIdentifier, $data_type_to_use);
				break;
			case 'date_field':
				$input_label=__('Use the date of the day as default value', 'wpshop');
				$input_def['type'] = 'checkbox';
				$input_def['possible_value'] = 'date_of_current_day';
				$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using date of the day as value when editing a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
			case 'textarea':
				$input_def['type'] = 'textarea';
				$input_def['value'] = '';
				$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				break;
		}
	
		echo json_encode(array($the_input, $input_label));
		die();
	}
	add_action('wp_ajax_attribute_output_type', 'ajax_attribute_output_type_callback');

	/**
	 * Lecture des differents types de champs possible pour les attributs
	 */
	function ajax_attribute_entity_set_selection_callback() {
		check_ajax_referer( 'wpshop_attribute_entity_set_selection', 'wpshop_ajax_nonce' );

		$current_entity_id = isset($_POST['current_entity_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_entity_id'])) : null;

		$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  wpshop_attributes::getDbTable(), wpshop_attributes::currentPageCode);
	
		echo json_encode($the_input);
		die();
	}
	add_action('wp_ajax_attribute_entity_set_selection', 'ajax_attribute_entity_set_selection_callback');
	/**
	 * Lecture des differents types de champs possible pour les attributs
	 */
	function ajax_attribute_set_entity_selection_callback() {
		check_ajax_referer( 'wpshop_attribute_set_entity_selection', 'wpshop_ajax_nonce' );

		$current_entity_id = isset($_POST['current_entity_id']) ? intval(wpshop_tools::varSanitizer($_POST['current_entity_id'])) : null;

		$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  wpshop_attributes_set::getDbTable(), wpshop_attributes::currentPageCode, false);
	
		echo json_encode($the_input);
		die();
	}
	add_action('wp_ajax_attribute_set_entity_selection', 'ajax_attribute_set_entity_selection_callback');

	/**
	 * Affichage de la boite de dialogue permettant de changer le type de données sur les attributs de type liste déroulante
	 */
	function ajax_attribute_select_data_type_callback() {
		check_ajax_referer( 'wpshop_attribute_change_select_data_type', 'wpshop_ajax_nonce' );
		$result = '';

		$current_attribute = isset($_POST['current_attribute']) ? intval(wpshop_tools::varSanitizer($_POST['current_attribute'])) : null;
		$attribute = wpshop_attributes::getElement($current_attribute);

		$types_toggled = unserialize(WPSHOP_ATTR_SELECT_TYPE_TOGGLED);
		$result .= '<p class="wpshop_change_select_data_type_change wpshop_change_select_data_type_change_current_attribute" >' . sprintf(__('Selected attribute %s', 'wpshop'), $attribute->frontend_label) . '</p>';
		$result .= '<p class="wpshop_change_select_data_type_change wpshop_change_select_data_type_change_types" >' . sprintf(__('Actual data type is %s. After current operation: %s', 'wpshop'), __($attribute->data_type_to_use.'_data', 'wpshop'), __($types_toggled[$attribute->data_type_to_use], 'wpshop')) . '</p>';

		if ( $attribute->data_type_to_use == 'custom' ) {
			$sub_output='';
			$wp_types = unserialize(WPSHOP_INTERNAL_TYPES);
			unset($input_def);$input_def=array();
			$input_def['label'] = __('Type of data for list', 'wpshop');
			$input_def['type'] = 'select';
			$input_def['name'] = 'internal_data';
			$input_def['valueToPut'] = 'index';
			$input_def['possible_value'] = $wp_types;
			$input_def['value'] = !empty($attribute_select_options[0]->default_value) ? $attribute_select_options[0]->default_value : null;
			$combo_wp_type = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
			$result .= __('Choose the data type to use for this attribute', 'wpshop') . '<a href="#" title="'.sprintf(__('If the type you want to use is not in the list below. You have to create it by using %s menu', 'wpshop'), __('Entities', 'wpshop')).'" class="wpshop_infobulle_marker">?</a><div class="clear wpshop_attribute_select_data_type_internal_list">'.$combo_wp_type.'</div>';
			$result .= '<input type="hidden" value="no" name="delete_items_of_entity" id="delete_items_of_entity" /><input type="hidden" value="no" name="delete_entity" id="delete_entity" />';
		}
		else {
			$result .= '<input type="hidden" value="' . $attribute->default_value . '" name="internal_data" id="internal_data" />';

			unset($input_def);
			$input_def['label'] = __('Delete existing items when transfer is complete', 'wpshop');
			$input_def['name'] = 'delete_items_of_entity';
			$input_def['option'] = ' class="wpshop_attribute_change_select_data_type_deletion_input wpshop_attribute_change_select_data_type_deletion_input_item" ';
			$input_def['type'] = 'checkbox';
			$input_def['possible_value'] = 'yes';
			$result .= '<p class="cursor" >' . wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE) . ' <label for="' . $input_def['name'] . '">' . $input_def['label'] . '</label></p>';

			$input_def['label'] = __('Delete entity type when transfer is complete', 'wpshop');
			$input_def['name'] = 'delete_entity';
			$input_def['option'] = ' class="wpshop_attribute_change_select_data_type_deletion_input wpshop_attribute_change_select_data_type_deletion_input_entity" ';
			$result .= '<p>' . wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE) . ' <label for="' . $input_def['name'] . '">' . $input_def['label'] . '</label></p>';

			$result .= '<div class="wpshop_attribute_change_data_type_alert wpshopHide" >' . __('Be careful by checking boxes above, you will destroy element. This operation could not be reversed later', 'wpshop') . '</div>';
		}
		
		$result .= '<input type="hidden" value="' . str_replace('_data', '', $types_toggled[$attribute->data_type_to_use]) . '" name="wpshop_attribute_change_data_type_new_type" id="wpshop_attribute_change_data_type_new_type" />';

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_attribute_select_data_type', 'ajax_attribute_select_data_type_callback');
	/**
	 * Changement de type de données pour les attributs de type liste déroulante
	 */
	function ajax_attribute_select_data_type_change_callback() {
		global $wpdb;
		check_ajax_referer( 'wpshop_attribute_change_select_data_type_change', 'wpshop_ajax_nonce' );
		$result = '';

		$current_attribute = isset($_POST['attribute_id']) ? intval(wpshop_tools::varSanitizer($_POST['attribute_id'])) : null;
		$data_type = isset($_POST['data_type']) ? wpshop_tools::varSanitizer($_POST['data_type']) : null;
		$internal_data_type = isset($_POST['internal_data']) ? wpshop_tools::varSanitizer($_POST['internal_data']) : null;
		$delete_items_of_entity = isset($_POST['delete_items_of_entity']) ? wpshop_tools::varSanitizer($_POST['delete_items_of_entity']) : false;
		$delete_entity = isset($_POST['delete_entity']) ? wpshop_tools::varSanitizer($_POST['delete_entity']) : false;


		if ( $data_type == 'internal' ) {
			$options_list = wpshop_attributes::get_select_option_list_($current_attribute);
			if(!empty($options_list)){
				foreach($options_list as $option){
					/*	Creation de l'entité produit dans la table des posts	*/
					$new_post = array(
							'post_title' 	=> $option->name,
							'post_name' 	=> $option->value,
							'post_status' 	=> 'publish',
							'post_type' 	=> $internal_data_type
					);
					$new_option_id = wp_insert_post($new_post);
					if(!empty($new_option_id)){
						$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'deleted'), array('attribute_id'=>$current_attribute));
					}
				}
			}
		}
		else {
			$post_list = query_posts(array('post_type' => $internal_data_type));
			if (!empty($post_list)) {
				$p=1;
				$error = false;
				foreach ($post_list as $post) {
					$last_insert = $wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('status'=>'valid', 'creation_date'=>current_time('mysql',0), 'attribute_id'=>$current_attribute, 'position'=>$p, 'value'=>$post->post_name, 'label'=>$post->post_title));
					if(is_int($last_insert) && $delete_items_of_entity){
						wp_delete_post($post->ID, true);
					}
					else{
						$error = true;
					}
					$p++;
				}
				if(!$error && $delete_entity){
					$post = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s AND post_name=%s", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, $internal_data_type);
					wp_delete_post($wpdb->get_var($post), true);
				}
			}
			wp_reset_query();
		}

		/*	Changement du type dans l'attribut	*/
		$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('data_type_to_use' => $data_type, 'default_value' => $internal_data_type), array('id' => $current_attribute));

		$result = wpshop_attributes::get_select_options_list($current_attribute, $editedItem->$data_type);

		echo json_encode($result);
		die();
	}
	add_action('wp_ajax_attribute_select_data_type_change', 'ajax_attribute_select_data_type_change_callback');
	/**
	 * Duplique un attribut vers une autre entité
	 */
	function ajax_wpshop_duplicate_attribute_callback (){
		check_ajax_referer( 'wpshop_duplicate_attribute', 'wpshop_ajax_nonce' );
		global $wpdb;

		$result = '';
		
		$current_attribute = isset($_POST['attribute_id']) ? intval(wpshop_tools::varSanitizer($_POST['attribute_id'])) : null;
		$new_entity = isset($_POST['entity']) ? intval(wpshop_tools::varSanitizer($_POST['entity'])) : null;

		/*	Récupération de la définition de l'attribut	*/
		$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE id = %d", $current_attribute);
		$attribute_def = $wpdb->get_row($query, ARRAY_A);
		/*	Modification de l'entité affectée	*/
		$attribute_def['id'] = '';
		$attribute_def['creation_date'] = current_time('mysql', 0);
		$attribute_def['entity_id'] = $new_entity;
		$attribute_def['code'] = $attribute_def['code'] . '-' . $new_entity;

		/*	Récupération de la définition de l'attribut	*/
		$query = $wpdb->prepare("SELECT id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $attribute_def['code']);
		$check_existing_attribute = $wpdb->get_var($query);
		if ( empty($check_existing_attribute) ) {
			/*	Enregistrement du nouvel attribut	*/
			$new_attribute = $wpdb->insert(WPSHOP_DBT_ATTRIBUTE, $attribute_def);
			$new_attribute_id = $wpdb->insert_id;

			if ($new_attribute) {
				if (($attribute_def['backend_input'] == 'select') || ($attribute_def['backend_input'] == 'multiple-select') && ($attribute_def['data_type_to_use'] == 'custom') ) {
					$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d", $current_attribute);
					$attribute_options_list = $wpdb->get_results($query, ARRAY_A);
					foreach ( $attribute_options_list as $option ) {
						$option['id'] = '';
						$option['creation_date'] = current_time('mysql', 0);
						$option['attribute_id'] = $new_attribute_id;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, $option);
					}
				}
				$result = true;
				$result_output = '<p class="wpshop_duplicate_attribute_result" ><a href="' . admin_url('edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '&page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING . '&action=edit&id=' . $new_attribute_id) . '" >' . __('Edit the new attribute', 'wpshop') . '</a></p>';
			}
			else {
				$result = false;
				$result_output = __('An error occured while duplicating attribute', 'wpshop');
			}
		}
		else {
			$result = false;
			$result_output = __('This attribute has already been duplicate to this entity', 'wpshop');
		}


		echo json_encode(array($result, $result_output));		
		die();
	}
	add_action('wp_ajax_wpshop_duplicate_attribute', 'ajax_wpshop_duplicate_attribute_callback');	


/*	Page options	*/
	/**
	 * Activation des addons
	 */
	function ajax_activate_addons() {
		global $wpdb;
		check_ajax_referer( 'wpshop_ajax_activate_addons', 'wpshop_ajax_nonce' );

		$addon_name = isset($_POST['addon']) ? wpshop_tools::varSanitizer($_POST['addon']) : null;
		$addon_code = isset($_POST['code']) ? wpshop_tools::varSanitizer($_POST['code']) : null;
		$state = false;
		
		if (!empty($addon_name) && !empty($addon_code)) {
			$addons_list = (unserialize(WPSHOP_ADDONS_LIST));
			if (in_array($addon_name, array_keys($addons_list))) {
				$plug = get_plugin_data( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/wpshop.php' );
				$code = substr(hash ( "sha256" , $plug['Name'] ), WPSHOP_ADDONS_KEY_IS, 5) . '-' . substr(hash ( "sha256" , 'addons' ), WPSHOP_ADDONS_KEY_IS, 5) . '-' . substr(hash ( "sha256" , $addons_list[$addon_name][0] ),  $addons_list[$addon_name][1], 5);
				if ($code == $addon_code) {
					$extra_options = get_option('wpshop_addons_state', array());
					$extra_options[$addon_name] = true;
					if ( update_option('wpshop_addons_state', $extra_options) ) {
						$result = array(true, __('The addon has been activated successfully', 'wpshop'), __('Activated','wpshop'));
						if( !empty($addons_list[$addon_name][3]) ) {
							$activate_attribute_for_addon = $wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('status' => 'valid'), array('code' => $addons_list[$addon_name][3]));
							/**
							 * Ajout de l'attribut dans un groupe d'attribut pour éviter à l'utilisateur d'avoir à le faire
							 */
// 							if( $activate_attribute_for_addon !== false ){
// 								$query = $wpdb->prepare("SELECT id, entity_id FROM " . WPSHOP_DBT_ATTRIBUTE . " WHERE code = %s", $addons_list[$addon_name][3]);
// 								$attribute = $wpdb->get_row($query);
// 							}
						}
						$state = true;
					}
					else {
						$result = array(false, __('An error occured','wpshop'), __('Desactivated','wpshop'));
					}
				}
				else {
					$result = array(false, __('The activating code is invalid', 'wpshop'), __('Desactivated','wpshop'));
				}
			}
			else {
				$result = array(false, __('The addon to activate is invalid', 'wpshop'), __('Desactivated','wpshop'));
			}
		}
		else {
			$result = array(false, __('An error occured','wpshop'), __('Desactivated','wpshop'));
		}
		$activated_class = unserialize(WPSHOP_ADDONS_STATES_CLASS);
		
		echo json_encode(array_merge($result, array($addon_name, $activated_class[$state])));
		die();
	}
	add_action('wp_ajax_activate_wpshop_addons', 'ajax_activate_addons');

	/**
	 * Désactivation des addons
	 */
	function ajax_desactivate_wpshop_addons() {
		check_ajax_referer( 'wpshop_ajax_activate_addons', 'wpshop_ajax_nonce' );

		$addon_name = isset($_POST['addon']) ? wpshop_tools::varSanitizer($_POST['addon']) : null;
		$state = true;

		if ( !empty($addon_name) ) {
			$addons_list = array_keys(unserialize(WPSHOP_ADDONS_LIST));
			if (in_array($addon_name, $addons_list)) {
				$extra_options = get_option('wpshop_addons_state', array());
				$extra_options[$addon_name] = false;
				if ( update_option('wpshop_addons_state', $extra_options) ) {
					$result = array(true, __('The addon has been desactivated successfully', 'wpshop'), __('Desactivated','wpshop'));
					$state = false;
				}
				else {
					$result = array(false, __('An error occured','wpshop'), __('Activated','wpshop'));
				}
			}
			else {
				$result = array(false, __('The addon to desactivate is invalid', 'wpshop'), __('Activated','wpshop'));
			}
		}
		$activated_class = unserialize(WPSHOP_ADDONS_STATES_CLASS);

		echo json_encode(array_merge($result, array($addon_name, $activated_class[$state])));
		die();
	}
	add_action('wp_ajax_desactivate_wpshop_addons', 'ajax_desactivate_wpshop_addons');



/*	Frontend	*/
	function ajax_wpshop_add_to_cart() {
		global $wpshop_cart;
		$product_id = isset($_POST['wpshop_pdt']) ? intval(wpshop_tools::varSanitizer($_POST['wpshop_pdt'])) : null;

		$cart_type_for_adding = 'normal';
		if (!empty($_POST['wpshop_cart_type']) ) {
			switch(wpshop_tools::varSanitizer($_POST['wpshop_cart_type'])){
				case 'cart':
					$wpshop_cart_type = 'normal';
					break;
				case 'quotation':
					$wpshop_cart_type = 'quotation';
					break;
				default:
					$wpshop_cart_type = 'normal';
					break;
			}
		}

		$return = $wpshop_cart->add_to_cart(array($product_id), array($product_id=>1), $wpshop_cart_type);
		if ($return == 'success') {
			$cart_page_url = get_permalink( get_option('wpshop_cart_page_id') );
			if ($wpshop_cart_type == 'normal') {
				/*	Include the product sheet template	*/
				ob_start();
				require_once(wpshop_display::get_template_file('product_added_to_cart_message.tpl.php'));
				$succes_message_box = ob_get_contents();
				ob_end_clean();
				echo json_encode(array(true, $succes_message_box));
			}
			else {
				echo json_encode(array(true, $cart_page_url));
			}
		}
		else echo json_encode(array(false, $return));

		die();
	}
	add_action('wp_ajax_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');
	add_action('wp_ajax_nopriv_wpshop_add_product_to_cart', 'ajax_wpshop_add_to_cart');

?>