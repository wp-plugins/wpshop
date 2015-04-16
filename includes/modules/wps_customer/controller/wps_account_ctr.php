<?php
class wps_account_ctr {
	/** Define the main directory containing the template for the current plugin
	* @var string
	*/
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_ACCOUNT_DIR;

	function __construct() {
		/** Template Load **/
		$this->template_dir = WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . "/templates/";
		/** Shortcodes **/
		// Sign up Display Shortcode
		add_shortcode( 'wps_signup', array( &$this, 'display_signup' ) );
		// Log in Form Display Shortcode
		add_shortcode( 'wpshop_login', array( &$this, 'get_login_form'));
		//Log in first step
		add_shortcode( 'wps_first_login', array( &$this, 'get_login_first_step'));
		// Forgot password Form
		add_shortcode( 'wps_forgot_password', array( &$this, 'get_forgot_password_form'));
		// Renew password form
		add_shortcode( 'wps_renew_password', array( &$this, 'get_renew_password_form'));
		//Account informations
		add_shortcode( 'wps_account_informations', array($this, 'display_account_informations') );
		//Account form
		add_shortcode( 'wps_account_informations_form', array($this, 'account_informations_form') );

		/** Ajax Actions **/
		add_action('wp_ajax_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );
		add_action('wp_ajax_nopriv_wps_display_connexion_form', array(&$this, 'wps_ajax_get_login_form_interface') );

		add_action('wp_ajax_wps_login_request', array(&$this, 'control_login_form_request') );
		add_action('wp_ajax_nopriv_wps_login_request', array(&$this, 'control_login_form_request') );

		add_action('wp_ajax_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );
		add_action('wp_ajax_nopriv_wps_forgot_password_request', array(&$this, 'wps_forgot_password_request') );

		add_action('wp_ajax_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );
		add_action('wp_ajax_nopriv_wps_forgot_password_renew', array(&$this, 'wps_forgot_password_renew') );

		add_action('wp_ajax_wps_signup_request', array(&$this, 'wps_save_signup_form') );
		add_action('wp_ajax_nopriv_wps_signup_request', array(&$this, 'wps_save_signup_form') );

		add_action('wp_ajax_wps_login_first_request', array(&$this, 'wps_login_first_request') );
		add_action('wp_ajax_nopriv_wps_login_first_request', array(&$this, 'wps_login_first_request') );

		add_action( 'wp_ajax_wps_save_account_informations', array($this, 'wps_save_account_informations') );

		add_action( 'wp_ajax_wps_account_reload_informations', array($this, 'wps_account_reload_informations') );

		add_action( 'wp_ajax_wps_fill_forgot_password_modal', array($this, 'wps_fill_forgot_password_modal') );
		add_action( 'wp_ajax_nopriv_wps_fill_forgot_password_modal', array($this, 'wps_fill_forgot_password_modal') );

		add_action( 'wp_ajax_wps_fill_account_informations_modal', array($this, 'wps_fill_account_informations_modal') );
		add_action( 'wp_ajax_nopriv_wps_fill_account_informations_modal', array($this, 'wps_fill_account_informations_modal') );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts') );
	}

	/**
	 * Add scripts
	 */
	function add_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'wps_forgot_password_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_forgot_password.js' );
		wp_enqueue_script( 'wps_login_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_login.js' );
		wp_enqueue_script( 'wps_signup_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_signup.js' );
		wp_enqueue_script( 'wps_account_js', WPS_ACCOUNT_URL.'wps_customer/assets/frontend/js/wps_account.js' );
	}

	/** LOG IN - Display log in Form **/
	function get_login_form( $force_login = false ) {
		$args = array();
		if ( get_current_user_id() != 0 ) {
			return __( 'You are already logged', 'wpshop');
		}
		else {
			if ( !empty($_GET['action']) && $_GET['action'] == 'retrieve_password' && !empty($_GET['key']) && !empty($_GET['login']) && !$force_login ) {
				$output = self::get_renew_password_form();
			}
			else {
				ob_start();
				require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir, "frontend", "login/login-form") );
				$output = ob_get_contents();
				ob_end_clean();
				if ( !$force_login ) {
					$output .= do_shortcode( '[wps_signup]' );
				}
			}
			return $output;
		}
	}

	/** LOG IN - AJAX - Action to connect **/
	function control_login_form_request() {
		$result = '';
		$status = false;
		$origin = $_POST['wps-checking-origin'];
		$page_account_id = wpshop_tools::get_page_id( get_option( 'wpshop_myaccount_page_id') );
		if ( !empty($_POST['wps_login_user_login']) && !empty($_POST['wps_login_password']) ) {
			$creds = array();
			// Test if an user exist with this login
			$user_checking = get_user_by( 'login', $_POST['wps_login_user_login']);
			if( !empty($user_checking) ) {
				$creds['user_login'] = sanitize_user($_POST['wps_login_user_login']);
			}
			else {
				if ( is_email($_POST['wps_login_user_login']) ) {
					$user_checking = get_user_by( 'email', $_POST['wps_login_user_login']);
					$creds['user_login'] = $user_checking->user_login;
				}
			}
			$creds['user_password'] = wpshop_tools::varSanitizer( $_POST['wps_login_password'] );
			$creds['remember'] =  ( !empty($_POST['wps_login_remember_me']) ) ? true : false;
			$user = wp_signon( $creds, false );
			if ( is_wp_error($user) ) {
				$result = '<div class="wps-alert-error">' .__('Connexion error', 'wpshop'). '</div>';
			}
			else {
				$permalink_option = get_option( 'permalink_structure' );
				$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ) );
				if( $origin == $page_account_id ) {
					$result = get_permalink( $page_account_id );
				}
				else {
					$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
				}
				$status = true;
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('E-Mail and Password are required', 'wpshop'). '</div>';
		}

		echo json_encode( array( $status, $result) );
		die();
	}

	/**
	 * LOG IN - AJAX - Display log in Form in Ajax
	 */
	function wps_ajax_get_login_form_interface() {
		$response = array( 'status' => true, 'response' => self::get_login_form() );
		echo json_encode( $response );
		die();
	}

	/** LOG IN - Display first login step **/
	function get_login_first_step() {
		$output = '';
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "login/login-form", "first") );
		$output .= ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * LOG IN - First Step log in request
	 */
	function wps_login_first_request() {
		$status = false; $login_action = false; $response = '';
		$user_email = ( !empty($_POST['email_address']) ) ? wpshop_tools::varSanitizer( $_POST['email_address'] ) : null;
		if ( !empty($user_email) ) {
			$status = true;
			/** Check if a user exist with it's email **/
			$checking_user = get_user_by( 'login', $user_email);
			if ( !empty($checking_user) ) {
				$login_action = true;
				$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
				$response = $user_firstname;
			}
			else {
				$checking_user = get_user_by( 'email', $user_email);
				if ( !empty( $checking_user ) ) {
					$login_action = true;
					$user_firstname = get_user_meta( $checking_user->ID, 'first_name', true );
					$response = $user_firstname;
				}
			}

			if( !$login_action && is_email($user_email)  ) {
				$response = $user_email;
			}
		}
		else {
			$response = '<div class="wps-alert-error">' .__( 'An e-mail address is required', 'wpshop' ). '</div>';
		}
		echo json_encode( array( 'status'=> $status, 'response' => $response, 'login_action' => $login_action) );
		die();
	}

	/**
	 * FORGOT PASSWORD - Display the forgot Password Form
	 */
	function get_forgot_password_form() {
		$output = '';
		if ( get_current_user_id() == 0 ) {
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir, "frontend", "forgot-password/forgot-password") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * FORGOT PASSWORD - AJAX - Fill the forgot password modal
	 */
	function wps_fill_forgot_password_modal() {
		$status = false; $title = $content = '';
		$title = __( 'Forgot password', 'wpshop' );
		$content = do_shortcode('[wps_forgot_password]');
		$status = true;
		echo json_encode( array('status' => $status, 'title' => $title, 'content' => $content) );
		wp_die();
	}

	/**
	 * FORGOT PASSWORD- AJAX - Forgot Password Request
	 */
	function wps_forgot_password_request() {
		global $wpdb;
		$status = false; $result = '';
		$user_login = ( !empty( $_POST['wps_user_login']) ) ? wpshop_tools::varSanitizer($_POST['wps_user_login']) : null;
		if ( !empty($user_login) ) {
			$existing_user = false;
			$exist_user = get_user_by('login', $user_login);
			if( !empty($exist_user) ) {
				$existing_user = true;
			}
			else {
				$exist_user = get_user_by('email', $user_login);
				if ( !empty($exist_user) ) {
					$existing_user = true;
				}
			}

			if ( $existing_user ) {
				$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
				if ( empty($key) ) {
					$key = wp_generate_password(20, false);
					$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
				}
				$this->send_forgot_password_email($key, $user_login, $exist_user);
				$result = '<div class="wps-alert-info">' .__('An e-mail with an password renew link has been sent to you', 'wpshop'). '</div>';
				$status = true;
			}
			else {
				$result = '<div class="wps-alert-error">' .__('No customer account corresponds to this email', 'wpshop'). '</div>';
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('Please fill the required field', 'wpshop'). '</div>';
		}
		$response = array( $status, $result );
		echo json_encode( $response );
		die();
	}

	/**
	 * FORGOT PASSWORD - Send Forgot Password Email Initialisation
	 * @param string $key
	 * @param string $user_login
	 */
	function send_forgot_password_email($key, $user_login, $exist_user){
		$user_data = $exist_user->data;
		$email = $user_data->user_email;
		$wps_message = new wps_message_ctr();
		$first_name = get_user_meta( $user_data->ID, 'first_name', true );
		$last_name = get_user_meta( $user_data->ID, 'last_name', true );
		$permalink_option = get_option( 'permalink_structure' );
		$link = '<a href="' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ).( (!empty($permalink_option)) ? '?' : '&').'order_step=2&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '">' .get_permalink( wpshop_tools::get_page_id( get_option('wpshop_checkout_page_id') ) ). '&action=retrieve_password&key=' .$key. '&login=' .rawurlencode($user_login). '</a>';
		if( !empty($key) && !empty( $user_login ) ) {
			$wps_message->wpshop_prepared_email($email,
			'WPSHOP_FORGOT_PASSWORD_MESSAGE',
			array( 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'forgot_password_link' => $link)
			);
		}
	}

	/** FORGOT PASSWORD - AJAX - Make renew password action **/
	function wps_forgot_password_renew() {
		global $wpdb;
		$status = false; $result = $form = '';
		$password = ( !empty( $_POST['pass1']) ) ? wpshop_tools::varSanitizer( $_POST['pass1'] ) : null;
		$confirm_password = ( !empty( $_POST['pass2']) ) ? wpshop_tools::varSanitizer( $_POST['pass2'] ) : null;
		$activation_key = ( !empty( $_POST['activation_key']) ) ?  wpshop_tools::varSanitizer( $_POST['activation_key'] ) : null;
		$login = ( !empty( $_POST['user_login']) ) ?  wpshop_tools::varSanitizer( $_POST['user_login'] ) : null;
		if ( !empty($password) && !empty($confirm_password) && $confirm_password == $password ) {
			if ( !empty($activation_key) && !empty($login) ) {
				$existing_user = false;
				$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $activation_key, $login ) );
				if( empty($user) ) {
					$existing_user = true;
				}
				else {
					$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_email = %s", $activation_key, $login ) );
					if( !empty($user) ) {
						$existing_user = true;
					}
				}

				if ( $existing_user ){
					wp_set_password($password, $user->ID);
					wp_password_change_notification($user);
					$status = true;
					$result = '<div class="wps-alert-success">' .__('Your password has been updated', 'wpshop'). '. <a href="#" id="display_connexion_form"> ' .__('Connect you', 'wpshop').' !</a></div>';
					$form = self::get_login_form( true );
				}
				else {
					$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
				}
			}
			else {
				$result = '<div class=" wps-alert-error">' .__('Invalid activation key', 'wpshop'). '</div>';
			}
		}
		else {
			$result = '<div class="wps-alert-error">' .__('Password and confirmation password are differents', 'wpshop'). '</div>';
		}

		$response = array( $status, $result, $form );
		echo json_encode( $response);
		die();
	}

	/**
	 * FORGOT PASSWORD - Display renew password interface
	 * @return string
	 */
	function get_renew_password_form() {
		if ( get_current_user_id() == 0 ) {
			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "forgot-password/password-renew") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/** FORGOT PASSWORD - AJAX - Get Forgot Password form **/
	function wps_ajax_get_forgot_password_form() {
		echo json_encode( array(self::get_forgot_password_form() ) );
		die();
	}

	/**
	 * SIGN UP - Display Sign up form
	 * @return string
	 */
	function display_signup( $args = array() ) {
		global $wpdb;
		$output = '';
		if ( get_current_user_id() == 0 || !empty($args) ) {
			$fields_to_output = $signup_fields = array();

			$password_attribute = $signup_form_attributes =  array();

			$entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );

			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $entity_id);
			$customer_entity_id = $wpdb->get_var( $query );
			$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
			$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
			$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid' );
			$customer_attributes_sections = $wpdb->get_results( $query );
			foreach( $customer_attributes_sections as $k => $customer_attributes_section ) {
				foreach( $account_attributes[$customer_attributes_section->id]['attribut'] as $attribute ) {
					$signup_fields[] = $attribute;
				}
			}
			ob_start();
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "signup/signup") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * SIGN UP - Save sign up form
	 */
	function wps_save_signup_form() {
		global $wpdb, $wpshop;
		$user_id = ( !empty($_POST['wps_sign_up_request_from_admin']) ) ? 0 : get_current_user_id();
		$wps_message = new wps_message_ctr();
		$status = $account_creation = false; $result = '';
		$exclude_user_meta = array( 'user_email', 'user_pass' );
		$element_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		if ( !empty( $element_id) ){
			$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
			$attribute_set_id = $wpdb->get_var( $query );
			if ( !empty($attribute_set_id) ){
				$group  = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						if( !empty($_POST['wps_sign_up_request_from_admin']) ) {
							foreach( $attribute_set_field['content'] as $attribute_code => $att_def ) {
								if( $attribute_code != 'account_user_email' ) {
									 $attribute_set_field['content'][$attribute_code]['required'] = 'no';
								}
							}
						}
						$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'] );
					}
					if ( empty($wpshop->errors) ) {
						$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
						$user_pass = ( !empty($_POST['attribute']['varchar']['user_pass']) && !empty($_POST['wps_signup_account_creation']) ) ? $_POST['attribute']['varchar']['user_pass'] : wp_generate_password( 12, false );

						if ( $user_id == 0  ) {
							$user_id = wp_create_user($user_name, $user_pass, $_POST['attribute']['varchar']['user_email']);
							if ( !is_object( $user_id) ) {
								$account_creation = true;
								/** Update newsletter user preferences **/
								$newsletter_preferences = array();
								if( !empty($_POST['newsletters_site']) ) {
									$newsletter_preferences['newsletters_site'] = 1;
								}
								if( !empty($_POST['newsletters_site_partners']) ) {
									$newsletter_preferences['newsletters_site_partners'] = 1;
								}

								update_user_meta( $user_id, 'user_preferences', $newsletter_preferences);
							}
						}

						$customer_entity_request = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id);
						$customer_entity_id = $wpdb->get_var( $customer_entity_request );

						//Save attributes
						if( !empty($_POST['attribute']) ) {

							foreach( $_POST['attribute'] as $att_type => $atts ) {
								foreach( $atts as $att_code => $att_value ) {
									$q = $wpdb->prepare( 'SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE code = %s', $att_code );
									$attribute_id = $wpdb->get_var( $q );

									if( !empty($attribute_id) ) {
										$t = $wpdb->insert( WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($att_type),
											 array( 'entity_type_id' => $element_id, 'attribute_id' => $attribute_id, 'entity_id' => $customer_entity_id, 'user_id' => $user_id, 'creation_date_value' => current_time( 'mysql', 0), 'language' => 'fr_FR', 'value' => $att_value )
										);
									}
								}
							}
						}


						foreach( $attribute_set_field['content'] as $attribute ) {
							if ( !in_array( $attribute['name'], $exclude_user_meta ) ) {
								update_user_meta( $user_id, $attribute['name'], wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']])  );
							}
							else {
								wp_update_user( array('ID' => $user_id, $attribute['name'] => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) ) );
							}
						}

						if ( !empty( $_SESSION ) && !empty( $_SESSION[ 'cart' ] ) ) {
							$permalink_option = get_option( 'permalink_structure' );
							$checkout_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_checkout_page_id' ));
							$result = get_permalink( $checkout_page_id  ).( ( !empty($permalink_option) ) ? '?' : '&').'order_step=3';
						}
						else {
							$account_page_id = wpshop_tools::get_page_id( get_option( 'wpshop_myaccount_page_id' ));
							$result = get_permalink( $account_page_id  );
						}
						$status = true;

						if ( $account_creation && empty($_POST['wps_sign_up_request_from_admin']) ) {
							$secure_cookie = is_ssl() ? true : false;
							wp_set_auth_cookie($user_id, true, $secure_cookie);
						}
						$wps_message->wpshop_prepared_email($_POST['attribute']['varchar']['user_email'], 'WPSHOP_SIGNUP_MESSAGE', array('customer_first_name' => ( !empty($_POST['attribute']['varchar']['first_name']) ) ? $_POST['attribute']['varchar']['first_name'] : '', 'customer_last_name' => ( !empty($_POST['attribute']['varchar']['last_name']) ) ? $_POST['attribute']['varchar']['last_name'] : '', 'customer_user_email' => ( !empty($_POST['attribute']['varchar']['user_email']) ) ? $_POST['attribute']['varchar']['user_email'] : '') );

					}
					else {
						$result = '<div class="wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
						foreach(  $wpshop->errors as $error ){
							$result .= '<li>' .$error. '</li>';
						}
						$result .= '</div>';
					}
				}

			}
		}
		echo json_encode( array( $status, $result, $user_id) );
		die();
	}

	/** SIGN UP - Display the commercial & newsletter form
	 * @return void
	 */
	function display_commercial_newsletter_form() {
		$output = '';
		$user_preferences = get_user_meta( get_current_user_id(), 'user_preferences', true );
		$wpshop_cart_option = get_option( 'wpshop_cart_option' );
		ob_start();
		require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir, "frontend", "signup/signup", "newsletter") );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * ACCOUNT - Display Account informations
	 * @return string
	 */
	function display_account_informations( $customer_id = '' ) {
		global $wpdb;
		$output = $attributes_sections_tpl = $attribute_details = '';
		$is_from_admin = ( !empty($customer_id) ) ? true : false;
		$customer_id = ( !empty($customer_id) ) ? $customer_id : get_current_user_id();
		if( $customer_id != 0 ) {
			$customer_entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $customer_id );
			$cid = $wpdb->get_var( $query );

			if( !empty($customer_entity_id) ) {
				$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d AND status = %s AND default_set = %s', $customer_entity_id, 'valid', 'yes' );
				$attributes_sets = $wpdb->get_results( $query );
				foreach( $attributes_sets as $attributes_set ) {
					if( !empty($attributes_set->id) ) {
						$query = $wpdb->prepare( 'SELECT * FROM '. WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid');
						$attributes_sections = $wpdb->get_results( $query );

						if( !empty($attributes_sections) ) {
							foreach( $attributes_sections as $attributes_section ) {
								$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE status = %s AND entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d', 'valid', $customer_entity_id, $attributes_set->id, $attributes_section->id);
								$attributes_details = $wpdb->get_results( $query );

								foreach( $attributes_details as $attributes_detail ) {
									$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE. ' WHERE id = %d AND status = %s', $attributes_detail->attribute_id, 'valid' );
									$attribute_def = $wpdb->get_row( $query );

									$query = $wpdb->prepare( 'SELECT value  FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($attribute_def->data_type). ' WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d ', $customer_entity_id, $attribute_def->id, $cid );
									$attribute_value = $wpdb->get_var( $query );

									/**	Check attribute type for specific type display	*/
									if ( "datetime" == $attribute_def->data_type ) {
										$attribute_value = mysql2date( get_option( 'date_format' ) . ( ( substr( $attribute_value, -9 ) != ' 00:00:00' ) ? ' ' . get_option( 'time_format' ) : '' ), $attribute_value, true);
									}

									/**	Check attribute input type in order to get specific value	*/
									if ( in_array( $attribute_def->backend_input, array( 'multiple-select', 'select', 'radio', 'checkbox' ) ) ) {
										if ( $attribute_def->data_type_to_use == 'custom' ) {
											$query = $wpdb->prepare("SELECT label FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d AND status = 'valid' AND id = %d", $attribute_def->id, $attribute_value );
											$attribute_value = $wpdb->get_var( $query );
										}
										else if ( $attribute_def->data_type_to_use == 'internal')  {
											$associated_post = get_post( $atribute_value );
											$attribute_value = $associated_post->post_title;
										}
									}

									if( !empty( $attribute_def ) ) {
										if( $attribute_def->frontend_input != 'password' ) {
											ob_start();
											require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "account/account_informations_element") );
											$attribute_details .= ob_get_contents();
											ob_end_clean();
										}
									}
								}

								ob_start();
								require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "account/account_informations_group_element") );
								$attributes_sections_tpl .= ob_get_contents();
								ob_end_clean();
							}

						}
					}
				}
			}



			ob_start();
			require_once( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir, "frontend", "account/account_informations") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * ACCOUNT - Edit account informations data
	 */
	function account_informations_form() {
		global $wpdb;
		$output = '';
		if ( get_current_user_id() != 0 ) {
			// Customer ID data
				$customer_entity_type_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
				$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, get_current_user_id() );
				$cid = $wpdb->get_var( $query );

				$fields_to_output = $signup_fields = array();

				$password_attribute = $signup_form_attributes =  array();

				$entity_id = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );

				$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_SET.' WHERE entity_id = %d', $entity_id);
				$customer_entity_id = $wpdb->get_var( $query );
				$attributes_set = wpshop_attributes_set::getElement($customer_entity_id);
				$account_attributes = wpshop_attributes_set::getAttributeSetDetails( ( !empty($attributes_set->id) ) ? $attributes_set->id : '', "'valid'");
				$query = $wpdb->prepare('SELECT id FROM '.WPSHOP_DBT_ATTRIBUTE_GROUP.' WHERE attribute_set_id = %d', $attributes_set->id );
				$customer_attributes_sections = $wpdb->get_results( $query );
				foreach( $customer_attributes_sections as $k => $customer_attributes_section ) {
					if ( !empty( $account_attributes[$customer_attributes_section->id] ) ) {
						foreach( $account_attributes[$customer_attributes_section->id]['attribut'] as $attribute ) {
							$signup_fields[] = $attribute;
						}
					}
				}

			ob_start();
			require( wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, $this->template_dir,"frontend", "account/account_form") );
			$output = ob_get_contents();
			ob_end_clean();
		}
		return $output;
	}

	/**
	 * ACCOUNT - Save account informations
	 */
	function wps_save_account_informations () {
		global $wpdb; global $wpshop;
		$status = false; $response = '';

		$exclude_user_meta = array( 'user_email', 'user_pass' );
		$wps_entities = new wpshop_entities();
		$element_id = $wps_entities->get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );

		$user_id = get_current_user_id();

		if ( !empty( $element_id) && !empty($user_id) ) {
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $user_id );
			$cid = $wpdb->get_var( $query );

			$query = $wpdb->prepare('SELECT id FROM ' .WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d', $element_id );
			$attribute_set_id = $wpdb->get_var( $query );
			if ( !empty($attribute_set_id) ) {
				$group  = wps_address::get_addresss_form_fields_by_type( $attribute_set_id );
				foreach ( $group as $attribute_sets ) {
					foreach ( $attribute_sets as $attribute_set_field ) {
						$validate = $wpshop->validateForm($attribute_set_field['content'], $_POST['attribute'] );
						if ( empty($wpshop->errors) ) {
							$user_name = !empty($_POST['attribute']['varchar']['user_login']) ? $_POST['attribute']['varchar']['user_login'] : $_POST['attribute']['varchar']['user_email'];
							$user_pass = ( !empty($_POST['attribute']['varchar']['user_pass']) ) ? $_POST['attribute']['varchar']['user_pass'] : '';

							$wpshop_attributes = new wpshop_attributes();
							foreach( $attribute_set_field['content'] as $attribute ) {
								$attribute_def = wpshop_attributes::getElement( $attribute['name'], "'valid'", 'code');
								if ( !in_array( $attribute['name'], $exclude_user_meta ) ) {
									update_user_meta( $user_id, $attribute['name'], wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']])  );
								}
								else {
									wp_update_user( array('ID' => $user_id, $attribute['name'] => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) ) );
								}

								//Save data in attribute tables, ckeck first if exist to know if Insert or Update
								$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($attribute['data_type']). ' WHERE entity_type_id = %d AND entity_id = %d AND attribute_id = %d', $element_id, $cid, $attribute_def->id );
								$checking_attribute_exist = $wpdb->get_results( $query );
								if( !empty( $checking_attribute_exist) ) {
									$wpdb->update(
											WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($attribute['data_type']),
											array( 'value' => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) ),
											array( 'entity_type_id' => $element_id,  'entity_id' => $cid, 'attribute_id' => $attribute_def->id)
											);
								}
								else {
									$wpdb->insert(
											WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.strtolower($attribute['data_type']),
											array( 'entity_type_id' => $element_id, 'attribute_id' => $attribute_def->id, 'entity_id' => $cid, 'user_id' => $user_id, 'creation_date_value' => current_time( 'mysql', 0), 'language' => 'fr_FR', 'value' => wpshop_tools::varSanitizer( $_POST['attribute'][$attribute['data_type']][$attribute['name']]) )
											);
								}

							}


							/** Update newsletter user preferences **/
							$newsletter_preferences = array();
							if( !empty($_POST['newsletters_site']) ) {
								$newsletter_preferences['newsletters_site'] = 1;
							}
							if( !empty($_POST['newsletters_site_partners']) ) {
								$newsletter_preferences['newsletters_site_partners'] = 1;
							}
							update_user_meta( $user_id, 'user_preferences', $newsletter_preferences);

							$status = true;
						}
						else {

							$response = '<div class="wps-alert-error">' .__('Some errors have been detected', 'wpshop') . ' : <ul>';
							foreach(  $wpshop->errors as $error ){
								$response .= '<li>' .$error. '</li>';
							}
							$response .= '</div>';
						}
					}
				}
			}
		}
		echo json_encode( array( 'status' => $status, 'response' => $response) );
		wp_die();
	}

	/**
	 * ACCOUNT - AJAX - Reload account informations data
	 */
	function wps_account_reload_informations() {
		$status = false;
		$response = do_shortcode('[wps_account_informations]');
		if( !empty($response) ) {
			$status = true;
		}
		echo json_encode( array('status' => $status, 'response' => $response) );
		wp_die();
	}

	/**
	 * ACCOUNT - AJAX - Fill account informations modal
	 */
	function wps_fill_account_informations_modal() {
		$title = $content = '';
		$title = __('Edit your account informations', 'wpshop');
		$content = do_shortcode( '[wps_account_informations_form]' );
		echo json_encode( array( 'status' => true, 'title' => $title, 'content' => $content) );
		wp_die();
	}

}