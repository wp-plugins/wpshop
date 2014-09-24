<?php
class wps_shipping_mode_ctr {
	
	/**
	 * Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_SHIPPING_MODE_DIR;
	
	function __construct() {
		$this->template_dir = WPS_SHIPPING_MODE_PATH . WPS_SHIPPING_MODE_DIR . "/templates/";
		
		add_thickbox();
		/** Template Load **/
		add_filter( 'wpshop_custom_template', array( $this, 'custom_template_load' ) );
		
		add_action( 'admin_init', array( $this, 'migrate_default_shipping_mode' ) );
		
		/**	Add module option to wpshop general options	*/
		add_filter('wpshop_options', array( $this, 'add_options'), 9);
		add_action('wsphop_options', array( $this, 'create_options'), 8);
		
		// Add files in back-office
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts_in_admin' ) );
		// Add files in front-office
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts') );
		
		
		// Available Shortcodes
		add_shortcode( 'wps_shipping_mode', array( &$this, 'display_shipping_mode') );
		add_shortcode( 'wps_shipping_method', array( &$this, 'display_shipping_methods') );
		add_shortcode( 'wps_shipping_summary', array( &$this, 'display_shipping_summary') );
	}
	
	/**
	 * Add CSS and JS files in front-office
	 */
	function add_scripts() {
		//CSS files
		wp_register_style( 'wps_shipping_mode_css', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/frontend/css/wps_shipping_mode.css', false );
		wp_enqueue_style( 'wps_shipping_mode_css' );
		// Javascript Files
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wps_shipping_method_js', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/frontend/js/shipping_method.js', false );
	}
	
	/**
	 * Add JS and CSS files in back-office
	 */
	function add_scripts_in_admin() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		// Javascript files
		wp_enqueue_script( 'wps_shipping_mode_js', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/backend/js/wps_shipping_mode.js', false );
		//CSS files
		wp_register_style( 'wps_shipping_mode_css', WPS_SHIPPING_MODE_URL . WPS_SHIPPING_MODE_DIR .'/assets/backend/css/wps_shipping_mode.css', false );
		wp_enqueue_style( 'wps_shipping_mode_css' );
	}
	
	/** 
	 * Load templates 
	 **/
	function get_template_part( $side, $slug, $name=null ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
	
		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $this->plugin_dirname . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
	
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
	
				if ( file_exists($this->template_dir . $template_name)) {
					$path = $this->template_dir . $template_name;
					break;
				}
			}
		}
	
		return $path;
	}
	
	/** Load module/addon automatically to existing template list
	 *
	 * @param array $templates The current template definition
	 *
	 * @return array The template with new elements
	 */
	function custom_template_load( $templates ) {
		include($this->template_dir.'backend/main_elements.tpl.php');
		include($this->template_dir.'wpshop/main_elements.tpl.php');
		$wpshop_display = new wpshop_display();
		$templates = $wpshop_display->add_modules_template_to_internal( $tpl_element, $templates );
		unset($tpl_element);
	
		return $templates;
	}
	
	/**
	 * Declare option groups for the module
	 */
	function add_options( $option_group ) {
		$option_group['wpshop_shipping_option']['subgroups']['wps_shipping_mode']['class'] = ' wpshop_admin_box_options_shipping_mode';
		return $option_group;
	}
	
	/** 
	 * Create Options 
	 **/
	function create_options() {
		add_settings_section('wps_shipping_mode', __('Shipping method', 'wpshop'), '', 'wps_shipping_mode');
		register_setting('wpshop_options', 'wps_shipping_mode', array( $this, 'wpshop_options_validate_wps_shipping_mode'));
		add_settings_field('wps_shipping_mode', __('Shipping Mode', 'wpshop'), array( $this, 'display_shipping_mode_in_admin'), 'wps_shipping_mode', 'wps_shipping_mode');
	}

	/** 
	 * WPS Shipping mode Option Validator 
	 **/
	function wpshop_options_validate_wps_shipping_mode( $input ) {
		if ( !empty($input['modes']) ) {
			foreach( $input['modes'] as $mode => $mode_det ) {
				/** Custom Shipping rules **/
				$input['modes'][$mode]['custom_shipping_rules']['fees'] = wpshop_shipping::shipping_fees_string_2_array( $input['modes'][$mode]['custom_shipping_rules']['fees'] );
					
				/** Shipping Modes Logo Treatment **/
				if ( !empty($_FILES[$mode.'_logo']['name']) && empty($_FILES[$mode.'_logo']['error']) ) {
					$filename = $_FILES[$mode.'_logo'];
					$upload  = wp_handle_upload($filename, array('test_form' => false));
					$wp_filetype = wp_check_filetype(basename($filename['name']), null );
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
							'guid' => $wp_upload_dir['url'] . '/' . basename( $filename['name'] ),
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => preg_replace(' /\.[^.]+$/', '', basename($filename['name'])),
							'post_content' => '',
							'post_status' => 'inherit'
					);
					$attach_id = wp_insert_attachment( $attachment, $upload['file']);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );
	
					$input['modes'][$mode]['logo'] = $attach_id;
				}
			}
		}
		return $input;
	}
	
	/** 
	 * Migrate Old Shipping Mode to the new storage system
	 **/
	function migrate_default_shipping_mode() {
		$data = array();
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		if ( empty($shipping_mode_option) ) {
			$data['modes']['default_shipping_mode']['active'] = 'on';
			$data['modes']['default_shipping_mode']['name'] = __('Home Delivery', 'wpshop');
			/** Check CUstom Shipping **/
			$custom_shipping_option = get_option( 'wpshop_custom_shipping' );
			if ( !empty($custom_shipping_option) ) {
				$data['modes']['default_shipping_mode']['custom_shipping_rules'] = $custom_shipping_option;
			}
			/** Check Country Limit **/
			$limit_destination = get_option( 'wpshop_limit_shipping_destination' );
			if ( !empty($custom_shipping_option) ) {
				$data['modes']['default_shipping_mode']['limit_destination'] = $limit_destination;
			}
	
			/** Check Others shipping configurations **/
			$wpshop_shipping_rules_option = get_option('wpshop_shipping_rules');
			if ( !empty($wpshop_shipping_rules_option) ){
				if ( !empty($wpshop_shipping_rules_option['min_max']) ) {
					$data['modes']['default_shipping_mode']['min_max'] = $wpshop_shipping_rules_option['min_max'];
				}
				if ( !empty($wpshop_shipping_rules_option['free_from']) ) {
					$data['modes']['default_shipping_mode']['free_from'] = $wpshop_shipping_rules_option['free_from'];
				}
				if ( !empty($wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping']) ) {
					$data['modes']['default_shipping_mode']['free_shipping'] = $wpshop_shipping_rules_option['wpshop_shipping_rule_free_shipping'];
				}
			}
			$data['default_choice'] = 'default_shipping_mode';
	
			update_option( 'wps_shipping_mode', $data );
		}
	}
	
	/**
	 *  Display the Admin Interface for Shipping Mode 
	 **/
	function display_shipping_mode_in_admin() {
		$shipping_mode_option = get_option( 'wps_shipping_mode' );
		$tpl_component = array();
		$tpl_component['INTERFACES'] = '';
		$tpl_component['LOADER_ICON'] = WPSHOP_LOADING_ICON;
		if( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) ){
	
			foreach( $shipping_mode_option['modes'] as $key => $shipping_mode ) {
				$tpl_component['INTERFACES'] .= self::generate_shipping_mode_interface( $key, $shipping_mode );
	
			}
		}
			
		$output = wpshop_display::display_template_element('wps_shipping_mode_main', $tpl_component, array(), 'admin');
		unset( $tpl_component );
		echo $output;
	}
	
	/**
	 * Generate Shipping mode configuration back-office interface
	 * @param string $key
	 * @param array $shipping_mode
	 * @return string
	 */
	function generate_shipping_mode_interface( $key, $shipping_mode ) {
		global $wpdb;
		$tpl_component = array();
			
		$shipping_mode_option = get_option( 'wps_shipping_mode');
		$default_shipping_mode = !empty( $shipping_mode_option['default_choice'] ) ? $shipping_mode_option['default_choice'] : '';
	
		$countries = unserialize(WPSHOP_COUNTRY_LIST);
	
		/** Default Weight Unity **/
		$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
		$query = $wpdb->prepare('SELECT name FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
		$unity = $wpdb->get_var( $query );
			
			
		$sub_tpl_component = $tpl_thickbox_content =  array();
			
		$tpl_thickbox_content['LOADER_ICON'] = WPSHOP_LOADING_ICON;
			
		$sub_tpl_component['DEFAULT_SHIPPING_MODE_ACTIVE'] = ( !empty($default_shipping_mode) && $default_shipping_mode == $key ) ? 'checked="checked"' : '';
		$sub_tpl_component['SHIPPING_MODE_ACTIVE'] = ( !empty( $shipping_mode) && !empty($shipping_mode['active']) ) ? 'checked="checked"' : '';
		$sub_tpl_component['SHIPPING_MODE_NAME'] = $shipping_mode['name'];
		$sub_tpl_component['SHIPPING_MODE_ID'] = $tpl_thickbox_content['SHIPPING_MODE_ID'] = $key;
		$sub_tpl_component['SHIPPING_MODE_THUMBNAIL'] = ( !empty($shipping_mode['logo']) ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail', false, array('class' => 'wps_shipping_mode_logo') ) : '';
		$sub_tpl_component['SHIPPING_MODE_LOGO_POST_ID'] = ( !empty($shipping_mode['logo']) ) ? $shipping_mode['logo'] : '';
			
			
		$tpl_thickbox_content['EXTRA_CONTENT'] = apply_filters('wps_shipping_mode_config_extra_params_'.$key, $key );
			
		/** Free From Config **/
		$tpl_thickbox_content['EXPLANATION'] = !empty($shipping_mode['explanation']) ? $shipping_mode['explanation'] : '';
		$tpl_thickbox_content['FREE_FROM_VALUE'] = !empty($shipping_mode['free_from']) ? $shipping_mode['free_from'] : '';
		$tpl_thickbox_content['ACTIVATE_FREE_SHIPPING_FROM'] = !empty($shipping_mode['activate_free_shipping_from']) ? 'checked="checked"' : '';
		$tpl_thickbox_content['FREE_SHIPPING'] = !empty($shipping_mode['free_shipping']) ? 'checked="checked"' : '';
	
		/** Min-Max Config **/
		$tpl_thickbox_content['MIN_MAX_ACTIVATE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['activate']) ) ? 'checked="checked"' : '';
		$tpl_thickbox_content['ADDITIONNAL_CLASS'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['activate']) ) ? '' : 'wpshopHide';
		$tpl_thickbox_content['MIN_VALUE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['min']) ) ? $shipping_mode['min_max']['min'] : '';
		$tpl_thickbox_content['MAX_VALUE'] = (!empty($shipping_mode['min_max']) && !empty($shipping_mode['min_max']['max']) ) ? $shipping_mode['min_max']['max'] : '';
	
		/** Shipping Limit destination Configuration **/
		$tpl_thickbox_content['COUNTRIES_LIST'] = '';
		if( !empty($countries) ) {
			foreach( $countries as $key => $country) {
					
				$tpl_thickbox_content['COUNTRIES_LIST'] .= '<option value="' .$key. '"' . ( (!empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($key, $shipping_mode['limit_destination']['country']) ) ? 'selected="selected"' : '' ) .'>' .$country. '</option>';
			}
		}
	
	
		/** Custom Shipping Rules COnfiguration **/
		$tpl_thickbox_content['CUSTOM_SHIPPING_FEES_DATA'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) ) ? $shipping_mode['custom_shipping_rules'] : '';
		$tpl_thickbox_content['CUSTOM_SHIPPING_RULES_ACTIVE'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active']) ) ? 'checked="checked"' : '';
		$tpl_thickbox_content['CUSTOM_SHIPPING_ACTIVE_CP'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_cp']) ) ? 'checked="checked"' : '';
		$tpl_thickbox_content['CUSTOM_SHIPPING_ACTIVE_DEPARTMENT'] = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['active_department']) ) ? 'checked="checked"' : '';
	
	
		$tpl_thickbox_content['SHIPPING_WEIGHT_UNITY'] = __($unity, 'wpshop');
	
		$tpl_thickbox_content['CUSTOM_SHIPPING_COUNTRY_LIST'] = '';
		if( !empty($countries) ) {
			foreach( $countries as $key => $country) {
				$tpl_thickbox_content['CUSTOM_SHIPPING_COUNTRY_LIST'] .= '<option value="' .$key. '">' .$country. '</option>';
			}
		}
	
		$tpl_thickbox_content['SHIPPING_MODE_POSTCODE_LIMIT_DESTINATION'] = ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) ? $shipping_mode['limit_destination']['postcode'] : '';
		$tpl_thickbox_content['SHIPPING_MODE_DEPARTMENT_LIMIT_DESTINATION'] = ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['department']) ) ? $shipping_mode['limit_destination']['department'] : '';
			
		$fees_data = ( !empty($shipping_mode) & !empty($shipping_mode['custom_shipping_rules']) && !empty($shipping_mode['custom_shipping_rules']['fees']) ) ? $shipping_mode['custom_shipping_rules']['fees'] : array();
		if(is_array($fees_data)) {
			$fees_data = wpshop_shipping::shipping_fees_array_2_string($fees_data);
		}
		$tpl_thickbox_content['CUSTOM_SHIPPING_FEES_DATA'] = $fees_data;
		$tpl_thickbox_content['CUSTOM_SHIPPING_RULES_DISPLAY'] = self::generate_shipping_rules_table( $fees_data, $tpl_thickbox_content['SHIPPING_MODE_ID'] );
	
		$sub_tpl_component['SHIPPING_MODE_CONFIGURATION_INTERFACE'] = wpshop_display::display_template_element('wps_shipping_mode_configuration_interface', $tpl_thickbox_content, array(), 'admin');
		unset( $tpl_thickbox_content );
		$output = wpshop_display::display_template_element('wps_shipping_mode_each_interface', $sub_tpl_component, array(), 'admin');
		unset( $sub_tpl_component );
		return $output;
	}
	
	
	/** 
	 * Generate cutom fees resume table
	 * @param array $fees_data
	 * @param string $key
	 */
	function generate_shipping_rules_table( $fees_data, $shipping_mode_id ){
		global $wpdb;
		$result = '';
		if ( !empty( $fees_data) ) {
			$shipping_rules = wpshop_shipping::shipping_fees_string_2_array( stripslashes($fees_data) );
			$result = '';
			$tpl_component ='';
			$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] = '';
			$tpl_component['SHIPPING_MODE_ID'] = $shipping_mode_id;
			$country_list = unserialize(WPSHOP_COUNTRY_LIST);
			$weight_defaut_unity_option = get_option ('wpshop_shop_default_weight_unity');
			$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $weight_defaut_unity_option);
			$unity = $wpdb->get_var( $query );
			$currency_defaut_option = get_option ('wpshop_shop_default_currency');
			$query = $wpdb->prepare('SELECT unit FROM '. WPSHOP_DBT_ATTRIBUTE_UNIT . ' WHERE id=%d', $currency_defaut_option);
			$currency = $wpdb->get_var( $query );
			if ( !empty($shipping_rules) ) {
				foreach ( $shipping_rules as $shipping_rule ) {
					$country_name = '';
					$code_country = explode('-', $shipping_rule['destination']);
					$code_country = $code_country[0];
					foreach ( $country_list as $key=>$country ) {
						if (  $key == $code_country ) {
							$country_name = $country;
						}
					}
					if ( !empty($shipping_rule['fees']) ) {
						foreach( $shipping_rule['fees'] as $k=>$fee ) {
							$tpl_line_component['SHIPPING_MODE_ID'] = $shipping_mode_id;
							$tpl_line_component['SHIPPING_RULE_DESTINATION'] = $shipping_rule['destination'];
							$tpl_line_component['SHIPPING_RULE_COUNTRY'] = $country_name;
							$tpl_line_component['SHIPPING_RULE_WEIGHT'] = ($unity == 'kg') ? $k / 1000 : $k;
							$tpl_line_component['SHIPPING_RULE_WEIGHT_UNITY'] = $unity;
							$tpl_line_component['SHIPPING_RULE_FEE'] = $fee;
							$tpl_line_component['SHIPPING_RULE_WEIGHT_CURRENCY'] = $currency;
							$tpl_line_component['MEDIAS_ICON_URL'] = WPSHOP_MEDIAS_ICON_URL;
			
							$tpl_component['CUSTOM_SHIPPING_RULES_LINES'] .= wpshop_display::display_template_element('shipping_rules_table_line', $tpl_line_component, array(), 'admin');
							unset($tpl_line_component);
						}
					}
						
				}
				$result = wpshop_display::display_template_element('shipping_rules_table', $tpl_component, array(), 'admin');
				unset($tpl_component);
			}
		}
		return $result;
	}
	
	/**
	 * Display Available shipping modes for an address
	 */
	function display_shipping_mode() {
		$shipping_modes = $this->generate_shipping_mode_for_an_address();
		$output = wpshop_display::display_template_element('shipping_modes', array( 'SHIPPING_MODES' => $shipping_modes[1] ), array(), 'wpshop');
		$output .= apply_filters( 'wps_additionnal_shipping_mode','' );
		return $output;
	}
	
	/**
	 * Display Shipping modes for an address
	 * @return string
	 */
	function generate_shipping_mode_for_an_address( $address_id = '' ) {
		$output = '';
		$status = false;
		$shipping_address_id = ( !empty($address_id) ) ? $address_id : '';
		$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : $shipping_address_id;
		if ( !empty($shipping_address_id) ) {
			$shipping_mode_option = get_option( 'wps_shipping_mode' );
			$address_metadata = get_post_meta( $shipping_address_id, '_wpshop_address_metadata', true);
			if( !empty( $shipping_mode_option ) && !empty($shipping_mode_option['modes']) ){
				foreach( $shipping_mode_option['modes'] as $k => $shipping_mode ) {
					$tpl_component = array($shipping_mode);
					if ( !empty($shipping_mode) && !empty($shipping_mode['active']) ) {
						/** Check Country Shipping Limitation **/
						if ( empty($shipping_mode['limit_destination']) || ( !empty($shipping_mode['limit_destination']) && empty($shipping_mode['limit_destination']['country']) ) || ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($address_metadata['country'], $shipping_mode['limit_destination']['country']) ) ) {
							/** Check Limit Destination By Postcode **/
							$visible = true;
	
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) {
								$postcodes = explode(',', $shipping_mode['limit_destination']['postcode'] );
								foreach( $postcodes as $postcode_id => $postcode ) {
									$postcodes[ $postcode_id ] = trim( str_replace( ' ', '', $postcode) );
								}
								if ( !in_array($address_metadata['postcode'], $postcodes) ) {
									$visible = false;
								}
							}
	
							$department = substr( $address_metadata['postcode'], 0, 2 );
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['department']) ) {
								$departments = explode(',', $shipping_mode['limit_destination']['department'] );
								foreach( $departments as $department_id => $d ) {
									$departments[ $department_id ] = trim( str_replace( ' ', '', $d) );
								}
	
								if ( !in_array($department, $departments) ) {
									$visible = false;
								}
							}
	
	
							if ( $visible ) {
								$tpl_component['SHIPPING_MODE_SELECTED'] = ( !empty($shipping_mode_option) && !empty($shipping_mode_option['default_choice']) && $shipping_mode_option['default_choice'] == $k ) ? 'checked="checked"' : '';
								$tpl_component['SHIPPING_MODE_LOGO'] = !empty( $shipping_mode['logo'] ) ? wp_get_attachment_image( $shipping_mode['logo'], 'thumbnail', false, array('height' => '40') ) : '';
								$tpl_component['SHIPPING_METHOD_CODE'] = $k;
								$tpl_component['SHIPPING_METHOD_NAME'] = __($shipping_mode['name'], 'wpshop');
								$tpl_component['SHIPPING_METHOD_EXPLANATION'] = !empty($shipping_mode['explanation']) ?  __($shipping_mode['explanation'], 'wpshop')  : '';
								$tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] = apply_filters('wps_shipping_mode_additional_content', $k );
								if ( $tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] == $k ) {
									$tpl_component['WPS_SHIPPING_MODE_ADDITIONAL_CONTENT'] = '';
								}
								$tpl_component['SHIPPING_METHOD_CONTENT'] = '';
								$tpl_component['SHIPPING_METHOD_CONTAINER_CLASS'] = '';
								$output .= wpshop_display::display_template_element('shipping_mode_front_display', $tpl_component, array(), 'wpshop');
								unset( $tpl_component );
								$status = true;
							}
						}
							
					}
				}
					
				if ( empty( $output) ) {
					$output = '<div class="error_bloc">' .__('Sorry ! You can\'t order on this shop, because we don\'t ship in your country.', 'wpshop' ). '</div>';
				}
			}
			else {
				$output .= __('No shipping mode are avalaible for your shipping address.', 'wpshop');
			}
		}
		else {
			$output .= __('The shipping modes will be display when you have register an shipping address.', 'wpshop');
		}
		return array( $status, $output);
	}

	/**
	 * ***********************************************
	 * NEW CHECKOUT TUNNEL FUNCTIONS FOR SHIPPING STEP
	 * *********************************************** 
	 */
	
	/**
	 * Display shipping modes 
	 * @return string
	 */
	function display_shipping_methods() {
		$output = $shipping_methods = ''; $no_shipping_mode_for_area = false;
		$shipping_modes = get_option( 'wps_shipping_mode' );
		if( !empty($_SESSION['shipping_address']) ) {
			$shipping_modes = $this->get_shipping_mode_for_address( $_SESSION['shipping_address'] );
			if( empty($shipping_modes) ) {
				$no_shipping_mode_for_area = true;
			}
		}
		ob_start();
		require_once( $this->get_template_part( "frontend", "shipping-mode", "container") );
		$output = ob_get_contents();
		ob_end_clean();
			
		return $output;
	}
	
	/**
	 * Display a shipping summary( Choosen Shipping & billing address, choosen shipping mode )
	 * @return string
	 */
	function display_shipping_summary() {
		$output = '';
		$billing_address_id = ( !empty($_SESSION['billing_address']) ) ? $_SESSION['billing_address'] : null;
		$shipping_address_id = ( !empty($_SESSION['shipping_address']) ) ? $_SESSION['shipping_address'] : null;
		$shipping_mode = ( !empty($_SESSION['shipping_method']) ) ? $_SESSION['shipping_method'] : null;
			
		if( !empty($billing_address_id)  ) {
			$billing_infos = get_post_meta($billing_address_id, '_wpshop_address_metadata', true);
			$billing_content = wps_address::display_an_address( $billing_infos, $billing_address_id);
	
			if ( !empty($shipping_address_id) && !empty($shipping_mode) ) {
				$shipping_infos = get_post_meta($shipping_address_id, '_wpshop_address_metadata', true);
				$shipping_content = wps_address::display_an_address( $shipping_infos, $shipping_address_id);
					
				$shipping_mode_option = get_option( 'wps_shipping_mode' );
				$shipping_mode = ( !empty($shipping_mode_option) && !empty($shipping_mode_option['modes']) && !empty($shipping_mode_option['modes'][$shipping_mode]) && !empty($shipping_mode_option['modes'][$shipping_mode]['name']) ) ? $shipping_mode_option['modes'][$shipping_mode]['name'] : '';
			}
	
			ob_start();
			require( $this->get_template_part( "frontend", "shipping-infos", "summary") );
			$output = ob_get_contents();
			ob_end_clean();
		}
			
			
		return $output;
	}


	function get_shipping_mode_for_address( $address_id ) {
		$shipping_modes_to_display = array();
		if( !empty($address_id) ) {
			$shipping_modes = get_option( 'wps_shipping_mode' );
			$address_metadata = get_post_meta( $address_id, '_wpshop_address_metadata', true);
			if( !empty( $shipping_modes ) && !empty($shipping_modes['modes']) ){
				foreach( $shipping_modes['modes'] as $k => $shipping_mode ) {
					if ( !empty($shipping_mode) && !empty($shipping_mode['active']) ) {
						/** Check Country Shipping Limitation **/
						if ( empty($shipping_mode['limit_destination']) || ( !empty($shipping_mode['limit_destination']) && empty($shipping_mode['limit_destination']['country']) ) || ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['country']) && in_array($address_metadata['country'], $shipping_mode['limit_destination']['country']) ) ) {
							/** Check Limit Destination By Postcode **/
							$visible = true;
							/** Check Postcode limitation **/
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['postcode']) ) {
								$postcodes = explode(',', $shipping_mode['limit_destination']['postcode'] );
								foreach( $postcodes as $postcode_id => $postcode ) {
									$postcodes[ $postcode_id ] = trim( str_replace( ' ', '', $postcode) );
								}
								if ( !in_array($address_metadata['postcode'], $postcodes) ) {
									$visible = false;
								}
							}
							/** Check Department limitation **/
							$department = substr( $address_metadata['postcode'], 0, 2 );
							if ( !empty($shipping_mode['limit_destination']) && !empty($shipping_mode['limit_destination']['department']) ) {
								$departments = explode(',', $shipping_mode['limit_destination']['department'] );
								foreach( $departments as $department_id => $d ) {
									$departments[ $department_id ] = trim( str_replace( ' ', '', $d) );
								}
					
								if ( !in_array($department, $departments) ) {
									$visible = false;
								}
							}
					
							if ( $visible ) {
								$shipping_modes_to_display['modes'][$k] = $shipping_mode;
							}
						}
					}
				}
			}
		}
		return $shipping_modes_to_display;
	}
	
	
}