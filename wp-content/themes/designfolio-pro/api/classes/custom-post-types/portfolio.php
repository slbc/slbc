<?php

/**
 * Portfolio custom post type.
 *
 * Class name suffix _CPT stands for [C]ustom_[P]ost_[T]ype.
 *
 * @since 0.1.0
 */
class PC_Portfolio_CPT {

	/**
	 * Portfolio class constructor.
	 * 
	 * Contains hooks that point to class methods to initialise the custom post type etc.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		/* Add Portfolio widget area. */
		add_action( 'widgets_init', array( &$this, 'register_portfolio_sidebar' ) );

		/* Add taxonomy-portfolio_group.php to the list of custom archive sidebars. */
		add_filter( 'pc_custom_primary_sidebar_archive', array( &$this, 'add_custom_pf_primary_sidebar_archive' ) );

		/* Add single-portfolio.php to the list of custom post sidebars. */
		add_filter( 'pc_custom_primary_sidebar_posts', array( &$this, 'add_custom_pf_primary_sidebar_posts' ) );

		/* Add portfolio-page.php to the list of custom page sidebars. */
		add_filter( 'pc_custom_primary_sidebar_pages', array( &$this, 'add_custom_pf_primary_sidebar_pages' ) );

		/* Register CPT and associated taxonomy. */
		add_action( 'init', array( &$this, 'register_post_type' ) );
		add_action( 'init', array( &$this, 'register_taxonomy' ) );

		/* Customize CPT columns on overview page. */
		add_filter( 'manage_portfolio_posts_columns', array( &$this, 'change_overview_columns' ) ); /* Which columns are displayed. */
		add_action( 'manage_portfolio_posts_custom_column', array( &$this, 'custom_column_content' ), 10, 2 ); /* The html output for each column. */
		add_filter( 'manage_edit-portfolio_sortable_columns', array( &$this, 'sort_custom_columns' ) ); /* Specify which columns are sortable. */

		/* Customize the CPT messages. */
		add_filter( 'post_updated_messages', array( &$this, 'update_cpt_messages' ) );

		/* Add meta boxes to portfolio custom post type. */
		add_action( 'admin_init', array( &$this, 'portfolio_cpt_meta_box_init' ) );
		add_action( 'add_meta_boxes', array( &$this, 'move_featured_image_metabox' ) );
		
		/* Add dropdown filter on portfolio CPT edit.php to sort by taxonomy. */
		// These work OK but until I can figure out how to get the default taxonomy term to be associated
		// automatically with new CPT items then I will leave this feature out as the show all option doesn't
		// work properly.
		// add_action( 'restrict_manage_posts', array( &$this, 'taxonomy_filter_restrict_manage_posts' ) );
		// add_filter( 'parse_query', array( &$this, 'taxonomy_filter_post_type_request' ) );
	}

	/**
	 * Register Portfolio post type.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {

		/* Post type arguments. */
		$args = array(
			'public' => true,
			'exclude_from_search' => false, // @todo Setting this to true will break the pagination for the portfolio_group custom taxonomy. See: http://core.trac.wordpress.org/ticket/17592
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array(
				'editor', 'author', 'thumbnail', 'title', 'comments', 'excerpt', 'revisions'
			),
			'labels' => array(
				'name' =>				__( 'Portfolios', 'presscoders' ),
				'singular_name' =>		__( 'Portfolio', 'presscoders' ),
				'add_new' =>			__( 'Add New Item', 'presscoders' ),
				'add_new_item' =>		__( 'Add New Portfolio Item', 'presscoders' ),
				'edit_item' =>			__( 'Edit Portfolio', 'presscoders' ),
				'new_item' =>			__( 'New Portfolio', 'presscoders' ),
				'view_item' =>			__( 'View Portfolio', 'presscoders' ),
				'search_items' =>		__( 'Search Portfolios', 'presscoders' ),
				'not_found' =>			__( 'No Portfolios Found', 'presscoders' ),
				'not_found_in_trash' =>	__( 'No Portfolios Found In Trash', 'presscoders' )
			)
		);

		/* Register post type. */
		register_post_type( 'portfolio', $args );
	}

	/**
	 * Register Portfolio taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomy() {

		/* Portfolio taxonomy arguments. */
		$args = array(
			'hierarchical' => true,
			'query_var' => true, 
			'show_tagcloud' => false,
			'sort' => true,
			'rewrite' => array( 'slug' => 'portfolio-group' ),
			'labels' => array(
				'name' =>				__( 'Portfolio Groups', 'presscoders' ),
				'singular_name' =>		__( 'Portfolio Group', 'presscoders' ),
				'edit_item' =>			__( 'Edit Portfolio', 'presscoders' ),
				'update_item' =>		__( 'Update Portfolio', 'presscoders' ),
				'add_new_item' =>		__( 'Add New Group', 'presscoders' ),
				'new_item_name' =>		__( 'New Portfolio Name', 'presscoders' ),
				'all_items' =>			__( 'All Portfolios', 'presscoders' ),
				'search_items' =>		__( 'Search Portfolios', 'presscoders' ),
				'parent_item' =>		__( 'Parent Genre', 'presscoders' ),
				'parent_item_colon' =>	__( 'Parent Genre:', 'presscoders' )
			)
		);

		/* Register the portfolio taxonomy. */
		register_taxonomy( 'portfolio_group', array( 'portfolio' ), $args );
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
			'id' =>		__( 'Portfolio ID', 'presscoders' ),
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
				$taxonomy = 'portfolio_group';
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

		if ( in_array( 'portfolio', $post_types ) ) {
			remove_meta_box( 'postimagediv', 'portfolio', 'side' ); 
			add_meta_box( 'postimagediv', __( 'Portfolio Featured Image', 'presscoders' ), 'post_thumbnail_meta_box', 'portfolio', 'side', 'high' );
		}
	}

	/**
	 * Initialise custom post type meta boxes.
	 *
	 * @since 0.1.0
	 */
	public function portfolio_cpt_meta_box_init() {
		
		/* Main PF meta box. */
		add_meta_box( PC_THEME_NAME_SLUG.'-portfolio-cpt', __( 'Override Portfolio Links', 'presscoders' ), array( &$this, 'portfolio_cpt_meta_box' ), 'portfolio', 'normal', 'high' );
		/* Hook to save our meta box data when the post is saved. */
		add_action( 'save_post', array( &$this, 'portfolio_cpt_save_meta_box' ) );
		/* Hook to save our meta box data when the post is saved. */
		add_action( 'save_post', array( &$this, 'portfolio_cpt_save_meta_box' ) );
	}

	/**
	 * Display the meta box for portfolio data fields.
	 *
	 * @since 0.1.0
	 */
	public function portfolio_cpt_meta_box($post, $args) {

		/* Retrieve our custom meta box values */
		$portfolio_cpt_fi_link = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link', true); // featured image link
		$portfolio_cpt_title_link = get_post_meta($post->ID, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link', true); // portfolio title link

		?>

		<table width="100%">
			<tbody>
				<?php $hide_css = has_post_thumbnail($post->ID) ? '' : ' style="display:none;"'; ?>

				<tr<?php echo $hide_css; ?>>
					<td width="120">Featured Image Link:</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_portfolio_cpt_fi_link" value="<?php echo esc_attr( $portfolio_cpt_fi_link ); ?>"></td>
				</tr>
				<tr>
					<td width="120">Portfolio Title Link:</td>
					<td><input style="width:100%;" type="text" name="<?php echo PC_THEME_NAME_SLUG; ?>_portfolio_cpt_title_link" value="<?php echo esc_attr( $portfolio_cpt_title_link ); ?>"></td>
				</tr>
				<tr>
					<td colspan="2">
						<?php if( !has_post_thumbnail($post->ID) ) : ?>
						<span style="font-style:italic;color:#666;">By default the title links to the single portfolio post, but you can override this by adding a specific link above. To remove the title link altogether enter <span style="font-family:courier new;color: #444;font-weight:bold;">'none'</span> (without quotes).</span>
						<?php else : ?>
						<span style="font-style:italic;color:#666;">By default the title links to the single portfolio post, and the featured image links to the image URL. These can be overridden by adding specific links above. To remove either link altogether enter <span style="font-family:courier new;color: #444;font-weight:bold;">'none'</span> (without quotes).</span>
						<?php endif; ?>
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
	public function portfolio_cpt_save_meta_box($post_id) {
		global $typenow;

		/* Only work for specific post type */
		if( $typenow != 'portfolio' )
			return;

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link', esc_url(esc_attr($_POST[ PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link'])) );

		if( isset($_POST[ PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link']) )
			update_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link', esc_url(esc_attr($_POST[ PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link'])) );

		$post = get_post( $post_id );

		/* Try and set a default Portfolio group if one not assigned. */
		if ( 'publish' === $post->post_status ) {

			$defaults = array( 'portfolio_group' => array( 'default' ) );
			$taxonomies = get_object_taxonomies( $post->post_type );

			foreach ( (array) $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $post_id, $taxonomy );
				if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
					wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
				}
			}
		}
	}

	/**
	 * Save the custom post type meta box input field settings.
	 *
	 * @since 0.1.0
	 */
	public function update_cpt_messages( $messages ) {
		global $post, $post_ID;

		$messages['portfolio'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Portfolio updated.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'presscoders' ),
			3 => __( 'Custom field deleted.', 'presscoders' ),
			4 => __( 'Portfolio updated.', 'presscoders' ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Portfolio restored to revision from %s', 'presscoders' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Portfolio published.', 'presscoders' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Portfolio saved.', 'presscoders' ),
			8 => sprintf( __( 'Portfolio submitted.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Portfolio scheduled for: <strong>%1$s</strong>.', 'presscoders' ),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i', 'presscoders' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Portfolio draft updated.', 'presscoders' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
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
		if( $typenow != 'portfolio' )
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
	 * Add a framework filter to use taxonomy-portfolio_group.php for Portfolio archive primary sidebar.
	 *
	 * @since 0.1.0
	 */
	public function add_custom_pf_primary_sidebar_archive( $custom_archive_pages ) {

		$custom_archive_pages['taxonomy-portfolio_group.php'] = 'primary-portfolio-widget-area';
		return $custom_archive_pages;
	}

	/**
	 * Add a framework filter to use single-portfolio.php for Portfolio post primary sidebar.
	 *
	 * @since 0.1.0
	 */
	public function add_custom_pf_primary_sidebar_posts( $custom_theme_posts ) {

		$custom_theme_posts['single-portfolio.php'] = 'primary-portfolio-widget-area';
		return $custom_theme_posts;
	}

	/**
	 * Add a framework filter to use portfolio-page.php for Portfolio page primary sidebar.
	 *
	 * @since 0.1.0
	 */
	public function add_custom_pf_primary_sidebar_pages( $custom_theme_pages ) {

		$custom_theme_pages['portfolio-page.php'] = 'primary-portfolio-widget-area';
		return $custom_theme_pages;
	}

	/**
	 * Add Portfolio widget area.
	 *
	 * @since 0.1.0
	 */
	public function register_portfolio_sidebar() {

		register_sidebar( array(
			'name' => __( 'Portfolio', 'presscoders' ),
			'id' => 'primary-portfolio-widget-area',
			'description' => __( 'Portfolio widget area', 'presscoders' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
			'width' => 'normal'
		) );
	}
}

?>