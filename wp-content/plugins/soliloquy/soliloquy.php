<?php
/*
Plugin Name: Soliloquy
Plugin URI: http://soliloquywp.com/
Description: Soliloquy is the best responsive WordPress slider plugin. Period.
Author: Thomas Griffin
Author URI: http://thomasgriffinmedia.com/
Version: 1.5.5.3
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*
	Copyright 2013	 Thomas Griffin	 (email : thomas@thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/** Load all of the necessary class files for the plugin */
spl_autoload_register( 'Tgmsp::autoload' );

/**
 * Init class for Soliloquy.
 *
 * Loads all of the necessary components for the Soliloquy plugin.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp {

	/**
	 * You can define your license key here.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $key = '';

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Current version of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.5.5.3';

	/**
	 * Holds a copy of the main plugin filepath.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private static $file = __FILE__;

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		/** Run a hook before the slider is loaded and pass the object */
		do_action_ref_array( 'tgmsp_init', array( $this ) );

		/** Run activation hook and make sure the WordPress version supports the plugin */
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		/** Add theme support for post thumbnails if it doesn't exist */
		if ( ! current_theme_supports( 'post-thumbnails' ) )
			add_theme_support( 'post-thumbnails' );

		/** Load the plugin */
		add_action( 'widgets_init', array( $this, 'widget' ) );
		add_action( 'init', array( $this, 'init' ) );

	}

	/**
 	 * Registers a plugin activation hook to make sure the current WordPress
 	 * version is suitable (>= 3.3.1) for use.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @global int $wp_version The current version of this particular WP instance
 	 */
	public function activation() {

		global $wp_version;

		if ( version_compare( $wp_version, '3.5.1', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( printf( __( 'Sorry, but your version of WordPress, <strong>%s</strong>, does not meet Soliloquy\'s required version of <strong>3.5.1</strong> to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>', 'soliloquy' ), $wp_version, admin_url() ) );
		}

		/** Add option to prevent extra queries */
		add_option( 'soliloquy_license_key' );

	}

	/**
 	 * Registers the widget with WordPress.
 	 *
 	 * @since 1.0.0
 	 */
	public function widget() {

		register_widget( 'Tgmsp_Widget' );

	}

	/**
	 * Loads the plugin upgrader, registers the post type and
	 * loads all the actions and filters for the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		/** Load the plugin textdomain for internationalizing strings */
		load_plugin_textdomain( 'soliloquy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		/** Setup the license checker */
		global $soliloquy_license;
		$soliloquy_license = get_option( 'soliloquy_license_key' );

		// Only run certain processes in the admin.
		if ( is_admin() ) :
			/** Only process upgrade and addons page if a key has been entered and verified */
			if ( isset( $soliloquy_license['license'] ) ) {
				$args = array(
					'remote_url' 	=> 'http://soliloquywp.com/',
					'version' 		=> $this->version,
					'plugin_name'	=> 'Soliloquy',
					'plugin_slug' 	=> 'soliloquy',
					'plugin_path' 	=> plugin_basename( __FILE__ ),
					'plugin_url' 	=> WP_PLUGIN_URL . '/soliloquy',
					'time' 			=> 43200,
					'key' 			=> $soliloquy_license['license']
				);

				/** Instantiate the automatic plugin upgrader class */
				$tgmsp_updater = new Tgmsp_Updater( $args );

				/** Load the addons page */
				if ( isset( $soliloquy_license['single'] ) && ! $soliloquy_license['single'] || ! isset( $soliloquy_license['single'] ) )
					$tgmsp_addons = new Tgmsp_Addons( $soliloquy_license['license'] );

				/** Load the updates page */
				$tgmsp_updates = new Tgmsp_Updates();
			}

			/** Instantiate all the necessary admin components of the plugin */
			$tgmsp_admin	   = new Tgmsp_Admin();
			$tgmsp_ajax		   = new Tgmsp_Ajax();
			$tgmsp_adminassets = new Tgmsp_AdminAssets();
			$tgmsp_editor	   = new Tgmsp_Editor();
			$tgmsp_help		   = new Tgmsp_Help();
			$tgmsp_license	   = new Tgmsp_License();
			$tgmsp_media	   = new Tgmsp_Media();
		endif;

		// Load these components regardless.
		$tgmsp_assets	 = new Tgmsp_Assets();
		$tgmsp_posttype	 = new Tgmsp_Posttype();
		$tgmsp_shortcode = new Tgmsp_Shortcode();
		$tgmsp_strings	 = new Tgmsp_Strings();

	}

	/**
	 * PSR-0 compliant autoloader to load classes as needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classname The name of the class
	 * @return null Return early if the class name does not start with the correct prefix
	 */
	public static function autoload( $classname ) {

		if ( 'Tgmsp' !== mb_substr( $classname, 0, 5 ) )
			return;

		$filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		if ( file_exists( $filename ) )
			require $filename;

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

	/**
	 * Getter method for retrieving the license key.
	 *
	 * @since 1.0.0
	 */
	public static function get_key() {

		return self::$key;

	}

	/**
	 * Getter method for retrieving the main plugin filepath.
	 *
	 * @since 1.2.0
	 */
	public static function get_file() {

		return self::$file;

	}

	/**
	 * Getter method for retrieving all Soliloquy sliders.
	 *
	 * @since 1.3.0
	 */
	public static function get_sliders() {

		$args = array(
			'post_type' 		=> 'soliloquy',
			'posts_per_page' 	=> -1
		);

		return get_posts( $args );

	}

	/**
	 * Helper flag method for any Soliloquy screen.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if on a Soliloquy screen, false if not
	 */
	public static function is_soliloquy_screen() {

		$current_screen = get_current_screen();

		if ( ! $current_screen )
			return false;

		if ( 'soliloquy' == $current_screen->post_type )
			return true;

		return false;

	}

	/**
	 * Helper flag method for the Add/Edit Soliloquy screens.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if on a Soliloquy Add/Edit screen, false if not
	 */
	public static function is_soliloquy_add_edit_screen() {

		$current_screen = get_current_screen();

		if ( ! $current_screen )
			return false;

		if ( 'soliloquy' == $current_screen->post_type && 'post' == $current_screen->base )
			return true;

		return false;

	}

}

/** Instantiate the init class */
$tgmsp = new Tgmsp();

if ( ! function_exists( 'soliloquy_slider' ) ) {
	/**
	 * Template tag function for outputting the slider within templates.
	 *
	 * @since 1.0.0
	 *
	 * @package Soliloquy
	 * @param int|string $id The Soliloquy slider ID or unique slug
	 * @param bool $return Flag for returning or echoing the slider content
	 */
	function soliloquy_slider( $id, $return = false ) {

		/** Check if slider ID is an integer or string */
		if ( is_numeric( $id ) )
			$id = absint( $id );
		else
			$id = esc_attr( $id );

		/** Return if no slider ID has been entered or if it is not valid */
		if ( ! $id || empty( $id ) ) {
			printf( '<p>%s</p>', Tgmsp_Strings::get_instance()->strings['no_id'] );
			return;
		}

		/** Validate based on type of ID submitted */
		if ( is_numeric( $id ) ) {
			$validate = get_post( $id, OBJECT );
			if ( ! $validate || isset( $validate->post_type ) && 'soliloquy' !== $validate->post_type ) {
				printf( '<p>%s</p>', Tgmsp_Strings::get_instance()->strings['invalid_id'] );
				return;
			}
		} else {
			$validate = get_page_by_path( $id, OBJECT, 'soliloquy' );
			if ( ! $validate ) {
				printf( '<p>%s</p>', Tgmsp_Strings::get_instance()->strings['invalid_slug'] );
				return;
			}
		}

		if ( $return )
			return do_shortcode( '[soliloquy id="' . $id . '"]' );
		else
			echo do_shortcode( '[soliloquy id="' . $id . '"]' );

	}
}