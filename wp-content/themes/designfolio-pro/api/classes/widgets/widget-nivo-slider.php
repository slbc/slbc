<?php

// ----------------------------------
//  Nivo Slider Widget Class
// ----------------------------------

class pc_nivo_slider_widget extends WP_Widget {

	function pc_nivo_slider_widget() {

		/* Add additional slider image sizes used ony by the slider */
		$this->set_slider_image_sizes();

		$widget_ops = array('classname' => 'pc_nivo_slider_widget', 'description' => __( 'A flexible slider control that displays links to featured post/pages. Add this to any widget area and it will dynamically resize!', 'presscoders' ) );
		$this->WP_Widget('pc_nivo_slider_widget_'.PC_THEME_NAME_SLUG, __( 'Nivo Slider', 'presscoders' ), $widget_ops);
	}

	function form( $instance ) {

        $defaults = array( 'nivo_posts_category' => '1', 'nivo_pages_parent' => '2', 'nivo_images_list' => '', 'nivo_id_list' => '', 'overlay_opacity' => '0.8', 'anim_speed' => '600', 'pause_time' => '4000', 'featured_slider_items' => '4', 'featured_item_type' => 'post_featured_item', 'nivo_transition_effect' => 'fade' );
        $instance = wp_parse_args( (array) $instance, $defaults );

		if ( !isset($instance['featured_slider_items']) || !$featured_slider_items = (int) $instance['featured_slider_items'] )
			$featured_slider_items = 4;
		else {
			if ( !($featured_slider_items>=1) ) $featured_slider_items = 4; // only allow positive numbers > 0
		}
		
		if ( !isset($instance['overlay_opacity']) || !$overlay_opacity = (float) $instance['overlay_opacity'] ) {
			if( $overlay_opacity != 0.0 ) $overlay_opacity = 0.8; // doesn't appear to be a valid number (and not zero) so set to 0.8
		}
		else {
			if ( !($overlay_opacity>=0.0 && $overlay_opacity<=1.0) ) $overlay_opacity = 0.8; // only allow between 0.0 -> 1.0 (inclusive) else reset to 0.8
		}

		if ( !isset($instance['anim_speed']) || !$anim_speed = (int) $instance['anim_speed'] ) {
			$anim_speed = 600; // doesn't appear to be a valid number so set to 600 ms
		}

		if ( !isset($instance['pause_speed']) || !$pause_speed = (int) $instance['pause_speed'] ) {
			$pause_speed = 4000; // doesn't appear to be a valid number so set to 4000ms
		}

		$nivo_posts_category = $instance['nivo_posts_category'];
		$nivo_pages_parent = $instance['nivo_pages_parent'];
		$featured_item_type = $instance['featured_item_type'];
		$nivo_transition_effect = $instance['nivo_transition_effect'];
		$nivo_images_list = $instance['nivo_images_list'];
		$nivo_id_list = $instance['nivo_id_list'];
		?>

		<script language="javascript">
			jQuery(document).ready(function($) {

				$("#widgets-right .auto-save").livequery(function(){
				  var widget = $(this).closest('div.widget');
				  wpWidgets.save(widget, 0, 1, 0);
				  return false;
				});

				// Get the base name for a particular widget instance (there may be several defined)
				var rdo_name = '<?php echo $this->get_field_name('featured_item_type'); ?>';
				var rdo_name_base = rdo_name.slice(0, -20);

				// Get selected radio button for current slider widget instance
				var current_radio = $("input[name='" + rdo_name + "']:radio:checked").val();
				show_hide_controls(current_radio);

				// Show the controls associated with the selected radio button, and hide the others
				$("input[name='" + rdo_name + "']:radio").change(function(){
					current_radio = $("input[name='" + rdo_name + "']:radio:checked").val();
					show_hide_controls(current_radio);
				});

				// Hide by default
				$("#nivo-slider-toggle-<?php echo $this->number; ?>").css({'display': 'none'});

				// Toggle widget options for advanced section
				$("#nivo-slider-trigger-<?php echo $this->number; ?>").click(function () {

					var expanded_status;
					expanded_status = $(this).is(":contains('[+]')");

					if( expanded_status ) {
						$("#nivo-slider-toggle-<?php echo $this->number; ?>").show('fast');
						$("#nivo-slider-trigger-<?php echo $this->number; ?>").html('<a>[-] <span style="font-style:italic;"><?php _e( 'Advanced Options', 'presscoders' ); ?></span></a>');
					}
					else {
						$("#nivo-slider-toggle-<?php echo $this->number; ?>").hide('fast');
						$("#nivo-slider-trigger-<?php echo $this->number; ?>").html('<a>[+] <span style="font-style:italic;"><?php _e( 'Advanced Options', 'presscoders' ); ?></span></a>');
					}
				});

				function show_hide_controls(current_radio) {
					if( current_radio == "post_featured_item" ) {
						// Show controls
						$("select[name='" + rdo_name_base + "[nivo_posts_category]" + "']").parent().css("display", "block");

						// Hide controls
						$("select[name='" + rdo_name_base + "[nivo_pages_parent]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_images_list]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_id_list]" + "']").parent().css("display", "none");
					}
					else if( current_radio == "page_featured_item" ) {
						// Show controls
						$("select[name='" + rdo_name_base + "[nivo_pages_parent]" + "']").parent().css("display", "block");

						// Hide controls
						$("select[name='" + rdo_name_base + "[nivo_posts_category]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_images_list]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_id_list]" + "']").parent().css("display", "none");
					}
					else if( current_radio == "image_featured_item" ) {
						// Show controls
						$("input[name='" + rdo_name_base + "[nivo_images_list]" + "']").parent().css("display", "block");

						// Hide controls
						$("select[name='" + rdo_name_base + "[nivo_posts_category]" + "']").parent().css("display", "none");
						$("select[name='" + rdo_name_base + "[nivo_pages_parent]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_id_list]" + "']").parent().css("display", "none");
					}
					else {
						// Must be current_radio == "id_featured_item"
						// Show controls
						$("input[name='" + rdo_name_base + "[nivo_id_list]" + "']").parent().css("display", "block");

						// Hide controls
						$("select[name='" + rdo_name_base + "[nivo_posts_category]" + "']").parent().css("display", "none");
						$("select[name='" + rdo_name_base + "[nivo_pages_parent]" + "']").parent().css("display", "none");
						$("input[name='" + rdo_name_base + "[nivo_images_list]" + "']").parent().css("display", "none");
					}
				}

			});

		</script>

		<div style="width:1px;height:1px;" <?php if(!is_numeric($this->number)): ?>class="auto-save"<?php endif; ?> ></div>

		<p style="margin-bottom:2px;"><?php _e( 'Choose Slider Content Type', 'presscoders' ); ?>:</p>
		<label><input type="radio" value="post_featured_item" <?php checked('post_featured_item', $featured_item_type); ?> name="<?php echo $this->get_field_name('featured_item_type'); ?>" /> <?php _e( 'Posts (category)', 'presscoders' ); ?></label><br />
		<label><input type="radio" value="page_featured_item" <?php checked('page_featured_item', $featured_item_type); ?> name="<?php echo $this->get_field_name('featured_item_type'); ?>" /> <?php _e( 'Pages (page parent)', 'presscoders' ); ?></label><br />
		<label><input type="radio" value="image_featured_item" <?php checked('image_featured_item', $featured_item_type); ?> name="<?php echo $this->get_field_name('featured_item_type'); ?>" /> <?php _e( 'Media Libray Image ID\'s', 'presscoders' ); ?></label><br />
		<label><input type="radio" value="id_featured_item" <?php checked('id_featured_item', $featured_item_type); ?> name="<?php echo $this->get_field_name('featured_item_type'); ?>" /> <?php _e( 'Individual Post/Page ID\'s', 'presscoders' ); ?></label>

		<p style="margin-top:8px;"><?php _e( 'Post category', 'presscoders' ); ?>: <?php $args = array('show_option_all' => 'All Categories', 'id' => $this->get_field_id('nivo_posts_category'), 'hide_empty' => 0, 'hierarchical' => 0, 'show_count' => 0, 'name' => $this->get_field_name('nivo_posts_category'), 'selected' => $instance['nivo_posts_category']); wp_dropdown_categories( $args ); ?></p>
		
		<p style="margin-top:8px;"><?php _e( 'Page parent', 'presscoders' ); ?>: <?php $args = array('depth' => 1, 'name' => $this->get_field_name('nivo_pages_parent'), 'selected' => $instance['nivo_pages_parent']); wp_dropdown_pages( $args ); ?></p>

		<p style="margin-top:8px;"><label for="<?php echo $this->get_field_id('nivo_images_list'); ?>"><?php _e( 'Add media library image ID\'s:', 'presscoders' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('nivo_images_list'); ?>" name="<?php echo $this->get_field_name('nivo_images_list'); ?>" type="text" value="<?php echo $nivo_images_list; ?>" /></p>

		<p style="margin-top:8px;"><label for="<?php echo $this->get_field_id('nivo_id_list'); ?>"><?php _e( 'Enter list of Post/Page ID\'s:', 'presscoders' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('nivo_id_list'); ?>" name="<?php echo $this->get_field_name('nivo_id_list'); ?>" type="text" value="<?php echo $nivo_id_list; ?>" /></p>

		<p id="nivo-slider-trigger-<?php echo $this->number; ?>" style="cursor:pointer;font-size:1em;"><a>[+] <span style="font-style:italic;"><?php _e( 'Advanced Options', 'presscoders' ); ?></span></a></p>

		<div id="nivo-slider-toggle-<?php echo $this->number; ?>">
		
			<p style="margin-top:10px;">
				<?php _e( 'Transition Effect', 'presscoders' ); ?>:<br />
				<select style="width: 100%;" id="<?php echo $this->get_field_id('nivo_transition_effect'); ?>" name="<?php echo $this->get_field_name('nivo_transition_effect'); ?>">
					<option value="sliceDown" <?php selected('sliceDown', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Down', 'presscoders' ); ?>&nbsp;</option>
					<option value="sliceDownLeft" <?php selected('sliceDownLeft', $instance['nivo_transition_effect']); ?>><?php _e( 'Down Left', 'presscoders' ); ?>Slice &nbsp;</option>
					<option value="sliceDownRight" <?php selected('sliceDownRight', $instance['nivo_transition_effect']); ?>><?php _e( 'Down Right', 'presscoders' ); ?>Slice &nbsp;</option>
					<option value="sliceUp" <?php selected('sliceUp', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Up', 'presscoders' ); ?>&nbsp;</option>
					<option value="sliceUpLeft" <?php selected('sliceUpLeft', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Up Left', 'presscoders' ); ?>&nbsp;</option>
					<option value="sliceUpRight" <?php selected('sliceUpRight', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Up Right', 'presscoders' ); ?>&nbsp;</option>
					<option value="sliceUpDown" <?php selected('sliceUpDown', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Up Down', 'presscoders' ); ?>&nbsp;</option>
					<option value="sliceUpDownLeft" <?php selected('sliceUpDownLeft', $instance['nivo_transition_effect']); ?>><?php _e( 'Slice Up Down Left', 'presscoders' ); ?>&nbsp;</option>
					<option value="fold" <?php selected('fold', $instance['nivo_transition_effect']); ?>><?php _e( 'Fold', 'presscoders' ); ?>&nbsp;</option>
					<option value="fade" <?php selected('fade', $instance['nivo_transition_effect']); ?>><?php _e( 'Fade', 'presscoders' ); ?>&nbsp;</option>
					<option value="boxRandom" <?php selected('boxRandom', $instance['nivo_transition_effect']); ?>><?php _e( 'Box Random', 'presscoders' ); ?>&nbsp;</option>
					<option value="boxRain" <?php selected('boxRain', $instance['nivo_transition_effect']); ?>><?php _e( 'Box Rain', 'presscoders' ); ?>&nbsp;</option>
					<option value="boxRainReverse" <?php selected('boxRainReverse', $instance['nivo_transition_effect']); ?>><?php _e( 'Box Rain Reverse', 'presscoders' ); ?>&nbsp;</option>
					<option value="boxRainGrow" <?php selected('boxRainGrow', $instance['nivo_transition_effect']); ?>><?php _e( 'Box Rain Grow', 'presscoders' ); ?>&nbsp;</option>
					<option value="boxRainGrowReverse" <?php selected('boxRainGrowReverse', $instance['nivo_transition_effect']); ?>><?php _e( 'Box Rain Grow Reverse', 'presscoders' ); ?>&nbsp;</option>
					<option value="slideInRight" <?php selected('slideInRight', $instance['nivo_transition_effect']); ?>><?php _e( 'Wipe In Right', 'presscoders' ); ?>&nbsp;</option>
					<option value="slideInLeft" <?php selected('slideInLeft', $instance['nivo_transition_effect']); ?>><?php _e( 'Slide In Left', 'presscoders' ); ?>&nbsp;</option>
					<option value="random" <?php selected('random', $instance['nivo_transition_effect']); ?>><?php _e( 'Random', 'presscoders' ); ?>&nbsp;</option>
				</select>
			</p>

			<table>
				<tr>
					<td><label for="<?php echo $this->get_field_id('featured_slider_items'); ?>"><?php _e( 'Featured Items (max)', 'presscoders' ); ?>:&nbsp;&nbsp;</label></td>
					<td><input id="<?php echo $this->get_field_id('featured_slider_items'); ?>" name="<?php echo $this->get_field_name('featured_slider_items'); ?>" type="text" value="<?php echo $featured_slider_items; ?>" size="4" /></td>
				</tr>
				<tr>
					<td><label for="<?php echo $this->get_field_id('overlay_opacity'); ?>"><?php _e( 'Overlay Opacity', 'presscoders' ); ?>:</label></td>
					<td><input id="<?php echo $this->get_field_id('overlay_opacity'); ?>" name="<?php echo $this->get_field_name('overlay_opacity'); ?>" type="text" value="<?php echo $overlay_opacity; ?>" size="4" /></td>
				</tr>
				<tr>
					<td><label for="<?php echo $this->get_field_id('anim_speed'); ?>"><?php _e( 'Transition Speed', 'presscoders' ); ?>:</label></td>
					<td><input id="<?php echo $this->get_field_id('anim_speed'); ?>" name="<?php echo $this->get_field_name('anim_speed'); ?>" type="text" value="<?php echo $anim_speed; ?>" size="4" /></td>
				</tr>
				<tr>
					<td><label for="<?php echo $this->get_field_id('pause_speed'); ?>"><?php _e( 'Pause Time', 'presscoders' ); ?>:</label></td>
					<td><input id="<?php echo $this->get_field_id('pause_speed'); ?>" name="<?php echo $this->get_field_name('pause_speed'); ?>" type="text" value="<?php echo $pause_speed; ?>" size="4" /></td>
				</tr>
			</table>
		</div> <!-- nivo-slider-toggle -->

<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['nivo_posts_category'] = $new_instance['nivo_posts_category'];
		$instance['nivo_pages_parent'] = $new_instance['nivo_pages_parent'];
		$instance['nivo_images_list'] = $new_instance['nivo_images_list'];
		$instance['nivo_id_list'] = $new_instance['nivo_id_list'];
		$instance['overlay_opacity'] = (float) $new_instance['overlay_opacity'];
		$instance['anim_speed'] = (int) $new_instance['anim_speed'];
		$instance['pause_speed'] = (int) $new_instance['pause_speed'];
		$instance['featured_slider_items'] = (int) $new_instance['featured_slider_items'];
		$instance['featured_item_type'] = $new_instance['featured_item_type'];
		$instance['nivo_transition_effect'] = $new_instance['nivo_transition_effect'];

		return $instance;
	}

	function widget($args, $instance) {

		extract($args);

		$featured_item_type = $instance['featured_item_type'];
		$nivo_posts_category = $instance['nivo_posts_category'];
		$nivo_pages_parent = $instance['nivo_pages_parent'];
		$nivo_images_list = $instance['nivo_images_list'];
		$nivo_id_list = $instance['nivo_id_list'];
		$overlay_opacity = (float) $instance['overlay_opacity'];  // convert to float
		$anim_speed = (int) $instance['anim_speed'];
		$pause_speed = (int) $instance['pause_speed'];
		$featured_slider_items = (int) $instance['featured_slider_items'];  // convert to integer
		$nivo_transition_effect = $instance['nivo_transition_effect'];
        $nav_arrows_hide = (PC_SLIDER_NAV_ARROWS) ? 'false' : 'true';
        $nav_arrows_hover = (PC_SLIDER_NAV_ARROWS) ? 'true' : 'false';
		?>

		<script type="text/javascript">

			jQuery(document).ready(function($) {
				$('.slider_<?php echo $this->id; ?>').nivoSlider({
					animSpeed: <?php echo $anim_speed; ?>,
					pauseTime: <?php echo $pause_speed; ?>,
					effect: '<?php echo $nivo_transition_effect; ?>',
                    directionNav: <?php echo $nav_arrows_hover; ?>,
					directionNavHide: <?php echo $nav_arrows_hide; ?>,
					captionOpacity: <?php echo $overlay_opacity; ?>
				});

				// Display slider once page has loaded
				$(".slider-wrapper").fadeIn( 'slow' );
			});

		</script>

		<?php

		// START SLIDER
		if( $featured_item_type=="post_featured_item" ) {
			// Display posts
			$args = array( 'numberposts' => -1, 'category' => $nivo_posts_category );
			$slider_items = get_posts( $args );
		}
		elseif( $featured_item_type=="page_featured_item" ) {
			// Display pages
			$slider_items = get_pages("child_of=$nivo_pages_parent");
		}
		elseif( $featured_item_type=="image_featured_item" ) {
			// Display images only (no link to post/page)
			
			// Add in more widget options to insert image ID's only. If this option is selected then use the function below to get images by attachment ID rather than post/page ID
			
			// Create array of post/page ID's
			$nivo_image_list_array = explode(",", $nivo_images_list);

			// Turn it into an integer array
			$nivo_image_list_array = array_map(create_function('$value', 'return (int)$value;'),$nivo_image_list_array);

			// Remove any zero ID's (resulting from the integer casting)
			$slider_items = $nivo_image_list_array;
			$slider_items = array_filter($slider_items, array( &$this, 'delete_zero' ) );
		}
		else {
			// Assuming 'id_featured_item' selected
			// Display list of posts/pages from a lits of ID's

			$args = array( 'numberposts' => -1, 'include' => $nivo_id_list, 'orderby' => 'post_date' );
			$args1 = array( 'include' => $nivo_id_list, 'orderby' => 'post_date' );

			$post_slider_items = get_posts( $args );
			$page_slider_items = get_pages( $args1 );
			$slider_items = array_merge($post_slider_items, $page_slider_items);
		}

		if(count($slider_items) == 0) {
			echo "<div>No items to show. Please select valid content/ID's that contain some items!</div>";
		}
		else {
			// There is at least one post to show
			global $pc_is_front_page;
			global $pc_post_id; // need the post id to check for the sidebars per post/page feature
			global $wp_registered_sidebars; // global widget areas array
			global $pc_global_column_layout;

			// ***********************************************************
			// **     DETERMINE WIDGET AREA LOCATION AND WIDTH TYPE     **
			// ***********************************************************

			$widget_area = null;
			$sidebars_widgets = wp_get_sidebars_widgets();
			// Try to get the widget area the widget belongs to
			foreach ( $sidebars_widgets as $sidebar => $widgets ) {
				if ( 'wp_inactive_widgets' == $sidebar )
					continue; // Ignore inactive widgets
				if ( is_array($widgets) ) {
					if ( in_array( $this->id, $widgets ) ) {
						$widget_area = $sidebar;
					}
				}
			}

			// If widget instance exists in a widget area
			if( $widget_area ) {
				// find the widget area width
				if( isset($wp_registered_sidebars[$widget_area]['width']) ) {
					$widget_area_width = $wp_registered_sidebars[$widget_area]['width'];
					$width_default = 0; // for debugging
				}
				// if not found, default to normal (i.e. standard widget width)
				else {
					$widget_area_width = 'normal';
					$width_default = 1; // for debugging
				}
			}

			// *******************************************************
			// **     SET THE THUMBNAIL SIZE USED BY THE WIDGET     **
			// *******************************************************

			/* Add code to enter the correct classname, and slider thumbnail size, depending on the column layout and the widget area width. */
			if($widget_area_width == 'full') {
				$widget_area_class_width = "full";
				$slider_thumb_size = 'slider_'.PC_SLIDER_IMG_FULL_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT;
			}
			elseif ($widget_area_width == 'wide') {
				// check page layout to see if the page layout is twothirds, or full width
				$layout_num = (int)substr($pc_global_column_layout, 0, 1);
				if ($layout_num == 1) {
					// full width layout
					$widget_area_class_width = "full";
					$slider_thumb_size = 'slider_'.PC_SLIDER_IMG_FULL_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT;
				}
				elseif ($layout_num == 2) {
					// 2 column layout
					$widget_area_class_width = "twothirds";
					$slider_thumb_size = 'slider_'.PC_SLIDER_IMG_TWO_THIRDS_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT;
				}
				else {
					// else assume 3 column layout
					$widget_area_class_width = "third";

					$slider_thumb_size = 'slider_'.PC_SLIDER_IMG_TWO_THIRDS_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT;
				}
			}
			else {
				// assume widget area width is 'normal'
				$widget_area_class_width = "third";
				$slider_thumb_size = 'slider_'.PC_SLIDER_IMG_THIRD_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT;
			}

			// ********************************************
			// **     START - NIVO-SLIDER DEBUG INFO     **
			// ********************************************
		
			// Create a function to output nivo-slider debug info. Pass variables in as
			// an array. Only output variables passed to the function. Have a switch to
			// output to screen or Firebug console object (if it exists - i.e. the browser
			// may not be Firefox).

			$debug = 0; // set to 1 to turn on, 0 to turn off
			if( $debug ) {
				echo "<br /><br /><pre>";
				//print_r($pc_global_column_layout);
				//print_r($sidebars_widgets);
				//print_r($this);
				//print_r($wp_registered_sidebars);
				//print_r($wp_registered_widgets);
				echo "</pre>";

				echo "Front page test: [".$pc_is_front_page."]<br />";
				echo "Class ID Instance: ".$this->id."<br />";
				$id_number = (int)$this->number;
				echo "Class ID Instance #: ".$id_number."<br />";

				echo "Widget area name: {$widget_area}<br />";

				if( $width_default == 1 ) {
					echo "Widget area width (default): ".$widget_area_width."<br />";
				}
				else {
					echo "Widget area width: ".$widget_area_width."<br />";
				}

				echo "Widget area class width: {$widget_area_class_width}<br />";
				echo "Slider thumb size: {$slider_thumb_size}<br /><br />";

				//echo "<pre>".'<br />$slider_items: <br />';
				//print_r($slider_items);
				//echo "</pre>";

                //echo "1/3: ".PC_SLIDER_IMG_THIRD_WIDTH."<br />";
                //echo "2/3: ".PC_SLIDER_IMG_TWO_THIRDS_WIDTH."<br />";
                //echo "Full: ".PC_SLIDER_IMG_FULL_WIDTH."<br />";
                //echo "Height: ".PC_SLIDER_IMG_HEIGHT."<br />";
                //echo "Arrows: ".PC_SLIDER_NAV_ARROWS."<br />";

				?>

				<script language="JavaScript">
					console.warn("START DEBUG: Nivo Slider Widget Area [<?php echo $widget_area; ?>]");
					console.log("-> Class id instance: [<?php echo $this->id; ?>]");
					console.log("-> Class id instance #: [<?php echo $id_number; ?>]");
					console.log("-> Widget area class width: [<?php echo $widget_area_class_width; ?>]");
					console.log("-> Widget area width: [<?php echo $widget_area_width; ?>]");
					console.log("-> Widget area name: [<?php echo $widget_area; ?>]");
					console.warn("END DEBUG: Nivo Slider Widget Area [<?php echo $widget_area; ?>]");
				</script>

				<?php
			}
			// ******************************************
			// **     END - NIVO-SLIDER DEBUG INFO     **
			// ******************************************

			echo $before_widget;

			?>
			<div class="slider-wrapper <?php echo $widget_area_class_width; // 'third', 'twothirds', or 'full' ?>">
				<div class="slider nivoSlider slider_<?php echo $this->id; ?>">
				<?php
					$captions = "";
					$i=0;
				?>
				<?php foreach($slider_items as $slider_item) { // Loop through the featured items
					
					if( $i >= $featured_slider_items ) break; // break out of the foreach loop

					if ( $featured_item_type=="image_featured_item" ) {
						$featured_image = wp_get_attachment_image( (int)$slider_item, $slider_thumb_size );
						$permalink = "#";
					}
					else {
						// show full post/page image/link
						$featured_image = PC_Utility::theme_get_slider_image( $slider_item, true, $slider_thumb_size ); // If post has no featured image, a default one is returned
						$permalink = get_permalink($slider_item->ID);
						// store captions for posts here so we don't need two loops
						$captions .= "<div id=\"".$slider_item->ID."\" class=\"nivo-html-caption\">";
						$captions .= "<a href=\"".$permalink."\">";
						$captions .= $featured_image;
						$captions .= "</a><span class=\"top\">";
						$captions .= "<span class=\"slidertitle\"><a href=\"".$permalink."\">".strtoupper(get_the_title($slider_item->ID))."</a></span>";
						// @todo If this slider isn't deprecated then consider putting the content through the standard filter as follows: apply_filters( 'the_content', $slider_item->post_content ); Test this before/after to make sure normal output is unnaffected.
						$captions .= "<span class=\"sliderdate\">".get_the_time('F jS, Y', $slider_item->ID)."</span>".PC_Utility::n_words( wp_strip_all_tags( $slider_item->post_content), 165 );
						$captions .= "</span></div>";
					} ?>

					<?php

					if ( $featured_item_type!="image_featured_item" ) {	?>
						<a href="<?php echo $permalink; ?>">
							<?php echo $featured_image; ?>
						</a>
					<?php
					}
					else {
						echo $featured_image;
					}
					?>
					<?php $i++;	?>
				<?php } // end foreach
				?>

				</div> <!-- .slider .nivoSlider -->

				<?php echo $captions; ?>

			</div><!-- .slider-wrapper -->

			<?php echo $after_widget; ?>

		<?php
		}
		// END SLIDER
	}

	/**
	 * Set Nivo slider image sizes.
	 *
	 * @since 0.1.0
	 */
	public function set_slider_image_sizes() {
		add_image_size( 'slider_'.PC_SLIDER_IMG_THIRD_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT, PC_SLIDER_IMG_THIRD_WIDTH, PC_SLIDER_IMG_HEIGHT, true ); // 1/3 Nivo slider widget size
		add_image_size( 'slider_'.PC_SLIDER_IMG_TWO_THIRDS_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT, PC_SLIDER_IMG_TWO_THIRDS_WIDTH, PC_SLIDER_IMG_HEIGHT, true ); // 2/3 Nivo slider widget size
		add_image_size( 'slider_'.PC_SLIDER_IMG_FULL_WIDTH.'x'.PC_SLIDER_IMG_HEIGHT, PC_SLIDER_IMG_FULL_WIDTH, PC_SLIDER_IMG_HEIGHT, true ); // Full Nivo slider widget size
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