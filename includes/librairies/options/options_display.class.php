<?php
/**
* Display options management
* 
* Define the different method to manage the different display options
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different method to manage the different display options
* @package wpshop
* @subpackage librairies
*/
class wpshop_display_options
{

	/**
	*
	*/
	function declare_options(){
		register_setting('wpshop_options', 'wpshop_display_option', array('wpshop_display_options', 'part_validator'));
		$wpshop_display_option = get_option('wpshop_display_option');
			add_settings_section('wpshop_display_options_sections', __('Display options', 'wpshop'), array('wpshop_display_options', 'part_explanation'), 'wpshop_display_option');
				/*	Add the different field option	*/
				add_settings_field('wpshop_display_cat_sheet_output', __('Display type for category page', 'wpshop'), array('wpshop_display_options', 'wpshop_display_cat_sheet_output'), 'wpshop_display_option', 'wpshop_display_options_sections');		
				add_settings_field('wpshop_display_list_type', __('Display type for element list', 'wpshop'), array('wpshop_display_options', 'wpshop_display_list_type'), 'wpshop_display_option', 'wpshop_display_options_sections');		
				add_settings_field('wpshop_display_grid_element_number', __('Number of element by line for grid mode', 'wpshop'), array('wpshop_display_options', 'wpshop_display_grid_element_number'), 'wpshop_display_option', 'wpshop_display_options_sections');
				add_settings_field('wpshop_display_element_per_page', __('Number of element per page', 'wpshop'), array('wpshop_display_options', 'wpshop_display_element_per_page'), 'wpshop_display_option', 'wpshop_display_options_sections');	
	}
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
		$newinput['wpshop_display_element_per_page'] = $input['wpshop_display_element_per_page'];

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
				$current_value = (is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) && in_array($content_definition, $wpshop_display_option['wpshop_display_cat_sheet_output'])) || !is_array($wpshop_display_option['wpshop_display_cat_sheet_output']) ? $content_definition : '';

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

		echo $option_field_output.' <a href="#" title="'.__('Default display mode on shop','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	/**
	*	Add the option field to choose how many element to output when grid mode is selected
	*/
	function wpshop_display_grid_element_number(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_grid_element_number';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = '<div id="' . $field_identifier . 'slider" class="slider_variable"></div>
			' . wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'hidden', ' readonly="readonly" class="sliderValue" ') . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $field_identifier . 'slider").slider({
			value:' . ($wpshop_display_option[$field_identifier] <= 0 ? WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE : $wpshop_display_option[$field_identifier]) . ',
			min: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MIN_RANGE . ',
			max: ' . WPSHOP_DISPLAY_GRID_ELEMENT_NUMBER_PER_LINE_MAX_RANGE . ',
			step: 1,
			slide: function(event, ui) {
				jQuery("#' . $field_identifier . '").val(ui.value);
				jQuery("#' . $field_identifier . 'slider a span").html(ui.value);
			}
		});
		jQuery("#' . $field_identifier . 'slider a").append("<span></span>");
		jQuery("#' . $field_identifier . '").val(jQuery("#' . $field_identifier . 'slider").slider("value"));
		jQuery("#' . $field_identifier . 'slider a span").html(jQuery("#' . $field_identifier . 'slider").slider("value"));
	});
</script>';
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output.' <a href="#" title="'.__('Number of products displayed per line when grid display mode is active','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}
	/**
	*	Add the option field to choose how many element to output per page in product listing
	*/
	function wpshop_display_element_per_page(){
		global $wpshop_display_option;
		$field_identifier = 'wpshop_display_element_per_page';

		if(current_user_can('wpshop_edit_options')){
			$option_field_output = wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, !empty($wpshop_display_option[$field_identifier]) ? $wpshop_display_option[$field_identifier] : 20, 'text');
		}
		else{
			$option_field_output = $wpshop_display_option[$field_identifier];
		}

		echo $option_field_output.' <a href="#" title="'.__('Number of elements per page','wpshop').'" class="wpshop_infobulle_marker">?</a>';
	}

}
