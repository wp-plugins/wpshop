<div class="wps_dashboard_video_container">
	<div class="wps_dashboard_video_title"><?php echo $videos_items[ $rand_element ]->title; ?></div>
	<div class="wps_dashboard_video"><iframe width="400" height="290" src="<?php echo $videos_items[ $rand_element ]->embed_link; ?>" frameborder="0" allowfullscreen></iframe></div>
	<div class="wps_dashboard_video_description"><?php echo $videos_items[ $rand_element ]->description; ?></div>
</div>