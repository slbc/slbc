<?php
/*
Simple:Press
Admin plugins list
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_plugins_list_form() {
	global $spPaths;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sppluginsform', 'sfreloadpl');
});
</script>
<?php
    # get plugins
	$plugins = spa_get_plugins_list_data();

	# get update version info
	$xml = sp_load_version_xml();

    # check active plugins
    $invalid = sp_validate_active_plugins();
    if (!empty($invalid)) {
        foreach ($invalid as $plugin_file => $error) {
    		echo '<div id="message" class="error"><p>'.sprintf(spa_text('The plugin %1$s has been deactivated due to error: %2$s'), esc_html($plugin_file), $error->get_error_message()).'</p></div>';
        }
    }

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=plugins-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=list';
	$msg = esc_js(spa_text('Are you sure you want to delete the selected Simple Press plugins?'));
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sppluginsform" name="sppluginsform" onsubmit="javascript: if (ActionType.options[ActionType.selectedIndex].value == 'delete-selected' || ActionType2.options[ActionType2.selectedIndex].value == 'delete-selected') {if(confirm('<?php echo $msg; ?>')) {return true;} else {return false;}} else {return true;}">
	<?php echo sp_create_nonce('forum-adminform_plugins'); ?>
<?php
	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Available Plugins')." - ".spa_text('Install Simple:Press Plugins'), true);
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sfoptionerror">';
	echo spa_text('Plugins Folder').': <b>wp-content/'.$spPaths['plugins'].'</b>';
	echo '</div>';

	spa_paint_open_fieldset(spa_text('Plugin Management'), true, 'plugins', false);
?>
	<div class="tablenav top">
		<div class="alignleft actions">
			<select id="ActionType" name="action">
				<option selected="selected" value="-1"><?php echo spa_text('Bulk Actions'); ?></option>
				<option value="activate-selected"><?php echo spa_text('Activate'); ?></option>
				<option value="deactivate-selected"><?php echo spa_text('Deactivate'); ?></option>
				<?php if (!is_multisite() || is_super_admin()) { ?><option value="delete-selected"><?php echo spa_text('Delete'); ?></option><?php }?>
			</select>
			<input id="doaction" class="button-secondary action" type="submit" value="<?php echo spa_text('Apply'); ?>" />
		</div>
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count($plugins).' '.spa_text('plugins');?></span>
		</div>
	</div>

	<table class="wp-list-table widefat plugins">
        <thead>
		<tr>
			<th id='cb' class='manage-column column-cb check-column' scope='column'>
				<input type="checkbox">
			</th>
			<th>
			</th>
			<th>
				<?php spa_etext('Plugin'); ?>
			</th>
			<th>
				<?php spa_etext('Description'); ?>
			</th>
		</tr>
        </thead>

        <tfoot>
		<tr>
			<th id='cb' class='manage-column column-cb check-column' scope='column'>
				<input type="checkbox">
			</th>
			<th>
			</th>
			<th>
				<?php spa_etext('Plugin'); ?>
			</th>
			<th>
				<?php spa_etext('Description'); ?>
			</th>
		</tr>
        </tfoot>

        <tbody class="the-list">
<?php
        if (empty($plugins)) echo '<tr><td colspan="2">'.spa_text('No plugins found.').'</td></tr>';

    	foreach ((array)$plugins as $plugin_file => $plugin_data) {
    		$is_active = sp_is_plugin_active($plugin_file);
            if ($is_active) {
                $url = SFADMINPLUGINS.'&amp;action=deactivate&amp;plugin='.esc_attr($plugin_file).'&amp;title='.esc_attr($plugin_data['Name']).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
                $actionlink = "<a href='".$url."' title='".spa_text('Deactivate this Plugin')."'>".spa_text('Deactivate')."</a>";
				$actionlink = apply_filters('sph_plugins_active_buttons', $actionlink, $plugin_file);
				$rowClass = 'active';
                $icon = '<img src="'.SFADMINIMAGES.'sp_Yes.png" title="'.spa_text('Plugin activated').'" alt="" style="vertical-align: middle;" />';
            } else {
                $url = SFADMINPLUGINS.'&amp;action=activate&amp;plugin='.esc_attr($plugin_file).'&amp;title='.esc_attr($plugin_data['Name']).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
                $actionlink = "<a href='".$url."' title='".spa_text('Activate this Plugin')."'>".spa_text('Activate')."</a>";
                $url = SFADMINPLUGINS.'&amp;action=delete&amp;plugin='.esc_attr($plugin_file).'&amp;title='.esc_attr($plugin_data['Name']).'&amp;sfnonce='.wp_create_nonce('forum-adminform_plugins');
        		$msg = esc_js(spa_text('Are you sure you want to delete this Simple Press plugin?'));
				if (!is_multisite() || is_super_admin()) {
                    $actionlink.= ' | <a href="javascript: if(confirm(\''.$msg.'\')) {window.location=\''.$url.'\';}" title="'.spa_text('Delete this Plugin').'">'.spa_text('Delete').'</a>';
                }
				$actionlink = apply_filters('sph_plugins_inactive_buttons', $actionlink, $plugin_file);
				$rowClass = 'inactive';
                $icon = '<img src="'.SFADMINIMAGES.'sp_No.png" title="'.spa_text('Plugin not activated').'" alt="" style="vertical-align: middle;" />';
            }

    		$description = $plugin_data['Description'];
    		$plugin_name = $plugin_data['Name'];
?>
        	<tr class='<?php echo $rowClass; ?>'>
        		<th class='check-column' scope='row'>
					<input id="checkbox_<?php echo rand(1, 9999999999); ?>" type="checkbox" value="<?php echo $plugin_file; ?>" name="checked[]" />
				</th>
				<td class='check-column'>
                	<?php echo $icon; ?>
				</td>
        		<td class='plugin-title'>
					<strong><?php echo esc_html($plugin_name); ?></strong>
	        		<div class="row-actions-visible">
    	   				<span><?php echo str_replace("&nbsp;&nbsp;", "  |  ", $actionlink); ?></span>
        			</div>
				</td>
        		<td class='column-description desc'>
        			<div class='plugin-description'>
						<?php echo esc_html($description); ?>
					</div>
					<div class='<?php echo $rowClass; ?> second plugin-version-author-uri'>
<?php
		        		$plugin_meta = array();
		        		if (!empty($plugin_data['Version'])) $plugin_meta[] = sprintf(spa_text('Version %s'), $plugin_data['Version']);
		        		if (!empty($plugin_data['Author'])) {
		        			$author = $plugin_data['Author'];
		        			if (!empty($plugin_data['AuthorURI'])) $author = '<a href="'.esc_url($plugin_data['AuthorURI']).'" title="'.spa_text('Visit author homepage').'">'.esc_html($plugin_data['Author']).'</a>';
		        			$plugin_meta[] = sprintf(spa_text('By %s'), $author);
		        		}
		        		if (!empty($plugin_data['PluginURI'])) $plugin_meta[] = '<a href="'.esc_url($plugin_data['PluginURI']).'" title="'.spa_text('Visit plugin site').'">'.esc_html(spa_text('Visit plugin site')).'</a>';

		        		echo implode(' | ', $plugin_meta);
?>
					</div>
				</td>
        	</tr>
<?php
        	# any upgrade for this plugin?  in multisite only main site can update
			if (is_main_site() && $xml) {
				foreach ($xml->plugins->plugin as $latest) {
					if ($plugin_data['Name'] == $latest->name) {
						if ((version_compare($latest->version, $plugin_data['Version'], '>') == 1)) {
?>
							<tr class="plugin-update-tr">
								<td class="plugin-update colspanchange" colspan="3">
									<div class="update-message">
										<?php echo spa_text('There is an update for the').' '.$plugin_data['Name'].' '.spa_text('plugin').'. '; ?>
										<?php echo spa_text('Version').' '.$latest->version.' '.spa_text('is available').'. '; ?>
										<?php echo spa_text('For details and to download please visit').' '.SFPLUGHOME.' '.spa_text('or').' '.spa_text('go to the').' '; ?>
										<?php echo '<a href="'.self_admin_url('update-core.php').'" title="" target="_parent">'.spa_text('WordPress updates page').'</a>'; ?>
									</div>
								</td>
							</tr>
<?php
						}
						break;
					}
				}
			}
         }
		do_action('sph_plugins_list_panel');
?>
        </tbody>
    </table>

	<div class="tablenav bottom">
		<div class="alignleft actions">
			<select id="ActionType2" name="action2">
				<option selected="selected" value="-1"><?php echo spa_text('Bulk Actions'); ?></option>
				<option value="activate-selected"><?php echo spa_text('Activate'); ?></option>
				<option value="deactivate-selected"><?php echo spa_text('Deactivate'); ?></option>
				<?php if (!is_multisite() || is_super_admin()) { ?><option value="delete-selected"><?php echo spa_text('Delete'); ?></option><?php }?>
			</select>
			<input id="doaction" class="button-secondary action" type="submit" value="<?php echo spa_text('Apply'); ?>" name="" />
		</div>
		<div class="tablenav-pages one-page">
			<span class="displaying-num"><?php echo count($plugins).' '.spa_text('plugins');?></span>
		</div>
	</div>
	</form>
<?php
	spa_paint_close_fieldset(false);
	spa_paint_close_panel();
	spa_paint_close_tab();
}

?>