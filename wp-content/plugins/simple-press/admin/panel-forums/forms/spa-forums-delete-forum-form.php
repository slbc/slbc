<?php
/*
Simple:Press
Admin Forums Delete Forum Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete forum form.  It is hidden until the delete forum link is clicked
function spa_forums_delete_forum_form($forum_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfforumdelete<?php echo $forum_id; ?>', 'sfreloadfb');
});
</script>
<?php
	$forum = spdb_table(SFFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=deleteforum';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="sfforumdelete<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumdelete');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Delete Forum'), 'true', 'delete-forum', false);
?>
					<input type="hidden" name="group_id" value="<?php echo $forum->group_id; ?>" />
					<input type="hidden" name="forum_id" value="<?php echo $forum->forum_id; ?>" />
					<input type="hidden" name="cforum_seq" value="<?php echo $forum->forum_seq; ?>" />
					<input type="hidden" name="parent" value="<?php echo $forum->parent; ?>" />
					<input type="hidden" name="children" value="<?php echo $forum->children; ?>" />
<?php
					echo '<p>';
					spa_etext('Warning! You are about to delete a forum');
					echo '</p>';
					echo '<p>';
					spa_etext('This will remove ALL topics and posts contained in this forum');
					echo '</p>';
					echo '<p>';
					spa_etext('Any Subforums will be promoted');
					echo '</p>';
					echo '<p>';
					echo sprintf(spa_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the delete forum button below to proceed');
					echo '</p>';
					echo '<p><strong>';
					spa_etext('IMPORTANT: Be patient. For busy forums this action can take some time');
					echo '</strong></p>';
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_forums_delete_forum_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="sfforumdelete<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Delete Forum'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#forum-<?php echo $forum->forum_id; ?>').html('');" id="sfforumdelete<?php echo $forum->forum_id; ?>" name="delforumcancel<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>