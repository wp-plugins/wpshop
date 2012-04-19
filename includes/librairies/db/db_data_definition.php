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

$wpshop_eav_content = array();
$wpshop_eav_content_update = array();
$wpshop_db_content_add = array();
$wpshop_db_content_update = array();
$wpshop_db_options_add = array();
$wpshop_db_options_update = array();
$wpshop_db_version = 0;

{/*	Version 0	*/
	$wpshop_db_version = 0;

	/*	Default entities	*/
	$wpshop_eav_content[$wpshop_db_version]['entities']['product'] = $wpdb->posts;

	/*	Default attributes	*/
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_reference', 'is_required' => 'yes', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Product reference', 'wpshop'));
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_attribute_set_id', 'is_required' => 'yes', 'data_type' => 'integer', 'frontend_input' => 'text', 'frontend_label' => __('Attribute set', 'wpshop'), 'is_visible_in_front' => 'no');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_weight', 'is_required' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Product weight', 'wpshop'), 'is_requiring_unit' => 'yes');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_height', 'is_required' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Product height', 'wpshop'), 'is_requiring_unit' => 'yes');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_width', 'is_required' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Product width', 'wpshop'), 'is_requiring_unit' => 'yes');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_price', 'is_required' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Price', 'wpshop'), 'is_requiring_unit' => 'yes', 'is_visible_in_front' => 'no');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_stock', 'is_required' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Stock', 'wpshop'), 'is_visible_in_front' => 'no');

	/*	Default attribute group	*/
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array('code' => 'general', 'name' => __('Main information', 'wpshop'), 'details' => array('product_reference', 'product_attribute_set_id'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array('code' => 'feature', 'name' => __('Feature', 'wpshop'), 'details' => array('product_weight', 'product_height', 'product_width'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array('code' => 'additionnal_informations', 'name' => __('Additionnal informations', 'wpshop'), 'details' => array('product_price', 'product_stock'));

	/*	Unit	*/
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'mm', 'name' => __('Millimeters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'm', 'name' => __('Meters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'l', 'name' => __('Liters', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'oz', 'name' => __('Ounce', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'g', 'name' => __('Gram', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => 'kg', 'name' => __('Kilogram', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => '&euro;', 'name' => __('euro', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'unit' => '$', 'name' => __('dollar', 'wpshop'));
}
{/*	Version 1	*/
	$wpshop_db_version = 1;

	/*	Add unit groups	*/
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('length', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('capacity', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('weight', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('currency', 'wpshop'));
	$wpshop_db_content_add[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP][] = array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'name' => __('various', 'wpshop'));

	/*	Update unit with group identifier	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 1), 'where' => array('unit' => 'm'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 1, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'mm'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 2), 'where' => array('unit' => 'oz'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 2, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'l'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 3), 'where' => array('unit' => 'g'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 3, 'is_default_of_group' => 'yes'), 'where' => array('unit' => 'kg'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 4), 'where' => array('unit' => '$'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_UNIT][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'group_id' => 4, 'is_default_of_group' => 'yes'), 'where' => array('unit' => '&euro;'));

	/*	Update attribute with default unit	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 1, '_default_unit' => 2), 'where' => array('code' => 'product_height'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 1, '_default_unit' => 2), 'where' => array('code' => 'product_width'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 3, '_default_unit' => 6), 'where' => array('code' => 'product_weight'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_unit_group_id' => 4, '_default_unit' => 7), 'where' => array('code' => 'product_price'));
}

{/*	Version 3	*/
	$wpshop_db_version = 3;

	$wpshop_db_options_add[$wpshop_db_version]['permalink_structure'] = '/%postname%';
	$wpshop_db_options_add[$wpshop_db_version]['wpshop_paymentMethod'] = array('paypal' => false, 'checks' => false);
}

{/*	Version 7	*/
	$wpshop_db_version = 7;

	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Model', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'model', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Visibilty', 'wpshop'), 'frontend_input_values' => array( 'visibility_catlog' => __('Catalog', 'wpshop'), 'visibility_search' => __('Search', 'wpshop'), 'visibility_both' => __('Catalog & Search', 'wpshop')), 'default_value' => 'visibility_both', 'is_requiring_unit' => 'no', 'code' => 'visibility', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Manufacturer', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'manufacturer', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Color', 'wpshop'), 'frontend_input_values' => array( 'color_blue' => __('Blue', 'wpshop'), 'color_green' => __('Green', 'wpshop'), 'color_red' => __('Red', 'wpshop')), 'default_value' => '', 'is_requiring_unit' => 'no', 'code' => 'color', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Barcode', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'barcode', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Tarif Code', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'tarif_code', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Country of Manufacture', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'country_of_manufacture', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('ISBN', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'isbn', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Declare this product as new', 'wpshop'), 'frontend_input_values' => array( 'new_yes' => __('Yes', 'wpshop'), 'new_no' => __('No', 'wpshop')), 'default_value' => 'new_no', 'is_requiring_unit' => 'no', 'code' => 'declare_new', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Set product as new from date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'set_new_from', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Set product as new to date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'set_new_to', 'attribute_status' => 'notused');

	/*	Inventory	*/
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Manage Stock', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'manage_stock', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Allow Decimals', 'wpshop'), 'frontend_input_values' => array( 'decimal_yes' => __('Yes', 'wpshop'), 'decimal_no' => __('No', 'wpshop')), 'default_value' => 'decimal_no', 'is_requiring_unit' => 'no', 'code' => 'qty_uses_decimals', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Maximum authorized quantity in cart', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'maxi_authorized_quantity', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Minimum authorized quantity in cart', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'mini_authorized_quantity', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Use quantity increments', 'wpshop'), 'frontend_input_values' => array( 'increment_yes' => __('Yes', 'wpshop'), 'increment_no' => __('No', 'wpshop')), 'default_value' => 'increment_no', 'is_requiring_unit' => 'no', 'code' => 'qty_increments', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Set product as out at ', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'set_as_out_at', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Set product as out of stock', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'set_as_out_of_stock', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Notify when quantity goes below', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'notify_quantity_goes_below', 'attribute_status' => 'notused');

	/*	Supplier	*/
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Cost', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'cost', 'attribute_status' => 'notused');

	/*	Prices	*/
	$wpshop_eav_content_update[$wpshop_db_version]['attributes']['product'][] = array('code' => 'product_price', 'attribute_status' => 'valid', 'last_update_date' => current_time('mysql', 0), 'frontend_label' => __('Price ATI', 'wpshop'));
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Price ET(HT)', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'price_ht');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Rate Taxe (TVA)', 'wpshop'), 'frontend_input_values' => array( 'taux_1' => 2.1, 'taux_2' => 5.5, 'taux_3' => 19.6), 'default_value' => 'taux_3', 'is_requiring_unit' => 'no', 'code' => 'tx_tva');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Taxe', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'tva');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Eco Taxe', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'eco_taxe', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Rate TVA Eco Taxe', 'wpshop'), 'frontend_input_values' => array( 'taux_1' => 2.1, 'taux_2' => 5.5, 'taux_3' => 19.6), 'default_value' => 'taux_3', 'is_requiring_unit' => 'no', 'code' => 'eco_taxe_rate_tva', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('TVA Eco Taxe', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'eco_taxe_tva', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Discount rate', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'discount_rate', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Discount amount', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'discount_amount', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Special Price', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'special_price', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Cost Of Postage', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'cost_of_postage', 'attribute_status' => 'valid');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Special Price From Date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'special_from', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Special Price To Date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'special_to', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Apply minimum advertised price', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'apply_map', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Manufacturer\'s Sugessted Retail Price', 'wpshop'), '_unit_group_id' => '4', '_default_unit' => '7', 'is_requiring_unit' => 'yes', 'code' => 'suggested_price', 'attribute_status' => 'notused');

	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Return merchandise authorization', 'wpshop'), 'frontend_input_values' => array( 'rma_yes' => __('Yes', 'wpshop'), 'rma_no' => __('No', 'wpshop')), 'default_value' => 'rma_no', 'is_requiring_unit' => 'no', 'code' => 'rma', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Allow Gift Message', 'wpshop'), 'frontend_input_values' => array( 'gift_message_yes' => __('Yes', 'wpshop'), 'gift_message_no' => __('No', 'wpshop')), 'default_value' => 'gift_message_no', 'is_requiring_unit' => 'no', 'code' => 'allow_gift_message', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Allow Gift Wrapping', 'wpshop'), 'frontend_input_values' => array( 'gift_wrapping_yes' => __('Yes', 'wpshop'), 'gift_wrapping_no' => __('No', 'wpshop')), 'default_value' => 'gift_wrapping_no', 'is_requiring_unit' => 'no', 'code' => 'allow_gift_wrapping', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'decimal', 'frontend_input' => 'text', 'frontend_label' => __('Price For Gift Option', 'wpshop'), 'is_requiring_unit' => 'yes', '_unit_group_id' => '4', '_default_unit' => '7', 'code' => 'price_gift_options', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'varchar', 'frontend_input' => 'text', 'frontend_label' => __('Product tag', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'product_tag', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Up Sells', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'up_sells', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Cross Sells', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'cross_sells', 'attribute_status' => 'notused');


	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'checkbox', 'frontend_label' => __('Chemical Product', 'wpshop'), 'frontend_input_values' => array( 'chemical_yes' => __('Yes', 'wpshop'), 'chemical_no' => __('No', 'wpshop')), 'default_value' => 'chemical_no', 'is_requiring_unit' => 'non', 'code' => 'chemical_product', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'checkbox', 'frontend_label' => __('Danger\'s Pictogram', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'pictogram', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Risk Phrases', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'risk_phrases', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Safety Advices', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'safety_advices', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Security Datas', 'wpshop'), 'frontend_input_values' => array( 'datas_yes' => __('Yes', 'wpshop'), 'datas_no' => __('No', 'wpshop')), 'default_value' => 'datas_no', 'is_requiring_unit' => 'non', 'code' => 'security_datas', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'text', 'frontend_input' => 'file', 'frontend_label' => __('Security Datas File', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'security_datas', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Last Update Date', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'last_update_date', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Product consumption', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'consumption', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Frequency of use', 'wpshop'), 'is_requiring_unit' => 'non', 'code' => 'frequency', 'attribute_status' => 'notused');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'radio', 'frontend_label' => __('Cancerigene Mutagene Reprotoxique', 'wpshop'), 'frontend_input_values' => array( 'cmr_yes' => __('Yes', 'wpshop'), 'cmr_no' => __('No', 'wpshop')), 'default_value' => 'cmr_no', 'is_requiring_unit' => 'non', 'code' => 'cmr', 'attribute_status' => 'notused');

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array( 'name' => __('Prices', 'wpshop'), 'code' => 'prices', 'details' => array('price_ht', 'product_price', 'tx_tva', 'tva'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array( 'name' => __('Inventory', 'wopshop'), 'code' => 'inventory', 'details' => array('product_stock'));
	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array( 'name' => __('Shipping', 'wopshop'), 'code' => 'shipping', 'details' => array('cost_of_postage'));
	$wpshop_eav_content_update[$wpshop_db_version]['attribute_groups']['product']['default'][] = array('code' => 'additionnal_informations', 'details' => array());

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['chemical_product'][] = array('status' => 'moderated', 'name' => __('Chemical Product', 'wpshop'), 'code' => 'chemical_product', 'details' => array('chemical_product', 'pictogram', 'risk_phrases', 'safety_advices', 'security_datas', 'last_update_date', 'consumption', 'frequency', 'cmr'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'valid'), 'where' => array('status' => ''));
}

{/*	Version 9	*/
	$wpshop_db_version = 9;

	/*	Make a correction on attribute that have been created with bad attribute set group	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('entity_id' => '0'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('attribute_set_id' => '0'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_DETAILS][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'deleted'), 'where' => array('attribute_group_id' => '0'));

	/*	Add a default attribute set for new attribute set creation	*/
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_SET][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'default_set' => 'yes'), 'where' => array('id' => '1'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE_GROUP][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'default_group' => 'yes'), 'where' => array('code' => 'additionnal_informations'));
}

{/*	Version 10	*/
	$wpshop_db_version = 10;

	$wpshop_db_options_add[$wpshop_db_version]['wpshop_shop_default_currency'] = WPSHOP_SHOP_DEFAULT_CURRENCY;
	$wpshop_db_options_add[$wpshop_db_version]['wpshop_shop_currencies'] = unserialize(WPSHOP_SHOP_CURRENCIES);
}

{/*	Version 11	*/
	$wpshop_db_version = 11;

	$wpshop_db_options_add[$wpshop_db_version]['wpshop_shipping_rules'] = unserialize(WPSHOP_SHOP_SHIPPING_RULES);
}

{/*	Version 12	*/
	$wpshop_db_version = 12;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'product_price'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'price_ht'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'tva'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), '_default_unit' => 0), 'where' => array('code' => 'cost_of_postage'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_for_sort_by' => 'yes'), 'where' => array('code' => 'product_price'));

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated'), 'where' => array('code' => 'declare_new'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated'), 'where' => array('code' => 'set_new_from'));
	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'status' => 'moderated'), 'where' => array('code' => 'set_new_to'));

	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'integer', 'frontend_input' => 'select', 'frontend_label' => __('Highlight this product', 'wpshop'), 'frontend_input_values' => array( 'highlight_yes' => __('Yes', 'wpshop'), 'highlight_no' => __('No', 'wpshop')), 'default_value' => 'highlight_no', 'is_requiring_unit' => 'no', 'code' => 'highlight_product', 'attribute_status' => 'moderated');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Highlight from date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'highlight_from', 'attribute_status' => 'moderated');
	$wpshop_eav_content[$wpshop_db_version]['attributes']['product'][] = array( 'is_required' => 'no', 'is_visible_in_front' => 'no', 'data_type' => 'datetime', 'frontend_input' => 'text', 'frontend_label' => __('Highlight to date', 'wpshop'), 'is_requiring_unit' => 'no', 'code' => 'highlight_to', 'attribute_status' => 'moderated');

	$wpshop_eav_content[$wpshop_db_version]['attribute_groups']['product']['default'][] = array('code' => 'product_highlight', 'name' => __('Product highlight', 'wpshop'), 'details' => array('declare_new', 'set_new_from', 'set_new_to', 'highlight_product', 'highlight_from', 'highlight_to'));
}
{/*	Version 13	*/
	$wpshop_db_version = 13;

	$wpshop_db_content_update[$wpshop_db_version][WPSHOP_DBT_ATTRIBUTE][] = array('datas' => array('last_update_date' => current_time('mysql', 0), 'is_used_for_sort_by' => 'yes'), 'where' => array('code' => 'product_stock'));
}


{/*	Version dev	- Call for every plugin db version	*/
	$wpshop_db_version = 'dev';
}
