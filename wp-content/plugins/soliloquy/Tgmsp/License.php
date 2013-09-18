<?php
/**
 * License class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_License {

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

		add_action( 'admin_init', array( $this, 'admin_init' ), 5 );

	}

	/**
	 * Registers the option setting for license keys and processes license verification.
	 *
	 * @since 1.0.0
	 *
	 * @global array $soliloquy_license Array of Soliloquy license info
	 */
	public function admin_init() {

		global $soliloquy_license;

		/** Setup transients to perform license validation checks */
		if ( false === ( $validation_transient = get_transient( 'soliloquy_license_validation' ) ) ) {
			$validation_transient = $this->run_validation_check();
			set_transient( 'soliloquy_license_validation', $validation_transient, 60*60*24 );
		}

		/** If the user has pre-defined their license key and it hasn't already been validated, go ahead and validate it */
		if ( ( Tgmsp::get_key() || defined( 'SOLILOQUY_LICENSE_KEY' ) ) && ! isset( $soliloquy_license['license'] ) ) {
			$key 		= Tgmsp::get_key() ? Tgmsp::get_key() : SOLILOQUY_LICENSE_KEY; // It's going to be one or the other if we get here
			$verify_key = self::perform_remote_request( 'verify-soliloquy-license', array( 'key' => $key ) );

			/** Return early is there is an error (but output no notices) */
			if ( is_wp_error( $verify_key ) )
				return;

			/** Return early if there is an error verifying a key (but output no notices) */
			if ( isset( $verify_key->key_error ) )
				return;

			/** If we have reached this point, the key has been successfully verified */
			$license_key 				= array();
			$license_key['license'] 	= ( isset( $verify_key->license ) ) ? $verify_key->license : '';
			$license_key['single'] 		= ( isset( $verify_key->single ) && $verify_key->single ) ? true : false;
			update_option( 'soliloquy_license_key', $license_key );
			return;
		}

		/** Process license key verification */
		if ( isset( $_POST[sanitize_key( 'verify_soliloquy_license' )] ) && 'true' == $_POST[sanitize_key( 'verify_soliloquy_license' )] ) {
			/** Security check */
			check_admin_referer( 'soliloquy-verify-license-key' );

			/** We need to verify the plugin license */
			$verify_key = self::perform_remote_request( 'verify-soliloquy-license', array( 'key' => $_POST['soliloquy_license_key'] ) );

			/** Return early is there is an error */
			if ( is_wp_error( $verify_key ) ) {
				add_settings_error( 'tgmsp', 'tgmsp-http-error', $verify_key->get_error_message(), 'error' );
				return;
			}

			/** Return early and output message if there is an error verifying a key */
			if ( isset( $verify_key->key_error ) ) {
				add_settings_error( 'tgmsp', 'tgmsp-key-error', __( $verify_key->key_error, 'soliloquy' ), 'error' );
				return;
			}

			/** If we have reached this point, the key has been successfully verified */
			$license_key 				= array();
			$license_key['license'] 	= ( isset( $verify_key->license ) ) ? $verify_key->license : '';
			$license_key['single'] 		= ( isset( $verify_key->single ) && $verify_key->single ) ? true : false;
			update_option( 'soliloquy_license_key', $license_key );

			/** Redirect the user back to the license verification page */
			wp_redirect( add_query_arg( apply_filters( 'tgmsp_validation_redirect_args', array( 'post_type' => 'soliloquy', 'page' => 'soliloquy-settings' ), $license_key ), admin_url( 'edit.php' ) ) );
			exit;
		}

		/** Process license key deactivation */
		if ( isset( $_POST[sanitize_key( 'deactivate_soliloquy_license' )] ) && 'true' == $_POST[sanitize_key( 'deactivate_soliloquy_license' )] ) {
			/** Security check */
			check_admin_referer( 'soliloquy-deactivate-license-key' );

			/** We need to deactivate the license for this site */
			$deactivate = self::perform_remote_request( 'deactivate-soliloquy-license', array( 'key' => $_POST['soliloquy_license_key'] ) );

			/** Return early is there is an error */
			if ( is_wp_error( $deactivate ) ) {
				add_settings_error( 'tgmsp', 'tgmsp-http-error', $verify_key->get_error_message(), 'error' );
				return;
			}

			/** Return early and output message if there is an error verifying a key */
			if ( isset( $deactivate->deactivate_error ) ) {
				add_settings_error( 'tgmsp', 'tgmsp-deactivate-error', __( $deactivate->deactivate_error, 'soliloquy' ), 'error' );
				return;
			}

			/** If we have reached this point, the key has been deactivated, so deactivate automatic upgrades */
			delete_option( 'soliloquy_license_key' );

			/** Redirect the user back to the license verification page */
			wp_redirect( add_query_arg( apply_filters( 'tgmsp_deactivation_redirect_args', array( 'post_type' => 'soliloquy', 'page' => 'soliloquy-settings', 'deactivate_license' => true ), $soliloquy_license ), admin_url( 'edit.php' ) ) );
			exit;
		}

	}

	/**
	 * Queries the remote URL via wp_remote_post and returns a json decoded response.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The name of the $_POST action var
	 * @param array $body The content to retrieve from the remote URL
	 * @param array $headers The headers to send to the remote URL
	 * @param string $return_format The format for returning content from the remote URL
	 * @return string|boolean Json decoded response on success, false on failure
	 */
	public static function perform_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {

		/** Build body */
		$body = wp_parse_args( $body, array(
			'action' 					=> $action,
			'wp-version' 				=> get_bloginfo( 'version' ),
			'referer' 					=> site_url(),
			'single' 					=> 'false'
		) );
		$body = http_build_query( $body, '', '&' );

		/** Build headers */
		$headers = wp_parse_args( $headers, array(
			'Content-Type' 		=> 'application/x-www-form-urlencoded',
			'Content-Length' 	=> strlen( $body )
		) );

		/** Setup variable for wp_remote_post */
		$post = array(
			'headers' 	=> $headers,
			'body' 		=> $body
		);

		/** Perform the query and retrieve the response */
		$response 		= wp_remote_post( esc_url_raw( 'http://soliloquywp.com/' ), $post );
		$response_code 	= wp_remote_retrieve_response_code( $response );
		$response_body 	= wp_remote_retrieve_body( $response );

		/** Bail out early if there are any errors */
		if ( is_wp_error( $response ) )
			return new WP_Error( 'http-error', Tgmsp_Strings::get_instance()->strings['http_error'] );

		if ( 200 != $response_code || is_wp_error( $response_body ) )
			return false;

		/** Return body content if not json, else decode json */
		if ( 'json' != $return_format )
			return $response_body;
		else
			return json_decode( $response_body );

		return false;

	}

	/**
	 * Callback for license cron. Pings the API to make sure licenses are valid.
	 *
	 * @since 1.0.0
	 */
	public function run_validation_check() {

		/** Make sure the option is still in the DB before running the check */
		global $soliloquy_license;
		if ( ! isset( $soliloquy_license['license'] ) )
			return;

		/** Query the API to validate the license key */
		$validate = self::perform_remote_request( 'validate-soliloquy-license', array( 'key' => $soliloquy_license['license'] ) );

		/** Force the plugin to keep checking if there are errors in connecting */
		if ( is_wp_error( $validate ) )
			delete_transient( 'soliloquy_license_validation' );

		// Change the license type if necessary.
		if ( isset( $validate->response ) )
			if ( 'developer' == $validate->response && isset( $soliloquy_license['single'] ) && $soliloquy_license['single'] )
				$soliloquy_license['single'] = false;

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