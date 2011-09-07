<?php

class WP_Widget_Wpshop_Product_categories extends WP_Widget {

	/**
	* Widget Constuctor
	*
	*	@return An instance of wp widget
	*/
	function WP_Widget_Wpshop_Product_categories(){
		$params = array(
			'classname' => 'widget_wpshop_pdt_categories',
			'description' => __( 'Product categories widget', 'wpshop' )
		);
		$this->WP_Widget( 'wpshop_pdt_categories', __( 'Product Categories', 'wpshop' ), $params );
	}

	/**
	*	Define the content for the widget
	*
	*	@param mixed $instance The current widget instance
	*/
	function form( $instance ){
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'empty_title' => false
		));

		$title    = esc_attr( $instance['title'] );
		$empty_title    = (bool) $instance['empty_title'];
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title', 'wpshop' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
<?php
	}

	/**
	* Widget Output
	*
	* @param array $args
	* @param array $instance Widget values.
	*/
	function widget($args, $instance){
		$widget_content = '';

		/*	Get the default args from wordpress	*/
		extract($args);

		/*	Get the widget title from the admin configuration	*/
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Catalog', 'wpshop') : $instance['title'] );

		/*	Get the widget's content	*/
		$widget_content = wpshop_categories::category_tree_output(0);

		/*	Add the different element to the widget	*/
		$widget_content = $before_widget . $before_title . $title . $after_title . $widget_content . $after_widget;

		echo $widget_content;
	}
}