<div class="wps-table-content wps-table-row">
		<div class="wps-table-cell"><a href="<?php echo get_permalink($ordered_product); ?>"><?php echo get_the_post_thumbnail( $ordered_product, 'thumbnail' ); ?></a></div>
		<div class="wps-table-cell"><a href="<?php echo get_permalink($ordered_product); ?>" target="_blank"><?php echo get_the_title( $ordered_product ); ?></a></div>
		<div class="wps-table-cell"><button class="wps-bton-first-mini-rounded wps-add-opinion-opener" id="wps-add-opinion-<?php echo $ordered_product; ?>"><?php _e( 'Add your opinion', 'wpshop'); ?></button></div>
</div>
