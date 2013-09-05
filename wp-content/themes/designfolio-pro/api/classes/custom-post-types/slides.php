<?php

/**
 * Slide custom post type.
 *
 * Class name suffix _CPT stands for [C]ustom_[P]ost_[T]ype.
 *
 * @since 0.1.0
 */
class PC_Slide_CPT {

	/**
	 * Slide class constructor.
	 * 
	 * Contains hooks that point to class methods to initialise the custom post type etc.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		/* Add Slide widget area. */
		add_action( 'widgets_init', array( &$this, 'register_slide_sidebar' ) );

		/* Add taxonomy-slide_group.php to the list of custom sidebars to use for archive pages. */
		add_filter( 'pc_custom_primary_sidebar_archive', array( &$this, 'add_custom_slide_primary_sidebar_archive' ) );

		/* Add single-slide.php to the list of custom post sidebars. */
		add_filter( 'pc_custom_primary_sidebar_posts', array( &$this, 'add_custom_slide_primary_sidebar_posts' ) );

		/* Register CPT and associated taxonomy. */
		add_action( 'init', array( &$this, 'register_post_type' ) );
		add_action( 'init', array( &$this, 'register_taxonomy' ) );

		/* Customize CPT columns on overview page. */
		add_filter( 'manage_slide_posts_columns', array( &$this, 'change_overview_columns' ) ); /* Which columns are displayed. */
		add_action( 'manage_slide_posts_custom_column', array( &$this, 'custom_column_content' ), 10, 2 ); /* The html output for each column. */
		add_filter( 'manage_edit-slide_sortable_columns', array( &$this, 'sort_custom_columns' ) ); /* Specify which columns are sortable. */

		/* Customize the CPT messages. */
		add_filter( 'post_updated_messages', array( &$this, 'update_cpt_messages' ) );

		/* Add meta boxes to slide custom post type. */
		add_action( 'admin_init', array( &$this, 'slide_cpt_meta_box_init' ) );
		add_action( 'add_meta_boxes', array( &$this, 'move_featured_image_metabox' ) );
		
		/* Add dropdown filter on slide CPT edit.php to sort by taxonomy. */
		// These work OK but until I can figure out how to get the default taxonomy term to be associated
		// automatically with new CPT items then I will leave this feature out as the show all option doesn't
		// work properly.
		// add_action( 'restrict_manage_posts', array( &$this, 'taxonomy_filter_restrict_manage_posts' ) );
		// add_filter( 'parse_query', array( &$this, 'taxonomy_filter_post_type_request' ) );
	}

	/**
	 * Register Slide post type.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {

		/* Post type arguments. */
		$args = array(
			'public' => true,
			'exclude_from_search' => false, // @todo Setting this to true will break the pagination for the slide_group custom taxonomy. See: http://core.trac.wordpress.org/ticket/17592
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'editor', 'author', 'thumbnail', 'title', 'comments', 'revisions'
			),
			'labels' => array(
				'name' =>				__( 'Slides', 'presscoders' ),
				'singular_name' =>		__( 'Slide', 'presscoders' ),
				'add_new' =>			__( 'Add New Slide', 'presscoders' ),
				'add_new_item' =>		__( 'Add New Slide', 'presscoders' ),
				'edit_item' =>			__( 'Edit Slide', 'presscoders' ),
				'new_item' =>			__( 'New Slide', 'presscoders' ),
				'view_item' =>			__( 'View Slide', 'presscoders' ),
				'search_items' =>		__( 'Search Slides', 'presscoders' ),
				'not_found' =>			__( 'No Slides Found', 'presscoders' ),
				'not_found_in_trash' =>	__( 'No Slides Found In Trash', 'presscoders' )
			)
		);

		/* Register post type. */
		register_post_type( 'slide', $args );
	}

	/**
	 * Register Slide taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomy() {

		/* Slide taxonomy arguments. */
		$args = array(
			'hierarchical' => true,
			'query_var' => true, 
			'show_tagcloud' => false,
			'sort' => true,
			'rewrite' => array( 'slug' => 'slide-group' ),
			'labels' => array(
				'name' =>				__( 'Slide Groups', 'presscoders' ),
				'singular_name' =>		__( 'Slide Group', 'presscoders' ),
				'edit_item' =>			__( 'Edit Slide', 'presscoders' ),
				'update_item' =>		__( 'Update Slide', 'presscoders' ),
				'add_new_item' =>		__( 'Add New Group', 'presscoders' ),
				'new_item_name' =>		__( 'New Slide Name', 'presscoders' ),
				'all_items' =>			__( 'All Slides', 'presscoders' ),
				'search_items' =>		__( 'Search Slides', 'presscoders' ),
				'parent_item' =>		__( 'Parent Genre', 'presscoders' ),
				'parent_item_colon' =>	__( 'Parent Genre:', 'presscoders' )
			)
		);

		/* Register the slide taxonomy. */
		register_taxonomy( 'slide_group', array( 'slide' ), $args );
	}

	/**
	 * Change the columns on the custom post types overview page.
	 *
	 * @since 0.1.0
	 */
	public function change_overview_columns( $cols ) {

		$cols = array(
			'cb' =>		'<input type="checkbox" />',
			'title' =>	__( 'Title', 'presscoders' ),
			'author' =>	__( 'Author', 'presscoders' ),
			'image' =>	__( 'Background Image', 'presscoders' ),
			'group' =>	__( 'Group', 'presscoders' ),
			'id' =>		__( 'Slide ID', 'presscoders' ),
			'date' =>	__( 'Date', 'presscoders' )
		);
		return $cols;
	}

	/**
	 * Add some content to the custom columns from the custom post type.
	 *
	 * @since 0.1.0
	 */
	public function custom_column_content( $column, $post_id ) {

		switch ( $column ) {
			case "title":
				echo 'title';
				break;
			case "image":
				if( has_post_thumbnail( $post_id ) ) {
					$image_attr = array( 'height' => '50' );
					$image = PC_Utility::get_responsive_featured_image( $post_id, 'thumbnail', $image_attr );
				}
				else {
					$image = "<em>No image.</em>";
				}

				echo $image;
				break;
			case "group":
				$taxonomy = 'slide_group';
				$post_type = get_post_type($post_id);
				$terms = get_the_terms( $post_id, $taxonomy );

				/* get_the_terms() only returns an array on success so need check for valid array. */
				if( is_array($terms) ) {
					$str = "";
					foreach( $terms as $term) {
						$str .= "<a href='edit.php?post_type={$post_type}&{$taxonomy}={$term->slug}'> " . esc_html(sanitize_term_field('name', $term->name, $term->term_id, 'group', 'edit')) . "</a>, ";
					}
					echo rtrim( $str, ", ");
				}
				else {
					echo "<em>Not in any groups.</em>";
				}
				break;
			case "id":
				echo $post_id;
				break;
		}
	}

	/**
	 * Make custom columns sortable.
	 *
	 * @since 0.1.0
	 */
	function sort_custom_columns() {
		return array(
			'title'		=> 'title',
			'date'		=> 'date',
			'id'		=> 'id'
		);
	}

	/**
	 * Move featured image meta box.
	 *
	 * @since 0.1.0
	 */
	public function move_featured_image_metabox() {
		$post_types = get_post_types( array( '_builtin' => false ) );

		if ( in_array( 'slide', $post_types ) ) {
			remove_meta_box( 'postimagediv', 'slide', 'side' ); 
			add_meta_box( 'postimagediv', __( 'Slide Featured Image', 'presscoders' ), 'post_thumbnail_meta_box', 'slide', 'normal', 'high' );
		}
	}

	/**
	 * Initialise custom post type meta boxes.
	 *
	 * @since 0.1.0
	 */
	public function slide_cpt_meta_box_init() {
		add_meta_box( PC_THEME_NAME_SLUG.'-slide-cpt', __( 'Slide Title and Links', 'presscoders' ), array( &$this, 'slide_cpt_meta_box' ), 'slide', 'normal', 'high' );
		/* Hook to save our meta box data when the post is saved. */
		add_action( 'save_post', array( &$this, 'slide_cpt_save_meta_box' ) );
	}

	/**
	 * Display the meta box for slide data fields.
	 *
	 * @since 0.1.0
	 */
	public function slide_cpt_meta_box($post, $args) {

		/* Retrieve our custom meta box values */
		$slide_cpt_fi_link = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_fi_link', true); // featured image link
		$slide_cpt_title_link = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_title_link', true); // slide title link
		$slide_cpt_show_title = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_show_title', true); // slide optional title
		?>

		<script language="javascript">

			// Toggle display of slide title text box if check box selected
			jQuery(document).ready(function($) {
				// Sync the toggle with the state of the checkbox		
				if( $('#chk_slide_cpt_show_title').attr('checked') )
					$("#slide_cpt_show_title_tr").css("display","none");					
				else
					$("#slide_cpt_show_title_tr").css("display","table-row");

				// Toggle the text box
				$("#chk_slide_cpt_show_title").click(function () {
					$("#slide_cpt_show_title_tr").toggle("100");
				});

			});

		</script>

		<table width="100%">
			<tbody>
				<tr>
					<td colspan="3"><label><input id="chk_slide_cpt_show_title" type="checkbox" value="1" name="<?php echo PC_THEME_NAME_SLUG; ?>_slide_cpt_show_title" <?php checked( $slide_cpt_show_title, '1' ); ?>>&nbsp;Hide slide title</label></td>
				</tr>
				<tr id="slide_cpt_show_title_tr">
					<td width="120">Slide title URL</td>
					<td>
						<input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_slide_cpt_title_link" value="<?php echo esc_attr( $slide_cpt_title_link ); ?>">
					</td>
					<td width="140">
						<span style="font-style:italic;color:#666;">&nbsp;&nbsp;Leave blank for no link</span>
					</td>
				</tr>
				<?php

				$hide_css = has_post_thumbnail($post->ID) ? '' : ' style="display:none;"'; ?>

				<tr<?php echo $hide_css; ?>>
					<td width="120">Featured image URL</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_slide_cpt_fi_link" value="<?php echo esc_attr( $slide_cpt_fi_link ); ?>"></td>
					<td width="140">
						<span style="font-style:italic;color:#666;">&nbsp;&nbsp;Leave blank for no link</span>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
	}

	/**
	 * Save the custom post type meta box input field settings.
	 *
	 * @since 0.1.0
	 */
	public function slide_cpt_save_meta_box($post_id) {
		global $typenow;

		/* Only work for specific post type */
		if( $typenow != 'slide' )
			return;

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_fi_link']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_fi_link', esc_url(esc_attr($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_fi_link'])) );
		else
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_fi_link', get_permalink($post_id) );

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_title_link']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_title_link', esc_url(esc_attr($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_title_link'])) );
		else
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_title_link', get_permalink($post_id) );

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_show_title']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_show_title', esc_attr($_POST[ PC_THEME_NAME_SLUG.'_slide_cpt_show_title']) );
		else
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_slide_cpt_show_title', 0 );
	}

	/**
	 * Save the custom post type meta box input field settings.
	 *
	 * @since 0.1.0
	 */
	public function update_cpt_messages( $messages ) {
		global $post, $post_ID;

		$messages['slide'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Slide updated.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'presscoders' ),
			3 => __( 'Custom field deleted.', 'presscoders' ),
			4 => __( 'Slide updated.', 'presscoders' ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Slide restored to revision from %s', 'presscoders' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Slide published.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Slide saved.', 'presscoders' ),
			8 => sprintf( __( 'Slide submitted.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Slide scheduled for: <strong>%1$s</strong>.', 'presscoders' ),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i', 'presscoders' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Slide draft updated.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Filter the request to just give posts for the given taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function taxonomy_filter_restrict_manage_posts() {
		global $typenow;

		/* Only work for specific post type */
		if( $typenow != 'slide' )
			return;

		$post_types = get_post_types( array( '_builtin' => false ) );

		if ( in_array( $typenow, $post_types ) ) {
			$filters = get_object_taxonomies( $typenow );

			foreach ( $filters as $tax_slug ) {
				if( !isset($_GET[$tax_slug]) ) {
					$selected = '';
				}
				else {
					$selected = $_GET[$tax_slug];
				}

				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'taxonomy' 	  => $tax_slug,
					'name' 		  => $tax_obj->name,
					'orderby' 	  => 'name',
					'selected' 	  => $selected,
					'hierarchical' 	  => $tax_obj->hierarchical,
					'show_count' 	  => true,
					'hide_empty' 	  => true
				) );
			}
		}
	}

	/**
	 * Add a filter to the query so the dropdown will work.
	 *
	 * @since 0.1.0
	 */
	public function taxonomy_filter_post_type_request( $query ) {
	  global $pagenow, $typenow;

	  if ( 'edit.php' == $pagenow ) {
		$filters = get_object_taxonomies( $typenow );
		foreach ( $filters as $tax_slug ) {
		  $var = &$query->query_vars[$tax_slug];
		  if ( isset( $var ) ) {
			$term = get_term_by( 'id', $var, $tax_slug );
			$var = $term->slug;
		  }
		}
	  }
	}

	/**
	 * Add a framework filter to use taxonomy-slide_group.php for Slides archive primary sidebar.
	 *
	 * @since 0.1.0
	 */
	public function add_custom_slide_primary_sidebar_archive( $custom_archive_pages ) {

		$custom_archive_pages['taxonomy-slide_group.php'] = 'primary-slide-widget-area';
		return $custom_archive_pages;
	}

	/**
	 * Add a framework filter to use single-slide.php for Slides post primary sidebar.
	 *
	 * @since 0.1.0
	 */
	public function add_custom_slide_primary_sidebar_posts( $custom_theme_posts ) {

		$custom_theme_posts['single-slide.php'] = 'primary-slide-widget-area';
		return $custom_theme_posts;
	}

	/**
	 * Add Slide widget area.
	 *
	 * @since 0.1.0
	 */
	public function register_slide_sidebar() {

		register_sidebar( array(
			'name' => __( 'Slide', 'presscoders' ),
			'id' => 'primary-slide-widget-area',
			'description' => __( 'Slide widget area', 'presscoders' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
			'width' => 'normal'
		) );
	}
}

?>