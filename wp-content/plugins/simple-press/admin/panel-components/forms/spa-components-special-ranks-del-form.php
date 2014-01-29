<?php
/*
Simple:Press
Admin Components Special Rank Delete Member Form
$LastChangedDate: 2013-03-16 15:24:09 -0700 (Sat, 16 Mar 2013) $
$Rev: 10079 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_components_sr_del_members_form($rank_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfmemberdel<?php echo $rank_id; ?>', 'sfreloadfr');
});
</script>
<?php
	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;saveform=specialranks&amp;action=delmember&amp;id=$rank_id";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfmemberdel<?php echo $rank_id; ?>" name="sfmemberdel<?php echo $rank_id ?>">
<?php
		echo sp_create_nonce('special-rank-del');
		spa_paint_open_tab(spa_text('Components').' - '.spa_text('Special Ranks'));
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Remove Members'), false, '', false);
?>
					<p align="center"><?php spa_etext('Select member to add (use CONTROL for multiple members)') ?></p>
<?php
                	$from = esc_js(spa_text('Current members'));
                	$to = esc_js(spa_text('Selected Members'));
                    $action = 'delru';
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
		<input type="submit" class="button-primary" id="sfmemberdel<?php echo $rank_id; ?>" name="sfmemberdel<?php echo $rank_id; ?>" onclick="javascript:jQuery('#dmember_id<?php echo $rank_id; ?> option').each(function(i) {jQuery(this).attr('selected', 'selected');});" value="<?php spa_etext('Remove Members'); ?>" />
		<input type="button" class="button-primary" onclick="spjToggleLayer('<?php echo $loc; ?>');javascript:jQuery('#members-<?php echo $rank_id; ?>').html('');" id="sfmemberdel<?php echo $rank_id; ?>" name="addmemberscancel<?php echo $rank_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>