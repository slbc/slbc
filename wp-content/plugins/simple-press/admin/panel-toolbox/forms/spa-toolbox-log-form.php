<?php
/*
Simple:Press
Admin Toolbox Uninstall Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_log_form() {
	$sflog = spa_get_log_data();

#== log Tab ==========================================================

	spa_paint_open_tab(spa_text('Toolbox')." - ".spa_text('Install Log'), true);

			if (!$sflog) {
				spa_etext("There are no Install Log Entries");
				return;
			}

			spa_paint_open_fieldset(spa_text('Install Log'), false, '', true);
				echo "<table class='form-table'><tr>";
				echo "<th>".spa_text('Version')."</th>";
				echo "<th>".spa_text('Build')."</th>";
				echo "<th>".spa_text('Release')."</th>";
				echo "<th>".spa_text('Installed')."</th>";
				echo "<th>".spa_text('By')."</th>";
				echo "</tr>";

				foreach ($sflog as $log) {
					$idVer = 'version'.str_replace('.', '', $log['version']);
					$idQVer = str_replace('.', '-', $log['version']);

					echo "<tr>";
					echo "<td class='sflabel'>".$log['version'];

				    $site = SFHOMEURL.'index.php?sp_ahah=install-log&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;log='.$idQVer;
					$gif = SFCOMMONIMAGES.'working.gif';
					echo '<input type="button" class="button button-highlighted sfalignright" value="'.spa_text('Details').'" onclick="spjLoadAhah(\''.$site.'\', \''.$idVer.'\', \''.$gif.'\');" />';

					echo "</td>";
					echo "<td class='sflabel'>".$log['build']."</td>";
					echo "<td class='sflabel'>".$log['release_type']."</td>";
					echo "<td class='sflabel'>".sp_date('d', $log['install_date'])."</td>";
					echo "<td class='sflabel'>".sp_filter_name_display($log['display_name'])."</td>";
					echo "</tr>";
					echo "<tr><td style='display:none;' class='sflabel' id='".$idVer."' colspan='5'></td></tr>";
				}
				echo '</table>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_toolbox_install_panel');
	spa_paint_close_tab();
}
?>