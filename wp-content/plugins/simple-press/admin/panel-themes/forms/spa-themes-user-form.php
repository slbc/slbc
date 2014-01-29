<?php
/*
Simple:Press
Admin themes user form
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_user_form($admin, $save, $form, $reload) {
	if ($form) {
?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
        	jQuery('#sfthemesuser').ajaxForm({
        		target: '#sfmsgspot',
        		success: function() {
<?php
		if (!empty($reload)) echo "jQuery('#".$reload."').click();";
?>
        			jQuery('#sfmsgspot').fadeIn();
        			jQuery('#sfmsgspot').fadeOut(6000);
        		}
        	});
        });
        </script>
<?php
		spa_paint_options_init();
		$ahahURL = SFHOMEURL."index.php?sp_ahah=themes-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=plugin&amp;func=".$save;
		echo '<form action="'.$ahahURL.'" method="post" id="sfpluginsuser" name="sfpluginsuser">';
		echo sp_create_nonce('forum-adminform_userplugin');
	}

	call_user_func($admin);

	if ($form) {
?>
    	<div class="sfform-submit-bar">
    	   <input type="submit" class="button-primary" value="<?php spa_etext('Update'); ?>" />
    	</div>
        </form>

    	<div class="sfform-panel-spacer"></div>
<?php
	}
}
?>