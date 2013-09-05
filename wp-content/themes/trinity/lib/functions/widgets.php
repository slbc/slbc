<?php

/*
Widget Name: List Posts Widget
Description: Display a list of posts. Supports multiple usage.
Author: ChurchThemes
Author URI: http://churchthemes.net
*/

add_action('init', 'post_list_widget');
function post_list_widget() {

	$prefix = 'post-list'; // $id prefix
	$name = __('List Posts');
	$widget_ops = array('classname' => 'post_list', 'description' => __('Display a list of posts that match a certain criteria and order them however you like. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('post_list');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'post_list', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'post_list_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'post_list', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'post_list_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function post_list($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('post-list-', '', @$widget_id);
    $options = get_option('post_list');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags
		echo $before_widget;

		// print content and widget end tags
		$title = stripslashes($vars['title']);
		$num = $vars['num'];
		if(empty($num)) $num = 3;
		$order_by = $vars['order_by'];
		$the_order = $vars['the_order'];
		$show_image = $vars['show_image'];
		$show_date = $vars['show_date'];
		$show_author = $vars['show_author'];
		$post_author = $vars['post_author'];
		$post_cat = $vars['post_cat'];
		$post_tag = $vars['post_tag'];
		if($post_tag == 0):
			$post_tag = null;
		elseif($post_tag):
			$the_tag = get_term_by('id', $post_tag, 'post_tag');
			$post_tag = $the_tag->slug;
		endif;

		global $post;

if($order_by == 'meta_value_num'):
		$args=array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'author' => $post_author,
			'cat' => $post_cat,
			'tag' => $post_tag,
			'meta_key' => 'Views',
			'orderby' => $order_by,
			'order' => $the_order,
		);
else:
		$args=array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'author' => $post_author,
			'cat' => $post_cat,
			'tag' => $post_tag,
			'orderby' => $order_by,
			'order' => $the_order,
		);
endif;

		$query = null;
		$query = new WP_Query($args);

		if($title):
			echo $before_title . $title . $after_title;
			echo "<ul class=\"list_widget\">\n";
			$i = 0;
			if( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); $i++;

				$the_title = strip_tags(get_the_title());
				$the_author = strip_tags(get_the_author());
				$the_thumb = get_thumbnail($post->ID,'80','80');

				if($query->post_count == 1):
					echo "<li class=\"first last\">\n";
				elseif($i == 1):
					echo "<li class=\"first\">\n";
				elseif($i == $query->post_count):
					echo "<li class=\"last\">\n";
				else:
					echo "<li>\n";
				endif;
					echo "<a href=\"".get_permalink()."\">";
				if($show_image == 'true' && !empty($the_thumb)):
					echo "<img src=\"".$the_thumb."\" alt=\"".$the_title."\">\n";
				endif;
				if($show_date == 'true' && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left\">".get_the_date()."</p>";
				elseif($show_date == 'true' && $show_image == 'true'):
					echo "<p>".get_the_date()."</p>";
				endif;
				if($show_image == 'false' || empty($the_thumb)):
					echo "<h5 class=\"left\">".$the_title."</h5>\n";
				else:
					echo "<h5>".$the_title."</h5>\n";
				endif;
				if($show_author == 'true' && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left notranslate\">".$the_author."</p>";
				elseif($show_author == 'true' && $show_image == 'true'):
					echo "<p class=\"notranslate\">".$the_author."</p>";
				endif;
				echo "</a>\n";
				echo "<div class=\"clear\"></div>\n";
				echo "</li>\n";
			endwhile; wp_reset_query();
			else:
				echo "<li><p class=\"left noresults\">Sorry, no posts found.</p></li>";
			endif;
				echo "</ul>\n";
				echo $after_widget;
		endif;
}

function post_list_control($args) {

	$prefix = 'post-list'; // $id prefix

	$options = get_option('post_list');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = post_list_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'post_list');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['title'];
	$num = @$opts['num'];
	$order_by = @$opts['order_by'];
	$the_order = @$opts['the_order'];
	$show_image = @$opts['show_image'];
	$show_date = @$opts['show_date'];
	$show_author = @$opts['show_author'];
	$post_author = @$opts['post_author'];
	$post_cat = @$opts['post_cat'];
	$post_tag = @$opts['post_tag'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]"><?php _e('Order By', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]">
			<option value="date"<?php if($order_by == 'date'): echo ' selected="selected"'; endif; ?>><?php _e('Post Date', 'churchthemes'); ?></option>
			<option value="title"<?php if($order_by == 'title'): echo ' selected="selected"'; endif; ?>><?php _e('Title', 'churchthemes'); ?></option>
			<option value="modified"<?php if($order_by == 'modified'): echo ' selected="selected"'; endif; ?>><?php _e('Date Modified', 'churchthemes'); ?></option>
			<option value="menu_order"<?php if($order_by == 'menu_order'): echo ' selected="selected"'; endif; ?>><?php _e('Menu Order', 'churchthemes'); ?></option>
			<option value="id"<?php if($order_by == 'id'): echo ' selected="selected"'; endif; ?>><?php _e('Post ID', 'churchthemes'); ?></option>
			<option value="rand"<?php if($order_by == 'rand'): echo ' selected="selected"'; endif; ?>><?php _e('Random', 'churchthemes'); ?></option>
			<option value="meta_value_num"<?php if($order_by == 'meta_value_num'): echo ' selected="selected"'; endif; ?>><?php _e('View Count', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]"><?php _e('Order', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]">
			<option value="DESC"<?php if($the_order == 'DESC'): echo ' selected="selected"'; endif; ?>><?php _e('Descending', 'churchthemes'); ?></option>
			<option value="ASC"<?php if($the_order == 'ASC'): echo ' selected="selected"'; endif; ?>><?php _e('Ascending', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]"><?php _e('Thumbnail Image', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]">
			<option value="true"<?php if($show_image == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_image == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_date]"><?php _e('Date', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_date]">
			<option value="true"<?php if($show_date == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_date == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_author]"><?php _e('Author', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_author]">
			<option value="true"<?php if($show_author == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_author == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>]"><?php _e('Display', 'churchthemes'); ?></label>
		<br />
		<?php wp_dropdown_users('show_option_all=All Authors&selected='.$post_author.'&show_count=1&orderby=display_name&who=authors&name='.$prefix.'['.$number.'][post_author]'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Categories&selected='.$post_cat.'&show_count=1&hierarchical=1&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][post_cat]'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Tags&selected='.$post_tag.'&show_count=1&hierarchical=0&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][post_tag]&taxonomy=post_tag'); ?>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][num]"><?php _e('Number of Posts', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][num]" size="2" placeholder="3" value="<?php echo stripslashes($num); ?>" />
		<br />
		<small><em><?php _e('Enter -1 to display unlimited results', 'churchthemes'); ?></em></small>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('post_list_update')){
	function post_list_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: Sermons List Widget
Description: Display a list of sermons. Supports multiple usage.
Author: ChurchThemes
Author URI: http://churchthemes.net
*/

add_action('init', 'sermon_list_widget');
function sermon_list_widget() {

	$prefix = 'sermon-list'; // $id prefix
	$name = __('List Sermons');
	$widget_ops = array('classname' => 'sermon_list', 'description' => __('Display a list of sermons that match a certain criteria and order them however you like. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('sermon_list');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'sermon_list', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'sermon_list_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'sermon_list', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'sermon_list_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function sermon_list($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('sermon-list-', '', @$widget_id);
    $options = get_option('sermon_list');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags
		echo $before_widget;

		// print content and widget end tags
		$title = stripslashes($vars['title']);
		$num = $vars['num'];
		if(empty($num)) $num = 3;
		$order_by = $vars['order_by'];
		$the_order = $vars['the_order'];
		$show_image = $vars['show_image'];
		$show_date = $vars['show_date'];
		$show_speaker = $vars['show_speaker'];
		$speaker = $vars['sermon_speaker'];
		if($speaker):
			$the_speaker = get_term_by('id', $speaker, 'sermon_speaker');
			$speaker = $the_speaker->slug;
		endif;
		$service = $vars['sermon_service'];
		if($service):
			$the_service = get_term_by('id', $service, 'sermon_service');
			$service = $the_service->slug;
		endif;
		$series = $vars['sermon_series'];
		if($series):
			$the_series = get_term_by('id', $series, 'sermon_series');
			$series = $the_series->slug;
		endif;
		$topic = $vars['sermon_topic'];
		if($topic):
			$the_topic = get_term_by('id', $topic, 'sermon_topic');
			$topic = $the_topic->slug;
		endif;

		global $post;

if($order_by == 'meta_value_num'):
		$args=array(
			'post_type' => 'ct_sermon',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'sermon_speaker' => $speaker,
			'sermon_service' => $service,
			'sermon_series' => $series,
			'sermon_topic' => $topic,
			'meta_key' => 'Views',
			'orderby' => $order_by,
			'order' => $the_order,
		);
else:
		$args=array(
			'post_type' => 'ct_sermon',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'sermon_speaker' => $speaker,
			'sermon_service' => $service,
			'sermon_series' => $series,
			'sermon_topic' => $topic,
			'orderby' => $order_by,
			'order' => $the_order,
		);
endif;

		$query = null;
		$query = new WP_Query($args);

		if($title):
			echo $before_title . $title . $after_title;
			echo "<ul class=\"list_widget\">\n";
			$i = 0;
			if( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); $i++;
				$sermon_speaker = get_the_term_list($post->ID, 'sermon_speaker', '', ' + ', '');
				$the_title = strip_tags(get_the_title());
				$the_thumb = get_thumbnail($post->ID,'80','80');

				if($query->post_count == 1):
					echo "<li class=\"first last\">\n";
				elseif($i == 1):
					echo "<li class=\"first\">\n";
				elseif($i == $query->post_count):
					echo "<li class=\"last\">\n";
				else:
					echo "<li>\n";
				endif;
				echo "<a href=\"".get_permalink()."\">";
				if($show_image == 'true' && !empty($the_thumb)):
					echo "<img src=\"".$the_thumb."\" alt=\"".$the_title."\">\n";
				endif;
				if($show_date == 'true' && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left\">".get_the_date()."</p>";
				elseif($show_date == 'true'):
					echo "<p>".get_the_date()."</p>";
				endif;
				if($show_image == 'false' || empty($the_thumb)):
					echo "<h5 class=\"left\">".$the_title."</h5>\n";
				else:
					echo "<h5>".$the_title."</h5>\n";
				endif;
				if($show_speaker == 'true' && !empty($sermon_speaker) && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left notranslate\">".strip_tags($sermon_speaker)."</p>";
				elseif($show_speaker == 'true' && !empty($sermon_speaker)):
					echo "<p class=\"notranslate\">".strip_tags($sermon_speaker)."</p>";
				endif;
				echo "</a>\n";
				echo "<div class=\"clear\"></div>\n";
				echo "</li>\n";
			endwhile; wp_reset_query();
			else:
				echo "<li><p class=\"left noresults\">Sorry, no sermons found.</p></li>";
			endif;
				echo "</ul>\n";
				echo $after_widget;
		endif;
}

function sermon_list_control($args) {

	$prefix = 'sermon-list'; // $id prefix

	$options = get_option('sermon_list');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = sermon_list_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'sermon_list');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['title'];
	$num = @$opts['num'];
	$order_by = @$opts['order_by'];
	$the_order = @$opts['the_order'];
	$show_image = @$opts['show_image'];
	$show_date = @$opts['show_date'];
	$show_speaker = @$opts['show_speaker'];
	$speaker = @$opts['sermon_speaker'];
	$service = @$opts['sermon_service'];
	$series = @$opts['sermon_series'];
	$topic = @$opts['sermon_topic'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]"><?php _e('Order By', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]">
			<option value="date"<?php if($order_by == 'date'): echo ' selected="selected"'; endif; ?>><?php _e('Post Date', 'churchthemes'); ?></option>
			<option value="title"<?php if($order_by == 'title'): echo ' selected="selected"'; endif; ?>><?php _e('Title', 'churchthemes'); ?></option>
			<option value="modified"<?php if($order_by == 'modified'): echo ' selected="selected"'; endif; ?>><?php _e('Date Modified', 'churchthemes'); ?></option>
			<option value="menu_order"<?php if($order_by == 'menu_order'): echo ' selected="selected"'; endif; ?>><?php _e('Menu Order', 'churchthemes'); ?></option>
			<option value="id"<?php if($order_by == 'id'): echo ' selected="selected"'; endif; ?>><?php _e('Post ID', 'churchthemes'); ?></option>
			<option value="rand"<?php if($order_by == 'rand'): echo ' selected="selected"'; endif; ?>><?php _e('Random', 'churchthemes'); ?></option>
			<option value="meta_value_num"<?php if($order_by == 'meta_value_num'): echo ' selected="selected"'; endif; ?>><?php _e('View Count', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]"><?php _e('Order', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]">
			<option value="DESC"<?php if($the_order == 'DESC'): echo ' selected="selected"'; endif; ?>><?php _e('Descending', 'churchthemes'); ?></option>
			<option value="ASC"<?php if($the_order == 'ASC'): echo ' selected="selected"'; endif; ?>><?php _e('Ascending', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]"><?php _e('Thumbnail Image', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]">
			<option value="true"<?php if($show_image == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_image == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_date]"><?php _e('Date', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_date]">
			<option value="true"<?php if($show_date == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_date == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_speaker]"><?php _e('Speaker', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_speaker]">
			<option value="true"<?php if($show_speaker == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_speaker == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>]"><?php _e('Display', 'churchthemes'); ?></label>
		<br />
		<?php wp_dropdown_categories('show_option_all=All Speakers&selected='.$speaker.'&show_count=1&hierarchical=0&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][sermon_speaker]&taxonomy=sermon_speaker'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Services&selected='.$service.'&show_count=1&hierarchical=1&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][sermon_service]&taxonomy=sermon_service'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Series&selected='.$series.'&show_count=1&hierarchical=1&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][sermon_series]&taxonomy=sermon_series'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Topics&selected='.$topic.'&show_count=1&hierarchical=0&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][sermon_topic]&taxonomy=sermon_topic'); ?>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][num]"><?php _e('Number of Sermons', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][num]" size="2" placeholder="3" value="<?php echo stripslashes($num); ?>" />
		<br />
		<small><em><?php _e('Enter -1 to display unlimited results', 'churchthemes'); ?></em></small>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('sermon_list_update')){
	function sermon_list_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: People List Widget
Description: Display a list of people. Supports multiple usage.
Author: ChurchThemes
Author URI: http://churchthemes.net
*/

add_action('init', 'people_list_widget');
function people_list_widget() {

	$prefix = 'people-list'; // $id prefix
	$name = __('List People');
	$widget_ops = array('classname' => 'people_list', 'description' => __('Display a list of people that match a certain criteria and order them however you like. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('people_list');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'people_list', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'people_list_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'people_list', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'people_list_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function people_list($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('people-list-', '', @$widget_id);
    $options = get_option('people_list');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags
		echo $before_widget;

		// print content and widget end tags
		$title = stripslashes($vars['title']);
		$num = $vars['num'];
		if(empty($num)) $num = 3;
		$order_by = $vars['order_by'];
		$the_order = $vars['the_order'];
		$show_image = $vars['show_image'];
		$show_role = $vars['show_role'];
		$show_email = $vars['show_email'];
		$category = $vars['person_category'];
		if($category):
			$the_category = get_term_by('id', $category, 'person_category');
			$category = $the_category->slug;
		endif;
		$tag = $vars['person_tag'];
		if($tag):
			$the_tag = get_term_by('id', $tag, 'person_tag');
			$tag = $the_tag->slug;
		endif;
		$role = "_ct_ppl_role";
		$emailaddress = "_ct_ppl_emailaddress";

		global $post;

if($order_by == 'meta_value_num'):
		$args=array(
			'post_type' => 'ct_person',
			'post_status' => 'publish',
			'posts_per_page' => $num,
			'meta_key' => 'Views',
			'orderby' => $order_by,
			'order' => $the_order,
			'person_category' => $category,
			'person_tag' => $tag,
		);
else:
		$args=array(
			'post_type' => 'ct_person',
			'post_status' => 'publish',
			'posts_per_page' => $num,
			'orderby' => $order_by,
			'order' => $the_order,
			'person_category' => $category,
			'person_tag' => $tag,
		);
endif;

		$query = null;
		$query = new WP_Query($args);

		if($title):
			echo $before_title . $title . $after_title;
			echo "<ul class=\"list_widget\">\n";
			$i = 0;
			if($query->have_posts()): while($query->have_posts()): $query->the_post(); $i++;

				$the_title = strip_tags(get_the_title());
				$role = get_post_meta($post->ID, '_ct_ppl_role', true);
				$emailaddress = get_post_meta($post->ID, '_ct_ppl_emailaddress', true);
				$the_thumb = get_thumbnail($post->ID,'80','80');

				if($query->post_count == 1):
					echo "<li class=\"first last\">\n";
				elseif($i == 1):
					echo "<li class=\"first\">\n";
				elseif($i == $query->post_count):
					echo "<li class=\"last\">\n";
				else:
					echo "<li>\n";
				endif;
				echo "<a href=\"".get_permalink()."\">";
				if($show_image == 'true' && !empty($the_thumb)):
					echo "<img src=\"".$the_thumb."\" alt=\"".$the_title."\">\n";
				endif;
				if($show_role == 'true' && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left\">".$role."</p>";
				elseif($show_role == 'true'):
					echo "<p>".$role."</p>";
				endif;
				if($show_image == 'false' || empty($the_thumb)):
					echo "<h5 class=\"left\">".$the_title."</h5>\n";
				else:
					echo "<h5>".$the_title."</h5>\n";
				endif;
				if($show_email == 'true' && ($show_image == 'false' || empty($the_thumb))):
					echo "<p class=\"left notranslate\"><a href=\"mailto:".$emailaddress."\">".$emailaddress."</a></p>";
				elseif($show_email == 'true'):
					echo "<p class=\"notranslate\"><a href=\"mailto:".$emailaddress."\">".$emailaddress."</a></p>";
				endif;
				echo "</a>\n";
				echo "<div class=\"clear\"></div>\n";
				echo "</li>\n";
			endwhile;
			else:
				echo "<li><p class=\"left noresults\">Sorry, no people found.</p></li>";
			endif;
				echo "</ul>\n";
				echo $after_widget;
		endif;
}

function people_list_control($args) {

	$prefix = 'people-list'; // $id prefix

	$options = get_option('people_list');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = people_list_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'people_list');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['title'];
	$num = @$opts['num'];
	$order_by = @$opts['order_by'];
	$the_order = @$opts['the_order'];
	$show_image = @$opts['show_image'];
	$show_role = @$opts['show_role'];
	$show_email = @$opts['show_email'];
	$category = @$opts['person_category'];
	$tag = @$opts['person_tag'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]"><?php _e('Order By', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]">
			<option value="date"<?php if($order_by == 'date'): echo ' selected="selected"'; endif; ?>><?php _e('Post Date', 'churchthemes'); ?></option>
			<option value="title"<?php if($order_by == 'title'): echo ' selected="selected"'; endif; ?>><?php _e('Title', 'churchthemes'); ?></option>
			<option value="modified"<?php if($order_by == 'modified'): echo ' selected="selected"'; endif; ?>><?php _e('Date Modified', 'churchthemes'); ?></option>
			<option value="menu_order"<?php if($order_by == 'menu_order'): echo ' selected="selected"'; endif; ?>><?php _e('Menu Order', 'churchthemes'); ?></option>
			<option value="id"<?php if($order_by == 'id'): echo ' selected="selected"'; endif; ?>><?php _e('Post ID', 'churchthemes'); ?></option>
			<option value="rand"<?php if($order_by == 'rand'): echo ' selected="selected"'; endif; ?>><?php _e('Random', 'churchthemes'); ?></option>
			<option value="meta_value_num"<?php if($order_by == 'meta_value_num'): echo ' selected="selected"'; endif; ?>><?php _e('View Count', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]"><?php _e('Order', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]">
			<option value="DESC"<?php if($the_order == 'DESC'): echo ' selected="selected"'; endif; ?>><?php _e('Descending', 'churchthemes'); ?></option>
			<option value="ASC"<?php if($the_order == 'ASC'): echo ' selected="selected"'; endif; ?>><?php _e('Ascending', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]"><?php _e('Thumbnail Image', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_image]">
			<option value="true"<?php if($show_image == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_image == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_role]"><?php _e('Role', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_role]">
			<option value="true"<?php if($show_role == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_role == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_email]"><?php _e('Email Address', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_email]">
			<option value="true"<?php if($show_email == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_email == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>]"><?php _e('Display', 'churchthemes'); ?></label>
		<br />
		<?php wp_dropdown_categories('show_option_all=All Categories&selected='.$category.'&show_count=1&hierarchical=1&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][person_category]&taxonomy=person_category'); ?>
	</p>
	<p>
		<?php wp_dropdown_categories('show_option_all=All Tags&selected='.$tag.'&show_count=1&hierarchical=0&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][person_tag]&taxonomy=person_tag'); ?>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][num]"><?php _e('Number of People', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][num]" size="2" placeholder="3" value="<?php echo stripslashes($num); ?>" />
		<br />
		<small><em><?php _e('Enter -1 to display unlimited results', 'churchthemes'); ?></em></small>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('people_list_update')){
	function people_list_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: Locations List Widget
Description: Display a list of locations. Supports multiple usage.
Author: ChurchThemes
Author URI: http://churchthemes.net
*/

add_action('init', 'location_list_widget');
function location_list_widget() {

	$prefix = 'location_list'; // $id prefix
	$name = __('List Locations');
	$widget_ops = array('classname' => 'location_list', 'description' => __('Display a list of locations that match a certain criteria and order them however you like. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('location_list');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'location_list', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'location_list_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'location_list', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'location_list_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function location_list($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('location_list-', '', @$widget_id);
    $options = get_option('location_list');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags
		echo $before_widget;

		// print content and widget end tags
		$title = stripslashes($vars['title']);
		$num = $vars['num'];
		if(empty($num)) $num = 3;
		$order_by = $vars['order_by'];
		$the_order = $vars['the_order'];
		$show_address = $vars['show_address'];
		$show_service_times = $vars['show_service_times'];
		$link_to = $vars['link_to'];
		$tag = $vars['tag'];
		if($tag):
			$the_tag = get_term_by('id', $tag, 'location_tag');
			$tag = $the_tag->slug;
		endif;

		global $post;

if($order_by == 'meta_value_num'):
		$args=array(
			'post_type' => 'ct_location',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'location_tag' => $tag,
			'meta_key' => 'Views',
			'orderby' => $order_by,
			'order' => $the_order,
		);
else:
		$args=array(
			'post_type' => 'ct_location',
			'post_status' => 'publish',
			'paged' => true,
			'p' => $id,
			'posts_per_page' => $num,
			'location_tag' => $tag,
			'orderby' => $order_by,
			'order' => $the_order,
		);
endif;

		$query = null;
		$query = new WP_Query($args);



		if($title):
			echo $before_title . $title . $after_title;
			echo "<ul class=\"list_locations notranslate\">\n";
			$i = 0;
			if( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); $i++;

				$the_title = strip_tags(get_the_title());
				$loc_address1 = get_post_meta($post->ID, '_ct_loc_address1', true);
				$loc_address2 = get_post_meta($post->ID, '_ct_loc_address2', true);
				$loc_address3 = get_post_meta($post->ID, '_ct_loc_address3', true);
				$loc_map_link = get_post_meta($post->ID, '_ct_loc_map_link', true);
				$loc_service1 = get_post_meta($post->ID, '_ct_loc_service1', true);
				$loc_service2 = get_post_meta($post->ID, '_ct_loc_service2', true);
				$loc_service3 = get_post_meta($post->ID, '_ct_loc_service3', true);
				$loc_service4 = get_post_meta($post->ID, '_ct_loc_service4', true);
				$loc_service5 = get_post_meta($post->ID, '_ct_loc_service5', true);

				if($query->post_count == 1):
					echo "<li class=\"first last\">\n";
				elseif($i == 1):
					echo "<li class=\"first\">\n";
				elseif($i == $query->post_count):
					echo "<li class=\"last\">\n";
				else:
					echo "<li>\n";
				endif;
				if($link_to == 'post'):
					echo "<a href=\"".get_permalink()."\" class=\"link\">info</a>\n";
				elseif($link_to == 'map' && $loc_map_link):
					echo "<a href=\"".$loc_map_link."\" target=\"_blank\" class=\"link\">map</a>\n";
				endif;
				echo "<h5>".$the_title."</h5>";
				echo "<p>";
				if($show_address == 'true' && $loc_address1):
					echo $loc_address1;
					if($loc_address2):
						echo "<br />".$loc_address2;
					endif;
					if($loc_address3):
						echo "<br />".$loc_address3;
					endif;
				endif;
				echo "</p>\n";
				if($show_service_times == 'true' && $loc_service1):
					echo "<ul class=\"services\">";
					echo "<li>".$loc_service1."</li>";
					if($loc_service2):
						echo "<li>".$loc_service2."</li>";
					endif;
					if($loc_service3):
						echo "<li>".$loc_service3."</li>";
					endif;
					if($loc_service4):
						echo "<li>".$loc_service4."</li>";
					endif;
					if($loc_service5):
						echo "<li>".$loc_service5."</li>";
					endif;
					echo "</ul>";
				endif;
				echo "</li>\n";
			endwhile; wp_reset_query();
			else:
				echo "<li><p class=\"left noresults\">Sorry, no locations found.</p></li>";
			endif;
			echo "</ul>\n";
			echo $after_widget;
		endif;
}

function location_list_control($args) {

	$prefix = 'location_list'; // $id prefix

	$options = get_option('location_list');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = location_list_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'location_list');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['title'];
	$order_by = @$opts['order_by'];
	$the_order = @$opts['the_order'];
	$tag = @$opts['tag'];
	$show_address = @$opts['show_address'];
	$show_service_times = @$opts['show_service_times'];
	$num = @$opts['num'];
	$link_to = @$opts['link_to'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]"><?php _e('Order By', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]">
			<option value="date"<?php if($order_by == 'date'): echo ' selected="selected"'; endif; ?>><?php _e('Post Date', 'churchthemes'); ?></option>
			<option value="title"<?php if($order_by == 'title'): echo ' selected="selected"'; endif; ?>><?php _e('Title', 'churchthemes'); ?></option>
			<option value="modified"<?php if($order_by == 'modified'): echo ' selected="selected"'; endif; ?>><?php _e('Date Modified', 'churchthemes'); ?></option>
			<option value="menu_order"<?php if($order_by == 'menu_order'): echo ' selected="selected"'; endif; ?>><?php _e('Menu Order', 'churchthemes'); ?></option>
			<option value="id"<?php if($order_by == 'id'): echo ' selected="selected"'; endif; ?>><?php _e('Post ID', 'churchthemes'); ?></option>
			<option value="rand"<?php if($order_by == 'rand'): echo ' selected="selected"'; endif; ?>><?php _e('Random', 'churchthemes'); ?></option>
			<option value="meta_value_num"<?php if($order_by == 'meta_value_num'): echo ' selected="selected"'; endif; ?>><?php _e('View Count', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]"><?php _e('Order', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][the_order]">
			<option value="DESC"<?php if($the_order == 'DESC'): echo ' selected="selected"'; endif; ?>><?php _e('Descending', 'churchthemes'); ?></option>
			<option value="ASC"<?php if($the_order == 'ASC'): echo ' selected="selected"'; endif; ?>><?php _e('Ascending', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>]"><?php _e('Display', 'churchthemes'); ?></label>
		<br />
		<?php wp_dropdown_categories('show_option_all=All Tags&selected='.$tag.'&show_count=1&hierarchical=0&hide_empty=0&orderby=title&name='.$prefix.'['.$number.'][tag]&taxonomy=location_tag'); ?>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_address]"><?php _e('Address', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_address]">
			<option value="true"<?php if($show_address == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_address == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][show_service_times]"><?php _e('Service Times', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][show_service_times]">
			<option value="true"<?php if($show_service_times == 'true'): echo ' selected="selected"'; endif; ?>><?php _e('Show', 'churchthemes'); ?></option>
			<option value="false"<?php if($show_service_times == 'false'): echo ' selected="selected"'; endif; ?>><?php _e('Hide', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][num]"><?php _e('Number of Locations', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][num]" size="2" placeholder="3" value="<?php echo stripslashes($num); ?>" />
		<br />
		<small><em><?php _e('Enter -1 to display unlimited results', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][link_to]"><?php _e('Link To', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][link_to]">
			<option value="post"<?php if($link_to == 'post'): echo ' selected="selected"'; endif; ?>><?php _e('Location Post', 'churchthemes'); ?></option>
			<option value="map"<?php if($link_to == 'map'): echo ' selected="selected"'; endif; ?>><?php _e('Map', 'churchthemes'); ?></option>
			<option value="none"<?php if($link_to == 'none'): echo ' selected="selected"'; endif; ?>><?php _e('- No Link -', 'churchthemes'); ?></option>
		</select>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('location_list_update')){
	function location_list_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: Online Giving (PayPal)
Description: Display an area where users can give/tithe online through PayPal. Supports multiple usage.
Author: ChurchThemes
Version: 1.0
Author URI: http://churchthemes.net
*/

add_action('init', 'giving_paypal_widget');
function giving_paypal_widget() {

	$prefix = 'giving-paypal'; // $id prefix
	$name = __('Online Giving (PayPal)');
	$widget_ops = array('classname' => 'giving_paypal', 'description' => __('Display an area where users can give/tithe online through PayPal. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('giving_paypal');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'giving_paypal', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'giving_paypal_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'giving_paypal', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'giving_paypal_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function giving_paypal($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('giving-paypal-', '', @$widget_id);
    $options = get_option('giving_paypal');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags

		// print content and widget end tags
		$title = stripslashes($vars['paypal_title']);
		$paypal_description =  stripslashes($vars['paypal_description']);
		$paypal_email = $vars['paypal_email'];
		$paypal_transaction_name = $vars['paypal_transaction_name'];
		$paypal_currency_mode = $vars['paypal_currency_mode'];
		$paypal_default_amount = $vars['paypal_default_amount'];
		$paypal_button_text = $vars['paypal_button_text'];
		$paypal_logo_style = $vars['paypal_logo_style'];

		if($title && $paypal_email):
			echo $before_widget;
			echo $before_title . $title . $after_title;
			echo "<div class=\"give_paypal\">\n";
			echo "<p>".$paypal_description."</p>\n";
			echo "<form name=\"_donations\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">\n";
			echo "	<input type=\"hidden\" name=\"cmd\" value=\"_donations\">\n";
			echo "	<input type=\"hidden\" name=\"business\" value=\"".$paypal_email."\">\n";
			echo "	<input type=\"hidden\" name=\"item_name\" value=\"".$paypal_transaction_name."\">\n";
			echo "	<input type=\"hidden\" name=\"currency_code\" value=\"".$paypal_currency_mode."\">\n";
			if($paypal_currency_mode == 'USD' || $paypal_currency_mode == 'AUD' || $paypal_currency_mode == 'CAD' || $paypal_currency_mode == 'HKD' || $paypal_currency_mode == 'MXN' || $paypal_currency_mode == 'NZD' || $paypal_currency_mode == 'SGD'): echo '$';
			elseif($paypal_currency_mode == 'GBP'): echo '&pound;';
			elseif($paypal_currency_mode == 'CZK'): echo 'K&#269;';
			elseif($paypal_currency_mode == 'DKK' || $paypal_currency_mode == 'NOK' || $paypal_currency_mode == 'SEK'): echo 'kr';
			elseif($paypal_currency_mode == 'EUR'): echo '&euro;';
			elseif($paypal_currency_mode == 'HUF'): echo 'Ft';
			elseif($paypal_currency_mode == 'ILS'): echo '&#8362;';
			elseif($paypal_currency_mode == 'JPY'): echo '&yen;';
			elseif($paypal_currency_mode == 'PHP'): echo 'Php';
			elseif($paypal_currency_mode == 'PLN'): echo 'z&#322;';
			elseif($paypal_currency_mode == 'CHF'): echo 'CHF';
			elseif($paypal_currency_mode == 'TWD'): echo 'NT$';
			elseif($paypal_currency_mode == 'THB'): echo '&#3647;';
			endif;
			echo " ";
			echo "<input type=\"text\" name=\"amount\" class=\"amount\" size=\"10\" value=\"".$paypal_default_amount."\">\n";
		if($paypal_button_text):
			echo "<input type=\"submit\" class=\"button\" name=\"submit\" value=\"".$paypal_button_text."\">\n";
		else:
			echo "<input type=\"submit\" class=\"button\" name=\"submit\" value=\"Donate\">\n";
		endif;
			echo "</form>\n";
			if($paypal_logo_style != 'false'):
				echo "<div class=\"branding\">\n";
			endif;
			if($paypal_logo_style == 'paypal_with_methods'):
				echo "<a href=\"https://www.paypal.com/us/mrb/pal=WC5EWXNR7VAXS\" target=\"_blank\"><img src=\"".get_template_directory_uri()."/images/paypal_with_methods.png\" alt=\"PayPal\" title=\"Powered by PayPal\"></a>\n";
			elseif($paypal_logo_style == 'paypal_logo'):
				echo "<a href=\"https://www.paypal.com/us/mrb/pal=WC5EWXNR7VAXS\" target=\"_blank\"><img src=\"".get_template_directory_uri()."/images/paypal.png\" alt=\"PayPal\" title=\"Powered by PayPal\"></a>\n";
			endif;
			if($paypal_logo_style != 'false'):
				echo "</div>\n";
			endif;
			echo "</div>";
			echo $after_widget;
		endif;
}

function giving_paypal_control($args) {

	$prefix = 'giving-paypal'; // $id prefix

	$options = get_option('giving_paypal');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = giving_paypal_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'giving_paypal');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['paypal_title'];
	$paypal_description = @$opts['paypal_description'];
	$paypal_email = @$opts['paypal_email'];
	$paypal_transaction_name = @$opts['paypal_transaction_name'];
	$paypal_currency_mode = @$opts['paypal_currency_mode'];
	$paypal_default_amount = @$opts['paypal_default_amount'];
	$paypal_button_text = @$opts['paypal_button_text'];
	$paypal_logo_style = @$opts['paypal_logo_style'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_description]"><?php _e('Description', 'churchthemes'); ?></label>
		<br />
		<textarea name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_description]" rows="5" cols="26" placeholder="Reasons to give, Biblical references, etc."><?php echo stripslashes($paypal_description); ?></textarea>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_email]"><?php _e('PayPal Email', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_email]" placeholder="user@domain.com" value="<?php echo $paypal_email; ?>" class="widefat<?php if(empty($paypal_email)): echo ' error'; endif; ?>" />
		<br />
		<small><em><?php _e('This must be a valid PayPal account ID', 'churchthemes'); ?></em><br /><a href="https://www.paypal.com/us/mrb/pal=WC5EWXNR7VAXS" target="_blank"><?php _e('Get a Free Merchant Account', 'churchthemes'); ?></a></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_transaction_name]"><?php _e('Purpose', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_transaction_name]" placeholder="General Fund" value="<?php echo $paypal_transaction_name; ?>" />
		<br />
		<small><em><?php _e('Leaving this blank will require the user to state the purpose of the funds during checkout on PayPal\'s website', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_currency_mode]"><?php _e('Currency Mode', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_currency_mode]">
			<option value="USD"<?php if($paypal_currency_mode == 'USD'): echo ' selected="selected"'; endif; ?>><?php _e('US Dollar', 'churchthemes'); ?></option>
			<option value="GBP"<?php if($paypal_currency_mode == 'GBP'): echo ' selected="selected"'; endif; ?>><?php _e('Pound Sterling', 'churchthemes'); ?></option>
			<option value="AUD"<?php if($paypal_currency_mode == 'AUD'): echo ' selected="selected"'; endif; ?>><?php _e('Australian Dollar', 'churchthemes'); ?></option>
			<option value="CAD"<?php if($paypal_currency_mode == 'CAD'): echo ' selected="selected"'; endif; ?>><?php _e('Canadian Dollar', 'churchthemes'); ?></option>
			<option value="CZK"<?php if($paypal_currency_mode == 'CZK'): echo ' selected="selected"'; endif; ?>><?php _e('Czech Koruna', 'churchthemes'); ?></option>
			<option value="DKK"<?php if($paypal_currency_mode == 'DKK'): echo ' selected="selected"'; endif; ?>><?php _e('Danish Krone', 'churchthemes'); ?></option>
			<option value="EUR"<?php if($paypal_currency_mode == 'EUR'): echo ' selected="selected"'; endif; ?>><?php _e('Euros', 'churchthemes'); ?></option>
			<option value="HKD"<?php if($paypal_currency_mode == 'HKD'): echo ' selected="selected"'; endif; ?>><?php _e('Hong Kong Dollar', 'churchthemes'); ?></option>
			<option value="HUF"<?php if($paypal_currency_mode == 'HUF'): echo ' selected="selected"'; endif; ?>><?php _e('Hungarian Forint', 'churchthemes'); ?></option>
			<option value="ILS"<?php if($paypal_currency_mode == 'ILS'): echo ' selected="selected"'; endif; ?>><?php _e('Israeli New Sheqel', 'churchthemes'); ?></option>
			<option value="JPY"<?php if($paypal_currency_mode == 'JPY'): echo ' selected="selected"'; endif; ?>><?php _e('Japanese Yen', 'churchthemes'); ?></option>
			<option value="MXN"<?php if($paypal_currency_mode == 'MXN'): echo ' selected="selected"'; endif; ?>><?php _e('Mexican Peso', 'churchthemes'); ?></option>
			<option value="NOK"<?php if($paypal_currency_mode == 'NOK'): echo ' selected="selected"'; endif; ?>><?php _e('Norwegian Krone', 'churchthemes'); ?></option>
			<option value="NZD"<?php if($paypal_currency_mode == 'NZD'): echo ' selected="selected"'; endif; ?>><?php _e('New Zealand Dollar', 'churchthemes'); ?></option>
			<option value="PHP"<?php if($paypal_currency_mode == 'PHP'): echo ' selected="selected"'; endif; ?>><?php _e('Philippine Peso', 'churchthemes'); ?></option>
			<option value="PLN"<?php if($paypal_currency_mode == 'PLN'): echo ' selected="selected"'; endif; ?>><?php _e('Polish Zloty', 'churchthemes'); ?></option>
			<option value="SGD"<?php if($paypal_currency_mode == 'SGD'): echo ' selected="selected"'; endif; ?>><?php _e('Singapore Dollar', 'churchthemes'); ?></option>
			<option value="SEK"<?php if($paypal_currency_mode == 'SEK'): echo ' selected="selected"'; endif; ?>><?php _e('Swedish Krona', 'churchthemes'); ?></option>
			<option value="CHF"<?php if($paypal_currency_mode == 'CHF'): echo ' selected="selected"'; endif; ?>><?php _e('Swiss Franc', 'churchthemes'); ?></option>
			<option value="TWD"<?php if($paypal_currency_mode == 'TWD'): echo ' selected="selected"'; endif; ?>><?php _e('Taiwan New Dollar', 'churchthemes'); ?></option>
			<option value="THB"<?php if($paypal_currency_mode == 'THB'): echo ' selected="selected"'; endif; ?>><?php _e('Thai Baht', 'churchthemes'); ?></option>
		</select>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_default_amount]"><?php _e('Default Amount', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_default_amount]" size="6" placeholder="25" value="<?php echo $paypal_default_amount; ?>" /> <small><em><?php _e('This field is optional', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_button_text]"><?php _e('Button Text', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_button_text]" placeholder="Give" value="<?php echo $paypal_button_text; ?>" class="widefat" />
		<br />
		<small><em><?php _e('Leaving this blank will default to: Donate', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_logo_style]"><?php _e('Logo Style', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][paypal_logo_style]">
			<option value="false"<?php if($paypal_logo_style == 'false') { echo ' selected="selected"'; } ?>><?php _e('- None -', 'churchthemes'); ?></option>
			<option value="paypal_with_methods"<?php if($paypal_logo_style == 'paypal_with_methods' || $paypal_logo_style == '') { echo ' selected="selected"'; } ?>><?php _e('PayPal + Payment Methods', 'churchthemes'); ?></option>
			<option value="paypal_logo"<?php if($paypal_logo_style == 'paypal_logo') { echo ' selected="selected"'; } ?>><?php _e('PayPal Logo', 'churchthemes'); ?></option>
		</select>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('giving_paypal_update')){
	function giving_paypal_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: Online Giving (EasyTithe)
Description: Display an area where users can give/tithe online through EasyTithe. Supports multiple usage.
Author: ChurchThemes
Version: 1.0
Author URI: http://churchthemes.net
*/

add_action('init', 'giving_easytithe_widget');
function giving_easytithe_widget() {

	$prefix = 'giving-easytithe'; // $id prefix
	$name = __('Online Giving (EasyTithe)');
	$widget_ops = array('classname' => 'giving_easytithe', 'description' => __('Display a button to launch EasyTithe where users can give/tithe online. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('giving_easytithe');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'giving_easytithe', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'giving_easytithe_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'giving_easytithe', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'giving_easytithe_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function giving_easytithe($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('giving-easytithe-', '', @$widget_id);
    $options = get_option('giving_easytithe');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags
		echo $before_widget;

		// print content and widget end tags
		$title = stripslashes($vars['title']);
		$description =  stripslashes($vars['description']);
		$easytithe_id = $vars['easytithe_id'];
		$button_text = $vars['button_text'];
		$logo_style = $vars['logo_style'];

		if($title && $easytithe_id):
			echo $before_title . $title . $after_title;
			echo "	<div class=\"give_tithe_widget\">\n";
			echo "		<p>".$description."</p>\n";
			echo "		<script type=\"text/javascript\" src=\"http://www.easytithe.com/sys.js\"></script>\n";
		if($button_text):
			echo "		<a href=\"javascript:popEasyTithe('".$easytithe_id."')\" class=\"button\">".$button_text."</a>\n";
		else:
			echo "		<a href=\"javascript:popEasyTithe('".$easytithe_id."')\" class=\"button\">Launch Secure Giving</a>\n";
		endif;
		if($logo_style == 'easytithe_with_methods'):
			echo "			<p><br /><a href=\"https://www.easytithe.com/signup/?r=livi1941\" target=\"_blank\"><img src=\"".get_template_directory_uri()."/images/easytithe_with_methods.png\" alt=\"EasyTithe\" title=\"Powered by EasyTithe\"></a></p>\n";
		elseif($logo_style == 'easytithe_logo'):
			echo "			<p><br /><a href=\"https://www.easytithe.com/signup/?r=livi1941\" target=\"_blank\"><img src=\"".get_template_directory_uri()."/images/easytithe.png\" alt=\"EasyTithe\" title=\"Powered by EasyTithe\"></a></p>\n";
		endif;
			echo "	</div>";
			echo $after_widget;
		endif;
}

function giving_easytithe_control($args) {

	$prefix = 'giving-easytithe'; // $id prefix

	$options = get_option('giving_easytithe');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = giving_easytithe_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'giving_easytithe');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['title'];
	$description = @$opts['description'];
	$easytithe_id = @$opts['easytithe_id'];
	$button_text = @$opts['button_text'];
	$logo_style = @$opts['logo_style'];

	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($title); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][description]"><?php _e('Description', 'churchthemes'); ?></label>
		<br />
		<textarea name="<?php echo $prefix; ?>[<?php echo $number; ?>][description]" rows="5" cols="26" placeholder="Reasons to give, Biblical references, etc."><?php echo stripslashes($description); ?></textarea>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][easytithe_id]"><?php _e('Account ID', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][easytithe_id]" placeholder="urch1941" value="<?php echo $easytithe_id; ?>" class="widefat<?php if(empty($easytithe_id)): echo ' error'; endif; ?>" />
		<br />
		<small><em><?php _e('This must be a valid EasyTithe account ID', 'churchthemes'); ?></em><br /><a href="https://www.easytithe.com/signup/?r=livi1941" target="_blank"><?php _e('Try EasyTithe', 'churchthemes'); ?></a></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][button_text]"><?php _e('Button Text', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][button_text]" placeholder="Launch Secure Giving" value="<?php echo $button_text; ?>" class="widefat" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][logo_style]"><?php _e('Logo Style', 'churchthemes'); ?></label>
		<br />
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][logo_style]">
			<option value="false"<?php if($logo_style == 'false') { echo ' selected="selected"'; } ?>><?php _e('- None -', 'churchthemes'); ?></option>
			<option value="easytithe_with_methods"<?php if($logo_style == 'easytithe_with_methods' || $logo_style == '') { echo ' selected="selected"'; } ?>><?php _e('EasyTithe + Payment Methods', 'churchthemes'); ?></option>
			<option value="easytithe_logo"<?php if($logo_style == 'easytithe_logo') { echo ' selected="selected"'; } ?>><?php _e('EasyTithe Logo', 'churchthemes'); ?></option>
		</select>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('giving_easytithe_update')){
	function giving_easytithe_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}


/*
Widget Name: Twitter Feed
Description: Display your public tweets. Supports multiple usage.
Author: ChurchThemes
Version: 1.0
Author URI: http://churchthemes.net
*/

add_action('init', 'twitter_feed_widget');
function twitter_feed_widget() {

	$prefix = 'twitter-feed'; // $id prefix
	$name = __('Twitter Feed');
	$widget_ops = array('classname' => 'twitter_feed', 'description' => __('Displays recent tweets from a specified twitter account. Supports multiple usage.'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);

	$options = get_option('twitter_feed');
	if(isset($options[0])) unset($options[0]);

	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'ct_twitter_feed', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'ct_twitter_feed_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'ct_twitter_feed', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'ct_twitter_feed_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function ct_twitter_feed($args, $vars = array()) {
    extract($args);
    $widget_number = (int)str_replace('twitter-feed-', '', @$widget_id);
    $options = get_option('twitter_feed');
    if(!empty($options[$widget_number])){
    	$vars = $options[$widget_number];
    }
    // widget open tags

	// print content and widget end tags
	$twitter_title = ( $vars['twitter_title'] ) ? $vars['twitter_title'] : null;
	$twitter_user = ( $vars['twitter_user'] ) ? $vars['twitter_user'] : null;
	$hide_retweets = ( $vars['twitter_rts'] == 'Yes' ) ? 0 : 1;
	$twitter_num = ( $vars['twitter_num'] ) ? $vars['twitter_num'] : 3;
	$twitter_followtext = ( $vars['twitter_followtext'] ) ? $vars['twitter_followtext'] : __( "Follow Us", 'churchthemes' );

	if ( $twitter_title && $twitter_user ) {
		echo $before_widget;
		echo $before_title . esc_html( $twitter_title ) . $after_title;
		echo '<ul id="twitter_feed" ></ul>';
		printf( '<script type="text/javascript" src="http://api.twitter.com/1/statuses/user_timeline.json?screen_name=%1$s&include_rts=%2$s&callback=churchthemes_twitter_callback&count=%3$s"></script>',
			esc_attr( $twitter_user ),
			intval( $hide_retweets ),
			intval( $twitter_num )
		);
		printf( '<div class="follow"><a href="http://twitter.com/%1$s" class="button">%2$s</a></div>',
			esc_attr( $twitter_user ),
			esc_html( $twitter_followtext )
		);
		echo $after_widget;
	}
}

function ct_twitter_feed_control($args) {

	$prefix = 'twitter-feed'; // $id prefix

	$options = get_option('twitter_feed');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;

			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}

		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}

		// clear unused options and update options in DB. return actual options array
		$options = ct_twitter_feed_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'twitter_feed');
	}

	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];

	// now we can output control
	$opts = @$options[$number];

	$title = @$opts['twitter_title'];
	$twitter_user = @$opts['twitter_user'];
	$twitter_rts = @$opts['twitter_rts'];
	$twitter_replies = @$opts['twitter_replies'];
	$twitter_num = ( @$opts['twitter_num'] ) ? @$opts['twitter_num'] : 3;
	$twitter_followtext = @$opts['twitter_followtext'];
	?>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_title]"><?php _e('Title', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_title]" value="<?php echo wp_filter_nohtml_kses( $title ); ?>" class="widefat<?php if(empty($title)): echo ' error'; endif; ?>" />
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_user]"><?php _e('Twitter Name', 'churchthemes'); ?> *</label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_user]" placeholder="awesomechurch" value="<?php echo wp_filter_nohtml_kses( $twitter_user ); ?>" class="widefat<?php if(empty($twitter_user)): echo ' error'; endif; ?>" />
		<br />
		<small><em><?php _e('Must be a valid Twitter username.', 'churchthemes'); ?></em><br /><a href="https://twitter.com/signup" target="_blank"><?php _e('Get a Twitter Account', 'churchthemes'); ?></a></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_rts]"><?php _e('Hide Retweets', 'churchthemes'); ?></label>
		<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_rts]">
			<option value="No"<?php if($twitter_rts == 'No'): echo ' selected="selected"'; endif; ?>><?php _e('No', 'churchthemes'); ?></option>
			<option value="Yes"<?php if($twitter_rts == 'Yes'): echo ' selected="selected"'; endif; ?>><?php _e('Yes', 'churchthemes'); ?></option>
		</select>
		<br />
		<small><em><?php _e('If Retweets are hidden they will be subtracted from your total Number of Tweets.', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_num]"><?php _e('Number of Tweets', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_num]" size="1" placeholder="3" value="<?php echo intval( $twitter_num ); ?>" />
		<br />
		<small><em><?php _e('Leaving this blank will default to: 3', 'churchthemes'); ?></em></small>
	</p>
	<p>
		<label for="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_followtext]"><?php _e('Follow Button Text', 'churchthemes'); ?></label>
		<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][twitter_followtext]" placeholder="Follow Us" value="<?php echo wp_filter_nohtml_kses( $twitter_followtext ); ?>" class="widefat" />
		<br />
		<small><em><?php _e('This button will link to your Twitter profile.', 'churchthemes'); ?></em></small>
	</p>
	<?php
}

// helper function can be defined in another plugin
if(!function_exists('ct_twitter_feed_update')){
	function ct_twitter_feed_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;

		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];

				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}

		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}

		// return updated array
		return $options;
	}
}

?>