<?php
/**
 * Ajax class for Soliloquy.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Ajax {

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

		add_action( 'wp_ajax_soliloquy_link_search', array( $this, 'link_search' ) );
		add_action( 'wp_ajax_soliloquy_refresh_images', array( $this, 'refresh_images' ) );
		add_action( 'wp_ajax_soliloquy_iframe_refresh_images', array ( $this, 'refresh_images' ) );
		add_action( 'wp_ajax_soliloquy_sort_images', array( $this, 'sort_images' ) );
		add_action( 'wp_ajax_soliloquy_remove_images', array( $this, 'remove_images' ) );
		add_action( 'wp_ajax_soliloquy_update_meta', array( $this, 'update_meta' ) );
		add_action( 'wp_ajax_soliloquy_load_library', array( $this, 'load_library' ) );
		add_action( 'wp_ajax_soliloquy_library_search', array( $this, 'library_search' ) );
		add_action( 'wp_ajax_soliloquy_insert_slides', array( $this, 'insert_slides' ) );

	}

	/**
	 * Returns search results from the internal content linking feature.
	 *
	 * @since 1.0.0
	 */
	public function link_search() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_linking', 'nonce' );

		$args = array();

		if ( isset( $_POST['search'] ) ) {
			$args['s'] = stripslashes( $_POST['search'] );
			$args['pagenum'] = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		}

		require( ABSPATH . WPINC . '/class-wp-editor.php' );
		$results['links'] = _WP_Editors::wp_link_query( $args );

		/** Do nothing if no search results have been found */
		if ( ! isset( $results ) )
			die;

		echo json_encode( $results );
		die;

	}

	/**
	 * Ajax callback to refresh attachment images for the current Soliloquy.
	 *
	 * @since 1.0.0
	 */
	public function refresh_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_uploader', 'nonce' );

		/** Prepare our variables */
		$response['images'] = array(); // This will hold our images as an object titled 'images'
		$images 			= array();
		$html 				= ''; // This will hold the HTML for our metadata structure
		$args 				= array(
			'orderby' 			=> 'menu_order',
			'order' 			=> 'ASC',
			'post_type' 		=> 'attachment',
			'post_parent' 		=> $_POST['id'],
			'post_status' 		=> null,
			'posts_per_page' 	=> -1
		);

		/** Get all of the image attachments to the Soliloquy */
		$attachments = get_posts( $args );

		/** Loop through the attachments and store the data */
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				/** Get attachment metadata for each attachment */
				$thumb = wp_get_attachment_image_src( $attachment->ID, 'soliloquy-thumb' );

				/** Store data in an array to send back to the script as on object */
				$images[] = apply_filters( 'tgmsp_ajax_refresh_callback', array(
					'id' 		=> $attachment->ID,
					'src' 		=> $thumb[0],
					'width' 	=> $thumb[1],
					'height' 	=> $thumb[2],
					'title' 	=> $attachment->post_title,
					'alt' 		=> get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
					'link' 		=> get_post_meta( $attachment->ID, '_soliloquy_image_link', true ),
					'linktitle' => get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true ),
					'linktab' 	=> get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ),
					'linkcheck' => checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ),
					'caption' 	=> $attachment->post_excerpt,
					'mime'		=> $attachment->post_mime_type,
					'content'   => $attachment->post_content
				), $attachment );
			}
		}

		/** Now let's loop through our images and build out the HTML structure */
		if ( $images ) {
			foreach ( $images as $image ) {
				switch ( $image['mime'] ) :
					default :
						$html .= '<li id="' . $image['id'] . '" class="soliloquy-image attachment-' . $image['id'] . '">';
							$html .= '<img src="' . $image['src'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '" />';
							$html .= '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
							$html .= '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
							$html .= '<div id="meta-' . $image['id'] . '" class="soliloquy-image-meta" style="display: none;">';
								$html .= '<div class="media-modal wp-core-ui">';
									$html .= '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
									$html .= '<div class="media-modal-content">';
										$html .= '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
											$html .= '<div class="media-frame-title">';
												$html .= '<h1>' . Tgmsp_Strings::get_instance()->strings['update_meta'] . '</h1>';
											$html .= '</div>';
											$html .= '<div class="media-frame-content">';
												$html .= '<div class="attachments-browser">';
													$html .= '<div class="soliloquy-meta attachments">';
														if ( array_key_exists( 'before_image_meta_table', $image ) )
															foreach ( (array) $image['before_image_meta_table'] as $data )
																$html .= $data;
														$html .= '<table id="soliloquy-meta-table-' . $image['id'] . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $image['id'] . '" data-slide-type="image">';
															$html .= '<tbody>';
																if ( array_key_exists( 'before_image_title', $image ) )
																	foreach ( (array) $image['before_image_title'] as $data )
																		$html .= $data;
																$html .= '<tr id="soliloquy-title-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-title-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_title'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<input id="soliloquy-title-' . $image['id'] . '" class="soliloquy-title" type="text" name="_soliloquy_uploads[title]" value="' . $image['title'] . '" />';
																	$html .= '</td>';
																$html .= '</tr>';
																if ( array_key_exists( 'before_image_alt', $image ) )
																	foreach ( (array) $image['before_image_alt'] as $data )
																		$html .= $data;
																$html .= '<tr id="soliloquy-alt-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-alt-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_alt'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<input id="soliloquy-alt-' . $image['id'] . '" class="soliloquy-alt" type="text" name="_soliloquy_uploads[alt]" value="' . $image['alt'] . '" />';
																	$html .= '</td>';
																$html .= '</tr>';
																if ( array_key_exists( 'before_image_link', $image ) )
																	foreach ( (array) $image['before_image_link'] as $data )
																		$html .= $data;
																$html .= '<tr id="soliloquy-link-box-' . $image['id'] . '" class="soliloquy-link-cell" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-link-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_link'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<div class="soliloquy-link-normal-wrap soliloquy-top">';
																			$html .= '<p class="no-margin"><label class="soliloquy-link-url" for="soliloquy-link-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_url'] . '</label>';
																			$html .= '<input id="soliloquy-link-' . $image['id'] . '" class="soliloquy-link" type="text" name="_soliloquy_uploads[link]" value="' . $image['link'] . '" /></p>';
																			$html .= '<p class="no-margin"><label class="soliloquy-link-title-label" for="soliloquy-link-title-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_url_title'] . '</label>';
																			$html .= '<input id="soliloquy-link-title-' . $image['id'] . '" class="soliloquy-link-title" type="text" name="_soliloquy_uploads[link_title]" value="' . $image['linktitle'] . '" /></p>';
																			$html .= '<p><label class="soliloquy-link-tab-label" for="soliloquy-link-tab-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['tab'] . '</label><input id="soliloquy-link-tab-' . $image['id'] . '" class="soliloquy-link-check" type="checkbox" name="_soliloquy_uploads[link_tab]" value="' . $image['linktab'] . '"' . $image['linkcheck'] . ' />';
																			$html .= '<span class="description"> ' . Tgmsp_Strings::get_instance()->strings['new_tab'] . '</span></p>';
																			$html .= '<a id="soliloquy-link-existing" class="button button-secondary" href="#">' . Tgmsp_Strings::get_instance()->strings['link_existing'] . '</a>';
																			$html .= '<div id="soliloquy-internal-linking-' . $image['id'] . '" style="display: none;">';
																				$html .= '<label class="soliloquy-search-label" for="soliloquy-search-links-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['search'] . '</label>';
																				$html .= '<input class="soliloquy-search" type="text" id="soliloquy-search-links-' . $image['id'] . '" value="" />';
																				$html .= '<div class="soliloquy-search-results">';
																					$html .= '<ul id="soliloquy-list-links-' . $image['id'] . '" class="soliloquy-results-list">';
																						$html .= '<li class="soliloquy-no-results"><span>' . Tgmsp_Strings::get_instance()->strings['no_results_default'] . '</span></li>';
																					$html .= '</ul>';
																				$html .= '</div>';
																			$html .= '</div>';
																		$html .= '</div>';
																	$html .= '</td>';
																$html .= '</tr>';
																if ( array_key_exists( 'before_image_caption', $image ) )
																	foreach ( (array) $image['before_image_caption'] as $data )
																		$html .= $data;
																$html .= '<tr id="soliloquy-caption-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-caption-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['image_caption'] . '</label></th>';
																	$html .= '<td>';
																	    ob_start();
																	    wp_editor( $image['caption'], 'soliloquy-caption-' . $image['id'], array( 'wpautop' => true, 'media_buttons' => false, 'textarea_rows' => '6', 'textarea_name' => '_soliloquy_uploads[caption]', 'tabindex' => '100', 'tinymce' => false, 'teeny' => true, 'quicktags' => array('buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'), 'dfw' => false ) );
																	    $contents = ob_get_clean();
																	    $html .= $contents;
																		$html .= '<span class="description">' . Tgmsp_Strings::get_instance()->strings['image_caption_desc'] . '</span>';
																	$html .= '</td>';
																$html .= '</tr>';
																if ( array_key_exists( 'after_meta_defaults', $image ) )
																	foreach ( (array) $image['after_meta_defaults'] as $data )
																		$html .= $data;
															$html .= '</tbody>';
														$html .= '</table>';
														if ( array_key_exists( 'after_image_meta_table', $image ) )
															foreach ( (array) $image['after_image_meta_table'] as $data )
																$html .= $data;
													$html .= '</div><!-- end .soliloquy-meta -->';
													$html .= '<div class="media-sidebar">';
														$html .= '<div class="soliloquy-meta-sidebar">';
															$html .= '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_seo'] . '</strong>';
															$html .= '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_seo_desc'] . '</p>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_links'] . '</strong>';
															$html .= '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_links_desc'] . '</p>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_img_cap'] . '</strong>';
															$html .= '<p>' . Tgmsp_Strings::get_instance()->strings['media_img_cap_desc'] . '</p>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_sb_se'] . '</strong>';
															$html .= '<p class="no-margin">' . Tgmsp_Strings::get_instance()->strings['media_sb_se_desc'] . '</p>';
														$html .= '</div><!-- end .soliloquy-meta-sidebar -->';
													$html .= '</div><!-- end .media-sidebar -->';
												$html .= '</div><!-- end .attachments-browser -->';
											$html .= '</div><!-- end .media-frame-content -->';
											$html .= '<div class="media-frame-toolbar">';
												$html .= '<div class="media-toolbar">';
													$html .= '<div class="media-toolbar-primary">';
														$html .= '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
													$html .= '</div><!-- end .media-toolbar-primary -->';
												$html .= '</div><!-- end .media-toolbar -->';
											$html .= '</div><!-- end .media-frame-toolbar -->';
										$html .= '</div><!-- end .media-frame -->';
									$html .= '</div><!-- end .media-modal-content -->';
								$html .= '</div><!-- end .media-modal -->';
								$html .= '<div class="media-modal-backdrop"></div>';
							$html .= '</div><!-- end .soliloquy-image-meta -->';
						$html .= '</li>';
					break;
					case 'soliloquy/video' :
						$html .= '<li id="' . $image['id'] . '" class="soliloquy-image soliloquy-video attachment-' . $image['id'] . '" data-full-delete="true">';
							$html .= '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
							$html .= '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
							$html .= '<div id="meta-' . $image['id'] . '" class="soliloquy-image-meta" style="display: none;">';
								$html .= '<div class="media-modal wp-core-ui">';
									$html .= '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
									$html .= '<div class="media-modal-content">';
										$html .= '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
											$html .= '<div class="media-frame-title">';
												$html .= '<h1>' . Tgmsp_Strings::get_instance()->strings['update_video_meta'] . '</h1>';
											$html .= '</div>';
											$html .= '<div class="media-frame-content">';
												$html .= '<div class="attachments-browser">';
													$html .= '<div class="soliloquy-meta attachments">';
														$html = apply_filters( 'tgmsp_before_video_meta_table', $html, $image );
														$html .= '<table id="soliloquy-meta-table-' . $image['id'] . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $image['id'] . '" data-slide-type="video">';
															$html .= '<tbody>';
																$html = apply_filters( 'tgmsp_before_video_title', $html, $image );
																$html .= '<tr id="soliloquy-video-title-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-video-title-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['video_slide'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<input id="soliloquy-video-title-' . $image['id'] . '" class="soliloquy-video-title" type="text" name="_soliloquy_uploads[video_title]" value="' . esc_attr( strip_tags( $image['title'] ) ) . '" />';
																	$html .= '</td>';
																$html .= '</tr>';
																$html = apply_filters( 'tgmsp_before_video_url', $html, $image );
																$html .= '<tr id="soliloquy-video-url-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-video-url-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['video_title'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<input id="soliloquy-video-url-' . $image['id'] . '" class="soliloquy-video-url" type="text" name="_soliloquy_uploads[video_url]" value="' . esc_url( $image['content'] ) . '" />';
																	$html .= '</td>';
																$html .= '</tr>';
																$html = apply_filters( 'tgmsp_before_video_caption', $html, $image );
																$html .= '<tr id="soliloquy-video-caption-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-video-caption-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['video_caption'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<textarea id="soliloquy-video-caption-' . $image['id'] . '" class="soliloquy-video-caption" rows="3" name="_soliloquy_uploads[video_caption]">' . esc_html( $image['caption'] ) . '</textarea>';
																		$html .= '<span class="description">' . Tgmsp_Strings::get_instance()->strings['image_caption_desc'] . '</span>';
																	$html .= '</td>';
																$html .= '</tr>';
																$html = apply_filters( 'tgmsp_after_video_meta_defaults', $html, $image );
															$html .= '</tbody>';
														$html .= '</table>';
														$html = apply_filters( 'tgmsp_after_video_meta_table', $html, $image );
													$html .= '</div><!-- end .soliloquy-meta -->';
													$html .= '<div class="media-sidebar">';
														$html .= '<div class="soliloquy-meta-sidebar">';
															$html .= '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_video_help'] . '</strong>';
															$html .= '<p>' . Tgmsp_Strings::get_instance()->strings['media_video_help_desc'] . '</p>';
														$html .= '</div><!-- end .soliloquy-meta-sidebar -->';
													$html .= '</div><!-- end .media-sidebar -->';
												$html .= '</div><!-- end .attachments-browser -->';
											$html .= '</div><!-- end .media-frame-content -->';
											$html .= '<div class="media-frame-toolbar">';
												$html .= '<div class="media-toolbar">';
													$html .= '<div class="media-toolbar-primary">';
														$html .= '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
													$html .= '</div><!-- end .media-toolbar-primary -->';
												$html .= '</div><!-- end .media-toolbar -->';
											$html .= '</div><!-- end .media-frame-toolbar -->';
										$html .= '</div><!-- end .media-frame -->';
									$html .= '</div><!-- end .media-modal-content -->';
								$html .= '</div><!-- end .media-modal -->';
								$html .= '<div class="media-modal-backdrop"></div>';
							$html .= '</div><!-- end .soliloquy-image-meta -->';
							$html .= '<div class="soliloquy-video-wrap">';
								$html .= '<div class="soliloquy-video-inside">';
									$html .= '<div class="soliloquy-video-table">';
										$html .= '<h4 class="no-margin">' . esc_html( $image['title'] ) . '</h4>';
										$html .= '<span class="soliloquy-mini">' . Tgmsp_Strings::get_instance()->strings['video_slide_mini'] . '</span>';
									$html .= '</div>';
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</li>';
					break;
					case 'soliloquy/html' :
						$html .= '<li id="' . $image['id'] . '" class="soliloquy-image soliloquy-html attachment-' . $image['id'] . '" data-full-delete="true">';
							$html .= '<a href="#" class="remove-image" title="' . Tgmsp_Strings::get_instance()->strings['remove_image'] . '"></a>';
							$html .= '<a href="#" class="modify-image" title="' . Tgmsp_Strings::get_instance()->strings['modify_image'] . '"></a>';
							$html .= '<div id="meta-' . $image['id'] . '" class="soliloquy-image-meta" style="display: none;">';
								$html .= '<div class="media-modal wp-core-ui">';
									$html .= '<a class="media-modal-close" href="#"><span class="media-modal-icon"></span></a>';
									$html .= '<div class="media-modal-content">';
										$html .= '<div class="media-frame soliloquy-media-frame wp-core-ui hide-menu hide-router soliloquy-meta-wrap">';
											$html .= '<div class="media-frame-title">';
												$html .= '<h1>' . Tgmsp_Strings::get_instance()->strings['update_html_meta'] . '</h1>';
											$html .= '</div>';
											$html .= '<div class="media-frame-content">';
												$html .= '<div class="attachments-browser">';
													$html .= '<div class="soliloquy-meta attachments">';
														$html = apply_filters( 'tgmsp_before_html_meta_table', $html, $attachment );
														$html .= '<table id="soliloquy-meta-table-' . $image['id'] . '" class="form-table soliloquy-meta-table" data-attachment-id="' . $image['id'] . '" data-slide-type="html">';
															$html .= '<tbody>';
																$html = apply_filters( 'tgmsp_before_html_title', $html, $attachment );
																$html .= '<tr id="soliloquy-html-title-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-html-title-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['html_slide'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<input id="soliloquy-html-title-' . $image['id'] . '" class="soliloquy-html-title" type="text" name="_soliloquy_uploads[html_title]" value="' . esc_attr( strip_tags( $image['title'] ) ) . '" />';
																	$html .= '</td>';
																$html .= '</tr>';
																$html = apply_filters( 'tgmsp_before_html_code', $html, $attachment );
																$html .= '<tr id="soliloquy-html-code-box-' . $image['id'] . '" valign="middle">';
																	$html .= '<th scope="row"><label for="soliloquy-html-code-' . $image['id'] . '">' . Tgmsp_Strings::get_instance()->strings['html_code'] . '</label></th>';
																	$html .= '<td>';
																		$html .= '<textarea id="soliloquy-html-code-' . $image['id'] . '" class="soliloquy-html-code" name="_soliloquy_uploads[html_code]">' . $image['content'] . '</textarea>';
																	$html .= '</td>';
																$html .= '</tr>';
																$html = apply_filters( 'tgmsp_after_html_meta_defaults', $html, $attachment );
															$html .= '</tbody>';
														$html .= '</table>';
														$html = apply_filters( 'tgmsp_after_html_meta_table', $html, $attachment );
													$html .= '</div><!-- end .soliloquy-meta -->';
													$html .= '<div class="media-sidebar">';
														$html .= '<div class="soliloquy-meta-sidebar">';
															$html .= '<h3>' . Tgmsp_Strings::get_instance()->strings['media_sb_tips'] . '</h3>';
															$html .= '<strong>' . Tgmsp_Strings::get_instance()->strings['media_html_help'] . '</strong>';
															$html .= '<p>' . Tgmsp_Strings::get_instance()->strings['media_html_help_desc'] . '</p>';
														$html .= '</div><!-- end .soliloquy-meta-sidebar -->';
													$html .= '</div><!-- end .media-sidebar -->';
												$html .= '</div><!-- end .attachments-browser -->';
											$html .= '</div><!-- end .media-frame-content -->';
											$html .= '<div class="media-frame-toolbar">';
												$html .= '<div class="media-toolbar">';
													$html .= '<div class="media-toolbar-primary">';
														$html .= '<a href="#" class="soliloquy-meta-submit button media-button button-large button-primary media-button-insert" title="' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Strings::get_instance()->strings['save_meta'] . '</a>';
													$html .= '</div><!-- end .media-toolbar-primary -->';
												$html .= '</div><!-- end .media-toolbar -->';
											$html .= '</div><!-- end .media-frame-toolbar -->';
										$html .= '</div><!-- end .media-frame -->';
									$html .= '</div><!-- end .media-modal-content -->';
								$html .= '</div><!-- end .media-modal -->';
								$html .= '<div class="media-modal-backdrop"></div>';
							$html .= '</div><!-- end .soliloquy-image-meta -->';
							$html .= '<div class="soliloquy-video-wrap">';
								$html .= '<div class="soliloquy-video-inside">';
									$html .= '<div class="soliloquy-video-table">';
										$html .= '<h4 class="no-margin">' . esc_html( $image['title'] ) . '</h4>';
										$html .= '<span class="soliloquy-mini">' . Tgmsp_Strings::get_instance()->strings['html_slide_mini'] . '</span>';
									$html .= '</div>';
								$html .= '</div>';
							$html .= '</div>';
						$html .= '</li>';
					break;
				endswitch;
			}
		}

		/** Store the HTML */
		$response['images'] = $html;

		/** Json encode the images, send them back to the script for processing and die */
		echo json_encode( $response );
		die;

	}

	/**
	 * Ajax callback to save the sortable image order for the current slider.
	 *
	 * @since 1.0.0
	 */
	public function sort_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_sortable', 'nonce' );

		/** Prepare our variables */
		$order 	= explode( ',', $_POST['order'] );
		$i 		= 1;

		/** Update the menu order for the images in the database */
		foreach ( $order as $id ) {
			$sort 				= array();
			$sort['ID'] 		= $id;
			$sort['menu_order'] = $i;
			wp_update_post( $sort );
			$i++;
		}

		do_action( 'tgmsp_ajax_sort_images', $_POST );

		/** Send the order back to the script */
		echo json_encode( $order );
		die;

	}

	/**
	 * Ajax callback to remove an image from the current slider.
	 *
	 * @since 1.0.0
	 */
	public function remove_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_remove', 'nonce' );

		/** Prepare our variable */
		$attachment_id = (int) $_POST['attachment_id'];
		$delete		   = stripslashes( $_POST['do_delete'] );

		do_action( 'tgmsp_ajax_pre_remove_images', $attachment_id );

		// If dealing with a video or HTML slide, delete the attachment entirely.
		if ( 'true' == $delete ) {
			wp_delete_post( $attachment_id, true );
			do_action( 'tgmsp_ajax_remove_images', $attachment_id );
			echo json_encode( true );
			die;
		}

		// Remove the attachment from the parent without deleting the attachment.
		$updates 				= array();
		$updates['ID'] 			= $attachment_id;
		$updates['post_parent'] = $updates['menu_order'] = 0;
		wp_update_post( $updates );

		do_action( 'tgmsp_ajax_remove_images', $attachment_id );

		echo json_encode( $delete );
		die;

	}

	/**
	 * Ajax callback to update image meta for the current slider.
	 *
	 * @since 1.0.0
	 */
	public function update_meta() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_meta', 'nonce' );

		/** Make sure attachment ID is an integer */
		$attachment_id = (int) $_POST['attach'];
		$type		   = stripslashes( $_POST['type'] );

		switch ( $type ) :
			case 'image' :
				/** Update attachment title */
				$entry 				   = array();
				$entry['ID'] 		   = $attachment_id;
				$entry['post_title']   = strip_tags( $_POST['soliloquy-title'] );
				$entry['post_excerpt'] = current_user_can( 'unfiltered_html' ) ? stripslashes( $_POST['wp-editor-area'] ) : wp_kses_post( $_POST['wp-editor-area'] );
				wp_update_post( $entry );

				/** Update attachment alt text */
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', strip_tags( $_POST['soliloquy-alt'] ) );

				/** Update attachment link items */
				update_post_meta( $attachment_id, '_soliloquy_image_link', esc_url( $_POST['soliloquy-link'] ) );
				update_post_meta( $attachment_id, '_soliloquy_image_link_title', esc_attr( strip_tags( $_POST['soliloquy-link-title'] ) ) );
				update_post_meta( $attachment_id, '_soliloquy_image_link_tab', ( 'true' == $_POST['soliloquy-link-check'] ) ? (int) 1 : (int) 0 );

				do_action( 'tgmsp_ajax_update_meta', $_POST );
			break;
			case 'video' :
				$entry 					= array();
				$entry['ID'] 			= $attachment_id;
				$entry['post_title'] 	= strip_tags( stripslashes( $_POST['soliloquy-video-title'] ) );
				$entry['post_content']  = esc_url( $_POST['soliloquy-video-url'] );
				$entry['post_excerpt']  = current_user_can( 'unfiltered_html' ) ? stripslashes( $_POST['soliloquy-video-caption'] ) : wp_kses_post( $_POST['soliloquy-video-caption'] );
				wp_update_post( $entry );
			break;
			case 'html' :
				$entry 					= array();
				$entry['ID'] 			= $attachment_id;
				$entry['post_title'] 	= strip_tags( stripslashes( $_POST['soliloquy-html-title'] ) );
				$entry['post_content']  = current_user_can( 'unfiltered_html' ) ? stripslashes( $_POST['soliloquy-html-code'] ) : wp_kses_post( $_POST['soliloquy-html-code'] );
				wp_update_post( $entry );
			break;
		endswitch;

		echo json_encode( true );
		die;

	}

	/**
	 * Loads extra image library contents into the library selection view.
	 *
	 * @since 1.5.0
	 */
	public function load_library() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_load_library', 'nonce' );

		/** Make sure attachment ID is an integer */
		$offset  = (int) $_POST['offset'];
		$post_id = absint( $_POST['post_id'] );
		$html    = '';

		// Grab the library contents with the included offset parameter.
		$library = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'any', 'posts_per_page' => 20, 'offset' => $offset ) );
		if ( $library ) : foreach ( (array) $library as $image ) : $class = $post_id == $image->post_parent ? ' selected soliloquy-in-slider' : '';
			$html .= '<li class="attachment' . $class . '" data-attachment-id="' . absint( $image->ID ) . '">';
				$html .= '<div class="attachment-preview landscape">';
					$html .= '<div class="thumbnail">';
						$html .= '<div class="centered">';
							$src = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
							$html .= '<img src="' . esc_url( $src[0] ) . '" />';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<a class="check" href="#"><div class="media-modal-icon"></div></a>';
				$html .= '</div>';
			$html .= '</li>';
		endforeach; endif;

		echo json_encode( array( 'html' => stripslashes( $html ) ) );
		die;

	}

	/**
	 * Loads library items that matched the search query.
	 *
	 * @since 1.5.0
	 */
	public function library_search() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_library_search', 'nonce' );

		/** Make sure attachment ID is an integer */
		$search  = stripslashes( $_POST['search'] );
		$post_id = absint( $_POST['post_id'] );
		$html    = '';

		// Grab the library contents based on the search parameter.
		$library = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => -1, 's' => $search ) );
		if ( $library ) : foreach ( (array) $library as $image ) : $class = $post_id == $image->post_parent ? ' selected soliloquy-in-slider' : '';
			$html .= '<li class="attachment' . $class . '" data-attachment-id="' . absint( $image->ID ) . '">';
				$html .= '<div class="attachment-preview landscape">';
					$html .= '<div class="thumbnail">';
						$html .= '<div class="centered">';
							$src = wp_get_attachment_image_src( $image->ID, 'thumbnail' );
							$html .= '<img src="' . esc_url( $src[0] ) . '" />';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<a class="check" href="#"><div class="media-modal-icon"></div></a>';
				$html .= '</div>';
			$html .= '</li>';
		endforeach; endif;

		echo json_encode( array( 'html' => stripslashes( $html ) ) );
		die;

	}

	/**
	 * Inserts all available slides into the slider.
	 *
	 * @since 1.5.0
	 */
	public function insert_slides() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_insert_slides', 'nonce' );

		/** Make sure attachment ID is an integer */
		$post_id  = absint( $_POST['post_id'] );
		$selected = isset( $_POST['data']['selected'] ) ? $_POST['data']['selected'] : array();
		$video    = isset( $_POST['data']['video'] ) ? $_POST['data']['video'] : array();
		$html     = isset( $_POST['data']['html'] ) ? $_POST['data']['html'] : array();

		// If we have items that have been selected, loop through them and update the attachment post parent to our slider.
		if ( ! empty( $selected ) ) {
			foreach ( $selected as $i => $id ) {
				$data 				 = array();
				$data['ID'] 		 = absint( $id );
				$data['post_parent'] = $post_id;
				$data['menu_order']  = 0;
				wp_update_post( $data );
			}
		}

		// If we have video slides, generate attachments from them and attach them to the slider.
		if ( ! empty( $video ) ) {
			foreach ( $video as $i => $data ) {
				$entry = array();
				// If no title is set, generate a random title for the video slide.
				$entry['post_title'] 	 = isset( $data['title'] ) ? strip_tags( stripslashes( $data['title'] ) ) : mb_substr( md5( time() ), 0, 6 );
				$entry['post_content']   = isset( $data['url'] ) ? esc_url( stripslashes( $data['url'] ) ) : '';
				$entry['post_excerpt']   = isset( $data['caption'] ) ? ( current_user_can( 'unfiltered_html' ) ? stripslashes( $data['caption'] ) : wp_kses_post( $data['caption'] ) ) : '';
				$entry['post_status']    = 'inherit';
				$entry['post_mime_type'] = 'soliloquy/video';

				// Insert the attachment into the database.
				wp_insert_attachment( $entry, false, $post_id );
			}
		}

		// If we have HTML slides, generate attachments from them and attach them to the slider.
		if ( ! empty( $html ) ) {
			foreach ( $html as $i => $data ) {
				$entry = array();
				// If no title is set, generate a random title for the video slide.
				$entry['post_title'] 	 = isset( $data['title'] ) ? strip_tags( stripslashes( $data['title'] ) ) : mb_substr( md5( time() ), 0, 6 );
				$entry['post_content']   = isset( $data['code'] ) ? stripslashes( $data['code'] ) : '';
				$entry['post_status']    = 'inherit';
				$entry['post_mime_type'] = 'soliloquy/html';

				// Insert the attachment into the database.
				wp_insert_attachment( $entry, false, $post_id );
			}
		}

		echo json_encode( $selected );
		die;

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