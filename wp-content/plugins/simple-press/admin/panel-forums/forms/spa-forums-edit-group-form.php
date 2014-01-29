<?php
/*
Simple:Press
Admin Forums Edit Group Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit group information form.  It is hidden until the edit group link is clicked
function spa_forums_edit_group_form($group_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfgroupedit<?php echo $group_id; ?>', 'sfreloadfb');
});
</script>
<?php

	global $spPaths;

	$group = $group = spdb_table(SFGROUPS, "group_id=$group_id", "row");

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=editgroup';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfgroupedit<?php echo $group->group_id; ?>" name="sfgroupedit<?php echo $group->group_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_groupedit');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit Group'), 'true', 'edit-forum-group', false);
?>
					<input type="hidden" name="group_id" value="<?php echo $group->group_id; ?>" />
					<input type="hidden" name="cgroup_name" value="<?php echo sp_filter_title_display($group->group_name); ?>" />
					<input type="hidden" name="cgroup_desc" value="<?php echo sp_filter_text_edit($group->group_desc); ?>" />
					<input type="hidden" name="cgroup_seq" value="<?php echo $group->group_seq; ?>" />
					<input type="hidden" name="cgroup_icon" value="<?php echo esc_attr($group->group_icon); ?>" />
					<input type="hidden" name="cgroup_rss" value="<?php echo $group->group_rss; ?>" />
					<input type="hidden" name="cgroup_message" value="<?php echo sp_filter_text_edit($group->group_message); ?>" />
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext('Group name') ?>:</td>
							<td><input type="text" class=" sfpostcontrol" size="45" name="group_name" value="<?php echo sp_filter_title_display($group->group_name); ?>" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Description') ?>:&nbsp;</td>
							<td><input type="text" class=" sfpostcontrol" size="85" name="group_desc" value="<?php echo sp_filter_text_edit($group->group_desc); ?>" /></td>
						</tr><tr>
<?php
							echo spa_group_sequence_options('edit', $group->group_seq);
?>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('group_icon', spa_text('Select Icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $group->group_icon); ?>
								</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Replacement external RSS URL') ?>:<br /><?php spa_etext('Default'); ?>: <strong><?php echo sp_get_sfqurl(sp_build_url('', '', 0, 0, 0, 1)).'group='.$group->group_id; ?></strong></td>
							<td><input class="sfpostcontrol" type="text" name="group_rss" size="45" value="<?php echo sp_filter_url_display($group->group_rss); ?>" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Special group message to be displayed above forums') ?>:</td>
							<td><textarea class="sfpostcontrol" cols="65" rows="3" name="group_message"><?php echo sp_filter_text_edit($group->group_message); ?></textarea></td>
						</tr>
						<?php do_action('sph_forums_edit_group_panel'); ?>
					</table>
					<br /><br />
					<?php spa_etext('Set default usergroup permission sets for this group') ?>
					<br /><br />
					<?php spa_etext('Note - This will not will add or modify any current permissions. It is only a default setting for future forums created in this group.  Existing default usergroup settings will be shown in the drop down menus') ?>
						<table class="form-table">
<?php
							$usergroups = spa_get_usergroups_all();
							foreach ($usergroups as $usergroup) {
?>
							<tr>
								<td class="sflabel"><?php echo sp_filter_title_display($usergroup->usergroup_name); ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="hidden" name="usergroup_id[]" value="<?php echo $usergroup->usergroup_id; ?>" /></td>
								<?php $roles = sp_get_all_roles(); ?>
								<td class="sflabel"><select style="width:165px" class='sfacontrol' name='role[]'>
<?php
									$defrole = spa_get_defpermissions_role($group->group_id, $usergroup->usergroup_id);
									$out = '';
									if ($defrole == -1 || $defrole == '') {
										$out = '<option value="-1">'.spa_text('Select permission set').'</option>';
									}
									foreach($roles as $role)
									{
										$selected = '';
										if ($defrole == $role->role_id)
										{
											$selected = 'selected="selected" ';
										}
										$out.='<option '.$selected.'value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
									}
									echo $out;
?>
									</select>
								</td>
							</tr>
							<?php } ?>
						</table>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="groupedit<?php echo $group->group_id; ?>" name="groupedit<?php echo $group->group_id; ?>" value="<?php spa_etext('Update Group'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#group-<?php echo $group->group_id; ?>').html('');" id="sfgroupedit<?php echo $group->group_id; ?>" name="groupeditcancel<?php echo $group->group_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>