<?php
/*
Simple:Press
Admin Profiles Tabs and Menus Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_profiles_tabs_menus_form()
{
	# get profile tabs and menus
	$tabs = spa_get_tabsmenus_data();

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sptabsmenusform', 'sfreloadptm');

	jQuery('#tabsList').sortable({
		placeholder: 'sortable-placeholder',
		update: function () {
			/* run sortable on tabs since the order changed */
			jQuery("input#spTabsOrder").val(jQuery("#tabsList").sortable('serialize'));
		}
	});

	jQuery('.menuList').sortable({
		placeholder: 'sortable-placeholder',
	    connectWith: jQuery('.menuList'),
		update: function () {
			/* run sortable on changed menu */
			id = this.id;
			tid = id.substring(8);
			jQuery("input#spMenusOrder"+tid).val(jQuery("#menuList"+tid).sortable('serialize'));
		}
	});

<?php if ($tabs) { ?>
	/* now run sortable on tabs and menus so they're guaranteed to be populate */
	jQuery("input#spTabsOrder").val(jQuery("#tabsList").sortable('serialize'));
	num = <?php echo count($tabs); ?>;
	for (i=0; i<=num; i++) {
		jQuery("input#spMenusOrder"+i).val(jQuery("#menuList"+i).sortable('serialize'));
	}
<?php } ?>
});
</script>
<?php

    $ahahURL = SFHOMEURL."index.php?sp_ahah=profiles-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=tabs-menus";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sptabsmenusform" name="sptabsmenusform">
	<?php echo sp_create_nonce('forum-adminform_tabsmenus'); ?>
<?php

	spa_paint_options_init();

#== CUSTOM FIELDS Tab ============================================================

	spa_paint_open_tab(spa_text('Profiles')." - ".spa_text('Tabs'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Profile Menu Order'), true, 'profile-menus');
				echo '<tr>';
				echo '<td colspan="2">';
				echo '<p>'.spa_text('Here you can set the order of Profile Tabs and Menus by dragging and dropping below.  Additionally, you can edit any of the Tabs or Menus.').'</p>';

				if (!empty($tabs))
				{
					echo '<ul id="tabsList" class="tabsList menu">';
					foreach ($tabs as $tindex => $tab)
					{
						echo '<li id="tab-'.$tindex.'" class="menu-item-depth-0">';
                        $class = ($tab['display']) ? '' : ' menu-item-disabled';
						echo "<div class='menu-item$class'>";
						echo '<span class="item-name">'.$tab['name'].'</span>';
						echo '<span class="item-controls">';
						echo '<span class="item-type">'.spa_text('Tab').'</span>';
						echo '<a class="item-edit" href="javascript:void(0);" onclick="spjToggleLayer(\'item-edit-'.$tindex.'\');">Edit Menu</a>';
						echo '<input type="text" class="inline_edit" size="70" id="spMenusOrder'.$tindex.'" name="spMenusOrder'.$tindex.'" />';
						echo '</span>';
						echo '</div>';
						echo '<div id="item-edit-'.$tindex.'" class="menu-item-settings inline_edit">';
						echo '<p class="description">'.spa_text('Tab Name').'<br /><input type="text" class="sfpostcontrol" id="tab-name-'.$tindex.'" name="tab-name-'.$tindex.'" value="'.sp_filter_title_display($tab['name']).'" /></p>';
                        echo '<input type="hidden" id="tab-slug-'.$tindex.'" name="tab-slug-'.$tindex.'" value="'.esc_attr($tab['slug']).'" />';
						echo '<p/><p class="description">'.spa_text('Tab Auth').'<br /><input type="text" class="sfpostcontrol" id="tab-auth-'.$tindex.'" name="tab-auth-'.$tindex.'" value="'.sp_filter_title_display($tab['auth']).'" /></p>';
						$checked = ($tab['display']) ? $checked = 'checked="checked" ' : '';
						echo '<p/><p class="description"><label for="sf-tab-display-'.$tindex.'">'.spa_text('Display Tab').'</label><input type="checkbox" '.$checked.'name="tab-display-'.$tindex.'" id="sf-tab-display-'.$tindex.'" /></p>';
						echo '<p><a href="javascript:void(0);" onclick="spjToggleLayer(\'item-edit-'.$tindex.'\');" >'.spa_text('Close').'</a></p>';
						echo '</div>';

						# now output any menus on the tab
						echo '<ul id="menuList'.$tindex.'" class="menuList menu">';
						if (!empty($tab['menus'])) {
							foreach ($tab['menus'] as $mindex => $menu) {
								echo '<li id="tab'.$tindex.'-'.$mindex.'" class="menu-item-depth-1">';
                                $class = ($menu['display']) ? '' : ' menu-item-disabled';
								echo "<div class='menu-item$class'>";
								echo '<span class="item-name">'.$menu['name'].'</span>';
								echo '<span class="item-controls">';
								echo '<span class="item-type">'.spa_text('Menu').'</span>';
								echo '<a class="item-edit" href="javascript:void(0);" onclick="spjToggleLayer(\'item-edit-'.$tindex.'-'.$mindex.'\');" >Edit Menu</a>';
								echo '</span>';
								echo '</div>';
								echo '<div id="item-edit-'.$tindex.'-'.$mindex.'" class="menu-item-settings inline_edit">';
								echo '<p class="description">'.spa_text('Menu Name').'<br /><input type="text" class="sfpostcontrol" id="menu-name-'.$tindex.'-'.$mindex.'" name="menu-name-'.$tindex.'-'.$mindex.'" value="'.sp_filter_title_display($menu['name']).'" /></p>';
                                echo '<input type="hidden" id="menu-slug-'.$tindex.'-'.$mindex.'" name="menu-slug-'.$tindex.'-'.$mindex.'" value="'.esc_attr($menu['slug']).'" />';
								echo '<p/><p class="description">'.spa_text('Menu Auth').'<br /><input type="text" class="sfpostcontrol" id="menu-auth-'.$tindex.'-'.$mindex.'" name="menu-auth-'.$tindex.'-'.$mindex.'" value="'.sp_filter_title_display($menu['auth']).'" /></p>';
								echo '<p/><p class="description">'.spa_text('Menu Form').'<br /><input type="text" class="sfpostcontrol" id="menu-form-'.$tindex.'-'.$mindex.'" name="menu-form-'.$tindex.'-'.$mindex.'" value="'.esc_attr($menu['form']).'" /></p>';
								$checked = ($menu['display']) ? $checked = 'checked="checked" ' : '';
								echo '<p/><p class="description"><label for="sf-menu-display-'.$tindex.'-'.$mindex.'">'.spa_text('Display Menu').'</label><input type="checkbox" '.$checked.'name="menu-display-'.$tindex.'-'.$mindex.'" id="sf-menu-display-'.$tindex.'-'.$mindex.'" /></p>';
								echo '<p><a href="javascript:void(0);" onclick="spjToggleLayer(\'item-edit-'.$tindex.'-'.$mindex.'\');" >'.spa_text('Close').'</a></p>';
								echo '</div>';
								echo '</li>';
							}
						}
						echo '</ul>';
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '<input type="text" class="inline_edit" size="70" id="spTabsOrder" name="spTabsOrder" />';
				echo '</td>';
				echo '</tr>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_tabsmenus_panel');
	spa_paint_close_tab();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Profile Tabs and Menus'); ?>" />
	</div>
	</form>
<?php
}
?>