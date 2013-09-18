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
	// Create a curl connection
	$getsize = curl_init();

	// Set the url we're requesting
	curl_setopt($getsize, CURLOPT_URL, $url);

	// Set a valid user agent
	curl_setopt($getsize, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");

	// Don't output any response directly to the browser
	curl_setopt($getsize, CURLOPT_RETURNTRANSFER, true);

	// Don't return the header (we'll use curl_getinfo();
	curl_setopt($getsize, CURLOPT_HEADER, false);

	// Don't download the body content
	curl_setopt($getsize, CURLOPT_NOBODY, true);

	// Follow location headers
	curl_setopt($getsize, CURLOPT_FOLLOWLOCATION, true);

	// Set the timeout (in seconds)
	curl_setopt($getsize, CURLOPT_TIMEOUT, $timeout);

	// Run the curl functions to process the request
	$getsize_store = curl_exec($getsize);
	$getsize_error = curl_error($getsize);
	$getsize_info = curl_getinfo($getsize);

	// Close the connection
	curl_close($getsize); // Print the file size in bytes

	return $getsize_info['download_content_length'];
}

//Returns duration of .mp3 file
function wpfc_mp3_duration($mp3_url) {
	require_once WPFC_SERMONS . '/includes/getid3/getid3.php'; 
	$filename = tempnam('/tmp','getid3');
	if (file_put_contents($filename, file_get_contents($mp3_url, false, null, 0, 300000))) {
		  $getID3 = new getID3;
		  $ThisFileInfo = $getID3->analyze($filename);
		  unlink($filename);
	}

	$bitratez=$ThisFileInfo[audio][bitrate]; // get the bitrate from the audio file

	$headers = get_headers($mp3_url, 1); // Get the headers from the remote file
				if ((!array_key_exists("Content-Length", $headers))) { return false; } // Get the content length from the remote file
				$filesize= round($headers["Content-Length"]/1000); // Make the failesize into kilobytes & round it

	$contentLengthKBITS=$filesize*8; // make kbytes into kbits
	$bitrate=$bitratez/1000; //convert bits/sec to kbit/sec
	$seconds=$contentLengthKBITS/$bitrate; // Calculate seconds in song

	$playtime_mins = floor($seconds/60); // get the minutes of the playtime string
	$playtime_secs = $seconds % 60; // get the seconds for the playtime string
	if(strlen($playtime_secs)=='1'){$zero='0';} // if the string is a multiple of 10, we need to add a 0 for visual reasons
	$playtime_secs = $zero.$playtime_secs; // add the zero if nessecary
	$playtime_string=$playtime_mins.':'.$playtime_secs; // create the playtime string

		return $playtime_string;
}

?>