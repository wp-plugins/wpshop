<?php
$tpl_element = array();
/**
 * FORGOT PASSWORD INTERFACE
 */
ob_start();
?>
	<div id="wps_forgot_password_alert_container"></div> 
	<h2><?php _e('Forgotten password', 'wpshop'); ?></h2>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_forgot_password_form" >
		<div class="wps-form-group">
			<label for="wps_new_password_request"><?php _e('Your email address', 'wpshop');?></label>
			<div class="wps-form"><input type="text" name="wps_user_login"  class="input" size="20" id="wps_new_password_request" /></div>
		</div>
		<input type="hidden" name="action" value="wps_forgot_password_request" />
	</form>
	<div class="wps-form-group"><button id="wps_send_forgot_password_request" class="wps-bton wps-bton-prim"><?php _e('Renew your password', 'wpshop')?></button>  <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" id="wps_request_password_loader" class="wpshopHide" /></div>

<?php
$tpl_element['wpshop']['default']['wps_forgot_password_form_request'] = ob_get_contents();
ob_end_clean();


/**
 * FORGOT PASSWORD INIT INTERFACE
 */
ob_start();
?>
	<div id="wps_forgot_password_alert_container"></div> 
	<h2><?php _e('Password renew', 'wpshop'); ?></h2>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_forgot_password_form_renew" >
		<input type="hidden" name="activation_key" value="{WPSHOP_ACTIVATION_KEY}" />
		<input type="hidden" name="user_login" value="{WPSHOP_USER_LOGIN}" />
		<div class="wps-form-group">
			<label for="wps_new_password"><?php _e('New password', 'wpshop');?></label>
			<div class="wps-form"><input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" /></div>
		</div>
		<div class="wps-form-group">
			<label for="wps_confirm_new_password"><?php _e('Confirm new password', 'wpshop');?></label>
			<div class="wps-form"><input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" /></div>
		</div>
		<div class="wps-form-group">
		<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator'); ?></div>
		<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
		</div>
		
		<input type="hidden" name="action" value="wps_forgot_password_renew" />
	</form>
	<div class="wps-form-group">
		<div class="wps-form-group"><button id="wps_send_forgot_password_renew" class="wps-bton wps-bton-prim"><?php _e('Renew your password', 'wpshop')?></button> <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" id="wps_renew_password_loader" class="wpshopHide" /></div>
	</div>
<?php
$tpl_element['wpshop']['default']['wps_forgot_password_form_renew'] = ob_get_contents();
ob_end_clean();