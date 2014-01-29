<?php
/*
Simple:Press
Admin Components Special Rank Add Member Form
$LastChangedDate: 2013-09-12 23:15:23 -0700 (Thu, 12 Sep 2013) $
$Rev: 10674 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_sr_add_members_form($rank_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfmembernew<?php echo $rank_id; ?>', 'sfreloadfr');
});
</script>
<?php
	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;saveform=specialranks&amp;action=addmember&amp;id=$rank_id";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfmembernew<?php echo $rank_id; ?>" name="sfmembernew<?php echo $rank_id ?>">
<?php
		echo sp_create_nonce('special-rank-add');
		spa_paint_open_tab(spa_text('Components').' - '.spa_text('Forum Ranks'));
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Add Members'), false, '', false);
?>
					<p align="center"><?php spa_etext('Select members to add (use CONTROL for multiple members)') ?></p>
<?php
                	$from = esc_js(spa_text('Eligible members'));
                	$to = esc_js(spa_text('Selected members'));
                    $action = 'addru';
                	include_once(SF_PLUGIN_DIR.'/admin/library/ahah/spa-ahah-multiselect.php');
?>
					<div class="clearboth"></div>
<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
		spa_paint_close_tab();
        $loc = 'sfrankshow-'.$rank_id;
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfnewmember<?php echo $rank_id; ?>" name="sfnewmember<?php echo $rank_id; ?>" onclick="javascript:jQuery('#amember_id<?php echo $rank_id; ?> option').each(function(i) {jQuery(this).attr('selected', 'selected');});" value="<?php spa_etext('Add Members'); ?>" />
		<input type="button" class="button-primary" onclick="spjToggleLayer('<?php echo $loc; ?>');javascript:jQuery('#members-<?php echo $rank_id; ?>').html('');" id="addmemberscancel<?php echo $rank_id; ?>" name="addmemberscancel<?php echo $rank_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>