<?php
/*
 * Specific
 *
 *
 * General
 *
 */

$tpl_element = array();

/**
 *
 *
 * Admin button
 *
 *
 */
/*	"Duplicate product" button	*/
ob_start();
?>
<button class="wpshop_product_duplication_button" id="wpshop_product_id_{WPSHOP_PRODUCT_ID}" ><?php _e('Duplicate the product', 'wpshop'); ?></button><span id="wpshop_loading_duplicate_pdt_{WPSHOP_PRODUCT_ID}" class="wpshop_loading_picture" ></span>
<?php
$tpl_element['wpshop_duplicate_product'] = ob_get_contents();
ob_end_clean();












/**
 *
 *
 * Frontend sorting to preserve from changes
 *
 *
 */
/*	Sorting bloc hidden fields */
/*
 * {WPSHOP_DISPLAY_TYPE}
* {WPSHOP_ORDER}
* {WPSHOP_PRODUCT_NUMBER}
* {WPSHOP_CURRENT_PAGE}
* {WPSHOP_CATEGORY_ID}
* {WPSHOP_PRODUCT_ID}
* {WPSHOP_ATTR}
*/
ob_start();
?>
	<input type="hidden" name="display_type" value="{WPSHOP_DISPLAY_TYPE}" class="hidden_sorting_fields" />
	<input type="hidden" name="order" value="{WPSHOP_ORDER}" class="hidden_sorting_fields" />
	<input type="hidden" name="products_per_page" value="{WPSHOP_PRODUCT_NUMBER}" class="hidden_sorting_fields" />
	<input type="hidden" name="page_number" value="{WPSHOP_CURRENT_PAGE}" />
	<input type="hidden" name="cid" value="{WPSHOP_CATEGORY_ID}" class="hidden_sorting_fields" />
	<input type="hidden" name="pid" value="{WPSHOP_PRODUCT_ID}" class="hidden_sorting_fields" />
	<input type="hidden" name="attr" value="{WPSHOP_ATTR}" class="hidden_sorting_fields" /><?php
$tpl_element['product_listing_sorting_hidden_field'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc */
/*
 * {WPSHOP_SORTING_HIDDEN_FIELDS}
* {WPSHOP_SORTING_CRITERIA}
*/
ob_start();
?>
<div class="hidden_sorting_bloc" >
	{WPSHOP_SORTING_HIDDEN_FIELDS}{WPSHOP_SORTING_CRITERIA}
</div><?php
$tpl_element['product_listing_sorting_hidden'] = ob_get_contents();
ob_end_clean();

/*	Sorting bloc hidden fields */
/*
 * {WPSHOP_DISPLAY_TYPE}
 * {WPSHOP_ORDER}
 * {WPSHOP_PRODUCT_NUMBER}
 * {WPSHOP_CURRENT_PAGE}
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_PRODUCT_ID}
 * {WPSHOP_ATTR}
 */
ob_start();
?>
	<input type="hidden" name="sorting_criteria" value="{WPSHOP_CRITERIA_DEFAULT}" class="hidden_sorting_fields" /><?php
$tpl_element['product_listing_sorting_criteria_hidden'] = ob_get_contents();
ob_end_clean();