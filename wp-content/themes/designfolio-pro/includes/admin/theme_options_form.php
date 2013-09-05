	<div class="wrap pc_settings">

		<div class="icon32" id="icon-themes"><br></div>
		<img src="<?php echo PC_THEME_ROOT_URI.'/images/designfolio-options-logo.png'; ?>" class="optionslogo" />
		
		<div id="pc-buttons">
			<a class="button-secondary pc-lower" href="http://www.presscoders.com/designfolio-setup/" target="_blank">Theme Setup Tutorials</a>
			<a class="button-secondary pc-lower" href="http://www.presscoders.com/contact/" target="_blank">Support</a>
			<a href="https://twitter.com/#!/presscoders" target="_blank"><img src="<?php echo PC_THEME_ROOT_URI.'/images/twitter.png'; ?>" class="pc-icon" /></a>
			<a href="https://www.facebook.com/PressCoders" target="_blank"><img src="<?php echo PC_THEME_ROOT_URI.'/images/facebook.png'; ?>" class="pc-icon" /></a>
		</div>

		<?php
			// Check to see if user clicked on the reset options button
			if(isset($_POST['reset_options'])) {
				// Access theme defaults
				global $pc_default_options;
				
				// Reset theme defaults
				update_option(PC_OPTIONS_DB_NAME, $pc_default_options);

				// Display update notice here
				?>
				<div class="error"><p><?php printf( __( '%s theme options have been reset!', 'presscoders' ), PC_THEME_NAME ); ?></p></div>
				<?php
				PC_Utility::pc_fadeout_element('.error'); // fadeout .updated class
			}

			// Check to see if user clicked on the reset options button
			if( isset($_GET['settings-updated']) && !isset($_POST['reset_options']) ) {
				?>
				<div class="updated"><p><?php printf( __( '%s theme options updated!', 'presscoders' ), PC_THEME_NAME ); ?></p></div>
				<?php
				PC_Utility::pc_fadeout_element(); // fadeout .updated class
			}
		?>

		<!-- Start Main Form -->
		<form name="<?php echo PC_THEME_NAME_SLUG; ?>_options_form" method="post" action="options.php">
			
			<?php settings_fields( PC_THEME_OPTIONS_GROUP ); ?>
			<?php $options = get_option(PC_OPTIONS_DB_NAME); ?>

			<?php
				/* Add theme specific JS/jQuery via this hook. */
				PC_Hooks::pc_theme_option_js(); /* Framework hook wrapper */
			?>

      <div id="pc-options-wrap">

		<div class="ltinfo">
				
			<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/palette.png'; ?>" width="32" height="32" class="optionsicon" />
			<h3><?php _e( 'Custom Colors and Layout', 'presscoders' ); ?></h3>
			 
			<p><?php _e( 'Customize your site colors, and column layout for posts/pages.', 'presscoders' ); ?></p>
				
		</div><!-- .ltinfo -->
				
		<div class="rtoptions">

			<div class="box">
				<select name='<?php echo PC_OPTIONS_DB_NAME; ?>[<?php echo PC_DEFAULT_LAYOUT_THEME_OPTION; ?>]'>
					<option value='1-col' <?php selected('1-col', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '1-Column (full width)', 'presscoders' ); ?></option>
					<option value='2-col-l' <?php selected('2-col-l', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '2-Column Sidebar Left', 'presscoders' ); ?></option>
					<option value='2-col-r' <?php selected('2-col-r', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '2-Column Sidebar Right', 'presscoders' ); ?></option>
					<option value='3-col-l' <?php selected('3-col-l', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '3-Column Sidebars Left', 'presscoders' ); ?></option>
					<option value='3-col-r' <?php selected('3-col-r', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '3-Column Sidebars Right', 'presscoders' ); ?></option>
					<option value='3-col-c' <?php selected('3-col-c', $options[ PC_DEFAULT_LAYOUT_THEME_OPTION ]); ?>><?php _e( '3-Column Content Center', 'presscoders' ); ?></option>
				</select>&nbsp;&nbsp;<?php _e( 'Default Page Layout', 'presscoders' ); ?>
				<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="<?php _e( 'This can be overridden for individual posts/pages.', 'presscoders' ); ?>" />
			</div>

            <?php
				/* Add theme specific custom color/layout options via this hook. */
                PC_Hooks::pc_set_theme_option_fields_custom_colors(); /* Framework hook wrapper */
            ?>

		</div><!-- .rtoptions -->
				
		<div class="line"></div>
				
        <div class="ltinfo">
			<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/favicon-icon.png'; ?>" width="32" height="32" class="optionsicon" />
            <h3><?php _e( 'Custom Favicon', 'presscoders' ); ?></h3>

			<p><?php _e( 'Add a custom favicon image url here.', 'presscoders' ); ?></p>

		</div><!-- .ltinfo -->

		<div class="rtoptions">
			<?php $favicon = PC_Utility::get_custom_favicon(); ?>
            <div class="box">
				<input type="text" class="gray" name="<?php echo PC_OPTIONS_DB_NAME; ?>[txt_favicon]" value="<?php echo $options[ 'txt_favicon' ]; ?>" size="60" />
                <img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="<?php _e( 'Specify a valid favicon url with extension .ico, .png, .jpg, or .gif. An image size of 16x16 pixels is recommended. Tip: use the media library to upload a favicon.', 'presscoders' ); ?>" />
			</div>
			<div><?php _e( 'Current active favicon image:', 'presscoders' ); ?> <img src="<?php echo $favicon; ?>" width="16" height="16" /></div>
		</div><!-- .rtoptions -->

        <div class="line"></div>

		<?php
			/* Add theme specific default settings vis this hook. */
            PC_Hooks::pc_set_theme_option_fields_1(); /* Framework hook wrapper */
		?>

		<div class="ltinfo">
				
			<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/sprocket-32.png'; ?>" width="32" height="32" class="optionsicon" />
			<h3><?php _e( 'Miscellaneous Options', 'presscoders' ); ?></h3>
			 
			<p><?php _e( 'Various options that affect the theme appearance.', 'presscoders' ); ?></p>
				
		</div><!-- .ltinfo -->
				
		<div class="rtoptions">

			<div class="box">
 				<label><input name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_show_social_buttons]" type="checkbox" value="1" class="alignleft" <?php if (isset($options[ 'chk_show_social_buttons' ])) { checked('1', $options[ 'chk_show_social_buttons' ]); } ?> /><?php _e( 'Show Twitter/Facebook Buttons?', 'presscoders' ); ?></label>
			</div>

            <?php
				/* Add theme specific misc options via this hook. */
                PC_Hooks::pc_set_theme_option_fields_misc(); /* Framework hook wrapper */
            ?>

		</div><!-- .rtoptions -->
		
		<div class="line"></div>

		<div class="ltinfo">
			 
			<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/sprocket-32.png'; ?>" width="32" height="32" class="optionsicon" />
			<h3><?php _e( 'General Settings', 'presscoders' ); ?></h3>
			 
		</div><!-- .ltinfo -->
		
		<div class="rtoptions">

			<div class="box">
				<label><?php _e( 'Admin E-mail:', 'presscoders' ); ?> <input type="text" class="gray" style="width: 220px;" name="<?php echo PC_OPTIONS_DB_NAME; ?>[<?php echo PC_ADMIN_EMAIL_TEXTBOX; ?>]" value="<?php echo $options[ PC_ADMIN_EMAIL_TEXTBOX ]; ?>" />
					<img src="<?php echo PC_THEME_ROOT_URI.'/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="<?php _e( 'The contact form page template sends emails to this address.', 'presscoders' ); ?>" />
				</label>
			</div>

		</div><!-- .rtoptions -->
				
		<?php
			/* Add theme specific general settings options via this hook. */
			PC_Hooks::pc_set_theme_option_fields_gs(); /* Framework hook wrapper */
		?>
		
      </div><!-- .pc-options-wrap -->

		<span class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'presscoders' ); ?>" /></span>

		</form> <!-- main form closing tag -->

		<form action="<?php echo PC_Utility::currURL(); // current page url ?>" method="post" id="pc-theme-options-reset" style="display:inline;">
			<span class="submit-theme_options-reset">
				<input type="submit" onclick="return confirm('Are you sure? All theme options will be reset to their default settings!');" class="button submit-button reset-button" value="Reset <?php echo PC_THEME_NAME; ?> Options" name="pc_reset">
				<input type="hidden" name="reset_options" value="true">
			</span>
		</form>

	</div><!-- .wrap pc_settings -->