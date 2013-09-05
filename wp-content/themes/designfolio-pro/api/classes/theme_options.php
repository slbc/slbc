<?php

/**
 * Theme options class.
 *
 * Handles all the functionality for theme options.
 *
 * @since 0.1.0
 */
class PC_Theme_Options {

	/* Handle to the theme options page */
	protected $_theme_options_page;

	/**
	 * Theme options class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		$this->default_theme_options();

		add_action( 'admin_init', array( &$this, 'register_theme_settings' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'theme_admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'theme_options_page_init' ) );
	}

	/**
	 * Adds html code defined in theme options textarea to theme header.
	 *
	 * @since 0.1.0
	 */
	public function theme_header_html() {
		$options = get_option( PC_OPTIONS_DB_NAME );
		if( !empty($options['txtarea_header']) ) { echo $options['txtarea_header']; }
	}

	/**
	 * Adds html code defined in theme options textarea to theme footer.
	 *
	 * @since 0.1.0
	 */
	public function theme_footer_html() {
		$options = get_option( PC_OPTIONS_DB_NAME );
		if( !empty($options['txtarea_footer']) ) { echo $options['txtarea_footer']; }
	}

	/**
	 * Use solid background color.
	 *
	 * @since 0.1.0
	 */
	public function pc_solid_header_bg_color() {
		$options = get_option( PC_OPTIONS_DB_NAME );

		if ( isset($options[ 'chk_solid-header-bg' ]) ) { ?>

<style type="text/css">
	#header { background: transparent; }
</style>
			<?php
		} // endif
	}

	/**
	 * Register theme options with Settings API.
	 *
	 * @since 0.1.0
	 */
	public function register_theme_settings(){
		register_setting( PC_THEME_OPTIONS_GROUP, PC_OPTIONS_DB_NAME );
	}

	/**
	 * Register admin scripts and styles, ready for enqueueing on the theme options page
	 *
	 * @since 0.1.0
	 */
	public function theme_admin_init(){

		// Register theme option style sheets
		wp_register_style( 'jquery-ui-base-css', 'http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css' );
		wp_register_style( 'theme_admin_stylesheet', PC_THEME_ROOT_URI.'/includes/css/theme_admin.css' );
		// @todo delete this if no longer needed
		// wp_register_style('theme_colorpicker_stylesheet', PC_THEME_ROOT_URI.'/api/js/colorpickers/colorpicker/css/colorpicker.css');
	}

	/**
	 * Register theme options page, and enqueue scripts/styles.
	 *
	 * @since 0.1.0
	 */
	public function theme_options_page_init() {

		/* @todo Add this in via a hook with cb function in theme specific utility cb class. */
		$options_menu_label = ( PC_THEME_NAME == 'Designfolio Pro' ) ? 'Designfolio Options' : PC_THEME_NAME.' Options';
		
		$this->_theme_options_page = add_theme_page( PC_THEME_NAME." Options", $options_menu_label, 'edit_theme_options', PC_THEME_MENU_SLUG, array( &$this, 'render_theme_form' ) );

		global $pcc;
		$pcc = $this->_theme_options_page;

		/* Enqueue scripts and styles for the theme option page */
		add_action( "admin_print_styles-$this->_theme_options_page", array( &$this,  'theme_admin_styles' ) );
		add_action( "admin_print_scripts-$this->_theme_options_page", array( &$this,  'theme_admin_scripts' ) );
	}

	/**
	 * Enqueue theme options page scripts.
	 *
	 * @since 0.1.0
	 */
	public function theme_admin_scripts() {

		/* Scripts for theme options page only. */
		wp_enqueue_script( 'jquery-ui-tooltip' );
	}

	/**
	 * Enqueue theme options page styles.
	 *
	 * @since 0.1.0
	 */
	public function theme_admin_styles() {

		/* Styles for theme options page only. */
		wp_enqueue_style( 'theme_admin_stylesheet' );
		wp_enqueue_style( 'jquery-ui-base-css' );
	}

	/**
	 * Display theme options page.
	 *
	 * @since 0.1.0
	 */
	public function render_theme_form() {
		/* Include the options form for now, as it is quite a long file */
		/* This should be generated in the future(?). At least add in hooks to let users extend the options form. */
		
		require_once( PC_THEME_ROOT_DIR.'/includes/admin/theme_options_form.php' );
	}

	/**
	 * Set default theme options.
	 * 
	 * This function updates the theme options with the specified defaults ONLY if they don't exist. The
	 * idea being that new theme options can be added by a developer and picked up by the theme without having
	 * to blank the existing theme options first. If some options exist then merge current options with defaults
	 * to set any new theme options that may have been added since last theme activation. This method does NOT
	 * overwrite any theme options if they already exist.
	 *
	 * Important: Any checkboxes you need to be ON by default just add them to an array, in addition to the
	 * $pc_default_options array, but with a "0" value. Otherwise when the theme is deactivated/reactivated the ON by
	 * default checkboxes will always be set to ON even if the user set to off in the meantime. This is because if
	 * a check box is turned off it is not stored in the options db at all, but to be efectively tested by the
     * array_merge($pc_default_options, $current_options) function, if it has been turned off by the user then it
     * needs to be set to zero manually in the code.
	 *
	 * @since 0.1.0
	 */
	public function default_theme_options() {

		/* Define as global to accessible anywhere (i.e. from within hook callbacks). */
		global $pc_default_options;

		/* Define some theme specific option constants. */
		define( "PC_ADMIN_EMAIL_TEXTBOX", "txt_admin_email" );
		define( "PC_DEFAULT_LAYOUT_THEME_OPTION", "drp_default_layout" );

		/* Defaults options array. */
		$pc_default_options = array(
					PC_DEFAULT_LAYOUT_THEME_OPTION => "2-col-r",
					"chk_show_social_buttons" => "1",
					PC_ADMIN_EMAIL_TEXTBOX => get_bloginfo( 'admin_email' ),
                    "txt_favicon" => ""
		);

		/* Get a copy of the current theme options. */
		$current_options = get_option( PC_OPTIONS_DB_NAME );

		/* If theme options not set yet then don't bother trying to merge with the $pc_default_off_checkboxes. */
		if ( is_array($current_options)) {
            /* Define as global to accessible anywhere (i.e. from within hook callbacks). */
            global $pc_default_off_checkboxes;

			$pc_default_off_checkboxes = array(
									"chk_show_social_buttons" => "0",
									);
		}

        /* Add theme specific default settings vis this hook. */
        PC_Hooks::pc_theme_option_defaults(); /* Framework hook wrapper */

		/* Added this here rather inside the same 'if' statement above so we can add extra $pc_default_off_checkboxes via a hook. */
		if ( is_array($current_options)) {
			/* Manually set the checkboxes that have been unchecked, by the user, to zero. */
			$current_options = array_merge($pc_default_off_checkboxes, $current_options);
		}

		/* If there are no existing options just use defaults (no merge). */
		if ( !$current_options || empty($current_options) ) {
			// Update options in db
			update_option( PC_OPTIONS_DB_NAME, $pc_default_options);
		}
		/* Else merge existing options with current ones (new options are added, but none are overwritten). */
		else {
			/* Merge current options with the defaults, i.e. add any new options but don't overwrite existing ones. */
			$result = array_merge($pc_default_options, $current_options);

			/* Update options in db. */
			update_option( PC_OPTIONS_DB_NAME, $result);
		}
	}
}

?>