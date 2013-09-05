<?php

/*								Generate Sidebars
-----------------------------------------------------------------------------------
*/

define('CT_ADMIN', get_template_directory_uri() . '/lib/admin');

add_action('admin_init', 'sidebar_init');

function sidebar_init() {
		
	if(isset($_GET['page']) && $_GET['page'] == 'sidebars') {
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('sidebar-manager', CT_ADMIN . '/scripts/sidebar.js');
		wp_enqueue_style('sidebar-manager', CT_ADMIN . '/css/sidebar.css');
	}
}

function sidebar_admin_menu() {
	add_theme_page( __( 'Sidebars', 'churchthemes' ), __( 'Sidebars', 'churchthemes' ), 'edit_themes', 'sidebars', 'sidebar_admin_page' );
}

function sidebar_admin_page() {	
	if(isset($_POST['add_themesidebar'])) {
		if ( $_POST['add_themesidebar'] == 'true' ) { themesidebar_add(); }
	}
	if(isset($_POST['remove_themesidebar'])) {
		if ( $_POST['remove_themesidebar'] == 'true' ) { themesidebar_remove(); }
	}
?>
	<div class="wrap">
		<div id="icon-themes" class="icon32"><br /></div>
		<h2><?php _e('Sidebars','churchthemes'); ?></h2>
		<br />
		
		<div class="form-wrap">
			<form method="POST" action="">
				<?php wp_nonce_field('add_new_sidebar','a_wpnonce'); ?>
				<input type="hidden" name="add_themesidebar" value="true" />
				<p><input type="text" name="addsidebar" id="addsidebar" size="32" value=""/> <?php _e('Type a name for a new sidebar and click &quot;Add New Sidebar&quot;','churchthemes'); ?></p>
				<p><input type="submit" name="" value="Add New Sidebar" class="button" /></p>
				<br />
			</form>
		</div>
		
		<div class="form-wrap">
			<form id="manager-sidebars" method="POST" action="">
				<?php wp_nonce_field('remove_sidebar','r_wpnonce'); ?>
				<input type="hidden" name="remove_themesidebar" value="true" />
				<table id="sidebar-list" class="widefat" cellspacing="0">
					<thead class="sidebar-name">
						<tr>
							<th class="column-cb check-column"><input type="checkbox" disabled="true"/></th>
							<th class="manage-column"><?php _e('Available Sidebars','churchthemes'); ?></th>
						</tr>
					</thead>
					<tfoot class="sidebar-name">
						<tr>
							<th class="column-cb check-column"><input type="checkbox" disabled="true"/></th>
							<th class="manage-column"><?php _e('Available Sidebars','churchthemes'); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php
						$ct_sidebars = get_option('ct_generated_sidebars');
						foreach ($ct_sidebars as $ct_sidebar) {
							$sidebar_id = strtolower($ct_sidebar);
							$sidebar_id = str_replace( ' ', '', $sidebar_id );
							echo '<tr><th class="column-cb check-column"><input type="checkbox" name="'.$sidebar_id.'" id="'.$ct_sidebar.'" /></th><td><strong>'.$ct_sidebar.'</strong><br /></td></tr>';
						}
						?>
					</tbody>
				</table>
				<br />
				<p><input type="submit" value="Delete Selected" class="button" /></p>
			</form>
		</div>
	</div>
<?php
}

$ct_generated_sidebars = array(
		'primarysidebar' => 'Primary Sidebar'
);

add_option( 'ct_generated_sidebars', $ct_generated_sidebars );

function themesidebar_add() {

	if ( !empty($_POST) && check_admin_referer('add_new_sidebar','a_wpnonce') ) {
	
	$sidebar_id = strtolower($_POST['addsidebar']);
	$sidebar_id = str_replace( ' ', '', $sidebar_id );
	
	$ct_sidebars = get_option('ct_generated_sidebars');
	foreach ($ct_sidebars as $ct_sidebar) {
		if ($_POST['addsidebar'] && $ct_sidebar == $_POST['addsidebar']) {
			wp_die('<div class="wrap"><div id="icon-themes" class="icon32"><br /></div><h2>Sidebar Manager</h2><div id="message" class="error below-h2"><p>Error: Sidebar already exists.</p></div></div>');
		} elseif (!$_POST['addsidebar']) {
		wp_die('<div class="wrap"><div id="icon-themes" class="icon32"><br /></div><h2>Sidebar Manager</h2><div id="message" class="error below-h2"><p>Error: You have not entered a name!</p></div></div>');
		}
	} 
	$ct_sidebars = get_option('ct_generated_sidebars');
	$ct_sidebars[$sidebar_id] = $_POST['addsidebar'];
	update_option('ct_generated_sidebars', $ct_sidebars);
}

}
function themesidebar_remove() {

	if ( !empty($_POST) && check_admin_referer('remove_sidebar','r_wpnonce') ) {

		$ct_sidebars = get_option('ct_generated_sidebars');
		foreach ($ct_sidebars as $ct_sidebar) {
			$sidebar_id = strtolower($ct_sidebar);
			$sidebar_id = str_replace( ' ', '', $sidebar_id );
			if(isset($_POST[$sidebar_id])) {
				$ct_sidebars = get_option('ct_generated_sidebars');
				unset( $ct_sidebars[$sidebar_id] );
				update_option('ct_generated_sidebars', $ct_sidebars);
			}
		}
	}
}

add_action('admin_menu', 'sidebar_admin_menu');


/*								Register Sidebars
-----------------------------------------------------------------------------------
*/

function ct_widgets_init() {
	
	// Area 1, Contains generated sidebars.
	$ct_sidebars = get_option('ct_generated_sidebars');
	foreach ($ct_sidebars as $ct_sidebar) {
		
		$sidebar_id = strtolower($ct_sidebar);
		$sidebar_id = str_replace( ' ', '', $sidebar_id );

		register_sidebar( array(
			'name'			=> $ct_sidebar,
			'id'			=> $sidebar_id,
			'before_widget' => '<div class="widget %2$s">',
			'after_widget'	=> '<div class="clear"></div></div>',
			'before_title'	=> '<h3>',
			'after_title'	=> '</h3>',
		));
	}	

}

add_action( 'widgets_init', 'ct_widgets_init' );


/*						Register other default Sidebars
-----------------------------------------------------------------------------------
*/

if (function_exists('register_sidebar')) {

	register_sidebar(array(
		'before_widget' => '<div class="home_widget">',
		'after_widget' => '<div class="clear"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'description' => 'Using only one widget in this area is recommended',
		'name' => 'Homepage Left'
	));
	
	register_sidebar(array(
		'before_widget' => '<div class="home_widget">',
		'after_widget' => '<div class="clear"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'description' => 'Using only one widget in this area is recommended',
		'name' => 'Homepage Center'
	));
	
	register_sidebar(array(
		'before_widget' => '<div class="home_widget">',
		'after_widget' => '<div class="clear"></div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'description' => 'Using only one widget in this area is recommended',
		'name' => 'Homepage Right'
	));

}

?>