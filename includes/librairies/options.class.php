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
		global $wpshop_display_option, $wpshop_product_option;

		register_setting('wpshop_options', 'wpshop_options', array('wpshop_option', 'wpshop_options_validator'));
		register_setting('wpshop_options', 'wpshop_display_option', array('wpshop_display_options', 'part_validator'));
		$wpshop_display_option = get_option('wpshop_display_option');
		register_setting('wpshop_options', WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, array('wpshop_product_options', 'part_validator'));
		$wpshop_product_option = get_option(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);


		{/* Declare the different options for the plugin display	*/
			add_settings_section('wpshop_display_options_sections', '&nbsp;', array('wpshop_display_options', 'part_explanation'), 'wpshop_display_option');
			/*	Add the different field option	*/
			add_settings_field('wpshop_display_cat_sheet_output', __('Display type for category page', 'wpshop'), array('wpshop_display_options', 'wpshop_display_cat_sheet_output'), 'wpshop_display_option', 'wpshop_display_options_sections');		
			add_settings_field('wpshop_display_list_type', __('Display type for element list', 'wpshop'), array('wpshop_display_options', 'wpshop_display_list_type'), 'wpshop_display_option', 'wpshop_display_options_sections');		
			add_settings_field('wpshop_display_grid_element_number', __('Number of element by line for grid mode', 'wpshop'), array('wpshop_display_options', 'wpshop_display_grid_element_number'), 'wpshop_display_option', 'wpshop_display_options_sections');

			add_settings_field('wpshop_display_reset_template_element', __('Reset template file', 'wpshop'), array('wpshop_display_options', 'wpshop_display_reset_template_element'), 'wpshop_display_option', 'wpshop_display_options_sections');		
		}

		{/* Declare the different options for the products	*/
			add_settings_section('wpshop_product_options_sections', '&nbsp;', array('wpshop_product_options', 'part_explanation'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
			/*	Add the different field option	*/
			add_settings_field('wpshop_pdct_ref_prefix', __('Prefix for products\' reference', 'wpshop'), array('wpshop_product_options', 'wpshop_pdct_ref_prefix'), WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT, 'wpshop_product_options_sections');		
		}
	}

	/**
	*
	*/
	function option_main_page()
	{
		wpshop_display::displayPageHeader('WP Shop options', WPSHOP_MEDIAS_ICON_URL . 'options.png', 'wpshop option', 'wpshop option', false);
?>
		<form action="options.php" method="post">
			<div id="options-tabs" >
				<ul>
					<!-- <li><a href="#wpshop_option" ><?php _e('Display', 'wpshop'); ?></a></li> -->
					<li><a href="#wpshop_display_option" ><?php _e('Display', 'wpshop'); ?></a></li>
					<!-- <li><a href="#wpshop_product_option" ><?php _e('Products', 'wpshop'); ?></a></li> -->
				</ul>
				<!-- <div id="wpshop_option" ></div> -->
				<div id="wpshop_display_option" ><?php do_settings_sections('wpshop_display_option'); ?></div>
				<!-- <div id="wpshop_product_option" ><?php do_settings_sections(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT); ?></div> -->
			</div>
<?php
		settings_fields('wpshop_options');
		if(current_user_can('wpshop_edit_options'))
		{
?>
			<input class="button-primary alignright" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
<?php
		}
?>
		</form>
<?php
		wpshop_display::displayPageFooter();
	}

	/**
	*
	*/
	function wpshop_options_validator()
	{
	
	}

}

/**
* Define the different method to manage the different product options
* @package wpshop
* @subpackage librairies
*/
class wpshop_product_options
{

	/**
	*	Add an explanation on the option part
	*/
	function part_explanation(){
		
	}
	/**
	*	Add option validation for current option part
	*/
	function part_validator($input){
		$newinput['wpshop_pdct_ref_prefix'] = $input['wpshop_pdct_ref_prefix'];
		$newinput['product_slug'] = 'catalog';

		return $newinput;	
	}

	/**
	*	Add the option field to choose a prefix for product reference
	*/
	function wpshop_pdct_ref_prefix(){
		global $wpshop_product_option;
		$field_identifier = 'wpshop_pdct_ref_prefix';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '[' . $field_identifier . ']', $field_identifier, $wpshop_product_option[$field_identifier], 'text');
		}
		else{
			$option_field_output = $wpshop_product_option[$field_identifier];
		}

		echo $option_field_output;
	}

}

/**
* Define the different method to manage the different product options
* @package wpshop
* @subpackage librairies
*/
class wpshop_display_options
{
	/**
	*	Add an explanation on the option part
	*/
	function part_explanation(){
		
	}
	/**
	*	Add option validation for current option part
	*/
	function part_validator($input){
		$newinput['wpshop_display_list_type'] = $input['wpshop_display_list_type'];
		if($input['wpshop_display_grid_element_number'] < WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE){
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE;
		}
		elseif($input['wpshop_display_grid_element_number'] > WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE){
			$input['wpshop_display_grid_element_number'] = WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE;
		}
		$newinput['wpshop_display_grid_element_number'] = $input['wpshop_display_grid_element_number'];
		$newinput['wpshop_display_cat_sheet_output'] = $input['wpshop_display_cat_sheet_output'];
		$newinput['wpshop_display_reset_template_element'] = $input['wpshop_display_reset_template_element'];
		return $newinput;
	}

	/**
	*	Add the option field to choose how to display category page
	*/
	function wpshop_display_cat_sheet_output(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_cat_sheet_output';

		if(current_user_can('wpshop_edit_options')){
			$content = array('category_description', 'category_subcategory', 'category_subproduct');
			$option_field_output = '';
			foreach($content as $content_definition){
				$current_value = (is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) && in_array($content_definition, $wpshop_display_option['wpshop_display_cat_sheet_output'])) ? $content_definition : '';

				switch($content_definition){
					case 'category_description':
					{
						$field_label = __('Display product category description', 'wpshop');
					}
					break;
					case 'category_subcategory':
					{
						$field_label = __('Display sub categories listing', 'wpshop');
					}
					break;
					case 'category_subproduct':
					{
						$field_label = __('Display products listing', 'wpshop');
					}
					break;
					default:
					{
						$field_label = __('Nothing defined here', 'wpshop');
					}
					break;
				}
				$option_field_output .= wpshop_form::form_input_check('wpshop_display_option[' . $field_identifier . '][]', $field_identifier . '_' . $content_definition, $content_definition, $current_value, 'checkbox') . '<label for="' . $field_identifier . '_' . $content_definition . '" >' . $field_label . '</label><br/>';
			}
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose ho to output element list grid or list
	*/
	function wpshop_display_list_type(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_list_type';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input_select('wpshop_display_option[' . $field_identifier . ']', $field_identifier, array('grid' => __('Grid', 'wpshop'), 'list' => __('List', 'wpshop')), $wpshop_display_option[$field_identifier], '', 'index');
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}
	/**
	*	Add the option field to choose how many element to output when grid mode is selected
	*/
	function wpshop_display_grid_element_number(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_grid_element_number';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = '<div id="' . $field_identifier . 'slider" class="slider_variable"></div>
			' . wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'text', ' readonly="readonly" class="sliderValue" ') . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $field_identifier . 'slider").slider({
			value:' . ($wpshop_display_option[$field_identifier] <= 0 ? WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE : $wpshop_display_option[$field_identifier]) . ',
			min: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE . ',
			max: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE . ',
			step: 1,
			slide: function(event, ui) {
				jQuery("#' . $field_identifier . '").val(ui.value);
			}
		});
		jQuery("#' . $field_identifier . '").val(jQuery("#' . $field_identifier . 'slider").slider("value"));
	});
</script>';
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}	
	/**
	*	Add the option field to choose how many element to output when grid mode is selected
	*/
	function wpshop_display_reset_template_element(){
		global $wpshop_display_option, $current_user;
		$field_identifier = 'wpshop_display_reset_template_element';

		$last_reset_infos = '&nbsp;';
		if($wpshop_display_option[$field_identifier] != ''){
			$infos = explode('dateofreset', $wpshop_display_option[$field_identifier]);
			if($infos[0] > 0){
				$user_first_name = get_user_meta($infos[0], 'first_name', true);
				$user_first_name = ($user_first_name != '') ? $user_first_name : __('First name not defined', 'wpshop');
				$user_last_name = get_user_meta($infos[0], 'last_name', true);
				$user_last_name = ($user_last_name != '') ? $user_last_name : __('Last name not defined', 'wpshop');
				$last_reset_infos = sprintf(__('Last template reset was made by %s on %s', 'wpshop'), $user_first_name . '&nbsp;' . $user_last_name, mysql2date('d/m/Y H:i', $infos[1], true));
			}
		}

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'hidden', ' readonly="readonly" ') . '
<input type="button" value="' . __('Reset template file with default plugin file', 'wpshop') . '" name="reset_template_file" id="reset_template_file" class="button-secondary" /><div id="last_reset_infos" >' . $last_reset_infos . '</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#reset_template_file").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('All modification applied to template file will be lost!\r\n\r\nAre you sure you want to reset template?', 'wpshop') . '"))){
				jQuery("#' . $field_identifier . '").val("' . $current_user->ID . 'dateofreset' . date('Y-m-d H:i:s') . '");
				jQuery("#last_reset_infos").load(WPSHOP_AJAX_FILE_URL, {
					"post": "true",
					"elementCode": "templates",
					"action": "reset_template_files",
					"reset_info": jQuery("#' . $field_identifier . '").val()
				});
			}
		});
	});
</script>';
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output;
	}

}
