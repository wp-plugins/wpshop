<?php
/**
 * Customer Account Dashboard
 */
ob_start();
?>
<div id="secondary">
	<aside>
		<div id="wps_customer_account_dashboard_nav">
			<ul>
				<li id="account_informations"><a href="#"><?php _e('Account informations', 'wpshop'); ?></a></li>
				<li id="account_address_book"><a href="#"><?php _e('Address book', 'wpshop'); ?></a></li>
				<li id="account_orders"><a href="#"><?php _e('My orders', 'wpshop'); ?></a></li>
			</ul>
		</div>
	</aside>
</div>
<div id="wps_customer_account_dashboard_content"></div>


<?php 
$tpl_element['wpshop']['default']['wpshop_customer_account_dashboard'] = ob_get_contents();
ob_end_clean();




/**
 * Customer Account Orders List
 */

 ob_start();
 ?>
 <h2><?php _e('My orders', 'wpshop'); ?></h2>
 {WPSHOP_CUSTOMER_ACCOUNT_ORDERS_LIST}
 <?php 
 $tpl_element['wpshop']['default']['wpshop_customer_account_orders_section'] = ob_get_contents();
 ob_end_clean();
 
 
 
 
 
 
 /**
  * Customer Account Order element 
  */
  
 ob_start();
 ?>
  <div class="order">
	  <div>
		  <?php  _e('Order number','wpshop'); ?> : <strong>{WPSHOP_CUSTOMER_ACCOUNT_ORDER_KEY}</strong><br />
		  <?php  _e('Date','wpshop'); ?> : <strong>{WPSHOP_CUSTOMER_ACCOUNT_ORDER_DATE}</strong><br />
		  <?php  _e('Total ATI','wpshop'); ?> : <strong>{WPSHOP_CUSTOMER_ACCOUNT_TOTAL_ATI} {WPSHOP_CURRENCY}</strong><br />
		  <?php  _e('Status','wpshop'); ?> : <strong><span class="status {WPSHOP_CUSTOMER_ACCOUNT_ORDER_STATUS}">{WPSHOP_CUSTOMER_ACCOUNT_ORDER_STATUS}</span></strong><br />
		  <a href="{WPSHOP_CUSTOMER_ACCOUNT_ORDER_LINK}" title="<?php _e('More info about this order...', 'wpshop'); ?>"><?php _e('More info about this order...', 'wpshop'); ?></a>
	  </div>
  </div>
  
  <?php 
  $tpl_element['wpshop']['default']['wpshop_customer_account_order'] = ob_get_contents();
  ob_end_clean();