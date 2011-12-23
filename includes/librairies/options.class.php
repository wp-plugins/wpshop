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
		if(isset($_POST['submit'])) {
			$options = array(
				'useSpecialPermalink' => isset($_POST['useSpecialPermalink']) && $_POST['useSpecialPermalink']=='on',
				'exampleProduct' => isset($_POST['exampleProduct']) && $_POST['exampleProduct']=='on',
				'paymentByPaypal' => isset($_POST['paymentByPaypal']) && $_POST['paymentByPaypal']=='on',
				'paymentByChecks' => isset($_POST['paymentByChecks']) && $_POST['paymentByChecks']=='on',
				'paypalEmail' => isset($_POST['paypalEmail']) ? $_POST['paypalEmail'] : null,
				'paypalMode' => !empty($_POST['paypalMode']) ? $_POST['paypalMode'] : null,
				'company_name' => !empty($_POST['company_name']) ? $_POST['company_name'] : null,
				'company_street' => !empty($_POST['company_street']) ? $_POST['company_street'] : null,
				'company_postcode' => !empty($_POST['company_postcode']) ? $_POST['company_postcode'] : null,
				'company_city' => !empty($_POST['company_city']) ? $_POST['company_city'] : null,
				'company_country' => !empty($_POST['company_country']) ? $_POST['company_country'] : null
			);
			$bool = true;
			
			// Paypal
			if($options['paymentByPaypal']) {
				if(empty($options['paypalEmail'])) {
					$error = __('You have to type a Paypal email adress.', 'wpshop');
					$bool = false;
				}
				elseif(!is_email($options['paypalEmail'])) {
					$error = __('Paypal email adress invalid.', 'wpshop');
					$bool = false;
				}
			}
			
			// Check
			if($options['paymentByChecks']) {
				if(empty($options['company_name'])) {
					$error = __('You have to type a company name.', 'wpshop');
					$bool = false;
				}
				elseif(empty($options['company_street'])) {
					$error = __('You have to type a company street.', 'wpshop');
					$bool = false;
				}
				elseif(empty($options['company_postcode'])) {
					$error = __('You have to type a company postcode.', 'wpshop');
					$bool = false;
				}
				elseif(empty($options['company_city'])) {
					$error = __('You have to type a company city.', 'wpshop');
					$bool = false;
				}
				elseif(empty($options['company_country'])) {
					$error = __('You have to type a company country.', 'wpshop');
					$bool = false;
				}
			}
			
			if($bool) {
				// Si le plugin est déjà installé et que l'utilisateur modifie sa config
				if(!empty($_POST['submitMode']) && $_POST['submitMode'] == 'save') {
					wpshop_install::save_payment_config($options);
				}
				// Sinon installation
				else {
					wpshop_install::install_wpshop($options);
					wpshop_install::update_wpshop();
					wpshop_database::check_database();
					wp_safe_redirect('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT); exit;
				}
			}
		}
		
		/*	Get current plugin version	*/
		$current_db_version = get_option('wpshop_db_options', 0);
				
		// Si la bdd est installée
		if(isset($current_db_version['db_version']) && $current_db_version['db_version']>0) {
		
			// On récupère les informations de paiements
			$paymentInfo = get_option('wpshop_paymentAddress', true);
			$paypalEmail = get_option('wpshop_paypalEmail');
			$paypalMode = get_option('wpshop_paypalMode', true);
			$paymentMethod = get_option('wpshop_paymentMethod', true);
			
			echo '
				<div class="wrap">
						<div id="icon-options-general" class="icon32"><br /></div>
						<h2>'.__('WP-Shop options', 'wpshop').'</h2><br />
						
						<div id="options-tabs">
							<ul>
								<li><a href="#wpshop_general_option">'.__('General', 'wpshop').'</a></li>
								<li><a href="#wpshop_display_option">'.__('Display', 'wpshop').'</a></li>
							</ul>
						
							<div id="wpshop_general_option">
								<form method="post">
								<div class="simple">
								
									<label class="simple" style="height:100px;">'.__('Payment method', 'wpshop').'</label> 
									<input type="checkbox" name="paymentByPaypal"'.(!empty($paymentMethod['paypal'])?' checked="checked"':null).' /> '.__('Allow <strong>Paypal</strong>', 'wpshop').'
									
									<div class="inputblock">
										<input type="text" name="paypalEmail" placeholder="'.__('Paypal business email', 'wpshop').'" value="'.(!empty($_POST['paypalEmail'])?$_POST['paypalEmail']:$paypalEmail).'" /> 
										<select name="paypalMode">
											<option value="normal"'.($paypalMode=='sandbox'?null:' selected="selected"').'>Classique</option>
											<option value="sandbox"'.($paypalMode=='sandbox'?' selected="selected"':null).'>Sandbox</option>
										</select>
									</div>
									
									<br /><br />
									
									<input type="checkbox" name="paymentByChecks"'.(!empty($paymentMethod['checks'])?' checked="checked"':null).' /> '.__('Allow <strong>checks</strong>', 'wpshop').'
									
									<div class="inputblock">
										<input type="text" name="company_name" placeholder="'.__('Company name', 'wpshop').'" value="'.(!empty($_POST['company_name'])?$_POST['company_name']:$paymentInfo['company_name']).'" /><br />
										<input type="text" name="company_street" placeholder="'.__('Street', 'wpshop').'" value="'.(!empty($_POST['company_street'])?$_POST['company_street']:$paymentInfo['company_street']).'" /><br />
										<input type="text" name="company_postcode" placeholder="'.__('Postcode', 'wpshop').'" value="'.(!empty($_POST['company_postcode'])?$_POST['company_postcode']:$paymentInfo['company_postcode']).'" /> 
										<input type="text" name="company_city" placeholder="'.__('City', 'wpshop').'" value="'.(!empty($_POST['company_city'])?$_POST['company_city']:$paymentInfo['company_city']).'" /><br />
										<input type="text" name="company_country" placeholder="'.__('Country', 'wpshop').'" value="'.(!empty($_POST['company_country'])?$_POST['company_country']:$paymentInfo['company_country']).'" /><br />
									</div>
									
								</div>
								
								<input type="hidden" name="submitMode" value="save" />
								
								<input type="submit" name="submit" id="submit" class="button-primary" value="'.__('Save the settings', 'wpshop').'" />
								
								<span class="error">'.(!empty($error)?$error:null).'</span>
							</form>
						</div>
						
						<div id="wpshop_display_option">
							<form action="options.php" method="post">';
								do_settings_sections('wpshop_display_option');
								settings_fields('wpshop_options');
								if(current_user_can('wpshop_edit_options')) {
									echo '<input class="button-primary" name="Submit" type="submit" value="'.__('Save Changes','wpshop').'" />';
								}
							echo '
							</form>
						</div>
					</div>
				</div>';
				
			//wpshop_display::displayPageFooter();
		}
		else {
			
			$title = __('Plugin general settings', 'wpshop');
			$warning = __('Before installation, thanks to choose the configuration settings to apply to the plugin WP-Shop.', 'wpshop');
			$h3 = __('To works correctly, WP-Shop requires the use of a custom permalinks structure like <code>/%postname%</code>. It is therefore strongly advised to keep the option permalinks checked.', 'wpshop');
			echo '
				<div class="wrap">
					<form method="post">
						<div id="icon-options-general" class="icon32"><br /></div>
						<h2>'.$title.'</h2>
						<p>'.$warning.'</p>
						<h3>'.$h3.'</h3>
						
						<div class="simple">
							<label class="simple">'.__('Permalinks', 'wpshop').'</label> <input type="checkbox" name="useSpecialPermalink" checked="checked" /> '.__('Use the custom permalinks structure', 'wpshop').'
						</div>
						
						<div class="simple">
							<label class="simple">'.__('Products', 'wpshop').'</label> <input type="checkbox" name="exampleProduct" checked="checked" /> '.__('Add a example product to the database', 'wpshop').'
						</div>
						
						<div class="simple">
						
							<label class="simple" style="height:100px;">'.__('Payment method', 'wpshop').'</label> 
							<input type="checkbox" name="paymentByPaypal" checked="checked" /> '.__('Allow <strong>Paypal</strong>', 'wpshop').'
							
							<div class="inputblock">
								<input type="text" name="paypalEmail" placeholder="'.__('Paypal business email', 'wpshop').'" value="'.(!empty($_POST['paypalEmail'])?$_POST['paypalEmail']:null).'" /> 
								<select name="paypalMode">
									<option value="normal">Classique</option>
									<option value="sandbox">Sandbox</option>
								</select>
							</div>
							
							<br /><br />
							
							<input type="checkbox" name="paymentByChecks" checked="checked" /> '.__('Allow <strong>checks</strong>', 'wpshop').'
							
							<div class="inputblock">
								<input type="text" name="company_name" placeholder="'.__('Company name', 'wpshop').'" value="'.(!empty($_POST['company_name'])?$_POST['company_name']:null).'" /><br />
								<input type="text" name="company_street" placeholder="'.__('Street', 'wpshop').'" value="'.(!empty($_POST['company_street'])?$_POST['company_street']:null).'" /><br />
								<input type="text" name="company_postcode" placeholder="'.__('Postcode', 'wpshop').'" value="'.(!empty($_POST['company_postcode'])?$_POST['company_postcode']:null).'" /> 
								<input type="text" name="company_city" placeholder="'.__('City', 'wpshop').'" value="'.(!empty($_POST['company_city'])?$_POST['company_city']:null).'" /><br />
								<input type="text" name="company_country" placeholder="'.__('Country', 'wpshop').'" value="'.(!empty($_POST['company_country'])?$_POST['company_country']:null).'" /><br />
							</div>
							
						</div>
						
						<input type="submit" name="submit" id="submit" class="button-primary" value="'.__('Save the settings', 'wpshop').'" />
						
						<span class="error">'.(!empty($error)?$error:null).'</span>
					</form>
				</div>';
		}
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
