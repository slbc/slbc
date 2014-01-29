<?php
/*
Simple:Press
Admin User Groups Main Display
$LastChangedDate: 2013-05-04 06:16:36 -0700 (Sat, 04 May 2013) $
$Rev: 10259 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_usergroup_main()
{
	$usergroups = spa_get_usergroups_all(Null);
	if($usergroups)
	{
		foreach ($usergroups as $usergroup)
		{
			# display the current usergroup information in table format
?>
			<table class="sfmaintable" cellpadding="0" cellspacing="0">
				<tr>
					<th align="center" width="7%" scope="col"><?php spa_etext("User Group ID") ?></th>
					<th scope="col"><?php spa_etext("User Group Name") ?></th>
					<th align="center" width="8%" scope="col"><?php spa_etext("Moderator") ?></th>
					<th align="center" width="15%" scope="col"></th>
					<th align="center" width="15%" scope="col"></th>
				</tr>
				<tr>
					<td align="center" style="padding:10px 0;"><?php echo $usergroup->usergroup_id; ?></td>
					<td><strong><?php echo sp_filter_title_display($usergroup->usergroup_name); ?></strong><br /><small><?php echo sp_filter_title_display($usergroup->usergroup_desc); ?></small></td>
					<td align="center" style="padding:10px 0;"><?php if ($usergroup->usergroup_is_moderator == 1) echo spa_etext("Yes"); else echo spa_etext("No"); ?></td>
					<td align="right" style="padding:10px 0;">
<?php
                        $base = SFHOMEURL."index.php?sp_ahah=usergroups-loader&amp;sfnonce=".wp_create_nonce('forum-ahah');
						$target = "usergroup-".$usergroup->usergroup_id;
						$image = SFADMINIMAGES;
?>
						<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Edit User Group'),0); ?>" onclick="spjLoadForm('editusergroup', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $usergroup->usergroup_id; ?>');" />
					</td>
					<td style="padding:10px 0;">
						<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Delete User Group'),0); ?>" onclick="spjLoadForm('delusergroup', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $usergroup->usergroup_id; ?>');" />
					</td>
				</tr>
				<tr class="sfinline-form"> <!-- This row will hold ahah forms for the current user group -->
				  	<td colspan="5">
						<div id="usergroup-<?php echo $usergroup->usergroup_id; ?>">
						</div>
					</td>
				</tr>
				<tr class="sfsubtable sfugrouptable">
					<td align="center" style="padding:10px 0;"></td>
					<td colspan="2" style="padding:10px 0;">
<?php
                        $site = SFHOMEURL."index.php?sp_ahah=usergroups&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;ug=".$usergroup->usergroup_id;
						$gif= SFCOMMONIMAGES."working.gif";
						$text = esc_js(spa_text('Show/Hide Members'));
						?>
						<input type="button" id="show<?php echo $usergroup->usergroup_id; ?>" class="button button-highlighted" value="<?php echo $text; ?>" onclick="spjShowMemberList('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $usergroup->usergroup_id; ?>');" />
					</td>
					<td colspan="2" align="center" style="padding:10px 0;">
<?php
                        $base = SFHOMEURL."index.php?sp_ahah=usergroups-loader&amp;sfnonce=".wp_create_nonce('forum-ahah');
						$target = "members-".$usergroup->usergroup_id;
						$image = SFADMINIMAGES;
?>
						<input type="button" id="add<?php echo $usergroup->usergroup_id; ?>" class="button button-highlighted" value="<?php spa_etext('Add Members'); ?>" onclick="spjLoadForm('addmembers', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $usergroup->usergroup_id; ?>'); " />
						<input type="button" id="remove<?php echo $usergroup->usergroup_id; ?>" class="button button-highlighted" value="<?php spa_etext('Move/Delete Members'); ?>" onclick="spjLoadForm('delmembers', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $usergroup->usergroup_id; ?>'); " />
					</td>
				</tr>
				<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
				  	<td colspan="5">
                        <div id="members-<?php echo $usergroup->usergroup_id; ?>"></div>
					</td>
				</tr>
			</table>
<?php
     	}
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no User Groups defined').'</div>';
	}
?>
    <div class="sfform-panel-spacer"></div>
	<table class="sfmaintable" cellpadding="0" cellspacing="0">
		<tr>
			<th style="text-align:left;padding:10px 20px;" scope="col"><?php spa_etext('Members Not Belonging To Any Usergroup') ?></th>
		</tr>
		<tr class="sfsubtable sfugrouptable">
			<td colspan="2" style="padding:10px 20px;">
<?php
                $site = SFHOMEURL."index.php?sp_ahah=usergroups&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;ug=0";
				$gif= SFCOMMONIMAGES."working.gif";
				$text = esc_js(spa_text('Show/Hide Members with No Memberships'));
				?>
				<input type="button" id="show-0" class="button button-highlighted" value="<?php echo $text; ?>" onclick="spjShowMemberList('<?php echo $site; ?>', '<?php echo $gif; ?>', '0');" />
			</td>
		</tr>
		<tr class="sfinline-form"> <!-- This row will hold hidden forms for the current user group membership-->
		  	<td>
                <div id="members-0"></div>
			</td>
		</tr>
	</table>
<?php
}
?>