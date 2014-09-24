<?php

/**
 * Databases functions of Export module for WPShop
 * @package File
 */
 
 /**
  * Class for databases function
  * @package Class
  */
class exportclientmdl{
	/** 
	 * Create a List of all the clients who already ordered in your wpshop database 
	 */
	function checkuserlist(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT (post_author) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
		$uservar = $wpdb->get_results($query);
				/** Create the header **/
		$usertab[0]['name'] = __('First Name', 'wpsexport_i18n');
		$usertab[0]['lastname'] = __('Last Name', 'wpsexport_i18n');
		$usertab[0]['mail'] = __('E-Mail', 'wpsexport_i18n');
		$usertab[0]['ID'] = __('User ID', 'wpsexport_i18n');
		$count = 1;
		foreach ($uservar as $user){
			$user_id = $user->post_author;
			$tmparray = get_userdata($user_id);
			if ( !empty($tmparray->user_firstname))
				  $usertab[$count]['firstname'] = $tmparray->user_firstname;
			else
				  $usertab[$count]['firstname'] = __('Unknown', 'wpsexport_i18n');
			if ( !empty($tmparray->user_lastname))
				  $usertab[$count]['lastname'] = $tmparray->user_lastname;
			else
				  $usertab[$count]['lastname'] = __('Unknown', 'wpsexport_i18n');
			if ( !empty($tmparray->user_email))
				  $usertab[$count]['email'] = $tmparray->user_email;
			else
				  $usertab[$count]['email'] = __('Unknown', 'wpsexport_i18n');
			$usertab[$count]['ID'] = $tmparray->ID;
			$count++;
		  }
		$this->exporttocsv($usertab, 'customer');
	}

	/** 
	 * Create a List of all the clients in your wpshop database 
	 */
	function checkalluserlist(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->users} WHERE user_status = %d", "0");
		$uservar = $wpdb->get_results($query);
				/** Create the header **/
		$usertab[0]['name'] = __('First Name', 'wpsexport_i18n');
		$usertab[0]['lastname'] = __('Last Name', 'wpsexport_i18n');
		$usertab[0]['mail'] = __('E-Mail', 'wpsexport_i18n');
		$usertab[0]['ID'] = __('User ID', 'wpsexport_i18n');
		$count = 1;
		foreach ($uservar as $user){
			$user_id = $user->ID;
			$tmparray = get_userdata($user_id);
			if ( !empty($tmparray->user_firstname))
				  $usertab[$count]['firstname'] = $tmparray->user_firstname;
			else
				  $usertab[$count]['firstname'] = __('Unknown', 'wpsexport_i18n');
			if ( !empty($tmparray->user_lastname))
				  $usertab[$count]['lastname'] = $tmparray->user_lastname;
			else
				  $usertab[$count]['lastname'] = __('Unknown', 'wpsexport_i18n');
			if ( !empty($tmparray->user_email))
				  $usertab[$count]['email'] = $tmparray->user_email;
			else
				  $usertab[$count]['email'] = __('Unknown', 'wpsexport_i18n');
			$usertab[$count]['ID'] = $tmparray->ID;
			$count++;
		  }
		$this->exporttocsv($usertab, 'customer');
	}
	
	/**
	 * Get all the orders in your database 
	 */
	function exportorders(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
		$ordervar = $wpdb->get_results($query);
		$queryproducts = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_product");
		$productsvar = $wpdb->get_results($queryproducts);
		/** Create the header **/
		$ordertab[0]['client'] = __('Client ID', 'wpsexport_i18n');
		$ordertab[0]['date'] = __('Date', 'wpsexport_i18n');
		$ordertab[0]['montantttc'] = __('including tax and VAT', 'wpsexport_i18n');
		$ordertab[0]['montantht'] = __('not including tax and VAT', 'wpsexport_i18n');
		$ordertab[0]['product'] = __('Products', 'wpsexport_i18n');
		$ordertab[0]['ID'] = __('Order ID', 'wpsexport_i18n');
		$ordertab[0]['pay'] = __('Status', 'wpsexport_i18n');
		$ordertab[0]['paycurr'] = __('Paiement en', 'wpsexport_i18n');
		$ordertab[0]['paymet'] = __('Method', 'wpsexport_i18n');
		$count = 1;
			foreach ($ordervar as $orders){
				$order_ID = $orders->ID;
				$orderarray = get_post_meta($order_ID);
				$string = unserialize($orderarray[_order_postmeta][0]);
				$ordertab[$count]['client'] = $string['customer_id'];
				$ordertab[$count]['date'] = $string['order_date'];
				$ordertab[$count]['montantttc'] = $string['order_total_ttc'];
				$ordertab[$count]['montantht'] = $string['order_total_ht'];
				foreach ($productsvar as $product){
					if (!empty($string['order_items'][$product->ID])){
						$ordertab[$count]['product'] = $ordertab[$count]['product']. $string['order_items'][$product->ID]['item_name']. ' / ';
					}
				}
				$ordertab[$count]['ID'] = $string['order_key'];
				$ordertab[$count]['pay'] = $string['order_status'];
				$ordertab[$count]['paycurr'] = $string['order_currency'];
				$ordertab[$count]['paymet'] = $string['order_payment']['received'][0]['method'];
				$count++;
			}
			$this->exporttocsv($ordertab, 'Orders');
	}

	/** Update time of the export **/
	function timeexport(){
		global $wpdb;
		$wpdb->insert('wp_exportclient', array('exporttime' => time()));
	}

	/** 
	* Create and download your CSV file 
	* @param string $userlist The type of list
	* @param string $filename The file name
	*/
	function exporttocsv($userlist, $filename){
		$this->timeexport();
		$multifile = 0;
		$count = 0;
		$filename = $filename.time();
		$fp = fopen('list_of_' .$filename. '.csv', 'w');
		$exportfile = 'list_of_' .$filename. '.csv';
		foreach($userlist as $fields){
				fputcsv($fp, $fields, ';');
		}
		fclose($fp);
		header("Content-type: application/force-download");
		header("Content-Disposition: attachment; filename=".$exportfile);
		readfile($exportfile);
		unlink($exportfile);
		exit();
	}

	/** 
	 * Get users who ordered more than a defined cost 
	 */
	
	function bestuserslist(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
		$ordervar = $wpdb->get_results($query);
		$count = 1;
		$ordertab[0]['client'] = __('Client ID', 'wpsexport_i18n');
		$ordertab[0]['date'] = __('Date', 'wpsexport_i18n');
		$ordertab[0]['ID'] = __('Order ID', 'wpsexport_i18n');
		$ordertab[0]['montantttc'] = __('including tax and VAT', 'wpsexport_i18n');
		foreach ($ordervar as $orders){
				$order_ID = $orders->ID;
				$orderarray = get_post_meta($order_ID);
				$string = unserialize($orderarray[_order_postmeta][0]);
				if ($string['order_total_ttc'] >= $_GET['maxmoney']){
				$ordertab[$count]['client'] = $string['customer_id'];
				$ordertab[$count]['date'] = $string['order_date'];
				$ordertab[$count]['ID'] = $string['order_key'];
				$ordertab[$count]['montantttc'] = $string['order_total_ttc'];
				}
				$count++;
			}
			if (empty($ordertab[1])){
				echo '<script language="Javascript">
				alert ("AUCUNE COMMANDE NE CORRESPOND A VOS CRITERES" )
				</script>';
			}
			else
				$this->exporttocsv($ordertab, 'Orders');
	}

	/** 
	 *A list of non completed orders 
	 */
	function ordernotcomplete(){
		global $wpdb;
		$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->users} WHERE user_status = %s", "");
		$users = $wpdb->get_results($query);
		$queryproducts = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_product");
		$productsvar = $wpdb->get_results($queryproducts);
		$count = 1;
			foreach ($users as $user){
			$user_ID = $user->ID;
			$userinfo = get_userdata($user_ID);
			$userarray = get_user_meta($user_ID);
				if (isset($userarray['_wpshop_persistent_cart'])){
					$usertab[0]['client'] = __('Client ID', 'wpsexport_i18n');
					$usertab[0]['nom'] = __('Last Name', 'wpsexport_i18n');
					$usertab[0]['prenom'] = __('First Name', 'wpsexport_i18n');
					$usertab[0]['produit'] = __('Product', 'wpsexport_i18n');
					$usertab[0]['amout'] = __('Amount', 'wpsexport_i18n');
					$string = unserialize($userarray[_wpshop_persistent_cart][0]);
					$usertab[$count]['client'] = $user_ID;
					$usertab[$count]['nom'] = $userinfo->user_lastname;
					$usertab[$count]['prenom'] = $userinfo->user_firstname;
					foreach ($productsvar as $product){
						if (!empty($string[cart]['order_items'][$product->ID])){
							$usertab[$count]['product'] = $usertab[$count]['product']. $string[cart]['order_items'][$product->ID]['item_name']. ' / ';
						}
					}
					$usertab[$count]['amount'] = $string[cart][order_grand_total];
				}
				$count++;
			}
		$this->exporttocsv($usertab, 'Uncompleted_Cart');
	}
}