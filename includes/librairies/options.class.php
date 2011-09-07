<?php
/**
* Plugin option manager
* 
* Define the different method to manage the different options into the plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different options into the plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_options
{

	/**
	*	Declare the different options for the plugin	
	*/
	function add_options() 
	{
		register_setting('wpshop_options', 'wpshop_options', array('wpshop_option', 'wpshop_options_validator'));

		{/* Declare the different options for the correctiv actions	*/
			add_settings_section('wpshop_product_options_sections', __('Products\' options', 'wpshop'), array('wpshop_option', 'wpshop_product_options_explanation'), 'wpshop_product_options_settings');
			/*	Add the different field for the correctives actions	*/
			add_settings_field('wpshop_pdct_ref_prefix', __('Prefix for products\' reference', 'wpshop'), array('wpshop_option', 'wpshop_pdct_ref_prefix'), 'wpshop_product_options_settings', 'wpshop_product_options_sections');
		}
	}

	/**
	*
	*/
	function option_main_page()
	{
?>
<div id="options-tabs" >
	<ul>
		<li id="wpshop-general-tab" ><a href="<?php admin_url(); ?>options-general.php?page=<?php echo WPSHOP_URL_SLUG_OPTION; ?>&amp;tab=general" ><?php _e('General', 'wpshop'); ?></a></li>
		<li id="wpshop-products-tab" ><a href="<?php admin_url(); ?>options-general.php?page=<?php echo WPSHOP_URL_SLUG_OPTION; ?>&amp;tab=product" ><?php _e('Products', 'wpshop'); ?></a></li>
		<li id="wpshop-categories-tab" ><a href="<?php admin_url(); ?>options-general.php?page=<?php echo WPSHOP_URL_SLUG_OPTION; ?>&amp;tab=categories" ><?php _e('Categories', 'wpshop'); ?></a></li>
	</ul>
</div>
<?php
	}

	/**
	*
	*/
	function wpshop_options_validator()
	{
	
	}

}