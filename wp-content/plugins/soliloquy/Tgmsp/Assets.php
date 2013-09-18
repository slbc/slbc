<?php
/**
 * Aseets class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Assets {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		/** Load dev scripts and styles if in Soliloquy dev mode */
		$dev = defined( 'SOLILOQUY_DEV' ) && SOLILOQUY_DEV ? '-dev' : '';

		/** Register scripts and styles */
		wp_register_script( 'soliloquy-script', plugins_url( 'js/soliloquy.js', dirname( __FILE__ ) ), array( 'jquery' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-fitvids', plugins_url( 'js/fitvids.js', dirname( __FILE__ ) ), array( 'jquery' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-mousewheel', plugins_url( 'js/mousewheel.js', dirname( __FILE__ ) ), array( 'jquery' ), Tgmsp::get_instance()->version, true );
		wp_register_script( 'soliloquy-vimeo', 'http://a.vimeocdn.com/js/froogaloop2.min.js', array( 'jquery' ), Tgmsp::get_instance()->version, true );
		wp_register_style( 'soliloquy-style', plugins_url( 'css/soliloquy' . $dev . '.css', dirname( __FILE__ ) ), array(), Tgmsp::get_instance()->version );

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}