<?php
/*
Simple:Press
Admin Forums Main Display
$LastChangedDate: 2013-05-04 06:16:36 -0700 (Sat, 04 May 2013) $
$Rev: 10259 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_forums_forums_main() {
	$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
	if ($groups) {
		foreach ($groups as $group) {
			# display the current group information in table format
?>
			<table class="sfmaintable" cellpadding="0" cellspacing="0">
				<tr> <!-- display group table header information -->
					<th align="center" width="40"><?php spa_etext('Icon'); ?></th>
					<th align="center" width="31"><?php spa_etext('ID'); ?></th>
					<th scope="col"><?php spa_etext('Group Name') ?></th>
					<th align="center" width="20" scope="col"></th>
					<th align="center" width="30%" scope="col"></th>
				</tr>
				<tr> <!-- display group information for each group -->
<?php
					if (empty($group->group_icon)) {
						$icon = SPTHEMEICONSURL.'sp_GroupIcon.png';
					} else {
						$icon = esc_url(SFCUSTOMURL.$group->group_icon);
						if (!file_exists(SFCUSTOMDIR.$group->group_icon)) {
							$icon = SPTHEMEICONSURL.'sp_GroupIcon.png';
						}
					}
?>
					<td align="center">
<?php
						echo '<img src="'.$icon.'" alt="" title="'.spa_text('Current group icon').'" />';
?>
					</td>
					<td align="center"><?php echo $group->group_id; ?></td>
					<td><p><strong><?php echo sp_filter_title_display($group->group_name); ?></strong><br /><?php echo sp_filter_text_display($group->group_desc); ?></p></td>
					<td></td>
					<td align="center">
<?php
                        $base = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah');
						$target = "group-$group->group_id";
						$image = SFADMINIMAGES;
?>
						<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Add Group Permission'),1); ?>" onclick="spjLoadForm('groupperm', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $group->group_id; ?>');" />
						<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Edit Group'),0); ?>" onclick="spjLoadForm('editgroup', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $group->group_id; ?>');" />
						<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Delete Group'),0); ?>" onclick="spjLoadForm('deletegroup', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $group->group_id; ?>');" />
					</td>
				</tr>
				<tr class="sfinline-form">  <!-- This row will hold ahah forms for the current group -->
				  	<td colspan="10">
						<div id="group-<?php echo $group->group_id; ?>">
						</div>
					</td>
				</tr>
			</table>
<?php
			$forums = spa_get_forums_in_group($group->group_id);
			if ($forums) {
				# display the current forum information for each forum in table format
?>
				<table  class="sfsubtable" cellpadding="0" cellspacing="0">
					<tr> <!-- display forum table header information -->
						<th align="center" width="40"></th>
						<th align="center" width="31"><?php spa_etext('ID'); ?></th>
						<th scope="col"><?php spa_etext('Forum Name') ?></th>
						<th align="center" width="20" scope="col"></th>
						<th align="center" width="30%" scope="col"></th>
					</tr>
<?php
					spa_paint_group_forums($group->group_id, 0, '', 0);
?>
				</table>
				<br /><br />
<?php
			} else {
				echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no forums defined in this group').'</div>';
			}
		}
	} else {
		echo '<div class="sfempty">&nbsp;&nbsp;&nbsp;&nbsp;'.spa_text('There are no groups defined').'</div>';
	}
}

function spa_paint_group_forums($groupid, $parent, $parentname, $level) {
	$space = '<img class="sfalignleft" src="'.SFADMINIMAGES.'sp_SubforumLevel.png" alt="" />';
	$forums = spa_get_group_forums_by_parent($groupid, $parent);
	$noMembers = array();

	if($forums) {

		$noMembers = spa_forums_check_memberships($forums);

		foreach ($forums as $forum) {
			$locked = '';
			if ($forum->forum_status) $locked = spa_text('Locked');
			$subforum = $forum->parent;

			$haschild = '';
			if ($forum->children) {
				$childlist = array(unserialize($forum->children));
				if (count($childlist) > 0) $haschild = $childlist;
			}

			$rowClass = (in_array($forum->forum_id, $noMembers)) ? ' class="spNoMembers"' : '';
	?>
			<tr <?php echo($rowClass); ?>> <!-- display forum information for each forum -->
	<?php
			if (empty($forum->forum_icon)) {
				$icon = SPTHEMEICONSURL.'sp_ForumIcon.png';
			} else {
				$icon = esc_url(SFCUSTOMURL.$forum->forum_icon);
				if (!file_exists(SFCUSTOMDIR.$forum->forum_icon)) {
					$icon = SPTHEMEICONSURL.'sp_ForumIcon.png';
				}
			}
	?>
			<td align="center">
	<?php
			echo '<img src="'.$icon.'" alt="" title="'.spa_text('Current forum icon').'" />';
	?>
			</td>
			<td align="center"><?php echo $forum->forum_id; ?></td>

	<?php
			if ($subforum) { ?>
				<td>
                <?php if ($forum->forum_disabled) echo '<img class="sfalignleft" src="'.SFADMINIMAGES.'sp_No.png" alt="" title="'.spa_text('Subforum is disabled').'" /> '; ?>
				<?php echo str_repeat($space, ($level-1)); ?>
				<img class="sfalignleft" src="<?php echo SFADMINIMAGES.'sp_Subforum.png'; ?>" alt="" title="<?php spa_etext('Subforum'); ?>" />
				<p><strong><?php echo sp_filter_title_display($forum->forum_name).'</strong> ('.spa_text('Subforum of').': '.$parentname.')'; ?>
	<?php	} else { ?>
				<td>
                <?php if ($forum->forum_disabled) echo '<img class="sfalignleft" src="'.SFADMINIMAGES.'sp_No.png" alt="" title="'.spa_text('Forum is disabled').'" /> '; ?>
                <p><strong><?php echo sp_filter_title_display($forum->forum_name); ?></strong>
	<?php	} ?>
			<br />
			<p><?php echo sp_filter_text_display($forum->forum_desc); ?></p>
	<?php
			if(in_array($forum->forum_id, $noMembers)) {
				echo '<p><b>'.spa_text('Warning - There are no usergroups with members that have permission to use this forum').'</b></p>';
			}

			if($haschild) { ?>
				<img src="<?php echo SFADMINIMAGES.'sp_HasChild.png'; ?>" alt="" title="<?php spa_etext('Parent Forum'); ?>" />
	<?php	} ?>

			</td>
			<td align="center"><?php if ($forum->forum_status) echo '<img src="'.SFADMINIMAGES.'sp_Locked.png" alt="" />'; ?></td>

			<td align="center">
	<?php
            $base = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah');
			$target = "forum-$forum->forum_id";
			$image = SFADMINIMAGES;
	?>
			<input id="sfreloadpb<?php echo $forum->forum_id; ?>" type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('View Forum Permissions'),1); ?>" onclick="spjLoadForm('forumperm', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $forum->forum_id; ?>');" />
			<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Edit Forum'),0); ?>" onclick="spjLoadForm('editforum', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $forum->forum_id; ?>');" />
			<input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Delete Forum'),0); ?>" onclick="spjLoadForm('deleteforum', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $forum->forum_id; ?>');" />
            <?php if ($forum->forum_disabled) { ?>
                <input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Enable Forum'),0); ?>" onclick="spjLoadForm('enableforum', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $forum->forum_id; ?>');" />
            <?php } else { ?>
                <input type="button" class="spButton-tall" value="<?php echo sp_splice(spa_text('Disable Forum'),0); ?>" onclick="spjLoadForm('disableforum', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $forum->forum_id; ?>');" />
            <?php } ?>
			</td>
			</tr>
			<tr class="sfinline-form">  <!-- This row will hold ahah forms for the current forum -->
			<td colspan="10">
			<div id="forum-<?php echo $forum->forum_id; ?>">
			</div>
			</td>
			</tr>
	<?php
			if($haschild) {
				$newlevel = $level+1;
				spa_paint_group_forums($groupid, $forum->forum_id, $forum->forum_name, $newlevel);
			}
		}
	}
}

function spa_forums_check_memberships($forums) {

	$value = sp_get_sfmeta('default usergroup', 'sfguests');
	$ugid = spdb_table(SFUSERGROUPS, "usergroup_id={$value[0]['meta_value']}", 'usergroup_id');
	if(empty($ugid)) $ugid=0;
	$noMembers = array();
	foreach ($forums as $forum) {
		$has_members = false;
		$permissions = sp_get_forum_permissions($forum->forum_id);
		if ($permissions) {
			foreach ($permissions as $permission) {
				$members = spdb_table(SFMEMBERSHIPS, "usergroup_id= $permission->usergroup_id", 'row', '', '1');
				if ($members || $permission->usergroup_id == $ugid) {
					$has_members = true;
					break;
				}
			}
		}
		if (!$has_members) $noMembers[] = $forum->forum_id;
	}
	return $noMembers;
}

?>