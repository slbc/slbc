<?php
/*
Simple:Press
Admin Components Special Ranks Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_special_rankings_form($rankings) {
	global $tab, $spPaths;

	echo '<table class="form-table"><tr>';
	echo '<th width="65%">';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfaddspecialrank', 'sfreloadfr');
});
</script>
<?php
	echo '<table width="100%" cellspacing="0">';
	echo '<tr>';
	echo '<th style="padding:1px; text-align:center;width:50%;">'.spa_text('Special Rank Name').'</th>';
	echo '<th style="padding:1px; text-align:center;width:30%;">'.spa_text('Special Rank Badge').'</th>';
	echo '<th style="padding:1px; text-align:center;width:20%;">&nbsp;</th>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '<th style="text-align:center;width:30%;">'.spa_text('Special Rank Members').'</th>';
	echo '<th style="text-align:center;width:5%;">'.spa_text('Remove').'</th>';
	echo '</tr>'."\n";

	# display rankings info
	if ($rankings) {
		foreach ($rankings as $rank) {
			echo '<tr>';
			echo '<td width="100%" colspan="4">';
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfspecialrankupdate<?php echo $rank['meta_id']; ?>', '');
});
</script>
<?php
			echo '<div id="srank'.$rank['meta_id'].'">';
			echo '<table width="100%" cellspacing="0">';
			echo '<tr>';
			echo '<td width="65%">';
            $ahahURL = SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=specialranks&amp;action=updaterank&amp;id='.$rank['meta_id'];
?>
			<form action="<?php echo $ahahURL; ?>" method="post" id="sfspecialrankupdate<?php echo $rank['meta_id']; ?>" name="sfspecialrankupdate<?php echo $rank['meta_id']; ?>">
<?php
			echo sp_create_nonce('special-rank-update');
			echo '<table width="100%">';
			echo '<tr>';
			echo '<td width="50%">';
			echo '<input type="text" class="sfpostcontrol" size="20" tabindex="'.$tab.'" name="specialrankdesc['.$rank['meta_id'].']" value="'.$rank['meta_key'].'" />';
			echo '</td>';
			echo '<td width="30%" align="center">';
			spa_select_icon_dropdown('specialrankbadge['.$rank['meta_id'].']', spa_text('Select Badge'), SF_STORE_DIR.'/'.$spPaths['ranks'].'/', $rank['meta_value']['badge']);
			echo '</td>';
			echo '<td width="20%" align="center">';
			echo '<input type="submit" class="sfform-submit-button" id="updatespecialrank'.$rank["meta_id"].'" name="updatespecialrank'.$rank["meta_id"].'" value="'.spa_text('Update Rank').'" />';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</form>';
			echo '</td>';
			echo '<td width="30%" align="center" style="vertical-align:middle">';
            $loc = '#sfrankshow-'.$rank['meta_id'];
            $loc2 = 'sfrankshow-'.$rank['meta_id'];
            $site = SFHOMEURL.'index.php?sp_ahah=components&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;action=show&amp;key='.$rank['meta_id'];
			$gif= SFCOMMONIMAGES."working.gif";
			$text = esc_js(spa_text('Show/Hide'));
?>
			<input type="button" id="show<?php echo $rank['meta_id']; ?>" class="button button-highlighted" value="<?php echo $text; ?>" onclick="spjToggleLayer('<?php echo $loc2; ?>');spjShowMemberList('<?php echo $site; ?>', '<?php echo $gif; ?>', '<?php echo $rank['meta_id']; ?>');" />
<?php
            $base = SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah');
			$target = 'members-'.$rank['meta_id'];
			$image = SFADMINIMAGES;
?>
			<input type="button" id="add<?php echo $rank['meta_id']; ?>" class="button button-highlighted" value="<?php spa_etext('Add'); ?>" onclick="jQuery('<?php echo $loc; ?>').show();spjLoadForm('addmembers', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $rank['meta_id']; ?>', 'open'); " />
			<input type="button" id="remove<?php echo $rank['meta_id']; ?>" class="button button-highlighted" value="<?php spa_etext('Remove'); ?>" onclick="jQuery('<?php echo $loc; ?>').show();spjLoadForm('delmembers', '<?php echo $base; ?>', '<?php echo $target; ?>', '<?php echo $image; ?>', '<?php echo $rank['meta_id']; ?>', 'open'); " />
<?php
			$tab++;
			echo '</td>';
			echo '<td class="sflabel" align="center" width="5%">';
            $site = SFHOMEURL.'index.php?sp_ahah=components&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;action=del_specialrank&amp;key='.$rank['meta_id'];
?>
			<img onclick="spjDelRow('<?php echo $site; ?>', 'srank<?php echo $rank['meta_id']; ?>');" src="<?php echo SFCOMMONIMAGES; ?>delete.png" title="<?php spa_etext('Delete Special Rank'); ?>" alt="" />
<?php
			echo '</td>';
			echo '</tr>';
			echo '<tr class="inline_edit" id="sfrankshow-'.$rank["meta_id"].'">';
			echo '<td colspan="4">';
            echo '<div id="members-'.$rank["meta_id"].'"></div>';
		    echo '</td>';
			echo '</tr>';
			echo '</table>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
		}
	}

	# always have one empty slot available for new rank
	echo '<tr>';
	echo '<td align="right" width="100%" colspan="3">';
    $ahahURL = SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=specialranks&amp;action=newrank';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" name="sfaddspecialrank" id="sfaddspecialrank">
<?php
	echo sp_create_nonce('special-rank-new');
	$tab++;
	echo '<table>';
	echo '<tr>';
	echo '<td>';
	echo '<input type="text" class="sfpostcontrol" size="25" tabindex="'.$tab.'" name="specialrank" value="" />';
	echo '</td>';
	echo '<td>';
	echo '<input type="submit" class="sfform-submit-button" id="addspecialrank" name="addspecialrank" value="'.spa_text('Add Special Rank').'" />';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '</td>';
	echo '</tr></table>';
}
?>