<?php
/*
Simple:Press
Profile Overview Form
$LastChangedDate: 2013-08-28 08:14:00 -0700 (Wed, 28 Aug 2013) $
$Rev: 10604 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormAccount').ajaxForm({
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
$out.= sp_text('On this panel, you may edit your Account Settings. Please note, you cannot change your Login Name.');
$out.= '</p>';
$out.= '<hr>';

# start the form
$out.= '<div class="spProfileAccount">';

$ahahURL = SFHOMEURL.'index.php?sp_ahah=profile-save&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;form=$thisSlug&amp;userid=$userid";
$out.= '<form action="'.$ahahURL.'" method="post" name="spProfileFormAccount" id="spProfileFormAccount" class="spProfileForm">';
$out.= sp_create_nonce('forum-profile');

$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileAccountForm_top', $out, $userid);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Login Name').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="text" disabled="disabled" class="spControl" name="login" value="'.esc_attr($spProfileUser->user_login).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileAccountLoginName', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Email Address').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="hidden" class="spControl" name="curemail" id="curemail" value="'.esc_attr($spProfileUser->user_email).'" />';
$tout.= '<input type="text" class="spControl" name="email" id="email" value="'.esc_attr($spProfileUser->user_email).'" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserEmailAddress', $tout, $userid, $thisSlug);

$tout = '';
$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('New Password').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="password" autocomplete="off" class="spControl" name="password1" id="pw1" value="" />';
$tout.= '</div>';

$tout.= '<div class="spColumnSection spProfileLeftCol">';
$tout.= '<p class="spProfileLabel">'.sp_text('Confirm New Password').': </p>';
$tout.= '</div>';
$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
$tout.= '<div class="spColumnSection spProfileRightCol">';
$tout.= '<input type="password" autocomplete="off" class="spControl" name="password2" id="pw2" value="" />';
$tout.= '</div>';
$out.= apply_filters('sph_ProfileUserNewPassword', $tout, $userid, $thisSlug);

$out = apply_filters('sph_ProfileAccountForm_bottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

$out.= '<div class="spProfileFormSubmit">';
$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Account').'" />';
$out.= '</div>';
$out.= '</form>';

$out.= "</div>\n";

$out = apply_filters('sph_ProfileAccountForm', $out, $userid);
echo $out;
?>