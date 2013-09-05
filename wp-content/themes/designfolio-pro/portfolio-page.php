<?php
/*
Template Name: Portfolio
*/
?>
<?php get_header(); ?>

<?php PC_Hooks::pc_after_get_header(); /* Framework hook wrapper */ ?>

	<div id="container" class="singular-page">

		<?php PC_Hooks::pc_after_container(); /* Framework hook wrapper */ ?>

		<div id="contentwrap" <?php echo PC_Utility::contentwrap_layout_classes(); ?>>

			<?php PC_Hooks::pc_before_content(); /* Framework hook wrapper */ ?>

			<div class="<?php echo PC_Utility::content_layout_classes_primary(); ?>">

				<?php PC_Hooks::pc_after_content_open(); /* Framework hook wrapper */ ?>

					<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							
							<?php if ( is_home() ) { ?>
								<?php PC_Utility::hide_title_header_tag( get_the_ID(), "h2", "page-title entry-title" ); ?>
							<?php } else { ?>
								<?php PC_Utility::hide_title_header_tag( get_the_ID(), "h1", "page-title entry-title" ); ?>
							<?php } ?>
							
							<?php
								the_content();
								wp_link_pages( array( 'before' => '<div class="page-link">', 'after' => '</div>' ) );
							?>

					</div> <!-- post-item -->

					<?php endwhile; ?>

					<?php
					$pc_portfolio_group = get_post_meta( get_the_id(), '_'.PC_THEME_NAME_SLUG.'_portfolio_group',true );
					$pc_portfolio_columns = get_post_meta( get_the_id(), '_'.PC_THEME_NAME_SLUG.'_portfolio_columns',true );

					switch ($pc_portfolio_columns) {
						case 'large':
							$thumb_size = 'large-pf-image';
							$pf_container_class = 'class="portfolio-large"';
							break;
						case 'medium':
							$thumb_size = 'medium-pf-image';
							$pf_container_class = 'class="portfolio-medium"';
							break;
						case 'small':
							$thumb_size = 'small-pf-image';
							$pf_container_class = 'class="portfolio-small"';
							break;
						default:
							$thumb_size = 'thumbnail';
							$pf_container_class = '';
					}

					//echo "<strong>Portfolio debug information</strong><br />";
					//echo "Portfolio group selected is: ".get_term( $pc_portfolio_group, 'portfolio_group' )->name." [category id=".$pc_portfolio_group."]<br />";
					//echo "Portfolio columns: ".$pc_portfolio_columns." [portfolio page post id=".get_the_id()."]<br />";

					$pf_group_exists = get_terms( 'portfolio_group', 'orderby=ID&number=1' );

					if ( !empty( $pf_group_exists ) ) :

						$r = new WP_Query( array(	'post_type' => 'portfolio',
													'orderby' => 'date',
													'showposts' => 1000, // @todo Having to set this to an arbitrarily high value as 0 doesn't work.
													'nopaging' => 0,
													'post_status' => 'publish',
													'ignore_sticky_posts' => 1,
													'tax_query' => array(
														array(
															'taxonomy' => 'portfolio_group',
															'field' => 'id',
															'terms' => $pc_portfolio_group
														)
													)							
						));

						if ($r->have_posts()) :

							$parent = get_term( $pc_portfolio_group, 'portfolio_group' );

							$args = array(
									'type'                     => 'post',
									'child_of'                 => $pc_portfolio_group,
									'hide_empty'               => true,
									'hierarchical'             => true,
									'taxonomy'				   => 'portfolio_group'
							);

							$cats = get_categories($args);

							$pf_group_links = array();
							$pf_group_links[] = $parent->name;

							?>

							<ul id="filters">
								<?php $parent_name = $parent->name;
								      if( $parent_name == 'default' ) $parent_name = 'portfolio'; // For the default portfolio group make the label more readable
								?>
								<li class="active"><a href="javascript:void(0)" class="all"><?php echo ucwords(strtolower($parent_name)); ?></a></li>

								<?php foreach($cats as $cat){ ?>
									<li><a href="javascript:void(0)" class="<?php echo $cat->slug; ?>"><?php echo ucwords(strtolower($cat->cat_name)); ?></a></li>
								<?php } ?>

							</ul>

							<div id="pc-portfolio"<?php echo $pf_container_class; ?>>

								<ul class="filterable-grid">

									<?php $count = 1; /* Initialize id counter. */ ?>

									<?php while ($r->have_posts()) : $r->the_post(); ?>

										<?php

										$pf_groups = get_the_terms( get_the_id(), 'portfolio_group' );

										if ( $pf_groups && !is_wp_error( $pf_groups ) ) : 

											$pf_group_list = array();
											foreach ( $pf_groups as $pf_group ) {
												$pf_group_list[] = strtolower($pf_group->slug);
											}
											$pf_group_list = join( " ", $pf_group_list );

										endif;
										
										?>

										<?php
											/* Image */
											$id = get_the_id();

											/* If the first post then add a ribbon class. */
											if( $count == 1 )
												$image_attr = array( 'class' => 'new-ribbon new-ribbon-'.$id.' featured-image featured-image-'.$id.' pf-post-image' );
											else 
												$image_attr = array( 'class' => 'featured-image featured-image-'.$id.' pf-post-image' );

											$image = PC_Utility::get_responsive_featured_image( $id, $thumb_size, $image_attr );

											$excerpt = strip_tags($post->post_excerpt);
											$title = get_the_title();

											$title_len = mb_strlen($title);
											$excerpt_len = mb_strlen($excerpt);
											$char_limit = 80; /* Limit title + excerpt to certain number of chars. */

											//echo "Total[b]: ".(mb_strlen($title) + mb_strlen($excerpt)).'<br />';
											//echo "TL[b]: ".mb_strlen($title).'<br />';
											//echo "EX[b]: ".mb_strlen($excerpt).'<br />';

											/* If title plus excerpt exceeds char limit, trim excerpt. */
											if( ($title_len + $excerpt_len) > $char_limit) {
												if( $title_len > $char_limit ) { /* Check special case of title exceeding char limit. */
													/* Extra 3 accounts for the ellipsis. */
													$title = mb_substr($title, 0, $char_limit - 3).'...';
													$excerpt = '';
												}
												else { /* Otherwise trim excerpt by required amount. */
													$trim_amount = ($title_len + $excerpt_len) - $char_limit;
													/* Extra 3 accounts for the ellipsis. */
													$excerpt = mb_substr($excerpt, 0, ($excerpt_len - $trim_amount - 3)).'...';
												}
											}

											//echo "<br />Total[a]: ".(mb_strlen($title) + mb_strlen($excerpt)).'<br />';
											//echo "TL[a]: ".mb_strlen($title).'<br />';
											//echo "EX[a]: ".mb_strlen($excerpt).'<br />';

											$portfolio_fi_url = trim(get_post_meta($id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link',true));
											$portfolio_title_url = trim(get_post_meta($id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link',true));

											$post_thumbnail_id = get_post_thumbnail_id( $id );
											$featured_image_src_arr = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

											/* Portfolio featured image link. */
											if( empty($portfolio_fi_url) ) {
												if( $image && is_array($featured_image_src_arr) ) {
													$featured_image_src = $featured_image_src_arr[0];
													$image = '<a href="'.$featured_image_src.'">'.$image.'</a>';
												}
											}
											else {
												if( $portfolio_fi_url != 'http://none' ) {
													$image = '<a href="'.$portfolio_fi_url.'">'.$image.'</a>';
												}
											}

											/* Portfolio title link. */
											if( empty($portfolio_title_url) ) {
												$pl = get_permalink($id);
												$title = '<a href="'.$pl.'">'.$title.'</a>';
											}
											else {
												if( $portfolio_title_url != 'http://none' ) {
													$title = '<a href="'.$portfolio_title_url.'">'.$title.'</a>';
												}
											}

											if( !empty($title) ) $title = '<h3 class="portfolio-title">'.$title.'</h3>';

										?>

										<li data-id="id-<?php echo $count; ?>" data-type="<?php echo $pf_group_list; ?>">
											<?php if( !empty($image) ) echo $image; ?>
											<?php echo $title; ?>
											<?php if( !empty($excerpt) ) echo '<div class="excerpt">'.$excerpt.'</div>'; ?>
										</li>

										<?php $count++; /* Increment id counter. */ ?>

									<?php endwhile; ?>

								</ul>

							</div>	

						<?php

						/* Reset the query. */
						wp_reset_postdata();

						endif; // if have posts
					
					else :
						echo '<div class="warning"><p>Try adding some Portfolio items to a group!</p></div>';

					endif; // not empty $pf_group_exists
					?>


					<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					
					<?php edit_post_link( __( 'Edit', 'presscoders' ), '<span class="edit-link">', '</span>' ); ?>

					<?php comments_template( '', true ); ?>

					<?php endwhile; ?>

			</div><!-- .content -->

			<?php PC_Hooks::pc_after_content(); /* Framework hook wrapper */ ?>
		
		</div><!-- #contentwrap -->
	
	</div><!-- #container -->

<?php get_footer(); ?>