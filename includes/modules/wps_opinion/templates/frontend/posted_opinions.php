<span class="wps-h5"><?php _e( 'My opinions', 'wpshop'); ?></span>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Opinion', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Rate', 'wpshop'); ?></div>
	</div>

	<?php 
	if( !empty($posted_opinions) ) : 
		foreach( $posted_opinions as $posted_opinion ) : 
			require( $this->get_template_part( "frontend", "posted_opinion") );
		endforeach;
	endif; 
	?>
</div>
