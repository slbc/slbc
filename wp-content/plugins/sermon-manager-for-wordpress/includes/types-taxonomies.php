<?php
/*
 * Creation of Sermon Post Types and Taxonomies
 * Also all meta boxes
 */
 
// Determine the correct slug name based on options
function generate_wpfc_slug($slug_name = NULL) {
    $sermon_settings = get_option('wpfc_options');
    $archive_slug = $sermon_settings['archive_slug'];
    if(empty($archive_slug))
    {
    	$archive_slug = 'sermons';
    }

    if (!isset($slug_name)) {
    	return array ('slug' => $archive_slug, 'with_front' => false);
    }

    if (isset($sermon_settings['common_base_slug']) == '1') {
    	return array ('slug' => $archive_slug."/".$slug_name, 'with_front' => false);
    } else {
    	return array ('slug' => $slug_name, 'with_front' => false);
    }
}

 // Create sermon Custom Post Type
add_action('init', 'create_wpfc_sermon_types');
function create_wpfc_sermon_types() {
  
  $labels = array(
    'name' => _x( 'Sermons', 'sermon-manager'),
    'singular_name' => _x( 'Sermon', 'sermon-manager'),
    'add_new' => _x( 'Add New', 'sermon-manager'),
    'add_new_item' => _x('Add New Sermon', 'sermon-manager'),
    'edit_item' => _x('Edit Sermon', 'sermon-manager'),
    'new_item' => _x('New Sermon', 'sermon-manager'),
    'view_item' => _x('View Sermon', 'sermon-manager'),
    'search_items' => _x('Search Sermons', 'sermon-manager'),
    'not_found' =>  _x('No sermons found', 'sermon-manager'),
    'not_found_in_trash' => _x('No sermons found in Trash', 'sermon-manager'), 
    'parent_item_colon' => '',
    'menu_name' => _x( 'Sermons', 'sermon-manager'),
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'menu_icon' => plugins_url('/img/book-open-bookmark.png', __FILE__),
	'capability_type' => 'post',
    'has_archive' => true, 
    'rewrite' => generate_wpfc_slug(),
    'hierarchical' => false,
    'supports' => array( 'title', 'comments', 'thumbnail', 'entry-views' )
  ); 
  register_post_type('wpfc_sermon',$args);
}

//create new taxonomies: preachers, sermon series, bible books & topics
add_action( 'init', 'create_wpfc_sermon_taxonomies', 0 );
function create_wpfc_sermon_taxonomies() {

//Preachers
$labels = array(	
	'name' => _x( 'Preachers', 'sermon-manager'),
	'singular_name' => _x( 'Preacher', 'sermon-manager' ),
	'menu_name' => _x( 'Preachers', 'sermon-manager' ),
	'search_items' => _x( 'Search preachers', 'sermon-manager' ), 
	'popular_items' => _x( 'Most frequent preachers', 'sermon-manager' ), 
	'all_items' => _x( 'All preachers', 'sermon-manager' ),
	'edit_item' => _x( 'Edit preachers', 'sermon-manager' ),
	'update_item' => _x( 'Update preachers', 'sermon-manager' ), 
	'add_new_item' => _x( 'Add new preacher', 'sermon-manager' ),
	'new_item_name' => _x( 'New preacher name', 'sermon-manager' ), 
	'separate_items_with_commas' => _x( 'Separate multiple preachers with commas', 'sermon-manager' ),
	'add_or_remove_items' => _x( 'Add or remove preachers', 'sermon-manager' ),
	'choose_from_most_used' => _x( 'Choose from most frequent preachers', 'sermon-manager' ),
	'parent_item' => null,
    'parent_item_colon' => null,
);

register_taxonomy('wpfc_preacher','wpfc_sermon', array(
	'hierarchical' => false, 
	'labels' => $labels, 
	'show_ui' => true,
	'query_var' => true,
    'rewrite' => generate_wpfc_slug('preacher'),
));

//Sermon Series
$labels = array(	
	'name' => _x( 'Sermon Series', 'sermon-manager'),
	'graphic' => '',
	'singular_name' => _x( 'Sermon Series', 'sermon-manager'),
	'menu_name' => _x( 'Sermon Series', 'sermon-manager' ),
	'search_items' => _x( 'Search sermon series', 'sermon-manager' ), 
	'popular_items' => _x( 'Most frequent sermon series', 'sermon-manager' ), 
	'all_items' => _x( 'All sermon series', 'sermon-manager' ),
	'edit_item' => _x( 'Edit sermon series', 'sermon-manager' ),
	'update_item' => _x( 'Update sermon series', 'sermon-manager' ), 
	'add_new_item' => _x( 'Add new sermon series', 'sermon-manager' ),
	'new_item_name' => _x( 'New sermon series name', 'sermon-manager' ), 
	'separate_items_with_commas' => _x( 'Separate sermon series with commas', 'sermon-manager' ),
	'add_or_remove_items' => _x( 'Add or remove sermon series', 'sermon-manager' ),
	'choose_from_most_used' => _x( 'Choose from most used sermon series', 'sermon-manager' ),
	'parent_item' => null,
    'parent_item_colon' => null,
);

register_taxonomy('wpfc_sermon_series','wpfc_sermon', array(
	'hierarchical' => false, 
	'labels' => $labels, 
	'show_ui' => true,
	'query_var' => true,
    'rewrite' => generate_wpfc_slug('series'),
));

//Sermon Topics
$labels = array(	
	'name' => _x( 'Sermon Topics', 'sermon-manager'),
	'singular_name' => _x( 'Sermon Topics', 'sermon-manager'),
	'menu_name' => _x( 'Sermon Topics', 'sermon-manager' ),
	'search_items' => _x( 'Search sermon topics', 'sermon-manager' ), 
	'popular_items' => _x( 'Most popular sermon topics', 'sermon-manager' ), 
	'all_items' => _x( 'All sermon topics', 'sermon-manager' ),
	'edit_item' => _x( 'Edit sermon topic', 'sermon-manager' ),
	'update_item' => _x( 'Update sermon topic', 'sermon-manager' ), 
	'add_new_item' => _x( 'Add new sermon topic', 'sermon-manager' ),
	'new_item_name' => _x( 'New sermon topic', 'sermon-manager' ), 
	'separate_items_with_commas' => _x( 'Separate sermon topics with commas', 'sermon-manager' ),
	'add_or_remove_items' => _x( 'Add or remove sermon topics', 'sermon-manager' ),
	'choose_from_most_used' => _x( 'Choose from most used sermon topics', 'sermon-manager' ),
	'parent_item' => null,
    'parent_item_colon' => null,
);

register_taxonomy('wpfc_sermon_topics','wpfc_sermon', array(
	'hierarchical' => false, 
	'labels' => $labels, 
	'show_ui' => true,
	'query_var' => true,
    'rewrite' => generate_wpfc_slug('topics'),
));

//Books of the Bible
$labels = array(	
	'name' => _x( 'Book of the Bible', 'sermon-manager'),
	'singular_name' => _x( 'Book of the Bible', 'sermon-manager'),
	'menu_name' => _x( 'Book of the Bible', 'sermon-manager' ),
	'search_items' => _x( 'Search books of the Bible', 'sermon-manager' ), 
	'popular_items' => _x( 'Most popular books of the Bible', 'sermon-manager' ), 
	'all_items' => _x( 'All books of the Bible', 'sermon-manager' ),
	'edit_item' => _x( 'Edit book of the Bible', 'sermon-manager' ),
	'update_item' => _x( 'Update book of the Bible', 'sermon-manager' ), 
	'add_new_item' => _x( 'Add new books of the Bible', 'sermon-manager' ),
	'new_item_name' => _x( 'New book of the Bible', 'sermon-manager' ), 
	'separate_items_with_commas' => _x( 'Separate books of the Bible with commas', 'sermon-manager' ),
	'add_or_remove_items' => _x( 'Add or remove books of the Bible', 'sermon-manager' ),
	'choose_from_most_used' => _x( 'Choose from most used books of the Bible', 'sermon-manager' ),
	'parent_item' => null,
    'parent_item_colon' => null,
);

register_taxonomy('wpfc_bible_book','wpfc_sermon', array(
	'hierarchical' => false, 
	'labels' => $labels, 
	'show_ui' => true,
	'query_var' => true,
    'rewrite' => generate_wpfc_slug('book'),
));

//Service Type
$labels = array(	
	'name' => _x( 'Service Type', 'sermon-manager'),
	'singular_name' => _x( 'Service Type', 'sermon-manager'),
	'menu_name' => _x( 'Service Type', 'sermon-manager' ),
	'search_items' => _x( 'Search service types', 'sermon-manager' ), 
	'popular_items' => _x( 'Most popular service types', 'sermon-manager' ), 
	'all_items' => _x( 'All service types', 'sermon-manager' ),
	'edit_item' => _x( 'Edit service type', 'sermon-manager' ),
	'update_item' => _x( 'Update service type', 'sermon-manager' ), 
	'add_new_item' => _x( 'Add new service types', 'sermon-manager' ),
	'new_item_name' => _x( 'New Service Type', 'sermon-manager' ), 
	'separate_items_with_commas' => _x( 'Separate service types with commas', 'sermon-manager' ),
	'add_or_remove_items' => _x( 'Add or remove service types', 'sermon-manager' ),
	'choose_from_most_used' => _x( 'Choose from most used service types', 'sermon-manager' ),
	'parent_item' => null,
    'parent_item_colon' => null,
);

register_taxonomy('wpfc_service_type','wpfc_sermon', array(
	'hierarchical' => false, 
	'labels' => $labels, 
	'show_ui' => true,
	'query_var' => true,
    'rewrite' => generate_wpfc_slug('service-type'),
));
}

// Initialize the metabox class.
add_action( 'init', 'initialize_wpfc_sermon_meta_boxes', 9999 );
function initialize_wpfc_sermon_meta_boxes() {
	require_once plugin_dir_path( __FILE__ ) . '/meta-box/init.php';	
}

add_filter( 'wpfc_meta_boxes', 'wpfc_sermon_metaboxes' );
// Define the metabox and field configurations.
function wpfc_sermon_metaboxes( array $meta_boxes ) {

	// Service Types
	$service_types = array(
					array( 'name' => 'Adult Bible Class', 'value' => 'Adult Bible Class', ),
					array( 'name' => 'Sunday AM', 'value' => 'Sunday AM', ),
					array( 'name' => 'Sunday PM', 'value' => 'Sunday PM', ),
					array( 'name' => 'Midweek Service', 'value' => 'Midweek Service', ),
					array( 'name' => 'Special Service', 'value' => 'Special Service', ),
					array( 'name' => 'Radio Broadcast', 'value' => 'Radio Broadcast', ),);	
	$service_types = apply_filters('service_types', $service_types);
	
	$meta_boxes[] = array(
		'id'         => 'wpfc_sermon_details',
		'title'      => __('Sermon Details', 'sermon-manager'),
		'pages'      => array( 'wpfc_sermon', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => __('Date', 'sermon-manager'),
				'desc' => __('Enter the date the sermon was given. <strong>NOTE: Each sermon must have a date!</strong>', 'sermon-manager'),
				'id'   => 'sermon_date',
				'type' => 'text_date_timestamp',
			),
			array(
				'name'    => __('Service Type', 'sermon-manager'),
				'desc'    => __('Select the type of service. Modify service types in Sermons -> Service Types.', 'sermon-manager'),
				'id'      => 'wpfc_service_type_select',
				'type'    => 'taxonomy_select',
				'taxonomy' => 'wpfc_service_type',
			),
			array(
				'name' => __('Main Bible Passage', 'sermon-manager'),
				'desc' => __('Enter the Bible passage with the full book names,e.g. "John 3:16-18".', 'sermon-manager'),
				'id'   => 'bible_passage',
				'type' => 'text',
			),
			array(
				'name' => __('Description', 'sermon-manager'),
				'desc' => __('Type a brief description about this sermon, an outline, or a full manuscript', 'sermon-manager'),
				'id'   => 'sermon_description',
				'type' => 'wysiwyg',
				'options' => array(	'textarea_rows' => 7, 'media_buttons' => false,),
			),
		),
	);

	$meta_boxes[] = array(
		'id'         => 'wpfc_sermon_files',
		'title'      => __('Sermon Files', 'sermon-manager'),
		'pages'      => array( 'wpfc_sermon', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => __('Location of MP3', 'sermon-manager'),
				'desc' => __('Upload an audio file or enter an URL.', 'sermon-manager'),
				'id'   => 'sermon_audio',
				'type' => 'file',
			),
			array(
				'name' => __('Video Embed Code', 'sermon-manager'),
				'desc' => __('Paste your embed code for Vimeo, Youtube, or other service here', 'sermon-manager'),
				'id'   => 'sermon_video',
				'type' => 'textarea',
			),
			array(
				'name' => __('Sermon Notes', 'sermon-manager'),
				'desc' => __('Upload a pdf file or enter an URL.', 'sermon-manager'),
				'id'   => 'sermon_notes',
				'type' => 'file',
			),
		),
	);
	
	return $meta_boxes;
}

//Remove service type box (since we already have a method for selecting it)
function remove_service_type_taxonomy() {
	$custom_taxonomy_slug = 'wpfc_service_type';
	$custom_post_type = 'wpfc_sermon';
	remove_meta_box('tagsdiv-wpfc_service_type', 'wpfc_sermon', 'side' );
}
add_action( 'admin_menu', 'remove_service_type_taxonomy' );

//add filter to insure the text Sermon, or sermon, is displayed when user updates a sermon
add_filter('post_updated_messages', 'wpfc_sermon_updated_messages');
function wpfc_sermon_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['wpfc_sermon'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Sermon updated. <a href="%s">View sermon</a>', 'sermon-manager'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.', 'sermon-manager'),
    3 => __('Custom field deleted.', 'sermon-manager'),
    4 => __('Sermon updated.', 'sermon-manager'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Sermon restored to revision from %s', 'sermon-manager'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Sermon published. <a href="%s">View sermon</a>', 'sermon-manager'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Sermon saved.', 'sermon-manager'),
    8 => sprintf( __('Sermon submitted. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Sermon scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview sermon</a>', 'sermon-manager'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i', 'sermon-manager' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Sermon draft updated. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

// TO DO: Add more help information
//display contextual help for Sermons
add_action( 'contextual_help', 'add_wpfc_sermon_help_text', 10, 3 );

function add_wpfc_sermon_help_text($contextual_help, $screen_id, $screen) { 
  //$contextual_help .= var_dump($screen); // use this to help determine $screen->id
  if ('wpfc_sermon' == $screen->id ) {
    $contextual_help =
      '<p>' . __('Things to remember when adding or editing a sermon:', 'sermon-manager') . '</p>' .
      '<ul>' .
      '<li>' . __('Specify a sermon series if appropriate. This will help your site visitors while browsing sermons.', 'sermon-manager') . '</li>' .
      '<li>' . __('Specify the correct preacher of the sermon.', 'sermon-manager') . '</li>' .
      '</ul>' .
      '<p>' . __('If you want to schedule the sermon to be published in the future:', 'sermon-manager') . '</p>' .
      '<ul>' .
      '<li>' . __('Under the Publish meta box, click on the Edit link next to Publish.', 'sermon-manager') . '</li>' .
      '<li>' . __('Change the date to the date to actual publish this article, then click on Ok.', 'sermon-manager') . '</li>' .
      '</ul>' .
      '<p><strong>' . __('For more help:', 'sermon-manager') . '</strong></p>' .
      '<p>' . __('<a href="http://wpforchurch.com/" target="_blank">Wordpress for Church</a>', 'sermon-manager') . '</p>' ;
  } elseif ( 'edit-sermon' == $screen->id ) {
    $contextual_help = 
      '<p>' . __('This is the help screen displaying on the sermons page.', 'sermon-manager') . '</p>' ;
  }
  return $contextual_help;
}

//create custom columns when listing sermon details in the Admin
add_action("manage_posts_custom_column", "wpfc_sermon_columns");
add_filter("manage_edit-wpfc_sermon_columns", "wpfc_sermon_edit_columns");

function wpfc_sermon_edit_columns($columns) {
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => __('Sermon Title', 'sermon-manager'),
		"preacher" => __('Preacher', 'sermon-manager'),
		"series" => __('Sermon Series', 'sermon-manager'),
		"topics" => __('Topics', 'sermon-manager'),
		"views" => __('Views', 'sermon-manager'),
	);
	return $columns;
}

function wpfc_sermon_columns($column){
	global $post;
	
	switch ($column){
		case "preacher":
			echo get_the_term_list($post->ID, 'wpfc_preacher', '', ', ','');
			break;
		case "series":
			echo get_the_term_list($post->ID, 'wpfc_sermon_series', '', ', ','');
			break;
		case "topics":
			echo get_the_term_list($post->ID, 'wpfc_sermon_topics', '', ', ','');
			break;
		case "views":
			echo wpfc_entry_views_get( array( 'post_id' => $post->ID ) );
			break;			
	}
}


// Custom taxonomy terms dropdown function
function wpfc_get_term_dropdown($taxonomy) {
	$terms = get_terms($taxonomy);
	foreach ($terms as $term) {
		$term_slug = $term->slug;
		$current_preacher = get_query_var('wpfc_preacher');
		$current_series = get_query_var('wpfc_sermon_series');
		$current_topic = get_query_var('wpfc_sermon_topics');
		$current_book = get_query_var('wpfc_bible_book');
		if($term_slug == $current_preacher || $term_slug == $current_series || $term_slug == $current_topic || $term_slug == $current_book) {
			echo '<option value="'.$term->slug.'" selected>'.$term->name.'</option>';
		} else {
			echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
		}
	}
} 

/*
Taxonomy Short Description
http://wordpress.mfields.org/plugins/taxonomy-short-description/
Shortens the description shown in the administration panels for all categories, tags and custom taxonomies.
V: 1.3.1
Copyright 2011  Michael Fields  michael@mfields.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

Function names have been modified to prevent conflicts.
*/

// Actions.
function wpfc_taxonomy_short_description_actions() {
	$taxonomies = get_taxonomies();
	foreach ( $taxonomies as $taxonomy ) {
		$config = get_taxonomy( $taxonomy );
		if ( isset( $config->show_ui ) && true == $config->show_ui ) {
			add_action( 'manage_' . $taxonomy . '_custom_column', 'wpfc_taxonomy_short_description_rows', 10, 3 );
			add_action( 'manage_edit-' . $taxonomy . '_columns',  'wpfc_taxonomy_short_description_columns' );
			add_filter( 'manage_edit-' . $taxonomy . '_sortable_columns', 'wpfc_taxonomy_short_description_columns' );
		}
	}
}
add_action( 'admin_init', 'wpfc_taxonomy_short_description_actions' );


// Term Columns.
// Remove the default "Description" column. Add a custom "Short Description" column.
function wpfc_taxonomy_short_description_columns( $columns ) {
	$position = 0;
	$iterator = 1;
	foreach( $columns as $column => $display_name ) {
		if ( 'name' == $column ) {
			$position = $iterator;
		}
		$iterator++;
	}
	if ( 0 < $position ) {
		/* Store all columns up to and including "Name". */
		$before = $columns;
		array_splice( $before, $position );

		/* All of the other columns are stored in $after. */
		$after  = $columns;
		$after = array_diff ( $columns, $before );

		/* Prepend a custom column for the short description. */
		$after = array_reverse( $after, true );
		$after['mfields_short_description'] = $after['description'];
		$after = array_reverse( $after, true );

		/* Remove the original description column. */
		unset( $after['description'] );

		/* Join all columns back together. */
		$columns = $before + $after;
	}
	return $columns;
}


// Term Rows. - Display the shortened description in each row's custom column.
function wpfc_taxonomy_short_description_rows( $string, $column_name, $term ) {
	if ( 'mfields_short_description' == $column_name ) {
		global $taxonomy;
		$string = term_description( $term, $taxonomy );
		$string = wpfc_taxonomy_short_description_shorten( $string, apply_filters( 'mfields_taxonomy_short_description_length', 130 ) );
	}
	return $string;
}

// Shorten a string to a given length.
function wpfc_taxonomy_short_description_shorten( $string, $max_length = 23, $append = '&#8230;', $encoding = 'utf8' ) {

	/* Sanitize $string. */
	$string = strip_tags( $string );
	$string = trim( $string );
	$string = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
	$string = rtrim( $string, '-' );

	/* Sanitize $max_length */
	if ( 0 == abs( (int) $max_length ) ) {
		$max_length = 23;
	}

	/* Return early if the php "mbstring" extension is not installed. */
	if ( ! function_exists( 'mb_substr' ) ) {
		$length = strlen( $string );
		if ( $length > $max_length ) {
			return substr_replace( $string, $append, $max_length );
		}
		return $string;
	}

	/* Count how many characters are in the string. */
	$length = strlen( utf8_decode( $string ) );

	/* String is longer than max-length. It needs to be shortened. */
	if ( $length > $max_length ) {

		/* Shorten the string to max-length */
		$short = mb_substr( $string, 0, $max_length, $encoding );

		/*
		 * A word has been cut in half during shortening.
		 * If the shortened string contains more than one word
		 * the last word in the string will be removed.
		 */
		if ( 0 !== mb_strpos( $string, $short . ' ', 0, $encoding ) ) {
			$pos = mb_strrpos( $short, ' ', $encoding );
			if ( false !== $pos ) {
				$short = mb_substr( $short, 0, $pos, $encoding );
			}
		}

		/* Append shortened string with the value of $append preceeded by a non-breaking space. */
		$string = $short . ' ' . $append;
	}

	return $string;
}
?>