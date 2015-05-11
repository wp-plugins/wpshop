<div class='wrap wpeo-logs-wrap'>
	<h2><?php _e('Logs', 'wpeologs-i18n'); ?></h2>

	<!--  Menu choose file -->
	<div class="alignleft">
		<h3><?php _e('Files', 'wpeo-logs-i18n'); ?></h3>
		
		<?php if(!empty($array_file)): ?>
			<ul class="wpeo-logs-menu">
			<?php foreach($array_file as $file):?>
				<li class="wpeo-logs-parent"><?php echo $file; ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>	
	</div>
	
	<div class="alignright wpeo-right-page">
		<!-- In short -->
		<div class="alignleft wpeo-logs-margin-right wpeo-logs-width">
			<h3><?php _e('In short', "wpeo-logs-i18n"); ?></h3>
			<div class="wpeo-logs-bloc wpeo-logs-in-short">	
				<div class='aligncenter'>				
					<?php require( WPEO_LOGS_TEMPLATES_MAIN_DIR . "backend/tools/render-graphic.tpl.php"); ?>
				</div>
			</div>
		</div>
		
		<!-- Number file -->
		<div class="alignleft wpeo-logs-width">
			<h3><?php _e('Archive file', "wpeo-logs-i18n"); ?></h3>
			<div class="wpeo-logs-bloc wpeo-logs-archive-file">
				<ul class='wpeo-logs-container'>
					<li><?php _e('Select your file in the left menu', "wpeo-logs-i18n"); ?></li>
				</ul>
			</div>	
		</div>
		
		<p class='clear'></p>
		
		<div class="wpeo-logs-table">
		</div>
	</div>
</div>