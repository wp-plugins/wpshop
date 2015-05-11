<?php if(empty($_POST['load_more'])):?>

  <!-- The table for display data -->
  <h3 class="wpeo-log-data-title">
    <?php _e('Your log data', 'wpeologs-i18n'); ?>
  </h3>

  <table id='wpeo-table-data-csv' class="tablesorter wp-list-table widefat fixed posts">
    <thead>
      <tr>
        <th class="wpeologs-user-column" ><?php _e( 'Author', 'wpeologs-i18n' ); ?></th>
        <th class="wpeologs-criticality-column" ><?php _e('Severity', 'wpeologs-i18n' ); ?></th>
        <th class="wpeologs-service-column" ><?php _e('Service', 'wpeologs-i18n' ); ?></th>
        <th class="wpeologs-object-id-column" ><?php _e( 'Object ID', 'wpeologs-i18n' ); ?></th>
        <th style='width: 40%;' class="wpeologs-content-column" ><?php _e( 'Main informations', 'wpeologs-i18n' ); ?></th>
        <th style='width: 20%;' class="wpeologs-meta-column" ><?php _e( 'Metas informations', 'wpeologs-i18n' ); ?></th>
        <th class="wpeologs-date-column" ><?php _e( 'Date', 'wpeologs-i18n' ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php require(WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/data-log-post.tpl.php"); ?>
    </tbody>
  </table>
<?php else: ?>
  <?php require(WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/data-log-post.tpl.php"); ?>
<?php endif; ?>
