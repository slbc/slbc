<?php
/*
Simple:Press
Admin User Groups Add User Group Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create user group form.  It is hidden until the create user group link is clicked
function spa_usergroups_create_usergroup_form() {
    global $spPaths;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfusergroupnew', 'sfreloadub');
});
</script>
<?php

	spa_paint_options_init();

    $ahahURL = SFHOMEURL."index.php?sp_ahah=usergroups-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=newusergroup";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfusergroupnew" name="sfusergroupnew">
<?php
		echo sp_create_nonce('forum-adminform_usergroupnew');
		spa_paint_open_tab(spa_text('User Groups')." - ".spa_text('Create New User Group'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Create New User Group'), 'true', 'create-new-user-group', false);
?>
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext("User Group Name") ?>:</td>
							<td><input type="text" class="sfpostcontrol" size="45" name="usergroup_name" value="" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext("User Group Description") ?>:&nbsp;&nbsp;</td>
							<td><input type="text" class="sfpostcontrol" size="85" name="usergroup_desc" value="" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('User Group Badge') ?>:<br />(<?php spa_etext('Upload badges on the Components - Forum Ranks admin panel') ?>)</td>
							<td><?php spa_select_icon_dropdown('usergroup_badge', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', ''); ?></td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfusergroup_join" class="sflabel"><?php spa_etext("Allow Members to Join Usergroup") ?>&nbsp;
                            <?php spa_etext("(Indicates that Members are allowed to choose to join this Usergroup on their profile page)") ?></label>
							<input type="checkbox" name="usergroup_join" id="sfusergroup_join" value="0" />
							</td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfusergroup_is_moderator" class="sflabel"><?php spa_etext("Is Moderator") ?>&nbsp;
                            <?php spa_etext("(Indicates that members of this User Group are considered moderators)") ?></label>
							<input type="checkbox" name="usergroup_is_moderator" id="sfusergroup_is_moderator" value="1" />
							</td>
						</tr>
					</table>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_usergroup_create_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New User Group'); ?>" />
		</div>
		</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>