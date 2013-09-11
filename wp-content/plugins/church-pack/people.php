<?php

// Register Post Type
add_action( 'init', 'register_cpt_wpfc_person' );

function register_cpt_wpfc_person() {

    $labels = array( 
        'name' => _x( 'People', 'wpfc_person' ),
        'singular_name' => _x( 'Person', 'wpfc_person' ),
        'add_new' => _x( 'Add New', 'wpfc_person' ),
        'add_new_item' => _x( 'Add New Person', 'wpfc_person' ),
        'edit_item' => _x( 'Edit Person', 'wpfc_person' ),
        'new_item' => _x( 'New Person', 'wpfc_person' ),
        'view_item' => _x( 'View Person', 'wpfc_person' ),
        'search_items' => _x( 'Search People', 'wpfc_person' ),
        'not_found' => _x( 'No people found', 'wpfc_person' ),
        'not_found_in_trash' => _x( 'No people found in Trash', 'wpfc_person' ),
        'parent_item_colon' => _x( 'Parent Person:', 'wpfc_person' ),
        'menu_name' => _x( 'People', 'wpfc_person' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Add people to display on your church site',
        'supports' => array( 'title' ),
        'taxonomies' => array( 'wpfc_people_category' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        'menu_icon' => plugins_url('/img/user-silhouette.png', __FILE__),
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'wpfc_person', $args );
}
// End Register Post Type

// Register Taxonomy
add_action( 'init', 'register_taxonomy_wpfc_person_category' );

function register_taxonomy_wpfc_person_category() {

    $labels = array( 
        'name' => _x( 'People Categories', 'churchpack' ),
        'singular_name' => _x( 'People Category', 'churchpack' ),
        'search_items' => _x( 'Search People Categories', 'churchpack' ),
        'popular_items' => _x( 'Popular People Categories', 'churchpack' ),
        'all_items' => _x( 'All People Categories', 'churchpack' ),
        'parent_item' => _x( 'Parent People Category', 'churchpack' ),
        'parent_item_colon' => _x( 'Parent People Category:', 'churchpack' ),
        'edit_item' => _x( 'Edit People Category', 'churchpack' ),
        'update_item' => _x( 'Update People Category', 'churchpack' ),
        'add_new_item' => _x( 'Add New People Category', 'churchpack' ),
        'new_item_name' => _x( 'New People Category Name', 'churchpack' ),
        'separate_items_with_commas' => _x( 'Separate people categories with commas', 'churchpack' ),
        'add_or_remove_items' => _x( 'Add or remove people categories', 'churchpack' ),
        'choose_from_most_used' => _x( 'Choose from the most used people categories', 'churchpack' ),
        'menu_name' => _x( 'People Categories', 'churchpack' ),
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

    register_taxonomy( 'wpfc_person_category', array('wpfc_person'), $args );
}
// End Register Taxonomy

// Meta Box
add_filter( 'cpmb_meta_boxes', 'wpfc_person_metaboxes' );

// Define the metabox and field configurations.
function wpfc_person_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_wpfc_';

	$meta_boxes[] = array(
		'id'         => 'person_metabox',
		'title'      => 'Person Details',
		'pages'      => array( 'wpfc_person', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => 'Photo',
				'desc' => 'Upload an image or enter an URL. This image will be used in exact size you select (no more than 300px wide is best)',
				'id'   => $prefix . 'person_image',
				'type' => 'file',
			),
			array(
				'name' => 'Position/Title',
				'desc' => 'Enter the position or title for this person',
				'id'   => $prefix . 'person_title',
				'type' => 'text',
			),
			array(
				'name' => 'Bio',
				'desc' => 'Type a brief biography about this person',
				'id'   => $prefix . 'person_bio',
				'type' => 'wysiwyg',
				'options' => array(	'textarea_rows' => 5, 'media_buttons' => false,),
			),
			array(
				'name' => 'Email',
				'desc' => 'Enter the email address',
				'id'   => $prefix . 'person_email',
				'type' => 'text',
			),
			array(
				'name' => 'Personal Blog',
				'desc' => 'Enter the full url for the website',
				'id'   => $prefix . 'person_blog',
				'type' => 'text',
			),
			array(
				'name' => 'Facebook',
				'desc' => 'Enter the full url for the profile',
				'id'   => $prefix . 'person_fb',
				'type' => 'text',
			),
			array(
				'name' => 'Twitter',
				'desc' => 'Enter the full url for the profile',
				'id'   => $prefix . 'person_twitter',
				'type' => 'text',
			),
		),
	);

	return $meta_boxes;
}

add_action( 'init', 'initialize_wpfc_person_meta_boxes', 9999 );

// Initialize the metabox class.
function initialize_wpfc_person_meta_boxes() {

	if ( ! class_exists( 'cpmb_Meta_Box' ) )
		require_once 'init.php';
		
}
// End Meta Box

// Output the details
function wpfc_person_details ($post_ID) {
$person_title = get_post_meta($post_ID, '_wpfc_person_title', true);
$person_image = get_post_meta($post_ID, '_wpfc_person_image', true);
$person_bio = get_post_meta($post_ID, '_wpfc_person_bio', true);
$person_email = get_post_meta($post_ID, '_wpfc_person_email', true);
$person_blog = get_post_meta($post_ID, '_wpfc_person_blog', true);
$person_fb = get_post_meta($post_ID, '_wpfc_person_fb', true);
$person_twitter = get_post_meta($post_ID, '_wpfc_person_twitter', true);
?>
<div id="church-pack" class="clearfix">
<?php if ( $person_image ) : ?>
<div class="person-image">
	<img src="<?php echo $person_image; ?>" alt="<?php the_title(); ?>" class="alignleft" />
</div>
<?php endif; ?>
<h2 class="churchpack-title"><?php the_title(); ?></h2> 
<h3 class="person-title"><?php echo $person_title; ?></h3>

<div class="cp-social clearfix">
	<ul>
		<?php if ( $person_email ) : ?>
			<li><a class="cp-email" href="mailto:<?php echo antispambot( $person_email ); ?>" title="<?php esc_attr_e( 'Email', 'churchpack' ); ?>"><span><?php _e( 'Email', 'churchpack' ); ?></span></a></li>
		<?php endif; ?>

		<?php if ( $person_blog ) : ?>
			<li><a class="cp-blog" href="<?php echo esc_url( $person_blog ); ?>" title="<?php esc_attr_e( 'Blog', 'churchpack' ); ?>"><span><?php _e( 'Blog', 'churchpack' ); ?></span></a></li>
		<?php endif; ?>
						
		<?php if ( $person_fb ) : ?>
			<li><a class="cp-facebook" href="<?php echo esc_url( $person_fb ); ?>" title="<?php esc_attr_e( 'Facebook', 'churchpack' ); ?>"><span><?php _e( 'Facebook', 'churchpack' ); ?></span></a></li>
		<?php endif; ?>

		<?php if ( $person_twitter ) : ?>
			<li><a class="cp-twitter" href="<?php echo esc_url( $person_twitter ); ?>" title="<?php esc_attr_e( 'Twitter', 'churchpack' ); ?>"><span><?php _e( 'Twitter', 'churchpack' ); ?></span></a></li>
		<?php endif; ?>
	</ul>
</div><!-- .cp-social -->

<div class="person-bio"><?php echo wpautop($person_bio); ?></div>

</div>
<?php
}


// Shortcode
add_shortcode('people', 'wpfc_display_people_shortcode');
function wpfc_display_people_shortcode($atts) {

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
		'post_type' => 'wpfc_person',
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
			<?php wpfc_person_details($post->ID); ?>							
			<?php //echo the_terms( $post->ID, 'wpfc_person_category', '', ', ', ' ' ); ?>
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

// Load Event Data to Post Content and Excerpt
add_filter('the_content', 'add_wpfc_person_content');
add_filter('the_excerpt', 'add_wpfc_person_excerpt');
	
function add_wpfc_person_content($content) {
	if ( 'wpfc_person' == get_post_type() ){
		$new_content = get_wpfc_person();
		$content = $new_content;	
	}	
	return $content;
}
	
function add_wpfc_person_excerpt($content)
	{
	  global $post;
	 
	  if ($post->ID)
	  {
	    $p_type= get_post_type($post->ID);
	    if ($p_type == 'wpfc_person')
	    {
			$new_content = get_wpfc_person();
			$content = $new_content;	
	    }
	  }
	 
	  return $content;
}

function get_wpfc_person($content = '') {
	global $post;
	ob_start(); 
		wpfc_person_details(get_the_ID());
	$buffer = ob_get_clean();
	return $buffer;
}
	
?>