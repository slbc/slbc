<?php
/*
Simple:Press
Admin Forums Add Group Permission Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the add group permission set form.  It is hidden until the add group permission set link is clicked
function spa_forums_add_group_permission_form($group_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfgrouppermnew<?php echo $group_id; ?>', 'sfreloadfb');
});
</script>
<?php
	$group = $group = spdb_table(SFGROUPS, "group_id=$group_id", "row");

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=grouppermission';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfgrouppermnew<?php echo $group->group_id; ?>" name="sfgrouppermnew<?php echo $group->group_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_grouppermissionnew');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Add a User Group Permission Set to an Entire Group'), 'true', 'add-a-user-group-permission-set-to-an-entire-group', false);
?>
					<?php echo spa_text('Set a usergroup permission set for all forum in a group').': '.sp_filter_title_display($group->group_name); ?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_display_usergroup_select(); ?></td>
						</tr><tr>
							<td class="sflabel"><?php spa_display_permission_select(); ?></td>
						</tr><tr>
							<td class="sflabel">
    							<label for="sfadddef"><?php spa_etext('Add to group default permissions') ?></label>
    							<input type="checkbox" id="sfadddef" name="adddef" />
                            </td>
						</tr>
					</table>

					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
					<p><?php spa_etext('Caution:  Any current permission set for the selected usergroup for any forum in this group will be overwritten') ?></p>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();

			do_action('sph_forums_group_perm_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="groupperm<?php echo $group->group_id; ?>" name="groupperm<?php echo $group->group_id; ?>" value="<?php spa_etext('Add Group Permission'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#group-<?php echo $group->group_id; ?>').html('');" id="grouppermcancel<?php echo $group->group_id; ?>" name="grouppermcancel<?php echo $group->group_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>