<?php
/*
Simple:Press
Admin Forums Merge Forums Form
$LastChangedDate: 2011-09-09 20:28:24 +0100 (Fri, 09 Sep 2011) $
$Rev: 7034 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the merge forums form.
function spa_forums_merge_form() {

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfmergeforums', 'sfreloadmf');
});
</script>
<?php

	spa_paint_options_init();

	$ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=mergeforums';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfmergeforums" name="sfmergeforums">
<?php
		echo sp_create_nonce('forum-adminform_mergeforums');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Merge Forums'));

			spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Select Source Forum to Merge From'), false, '', true);
?>
				<div id="forumselect">
					<?php spa_etext('The source forum selected here will have all sub-forums, topics, posts and references transferred to the forum selected as the target for the merge. It will then be deleted.') ?><br /><br />
					<select style="width:190px" class="sfacontrol" name="source">
						<?php echo sp_render_group_forum_select(false, false, false, true, spa_text('Select Source Forum to Merge From')); ?>
					</select>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();

		spa_paint_tab_right_cell();

			spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Select Target Forum to Merge To'), true, 'merge-forums', true);
?>
				<div id="forumselect">
					<?php spa_etext('The target forum selected here will inherit all sub-forums, topics, posts and references from the source forum. Current permissions for this forum will be retained.') ?><br /><br />
					<select style="width:190px" class="sfacontrol" name="target">
						<?php echo sp_render_group_forum_select(false, false, false, true, spa_text('Select Target Forum to Merge To')); ?>
					</select>
				</div>
<?php
			spa_paint_close_fieldset();
			spa_paint_close_panel();

			do_action('sph_forums_merge_forums_panel');

		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Perform Forum Merge'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>