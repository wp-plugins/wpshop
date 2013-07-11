<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Products management method file
*
* This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_orders {

	/**
	 * Create a new custom post type in wordpress for current element
	 */
	function create_orders_type( ) {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_ORDER, array(
			'labels' => array(
				'name' 					=> __('Orders', 'wpshop'),
				'singular_name' 		=> __('Order', 'wpshop'),
				'add_new' 				=> __('Add quotation', 'wpshop'),
				'add_new_item' 			=> __('Add new quotation', 'wpshop'),
				'edit' 					=> __('Edit', 'wpshop'),
				'edit_item' 			=> __('Edit Order', 'wpshop'),
				'new_item' 				=> __('New quotation', 'wpshop'),
				'view' 					=> __('View Order', 'wpshop'),
				'view_item' 			=> __('View Order', 'wpshop'),
				'search_items' 			=> __('Search Orders', 'wpshop'),
				'not_found' 			=> __('No Orders found', 'wpshop'),
				'not_found_in_trash' 	=> __('No Orders found in trash', 'wpshop'),
				'parent' 				=> __('Parent Orders', 'wpshop')
			),
			'description' 			=> __('This is where store orders are stored.', 'wpshop'),
			'public' 				=> true,
			'show_ui' 				=> true,
			'capability_type' 		=> 'post',
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_in_menu' 			=> true,
			'hierarchical' 			=> false,
			'show_in_nav_menus' 	=> false,
			'rewrite' 				=> false,
			'query_var' 			=> true,
			'supports' 				=> array('title'),
			'has_archive' 			=> false
		));
	}

	/**
	 *	Call the different boxes in edition page
	 */
	function add_meta_boxes( ) {
		global $post;

		/**	Add action button	*/
		add_meta_box(
			'wpshop_order_actions',
			__('Actions on order', 'wpshop'),
			array('wpshop_orders', 'order_actions'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);

		/**	Box with order customer information	*/
		add_meta_box(
			'wpshop_order_customer_information_box',
			__('Customer information', 'wpshop'),
			array('wpshop_orders', 'order_customer_information'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		/**	Box with the complete order content	*/
		add_meta_box(
			'wpshop_order_content',
			__('Order content', 'wpshop'),
			array('wpshop_orders', 'order_content'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		/**	Box for order message history	*/
		add_meta_box(
			'wpshop_order_private_comments',
			__('Comments', 'wpshop'),
			array('wpshop_orders', 'order_private_comments'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		/**	Box for payment information	*/
		add_meta_box(
			'wpshop_order_payment',
			__('Order payment', 'wpshop'),
			array('wpshop_orders', 'order_payment_box'),
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);

		/**	Box for shipping information	*/
		$shipping_option = get_option('wpshop_shipping_address_choice');
		if (!empty($shipping_option['activate']) && $shipping_option['activate']) {
			add_meta_box(
				'wpshop_order_shipping',
				__('Shipping', 'wpshop'),
				array('wpshop_orders', 'order_shipping_box'),
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low'
			);
		}

		/**	Box	containing listing of customer notification */
		$notifs = self::get_notification_by_object( array('object_type' => 'order', 'object_id' => $post->ID) );
		if ( !empty($notifs) ) {
			add_meta_box(
				'wpshop_order_customer_notification',
				__('Customer Notification', 'wpshop'),
				array('wpshop_orders', 'wpshop_order_customer_notification'),
					WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'low'
			);
		}
	}

	/**
	 * Define the box for actions on order
	 *
	 * @param object $order The current order being edited
	 */
	function order_actions( $order ) {
		$output = '';

		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);

		$tpl_component = array();

		$delete_button = wpshop_display::display_template_element('wpshop_admin_order_action_del_button', array('ADMIN_ORDER_DELETE_LINK' => esc_url( get_delete_post_link($order->ID) ) , 'ADMIN_ORDER_DELETE_TEXT' => (!EMPTY_TRASH_DAYS ? __('Delete Permanently', 'wpshop') :  __('Move to Trash', 'wpshop'))), array(), 'admin');
		$tpl_component['ADMIN_ORDER_DELETE_ORDER'] = current_user_can( "delete_post", $order->ID ) ? $delete_button : '';

		/**	Add an action list	*/
		$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] = '';

		/**	Display main information about the order	*/
		$order_main_info = '';
		if(!empty($order_postmeta['order_date'])){
			$order_main_info .=  __('Order date','wpshop').': <strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_date'], true).'</strong><br />';
		}
		if(empty($order_postmeta['order_date']) || (empty($order_postmeta['order_key']) && empty($order_postmeta['order_temporary_key']) && empty($order_postmeta['order_invoice_ref']))){
			$order_main_info .=  __('Temporary quotation reference','wpshop').': <strong>'.self::get_new_pre_order_reference(false).'</strong><br />';
		}
		else{
			if(!empty($order_postmeta['order_key'])){
				$order_main_info .=  __('Order reference','wpshop').': <strong>'.$order_postmeta['order_key'].'</strong><br />';
			}
			if(!empty($order_postmeta['order_temporary_key'])){
				$order_main_info .=  __('Pre-order reference','wpshop').': <strong>'.$order_postmeta['order_temporary_key'].'</strong><br />';
				if ( empty($order_postmeta['order_key']) ) {
					$order_main_info .= '<a href="' .WPSHOP_TEMPLATES_URL . 'invoice.php?order_id=' . $_GET['post']. '&mode=pdf">' .__('Download the quotation', 'wpshop'). '</a><br />';
				}
			}
			if(!empty($order_postmeta['order_invoice_ref'])){
				$sub_tpl_component = array();
				$sub_tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_INVOICE_REF'] = $order_postmeta['order_invoice_ref'];
				$sub_tpl_component['ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES'] = '';
				$sub_tpl_component['ADMIN_ORDER_INVOICE_DOWNLOAD_LINK'] = WPSHOP_TEMPLATES_URL . 'invoice.php?order_id=' . $order->ID;
				$order_invoice_download = wpshop_display::display_template_element('wpshop_admin_order_payment_received_invoice_download_links', $sub_tpl_component, array(), 'admin');
				$order_main_info .=  __('Invoice number','wpshop').': <strong>'.$order_postmeta['order_invoice_ref'].'</strong> ' . $order_invoice_download . '<br />';
			}
			else {
				$order_main_info .= wpshop_display::display_template_element('wpshop_admin_order_generate_invoice_button', array(), array(), 'admin');
			}
		}
		$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<li class="wpshop_order_main_information" >' . $order_main_info . '</li>';

		/*Add the current order status in display**/
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= ( !empty($order_postmeta['order_status']) ) ? (sprintf('<li class="order_status_' . $order->ID . ' wpshop_order_status_container wpshop_order_status_%1$s ">%2$s</li>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) ) : '';

		/**	Add a box allowing to notify the customer on order update	*/
		/**
		 *
		 * To check because notification is not really send
		 *
		 */
		if ( !empty($order->post_author) ) {
			$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '
			<li class="wpshop_order_notify_customer_on_update_container" >
				<input type="checkbox" name="notif_the_customer" id="wpshop_order_notif_the_customer_on_update" /> <label for="wpshop_order_notif_the_customer_on_update" >'.__('Send a notification to the customer', 'wpshop').'</label>
				<!-- <br/><input type="checkbox" name="notif_the_customer_sendsms" id="wpshop_order_notif_the_customer_sendsms_on_update" /> <label for="wpshop_order_nnotif_the_customer_sendsms_on_update" >'.__('Send a SMS to the customer', 'wpshop').'</label> -->
			</li>';
		}

		/*Add the button regarding the order status**/
		if ( !empty($order_postmeta['order_status']) ) {
			switch ( $order_postmeta['order_status'] ) {
				case 'awaiting_payment':
					$tpl_component['ADMIN_ORDER_ACTIONS_LIST'] .= '<li><button class="button markAsCanceled order_'.$order->ID.'" >'.__('Cancel this order', 'wpshop').'</button><input type="hidden" id="markascanceled_order_hidden_indicator" name="markascanceled_order_hidden_indicator" /></li>';
				break;
			}
		}
		echo wpshop_display::display_template_element('wpshop_admin_order_action_box', $tpl_component, array('type' => WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'id' => $order->ID), 'admin');
	}

	/**
	 * Define the box for order payment management
	 *
	 * @param object $order The current order being edited
	 */
	function order_payment_box( $order ) {
		$output = '';

		$order_status = unserialize(WPSHOP_ORDER_STATUS);
		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);

		if(!empty($_GET['download_invoice'])) {
			$pdf = new wpshop_export_pdf();
			$pdf->invoice_export( $_GET['download_invoice'], $_GET['invoice']);
		}

		$tpl_component = array();
		/**	Fill the template array with the complete order content. EXCEPT ITEMS	*/
		if ( !empty($order_postmeta) ) {
			foreach ( $order_postmeta as $meta_key => $meta_value ) {
				if ( !is_array($meta_value) ) {
					$tpl_component['ORDER_' . strtoupper($meta_key)] = $meta_value;
					if ( strpos($meta_key, 'total') || strpos($meta_key, 'amount') || strpos($meta_key, 'cost') ) {
						$tpl_component['ORDER_' . strtoupper($meta_key)] = wpshop_display::format_field_output('wpshop_product_price', $meta_value, $order_postmeta['order_total_ht']);
					}
				}
			}
		}

		$tpl_component['ORDER_TOTAL_AMOUNT_HT'] = ( !empty($order_postmeta['order_total_ht']) ) ? wpshop_display::format_field_output('wpshop_product_price', $order_postmeta['order_total_ht']) : null;
		$tpl_component['ORDER_TOTAL_AMOUNT_TTC'] = ( !empty($order_postmeta['order_grand_total']) ) ? wpshop_display::format_field_output('wpshop_product_price', $order_postmeta['order_grand_total']) : null;


		/**	Check if payment information exist into order array	*/
		$tpl_component['ADMIN_ORDER_CUSTOMER_CHOICE'] = '';
		$tpl_component['ADMIN_ORDER_PAYMENT_REST'] = '';
		$tpl_component['ADMIN_ORDER_PAYMENT_LIST'] = '';
		if ( !empty($order_postmeta['order_payment']) ) {
			/**	Customer choice for payment	*/
			$sub_tpl_component = array();
			$sub_tpl_component['ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_CLASSES'] = ' wpshop_admin_order_no_choice_made';
			$sub_tpl_component['ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES'] = __("Customer does not choose any payment method", 'wpshop');
// 			$sub_tpl_component['ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_METHOD'] = '';
			if (!empty($order_postmeta['order_payment']['customer_choice'])) {
				$sub_tpl_component['ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_CLASSES'] = ' wpshop_admin_order_choice_is_made';

				foreach ( $order_postmeta['order_payment']['customer_choice'] as $choice_key => $choice_value ) {
					$sub_tpl_component['ADMIN_ORDER_CUSTOMER_PAYMENT_CHOICES_' . strtoupper($choice_key)] = __($choice_value, 'wpshop');
				}
			}
			$tpl_component['ADMIN_ORDER_CUSTOMER_CHOICE'] .= wpshop_display::display_template_element('wpshop_admin_order_customer_choices', $sub_tpl_component, array(), 'admin');

			$payment_list = wpshop_payment::display_payment_list( $order->ID, $order_postmeta );
			$tpl_component['ADMIN_ORDER_PAYMENT_LIST'] = $payment_list[0];
			$waited_amount_sum = $payment_list[1];
			$received_amount_sum = $payment_list[2];

			/**	Check the due amount for this order	*/
			$sub_tpl_component = array();
			$waited_minus_received = $waited_amount_sum - $received_amount_sum;
			$sub_tpl_component['ADMIN_ORDER_WAITED_AMOUNT'] = $waited_amount_sum;
			$sub_tpl_component['ADMIN_ORDER_RECEIVED_AMOUNT'] = $received_amount_sum;
			$order_grand_total_minus_received = (!empty( $order_postmeta['order_grand_total'])) ? ($order_postmeta['order_grand_total'] - $received_amount_sum) : null;
			$order_grand_total_minus_received = number_format($order_grand_total_minus_received, 2, '.', '');

			$sub_tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_DUE_AMOUNT'] = $order_grand_total_minus_received;
			$tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_DUE_AMOUNT'] = $order_grand_total_minus_received;

			if ( $order_grand_total_minus_received <= 0 ) {
				$sub_tpl_component['ADMIN_ORDER_PAYMENT_REST_CLASSES'] = ' wpshop_admin_order_payment_box_payment_rest_nothing_due';
			}
			else {
				$sub_tpl_component['ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES'] = '';

				$active_payment_method = get_option('wpshop_paymentMethod');
				$no_payment_method_activ = false;
				$payment_method_list = array();
				if ( !empty($active_payment_method) ) {
					unset($active_payment_method['display_position']);
					unset($active_payment_method['default_method']);

					foreach ($active_payment_method as $payment_method_identifier => $payment_method_state) {
						if ( $payment_method_state ) {
							$payment_method_list[$payment_method_identifier] = __($payment_method_identifier, 'wpshop');
							$no_payment_method_activ = true;
						}
					}
				}

				$sub_tpl_component_new = array();
				$input_def = array();
				$input_def['id'] = 'wpshop_admin_order_payment_method_chooser';
				$input_def['name'] = 'wpshop_admin_order_payment_received[method]';
				$input_def['option'] = ' class="wpshop_admin_order_arrived_payment_method_choice wpshop_admin_order_new_payment_received_input" ';
				$input_def['possible_value'] = $payment_method_list;
				$input_def['type'] = 'select';
				$input_def['value'] = !empty($order_postmeta['order_payment']['customer_choice']['method']) ? $order_postmeta['order_payment']['customer_choice']['method'] : '';
				$input_def['valueToPut'] = 'index';
				$sub_tpl_component_new['ADMIN_ORDER_REVEICED_PAYMENT_METHOD_CHOOSER'] = wpshop_form::check_input_type($input_def);
				$sub_tpl_component_new['ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES'] = '';

				$sub_tpl_component_new['ADMIN_ORDER_RECEIVED_PAYMENT_UNSTYLED_WAITED_AMOUNT'] = $order_grand_total_minus_received;


				if ( $no_payment_method_activ ) {
					$tpl_part = 'wpshop_admin_order_waiting_payment';
				}
				else {
					$tpl_part = 'wpshop_admin_order_waiting_payment_no_method_set';
				}
				$tpl_component['ADMIN_ORDER_PAYMENT_LIST'] .= wpshop_display::display_template_element($tpl_part, $sub_tpl_component_new, array(), 'admin');

				$sub_tpl_component['ADMIN_ORDER_PAYMENT_REST_CLASSES'] = ' wpshop_admin_order_payment_box_payment_rest_missing_payment ';
			}

			$tpl_component['ADMIN_ORDER_PAYMENT_REST'] = wpshop_display::display_template_element( 'wpshop_admin_order_payment_rest', $sub_tpl_component, array(), 'admin');
			unset($sub_tpl_component);
		}
		else {
			$tpl_component['ADMIN_ORDER_CUSTOMER_CHOICE'] .= '<li class="wpshop_order_nothing_for_payment" >' . __('No information available for this order payment', 'wpshop') . '</li>';
			$tpl_component['ADMIN_ORDER_PAYMENT_LIST'] .= '';
		}

		$output .= wpshop_display::display_template_element('wpshop_admin_order_payment', $tpl_component, array(), 'admin');
		unset($tpl_component);

		echo $output;
	}

	/**
	 * Display a box allowing to add information about shipping for an order
	 *
	 * @param object $order The current order being edited
	 */
	function order_shipping_box( $order ) {
		$box_content = '';

		$order_postmeta = get_post_meta($order->ID, '_order_postmeta', true);

		if ( !empty($order_postmeta['order_status']) && $order_postmeta['order_status'] != 'shipped' ) {
			$box_content .= '<p><a class="button markAsShipped order_'.$order->ID.'">'.__('Mark as shipped', 'wpshop').'</a></p>';
		}
		else {
			$box_content .= __('Order shipping date','wpshop').': '.(empty($order_postmeta['order_shipping_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_shipping_date'],true).'</strong>').'<br />';
			if ( !empty($order_postmeta['order_trackingNumber']) ) {
				$box_content .= __('Tracking number','wpshop').': '.$order_postmeta['order_trackingNumber'].'<br /><br />';
			}
		}

		if ( !empty($order_postmeta['order_invoice_ref']) ) {
			$box_content .= '<a href="' .WPSHOP_TEMPLATES_URL . 'invoice.php?order_id=' . $order->ID . '&invoice_ref=' . $order_postmeta['order_invoice_ref']. '&bon_colisage=ok&mode=pdf" class="button-secondary" >' .__('Download the product list', 'wpshop'). '</a>';
		}

		echo $box_content;
	}



	/**
	 * Display the order content: the list of element put into order
	 *
	 * @param order $post The complete order content
	 */
	function order_content( $post ) {
		$order_content = '';

		$order = get_post_meta($post->ID, '_order_postmeta', true);

		$order_content .= '<div id="product_chooser_dialog" title="' . __('Choose a new product to add to the current order', 'wpshop') . '" class="wpshopHide" ><div class="loading_picture_container" id="product_chooser_picture" ><img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." /></div><div id="product_chooser_container" class="wpshopHide" >&nbsp;</div></div>
<div id="order_product_container" class="order_product_container wpshop_cls" >';
		if($order){/*	Read the order content if the order has product	*/
			$order_content .= '<input type="hidden" value="" name="order_products_to_delete" id="order_products_to_delete" />' . wpshop_cart::display_cart(true, $order, 'admin');
			if (empty($order['order_invoice_ref'])) {
				$order_content .= '<div id="order_refresh_button_container" class="wpshop_clear_block" ><button class="button-primary alignright wpshopHide" id="wpshop_admin_order_recalculate" >' . __('Refresh order informations', 'wpshop') . '</button></div>';
			}
		}
		elseif(!isset($order['order_invoice_ref']) || ($order['order_invoice_ref'] == "")){
			$order_content .= '
	<input type="button" class="button-primary" id="order_new_product_add_opener" value="' . __('Add a product to the current order', 'wpshop') . '" />';
		}
		$order_content .= '
		<div class="wpshop_cls" ></div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".wpshop_order_product_listing_line.order_product_qty input").keyup(function(){
			jQuery("#wpshop_admin_order_recalculate").show();
		});
		jQuery(".wpshop_order_product_listing_line.order_product_qty input").blur(function(){
			if((jQuery(this).val() == 0) || (jQuery(this).val() == "")){
				jQuery(this).closest("tr").children("td:last").children("a").click();
			}
			jQuery("#wpshop_admin_order_recalculate").show();
		});
		jQuery(".remove").click(function(){
			if(confirm(wpshopConvertAccentTojs("' . __('Are you sure that you want to delete this product from the order?', 'wpshop') . '"))){
				var product_id_to_delete = jQuery(this).closest("tr").attr("id").replace("product_", "");
				jQuery("#order_products_to_delete").val(jQuery("#order_products_to_delete").val().replace(product_id_to_delete + ",", "") + product_id_to_delete + ",");
				jQuery(this).closest("tr").remove();
				jQuery("#wpshop_admin_order_recalculate").show();
			}
		});
		jQuery("#wpshop_admin_order_recalculate").click( function(){
			jQuery("#order_refresh_button_container").html(jQuery(".loading_picture_container").html());
			jQuery("#order_refresh_button_container img").addClass("alignright");
			update_order_product_content("' . $post->ID . '", jQuery("#order_products_to_delete").val());
		});

		jQuery("#product_chooser_dialog").dialog({
			width:800,
			height:600,
			modal:true,
			autoOpen:false,
			resizable: false,
			dialogClass: "wpshop_uidialog_box",
			close:function(){
				jQuery("#product_chooser_picture").show();
				jQuery("#product_chooser_container").hide();
			},
			buttons:{
				"assign-product-to-order" : {
					text : "' . __('Add selected product to order', 'wpshop') . '",
					click: function(){
						jQuery("#wpshop_order_selector_product_form").submit();
					},
					class: "button-primary",
				}
			}
		});

		jQuery("#order_new_product_add_opener").click( function(){
			if(jQuery("#wpshop_admin_order_recalculate").is(":visible")){
				update_order_product_content("' . $post->ID . '", jQuery("#order_products_to_delete").val());
			}
			jQuery("#product_chooser_container").load("' . WPSHOP_AJAX_FILE_URL . '",{
				"post":true,
				"elementCode":"ajax_load_product_list",
				"order_id":"' . $post->ID . '"
			});
			jQuery("#product_chooser_dialog").dialog("open");
		});

		jQuery("#free_shipping_for_order").click(function(){
			jQuery("#order_refresh_button_container").html(jQuery(".loading_picture_container").html());
			jQuery("#order_refresh_button_container img").addClass("alignright");
			if(!jQuery(this).is(":checked")){
				jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
					"post":"true",
					"elementCode":"ajax_refresh_order",
					"action":"unset_shipping_to_free",
					"elementIdentifier":"' . $post->ID . '"
				});
			}
			else{
				jQuery("#order_product_container").load(WPSHOP_AJAX_FILE_URL,{
					"post":"true",
					"elementCode":"ajax_refresh_order",
					"action":"set_shipping_to_free",
					"elementIdentifier":"' . $post->ID . '"
				});
			}
		});
	});
</script>';

		echo $order_content;
	}

	/**
	 * Display the box with information about order's customer
	 *
	 * @param object $post The current order main informations -> Post information
	 * @param array $params Extra parameters
	 */
	function order_customer_information( $post, $params ) {
		global $customer_obj;
		global $wpshop_account;
		$user_order_box_content = '';

		$order_postmeta = get_post_meta($post->ID, '_order_postmeta', true);
		$order_info = get_post_meta($post->ID, '_order_info', true);

		$billing = !empty($order_info['billing']) ? $order_info['billing'] : '';
		$shipping = !empty($order_info['shipping']) ? $order_info['shipping'] : '';

		$choosen_billing_address = get_option('wpshop_billing_address');
		$billing_address = !empty($billing['id']) ? $billing['id'] : $choosen_billing_address['choice'];
		$shipping_option = get_option('wpshop_shipping_address_choice');
		$shipping_address = !empty($shipping['id']) ? $shipping['id'] : $shipping_option['choice'];

		$user_id = 0;
		if ( !empty( $order_postmeta['customer_id'] ) ) {
			$user_id = $order_postmeta['customer_id'];
			$user_info = get_userdata($order_postmeta['customer_id']);
			if ( !$billing || !empty( $params['force_changing'] ) ) {
				$billing = $user_info->billing_info;
			}
			if ( !$shipping || !empty($params['force_changing'] ) ) {
				$shipping = $user_info->shipping_info;
			}
		}
		else {
			$user_id = get_post_meta($post->ID, '_wpshop_order_customer_id', true);
		}
		
		echo '<input type="hidden" name="input_wpshop_order_customer_adress_load" id="input_wpshop_order_customer_adress_load" value="' . wp_create_nonce("wpshop_order_customer_adress_load") . '" />';
		echo '<div class="wpshop_order_customer_container wpshop_order_customer_container_user_information wpshop_order_customer_container_user_information_chooser" id="wpshop_order_customer_chooser">
			<p><label>'.__('Customer','wpshop').'</label></p>
				' . $customer_obj->custom_user_list(array('name'=>'user[customer_id]', 'id'=>'wpshop_order_user_customer_id'), (!empty($user_id) ? $user_id : ''), false, ( empty($order_postmeta['order_invoice_ref'])  ) ? false : true ) . '';
		if ( empty($order_postmeta['order_invoice_ref']) ) {
			echo '<br/><input type="button" class="button-primary" id="create_new_customer" value="' .__('Create a new customer', 'wpshop'). '"/>';
		}

		if ( !empty($post->post_parent) ) {
			echo '<div id="customer_account_information"><h2>' . __('User information', 'wpshop') . '</h2>' . $wpshop_account->display_account_information( $post->post_parent ) . '</div>';
		}

		echo '</div>';
		echo '<input type="hidden" name="wpshop_customer_id" id="wpshop_customer_id" value="0" />';
		echo '<div class="wpshop_order_customer_container wpshop_order_customer_container_user_information">';
		echo '<div id="customer_address_form">';

		if ( !empty($order_postmeta['order_invoice_ref']) ) {
			echo $wpshop_account->get_addresses_by_type( $billing_address, __('Billing address', 'wpshop'), array('only_display' => 'yes'));
		}
		else {
			echo '<p>'. __('Choose a customer in the list or create an new customer', 'wpshop').'</p>';
		}
		echo '</div>';
		echo '</div>';
		self::create_new_customer_interface();
		if (!empty($shipping_option['activate']) && $shipping_option['activate']) {
			echo '<div id="shipping_infos_bloc" class="wpshop_order_customer_container wpshop_order_customer_container_user_information">';
			if ( !empty($order_postmeta['order_status']) && in_array($order_postmeta['order_status'], array('completed', 'shipped')) ) {

				$tpl_component['ADDRESS_COMBOBOX'] = '';
				$tpl_component['ADDRESS_BUTTONS'] = '';
				$tpl_component['CUSTOMER_ADDRESS_TYPE_TITLE'] = __('Shipping address', 'wpshop');
				$address_fields = wpshop_address::get_addresss_form_fields_by_type($shipping_option['choice']);
				$tpl_component['CUSTOMER_ADDRESS_CONTENT'] = $wpshop_account->display_an_address( $address_fields, $order_info['shipping']['address']);
				$tpl_component['CUSTOMER_CHOOSEN_ADDRESS'] = wpshop_display::display_template_element('display_address_container', $tpl_component);
				echo wpshop_display::display_template_element('display_addresses_by_type_container', $tpl_component);
				unset( $tpl_component );
			}
			echo '</div>';
		}
		echo '<div class="wpshop_cls"></div>';
	}

	function create_new_customer_interface () {
		$output  = '<div id="create_new_customer_dialog" title="' . __('Create a new customer', 'wpshop') . '" >';
		$output .= '<div class="loading_picture_container" id="create_new_customer_picture" ><img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." /></div><div id="create_new_customer_in_admin_reponseBox"></div><div id="create_new_customer_container" >&nbsp;</div>';
		$output .= '</div>';

		echo $output;
	}


	function wpshop_order_customer_notification( $order ) {
		$output = '';

		$notifs = self::get_notification_by_object( array('object_type' => 'order', 'object_id' => $order->ID) );
		foreach ($notifs as $n) {
			$tpl_component['UPDATE_ORDER_MESSAGE_DATE'] = '';
			foreach ( $n['mess_dispatch_date'] as $date_message) {
				$tpl_component['UPDATE_ORDER_MESSAGE_DATE'] .= $date_message. ', ';
			}
			$tpl_component['UPDATE_ORDER_MESSAGE'] = $n['mess_message'];
			$output .= wpshop_display::display_template_element('wpshop_admin_order_customer_notification_item', $tpl_component);
			unset($tpl_component);
		}

		return $output;
	}

	/** Generate the billing reference regarding the order $order_id
	 * @return void
	*/
	function order_generate_billing_number($order_id, $force_invoicing = false){
		global $wpdb, $wpshop_modules_billing;

		// Get the order from the db
		$order = get_post_meta($order_id, '_order_postmeta', true);

		// If the payment is completed
		if(($order['order_status']=='completed') || $force_invoicing) {

			// If the reference hasn't been generated yet
			if(empty($order['order_invoice_ref'])) {
				$order['order_invoice_ref'] = $wpshop_modules_billing->generate_invoice_number( $order_id );

				update_post_meta($order_id, '_order_postmeta', $order);
			}
		}
	}

	/**
	 *	Save the order when clicking on save button
	 */
	function save_order_custom_informations() {
		global $wpshop_account, $wpdb, $wpshop_payment;
		if ( !empty($_REQUEST['post_ID']) && (get_post_type($_REQUEST['post_ID']) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER) && empty($_POST['edit_other_thing']) ) {

			$update_order_billing_and_shipping_infos = false;
			$order_info = array();
			$user_id = 0;
			if ( !empty( $_REQUEST['attribute']) ) {
				if ( is_admin() ) {
					$user_id = 	$_REQUEST['wpshop_customer_id'];
				}
				else {
					$user_id = get_current_user_id();
				}
				$_REQUEST['user']['customer_id'] = $user_id;


				$billing_set_infos = get_option('wpshop_billing_address');
				$shipping_set_infos = get_option('wpshop_shipping_address_choice');

				foreach ( $_REQUEST['attribute'] as $address_attribute_set_id => $address_detail_per_type ) {
					$stored_address = array();

						foreach ( $address_detail_per_type as $address_detail ) {
							if ( is_array ($address_detail) ) {
								$stored_address = array_merge($stored_address, $address_detail);
							}
						}
						if ( $address_attribute_set_id == $billing_set_infos['choice'] ) {
							$adress_type = 'billing';
						}
						else if ( $address_attribute_set_id == $shipping_set_infos['choice'] ) {
							$adress_type = 'shipping';
						}
						if ( $adress_type == 'billing' ) {
							$order_info[$adress_type]['id'] = $billing_set_infos['choice'];
						}
						else {
							$order_info[$adress_type]['id'] = $shipping_set_infos['choice'];
						}
						$order_info[$adress_type]['address'] = $stored_address;
						$update_order_billing_and_shipping_infos = true;

						$billing_info = get_user_meta($user_id, $adress_type . '_info', true);
						if ( empty( $billing_info ) ) {
							update_user_meta($user_id, $adress_type . '_info', $stored_address);
						}

				}

			}

			if($update_order_billing_and_shipping_infos) {
				update_post_meta($_REQUEST['post_ID'], '_order_info', $order_info);
				if ( !empty($_POST['billing_address']) ) {
					$wpshop_account->treat_forms_infos( $_REQUEST['billing_address'] );
				}
				if( !empty($_POST['shipping_address']) ) {
					$wpshop_account->treat_forms_infos( $_REQUEST['shipping_address'] );
				}
			}
			/**	Update order payment list	*/
			if ( !empty($_REQUEST['wpshop_admin_order_payment_received']) && !empty($_REQUEST['wpshop_admin_order_payment_received']['method'])
						&& !empty($_REQUEST['wpshop_admin_order_payment_received']['date']) && !empty($_REQUEST['wpshop_admin_order_payment_received']['received_amount']) && ( $_REQUEST['action_triggered_from'] == 'add_payment' || !empty($_REQUEST['wpshop_admin_order_payment_reference']) ) ) {
				$received_payment_amount = $_REQUEST['wpshop_admin_order_payment_received']['received_amount'];

				$params_array = array(
					'method' 			=> $_REQUEST['wpshop_admin_order_payment_received']['method'],
					'waited_amount' 	=> $received_payment_amount,
					'status' 			=> 'payment_received',
					'author' 			=> ( is_admin() && !empty($user_id) ) ? $user_id : get_current_user_id(),
					'payment_reference' => $_REQUEST['wpshop_admin_order_payment_received']['payment_reference'],
					'date' 				=> current_time('mysql', 0),
					'received_amount' 	=> $received_payment_amount
				);
				wpshop_payment::check_order_payment_total_amount($_REQUEST['post_ID'], $params_array, 'completed');
			}

			if ( is_admin() ) {
				$wpdb->update($wpdb->posts, array('post_parent' => $user_id, 'post_status' => 'publish'),  array('ID' => $_REQUEST['post_ID']) );
				$order_postmeta = get_post_meta($_REQUEST['post_ID'], '_order_postmeta', true);
				if ( empty( $order_postmeta['order_payment'] ) ) {
					$order_postmeta['order_payment']['customer_choice']['method'] = '';
					$order_postmeta['order_payment']['received'][] = array('waited_amount' => number_format($order_postmeta['order_grand_total'],2,'.', '') );
					update_post_meta($_REQUEST['post_ID'], '_order_postmeta', $order_postmeta);
				}
				if ( !empty($user_id) ) {
					update_post_meta($_REQUEST['post_ID'], '_wpshop_order_customer_id', $user_id);
				}
			}

			/*	Get order current content	*/
			$order_meta = get_post_meta(wpshop_tools::varSanitizer($_REQUEST['post_ID']), '_order_postmeta', true);
			
			/** If the order would be canceled **/
			if ( !empty($_REQUEST['markascanceled_order_hidden_indicator']) && wpshop_tools::varSanitizer($_REQUEST['markascanceled_order_hidden_indicator']) == 'canceled' ) {
				$order_meta['order_status'] = 'canceled';
				update_post_meta(wpshop_tools::varSanitizer($_REQUEST['post_ID']), '_order_postmeta', $order_meta);
			}
			
			if(empty($order_meta['customer_id']) ) {
				$order_meta['customer_id'] = $user_id;
			}
			if ( !empty ($_REQUEST['action_triggered_from']) && $_REQUEST['action_triggered_from'] == 'generate_invoice') {
				$order_meta['order_invoice_ref'] = wpshop_modules_billing::generate_invoice_number( $_REQUEST['post_ID'] );
			}
			// If the customer notification is checked
			if( !empty($_REQUEST['notif_the_customer']) && $_REQUEST['notif_the_customer']=='on' ) {
				/*	Get order current content	*/
				$user = get_post_meta($_REQUEST['post_ID'], '_order_info', true);
				$email = $user['billing']['address']['address_user_email'];
				$first_name = $user['billing']['address']['address_first_name'];
				$last_name = $user['billing']['address']['address_last_name'];

				$object = array('object_type'=>'order','object_id'=>$_REQUEST['post_ID']);
				/* Envoie du message de confirmation de commande au client	*/

				if ( empty( $order_meta['order_key'] ) ) {
					wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_QUOTATION_UPDATE_MESSAGE', array('order_id' => $object['object_id'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => current_time('mysql', 0), 'order_content' => '', 'order_addresses' => '', 'order_billing_address' => '', 'order_shipping_address' => ''));
				}
				else {
					wpshop_messages::wpshop_prepared_email(
					$email,
					'WPSHOP_ORDER_UPDATE_MESSAGE',
					array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'], 'order_billing_address' => '', 'order_shipping_address' => '', 'order_addresses' => '', 'order_addresses' => '', 'order_billing_address' => '', 'order_shipping_address' => '' ),
					$object);
				}
			}

			
			
			/*	Complete information about the order	*/
			if ( empty($order_meta['order_key']) ) {
				$order_meta['order_key'] = !empty($order_meta['order_key']) ? $order_meta['order_key'] : (!empty($order_meta['order_status']) && ($order_meta['order_status']!='awaiting_payment') ? wpshop_orders::get_new_order_reference() : '');
				$order_meta['order_temporary_key'] = (isset($order_meta['order_temporary_key']) && ($order_meta['order_temporary_key'] != '')) ? $order_meta['order_temporary_key'] : wpshop_orders::get_new_pre_order_reference();
			}
			$order_meta['order_status'] = (isset($order_meta['order_status']) && ($order_meta['order_status'] != '')) ? $order_meta['order_status'] : 'awaiting_payment';
			$order_meta['order_date'] = (isset($order_meta['order_date']) && ($order_meta['order_date'] != '')) ? $order_meta['order_date'] : current_time('mysql', 0);
			$order_meta['order_currency'] = wpshop_tools::wpshop_get_currency(true);
			/*	Update order content	*/

			/*	Set order information into post meta	*/
			update_post_meta($_REQUEST['post_ID'], '_order_postmeta', $order_meta);

			/* Update the others wpshop order post_meta */
			if ( !empty( $order_meta['customer_id'] ) ) {
				update_post_meta($_REQUEST['post_ID'], '_wpshop_order_customer_id', $order_meta['customer_id']);
			}
			update_post_meta($_REQUEST['post_ID'], '_wpshop_order_shipping_date', $order_meta['order_shipping_date']);
			update_post_meta($_REQUEST['post_ID'], '_wpshop_order_status', $order_meta['order_status']);
		}
	}


	/** Renvoi une nouvelle r�f�rence unique pour une commande
	* @return int
	*/
	function get_new_order_reference(){
		$number_figures = get_option('wpshop_order_number_figures', false);
		/* If the number doesn't exist, we create a default one */
		if(!$number_figures){
			$number_figures = 5;
			update_option('wpshop_order_number_figures', $number_figures);
		}

		$order_current_number = get_option('wpshop_order_current_number', false);
		/* If the counter doesn't exist, we initiate it */
		if(!$order_current_number) { $order_current_number = 1; }
		else { $order_current_number++; }
		update_option('wpshop_order_current_number', $order_current_number);

		$order_ref = (string)sprintf('%0'.$number_figures.'d', $order_current_number);
		return WPSHOP_ORDER_REFERENCE_PREFIX.$order_ref;
	}

	/** Renvoi une nouvelle r�f�rence unique pour un devis
	* @return int
	*/
	function get_new_pre_order_reference($save = true){
		$number_figures = get_option('wpshop_order_number_figures', false);
		/* If the number doesn't exist, we create a default one */
		if(!$number_figures){
			$number_figures = 5;
			update_option('wpshop_order_number_figures', $number_figures);
		}

		$order_current_number = get_option('wpshop_preorder_current_number', false);
		/* If the counter doesn't exist, we initiate it */
		if(!$order_current_number) { $order_current_number = 1; }
		else { $order_current_number++; }
		if($save){
			update_option('wpshop_preorder_current_number', $order_current_number);
		}

		$order_ref = (string)sprintf('%0'.$number_figures.'d', $order_current_number);
		return WPSHOP_PREORDER_REFERENCE_PREFIX.$order_ref;
	}


	/**
	*	Build an array with the different items to add to an order
	*
	*	@param array $products The item list to add to the order
	*
	*	@return array $item_list The item to add to order
	*/
	function add_product_to_order( $product ){
		$p = $product; wpshop_products::get_product_data($product['product_id']);
		/*	Read selected product list for adding to order	*/
		$price_infos = wpshop_prices::check_product_price( $p );
		$pu_ht = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ?  $price_infos['discount']['discount_et_price'] : $price_infos['et'];
		$pu_ttc = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ? $price_infos['discount']['discount_ati_price'] : $price_infos['ati'];
		$pu_tva = ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) && $price_infos['discount']['discount_exist']) ? $price_infos['discount']['discount_tva'] : $price_infos['tva'];
		$total_ht = $pu_ht*$product['product_qty'];
		$tva_total_amount = $pu_tva*$product['product_qty'];
		$total_ttc = $pu_ttc*$product['product_qty'];
		$tva = !empty($product[WPSHOP_PRODUCT_PRICE_TAX]) ? $product[WPSHOP_PRODUCT_PRICE_TAX] : null;

		$item_discount_type = $item_discount_value = $item_discount_amount = 0;


		$item = array(
			'item_id' => $product['product_id'],
			'item_ref' => !empty($product['product_reference']) ? $product['product_reference'] : null,
			'item_name' => !empty($product['product_name']) ? $product['product_name'] : 'wpshop_product_' . $product['product_id'],
			'item_qty' => $product['product_qty'],
			'item_pu_ht' => number_format($pu_ht, 5, '.', ''),
			'item_pu_ttc' => number_format($pu_ttc, 5, '.', ''),
			'item_ecotaxe_ht' => number_format(0, 5, '.', ''),
			'item_ecotaxe_tva' => 19.6,
			'item_ecotaxe_ttc' => number_format(0, 5, '.', ''),
			'item_discount_type' => $item_discount_type,
			'item_discount_value' => $item_discount_value,
			'item_discount_amount' => number_format($item_discount_amount, 5, '.', ''),
			'item_tva_rate' => $tva,
			'item_tva_amount' => number_format($pu_tva, 5, '.', ''),
			'item_total_ht' => number_format($total_ht, 5, '.', ''),
			'item_tva_total_amount' => number_format($tva_total_amount, 5, '.', ''),
			'item_total_ttc' => number_format($total_ttc, 5, '.', ''),
			'item_meta' => !empty($product['item_meta']) ? $product['item_meta'] : array()
		);

		$array_not_to_do = array(WPSHOP_PRODUCT_PRICE_HT,WPSHOP_PRODUCT_PRICE_TTC,WPSHOP_PRODUCT_PRICE_TAX_AMOUNT,'product_qty',WPSHOP_PRODUCT_PRICE_TAX,'product_id','product_reference','product_name','variations');

		if(!empty($product['item_meta'])) {
			foreach($product['item_meta'] as $key=>$value) {
				if( !isset($item['item_'.$key]) && !in_array($key, $array_not_to_do) && !empty($product[$key]) ) {
					$item['item_'.$key] = $product[$key];
				}
			}
		}

		return $item;
	}


	/**
	 *	Add information about user to the selected order
	 *
	 *	@param int $user_id The user identifier to get information for and to add to order meta informations
	 *	@param int $order_id The order identifier to update meta information for
	 *
	 *	@return void
	 */
	function set_order_customer_addresses($user_id, $order_id, $shipping_address_id='', $billing_address_id=''){
		/**	Get order informations	*/
		$billing_info['id'] = get_post_meta($billing_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
		$billing_info['address'] = get_post_meta($billing_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
		if ( !empty($_SESSION['shipping_partner_id']) ) {
			$partner_address_id = get_post_meta( $_SESSION['shipping_partner_id'], '_wpshop_attached_address', true);
			if (!empty($partner_address_id)) {
				foreach( $partner_address_id as $address_id ) {
					$shipping_info['id'] = get_post_meta($address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
					$shipping_info['address'] = get_post_meta( $address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
				}
			}
		}
		else {
			$shipping_info['id'] = get_post_meta($shipping_address_id, WPSHOP_ADDRESS_ATTRIBUTE_SET_ID_META_KEY, true);
			$shipping_info['address'] = get_post_meta($shipping_address_id, '_'.WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS.'_metadata', true);
		}

		$order_info = array('billing' => $billing_info, 'shipping' => $shipping_info);

		/**	Update order info metadata with new shipping	*/
		update_post_meta($order_id, '_order_info', $order_info);
	}


	/** Set the custom colums
	 * @return array
	*/
	function orders_edit_columns($columns){
	  $columns = array(
		'cb' => '<input type="checkbox" />',
		'order_status' => __('Status', 'wpshop'),
		'order_type' => __('Order type', 'wpshop'),
		'order_billing' => __('Billing', 'wpshop'),
		'order_shipping' => __('Shipping', 'wpshop'),
		'order_total' => __('Order total', 'wpshop'),
		'date' => __('Date', 'wpshop'),
		'order_actions' => __('Actions', 'wpshop')
	  );

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	function orders_custom_columns($column, $post_id) {
		if ( get_post_type( $post_id ) == WPSHOP_NEWTYPE_IDENTIFIER_ORDER ) {
			global $civility, $order_status;

			$metadata = get_post_custom();

			$order_postmeta = isset($metadata['_order_postmeta'][0])?unserialize($metadata['_order_postmeta'][0]):'';
			$addresses = get_post_meta($post_id,'_order_info', true);

			switch($column){
				case "order_status":
					echo !empty($order_postmeta['order_status']) ? sprintf('<mark class="%s" id="order_status_'.$post_id.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) : __('Unknown Status', 'wpshop');
				break;

				case "order_billing":
					if ( !empty($addresses['billing']) && !empty($addresses['billing']['address']) && is_array($addresses['billing']['address']) ) {
						$billing = $addresses['billing']['address'];
					}
					else if ( !empty($addresses['billing']) ) {
						$billing = $addresses['billing'];
					}
					if ( !empty($billing) ) {
						echo (!empty($billing['civility']) ? __(wpshop_attributes::get_attribute_type_select_option_info($billing['civility'], 'label', 'custom'), 'wpshop') : null).' <strong>'.(!empty($billing['address_first_name']) ? $billing['address_first_name'] : null).' '.(!empty($billing['address_last_name']) ? $billing['address_last_name'] : null).'</strong>';
						echo empty($billing['company'])?'<br />':', <i>'.$billing['company'].'</i><br />';
						echo (!empty($billing['address']) ? $billing['address'] : null).'<br />';
						echo (!empty($billing['postcode']) ? $billing['postcode'] : null).' '.(!empty($billing['city']) ? $billing['city'] : null).', '.(!empty($billing['country']) ? $billing['country'] : null);
					}
					else {
						echo __('No information available for user billing', 'wpshop');
					}
				break;

				case "order_shipping":
					if ( !empty($addresses['shipping']) && !empty($addresses['shipping']['address']) && is_array($addresses['shipping']['address']) ) {
						$shipping = $addresses['shipping']['address'];
					}
					else if ( !empty($addresses['shipping']) ) {
						$shipping = $addresses['shipping'];
					}
					if ( !empty($shipping) ) {
						echo '<strong>'.(!empty($shipping['address_first_name']) ? $shipping['address_first_name'] : null).' '.(!empty($shipping['address_last_name']) ? $shipping['address_last_name'] : null).'</strong>';
						echo empty($shipping['company'])?'<br />':', <i>'.$shipping['company'].'</i><br />';
						echo (!empty($shipping['address']) ? $shipping['address'] : null).'<br />';
						echo (!empty($shipping['postcode']) ? $shipping['postcode'] : null).' '.(!empty($shipping['city']) ? $shipping['city'] : null).', '.(!empty($shipping['country']) ? $shipping['country'] : null);
					}
					else{
						echo __('No information available for user shipping', 'wpshop');
					}
				break;

				case "order_type":
						echo '<a href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.(!empty($order_postmeta['order_temporary_key']) ? __('Quotation','wpshop') :  __('Basic order','wpshop')).'</a>';
					break;

				case "order_total":
					$currency = !empty($order_postmeta['order_currency']) ?$order_postmeta['order_currency'] : get_option('wpshop_shop_default_currency');
					echo !empty($order_postmeta['order_grand_total']) ? number_format($order_postmeta['order_grand_total'],2,'.', '').' '.  wpshop_tools::wpshop_get_sigle($currency) : 'NaN';
				break;

				case "order_actions":
					$buttons = '<p>';
					// Marquer comme envoy�
					if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'completed')) {
							$buttons .= '<a class="button markAsShipped order_'.$post_id.'">'.__('Mark as shipped', 'wpshop').'</a> ';
					}
					else if (!empty($order_postmeta['order_status']) && ($order_postmeta['order_status'] == 'awaiting_payment' )) {
					//		$buttons .= '<a class="button markAsCompleted order_'.$post_id.' alignleft" >'.__('Payment received', 'wpshop').'</a>' . wpshop_payment::display_payment_receiver_interface($post_id) . ' ';
					}

					// Voir la commande
						$buttons .= '<a class="button alignright" href="'.admin_url('post.php?post='.$post_id.'&action=edit').'">'.__('View', 'wpshop').'</a>';
					$buttons .= '</p>';
					$buttons .= '<input type="hidden" name="input_wpshop_change_order_state" id="input_wpshop_change_order_state" value="' . wp_create_nonce("wpshop_change_order_state") . '" />';
					$buttons .= '<input type="hidden" name="input_wpshop_dialog_inform_shipping_number" id="input_wpshop_dialog_inform_shipping_number" value="' . wp_create_nonce("wpshop_dialog_inform_shipping_number") . '" />';
					$buttons .= '<input type="hidden" name="input_wpshop_validate_payment_method" id="input_wpshop_validate_payment_method" value="' . wp_create_nonce("wpshop_validate_payment_method") . '" />';

					echo $buttons;
				break;
			}

		}
	}


	/** Prints the box content */
	function add_private_comment($oid, $comment, $send_email, $send_sms) {

		$order_private_comments = get_post_meta($oid, '_order_private_comments', true);
		$order_private_comments = !empty($order_private_comments) ? $order_private_comments : array();

		/*	Get order current content	*/
		$order_meta = get_post_meta($oid, '_order_postmeta', true);

		// Send email is checked
		if($send_email === "true") {
			// Get order current content
			$user = get_post_meta($oid, '_order_info', true);
			$email = isset($user['billing']['address']['address_user_email']) ? $user['billing']['address']['address_user_email'] :'';
			$first_name = isset($user['billing']['address']['address_first_name'])?$user['billing']['address']['address_first_name']:'';
			$last_name = isset($user['billing']['address']['address_last_name'])?$user['billing']['address']['address_last_name']:'';

			$object = array('object_type'=>'order','object_id'=>$oid);
			/* Envoie du message de confirmation de commande au client	*/
			wpshop_messages::wpshop_prepared_email(
				$email,
				'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE',
				array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'], 'message' => $comment, 'order_addresses' => '', 'order_billing_address' => '', 'order_shipping_address' => ''),
				$object
			);
		}
		// Send sms is checked
		/*if($send_sms === "true") {
			// Get order current content
			$user = get_post_meta($oid, '_order_info', true);
			$email = $user['billing']['address']['address_user_email'];
			$first_name = $user['billing']['address']['address_first_name'];
			$last_name = $user['billing']['address']['address_last_name'];
			$phone = !empty($user['billing']['address']['phone']) ? $user['billing']['phone'] : $user['shipping']['phone'];

			$message = wpshop_messages::customMessage(
				WPSHOP_ORDER_UPDATE_MESSAGE,
				array('customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_key' => $order_meta['order_key'])
			);
			$userList = array();
			$userList[]['from'][] = 'wpshop_list';
			$userList[]['tel'] = $phone;

			// Send the message
			sendsms_message::sendSMS($message, $userList);
		}*/

		$order_private_comments[] = array(
			'comment_date' => current_time('mysql',0),
			'send_email' => $send_email,
			'send_sms' => $send_sms,
			'comment' => $comment
		);

		if(is_array($order_private_comments)) {
			update_post_meta($oid, '_order_private_comments', $order_private_comments);
			return true;
		}
		else return false;
	}

	/** Orders comments */
	function order_private_comments($post){
		$content = '<textarea name="order_private_comment" style="width:100%"></textarea><br />';
		$content .= '<label><input type="checkbox" name="send_email" /> '.__('Send an email to customer','wpshop').'</label><br />';
		//$content .= '<label><input type="checkbox" name="send_sms" /> '.__('Send a SMS to customer','wpshop').'</label><br />';
		//$content .= '<label><input type="checkbox" name="allow_visibility" /> '.__('Visible from the customer account','wpshop').'</label><br />';
		$content .= '<br /><a class="button addPrivateComment order_'.$post->ID.'">'.__('Add the comment','wpshop').'</a>';

		$order_private_comments = get_post_meta($post->ID, '_order_private_comments', true);

		if ( !empty( $order_private_comments ) ) {
			$order_private_comments = array_reverse($order_private_comments);
			$content .= '<br /><br /><div id="comments_container">';
			foreach ( $order_private_comments as $o ) {
				$content .= '<hr /><b>'.__('Date','wpshop').':</b> '.mysql2date('d F Y, H:i:s',$o['comment_date'], true).'<br /><b>'.__('Message','wpshop').':</b> '.nl2br($o['comment']);
			}
			$content .= '</div>';
		}

		echo $content;
	}

	/**
	 * Return an array list of all the notifications regarding the object (ex of object : order, id=458)
	 */
	function get_notification_by_object($object) {
		global $wpdb;
		$data = array();
		if(!empty($object['object_type']) && !empty($object['object_id'])) {
			$order_postmeta = get_post_meta($object['object_id'], '_order_postmeta', true);
			$order_info = get_post_meta($object['object_id'], '_order_info', true);
			if ( !empty( $order_postmeta ) && !empty( $order_info ) ) {
			$option_message = get_option('WPSHOP_ORDER_UPDATE_MESSAGE');
			$order_date = ( !empty($order_postmeta['order_date']) ) ? gmdate('Y-m', time( $order_postmeta['order_date'] ) ) : null;
			if ( !empty($order_postmeta['customer_id'])) {
				$query_user = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s', $order_postmeta['customer_id'], WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
				$user_post_id = $wpdb->get_var( $query_user );
				$messages = get_post_meta( $user_post_id, '_wpshop_messages_histo_' .$option_message. '_'.$order_date, true );
				if ( !empty ($messages) ) {
					foreach ( $messages as $message ) {
						if ( $message['mess_object_id'] == $object['object_id'] ) {
							$data[] = $message;
						}
					}
				}
				}
			}
		}

		return $data;
	}


	/**
	 * Display orders list for a given customer
	 *
	 * @param object $post The current element being edited (i.e a customer)
	 * @param array $metaboxArgs Extras arguments
	 */
	function display_orders_for_customer($post, $metaboxArgs) {
		global $wpdb, $order_status;

		$query = $wpdb->prepare(
				"SELECT *
				FROM ".$wpdb->posts." AS posts
					INNER JOIN ".$wpdb->postmeta." AS metas ON (metas.post_id = posts.ID)
				WHERE post_type = %s
					AND post_status = %s
					AND meta_key = %s
					AND meta_value = %s
				ORDER BY post_date DESC",
				WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'publish', '_wpshop_order_customer_id', $post->post_author);
		$orders_id = $wpdb->get_results($query);

		/** Use the wpshop_customer_entities_custom_List_table to display the table */
		$wpshop_list_table = new wpshop_customer_entities_custom_List_table();
		$attribute_set_list = array();
		$i=0;
		foreach ($orders_id as $o_id) {

			$query  = $wpdb->prepare('SELECT meta_value, post_id FROM '.$wpdb->postmeta.' WHERE post_id = '.$o_id->ID.'', '');
			$infos = $wpdb->get_results($query);
			if (!empty($infos)) {
				$o = get_post_meta($o_id->ID, '_order_postmeta', true);
				$currency = wpshop_tools::wpshop_get_sigle($o['order_currency']);

				$attribute_set_list[$i]['date'] = $o['order_date'];
				if( empty($o['order_key']) ) {
					$attribute_set_list[$i]['order_number'] = $o['order_temporary_key'];
				}
				else {
					$attribute_set_list[$i]['order_number'] = $o['order_key'];
				}

				$attribute_set_list[$i]['total'] = number_format($o['order_grand_total'], 2, '.', '').' '.$currency;
				$attribute_set_list[$i]['status'] = '<span class="wpshop_orders_status-'.$o['order_status'].'">'.__($order_status[$o['order_status']], 'wpshop').'</span>';
				$attribute_set_list[$i]['action'] = $o_id->ID;
				$i++;
			}

		}

		$wpshop_list_table->prepare_items($attribute_set_list);
		$wpshop_list_table->views();
		$wpshop_list_table->display();
	}


}
