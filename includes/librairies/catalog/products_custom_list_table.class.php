<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Product_List_Table extends WP_List_Table {

	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct(){
			parent::__construct( array(
			'singular'=> 'wp_list_product', //Singular label
			'plural' => 'wp_list_products', //plural label, also this well be one of the table css class
			'ajax' => false
		) );
	}

	/**
	*	Define the output for each column if specific column output has not been defined
	*
	*	@param object|array $item The item to output column content for
	*	@param string $column_name The column that we are trying to output
	*
	*	@return mixed The column output if found, or defaut is an complete output of the item
	*/
	function column_default($item, $column_name){
		switch($column_name){
			case 'product_name':
				return $item['post_title'];
			break;
			case 'product_id':
				return '<label for="wpshop_product_cb_dialog_' . $item['ID'] . '" >' . WPSHOP_IDENTIFIER_PRODUCT . $item['ID'] . '</label>';
			break;
			case 'product_reference':
				return $item[$column_name];
			break;
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
			break;
		}
	}

	/**
	*	Define specific outptu for a given column
	*
	*	@param object|array $item The item we want to get the specific output for the current column
	*
	*	@return string The output build specificly for the given column
	*/
	function column_product_qty($item){
		return  '<a href="#" class="order_product_action_button qty_change">-</a><input type="text" name="wpshop_pdt_qty[' . $item['ID']  . ']" value="1" class="wpshop_order_product_qty" /><a href="#" class="order_product_action_button qty_change">+</a>';
	}

	/**
	*	Define specific outptu for a given column
	*
	*	@param object|array $item The item we want to get the specific output for the current column
	*
	*	@return string The output build specificly for the given column
	*/
	function column_product_price($item){
		return  __('Price ET', 'wpshop') . '&nbsp;:&nbsp;' . $item[WPSHOP_PRODUCT_PRICE_HT] . '&nbsp;' . wpshop_tools::wpshop_get_currency() . '<br/>' . __('Price ATI', 'wpshop') . '&nbsp;:&nbsp;' . $item[WPSHOP_PRODUCT_PRICE_TTC] . '&nbsp;' . wpshop_tools::wpshop_get_currency();
	}

	/**
	*	Define specific outptu for a given column
	*
	*	@param object|array $item The item we want to get the specific output for the current column
	*
	*	@return string The output build specificly for the given column
	*/
	function column_product_url($item){
		return '<a href="' . $item['guid'] . '" target="wpshop_product_view_product" target="wpshop_view_product" >' . __('View product', 'wpshop') . '</a><br/>
		<a href="' . admin_url('post.php?post=' . $item['ID']  . '&action=edit') . '" target="wpshop_edit_product" >' . __('Edit product', 'wpshop') . '</a>';
	}

	/**
	*	Define specific outptu for a given column
	*
	*	@param object|array $item The item we want to get the specific output for the current column
	*
	*	@return string The output build specificly for the given column
	*/
	function column_cb($item){
		return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" class="wpshop_product_cb_dialog" id="wpshop_product_cb_dialog_%2$s" />',
				/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
		);
	}

	/**
	* Define the columns that are going to be used in the table
	* @return array $columns, the array of columns to use with the table
	*/
	function get_columns() {
		return $columns= array(
			'cb'=>'',
			'product_id'=>'',
			'product_qty'=>__('Quantity', 'wpshop'),
			'product_reference'=>__('Sku', 'wpshop'),
			'product_name'=>__('Name', 'wpshop'),
			'product_url'=>__('Url', 'wpshop'),
			'product_price'=>__('Price', 'wpshop')
		);
	}

	/**
	* Define the columns that are going to be used for sorting the table
	* @return array $columns, the array of sortable columns in the table
	*/
	function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	*	Build The table output for future display
	*
	*	@param array|object $data, The list of item to display in the table
	*	@param int $per_page, The number of items per page in the table
	*	@param int $current_page, The current page number allowing to know wich item to display
	*
	*	@return void
	*/
	function prepare_items($data, $per_page, $current_page){		
		/**
		* REQUIRED. Now we need to define our column headers. This includes a complete
		* array of columns to be displayed (slugs & titles), a list of columns
		* to keep hidden, and a list of columns that are sortable. Each of these
		* can be defined in another method (as we've done here) before being
		* used to build the value for our _column_headers property.
		*/
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(array());
	}

}