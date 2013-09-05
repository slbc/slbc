<?php

function churchthemes_podcast_settings_enqueue_scripts() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'podcast-settings' ) {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
	}
}
add_action( 'admin_enqueue_scripts', 'churchthemes_podcast_settings_enqueue_scripts' );

add_action( 'admin_init', 'ct_podcast_settings_init' );
add_action( 'admin_menu', 'ct_podcast_settings_add_page' );

/**
 * Init plugin options to white list our options
 */
function ct_podcast_settings_init(){
	register_setting( 'ct_podcast', 'ct_podcast_settings', 'ct_podcast_settings_validate' );
}

/**
 * Load up the menu page
 */
function ct_podcast_settings_add_page() {
	add_submenu_page('edit.php?post_type=ct_sermon', __( 'Podcast Settings', 'churchthemes' ), __( 'Podcast', 'churchthemes' ), 'edit_themes', 'podcast-settings', 'ct_podcast_settings_do_page');
}

/**
 * Create arrays for our select and radio options
 */
$select_explicit_content = array(
	'no' => array(
		'value' =>	'no',
		'label' => __( 'No', 'churchthemes' )
	),
	'yes' => array(
		'value' =>	'yes',
		'label' => __( 'Yes', 'churchthemes' )
	)
);

/**
 * Create the options page
 */
function ct_podcast_settings_do_page() {
	global $select_explicit_content;

	/**
	 * Grab current domain for use in the 'Owner Email' setting
	 */
	$url = home_url();
	$parse = parse_url($url);

	/**
	 * Grab current user info for 'Webmaster' settings
	 */
	global $current_user;
	get_currentuserinfo();
	$admin_fname = $current_user->user_firstname;
	$admin_lname = $current_user->user_lastname;
	$admin_email = $current_user->user_email;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#upload_cover_image').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});
		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			uploadID.val(imgurl); /*assign the value to the input*/
			tb_remove();
		};
	});
	</script>
	<div class="wrap churchthemes">
		<?php screen_icon(); echo "<h2>" . __( 'Podcast Settings', 'churchthemes' ) . "</h2>"; ?>

		<?php if ( $_REQUEST['settings-updated'] !== false ) : ?>
			<div class="updated fade"><p><strong><?php _e( 'Settings saved.', 'churchthemes' ); ?></strong></p></div>
			<?php ct_save_podcast_settings() ?>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'ct_podcast' ); ?>
			<?php $options = get_option( 'ct_podcast_settings' ); ?>

			<h3><?php _e( 'General', 'churchthemes' ); ?></h3>

			<table class="form-table churchthemes">

				<tr>
					<th scope="row"><?php _e( 'Title', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[title]" class="regular-text" type="text" name="ct_podcast_settings[title]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('name'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['title'] ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Description', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[description]" class="regular-text" type="text" name="ct_podcast_settings[description]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('description'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['description'] ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Website Link', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[website_link]" class="regular-text" type="text" name="ct_podcast_settings[website_link]" placeholder="<?php _e( 'e.g. ' . $url, 'churchthemes' ); ?>" value="<?php echo esc_url( $options['website_link'] ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Language', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[language]" class="regular-text" type="text" name="ct_podcast_settings[language]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('language'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['language'] ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Copyright', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[copyright]" class="regular-text" type="text" name="ct_podcast_settings[copyright]" placeholder="<?php _e( 'e.g. Copyright ' . htmlspecialchars('&copy;') . ' ' . get_bloginfo('name'), 'churchthemes' ); ?>" value="<?php echo htmlspecialchars( esc_attr( $options['copyright'] ) ); ?>" />
					</td>
					<td class="info">
						<p><em><?php _e( 'Tip: Use "' . htmlspecialchars('&copy;') . '" to generate a copyright symbol.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Webmaster Name', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[webmaster_name]" class="regular-text" type="text" name="ct_podcast_settings[webmaster_name]" placeholder="<?php _e( 'e.g. ' . $admin_fname . ' ' . $admin_lname, 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['webmaster_name'] ); ?>" />
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Webmaster Email', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<input id="ct_podcast_settings[webmaster_email]" class="regular-text" type="text" name="ct_podcast_settings[webmaster_email]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('admin_email'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['webmaster_email'] ); ?>" />
					</td>
				</tr>

			</table>

			<br /><br />
			<h3><?php _e( 'iTunes', 'churchthemes' ); ?></h3>

			<table class="form-table churchthemes">

				<tr>
					<th scope="row"><?php _e( 'Author', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_author]" class="regular-text" type="text" name="ct_podcast_settings[itunes_author]" placeholder="<?php _e( 'e.g. Primary Speaker or Church Name', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_author'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'This will display at the "Artist" in the iTunes Store.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Subtitle', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_subtitle]" class="regular-text" type="text" name="ct_podcast_settings[itunes_subtitle]" placeholder="<?php _e( 'e.g. Preaching and teaching audio from ' . get_bloginfo('name'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_subtitle'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Your subtitle should briefly tell the listener what they can expect to hear.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Summary', 'churchthemes' ); ?></th>
					<td class="option">
						<textarea id="ct_podcast_settings[itunes_summary]" class="large-text" cols="40" rows="5" name="ct_podcast_settings[itunes_summary]" placeholder="<?php _e( 'e.g. Weekly teaching audio brought to you by ' . get_bloginfo('name') . ' in City Name. ' . get_bloginfo('name') . ' exists to make Jesus famous by loving the city, caring for the church, and providing free teaching resources such as this Podcast.', 'churchthemes' ); ?>"><?php echo esc_textarea( $options['itunes_summary'] ); ?></textarea>
					</td>
					<td class="info">
						<p><?php _e( 'Keep your Podcast Summary short, sweet and informative. Be sure to include a brief statement about your mission and in what region your audio content originates.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Owner Name', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_owner_name]" class="regular-text" type="text" name="ct_podcast_settings[itunes_owner_name]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('name'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_owner_name'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'This should typically be the name of your Church.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Owner Email', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_owner_email]" class="regular-text" type="text" name="ct_podcast_settings[itunes_owner_email]" placeholder="<?php _e( 'e.g. ' . get_bloginfo('admin_email'), 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_owner_email'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Use an email address that you don\'t mind being made public. If someone wants to contact you regarding your Podcast this is the address they will use.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Explicit Content', 'churchthemes' ); ?></th>
					<td class="option" colspan="2">
						<select name="ct_podcast_settings[itunes_explicit_content]">
							<?php
								$selected = $options['itunes_explicit_content'];
								$p = '';
								$r = '';
								foreach ( $select_explicit_content as $option ) {
									if(isset($option['label'])) {
										$label = $option['label'];
									} else {
										$label = null;
									}
									if ( $selected == $option['value'] ) // Make default first in list
										$p = "\n\t<option style=\"padding-right: 10px;\" selected='selected' value='" . esc_attr( $option['value'] ) . "'>$label</option>";
									else
										$r .= "\n\t<option style=\"padding-right: 10px;\" value='" . esc_attr( $option['value'] ) . "'>$label</option>";
								}
								echo $p . $r;
							?>
						</select>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'Cover Image', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_cover_image]" class="regular-text" type="text" name="ct_podcast_settings[itunes_cover_image]" value="<?php echo esc_url( $options['itunes_cover_image'] ); ?>" />
						<input id="upload_cover_image" type="button" class="button" value="Upload Image" />
<?php if($options['itunes_cover_image']): ?>
						<br />
						<img src="<?php echo esc_url( $options['itunes_cover_image'] ); ?>" class="preview" />
<?php endif; ?>
					</td>
					<td class="info">
						<p><?php _e( 'This JPG will serve as the Podcast artwork in the iTunes Store.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Top Category', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_top_category]" class="regular-text" type="text" name="ct_podcast_settings[itunes_top_category]" placeholder="<?php _e( 'e.g. Religion & Spirituality', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_top_category'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Choose the appropriate top-level category for your Podcast listing in iTunes.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Sub Category', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_podcast_settings[itunes_sub_category]" class="regular-text" type="text" name="ct_podcast_settings[itunes_sub_category]" placeholder="<?php _e( 'e.g. Christianity', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['itunes_sub_category'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Choose the appropriate sub category for your Podcast listing in iTunes.', 'churchthemes' ); ?></p>
					</td>
				</tr>

			</table>

			<br /><br />
			<h3><?php _e( 'Submit to iTunes', 'churchthemes' ); ?></h3>
			<table class="form-table churchthemes">
				<tr>
					<th scope="row"><?php _e( 'Podcast Feed URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input type="text" class="regular-text" readonly="readonly" value="<?php echo $url; ?>/feed/podcast" />
					</td>
					<td class="info">
						<p><?php _e( 'Use the ', 'churchthemes' ); ?><a href="http://www.feedvalidator.org/check.cgi?url=<?php echo $url; ?>/feed/podcast" target="_blank"><?php _e( 'Feed Validator', 'churchthemes' ); ?></a><?php _e( ' to diagnose and fix any problems before submitting your Podcast to iTunes.', 'churchthemes' ); ?></p>
					</td>
				</tr>
			</table>

			<br />
			<p><?php _e( 'Once your Podcast Settings are complete and your Sermons are ready, it\'s time to ', 'churchthemes' ); ?><a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank"><?php _e( 'Submit Your Podcast', 'churchthemes' ); ?></a><?php _e( ' to the iTunes Store!', 'churchthemes' ); ?></p>

			<p><?php _e( 'Alternatively, if you want to track your Podcast subscribers, simply pass the Podcast Feed URL above through ', 'churchthemes' ); ?><a href="http://feedburner.google.com/" target="_blank"><?php _e( 'FeedBurner', 'churchthemes' ); ?></a><?php _e( '. FeedBurner will then give you a new URL to submit to iTunes instead.', 'churchthemes' ); ?></p>

			<p><?php _e( 'Please read the ', 'churchthemes' ); ?><a href="http://www.apple.com/itunes/podcasts/creatorfaq.html" target="_blank"><?php _e( 'iTunes FAQ for Podcast Makers', 'churchthemes' ); ?></a><?php _e( ' for more information.', 'churchthemes' ); ?></p>

			<p class="submit clear">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Settings', 'churchthemes' ); ?>" />
			</p>
		</form>

	</div>
	<?php
}

/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function ct_podcast_settings_validate( $input ) {
	global $select_explicit_content;

	// Say our text option must be safe text with no HTML tags
	if ( ! isset( $input['sometext'] ) )
		$input['sometext'] = null;
	$input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );

	// Our select option must actually be in our array of select options
	if ( ! isset( $input['select1'] ) || ! array_key_exists( $input['select1'], $select_explicit_content ) )
		$input['select1'] = null;

	// Say our textarea option must be safe text with the allowed tags for posts
	if ( ! isset( $input['sometextarea'] ) )
		$input['sometextarea'] = null;
	$input['sometextarea'] = wp_filter_post_kses( $input['sometextarea'] );

	return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
