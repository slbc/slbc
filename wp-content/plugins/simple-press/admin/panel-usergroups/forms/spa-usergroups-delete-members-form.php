<?php
/*
Simple:Press
Admin User Groups Delete Member Form
$LastChangedDate: 2013-09-19 13:12:36 -0700 (Thu, 19 Sep 2013) $
$Rev: 10709 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_usergroups_delete_members_form($usergroup_id)
{
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#sfmemberdel<?php echo $usergroup_id; ?>').ajaxForm({
		target: '#sfmsgspot',
	});
});
</script>
<?php
	spa_paint_options_init();

    $ahahURL = SFHOMEURL."index.php?sp_ahah=usergroups-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=delmembers";

	$url = SFHOMEURL.'index.php?sp_ahah=memberships&amp;action=del&amp;sfnonce='.wp_create_nonce('forum-ahah');
	$target = 'sfmsgspot';
	$smessage = esc_js(spa_text('Please Wait - Processing'));
	$emessage = esc_js(spa_text('Users Deleted/Moved'));

?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id ?>" onsubmit="spjAddDelMembers('sfmemberdel<?php echo $usergroup_id ?>', '<?php echo($url); ?>', '<?php echo($target); ?>', '<?php echo($smessage); ?>', '<?php echo($emessage); ?>', 0, 50, '#dmid<?php echo $usergroup_id; ?>');">
<?php
		echo sp_create_nonce('forum-adminform_memberdel');
		spa_paint_open_tab(spa_text('User Groups')." - ".spa_text('Manage User Groups'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Delete/Move Members'), 'true', 'move-delete-members', false);
?>
					<input type="hidden" name="usergroupid" value="<?php echo $usergroup_id; ?>" />
					<p><?php spa_etext("Select members to delete/move (use CONTROL for multiple users)") ?></p>
					<p><?php spa_etext("To move members, select a new usergroup") ?></p>
					<?php spa_display_usergroup_select() ?>
<?php
					$from = esc_js(spa_text('Current Members'));
					$to = esc_js(spa_text('Selected Members'));
                    $action = 'delug';
                	include_once(SF_PLUGIN_DIR.'/admin/library/ahah/spa-ahah-multiselect.php');
?>
					<div class="clearboth"></div>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_usergroup_delete_member_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<span><input type="submit" class="button-primary" id="sfmemberdel<?php echo $usergroup_id; ?>" name="sfmemberdel<?php echo $usergroup_id; ?>" value="<?php spa_etext('Delete/Move Members'); ?>" /> <span class="button sfhidden" id='onFinish'></span>
		<input type="button" class="button-primary" onclick="javascript:jQuery('#members-<?php echo $usergroup_id; ?>').html('');" id="sfmemberdel<?php echo $usergroup_id; ?>" name="delmemberscancel<?php echo $usergroup_id; ?>" value="<?php spa_etext('Cancel'); ?>" /></span>
		<br />
		<div class="pbar" id="progressbar"></div>
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>