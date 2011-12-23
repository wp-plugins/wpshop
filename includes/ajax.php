<?php
/**
* Plugin ajax request management.
*
*	Every ajax request will be send to this page wich will return the request result regarding all the parameters
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage includes
*/

/**
*	Wordpress - Ajax functionnality activation
*/
DEFINE('DOING_AJAX', true);
/**
*	Wordpress - Specify that we are in wordpress admin
*/
DEFINE('WP_ADMIN', true);
/**
*	Wordpress - Main bootstrap file that load wordpress basic files
*/
require_once('../../../../wp-load.php');
/**
*	Wordpress - Admin page that define some needed vars and include file
*/
require_once(ABSPATH . 'wp-admin/includes/admin.php');


/**
*	First thing we define the main directory for our plugin in a super global var	
*/
DEFINE('WPSHOP_PLUGIN_DIR', basename(dirname(__FILE__)));
/**
*	Include the different config for the plugin	
*/
require_once(WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/includes/config.php' );
/**
*	Include the file which includes the different files used by all the plugin
*/
require_once(WPSHOP_INCLUDES_DIR.'include.php');

/*	Get the different resquest vars to sanitize them before using	*/
$method = wpshop_tools::varSanitizer($_REQUEST['post'], '');
$action = wpshop_tools::varSanitizer($_REQUEST['action'], '');

/*	Element code define the main element type we are working on	*/
$elementCode = wpshop_tools::varSanitizer($_REQUEST['elementCode']);

/*	Element code define the secondary element type we are working on. For example when working on elementCode:Document elementType:product, we are working on the document for products	*/
$elementType = wpshop_tools::varSanitizer($_REQUEST['elementType']);
$elementIdentifier = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);

/*	First look at the request method Could be post or get	*/
switch($method)
{
	case 'true':
	{/*	In case request method is equal to true, it means that we are working with post request method	*/
		/*	Look at the element type we have to work on	*/
		switch($elementCode)
		{
			case 'attribute_set':
			{
				switch($elementType)
				{
					case 'attributeSetSection':
					{
						switch($action)
						{
							case 'saveNewAttributeSetSection';
							{
								$attributeSetSectionName = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionName']);
								$attributeSetInfos = array();
								$attributeSetInfos['id'] = '';
								$attributeSetInfos['status'] = 'valid';
								$attributeSetInfos['attribute_set_id'] = $elementIdentifier;
								$query = $wpdb->prepare("SELECT MAX(position) + 1 AS LAST_GROUP_POSITION FROM ". WPSHOP_DBT_ATTRIBUTE_GROUP . " WHERE attribute_set_id = %s", $elementIdentifier);
								$attributeSetInfos['position'] = $wpdb->get_var($query);
								$attributeSetInfos['creation_date'] = date('Y-m-d H:i:s');
								$attributeSetInfos['code'] = wpshop_tools::slugify($attributeSetSectionName, array('noAccent', 'noSpaces', 'lowerCase'));
								$attributeSetInfos['name'] = $attributeSetSectionName;
								$attributeSetSectionCreation = wpshop_database::save($attributeSetInfos, WPSHOP_DBT_ATTRIBUTE_GROUP);
								if($attributeSetSectionCreation == 'done')
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('New section has been created successfully', 'wpshop');
								}
								else
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while saving new section', 'wpshop');
								}
								echo wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . '<script type="text/javascript" >wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);</script>';
							}
							break;

							case 'editAttributeSetSection';
							{
								$attributeSetSectionName = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionName']);
								$attributeSetSectionId = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionId']);
								$attributeSetInfos = array();
								$attributeSetInfos['last_update_date'] = date('Y-m-d H:i:s');
								$attributeSetInfos['name'] = $attributeSetSectionName;
								$attributeSetSectionCreation = wpshop_database::update($attributeSetInfos, $attributeSetSectionId, WPSHOP_DBT_ATTRIBUTE_GROUP);
								if($attributeSetSectionCreation == 'done')
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('The section has been updated successfully', 'wpshop');
								}
								else
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while updating the section', 'wpshop');
								}
								echo wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . '<script type="text/javascript" >wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);</script>';
							}
							break;

							case 'deleteAttributeSetSection';
							{
								$attributeSetSectionName = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionName']);
								$attributeSetSectionId = wpshop_tools::varSanitizer($_REQUEST['attributeSetSectionId']);
								$attributeSetInfos = array();
								$attributeSetInfos['status'] = 'deleted';
								$attributeSetInfos['last_update_date'] = date('Y-m-d H:i:s');
								$attributeSetInfos['name'] = $attributeSetSectionName;
								$attributeSetSectionCreation = wpshop_database::update($attributeSetInfos, $attributeSetSectionId, WPSHOP_DBT_ATTRIBUTE_GROUP);
								if($attributeSetSectionCreation == 'done')
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_SUCCES_ICON . '\' alt=\'action_success\' class=\'wpshopPageMessage_Icon\' />' . __('The section has been successfully been deleted', 'wpshop');
								}
								else
								{
									$attributeSetSectionCreation_Result = '<img src=\'' . WPSHOP_ERROR_ICON . '\' alt=\'action_error\' class=\'wpshopPageMessage_Icon\' />' . __('An error occured while deleting the section', 'wpshop');
								}
								echo wpshop_attributes_set::attributeSetDetailsManagement($elementIdentifier) . '<script type="text/javascript" >wpshopShowMessage("' . $attributeSetSectionCreation_Result . '");hideShowMessage(5000);</script>';
							}
							break;
						}
					}
					break;
				}
			}
			break;

			case 'attribute_unit_management':
			{
				switch($action)
				{
					case 'load_attribute_unit_list':
					{
						$current_group = wpshop_tools::varSanitizer($_POST['current_group']);
						$input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group($current_group);
						$input_def['type'] = 'select';
						$input_def['name'] = '_default_unit';
						echo wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
					}
					break;
					case 'load_unit_interface':
					{
						echo '
<div id="wpshop_unit_main_listing_interface" >
	<ul>
		<li id="wpshop_unit_list_tab" ><a href="#wpshop_unit_list" >' . __('Unit', 'wpshop') . '</a></li>
		<li id="wpshop_unit_group_list_tab" ><a href="#wpshop_unit_group_list" >' . __('Unit group', 'wpshop') . '</a></li>
	</ul>
	<div id="wpshop_unit_list" >' . wpshop_attributes_unit::elementList() . '</div>
	<div id="wpshop_unit_group_list" >' . wpshop_attributes_unit::unit_group_list() . '</div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_unit_main_listing_interface").tabs();
	});
</script>';
					}
					break;


					case 'load_attribute_units':
					{
						echo wpshop_attributes_unit::elementList();
					}
					break;
					case 'add_attribute_unit':
					case 'edit_attribute_unit':
					{
						$atribute_unit = '';
						if($action == 'edit_attribute_unit'){
							$atribute_unit = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
						}
						echo wpshop_attributes_unit::elementEdition($atribute_unit);
					}
					break;
					case 'save_new_attribute_unit':
					{
						$save_output = '';

						$attribute_unit_informations['id'] = '';
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
						$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
						$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
						$attribute_unit_informations['group_id'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);

						$save_unit_result = wpshop_database::save($attribute_unit_informations, WPSHOP_DBT_ATTRIBUTE_UNIT);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes_unit::elementList();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The new unit has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes_unit::elementEdition();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during new unit saving', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
					}
					break;
					case 'update_attribute_unit':
					{
						$save_output = '';

						$attributeUnitId = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['id']);
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['status']);
						$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['name']);
						$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['unit']);
						$attribute_unit_informations['group_id'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['group_id']);
						$attribute_unit_informations['is_default_of_group'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT]['is_default_of_group']);
						if($attribute_unit_informations['is_default_of_group'] == 'yes'){
							$query = $wpdb->prepare("UPDATE " . WPSHOP_DBT_ATTRIBUTE_UNIT . " SET is_default_of_group = 'no' WHERE group_id = %d", $attribute_unit_informations['group_id']);
							$wpdb->query($query);
						}

						$save_unit_result =  wpshop_database::update($attribute_unit_informations, $attributeUnitId, WPSHOP_DBT_ATTRIBUTE_UNIT);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes_unit::elementList();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes_unit::elementEdition($attributeUnitId);
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit saving', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
					}
					break;
					case 'delete_attribute_unit':
					{
						$unit_id = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
						$save_output = '';

						$attribute_unit_informations['status'] = 'deleted';
						$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
						$save_unit_result = wpshop_database::update($attribute_unit_informations, $unit_id, WPSHOP_DBT_ATTRIBUTE_UNIT);
						if($save_unit_result == 'done'){
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action succesful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit has been deleted succesfully', 'wpshop');
						}
						else{
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit deletion', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . wpshop_attributes_unit::elementList();
					}
					break;


					case 'load_attribute_unit_groups':
					{
						echo wpshop_attributes_unit::unit_group_list();
					}
					break;
					case 'add_attribute_unit_group':
					case 'edit_attribute_unit_group':
					{
						$atribute_unit_group = '';
						if($action == 'edit_attribute_unit_group'){
							$atribute_unit_group = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
						}
						echo wpshop_attributes_unit::unit_group_edition($atribute_unit_group);
					}
					break;
					case 'save_new_attribute_unit_group':
					{
						$save_output = '';

						$attribute_unit_informations['id'] = '';
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
						$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

						$save_unit_result = wpshop_database::save($attribute_unit_informations, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes_unit::unit_group_list();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The new unit group has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes_unit::unit_group_edition();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during new unit group saving', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
					}
					break;
					case 'update_attribute_unit_group':
					{
						$save_output = '';

						$attributeUnitId = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['id']);
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['status']);
						$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['name'] = wpshop_tools::varSanitizer($_POST[WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP]['name']);

						$save_unit_result =  wpshop_database::update($attribute_unit_informations, $attributeUnitId, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes_unit::unit_group_list();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit group has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes_unit::unit_group_edition($attributeUnitId);
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit group saving', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
					}
					break;
					case 'delete_attribute_unit_group':
					{
						$unit_id = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
						$save_output = '';

						$attribute_unit_informations['status'] = 'deleted';
						$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
						$save_unit_result = wpshop_database::update($attribute_unit_informations, $unit_id, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP);
						if($save_unit_result == 'done'){
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action succesful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit group has been deleted succesfully', 'wpshop');
						}
						else{
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during unit group deletion', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . wpshop_attributes_unit::unit_group_list();
					}
					break;
				}
			}
			break;

			case 'product_attachment':
			{
				$attachement_type = wpshop_tools::varSanitizer($_REQUEST['attachement_type']);
				$part_to_reload = wpshop_tools::varSanitizer($_REQUEST['part_to_reload']);
				echo wpshop_products::product_attachement_by_type($elementIdentifier, $attachement_type, 'media-upload.php?post_id=' . $elementIdentifier . '&amp;tab=library&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=566') . '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#' . $part_to_reload . '").attr("src", "' . WPSHOP_MEDIAS_ICON_URL . 'reload_vs.png");
	});
</script>';
			}
			break;

			case 'templates':
			{
				switch($action)
				{
					case 'reset_template_files':
					{
						$reset_info = wpshop_tools::varSanitizer($_REQUEST['reset_info']);
						$last_reset_infos = '';
						wpshop_display::check_template_file(true);
						if($reset_info != ''){
							$infos = explode('dateofreset', $reset_info);
							if($infos[0] > 0){
								$user_first_name = get_user_meta($infos[0], 'first_name', true);
								$user_first_name = ($user_first_name != '') ? $user_first_name : __('First name not defined', 'wpshop');
								$user_last_name = get_user_meta($infos[0], 'last_name', true);
								$user_last_name = ($user_last_name != '') ? $user_last_name : __('Last name not defined', 'wpshop');
								$last_reset_infos = sprintf(__('Last template reset was made by %s on %s', 'wpshop'), $user_first_name . '&nbsp;' . $user_last_name, mysql2date('d/m/Y H:i', $infos[1], true));
							}
							$wpshop_display_option = get_option('wpshop_display_option');
							$wpshop_display_option['wpshop_display_reset_template_element'] = $reset_info;
							update_option('wpshop_display_option', $wpshop_display_option);
						}

						echo $last_reset_infos;
					}
					break;
				}
			}
			break;
			
			case 'speedSearch':
				switch($_REQUEST['searchType']) 
				{
					case 'products':
						if(empty($_REQUEST['search']))
							$data = wpshop_products::product_list(true);
						else $data = wpshop_products::product_list(true, $_REQUEST['search']);
					break;
					
					case 'attr':
						if(empty($_REQUEST['search']))
							$data = wpshop_products::product_list_attr(true);
						else $data = wpshop_products::product_list_attr(true, $_REQUEST['search']);
					break;
					
					case 'groups':
						if(empty($_REQUEST['search']))
							$data = wpshop_products::product_list_group_attr(true);
						else $data = wpshop_products::product_list_group_attr(true, $_REQUEST['search']);
					break;
					
					case 'cats':
						if(empty($_REQUEST['search']))
							$data = wpshop_categories::product_list_cats(true);
						else $data = wpshop_categories::product_list_cats(true, $_REQUEST['search']);
					break;
					
					default:
						/*	Default case is get request method	*/
					break;
				}
				echo empty($data) ? __('No match', 'wpshop') : $data;
			break;
			
			case 'ajax_cartAction':
				if(!empty($_REQUEST['pid'])):
					switch($_REQUEST['action']) 
					{
						case 'addProduct':
							$return = $GLOBALS['cart']->add_to_cart($_REQUEST['pid'], 1);
							if($return == 'success') {
								$cart_page_url = get_permalink(get_option('wpshop_cart_page_id'));
								echo json_encode(array(true, '<h1>'.__('Your product has been sucessfuly added to your cart', 'wpshop').'</h1><br /><a href="'.$cart_page_url.'">'.__('View my cart','wpshop').'</a> <input type="button" class="button-secondary closeAlert" value="'.__('Continue shopping','wpshop').'" />'));
							}
							else echo json_encode(array(false, $return));
						break;
						
						case 'setProductQty':
							if(isset($_REQUEST['qty'])):
								echo $GLOBALS['cart']->set_product_qty($_REQUEST['pid'],$_REQUEST['qty']);
							else:
								echo __('Parameters error.','wpshop');
							endif;
						break;
					}
				else:
					echo 'Erreur produit';
				endif;
			break;
			
			case 'ajax_markAsShipped':
				if(!empty($_REQUEST['oid']) && isset($_REQUEST['trackingNumber'])):
				
					$order_id = $_REQUEST['oid'];
					
					// On met à jour le statut de la commande
					$order = get_post_meta($order_id, '_order_postmeta', true);
					$order['order_status'] = 'shipped';
					// On enregistre le numéro de suivi
					$order['order_trackingNumber'] = empty($_REQUEST['trackingNumber'])?null:$_REQUEST['trackingNumber'];
					$order['order_shipping_date'] = date('Y-m-d H:i:s');
					update_post_meta($order_id, '_order_postmeta', $order);
					
					// Si paiement par chèque
					if ($order['payment_method'] == 'check') {
						// Reduction des stock produits
						$order = get_post_meta($order_id, '_order_postmeta', true);
						foreach($order['order_items'] as $o) {
							wpshop_products::reduce_product_stock_qty($o['id'], $o['qty']);
						}
					}
					
					// EMAIL DE CONFIRMATION -------
					
					$order_info = get_post_meta($_REQUEST['oid'], '_order_info', true);
					$email = $order_info['billing']['email'];
					$first_name = $order_info['billing']['first_name'];
					$last_name = $order_info['billing']['last_name'];
										
					// Envoie du message de confirmation de paiement au client
					$title = __('Your order has been shipped', 'wpshop');
					$message = sprintf(__('Hello %s %s, this email confirms that your order has just been shipped. Thank you for your loyalty. Have a good day.', 'wpshop'), $first_name, $last_name);
					@mail($email, $title, $message);
					
					// FIN EMAIL DE CONFIRMATION -------
										
					echo json_encode(array(true, 'shipped'));
				else:
					echo json_encode(array(false, __('Incorrect order request', 'wpshop')));
				endif;
			break;
			
			case 'ajax_loadOrderTrackNumberForm':
				if(!empty($_REQUEST['oid'])):
					echo json_encode(array(true, '<h1>'.__('Tracking number','wpshop').'</h1><p>'.__('Enter a tracking number, or leave blank:','wpshop').'</p><input type="hidden" value="'.$_REQUEST['oid'].'" name="oid" /><input type="text" name="trackingNumber" /><br /><br /><input type="submit" class="button-primary sendTrackingNumber" value="'.__('Send','wpshop').'" /> <input type="button" class="button-secondary closeAlert" value="'.__('Cancel','wpshop').'" />'));
				else:
					echo json_encode(array(false, __('Order reference error', 'wpshop')));
				endif;
			break;
			
			case 'ajax_hideTplVersionNotice':
				$templateVersions = get_option('wpshop_templateVersions', array());
				$templateVersions[WPSHOP_TPL_VERSION] = true;
				update_option('wpshop_templateVersions', $templateVersions);
			break;
		}
	}
	break;

	default:
	{
		/*	Default case is get request method	*/
	}
	break;
}
