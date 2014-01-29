<?php
/*
Simple:Press
Admin Forums Edit Forum Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit form information form.  It is hidden until the edit forum link is clicked
function spa_forums_edit_forum_form($forum_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfforumedit<?php echo $forum_id; ?>', 'sfreloadfb');
});
</script>
<?php

	global $spPaths;

	$forum = spdb_table(SFFORUMS, "forum_id=$forum_id", 'row');

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=editforum';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_forumedit');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit Forum'), 'true', 'edit-forum', false);

				if ($forum->parent ? $subforum=true : $subforum=false);
?>
					<input type="hidden" name="forum_id" value="<?php echo $forum->forum_id; ?>" />
					<input type="hidden" name="cgroup_id" value="<?php echo $forum->group_id; ?>" />
					<input type="hidden" name="cforum_name" value="<?php echo sp_filter_title_display($forum->forum_name); ?>" />
					<input type="hidden" name="cforum_slug" value="<?php echo esc_attr($forum->forum_slug); ?>" />
					<input type="hidden" name="cforum_seq" value="<?php echo $forum->forum_seq; ?>" />
					<input type="hidden" name="cforum_desc" value="<?php echo sp_filter_text_edit($forum->forum_desc); ?>" />
					<input type="hidden" name="cforum_status" value="<?php echo $forum->forum_status; ?>" />
					<input type="hidden" name="cforum_rss_private" value="<?php echo $forum->forum_rss_private; ?>" />
					<input type="hidden" name="cforum_icon" value="<?php echo esc_attr($forum->forum_icon); ?>" />
					<input type="hidden" name="cforum_rss" value="<?php echo $forum->forum_rss; ?>" />
					<input type="hidden" name="cforum_message" value="<?php echo sp_filter_text_edit($forum->forum_message); ?>" />

					<table class="form-table">
						<tr>
							<td width="35%" class="sflabel">
								<p><?php spa_etext('Type of forum'); ?>:<br /><br /></p>

<?php							if($subforum ? $checked='' : $checked='checked="checked"'); ?>
								<label for="sfradio1" class="sflabel radio">&nbsp;&nbsp;&nbsp;<?php spa_etext('Standard forum'); ?></label>
								<input type="radio" name="forumtype" id="sfradio1" value="1" <?php echo $checked; ?> onchange="spjSetForumOptions('forum');" />

<?php							if($subforum ? $checked='checked="checked"' : $checked=''); ?>
								<label for="sfradio2" class="sflabel radio">&nbsp;&nbsp;&nbsp;<?php spa_etext('Sub or child forum'); ?></label>
								<input type="radio" name="forumtype" id="sfradio2" value="2" <?php echo $checked; ?> onchange="spjSetForumOptions('subforum');" />

							</td>
<?php
                            $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums&amp;sfnonce='.wp_create_nonce('forum-ahah');
							$target = "fseq";
?>
							<td class="sflabel">

<?php							if ($subforum ? $style=' style="display:none"' : $style=' style="display:block"'); ?>
								<div id="groupselect"<?php echo $style; ?>>
									<?php spa_etext('The group this forum belongs to') ?>:<br /><br />
									<select style="width:190px" class="sfacontrol" name="group_id" onchange="spjSetForumSequence('edit', 'forum', this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>');">
										<?php echo spa_create_group_select($forum->group_id); ?>
									</select>
								</div>

<?php							if ($subforum ? $style=' style="display:block"' : $style=' style="display:none"'); ?>
								<div id="forumselect"<?php echo $style; ?>>
									<?php spa_etext('Parent forum this subforum belongs to') ?>:<br /><br />
									<select style="width:190px" class="sfacontrol" name="forum_parent" onchange="spjSetForumSequence('edit', 'subforum', this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>');">
										<?php echo spa_create_group_forum_select($forum->group_id, $forum->forum_id, $forum->parent); ?>
									</select>
								</div>

							</td>
						</tr>
					</table>
					<br />

					<?php
					$target='thisforumslug';
					$ahahURL = SFHOMEURL.'index.php?sp_ahah=forums&amp;sfnonce='.wp_create_nonce('forum-ahah');
					?>

					<table class="form-table">
						<tr>
							<td class="sflabel"><?php if($subforum ? spa_etext('Subforum name') : spa_etext('Forum name')) ?>:</td>
							<td><input type="text" class=" sfpostcontrol" size="45" name="forum_name" value="<?php echo sp_filter_title_display($forum->forum_name); ?>" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Forum Slug') ?>:</td>
							<td><input type="text" class=" sfpostcontrol" size="45" id="thisforumslug" name="thisforumslug" value="<?php echo esc_attr($forum->forum_slug); ?>" onchange="spjSetForumSlug(this, '<?php echo $ahahURL; ?>', '<?php echo $target; ?>', 'edit');" /></td>
						</tr><tr>
							<td class="sflabel"><?php spa_etext('Description') ?>:&nbsp;&nbsp;</td>
							<td><input type="text" class=" sfpostcontrol" size="85" name="forum_desc" value="<?php echo sp_filter_text_edit($forum->forum_desc); ?>" /></td>
						</tr>

						<tr id="fsequence">
							<td class="sflabel"><?php spa_etext('Display position') ?>:</td>
							<td id='fseq'>

<?php						if($subforum)
							{
								echo spa_edit_forum_sequence_options('edit', 'subforum', $forum->forum_id, $forum->forum_seq);
							} else {
								echo spa_edit_forum_sequence_options('edit', 'forum', $forum->group_id, $forum->forum_seq);
							}
?>

							</td>
						</tr>
					</table><br />

					<table class="form-table">
						<tr>
							<td class="sflabel"><label for="sfforum_status_<?php echo $forum->forum_id; ?>"><?php spa_etext('Locked') ?></label>
							<input type="checkbox" id="sfforum_status_<?php echo $forum->forum_id; ?>" name="forum_status"
							<?php if ($forum->forum_status == TRUE) {?> checked="checked" <?php } ?> /></td>
						</tr><tr>
							<td class="sflabel"><label for="sfforum_private_<?php echo $forum->forum_id; ?>"><?php spa_etext('Disable forum RSS feed so feed will not be generated') ?></label>
							<input type="checkbox" id="sfforum_private_<?php echo $forum->forum_id; ?>" name="forum_private"
								<?php if ($forum->forum_rss_private == TRUE) {?> checked="checked" <?php } ?> /></td>
						</tr>
					<table><br />

					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext('Custom forum icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('forum_icon', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->forum_icon); ?>
							</td>
						<tr>
							<td class="sflabel"><?php spa_etext('Custom forum icon when new posts') ?></td>
							<td>
								<?php spa_select_icon_dropdown('forum_icon_new', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->forum_icon_new); ?>
							</td>
						</tr>
						<tr>
							<td class="sflabel"><?php spa_etext('Custom topic icon') ?></td>
							<td>
								<?php spa_select_icon_dropdown('topic_icon', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon); ?>
							</td>
						<tr>
							<td class="sflabel"><?php spa_etext('Custom topic icon when new posts') ?></td>
							<td>
								<?php spa_select_icon_dropdown('topic_icon_new', spa_text('Select icon'), SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/', $forum->topic_icon_new); ?>
							</td>
						</tr>
					<table><br />

					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext('Replacement external RSS URL') ?>:<br /><?php spa_etext('Default'); ?>: <strong><?php echo sp_build_url($forum->forum_slug, '', 0, 0, 0, 1); ?></strong></td>
							<td><input class="sfpostcontrol" type="text" name="forum_rss" size="45" value="<?php echo sp_filter_url_display($forum->forum_rss); ?>" /></td>
						</tr>
                        <tr>
							<td class="sflabel"><?php spa_etext('Custom Meta Keywords') ?></td>
							<td><input type="text" class=" sfpostcontrol" size="85" name="forum_keywords" value="<?php echo sp_filter_title_display($forum->keywords); ?>" /></td>
						</tr>
                        <tr>
							<td class="sflabel"><?php spa_etext('Special forum message to be displayed above topics') ?>:</td>
							<td><textarea class="sfpostcontrol" cols="65" rows="3" name="forum_message"><?php echo sp_filter_text_edit($forum->forum_message); ?></textarea></td>
						</tr>
<?php
						do_action('sph_forum_edit_forum_options', $forum);
?>
					</table>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfforumedit<?php echo $forum->forum_id; ?>" name="sfforumedit<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Update Forum'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#forum-<?php echo $forum->forum_id; ?>').html('');" id="sfforumedit<?php echo $forum->forum_id; ?>" name="editforumcancel<?php echo $forum->forum_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>