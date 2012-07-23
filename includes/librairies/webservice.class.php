<?php
class wpshop_webservice {

	/**
	 * Constructor
	 */
	function __construct() {
	
		if(is_user_logged_in()) {
			
			if(!empty($_GET['action']) && $_GET['action']=='webservice' && !empty($_GET['event'])) {
			
				/*switch($_GET['event']) {
				
					case 'setAttributesValuesForItem':
					
						if(!empty($_GET['productID']) && !empty($_GET['values'])) {
							$return = wpshop_attributes::setAttributesValuesForItem($_GET['productID'], unserialize($_GET['values']));
							//echo '<pre>'; print_r($return); echo '</pre>';
						}
						
					break;
					
				}*/
				
			}

		}
		
	}
}
?>