<?php

add_action( 'admin_init', 'ct_social_footer_settings_init' );
add_action( 'admin_menu', 'ct_social_footer_settings_add_page' );

/**
 * Init plugin options to white list our options
 */
function ct_social_footer_settings_init(){
	register_setting( 'ct_social_footer', 'ct_social_footer_settings', 'ct_social_footer_settings_validate' );
}

/**
 * Load up the menu page
 */
function ct_social_footer_settings_add_page() {
	add_theme_page( __( 'Social Footer', 'churchthemes' ), __( 'Social Footer', 'churchthemes' ), 'edit_themes', 'social-footer', 'ct_social_footer_settings_do_page' );
}

/**
 * Create arrays for our select and radio options
 */
$radio_toggle = array(
	'on' => array(
		'value' => 'on',
		'label' => __( 'On', 'churchthemes' )
	),
	'off' => array(
		'value' => 'off',
		'label' => __( 'Off', 'churchthemes' )
	)
);

/**
 * Create the options page
 */
function ct_social_footer_settings_do_page() {
	global $radio_toggle;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . __( 'Social Footer Settings', 'churchthemes' ) . "</h2>"; ?>

		<?php if ( $_REQUEST['settings-updated'] !== false ) : ?>
			<div class="updated fade"><p><strong><?php _e( 'Settings saved.', 'churchthemes' ); ?></strong></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'ct_social_footer' ); ?>
			<?php $options = get_option( 'ct_social_footer_settings' ); ?>

			<table class="form-table churchthemes">

				<tr><th scope="row"><?php _e( 'Social Footer', 'churchthemes' ); ?></th>
					<td class="option">
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e( 'Social Footer', 'churchthemes' ); ?></span>
							</legend>
							<?php
								if ( ! isset( $checked ) )
									$checked = '';
								foreach ( $radio_toggle as $option ) {
									if(isset($options['social_footer'])) {
										$radio_setting = $options['social_footer'];
									} else {
										$radio_setting = null;
									}
									if ( '' != $radio_setting ) {
										if ( $options['social_footer'] == $option['value'] ) {
											$checked = "checked=\"checked\"";
										} else {
											$checked = '';
										}
									}
							?>
							<label class="description">
								<input type="radio" name="ct_social_footer_settings[social_footer]" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?>
							</label>
							<br />
							<?php
								}
							?>
						</fieldset>
					</td>
					<td class="info">
						<p><?php _e( 'Allow your users to connect with you on various social networks.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If turned "Off" this setting will override individual page settings and completely disable the Social Footer.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Title', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[title]" class="regular-text" type="text" name="ct_social_footer_settings[title]" placeholder="<?php _e( 'e.g. Connect With Us', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['title'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Set the title for the Social Footer.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Facebook URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[facebook]" class="regular-text" type="text" name="ct_social_footer_settings[facebook]" placeholder="<?php _e( 'e.g. http://www.facebook.com/yourchurch', 'churchthemes' ); ?>" value="<?php echo esc_url( $options['facebook'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Link to your Facebook page.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Twitter URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[twitter]" class="regular-text" type="text" name="ct_social_footer_settings[twitter]" placeholder="<?php _e( 'e.g. http://twitter.com/yourchurch', 'churchthemes' ); ?>" value="<?php echo esc_url( $options['twitter'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Link to your Twitter page.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Flickr URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[flickr]" class="regular-text" type="text" name="ct_social_footer_settings[flickr]" placeholder="<?php _e( 'e.g. http://www.flickr.com/photos/yourchurch', 'churchthemes' ); ?>" value="<?php echo esc_url( $options['flickr'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Link to your Flickr photostream.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'YouTube URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[youtube]" class="regular-text" type="text" name="ct_social_footer_settings[youtube]" placeholder="<?php _e( 'e.g. http://www.youtube.com/yourchurch', 'churchthemes' ); ?>" value="<?php echo esc_url( $options['youtube'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Link to your YouTube channel.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Vimeo URL', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_social_footer_settings[vimeo]" class="regular-text" type="text" name="ct_social_footer_settings[vimeo]" placeholder="<?php _e( 'e.g. http://vimeo.com/yourchurch', 'churchthemes' ); ?>" value="<?php echo esc_url( $options['vimeo'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Link to your Vimeo profile or channel.', 'churchthemes' ); ?></p>
					</td>
				</tr>

			</table>

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
function ct_social_footer_settings_validate( $input ) {
	global $radio_toggle;

	// Say our text option must be safe text with no HTML tags
	if ( ! isset( $input['sometext'] ) )
		$input['sometext'] = null;
	$input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );

	// Our radio option must actually be in our array of radio options
	if ( ! isset( $input['radio1'] ) || ! array_key_exists( $input['radio1'], $radio_toggle ) )
		$input['radio1'] = null;

	return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/

?>