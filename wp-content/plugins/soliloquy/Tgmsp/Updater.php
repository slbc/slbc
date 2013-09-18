<?php
/**
 * Updater class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Updater {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * URL of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_url = false;
		
	/**
	 * Remote URL for getting plugin upgrades.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $remote_url = false;
	
	/**
	 * Version number of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $version = false;
	
	/**
	 * Plugin name.
	 *
	 * @since 1.4.2
	 *
	 * @var string
	 */
	public $plugin_name = false;
		
	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = false;
		
	/**
	 * Plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_path = false;
		
	/**
	 * Time period between upgrade checks.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $time_upgrade_check = false;
		
	/**
	 * Holds the license key for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $license_key = false;
		
	/**
	 * Plugins to be used in upgrade checks.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugins = '';
	
	/**
	 * Flag for determining if the plugin has an update.
	 *
	 * @since 1.4.2
	 *
	 * @var bool
	 */
	public $has_update = false;
	
	/**
	 * Holds number of updates available.
	 *
	 * @since 1.3.4
	 *
	 * @var int
	 */
	private static $updates = 0;
	
	/**
	 * Holds copy of all plugin update instances.
	 *
	 * @since 1.4.2
	 *
	 * @var object
	 */
	private static $update_instances;
		
	/**
	 * Constructor. Parses default args with new args and sets up interactions
	 * within the admin area of WordPress.
	 *
	 * Hooks interactions into admin_init, adds hover links to plugin row and
	 * filters plugins_api to point to a custom repo for upgrading.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Empty array
	 */
	public function __construct( $args = array() ) {
		
		self::$instance = $this;
		
		/** Pass the update instance to our property */
		self::$update_instances[] = $this;
		
		/** Parse default args with new args and extract them as variables */
		extract( wp_parse_args( $args, array(
			'plugin_name'	=> false,
			'plugin_url' 	=> false,
			'remote_url' 	=> false,
			'version' 		=> false,
			'plugin_slug' 	=> false,
			'plugin_path' 	=> false,
			'time' 			=> 43200,
			'key' 			=> false
		) ) );
			
		/** Set class properties equal to parsed and extracted args */
		$this->plugin_name			= $plugin_name;
		$this->plugin_url 			= $plugin_url;
		$this->remote_url 			= $remote_url;
		$this->version 				= $version;
		$this->plugin_slug 			= $plugin_slug;
		$this->plugin_path 			= $plugin_path;
		$this->license_key 			= $key;
		$this->time_upgrade_check 	= $time;
		
		/** Grab and store the plugin options */
		$this->plugins = $this->get_plugin_options();
		
		/** Only run checks for administrators */
		if ( current_user_can( 'update_plugins' ) ) {
			/** Check for updates */
			$this->check_periodic_updates();
			if ( isset( $this->plugins[$this->plugin_slug]->new_version ) ) {
				if ( ! version_compare( $this->version, $this->plugins[$this->plugin_slug]->new_version, '>=' ) ) {
					$this->has_update = true;
					self::$updates++;
					/** Load interactions with WordPress */
					add_action( 'admin_menu', array( $this, 'admin_menu' ) );
					add_action( 'load-update-core.php', array( $this, 'force_update_check' ) );
					add_filter( 'plugins_api', array( $this, 'plugins_api' ), 5, 3 );
					add_filter( 'site_transient_update_plugins', array( $this, 'update_plugins_filter' ), 1000 );
				}
			}
		}
		
	}
	
	/**
	 * Inject the update notification icon into our menu item if any updates are
	 * available for Soliloquy or its Addons.
	 *
	 * @since 1.0.0
	 *
	 * @global array $menu Array of menu data for WordPress
	 */
	public function admin_menu() {
	
		global $menu;
		
		/** Generate the update icon information */
		$update_icon = self::get_updates() ? '<span title="' . Tgmsp_Strings::get_instance()->strings['update_available'] . '" class="update-plugins count-' . self::get_updates() . '"><span class="update-count">' . self::get_updates() . '</span></span>' : false;

		/** Loop through the menu and output our updates number */
		if ( $update_icon ) {
			foreach ( $menu as $i => $array ) {
				if ( preg_match( '|soliloquy$|', $array[2] ) && ! isset( $array['update'] ) ) {
					$menu[$i][0] 			= $menu[$i][0] . ' ' . $update_icon;
					$submenu[$i]['update'] 	= true;
				}
			}
		}
	
	}
		
	/**
	 * Performs a periodic upgrade check to see if the plugin needs to be
	 * upgraded or not.
	 *
	 * @since 1.0.0
	 */
	private function check_periodic_updates() {
		
		$last_update = isset( $this->plugins[$this->plugin_slug]->last_update ) ? $this->plugins[$this->plugin_slug]->last_update : false;
			
		/** If we haven't performed an update, perform one now */
		if ( ! $last_update ) {
			$last_update = $this->check_for_updates();
			$last_update = isset( $last_update->last_update ) ? $last_update->last_update : time();
		}
				
		/** If the time since the last update is greater than the time specified in the constructor, perform an update check */
		if ( ( time() - $last_update ) > $this->time_upgrade_check )
			$this->check_for_updates();
			
	}
		
	/**
	 * Checks to see if plugin should have an update check run or not.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $manual Flag for checking automatically or not
	 * @return boolean|string Return early if plugin is not in an array, else return plugin slug
	 */
	public function check_for_updates( $manual = false ) {
		
		/** If plugin is not in an array, return early */
		if ( ! is_array( $this->plugins ) )
			return false;
				
		/** If plugin options don't exist, create them */
		if ( ! isset( $this->plugins[$this->plugin_slug] ) ) {
			$plugin_options 				= new stdClass;
			$plugin_options->url 			= $this->plugin_url;
			$plugin_options->slug 			= $this->plugin_slug;
			$plugin_options->package 		= '';
			$plugin_options->new_version 	= $this->version;
			$plugin_options->last_update 	= time();
			$plugin_options->id 			= '0';
				
			$this->plugins[$this->plugin_slug] = $plugin_options;
			$this->save_plugin_options();
		}
			
		$current_plugin = $this->plugins[$this->plugin_slug];
			
		/** If the time since the last update is greater than the time specified in the constructor or manual is true, perform an update check */
		if ( ( time() - $current_plugin->last_update ) > $this->time_upgrade_check || $manual ) {
			/** Perform the remote request to check for updates */
			$version_info = $this->perform_remote_request( 'do-plugin-update-check', array( 'key' => $this->license_key, 'plugin' => $this->plugin_slug ) );
				
			/** Bail out if there are any errors */
			if ( is_wp_error( $version_info ) )
				return false;
				
			/** The query should return the plugin version and a download url */	
			if ( isset( $version_info->version ) && isset( $version_info->download_url ) ) {					
				$current_plugin->new_version 		= $version_info->version;
				$current_plugin->package 			= $version_info->download_url;
				$current_plugin->last_update		= time();
				$this->plugins[$this->plugin_slug] 	= $current_plugin;
				$this->save_plugin_options();
			}
		}
			
		return $this->plugins[$this->plugin_slug];
		
	}
	
	/**
	 * Force the updater to run an update check whenever a user visits the Updates page 
	 * in the WordPress dashboard.
	 *
	 * @since 1.0.5
	 */
	public function force_update_check() {
	
		/** Perform a manual update check for the plugin */
		$this->check_for_updates( true );
	
	}
		
	/**
	 * Return the plugin data to WordPress when it runs its own update checker.
	 *
	 * @since 1.0.0
	 *
	 * @param object $value The updater object for the plugin
	 * @return object $value Amended updater object with our plugin
	 */
	public function update_plugins_filter( $value ) {
		
		if ( isset( $this->plugins[$this->plugin_slug] ) && $this->plugin_path )
			$value->response[$this->plugin_path] = $this->plugins[$this->plugin_slug];
				
		return $value;
		
	}
		
	/**
	 * Filters the plugins_api function to get our own custom plugin information
	 * from our private repo.
	 *
	 * @since 1.0.0
	 *
	 * @param object $api The original plugins_api object
	 * @param string $action The action sent by plugins_api
	 * @param array $args Additional args to send to plugins_api
	 * @return object $api New object with plugin information on success, default response on failure
	 */
	public function plugins_api( $api, $action, $args ) {
	
		$plugin = ( 'plugin_information' == $action ) && isset( $args->slug ) && ( $this->plugin_slug == $args->slug );
			
		/** If our plugin doesn't match, return the default result */
		if ( ! $plugin )
			return $api;
		else {
			/** Our plugin matches, so query the remote URL and get information */
			$plugin_info = $this->perform_remote_request( 'get-plugin-info', array( 'key' => $this->license_key, 'plugin' => $this->plugin_slug ) );
				
			/** Overwrite the default api object with our own information */
			$api 							= new stdClass;
			$api->name 						= isset( $plugin_info->name ) ? $plugin_info->name : '';
			$api->slug 						= isset( $plugin_info->slug ) ? $plugin_info->slug : '';
			$api->version 					= isset( $plugin_info->version ) ? $plugin_info->version : '';
			$api->author 					= isset( $plugin_info->author ) ? $plugin_info->author : '';
			$api->author_profile 			= isset( $plugin_info->author_profile ) ? $plugin_info->author_profile : '';
			$api->requires 					= isset( $plugin_info->requires ) ? $plugin_info->requires : '';
			$api->tested 					= isset( $plugin_info->tested ) ? $plugin_info->tested : '';
			$api->last_updated 				= isset( $plugin_info->last_updated ) ? $plugin_info->last_updated : '';
			$api->homepage 					= isset( $plugin_info->homepage ) ? $plugin_info->homepage : '';
			$api->sections['description'] 	= isset( $plugin_info->description ) ? $plugin_info->description : '';
			$api->sections['installation'] 	= isset( $plugin_info->installation ) ? $plugin_info->installation : '';
			$api->sections['changelog'] 	= isset( $plugin_info->changelog ) ? $plugin_info->changelog : '';
			$api->sections['FAQ'] 			= isset( $plugin_info->FAQ ) ? $plugin_info->FAQ : '';
			$api->download_link 			= isset( $plugin_info->download_link ) ? $plugin_info->download_link : '';
				
			/** Return the new object with all the necessary information */
			return $api;
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
	public function perform_remote_request( $action, $body = array(), $headers = array(), $return_format = 'json' ) {
		
		/** Build body */
		$body = wp_parse_args( $body, array( 
			'action' 		=> $action,
			'wp-version' 	=> get_bloginfo( 'version' ),
			'referer' 		=> site_url()
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
		$response 		= wp_remote_post( esc_url_raw( $this->remote_url ), $post );
		$response_code 	= wp_remote_retrieve_response_code( $response );
		$response_body 	= wp_remote_retrieve_body( $response );
			
		/** Bail out early if there are any errors */
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
	 * Returns options for the plugin set for automatic upgrades.
	 *
	 * @since 1.0.0
	 *
	 * @return array $options Plugin options
	 */
	private function get_plugin_options() {
			
		/** Multisite check */
		if ( is_multisite() )
			$options = get_site_option( 'tgmsp_plugins_' . $this->plugin_slug, false, false );
		else
			$options = get_option( 'tgmsp_plugins_' . $this->plugin_slug );
				
		if ( ! $options )
			$options = array();
				
		return $options;
		
	}
		
	/**
	 * Update and save plugin options.
	 *
	 * @since 1.0.0
	 */
	private function save_plugin_options() {
	
		/** Multisite check */
		if ( is_multisite() )
			update_site_option( 'tgmsp_plugins_' . $this->plugin_slug, $this->plugins );
		else
			update_option( 'tgmsp_plugins_' . $this->plugin_slug, $this->plugins );
		
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
	 * Getter method for retrieving the object instance count.
	 *
	 * @since 1.3.4
	 */
	public static function get_updates() {
	
		return self::$updates;
	
	}
	
	/**
	 * Getter method for retrieving all plugin update instances.
	 *
	 * @since 1.4.2
	 */
	public static function get_update_instances() {
	
		return self::$update_instances;
	
	}
	
}