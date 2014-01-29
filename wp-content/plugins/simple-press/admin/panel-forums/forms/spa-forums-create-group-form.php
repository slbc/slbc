<?php
/*
Simple:Press
Admin Forums Create Group Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create new group form. It is hidden until user clicks on the create new group link
function spa_forums_create_group_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfgroupnew', 'sfreloadfb');
});
</script>
<?php
	global $spPaths;

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=creategroup';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfgroupnew" name="sfgroupnew">
<?php
		echo sp_create_nonce('forum-adminform_groupnew');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Create New Group'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Create New Group'), 'true', 'create-new-forum-group', false);
?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext('Group name') ?>:</td>
							<td><input type="text" class="sfpostcontrol" size="45" name="group_name" value="" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Description') ?>:&nbsp;</td>
							<td><input type="text" class="sfpostcontrol" size="85" name="group_desc" value="" /></td>
						</tr><tr>
<?php
							echo spa_group_sequence_options('new', 0);
?>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('group_icon', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', ''); ?>
							</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Special group message to be displayed above forums') ?>:</td>
							<td><textarea class="sfpostcontrol" cols="65" rows="3" name="group_message"></textarea></td>
						</tr>

						<?php do_action('sph_forums_create_group_panel'); ?>
					</table>
					<div class="clearboth"></div>
					<br /><br />
					<strong><?php spa_etext('Set default usergroup permission sets') ?></strong>
					<br /><br />
					<?php spa_etext('Note - this will not change or define any current permissions. It\'s only a default setting for forums that get created in this Group. You will have the chance to explicitly set each permission when creating a forum in this group') ?>
					<table class="form-table">
						<?php
						$usergroups = spa_get_usergroups_all();
						foreach ($usergroups as $usergroup) {
						?>
						<tr>
							<td width="50%" class="sflabel">
                                <?php echo sp_filter_title_display($usergroup->usergroup_name); ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="hidden" name="usergroup_id[]" value="<?php echo $usergroup->usergroup_id; ?>" />
                            </td>
							<?php $roles = sp_get_all_roles(); ?>
							<td width="50%"><select style="width:165px" class='sfacontrol' name='role[]'>
<?php
								$out = '';
								$out = '<option value="-1">'.spa_text('Select permission set').'</option>';
								foreach($roles as $role)
								{
									$out.='<option value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
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
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New Group'); ?>" />
		</div>
		</form>

	<div class="sfform-panel-spacer"></div>
<?php
}

?>