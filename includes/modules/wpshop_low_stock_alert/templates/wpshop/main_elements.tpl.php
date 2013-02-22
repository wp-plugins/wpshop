<?php

/**
 * ALERT NOT BASED ON REAL STOCKS
 */
ob_start();
?>
<img src="{WPSHOP_MEDIAS_ICON_URL}error.gif" alt="" /> <?php _e('Stock soon exhausted', 'wpshop_low_stock_alert'); ?>
<?php 
$tpl_element['wpshop']['default']['wpshop_low_stock_alert_not_based_on_real_stock'] = ob_get_contents();
ob_end_clean();


/**
 * ALERT BASED ON REAL STOCKS
 */
ob_start();
?>
<img src="{WPSHOP_MEDIAS_ICON_URL}error.gif" alt="" /> <?php _e('Stock soon exhausted', 'wpshop_low_stock_alert'); ?>, {WPSHOP_REST_PRODUCT_QTY}
<?php 
$tpl_element['wpshop']['default']['wpshop_low_stock_alert_based_on_real_stock'] = ob_get_contents();
ob_end_clean();
?>
