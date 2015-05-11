<?php if(!empty($file)): ?>
  <?php foreach($file as $key => $value): ?>
    <tr>
      <td><?php echo !(empty($value[1])) ? get_userdata($value[1])->user_login : __('Empty', 'wpeologs-i18n'); ?></td>
      <td><?php echo $value[5]; ?></td>
      <td><?php echo $value[3]; ?></td>
      <td><?php echo !(empty($value[4])) ? trim($value[4], "\"") : __('Empty', 'wpeologs-i18n'); ?></td>
      <td><?php echo !(empty($value[0])) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $value[0], true ) : __('Empty', 'wpeologs-i18n'); ?></td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td><?php _e('Nothing to display, select your log of file', 'wpeologs-i18n'); ?></td>
  </tr>
<?php endif; ?>
