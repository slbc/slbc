<?php

/**
 * Add support for customizing the background via the WP admin
 */
function churchthemes_custom_background_setup() {
	if(	   !defined( 'CHURCHTHEMES_DEFAULT_BACKGROUND_IMAGE' )
		|| !defined( 'CHURCHTHEMES_DEFAULT_BACKGROUND_COLOR' )
	){
		return;
	}

	$defaults = array(
		'default-color'          => CHURCHTHEMES_DEFAULT_BACKGROUND_COLOR,
		'default-image'          => CHURCHTHEMES_DEFAULT_BACKGROUND_IMAGE,
		'admin-preview-callback' => null,
	);
	add_theme_support( 'custom-background', $defaults );
}
add_action( 'after_setup_theme', 'churchthemes_custom_background_setup' );


function churchthemes_custom_background_admin_defaults() {
	if ( !get_background_color() ) {
		set_theme_mod( 'background_color', CHURCHTHEMES_DEFAULT_BACKGROUND_COLOR );
	}
}
add_action( 'init', 'churchthemes_custom_background_admin_defaults' );
