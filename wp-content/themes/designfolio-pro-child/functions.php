<?php
/* Designfolio Pro Child Theme.
 *
 * v1.0.
 */

add_action( 'after_setup_theme', 'override_parent_theme_main_features', 11 );
add_action( 'after_setup_theme', 'override_parent_theme_lower_level_features', 13 );

/* Remove features added in the parent theme functions.php file. */
function override_parent_theme_main_features() {

	/* e.g. Remove color scheme support. */
	//remove_theme_support( 'color-schemes' );

	/* e.g. Remove Google font support. */
	//remove_theme_support( 'google-fonts' );
}

/* Alter lower level features in the framework itself. */
function override_parent_theme_lower_level_features() {

	/* Add code here.. */
}

/* Add other hooks/functions below.. */

?>
