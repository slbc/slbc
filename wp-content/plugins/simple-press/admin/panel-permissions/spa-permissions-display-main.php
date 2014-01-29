<?php
/*
Simple:Press
Admin Permissions Main Display
$LastChangedDate: 2013-05-04 06:16:36 -0700 (Sat, 04 May 2013) $
$Rev: 10259 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_permissions_permission_main()
{
	$roles = sp_get_all_roles();
	if ($roles)
	{
		# display the permission set roles in table format
?>
		<table class="sfsubtable" cellpadding="0" cellspacing="0">
			<tr>
				<th align="center" width="9%" scope="col"><?php spa_etext("Permission Set ID") ?></th>
				<th scope="col"><?php spa_etext("Permission Set Name") ?></th>
				<th align="center" width="5%" scope="col"></th>
				<th align="center" width="15%" scope="col"></th>
			</tr>
<?php
			foreach($roles as $role)
			{
?>
			<tr>
				<td align="center"><?php echo $role->role_id; ?></td>
				<td><strong><?php echo sp_filter_title_display($role->role_name); ?></strong><br /><small><?php echo sp_filter_title_display($role->role_desc); ?></small></td>
				<td align="center">
<?php
                    $base = SFHOMEURL."index.php?sp_ahah=permissions-loader&amp;sfnonce=".wp_create_nonce('forum-ahah');
					$target = "perm-".$role->role_id;
					$image = SFADMINIMAGES;
?>
					<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Edit Permission'),0); ?>" onclick="spjLoadForm('editperm', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $role->role_id; ?>');" />
				</td>
				<td align="center">
					<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Delete Permission'),0); ?>" onclick="spjLoadForm('delperm', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $role->role_id; ?>');" />
				</td>
			</tr>
			<tr class="sfinline-form"> <!-- This row will hold ahah forms for the current permission set -->
			  	<td colspan="5">
					<div id="perm-<?php echo $role->role_id; ?>">
					</div>
				</td>
			</tr>
<?php	} ?>
		</table>
		<br />
<?php
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no Permission Sets defined.').'</div>';
	}
}
?>