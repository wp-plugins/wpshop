<?php
/**
 * Fichier du controlleur principal de l'extension de caisse pour WP-Shop / Main controller file for point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 2.0
 */

/**
 * Classe du controlleur principal de l'extension de caisse pour WP-Shop / Main controller class for point of sale management plugin
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_pos_addon {

	/**
	 * Instanciation des différents élement de l'extension / Call the different element to instanciate the addon
	 */
	function __construct() {
		/**	Declaration des sessions / Call session utilities on init	*/
		add_action( 'init', array( $this, 'wps_pos_addon_session' ) );
		add_action( 'init', array( $this, 'wps_pos_option_db' ) );

		/**	Instanciation des différents composants du logiciel de caisse / Instanciate the different component for POS addon	*/
		$this->wps_pos_customer = new wps_pos_addon_customer();
		$this->wps_pos_product = new wps_pos_addon_product();
		$this->wps_pos_order = new wps_pos_addon_order();

		/**	Appel des scripts et styles pour le logiciel de caisse / Include styles and scripts for backend	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_assets' ) );
		add_action( 'admin_print_scripts', array( &$this, 'print_js') );

		/**	Appel du point d'accroche de création de menu dans l'administration et redéfinition de l'ordre des menus / Define the administration menu with some arrangements for displaying the created menu under wpshop menu	*/
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'menu_order', array( $this, 'admin_menu_order' ), 11 );
		add_action( 'custom_menu_order', array( $this, 'admin_custom_menu_order' ) );

		/**	AJAX Definition	*/
		/**	Recharge la liste d'un élément donné ( client ou produit ) pour une lettre donnée / Load element list corresponding to a given letter	*/
		add_action( 'wp_ajax_wpspos_load_element_from_letter', array( $this, 'ajax_load_element_from_letter' ) );
		add_action( 'wp_ajax_wpspos_save_config_barcode_only', array( $this, 'ajax_save_config_barcode_only' ) );
	}

	/**
	 * Déclaration des scripts et styles pour le logiciel de caisse / Enqueue scripts and styles for POS addon
	 *
	 * @uses wp_register_style
	 * @uses wp_enqueue_style
	 * @uses wp_enqueue_script
	 */
	function admin_assets() {
		global $wps_pos_addon_menu;
		$screen = get_current_screen();

		if ( $screen->id == $wps_pos_addon_menu ) {
			wp_register_style( 'wpspos-common-styles', WPSPOS_URL . 'assets/css/backend.css', '', WPSPOS_VERSION );
			wp_enqueue_style( 'wpspos-common-styles' );

			wp_enqueue_script('wpspos-backend-js',  WPSPOS_URL . 'assets/js/backend.js', '', WPSPOS_VERSION);
			wp_enqueue_script('wpshop_jquery_chosen',  WPSHOP_JS_URL . 'jquery-libs/chosen.jquery.min.js', '', WPSHOP_VERSION);
		}
	}

	/**
	 * Define scripts that have to be printed
	 */
	function print_js() {
		global $wps_pos_addon_menu;
		$screen = get_current_screen();

		if ( $screen->id == $wps_pos_addon_menu ) {
			require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_PATH . 'assets/', 'js', 'header.js' ) );
		}
	}

	/**
	 * Ajout du menu pour le logiciel de caisse dans le backend / Create a new administration menu into backend
	 */
	function admin_menu() {
		global $wps_pos_addon_menu;

		$wps_pos_addon_menu = add_menu_page( __( 'WP-Shop point of sale interface', 'wps-pos-i18n' ), __( 'WP-Shop POS', 'wps-pos-i18n' ), 'manage_options', 'wps-pos', array( $this, 'display_pos' ), 'dashicons-store' );
	}

	/**
	 * WP HOOK - Reorder the admin menu for placing POS addon just below shop menu
	 *
	 * @param array $current_menu_order The current defined menu order we want to change
	 *
	 * @return array The new admin menu order with the POS addon placed
	 */
	function admin_menu_order( $current_menu_order ) {
		/**	Create a new menu order	*/
		$wps_pos_menu_ordered = array();

		/**	Read the current existing menu order for rearrange it	*/
		foreach ( $current_menu_order as $menu_item ) {
			if ( 'edit.php?post_type=wpshop_shop_order' == $menu_item ) {
				$wps_pos_menu_ordered[] = 'wps-pos';
				$wps_pos_menu_ordered[] = 'edit.php?post_type=wpshop_shop_order';

				unset( $current_menu_order[ array_search( 'wps-pos', $current_menu_order ) ] );
			}
			else if ( 'wps-pos' != $menu_item ) {
				$wps_pos_menu_ordered[] = $menu_item;
			}
		}

		return $wps_pos_menu_ordered;
	}

	/**
	 * WP HOOK - Define the capability to have to change admin menu order
	 *
	 * @return boolean A boolean var defining if we apply admin menu reorder for current user
	 */
	function admin_custom_menu_order() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Declare session for pos administration
	 */
	function wps_pos_addon_session() {
		@session_start();
		
		if ( !empty( $_GET ) && !empty( $_GET[ 'new_order' ] ) && ( 'yes' == $_GET[ 'new_order' ] ) ) {
			session_destroy();
			unset( $_SESSION[ 'cart' ] );
			unset( $_SESSION[ 'wps-pos-addon' ] );

			wp_safe_redirect( admin_url( 'admin.php?page=wps-pos' ) );
		}
		
		if( empty( $_GET[ 'page' ] ) || ( 'wps-pos' != $_GET[ 'page' ] ) ) {
			unset( $_SESSION[ 'wps-pos-addon' ] );
		}
		
	}
	
	/**
	 * Add or update options in DB
	 */
	function wps_pos_option_db() {
		$option = 'wps_pos_options';
		$options = get_option( $option, false );
		if( $options === false ) {
			$values = array(
				'only_barcode' => 'checked',
				);
			add_option( $option, $values );
		} else {
			/**
			 * If want to treat options case by case */
			/*
			foreach( $options as $option ) {
				
			}
			*/
		}
	}

	/**
	 * Effectue des actions à l'activation du logiciel de caisse / Do some defautl action on POS addon activation
	 */
	public static function action_to_do_on_activation() {
		global $wpdb;

		/** Activate Barcode attribute **/
		if ( false ) {
			$wpdb->update( WPSHOP_DBT_ATTRIBUTE, array( 'status' => 'valid' ), array( 'code' => 'barcode' ) );

			/** Get the product entity id **/
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_name = %s', WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			$product_entity_id = $wpdb->get_var( $query );

			/** Check the barcode attribute id **/
			$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', 'barcode');
			$attribute_barcode_id = $wpdb->get_var( $query );

			$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $product_entity_id );
			$products_groups = $wpdb->get_results( $query );
			/** For each attributes groups used for product configuration **/
			foreach( $products_groups as $products_group ) {
				$query = $wpdb->prepare( 'SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE attribute_set_id = %d AND code = %s', $products_group->id, 'general');
				$attributes_set_sections = $wpdb->get_results( $query );
				foreach( $attributes_set_sections as $attributes_set_section ) {
					$query = $wpdb->prepare( 'SELECT MAX(position) AS max_position FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d', $product_entity_id, $products_group->id, $attributes_set_section->id);
					$max_position = $wpdb->get_var( $query );

					$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d AND attribute_id = %d', $product_entity_id, $products_group->id, $attributes_set_section->id, $attribute_barcode_id);
					$exist_barcode_details_definition = $wpdb->get_results( $query );
					/** Insert the barcode attribute details **/
					if ( !empty ($max_position) && empty($exist_barcode_details_definition) ) {
						$wpdb->insert( WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $product_entity_id, 'attribute_set_id' => $products_group->id, 'attribute_group_id' => $attributes_set_section->id, 'attribute_id' => $attribute_barcode_id, 'position' => (int)$max_position +1) );
					}
				}
			}
		}

		/** Activate Barcode for search **/
		$wpdb->update( WPSHOP_DBT_ATTRIBUTE, array('is_used_for_sort_by' => 'yes', 'is_used_in_quick_add_form' => 'yes'), array('code' => 'barcode') );

		/** Activate attribute for the product quick add form **/
		$price_piloting_option = get_option( 'wpshop_shop_price_piloting' );
		$code = ( !empty($price_piloting_option) && $price_piloting_option == 'HT' ) ? 'price_ht' : 'product_price';
		$wpdb->update( WPSHOP_DBT_ATTRIBUTE, array( 'is_used_in_quick_add_form' => 'yes'), array('code' => $code)  );
		$wpdb->update( WPSHOP_DBT_ATTRIBUTE, array( 'is_used_in_quick_add_form' => 'yes'), array('code' => 'tx_tva' )  );

		/** Check If Shop Customer attribute set exist - Deprecated **/
		/* $customer_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS );
		$query = $wpdb->prepare( 'SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE name = %s',  __('Shop Customer', 'wps-pos-i18n') );
		$exist_attribute_group = $wpdb->get_var( $query ); */
		
		$exist_attribute_group = 'Nope';

		if ( empty($exist_attribute_group) ) {
			/** Create a Attributes Group for POS Customer infos **/
			$wpdb->insert(
				WPSHOP_DBT_ATTRIBUTE_SET,
				array(
					'status' => 'valid',
					'creation_date' => current_time('mysql', 0),
					'last_update_date' => current_time('mysql', 0),
					'entity_id' => $customer_entity_id,
					'name' => __('Shop Customer', 'wps-pos-i18n')
				)
			);
			$attribute_set_id = $wpdb->insert_id;
			/** Create Attributes Group **/
			$wpdb->insert(
				WPSHOP_DBT_ATTRIBUTE_GROUP,
				array(
					'status' => 'valid',
					'default_group' => 'yes',
					'creation_date' => current_time('mysql', 0),
					'last_update_date' => current_time('mysql', 0),
					'attribute_set_id' => $attribute_set_id,
					'code' => sanitize_title( __('Shop Customer main infos', 'wps-pos-i18n') ),
					'name' => __('Shop Customer main infos', 'wps-pos-i18n')
				)
			);
			$main_set_id = $wpdb->insert_id;
			$wpdb->insert(
				WPSHOP_DBT_ATTRIBUTE_GROUP,
				array(
					'status' => 'valid',
					'creation_date' => current_time('mysql', 0),
					'last_update_date' => current_time('mysql', 0),
					'attribute_set_id' => $attribute_set_id,
					'code' => sanitize_title( __('Shop Customer address infos', 'wps-pos-i18n') ),
					'name' => __('Shop Customer address infos', 'wps-pos-i18n')
				)
			);
			$address_set_id = $wpdb->insert_id;
			/** Affect Attributes **/
			$attributes = array( 'last_name' => $main_set_id, 'first_name' => $main_set_id, 'address_last_name' => $main_set_id, 'address_first_name' => $main_set_id, 'address_user_email' => $main_set_id, 'address' => $address_set_id, 'postcode' => $address_set_id, 'city' => $address_set_id, 'phone' => $address_set_id);
			$i = 1;
			foreach( $attributes as $attribute => $group_id) {
				$attribute_def = wpshop_attributes::getElement( $attribute, "'valid'", 'code');
				if ( !empty($attribute_def) ) {
					$wpdb->insert(
						WPSHOP_DBT_ATTRIBUTE_DETAILS,
						array(
							'status' => 'valid',
							'creation_date' => current_time('mysql', 0),
							'last_update_date' => current_time('mysql', 0),
							'entity_type_id' => $customer_entity_id,
							'attribute_set_id' =>  $attribute_set_id,
							'attribute_group_id' => $group_id,
							'attribute_id' => $attribute_def->id,
							'position' => $i
						)
					);
					$i++;
				}
			}
		}

		$user_name = 'default_customer';
		$user_id = username_exists( $user_name );
		if ( !$user_id ) {
			$random_password = wp_generate_password( $length = 12, $include_standard_special_chars=false );
			$user_id = wp_create_user( $user_name, $random_password, 'client_defaut@wpshop.fr' );

			if ( !empty($user_id) ) {
				update_user_meta($user_id, 'last_name', __('Default', 'wps-pos-i18n') );
				update_user_meta($user_id, 'first_name', __('Customer', 'wps-pos-i18n') );
				/** Add the default customer id in Option table **/
				update_option('wpshop_pos_addon_default_customer_id', $user_id);
			}
		}

		/** Get post of user id */
		$customers_associated_to_user = get_posts( array (
			'post_status' => 'draft',
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
			'author' => $user_id,
		) );
		$customer_id = !empty( $customers_associated_to_user ) && !empty( $customers_associated_to_user[ 0 ] ) && !empty( $customers_associated_to_user[ 0 ]->ID ) ? $customers_associated_to_user[ 0 ]->ID : null;

		$customer_entity_id = wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);

		if ( !empty( $customer_id ) ) {
			/** Insert attribute data */
			wpshop_attributes::saveAttributeForEntity( array(
				'varchar' => array(
					'last_name' => __('Default', 'wps-pos-i18n'),
					'first_name' => __('Customer', 'wps-pos-i18n'),
					'user_email' => 'client_defaut@wpshop.fr',
					'address_last_name' => __('Default', 'wps-pos-i18n'),
					'address_first_name' => __('Customer', 'wps-pos-i18n'),
					'address_user_email' => 'client_defaut@wpshop.fr',
					'address' => __('Default address'),
					'postcode' => '42420',
					'city' => __('Default city'),
					'phone' => '0000000042',
				),
			),  $customer_entity_id, $customer_id );
		}
	}

	/**
	 * CORE - Install all extra-modules in "Modules" folder
	 */
	function install_modules() {
		/**	Define the directory containing all exrta-modules for current plugin	*/
		$module_folder = WPSPOS_PATH . 'modules/';

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' ) {
					$child_folder_content = scandir( $module_folder . $folder );
					if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						include( $f );
					}
				}
			}
		}
	}

	/**
	 * DISPLAY - Display the POS main interface
	 */
	function display_pos() {
		/**	Define the current step for current order	*/
		$default_customer_id = get_option( 'wpshop_pos_addon_default_customer_id' );
		$default_user_exists = false;
		if ( !empty( $default_customer_id ) ) {
			$default_user = get_user_by( 'id', $default_customer_id );
			if ( false !== $default_user ) {
				$default_user_exists = true;
			}
		}

		if ( !empty( $default_user_exists ) ) {
			$current_step = 2;
			$_SESSION[ 'cart' ][ 'customer_id' ] = $default_customer_id;
		}
		else {
			$current_step = 0;
			$_SESSION[ 'cart' ][ 'customer_id' ] = null;
		}

		require_once( wpshop_tools::get_template_part( WPSPOS_DIR, WPSPOS_TEMPLATES_MAIN_DIR, 'backend', 'pos' ) );
	}

	/**
	 * AJAX - Load element list from choosen letter into alphabet list
	 */
	function ajax_load_element_from_letter() {
		$response = array(
			'status' => false,
			'output' => __('An error occured', 'wps-pos-i18n'),
		);

		$alphabet = unserialize( WPSPOS_ALPHABET_LETTERS );
		$letter = !empty( $_POST['term'] ) && in_array( $_POST['term'], $alphabet )	? $_POST['term'] : null;
		$element_type = !empty( $_POST['element_type'] ) ? wpshop_tools::varSanitizer( $_POST['element_type'] ) : 'customer';
		$response[ 'element_type' ] = $element_type;

		if ( !empty( $letter ) ) {
			$error_message = '';
			switch ( $element_type ) {
				case 'customer':
					$result = $this->wps_pos_customer->display_customer_list( $letter );
					break;
				case 'product':
					$wps_pos_product = new wps_pos_addon_product();
					$result = $this->wps_pos_product->get_product_table_by_alphabet( $letter );
					break;
			}

			if ( empty( $result ) ) {
				$response[ 'output' ] = sprintf( __( 'Nothing found in %s for letter %s', 'wps-pos-i18n' ), __( $element_type, 'wps-pos-i18n' ), $letter);
			}
			else {
				$response[ 'status' ] = true;
				$response[ 'output' ] = $result;
			}

		}
		else {
			$response[ 'output' ] = sprintf( __( 'THe requested term (%s) to search is invalid. Please check your request and try again', 'wps-pos-i18n' ), $_POST['term'] );
		}

		wp_die( json_encode( $response ) );
	}

	/**
	 * AJAX - Save state of checkbox 
	 */
	function ajax_save_config_barcode_only() {
		$option = 'wps_pos_options';
		$values = get_option( $option );
		if( !empty( $_POST['value_checkbox'] ) && $_POST['value_checkbox'] == 'checked' ) {
			$values['only_barcode'] = 'checked';
  			update_option( $option, $values );
		} elseif( !empty( $_POST['value_checkbox'] ) && $_POST['value_checkbox'] == 'unchecked' ) {
			$values['only_barcode'] = 'unchecked';
  			update_option( $option, $values );
		}
		wp_die();
	}
}

?>