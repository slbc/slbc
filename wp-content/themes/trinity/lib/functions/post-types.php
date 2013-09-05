<?php

/**
 * Post list shortcode
 * @shortcode post_list
 */
function churchthemes_posts_shortcode($atts, $content = null){
	global $post, $wp_query;
	extract(shortcode_atts(array(
		// Default behaviors
		'post_status' => 'publish',
		'num' => get_option( 'posts_per_page' ),
		'paging' => 'show',
		'images' => 'show',
		'offset' => '', // number of posts to displace
		'orderby' => 'date',
		'order' => 'DESC',
		'p' => '', // post ID
		'name' => '', // post slug
		'post__in' => '', // posts to retrieve, comma separated IDs
		'post__not_in' => '', // posts to ignore, comma separated IDs
		'year' => '', // 4 digit year (e.g. 2012)
		'monthnum' => '', // 1-12
		'w' => '', // 0-53
		'day' => '', // 1-31
		'hour' => '', // 0-23
		'minute' => '', // 0-60
		'second' => '', // 0-60
		'author' => '', // author ID
		'author_name' => '', // author username
		'tag' => '', // tag slug, if separated by "+" the functionality becomes identical to tag_slug__and
		'tag_id' => '', // tag ID
		'tag__and' => '', // posts that are tagged both x AND y, comma separated IDs
		'tag__in' => '', // posts that are tagged x OR y, comma separated IDs
		'tag__not_in' => '', // exclude posts with these tags, comma separated IDs
		'tag_slug__and' => '', // posts that are tagged both x AND y, comma separated slugs
		'tag_slug__in' => '', // posts that are tagged x OR y, comma separated slugs
		'cat' => '', // category ID
		'category_name' => '', // category slug
		'category__and' => '', // posts that are in both categories x AND y, comma separated IDs
		'category__in' => '', // posts that are in categories x OR y, comma separated IDs
		'category__not_in' => '', // exclude posts from these categories, comma separated IDs
	), $atts));

	if($orderby == 'views'): $orderby = 'meta_value_num'; endif;
	if($paging == 'hide'):
		$paged = null;
	else:
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	endif;

	$args = array(
		'post_type' => 'post', // only return posts
		'post_status' => $post_status, // default: publish
		'posts_per_page' => $num, // default: Settings > Reading > Blog pages show at most
		'paged' => $paged, // default: paged
		'offset' => $offset,
		'orderby' => $orderby, // default: date
		'order' => $order, // default: DESC
		'p' => $p,
		'name' => $name,
		'year' => $year,
		'monthnum' => $monthnum,
		'w' => $w,
		'day' => $day,
		'hour' => $hour,
		'minute' => $minute,
		'second' => $second,
		'author' => $author,
		'author_name' => $author_name,
		'tag' => $tag,
		'cat' => $cat,
		'category_name' => $category_name,
	);

	// the following parameters require array values
	if ($orderby == 'meta_value_num') {
		$args = array_merge( $args, array( 'meta_key' => 'Views' ) );
	}
	if ($post__in) {
		$args = array_merge( $args, array( 'post__in' => explode(',', $post__in) ) );
	}
	if ($post__not_in) {
		$args = array_merge( $args, array( 'post__not_in' => explode(',', $post__not_in) ) );
	}
	if ($tag_id) {
		$args = array_merge( $args, array( 'tag_id' => explode(',', $tag_id) ) );
	}
	if ($tag__and) {
		$args = array_merge( $args, array( 'tag__and' => explode(',', $tag__and) ) );
	}
	if ($tag__in) {
		$args = array_merge( $args, array( 'tag__in' => explode(',', $tag__in) ) );
	}
	if ($tag__not_in) {
		$args = array_merge( $args, array( 'tag__not_in' => explode(',', $tag__not_in) ) );
	}
	if ($tag_slug__and) {
		$args = array_merge( $args, array( 'tag_slug__and' => explode(',', $tag_slug__and) ) );
	}
	if ($tag_slug__in) {
		$args = array_merge( $args, array( 'tag_slug__in' => explode(',', $tag_slug__in) ) );
	}
	if ($category__and) {
		$args = array_merge( $args, array( 'category__and' => explode(',', $category__and) ) );
	}
	if ($category__in) {
		$args = array_merge( $args, array( 'category__in' => explode(',', $category__in) ) );
	}
	if ($category__not_in) {
		$args = array_merge( $args, array( 'category__not_in' => explode(',', $category__not_in) ) );
	}

	query_posts($args);

	ob_start();
	if ( $images != 'hide' ) {
			include('shortcode-posts.php');
	}
	else {
		include('shortcode-posts-noimage.php');
	}
	if($paging != 'hide') {
		pagination();
	}
	wp_reset_query();
	$content = ob_get_clean();
	return $content;
}
add_shortcode( 'posts', 'churchthemes_posts_shortcode' );

// End Posts Shortcode


/* SLIDE */

// Register Post Type
add_action('init', 'sl_register');

function sl_register() {
	$labels = array(
		'name' => ( 'Slides' ),
		'singular_name' => ( 'Slide' ),
		'add_new' => _x( 'Add New', 'ct_slide' ),
		'add_new_item' => __( 'Add New Slide' ),
		'edit_item' => __( 'Edit Slide' ),
		'new_item' => __( 'New Slide' ),
		'view_item' => __( 'View Slide' ),
		'search_items' => __( 'Search Slides' ),
		'not_found' =>  __( 'No Slides found' ),
		'not_found_in_trash' => __( 'No Slides found in Trash' ),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 10,
		'menu_icon' => get_template_directory_uri() . '/lib/admin/images/menu_icon-slide-16.png',
		'supports' => array( 'title', 'thumbnail', 'revisions' )
	);

	register_post_type( 'ct_slide' , $args );

	flush_rewrite_rules(false);

}
// End Register Post Type

// Create Custom Taxonomies
add_action( 'init', 'create_slide_taxonomies', 0 );

function create_slide_taxonomies() {

	// Slide Tags Taxonomy (Non-Hierarchical)
	$labels = array(
		'name' => _x( 'Slide Tags', 'taxonomy general name' ),
		'singular_name' => _x( 'Tag', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Tags' ),
		'popular_items' => __( 'Popular Tags' ),
		'all_items' => __( 'All Tags' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Tag' ),
		'update_item' => __( 'Update Tag' ),
		'add_new_item' => __( 'Add New Tag' ),
		'new_item_name' => __( 'New Tag Name' ),
		'separate_items_with_commas' => __( 'Separate Tags with commas' ),
		'add_or_remove_items' => __( 'Add or remove Tags' ),
		'choose_from_most_used' => __( 'Choose from the most used Tags' )
	);
	register_taxonomy( 'slide_tag', 'ct_slide', array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'slide_tag' ),
	));
	// End Slide Tags Taxonomy

}
// End Custom Taxonomies

// Submenu
add_action('admin_menu', 'sl_submenu');

function sl_submenu() {

	// Add to end of admin_menu action function
	global $submenu;
	$submenu['edit.php?post_type=ct_slide'][5][0] = __('All Slides');
	$post_type_object = get_post_type_object('ct_slide');
	$post_type_object->labels->name = "Slides";

}
// End Submenu

// Create Slide Options Box
add_action("admin_init", "sl_admin_init");

function sl_admin_init(){
    add_meta_box("sl_meta", "Slide Options", "sl_meta_options", "ct_slide", "normal", "core");
}

// Custom Field Keys
function sl_meta_options(){
	global $post;
	$custom = get_post_custom($post->ID);
	isset($custom["_ct_sl_tagline"][0]) ? $sl_tagline = $custom["_ct_sl_tagline"][0] : $sl_tagline = null;
	isset($custom["_ct_sl_linkurl"][0]) ? $sl_linkurl = $custom["_ct_sl_linkurl"][0] : $sl_linkurl = null;
	isset($custom["_ct_sl_disable_text"][0]) ? $sl_disable_text = $custom["_ct_sl_disable_text"][0] : $sl_disable_text = null;
	isset($custom["_ct_sl_notes"][0]) ? $sl_notes = $custom["_ct_sl_notes"][0] : $sl_notes = null;
// End Custom Field Keys

// Start HTML
?>

	<h2 class="meta_section"><?php _e('Featured Image', 'churchthemes'); ?></h2>

	<div class="meta_item first">
		<a title="Set Featured Image" href="media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=285" id="set-post-thumbnail" class="thickbox button rbutton"><?php _e('Set Featured Image', 'churchthemes'); ?></a>
		<br />
		<span><?php _e('To ensure the best image quality possible, please use a JPG image that is 924 x 345 pixels', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('General', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_sl_tagline"><?php _e('Tagline', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sl_tagline" size="70" autocomplete="on" value="<?php echo esc_attr($sl_tagline); ?>">
		<span><?php _e('Tagline shown under the title on the slide (80 characters max)', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sl_linkurl"><?php _e('Slide Link', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sl_linkurl" size="70" autocomplete="on" placeholder="e.g. http://mychurch.org/some-page-with-more-info/" value="<?php echo esc_url($sl_linkurl); ?>">
		<span><?php _e('Where users are taken when the slide image is clicked', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sl_disable_text"><?php _e('Disable Text', 'churchthemes'); ?></label>
		<input type="checkbox" name="_ct_sl_disable_text" class="ct_meta_checkbox" value="true" <?php if($sl_disable_text == true) echo 'checked="checked"'; ?>>
		<span><?php _e('Disables the Title/Tagline text and displays only the slide image', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('More', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_sl_notes">
			<?php _e('Admin Notes', 'churchthemes'); ?>
			<br /><br />
			<span class="label_note"><?php _e('Not Published', 'churchthemes'); ?></span>
		</label>
		<textarea type="text" name="_ct_sl_notes" cols="60" rows="8"><?php echo esc_textarea($sl_notes); ?></textarea>
	</div>

	<div class="meta_clear"></div>

<?php
// End HTML
}

// Save Custom Field Values
add_action('save_post', 'save_ct_sl_meta');

function save_ct_sl_meta(){

	global $post_id;

	if(isset($_POST['post_type']) && ($_POST['post_type'] == "ct_slide")):

		$sl_tagline = wp_filter_nohtml_kses( $_POST['_ct_sl_tagline'] );
		update_post_meta($post_id, '_ct_sl_tagline', $sl_tagline);

		$sl_linkurl = esc_url_raw( $_POST['_ct_sl_linkurl'] );
		update_post_meta($post_id, '_ct_sl_linkurl', $sl_linkurl);

		$sl_disable_text = isset( $_POST['_ct_sl_disable_text'] ) ? wp_filter_nohtml_kses( $_POST['_ct_sl_disable_text'] ) : null;
		update_post_meta($post_id, '_ct_sl_disable_text', $sl_disable_text);

		$sl_notes = wp_filter_nohtml_kses( $_POST['_ct_sl_notes'] );
		update_post_meta($post_id, '_ct_sl_notes', $sl_notes);

	endif;
}
// End Custom Field Values
// End Slide Options Box

// Custom Columns
function sl_register_columns($columns){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => 'Title',
			'sl_tagline' => 'Tagline',
			'sl_tags' => 'Tags',
			'sl_image' => 'Featured Image'
		);
		return $columns;
}
add_filter('manage_edit-ct_slide_columns', 'sl_register_columns');

function sl_display_columns($column){
		global $post;
		$custom = get_post_custom();
		switch ($column)
		{
			case 'sl_tagline':
				$tagline = $custom['_ct_sl_tagline'][0];
				echo $tagline;
				break;
			case 'sl_tags':
				echo get_the_term_list($post->ID, 'slide_tag', '', ', ', '');
				break;
			case 'sl_image':
				echo get_the_post_thumbnail($post->ID, 'admin');
				break;
		}
}
add_action('manage_posts_custom_column', 'sl_display_columns');

// End Custom Columns

/* END SLIDE */


/* SERMON */

// Register Post Type
add_action('init', 'sm_register');

function sm_register() {
	$labels = array(
		'name' => ( 'Sermons' ),
		'singular_name' => ( 'Sermon' ),
		'add_new' => _x( 'Add New', 'ct_sermon' ),
		'add_new_item' => __( 'Add New Sermon' ),
		'edit_item' => __( 'Edit Sermon' ),
		'new_item' => __( 'New Sermon' ),
		'view_item' => __( 'View Sermon' ),
		'search_items' => __( 'Search Sermons' ),
		'not_found' =>  __( 'No Sermons found' ),
		'not_found_in_trash' => __( 'No Sermons found in Trash' ),
		'parent_item_colon' => ''
	);


	$sermon_settings = get_option('ct_sermon_settings');
	$archive_slug = $sermon_settings['archive_slug'];
	if(empty($archive_slug)):
		$archive_slug = 'sermons';
	endif;

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'has_archive' => $archive_slug,
		'query_var' => true,
		'rewrite' => array('slug' => $archive_slug),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 10,
		'menu_icon' => get_template_directory_uri() . '/lib/admin/images/menu_icon-sermon-16.png',
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'comments' ),
		'taxonomies' => array( 'sermon_speaker', 'sermon_service', 'sermon_series', 'sermon_topic' )
	);

	register_post_type('ct_sermon', $args);

	flush_rewrite_rules(false);

}
// End Register Post Type

// Create Custom Taxonomies
add_action( 'init', 'create_sermon_taxonomies', 0 );

function create_sermon_taxonomies() {

	// Speakers Taxonomy (Non-Hierarchical)
	$labels = array(
		'name' => _x( 'Speakers', 'taxonomy general name' ),
		'singular_name' => _x( 'Speaker', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Speakers' ),
		'popular_items' => __( 'Popular Speakers' ),
		'all_items' => __( 'All Speakers' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Speaker' ),
		'update_item' => __( 'Update Speaker' ),
		'add_new_item' => __( 'Add New Speaker' ),
		'new_item_name' => __( 'New Speaker Name' ),
		'separate_items_with_commas' => __( 'Separate Speakers with commas' ),
		'add_or_remove_items' => __( 'Add or remove Speakers' ),
		'choose_from_most_used' => __( 'Choose from the most used Speakers' )
	);
	register_taxonomy( 'sermon_speaker', 'ct_sermon', array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'speakers' ),
	));
	// End Speakers Taxonomy

	// Services Taxonomy (Hierarchical)
	$labels = array(
		'name' => _x( 'Services', 'taxonomy general name' ),
		'singular_name' => _x( 'Service', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Services' ),
		'all_items' => __( 'All Services' ),
		'parent_item' => __( 'Parent Service' ),
		'parent_item_colon' => __( 'Parent Service:' ),
		'edit_item' => __( 'Edit Service' ),
		'update_item' => __( 'Update Service' ),
		'add_new_item' => __( 'Add New Service' ),
		'new_item_name' => __( 'New Service Name' ),
	);
	register_taxonomy( 'sermon_service', array( 'ct_sermon' ), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'services' ),
	));
	// End Services Taxonomy

	// Series Taxonomy (Hierarchical)
	$labels = array(
		'name' => _x( 'Series', 'taxonomy general name' ),
		'singular_name' => _x( 'Series', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Series' ),
		'all_items' => __( 'All Series' ),
		'parent_item' => __( 'Parent Series' ),
		'parent_item_colon' => __( 'Parent Series:' ),
		'edit_item' => __( 'Edit Series' ),
		'update_item' => __( 'Update Series' ),
		'add_new_item' => __( 'Add New Series' ),
		'new_item_name' => __( 'New Series Name' ),
	);
	register_taxonomy( 'sermon_series', array( 'ct_sermon' ), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'series' ),
	));
	// End Series Taxonomy

	// Topics Taxonomy (Non-Hierarchical)
	$labels = array(
		'name' => _x( 'Topics', 'taxonomy general name' ),
		'singular_name' => _x( 'Topic', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Topics' ),
		'popular_items' => __( 'Popular Topics' ),
		'all_items' => __( 'All Topics' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Topic' ),
		'update_item' => __( 'Update Topic' ),
		'add_new_item' => __( 'Add New Topic' ),
		'new_item_name' => __( 'New Topic Name' ),
		'separate_items_with_commas' => __( 'Separate Topics with commas' ),
		'add_or_remove_items' => __( 'Add or remove Topics' ),
		'choose_from_most_used' => __( 'Choose from the most used Topics' )
	);
	register_taxonomy( 'sermon_topic', 'ct_sermon', array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'topics' ),
	));
	// End Topics Taxonomy

}
// End Custom Taxonomies


// Create Submenu
add_action('admin_menu', 'sm_submenu');

function sm_submenu() {

	// Add to end of admin_menu action function
	global $submenu;
	$submenu['edit.php?post_type=ct_sermon'][5][0] = __('All Sermons');
	$post_type_object = get_post_type_object('ct_sermon');
	$post_type_object->labels->name = "Sermons";

}
// End Submenu

// Create Sermon Options Box
add_action("admin_init", "sm_admin_init");

function sm_admin_init(){
	add_meta_box("sm_meta", "Sermon Options", "sm_meta_options", "ct_sermon", "normal", "core");
}
// End Sermon Options Box

// Custom Field Keys
function sm_meta_options(){
	global $post;
	$custom = get_post_custom($post->ID);
	isset($custom["_ct_sm_bible01_book"][0]) ? $sm_bible01_book = $custom["_ct_sm_bible01_book"][0] : $sm_bible01_book = null;
	isset($custom["_ct_sm_bible01_start_chap"][0]) ? $sm_bible01_start_chap = $custom["_ct_sm_bible01_start_chap"][0] : $sm_bible01_start_chap = null;
	isset($custom["_ct_sm_bible01_start_verse"][0]) ? $sm_bible01_start_verse = $custom["_ct_sm_bible01_start_verse"][0] : $sm_bible01_start_verse = null;
	isset($custom["_ct_sm_bible01_end_chap"][0]) ? $sm_bible01_end_chap = $custom["_ct_sm_bible01_end_chap"][0] : $sm_bible01_end_chap = null;
	isset($custom["_ct_sm_bible01_end_verse"][0]) ? $sm_bible01_end_verse = $custom["_ct_sm_bible01_end_verse"][0] : $sm_bible01_end_verse = null;
	isset($custom["_ct_sm_bible02_book"][0]) ? $sm_bible02_book = $custom["_ct_sm_bible02_book"][0] : $sm_bible02_book = null;
	isset($custom["_ct_sm_bible02_start_chap"][0]) ? $sm_bible02_start_chap = $custom["_ct_sm_bible02_start_chap"][0] : $sm_bible02_start_chap = null;
	isset($custom["_ct_sm_bible02_start_verse"][0]) ? $sm_bible02_start_verse = $custom["_ct_sm_bible02_start_verse"][0] : $sm_bible02_start_verse = null;
	isset($custom["_ct_sm_bible02_end_chap"][0]) ? $sm_bible02_end_chap = $custom["_ct_sm_bible02_end_chap"][0] : $sm_bible02_end_chap = null;
	isset($custom["_ct_sm_bible02_end_verse"][0]) ? $sm_bible02_end_verse = $custom["_ct_sm_bible02_end_verse"][0] : $sm_bible02_end_verse = null;
	isset($custom["_ct_sm_bible03_book"][0]) ? $sm_bible03_book = $custom["_ct_sm_bible03_book"][0] : $sm_bible03_book = null;
	isset($custom["_ct_sm_bible03_start_chap"][0]) ? $sm_bible03_start_chap = $custom["_ct_sm_bible03_start_chap"][0] : $sm_bible03_start_chap = null;
	isset($custom["_ct_sm_bible03_start_verse"][0]) ? $sm_bible03_start_verse = $custom["_ct_sm_bible03_start_verse"][0] : $sm_bible03_start_verse = null;
	isset($custom["_ct_sm_bible03_end_chap"][0]) ? $sm_bible03_end_chap = $custom["_ct_sm_bible03_end_chap"][0] : $sm_bible03_end_chap = null;
	isset($custom["_ct_sm_bible03_end_verse"][0]) ? $sm_bible03_end_verse = $custom["_ct_sm_bible03_end_verse"][0] : $sm_bible03_end_verse = null;
	isset($custom["_ct_sm_bible04_book"][0]) ? $sm_bible04_book = $custom["_ct_sm_bible04_book"][0] : $sm_bible04_book = null;
	isset($custom["_ct_sm_bible04_start_chap"][0]) ? $sm_bible04_start_chap = $custom["_ct_sm_bible04_start_chap"][0] : $sm_bible04_start_chap = null;
	isset($custom["_ct_sm_bible04_start_verse"][0]) ? $sm_bible04_start_verse = $custom["_ct_sm_bible04_start_verse"][0] : $sm_bible04_start_verse = null;
	isset($custom["_ct_sm_bible04_end_chap"][0]) ? $sm_bible04_end_chap = $custom["_ct_sm_bible04_end_chap"][0] : $sm_bible04_end_chap = null;
	isset($custom["_ct_sm_bible04_end_verse"][0]) ? $sm_bible04_end_verse = $custom["_ct_sm_bible04_end_verse"][0] : $sm_bible04_end_verse = null;
	isset($custom["_ct_sm_bible05_book"][0]) ? $sm_bible05_book = $custom["_ct_sm_bible05_book"][0] : $sm_bible05_book = null;
	isset($custom["_ct_sm_bible05_start_chap"][0]) ? $sm_bible05_start_chap = $custom["_ct_sm_bible05_start_chap"][0] : $sm_bible05_start_chap = null;
	isset($custom["_ct_sm_bible05_start_verse"][0]) ? $sm_bible05_start_verse = $custom["_ct_sm_bible05_start_verse"][0] : $sm_bible05_start_verse = null;
	isset($custom["_ct_sm_bible05_end_chap"][0]) ? $sm_bible05_end_chap = $custom["_ct_sm_bible05_end_chap"][0] : $sm_bible05_end_chap = null;
	isset($custom["_ct_sm_bible05_end_verse"][0]) ? $sm_bible05_end_verse = $custom["_ct_sm_bible05_end_verse"][0] : $sm_bible05_end_verse = null;
	isset($custom["_ct_sm_audio_file"][0]) ? $sm_audio_file = $custom["_ct_sm_audio_file"][0] : $sm_audio_file = null;
	isset($custom["_ct_sm_audio_length"][0]) ? $sm_audio_length = $custom["_ct_sm_audio_length"][0] : $sm_audio_length = null;
	isset($custom["_ct_sm_audio_button_text"][0]) ? $sm_audio_button_text = $custom["_ct_sm_audio_button_text"][0] : $sm_audio_button_text = null;
	isset($custom["_ct_sm_video_embed"][0]) ? $sm_video_embed = $custom["_ct_sm_video_embed"][0] : $sm_video_embed = null;
	isset($custom["_ct_sm_video_file"][0]) ? $sm_video_file = $custom["_ct_sm_video_file"][0] : $sm_video_file = null;
	isset($custom["_ct_sm_video_button_text"][0]) ? $sm_video_button_text = $custom["_ct_sm_video_button_text"][0] : $sm_video_button_text = null;
	isset($custom["_ct_sm_sg_file"][0]) ? $sm_sg_file = $custom["_ct_sm_sg_file"][0] : $sm_sg_file = null;
	isset($custom["_ct_sm_sg_button_text"][0]) ? $sm_sg_button_text = $custom["_ct_sm_sg_button_text"][0] : $sm_sg_button_text = null;
	isset($custom["_ct_sm_notes"][0]) ? $sm_notes = $custom["_ct_sm_notes"][0] : $sm_notes = null;
// End Custom Field Keys

// Start HTML
	?>
	<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function() {
		jQuery('#upload_audio').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?TB_iframe=true');
			return false;
		});
		window.send_to_editor = function(html) {
			audiourl = jQuery(html).attr('href');
			uploadID.val(audiourl); /*assign the value to the input*/
			tb_remove();
		};
		jQuery('#upload_video').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?TB_iframe=true');
			return false;
		});
		window.send_to_editor = function(html) {
			videourl = jQuery(html).attr('href');
			uploadID.val(videourl); /*assign the value to the input*/
			tb_remove();
		};
		jQuery('#upload_doc').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?TB_iframe=true');
			return false;
		});
		window.send_to_editor = function(html) {
			docurl = jQuery(html).attr('href');
			uploadID.val(docurl); /*assign the value to the input*/
			tb_remove();
		};
	});
	</script>

	<h2 class="meta_section"><?php _e('Featured Image', 'churchthemes'); ?></h2>

	<div class="meta_item first">
		<a title="Set Featured Image" href="media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=285" id="set-post-thumbnail" class="thickbox button rbutton"><?php _e('Set Featured Image', 'churchthemes'); ?></a>
		<br />
		<span><?php _e('To ensure the best image quality possible, please use a JPG image that is at least 608 x 342 (pixels)', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Bible References', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label><?php _e('Passage 1', 'churchthemes'); ?></label>
		<select name="_ct_sm_bible01_book">
			<option value=""<?php if($sm_bible01_book=="") echo " selected";?>><?php _e('- Select Book -', 'churchthemes'); ?></option>
			<option value="Genesis"<?php if($sm_bible01_book=="Genesis") echo " selected";?>><?php _e('Genesis', 'churchthemes'); ?></option>
			<option value="Exodus"<?php if($sm_bible01_book=="Exodus") echo " selected";?>><?php _e('Exodus', 'churchthemes'); ?></option>
			<option value="Leviticus"<?php if($sm_bible01_book=="Leviticus") echo " selected";?>><?php _e('Leviticus', 'churchthemes'); ?></option>
			<option value="Numbers"<?php if($sm_bible01_book=="Numbers") echo " selected";?>><?php _e('Numbers', 'churchthemes'); ?></option>
			<option value="Deuteronomy"<?php if($sm_bible01_book=="Deuteronomy") echo " selected";?>><?php _e('Deuteronomy', 'churchthemes'); ?></option>
			<option value="Joshua"<?php if($sm_bible01_book=="Joshua") echo " selected";?>><?php _e('Joshua', 'churchthemes'); ?></option>
			<option value="Judges"<?php if($sm_bible01_book=="Judges") echo " selected";?>><?php _e('Judges', 'churchthemes'); ?></option>
			<option value="Ruth"<?php if($sm_bible01_book=="Ruth") echo " selected";?>><?php _e('Ruth', 'churchthemes'); ?></option>
			<option value="1 Samuel"<?php if($sm_bible01_book=="1 Samuel") echo " selected";?>><?php _e('1 Samuel', 'churchthemes'); ?></option>
			<option value="2 Samuel"<?php if($sm_bible01_book=="2 Samuel") echo " selected";?>><?php _e('2 Samuel', 'churchthemes'); ?></option>
			<option value="1 Kings"<?php if($sm_bible01_book=="1 Kings") echo " selected";?>><?php _e('1 Kings', 'churchthemes'); ?></option>
			<option value="2 Kings"<?php if($sm_bible01_book=="2 Kings") echo " selected";?>><?php _e('2 Kings', 'churchthemes'); ?></option>
			<option value="1 Chronicles"<?php if($sm_bible01_book=="1 Chronicles") echo " selected";?>><?php _e('1 Chronicles', 'churchthemes'); ?></option>
			<option value="2 Chronicles"<?php if($sm_bible01_book=="2 Chronicles") echo " selected";?>><?php _e('2 Chronicles', 'churchthemes'); ?></option>
			<option value="Ezra"<?php if($sm_bible01_book=="Ezra") echo " selected";?>><?php _e('Ezra', 'churchthemes'); ?></option>
			<option value="Nehemiah"<?php if($sm_bible01_book=="Nehemiah") echo " selected";?>><?php _e('Nehemiah', 'churchthemes'); ?></option>
			<option value="Esther"<?php if($sm_bible01_book=="Esther") echo " selected";?>><?php _e('Esther', 'churchthemes'); ?></option>
			<option value="Job"<?php if($sm_bible01_book=="Job") echo " selected";?>><?php _e('Job', 'churchthemes'); ?></option>
			<option value="Psalm"<?php if($sm_bible01_book=="Psalm") echo " selected";?>><?php _e('Psalm', 'churchthemes'); ?></option>
			<option value="Proverbs"<?php if($sm_bible01_book=="Proverbs") echo " selected";?>><?php _e('Proverbs', 'churchthemes'); ?></option>
			<option value="Ecclesiastes"<?php if($sm_bible01_book=="Ecclesiastes") echo " selected";?>><?php _e('Ecclesiastes', 'churchthemes'); ?></option>
			<option value="Song of Solomon"<?php if($sm_bible01_book=="Song of Solomon") echo " selected";?>><?php _e('Song of Solomon', 'churchthemes'); ?></option>
			<option value="Isaiah"<?php if($sm_bible01_book=="Isaiah") echo " selected";?>><?php _e('Isaiah', 'churchthemes'); ?></option>
			<option value="Jeremiah"<?php if($sm_bible01_book=="Jeremiah") echo " selected";?>><?php _e('Jeremiah', 'churchthemes'); ?></option>
			<option value="Lamentations"<?php if($sm_bible01_book=="Lamentations") echo " selected";?>><?php _e('Lamentations', 'churchthemes'); ?></option>
			<option value="Ezekiel"<?php if($sm_bible01_book=="Ezekiel") echo " selected";?>><?php _e('Ezekiel', 'churchthemes'); ?></option>
			<option value="Daniel"<?php if($sm_bible01_book=="Daniel") echo " selected";?>><?php _e('Daniel', 'churchthemes'); ?></option>
			<option value="Hosea"<?php if($sm_bible01_book=="Hosea") echo " selected";?>><?php _e('Hosea', 'churchthemes'); ?></option>
			<option value="Joel"<?php if($sm_bible01_book=="Joel") echo " selected";?>><?php _e('Joel', 'churchthemes'); ?></option>
			<option value="Amos"<?php if($sm_bible01_book=="Amos") echo " selected";?>><?php _e('Amos', 'churchthemes'); ?></option>
			<option value="Obadiah"<?php if($sm_bible01_book=="Obadiah") echo " selected";?>><?php _e('Obadiah', 'churchthemes'); ?></option>
			<option value="Jonah"<?php if($sm_bible01_book=="Jonah") echo " selected";?>><?php _e('Jonah', 'churchthemes'); ?></option>
			<option value="Micah"<?php if($sm_bible01_book=="Micah") echo " selected";?>><?php _e('Micah', 'churchthemes'); ?></option>
			<option value="Nahum"<?php if($sm_bible01_book=="Nahum") echo " selected";?>><?php _e('Nahum', 'churchthemes'); ?></option>
			<option value="Habakkuk"<?php if($sm_bible01_book=="Habakkuk") echo " selected";?>><?php _e('Habakkuk', 'churchthemes'); ?></option>
			<option value="Zephaniah"<?php if($sm_bible01_book=="Zephaniah") echo " selected";?>><?php _e('Zephaniah', 'churchthemes'); ?></option>
			<option value="Haggai"<?php if($sm_bible01_book=="Haggai") echo " selected";?>><?php _e('Haggai', 'churchthemes'); ?></option>
			<option value="Zechariah"<?php if($sm_bible01_book=="Zechariah") echo " selected";?>><?php _e('Zechariah', 'churchthemes'); ?></option>
			<option value="Malachi"<?php if($sm_bible01_book=="Malachi") echo " selected";?>><?php _e('Malachi', 'churchthemes'); ?></option>
			<option value="Matthew"<?php if($sm_bible01_book=="Matthew") echo " selected";?>><?php _e('Matthew', 'churchthemes'); ?></option>
			<option value="Mark"<?php if($sm_bible01_book=="Mark") echo " selected";?>><?php _e('Mark', 'churchthemes'); ?></option>
			<option value="Luke"<?php if($sm_bible01_book=="Luke") echo " selected";?>><?php _e('Luke', 'churchthemes'); ?></option>
			<option value="John"<?php if($sm_bible01_book=="John") echo " selected";?>><?php _e('John', 'churchthemes'); ?></option>
			<option value="Acts"<?php if($sm_bible01_book=="Acts") echo " selected";?>><?php _e('Acts', 'churchthemes'); ?></option>
			<option value="Romans"<?php if($sm_bible01_book=="Romans") echo " selected";?>><?php _e('Romans', 'churchthemes'); ?></option>
			<option value="1 Corinthians"<?php if($sm_bible01_book=="1 Corinthians") echo " selected";?>><?php _e('1 Corinthians', 'churchthemes'); ?></option>
			<option value="2 Corinthians"<?php if($sm_bible01_book=="2 Corinthians") echo " selected";?>><?php _e('2 Corinthians', 'churchthemes'); ?></option>
			<option value="Galatians"<?php if($sm_bible01_book=="Galatians") echo " selected";?>><?php _e('Galatians', 'churchthemes'); ?></option>
			<option value="Ephesians"<?php if($sm_bible01_book=="Ephesians") echo " selected";?>><?php _e('Ephesians', 'churchthemes'); ?></option>
			<option value="Philippians"<?php if($sm_bible01_book=="Philippians") echo " selected";?>><?php _e('Philippians', 'churchthemes'); ?></option>
			<option value="Colossians"<?php if($sm_bible01_book=="Colossians") echo " selected";?>><?php _e('Colossians', 'churchthemes'); ?></option>
			<option value="1 Thessalonians"<?php if($sm_bible01_book=="1 Thessalonians") echo " selected";?>><?php _e('1 Thessalonians', 'churchthemes'); ?></option>
			<option value="2 Thessalonians"<?php if($sm_bible01_book=="2 Thessalonians") echo " selected";?>><?php _e('2 Thessalonians', 'churchthemes'); ?></option>
			<option value="1 Timothy"<?php if($sm_bible01_book=="1 Timothy") echo " selected";?>><?php _e('1 Timothy', 'churchthemes'); ?></option>
			<option value="2 Timothy"<?php if($sm_bible01_book=="2 Timothy") echo " selected";?>><?php _e('2 Timothy', 'churchthemes'); ?></option>
			<option value="Titus"<?php if($sm_bible01_book=="Titus") echo " selected";?>><?php _e('Titus', 'churchthemes'); ?></option>
			<option value="Philemon"<?php if($sm_bible01_book=="Philemon") echo " selected";?>><?php _e('Philemon', 'churchthemes'); ?></option>
			<option value="Hebrews"<?php if($sm_bible01_book=="Hebrews") echo " selected";?>><?php _e('Hebrews', 'churchthemes'); ?></option>
			<option value="James"<?php if($sm_bible01_book=="James") echo " selected";?>><?php _e('James', 'churchthemes'); ?></option>
			<option value="1 Peter"<?php if($sm_bible01_book=="1 Peter") echo " selected";?>><?php _e('1 Peter', 'churchthemes'); ?></option>
			<option value="2 Peter"<?php if($sm_bible01_book=="2 Peter") echo " selected";?>><?php _e('2 Peter', 'churchthemes'); ?></option>
			<option value="1 John"<?php if($sm_bible01_book=="1 John") echo " selected";?>><?php _e('1 John', 'churchthemes'); ?></option>
			<option value="2 John"<?php if($sm_bible01_book=="2 John") echo " selected";?>><?php _e('2 John', 'churchthemes'); ?></option>
			<option value="3 John"<?php if($sm_bible01_book=="3 John") echo " selected";?>><?php _e('3 John', 'churchthemes'); ?></option>
			<option value="Jude"<?php if($sm_bible01_book=="Jude") echo " selected";?>><?php _e('Jude', 'churchthemes'); ?></option>
			<option value="Revelation"<?php if($sm_bible01_book=="Revelation") echo " selected";?>><?php _e('Revelation', 'churchthemes'); ?></option>
		</select>
	</div>

	<div class="meta_item verse_start">
		<label for="_ct_sm_bible01_start_chap"><?php _e('Start', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible01_start_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible01_start_chap); ?>" /> : <input type="text" name="_ct_sm_bible01_start_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible01_start_verse); ?>" />
	</div>

	<div class="meta_item verse_end">
		<label for="_ct_sm_bible01_end_chap"><?php _e('End', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible01_end_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible01_end_chap); ?>" /> : <input type="text" name="_ct_sm_bible01_end_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible01_end_verse); ?>" />
	</div>

	<div class="meta_item">
		<label><?php _e('Passage 2', 'churchthemes'); ?></label>
		<select name="_ct_sm_bible02_book">
			<option value=""<?php if($sm_bible02_book=="") echo " selected";?>><?php _e('- Select Book -', 'churchthemes'); ?></option>
			<option value="Genesis"<?php if($sm_bible02_book=="Genesis") echo " selected";?>><?php _e('Genesis', 'churchthemes'); ?></option>
			<option value="Exodus"<?php if($sm_bible02_book=="Exodus") echo " selected";?>><?php _e('Exodus', 'churchthemes'); ?></option>
			<option value="Leviticus"<?php if($sm_bible02_book=="Leviticus") echo " selected";?>><?php _e('Leviticus', 'churchthemes'); ?></option>
			<option value="Numbers"<?php if($sm_bible02_book=="Numbers") echo " selected";?>><?php _e('Numbers', 'churchthemes'); ?></option>
			<option value="Deuteronomy"<?php if($sm_bible02_book=="Deuteronomy") echo " selected";?>><?php _e('Deuteronomy', 'churchthemes'); ?></option>
			<option value="Joshua"<?php if($sm_bible02_book=="Joshua") echo " selected";?>><?php _e('Joshua', 'churchthemes'); ?></option>
			<option value="Judges"<?php if($sm_bible02_book=="Judges") echo " selected";?>><?php _e('Judges', 'churchthemes'); ?></option>
			<option value="Ruth"<?php if($sm_bible02_book=="Ruth") echo " selected";?>><?php _e('Ruth', 'churchthemes'); ?></option>
			<option value="1 Samuel"<?php if($sm_bible02_book=="1 Samuel") echo " selected";?>><?php _e('1 Samuel', 'churchthemes'); ?></option>
			<option value="2 Samuel"<?php if($sm_bible02_book=="2 Samuel") echo " selected";?>><?php _e('2 Samuel', 'churchthemes'); ?></option>
			<option value="1 Kings"<?php if($sm_bible02_book=="1 Kings") echo " selected";?>><?php _e('1 Kings', 'churchthemes'); ?></option>
			<option value="2 Kings"<?php if($sm_bible02_book=="2 Kings") echo " selected";?>><?php _e('2 Kings', 'churchthemes'); ?></option>
			<option value="1 Chronicles"<?php if($sm_bible02_book=="1 Chronicles") echo " selected";?>><?php _e('1 Chronicles', 'churchthemes'); ?></option>
			<option value="2 Chronicles"<?php if($sm_bible02_book=="2 Chronicles") echo " selected";?>><?php _e('2 Chronicles', 'churchthemes'); ?></option>
			<option value="Ezra"<?php if($sm_bible02_book=="Ezra") echo " selected";?>><?php _e('Ezra', 'churchthemes'); ?></option>
			<option value="Nehemiah"<?php if($sm_bible02_book=="Nehemiah") echo " selected";?>><?php _e('Nehemiah', 'churchthemes'); ?></option>
			<option value="Esther"<?php if($sm_bible02_book=="Esther") echo " selected";?>><?php _e('Esther', 'churchthemes'); ?></option>
			<option value="Job"<?php if($sm_bible02_book=="Job") echo " selected";?>><?php _e('Job', 'churchthemes'); ?></option>
			<option value="Psalm"<?php if($sm_bible02_book=="Psalm") echo " selected";?>><?php _e('Psalm', 'churchthemes'); ?></option>
			<option value="Proverbs"<?php if($sm_bible02_book=="Proverbs") echo " selected";?>><?php _e('Proverbs', 'churchthemes'); ?></option>
			<option value="Ecclesiastes"<?php if($sm_bible02_book=="Ecclesiastes") echo " selected";?>><?php _e('Ecclesiastes', 'churchthemes'); ?></option>
			<option value="Song of Solomon"<?php if($sm_bible02_book=="Song of Solomon") echo " selected";?>><?php _e('Song of Solomon', 'churchthemes'); ?></option>
			<option value="Isaiah"<?php if($sm_bible02_book=="Isaiah") echo " selected";?>><?php _e('Isaiah', 'churchthemes'); ?></option>
			<option value="Jeremiah"<?php if($sm_bible02_book=="Jeremiah") echo " selected";?>><?php _e('Jeremiah', 'churchthemes'); ?></option>
			<option value="Lamentations"<?php if($sm_bible02_book=="Lamentations") echo " selected";?>><?php _e('Lamentations', 'churchthemes'); ?></option>
			<option value="Ezekiel"<?php if($sm_bible02_book=="Ezekiel") echo " selected";?>><?php _e('Ezekiel', 'churchthemes'); ?></option>
			<option value="Daniel"<?php if($sm_bible02_book=="Daniel") echo " selected";?>><?php _e('Daniel', 'churchthemes'); ?></option>
			<option value="Hosea"<?php if($sm_bible02_book=="Hosea") echo " selected";?>><?php _e('Hosea', 'churchthemes'); ?></option>
			<option value="Joel"<?php if($sm_bible02_book=="Joel") echo " selected";?>><?php _e('Joel', 'churchthemes'); ?></option>
			<option value="Amos"<?php if($sm_bible02_book=="Amos") echo " selected";?>><?php _e('Amos', 'churchthemes'); ?></option>
			<option value="Obadiah"<?php if($sm_bible02_book=="Obadiah") echo " selected";?>><?php _e('Obadiah', 'churchthemes'); ?></option>
			<option value="Jonah"<?php if($sm_bible02_book=="Jonah") echo " selected";?>><?php _e('Jonah', 'churchthemes'); ?></option>
			<option value="Micah"<?php if($sm_bible02_book=="Micah") echo " selected";?>><?php _e('Micah', 'churchthemes'); ?></option>
			<option value="Nahum"<?php if($sm_bible02_book=="Nahum") echo " selected";?>><?php _e('Nahum', 'churchthemes'); ?></option>
			<option value="Habakkuk"<?php if($sm_bible02_book=="Habakkuk") echo " selected";?>><?php _e('Habakkuk', 'churchthemes'); ?></option>
			<option value="Zephaniah"<?php if($sm_bible02_book=="Zephaniah") echo " selected";?>><?php _e('Zephaniah', 'churchthemes'); ?></option>
			<option value="Haggai"<?php if($sm_bible02_book=="Haggai") echo " selected";?>><?php _e('Haggai', 'churchthemes'); ?></option>
			<option value="Zechariah"<?php if($sm_bible02_book=="Zechariah") echo " selected";?>><?php _e('Zechariah', 'churchthemes'); ?></option>
			<option value="Malachi"<?php if($sm_bible02_book=="Malachi") echo " selected";?>><?php _e('Malachi', 'churchthemes'); ?></option>
			<option value="Matthew"<?php if($sm_bible02_book=="Matthew") echo " selected";?>><?php _e('Matthew', 'churchthemes'); ?></option>
			<option value="Mark"<?php if($sm_bible02_book=="Mark") echo " selected";?>><?php _e('Mark', 'churchthemes'); ?></option>
			<option value="Luke"<?php if($sm_bible02_book=="Luke") echo " selected";?>><?php _e('Luke', 'churchthemes'); ?></option>
			<option value="John"<?php if($sm_bible02_book=="John") echo " selected";?>><?php _e('John', 'churchthemes'); ?></option>
			<option value="Acts"<?php if($sm_bible02_book=="Acts") echo " selected";?>><?php _e('Acts', 'churchthemes'); ?></option>
			<option value="Romans"<?php if($sm_bible02_book=="Romans") echo " selected";?>><?php _e('Romans', 'churchthemes'); ?></option>
			<option value="1 Corinthians"<?php if($sm_bible02_book=="1 Corinthians") echo " selected";?>><?php _e('1 Corinthians', 'churchthemes'); ?></option>
			<option value="2 Corinthians"<?php if($sm_bible02_book=="2 Corinthians") echo " selected";?>><?php _e('2 Corinthians', 'churchthemes'); ?></option>
			<option value="Galatians"<?php if($sm_bible02_book=="Galatians") echo " selected";?>><?php _e('Galatians', 'churchthemes'); ?></option>
			<option value="Ephesians"<?php if($sm_bible02_book=="Ephesians") echo " selected";?>><?php _e('Ephesians', 'churchthemes'); ?></option>
			<option value="Philippians"<?php if($sm_bible02_book=="Philippians") echo " selected";?>><?php _e('Philippians', 'churchthemes'); ?></option>
			<option value="Colossians"<?php if($sm_bible02_book=="Colossians") echo " selected";?>><?php _e('Colossians', 'churchthemes'); ?></option>
			<option value="1 Thessalonians"<?php if($sm_bible02_book=="1 Thessalonians") echo " selected";?>><?php _e('1 Thessalonians', 'churchthemes'); ?></option>
			<option value="2 Thessalonians"<?php if($sm_bible02_book=="2 Thessalonians") echo " selected";?>><?php _e('2 Thessalonians', 'churchthemes'); ?></option>
			<option value="1 Timothy"<?php if($sm_bible02_book=="1 Timothy") echo " selected";?>><?php _e('1 Timothy', 'churchthemes'); ?></option>
			<option value="2 Timothy"<?php if($sm_bible02_book=="2 Timothy") echo " selected";?>><?php _e('2 Timothy', 'churchthemes'); ?></option>
			<option value="Titus"<?php if($sm_bible02_book=="Titus") echo " selected";?>><?php _e('Titus', 'churchthemes'); ?></option>
			<option value="Philemon"<?php if($sm_bible02_book=="Philemon") echo " selected";?>><?php _e('Philemon', 'churchthemes'); ?></option>
			<option value="Hebrews"<?php if($sm_bible02_book=="Hebrews") echo " selected";?>><?php _e('Hebrews', 'churchthemes'); ?></option>
			<option value="James"<?php if($sm_bible02_book=="James") echo " selected";?>><?php _e('James', 'churchthemes'); ?></option>
			<option value="1 Peter"<?php if($sm_bible02_book=="1 Peter") echo " selected";?>><?php _e('1 Peter', 'churchthemes'); ?></option>
			<option value="2 Peter"<?php if($sm_bible02_book=="2 Peter") echo " selected";?>><?php _e('2 Peter', 'churchthemes'); ?></option>
			<option value="1 John"<?php if($sm_bible02_book=="1 John") echo " selected";?>><?php _e('1 John', 'churchthemes'); ?></option>
			<option value="2 John"<?php if($sm_bible02_book=="2 John") echo " selected";?>><?php _e('2 John', 'churchthemes'); ?></option>
			<option value="3 John"<?php if($sm_bible02_book=="3 John") echo " selected";?>><?php _e('3 John', 'churchthemes'); ?></option>
			<option value="Jude"<?php if($sm_bible02_book=="Jude") echo " selected";?>><?php _e('Jude', 'churchthemes'); ?></option>
			<option value="Revelation"<?php if($sm_bible02_book=="Revelation") echo " selected";?>><?php _e('Revelation', 'churchthemes'); ?></option>
		</select>
	</div>

	<div class="meta_item verse_start">
		<label for="_ct_sm_bible02_start_chap"><?php _e('Start', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible02_start_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible02_start_chap); ?>" /> : <input type="text" name="_ct_sm_bible02_start_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible02_start_verse); ?>" />
	</div>

	<div class="meta_item verse_end">
		<label for="_ct_sm_bible02_end_chap"><?php _e('End', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible02_end_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible02_end_chap); ?>" /> : <input type="text" name="_ct_sm_bible02_end_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible02_end_verse); ?>" />
	</div>

	<div class="meta_item">
		<label><?php _e('Passage 3', 'churchthemes'); ?></label>
		<select name="_ct_sm_bible03_book">
			<option value=""<?php if($sm_bible03_book=="") echo " selected";?>><?php _e('- Select Book -', 'churchthemes'); ?></option>
			<option value="Genesis"<?php if($sm_bible03_book=="Genesis") echo " selected";?>><?php _e('Genesis', 'churchthemes'); ?></option>
			<option value="Exodus"<?php if($sm_bible03_book=="Exodus") echo " selected";?>><?php _e('Exodus', 'churchthemes'); ?></option>
			<option value="Leviticus"<?php if($sm_bible03_book=="Leviticus") echo " selected";?>><?php _e('Leviticus', 'churchthemes'); ?></option>
			<option value="Numbers"<?php if($sm_bible03_book=="Numbers") echo " selected";?>><?php _e('Numbers', 'churchthemes'); ?></option>
			<option value="Deuteronomy"<?php if($sm_bible03_book=="Deuteronomy") echo " selected";?>><?php _e('Deuteronomy', 'churchthemes'); ?></option>
			<option value="Joshua"<?php if($sm_bible03_book=="Joshua") echo " selected";?>><?php _e('Joshua', 'churchthemes'); ?></option>
			<option value="Judges"<?php if($sm_bible03_book=="Judges") echo " selected";?>><?php _e('Judges', 'churchthemes'); ?></option>
			<option value="Ruth"<?php if($sm_bible03_book=="Ruth") echo " selected";?>><?php _e('Ruth', 'churchthemes'); ?></option>
			<option value="1 Samuel"<?php if($sm_bible03_book=="1 Samuel") echo " selected";?>><?php _e('1 Samuel', 'churchthemes'); ?></option>
			<option value="2 Samuel"<?php if($sm_bible03_book=="2 Samuel") echo " selected";?>><?php _e('2 Samuel', 'churchthemes'); ?></option>
			<option value="1 Kings"<?php if($sm_bible03_book=="1 Kings") echo " selected";?>><?php _e('1 Kings', 'churchthemes'); ?></option>
			<option value="2 Kings"<?php if($sm_bible03_book=="2 Kings") echo " selected";?>><?php _e('2 Kings', 'churchthemes'); ?></option>
			<option value="1 Chronicles"<?php if($sm_bible03_book=="1 Chronicles") echo " selected";?>><?php _e('1 Chronicles', 'churchthemes'); ?></option>
			<option value="2 Chronicles"<?php if($sm_bible03_book=="2 Chronicles") echo " selected";?>><?php _e('2 Chronicles', 'churchthemes'); ?></option>
			<option value="Ezra"<?php if($sm_bible03_book=="Ezra") echo " selected";?>><?php _e('Ezra', 'churchthemes'); ?></option>
			<option value="Nehemiah"<?php if($sm_bible03_book=="Nehemiah") echo " selected";?>><?php _e('Nehemiah', 'churchthemes'); ?></option>
			<option value="Esther"<?php if($sm_bible03_book=="Esther") echo " selected";?>><?php _e('Esther', 'churchthemes'); ?></option>
			<option value="Job"<?php if($sm_bible03_book=="Job") echo " selected";?>><?php _e('Job', 'churchthemes'); ?></option>
			<option value="Psalm"<?php if($sm_bible03_book=="Psalm") echo " selected";?>><?php _e('Psalm', 'churchthemes'); ?></option>
			<option value="Proverbs"<?php if($sm_bible03_book=="Proverbs") echo " selected";?>><?php _e('Proverbs', 'churchthemes'); ?></option>
			<option value="Ecclesiastes"<?php if($sm_bible03_book=="Ecclesiastes") echo " selected";?>><?php _e('Ecclesiastes', 'churchthemes'); ?></option>
			<option value="Song of Solomon"<?php if($sm_bible03_book=="Song of Solomon") echo " selected";?>><?php _e('Song of Solomon', 'churchthemes'); ?></option>
			<option value="Isaiah"<?php if($sm_bible03_book=="Isaiah") echo " selected";?>><?php _e('Isaiah', 'churchthemes'); ?></option>
			<option value="Jeremiah"<?php if($sm_bible03_book=="Jeremiah") echo " selected";?>><?php _e('Jeremiah', 'churchthemes'); ?></option>
			<option value="Lamentations"<?php if($sm_bible03_book=="Lamentations") echo " selected";?>><?php _e('Lamentations', 'churchthemes'); ?></option>
			<option value="Ezekiel"<?php if($sm_bible03_book=="Ezekiel") echo " selected";?>><?php _e('Ezekiel', 'churchthemes'); ?></option>
			<option value="Daniel"<?php if($sm_bible03_book=="Daniel") echo " selected";?>><?php _e('Daniel', 'churchthemes'); ?></option>
			<option value="Hosea"<?php if($sm_bible03_book=="Hosea") echo " selected";?>><?php _e('Hosea', 'churchthemes'); ?></option>
			<option value="Joel"<?php if($sm_bible03_book=="Joel") echo " selected";?>><?php _e('Joel', 'churchthemes'); ?></option>
			<option value="Amos"<?php if($sm_bible03_book=="Amos") echo " selected";?>><?php _e('Amos', 'churchthemes'); ?></option>
			<option value="Obadiah"<?php if($sm_bible03_book=="Obadiah") echo " selected";?>><?php _e('Obadiah', 'churchthemes'); ?></option>
			<option value="Jonah"<?php if($sm_bible03_book=="Jonah") echo " selected";?>><?php _e('Jonah', 'churchthemes'); ?></option>
			<option value="Micah"<?php if($sm_bible03_book=="Micah") echo " selected";?>><?php _e('Micah', 'churchthemes'); ?></option>
			<option value="Nahum"<?php if($sm_bible03_book=="Nahum") echo " selected";?>><?php _e('Nahum', 'churchthemes'); ?></option>
			<option value="Habakkuk"<?php if($sm_bible03_book=="Habakkuk") echo " selected";?>><?php _e('Habakkuk', 'churchthemes'); ?></option>
			<option value="Zephaniah"<?php if($sm_bible03_book=="Zephaniah") echo " selected";?>><?php _e('Zephaniah', 'churchthemes'); ?></option>
			<option value="Haggai"<?php if($sm_bible03_book=="Haggai") echo " selected";?>><?php _e('Haggai', 'churchthemes'); ?></option>
			<option value="Zechariah"<?php if($sm_bible03_book=="Zechariah") echo " selected";?>><?php _e('Zechariah', 'churchthemes'); ?></option>
			<option value="Malachi"<?php if($sm_bible03_book=="Malachi") echo " selected";?>><?php _e('Malachi', 'churchthemes'); ?></option>
			<option value="Matthew"<?php if($sm_bible03_book=="Matthew") echo " selected";?>><?php _e('Matthew', 'churchthemes'); ?></option>
			<option value="Mark"<?php if($sm_bible03_book=="Mark") echo " selected";?>><?php _e('Mark', 'churchthemes'); ?></option>
			<option value="Luke"<?php if($sm_bible03_book=="Luke") echo " selected";?>><?php _e('Luke', 'churchthemes'); ?></option>
			<option value="John"<?php if($sm_bible03_book=="John") echo " selected";?>><?php _e('John', 'churchthemes'); ?></option>
			<option value="Acts"<?php if($sm_bible03_book=="Acts") echo " selected";?>><?php _e('Acts', 'churchthemes'); ?></option>
			<option value="Romans"<?php if($sm_bible03_book=="Romans") echo " selected";?>><?php _e('Romans', 'churchthemes'); ?></option>
			<option value="1 Corinthians"<?php if($sm_bible03_book=="1 Corinthians") echo " selected";?>><?php _e('1 Corinthians', 'churchthemes'); ?></option>
			<option value="2 Corinthians"<?php if($sm_bible03_book=="2 Corinthians") echo " selected";?>><?php _e('2 Corinthians', 'churchthemes'); ?></option>
			<option value="Galatians"<?php if($sm_bible03_book=="Galatians") echo " selected";?>><?php _e('Galatians', 'churchthemes'); ?></option>
			<option value="Ephesians"<?php if($sm_bible03_book=="Ephesians") echo " selected";?>><?php _e('Ephesians', 'churchthemes'); ?></option>
			<option value="Philippians"<?php if($sm_bible03_book=="Philippians") echo " selected";?>><?php _e('Philippians', 'churchthemes'); ?></option>
			<option value="Colossians"<?php if($sm_bible03_book=="Colossians") echo " selected";?>><?php _e('Colossians', 'churchthemes'); ?></option>
			<option value="1 Thessalonians"<?php if($sm_bible03_book=="1 Thessalonians") echo " selected";?>><?php _e('1 Thessalonians', 'churchthemes'); ?></option>
			<option value="2 Thessalonians"<?php if($sm_bible03_book=="2 Thessalonians") echo " selected";?>><?php _e('2 Thessalonians', 'churchthemes'); ?></option>
			<option value="1 Timothy"<?php if($sm_bible03_book=="1 Timothy") echo " selected";?>><?php _e('1 Timothy', 'churchthemes'); ?></option>
			<option value="2 Timothy"<?php if($sm_bible03_book=="2 Timothy") echo " selected";?>><?php _e('2 Timothy', 'churchthemes'); ?></option>
			<option value="Titus"<?php if($sm_bible03_book=="Titus") echo " selected";?>><?php _e('Titus', 'churchthemes'); ?></option>
			<option value="Philemon"<?php if($sm_bible03_book=="Philemon") echo " selected";?>><?php _e('Philemon', 'churchthemes'); ?></option>
			<option value="Hebrews"<?php if($sm_bible03_book=="Hebrews") echo " selected";?>><?php _e('Hebrews', 'churchthemes'); ?></option>
			<option value="James"<?php if($sm_bible03_book=="James") echo " selected";?>><?php _e('James', 'churchthemes'); ?></option>
			<option value="1 Peter"<?php if($sm_bible03_book=="1 Peter") echo " selected";?>><?php _e('1 Peter', 'churchthemes'); ?></option>
			<option value="2 Peter"<?php if($sm_bible03_book=="2 Peter") echo " selected";?>><?php _e('2 Peter', 'churchthemes'); ?></option>
			<option value="1 John"<?php if($sm_bible03_book=="1 John") echo " selected";?>><?php _e('1 John', 'churchthemes'); ?></option>
			<option value="2 John"<?php if($sm_bible03_book=="2 John") echo " selected";?>><?php _e('2 John', 'churchthemes'); ?></option>
			<option value="3 John"<?php if($sm_bible03_book=="3 John") echo " selected";?>><?php _e('3 John', 'churchthemes'); ?></option>
			<option value="Jude"<?php if($sm_bible03_book=="Jude") echo " selected";?>><?php _e('Jude', 'churchthemes'); ?></option>
			<option value="Revelation"<?php if($sm_bible03_book=="Revelation") echo " selected";?>><?php _e('Revelation', 'churchthemes'); ?></option>
		</select>
	</div>

	<div class="meta_item verse_start">
		<label for="_ct_sm_bible03_start_chap"><?php _e('Start', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible03_start_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible03_start_chap); ?>" /> : <input type="text" name="_ct_sm_bible03_start_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible03_start_verse); ?>" />
	</div>

	<div class="meta_item verse_end">
		<label for="_ct_sm_bible03_end_chap"><?php _e('End', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible03_end_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible03_end_chap); ?>" /> : <input type="text" name="_ct_sm_bible03_end_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible03_end_verse); ?>" />
	</div>

	<div class="meta_item">
		<label><?php _e('Passage 4', 'churchthemes'); ?></label>
		<select name="_ct_sm_bible04_book">
			<option value=""<?php if($sm_bible04_book=="") echo " selected";?>><?php _e('- Select Book -', 'churchthemes'); ?></option>
			<option value="Genesis"<?php if($sm_bible04_book=="Genesis") echo " selected";?>><?php _e('Genesis', 'churchthemes'); ?></option>
			<option value="Exodus"<?php if($sm_bible04_book=="Exodus") echo " selected";?>><?php _e('Exodus', 'churchthemes'); ?></option>
			<option value="Leviticus"<?php if($sm_bible04_book=="Leviticus") echo " selected";?>><?php _e('Leviticus', 'churchthemes'); ?></option>
			<option value="Numbers"<?php if($sm_bible04_book=="Numbers") echo " selected";?>><?php _e('Numbers', 'churchthemes'); ?></option>
			<option value="Deuteronomy"<?php if($sm_bible04_book=="Deuteronomy") echo " selected";?>><?php _e('Deuteronomy', 'churchthemes'); ?></option>
			<option value="Joshua"<?php if($sm_bible04_book=="Joshua") echo " selected";?>><?php _e('Joshua', 'churchthemes'); ?></option>
			<option value="Judges"<?php if($sm_bible04_book=="Judges") echo " selected";?>><?php _e('Judges', 'churchthemes'); ?></option>
			<option value="Ruth"<?php if($sm_bible04_book=="Ruth") echo " selected";?>><?php _e('Ruth', 'churchthemes'); ?></option>
			<option value="1 Samuel"<?php if($sm_bible04_book=="1 Samuel") echo " selected";?>><?php _e('1 Samuel', 'churchthemes'); ?></option>
			<option value="2 Samuel"<?php if($sm_bible04_book=="2 Samuel") echo " selected";?>><?php _e('2 Samuel', 'churchthemes'); ?></option>
			<option value="1 Kings"<?php if($sm_bible04_book=="1 Kings") echo " selected";?>><?php _e('1 Kings', 'churchthemes'); ?></option>
			<option value="2 Kings"<?php if($sm_bible04_book=="2 Kings") echo " selected";?>><?php _e('2 Kings', 'churchthemes'); ?></option>
			<option value="1 Chronicles"<?php if($sm_bible04_book=="1 Chronicles") echo " selected";?>><?php _e('1 Chronicles', 'churchthemes'); ?></option>
			<option value="2 Chronicles"<?php if($sm_bible04_book=="2 Chronicles") echo " selected";?>><?php _e('2 Chronicles', 'churchthemes'); ?></option>
			<option value="Ezra"<?php if($sm_bible04_book=="Ezra") echo " selected";?>><?php _e('Ezra', 'churchthemes'); ?></option>
			<option value="Nehemiah"<?php if($sm_bible04_book=="Nehemiah") echo " selected";?>><?php _e('Nehemiah', 'churchthemes'); ?></option>
			<option value="Esther"<?php if($sm_bible04_book=="Esther") echo " selected";?>><?php _e('Esther', 'churchthemes'); ?></option>
			<option value="Job"<?php if($sm_bible04_book=="Job") echo " selected";?>><?php _e('Job', 'churchthemes'); ?></option>
			<option value="Psalm"<?php if($sm_bible04_book=="Psalm") echo " selected";?>><?php _e('Psalm', 'churchthemes'); ?></option>
			<option value="Proverbs"<?php if($sm_bible04_book=="Proverbs") echo " selected";?>><?php _e('Proverbs', 'churchthemes'); ?></option>
			<option value="Ecclesiastes"<?php if($sm_bible04_book=="Ecclesiastes") echo " selected";?>><?php _e('Ecclesiastes', 'churchthemes'); ?></option>
			<option value="Song of Solomon"<?php if($sm_bible04_book=="Song of Solomon") echo " selected";?>><?php _e('Song of Solomon', 'churchthemes'); ?></option>
			<option value="Isaiah"<?php if($sm_bible04_book=="Isaiah") echo " selected";?>><?php _e('Isaiah', 'churchthemes'); ?></option>
			<option value="Jeremiah"<?php if($sm_bible04_book=="Jeremiah") echo " selected";?>><?php _e('Jeremiah', 'churchthemes'); ?></option>
			<option value="Lamentations"<?php if($sm_bible04_book=="Lamentations") echo " selected";?>><?php _e('Lamentations', 'churchthemes'); ?></option>
			<option value="Ezekiel"<?php if($sm_bible04_book=="Ezekiel") echo " selected";?>><?php _e('Ezekiel', 'churchthemes'); ?></option>
			<option value="Daniel"<?php if($sm_bible04_book=="Daniel") echo " selected";?>><?php _e('Daniel', 'churchthemes'); ?></option>
			<option value="Hosea"<?php if($sm_bible04_book=="Hosea") echo " selected";?>><?php _e('Hosea', 'churchthemes'); ?></option>
			<option value="Joel"<?php if($sm_bible04_book=="Joel") echo " selected";?>><?php _e('Joel', 'churchthemes'); ?></option>
			<option value="Amos"<?php if($sm_bible04_book=="Amos") echo " selected";?>><?php _e('Amos', 'churchthemes'); ?></option>
			<option value="Obadiah"<?php if($sm_bible04_book=="Obadiah") echo " selected";?>><?php _e('Obadiah', 'churchthemes'); ?></option>
			<option value="Jonah"<?php if($sm_bible04_book=="Jonah") echo " selected";?>><?php _e('Jonah', 'churchthemes'); ?></option>
			<option value="Micah"<?php if($sm_bible04_book=="Micah") echo " selected";?>><?php _e('Micah', 'churchthemes'); ?></option>
			<option value="Nahum"<?php if($sm_bible04_book=="Nahum") echo " selected";?>><?php _e('Nahum', 'churchthemes'); ?></option>
			<option value="Habakkuk"<?php if($sm_bible04_book=="Habakkuk") echo " selected";?>><?php _e('Habakkuk', 'churchthemes'); ?></option>
			<option value="Zephaniah"<?php if($sm_bible04_book=="Zephaniah") echo " selected";?>><?php _e('Zephaniah', 'churchthemes'); ?></option>
			<option value="Haggai"<?php if($sm_bible04_book=="Haggai") echo " selected";?>><?php _e('Haggai', 'churchthemes'); ?></option>
			<option value="Zechariah"<?php if($sm_bible04_book=="Zechariah") echo " selected";?>><?php _e('Zechariah', 'churchthemes'); ?></option>
			<option value="Malachi"<?php if($sm_bible04_book=="Malachi") echo " selected";?>><?php _e('Malachi', 'churchthemes'); ?></option>
			<option value="Matthew"<?php if($sm_bible04_book=="Matthew") echo " selected";?>><?php _e('Matthew', 'churchthemes'); ?></option>
			<option value="Mark"<?php if($sm_bible04_book=="Mark") echo " selected";?>><?php _e('Mark', 'churchthemes'); ?></option>
			<option value="Luke"<?php if($sm_bible04_book=="Luke") echo " selected";?>><?php _e('Luke', 'churchthemes'); ?></option>
			<option value="John"<?php if($sm_bible04_book=="John") echo " selected";?>><?php _e('John', 'churchthemes'); ?></option>
			<option value="Acts"<?php if($sm_bible04_book=="Acts") echo " selected";?>><?php _e('Acts', 'churchthemes'); ?></option>
			<option value="Romans"<?php if($sm_bible04_book=="Romans") echo " selected";?>><?php _e('Romans', 'churchthemes'); ?></option>
			<option value="1 Corinthians"<?php if($sm_bible04_book=="1 Corinthians") echo " selected";?>><?php _e('1 Corinthians', 'churchthemes'); ?></option>
			<option value="2 Corinthians"<?php if($sm_bible04_book=="2 Corinthians") echo " selected";?>><?php _e('2 Corinthians', 'churchthemes'); ?></option>
			<option value="Galatians"<?php if($sm_bible04_book=="Galatians") echo " selected";?>><?php _e('Galatians', 'churchthemes'); ?></option>
			<option value="Ephesians"<?php if($sm_bible04_book=="Ephesians") echo " selected";?>><?php _e('Ephesians', 'churchthemes'); ?></option>
			<option value="Philippians"<?php if($sm_bible04_book=="Philippians") echo " selected";?>><?php _e('Philippians', 'churchthemes'); ?></option>
			<option value="Colossians"<?php if($sm_bible04_book=="Colossians") echo " selected";?>><?php _e('Colossians', 'churchthemes'); ?></option>
			<option value="1 Thessalonians"<?php if($sm_bible04_book=="1 Thessalonians") echo " selected";?>><?php _e('1 Thessalonians', 'churchthemes'); ?></option>
			<option value="2 Thessalonians"<?php if($sm_bible04_book=="2 Thessalonians") echo " selected";?>><?php _e('2 Thessalonians', 'churchthemes'); ?></option>
			<option value="1 Timothy"<?php if($sm_bible04_book=="1 Timothy") echo " selected";?>><?php _e('1 Timothy', 'churchthemes'); ?></option>
			<option value="2 Timothy"<?php if($sm_bible04_book=="2 Timothy") echo " selected";?>><?php _e('2 Timothy', 'churchthemes'); ?></option>
			<option value="Titus"<?php if($sm_bible04_book=="Titus") echo " selected";?>><?php _e('Titus', 'churchthemes'); ?></option>
			<option value="Philemon"<?php if($sm_bible04_book=="Philemon") echo " selected";?>><?php _e('Philemon', 'churchthemes'); ?></option>
			<option value="Hebrews"<?php if($sm_bible04_book=="Hebrews") echo " selected";?>><?php _e('Hebrews', 'churchthemes'); ?></option>
			<option value="James"<?php if($sm_bible04_book=="James") echo " selected";?>><?php _e('James', 'churchthemes'); ?></option>
			<option value="1 Peter"<?php if($sm_bible04_book=="1 Peter") echo " selected";?>><?php _e('1 Peter', 'churchthemes'); ?></option>
			<option value="2 Peter"<?php if($sm_bible04_book=="2 Peter") echo " selected";?>><?php _e('2 Peter', 'churchthemes'); ?></option>
			<option value="1 John"<?php if($sm_bible04_book=="1 John") echo " selected";?>><?php _e('1 John', 'churchthemes'); ?></option>
			<option value="2 John"<?php if($sm_bible04_book=="2 John") echo " selected";?>><?php _e('2 John', 'churchthemes'); ?></option>
			<option value="3 John"<?php if($sm_bible04_book=="3 John") echo " selected";?>><?php _e('3 John', 'churchthemes'); ?></option>
			<option value="Jude"<?php if($sm_bible04_book=="Jude") echo " selected";?>><?php _e('Jude', 'churchthemes'); ?></option>
			<option value="Revelation"<?php if($sm_bible04_book=="Revelation") echo " selected";?>><?php _e('Revelation', 'churchthemes'); ?></option>
		</select>
	</div>

	<div class="meta_item verse_start">
		<label for="_ct_sm_bible04_start_chap"><?php _e('Start', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible04_start_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible04_start_chap); ?>" /> : <input type="text" name="_ct_sm_bible04_start_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible04_start_verse); ?>" />
	</div>

	<div class="meta_item verse_end">
		<label for="_ct_sm_bible04_end_chap"><?php _e('End', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible04_end_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible04_end_chap); ?>" /> : <input type="text" name="_ct_sm_bible04_end_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible04_end_verse); ?>" />
	</div>

	<div class="meta_item">
		<label><?php _e('Passage 5', 'churchthemes'); ?></label>
		<select name="_ct_sm_bible05_book">
			<option value=""<?php if($sm_bible05_book=="") echo " selected";?>><?php _e('- Select Book -', 'churchthemes'); ?></option>
			<option value="Genesis"<?php if($sm_bible05_book=="Genesis") echo " selected";?>><?php _e('Genesis', 'churchthemes'); ?></option>
			<option value="Exodus"<?php if($sm_bible05_book=="Exodus") echo " selected";?>><?php _e('Exodus', 'churchthemes'); ?></option>
			<option value="Leviticus"<?php if($sm_bible05_book=="Leviticus") echo " selected";?>><?php _e('Leviticus', 'churchthemes'); ?></option>
			<option value="Numbers"<?php if($sm_bible05_book=="Numbers") echo " selected";?>><?php _e('Numbers', 'churchthemes'); ?></option>
			<option value="Deuteronomy"<?php if($sm_bible05_book=="Deuteronomy") echo " selected";?>><?php _e('Deuteronomy', 'churchthemes'); ?></option>
			<option value="Joshua"<?php if($sm_bible05_book=="Joshua") echo " selected";?>><?php _e('Joshua', 'churchthemes'); ?></option>
			<option value="Judges"<?php if($sm_bible05_book=="Judges") echo " selected";?>><?php _e('Judges', 'churchthemes'); ?></option>
			<option value="Ruth"<?php if($sm_bible05_book=="Ruth") echo " selected";?>><?php _e('Ruth', 'churchthemes'); ?></option>
			<option value="1 Samuel"<?php if($sm_bible05_book=="1 Samuel") echo " selected";?>><?php _e('1 Samuel', 'churchthemes'); ?></option>
			<option value="2 Samuel"<?php if($sm_bible05_book=="2 Samuel") echo " selected";?>><?php _e('2 Samuel', 'churchthemes'); ?></option>
			<option value="1 Kings"<?php if($sm_bible05_book=="1 Kings") echo " selected";?>><?php _e('1 Kings', 'churchthemes'); ?></option>
			<option value="2 Kings"<?php if($sm_bible05_book=="2 Kings") echo " selected";?>><?php _e('2 Kings', 'churchthemes'); ?></option>
			<option value="1 Chronicles"<?php if($sm_bible05_book=="1 Chronicles") echo " selected";?>><?php _e('1 Chronicles', 'churchthemes'); ?></option>
			<option value="2 Chronicles"<?php if($sm_bible05_book=="2 Chronicles") echo " selected";?>><?php _e('2 Chronicles', 'churchthemes'); ?></option>
			<option value="Ezra"<?php if($sm_bible05_book=="Ezra") echo " selected";?>><?php _e('Ezra', 'churchthemes'); ?></option>
			<option value="Nehemiah"<?php if($sm_bible05_book=="Nehemiah") echo " selected";?>><?php _e('Nehemiah', 'churchthemes'); ?></option>
			<option value="Esther"<?php if($sm_bible05_book=="Esther") echo " selected";?>><?php _e('Esther', 'churchthemes'); ?></option>
			<option value="Job"<?php if($sm_bible05_book=="Job") echo " selected";?>><?php _e('Job', 'churchthemes'); ?></option>
			<option value="Psalm"<?php if($sm_bible05_book=="Psalm") echo " selected";?>><?php _e('Psalm', 'churchthemes'); ?></option>
			<option value="Proverbs"<?php if($sm_bible05_book=="Proverbs") echo " selected";?>><?php _e('Proverbs', 'churchthemes'); ?></option>
			<option value="Ecclesiastes"<?php if($sm_bible05_book=="Ecclesiastes") echo " selected";?>><?php _e('Ecclesiastes', 'churchthemes'); ?></option>
			<option value="Song of Solomon"<?php if($sm_bible05_book=="Song of Solomon") echo " selected";?>><?php _e('Song of Solomon', 'churchthemes'); ?></option>
			<option value="Isaiah"<?php if($sm_bible05_book=="Isaiah") echo " selected";?>><?php _e('Isaiah', 'churchthemes'); ?></option>
			<option value="Jeremiah"<?php if($sm_bible05_book=="Jeremiah") echo " selected";?>><?php _e('Jeremiah', 'churchthemes'); ?></option>
			<option value="Lamentations"<?php if($sm_bible05_book=="Lamentations") echo " selected";?>><?php _e('Lamentations', 'churchthemes'); ?></option>
			<option value="Ezekiel"<?php if($sm_bible05_book=="Ezekiel") echo " selected";?>><?php _e('Ezekiel', 'churchthemes'); ?></option>
			<option value="Daniel"<?php if($sm_bible05_book=="Daniel") echo " selected";?>><?php _e('Daniel', 'churchthemes'); ?></option>
			<option value="Hosea"<?php if($sm_bible05_book=="Hosea") echo " selected";?>><?php _e('Hosea', 'churchthemes'); ?></option>
			<option value="Joel"<?php if($sm_bible05_book=="Joel") echo " selected";?>><?php _e('Joel', 'churchthemes'); ?></option>
			<option value="Amos"<?php if($sm_bible05_book=="Amos") echo " selected";?>><?php _e('Amos', 'churchthemes'); ?></option>
			<option value="Obadiah"<?php if($sm_bible05_book=="Obadiah") echo " selected";?>><?php _e('Obadiah', 'churchthemes'); ?></option>
			<option value="Jonah"<?php if($sm_bible05_book=="Jonah") echo " selected";?>><?php _e('Jonah', 'churchthemes'); ?></option>
			<option value="Micah"<?php if($sm_bible05_book=="Micah") echo " selected";?>><?php _e('Micah', 'churchthemes'); ?></option>
			<option value="Nahum"<?php if($sm_bible05_book=="Nahum") echo " selected";?>><?php _e('Nahum', 'churchthemes'); ?></option>
			<option value="Habakkuk"<?php if($sm_bible05_book=="Habakkuk") echo " selected";?>><?php _e('Habakkuk', 'churchthemes'); ?></option>
			<option value="Zephaniah"<?php if($sm_bible05_book=="Zephaniah") echo " selected";?>><?php _e('Zephaniah', 'churchthemes'); ?></option>
			<option value="Haggai"<?php if($sm_bible05_book=="Haggai") echo " selected";?>><?php _e('Haggai', 'churchthemes'); ?></option>
			<option value="Zechariah"<?php if($sm_bible05_book=="Zechariah") echo " selected";?>><?php _e('Zechariah', 'churchthemes'); ?></option>
			<option value="Malachi"<?php if($sm_bible05_book=="Malachi") echo " selected";?>><?php _e('Malachi', 'churchthemes'); ?></option>
			<option value="Matthew"<?php if($sm_bible05_book=="Matthew") echo " selected";?>><?php _e('Matthew', 'churchthemes'); ?></option>
			<option value="Mark"<?php if($sm_bible05_book=="Mark") echo " selected";?>><?php _e('Mark', 'churchthemes'); ?></option>
			<option value="Luke"<?php if($sm_bible05_book=="Luke") echo " selected";?>><?php _e('Luke', 'churchthemes'); ?></option>
			<option value="John"<?php if($sm_bible05_book=="John") echo " selected";?>><?php _e('John', 'churchthemes'); ?></option>
			<option value="Acts"<?php if($sm_bible05_book=="Acts") echo " selected";?>><?php _e('Acts', 'churchthemes'); ?></option>
			<option value="Romans"<?php if($sm_bible05_book=="Romans") echo " selected";?>><?php _e('Romans', 'churchthemes'); ?></option>
			<option value="1 Corinthians"<?php if($sm_bible05_book=="1 Corinthians") echo " selected";?>><?php _e('1 Corinthians', 'churchthemes'); ?></option>
			<option value="2 Corinthians"<?php if($sm_bible05_book=="2 Corinthians") echo " selected";?>><?php _e('2 Corinthians', 'churchthemes'); ?></option>
			<option value="Galatians"<?php if($sm_bible05_book=="Galatians") echo " selected";?>><?php _e('Galatians', 'churchthemes'); ?></option>
			<option value="Ephesians"<?php if($sm_bible05_book=="Ephesians") echo " selected";?>><?php _e('Ephesians', 'churchthemes'); ?></option>
			<option value="Philippians"<?php if($sm_bible05_book=="Philippians") echo " selected";?>><?php _e('Philippians', 'churchthemes'); ?></option>
			<option value="Colossians"<?php if($sm_bible05_book=="Colossians") echo " selected";?>><?php _e('Colossians', 'churchthemes'); ?></option>
			<option value="1 Thessalonians"<?php if($sm_bible05_book=="1 Thessalonians") echo " selected";?>><?php _e('1 Thessalonians', 'churchthemes'); ?></option>
			<option value="2 Thessalonians"<?php if($sm_bible05_book=="2 Thessalonians") echo " selected";?>><?php _e('2 Thessalonians', 'churchthemes'); ?></option>
			<option value="1 Timothy"<?php if($sm_bible05_book=="1 Timothy") echo " selected";?>><?php _e('1 Timothy', 'churchthemes'); ?></option>
			<option value="2 Timothy"<?php if($sm_bible05_book=="2 Timothy") echo " selected";?>><?php _e('2 Timothy', 'churchthemes'); ?></option>
			<option value="Titus"<?php if($sm_bible05_book=="Titus") echo " selected";?>><?php _e('Titus', 'churchthemes'); ?></option>
			<option value="Philemon"<?php if($sm_bible05_book=="Philemon") echo " selected";?>><?php _e('Philemon', 'churchthemes'); ?></option>
			<option value="Hebrews"<?php if($sm_bible05_book=="Hebrews") echo " selected";?>><?php _e('Hebrews', 'churchthemes'); ?></option>
			<option value="James"<?php if($sm_bible05_book=="James") echo " selected";?>><?php _e('James', 'churchthemes'); ?></option>
			<option value="1 Peter"<?php if($sm_bible05_book=="1 Peter") echo " selected";?>><?php _e('1 Peter', 'churchthemes'); ?></option>
			<option value="2 Peter"<?php if($sm_bible05_book=="2 Peter") echo " selected";?>><?php _e('2 Peter', 'churchthemes'); ?></option>
			<option value="1 John"<?php if($sm_bible05_book=="1 John") echo " selected";?>><?php _e('1 John', 'churchthemes'); ?></option>
			<option value="2 John"<?php if($sm_bible05_book=="2 John") echo " selected";?>><?php _e('2 John', 'churchthemes'); ?></option>
			<option value="3 John"<?php if($sm_bible05_book=="3 John") echo " selected";?>><?php _e('3 John', 'churchthemes'); ?></option>
			<option value="Jude"<?php if($sm_bible05_book=="Jude") echo " selected";?>><?php _e('Jude', 'churchthemes'); ?></option>
			<option value="Revelation"<?php if($sm_bible05_book=="Revelation") echo " selected";?>><?php _e('Revelation', 'churchthemes'); ?></option>
		</select>
	</div>

	<div class="meta_item verse_start">
		<label for="_ct_sm_bible05_start_chap"><?php _e('Start', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible05_start_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible05_start_chap); ?>" /> : <input type="text" name="_ct_sm_bible05_start_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible05_start_verse); ?>" />
	</div>

	<div class="meta_item verse_end">
		<label for="_ct_sm_bible05_end_chap"><?php _e('End', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_bible05_end_chap" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible05_end_chap); ?>" /> : <input type="text" name="_ct_sm_bible05_end_verse" size="5" autocomplete="on" value="<?php echo esc_attr($sm_bible05_end_verse); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Audio Content', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('These fields are required for your Podcast RSS Feed to validate.', 'churchthemes'); ?><br /><a href="edit.php?post_type=ct_sermon&page=podcast-settings"><?php _e('View Podcast Settings', 'churchthemes'); ?></a></p>

	<div class="meta_item">
		<label for="_ct_sm_audio_file"><?php _e('Audio Source', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_audio_file" size="70" autocomplete="on" placeholder="e.g. http://mychurch.org/wp-content/sermons/audio/2011.01.01_service_speaker.mp3" value="<?php echo esc_url($sm_audio_file); ?>" />
		<input id="upload_audio" type="button" class="thickbox button rbutton" value="Upload File" />
		<span><?php _e('Enter the URL of the audio file (must be an MP3).', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sm_audio_length"><?php _e('Audio Length', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_audio_length" size="10" autocomplete="on" placeholder="e.g. 55:36" value="<?php echo esc_attr($sm_audio_length); ?>" />
		<span><?php _e('The Audio Length is the duration of playback in hours, minutes and seconds (hh:mm:ss).', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sm_audio_button_text"><?php _e('Button Text', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_audio_button_text" size="30" autocomplete="on" placeholder="<?php _e('e.g. Audio (MP3)', 'churchthemes'); ?>" value="<?php echo esc_attr($sm_audio_button_text); ?>" />
		<span><?php _e('Enter the text for the audio download button.', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Video Content', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('Enter the embed code provided by your video service (such as Vimeo or YouTube) below. This field can also accept shortcodes.', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_sm_video_embed"><?php _e('Embed Code', 'churchthemes'); ?></label>
		<textarea name="_ct_sm_video_embed" cols="60" rows="8" placeholder="e.g. &lt;iframe src=&quot;http://player.vimeo.com/video/26069328?title=0..."><?php echo esc_textarea($sm_video_embed); ?></textarea>
		<span><?php _e('Embed your video using a width of 608 pixels.', 'churchthemes'); ?></span>
	</div>

	<p class="meta_info clear"><br /><br /><?php _e('The fields below are required if you would like to give users the option to download the video file.', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_sm_video_file"><?php _e('Video Source', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_video_file" size="70" autocomplete="on" placeholder="e.g. http://mychurch.org/wp-content/sermons/video/2011.01.01_service_speaker.mp4" value="<?php echo esc_url($sm_video_file); ?>" />
		<input id="upload_video" type="button" class="thickbox button rbutton" value="Upload File" />
		<span><?php _e('Enter the URL of the video file (M4V or MP4 recommended).', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sm_video_button_text"><?php _e('Button Text', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_video_button_text" size="30" autocomplete="on" placeholder="<?php _e('e.g. Video (MP4)', 'churchthemes'); ?>" value="<?php echo esc_attr($sm_video_button_text); ?>" />
		<span><?php _e('Enter the text for the video download button.', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Document', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('These fields are required to display a downloadable document.', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_sm_sg_file"><?php _e('Document File', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_sg_file" size="70" autocomplete="on" placeholder="e.g. http://mychurch.org/wp-content/sermons/docs/2011.01.01_study_guide.pdf" value="<?php echo esc_url($sm_sg_file); ?>" />
		<input id="upload_doc" type="button" class="thickbox button rbutton" value="Upload File" />
		<span><?php _e('Enter the URL of the document file.', 'churchthemes'); ?></span>
	</div>

	<div class="meta_item">
		<label for="_ct_sm_sg_button_text"><?php _e('Button Text', 'churchthemes'); ?></label>
		<input type="text" name="_ct_sm_sg_button_text" size="30" autocomplete="on" placeholder="<?php _e('e.g. Study Guide', 'churchthemes'); ?>" value="<?php echo esc_attr($sm_sg_button_text); ?>" />
		<span><?php _e('Enter the text for the document download button.', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('More', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_sm_notes">
			<?php _e('Admin Notes', 'churchthemes'); ?>
			<br /><br />
			<span class="label_note"><?php _e('Not Published', 'churchthemes'); ?></span>
		</label>
		<textarea type="text" name="_ct_sm_notes" cols="60" rows="8"><?php echo esc_textarea($sm_notes); ?></textarea>
	</div>

	<div class="meta_clear"></div>

<?php
// End HTML
}

// Save Custom Field Values
add_action('save_post', 'save_ct_sm_meta');

function save_ct_sm_meta(){

	global $post_id;

	$allowed_html = array(
		'iframe' => array(
			'width' => array(),
			'height' => array(),
			'frameborder' => array(),
			'allowfullscreen' => array(),
			'src' => array(),
		),
		'object' => array(
			'width' => array(),
			'height' => array(),
		),
		'param' => array(
			'name' => array(),
			'value' => array(),
		),
		'embed' => array(
			'src' => array(),
			'type' => array(),
			'width' => array(),
			'height' => array(),
			'allowscriptaccess' => array(),
			'allowfullscreen' => array(),
		),
	);

	if(isset($_POST['post_type']) && ($_POST['post_type'] == "ct_sermon")):

		$sm_bible01_book = wp_filter_nohtml_kses( $_POST['_ct_sm_bible01_book'] );
		update_post_meta($post_id, '_ct_sm_bible01_book', $sm_bible01_book);

		$sm_bible01_start_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible01_start_chap'] );
		update_post_meta($post_id, '_ct_sm_bible01_start_chap', $sm_bible01_start_chap);

		$sm_bible01_start_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible01_start_verse'] );
		update_post_meta($post_id, '_ct_sm_bible01_start_verse', $sm_bible01_start_verse);

		$sm_bible01_end_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible01_end_chap'] );
		update_post_meta($post_id, '_ct_sm_bible01_end_chap', $sm_bible01_end_chap);

		$sm_bible01_end_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible01_end_verse'] );
		update_post_meta($post_id, '_ct_sm_bible01_end_verse', $sm_bible01_end_verse);

		$sm_bible02_book = wp_filter_nohtml_kses( $_POST['_ct_sm_bible02_book'] );
		update_post_meta($post_id, '_ct_sm_bible02_book', $sm_bible02_book);

		$sm_bible02_start_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible02_start_chap'] );
		update_post_meta($post_id, '_ct_sm_bible02_start_chap', $sm_bible02_start_chap);

		$sm_bible02_start_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible02_start_verse'] );
		update_post_meta($post_id, '_ct_sm_bible02_start_verse', $sm_bible02_start_verse);

		$sm_bible02_end_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible02_end_chap'] );
		update_post_meta($post_id, '_ct_sm_bible02_end_chap', $sm_bible02_end_chap);

		$sm_bible02_end_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible02_end_verse'] );
		update_post_meta($post_id, '_ct_sm_bible02_end_verse', $sm_bible02_end_verse);

		$sm_bible03_book = wp_filter_nohtml_kses( $_POST['_ct_sm_bible03_book'] );
		update_post_meta($post_id, '_ct_sm_bible03_book', $sm_bible03_book);

		$sm_bible03_start_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible03_start_chap'] );
		update_post_meta($post_id, '_ct_sm_bible03_start_chap', $sm_bible03_start_chap);

		$sm_bible03_start_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible03_start_verse'] );
		update_post_meta($post_id, '_ct_sm_bible03_start_verse', $sm_bible03_start_verse);

		$sm_bible03_end_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible03_end_chap'] );
		update_post_meta($post_id, '_ct_sm_bible03_end_chap', $sm_bible03_end_chap);

		$sm_bible03_end_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible03_end_verse'] );
		update_post_meta($post_id, '_ct_sm_bible03_end_verse', $sm_bible03_end_verse);

		$sm_bible04_book = wp_filter_nohtml_kses( $_POST['_ct_sm_bible04_book'] );
		update_post_meta($post_id, '_ct_sm_bible04_book', $sm_bible04_book);

		$sm_bible04_start_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible04_start_chap'] );
		update_post_meta($post_id, '_ct_sm_bible04_start_chap', $sm_bible04_start_chap);

		$sm_bible04_start_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible04_start_verse'] );
		update_post_meta($post_id, '_ct_sm_bible04_start_verse', $sm_bible04_start_verse);

		$sm_bible04_end_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible04_end_chap'] );
		update_post_meta($post_id, '_ct_sm_bible04_end_chap', $sm_bible04_end_chap);

		$sm_bible04_end_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible04_end_verse'] );
		update_post_meta($post_id, '_ct_sm_bible04_end_verse', $sm_bible04_end_verse);

		$sm_bible05_book = wp_filter_nohtml_kses( $_POST['_ct_sm_bible05_book'] );
		update_post_meta($post_id, '_ct_sm_bible05_book', $sm_bible05_book);

		$sm_bible05_start_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible05_start_chap'] );
		update_post_meta($post_id, '_ct_sm_bible05_start_chap', $sm_bible05_start_chap);

		$sm_bible05_start_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible05_start_verse'] );
		update_post_meta($post_id, '_ct_sm_bible05_start_verse', $sm_bible05_start_verse);

		$sm_bible05_end_chap = wp_filter_nohtml_kses( $_POST['_ct_sm_bible05_end_chap'] );
		update_post_meta($post_id, '_ct_sm_bible05_end_chap', $sm_bible05_end_chap);

		$sm_bible05_end_verse = wp_filter_nohtml_kses( $_POST['_ct_sm_bible05_end_verse'] );
		update_post_meta($post_id, '_ct_sm_bible05_end_verse', $sm_bible05_end_verse);

		$sm_audio_file = esc_url_raw( $_POST['_ct_sm_audio_file'] );
		update_post_meta($post_id, '_ct_sm_audio_file', $sm_audio_file);

		$sm_audio_length = wp_filter_nohtml_kses( $_POST['_ct_sm_audio_length'] );
		update_post_meta($post_id, '_ct_sm_audio_length', $sm_audio_length);

		$sm_audio_button_text = wp_filter_nohtml_kses( $_POST['_ct_sm_audio_button_text'] );
		update_post_meta($post_id, '_ct_sm_audio_button_text', $sm_audio_button_text);

		$sm_video_embed = wp_kses( $_POST['_ct_sm_video_embed'], $allowed_html );
		update_post_meta($post_id, '_ct_sm_video_embed', $sm_video_embed);

		$sm_video_file = esc_url_raw( $_POST['_ct_sm_video_file'] );
		update_post_meta($post_id, '_ct_sm_video_file', $sm_video_file);

		$sm_video_button_text = wp_filter_nohtml_kses( $_POST['_ct_sm_video_button_text'] );
		update_post_meta($post_id, '_ct_sm_video_button_text', $sm_video_button_text);

		$sm_sg_file = esc_url_raw( $_POST['_ct_sm_sg_file'] );
		update_post_meta($post_id, '_ct_sm_sg_file', $sm_sg_file);

		$sm_sg_button_text = wp_filter_nohtml_kses( $_POST['_ct_sm_sg_button_text'] );
		update_post_meta($post_id, '_ct_sm_sg_button_text', $sm_sg_button_text);

		$sm_notes = wp_filter_nohtml_kses( $_POST['_ct_sm_notes'] );
		update_post_meta($post_id, '_ct_sm_notes', $sm_notes);

	endif;
}
// End Custom Field Values
// End Sermon Options Box

// Custom Columns
function sm_register_columns($columns){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', 'churchthemes'),
			'sm_speaker' => __('Speaker', 'churchthemes'),
			'sm_series' => __('Series', 'churchthemes'),
			'sm_service' => __('Service', 'churchthemes'),
			'sm_topic' => __('Topic', 'churchthemes'),
			'sm_views' => __('Views', 'churchthemes'),
			'sm_image' => __('Featured Image', 'churchthemes'),
		);
		return $columns;
}
add_filter('manage_edit-ct_sermon_columns', 'sm_register_columns');

function sm_display_columns($column){
		global $post;
		$custom = get_post_custom();
		switch ($column)
		{
			case 'sm_speaker':
				echo get_the_term_list($post->ID, 'sermon_speaker', '', ', ', '');
				break;
			case 'sm_service':
				echo get_the_term_list($post->ID, 'sermon_service', '', ', ', '');
				break;
			case 'sm_series':
				echo get_the_term_list($post->ID, 'sermon_series', '', ', ', '');
				break;
			case 'sm_topic':
				echo get_the_term_list($post->ID, 'sermon_topic', '', ', ', '');
				break;
			case 'sm_views':
				$meta_views = isset( $custom['Views'][0] ) ? $custom['Views'][0] : '0';
				echo $meta_views;
				break;
			case 'sm_image':
				echo get_the_post_thumbnail($post->ID, 'admin');
				break;
		}
}
add_action('manage_posts_custom_column', 'sm_display_columns');

// End Custom Columns

// Create Shortcodes

add_shortcode("sermons", "ct_sc_sermons");

class ChurchThemes_Sermon_Shortcode {

	static $add_script;

	function init() {
		add_shortcode('sermons', array(__CLASS__, 'ct_sc_sermons'));
	}

	function ct_sc_sermons($atts, $content = null) {
		extract(shortcode_atts(
			array(
				// Default behaviors if values aren't specified
				'id' => '',
				'num' => get_option( 'posts_per_page' ),
				'paging' => 'show',
				'speaker' => '',
				'service' => '',
				'series' => '',
				'topic' => '',
				'orderby' => 'date',
				'order' => 'DESC',
				'images' => 'show',
			), $atts));

		global $post;

		if($orderby == 'views'): $orderby = 'meta_value_num'; endif;
		if($paging == 'hide'):
			$paged = null;
		else:
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		endif;

if($orderby == 'meta_value_num'):
		$args=array(
			'post_type' => 'ct_sermon',
			'post_status' => 'publish',
			'paged' => $paged,
			'p' => $id,
			'posts_per_page' => $num,
			'sermon_speaker' => $speaker,
			'sermon_services' => $service,
			'sermon_series' => $series,
			'sermon_topic' => $topic,
			'meta_key' => 'Views',
			'orderby' => $orderby,
			'order' => $order,
		);
else:
		$args=array(
			'post_type' => 'ct_sermon',
			'post_status' => 'publish',
			'paged' => $paged,
			'p' => $id,
			'posts_per_page' => $num,
			'sermon_speaker' => $speaker,
			'sermon_services' => $service,
			'sermon_series' => $series,
			'sermon_topic' => $topic,
			'orderby' => $orderby,
			'order' => $order,
		);
endif;

		query_posts($args);

		ob_start();
		if ( $images != 'hide' ) {
			include('shortcode-sermons.php');
		}
		else {
			include('shortcode-sermons-noimage.php');
		}
		if($paging != 'hide') {
			pagination();
		}
		wp_reset_query();
		$content = ob_get_clean();
		return $content;

	}
}

ChurchThemes_Sermon_Shortcode::init();

// End Shortcodes

/* END SERMON */


/* LOCATION */

// Register Post Type
add_action('init', 'loc_register');

function loc_register() {
	$labels = array(
		'name' => ( 'Locations' ),
		'singular_name' => ( 'Location' ),
		'add_new' => _x( 'Add New', 'ct_location' ),
		'add_new_item' => __( 'Add New Location' ),
		'edit_item' => __( 'Edit Location' ),
		'new_item' => __( 'New Location' ),
		'view_item' => __( 'View Location' ),
		'search_items' => __( 'Search Location' ),
		'not_found' =>  __( 'No Locations found' ),
		'not_found_in_trash' => __( 'No Locations found in Trash' ),
		'parent_item_colon' => ''
	);

	$location_settings = get_option('ct_location_settings');
	$archive_slug = $location_settings['archive_slug'];
	if( empty($archive_slug) && function_exists('em_load_event') ):
		$archive_slug = 'location';
	elseif(empty($archive_slug)):
		$archive_slug = 'locations';
	endif;

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'has_archive' => $archive_slug,
		'query_var' => true,
		'rewrite' => array('slug' => $archive_slug),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 10,
		'menu_icon' => get_template_directory_uri() . '/lib/admin/images/menu_icon-location-16.png',
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'comments' )
	);

	register_post_type( 'ct_location' , $args );

	flush_rewrite_rules(false);

}
// End Register Post Type

// Create Custom Taxonomies
add_action( 'init', 'create_location_taxonomies', 0 );

function create_location_taxonomies() {

	// Location Tags Taxonomy (Non-Hierarchical)
	$labels = array(
		'name' => _x( 'Location Tags', 'taxonomy general name' ),
		'singular_name' => _x( 'Location Tag', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Location Tags' ),
		'all_items' => __( 'All Location Tags' ),
		'parent_item' => __( 'Parent Location Tag' ),
		'parent_item_colon' => __( 'Parent Location Tag:' ),
		'edit_item' => __( 'Edit Location Tag' ),
		'update_item' => __( 'Update Location Tag' ),
		'add_new_item' => __( 'Add New Location Tag' ),
		'new_item_name' => __( 'New Location Tag Name' ),
	);
	register_taxonomy( 'location_tag', array( 'ct_location' ), array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'location_tag' ),
	));
	// End Location Tags Taxonomy

}
// End Custom Taxonomies

// Submenu
add_action('admin_menu', 'loc_submenu');

function loc_submenu() {

	// Add to end of admin_menu action function
	global $submenu;
	$submenu['edit.php?post_type=ct_location'][5][0] = __('All Locations');
	$post_type_object = get_post_type_object('ct_location');
	$post_type_object->labels->name = "Locations";

}
// End Submenu

// Create Location Options Box
add_action("admin_init", "loc_admin_init");

function loc_admin_init(){
    add_meta_box("loc_meta", "Location Options", "loc_meta_options", "ct_location", "normal", "core");
}

// Custom Field Keys
function loc_meta_options(){
	global $post;
	$custom = get_post_custom($post->ID);
	isset($custom["_ct_loc_address1"][0]) ? $loc_address1 = $custom["_ct_loc_address1"][0] : $loc_address1 = null;
	isset($custom["_ct_loc_address2"][0]) ? $loc_address2 = $custom["_ct_loc_address2"][0] : $loc_address2 = null;
	isset($custom["_ct_loc_address3"][0]) ? $loc_address3 = $custom["_ct_loc_address3"][0] : $loc_address3 = null;
	isset($custom["_ct_loc_map_link"][0]) ? $loc_map_link = $custom["_ct_loc_map_link"][0] : $loc_map_link = null;
	isset($custom["_ct_loc_map_code"][0]) ? $loc_map_code = $custom["_ct_loc_map_code"][0] : $loc_map_code = null;
	isset($custom["_ct_loc_service1"][0]) ? $loc_service1 = $custom["_ct_loc_service1"][0] : $loc_service1 = null;
	isset($custom["_ct_loc_service2"][0]) ? $loc_service2 = $custom["_ct_loc_service2"][0] : $loc_service2 = null;
	isset($custom["_ct_loc_service3"][0]) ? $loc_service3 = $custom["_ct_loc_service3"][0] : $loc_service3 = null;
	isset($custom["_ct_loc_service4"][0]) ? $loc_service4 = $custom["_ct_loc_service4"][0] : $loc_service4 = null;
	isset($custom["_ct_loc_service5"][0]) ? $loc_service5 = $custom["_ct_loc_service5"][0] : $loc_service5 = null;
	isset($custom["_ct_loc_notes"][0]) ? $loc_notes = $custom["_ct_loc_notes"][0] : $loc_notes = null;
// End Custom Field Keys

// Start HTML
?>

	<h2 class="meta_section"><?php _e('Featured Image', 'churchthemes'); ?></h2>

	<div class="meta_item first">
		<a title="Set Featured Image" href="media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=285" id="set-post-thumbnail" class="thickbox button rbutton"><?php _e('Set Featured Image', 'churchthemes'); ?></a>
		<br />
		<span><?php _e('To ensure the best image quality possible, please use a JPG image that is at least 400 x 400 (pixels)', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Location', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_loc_address1"><?php _e('Address Line 1', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_address1" size="40" autocomplete="on" value="<?php echo esc_attr($loc_address1); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_address2"><?php _e('Address Line 2', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_address2" size="40" autocomplete="on" value="<?php echo esc_attr($loc_address2); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_address3"><?php _e('Address Line 3', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_address3" size="40" autocomplete="on" value="<?php echo esc_attr($loc_address3); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_map_link"><?php _e('Map Link', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_map_link" size="70" autocomplete="on" placeholder="e.g. http://maps.google.com/maps?q=Our+Church,+1234+Main+St,+Anywhere,+CA+12345+USA" value="<?php echo esc_url($loc_map_link); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_map_code"><?php _e('Map Embed Code', 'churchthemes'); ?></label>
		<textarea name="_ct_loc_map_code" cols="60" rows="8" autocomplete="on" placeholder="&lt;iframe width=&quot;608&quot; height=&quot;342&quot; frameborder=&quot;0&quot; scrolling=&quot;no&quot; margi..."><?php echo esc_textarea($loc_map_code); ?></textarea>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Service Times', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('Enter up to five services per location (blank fields will be ignored)', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_loc_service1"><?php _e('Service 1', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_service1" size="20" autocomplete="on" value="<?php echo esc_attr($loc_service1); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_service2"><?php _e('Service 2', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_service2" size="20" autocomplete="on" value="<?php echo esc_attr($loc_service2); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_service3"><?php _e('Service 3', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_service3" size="20" autocomplete="on" value="<?php echo esc_attr($loc_service3); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_service4"><?php _e('Service 4', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_service4" size="20" autocomplete="on" value="<?php echo esc_attr($loc_service4); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_loc_service5"><?php _e('Service 5', 'churchthemes'); ?></label>
		<input type="text" name="_ct_loc_service5" size="20" autocomplete="on" value="<?php echo esc_attr($loc_service5); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('More', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_loc_notes">
			<?php _e('Admin Notes', 'churchthemes'); ?>
			<br /><br />
			<span class="label_note"><?php _e('Not Published', 'churchthemes'); ?></span>
		</label>
		<textarea type="text" name="_ct_loc_notes" cols="60" rows="8"><?php echo esc_textarea($loc_notes); ?></textarea>
	</div>

	<div class="meta_clear"></div>

<?php
// End HTML
}

// Save Custom Field Values
add_action('save_post', 'save_ct_loc_meta');

function save_ct_loc_meta(){

	global $post_id, $allowedtags;

	$allowed_html = array_merge_recursive( $allowedtags, array(
		'iframe' => array(
			'width' => array(),
			'height' => array(),
			'frameborder' => array(),
			'scrolling' => array(),
			'marginheight' => array(),
			'marginwidth' => array(),
			'src' => array(),
			'class' => array(),
		),
		'a' => array(
			'style' => array(),
			'class' => array(),
			'target' => array(),
		),
		'em' => array(),
		'br' => array(),
		'p' => array(),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
	));

	if(isset($_POST['post_type']) && ($_POST['post_type'] == "ct_location")):

		$loc_address1 = wp_filter_nohtml_kses( $_POST['_ct_loc_address1'] );
		update_post_meta($post_id, '_ct_loc_address1', $loc_address1);

		$loc_address2 = wp_filter_nohtml_kses( $_POST['_ct_loc_address2'] );
		update_post_meta($post_id, '_ct_loc_address2', $loc_address2);

		$loc_address3 = wp_filter_nohtml_kses( $_POST['_ct_loc_address3'] );
		update_post_meta($post_id, '_ct_loc_address3', $loc_address3);

		$loc_map_link = esc_url_raw( $_POST['_ct_loc_map_link'] );
		update_post_meta($post_id, '_ct_loc_map_link', $loc_map_link);

		$loc_map_code = wp_kses( $_POST['_ct_loc_map_code'], $allowed_html ); // Allow some HTML
		update_post_meta($post_id, '_ct_loc_map_code', $loc_map_code);

		$loc_service1 = wp_filter_nohtml_kses( $_POST['_ct_loc_service1'] );
		update_post_meta($post_id, '_ct_loc_service1', $loc_service1);

		$loc_service2 = wp_filter_nohtml_kses( $_POST['_ct_loc_service2'] );
		update_post_meta($post_id, '_ct_loc_service2', $loc_service2);

		$loc_service3 = wp_filter_nohtml_kses( $_POST['_ct_loc_service3'] );
		update_post_meta($post_id, '_ct_loc_service3', $loc_service3);

		$loc_service4 = wp_filter_nohtml_kses( $_POST['_ct_loc_service4'] );
		update_post_meta($post_id, '_ct_loc_service4', $loc_service4);

		$loc_service5 = wp_filter_nohtml_kses( $_POST['_ct_loc_service5'] );
		update_post_meta($post_id, '_ct_loc_service5', $loc_service5);

		$loc_notes = wp_filter_nohtml_kses( $_POST['_ct_loc_notes'] );
		update_post_meta($post_id, '_ct_loc_notes', $loc_notes);

	endif;
}
// End Custom Field Values
// End Location Options Box

// Custom Columns
function loc_register_columns($columns){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => 'Name',
			'loc_location' => 'Location',
			'loc_services' => 'Services',
			'loc_tags' => 'Tags',
			'loc_views' => 'Views',
			'loc_image' => 'Featured Image'
		);
		return $columns;
}
add_filter('manage_edit-ct_location_columns', 'loc_register_columns');

function loc_display_columns($column){
		global $post;
		$custom = get_post_custom();
		switch ($column)
		{
			case 'loc_location':
				$loc_address1 = $custom['_ct_loc_address1'][0];
				$loc_address2 = $custom['_ct_loc_address2'][0];
				$loc_address3 = $custom['_ct_loc_address3'][0];
				echo $loc_address1 . '<br />' . $loc_address2 . '<br />' . $loc_address3;
				break;
			case 'loc_services':
				$loc_service1 = $custom['_ct_loc_service1'][0];
				$loc_service2 = $custom['_ct_loc_service2'][0];
				$loc_service3 = $custom['_ct_loc_service3'][0];
				$loc_service4 = $custom['_ct_loc_service4'][0];
				$loc_service5 = $custom['_ct_loc_service5'][0];
				echo $loc_service1;
				if(!empty($loc_service2)): echo '<br />' . $loc_service2; endif;
				if(!empty($loc_service3)): echo '<br />' . $loc_service3; endif;
				if(!empty($loc_service4)): echo '<br />' . $loc_service4; endif;
				if(!empty($loc_service5)): echo '<br />' . $loc_service5; endif;
				break;
			case 'loc_tags':
				echo get_the_term_list($post->ID, 'location_tag', '', ', ', '');
				break;
			case 'loc_views':
				$meta_views = isset( $custom['Views'][0] ) ? $custom['Views'][0] : '0';
				echo $meta_views;
				break;
			case 'loc_image':
				echo get_the_post_thumbnail($post->ID, 'admin');
				break;
		}
}
add_action('manage_posts_custom_column', 'loc_display_columns');

// End Custom Columns

/* END LOCATION */


/* PERSON */

// Register Post Type
add_action('init', 'ppl_register');

function ppl_register() {
	$labels = array(
		'name' => ( 'People' ),
		'singular_name' => ( 'Person' ),
		'add_new' => _x( 'Add New', 'ct_person' ),
		'add_new_item' => __( 'Add New Person' ),
		'edit_item' => __( 'Edit Person' ),
		'new_item' => __( 'New Person' ),
		'view_item' => __( 'View Person' ),
		'search_items' => __( 'Search People' ),
		'not_found' =>  __( 'No People found' ),
		'not_found_in_trash' => __( 'No People found in Trash' ),
		'parent_item_colon' => ''
	);

	$person_settings = get_option('ct_person_settings');
	$archive_slug = $person_settings['archive_slug'];
	if(empty($archive_slug)):
		$archive_slug = 'people';
	endif;

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'has_archive' => $archive_slug,
		'query_var' => true,
		'rewrite' => array('slug' => $archive_slug),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 10,
		'menu_icon' => get_template_directory_uri() . '/lib/admin/images/menu_icon-person-16.png',
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'comments' )
	);

	register_post_type( 'ct_person' , $args );

	flush_rewrite_rules(false);

}
// End Register Post Type

// Create Custom Taxonomies
add_action( 'init', 'create_person_taxonomies', 0 );

function create_person_taxonomies() {

	// Person Categories Taxonomy (Hierarchical)
	$labels = array(
		'name' => _x( 'Categories', 'taxonomy general name' ),
		'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Categories' ),
		'popular_items' => __( 'Popular Categories' ),
		'all_items' => __( 'All Categories' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Edit Department' ),
		'update_item' => __( 'Update Category' ),
		'add_new_item' => __( 'Add New Category' ),
		'new_item_name' => __( 'New Category Name' ),
		'separate_items_with_commas' => __( 'Separate Categories with commas' ),
		'add_or_remove_items' => __( 'Add or remove Categories' ),
		'choose_from_moppl_used' => __( 'Choose from the most used Categories' )
	);
	register_taxonomy( 'person_category', 'ct_person', array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'person_category' ),
	));
	// End Person Categories Taxonomy

	// Person Tags Taxonomy (Non-Hierarchical)
	$labels = array(
		'name' => _x( 'Person Tags', 'taxonomy general name' ),
		'singular_name' => _x( 'Person Tag', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Person Tags' ),
		'all_items' => __( 'All Person Tags' ),
		'parent_item' => __( 'Parent Person Tag' ),
		'parent_item_colon' => __( 'Parent Person Tag:' ),
		'edit_item' => __( 'Edit Person Tag' ),
		'update_item' => __( 'Update Person Tag' ),
		'add_new_item' => __( 'Add New Person Tag' ),
		'new_item_name' => __( 'New Person Tag Name' ),
	);
	register_taxonomy( 'person_tag', array( 'ct_person' ), array(
		'hierarchical' => false,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'person_tag' ),
	));
	// End Person Tags Taxonomy

}
// End Custom Taxonomies

// Submenu
add_action('admin_menu', 'ppl_submenu');

function ppl_submenu() {

	// Add to end of admin_menu action function
	global $submenu;
	$submenu['edit.php?post_type=ct_person'][5][0] = __('All People');
	$post_type_object = get_post_type_object('ct_person');
	$post_type_object->labels->name = "People";

}
// End Submenu

// Create People Options Box
add_action("admin_init", "ppl_admin_init");

function ppl_admin_init(){
    add_meta_box("ppl_meta", "Additional Information", "ppl_meta_options", "ct_person", "normal", "core");
}

// Custom Field Keys
function ppl_meta_options(){
	global $post;
	$custom = get_post_custom($post->ID);
	isset($custom["_ct_ppl_role"][0]) ? $ppl_role = $custom["_ct_ppl_role"][0] : $ppl_role = null;
	isset($custom["_ct_ppl_emaillabel"][0]) ? $ppl_emaillabel = $custom["_ct_ppl_emaillabel"][0] : $ppl_emaillabel = null;
	isset($custom["_ct_ppl_emailaddress"][0]) ? $ppl_emailaddress = $custom["_ct_ppl_emailaddress"][0] : $ppl_emailaddress = null;
	isset($custom["_ct_ppl_phonelabel1"][0]) ? $ppl_phonelabel1 = $custom["_ct_ppl_phonelabel1"][0] : $ppl_phonelabel1 = null;
	isset($custom["_ct_ppl_phonenum1"][0]) ? $ppl_phonenum1 = $custom["_ct_ppl_phonenum1"][0] : $ppl_phonenum1 = null;
	isset($custom["_ct_ppl_phonelabel2"][0]) ? $ppl_phonelabel2 = $custom["_ct_ppl_phonelabel2"][0] : $ppl_phonelabel2 = null;
	isset($custom["_ct_ppl_phonenum2"][0]) ? $ppl_phonenum2 = $custom["_ct_ppl_phonenum2"][0] : $ppl_phonenum2 = null;
	isset($custom["_ct_ppl_phonelabel3"][0]) ? $ppl_phonelabel3 = $custom["_ct_ppl_phonelabel3"][0] : $ppl_phonelabel3 = null;
	isset($custom["_ct_ppl_phonenum3"][0]) ? $ppl_phonenum3 = $custom["_ct_ppl_phonenum3"][0] : $ppl_phonenum3 = null;
	isset($custom["_ct_ppl_delicious"][0]) ? $ppl_delicious = $custom["_ct_ppl_delicious"][0] : $ppl_delicious = null;
	isset($custom["_ct_ppl_facebook"][0]) ? $ppl_facebook = $custom["_ct_ppl_facebook"][0] : $ppl_facebook = null;
	isset($custom["_ct_ppl_flickr"][0]) ? $ppl_flickr = $custom["_ct_ppl_flickr"][0] : $ppl_flickr = null;
	isset($custom["_ct_ppl_lastfm"][0]) ? $ppl_lastfm = $custom["_ct_ppl_lastfm"][0] : $ppl_lastfm = null;
	isset($custom["_ct_ppl_linkedin"][0]) ? $ppl_linkedin = $custom["_ct_ppl_linkedin"][0] : $ppl_linkedin = null;
	isset($custom["_ct_ppl_myspace"][0]) ? $ppl_myspace = $custom["_ct_ppl_myspace"][0] : $ppl_myspace = null;
	isset($custom["_ct_ppl_picasa"][0]) ? $ppl_picasa = $custom["_ct_ppl_picasa"][0] : $ppl_picasa = null;
	isset($custom["_ct_ppl_ping"][0]) ? $ppl_ping = $custom["_ct_ppl_ping"][0] : $ppl_ping = null;
	isset($custom["_ct_ppl_posterous"][0]) ? $ppl_posterous = $custom["_ct_ppl_posterous"][0] : $ppl_posterous = null;
	isset($custom["_ct_ppl_tumblr"][0]) ? $ppl_tumblr = $custom["_ct_ppl_tumblr"][0] : $ppl_tumblr = null;
	isset($custom["_ct_ppl_twitter"][0]) ? $ppl_twitter = $custom["_ct_ppl_twitter"][0] : $ppl_twitter = null;
	isset($custom["_ct_ppl_wordpress"][0]) ? $ppl_wordpress = $custom["_ct_ppl_wordpress"][0] : $ppl_wordpress = null;
	isset($custom["_ct_ppl_youtube"][0]) ? $ppl_youtube = $custom["_ct_ppl_youtube"][0] : $ppl_youtube = null;
	isset($custom["_ct_ppl_notes"][0]) ? $ppl_notes = $custom["_ct_ppl_notes"][0] : $ppl_notes = null;
// End Custom Field Keys

// Start HTML
?>

	<h2 class="meta_section"><?php _e('Featured Image', 'churchthemes'); ?></h2>

	<div class="meta_item first">
		<a title="Set Featured Image" href="media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=285" id="set-post-thumbnail" class="thickbox button rbutton"><?php _e('Set Featured Image', 'churchthemes'); ?></a>
		<br />
		<span><?php _e('To ensure the best image quality possible, please use a JPG image that is at least 400 x 400 (pixels)', 'churchthemes'); ?></span>
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('General', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_ppl_role"><?php _e('Role', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_role" size="50" autocomplete="on" placeholder="<?php _e('e.g. Senior Pastor', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_role); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Email Address', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('Enter a label and email address to display (just leave this blank if you don\'t want to publish an email)', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_ppl_emaillabel"><?php _e('Email Label', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_emaillabel" size="20" autocomplete="on" placeholder="<?php _e('e.g. Email Me', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_emaillabel); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_emailaddress"><?php _e('Email Address', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_emailaddress" size="50" autocomplete="on" placeholder="<?php _e('e.g. first.last@mychurch.org', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_emailaddress); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Phone Numbers', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('Enter a label and number for up to three Phone Number fields (blank fields will be ignored)', 'churchthemes'); ?></p>

	<div class="meta_item phone_label">
		<label for="_ct_ppl_phonelabel1"><?php _e('Phone Label 1', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonelabel1" size="20" autocomplete="on" placeholder="<?php _e('e.g. Office', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_phonelabel1); ?>" />
	</div>

	<div class="meta_item phone_number">
		<label for="_ct_ppl_phonenum1"><?php _e('Phone Number 1', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonenum1" size="20" autocomplete="on" placeholder="+x (xxx) xxx-xxxx" value="<?php echo esc_attr($ppl_phonenum1); ?>" />
	</div>

	<div class="meta_item phone_label">
		<label for="_ct_ppl_phonelabel2"><?php _e('Phone Label 2', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonelabel2" size="20" autocomplete="on" placeholder="<?php _e('e.g. Mobile', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_phonelabel2); ?>" />
	</div>

	<div class="meta_item phone_number">
		<label for="_ct_ppl_phonenum2"><?php _e('Phone Number 2', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonenum2" size="20" autocomplete="on" placeholder="+x (xxx) xxx-xxxx" value="<?php echo esc_attr($ppl_phonenum2); ?>" />
	</div>

	<div class="meta_item phone_label">
		<label for="_ct_ppl_phonelabel3"><?php _e('Phone Label 3', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonelabel3" size="20" autocomplete="on" placeholder="<?php _e('e.g. Fax', 'churchthemes'); ?>" value="<?php echo esc_attr($ppl_phonelabel3); ?>" />
	</div>

	<div class="meta_item phone_number">
		<label for="_ct_ppl_phonenum3"><?php _e('Phone Number 3', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_phonenum3" size="20" autocomplete="on" placeholder="+x (xxx) xxx-xxxx" value="<?php echo esc_attr($ppl_phonenum3); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('Social Connections', 'churchthemes'); ?></h2>

	<p class="meta_info"><?php _e('Enter the full URL to each social profile link you wish to display (blank fields will be ignored)', 'churchthemes'); ?></p>

	<div class="meta_item">
		<label for="_ct_ppl_delicious"><?php _e('Delicious', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_delicious" size="70" autocomplete="on" placeholder="e.g. http://www.delicious.com/username" value="<?php echo esc_url($ppl_delicious); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_facebook"><?php _e('Facebook', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_facebook" size="70" autocomplete="on" placeholder="e.g. http://www.facebook.com/username" value="<?php echo esc_url($ppl_facebook); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_flickr"><?php _e('Flickr', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_flickr" size="70" autocomplete="on" placeholder="e.g. http://www.flickr.com/username" value="<?php echo esc_url($ppl_flickr); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_lastfm"><?php _e('Last.fm', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_lastfm" size="70" autocomplete="on" placeholder="e.g. http://www.last.fm/user/username" value="<?php echo esc_url($ppl_lastfm); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_linkedin"><?php _e('LinkedIn', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_linkedin" size="70" autocomplete="on" placeholder="e.g. http://www.linkedin.com/in/username" value="<?php echo esc_url($ppl_linkedin); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_myspace"><?php _e('MySpace', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_myspace" size="70" autocomplete="on" placeholder="e.g. http://www.myspace.com/username" value="<?php echo esc_url($ppl_myspace); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_picasa"><?php _e('Picasa', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_picasa" size="70" autocomplete="on" placeholder="e.g. http://picasaweb.google.com/username" value="<?php echo esc_url($ppl_picasa); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_ping"><?php _e('Ping', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_ping" size="70" autocomplete="on" placeholder="e.g. http://c.itunes.apple.com/us/profile/idnumber" value="<?php echo esc_url($ppl_ping); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_posterous"><?php _e('Posterous', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_posterous" size="70" autocomplete="on" placeholder="e.g. http://username.posterous.com" value="<?php echo esc_url($ppl_posterous); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_tumblr"><?php _e('Tumblr', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_tumblr" size="70" autocomplete="on" placeholder="e.g. http://username.tumblr.com" value="<?php echo esc_url($ppl_tumblr); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_twitter"><?php _e('Twitter', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_twitter" size="70" autocomplete="on" placeholder="e.g. http://twitter.com/username" value="<?php echo esc_url($ppl_twitter); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_wordpress"><?php _e('WordPress', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_wordpress" size="70" autocomplete="on" placeholder="e.g. http://username.wordpress.com" value="<?php echo esc_url($ppl_wordpress); ?>" />
	</div>

	<div class="meta_item">
		<label for="_ct_ppl_youtube"><?php _e('YouTube', 'churchthemes'); ?></label>
		<input type="text" name="_ct_ppl_youtube" size="70" autocomplete="on" placeholder="e.g. http://www.youtube.com/username" value="<?php echo esc_url($ppl_youtube); ?>" />
	</div>

	<hr class="meta_divider" />

	<h2 class="meta_section"><?php _e('More', 'churchthemes'); ?></h2>

	<div class="meta_item">
		<label for="_ct_ppl_notes">
			<?php _e('Admin Notes', 'churchthemes'); ?>
			<br /><br />
			<span class="label_note"><?php _e('Not Published', 'churchthemes'); ?></span>
		</label>
		<textarea type="text" name="_ct_ppl_notes" cols="60" rows="8"><?php echo esc_textarea($ppl_notes); ?></textarea>
	</div>

	<div class="meta_clear"></div>

<?php
// End HTML
}

// Save Custom Field Values
add_action('save_post', 'save_ct_ppl_meta');

function save_ct_ppl_meta(){

	global $post_id;

	if(isset($_POST['post_type']) && ($_POST['post_type'] == "ct_person")):

		$ppl_role = wp_filter_nohtml_kses( $_POST['_ct_ppl_role'] );
		update_post_meta($post_id, '_ct_ppl_role', $ppl_role);

		$ppl_emaillabel = wp_filter_nohtml_kses( $_POST['_ct_ppl_emaillabel'] );
		update_post_meta($post_id, '_ct_ppl_emaillabel', $ppl_emaillabel);

		$ppl_emailaddress = wp_filter_nohtml_kses( $_POST['_ct_ppl_emailaddress'] );
		update_post_meta($post_id, '_ct_ppl_emailaddress', $ppl_emailaddress);

		$ppl_phonelabel1 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonelabel1'] );
		update_post_meta($post_id, '_ct_ppl_phonelabel1', $ppl_phonelabel1);

		$ppl_phonenum1 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonenum1'] );
		update_post_meta($post_id, '_ct_ppl_phonenum1', $ppl_phonenum1);

		$ppl_phonelabel2 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonelabel2'] );
		update_post_meta($post_id, '_ct_ppl_phonelabel2', $ppl_phonelabel2);

		$ppl_phonenum2 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonenum2'] );
		update_post_meta($post_id, '_ct_ppl_phonenum2', $ppl_phonenum2);

		$ppl_phonelabel3 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonelabel3'] );
		update_post_meta($post_id, '_ct_ppl_phonelabel3', $ppl_phonelabel3);

		$ppl_phonenum3 = wp_filter_nohtml_kses( $_POST['_ct_ppl_phonenum3'] );
		update_post_meta($post_id, '_ct_ppl_phonenum3', $ppl_phonenum3);

		$ppl_delicious = esc_url_raw( $_POST['_ct_ppl_delicious'] );
		update_post_meta($post_id, '_ct_ppl_delicious', $ppl_delicious);

		$ppl_facebook = esc_url_raw( $_POST['_ct_ppl_facebook'] );
		update_post_meta($post_id, '_ct_ppl_facebook', $ppl_facebook);

		$ppl_flickr = esc_url_raw( $_POST['_ct_ppl_flickr'] );
		update_post_meta($post_id, '_ct_ppl_flickr', $ppl_flickr);

		$ppl_lastfm = esc_url_raw( $_POST['_ct_ppl_lastfm'] );
		update_post_meta($post_id, '_ct_ppl_lastfm', $ppl_lastfm);

		$ppl_linkedin = esc_url_raw( $_POST['_ct_ppl_linkedin'] );
		update_post_meta($post_id, '_ct_ppl_linkedin', $ppl_linkedin);

		$ppl_myspace = esc_url_raw( $_POST['_ct_ppl_myspace'] );
		update_post_meta($post_id, '_ct_ppl_myspace', $ppl_myspace);

		$ppl_picasa = esc_url_raw( $_POST['_ct_ppl_picasa'] );
		update_post_meta($post_id, '_ct_ppl_picasa', $ppl_picasa);

		$ppl_ping = esc_url_raw( $_POST['_ct_ppl_ping'] );
		update_post_meta($post_id, '_ct_ppl_ping', $ppl_ping);

		$ppl_posterous = esc_url_raw( $_POST['_ct_ppl_posterous'] );
		update_post_meta($post_id, '_ct_ppl_posterous', $ppl_posterous);

		$ppl_tumblr = esc_url_raw( $_POST['_ct_ppl_tumblr'] );
		update_post_meta($post_id, '_ct_ppl_tumblr', $ppl_tumblr);

		$ppl_twitter = esc_url_raw( $_POST['_ct_ppl_twitter'] );
		update_post_meta($post_id, '_ct_ppl_twitter', $ppl_twitter);

		$ppl_wordpress = esc_url_raw( $_POST['_ct_ppl_wordpress'] );
		update_post_meta($post_id, '_ct_ppl_wordpress', $ppl_wordpress);

		$ppl_youtube = esc_url_raw( $_POST['_ct_ppl_youtube'] );
		update_post_meta($post_id, '_ct_ppl_youtube', $ppl_youtube);

		$ppl_notes = wp_filter_nohtml_kses( $_POST['_ct_ppl_notes'] );
		update_post_meta($post_id, '_ct_ppl_notes', $ppl_notes);

	endif;
}
// End Custom Field Values
// End Person Options Box

// Custom Columns
function ppl_register_columns($columns){
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => 'Name',
			'ppl_role' => 'Role',
			'ppl_category' => 'Category',
			'ppl_tags' => 'Tags',
			'ppl_views' => 'Views',
			'ppl_image' => 'Featured Image'
		);
		return $columns;
}
add_filter('manage_edit-ct_person_columns', 'ppl_register_columns');

function ppl_display_columns($column){
		global $post;
		$custom = get_post_custom();
		switch ($column)
		{
			case 'ppl_role':
				$ppl_role = $custom['_ct_ppl_role'][0];
				echo $ppl_role;
				break;
			case 'ppl_category':
				echo get_the_term_list($post->ID, 'person_category', '', ', ', '');
				break;
			case 'ppl_tags':
				echo get_the_term_list($post->ID, 'person_tag', '', ', ', '');
				break;
			case 'ppl_views':
				$meta_views = isset( $custom['Views'][0] ) ? $custom['Views'][0] : '0';
				echo $meta_views;
				break;
			case 'ppl_image':
				echo get_the_post_thumbnail($post->ID, 'admin');
				break;
		}
}
add_action('manage_posts_custom_column', 'ppl_display_columns');

// End Custom Columns

// Custom Functions
function ppl_has_categories($peoplecategories = '') {
	global $post;
	$taxonomy = "ppl_categories";

	if ( !in_the_loop() ) return false;

	$post_id = (int) $post->ID;

	$terms = get_object_term_cache($post_id, $taxonomy);
	if (empty($terms))
	       	$terms = wp_get_object_terms($post_id, $taxonomy);
	if (empty($terms)) return false;

	if (empty($speakers)) return (!empty($terms));

	$peoplecategories = (array) $peoplecategories;
	foreach($terms as $term) {
		if ( in_array( $term->term_id, $peoplecategories ) )
			return true;
		elseif ( in_array( $term->name, $peoplecategories ) )
			return true;
		elseif ( in_array( $term->slug, $peoplecategories ) )
			return true;
	}

	return false;
}

function ppl_has_tags($peopletags = '') {
	global $post;
	$taxonomy = "ppl_tags";

	if ( !in_the_loop() ) return false;

	$post_id = (int) $post->ID;

	$terms = get_object_term_cache($post_id, $taxonomy);
	if (empty($terms))
	       	$terms = wp_get_object_terms($post_id, $taxonomy);
	if (empty($terms)) return false;

	if (empty($services)) return (!empty($terms));

	$peopletags = (array) $peopletags;
	foreach($terms as $term) {
		if ( in_array( $term->term_id, $peopletags ) )
			return true;
		elseif ( in_array( $term->name, $peopletags ) )
			return true;
		elseif ( in_array( $term->slug, $peopletags ) )
			return true;
	}

	return false;
}
// End Custom Functions

// Create Shortcodes
function sc_people($atts, $content = null) {
	extract(shortcode_atts(
		array(
			// Default behaviors if values aren't specified
			'num' => get_option( 'posts_per_page' ),
			'id' => '',
			'paging' => 'show',
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'cat' => '',
			'tag' => '',
			'images' => 'show',
		), $atts));

	global $post;

	if($orderby == 'views'): $orderby = 'meta_value_num'; endif;
	if($paging == 'hide'):
		$paged = null;
	else:
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	endif;

if($orderby == 'meta_value_num'):
	$args=array(
		'post_type' => 'ct_person',
		'post_status' => 'publish',
		'paged' => $paged,
		'id' => $id,
		'posts_per_page' => $num,
		'meta_key' => 'Views',
		'orderby' => $orderby,
		'order' => $order,
		'person_category' => $cat,
		'person_tag' => $tag,
	);
else:
	$args=array(
		'post_type' => 'ct_person',
		'post_status' => 'publish',
		'paged' => $paged,
		'id' => $id,
		'posts_per_page' => $num,
		'orderby' => $orderby,
		'order' => $order,
		'person_category' => $cat,
		'person_tag' => $tag,
	);
endif;

	query_posts($args);

	ob_start();
	if ( $images != 'hide' ) {
		include('shortcode-people.php');
	}
	else {
		include('shortcode-people-noimage.php');
	}
	if($paging != 'hide') {
		pagination();
	}
	wp_reset_query();
	$content = ob_get_clean();
	return $content;

}
add_shortcode("people", "sc_people");

// End Shortcodes

/* END PERSON */

?>