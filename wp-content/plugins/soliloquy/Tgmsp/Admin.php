<?php
/**
 * Admin class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Admin {

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

		add_action( 'admin_init', array( $this, 'upgrade_plugin_internals' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_seo_support' ), 99 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_slider_settings' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'tgmsp_soliloquy_settings', array( $this, 'output_soliloquy_plugin_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( Tgmsp::get_file() ), array( $this, 'settings_link' ) );

	}

	/**
	 * Generic utility to for upgrading any internal plugin pieces when a new update
	 * requires it.
	 *
	 * @since 1.5.0
	 */
	public function upgrade_plugin_internals() {

		global $soliloquy_license;

		// Return early if we have already have the db_version key set and it is greater than or equal to the current version of Soliloquy.
		if ( ! $soliloquy_license || isset( $soliloquy_license['db_version'] ) && version_compare( Tgmsp::get_instance()->version, $soliloquy_license['db_version'], '<=' ) )
			return;

		// For pre-1.5.0 Soliloquy users, upgrade all current video slides to the new system.
		if ( ! isset( $soliloquy_license['db_version'] ) || '1.5' < $soliloquy_license['db_version'] ) :
			// Grab all sliders to be processed.
			$sliders = Tgmsp::get_sliders();
			if ( $sliders ) :
				foreach ( $sliders as $slider ) :
					$attachments = get_posts( array(
						'orderby' 			=> 'menu_order',
						'order' 			=> 'ASC',
						'post_type' 		=> 'attachment',
						'post_parent' 		=> $slider->ID,
						'post_status' 		=> null,
						'posts_per_page' 	=> -1
					) );
					if ( $attachments ) :
						foreach ( $attachments as $attachment ) :
							// If the meta field does not exist or is not set to video, continue over the loop.
							$video = get_post_meta( $attachment->ID, '_soliloquy_image_link_type', true );
							if ( ! $video || 'image' == $video || '' == $video || 'video' != $video )
								continue;

							// Generate a new attachment with the current settings and remove association with old attachment.
							$link 	   = get_post_meta( $attachment->ID, '_soliloquy_video_link', true );
							$new_entry = array();
							$new_entry['post_title']     = isset( $attachment->post_title ) ? $attachment->post_title : '';
							$new_entry['post_content']   = $link ? esc_url( $link ) : '';
							$new_entry['post_excerpt']   = isset( $attachment->post_excerpt ) ? $attachment->post_excerpt : '';
							$new_entry['post_status'] 	 = 'inherit';
							$new_entry['post_mime_type'] = 'soliloquy/video';
							$new_entry['menu_order']	 = isset( $attachment->menu_order ) ? $attachment->menu_order : 0;
							wp_insert_attachment( $new_entry, false, $slider->ID );

							// Remove the old attachment association.
							$remove 			   = array();
							$remove['ID'] 		   = $attachment->ID;
							$remove['post_parent'] = $remove['menu_order'] = 0;
							wp_update_post( $remove );
						endforeach;
					endif;
				endforeach;
			endif;

			// Update the global Soliloquy option to reflect that the version has been updated.
			if ( ! is_array( $soliloquy_license ) )
			    $soliloquy_license = array();

			$soliloquy_license['db_version'] = '1.5';
			update_option( 'soliloquy_license_key', $soliloquy_license );
		endif;

	}

	/**
	 * Adds a menu item to the Soliloquy post type.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {

		add_submenu_page( 'edit.php?post_type=soliloquy', Tgmsp_Strings::get_instance()->strings['page_title'], Tgmsp_Strings::get_instance()->strings['menu_title'], apply_filters( 'tgmsp_settings_cap', 'manage_options' ), 'soliloquy-settings', array( $this, 'soliloquy_plugin_settings' ) );

	}

	/**
	 * Outputs the form callback and hooks everything into tgmsp_soliloquy_settings.
	 *
	 * @since 1.0.0
	 */
	public function soliloquy_plugin_settings() {

		global $soliloquy_license;

		echo '<div class="wrap soliloquy-settings">';
			screen_icon( 'soliloquy' );
			echo '<h2 class="soliloquy-settings-title">' . esc_html( get_admin_page_title() ) . '</h2>';

			do_action( 'tgmsp_soliloquy_settings', $soliloquy_license );

		echo '</div>';

	}

	/**
	 * Outputs the form area for verifying and possibly deactivating license keys.
	 *
	 * @since 1.0.0
	 */
	public function output_soliloquy_plugin_settings( $license ) {

		// Output a message if the user has just upgraded from Soliloquy Lite.
		if ( isset( $_GET['just_upgraded'] ) && $_GET['just_upgraded'] ) {
			echo '<div class="soliloquy-just-upgraded">';
				echo '<h2 class="soliloquy-upgraded-title">' . Tgmsp_Strings::get_instance()->strings['congrats'] . '</h2>';
				echo '<p>' . Tgmsp_Strings::get_instance()->strings['congrats_desc'] . '</p>';
			echo '</div>';
		}

		if ( ! $license || empty( $license['license'] ) ) {
			echo '<form method="post" action="">';
				wp_nonce_field( 'soliloquy-verify-license-key' );
				echo '<input type="hidden" name="verify_soliloquy_license" value="true" />';
				echo '<table class="form-table">';
					echo '<tbody>';
						echo '<tr valign=middle>';
							echo '<th scope="row"><label for="soliloquy-verify-license">' . Tgmsp_Strings::get_instance()->strings['enter_key'] . '</label></th>';
							echo '<td>';
								echo '<input id="soliloquy-verify-license" type="text" name="soliloquy_license_key" value="" />';
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
				echo '</table>';
				submit_button( Tgmsp_Strings::get_instance()->strings['verify_key'] );
			echo '</form>';
		} elseif ( isset( $license['single'] ) && $license['single'] ) {
			echo '<div id="tgmsp-verified" class="updated"><p><strong>' . Tgmsp_Strings::get_instance()->strings['key_verified'] . '</strong></p></div>';
			echo '<p>' . sprintf( Tgmsp_Strings::get_instance()->strings['key_upgrade'], '<a href="http://soliloquywp.com" target="_blank">http://soliloquywp.com</a>' ) . '</p>';

			echo '<form method="post" action="">';
				wp_nonce_field( 'soliloquy-deactivate-license-key' );
				echo '<input type="hidden" name="deactivate_soliloquy_license" value="true" />';
				echo '<input type="hidden" name="soliloquy_license_key" value="' . esc_attr( $license['license'] ) . '" />';
				echo '<p class="submit deactivate-license-submit"><input id="soliloquy-deactivate-key" type="submit" class="button-secondary" name="soliloquy_deactivate_license" value="' . Tgmsp_Strings::get_instance()->strings['deactivate_key'] . '" /></p>';
			echo '</form>';
		} else {
			echo '<div id="tgmsp-verified" class="updated"><p><strong>' . Tgmsp_Strings::get_instance()->strings['key_verified'] . '</strong></p></div>';
		}

	}

	/**
	 * There is no need to apply SEO to the Soliloquy post type, so we check to
	 * see if some popular SEO plugins are installed, and if so, remove the inpost
	 * meta boxes from view.
	 *
	 * This method also has a filter that can be used to remove any unwanted metaboxes
	 * from the Soliloquy screen - tgmsp_remove_metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function remove_seo_support() {

		$plugins = array(
			array( 'WPSEO_Metabox', 'wpseo_meta', 'normal' ),
			array( 'All_in_One_SEO_Pack', 'aiosp', 'advanced' ),
			array( 'Platinum_SEO_Pack', 'postpsp', 'normal' ),
			array( 'SEO_Ultimate', 'su_postmeta', 'normal' )
		);
		$plugins = apply_filters( 'tgmsp_remove_metaboxes', $plugins );

		/** Loop through the arrays and remove the metaboxes */
		foreach ( $plugins as $plugin )
			if ( class_exists( $plugin[0] ) )
				remove_meta_box( $plugin[1], convert_to_screen( 'soliloquy' ), $plugin[2] );

	}

	/**
	 * Add the metaboxes to the Soliloquy edit screen.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		add_meta_box( 'soliloquy_uploads', Tgmsp_Strings::get_instance()->strings['meta_uploads'], array( $this, 'soliloquy_uploads' ), 'soliloquy', 'normal', 'high' );
		add_meta_box( 'soliloquy_settings', Tgmsp_Strings::get_instance()->strings['meta_settings'], array( $this, 'soliloquy_settings' ), 'soliloquy', 'normal', 'high' );
		add_meta_box( 'soliloquy_instructions', Tgmsp_Strings::get_instance()->strings['meta_instructions'], array( $this, 'soliloquy_instructions' ), 'soliloquy', 'side', 'core' );

	}

	/**
	 * Callback function for Soliloquy image uploads.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post Current post object data
	 */
	public function soliloquy_uploads( $post ) {

		/** Always keep security first */
		wp_nonce_field( 'soliloquy_uploads', 'soliloquy_uploads' );

		?>
		<input id="soliloquy-uploads" type="hidden" name="soliloquy-uploads" value="1" />
		<div id="soliloquy-area">
			<div id="soliloquy-slider-type">
				<p class="soliloquy-chose-slider-type">
					<span class="soliloquy-type"><?php echo Tgmsp_Strings::get_instance()->strings['slider_type']; ?></span>
					<span class="soliloquy-type-select">
						<label for="soliloquy-default-slider"><input id="soliloquy-default-slider" type="radio" name="_soliloquy_settings[type]" value="default" <?php checked( self::get_custom_field( '_soliloquy_settings', 'type' ), 'default' ); ?> /> <?php echo Tgmsp_Strings::get_instance()->strings['slider_type_default']; ?></label>
						<?php do_action( 'tgmsp_slider_type', $post ); ?>
					</span>
				</p>
			</div>
			<?php do_action( 'tgmsp_before_upload_area', $post ); ?>
			<p class="soliloquy-upload-text"><?php echo Tgmsp_Strings::get_instance()->strings['upload_info']; ?></p>
			<a href="#" id="soliloquy-upload" class="button button-secondary" title="<?php echo esc_attr( Tgmsp_Strings::get_instance()->strings['upload_images'] ); ?>"><?php echo esc_html( Tgmsp_Strings::get_instance()->strings['upload_images'] ); ?></a>
			<?php $this->do_upload_output(); // Process the output of the custom upload UI. ?>
			<?php do_action( 'tgmsp_before_images_display', $post ); ?>
			<ul id="soliloquy-images">
				<?php
					/** List out all image attachments for the slider */
					$args = apply_filters( 'tgmsp_list_images_args', array(
						'orderby' 			=> 'menu_order',
						'order' 			=> 'ASC',
						'post_type' 		=> 'attachment',
						'post_parent' 		=> $post->ID,
						'post_status' 		=> null,
						'posts_per_page' 	=> -1
					) );
					$attachments = get_posts( $args );

					if ( $attachments ) {
						foreach ( $attachments as $attachment ) {
							switch ( $attachment->post_mime_type ) :
								default :
									echo '<li id="' . $attachment->ID . '" class="soliloquy-image attachment-' . $attachment->ID . '">';
										echo wp_get_attachment_image( $attachment->ID, apply_filters( 'tgmsp_display_thumb_size', 'soliloquy-thumb', $attachment, $post ) );
										echo '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
										echo '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
										echo '<div id="meta-' . $attachment->ID . '" class="soliloquy-image-meta" style="display: none;">';
											echo '<div class="media-modal wp-core-ui">';
												echo '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
												echo '<div class="media-modal-content">';
													echo '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
														echo '<div class="media-frame-title">';
															echo '<h1>' . Tgmsp_Strings::get_instance()->strings['update_meta'] . '</h1>';
														echo '</div>';
														echo '<div class="media-frame-content">';
															echo '<div class="attachments-browser">';
																echo '<div class="soliloquy-meta attachments">';
																	do_action( 'tgmsp_before_image_meta_table', $attachment );
																	echo '<table id="soliloquy-meta-table-' . $attachment->ID . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $attachment->ID . '" data-slide-type="image">';
																		echo '<tbody>';
																			do_action( 'tgmsp_before_image_title', $attachment );
																			echo '<tr id="soliloquy-title-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-title-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_title'] . '</label></th>';
																				echo '<td>';
																					echo '<input id="soliloquy-title-' . $attachment->ID . '" class="soliloquy-title" type="text" name="_soliloquy_uploads[title]" value="' . esc_attr( strip_tags( $attachment->post_title ) ) . '" />';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_image_alt', $attachment );
																			echo '<tr id="soliloquy-alt-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-alt-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_alt'] . '</label></th>';
																				echo '<td>';
																					echo '<input id="soliloquy-alt-' . $attachment->ID . '" class="soliloquy-alt" type="text" name="_soliloquy_uploads[alt]" value="' . esc_attr( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) ) . '" />';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_image_link', $attachment );
																			echo '<tr id="soliloquy-link-box-' . $attachment->ID . '" class="soliloquy-link-cell" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-link-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_link'] . '</label></th>';
																				echo '<td>';
																					echo '<div class="soliloquy-link-normal-wrap soliloquy-top">';
																						echo '<p class="no-margin"><label class="soliloquy-link-url" for="soliloquy-link-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_url'] . '</label>';
																						echo '<input id="soliloquy-link-' . $attachment->ID . '" class="soliloquy-link" type="text" name="_soliloquy_uploads[link]" value="' . esc_url( get_post_meta( $attachment->ID, '_soliloquy_image_link', true ) ) . '" /></p>';
																						echo '<p class="no-margin"><label class="soliloquy-link-title-label" for="soliloquy-link-title-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_url_title'] . '</label>';
																						echo '<input id="soliloquy-link-title-' . $attachment->ID . '" class="soliloquy-link-title" type="text" name="_soliloquy_uploads[link_title]" value="' . esc_attr( strip_tags( get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true ) ) ) . '" /></p>';
																						echo '<p><label class="soliloquy-link-tab-label" for="soliloquy-link-tab-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['tab'] . '</label><input id="soliloquy-link-tab-' . $attachment->ID . '" class="soliloquy-link-check" type="checkbox" name="_soliloquy_uploads[link_tab]" value="' . esc_attr( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ) ) . '"' . checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ) . ' />  ';
																						echo '<span class="description soliloquy-link-check-desc"> ' . Tgmsp_Strings::get_instance()->strings['new_tab'] . '</span></p>';
																						echo '<a id="soliloquy-link-existing" class="button button-secondary" href="#">' . Tgmsp_Strings::get_instance()->strings['link_existing'] . '</a>';
																						echo '<div id="soliloquy-internal-linking-' . $attachment->ID . '" style="display: none;">';
																							echo '<label class="soliloquy-search-label" for="soliloquy-search-links-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['search'] . '</label>';
																							echo '<input class="soliloquy-search" type="text" id="soliloquy-search-links-' . $attachment->ID . '" value="" />';
																							echo '<div class="soliloquy-search-results">';
																								echo '<ul id="soliloquy-list-links-' . $attachment->ID . '" class="soliloquy-results-list">';
																									echo '<li class="soliloquy-no-results"><span>' . Tgmsp_Strings::get_instance()->strings['no_results_default'] . '</span></li>';
																								echo '</ul>';
																							echo '</div>';
																						echo '</div>';
																					echo '</div>';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_image_caption', $attachment );
																			echo '<tr id="soliloquy-caption-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-caption-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['image_caption'] . '</label></th>';
																				echo '<td>';
																				    wp_editor( $attachment->post_excerpt, 'soliloquy-caption-' . $attachment->ID, array( 'wpautop' => true, 'media_buttons' => false, 'textarea_rows' => '6', 'textarea_name' => '_soliloquy_uploads[caption]', 'tabindex' => '100', 'tinymce' => false, 'teeny' => true, 'quicktags' => array('buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'), 'dfw' => false ) );
																					echo '<span class="description">' . Tgmsp_Strings::get_instance()->strings['image_caption_desc'] . '</span>';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_after_meta_defaults', $attachment );
																		echo '</tbody>';
																	echo '</table>';
																	do_action( 'tgmsp_after_image_meta_table', $attachment );
																echo '</div><!-- end .soliloquy-meta -->';
																echo '<div class="media-sidebar">';
																	echo '<div class="soliloquy-meta-sidebar">';
																		echo '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_seo'] . '</strong>';
																		echo '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_seo_desc'] . '</p>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_links'] . '</strong>';
																		echo '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_links_desc'] . '</p>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_cap'] . '</strong>';
																		echo '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_cap_desc'] . '</p>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_sb_se'] . '</strong>';
																		echo '<p class="no-margin">' . Tgmsp_Strings::get_instance()->strings['media_sb_se_desc'] . '</p>';
																	echo '</div><!-- end .soliloquy-meta-sidebar -->';
																echo '</div><!-- end .media-sidebar -->';
															echo '</div><!-- end .attachments-browser -->';
														echo '</div><!-- end .media-frame-content -->';
														echo '<div class="media-frame-toolbar">';
															echo '<div class="media-toolbar">';
																echo '<div class="media-toolbar-primary">';
																	echo '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
																echo '</div><!-- end .media-toolbar-primary -->';
															echo '</div><!-- end .media-toolbar -->';
														echo '</div><!-- end .media-frame-toolbar -->';
													echo '</div><!-- end .media-frame -->';
												echo '</div><!-- end .media-modal-content -->';
											echo '</div><!-- end .media-modal -->';
											echo '<div class="media-modal-backdrop"></div>';
										echo '</div><!-- end .soliloquy-image-meta -->';
									echo '</li>';
								break;
								case 'soliloquy/video' :
									echo '<li id="' . $attachment->ID . '" class="soliloquy-image soliloquy-video attachment-' . $attachment->ID . '" data-full-delete="true">';
										echo '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
										echo '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
										echo '<div id="meta-' . $attachment->ID . '" class="soliloquy-image-meta" style="display: none;">';
											echo '<div class="media-modal wp-core-ui">';
												echo '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
												echo '<div class="media-modal-content">';
													echo '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
														echo '<div class="media-frame-title">';
															echo '<h1>' . Tgmsp_Strings::get_instance()->strings['update_video_meta'] . '</h1>';
														echo '</div>';
														echo '<div class="media-frame-content">';
															echo '<div class="attachments-browser">';
																echo '<div class="soliloquy-meta attachments">';
																	do_action( 'tgmsp_before_video_meta_table', $attachment );
																	echo '<table id="soliloquy-meta-table-' . $attachment->ID . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $attachment->ID . '" data-slide-type="video">';
																		echo '<tbody>';
																			do_action( 'tgmsp_before_video_title', $attachment );
																			echo '<tr id="soliloquy-video-title-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-video-title-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['video_slide'] . '</label></th>';
																				echo '<td>';
																					echo '<input id="soliloquy-video-title-' . $attachment->ID . '" class="soliloquy-video-title" type="text" name="_soliloquy_uploads[video_title]" value="' . esc_attr( strip_tags( $attachment->post_title ) ) . '" />';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_video_url', $attachment );
																			echo '<tr id="soliloquy-video-url-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-video-url-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['video_title'] . '</label></th>';
																				echo '<td>';
																					echo '<input id="soliloquy-video-url-' . $attachment->ID . '" class="soliloquy-video-url" type="text" name="_soliloquy_uploads[video_url]" value="' . esc_url( $attachment->post_content ) . '" />';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_video_caption', $attachment );
																			echo '<tr id="soliloquy-video-caption-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-video-caption-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['video_caption'] . '</label></th>';
																				echo '<td>';
																					echo '<textarea id="soliloquy-video-caption-' . $attachment->ID . '" class="soliloquy-video-caption" rows="3" name="_soliloquy_uploads[video_caption]">' . esc_html( $attachment->post_excerpt ) . '</textarea>';
																					echo '<span class="description">' . Tgmsp_Strings::get_instance()->strings['image_caption_desc'] . '</span>';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_after_video_meta_defaults', $attachment );
																		echo '</tbody>';
																	echo '</table>';
																	do_action( 'tgmsp_after_video_meta_table', $attachment );
																echo '</div><!-- end .soliloquy-meta -->';
																echo '<div class="media-sidebar">';
																	echo '<div class="soliloquy-meta-sidebar">';
																		echo '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_video_help'] . '</strong>';
																		echo '<p>' . Tgmsp_Strings::get_instance()->strings['media_video_help_desc'] . '</p>';
																	echo '</div><!-- end .soliloquy-meta-sidebar -->';
																echo '</div><!-- end .media-sidebar -->';
															echo '</div><!-- end .attachments-browser -->';
														echo '</div><!-- end .media-frame-content -->';
														echo '<div class="media-frame-toolbar">';
															echo '<div class="media-toolbar">';
																echo '<div class="media-toolbar-primary">';
																	echo '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
																echo '</div><!-- end .media-toolbar-primary -->';
															echo '</div><!-- end .media-toolbar -->';
														echo '</div><!-- end .media-frame-toolbar -->';
													echo '</div><!-- end .media-frame -->';
												echo '</div><!-- end .media-modal-content -->';
											echo '</div><!-- end .media-modal -->';
											echo '<div class="media-modal-backdrop"></div>';
										echo '</div><!-- end .soliloquy-image-meta -->';
										echo '<div class="soliloquy-video-wrap">';
											echo '<div class="soliloquy-video-inside">';
												echo '<div class="soliloquy-video-table">';
													echo '<h4 class="no-margin">' . esc_html( $attachment->post_title ) . '</h4>';
													echo '<span class="soliloquy-mini">' . Tgmsp_Strings::get_instance()->strings['video_slide_mini'] . '</span>';
												echo '</div>';
											echo '</div>';
										echo '</div>';
									echo '</li>';
								break;
								case 'soliloquy/html' :
									echo '<li id="' . $attachment->ID . '" class="soliloquy-image soliloquy-html attachment-' . $attachment->ID . '" data-full-delete="true">';
										echo '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
										echo '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
										echo '<div id="meta-' . $attachment->ID . '" class="soliloquy-image-meta" style="display: none;">';
											echo '<div class="media-modal wp-core-ui">';
												echo '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
												echo '<div class="media-modal-content">';
													echo '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
														echo '<div class="media-frame-title">';
															echo '<h1>' . Tgmsp_Strings::get_instance()->strings['update_html_meta'] . '</h1>';
														echo '</div>';
														echo '<div class="media-frame-content">';
															echo '<div class="attachments-browser">';
																echo '<div class="soliloquy-meta attachments">';
																	do_action( 'tgmsp_before_html_meta_table', $attachment );
																	echo '<table id="soliloquy-meta-table-' . $attachment->ID . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $attachment->ID . '" data-slide-type="html">';
																		echo '<tbody>';
																			do_action( 'tgmsp_before_html_title', $attachment );
																			echo '<tr id="soliloquy-html-title-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-html-title-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['html_slide'] . '</label></th>';
																				echo '<td>';
																					echo '<input id="soliloquy-html-title-' . $attachment->ID . '" class="soliloquy-html-title" type="text" name="_soliloquy_uploads[html_title]" value="' . esc_attr( strip_tags( $attachment->post_title ) ) . '" />';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_before_html_code', $attachment );
																			echo '<tr id="soliloquy-html-code-box-' . $attachment->ID . '" valign="middle">';
																				echo '<th scope="row"><label for="soliloquy-html-code-' . $attachment->ID . '">' . Tgmsp_Strings::get_instance()->strings['html_code'] . '</label></th>';
																				echo '<td>';
																					echo '<textarea id="soliloquy-html-code-' . $attachment->ID . '" class="soliloquy-html-code" name="_soliloquy_uploads[html_code]">' . $attachment->post_content . '</textarea>';
																				echo '</td>';
																			echo '</tr>';
																			do_action( 'tgmsp_after_html_meta_defaults', $attachment );
																		echo '</tbody>';
																	echo '</table>';
																	do_action( 'tgmsp_after_html_meta_table', $attachment );
																echo '</div><!-- end .soliloquy-meta -->';
																echo '<div class="media-sidebar">';
																	echo '<div class="soliloquy-meta-sidebar">';
																		echo '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
																		echo '<strong>' . Tgmsp_Strings::get_instance()->strings['media_html_help'] . '</strong>';
																		echo '<p>' . Tgmsp_Strings::get_instance()->strings['media_html_help_desc'] . '</p>';
																	echo '</div><!-- end .soliloquy-meta-sidebar -->';
																echo '</div><!-- end .media-sidebar -->';
															echo '</div><!-- end .attachments-browser -->';
														echo '</div><!-- end .media-frame-content -->';
														echo '<div class="media-frame-toolbar">';
															echo '<div class="media-toolbar">';
																echo '<div class="media-toolbar-primary">';
																	echo '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
																echo '</div><!-- end .media-toolbar-primary -->';
															echo '</div><!-- end .media-toolbar -->';
														echo '</div><!-- end .media-frame-toolbar -->';
													echo '</div><!-- end .media-frame -->';
												echo '</div><!-- end .media-modal-content -->';
											echo '</div><!-- end .media-modal -->';
											echo '<div class="media-modal-backdrop"></div>';
										echo '</div><!-- end .soliloquy-image-meta -->';
										echo '<div class="soliloquy-video-wrap">';
											echo '<div class="soliloquy-video-inside">';
												echo '<div class="soliloquy-video-table">';
													echo '<h4 class="no-margin">' . esc_html( $attachment->post_title ) . '</h4>';
													echo '<span class="soliloquy-mini">' . Tgmsp_Strings::get_instance()->strings['html_slide_mini'] . '</span>';
												echo '</div>';
											echo '</div>';
										echo '</div>';
									echo '</li>';
								break;
							endswitch;
						}
					}
				?>
			</ul>
			<?php do_action( 'tgmsp_after_upload_area', $post ); ?>
		</div><!-- end #soliloquy-area -->
		<?php

	}

	/**
	 * Callback function for Soliloquy settings.
	 *
	 * @since 1.0.0
	 *
	 * @global array $_wp_additional_image_sizes Additional registered image sizes
	 * @param object $post Current post object data
	 */
	public function soliloquy_settings( $post ) {

		global $_wp_additional_image_sizes;

		/** Always keep security first */
		wp_nonce_field( 'soliloquy_settings_script', 'soliloquy_settings_script' );

		do_action( 'tgmsp_before_settings_table', $post );

		?>
		<table class="form-table">
			<tbody>
				<?php do_action( 'tgmsp_before_setting_default', $post ); ?>
				<tr id="soliloquy-default-size-box" valign="middle">
					<th scope="row"><label for="soliloquy-default-size"><?php echo Tgmsp_Strings::get_instance()->strings['slider_default']; ?></label></th>
					<td>
					<?php
						$defaults = apply_filters( 'tgmsp_default_sizes', array( 'default', 'custom' ) );
						echo '<select id="soliloquy-default-size" name="_soliloquy_settings[default]">';
							foreach ( $defaults as $default ) {
								echo '<option value="' . esc_attr( $default ) . '"' . selected( $default, self::get_custom_field( '_soliloquy_settings', 'default' ), false ) . '>' . esc_html( $default ) . '</option>';
								}
						echo '</select>';
					?>
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_default_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_size', $post ); ?>
				<tr id="soliloquy-size-box" valign="middle">
					<th scope="row"><label for="soliloquy-width"><?php echo Tgmsp_Strings::get_instance()->strings['slider_size']; ?></label></th>
					<td>
						<div id="soliloquy-default-sizes">
							<input id="soliloquy-width" type="text" name="_soliloquy_settings[width]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'width' ) ); ?>" /> &#215; <input id="soliloquy-height" type="text" name="_soliloquy_settings[height]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'height' ) ); ?>" />
							<p class="description"><?php printf( '%s <a class="soliloquy-size-more" href="#">%s</a>', Tgmsp_Strings::get_instance()->strings['slider_size_desc'], Tgmsp_Strings::get_instance()->strings['slider_size_more'] ); ?></p>
							<p id="soliloquy-explain-size" class="description" style="display: none;"><?php printf( '%s <a href="%s" target="_blank">%s</a>.', Tgmsp_Strings::get_instance()->strings['slider_size_explain'], 'http://codex.wordpress.org/Function_Reference/add_image_size', 'add_image_size()' ); ?></p>
						</div>

						<div id="soliloquy-custom-sizes">
						<?php
							$wp_sizes 	= get_intermediate_image_sizes();
							$wp_sizes[] = 'full';
							echo '<select id="soliloquy-custom-size" name="_soliloquy_settings[custom]">';
								foreach ( (array) $wp_sizes as $size ) {
									if ( isset( $_wp_additional_image_sizes[$size] ) ) {
										$width 	= absint( $_wp_additional_image_sizes[$size]['width'] );
										$height = absint( $_wp_additional_image_sizes[$size]['height'] );
									} else {
										$width	= absint( get_option( $size . '_size_w' ) );
										$height	= absint( get_option( $size . '_size_h' ) );
									}

									if ( ! $width && ! $height )
										echo '<option value="' . esc_attr( $size ) . '"' . selected( $size, self::get_custom_field( '_soliloquy_settings', 'custom' ), false ) . '>' . esc_html( $size ) . '</option>';
									else
										echo '<option value="' . esc_attr( $size ) . '"' . selected( $size, self::get_custom_field( '_soliloquy_settings', 'custom' ), false ) . '>' . esc_html( $size . ' (' . $width . ' &#215; ' . $height . ')' ) . '</option>';
								}
							echo '</select>';
						?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['wp_size']; ?></span>
						</div>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_transition', $post ); ?>
				<tr id="soliloquy-transition-box" valign="middle">
					<th scope="row"><label for="soliloquy-transition"><?php echo Tgmsp_Strings::get_instance()->strings['slider_transition']; ?></label></th>
					<td>
					<?php
						$transitions = apply_filters( 'tgmsp_slider_transitions', array( 'fade', 'slide-horizontal', 'slide-vertical' ) );
						echo '<select id="soliloquy-transition" name="_soliloquy_settings[transition]">';
							foreach ( $transitions as $transition ) {
								echo '<option value="' . esc_attr( $transition ) . '"' . selected( $transition, self::get_custom_field( '_soliloquy_settings', 'transition' ), false ) . '>' . esc_html( $transition ) . '</option>';
							}
						echo '</select>';
					?>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_animation', $post ); ?>
				<tr id="soliloquy-animate-box" valign="middle">
					<th scope="row"><label for="soliloquy-animate"><?php echo Tgmsp_Strings::get_instance()->strings['slider_animate']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'animate' ) ) { ?>
							<input id="soliloquy-animate" type="checkbox" name="_soliloquy_settings[animate]" value="1" checked="checked" rel="tester" /> <?php } else { ?>
							<input id="soliloquy-animate" type="checkbox" name="_soliloquy_settings[animate]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'animate' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'animate' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_animate_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_speed', $post ); ?>
				<tr id="soliloquy-speed-box" valign="middle">
					<th scope="row"><label for="soliloquy-speed"><?php echo Tgmsp_Strings::get_instance()->strings['slider_speed']; ?></label></th>
					<td>
						<input id="soliloquy-speed" type="text" name="_soliloquy_settings[speed]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'speed' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_milliseconds']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_duration', $post ); ?>
				<tr id="soliloquy-duration-box" valign="middle">
					<th scope="row"><label for="soliloquy-duration"><?php echo Tgmsp_Strings::get_instance()->strings['slider_animation_dur']; ?></label></th>
					<td>
						<input id="soliloquy-duration" type="text" name="_soliloquy_settings[duration]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'duration' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_milliseconds']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_video', $post ); ?>
				<tr id="soliloquy-video-box" valign="middle">
					<th scope="row"><label for="soliloquy-video"><?php echo Tgmsp_Strings::get_instance()->strings['slider_video']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'video' ) ) { ?>
							<input id="soliloquy-video" type="checkbox" name="_soliloquy_settings[video]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-video" type="checkbox" name="_soliloquy_settings[video]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'video' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'video' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_video_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_preloader', $post ); ?>
				<tr id="soliloquy-preloader-box" valign="middle">
					<th scope="row"><label for="soliloquy-preloader"><?php echo Tgmsp_Strings::get_instance()->strings['slider_preloader']; ?></label></th>
					<td>
						<input id="soliloquy-preloader" type="checkbox" name="_soliloquy_settings[preloader]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'preloader' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'preloader' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_preloader_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_advanced', $post ); ?>
				<tr id="soliloquy-advanced-box" valign="middle">
					<th scope="row"><label for="soliloquy-advanced"><strong><?php echo Tgmsp_Strings::get_instance()->strings['slider_advanced']; ?></strong></label></th>
					<td>
						<input id="soliloquy-advanced" type="checkbox" name="_soliloquy_settings[advanced]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'advanced' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'advanced' ), 1 ); ?> />
						<span class="description"><strong><?php echo Tgmsp_Strings::get_instance()->strings['slider_advanced_desc']; ?></strong></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_navigation', $post ); ?>
				<tr id="soliloquy-navigation-box" valign="middle">
					<th scope="row"><label for="soliloquy-navigation"><?php echo Tgmsp_Strings::get_instance()->strings['slider_prevnext']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'navigation' ) ) { ?>
							<input id="soliloquy-navigation" type="checkbox" name="_soliloquy_settings[navigation]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-navigation" type="checkbox" name="_soliloquy_settings[navigation]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'navigation' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'navigation' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_prevnext_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_control', $post ); ?>
				<tr id="soliloquy-control-box" valign="middle">
					<th scope="row"><label for="soliloquy-control"><?php echo Tgmsp_Strings::get_instance()->strings['slider_control']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'control' ) ) { ?>
							<input id="soliloquy-control" type="checkbox" name="_soliloquy_settings[control]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-control" type="checkbox" name="_soliloquy_settings[control]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'control' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'control' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_control_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_keyboard', $post ); ?>
				<tr id="soliloquy-keyboard-box" valign="middle">
					<th scope="row"><label for="soliloquy-keyboard"><?php echo Tgmsp_Strings::get_instance()->strings['slider_key']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'keyboard' ) ) { ?>
							<input id="soliloquy-keyboard" type="checkbox" name="_soliloquy_settings[keyboard]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-keyboard" type="checkbox" name="_soliloquy_settings[keyboard]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'keyboard' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'keyboard' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_key_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_multi_keyboard', $post ); ?>
				<tr id="soliloquy-multi-keyboard-box" valign="middle">
					<th scope="row"><label for="soliloquy-multi-keyboard"><?php echo Tgmsp_Strings::get_instance()->strings['slider_multi_key']; ?></label></th>
					<td>
						<input id="soliloquy-multi-keyboard" type="checkbox" name="_soliloquy_settings[multi_key]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'multi_key' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'multi_key' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_multi_key_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_mousewheel', $post ); ?>
				<tr id="soliloquy-mousewheel-box" valign="middle">
					<th scope="row"><label for="soliloquy-mousewheel"><?php echo Tgmsp_Strings::get_instance()->strings['slider_mouse']; ?></label></th>
					<td>
						<input id="soliloquy-mousewheel" type="checkbox" name="_soliloquy_settings[mousewheel]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'mousewheel' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'mousewheel' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_mouse_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_pauseplay', $post ); ?>
				<tr id="soliloquy-pauseplay-box" valign="middle">
					<th scope="row"><label for="soliloquy-pauseplay"><?php echo Tgmsp_Strings::get_instance()->strings['slider_pp']; ?></label></th>
					<td>
						<input id="soliloquy-pauseplay" type="checkbox" name="_soliloquy_settings[pauseplay]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'pauseplay' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'pauseplay' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_pp_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_random', $post ); ?>
				<tr id="soliloquy-random-box" valign="middle">
					<th scope="row"><label for="soliloquy-random"><?php echo Tgmsp_Strings::get_instance()->strings['slider_random']; ?></label></th>
					<td>
						<input id="soliloquy-random" type="checkbox" name="_soliloquy_settings[random]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'random' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'random' ), 1 ); ?> />
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_number', $post ); ?>
				<tr id="soliloquy-number-box" valign="middle">
					<th scope="row"><label for="soliloquy-number"><?php echo Tgmsp_Strings::get_instance()->strings['slider_start']; ?></label></th>
					<td>
						<input id="soliloquy-number" type="text" name="_soliloquy_settings[number]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'number' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_start_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_loop', $post ); ?>
				<tr id="soliloquy-loop-box" valign="middle">
					<th scope="row"><label for="soliloquy-loop"><?php echo Tgmsp_Strings::get_instance()->strings['slider_loop']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'loop' ) ) { ?>
							<input id="soliloquy-loop" type="checkbox" name="_soliloquy_settings[loop]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-loop" type="checkbox" name="_soliloquy_settings[loop]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'loop' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'loop' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_loop_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_action', $post ); ?>
				<tr id="soliloquy-action-box" valign="middle">
					<th scope="row"><label for="soliloquy-action"><?php echo Tgmsp_Strings::get_instance()->strings['slider_pause']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'action' ) ) { ?>
							<input id="soliloquy-action" type="checkbox" name="_soliloquy_settings[action]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-action" type="checkbox" name="_soliloquy_settings[action]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'action' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'action' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_pause_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_hover', $post ); ?>
				<tr id="soliloquy-hover-box" valign="middle">
					<th scope="row"><label for="soliloquy-hover"><?php echo Tgmsp_Strings::get_instance()->strings['slider_hover']; ?></label></th>
					<td>
						<input id="soliloquy-hover" type="checkbox" name="_soliloquy_settings[hover]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'hover' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'hover' ), 1 ); ?> />
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_css', $post ); ?>
				<tr id="soliloquy-slider-css-box" valign="middle">
					<th scope="row"><label for="soliloquy-slider-css"><?php echo Tgmsp_Strings::get_instance()->strings['slider_css']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'css' ) ) { ?>
							<input id="soliloquy-slider-css" type="checkbox" name="_soliloquy_settings[css]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-slider-css" type="checkbox" name="_soliloquy_settings[css]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'css' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'css' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_css_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_reverse', $post ); ?>
				<tr id="soliloquy-reverse-box" valign="middle">
					<th scope="row"><label for="soliloquy-reverse"><?php echo Tgmsp_Strings::get_instance()->strings['slider_reverse']; ?></label></th>
					<td>
						<input id="soliloquy-reverse" type="checkbox" name="_soliloquy_settings[reverse]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'reverse' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'reverse' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_reverse_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_smooth', $post ); ?>
				<tr id="soliloquy-smooth-box" valign="middle">
					<th scope="row"><label for="soliloquy-smooth"><?php echo Tgmsp_Strings::get_instance()->strings['slider_smooth']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'smooth' ) ) { ?>
							<input id="soliloquy-smooth" type="checkbox" name="_soliloquy_settings[smooth]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-smooth" type="checkbox" name="_soliloquy_settings[smooth]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'smooth' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'smooth' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_smooth_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_touch', $post ); ?>
				<tr id="soliloquy-touch-box" valign="middle">
					<th scope="row"><label for="soliloquy-touch"><?php echo Tgmsp_Strings::get_instance()->strings['slider_touch']; ?></label></th>
					<td>
					<?php
						if ( '' === self::get_custom_field( '_soliloquy_settings', 'touch' ) ) { ?>
							<input id="soliloquy-touch" type="checkbox" name="_soliloquy_settings[touch]" value="1" checked="checked" /> <?php } else { ?>
							<input id="soliloquy-touch" type="checkbox" name="_soliloquy_settings[touch]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'touch' ) ); ?>" <?php checked( self::get_custom_field( '_soliloquy_settings', 'touch' ), 1 ); ?> /> <?php } ?>
							<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_touch_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_delay', $post ); ?>
				<tr id="soliloquy-delay-box" valign="middle">
					<th scope="row"><label for="soliloquy-delay"><?php echo Tgmsp_Strings::get_instance()->strings['slider_delay']; ?></label></th>
					<td>
						<input id="soliloquy-delay" type="text" name="_soliloquy_settings[delay]" value="<?php echo esc_attr( self::get_custom_field( '_soliloquy_settings', 'delay' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Strings::get_instance()->strings['slider_delay_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_end_of_settings', $post ); ?>
			</tbody>
		</table>

		<?php do_action( 'tgmsp_after_settings_table', $post ); ?>

		<div class="soliloquy-advanced">
			<p><strong><?php echo Tgmsp_Strings::get_instance()->strings['slider_cb']; ?></strong></p>
		</div>
		<?php

		// Ugly hack to make sure wp_editor initializes in ajax calls.
		echo '<!--';
        wp_editor( '', 'invisible_editor_for_initialization' );
        echo '-->';

		do_action( 'tgmsp_after_settings', $post );

	}

	/**
	 * Callback function for Soliloquy instructions.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post Current post object data
	 */
	public function soliloquy_instructions( $post ) {

		$instructions = '<p>' . Tgmsp_Strings::get_instance()->strings['instructions'] . '</p>';
		$instructions .= '<p><code>[soliloquy id="' . $post->ID . '"]</code>';

		if ( 'auto-draft' == $post->post_status )
			$instructions .= '</p>';
		else
			$instructions .= '<br /><code>[soliloquy id="' . $post->post_name . '"]</code></p>';

		$instructions .= '<p>' . Tgmsp_Strings::get_instance()->strings['instructions_more'] . '</p>';
		$instructions .= '<p><code>if ( function_exists( \'soliloquy_slider\' ) ) soliloquy_slider( \'' . $post->ID . '\' );</code>';

		if ( 'auto-draft' == $post->post_status )
			$instructions .= '</p>';
		else
			$instructions .= '<br /><code>if ( function_exists( \'soliloquy_slider\' ) ) soliloquy_slider( \'' . $post->post_name . '\' );</code></p>';


		echo apply_filters( 'tgmsp_slider_instructions', $instructions, $post );

	}

	/**
	 * Save settings post meta fields added to Soliloquy metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The post ID
	 * @param object $post Current post object data
	 */
	public function save_slider_settings( $post_id, $post ) {

		/** Bail out if we fail a security check */
		if ( ! isset( $_POST[sanitize_key( 'soliloquy_settings_script' )] ) || ! wp_verify_nonce( $_POST[sanitize_key( 'soliloquy_settings_script' )], 'soliloquy_settings_script' ) )
			return $post_id;

		/** Bail out if running an autosave, ajax or a cron */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		/** Bail out if the user doesn't have the correct permissions to update the slider */
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		/** All security checks passed, so let's store our data */
		$settings = isset( $_POST['_soliloquy_settings'] ) ? $_POST['_soliloquy_settings'] : '';

		/** Sanitize all data before updating */
		if ( isset( $_POST['_soliloquy_settings']['default'] ) && 'default' == $_POST['_soliloquy_settings']['default'] ) {
			$settings['default']	= esc_attr( $_POST['_soliloquy_settings']['default'] );
			$settings['width']		= preg_match( '|^\d+%{0,1}$|', trim( $_POST['_soliloquy_settings']['width'] ) ) ? trim( $_POST['_soliloquy_settings']['width'] ) : 600;
			$settings['height']		= preg_match( '|^\d+%{0,1}$|', trim( $_POST['_soliloquy_settings']['height'] ) ) ? trim( $_POST['_soliloquy_settings']['height'] ) : 300;
			$settings['custom']		= false;
		} else {
			$settings['default']	= esc_attr( $_POST['_soliloquy_settings']['default'] );
			$settings['width']		= false;
			$settings['height']		= false;
			$settings['custom']		= esc_attr( $_POST['_soliloquy_settings']['custom'] );
		}

		$settings['transition']	= preg_replace( '#[^a-z0-9-_]#', '', $_POST['_soliloquy_settings']['transition'] );
		$settings['animate']	= isset( $_POST['_soliloquy_settings']['animate'] ) ? 1 : 0;
		$settings['speed']		= absint( $_POST['_soliloquy_settings']['speed'] ) ? absint( $_POST['_soliloquy_settings']['speed'] ) : 7000;
		$settings['duration']	= absint( $_POST['_soliloquy_settings']['duration'] ) ? absint( $_POST['_soliloquy_settings']['duration'] ) : 600;
		$settings['video']		= isset( $_POST['_soliloquy_settings']['video'] ) ? 1 : 0;
		$settings['preloader']	= isset( $_POST['_soliloquy_settings']['preloader'] ) ? 1 : 0;
		$settings['advanced']	= isset( $_POST['_soliloquy_settings']['advanced'] ) ? 1 : 0;
		$settings['navigation']	= isset( $_POST['_soliloquy_settings']['navigation'] ) ? 1 : 0;
		$settings['control']	= isset( $_POST['_soliloquy_settings']['control'] ) ? 1 : 0;
		$settings['keyboard']	= isset( $_POST['_soliloquy_settings']['keyboard'] ) ? 1 : 0;
		$settings['multi_key']	= isset( $_POST['_soliloquy_settings']['multi_key'] ) ? 1 : 0;
		$settings['mousewheel']	= isset( $_POST['_soliloquy_settings']['mousewheel'] ) ? 1 : 0;
		$settings['pauseplay']	= isset( $_POST['_soliloquy_settings']['pauseplay'] ) ? 1 : 0;
		$settings['random']		= isset( $_POST['_soliloquy_settings']['random'] ) ? 1 : 0;
		$settings['number']		= absint( $_POST['_soliloquy_settings']['number'] ) ? absint( $_POST['_soliloquy_settings']['number'] ) : 0;
		$settings['loop']		= isset( $_POST['_soliloquy_settings']['loop'] ) ? 1 : 0;
		$settings['action']		= isset( $_POST['_soliloquy_settings']['action'] ) ? 1 : 0;
		$settings['hover']		= isset( $_POST['_soliloquy_settings']['hover'] ) ? 1 : 0;
		$settings['css']		= isset( $_POST['_soliloquy_settings']['css'] ) ? 1 : 0;
		$settings['reverse']	= isset( $_POST['_soliloquy_settings']['reverse'] ) ? 1 : 0;
		$settings['smooth']		= isset( $_POST['_soliloquy_settings']['smooth'] ) ? 1 : 0;
		$settings['touch']		= isset( $_POST['_soliloquy_settings']['touch'] ) ? 1 : 0;
		$settings['delay']		= absint( $_POST['_soliloquy_settings']['delay'] ) ? absint( $_POST['_soliloquy_settings']['delay'] ) : 0;

		/** Update the type of slider */
		$settings['type']		= isset( $_POST['_soliloquy_settings']['type'] ) ? esc_attr( $_POST['_soliloquy_settings']['type'] ) : 'default';

		/** Provide hook if users want to save additional settings added into this area */
		do_action( 'tgmsp_save_slider_settings', $settings, $post_id, $post );

		/** Allow devs to filter the settings */
		$settings = apply_filters( 'tgmsp_slider_settings', $settings, $post_id, $post );

		/** Update post meta with sanitized values */
		update_post_meta( $post_id, '_soliloquy_settings', $settings );

	}

	/**
	 * Outputs any error messages when verifying license keys.
	 *
	 * @since 1.0.0
	 *
	 * @global array $soliloquy_license Soliloquy license information
	 */
	public function admin_notices() {

		global $soliloquy_license;
		$current_screen = get_current_screen();

		if ( Tgmsp::is_soliloquy_screen() && current_user_can( 'manage_options' ) ) {
			/** No license has been entered, so encourage users to enter the license */
			if ( ! isset( $soliloquy_license['license'] ) && 'soliloquy_page_soliloquy-settings' !== $current_screen->id )
				add_settings_error( 'tgmsp', 'tgmsp-no-key', sprintf( Tgmsp_Strings::get_instance()->strings['no_license'], add_query_arg( array( 'post_type' => 'soliloquy', 'page' => 'soliloquy-settings' ), admin_url( 'edit.php' ) ) ), 'updated' );

			/** The license has been deactivated, so advise users */
			if ( isset( $_GET['deactivate_license'] ) && $_GET['deactivate_license'] )
				add_settings_error( 'tgmsp', 'tgmsp-hold-upgrades', Tgmsp_Strings::get_instance()->strings['license_deactivated'], 'updated' );

			/** Allow settings notices to be filtered */
			apply_filters( 'tgmsp_output_notices', settings_errors( 'tgmsp' ) );
		}

	}

	/**
	 * Add Settings page to plugin action links in the Plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Default plugin action links
	 * @return array $links Amended plugin action links
	 */
	public function settings_link( $links ) {

		$setting_link = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => 'soliloquy', 'page' => 'soliloquy-settings' ), admin_url( 'edit.php' ) ), Tgmsp_Strings::get_instance()->strings['plugin_settings'] );
		array_unshift( $links, $setting_link );

		return $links;

	}

	/**
	 * Helper function to get custom field values for the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id The current Soliloquy ID
	 * @global object $post The current Soliloquy post type object
	 * @param string $field The custom field name to retrieve
	 * @param string|int $setting The setting or array index to retrieve within the custom field
	 * @param int $index The array index number to retrieve
	 * @param int $postid The current post ID
	 * @return string|boolean The custom field value on success, false on failure
	 */
	public static function get_custom_field( $field, $setting = null, $index = null, $postid = null ) {

		global $id, $post;

		/** Do nothing if the field is not set */
		if ( ! $field )
			return false;

		/** Get the current Soliloquy ID */
		if ( is_null( $postid ) )
			$post_id = ( null === $id ) ? $post->ID : $id;
		else
			$post_id = absint( $postid );

		$custom_field = get_post_meta( $post_id, $field, true );

		/** Return the sanitized field and setting if an array, otherwise return the sanitized field */
		if ( $custom_field && isset( $custom_field[$setting] ) ) {
			if ( is_int( $index ) && is_array( $custom_field[$setting] ) )
				return stripslashes_deep( $custom_field[$setting][$index] );
			else
				return stripslashes_deep( $custom_field[$setting] );
		} elseif ( is_array( $custom_field ) ) {
			return stripslashes_deep( $custom_field );
		} else {
			return stripslashes( $custom_field );
		}

		return false;

	}

	/**
	 * Method for holding the link types for Soliloquy.
	 *
	 * @since 1.3.0
	 */
	public function link_types() {

		$types = array(
			array(
				'name' => Tgmsp_Strings::get_instance()->strings['image_link_normal'],
				'slug' => 'normal'
			),
			array(
				'name' => Tgmsp_Strings::get_instance()->strings['image_link_video'],
				'slug' => 'video'
			)
		);

		return apply_filters( 'tgmsp_link_types', $types );

	}

	/**
	 * Outputs the custom upload UI for uploading and managing slides in Soliloquy.
	 *
	 * @since 1.5.0
	 */
	public function do_upload_output() {

		global $post;
		?>
		<div id="soliloquy-upload-ui-wrapper">
			<div id="soliloquy-upload-ui" class="soliloquy-image-meta" style="display: none;">
				<div class="media-modal wp-core-ui">
					<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>
					<div class="media-modal-content">
						<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu soliloquy-meta-wrap">
							<div class="media-frame-title">
								<h1><?php echo Tgmsp_Strings::get_instance()->strings['upload_ui']; ?></h1>
							</div>
							<div class="media-frame-router">
								<div class="media-router">
									<a href="#" class="media-menu-item active" data-soliloquy-content="soliloquy-image-slides"><?php echo Tgmsp_Strings::get_instance()->strings['image']; ?></a>
									<a href="#" class="media-menu-item" data-soliloquy-content="soliloquy-gallery-slides"><?php echo Tgmsp_Strings::get_instance()->strings['gallery']; ?></a>
									<a href="#" class="media-menu-item" data-soliloquy-content="soliloquy-video-slides"><?php echo Tgmsp_Strings::get_instance()->strings['video']; ?></a>
									<a href="#" class="media-menu-item" data-soliloquy-content="soliloquy-html-slides"><?php echo Tgmsp_Strings::get_instance()->strings['html']; ?></a>
								</div><!-- end .media-router -->
							</div><!-- end .media-frame-router -->
							<!-- begin content for uploading image slides -->
							<div id="soliloquy-image-slides">
								<div class="media-frame-content">
									<div class="attachments-browser">
										<div class="soliloquy-meta attachments soliloquy-image-ui soliloquy-ui-content">
											<div class="upload-form-holder">
												<?php media_upload_form(); ?>
												<script type="text/javascript">var post_id = <?php echo $post->ID; ?>, shortform = 3;</script>
												<input type="hidden" name="post_id" id="post_id" value="<?php echo $post->ID; ?>" />
												<div id="media-items" class="hide-if-no-js media-upload-form"></div>
											</div>
										</div><!-- end .soliloquy-meta -->
										<div class="media-sidebar">
											<div class="soliloquy-meta-sidebar">
												<h3><?php echo Tgmsp_Strings::get_instance()->strings['media_sb_tips']; ?></h3>
												<strong><?php echo Tgmsp_Strings::get_instance()->strings['uploading_images']; ?></strong>
												<p><?php echo Tgmsp_Strings::get_instance()->strings['uploading_images_desc']; ?></p>
											</div><!-- end .soliloquy-meta-sidebar -->
										</div><!-- end .media-sidebar -->
									</div><!-- end .attachments-browser -->
								</div><!-- end .media-frame-content -->
							</div><!-- end #soliloquy-image-slides -->
							<!-- end content for uploading image slides -->
							<!-- begin content for inserting slides from media library -->
							<div id="soliloquy-gallery-slides" style="display: none;">
								<div class="media-frame-content">
									<div class="attachments-browser">
										<div class="media-toolbar soliloquy-library-toolbar">
											<div class="media-toolbar-primary">
												<input type="search" placeholder="<?php echo Tgmsp_Strings::get_instance()->strings['search']; ?>" id="soliloquy-gallery-search" class="search" value="" />
											</div>
											<div class="media-toolbar-secondary">
												<a class="button media-button button-large button-secodary soliloquy-load-library" href="#" data-soliloquy-offset="20"><?php echo Tgmsp_Strings::get_instance()->strings['load_images']; ?></a>
											</div>
										</div>
										<?php $library = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => 20 ) ); ?>
										<?php if ( $library ) : ?>
										<ul class="attachments soliloquy-gallery">
										<?php foreach ( (array) $library as $image ) : $class = $post->ID == $image->post_parent ? ' selected soliloquy-in-slider' : ''; ?>
											<li class="attachment<?php echo $class; ?>" data-attachment-id="<?php echo absint( $image->ID ); ?>">
												<div class="attachment-preview landscape">
													<div class="thumbnail">
														<div class="centered">
															<?php $src = wp_get_attachment_image_src( $image->ID, 'thumbnail' ); ?>
															<img src="<?php echo esc_url( $src[0] ); ?>" />
														</div>
													</div>
													<a class="check" href="#"><div class="media-modal-icon"></div></a>
												</div>
											</li>
										<?php endforeach; ?>
										</ul><!-- end .soliloquy-meta -->
										<?php endif; ?>
										<div class="media-sidebar">
											<div class="soliloquy-meta-sidebar">
												<h3><?php echo Tgmsp_Strings::get_instance()->strings['media_sb_tips']; ?></h3>
												<strong><?php echo Tgmsp_Strings::get_instance()->strings['media_img_seo']; ?></strong>
												<p><?php echo Tgmsp_Strings::get_instance()->strings['media_img_seo_desc']; ?></p>
											</div><!-- end .soliloquy-meta-sidebar -->
										</div><!-- end .media-sidebar -->
									</div><!-- end .attachments-browser -->
								</div><!-- end .media-frame-content -->
							</div><!-- end #soliloquy-image-slides -->
							<!-- end content for inserting slides from media library -->
							<!-- begin content for inserting video slides -->
							<div id="soliloquy-video-slides" style="display: none;">
								<div class="media-frame-content">
									<div class="attachments-browser">
										<div class="soliloquy-meta attachments soliloquy-image-ui soliloquy-ui-content">
											<p class="no-margin-top"><a href="#" class="soliloquy-add-video-slide button button-large button-primary" data-soliloquy-video-number="1" title="<?php echo Tgmsp_Strings::get_instance()->strings['add_video']; ?>"><?php echo Tgmsp_Strings::get_instance()->strings['add_video']; ?></a></p>
											<div class="soliloquy-video-info">
												<p class="no-margin-top center"><strong><?php echo Tgmsp_Strings::get_instance()->strings['video_link_info']; ?></strong></p>
												<div class="soliloquy-accepted-urls">
													<div class="soliloquy-left">
														<span><strong><?php echo Tgmsp_Strings::get_instance()->strings['youtube_urls']; ?></strong></span>
														<span>youtube.com/v/{vidid}</span>
														<span>youtube.com/vi/{vidid}</span>
														<span>youtube.com/?v={vidid}</span>
														<span>youtube.com/?vi={vidid}</span>
														<span>youtube.com/watch?v={vidid}</span>
														<span>youtube.com/watch?vi={vidid}</span>
														<span>youtu.be/{vidid}</span>
													</div>
													<div class="soliloquy-right">
														<span><strong><?php echo Tgmsp_Strings::get_instance()->strings['vimeo_urls']; ?></strong></span>
														<span>vimeo.com/{vidid}</span>
														<span>vimeo.com/groups/tvc/videos/{vidid}</span>
														<span>player.vimeo.com/video/{vidid}</span>
													</div>
												</div>
											</div>
										</div><!-- end .soliloquy-meta -->
										<div class="media-sidebar">
											<div class="soliloquy-meta-sidebar">
												<h3><?php echo Tgmsp_Strings::get_instance()->strings['media_sb_tips']; ?></h3>
												<strong><?php echo Tgmsp_Strings::get_instance()->strings['uploading_vid']; ?></strong>
												<p><?php echo Tgmsp_Strings::get_instance()->strings['uploading_vid_desc']; ?></p>
											</div><!-- end .soliloquy-meta-sidebar -->
										</div><!-- end .media-sidebar -->
									</div><!-- end .attachments-browser -->
								</div><!-- end .media-frame-content -->
							</div><!-- end #soliloquy-image-slides -->
							<!-- end content for inserting video slides -->
							<!-- begin content for inserting HTML slides -->
							<div id="soliloquy-html-slides" style="display: none;">
								<div class="media-frame-content">
									<div class="attachments-browser">
										<div class="soliloquy-meta attachments soliloquy-image-ui soliloquy-ui-content">
											<p class="no-margin-top"><a href="#" class="soliloquy-add-html-slide button button-large button-primary" data-soliloquy-html-number="1" title="<?php echo Tgmsp_Strings::get_instance()->strings['add_html']; ?>"><?php echo Tgmsp_Strings::get_instance()->strings['add_html']; ?></a></p>
										</div><!-- end .soliloquy-meta -->
										<div class="media-sidebar">
											<div class="soliloquy-meta-sidebar">
												<h3><?php echo Tgmsp_Strings::get_instance()->strings['media_sb_tips']; ?></h3>
												<strong><?php echo Tgmsp_Strings::get_instance()->strings['uploading_html']; ?></strong>
												<p><?php echo Tgmsp_Strings::get_instance()->strings['uploading_html_desc']; ?></p>
											</div><!-- end .soliloquy-meta-sidebar -->
										</div><!-- end .media-sidebar -->
									</div><!-- end .attachments-browser -->
								</div><!-- end .media-frame-content -->
							</div><!-- end #soliloquy-image-slides -->
							<!-- end content for inserting HTML slides -->
							<div class="media-frame-toolbar">
								<div class="media-toolbar">
									<div class="media-toolbar-primary">
										<a href="#" class="soliloquy-media-insert button media-button button-large button-primary media-button-insert" title="<?php echo Tgmsp_Strings::get_instance()->strings['insert_slides']; ?>"><?php echo Tgmsp_Strings::get_instance()->strings['insert_slides']; ?></a>
									</div><!-- end .media-toolbar-primary -->
								</div><!-- end .media-toolbar -->
							</div><!-- end .media-frame-toolbar -->
						</div><!-- end .media-frame -->
					</div><!-- end .media-modal-content -->
				</div><!-- end .media-modal -->
				<div class="media-modal-backdrop"></div>
			</div><!-- end .soliloquy-image-meta -->
		</div><!-- end #soliloquy-upload-ui-wrapper-->
		<?php

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