<?php
/** Load the WP Upgrader class so we can extend the skin */
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Addons skin class for Soliloquy.
 *
 * @since 1.2.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Skin extends WP_Upgrader_Skin {

	/**
	 * Constructor (defaults to the parent constructor).
	 *
	 * @since 1.2.0
	 */
	public function __construct( $args = array() ) {

		parent::__construct();

	}

	/**
	 * Set the upgrader object and store it as a property in the parent class.
	 *
	 * @since 1.2.0
	 */
	public function set_upgrader( &$upgrader ) {

		if ( is_object( $upgrader ) )
			$this->upgrader =& $upgrader;

	}

	/**
	 * Set the upgrader result and store it as a property in the parent class.
	 *
	 * @since 1.2.0
	 */
	public function set_result( $result ) {

		$this->result = $result;

	}

	/**
	 * Empty out the header of its HTML content and only check to see if it has
	 * been performed or not.
	 *
	 * @since 1.2.0
	 */
	public function header() {}

	/**
	 * Empty out the footer of its HTML contents.
	 *
	 * @since 1.2.0
	 */
	function footer() {}

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 1.2.0
	 */
	function error( $errors ) {

		if ( ! empty( $errors ) )
			echo json_encode( array( 'error' => Tgmsp_Strings::get_instance()->strings['addon_install_error'] ) );

	}

	/**
	 * Empty out the feedback method to prevent outputting HTML strings as the install
	 * is progressing.
	 *
	 * @since 1.2.0
	 */
	function feedback( $string ) {}

}