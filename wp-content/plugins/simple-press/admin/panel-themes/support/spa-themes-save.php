<?php
/*
Simple:Press
Admin plugins Update Support Functions
$LastChangedDate: 2013-08-29 01:39:16 -0700 (Thu, 29 Aug 2013) $
$Rev: 10608 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

/**
* Saves the selected theme as the current active theme.
*/
function spa_save_theme_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

	$theme = sp_esc_str($_POST['theme']);
	$style = sp_esc_str($_POST['style']);
	$color = sp_esc_str($_POST['color-'.$theme]);

	if (isset($_POST['activate']) || isset($_POST['update'])) {
		if (empty($theme) || empty($style)) return spa_text('An error occurred activating the Theme!');
		if (empty($color)) $color = sp_esc_str($_POST['default-color']);

		# activate the theme
		$current = array();
		$current['theme'] = $theme;
		$current['style'] = $style;
		$current['color'] = $color;
		sp_update_option('sp_current_theme', $current);

		# load theme functions file in case it wants to hook into activation
		if (file_exists(SPTHEMEBASEDIR.$theme.'/templates/spFunctions.php')) {
			include_once(SPTHEMEBASEDIR.$theme.'/templates/spFunctions.php');
		}

		# clean out the combined css file
		sp_clear_combined_css('all');
		sp_clear_combined_css('mobile');
		sp_clear_combined_css('tablet');

		# theme activation action
		do_action('sph_activate_theme', $current);
		do_action('sph_activate_theme_'.$theme, $current);

		return spa_text('Theme activated/updated');
	} else if (isset($_POST['delete']) == 'delete' && (!is_multisite() || is_super_admin())) {
		$mess = sp_delete_sp_theme($theme);
		return $mess;
	}
}

function spa_save_theme_mobile_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

	$mobileTheme = sp_get_option('sp_mobile_theme');
	$curTheme = sp_get_option('sp_current_theme');

	$mobile = array();
	$active = isset($_POST['active']);
	if ($active && $mobileTheme['active']) {
		if(isset($_POST['theme']) ? $theme = sp_esc_str($_POST['theme']) : $theme = $mobileTheme['theme']);
		if(isset($_POST['style']) ? $style = sp_esc_str($_POST['style']) : $style = $mobileTheme['style']);
		if(isset($_POST['color-'.$theme]) ? $color = sp_esc_str($_POST['color-'.$theme]) : $color = $mobileTheme['color']);

		if(isset($_POST['pagetemplate']) ? $pagetemplate = sp_esc_str($_POST['pagetemplate']) : $pagetemplate = $mobileTheme['pagetemplate']);

		if(isset($_POST['pagetemplate'])) {
			$usetemplate = isset($_POST['usetemplate']);
			$notitle = isset($_POST['notitle']);
		} else {
			$usetemplate = $mobileTheme['usetemplate'];
			$notitle = $mobileTheme['notitle'];
		}

		if (empty($theme) || empty($style)) return spa_text('No data changed');
		if (empty($color)) $color = sp_esc_str($_POST['default-color']);

		$mobile['active'] = true;
		$mobile['theme'] = $theme;
		$mobile['style'] = $style;
		$mobile['color'] = $color;
		$mobile['usetemplate'] = $usetemplate;
		$mobile['pagetemplate'] = $pagetemplate;
		$mobile['notitle'] = $notitle;
	} else {
		$mobile = array();
		$mobile['active'] = $active;
		$mobile['theme'] = $curTheme['theme'];
		$mobile['style'] = $curTheme['style'];
		$mobile['color'] = $curTheme['color'];
		$mobile['usetemplate'] = false;
		$mobile['pagetemplate'] = spdb_table(SFWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".sp_get_option('sfpage'), 'meta_value');
		$mobile['notitle'] = true;
	}
	sp_update_option('sp_mobile_theme', $mobile);

	# clean out the combined css file
	sp_clear_combined_css('mobile');
	sp_clear_combined_css('tablet');

	# theme activation action
	do_action('sph_activate_mobile_theme', $mobile);
	do_action('sph_activate_mobile_theme_'.$mobile['theme'], $mobile);

	return spa_text('Mobile Phone theme activated/updated');
}

function spa_save_theme_tablet_data() {
	check_admin_referer('forum-adminform_themes', 'forum-adminform_themes');

	$tabletTheme = sp_get_option('sp_tablet_theme');
	$curTheme = sp_get_option('sp_current_theme');

	$tablet = array();
	$active = isset($_POST['active']);
	if ($active && $tabletTheme['active']) {
		if(isset($_POST['theme']) ? $theme = sp_esc_str($_POST['theme']) : $theme = $tabletTheme['theme']);
		if(isset($_POST['style']) ? $style = sp_esc_str($_POST['style']) : $style = $tabletTheme['style']);
		if(isset($_POST['color-'.$theme]) ? $color = sp_esc_str($_POST['color-'.$theme]) : $color = $tabletTheme['color']);

		if(isset($_POST['pagetemplate']) ? $pagetemplate = sp_esc_str($_POST['pagetemplate']) : $pagetemplate = $tabletTheme['pagetemplate']);

		if(isset($_POST['pagetemplate'])) {
			$usetemplate = isset($_POST['usetemplate']);
			$notitle = isset($_POST['notitle']);
		} else {
			$usetemplate = $tabletTheme['usetemplate'];
			$notitle = $tabletTheme['notitle'];
		}

		if (empty($theme) || empty($style)) return spa_text('No data changed');
		if (empty($color)) $color = sp_esc_str($_POST['default-color']);

		$tablet['active'] = true;
		$tablet['theme'] = $theme;
		$tablet['style'] = $style;
		$tablet['color'] = $color;
		$tablet['usetemplate'] = $usetemplate;
		$tablet['pagetemplate'] = $pagetemplate;
		$tablet['notitle'] = $notitle;
	} else {
		$tablet = array();
		$tablet['active'] = $active;
		$tablet['theme'] = $curTheme['theme'];
		$tablet['style'] = $curTheme['style'];
		$tablet['color'] = $curTheme['color'];
		$tablet['usetemplate'] = false;
		$tablet['pagetemplate'] = spdb_table(SFWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".sp_get_option('sfpage'), 'meta_value');
		$tablet['notitle'] = true;
	}
	sp_update_option('sp_tablet_theme', $tablet);

	# clean out the combined css file
	sp_clear_combined_css('tablet');

	# theme activation action
	do_action('sph_activate_tablet_theme', $tablet);
	do_action('sph_activate_tablet_theme_'.$tablet['theme'], $tablet);

	return spa_text('Mobile Tablet theme activated/updated');
}

function spa_save_editor_data() {
	check_admin_referer('forum-adminform_theme-editor', 'forum-adminform_theme-editor');

	$file = stripslashes($_POST['file']);
	$newcontent = stripslashes($_POST['spnewcontent']);
	if (is_writeable($file)) {
		$f = fopen($file, 'w+');
		if ($f !== FALSE) {
			fwrite($f, $newcontent);
			fclose($f);
			$msg = spa_text('Theme file updated!');
		} else {
			$msg = spa_text('Unable to save theme file');
		}
	} else {
		$msg = spa_text('Theme file is not writable!');
	}

	return $msg;
}
?>