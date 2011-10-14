/*	Define the jQuery noConflict var for the plugin	*/
var wpshop = jQuery.noConflict();

/*	Check all event on page load	*/
wpshop(document).ready(function(){
	jQuery("#wpshopFormManagementContainer").tabs();
	/*	Define the tools for the widget containing the different categories and products	*/
	wpshop(".wpshop_open_category").click(function(){
		widget_menu_animation(wpshop(this));
	});

	wpshop("a[rel=appendix]").fancybox({
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	wpshop("a#product_thumbnail").fancybox({
		'titleShow'     : false
	});
});

/**
*	Define the function allowing to open or close the widget menu
*/
function widget_menu_animation(current_element){
	current_category = current_element.attr("id").replace("wpshop_open_category_", "");
	if(current_element.hasClass("wpshop_category_closed")){
		current_element.removeClass("wpshop_category_closed");
		current_element.addClass("wpshop_category_opened");
		wpshop(".wpshop_category_sub_content_" + current_category).slideDown();
	}
	else{
		current_element.removeClass("wpshop_category_opened");
		current_element.addClass("wpshop_category_closed");
		wpshop(".wpshop_category_sub_content_" + current_category).slideUp();
	}
}