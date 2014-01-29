<?php
/*
Simple:Press
Admin Forums Disable Forum Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the disable forum form.  It is hidden until the disable forum link is clicked
function spa_forums_disable_forum_form($forum_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfforumdisable<?php echo $forum_id; ?>', 'sfreloadfb');
});
</script>
<?php
	spa_paint_options_init();
    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=disableforum';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfforumdisable<?php echo $forum_id; ?>" name="sfforumdisable<?php echo $forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumdisable');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Disable Forum'), 'true', 'disable-forum', false);
?>
					<input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
<?php
					echo '<p><b>';
					spa_etext('Warning! You are about to disable this forum');
					echo '</b></p>';
					echo '<p>';
					spa_etext('This will completely hide the forum from ALL users including admins on the front end.');
					echo '</p>';
                    echo '<p>';
					spa_etext('This will not delete the forum topic or posts. It only hides the forum. You can reenable at any time.');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the disable forum button below to proceed.');
					echo '</p>';
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_forums_disable_forum_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfforumdisable<?php echo $forum_id; ?>" name="sfforumdisable<?php echo $forum_id; ?>" value="<?php spa_etext('Disable Forum'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#forum-<?php echo $forum_id; ?>').html('');" id="sfforumdisable<?php echo $forum_id; ?>" name="disableforumcancel<?php echo $forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>