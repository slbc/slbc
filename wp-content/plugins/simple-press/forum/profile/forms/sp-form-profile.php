<?php
/*
Simple:Press
Profile Profile Form
$LastChangedDate: 2013-08-24 11:13:22 -0700 (Sat, 24 Aug 2013) $
$Rev: 10585 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormProfile').ajaxForm({
        dataType: 'json',
		success: function(response) {
            if (response.type == 'success') {
        	   spjDisplayNotification(0, response.message);
            } else {
        	   spjDisplayNotification(1, response.message);
            }
		}
	});
})
</script>
<?php
$out = '';
$out.= '<p>';
$out.= sp_text('On this panel, you may edit your Profile. Please note, you cannot change your Login Name.');
$out.= '</p>';
$out.= '<hr>';

# start the form
$out.= '<div class="spProfileProfile">';

$ahahURL = SFHOMEURL.'index.php?sp_ahah=profile-save&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;form=$thisSlug&amp;userid=$userid";
$out.= '<form action="'.$ahahURL.'" method="post" name="spProfileFormProfile" id="spProfileFormProfile" class="spProfileForm">';
$out.= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileProfileFormTop', $out, $userid);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Login Name').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" disabled="disabled" class="spControl" name="login" value="'.esc_attr($spProfileUser->user_login).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserLoginName', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Display Name').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="hidden" class="spControl" name="oldname" id="oldname" value="'.sp_filter_name_display($spProfileUser->display_name).'" />';

# Check to see if the display name is allowed to be edited by the user.
# If it is not, disable the field.
$disabled_text = ($spProfileOptions['nameformat'] || $spThisUser->admin) ? '' : 'disabled="disabled" ';

$tout.= '<input type="text" '.$disabled_text.'class="spControl" name="display_name" id="display_name" value="'.sp_filter_name_display($spProfileUser->display_name).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserDisplayName', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('First Name').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" class="spControl" name="first_name" id="first_name" value="'.sp_filter_name_display($spProfileUser->first_name).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserFirstName', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Last Name').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" class="spControl" name="last_name" id="last_name" value="'.sp_filter_name_display($spProfileUser->last_name).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserLastName', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Website').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" class="spControl" name="website" id="website" value="'.sp_filter_url_display($spProfileUser->user_url).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserWebsite', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Location').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" class="spControl" name="location" id="location" value="'.sp_filter_title_display($spProfileUser->location).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserLocation', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Short Biography').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<textarea class="spControl" name="description" rows="4">'.sp_filter_text_edit($spProfileUser->description).'</textarea>';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserBiography', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileProfileFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out.= '<div class="spProfileFormSubmit">';
$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Profile').'" />';
$out.= '</div>';
$out.= '</form>';

$out.= "</div>\n";

$out = apply_filters('sph_ProfileProfileForm', $out, $userid);
echo $out;
?>