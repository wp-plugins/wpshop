<div class="wps-boxed" id="wpshop_filter_search_container">
	<div class="wps-gridwrapper" >
		<div class="wps-grid4x6" id="wpshop_filter_search_count_products"></div>
		<div class="wps-grid2x6"><button id="init_fields" class="wps-bton-mini-rounded-second alignRight"><?php _e('Init fields', 'wpshop'); ?></button></div>
	</div>
	
	
	<div>
		<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" name=" " id="filter_search_action">
			<input type="hidden" name="action" value="filter_search_action" />
			<input type="hidden" name="wpshop_filter_search_category_id" value="<?php echo $category_id; ?>" />
			<input type="hidden" name="wpshop_filter_search_current_page_id" id="wpshop_filter_search_current_page_id" value="1" />
		
			<?php echo $filter_elements; ?>
		</form>
	</div>
</div>
