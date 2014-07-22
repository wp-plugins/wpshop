<h3><?php _e( 'My wish list', 'wp_wish_list' ); ?></h3>

<?php if ( !empty( $product_in_wishlist ) ) : ?>

<ul class="wps-wishlist" >
<?php foreach ( $product_in_wishlist as $product ) : ?>
	<li class="wps-wishlist-item" >
		<a href="<?php echo $product->product_Link; ?>" title="<?php echo $product->product_Title; ?>"><?php echo $product->product_Thumbnail; ?></a>
		<span itemprop="offers" itemscope itemtype="http://data-vocabulary.org/Offers">
			<a href="<?php echo $product->product_Link; ?>" title="<?php echo $product->product_Title; ?>" >
				<h4 itemprop="name" ><?php echo $product->product_Title; ?></h4>
				<p itemprop="description" ><?php echo $product->product_Description; ?></p>
			</a>
		</span>
	</li>
<?php endforeach; ?>
</ul>

<?php require( $this->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "wps_modal" ) ); ?>

<?php else : ?>
	<?php _e( 'You don\'t have any product into your wishlist for the moment', 'wp_wish_list' ); ?>
<?php endif; ?>