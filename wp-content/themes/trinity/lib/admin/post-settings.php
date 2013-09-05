<?php

add_action( 'admin_init', 'ct_post_settings_init' );
add_action( 'admin_menu', 'ct_post_settings_add_page' );

/**
 * Init plugin options to white list our options
 */
function ct_post_settings_init(){
	register_setting( 'ct_post', 'ct_post_settings', 'ct_post_settings_validate' );
}

/**
 * Load up the menu page
 */
function ct_post_settings_add_page() {
	add_submenu_page('edit.php', __( 'Settings', 'churchthemes' ), __( 'Settings', 'churchthemes' ), 'edit_themes', 'post-settings', 'ct_post_settings_do_page');
}

/**
 * Create arrays for our select and radio options
 */
$select_orderby = array(
	'date' => array(
		'value' =>	'date',
		'label' => __( 'Post Date', 'churchthemes' )
	),
	'title' => array(
		'value' =>	'title',
		'label' => __( 'Title', 'churchthemes' )
	),
	'modified' => array(
		'value' => 'modified',
		'label' => __( 'Date Modified', 'churchthemes' )
	),
	'menu_order' => array(
		'value' => 'menu_order',
		'label' => __( 'Menu Order', 'churchthemes' )
	),
	'id' => array(
		'value' => 'id',
		'label' => __( 'Post ID', 'churchthemes' )
	),
	'rand' => array(
		'value' => 'rand',
		'label' => __( 'Random', 'churchthemes' )
	),
	'views' => array(
		'value' => 'views',
		'label' => __( 'View Count', 'churchthemes' )
	)
);

$select_layout = array(
	'right' => array(
		'value' =>	'right',
		'label' => __( 'Sidebar Right', 'churchthemes' )
	),
	'left' => array(
		'value' =>	'left',
		'label' => __( 'Sidebar Left', 'churchthemes' )
	),
	'full' => array(
		'value' => 'full',
		'label' => __( 'No Sidebar (Full Width)', 'churchthemes' )
	)
);

$radio_order = array(
	'asc' => array(
		'value' => 'ASC',
		'label' => __( 'Ascending', 'churchthemes' )
	),
	'desc' => array(
		'value' => 'DESC',
		'label' => __( 'Descending', 'churchthemes' )
	)
);

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
function ct_post_settings_do_page() {
	global $select_orderby, $select_layout, $radio_order, $radio_toggle;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap churchthemes">
		<?php screen_icon(); echo "<h2>" . __( 'Posts Settings', 'churchthemes' ) . "</h2>"; ?>

		<?php if ( $_REQUEST['settings-updated'] !== false ) : ?>
			<div class="updated fade"><p><strong><?php _e( 'Settings saved.', 'churchthemes' ); ?></strong></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'ct_post' ); ?>
			<?php $options = get_option( 'ct_post_settings' ); ?>

			<table class="form-table churchthemes">
				<tr>
					<th scope="row"><?php _e( 'Order Posts By', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_post_settings[orderby]">
							<?php
								$selected = $options['orderby'];
								$p = '';
								$r = '';
								foreach ( $select_orderby as $option ) {
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
					<td class="info">
						<p><?php _e( 'Select what you would like to order your Post Archives by.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr><th scope="row"><?php _e( 'Order Direction', 'churchthemes' ); ?></th>
					<td class="option">
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Order Direction', 'churchthemes' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( $radio_order as $option ) {
								if(isset($options['order'])) {
									$radio_setting = $options['order'];
								} else {
									$radio_setting = null;
								}
								if ( '' != $radio_setting ) {
									if ( $options['order'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<label class="description">
									<input type="radio" name="ct_post_settings[order]" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?>
								</label>
								<br />
								<?php
							}
						?>
						</fieldset>
					</td>
					<td class="info">
						<p><?php _e( 'Select the order direction for the Post Archives.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Archive Title', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_post_settings[archive_title]" class="regular-text" type="text" name="ct_post_settings[archive_title]" placeholder="<?php _e( 'Post Archives', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['archive_title'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Name displayed as the page title when viewing an archive of posts.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "Post Archives" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Archive Tagline', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_post_settings[archive_tagline]" class="regular-text" type="text" name="ct_post_settings[archive_tagline]" placeholder="<?php _e( 'e.g. All the latest news', 'churchthemes' ); ?>" value="<?php echo wp_filter_nohtml_kses( $options['archive_tagline'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Text displayed to the right of the Archive Title when viewing an archive of posts.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Archive Layout', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_post_settings[archive_layout]">
							<?php
								$selected = $options['archive_layout'];
								$p = '';
								$r = '';
								foreach ( $select_layout as $option ) {
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
					<td class="info">
						<p><?php _e( 'Select the layout you would like to use when viewing the Post Archives.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Archive Sidebar', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_post_settings[archive_sidebar]">
							<?php
								$selected = $options['archive_sidebar'];
								$p = '';
								$r = '';
								$ct_sidebars = get_option('ct_generated_sidebars');
								foreach ($ct_sidebars as $key => $value) {
									$select_sidebars = array( $key => array('value' => $key, 'label' => $value));
									foreach ( $select_sidebars as $option ) {
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
								}
								echo $p . $r;
							?>
						</select>
					</td>
					<td class="info">
						<p><?php _e( 'Select the', 'churchthemes' ); ?> <a href="themes.php?page=sidebars"><?php _e( 'Sidebar', 'churchthemes' ); ?></a> <?php _e( 'to be displayed when viewing the Post Archives.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Single Post Layout', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_post_settings[single_layout]">
							<?php
								$selected = $options['single_layout'];
								$p = '';
								$r = '';
								foreach ( $select_layout as $option ) {
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
					<td class="info">
						<p><?php _e( 'Select the layout you would like to use when viewing a single post.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Single Post Sidebar', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_post_settings[single_sidebar]">
							<?php
								$selected = $options['single_sidebar'];
								$p = '';
								$r = '';
								$ct_sidebars = get_option('ct_generated_sidebars');
								foreach ($ct_sidebars as $key => $value) {
									$select_sidebars = array( $key => array('value' => $key, 'label' => $value));
									foreach ( $select_sidebars as $option ) {
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
								}
								echo $p . $r;
							?>
						</select>
					</td>
					<td class="info">
						<p><?php _e( 'Select the', 'churchthemes' ); ?> <a href="themes.php?page=sidebars"><?php _e( 'Sidebar', 'churchthemes' ); ?></a> <?php _e( 'to be displayed when viewing a single post.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr><th scope="row"><?php _e( 'Facebook Likes', 'churchthemes' ); ?></th>
					<td class="option">
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Facebook Likes', 'churchthemes' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( $radio_toggle as $option ) {
								if(isset($options['facebook_likes'])) {
									$radio_setting = $options['facebook_likes'];
								} else {
									$radio_setting = null;
								}
								if ( '' != $radio_setting ) {
									if ( $options['facebook_likes'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<label class="description">
									<input type="radio" name="ct_post_settings[facebook_likes]" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?>
								</label>
								<br />
								<?php
							}
						?>
						</fieldset>
					</td>
					<td class="info">
						<p><?php _e( 'Users can show their Facebook friends that they like a particular post by clicking this button as well as see the total number of past likes.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr><th scope="row"><?php _e( 'Tweet This', 'churchthemes' ); ?></th>
					<td class="option">
						<fieldset><legend class="screen-reader-text"><span><?php _e( 'Tweet This', 'churchthemes' ); ?></span></legend>
						<?php
							if ( ! isset( $checked ) )
								$checked = '';
							foreach ( $radio_toggle as $option ) {
								if(isset($options['tweet_this'])) {
									$radio_setting = $options['tweet_this'];
								} else {
									$radio_setting = null;
								}
								if ( '' != $radio_setting ) {
									if ( $options['tweet_this'] == $option['value'] ) {
										$checked = "checked=\"checked\"";
									} else {
										$checked = '';
									}
								}
								?>
								<label class="description">
									<input type="radio" name="ct_post_settings[tweet_this]" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo $checked; ?> /> <?php echo $option['label']; ?>
								</label>
								<br />
								<?php
							}
						?>
						</fieldset>
					</td>
					<td class="info">
						<p><?php _e( 'Users can quickly tweet about a post by clicking this button as well as see the total number of past tweets.', 'churchthemes' ); ?></p>
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
function ct_post_settings_validate( $input ) {
	global $select_orderby, $select_layout, $select_sidebars, $radio_order, $radio_toggle;

	// Say our text option must be safe text with no HTML tags
	if ( ! isset( $input['sometext'] ) )
		$input['sometext'] = null;
	$input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );

	// Our select option must actually be in our array of select options
	if ( ! isset( $input['select1'] ) || ! array_key_exists( $input['select1'], $select_orderby ) )
		$input['select1'] = null;

	if ( ! isset( $input['select2'] ) || ! array_key_exists( $input['select2'], $select_layout ) )
		$input['select2'] = null;

	if ( ! isset( $input['select3'] ) || ! array_key_exists( $input['select3'], $select_sidebars ) )
		$input['select3'] = null;

	// Our radio option must actually be in our array of radio options
	if ( ! isset( $input['radio1'] ) || ! array_key_exists( $input['radio1'], $radio_order ) )
		$input['radio1'] = null;

	if ( ! isset( $input['radio2'] ) || ! array_key_exists( $input['radio2'], $radio_toggle ) )
		$input['radio2'] = null;

	return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/

?>