<?php
header( 'Content-Type: text/xml; charset="UTF-8"', true );
echo '<?xml version="1.0" encoding="UTF-8"?>';
if ( ( $output = get_transient( 'churchthemes_podcast_feed_cache' ) ) !== false ) {
	print( $output );
	exit;
}
$settings = get_option( 'ct_podcast_settings' );
ob_start(); ?>
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
	<channel>
		<title><?php echo esc_html( $settings['title'] ) ?></title>
		<link><?php echo esc_url( $settings['website_link'] ) ?></link>
		<atom:link href="<?php echo ct_get_current_url() ?>" rel="self" type="application/rss+xml" />
		<language><?php echo esc_html( $settings['language'] ) ?></language>
		<copyright><?php echo htmlentities( $settings['copyright'] ) ?></copyright>
		<itunes:subtitle><?php echo esc_html( $settings['itunes_subtitle'] ) ?></itunes:subtitle>
		<itunes:author><?php echo esc_html( $settings['itunes_author'] ) ?></itunes:author>
		<itunes:summary><?php echo esc_html( $settings['itunes_summary'] ) ?></itunes:summary>
		<description><?php echo esc_html( $settings['description'] ) ?></description>
		<itunes:owner>
			<itunes:name><?php echo esc_html( $settings['itunes_owner_name'] ) ?></itunes:name>
			<itunes:email><?php echo esc_html( $settings['itunes_owner_email'] ) ?></itunes:email>
		</itunes:owner>
		<itunes:explicit><?php echo esc_html( $settings['itunes_explicit_content'] ) ?></itunes:explicit>
		<itunes:image href="<?php echo esc_url( $settings['itunes_cover_image'] ) ?>" />
		<itunes:category text="<?php echo esc_attr( $settings['itunes_top_category'] ) ?>">
			<itunes:category text="<?php echo esc_attr( $settings['itunes_sub_category'] ) ?>"/>
		</itunes:category>
<?php if ( have_posts() ) : ?>
<?php while ( have_posts() ) : the_post() ?>
<?php
$series = get_the_term_list( get_the_ID(), 'sermon_series', '', ', ', '' );
$subtitle = ( $series ) ? $series : churchthemes_get_the_excerpt( array('more' => '[...]', 'length' => 150, 'unit' => 'char') );
$post_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
$post_image_src = ( $post_image ) ? $post_image['0'] : null;
$audio_file = get_post_meta( get_the_ID(), '_ct_sm_audio_file', true );
?>
		<item>
			<title><?php the_title_rss() ?></title>
			<link><?php the_permalink_rss() ?></link>
			<itunes:author><?php echo esc_html( strip_tags( get_the_term_list( get_the_ID(), 'sermon_speaker', '', ' &amp; ', '' ) ) ) ?></itunes:author>
			<itunes:subtitle><?php echo esc_html( strip_tags( $subtitle ) ) ?></itunes:subtitle>
			<itunes:summary><?php the_excerpt_rss() ?> </itunes:summary>
			<itunes:image href="<?php echo esc_url( $post_image_src ) ?>" />
			<enclosure url="<?php echo esc_url( $audio_file ) ?>" length="<?php echo ct_get_filesize( esc_url_raw( $audio_file ) ) ?>" type="audio/mpeg" />
			<guid><?php echo esc_url( $audio_file ) ?></guid>
			<pubDate><?php echo get_post_time( 'r', false ) ?></pubDate>
			<itunes:duration><?php echo esc_html( get_post_meta( get_the_ID(), '_ct_sm_audio_length', true ) ) ?></itunes:duration>
			<itunes:keywords><?php echo esc_html( strip_tags( get_the_term_list( get_the_ID(), 'sermon_topic', '', ', ', '' ) ) ) ?></itunes:keywords>
		</item>
<?php endwhile; ?>
<?php endif; ?>
	</channel>
</rss><?php $output = ob_get_clean() ?>

<?php set_transient( 'churchthemes_podcast_feed_cache', $output, 60*60 ) // Hold cache for 1 hour ?>

<?php print( $output ) ?>