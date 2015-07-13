<div class="wpeo-bloc-service">
	<?php $service_slug = !empty($service_slug) ? $service_slug : ''; ?>
	<!-- Invisible slug input -->
	<input data-name='service_slug' type='hidden' class='wpeo-service-slug' value='<?php echo $service_slug; ?>' />
	
	<!-- Service active -->
	<label for="wpeo-service-active-<?php echo $service_slug; ?>">
		<span class="wpeo-label"><?php _e("Service active", "wpeologs-i18n"); ?></span>
		<input id="wpeo-service-active-<?php echo $service_slug; ?>" data-name='service_active' class='wpeo-service-active' <?php echo ( !empty ( $array['service_active'] ) && ((int) $array['service_active'] || empty( $service_slug ) ) ) ? 'checked' : ''; ?> type='checkbox' />
	</label>
	
	
	<!-- Service name -->
	<label for="wpeo-service-name-<?php echo $service_slug; ?>">
		<span class="wpeo-label"><?php _e("Service name", "wpelogs-i18n"); ?></span>
  		<input id="wpeo-service-name-<?php echo $service_slug; ?>" data-name='service_name' placeholder="<?php _e('Service name', 'wpeologs-i18n'); ?>" type='text' class='wpeo-service-name' value='<?php echo !empty($array['service_name']) ? $array['service_name'] : ''; ?>' />
  	</label>
  	<!-- Service size -->
  	
  	<label for="wpeo-service-size-<?php echo $service_slug; ?>">
  		<span class="wpeo-label"><?php _e("Service size", "wpeologs-i18n"); ?></span>
  		<input id="wpeo-service-size-<?php echo $service_slug; ?>" data-name='service_size' placeholder="<?php _e('Service size', 'wpelogs-i18n'); ?>" type='number' class='wpeo-service-size' value='<?php echo !empty($array['service_size']) ? $this->convert_to($array['service_size'], $array['service_size_format'], false) : ''; ?>' />
	</label>
	
	<!-- Service format (octets, mo, ko, go) -->
	<label for="wpeo-service-size-format-<?php echo $service_slug; ?>">
		<span class="wpeo-label"><?php _e("Service size format", "wpeologs-i18n"); ?></span>
		<select id="wpeo-service-size-format-<?php echo $service_slug; ?>" data-name='service_size_format' class='wpeo-service-size-format'>
	    	<?php if(!empty($array_size_format)): ?>
	    		<?php foreach($array_size_format as $key => $value): ?>
	       		<option <?php echo selected(!empty($array['service_size_format']) ? $array['service_size_format'] : 'oc', $key); ?> value='<?php echo $key; ?>'><?php echo $value; ?></option>
	      	<?php endforeach; ?>
	    <?php endif; ?>
		</select>
	</label>
	
	<!-- Service rotate -->
	<label for="wpeo-service-rotate-<?php echo $service_slug; ?>">
		<span class="wpeo-label"><?php _e('Active rotate', 'wpeologs-i18n'); ?></span>
		<input data-name='service_rotate' id="wpeo-service-rotate-<?php echo $service_slug; ?>" class='wpeo-service-rotate' <?php echo ( !empty ( $array['service_rotate'] ) && (int)$array['service_rotate'] ) ? 'checked' : ''; ?> type='checkbox' />
	</label>
	
	<!-- Service rotate number file -->
	<label class="wpeo-service-file-bloc" style="<?php echo ( !empty( $array['service_rotate'] ) && (int)$array['service_rotate'] ) ? '' : 'display: none;'; ?>" for="wpeo-service-file-<?php echo $service_slug; ?>">
		<span class="wpeo-label"><?php _e("Service rotate number file", "wpeologs-i18n"); ?></span>
		<input id="wpeo-service-file-<?php echo $service_slug; ?>" data-name='service_file' placeholder="<?php _e("Service file", "wpelogs-i18n"); ?>" type='number' class='wpeo-service-file' value='<?php echo !empty($array['service_file']) ? $array['service_file'] : ''; ?>' />
	</label>
	
	<!-- If create service, add + button -->
	<?php if(empty($service_slug)): ?>
		<a class='wpeo-service-add alignright' href="#"><span class="dashicons dashicons-plus-alt"></span></a>
		<p class="clear"></p>
	<?php endif; ?>
	
	<!-- For say save in progress or up to date -->
	<?php if(!empty($service_slug)): ?>
		<p class="wpeo-logs-state-service">
			<span class="wpeo-logs-italic wpeo-logs-up-to-date alignright"><?php _e("Up to date", "wpeologs-i18n"); ?></span>
			<span style="display: none;" class="wpeo-logs-italic alignright wpeo-logs-saving"><?php _e("Saving...", "wpeologs-i18n"); ?></span>
		</p>
	<?php endif; ?>
</div>