<?php
/*
Simple:Press
Ahah call for acknowledgements
$LastChangedDate: 2013-05-14 14:16:41 -0700 (Tue, 14 May 2013) $
$Rev: 10301 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_api_support();

$theme = sp_get_current_sp_theme();

$out = '';
$out.= '<div id="spAbout">';
$out.= '<img src="'.SFCOMMONIMAGES.'sp-small-logo.png" alt="" title="" /><br />';
$out.= '<p>&copy; 2006-'.date('Y').' '.sp_text('by').' <a href="http://www.yellowswordfish.com"><b>Andy Staines</b></a> '.sp_text('and').' <a href="http://cruisetalk.org/"><b>Steve Klasen</b></a></p>';
$out.= '<p><a href="http://twitter.com/simpleforum">'.sp_text('Follow us On Twitter').'</a></p>';
$out.= '<hr />';
$out.= '<p>';

$ack = array(
	sp_text('printThis by Jason Day').': <a href="https://github.com/jasonday/printThis">printThis</a>',
	sp_text('Math Spam Protection based on code by Michael Woehrer').': <a href="http://sw-guide.de/">Software Guide</a>',
	sp_text('Calendar Date Picker by TengYong Ng').': <a href="http://www.rainforestnet.com">Rain Forest Net</a>',
	sp_text('Image Uploader by Andrew Valums').': <a href="http://valums.com/ajax-upload/">Ajax upload</a>',
	sp_text('Checkbox and Radio Button transformations by').': <a href="http://www.no-margin-for-errors.com/">Stephane Caron</a>',
	sp_text('SPF RPX implementation uses code and ideas from RPX').': <a href="http://rpxwiki.com/WordpressPlugin">Brian Ellin</a>',
	sp_text('Popup Tooltips by the Vertigo Project').': <a href="http://www.vertigo-project.com/">Vertigo Project</a>',
	sp_text('Table Drag and Drop').': <a href="http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/">Isocra Consulting</a>',
	sp_text('Mobile Device Detection based on code by Brett Jankord').': <a href="http://www.brettjankord.com/2012/01/16/categorizr-a-modern-device-detection-script/">Categorizr</a>',
	sp_text('CSS and JS Concatenation based on code by Ronen Yacobi').': <a href="http://http://yacobi.info/">CSS And Script File Aggregation</a>',
);

$ack = apply_filters('sph_acknowledgements', $ack);

foreach($ack as $a) {
	$out.= $a.'<br />';
}

$out.= '</p>';
$out.= '<hr />';
$out.= '<p>'.sp_text('Our thanks to all the people who have aided, abetted, coded, suggested and helped test this plugin').'</p><br />';
$out.= sp_text('This forum is using the').' <strong>'.$theme['theme'].'</strong> '.sp_text('theme').'<br />';
$out.= '</div>';
echo $out;
die();
?>