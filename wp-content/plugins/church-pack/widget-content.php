<?php
// Register Post Type
add_action( 'init', 'register_cpt_wpfc_widget_content' );

function register_cpt_wpfc_widget_content() {

    $labels = array( 
        'name' => _x( 'Widget Content', 'churchpack' ),
        'singular_name' => _x( 'Widget Content', 'churchpack' ),
        'add_new' => _x( 'Add New', 'churchpack' ),
        'add_new_item' => _x( 'Add New Widget Content', 'churchpack' ),
        'edit_item' => _x( 'Edit Widget Content', 'churchpack' ),
        'new_item' => _x( 'New Widget Content', 'churchpack' ),
        'view_item' => _x( 'View Widget Content', 'churchpack' ),
        'search_items' => _x( 'Search Widget Content', 'churchpack' ),
        'not_found' => _x( 'No widget content found', 'churchpack' ),
        'not_found_in_trash' => _x( 'No widget content found in Trash', 'churchpack' ),
        'parent_item_colon' => _x( 'Parent Widget Content:', 'churchpack' ),
        'menu_name' => _x( 'Widget Content', 'churchpack' ),
    );

    $args = array( 
        'labels' => $labels,
        'description' => 'Allows specific content to be added to widget areas',
        'menu_icon' => plugins_url('/img/notebooks.png', __FILE__),
        'show_in_nav_menus' => false,
        'can_export' => true,
        'public' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title','editor','revisions','thumbnail','author')
    );

    register_post_type( 'wpfc_widget_content', $args );
}
// End Register Post Type

// Widget Content Widget
// First create the widget for the admin panel
class wpfc_content_widget extends WP_Widget {
	function wpfc_content_widget() {
		$widget_ops = array( 'description' => __( 'Displays custom post content in a widget', 'churchpack' ) );
		$this->WP_Widget( 'wpfc_content_widget', __( 'Widget Content', 'churchpack' ), $widget_ops );
	}

	function form( $instance ) {
		$custom_post_id = ''; // Initialize the variable
		if (isset($instance['custom_post_id'])) {
			$custom_post_id = esc_attr($instance['custom_post_id']);
		};
		$show_custom_post_title  = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : true;
		$show_featured_image  = isset( $instance['show_featured_image'] ) ? $instance['show_featured_image'] : true;
		$apply_content_filters  = isset( $instance['apply_content_filters'] ) ? $instance['apply_content_filters'] : true;
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'custom_post_id' ); ?>"> <?php echo __( 'Widget Content to Display:', 'churchpack' ) ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'custom_post_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_post_id' ); ?>">
				<?php
					$args = array('post_type' => 'wpfc_widget_content', 'suppress_filters' => 0, 'numberposts' => -1, 'order' => 'ASC');
					$content_block = get_posts( $args );
					if ($content_block) {
						foreach( $content_block as $content_block ) : setup_postdata( $content_block );
							echo '<option value="' . $content_block->ID . '"';
							if( $custom_post_id == $content_block->ID ) {
								echo ' selected';
								$widgetExtraTitle = $content_block->post_title;
							};
							echo '>' . $content_block->post_title . '</option>';
						endforeach;
					} else {
						echo '<option value="">' . __( 'No widget content available', 'churchpack' ) . '</option>';
					};
				?>
				</select>
			</label>
		</p>
		
		<input type="hidden" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $widgetExtraTitle ?>" />

		<p>
			<?php
				echo '<a href="post.php?post=' . $custom_post_id . '&action=edit">' . __( 'Edit Widget Content', 'churchpack' ) . '</a>' ;
			?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) isset( $instance['show_custom_post_title'] ), true ); ?> id="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>" name="<?php echo $this->get_field_name( 'show_custom_post_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>"><?php echo __( 'Show Post Title', 'churchpack' ) ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) isset( $instance['show_featured_image'] ), true ); ?> id="<?php echo $this->get_field_id( 'show_featured_image' ); ?>" name="<?php echo $this->get_field_name( 'show_featured_image' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_featured_image' ); ?>"><?php echo __( 'Show featured image', 'churchpack' ) ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) isset( $instance['apply_content_filters'] ), true ); ?> id="<?php echo $this->get_field_id( 'apply_content_filters' ); ?>" name="<?php echo $this->get_field_name( 'apply_content_filters' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'apply_content_filters' ); ?>"><?php echo __( 'Do not apply content filters', 'churchpack' ) ?></label>
		</p> <?php 
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['custom_post_id'] = strip_tags( $new_instance['custom_post_id'] );
		$instance['show_custom_post_title'] = $new_instance['show_custom_post_title'];
		$instance['show_featured_image'] = $new_instance['show_featured_image'];
		$instance['apply_content_filters'] = $new_instance['apply_content_filters'];
		return $instance;
	}

	// Display the content block content in the widget area
	function widget($args, $instance) {
		extract($args);
		$custom_post_id  = ( $instance['custom_post_id'] != '' ) ? esc_attr($instance['custom_post_id']) : __('Find', 'churchpack');
		// Variables from the widget settings.
		$show_custom_post_title = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : false;
		$show_featured_image  = isset($instance['show_featured_image']) ? $instance['show_featured_image'] : false;
		$apply_content_filters  = isset($instance['apply_content_filters']) ? $instance['apply_content_filters'] : false;
		$content_post = get_post($custom_post_id);
		$content = $content_post->post_content;
		if ( !$apply_content_filters ) { // Don't apply the content filter if checkbox selected
			$content = apply_filters('the_content', $content);
		}
		echo $before_widget;
		if ( $show_custom_post_title ) {
			echo $before_title . apply_filters('widget_title',$content_post->post_title) . $after_title; // This is the line that displays the title (only if show title is set) 
		}
		if ( $show_featured_image ) {
			echo get_the_post_thumbnail($content_post->ID);
		} 
		echo do_shortcode($content); // This is where the actual content of the custom post is being displayed
		echo $after_widget;
	}
}

 // class wpfc_widget_widget

add_action( 'widgets_init', 'wpfc_widget_content_load_widgets' );
function wpfc_widget_content_load_widgets() {
	register_widget( 'wpfc_content_widget' );
}

// Add the ability to display the content block in a reqular post using a shortcode
function wpfc_widget_content_shortcode($atts) {
	extract(shortcode_atts(array(
		'id' => '',
		'class' => 'widget_content'
	), $atts));
	
	$content = "";
	
	if($id != "") {
		$args = array(
			'post__in' => array($id),
			'post_type' => 'widget_content',
		);
		
		$content_post = get_posts($args);
		
		foreach( $content_post as $post ) :
			$content .= '<div class="'. esc_attr($class) .'">';
			$content .= apply_filters('the_content', $post->post_content);
			$content .= '</div>';
		endforeach;
	}
	
	return $content;
}
add_shortcode('widget_content', 'wpfc_widget_content_shortcode');
// End Widget Content
?>