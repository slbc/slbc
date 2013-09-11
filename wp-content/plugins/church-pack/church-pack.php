<?php
/*
Plugin Name: Church Pack
Plugin URI: http://wpforchurch.com
Description: Add groups, people (staff or all members), events, and more to your WordPress site. Visit <a href="http://wpforchurch.com" target="_blank">Wordpress for Church</a> for tutorials and support.
Version: 1.2.3
Author: Jack Lamb
Author URI: http://wpforchurch.com/
License: GPL
*/

// Plugin Folder Path
if ( ! defined( 'CP_PLUGIN_DIR' ) )
	define( 'CP_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );

// Plugin Folder URL
if ( ! defined( 'CP_PLUGIN_URL' ) )
	define( 'CP_PLUGIN_URL', plugin_dir_url( CP_PLUGIN_DIR ) . basename( dirname( __FILE__ ) ) . '/' );

// Plugin Root File
if ( ! defined( 'CP_PLUGIN_FILE' ) )
	define( 'CP_PLUGIN_FILE', __FILE__ );

// Translations
function wpfc_churchpack_translations() {
	load_plugin_textdomain( 'churchpack', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wpfc_churchpack_translations' );

// Include Options
require_once plugin_dir_path( __FILE__ ) . '/options.php';

// Call options array
$coreoptions = get_option('cpo_core_options');
$options = get_option('cpo_options');

// Include Groups
$groups = isset($coreoptions['groups']);
if ( $groups == '1' ) { 
require_once plugin_dir_path( __FILE__ ) . '/groups.php';
}

// Include People
$people = isset($coreoptions['people']);
if ( $people == '1' ) { 
require_once plugin_dir_path( __FILE__ ) . '/people.php';
}

// Include Photo Albums
$photos = isset($coreoptions['photos']);
if ( $photos == '1' ) { 
require_once plugin_dir_path( __FILE__ ) . '/albums.php';
}

// Include Widget Content
$widgetcontent = isset($coreoptions['widgetcontent']);
if ( $widgetcontent == '1' ) { 
require_once plugin_dir_path( __FILE__ ) . '/widget-content.php';
}

// Include Widgets
require_once plugin_dir_path( __FILE__ ) . '/widgets.php';

// Include Meta Box Class
require_once plugin_dir_path( __FILE__ ) . '/meta-box/init.php';

// Implement Shortcodes
$shortcodes = isset($coreoptions['shortcodes']);
if ( !$shortcodes == '1' ) { 
require plugin_dir_path( __FILE__ ) . 'shortcodes/shortcode-functions.php'; // Main shortcode functions
require plugin_dir_path( __FILE__ ) . 'shortcodes/mce/churchpack_shortcodes_tinymce.php'; // Add mce buttons to post editor	
}

// Add image sizes
function wpfc_churchpack_images() {
	add_image_size( 'churchpack_tiny', 50, 50, true );
	add_image_size( 'churchpack_thumb', 210, 125, true );
}
add_action("admin_init", "wpfc_churchpack_images");

// Add CSS to header
function wpfc_churchpack_styles() {
		wp_register_style('church-pack', plugins_url('/church-pack.css', __FILE__) ); 
		wp_enqueue_style( 'church-pack' );
}
add_action( 'wp_head', 'wpfc_churchpack_styles' );

// Load JS for Events Shortcode
function wpfc_churchpack_scripts() {
	wp_enqueue_script ('jquery');
	global $wp_query;
	global $post;
	if($post) {
		if ( false !== strpos($post->post_content, '[events') ) {	
			wp_register_script('church-events', plugins_url('/js/jquery.events.js', __FILE__) ); 
			wp_enqueue_script( 'church-events' );
			$event_ajax = admin_url( 'admin-ajax.php' );
			?> 
			<script type="text/javascript">
			/* <![CDATA[ */
			var ajax_url = '<?php echo $event_ajax;?>';
			/* ]]> */
			</script>
			<?php
		}
	}
}

// Google maps shortcode - legacy code, this just produces an image. The new shortcode is built into the editor and is very feature rich!
function cp_googlemaps ( $atts ) {

    extract( shortcode_atts( array(
         'width' => '638',
         'height' => '210',
         'color' => 'green',
         'zoom' => '15',
         'align' => 'center',
		 'address' => ''
     ), $atts) );

    ob_start();
    
    $address_url = preg_replace( '![^a-z0-9]+!i', '+', $address );
    
    // Display ?>

    <div id="cp-map"><a href="<?php echo 'http://maps.google.com/maps?q=' . $address_url; ?>" target="_blank"><img class="align<?php echo $align; ?> cp-googlemaps" src="http://maps.google.com/maps/api/staticmap?center=<?php echo $address_url; ?>&zoom=<?php echo $zoom; ?>&size=<?php echo $width; ?>x<?php echo $height; ?>&markers=color:<?php echo $color; ?>|<?php echo $address_url; ?>&sensor=false"></a></div><div class="clear"></div>
    
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;

    }

add_shortcode('cp-map', 'cp_googlemaps');
?>