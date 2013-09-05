<?php

function churchthemes_theme_options_enqueue_scripts() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'theme-options' ) {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_enqueue_script('farbtastic');
		wp_enqueue_style('farbtastic');
	}
}
add_action( 'admin_enqueue_scripts', 'churchthemes_theme_options_enqueue_scripts' );

add_action( 'admin_init', 'ct_theme_options_init' );
add_action( 'admin_menu', 'ct_theme_options_add_page' );

/**
 * Init plugin options to white list our options
 */
function ct_theme_options_init(){
	register_setting( 'ct_general', 'ct_theme_options', 'ct_theme_options_validate' );
}

/**
 * Load up the menu page
 */
function ct_theme_options_add_page() {
	add_theme_page( __( 'Theme Options', 'churchthemes' ), __( 'Theme Options', 'churchthemes' ), 'edit_theme_options', 'theme-options', 'ct_theme_options_do_page' );
}

/**
 * Create arrays for our select and radio options
 */
$select_target = array(
	'_blank' => array(
		'value' =>	'_blank',
		'label' => __( 'Open in a new window', 'churchthemes' )
	),
	'_self' => array(
		'value' =>	'_self',
		'label' => __( 'Open in the same window', 'churchthemes' )
	),
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
	),
);

/**
 * Create the options page
 */
function ct_theme_options_do_page() {
	global $select_target, $select_layout;

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	?>
	<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function() {
		jQuery('#colorPickerDiv').hide();
		jQuery('#colorPickerDiv').farbtastic('.color');
		jQuery("#pickcolor").click(function() {
			jQuery('#colorPickerDiv').fadeToggle()
			jQuery('.on').toggle()
			jQuery('.off').toggle()
		});
		jQuery(".color").keyup(function() {
			var b=jQuery('.color').val(),a=b;
			if(a.charAt(0)!='#') {
				a="#"+a
			}
			a=a.replace(/[^#a-fA-F0-9]+/,'');
			if(a!=b) {
				jQuery('.color').val(a)
			}
			if(a.length==4||a.length==7) {
				pickColor(a)
			}
		});
	});
	jQuery(document).ready(function() {
		jQuery('#upload_logo').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});
		jQuery('#upload_favicon').click(function() {
			uploadID = jQuery(this).prev('input');
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			return false;
		});
		jQuery('#upload_ios_icon').click(function() {
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
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" . __( 'Theme Options', 'churchthemes' ) . "</h2>"; ?>

		<?php if ( $_REQUEST['settings-updated'] !== false ) : ?>
			<div class="updated fade"><p><strong><?php _e( 'Options saved.', 'churchthemes' ); ?></strong></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'ct_general' ); ?>
			<?php $options = get_option( 'ct_theme_options' ); ?>
			<?php $main_color = $options['main_color']; if(empty($main_color) || $main_color == '#'): $options['main_color'] = CHURCHTHEMES_OPTIONS_MAIN_COLOR; endif; ?>

			<table class="form-table churchthemes">
				<tr>
					<th scope="row"><?php _e( 'Main Color', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[main_color]" class="color" type="text" name="ct_theme_options[main_color]" placeholder="<?php echo CHURCHTHEMES_OPTIONS_MAIN_COLOR; ?>" value="<?php echo esc_attr( $options['main_color'] ); ?>" />
						<a class="hide-if-no-js" href="javascript:void(0)" id="pickcolor"><span class="off"><?php _e('Select a Color', 'churchthemes' ); ?></span><span class="on" style="display:none"><?php _e('Done', 'churchthemes' ); ?></span></a>
						<div id="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					</td>
					<td class="info">
						<p><?php _e( 'Choose the main highlight color for items such as links on your website', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "' . CHURCHTHEMES_OPTIONS_MAIN_COLOR . '" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'Logo', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[logo]" class="regular-text" type="text" name="ct_theme_options[logo]" value="<?php echo esc_url( $options['logo'] ); ?>" />
						<input id="upload_logo" type="button" class="button" value="Upload Image" />
<?php if($options['logo']): ?>
						<br />
						<img src="<?php echo esc_url( $options['logo'] ); ?>" class="preview" />
<?php endif; ?>
					</td>
					<td class="info">
						<p><?php _e( 'Enter the link to your logo image that will appear in the header.', 'churchthemes' ); ?></p>
						<p><a href="http://www.ehow.com/how_2034277_make-png-photoshop.html" target="_blank"><?php _e( 'Use a Transparent 24-bit PNG', 'churchthemes' ); ?></a></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Logo Width', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[logo_width]" class="regular-text" type="text" name="ct_theme_options[logo_width]" placeholder="<?php echo CHURCHTHEMES_OPTIONS_LOGO_WIDTH; ?>" value="<?php echo esc_attr( $options['logo_width'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Enter a custom width (in pixels) for your logo image.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "' . CHURCHTHEMES_OPTIONS_LOGO_WIDTH . '" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Logo Height', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[logo_height]" class="regular-text" type="text" name="ct_theme_options[logo_height]" placeholder="<?php echo CHURCHTHEMES_OPTIONS_LOGO_HEIGHT; ?>" value="<?php echo esc_attr( $options['logo_height'] ); ?>" />
					</td>
					<td class="info">
						<?php _e( 'Enter a custom height (in pixels) for your logo image.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "' . CHURCHTHEMES_OPTIONS_LOGO_HEIGHT . '" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Logo Top Margin', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[logo_top_margin]" class="regular-text" type="text" name="ct_theme_options[logo_top_margin]" placeholder="<?php echo CHURCHTHEMES_OPTIONS_LOGO_TOP_MARGIN ?>" value="<?php echo esc_attr( $options['logo_top_margin'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Enter a custom top margin (in pixels) for your logo image.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "' . CHURCHTHEMES_OPTIONS_LOGO_TOP_MARGIN . '" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'Favicon', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[favicon]" class="regular-text" type="text" name="ct_theme_options[favicon]" value="<?php echo esc_url( $options['favicon'] ); ?>" />
						<input id="upload_favicon" type="button" class="button" value="Upload Image" />
<?php if($options['favicon']): ?>
						<br />
						<img src="<?php echo esc_url( $options['favicon'] ); ?>" class="preview" />
<?php endif; ?>
					</td>
					<td class="info">
						<p><?php _e( 'A favicon is a 16x16 pixel icon that represents your site. Upload the ICO or PNG image that you want to use as your favicon.', 'churchthemes' ); ?></p>
						<p><a href="http://converticon.com/" target="_blank"><?php _e( 'Make an ICO or PNG with Converticon', 'churchthemes' ); ?></a></p>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'iOS Icon', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[ios_icon]" class="regular-text" type="text" name="ct_theme_options[ios_icon]" value="<?php echo esc_url( $options['ios_icon'] ); ?>" />
						<input id="upload_ios_icon" type="button" class="button" value="Upload Image" />
<?php if($options['ios_icon']): ?>
						<br />
						<img src="<?php echo esc_url( $options['ios_icon'] ); ?>" class="preview" />
<?php endif; ?>
					</td>
					<td class="info">
						<p><?php _e( 'An iOS icon is a 114x114 pixel icon that represents your site when someone saves your website on the home screen of their iOS device (iPhone, iPad or iPod Touch). Upload the PNG image that you want to use as your iOS icon.', 'churchthemes' ); ?></p>
						<p><a href="http://developer.apple.com/library/ios/#documentation/userexperience/conceptual/mobilehig/IconsImages/IconsImages.html" target="_blank"><?php _e( 'Read more about iOS Icons', 'churchthemes' ); ?></a></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'External Links', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_theme_options[external_target]">
							<?php
								$selected = $options['external_target'];
								$p = '';
								$r = '';
								foreach ( $select_target as $option ) {
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
						<p><?php _e( 'What should happen when a user clicks a link that takes them away from your site?', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Search Results Title', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[search_results_title]" class="regular-text" type="text" name="ct_theme_options[search_results_title]" placeholder="<?php _e( 'Search Results', 'churchthemes' ); ?>" value="<?php echo esc_attr( $options['search_results_title'] ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Name displayed as the page title when viewing search results.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'If blank, "Search Results" will be used by default.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Search Results Layout', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_theme_options[search_results_layout]">
							<?php
								$selected = $options['search_results_layout'];
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
						<p><?php _e( 'Select the layout you would like to use when viewing search results.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Search Results Sidebar', 'churchthemes' ); ?></th>
					<td class="option">
						<select name="ct_theme_options[search_results_sidebar]">
							<?php
								$selected = $options['search_results_sidebar'];
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
						<p><?php _e( 'Select the', 'churchthemes' ); ?> <a href="themes.php?page=sidebars"><?php _e( 'Sidebar', 'churchthemes' ); ?></a> <?php _e( 'to be displayed when viewing search results.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e( 'Footer Copyright Text', 'churchthemes' ); ?></th>
					<td class="option">
						<input id="ct_theme_options[footer_copyright_text]" class="regular-text" type="text" name="ct_theme_options[footer_copyright_text]" placeholder="<?php _e( 'e.g. Copyright ' . htmlspecialchars('&copy;') . ' ' . date('Y') . ' ' . get_bloginfo('name') . ' | All rights reserved.', 'churchthemes' ); ?>" value="<?php echo htmlspecialchars( esc_attr( $options['footer_copyright_text'] ) ); ?>" />
					</td>
					<td class="info">
						<p><?php _e( 'Enter your copyright text that will appear in the right side of the footer. It can contain HTML if you\'d like.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'Tip: Use "' . htmlspecialchars('&copy;') . '" to generate a copyright symbol.', 'churchthemes' ); ?></em></p>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'Analytics Code', 'churchthemes' ); ?></th>
					<td class="option">
						<textarea id="ct_theme_options[analytics_code]" cols="35" rows="5" name="ct_theme_options[analytics_code]"><?php echo esc_textarea( $options['analytics_code'] ); ?></textarea>
					</td>
					<td class="info">
						<p><?php _e( 'You can paste your ', 'churchthemes' ); ?><a href="http://www.google.com/analytics/" target="_blank"><?php _e( 'Google Analytics', 'churchthemes' ); ?></a><?php _e( ' or other tracking code here.', 'churchthemes' ); ?></p>
						<p><?php _e( 'This code will be automatically added just above the &lt;/body&gt; tag of your website.', 'churchthemes' ); ?></p>
					</td>
				</tr>

				<tr class="top">
					<th scope="row"><?php _e( 'Custom CSS', 'churchthemes' ); ?></th>
					<td class="option">
						<textarea id="ct_theme_options[custom_css]" cols="35" rows="10" name="ct_theme_options[custom_css]"><?php echo esc_textarea( $options['custom_css'] ); ?></textarea>
					</td>
					<td class="info">
						<p><?php _e( 'Want to add any custom CSS code? Put it in here, and the rest is taken care of. This overrides all other stylesheets.', 'churchthemes' ); ?></p>
						<p><em><?php _e( 'Example: a.button{color:green}', 'churchthemes' ); ?></em></p>
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
function ct_theme_options_validate( $input ) {
	global $select_target, $select_layout;

	// Say our text option must be safe text with no HTML tags
	if ( ! isset( $input['sometext'] ) )
		$input['sometext'] = null;
	$input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );

	// Our select option must actually be in our array of select options
	if ( ! isset( $input['select1'] ) || ! array_key_exists( $input['select1'], $select_target ) )
		$input['select1'] = null;

	if ( ! isset( $input['select2'] ) || ! array_key_exists( $input['select2'], $select_layout ) )
		$input['select2'] = null;

	// Say our textarea option must be safe text with the allowed tags for posts
	if ( ! isset( $input['sometextarea'] ) )
		$input['sometextarea'] = null;
	$input['sometextarea'] = wp_filter_post_kses( $input['sometextarea'] );

	return $input;
}

// adapted from http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/

?>