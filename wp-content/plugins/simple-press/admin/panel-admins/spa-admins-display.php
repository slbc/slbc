<?php
/*
Simple:Press
Admin Admins Display Rendering
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_render_admins_panel($formid) {
?>
	<div class="clearboth"></div>

	<div class="wrap sfatag">
		<?php
			spa_render_sidemenu();
		?>
		<div id='sfmsgspot'></div>
		<div id="sfmaincontainer">
			<?php spa_render_admins_container($formid); ?>
		</div>
			<div class="clearboth"></div>
	</div>
<?php
}

function spa_render_admins_container($formid) {

	switch($formid) {
		case 'youradmin':
			include_once(SF_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-your-options-form.php');
			spa_admins_your_options_form();
			break;

		case 'globaladmin':
			include_once(SF_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-global-options-form.php');
			spa_admins_global_options_form();
			break;

		case 'manageadmin':
			require_once(ABSPATH.'wp-admin/includes/admin.php');
			include_once(SF_PLUGIN_DIR.'/admin/panel-admins/forms/spa-admins-manage-admins-form.php');
			spa_admins_manage_admins_form();
			break;

        # leave this for plugins to add to this panel
		case 'plugin':
			include_once(SF_PLUGIN_DIR.'/admin/panel-plugins/forms/spa-plugins-user-form.php');
            $admin = (isset($_GET['admin'])) ? sp_esc_str($_GET['admin']) : '';
            $save = (isset($_GET['save'])) ? sp_esc_str($_GET['save']) : '';
            $form = (isset($_GET['form'])) ? sp_esc_int($_GET['form']) : '';
            $reload = (isset($_GET['reload'])) ? sp_esc_str($_GET['reload']) : '';
			spa_plugins_user_form($admin, $save, $form, $reload);
			break;
	}
}
?>