<?php
/**
 * The template for displaying individual sermons on archive pages
 */
?>


<article id="sermon-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action( 'sm_archive_loop_before' ); ?>
	<div id="archive_sermon_image">
		<?php render_sermon_image('thumbnail'); ?>
	</div>
	<h2 class="sermon-title">
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
				<span class="sermon-date-meta"><?php wpfc_sermon_date('m/d/y', '', ''); echo the_terms( $post->ID, 'wpfc_service_type',  ' (', ' ', ')'); ?><span>
	</h2>

	<div class="sermon-summary">
		<?php do_action( 'sm_sermon_media' ); ?>
		<?php
		morgan_sermon_buttons();
		wpfc_sermon_meta('bible_passage', '<span class="bible_passage">Bible Text: ', '</span> | ');
		echo the_terms( $post->ID, 'wpfc_preacher',  '<span class="preacher_name">Speaker: ', ' ', '</span>');
		echo the_terms( $post->ID, 'wpfc_sermon_series', '<span class="sermon_series">Series: ', ' ', '</span>' ); 
		?>
	</div>

	<?php do_action( 'sm_archive_loop_after' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->