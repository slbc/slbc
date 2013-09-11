<?php
// Register Post Type
add_action( 'init', 'register_cpt_wpfc_photo_album' );

function register_cpt_wpfc_photo_album() {

    $labels = array( 
        'name' => _x( 'Photo Albums', 'wpfc_photo_album' ),
        'singular_name' => _x( 'Photo Album', 'wpfc_photo_album' ),
        'add_new' => _x( 'Add New', 'wpfc_photo_album' ),
        'add_new_item' => _x( 'Add New Photo Album', 'wpfc_photo_album' ),
        'edit_item' => _x( 'Edit Photo Album', 'wpfc_photo_album' ),
        'new_item' => _x( 'New Photo Album', 'wpfc_photo_album' ),
        'view_item' => _x( 'View Photo Album', 'wpfc_photo_album' ),
        'search_items' => _x( 'Search Photo Albums', 'wpfc_photo_album' ),
        'not_found' => _x( 'No photo albums found', 'wpfc_photo_album' ),
        'not_found_in_trash' => _x( 'No photo albums found in Trash', 'wpfc_photo_album' ),
        'parent_item_colon' => _x( 'Parent Photo Album:', 'wpfc_photo_album' ),
        'menu_name' => _x( 'Photo Albums', 'wpfc_photo_album' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Easily Add Photo Albums to your site',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies' => array( 'wpfc_album_category' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        'menu_icon' => plugins_url('/img/pictures-stack.png', __FILE__),
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'wpfc_photo_album', $args );
}
// End Register Post Type

// Register taxonomy
add_action( 'init', 'register_taxonomy_wpfc_photo_album_category' );

function register_taxonomy_wpfc_photo_album_category() {

    $labels = array( 
        'name' => _x( 'Photo Album Categories', 'churchpack' ),
        'singular_name' => _x( 'Photo Album Category', 'churchpack' ),
        'search_items' => _x( 'Search Photo Album Categories', 'churchpack' ),
        'popular_items' => _x( 'Popular Photo Album Categories', 'churchpack' ),
        'all_items' => _x( 'All Photo Album Categories', 'churchpack' ),
        'parent_item' => _x( 'Parent Photo Album Category', 'churchpack' ),
        'parent_item_colon' => _x( 'Parent Photo Album Category:', 'churchpack' ),
        'edit_item' => _x( 'Edit Photo Album Category', 'churchpack' ),
        'update_item' => _x( 'Update Photo Album Category', 'churchpack' ),
        'add_new_item' => _x( 'Add New Photo Album Category', 'churchpack' ),
        'new_item_name' => _x( 'New Photo Album Category Name', 'churchpack' ),
        'separate_items_with_commas' => _x( 'Separate photo album categories with commas', 'churchpack' ),
        'add_or_remove_items' => _x( 'Add or remove photo album categories', 'churchpack' ),
        'choose_from_most_used' => _x( 'Choose from the most used photo album categories', 'churchpack' ),
        'menu_name' => _x( 'Photo Album Categories', 'churchpack' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'hierarchical' => true,

        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'wpfc_photo_album_category', array('wpfc_photo_album'), $args );
}
// End Register taxonomy


// Load Scripts
function churchpack_album_header() {
	wp_enqueue_script('jquery');
	wp_register_script('pretty_photo', plugins_url('/js/jquery.prettyPhoto.js', __FILE__) );
	wp_enqueue_script('pretty_photo' );
	wp_register_style('pretty_photo', plugins_url('/css/prettyPhoto.css', __FILE__) ); 
	wp_enqueue_style( 'pretty_photo' );
	wp_register_script('cp_gallery', plugins_url('/js/jquery.gallery.js', __FILE__) );
	wp_enqueue_script('cp_gallery' );
}
add_action( 'wp_head', 'churchpack_album_header' );

// Enable Pretty Photo for galleries
function churchpack_prettyadd ($content) {
	$content = preg_replace("/<a/","<a rel=\"prettyPhoto[slides]\"",$content,1);
	return $content;
}
add_filter( 'wp_get_attachment_link', 'churchpack_prettyadd');

// Add Metabox
function wpfc_gallery_metabox_add() {
	add_meta_box( 'wpfc_gallery_metabox', __( 'Gallery Images', 'churchpack' ), 'wpfc_gallery_metabox', 'wpfc_photo_album', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wpfc_gallery_metabox_add' );

// Build the Metabox
function wpfc_gallery_metabox( $post ) {
	
	$original_post = $post;
	
	$args = array(
		'post_type' => 'attachment',
		'post_status' => 'inherit',
		'post_parent' => $post->ID,
		'post_mime_type' => 'image',
		'posts_per_page' => '-1',
		'order' => 'ASC',
		'orderby' => 'menu_order',
	);

	$intro = '<p><a href="media-upload.php?post_id=' . $post->ID .'&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=715" id="add_image" class="thickbox" title="' . __( 'Add Image', 'gallery-metabox' ) . '">' . __( 'Upload Images', 'gallery-metabox' ) . '</a> | <a href="media-upload.php?post_id=' . $post->ID .'&amp;type=image&amp;tab=gallery&amp;TB_iframe=1&amp;width=640&amp;height=715" id="manage_gallery" class="thickbox" title="' . __( 'Manage Gallery', 'gallery-metabox' ) . '">' . __( 'Manage Gallery', 'gallery-metabox' ) . '</a></p>';
	echo apply_filters( 'be_gallery_metabox_intro', $intro );

	$loop = new WP_Query( $args );
	if( !$loop->have_posts() )
		echo '<p>No images.</p>';
			
	while( $loop->have_posts() ): $loop->the_post(); global $post;
		$thumbnail = wp_get_attachment_image_src( $post->ID, 'thumbnail' );
		echo '<img src="' . $thumbnail[0] . '" alt="' . get_the_title() . '" title="' . get_the_content() . '" /> ';
	endwhile; 
	
	$post = $original_post;
}

// End Add Metabox

// Shortcode
add_shortcode('albums', 'wpfc_display_albums_shortcode');
function wpfc_display_albums_shortcode($atts) {

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
	// begin - code from : http://wordpress.org/support/topic/wp-pagenavi-with-custom-query-and-paged-variable?replies=2
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
	// - end
	$args = array(
		'post_type' => 'wpfc_photo_album',
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

		<div class="churchpack-album">	
			<?php if(has_post_thumbnail()) : ?>
				<div class="churchpack-thumb">
					<a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>"><?php the_post_thumbnail('churchpack_tall', array('class' => 'churchpack-thumb alignleft', 'alt' => ''.get_the_title().'', 'title' => ''.get_the_title().'')); ?></a>		    	
				</div>
			<?php endif; ?>
			
			<a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>" title="<?php the_title(); ?>"><h3 class="churchpack-title"><?php the_title(); ?></h3></a>
			<?php the_excerpt(); ?>							
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

// Load Album Data to Post Content and Excerpt
add_filter('the_content', 'add_wpfc_album_content');
	
function add_wpfc_album_content($content)
	{
	  global $post;
	 
	  if ($post->ID)
	  {
	    $p_type= get_post_type($post->ID);
	    if ($p_type == 'wpfc_photo_album')
	    {
	      $content .= get_wpfc_album();
	    }
	  }
	 
	  return $content;
}
	

function get_wpfc_album($content = '') {
	global $post;
	ob_start(); ?>
	<div class="album-data">
		 <?php
			$gallery = '[gallery link="file" columns="3"]';
			echo $gallery;
		?>
	</div>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}
?>