<?php
/**
 * Checks to see if new post type names (with prefixes) are being used. If not, then the old
 * post type names are converted as long as there aren't conflicting post type names that are
 * being registered by other plugins.
 *
 * @action after_setup_theme
 */
function churchthemes_prefix_post_types(){

	global $wpdb;

	$prefix = 'ct_'; // Define the prefix you would like to use
	$post_types = array( 'slide', 'sermon', 'location', 'person' ); // Create an array of existing post type names that will receieve a prefix

	foreach ( $post_types as $post_type ) {

		// Check first to see if another plugin is registering a conflicting post type name
		if ( post_type_exists( $post_type ) ) {
			continue;
		}

		// Check to see if database entries exist for the prefixed post type
		$new_entries_exist = ( $wpdb->query( $wpdb->prepare( "SELECT * FROM " . $wpdb->posts . " WHERE post_type = %s", $prefix . $post_type ) ) ) ? true : false;

		if ( $new_entries_exist ) {
			continue;
		}

		// If we make it this far, check to see if database entries exist for the old post type
		$old_entries_exist = ( $wpdb->query( $wpdb->prepare( "SELECT * FROM " . $wpdb->posts . " WHERE post_type = %s", $post_type ) ) ) ? true : false;

		if ( $old_entries_exist ) {

			$wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . $wpdb->posts . ' SET post_type = %s WHERE post_type = %s',
					$prefix . $post_type,
					$post_type
				)
			);

			$wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . $wpdb->posts . ' SET guid = replace( guid, "post_type = %s", "post_type = %s" )',
					$post_type,
					$prefix . $post_type
				)
			);

		}
	}
}
add_action( 'after_setup_theme', 'churchthemes_prefix_post_types' );
