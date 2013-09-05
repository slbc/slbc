<h2 class="meta_section"><?php _e('General', 'churchthemes'); ?></h2>

<div class="meta_item">
	<label><?php _e('Layout', 'churchthemes'); ?></label>
	<?php $selected = ' selected="selected"'; ?>
	<?php $metabox->the_field('ct_page_layout'); ?>
	<select name="<?php $metabox->the_name(); ?>">
		<option value="right"<?php if ($metabox->get_the_value('ct_page_layout') == 'right') echo $selected; ?>><?php _e('Sidebar Right', 'churchthemes'); ?></option>
		<option value="left"<?php if ($metabox->get_the_value('ct_page_layout') == 'left') echo $selected; ?>><?php _e('Sidebar Left', 'churchthemes'); ?></option>
		<option value="full"<?php if ($metabox->get_the_value('ct_page_layout') == 'full') echo $selected; ?>><?php _e('No Sidebar (Full Width)', 'churchthemes'); ?></option>
	</select>
</div>

<div class="meta_item">
	<label><?php _e('Use', 'churchthemes'); ?></label>
	<?php $selected = ' selected="selected"'; ?>
	<?php $metabox->the_field('ct_page_sidebar'); ?>
	<select name="<?php $metabox->the_name(); ?>">
		<?php
			$ct_sidebars = get_option('ct_generated_sidebars');
			foreach ($ct_sidebars as $ct_sidebar) {
				$sidebar_id = strtolower($ct_sidebar);
				$sidebar_id = str_replace( ' ', '', $sidebar_id );
				if($metabox->get_the_value() == $ct_sidebar) {
					echo '<option value="'.$ct_sidebar.'"'.$selected.'>'.$ct_sidebar.'</option>';
				} else {
					echo '<option value="'.$ct_sidebar.'">'.$ct_sidebar.'</option>';
				}
			}
		?>
	</select>
	<span><?php _e('Only widgets from the selected group will be displayed in this page\'s Sidebar', 'churchthemes'); ?></span>
</div>

<div class="meta_item">
	<label><?php _e('Social Footer', 'churchthemes'); ?></label>
	<?php $selected = ' selected="selected"'; ?>
	<?php $metabox->the_field('ct_social_footer'); ?>
	<select name="<?php $metabox->the_name(); ?>">
		<option value="show"<?php if ($metabox->get_the_value() == 'show') echo $selected; ?>><?php _e('Show', 'churchthemes'); ?></option>
		<option value="hide"<?php if ($metabox->get_the_value() == 'hide') echo $selected; ?>><?php _e('Hide', 'churchthemes'); ?></option>
	</select>
	<span><?php _e('You can override this setting and completely disable the Social Footer in the', 'churchthemes'); ?> <a href="themes.php?page=social-footer"><?php _e('Social Footer Settings', 'churchthemes'); ?></a></span>
</div>

<div class="meta_item">
	<label><?php _e('Tagline', 'churchthemes'); ?></label>
	<input type="text" name="<?php $metabox->the_name('ct_page_tagline'); ?>" size="50" placeholder="Optional page tagline" value="<?php $metabox->the_value('ct_page_tagline'); ?>"/>
	<span><?php _e('Displays to the right of the Page Title', 'churchthemes'); ?></span>
</div>

<hr class="meta_divider" />

<h2 class="meta_section"><?php _e('More', 'churchthemes'); ?></h2>

<div class="meta_item">
	<label><?php _e('Admin Notes', 'churchthemes'); ?><br /><br /><span class="label_note"><?php _e('Not Published', 'churchthemes'); ?></span></label>
	<?php $metabox->the_field('ct_page_special_notes'); ?>
	<textarea name="<?php $metabox->the_name(); ?>" cols="50" rows="6"><?php $metabox->the_value('ct_page_special_notes'); ?></textarea>
</div>

<div class="meta_clear"></div>