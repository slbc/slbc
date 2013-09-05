<?php

// ---------------------
// Info Box Widget Class
// ---------------------

class pc_info_box_font_icons_widget extends WP_Widget {

	// Constructor
	function pc_info_box_font_icons_widget(){
        $widget_ops = array('classname' => 'pc_info_widget', 'description' => __('Display phone number, plus twitter and facebook links using \'retina-ready\' scalable icons.', 'presscoders' ) ); 
        $this->WP_Widget('pc_info_widget_fi_'.PC_THEME_NAME_SLUG, __( 'Info Box', 'presscoders' ), $widget_ops);
	}

	// Build widget options form
	function form($instance){
        $defaults = array(  'title' => '',
							'info_description' => '',
                            'phone_number' => '',
                            'twitter_url' => '',
                            'facebook_url' => '',
                            'youtube_url' => '',
                            'googleplus_url' => '',
                            'linkedin_url' => '',
                            'flickr_url' => '',
                            'pinterest_url' => '',
							'rss_url' => ''
                        );
        $instance = wp_parse_args( (array) $instance, $defaults );
		$title = strip_tags($instance['title']);
		$info_description = strip_tags($instance['info_description']);
        $phone_number = strip_tags($instance['phone_number']);
        $twitter_url = strip_tags($instance['twitter_url']);
        $facebook_url = strip_tags($instance['facebook_url']);
        $rss_url = strip_tags($instance['rss_url']);
        $youtube_url = strip_tags($instance['youtube_url']);
        $googleplus_url = strip_tags($instance['googleplus_url']);
        $linkedin_url = strip_tags($instance['linkedin_url']);
        $flickr_url = strip_tags($instance['flickr_url']);
        $pinterest_url = strip_tags($instance['pinterest_url']);
        ?>

            <style type="text/css">
                <!--
                div.scroll {
                    height: 120px;
                    overflow: auto;
                    border: 1px solid #dfdfdf;
                    background-color: #f8f8f8;
                    padding: 2px 2px 1px 2px;
					margin-bottom: 15px;
                }
                div.scroll table tr {
                    height: 30px;
                }
                -->
            </style>

            <p>
            <label for="<?php echo $this->get_field_name('title'); ?>"><?php _e('Title', 'presscoders' ) ?></label>
			<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </p>

            <p>
            <label for="<?php echo $this->get_field_name('info_description'); ?>"><?php _e('Description', 'presscoders' ) ?></label>
			<input class="widefat" name="<?php echo $this->get_field_name('info_description'); ?>" type="text" value="<?php echo esc_attr($info_description); ?>" />
            </p>

            <p>
            <label for="<?php echo $this->get_field_name('phone_number'); ?>"><?php _e('Phone Number', 'presscoders' ) ?></label>
			<input class="widefat" name="<?php echo $this->get_field_name('phone_number'); ?>" type="text" value="<?php echo esc_attr($phone_number); ?>" />
            </p>

            <p><label>Available Icons</label></p>

            <div class="scroll">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td><span class="sm-icon fb"><p>F</p></span></td>
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('facebook_url'); ?>" type="text" value="<?php echo esc_attr($facebook_url); ?>" /></td>
                        <td style="width:73px;font-size:11px;">&nbsp;<?php _e('Facebook URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon tw"><p>L</p></span></td>
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('twitter_url'); ?>" type="text" value="<?php echo esc_attr($twitter_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('Twitter URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon yt"><p>X</p></span></td>
						<td><input style="width:100%;" name="<?php echo $this->get_field_name('youtube_url'); ?>" type="text" value="<?php echo esc_attr($youtube_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('YouTube URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon fr"><p>N</p></span></td>                      
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('flickr_url'); ?>" type="text" value="<?php echo esc_attr($flickr_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('Flickr URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon pin"><p>:</p></span></td>                      
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('pinterest_url'); ?>" type="text" value="<?php echo esc_attr($pinterest_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('Pinterest URL', 'presscoders' ) ?></td>
                    </tr>
					<tr>
                        <td><span class="sm-icon google"><p>G</p></span></td>
						<td><input style="width:100%;" name="<?php echo $this->get_field_name('googleplus_url'); ?>" type="text" value="<?php echo esc_attr($googleplus_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('Google+ URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon linkedin"><p>I</p></span></td>
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('linkedin_url'); ?>" type="text" value="<?php echo esc_attr($linkedin_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('LinkedIn URL', 'presscoders' ) ?></td>
                    </tr>
                    <tr>
                        <td><span class="sm-icon rss"><p>R</p></span></td>
                        <td><input style="width:100%;" name="<?php echo $this->get_field_name('rss_url'); ?>" type="text" value="<?php echo esc_attr($rss_url); ?>" /></td>
                        <td style="font-size:11px;">&nbsp;<?php _e('RSS URL', 'presscoders' ) ?></td>
                    </tr>
                </table>
            </div>            
        <?php
	}

	// Save widget settings
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['info_description'] = strip_tags($new_instance['info_description']);
		$instance['phone_number'] = strip_tags($new_instance['phone_number']);
        $instance['twitter_url'] = strip_tags($new_instance['twitter_url']);
        $instance['facebook_url'] = strip_tags($new_instance['facebook_url']);
        $instance['rss_url'] = strip_tags($new_instance['rss_url']);
        $instance['youtube_url'] = strip_tags($new_instance['youtube_url']);
        $instance['googleplus_url'] = strip_tags($new_instance['googleplus_url']);
        $instance['linkedin_url'] = strip_tags($new_instance['linkedin_url']);
        $instance['flickr_url'] = strip_tags($new_instance['flickr_url']);
        $instance['pinterest_url'] = strip_tags($new_instance['pinterest_url']);
 
        return $instance;
    }
 
	// Display widget
    function widget($args, $instance) {

        extract($args);
		echo $before_widget;

 		$title = $instance['title'];
 		$info_description = $instance['info_description'];
		$phone_number = $instance['phone_number'];
        $twitter_url = $instance['twitter_url'];
        $facebook_url = $instance['facebook_url'];
        $rss_url = $instance['rss_url'];
        $youtube_url = $instance['youtube_url'];
        $googleplus_url = $instance['googleplus_url'];
        $linkedin_url = $instance['linkedin_url'];
        $flickr_url = $instance['flickr_url'];
        $pinterest_url = $instance['pinterest_url'];

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

		if ( !empty( $info_description ) ) { echo '<p class="info_description">'.$info_description.'</p>'; }

		if ( !empty( $phone_number ) ) { echo '<span class="phone"><i class="icon-phone"></i><a href="tel:'.$phone_number.'">'.$phone_number.'</a></span>'; }
        
		if ( !empty( $facebook_url ) ) { echo '<a href="'.$facebook_url.'" target="_blank" class="sm-icon fb"><p>F</p></a>'; }
		if ( !empty( $twitter_url ) ) { echo '<a href="'.$twitter_url.'" target="_blank" class="sm-icon tw"><p>L</p></a>'; }
		if ( !empty( $youtube_url ) ) { echo '<a href="'.$youtube_url.'" target="_blank" class="sm-icon yt"><p>X</p></a>'; }
        if ( !empty( $flickr_url ) ) { echo '<a href="'.$flickr_url.'" target="_blank" class="sm-icon fr"><p>N</p></a>'; }
        if ( !empty( $pinterest_url ) ) { echo '<a href="'.$pinterest_url.'" target="_blank" class="sm-icon pin"><p>:</p></a>'; }
		if ( !empty( $googleplus_url ) ) { echo '<a href="'.$googleplus_url.'" target="_blank" class="sm-icon google"><p>G</p></a>'; }
		if ( !empty( $linkedin_url ) ) { echo '<a href="'.$linkedin_url.'" target="_blank" class="sm-icon linkedin"><p>I</p></a>'; }
		if ( !empty( $rss_url ) ) { echo '<a href="'.$rss_url.'" target="_blank" class="sm-icon rss"><p>R</p></a>'; }

		echo $after_widget;
    }
}

?>