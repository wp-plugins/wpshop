<?php
/**
 * MESSAGE SAVE MONEY
*/
ob_start();
?>
<span class="wpshop_save_money_message wps-label-vert"><?php _e('Saving', 'wpshop'); ?> {WPSHOP_SAVING_MONEY_AMOUNT} {WPSHOP_CURRENCY}</span>
<?php 
$tpl_element['wpshop']['default']['wpshop_marketing_message_save_money'] = ob_get_contents();
ob_end_clean();