<?php
/**
* Plugin initialisation definition file.
* 
*	This file contains the different methods needed by the plugin on initialisation
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	Define the different plugin initialisation's methods
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_init
{

	/**
	*	This is the function loaded when wordpress load the different plugin
	*/
	function load(){
		/*	Declare the different options for the plugin	*/
		add_action('admin_init', array('wpshop_options', 'add_options'));

		/*	Get the current language to translate the different text in plugin	*/
		$locale = get_locale();
		$moFile = WPSHOP_LANGUAGES_DIR . 'wpshop-' . $locale . '.mo';
		if(!empty($locale) && (is_file($moFile))){
			load_textdomain('wpshop', $moFile);
		}
		
		/*	Include head js	*/
		add_action('admin_head', array('wpshop_init', 'admin_js_head'));
			
		/*	Check if we are on a page of our plugin in order to avoid conflict with other extension	*/
		if((isset($_GET['page']) && (substr($_GET['page'], 0, 7) == 'wpshop_')) || (isset($_GET['post']) && ($_GET['post'] > 0)) || (isset($_GET['post_type']) && ($_GET['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT))){
			/*	Include the different javascript	*/
			add_action('admin_init', array('wpshop_init', 'admin_js'));

			/*	Include the different css	*/
			add_action('admin_init', array('wpshop_init', 'admin_css'));
		}

		/*	Include the different css	*/
		add_action('admin_init', array('wpshop_init', 'admin_all_css'));
		add_action('admin_init', array('wpshop_init', 'admin_all_js'));

		/*	Include the different css	*/
		add_action('wp_print_styles', array('wpshop_init', 'frontend_css'));
		add_action('wp_head', array('wpshop_init', 'frontend_js'));


		/* On initialise le formulaire seulement dans la page de création/édition */
		if (isset($_GET['page'],$_GET['action']) && $_GET['page']=='wpshop_doc' && $_GET['action']=='edit') {
			add_action('admin_init', array('wpshop_doc', 'init_wysiwyg'));
		}
		/* On récupère la liste des pages documentées afin de les comparer a la page courante */
		$pages_list = wpshop_doc::get_doc_pages_name_array();
		if((isset($_GET['page']) && in_array($_GET['page'], $pages_list)) || (isset($_GET['post_type']) && in_array($_GET['post_type'], $pages_list))) {
			add_action('contextual_help', array('wpshop_doc', 'pippin_contextual_help'), 10, 3);
		}
	}

	/**
	*	Admin menu creation
	*/
	function admin_menu() {
		/*	Get current plugin version	*/
		$current_db_version = get_option('wpshop_db_options', 0);
		
		// Si la bdd est installée
		if(isset($current_db_version['db_version']) && $current_db_version['db_version']>0) {
		
			/*	Main menu creation	*/
			add_menu_page(__('Dashboard', 'wpshop' ), __('Shop', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array('wpshop_display', 'display_page'), WPSHOP_MEDIAS_URL . "icones/logo.png");
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Dashboard', 'wpshop' ), __('Dashboard', 'wpshop'), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array('wpshop_display', 'display_page'));

			/*	Add product menus	*/
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Products', 'wpshop' ), __('Products', 'wpshop'), 'wpshop_view_product', 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			//add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Add new product', 'wpshop' ), __('Add new product', 'wpshop'), 'wpshop_add_product', 'post-new.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Categories', 'wpshop' ), __('Categories', 'wpshop'), 'wpshop_manage_product_categories', 'edit-tags.php?taxonomy=' . WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES . '&amp;post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);

			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Orders', 'wpshop'), __('Orders', 'wpshop'), 'wpshop_view_orders', 'edit.php?post_type=' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER);
			
			/*	Add eav model menus	*/
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Attributes', 'wpshop' ), __('Attributes', 'wpshop'), 'wpshop_view_attributes', WPSHOP_URL_SLUG_ATTRIBUTE_LISTING, array('wpshop_display','display_page'));
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Attributes groups', 'wpshop' ), __('Attributes groups', 'wpshop'), 'wpshop_view_attribute_set', WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING, array('wpshop_display','display_page'));

			/*	Add shortcodes menus	*/
			add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, __('Shortcodes', 'wpshop' ), __('Shortcodes', 'wpshop'), 'wpshop_view_shortcodes', WPSHOP_URL_SLUG_SHORTCODES, array('wpshop_display','display_page'));
			
			/*	Add tools menu	*/
			add_management_page(WPSHOP_URL_SLUG_DASHBOARD, __('Documentation wpshop', 'wpshop' ), 'wpshop_view_documentation_menu', 'wpshop_doc', array('wpshop_doc', 'mydoc'));

			/*	Add the options menu	*/
			add_options_page(__('WPShop options', 'wpshop'), __('Shop', 'wpshop'), 'wpshop_view_options', WPSHOP_URL_SLUG_OPTION, array('wpshop_options', 'option_main_page'));
		}
		else {
			add_menu_page(__('Dashboard', 'wpshop' ), __('Shop', 'wpshop' ), 'wpshop_view_options', WPSHOP_URL_SLUG_DASHBOARD, array('wpshop_options', 'option_main_page'), WPSHOP_MEDIAS_URL . "icones/logo.png");
		}
	}

	/**
	*	Admin javascript "header script" part definition
	*/
	function admin_js_head(){
		echo '<script type="text/javascript">var WPSHOP_AJAX_FILE_URL = "'.WPSHOP_AJAX_FILE_URL.'";</script>';
	}
	
	/**
	*	Admin javascript "footer script" part definition
	*/
	function admin_js_footer(){
		global $wp_version;
		ob_start();
		include(WPSHOP_JS_DIR . 'pages/wpshop_product.js');
		$wpshop_product_js = ob_get_contents();
		ob_end_clean();
?>
<script type="text/javascript" >
	var wp_version = "<?php echo $wp_version; ?>";
<?php echo $wpshop_product_js; ?>

</script>
<?php
	}
	/**
	*	Admin javascript "file" part definition
	*/
	function admin_js(){
		/*	Check the wp version in order to include the good jquery librairy. Causes issue because of wp core update	*/
		global $wp_version;
		if(($wp_version < '3.2') && (!isset($_GET['post'])) && (!isset($_GET['post_type']))){
			wp_enqueue_script('wpshop_jquery', WPSHOP_JS_URL . 'jquery-libs/jquery1.6.1.js', '', WPSHOP_VERSION);
		}

		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-form');

		wp_enqueue_script('wpshop_main_js', WPSHOP_JS_URL . 'main.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wpshop_jq_datatable', WPSHOP_JS_URL . 'jquery-libs/jquery.dataTables.min.js', '', WPSHOP_VERSION);

		if((isset($_GET['post']) 
			|| (isset($_GET['post_type']) && ($_GET['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT)) 
			|| (isset($_GET['page']) && ($_GET['page'] == WPSHOP_URL_SLUG_OPTION)))
			&& ($wp_version > '3.1')){
			wp_enqueue_script('wpshop_jq_ui', WPSHOP_JS_URL . 'jquery-libs/jquery-ui-1.8.16.js', '', WPSHOP_VERSION);
		}

		/*	Include specific js file for the current page if existing	*/
		if(is_file(WPSHOP_JS_DIR . 'pages/' . $_GET['page'] . '.js')){
			wp_enqueue_script($_GET['page'] . '_js', WPSHOP_JS_URL . 'pages/' . $_GET['page'] . '.js', '', WPSHOP_VERSION);
		}
	}
	/**
	*	Admin javascript "frontend" part definition
	*/
	function frontend_js(){
		echo '<script type="text/javascript">var WPSHOP_AJAX_URL = "'.WPSHOP_AJAX_FILE_URL.'";</script>';
	}

	/**
	*	Admin javascript "header script" part definition
	*/
	function admin_css_head(){
		ob_start();
		include(WPSHOP_CSS_DIR . 'pages/wpshop_product.css');
		$wpshop_product_css = ob_get_contents();
		ob_end_clean();
?>
<style type="text/css" >
<?php echo $wpshop_product_css; ?>
</style>
<?php
	}
	/**
	*	Admin css part definition
	*/
	function admin_all_js(){
		wp_enqueue_script('jquery-ui-tabs');

		wp_enqueue_script('wpshop_main_common_js', WPSHOP_JS_URL . 'main_common.js', '', WPSHOP_VERSION);
	}
	/**
	*	Admin css part definition
	*/
	function admin_all_css(){
		wp_register_style('wpshop_main_common_css', WPSHOP_CSS_URL . 'main_common.css');
		wp_enqueue_style('wpshop_main_common_css');
	}
	/**
	*	Admin javascript "file" part definition
	*/
	function admin_css(){
		wp_register_style('wpshop_jquery_datatable', WPSHOP_CSS_URL . 'jquery-libs/jquery-default-datatable.css');
		wp_enqueue_style('wpshop_jquery_datatable');
		wp_register_style('wpshop_jquery_datatable_ui', WPSHOP_CSS_URL . 'jquery-libs/jquery-default-datatable-jui.css');
		wp_enqueue_style('wpshop_jquery_datatable_ui');

		wp_register_style('wpshop_jquery_ui', WPSHOP_CSS_URL . 'jquery-ui.css');
		wp_enqueue_style('wpshop_jquery_ui');

		wp_register_style('wpshop_main_css', WPSHOP_CSS_URL . 'main.css');
		wp_enqueue_style('wpshop_main_css');

		/*	Include specific css file for the current page if existing	*/
		if(is_file(WPSHOP_CSS_DIR . 'pages/' . $_GET['page'] . '.css'))
		{
			wp_register_style($_GET['page'] . '_css', WPSHOP_CSS_URL . 'pages/' . $_GET['page'] . '.css');
			wp_enqueue_style($_GET['page'] . '_css');
		}
	}
	/**
	*	Admin javascript "file" part definition
	*/
	function frontend_css(){
		wp_register_style('wpshop_frontend_main_css', wpshop_display::get_template_file('frontend_main.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'));
		wp_enqueue_style('wpshop_frontend_main_css');
		wp_register_style('wpshop_jquery_ui', wpshop_display::get_template_file('jquery-ui.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'));
		wp_enqueue_style('wpshop_jquery_ui');
		wp_register_style('wpshop_jquery_fancybox', wpshop_display::get_template_file('jquery.fancybox-1.3.4.css', WPSHOP_TEMPLATES_URL, 'wpshop/css', 'output'));
		wp_enqueue_style('wpshop_jquery_fancybox');

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('wpshop_frontend_main_js', wpshop_display::get_template_file('frontend_main.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION);
		wp_enqueue_script('fancyboxmousewheel', wpshop_display::get_template_file('fancybox/jquery.mousewheel-3.0.4.pack.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION);
		wp_enqueue_script('fancybox', wpshop_display::get_template_file('fancybox/jquery.fancybox-1.3.4.pack.js', WPSHOP_TEMPLATES_URL, 'wpshop/js', 'output'), '', WPSHOP_VERSION);
	}



	/**
	*	Function called on plugin initialisation allowing to declare the new types needed by our plugin
	*	@see wpshop_products::create_wpshop_products_type();
	*	@see wpshop_categories::create_product_categories();
	*/
	function add_new_wp_type(){
		/*	Add wpshop product type and add a new meta_bow into product creation/edition interface for regrouping title and editor in order to sort interface	*/
		wpshop_products::create_wpshop_products_type();
		add_action('add_meta_boxes', array('wpshop_products', 'add_meta_boxes'));
		add_action('save_post', array('wpshop_products', 'save_product_eav_informations'));
		add_filter('post_type_link', array('wpshop_products', 'set_product_permalink'), 10, 3);
		add_action('manage_posts_custom_column',  array('wpshop_products', 'product_custom_columns'));
		add_filter('manage_edit-'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'_columns', array('wpshop_products', 'product_edit_columns'));


		/*	Add wpshop product category term	*/
		wpshop_categories::create_product_categories();	
		
		/*	Add wpshop orders term	*/
		wpshop_orders::create_orders_type();	
		add_action('add_meta_boxes', array('wpshop_orders', 'add_meta_boxes'));
		add_action('manage_posts_custom_column',  array('wpshop_orders', 'orders_custom_columns'));
		add_filter('manage_edit-'.WPSHOP_NEWTYPE_IDENTIFIER_ORDER.'_columns', array('wpshop_orders', 'orders_edit_columns'));
		//add_action('save_post', array('wpshop_products', 'save_product_eav_informations'));
	}

}