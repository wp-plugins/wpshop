<div class="sorting_bloc<?php if(!empty($atts['sorting']) && ($atts['sorting'] == 'no')) echo ' wpshopHide'; ?>">
	<span>
		<?php _e('Sorting','wpshop'); ?> 
		<select name="sorting_criteria" class="hidden_sorting_criteria_field" >
			<option value="" selected="selected"><?php _e('Choose...','wpshop'); ?></option>
			<?php foreach($sorting_criteria as $c): ?>
				<option value="<?php echo $c['code']; ?>"><?php echo __($c['frontend_label'],'wpshop'); ?></option>
			<?php endforeach; ?>
		</select>
	</span>
	<input type="hidden" name="display_type" value="<?php echo $type; ?>" class="hidden_sorting_fields" />
	<input type="hidden" name="order" value="<?php echo $order_by_sorting; ?>" class="hidden_sorting_fields" />
	<input type="hidden" name="products_per_page" value="<?php echo $pagination; ?>" class="hidden_sorting_fields" />
	<input type="hidden" name="page_number" value="1" />
	<input type="hidden" name="cid" value="<?php echo $cid; ?>" class="hidden_sorting_fields" />
	<input type="hidden" name="pid" value="<?php echo $pid; ?>" class="hidden_sorting_fields" />
	<input type="hidden" name="attr" value="<?php echo $attr; ?>" class="hidden_sorting_fields" />

	<ul class="wpshop_sorting_tools">
		<li><a href="#" id="reverse_sorting" class="ui-icon product_asc_listing" title="<?php _e('Reverse','wpshop'); ?>"></a></li>
		<li><a href="#" id="list_display" class="change_display_mode<?php echo $type=='list'?' active':null;?>" title="<?php _e('Change to list display','wpshop'); ?>"></a></li>
		<li><a href="#" id="grid_display" class="change_display_mode<?php echo $type=='grid'?' active':null;?>" title="<?php _e('Change to grid display','wpshop'); ?>"></a></li>
	</ul>
</div>