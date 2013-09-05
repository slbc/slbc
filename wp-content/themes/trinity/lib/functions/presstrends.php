<?php
/**
 * PressTrends
 *
 * This script gathers and reports metrics on how and where the theme is being used.
 *
 * @package ChurchThemes
 */
function presstrends_theme() {

	// PressTrends Account API Key
	$api_key = CHURCHTHEMES_PRESSTRENDS_API_KEY;
	$auth = CHURCHTHEMES_PRESSTRENDS_THEME_AUTH;

	if ( empty( $api_key ) || empty( $auth ) || ( $data = get_transient( 'presstrends_theme_cache_data' ) ) !== false ) {
		return;
	}

	// Start of Metrics
	global $wpdb;

	$api_base = 'http://api.presstrends.io/index.php/api/sites/add/auth/';
	$url = $api_base . $auth . '/api/' . $api_key . '/';

	$count_posts = wp_count_posts();
	$count_pages = wp_count_posts( 'page' );
	$comments_count = wp_count_comments();
	$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );

	// wp_get_theme was introduced in 3.4, for compatibility with older versions.
	if ( function_exists( 'wp_get_theme' ) ) {
		$theme_data = wp_get_theme();
		$theme_name = urlencode( $theme_data->Name );
		$theme_version = $theme_data->Version;
	}
	else {
		$theme_data = get_theme_data( untrailingslashit( get_stylesheet_directory() ) . '/style.css' );
		$theme_name = $theme_data['Name'];
		$theme_version = $theme_data['Version'];
	}

	$plugin_name = '&';
	foreach ( get_plugins() as $plugin_info ) {
		$plugin_name .= $plugin_info['Name'] . '&';
	}

	$data = array(
		'url'             => stripslashes( str_replace( array( 'http://', '/', ':' ), '', site_url() ) ),
		'posts'           => $count_posts->publish,
		'pages'           => $count_pages->publish,
		'comments'        => $comments_count->total_comments,
		'approved'        => $comments_count->approved,
		'spam'            => $comments_count->spam,
		'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
		'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
		'theme_version'   => $theme_version,
		'theme_name'      => $theme_name,
		'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
		'plugins'         => count( get_option( 'active_plugins' ) ),
		'plugin'          => urlencode( $plugin_name ),
		'wpversion'       => get_bloginfo( 'version' ),
		'api_version'	  => '2.4',
	);

	foreach ( $data as $k => $v ) {
		$url .= $k . '/' . $v . '/';
	}

	wp_remote_get( $url );

	set_transient( 'presstrends_theme_cache_data', $data, 60 * 60 * 24 ); // 24 hr TTL
}
add_action( 'admin_init', 'presstrends_theme' );
