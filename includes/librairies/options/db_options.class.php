<?php
/**
 * Plugin database options
 * 
 * Allows to manage the different option for database, like database version, last optinmisation date
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**
 * Allows to manage the different option for database, like database version, last optinmisation date
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_db_option
{
	/**
	*	The plugin database version
	*/
	protected $db_version;
	/**
	*	The last optimisation date
	*/
	protected $db_optimisation_date;

	/**
	*	Declare the different options for the plugin	
	*/
	function add_options() 
	{
		register_setting('wpshop_db_option', 'wpshop_db_option');
	}

	/**
	*	Class contructor. Get the current configuration into database for protected var setting
	*
	*	@return null
	*/
	function wpshop_db_option()
	{
		$db_option = unserialize(get_option('wpshop_db_option'));

		$this->set_db_version($db_option['db_version']);
		$this->set_db_optimisation_date($db_option['db_optimisation_date']);

		return;
	}

	/**
	*	Method for database option value creation, launched at plugin's activation
	*
	*	@return null
	*/
	function create_db_option()
	{
		$new_db_options = array();
		$new_db_options['db_version'] = '0';
		wpshop_db_option::set_db_version($new_db_options['db_version']);
		$new_db_options['db_optimisation_date'] = date('Y-m-d');
		wpshop_db_option::set_db_optimisation_date($new_db_options['db_optimisation_date']);

		add_option('wpshop_db_option', serialize($new_db_options));

		return;
	}

	/**
	*	Method for database option update
	*
	*	@return null
	*/
	function set_db_option()
	{
		$new_db_options = array();
		$new_db_options['db_version'] = $this->get_db_version();
		$new_db_options['db_optimisation_date'] = $this->get_db_optimisation_date();

		update_option('wpshop_db_option',serialize($new_db_options));

		return;
	}

	/**
	*	Method to access to the current database version
	*
	*	@return integer The current database version
	*/
	function get_db_version()
	{
		return $this->db_version;
	}
	/**
	*	Method to set the database version
	*
	*	@return null
	*/
	function set_db_version($db_version)
	{
		$this->db_version = $db_version;

		return;
	}

	/**
	*	Method to access to the current optimisation database date
	*
	*	@return integer The current optimisation database date
	*/
	function get_db_optimisation_date()
	{
		return $this->db_optimisation_date;
	}
	/**
	*	Method to set the last database optimisation date
	*
	*	@return null
	*/
	function set_db_optimisation_date($db_optimisation_date)
	{
		$this->db_optimisation_date = $db_optimisation_date;
	}

}