<?php
$tpl_element = array();

/**
 * BOX STATS
 */
ob_start();
?>
<div id="pageTitleContainer" class="pageTitle">
	<h2><?php _e( 'WPShop Statistics', 'wpshop' ); ?></h2>
</div>
<div class="postbox-container" style="width:49%;">
	{WPSHOP_LEFT_BOXES}
</div>
<div class="postbox-container" style="width:49%; float:right;">
	{WPSHOP_RIGHT_BOXES}
</div>
<?php
$tpl_element['admin']['default']['wps_statistics_interface'] = ob_get_contents();
ob_end_clean();

ob_start();
?>
<div class="postbox">
	<h3 class="hndle"><span>{WPSHOP_STATISTICS_TITLE}</span></h3>
	<div class="inside" id="inside_{WPSHOP_STATISTICS_CANVAS_ID}">
		<center><canvas id="{WPSHOP_STATISTICS_CANVAS_ID}" width="{WPSHOP_CANVAS_WIDTH}" height="{WPSHOP_CANVAS_HEIGHT}"></canvas></center>
		{WPSHOP_STATISTICS_JS}
	</div>
</div>
<?php
$tpl_element['admin']['default']['wps_postbox'] = ob_get_contents();
ob_end_clean();