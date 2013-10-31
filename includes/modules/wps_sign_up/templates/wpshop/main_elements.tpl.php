<?php
$tpl_element = array();
/**
 * WPSHOP SIGNUP FORM
 */
ob_start();
?>

		{WPSHOP_SIGN_UP_INTERFACE}
	

<?php
$tpl_element['wpshop']['default']['wps_sign_up_form'] = ob_get_contents();
ob_end_clean();


/**
 * WPSHOP SIGNUP FORM INTERFACE
 */
ob_start();
?>
	<?php _e('Already member ?', 'wpshop'); ?><a href="#" id="display_connexion_form"> <?php _e('Connect you', 'wpshop'); ?> !</a>
	<h2><?php _e('Your informations', 'wpshop'); ?></h2>
	<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" id="wps_sign_up_form">
		<input type="hidden" name="action" value="wps_save_account_form" />
		<div id="wps_forgot_password_alert_container">{WPSHOP_WPS_LOGIN_ALERT_MESSAGE}</div> 
		{WPSHOP_SIGN_UP_FORM_FIELDS}
		<input type="checkbox" id="wps_signup_account_creation" name="wps_signup_account_creation"/><label for="wps_signup_account_creation"><?php _e('Do you want to create an account', 'wpshop'); ?></label>
		<div id="wps_signup_account_creation_additional_fields">
			{WPSHOP_SIGN_UP_ADDITIONNAL_FIELDS}
		</div>
		<div class="wps-form-group">
			<input type="button" name="wps_sign_up_request" class="wps-bton wps-bton-prim" value="<?php _e('Next step', 'wpshop'); ?>" /> <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" class="wpshopHide" id="signup_loader"/>
		</div>
	</form>

<?php
$tpl_element['wpshop']['default']['wps_sign_up_form_interface'] = ob_get_contents();
ob_end_clean();


/**
 * WPSHOP SIGNUP FORM
 */
ob_start();
?>
<div class="wps-form-group">
	<label {WPSHOP_SIGNUP_FORM_LABEL_FOR}>{WPSHOP_SIGNUP_FORM_LABEL}</label>
	<div class="wps-form">{WPSHOP_SIGNUP_FORM_FIELD}</div>
</div>
<?php
$tpl_element['wpshop']['default']['wps_sign_up_form_field'] = ob_get_contents();
ob_end_clean();