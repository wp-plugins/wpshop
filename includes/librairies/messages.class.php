<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


class wpshop_messages {

	function __construct() {
		add_shortcode( 'order_customer_personnal_informations', array( &$this, 'order_personnal_informations') );

	}


	/**
	 *	Call wordpress function that declare a new term type in coupon to define the product as wordpress term (taxonomy)
	 */
	public static function create_message_type() {
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
			'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE,
			'posts_per_page' => '-1',
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

	/**
	 *
	 */
	function create_default_message() {
		/**	Get default messages defined into xml files 	*/
		$xml_default_emails = file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_emails.xml' );
		$default_emails = new SimpleXMLElement( $xml_default_emails );
		/**	Read default emails for options creation	*/
		foreach ( $default_emails->xpath( '//emails/email' ) as $email ) {
			if (  ( WPSHOP_DEFINED_SHOP_TYPE == (string)$email->attributes()->shop_type ) || ( 'sale' == WPSHOP_DEFINED_SHOP_TYPE ) ) {
				self::createMessage( (string)$email->attributes()->code, constant( (string)$email->subject ), (string)$email->content );
			}
		}
	}


	/* Prints the box content */
	function message_histo_box($post, $params) {
		$output  = '<div id="message_histo_container">';
		$output .= self::get_historic_message_by_type($post->ID);
		$output .= '</div>';
		echo $output;
	}

	function get_historic_message_by_type ( $message_type_id ) {
		global $wpdb;
		$output = '';
		if ( !empty($message_type_id) ) {
			/** Find in database all messsage for this type **/
			$query = $wpdb->prepare('SELECT * FROM ' .$wpdb->postmeta. ' WHERE meta_key LIKE %s ORDER BY meta_id DESC', '_wpshop_messages_histo_' .$message_type_id. '%');
			$messages = $wpdb->get_results( $query );
			if ( !empty($messages) ) {
				$tpl_component = array();
				foreach ( $messages as $message ) {
					$message_data = maybe_unserialize( $message->meta_value );
					$tpl_component['MESSAGE_USER_EMAIL'] = $message_data[0]['mess_user_email'];
					$tpl_component['MESSAGE_TITLE'] = $message_data[0]['mess_title'];
					$tpl_component['MESSAGE_CONTENT'] = $message_data[0]['mess_message'];
					$tpl_component['MESSAGE_DISPATCH_DATE'] = '';
					foreach( $message_data as $d ) {
                                                $tpl_component['MESSAGE_DISPATCH_DATE'] .= '<tr><td>' .$d['mess_user_email']. '</td><td>' . $d['mess_dispatch_date'][0] .'</td></tr>';
                                        }
 					$output .= wpshop_display::display_template_element('wpshop_admin_message_histo_display_each_element', $tpl_component, array(), 'admin');
				}
			}
		}
		return $output;
	}

	function message_info_box($post, $params) {
		global $wpdb;
		// USERS
		$users = wpshop_customer::getUserList();
		$select_users = '';
		foreach($users as $user) {
			if ($user->ID != 1) {
				$select_users .= '<option value="'.$user->ID.'">'.$user->user_login.'</option>';
			}
		}
		/** Check the message model **/
		$query = $wpdb->prepare('SELECT option_name FROM '. $wpdb->options .' WHERE option_value = %d AND option_name LIKE %s LIMIT 1', $post->ID, '%_MESSAGE');
		$model_name = $wpdb->get_var( $query);

		$output  = '<label>'.__('Recipient','wpshop').'</label><br />';
		$output .= '<select id="selected_recipient" name="selected_recipient" class="chosen_select">' .$select_users. '</select><br />';
		//$output .= __('Order id', 'wpshop'). ' <input type="text" name="wpshop_messages_histo_order_id" id="wpshop_messages_histo_order_id" class="shipping_rules_configuration_input" />';
		$output .= '<input type="hidden" name="wpshop_postid" id="wpshop_postid" value="'.$post->ID.'" />';
		$output .= '<input type="hidden" name="wpshop_message_model" id="wpshop_message_model" value="'.$model_name.'" />';
		$output .= '<br /><br /><span id="message_sender_loader" class="wpshopHide"><img src="' .WPSHOP_LOADING_ICON. '" alt="Loading" /></span><input type="button" class="button-primary alignright" value="'.__('Send the message','wpshop').'" id="sendMessage" /><br /><br />';

		echo $output;
	}

	function createMessage( $code, $object = "", $message = "" ) {
		$id = 0;

		$object = empty( $object ) ? constant( $code . '_OBJECT' ) : $object;
		$message = empty( $message ) ? constant( $code ) : $message;
		$message_option = get_option( $code, null );

		if ( empty( $message_option ) && !empty( $object ) && !empty( $message ) ) {
			$new_message = array(
				'post_title' 	=> __( $object , 'wpshop'),
				'post_content' 	=> self::customize_message( __( $message, 'wpshop' ) ),
				'post_status' 	=> 'publish',
				'post_author' 	=> 1,
				'post_type' 	=> WPSHOP_NEWTYPE_IDENTIFIER_MESSAGE
			);
			$id = wp_insert_post( $new_message );

			update_option( $code, $id );
		}
		else {
			$id = $message_option;
		}

        return $id;
	}

	function customize_message( $message ) {
		if( !empty($message) ) {
			$tpl_component = array();
			$tpl_component['MESSAGE'] = $message;

			$message = wpshop_display::display_template_element('message_general_template', $tpl_component );

			unset( $tpl_component );
		}
		return $message;
	}

	/**
	 * Transfert des messages des tables crées vers la table de wordpress
	 */
	function importMessageFromLastVersion() {
		global $wpdb;
		$tab_objet = $tab_message = array();

		/**	Get default messages defined into xml files 	*/
		$xml_default_emails = file_get_contents( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/assets/datas/default_emails.xml' );
		$default_emails = new SimpleXMLElement( $xml_default_emails );

		/**	Read default emails for options creation	*/
		foreach ( $default_emails->xpath( '//emails/email' ) as $email ) {
			$subject = (string)$email->subject;
			$message = (string)$email->content;
			$message_id = self::createMessage( (string)$email->attributes()->code, $subject, $message );

			$tab_objet_en[ $id ] = $subject;
			$tab_message_en[ $id ] = $message;

			$tab_objet[ $id ] = __( $subject, 'wpshop' );
			$tab_message[ $id ] = __( $message, 'wpshop' );
		}



		$postmeta = array();
		$query = $wpdb->prepare("SELECT *, MESS_HISTO.hist_datetime FROM ".WPSHOP_DBT_MESSAGES." AS MESS INNER JOIN ".WPSHOP_DBT_HISTORIC." AS MESS_HISTO ON (MESS_HISTO.hist_message_id = MESS.mess_id)", '');
		$histo_message = $wpdb->get_results($query);
		$stored_message = array();
		foreach ( $histo_message as $message ) {
			$stored_message[ $message->mess_title ][] = $message;
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
			$object = constant($code.'_OBJECT');
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


	/** Store a new message
	* @return boolean
	*/
	function add_message($recipient_id=0, $email, $title, $message, $model_id, $object, $date = null) {
		$date = empty($date) ? current_time('mysql', 0) : $date;
		$object_empty = array('object_type'=>'','object_id'=>0);
		$object = array_merge($object_empty, $object);

		$historic = get_post_meta($recipient_id, '_wpshop_messages_histo_' .$model_id. '_' .substr($date, 0, 7), true);
		$data_to_insert = array(
			'mess_user_id' => $recipient_id,
			'mess_user_email' => $email,
			'mess_object_type' => $object['object_type'],
			'mess_object_id' => $object['object_id'],
			'mess_title' => $title,
			'mess_message' => $message,
			'mess_dispatch_date' => array($date)
		);
                $historic[] = $data_to_insert;

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

		$logo_option = get_option( 'wpshop_logo' );

		$data['your_shop_logo'] = ( !empty($logo_option) ) ? '<img src="'.$logo_option.'" alt="' .get_bloginfo('name'). '" />' : '';

		foreach($data as $key => $value) {
			$avant[] = '['.$key.']';
			switch ($key) {
				case 'order_content' :
					$apres[] = ( $duplicate_message ) ? '[order_content]' : self::order_content_template_for_mail ( $data['order_id'] );
					break;
				case 'order_addresses' :
					$apres[] = ( $duplicate_message ) ? '[order_addresses]' : self::order_addresses_template_for_mail ( $data['order_id'] );
				break;

				case 'order_billing_address' :
					$apres[] = ( $duplicate_message ) ? '[order_billing_address]' : self::order_addresses_template_for_mail ( $data['order_id'], 'billing' );
				break;

				case 'order_shipping_address' :
					$apres[] = ( $duplicate_message ) ? '[order_shipping_address]' : self::order_addresses_template_for_mail ( $data['order_id'], 'shipping' );
				break;

				case 'order_customer_comments' :
					$apres[] = ( $duplicate_message ) ? '[order_customer_comments]' : self::order_customer_comment_template_for_mail ( $data['order_id'] );
				break;
				case 'order_personnal_informations' :
					$apres[] = ( $duplicate_message ) ? '[order_personnal_informations]' : self::order_personnal_informations();
				break;
				default :
					$apres[] = $value;
					break;
			}
		}
		$string = str_replace($avant, $apres, $string);

		$string = apply_filters( 'wps_more_customized_message', $string, $data, $duplicate_message );

		if ( ($model_name != 'WPSHOP_NEW_ORDER_ADMIN_MESSAGE') ) {
			$string = preg_replace("/\[(.*)\]/Usi", '', $string);
		}

		return $string;
	}

	/** Envoie un email personnalis? */
	function wpshop_prepared_email($email, $model_name, $data=array(), $object=array(), $attached_file = '') {
		global $wpdb;
		$model_id = get_option($model_name, 0);
		$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE ID = %s', $model_id);
		$post_message = $wpdb->get_row( $query );
		$duplicate_message = '';
		if ( !empty($post_message) ) {
			$title = self::customMessage($post_message->post_title, $data, $model_name);
			$message = self::customMessage($post_message->post_content, $data, $model_name);
			/* On envoie le mail */
			if ( array_key_exists('order_content', $data) || array_key_exists('order_addresses', $data) || array_key_exists('order_customer_comments', $data) ) {
				$duplicate_message = self::customMessage($post_message->post_content, $data, $model_name, true);
			}
			if ( !empty($email) ) {
				self::wpshop_email($email, $title, $message, $save=true, $model_id, $object, $attached_file, $duplicate_message);
			}
		}
	}

	/** Envoie un mail */
	function wpshop_email($email, $title, $message, $save=true, $model_id, $object=array(), $attachments='', $duplicate_message='') {
		global $wpdb;
		// Sauvegarde
		if($save) {
			$user = $wpdb->get_row('SELECT ID FROM '.$wpdb->users.' WHERE user_email="'.$email.'";');
			$user_id = $user ? $user->ID : get_current_user_id();
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
		/** Notification **/
// 		$headers .= 'Reply-To:<' .$noreply_email.'>\r\n';
// 		$headers .= 'Return-Receipt-To:<'.$noreply_email.'>\r\n';
// 		$headers .= 'Disposition-Notification-To:<'.$noreply_email.'>\r\n';

		// Mail en HTML
		@wp_mail($email, $title, $message, $headers, $attachments);


		if ( !empty($attachments) ) {
			unlink( $attachments );
		}
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
						if ( count($product_attribute_order_detail) > 0 && is_array($product_attribute_order_detail) ) {
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
					$tpl_component['ITEM_PU_TTC'] = number_format((float)$item['item_pu_ttc'], 2, '.', ''). ' '.$currency_code;
					$tpl_component['TOTAL_HT'] = number_format((float)$item['item_total_ht'], 2, '.', ''). ' '.$currency_code;
					$tpl_component['TOTAL_TTC'] = number_format((float)$item['item_total_ttc'], 2, '.', ''). ' '.$currency_code;
					$message .= wpshop_display::display_template_element('line_administrator_order_email', $tpl_component);
				}
			}

			$tpl_component['TOTAL_SHIPPING_COST'] = number_format((float)$orders_infos['order_shipping_cost'], 2, '.', ''). ' '.$currency_code;
			$tpl_component['TOTAL_BEFORE_DISCOUNT'] = number_format((float)$orders_infos['order_grand_total_before_discount'], 2, '.', ''). ' '.$currency_code;
			$tpl_component['TOTAL_ATI'] = number_format((float)$orders_infos['order_grand_total'], 2, '.', ''). ' '.$currency_code;

			$message .= wpshop_display::display_template_element('total_ht_administrator_order_email', array('TOTAL_HT' => number_format((float)$orders_infos['order_total_ht'], 2, '.', ''). ' '.$currency_code, 'TOTAL_ATI' => $tpl_component['TOTAL_ATI']));

			if ( !empty($orders_infos['order_tva']) ) {
				foreach ( $orders_infos['order_tva'] as $rate=>$montant ) {
					$tpl_component['TVA_RATE'] = ( !empty($rate) && $rate == 'VAT_shipping_cost') ? __('on Shipping cost', 'wpshop').' '.WPSHOP_VAT_ON_SHIPPING_COST : $rate;
					$tpl_component['TVA'] = number_format((float)$montant, 2, '.', ''). ' '.$currency_code;
					$message .= wpshop_display::display_template_element('tva_administrator_order_email', $tpl_component);
				}
			}


			$tpl_component['ORDER_TO_PAY_NOW'] = number_format( $orders_infos['order_amount_to_pay_now'], 2, '.','') .' '.$currency_code;
			$tpl_component['ALREADY_PAID'] = 0;
			if( !empty($orders_infos['order_payment']) && !empty($orders_infos['order_payment']) && !empty($orders_infos['order_payment']['received']) ) {
				foreach( $orders_infos['order_payment']['received'] as $payment ) {
					if( $payment['status'] == 'payment_received') {
						$tpl_component['ALREADY_PAID'] += $payment['received_amount'];
					}
				}
			}
			$tpl_component['ALREADY_PAID'] = number_format( $tpl_component['ALREADY_PAID'], 2, '.', '').' '.$currency_code;

			$tpl_component = apply_filters( 'wps_email_order_content', $tpl_component, $orders_infos );

			$message .= wpshop_display::display_template_element('total_order_administrator_order_email', $tpl_component);
		}
		return $message;
	}

	/*
	 * Return a table which display billing and shipping addresses used in the order to send by e-mail
	 */
	function order_addresses_template_for_mail ( $order_id, $address_type = '' ) {
		global $wpdb;
		$shipping_option = get_option( 'wpshop_shipping_address_choice' );
		$display_shipping = ( !empty($shipping_option) && !empty($shipping_option['activate']) ) ? true : false;
		$message = '';
		if ( !empty($order_id) ) {
			$order_addresses = get_post_meta($order_id, '_order_info', true);
			if ( !empty($order_addresses) ) {
				foreach ( $order_addresses as $key=>$order_address ) {
					if ( !empty($order_address) && ( empty($address_type) || $address_type == $key ) ) {

						if( $key != 'shipping' || ($key == 'shipping' && $display_shipping) ) {
							$tpl_components['ADDRESS_TYPE'] = ( !empty($key) && $key == 'billing' ) ? __('Billing address', 'wpshop') : __('Shipping address', 'wpshop');
							if ( !empty($order_address['address']['civility']) ) {
								$query = $wpdb->prepare('SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $order_address['address']['civility']);
								$tpl_components['CUSTOMER_CIVILITY'] = $wpdb->get_var( $query );
							}
							else {
								$tpl_components['CUSTOMER_CIVILITY'] = '';
							}
							$tpl_components['CUSTOMER_LAST_NAME'] = (!empty($order_address['address']['address_last_name']) ) ? $order_address['address']['address_last_name'] : '';
							$tpl_components['CUSTOMER_FIRST_NAME'] = (!empty($order_address['address']['address_first_name']) ) ? $order_address['address']['address_first_name'] : '';
							$tpl_components['CUSTOMER_COMPANY'] = (!empty($order_address['address']['company']) ) ? $order_address['address']['company'] : '';
							$tpl_components['CUSTOMER_ADDRESS'] = (!empty($order_address['address']['address']) ) ? $order_address['address']['address'] : '';
							$tpl_components['CUSTOMER_POSTCODE'] = (!empty($order_address['address']['postcode']) ) ? $order_address['address']['postcode'] : '';
							$tpl_components['CUSTOMER_CITY'] = (!empty($order_address['address']['city']) ) ? $order_address['address']['city'] : '';
							$tpl_components['CUSTOMER_STATE'] = (!empty($order_address['address']['state']) ) ? $order_address['address']['state'] : '';
							$tpl_components['CUSTOMER_PHONE'] = (!empty($order_address['address']['phone']) ) ? ' Tel. : '.$order_address['address']['phone'] : '';
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


	function order_personnal_informations() {
		global $wpdb;
		$user_id = get_current_user_id();
		$tpl_component = array();
		$message = '';
		$customer_entity = wpshop_entities::get_entity_identifier_from_code( WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS );
		if( !empty($customer_entity) ) {

			$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_SET. ' WHERE entity_id = %d AND status = %s', $customer_entity, 'valid' );
			$attributes_sets = $wpdb->get_results( $query );

			if( !empty($attributes_sets) ) {
				foreach( $attributes_sets as $attributes_set ){
					$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_GROUP. ' WHERE attribute_set_id = %d AND status = %s', $attributes_set->id, 'valid');
					$attributes_groups = $wpdb->get_results( $query );

					if( !empty($attributes_groups) ) {
						foreach( $attributes_groups as $attribute_group ) {
							$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE_DETAILS. ' WHERE entity_type_id = %d AND attribute_set_id = %d AND attribute_group_id = %d AND status = %s ORDER BY position', $customer_entity, $attributes_set->id, $attribute_group->id, 'valid' );
							$attribute_ids = $wpdb->get_results( $query );

							if( !empty($attribute_ids) ) {
								foreach( $attribute_ids as $attribute_id ) {
									//$attribute_def = wpshop_attributes::getElement( $attribute_id->attribute_id, "'valid''", 'id');
									$query = $wpdb->prepare( 'SELECT * FROM '.WPSHOP_DBT_ATTRIBUTE. ' WHERE id = %d AND status = %s', $attribute_id->attribute_id, 'valid' );
									$attribute_def = $wpdb->get_row( $query );

									if( !empty($attribute_def) ) {
										$user_attribute_meta = get_user_meta( $user_id, $attribute_def->code, true );

										if( in_array( $attribute_def->frontend_input, array( 'checkbox', 'radio', 'select') ) ) {
											$query = $wpdb->prepare( 'SELECT label FROM '.WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $user_attribute_meta);
											$value = $wpdb->get_var( $query );
										}
										else {
											$value = $user_attribute_meta;
										}
										if( $attribute_def->code != 'user_pass' ) {
											$message .= wpshop_display::display_template_element('order_email_customer_informations_line', array('ATTRIBUTE_NAME' => $attribute_def->frontend_label, 'ATTRIBUTE_VALUE' => $value) );
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$output = wpshop_display::display_template_element('order_email_customer_informations', array('CONTENT' => $message) );
		return $output;
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

	function get_histo_messages_per_customer( $args ) {
		global $wpdb;
		$output = '';
		$messages_data = array();
		if( !empty($args) && !empty($args['customer_id']) ) {
			//Get customer Post entity ID
			$query = $wpdb->prepare( 'SELECT ID FROM '. $wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_CUSTOMERS, $args['customer_id']  );
			$customer_id = $wpdb->get_var( $query );

			if( !empty($customer_id) ) {
				$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->postmeta. ' WHERE post_id = %d AND meta_key LIKE %s ORDER BY meta_id', $customer_id, '_wpshop_messages_histo_%' );
				$messages = $wpdb->get_results( $query );
				if( !empty($messages) ) {
					foreach( $messages as $message ) {
						if( empty($messages_data[$message->post_id]) ) {
							$messages_data[$message->post_id] = array('date' => '',
																	  'user_email' => '',
																	  'mail_object' => '',
																	  'mail_content' => ''
																		);
						}
					}
				}
			}

		}
		return $output;
	}

}

?>