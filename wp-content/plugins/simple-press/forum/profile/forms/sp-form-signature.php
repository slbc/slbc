<?php
/*
Simple:Press
Profile Signature Form
$LastChangedDate: 2013-04-25 13:56:18 -0700 (Thu, 25 Apr 2013) $
$Rev: 10207 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$ahahURL = SFHOMEURL.'index.php?sp_ahah=profile&sfnonce='.wp_create_nonce('forum-ahah')."&action=update-sig&user=$userid";
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormSignature').ajaxForm({
        dataType: 'json',
        beforeSerialize: spjEdGetSignature,
		success: function(response) {
            jQuery('#spProfileSignaturePreview').load('<?php echo $ahahURL; ?>');
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
$out.= sp_text('On this panel, you may edit your Signature.');
$out.= '</p>';
$out.= '<hr>';

$out.= '<div class="spProfileSignature">';

$ahahURL = SFHOMEURL.'index.php?sp_ahah=profile-save&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;form=$thisSlug&amp;userid=$userid";
$out.= '<form action="'.$ahahURL.'" method="post" name="spProfileFormSignature" id="spProfileFormSignature" class="spProfileForm">';
$out.= sp_create_nonce('forum-profile');

$out.= '<div class="spEditor">';
$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
$out = apply_filters('sph_ProfileSignatureFormTop', $out, $userid);

# Signature Set
$out.= '<div class="spEditorSection">';
$out.= '<div class="spColumnSection spCenter">';
$out.= '<div class="spEditorTitle">'.sp_text('Set up Your Signature').':</div><br />';
$out.= '</div>';
$out.= '</div>';

$out.= '<div id="spEditorContent">';
$value = esc_html($spProfileUser->signature);
$out.= sp_SetupSigEditor($value);
$spSigImageSize = sp_get_option('sfsigimagesize');
$sigWidth = sp_text('width - none specified').', ';
$sigHeight = sp_text('height - none specified');
if ($spSigImageSize['sfsigwidth'] > 0) $sigWidth = sp_text('width').' - '.$spSigImageSize['sfsigwidth'].', ';
if ($spSigImageSize['sfsigheight'] > 0) $sigHeight = sp_text('height').' - '.$spSigImageSize['sfsigheight'];
$out.= '<p class="spCenter">'.sp_text('Signature Image Size Limits (pixels)').': '.$sigWidth.$sigHeight.'</p>';
$out.= '<p class="spCenter">'.sp_text('If you reset your signature, be sure to save it').'</p>';

$out.= '<div class="spProfileFormSubmit">';
# reset signature - plugins need to filter this input and provide their own with onclick to their js
$tout = '<input type="button" class="spSubmit" name="reset" value="'.sp_text('Reset Signature').'" onclick="spjClearIt(\'postitem\')" />';
$out.= apply_filters('sph_ProfileSignatureReset', $tout);
$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Signature').'" />';
$out.= '</div>';
$out.= '</div>';

$out = apply_filters('sph_SignaturesFormBottom', $out, $userid);
$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);
$out.= '</div>';
$out.= '</form>';

$out.= '<div class="spColumnSection spCenter">';
$out.= '<p class="spTextLeft"><br />'.sp_text('Preview of Your Signature (update to see changes)').':</p><br />';
$out.= '<div id="spProfileSignaturePreview">';
$out.= sp_Signature('echo=0', $spProfileUser->signature);
$out.= '</div>';
$out.= '</div>';

$out.= '</div>'."\n";

$out = apply_filters('sph_ProfileSignatureForm', $out, $userid);
echo $out;

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		spjEdOpenEditor('postitem');
        setTimeout(function() {
            spjSetProfileDataHeight();
        }, 750);
	});
</script>
<?php

?>