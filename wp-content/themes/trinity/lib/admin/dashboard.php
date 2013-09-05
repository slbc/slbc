<?php

function churchthemes_extend_right_now() {
	$args = array(
		'exclude_from_search' => false,
		'_builtin' => false
	);
	$output = 'object';
	$operator = 'and';

	$post_types = get_post_types($args, $output, $operator);

	foreach($post_types as $post_type) {
		$num_posts = wp_count_posts($post_type->name);
		$num = number_format_i18n($num_posts->publish);
		$text = _n($post_type->labels->singular_name, $post_type->labels->name, intval($num_posts->publish));
		if(current_user_can('edit_posts')) {
			?>
			<tr>
				<td class="first b b-<?php echo $post_type->name ?>"><a href="edit.php?post_type=<?php echo $post_type->name ?>"><?php echo $num ?></a></td>
				<td class="t <?php echo $post_type->name ?>"><a href="edit.php?post_type=<?php echo $post_type->name ?>"<?php if(post_type_supports($post_type->name, 'entry-views')): ?> title="<?php echo number_format(array_sum(churchthemes_get_meta_values('Views', $post_type->name, 'publish'))) . __(' Views', 'churchthemes') ?>"<?php endif; ?>><?php echo $post_type->labels->name ?></a></td>
			</tr>
			<?php
		}
	}
}
add_action( 'right_now_content_table_end' , 'churchthemes_extend_right_now' );

if (current_user_can('edit_theme_options')) {

	function churchthemes_dashboard_widgets() {
		// wp_add_dashboard_widget('churchthemes_support', __('ChurchThemes Support', 'churchthemes'), 'churchthemes_support');
		wp_add_dashboard_widget('churchthemes_top_sermons', __('Top 10 Sermons', 'churchthemes'), 'churchthemes_top_sermons');
	}
	add_action('wp_dashboard_setup', 'churchthemes_dashboard_widgets');

	function churchthemes_support() {
		?>
		<h2>Need help? Get support here.</h2>
		<p>We are committed to providing the <strong><em>absolute best support</em></strong> for all our themes. If you're experiencing problems, or just need some help, make sure to check out our <a href="https://churchthemes.zendesk.com/forums/20106966-documentation" target="_blank">Documentation</a>, <a href="https://churchthemes.zendesk.com/forums/20124487-tutorial-videos" target="_blank">Tutorial Videos</a>, and <a href="https://churchthemes.zendesk.com/forums/20060137-knowledge-base" target="_blank">Knowledge Base</a> articles. We are constantly updating these with solutions to common problems.</p>
		<br>
		<form action="https://churchthemes.zendesk.com/categories/search" target="_blank">
			<input name="query" type="text" class="textfield" size="40" placeholder="Keywords about your topic...">
			<input class="button rbutton" type="submit" value="Search Support Forums">
			<input type="hidden" name="for_search" value="1">
			<input type="hidden" name="commit" value="Search">
		</form>
		<br>
		<p><em>Still can't find a solution? Let's create a new ticket. Make sure to have the <strong>order number</strong> we emailed you handy.</em></p>
		<p><a href="https://churchthemes.zendesk.com/tickets/new" target="_blank" class="button rbutton">Create a Support Ticket</a></p>
		<div class="ct_divider"></div>

		<h2>Awesome ideas, welcome.</h2>
		<p>If you have an awesome idea please share it! It would mean the world to us and the entire ChurchThemes community.</p>
		<a href="https://churchthemes.zendesk.com/forums/20060147-feature-requests/entries/new" target="_blank" class="button rbutton">Make A Suggestion</a><br><br>
		<div class="ct_divider"></div>

		<h2>Please, get in touch!</h2>
		<p>We would absolutely love to see how you're using ChurchThemes to make Jesus famous! It is, after all, our whole motivation for continuing to develop WordPress themes for churches.<p>
		<p>Once you're all setup, give us a shout on Twitter <a href="http://twitter.com/churchthemesnet" target="_blank">@churchthemesnet</a> with your link or post it on our <a href="http://www.facebook.com/churchthemes" target="_blank">Facebook wall</a>.</p>
		<p>All the best to you and your local church &#8212; <em>happy WordPressing! :)</em></p>
		<p>&mdash; The ChurchThemes Team</p>
		<br>
		<?php
	}

	function churchthemes_top_sermons() {
		global $post, $meta_counts;
		query_posts( array(
			'post_type' => 'ct_sermon',
			'orderby' => 'meta_value_num',
			'meta_key' => 'Views',
			'order' => 'DESC',
			'posts_per_page' => 10,
		));
		$total_views =
		wp_reset_query();
		query_posts( array(
			'post_type' => 'ct_sermon',
			'orderby' => 'meta_value_num',
			'meta_key' => 'Views',
			'order' => 'DESC',
			'posts_per_page' => 10,
		));
		if( have_posts() ) :
		$i = 0;
			?>
			<h2 class="center" style="font-size:18px;padding-bottom:10px;"><?php _e('Your sermons have been viewed', 'churchthemes') ?> <strong><?php echo number_format(array_sum(churchthemes_get_meta_values('Views', 'ct_sermon', 'publish'))) ?> <?php _e('times', 'churchthemes') ?></strong></h2>
			<table width="100%" cellspacing="0" class="top_sermons">
				<thead>
					<th align="left" class="title"><?php _e('Title', 'churchthemes') ?></th>
					<th align="left"><?php _e('Speaker', 'churchthemes') ?></th>
					<th align="left"><?php _e('Published', 'churchthemes') ?></th>
					<th align="right"><?php _e('Views', 'churchthemes') ?></th>
				</thead>
			<?php while ( have_posts() ) : the_post(); $i++; ?>
			<?php
				global $post;
				$speakers = get_the_terms($post->ID, 'sermon_speaker');
			?>
				<tr<?php if($i % 2 == 0) echo ' class="alt"'; ?>>
					<td>
						<span class="count"><?php echo $i ?>.</span>
						<span class="title" style="clear:none;">
							<a href="<?php echo get_edit_post_link( $post->ID ) ?>" title="<?php echo comments_number() ?>"><?php the_title() ?></a>
						</span>
					</td>
					<td>
					<?php foreach($speakers as $speaker): ?>
						<?php if(count($speakers) > 1): ?>
						<br>
						<?php endif; ?>
						<a href="<?php echo esc_url ( add_query_arg( array( 'sermon_speaker' => $speaker->slug, 'post_type' => 'ct_sermon' ), 'edit.php' ) ) ?>" title="<?php esc_attr_e($speaker->count) ?> Sermons by <?php esc_attr_e($speaker->name) ?>"><?php echo $speaker->name ?></a>
					<?php endforeach; ?>
						<br>
					</td>
					<td><?php echo get_the_date('Y/m/d') ?></td>
					<td align="right"><?php echo get_post_meta($post->ID, 'Views', true) ?></td>
				</tr>
				<?php
			endwhile;
		else:
			$noviews_query = new WP_Query(array(
				'post_type' => 'ct_sermon',
				'posts_per_page' => 1,
			));
			if( $noviews_query->have_posts() ) :
				?>
				<p class="center"><strong><?php _e('We can\'t display your top sermons yet', 'churchthemes') ?></strong></p>
				<?php
			else:
				?>
				<p class="center"><strong><?php _e('The Sermon Media Library is empty', 'churchthemes') ?></strong></p>
				<p class="center"><a href="post-new.php?post_type=ct_sermon"><?php _e('Add a Sermon', 'churchthemes') ?></a></p>
				<?php
			endif;
		endif;
		if( have_posts() ) :
			?>
			</table>
			<?php
		endif;
		wp_reset_query();
	}
}
