<div>
	<?php 
	wpshop_paypal::display_form($_SESSION['order_id']);
	wpshop_cart::empty_cart(); 
	?>
</div>
