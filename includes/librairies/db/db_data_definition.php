<?php
/**
* Plugin database start content definition file.
* 
*	This file contains the different definitions for the database content.
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies-db
*/
 
{/*	Define the main table necessary for plugin working	*/

/*	Define the different attribute set section	*/
$i = 0;
$initialEavData['attributeGroup'][$i]['name'] = __('Main information', 'wpshop');
$initialEavData['attributeGroup'][$i]['code'] = 'general';
$initialEavData['attributeGroup'][$i]['details'] = array('product_reference', 'product_attribute_set_id');
$i++;
$initialEavData['attributeGroup'][$i]['name'] = __('Feature', 'wpshop');
$initialEavData['attributeGroup'][$i]['code'] = 'feature';
$initialEavData['attributeGroup'][$i]['details'] = array('product_weight', 'product_height', 'product_width');
$i++;
$initialEavData['attributeGroup'][$i]['name'] = __('Additionnal informations', 'wpshop');
$initialEavData['attributeGroup'][$i]['code'] = 'additionnal_informations';
$initialEavData['attributeGroup'][$i]['details'] = array('product_price', 'product_stock');
$i++;


/*	Define the different attribute for each entities	*/
	$initialEavData['entities']['product']['dbTable'] = $wpdb->posts;
$initialEavData['entities']['product']['attributes']['product_reference']['is_required'] = 'yes';
$initialEavData['entities']['product']['attributes']['product_reference']['data_type'] = 'varchar';
$initialEavData['entities']['product']['attributes']['product_reference']['frontend_input'] = 'text';
$initialEavData['entities']['product']['attributes']['product_reference']['frontend_label'] = __('Product reference', 'wpshop');
	$initialEavData['entities']['product']['attributes']['product_attribute_set_id']['is_required'] = 'yes';
	$initialEavData['entities']['product']['attributes']['product_attribute_set_id']['is_visible_in_front'] = 'no';
	$initialEavData['entities']['product']['attributes']['product_attribute_set_id']['data_type'] = 'integer';
	$initialEavData['entities']['product']['attributes']['product_attribute_set_id']['frontend_input'] = 'text';
	$initialEavData['entities']['product']['attributes']['product_attribute_set_id']['frontend_label'] = __('Attribute set', 'wpshop');
$initialEavData['entities']['product']['attributes']['product_weight']['is_required'] = 'no';
$initialEavData['entities']['product']['attributes']['product_weight']['data_type'] = 'decimal';
$initialEavData['entities']['product']['attributes']['product_weight']['frontend_input'] = 'text';
$initialEavData['entities']['product']['attributes']['product_weight']['frontend_label'] = __('Product weight', 'wpshop');
$initialEavData['entities']['product']['attributes']['product_weight']['is_requiring_unit'] = 'yes';
	$initialEavData['entities']['product']['attributes']['product_height']['is_required'] = 'no';
	$initialEavData['entities']['product']['attributes']['product_height']['data_type'] = 'decimal';
	$initialEavData['entities']['product']['attributes']['product_height']['frontend_input'] = 'text';
	$initialEavData['entities']['product']['attributes']['product_height']['frontend_label'] = __('Product height', 'wpshop');
	$initialEavData['entities']['product']['attributes']['product_height']['is_requiring_unit'] = 'yes';
$initialEavData['entities']['product']['attributes']['product_width']['is_required'] = 'no';
$initialEavData['entities']['product']['attributes']['product_width']['data_type'] = 'decimal';
$initialEavData['entities']['product']['attributes']['product_width']['frontend_input'] = 'text';
$initialEavData['entities']['product']['attributes']['product_width']['frontend_label'] = __('Product width', 'wpshop');
$initialEavData['entities']['product']['attributes']['product_width']['is_requiring_unit'] = 'yes';
	$initialEavData['entities']['product']['attributes']['product_price']['is_required'] = 'no';
	$initialEavData['entities']['product']['attributes']['product_price']['data_type'] = 'decimal';
	$initialEavData['entities']['product']['attributes']['product_price']['is_visible_in_front'] = 'no';
	$initialEavData['entities']['product']['attributes']['product_price']['is_requiring_unit'] = 'yes';
	$initialEavData['entities']['product']['attributes']['product_price']['frontend_input'] = 'text';
	$initialEavData['entities']['product']['attributes']['product_price']['frontend_label'] = __('Price', 'wpshop');
$initialEavData['entities']['product']['attributes']['product_stock']['is_required'] = 'no';
$initialEavData['entities']['product']['attributes']['product_stock']['data_type'] = 'decimal';
$initialEavData['entities']['product']['attributes']['product_stock']['is_visible_in_front'] = 'no';
$initialEavData['entities']['product']['attributes']['product_stock']['frontend_input'] = 'text';
$initialEavData['entities']['product']['attributes']['product_stock']['frontend_label'] = __('Stock', 'wpshop');

/*	Define initial datas to set	*/
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['mm']['name'] = __('Millimeters', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['m']['name'] = __('Meters', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['l']['name'] = __('Liters', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['oz']['name'] = __('Ounce', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['g']['name'] = __('Gram', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['kg']['name'] = __('Kilogram', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['&euro;']['name'] = __('euro', 'wpshop');
$initialData[WPSHOP_DBT_ATTRIBUTE_UNIT]['$']['name'] = __('dollar', 'wpshop');

}


$wpshop_data_version = array();
{/*	Define database datas for plugin version	*/													/*	1	*/
	$current_version = 1;

	$i = 0;
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['status'] = 'valid';
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['creation_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['name'] = 'length';
	$i++;
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['status'] = 'valid';
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['creation_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['name'] = 'capacity';
	$i++;
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['status'] = 'valid';
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['creation_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['name'] = 'weight';
	$i++;
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['status'] = 'valid';
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['creation_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['name'] = 'currency';
	$i++;
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['status'] = 'valid';
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['creation_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['INSERT'][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][$i]['name'] = 'various';

	$i = 0;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '1';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['is_default_of_group'] = 'yes';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'mm';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '1';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'm';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '2';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['is_default_of_group'] = 'yes';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'l';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '2';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'oz';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '3';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'g';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '3';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['is_default_of_group'] = 'yes';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = 'kg';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '4';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['is_default_of_group'] = 'yes';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = '&euro;';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['group_id'] = '4';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE_UNIT][$i]['where_condition'][$i]['unit'] = '$';

	$i = 0;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_unit_group_id'] = '3';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_default_unit'] = '6';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['where_condition'][$i]['code'] = 'product_weight';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_unit_group_id'] = '1';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_default_unit'] = '2';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['where_condition'][$i]['code'] = 'product_height';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_unit_group_id'] = '1';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_default_unit'] = '2';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['where_condition'][$i]['code'] = 'product_width';
	$i++;
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['last_update_date'] = date('Y-m-d H:i:s');
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_unit_group_id'] = '4';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['_default_unit'] = '7';
	$wpshop_data_version[$current_version]['UPDATE'][WPSHOP_DBT_ATTRIBUTE][$i]['where_condition'][$i]['code'] = 'product_price';

	unset($current_version);
}
{/*	Define database datas for plugin version	*/													/*	2	*/
	$current_version = 2;

	unset($current_version);
}