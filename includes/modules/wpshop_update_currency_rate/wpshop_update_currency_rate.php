<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}
if ( !class_exists("wpshop_update_currency_rate") ) {
	class wpshop_update_currency_rate {
		
		function __construct() {
			add_action( 'update_currency_rate', 'update_currencies_rate' );
		}
		
		function wpshop_update_currency_rate_cron() {
			if ( ! wp_next_scheduled('update_currency_rate') ) {
				wp_schedule_event( time(), 'daily', 'update_currency_rate' );
			}
			
		}
		
		function update_currencies_rate () {
			global $wpdb;
			$currency_group = get_option( 'wpshop_shop_currency_group');
			$default_currency_id = get_option( 'wpshop_shop_default_currency' );
			$default_currency = '';
			$currency_query =  $wpdb->prepare('SELECT code_iso FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id = %d',  $default_currency_id);
			if ( !empty($currency_query) ) {
				$default_currency = $wpdb->get_var($currency_query);
			}
			$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE group_id = %d',  $currency_group);
			$currencies = $wpdb->get_results($query);
			if ( !empty($currencies) )  {
				foreach( $currencies as $currency) {
					if ( $currency->id != $default_currency_id ) {
						$currency_rate = self::convertCurrency($default_currency, $currency->code_iso, 1);
						$wpdb->update( WPSHOP_DBT_ATTRIBUTE_UNIT, array('change_rate' => $currency_rate), array( 'id' => $currency->id) );
					}
				}
			}
		}
		
		
 		function convertCurrency( $sCurSource, $sCurDest, $fAmount ){
 			$sURLToYahoo = 'http://quote.yahoo.com/d/quotes.csv?s='.$sCurSource.$sCurDest.'=X&f=l1&e=.csv';
			$sResult = file_get_contents( $sURLToYahoo );
		 
 			return (float)$sResult*$fAmount;
 		}
		
	}
}
if (class_exists("wpshop_update_currency_rate"))
{
	$inst_wpshop_shipping_partners = new wpshop_update_currency_rate();
}