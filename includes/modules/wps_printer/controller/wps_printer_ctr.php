<?php
class wps_printer{
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_PRINTER_DIR;
	
	
	function __construct() {
		// Template loading...
		$this->template_dir = WPS_PRINTER_PATH . WPS_PRINTER_DIR . '/templates/';
	}
	
	/**
	 * Generate Invoice
	 * @param integer $order_id
	 * @param $string $invoice_ref
	 * @param string $output_type
	 * @return string
	 */
	function generate_invoice( $order_id, $invoice_ref, $output_type = 'pdf' ) {
		$output = $invoice_css = $invoice_sender_data = $invoice_receiver_data = '';
		// Order datas 
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
// 		echo '<pre>';print_r( $order_postmeta );echo '</pre>';exit;
		$wps_currency = wpshop_tools::wpshop_get_currency();
		
// 		echo '<pre>';print_r( $order_postmeta );echo '</pre>';exit;
		// Add CSS Style rules
		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRINTER_DIR, $this->template_dir, "frontend", "wps_printer_css") );
		$invoice_css .= ob_get_contents();
		ob_end_clean();
		
		// Load sender informations
		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRINTER_DIR, $this->template_dir, "frontend", "wps_printer_sender_part_template") );
		$invoice_sender_data .= ob_get_contents();
		ob_end_clean();
		
		//Load Receiver informations
// 		$wps_billing = new wps_billing();
// 		$invoice_receiver_data = $wps_billing->invoice_receiver_part( $order_id );
		
		// Load Invoice page content
		ob_start();
		require( wpshop_tools::get_template_part( WPS_PRINTER_DIR, $this->template_dir, "frontend", "wps_invoice_template") );
		$output .= ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
}