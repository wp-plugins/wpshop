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
						'add_new' 				=> __('Add Order', 'wpshop'),
						'add_new_item' 			=> __('Add New Order', 'wpshop'),
						'edit' 					=> __('Edit', 'wpshop'),
						'edit_item' 			=> __('Edit Order', 'wpshop'),
						'new_item' 				=> __('New Order', 'wpshop'),
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
		
		// Ajout de la box action
		add_meta_box( 
			'wpshop_order_status',
			__('Status', 'wpshop'),
			array('wpshop_orders', 'order_status_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_ORDER, 'normal', 'low'
		);
	}
	
	// Print the content of the order
	function order_content($post) {
	
		$metadata = get_post_custom();
		$order = unserialize($metadata['_order_postmeta'][0]);
		$currency = wpshop_tools::wpshop_get_sigle($order['order_currency']);
		
		foreach($order['order_items'] as $o) {
			echo '<span class="right">'.number_format($o['item_total_ttc'], 2, '.', '').' '.$currency.'</span>'.$o['item_qty'].' x '.$o['item_name'].'<br />';
		}
		echo '<hr />';
		echo '<span class="right">'.number_format($order['order_total_ht'], 2, '.', '').' '.$currency.'</span>'.__('Total ET','wpshop').'<br />';
		foreach($order['order_tva'] as $k => $v) {
			echo '<span class="right">'.number_format($v,2,'.',' ').' '.$currency.'</span>'.__('Tax','wpshop').' '.$k.'%'.'<br />';
		}
		echo '<span class="right">'.(empty($order['order_shipping_cost'])?'<strong>'.__('Free','wpshop').'</strong>':number_format($order['order_shipping_cost'], 2, '.', '').' '.$currency).'</span>'.__('Shipping fee','wpshop').'<br />';
		echo '<span class="right"><strong>'.number_format($order['order_grand_total'], 2, '.', '').' '.$currency.'</strong></span>'.__('Total ATI','wpshop');
	}
	
	/* Prints the box content */
	function order_info_box($post) {

		// Use nonce for verification
		//wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

		$metadata = get_post_custom();
		$order_postmeta = unserialize($metadata['_order_postmeta'][0]);
		$order_info = unserialize($metadata['_order_info'][0]);
		$billing = $order_info['billing'];
		$shipping = $order_info['shipping'];
		
		// Civility
		$civility = array(1=>__('Mr.','wpshop'),__('Mrs.','wpshop'),__('Miss','wpshop'));
			
		// The actual fields for data entry
		echo '<div class="half"><big>'.__('Billing','wpshop').'</big><br /><br />';
			echo (!empty($billing['civility']) ? $civility[$billing['civility']] : null).' <strong>'.$billing['first_name'].' '.$billing['last_name'].'</strong>';
			echo empty($billing['company'])?'<br />':', <i>'.$billing['company'].'</i><br />';
			echo $billing['address'].'<br />';
			echo $billing['postcode'].' '.$billing['city'].', '.$billing['country'];
		echo '</div>';
		
		echo '<div class="half"><big>'.__('Shipping','wpshop').'</big><br /><br />';
			echo '<strong>'.$shipping['first_name'].' '.$shipping['last_name'].'</strong>';
			echo empty($shipping['company'])?'<br />':', <i>'.$shipping['company'].'</i><br />';
			echo $shipping['address'].'<br />';
			echo $shipping['postcode'].' '.$shipping['city'].', '.$shipping['country'];
		echo '</div>';
	}
	
	/* Prints the box content */
	function order_status_box($post) {
		global $post;
		
		// Status
		$order_status = array(
			'awaiting_payment' => __('Awaiting payment', 'wpshop'),
			'completed' => __('Paid', 'wpshop'),
			'shipped' => __('Shipped', 'wpshop')
		);
		
		$metadata = get_post_custom();
		$order_postmeta = unserialize($metadata['_order_postmeta'][0]);
		$order_info = unserialize($metadata['_order_info'][0]);
		
		echo __('Order date','wpshop').': <strong>'.$order_postmeta['order_date'].'</strong><br />';
		echo __('Order payment date','wpshop').': '.(empty($order_postmeta['order_payment_date'])?__('Unknow','wpshop'):'<strong>'.$order_postmeta['order_payment_date'].'</strong>').'<br />';
		echo __('Order shipping date','wpshop').': '.(empty($order_postmeta['order_shipping_date'])?__('Unknow','wpshop'):'<strong>'.$order_postmeta['order_shipping_date'].'</strong>').'<br /><br />';
		echo '<div class="column-order_status">';
		echo sprintf('<mark class="%s" id="order_status_'.$post->ID.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), $order_status[strtolower($order_postmeta['order_status'])]);
		echo '</div>';
		
		// Marquer comme envoyé
		if($order_postmeta['order_status'] == 'completed') {
			echo '<p><a class="button markAsShipped order_'.$post->ID.'">'.__('Mark as shipped', 'wpshop').'</a></p>';
		}
		elseif($order_postmeta['order_status'] == 'awaiting_payment') {
			echo '<p><a class="button markAsCompleted order_'.$post->ID.'">'.__('Payment received', 'wpshop').'</a></p>';
		}
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
		global $post;
		
		// Status
		$order_status = array(
			'awaiting_payment' => __('Awaiting payment', 'wpshop'),
			'completed' => __('Paid', 'wpshop'),
			'shipped' => __('Shipped', 'wpshop')
		);
		// Civility
		$civility = array(1=>__('Mr.','wpshop'),__('Mrs.','wpshop'),__('Miss','wpshop'));
		
		$metadata = get_post_custom();
		$order_postmeta = unserialize($metadata['_order_postmeta'][0]);
		$order_info = unserialize($metadata['_order_info'][0]);
		$billing = $order_info['billing'];
		$shipping = $order_info['shipping'];

		switch ($column) {
			case "order_status":
				echo sprintf('<mark class="%s" id="order_status_'.$post->ID.'">%s</mark>', sanitize_title(strtolower($order_postmeta['order_status'])), $order_status[strtolower($order_postmeta['order_status'])]);
			break;
			
			case "order_billing":
				echo (!empty($billing['civility']) ? $civility[$billing['civility']] : null).' <strong>'.$billing['first_name'].' '.$billing['last_name'].'</strong>';
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
					$buttons .= '<a class="button markAsCompleted order_'.$post->ID.'">'.__('Payment received', 'wpshop').'</a> ';
				}
				// Voir la commande
				$buttons .= '<a class="button" href="'.admin_url('post.php?post='.$post->ID.'&action=edit').'">'.__('View', 'wpshop').'</a>';
				$buttons .= '</p>';
				echo $buttons;
			break;
		  }
	}
	
	/** Generate the billing reference regarding the order $order_id
	 * @return void
	*/
	function order_generate_billing_number($order_id) {
		global $wpdb;
		
		// Get the order from the db
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		// If the payment is completed
		if($order['order_status']=='completed') {
		
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

}