<?php
/**
* Plugin dashboard methods definer
* 
*	Define the different method and variable used to create the plugin dashbord
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package wpshop
* @subpackage librairies
*/


/**
 * Define the different method and variable used to create the plugin dashbord
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_dashboard
{

	/**
	*	Main function called to create the dashboard output
	*
	*	@return mixed The dashboard element content
	*/
	function wpshop_dashboard_load()
	{
		/*	Page content header	*/
		wpshop_display::displayPageHeader(__('Tableau de bord', 'wpshop'), '', __('Tableau de bord', 'wpshop'), __('Tableau de bord', 'wpshop'), false);

		/*	Page content	*/
		echo 'this is dashboard';

		/*	Page content footer	*/
		wpshop_display::displayPageFooter();
	}

}