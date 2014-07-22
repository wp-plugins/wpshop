<!--  
<div id="wps_opinion_list">
	<?php
	 if( !empty($opinions) ) : 
		foreach( $opinions as $opinion ): 
			require( $this->get_template_part( "frontend", "opinion") );
		endforeach; 
	 endif; 
	 ?>
</div>
-->
<span class="wps-h5"><?php _e( 'My opinions', 'wpshop'); ?></span>
<div class="wps-table">
	<div class="wps-table-header wps-table-row">
		<div class="wps-table-cell"><?php _e( 'Date', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Product', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Opinion', 'wpshop'); ?></div>
		<div class="wps-table-cell"><?php _e( 'Rate', 'wpshop'); ?></div>
	</div>

	<?php
	 if( !empty($opinions) ) : 
		foreach( $opinions as $opinion ): 
			require( $this->get_template_part( "frontend", "opinion") );
		endforeach; 
	else : 
		echo __( 'You have never post opinion', 'wpshop');
	endif;  
	 ?>
</div>


