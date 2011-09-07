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
require_once(	WPSHOP_INCLUDES_DIR . 'include.php' );

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
					case 'load_attribute_units':
					{
						echo wpshop_attributes::get_attribute_unit_list();
					}
					break;
					case 'load_attribute_units_form':
					case 'edit_attribute_unit':
					{
						$atribute_unit = '';
						if($action == 'edit_attribute_unit'){
							$atribute_unit = wpshop_tools::varSanitizer($_REQUEST['elementIdentifier']);
						}
						echo wpshop_attributes::get_attribute_unit_form($atribute_unit);
					}
					break;
					case 'save_new_attribute_unit':
					{
						$save_output = '';

						$attribute_unit_informations['id'] = '';
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_REQUEST['status']);
						$attribute_unit_informations['creation_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_REQUEST['unit']);

						$save_unit_result = wpshop_database::save($attribute_unit_informations, WPSHOP_DBT_ATTRIBUTE_UNIT);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes::get_attribute_unit_list();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The new unit has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes::get_attribute_unit_form();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action error" src="' . WPSHOP_ERROR_ICON . '" />' . __('An error occured during new unit saving', 'wpshop');
						}

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . $save_output;
					}
					break;
					case 'update_attribute_unit':
					{
						$save_output = '';

						$attributeUnitId = wpshop_tools::varSanitizer($_REQUEST['id']);
						$attribute_unit_informations['status'] = wpshop_tools::varSanitizer($_REQUEST['status']);
						$attribute_unit_informations['last_update_date'] = date('Y-m-d H:i:s');
						$attribute_unit_informations['unit'] = wpshop_tools::varSanitizer($_REQUEST['unit']);

						$save_unit_result =  wpshop_database::update($attribute_unit_informations, $attributeUnitId, WPSHOP_DBT_ATTRIBUTE_UNIT);
						if($save_unit_result == 'done'){
							$save_output = wpshop_attributes::get_attribute_unit_list();
							$save_message = '<img class="wpshopPageMessage_Icon" alt="action successful" src="' . WPSHOP_SUCCES_ICON . '" />' . __('The unit has been saved succesfully', 'wpshop');
						}
						else{
							$save_output = wpshop_attributes::get_attribute_unit_form($attributeUnitId);
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

						echo '<script type="text/javascript" >wpshop(document).ready(function(){setTimeout(function(){ wpshop("#wpshopMessage_unit").removeClass("wpshopPageMessage_Updated"); wpshop("#wpshopMessage_unit").html(""); }, 5000);});</script><div class="fade below-h2 wpshopPageMessage wpshopPageMessage_Updated" id="wpshopMessage_unit">' . $save_message . '</div>' . wpshop_attributes::get_attribute_unit_list();
					}
					break;
				}
			}
			break;
		}
	}
	break;

	default:
	{/*	Default case is get request method	*/

	}
	break;
}
