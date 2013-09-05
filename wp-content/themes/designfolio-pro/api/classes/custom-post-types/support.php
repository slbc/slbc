<?php

/**
 * Support custom post type.
 *
 * Class name suffix _CPT stands for [C]ustom_[P]ost_[T]ype.
 *
 * @since 0.1.0
 */
class PC_Support_CPT {

	/**
	 * Support class constructor.
	 * 
	 * Contains hooks that point to class methods to initialise the custom post type etc.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		/* Register CPT and associated taxonomy. */
		/* Note: for some reason registering the taxonomy first allowed the CPT/taxonomy structure to work better together. i.e. posts => /support/ and taxonomiy => /support/category/ */
		add_action( 'init', array( &$this, 'register_taxonomy' ) );
		add_action( 'init', array( &$this, 'register_post_type' ) );

		/* Customize CPT columns on overview page. */
		add_filter( 'manage_support_posts_columns', array( &$this, 'change_overview_columns' ) ); /* Which columns are displayed. */
		add_action( 'manage_support_posts_custom_column', array( &$this, 'custom_column_content' ), 10, 2 ); /* The html output for each column. */
		add_filter( 'manage_edit-support_sortable_columns', array( &$this, 'sort_custom_columns' ) ); /* Specify which columns are sortable. */

		/* Customize the CPT messages. */
		add_filter( 'post_updated_messages', array( &$this, 'update_cpt_messages' ) );
		add_filter( 'enter_title_here', array( &$this, 'update_title_message' ) );

		/* Add meta boxes to support custom post type. */
		add_action( 'admin_init', array( &$this, 'support_cpt_meta_box_init' ) );
		add_action( 'add_meta_boxes', array( &$this, 'move_featured_image_metabox' ) );
		
		/* Add dropdown filter on support CPT edit.php to sort by taxonomy. */
		// These work OK but until I can figure out how to get the default taxonomy term to be associated
		// automatically with new CPT items then I will leave this feature out as the show all option doesn't
		// work properly.
		// add_action( 'restrict_manage_posts', array( &$this, 'taxonomy_filter_restrict_manage_posts' ) );
		// add_filter( 'parse_query', array( &$this, 'taxonomy_filter_post_type_request' ) );
	}

	/**
	 * Register Support post type.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {

		/* Post type arguments. */
		$args = array(
			'public' => true,
			'_builtin' => false,
			'has_archive' => true,
			'exclude_from_search' => false, // @todo Setting this to true will break the pagination for the slide_group custom taxonomy. See: http://core.trac.wordpress.org/ticket/17592
			'query_var' => true,
			'rewrite' => array( 'slug' => 'support' ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'editor', 'author', 'thumbnail', 'title', 'comments', 'revisions'
			),
			'labels' => array(
				'name' =>				__( 'Support Docs', 'presscoders' ),
				'singular_name' =>		__( 'Support Document', 'presscoders' ),
				'add_new' =>			__( 'Add New Document', 'presscoders' ),
				'add_new_item' =>		__( 'Add New Document', 'presscoders' ),
				'edit_item' =>			__( 'Edit Support Doc', 'presscoders' ),
				'new_item' =>			__( 'New Support Doc', 'presscoders' ),
				'view_item' =>			__( 'View Support Doc', 'presscoders' ),
				'search_items' =>		__( 'Search Support Docs', 'presscoders' ),
				'not_found' =>			__( 'No Support Documents Found', 'presscoders' ),
				'not_found_in_trash' =>	__( 'No Support Documents Found In Trash', 'presscoders' )
			)
		);

		/* Register post type. */
		register_post_type( 'support', $args );
	}

	/**
	 * Register Support taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomy() {

		/* Support taxonomy arguments. */
		$args = array(
			'hierarchical' => true,
			//'with_front' => true,
			'query_var' => true, 
			'show_tagcloud' => false,
			'rewrite' => array( 'slug' => 'support/category' ),
			'sort' => true,
			'labels' => array(
				'name' =>				__( 'Document Groups', 'presscoders' ),
				'singular_name' =>		__( 'Document Group', 'presscoders' ),
				'edit_item' =>			__( 'Edit Support Doc', 'presscoders' ),
				'update_item' =>		__( 'Update Support Doc' , 'presscoders' ),
				'add_new_item' =>		__( 'Add New Support Doc Group', 'presscoders' ),
				'new_item_name' =>		__( 'New Support Doc Name', 'presscoders' ),
				'all_items' =>			__( 'All Support Docs', 'presscoders' ),
				'search_items' =>		__( 'Search Support Docs', 'presscoders' ),
				'parent_item' =>		__( 'Parent Genre', 'presscoders' ),
				'parent_item_colon' =>	__( 'Parent Genre:', 'presscoders' )
			)
		);

		/* Register the support taxonomy. */
		register_taxonomy( 'support_group', array( 'support' ), $args );
	}

	/**
	 * Change the columns on the custom post types overview page.
	 *
	 * @since 0.1.0
	 */
	public function change_overview_columns( $cols ) {

		$cols = array(
			'cb' =>			'<input type="checkbox" />',
			'title' =>		__( 'Name', 'presscoders' ),
			'author' =>		__( 'Author', 'presscoders' ),
			'company' =>	__( 'Company', 'presscoders' ),
			'image' =>		__( 'Image Icon', 'presscoders' ),
			'group' =>		__( 'Group', 'presscoders' ),
			'id' =>			__( 'ID', 'presscoders' ),
			'date' =>		__( 'Date', 'presscoders' )
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
			case "company":
				$company_url = trim(get_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company_url',true)); 
				$company_name = get_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company',true);
				echo ( $company_url == '' ) ? $company_name : '<a href="'.$company_url.'" target="_blank">'.$company_name.'</a>'; 
				break;
			case "image":
				/* If no featured image set, use gravatar if specified. */
				if( !($image = get_the_post_thumbnail( $post_id, array(32,32) ) ) ) {
					$image = get_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_image',true);
					if( trim($image) == '' ) {
						$image = "<em>no image</em>";
					}
					else {
						$image = get_avatar( $image, $size = '32' );
					}
				}
				echo $image;
				break;
			case "group":
				$taxonomy = 'support_group';
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
					echo "<em>no groups</em>";
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
			'company'	=> 'company',
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

		if ( in_array( 'support', $post_types ) ) {
			remove_meta_box( 'postimagediv', 'support', 'side' ); 
			add_meta_box( 'postimagediv', __( 'Support Document Image', 'presscoders' ), 'post_thumbnail_meta_box', 'support', 'side', 'low');
		}
	}

	/**
	 * Initialise custom post type meta boxes.
	 *
	 * @since 0.1.0
	 */
	public function support_cpt_meta_box_init() {
		add_meta_box( PC_THEME_NAME_SLUG.'-support-cpt', __( 'Support Document Details', 'presscoders' ), array( &$this, 'support_cpt_meta_box' ), 'support', 'normal', 'high' );
		/* Hook to save our meta box data when the post is saved. */
		add_action( 'save_post', array( &$this, 'support_cpt_save_meta_box' ) );
	}

	/**
	 * Display the meta box for supports data fields.
	 *
	 * @since 0.1.0
	 */
	public function support_cpt_meta_box($post, $args) {
		/* Retrieve our custom meta box values */
		$support_cpt_company = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company', true);
		$support_cpt_company_url = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company_url', true);
		$support_cpt_image = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_support_cpt_image', true);

		?>

		<table width="100%">
			<tbody>
				<tr>
					<td>Company:</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_support_cpt_company" value="<?php echo esc_attr( $support_cpt_company ); ?>"></td>
				</tr>
				<tr>
					<td>Company Link:</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_support_cpt_company_url" value="<?php echo esc_attr( $support_cpt_company_url ); ?>"></td>
				</tr>
				<tr>
					<td width="100">Gravatar E-mail:</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_support_cpt_image" value="<?php echo esc_attr( $support_cpt_image ); ?>"></td>
				</tr>
				<tr>
					<td colspan="2"><div style="font-style:italic;color:#666;margin-top:10px;line-height:18px;">To upload an image, use the Support Image feature to the right (recommended 50x50 pixels), or enter a Gravatar e-mail above. A default icon is shown for an invalid gravatar e-mail.</div></td>
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
	public function support_cpt_save_meta_box($post_id) {
		global $typenow;

		/* Only work for specific post type */
		if( $typenow != 'support' )
			return;

		/* Save the meta box data as post meta, using the post ID as a unique prefix. */
		if( isset($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_company']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company', esc_attr($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_company']) );

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_company_url']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_company_url', esc_attr($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_company_url']) );

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_image']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_support_cpt_image', esc_attr($_POST[ PC_THEME_NAME_SLUG.'_support_cpt_image']) );
	}

	/**
	 * Save the custom post type meta box input field settings.
	 *
	 * @since 0.1.0
	 */
	public function update_cpt_messages( $messages ) {
		global $post, $post_ID;

		$messages['support'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Support updated.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'presscoders' ),
			3 => __( 'Custom field deleted.', 'presscoders' ),
			4 => __( 'Support updated.', 'presscoders' ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Support restored to revision from %s', 'presscoders' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Support published.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Support saved.', 'presscoders' ),
			8 => sprintf( __( 'Support submitted.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Support scheduled for: <strong>%1$s</strong>.', 'presscoders' ),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i', 'presscoders' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Support draft updated.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		return $messages;
	}

	/**
	 * Update the title edit prompt message shown when editing a new support post.
	 *
	 * @since 0.1.0
	 */
	public function update_title_message( $message ) {
		global $post;

		$pt = get_post_type( $post );
		if( $pt == 'support' ) {
			$message = "Enter name here";
		}

		return $message;
	}

	/**
	 * Filter the request to just give posts for the given taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function taxonomy_filter_restrict_manage_posts() {
		global $typenow;

		/* Only work for specific post type */
		if( $typenow != 'support' )
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
}

?>