<?php
/*
Simple:Press Admin
Ahah call for permalink update/integration
$LastChangedDate: 2013-03-02 10:15:32 -0700 (Sat, 02 Mar 2013) $
$Rev: 9944 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

# ----------------------------------
# Check Whether User Can Manage Toolbox
if (!sp_current_user_can('SPF Manage Options')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

if (isset($_GET['item'])) {
	$item = $_GET['item'];
	if($item == 'upperm') spa_update_permalink_tool();
}

die();

function spa_update_permalink_tool() {
	echo '<strong>&nbsp;'.sp_update_permalink(true).'</strong>';
	?>
	<script type="text/javascript">window.location= "<?php echo SFADMININTEGRATION; ?>";</script>
	<?php
	die();
}

die();
?>