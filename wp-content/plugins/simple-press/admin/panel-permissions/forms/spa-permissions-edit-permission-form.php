<?php
/*
Simple:Press
Admin Permissions Edit Permission Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the edit permission set form.  It is hidden until the edit permission set link is clicked
function spa_permissions_edit_permission_form($role_id)
{
    global $spGlobals;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfroleedit<?php echo $role_id; ?>', 'sfreloadpb');
	jQuery(function(jQuery){vtip();})
});
</script>
<?php
	# Get correct tooltips file
	$lang = WPLANG;
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-permissions-tips-en.php';
	if (file_exists($ttpath)) include_once($ttpath);

	$role = spa_get_role_row($role_id);

	spa_paint_options_init();
    $ahahURL = SFHOMEURL."index.php?sp_ahah=permissions-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=editperm";
	?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfroleedit<?php echo $role->role_id; ?>" name="sfroleedit<?php echo $role->role_id; ?>">
		<?php
		echo sp_create_nonce('forum-adminform_roleedit');
		spa_paint_open_tab(spa_text('Permissions')." - ".spa_text('Manage Permissions'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Edit Permission'), 'true', 'edit-master-permission-set', false);
					?>
					<input type="hidden" name="role_id" value="<?php echo $role->role_id; ?>" />
					<table class="form-table">
						<tr>
							<td class="sflabel"><?php spa_etext("Permission Set Name") ?>:&nbsp;&nbsp;<br />
							<input type="text" class="sfpostcontrol" size="45" name="role_name" value="<?php echo sp_filter_title_display($role->role_name); ?>" /></td>
							<td class="sflabel"><?php spa_etext("Permission Set Description") ?>:&nbsp;&nbsp;<br/>
							<input type="text" class="sfpostcontrol" size="85" name="role_desc" value="<?php echo sp_filter_title_display($role->role_desc); ?>" /></td>
						</tr>
					</table>

					<br /><p><strong><?php spa_etext("Permission Set Actions") ?>:</strong></p>
					<?php
					echo '<p><img src="'.SFADMINIMAGES.'sp_GuestPerm.png" alt="" width="16" height="16" align="top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon will be ignored for Guest Users').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINIMAGES.'sp_GlobalPerm.png" alt="" width="16" height="16" align="top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon require enabling to use').'</small>';
					echo '&nbsp;&nbsp;&nbsp;<img src="'.SFADMINIMAGES.'sp_Warning.png" alt="" width="16" height="16" align="top" />';
					echo '<small>&nbsp;'.spa_text('Note: Action settings displaying this icon should be used with great care').'</small></p>';

					sp_build_site_auths_cache();

					$sql = "SELECT auth_id, auth_name, auth_cat, authcat_name, warning FROM ".SFAUTHS."
							JOIN ".SFAUTHCATS." ON ".SFAUTHS.".auth_cat = ".SFAUTHCATS.".authcat_id
							WHERE active = 1
							ORDER BY auth_cat, auth_id";
					$authlist = spdb_select('set', $sql);

					$role_auths = maybe_unserialize($role->role_auths);

					$firstitem = true;
					$category = '';
					$thiscol = 0;
					?>
					<!-- OPEN OUTER CONTAINER DIV -->
					<div class="outershell" style="width: 100%;">
					<?php

					foreach($authlist as $a) {
						if($category != $a->authcat_name) {
							$category = $a->authcat_name;
							if(!$firstitem) {
								?>
								<!-- CLOSE DOWN THE ENDS -->
								</table><div class="clearboth"></div></div>
								<?php
								if($thiscol==3) {
									?>
									<div class="clearboth"></div>
									<?php
									$thiscol=0;
								}
							}
							?>
							<!-- OPEN NEW INNER DIV -->
							<div class="innershell" style="width: 32%; float: left;padding: 10px 10px 0 0">
							<!-- NEW INNER DETAIL TABLE -->
							<table width="100%" border="0">
							<tr><td colspan="2" class="permhead"><?php spa_etext($category); ?></td></tr>
							<?php
							$firstitem = false;
							$thiscol++;
						}


						$auth_id = $a->auth_id;
						$auth_name = $a->auth_name;
						$authWarn = (empty($a->warning)) ? false : true;
						$warn = ($authWarn) ? " permwarning" : '';
						$tip = ($authWarn) ? " class='vtip permwarning' title='".esc_js(spa_text($a->warning))."'" : '';

						$button = 'b-'.$auth_id;
						$checked = '';
						if (isset($role_auths[$auth_id]) && $role_auths[$auth_id]) $checked = ' checked="checked"';
						if ($spGlobals['auths'][$auth_id]->ignored || $spGlobals['auths'][$auth_id]->enabling || $authWarn) {
							$span = '';
						} else {
							$span = ' colspan="2" ';
						}

						?>
						<tr<?php echo($tip); ?>>
							<td class="permentry<?php echo($warn); ?>">

								<label for="sfR<?php echo $role->role_id.$button; ?>" class="sflabel">
								<img align="top" style="float: right; border: 0pt none ; margin: -4px 5px 0px 3px; padding: 0;" class="vtip" title="<?php echo $tooltips[$auth_name]; ?>" src="<?php echo SFADMINIMAGES; ?>sp_Information.png" alt="" />
								<?php spa_etext($spGlobals['auths'][$auth_id]->auth_desc); ?></label>
								<input type="checkbox" name="<?php echo $button; ?>" id="sfR<?php echo $role->role_id.$button; ?>"<?php echo $checked; ?>  />
								<?php if ($span == '') { ?>
									<td align="center" class="permentry" width="32px">
								<?php }
								if ($span == '') {
									if ($spGlobals["auths"][$auth_id]->enabling) {
										echo '<img src="'.SFADMINIMAGES.'sp_GlobalPerm.png" alt="" width="16" height="16" title="'.spa_text('Requires Enabling').'" />';
									}
									if($spGlobals['auths'][$auth_id]->ignored) {
										echo '<img src="'.SFADMINIMAGES.'sp_GuestPerm.png" alt="" width="16" height="16" title="'.spa_text('Ignored for Guests').'" />';
									}
									if($authWarn) {
										echo '<img src="'.SFADMINIMAGES.'sp_Warning.png" alt="" width="16" height="16" title="'.spa_text('Use with Caution').'" />';
									}
									echo '</td>';
								} else {
									?>
									</td><td class="permentry" width="32px"></td>
									<?php
								}
								?>
						</tr>
						<?php
					}
					?>
					<!-- END CONTAINER DIV -->
					</table></div><div class="clearboth"></div>
					</div>
					<?php
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_perm_edit_perm_panel');
		spa_paint_close_tab();
		?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="sfpermedit<?php echo $role->role_id; ?>" name="sfpermedit<?php echo $role->role_id; ?>" value="<?php spa_etext('Update Permission'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#perm-<?php echo $role->role_id; ?>').html('');" id="sfpermedit<?php echo $role->role_id; ?>" name="editpermcancel<?php echo $role->role_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
		</div>
		</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>