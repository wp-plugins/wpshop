jQuery(document).ready(function() {
	/** 
	 * Tools 
	 */
	var current_parent_name = "";
	
	/** Menu */
	jQuery('.wpeo-logs-wrap .wpeo-logs-menu .wpeo-logs-parent').click(function() {
		var element = jQuery(this);
		
		if(!element.closest('li').hasClass('wpeo-logs-li-active')) {
			jQuery('.wpeo-logs-parent').removeClass('wpeo-logs-li-active');
			element.closest('li').toggleClass('wpeo-logs-li-active');
			
			current_parent_name = element.html();
			
			/** Load child with ajax */		
			var data = {
				"action": "wpeo-get-bulleted-list",
				"current_file_name": current_parent_name,
			};
			
			jQuery(".wpeo-logs-archive-file").find('ul').load(ajaxurl, data, function() {});
			
			data = {
				"action": "wpeo-render-csv",
				"current_parent_name": element.html(),
				"current_file_name": element.html(),
				"current_index": 0,
			}
			
			jQuery(".wpeo-logs-table").load(ajaxurl, data, function() {});
		}
	});
	
	/** Archives */
	jQuery(document).on("click", '.wpeo-logs-wrap .wpeo-archive-file', function() {
		var element = jQuery(this);
		
		if(!element.hasClass('wpeo-logs-archive-file-active')) {
			jQuery('.wpeo-archive-file').removeClass('wpeo-logs-archive-file-active');
			
			
			if(element.attr('data-name'))
				var current_file_name = element.attr('data-name');
			else {
				element.toggleClass('wpeo-logs-archive-file-active');
				var current_file_name = element.html();
			}
			
			/** Load archive ajax */
			var data = {
				"action": "wpeo-render-csv",
				"current_parent_name": current_parent_name,
				"current_file_name": current_file_name,
				"current_index": 0,
				"get_archive": true,
			};
			
			jQuery(".wpeo-logs-table").load(ajaxurl, data, function() {});
		}
	});
	
	/**
	 *  Settings 
	 */
	
	/** Add service button */
	jQuery(".wpeo-logs-add-service-button").click(function() {
		jQuery(".wpeo-logs-bloc-add").fadeIn();
		
		var speed = 750; // Durée de l'animation (en ms)
		jQuery('html, body').animate( { scrollTop: jQuery(".wpeo-logs-bloc-add").offset().top }, speed ); // Go
		return false;
	});
	
	
	
	/** Add service */
	jQuery('.wpeo-service-add').click(function() {
		var closest = jQuery(this).closest('div');

		// Get form value
		var service_active = closest.find('.wpeo-service-active').is(':checked');
		var service_rotate = closest.find('.wpeo-service-rotate').is(':checked');
		var service_name = closest.find('.wpeo-service-name').val();
		var service_size = closest.find('.wpeo-service-size').val();
		var service_size_format = closest.find('.wpeo-service-size-format option:selected').val();
		var service_file = closest.find('.wpeo-service-file').val();

	    var data = {
	      'action': 'wpeo-update-service',
	      'service_active': service_active,
	      'service_name': service_name,
	      'service_size': service_size,
	      'service_size_format': service_size_format,
	      'service_file': service_file,
	      'service_rotate': service_rotate,
	     
	    };
	    
	    if(service_name != "" && service_size != "") {
		    // Ajax post
		    jQuery.post(ajaxurl, data, function(response) {
		    	// Render new li servicewpeo-logs-bloc-add
		    	closest.find('.wpeo-service-rotate').prop('checked', false);
				closest.find('.wpeo-service-name').val('');
				closest.find('.wpeo-service-size').val('');
				closest.find('.wpeo-service-size-format option[value="octet"]').prop('selected', true);
		  	 	closest.find('.wpeo-service-file').val('');
		  	 	/** Hide and show the new service */
		  	 	jQuery('.wpeo-logs-bloc-add').hide();
		  	 	jQuery('.wpeo-logs-notice-add-new').hide();
		    	jQuery('.wpeo-logs-service').append(response.render);
		    	
		    });
	    }
	    else {
	    	/** Shake effect */
	    	if(service_name == "") {
	    		closest.find('.wpeo-service-name').shake(2, 13, 250);
	    	}
	    	if(service_size == "") {
	    		closest.find('.wpeo-service-size').shake(2, 13, 250);
	    	}
	    }
	  });
  
	  /** Update service */
	  jQuery(document).on('blur', '.wpeo-logs-service input', function() {
		  // Get slug
		  var input_blur = jQuery(this).closest('div');
		  
		  // Form value
		  var slug = input_blur.find('.wpeo-service-slug').val();
		  var file_format = input_blur.find('.wpeo-service-size-format option:selected').val();
		  
		  if(slug != '') {
			  input_blur.find('.wpeo-logs-up-to-date').hide();
			  input_blur.find('.wpeo-logs-saving').show();
			  
			  if('checkbox' == jQuery(this).attr('type'))
				  update_service_data(input_blur, slug, file_format, jQuery(this).attr('data-name'), jQuery(this).is(':checked'));
			  else
				  update_service_data(input_blur, slug, file_format, jQuery(this).attr('data-name'), jQuery(this).val());
		  }
	  });
	  
	  /** Hide/show file service */
	  jQuery(document).on('click', '.wpeo-service-rotate', function() {
		 var element = jQuery(this);
		 element.parent().parent().find('.wpeo-service-file-bloc').fadeToggle();
	  });
	  
});

function update_service_data(div, slug, file_format, data_name, value) {
	var data = {
		'action': 'wpeo-update-service',
		'service_slug': slug,
		'service_size_format': file_format,
	};
	
	data[data_name] = value;
	
	jQuery.post(ajaxurl, data, function() {
		// hide saving, and show up to date
		div.find('.wpeo-logs-saving').hide();
		div.find('.wpeo-logs-up-to-date').show();
		
	});
}

jQuery.fn.shake = function(intShakes, intDistance, intDuration) {
	this.each(function() {
		jQuery(this).css({
			position: "relative"
	});
	for (var x = 1; x <= intShakes; x++) {
		jQuery(this).animate({
			left: (intDistance * -1)
		}, (((intDuration / intShakes) / 4))).animate({
			left: intDistance
		}, ((intDuration / intShakes) / 2)).animate({
			left: 0
		}, (((intDuration / intShakes) / 4)));
		}
	});
	return this;
};
	 