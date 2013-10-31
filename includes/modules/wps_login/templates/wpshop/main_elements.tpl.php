<?php
$tpl_element = array();
/**
 * WPSHOP LOGIN FORM
 */
ob_start();
?>
	<?php _e('You don\'t have an account', 'wpshop'); ?> ? <a href="#" id="display_sign_up_form"><?php _e('Please enter your personnal informations', 'wpshop')?></a>
	<h2><?php _e('Connexion', 'wpshop'); ?></h2>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="wps_login_form">
		<input type="hidden" name="action" value="wps_login_request" />
		<div class="wps-form-group">
			<label for="wps_login_email"><?php _e('Email address', 'wpshop');?></label>
			<div class="wps-form"><input type="text" name="wps_login_user_login"  class="input" size="20" id="wps_login_email" /></div>
		</div>
		<div class="wps-form-group">
			<label for="wps_login_password"><?php _e('Password', 'wpshop');?></label>
			<div class="wps-form"><input type="password" name="wps_login_password"  class="input" size="20" id="wps_login_password" /></div>
		</div>
		<div class="wps-form-group">
			<input type="checkbox" name="wps_login_remember_me"  id="wps_login_remember_me" /><label for="wps_login_remember_me"><?php _e('Remember me', 'wpshop'); ?></label>
		</div>
		<div class="wps-form-group">
			<a href="#" id="forgot_password_interface_opener"><?php _e('Forgotten password', 'wpshop'); ?> ?</a>
		</div>
		<div class="wps-form-group">
			<input type="button" name="wps_login_request" class="wps-bton wps-bton-prim" value="<?php _e('Connexion', 'wpshop'); ?>" /> <img src="{WPSHOP_LOADING_ICON}" alt="<?php _e('Loading', 'wpshop'); ?>" class="wpshopHide" id="login_loader"/>
		</div>
	</form>
<?php
$tpl_element['wpshop']['default']['wps_login_form'] = ob_get_contents();
ob_end_clean();