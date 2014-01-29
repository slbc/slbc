<?php
/*
Simple:Press
Admin Toolbox Inspector Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_inspector_form()
{
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfinspectorform', '');
});
</script>
<?php
	$ins = spa_get_inspector_data();
    $ahahURL = SFHOMEURL."index.php?sp_ahah=toolbox-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=inspector";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfinspectorform" name="sfinspector">
	<?php echo sp_create_nonce('forum-adminform_inspector'); ?>
<?php

	spa_paint_options_init();

#== UNINSTALL Tab ==========================================================

	spa_paint_open_tab(spa_text('Toolbox')." - ".spa_text('Data Inspector'));

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Data Inspector'), true, 'inspect-data', true);
				echo '<br /><div class="sfoptionerror">';
				spa_etext("Turning any of these options on will cause the data object being used to populate the relevant view or section to be displayed. You are the only user who will be shown these displays");
				echo '.<br />';
				echo '</div>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Control Data'), false, '', true);
				spa_paint_checkbox(spa_text('spVars'), "con_spVars", $ins['con_spVars']);
				spa_paint_checkbox(spa_text('spGlobals'), "con_spGlobals", $ins['con_spGlobals']);
				spa_paint_checkbox(spa_text('spThisUser'), "con_spThisUser", $ins['con_spThisUser']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Group View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spGroupView'), "gv_spGroupView", $ins['gv_spGroupView']);
				spa_paint_checkbox(spa_text('spThisGroup'), "gv_spThisGroup", $ins['gv_spThisGroup']);
				spa_paint_checkbox(spa_text('spThisForum'), "gv_spThisForum", $ins['gv_spThisForum']);
				spa_paint_checkbox(spa_text('spThisForumSubs'), "gv_spThisForumSubs", $ins['gv_spThisForumSubs']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Forum View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spForumView'), "fv_spForumView", $ins['fv_spForumView']);
				spa_paint_checkbox(spa_text('spThisForum'), "fv_spThisForum", $ins['fv_spThisForum']);
				spa_paint_checkbox(spa_text('spThisForumSubs'), "fv_spThisForumSubs", $ins['fv_spThisForumSubs']);
				spa_paint_checkbox(spa_text('spThisSubForum'), "fv_spThisSubForum", $ins['fv_spThisSubForum']);
				spa_paint_checkbox(spa_text('spThisTopic'), "fv_spThisTopic", $ins['fv_spThisTopic']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

	spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Topic View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spTopicView'), "tv_spTopicView", $ins['tv_spTopicView']);
				spa_paint_checkbox(spa_text('spThisTopic'), "tv_spThisTopic", $ins['tv_spThisTopic']);
				spa_paint_checkbox(spa_text('spThisPost'), "tv_spThisPost", $ins['tv_spThisPost']);
				spa_paint_checkbox(spa_text('spThisPostUser'), "tv_spThisPostUser", $ins['tv_spThisPostUser']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();


		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Member View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spMembersList'), "mv_spMembersList", $ins['mv_spMembersList']);
				spa_paint_checkbox(spa_text('spThisMemberGroup'), "mv_spThisMemberGroup", $ins['mv_spThisMemberGroup']);
				spa_paint_checkbox(spa_text('spThisMember'), "mv_spThisMember", $ins['mv_spThisMember']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Profile View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spProfileUser'), "pro_spProfileUser", $ins['pro_spProfileUser']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Topic List View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spTopicListView'), "tlv_spTopicListView", $ins['tlv_spTopicListView']);
				spa_paint_checkbox(spa_text('spThisListTopic'), "tlv_spThisListTopic", $ins['tlv_spThisListTopic']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Post List View Data'), false, '', true);
				spa_paint_checkbox(spa_text('spPostListView'), "plv_spPostListView", $ins['plv_spPostListView']);
				spa_paint_checkbox(spa_text('spThisListPost'), "plv_spThisListPost", $ins['plv_spThisListPost']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_toolbox_insepctor_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Inspector Settings'); ?>" />
	</div>
	</form>
<?php
}
?>