<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="en-US">
<!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php printf( __( 'Invoice n°%s', 'wpshop'), $invoice_ref ); ?> - <?php bloginfo( 'name'); ?></title>
		<?php echo $invoice_css; ?>
	</head>
	<body>
		<table class="invoice_main_title_container" >
			<tbody>
				<tr>
					<td class="invoice_logo">
					<?php 
					$shop_logo = get_option('wpshop_logo');
					if( !empty($shop_logo) ) :
					?>
						<img src="<?php echo $shop_logo; ?>" alt="<?php bloginfo( 'name'); ?>" />
					<?php endif; ?>
					</td>
					<td class="invoice_main_title" ><?php _e( 'Invoice', 'wpshop' ); ?></td>
				</tr>
				<tr>
					<td></td>
					<td >
						<?php echo $invoice_ref; ?>
						<br/><br/>
						<?php echo ( !empty($order_postmeta['order_key']) ) ? __( 'Order ID', 'wpshop' ). ' : '.$order_postmeta['order_key'] : ''; ?>
						<br/><?php echo ( !empty($order_postmeta['order_date']) ) ? __( 'Order date', 'wpshop' ). ' : '. mysql2date('d F Y', $order_postmeta['order_date'], true) : '' ; ?><br/>
					</td>
				</tr>
			</tbody>
		</table>
	
		<table class="invoice_part_main_container" >
			<tbody>
				<tr>
					<td class="invoice_sender_title" ><?php _e('Sender', 'wpshop'); ?></td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_title" ><?php _e('Customer', 'wpshop'); ?></td>
				</tr>
				<tr>
					<td class="invoice_sender_container" valign="top">
						<?php echo $invoice_sender_data; ?>
					</td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_container" valign="top">
						<?php echo $invoice_receiver_data; ?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<h4 style="text-align: right; width: 100%; margin: 30px 0px 0px;"><?php $invoice_amount_informations; ?></h4>
		
		<!-- Ordered products part -->
		<table class="invoice_lines" >
			<thead>
				<tr>
					<th><?php _e('Ref.', 'wpshop'); ?></th>
					<th><?php _e('Name', 'wpshop'); ?></th>
					<th><?php _e('Qty', 'wpshop'); ?></th>
					<th><?php _e('U.P ET', 'wpshop'); ?></th>
					<th><?php _e('Total ET', 'wpshop'); ?></th>
					<th><?php _e('Taxes amount', 'wpshop'); ?></th>
					<th><?php _e('Total ATI', 'wpshop'); ?></th>
				</tr>
			</thead>
			<tbody>
				
				<?php if( !empty($order_postmeta['order_items']) ) : ?>
					<?php foreach( $order_postmeta['order_items'] as $item_id => $item ) : ?>
					<tr>
						<td class="invoice_line_ref" ><?php echo ( !empty($item['item_ref']) ) ? $item['item_ref'] : ''; ?></td>
						<td class="invoice_line_product_name" >
							<?php 
								// Product name
								$item_name = ( !empty($item['item_name']) ) ? $item['item_name'] : '';
								// Check if product is variation
								if( get_post_type( $item['item_id'] ) == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) : 
									$parent_data = wpshop_products::get_parent_variation( $item['item_id'] ); 
									if( !empty($parent_data) && !empty($parent_data['parent_post']) ) : 
										$item_name = $parent_data['parent_post']->post_title;
									endif;
								endif;
								
								// Product variation list
								$product_attribute_order_detail = wpshop_attributes_set::getAttributeSetDetails( get_post_meta($item['item_id'], WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true)  ) ;
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
								$row_item_details = '';
								if( !empty($variation_attribute_ordered['attribute_list']) ) : 
									foreach ( $variation_attribute_ordered['attribute_list'] as $attribute_variation_to_output ) : 
									$row_item_details .= '<li>' .$attribute_variation_to_output. '</li>';
									endforeach;
								endif;
							?>
							<?php echo $item_name; ?>
							<ul><?php echo $row_item_details; ?></ul>
						</td>
						<td class="wpshop_aligncenter" ><?php echo ( !empty($item['item_qty']) ) ? $item['item_qty'] : 1 ?></td>
						<td class="wpshop_alignright" ><?php echo ( !empty( $item['item_pu_ht'] ) ) ?  wpshop_tools::formate_number( $item['item_pu_ht'] ) : ''; ?></td>
						<td class="wpshop_alignright" ><?php echo ( !empty( $item['item_total_ht'] ) ) ?  wpshop_tools::formate_number( $item['item_total_ht'] ) : ''; ?></td>
						<td class="wpshop_alignright" >
							<?php echo ( !empty( $item['item_tva_total_amount'] ) ) ?  wpshop_tools::formate_number( $item['item_tva_total_amount'] ) : ''; ?> 
							(<?php echo ( !empty( $item['item_tva_rate'] ) ) ?  wpshop_tools::formate_number( $item['item_tva_rate'] ) : ''; ?> %)
						</td>
						<td class="wpshop_alignright" ><?php echo ( !empty( $item['item_total_ttc'] ) ) ?  wpshop_tools::formate_number( $item['item_total_ttc'] ) : ''; ?></td>
					</tr>
					<?php endforeach; ?>
				<?php endif;?>

			</tbody>
		</table>
		
		<!-- Order summary display -->
		<table class="wpshop_invoice_summaries_container" >
			<tbody>
			<tr>
				<td class="wpshop_invoice_summaries_container_infos" ></td>
				<td class="wpshop_invoice_summaries_container_totals" >
				<table class="invoice_summary" >
				<tbody>
				<tr>
				<td class="invoice_summary_row_title" ><?php _e('Order grand total ET', 'wpshop'); ?></td>
				<td class="invoice_summary_row_amount" ><?php echo ( !empty($order_postmeta['order_total_ht']) ) ?  wpshop_tools::formate_number( $order_postmeta['order_total_ht'] ) : ''; ?> <?php echo $wps_currency; ?></td>
			</tr>
			{WPSHOP_INVOICE_SUMMARY_TOTAL_DISCOUNTED}
			
			<?php
			 if( !empty($order_postmeta['order_tva']) ) : 
				foreach( $order_postmeta['order_tva'] as $tva_id => $tva_amount ) : 
			?>
				<tr class="wpshop_invoice_grand_total" >
					<td class="invoice_summary_row_title" ><?php _e('Shipping cost', 'wpshop'); ?></td>
					<td class="invoice_summary_row_amount" ><?php echo ( !empty($order_postmeta['order_shipping_cost']) ) ?  wpshop_tools::formate_number( $order_postmeta['order_shipping_cost']) : '' ; ?> <?php echo $wps_currency; ?></td>
				</tr>
			<?php	
				endforeach;
			 endif;
			?>
				<tr class="wpshop_invoice_grand_total" >
					<td class="invoice_summary_row_title" ><?php _e('Shipping cost', 'wpshop'); ?></td>
					<td class="invoice_summary_row_amount" ><?php echo ( !empty($order_postmeta['order_shipping_cost']) ) ?  wpshop_tools::formate_number( $order_postmeta['order_shipping_cost']) : '' ; ?> <?php echo $wps_currency; ?></td>
				</tr>
			<tr class="wpshop_invoice_grand_total" >
				<td class="invoice_summary_row_title" ><?php _e('Shipping cost', 'wpshop'); ?></td>
				<td class="invoice_summary_row_amount" ><?php echo ( !empty($order_postmeta['order_shipping_cost']) ) ?  wpshop_tools::formate_number( $order_postmeta['order_shipping_cost']) : '' ; ?> <?php echo $wps_currency; ?></td>
			</tr>
			{WPSHOP_INVOICE_ORDER_DISCOUNT}
			<tr class="wpshop_invoice_grand_total" >
				<td class="invoice_summary_row_title" ><?php _e('Order grand total ATI', 'wpshop'); ?></td>
										<td class="invoice_summary_row_amount" ><?php echo ( !empty($order_postmeta['order_grand_total']) ) ? wpshop_tools::formate_number( $order_postmeta['order_grand_total'] ): '' ?> <?php echo $wps_currency; ?></td>
									</tr>
									{WPSHOP_INVOICE_SUMMARY_MORE}
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
		</table>
		
		
		
		<table class="iban_infos">
			<tr><td>
				<?php echo $invoice_iban_informations; ?>
			</td></tr>
		</table>
		{WPSHOP_RECEIVED_PAYMENT}
		{WPSHOP_INVOICE_FOOTER}
		
		
		
	</body>
</html>
 