<?php
/*
Simple:Press
Admin themes list
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_list_form() {
	global $spPaths;
    # get current theme
    $curTheme = sp_get_option('sp_current_theme');

    # get themes
	$themes = sp_get_themes_list_data();

	# get update version info
	$xml = sp_load_version_xml();

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Available Themes')." - ".spa_text('Select Simple:Press Theme'), true);
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sfoptionerror">';
	echo spa_text('Themes Folder').': <b>wp-content/'.$spPaths['themes'].'</b>';
	echo '</div>';

	spa_paint_open_fieldset(spa_text('Theme Management'), true, 'themes', false);
?>
	<h3><?php echo spa_text('Current Theme'); ?></h3>
	<div id="current-theme" class="has-screenshot">
<?php
        if (file_exists(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/'.$curTheme['style'])) {
?>
    		<img src="<?php echo SPTHEMEBASEURL.$curTheme['theme'].'/'.$themes[$curTheme['theme']]['Screenshot']; ?>" />
    		<h4>
    			<?php echo $themes[$curTheme['theme']]['Name'].' '.$themes[$curTheme['theme']]['Version'].' '.spa_text('by').' <a href="'.$themes[$curTheme['theme']]['AuthorURI'].'" title="'.spa_text('Visit author homepage').'">'.$themes[$curTheme['theme']]['Author'].'</a>'; ?>
    		</h4>
    		<p class="theme-description" style="padding: 0;">
    			<?php echo $themes[$curTheme['theme']]['Description']; ?>
    		</p>
<?php
            $overlays = sp_get_overlays(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/overlays');
            if (!empty($overlays)) {
?>
                <script type="text/javascript">
                jQuery(document).ready(function() {
					spjAjaxForm('sftheme-<?php echo esc_js($curTheme['theme']); ?>', 'sfreloadtlist');
                });
                </script>
                <br />
<?php
                $ahahURL = SFHOMEURL."index.php?sp_ahah=themes-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=theme";
            	echo '<form action="'.$ahahURL.'" method="post" id="sftheme-'.esc_attr($curTheme['theme']).'" name="sftheme-'.esc_attr($curTheme['theme']).'">';
                echo sp_create_nonce('forum-adminform_themes');
                echo '<input type="hidden" name="theme" value="'.esc_attr($curTheme['theme']).'" />';
                echo '<input type="hidden" name="style" value="'.esc_attr($themes[$curTheme['theme']]['Stylesheet']).'" />';

            	echo '<input type="hidden" name="default-color" value="'.esc_attr($overlays[0]).'" />';
    			echo spa_text('Select Overlay').': ';
    			echo '<select name="color-'.esc_attr($curTheme['theme']).'">';
            	foreach ($overlays as $overlay) {
            		$overlay = trim($overlay);
    	    		$selected = ($curTheme['color'] == $overlay) ? ' selected="selected" ' : '';
    				echo '<option'.$selected.' value="'.esc_attr($overlay).'">'.esc_html($overlay).'</option>';
        		}
    			echo '</select> ';
                echo ' <input type="submit" class="button-secondary action" id="update" name="update" value="'.spa_text('Update Overlay').'" />';
                echo '</form>';
    		}

         	# any upgrade for this theme? in multisite only main site can update
    		if (is_main_site() && $xml) {
    			foreach ($xml->themes->theme as $latest) {
    				if ($themes[$curTheme['theme']]['Name'] == $latest->name) {
    					if ((version_compare($latest->version, $themes[$curTheme['theme']]['Version'], '>') == 1)) {
    						echo '<br />';
    						echo '<p style="padding: 0;">';
    						echo '<strong>'.spa_text('There is an update for the').' '.$themes[$curTheme['theme']]['Name'].' '.spa_text('theme').'.</strong> ';
    						echo spa_text('Version').' '.$latest->version.' '.spa_text('is available').'. ';
    						echo spa_text('For details and to download please visit').' '.SFPLUGHOME.' '.spa_text('or').' '.spa_text('go to the').' ';
    						echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.spa_text('WordPress updates page').'</a>';
    						echo '</p>';
    					}
    					break;
    				}
    			}
    		}
        } else {
     		echo '<h4>'.spa_text('The current theme stylesheet').':<br /><br />'.SPTHEMEBASEDIR.$curTheme['theme'].'/styles/'.$curTheme['style'].'<br /><br />'.spa_text('cannot be found. Please correct or select a new theme for proper operation.').'</h4>';
        }
?>
	</div>

	<br class="clear" />

	<h3><?php echo spa_text('Available Themes'); ?></h3>
<?php
	$numThemes = count($themes);
 	if ($numThemes > 1) {
?>
		<table id="availablethemes" cellspacing="0" cellpadding="0">
			<tbody id="the-list" class="list-themes">
<?php
			$curCol = 1;
	    	foreach ((array)$themes as $theme_file => $theme_data) {
   				# skip cur theme
	    		if ($theme_file == $curTheme['theme']) continue;

	    		$theme_desc = $theme_data['Description'];
	    		$theme_name = $theme_data['Name'];
	    		$theme_version = $theme_data['Version'];
	    		$theme_author = $theme_data['Author'];
	    		$theme_uri = $theme_data['AuthorURI'];
	    		$theme_style = $theme_data['Stylesheet'];
	            $theme_image = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
                $theme_overlays = sp_get_overlays(SPTHEMEBASEDIR.$theme_file.'/styles/overlays');
?>
	            <?php if ($curCol == 1) { ?>
	            	<tr>
					<td class="available-theme top left">
				<?php } ?>
	            <?php if ($curCol == 2) { ?>
					<td class="available-theme top">
				<?php } ?>
	            <?php if ($curCol == 3) { ?>
					<td class="available-theme top right">
				<?php } ?>

					<img alt="" src="<?php echo $theme_image; ?>" />
					<h4>
						<?php echo $theme_name.' '.$theme_version.' '.spa_text('by').' <a href="'.$theme_uri.'" title="'.spa_text('Visit author homepage').'">'.$theme_author.'</a>'; ?>
					</h4>
					<p class="description" style="padding: 0;">
						<?php echo $theme_desc; ?>
					</p>
					<br />
					<span class="action-links">
	                    <script type="text/javascript">
	                    jQuery(document).ready(function() {
	                    	spjAjaxForm('sftheme-<?php echo esc_js($theme_file); ?>', 'sfreloadtlist');
	                    });
	                    </script>
	                    <?php $ahahURL = SFHOMEURL."index.php?sp_ahah=themes-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=theme"; ?>
                        <?php $msg = esc_js(spa_text('Are you sure you want to delete this Simple Press theme?')); ?>
	                	<form action="<?php echo $ahahURL; ?>" method="post" id="sftheme-<?php echo esc_attr($theme_file); ?>" name="sftheme-<?php echo esc_attr($theme_file); ?>" >
	                    <?php echo sp_create_nonce('forum-adminform_themes'); ?>
	                    <input type="hidden" name="theme" value="<?php echo esc_attr($theme_file); ?>" />
	                    <input type="hidden" name="style" value="<?php echo esc_attr($theme_style); ?>" />
<?php
                        $defOverlay = (!empty($theme_overlays)) ? esc_attr($theme_overlays[0]) : 0;
			        	echo "<input type='hidden' name='default-color' value='$defOverlay' />";
						if ($theme_overlays) {
							echo spa_text('Select Overlay').': ';
							echo ' <select name="color-'.esc_attr($theme_file).'" style="margin-bottom:5px;">';
			            	foreach ($theme_overlays as $theme_overlay) {
				        		$theme_overlay = trim($theme_overlay);
			    	    		$selected = ($theme_overlays[0] == $theme_overlay) ? ' selected="selected" ' : '';
								echo '<option'.$selected.' value="'.esc_attr($theme_overlay).'">'.esc_html($theme_overlay).'</option>';
			        		}
							echo '</select> ';
						}
?>
	                    <input type="submit" class="button-secondary action" id="activate-<?php echo esc_attr($theme_file); ?>" name="activate" value="<?php echo spa_etext('Activate Theme'); ?>" />
	                    <?php if (!is_multisite() || is_super_admin()) { ?><input type="submit" class="button-secondary action" id="delete-<?php echo esc_attr($theme_file); ?>" name="delete" value="<?php echo spa_etext('Delete Theme'); ?>" onclick="javascript: if(confirm('<?php echo $msg; ?>')) {return true;} else {return false;}" /><?php }?>
	                    </form>
					</span>
<?php
		         	# any upgrade for this theme?
					if ($xml) {
						foreach ($xml->themes->theme as $latest) {
							if ($theme_data['Name'] == $latest->name) {
								if ((version_compare($latest->version, $theme_data['Version'], '>') == 1)) {
									echo '<br />';
									echo '<p style="padding: 0;">';
									echo '<strong>'.spa_text('There is an update for the').' '.$theme_data['Name'].' '.spa_text('theme').'.</strong> ';
									echo spa_text('Version').' '.$latest->version.' '.spa_text('is available').'. ';
									echo spa_text('For details and to download please visit').' '.SFPLUGHOME.' '.spa_text('or').' '.spa_text('go to the').' ';
									echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.spa_text('WordPress updates page').'</a>';
									echo '</p>';
								}
								break;
							}
						}
					}

				echo '</td>';

				$curCol++;
				if ($curCol == 4) {
					echo '</tr>';
					$curCol = 1;
				}
       		}
       		if ($curCol == 2) echo '<td class="available-theme top"></td><td class="available-theme top right"></td></tr>';
       		if ($curCol == 3) echo '<td class="available-theme top right"></td></tr>';
        	echo '</tbody>';
    	echo '</table>';
 	} else {
 		echo spa_text('No other available themes found');
	}
	do_action('sph_themes_list_panel');

	spa_paint_close_fieldset(false);
	spa_paint_close_panel();
	spa_paint_close_tab();
}

?>