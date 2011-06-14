<?php
/**
* Plugin options
* 
* Allows to manage the different option for the plugin excluding database options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
 * Allows to manage the different option for plugin excluding database options
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_option
{

	/**
	*	Declare the different options for the plugin	
	*/
	function add_options() 
	{
		register_setting('wpshop_product_options', 'wpshop_product_options', array('wpshop_option', 'wpshop_product_options_validator'));

		{/* Declare the different options for the correctiv actions	*/
			add_settings_section('wpshop_product_options_sections', __('Options pour les produits', 'wpshop'), array('wpshop_option', 'wpshop_product_options_explanation'), 'wpshop_product_options_settings');
			/*	Add the different field for the correctives actions	*/
			add_settings_field('wpshop_pdct_ref_prefix', __('Pr&eacute;fixe pour les r&eacute;f&eacute;rence des produits', 'wpshop'), array('wpshop_option', 'wpshop_pdct_ref_prefix'), 'wpshop_product_options_settings', 'wpshop_product_options_sections');
		}
	}

	/**
	*	Create the html ouput code for the options page
	*
	*	@return The html code to output for option page
	*/
	function optionMainPage()
	{
		echo wpshop_display::displayPageHeader(__('Options de la boutique wpshop', 'wpshop'), WP_PLUGIN_URL . '/' . WPSHOP_OPTIONS_ICON, __('options de wpshop', 'wpshop'), __('options de wpshop', 'wpshop'), false);
?>
<div id="digirisk_options_container" >
	<form action="options.php" method="post">

	<?php settings_fields('wpshop_product_options'); ?>
	<?php do_settings_sections('wpshop_product_options_settings'); ?>

	<br/><br/>
	<input class="button-primary" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
</div>
<?php
		echo wpshop_display::displayPageFooter();
	}

	/**
	*	Validate the different data sent for the option
	*
	*	@param array $sentData An array which will receive the values sent by the user with the form
	*
	*	@return array $dataToSave An array with the send values cleaned for more secure usage
	*/
	function wpshop_product_options_validator($sentData)
	{
		$dataToSave['product_reference_prefix'] = $sentData['product_reference_prefix'];

		return $dataToSave;
	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function wpshop_product_options_explanation()
	{
		
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function wpshop_pdct_ref_prefix()
	{
		$options = get_option('wpshop_product_options');
		echo "<input id='product_reference_prefix' name='wpshop_product_options[product_reference_prefix]' size='40' type='text' value='{$options['product_reference_prefix']}' />";
	}

}