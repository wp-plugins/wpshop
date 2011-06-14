<?php
/**
* Plugin Loader
* 
* Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different element usefull for the plugin usage. The menus, includes script, start launch script, css, translations
* @package wpshop
* @subpackage librairies
*/
class wpshop_init
{

	/**
	*	Load the different element need to create the plugin environnement
	*/
	function wpshop_plugin_load()
	{
		global $db_options;

		/*	Call function to create the main left menu	*/
		add_action('admin_menu', array('wpshop_init', 'wpshop_menu') );

		/* Add plugin options	*/
		add_action('admin_init', array('wpshop_db_option', 'add_options'));
		add_action('admin_init', array('wpshop_option', 'add_options'));

		/*	Get the current language to translate the different text in plugin	*/
		$locale = get_locale();
		$moFile = WPSHOP_INC_PLUGIN_DIR . 'languages/wpshop-' . $locale . '.mo';
		if( !empty($locale) && (is_file($moFile)) )
		{
			load_textdomain('wpshop', $moFile);
		}

		wpshop_database::wpshop_db_update();
		/*	Check the last optimisation date if it was not perform today weoptimise the database	*/
		if($db_options->get_db_optimisation_date() != date('Y-m-d'))
		{
			wpshop_database::wpshop_db_optimisation();

			$db_options->set_db_optimisation_date(date('Y-m-d'));
			$db_options->set_db_option();
		}

		/*	Include the different javascript	*/
		add_action('admin_init', array('wpshop_init', 'wpshop_admin_js') );
		/*	Include the different css	*/
		add_action('admin_init', array('wpshop_init', 'wpshop_admin_css') );
		add_action('admin_init', array('wpshop_display','addContextualHelp'));
	}

	/**
	*	Create the main left menu with different parts
	*/
	function wpshop_menu() 
	{
		/*	Main menu creation	*/
		add_menu_page(__('Tableau de bord', 'wpshop' ), __('Boutique', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array('wpshop_display', 'displayPage'), WPSHOP_MENU_ICON);

		/*	Redefine the dashboard page	*/
		// add_submenu_page( WPSHOP_URL_SLUG_DASHBOARD, __('Tableau de bord', 'wpshop' ), __('Tableau de bord', 'wpshop' ), 'wpshop_view_dashboard', WPSHOP_URL_SLUG_DASHBOARD, array('wpshop_dashboard','wpshop_dashboard_load'));
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_products::pageTitle(), '---------&nbsp;' . __('Produits', 'wpshop'), 'wpshop_view_product', WPSHOP_URL_SLUG_PRODUCT_MAIN_MENU, array('wpshop_display','displayPage'));

		/*	Define products menu	*/
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_products::pageTitle(), '---------&nbsp;' . __('Produits', 'wpshop'), 'wpshop_view_product', WPSHOP_URL_SLUG_PRODUCT_MAIN_MENU, array('wpshop_display','displayPage'));
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_products::pageTitle(), __('Liste des produits', 'wpshop' ), 'wpshop_view_product', WPSHOP_URL_SLUG_PRODUCT_LISTING, array('wpshop_display','displayPage'));
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_products::pageTitle(), __('Ajouter un produit', 'wpshop' ), 'wpshop_add_product', WPSHOP_URL_SLUG_PRODUCT_EDITION, array('wpshop_display','displayPage'));

		/*	Define categories menu	*/
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_categories::pageTitle(), '---------&nbsp;' . __('Cat&eacute;gories', 'wpshop'), 'wpshop_view_product_category', WPSHOP_URL_SLUG_CATEGORY_MAIN_MENU, array('wpshop_display','displayPage'));
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_categories::pageTitle(), __('Liste des cat&eacute;gories', 'wpshop' ), 'wpshop_view_product_category', WPSHOP_URL_SLUG_CATEGORY_LISTING, array('wpshop_display','displayPage'));
		add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_categories::pageTitle(), __('Ajouter une categorie', 'wpshop' ), 'wpshop_add_product_category', WPSHOP_URL_SLUG_CATEGORY_EDITION, array('wpshop_display','displayPage'));

		/*	Define attributes menu	*/
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_attributes::pageTitle(), '---------&nbsp;' . __('Attributs', 'wpshop'), 'wpshop_view_attribute', WPSHOP_URL_SLUG_ATTRIBUTE_MAIN_MENU, array('wpshop_display','displayPage'));
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_attributes::pageTitle(), __('Liste des attributs', 'wpshop' ), 'wpshop_view_attribute', WPSHOP_URL_SLUG_ATTRIBUTE_LISTING, array('wpshop_display','displayPage'));
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_attributes::pageTitle(), __('Ajouter un attribut', 'wpshop' ), 'wpshop_add_attribute', WPSHOP_URL_SLUG_ATTRIBUTE_EDITION, array('wpshop_display','displayPage'));
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_attributes_set::pageTitle(), __('Groupe d\'attribut', 'wpshop' ), 'wpshop_view_attribute_set', WPSHOP_URL_SLUG_ATTRIBUTE_SET_LISTING, array('wpshop_display','displayPage'));

		/*	Define Options menu	*/
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_language::pageTitle(), '---------&nbsp;' . __('Options', 'wpshop'), 'wpshop_view_language', WPSHOP_URL_SLUG_OPTION_MAIN_MENU, array('wpshop_display','displayPage'));
		// add_submenu_page(WPSHOP_URL_SLUG_DASHBOARD, wpshop_language::pageTitle(), __('Langues', 'wpshop'), 'wpshop_view_language', WPSHOP_URL_SLUG_LANGUAGE, array('wpshop_display','displayPage'));

		/*	Add the options menu	*/
		add_options_page(__('Options de la boutique', 'evarisk'), __('Boutique', 'evarisk'), 'wpshop_view_options', WPSHOP_URL_SLUG_OPTION, array('wpshop_option', 'optionMainPage'));
	}

	/**
	*	Define the javascript to include in each page
	*/
	function wpshop_admin_js()
	{
		if(!wp_script_is('jquery-ui-tabs', 'queue'))
		{
			wp_enqueue_script('jquery-ui-tabs');
		}
		if(!wp_script_is('jquery', 'queue'))
		{
			wp_enqueue_script('jquery');
		}
		wp_enqueue_script('wpshop_main_js', WPSHOP_JSURL . 'wpshop_main.js', '', WPSHOP_VERSION);
		wp_enqueue_script('wpshop_jqui_min', WPSHOP_JSURL . 'jquery_librairies/jquery.ui-min.js', '', WPSHOP_VERSION);
		wp_enqueue_script('fileuploader', WPSHOP_JSURL . 'jquery_librairies/jquery.fileuploader.js', '', WPSHOP_VERSION);
		wp_enqueue_script('fancyboxmousewheel', WPSHOP_JSURL . 'jquery_librairies/fancybox/jquery.mousewheel-3.0.4.pack.js', '', WPSHOP_VERSION);
		wp_enqueue_script('fancybox', WPSHOP_JSURL . 'jquery_librairies/fancybox/jquery.fancybox-1.3.4.pack.js', '', WPSHOP_VERSION);
	}

	/**
	*	Define the css to include in each page
	*/
	function wpshop_admin_css()
	{
		if(!wp_style_is( 'wpshop_jquery_custom', 'queue'))
		{
			wp_register_style('wpshop_jquery_custom', WPSHOP_CSS_URL . 'jquery_librairies/jquery.ui.custom.css');
			wp_enqueue_style('wpshop_jquery_custom');
		}
		if(!wp_style_is( 'wpshop_jquery_fileuploader', 'queue'))
		{
			wp_register_style('wpshop_jquery_fileuploader', WPSHOP_CSS_URL . 'jquery_librairies/jquery.fileuploader.css');
			wp_enqueue_style('wpshop_jquery_fileuploader');
		}
		if(!wp_style_is( 'wpshop_jquery_fancybox', 'queue'))
		{
			wp_register_style('wpshop_jquery_fancybox', WPSHOP_CSS_URL . 'jquery_librairies/fancybox/jquery.fancybox-1.3.4.css');
			wp_enqueue_style('wpshop_jquery_fancybox');
		}
		wp_register_style('wpshop_main_css', WPSHOP_CSS_URL . 'wpshop_main.css');
		wp_enqueue_style('wpshop_main_css');
	}

}