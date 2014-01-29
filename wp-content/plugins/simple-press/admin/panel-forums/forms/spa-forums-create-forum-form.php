<?php
/*
Simple:Press
Admin Forums Create Forum Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the create new forum forum.  It is hidden until the create new forum link is clicked
function spa_forums_create_forum_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfforumnew', 'sfreloadfb');
});
</script>
<?php
	global $spPaths;

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=createforum';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfforumnew" name="sfforumnew">
<?php
		echo sp_create_nonce('forum-adminform_forumnew');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Create New Forum'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Create New Forum'), 'true', 'create-new-forum', false);

					# check there are groups before proceeding
					if (spdb_count(SFGROUPS) == 0) {
						echo '<br /><div class="sfoptionerror">';
						spa_etext('There are no groups defined');
						echo '<br />'.spa_text('Create new group');
						echo '</div><br />';
						spa_paint_close_fieldset(false);
						spa_paint_close_panel();
						spa_paint_close_tab();
						return;
					}
?>
					<table class="form-table">
						<tr>
							<td width="35%" class="sflabel">
								<p><?php spa_etext('What type of forum are you creating'); ?>:<br /><br /></p>
								<label for="sfradio1" class="sflabel radio">&nbsp;&nbsp;&nbsp;<?php spa_etext('Standard Forum'); ?></label>
								<input type="radio" name="forumtype" id="sfradio1" value="1" checked="checked" onchange="spjSetForumOptions('forum');" />

<?php							# check there are forums before offering subforum creation!
								if(spdb_count(SFFORUMS) != 0) {
?>
									<label for="sfradio2" class="sflabel radio">&nbsp;&nbsp;&nbsp;<?php spa_etext('Sub or child forum'); ?></label>
									<input type="radio" name="forumtype" id="sfradio2" value="2" onchange="spjSetForumOptions('subforum');" />
<?php							}
?>
							</td>
<?php
                            $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums&amp;sfnonce='.wp_create_nonce('forum-ahah');
							$target = 'fseq';
?>
							<td class="sflabel">
								<div id="groupselect" style="display:block;">
									<?php spa_etext('Select group new forum will belong to') ?>:<br /><br />
									<select style="width:190px" class="sfacontrol" name="group_id" onchange="spjSetForumSequence('new', 'forum', this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>');">
										<?php echo spa_create_group_select(); ?>
									</select>
								</div>
								<div id="forumselect" style="display:none;">
									<?php spa_etext('Select forum new subforum will belong to') ?>:<br /><br />
									<select style="width:190px" class="sfacontrol" name="forum_id" onchange="spjSetForumSequence('new', 'subforum', this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>');">
										<?php echo sp_render_group_forum_select(false, false, false, true); ?>
									</select>
								</div>
							</td>
						</tr>
					</table>
					<br />
					<table class="form-table sfhidden" id="block1">
						<tr>
							<?php
							$target='thisforumslug';
							$ahahURL = SFHOMEURL.'index.php?sp_ahah=forums&amp;sfnonce='.wp_create_nonce('forum-ahah');
							?>

							<td class="sflabel"><?php spa_etext('Forum name') ?>:</td>
							<td><input type="text" class="sfpostcontrol" size="45" name="forum_name" value="" onchange="spjSetForumSlug(this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>');" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Forum slug') ?>:&nbsp;&nbsp;</td>
							<td><input id="thisforumslug" type="text" class="sfpostcontrol" size="45" name="thisforumslug" value="" disabled="disabled" onchange="spjSetForumSlug(this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>', 'new');" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Description') ?>:&nbsp;&nbsp;</td>
							<td><input type="text" class="sfpostcontrol" size="85" name="forum_desc" value="" /></td>
						</tr>
						<tr id="fsequence">
							<td class="sflabel"><?php spa_etext('Display position') ?>:</td>
							<td id='fseq'></td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfforum_status"><?php spa_etext('Locked') ?></label></td>
							<td><input type="checkbox" id="sfforum_status" name="forum_status" /></td>
						</tr><tr>
							<td class="sflabel" colspan="2"><label for="sfforum_private"><?php spa_etext('Disable forum RSS feed so feed will not be generated') ?></label></td>
							<td><input type="checkbox" id="sfforum_private" name="forum_private" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom forum icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('forum_icon', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', ''); ?>
							</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom forum icon when new posts') ?></td>
							<td>
								<?php spa_select_icon_dropdown('forum_icon_new', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', ''); ?>
							</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom topic icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('topic_icon', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', ''); ?>
							</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom topic icon when new posts') ?></td>
							<td>
								<?php spa_select_icon_dropdown('topic_icon_new', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', ''); ?>
							</td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Custom meta keywords (SEO option must be enabled)') ?></td>
							<td><input id="forum_keywords" type="text" class="sfpostcontrol" size="45" name="forum_keywords" value="" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Special forum message to be displayed above topics') ?>:</td>
							<td><textarea class="sfpostcontrol" cols="65" rows="3" name="forum_message"></textarea></td>
						</tr>
<?php
						do_action('sph_forum_create_forum_options');
?>
					</table>
					<br /><br />
<?php
					$usergroups = spa_get_usergroups_all();
					if ($usergroups) {
?>
						<div id="block2" class="sfhidden">
						<?php spa_etext('Add usergroup permission sets') ?>
						<br /><br />
						<?php spa_etext('You can selectively set the permission sets for the forum below. If you want to use the default permissions for the selected group, then do not select anything') ?>
						<table class="form-table">
						<?php foreach ($usergroups as $usergroup) { ?>
							<tr>
								<td class="sflabel"><?php echo sp_filter_title_display($usergroup->usergroup_name); ?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="hidden" name="usergroup_id[]" value="<?php echo $usergroup->usergroup_id; ?>" /></td>
								<?php $roles = sp_get_all_roles(); ?>
								<td class="sflabel"><select style="width:165px" class='sfacontrol' name='role[]'>
<?php
									$out = '';
									$out = '<option value="-1">'.spa_text('Select permission set').'</option>';
									foreach($roles as $role) {
										$out.='<option value="'.$role->role_id.'">'.sp_filter_title_display($role->role_name).'</option>'."\n";
									}
									echo $out;
?>
									</select>
								</td>
							</tr>
						<?php } ?>
						</table><br />
					<?php } ?>
					</div>
					<div class="clearboth"></div>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Create New Forum'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>