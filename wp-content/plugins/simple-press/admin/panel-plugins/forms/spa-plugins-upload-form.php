<?php
/*
Simple:Press
Admin plugins uploader
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_plugins_upload_form() {
    # Make sure only super admin can upload on multisite
	if (is_multisite() && !is_super_admin()) {
   		spa_etext('Access denied - you do not have permission');
    	die();
    }

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Upload Plugin')." - ".spa_text('Upload a Simple:Press Plugin'), true);
	spa_paint_open_panel();
        spa_paint_open_fieldset(spa_text('Upload Plugin'), true, 'upload-plugin', false);
            echo '<p>'.spa_text('Upload a Simple:Press plugin in .zip format').'</p>';
            echo '<p>'.spa_text('If you have a plugin in a .zip format, you may upload it here').'</p>';
?>
        	<form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url('update.php?action=upload-sp-plugin'); ?>" id="sfpluginuploadform" name="sfpluginuploadform">
                <?php echo sp_create_nonce('forum-plugin_upload'); ?>
        		<p><input type="file" id="pluginzip" name="pluginzip" /></p>
        		<p><input type="button" class="button-primary" id="saveupload" name="saveupload" value="<?php spa_etext('Upload Now') ?>" onclick="jQuery('#saveupload').attr('disabled', 'disabled'); javascript:document.sfpluginuploadform.submit();" /></p>
        	</form>
<?php
		spa_paint_close_fieldset();

        do_action('sph_plugins_upload_panel');
	spa_paint_close_panel();
	spa_paint_close_tab();
}

?>