<?php
/*
Simple:Press
Admin integration Update Support Functions
$LastChangedDate: 2013-09-14 14:49:56 -0700 (Sat, 14 Sep 2013) $
$Rev: 10682 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_integration_page_data() {
    check_admin_referer('forum-adminform_integration', 'forum-adminform_integration');

	$mess = '';
	$slugid = sp_esc_int($_POST['slug']);
	if ($slugid == '' || $slugid == 0) {
		$setslug = '';
		$setpage = 0;
	} else {
		$setpage = $slugid;
		$page = spdb_table(SFWPPOSTS, "ID=$slugid", 'row');
		$setslug = $page->post_name;

		if ($page->post_parent) {
			$parent = $page->post_parent;
			while ($parent) {
				$thispage = spdb_table(SFWPPOSTS, "ID=$parent", 'row');
				$setslug = $thispage->post_name.'/'.$setslug;
				$parent = $thispage->post_parent;
			}
		}
	}

	sp_update_option('sfpage', $setpage);
	sp_update_option('sfslug', $setslug);

	spa_update_check_option('sfinloop');
	spa_update_check_option('sfmultiplecontent');
	spa_update_check_option('sfwpheadbypass');
	spa_update_check_option('sfwplistpages');
	spa_update_check_option('sfscriptfoot');
	spa_update_check_option('sfuseob');

	if (!$setpage) {
		$mess.= spa_text('Page slug missing');
		$mess.= ' - '.spa_text('Unable to determine forum permalink without it');
	} else {
		$mess.= spa_text('Forum page and slug updated');
        sp_update_permalink(true);
	}

    do_action('sph_integration_save');

	return $mess;
}

function spa_save_integration_storage_data() {
	check_admin_referer('forum-adminform_storage', 'forum-adminform_storage');

	$mess = spa_text('Storage locations updated');

	$sfstorage = array();
	$sfstorage = sp_get_option('sfconfig');
	if (!empty($_POST['plugins'])) $sfstorage['plugins'] = trim(sp_filter_title_save(trim($_POST['plugins'])), '/');
	if (!empty($_POST['themes'])) $sfstorage['themes'] = trim(sp_filter_title_save(trim($_POST['themes'])), '/');
	if (!empty($_POST['avatars'])) $sfstorage['avatars'] = trim(sp_filter_title_save(trim($_POST['avatars'])), '/');
	if (!empty($_POST['avatar-pool'])) $sfstorage['avatar-pool'] = trim(sp_filter_title_save(trim($_POST['avatar-pool'])), '/');
	if (!empty($_POST['smileys'])) $sfstorage['smileys'] = trim(sp_filter_title_save(trim($_POST['smileys'])), '/');
	if (!empty($_POST['ranks'])) $sfstorage['ranks'] = trim(sp_filter_title_save(trim($_POST['ranks'])), '/');
	if (!empty($_POST['image-uploads'])) $sfstorage['image-uploads'] = trim(sp_filter_title_save(trim($_POST['image-uploads'])), '/');
	if (!empty($_POST['media-uploads'])) $sfstorage['media-uploads'] = trim(sp_filter_title_save(trim($_POST['media-uploads'])), '/');
	if (!empty($_POST['file-uploads'])) $sfstorage['file-uploads'] = trim(sp_filter_title_save(trim($_POST['file-uploads'])), '/');
	if (!empty($_POST['custom-icons'])) $sfstorage['custom-icons'] = trim(sp_filter_title_save(trim($_POST['custom-icons'])), '/');
	if (!empty($_POST['language-sp'])) $sfstorage['language-sp'] = trim(sp_filter_title_save(trim($_POST['language-sp'])), '/');
	if (!empty($_POST['language-sp-plugins'])) $sfstorage['language-sp-plugins'] = trim(sp_filter_title_save(trim($_POST['language-sp-plugins'])), '/');
	if (!empty($_POST['language-sp-themes'])) $sfstorage['language-sp-themes'] = trim(sp_filter_title_save(trim($_POST['language-sp-themes'])), '/');
	if (!empty($_POST['cache'])) $sfstorage['cache'] = trim(sp_filter_title_save(trim($_POST['cache'])), '/');

	sp_update_option('sfconfig', $sfstorage);

    do_action('sph_integration_storage_save');

	return $mess;
}

?>