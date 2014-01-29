<?php
/*
Simple:Press
Admin Forums Add Permission Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add new forum permission set form.  It is hidden until the add permission set link is clicked
function spa_forums_add_permission_form($forum_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfpermissionnew<?php echo $forum_id; ?>', 'sfreloadfb');
});
</script>
<?php
	$forum = spdb_table(SFFORUMS, "forum_id=$forum_id", 'row');

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=addperm';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfpermissionnew<?php echo $forum->forum_id; ?>" name="sfpermissionnew<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_permissionnew');
		spa_paint_open_tab(spa_text('Forums')." - ".spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Add Permission Set'), 'true', 'add-user-group-permission-set', false);
?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_usergroup_select(true, $forum->forum_id); ?></td>
						</tr><tr>
							<td class="sflabel"><?php spa_display_permission_select(); ?></td>
						</tr>
					</table>
					<input type="hidden" name="forum_id" value="<?php echo $forum->forum_id; ?>" />
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();

			do_action('sph_forums_add_perm_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="permnew<?php echo $forum->forum_id; ?>" name="permnew<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Add Permission Set'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#newperm-<?php echo $forum->forum_id; ?>').html('');" id="sfpermissionnew<?php echo $forum->forum_id; ?>" name="addpermcancel<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Cancel'	); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>