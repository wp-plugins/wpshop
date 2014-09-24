<?php
class wps_statistics {
	private $template_dir;
	
	private $plugin_dirname = WPS_STATISTICS_DIR;
	
	function __construct() {
		$this->template_dir = WPS_STATISTICS_PATH . WPS_STATISTICS_DIR . "/templates/";
		add_action('admin_menu', array(&$this, 'register_stats_menu'), 250);
		add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
		add_action( 'save_post', array( &$this, 'wps_statistics_save_customer_infos') );
		/** Add admin script **/
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		/** Add CSS Files **/
		add_action( 'wp_enqueue_scripts', array( $this, 'add_css_files' ) );
		
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
		add_meta_box( 'wps_statistics_customer', __( 'Statistics', 'wps_price' ), array( &$this, 'wps_statistics_meta_box_content' ), WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, 'side', 'low' );
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
	}
	
	/** Load the module template **/
	function custom_template_load( $templates ) {
	$path = plugin_dir_path(__FILE__).'templates\backend\main_elements.tpl.php';
	$path = str_replace('controller/', '', $path);
		include($path);
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
	$this->hourly_orders($begin_date, $end_date);
	$this->best_orders($begin_date, $end_date);
	$this->customers_by_month();
}

	
	/** Display Statistics Dashboard **/
	function wps_display_statistics() {
		$tpl_component = array();
		$status = false; $result = '';
		$begin_date = date( 'Y-m-d', strtotime( '1 months ago') );
		$end_date =  date( 'Y-m-d' );
		echo $this->get_statistics_interface( $begin_date, $end_date );
	}
	
	
	function get_template_part( $side, $slug, $name=null ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
	
		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $this->plugin_dirname . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
	
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
	
				if ( file_exists($this->template_dir . $template_name)) {
					$path = $this->template_dir . $template_name;
					break;
				}
			}
		}
		return $path;
	}
	
	function hourly_orders($begindate, $enddate) {
		$canvas_js = '';
		$box_title = __('Hourly orders', 'wpshop');
		$canvas_width = 550;
		$canvas_height = 400;
		$wps_stats_mdl = new wps_statistics_mdl();
		$datadate = $wps_stats_mdl->wps_get_orders_by_hours( $begindate, $enddate, $choosenday, $ajax_origin);
		
			$canvas_js .= '
			
			<center>
			<img src="' .WPSHOP_LOADING_ICON. '" alt="' .__( 'Loading', 'wpshop'). '" id="wps-hourly-orders-loader" />
			<button type="button" name="General" id="" class="wps_day_button">General</button>						
			<button type="button" name="Monday" id="monday" class="wps_day_button">Monday</button>
			<button type="button" name="Tuesday" id="tuesday" class="wps_day_button">Tuesday</button>
			<button type="button" name="Wednesday" id="wednesday" class="wps_day_button">Wednesday</button>
			<button type="button" name="Thursday" id="thursday" class="wps_day_button">Thursday</button>
			<button type="button" name="Friday" id=friday" class="wps_day_button">Friday</button>
			<button type="button" name="Saturday" id="saturday" class="wps_day_button">Saturday</button>
			<button type="button" name="Sunday" id="sunday" class="wps_day_button">Sunday</button>
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
				$canvas_js .= '<script type="text/javascript">';
				$canvas_js .= 'var data = {labels: [';
					for( $i = 0; $i <= 23; $i++ ) {
						$canvas_js .= '"'.( ($i < 10 ) ? '0' : '' ).$i.'",';
					}
				$canvas_js .= '],datasets: [{label: "Donnees",
				fillColor: "rgba(50,50,50,0.2)",
				strokeColor: "rgba(220,150,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#0ff",
				pointHighlightFill: "#0ff",
				pointHighlightStroke: "rgba(220,220,220,1)"
				,data: [';
				for( $i = 0; $i <= 23; $i++ ) {
					$canvas_js .= ( !empty($tmp_array[$i]) ) ? $tmp_array[$i].',' : '0,';
				}
				$tmpvalue = 0;
				foreach ($tmp_array as $values){
					if ($values > $tmp_value)
						$tmp_value = $values;
				}
				$canvas_js .= ']}]};';
				$canvas_js .= 'var LineOrders = new Chart(document.getElementById("wps_hourly_orders_canvas").getContext("2d")).Line(data, {scaleOverride : true, scaleSteps : ';
				$canvas_js .= $tmp_value;
				$canvas_js .= ', scaleStepWidth : ';
				if ($tmp_value / 2 >= 25)
				$canvas_js .= $tmp_value / 2;
				else
				$canvas_js .= $tmp_value;
				$canvas_js .= ', scaleStartValue : 0})';
				$canvas_js .= '</script>';
			}
			else{
					$canvas_js .= __('No orders', 'wpshop');
			} 
			require( $this->get_template_part( "backend", "statistic_metabox_content") );
	}
	
	function best_orders($begindate, $enddate){
			$canvas_js = '';
			$box_title = __('Best sales', 'wpshop');
			$canvas_width = 550;
			$canvas_height = 400;
			$wps_stats_mdl = new wps_statistics_mdl();
			$products = $wps_stats_mdl->wps_best_sales($begindate, $enddate);
			if( !empty($products) ) {
				arsort( $products );
				$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
				$canvas_js  = '<script type="text/javascript">var pieData = [';
				$i = 0;
				foreach( $products as $product ) {
					if ( $i < 8 ) {
						$canvas_js .= '{value:' .$product. ', color:"' .$colors[$i]. '"},';
						$i++;
					}
				}
				$canvas_js .= '];';
				$canvas_js .= 'var myPie = new Chart(document.getElementById("best_sales").getContext("2d")).Pie(pieData);';
				$canvas_js .= '</script>';
				
				$i = 0;
				$canvas_js .= '<ul class="wps_statistics_legend">';
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
						$canvas_js .= '<li><div style="background : ' .$colors[$i]. ';" class="legend_indicator"></div>' .$product_name. ' (' .sprintf( __('%s items', 'wpshop'), $product).')</li>';
						$i++;
					}
				}
				$canvas_js .= '</ul>';
			}
			else {
				$canvas_js = __( 'No order has been made on your shop', 'wpshop');
			unset( $orders );
		}
		require( $this->get_template_part( "backend", "statistic_metabox_content") );
	}
		
		
	function order_status($begindate, $enddate){
		$canvas_js = '';
		$box_title = __('Orders status', 'wpshop');
		$canvas_width = 550;
		$canvas_height = 400;
		$wps_stats_mdl = new wps_statistics_mdl();
		$products = $wps_stats_mdl->wps_order_status($begindate, $enddate);
		if( !empty($orders_status) ) {
			$colors = array( 'canceled' => '#E0E4CC', 'shipped' => '#69D2E7', 'completed' => '#64BC43', 'refunded' => '#E03E3E', 'partially_paid' => '#FF9900','awaiting_payment' => '#F4FA58', 'denied' => '#414141', 'incorrect_amount' => '#F38630', 'payment_refused' => '#8F33E0');
			arsort( $orders_status );
			$canvas_js  = '<script type="text/javascript">var pieData2 = [';
			foreach( $orders_status as $status => $count ) {
				$canvas_js .= '{value:' .$count. ', color:"' .$colors[$status]. '"},';
			}
			$canvas_js .= '];';
			$canvas_js .= 'var pie_order_status = new Chart(document.getElementById("order_status").getContext("2d")).Pie(pieData2);';
			$canvas_js .= '</script>';
			$canvas_js .= '<ul class="wps_statistics_legend">';
			$payment_status = unserialize( WPSHOP_ORDER_STATUS );
			foreach( $orders_status as $status => $count ) {
				$canvas_js .= '<li><span style="background : ' .$colors[$status]. ';" class="legend_indicator"></span><span>' .__($payment_status[ $status ], 'wpshop' ). ' (' .$count.')</span></li>';
			}
			$canvas_js .= '</ul>';
			
		}
		else {
			$canvas_js = __( 'No order has been made on your shop', 'wpshop');
		}
		unset( $orders );
		require( $this->get_template_part( "backend", "statistic_metabox_content") );
	}
	
	
	function orders_by_month(){
		$canvas_js = '';
		$box_title = __('Orders by month', 'wpshop');
		$canvas_width = 550;
		$canvas_height = 400;
		$wps_stats_mdl = new wps_statistics_mdl();
		$products = $wps_stats_mdl->wps_orders_by_month();
		if ( !empty($order_recap) ) {
			krsort( $order_recap );
			$canvas_js .= '<script type="text/javascript">';
			$canvas_js .= 'var data  = { labels : ["' .__('January', 'wpshop'). '","' .__('February', 'wpshop'). '","' .__('March', 'wpshop'). '","' .__('April', 'wpshop'). '","' .__('May', 'wpshop'). '","' .__('June', 'wpshop'). '","' .__('July', 'wpshop'). '","' .__('August', 'wpshop'). '" ,"' .__('September', 'wpshop'). '" ,"' .__('October', 'wpshop'). '","' .__('November', 'wpshop'). '","' .__('December', 'wpshop'). '"],';
			$canvas_js .= 'datasets : [';
			$i = 0;
			$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
			$order_recap = array_slice( $order_recap, 0, 2, true );
			$order_recap = array_reverse( $order_recap, true );
			foreach( $order_recap as $y => $year ) {
				
				if ( $i < 2 ) {
					$canvas_js .= '{fillColor : "' .$colors[$i][0]. '",pointStrokeColor : "#fff",strokeColor :"' .$colors[$i][1]. '", pointColor :"' .$colors[$i][1]. '", ';
					$canvas_js .= 'data : [';
					for( $j = 1; $j <= 12; $j++) {
						if( !empty($year[$j]) ) {
							$canvas_js .= round($year[$j]).',';
						}
						else {
							$canvas_js .= '0,';
						}
					}
					$canvas_js .= ']';
					$canvas_js .= '},';
					$colors[$i][] = $y;

					$i++;
				}
			}
			$canvas_js .= ']};';
			$canvas_js .= 'var LineOrders = new Chart(document.getElementById("wps_orders_summary").getContext("2d")).Line(data);';
			$canvas_js .= '</script>';
			
			/** Legend **/
			$canvas_js .= '<center><ul class="wps_statistics_legend">';
			foreach( $colors as $color ) {
				if ( !empty($color) && !empty($color[2]) )
					$canvas_js .= '<li style="width : auto; margin-right : 20px;"><div style="background : ' .$color[0]. ';" class="legend_indicator"></div>' .$color[2]. '</li>';
			}
			$canvas_js .= '</ul></center>';
			
		}
		else {
			$canvas_js = __( 'No order has been made on your shop', 'wpshop');
		}
		require( $this->get_template_part( "backend", "statistic_metabox_content") );
	}
	
	
	function customers_by_month(){
	$canvas_js = '';
	$box_title = __('Monthly customers', 'wpshop');
	$canvas_width = 550;
	$canvas_height = 400;
	$wps_stats_mdl = new wps_statistics_mdl();
	$products = $wps_stats_mdl->wps_customers_month();
		if ( !empty($customers_recap) ) {
				krsort( $customers_recap );
				$canvas_js .= '<script type="text/javascript">';
				$canvas_js .= 'var data  = { labels : ["' .__('January', 'wpshop'). '","' .__('February', 'wpshop'). '","' .__('March', 'wpshop'). '","' .__('April', 'wpshop'). '","' .__('May', 'wpshop'). '","' .__('June', 'wpshop'). '","' .__('July', 'wpshop'). '","' .__('August', 'wpshop'). '" ,"' .__('September', 'wpshop'). '" ,"' .__('October', 'wpshop'). '","' .__('November', 'wpshop'). '","' .__('December', 'wpshop'). '"],';
				$canvas_js .= 'datasets : [';
				$i = 0;
				$colors = array(array('#E0E4CC', '#A8AA99') , array('#69D2E7', '#4CA3B5'));
				$customers_recap = array_slice( $customers_recap, 0, 2, true );
				$customers_recap = array_reverse( $customers_recap, true );
				foreach( $customers_recap as $y => $year ) {
				
					if ( $i < 2 ) {
						$canvas_js .= '{fillColor : "' .$colors[$i][0]. '",strokeColor :"' .$colors[$i][1]. '",';
						$canvas_js .= 'data : [';
						for( $j = 1; $j <= 12; $j++) {
							if( !empty($year[$j]) ) {
								$canvas_js .= $year[$j].',';
								if ( $count_users < $year[$j] ) {
									$count_users = $year[$j];
								}
							}
							else {
								$canvas_js .= '0,';
							}
						}
						$canvas_js .= ']';
						$canvas_js .= '},';
						$colors[$i][] = $y;
						$i++;
					}
				}
				$canvas_js .= ']};';
				$canvas_js .= 'var BarCustomers = new Chart(document.getElementById("wps_customers_account_creation").getContext("2d")).Bar(data, {scaleOverride : true, scaleSteps : ' .round( ($count_users / 5) ). ', scaleStepWidth : 5, scaleStartValue : 0});';
				$canvas_js .= '</script>';
				
				/** Legend **/
				$canvas_js .= '<center><ul class="wps_statistics_legend">';
				foreach( $colors as $color ) {
					if ( !empty($color) && !empty($color[2]) )
					$canvas_js .= '<li style="width : auto; margin-right : 20px;"><div style="background : ' .$color[0]. ';" class="legend_indicator"></div>' .$color[2]. '</li>';
				}
				$canvas_js .= '</ul></center>';
		}
		else {
			$canvas_js = __( 'No customer account has been created on your shop', 'wpshop');
		}
	}
	
	
	function most_viewed_month(){
	$canvas_js = '';
	$box_title = __('Most viewed products', 'wpshop');
	$canvas_width = 550;
	$canvas_height = 400;
	$wps_stats_mdl = new wps_statistics_mdl();
	$products = $wps_stats_mdl->wps_most_viewed_products();
		if( !empty($products) ) {
			$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
			$canvas_js  = '<script type="text/javascript">var pieData = [';
			$i = 0;
			foreach( $products as $product ) {
				$canvas_js .= '{value:' .$product->meta_value. ', color:"' .$colors[$i]. '"},';
				$i++;
			}
			$canvas_js .= '];';
			$canvas_js .= 'var most_viewed_products = new Chart(document.getElementById("wps_most_viewed_products").getContext("2d")).Pie(pieData);';
			$canvas_js .= '</script>';
			
			/** Legend **/
			$i = 0;
			$canvas_js .= '<ul class="wps_statistics_legend">';
			foreach( $products as $product ) {
				$canvas_js .= '<li><div style="background : ' .$colors[$i]. ';" class="legend_indicator"></div>' .get_the_title( $product->post_id ). ' (' .sprintf( __('%s views', 'wpshop'), $product->meta_value).')</li>';
				$i++;
			}
			$canvas_js .= '</ul>';
		}
		else {
			$canvas_js = __( 'No products has been viewed on your shop', 'wpshop');
		}
		require( $this->get_template_part( "backend", "statistic_metabox_content") );
	}
	
	
	function best_customers(){
	$canvas_js = '';
	$box_title = __('Best customers', 'wpshop');
	$canvas_width = 550;
	$canvas_height = 400;
	$wps_stats_mdl = new wps_statistics_mdl();
	$products = $wps_stats_mdl->wps_best_customers();
		if ( !empty($customer_recap) ) {
			arsort( $customer_recap );
			$colors = array( '#69D2E7', '#E0E4CC', '#F38630', '#64BC43', '#8F33E0', '#F990E6', '#414141', '#E03E3E');
			$canvas_js  = '<script type="text/javascript">var pieData = [';
			$i = 0;
			foreach( $customer_recap as $customer_id => $customer ) {
				if ( $i < 8 ) {
					$canvas_js .= '{value:' .round($customer, 2). ', color:"' .$colors[$i]. '"},';
					$i++;
				}
			}
			$canvas_js .= '];';
			$canvas_js .= 'var best_customers = new Chart(document.getElementById("wps_best_customers").getContext("2d")).Pie(pieData);';
			$canvas_js .= '</script>';
			
			$i = 0;
			$canvas_js .= '<ul class="wps_statistics_legend">';
			foreach( $customer_recap as $customer_id => $customer ) {
				if ( $i < 8 ) {
					$user_data = get_userdata( $customer_id );
					$customer_name = ( !empty($user_data) && !empty($user_data->last_name) ) ? strtoupper( $user_data->last_name) : '';
					$customer_name .= ( !empty($user_data) && !empty($user_data->first_name) ) ? ' '.$user_data->first_name : '';
					$customer_email = ( !empty($user_data) && !empty($user_data->user_email) ) ? ' - '.$user_data->user_email : '';
					$canvas_js .= '<li><div style="background : ' .$colors[$i]. ';" class="legend_indicator"></div>' .$customer_name.$customer_email.' (' .number_format($customer, 2, '.', '').' '.wpshop_tools::wpshop_get_currency( false ).')</li>';
					$i++;
				}
			}
			$canvas_js .= '</ul>';
		}
		else {
			$canvas_js = __( 'There is non best customer on your shop', 'wpshop');
		}
		unset( $orders );
	}
}
