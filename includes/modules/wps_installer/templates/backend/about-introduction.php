<div class="changelog" >

	<div class="feature-section col three-col">
		<div class="col-1">
			<h4><?php _e( 'Configure my shop', 'wpshop'); ?></h4>
			<p><?php printf( __( 'We update WPShop in order to improve speed, accessibility and mobile usage. Go on %smain settings interface%s in order to configure your new shop, payment gateways or shipping methods', 'wpshop'), '<a href="' . admin_url( 'options-general.php?page=wpshop_option' ) . '" target="_wps_settings_interface" >', '</a>' ); ?></p>
			<p><a href="<?php echo admin_url( 'options-general.php?page=wpshop_option' ); ?>" class="button button-large button-primary"><?php _e( 'Configure my shop', 'wpshop'); ?></a></p>
		</div>
		<div class="col-2">
			<h4><?php _e( 'Create products', 'wpshop'); ?></h4>
			<p><?php _e( 'Create a catalog easily', 'wpshop'); ?></p>
			<p><a href="<?php echo admin_url( 'edit.php?post_type=wpshop_product' ); ?>" class="button button-large button-primary"><?php _e( 'Create products', 'wpshop'); ?></a></p>
		</div>
		<div class="col-3 last-feature">
			<h4><?php _e( 'Manage orders', 'wpshop'); ?></h4>
			<p><?php _e( 'An overview on your orders, keep in touch with your customers.', 'wpshop'); ?></p>
			<p><a href="<?php echo admin_url( 'edit.php?post_type=wpshop_shop_order' ); ?>" class="button button-large button-primary"><?php _e( 'Manage orders', 'wpshop'); ?></a></p>
		</div>
	</div>

	<div class="feature-section col three-col">
		<div class="col-1">
			<h4><?php _e( 'Full content customization', 'wpshop'); ?></h4>
			<p><?php printf( __( 'You can design all you %stransactionnal emails%s, change the different %spage layout%s (as cart page, checkout page, and so on)', 'wpshop'), '<a href="' . admin_url( 'edit.php?post_type=wpshop_shop_message' ) . '" target="_wps_settings_interface" >', '</a>', '<a href="' . admin_url( 'edit.php?post_type=page' ) . '" target="_wps_settings_interface" >', '</a>' ); ?></p>
		</div>
		<div class="col-2">
			<h4><?php _e( 'Payment methods', 'wpshop'); ?></h4>
			<p><?php printf( __( 'When installing WPShop you will have included by default, checks and paypal payment gateway. However there are more %spayment method%s developped for WPShop. If you are interested by a non existant payment method. You can contact us on our forum (link below)', 'wpshop'), '<a href="http://www.wpshop.fr/shop-theme/" target="_wps_about_extra" >', '</a>' ); ?></p>
		</div>
		<div class="col-3 last-feature">
			<h4><?php _e( 'Shipping methods', 'wpshop'); ?></h4>
			<p><?php printf( __( 'By default you have one shipping method available into WPShop. You can create as much as you want using %ssettings interface%s. You will also find on our %swebsite%s additionnals shipping methods.', 'wpshop'), '<a href="' . admin_url( 'options-general.php?page=wpshop_option#wpshop_shipping_option' ) . '" target="_wps_settings_interface" >', '</a>', '<a href="http://www.wpshop.fr/shop-theme/" target="_wps_about_extra" >', '</a>' ); ?></p>
		</div>
	</div>

	<h2 class="about-headline-callout"><?php _e( 'Extend default WPShop functionnalities', 'wpshop'); ?></h2>
	<div class="feature-section col two-col">
		<div class="col-1">
			<h4><?php _e( 'External addons', 'wpshop'); ?></h4>
			<p><?php printf( __( 'Need a theme? or a payment gateway? or a shipping addons? Anythong else? Check %sour website%s in order to find the addons for WPShop that you need. If you don\'t find it please contact us through the forum with the link below.', 'wpshop'), '<a href="http://www.wpshop.fr/shop-theme/" >', '</a>' ); ?></p>
		</div>
		<div class="col-2 last-feature">
			<h4><?php _e( 'Custom hooks', 'wpshop'); ?></h4>
			<p><?php printf( __( 'We inserted some custom hook through WPShop code. That will give you some possibilities to add functionnalities when it is already planned.', 'wpshop') ); ?></p>
		</div>
	</div>

	<h3 class="about-headline-callout"><?php printf( __( 'A question ? A comment ? A need ? Join us on %sWPShop forum%s', 'wpshop'), '<a href="http://forums.eoxia.com/login" taget="_wpshop_forums" >', '</a>' ); ?></h3>

	<hr>
	<div class="return-to-dashboard"><a href="<?php echo admin_url( 'admin.php?page=wpshop_dashboard' ); ?>"><?php _e( 'Go to your shop dashboard', 'wpshop'); ?></a></div>
</div>