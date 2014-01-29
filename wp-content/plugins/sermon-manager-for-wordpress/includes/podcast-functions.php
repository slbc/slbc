<?php
/**
 * Podcast Settings
 */

// Create custom RSS feed for sermon podcasting
function wpfc_sermon_podcast_feed() {
	load_template( WPFC_SERMONS . 'includes/podcast-feed.php');
}
add_action('do_feed_podcast', 'wpfc_sermon_podcast_feed', 10, 1);


// Custom rewrite for podcast feed
function wpfc_sermon_podcast_feed_rewrite($wp_rewrite) {
	$feed_rules = array(
		'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index(1),
		'(.+).xml' => 'index.php?feed='. $wp_rewrite->preg_index(1)
	);
	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'wpfc_sermon_podcast_feed_rewrite');


// Get the filesize of a remote file, used for Podcast data
function wpfc_get_filesize( $url, $timeout = 10 ) {
	$headers = wp_get_http_headers( $url);
    $duration = isset( $headers['content-length'] ) ? (int) $headers['content-length'] : 0;
	
	if( $duration ) {
			sscanf( $duration , "%d:%d:%d" , $hours , $minutes , $seconds );
			
			$length = isset( $seconds ) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

			if( ! $length ) {
					$length = (int) $duration;
			}

			return $length;
	}

	return 0;	
}

//Returns duration of .mp3 file
function wpfc_mp3_duration($mp3_url) {
	if ( ! class_exists( 'getID3' ) ) {
		require_once WPFC_SERMONS . '/includes/getid3/getid3.php'; 
	}
	$filename = tempnam('/tmp','getid3');
	if (file_put_contents($filename, file_get_contents($mp3_url, false, null, 0, 300000))) {
		  $getID3 = new getID3;
		  $ThisFileInfo = $getID3->analyze($filename);
		  unlink($filename);
	}

	$bitratez=$ThisFileInfo[audio][bitrate]; // get the bitrate from the audio file

	$headers = get_headers($mp3_url, 1); // Get the headers from the remote file
				if ((!array_key_exists("Content-Length", $headers))) { return false; } // Get the content length from the remote file
				$content_length = (int)$headers["Content-Length"]; 
				$filesize= round($content_length/1000); // Make the filesize into kilobytes & round it

	$contentLengthKBITS=$filesize*8; // make kbytes into kbits
	$bitrate=$bitratez/1000; //convert bits/sec to kbit/sec
	$seconds=$contentLengthKBITS/$bitrate; // Calculate seconds in song

	$playtime_mins = floor($seconds/60); // get the minutes of the playtime string
	$playtime_secs = $seconds % 60; // get the seconds for the playtime string
	if(strlen($playtime_secs)=='1'){$zero='0';} // if the string is a multiple of 10, we need to add a 0 for visual reasons
	$playtime_secs = $zero.$playtime_secs; // add the zero if necessary
	$playtime_string=$playtime_mins.':'.$playtime_secs; // create the playtime string

		return $playtime_string;
}

?>