<?php
/*
Simple:Press
Admin Options Global Form
$LastChangedDate: 2013-03-16 15:24:09 -0700 (Sat, 16 Mar 2013) $
$Rev: 10079 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_global_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfglobalform', 'sfreloadog');
});
</script>
<?php

	global $wp_roles, $tab;

	$sfoptions = spa_get_global_data();
    $ahahURL = SFHOMEURL.'index.php?sp_ahah=options-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=global';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfglobalform" name="sfglobal">
	<?php echo sp_create_nonce('forum-adminform_global'); ?>
<?php

	spa_paint_options_init();

#== GLOBAL Tab ============================================================

	spa_paint_open_tab(spa_text('Options').' - '.spa_text('Global Settings'));

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Lock Down Forum'), true, 'lock-down-forum');
				spa_paint_checkbox(spa_text('Lock the entire forum (read only)'), 'sflockdown', $sfoptions['sflockdown']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('WP Admin Pages Access'), true, 'block-admin');
				spa_paint_checkbox(spa_text('Block user access to WP admin pages'), 'blockadmin', $sfoptions['blockadmin']);
                if ($sfoptions['blockadmin']) {
    				$roles = array_keys($wp_roles->role_names);
    				if ($roles) {
    				    echo '<tr><td colspan="2"><p class="subhead">'.spa_text('Allow these WP roles access to the WP admin').':</p>';
         			    echo '<p><strong><small>('.spa_text('Administrators will always have access').')</small></strong></p></td></tr>';
    					foreach ($roles as $index => $role) {
                            if ($role != 'administrator') spa_paint_checkbox($role, 'role-'.$index, $sfoptions['blockroles'][$role]);
                        }
    				}
    				spa_paint_input(spa_text('URL to redirect to if blocking admin access'), 'blockredirect', $sfoptions['blockredirect'], false, true);
                    spa_paint_checkbox(spa_text("Redirect to user's profile page (overrides URL above)"), 'blockprofile', $sfoptions['blockprofile']);
                }
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Auto Update'), true, 'auto-update');
				spa_paint_checkbox(spa_text('Use auto update'), 'sfautoupdate', $sfoptions['sfautoupdate']);
				spa_paint_input(spa_text('How many seconds before refresh'), 'sfautotime', $sfoptions['sfautotime']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_global_left_panel');

	spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('RSS Feeds'), true, 'rss-feeds');
				spa_paint_input(spa_text('Number of recent posts to feed'), 'sfrsscount', $sfoptions['sfrsscount']);
				spa_paint_checkbox(spa_text('Limit feeds to topic names (no post content)'), 'sfrsstopicname', $sfoptions['sfrsstopicname']);
				spa_paint_input(spa_text('Limit to number of words if showing content (0 = all)'), 'sfrsswords', $sfoptions['sfrsswords']);
				spa_paint_checkbox(spa_text('Enable feedkeys for private RSS feeds'), 'sfrssfeedkey', $sfoptions['sfrssfeedkey']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('CSS/JS Combined Caching'), true, 'combined-caches');
				spa_paint_checkbox(spa_text('Enable combining and caching of forum CSS files'), 'combinecss', $sfoptions['combinecss']);
				spa_paint_checkbox(spa_text('Enable combining and caching of forum script (JS) files'), 'combinejs', $sfoptions['combinejs']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Post Editing'), true, 'post-editing');
				?>
				<tr valign='top'>
					<td class='sflabel' width='100%' colspan='2'>
						<table class="form-table table-cbox"><tr><td class="td-cbox"><?php spa_etext('Select Default Editor'); ?>:</td>
							<td width='70%' class='td-cbox'>

							<?php if (defined('RICHTEXT')) {
								if ($sfoptions['defeditor'] == 1 ? $checked = 'checked="checked"' : $checked = ''); ?>
								<label for="sfradio-editor1" class="sflabel radio"><?php echo(spa_text('Rich text').' ('.RICHTEXTNAME.')'); ?></label>
								<input type="radio" name="editor" id="sfradio-editor1"  tabindex="<?php echo($tab); $tab++; ?>" value="1" <?php echo($checked); ?> />
							<?php } ?>

							<?php if (defined('HTML')) {
								if ($sfoptions['defeditor'] == 2 ? $checked = 'checked="checked"' : $checked = ''); ?>
								<label for="sfradio-editor2" class="sflabel radio"><?php echo(spa_text('HTML').' ('.HTMLNAME.')'); ?></label>
								<input type="radio" name="editor" id="sfradio-editor2"  tabindex="<?php echo($tab); $tab++; ?>" value="2" <?php echo($checked); ?> />
							<?php } ?>

							<?php if (defined('BBCODE')) {
								if ($sfoptions['defeditor'] == 3 ? $checked = 'checked="checked"' : $checked = ''); ?>
								<label for="sfradio-editor3" class="sflabel radio"><?php echo(spa_text('bbCode').' ('.BBCODENAME.')'); ?></label>
								<input type="radio" name="editor" id="sfradio-editor3"  tabindex="<?php echo($tab); $tab++; ?>" value="3" <?php echo($checked); ?> />
							<?php }

								if ($sfoptions['defeditor'] == 4 ? $checked = 'checked="checked"' : $checked = ''); ?>
								<label for="sfradio-editor4" class="sflabel radio"><?php echo(spa_text('Plain text').' ('.PLAINTEXTNAME.')'); ?></label>
								<input type="radio" name="editor" id="sfradio-editor4"  tabindex="<?php echo($tab); $tab++; ?>" value="4" <?php echo($checked); ?> />

					</td></tr></table></td>
				</tr>
				<?php
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_options_global_right_panel');

	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Global Options'); ?>" />
	</div>
	</form>
<?php
}
?>