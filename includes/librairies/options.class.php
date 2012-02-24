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

/** Stocke les erreurs de saisies */
$options_errors = array();

class wpshop_options
{
	/**
	*	Declare the different options for the plugin	
	*/
	function add_options() {
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
	function option_main_page(){
		global $options_errors;

		if(isset($_POST['submit'])) {
			$options = array(
				'useSpecialPermalink' => isset($_POST['useSpecialPermalink']) && $_POST['useSpecialPermalink']=='on',
				'exampleProduct' => isset($_POST['exampleProduct']) && $_POST['exampleProduct']=='on',
				'paymentByPaypal' => isset($_POST['paymentByPaypal']) && $_POST['paymentByPaypal']=='on',
				'paymentByChecks' => isset($_POST['paymentByChecks']) && $_POST['paymentByChecks']=='on',
				'paypalEmail' => isset($_POST['paypalEmail']) ? $_POST['paypalEmail'] : null,
				'paypalMode' => !empty($_POST['paypalMode']) ? $_POST['paypalMode'] : null,
				
				'company_info_legal_statut' => !empty($_POST['company_info_legal_statut']) ? $_POST['company_info_legal_statut'] : null,
				'company_info_capital' => !empty($_POST['company_info_capital']) ? $_POST['company_info_capital'] : null,
				'company_info_name' => !empty($_POST['company_info_name']) ? $_POST['company_info_name'] : null,
				'company_info_street' => !empty($_POST['company_info_street']) ? $_POST['company_info_street'] : null,
				'company_info_postcode' => !empty($_POST['company_info_postcode']) ? $_POST['company_info_postcode'] : null,
				'company_info_city' => !empty($_POST['company_info_city']) ? $_POST['company_info_city'] : null,
				'company_info_country' => !empty($_POST['company_info_country']) ? $_POST['company_info_country'] : null,
				
				'company_name' => !empty($_POST['company_name']) ? $_POST['company_name'] : null,
				'company_street' => !empty($_POST['company_street']) ? $_POST['company_street'] : null,
				'company_postcode' => !empty($_POST['company_postcode']) ? $_POST['company_postcode'] : null,
				'company_city' => !empty($_POST['company_city']) ? $_POST['company_city'] : null,
				'company_country' => !empty($_POST['company_country']) ? $_POST['company_country'] : null,
				'NOREPLY_EMAIL' => !empty($_POST['NOREPLY_EMAIL']) ? $_POST['NOREPLY_EMAIL'] : null,
				'CONTACT_EMAIL' => !empty($_POST['CONTACT_EMAIL']) ? $_POST['CONTACT_EMAIL'] : null,
				'WPSHOP_SIGNUP_MESSAGE_OBJECT' => !empty($_POST['WPSHOP_SIGNUP_MESSAGE_OBJECT']) ? $_POST['WPSHOP_SIGNUP_MESSAGE_OBJECT'] : null,
				'WPSHOP_SIGNUP_MESSAGE' => !empty($_POST['WPSHOP_SIGNUP_MESSAGE']) ? $_POST['WPSHOP_SIGNUP_MESSAGE'] : null,
				'WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT' => !empty($_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT']) ? $_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'] : null,
				'WPSHOP_ORDER_CONFIRMATION_MESSAGE' => !empty($_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE']) ? $_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE'] : null,
				'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => !empty($_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']) ? $_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] : null,
				'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE' => !empty($_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE']) ? $_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'] : null,
				'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => !empty($_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']) ? $_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] : null,
				'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE' => !empty($_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE']) ? $_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'] : null,
				'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT' => !empty($_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT']) ? $_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'] : null,
				'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE' => !empty($_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE']) ? $_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'] : null,
				
				'billing_number_figures' => !empty($_POST['billing_number_figures']) ? $_POST['billing_number_figures'] : null,
			);
			$bool = true;
			
			/** Liste des erreurs vide */
			$options_errors = array();
			
			/** Emails paypal */
			if($options['paymentByPaypal']) {
				if(empty($options['paypalEmail'])) {
					$options_errors['paypalEmail'] = __('You have to type a Paypal email adress.', 'wpshop');
					$bool = false;
				}
				elseif(!is_email($options['paypalEmail'])) {
					$options_errors['paypalEmail'] = __('Paypal email adress invalid.', 'wpshop');
					$bool = false;
				}
			}
			/** Coordonnées société */
			if(empty($options['company_info_legal_statut'])) {
				$options_errors['company_info_legal_statut'] = __('You have to type a legal status for your company.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_capital'])) {
				$options_errors['company_info_capital'] = __('You have to type a capital.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_name'])) {
				$options_errors['company_info_name'] = __('You have to type a company name.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_street'])) {
				$options_errors['company_info_street'] = __('You have to type a company street.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_postcode'])) {
				$options_errors['company_info_postcode'] = __('You have to type a company postcode.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_city'])) {
				$options_errors['company_info_city'] = __('You have to type a company city.', 'wpshop');
				$bool = false;
			}
			if(empty($options['company_info_country'])) {
				$options_errors['company_info_country'] = __('You have to type a company country.', 'wpshop');
				$bool = false;
			}
			/** Paiment par chéques */
			if($options['paymentByChecks']) {
				if(empty($options['company_name'])) {
					$options_errors['company_name'] = __('You have to type a company name.', 'wpshop');
					$bool = false;
				}
				if(empty($options['company_street'])) {
					$options_errors['company_street'] = __('You have to type a company street.', 'wpshop');
					$bool = false;
				}
				if(empty($options['company_postcode'])) {
					$options_errors['company_postcode'] = __('You have to type a company postcode.', 'wpshop');
					$bool = false;
				}
				if(empty($options['company_city'])) {
					$options_errors['company_city'] = __('You have to type a company city.', 'wpshop');
					$bool = false;
				}
				if(empty($options['company_country'])) {
					$options_errors['company_country'] = __('You have to type a company country.', 'wpshop');
					$bool = false;
				}
			}
			/** Adresses emails */
			if(!is_email($options['NOREPLY_EMAIL'])) {
				$options_errors['NOREPLY_EMAIL'] = __('Answers email is syntactically incorrect', 'wpshop');
				$bool = false;
			}
			if(!is_email($options['CONTACT_EMAIL'])) {
				$options_errors['CONTACT_EMAIL'] = __('Contact email addess is syntactically incorrect', 'wpshop');
				$bool = false;
			}
			
			/* Facturation */
			if(empty($options['billing_number_figures'])) {
				$options_errors['billing_number_figures'] = __('You have to type the number of figures to use in billing', 'wpshop');
				$bool = false;
			}
			elseif($options['billing_number_figures']<1 OR $options['billing_number_figures']>10) {
				$options_errors['billing_number_figures'] = __('Nulber of figures must be bigger than 0 and smaller than 10', 'wpshop');
				$bool = false;
			}
			
			/** Champs messages personnalisés */
			if(empty($options['WPSHOP_SIGNUP_MESSAGE_OBJECT'])) {
				$options_errors['WPSHOP_SIGNUP_MESSAGE_OBJECT'] = __('Signup message object must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_SIGNUP_MESSAGE'])) {
				$options_errors['WPSHOP_SIGNUP_MESSAGE'] = __('Signup message must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'])) {
				$options_errors['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'] = __('Order confirmation message object must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_ORDER_CONFIRMATION_MESSAGE'])) {
				$options_errors['WPSHOP_ORDER_CONFIRMATION_MESSAGE'] = __('Order confirmation message must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])) {
				$options_errors['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] = __('Paypal payment confirmation message object must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'])) {
				$options_errors['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'] = __('Paypal payment confirmation message must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])) {
				$options_errors['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] = __('Payment confirmation message object must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'])) {
				$options_errors['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'] = __('Payment confirmation message must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'])) {
				$options_errors['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'] = __('Shipping confirmation message object must be filled', 'wpshop');
				$bool = false;
			}
			if(empty($options['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'])) {
				$options_errors['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'] = __('Shipping confirmation message must be filled', 'wpshop');
				$bool = false;
			}
			
			if($bool) {
				// Si le plugin est déjà installé et que l'utilisateur modifie sa config
				if(!empty($_POST['submitMode']) && $_POST['submitMode'] == 'save'){
					wpshop_install::save_config($options, $install=false);
				}
				// Sinon installation
				else{
					wpshop_install::install_wpshop($options);
					wpshop_tools::wpshop_safe_redirect('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
				}
			}
		}

		/*	Get current plugin version	*/
		$current_db_version = get_option('wpshop_db_options', 0);

		/** Listage des erreurs */
		$error='';
		if(!empty($options_errors)) {
			$error='<ul>';
			foreach($options_errors as $o) {
				$error.='<li>'.$o.'</li>';
			}
			$error.='</ul>';
		}

		// Si la bdd est installée
		if(isset($current_db_version['db_version']) && $current_db_version['db_version']>0) {
		
			// On récupère les informations de paiements
			$paymentInfo = get_option('wpshop_paymentAddress', null);
			$paypalEmail = get_option('wpshop_paypalEmail', null);
			$paypalMode = get_option('wpshop_paypalMode', null);
			$paymentMethod = get_option('wpshop_paymentMethod', null);
			$emails = get_option('wpshop_emails', null);
			$company = get_option('wpshop_company_info', array());

			$data_company_info = array(
				'company_info_legal_statut' => !empty($company['company_legal_statut']) ? $company['company_legal_statut'] : null,
				'company_info_capital' => !empty($company['company_capital']) ? $company['company_capital'] : null,
				'company_info_name' => !empty($company['company_name']) ? $company['company_name'] : null,
				'company_info_street' => !empty($company['company_street']) ? $company['company_street'] : null,
				'company_info_postcode' => !empty($company['company_postcode']) ? $company['company_postcode'] : null,
				'company_info_city' => !empty($company['company_city']) ? $company['company_city'] : null,
				'company_info_country' => !empty($company['company_country']) ? $company['company_country'] : null
			);
			
			$data_payment_method = array(
				'paypalEmail' => $paypalEmail,
				'paypalMode' => $paypalMode,
				'paymentMethod' => $paymentMethod,
				'company_name' => !empty($paymentInfo['company_name']) ? $paymentInfo['company_name'] : null,
				'company_street' => !empty($paymentInfo['company_street']) ? $paymentInfo['company_street'] : null,
				'company_postcode' => !empty($paymentInfo['company_postcode']) ? $paymentInfo['company_postcode'] : null,
				'company_city' => !empty($paymentInfo['company_city']) ? $paymentInfo['company_city'] : null,
				'company_country' => !empty($paymentInfo['company_country']) ? $paymentInfo['company_country'] : null
			);
			$data_emails = array(
				'NOREPLY_EMAIL' => !empty($emails['noreply_email']) ? $emails['noreply_email'] : null,
				'CONTACT_EMAIL' => !empty($emails['contact_email']) ? $emails['contact_email'] : null
			);
			$data_customs_mails = array(
				'WPSHOP_SIGNUP_MESSAGE_OBJECT' => get_option('WPSHOP_SIGNUP_MESSAGE_OBJECT',WPSHOP_SIGNUP_MESSAGE_OBJECT),
				'WPSHOP_SIGNUP_MESSAGE' => get_option('WPSHOP_SIGNUP_MESSAGE',WPSHOP_SIGNUP_MESSAGE),
				'WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT' => get_option('WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT',WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT),
				'WPSHOP_ORDER_CONFIRMATION_MESSAGE' => get_option('WPSHOP_ORDER_CONFIRMATION_MESSAGE',WPSHOP_ORDER_CONFIRMATION_MESSAGE),
				'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => get_option('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT',WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT),
				'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE' => get_option('WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE',WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE),
				'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => get_option('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT',WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT),
				'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE' => get_option('WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE',WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE),
				'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT' => get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT',WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT),
				'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE' => get_option('WPSHOP_SHIPPING_CONFIRMATION_MESSAGE',WPSHOP_SHIPPING_CONFIRMATION_MESSAGE)
			);
			
			// Nombre de chiffres pour les factures
			$number_figures = get_option('wpshop_billing_number_figures', 5);
			$data_billing_settings = array(
				'number_figures' => $number_figures
			);
			
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
								
								'.(!empty($error)?'<div class="error"><p>'.$error.'</p></div>':null).'
								
								<div class="simple">
									'.self::wpshop_options_company_info_form($data_company_info,$options_errors).'
								</div>
								
								<div class="simple">
									'.self::wpshop_options_payment_method_form($data_payment_method,$options_errors).'
								</div>
								
								<div class="simple">
									'.self::wpshop_options_billing_settings_form($data_billing_settings,$options_errors).'
								</div>
								
								<div class="simple">
									'.self::wpshop_options_emails_form($data_emails,$options_errors).'
								</div>
								
								<div class="simple">
									'.self::wpshop_options_customs_mails_form($data_customs_mails,$options_errors).'
								</div>
								
								<input type="hidden" name="submitMode" value="save" />
								
								<input type="submit" name="submit" id="submit" class="button-primary" value="'.__('Save the settings', 'wpshop').'" />
								
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
		else{
			if(WPSHOP_DEBUG_MODE && in_array(long2ip(ip2long($_SERVER['REMOTE_ADDR'])), unserialize(WPSHOP_DEBUG_ALLOWED_IP))){
				echo '<span class="fill_form_for_test" >Fill the form for test</span>';
			}
			$title = __('Plugin general settings', 'wpshop');
			$warning = __('Before installation, thanks to choose the configuration settings to apply to the plugin WP-Shop.', 'wpshop');
			$h3 = __('To works correctly, WP-Shop requires the use of a custom permalinks structure like <code>/%postname%</code>. It is therefore strongly advised to keep the option permalinks checked.', 'wpshop');
echo '
<div class="wrap">
	<form method="post">
		<div id="icon-options-general" class="icon32"><br /></div>
		
		<h2>'.$title.'</h2>
		
		'.(!empty($error)?'<div class="error"><p>'.$error.'</p></div>':null).'
		
		<p>'.$warning.'</p>
		
		<input type="hidden" name="useSpecialPermalink_confirmMessage" value="'.__('Are you sure you want to uncheck this option ? The plugin may not work correctly ... Confirm please.','wpshop').'" />
		
		<div class="simple">
			<h3 style="margin-top:0px;">'.$h3.'</h3>
			<table class="table_option">
			<tr>
				<td><label class="simple">'.__('Permalinks', 'wpshop').'</label></td>
				<td><input type="checkbox" name="useSpecialPermalink" checked="checked" /> '.__('Use the custom permalinks structure', 'wpshop').'</td>
			</tr>
			</table>
		</div>
		
		<div class="simple">
			<table class="table_option">
			<tr>
				<td><label class="simple">'.__('Products', 'wpshop').'</label></td>
				<td><input type="checkbox" name="exampleProduct" checked="checked" /> '.__('Add a example product to the database', 'wpshop').'</td>
			</tr>
			</table>
		</div>
		
		<div class="simple">
			'.self::wpshop_options_company_info_form(array(),$options_errors).'
		</div>
		
		<div class="simple">
			'.self::wpshop_options_payment_method_form(array(),$options_errors).'
		</div>
		
		<div class="simple">
			'.self::wpshop_options_billing_settings_form(array(),$options_errors).'
		</div>
		
		<div class="simple">
			'.self::wpshop_options_emails_form(array(),$options_errors).'
		</div>
		
		<div class="simple">
			'.self::wpshop_options_customs_mails_form(array(),$options_errors).'
		</div>
		
		<input type="submit" name="submit" id="submit" class="button-primary" value="'.__('Save the settings', 'wpshop').'" />
		
	</form>
</div>';
		}
	}
	
	function wpshop_options_billing_settings_form($data, $errors) {
		$data = array(
			'billing_number_figures' => !empty($data['number_figures']) ? $data['number_figures'] : null,
		);
		
		$readonly = !empty($data['billing_number_figures']) ? 'readonly="readonly""': null;
		
		return '
		<table class="table_option">
			<tr>
				<td class="top"><label class="simple">'.__('Billing settings', 'wpshop').'</label></td>
				<td>
					<label for="billing_number_figures">'.__('Number of figures', 'wpshop').'</label>
					<input type="text" name="billing_number_figures" id="billing_number_figures" '.$readonly.' value="'.(!empty($_POST['billing_number_figures'])?$_POST['billing_number_figures']:$data['billing_number_figures']).'" class="'.(isset($errors['billing_number_figures'])?'error':null).'" />
				</td>
			</tr>
		</table>';
	}
	
	function wpshop_options_company_info_form($data, $errors) {
		
		$data = array(
			'company_info_legal_statut' => !empty($data['company_info_legal_statut']) ? $data['company_info_legal_statut'] : null,
			'company_info_capital' => !empty($data['company_info_capital']) ? $data['company_info_capital'] : null,
			'company_info_name' => !empty($data['company_info_name']) ? $data['company_info_name'] : null,
			'company_info_street' => !empty($data['company_info_street']) ? $data['company_info_street'] : null,
			'company_info_postcode' => !empty($data['company_info_postcode']) ? $data['company_info_postcode'] : null,
			'company_info_city' => !empty($data['company_info_city']) ? $data['company_info_city'] : null,
			'company_info_country' => !empty($data['company_info_country']) ? $data['company_info_country'] : null,
		);
		
		$legal_status = array(
			'autoentrepreneur' => 'Auto-Entrepreneur',
			'eurl' => 'EURL',
			'sarl' => 'SARL',
			'sa' => 'SA',
			'sas' => 'SAS',
		);
		$select_legal_statut='<select name="company_info_legal_statut">';
		foreach($legal_status as $key=>$value) {
			$selected = $data['company_info_legal_statut']==$key ? ' selected="selected"' : null;
			$select_legal_statut.='<option value="'.$key.'"'.$selected.'>'.__($value,'wpshop').'</option>';
		}
		$select_legal_statut.='</select>';
		
		return '
		<table class="table_option">
							<tr>
								<td class="top"><label class="simple">'.__('Company info', 'wpshop').'</label></td>
								<td>
									<table class="table_mini_bloc">
										<tr>
											<td>
												<label for="company_info_legal_statut">'.__('Legal status', 'wpshop').'</label><br />
												'.$select_legal_statut.'
											</td>
											<td>
												<label for="company_info_capital">'.__('Capital', 'wpshop').' EUR</label><br />
												<input type="text" name="company_info_capital" id="company_info_capital" value="'.(!empty($_POST['company_info_capital'])?$_POST['company_info_capital']:$data['company_info_capital']).'" class="'.(isset($errors['company_info_capital'])?'error':null).'" />
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<label for="company_info_name">'.__('Company name', 'wpshop').'</label><br />
												<input type="text" name="company_info_name" id="company_info_name" value="'.(!empty($_POST['company_info_name'])?$_POST['company_info_name']:$data['company_info_name']).'" class="'.(isset($errors['company_info_name'])?'error':null).'" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="company_info_street">'.__('Street', 'wpshop').'</label><br />
												<input type="text" name="company_info_street" id="company_info_street" value="'.(!empty($_POST['company_info_street'])?$_POST['company_info_street']:$data['company_info_street']).'" class="'.(isset($errors['company_info_street'])?'error':null).'" />
											</td>
											<td>
												<label for="company_info_postcode">'.__('Postcode', 'wpshop').'</label><br />
												<input type="text" name="company_info_postcode" id="company_info_postcode" value="'.(!empty($_POST['company_info_postcode'])?$_POST['company_info_postcode']:$data['company_info_postcode']).'" class="'.(isset($errors['company_info_postcode'])?'error':null).'" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="company_info_city">'.__('City', 'wpshop').'</label><br />
												<input type="text" name="company_info_city" id="company_info_city" value="'.(!empty($_POST['company_info_city'])?$_POST['company_info_city']:$data['company_info_city']).'" class="'.(isset($errors['company_info_city'])?'error':null).'" />
											</td>
											<td>
												<label for="company_info_country">'.__('Country', 'wpshop').'</label><br />
												<input type="text" name="company_info_country" id="company_info_country" value="'.(!empty($_POST['company_info_country'])?$_POST['company_info_country']:$data['company_info_country']).'" class="'.(isset($errors['company_info_country'])?'error':null).'" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
							</table>';
							
	}
	
	function wpshop_options_payment_method_form($data, $errors) {
		
		$data = array(
			'paymentMethod' => !empty($data['paymentMethod']) ? $data['paymentMethod'] : null,
			'paypalEmail' => !empty($data['paypalEmail']) ? $data['paypalEmail'] : null,
			'paypalEmail' => !empty($data['paypalEmail']) ? $data['paypalEmail'] : null,
			'paypalMode' => !empty($data['paypalMode']) ? $data['paypalMode'] : null,
			'company_name' => !empty($data['company_name']) ? $data['company_name'] : null,
			'company_street' => !empty($data['company_street']) ? $data['company_street'] : null,
			'company_postcode' => !empty($data['company_postcode']) ? $data['company_postcode'] : null,
			'company_city' => !empty($data['company_city']) ? $data['company_city'] : null,
			'company_country' => !empty($data['company_country']) ? $data['company_country'] : null,
		);

		return '
		<table class="table_option">
							<tr>
								<td><label class="simple">'.__('Payment method', 'wpshop').'</label></td>
								<td style="width:200px;"><input type="checkbox" name="paymentByPaypal" id="paymentByPaypal" ' . ($data['paymentMethod']['paypal'] ? ' checked="checked" ' : '') . ' /> '.__('Allow <strong>Paypal</strong>', 'wpshop').'</td>
								<td>
										<label for="paypalEmail">'.__('Paypal business email', 'wpshop').'</label><br />
										<input type="text" name="paypalEmail" id="paypalEmail" value="'.(!empty($_POST['paypalEmail'])?$_POST['paypalEmail']:$data['paypalEmail']).'" class="'.(isset($errors['paypalEmail'])?'error':null).'" /> 
										<select name="paypalMode">
											<option value="normal"'.((!empty($data['paypalMode']) && $data['paypalMode']=='sandbox') ? null : ' selected="selected"').'>Classique</option>
											<option value="sandbox"'.((!empty($data['paypalMode']) && $data['paypalMode']=='sandbox') ? ' selected="selected"' : null).'>Sandbox</option>
										</select>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><input type="checkbox" name="paymentByChecks" id="paymentByChecks" ' . ($data['paymentMethod']['checks'] ? ' checked="checked" ' : '') . ' /> '.__('Allow <strong>checks</strong>', 'wpshop').'</td>
								<td>
									<table class="table_mini_bloc">
										<tr>
											<td colspan="2">
												<label for="company_name">'.__('Company name', 'wpshop').'</label><br />
												<input type="text" name="company_name" id="company_name" value="'.(!empty($_POST['company_name'])?$_POST['company_name']:$data['company_name']).'" class="'.(isset($errors['company_name'])?'error':null).'" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="company_street">'.__('Street', 'wpshop').'</label><br />
												<input type="text" name="company_street" id="company_street" value="'.(!empty($_POST['company_street'])?$_POST['company_street']:$data['company_street']).'" class="'.(isset($errors['company_street'])?'error':null).'" />
											</td>
											<td>
												<label for="company_postcode">'.__('Postcode', 'wpshop').'</label><br />
												<input type="text" name="company_postcode" id="company_postcode" value="'.(!empty($_POST['company_postcode'])?$_POST['company_postcode']:$data['company_postcode']).'" class="'.(isset($errors['company_postcode'])?'error':null).'" />
											</td>
										</tr>
										<tr>
											<td>
												<label for="company_city">'.__('City', 'wpshop').'</label><br />
												<input type="text" name="company_city" id="company_city" value="'.(!empty($_POST['company_city'])?$_POST['company_city']:$data['company_city']).'" class="'.(isset($errors['company_city'])?'error':null).'" />
											</td>
											<td>
												<label for="company_country">'.__('Country', 'wpshop').'</label><br />
												<input type="text" name="company_country" id="company_country" value="'.(!empty($_POST['company_country'])?$_POST['company_country']:$data['company_country']).'" class="'.(isset($errors['company_country'])?'error':null).'" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
							</table>';
							
	}
	function wpshop_options_emails_form($data, $errors) {
		$admin_email = get_bloginfo('admin_email');
		$data = array(
			'NOREPLY_EMAIL' => !empty($data['NOREPLY_EMAIL']) ? $data['NOREPLY_EMAIL'] : $admin_email,
			'CONTACT_EMAIL' => !empty($data['CONTACT_EMAIL']) ? $data['CONTACT_EMAIL'] : $admin_email
		);
		
		return '
		<table class="table_option">
								<tr>
									<td class="top"><label class="simple">'.__('Email addresses', 'wpshop').'</label></td>
									<td style="width:200px;"><label class="simple" style="font-weight:normal;" for="NOREPLY_EMAIL">'.__('Mails answers address email', 'wpshop').'</label></td>
									<td><input type="text" name="NOREPLY_EMAIL" id="NOREPLY_EMAIL" value="'.(!empty($_POST['NOREPLY_EMAIL'])?$_POST['NOREPLY_EMAIL']:$data['NOREPLY_EMAIL']).'" class="'.(isset($errors['NOREPLY_EMAIL'])?'error':null).'" /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label class="simple" style="font-weight:normal;" for="CONTACT_EMAIL">'.__('Contact email', 'wpshop').'</label></td>
									<td><input type="text" name="CONTACT_EMAIL" id="CONTACT_EMAIL" value="'.(!empty($_POST['CONTACT_EMAIL'])?$_POST['CONTACT_EMAIL']:$data['CONTACT_EMAIL']).'" class="'.(isset($errors['CONTACT_EMAIL'])?'error':null).'" /></td>
								</tr>
							</table>';
	}
	function wpshop_options_customs_mails_form($data, $errors) {
		$data = array(
			'WPSHOP_SIGNUP_MESSAGE_OBJECT' => !empty($data['WPSHOP_SIGNUP_MESSAGE_OBJECT']) ? $data['WPSHOP_SIGNUP_MESSAGE_OBJECT'] : WPSHOP_SIGNUP_MESSAGE_OBJECT,
			'WPSHOP_SIGNUP_MESSAGE' => !empty($data['WPSHOP_SIGNUP_MESSAGE']) ? $data['WPSHOP_SIGNUP_MESSAGE'] : WPSHOP_SIGNUP_MESSAGE,
			'WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT' => !empty($data['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT']) ? $data['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'] : WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT,
			'WPSHOP_ORDER_CONFIRMATION_MESSAGE' => !empty($data['WPSHOP_ORDER_CONFIRMATION_MESSAGE']) ? $data['WPSHOP_ORDER_CONFIRMATION_MESSAGE'] : WPSHOP_ORDER_CONFIRMATION_MESSAGE,
			'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => !empty($data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']) ? $data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] : WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT,
			'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE' => !empty($data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE']) ? $data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'] : WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE,
			'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT' => !empty($data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']) ? $data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'] : WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT,
			'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE' => !empty($data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE']) ? $data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'] : WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE,
			'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT' => !empty($data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT']) ? $data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'] : WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT,
			'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE' => !empty($data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE']) ? $data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'] : WPSHOP_SHIPPING_CONFIRMATION_MESSAGE,
		);
		
		return '
		<h3 style="margin-top:0px;">'.__('Some emails can be customized from the settings page of the plugin. Here is a list of the various tags available','wpshop').' :</h3>
							'.__('Customer first name', 'wpshop').' <code>[customer_first_name]</code> &bull;
							'.__('Customer last name', 'wpshop').' <code>[customer_last_name]</code> &bull;
							'.__('Order id', 'wpshop').' <code>[order_key]</code> &bull;
							'.__('Paypal transaction id', 'wpshop').' <code>[paypal_order_key]</code><br /><br />
							
							<table class="table_option">
								<tr>
									<td class="top"><label class="simple">'.__('Email messages', 'wpshop').'</label></td>
									<td style="width:200px;"><label class="simple" style="font-weight:normal;">'.__('Signup message', 'wpshop').'</label></td>
									<td>
										<table class="table_mini_bloc">
											<tr>
												<td style="width:85px;"><label for="WPSHOP_SIGNUP_MESSAGE_OBJECT">'.__('Subject','wpshop').'</label> :</td>
												<td><input type="text" name="WPSHOP_SIGNUP_MESSAGE_OBJECT" id="WPSHOP_SIGNUP_MESSAGE_OBJECT" class="large'.(isset($errors['WPSHOP_SIGNUP_MESSAGE_OBJECT'])?' error':null).'" value="'.__(!empty($_POST['WPSHOP_SIGNUP_MESSAGE_OBJECT'])?$_POST['WPSHOP_SIGNUP_MESSAGE_OBJECT']:$data['WPSHOP_SIGNUP_MESSAGE_OBJECT'], 'wpshop').'" /></td>
											</tr>
											<tr>
												<td><label for="WPSHOP_SIGNUP_MESSAGE">'.__('Message','wpshop').'</label> :</td>
												<td><textarea name="WPSHOP_SIGNUP_MESSAGE" id="WPSHOP_SIGNUP_MESSAGE" class="'.(isset($errors['WPSHOP_SIGNUP_MESSAGE'])?'error':null).'" >'.__(!empty($_POST['WPSHOP_SIGNUP_MESSAGE'])?$_POST['WPSHOP_SIGNUP_MESSAGE']:$data['WPSHOP_SIGNUP_MESSAGE'], 'wpshop').'</textarea></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label class="simple" style="font-weight:normal;">'.__('Order confirmation message', 'wpshop').'</label></td>
									<td>
										<table class="table_mini_bloc">
											<tr>
												<td style="width:85px;"><label for="WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT">'.__('Subject','wpshop').'</label> :</td>
												<td><input type="text" name="WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT" id="WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT" class="large'.(isset($errors['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'])?' error':null).'" value="'.__(!empty($_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'])?$_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT']:$data['WPSHOP_ORDER_CONFIRMATION_MESSAGE_OBJECT'], 'wpshop').'" /></td>
											</tr>
											<tr>
												<td><label for="WPSHOP_ORDER_CONFIRMATION_MESSAGE">'.__('Message','wpshop').'</label> :</td>
												<td><textarea name="WPSHOP_ORDER_CONFIRMATION_MESSAGE" id="WPSHOP_ORDER_CONFIRMATION_MESSAGE" class="'.(isset($errors['WPSHOP_ORDER_CONFIRMATION_MESSAGE'])?'error':null).'">'.__(!empty($_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE'])?$_POST['WPSHOP_ORDER_CONFIRMATION_MESSAGE']:$data['WPSHOP_ORDER_CONFIRMATION_MESSAGE'], 'wpshop').'</textarea></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label class="simple" style="font-weight:normal;">'.__('Paypal payment confirmation message', 'wpshop').'</label></td>
									<td>
										<table class="table_mini_bloc">
											<tr>
												<td style="width:85px;"><label for="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT">'.__('Subject','wpshop').'</label> :</td>
												<td><input type="text" name="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" id="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" class="large'.(isset($errors['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])?' error':null).'" value="'.__(!empty($_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])?$_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']:$data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'], 'wpshop').'" /></td>
											</tr>
											<tr>
												<td><label for="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE">'.__('Message','wpshop').'</label> :</td>
												<td><textarea name="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE" id="WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE" class="'.(isset($errors['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'])?'error':null).'">'.__(!empty($_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'])?$_POST['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE']:$data['WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE'], 'wpshop').'</textarea></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label class="simple" style="font-weight:normal;">'.__('Others payment confirmation message', 'wpshop').'</label></td>
									<td>
										<table class="table_mini_bloc">
											<tr>
												<td style="width:85px;"><label for="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT">'.__('Subject','wpshop').'</label> :</td>
												<td><input type="text" name="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" id="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT" class="large'.(isset($errors['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])?' error':null).'" value="'.__(!empty($_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'])?$_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT']:$data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE_OBJECT'], 'wpshop').'" /></td>
											</tr>
											<tr>
												<td><label for="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE">'.__('Message','wpshop').'</label> :</td>
												<td><textarea name="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE" id="WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE" class="'.(isset($errors['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'])?'error':null).'">'.__(!empty($_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'])?$_POST['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE']:$data['WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE'], 'wpshop').'</textarea></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><label class="simple" style="font-weight:normal;">'.__('Shipping confirmation message', 'wpshop').'</label></td>
									<td>
										<table class="table_mini_bloc">
											<tr>
												<td style="width:85px;"><label for="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT">'.__('Subject','wpshop').'</label> :</td>
												<td><input type="text" name="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT" id="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT" class="large'.(isset($errors['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'])?' error':null).'" value="'.__(!empty($_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'])?$_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT']:$data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE_OBJECT'], 'wpshop').'" /></td>
											</tr>
											<tr>
												<td><label for="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE">'.__('Message','wpshop').'</label> :</td>
												<td><textarea name="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE" id="WPSHOP_SHIPPING_CONFIRMATION_MESSAGE" class="'.(isset($errors['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'])?'error':null).'">'.__(!empty($_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'])?$_POST['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE']:$data['WPSHOP_SHIPPING_CONFIRMATION_MESSAGE'], 'wpshop').'</textarea></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>';
	}

	/**
	*
	*/
	function wpshop_options_validator(){
	
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
		$option_field_output = '';
		global $wpshop_display_option, $current_user;
		$field_identifier = 'wpshop_display_reset_template_element';

		$last_reset_infos = '&nbsp;';
		if($wpshop_display_option[$field_identifier] != ''){
			$infos = explode('dateofreset', $wpshop_display_option[$field_identifier]);
			if($infos[0] > 0){
				$user_first_name = get_user_meta($infos[0], 'first_name', true);
				$user_first_name = ($user_first_name != '') ? $user_first_name : get_userdata($infos[0])->user_nicename;
				$user_last_name = get_user_meta($infos[0], 'last_name', true);
				$user_last_name = ($user_last_name != '') ? $user_last_name :'';
				$last_reset_infos = sprintf(__('Last template reset was made by %s on %s', 'wpshop'), $user_first_name . '&nbsp;' . $user_last_name, mysql2date('d/m/Y H:i', $infos[1], true));
			}
		}

		if(current_user_can('wpshop_edit_options')){
			/*	Allows to specify a given list of file to overwrite in template	*/
			$option_field_output .= '<div id="wpshop_option_tpl_updater_display" >' . __('Choose the different template files to update', 'wpshop') . '</div>
			<div class="wpshopHide" id="wpshop_option_tpl_updater" >
				<div><span id="tpl_updater_check_all" class="tpl_updater_mass_action" >' . __('Check all', 'wpshop') . '</span>&nbsp;/&nbsp;<span id="tpl_updater_uncheck_all" class="tpl_updater_mass_action" >' . __('Uncheck all', 'wpshop') . '</span></div>
				' . wpshop_display::list_template_files(WPSHOP_TEMPLATES_DIR . 'wpshop') . '
			</div><br/>';

			$option_field_output .= wpshop_form::form_input('wpshop_display_option[' . $field_identifier . ']', $field_identifier, $wpshop_display_option[$field_identifier], 'hidden', ' readonly="readonly" ') . '
<input type="button" value="' . __('Reset template file with default plugin file', 'wpshop') . '" name="reset_template_file" id="reset_template_file" class="button-secondary" /><div id="last_reset_infos" >' . $last_reset_infos . '</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_option_tpl_updater_display").click(function(){
			jQuery("#wpshop_option_tpl_updater").toggle();
		});
		jQuery("#tpl_updater_check_all").click(function(){
			jQuery(".template_file_to_replace_checkbox").each(function(){
				jQuery(this).prop("checked", true);
			});
		});
		jQuery("#tpl_updater_uncheck_all").click(function(){
			jQuery(".template_file_to_replace_checkbox").each(function(){
				jQuery(this).prop("checked", false);
			});
		});
		jQuery("#reset_template_file").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('All modification applied to template file will be lost!\r\n\r\nAre you sure you want to reset template?', 'wpshop') . '"))){
				jQuery("#' . $field_identifier . '").val("' . $current_user->ID . 'dateofreset' . date('Y-m-d H:i:s') . '");
				var tpl_file_list = "";
				jQuery(".template_file_to_replace_checkbox").each(function(){
					if(jQuery(this).is(":checked")){
						tpl_file_list += jQuery(this).val() + "!#!";
					}
				});
				jQuery("#last_reset_infos").load(WPSHOP_AJAX_FILE_URL, {
					"post": "true",
					"elementCode": "templates",
					"action": "reset_template_files",
					"tpl_file_list": tpl_file_list,
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
