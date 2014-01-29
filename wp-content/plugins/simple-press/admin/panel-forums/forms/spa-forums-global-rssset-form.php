<?php
/*
Simple:Press
Admin Forums Global RSS Set Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add global permission set form. It is hidden until user clicks the add global permission set link
function spa_forums_global_rssset_form($id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfglobalrssset', 'sfreloadfd');
});
</script>
<?php

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=globalrssset';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfglobalrssset" name="sfglobalrssset">
<?php
		echo sp_create_nonce('forum-adminform_globalrssset');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Global RSS Settings'), true);
			spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Globally Enable/Disable RSS Feeds'), false);
				echo '<tr><td colspan="2"><br />';
				echo '<div class="sfoptionerror">';
				spa_etext('Warning: Enabling or disabling RSS feeds from this form will apply that setting to ALL forums and overwrite any existing RSS feed settings. If you wish to individually enable/disable RSS feeds for a single forum, please visit the manage forums admin panel - edit the forum and set the RSS feed status there');
				echo '<br /><br />';
				if ($id == 1) spa_etext('Please press the confirm button below to disable RSS feeds for all forums');
				if ($id == 0) spa_etext('Please press the confirm button below to enable RSS feeds for all forums');
				echo '</div><br />';
				echo '</td></tr>';
				echo '<input type="hidden" name="sfglobalrssset" value="'.$id.'" />';
			spa_paint_close_fieldset();
			spa_paint_close_panel();
			do_action('sph_forums_rss_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
			<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Confirm RSS Feed Status'); ?>" />
			<input type="button" class="button-primary" onclick="javascript:jQuery('#sfallrss').html('');" id="sfallrsscancel" name="sfallrsscancel" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>