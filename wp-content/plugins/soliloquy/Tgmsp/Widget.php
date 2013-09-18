<?php
/**
 * Widget class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Widget extends WP_Widget {

 	/**
	 * Constructor. Sets up and creates the widget with appropriate settings.
	 *
	 * @since 1.0.0
	 */
 	public function __construct() {

 	 	$widget_ops = apply_filters( 'tgmsp_widget_ops', array(
 	 		'classname' 	=> 'soliloquy',
 	 		'description' 	=> __( 'Place a Soliloquy slider in your sidebar.', 'soliloquy' )
 	 	) );

 	 	$control_ops = apply_filters( 'tgmsp_widget_control_ops', array(
 	 		'id_base' 	=> 'soliloquy',
 	 		'height' 	=> 350,
 	 		'width' 	=> 225
 	 	) );

 	 	$this->WP_Widget( 'soliloquy', apply_filters( 'tgmsp_widget_name', __( 'Soliloquy', 'soliloquy' ) ), $widget_ops, $control_ops );

 	}

 	/**
	 * Outputs the widget within the sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The default widget arguments
	 * @param array $instance The input settings for the current widget instance
	 */
 	public function widget( $args, $instance ) {

 	 	/** Take arguments array and turn keys into variables */
 	 	extract( $args );

 	 	$title 			= apply_filters( 'widget_title', $instance['title'] );
 	 	$soliloquy_id 	= $instance['soliloquy_id'];

 	 	do_action( 'tgmsp_widget_before_output', $args, $instance );

 	 	echo $before_widget;

 	 	do_action( 'tgmsp_widget_before_title', $args, $instance );

 	 	/** If a title exists, output it */
 	 	if ( $title )
 	 		echo $before_title . $title . $after_title;

 	 	do_action( 'tgmsp_widget_before_slider', $args, $instance );

 	 	/** If a user has selected a slider, output it */
 	 	if ( $soliloquy_id )
 	 		soliloquy_slider( $soliloquy_id );

 	 	do_action( 'tgmsp_widget_after_slider', $args, $instance );

 	 	echo $after_widget;

 	 	do_action( 'tgmsp_widget_after_output', $args, $instance );

 	}

 	/**
	 * Sanitizes and updates the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance The new input settings for the current widget instance
	 * @param array $old_instance The old input settings for the current widget instance
	 */
 	public function update( $new_instance, $old_instance ) {

 	 	/** Set $instance to the old instance in case no new settings have been updated for a particular field */
 	 	$instance = $old_instance;

 	 	/** Sanitize inputs */
 	 	$instance['title'] 			= strip_tags( $new_instance['title'] );
 	 	$instance['soliloquy_id'] 	= absint( $new_instance['soliloquy_id'] );

 	 	do_action( 'tgmsp_widget_update', $new_instance, $instance );

 	 	return apply_filters( 'tgmsp_widget_update_instance', $instance, $new_instance );

 	}

 	/**
	 * Outputs the form where the user can specify settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The input settings for the current widget instance
	 */
 	public function form( $instance ) {

 	 	/** Get all available sliders ready to be output as select options */
 	 	$sliders 		= get_posts( array( 'post_type' => 'soliloquy', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
 	 	$title 			= isset( $instance['title'] ) ? $instance['title'] : '';
		$soliloquy_id 	= isset( $instance['soliloquy_id'] ) ? $instance['soliloquy_id'] : null;

 	 	?>
 	 	<?php do_action( 'tgmsp_widget_before_form', $instance ); ?>
 	 	<p>
 	 		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo Tgmsp_Strings::get_instance()->strings['widget_title']; ?></label>
 	 		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%;" />
 	 	</p>
 	 	<?php do_action( 'tgmsp_widget_middle_form', $instance ); ?>
 	 	<p>
 	 		<label for="<?php echo $this->get_field_id( 'soliloquy_id' ); ?>"><?php echo Tgmsp_Strings::get_instance()->strings['widget_slider']; ?></label>
 	 		<select id="<?php echo esc_attr( $this->get_field_id( 'soliloquy_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'soliloquy_id' ) ); ?>">
				<?php
					foreach ( $sliders as $slider )
						echo '<option value="' . absint( $slider->ID ) . '"' . selected( absint( $slider->ID ), $soliloquy_id, false ) . '>' . esc_attr( $slider->post_title ) . '</option>';
				?>
			</select>
 	 	</p>
 	 	<?php do_action( 'tgmsp_widget_after_form', $instance ); ?>
 	 	<?php

 	}

}