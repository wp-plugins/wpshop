<div class="wrap wpeo-logs-wrap">
  <h2><?php _e('Logs', 'wpeologs-i18n'); ?></h2>

  <!-- Form ajax for add service -->
  <h3><?php _e('My services', 'wpeologs-i18n'); ?> <a class="add-new-h2 wpeo-logs-add-service-button" href="#">Add New</a></h3>

  <div class='wpeo-logs-service'>
  	<?php if(!empty($current_option['my_services'])): ?>
  		<?php foreach($current_option['my_services'] as $service_slug => $array): ?>
  			<?php $this->display_model_service($service_slug, $array); ?>
  		<?php endforeach; ?>
  		<?php unset($service_slug); ?>
  		<?php unset($array); ?>
  	<?php else: ?>
  		<p class="wpeo-logs-notice-add-new"><?php _e("Click", "wpeologs-i18n"); ?> <a class="add-new-h2 wpeo-logs-add-service-button" href="#">Add New</a> <?php _e("button for add new service", "wpeologs-i18n"); ?></p>
  	<?php endif;?>
  	
  </div>
  	
  	<p class="clear margin-bottom-20"></p>
  	
  	<div class="wpeo-logs-bloc-add">
	  	<h3><?php _e('Add a service', 'wpeologs-i18n'); ?></h3>
   		<?php require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/settings/models/service.tpl.php"); ?>
    </div>
</div>
