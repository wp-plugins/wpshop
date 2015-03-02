<?php 	global $wpdb; ?>
<?php	$opinion_option = get_option( 'wps_opinion' ); ?>
<?php 	$opinion_state = !empty($opinion_option) && !empty($opinion_option['active']) ? true : false ; ?>
<?php
	// Formate datas
	$data = array();
	foreach( $product_atts_def as $pad ) :
		foreach( $pad as $attributes_group_name => $attribute_group ) :
			if( $attribute_group['display_on_frontend'] == 'yes' ) :
				$data[ $attribute_group['code'] ]['title'] = __( $attributes_group_name, 'wpshop' );
				$display = false;
				if( !empty($attribute_group['attributes']) ) :
					foreach( $attribute_group['attributes'] as $attribute ) :
						if( $attribute['is_visible_in_front'] == 'yes' && !empty($attribute['value']) ) :
							$display = true;
							$data[ $attribute_group['code'] ]['attributes'][] = $attribute;
						endif;
					endforeach;
				endif;
				$data[ $attribute_group['code'] ]['display'] = $display;
			endif;
		endforeach;
	endforeach;
?>

<?php if ( !empty( $data ) || $opinion_state ) : ?>
<div class="wps-ui-tab">
	<!-- Menu -->
	<ul>
		<?php
		$i = 0;
		foreach( $data as $attribute_group_code => $attribute_group ) : ?>
			<?php if( $attribute_group['display'] ) : ?>
				<li class="<?php echo ( ($i == 0 ) ? 'wps-activ' : '' ) ;?>"><a data-toogle="wps-tab-<?php echo $attribute_group_code; ?>" href="#"><?php echo $attribute_group['title']; ?></a></li>
			<?php endif; ?>
			<?php $i++; ?>
		<?php endforeach; ?>
		<?php if( $opinion_state ) : ?>
		<li class="<?php echo ( ($i == 0 ) ? 'wps-activ' : '' ) ;?>"><a data-toogle="wps-tab-opinions" href="#"><?php _e( 'Opinions', 'wpshop'); ?></a></li>
		<?php endif;?>
	</ul>


	<!-- Content -->
	<div>
		<?php
		$i = 0;
		foreach( $data as $attribute_group_code => $attribute_group ) : ?>
			<?php if( $attribute_group['display'] ) : ?>
				<div class="wps-tab-<?php echo $attribute_group_code; ?>" style="<?php echo ( ($i == 0 ) ? 'display : block;' : 'display : none;' ) ;?>" >
					<ul>
						<?php foreach( $attribute_group['attributes'] as $attribute ) : ?>
							<?php if( $attribute['is_visible_in_front'] == 'yes' ) : ?>

								<?php if( !empty($attribute['value']) ) : ?>
									<?php
									$value = $attribute['value'];
									if( $attribute['data_type'] == 'integer' ) :
										if( $attribute['data_type_to_use'] == 'custom' ) :
									?>
											<?php
											$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE id = %d', $attribute['value']);
											$value = $wpdb->get_var( $query );
											?>
										<?php else : ?>
											<?php $value = get_the_title( $attribute['value'] ); ?>
										<?php endif; ?>

									<?php endif; ?>
									<?php if( $attribute['data_type'] == 'decimal' ) :  ?>
										<?php $value = wpshop_tools::formate_number( $value ); ?>
									<?php endif; ?>
									<li><?php _e( $attribute['frontend_label'], 'wpshop' ); ?> : <?php echo $value; ?> <?php echo ( ( $attribute['is_requiring_unit'] == 'yes') ? $attribute['unit'] : '' ); ?></li>
								<?php endif; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php $i++; ?>
		<?php endforeach; ?>
		<?php if( $opinion_state ) : ?>
		<div class="wps-tab-opinions" style="<?php echo ( ($i == 0 ) ? 'display : block;' : 'display : none;' ) ;?>">
			<?php echo do_shortcode( '[wps_opinion_product pid="' .$args['pid']. '"]' ); ?>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>