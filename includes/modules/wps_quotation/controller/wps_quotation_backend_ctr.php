<?php
class wps_quotation_backend_ctr {
	/*
	 * Declare filter and actions
	 */
	public function __construct() {
		add_filter( 'wps-filter-free-product-bton-tpl', array( $this, 'wps_free_product_bton_tpl' ) );
		add_action( 'wp_ajax_wps_free_product_form_page_tpl', array( $this, 'wps_free_product_form_page_tpl' ) );
		add_action( 'wp_ajax_wps_create_new_free_product', array( $this, 'wps_create_new_free_product' ) );
		add_action( 'init', array( $this, 'wps_free_product_post_status' ) );
	}

	/*
	 * Create a new post status for free products
	 */
	function wps_free_product_post_status(){
		register_post_status( 'free_product', array(
		'label'                     => __( 'Free product', 'wpshop' ),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => false,
		) );
	}
	/*
	 * Template for display button - Filter : 'wps_orders\templates\backend\product-listing\wps_orders_product_listing.php'
	 * @param integer $order_id ID of order
	 */
	public function wps_free_product_bton_tpl($order_id) {
		$order_post_meta = get_post_meta( $order_id, '_wpshop_order_status', true );
		if ( 'completed' != $order_post_meta ) {
			require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_PATH . WPS_QUOTATION_DIR . "/templates/", "backend", "add_free_product_bton_tpl") );
		}
	}
	/*
	 * Template for display form (AjaxForm) - Call from : Line 3 'templates\backend\add_free_product_form_page_tpl.php'
	 */
	public function wps_free_product_form_page_tpl() {
		$order_id = ( !empty($_GET['oid']) ) ? intval( $_GET['oid']) : null;
		require ( wpshop_tools::get_template_part( WPS_QUOTATION_DIR, WPS_QUOTATION_PATH . WPS_QUOTATION_DIR . "/templates/", "backend", "add_free_product_form_page_tpl") );
		wp_die();
	}
	/*
	 * Ajax - Free product function creation
	 * @return boolean $status Status of request ajax
	 * @return string $message Message of error
	 * @return integer $pid Product ID
	 */
	public function wps_create_new_free_product() {
		global $wpdb;
		$status = false;
		$output = __('Error at product creation!', 'wpshop');
		$new_product_id = -1;

		$post_title = ( !empty($_POST['post_title']) ) ? $_POST['post_title'] : -1;
		$post_content = ( !empty($_POST['post_content']) ) ? $_POST['post_content'] : '';
		$attributes = ( !empty($_POST['attribute']) ) ? $_POST['attribute'] : -1;

		if( $post_title != -1 ) {
			$new_product_id = wp_insert_post( array(
					'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
					'post_status' => 'free_product',
					'post_title' => $post_title,
					'post_content' => $post_content,
			) );
			if( !is_object( $new_product_id ) ) {
				$attribute_set_list = wpshop_attributes_set::get_attribute_set_list_for_entity(wpshop_entities::get_entity_identifier_from_code('wpshop_product'));
				$id_attribute_set = null;
				foreach( $attribute_set_list as $attribute_set ) {
					if( $attribute_set->name == 'free_product' ) {
						$id_attribute_set = $attribute_set->id;
						break;
					}
				}
				update_post_meta( $new_product_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT . '_attribute_set_id', $id_attribute_set );
				$data_to_save['post_ID'] = $data_to_save['product_id'] = intval( $new_product_id );
				$data_to_save['wpshop_product_attribute'] = ( !empty($attributes) ) ? $attributes : array();
				$data_to_save['user_ID'] = get_current_user_id();
				$data_to_save['action'] = 'editpost';
				$status = false;
				$output = __('Product created partially!', 'wpshop');
				if( !empty($new_product_id) && !empty( $data_to_save['user_ID'] ) ) {
					$product_class = new wpshop_products();
					$product_class->save_product_custom_informations( $new_product_id, $data_to_save );
					$status = true;
					$output = __('Product created successfully.', 'wpshop');
				}
			}
		}

		echo json_encode( array( 'status' => $status, 'message' => $output, 'pid' => $new_product_id ) );
		wp_die();
	}
}