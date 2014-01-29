<?php
/*
Simple:Press
Admin Toolbox Changelog Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_changelog_form() {

#== CHANGELOG Tab ==========================================================

	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Toolbox').' - '.spa_text('Change Log'), true);
		spa_paint_open_panel();
    		spa_paint_open_fieldset(spa_text('Change Log'), false, '', false);

    		# Display current change log if available or passed log
    		if (isset($_POST['clselect'])) {
    			$current = $_POST['clselect'];
    			$cFile = 'change-log-'.$_POST['clselect'].'.txt';
    		} else {
    			$cFile = 'change-log-'.SPBUILD.'.txt';
    			$current = 0;
    		}

    		echo '<div id="sp-changelog-data">';
    		$c = wp_remote_get('http://simple-press.com/downloads/simple-press/changelogs/'.$cFile);
            if (is_wp_error($c) || wp_remote_retrieve_response_code($c) != 200) {
    			$b = substr($cFile, 11);
    			$b = str_replace('.txt', '', $b);
    			echo '<p>'.sprintf(spa_text('No change log file found for build %s'), $b).'</p>';
    		} else {
    			echo wpautop($c['body']);
    		}
    		echo '</div>';

    		spa_paint_close_fieldset(false);
		spa_paint_close_panel();

		$c = wp_remote_get('http://simple-press.com/downloads/simple-press/changelogs/log-index.xml');
        if (is_wp_error($c) || wp_remote_retrieve_response_code($c) != 200) {
   			echo '<p>'.spa_text('Unable to communicate with Simple Press server').'</p>';
        } else {
			$l = new SimpleXMLElement($c['body']);
			if (!empty($l)) {
        		spa_paint_open_panel();
    				spa_paint_open_fieldset(spa_text('Review Change Logs'), false, '', false);
    				echo '<div id="sp-changelog-list">';
    				echo '<form name="loadchangelog" method="post" action="admin.php?page=simple-press/admin/panel-toolbox/spa-toolbox.php&amp;tab=changelog">';
    				echo '<select name="clselect" class="sfcontrol" style="vertical-align:middle;font-weight: normal;">';
    				foreach ($l->log as $i=>$x) {
    					if($x->build==$current ? $s=" selected='selected' " : $s="");
    					echo "<option value='".$x->build."' $s>".$x->build." (".$x->version.") - ".$x->date."</option>";
    				}
    				echo '</select>';
    				echo '<input type="submit" class="button-primary" id="gochangelog" name="gochangelog" value="'.spa_text('Display Change Log').'" />';
    				echo '</form>';
    				echo '</div>';
    				spa_paint_close_fieldset(false);
        		spa_paint_close_panel();
			}
		}

		do_action('sph_toolbox_changelog_panel');
	spa_paint_close_tab();
}
?>