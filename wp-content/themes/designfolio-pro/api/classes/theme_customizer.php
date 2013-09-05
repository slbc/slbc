<?php

/**
 * Theme Customizer class.
 *
 * Handles all framework functionality related to the theme customizer.
 *
 * @since 0.1.0
 */
class PC_Theme_Customizer {

	/**
	 * Theme Customizer class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		/* Initialize framework/theme specific customizer defaults. */
		$this->initialize_customizer_defaults();
		if( method_exists( 'PC_TS_Utility', 'theme_specific_customizer_defaults' ) )
			PC_TS_Utility::theme_specific_customizer_defaults();

		/* Register framework theme customizer controls. */
		add_action( 'customize_register', array( &$this, 'theme_customizer_register_controls' ) );

		add_action( 'admin_menu', array( &$this, 'add_admin_theme_customizer_menu' ) );
		add_action( 'admin_bar_menu', array( &$this, 'add_wp_toolbar_theme_options_link' ) );
	}

	/**
	 * Define and initialize theme customizer defaults array.
	 *
	 * @since 0.1.0
	 */
	public function initialize_customizer_defaults() {

		global $pc_customizer_defaults;

		$pc_customizer_defaults = array(	'chk_hide_description'			=> null,
											'pc_txt_logo_url'				=> null,
											'pc_txt_custom_google_font'		=> null,
											'pc-google-webfonts-selector'	=> 'h1, h2, h3, h4'
		);
	}

	/**
	 * Theme customizer supported features.
	 *
	 * @since 0.1.0
	 */
	public function theme_customizer_register_controls($wp_customize) {

		/* Reference theme customizer option defaults. */
		global $pc_customizer_defaults;

		/* Add checkbox to display/hide site tagline. */
		$wp_customize->add_setting( 'chk_hide_description', array(
			'default'        => $pc_customizer_defaults['chk_hide_description']
		) );
		$wp_customize->add_control( 'chk_hide_description', array(
			'label'    => __( 'Hide tagline', 'presscoders' ),
			'section'  => 'title_tagline',
			'type'     => 'checkbox',
		) );

		/* Add upload image control to handle site logo exclusively via the theme customizer. */
		$wp_customize->add_setting( 'pc_txt_logo_url', array(
			'default'        => $pc_customizer_defaults['pc_txt_logo_url']
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'pc_txt_logo_url', array(
			'label'   => __( 'Add Logo (replaces Site Title)', 'presscoders' ),
			'section' => 'title_tagline',
			'settings'   => 'pc_txt_logo_url',
		) ) );

		/* Add JS to footer to make the site title and tagline update in real-time. */
		if ( $wp_customize->is_preview() && ! is_admin() )
			add_action( 'wp_footer', array( $this, 'real_time_customize_preview' ), 21);

		/* Update theme customizer transport setting for site title and tagline so they change in real-time. */
		$wp_customize->get_setting('blogname')->transport='postMessage';
		$wp_customize->get_setting('blogdescription')->transport='postMessage';

		/* Update 'Site Title & Tagline' label to include 'Logo'. */
		if ( $wp_customize->is_preview() && ! is_admin() )
			add_action( 'wp_footer', array( $this, 'alter_title_and_tagline_label' ), 21);
	}

	/**
	 * Adds JavaScript to the previewer frame footer to enable live edit updates.
	 *
	 * @since 0.1.0
	 */
	public function real_time_customize_preview() {
		
		global $pc_customizer_defaults;
		
		?>
		<script type="text/javascript" defer="defer">
		( function( $ ){
		wp.customize('blogname',function( value ) {
			value.bind(function(to) {
				$('#site-title a').html(to);
			});
		});
		wp.customize('blogdescription',function( value ) {
			value.bind(function(to) {
				$('#site-description').html(to);
			});
		});
		} )( jQuery )
		</script>
		<?php 
	}

	/**
	 * Add JS to the previewer frame footer to alter the 'Site Title & Tagline' section label.
	 *
	 * @since 0.1.0
	 */
	public function alter_title_and_tagline_label() {
		?>
		<script type="text/javascript" defer="defer">
		( function( $ ){ jQuery('li#customize-section-title_tagline h3', window.top.document).text('Site Logo, Title & Tagline', '#00ff00');})( jQuery )
		</script>
		<?php 
	}

	/**
	 * Add a select box drop down to theme customizer to control color schemes.
	 *
	 * @since 0.1.0
	 */
	public function theme_customizer_register_color_schemes($wp_customize) {

		/* Add the color scheme choices if defined in the theme. */
		if( current_theme_supports('color-schemes') ) {

			global $pc_color_schemes;
			$flip_array = array_flip($pc_color_schemes); /* Swap array key => values. */

			$wp_customize->add_section( 'pc_color_scheme', array(
				'title'          => __( 'Color Scheme', 'presscoders' ),
				'priority'       => 35,
				'theme_supports' => 'color-schemes'
			) );

			$wp_customize->add_setting( PC_OPTIONS_DB_NAME.'['.PC_COLOR_SCHEME_DROPDOWN.']', array(
				'default'        => 'default',
				'type'           => 'option'
			) );

			$wp_customize->add_control( PC_OPTIONS_DB_NAME.'['.PC_COLOR_SCHEME_DROPDOWN.']', array(
				'label'   => 'Select Color Scheme:',
				'section' => 'pc_color_scheme',
				'type'    => 'select',
				'choices'    => $flip_array )
			);
		}
	}

	/**
	 * Add a select box drop downs to theme customizer to control Google web fonts.
	 *
	 * @since 0.1.0
	 */
	public function theme_customizer_register_google_webfonts($wp_customize) {

		/* Add the color scheme choices if defined in the theme. */
		if( current_theme_supports('google-fonts-customizer') ) {

			global $pc_customizer_defaults;
			global $pc_google_font_nice_list;

			$wp_customize->add_section( 'pc-google-webfonts-section', array(
				'title'          => __( 'Custom Font', 'presscoders' ),
				'priority'       => 35,
				'theme_supports' => 'google-fonts-customizer'
			) );

			/* Custom font. */
			$wp_customize->add_setting( 'pc-google-webfonts', array(
				'default'        => $pc_customizer_defaults['pc-google-webfonts']
			) );

			$wp_customize->add_control( 'pc-google-webfonts', array(
				'label'   => __( 'Choose Font:', 'presscoders' ),
				'section' => 'pc-google-webfonts-section',
				'type'    => 'select',
				'choices'    => $pc_google_font_nice_list )
			);

			$wp_customize->add_setting( 'pc_txt_custom_google_font', array(
				'default' => $pc_customizer_defaults['pc_txt_custom_google_font']
			) );
		 
			$wp_customize->add_control( 'pc_txt_custom_google_font', array(
				'label'   => 'Use Alternative Google Web Font',
				'section' => 'pc-google-webfonts-section',
				'type'    => 'text',
			) );

			$wp_customize->add_setting( 'pc-google-webfonts-selector', array(
				'default' => $pc_customizer_defaults['pc-google-webfonts-selector']
			) );
		 
			$wp_customize->add_control( 'pc-google-webfonts-selector', array(
				'label'   => 'Font CSS Selector',
				'section' => 'pc-google-webfonts-section',
				'type'    => 'text',
			) );
		}
	}

    /**
     * Enqueue theme customizer main Google font CSS.
     *
     * @since 0.1.0
     */
    public function enqueue_customizer_google_font() {

		global $pc_customizer_defaults;
		$pc_google_webfont = get_theme_mod( 'pc-google-webfonts', $pc_customizer_defaults['pc-google-webfonts'] );
		$pc_google_custom_webfont = get_theme_mod( 'pc_txt_custom_google_font', $pc_customizer_defaults['pc_txt_custom_google_font'] );

		/* Get a drop down Google font or custom font if specified. */
		if( empty($pc_google_custom_webfont) ) {
			$theme_google_font = $pc_google_webfont;
		}
		else {
			$theme_google_font = str_replace( 'http://fonts.googleapis.com/css?family=', '', $pc_google_custom_webfont );
		}
		
		$pc_google_font = explode(":", $theme_google_font);
		$theme_google_nice_font = str_replace( '+', ' ', $pc_google_font );
		$theme_google_nice_font = 'pc-google-font-'.strtolower(str_replace( ' ', '-', $theme_google_nice_font[0] ));
		$font_uri_base = 'http://fonts.googleapis.com/css?family=';

		/* Don't enqueue Google font if none selected, or 'None' specified in theme defaults (functions.php). */
		if( $theme_google_font != 'none' && $theme_google_font != 'None' ) {

			if( empty($pc_google_custom_webfont) ) {
				/* Use a Google font from the drop down list. */
				$src = $theme_google_font;
				wp_enqueue_style( $theme_google_nice_font, $font_uri_base.$src );
			}
			else {
				/* Use a custom Google font. */
				wp_enqueue_style( $theme_google_nice_font, $pc_google_custom_webfont );
			}
		}
	}

    /**
     * Enqueue theme customizer custom CSS selector(s) for Google fonts.
     *
     * @since 0.1.0
     */
    public function theme_customizer_google_fonts_css() {

		global $pc_customizer_defaults;
		$pc_google_webfont = get_theme_mod( 'pc-google-webfonts', $pc_customizer_defaults['pc-google-webfonts'] );
		$pc_google_webfonts_selector = get_theme_mod( 'pc-google-webfonts-selector', $pc_customizer_defaults['pc-google-webfonts-selector'] );
		$pc_google_custom_webfont = get_theme_mod( 'pc_txt_custom_google_font', $pc_customizer_defaults['pc_txt_custom_google_font'] );

		/* Get a drop down Google font or custom font if specified. */
		if( empty($pc_google_custom_webfont) ) {
			$theme_google_font = $pc_google_webfont;
		}
		else {
			$theme_google_font = str_replace( 'http://fonts.googleapis.com/css?family=', '', $pc_google_custom_webfont );
		}

		/* Don't output Google font CSS if no font selected. */
		if( $theme_google_font != 'none' && $theme_google_font != 'None' ) {

			/* Use the drop down Google font or custom font if specified. */
			$font_array  = explode(":", $theme_google_font);
			$theme_google_font = str_replace( '+', ' ', $font_array[0] );
			$font_css_str = $pc_google_webfonts_selector." { font-family: '".$theme_google_font."', serif; }";

			/* We have some CSS to output. */
			echo "\n<!-- ".PC_THEME_NAME." Google web font CSS -->\n";
			echo "<style type=\"text/css\">";
			if( $theme_google_font != 'none' && $theme_google_font != 'None' )
				echo $font_css_str;
			echo "</style>\n";
		}
	}

	/**
	 * Add theme customizer menu link to Appearance admin menu.
	 *
	 * @since 0.1.0
	 */
	public function add_admin_theme_customizer_menu() {
		add_theme_page( 'Theme Customizer', 'Theme Customizer', 'edit_theme_options', 'customize.php' );
	}

	/**
	 * Add theme customizer menu link to Appearance admin menu.
	 *
	 * @since 0.1.0
	 */
	public function add_wp_toolbar_theme_options_link($wp_admin_bar) {

		$href = get_admin_url().'themes.php?page='.PC_THEME_MENU_SLUG;

		$args = array(	'parent' => 'appearance',
						'id' => 'pc-theme-options',
						'title' => PC_THEME_NAME.' Options', 
						'href' => $href
		);
		$wp_admin_bar->add_node($args);
	}
}

?>