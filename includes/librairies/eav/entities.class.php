<?php
/**
* Définition des utilitaires pour gérer les entités
*
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
* Définition des utilitaires pour gérer les entités
*
* @package wpshop
* @subpackage librairies
*/
class wpshop_entities {

	/**
	 *	Creation du type personnalise Entite pour la gestion des elements necessitant une page dédiée
	 */
	function create_wpshop_entities_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, array(
			'labels' => array(
				'name'					=> __( 'Entities', 'wpshop' ),
				'singular_name' 		=> __( 'Entity', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new entity', 'wpshop' ),
				'add_new' 				=> __( 'Add new entity', 'wpshop' ),
				'add_new_item' 			=> __( 'Add new entity', 'wpshop' ),
				'edit_item' 			=> __( 'Edit entity', 'wpshop' ),
				'new_item' 				=> __( 'New entity', 'wpshop' ),
				'view_item' 			=> __( 'View entity', 'wpshop' ),
				'search_items' 			=> __( 'Search entities', 'wpshop' ),
				'not_found' 			=> __( 'No entities found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No entities found in Trash', 'wpshop' ),
				'parent_item_colon' 	=> '',
			),
			'supports' 				=> array( 'title', 'editor', 'page-attributes' ),
			'public' 				=> true,
			'has_archive'			=> true,
			'publicly_queryable' 	=> false,
			'show_in_nav_menus' 	=> false,
			'show_in_menu' 			=> true,
			'exclude_from_search'	=> true
		));
	}
	/**
	 * Ajout des box pour la gestion des entités
	 */
	function add_meta_boxes (){
		add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_support_section', __('Part to display', 'wpshop'), array('wpshop_entities', 'wpshop_entity_support_section'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'normal', 'high');
		add_meta_box(WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES . '_rewrite', __('Rewrite for entity', 'wpshop'), array('wpshop_entities', 'wpshop_entity_rewrite'), WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'normal', 'high');
	}

	/**
	 * Contenu de la boite permettant de gérer les différents éléments utilisable dans la page de l'entité
	 */
	function wpshop_entity_support_section ($post){
		$output = '';
		$support_list = unserialize(WPSHOP_REGISTER_POST_TYPE_SUPPORT);

		$current_entity_params = get_post_meta($post->ID, '_wpshop_entity_params', true);

		unset($input_def);$input_def=array();
		$input_def['type'] = 'checkbox';

		foreach($support_list as $support){
			$input_def['id'] = 'wpshop_entity_support';
			$input_def['name'] = $support;
			$input_def['possible_value'] = array($support);
			if(!empty($current_entity_params['support']) && in_array($support, $current_entity_params['support'])){
				$input_def['value'] = $support;
			}

			$output .= '<p>' . wpshop_form::check_input_type($input_def, 'wpshop_entity_support') . ' <label for="'.$input_def['id'].'_'.$support.'">' . __($support, 'wpshop') . '</label></p>';
		}

		echo $output;
	}

	/**
	 * Contenu de la boite permettant de gérer la réécirure pour l'entité
	 */
	function wpshop_entity_rewrite ($post){
		$output = '';

		$current_entity_params = get_post_meta($post->ID, '_wpshop_entity_params', true);

		unset($input_def);$input_def=array();
		$input_def['type'] = 'text';
		$input_def['id'] = 'wpshop_entity_rewrite';
		$input_def['name'] = 'wpshop_entity_rewrite[slug]';
		$input_def['value'] = (!empty($current_entity_params['rewrite']['slug']) ? $current_entity_params['rewrite']['slug'] :'');

		$output .= '<p><label for="'.$input_def['id'].'">' . __('Choose how this entity will be rewrite in front side. If you let it empty default will be taken', 'wpshop') . '</label><br/>' . wpshop_form::check_input_type($input_def) . '</p>';

		echo $output;
	}
	/**
	 * Enregistrement des options de l'entité
	 */
	function save_custom_informations() {
		$post_id = !empty($_POST['post_ID']) ? intval( wpshop_tools::varSanitizer($_POST['post_ID']) ) : null;
		$post_support = !empty($_POST['wpshop_entity_support']) ? $_POST['wpshop_entity_support'] : null;
		$wpshop_entity_rewrite = !empty($_POST['wpshop_entity_rewrite']) ? $_POST['wpshop_entity_rewrite'] : null;

		if ( get_post_type($post_id) == WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES ) {
			update_post_meta($post_id, '_wpshop_entity_params', array('support' => $post_support, 'rewrite' => $wpshop_entity_rewrite));
			flush_rewrite_rules();
		}
	}


	/**
	 * Ajout des différents menus correspondant aux entités créées
	 */
	function create_wpshop_entities_custom_type() {
		/*	Récupération des entités créées	*/
		$entities = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
			'post_status'	=> 'publish'
		));

		/*	Lecture des entités créées et enregistrement	*/
		if (!empty($entities)) {
			foreach ( $entities as $entity ) {
				if ( $entity->post_name != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
					$current_entity_params = get_post_meta($entity->ID, '_wpshop_entity_params', true);

					register_post_type($entity->post_name, array(
						'labels' => array(
							'name'					=> __( $entity->post_title , 'wpshop' ),
							'singular_name' 		=> __( $entity->post_title, 'wpshop' ),
							'add_new_item' 			=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'add_new' 				=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'add_new_item' 			=> sprintf( __( 'Add new %s', 'wpshop' ), $entity->post_title),
							'edit_item' 			=> sprintf( __( 'Edit %s', 'wpshop' ), $entity->post_title),
							'new_item' 				=> sprintf( __( 'New %s', 'wpshop' ), $entity->post_title),
							'view_item' 			=> sprintf( __( 'View %s', 'wpshop' ), $entity->post_title),
							'search_items' 			=> sprintf( __( 'Search %s', 'wpshop' ), $entity->post_title),
							'not_found' 			=> sprintf( __( 'No %s found', 'wpshop' ), $entity->post_title),
							'not_found_in_trash' 	=> sprintf( __( 'No %s found in Trash', 'wpshop' ), $entity->post_title),
							'parent_item_colon' 	=> '',
						),
						'description' 			=> $entity->post_content,
						'supports' 				=> $current_entity_params['support'],
						'public' 				=> true,
						'has_archive'			=> true,
						'publicly_queryable' 	=> true,
						'show_in_nav_menus' 	=> true,
						'show_in_menu' 			=> 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
						'exclude_from_search'	=> false,
						'rewrite'				=> $current_entity_params['rewrite']
					));

					/*	Ajout des metabox	*/
					add_action('add_meta_boxes', array('wpshop_entities', 'add_meta_boxes_to_custom_types'));
					/*	Sauvgarde des informations personnalisées	*/
					add_action('save_post', array('wpshop_entities', 'save_entities_custom_informations'));
				}
			}
			/*	Reset de la liste des résultats pour éviter les comportements indésirables	*/
			wp_reset_query();
		}
	}

	/**
	 * Ajoute les metabox pour les types personnalisés
	 */
	function add_meta_boxes_to_custom_types() {
		global $post;

		/*	Les produits sont gérés séparément	*/
		if ( $post->post_type != WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {

			/*	Récupération de la liste des groupes d'attributs affectés à l'entité courante	*/
			$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code($post->post_type));

			/*	Vérification de l'existence ou non d'un groupe d'attribut déjà enregistré pour l'entité courante	*/
			$attribute_set_id = get_post_meta($post->ID, sprintf(WPSHOP_ATTRIBUTE_SET_ID_META_KEY, $post->post_type), true);

			if(((count($attribute_set_list) == 1) || ((count($attribute_set_list) > 1) && !empty($attribute_set_id)))){
				if((count($attribute_set_list) == 1) || empty($attribute_set_id)){
					$attribute_set_id = $attribute_set_list[0]->id;
				}

				/*	Récupération de la liste complète des attributs associés à l'entité courante	*/
				$currentTabContent = wpshop_attributes::getAttributeFieldOutput($attribute_set_id, $post->post_type, $post->ID);

				$fixed_box_exist = false;
				/*	Lecture de la liste des sous groupes et des attributs pour la création des metaboxes	*/
				if ( !empty($currentTabContent['box']) && is_array($currentTabContent['box']) ) {
					foreach ($currentTabContent['box'] as $boxIdentifier => $boxTitle) {
						if (!empty($currentTabContent['box'][$boxIdentifier.'_backend_display_type']) &&( $currentTabContent['box'][$boxIdentifier.'_backend_display_type']=='movable-tab')) {
							add_meta_box($post->post_type . '_' . $boxIdentifier, __($boxTitle, 'wpshop'), array('wpshop_entities', 'meta_box_content'), $post->post_type, 'normal', 'default', array('currentTabContent' => $currentTabContent['boxContent']));
						}
						else $fixed_box_exist = true;
					}
				}
				if($fixed_box_exist) {
					add_meta_box($post->post_type . '_fixed_tab', __('Informations', 'wpshop'), array('wpshop_entities', 'meta_box_content_datas'), $post->post_type, 'normal', 'high', array('currentTabContent' => $currentTabContent));
				}
			}
			elseif (count($attribute_set_list) > 1) {
				$input_def['id'] = $post->post_type.'_attribute_set_id';
				$input_def['name'] = $post->post_type.'_attribute_set_id';
				$input_def['value'] = '';
				$input_def['type'] = 'select';
				$input_def['possible_value'] = $attribute_set_list;

				$input_def['value'] = '';
				foreach ($attribute_set_list as $set) {
					if( $set->default_set == 'yes' ) {
						$input_def['value'] = $set->id;
					}
				}

				$currentTabContent = '
		<ul class="attribute_set_selector" >
			<li class="attribute_set_selector_title_select" ><label for="title" >' . sprintf(__('Choose a title for the %s', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</label></li>
			<li class="attribute_set_selector_group_selector" ><label for="' . $input_def['id'] . '" >' . sprintf(__('Choose an attribute group for this %s', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</label>&nbsp;'.wpshop_form::check_input_type($input_def).'</li>
			<li class="attribute_set_selector_save_instruction" >' . sprintf(__('Save the %s with the "Save draft" button on the right side', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))) . '</li>
			<li class="attribute_set_selector_after_save_instruction" >' . __('Once the group chosen, the different attribute will be displayed here', 'wpshop') . '</li>
		</ul>';

				add_meta_box($post->post_type . '_attribute_set_selector',sprintf( __('%s attributes', 'wpshop'), get_the_title(wpshop_entities::get_entity_identifier_from_code($post->post_type))), array('wpshop_entities', 'meta_box_content'), $post->post_type, 'normal', 'high', array('currentTabContent' => $currentTabContent));
			}

		}
	}

	/**
	 * Définition du contenu des "metaboxes flottantes" pour les entités
	 *
	 * @param object $post La définition de l'entité en cours d'édition
	 * @param array $metaboxArgs La liste de paramètres passés à travers la fonction add_meta_box
	 */
	function meta_box_content($post, $metaboxArgs) {
		/*	Add the extra fields defined by the default attribute group in the general section	*/
		echo '<div class="wpshop_extra_field_container" >' . $metaboxArgs['args']['currentTabContent'] . '</div>';
	}
	/**
	 * Définition du contenu de la "metabox" contenant les onglets correspondant aux différents groupes et sous-groupes d'attributs
	 *
	 * @param object $post La définition de l'entité en cours d'édition
	 * @param array $metaboxArgs La liste de paramètres passés à travers la fonction add_meta_box
	 */
	function meta_box_content_datas($post, $metaboxArgs) {

		$currentTabContent = $metaboxArgs['args']['currentTabContent'];

		echo '<div id="fixed-tabs" class="wpshop_tabs wpshop_detail_tabs entities_attribute_tabs ' . $post->post_type . '_attribute_tabs" >
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

		if (!empty($currentTabContent['boxMore'])) {
			echo $currentTabContent['boxMore'];
		}
		echo '</div>';
	}

	/**
	 * Enregistrement des informations concernant l'entité en cours d'édition
	 */
	function save_entities_custom_informations() {
		global $wpdb;
		$post_id = !empty($_REQUEST['post_ID']) ? intval( wpshop_tools::varSanitizer($_REQUEST['post_ID']) ) : null;

		if ( !empty($post_id) ) {
			$current_post_type = get_post_type($post_id);

			/*	Vérification de l'existence de l'envoi de l'identifiant du set d'attribut	*/
			if	( !empty($_REQUEST[$current_post_type . '_attribute_set_id']) ) {
				$attribute_set_id = intval( wpshop_tools::varSanitizer($_REQUEST[$current_post_type . '_attribute_set_id']) );
				$attribet_set_infos = wpshop_attributes_set::getElement($attribute_set_id, "'valid'", 'id');

				if ( $attribet_set_infos->entity == $_REQUEST['post_type'] ) {
					/*	Enregistrement de l'identifiant du set d'attribut associé à l'entité	*/
					update_post_meta($post_id, sprintf(WPSHOP_ATTRIBUTE_SET_ID_META_KEY, $current_post_type), $attribute_set_id);

					/*	Enregistrement de tous les attributs	*/
					if ( !empty($_REQUEST[$current_post_type . '_attribute']) ) {
						/*	Traduction des virgule en point pour la base de donnees	*/
						if ( !empty($_REQUEST[$current_post_type . '_attribute']['decimal']) ) {
							foreach($_REQUEST[$current_post_type . '_attribute']['decimal'] as $attributeName => $attributeValue){
								if(!is_array($attributeValue)){
									$_REQUEST[$current_post_type . '_attribute']['decimal'][$attributeName] = str_replace(',','.',$_REQUEST[$current_post_type . '_attribute']['decimal'][$attributeName]);
								}
							}
						}

						/*	Enregistrement des valeurs des différents attributs	*/
						wpshop_attributes::saveAttributeForEntity($_REQUEST[$current_post_type . '_attribute'], wpshop_entities::get_entity_identifier_from_code($current_post_type), $post_id, get_locale());

						/*	Enregistrement des valeurs des attributs dans les metas de l'entité => Permet de profiter de la recherche native de wordpress	*/
						$productMetaDatas = array();
						foreach($_REQUEST[$current_post_type . '_attribute'] as $attributeType => $attributeValues){
							foreach($attributeValues as $attributeCode => $attributeValue){
								$productMetaDatas[$attributeCode] = $attributeValue;
							}
						}
						update_post_meta($_REQUEST['post_ID'], WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
					}
				}
			}
		}

		flush_rewrite_rules();
	}

	/**
	 * Récupération de la liste des entités existantes
	 *
	 * @return array La liste des entités existantes
	 */
	function get_entity() {
		$entities_list = array();
		$entities = query_posts(array(
			'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES,
			'post_status' 	=> 'publish',
			'orderby'		=> 'menu_order',
			'order'			=> 'ASC'
		));

		if ( !empty($entities) ) {
			foreach ($entities as $entity_index => $entity) {
				$entities_list[$entity->ID] = $entity->post_title;
			}
		}
		wp_reset_query();

		return $entities_list;
	}

	/**
	 * Récupération de l'identifiant d'une entité
	 *
	 * @param string $code
	 * @return integer L'identifiant de l'entité dont on veut récupérer les informations
	 */
	function get_entity_identifier_from_code($code) {
		global $wpdb;
		$entity_id = null;

		$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type=%s AND post_status=%s AND post_name=%s ORDER BY menu_order ASC", WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, 'publish', $code);
		$entity_id = $wpdb->get_var($query);

		return $entity_id;
	}

	/**
	 * Duplicate an element of wpshop_entity type
	 *
	 * @param integer $post_id
	 */
	function duplicate_entity_element($post_id) {

		/*	Get current post information	*/
		$post_infos = get_post( $post_id, ARRAY_A );

		/*	Set new information for post that will be created	*/
		unset($post_infos['ID']);
		$post_infos['post_date'] = current_time('mysql', 1);
		$post_infos['post_date_gmt'] = current_time('mysql', 1);
		$post_infos['post_modified'] = current_time('mysql', 1);
		$post_infos['post_modified_gmt'] = current_time('mysql', 1);
		$post_infos['post_status'] = 'draft';
		$post_infos['post_title'] = $post_infos['post_title'] . ' - ' . sprintf(__('Copy of %s', 'wpshop'), $post_id);
		$post_infos['guid'] = NULL;

		/*	Insert the new post	*/
		$last_post = wp_insert_post($post_infos);

		/*	If there is no error then duplicate meta informations	*/
		if ( is_int($last_post) && !empty($last_post) ) {
			$meta_creation = true;
			$current_post_meta = get_post_meta($post_id);
			foreach ( $current_post_meta as $post_meta_key => $post_meta_value ) {
				$meta_is_array = unserialize($post_meta_value[0]);
				$meta_real_value = (is_array($meta_is_array) ? $meta_is_array : $post_meta_value[0]);
				$meta_creation = update_post_meta($last_post, $post_meta_key, $meta_real_value);
			}

			/*	Duplicate element taxonomy	*/
			/*	Check the taxonomy to get	*/
			switch ( get_post_type($post_id) ) {
				case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
					$taxonomy = WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES;
					break;
				default:
					$taxonomy = '';
					break;
			}
			$post_taxonomies = wp_get_post_terms( $post_id,  $taxonomy);
			foreach ( $post_taxonomies as $post_taxonomy ) {
				wp_set_post_terms( $last_post, $post_taxonomy->term_id, $taxonomy, true);
			}

			/*	Create a post meta allowing to know if the element has been duplicated from another	*/
			update_post_meta($last_post, '_wpshop_duplicate_element', $post_id);

			$new_element_link = '<a class="clear wpshop_duplicate_entity_element_link" href="' . admin_url('post.php?post=' . $last_post . '&action=edit') . '" >' . __('Go on the new element edit page', 'wpshop') . '</a>';
			if ( $meta_creation ) {
				return array('true', '<br/>' . $new_element_link);
			}
			else {
				return array('true', '<br/>' . __('Some errors occured while duplicating meta information, but element has been created.', 'wpshop') . ' ' . $new_element_link);
			}
		}

		return array('false', __('An error occured while duplicating element', 'wpshop'));
	}

	/**
	 * Create an entity of customer type when a new user is created
	 *
	 * @param integer $user_id
	 */
	function create_entity_customer_when_user_is_created($user_id){
		$user_info = get_userdata($user_id);

		wp_insert_post(array('post_type'=>WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'post_author' => $user_id, 'post_title'=>$user_info->user_nicename));
	}


	/**
	 * Define custom columns header display in post_type page
	 *
	 * @param string $columns The default column for the post_type given in second parameter
	 * @param string $post_type The post type of the currentpage
	 *
	 * @return array The new columns to display for the post_type given in second parameter
	 */
	function custom_columns_header($columns, $post_type) {
		global $wpdb;

		/*	Get the attribute list to display as custom column for the current entity	*/
		$query = $wpdb->prepare("SELECT code, frontend_label FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT WHERE status=%s AND is_used_in_admin_listing_column=%s AND entity_id=%d", 'valid', 'yes', self::get_entity_identifier_from_code($post_type));
		$attributes_list = $wpdb->get_results($query);
		$wpshop_custom_columns = array();
		foreach ( $attributes_list as $attribute ) {
			$wpshop_custom_columns[$attribute->code] = __($attribute->frontend_label, 'wpshop');
		}

		/*	Check the current entity to display custom column correctly. Add the custom column into default column list	*/
		switch ( $post_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$columns = array_merge(array(
					'cb' => '<input type="checkbox" />',
					'title' => __('Product name', 'wpshop')
				), $wpshop_custom_columns);

				$columns['author'] = __('Creator', 'wpshop');
				$columns['date'] = __('Date', 'wpshop');

				break;
		}

		return $columns;
	}

	/**
	 *
	 * @param unknown_type $column
	 * @param unknown_type $post_id
	 */
	function custom_columns_content($column, $post_id) {
		$post_type = get_post_type($post_id);

		switch ( $post_type ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$column_content = '<strong>-</strong>';
				$product = wpshop_products::get_product_data($post_id);

				switch ($column) {
					case "product_stock":
						if( !empty($product['product_stock']) )
							$column_content = (int)$product['product_stock'].' '.__('unit(s)','wpshop');
						break;

					default:
						if ( !empty($product[$column]) ) {
							$attribute_prices = unserialize(WPSHOP_ATTRIBUTE_PRICES);
							if ( in_array($column, $attribute_prices) ) {
								$column_content = wpshop_tools::price($product[$column],2,'.', ' ').' '.wpshop_tools::wpshop_get_currency();
							}
							else
								$column_content = $product[$column];
						}
						break;
				}
				break;
			default:
				$column_content = '';
				break;
		}

		echo $column_content;
	}

	/**
	 * Define the different field available for bulk edition for entities. Attributes to display are defined by checking box in attribute option
	 *
	 * @param string $column_name The column name for output type definition
	 * @param string $post_type The current
	 *
	 */
	function quick_edit( $column_name, $entity ) {

		switch ( $entity ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$attribute_def = wpshop_attributes::getElement($column_name, "'valid'", 'code');
?>
<div class="wpshop_bulk_and_quick_edit_column_container wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_container">
	<span class="wpshop_bulk_and_quick_edit_column_label wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_label"><?php _e($attribute_def->frontend_label, 'wpshop'); ?></span>
	<input class="wpshop_bulk_and_quick_edit_input wpshop_bulk_and_quick_edit_input_data_type_<?php echo $attribute_def->data_type; ?> wpshop_bulk_and_quick_edit_input_data_code_<?php echo $attribute_def->code; ?>" type="text" name="<?php echo $entity; ?>_attribute[<?php echo $attribute_def->data_type; ?>][<?php echo $attribute_def->code; ?>]" value="" /><input type="hidden" name="<?php echo $entity; ?>_provenance[]" value="bulk" />
</div>
<?php
			break;
		}

	}
	/**
	 * Define the different field available for bulk edition for entities. Attributes to display are defined by checking box in attribute option
	 *
	 * @param string $column_name The column name for output type definition
	 * @param string $post_type The current
	 *
	 */
	function bulk_edit( $column_name, $entity ) {

		switch ( $entity ) {
			case WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT:
				$attribute_def = wpshop_attributes::getElement($column_name, "'valid'", 'code');
?>
<div class="wpshop_bulk_and_quick_edit_column_container wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_container">
	<span class="wpshop_bulk_and_quick_edit_column_label wpshop_bulk_and_quick_edit_column_<?php echo $column_name; ?>_label"><?php _e($attribute_def->frontend_label, 'wpshop'); ?></span>
	<input class="wpshop_bulk_and_quick_edit_input wpshop_bulk_and_quick_edit_input_data_type_<?php echo $attribute_def->data_type; ?> wpshop_bulk_and_quick_edit_input_data_code_<?php echo $attribute_def->code; ?>" type="text" name="<?php echo $entity; ?>_-code-_<?php echo $attribute_def->code; ?>" value="" />
</div>
<?php
			break;
		}

	}
}

?>