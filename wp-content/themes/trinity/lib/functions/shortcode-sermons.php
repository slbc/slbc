<?php
$i = 0;

if(have_posts()): while(have_posts()): the_post();

$i++;

$sermon_speaker = get_the_term_list($post->ID, 'sermon_speaker');

$img_atts = array(
	'alt'	=> trim(strip_tags($post->post_title)),
	'title'	=> trim(strip_tags($post->post_title)),
);
?>
<div class="post ct_sermon<?php if($i == 1): echo ' first'; endif; ?>">
	<div class="date"><?php the_time(get_option('date_format')); ?></div>
	<h2><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'churchthemes'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
<?php if($sermon_speaker): ?>
	<h4><?php echo $sermon_speaker; ?></h4>
<?php endif; ?>
	<div class="excerpt">
		<div class="image"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute('echo=0'); ?>" rel="bookmark"><?php echo get_the_post_thumbnail($post->ID, 'archive', $img_atts); ?></a></div>
		<p><?php the_excerpt(); ?><p>
	</div>
	<div class="clear"></div>
</div>
<?php endwhile; else: ?>
<div class="post ct_sermon first">
	<h2><?php _e('No results found', 'churchthemes'); ?></h2>
	<p><?php _e('Sorry, nothing was found matching that criteria.', 'churchthemes'); ?></p>
</div>
<?php endif; ?>
