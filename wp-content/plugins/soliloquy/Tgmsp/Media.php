<?php
/**
 * Media class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Media {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		add_filter( 'get_media_item_args', array( $this, 'force_send' ) );
		add_filter( 'plupload_init', array( $this, 'plupload' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_image_link' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'save_image_link' ), 10, 2 );

	}

	/**
	 * Since Soliloquy doesn't support the post editor by default, we need to force the
	 * "Insert into Slider" button to appear for non-attached images.
	 *
	 * @since 1.4.0
	 *
	 * @param array $args The current media item args
	 * @return array $args Amended media item args
	 */
	public function force_send( $args ) {

		if ( ! Tgmsp::is_soliloquy_add_edit_screen() )
			return $args;

		/** Force send to true */
		$args['send']   = true;
		$args['delete'] = false;
		return $args;

	}

	public function plupload( $init ) {

		if ( ! Tgmsp::is_soliloquy_add_edit_screen() )
			return $init;

		global $post;
		$init['url'] = add_query_arg( array( 'post_id' => absint( $post->ID ) ), admin_url( 'async-upload.php' ) );
		$init['multipart_params']['post_id']= absint( $post->ID );

		return $init;

	}

	/**
	 * Is this the Soliloquy upload iframe context?
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow Current WordPress admin screen
	 * @return bool
	 */
	public function is_our_context() {

		global $pagenow;

		if ( isset( $_REQUEST['context'] ) && 'soliloquy-image-uploads' == $_REQUEST['context'] )
			return true;

		if ( 'async-upload.php' == $pagenow && isset( $_REQUEST['fetch'] ) && isset( $_REQUEST['attachment_id'] ) ) {
			$parent = get_post( wp_get_post_parent_id( $_REQUEST['attachment_id'] ) );

			if ( $parent )
				if ( 'soliloquy' == $parent->post_type )
					return true;
		}

		/** The current action is not in our context, so return false */
		return false;

	}

	/**
	 * Add an extra image meta field to store image links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Default array of meta fields for uploads
	 * @param object $attachment The current attachment object
	 */
	public function add_image_link( $fields, $attachment ) {

		if ( $this->is_our_context() ) {
			$fields['soliloquy_link'] = apply_filters( 'tgmsp_extra_media_fields_link', array(
				'label' => Tgmsp_Strings::get_instance()->strings['image_link'],
				'input' => 'text',
				'value' => get_post_meta( $attachment->ID, '_soliloquy_image_link', true )
			) );

			$fields['soliloquy_link_title'] = apply_filters( 'tgmsp_extra_media_fields_link_title', array(
				'label' => Tgmsp_Strings::get_instance()->strings['image_link_title'],
				'input' => 'text',
				'value' => get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true )
			) );

			$fields['soliloquy_link_tab'] = apply_filters( 'tgmsp_extra_media_fields_link_tab', array(
				'label' => Tgmsp_Strings::get_instance()->strings['new_tab'],
				'input' => 'html',
				'html' 	=> '<input id="attachments[' . $attachment->ID . '][soliloquy_link_tab]" name="attachments[' . $attachment->ID . '][soliloquy_link_tab]" type="checkbox" value="' . get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ) . '"' . checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ) . ' />'
			) );

			$fields = apply_filters( 'tgmsp_media_fields', $fields, $attachment );
		}

		return $fields;

	}

	/**
	 * Save extra image meta field to store image links.
	 *
	 * @since 1.0.0
	 *
	 * @param object $attachment The current attachment object
	 * @param array $post_var The submitted $_POST array
	 */
	public function save_image_link( $attachment, $post_var ) {

		if ( $this->is_our_context() ) {
			/** Update image meta link field */
			update_post_meta( $attachment['ID'], '_soliloquy_image_link', isset( $post_var['soliloquy_link'] ) ? esc_url( $post_var['soliloquy_link'] ) : '' );
			update_post_meta( $attachment['ID'], '_soliloquy_image_link_title', isset( $post_var['soliloquy_link_title'] ) ? esc_attr( strip_tags( $post_var['soliloquy_link_title'] ) ) : '' );
			update_post_meta( $attachment['ID'], '_soliloquy_image_link_tab', isset( $post_var['soliloquy_link_tab'] ) ? (int) 1 : (int) 0 );

			do_action( 'tgmsp_update_media_fields', $attachment, $post_var );
		}

		return $attachment;

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}