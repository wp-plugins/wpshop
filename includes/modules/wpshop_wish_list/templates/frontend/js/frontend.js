
jQuery(document).ready(function(){

    jQuery(document).on('click', "#wishlist_button_add", function(){
        // Change icone to loading
        jQuery('#wishlist_button_icone').attr('src', WISH_LIST_PICTURES_URL_AJAX + "ajax-loader.gif");
        jQuery('#wishlist_button_icone').attr('alt', WISH_LIST_LOADING_AJAX);

        var data = {
			action: "wps_wishlist_add_product",
			product_id: jQuery("#product_ID").val()
		};

		jQuery.post( ajaxurl, data, function( response ){
            if( response['status'] == true ) {
                // Change button
                jQuery("#wish_list_button_container").html( response[ 'button' ] );
                // Change icone to confirm
               jQuery('#wishlist_button_icone').attr('src', WISH_LIST_PICTURES_URL_AJAX + "ajax-valid.gif");
               jQuery('#wishlist_button_icone').attr('alt', 'OK');
            }
		}, 'json');
    });

    jQuery(document).on('click', "#wishlist_button_remove", function(){
        // Change icone to loading
        jQuery('#wishlist_button_icone').attr('src', WISH_LIST_PICTURES_URL_AJAX + "ajax-loader.gif");
        jQuery('#wishlist_button_icone').attr('alt', WISH_LIST_LOADING_AJAX);

        var data = {
			action: "wps_wishlist_remove_product",
			product_id: jQuery("#product_ID").val()
		};

		jQuery.post( ajaxurl, data, function( response ){
	        if ( response['status'] == true ) {
	            // Change button
	            jQuery("#wish_list_button_container").html("<button id='wishlist_button_add'> <img id='wishlist_button_icone' src='#' alt='' /> " + WISHLIST_BUTTON_ADD + "</button>");
			}
		}, 'json');
    });
    //**********************************************************************


    //******************  page -> My Wish List  **********************
    var preSend_firstTime = true; // Check if first time is open pop up to no reload it everytime
    jQuery(document).on('click', "#wishlist_button_pre_send", function(){
    	if (preSend_firstTime) {
            preSend_firstTime = false;

            // Change icone to loading
            jQuery('#wishlist_button_icone').attr('src', WISH_LIST_PICTURES_URL_AJAX + "ajax-loader.gif");
            jQuery('#wishlist_button_icone').attr('alt', WISH_LIST_LOADING_AJAX);

            var data = {
            	action: "wps_wishlist_content_popup"
            };

            jQuery.post( ajaxurl, data, function( response ){
                 // Change icone to none
                jQuery('#wishlist_button_icone').attr('src', '#');
                jQuery('#wishlist_button_icone').attr('alt', '');

                // Show pop up to add receivers and send wish list
                jQuery('.wps-modal-wrapper').addClass('wps-modal-opened');
                jQuery('body').addClass('wps-body-inactiv');

                jQuery('.wps-modal-h3').html(response['title']); // Display title
                jQuery('.wps-modal-body').html(response['content']); // Display content
                jQuery('#wish_list_receivers').chosen({width: "95%"}); // Make select plugin chosen
            }, 'json');
       }
       else {
           // Show pop up to add receivers and send wish list
           jQuery('.wps-modal-wrapper').addClass('wps-modal-opened');
           jQuery('body').addClass('wps-body-inactiv');
       }
    });

    //************** Buttons from FORMULAIRE from pop up **************
    jQuery(document).on('click', "#wishlist_button_send", function(){ // SEND button from pop up

        var list_receivers = [];
        jQuery('#wish_list_receivers option').each( function(){
            list_receivers.push(jQuery(this).text()); // Get all email from SELECT of receivers
        } );
        if ( list_receivers[0] != null ) { // If contain email

            jQuery('#wishlist_display_error').html(''); // no error

            //******* AJAX_LOADER *******
            jQuery('#receivers_form_status').attr('src', WISH_LIST_PICTURES_URL_AJAX + "ajax-loader.gif");
            jQuery('#receivers_form_status').attr('alt', WISH_LIST_LOADING_AJAX);

            var data = {
                action: "wps_wishlist_send",
                receivers: list_receivers
            };
            jQuery.post( ajaxurl, data, function( response ){
                 // ********** END AJAX_LOADER ************
                 jQuery('#receivers_form_status').attr('src', '');
                 jQuery('#receivers_form_status').attr('alt', '');

                    if(response['status'])
                    {
                        // Change value button
                        jQuery('#wishlist_button_pre_send').html("<img id='wishlist_button_icone' src='" + WISH_LIST_PICTURES_URL_AJAX + "ajax-valid.gif' alt='' />" + response['message'] + "<br />" + "<i id='wishlist_button_italic'>" + WISH_LIST_SEND_BACK + "</i>"); // Display message response

                        // Clear list of receivers
                        jQuery('#wish_list_receivers option').each(function(){
                            jQuery(this).remove();
                        });
                        jQuery('#wish_list_receivers').trigger("liszt:updated"); // Update chosen

                        preSend_firstTime = true; // To reload pop up
                          // Close pop up
                        jQuery('.wps-modal-wrapper').removeClass('wps-modal-opened');
                        jQuery('body').removeClass('wps-body-inactiv');

                    }
            }, 'json');
        }
        else { // SELECT Does not contain email
            jQuery('#wishlist_display_error').html(WISHLIST_ADD_RECEIVER_ERROR);
        }
    });

    jQuery(document).on('click', "#wishlist_button_cancel", function(){ // CANCEL button to close pop up
        // Close pop up
        jQuery('.wps-modal-wrapper').removeClass('wps-modal-opened');
        jQuery('body').removeClass('wps-body-inactiv');
    });

    jQuery(document).on('click', "#wishlist_button_add_receivers", function(){ // Add button from pop up
        var valide = true; // Email valide
        var email = jQuery("#wishlist_email_receivers").val();

        jQuery('#wish_list_receivers option').each(function(){
                if( email == jQuery(this).text()) valide = false; // Email Already exist
        });

        // Check validate email
        if(!email.match(/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/) || !valide)
        {
            jQuery('#wishlist_display_error').html(WISHLIST_ADD_RECEIVER_EMAIL_ERROR); // Display error
        }

        else // Email valide, ok to add
        {
            jQuery('#wishlist_display_error').html(''); // No error

            // Valid email -> Add email receiver to list of receivers
            jQuery("#wish_list_receivers").append("<option selected='selected'>" + email + "</option>");
            jQuery('#wish_list_receivers').trigger("liszt:updated"); // Update chosen
            jQuery("#wishlist_email_receivers").val('');
        }
        return false; // No submit
    });
    //**********************************************************************

}); // end jQuery(document).ready(..)