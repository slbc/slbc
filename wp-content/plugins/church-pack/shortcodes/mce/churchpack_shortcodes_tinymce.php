<?php
/**
 * This file has all the main shortcode functions
 */
class CHURCHPACK_TinyMCE_Buttons {

	function __construct() {
    	add_action( 'init', array(&$this,'init') );
    }
    function init() {
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;		
		if ( get_user_option('rich_editing') == 'true' ) {  
			add_filter( 'mce_external_plugins', array(&$this, 'add_plugin') );  
			add_filter( 'mce_buttons', array(&$this,'register_button') ); 
			wp_localize_script( 'jquery', 'churchpackShortcodesVars', array('template_url' => plugin_dir_url( __FILE__ ) ) );
		}  
    }  
	function add_plugin($plugin_array) {  
	   $plugin_array['churchpack_shortcodes'] = plugin_dir_url( __FILE__ ) .'js/churchpack_shortcodes_tinymce.js';
	   return $plugin_array; 
	}
	function register_button($buttons) {  
	   array_push($buttons, "churchpack_shortcodes_button");
	   return $buttons; 
	} 	

}
$churchpackshortcode = new CHURCHPACK_TinyMCE_Buttons;