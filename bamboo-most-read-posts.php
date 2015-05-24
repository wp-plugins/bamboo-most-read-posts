<?php
/**************************************************************************************************/
/*                                                                                                */
/* Plugin Name: Bamboo Most Read Posts                                                            */
/* Plugin URI:  http://www.bamboosolutions.co.uk/wordpress/plugins/bamboo-most-read-posts         */
/* Author:      Bamboo Solutions                                                                  */
/* Author URI:  http://www.bamboosolutions.co.uk                                                  */
/* Version:     1.0                                                                               */
/* Description: Provides a widget to display your site's most read posts          				  */
/*                                                                                                */
/**************************************************************************************************/

	// Disable post prefetching to keep view counts accurate
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

/**************************************************************************************************/

	// Enable post view tracking
	function bamboo_most_read_track_post_views( $post_id ) {

		if( !is_single() ) {
			return;
		}
		if( empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}

		$count_key = 'Bamboo Most Read View Count';
	    $count = get_post_meta( $post_id, $count_key, true );
    	if( $count=='' ){
        	$count = 0;
        	delete_post_meta( $post_id, $count_key );
        	add_post_meta( $post_id, $count_key, '0' );
    	} else {
        	$count++;
        	update_post_meta( $post_id, $count_key, $count );
	    }

	}
	add_action( 'wp_head', 'bamboo_most_read_track_post_views' );

/**************************************************************************************************/

	// Register the widget
	function register_bamboo_most_read() {

		register_widget( 'Bamboo_Most_Read' );

	}
	add_action( 'widgets_init', 'register_bamboo_most_read' );

/**************************************************************************************************/

	class Bamboo_Most_Read extends WP_Widget {

/**************************************************************************************************/

		public function __construct() {

			parent::__construct(
		 		'bamboo_most_read', // Base ID
				'Bamboo Most Read Posts', // Name
				array( 'description' => __( 'Your site\'s most read posts', 'bamboo' ), )
			);

		}

/**************************************************************************************************/

	 	public function form( $instance ) {

			if ( isset( $instance['title'] ) ) {
				$title = $instance[ 'title' ];
			} else {
				$title = '';
			}

			if( isset( $instance['count'] ) ){
				$count = $instance['count'];
			} else {
				$count = 5;
			}

			if( isset( $instance['display_date'] ) ){
				$display_date = $instance['display_date'];
			} else {
				$display_date = "off";
			}

?>
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'bamboo' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<p>
		<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'display_date' ); ?>" name="<?php echo $this->get_field_name( 'display_date' ); ?>" <?php if( 'on'==$display_date ) echo "checked=\"checked\"";?> >
		<label for="<?php echo $this->get_field_id( 'display_date' ); ?>"><?php _e( 'Display post date?', 'bamboo' ); ?></label></p>
	<p>
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of posts to show:', 'bamboo'); ?></label>
		<input id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" size="3" value="<?php echo esc_attr($count); ?>" />
	</p>
<?php
		}

/**************************************************************************************************/

		public function update( $new_instance, $old_instance ) {

			$instance = array();

			$instance['title'] 	  	  = strip_tags( $new_instance['title'] );
			$instance['display_date'] = strip_tags( $new_instance['display_date'] );
			$instance['count'] 	  	  = strip_tags( $new_instance['count'] );

			if( !is_numeric( $instance['count'] ) ) {
				$instance['count'] = "0";
			}

			return $instance;

		}

/**************************************************************************************************/

		public function widget( $args, $instance ) {

			extract( $args );

			$title 	  	  = apply_filters( 'widget_title', $instance['title'] );
			$display_date = $instance['display_date'];
			$count 	  	  = $instance['count'];

			$count_key = 'Bamboo Most Read View Count';
			$atts = Array(
				'meta_key' 		 => $count_key,
				'order_by' 	 	 => 'meta_value_num',
				'order' 		 => 'DESC',
				'posts_per_page' => $count
			);
			$query = new WP_Query( $atts );

			echo $before_widget;

			echo "<h2 class=\"widgettitle\">$title</h2>";
			echo "<ul>";

			while ( $query->have_posts() ) :

				$query->the_post();
				echo "<li><a href=\"" . get_permalink() . "\">" . get_the_title() ."</a></li>";
				if( "on"==$display_date ) {
					echo "<span class=\"post-date\">" . get_the_date() . "</span>";
				}

			endwhile;

			echo "</ul>";

			echo $after_widget;

		}

/**************************************************************************************************/

	}

/**************************************************************************************************/
?>