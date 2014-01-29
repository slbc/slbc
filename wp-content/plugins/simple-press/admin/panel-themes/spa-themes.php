<?php
/*
Simple:Press
Admin Themes
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Admins
global $spStatus;

if (!sp_current_user_can('SPF Manage Themes')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

include_once (SF_PLUGIN_DIR.'/admin/panel-themes/spa-themes-display.php');
include_once (SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-prepare.php');
include_once (SF_PLUGIN_DIR.'/admin/panel-themes/support/spa-themes-save.php');
include_once (SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

if ($spStatus != 'ok') {
	include_once (SPLOADINSTALL);
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-themes';
# --------------------------------------------------------------------

if (isset($_GET['tab']) ? $tab = $_GET['tab'] : $tab = 'list') ;

spa_panel_header();
spa_render_themes_panel($tab);
spa_panel_footer();

if (isset($_GET['action'])) {
	$action = $_GET['action'];
	$title  = $_GET['title'];
	$msg = $title.' '.spa_text('Theme').' <strong>'.spa_text('Activated').'</strong>';
	$msg = $title.' '.spa_text('Theme').' <strong>'.spa_text('Deleted').'</strong>';
	$msg = apply_filters('sph_theme_message', $msg);

	?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#sfmsgspot").fadeIn("fast");
		jQuery("#sfmsgspot").html("<?php echo($msg); ?>");
		jQuery("#sfmsgspot").fadeOut(8000);
		window.location = '<?php echo(SFADMINTHEMES); ?>';
	});
	</script>
	<?php
}

?>