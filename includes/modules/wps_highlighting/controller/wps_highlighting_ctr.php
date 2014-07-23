<?php
class wps_highlighting_ctr {
	
	/** Define the main directory containing the template for the current plugin
	 * @var string
	 */
	private $template_dir;
	/**
	 * Define the directory name for the module in order to check into frontend
	 * @var string
	 */
	private $plugin_dirname = WPS_HIGHLIGHTING_DIR;
	
	function __construct() {
		$this->template_dir = WPS_HIGHLIGHTING_PATH . WPS_HIGHLIGHTING_DIR . "/templates/";
		add_action( 'init', array($this, 'register_post_type') );
		add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
		add_action( 'save_post', array($this, 'save_post_action') );
		
		add_shortcode( 'wps_highlighting', array( $this, 'display_highlightings' ) );
	}
	
	/** Load templates **/
	function get_template_part( $side, $slug, $name=null ) {
		$path = '';
		$templates = array();
		$name = (string)$name;
		if ( '' !== $name )
			$templates[] = "{$side}/{$slug}-{$name}.php";
		else
			$templates[] = "{$side}/{$slug}.php";
	
		/**	Check if required template exists into current theme	*/
		$check_theme_template = array();
		foreach ( $templates as $template ) {
			$check_theme_template = $this->plugin_dirname . "/" . $template;
		}
		$path = locate_template( $check_theme_template, false );
	
		if ( empty( $path ) ) {
			foreach ( (array) $templates as $template_name ) {
				if ( !$template_name )
					continue;
	
				if ( file_exists($this->template_dir . $template_name)) {
					$path = $this->template_dir . $template_name;
					break;
				}
			}
		}
	
		return $path;
	}
	
	/**
	 * Register Post type
	 */
	function register_post_type() {
		$labels = array(
				'name'               => __( 'Highlighting', 'wps_highlighting' ),
				'singular_name'      => __( 'Highlighting', 'wps_highlighting' ),
				'menu_name'          => __( 'Highlightings', 'wps_highlighting' ),
				'add_new'            => __( 'Add new highlighting', 'wps_highlighting' ),
				'add_new_item'       => __( 'Add new highlighting', 'wps_highlighting' ),
				'new_item'           => __( 'New Highlighting', 'wps_highlighting' ),
				'edit_item'          => __( 'Edit Highlighting', 'wps_highlighting' ),
				'view_item'          => __( 'View Highlighting', 'wps_highlighting' ),
				'all_items'          => __( 'All Highlightings', 'wps_highlighting' ),
				'search_items'       => __( 'Search Highlighting', 'wps_highlighting' ),
				'parent_item_colon'  => __( 'Parent Highlighting :', 'wps_highlighting' ),
				'not_found'          => __( 'No Highlighting found.', 'wps_highlighting' ),
				'not_found_in_trash' => __( 'No Highlightings found in Trash.', 'wps_highlighting' ),
		);
		
		$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'menu_icon'			 => 'dashicons-star-filled',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => true,
				'menu_position'      => null,
				'supports'           => array( 'title', 'thumbnail' )
		);
		
		register_post_type( WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, $args );
	}
	
	/**
	 * Add Meta Box
	 */
	function add_meta_boxes() {
		add_meta_box( 'wps_highlighting_meta_box', __( 'Select the hook', 'wps_highlighting'), array( $this, 'meta_box_content' ), WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, 'side', 'default');
		add_meta_box( 'wps_highlighting_meta_box_link', __( 'Link of Highlighting', 'wps_highlighting'), array( $this, 'meta_box_content_link' ), WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING, 'side', 'default');
	}
	
	/**
	 * Meta Box content
	 */
	function meta_box_content() {
		global $post;
		
		$hook = get_post_meta( $post->ID, '_wps_highlighting_hook', true );
		$output  = '<select name="wps_highlighting_hook">';
		$output .= '<option value="sidebar" ' .( ( !empty($hook) && $hook == 'sidebar' ) ? 'selected="selected"' : '' ). '>' .__( 'Sidebar', 'wps_highlighting' ). '</option>';
		$output .= '<option value="home" ' .( ( !empty($hook) && $hook == 'home' ) ? 'selected="selected"' : '' ). '>' .__( 'HomePage Content', 'wps_highlighting' ). '</option>';
		$output .= '</select>';
		$output .= '<hr/>';
		$output .= '<div style="padding : 5px; background #CCC;"><u><strong>' .__( 'shortcode for display Highlightings', 'wpshop'). '</strong></u><ul><li><u>Home page content :</u> [wps_highlighting hook_name="home"]</li><li><u>Sidebar :</u> [wps_highlighting hook_name="sidebar"]</li><ul></div>';
		echo $output;
	}
	
	function meta_box_content_link() {
		global $post;
		$link = get_post_meta( $post->ID, '_wps_highlighting_link', true );
		$output  = '<label for="wps_highlighting_link">' .__( 'Link of Highlighting', 'wps_highlighting' ). '</label><br/>';
		$output .= '<input type="text" id="wps_highlighting_link" name="wps_highlighting_link" value="' .$link. '" />';
		echo $output;
	}
	
	/**
	 * Save action
	 */
	function save_post_action() {
		if( !empty($_POST['post_type']) && !empty($_POST['post_type']) && $_POST['post_type'] == WPS_NEWTYPE_IDENTIFIER_HIGHLIGHTING ) {
			if( !empty($_POST['wps_highlighting_hook']) ) {
				update_post_meta( $_POST['post_ID'], '_wps_highlighting_hook', $_POST['wps_highlighting_hook'] );
			}
			update_post_meta( $_POST['post_ID'], '_wps_highlighting_link', $_POST['wps_highlighting_link']); 
		}
	}

	function get_data_for_hook( $hook ) {
		$highlightings_datas = array();
		if( !empty($hook) ) {
			$wps_highlighting_mdl = new wps_highlighting_model();
			$highlightings = $wps_highlighting_mdl->get_highlighting( $hook );
			if( !empty($highlightings) ) {
				foreach( $highlightings as $highlighting ) {
					$wps_highlighting = new wps_highlighting_model( $highlighting['post_data']->ID, $highlighting['post_data']->post_title, $highlighting['post_meta']['hook'], $highlighting['post_meta']['link'] );
					$highlightings_datas[] = $wps_highlighting;
				}
			}
		}
		return $highlightings_datas;
	}
	
	function display_highlightings( $args ) {
		$output = $highlightings = '';
		if( !empty($args) && !empty($args['hook_name']) ) {
			$datas = $this->get_data_for_hook( $args['hook_name'] );
			//Display in Template
			if( !empty($datas) ) {
				foreach( $datas as $data ) {
					ob_start();
					require( $this->get_template_part( "frontend", "highlighting") );
					$highlightings .= ob_get_contents();
					ob_end_clean();
				}
			}
		}
		return $highlightings;
	}
	
}