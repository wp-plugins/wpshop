<?php if(empty($_POST['load_more'])):?>

  <!-- The table for display data -->
  <h3 class="wpeo-log-data-title"><?php _e('Your log data', 'wpeologs-i18n'); ?></h3>

  <table id='wpeo-table-data-csv' class="tablesorter wp-list-table widefat fixed posts">
    <thead>
      <tr>
        <th id="author" class="header-order manage-column column-author" style="" scope="col"><?php _e('Author', 'wpeologs-i18n'); ?></th>
        <th id="severity" class="header-order manage-column column-severity" style="" scope="col"><?php _e('Severity', 'wpeologs-i18n'); ?></th>
        <th id="object-id" class="header-order manage-column column-object-id" style="" scope="col"><?php _e('Object ID', 'wpeologs-i18n'); ?></th>
        <th style='width: 50%;' id="message" class="header-order manage-column column-message" style="" scope="col"><?php _e('Message', 'wpeologs-i18n'); ?></th>
        <th id="date" class="header-order manage-column column-date sortable <?php echo (!empty($order)) ? $order : ""; ?>" style="" scope="col">
            <span>Date</span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/data-log.tpl.php"); ?>
    </tbody>
  </table>
<?php else: ?>
  <?php require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/models/data-log.tpl.php"); ?>
<?php endif; ?>
