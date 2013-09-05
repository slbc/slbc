<?php

/* Load Press Coders theme framework class */
if( file_exists( get_template_directory().'/api/classes/api.php' ) ) {
	require_once( get_template_directory().'/api/classes/api.php' );
}

if( !class_exists( 'PC_Main_Theme' ) ) :

class PC_Main_Theme extends PC_Framework {

	public function __construct($theme_name) {
		/* Call parent construtor manually to make both constructors fire. */
		parent::__construct($theme_name);

		/* Add theme support for framework features. */
		add_action( 'after_setup_theme', array( &$this, 'theme_support' ) );
	}

	/* Add support for theme features. */
	public function theme_support() {

		/** CONSTANTS AND GLOBAL VARIABLES **/

		define( 'PC_INSTALL_DEFAULT_CONTENT', TRUE );
		define( 'PC_INSTALL_CONTENT_PROMPT', TRUE );

		/** WORDPRESS BUILT-IN SUPPORTED THEME FEATURES **/

		add_theme_support( 'automatic-feed-links' );	/* Add posts and comments RSS feed links to head. */
		add_theme_support( 'post-thumbnails' );			/* Use the post thumbnails feature. */
		add_theme_support( 'custom-background' );		/* A simple uploader to change the site background image. */
		add_editor_style();								/* Post/page editor style sheet to match site styles. */

		/** FRAMEWORK SUPPORTED THEME FEATURES **/

		add_theme_support( 'theme-options-page' );							/* Display a theme options page. */
		add_theme_support( 'seo' );											/* Enable SEO functionality. */
		add_theme_support( 'custom-css' );									/* Enable custom CSS editing. */
		add_theme_support( 'hf-code-insert' );								/* Custom header/footer code insert, plus flexible footer links. */
		add_theme_support( 'shortcodes' );									/* Include all framework shortcodes. */
		add_theme_support( 'sidebar-commander' );							/* Create custom widget areas. */
		add_theme_support( 'social-media-buttons', 'pc_pre_post_meta' );	/* Include the social media buttons in single.php. */
		add_theme_support( 'breadcrumb-trail' );							/* Display breadcrumb trail. */
		add_theme_support( 'fancybox' );									/* Include Fancybox lightbox. */
		add_theme_support( 'superfish' );									/* Load Superfish jQuery menu. */
		add_theme_support( 'modernizr' );									/* Load Modernizr library. */
		add_theme_support( 'fitvids' );										/* Responsive video resizing. */
		//add_theme_support( 'simple-debug' );
		add_theme_support( 'auto-update', array( 'http://wp-updates.com/api/1/theme', 231 ) ); /* Auto update theme. */
		
		add_theme_support( 'custom-post-types', array(	'testimonials',
														'slides',
														'portfolio' => array(	'large-pf-image' => array( 466, 250 ),
																				'medium-pf-image' => array( 303, 200 ),
																				'small-pf-image' => array( 221, 150 ) )
		) ); /* Load framework CPT's. */

        /* Include specified framework widgets. */
		add_theme_support( 'pc_widgets',	'twitter-feed',
											'tml',
											'theme-recent-posts',
											'blog-style-recent-posts',
											'color-scheme-switcher',
											array( 'content-slider' => array(	'one-third-width' => 270,
																				'two-thirds-width' => 650,
																				'full-width' => 960,
																				'height' => 300,
																				'nav-arrows' => true )
											),
											'info-box'
		);

		/* Add array of menu location labels, or leave 2nd parameter blank for a single default menu. */
		add_theme_support( 'custom-menus', array( 'Primary Navigation', 'Top Menu' ) );

		/* Add array of theme color schemes. */
		add_theme_support( 'color-schemes',  array( __( 'Navy', 'presscoders' ) => 'default',
													__( 'Gray with Orange', 'presscoders' ) => 'gray-with-orange',
													__( 'Gray with Blue', 'presscoders' ) => 'dark-gray',
													__( 'Blue', 'presscoders' ) => 'blue',
													__( 'Black', 'presscoders' ) => 'black',
													__( 'Brown', 'presscoders' ) => 'brown',
													__( 'Light Gray', 'presscoders' ) => 'light-gray' ) );

		/* Display Google web font options. */
		add_theme_support( 'google-fonts', array(
										'default' => 'Droid+Sans:400,700',
										'font_list' => array(	'PT+Sans+Narrow:400,700',
																'Droid+Sans:400,700',
																'Droid+Serif:400,700,700italic,400italic',
																'Oswald',
																'Ubuntu:300,400,500,700,300italic,400italic,500italic,700italic',
																'Anton',
																'Arvo:400,700,400italic,700italic' )
										)
		);

		/* ADDITIONAL THEME FEATURES */

		/* Default thumbnail size for post thumbnails. */
		set_post_thumbnail_size( 580, 200, true );

		/* Example adding an extra custom thumbnail size. */
		// add_image_size( 'blog-thumb', 620, 300, true );
	}

} /* End class definition */

/* Create theme class instance */
global $pc_theme_object;
$pc_theme_object = new PC_Main_Theme( 'Designfolio Pro' );

endif; /* Endif class definition and instantiation. */

?>