<?php
/*
Simple:Press
Profile Photos Form
$LastChangedDate: 2013-03-10 04:51:23 -0700 (Sun, 10 Mar 2013) $
$Rev: 10040 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# double check we have a user
if (empty($userid)) return;

$ahahURL = SFHOMEURL.'index.php?sp_ahah=profile&sfnonce='.wp_create_nonce('forum-ahah')."&action=update-photos&user=$userid";
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	/* ajax form and message */
	jQuery('#spProfileFormPhotos').ajaxForm({
        dataType: 'json',
		success: function(response) {
            jQuery('#spProfilePhotos').load('<?php echo $ahahURL; ?>');
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
$out.= sp_text('On this panel, you may reference some personal photos or images that can be displayed in your profile.');
$out.= sprintf(spa_text('There is a limit of %d photos that you can store in your profile.'), $spProfileOptions['photosmax']);
$out.= '</p>';
$out.= '<hr>';

$out.= '<div class="spProfilePhotos">';

if ($spProfileOptions['photosmax'] < 1) {
	$out.= '<p class="spProfileLabel">'.sp_text('Profile photos are not enabled on this forum').'</p>';
} else {
    $ahahURL = SFHOMEURL.'index.php?sp_ahah=profile-save&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;form=$thisSlug&amp;userid=$userid";
	$out.= '<form action="'.$ahahURL.'" method="post" name="spProfileFormPhotos" id="spProfileFormPhotos" class="spProfileForm">';
	$out.= sp_create_nonce('forum-profile');

	$out = apply_filters('sph_ProfileFormTop', $out, $userid, $thisSlug);
	$out = apply_filters('sph_ProfilePhotosFormTop', $out, $userid);

    $out.= '<div id="spProfilePhotos">';
    $tout = '';
	for ($x=0; $x < $spProfileOptions['photosmax']; $x++) {
    	$tout.= '<div class="spColumnSection spProfileLeftCol">';
		$tout.= '<p class="spProfileLabel">'.sp_text('Url to Photo').' '.($x+1).'</p>';
    	$tout.= '</div>';
    	$tout.= '<div class="spColumnSection spProfileSpacerCol"></div>';
        $photo = (!empty($spProfileUser->photos[$x])) ? $spProfileUser->photos[$x] : '';
    	$tout.= '<div class="spColumnSection spProfileRightCol">';
		$tout.= "<p class='spProfileLabel'><input class='spControl' type='text' name='photo$x' value='$photo' /></p>";
    	$tout.= '</div>';
	}
    $out.= apply_filters('sph_ProfilePhotosLoop', $tout, $userid);
	$out.= '</div>';

	$out = apply_filters('sph_ProfilePhotosFormBottom', $out, $userid);
	$out = apply_filters('sph_ProfileFormBottom', $out, $userid, $thisSlug);

	$out.= '<div class="spProfileFormSubmit">';
	$out.= '<input type="submit" class="spSubmit" name="formsubmit" value="'.sp_text('Update Photos').'" />';
	$out.= '</div>';
	$out.= '</form>';
}
$out.= '</div>'."\n";

$out = apply_filters('sph_ProfilePhotosForm', $out, $userid);
echo $out;
?>