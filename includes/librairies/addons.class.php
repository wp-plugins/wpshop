<?php
/**
* Products management method file
* 
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_addons
{
	/**
	 * Gérer les actions $_POST
	 */
	function manage_post() 
	{
	}
	
	/**
	 * Affiche la page des groupes
	 */
	function display_page() 
	{
		self::manage_post();
	
		ob_start();
		wpshop_display::displayPageHeader(__('Addons', 'wpshop'), '', __('Addons', 'wpshop'), __('Addons', 'wpshop'), false, '', '');
		$content = ob_get_contents();
		ob_end_clean();
		
		$content .= '<p>'.__('WPShop allow additional module to be activate.','wpshop').'</p>';
		
		$addons_list = unserialize(WPSHOP_ADDONS_LIST);
		foreach ($addons_list as $addon => $name) {
			$activated_status = constant($addon);
			$activated_string = $activated_status ? __('Activated','wpshop') : __('Desctivated','wpshop');
			$content .=  '<strong>'.$name.'</strong>: '.$activated_string;
			if (!$activated_status) {
				$content .=  ' <input type="text" name="'.$addon.'" /> <input type="button" name="'.$addon.'" class="addons_activating_button button-primary" value="'.__('Activate this addon!','wpshop').'" />';
			}
		}
		
		$content .= '</div>';

		echo $content;
	}

}