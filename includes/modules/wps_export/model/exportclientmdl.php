<?php

function checkuserlist(){
	global $wpdb;
	$query = $wpdb->prepare("SELECT DISTINCT (post_author) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
	$uservar = $wpdb->get_results($query);
	$usertab[0]['name'] = 'Prénom';
	$usertab[0]['lastname'] = 'Nom';
	$usertab[0]['mail'] = 'E-Mail';
	$usertab[0]['ID'] = 'ID Utilisateur';
	$count = 1;
	foreach ($uservar as $user){
		$user_id = $user->post_author;
		$tmparray = get_userdata($user_id);
		if ( !empty($tmparray->user_firstname))
			  $usertab[$count]['firstname'] = $tmparray->user_firstname;
		else
			  $usertab[$count]['firstname'] = 'Non Specifie';
		if ( !empty($tmparray->user_lastname))
			  $usertab[$count]['lastname'] = $tmparray->user_lastname;
		else
			  $usertab[$count]['lastname'] = 'Non Specifie';
		if ( !empty($tmparray->user_email))
			  $usertab[$count]['email'] = $tmparray->user_email;
		else
			  $usertab[$count]['email'] = 'Non Specifie';
	  	$usertab[$count]['ID'] = $tmparray->ID;
	  	$count++;
	  }
	exporttocsv($usertab, 'customer');
}

function checkalluserlist(){
	global $wpdb;
	$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->users} WHERE user_status = %d", "0");
	$uservar = $wpdb->get_results($query);
	$usertab[0]['name'] = 'Prénom';
	$usertab[0]['lastname'] = 'Nom';
	$usertab[0]['mail'] = 'E-Mail';
	$usertab[0]['ID'] = 'ID Utilisateur';
	$count = 1;
	foreach ($uservar as $user){
		$user_id = $user->ID;
		$tmparray = get_userdata($user_id);
		if ( !empty($tmparray->user_firstname))
			  $usertab[$count]['firstname'] = $tmparray->user_firstname;
		else
			  $usertab[$count]['firstname'] = 'Non Specifie';
		if ( !empty($tmparray->user_lastname))
			  $usertab[$count]['lastname'] = $tmparray->user_lastname;
		else
			  $usertab[$count]['lastname'] = 'Non Specifie';
		if ( !empty($tmparray->user_email))
			  $usertab[$count]['email'] = $tmparray->user_email;
		else
			  $usertab[$count]['email'] = 'Non Specifie';
	  	$usertab[$count]['ID'] = $tmparray->ID;
	  	$count++;
	  }
	exporttocsv($usertab, 'customer');
}

function exportorders(){
	global $wpdb;
	$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
	$ordervar = $wpdb->get_results($query);
	$queryproducts = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_product");
	$productsvar = $wpdb->get_results($queryproducts);
	$ordertab[0]['client'] = 'ID Client';
	$ordertab[0]['date'] = 'Date';
	$ordertab[0]['montantttc'] = 'Montant TTC';
	$ordertab[0]['montantht'] = 'Montant HT';
	$ordertab[0]['product'] = 'Produits';
	$ordertab[0]['ID'] = 'ID Commande';
	$ordertab[0]['pay'] = 'Etat du paiement';
	$ordertab[0]['paycurr'] = 'Paiement en';
	$ordertab[0]['paymet'] = 'Méthode';
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
		exporttocsv($ordertab, 'Orders');
}

function lasttimeexport(){
	
	global $wpdb;
	$count = 0;
	$tabres = $wpdb->get_results("SELECT MAX(exporttime) FROM wp_exportclient", ARRAY_A);
	$time = time();
	if (($time - 2592000) >= ($tabres[0]['MAX(exporttime)'])){
		echo ('<p style="text-align: center">');
		echo ('Aucune exportation depuis plus de 30 jours');
		echo ('</p>');
	}
}

function timeexport(){
	global $wpdb;
	$wpdb->insert('wp_exportclient', array('exporttime' => time()));
}

function exporttocsv($userlist, $filename){
	timeexport();
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

function bestuserslist(){
	global $wpdb;
	$query = $wpdb->prepare("SELECT DISTINCT (ID) FROM {$wpdb->posts} WHERE post_type = %s", "wpshop_shop_order");
	$ordervar = $wpdb->get_results($query);
	$count = 1;
	$ordertab[0]['client'] = 'ID Client';
	$ordertab[0]['date'] = 'Date';
	$ordertab[0]['ID'] = 'ID Commande';
	$ordertab[0]['montantttc'] = 'Montant TTC';
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
			exporttocsv($ordertab, 'Orders');
}

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
				$usertab[0]['client'] = 'ID Client';
				$usertab[0]['nom'] = 'Nom';
				$usertab[0]['prenom'] = 'Prenom';
				$usertab[0]['produit'] = 'Produits';
				$usertab[0]['amout'] = 'Montant commande';
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
	exporttocsv($usertab, 'Uncompleted_Cart');
}