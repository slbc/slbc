<?php
/*
Template Name: Contact Form
*/
?>
<?php 

$cf_nameError = '';
$cf_emailError = '';
$cf_commentError = '';
$adminError = '';

// Form submission handler
if(isset($_POST['cf_submitted'])) {

		$cf_options = get_option( PC_OPTIONS_DB_NAME );
		$cf_admin_email = trim($cf_options[ PC_ADMIN_EMAIL_TEXTBOX ]);

		if( empty($cf_admin_email) ) {
			$adminError = sprintf( __( 'Please enter a valid admin email in %s theme options.', 'presscoders' ), PC_THEME_NAME );
			$hasError = true;
		}

		//Check name field is not empty
		if(trim($_POST['cf_contactName']) === '') {
			$cf_nameError =  __('Please enter your name.', 'presscoders' ); 
			$hasError = true;
		} else {
			$name = trim($_POST['cf_contactName']);
		}

		//Check for valid email address
		if(trim($_POST['cf_email']) === '')  {
			$cf_emailError = __('Please enter your email address.', 'presscoders' );
			$hasError = true;
		} else if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim($_POST['cf_email']))) {
			$cf_emailError = __('You entered an invalid email address.', 'presscoders' );
			$hasError = true;
		} else {
			$cf_email = trim($_POST['cf_email']);
		}

		//Check that comments were entered
		if(trim($_POST['cf_comments']) === '') {
			$cf_commentError = __('Please enter a message.', 'presscoders' );
			$hasError = true;
		} else {
			if(function_exists('stripslashes')) {
				$cf_comments = stripslashes(trim($_POST['cf_comments']));
			} else {
				$cf_comments = trim($_POST['cf_comments']);
			}
		}
			
		// Send e-mail if no errors
		if(!isset($hasError)) {
			$emailTo = $cf_admin_email;
			$subject = __('Contact Form Submission from ', 'presscoders').$name;
			$cf_sendCopy = trim($_POST['cf_sendCopy']);
			
			$adminError = sprintf( __( 'Please enter a valid admin email in %s theme options.', 'presscoders' ), PC_THEME_NAME );
			$tmp_name = __( 'Name:', 'presscoders' );
			$tmp_email = __( 'E-mail:', 'presscoders' );
			$tmp_comments = __( 'Comments:', 'presscoders' );

			$body = $tmp_name.' '.$name.'\n\n'.$tmp_email.' '.$cf_email.'\n\n'.$tmp_comments.' '.$cf_comments;
			$headers = __('From: ', 'presscoders').get_bloginfo('name').' <'.$emailTo.'>' . "\r\n" . __('Reply-To: ', 'presscoders' ) . $cf_email;
			
			mail($emailTo, $subject, $body, $headers);

			if($cf_sendCopy == true) {
				$subject = __('You emailed ', 'presscoders' ).get_bloginfo('name');
				$headers = __('From: ', 'presscoders' ) . '<'.$emailTo.'>';
				mail($cf_email, $subject, $body, $headers);
			}
			$emailSent = true;
		}
} ?>
<?php get_header(); ?>

<?php PC_Hooks::pc_after_get_header(); /* Framework hook wrapper */ ?>

	<div id="container">

		<?php PC_Hooks::pc_after_container(); /* Framework hook wrapper */ ?>

		<div id="contentwrap" <?php echo PC_Utility::contentwrap_layout_classes(); ?>>

			<?php PC_Hooks::pc_before_content(); /* Framework hook wrapper */ ?>

			<div class="<?php echo PC_Utility::content_layout_classes_primary(); ?>">

				<?php PC_Hooks::pc_after_content_open(); /* Framework hook wrapper */ ?>

				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

					<div id="contact-page">
						
						<h1 class="page-title entry-title"><?php the_title(); ?></h1>
						
						<div class="entry">
							<?php
								the_content();
								wp_link_pages( array( 'before' => '<div class="page-link">', 'after' => '</div>' ) );
							?>
						</div>

						<?php if(isset($emailSent) && $emailSent == true) { ?>
							<p class="alert"><?php _e('Your email was sent successfully. Thank you for contacting us.', 'presscoders'); ?></p>
						<?php } ?>
												
							<?php if(isset($hasError) ) { ?>
								<p class="contact-error"><?php _e('There was an error submitting the form.', 'presscoders' ); ?></p>
								<?php if($adminError != '') { ?>
									<p class="contact-error"><?php echo $adminError; ?></p>
								<?php } ?>
							<?php } ?>
							
							<form action="<?php the_permalink(); ?>" id="contactForm" method="post">
								<div class="contactform">
									<div class="cffield">
									<label for="cf_contactName"><?php _e('Name', 'presscoders' ); ?></label>
										<input type="text" name="cf_contactName" id="cf_contactName" value="<?php if(isset($_POST['cf_contactName'])) echo $_POST['cf_contactName'];?>" class="txt requiredField" />
										<?php if($cf_nameError != '') { ?>
											<span class="contact-error"><?php echo $cf_nameError;?></span> 
										<?php } ?>
									</div>
									
									<div class="cffield">
									<label for="cf_email"><?php _e('Email', 'presscoders' ); ?></label>
										<input type="text" name="cf_email" id="cf_email" value="<?php if(isset($_POST['cf_email']))  echo $_POST['cf_email'];?>" class="txt requiredField email" />
										<?php if($cf_emailError != '') { ?>
											<span class="contact-error"><?php echo $cf_emailError;?></span>
										<?php } ?>
									</div>
									
									<div class="cffield">
									<label for="cf_message"><?php _e('Message', 'presscoders'); ?></label>
										<textarea name="cf_comments" id="cf_message" rows="20" cols="30" class="requiredField"><?php if(isset($_POST['cf_comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['cf_comments']); } else { echo $_POST['cf_comments']; } } ?></textarea>
										<?php if($cf_commentError != '') { ?>
											<span class="contact-error"><?php echo $cf_commentError;?></span> 
										<?php } ?>
									</div>
									<div class="cfcheckbox">
									<input type="checkbox" name="cf_sendCopy" id="cf_sendCopy" value="true"<?php if(isset($_POST['cf_sendCopy']) && $_POST['cf_sendCopy'] == true) echo ' checked="checked"'; ?> /><label for="cf_sendCopy"><?php _e('Send yourself a copy of this email?', 'presscoders' ); ?></label>
									</div>
									<div class="cfsubmit"><input type="hidden" name="cf_submitted" id="cf_submitted" value="true" /><input class="submit button" type="submit" value="<?php _e('Send E-mail', 'presscoders' ); ?>" />
									</div>
								</div>
							</form>
						
					</div><!-- #contact_page -->                 

				<?php endwhile; ?>

			</div><!-- .content -->
			
			<?php PC_Hooks::pc_after_content(); /* Framework hook wrapper */ ?>
		
		</div><!-- #contentwrap -->
	
	</div><!-- #container -->

<?php get_footer(); ?>