<?php

/**
 * Filter Search Interface
 */
ob_start();
?>
<div id="wpshop_filter_search_container">
	<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" name=" " id="filter_search_action">
	<input type="hidden" name="action" value="filter_search_action" />
	{WPSHOP_FILTER_SEARCH_ELEMENT}
	</form>
</div>
<?php 
$tpl_element['wpshop']['default']['wpshop_filter_search_interface'] = ob_get_contents();
ob_end_clean();

/**
 * Filter Search Interface
 */
ob_start();
?>

<p class="formField" style="margin-top : 10px">
	<label>{WPSHOP_FILTER_SEARCH_ATTRIBUTE_TITLE}</label>
	<div id="slider_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" class="slider_filter_search filter_search_element"></div>
	<div id="amount_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" class="amount"><div class="amount_min" id="amount_min_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}"></div><div class="amount_max" id="amount_max_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}"></div></div>
</p>

<input type="hidden" id="amount_min_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" name="amount_min_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" value="{WPSHOP_FILTER_SEARCH_MIN_DATA}" />
<input type="hidden" id="amount_max_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" name="amount_max_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" value="{WPSHOP_FILTER_SEARCH_MAX_DATA}" />
<div id="slider_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" style="width:100%;" class="slider_variable wpshop_options_slider wpshop_options_slider_shipping wpshop_options_slider_shipping_rules"></div>
<script type="text/javascript">
	
	jQuery(document).ready(function(){
		jQuery("#slider_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").slider({
			range: true,
			min: {WPSHOP_FILTER_SEARCH_MIN_DATA},
			max: {WPSHOP_FILTER_SEARCH_MAX_DATA},
			values: [ {WPSHOP_FILTER_SEARCH_MIN_DATA}, {WPSHOP_FILTER_SEARCH_MAX_DATA} ],
			slide: function( event, ui ) {

				jQuery("#amount_min_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").val(ui.values[0]);
				jQuery("#amount_min_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").html( ui.values[0] + " {WPSHOP_DEFAULT_UNITY_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}}");
				jQuery("#amount_max_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").val(ui.values[1]);
				jQuery("#amount_max_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").html( ui.values[1] + " {WPSHOP_DEFAULT_UNITY_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}}");
			}
			
		});
		jQuery("#amount_min_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").html(jQuery("#amount_min_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").val() + " {WPSHOP_DEFAULT_UNITY_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}}" );
		jQuery("#amount_max_indicator_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").html(jQuery("#amount_max_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}").val() + " {WPSHOP_DEFAULT_UNITY_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}}" );
	});
</script>

<?php 
$tpl_element['wpshop']['default']['wpshop_filter_search_element_for_integer_data'] = ob_get_contents();
ob_end_clean();


ob_start();
?>
<p class="formField" style="margin-top : 20px">
	<label for="filter_search_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}">{WPSHOP_FILTER_SEARCH_ATTRIBUTE_TITLE}</label>
	<select id="filter_search_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" name="filter_search_{WPSHOP_FILTER_SEARCH_FILTER_LIST_NAME}" class="filter_search_element" >
		{WPSHOP_FILTER_SEARCH_LIST_VALUE}
	</select>
</p>

<?php 
$tpl_element['wpshop']['default']['wpshop_filter_search_element_for_text_data'] = ob_get_contents();
ob_end_clean();


?>
