<?php
/**
 * Addons class for Soliloquy.
 *
 * @since 1.2.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Addons {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.2.0
	 *
	 * @var object
	 */
	private static $instance;
	
	/**
	 * Holds the pagehook for the Addons page.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private $pagehook;
	
	/**
	 * Holds the license key for reference when listing, getting and installing addons.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private $license_key;

	/**
	 * Constructor. Authenticates the license key, and if successful, hooks all 
	 * interactions to initialize the class.
	 *
	 * @since 1.2.0
	 */
	public function __construct( $key = '' ) {
	
		self::$instance = $this;
	
		/** Return early if the Addons constant is defined and set to false */
		if ( defined( 'SOLILOQUY_ADDONS_PAGE' ) && ! SOLILOQUY_ADDONS_PAGE )
			return;
	
		/** If we cannot authenticate the license key and confirm that it is a developer license key, do nothing */
		if ( false === ( $authenticate = get_transient( 'soliloquy_authenticate_addons' ) ) ) {
			$authenticate = $this->authenticate( $key );
			set_transient( 'soliloquy_authenticate_addons', $authenticate, 60*60*24 );
		}
		
		/** The key can't be authenticated, so return early */
		if ( ! $authenticate )
			return;
		
		/** The key has been verified, so let's begin creating the Addons area */
		$this->license_key = $key;
	
		/** Begin creating the Addons area */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_soliloquy_activate_addon', array( $this, 'activate_addon' ) );
		add_action( 'wp_ajax_soliloquy_deactivate_addon', array( $this, 'deactivate_addon' ) );
		add_action( 'wp_ajax_soliloquy_install_addon', array( $this, 'install_addon' ) );
	
	}
	
	/**
	 * Adds a menu item to the Soliloquy post type.
	 *
	 * @since 1.2.0
	 */
	public function admin_menu() {

		/** Create the submenu page and store the pagehook for reference */
		$this->pagehook = apply_filters( 'tgmsp_addons_page', add_submenu_page( 'edit.php?post_type=soliloquy', Tgmsp_Strings::get_instance()->strings['addons_page_title'], Tgmsp_Strings::get_instance()->strings['addons_menu_title'], apply_filters( 'tgmsp_settings_cap', 'manage_options' ), 'soliloquy-addons', array( $this, 'soliloquy_addon_settings' ) ) );
		
		/** Load any necessary elements for our Addons page */
		if ( $this->pagehook )
			add_action( 'load-' . $this->pagehook, array( $this, 'init' ) );

	}

	/**
	 * Outputs the admin UI for the Addons page.
	 *
	 * @since 1.2.0
	 */
	public function soliloquy_addon_settings() {
		
		echo '<div id="soliloquy-addon-area" class="wrap">';
			screen_icon( 'soliloquy' );
			echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';
			echo '<p class="addon-intro"><strong>' . Tgmsp_Strings::get_instance()->strings['addons_intro'] . '</strong></p>';
			
			/** Store addon data as a transient */
			if ( false === ( $addon_data = get_transient( 'soliloquy_addon_data' ) ) ) {
				$addon_data = Tgmsp_License::perform_remote_request( 'get-addons-data', array( 'key' => $this->license_key ) );
				set_transient( 'soliloquy_addon_data', $addon_data, 60*60*12 );
			}

			/** If there is an error with grabbing the addon data, delete the transient and output an error notice */
			if ( is_wp_error( $addon_data ) ) {
				delete_transient( 'soliloquy_addon_data' );
				add_settings_error( 'tgmsp', 'tgmsp-addon-error', '', 'error' );
				return;
			}
			
			/** If there was a key validation error, output the notice */
			if ( isset( $addon_data->key_error ) ) {
				add_settings_error( 'tgmsp', 'tgmsp-addon-key-error', '', 'error' );
				return;
			}
			
			/** We've successfully grabbed the data, so let's start manipulating it */
			$i = 0;
			foreach ( (array) $addon_data as $i => $addon ) {
				/** Attempt to get the plugin basename if it is installed or active */
				$plugin_basename 	= $this->get_plugin_basename_from_slug( $addon->slug );
				$installed_plugins 	= get_plugins();
				$last				= ( 2 == $i%3 ) ? 'last' : '';
				
				echo '<div class="soliloquy-addon ' . $last . '">';
					echo '<img class="soliloquy-addon-thumb" src="' . esc_url( $addon->image ) . '" width="300px" height="250px" alt="' . esc_attr( $addon->title ) . '" />';
					echo '<h3 class="soliloquy-addon-title">' . esc_html( $addon->title ) . '</h3>';
					
					/** If the plugin is active, display an active message and deactivate button */
					if ( is_plugin_active( $plugin_basename ) ) {
						echo '<div class="soliloquy-addon-active soliloquy-addon-message">';
							echo '<span class="addon-status">' . Tgmsp_Strings::get_instance()->strings['addon_active'] . '</span>';
							echo '<div class="soliloquy-addon-action">';
								echo '<a class="button-secondary soliloquy-addon-action-button soliloquy-deactivate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . Tgmsp_Strings::get_instance()->strings['addon_deactivate'] . '</a>';
							echo '</div>';
						echo '</div>';
					}
					
					/** If the plugin is not installed, display an install message and install button */
					if ( ! isset( $installed_plugins[$plugin_basename] ) ) {
						echo '<div class="soliloquy-addon-not-installed soliloquy-addon-message">';
							echo '<span class="addon-status">' . Tgmsp_Strings::get_instance()->strings['addon_not_installed'] . '</span>';
							echo '<div class="soliloquy-addon-action">';
								echo '<a class="button-secondary soliloquy-addon-action-button soliloquy-install-addon" href="#" rel="' . esc_url( $addon->url ) . '">' . Tgmsp_Strings::get_instance()->strings['addon_install'] . '</a>';
							echo '</div>';
						echo '</div>';
					}
					/** If the plugin is installed but not active, display an activate message and activate button */
					elseif ( is_plugin_inactive( $plugin_basename ) ) {
						echo '<div class="soliloquy-addon-inactive soliloquy-addon-message">';
							echo '<span class="addon-status">' . Tgmsp_Strings::get_instance()->strings['addon_inactive'] . '</span>';
							echo '<div class="soliloquy-addon-action">';
								echo '<a class="button-secondary soliloquy-addon-action-button soliloquy-activate-addon" href="#" rel="' . esc_attr( $plugin_basename ) . '">' . Tgmsp_Strings::get_instance()->strings['addon_activate'] . '</a>';
							echo '</div>';
						echo '</div>';
					}
					
					echo '<p class="soliloquy-addon-excerpt">' . esc_html( $addon->excerpt ) . '</p>';
				echo '</div>';
				$i++;
			}		
		echo '</div>';

	}
	
	/**
	 * Loads interactions on the Addons page. This function is only run on the Addons page so
	 * we can rest assured that nothing is being loaded where it shouldn't.
	 *
	 * @since 1.2.0
	 */
	public function init() {
	
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	
	}
	
	/**
	 * Enqueue custom scripts and styles for the Soliloquy Addons page.
	 *
	 * @since 1.2.0
	 */
	public function load_assets() {
	
		/** Load dev scripts and styles if in Soliloquy dev mode */
		$dev = defined( 'SOLILOQUY_DEV' ) && SOLILOQUY_DEV ? '-dev' : '';

		/** Load the CSS for the Addons area */
		wp_register_style( 'soliloquy-addons', plugins_url( 'css/addons' . $dev . '.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'soliloquy-addons' );
		
		/** Load the JS for the Addons area */
		wp_register_script( 'soliloquy-addons', plugins_url( 'js/addons' . $dev . '.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
		
		/** Prepare args to be passed to wp_localize_script */
		$args = array(
			'active'			=> Tgmsp_Strings::get_instance()->strings['addon_active'],
			'activate'			=> Tgmsp_Strings::get_instance()->strings['addon_activate'],
			'activating'		=> Tgmsp_Strings::get_instance()->strings['addon_activating'],
			'activate_nonce' 	=> wp_create_nonce( 'soliloquy_activate_addon' ),
			'connect_error'		=> Tgmsp_Strings::get_instance()->strings['addon_connect_error'],
			'deactivate'		=> Tgmsp_Strings::get_instance()->strings['addon_deactivate'],
			'deactivating'		=> Tgmsp_Strings::get_instance()->strings['addon_deactivating'],
			'deactivate_nonce' 	=> wp_create_nonce( 'soliloquy_deactivate_addon' ),
			'inactive'			=> Tgmsp_Strings::get_instance()->strings['addon_inactive'],
			'install'			=> Tgmsp_Strings::get_instance()->strings['addon_install'],
			'installing'		=> Tgmsp_Strings::get_instance()->strings['addon_installing'],
			'install_nonce' 	=> wp_create_nonce( 'soliloquy_install_addon' ),
			'not_installed'		=> Tgmsp_Strings::get_instance()->strings['addon_not_installed'],
			'pagehook'			=> $this->pagehook,
			'proceed'			=> Tgmsp_Strings::get_instance()->strings['addon_proceed'],
			'spinner'			=> plugins_url( 'css/images/loading.gif', dirname( __FILE__ ) ),
		);
		
		wp_localize_script( 'soliloquy-addons', 'soliloquy_addon', $args );
		wp_enqueue_script( 'soliloquy-addons' );

	}
	
	/**
	 * Activates an Addon via Ajax.
	 *
	 * @since 1.2.0
	 */
	public function activate_addon() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_activate_addon', 'nonce' );
		
		/** Activate the plugin */
		if ( isset( $_POST['plugin'] ) ) {
			$activate = activate_plugin( $_POST['plugin'] );
			
			if ( is_wp_error( $activate ) ) {
				echo json_encode( array( 'error' => $activate->get_error_message() ) );
				die;
			}
		}
		
		echo json_encode( true );
		die;

	}
	
	/**
	 * Deactivates an Addon via Ajax.
	 *
	 * @since 1.2.0
	 */
	public function deactivate_addon() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_deactivate_addon', 'nonce' );
		
		/** Deactivate the plugin */
		if ( isset( $_POST['plugin'] ) )
			$deactivate = deactivate_plugins( $_POST['plugin'] );
		
		echo json_encode( true );
		die;

	}
	
	/**
	 * Installs an Addon via Ajax.
	 *
	 * @since 1.2.0
	 *
	 * @global string $hook_suffix The current pagehook suffx
	 */
	public function install_addon() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_install_addon', 'nonce' );
		
		/** Install the plugin */
		if ( isset( $_POST['plugin'] ) ) {
			/** Here we go - we will use WP_Filesystem to install the plugin from Amazon S3 */
			$download_url 	= $_POST['plugin'];
			$pagehook		= $_POST['hook'];
			global $hook_suffix; // Have to declare this in order to avoid an undefined index notice, doesn't do anything
			
			/** Set the current screen to avoid undefined notices */
			set_current_screen();
			
			/** Prepare variables for request_filesystem_credentials */
			$method = '';
			$url 	= add_query_arg(
				array(
					'post_type' => 'soliloquy',
					'page'		=> $pagehook
				),
				admin_url( 'edit.php' )
			);
			
			/** Start output bufferring to catch the filesystem form if credentials are needed */
			ob_start();
			if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
				$form = ob_get_clean();
				echo json_encode( array( 'form' => $form ) );
				die;
			}	

			if ( ! WP_Filesystem( $creds ) ) {
				ob_start();
				request_filesystem_credentials( $url, $method, true, false, null ); // Setup WP_Filesystem
				$form = ob_get_clean();
				echo json_encode( array( 'form' => $form ) );
				die;
			}

			/** We do not need any extra credentials if we have gotten this far, so let's install the plugin */
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes
			require_once plugin_dir_path( __FILE__ ) . 'Skin.php'; // Need to customize the upgrader skin
			
			/** Create a new Plugin_Upgrader instance */
			$installer = new Plugin_Upgrader( $skin = new Tgmsp_Skin() );
			$installer->install( $download_url );
			
			/** Flush the cache and return the newly installed plugin basename */
			wp_cache_flush();
			if ( $installer->plugin_info() ) {
				$plugin_basename = $installer->plugin_info();
				echo json_encode( array( 'plugin' => $plugin_basename ) );
				die;
			}
		}
			
		echo json_encode( true );
		die;

	}
	
	/**
	 * Authenticates the license key to ensure that only valid keys with the developer status can
	 * see and access the Addons area.
	 *
	 * @since 1.2.0
	 *
	 * @param string $key The submitted license key
	 */
	private function authenticate( $key ) {
	
		/** Perform our request to the server to verify the authenticity of the license key */
		$validate = Tgmsp_License::perform_remote_request( 'authenticate-addons-area', array( 'key' => $key ) );
		
		/** If there is an error connecting, delete the transient and hide the Addons page until we can verify */
		if ( is_wp_error( $validate ) ) {
			delete_transient( 'soliloquy_authenticate_addons' );
			return false;
		}
		
		/** If the key_error property is set, the key could not be verified as an active developer license */
		if ( isset( $validate->key_error ) )
			return false;
		
		/** Our key has been authenticated */
		return true;
	
	}
	
	/**
	 * Helper function to retrieve the plugin basename from the plugin slug.
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug The plugin slug
	 * @return string The plugin basename if found, else the plugin slug
	 */
	private function get_plugin_basename_from_slug( $slug ) {
	
		$keys = array_keys( get_plugins() );
		
		foreach ( $keys as $key )
			if ( preg_match( '|^' . $slug . '|', $key ) )
				return $key;

		return $slug;
	
	}
	
	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.2.0
	 */
	public static function get_instance() {
	
		return self::$instance;
	
	}
	
}