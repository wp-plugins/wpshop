<?php
/**
 * Main controller file for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */

/**
 * Main controller class for product mass modification module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 */
class wps_product_mass_interface_ctr {

	/**
	 * Instanciate the module: declare scripts, styles, hook wordpress
	 */
	function __construct() {
		/**	Declare styles and scripts	*/
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_css' ) );
		add_action( 'admin_print_scripts', array( &$this, 'admin_printed_js' ) );

		/**	Hook wordpress admin footer in order to add the mass update/creation interface	*/
		add_action( 'in_admin_footer', array( $this, 'add_mass_update_button' ) );

		/**	Trigger ajax action	*/
		add_action( 'wp_ajax_wps_add_quick_interface', array($this, 'wps_add_quick_interface' ) );
		add_action( 'wp_ajax_wps_mass_interface_new_product_creation', array($this, 'wps_mass_interface_new_product_creation' ) );

		add_action( 'wp_ajax_wps_save_product_quick_interface', array( $this, 'wps_save_product_quick_interface' ) );
	}

	/**
	 * Define the aministration pat styles
	 */
	function admin_css() {
		wp_enqueue_style( 'wps-mass-product-update', WPS_PDCT_MASS_URL.'/assets/css/backend.css', '', WPS_PDCT_MASS_VERSION);
	}

	/**
	 * Add javascript to administration
	 */
	function add_admin_scripts() {
		wp_enqueue_script( 'admin_product_js', WPS_PDCT_MASS_URL.'/assets/js/backend.js', '', WPS_PDCT_MASS_VERSION, true);
	}

	/**
	 * Print javascript (dynamic js content) instruction into html code head.
	 */
	function admin_printed_js() {
		require_once( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_PATH . '/assets/', 'js', "header.js" ) );
	}

	/**
	 * WORDPRESS HOOK - Add a button in footer of product listing in order to allow user to edit/add product with a mass interface
	 */
	function add_mass_update_button() {
		/**	Get current screen */
		$screen = get_current_screen();

		/**	Check if we are on product edition page. Add the mass interface only on this page	*/
		if( 'edit-wpshop_product' === $screen->id ) {
			require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "massinterface", "button" ) );
		}
	}



	/**
	 * Create an array with all attributes used
	 *
	 * @param array $attribute_list The list of attributes associated to the product
	 *
	 * @return array
	 */
	function check_attribute_to_display_for_quick_add( $attribute_list ) {
		$quick_add_form_attributes = array();

		if ( !empty( $attribute_list ) ) {
			foreach( $attribute_list as $attributes_group ) {
				foreach( $attributes_group as $attributes_sections ) {
					if( !empty($attributes_sections) && !empty($attributes_sections['attributes']) ) {
						foreach( $attributes_sections['attributes'] as $attribute_id => $att_def ) {
							if( !empty($att_def) && !empty($att_def['is_used_in_quick_add_form']) && $att_def['is_used_in_quick_add_form'] == 'yes' ) {
								$quick_add_form_attributes[ $attribute_id ] = $att_def;
							}
						}
					}
				}
			}
		}

		return $quick_add_form_attributes;
	}

	/**
	 * Display pagination
	 *
	 * @return string
	 */
	function get_products_pagination( $current_page ) {
		global $wpdb;
		$user_id = get_current_user_id();

		/**	Define the element number per page. If the user change the default value, take this value	*/
		$one_page_limit = get_user_meta( $user_id, 'edit_wpshop_product_per_page', true );
		$one_page_limit = ( !empty($one_page_limit) ) ? $one_page_limit : 20;

		/**	Count the number of product existing in the shop	*/
		$query = $wpdb->prepare( "SELECT COUNT( * ) AS products_number FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ( 'publish', 'draft' )", WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
		$products = $wpdb->get_var( $query );

		if( !empty($products) ) {
			$args = array(
					'base' => '%_%',
					'format' => admin_url( 'admin-ajax.php?action=wps_add_quick_interface&page=%#%' ),
					'current' => ( $current_page + 1 ),
					'total' => ceil( $products / $one_page_limit ),
					'type' => 'array',
					'prev_next' => false,
					'show_all' => true,
			);
			$paginate = paginate_links( $args );

			$wps_product_ctr = new wps_product_ctr();
			ob_start();
			require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface_pagination" ) );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}



	/**
	 * AJAX - Display product to edit
	 */
	function wps_add_quick_interface() {
		global $wpdb;
		$entity_class = new wpshop_entities();
		$product_entity_id = $entity_class->get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT );

		$page = !empty( $_GET[ 'page' ] ) && is_int( (int)$_GET[ 'page' ] ) ? (int)($_GET[ 'page' ] - 1) : 0;
		$user_id = get_current_user_id();
		$product_limit = get_user_meta( $user_id, 'edit_wpshop_product_per_page', true );
		$product_limit = ( !empty($product_limit) ) ? $product_limit : 20;

		$wps_product_mass_interface_mdl = new wps_product_mass_interface_mdl();
		$products = $wps_product_mass_interface_mdl->get_quick_interface_products( $page, $product_limit );
		$quick_add_form_attributes = array();
		// Construct Table Head Data
		if( !empty($products) ) {
			foreach( $products as $product ) {
				if( !empty($product) && !empty( $product['attributes_datas'] ) ) {
					$quick_add_form_attributes = $this->check_attribute_to_display_for_quick_add( $product['attributes_datas'] );
				}
			}
		}
		$pagination = $this->get_products_pagination( $page );
		$auto_check = false;

		ob_start();
		require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface" ) );
		$output = ob_get_contents();
		ob_end_clean();

		wp_die( $output );
	}

	/**
	 * AJAX - Create a draft product and display the line allowing to edit informations for this product
	 */
	function wps_mass_interface_new_product_creation() {
		global $wpdb;
		$output = '';

		$new_product_id = wp_insert_post( array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
			'post_status' => 'draft',
			'post_title' => __( 'New product', 'wps-product-mass-interface-i18n' ),
		) );
		if ( !empty( $new_product_id ) ) {
			$product_attribute_set_id = 1;
			update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $product_attribute_set_id );
			$wps_product_mass_interface_mdl = new wps_product_mass_interface_mdl();
			$product[ 'post_datas' ] = get_post( $new_product_id );
			$product[ 'attributes_datas' ] = $wps_product_mass_interface_mdl->get_product_atts_def( $new_product_id );
			$quick_add_form_attributes = $this->check_attribute_to_display_for_quick_add( $product['attributes_datas'] );
			$auto_check = true;
			require( wpshop_tools::get_template_part( WPS_PDCT_MASS_DIR, WPS_PDCT_MASS_TEMPLATES_MAIN_DIR, "backend", "quick_add_interface", "product_line" ) );
		}

		wp_die( $output );
	}

	/**
	 * AJAX - Save datas
	 */
	function wps_save_product_quick_interface() {
		global $wpdb;
		$status = true;
		$response = __( 'Selected products have been successfully saved', 'wps-product-mass-interface-i18n' );
		$message_class = 'wpshop-msg-success';
		$total_nb_of_product = $saved_product = 0;

		if( !empty( $_POST['wps_product_quick_save'] ) ) {
			$total_nb_of_product = count( $_POST['wps_product_quick_save'] );
			foreach ( $_POST['wps_product_quick_save'] as $product_id ) {
				$datas_to_save = array();

				if ( !empty( $_POST ) && !empty( $_POST[ 'wps_mass_interface' ] ) && array_key_exists( $product_id , $_POST[ 'wps_mass_interface' ] ) && !empty( $_POST[ 'wps_mass_interface' ][ $product_id ][ $wpdb->posts ] ) ) {
					$updated_post = wp_update_post( wp_parse_args( $_POST[ 'wps_mass_interface' ][ $product_id ][ $wpdb->posts ], array( 'ID' => $product_id ) ) );

					if ( !empty( $updated_post ) ) {
						$datas_to_save['post_ID'] = intval( $product_id );
						$datas_to_save['wpshop_product_attribute'] = ( !empty($_POST['wpshop_product_attribute'][ $product_id ]) ) ? $_POST['wpshop_product_attribute'][ $product_id ] : array();
						$datas_to_save['user_ID'] = $datas_to_save['post_author'] = get_current_user_id();
						$datas_to_save['action'] = 'editpost';
						if( !empty($product_id) && !empty( $datas_to_save['user_ID'] ) ) {
							$product_class = new wpshop_products();
							$product_class->save_product_custom_informations( $product_id, $datas_to_save );

							$saved_product++;
						}
					}
				}
			}
		}

		if ( $total_nb_of_product != $saved_product ) {
			$status = true;
			$message_class = 'wpshop-msg-danger';
			$response = sprintf( __( 'You try to update %1$d products but only %2$d of them have been successfully saved', 'wps-product-mass-interface-i18n' ), $total_nb_of_product, $saved_product );
		}

		wp_die( '<div class="wpshop-msg ' . $message_class . '" >' . $response . '</div>' );
	}

}

?>