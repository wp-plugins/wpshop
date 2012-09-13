<?php

/*	Vérification de l'inclusion correcte du fichier => Interdiction d'acceder au fichier directement avec l'url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

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
class wpshop_metabox {

    /**
     * Adds the meta box container to the page "post" and "page"
     */
    function add_meta_boxes() {
		// Page "post"
		add_meta_box( 
             'wpshop_shortcodes_insertion_box_for_post', __('Shortcodes insertion', 'wpshop'),
            array('wpshop_metabox', 'render_meta_box_content'), 'post', 'side', 'default'
        );
		// Page "page"
        add_meta_box( 
             'wpshop_shortcodes_insertion_box_for_page', __('Shortcodes insertion', 'wpshop'),
            array('wpshop_metabox', 'render_meta_box_content'), 'page', 'side', 'default'
        );

		/**
		 * Ajout d'une metabox permettant de sélectionner la page a utiliser pour les différentes pas de wpshop
		 */
        add_meta_box( 
             'wpshop_page_selection', __('Wpshop pages configuration', 'wpshop'),
            array('wpshop_metabox', 'display_page_option_choice'), 'page', 'side', 'default'
        );
    }

    /**
     * Render Meta Box content
     */
    function render_meta_box_content() {
		// Products list
		$product_string = wpshop_products::product_list(true);
		// Attributs list
		$products_attr_string = wpshop_products::product_list_attr(true);
		// Groups list
		$groups_string = wpshop_products::product_list_group_attr(true);
		// Category list
		$cats_string = wpshop_categories::product_list_cats(true);
		
		$content = '
		<div id="wpshop_shortcode_post_insert_tab" class="wpshop_tabs wpshop_detail_tabs wpshop_shortcode_post_insert" >
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

    /**
     * Affichage de la box permettant de sélectionner la page wpshop pour laquelle la page en cours d'édition sera utilisée
     * @param unknown_type $post
     */
	function display_page_option_choice($post) {
		$content = '';

		$content .= __('Use this page as default page for', 'wpshop');

		$page_list = unserialize(WPSHOP_DEFAULT_PAGES);
		$done = false;
		foreach ( $page_list as $page_type => $pages) {
			foreach ( $pages as $page_def ) {
				$checked = '' ;
				if ((strpos($page_def['post_content'], $post->post_content) !== false) && !$done) {
					$checked = 'checked="checked" ';
					$done = true;
				}
				$content .= '<p><input type="radio" value="' . $page_def['page_code'] . '" '.$checked.'name="wpshop_default_pages[]" id="' . $page_def['page_code'] . '" /> <label for="' . $page_def['page_code'] . '" >' . __($page_def['post_title'], 'wpshop') . '</label></p>';
			}
		}

		echo $content;
	}

	/**
	 * Enregistrement de la page wpshop utilisant la page en cours d'édition
	 * @param unknown_type $post_id
	 */
	function save_custom_informations($post_id) {
		if (get_post_type($post_id) == 'page') {
			$page_to_update = isset($_POST['wpshop_default_pages'][0]) ? wpshop_tools::varSanitizer($_POST['wpshop_default_pages'][0]) : null;

			update_option($page_to_update, $post_id);
		}
	}

}