<?php
/*
Simple:Press
Admin Help
$LastChangedDate: 2013-05-03 03:44:15 -0700 (Fri, 03 May 2013) $
$Rev: 10258 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

if (!isset($_GET['file'])) die();

$file = sp_esc_str($_GET['file']);
$tag = sp_esc_str($_GET['item']);
$tag = '['.$tag.']';
$folder = 'panels/';

# Formatting and Display of Help Panel
$helptext = wpautop(sp_retrieve_help($file, $tag, $folder), false);

echo '<div class="sfhelptext">';
echo '<div class="sfhelptag"><p>'.sp_convert_tag($tag).'</p></div>';
echo '<fieldset>';
echo $helptext;
echo '</fieldset>';
echo '<div class="sfhelptextlogo">';
echo '<img src="'.SFCOMMONIMAGES.'sp-small-megaphone.png" alt="" title="" />';
echo '</div></div>';

die();

function sp_retrieve_help($file, $tag, $folder) {
	$path = SPHELP.'admin/'.$folder;
	$note = '';
	$lang = WPLANG;
	if (empty($lang)) $lang = 'en';

	$helpfile = $path.$file.'.'.$lang;
    $helpfile = apply_filters('sph_admin_help-'.$file, $helpfile, $tag, $lang);

	if (file_exists($helpfile) == false) {
		$helpfile = $path.$file.'.en';
		if (file_exists($helpfile) == false) {
			return spa_text('No help file can be located');
		} else {
			$note = spa_text('Sorry but a help file can not be found in your language');
		}
	}

	$fh = fopen($helpfile, 'r');
	do {
		$theData = fgets($fh);
		if (feof($fh)) break;
	} while ((substr($theData, 0, strlen($tag)) != $tag));


	$theData = '';
	$theEnd = false;
	do {
		if (feof($fh)) break;
		$theLine = fgets($fh);
		if (substr($theLine, 0, 5) == '[end]') {
			$theEnd = true;
		} else {
			$theData.= $theLine;
		}
	} while ($theEnd == false);

	fclose($fh);

	return $note.'<br /><br />'.$theData;
}

function sp_convert_tag($tag) {
	$tag = str_replace ('[', '', $tag);
	$tag = str_replace (']', '', $tag);
	$tag = str_replace ('-', ' ', $tag);
	$tag = str_replace ('_', ' ', $tag);
	return ucwords($tag);
}

?>