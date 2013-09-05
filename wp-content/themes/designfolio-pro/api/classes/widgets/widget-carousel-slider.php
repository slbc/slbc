<?php

// ----------------------------------
//  Carousel Slider Widget Class
// ----------------------------------

class pc_carousel_slider_widget extends WP_Widget {

	function pc_carousel_slider_widget() {

		/* Add additional slider image sizes used only by the slider */
		$this->set_slider_image_sizes();

		$widget_ops = array(	'classname' => 'pc_carousel_slider_widget',
								'description' => __( 'A carousel slider displaying a strip of items.', 'presscoders' )
		);
		$this->WP_Widget('pc_carousel_slider_widget_'.PC_THEME_NAME_SLUG, __( 'Carousel Slider', 'presscoders' ), $widget_ops);
	}

	function form( $instance ) {
		$defaults = array(	'delay_speed' => '7000',
							'transition_speed' => '600',
							'portfolio_groups' => '1',
							'number_items' => 4,
							'randomize' => false,
							'autoplay' => false,
							'animation_loop' => true
		);
        $instance = wp_parse_args( (array) $instance, $defaults );

		$delay_speed = strip_tags($instance['delay_speed']);
		$transition_speed = strip_tags($instance['transition_speed']);
        $randomize = strip_tags($instance['randomize']);
        $autoplay = strip_tags($instance['autoplay']);
        $animation_loop = strip_tags($instance['animation_loop']);

		if ( !isset($instance['delay_speed']) || !$delay_speed = (int) $instance['delay_speed'] )
			$delay_speed = 3000;

		if ( !isset($instance['transition_speed']) || !$transition_speed = (int) $instance['transition_speed'] )
			$transition_speed = 350;

		if ( !isset($instance['number_items']) || !$number_items = (int) $instance['number_items'] )
			$number_items = 4;

		/* Check the taxonmy contains any terms. If none found then exit the function. */
		$args = array(	'taxonomy' => 'portfolio_group',
						'title_li' => '',
						'show_option_none' => 'zero',
						'style' => 'none',
						'echo' => 0
		);
		if( wp_list_categories( $args ) == 'zero' ) {
			_e( 'No portfolio groups found. New groups can be added via Portfolios -> Portfolio Groups.', 'presscoders' );
			return;
		}
		?>

		<div style="margin-bottom:2px;"><label for="<?php echo $this->get_field_id('portfolio_groups'); ?>"><?php _e( 'Choose portfolio group:', 'presscoders' ); ?></label></div>
		<?php
			$args = array(
				'id' =>				$this->get_field_id( 'portfolio_groups' ),
				'hide_empty'=>		0,
				'hierarchical' =>	1,
				'show_count' =>		1,
				'name' =>			$this->get_field_name( 'portfolio_groups' ),
				'taxonomy' =>		'portfolio_group',
				'class'=>			'widefat',
				'selected' =>		$instance[ 'portfolio_groups' ]
			);
			wp_dropdown_categories( $args );
		?>
		<p style="margin:10px 0px;">
			<label for="<?php echo $this->get_field_id('number_items'); ?>"><?php _e( 'Maximum No. of slider items', 'presscoders' ); ?>: </label>
			<input id="<?php echo $this->get_field_id('number_items'); ?>" name="<?php echo $this->get_field_name('number_items'); ?>" type="text" value="<?php echo $number_items; ?>" size="3" />
		</p>

		<label><input type="checkbox" value="1" <?php checked( $randomize, '1' ); ?> name="<?php echo $this->get_field_name( 'randomize' ); ?>" />&nbsp;<?php _e('Random slide order', 'presscoders' ) ?></label><br />
		<label><input type="checkbox" value="1" <?php checked( $autoplay, '1' ); ?> name="<?php echo $this->get_field_name( 'autoplay' ); ?>" />&nbsp;<?php _e('Autoplay slides', 'presscoders' ) ?></label><br />
		<label><input type="checkbox" value="1" <?php checked( $animation_loop, '1' ); ?> name="<?php echo $this->get_field_name( 'animation_loop' ); ?>" />&nbsp;<?php _e('Loop Slides', 'presscoders' ) ?></label>

		<p>
			<div style="margin-bottom:2px;"><?php _e( 'Transition settings', 'presscoders' ); ?>:</div>
			<label for="<?php echo $this->get_field_id('transition_speed'); ?>">&nbsp;&nbsp;<?php _e( 'Speed', 'presscoders' ); ?>: </label>
			<input id="<?php echo $this->get_field_id('transition_speed'); ?>" name="<?php echo $this->get_field_name('transition_speed'); ?>" type="text" value="<?php echo $transition_speed; ?>" size="2" /> ms
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('delay_speed'); ?>"><?php _e( 'Delay between slides', 'presscoders' ); ?>: </label>
			<input id="<?php echo $this->get_field_id('delay_speed'); ?>" name="<?php echo $this->get_field_name('delay_speed'); ?>" type="text" value="<?php echo $delay_speed; ?>" size="2" /> ms
		</p>

	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'delay_speed' ] = strip_tags($new_instance[ 'delay_speed' ]);
		$instance[ 'transition_speed' ] = strip_tags($new_instance[ 'transition_speed' ]);
		$instance[ 'portfolio_groups' ] = $new_instance[ 'portfolio_groups' ];
		$instance[ 'number_items' ] = (int) $new_instance[ 'number_items' ];
        $instance[ 'randomize' ] = strip_tags($new_instance['randomize']);
        $instance[ 'autoplay' ] = strip_tags($new_instance['autoplay']);
        $instance[ 'animation_loop' ] = strip_tags($new_instance['animation_loop']);

		return $instance;
	}

	function widget($args, $instance) {

		extract($args);

		$delay_speed = $instance[ 'delay_speed' ];
		$transition_speed = $instance[ 'transition_speed' ];
        $number_items = $instance[ 'number_items' ];
		$portfolio_groups = $instance[ 'portfolio_groups' ];
		$randomize = $instance['randomize'];
		$autoplay = $instance['autoplay'];
		$animation_loop = $instance['animation_loop'];

		if( empty($portfolio_groups) ) $portfolio_groups = '';

		$order = ( $randomize ) ? 'rand' : 'date';
		$autoplay_slides = ($autoplay) ? 'true' : 'false';
		$auto_animation_loop = ($animation_loop) ? 'true' : 'false';

		$r = new WP_Query( array(	'post_type' => 'portfolio',
									'orderby' => $order,
									'showposts' => $number_items,
									'nopaging' => 0,
									'post_status' => 'publish',
									'ignore_sticky_posts' => 1,
									'tax_query' => array(
										array(
											'taxonomy' => 'portfolio_group',
											'field' => 'id',
											'terms' => $portfolio_groups
										)
									)							
		));

		if ($r->have_posts()) :

			$slider_thumb_size = 'carousel_image_content';
		?>

		<?php echo $before_widget; ?>

			<script type="text/javascript">
				
				jQuery(window).load(function() {
					jQuery('.flexslider_<?php echo $this->id; ?>').flexslider({
						  animation: 'slide',
						  animationLoop: <?php echo $auto_animation_loop; ?>,
						  pauseOnAction: true,
						  slideshow: <?php echo $autoplay_slides; ?>,
						  pauseOnHover: true,
						  slideshowSpeed: <?php echo $delay_speed; ?>,
						  animationSpeed: <?php echo $transition_speed; ?>,
						  controlsContainer: ".flex-container",
						  multipleKeyboard: true,
						  useCSS: false,
						  itemWidth: 1, // set to arbitrary value to switch carousel on, minItems and maxItems used to resize items width responsively.
						  minItems: 4,
						  maxItems: 4,
						  itemMargin: 5, // this should match the '.carousel li' margin set in style.css
						  //move: 1,
						  start: function(slider) {
							jQuery(document).ready(function($) {
								jQuery(".flex-container").css({ 'opacity': 1 });
							//	jQuery("#before-content .flex-container").attr('style', 'padding-top:10px;margin-top:50px;');
							//	jQuery("#before-content .flexslider").css({ 'padding-top': '20px' });
							//	jQuery("#before-content").css({ 'margin': '30px 0 20px 0' });
							});
						  },
					});
				});

			</script>

			<div class="flex-container">

				<div class="flexslider carousel flexslider_<?php echo $this->id; ?>">
					<ul class="slides slides_<?php echo $this->id; ?>">

						<?php while ($r->have_posts()) : $r->the_post(); ?>

							<?php
								/* Get meta data for a particular portfolio item. */
								$post_id = get_the_ID();
								$featured_image = PC_Utility::get_responsive_slider_image( $post_id, $slider_thumb_size );
								$title = get_the_title();
																
								$portfolio_fi_url = trim(get_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_fi_link',true));
								$portfolio_title_url = trim(get_post_meta($post_id, '_'.PC_THEME_NAME_SLUG.'_portfolio_cpt_title_link',true));

								$content = wpautop( do_shortcode( get_the_content() ) );

								if( !empty($title) ) {
									$title = empty($portfolio_fi_url) ? $title : '<a href="'.$portfolio_fi_url.'">'.$title.'</a>';
									$title = '<h3 class="slide-name">'.$title.'</h3>';
								}

								if( !empty($portfolio_fi_url) && $featured_image )
									$featured_image = '<a href="'.$portfolio_fi_url.'">'.$featured_image.'</a>';
									//$featured_image = '<a href="'.$portfolio_fi_url.'" title="'.get_the_title().'">'.$featured_image.'</a>';
							?>

							<li>
								<?php
									echo '<div class="slide-content">';
									if(!empty($featured_image)) echo $featured_image;
									echo $title;
									//if(!empty($content)) echo $content;
									echo '</div>';
								?>
							</li>

						<?php endwhile; ?>

					</ul>
				</div>

			</div>

		<?php echo $after_widget; ?>

		<?php

		/* Reset the global $the_post as this query will have stomped on it. */
		wp_reset_postdata();

		else :

			echo "<div>No slider items to show. Please select a group that contains some items!</div>";

		endif;
	}

	/**
	 * Set content slider image sizes.
	 *
	 * @since 0.1.0
	 */
	public function set_slider_image_sizes() {

		global $_wp_additional_image_sizes;

		/* Check if carousel image size needs specifying, as they may have been set in another slider widget. */
		if ( !isset( $_wp_additional_image_sizes['carousel_image_content'] ) ) {
			add_image_size( 'carousel_image_content', PC_CAROUSEL_CONTENT_WIDTH, PC_CAROUSEL_CONTENT_HEIGHT, true );
		}
	}

	/**
	 * PHP callback function to filter an array, returning only numbers greater than zero.
	 *
	 * @since 0.1.0
	 */
	public function delete_zero($var)
	{
		return ($var > 0);
	}
}

?>