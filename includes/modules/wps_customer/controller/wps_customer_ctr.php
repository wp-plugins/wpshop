<?php
/**
 * Manage Customer general and front-end functions
 * @author ALLEGRE Jérôme - EOXIA
 *
 */
class wps_customer_ctr {

	function __construct() {
		/**	Create customer entity type on wordpress initilisation*/
		add_action( 'init', array( $this, 'create_customer_entity' ) );

		/**	Call style for administration	*/
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_css' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box_to_customer_entity') );
		add_action( 'admin_init', array( $this, 'customer_action_on_plugin_init'));

		/**	When a wordpress user is created, create a customer (post type)	*/
		add_action( 'user_register', array( $this, 'create_entity_customer_when_user_is_created') );

		/**	Add filters for customer list	*/
		add_filter( 'bulk_actions-edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, array( $this, 'customer_list_table_bulk_actions' ) );
		add_filter( 'manage_edit-' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_columns', array( $this, 'list_table_header' ) );
		add_action( 'manage_' . WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS . '_posts_custom_column' , array( $this, 'list_table_column_content' ), 10, 2 );
		add_action( 'restrict_manage_posts', array(&$this, 'list_table_filters') );
		//add_filter( 'parse_query', array(&$this, 'list_table_filter_parse_query') );

		/**	Filter search for customers	*/
		//add_filter( 'pre_get_posts', array( $this, 'customer_search' ) );

		/** Customer options for the shop */
		add_action('wsphop_options', array(&$this, 'declare_options'), 8);
	}

	/**
	 * Customer options for the shop
	 */
	public static function declare_options() {
		if((WPSHOP_DEFINED_SHOP_TYPE == 'sale') && !isset($_POST['wpshop_shop_type']) || (isset($_POST['wpshop_shop_type']) && ($_POST['wpshop_shop_type'] != 'presentation')) && !isset($_POST['old_wpshop_shop_type']) || (isset($_POST['old_wpshop_shop_type']) && ($_POST['old_wpshop_shop_type'] != 'presentation'))){
			register_setting('wpshop_options', 'wpshop_cart_option', array('wps_customer_ctr', 'wpshop_options_validate_customers_newsleters'));
			add_settings_field('display_newsletters_subscriptions', __('Display newsletters subscriptions', 'wpshop'), array('wps_customer_ctr', 'display_newsletters_subscriptions'), 'wpshop_cart_info', 'wpshop_cart_info');
		}
	}

	/**
	 * Validate Options Customer
	 * @param unknown_type $input
	 * @return unknown
	 */
	public static function wpshop_options_validate_customers_newsleters( $input ) {
		return $input;
	}

	public static function display_newsletters_subscriptions() {
		$cart_option = get_option('wpshop_cart_option', array());
		$output = '';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_display_newsletter_site_subscription';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = !empty($cart_option['display_newsletter']['site_subscription']) ? $cart_option['display_newsletter']['site_subscription'][0] : 'no';
		$input_def['possible_value'] = 'yes';
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[display_newsletter][site_subscription]') . '<label for="' . $input_def['id'] . '">' . __( 'Newsletters of the site', 'wpshop' ) . '</label>' . '<a href="#" title="'.__('Check this box if you want display newsletter site subscription','wpshop').'" class="wpshop_infobulle_marker">?</a>' . '<br>';

		$input_def = array();
		$input_def['name'] = '';
		$input_def['id'] = 'wpshop_cart_option_display_newsletter_partner_subscription';
		$input_def['type'] = 'checkbox';
		$input_def['valueToPut'] = 'index';
		$input_def['value'] = !empty($cart_option['display_newsletter']['partner_subscription']) ? $cart_option['display_newsletter']['partner_subscription'][0] : 'no';
		$input_def['possible_value'] = 'yes';
		$output .= wpshop_form::check_input_type($input_def, 'wpshop_cart_option[display_newsletter][partner_subscription]') . '<label for="' . $input_def['id'] . '">' . __( 'Newsletters of the partners', 'wpshop' ) . '</label>' . '<a href="#" title="'.__('Check this box if you want display newsletter partners subscription','wpshop').'" class="wpshop_infobulle_marker">?</a>' . '<br>';

		echo $output;
	}

	/**
	 * Include stylesheets
	 */
	function admin_css() {
		wp_register_style( 'wpshop-modules-customer-backend-styles', WPS_ACCOUNT_URL . '/' . WPS_ACCOUNT_DIR . '/assets/backend/css/backend.css', '', WPSHOP_VERSION );
		wp_enqueue_style( 'wpshop-modules-customer-backend-styles' );
	}

	/**
	 * Add Meta Box to Customer Entity
	 */
	function add_meta_box_to_customer_entity() {
		add_meta_box( 'wps_customer_informations', __( 'Customer\'s account informations', 'wpshop' ), array( $this, 'wps_customer_account_informations' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
		add_meta_box( 'wps_customer_orders', __( 'Customer\'s orders', 'wpshop' ), array( $this, 'wps_customer_orders_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
		add_meta_box( 'wps_customer_addresses_list', __( 'Customer\'s addresses', 'wpshop' ), array( $this, 'wps_customer_addresses_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'normal', 'low' );
		add_meta_box( 'wps_customer_messages_list', __( 'Customer\'s send messages', 'wpshop' ), array( $this, 'wps_customer_messages_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
		add_meta_box( 'wps_customer_coupons_list', __( 'Customer\'s coupons list', 'wpshop' ), array( $this, 'wps_customer_coupons_list' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
	}

	/**
	 * META-BOX CONTENT - Display customer's order list in customer back-office interface
	 */
	function wps_customer_orders_list() {
		global $post;
		$output = '';
		$wps_orders = new wps_orders_ctr();
		$output = $wps_orders->display_orders_in_account( $post->post_author);
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display Customer's addresses in customer back-office interface
	 */
	function wps_customer_addresses_list() {
		global $post;
		$output = '';
		$wps_addresses = new wps_address();
		$output = $wps_addresses->display_addresses_interface( $post->post_author );
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display customer's send messages
	 */
	function wps_customer_messages_list() {
		global $post;
		$wps_messages = new wps_message_ctr();
		$output = $wps_messages->display_message_histo_per_customer( array(),$post->post_author);
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display wps_customer's coupons list
	 */
	function wps_customer_coupons_list() {
		global $post;
		$wps_customer = new wps_coupon_ctr();
		$output = $wps_customer->display_coupons( $post->post_author );
		echo $output;
	}

	/**
	 * META-BOX CONTENT - Display Customer's account informations in administration panel
	 */
	function wps_customer_account_informations() {
		global $post;
		$wps_account = new wps_account_ctr();
		$output = $wps_account->display_account_informations( $post->post_author );
		echo $output;
	}

	/**
	 * Return a list  of users
	 * @param array $customer_list_params
	 * @param integer $selected_user
	 * @param boolean $multiple
	 * @param boolean $disabled
	 * @return string
	 */
	function custom_user_list($customer_list_params = array('name'=>'user[customer_id]', 'id'=>'user_customer_id'), $selected_user = "", $multiple = false, $disabled = false) {
		$content_output = '';

		// USERS
		$wps_customer_mdl = new wps_customer_mdl();
		$users = $wps_customer_mdl->getUserList();
		$select_users = '';
		if( !empty($users) ) {
			foreach($users as $user) {
				if ($user->ID != 1) {
					$lastname = get_user_meta( $user->ID, 'last_name', true );
					$firstname = get_user_meta( $user->ID, 'first_name', true );
					$select_users .= '<option value="'.$user->ID.'"' . ( ( !$multiple ) && ( $selected_user == $user->ID ) ? ' selected="selected"' : '') . ' >'.$lastname. ' ' .$firstname.' ('.$user->user_email.')</option>';
				}
			}
			$content_output = '
			<select name="' . $customer_list_params['name'] . '" id="' . $customer_list_params['id'] . '" data-placeholder="' . __('Choose a customer', 'wpshop') . '" class="chosen_select"' . ( $multiple ? ' multiple="multiple" ' : '') . '' . ( $disabled ? ' disabled="disabled" ' : '') . '>
				<option value="0" ></option>
				'.$select_users.'
			</select>';
		}
		return $content_output;
	}

	/**
	 * Action on plug-on action
	 */
	public static function customer_action_on_plugin_init() {
		global $wpdb;
		$user_meta_for_wpshop = array('metaboxhidden_'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

		/*	Get user list from user meta	*/
		$query = "SELECT ID FROM {$wpdb->users}";
		$user_list = $wpdb->get_results($query);

		/*	Get the different meta needed for user in wpshop	*/
		foreach ($user_list as $user) {
			/*	Check if meta exist for each user	*/
			foreach($user_meta_for_wpshop as $meta_to_check){
				$query = $wpdb->prepare("SELECT meta_value FROM ".$wpdb->usermeta." WHERE user_id=%d AND meta_key=%s", $user->ID, $meta_to_check);
				$meta_value = $wpdb->get_var($query);
				if(empty($meta_value)){
					update_user_meta($user->ID, $meta_to_check, unserialize(WPSHOP_PRODUCT_HIDDEN_METABOX));
				}
			}
		}
		return;
	}



	/**
	 * Create the customer entity
	 */
	function create_customer_entity() {
		global $wpdb;
		$query = $wpdb->prepare( "SELECT P.post_title, PM.meta_value FROM {$wpdb->posts} AS P INNER JOIN {$wpdb->postmeta} AS PM ON (PM.post_id = P.ID) WHERE P.post_name = %s AND PM.meta_key = %s", WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, '_wpshop_entity_params' );
		$customer_entity_definition = $wpdb->get_row( $query );
		$current_entity_params = !empty( $customer_entity_definition ) && !empty( $customer_entity_definition->meta_value ) ? unserialize( $customer_entity_definition->meta_value ) : null;

		$post_type_params = array(
			'labels' => array(
				'name'					=> __( 'Customers' , 'wpshop' ),
				'singular_name' 		=> __( 'Customer', 'wpshop' ),
				'add_new_item' 			=> __( 'New customer', 'wpshop' ),
				'add_new' 				=> __( 'New customer', 'wpshop' ),
				'edit_item' 			=> __( 'Edit customer', 'wpshop' ),
				'new_item' 				=> __( 'New customer', 'wpshop' ),
				'view_item' 			=> __( 'View customer', 'wpshop' ),
				'search_items' 			=> __( 'Search in customers', 'wpshop' ),
				'not_found' 			=> __( 'No customer found', 'wpshop' ),
				'not_found_in_trash' 	=> __( 'No customer founded in trash', 'wpshop' ),
				'parent_item_colon' 	=> '',
			),
			'description'         	=> '',
			'supports'            	=> !empty($current_entity_params['support']) ? $current_entity_params['support'] : array( 'title' ),
			'hierarchical'        	=> false,
			'public'              	=> false,
			'show_ui'             	=> true,
			'show_in_menu'        	=> true, //'edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
			'show_in_nav_menus'   	=> false,
			'show_in_admin_bar'   	=> false,
			'can_export'          	=> false,
			'has_archive'         	=> false,
			'exclude_from_search' 	=> true,
			'publicly_queryable'  	=> false,
			'rewrite'			  	=> false,
			'menu_icon'			  	=> 'dashicons-id-alt',
		);
		register_post_type( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $post_type_params );
	}

	/**
	 * Create an entity of customer type when a new user is created
	 *
	 * @param integer $user_id
	 */
	public static function create_entity_customer_when_user_is_created( $user_id ) {
		$user_info = get_userdata($user_id);
		wp_insert_post(array('post_type'=>WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'post_author' => $user_id, 'post_title'=>$user_info->user_nicename));

		/** Change metabox Hidden Nav Menu Definition to display WPShop categories' metabox **/
		$usermeta = get_post_meta( $user_id, 'metaboxhidden_nav-menus', true);
		if ( !empty($usermeta) && is_array($usermeta) ) {
			$data_to_delete = array_search('add-wpshop_product_category', $usermeta);
			if ( $data_to_delete !== false ) {
				unset( $usermeta[$data_to_delete] );
				update_user_meta($user_id, 'metaboxhidden_nav-menus', $usermeta);
			}
		}
	}


	/**
	 * Change the customer list table header to display custom informations
	 *
	 * @param array $current_header The current header list displayed to filter and modify for new output
	 *
	 * @return array The new header to display
	 */
	function list_table_header( $current_header ) {
		unset( $current_header['title'] );
		unset( $current_header['date'] );

		$current_header['customer_identifier'] = __( 'Customer ID', 'wpshop' );
		$current_header['customer_name'] = '<span class="wps-customer-last_name" >' . __( 'Last-name', 'wpshop' ) . '</span><span class="wps-customer-first_name" >' . __( 'First-name', 'wpshop' ) . '</span>';
		$current_header['customer_email'] = __( 'E-mail', 'wpshop' );
		$current_header['customer_orders'] = __( 'Customer\'s orders', 'wpshop' );
		$current_header['customer_date_subscription'] = __( 'Subscription', 'wpshop' );
		$current_header['customer_date_lastlogin'] = __( 'Last login date', 'wpshop' );

		return $current_header;
	}

	/**
	 * Display the content into list table column
	 *
	 * @param string $column THe column identifier to modify output for
	 * @param integer $post_id The current post identifier
	 */
	function list_table_column_content( $column, $post_id ) {
		global $wpdb;
		/**	Get wp_users idenfifier from customer id	*/
		$query = $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", $post_id);
		$current_user_id_in_list = $wpdb->get_var( $query );

		/**	Get current post informations	*/
		$customer_post = get_post( $post_id );

		/**	Get user data	*/
		$current_user_datas = get_userdata( $current_user_id_in_list );

		/**	Switch current column for custom case	*/
		$use_template = true;
		switch ( $column ) {
			case 'customer_identifier':
				echo $post_id;
				$use_template = false;
			break;
			case 'customer_date_subscription':
				echo mysql2date( get_option( 'date_format' ), $current_user_datas->user_registered, true );
				$use_template = false;
			break;
			case 'customer_date_lastlogin':
				$last_login = get_user_meta( $current_user_id_in_list, 'last_login_time', true );
				if ( !empty( $last_login ) ) :
					echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) , $last_login, true );
				else:
					_e( 'Never logged in', 'wpshop' );
				endif;
				$use_template = false;
			break;
		}

		/**	Require the template for displaying the current column	*/
		if ( $use_template ) {
			$template = wpshop_tools::get_template_part( WPS_ACCOUNT_DIR, WPS_ACCOUNT_PATH . WPS_ACCOUNT_DIR . '/templates/', 'backend', 'customer_listtable/' . $column );
			if ( is_file( $template ) ) {
				require( $template );
			}
		}
	}

	/**
	 * Filter bulk actions into customer list table
	 *
	 * @param array $actions Current available actions list
	 *
	 * @return array The new action list to use into customer list table
	 */
	function customer_list_table_bulk_actions( $actions ){
		unset( $actions[ 'edit' ] );
		unset( $actions[ 'trash' ] );

		return $actions;
	}

	function list_table_filters() {
		if (isset($_GET['post_type'])) {
			$post_type = $_GET['post_type'];
			if (post_type_exists($post_type) && ($post_type == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS)) {
				$filter_possibilities = array();
				$filter_possibilities[''] = __('-- Select Filter --', 'wpshop');
				$filter_possibilities['orders'] = __('List customers with orders', 'wpshop');
				$filter_possibilities['no_orders'] = __('List customers without orders', 'wpshop');
				echo wpshop_form::form_input_select('entity_filter', 'entity_filter', $filter_possibilities, (!empty($_GET['entity_filter']) ? $_GET['entity_filter'] : ''), '', 'index');
			}
		}
	}

	function list_table_filter_parse_query($query) {
		global $pagenow, $wpdb;

		if ( is_admin() && ($pagenow == 'edit.php') && !empty( $_GET['post_type'] ) && ( $_GET['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS ) && !empty( $_GET['entity_filter'] ) ) {
			$check = null;
			switch ( $_GET['entity_filter'] ) {
				case 'orders':
					$sql_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s
						AND post_author IN (
						SELECT post_author
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s)",
					WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
					'auto-draft',
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'auto-draft');
					$check = 'post__in';
					break;
				case 'no_orders':
					$sql_query = $wpdb->prepare(
						"SELECT ID
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s
						AND post_author NOT IN (
						SELECT post_author
						FROM {$wpdb->posts}
						WHERE post_type = %s
						AND post_status != %s)",
					WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS,
					'auto-draft',
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
					'auto-draft');
					$check = 'post__in';
					break;
			}

			if ( !empty( $check ) ) {
				$results = $wpdb->get_results($sql_query);
				$user_id_list = array();
				foreach($results as $item){
					$user_id_list[] = $item->ID;
				}
				if( empty($post_id_list) ) {
					$post_id_list[] = 'no_result';
				}
				$query->query_vars[$check] = $user_id_list;
			}
			$query->query_vars['post_type'] = WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS;
			$query->query_vars['post_status'] = 'any';
		}
	}

}

?>
