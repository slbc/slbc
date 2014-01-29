<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2013-08-13 00:45:37 -0700 (Tue, 13 Aug 2013) $
$Rev: 10511 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_uninstall_form()
{
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfuninstallform', '');
});
</script>
<?php

	$sfoptions = spa_get_uninstall_data();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=toolbox-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=uninstall';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfuninstallform" name="sfuninstall">
	<?php echo sp_create_nonce('forum-adminform_uninstall'); ?>
<?php

	spa_paint_options_init();

#== UNINSTALL Tab ==========================================================

	spa_paint_open_tab(spa_text('Toolbox').' - '.spa_text('Uninstall'), true);
		spa_paint_open_panel();
			echo '<br /><div class="sfoptionerror">';
			spa_etext('Should you, at any time, decide to remove Simple:Press, check the option below and then deactivate the plugin in the standard WP fashion');
            echo '.<br />';
            spa_etext('THIS MAY REMOVE ALL FORUM DATA FROM YOUR DATABASE (see option)');
            echo '!<br />';
            spa_etext('THIS MAY REMOVE ALL STORAGE LOCATIONS AND DATA EXCEPT FOR YOUR SIMPLE:PRESS THEMES AND PLUGINS (see option)');
            echo '!<br />';
            spa_etext('THIS CAN NOT BE REVERSED');
            echo '!<br />';
            spa_etext('Please note that you will still manually need to remove the Simple:Press core plugin files as well as any Simple:Press Plugins and Themes you have added');
            echo '.<br />';
			echo '</div>';

			spa_paint_open_fieldset(spa_text('Removing Simple:Press'), true, 'uninstall', true);
				spa_paint_checkbox(spa_text('Completely remove Simple:Press database entries'), 'sfuninstall', $sfoptions['sfuninstall']);
				spa_paint_checkbox(spa_text('Completely remove Simple:Press storage locations'), 'removestorage', $sfoptions['removestorage']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_toolbox_uninstall_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Uninstall'); ?>" />
	</div>
	</form>
<?php
}
?>