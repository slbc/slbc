<?php
/*
Simple:Press
DESC: Admin functions for core, theme and plugin updates
$LastChangedDate: 2013-09-24 07:18:02 -0700 (Tue, 24 Sep 2013) $
$Rev: 10733 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_load_version_xml() {
    $url = 'http://simple-press.com/downloads/simple-press/simple-press.xml';
	$options = array(
		'timeout' => 5,
	);
	$response = wp_remote_get($url, $options);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) return '';
    $body = wp_remote_retrieve_body($response);
    if (!$body) return '';
	$xml = new SimpleXMLElement($body);
	return $xml;
}

# check sp core version
function sp_update_check_sp_version() {
	$xml = sp_load_version_xml();
	if ($xml) {
		$installed_version = sp_get_option('sfversion');
		$installed_build = sp_get_option('sfbuild');
		if (empty($installed_build)) return;
		if ($xml->core->build > $installed_build) {
			$up = get_site_transient('update_plugins');
			$data = new stdClass;
			$data->slug = 'simple-press';
			$data->new_version = (string) $xml->core->version.' Build '.(string) $xml->core->build;
			$data->new_build = (string) $xml->core->build;
			$data->url = 'http://simple-press.com';
			$data->package = (string) $xml->core->archive;
			$up->response['simple-press/sp-control.php'] = $data;
			set_site_transient('update_plugins', $up);
		}
	}
}

function sp_core_plugin_info($res, $action, $args) {
    if ($action == 'plugin_information' && $args->slug == 'simple-press') {
        $xml = sp_load_version_xml();
        $res = new stdClass;
        $res->name = 'Simple:Press';
        $res->slug = $args->slug;
        $res->external = 1;
        $res->version = (string) $xml->core->version.' Build '.(string) $xml->core->build;
        $res->author = '<a href="http://simple-press.com/about" />Andy Staines and Steve Klasen</a>';
        $res->requires = (string) $xml->core->requires;
        $res->tested = (string) $xml->core->compatible;
        $res->homepage = 'http://simple-press.com';
        $res->sections['description'] = (string) $xml->core->description;
        $res->sections['changelog'] = (string) $xml->core->changelog;
        $res->download_link = (string) $xml->core->archive;
    }
	return $res;
}

function sp_plugins_check_sp_version($plugin) {
	global $spStatus;

	if ($plugin == 'simple-press/sp-control.php') {
		$msg = '';

		# get wp admin screen type
		$screen = get_current_screen();

		if (sp_get_option('sfuninstall')) {
			if (!$screen->is_network) $msg = '<p style="color:red;">'.spa_text('Simple:Press is READY TO BE REMOVED. When you deactivate it ALL DATA WILL BE DELETED').'</p>';

		} elseif ($spStatus == 'Upgrade') {
			if (!$screen->is_network) $msg = spa_text('Select the forum menu to complete the upgrade of your Simple:Press');
		} else {
			$xml = sp_load_version_xml();
			if (empty($xml)) return;

			$installed_version = sp_get_option('sfversion');
    		$installed_build = sp_get_option('sfbuild');
    		if (empty($installed_build)) return;
    		if ($xml->core->build > $installed_build) {
				$msg = spa_text('Latest version available').': <strong>'.$xml->core->version.' '.spa_text('Build').': '.$xml->core->build.'</strong> - '.$xml->core->message.'<br />';
				$msg.= spa_text('For details and to download please visit').' '.SFPLUGHOME.' '.spa_text('or');
				$msg.= ' <a href="'.wp_nonce_url(self_admin_url('update-core.php?action=do-plugin-upgrade&amp;plugins=simple-press/sp-control.php'), 'upgrade-core').'" title="" target="_parent">'.spa_text('update automatically').'</a>';
			}
		}

		if ($msg) echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">'.$msg.'</div></td></tr>';
	}
}

function sp_update_check_sp_plugins() {
	global $spStatus;
	if ($spStatus != 'ok') return;

	$xml = sp_load_version_xml();
	if ($xml) {
		$plugins = sp_get_plugins();
		if (empty($plugins)) return;

        $up = new stdClass;
        $update = false;
		$header = true;
		foreach ($plugins as $file => $installed) {
			foreach ($xml->plugins->plugin as $latest) {
				if ($installed['Name'] == $latest->name) {
					if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
						if ($header) {
							$form_action = 'update-core.php?action=do-sp-plugin-upgrade';
							?>
							<h3><?php spa_etext('Simple:Press Plugins'); ?></h3>
							<p><?php spa_etext( 'The following plugins have new versions available. Check the ones you want to update and then click Update SP Plugin'); ?></p>
							<p><?php spa_etext('Please Note: Any customizations you have made to plugin files will be lost'); ?></p>
							<form method="post" action="<?php echo $form_action; ?>" name="upgrade-sp-plugins" class="upgrade">
							<?php wp_nonce_field('upgrade-core'); ?>
							<p><input id="upgrade-themes" class="button" type="submit" value="<?php spa_etext('Update SP Plugins'); ?>" name="upgrade" /></p>
							<table class="widefat" cellspacing="0" id="update-themes-table">
								<thead>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all"><?php spa_etext('Select All'); ?></label></th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-2" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-2"><?php spa_etext('Select All'); ?></label></th>
								</tr>
								</tfoot>
								<tbody class="plugins">
							<?php
							$header = false;
						}
						echo "
							<tr class='active'>
							<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($file)."' /></th>
							<td><strong>{$installed['Name']}</strong><br />".sprintf(spa_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $installed['Version'], $latest->version, $latest->requires)."</td>
							</tr>";
						$data = new stdClass;
						$data->slug = $file;
						$data->new_version = (string) $latest->version;
						$data->url = 'http://simple-press.com';
						$data->package = ((string) $latest->archive).'&wpupdate=1';
						$up->response[$file] = $data;
                        $update = true;
					}
				}
			}
		}

        # any plugins to update?
        if ($update) {
            set_site_transient('sp_update_plugins', $up);
        } else {
            delete_site_transient('sp_update_plugins');
        }

		if (!$header) {
			?>
				</tbody>
			</table>
			<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php spa_etext('Update SP Plugins'); ?>" name="upgrade" /></p>
			</form>
			<?php
		} else {
			echo '<h3>'.spa_text('Simple:Press Plugins').'</h3>';
			echo '<p>'.spa_text('Your SP plugins are all up to date').'</p>';
		}
	}
}

function sp_update_check_sp_themes() {
	global $spStatus;
	if ($spStatus != 'ok') return;

	$xml = sp_load_version_xml();
	if ($xml) {
		include_once (SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php');
		$themes = sp_get_themes();
		if (empty($themes)) return;

        $up = new stdClass;
        $update = false;
		$header = true;
		foreach ($themes as $file => $installed) {
			foreach ($xml->themes->theme as $latest) {
				if ($installed['Name'] == $latest->name) {
					if ((version_compare($latest->version, $installed['Version'], '>') == 1)) {
						if ($header) {
							$form_action = 'update-core.php?action=do-sp-theme-upgrade';
							?>
							<h3><?php spa_etext('Simple:Press Themes'); ?></h3>
							<p><?php spa_etext( 'The following themes have new versions available. Check the ones you want to update and then click Update Themes.'); ?></p>
							<p><?php echo '<b>'.spa_text('Please Note:').'</b> '.spa_text('Any customizations you have made to theme files will be lost.'); ?></p>
							<form method="post" action="<?php echo $form_action; ?>" name="upgrade-themes" class="upgrade">
							<?php wp_nonce_field('upgrade-core'); ?>
							<p><input id="upgrade-themes" class="button" type="submit" value="<?php spa_etext('Update SP Themes'); ?>" name="upgrade" /></p>
							<table class="widefat" cellspacing="0" id="update-themes-table">
								<thead>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all"><?php spa_etext('Select All'); ?></label></th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<th scope="col" class="manage-column check-column"><input type="checkbox" id="themes-select-all-2" /></th>
									<th scope="col" class="manage-column"><label for="themes-select-all-2"><?php spa_etext('Select All'); ?></label></th>
								</tr>
								</tfoot>
								<tbody class="plugins">
							<?php
							$header = false;
						}
						$screenshot = SPTHEMEBASEURL.$file.'/'.$installed['Screenshot'];
						echo "
							<tr class='active'>
							<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='".esc_attr($file)."' /></th>
							<td class='plugin-title'><img src='$screenshot' width='64' height='64' style='float:left; padding: 5px' /><strong>{$installed['Name']}</strong>".sprintf(spa_text('You have version %1$s installed. Update to %2$s. Requires SP Version %3$s.'), $installed['Version'], $latest->version, $latest->requires)."</td>
							</tr>";
						$data = new stdClass;
						$data->slug = $file;
						$data->stylesheet = $installed['Stylesheet'];
						$data->new_version = (string) $latest->version;
						$data->url = 'http://simple-press.com';
						$data->package = ((string) $latest->archive).'&wpupdate=1';
						$up->response[$file] = $data;
                        $update = true;
					}
				}
			}
		}

        # any themes to update?
        if ($update) {
            set_site_transient('sp_update_themes', $up);
        } else {
            delete_site_transient('sp_update_themes');
        }

		if (!$header) {
			?>
				</tbody>
			</table>
			<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php spa_etext('Update SP Themes'); ?>" name="upgrade" /></p>
			</form>
			<?php
		} else {
			echo '<h3>'.spa_text('Simple:Press Themes').'</h3>';
			echo '<p>'.spa_text('Your SP themes are all up to date').'</p>';
		}
	}
}

function sp_remove_plugin_info($x, $y) {
	remove_action('after_plugin_row_simple-press/sp-control.php', 'wp_plugin_update_row', 10, 2 );
}

function sp_update_wp_counts($counts, $titles) {
	$pup = get_site_transient('sp_update_plugins');
	$tup = get_site_transient('sp_update_themes');
    if (!empty($pup) || !empty($tup)) {
        if (!empty($pup->response)) {
            $num = count($pup->response);
            $counts['counts']['plugins'] = $counts['counts']['plugins'] + $num;
        	$titles['sp_plugins'] = sprintf(_n('%d Simple:Press Plugin Update', '%d Simple:Press Plugin Updates', $num), $num);
        }
        if (!empty($tup->response)) {
            $num = count($tup->response);
            $counts['counts']['themes'] = $counts['counts']['themes'] + $num;
            $titles['sp_themes'] = sprintf(_n('%d Simple:Press Theme Update', '%d Simple:press Theme Updates', $num), $num);
        }

    	$counts['counts']['total'] = $counts['counts']['plugins'] + $counts['counts']['themes'] + $counts['counts']['wordpress'];
    	$counts['title'] = $titles ? esc_attr(implode(', ', $titles)) : '';
    }

    return $counts;
}

function sp_update_plugins() {
	if (!sp_current_user_can('SPF Manage Plugins')) {
		spa_etext('Access denied - you do not have permission');
		die();
	}

	check_admin_referer('upgrade-core');

	if (isset($_GET['plugins'])) {
		$plugins = explode(',', $_GET['plugins']);
	} elseif (isset( $_POST['checked'])) {
		$plugins = (array) $_POST['checked'];
	} else {
		wp_redirect(admin_url('update-core.php'));
		exit;
	}

	$url = 'update.php?action=update-sp-plugins&amp;plugins='.urlencode(implode(',', $plugins));
	$url = wp_nonce_url($url, 'bulk-update-sp-plugins');

	$title = spa_text('Update SP Plugins');

	require_once(ABSPATH.'wp-admin/admin-header.php');
	echo '<div class="wrap">';
	screen_icon('plugins');
	echo '<h2>'.spa_text('Update SP Plugins').'</h2>';
	echo "<iframe src='$url' style='width: 100%; height: 100%; min-height: 750px;' frameborder='0'></iframe>";
	echo '</div>';
	include(ABSPATH.'wp-admin/admin-footer.php');
}

function sp_do_plugins_update() {
	if (!sp_current_user_can('SPF Manage Plugins')) {
		spa_etext('Access denied - you do not have permission');
		die();
	}

	check_admin_referer( 'bulk-update-sp-plugins' );

	if (isset($_GET['plugins'])) {
		$plugins = explode(',', stripslashes($_GET['plugins']));
	} elseif (isset($_POST['checked'])) {
		$plugins = (array) $_POST['checked'];
	} else {
		$plugins = array();
	}

	$plugins = array_map('urldecode', $plugins);
	$url = 'update.php?action=update-sp-plugins&amp;plugins='.urlencode(implode(',', $plugins));
	$url = wp_nonce_url($url, 'bulk-update-sp-plugins');

	wp_enqueue_script('jquery');
	iframe_header();

	$upgrader = new SP_Plugin_Upgrader(new Bulk_SP_Plugin_Upgrader_Skin(compact('nonce', 'url')));
	$upgrader->bulk_upgrade($plugins);

	iframe_footer();
}

function sp_update_themes() {
	if (!sp_current_user_can('SPF Manage Themes')) {
		spa_etext('Access denied - you do not have permission');
		die();
	}

	check_admin_referer('upgrade-core');

	if (isset($_GET['themes'])) {
		$themes = explode(',', $_GET['themes']);
	} elseif (isset($_POST['checked'])) {
		$themes = (array) $_POST['checked'];
	} else {
		wp_redirect( admin_url('update-core.php') );
		exit;
	}

	$url = 'update.php?action=update-sp-themes&amp;themes='.urlencode(implode(',', $themes));
	$url = wp_nonce_url($url, 'bulk-update-sp-themes');

	$title = spa_text('Update SP Themes');

	require_once(ABSPATH.'wp-admin/admin-header.php');
	echo '<div class="wrap">';
	screen_icon('themes');
	echo '<h2>'.spa_text('Update SP Themes').'</h2>';
	echo "<iframe src='$url' style='width: 100%; height: 100%; min-height: 750px;' frameborder='0'></iframe>";
	echo '</div>';
	include(ABSPATH.'wp-admin/admin-footer.php');
}

function sp_do_themes_update() {
	if (!sp_current_user_can('SPF Manage Themes')) {
		spa_etext('Access denied - you do not have permission');
    	die();
	}

	check_admin_referer( 'bulk-update-sp-themes' );

	if (isset($_GET['themes'])) {
		$themes = explode(',', stripslashes($_GET['themes']));
	} elseif (isset($_POST['checked'])) {
		$themes = (array) $_POST['checked'];
	} else {
		$themes = array();
	}

	$themes = array_map('urldecode', $themes);

	$url = 'update.php?action=update-sp-themes&amp;plugins='.urlencode(implode(',', $themes));
	$url = wp_nonce_url($url, 'bulk-update-sp-themes');

	wp_enqueue_script('jquery');
	iframe_header();

	$upgrader = new SP_Theme_Upgrader(new Bulk_SP_Theme_Upgrader_Skin(compact('nonce', 'url')));
	$upgrader->bulk_upgrade($themes);

	iframe_footer();
}

function sp_plugin_upgrade_link($actions, $info) {
    # bail if not and sp upgrade
    if ($info['Title'] != 'Simple:Press') return $actions;

    # okay, our upgrade so offer link to upgrade db
    $actions['sp_db_update_page'] = '<a href="'.self_admin_url('admin.php?page='.SPINSTALLPATH).'" title="'.sp_text('Go Upgrade SP Database').'" target="_parent">'.spa_text('Upgrade the Simple:Press Database').'</a>';
    $actions = array_reverse($actions, true);
    return $actions;
}

function sp_do_plugin_upload() {
	if (!sp_current_user_can('SPF Manage Plugins')) {
		spa_etext('Access denied - you do not have permission');
		die();
	}

	check_admin_referer('forum-plugin_upload', 'forum-plugin_upload');

    include_once(SPBOOT.'admin/spa-admin-updater-class.php');

	$file_upload = new File_Upload_Upgrader('pluginzip', 'package');

	require_once(ABSPATH.'wp-admin/admin-header.php');

	$title = sprintf(spa_text('Uploading SP Plugin from uploaded file: %s'), basename($file_upload->filename));
	$nonce = 'plugin-upload';
	$url = add_query_arg(array('package' => $file_upload->id), 'update.php?action=upload-sp-plugin');
	$type = 'upload';
	$upgrader = new SP_Plugin_Upgrader(new SP_Plugin_Installer_Skin(compact('type', 'title', 'nonce', 'url')));
	$result = $upgrader->install($file_upload->package);

	if ($result || is_wp_error($result)) $file_upload->cleanup();

    # double check if we deleted the upload file and output message if not
    if (file_exists($file_upload->package)) echo sprintf(spa_text('Notice: Unable to remove the uploaded plugin zip archive: %s'), $file_upload->package);

	include(ABSPATH.'wp-admin/admin-footer.php');
}

function sp_do_theme_upload() {
	if (!sp_current_user_can('SPF Manage Themes')) {
		spa_etext('Access denied - you do not have permission');
		die();
	}

	check_admin_referer('forum-theme_upload', 'forum-theme_upload');

    include_once(SPBOOT.'admin/spa-admin-updater-class.php');

	$file_upload = new File_Upload_Upgrader('themezip', 'package');

	require_once(ABSPATH.'wp-admin/admin-header.php');

	$title = sprintf(spa_text('Uploading SP Theme from uploaded file: %s'), basename($file_upload->filename));
	$nonce = 'theme-upload';
	$url = add_query_arg(array('package' => $file_upload->id), 'update.php?action=upload-sp-theme');
	$type = 'upload';
	$upgrader = new SP_Theme_Upgrader(new SP_Theme_Installer_Skin(compact('type', 'title', 'nonce', 'url')));
	$result = $upgrader->install($file_upload->package);

	if ($result || is_wp_error($result)) $file_upload->cleanup();

    # double check if we deleted the upload file and output message if not
    if (file_exists($file_upload->package)) echo sprintf(spa_text('Notice: Unable to remove the uploaded theme zip archive: %s'), $file_upload->package);

	include(ABSPATH.'wp-admin/admin-footer.php');
}

?>