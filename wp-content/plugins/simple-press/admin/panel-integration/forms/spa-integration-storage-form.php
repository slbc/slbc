<?php
/*
Simple:Press
Admin Integration Storage Locations Form
$LastChangedDate: 2013-09-02 13:14:36 -0700 (Mon, 02 Sep 2013) $
$Rev: 10638 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_integration_storage_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfstorageform', 'sfreloadsl');
	jQuery(function(jQuery){vtip();})
});
</script>
<?php
	# Get correct tooltips file
	$lang = WPLANG;
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-integration-storage-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-integration-storage-tips-en.php';
	if (file_exists($ttpath)) include_once($ttpath);

	$sfoptions = spa_get_storage_data();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=integration-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=storage';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfstorageform" name="sfstorage">
	<?php echo sp_create_nonce('forum-adminform_storage'); ?>
<?php

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Integration').' - '.spa_text('Storage Locations'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Set Storage Locations'), true, 'storage-locations', true);

			echo '<table class="form-table"><tr><td colspan="3"><br /><div class="sfoptionerror">';
			spa_etext('BEWARE: Please read the help before making any changes to these locations. Incorrect changes may cause Simple:Press to stop functioning');
			echo '</div><br />';

			echo '&nbsp;<img src="'.SFADMINIMAGES.'sp_Yes.png" title="'.spa_text('Location found').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.spa_text('Location found').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<img src="'.SFADMINIMAGES.'sp_No.png" title="'.spa_text('Location not found').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.spa_text('Location not found').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<img src="'.SFADMINIMAGES.'sp_YesWrite.png" title="'.spa_text('Write - OK').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.spa_text('Write - OK').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<img src="'.SFADMINIMAGES.'sp_NoWrite.png" title="'.spa_text('Write - denied').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;'.spa_text('Write - denied');

			echo '<p><strong>'.spa_text('Set the new location of your').':</strong></p></td></tr>';

			$ok = true;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['plugins'];
			$r = spa_paint_storage_input(spa_text('Plugins Folder'), 'plugins', $sfoptions['plugins'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['themes'];
			$r = spa_paint_storage_input(spa_text('Themes Folder'), 'themes', $sfoptions['themes'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['avatars'];
			$r = spa_paint_storage_input(spa_text('Avatars Folder'), 'avatars', $sfoptions['avatars'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['avatar-pool'];
			$r = spa_paint_storage_input(spa_text('Avatar Pool Folder'), 'avatar-pool', $sfoptions['avatar-pool'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['smileys'];
			$r = spa_paint_storage_input(spa_text('Smileys Folder'), 'smileys', $sfoptions['smileys'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['ranks'];
			$r = spa_paint_storage_input(spa_text('Forum Badges Folder'), 'ranks', $sfoptions['ranks'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['custom-icons'];
			$r = spa_paint_storage_input(spa_text('Custom Icons Folder'), 'custom-icons', $sfoptions['custom-icons'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['language-sp'];
			$r = spa_paint_storage_input(spa_text('Simple:Press Language Files'), 'language-sp', $sfoptions['language-sp'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['language-sp-plugins'];
			$r = spa_paint_storage_input(spa_text('Simple:Press Plugin Language Files'), 'language-sp-plugins', $sfoptions['language-sp-plugins'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['language-sp-themes'];
			$r = spa_paint_storage_input(spa_text('Simple:Press Theme Language Files'), 'language-sp-themes', $sfoptions['language-sp-themes'], $path);
			if(!$r) $ok = false;

			$path = WP_CONTENT_DIR.'/'.$sfoptions['cache'];
			$r = spa_paint_storage_input(spa_text('Forum CSS/JS Cache'), 'cache', $sfoptions['cache'], $path);
			if(!$r) $ok = false;

			do_action('sph_integration_storage_panel_location');

			if (!$ok) {
				echo '<tr><td colspan="3"><br /><div class="sfoptionerror"><h4>';
				spa_etext('For Simple:Press to function correctly it is imperative that the above location errors are resolved');
				echo '</h4></div></td></tr>';
			}

			echo '</table>';
			spa_paint_close_fieldset();

		spa_paint_close_panel();

		do_action('sph_integration_storage_panel');
	spa_paint_close_tab();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Storage Locations'); ?>" />
	</div>
	</form>
<?php

	spa_check_upgrade_error();
}

function spa_paint_storage_input($label, $name, $value, $path, $na=false) {
	global $tab, $tooltips;

	$found = false;
	$ok = false;
	if (file_exists($path)) {
		$found = true;
		$ok = true;
	}

	if ($found) {
		$icon1 = '<img src="'.SFADMINIMAGES.'sp_Yes.png" title="'.spa_text('Location found').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
	} else {
		$icon1 = '<img src="'.SFADMINIMAGES.'sp_No.png" title="'.spa_text('Location not found').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
		$icon2 = '<img src="'.SFADMINIMAGES.'sp_NoWrite.png" title="'.spa_text('Write - denied').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
	}

	if ($found) {
		if (is_writable($path)) {
			$icon2 = '<img src="'.SFADMINIMAGES.'sp_YesWrite.png" title="'.spa_text('Write - OK').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
		} else {
			$icon2 = '<img src="'.SFADMINIMAGES.'sp_NoWrite.png" title="'.spa_text('Write - denied').'" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
			$ok = false;
		}
	}

	if ($na) {
		$icon2 = '<img src="'.SFADMINIMAGES.'sp_NA.gif" title="" alt="" style="vertical-align: middle;" />&nbsp;&nbsp;';
		$ok = $found;
	}

	echo "<tr valign='top'>\n";
	if ($found) {
		echo "<td class='sflabel' width='40%'>\n";
	} else {
		echo "<td class='sflabel highlight' width='40%'>\n";
	}

	echo "<span class='sfalignleft'>".$icon1.$icon2.$label.":</span>";
	echo "<span class='sfalignright'>";
	echo '<img src="'.SFADMINIMAGES.'sp_Information.png" alt="" class="vtip" title="'.$tooltips[sp_create_slug($name, false)].'" />&nbsp;&nbsp;';
	echo SF_STORE_RELATIVE_BASE.'</span>';

	echo "</td>\n";
	if ($found) {
		$opentd = '<td>';
	} else {
		$opentd = "<td class='highlight'>\n";
	}
	echo $opentd;
	echo '<input type="text" class="sfpostcontrol" tabindex="'.$tab.'" name="'.$name.'" value="'.esc_attr($value).'" ';
	echo "/></td>\n";

	echo "</tr>\n";
	$tab++;
	return $ok;
}

function spa_check_upgrade_error() {
	# REPORTS ERRORS IF COPY OR UNZIP FAILED ---------------

	$r = spdb_table(SFOPTIONS, "option_name='spStorageInstall2'", 'row');
	($r ? $sCreate = $r->option_value : $sCreate=true);
	$r = spdb_table(SFOPTIONS, "option_name='spOwnersInstall2'", 'row');
	($r ? $sOwner = $r->option_value : $sOwner=true);
	$r = spdb_table(SFOPTIONS, "option_name='spCopyZip2'", 'row');
	($r ? $sCopy = $r->option_value : $sCopy=true);
	$r = spdb_table(SFOPTIONS, "option_name='spUnZip2'", 'row');
	($r ? $sUnzip = $r->option_value : $sUnzip=true);

	if ($sCreate && $sCopy && $sUnzip) {
		return;
	} else {

		$image = "<img src='".SF_PLUGIN_URL."/sp-startup/install/resources/images/important.png' alt='' style='float:left;padding: 5px 5px 8px 0;' />";

		echo '<h3><br />';
		spa_etext("YOU WILL NEED TO PERFORM THE FOLLOWING TASKS TO ALLOW SIMPLE:PRESS TO WORK CORRECTLY");
		echo '</h3>';

		if ($sCreate == false) {
			echo $image.'<h4>[';
			spa_etext('Storage location creation failed on upgrade');
			echo '] - ';
			spa_etext("You will need to manually create a required sub-folder in your wp-content folder named 'sp-resources'");
			echo '</h4>';
		} elseif($sOwner == false) {
			echo $image.'<h5>[';
			spa_etext('Storage location part 1 ownership failed');
			echo '] - ';
			spa_etext("We were unable to create your folders with the correct server ownership and these will need to be manually changed");
			echo '</h5>';
		}
		if ($sCopy == false) {
			echo $image.'<h4>[';
			spa_etext('Resources file failed to copy on upgrade');
			echo '] - ';
			spa_etext("You will need to manually copy the file '/simple-press/sp-startup/install/sp-resources-install-part2.zip' to the new 'wp-content/sp-resources' folder");
			echo '</h4>';
		}
		if ($sUnzip == false) {
			echo $image.'<h4>[';
			spa_etext('Resources file failed to unzip on upgrade');
			echo '] - ';
			spa_etext("You will need to manually unzip the file 'sp-resources-install-part2.zip in the new 'wp-content/sp-resources' folder");
			echo '</h4>';
		}
	}
	sp_delete_option('spStorageInstall2');
	sp_delete_option('spCopyZip2');
	sp_delete_option('spUnZip2');
}

?>