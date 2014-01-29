<?php
/*
Simple:Press
Ahah call save Profile data
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_api_support();

# workaround function for php installs without exif.  leave original function since this is slower.
if (!function_exists('exif_imagetype')) {
    function exif_imagetype($filename) {
    	if ((list($width, $height, $type, $attr) = getimagesize(str_replace(' ', '%20', $filename))) !== false) return $type;
    	return false;
    }
}

do_action('sph_ProfileSaveStart');

$message = sp_UpdateProfile();

$response = array('type' => '', 'message' => '');
$response['type'] = $message['type'];
$response['message'] = $message['text'];

print json_encode($response);

die();

##############################

function sp_UpdateProfile() {
	global $spGlobals, $spThisUser;

	# make sure nonce is there
	check_admin_referer('forum-profile', 'forum-profile');

    $message = array();

	# dont update forum if its locked down
    if ($spGlobals['lockdown']) {
    	$message['type'] = 'error';
		$message['text'] = sp_text('This forum is currently locked - access is read only - profile not updated');
		return $message;
    }

	# do we have a form to update?
	if (isset($_GET['form'])) {
		$thisForm = sp_esc_str($_GET['form']);
	} else {
    	$message['type'] = 'error';
		$message['text'] = sp_text('Profile update aborted - no valid form');
		return $message;
	}

	# do we have an actual user to update?
	if (isset($_GET['userid'])) {
		$thisUser = sp_esc_int($_GET['userid']);
	} else {
    	$message['type'] = 'error';
		$message['text'] = sp_text('Profile update aborted - no valid user');
		return $message;
	}

	# Check the user ID for current user of admin edit
	if ($thisUser != $spThisUser->ID && !$spThisUser->admin) {
    	$message['type'] = 'error';
		$message['text'] = sp_text('Profile update aborted - no valid user');
		return $message;
	}

	if (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw) {
		$pass1 = $pass2 = '';
		if (isset($_POST['password1'])) $pass1 = $_POST['password1'];
		if (isset($_POST['password2'])) $pass2 = $_POST['password2'];
		if (empty($pass1) || empty($pass2) || ($pass1 != $pass2)) {
	    	$message['type'] = 'error';
			$message['text'] = sp_text('Cannot save profile until password has been changed');
			return $message;
		}
	}

    # form save filter
    $thisForm = apply_filters('sph_profile_save_thisForm', $thisForm);

	# valid save attempt, so lets process the save
	switch ($thisForm) {
		case 'show-memberships': # update memberships
			# any usergroup removals?
			if (isset($_POST['usergroup_leave'])) {
                foreach ($_POST['usergroup_leave'] as $membership) {
					sp_remove_membership(sp_esc_str($membership), $thisUser);
				}
			}

			# any usergroup joins?
            if (isset($_POST['usergroup_join'])) {
                foreach ($_POST['usergroup_join'] as $membership) {
                    sp_add_membership(sp_esc_int($membership), $thisUser);
                }
            }

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileMemberships', $message, $thisUser);

			# output update message
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Memberships updated');
			}

			break;

		case 'account-settings': # update account settings
			# check for password update
			$pass1 = $pass2 = '';
			if (isset($_POST['password1'])) $pass1 = $_POST['password1'];
			if (isset($_POST['password2'])) $pass2 = $_POST['password2'];
			if (!empty($pass1) || !empty($pass2)) {
				if ($pass1 != $pass2) {
					$message['type'] = 'error';
					$message['text'] = sp_text('Please enter the same password in the two password fields');
					return $message;
				} else {
					# update the password
					$user->ID = (int) $thisUser;
					$user->user_pass = $pass1;
                    wp_update_user(get_object_vars($user));
					if (isset($spThisUser->sp_change_pw) && $spThisUser->sp_change_pw) delete_user_meta($spThisUser->ID, 'sp_change_pw');
				}
			}

			# now check the email is valid and unique
            $update = apply_filters('sph_ProfileUserEmailUpdate', true);
            if ($update) {
    			$curEmail = sp_filter_email_save($_POST['curemail']);
    			$email = sp_filter_email_save($_POST['email']);
    			if ($email != $curEmail) {
    				if (empty($email)) {
    					$message['type'] = 'error';
    					$message['text'] = sp_text('Please enter a valid email address');
    					return $message;
    				} elseif (($owner_id = email_exists($email)) && ($owner_id != $thisUser)) {
    					$message['type'] = 'error';
    					$message['text'] = sp_text('The email address is already registered. Please choose another one');
    					return $message;
    				}

    				# save new email address
    				$sql = 'UPDATE '.SFUSERS." SET user_email='$email' WHERE ID=".$thisUser;
    				spdb_query($sql);
    			}
            }

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileSettings', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Account settings updated');
			}

			break;

		case 'edit-profile': # update profile settings
			# validate any username change
            $update = apply_filters('sph_ProfileUserDisplayNameUpdate', true);
            if ($update) {
    			$spProfile = sp_get_option('sfprofile');
    			if ($spProfile['nameformat'] || $spThisUser->admin) {
    				$display_name = (!empty($_POST['display_name'])) ? trim($_POST['display_name']) : spdb_table(SFUSERS, "ID=$thisUser", 'user_login');
    				$display_name = sp_filter_name_save($display_name);

				    # make sure display name isnt already used
    				if ($_POST['oldname'] != $display_name) {
    					$records = spdb_table(SFMEMBERS, "display_name='$display_name'");
    					if ($records) {
    						foreach ($records as $record) {
    							if ($record->user_id != $thisUser) {
    								$message['type'] = 'error';
    								$message['text'] = $display_name.' '.sp_text('is already in use - please choose a different display name');
    								return $message;
    							}
    						}
    					}

                        # validate display name
                       	$errors = new WP_Error();
                        $user = new stdClass();
                        $user->display_name = $display_name;
                        sp_validate_display_name($errors, true, $user);
                       	if ($errors->get_error_codes()) {
							$message['type'] = 'error';
							$message['text'] = sp_text('The display name you have chosen is not allowed on this site');
							return $message;
                       	}

    					# now save the display name
    					sp_update_member_item($thisUser, 'display_name', $display_name);

    					# Update new users list with changed display name
    					sp_update_newuser_name(sp_filter_name_save($_POST['oldname']), $display_name);

                        # do we need to sync display name with wp?
            			$options = sp_get_member_item($thisUser, 'user_options');
                        if ($options['namesync']) spdb_query('UPDATE '.SFUSERS.' SET display_name="'.$display_name.'" WHERE ID='.$thisUser);
    				}
    			}
            }

			# save the url
            $update = apply_filters('sph_ProfileUserWebsiteUpdate', true);
            if ($update) {
    			$url = sp_filter_url_save($_POST['website']);
	       		$sql = 'UPDATE '.SFUSERS.' SET user_url="'.$url.'" WHERE ID='.$thisUser;
	   	       	spdb_query($sql);
            }

			# update first name, last name, location and biorgraphy
            $update = apply_filters('sph_ProfileUserFirstNameUpdate', true);
            if ($update) update_user_meta($thisUser, 'first_name', sp_filter_name_save(trim($_POST['first_name'])));
            $update = apply_filters('sph_ProfileUserLastNameUpdate', true);
            if ($update) update_user_meta($thisUser, 'last_name', sp_filter_name_save(trim($_POST['last_name'])));
            $update = apply_filters('sph_ProfileUserLocationUpdate', true);
            if ($update) update_user_meta($thisUser, 'location', sp_filter_title_save(trim($_POST['location'])));
            $update = apply_filters('sph_ProfileUserBiographyUpdate', true);
            if ($update) update_user_meta($thisUser, 'description', sp_filter_text_save($_POST['description']));

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileProfile', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Profile settings updated');
			}

			break;

		case 'edit-identities': # update identity settings
			# update the user identities
            $update = apply_filters('sph_ProfileUserAIMUpdate', true);
            if ($update) update_user_meta($thisUser, 'aim', sp_filter_title_save(trim($_POST['aim'])));
            $update = apply_filters('sph_ProfileUserYahooUpdate', true);
            if ($update) update_user_meta($thisUser, 'yim', sp_filter_title_save(trim($_POST['yim'])));
            $update = apply_filters('sph_ProfileUserGoogleUpdate', true);
            if ($update) update_user_meta($thisUser, 'jabber', sp_filter_title_save(trim($_POST['jabber'])));
            $update = apply_filters('sph_ProfileUserMSNUpdate', true);
            if ($update) update_user_meta($thisUser, 'msn', sp_filter_title_save(trim($_POST['msn'])));
            $update = apply_filters('sph_ProfileUserICQUpdate', true);
            if ($update) update_user_meta($thisUser, 'icq', sp_filter_title_save(trim($_POST['icq'])));
            $update = apply_filters('sph_ProfileUserSkypeUpdate', true);
            if ($update) update_user_meta($thisUser, 'skype', sp_filter_title_save(trim($_POST['skype'])));
            $update = apply_filters('sph_ProfileUserFacebookUpdate', true);
            if ($update) update_user_meta($thisUser, 'facebook', sp_filter_title_save(trim($_POST['facebook'])));
            $update = apply_filters('sph_ProfileUserMySpaceUpdate', true);
            if ($update) update_user_meta($thisUser, 'myspace', sp_filter_title_save(trim($_POST['myspace'])));
            $update = apply_filters('sph_ProfileUserTwitterUpdate', true);
            if ($update) update_user_meta($thisUser, 'twitter', sp_filter_title_save(trim($_POST['twitter'])));
            $update = apply_filters('sph_ProfileUserLinkedInUpdate', true);
            if ($update) update_user_meta($thisUser, 'linkedin', sp_filter_title_save(trim($_POST['linkedin'])));
            $update = apply_filters('sph_ProfileUserYouTubeUpdate', true);
            if ($update) update_user_meta($thisUser, 'youtube', sp_filter_title_save(trim($_POST['youtube'])));
            $update = apply_filters('sph_ProfileUserGooglePlusUpdate', true);
            if ($update) update_user_meta($thisUser, 'googleplus', sp_filter_title_save(trim($_POST['googleplus'])));

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileIdentities', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Identities updated');
			}

			break;

		case 'avatar-upload': # upload avatar
			# validate uploaded file
			global $spPaths;
			$uploaddir = SF_STORE_DIR.'/'.$spPaths['avatars'].'/';
			$filename = basename($_FILES['avatar-upload']['name']);

			# did we get an avatar to upload?
			if (empty($_FILES['avatar-upload']['name'])) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the avatar filename was empty');
				return $message;
			}

			# Verify the file extension
			$path = pathinfo($filename);
			$ext = strtolower($path['extension']);
			if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, only JPG, JPEG, PNG, or GIF files are allowed');
				return $message;
			}

			# check image file mimetype
			$mimetype = 0;
			$mimetype = exif_imagetype($_FILES['avatar-upload']['tmp_name']);
			if (empty($mimetype) || $mimetype == 0 || $mimetype > 3) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the avatar file is an invalid format');
				return $message;
			}

			# make sure file extension and mime type actually match
			if (($mimetype == 1 && $ext != 'gif') ||
				($mimetype == 2 && ($ext != 'jpg' && $ext != 'jpeg')) ||
				($mimetype == 3 && $ext != 'png')) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the file mime type does not match file extension');
				return $message;
			}

			# Clean up file name just in case
			$filename = date('U').sp_filter_filename_save(basename($_FILES['avatar-upload']['name']));
			$uploadfile = $uploaddir.$filename;

			# check for existence
			if (file_exists($uploadfile)) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the avatar file already exists');
				return $message;
			}

			# check file size against limit if provided
			$spAvatars = sp_get_option('sfavatars');
			if ($_FILES['avatar-upload']['size'] > $spAvatars['sfavatarfilesize']) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the avatar file exceeds the maximum allowed size');
				return $message;
			}

			# valid avatar, so try moving the uploaded file to the avatar storage directory
			if (move_uploaded_file($_FILES['avatar-upload']['tmp_name'], $uploadfile)) {
				@chmod("$uploadfile", 0644);

                # do we need to resize?
               	$sfavatars = sp_get_option('sfavatars');
                if ($sfavatars['sfavatarresize']) {
                    $editor = wp_get_image_editor($uploadfile);
                    if (is_wp_error($editor)) {
                        @unlink($uploadfile);
        				$message['type'] = 'error';
        				$message['text'] = sp_text('Sorry, there was a problem resizing the avatar');
        				return $message;
                    } else {
                        $editor->resize($sfavatars['sfavatarsize'], $sfavatars['sfavatarsize'], true);
                        $imageinfo = $editor->save($uploadfile);
                        $filename = $imageinfo['file'];
                    }
                }

				# update member avatar data
				$avatar = sp_get_member_item($thisUser, 'avatar');
				$avatar['uploaded'] = $filename;
				sp_update_member_item($thisUser, 'avatar', $avatar);
			} else {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, the avatar file could not be moved to the avatar storage location');
				return $message;
			}

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarUpload', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Uploaded avatar updated');
			}

			break;

		case 'avatar-pool': # pool avatar
			# get pool avatar name
			$filename = sp_filter_filename_save($_POST['spPoolAvatar']);

			# error if no pool avatar provided
			if (empty($filename)) {
				$message['type'] = 'error';
				$message['text'] = sp_text('Sorry, you must select a pool avatar before trying to save it');
				return $message;
			}

			# save the pool avatar
			$avatar = sp_get_member_item($thisUser, 'avatar');
			$avatar['pool'] = $filename;
			sp_update_member_item($thisUser, 'avatar', $avatar);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarPool', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Pool avatar updated');
			}

			break;

		case 'avatar-remote': # remote avatar
			# get remote avatar name
			$filename = sp_filter_url_save($_POST['spAvatarRemote']);
			$avatar = sp_get_member_item($thisUser, 'avatar');
			$avatar['remote'] = $filename;
			sp_update_member_item($thisUser, 'avatar', $avatar);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileAvatarRemote', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Remote avatar updated');
			}

			break;

		case 'edit-signature': # save signature
			# Check if maxmium links has been exceeded
            $numLinks = substr_count($_POST['postitem'], '</a>');
			$spFilters = sp_get_option('sffilters');
			if (!sp_get_auth('create_links', 'global', $thisUser) && $numLinks > 0 && !$spThisUser->admin) {
				    $message['type'] = 'error';
				    $message['text'] = sp_text('You are not allowed to put links in signatures');
				    return $message;
            }
			if (sp_get_auth('create_links', 'global', $thisUser) && $spFilters['sfmaxlinks'] != 0 && $numLinks > $spFilters['sfmaxlinks'] && !$spThisUser->admin) {
					$message['type'] = 'error';
					$message['text'] = sp_text('Maximum number of allowed links exceeded in signature').': '.$spFilters['sfmaxlinks'].' '.sp_text('allowed');
					return $message;
            }
			$sig = esc_sql(sp_filter_save_kses(trim($_POST['postitem'])));
			sp_update_member_item($thisUser, 'signature', $sig);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileSignature', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Signature updated');
			}

			break;

		case 'edit-photos': # save photos
			$photos = array();
			$spProfileOptions = sp_get_option('sfprofile');
			for ($x=0; $x < $spProfileOptions['photosmax']; $x++) {
				$photos[$x] = sp_filter_url_save($_POST['photo'.$x]);
			}
			update_user_meta($thisUser, 'photos', $photos);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfilePhotos', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Photos updated');
			}

			break;

		case 'edit-global-options': # save global options
			$options = sp_get_member_item($thisUser, 'user_options');
			$options['hidestatus'] = (isset($_POST['hidestatus'])) ? true : false;
            $update = apply_filters('sph_ProfileUserSyncNameUpdate', true);
            if ($update) $options['namesync'] = (isset($_POST['namesync'])) ? true : false;
			sp_update_member_item($thisUser, 'user_options', $options);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileGlobalOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Global options updated');
			}

			break;

		case 'edit-posting-options': # save posting options
            $update = apply_filters('sph_ProfileUserEditorUpdate', true);
            if ($update) {
    			$options = sp_get_member_item($thisUser, 'user_options');
	       		if (isset($_POST['editor'])) $options['editor'] = sp_esc_int($_POST['editor']);
	   	       	sp_update_member_item($thisUser, 'user_options', $options);
            }

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfilePostingOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Posting options updated');
			}

			break;

		case 'edit-display-options': # save display options
			$options = sp_get_member_item($thisUser, 'user_options');
			if (isset($_POST['timezone'])) {
				if (preg_match('/^UTC[+-]/', $_POST['timezone']) ) {
					# correct for manual UTC offets
					$userOffset = preg_replace('/UTC\+?/', '', $_POST['timezone']) * 3600;
				} else {
					# get timezone offset for user
					$date_time_zone_selected = new DateTimeZone(sp_esc_str($_POST['timezone']));
					$userOffset = timezone_offset_get($date_time_zone_selected, date_create());
				}

				# get timezone offset for server based on wp settings
				$wptz = get_option('timezone_string');
				if (empty($wptz)) {
					$serverOffset = get_option('gmt_offset');
				} else {
					$date_time_zone_selected = new DateTimeZone($wptz);
					$serverOffset = timezone_offset_get($date_time_zone_selected, date_create());
				}

				# calculate time offset between user and server
				$options['timezone'] = (int) round(($userOffset - $serverOffset) / 3600, 2);
				$options['timezone_string'] = sp_esc_str($_POST['timezone']);
			} else {
				$options['timezone'] = 0;
				$options['timezone_string'] = 'UTC';
			}

			if (isset($_POST['unreadposts'])) {
                $sfcontrols = sp_get_option('sfcontrols');
				$options['unreadposts'] = is_numeric($_POST['unreadposts']) ? max(min(sp_esc_int($_POST['unreadposts']), $sfcontrols['sfmaxunreadposts']), 0) : $sfcontrols['sfdefunreadposts'];
	   	    }

			$options['topicASC'] = isset($_POST['topicASC']);
			$options['postDESC'] = isset($_POST['postDESC']);

			sp_update_member_item($thisUser, 'user_options', $options);

			# fire action for plugins
			$message = apply_filters('sph_UpdateProfileDisplayOptions', $message, $thisUser);

			# output profile save status
			if (empty($message)) {
				$message['type'] = 'success';
				$message['text'] = sp_text('Display options updated');
			}

			break;

		default:
			break;
	}

	# let plugins do their thing on success
	$message = apply_filters('sph_ProfileFormSave_'.$thisForm, $message, $thisUser, $thisForm);
	do_action('sph_UpdateProfile', $thisUser, $thisForm);

	# done saving - return the messages
	return $message;
}
?>