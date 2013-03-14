<?php
/**
 * Payment options management
 *
 * Define the different method to manage the different payment options
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage the different payment options
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_payment_options {

	/**
	 *
	 */
	function declare_options() {
		add_settings_field('wpshop_payment_options_def', '', array('wpshop_payment_options', 'wpshop_payment_options_def'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');

		$options = get_option('wpshop_paymentMethod');
		add_settings_section('wpshop_paymentMethod', __('Payment method', 'wpshop'), array('wpshop_payment_options', 'plugin_section_text'), 'wpshop_paymentMethod');
		add_settings_field('wpshop_payment_paypal', __('Paypal', 'wpshop'), array('wpshop_payment_options', 'wpshop_paypal_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
		add_settings_field('wpshop_company_member_of_a_approved_management_center', '', array('wpshop_payment_options', 'wpshop_company_member_of_a_approved_management_center_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
		add_settings_field('wpshop_payment_checks', __('Checks', 'wpshop'), array('wpshop_payment_options', 'wpshop_checks_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');
		add_settings_field('wpshop_payment_bank_transfer', __('Bank transfer', 'wpshop'), array('wpshop_payment_options', 'wpshop_rib_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');

		if(WPSHOP_PAYMENT_METHOD_CIC || !empty($options['cic'])) add_settings_field('wpshop_payment_cic', __('CIC payment', 'wpshop'), array('wpshop_payment_options', 'wpshop_cic_field'), 'wpshop_paymentMethod', 'wpshop_paymentMethod');

		register_setting('wpshop_options', 'wpshop_paymentMethod', array('wpshop_payment_options', 'wpshop_options_validate_default_payment_method'));
		register_setting('wpshop_options', 'wpshop_paymentMethod_options', array('wpshop_payment_options', 'wpshop_options_validate_payment_method_options'));
		register_setting('wpshop_options', 'wpshop_paymentAddress', array('wpshop_payment_options', 'wpshop_options_validate_paymentAddress'));
		register_setting('wpshop_options', 'wpshop_paypalEmail', array('wpshop_payment_options', 'wpshop_options_validate_paypalEmail'));
		register_setting('wpshop_options', 'wpshop_paypalMode', array('wpshop_payment_options', 'wpshop_options_validate_paypalMode'));
		if(WPSHOP_PAYMENT_METHOD_CIC || !empty($options['cic'])) register_setting('wpshop_options', 'wpshop_cmcic_params', array('wpshop_payment_options', 'wpshop_options_validate_cmcic_params'));

		register_setting('wpshop_options', 'wpshop_payment_partial', array('wpshop_payment_options', 'partial_payment_saver'));
		add_settings_section('wpshop_payment_partial_on_command', __('Partial payment', 'wpshop'), array('wpshop_payment_options', 'partial_payment_explanation'), 'wpshop_payment_partial_on_command');
		add_settings_field('wpshop_payment_partial', '', array('wpshop_payment_options', 'partial_payment'), 'wpshop_payment_partial_on_command', 'wpshop_payment_partial_on_command');
	}

	function wpshop_payment_options_def() {
		$output  = '';
		$output .= '<div class="payment_option_line_indicator">';
		$output .= '<div class="payment_option_default_method_part" >' .__('Default payment method', 'wpshop'). '</div>';
		$output .= '<div class="payment_option_display_position_part"  >' .__('Payment method display position', 'wpshop').'</div>';
		$output .= '<div class="payment_option_payment_configuration_part" >' .__('State and parameters', 'wpshop').'</div>';
		$output .= '</div>';
		echo $output;
	}
	// Common section description
	function plugin_section_text() {
		echo '';
	}

	/* -------------------------------- */
	/* --------- PAYMENT METHOD ------- */
	/* -------------------------------- */
	function wpshop_paymentByPaypal_field() {
		echo '';
	}

	/**
	 * Paypal configuration interface
	 */
	function wpshop_paypal_field() {
		$options = get_option('wpshop_paymentMethod');
		$paypalEmail = get_option('wpshop_paypalEmail');
		$paypalMode = get_option('wpshop_paypalMode',0);

		$output  = '<div class="payment_option_default_method_part" >';
		$output .= '<input type="radio" name="wpshop_paymentMethod[default_method]"  id="wpshop_paymentMethod[default_method][paypal]" value="paypal" ' .(( !empty($options) && !empty($options['default_method']) && $options['default_method'] == 'paypal') ? 'checked="checked"' : '' ). '/>';
		$output .= '</div>';


		$output .= '<div class="payment_option_display_position_part" >';
		$output .= '<input type="text" style="width:50px;" name="wpshop_paymentMethod[display_position][paypal]" id="wpshop_paymentMethod[display_position][paypal]" value="' . ( ( !empty($options) && !empty($options['display_position']) && !empty($options['display_position']['paypal']) ) ? $options['display_position']['paypal'] : null ). '" />';
		$output .= '</div>';

		$output .= '<div class="payment_option_payment_configuration_part" >';
		$output .= '<input type="checkbox" name="wpshop_paymentMethod[paypal]" id="paymentByPaypal" '.(!empty($options['paypal']) ?'checked="checked"' : null ).' />&nbsp;<label for="paymentByPaypal" >'.__('Activate this payment method', 'wpshop').'</label>';
		$output .= '<div class="wpshop_payment_method_parameter paymentByPaypal_content" >';
		$output .= '<label class="simple_right">'.__('Business email','wpshop').'</label> <input name="wpshop_paypalEmail" type="text" value="'.$paypalEmail.'" /><br />';
		$output .= '<label class="simple_right">'.__('Mode','wpshop').'</label>';
		$output .= '<select name="wpshop_paypalMode">';
		$output .= '<option value="normal"'.(($paypalMode=='sandbox') ? null : ' selected="selected"').'>'.__('Production mode','wpshop').'</option>';
		$output .= '<option value="sandbox"'.(($paypalMode=='sandbox') ? ' selected="selected"' : null).'>'.__('Sandbox mode','wpshop').'</option>';
		$output .= '</select>';
		$output .= '<a href="#" title="'.__('This checkbox allow to use Paypal in Sandbox mode (test) or production mode (real money)','wpshop').'" class="wpshop_infobulle_marker">?</a>';
		$output .= '</div>';
		$output .= '</div>';

		echo $output;
	}

	/**
	 * Check payment method configuration interface
	 */
	function wpshop_checks_field() {
		$options = get_option('wpshop_paymentMethod');
		$company_payment = get_option('wpshop_paymentAddress');
		$company = get_option('wpshop_company_info');

		$output  = '<div class="payment_option_default_method_part" >';
		$output .= '<input type="radio" name="wpshop_paymentMethod[default_method]" id="wpshop_paymentMethod[default_method][checks]" value="checks" ' .(( !empty($options) && !empty($options['default_method']) && $options['default_method'] == 'checks') ? 'checked="checked"' : '' ). '/>';
		$output .= '</div>';

		$output .= '<div class="payment_option_display_position_part" >';
		$output .= '<input type="text" style="width:50px;" name="wpshop_paymentMethod[display_position][checks]"  id="wpshop_paymentMethod[display_position][checks]" value="' . ( ( !empty($options) && !empty($options['display_position']) && !empty($options['display_position']['checks']) ) ? $options['display_position']['checks'] : null ). '" />';
		$output .= '</div>';

		$output .= '<div class="payment_option_payment_configuration_part" >';
		$output .= '<input name="wpshop_company_info[company_member_of_a_approved_management_center]" id="company_is_member_of_management_center" type="checkbox"'.(!empty($company['company_member_of_a_approved_management_center'])?' checked="checked"':null).' />&nbsp;<label for="company_is_member_of_management_center" >'.__('Member of an approved management center, accepting as such payments by check.', 'wpshop').'</label><a href="#" title="'.__('Is your company member of a approved management center ? Will appear in invocies.','wpshop').'" class="wpshop_infobulle_marker">?</a><br class="wpshop_cls" />';
		$output .= '<input type="checkbox" name="wpshop_paymentMethod[checks]" id="paymentByCheck" '.(!empty($options['checks'])?'checked="checked"':null).' />&nbsp;<label for="paymentByCheck" >'.__('Activate this payment method', 'wpshop').'</label><a href="#" title="'.__('Checks will be sent to address you have to type below','wpshop').'" class="wpshop_infobulle_marker">?</a><br />';
		$output .= '<div class="wpshop_payment_method_parameter paymentByCheck_content" >';
		$output .= '<label class="simple_right">'.__('Company name', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_name]" type="text" value="'.(!empty($company_payment['company_name'])?$company_payment['company_name']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('Street', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_street]" type="text" value="'.(!empty($company_payment['company_street'])?$company_payment['company_street']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('Postcode', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_postcode]" type="text" value="'.(!empty($company_payment['company_postcode'])?$company_payment['company_postcode']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('City', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_city]" type="text" value="'.(!empty($company_payment['company_city'])?$company_payment['company_city']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('Country', 'wpshop').'</label> <input name="wpshop_paymentAddress[company_country]" type="text" value="'.(!empty($company_payment['company_country'])?$company_payment['company_country']:'').'" />';
		$output .= '</div>';
		$output .= '</div>';

		echo $output;
	}

	/**
	 * Banktransfer payment method interface
	 */
	function wpshop_rib_field() {
		$options = get_option('wpshop_paymentMethod');
		$wpshop_paymentMethod_options = get_option('wpshop_paymentMethod_options');

		$output  = '<div class="payment_option_default_method_part" >';
		$output .= '<input type="radio" name="wpshop_paymentMethod[default_method]" id="wpshop_paymentMethod[default_method][banktransfer]" value="banktransfer" ' .(( !empty($options) && !empty($options['default_method']) && $options['default_method'] == 'banktransfer') ? 'checked="checked"' : '' ). '/>';
		$output .= '</div>';

		$output .= '<div class="payment_option_display_position_part" >';
		$output .= '<input type="text" style="width:50px;" name="wpshop_paymentMethod[display_position][banktransfer]"  id="wpshop_paymentMethod[display_position][banktransfer]" value="' . ( ( !empty($options) && !empty($options['display_position']) && !empty($options['display_position']['banktransfer']) ) ? $options['display_position']['banktransfer'] : null ). '" />';
		$output .= '</div>';

		$output .= '<div class="payment_option_payment_configuration_part" >';
		$output .= '<input type="checkbox" name="wpshop_paymentMethod[banktransfer]" id="paymentByBankTransfer" '.(!empty($options['banktransfer'])?'checked="checked"':null).' />&nbsp;<label for="paymentByBankTransfer" >'.__('Activate this payment method', 'wpshop').'</label><a href="#" title="'.__('When checking this box, you will allow your customer to pass order through bank transfer payment method','wpshop').'" class="wpshop_infobulle_marker">?</a><br />';
		$output .= '<div class="wpshop_payment_method_parameter paymentByBankTransfer_content" >';
		$output .= '<label class="simple_right">'.__('Bank name', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][bank_name]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bank_name'])?$wpshop_paymentMethod_options['banktransfer']['bank_name']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('IBAN', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][iban]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['iban'])?$wpshop_paymentMethod_options['banktransfer']['iban']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('BIC/SWIFT', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][bic]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['bic'])?$wpshop_paymentMethod_options['banktransfer']['bic']:'').'" /><br />';
		$output .= '<label class="simple_right">'.__('Account owner name', 'wpshop').'</label> <input name="wpshop_paymentMethod_options[banktransfer][accountowner]" type="text" value="'.(!empty($wpshop_paymentMethod_options) && !empty($wpshop_paymentMethod_options['banktransfer']) && !empty($wpshop_paymentMethod_options['banktransfer']['accountowner'])?$wpshop_paymentMethod_options['banktransfer']['accountowner']:'').'" /><br />';
		$output .= '</div>';
		$output .= '</div>';
		echo $output;

	}

	/**
	 * CIC Payment method configuration interface
	 */
	function wpshop_cic_field(){
		$options = get_option('wpshop_paymentMethod');
		$cmcic_params = get_option('wpshop_cmcic_params', array());

		$output  = '<div class="payment_option_default_method_part" >';
		$output .= '<input type="radio" name="wpshop_paymentMethod[default_method]" id="wpshop_paymentMethod[default_method][cic]" value="cic" ' .(( !empty($options) && !empty($options['default_method']) && $options['default_method'] == 'cic') ? 'checked="checked"' : '' ). '/>';
		$output .= '</div>';

		$output .= '<div class="payment_option_display_position_part" >';
		$output .= '<input type="text" style="width:50px;" name="wpshop_paymentMethod[display_position][cic]" id="wpshop_paymentMethod[display_position][cic]" value="' . ( ( !empty($options) && !empty($options['display_position']) && !empty($options['display_position']['cic']) ) ? $options['display_position']['cic'] : null ). '" />';
		$output .= '</div>';

		$output .= '<div class="payment_option_payment_configuration_part" >';
		$output .= '<input type="checkbox" name="wpshop_paymentMethod[cic]" id="paymentByCreditCard_CIC" '.(!empty($options['cic'])?'checked="checked"':null).' /><label for="paymentByCreditCard_CIC" >'.__('Activate this payment method', 'wpshop').'</label>';
		$output .= '<div class="wpshop_payment_method_parameter paymentByCreditCard_CIC_content" >';
		$output .= '<label class="simple_right">'.__('Key', 'wpshop').'</label> <input name="wpshop_cmcic_params[cle]" type="text" value="'.$cmcic_params['cle'].'" /><br />';
		$output .= '<label class="simple_right">'.__('TPE', 'wpshop').'</label> <input name="wpshop_cmcic_params[tpe]" type="text" value="'.$cmcic_params['tpe'].'" /><br />';
		$output .= '<label class="simple_right">'.__('Version', 'wpshop').'</label> <input name="wpshop_cmcic_params[version]" type="text" value="'.$cmcic_params['version'].'" /> => 3.0<br />';
		$output .= '<label class="simple_right">'.__('Serveur', 'wpshop').'</label> <input name="wpshop_cmcic_params[serveur]" type="text" value="'.$cmcic_params['serveur'].'" /><br />';
		$output .= '<label class="simple_right">'.__('Company code', 'wpshop').'</label> <input name="wpshop_cmcic_params[codesociete]" type="text" value="'.$cmcic_params['codesociete'].'" /><br />';
		$output .= '<label class="simple_right">' .__('Payment method display position', 'wpshop'). '</label> <input type="text" name="wpshop_paymentMethod[display_position][cic]" id="wpshop_paymentMethod[display_position][cic]" value="' . ( ( !empty($options) && !empty($options['display_position']) && !empty($options['display_position']['cic']) ) ? $options['display_position']['cic'] : null ). '" />';
		$output .= '</div>';
		$output .= '</div>';


		echo $output;
	}

	function wpshop_company_member_of_a_approved_management_center_field() {
	}

	/* Processing */
	function wpshop_options_validate_paymentMethod($input) {
		foreach ($input as $k => $i) {
			if ( $k != 'default_method' && !is_array($i) ) {
				$input[$k] = !empty($input[$k]) && ($input[$k]=='on');
			}
		}
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_payment_method_options($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paymentAddress($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalEmail($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_paypalMode($input) {
		return $input;
	}
	/* Processing */
	function wpshop_options_validate_cmcic_params($input) {
		return $input;
	}



	/**
	 * Partial payment explanation part
	 */
	function partial_payment_explanation() {
		_e('You can define if customer have to pay the complete amount of order or if they just have to pay a part on command and the rest later', 'wpshop');
	}
	/**
	 * Save options for partial payment. For specific treatment on choosen value, do it here
	 *
	 * @param array $input The different input sent through $_POST
	 * @return array The different values to save for current option
	 */
	function partial_payment_saver($input) {
		return $input;
	}
	/**
	 * Partial payment configuration area display
	 */
	function partial_payment() {
		$output = '';

		$partial_payment_current_config = get_option('wpshop_payment_partial', array('for_all' => array()));

		$partial_for_all_is_activate = false;
		if ( !empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['activate']) ) {
			$partial_for_all_is_activate = true;
		}

		$output .= '
<input type="checkbox" name="wpshop_payment_partial[for_all][activate]"' . ($partial_for_all_is_activate ? ' checked="checked"' : '') . ' id="wpshop_payment_partial_on_command_activation_state" /> <label for="wpshop_payment_partial_on_command_activation_state" >' . __('Activate partial command for all order', 'wpshop') . '</label><a href="#" title="'.__('If you want that customer pay a part o f total amount of there order, check this box then fill fields below','wpshop').'" class="wpshop_infobulle_marker">?</a>
<div class="wpshop_partial_payment_config_container' . ($partial_for_all_is_activate ? '' : ' wpshopHide') . '" id="wpshop_partial_payment_config_container" >
	<div class="alignleft" >
		' . __('Value of partial payment', 'wpshop') . '<br/>
		<input type="text" value="' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['value']) ? $partial_payment_current_config['for_all']['value'] : '') . '" name="wpshop_payment_partial[for_all][value]" />
	</div>
	<div class="" >
		' . __('Type of partial payment', 'wpshop') . '<br/>
		<select name="wpshop_payment_partial[for_all][type]" >
			<option value="percentage"' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && (empty($partial_payment_current_config['for_all']['type']) || $partial_payment_current_config['for_all']['type'] == 'percentage') ? ' selected="selected"' : '') . ' >' . __('%', 'wpshop') . '</option>
			<option value="amount"' . (!empty($partial_payment_current_config) && !empty($partial_payment_current_config['for_all']) && !empty($partial_payment_current_config['for_all']['type']) && ($partial_payment_current_config['for_all']['type'] == 'amount') ? ' selected="selected"' : '') . ' >' . wpshop_tools::wpshop_get_currency() . '</option>
		</select>
	</div>
</div>';

		echo $output;
	}

	function wpshop_options_validate_default_payment_method ($input) {
		return $input;
	}

}