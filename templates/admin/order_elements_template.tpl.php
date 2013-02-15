<?php

/**	Order actions box	*/
ob_start();
?>
<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="<?php echo wp_create_nonce("wpshop_change_order_state"); ?>" />
<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="<?php echo wp_create_nonce("wpshop_dialog_inform_shipping_number"); ?>" />
<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="<?php echo wp_create_nonce("wpshop_validate_payment_method"); ?>" />
<ul class="wpshop_orders_actions_list" >
	{WPSHOP_ADMIN_ORDER_ACTIONS_LIST}
	<li class="wpshop_orders_actions_main" >
		{WPSHOP_ADMIN_ORDER_DELETE_ORDER}
		<input type="submit" value="<?php _e('Save order', 'wpshop'); ?>" name="save" class="button-primary wpshop_order_save_button" id="wpshop_order_save_button" />
		<img id="ajax-loading-wphop-order" class="alignright wpshopHide ajax-loading-wphop-order" alt="" src="<?php echo admin_url('images/wpspin_light.gif'); ?>">
	</li>
</ul>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		if(jQuery("#title").val() == ""){
			jQuery("#title").val((wpshopConvertAccentTojs("<?php echo sprintf(__('Order - %s', 'wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)); ?>")));
		}

		jQuery("#wpshop_order_save_button").live('click', function(){
			jQuery('#ajax-loading-wphop-order').show();
			display_message_for_received_payment( true );
		});

		/**	Add an action on order save button	*/
		jQuery("#wpshop_order_arrived_payment_amount_add_button").live("click", function(){
			display_message_for_received_payment( false );
		});
	});

	/**
	 * Output a message to the user if he received a new payment
	 */
	function display_message_for_received_payment( from_general_button ) {

		var form_is_complete = true;
		jQuery(".wpshop_admin_order_new_payment_received_input").each(function(){
			if( jQuery(this).val() == "" ){
				form_is_complete = false;
			}
		});
		if( !form_is_complete && !from_general_button ){
			jQuery("#ajax-loading-wphop-order").hide();
			return false;
		}

		if ( form_is_complete ) {
			/**	Get the current due amount to display a message to current admin	*/
			var current_due_amount = jQuery("#wpshop_admin_order_due_amount").val();
			var received_amount = jQuery("#wpshop_admin_order_payment_received_amount").val();

			var message_to_display = "<?php _e('Adding this payment will result of the billing of this order.\r\nAre you sure you want to continue?', 'wpshop'); ?>";
			if (current_due_amount == received_amount) {
				message_to_display = "<?php _e('It seems you received complete payment for this order.\rThis', 'wpshop'); ?>";
			}
		}
	}
</script><?php
$tpl_element['wpshop_admin_order_action_box'] = ob_get_contents();
ob_end_clean();

ob_start();
?><a class="submitdelete deletion" href="{WPSHOP_ADMIN_ORDER_DELETE_LINK}">{WPSHOP_ADMIN_ORDER_DELETE_TEXT}</a><?php
$tpl_element['wpshop_admin_order_action_del_button'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<p><?php _e('Sended', 'wpshop'); ?> : <br/>
{WPSHOP_UPDATE_ORDER_MESSAGE_DATE}</p>
<p><?php _e('Message', 'wpshop'); ?> : <br/>{WPSHOP_UPDATE_ORDER_MESSAGE}</p>
<hr/>
<?php
$tpl_element['wpshop_admin_order_customer_notification_item'] = ob_get_contents();
ob_end_clean();


ob_start();
?>
<ul class="wpshop_order_payment_main_container" >
	{WPSHOP_ADMIN_ORDER_CUSTOMER_CHOICE}
	<li class="wpshop_order_payment_total_amount" >
		<?php _e('Order total amount', 'wpshop'); ?>
		<span class="alignright" >{WPSHOP_ORDER_TOTAL_AMOUNT_TTC} {WPSHOP_CURRENCY}</span>
	</li>
	{WPSHOP_ADMIN_ORDER_PAYMENT_LIST}
	{WPSHOP_ADMIN_ORDER_PAYMENT_REST}
</ul><?php
$tpl_element['wpshop_admin_order_payment'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_admin_order_payment_box_customer_payment_choice{WPSHOP_ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_CLASSES}" ><?php _e('Choosen payment method'); ?><span class="alignright">{WPSHOP_ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_METHOD}</span></li><?php
$tpl_element['wpshop_admin_order_customer_choices'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_admin_order_payment_box_payment_received{WPSHOP_ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES}" ><span class="ui-icon wpshop_order_payment_received_icon" ></span>{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_DATE}<span class="alignright" >{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_METHOD}: {WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_PAYMENT_REFERENCE}</span><div class="wpshop_admin_order_received_payment_compte_info" >{WPSHOP_PAYMENT_INVOICE_DOWNLOAD_LINKS}<span class="wpshop_order_received_payment_amount" >{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_RECEIVED_AMOUNT}</span></div><div class="wpshop_cls" ></div></li><?php
$tpl_element['wpshop_admin_order_payment_received'] = ob_get_contents();
ob_end_clean();

ob_start();
?><span class="wpshop_order_received_payment_invoice_ref" ><a href="{WPSHOP_ADMIN_ORDER_INVOICE_DOWNLOAD_LINK}" target="wpshop_invoice_download" >{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_INVOICE_REF}</a> | <a href="{WPSHOP_ADMIN_ORDER_INVOICE_DOWNLOAD_LINK}&mode=pdf" target="wpshop_invoice_download" ><?php _e('pdf', 'wpshop'); ?></a></span><?php
$tpl_element['wpshop_admin_order_payment_received_invoice_download_links'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_admin_order_payment_box_payment_rest{WPSHOP_ADMIN_ORDER_PAYMENT_REST_CLASSES}" ><span class="ui-icon wpshop_order_payment_rest_icon" ></span><?php _e('Due amount for this order', 'wpshop'); ?><span class="wpshop_order_receveid_payment_amount alignright" >{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_DUE_AMOUNT} {WPSHOP_CURRENCY}</span></li><?php
$tpl_element['wpshop_admin_order_payment_rest'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_admin_order_payment_box_waiting_payment{WPSHOP_ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES}" >
	<div class="wpshop_admin_order_payment_box_arriving_payment_title" ><?php _e('New payment received', 'wpshop'); ?></div>
	<div class="wpshop_cls wpshop_admin_order_arrived_payment_method_choice_container" >
		<div>
			<label for="wpshop_admin_order_paymet_method_chooser" ><?php _e('Payment method', 'wpshop'); ?></label>
			{WPSHOP_ADMIN_ORDER_REVEICED_PAYMENT_METHOD_CHOOSER}
		</div>
		<div>
			<label for="wpshop_admin_order_payment_reference" ><?php _e('Payment reference', 'wpshop'); ?></label>
			<input type="text" id="wpshop_admin_order_payment_reference" name="wpshop_admin_order_payment_received[payment_reference]" value="" class="wpshop_admin_order_new_payment_received_input wpshop_admin_order_arrived_payment_method_choice_transaction_identifier" />
		</div>
	</div>
	<div class="wpshop_cls wpshop_admin_order_arrived_payment_date_and_amount_container" >
		<div>
			<label for="wpshop_admin_order_payment_received_date" ><?php _e('Date', 'wpshop'); ?></label>
			<input type="text" value="" id="wpshop_admin_order_payment_received_date" name="wpshop_admin_order_payment_received[date]" class="wpshop_admin_order_new_payment_received_input wpshop_datetime wpshop_admin_order_arrived_payment_date" />
		</div>
		<div>
			<label for="wpshop_admin_order_payment_received_amount" ><?php _e('Amount', 'wpshop'); ?></label>
			<input class="wpshop_admin_order_new_payment_received_input wpshop_admin_order_arrived_payment_amount" id="wpshop_admin_order_payment_received_amount" name="wpshop_admin_order_payment_received[received_amount]" type="text" value="{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_UNSTYLED_WAITED_AMOUNT}" />{WPSHOP_CURRENCY}
		</div>
	</div>
	<input type="hidden" value="{WPSHOP_ADMIN_ORDER_RECEIVED_PAYMENT_DUE_AMOUNT}" id="wpshop_admin_order_due_amount" />
	<button class="wpshop_cls alignright button-secondary wpshop_order_arrived_payment_amount_button" id="wpshop_order_arrived_payment_amount_add_button" ><?php _e('Add', 'wpshop'); ?></button>
	<div class="wpshop_cls" >
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker();
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "dateFormat", "yy-mm-dd");
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeMonth", true);
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "changeYear", true);
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "yearRange", "-90:+10");
		jQuery(".wpshop_admin_order_arrived_payment_date").datepicker("option", "navigationAsDateFormat", true);
		jQuery(".wpshop_admin_order_arrived_payment_date").val("<?php echo substr(current_time('mysql', 0), 0, 10); ?>");
	});
</script></div>
</li><?php
$tpl_element['wpshop_admin_order_waiting_payment'] = ob_get_contents();
ob_end_clean();

ob_start();
?><li class="wpshop_admin_order_payment_box_waiting_payment{WPSHOP_ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES}" >
	<div class="wpshop_admin_order_payment_box_arriving_payment_title" ><?php _e('New payment received', 'wpshop'); ?></div>
	<?php echo sprintf( __("You don't have any payment method set in your shop. You won't be able to add a new payment until you configure %sthis options%s", 'wpshop') , '<a target="_blank" href="' . admin_url('options-general.php?page=wpshop_option&settings-updated=true#wpshop_payments_option') . '" >', '</a>'); ?>
</li><?php
$tpl_element['wpshop_admin_order_waiting_payment_no_method_set'] = ob_get_contents();
ob_end_clean();
