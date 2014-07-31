<?php
/**
 * File for installer control class definition
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */

/**
 * Class for installer control
 *
 * @author Development team <dev@eoxia.com>
 * @version 1.0
 *
 */
class wps_dashboard_ctr {

	/**
	 * Instanciate the module controller
	 */
	function __construct() {	}

	/**
	 * Check and get the template file path to use for a given display part
	 *
	 * @uses locate_template()
	 * @uses get_template_part()
	 *
	 * @param string $plugin_dir_name
	 * @param string $plugin_template_dir
	 * @param string $side The website part were the template will be displayed. Backend or frontend
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 *
	 * @return string The template file path to use
	 */
	function get_template_part( $plugin_dir_name, $plugin_template_dir, $side, $slug, $name = "" ) {
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
			$check_theme_template = $plugin_dir_name . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );

		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;

				if ( file_exists( $plugin_template_dir . $template_name ) ) {
					$path = $plugin_template_dir . $template_name;
					break;
				}
			}
		}

		return $path;
	}

	/**
	 * DISPLAy - Display wpshop dashboard
	 */
	function display_dashboard() {
		global $order_status, $wpdb;

		require_once( $this->get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, "backend", "dashboard" ) );
	}


	function wpshop_dashboard_orders() {
		$output = '';
		$orders = get_posts( array( 'posts_per_page' => 10, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'post_status' => 'publish', 'orderby' => 'post_date', 'order' => 'DESC') );
		if ( !empty($orders) ) {
			$payment_status = unserialize( WPSHOP_ORDER_STATUS );

			$output .= '<table id="wps_dashboard_orders_summary">';
			$output .= '<tr><th class="wps_dashboard_order_date">' .__('Date', 'wpshop'). '</th><th class="wps_dashboard_order_customer_name">' .__('Customer', 'wpshop'). '</th><th class="wps_dashboard_order_amount">' .__('Amount', 'wpshop'). '</th><th class="wps_dashboard_order_status">' .__('Status', 'wpshop'). '</th><th class="wps_dashboard_order_actions"></th></tr>';
			$stried = false;
			foreach( $orders as $order ) {
				$stried = ( $stried == false ) ? true : false;
				$additionnal_class = ($stried) ? 'wps_stried_line' : '';
				$output .= '<tr class="' .$additionnal_class. '">';
				$order_meta = get_post_meta( $order->ID, '_order_postmeta', true );
				$order_info = get_post_meta( $order->ID, '_order_info', true );

				if ( !empty($order_meta) ) {
					$output .= '<td>' .( (!empty($order_meta) && !empty($order_meta['order_date']) ) ? date( 'd-m-Y', strtotime($order_meta['order_date']) ): '' ). '</td>';
					$output .= '<td>' .( (!empty($order_info) && !empty($order_info['billing']) && !empty($order_info['billing']['address']) && !empty($order_info['billing']['address']['address_last_name']) && !empty($order_info['billing']['address']['address_first_name']) ) ? strtoupper($order_info['billing']['address']['address_last_name']).' '.$order_info['billing']['address']['address_first_name']: '' ). '</td>';

					$output .= '<td>' .( (!empty($order_meta['order_grand_total']) ) ? number_format( $order_meta['order_grand_total'], 2, '.', '' ).' '.wpshop_tools::wpshop_get_currency( false ) : '-' ). '</td>';
					$output .= '<td><span class="wps_dashboard_' .$order_meta['order_status']. '">' .__($payment_status[ $order_meta['order_status'] ], 'wpshop' ). '</span></td>';
					$output .= '<td>';
					$output .= '<a href="' .admin_url('/post.php?post=' .$order->ID. '&action=edit'). '"><img src="' .WPSHOP_MEDIAS_ICON_URL. 'icon_loupe.png" alt="' .__('See', 'wpshop'). '" /></a>';

					$invoice_ref = '';
					if ( !empty($order_meta['order_invoice_ref']) ) {
						$invoice_ref = $order_meta['order_invoice_ref'];
					}
					if ( !empty($invoice_ref) ) {
						if( !empty($order_meta) && !empty($order_meta['order_payment']) && !empty($order_meta['order_payment']['received']) ) {
							$invoice_ref = $order_meta['order_payment']['received'][ count($order_meta['order_payment']['received']) - 1 ]['invoice_ref'];
						}
					}

					if ( ( $order_meta['order_status'] == 'partially_paid' || $order_meta['order_status'] == 'completed' || $order_meta['order_status'] == 'shipped' ) && !empty($invoice_ref) ) {
						$output .= ' <a href="' .WPSHOP_TEMPLATES_URL. 'invoice.php?order_id=' .$order->ID. '&invoice_ref&=' .$invoice_ref. '&mode=pdf"><img src="' .WPSHOP_MEDIAS_ICON_URL. 'icon_invoice.png" alt="' .__('Invoice', 'wpshop'). '" /></a>';
					}
					if ( $order_meta['order_status'] == 'shipped' ) {
						$output .= ' <a href="'.WPSHOP_TEMPLATES_URL. 'invoice.php?order_id=' .$order->ID. '&bon_colisage=ok&mode=pdf"><img src="' .WPSHOP_MEDIAS_ICON_URL. 'bon_colisage_icon.png" alt="' .__('Shipping Slip', 'wpshop'). '" /></a>';
					}
					$output .= '</td>';
				}
				$output .= '</tr>';
			}
			$output .= '</table>';
		}

		return $output;
	}


	function wpshop_rss_feed() {
		$output = '';
		include_once( ABSPATH . WPINC . '/feed.php' );

		$rss = fetch_feed( 'http://www.wpshop.fr/feed/' );
		if( ! is_wp_error( $rss ) ){
			$maxitems = $rss->get_item_quantity( 4 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}
		else {
			$output .= '<p>' . __('WPShop News cannot be loaded', 'wpshop') . '</p>';
		}

		if ( $maxitems == 0 ) {
			$output .= '<p>' . __('No WPShop new has been found', 'wpshop') . '</p>';
		}
		else {
			$output .= '<ul class="recent-orders">';
			foreach ( $rss_items as $item ) {
				$output .= '<li><a href="' .$item->get_permalink() . '" title="' .$item->get_title(). '" target="_blank">' .$item->get_title(). '</a><br/>';
				$output .= $item->get_content();
				$output .= '</li>';
			}
			$output .= '</ul>';
		}
		echo $output;
	}


	function wpshop_rss_tutorial_videos() {
		$ini_get_checking = ini_get( 'allow_url_fopen' );

		if ( $ini_get_checking != 0 ) {
			$content = file_get_contents( 'http://www.wpshop.fr/rss_video.xml' );
			$videos_rss = new SimpleXmlElement($content);
			if ( !empty($videos_rss) && !empty($videos_rss->channel) ) {
				$videos_items = array();
				foreach( $videos_rss->channel->item as $i => $item ) {
					$videos_items[] = $item;
				}
				$rand_element = array_rand( $videos_items );

				ob_start();
				require_once( $this->get_template_part( WPS_DASHBOARD_DIR, WPSDASHBOARD_TPL_DIR, "backend", "dashboard", "videos" ) );
				$output = ob_get_contents();
				ob_end_clean();
			}
			else {
				$output = __('No tutorial videos can be loaded', 'wpshop' );
			}
		}
		else {
			$output = __( 'Your servor doesn\'t allow to open external files', 'wpshop');
		}

		echo $output;
	}

	function wpshop_dashboard_get_changelog() {
		$readme_file = fopen( WPSHOP_DIR.'/readme.txt', 'r' );
		if ( $readme_file ) {
			$txt = file_get_contents( WPSHOP_DIR.'/readme.txt' );
			$pre_change_log = explode( '== Changelog ==', $txt );
			$versions = explode( '= Version', $pre_change_log[1] );

			echo $versions[1];
		}
	}
}

?>