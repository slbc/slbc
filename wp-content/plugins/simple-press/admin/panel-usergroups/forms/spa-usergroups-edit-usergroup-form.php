<?php
/*
Simple:Press
Admin User Groups Edit User Group Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit user group form.  It is hidden until the edit user group link is clicked
function spa_usergroups_edit_usergroup_form($usergroup_id) {
    global $spPaths;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfusergroupedit<?php echo $usergroup_id; ?>', 'sfreloadub');
});
</script>
<?php

	$usergroup = spa_get_usergroups_row($usergroup_id);

	spa_paint_options_init();

    $ahahURL = SFHOMEURL."index.php?sp_ahah=usergroups-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=editusergroup";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_usergroupedit');
		spa_paint_open_tab(spa_text('User Groups')." - ".spa_text('Manage User Groups'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit User Group'), 'true', 'edit-user-group', false);
?>
					<input type="hidden" name="usergroup_id" value="<?php echo $usergroup->usergroup_id; ?>" />
					<input type="hidden" name="ugroup_name" value="<?php echo sp_filter_title_display($usergroup->usergroup_name); ?>" />
					<input type="hidden" name="ugroup_desc" value="<?php echo sp_filter_title_display($usergroup->usergroup_desc); ?>" />
					<input type="hidden" name="ugroup_join" value="<?php echo $usergroup->usergroup_join; ?>" />
					<input type="hidden" name="ugroup_ismod" value="<?php echo $usergroup->usergroup_is_moderator; ?>" />
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext('User Group Name') ?>:</td>
							<td><input type="text" class="sfpostcontrol" size="45" name="usergroup_name" value="<?php echo sp_filter_title_display($usergroup->usergroup_name); ?>" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('User Group Description') ?>:</td>
							<td><input type="text" class="sfpostcontrol" size="85" name="usergroup_desc" value="<?php echo sp_filter_title_display($usergroup->usergroup_desc); ?>" /></td>
						</tr><tr>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('User Group Badge') ?>:<br />(<?php spa_etext('Upload badges on the Components - Forum Ranks admin panel') ?>)</td>
							<td><?php spa_select_icon_dropdown('usergroup_badge', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', $usergroup->usergroup_badge); ?></td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfusergroup_join_<?php echo $usergroup->usergroup_id; ?>"><?php spa_etext('Allow members to join usergroup') ?>&nbsp;
							<?php spa_etext('(Indicates that members are allowed to choose to join this usergroup on their profile page)') ?></label>
                            <input type="checkbox" name="usergroup_join" id="sfusergroup_join_<?php echo $usergroup->usergroup_id; ?>" value="1" <?php if ($usergroup->usergroup_join == 1) echo 'checked="checked"'; ?>/>
							</td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfusergroup_is_moderator_<?php echo $usergroup->usergroup_id; ?>"><?php spa_etext('Is moderator') ?>&nbsp;
                            <?php spa_etext('(Indicates that members of this usergroup are considered Moderators)') ?></label>
							<input type="checkbox" name="usergroup_is_moderator" id="sfusergroup_is_moderator_<?php echo $usergroup->usergroup_id; ?>" value="1" <?php if ($usergroup->usergroup_is_moderator == 1) echo 'checked="checked"'; ?>/>
							</td>
						</tr>
					</table>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_usergroup_edit_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" value="<?php spa_etext('Update User Group'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#usergroup-<?php echo $usergroup->usergroup_id; ?>').html('');" id="sfusergroupedit<?php echo $usergroup->usergroup_id; ?>" name="editusergroupcancel<?php echo $usergroup->usergroup_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>