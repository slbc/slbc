<?php
$i = 0;

if(have_posts()): while(have_posts()): the_post();

$i++;

$person_role = get_post_meta($post->ID, '_ct_ppl_role', true);

$img_atts = array(
	'alt'	=> trim(strip_tags($post->post_title)),
	'title'	=> trim(strip_tags($post->post_title)),
);
?>
<div class="post ct_person<?php if($i == 1): echo ' first'; endif; ?>">
	<h2><a href="<?php the_permalink(); ?>" title="<?php printf(esc_attr__('Permalink to %s', 'churchthemes'), the_title_attribute('echo=0')); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
<?php if($person_role): ?>
	<h4><?php echo $person_role; ?></h4>
<?php endif; ?>
	<div class="excerpt">
		<div class="image"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute('echo=0'); ?>" rel="bookmark"><?php echo get_the_post_thumbnail($post->ID, 'archive', $img_atts); ?></a></div>
		<p><?php the_excerpt(); ?><p>
		<?php ct_person_contact(); ?>
		<?php ct_person_social(); ?>
	</div>
	<div class="clear"></div>
</div>
<?php endwhile; else: ?>
<div class="post ct_person first">
	<h2><?php _e('No results found', 'churchthemes'); ?></h2>
	<p><?php _e('Sorry, nothing was found matching that criteria.', 'churchthemes'); ?></p>
</div>
<?php endif; ?>
