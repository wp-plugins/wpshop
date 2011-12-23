<?php
/**
* Define the different metaboxes to insert
* 
*	Define the different metaboxes to insert
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different metaboxes to insert
* @package wpshop
* @subpackage librairies
*/
class wpshop_metabox
{
    /**
     * Adds the meta box container to the page "post" and "page"
     */
    public function add_some_meta_box()
    {
		// Page "post"
		add_meta_box( 
             'some_meta_box_name', __('Shortcodes insertion', 'wpshop'),
            array('wpshop_metabox', 'render_meta_box_content'), 'post', 'side', 'default'
        );
		// Page "page"
        add_meta_box( 
             'some_meta_box_name', __('Shortcodes insertion', 'wpshop'),
            array('wpshop_metabox', 'render_meta_box_content'), 'page', 'side', 'default'
        );
    }

    /**
     * Render Meta Box content
     */
    public function render_meta_box_content() 
    {
		// Products list
		$product_string = wpshop_products::product_list(true);
		// Attributs list
		$products_attr_string = wpshop_products::product_list_attr(true);
		// Groups list
		$groups_string = wpshop_products::product_list_group_attr(true);
		// Category list
		$cats_string = wpshop_categories::product_list_cats(true);
		
		$content = '
		<div id="superTab">
			<ul>
				<li><a href="#wpshop_product_category-1">'.__('Products','wpshop').'</a></li>
				<li><a href="#wpshop_product_category-2">'.__('Attributs','wpshop').'</a></li>
				<li><a href="#wpshop_product_category-3">'.__('Attributs groups','wpshop').'</a></li>
				<li><a href="#wpshop_product_category-4">'.__('Category','wpshop').'</a></li>
			</ul>
			
			<div id="wpshop_product_category-1" class="simple">
				<input type="text" value="" placeholder="'.__('Search...','wpshop').'" id="search_products" />
				<div>
				<ul id="products_selected">
					'.$product_string.'
				</ul>
				</div><br />
				<label><input type="radio" checked="checked" name="product_display_type" value="list" /> '.__('List', 'wpshop').'</label> &nbsp;
				<label><input type="radio" name="product_display_type" value="grid" /> '.__('Grid', 'wpshop').'</label>
				<a class="preview button" id="insert_products">'.__('Insert','wpshop').'</a><br /><br />
			</div>
			
			<div id="wpshop_product_category-2" class="simple">
				<input type="text" value="" placeholder="'.__('Search...','wpshop').'" id="search_attr" />
				<div>
				<ul id="attr_selected">
					'.$products_attr_string.'
				</ul>
				</div><br />
				<a class="preview button" id="insert_attr">'.__('Insert','wpshop').'</a><br /><br />
			</div>
			
			<div id="wpshop_product_category-3" class="simple">
				<input type="text" value="" placeholder="'.__('Search...','wpshop').'" id="search_groups" />
				<div>
				<ul id="groups_selected">
					'.$groups_string.'
				</ul>
				</div><br />
				<a class="preview button" id="insert_groups">'.__('Insert','wpshop').'</a><br /><br />
			</div>
			
			<div id="wpshop_product_category-4" class="simple">
				<input type="text" value="" placeholder="'.__('Search...','wpshop').'" id="search_cats" />
				<div>
				<ul id="cats_selected">
					'.$cats_string.'
				</ul>
				</div><br />
				<label><input type="radio" checked="checked" name="cats_display_type" value="list" /> '.__('List', 'wpshop').'</label> &nbsp;
				<label><input type="radio" name="cats_display_type" value="grid" /> '.__('Grid', 'wpshop').'</label>
				<a class="preview button" id="insert_cats">'.__('Insert','wpshop').'</a><br /><br />
			</div>
		</div>';
		
        echo $content;
    }
}