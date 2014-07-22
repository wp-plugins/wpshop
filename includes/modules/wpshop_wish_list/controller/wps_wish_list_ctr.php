<?php
class wpeo_wish_list_ctr{

    function __construct() {
		/**	Load plugin translation	*/
		load_plugin_textdomain( 'wp_wish_list', false, WPSHOP_MODULES_DIR . '/' . WPWISHLIST_DIR . '/languages/' );

        add_action( 'wp_enqueue_scripts', array( &$this, 'admin_js' )); // Init js
        add_action( 'wp_print_scripts', array( &$this, 'translate_wishlist_button_js'));  // Init var js translate

        add_action('wp_ajax_wps_wishlist_add_product', array( &$this, 'wps_wishlist_ctr_add_product' )); // Add a product with Ajax
        add_action('wp_ajax_nopriv_wps_wishlist_add_product', array(&$this, 'wps_wishlist_ctr_add_product' )); // Add a product with Ajax
        add_action('wp_ajax_wps_wishlist_remove_product', array(&$this, 'wps_wishlist_ctr_remove_product' )); // Remove a product with Ajax
        add_action('wp_ajax_nopriv_wps_wishlist_remove_product', array(&$this, 'wps_wishlist_ctr_remove_product' )); // Remove a product with Ajax

        add_action('wp_ajax_wps_wishlist_content_popup', array(&$this, 'wps_wishlist_content_popup' )); // Display content on pop up
        add_action('wp_ajax_nopriv_wps_wishlist_content_popup', array(&$this, 'wps_wishlist_content_popup' )); // Display content on pop up

        add_action('wp_ajax_wps_wishlist_send', array( &$this, 'wps_wishlist_send' )); // Send wish list with Ajax
        add_action('wp_ajax_nopriv_wps_wishlist_send', array( &$this, 'wps_wishlist_send' )); // Send wish list with Ajax

        add_shortcode( 'wps_wishlist_button', array(&$this, 'display_wish_list_button') ); // Display wish list button with shortcode

        /** Short code to display wish list	*/
        add_shortcode( 'wps_wishlist', array( &$this, 'wps_display_wishlist' ) );

        register_activation_hook( WPWISHLIST_FILE, array(&$this, 'wps_init_message') );

        /**	Include the different styles	*/
        add_action( 'wp_enqueue_scripts', array( $this, 'admin_css') );
    }

	/**
	 * Check and get the template file path to use for a given display part
	 *
	 * @uses locate_template()
	 * @uses get_template_part()
	 *
	 * @param string $side The website part were the template will be displayed. Backend or frontend
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 *
	 * @return string The template file path to use
	 */
	function get_template_part( $plugin_dir_name, $plugin_template_dir, $side, $slug, $name = null ) {
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
			$check_theme_template = $plugin_dir_name . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );

		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;

				if ( file_exists( $plugin_template_dir . $template_name ) ) {
					$path = $plugin_template_dir . $template_name;
					break;
				}
			}
		}

		return $path;
	}

    /**
     * Enqueue admin js
     */
    function admin_js() {
    	wp_enqueue_script('jquery-form'); // Ajax form plugin
        wp_enqueue_script('wp_wishlist_frontend_js', WPWISHLIST_FRONTEND_LIBS_URL . 'js/frontend.js', array('jquery'), '1.0'); // Front End script
        wp_enqueue_script('wps-wishlist-chosen', WPWISHLIST_FRONTEND_LIBS_URL . 'js/chosen/chosen.jquery.min.js', array('jquery'), '1.0'); // chosen
    }


    /**
     * Include styles
     */
	function admin_css() {
		wp_register_style( 'wp_wishlist_frontend_css', WPWISHLIST_FRONTEND_LIBS_URL . 'css/frontend.css');
		wp_enqueue_style( 'wp_wishlist_frontend_css' );
	}


    /**
     * Var translated to javascript
     */
    function translate_wishlist_button_js() {
		echo '
		<script type="text/javascript">
		var WISHLIST_BUTTON_ADD = "'. __('Add to my wish list', 'wp_wish_list') .' ";
                var WISHLIST_BUTTON_REMOVE_1 = "' . __('Product is on your wish list', 'wp_wish_list') . ' ";
                var WISHLIST_BUTTON_REMOVE_2 = "' . __('Delete', 'wp_wish_list') . ' ";
                var WISHLIST_BUTTON_LABEL_REMOVE = "' . __('This product is already on your wish list', 'wp_wish_list') . ' ";
                var WISHLIST_ADD_RECEIVER_ERROR = "' . __('You must add at least one receiver!', 'wp_wish_list') . ' ";
                var WISHLIST_ADD_RECEIVER_EMAIL_ERROR = "' . __('Email is not valid or already added!', 'wp_wish_list') . ' ";
                var WISHLIST_BUTTON_SEND = "' . __('Send', 'wp_wish_list') . ' ";
                var WISHLIST_BUTTON_CANCEL = "' . __('Cancel', 'wp_wish_list') . ' ";
                var WISH_LIST_PICTURES_URL_AJAX = "'. WPWISHLIST_PICTURES_URL  .'";
                var WISH_LIST_LOADING_AJAX = "' . __('Loading...', 'wp_wish_list') . ' ";
                var WISH_LIST_SEND_BACK = "' . __('Send it back', 'wp_wish_list') . ' ";
        </script>';
	}


    function wps_wishlist_ctr_add_product() // Add a product on wish list
    {
        $wps_wishlist_model = new wpeo_wish_list_model();

        $wps_wishlist_model->wps_wishlist_add_product();
    }

    function wps_wishlist_ctr_remove_product() // Remove a product on wish list
    {
        $wps_wishlist_model = new wpeo_wish_list_model();

        $wps_wishlist_model->wps_wishlist_remove_product();
    }



    // Display the wish list button
    function display_wish_list_button( ) {
        $product_ID = get_the_ID();

        ob_start();
		require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "wish_list_button") );
		$output = ob_get_contents();
		ob_end_clean();

        return $output;
    }

    /**
     * DISPLAY - SHORTCODE - Display the wishlist of current user into account
     *
     * @return string THe complete html output for wishlist display
     */
	function wps_display_wishlist() {
		$wishlist_display = '';

		/**	Instanciate a new wishlist model object	*/
		$wps_wishlist_model = new wpeo_wish_list_model();

		/**	Get wishlist content	*/
		$product_in_wishlist = $wps_wishlist_model->get_all_product();

		/**	Display wishlist content OR a message specifying that the wishlist is empty	*/
		ob_start();
		require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "wish_list") );
		$wishlist_display = ob_get_contents();
		ob_end_clean();

		return $wishlist_display;
	}

	/**
	 *
	 */
	static function wps_init_message() {
	    $wpshop_messages = new wpshop_messages();
	    $wpshop_messages->createMessage('WPSHOP_WISH_LIST_SENT_MESSAGE');
	}

	/**
	 * AJAX - Fill the modal for sending wishlist to friend
	 */
	function wps_wishlist_content_popup() {
		/**	Define the modal title	*/
	    $title =  __('Send my wish list', 'wp_wish_list');

	    /**	Get the modal content	*/
	    ob_start();
		require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "wish_list", "sender") );
		$content = ob_get_contents();
		ob_end_clean();

	    wp_die( json_encode(array('title' => $title, 'content' => $content) ));
	}

	function wps_wishlist_send() {
		$current_user = wp_get_current_user();

		$list_receivers = $_POST['receivers']; // Get all email of receivers
		$wpshop_messages = new wpshop_messages();

		if ( !empty( $list_receivers ) ) {
			foreach ( $list_receivers as $email ) {
				$data = array(
					'customer_first_name' => $current_user->user_firstname,
					'customer_last_name' => $current_user->user_lastname,
					'wishlist' => $this->display_wishlist_html_mail(),
				);
			   	$wpshop_messages->wpshop_prepared_email( $email, 'WPSHOP_WISH_LIST_SENT_MESSAGE', $data );
			}

			$status = true;
			$message = __( 'Wish list sent', 'wp_wish_list' );
		}
		else {
			$status = false;
			$message = __( 'Error', 'wp_wish_list' );
		}

		wp_die(json_encode(array('status' => $status, 'message' => $message)));
	}

	function display_wishlist_html_mail() {
		$wps_wishlist_model = new wpeo_wish_list_model();
		$datas = $wps_wishlist_model->get_all_product();

		$string = '';

		if ( !empty($datas) ) {
			foreach ( $datas as $data ) {
				$string .=
				"<div id='wish_list_container'>
					<ul>
						<li>Product title : $data->product_Title</li>
						<li><a href='$data->product_Link'>Product link</a></li>
					</ul>
				</div>";
			}
		}

		return $string;
	}

}

?>