<?php
/*
 * Creation of Sermon Post Types and Taxonomies
 * Also all meta boxes
 */
 
// Determine the correct slug name based on options
function generate_wpfc_slug($slug_name = NULL) {
    $sermon_settings = get_option('wpfc_options');
    $archive_slug = $sermon_settings['archive_slug'];
    if(empty($archive_slug)) {
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
    'name' 					=> __( 'Sermons', 					'sermon-manager' ),
    'singular_name' 		=> __( 'Sermon', 					'sermon-manager' ),
    'add_new' 				=> __( 'Add New', 					'sermon-manager' ),
    'add_new_item' 			=> __( 'Add New Sermon', 			'sermon-manager' ),
    'edit_item' 			=> __( 'Edit Sermon', 				'sermon-manager' ),
    'new_item' 				=> __( 'New Sermon', 				'sermon-manager' ),
    'view_item' 			=> __( 'View Sermon', 				'sermon-manager' ),
    'search_items' 			=> __( 'Search Sermons', 			'sermon-manager' ),
    'not_found' 			=> __( 'No sermons found', 			'sermon-manager' ),
    'not_found_in_trash' 	=> __( 'No sermons found in Trash', 'sermon-manager' ),
    'menu_name' 			=> __( 'Sermons', 					'sermon-manager' ),
  );

  $args = array(
    'labels' 				=> $labels,
    'public' 				=> true,
    'publicly_queryable' 	=> true,
    'show_ui' 				=> true,
    'show_in_menu' 			=> true,
    'query_var' 			=> true,
    'menu_icon' 			=> SM_PLUGIN_URL.'includes/img/book-open-bookmark.png',
	'capability_type' 		=> 'post',
    'has_archive' 			=> true,
    'rewrite' 				=> generate_wpfc_slug(),
    'hierarchical' 			=> false,
    'supports' 				=> array( 'title', 'comments', 'thumbnail', 'entry-views' )
  ); 
  register_post_type('wpfc_sermon',$args);
}

//create new taxonomies: preachers, sermon series, bible books & topics
add_action( 'init', 'create_wpfc_sermon_taxonomies', 0 );
function create_wpfc_sermon_taxonomies() {

//Preachers
$labels = array(	
	'name' 							=> __( 'Preachers', 								'sermon-manager' ),
	'singular_name' 				=> __( 'Preacher', 									'sermon-manager' ),
	'menu_name' 					=> __( 'Preachers', 								'sermon-manager' ),
	'search_items' 					=> __( 'Search preachers', 							'sermon-manager' ),
	'popular_items' 				=> __( 'Most frequent preachers', 					'sermon-manager' ),
	'all_items' 					=> __( 'All preachers', 							'sermon-manager' ),
	'edit_item' 					=> __( 'Edit preachers', 							'sermon-manager' ),
	'update_item' 					=> __( 'Update preachers', 							'sermon-manager' ),
	'add_new_item' 					=> __( 'Add new preacher', 							'sermon-manager' ),
	'new_item_name' 				=> __( 'New preacher name', 						'sermon-manager' ),
	'separate_items_with_commas' 	=> __( 'Separate multiple preachers with commas', 	'sermon-manager' ),
	'add_or_remove_items' 			=> __( 'Add or remove preachers', 					'sermon-manager' ),
	'choose_from_most_used' 		=> __( 'Choose from most frequent preachers', 		'sermon-manager' ),
	'parent_item' 					=> null,
    'parent_item_colon' 			=> null,
);

register_taxonomy('wpfc_preacher','wpfc_sermon', array(
	'hierarchical' 	=> false,
	'labels' 		=> $labels,
	'show_ui' 		=> true,
	'query_var' 	=> true,
    'rewrite' 		=> generate_wpfc_slug('preacher'),
));

//Sermon Series
$labels = array(	
	'name' 							=> __( 'Sermon Series', 						'sermon-manager' ),
	'singular_name' 				=> __( 'Sermon Series', 						'sermon-manager' ),
	'menu_name' 					=> __( 'Sermon Series', 						'sermon-manager' ),
	'search_items' 					=> __( 'Search sermon series', 					'sermon-manager' ),
	'popular_items' 				=> __( 'Most frequent sermon series', 			'sermon-manager' ),
	'all_items' 					=> __( 'All sermon series', 					'sermon-manager' ),
	'edit_item' 					=> __( 'Edit sermon series', 					'sermon-manager' ),
	'update_item' 					=> __( 'Update sermon series', 					'sermon-manager' ),
	'add_new_item' 					=> __( 'Add new sermon series', 				'sermon-manager' ),
	'new_item_name' 				=> __( 'New sermon series name', 				'sermon-manager' ),
	'separate_items_with_commas' 	=> __( 'Separate sermon series with commas', 	'sermon-manager' ),
	'add_or_remove_items' 			=> __( 'Add or remove sermon series', 			'sermon-manager' ),
	'choose_from_most_used' 		=> __( 'Choose from most used sermon series', 	'sermon-manager' ),
	'parent_item' 					=> null,
    'parent_item_colon' 			=> null,
);

register_taxonomy('wpfc_sermon_series','wpfc_sermon', array(
	'hierarchical' 	=> false,
	'labels' 		=> $labels,
	'show_ui' 		=> true,
	'query_var' 	=> true,
    'rewrite' 		=> generate_wpfc_slug('series'),
));

//Sermon Topics
$labels = array(	
	'name' 							=> __( 'Sermon Topics', 						'sermon-manager' ),
	'singular_name' 				=> __( 'Sermon Topics', 						'sermon-manager' ),
	'menu_name' 					=> __( 'Sermon Topics', 						'sermon-manager' ),
	'search_items' 					=> __( 'Search sermon topics', 					'sermon-manager' ),
	'popular_items' 				=> __( 'Most popular sermon topics', 			'sermon-manager' ),
	'all_items' 					=> __( 'All sermon topics', 					'sermon-manager' ),
	'edit_item' 					=> __( 'Edit sermon topic', 					'sermon-manager' ),
	'update_item' 					=> __( 'Update sermon topic', 					'sermon-manager' ),
	'add_new_item' 					=> __( 'Add new sermon topic', 					'sermon-manager' ),
	'new_item_name' 				=> __( 'New sermon topic', 						'sermon-manager' ),
	'separate_items_with_commas' 	=> __( 'Separate sermon topics with commas', 	'sermon-manager' ),
	'add_or_remove_items' 			=> __( 'Add or remove sermon topics', 			'sermon-manager' ),
	'choose_from_most_used' 		=> __( 'Choose from most used sermon topics', 	'sermon-manager' ),
	'parent_item' 					=> null,
    'parent_item_colon' 			=> null,
);

register_taxonomy('wpfc_sermon_topics','wpfc_sermon', array(
	'hierarchical' 	=> false,
	'labels' 		=> $labels,
	'show_ui' 		=> true,
	'query_var' 	=> true,
    'rewrite' 		=> generate_wpfc_slug('topics'),
));

//Books of the Bible
$labels = array(	
	'name' 							=> __( 'Book of the Bible', 						'sermon-manager' ),
	'singular_name' 				=> __( 'Book of the Bible', 						'sermon-manager' ),
	'menu_name' 					=> __( 'Book of the Bible', 						'sermon-manager' ),
	'search_items' 					=> __( 'Search books of the Bible', 				'sermon-manager' ),
	'popular_items' 				=> __( 'Most popular books of the Bible', 			'sermon-manager' ),
	'all_items' 					=> __( 'All books of the Bible', 					'sermon-manager' ),
	'edit_item' 					=> __( 'Edit book of the Bible', 					'sermon-manager' ),
	'update_item' 					=> __( 'Update book of the Bible', 					'sermon-manager' ),
	'add_new_item' 					=> __( 'Add new books of the Bible', 				'sermon-manager' ),
	'new_item_name' 				=> __( 'New book of the Bible', 					'sermon-manager' ),
	'separate_items_with_commas' 	=> __( 'Separate books of the Bible with commas', 	'sermon-manager' ),
	'add_or_remove_items' 			=> __( 'Add or remove books of the Bible', 			'sermon-manager' ),
	'choose_from_most_used' 		=> __( 'Choose from most used books of the Bible', 	'sermon-manager' ),
	'parent_item' 					=> null,
    'parent_item_colon' 			=> null,
);

register_taxonomy('wpfc_bible_book','wpfc_sermon', array(
	'hierarchical' 	=> false,
	'labels' 		=> $labels,
	'show_ui' 		=> true,
	'query_var' 	=> true,
    'rewrite' 		=> generate_wpfc_slug('book'),
));

//Service Type
$labels = array(	
	'name' 							=> __( 'Service Type', 							'sermon-manager' ),
	'singular_name' 				=> __( 'Service Type', 							'sermon-manager' ),
	'menu_name' 					=> __( 'Service Type', 							'sermon-manager' ),
	'search_items' 					=> __( 'Search service types', 					'sermon-manager' ),
	'popular_items' 				=> __( 'Most popular service types', 			'sermon-manager' ),
	'all_items' 					=> __( 'All service types', 					'sermon-manager' ),
	'edit_item'						=> __( 'Edit service type', 					'sermon-manager' ),
	'update_item' 					=> __( 'Update service type', 					'sermon-manager' ),
	'add_new_item' 					=> __( 'Add new service types', 				'sermon-manager' ),
	'new_item_name' 				=> __( 'New Service Type', 						'sermon-manager' ),
	'separate_items_with_commas' 	=> __( 'Separate service types with commas', 	'sermon-manager' ),
	'add_or_remove_items' 			=> __( 'Add or remove service types', 			'sermon-manager' ),
	'choose_from_most_used' 		=> __( 'Choose from most used service types', 	'sermon-manager' ),
	'parent_item' 					=> null,
    'parent_item_colon' 			=> null,
);

register_taxonomy('wpfc_service_type','wpfc_sermon', array(
	'hierarchical' 	=> false,
	'labels' 		=> $labels,
	'show_ui' 		=> true,
	'query_var' 	=> true,
    'rewrite' 		=> generate_wpfc_slug('service-type'),
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
			/* just testing for duration and file size fields _wpfc_sermon_duration & _wpfc_sermon_size
			array(
				'name' => __('MP3 Duration', 'sermon-manager'),
				'desc' => __('Length in minutes (will be automatically determined by Sermon Manager when you save)', 'sermon-manager'),
				'id'   => '_wpfc_sermon_duration',
				'type' => 'text',
			),
			array(
				'name' => __('MP3 File size', 'sermon-manager'),
				'desc' => __('File size in bytes (will be automatically determined by Sermon Manager when you save)', 'sermon-manager'),
				'id'   => '_wpfc_sermon_size',
				'type' => 'text',
			),*/
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

/**
 * Get podcast file info on save and store in custom fields.
 * Using meta boxes validation filter.
 * Added by T Hyde 9 Oct 2013
 *
 * @param $new
 * @param $post_id
 * @param $field
 * @return $new unchanged
 */
function wpfc_sermon_audio_validate( $new, $post_id, $field ) {

    // only for sermon audio
    if ( $field['id'] != 'sermon_audio' )
        return $new;

    $current = get_post_meta($post_id, 'sermon_audio', 'true');
    $currentsize = get_post_meta($post_id, '_wpfc_sermon_size', 'true');

    // only grab if different (getting data from dropbox can be a bit slow)
    if ( $new != '' && ( $new != $current || empty($currentsize) ) ) {

        // get file data
        $size =  wpfc_get_filesize( $new );
        $duration = wpfc_mp3_duration( $new );

        // store in hidden custom fields
        update_post_meta( $post_id, '_wpfc_sermon_duration', $duration );
        update_post_meta( $post_id, '_wpfc_sermon_size', $size );

    } elseif ($new == '') {

        // clean up if file removed
        delete_post_meta( $post_id, '_wpfc_sermon_duration');
        delete_post_meta( $post_id, '_wpfc_sermon_size' );

    }

    return $new;
}
add_filter('wpfc_validate_file','wpfc_sermon_audio_validate', 10, 3);

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
//add_action( 'contextual_help', 'add_wpfc_sermon_help_text', 10, 3 );


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