<?php



class wpeo_wish_list_model{

    var $product_ID;
    var $product_Title;
    var $product_Link;
    var $product_Thumbnail;
    var $product_Description;


    function __construct($id = '', $title = '', $link = '', $thumbnail = '', $description = '' ){
        if(!empty($id))
        {
            $this->product_ID = $id;
            $this->product_Title = $title;
            $this->product_Link = $link;
            $this->product_Thumbnail = $thumbnail;
            $this->product_Description = $description;
        }
    }

    public function get_all_product() // Get all product from wish list on an array
    {
        $user_ID = get_current_user_id();
        $all_Product = array();

        $meta_values = get_user_meta($user_ID, 'wish-list-item', true); // Get all product ID in string

        if($meta_values) // If not false (empty)
        {
             foreach($meta_values as $val)
             {
             	if ( !empty( $val ) ) {
                 $product = get_post( $val ); // Get post by id on an object
                 $my_new_product = new wpeo_wish_list_model($product->ID, $product->post_title, get_permalink( $product->ID ), get_the_post_thumbnail( $product->ID ) );

                 $all_Product[] = $my_new_product; // Add new object on array
             	}
             }
        }
        else {
        	//affichage message wishlist vide
        }
        return $all_Product;
    }

    // Add a product on wish list
    public function wps_wishlist_add_product() {
    	global $wpeo_wish_list;

    	$user_ID = get_current_user_id();
        $product_ID = $_POST['product_id']; // Get current post id by Ajax

        $button = '';
        if(!empty($product_ID))
        {
            $meta_values = (array)get_user_meta($user_ID, 'wish-list-item', true); // Get all product ID in string
            if ( !in_array( $product_ID, $meta_values )) {
           		$meta_values[] = $product_ID; // Concatenate new id product with separator ';'
            }

            $check = update_user_meta( $user_ID, 'wish-list-item', $meta_values ); // Add product

            if($check) // Check process
            {
                $status = true;
                $message = __("Product added on your wish list", 'wp_wish_list');
                ob_start();
                require( $wpeo_wish_list->get_template_part( WPWISHLIST_DIR, WPWISHLIST_TEMPLATES_DIR, "frontend", "button", "delete") );
                $button = ob_get_contents();
                ob_end_clean();
            }
            else
            {
                $status = false;
                $message = __("Error, you must be connected", 'wp_wish_list');
            }
        }
        else
        {
            $status = false;
            $message = __("Error", 'wp_wish_list');
        }

        wp_die(json_encode(array('status' => $status, 'message' => $message, 'product_ID' => $product_ID, 'button' => $button)));
    }


    function wps_wishlist_remove_product()
    {
        $deleted = false; // First, product is not delete
        $i = 0; // To trought array
        $meta_values_end = ''; // Final string
        $product_ID = $_POST['product_id'];

        if(!empty($product_ID))
        {
            $user_ID = get_current_user_id();

            $products = $this->get_all_product();

            if(!empty($products)) // If array not empty
            {
                while(!$deleted)
                {
                    if($products[$i]->product_ID == $product_ID)
                    {
                        unset($products[$i]); // Delete product
                        $deleted = true;
                    }
                    $i++;
                }

                foreach ($products as $product)
                {
                    // For each products, save in string his ID
                    $meta_values_end = $meta_values_end . (string)$product->product_ID . ';';
                }

                $check = update_user_meta($user_ID, 'wish-list-item', $meta_values_end); // Update table

                if($check)
                {
                    $status = true;
                    $message = __("Product deleted from your wish list", 'wp_wish_list');
                }
                else // Error update table
                {
                    $status = false;
                    $message = __("Error", 'wp_wish_list');
                }
            }
            else // array $products empty
            {
                $status = false;
                $message = __("Error", 'wp_wish_list');
            }
        }
        else // Product id empty
        {
           $status = false;
           $message = __("Error", 'wp_wish_list');
        }
        wp_die(json_encode(array('status' => $status, 'message' => $message)));
    }

} // End class
?>
