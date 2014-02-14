<?php
/**
 * Plugin Name: WP Shop Help
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Help
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WpShop Help bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wps_help") ) {
	class wps_help {
		function __construct() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			
			add_action( 'admin_print_footer_scripts', array( &$this, 'wps_dashboard_help') );
			
			/** Ajax actions **/
			add_action( 'wp_ajax_close_wps_help_window', array( &$this, 'wps_ajax_close_wps_help_window' ) );
		}
		
		function wps_dashboard_help (){

			$help_cases = array( 'dashboard_help' => array( 'edge' => 'left', 'at' => 'left bottom', 'my' => 'left top', 'pointer_id' => '#download_newsletter_contacts' ),
								 'product_page_categories' => array( 'edge' => 'right',  'at' => '', 'my' => '', 'pointer_id' => '#wpshop_product_categorydiv'),
					             'product_datas_configuration' => array( 'edge' => 'bottom', 'at' => 'center top', 'my' => 'bottom right', 'pointer_id' => '#wpshop_product_fixed_tab'),
								 'product_display_configuration' => array('edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '.wpshop_product_data_display_tab' ),
								 'product_variations' => array('edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '#wpshop_new_variation_list_button' ),
								 'product_variations_configuration' => array('edge' => 'bottom', 'at' => 'right bottom', 'my' => 'bottom', 'pointer_id' => '#wpshop_variation_parameters_button' ),
								 'add_product_automaticly_to_cart' => array('edge' => 'right',  'at' => '', 'my' => '', 'pointer_id' => '#wpshop_product_options' ),
								 'category_filterable_attributes' => array('edge' => 'bottom', 'at' => 'top', 'my' => 'bottom', 'pointer_id' => '.filterable_attributes_container' ),
								 'category_picture' => array('edge' => 'bottom', 'at' => '', 'my' => 'bottom', 'pointer_id' => '.category_new_picture_upload' ),
								 'order_customer_comment' => array('edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_customer_comment' ),
								 'message_historic' => array('edge' => 'bottom', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_message_histo' ), 
								 'order_notification_message' => array('edge' => 'bottom', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_private_comments' ),
								 'order_shipping_box' => array('edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wpshop_order_shipping' ),
								 'options_payment_part' => array('edge' => 'right', 'at' => '', 'my' => '', 'pointer_id' => '#wps_payment_mode_list_container' )
 								);
			/** Get help data seen by user **/
			$closed_help_window = get_user_meta( get_current_user_id(), '_wps_closed_help', true);
			foreach( $help_cases as $help_id => $help_case ) {
				if ( empty($closed_help_window) || ( !empty($closed_help_window) && !array_key_exists( $help_id, $closed_help_window)) ) {
					switch( $help_id ) {
						case 'dashboard_help' : 
							$pointer_content  = '<h3>' .__( 'Customers information download', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can download emails of customers who accept to receive your commercials offers or your partners commercials offers by newsletter', 'wpshop'). '</p>';
						break;
						
						case 'product_page_categories' :
							$pointer_content  = '<h3>' .__( 'WPShop Categories', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can classify your products by category.', 'wpshop'). '<br/></p>';
							$pointer_content .= '<p><a href="' .admin_url('edit-tags.php?taxonomy=wpshop_product_category&post_type=wpshop_product'). '" class="button-primary" target="_blank">' .__('Create my WPShop categories', 'wpshop' ). '</a></p>';
						break;
						
						case 'product_datas_configuration' : 
							$pointer_content  = '<h3>' .__( 'Product configurations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can configure your product (Price, weight, reference and all attributes you want to create and affect to products', 'wpshop'). '</p>';
						break;
						
						case 'product_display_configuration' :
							$pointer_content  = '<h3>' .__( 'Product display configurations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage what elements you want to display on your product page', 'wpshop' ). '</p>';
						break;
						
						case 'product_variations' :
							$pointer_content  = '<h3>' .__( 'Product variations', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can generate your product variations.', 'wpshop'). '</p><br/>';
							$pointer_content .= '<p><a href="http://www.wpshop.fr/documentations/configurer-un-produit-avec-des-options/" class="button-primary" target="_blank">' .__('Read the tutorial', 'wpshop').'</a></p>';
						break;
						case 'product_variations_configuration' :
							$pointer_content  = '<h3>' .__( 'Variations configuration', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage your product variations configurations (Display "Price from", variation price priority...).', 'wpshop'). '</p>';
						break;
						case 'add_product_automaticly_to_cart' : 
							$pointer_content  = '<h3>' .__('Add product to cart', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'If you check this checkbox, this produc will be add automaticly to cart. This functionnality is helpful if you want to bill fees for example.', 'wpshop' ). '</p>';
						break;
						case 'category_filterable_attributes' : 
							$pointer_content  = '<h3>' .__('Filterable search', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a filter search to your WPShop, here will be display all available attributes for a filter search in this category', 'wpshop' ). '</p><br/>';
							$pointer_content .= '<p><a href="http://www.wpshop.fr/documentations/la-recherche-par-filtre/" class="button-primary" target="_blank">' .__('Read the filter search tutorial', 'wpshop').'</a></p>';
						break;
						case 'category_picture' : 
							$pointer_content  = '<h3>' .__('Category image', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a picture to illustrate your category', 'wpshop' ). '</p>';
						break;
						case 'order_customer_comment' : 
							$pointer_content  = '<h3>' .__('Order customer comment', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here is displayed the customer comment wrote during the order', 'wpshop' ). '</p>';
						break;
						case 'message_historic' : 
							$pointer_content  = '<h3>' .__('Message Historic', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'This is this message historic. You can check here if an automatic e-mail was send to a customer', 'wpshop' ). '</p>';
						break;
						case 'order_notification_message' : 
							$pointer_content  = '<h3>' .__('Order notification', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can add a private comment to the order or send an comment to customer about this order', 'wpshop' ). '</p>';
						break;
						case 'order_shipping_box' :
							$pointer_content  = '<h3>' .__('Shipping actions', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'Here, you can manage your shipping actions, download the shipping slip.', 'wpshop' ). '</p>';
						break;
						case 'options_payment_part' : 
							$pointer_content  = '<h3>' .__('Payment configuration', 'wpshop'). '</h3>';
							$pointer_content .= '<p>' .__( 'You can manage your payment methods (Change name, add description, add a logo and apply configurations ). You can add others payment methods', 'wpshop' ). '</p><br/>';
							$pointer_content .= '<p><a href="http://www.wpshop.fr/shop-theme/" class="button-primary" target="_blank">' .__('See available payment methods', 'wpshop').'</a></p>';
						break;
						default : 
							$pointer_content = '';
						break;
						
					}
					
					?>
					<script type="text/javascript">
					   //<![CDATA[
					   jQuery(document).ready( function($) {
					    $('<?php echo $help_case['pointer_id']; ?>').pointer({
					        content: '<?php echo $pointer_content; ?>',
					        position : { 
						        edge : '<?php echo $help_case['edge']; ?>',
								at : '<?php echo $help_case['at']; ?>',
								my : '<?php echo $help_case['my']; ?>'
					        }, 
					        
					        close: function() {
					        	var data = {
										action: "close_wps_help_window",
										pointer_id : '<?php echo $help_id; ?>'
										
									};
									jQuery.post(ajaxurl, data, function(response) {
										if ( response['status'] ) {
											
										}
										
									}, 'json');
					        }
					      }).pointer('open');
					   });
					   //]]>
					</script>
					
					
					<?php
				}
				
			}
			
			if ( empty($closed_help_window) || ( !empty($closed_help_window) && !array_key_exists('download_newsletter_contacts', $closed_help_window)) ) {
				$pointer_content = '<h3>' .__( 'Customers information download', 'wpshop'). '</h3>';
				$pointer_content .= '<p>' .__( 'You can download emails of customers who accept to receive your commercials offers or your partners commercials offers by newsletter', 'wpshop'). '</p>';
				
				?>
				   <script type="text/javascript">
				   //<![CDATA[
				   jQuery(document).ready( function($) {
				    $('#download_newsletter_contacts').pointer({
				        content: '<?php echo $pointer_content; ?>',
				        position : { 
							edge : 'left',
							align : 40
				        }, 
				        
				        close: function() {
				        	var data = {
									action: "close_wps_help_window",
									pointer_id : 'download_newsletter_contacts'
									
								};
								jQuery.post(ajaxurl, data, function(response) {
									if ( response['status'] ) {
										
									}
									
								}, 'json');
				        }
				      }).pointer('open');
				   });
	
					
				   
				   //]]>
				   </script>
				<?php
			}
		}
	
	
		function wps_ajax_close_wps_help_window() {
			$status = false; $result = '';
			$pointer_id = !empty( $_POST['pointer_id']) ? wpshop_tools::varSanitizer( $_POST['pointer_id'] ) : '';
			if ( !empty($pointer_id) ) {
				$seen_help_windows = get_user_meta( get_current_user_id(), '_wps_closed_help', true);
				$seen_help_windows[ $pointer_id ] = true;
					
				update_user_meta( get_current_user_id(), '_wps_closed_help', $seen_help_windows);
				$status = true;
			}
			$response = array( 'status' => $status, 'response' => $result );
			echo json_encode( $response );
			die();
		}
	
	}
}
if ( class_exists("wps_help") ) {
	$wps_help = new wps_help();
}