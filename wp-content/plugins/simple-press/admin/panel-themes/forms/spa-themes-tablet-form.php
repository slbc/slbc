<?php
/*
Simple:Press
Admin themes tablet
$LastChangedDate: 2013-08-05 21:11:02 +0100 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_tablet_form() {
	global $spPaths;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sftablettheme', 'sfreloadtablist');
});
</script>
<?php
    # get current theme
    $tabletTheme = sp_get_option('sp_tablet_theme');

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=themes-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=tablet';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sftablettheme" name="sftablettheme">
	<?php echo sp_create_nonce('forum-adminform_themes'); ?>
<?php
	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Tablet Theme Support').' - '.spa_text('Tablet Theme'));
		spa_paint_open_panel();

			spa_paint_spacer();
			echo '<div class="sfoptionerror">';
			echo spa_text('Themes Folder').': <b>wp-content/'.$spPaths['themes'].'</b>';
			echo '</div>';

//			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Tablet Support'), true, 'tablet-support');
					spa_paint_checkbox(spa_text('Enable tablet theme support'), 'active', $tabletTheme['active']);
				spa_paint_close_fieldset();

			spa_paint_close_panel();
			spa_paint_tab_right_cell();
			spa_paint_open_panel();

			    if ($tabletTheme['active']) {
				    require_once(ABSPATH . 'wp-admin/includes/template.php');
				    require_once(ABSPATH.'wp-admin/includes/theme.php');
					spa_paint_open_fieldset(spa_text('Tablet Display Options'), true, 'tablet-display');
						spa_paint_checkbox(spa_text('Use alternate WordPress template'), 'usetemplate', $tabletTheme['usetemplate']);
						spa_paint_select_start(spa_text('Alternate page template'), 'pagetemplate', 'pagetemplate');
						echo '<option value="default">'.spa_text('Default Template').'</option>';
						page_template_dropdown($tabletTheme['pagetemplate']);
						spa_paint_select_end();
						spa_paint_checkbox(spa_text('Remove Page Title Completely'), 'notitle', $tabletTheme['notitle']);
					spa_paint_close_fieldset();
				}

			spa_paint_close_panel();
//		spa_paint_close_panel();

		do_action('sph_themes_tablet_option_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Tablet Component'); ?>" />
	</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
    if ($tabletTheme['active']) {
        # get themes
    	$themes = sp_get_themes_list_data();

    	# get update version info
    	$xml = sp_load_version_xml();

    	spa_paint_open_tab(spa_text('Available Themes')." - ".spa_text('Select Simple:Press Tablet Theme'), true);
    	spa_paint_open_panel();
    	spa_paint_open_fieldset(spa_text('Tablet Theme Management'), true, 'themes', false);
    ?>
    	<h3><?php echo spa_text('Current Tablet Theme'); ?></h3>
    	<div id="current-theme" class="has-screenshot">
    		<img src="<?php echo SPTHEMEBASEURL.$tabletTheme['theme'].'/'.$themes[$tabletTheme['theme']]['Screenshot']; ?>" />
    		<h4>
    			<?php echo $themes[$tabletTheme['theme']]['Name'].' '.$themes[$tabletTheme['theme']]['Version'].' '.spa_text('by').' <a href="'.$themes[$tabletTheme['theme']]['AuthorURI'].'" title="'.spa_text('Visit author homepage').'">'.$themes[$tabletTheme['theme']]['Author'].'</a>'; ?>
    		</h4>
    		<p class="theme-description" style="padding: 0;">
    			<?php echo $themes[$tabletTheme['theme']]['Description']; ?>
    		</p>
    <?php
            //if (!empty($themes[$tabletTheme['theme']]['Colors'])) {
            $overlays = sp_get_overlays(SPTHEMEBASEDIR.$tabletTheme['theme'].'/styles/overlays');
            if (!empty($overlays)) {
    ?>
                <script type="text/javascript">
                jQuery(document).ready(function() {
                	jQuery('#sftheme-<?php echo esc_js($tabletTheme['theme']); ?>').ajaxForm({
                		target: '#sfmsgspot',
                		success: function() {
                            jQuery('#sfreloadtablist').click();
                			jQuery('#sfmsgspot').fadeIn();
                			jQuery('#sfmsgspot').fadeOut(6000);
                		}
                	});
                });
                </script>
                <br />
    <?php
                $ahahURL = SFHOMEURL."index.php?sp_ahah=themes-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=tablet";
            	echo '<form action="'.$ahahURL.'" method="post" id="sftheme-'.esc_attr($tabletTheme['theme']).'" name="sftheme-'.esc_attr($tabletTheme['theme']).'">';
    	            echo sp_create_nonce('forum-adminform_themes');
    	            echo '<input type="hidden" name="active" value="'.$tabletTheme['active'].'" />';
    	            echo '<input type="hidden" name="theme" value="'.esc_attr($tabletTheme['theme']).'" />';
    	            echo '<input type="hidden" name="style" value="'.esc_attr($themes[$tabletTheme['theme']]['Stylesheet']).'" />';
    	            $theme_colors = empty($themes[$tabletTheme['theme']]['Colors']) ? '' : explode(',', $themes[$tabletTheme['theme']]['Colors']);
    	        	echo '<input type="hidden" name="default-color" value="'.esc_attr($overlays[0]).'" />';
    				echo spa_text('Select Overlay').': ';
    				echo '<select name="color-'.esc_attr($tabletTheme['theme']).'">';
                	foreach ($overlays as $overlay) {
    	        		$overlay = trim($overlay);
        	    		$selected = ($tabletTheme['color'] == $overlay) ? ' selected="selected" ' : '';
    					echo '<option'.$selected.' value="'.esc_attr($overlay).'">'.esc_html($overlay).'</option>';
            		}
    				echo '</select> ';
    	            echo ' <input type="submit" class="button-secondary action" id="saveit-cur" name="saveit-cur" value="'.spa_text('Update Overlay').'" />';
                echo '</form>';
    		}

             	# any upgrade for this theme?  in multisite only main site can update
    			if (is_main_site() && $xml) {
    				foreach ($xml->themes->theme as $latest) {
    					if ($themes[$tabletTheme['theme']]['Name'] == $latest->name) {
    						if ((version_compare($latest->version, $themes[$tabletTheme['theme']]['Version'], '>') == 1)) {
    							echo '<br />';
    							echo '<p style="padding: 0;">';
    							echo '<strong>'.spa_text('There is an update for the').' '.$themes[$tabletTheme['theme']]['Name'].' '.spa_text('theme').'.</strong> ';
    							echo spa_text('Version').' '.$latest->version.' '.spa_text('is available').'. ';
    							echo spa_text('For details and to download please visit').' '.SFPLUGHOME.' '.spa_text('or').' '.spa_text('go to the').' ';
    							echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.spa_text('WordPress updates page').'</a>';
    							echo '</p>';
    						}
    						break;
    					}
    				}
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
    	    		if ($theme_file == $tabletTheme['theme']) continue;

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
    	                    	spjAjaxForm('sftheme-<?php echo esc_js($theme_file); ?>', 'sfreloadtablist');
    	                    });
    	                    </script>
    	                    <?php $ahahURL = SFHOMEURL."index.php?sp_ahah=themes-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=tablet"; ?>
    	                	<form action="<?php echo $ahahURL; ?>" method="post" id="sftheme-<?php echo esc_attr($theme_file); ?>" name="sftheme-<?php echo esc_attr($theme_file);  ?>">
    	                    <?php echo sp_create_nonce('forum-adminform_themes'); ?>
                            <input type="hidden" name="active" value="<?php echo $tabletTheme['active']; ?>" />
    	                    <input type="hidden" name="theme" value="<?php echo esc_attr($theme_file); ?>" />
    	                    <input type="hidden" name="style" value="<?php echo esc_attr($theme_style); ?>" />
                            <?php $defOverlay = (!empty($theme_overlays)) ? esc_attr($theme_overlays[0]) : 0; ?>
    			        	<input type="hidden" name="default-color" value="<?php echo esc_attr($defOverlay); ?>" />
    	<?php
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
    	                    <input type="submit" class="button-secondary action" id="saveit-<?php echo esc_attr($theme_file); ?>" name="saveit-<?php echo esc_attr($theme_file); ?>" value="<?php echo spa_etext('Activate Tablet Theme'); ?>" />
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
    	do_action('sph_themes_tablet_list_panel');

    	spa_paint_close_fieldset(false);
    	spa_paint_close_panel();
    	spa_paint_close_tab();
    }
}
?>