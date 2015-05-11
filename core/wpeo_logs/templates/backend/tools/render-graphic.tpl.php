<canvas class="alignleft" id="myChart" width="200" height="200"></canvas>

<script type="text/javascript">
jQuery( document ).ready( function() {
	var data = 
	[
	    {
	        value: <?php echo $critical_error; ?>,
	        color:"#F7464A",
	        highlight: "#FF5A5E",
	        label: "Error"
	    },
	    {
	        value: <?php echo $total_log; ?>,
	        color: "#46BFBD",
	        highlight: "#5AD3D1",
	        label: "Information"
	    },
	    {
	        value: <?php echo $warning_error; ?>,
	        color: "#FDB45C",
	        highlight: "#FFC870",
	        label: "Warning"
	    }
	];
	
	var myPie = new Chart(document.getElementById("myChart").getContext("2d")).Pie(data);  
});
</script>

<!-- Display Legend -->
<div class="wpeo-logs-blog-legend">
	<div class="wpeo-logs-bloc-align">
		<ul class="wpeo-log-legend">
			<li><a class='wpeo-archive-file' data-name="_wpeo-critical.csv" href="#"><span style="background : #F7464A;" class="legend_indicator"></span> <?php _e('Errors', 'wpeo-logs-i18n'); ?></a></li>
			<li><a class='wpeo-archive-file' data-name="_wpeo-warning.csv" href="#"><span style="background : #FDB45C;" class="legend_indicator"></span> <?php _e('Warnings', 'wpeo-logs-i18n'); ?></a></li>
			<li><span style="background : #46BFBD;" class="legend_indicator"></span> Information</li>
		</ul>
	</div>
</div>

<p class="clear"></p>