<?php
// Set-up Action and Filter Hooks
// register_activation_hook(__FILE__, 'cpo_add_defaults');
add_action('admin_init', 'cpo_init' );
add_action('admin_menu', 'cpo_add_options_page');
//add_filter('plugin_action_links', 'cpo_plugin_action_links', 10, 2 );

// Define default option settings
function cpo_add_defaults() {
	$tmp = get_option('cpo_core_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('cpo_core_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	"events" => "1",
						"groups" => "1",
						"people" => "1",
						"prayers" => "1",
						"photos" => "1",
						"widgetcontent" => "1",
		);
		update_option('cpo_core_options', $arr);
	}
}

// Init plugin options to white list our options
function cpo_init(){
	register_setting( 'cpo_core_options', 'cpo_core_options', '' );
	register_setting( 'cpo_plugin_options', 'cpo_options', '' );
	register_setting('churchpack_license', 'churchpack_license_key', 'churchpack_license' );
}

function churchpack_option( $option ) {
	$options = get_option( 'cpo_options' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}

// Add menu page
function cpo_add_options_page() {
	$main_page = add_menu_page( __('Church Pack', 'churchpack'), 'Church Pack', 'manage_options', 'churchpack', 'cpo_render_dashboard');
	$main_page2 = add_submenu_page( 'churchpack', 'Church Pack Dashboard', 'Dashboard', 'manage_options', 'churchpack', 'cpo_render_dashboard' );
	$info_page = add_submenu_page('churchpack', 'Church Pack Settings', 'Settings', 'manage_options', 'churchpack-churchinfo', 'cpo_render_churchinfo' );
	$support_page = add_submenu_page('churchpack', 'Church Pack Support', 'Support', 'manage_options', 'churchpack-support', 'cpo_render_support_page' );
	add_action( "admin_print_scripts-$main_page", 'cpo_loadjs_admin_head' );
	add_action( "admin_print_scripts-$main_page2", 'cpo_loadjs_admin_head' );
	add_action( "admin_print_scripts-$info_page", 'cpo_loadjs_admin_head' );
	add_action( "admin_print_scripts-$support_page", 'cpo_loadjs_admin_head' );
}

// Add JS and CSS to Admin Header
function cpo_loadjs_admin_head() {
	wp_enqueue_script('jquery');
}

// Render the Dashboard
function cpo_render_dashboard() {
?>
<div class="wrap about-wrap">

<h1><?php _e( 'Welcome to Church Pack!' ); ?></h1>

<div class="about-text"><?php _e( 'Using Church Pack will improve your church or ministry website by adding several key features.' ); ?></div>

<div class="wp-badge"><?php _e( 'Church Pack<br/> v. 1.2.3' ); ?></div>

<h2 class="nav-tab-wrapper">
	<a href="admin.php?page=churchpack" class="nav-tab nav-tab-active">
		<?php _e( 'What&#8217;s Available' ); ?>
	</a><a href="admin.php?page=churchpack-churchinfo" class="nav-tab">
		<?php _e( 'Settings' ); ?>
	</a><a href="admin.php?page=churchpack-support" class="nav-tab">
		<?php _e( 'Support' ); ?>
	</a>
</h2>

<p><?php _e( 'Below you may find out more information about the Church Pack components and enable the ones you\'d like to try.' ); ?></p>

<form method="post" action="options.php">
	<?php settings_fields('cpo_core_options'); ?>
	<?php $coreoptions = get_option('cpo_core_options'); ?>

<div class="changelog">
	
	<!-- People -->
			<h4><?php _e( 'People' ); ?></h4>
			<!--<img src="images/screenshots/coediting.png" class="element-screenshot" />-->
			<p><?php _e( 'This can be used to add a listing of staff, teachers, deacons, elders, or even all members. People can be put into categories and then can be added to the frontend of your site with a simple shortcode to display all people or just a specific category. Images are automatically cropped and displayed. ' ); ?></p>
			<label><input id="people" name="cpo_core_options[people]" type="checkbox" value="1" <?php if (isset($coreoptions['people'])) { checked('1', $coreoptions['people']); } ?> /> <?php _e('Enable People', 'churchpack'); ?></label><br />
	
	<!-- Groups -->
			<h4><?php _e( 'Groups' ); ?></h4>
			<p><?php _e( 'Groups are designed to add a listing of ministries, small groups, classes, etc. Groups can be put into categories and added to the frontend of your site like People. ' ); ?></p>
			<label><input id="groups" name="cpo_core_options[groups]" type="checkbox" value="1" <?php if (isset($coreoptions['groups'])) { checked('1', $coreoptions['groups']); } ?> /> <?php _e('Enable Groups', 'churchpack'); ?></label><br />

	<!-- Widget Content -->
			<h4><?php _e( 'Widget Content' ); ?></h4>
			<p><?php _e( 'This allows you to create content in the regular WordPress editor and then add it by a widget to your site. No more formatting with HTML required!' ); ?></p>
			<label><input id="widgetcontent" name="cpo_core_options[widgetcontent]" type="checkbox" value="1" <?php if (isset($coreoptions['widgetcontent'])) { checked('1', $coreoptions['widgetcontent']); } ?> /> <?php _e('Enable Widget Content', 'churchpack'); ?></label><br />
	
	<!-- Photo Albums -->
			<h4><?php _e( 'Photo Albums' ); ?></h4>
			<!--<img src="images/screenshots/coediting.png" class="element-screenshot" />-->
			<p><?php _e( 'Easily add albums to your church website. Photos are added using WordPress\' core features & displayed nicely on your site.' ); ?></p>
			<label><input id="photos" name="cpo_core_options[photos]" type="checkbox" value="1" <?php if (isset($coreoptions['photos'])) { checked('1', $coreoptions['photos']); } ?> /> <?php _e('Enable Photo Albums', 'churchpack'); ?></label><br />
	
	<!-- Events -->
			<h4><?php _e( 'Events' ); ?></h4>
			<p><?php _e( 'You can easily add one-time, full day, multi-day, and recurring events. There are no complex options or kitchen sink type features that clutter most event plugins. The frontend display is clean and responsive. The main calendar is added with a shortcode and no extra template files are required.  <em>Note: This component is only in the Pro version .</em>' ); ?></p>

	<!-- Shortcodes -->
			<h4><?php _e( 'Shortcodes' ); ?></h4>
			<p><?php _e( 'Several easy to use shortcodes are built into the visual editor to allow you to quickly add tabs, toggles, buttons, hightlighted text boxes, Google maps, and much more. Enabled by default.' ); ?></p>
			<label><input id="shortcodes" name="cpo_core_options[shortcodes]" type="checkbox" value="1" <?php if (isset($coreoptions['shortcodes'])) { checked('1', $coreoptions['shortcodes']); } ?> /> <?php _e('Disable Shortcodes', 'churchpack'); ?></label><br />
	
</div>
	

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'churchpack') ?>" />
		</p>
</form>
		<p style="margin-top:15px;">
			<?php _e('Fugue Icons &copy; 2012 Yusuke Kamiyamane. All rights reserved. These icons are licensed under a <a href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 License.</a>', 'churchpack'); ?>
		</p>
	</div>
	<?php	
}

function cpo_render_churchinfo() {
	global $pagenow;
	settings_fields('cpo_plugin_options');
	$options = get_option('cpo_options');
	?>
	<div class="wrap">
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-tools"><br></div>
		<h2><?php _e('Settings', 'churchpack'); ?></h2>
		<div id="poststuff">
			<form method="post" action="options.php">
			<?php 
			settings_fields('cpo_plugin_options');
			$options = get_option('cpo_options');
				
					
					echo '<table class="form-table">'; ?>
							<tr><th scope="row">Church Name</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_name]" value="<?php echo $options['church_name']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">Address</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_address]" value="<?php echo $options['church_address']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">City</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_city]" value="<?php echo $options['church_city']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">State</th>
							<td>
								<input type="text" size="50" name="cpo_options[church_state]" value="<?php echo $options['church_state']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">Zip Code</th>
							<td>
								<input type="text" size="25" name="cpo_options[church_zip]" value="<?php echo $options['church_zip']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">Phone</th>
							<td>
								<input type="text" size="50" name="cpo_options[church_phone]" value="<?php echo $options['church_phone']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">Email</th>
							<td>
								<input type="text" size="50" name="cpo_options[church_email]" value="<?php echo $options['church_email']; ?>" />
							</td>
							</tr>
							<tr><th scope="row">Facebook URL</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_fb]" value="<?php echo $options['church_fb']; ?>" />
								<br /><span style="color:#666666;margin-left:2px;">Enter the full url: http://www.facebook.com/</span>
							</td>
							</tr>
							<tr><th scope="row">Twitter Username</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_twitter]" value="<?php echo $options['church_twitter']; ?>" />
								<br /><span style="color:#666666;margin-left:2px;">Only enter your username</span>
							</td>
							</tr>
							<tr><th scope="row">Vimeo URL</th>
							<td>
								<input type="text" size="75" name="cpo_options[church_vimeo]" value="<?php echo $options['church_vimeo']; ?>" />
								<br /><span style="color:#666666;margin-left:2px;">Enter the full url: http://www.vimeo.com/</span>
							</td>
							</tr>
							<tr><th scope="row">YouTube URL</th>
							<td>
								<input type="text" size="50" name="cpo_options[church_youtube]" value="<?php echo $options['church_youtube']; ?>" />
								<br /><span style="color:#666666;margin-left:2px;">Enter the full url: http://www.youtube.com/</span>
							</td>
							</tr>
							<?php
					
					echo '</table>';
				?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'churchpack') ?>" />
				</p>
			</form>
			
		</div>		
		<p><?php _e('If you need help, please visit <a href="http://www.wpforchurch.com/knowledgebase/" target="_blank">WP for Church</a>', 'churchpack'); ?></p>

	</div>
	<?php	
}

function cpo_render_support_page() {
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-edit-comments"><br></div>
		<h2><?php _e('Church Pack Support', 'churchpack'); ?></h2>
		<p><?php _e('If you need help, please visit <a href="http://www.wpforchurch.com/knowledgebase" target="_blank">the Knowledge base at WP for Church</a>', 'churchpack'); ?></p>
	
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function cpo_validate_options($input) {
	 // strip html from textboxes
	//settings_fields('cpo_plugin_options');
	///$options = get_option('cpo_options');
	///$options['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
		///if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
			///$options['text_string'] = '';
		///}
	///$options['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
	///return $options;
}

?>