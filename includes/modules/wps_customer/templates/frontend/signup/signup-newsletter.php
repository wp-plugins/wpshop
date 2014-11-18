<div class="wps-form-group">
	<input id="newsletters_site" type="checkbox" name="newsletters_site" <?php echo ( (!empty($user_preferences['newsletters_site']) && $user_preferences['newsletters_site']== 1 ) ? ' checked="checked"' : null); ?>>
	<label for="newsletters_site"><?php _e('I want to receive promotional information from the site','wpshop'); ?></label>
</div>
<div class="wps-form-group">
	<input id="newsletters_site_partners" type="checkbox" name="newsletters_site_partners" <?php echo ((!empty($user_preferences['newsletters_site_partners']) && $user_preferences['newsletters_site_partners']==1 ) ? ' checked="checked"' : null); ?>/>
	<label for="newsletters_site_partners"><?php _e('I want to receive promotional information from partner companies','wpshop'); ?></label>
</div>
