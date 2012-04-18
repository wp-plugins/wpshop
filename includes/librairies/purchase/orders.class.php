<?php
/**
* Products management method file
* 
*	This file contains the different methods for products management
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
	*	Call wordpress function that declare a new term type in order to define the product as wordpress term (taxonomy)
	*/
	function create_orders_type() {
	
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
			array(
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
			)
		);
	}
	
	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		global $post, $currentTabContent;

		// Ajout de la box info
		add_meta_box( 
			'wpshop_order_main_info',
			__('Billing & Shipping order info', 'wpshop'),
			array('wpshop_orders', 'order_info_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'high'
		);
		
		// Ajout de la box contenu de la commande
		add_meta_box( 
			'wpshop_order_content',
			__('Order content', 'wpshop'),
			array('wpshop_orders', 'order_content'),
			 WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);

		// Ajout de la box Informations principale
		add_meta_box( 
			'wpshop_order_main_infos',
			__('Main informations', 'wpshop'),
			array('wpshop_orders', 'order_main_infos_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);

		// Ajout de la box action
		add_meta_box( 
			'wpshop_order_status',
			__('Payment status', 'wpshop'),
			array('wpshop_orders', 'order_status_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'side', 'high'
		);
	}
	
	/** Print the content of the order
	*
	*/
	function order_content($post){
		$order_content = '';

		$order = get_post_meta($post->ID, '_order_postmeta', true);

		$order_content .= '<div id="product_chooser_dialog" title="' . __('Choose a new product to add to the current order', 'wpshop') . '" class="wpshopHide" ><div class="loading_picture_container" id="product_chooser_picture" ><img src="' . admin_url('images/loading.gif') . '" alt="loading..." /></div><div id="product_chooser_container" class="wpshopHide" >&nbsp;</div></div>
<div id="order_product_container" class="order_product_container clear" >';
		if($order){/*	Read the order content if the order has product	*/
			ob_start();
			wpshop_cart::display_cart(true, $order, 'admin');
			$cart = ob_get_contents();
			ob_end_clean();
			$order_content .= '<input type="hidden" value="" name="order_products_to_delete" id="order_products_to_delete" />' . $cart . '
	<div id="order_refresh_button_container" class="wpshop_clear_block" ><button class="button-primary alignright wpshopHide" id="wpshop_admin_order_recalculate" >' . __('Refresh order informations', 'wpshop') . '</button></div>';
		}
		elseif(!isset($order['order_invoice_ref']) || ($order['order_invoice_ref'] == "")){
			$order_content .= '
	<a href="#" id="order_new_product_add_opener" ><span class="alignleft" >' . __('Add a product to the current order', 'wpshop') . '</span><span class="ui-icon popup_opener" >&nbsp;</span></a>';
		}
		$order_content .= '
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
			close:function(){
				jQuery("#product_chooser_picture").show();
				jQuery("#product_chooser_container").hide();
			},
			buttons:{
				"' . __('Add selected product to order', 'wpshop') . '": function(){
					jQuery("#wpshop_order_selector_product_form").submit();
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
	
	/**	Print box containing the user associated to the current order
	*
	*/
	function order_info_box($post, $params){
		global $customer_obj;
		$user_order_box_content = '';

		$order_postmeta = get_post_meta($post->ID, '_order_postmeta', true);
		$order_info = get_post_meta($post->ID, '_order_info', true);

		$billing = $order_info['billing'];
		$shipping = $order_info['shipping'];

		$customer_selector_link = __('Change associated user', 'wpshop');
		$container_state = ' wpshopHide';
		if(empty($order_postmeta['customer_id'])){
			$customer_selector_link = __('Currently no user is selected for this order. Choose user', 'wpshop');
			$container_state = '';
		}
		else{
			$user_info = get_userdata($order_postmeta['customer_id']);
			if(!$billing || $params['force_changing']){
				$billing = $user_info->billing_info;
			}
			if(!$shipping || $params['force_changing']){
				$shipping = $user_info->shipping_info;
			}
		}

		$customer_selector_link_element = '';
		if(empty($order_postmeta['order_invoice_ref'])){
			$customer_selector_link_element = '
				<div class="clear" id="wpshop_order_customer_chooser" >
					<a href="#" id="wpshop_order_customer_changer" class="clear hide alignright" ><span class="wpshop_container_opener alignleft" >&nbsp;</span>' . $customer_selector_link . '</a>
					<div id="wpshop_order_customer_selector" class="' . $container_state . '" >
						<span class="wpshop_custom_search_input_icon" >&nbsp;</span>
						<input class="wpshop_custom_search_input" type="text" id="wpshop_custom_search_input" placeholder="' . __('Search in user list', 'evarisk') . '" />
						<div class="wpshop_complete_user_list clear" >' . $customer_obj->custom_user_list(array($order_postmeta['customer_id'])) . '</div>
					</div>
				</div>';
		}
		if(!empty($order_postmeta['customer_id'])){
			$customer_selector_link_element .= '<div class="current_customer_for_order" >' . sprintf(__('Current customer identifier : %s', 'wpshop') , WPSHOP_IDENTIFIER_CUSTOMER . $order_postmeta['customer_id']) . '</div>';
		}

		if($billing){// The actual fields for data entry
			if($order_postmeta['order_invoice_ref'] != ""){
				$user_order_box_content .= wpshop_account::display_customer_address('Billing', $billing);
			}
			else{
				$user_order_box_content .= wpshop_account::edit_customer_address('Billing', $billing, $order_postmeta['customer_id']);
			}
		}
		else{
			$user_order_box_content .= wpshop_account::edit_customer_address('Billing', $billing, $order_postmeta['customer_id']);
		}

		if($shipping){
			if($order_postmeta['order_invoice_ref'] != ""){
				$user_order_box_content .= wpshop_account::display_customer_address('Shipping', $billing);
			}
			else{
				$user_order_box_content .= wpshop_account::edit_customer_address('Shipping', $shipping, $order_postmeta['customer_id']);
			}
		}
		else{
			$user_order_box_content .= wpshop_account::edit_customer_address('Shipping', $shipping, $order_postmeta['customer_id']);
		}

		echo '<div class="loading_picture_container wpshopHide" id="customer_chooser_picture" ><img src="' . admin_url('images/loading.gif') . '" alt="loading..." /></div><div id="order_customer_box" class="clear" >' . $customer_selector_link_element . '<input type="hidden" value="' . $order_postmeta['customer_id'] . '" name="user[customer_id]" id="wpshop_order_customer_id_' . $post->ID . '" /><div class="clear" >' . $user_order_box_content . '</div></div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery(".wpshop_customer_selector_icon").click(function(){
			var new_user_id = jQuery(this).parent("td").parent("tr").attr("id").replace("customer_", "");
			jQuery("#order_customer_box").html("");
			jQuery("#customer_chooser_picture").show();
			jQuery("#order_customer_box").load(WPSHOP_AJAX_FILE_URL,{
				"post":"true",
				"elementCode":"ajax_load_user_form",
				"order_id":"' . $post->ID . '",
				"customer_id":new_user_id
			});
		});
		jQuery(".wpshop_complete_user_list .odd, .wpshop_complete_user_list .even").click(function(){
			jQuery(this).children("td:first").children("span").click();
		});

		jQuery("#wpshop_custom_search_input").autocomplete({
			source: "' . WPSHOP_INCLUDES_URL . 'live_search/search_users.php",
			select: function( event, ui ){
				jQuery("#customer_" + ui.item.id).children("td:first").children("span").click();
			}
		});
	});
</script>';
	}
	
	/* Prints the box content */
	function order_main_infos_box($post){
		$order_main_infos_box_content = '';
		$order = get_post_meta($post->ID, '_order_postmeta', true);

		if(!empty($order['order_date'])){
			$order_main_infos_box_content .=  __('Order date','wpshop').': <strong>'.mysql2date('d F Y H:i:s', $order['order_date'], true).'</strong><br />';
		}
		if(empty($order['order_date']) || (empty($order['order_key']) && empty($order['order_temporary_key']) && empty($order['order_invoice_ref']))){
			$order_main_infos_box_content .=  __('Temporary quotation reference','wpshop').': <strong>'.self::get_new_pre_order_reference(false).'</strong><br />';
		}
		else{
			if(!empty($order['order_key'])){
				$order_main_infos_box_content .=  __('Order reference','wpshop').': <strong>'.$order['order_key'].'</strong><br />';
			}
			if(!empty($order['order_temporary_key'])){
				$order_main_infos_box_content .=  __('Pre-order reference','wpshop').': <strong>'.$order['order_temporary_key'].'</strong><br />';
			}
			if(!empty($order['order_invoice_ref'])){
				$order_main_infos_box_content .=  __('Invoice number','wpshop').': <strong>'.$order['order_invoice_ref'].'</strong><br />';
			}

			/*	Display possibility to duplicate an order	*/
			$order_main_infos_box_content .=  '<br/><input type="hidden" name="pid" value="'.$post->ID.'" /><a class="button" href="#" id="duplicate_the_order">'.__('Duplicate the order', 'wpshop').'</a><br />';
		}

		$order_main_infos_box_content .= '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		if(jQuery("#title").val() == ""){
			jQuery("#title").val((wpshopConvertAccentTojs("' . sprintf(__('Order - %s', 'wpshop'), mysql2date('d M Y\, H:i:s', current_time('mysql', 0), true)) . '")));
		}

		// DUPLICATE AN ORDER
		jQuery("a#duplicate_the_order").click(function(){
			var _this = jQuery(this);
			_this.attr("class", "button");
			// Display loading...
			_this.addClass("loading");
			
			var pid = jQuery("input[name=pid]").val();
			
			jQuery.getJSON(WPSHOP_AJAX_FILE_URL, {post:"true", elementCode:"duplicate_order", pid:pid},
				function(data){
					_this.removeClass("loading");
					if(data[0]){
						_this.addClass("success");
						_this.after("<a href=\'' . admin_url('post.php?post=" + data[1] + "&action=edit') . '\' >' . __('View created order', 'wpshop') . '</a>");
					}
					else{
						_this.addClass("error");
					}
				}
			);
			
			return false;
		});
	});
</script>';

		echo $order_main_infos_box_content;
	}

	/* Prints the box content */
	function order_status_box($post){
		global $order_status;
		$order_status_box_content = '';

		$order_postmeta = get_post_meta($post->ID, '_order_postmeta', true);
		// echo '<pre>';print_r($order_postmeta);echo '</pre>';

		if(empty($order_postmeta['order_status'])){
			$order_status_box_content .= __('No information available for this order for the moment', 'wpshop');
		}
		else{
			$payment_method = '';
			if(!empty($order_postmeta['payment_method'])){
				$payment_method = '<p>'.sprintf(__('Payment method %s', 'wpshop'), __($order_postmeta['payment_method'], 'wpshop'));

				switch($order_postmeta['payment_method']){
					case 'check':
						$check_nb = get_post_meta($post->ID, '_order_check_number', true);
						if(!empty($check_nb))$payment_method .= '<br/>' . sprintf(__('Check number: %s', 'wpshop'), $check_nb);
					break;
					case 'paypal':
						$paypal_txn = get_post_meta($post->ID, '_order_paypal_txn_id', true);
						if(!empty($paypal_txn))$payment_method .= '<br/>' . sprintf(__('Transaction identifier: %s', 'wpshop'), $paypal_txn);
					break;
				}

				$payment_method .= '</p>';
			}
			else{
				$payment_method = '<p>'.__('No payment method selected for the moment', 'wpshop') . '</p>';
			}

			$order_status_box_content .= '<div class="column-order_status">' . 
			sprintf('<mark class="%s" id="order_status_'.$post->ID.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop')) . '</div>';

			// Marquer comme envoyé
			switch($order_postmeta['order_status']){
				case 'awaiting_payment':{
					$order_status_box_content .= __('Waiting for payment', 'wpshop') .'<p><a class="button markAsCompleted order_'.$post->ID.'">'.__('Payment received', 'wpshop').'</a></p>' . wpshop_payment::set_payment_transaction_number($post->ID) . ' ';
				}break;
				case 'completed':{
					$order_status_box_content .= __('Order payment date','wpshop').': '.(empty($order_postmeta['order_payment_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_payment_date'], true).'</strong>').'<br />' . $payment_method . '<p><a class="button markAsShipped order_'.$post->ID.'">'.__('Mark as shipped', 'wpshop').'</a></p>';
				}break;
				case 'shipped':{
					$order_status_box_content .= __('Order payment date','wpshop').': '.(empty($order_postmeta['order_payment_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_payment_date'], true).'</strong>').'<br />' . $payment_method;
					$order_status_box_content .= __('Order shipping date','wpshop').': '.(empty($order_postmeta['order_shipping_date'])?__('Unknow','wpshop'):'<strong>'.mysql2date('d F Y H:i:s', $order_postmeta['order_shipping_date'],true).'</strong>').'<br />';
					if(!empty($order_postmeta['order_trackingNumber']))$order_status_box_content .= __('Tracking number','wpshop').': '.$order_postmeta['order_trackingNumber'].'<br /><br />';
				}break;
			}

			if(!empty($order_postmeta['order_temporary_key']) && empty($order_postmeta['order_invoice_ref'])){
				$order_status_box_content .= '<br/><input type="hidden" name="oid" value="'.$post->ID.'" /><a class="button alignright" href="#" id="bill_order">'.__('Charge this order', 'wpshop').'</a><br class="clear" />';
			}
		}

		echo $order_status_box_content;
	}
	
	/** Set the custom colums
	 * @return array
	*/
	function orders_edit_columns($columns){
	  $columns = array(
		'cb' => '<input type="checkbox" />',
		'order_status' => __('Status', 'wpshop'),
		'title' => __('Order', 'wpshop'),
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
	function orders_custom_columns($column){
		global $post, $civility, $order_status;

		$metadata = get_post_custom();
		$order_postmeta = unserialize($metadata['_order_postmeta'][0]);
		$order_info = unserialize($metadata['_order_info'][0]);
		$billing = $order_info['billing'];
		$shipping = $order_info['shipping'];

		switch($column){
			case "order_status":
				echo sprintf('<mark class="%s" id="order_status_'.$post->ID.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), __($order_status[strtolower($order_postmeta['order_status'])], 'wpshop'));
			break;
			
			case "order_billing":
				echo (!empty($billing['civility']) ? __($civility[$billing['civility']], 'wpshop') : null).' <strong>'.$billing['first_name'].' '.$billing['last_name'].'</strong>';
				echo empty($billing['company'])?'<br />':', <i>'.$billing['company'].'</i><br />';
				echo $billing['address'].'<br />';
				echo $billing['postcode'].' '.$billing['city'].', '.$billing['country'];
			break;
			
			case "order_shipping":
				echo '<strong>'.$shipping['first_name'].' '.$shipping['last_name'].'</strong>';
				echo empty($shipping['company'])?'<br />':', <i>'.$shipping['company'].'</i><br />';
				echo $shipping['address'].'<br />';
				echo $shipping['postcode'].' '.$shipping['city'].', '.$shipping['country'];
			break;
			
			case "order_total":
				$currency = wpshop_tools::wpshop_get_sigle($order_postmeta['order_currency']);
				echo number_format($order_postmeta['order_grand_total'],2,'.', ' ').' '.$currency;
			break;
			
			case "order_actions":
				$buttons = '<p>';
				// Marquer comme envoyé
				if($order_postmeta['order_status'] == 'completed') {
					$buttons .= '<a class="button markAsShipped order_'.$post->ID.'">'.__('Mark as shipped', 'wpshop').'</a> ';
				}
				elseif($order_postmeta['order_status'] == 'awaiting_payment' ) {
					$buttons .= '<a class="button markAsCompleted order_'.$post->ID.' alignleft" >'.__('Payment received', 'wpshop').'</a>' . wpshop_payment::set_payment_transaction_number($post->ID) . ' ';
				}

				// Voir la commande
				$buttons .= '<a class="button alignright" href="'.admin_url('post.php?post='.$post->ID.'&action=edit').'">'.__('View', 'wpshop').'</a>';
				$buttons .= '</p>';
				echo $buttons;
			break;
		}
	}

	/** Generate the billing reference regarding the order $order_id
	 * @return void
	*/
	function order_generate_billing_number($order_id, $force_invoicing = false){
		global $wpdb;
		
		// Get the order from the db
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		// If the payment is completed
		if(($order['order_status']=='completed') || $force_invoicing) {
		
			// If the reference hasn't been generated yet
			if(empty($order['order_invoice_ref'])) {
			
				$number_figures = get_option('wpshop_billing_number_figures', false);
				/* If the number doesn't exist, we create a default one */
				if(!$number_figures) {
					$number_figures = 5;
					update_option('wpshop_billing_number_figures', $number_figures);
				}
				
				$billing_current_number = get_option('wpshop_billing_current_number', false);
				/* If the counter doesn't exist, we initiate it */
				if(!$billing_current_number) { $billing_current_number = 1; }
				else { $billing_current_number++; }
				update_option('wpshop_billing_current_number', $billing_current_number);
				
				$invoice_ref = WPSHOP_BILLING_REFERENCE_PREFIX.((string)sprintf('%0'.$number_figures.'d', $billing_current_number));
				$order['order_invoice_ref'] = $invoice_ref;
				update_post_meta($order_id, '_order_postmeta', $order);
			}
		}
	}

	/**
	*
	*/
	function save_order_custom_informations(){
		/*	Get order current content	*/
		$order_meta = get_post_meta($_REQUEST['post_ID'], '_order_postmeta', true);

		/* Envoie du message de confirmation de commande au client	*/
		wpshop_tools::wpshop_prepared_email($email, 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', array('customer_first_name' => $first_name, 'customer_last_name' => $last_name));

		/* On enregistre l'adresse de facturation et de livraison	*/
		$update_order_billing_and_shipping_infos = false;
		$order_info = array();
		if(isset($_REQUEST['user']['billing_info']) && !empty($_REQUEST['user']['billing_info']) && (count($_REQUEST['user']['billing_info']) > 0)){
			$order_info['billing'] = $_REQUEST['user']['billing_info'];
			$update_order_billing_and_shipping_infos = true;

			$billing_info = get_user_meta($order_meta['customer_id'], 'billing_info', true);
			if(empty($billing_info)){
				update_user_meta($order_meta['customer_id'], 'billing_info', $order_info['billing']);
			}
		}
		if(isset($_REQUEST['user']['shipping_info']) && !empty($_REQUEST['user']['shipping_info']) && (count($_REQUEST['user']['shipping_info']) > 0)){
			$order_info['shipping'] = $_REQUEST['user']['shipping_info'];
			$update_order_billing_and_shipping_infos = true;

			$shipping_info = get_user_meta($order_meta['customer_id'], 'shipping_info', true);
			if(empty($shipping_info)){
				update_user_meta($order_meta['customer_id'], 'shipping_info', $order_info['shipping']);
			}
		}
		if($update_order_billing_and_shipping_infos){
			update_post_meta($_REQUEST['post_ID'], '_order_info', $order_info);
		}

		if(empty($order_meta['customer_id'])){
			$order_meta['customer_id'] = $_REQUEST['user']['customer_id'];
		}

		/*	Complete information about the order	*/
		if(empty($order_meta['order_key'])){
			$order_meta['order_key'] = !empty($order_meta['order_key']) ? $order_meta['order_key'] : (!empty($order_meta['order_status']) && ($order_meta['order_status']!='awaiting_payment') ? wpshop_orders::get_new_order_reference() : '');
			$order_meta['order_temporary_key'] = (isset($order_meta['order_temporary_key']) && ($order_meta['order_temporary_key'] != '')) ? $order_meta['order_temporary_key'] : wpshop_orders::get_new_pre_order_reference();
		}
		$order_meta['order_status'] = (isset($order_meta['order_status']) && ($order_meta['order_status'] != '')) ? $order_meta['order_status'] : 'awaiting_payment';
		$order_meta['order_date'] = (isset($order_meta['order_date']) && ($order_meta['order_date'] != '')) ? $order_meta['order_date'] : current_time('mysql', 0);
		$order_meta['order_currency'] = wpshop_tools::wpshop_get_currency(true);/*	Update order content	*/

		/*	Set order information into post meta	*/
		update_post_meta($_REQUEST['post_ID'], '_order_postmeta', $order_meta);
	}

	/** Renvoi une nouvelle référence unique pour une commande
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

	/** Renvoi une nouvelle référence unique pour un devis
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
	function add_product_to_order($product){
		/*	Read selected product list for adding to order	*/
		$pu_ht = $product['product_price_ht'];
		$pu_ttc = $product['product_price_ttc'];
		$pu_tva = $product['product_tax_amount'];
		$total_ht = $pu_ht*$product['product_qty'];
		$tva_total_amount = $pu_tva*$product['product_qty'];
		$total_ttc = $pu_ttc*$product['product_qty'];
		$tva = $product['product_tax_rate'];

		$item = array(
			'item_id' => $product['product_id'],
			'item_ref' => $product['product_reference'],
			'item_name' => $product['product_name'],
			'item_qty' => $product['product_qty'],
			'item_pu_ht' => number_format($pu_ht, 5, '.', ''),
			'item_pu_ttc' => number_format($pu_ttc, 5, '.', ''),
			'item_ecotaxe_ht' => number_format(0, 5, '.', ''),
			'item_ecotaxe_tva' => 19.6,
			'item_ecotaxe_ttc' => number_format(0, 5, '.', ''),
			'item_discount_type' => 0,
			'item_discount_value' => 0,
			'item_discount_amount' => number_format(0, 5, '.', ''),
			'item_tva_rate' => $tva,
			'item_tva_amount' => number_format($pu_tva, 5, '.', ''),
			'item_total_ht' => number_format($total_ht, 5, '.', ''),
			'item_tva_total_amount' => number_format($tva_total_amount, 5, '.', ''),
			'item_total_ttc' => number_format($total_ttc, 5, '.', '')
		);

		return $item;
	}

	/**	Give to admin user possibility to duplicate an order
	*
	*/
	function duplicate_order($pid) {
		global $wpdb;
		
		// Get the product post info
		$query_posts = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE ID='.$pid);
		$data_posts = $wpdb->get_row($query_posts,ARRAY_A);
		$data_posts['ID'] = NULL;
		$data_posts['post_date'] = current_time('mysql', 0);
		$data_posts['post_date_gmt'] = current_time('mysql', 0);
		$data_posts['post_modified'] = current_time('mysql', 0);
		$data_posts['post_modified_gmt'] = current_time('mysql', 0);
		$data_posts['guid'] = NULL;
			
		// Get others features like thumbnails
		$query_posts_more = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'posts WHERE post_parent='.$pid.' AND post_type="attachment"');
		$data_posts_more = $wpdb->get_results($query_posts_more,ARRAY_A);
		
		// Postmeta
		$order_content_meta = get_post_meta($pid,'_order_postmeta', true);
		$order_content_meta['order_status'] = NULL;
		$order_content_meta['order_key'] = NULL;
		$order_content_meta['order_payment_date'] = NULL;
		$order_content_meta['order_shipping_date'] = NULL;
		$order_content_meta['payment_method'] = NULL;
		$order_content_meta['order_invoice_ref'] = NULL;
		$order_content_meta['order_temporary_key'] = NULL;
		$order_content_meta['order_old_shipping_cost'] = '0';
		$order_content_meta['shipping_is_free'] = false;
		$order_user_meta = get_post_meta($pid,'_order_info', true);

		$wpdb->insert($wpdb->prefix.'posts', $data_posts);
		$new_pid = $wpdb->insert_id;
		
		// Update the post_name to avoid duplicated product name
		$post_name = $data_posts['post_name'].$new_pid;
		$wpdb->update($wpdb->posts, array('post_name'=>$post_name), array('ID'=>$new_pid));
		
		// Replace the old product id by the new one
		foreach($data_posts_more as $k=>$v){
			$data_posts_more[$k]['ID'] = NULL;
			$data_posts_more[$k]['post_parent'] = $new_pid;
			$data_posts_more[$k]['post_date'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_date_gmt'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_modified'] = current_time('mysql', 0);
			$data_posts_more[$k]['post_modified_gmt'] = current_time('mysql', 0);
			$wpdb->insert($wpdb->prefix.'posts', $data_posts_more[$k]);
		}
		
		update_post_meta($new_pid, '_order_postmeta', $order_content_meta);
		update_post_meta($new_pid, '_order_info', $order_user_meta);

		return $new_pid;
	}

	/**
	*	Add information about user to the selected order
	*
	*	@param int $user_id The user identifier to get information for and to add to order meta informations
	*	@param int $order_id The order identifier to update meta information for
	*
	*	@return void
	*/
	function set_order_customer_addresses($user_id, $order_id){
		// On récupére les infos de facturation et de livraison
		$shipping_info = get_user_meta($user_id, 'shipping_info', true);
		$billing_info = get_user_meta($user_id, 'billing_info', true);

		$order_info = array('billing' => $billing_info, 'shipping' => $shipping_info);

		// On enregistre l'adresse de facturation et de livraison
		update_post_meta($order_id, '_order_info', $order_info);
	}

}