<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


class wpshop_messages {

	/**
	 *	Call wordpress function that declare a new term type in coupon to define the product as wordpress term (taxonomy)
	 */
	function create_message_type() {
		register_post_type(WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, array(
			'labels' => array(
				'name' 					=> __('Message', 'wpshop'),
				'singular_name' 		=> __('message', 'wpshop'),
				'add_new' 				=> __('Add message', 'wpshop'),
				'add_new_item' 			=> __('Add New message', 'wpshop'),
				'edit' 					=> __('Edit', 'wpshop'),
				'edit_item' 			=> __('Edit message', 'wpshop'),
				'new_item' 				=> __('New message', 'wpshop'),
				'view' 					=> __('View message', 'wpshop'),
				'view_item' 			=> __('View message', 'wpshop'),
				'search_items' 			=> __('Search messages', 'wpshop'),
				'not_found' 			=> __('No message found', 'wpshop'),
				'not_found_in_trash' 	=> __('No message found in trash', 'wpshop'),
				'parent-item-colon' 	=> ''
			),
			'description' 				=> __('This is where store messages are stored.', 'wpshop'),
			'public' 					=> true,
			'show_ui' 					=> true,
			'capability_type' 			=> 'post',
			'publicly_queryable' 		=> false,
			'exclude_from_search' 		=> true,
			'show_in_menu' 				=> false,
			'hierarchical' 				=> false,
			'show_in_nav_menus' 		=> false,
			'rewrite' 					=> false,
			'query_var' 				=> true,
			'supports' 					=> array('title','editor'),
			'has_archive' 				=> false
		));
	}

	function getMessageListOption($current=0) {
		$posts = query_posts(array(
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
		));
		$options='';
		if (!empty($posts)) {
				$options = '<option value="0">' .__('Select values from list', 'wpshop'). '</option>';
			foreach ($posts as $p) {
				$selected = $p->ID==$current ? ' selected="selected"': '';
				$options .= '<option value="'.$p->ID.'"'.$selected.'>'.$p->post_title.'</option>';
			}
		}
		wp_reset_query();
		return $options;
	}

	/**
	*	Create the different bow for the product management page looking for the attribute set to create the different boxes
	*/
	function add_meta_boxes() {
		// Ajout de la box info
		add_meta_box(
			'wpshop_message_histo',
			__('Message historic', 'wpshop'),
			array('wpshop_messages', 'message_histo_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, 'normal', 'high'
		);

		// Ajout de la box info
		add_meta_box(
			'wpshop_message_info',
			__('Informations', 'wpshop'),
			array('wpshop_messages', 'message_info_box'),
			 WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE, 'side', 'low'
		);
	}

	/* Prints the box content */
	function message_histo_box($post, $params) {
		$output = '';
		//Check if there is an old stockage method for historic messages
		$output .= self::create_date_historic_message_combobox($post->ID);
		echo $output;
	}

	function create_date_historic_message_combobox ( $message_type_id, $customer_id = 0 ) {
		global $wpdb;

		$query = $wpdb->prepare("SELECT * FROM " .$wpdb->postmeta. " WHERE post_id = %d AND meta_key LIKE 'wpshop_messages_histo\_%%'", $message_type_id);
		$histos = $wpdb->get_results( $query );
		$output = '';
		$output .= '<input type="hidden" id="message_type_id" value="' .$message_type_id. '" />';

		if ( !empty($histos) ) {
			$output .= '<input type="button" class="button-primary" value="' . __('Import messages historic','wpshop'). '" id="ImportHistoryMessageCustomer"/>';
			$output .=  '<div class="loading_picture_container wpshopHide" id="import_messages_loader"><img src="' .WPSHOP_LOADING_ICON. '" alt="loading..." /></div>';
		}
		else {
			if ($customer_id != 0 ) {
				$query = $wpdb->prepare("SELECT * FROM " .$wpdb->postmeta. " WHERE meta_key LIKE '_wpshop_messages_histo_" .$message_type_id. "\_%%' AND post_id = %d",  $customer_id);
			}
			else {
				$query = 'SELECT * FROM '.$wpdb->postmeta.' WHERE meta_key LIKE "_wpshop_messages_histo_' .$message_type_id. '\_%%"';
			}
			$list = $wpdb->get_results($query);
			$existing_date = array();
			if (!empty($list)) {
				$string_date = $string_content = $select_date = '';

				foreach ($list as $l) {
					$date = $l->meta_key;
					$date = str_replace('_wpshop_messages_histo_'.$message_type_id.'_', '', $l->meta_key);
					if ( !in_array($date, $existing_date) ) {
						$select_date .= '<option value="'.$date.'">'.$date.'</option>';
						$existing_date[] = $date;
					}
				}

				$string_date = substr($string_date,0,-3);
				$output .=  '<input type="hidden" id="customer_id" value="0" />';
				$output .=  '<input type="hidden" id="message_type_id" value="' .wpshop_tools::varSanitizer( $message_type_id ). '" />';
				$tpl_component ['OPTIONS_HISTO_MESSAGE_DATE'] = $select_date;
				$tpl_component['LOADING_ICON'] = WPSHOP_LOADING_ICON;
				$output .= wpshop_display::display_template_element('wpshop_messages_histo_date_selection_interface', $tpl_component, array(), 'admin');
				unset($tpl_component);
			}
			else {
				$output .=  '<p>'.__('There is no historic for this message','').'</p>';
			}

		}
		return $output;



	}

	function message_info_box($post, $params) {
		// USERS
		$users = wpshop_customer::getUserList();
		$select_users = '';
		foreach($users as $user) {
			if ($user->ID != 1) {
				$select_users .= '<option value="'.$user->ID.'">'.$user->user_login.'</option>';
			}
		}

		echo '<label>'.__('Recipient','wpshop').'</label><br />';
		echo wpshop_customer::custom_user_list(array('name'=>'recipient', 'id'=>'recipient'), "", false, false);
		/* echo '<select name="recipient" class="chosen_select">';
		echo $select_users;
		echo '</select>'; */

		echo '<input type="hidden" name="wpshop_postid" value="'.$post->ID.'" />';
		echo '<br /><br /><input type="button" class="button-primary alignright" value="'.__('Send the message','wpshop').'" id="sendMessage" /><br /><br />';
	}

	function createMessage ( $code ) {
		$object = get_option($code.'_OBJECT', null);
		$object = empty($object) ? constant($code.'_OBJECT') : $object;

		$message = get_option($code, null);
		$message = empty($message) ? constant($code) : $message;

		// Create post object
		$my_post = array(
				'post_title' => __($object, 'wpshop'),
				'post_content' => __($message, 'wpshop'),
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
		);
		$id = wp_insert_post( $my_post );

		update_option($code, $id);
	}

	/**
	 * Transfert des messages des tables crées vers la table de wordpress
	 */
	function importMessageFromLastVersion() {
		global $wpdb;
		$tab_objet = $tab_message = array();

		$i=0;
		$messages_code = array('WPSHOP_SIGNUP_MESSAGE', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', 'WPSHOP_ORDER_UPDATE_MESSAGE', 'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE', 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE');
		foreach ($messages_code as $code) {

			$object = get_option($code.'_OBJECT', null);
			$object = empty($object) ? constant($code.'_OBJECT') : $object;

			$message = get_option($code, null);
			$message = empty($message) ? constant($code) : $message;

			// Create post object
			$my_post = array(
					'post_title' => __($object, 'wpshop'),
					'post_content' => __($message, 'wpshop'),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
			);

			// Insert the post into the database
			$id = wp_insert_post( $my_post );

			update_option($code, $id);

			$tab_objet_en[$id] = $object;
			$tab_message_en[$id] = $message;

			$tab_objet[$id] = __($object, 'wpshop');
			$tab_message[$id] = __($message, 'wpshop');
			$i++;

		}


		$postmeta = array();
		$query = $wpdb->prepare("SELECT *, MESS_HISTO.hist_datetime FROM ".WPSHOP_DBT_MESSAGES." AS MESS INNER JOIN ".WPSHOP_DBT_HISTORIC." AS MESS_HISTO ON (MESS_HISTO.hist_message_id = MESS.mess_id)", '');
		$histo_message = $wpdb->get_results($query);
		$stored_message = array();
		foreach ( $histo_message as $message ) {
			$stored_message[$message->mess_title][] = $message;
		}

		foreach ( $stored_message as $message_subject => $messages ) {
			foreach ( $messages as $message ) {
				if ( in_array($message_subject, $tab_objet) ){
					$id_obj =  array_search($message_subject, $tab_objet);
				}
				elseif ( in_array($message_subject, $tab_objet_en) ){
					$id_obj =  array_search($message_subject, $tab_objet_en);
				}

				if( !empty($id_obj) ) {
					$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s ', $message->mess_user_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
					$user_post_id = $wpdb->get_var( $query );
					self::add_message($user_post_id, $message->mess_user_email, $message->mess_title, $message->mess_message, $id_obj, array('object_type'=>$message->mess_object_type, 'object_id'=>$message->mess_object_id), $message->hist_datetime);
				}
			}
		}


		$messages_code = array('WPSHOP_SIGNUP_MESSAGE', 'WPSHOP_ORDER_CONFIRMATION_MESSAGE', 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', 'WPSHOP_SHIPPING_CONFIRMATION_MESSAGE', 'WPSHOP_ORDER_UPDATE_MESSAGE', 'WPSHOP_ORDER_UPDATE_PRIVATE_MESSAGE', 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE');
		foreach ($messages_code as $code) {
			$object=constant($code.'_OBJECT');
			$object_components = explode('[', $object);
			if( (count($object_components) > 1) && !empty($object_components[1]) ) {
				$number_of_character = strlen($object_components[0]);
				$query = $wpdb->prepare("SELECT *, MESS_HISTO.hist_datetime FROM ".WPSHOP_DBT_MESSAGES." AS MESS INNER JOIN ".WPSHOP_DBT_HISTORIC." AS MESS_HISTO ON (MESS_HISTO.hist_message_id = MESS.mess_id) WHERE SUBSTRING(mess_title, 1, ".$number_of_character.") = '".$object_components[0]."' OR  SUBSTRING(mess_title, 1, ".$number_of_character.") = '".__($object_components[0], 'wpshop')."'", '');
				$histo_message = $wpdb->get_results($query);
				$stored_message = array();
				foreach ( $histo_message as $message ) {
					$stored_message[$message->mess_title][] = $message;
				}
				$query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE SUBSTRING(post_title, 1, ".$number_of_character.") = '".$object_components[0]."' OR  SUBSTRING(post_title, 1, ".$number_of_character.") = '".__($object_components[0], 'wpshop')."'", '');
				$post_id = $wpdb->get_var($query);
				foreach ( $stored_message as $message_subject => $messages ) {
					foreach ( $messages as $message ) {
						$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s ', $message->mess_user_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
						$user_post_id = $wpdb->get_var( $query );
						wpshop_messages::add_message($user_post_id, $message->mess_user_email, $message->mess_title, $message->mess_message, $post_id, array('object_type'=>$message->mess_object_type, 'object_id'=>$message->mess_object_id), $message->hist_datetime);
					}
				}
			}
		}


	}

	/** Set the custom colums
	 * @return array
	*/
	function messages_edit_columns($columns) {
	  $columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Name', 'wpshop'),
			'extract' =>__('Extract from the message','wpshop'),
			'date' => __('Creation date','wpshop'),
			'last_dispatch_date' => __('Last dispatch date','wpshop')
	  );

	  return $columns;
	}

	/** Give the content by column
	 * @return array
	*/
	function messages_custom_columns($column) {
		global $post;

		$metadata = get_post_custom();

		switch($column){
			case "extract":
				echo wp_trim_words($post->post_content, 55);
			break;
			case "last_dispatch_date":
				if(!empty($metadata['wpshop_message_last_dispatch_date'][0]))
					echo mysql2date('d F Y, H:i:s',$metadata['wpshop_message_last_dispatch_date'][0], true);
				else
					echo '-';
			break;
		}
	}

	/**
	*
	*/
	function save_message_custom_informations() {

















	}

	/** Store a new message
	* @return boolean
	*/
	function add_message($recipient_id=0, $email, $title, $message, $model_id, $object, $date = null) {
		$date = empty($date) ? current_time('mysql', 0) : $date;
		$object_empty = array('object_type'=>'','object_id'=>0);
		$object = array_merge($object_empty, $object);

		$historic = get_post_meta($recipient_id, '_wpshop_messages_histo_' .$model_id. '_' .substr($date, 0, 7), true);

		$historic[] = array(
			'mess_user_id' => $recipient_id,
			'mess_user_email' => $email,
			'mess_object_type' => $object['object_type'],
			'mess_object_id' => $object['object_id'],
			'mess_title' => $title,
			'mess_message' => $message,
			'mess_dispatch_date' => array($date)
		);

		update_post_meta($recipient_id, '_wpshop_messages_histo_' .$model_id. '_' .substr($date, 0, 7), $historic);
	}

	/**
	 * Add custom message to existing message list, for custom message output when saving new "custom post"
	 *
	 * @param array $messages Default message list
	 * @return array The new message list
	 */
	function update_wp_message_list( $messages) {
		$messages['post'][34070] = __('You have to fill all field marked with a red star');

		return $messages;
	}

	/** Create a custom message with $data array */
	function customMessage($string, $data, $model_name='', $duplicate_message=false) {
		$avant = array();
		$apres = array();
		
		foreach($data as $key => $value) {
			$avant[] = '['.$key.']';
			switch ($key) {
				case 'order_content' :
					$apres[] = ( $duplicate_message ) ? '[order_content]' : self::order_content_template_for_mail ( $data['order_id'] );
					break;
				case 'order_addresses' :
					$apres[] = ( $duplicate_message ) ? '[order_addresses]' : self::order_addresses_template_for_mail ( $data['order_id'] );
				break;
				case 'order_customer_comments' :
					$apres[] = ( $duplicate_message ) ? '[order_customer_comments]' : self::order_customer_comment_template_for_mail ( $data['order_id'] );
				break;
				default :
					$apres[] = $value;
					break;
			}
		}
		$string = str_replace($avant, $apres, $string);
		if ( ($model_name != 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE') ) {
			$string = preg_replace("/\[(.*)\]/Usi", '', $string);
		}
		return $string;
	}

	/** Envoie un email personnalis? */
	function wpshop_prepared_email($email, $model_name, $data=array(), $object=array()) {
		$model_id = get_option($model_name, 0);
		$post = get_post($model_id);
		$duplicate_message = '';
		if ( !empty($post) ) {
			$title = self::customMessage($post->post_title, $data, $model_name);
			$message = self::customMessage(nl2br($post->post_content), $data, $model_name);
			/* On envoie le mail */
			if ( array_key_exists('order_content', $data) || array_key_exists('order_addresses', $data) || array_key_exists('order_customer_comments', $data) ) {
				$duplicate_message = self::customMessage($post->post_content, $data, $model_name, true);
			}
			if ( !empty($email) ) {
				self::wpshop_email($email, $title, $message, $save=true, $model_id, $object, '', $duplicate_message);
			}
		}

	}

	/** Envoie un mail */
	function wpshop_email($email, $title, $message, $save=true, $model_id, $object=array(), $attachments='', $duplicate_message='') {
		global $wpdb;
		// Sauvegarde
		if($save) {
			$user = $wpdb->get_row('SELECT ID FROM '.$wpdb->users.' WHERE user_email="'.$email.'";');
			$user_id = $user ? $user->ID : 0;
			$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s ', $user_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
			$user_post_id = $wpdb->get_var( $query );
			if ( !empty($duplicate_message) ) {
				self::add_message($user_post_id, $email, $title, $duplicate_message, $model_id, $object);
			}
			else {
				self::add_message($user_post_id, $email, $title, $message, $model_id, $object);
			}

		}

		$emails = get_option('wpshop_emails', array());
		$noreply_email = $emails['noreply_email'];
		// Split the email to get the name
		$vers_nom = substr($email, 0, strpos($email,'@'));

		// Headers du mail
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8\r\n";
		$headers .= 'From: '.get_bloginfo('name').' <'.$noreply_email.'>' . "\r\n";
		// Mail en HTML
		@wp_mail($email, $title, $message, $headers, $attachments);
	}

	/*
	 * Return a table which display the order content to send by e-mail
	 */
	function order_content_template_for_mail ( $order_id ) {
		$message = '';
		if ( !empty($order_id) ) {
			$currency_code = wpshop_tools::wpshop_get_currency(false);
			$orders_infos = get_post_meta($order_id, '_order_postmeta', true);
			$message .= wpshop_display::display_template_element('administrator_order_email_head', '');
			if ( !empty($orders_infos['order_items']) ) {
				foreach ( $orders_infos['order_items'] as $key=>$item) {
					$tpl_component['ITEM_REF'] = $item['item_ref'];
					$tpl_component['ITEM_NAME'] = $item['item_name'];
					if ( !empty($key) ) {
						$tpl_component['ITEM_NAME'] .= '<br/>';
						$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($key, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
						$output_order = array();
						if ( count($product_attribute_order_detail) > 0 ) {
							foreach ( $product_attribute_order_detail as $product_attr_group_id => $product_attr_group_detail) {
								foreach ( $product_attr_group_detail['attribut'] as $position => $attribute_def) {
									if ( !empty($attribute_def->code) )
										$output_order[$attribute_def->code] = $position;
								}
							}
						}
						$variation_attribute_ordered = wpshop_products::get_selected_variation_display( $item['item_meta'], $output_order, 'invoice_print', 'common');
						ksort($variation_attribute_ordered['attribute_list']);
						$tpl_component['CART_PRODUCT_MORE_INFO'] = '';
						foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) {
							$tpl_component['CART_PRODUCT_MORE_INFO'] .= $attribute_variation_to_output;
						}
						$tpl_component['ITEM_NAME'] .= !empty($tpl_component['CART_PRODUCT_MORE_INFO']) ? wpshop_display::display_template_element('invoice_row_item_detail', $tpl_component, array('page' => 'admin_email_summary','type' => 'email_content','id' => 'product_option'), 'common') : '';
					}
					$tpl_component['ITEM_QTY'] = $item['item_qty'];
					$tpl_component['ITEM_PU_HT'] = number_format((float)$item['item_pu_ht'], 2, '.', ''). ' '.$currency_code;
					$tpl_component['TOTAL_HT'] = number_format((float)$item['item_total_ht'], 2, '.', ''). ' '.$currency_code;
					$message .= wpshop_display::display_template_element('line_administrator_order_email', $tpl_component);
				}
			}
			$message .= '<tr height="40" valign="middle">';
			$message .= '<td colspan="4" align="right">' .__('Total ET', 'wpshop'). '</td>';
			$message .= '<td align="center">' .number_format((float)$orders_infos['order_total_ht'], 2, '.', ''). ' '.$currency_code.'</td>';
			$message .= '</tr>';


			if ( !empty($orders_infos['order_tva']) ) {
				foreach ( $orders_infos['order_tva'] as $rate=>$montant ) {
					$tpl_component['TVA_RATE'] = $rate;
					$tpl_component['TVA'] = number_format((float)$montant, 2, '.', ''). ' '.$currency_code;
					$message .= wpshop_display::display_template_element('tva_administrator_order_email', $tpl_component);
				}
			}
			$tpl_component['TOTAL_BEFORE_DISCOUNT'] = number_format((float)$orders_infos['order_grand_total_before_discount'], 2, '.', ''). ' '.$currency_code;
			$tpl_component['TOTAL_SHIPPING_COST'] = number_format((float)$orders_infos['order_shipping_cost'], 2, '.', ''). ' '.$currency_code;
			$tpl_component['TOTAL_ATI'] = number_format((float)$orders_infos['order_grand_total'], 2, '.', ''). ' '.$currency_code;
			$message .= wpshop_display::display_template_element('total_order_administrator_order_email', $tpl_component);
		}
		return $message;
	}

	/*
	 * Return a table which display billing and shipping addresses used in the order to send by e-mail
	 */
	function order_addresses_template_for_mail ( $order_id ) {
		global $wpdb;
		$message = '';
		if ( !empty($order_id) ) {
			$order_addresses = get_post_meta($order_id, '_order_info', true);
			foreach ( $order_addresses as $key=>$order_address ) {
				if ( !empty($order_address) ) {
					$tpl_components['ADDRESS_TYPE'] = ( !empty($key) && $key == 'billing' ) ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop');
					if ( !empty($order_address['address']['civility']) ) {
						$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $order_address['address']['civility']);
						$tpl_components['CUSTOMER_CIVILITY'] = $wpdb->get_var( $query );
					}
					$tpl_components['CUSTOMER_LAST_NAME'] = (!empty($order_address['address']['address_last_name']) ) ? $order_address['address']['address_last_name'] : '';
					$tpl_components['CUSTOMER_FIRST_NAME'] = (!empty($order_address['address']['address_first_name']) ) ? $order_address['address']['address_first_name'] : '';
					$tpl_components['CUSTOMER_ADDRESS'] = (!empty($order_address['address']['address']) ) ? $order_address['address']['address'] : '';
					$tpl_components['CUSTOMER_POSTCODE'] = (!empty($order_address['address']['postcode']) ) ? $order_address['address']['postcode'] : '';
					$tpl_components['CUSTOMER_CITY'] = (!empty($order_address['address']['city']) ) ? $order_address['address']['city'] : '';
					$tpl_components['CUSTOMER_STATE'] = (!empty($order_address['address']['state']) ) ? $order_address['address']['state'] : '';
					$country = '';
					foreach ( unserialize(WPSHOP_COUNTRY_LIST) as $key => $value ) {
						if ( !empty($order_address['address']['country']) && $key ==  $order_address['address']['country']) {
								$country = $value;
						}
					}
					$tpl_components['CUSTOMER_COUNTRY'] = $country;
					$message .= wpshop_display::display_template_element('address_order_email', $tpl_components);
				}
			}
		}
		return $message;
	}
	/*
	 * Return a table which display customer comments about the order to send by e-mail
	*/
	function order_customer_comment_template_for_mail ( $order_id ) {
		global $wpdb;
		$message = '';
		if ( !empty($order_id) ) {
			$query = $wpdb->prepare('SELECT post_excerpt FROM ' .$wpdb->posts. ' WHERE ID = %d', $order_id);
			$tpl_component['CUSTOMER_COMMENT'] = $wpdb->get_var( $query );
			$order_infos = get_post_meta($order_id, '_order_postmeta', true);
			if ( !empty($order_infos['order_key']) ) {
				$tpl_component['CUSTOMER_COMMENT_TITLE'] =  __('Comments about the order', 'wpshop');
			}
			else {
				$tpl_component['CUSTOMER_COMMENT_TITLE'] =  __('Comments about the quotation', 'wpshop');
			}
			$message .= wpshop_display::display_template_element('customer_comments_order_email', $tpl_component);
		}
		return $message;
	}

	/**
	 * Change the historic message stockage method
	 * @param int $message_type_id
	 */
	function support_histo_message_passive ( $message_type_id ) {
		global $wpdb;
		@ini_set('max_execution_time', '500');
		$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->postmeta. ' WHERE meta_key LIKE %s AND post_id = %d', 'wpshop_messages_histo_%', $message_type_id);
		$results = $wpdb->get_results( $query );
		$is_ok = true;
		if ( !empty($results) ) {
			foreach ( $results as $result ) {
				$date = str_replace('wpshop_messages_histo_', '', $result->meta_key);
				$return = false;
				if ( !empty($result->meta_value) ) {
					foreach ( unserialize($result->meta_value) as $message ) {
						$query = $wpdb->prepare('SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s ', $message['mess_user_id'], WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
						$user_post_id = $wpdb->get_var( $query );
						$historic = get_post_meta($message['mess_user_id'], '_wpshop_messages_histo_' .$result->post_id. '_' .$date, true);
						$historic [] = $message;
						$return = update_post_meta($user_post_id, '_wpshop_messages_histo_' .$result->post_id. '_' .$date, $historic);
						if ( $return == 0) {
							$is_ok = false;
						}
					}
				}
			}
			if ( $is_ok ) {
				//Delete old historic messages
				$wpdb->query("DELETE FROM " .$wpdb->postmeta. " WHERE meta_key LIKE 'wpshop_messages_histo\_%%' AND post_id = " .$message_type_id. "");
			}
		}
 	}

	function wpshop_messages_historic_correction () {
		global $wpdb;
		$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->postmeta. ' WHERE meta_key LIKE %s', '_wpshop_messages_histo_%');
		$messages_histo = $wpdb->get_results( $query );

		foreach ( $messages_histo as $message ) {
			$query_user = $wpdb->prepare( 'SELECT ID FROM ' .$wpdb->posts. ' WHERE post_author = %d AND post_type = %s',  $message->post_id, WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS);
			$user_post_id = $wpdb->get_var( $query_user );
			$wpdb->update($wpdb->postmeta, array('post_id' => $user_post_id ), array('meta_id' => $message->meta_id ) );
		}
	}
}

?>