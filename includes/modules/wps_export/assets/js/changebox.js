// JavaScript Document
jQuery( document ).ready( function() {
	jQuery( '#bestbuyerbutton').hide();
	refresh_interface( jQuery("#whatexportid option:selected").val() );
	jQuery('#whatexportid').change(function() {
    var val = jQuery("#whatexportid option:selected").val();
		refresh_interface( val );
	});
	
	function refresh_interface( val ) {
		if ( val == 'export4' ) {
			jQuery('#bestbuyerbutton').show();
			jQuery('#bestbuyerbutton').focus();
		}
		else if (val != 'export4'){
			jQuery('#bestbuyerbutton').hide();
		}
	}
});