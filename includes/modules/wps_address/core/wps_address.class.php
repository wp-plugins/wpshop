<?php
/**
 * File defining class for addresses initialisation
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 * @package Geolocalisation
 */

/** Check if the plugin WPS_LOCALISATION_VERSION is defined. If not defined script will be stopped here */
if ( !defined( 'WPS_LOCALISATION_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpeo_geoloc') );
}

/**
 * Addresses initialisation class
 *
 * @author Eoxia developpement team <dev@eoxia.com>
 * @version 1.0
 * @package Geolocalisation
 */
class wps_address {

	/**
	 * Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_ADDRESS_DIR;

	/**
	 * Initialise Addresses component management
	 */
	function __construct() {
		$this->template_dir = WPS_ADDRESS_PATH . WPS_ADDRESS_DIR . "/templates/";

		/**	Load	*/
		add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );

		/**	Add shortocde listener for addresses	*/
		add_shortcode( 'wps_address_list', array( &$this, 'get_addresses') );
		add_shortcode( 'wps_shipping_address_summary', array( &$this, 'get_shipping_address_summary') );
		add_shortcode( 'wps_billing_address_summary', array( &$this, 'get_billing_address_summary') );

		add_shortcode( 'wps_addresses', array( &$this, 'display_addresses_interface') );



		/**	Add listener for ajax actions	*/
		add_action( 'wp_ajax_wps_load_address_form', array( &$this, 'wps_load_address_form') );
		add_action( 'wp_ajax_wps_save_address', array( &$this, 'wps_save_address') );
		add_action( 'wp_ajax_wps_save_first_address', array( &$this, 'wps_save_first_address') );

		add_action( 'wp_ajax_wps-address-edition-form-load', array( &$this, 'load_address_edition_form' ) );
		add_action( 'wp_ajax_wps-address-display-an-address', array( &$this, 'display_address' ) );
		add_action( 'wp_ajax_wps-address-save-address', array( &$this, 'wps_save_address' ) );
		add_action( 'wp_ajax_wps-address-display-list', array( &$this, 'display_addresses_list' ) );
		add_action( 'wp_ajax_wps-address-add-new', array( &$this, 'display_address_adding_form' ) );

		add_action( 'wp_ajax_wps_delete_an_address', array( &$this, 'wps_delete_an_address' ) );

		add_action( 'wp_ajax_wps_reload_address_interface', array( &$this, 'wps_reload_address_interface' ) );



		add_action( 'wp_ajax_display_address_form', array( &$this, '') );

		/**	Call administration style definition	*/
		add_action( 'wp_enqueue_scripts', array( &$this, 'admin_css' ) );

		/*	Include the different javascript	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_js' ) );
		add_action( 'admin_print_scripts', array(&$this, 'admin_printed_js') );

		/*	Include the different javascript	*/
		add_action( 'wp_enqueue_scripts', array( &$this, 'common_js' ) );

		/**	Load plugin translation	*/
		load_plugin_textdomain( 'wpeo_geoloc', false, WPS_LOCALISATION_LANGUAGES_DIR );

		/**	Add addresses metaboxes to wordpress element	*/
		add_action('add_meta_boxes', array( &$this, 'addresses_metaboxes'), 1);

		/**	SHORTCODE listener	*/
		add_shortcode( 'wps_addresses_list', array( &$this, 'shortcode_display_addresses_list' ) );

		add_action( 'wp_ajax_wps-add-an-address-in-admin', array( $this, 'wps_add_an_address_in_admin' ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-form' );
	}

	/**
	 * Check in database if there are addresses associated to current post type
	 *
	 * @param string $post The current post type
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function addresses_metaboxes( $post ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = %s", $post, WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES );
		$parent = $wpdb->get_var( $query );
		if ( !empty( $parent ) ) {
			$address_meta_box_checking = get_post_meta( $parent, '_wpshop_entity_attached_address', true);
			if ( !empty($address_meta_box_checking) ) {
				add_meta_box( 'wps_attached_addresses', __('Attached addresses', 'wpshop'), array( &$this, 'addresses_metaboxes_content' ), $post, 'normal', 'default' );
			}
		}
	}

	/**
	 * Call the different element to display addresses into associated metaboxes into backend part
	 *
	 * @param object $post The current post definition
	 * @param array $args A list of parameters allowing to specify the element to display
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function addresses_metaboxes_content( $post, $args ) {
		$addresses = $this->get_addresses_list( $post->ID );
		require( WPS_LOCALISATION_BACKEND_TPL_DIR . 'address-metabox.php' );
	}

	/**
	 * Print javascript (dynamic js content) instruction into html code head.
	 */
	function admin_printed_js() {
		require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'js/header.js.php' );
	}

	/**
	 * Load the different javascript librairies
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function common_js() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wps_address_js',  WPS_LOCALISATION_FRONTEND_TPL_URL . 'js/wps_address.js' );
	}

	/**
	 * Load the different javascript librairies for backend
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function admin_js() {
		wp_enqueue_script('wps_address_backend_js', WPS_LOCALISATION_BACKEND_TPL_URL . 'js/backend.js', '', WPS_LOCALISATION_VERSION);
	}

	/**
	 * Load the different css librairies
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function admin_css() {
		wp_register_style('wps_address_backend_css', WPS_LOCALISATION_BACKEND_TPL_URL . 'css/backend.css', '', WPS_LOCALISATION_VERSION);
		wp_enqueue_style('wps_address_backend_css');
	}


	/** Load module/addon automatically to existing template list
	 *
	 * @param array $templates The current template definition
	 *
	 * @return array The template with new elements
	 */
	function custom_template_load( $templates ) {
		include( WPS_LOCALISATION_FRONTEND_TPL_DIR . 'main_elements.tpl.php');

		$wpshop_display = new wpshop_display();
		$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
		unset($tpl_element);

		return $templates;
	}


	/**
	 * Get Address Interface
	 * @return string
	 */
	function get_addresses() {
		global $wpdb;
		$output = '';
		$user_id = get_current_user_id();
		$addresses_list = array();
		$addresses = '';

		$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
		$billing_address_option = get_option( 'wpshop_billing_address' );
		$addresses_types_def = array( $shipping_address_option['choice'] => 'shipping_address', $billing_address_option['choice'] => 'billing_address');

		if ( !empty($user_id) ) {
			$addresses_list = self::get_addresses_list( $user_id );
			if ( !empty($addresses_list) ) {
				foreach( $addresses_list as $type => $addresses_list_by_type ) {
					$first = true;
					foreach( $addresses_list_by_type as $address_id => $address ) {
						$address_type_id = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true);
						$sub_tpl_component = array();

						if ( ( $first && empty($_SESSION[ $addresses_types_def[$address_type_id] ]) ) || $first && !empty($_SESSION[$addresses_types_def[$address_type_id]]) ) {
							$sub_tpl_component['SELECTED_ADDRESS'] = 'checked="checked"';
							$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = 'class="wps-list-open"';
							$_SESSION[$addresses_types_def[$address_type_id]] = $address_id;
							$first = false;
						}
						elseif( !empty($_SESSION['shipping_address']) && $_SESSION['shipping_address'] == $address_id ) {
							$sub_tpl_component['SELECTED_ADDRESS'] = 'checked="checked"';
							$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = 'class="wps-list-open"';
						}
						else {
							$sub_tpl_component['SELECTED_ADDRESS'] = '';
							$sub_tpl_component['ADDRESS_CLASS_OPEN_ELEMENT'] = '';
						}
						$sub_tpl_component['ADDRESS_ID'] = $address_id;
						$sub_tpl_component['ADDRESS_TYPE'] = $addresses_types_def[$address_type_id];
						$sub_tpl_component['ADDRESS_TITLE'] = ( !empty($address['address_title']) ) ? $address['address_title'] : '';

						$sub_tpl_component['ADDRESS'] = self::display_an_address( $address );
						$addresses .= wpshop_display::display_template_element('wps_address', $sub_tpl_component, array(), 'wpshop');
						unset( $sub_tpl_component );
					}
				}
				$tpl_component = array();
				$billing_address_option = get_option( 'wpshop_billing_address' );
				$tpl_component['BILLING_ADDRESS_TYPE_ID'] = $billing_address_option['choice'];

				$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
				$tpl_component['SHIPPING_ADDRESS_TYPE_ID'] = $shipping_address_option['choice'];
				$tpl_component['ADDRESSES_LIST'] = $addresses;
				$output = wpshop_display::display_template_element('wps_addresses_container', $tpl_component, array(), 'wpshop');
				unset( $tpl_component );
			}
			else {
				/** Display an address_form **/
				$tpl_component['SHIPPING_FORM'] = self::display_form_fields( $shipping_address_option['choice'], '', 'first');
				$tpl_component['BILLING_FORM'] = self::display_form_fields( $billing_address_option['choice'], '', 'first');
				$tpl_component['LOADING_ICON'] =  WPSHOP_LOADING_ICON;
				$output = wpshop_display::display_template_element('wps_first_address_container', $tpl_component, array(), 'wpshop');
			}
		}
		return $output;
	}

	/**
	 * Get adress list for an user
	 * @param Integer $user_id
	 * @return Ambigous <multitype:, mixed, string, boolean, unknown, string>
	 */
	function get_addresses_list( $user_id ) {
		global $wpdb;
		$addresses_list = array();
		$query = $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts. ' WHERE post_type = %s AND post_parent = %s', WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $user_id );
		$addresses = $wpdb->get_results( $query );
		foreach( $addresses as $address ) {
			$address_post_meta = get_post_meta( $address->ID, '_wpshop_address_metadata', true);
			$address_type_post_meta = get_post_meta( $address->ID, '_wpshop_address_attribute_set_id', true);

			if( !empty($address_post_meta) && !empty($address_type_post_meta) ) {
				$addresses_list[$address_type_post_meta][$address->ID] = $address_post_meta;
			}
		}
		return $addresses_list;
	}

	/** Display Address**/
	function display_an_address( $address, $address_id = '', $address_type_id = '' ) {
		global $wpdb;
		$countries = unserialize(WPSHOP_COUNTRY_LIST);
		$output = '';
		if ( !empty($address) ) {
			$has_model = false;

			/** Check if a model exists**/
			if ( !empty($address_id) || !empty( $address_type_id ) ) {
				$address_type = ( !empty($address_type_id) ) ? $address_type_id : get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true );
				if( !empty($address_type) ) {
					/** Shipping & Billing option **/
					$shipping_option = get_option( 'wpshop_shipping_address_choice' );
					if ( !empty($shipping_option) && !empty($shipping_option['choice']) && $shipping_option['choice'] == $address_type && !empty($shipping_option['display_model']) ) {
						$display_model =  $shipping_option['display_model'];
						$has_model = true;

					}
					else {
						$billing_option = get_option( 'wpshop_billing_address' );
						if ( !empty($billing_option) && !empty($billing_option['choice']) && $billing_option['choice'] == $address_type && !empty($billing_option['display_model']) ) {
							$display_model =  $billing_option['display_model'];
							$has_model = true;
						}
					}
				}
			}
			$has_model = false;
			if (  $has_model ) {
				foreach( $display_model as $group_id => $group ) {
					foreach( $group as $att_id => $att ) {
						if ( !empty($att) ) {
							// Get attribute def
							$attribute_id = str_replace( 'attribute_', '' , $att );
							if( !empty($attribute_id) ) {
								$attribute_def = wpshop_attributes::getElement( $attribute_id, '"valid"', 'id' );
								if ( !empty($attribute_def) ) {
									if ( $attribute_def->frontend_input == 'select' ) {
										$query = $wpdb->prepare( 'SELECT value FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d',$address[ $attribute_def->code] );
										$output .= '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.__( $wpdb->get_var( $query ), 'wpshop' ).' ';
									}
									elseif( $attribute_def->frontend_verification == 'country' ) {
										$output .= ( !empty($countries[ $address[ $attribute_def->code] ]) ) ? '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.__( $countries[ $address[ $attribute_def->code] ], 'wpshop' ).' ' : '';
									}
									else {
										$output .= ( !empty($address[ $attribute_def->code]) ) ? '<strong>'.__( $attribute_def->frontend_label, 'wpshop').' :</strong> '.$address[ $attribute_def->code].' ' : ' ';
									}
								}
								//End Line
								$next_element = next( $display_model[$group_id] );
								$end_line = strstr( $next_element, '-end-line-', true );
								if ( !empty($end_line) && $end_line == 'wps-attribute' ) {
									$output .= '<br/>';
								}
							}
						}
					}
				}
			}
			else {
				if( !empty($address_type) ) {
					$tmp_array = array();
					$address_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS );
					$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS . ' WHERE attribute_set_id = %d AND entity_type_id = %d ORDER BY position', $address_type, $address_entity_id);
					$attributes_ids = $wpdb->get_results( $query );
					if( !empty($attributes_ids) ) {
						foreach( $attributes_ids as $attributes_id ) {
							if ( !empty( $attributes_id->attribute_id ) ) {
								$attribute_def = wpshop_attributes::getElement( $attributes_id->attribute_id, '"valid"', 'id' );
								if( $attribute_def->frontend_input != 'hidden') {
									if( !empty($attribute_def) && !empty($address[ $attribute_def->code ]) && $attribute_def->frontend_input != 'hidden' ) {
										$tmp_array[ $attribute_def->code]['label'] =  stripslashes( __( $attribute_def->frontend_label , 'wpshop' ) );

										if( $attribute_def->frontend_verification == 'country' ) {
											$tmp_array[ $attribute_def->code]['value'] =  ( !empty($countries[ $address[ $attribute_def->code] ]) ) ? stripslashes( __( $countries[ $address[ $attribute_def->code] ], 'wpshop' ) ) : stripslashes( $address[ $attribute_def->code ] );
										}
										elseif( in_array( $attribute_def->frontend_input, array('select', 'checkbox') ) ) {
											$query = $wpdb->prepare( 'SELECT label FROM '. WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $address[ $attribute_def->code] );
											$value = $wpdb->get_var( $query );
											$tmp_array[ $attribute_def->code]['value'] = ( !empty($value) ) ? stripslashes(  __( $value, 'wpshop' ) ) : '';
										}
										else {
											$tmp_array[ $attribute_def->code]['value'] = stripslashes(   __( $address[ $attribute_def->code ], 'wpshop' ) );
										}
									}
								}
							}
						}
						$address = $tmp_array;
					}
				}
				foreach( $address as $element_code => $element_value ) {
					if( is_array($element_value) ) {
						$output .= '<span class="wps-'.$element_code.'"><strong>' .stripslashes( $element_value['label'] ). ' :</strong> ' .stripslashes( $element_value['value'] ). '</span>';
					}
					else {
						$output .= '<span class="wps-'.$element_code.'">' .stripslashes( $element_value ). '</span>';
					}
				}
			}

		}
		return $output;
	}

	/**
	 * Get all addresses for current customer for display
	 *
	 * @param integer $address_type_id The current identifier of address type -> attribute_set_id
	 * @param string $address_type A string allowing to display
	 *
	 * @return string The complete html output for customer addresses
	 */
	function get_addresses_by_type( $address_type_id, $address_type_title, $args = array() ) {
		global $wpdb;
		/**	Get current customer addresses list	*/
		if ( is_admin() ) {
			$post = get_post( $_GET['post']);
			if ( !empty($post->post_parent) ) {
				$customer_id = $post->post_parent;
			}
			else {
				$customer_id = $post->post_author;
			}
		}
		else {
			$customer_id = get_current_user_id();
		}

		$query = $wpdb->prepare("
				SELECT ADDRESSES.ID
				FROM " . $wpdb->posts . " AS ADDRESSES
					INNER JOIN " . $wpdb->postmeta . " AS ADDRESSES_META ON (ADDRESSES_META.post_id = ADDRESSES.ID)
				WHERE ADDRESSES.post_type = %s
					AND ADDRESSES.post_parent = %d
				AND ADDRESSES_META.meta_key = %s
				AND ADDRESSES_META.meta_value = %d",
				WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, $customer_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_attribute_set_id', $address_type_id);
		$addresses = $wpdb->get_results($query);
		$addresses_list = '';


		/**	Initialize	*/
		$tpl_component = array();
		$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = ( !empty($args) && !empty($args['first']) && $args['first'] ) ? __('Your address', 'wpshop') : $address_type_title;
		$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
		$tpl_component['ADDRESS_BUTTONS'] = '';
		if( count($addresses) > 0 ) {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id;
		}
		else {
			$tpl_component['ADD_NEW_ADDRESS_LINK'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&amp;'). 'action=add_address&type=' .$address_type_id .'&first';
		}
		$tpl_component['ADDRESS_TYPE'] = ( !empty($address_type_title) && ($address_type_title == __('Shipping address', 'wpshop'))) ? 'shipping_address' : 'billing_address';
		$tpl_component['ADD_NEW_ADDRESS_TITLE'] = sprintf(__('Add a new %s', 'wpshop'), ( ( !empty($args) && !empty($args['first']) && $args['first'] ) ? __('address', 'wpshop') : $address_type_title ));


		/**	Read customer list	*/
		if( count($addresses) > 0 ) {
			/**	Get the fields for addresses	*/
			$address_fields = wps_address::get_addresss_form_fields_by_type($address_type_id);
			$first = true;
			$tpl_component['ADDRESS_COMBOBOX_OPTION'] = '';
			$nb_of_addresses = 0;
			foreach ( $addresses as $address ) {
				// Display the addresses
				/** If there isn't address in SESSION we display the first address of list by default */
				if ( empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $first && !is_admin() ) {
					$address_id = $address->ID;
					if ( !is_admin() ) {
						$_SESSION[$tpl_component['ADDRESS_TYPE']] = $address->ID;
					}
				}
				else {
					$address_id = ( !empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) )  ? $_SESSION[$tpl_component['ADDRESS_TYPE']] : '';
				}
				$address_selected_infos = get_post_meta($address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				$address_infos = get_post_meta($address->ID, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);


				if ( !empty($address_infos) ) {

					$tpl_component['ADDRESS_ID'] = $address->ID;
					/** If no address was selected, we select the first of the list **/
					$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = self::display_an_address($address_fields, $address_selected_infos, $address_id);
					$tpl_component['ADDRESS_BUTTONS'] = wpshop_display::display_template_element('addresses_box_actions_button_edit', $tpl_component);
					$tpl_component['choosen_address_LINK_EDIT'] = get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))) . (strpos(get_permalink(wpshop_tools::get_page_id(get_option('wpshop_myaccount_page_id'))), '?')===false ? '?' : '&') . 'action=editAddress&amp;id='.$address_id;
					$tpl_component['DEFAULT_ADDRESS_ID'] = $address_id;
					$tpl_component['ADRESS_CONTAINER_CLASS'] = ' wpshop_customer_adress_container_' . $address->ID;
					$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
					if ( empty($tpl_component['CUSTOMER_ADDRESS_CONTENT']) ) {
						$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = '<span style="color:red;">'.__('No data','wpshop').'</span>';
					}

					$tpl_component['ADDRESS_COMBOBOX_OPTION'] .= '<option value="' .$address->ID. '" ' .( ( !empty($_SESSION[$tpl_component['ADDRESS_TYPE']]) && $_SESSION[$tpl_component['ADDRESS_TYPE']] == $address->ID) ? 'selected="selected"' : null). '>' . (!empty($address_infos['address_title']) ? $address_infos['address_title'] : $address_type_title) . '</option>';
					$nb_of_addresses++;
				}
				$first = false;
			}
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			if ( !is_admin() ) {
				$tpl_component['ADDRESS_COMBOBOX'] = (!empty($tpl_component['ADDRESS_COMBOBOX_OPTION']) && ($nb_of_addresses > 1)) ? wpshop_display::display_template_element('addresses_type_combobox', $tpl_component) : '';
			}
		}
		else {
			if ( !empty($args) && !empty($args['first']) && $args['first'] ) {
				$tpl_component['ADDRESS_TYPE'] = 'first_address';
			}
			$tpl_component['ADDRESS_ID'] = 0;
			$tpl_component['DEFAULT_ADDRESS_ID'] = 0;
			$tpl_component['ADDRESS_COMBOBOX'] = '';
			$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = sprintf( __('You don\'t have any %s, %splease create a new one%s', 'wpshop'), ( (!empty($args) && !empty($args['first']) && $args['first']) ? __('address', 'wpshop') : strtolower($address_type_title) ) , '<a href="' . $tpl_component['ADD_NEW_ADDRESS_LINK'] . '" >', '</a>' );
		}

		$tpl_component['ADDRESS_BUTTONS'] .= wpshop_display::display_template_element('addresses_box_actions_button_new_address', $tpl_component);
		if ( !empty($args['only_display']) && ($args['only_display'] == 'yes') ) {
			$tpl_component['ADDRESS_BUTTONS'] = '';
		}

		$addresses_list .= wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);



		return $addresses_list;
	}


	/** Load Address Modal Box Content **/
	function wps_load_address_form() {
		$response = '';
		$address_id = ( !empty( $_POST['address_id']) ) ? wpshop_tools::varSanitizer( $_POST['address_id' ]) : '';
		$address_type_id = ( !empty( $_POST['address_type_id']) ) ? wpshop_tools::varSanitizer( $_POST['address_type_id'] ) : '';


		$form_data = self::loading_address_form( $address_type_id, $address_id, get_current_user_id() );
		$response = $form_data[0];
		$title = $form_data[1];

		echo json_encode( array($response, $title) );
		die();
	}


	function loading_address_form( $address_type_id, $address_id = '', $user_id = '' ) {
		$response  = '<div id="wps_address_error_container"></div>';
		$response .= '<form id="wps_address_form_save" action="' .admin_url('admin-ajax.php'). '" method="post">';
		$response .= 'Salut<input type="hidden" name="action" value="wps_save_address" />';
		$first_address_checking = false;

		$user_id = ( !empty($user_id) ) ? $user_id : get_current_user_id();

		if ( !empty($address_id) ) {
			$address_type = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true);
			$response .= self::display_form_fields($address_type, $address_id, '', '', array(), array(), array(), $user_id);
			$title = __('Edit your address', 'wpshop');
		}
		elseif($address_type_id) {
			$billing_option = get_option( 'wpshop_billing_address' );

			$addresses = self::get_addresses_list( $user_id );
			$list_addresses = ( !empty($addresses[ $billing_option['choice'] ]) ) ? $addresses[ $billing_option['choice'] ] : array();
			$first_address_checking = ( empty( $list_addresses ) ) ? true : false;

			$response .= self::display_form_fields($address_type_id, '', '', '', array(), array(), array(), $user_id );
			$title = __('Add a new address', 'wpshop');
		}
		/** Check if a billing address is already save **/
		if ( $first_address_checking && $address_type_id != $billing_option['choice'] ) {
			$response .= '<div class="wps-form"><input name="wps-shipping-to-billing" id="wps-shipping-to-billing" checked="checked" type="checkbox" /> <label for="wps-shipping-to-billing">' .__( 'Use the same address for billing', 'wpshop' ). '</label></div>';
		}


		$response .= '<button id="wps_submit_address_form" class="wps-bton-first-alignRight-rounded">' .__('Save', 'wpshop'). '</button>';
		$response .= '</form>';
		return array( $response, $title );
	}


	/** Ajax Function for save address **/
	function wps_save_address() {
		global $wpshop;

		$status = false; $result = $address_type = $same_address_type = '';
		foreach ( $_POST['attribute'] as $id_group => $attribute_group ) {
			$address_type = $id_group;
			$group = wps_address::get_addresss_form_fields_by_type ($id_group);
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$id_group], 'address_edition');
				}
				if ( $validate ) {
					self::save_address_infos( $id_group );
					if( !empty($_POST['wps-shipping-to-billing']) ) {
						$billing_option = get_option( 'wpshop_billing_address' );
						$shipping_option = get_option( 'wpshop_shipping_address_choice' );
						self::shipping_to_billing( $shipping_option['choice'], $billing_option['choice'] );
						self::save_address_infos( $billing_option['choice'] );
						$same_address_type = $billing_option['choice'];
					}

					$status = true;
				}
				else {
					if ( !empty($wpshop->errors) ){
						$result = '<div class="wps-alert wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
						foreach(  $wpshop->errors as $error ){
							$result .= '<li>' .$error. '</li>';
						}
						$result .= '</div>';
					}
				}
			}
		}
		echo json_encode( array( $status, $result, $address_type, $same_address_type ) );
		die();
	}

	/**
	 * Generate an array with all fields for the address form construction. Classified by address type.
	 * @param $typeof
	 * @return array
	 */
	function get_addresss_form_fields_by_type ( $typeof, $id ='' ) {
		$current_item_edited = isset($id) ? (int)wpshop_tools::varSanitizer($id) : null;
		$address = array();
		$all_addresses = '';
		/*	Get the attribute set details in order to build the product interface	*/

		$atribute_set_details = wpshop_attributes_set::getAttributeSetDetails($typeof, "'valid'");
		if ( !empty($atribute_set_details) ) {
			foreach ($atribute_set_details as $productAttributeSetDetail) {
				$address = array();
				$group_name = $productAttributeSetDetail['name'];

				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if ( !empty($_POST['submitbillingAndShippingInfo']) ) {
								$value = $_POST['attribute'][$typeof][$attribute->data_type][$attribute->code];
							}
							else {
								$value = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS), $current_item_edited, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$attribute_output_def['id'] = 'address_' . $typeof . '_' .$attribute_output_def['id'];
							$address[str_replace( '-', '_', sanitize_title($group_name) ).'_'.$attribute->code] = $attribute_output_def;
						}
					}
				}

				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['name'] = $group_name;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['content'] = $address;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['id'] = str_replace('-', '_', sanitize_title($group_name));
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['attribute_set_id'] = $productAttributeSetDetail['attribute_set_id'];
			}

		}
		return $all_addresses;
	}

	function wps_save_first_address() {
		global $wpshop;
		$errors = '';
		$status = false; $result = ''; $validate_address_2 = true;
		$shipping_address_option = get_option( 'wpshop_shipping_address_choice' );
		$billing_address_option = get_option( 'wpshop_billing_address' );
		/** Validate Shipping address **/
		$group = wps_address::get_addresss_form_fields_by_type ( $shipping_address_option['choice'] );
		foreach ( $group as $attribute_sets ) {
			foreach ( $attribute_sets as $attribute_set_field ) {
				$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$shipping_address_option['choice']], 'address_edition');
			}
		}

		if ( empty($_POST['shiptobilling']) ) {
			$group = wps_address::get_addresss_form_fields_by_type ( $billing_address_option['choice'] );
			foreach ( $group as $attribute_sets ) {
				foreach ( $attribute_sets as $attribute_set_field ) {
					$validate_address_2 = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'][$billing_address_option['choice']], 'address_edition');
				}
			}
		}

		if ( $validate && $validate_address_2) {
			$return = wps_address::save_address_infos( $shipping_address_option['choice'] );
			if( !empty($return) && !empty($return['current_id']) ) {
				$_SESSION['shipping_address'] = $return['current_id'];
			}
			if ( !empty( $_POST['shiptobilling']) ) {
				self::same_shipping_as_billing($_POST['billing_address'], $_POST['shipping_address']);
				$return = wps_address::save_address_infos( $billing_address_option['choice'] );
				if( !empty($return) && !empty($return['current_id']) ) {
					$_SESSION['billing_address'] = $return['current_id'];
				}
			}
			else {
				$return = wps_address::save_address_infos( $billing_address_option['choice'] );
				if( !empty($return) && !empty($return['current_id']) ) {
					$_SESSION['billing_address'] = $return['current_id'];
				}
			}
			$status = true;
			$result = self::get_addresses();
		}
		else {
			if ( !empty($wpshop->errors) ){
				$result = '<div class="wps-alert wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
				foreach(  $wpshop->errors as $error ){
					$result .= '<li>' .$error. '</li>';
				}
				$result .= '</div>';
			}
		}

		$response = array( $status, $result );
		echo json_encode( $response );
		die();
	}


	/** Treat the differents fields of form and classified them by form
	 * @return boolean
	 */
	function save_address_infos( $attribute_set_id ) {
		global $wpdb;
		$current_item_edited = !empty($_POST['attribute'][$attribute_set_id]['item_id']) ? (int)wpshop_tools::varSanitizer($_POST['attribute'][$attribute_set_id]['item_id']) : null;
		// Create or update the post address
		$post_parent = '';
		$post_author = get_current_user_id();
		if ( !empty($_REQUEST['user']['customer_id']) ) {
			$post_parent = $_REQUEST['user']['customer_id'];
			$post_author = $_REQUEST['user']['customer_id'];
		}
		elseif ( !empty($_REQUEST['post_ID']) ) {
			$post_parent = $_REQUEST['post_ID'];
		}
		else {
			$post_parent = get_current_user_id();
		}
		$post_address = array(
				'post_author' => $post_author,
				'post_title' => !empty($_POST['attribute'][$attribute_set_id]['varchar']['address_title']) ? $_POST['attribute'][$attribute_set_id]['varchar']['address_title'] : '',
				'post_status' => 'draft',
				'post_name' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS,
				'post_parent'=>	$post_parent
		);
		$_POST['edit_other_thing'] = true;

		if ( empty($current_item_edited) && (empty($_POST['current_attribute_set_id']) || $_POST['current_attribute_set_id'] != $attribute_set_id )) {
			$current_item_edited = wp_insert_post( $post_address );
			if ( is_admin()) {
				$_POST['attribute'][$attribute_set_id]['item_id'] = $current_item_edited;
			}
		}
		else {
			$post_address['ID'] = $current_item_edited;
			wp_update_post( $post_address );
		}

		//Update the post_meta of address
		update_post_meta($current_item_edited, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, $attribute_set_id);

		foreach ( $_POST['attribute'][ $attribute_set_id ] as $type => $type_content) {
			$attribute_not_to_do = array();
			if (is_array($type_content) ) {
				foreach ( $type_content as $code => $value) {
					$attribute_def = wpshop_attributes::getElement($code, "'valid'", 'code');
					if ( !empty($attribute_def->_need_verification) && $attribute_def->_need_verification == 'yes' ) {
						$code_verif = $code.'2';
						$attribute_not_to_do[] = $code_verif;
						if ( !empty($attributes[$code_verif] )) {
							unset($attributes[$code_verif]);
						}
					}
					if( !in_array($code, $attribute_not_to_do)) $attributes[$code] = $value;
				}
			}
		}

		$attributes = apply_filters( 'wps-address-coordinate-calculation', $attributes );

		$result = wpshop_attributes::setAttributesValuesForItem( $current_item_edited, $attributes, false, '' );
		$result['current_id'] = $current_item_edited;


		if( !empty($result['current_id']) ) {
			// Update $_SESSION[address type]
			$billing_option = get_option( 'wpshop_billing_address' );
			if( !empty($billing_option) && !empty($billing_option['choice']) && $billing_option['choice'] == $attribute_set_id ) {
				$_SESSION['billing_address'] = $result['current_id'];
			}
			else {
				$_SESSION['shipping_address'] = $result['current_id'];
			}
		}


		return $result;
	}

	/**
	 * Display the differents forms fields
	 * @param string $type : Type of address
	 * @param string $first : Customer first address ?
	 * @param string $referer : Referer website page
	 * @param string $admin : Display this form in admin panel
	 */
	function display_form_fields($type, $id = '', $first = '', $referer = '', $special_values = array(), $options = array(), $display_for_admin = array(), $other_customer = '' ) {
		global $wpshop, $wpshop_form, $wpdb;

		$choosen_address = get_option('wpshop_billing_address');
		$shipping_address = get_option('wpshop_shipping_address_choice');
		$output_form_fields = $form_model = '';

		$user_id = ( !empty($other_customer) ) ? $other_customer : get_current_user_id();


		if ( empty($type) ) {
			$type = $choosen_address['choice'];
		}

		$result = wps_address::get_addresss_form_fields_by_type($type, $id);

		/** Check if it's shipping or billing **/
		if ( $type == $choosen_address['choice'] ) {
			$form_model =  ( !empty($choosen_address['display_model']) ) ? $choosen_address['display_model'] : null;
		}
		elseif( $type == $shipping_address['choice'] ) {
			$form_model = ( !empty($shipping_address['display_model']) ) ? $shipping_address['display_model'] : null;
		}


		$form = $result[$type];
		// Take the post id to make the link with the post meta of  address
		$values = array();
		// take the address informations
		$current_item_edited = !empty($id) ? (int)wpshop_tools::varSanitizer($id) : null;

		foreach ( $form as $group_id => $group_fields) {
			if ( empty($options) || (!empty($options) && ($options['title']))) $output_form_fields .= '<h2>'.__( $group_fields['name'], 'wpshop' ).'</h2>';
			$end_line_indicator = 0; $fields_limit_per_line = -1;
			foreach ( $group_fields['content'] as $key => $field) {
				$attribute_def = wpshop_attributes::getElement( $field['name'], $element_status = "'valid'", $field_to_search = 'code' );
				/** Grid opening **/
				if ( !empty($form_model) && !empty($form_model[$group_id]) && in_array('wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id]) && $fields_limit_per_line == -1 ) {
					$current_key = array_search( 'wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id] );
					$current_attribute_key = array_search( 'attribute_'.$attribute_def->id, $form_model[$group_id] );

					if( $current_attribute_key > $current_key ) {
						/** Define limit **/
						if( in_array('wps-attribute-end-line-' . ($end_line_indicator + 1 ) , $form_model[$group_id]) ) {
							$next_key = array_search( 'wps-attribute-end-line-'.( $end_line_indicator + 1 ), $form_model[$group_id] );
							$fields_limit_per_line = $next_key - $current_key - 1;
							$fields_limit_per_line = ( $fields_limit_per_line > 6 )  ? 6 : $fields_limit_per_line;
						}
						else {
							$current_key = array_search( 'wps-attribute-end-line-'.$end_line_indicator, $form_model[$group_id] );
							$end_tab = count($form_model[$group_id]) - 1;
							$fields_limit_per_line = $end_tab - $current_key - 1;
							$fields_limit_per_line = ( $fields_limit_per_line > 6 )  ? 6 : $fields_limit_per_line;
						}
						if ( !empty($fields_limit_per_line) && $fields_limit_per_line != -1 ) {
							if ( $fields_limit_per_line == 1 ) {
								$output_form_fields .= '<div class="wps-row">';
							}
							else {
								$output_form_fields .= '<div class="wps-row wps-gridwrapper' .$fields_limit_per_line. '-padded">';
							}
						}
					}
				}

				if ( empty($options['field_to_hide']) || !is_array($options['field_to_hide']) || !in_array( $key, $options['field_to_hide'] ) ) {
					$attributeInputDomain = 'attribute[' . $type . '][' . $field['data_type'] . ']';
					// Test if there is POST var or if user have already fill his address infos and fill the fields with these infos
					if( !empty($_POST) ) {
						$referer = !empty($_POST['referer']) ? $_POST['referer'] : '';
						if ( !empty($form['id']) && !empty($field['name']) && isset($_POST[$form['id']."_".$field['name']]) ) {
							$value = $_POST[$form['id']."_".$field['name']];
						}
					}



					// Fill Automaticly some fields when it's an address creation
					switch ( $field['name']) {
						case 'address_title' :
							if( empty($field['value']) ) {
								/** Count Billing and shipping address **/
								$billing_address_count = $shipping_address_count = 1;
								if ( get_current_user_id() != 0 ) {
									$addresses = get_posts( array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS, 'post_parent' => get_current_user_id(), 'post_status' => 'draft') );
									if ( !empty($addresses) ) {
										foreach( $addresses as $address ) {
											$address_type = get_post_meta( $address->ID, '_wpshop_address_attribute_set_id', true);
											if ( !empty($address_type) ){
												if ( !empty( $shipping_address_choice['choice'] ) && $address_type == $shipping_address_choice['choice'] ) {
													$shipping_address_count++;
												}
												else{
													$billing_address_count++;
												}
											}
										}
									}
								}
								$field['value'] = ( $type == $choosen_address['choice'] ) ? __('Billing address', 'wpshop').( ($billing_address_count > 1) ? ' '.$billing_address_count : '' ) : __('Shipping address', 'wpshop').( ($shipping_address_count > 1) ? ' '.$shipping_address_count : '');

							}
							break;
						case 'address_last_name' :
							if( empty($field['value']) ) {
								$usermeta_last_name = get_user_meta( $user_id, 'last_name', true);
								$field['value'] = ( !empty($usermeta_last_name) ) ? $usermeta_last_name :  '';
							}
							break;
						case 'address_first_name' :
							if( empty($field['value']) ) {
								$usermeta_first_name = get_user_meta( $user_id, 'first_name', true);
								$field['value'] = ( !empty($usermeta_first_name) ) ? $usermeta_first_name :  '';
							}
							break;
						case 'address_user_email' :
							if( empty($field['value']) ) {
								$user_infos = get_userdata( $user_id );
								$field['value'] = ( !empty($user_infos) && !empty($user_infos->user_email) ) ? $user_infos->user_email :  '';
							}
							break;
						default :
							$field['value'] = ( !empty($field['value']) ) ? $field['value'] : '';
							break;
					}


					/** Fill fields if $_POST exist **/
					if ( !empty( $_POST['attribute'][$type][$field['data_type']][$field['name']] ) ) {
						$field['value'] = $_POST['attribute'][$type][$field['data_type']][$field['name']];
					}


					if( $field['name'] == 'address_title' && !empty($first) && $type == __('Billing address', 'wpshop') ) {
						$value = __('Billing address', 'wpshop');
					}
					elseif( $field['name'] == 'address_title' && !empty($first) && $type == __('Shipping address', 'wpshop') ) {
						$value = __('Shipping address', 'wpshop');
					}

					if ( !empty($special_values[$field['name']]) ) {
						$field['value'] = $special_values[$field['name']];
					}

					$template = 'wpshop_account_form_input';
					if ( $field['type'] == 'hidden' ) {
						$template = 'wpshop_account_form_hidden_input';
					}

					if ( $field['frontend_verification'] == 'country' ) {
						$field['type'] = 'select';
						/** display a country list **/
						$countries_list = unserialize(WPSHOP_COUNTRY_LIST);
						$possible_values = array_merge(array('' => __('Choose a country')), $countries_list);

						$limit_countries_list = get_option( 'wpshop_limit_country_list' );
						$default_country_choice = get_option( 'wpshop_country_default_choice' );
						if ( !empty($limit_countries_list) ) {
							$possible_values = array();
							if ( count($limit_countries_list) > 1 ) {
								$possible_values[''] = __('Choose a country');
							}
							foreach( $limit_countries_list as $country_code) {
								if ( !empty($countries_list) && !empty($countries_list[$country_code]) ) {
									$possible_values[$country_code] = $countries_list[$country_code];
								}
							}
						}

						$field['value'] = ( !empty($default_country_choice) && array_key_exists($default_country_choice, $possible_values ) ) ? $default_country_choice : '';
						$field['possible_value'] = $possible_values;
						$field['valueToPut'] = 'index';

					}



					$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
					$input_tpl_component = array();

					//$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = ( $field['type'] != 'hidden' ) ? stripslashes( __( $field['label'], 'wpshop' ) ) . ( ( $field['required'] == 'yes' ) ? ' <em>*</em>' : '') : '';
					$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
					$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain);
					//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);


					$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');


					unset($input_tpl_component);

					if ( $field['_need_verification'] == 'yes' ) {
						$field['name'] = $field['name'] . '2';
						$field['id'] = $field['id'] . '2';
						$element_simple_class = str_replace('"', '', str_replace('class="', '', str_replace('wpshop_input_datetime', '', $field['option'])));
						$input_tpl_component = array();
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = __( $field['label'], 'wpshop' ) . ( ( ($field['required'] == 'yes' && !is_admin()) || ($field['name'] == 'address_user_email' && is_admin()) ) ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL_OPTIONS'] = ' for="' . $field['id'] . '"';
						$input_tpl_component['CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS'] = ' wsphop_customer_account_form_container wsphop_customer_account_form_container_' . $field['name'] . $element_simple_class;
						$input_tpl_component['CUSTOMER_FORM_INPUT_LABEL'] = sprintf( __('Confirm %s', 'wpshop'), strtolower( __( $field['label'], 'wpshop' ) ) ). ( ($field['required'] == 'yes') && !is_admin() ? ' <span class="required">*</span>' : '');
						$input_tpl_component['CUSTOMER_FORM_INPUT_FIELD'] = wpshop_form::check_input_type($field, $attributeInputDomain) . $field['options'];
						//$output_form_fields .= wpshop_display::display_template_element($template, $input_tpl_component);
						$output_form_fields .= wpshop_display::display_template_element('wps_address_field', $input_tpl_component, array(), 'wpshop');
						unset($input_tpl_component);
					}
				}

				/** Grid closing **/
				if( $fields_limit_per_line != -1 && !empty($fields_limit_per_line) ) {
					$fields_limit_per_line--;
					if( $fields_limit_per_line == 0 ) {
						$output_form_fields .= '</div>';
						$fields_limit_per_line = -1;
						$end_line_indicator++;
					}
				}
			}
		}

		if ( $type ==  $choosen_address['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="billing_address" value="'.$choosen_address['choice'].'" />';
		}
		$shipping_address_options = get_option('wpshop_shipping_address_choice');
		if ( $type ==  $shipping_address_options['choice'] ) {
			$output_form_fields .= '<input type="hidden" name="shipping_address" value="' .$shipping_address_options['choice']. '" />';
		}
		$output_form_fields .= '<input type="hidden" name="edit_other_thing" value="'.false.'" /><input type="hidden" name="referer" value="'.$referer.'" />
								<input type="hidden" name="type_of_form" value="' .$type. '" /><input type="hidden" name="attribute[' .$type. '][item_id]" value="' .$current_item_edited. '" />';

		$output_form_fields .= ( $user_id != get_current_user_id() ) ? '<input type="hidden" name="user[customer_id]" value="' .$user_id. '" />' : '';

		if ( empty($first) ) $output_form_fields = wpshop_display::display_template_element('wpshop_customer_addresses_form', array('CUSTOMER_ADDRESSES_FORM_CONTENT' => $output_form_fields, 'CUSTOMER_ADDRESSES_FORM_BUTTONS' => ''));

		return $output_form_fields;
	}

	function same_shipping_as_billing($billing_address_id, $shipping_address_id) {
		if ( !empty($_POST) ) {
			$tableauGeneral =  $_POST;
		}
		else {
			$tableauGeneral = $_REQUEST;
		}

		// Create an array with the shipping address fields definition
		$shipping_fields = array();
		foreach ($tableauGeneral['attribute'][$billing_address_id] as $key=>$attribute_group ) {
			if ( is_array($attribute_group) ) {
				foreach( $attribute_group as $field_name=>$value ) {
					$shipping_fields[] =  $field_name;
				}
			}
		}
		// Test if the billing address field exist in shipping form
		foreach ($tableauGeneral['attribute'][$shipping_address_id] as $key=>$attribute_group ) {
			if (is_array($attribute_group) ) {
				foreach( $attribute_group as $field_name=>$value ) {
					if ( in_array($field_name, $shipping_fields) ) {
						if ($field_name == 'address_title') {
							$tableauGeneral['attribute'][$billing_address_id][$key][$field_name] = __('Billing address', 'wpshop');
						}
						else {
							$tableauGeneral['attribute'][$billing_address_id][$key][$field_name] = $tableauGeneral['attribute'][$shipping_address_id][$key][$field_name];
						}
					}
				}
			}
		}

		foreach ( $tableauGeneral as $key=>$value ) {
			if ( !empty($_POST) ) {
				$_POST[$key] = $value;
			}
			else {
				$_REQUEST[$key] = $value;
			}
		}

	}

	function shipping_to_billing( $shipping_address_type_id, $billing_address_type_id ) {
		global $wpdb;
		$tmp_array = array();
		$tmp_array = ( !empty($_POST) ) ? $_POST : $_REQUEST;

		$billing_fields = array();
		if( !empty($tmp_array) && !empty($tmp_array['attribute']) && !empty($tmp_array['attribute'][$shipping_address_type_id]) ) {
			foreach ($tmp_array['attribute'][$shipping_address_type_id] as $key => $attribute_group ) {
				if ( is_array($attribute_group) ) {
					foreach( $attribute_group as $field_name => $value ) {
						$attribute_def = wpshop_attributes::getElement( $field_name, "'valid'", 'code' );
						if( !empty($attribute_def) ) {
							$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE status = %s AND attribute_id = %d AND attribute_set_id = %s', 'valid', $attribute_def->id, $billing_address_type_id );
							$attribute_exist = $wpdb->get_var( $query );
							if ( !empty($attribute_exist) ) {
								$tmp_array['attribute'][$billing_address_type_id][$attribute_def->data_type][$field_name] = $value;
							}
						}
					}
				}
			}
			$_POST = $tmp_array;
		}
	}


	function get_shipping_address_summary() {
		$output = '';
		if ( !empty( $_SESSION['shipping_address']) ){
			$address_infos = get_post_meta($_SESSION['shipping_address'], '_wpshop_address_metadata', true);
			$tpl_component['ADDRESS'] = self::display_an_address( $address_infos );
			$tpl_component['TITLE'] = __('Shipping informations', 'wpshop');
			$tpl_component['ADDRESS_ID'] = $_SESSION['shipping_address'];
			$output = wpshop_display::display_template_element('wps_shipping_address_summary', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
		}
		return $output;
	}


	function get_billing_address_summary() {
		$output = '';
		if ( !empty( $_SESSION['billing_address']) ){
			$address_infos = get_post_meta($_SESSION['billing_address'], '_wpshop_address_metadata', true);
			$tpl_component['ADDRESS'] = self::display_an_address( $address_infos );
			$tpl_component['TITLE'] = __('Billing informations', 'wpshop');
			$tpl_component['ADDRESS_ID'] = $_SESSION['billing_address'];
			$output = wpshop_display::display_template_element('wps_shipping_address_summary', $tpl_component, array(), 'wpshop');
			unset( $tpl_component );
		}
		return $output;
	}


	function shortcode_display_addresses_list( $args ) {
		$addresses = $this->get_addresses_list( $args[ 'id' ] );
		require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'addresses.php' );
	}


	function display_addresses_interface( $customer_id = '' ) {
		$output = $extra_class = '';
		$is_from_admin = ( !empty($customer_id) ) ? true : false;
		$user_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
		if ( $user_id != 0 ) {

			/** Shipping address **/
			$shipping_option = get_option( 'wpshop_shipping_address_choice');
			$billing_option = get_option( 'wpshop_billing_address' );
			if( !empty($shipping_option) && !empty($shipping_option['activate']) ) {
				$address_title = __( 'Shipping address', 'wpshop' );
				$address_type = 'shipping-address';
				$extra_class= 'wps-'.$address_type;
				$address_type_id = $shipping_option['choice'];
				$box_content = self::display_address_interface_content( $shipping_option['choice'], $address_title, '', 'shipping', $user_id );
				/** First address checking **/
				$selected_address = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : '';
				$addresses = self::get_addresses_list( $user_id );
				$list_addresses = ( !empty($addresses[ $billing_option['choice'] ]) ) ? $addresses[ $billing_option['choice'] ] : array();
				$first_address_checking = ( empty( $list_addresses ) ) ? true : false;
				ob_start();
				require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, $this->template_dir, "frontend", "address", "container") );
				$output = ob_get_contents();
				ob_end_clean();

			}



			/** Billing address **/
			if( !empty($billing_option) && !empty($billing_option['choice']) ) {
				$address_title = __( 'Billing address', 'wpshop' );
				$address_type = 'billing-address';
				$address_type_id = $billing_option['choice'];
				$box_content = self::display_address_interface_content( $billing_option['choice'], $address_title, '', 'billing', $user_id );
				$extra_class= 'wps-'.$address_type;
				$first_address_checking = false;
				$selected_address = ( !empty($_SESSION['billing_address']) ) ? $_SESSION['billing_address'] : '';
				ob_start();
				require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, $this->template_dir, "frontend", "address", "container") );
				$output .= ob_get_contents();
				ob_end_clean();
			}

		}
		return $output;
	}

	/** Display address interface content **/
	function display_address_interface_content( $address_type_id, $address_title, $selected_address = null, $type, $customer_id = '' ) {
		$user_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
		$is_from_admin = ( !empty($customer_id) ) ? true : false;
		$select_id = ( !empty($type) && $type == 'shipping') ?  'shipping_address_address_list' : 'billing_address_address_list';
		$output = '';
		if( !empty($address_type_id) ) {
			$addresses = self::get_addresses_list( $user_id );
			$list_addresses = ( !empty($addresses[ $address_type_id ]) ) ? $addresses[ $address_type_id ] : array();
			if ( empty($list_addresses) ) {
				$form = self::display_form_fields($address_type_id);
			}
			$output = '';

			ob_start();
			require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, $this->template_dir, "frontend", "address", "content") );
			$output .= ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}



	/**
	 * Display address in Back-Office panel
	 * @param array $address_data
	 */
	function display_address_in_administration( $addresses_datas, $address_type ) {
		global $wpdb;
		$output = '';
		if( !empty($addresses_datas) && !empty($address_type) ) {
		$query = $wpdb->prepare( 'SELECT name FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE id = %d', $address_type );
		$title = __( $wpdb->get_var( $query ), 'wpshop');

		ob_start();
		require( wpshop_tools::get_template_part( WPS_ADDRESS_DIR, $this->template_dir, "backend", "admin-address", "content") );
		$output .= ob_get_contents();
		ob_end_clean();
		}
		return $output;
	}

	/**
	 * AJAX - Display addresses list for a given element
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_addresses_list() {
		$addresses = $this->get_addresses_list( $_POST[ 'post_id' ] );
		require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'addresses.php' );
		die();
	}

	/**
	 * AJAX - Display selected address for a given element
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_address() {
		$address_post_meta = get_post_meta( $_POST[ 'address_id' ], '_wpshop_address_metadata', true);
		$address_type_post_meta = get_post_meta( $_POST[ 'address_id' ], '_wpshop_address_attribute_set_id', true);
		if( !empty($address_post_meta) && !empty($address_type_post_meta) ) {
			$addresses_list[$address_type_post_meta][ $_POST[ 'address_id' ] ] = $address_post_meta;
		}
		$address_open = true;
		foreach ( $addresses_list as $address_type => $addresses_list_by_type ) :
			foreach ( $addresses_list_by_type as $address_id => $address ) :
				require( WPS_LOCALISATION_BACKEND_TPL_DIR . 'address.php' );
			endforeach;
		endforeach;

		die();
	}

	/**
	 * AJAX - Display address form or a given element. CHeck if there are many types of addresses in order to choose address type dropdown or directly address form
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function display_address_adding_form() {
		global $wpdb;

		$address_id = 0;
		$post_ID = $_POST[ 'post_id' ];
		$element = get_post( $post_ID );
		$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s ", $element->post_type);
		$element_cpt = $wpdb->get_var( $query );
		$attached_addresses = get_post_meta( $element_cpt, '_wpshop_entity_attached_address', true );

		if ( !empty( $attached_addresses ) ) {
			if ( count( $attached_addresses ) == 1 ) {
				$address_type_id = $attached_addresses[ 0 ];
				require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'address-form.php' );
			}
			else {
				require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'addresses-types.php' );
			}
		}
		else {
			printf( __( 'No addresses are attached to this element type %s', 'wpeo_geoloc' ), $element->post_type);
		}

		die();
	}

	/**
	 * AJAX - Display address form for address edition
	 *
	 * @since 1.0 - WPShop 1.3.7.0
	 */
	function load_address_edition_form() {
		$address_id = $_POST[ 'element_id' ];
		$post_ID = $_POST[ 'post_id' ];
		$address_type_id = get_post_meta( $address_id, '_wpshop_address_attribute_set_id', true);
		if ( empty( $address_id ) && empty( $address_type_id ) && !empty( $_POST[ 'wpeogeo-address-type-chosen-for-creation' ] ) ) {
			$address_type_id = $_POST[ 'wpeogeo-address-type-chosen-for-creation' ];
		}

		require_once( WPS_LOCALISATION_BACKEND_TPL_DIR . 'address-form.php' );

		die();
	}

	/**
	 * AJAX - Delete an address
	 */
	function wps_delete_an_address() {
		$status = false; $response = '';
		$address_id = ( !empty( $_POST['address_id']) ) ? intval( $_POST['address_id'] ) : null;
		if( !empty($address_id) ) {
			/** Check if user is author of address **/
			$address = get_post( $address_id );
			if( !empty($address) && !empty($address->post_type) && $address->post_type == WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS && !empty($address->post_author) && $address->post_author == get_current_user_id() ) {
				wp_delete_post( $address_id, true );
				$status = true;
			}
		}
		echo json_encode( array('status' => $status, 'response' => $response) );
		die();
	}

	/**
	 * AJAX - Relad Address Interface in new checkout tunnel
	 */
	function wps_reload_address_interface() {
		global $wpdb;
		$status = false; $response = '';
		$address_type = !empty($_POST['address_type']) ? intval( $_POST['address_type'] ) : null;
		$selected_address = !empty($_POST['address_id']) ? intval( $_POST['address_id'] ) : null;

		if ( !empty( $address_type ) ) {
			$billing_option = get_option( 'wpshop_billing_address' );
			if ( !empty($billing_option) && !empty($billing_option['choice']) && $billing_option['choice'] == $address_type ) {
				$type = 'billing';
			}
			else {
				$type = 'shipping';
			}
			$query = $wpdb->prepare( 'SELECT name FROM '.WPSHOP_DBT_ATTRIBUTE_SET .' WHERE id = %d ', $address_type );
			$address_title = __( $wpdb->get_var( $query ), 'wpshop' );
			$response = self::display_address_interface_content( $address_type, $address_title, $selected_address, $type );
			$status = true;
		}
		echo json_encode( array( 'status' => $status, 'response' => $response ) );
		die();
	}

	function wps_add_an_address_in_admin() {
		$address_type_id = ( !empty($_GET['address_type']) ) ? $_GET['address_type'] : '';
		$address_id = ( !empty($_GET['address_id']) ) ? $_GET['address_id'] : '';
		$form_data = self::loading_address_form( $address_type_id, $address_id, $_GET['customer_id'] );
		echo $form_data[0];
		wp_die();
	}

}
