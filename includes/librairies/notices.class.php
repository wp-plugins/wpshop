<?php
/**
* Define the different tools for the entire plugin
* 
*	Define the different tools for the entire plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different tools for the entire plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_notices {

	/** Notice the user to install the plugin */
	function install_admin_notice() {
		echo '<div class="updated"><p>';
		echo sprintf(__('You have to install the plugin and choose your settings to start selling. Go to <a href="%s">shop main page</a>.','wpshop'), admin_url('admin.php?page='.WPSHOP_URL_SLUG_DASHBOARD));
		echo '</p></div>';
	}
	/** Notice the user to choose a payment method */
	function paymentMethod_admin_notice() {
		/* Check that the user has already choose a payment method */
		$paymentMethod = get_option('wpshop_paymentMethod', array());
		if(empty($paymentMethod['paypal']) && empty($paymentMethod['checks'])) {
			echo '<div class="updated"><p>';
			echo sprintf(__('You haven\'t choose any payment method, please choose in the <a href="%s">settings page</a>.','wpshop'), admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION));
			echo '</p></div>';
		}
	}
	/** Notice the user to choose a payment method */
	function missing_emails_admin_notice() {
		/* Check that the user has already choose a payment method */
		$emails = get_option('wpshop_emails', array());
		if(empty($emails)) {
			echo '<div class="updated"><p>';
			echo sprintf(__('You haven\'t type any contact email address, please type one in the <a href="%s">settings page</a>.','wpshop'), admin_url('options-general.php?page='.WPSHOP_URL_SLUG_OPTION));
			echo '</p></div>';
		}
	}
}