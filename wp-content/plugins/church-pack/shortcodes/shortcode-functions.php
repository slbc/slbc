<?php
/**
 * This file has all the main shortcode functions
 */

/*
 * Load Scripts
 */
function churchpack_shortcodes_scripts() {
	wp_enqueue_script('jquery');
	wp_register_script( 'churchpack_tabs', plugin_dir_url( __FILE__ ) . 'js/churchpack_tabs.js', array ( 'jquery', 'jquery-ui-tabs'), '1.0', true );
	wp_register_script( 'churchpack_toggle', plugin_dir_url( __FILE__ ) . 'js/churchpack_toggle.js', 'jquery', '1.0', true );
	wp_register_script( 'churchpack_accordion', plugin_dir_url( __FILE__ ) . 'js/churchpack_accordion.js', array ( 'jquery', 'jquery-ui-accordion'), '1.0', true );
	wp_enqueue_style('churchpack_shortcode_styles', plugin_dir_url( __FILE__ ) . 'css/shortcodes.css');
	wp_register_script('churchpack_googlemap',  plugin_dir_url( __FILE__ ) . 'js/churchpack_googlemap.js', array('jquery'), '', true);
	wp_register_script('churchpack_googlemap_api', 'https://maps.googleapis.com/maps/api/js?sensor=false', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'churchpack_shortcodes_scripts');

/*
 * Clear Floats
 */
if( !function_exists('churchpack_clear_floats_shortcode') ) {
	function churchpack_clear_floats_shortcode() {
	   return '<div class="churchpack-clear-floats"></div>';
	}
	add_shortcode( 'churchpack_clear_floats', 'churchpack_clear_floats_shortcode' );
}

/*
 * Spacing
 */
if( !function_exists('churchpack_spacing_shortcode') ) {
	function churchpack_spacing_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'size' => '20px',
		  ),
		  $atts ) );
	 return '<hr class="churchpack-spacing" style="height: '. $size .'"></hr>';
	}
	add_shortcode( 'churchpack_spacing', 'churchpack_spacing_shortcode' );
}




/*
 * Fix Shortcodes
 * @since v1.0
 */
if( !function_exists('churchpack_fix_shortcodes') ) {
	function churchpack_fix_shortcodes($content){   
		$array = array (
			'<p>[' => '[', 
			']</p>' => ']', 
			']<br />' => ']'
		);
		$content = strtr($content, $array);
		return $content;
	}
	add_filter('the_content', 'churchpack_fix_shortcodes');
}


/**
* Social Icons
* @since 1.0
*/
if( !function_exists('churchpack_social_shortcode') ) {
	function churchpack_social_shortcode( $atts ){   
		extract( shortcode_atts( array(
			'icon' => 'twitter',
			'url' => 'http://www.twitter.com/wpforchurch',
			'title' => 'Follow Us',
			'target' => 'self',
			'rel' => '',
			'border_radius' => ''
		), $atts ) );
		
		return '<a href="' . $url . '" class="churchpack-social-icon" target="_'.$target.'" title="'. $title .'" rel="'. $rel .'"><img src="'. plugin_dir_url( __FILE__ ) .'images/social/'. $icon .'.png" alt="'. $icon .'" /></a>';
	}
	add_shortcode('churchpack_social', 'churchpack_social_shortcode');
}



//churchpack_highlights
if ( !function_exists( 'churchpack_highlight_shortcode' ) ) {
	function churchpack_highlight_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'yellow',
		  ),
		  $atts ) );
		  return '<span class="churchpack-highlight-'. $color .'">' . do_shortcode( $content ) . '</span>';
	
	}
	add_shortcode('churchpack_highlight', 'churchpack_highlight_shortcode');
}


/*
 * Buttons
 * @since v1.0
 */
if( !function_exists('churchpack_button_shortcode') ) {
	function churchpack_button_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'blue',
			'url' => 'http://www.wpforchurch.com',
			'title' => 'Visit Site',
			'size' => '', //options: large, giant
			'target' => 'self',
			'rel' => '',
			'border_radius' => ''
		), $atts ) );
		
		$border_radius_style = ( $border_radius ) ? 'style="border-radius:'. $border_radius .'"' : NULL;
		
		return '<a href="' . $url . '" class="churchpack-button ' . $color . ' ' . $size . '" target="_'.$target.'" title="'. $title .'" '. $border_radius_style .' rel="'.$rel.'">' . $content . '</a>';
	}
	add_shortcode('churchpack_button', 'churchpack_button_shortcode');
}



/*
 * Boxes
 */
if( !function_exists('churchpack_box_shortcode') ) { 
	function churchpack_box_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'gray',
			'float' => 'center',
			'text_align' => 'left',
			'width' => '100%'
		  ), $atts ) );
		  $alert_content = '';
		  $alert_content .= '<div class="churchpack-box ' . $color . ' '.$float.'" style="text-align:'. $text_align .'; width:'. $width .';">';
		  $alert_content .= ' '. do_shortcode($content) .'</div>';
		  return $alert_content;
	}
	add_shortcode('churchpack_box', 'churchpack_box_shortcode');
}

/*
 * Superquote
 */
if ( !function_exists( 'churchpack_superquote_shortcode' ) ) {
	function churchpack_superquote_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'yellow',
		  ),
		  $atts ) );
		return '<div class="churchpack-superquote">' . do_shortcode( $content ) . '</div>';
	
	}
	add_shortcode('churchpack_superquote', 'churchpack_superquote_shortcode');
}

/*
 * Columns
 */
if( !function_exists('churchpack_column_shortcode') ) {
	function churchpack_column_shortcode( $atts, $content = null ){
		extract( shortcode_atts( array(
			'size' => 'one-third',
			'position' =>'first'
		  ), $atts ) );
		  return '<div class="churchpack-' . $size . ' churchpack-column-'.$position.'">' . do_shortcode($content) . '</div>';
	}
	add_shortcode('churchpack_column', 'churchpack_column_shortcode');
}

/*
 * Toggle
 */
if( !function_exists('churchpack_toggle_shortcode') ) {
	function churchpack_toggle_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array( 'title' => 'Toggle Title' ), $atts ) );
		 
		// Enque scripts
		wp_enqueue_script('churchpack_toggle');
		
		// Display the Toggle
		return '<div class="churchpack-toggle"><h3 class="churchpack-toggle-trigger">'. $title .'</h3><div class="churchpack-toggle-container">' . do_shortcode($content) . '</div></div>';
	}
	add_shortcode('churchpack_toggle', 'churchpack_toggle_shortcode');
}


/*
 * Accordion
 */

// Main
if( !function_exists('churchpack_accordion_main_shortcode') ) {
	function churchpack_accordion_main_shortcode( $atts, $content = null  ) {
		
		// Enque scripts
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('churchpack_accordion');
		
		// Display the accordion	
		return '<div class="churchpack-accordion">' . do_shortcode($content) . '</div>';
	}
	add_shortcode( 'churchpack_accordion', 'churchpack_accordion_main_shortcode' );
}


// Section
if( !function_exists('churchpack_accordion_section_shortcode') ) {
	function churchpack_accordion_section_shortcode( $atts, $content = null  ) {
		extract( shortcode_atts( array(
		  'title' => 'Title',
		), $atts ) );
		  
	   return '<h3 class="churchpack-accordion-trigger"><a href="#">'. $title .'</a></h3><div>' . do_shortcode($content) . '</div>';
	}
	
	add_shortcode( 'churchpack_accordion_section', 'churchpack_accordion_section_shortcode' );
}


/*
 * Tabs
 */
if (!function_exists('churchpack_tabgroup_shortcode')) {
	function churchpack_tabgroup_shortcode( $atts, $content = null ) {
		
		//Enque scripts
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('churchpack_tabs');
		
		// Display Tabs
		$defaults = array();
		extract( shortcode_atts( $defaults, $atts ) );
		preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );
		$tab_titles = array();
		if( isset($matches[1]) ){ $tab_titles = $matches[1]; }
		$output = '';
		if( count($tab_titles) ){
		    $output .= '<div id="churchpack-tab-'. rand(1, 100) .'" class="churchpack-tabs">';
			$output .= '<ul class="ui-tabs-nav churchpack-clearfix">';
			foreach( $tab_titles as $tab ){
				$output .= '<li><a href="#churchpack-tab-'. sanitize_title( $tab[0] ) .'">' . $tab[0] . '</a></li>';
			}
		    $output .= '</ul>';
		    $output .= do_shortcode( $content );
		    $output .= '</div>';
		} else {
			$output .= do_shortcode( $content );
		}
		return $output;
	}
	add_shortcode( 'churchpack_tabgroup', 'churchpack_tabgroup_shortcode' );
}
if (!function_exists('churchpack_tab_shortcode')) {
	function churchpack_tab_shortcode( $atts, $content = null ) {
		$defaults = array( 'title' => 'Tab' );
		extract( shortcode_atts( $defaults, $atts ) );
		return '<div id="churchpack-tab-'. sanitize_title( $title ) .'" class="tab-content">'. do_shortcode( $content ) .'</div>';
	}
	add_shortcode( 'churchpack_tab', 'churchpack_tab_shortcode' );
}

/*
 * Google Maps
 */
if (! function_exists( 'churchpack_shortcode_googlemaps' ) ) :
	function churchpack_shortcode_googlemaps($atts, $content = null) {
		
		extract(shortcode_atts(array(
				"title" => '',
				"location" => '',
				"width" => '', //leave width blank for responsive designs
				"height" => '300',
				"zoom" => 8,
				"align" => '',
		), $atts));
		
		// load scripts
		wp_enqueue_script('churchpack_googlemap');
		wp_enqueue_script('churchpack_googlemap_api');
		
		
		$output = '<div id="map_canvas_'.rand(1, 100).'" class="churchpack-googlemap" style="height:'.$height.'px;width:100%">';
			$output .= (!empty($title)) ? '<input class="title" type="hidden" value="'.$title.'" />' : '';
			$output .= '<input class="location" type="hidden" value="'.$location.'" />';
			$output .= '<input class="zoom" type="hidden" value="'.$zoom.'" />';
			$output .= '<div class="map_canvas"></div>';
		$output .= '</div>';
		
		return $output;
	   
	}
	add_shortcode("churchpack_googlemap", "churchpack_shortcode_googlemaps");
endif;


/*
 * Divider
 */
if( !function_exists('churchpack_divider_shortcode') ) {
	function churchpack_divider_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'style' => 'solid',
			'margin_top' => '20px',
			'margin_bottom' => '20px',
		  ),
		  $atts ) );
		$style_attr = '';
		if ( $margin_top && $margin_bottom ) {  
			$style_attr = 'style="margin-top: '. $margin_top .';margin-bottom: '. $margin_bottom .';"';
		} elseif( $margin_bottom ) {
			$style_attr = 'style="margin-bottom: '. $margin_bottom .';"';
		} elseif ( $margin_top ) {
			$style_attr = 'style="margin-top: '. $margin_top .';"';
		} else {
			$style_attr = NULL;
		}
	 return '<hr class="churchpack-divider '. $style .'" '.$style_attr.' />';
	}
	add_shortcode( 'churchpack_divider', 'churchpack_divider_shortcode' );
}
