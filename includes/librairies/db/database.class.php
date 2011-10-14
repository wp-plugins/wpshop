<?php
/**
* Plugin database librairies include file.
* 
*	This file contains the different methods for database management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies-db
*/

/**
* Define the different method to access to database, for database creation and update for the different version
* @package wpshop
* @subpackage librairies-db
*/
class wpshop_database
{

	/**
	*	Get the field list into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field_list A wordpress database object containing the different field of the table
	*/
	function get_field_list($table_name)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name);
		$field_list = $wpdb->get_results($query);

		return $field_list;
	}
	/**
	*	Get a field defintion into a database table
	*
	*	@param string $table_name The name of the table we want to retrieve field list for
	*
	*	@return object $field A wordpress database object containing the field definition into the database table
	*/
	function get_field_definition($table_name, $field)
	{
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name . " WHERE Field = %s", $field);
		$fieldDefinition = $wpdb->get_results($query);

		return $fieldDefinition;
	}

	/**
	*	Make a translation of the different database field type into a form input type
	*
	*	@param string $table_name The name of the table we want to retrieve field input type for
	*
	*	@return array $field_to_form An array with the list of field with its type, name and value
	*/
	function fields_to_input($table_name)
	{

		$list_of_field_to_convert = wpshop_database::get_field_list($table_name);

		$field_to_form = self::fields_type($list_of_field_to_convert);

		return $field_to_form;
	}

	/**
	*	Transform the database table definition into an array for building a input for users
	*
	*	@param array $list_of_field_to_convert The list of field we want to have the types for
	*
	*	@return array $field_to_form The field stored into an array
	*/
	function fields_type($list_of_field_to_convert)
	{
		$field_to_form = array();
		$i = 0;
		foreach ($list_of_field_to_convert as $Key => $field_definition)
		{
			$field_to_form[$i]['name'] = $field_definition->Field;
			$field_to_form[$i]['value'] = $field_definition->Default;

			$type = 'text';
			if(($field_definition->Key == 'PRI') || ($field_definition->Field == 'creation_date') || ($field_definition->Field == 'last_update_date'))
			{
				$type =  'hidden';
			}
			else
			{
				$fieldtype = explode('(',$field_definition->Type);
				if($fieldtype[1] != '')$fieldtype[1] = str_replace(')','',$fieldtype[1]);

				if(($fieldtype[0] == 'char') || ($fieldtype[0] == 'varchar') || ($fieldtype[0] == 'int'))
				{
					$type = 'text';
				}
				elseif($fieldtype[0] == 'text')
				{
					$type = 'textarea';
				}
				elseif($fieldtype[0] == 'enum')
				{
					$fieldtype[1] = str_replace("'","",$fieldtype[1]);
					$possible_value = explode(",",$fieldtype[1]);

					if(count($possible_value) > 1)
					{
						$type = 'select';
					}
					else
					{
						$type = 'radio';
					}

					$field_to_form[$i]['possible_value'] = $possible_value;
				}
			}
			$field_to_form[$i]['type'] = $type;
			
			$i++;
		}
		return $field_to_form;
	}



	/**
	*	Save a new attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the creation has been done correctly or not
	*/
	function save($informationsToSet, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$updateResult = $wpdb->insert($dataBaseTable, $informationsToSet, '%s');
		if( $updateResult != false )
		{
			$requestResponse = 'done';
		}
		else
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}
	/**
	*	Update an existing attribute in database
	*
	*	@param array $informationsToSet An array with the different information we want to set
	*
	*	@return string $requestResponse A message that allows to know if the update has been done correctly or not
	*/
	function update($informationsToSet, $id, $dataBaseTable)
	{
		global $wpdb;
		$requestResponse = '';

		$updateResult = $wpdb->update($dataBaseTable, $informationsToSet , array( 'id' => $id ), '%s', array('%d') );

		if( $updateResult == 1 )
		{
			$requestResponse = 'done';
		}
		elseif( $updateResult == 0 )
		{
			$requestResponse = 'nothingToUpdate';
		}
		elseif( $updateResult == false )
		{
			$requestResponse = 'error';
		}

		return $requestResponse;
	}


	/**
	*	Check the database structure on plugin load
	*
	* @return void If there are errors display an admin notice
	*/
	function check_database(){
		global $db_error, $wpdb, $wpshop_db_table, $wpshop_db_table_version;
		$db_error = array();

		/*	Check if main database are correctly created	*/
		if(is_array($wpshop_db_table)){
			foreach($wpshop_db_table as $table_type => $table_definition){
				if(isset($table_definition['db_table_name']) && (wpshop_database::check_table_existence($table_definition['db_table_name']) == '')){
					$db_error['not_existing_table'][] = $table_definition['db_table_name'];
				}
			}
		}

		/*	Check if additionnal database table are correctly created	*/
		if(is_array($wpshop_db_table_version)){
			/*	New database table creation	*/
			foreach($wpshop_db_table_version as $db_version => $db_definition){
				foreach($db_definition as $table_type => $table_definition){
					if(isset($table_definition['db_table_name']) && (wpshop_database::check_table_existence($table_definition['db_table_name']) == '')){
						$db_error['not_existing_table'][] = $table_definition['db_table_name'];
					}
				}
			}
		}

		if(count($db_error) > 0){
			add_action('admin_notices', array('wpshop_database', 'database_notice'));
		}
	}
	/**
	*	Display an admin notice in order to inform user that there is an error with database
	*
	*	@return mixed An error message output
	*/
	function database_notice(){
		global $db_error;
?>
	<div id="wpshop_db_error_message" class="updated fade">
		<p>
<?php 
			echo _e('There is an error with the wpshop plugin database. below is a list of errors.', 'wpshop'); 
			if(isset($db_error['not_existing_table']) && (count($db_error['not_existing_table']) > 0)){
?>
			<br/><br/><span class="bold" >Table not existing</span>:&nbsp;<?php echo implode(', ', $db_error['not_existing_table']); 
			}
?>
		</p>
	</div>
<?php
	}
	/**
	*	Check if a table exist into database
	*
	*	@param string $table_name The table name we want to check
	*
	*	@return string $existing_table Will be empty if table does not exist, will contains table name if table exists
	*/
	function check_table_existence($table_name){
		global $wpdb;

		$query = $wpdb->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = %s AND table_name = %s", $wpdb->dbname, $table_name);
		$existing_table = $wpdb->get_var($query);

		return $existing_table;
	}

}