<?php
/*
 * General
	{WPSHOP_CART_LINK}		=> Link for the cart page
	{WPSHOP_CURRENCY}		=> Currency defined for the shop
 *
 */

$tpl_element = array();

/**
 *
 *
 * Frontend button
 *
 *
 */
/*	"Product unavailable" button 	|					Bouton Ajouter au panier Désactivé */
ob_start();
?>
<button itemprop="availability" content="out_of_stock" type="button" disabled="disabled" class="no_stock"><?php _e('Soon available', 'wpshop'); ?></button><?php
$tpl_element['unavailable_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Add to cart" button	|							Bouton Ajouter au panier */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="in_stock" type="button" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" class="wpshop_add_to_cart_button wpshop_products_listing_bton_panier_active"><?php _e('Add to cart', 'wpshop'); ?></button><span class="wpshop_cart_loading_picture"></span><?php
$tpl_element['add_to_cart_button'] = ob_get_contents();
ob_end_clean();

/*	"Go to product configuration" button	|			Bouton de configuration du produit si il contient des declinaisons */
ob_start();
?>
<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" id="wpshop_add_to_cart_{WPSHOP_PRODUCT_ID}" itemprop="availability" content="to_configure" class="wpshop_configure_product_button wpshop_products_listing_bton_panier_active" ><?php _e('Configure product', 'wpshop'); ?></a><?php
$tpl_element['configure_product_button'] = ob_get_contents();
ob_end_clean();


/*	"Ask quotation" button	| 							Bouton Demander un devis */
/**
 * {WPSHOP_PRODUCT_ID}
 */
ob_start();
?>
<button itemprop="availability" content="preorder" type="button" id="wpshop_ask_a_quotation_{WPSHOP_PRODUCT_ID}" class="wpshop_products_listing_bton_panier_active wpshop_ask_a_quotation_button"><?php _e('Ask a quotation', 'wpshop'); ?></button><?php
$tpl_element['ask_quotation_button'] = ob_get_contents();
ob_end_clean();



/*	Mini cart container	|								Mini Panier Container */
/**
 * {WPSHOP_CART_MINI_CONTENT}
 */
ob_start();
?>
<div class="wpshop_cart_summary_detail" ></div><div class="wpshop_cart_alert" ></div>
<div class="wpshop_cart_summary" >{WPSHOP_CART_MINI_CONTENT}</div>
<div class="wpshop_cart_free_shipping_cost_alert">{WPSHOP_FREE_SHIPPING_COST_ALERT}</div>
<?php
$tpl_element['mini_cart_container'] = ob_get_contents();
ob_end_clean();


/*	Mini cart content									Mini Panier contenu */
/**
 * {WPSHOP_CART_LINK}										- Lien vers le panier
 * {WPSHOP_PDT_CPT}											- Nombre de produit dans le panier
 * {WPSHOP_CART_TOTAL_AMOUNT}								- Montant total du panier
 */
ob_start();
?>
<a href="{WPSHOP_CART_LINK}"><?php echo sprintf(__('Your have %s item(s) in your cart','wpshop'), '{WPSHOP_PDT_CPT}').' - {WPSHOP_CART_TOTAL_AMOUNT}'?> {WPSHOP_CURRENCY}</a><?php
$tpl_element['mini_cart_content'] = ob_get_contents();
ob_end_clean();


/*	Cart table header and footer						Header tableau panier (Page) */
ob_start();
?>	<tr>
		<th><?php _e('Product name', 'wpshop'); ?></th>
		<th class="center"><?php _e('Unit price ET', 'wpshop'); ?></th>
		<th class="center"><?php _e('Quantity', 'wpshop'); ?></th>
		<th><?php _e('Total price ET', 'wpshop'); ?></th>
		<th><?php _e('Total price ATI', 'wpshop'); ?></th>
		<th class="center"><?php _e('Action', 'wpshop'); ?></th>
	</tr><?php
$tpl_element['cart_table_column_def'] = ob_get_contents();
ob_end_clean();

/*	Cart table header and footer						Tableau panier (page) */
ob_start();
?><table id="cartContent">
<thead>
{WPSHOP_CART_TABLE_COLUMN_DEF}
</thead>
<tfoot>
{WPSHOP_CART_TABLE_COLUMN_DEF}
</tfoot>
<tbody>
{WPSHOP_CART_CONTENT}
</tbody>
</table><?php
$tpl_element['cart_table_def'] = ob_get_contents();
ob_end_clean();

/*	Cart line detail									Ligne tableau panier (page) */
ob_start();
?><tr id="product_{WPSHOP_CART_LINE_ITEM_ID}">
	<td>
		<input type="hidden" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="currentProductQty" />{WPSHOP_CART_PRODUCT_NAME}
		<ul class="wpshop_cart_variation_details" >{WPSHOP_CART_PRODUCT_MORE_INFO}</ul>
	</td>
	<td class="product_price_ht center">{WPSHOP_CART_LINE_ITEM_PUHT} {WPSHOP_CURRENCY}</td>
	<td class="center" style="min-width:125px;">{WPSHOP_CART_LINE_ITEM_QTY_}</td>
	<td class="total_price_ht center"><span>{WPSHOP_CART_LINE_ITEM_TPHT} {WPSHOP_CURRENCY}</span></td>
	<td class="total_price_ttc center"><span>{WPSHOP_CART_LINE_ITEM_TPTTC} {WPSHOP_CURRENCY}</span></td>
	<td class="center">{WPSHOP_CART_LINE_ITEM_REMOVER}</td>
</tr><?php
$tpl_element['cart_line'] = ob_get_contents();
ob_end_clean();


/*	Product link	| 						 */
ob_start();
?><a href="{WPSHOP_CART_LINE_ITEM_LINK}">{WPSHOP_CART_LINE_ITEM_NAME}</a><?php
$tpl_element['cart_product_name'] = ob_get_contents();
ob_end_clean();


/*	Product quantity updater	| 						Panier tableau formulaire + - quantité */
ob_start();
?><a href="#" class="productQtyChange">-</a><input type="text" value="{WPSHOP_CART_LINE_ITEM_QTY}" name="productQty" id="wpshop_product_order_{WPSHOP_CART_LINE_ITEM_ID}"  /><a href="#" class="productQtyChange">+</a><?php
$tpl_element['cart_qty_content'] = ob_get_contents();
ob_end_clean();


/*	Product cart remover	|							Panier tableau supprimer élément */
ob_start();
?><a href="#" class="remove" title="<?php _e('Remove', 'wpshop'); ?>"><?php _e('Remove', 'wpshop'); ?></a><?php
$tpl_element['cart_line_remove'] = ob_get_contents();
ob_end_clean();


/*	Product variation detail in cart					Panier detail des variations */
ob_start();
?><li class="wpshop_cart_variation_details_item wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ID} wpshop_cart_variation_details_item_{WPSHOP_VARIATION_ATT_CODE}" >{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}</li><?php
$tpl_element['cart_variation_detail'] = ob_get_contents();
ob_end_clean();


/*	Vouncher field into cart							Coupons de reduction */
ob_start();
?><div class="wpshop_cart_vouncher_field_container" ><?php _e('Discount coupon','wpshop'); ?> : <input type="text" name="coupon_code" value="" /> <a href="#" class="submit_coupon"><?php _e('Submit the coupon','wpshop'); ?></a></div><?php
$tpl_element['cart_vouncher_part'] = ob_get_contents();
ob_end_clean();


/*	Empty cart button									Vidage du panier */
ob_start();
?><div class="wpshop_cart_buttons_container" ><div class="alignright" ><input type="submit" value="{WPSHOP_CART_BUTTON_VALIDATE_TEXT}" name="cartCheckout" class="alignright" /><br/><a href="#" class="emptyCart alignright" >{WPSHOP_BUTTON_EMPTY_CART_TEXT}</a></div></div><?php
$tpl_element['cart_buttons'] = ob_get_contents();
ob_end_clean();


/*	Cart Total summaries line content 					Contenu des lignes des totaux du panier */
ob_start();
?><div class="wpshop_cart_summary_line{WPSHOP_CART_SUMMARY_LINE_SPECIFIC}" >{WPSHOP_CART_SUMMARY_TITLE} : <span class="right{WPSHOP_CART_SUMMARY_AMOUNT_CLASS}" >{WPSHOP_CART_SUMMARY_AMOUNT} {WPSHOP_CURRENCY}</span></div><?php
$tpl_element['cart_summary_line_content'] = ob_get_contents();
ob_end_clean();


/*	Cart main page						Template general page panier */
ob_start();
?><span id="wpshop_loading">&nbsp;</span>
<div class="cart" >
	{WPSHOP_CART_OUTPUT}
	<div>
		<div><?php _e('Total ET','wpshop'); ?> : <span class="total_ht right">{WPSHOP_CART_PRICE_ET} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_TAXES}
		<div id="order_shipping_cost" ><?php _e('Shipping','wpshop'); ?> <?php _e('ATI','wpshop'); ?> : <span class="right">{WPSHOP_CART_SHIPPING_COST} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_DISCOUNT_SUMMARY}
		<div class="bold wpshop_clear" ><?php _e('Total ATI','wpshop'); ?> : <span class="total_ttc right bold">{WPSHOP_CART_TOTAL_ATI} {WPSHOP_CURRENCY}</span></div>
		{WPSHOP_CART_VOUNCHER}
	</div>
	{WPSHOP_CART_BUTTONS}
</div><?php
$tpl_element['cart_main_page'] = ob_get_contents();
ob_end_clean();


/*	product added to cart popup							Panier Popup après ajout au panier */
ob_start();
?>
<div class="wpshop_superBackground"></div>
<div class="wpshop_popupAlert">
		<div id="product_img_dialog_box"></div>
		<div id="product_infos_dialog_box">
			<p><h1><?php _e('Your product has been sucessfuly added to your cart', 'wpshop'); ?></h1></p>
			<br/>
			<p><span class="product_title_dialog_box"></span></p>
			<p><span class="product_price_dialog_box"></span></p>
			
		</div>
		<div id="buttons_line_dialog_box">
				<div class="alignleft"><a href="{WPSHOP_CART_LINK}" class="bouton_wpshop"><?php _e('View my cart','wpshop'); ?></a></div>
				<div class="alignright"><a href="" class="bouton_wpshop_commander closeAlert" ><?php _e('Continue shopping','wpshop'); ?></a></div>
		</div>

</div>
<?php
$tpl_element['product_added_to_cart_message'] = ob_get_contents();
ob_end_clean();


/*	Current product variation	*/


/*	Product is new	|									Nouveauté produit */
ob_start();
?>
<span class="vignette_nouveaute"><?php _e('New', 'wpshop'); ?></span><?php
$tpl_element['product_is_new_sticker'] = ob_get_contents();
ob_end_clean();


/*	Product is featured	|								En vedette produit */
ob_start();
?>
<span class="vignette_en_vedette"><?php _e('Featured', 'wpshop'); ?></span><?php
$tpl_element['product_is_featured_sticker'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Product front attribute display
 *
 *
 */
/*	Display the global container for product attribute	| 				Container single produit ui tab attribute */
/**
 * {WPSHOP_PDT_TABS}
 * {WPSHOP_PDT_TAB_DETAIL}
 */
ob_start();
?>
<div id="wpshop_product_feature"><ul>{WPSHOP_PDT_TABS}</ul>{WPSHOP_PDT_TAB_DETAIL}</div><?php
$tpl_element['product_attribute_container'] = ob_get_contents();
ob_end_clean();

/*	Define each tab for product attribute display						Ui tab attribute */
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_NAME}
 */
ob_start();
?>
<li><a href="#{WPSHOP_ATTRIBUTE_SET_CODE}" >{WPSHOP_ATTRIBUTE_SET_NAME}</a></li><?php
$tpl_element['product_attribute_tabs'] = ob_get_contents();
ob_end_clean();

/*	Define each tab content for product attribute display				Ui tab attribute */
/**
 * {WPSHOP_ATTRIBUTE_SET_CODE}
 * {WPSHOP_ATTRIBUTE_SET_CONTENT}
 */
ob_start();
?>
<div id="{WPSHOP_ATTRIBUTE_SET_CODE}"><ul>{WPSHOP_ATTRIBUTE_SET_CONTENT}</ul></div><?php
$tpl_element['product_attribute_tabs_detail'] = ob_get_contents();
ob_end_clean();

/*	Display each attribute label/value for products	|					Ui tab attribute */
/**
 * {WPSHOP_PDT_ENTITY_CODE}
 * {WPSHOP_ATTRIBUTE_CODE}
 * {WPSHOP_ATTRIBUTE_LABEL}
 * {WPSHOP_ATTRIBUTE_VALUE}
 * {WPSHOP_ATTRIBUTE_VALUE_UNIT}
 */
ob_start();
?>
<li>
	<span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_label {WPSHOP_ATTRIBUTE_CODE}_label" >
	{WPSHOP_ATTRIBUTE_LABEL}
	</span> :
	<span class="{WPSHOP_PDT_ENTITY_CODE}_frontend_attribute_value {WPSHOP_ATTRIBUTE_CODE}_value" >
		{WPSHOP_ATTRIBUTE_VALUE}{WPSHOP_ATTRIBUTE_VALUE_UNIT}
	</span>
</li><?php
$tpl_element['product_attribute_display'] = ob_get_contents();
ob_end_clean();

/*	Define attribute unit template	|									Unités */
/**
 * {WPSHOP_ATTRIBUTE_UNIT}
 */
ob_start();
?>
&nbsp;({WPSHOP_ATTRIBUTE_UNIT})<?php
$tpl_element['product_attribute_unit'] = ob_get_contents();
ob_end_clean();

/*	Define attribute display for select list with internal data	|		Variations */
/**
 * {WPSHOP_ATTRIBUTE_VALUE_POST_LINK}
 * {WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}
 */
ob_start();
?>
<a href="{WPSHOP_ATTRIBUTE_VALUE_POST_LINK}" target="wpshop_entity_element" >{WPSHOP_ATTRIBUTE_VALUE_POST_TITLE}</a><?php
$tpl_element['product_attribute_value_internal'] = ob_get_contents();
ob_end_clean();


/*	Define variation display	*/
ob_start();
?><div class="wpshop_variation{WPSHOP_VARIATION_CONTAINER_CLASS}" ><label for="{WPSHOP_VARIATION_IDENTIFIER}"{WPSHOP_VARIATION_LABEL_HELPER} class="wpshop_variation_label{WPSHOP_VARIATION_LABEL_CLASS}" >{WPSHOP_VARIATION_LABEL}</label> : {WPSHOP_VARIATION_INPUT}</div><?php
$tpl_element['product_variation_item'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?>{WPSHOP_VARIATION_VALUE}<?php
$tpl_element['product_variation_item_possible_values'] = ob_get_contents();
ob_end_clean();

/*	Define variation display	*/
ob_start();
?><form action="<?php echo admin_url('admin-ajax.php')?>" method="POST" id="wpshop_add_to_cart_form" ><input type="hidden" name="wpshop_pdt" id="wpshop_pdt" value="{WPSHOP_VARIATION_FORM_ELEMENT_ID}" /><input type="hidden" name="action" value="wpshop_add_product_to_cart" /><input type="hidden" name="wpshop_cart_type" value="cart" />{WPSHOP_VARIATION_FORM_VARIATION_LIST}</form><?php
$tpl_element['product_variation_form'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Product front display
 *
 *
 */
/*	Product complete sheet	|										Détails produits (single) */
/*
 * {WPSHOP_PRODUCT_THUMBNAIL}
 * {WPSHOP_PRODUCT_GALERY_PICS}
 * {WPSHOP_PRODUCT_PRICE}
 * {WPSHOP_PRODUCT_INITIAL_CONTENT}
 * {WPSHOP_PRODUCT_BUTTON_ADD_TO_CART}
 * {WPSHOP_PRODUCT_BUTTON_QUOTATION}
 * {WPSHOP_PRODUCT_BUTTONS}
 * {WPSHOP_PRODUCT_BUTTONS}
 * {WPSHOP_PRODUCT_GALERY_DOCS}
 * {WPSHOP_PRODUCT_FEATURES}
 */
ob_start();
?>
<div id="product_main_information_container" itemscope itemtype="http://data-vocabulary.org/Product" >
	<div id="product_galery" >
		{WPSHOP_PRODUCT_THUMBNAIL}
		{WPSHOP_PRODUCT_GALERY_PICS}
	</div>
	<div id="product_wp_initial_content" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers" >
		{WPSHOP_PRODUCT_PRICE}
		<p itemprop="description">{WPSHOP_PRODUCT_INITIAL_CONTENT}</p>
		{WPSHOP_PRODUCT_VARIATIONS}
		{WPSHOP_PRODUCT_BUTTONS}
		<div id="product_document_galery_container" >{WPSHOP_PRODUCT_GALERY_DOCS}</div>
	</div>
</div>
<div id="product_attribute_container" >{WPSHOP_PRODUCT_FEATURES}</div><?php
$tpl_element['product_complete_tpl'] = ob_get_contents();
ob_end_clean();


/*	Product mini display (List)										Produits mini liste */
ob_start();
?>
<li class="product_main_information_container-mini-list wpshop_clearfix wpshop_clear {WPSHOP_PRODUCT_CLASS}" itemscope itemtype="http://data-vocabulary.org/Product" >
	{WPSHOP_PRODUCT_EXTRA_STATE}
	<a href="{WPSHOP_PRODUCT_PERMALINK}" class="product_thumbnail-mini-list" title="{WPSHOP_PRODUCT_TITLE}">{WPSHOP_PRODUCT_THUMBNAIL}</a>
	<span class="product_information-mini-list" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
		<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" class="wpshop_clearfix">
			<h2 itemprop="name" >{WPSHOP_PRODUCT_TITLE}</h2>
			{WPSHOP_PRODUCT_PRICE}
			<p itemprop="description" class="wpshop_liste_description">{WPSHOP_PRODUCT_EXCERPT}</p>
		</a>
		{WPSHOP_PRODUCT_BUTTONS}
	</span>
</li><?php
$tpl_element['product_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Product mini display (grid)									Produits mini grid */
ob_start();
?>
<li class="product_main_information_container-mini-grid {WPSHOP_PRODUCT_CLASS}" itemscope itemtype="http://data-vocabulary.org/Product" >
	<a href="{WPSHOP_PRODUCT_PERMALINK}" title="{WPSHOP_PRODUCT_TITLE}" itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers" >
		<span class="wpshop_mini_grid_thumbnail product_thumbnail_{WPSHOP_PRODUCT_ID}">{WPSHOP_PRODUCT_THUMBNAIL}</span>
		{WPSHOP_PRODUCT_EXTRA_STATE}
		<h2 itemprop="name" >{WPSHOP_PRODUCT_TITLE}</h2>
		{WPSHOP_PRODUCT_PRICE}
	</a>
	{WPSHOP_PRODUCT_BUTTONS}
</li><?php
$tpl_element['product_mini_grid'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><div class="container_product_listing" ><ul class="products_listing wpshop_clearfix{WPSHOP_PRODUCT_CONTAINER_TYPE_CLASS}" >{WPSHOP_PRODUCT_LIST}</ul></div><?php
$tpl_element['product_list_container'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><span itemprop="price" class="wpshop_products_listing_price">{WPSHOP_PRODUCT_PRICE} {WPSHOP_TAX_PILOTING}</span><?php
$tpl_element['product_price_template_mini_output'] = ob_get_contents();
ob_end_clean();


/*	Product price display template	*/
ob_start();
?><h2 itemprop="price" class="wpshop_product_price" >{WPSHOP_PRODUCT_PRICE} {WPSHOP_TAX_PILOTING}</h2>
{WPSHOP_LOW_STOCK_ALERT_MESSAGE}
<?php
$tpl_element['product_price_template_complete_sheet'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc criteria list	*/
/*
 * {WPSHOP_SORTING_CRITERIA_LIST}
 */
ob_start();
?>
	<span>
		<?php _e('Sorting','wpshop'); ?>
		<select name="sorting_criteria" class="hidden_sorting_criteria_field" >
			<option value="" selected="selected"><?php _e('Choose...','wpshop'); ?></option>
			{WPSHOP_SORTING_CRITERIA_LIST}
		</select>
	</span><?php
$tpl_element['product_listing_sorting_criteria'] = ob_get_contents();
ob_end_clean();


/*	Sorting bloc */
/*
 * {WPSHOP_SORTING_HIDDEN_FIELDS}
 * {WPSHOP_SORTING_CRITERIA}
 * {WPSHOP_DISPLAY_TYPE_STATE_GRID}
 * {WPSHOP_DISPLAY_TYPE_STATE_LIST}
 */
ob_start();
?>
<div class="sorting_bloc">
	{WPSHOP_SORTING_HIDDEN_FIELDS}{WPSHOP_SORTING_CRITERIA}
	<ul class="wpshop_sorting_tools">
		<li><a href="#" class="ui-icon product_asc_listing reverse_sorting" title="<?php _e('Reverse','wpshop'); ?>"></a></li>
		<li><a href="#" class="change_display_mode list_display{WPSHOP_DISPLAY_TYPE_STATE_LIST}" title="<?php _e('Change to list display','wpshop'); ?>"></a></li>
		<li><a href="#" class="change_display_mode grid_display{WPSHOP_DISPLAY_TYPE_STATE_GRID}" title="<?php _e('Change to grid display','wpshop'); ?>"></a></li>
	</ul>
</div><?php
$tpl_element['product_listing_sorting'] = ob_get_contents();
ob_end_clean();


/**
 *
 *
 * Product front attachment galery
 *
 *
 */
/*	Product thumbnail (No thumbnail)	*/
ob_start();
?>
<img src="<?php echo WPSHOP_DEFAULT_PRODUCT_PICTURE; ?>" alt="<?php _e('Product has no image', 'wpshop'); ?>" class="default_picture_thumbnail" /><?php
$tpl_element['product_thumbnail_default'] = ob_get_contents();
ob_end_clean();

/*	Product thumbnail	*/
/**
 * {WPSHOP_PRODUCT_THUMBNAIL_URL}
 * {WPSHOP_PRODUCT_THUMBNAIL}
 */
ob_start();
?>
<a href="{WPSHOP_PRODUCT_THUMBNAIL_URL}" id="product_thumbnail" class="wpshop_picture_zoom_in" >{WPSHOP_PRODUCT_THUMBNAIL}</a><?php
$tpl_element['product_thumbnail'] = ob_get_contents();
ob_end_clean();

/*	Product attachment galery	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}
 */
ob_start();
?>
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery wpshop_clearfix" >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
$tpl_element['product_attachment_picture_galery'] = ob_get_contents();
ob_end_clean();

/*	Product attachment item picture ()	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_PICTURE}
 */
ob_start();
?>
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" ><a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" rel="appendix" >{WPSHOP_ATTACHMENT_ITEM_PICTURE_THUMBNAIL}</a></li><?php
$tpl_element['product_attachment_item_picture'] = ob_get_contents();
ob_end_clean();

/*	Product attachment galery	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}
 */
ob_start();
?>
<ul class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_galery wpshop_clearfix" >{WPSHOP_PRODUCT_ATTACHMENT_OUTPUT_CONTENT}</ul><?php
$tpl_element['product_attachment_galery'] = ob_get_contents();
ob_end_clean();

/*	Product attachment item document	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_TITLE}
 */
ob_start();
?>
<li class="product_{WPSHOP_ATTACHMENT_ITEM_TYPE}_item {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}" ><a href="{WPSHOP_ATTACHMENT_ITEM_GUID}" target="product_document" ><span>{WPSHOP_ATTACHMENT_ITEM_TITLE}</span></a></li><?php
$tpl_element['product_attachment_item_document'] = ob_get_contents();
ob_end_clean();



/**
 *
 *
 * Categories display
 *
 *
 */
/*	Mini category (list)	*/
/*
 * {WPSHOP_CATEGORY_LINK}
 * {WPSHOP_CATEGORY_THUMBNAIL}
 * {WPSHOP_CATEGORY_TITLE}
 * {WPSHOP_CATEGORY_DESCRIPTION}
 * {WPSHOP_CATEGORY_ITEM_WIDTH}
 *
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_CATEGORY_DISPLAY_TYPE}
 */
ob_start();
?><div class="category_main_information_container-mini-list wpshop_clear" >
	<a href="{WPSHOP_CATEGORY_LINK}" >
	<div class="category_thumbnail-mini-list" >{WPSHOP_CATEGORY_THUMBNAIL}</div>
		<div class="category_information-mini-list" >
			<div class="category_title-mini-list" >{WPSHOP_CATEGORY_TITLE}</div>
			<div class="category_more-mini-list" >{WPSHOP_CATEGORY_DESCRIPTION}</div>
		</div>
	</a>
</div><?php
$tpl_element['category_mini_list'] = ob_get_contents();
ob_end_clean();

/*	Mini category (grid)	*/
/*
 * {WPSHOP_CATEGORY_LINK}
 * {WPSHOP_CATEGORY_THUMBNAIL}
 * {WPSHOP_CATEGORY_TITLE}
 * {WPSHOP_CATEGORY_DESCRIPTION}
 * {WPSHOP_CATEGORY_ITEM_WIDTH}
 *
 * {WPSHOP_CATEGORY_ID}
 * {WPSHOP_CATEGORY_DISPLAY_TYPE}
 */
ob_start();
?><div class="category_main_information_container-mini-grid" style="width:{WPSHOP_ITEM_WIDTH};" >
	<a href="{WPSHOP_CATEGORY_LINK}" >
		<div class="category_thumbnail-mini-grid" >{WPSHOP_CATEGORY_THUMBNAIL}</div>
		<div class="category_information-mini-grid" >
			<div class="category_title-mini-grid" >{WPSHOP_CATEGORY_TITLE}</div>
			<div class="category_title-mini-grid" >{WPSHOP_CATEGORY_DESCRIPTION}</div>
		</div>
	</a>
</div><?php
$tpl_element['category_mini_grid'] = ob_get_contents();
ob_end_clean();



/*	Product attachment item document	*/
/**
 * {WPSHOP_ATTACHMENT_ITEM_TYPE}
 * {WPSHOP_ATTACHMENT_ITEM_SPECIFIC_CLASS}
 * {WPSHOP_ATTACHMENT_ITEM_GUID}
 * {WPSHOP_ATTACHMENT_ITEM_TITLE}
 */
ob_start();
?>
<li class="{WPSHOP_CUSTOMER_ADDRESS_ELEMENT_KEY}" >{WPSHOP_CUSTOMER_ADDRESS_ELEMENT}</li><?php
$tpl_element['customer_address_display'] = ob_get_contents();
ob_end_clean();




/**
 *
 *
 * Account display
 *
 *
 */
/*	Account form	*/
ob_start();
?><h2><?php _e('Personal information', 'wpshop'); ?></h2><div class="wpshop_customer_personnal_informations_form_container" >{WPSHOP_ACCOUNT_FORM_FIELD}</div><?php
$tpl_element['wpshop_account_form'] = ob_get_contents();
ob_end_clean();

/*	Account / Address form input	*/
ob_start();
?>
<p class="formField{WPSHOP_CUSTOMER_FORM_INPUT_MAIN_CONTAINER_CLASS}" ><label{WPSHOP_CUSTOMER_FORM_INPUT_LABEL_OPTIONS}>{WPSHOP_CUSTOMER_FORM_INPUT_LABEL}</label>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}</p><?php
$tpl_element['wpshop_account_form_input'] = ob_get_contents();
ob_end_clean();

/*	Account / Address form HIDDEN input	*/
ob_start();
?>{WPSHOP_CUSTOMER_FORM_INPUT_FIELD}<?php
$tpl_element['wpshop_account_form_hidden_input'] = ob_get_contents();
ob_end_clean();



/**	New entity quick add form	*/
ob_start();
?>
<div id="new_entity_quick_form_container" >
	<span id="wpshop_loading"> </span>
	<div class="wpshop_quick_add_entity_result wpshopHide" id="wpshop_quick_add_entity_result" ></div>
	<form action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST" id="new_entity_quick_form">
		<input type="hidden" name="attribute_set_id" id="attribute_set_id" value="{WPSHOP_ENTITY_ATTRIBUTE_SET_ID}" />
		<input type="hidden" name="entity_type" id="entity_type" value="{WPSHOP_ENTITY_TYPE}" />
		<input type="hidden" name="action" id="action" value="wpshop_quick_add_entity" />
		<input type="hidden" name="wpshop_ajax_nonce" id="wpshop_ajax_nonce" value="{WPSHOP_ENTITY_QUICK_ADDING_FORM_NONCE}" />
		<div class="wpshop_new_entity_form_field_container" >
			<div class="wpshop_new_entity_form_field wpshop_new_entity_form_field_specific" >{WPSHOP_NEW_ENTITY_FORM_DETAILS}</div>
		</div>
		<input type="submit" name="quick_entity_add_button" id="quick_entity_add_button" value="{WPSHOP_ENTITY_QUICK_ADD_BUTTON_TEXT}" />
	</form>
	{WPSHOP_DIALOG_BOX}
</div><?php
$tpl_element['quick_entity_add_form'] = ob_get_contents();
ob_end_clean();

/**	Display a input type text for wordpress internal fields (as post title)	*/
ob_start();
?>
<input type="text" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_text'] = ob_get_contents();
ob_end_clean();

/**	Display a input type file for wordpress internal fields (as post thumbnail sender)	*/
ob_start();
?>
<input type="file" value="{WPSHOP_WP_FIELD_VALUE}" name="wp_fields[{WPSHOP_WP_FIELD_NAME}]" id="wp_fields_{WPSHOP_WP_FIELD_NAME}" /><?php
$tpl_element['quick_entity_wp_internal_field_file'] = ob_get_contents();
ob_end_clean();

/**	Define the container for internal input	*/
ob_start();
?>
<div class="wpshop_clear">
	<div class="wpshop_form_label {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_label _{WPSHOP_WP_FIELD_NAME}_label alignleft">{WPSHOP_WP_FIELD_LABEL}</div>
	<div class="wpshop_form_input_element {WPSHOP_ENTITY_TYPE_TO_CREATE}_{WPSHOP_WP_FIELD_NAME}_input _{WPSHOP_WP_FIELD_NAME}_input alignleft">{WPSHOP_WP_FIELD_INPUT}</div>
</div><?php
$tpl_element['quick_entity_wp_internal_field_output'] = ob_get_contents();
ob_end_clean();

/**	Define template of element allowing to add a new to value to an attribute of list type	*/
ob_start();
?>
<div class="wpshop_attribute_new_creator_condition" ><?php _e('Or', 'wpshop'); ?></div><div class="wpshop_attribute_new_creator_field" ><input type="text" placeholder="<?php _e('Create a new element', 'wsphop'); ?>" name="{WPSHOP_NEW_ELEMENT_CREATION_FIELD}" /></div><?php
$tpl_element['quick_entity_specific_field_new_element'] = ob_get_contents();
ob_end_clean();



/**	Product configuration viewer main container	*/
/**		<div class="wpshop_product_variation_summary_currency_selector" >{WPSHOP_CURRENCY_SELECTOR}</div>		*/
ob_start();
?><div class="wpshop_product_variation_summary_main_container" ><h3 class="widget-title"><?php _e('Product configuration summary', 'wpshop'); ?></h3><div class="wpshop_product_variation_summary_container" id="wpshop_product_variation_summary_container" ></div><div class="wpshop_clear" ></div></div><?php
$tpl_element['wpshop_product_configuration_summary'] = ob_get_contents();
ob_end_clean();

/**	Product configuration viewer		Display all option for product with options	*/
ob_start();
?><div class="wpshop_product_variation_summary_product_name" >{WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_NAME}</div>
<ul class="wpshop_product_variation_summary_product_details" >{WPSHOP_PRODUCT_VARIATION_SUMMARY_DETAILS}</ul>
{WPSHOP_PRODUCT_VARIATION_SUMMARY_MORE_CONTENT}
<div class="wpshop_product_variation_summary_product_final_price alignright" ><?php _e('Product final price', 'wpshop'); ?> {WPSHOP_PRODUCT_MAIN_INFO_PRODUCT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div>
{WPSHOP_PRODUCT_VARIATION_SUMMARY_GRAND_TOTAL}
{WPSHOP_PARTIAL_PAYMENT_INFO}<?php
$tpl_element['wpshop_product_configuration_summary_detail'] = ob_get_contents();
ob_end_clean();

/*	Auto add to cart product line	| 						 */
ob_start();
?><div class="wpshop_product_variation_summary_auto_product alignright" >{WPSHOP_AUTO_PRODUCT_NAME} {WPSHOP_AUTO_PRODUCT_PRODUCT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div><?php
$tpl_element['wpshop_product_configuration_summary_detail_auto_product'] = ob_get_contents();
ob_end_clean();

/*	Current product configuration grand total line	| 						 */
ob_start();
?><div class="wpshop_clear wpshop_product_variation_summary_grand_total alignright" ><?php _e('Grand total', 'wpshop'); ?> {WPSHOP_SUMMARY_FINAL_RESULT_PRICE} {WPSHOP_CURRENCY_CHOOSEN} </div><?php
$tpl_element['wpshop_product_configuration_summary_detail_final_result'] = ob_get_contents();
ob_end_clean();




/**	Main container for display information about attribute that are configured to display description in frontend	*/
ob_start();
?><div class="wpshop_clear wpshop_product_variation_value_detail_main_container" id="wpshop_product_variation_value_detail_main_container" ></div><?php
$tpl_element['wpshop_product_variation_value_detail_container'] = ob_get_contents();
ob_end_clean();

/**	Main container for display information about attribute that are configured to display description in frontend	*/
ob_start();
?><h3 class="widget-title"><?php _e('Details about', 'wpshop'); ?> {WPSHOP_VARIATION_ATTRIBUTE_NAME_FOR_DETAIL}</h3>
<div class="wpshop_product_variation_value_detail_container" >
	<div class="wpshop_product_variation_value_detail_title" >{WPSHOP_VARIATION_VALUE_TITLE_FOR_DETAIL}</div>
	<div class="wpshop_product_variation_value_detail_description" >{WPSHOP_VARIATION_VALUE_DESC_FOR_DETAIL}</div>
	<div class="wpshop_product_variation_value_detail_link" ><a href="{WPSHOP_VARIATION_VALUE_LINK_FOR_DETAIL}" target="_blank" ><?php _e('View details', 'wpshop'); ?></a></div>
</div><?php
$tpl_element['wpshop_product_variation_value_detail_content'] = ob_get_contents();
ob_end_clean();




/**
 *
 * Checkout page
 *
 */
ob_start();
?><form method="post" name="checkoutForm" action="<?php echo get_permalink(get_option('wpshop_checkout_page_id')); ?>" >
	{WPSHOP_CHECKOUT_CUSTOMER_ADDRESSES_LIST}
	<h2>{WPSHOP_CHECKOUT_SUMMARY_TITLE}</h2>
	{WPSHOP_CHECKOUT_CART_CONTENT}
	
	<div>
		<?php _e('Comments about the order','wpshop'); ?>
		<textarea name="order_comments"></textarea>
	</div>
	{WPSHOP_CHECKOUT_PAYMENT_METHODS}
	<div{WPSHOP_CHECKOUT_PAYMENT_BUTTONS_CONTAINER}>{WPSHOP_CHECKOUT_TERM_OF_SALES}
		{WPSHOP_CHECKOUT_PAYMENT_BUTTONS}
	</div>
</form><?php
$tpl_element['wpshop_checkout_page'] = ob_get_contents();
ob_end_clean();

/**
 * Checkout page validation button
 */
ob_start();
?><input type="submit" name="takeOrder" value="{WPSHOP_CHECKOUT_PAGE_VALIDATION_BUTTON_TEXT}" /><?php
$tpl_element['wpshop_checkout_page_validation_button'] = ob_get_contents();
ob_end_clean();

/**
 * Payment method bloc
 */
ob_start();
?><table class="blockPayment{WPSHOP_CHECKOUT_PAYMENT_METHOD_STATE_CLASS}">
	<tr>
		<td class="paymentInput rounded-left"><input type="radio" name="modeDePaiement"{WPSHOP_CHECKOUT_PAYMENT_METHOD_INPUT_STATE} value="{WPSHOP_CHECKOUT_PAYMENT_METHOD_IDENTIFIER}" /></td>
		<td class="paymentImg"><img src="{WPSHOP_CHECKOUT_PAYMENT_METHOD_ICON}" alt="{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}" title="<?php echo sprintf(__('Pay by %s', 'wpshop'), '{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}'); ?>" /></td>
		<td class="paymentName">{WPSHOP_CHECKOUT_PAYMENT_METHOD_NAME}</td>
		<td class="last rounded-right">{WPSHOP_CHECKOUT_PAYMENT_METHOD_EXPLANATION}</td>
	</tr>
</table><?php
$tpl_element['wpshop_checkout_page_payment_method_bloc'] = ob_get_contents();
ob_end_clean();

/**
 * Check method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<p><?php _e('You have to send the check with the good amount to the adress :', 'wpshop'); ?></p>
<p>{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_NAME}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_STREET}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_POSTCODE}, {WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_CITY}<br/>
{WPSHOP_CHECK_CONFIRMATION_MESSAGE_COMPANY_COUNTRY}</p>
<p><?php _e('Your order will be shipped upon receipt of the check.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_check_confirmation_message'] = ob_get_contents();
ob_end_clean();

/**
 * Check method confirmation message
 */
ob_start();
?><p><?php _e('Thank you ! Your order has been placed and you will receive a confirmation email shortly.', 'wpshop'); ?></p>
<p><?php _e('You have to do a bank transfer on account detailled below:', 'wpshop'); ?></p>
<p><?php _e('Bank name', 'wpshop'); ?>{WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_BANK_NAME}<br/>
<?php _e('IBAN', 'wpshop'); ?>{WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_IBAN}<br/>
<?php _e('BIC/SWIFT', 'wpshop'); ?>{WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_BIC}<br/>
<?php _e('Account owner name', 'wpshop'); ?>{WPSHOP_BANKTRANSFER_CONFIRMATION_MESSAGE_ACCOUNTOWNER}</p>
<p><?php _e('Your order will be shipped upon receipt of funds.', 'wpshop'); ?></p><?php
$tpl_element['wpshop_checkout_page_banktransfer_confirmation_message'] = ob_get_contents();
ob_end_clean();


/**	Display informations about partial payment	*/
ob_start();
?><div class="wpshop_clear alignright wpshop_partial_payment" ><?php _e('Payable now', 'wpshop'); ?> ({WPSHOP_PARTIAL_PAYMENT_CONFIG_AMOUNT}{WPSHOP_PARTIAL_PAYMENT_CONFIG_TYPE}) {WPSHOP_PARTIAL_PAYMENT_AMOUNT} {WPSHOP_CURRENCY_CHOOSEN}</div><?php
$tpl_element['wpshop_partial_payment_display'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer newsletter preference
 *
 */
ob_start();
?><div class="wpshop_customer_newsletter_pref_container" >
	<div class="wpshop_customer_newsletter_pref_site_container" ><input id="newsletters_site" type="checkbox" name="newsletters_site"{WPSHOP_CUSTOMER_PREF_NEWSLETTER_SITE}><label for="newsletters_site"><?php _e('I want to receive promotional information from the site','wpshop'); ?></label></div>
	<div class="wpshop_customer_newsletter_pref_site_partners_container" ><input id="newsletters_site_partners" type="checkbox" name="newsletters_site_partners"{WPSHOP_CUSTOMER_PREF_NEWSLETTER_SITE_PARTNERS}><label for="newsletters_site_partners"><?php _e('I want to receive promotional information from partner companies','wpshop'); ?></label></div>
</div><?php
$tpl_element['wpshop_customer_preference_for_newsletter'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer account information form
 *
 */
ob_start();
?><div id="reponseBox"></div>
<form  method="post" id="register_form" action="<?php echo admin_url('admin-ajax.php'); ?>">
	<input type="hidden" name="wpshop_ajax_nonce" value="{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM_NONCE}" />
	<input type="hidden" name="action" value="wpshop_save_customer_account" />
	<div class="col1 wpshopShow" id="register_form_classic">
		{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM}
		{WPSHOP_CUSTOMER_ACCOUNT_INFOS_FORM_BUTTONS}
	</div>
</form><?php
$tpl_element['wpshop_customer_account_infos_form'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer addresses form
 *
 */
ob_start();
?><div id="reponseBox"></div>
<form method="post" name="billingAndShippingForm">
	<div class="col1 wpshopShow" id="register_form_classic">
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_CONTENT}
		{WPSHOP_CUSTOMER_ADDRESSES_FORM_BUTTONS}
	</div>
</form><?php
$tpl_element['wpshop_customer_addresses_form'] = ob_get_contents();
ob_end_clean();


/**
 *
 * Customer addresses type choice form
 *
 */
ob_start();
?><h1><?php _e('Address Type','wpshop'); ?></h1>
	<form id="selectNewAddress" method="post" action="{WPSHOP_ADDRESS_TYPE_CHOICE_FORM_ACTION}">
		<div class="create-account">
			<p><?php _e('Select the address type you want to create','wpshop'); ?></p>
			{WPSHOP_ADDRESS_TYPE_LISTING_INPUT}
		</div>
		<input type="hidden" name="referer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>" />
		<input type="submit" name="chooseAddressType" value="<?php _e('Choose','wpshop'); ?>" />
	</form><?php
$tpl_element['wpshop_customer_new_addresse_type_choice_form'] = ob_get_contents();
ob_end_clean();


/**
 *
 *
 * Frontend search
 *
 *
 */
/*	Form field	*/
ob_start();
?>
<div><label{WPSHOP_FIELD_LABEL_POINTER}>{WPSHOP_FIELD_LABEL_TEXT}</label> : {WPSHOP_FIELD_INPUT}</div><?php
$tpl_element['advanced_search_form_input'] = ob_get_contents();
ob_end_clean();

/*	Form	*/
ob_start();
?>
<form method="post" >
	<div><label for="wpshop_search_post_title" ><?php _e('Name','wpshop'); ?></label> : <input type="text" class="wpshop_advanced_search_field wpshop_advanced_search_field_post_title" name="wpshop_search_post_title" name="wpshop_search_post_title"  value="{WPSHOP_SEARCHED_POST_TITLE}" /></div>
	{WPSHOP_SPECIAL_FIELDS}
	<input type="submit" name="search" value="<?php _e('Search','wpshop'); ?>" />
</form><?php
$tpl_element['advanced_search_form'] = ob_get_contents();
ob_end_clean();


/* Order administrator email */
ob_start();
?><table style="background :#f3f3f3; width:800px; border : 1px solid #A4A4A4"><tr bgcolor="#74C2FD" height="80" valign="middle" align="center"><td width="100"><?php _e('Reference', 'wpshop'); ?></td><td width="300"><?php _e('Products', 'wpshop'); ?></td><td width="100"><?php _e('Quantity', 'wpshop'); ?></td><td width="100"><?php _e('Unit price ET', 'wpshop'); ?></td><td width="100"><?php _e('Total HT', 'wpshop'); ?></td></tr><?php
$tpl_element['administrator_order_email_head'] = ob_get_contents();
ob_end_clean();

/* Order administrator email */
ob_start();
?>
<tr height="40" valign="middle" align="center"><td>{WPSHOP_ITEM_REF}</td><td align="center">{WPSHOP_ITEM_NAME}</td><td align="center">{WPSHOP_ITEM_QTY}</td><td>{WPSHOP_ITEM_PU_HT}</td><td align="center">{WPSHOP_TOTAL_HT}</td></tr>
<?php
$tpl_element['line_administrator_order_email'] = ob_get_contents();
ob_end_clean();


/* Order administrator email */
ob_start();
?>
<tr height="40" valign="middle"><td colspan="4" align="right"><?php _e('Total ET', 'wpshop'); ?> </td><td align="center">{WPSHOP_TOTAL_HT}</td></tr>
<?php
$tpl_element['total_ht_administrator_order_email'] = ob_get_contents();
ob_end_clean();
/* Order administrator email */
ob_start();
?><tr height="40" valign="middle"><td colspan="4" align="right"><?php _e('Taxes', 'wpshop'); ?> ({WPSHOP_TVA_RATE} %) </td><td align="center">{WPSHOP_TVA}</td></tr><?php
$tpl_element['tva_administrator_order_email'] = ob_get_contents();
ob_end_clean();

ob_start();
?>{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}<br/><?php
$tpl_element['common']['default']['admin_email_summary']['email_content']['product_option']['cart_variation_detail'] = ob_get_contents();
ob_end_clean();


/* Order administrator email */
ob_start();
?>
<tr height="40" valign="middle"><td colspan="4" align="right"><?php _e('Total ATI before discount', 'wpshop'); ?> </td><td align="center">{WPSHOP_TOTAL_BEFORE_DISCOUNT}</td></tr><tr height="40" valign="middle"><td colspan="4" align="right"><?php _e('Shipping cost', 'wpshop'); ?> </td><td align="center">{WPSHOP_TOTAL_SHIPPING_COST}</td></tr><tr height="40" valign="middle"><td colspan="4" align="right"><?php _e('Total ATI', 'wpshop'); ?> </td><td align="center">{WPSHOP_TOTAL_ATI}</td></tr></table>
<?php
$tpl_element['total_order_administrator_order_email'] = ob_get_contents();
ob_end_clean();

ob_start();
?><table style="background :#f3f3f3; width:390px; border : 1px solid #A4A4A4; float : left; margin-right : 10px; margin-bottom:20px;"><tr bgcolor="#74C2FD" height="50" valign="middle" align="center"><td>{WPSHOP_ADDRESS_TYPE}</td></tr><tr><td>
{WPSHOP_CUSTOMER_CIVILITY} {WPSHOP_CUSTOMER_LAST_NAME} {WPSHOP_CUSTOMER_FIRST_NAME}<br/>
{WPSHOP_CUSTOMER_ADDRESS}<br/>
{WPSHOP_CUSTOMER_POSTCODE} {WPSHOP_CUSTOMER_CITY}<br/>
{WPSHOP_CUSTOMER_STATE}<br/>
{WPSHOP_CUSTOMER_COUNTRY}</td>
</tr>
</table>
<?php
$tpl_element['address_order_email'] = ob_get_contents();
ob_end_clean();

/* Order administrator email */
ob_start();
?>
<table style="background :#f3f3f3; width:800px; border : 1px solid #A4A4A4; clear : both;">
<tr >
<td width="800" valign="middle" align="left" bgcolor="#74C2FD" height="40" width="800" >{WPSHOP_CUSTOMER_COMMENT_TITLE}</td>
</tr>
<tr>
<td width="800">{WPSHOP_CUSTOMER_COMMENT}</td></tr>
</table>
<?php
$tpl_element['customer_comments_order_email'] = ob_get_contents();
ob_end_clean();

/****ADDRESSES DASHBOARD TEMPLATE ****/
/*Addresses DashBoard Head-Links*/
ob_start();
?>
<p class="formField">
<a href="{WPSHOP_LOGOUT_LINK_ADDRESS_DASHBOARD}" title="<?php _e('Logout','wpshop'); ?>" class="right"><?php _e('Logout','wpshop'); ?></a>
<a href="{WPSHOP_ACCOUNT_LINK_ADDRESS_DASHBOARD}" title="<?php _e('Edit my account infos', 'wpshop'); ?>"><?php _e('Edit my account infos', 'wpshop'); ?></a>
</p>
<?php
$tpl_element['link_head_addresses_dashboard'] = ob_get_contents();
ob_end_clean();


/*Addresses DashBoard  shipping & billing addresses display*/
ob_start();
?><div id="wpshop_customer_adresses_container_{WPSHOP_ADDRESS_TYPE}" class="big wpshop_customer_adresses_container wpshop_customer_adresses_container_{WPSHOP_ADDRESS_TYPE}" >
<input type="hidden" id="hidden_input_{WPSHOP_ADDRESS_TYPE}" name="{WPSHOP_ADDRESS_TYPE}" value="{WPSHOP_DEFAULT_ADDRESS_ID}" />
	<h3>
		{WPSHOP_CUSTOMER_ADDRESS_TYPE_TITLE}
		{WPSHOP_ADDRESS_COMBOBOX}
	</h3>

	<div class="wpshop_addresses_management_buttons">
		{WPSHOP_ADDRESS_BUTTONS}
		<div class="wpshop_clear" ></div>
	</div>

	<div id="choosen_address_{WPSHOP_ADDRESS_TYPE}" class="choosen_address_{WPSHOP_ADDRESS_TYPE}">
		{WPSHOP_CUSTOMER_CHOOSEN_ADDRESS}
		<div class="wpshopHide" id="loader_{WPSHOP_ADDRESS_TYPE}" ><img src="{WPSHOP_LOADING_ICON}" alt="loading..." /></div>
	</div>
</div>
<?php
$tpl_element['display_addresses_by_type_container'] = ob_get_contents();
ob_end_clean();


ob_start();
?><div id="edit_link_{WPSHOP_ADDRESS_TYPE}" class="alignleft"><a href="{WPSHOP_choosen_address_LINK_EDIT}" title="<?php _e('Edit', 'wpshop'); ?>"><?php _e('Edit', 'wpshop'); ?></a></div>
<?php
$tpl_element['addresses_box_actions_button_edit'] = ob_get_contents();
ob_end_clean();

ob_start();
?><a href="{WPSHOP_ADD_NEW_ADDRESS_LINK}" class="alignright" title="{WPSHOP_ADD_NEW_ADDRESS_TITLE}">{WPSHOP_ADD_NEW_ADDRESS_TITLE}</a><?php
$tpl_element['addresses_box_actions_button_new_address'] = ob_get_contents();
ob_end_clean();


/* ADDRESSES LIST BY TYPE COMBOBOX*/
ob_start();
?><select class="alignright address_choice_select" id='{WPSHOP_ADDRESS_TYPE}'>{WPSHOP_ADDRESS_COMBOBOX_OPTION}</select><?php
$tpl_element['addresses_type_combobox'] = ob_get_contents();
ob_end_clean();


/* ADDRESS CONTAINER */
ob_start();
?><ul class="wpshop_customer_adress_container{WPSHOP_ADRESS_CONTAINER_CLASS}" >{WPSHOP_CUSTOMER_ADDRESS_CONTENT}</ul><?php
$tpl_element['display_address_container'] = ob_get_contents();
ob_end_clean();


/* ADDRESS EACH LINE */
ob_start();
?>
<li class="{WPSHOP_CUSTOMER_ADDRESS_ELEMENT_KEY}" >{WPSHOP_CUSTOMER_ADDRESS_ELEMENT}&nbsp;</li><?php
$tpl_element['display_address_line'] = ob_get_contents();
ob_end_clean();


/* ADDRESS EACH LINE */
ob_start();
?><div class="wpshop_terms_box" id="wpshop_terms_acceptation_box" >{WPSHOP_TERMS_ACCEPTATION_BOX_CONTENT}</div><?php
$tpl_element['wpshop_terms_box'] = ob_get_contents();
ob_end_clean();
?>