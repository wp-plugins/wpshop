<div class="wrap">
<table id="wps_alert_summary">
<h2><?php _e('WPShop Alert settings menu', 'wpsalert_i18n'); ?></h2>

<form method="post" action="options.php">
    <?php settings_fields( 'baw-settings-group' ); ?>
    <?php do_settings_sections( 'baw-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e('Duration with no orders before alerting yourself there is a problem (Hours)', 'wpsalert_i18n'); ?> </th>
        <td><input type="number" name="wpshop_alert_choosen_interval" value="<?php echo esc_attr( get_option('wpshop_alert_choosen_interval') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><?php _e('Time interval between e-mail alerts sending (Hours)', 'wpsalert_i18n'); ?></th>
        <td><input type="number" name="wpshop_alert_interval" value="<?php echo esc_attr( get_option('wpshop_alert_interval') ); ?>" /></td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>
</table>
</div>