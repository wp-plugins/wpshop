<?php
/**
 * Plugin Name: WPShop Statistics
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WPShop Statistics
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPShop Statistics bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
 if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}

if ( !class_exists("wps_statistics") ) {
	class wps_statistics {
		function __construct() {
			add_action('admin_menu', array(&$this, 'register_stats_menu'), 250);
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_action( 'save_post', array( &$this, 'wps_statistics_save_customer_infos') );
			/** Add admin script **/
			add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_css_files' ) );

			
			/** AJAX ACTIONS ***/
			add_action('wp_ajax_wps_reload_statistics', array( &$this, 'wps_reload_statistics') );
			add_action('wp_ajax_wps_hourly_order_day', array( &$this, 'wps_hourly_order_day') );
			
			/** METABOX ACTIONS ***/
			add_action('add_meta_boxes', array( &$this, 'add_customer_meta_box'), 1 );
			
			
		}
		/**
		* Add Javascript files
		*/
		function add_scripts() {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script( 'wps_statistics_js_chart', WPSHOP_JS_URL.'Chart.js' );
			wp_enqueue_script( 'wps_statistics_js', plugins_url('templates/backend/js/wps_statistics.js', __FILE__) );
			wp_enqueue_script( 'wps_hourlyorders', plugins_url('templates/backend/js/hourlyorders.js', __FILE__) );
		}

		/**
		* Add CSS files
		*/
		function add_css_files() {
			wp_register_style( 'wps_statistics_css', plugins_url('templates/backend/css/wps_statistics.css', __FILE__) );
			wp_enqueue_style( 'wps_statistics_css' );
		}
		
		function add_customer_meta_box() {
			global $post;
			add_meta_box( 'wps_statistics_customer', __( 'Statistics', 'wpshop' ), array( &$this, 'wps_statistics_meta_box_content' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
		}
		
		function wps_statistics_meta_box_content() {
			global $post;
			$user_meta = '';
			if ( !empty($post) && !empty($post->post_author) ) {
				$user_meta = get_user_meta( $post->post_author, 'wps_statistics_exclude_customer', true );
			}
			$output = '<input type="checkbox" name="wps_statistics_exclude_customer" id="wps_statistics_exclude_customer" ' .( (!empty($user_meta) ) ? 'checked="checked"' : '' ). '/> <label for="wps_statistics_exclude_customer">' .__('Exclude this customer from WPShop Statistics', 'wpshop'). '</label>';
			echo $output;
		}
		
		function wps_statistics_save_customer_infos() {
			if ( !empty($_POST['action']) && $_POST['action'] != 'autosave' && !empty($_POST['post_type']) && $_POST['post_type'] == 'wpshop_customers') {
				$customer_def = get_post( $_POST['post_ID'] );
				update_user_meta( $customer_def->post_author, 'wps_statistics_exclude_customer', $_POST['wps_statistics_exclude_customer'] );
			}
		}
		
		
		/** Register Menu **/
		function register_stats_menu() {
			
			add_submenu_page( WPSHOP_URL_SLUG_DASHBOARD, __('Statistics', 'wpshop' ), __('Statistics', 'wpshop'), 'wpshop_view_statistics', 'wpshop_statistics', array('wps_statistics', 'wps_display_statistics'));
// 			add_menu_page(__('Shop Statistics', 'wpshop'), __('Shop Statistics', 'wpshop'), 'manage_options', 'wps_statistics',  array($this, 'wps_display_statistics'), '', 32);
		}
		
		/** Load the module template **/
		function custom_template_load( $templates ) {
			include('templates/backend/main_elements.tpl.php');
		
			foreach ( $tpl_element as $template_part => $template_part_content) {
				foreach ( $template_part_content as $template_type => $template_type_content) {
					foreach ( $template_type_content as $template_key => $template) {
						$templates[$template_part][$template_type][$template_key] = $template;
					}
				}
			}
			unset($tpl_element);

			return $templates;
		}
		
		
		function get_statistics_interface( $begin_date, $end_date ) {
			$tpl_component = array();
			$tpl_component['STATISTICS_BEGIN_DATE'] = $begin_date;
			$tpl_component['STATISTICS_END_DATE'] = $end_date;
			
			$sub_tpl_component = array_merge( $tpl_component, self::statistics_interface( $begin_date, $end_date ) );
			$tpl_component['STATISTICS_INTERFACE'] = wpshop_display::display_template_element('wps_stats', $sub_tpl_component, array(), 'admin');
			$output =  wpshop_display::display_template_element('wps_statistics_interface', $tpl_component, array(), 'admin');
			$output .=  wpshop_display::display_template_element('hourlyorders', $tpl_component, array(), 'admin');
			unset( $tpl_component );
			
			return $output;
		}
		
		
		/** Display Statistics Dashboard **/
		function wps_display_statistics() {
			$tpl_component = array();
			$status = false; $result = '';
			$begin_date = date( 'Y-m-d', strtotime( '1 months ago') );
			$end_date =  date( 'Y-m-d' );
			echo self::get_statistics_interface( $begin_date, $end_date );
		}
		
		
		function statistics_interface( $begin_date, $end_date ){
			$output = '';
			$tpl_component = $sub_tpl_component = array();
			/** Best sales **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Best sales', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'best_sales';
			$sub_tpl_component['CANVAS_WIDTH'] = 400;
			$sub_tpl_component['CANVAS_HEIGHT'] = 400;
			$sub_tpl_component['STATISTICS_JS'] = self::get_best_sales_datas( $begin_date, $end_date );
			$tpl_component['LEFT_BOXES'] = wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			/** Order summary **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Orders', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'wps_orders_summary';
			$sub_tpl_component['CANVAS_WIDTH'] = 550;
			$sub_tpl_component['CANVAS_HEIGHT'] = 550;
			$sub_tpl_component['STATISTICS_JS'] = self::get_orders_by_month();
			$tpl_component['LEFT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			/** Best customers **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Best customers', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'wps_best_customers';
			$sub_tpl_component['CANVAS_WIDTH'] = 400;
			$sub_tpl_component['CANVAS_HEIGHT'] = 400;
			$sub_tpl_component['STATISTICS_JS'] = self::get_best_customers( $begin_date, $end_date );
			$tpl_component['LEFT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			$tpl_component['RIGHT_BOXES'] = '';
			/** Most viewed products **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Most viewed products', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'wps_most_viewed_products';
			$sub_tpl_component['CANVAS_WIDTH'] = 400;
			$sub_tpl_component['CANVAS_HEIGHT'] = 400;
			$sub_tpl_component['STATISTICS_JS'] = self::get_most_viewed_products();
			$tpl_component['RIGHT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			/** Orders status **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Order Status', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'order_status';
			$sub_tpl_component['CANVAS_WIDTH'] = 400;
			$sub_tpl_component['CANVAS_HEIGHT'] = 400;
			$sub_tpl_component['STATISTICS_JS'] = self::get_order_status_datas( $begin_date, $end_date );
			$tpl_component['RIGHT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			/** Order summary **/
			$sub_tpl_component['STATISTICS_TITLE'] = __('Customers account creation', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'wps_customers_account_creation';
			$sub_tpl_component['CANVAS_WIDTH'] = 550;
			$sub_tpl_component['CANVAS_HEIGHT'] = 550;
			$sub_tpl_component['STATISTICS_JS'] = self::get_customers_by_month();
			$tpl_component['RIGHT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
			
			
			/** Hourly Orders ***/
			
			$sub_tpl_component['STATISTICS_TITLE'] = __('Hourly orders', 'wpshop');
			$sub_tpl_component['STATISTICS_CANVAS_ID'] = 'wps_hourly_orders_canvas';
			$sub_tpl_component['CANVAS_WIDTH'] = 550;
			$sub_tpl_component['CANVAS_HEIGHT'] = 400;
			$sub_tpl_component['STATISTICS_JS'] = '<div id="wps_hourly_order_container">'.self::hourlyorder($begin_date, $end_date, '').'</div>';
			$tpl_component['RIGHT_BOXES'] .= wpshop_display::display_template_element('wps_postbox', $sub_tpl_component, array(), 'admin');
			unset( $sub_tpl_component );
				
			return $tpl_component;
		}
		
		function wps_reload_statistics() {
			$status = false; $result = '';
			
			$begin_date = ( !empty($_POST['date_begin']) ) ? $_POST['date_begin'] : date( 'Y-m-d', strtotime( '1 months ago') );
			$end_date = ( !empty($_POST['date_end']) ) ?  $_POST['date_end'] : date( 'Y-m-d' );
			$tpl_component = self::statistics_interface( $begin_date, $end_date, $choosen_day );
			$result =  wpshop_display::display_template_element('wps_stats', $tpl_component, array(), 'admin');
			$status = true;
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}

		/** Get orders between selected dates ***/
		function monthlyorders($begindate, $enddate) {
			global $wpdb;
			$query = $wpdb->prepare("SELECT COUNT(*)
							FROM {$wpdb->posts} 
							WHERE post_type = '%s' 
							AND post_date 
							BETWEEN '%s' AND '%s'", 
							"wpshop_shop_order", $begindate, $enddate);
			$resultorder = $wpdb->get_var($query);
			return ($resultorder);
	}
	
		/** Get hourly orders ***/
		function hourlyorder($begindate, $enddate, $choosenday, $ajax_origin = false){
			global $wpdb;
			$begin_date = new DateTime( $begindate );
			$enddate = new DateTime( $enddate );
			$begindate = $begin_date->format('Y-m-d H:i:s');
			$enddate = $enddate->format('Y-m-d H:i:s');
			$query = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type = '%s' AND post_date BETWEEN '%s' AND '%s'", "wpshop_shop_order", $begindate, $enddate);
			$resultarray = $wpdb->get_results($query);
			$datadate = array();
			foreach ($resultarray as $array){
				$date = new DateTime( $array->post_date );
				$day = $date->format('l');
				$day = strtolower($day);
				$choosenday = strtolower($choosenday);
				if( empty($choosenday) || ( !empty($choosenday) && $choosenday ==  $day ) ) {
					$hour = $date->format('G');
					if ( empty($datadate[$day])){
						if	(empty($datadate[$day][$hour])){
							$datadate[$day][$hour] = 1;	
						}
						else {
							$datadate[$day][$hour] += 1;
						}
					}
					else {
						$datadate[$day][$hour] += 1;
					}
				}
			}
			/**Display the results ***/
			if( $ajax_origin ) {
				$output .= '<center><canvas id="wps_hourly_orders_canvas" width="550" height="400"></canvas></center>';
			}
			$output .= '
						
						<center>
						<img src="' .WPSHOP_LOADING_ICON. '" alt="' .__( 'Loading', 'wpshop'). '" id="wps-hourly-orders-loader" />
						<button type="button" name="General" id="" class="wps_day_button">' .__( 'General', 'wpshop').'</button>						
						<button type="button" name="Monday" id="monday" class="wps_day_button">' .__( 'Monday', 'wpshop').'</button>
						<button type="button" name="Tuesday" id="tuesday" class="wps_day_button">' .__( 'Tuesday', 'wpshop').'</button>
						<button type="button" name="Wednesday" id="wednesday" class="wps_day_button">' .__( 'Wednesday', 'wpshop').'</button>
						<button type="button" name="Thursday" id="thursday" class="wps_day_button">' .__( 'Thursday', 'wpshop').'</button>
						<button type="button" name="Friday" id=friday" class="wps_day_button">' .__( 'Friday', 'wpshop').'</button>
						<button type="button" name="Saturday" id="saturday" class="wps_day_button">' .__( 'Saturday', 'wpshop').'</button>
						<button type="button" name="Sunday" id="sunday" class="wps_day_button">' .__( 'Sunday', 'wpshop').'</button>
						</center>
						';
			if (!empty($datadate)){
				krsort($datadate);	
				$tmp_array = array();
				foreach( $datadate as $day_name => $day_data ) {
					foreach( $day_data as $hour => $d ) {
						if( empty($tmp_array[$hour]) ) {
							$tmp_array[$hour] += $day_data[$hour];
						}
						else {
							$tmp_array[$hour] += $day_data[$hour];
						}
					}
				}
				$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
				$output .= '<script type="text/javascript">';
				$output .= 'var data = {labels: [';
					for( $i = 0; $i <= 23; $i++ ) {
						$output .= '"'.( ($i < 10 ) ? '0' : '' ).$i.'",';
					}
				$output .= '],datasets: [{label: "Donnees",
				fillColor: "#B8E5ED",
				strokeColor: "#20A2D6",
				pointColor: "#FFF",
				pointStrokeColor: "#0074A2",
				pointHighlightFill: "#0ff",
				pointHighlightStroke: "#17AEEA"
				,data: [';
				for( $i = 0; $i <= 23; $i++ ) {
					$output .= ( !empty($tmp_array[$i]) ) ? $tmp_array[$i].',' : '0,';
				}
				$tmpvalue = 0;
				foreach ($tmp_array as $values){
					if ($values > $tmp_value)
						$tmp_value = $values;
				}
				$output .= ']}]};';
				$output .= 'var LineOrders = new Chart(document.getElementById("wps_hourly_orders_canvas").getContext("2d")).Line(data, {scaleOverride : true, scaleSteps : ';
				$output .= $tmp_value;
				$output .= ', scaleStepWidth : ';
				if ($tmp_value / 2 >= 25)
				$output .= $tmp_value / 2;
				else
				$output .= $tmp_value;
				$output .= ', scaleStartValue : 0})';
				$output .= '</script>';
			}
			else{
					$output .= __('No orders');
			}
			return $output;
}
		
		/** Get best Sales Datas ***/
		function get_best_sales_datas( $begin_date, $end_date ) {
			global $wpdb;
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begin_date.' 00:00:00', $end_date.' 23:59:59' );
			$orders = $wpdb->get_results( $query );
			$output;

			if ( !empty($orders) ) {
				$products = array();
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					if( !empty($order_meta) && !empty($order_meta['order_items']) ) {
						foreach( $order_meta['order_items'] as $item_id => $item ) {
							if( !empty($products[ $item_id ] ) ) {
								$products[ $item_id ] += $item['item_qty'];
							}
							else {
								$products[ $item_id ] = $item['item_qty'];
							}
						}
					}
				}
				/** Sort array **/ 
				if( !empty($products) ) {
					arsort( $products );
					$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
					$output  = '<script type="text/javascript">var pieData = [';
					$i = 0;
					foreach( $products as $product ) {
						if ( $i < 8 ) {
							$output .= '{value:' .$product. ', color:"' .$colors[$i]. '"},';
							$i++;
						}
					}
					$output .= '];';
					$output .= 'var myPie = new Chart(document.getElementById("best_sales").getContext("2d")).Pie(pieData);';
					$output .= '</script>';
					
					$i = 0;
					$output .= '<ul class="wps_statistics_legend">';
					foreach( $products as $item_id => $product ) {
						if ( $i < 8 ) {
							$product_type = get_post_type( $item_id );
							if ( $product_type == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
								$product_name = get_the_title( $item_id );
							}
							else {
								$parent_def = wpshop_products::get_parent_variation( $item_id );
								if ( !empty($parent_def) && !empty($parent_def['parent_post']) ) {
									$parent_post = $parent_def['parent_post'];
									$product_name = $parent_post->post_title;
								}
							}
							$output .= '<li><span style="background : ' .$colors[$i]. ';" class="legend_indicator"></span><span>' .$product_name. ' (' .sprintf( __('%s items', 'wpshop'), $product).')</span></li>';
							$i++;
						}
					}
					$output .= '</ul>';
				}
				unset( $orders );
			}
			else {
				$output = __( 'No order has been made on your shop', 'wpshop');
			}	
			return $output;
		}

		/**
		 * Get order status datas
		 * @return string
		 */
		function get_order_status_datas( $begin_date, $end_date ) {
			global $wpdb;
			$output = '';
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begin_date.' 00:00:00', $end_date.' 23:59:59' );
			$orders = $wpdb->get_results( $query );
			if ( !empty($orders) ) {
				$orders_status = array();
				/** Collect datas **/
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
					if ( !empty($order_meta) && !empty($order_meta['order_status']) ) {
						if ( !empty($orders_status[ $order_meta['order_status'] ]) ) {
							$orders_status[ $order_meta['order_status'] ]++;
						}
						else {
							$orders_status[ $order_meta['order_status'] ] = 1;
						}
					}
				}
				if( !empty($orders_status) ) {
					$colors = array( 'canceled' => '#E0E4CC', 'shipped' => '#69D2E7', 'completed' => '#64BC43', 'refunded' => '#E03E3E', 'partially_paid' => '#FF9900','awaiting_payment' => '#F4FA58', 'denied' => '#414141', 'incorrect_amount' => '#F38630', 'payment_refused' => '#8F33E0');
					arsort( $orders_status );
					$output  = '<script type="text/javascript">var pieData2 = [';
					foreach( $orders_status as $status => $count ) {
						$output .= '{value:' .$count. ', color:"' .$colors[$status]. '"},';
					}
					$output .= '];';
					$output .= 'var pie_order_status = new Chart(document.getElementById("order_status").getContext("2d")).Pie(pieData2);';
					$output .= '</script>';
					$output .= '<ul class="wps_statistics_legend">';
					$payment_status = unserialize( WPSHOP_ORDER_STATUS );
					foreach( $orders_status as $status => $count ) {
						$output .= '<li><div style="background : ' .$colors[$status]. ';" class="legend_indicator"></div>' .__($payment_status[ $status ], 'wpshop' ). ' (' .$count.')</li>';
					}
					$output .= '</ul>';
					
				}
				unset( $orders );
			}
			else {
				$output = __( 'No order has been made on your shop', 'wpshop');
			}
			return $output;
		}
	
		/** 
		 * Get Orders stats by Month
		 * @return string
		 */
		function get_orders_by_month() {
			$output = '';
			$order_recap = array();
			$orders = get_posts( array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'post_status' => 'publish') );
			if ( !empty($orders) ) {
				foreach( $orders as $order ) {
					$order_year = date( 'Y', strtotime( $order->post_date) );
					$order_month = date( 'n', strtotime( $order->post_date) );
					if ( empty($order_recap[$order_year]) ) {
						$order_recap[$order_year] = array();
					}
					$order_meta =  get_post_meta( $order->ID, '_order_postmeta', true );
					if ( !empty($order_meta) && !empty($order_meta['order_grand_total']) ) {
						if ( empty($order_recap[$order_year][ $order_month ]) ) {
							$order_recap[$order_year][ $order_month ] = $order_meta['order_grand_total'];
						}
						else {
							$order_recap[$order_year][ $order_month ] += $order_meta['order_grand_total'];
						}
					}
				}
				/** Display datas **/
				if ( !empty($order_recap) ) {
					krsort( $order_recap );
					$output .= '<script type="text/javascript">';
					$output .= 'var data  = { labels : ["' .__('January', 'wpshop'). '","' .__('February', 'wpshop'). '","' .__('March', 'wpshop'). '","' .__('April', 'wpshop'). '","' .__('May', 'wpshop'). '","' .__('June', 'wpshop'). '","' .__('July', 'wpshop'). '","' .__('August', 'wpshop'). '" ,"' .__('September', 'wpshop'). '" ,"' .__('October', 'wpshop'). '","' .__('November', 'wpshop'). '","' .__('December', 'wpshop'). '"],';
					$output .= 'datasets : [';
					$i = 0;
					$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
					$order_recap = array_slice( $order_recap, 0, 2, true );
					$order_recap = array_reverse( $order_recap, true );
					foreach( $order_recap as $y => $year ) {
						
						if ( $i < 2 ) {
							$output .= '{fillColor : "' .$colors[$i][0]. '",pointStrokeColor : "#fff",strokeColor :"' .$colors[$i][1]. '", pointColor :"' .$colors[$i][1]. '", ';
							$output .= 'data : [';
							for( $j = 1; $j <= 12; $j++) {
								if( !empty($year[$j]) ) {
									$output .= round($year[$j]).',';
								}
								else {
									$output .= '0,';
								}
							}
							$output .= ']';
							$output .= '},';
							$colors[$i][] = $y;

							$i++;
						}
					}
					$output .= ']};';
					$output .= 'var LineOrders = new Chart(document.getElementById("wps_orders_summary").getContext("2d")).Line(data);';
					$output .= '</script>';
					
					/** Legend **/
					$output .= '<center><ul class="wps_statistics_legend">';
					foreach( $colors as $color ) {
						if ( !empty($color) && !empty($color[2]) )
							$output .= '<li style="width : auto; margin-right : 20px;"><div style="background : ' .$color[0]. ';" class="legend_indicator"></div>' .$color[2]. '</li>';
					}
					$output .= '</ul></center>';
					
				}
				
			}
			else {
				$output = __( 'No order has been made on your shop', 'wpshop');
			}
			return $output;
		}
		
		/**
		 * Get Customers account creation by month
		 * @return string
		 */
		function get_customers_by_month() {
			$output = '';
			$customers_recap = array();
			$count_users = 0;
			$users = get_users();
			if ( !empty($users) ) {
				foreach( $users as $user ) {
					$user_year = date( 'Y', strtotime( $user->data->user_registered) );
					$user_month = date( 'n', strtotime( $user->data->user_registered) );
					if ( empty($customers_recap[$user_year]) ) {
						$customers_recap[$user_year] = array();
					}
					$user_role = $user->roles;
					if ( !empty($user_role) && in_array( 'customer', $user_role ) ) {
						if ( empty($customers_recap[$user_year][ $user_month ]) ) {
							$customers_recap[$user_year][ $user_month ] = 1;
						}
						else {
							$customers_recap[$user_year][ $user_month ]++;
						}
					}
				}
				
				/** Display Datas **/
				if ( !empty($customers_recap) ) {
					krsort( $customers_recap );
					$output .= '<script type="text/javascript">';
					$output .= 'var data  = { labels : ["' .__('January', 'wpshop'). '","' .__('February', 'wpshop'). '","' .__('March', 'wpshop'). '","' .__('April', 'wpshop'). '","' .__('May', 'wpshop'). '","' .__('June', 'wpshop'). '","' .__('July', 'wpshop'). '","' .__('August', 'wpshop'). '" ,"' .__('September', 'wpshop'). '" ,"' .__('October', 'wpshop'). '","' .__('November', 'wpshop'). '","' .__('December', 'wpshop'). '"],';
					$output .= 'datasets : [';
					$i = 0;
					$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
					$customers_recap = array_slice( $customers_recap, 0, 2, true );
					$customers_recap = array_reverse( $customers_recap, true );
					foreach( $customers_recap as $y => $year ) {
					
						if ( $i < 2 ) {
							$output .= '{fillColor : "' .$colors[$i][0]. '",strokeColor :"' .$colors[$i][1]. '",';
							$output .= 'data : [';
							for( $j = 1; $j <= 12; $j++) {
								if( !empty($year[$j]) ) {
									$output .= $year[$j].',';
									if ( $count_users < $year[$j] ) {
										$count_users = $year[$j];
									}
								}
								else {
									$output .= '0,';
								}
							}
							$output .= ']';
							$output .= '},';
							$colors[$i][] = $y;
							$i++;
						}
					}
					$output .= ']};';
					$output .= 'var BarCustomers = new Chart(document.getElementById("wps_customers_account_creation").getContext("2d")).Bar(data, {scaleOverride : true, scaleSteps : ' .round( ($count_users / 5) ). ', scaleStepWidth : 5, scaleStartValue : 0});';
					$output .= '</script>';
					
					/** Legend **/
					$output .= '<center><ul class="wps_statistics_legend">';
					foreach( $colors as $color ) {
						if ( !empty($color) && !empty($color[2]) )
						$output .= '<li style="width : auto; margin-right : 20px;"><div style="background : ' .$color[0]. ';" class="legend_indicator"></div>' .$color[2]. '</li>';
					}
					$output .= '</ul></center>';
				}	
				
			}
			else {
				$output = __( 'No customer account has been created on your shop', 'wpshop');
			}
			return $output;
		}
		
		/** Get most viewed products **/
		function get_most_viewed_products() {
			global $wpdb;
			$output = '';
			
			$query = $wpdb->prepare( 'SELECT post_id, meta_value FROM '. $wpdb->postmeta .', ' .$wpdb->posts. ' WHERE post_id = ID AND post_status = %s AND meta_key = %s ORDER BY CAST( meta_value AS SIGNED ) DESC LIMIT 8', 'publish', '_wpshop_product_view_nb');
			$products = $wpdb->get_results( $query );
			
			if( !empty($products) ) {
				$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
				$output  = '<script type="text/javascript">var pieData = [';
				$i = 0;
				foreach( $products as $product ) {
					$output .= '{value:' .$product->meta_value. ', color:"' .$colors[$i]. '"},';
					$i++;
				}
				$output .= '];';
				$output .= 'var most_viewed_products = new Chart(document.getElementById("wps_most_viewed_products").getContext("2d")).Pie(pieData);';
				$output .= '</script>';
				
				/** Legend **/
				$i = 0;
				
				$output .= '<ul class="wps_statistics_legend">';
				foreach( $products as $product ) {
					$output .= '<li><div style="background : ' .$colors[$i]. ';" class="legend_indicator"></div>' .get_the_title( $product->post_id ). ' (' .sprintf( __('%s views', 'wpshop'), $product->meta_value).')</li>';
					$i++;
				}
				$output .= '</ul>';
			}
			else {
				$output = __( 'No products has been viewed on your shop', 'wpshop');
			}
			return $output;
		}
		
		/** Get best customers **/
		function get_best_customers( $begin_date, $end_date ) {
			global $wpdb;
			
			$output = '';
			$customer_recap = array();
			
			$query = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_status = %s AND post_date BETWEEN %s AND %s',WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', $begin_date.' 00:00:00', $end_date.' 23:59:59' );
			$orders = $wpdb->get_results( $query );
			
			if ( !empty($orders) ) {
				foreach( $orders as $order ) {
					$order_meta = get_post_meta( $order->ID, '_order_postmeta', true);
					if ( !empty($order_meta) && !empty($order_meta['customer_id']) && !empty($order_meta['order_grand_total']) ) {
						/** Check if user is administrator **/
						$user_def = get_user_by( 'id', $order_meta['customer_id'] );
						$wps_statistics_exclude_customer = get_user_meta( $order_meta['customer_id'], 'wps_statistics_exclude_customer', true );
						$excluded_from_statistics = ( !empty($wps_statistics_exclude_customer) ) ? true : false;
						
						
						if ( !empty($user_def) && !empty($user_def->caps) && is_array($user_def->caps) && array_key_exists( 'customer', $user_def->caps) && $excluded_from_statistics === false ) {
							if ( empty($customer_recap[ $order_meta['customer_id'] ] ) ) {
								$customer_recap[ $order_meta['customer_id'] ] = $order_meta['order_grand_total'];
							}
							else {
								$customer_recap[ $order_meta['customer_id'] ] += $order_meta['order_grand_total']; 
							}
						}
					}
				}
				
				if ( !empty($customer_recap) ) {
					arsort( $customer_recap );
					$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
					$output  = '<script type="text/javascript">var pieData = [';
					$i = 0;
					foreach( $customer_recap as $customer_id => $customer ) {
						if ( $i < 8 ) {
							$output .= '{value:' .round($customer, 2). ', color:"' .$colors[$i]. '"},';
							$i++;
						}
					}
					$output .= '];';
					$output .= 'var best_customers = new Chart(document.getElementById("wps_best_customers").getContext("2d")).Pie(pieData);';
					$output .= '</script>';
					
					$i = 0;
					$output .= '<ul class="wps_statistics_legend">';
					foreach( $customer_recap as $customer_id => $customer ) {
						if ( $i < 8 ) {
							$user_data = get_userdata( $customer_id );
							$customer_name = ( !empty($user_data) && !empty($user_data->last_name) ) ? strtoupper( $user_data->last_name) : '';
							$customer_name .= ( !empty($user_data) && !empty($user_data->first_name) ) ? ' '.$user_data->first_name : '';
							$customer_email = ( !empty($user_data) && !empty($user_data->user_email) ) ? ' - '.$user_data->user_email : '';
							$output .= '<li><div style="background : ' .$colors[$i]. ';" class="legend_indicator"></div>' .$customer_name.$customer_email.' (' .number_format($customer, 2, '.', '').' '.wpshop_tools::wpshop_get_currency( false ).')</li>';
							$i++;
						}
					}
					$output .= '</ul>';
				}
				unset( $orders );
			}
			else {
				$output = __( 'There is non best customer on your shop', 'wpshop');
			}
			return $output;
		}
		
		function wps_hourly_order_day(){
			$status = false; $result = '';
			$choosen_day = ( !empty($_POST['day']) ) ? sanitize_title($_POST['day']) : '';
			$begin_date = ( !empty($_POST['date_begin']) ) ? sanitize_title($_POST['date_begin']) : '';
			$end_date = ( !empty($_POST['date_end']) ) ? sanitize_title($_POST['date_end']) : '';
			$result = $this->hourlyorder( $begin_date, $end_date, $choosen_day, true);
			if( !empty($result) ) {
				$status = true;
			}
			
			echo json_encode( array( 'status' => $status, 'response' => $result ) );
			wp_die();
		}
		
	}
}


if ( class_exists("wps_statistics") ) {
	$wps_statistics = new wps_statistics();
}