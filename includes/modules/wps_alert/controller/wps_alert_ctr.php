<?php

class wps_alert_ctr{
	function __construct(){
	$this->check_last_order();
	$this->get_daily_average();
	}
	
	function check_last_order(){
		$last_order_date = get_post_date();
		$output .= '';
		$output .= '<table id="wps_alert_summary">';
		if (!empty($last_order_date[0])){
			$last_order_date = strtotime($last_order_date[0]->post_date);
			if (get_option('last_order_opt') == false){
				$timestamp = time();
				update_option('last_order_opt', $timestamp);
			}
			else {
				$timestamp = time();
				$last_check_date = get_option('last_order_opt');
			}
			if ($timestamp - 86400 * ($this->get_daily_average())> $last_order_date){
				$datesaved = date('l jS \of F Y', $last_order_date);
				$datesaved = __($datesaved, 'wpshop');
				$output .= '<td> <center> <font color=red> NO ORDERS SINCE ';
				$output .= $datesaved;
				$output .= '<br /> Check your orders to know if there is a problem, and contact us if the problem persists <br /> Daily Orders : ~';
				$output .= $this->get_daily_average();
				$output .= '</center> </font> </td>';
				if ($timestamp - 86400 > $last_check_date){
					update_option('last_order_opt', $timestamp);
				}
			}
			else{
				$datesaved = date('l jS \of F Y', $last_order_date);
				$datesaved = __($datesaved, 'wpshop');
				$output .= '<center>';
				$output .= 'No alerts, have a good day ! <br /> Last order Date: ';
				$output .= $datesaved;
				$output .= '</center>';
 			}
			$output .= '</table>';
		}
		else{
			$output .= 'Aucune commande dans la base de donnees';
		}
		return ($output);
	}
	
	function get_daily_average(){
		$all_dates = get_post_date();
		$create_date = get_creation_date();
		$create_date = strtotime($create_date[0]->post_date);
		$count = 0;
		$res = 0;
		foreach ($all_dates as $order_date){
			$res += strtotime($order_date->post_date);
			$count++;
		}
		$res = $res / (3600 * 24);
		$create_date = $create_date / (3600 * 24);
		$res = ($res - $create_date) / (3600 *24);
		$res = ceil($res);
		return ($res);
	}
}