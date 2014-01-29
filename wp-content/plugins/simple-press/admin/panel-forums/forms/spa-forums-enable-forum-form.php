<?php
/*
Simple:Press
Admin Forums Enable Forum Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the enable forum form.  It is hidden until the enable forum link is clicked
function spa_forums_enable_forum_form($forum_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfforumenable<?php echo $forum_id; ?>', 'sfreloadfb');
});
</script>
<?php
	spa_paint_options_init();
    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=enableforum';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfforumenable<?php echo $forum_id; ?>" name="sfforumenable<?php echo $forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumenable');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Enable Forum'), 'true', 'enable-forum', false);
?>
					<input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>" />
<?php
					echo '<p><b>';
					spa_etext('Warning! You are about to enable this forum');
					echo '</b></p>';
					echo '<p>';
					spa_etext('This will restore the forum to the front end with permissions/memberships controlling access');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the enable forum button below to proceed');
					echo '</p>';
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_forums_enable_forum_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfforumenable<?php echo $forum_id; ?>" name="sfforumenable<?php echo $forum_id; ?>" value="<?php spa_etext('Enable Forum'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#forum-<?php echo $forum_id; ?>').html('');" id="sfforumenable<?php echo $forum_id; ?>" name="enableforumcancel<?php echo $forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>