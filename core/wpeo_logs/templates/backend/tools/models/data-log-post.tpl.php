<?php if(!empty($file)): ?>
  <?php foreach($file as $key => $value): ?>
    <?php
    $main_content = unserialize(base64_decode($value[4]));
    $meta_content = unserialize(base64_decode($value[5]));

    //$information_primary = strlen($information_primary)  >= 100 ? substr($information_primary, 0, 100) . '...' : $information_primary;
    //$information_secondary = strlen($information_secondary) >= 100 ? substr($information_secondary, 0, 100) . '...' : $information_secondary;

    ?>
    <tr>
      <td><?php echo get_userdata($value[1])->user_login; ?></td>
      <td><?php echo $value[1]; ?></td>
      <td><?php echo $value[7]; ?></td>
      <td><?php echo $value[3]; ?></td>
      <td>
        <ul class="wpeologs-sub-details" >
  			<?php foreach ( $main_content as $key => $value_content ) : ?>
  				<li><?php _e( $key ); ?> : <?php echo $value_content; ?></li>
  			<?php endforeach; ?>
  			</ul>
      </td>
      <td>
        <ul class="wpeologs-details" >
        <?php foreach ( $meta_content as $key => $meta_content ) : ?>
          <li>
            <?php _e( $key ); ?> :
            <ul class="wpeologs-sub-details">
              <?php foreach ( $meta_content as $value_meta ) : ?>
                <?php $meta_content_details = maybe_unserialize( $value_meta ); ?>
                <?php if ( is_array( $meta_content_details ) ) : ?>
                  <?php foreach ( $meta_content_details as $sub_key => $sub_value ) : ?>
                    <li><?php echo $sub_key; ?> : <?php echo $sub_value; ?></li>
                  <?php endforeach; ?>
                <?php elseif ( is_object( $meta_content_details ) ) : ?>
                  <?php foreach ( $meta_content_details as $sub_key => $sub_value ) : ?>
                    <li><?php echo $sub_key; ?> : <?php echo $sub_value; ?></li>
                  <?php endforeach; ?>
                <?php else : ?>
                  <li><?php echo $value_meta; ?></li>
                <?php endif; ?>

              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
      </td>
      <td><?php echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $value[0], true ); ?></td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td><?php _e('Nothing to display, select your log of file', 'wpeologs-i18n'); ?></td>
  </tr>
<?php endif; ?>
