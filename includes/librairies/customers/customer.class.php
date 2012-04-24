<?php

class wpshop_customer{

	/**
	* Constructor of the class
	*/
	function __construct(){
	
	}

	/**
	* Return a list of customer
	*/
	function custom_user_list($selected_user = array()){
		global $wpdb;

		/*	Start the table definition	*/
		$tableId = 'wpshop_customer_list';
		$tableTitles = array();
		$tableTitles[] = '';
		$tableTitles[] = __('Id', 'wpshop');
		$tableTitles[] = __('Lastname', 'wpshop');
		$tableTitles[] = __('Firstname', 'wpshop');
		$tableTitles[] = __('Subscription date', 'wpshop');
		$tableTitles[] = __('Billing address', 'wpshop');
		$tableTitles[] = __('Shipping address', 'wpshop');
		$tableClasses = array();
		$tableClasses[] = 'wpshop_customer_selector_column';
		$tableClasses[] = 'wpshop_customer_identifier_column';
		$tableClasses[] = 'wpshop_customer_lastname_column';
		$tableClasses[] = 'wpshop_customer_firstname_column';
		$tableClasses[] = 'wpshop_customer_subscription_date_column';
		$tableClasses[] = 'wpshop_customer_billing_address_column';
		$tableClasses[] = 'wpshop_customer_shipping_address_column';

		/*	Get user list	*/
		$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->users);
		$users = $wpdb->get_results($query);
		if(!empty($users)){
			$current_line_index = 0;
			foreach($users as $user){
				$tableRowsId[$current_line_index] = 'customer_' . $user->ID;

				$user_info = get_userdata($user->ID);
				$user_billing_info = get_user_meta($user->ID, 'billing_info', true);
				$user_shipping_info = get_user_meta($user->ID, 'shipping_info', true);

				unset($tableRowValue);
				$tableRowValue[] = array('class' => 'wpshop_customer_selector_cell', 'value' => '<span class="wpshop_customer_selector_icon ' . (!empty($selected_user) && (in_array($user->ID, $selected_user)) ? 'wpshop_user_selected ' : 'wpshop_user_not_selected ') . 'pointer" >&nbsp;</span>');
				$tableRowValue[] = array('class' => 'wpshop_customer_identifier_cell', 'value' => WPSHOP_IDENTIFIER_CUSTOMER . $user->ID);
				$tableRowValue[] = array('class' => 'wpshop_customer_lastname_cell', 'value' => (!empty($user_info->user_lastname) ? $user_info->user_lastname : $user_info->user_login));
				$tableRowValue[] = array('class' => 'wpshop_customer_firstname_cell', 'value' => $user_info->user_firstname);
				$tableRowValue[] = array('class' => 'wpshop_customer_subscription_date_cell', 'value' => mysql2date('d F Y \a\t H:i', $user_info->user_registered, true));
				$tableRowValue[] = array('class' => 'wpshop_customer_billing_address_cell', 'value' => (!empty($user_billing_info) ? wpshop_account::display_customer_address('billing', $user_billing_info) : __('User has no billing address set', 'wpshop')));
				$tableRowValue[] = array('class' => 'wpshop_customer_shipping_address_cell', 'value' => (!empty($user_billing_info) ? wpshop_account::display_customer_address('shipping', $user_shipping_info) : __('User has no shipping address set', 'wpshop')));
				$tableRows[] = $tableRowValue;

				$current_line_index++;
			}
		}
		else{
			unset($tableRowValue);
			$tableRowValue[] = array('class' => 'wpshop_customer_selector_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_customer_identifier_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_customer_lastname_cell', 'value' => __('No element to ouput here', 'wpshop'));
			$tableRowValue[] = array('class' => 'wpshop_customer_firstname_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_customer_subscription_date_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_customer_billing_address_cell', 'value' => '');
			$tableRowValue[] = array('class' => 'wpshop_customer_shipping_address_cell', 'value' => '');
			$tableRows[] = $tableRowValue;
		}

		return wpshop_display::getTable($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, '', false) . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $tableId . '").dataTable({
			"bPaginate": false,
			"bLengthChange": false,
			"bFilter": false,
			"bSort": false,
			"bInfo": false
		});
	});
</script>';
	}

}

?>