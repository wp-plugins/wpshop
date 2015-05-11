<?php if(!empty($array_glob_file)): ?>
	<?php foreach($array_glob_file as &$glob_file): ?>
		<li class='wpeo-archive-file'><?php echo $glob_file; ?></li>
	<?php endforeach; ?>
<?php else: ?>
	<li><?php _e("No archived file", "wpeo-logs-i18n"); ?></li>
<?php endif; ?>