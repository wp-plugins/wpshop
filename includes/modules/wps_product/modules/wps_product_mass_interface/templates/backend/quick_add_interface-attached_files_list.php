<?php if( !empty($files) ) : ?>
<u><strong><?php _e( 'Uploaded files', 'wpshop' ); ?> :</strong></u>
<ul>
<?php foreach( $files as $file ) : ?>
<li><a href="<?php echo wp_get_attachment_url(  $file->ID ); ?>" target="_blank"><?php echo $file->post_title; ?></a> <a href="#" class="wps-mass-delete-file" id="wps-mass-delete-file-<?php echo $file->ID; ?>"><span class="wps-icon-trash"></span></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>