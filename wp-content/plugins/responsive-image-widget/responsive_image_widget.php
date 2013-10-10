<?php
/*
Plugin Name: Responsive Image Widget
Plugin URI: http://keokee.com
Description: Displays a responsive image with an optional link.  An easy way to place reliably scaled images in the sidebar or other widget area.  The image scales to 100% of the width of its container and the height ratio provided.
Author: Benjamin Robinson, Keokee Creative Group
Version: 1.4
Author URI: http://keokee.com/
*/
 
class ResponsiveImageWidget extends WP_Widget
{
  function ResponsiveImageWidget()
  {
    $widget_ops = array('classname' => 'ResponsiveImageWidget', 'description' => 'Displays a single responsive image of a specified ratio which can be linked.' );
    $this->WP_Widget('ResponsiveImageWidget', 'Responsive Image Widget', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
	$image_url = $instance['image_url'];
	$image_link = $instance['image_link'];
	$image_ratio = $instance['image_ratio'];
	$image_position = $instance['image_position'];
?>
  <p>
	<label for="<?php echo $this->get_field_id('title'); ?>">
	Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
	</label>
  </p>
  <p>
	<label for="<?php echo $this->get_field_id('image_url'); ?>">
	Image File URL: <input class="widefat" id="<?php echo $this->get_field_id('image_url'); ?>" name="<?php echo $this->get_field_name('image_url'); ?>" type="text" value="<?php echo attribute_escape($image_url); ?>" />
	</label>
  </p>
  <p>
	<label for="<?php echo $this->get_field_id('image_ratio'); ?>">
	Image Height Ratio % : <input class="widefat" id="<?php echo $this->get_field_id('image_ratio'); ?>" name="<?php echo $this->get_field_name('image_ratio'); ?>" type="number" value="<?php echo attribute_escape($image_ratio); ?>" />
	(100 = square.)
    </label>
  </p>
  <p>
	<label for="<?php echo $this->get_field_id('image_position'); ?>">
	Crop From: <select class="widefat" id="<?php echo $this->get_field_id('image_position'); ?>" name="<?php echo $this->get_field_name('image_position'); ?>">
 			<?php /* $options = array('center', 'right', 'left', 'top', 'bottom');
			foreach ($options as $option) {
				echo '<option value="' . $option . '" id="' . $option . '"', $select == $option ? ' selected="selected"' : '', '>', $option, '</option>';
			}*/
			?>
            <option value="center">Center</option>
            <option value="left">Left</option>
            <option value="right">Right</option>
            <option value="top">Top</option>
            <option value="bottom">Bottom</option>
			</select>
    </label>
  </p>
  <p>
	<label for="<?php echo $this->get_field_id('image_link'); ?>">
	URL to Link To (optional): <input class="widefat" id="<?php echo $this->get_field_id('image_link'); ?>" name="<?php echo $this->get_field_name('image_link'); ?>" type="text" value="<?php echo attribute_escape($image_link); ?>" />
	</label>
  </p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	$instance['image_url'] = $new_instance['image_url'];
	$instance['image_link'] = $new_instance['image_link'];
	$instance['image_ratio'] = $new_instance['image_ratio'];
	$instance['image_position'] = $new_instance['image_position'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    
	$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$image_url = $instance['image_url'];
	$image_link = $instance['image_link'];
	$image_ratio = $instance['image_ratio'];
	$image_position = $instance['image_position'];
	
	if (!empty($title))
      echo '<div class="responsive-image-title">' . $before_title . $title . $after_title . '</div>';
	
	// Default to square if field is blank.
	if(!$image_ratio) { $image_ratio = 100; }
	
	// Make sure that the background fits with the ratio of the image.
	list($width, $height, $type, $attr) = getimagesize($image_url);
	if( (($height / $width)*100) <= $image_ratio ) {
		$background_size_correct = "background-size: auto 100% !important;";
	} else {
		$background_size_correct = "background-size: 100% !important;";
	}
		
	// Display the image -- uses the image as a background for a blank div rather than an "img" tag, this allows easy resizing with background properties
	if($image_link) {
		
		echo '<a style="display: block !important;" href="'.$image_link.'" title="'. $title .'">';
		echo '<div class="responsive-image-widget" style="position: relative; width: 100%; padding-bottom: '.$image_ratio.'%; background-image: url(' . $image_url . ') !important; background-position: 
'.$image_position.'; '. 
$background_size_correct .' background-repeat: no-repeat;"></div>';
		echo '</a>';
	} else {
			echo '<div style="position: relative; width: 100%; padding-bottom: '.$image_ratio.'%; background-image: url('. $image_url .') !important; background-position: '.$image_position.';  '. 
$background_size_correct .' 
background-repeat: no-repeat;"></div>';
	}
		
    echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("ResponsiveImageWidget");') );?>
