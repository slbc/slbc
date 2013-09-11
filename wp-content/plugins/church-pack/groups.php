<?php

// Register Post Type
add_action( 'init', 'register_cpt_wpfc_group' );

function register_cpt_wpfc_group() {

    $labels = array( 
        'name' => _x( 'Groups', 'churchpack' ),
        'singular_name' => _x( 'Group', 'churchpack' ),
        'add_new' => _x( 'Add New', 'churchpack' ),
        'add_new_item' => _x( 'Add New Group', 'churchpack' ),
        'edit_item' => _x( 'Edit Group', 'churchpack' ),
        'new_item' => _x( 'New Group', 'churchpack' ),
        'view_item' => _x( 'View Group', 'churchpack' ),
        'search_items' => _x( 'Search Groups', 'churchpack' ),
        'not_found' => _x( 'No groups found', 'churchpack' ),
        'not_found_in_trash' => _x( 'No groups found in Trash', 'churchpack' ),
        'parent_item_colon' => _x( 'Parent Group:', 'churchpack' ),
        'menu_name' => _x( 'Groups', 'churchpack' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Groups are to publish information about ministries, sunday school classes, or any type of group at your church',
        'supports' => array( 'title' ),
        'taxonomies' => array( 'wpfc_group_category' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        'menu_icon' => plugins_url('/img/block.png', __FILE__),
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'wpfc_group', $args );
}
// End Register Post Type

// Register taxonomy
add_action( 'init', 'register_taxonomy_wpfc_group_category' );

function register_taxonomy_wpfc_group_category() {

    $labels = array( 
        'name' => _x( 'Group Categories', 'churchpack' ),
        'singular_name' => _x( 'Group Category', 'churchpack' ),
        'search_items' => _x( 'Search Group Categories', 'churchpack' ),
        'popular_items' => _x( 'Popular Group Categories', 'churchpack' ),
        'all_items' => _x( 'All Group Categories', 'churchpack' ),
        'parent_item' => _x( 'Parent Group Category', 'churchpack' ),
        'parent_item_colon' => _x( 'Parent Group Category:', 'churchpack' ),
        'edit_item' => _x( 'Edit Group Category', 'churchpack' ),
        'update_item' => _x( 'Update Group Category', 'churchpack' ),
        'add_new_item' => _x( 'Add New Group Category', 'churchpack' ),
        'new_item_name' => _x( 'New Group Category Name', 'churchpack' ),
        'separate_items_with_commas' => _x( 'Separate group categories with commas', 'churchpack' ),
        'add_or_remove_items' => _x( 'Add or remove group categories', 'churchpack' ),
        'choose_from_most_used' => _x( 'Choose from the most used group categories', 'churchpack' ),
        'menu_name' => _x( 'Group Categories', 'churchpack' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => false,
        'show_ui' => true,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'wpfc_group_category', array('wpfc_group'), $args );
}
// End Register taxonomy

// Change Messages
add_filter('post_updated_messages', 'wpfc_group_updated_messages');
function wpfc_group_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['wpfc_group'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Group updated. <a href="%s">View event</a>', 'churchpack'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.', 'churchpack'),
    3 => __('Custom field deleted.', 'churchpack'),
    4 => __('Group updated.', 'churchpack'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Group restored to revision from %s', 'churchpack'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Group published. <a href="%s">View group</a>', 'churchpack'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Group saved.'),
    8 => sprintf( __('Group submitted. <a target="_blank" href="%s">Preview group</a>', 'churchpack'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Group scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'churchpack'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i', 'churchpack' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Group draft updated. <a target="_blank" href="%s">Preview group</a>', 'churchpack'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
// End Change Messages

// Meta Box
add_filter( 'cpmb_meta_boxes', 'wpfc_group_metaboxes' );

// Define the metabox and field configurations.
function wpfc_group_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_wpfc_';

	$meta_boxes[] = array(
		'id'         => 'group_metabox',
		'title'      => 'Group Details',
		'pages'      => array( 'wpfc_group', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => 'Image',
				'desc' => 'Upload an image or enter an URL. This image will be used in exact size you select.',
				'id'   => $prefix . 'group_image',
				'type' => 'file',
			),
			array(
				'name' => 'Description',
				'desc' => 'Type a brief description about this group (what the purpose is, when and where you meet, etc.)',
				'id'   => $prefix . 'group_desc',
				'type' => 'wysiwyg',
				'options' => array(	'textarea_rows' => 5, 'media_buttons' => false,),
			),
			array(
				'name' => 'Location',
				'desc' => 'Enter the name of the meeting place (Room 300, etc.)',
				'id'   => $prefix . 'group_location',
				'type' => 'text',
			),
			array(
				'name' => 'Address',
				'desc' => 'Enter the address of the meeting place if it is offsite to display a map (e.g., 456 E. Broad St, Billings, MT 85555)',
				'id'   => $prefix . 'group_address',
				'type' => 'text',
			),
			array(
				'name' => 'Leader',
				'desc' => 'Enter the leader for this group',
				'id'   => $prefix . 'group_leader',
				'type' => 'text',
			),
			array(
				'name' => 'Phone',
				'desc' => 'Enter the phone number of the group leader',
				'id'   => $prefix . 'group_leader_phone',
				'type' => 'text',
			),
			array(
				'name' => 'Email',
				'desc' => 'Enter the email address of the group leader',
				'id'   => $prefix . 'group_leader_email',
				'type' => 'text',
			),
		),
	);

	return $meta_boxes;
}

add_action( 'init', 'initialize_wpfc_group_meta_boxes', 9999 );

// Initialize the metabox class.
function initialize_wpfc_group_meta_boxes() {

	if ( ! class_exists( 'cpmb_Meta_Box' ) )
		require_once 'init.php';
		
}
// End Meta Box

// Output the excerpt
function wpfc_group_excerpt ($post_ID) {
$group_image = get_post_meta($post_ID, '_wpfc_group_image', true);
$group_desc = get_post_meta($post_ID, '_wpfc_group_desc', true);
?>
<div id="church-pack">
	<h2 class="group-title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
	<?php if ( $group_image ) : ?>
		<div class="group-image">
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>">
				<img src="<?php echo $group_image; ?>" alt="<?php the_title(); ?>" class="alignleft" />
			</a>
		</div>
	<?php endif; ?>
	
	<div class="group-desc"><?php echo wpautop($group_desc); ?></div>
</div>
<?php
}

// Output the details
function wpfc_group_details ($post_ID) {
$group_image = get_post_meta($post_ID, '_wpfc_group_image', true);
$group_desc = get_post_meta($post_ID, '_wpfc_group_desc', true);
$group_location = get_post_meta($post_ID, '_wpfc_group_location', true);
$group_address = get_post_meta($post_ID, '_wpfc_group_address', true);
$group_leader = get_post_meta($post_ID, '_wpfc_group_leader', true);
$group_leader_phone = get_post_meta($post_ID, '_wpfc_group_leader_phone', true);
$group_leader_email = get_post_meta($post_ID, '_wpfc_group_leader_email', true);
?>
<div id="church-pack">
	<?php if ( $group_image ) : ?>
		<div class="group-image">
			<img src="<?php echo $group_image; ?>" alt="<?php the_title(); ?>" class="alignleft" />
		</div>
	<?php endif; ?>
	
	<div class="group-desc"><?php echo wpautop($group_desc); ?></div>
	
	<div class="cp-group-details clearfix">
	<p>
		<?php if ( $group_leader ) : ?>
			<?php _e( 'Leader: ', 'churchpack' ); ?><?php echo $group_leader; ?><br/>
		<?php endif; ?>
		
		<?php if ( $group_leader_phone ) : ?>
			<?php _e( 'Leader Phone: ', 'churchpack' ); ?><?php echo $group_leader_phone; ?><br/>
		<?php endif; ?>
						
		<?php if ( $group_leader_email ) : ?>
			<a href="mailto:<?php echo antispambot( $group_leader_email ); ?>" title="<?php esc_attr_e( 'Email', 'churchpack' ); ?>"><?php _e( 'Email', 'churchpack' ); ?></a><br/>
		<?php endif; ?>

		<?php if ( $group_location ) : ?>
			<?php _e( 'Location: ', 'churchpack' ); ?><?php echo $group_location; ?><br/>
		<?php endif; ?>
		
		<?php if ( $group_address ) : 
			echo do_shortcode('[cp-map address=" ' . $group_address . ' "]');
		endif; ?>
	</p>
	</div><!-- .cp-group-details -->

</div><!-- #church-pack -->
<?php
}


// Shortcode
add_shortcode('group', 'wpfc_display_group_shortcode');
function wpfc_display_group_shortcode($atts) {

	// Pull in shortcode attributes and set defaults
	extract( shortcode_atts( array(
		'id' => false,
		'posts_per_page' => '10',
		'order' => 'DESC',
		'hide_nav' => false,
		'taxonomy' => false,
		'tax_term' => false,
		'tax_operator' => 'IN'
	), $atts ) );
	// begin - pagination
		global $paged;
		if( get_query_var( 'paged' ) )
			$my_page = get_query_var( 'paged' );
		else {
		if( get_query_var( 'page' ) )
			$my_page = get_query_var( 'page' );
		else
			$my_page = 1;
		set_query_var( 'paged', $my_page );
		$paged = $my_page;
		}
	// end - pagination
	$args = array(
		'post_type' => 'wpfc_group',
		'posts_per_page' => $posts_per_page,
		'order' => $order,
		'paged' => $my_page,
	);
	
	// If Post IDs
	if( $id ) {
		$posts_in = explode( ',', $id );
		$args['post__in'] = $posts_in;
	}
	
	// If taxonomy attributes, create a taxonomy query
	if ( !empty( $taxonomy ) && !empty( $tax_term ) ) {
	
		// Term string to array
		$tax_term = explode( ', ', $tax_term );
		
		// Validate operator
		if( !in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) )
			$tax_operator = 'IN';
					
		$tax_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $tax_term,
					'operator' => $tax_operator
				)
			)
		);
		$args = array_merge( $args, $tax_args );
	}
	
	$listing = new WP_Query( $args, $atts ) ;
	// Now that you've run the query, finish populating the object
	ob_start(); ?>
	<div id="wpfc_churchpack">	
	<div id="wpfc_churchpack_loading">
	<?php if(function_exists(wp_pagenavi)) : ?>
	<div id="churchpack-navigation"> 
	<?php wp_pagenavi( array( 'query' => $listing ) ); ?>
	</div>
	<?php
	endif;
	if ( !$listing->have_posts() )
		return;
	$inner = '';
	while ( $listing->have_posts() ): $listing->the_post(); global $post; ?>

		<div class="churchpack-people">	
			<?php wpfc_group_excerpt($post->ID); ?>							
			<?php //echo the_terms( $post->ID, 'wpfc_group_category', '', ', ', ' ' ); ?>
		</div>
			
	<?php
	endwhile; //end loop
	if(function_exists(wp_pagenavi)) : ?>
	<div id="churchpack-navigation"> 
	<?php wp_pagenavi( array( 'query' => $listing ) ); ?>
	</div>
	<?php
	endif;
	wp_reset_query();
	?>
	</div>
	</div>
	<?php
	
	$buffer = ob_get_clean();
	return $buffer;

}

// End Shortcode

// Load Data to Post Content and Excerpt
add_filter('the_content', 'add_wpfc_group_content');
add_filter('the_excerpt', 'add_wpfc_group_excerpt');
	
function add_wpfc_group_content($content) {
	if ( 'wpfc_group' == get_post_type() ){
		$new_content = get_wpfc_group();
		$content = $new_content;	
	}	
	return $content;
}
	
function add_wpfc_group_excerpt($content)
	{
	  global $post;
	 
	  if ($post->ID)
	  {
	    $p_type= get_post_type($post->ID);
	    if ($p_type == 'wpfc_group')
	    {
			$new_content = get_wpfc_group();
			$content = $new_content;	
	    }
	  }
	 
	  return $content;
}

function get_wpfc_group($content = '') {
	global $post;
	ob_start(); 
		wpfc_group_details(get_the_ID());
	$buffer = ob_get_clean();
	return $buffer;
}

?>