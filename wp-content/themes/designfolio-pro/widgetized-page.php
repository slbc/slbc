<?php
/*
Template Name: Widgetized Page
*/
?>
<?php get_header(); ?>

<?php PC_Hooks::pc_after_get_header(); /* Framework hook wrapper */ ?>

	<div id="container">

		<?php PC_Hooks::pc_after_container(); /* Framework hook wrapper */ ?>

		<div id="contentwrap" <?php echo PC_Utility::contentwrap_layout_classes(); ?>>
		
			<?php PC_Hooks::pc_before_content(); /* Framework hook wrapper */ ?>

			<div class="<?php echo PC_Utility::content_layout_classes_primary(); ?>">

				<?php PC_Hooks::pc_after_content_open(); /* Framework hook wrapper */ ?>

				<?php
					global $pc_template;
					global $pc_post_id; // need the post id to check for the sidebars per post/page

					$main_content_custom_widget_areas = get_post_meta( $pc_post_id, '_'.PC_THEME_NAME_SLUG.'_main_content_sort', true );
					$options = get_option( PC_OPTIONS_DB_NAME );

					// Show main content widgets if any exist
					if ( isset($main_content_custom_widget_areas) && !empty($main_content_custom_widget_areas) ) {
						$main_content_custom_widget_areas = array_keys($main_content_custom_widget_areas);

						foreach($main_content_custom_widget_areas as $main_content_custom_widget_area) {

							/* Just output the main content widgets (if any) else nothing. */
							if ( is_active_sidebar( $main_content_custom_widget_area ) ) : ?>
									<div id="<?php echo $main_content_custom_widget_area; ?>" class="widget-area">
										<?php dynamic_sidebar( $main_content_custom_widget_area ); ?>
									</div><?php
							endif;
						}
					}
				?>

			</div><!-- .content -->

			<?php PC_Hooks::pc_after_content(); /* Framework hook wrapper */ ?>

		</div><!-- #contentwrap -->
	
	</div><!-- #container -->

<?php get_footer(); ?>