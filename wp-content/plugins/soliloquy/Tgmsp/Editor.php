<?php
/**
 * Editor class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Editor {

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

		add_filter( 'media_buttons_context', array( $this, 'tinymce' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );

	}

	/**
	 * Adds a custom slider insert button beside the media uploader button.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 * @param string $context The media buttons context HTML
	 * @return string $context Amended media buttons context HTML
	 */
	public function tinymce( $context ) {

		global $pagenow;
		$output = '';

		/** Only run in post/page creation and edit screens */
		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			$img 	= '<span class="wp-media-buttons-icon" style="background-image: url(' . plugins_url( 'css/images/menu-icon.png', dirname( __FILE__ ) ) . '); margin-top: -1px;"></span>';
			$output = '<a href="#" class="button soliloquy-choose-slider" title="' . Tgmsp_Strings::get_instance()->strings['add_slider'] . '" style="padding-left: .4em;">' . $img . ' ' . Tgmsp_Strings::get_instance()->strings['add_slider_editor'] . '</a>';
		}

		return apply_filters( 'tgmsp_editor_button', $context . $output );

	}

	/**
	 * Contextualizes the post updated messages.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current Soliloquy post type object
	 * @param array $messages Array of default post updated messages
	 * @return array $messages Amended array of post updated messages
	 */
	public function messages( $messages ) {

		global $post;

		$messages['soliloquy'] = apply_filters( 'tgmsp_slider_messages', array(
			0	=> '',
			1	=> Tgmsp_Strings::get_instance()->strings['pm_general'],
			2	=> Tgmsp_Strings::get_instance()->strings['pm_cf_updated'],
			3	=> Tgmsp_Strings::get_instance()->strings['pm_cf_deleted'],
			4	=> Tgmsp_Strings::get_instance()->strings['pm_general'],
			5	=> isset( $_GET['revision'] ) ? sprintf( Tgmsp_Strings::get_instance()->strings['pm_revision'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6	=> Tgmsp_Strings::get_instance()->strings['pm_published'],
			7	=> Tgmsp_Strings::get_instance()->strings['pm_saved'],
			8	=> Tgmsp_Strings::get_instance()->strings['pm_submitted'],
			9	=> sprintf( Tgmsp_Strings::get_instance()->strings['pm_scheduled'], date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10	=> Tgmsp_Strings::get_instance()->strings['pm_draft']
		) );

		/** Return the amended array of post updated messages */
		return $messages;

	}

	/**
	 * Outputs the jQuery and HTML necessary to insert a slider when the user
	 * uses the button added to the media buttons above TinyMCE.
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow The current page slug
	 */
	public function admin_footer() {

		global $pagenow;

		/** Only run in post/page creation and edit screens */
		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			/** Get all published sliders */
			$sliders = get_posts( array( 'post_type' => 'soliloquy', 'posts_per_page' => -1, 'post_status' => 'publish' ) );

			?>
			<script type="text/javascript">
				(function($){
					// Close the modal window on user action
					var append_and_hide = function(e){
						e.preventDefault();
						$('#soliloquy-default-ui .selected').removeClass('details selected');
						$('#soliloquy-default-ui').appendTo('#soliloquy-default-ui-wrapper').hide();
					};
					$(document.body).on('click.soliloquyChooseSlider', '.soliloquy-choose-slider', function(e){
						e.preventDefault();
						var $this = $(this);

						// Show the modal.
						$('#soliloquy-default-ui').appendTo('body').show();

						$(document.body).on('click.soliloquyCloseMediaUI', '.media-modal-close, .media-modal-backdrop, .soliloquy-cancel-insertion', append_and_hide);
						$(document.body).on('keydown.soliloquyCloseMediaUI', function(e){
							e.preventDefault();
							if ( 27 == e.keyCode )
								append_and_hide(e);
						});
					});
					$(document.body).on('click.soliloquyThumbnail', '#soliloquy-default-ui .thumbnail, #soliloquy-default-ui .check, #soliloquy-default-ui .media-modal-icon', function(e){
						e.preventDefault();
						if ( $(this).parent().parent().hasClass('selected') ) {
							$(this).parent().parent().removeClass('details selected');
							$('.soliloquy-insert-slider').attr('disabled', 'disabled');
						} else {
							$(this).parent().parent().parent().find('.selected').removeClass('details selected');
							$(this).parent().parent().addClass('details selected');
							$('.soliloquy-insert-slider').removeAttr('disabled');
						}
					});
					$(document.body).on('click.soliloquyThumbnailClose', '#soliloquy-default-ui .check', function(e){
						e.preventDefault();
						$(this).parent().parent().removeClass('details selected');
						$('.soliloquy-insert-slider').attr('disabled', 'disabled');
					});
					$(document.body).on('click.soliloquyInsertSlider', '#soliloquy-default-ui .soliloquy-insert-slider', function(e){
						e.preventDefault();
						wp.media.editor.insert('[soliloquy id="' + $('#soliloquy-default-ui .selected').data('soliloquy-id') + '"]');
						append_and_hide(e);
					});
				})(jQuery);
			</script>

			<div id="soliloquy-default-ui-wrapper" style="display: none;">
				<div id="soliloquy-default-ui" class="soliloquy-image-meta">
				    <div class="media-modal wp-core-ui">
				        <a class="media-modal-close" href="#"><span class="media-modal-icon"></span>
				        </a>
				        <div class="media-modal-content">
				            <div class="media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">
				                <div class="media-frame-title">
				                    <h1><?php echo Tgmsp_Strings::get_instance()->strings['slider_choose']; ?></h1>
				                </div>
				                <div class="media-frame-content">
				                    <div class="attachments-browser">
				                        <ul class="soliloquy-meta attachments" style="padding-left: 8px; top: 1em;">
				                        	<?php foreach ( $sliders as $slider ) : ?>
				                        	<li class="attachment" data-soliloquy-id="<?php echo absint( $slider->ID ); ?>" style="margin: 8px;">
				                        		<div class="attachment-preview landscape">
				                        			<div class="thumbnail" style="display: table;">
				                        				<div style="display: table-cell; vertical-align: middle; text-align: center;">
					                        				<h3 style="margin: 0;"><?php echo $slider->post_title; ?></h3>
					                        				<code>[soliloquy id="<?php echo absint( $slider->ID ); ?>"]</code>
				                        				</div>
				                        			</div>
				                        			<a class="check" href="#"><div class="media-modal-icon"></div></a>
				                        		</div>
				                        	</li>
				                        	<?php endforeach; ?>
				                        </ul>
				                        <!-- end .soliloquy-meta -->
				                        <div class="media-sidebar">
				                            <div class="soliloquy-meta-sidebar">
				                                <h3 style="margin: 1.4em 0 1em;"><?php echo Tgmsp_Strings::get_instance()->strings['media_sb_tips']; ?></h3>
				                                <strong><?php echo Tgmsp_Strings::get_instance()->strings['editor_choose']; ?></strong>
				                                <p style="margin: 0 0 1.5em;"><?php echo Tgmsp_Strings::get_instance()->strings['editor_choose_desc']; ?></p>
				                                <strong><?php echo Tgmsp_Strings::get_instance()->strings['editor_insert']; ?></strong>
				                                <p style="margin: 0 0 1.5em;"><?php echo Tgmsp_Strings::get_instance()->strings['editor_insert_desc']; ?></p>
				                            </div>
				                            <!-- end .soliloquy-meta-sidebar -->
				                        </div>
				                        <!-- end .media-sidebar -->
				                    </div>
				                    <!-- end .attachments-browser -->
				                </div>
				                <!-- end .media-frame-content -->
				                <div class="media-frame-toolbar">
				                    <div class="media-toolbar">
				                    	<div class="media-toolbar-secondary">
				                            <a href="#" class="soliloquy-cancel-insertion button media-button button-large button-secondary media-button-insert" title="<?php echo esc_attr( Tgmsp_Strings::get_instance()->strings['slider_select_cancel'] ); ?>"><?php echo esc_attr( Tgmsp_Strings::get_instance()->strings['slider_select_cancel'] ); ?></a>
				                        </div>
				                        <div class="media-toolbar-primary">
				                            <a href="#" class="soliloquy-insert-slider button media-button button-large button-primary media-button-insert" disabled="disabled" title="<?php echo esc_attr( Tgmsp_Strings::get_instance()->strings['slider_select_insert'] ); ?>"><?php echo esc_attr( Tgmsp_Strings::get_instance()->strings['slider_select_insert'] ); ?></a>
				                        </div>
				                        <!-- end .media-toolbar-primary -->
				                    </div>
				                    <!-- end .media-toolbar -->
				                </div>
				                <!-- end .media-frame-toolbar -->
				            </div>
				            <!-- end .media-frame -->
				        </div>
				        <!-- end .media-modal-content -->
				    </div>
				    <!-- end .media-modal -->
				    <div class="media-modal-backdrop"></div>
				</div><!-- end #soliloquy-default-ui -->
			</div><!-- end #soliloquy-default-ui-wrapper -->
			<?php
		}

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