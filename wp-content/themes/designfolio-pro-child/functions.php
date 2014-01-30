<?php
/* Designfolio Pro Child Theme.
 *
 * v1.0.
 */

add_action( 'after_setup_theme', 'override_parent_theme_main_features', 11 );
add_action( 'after_setup_theme', 'override_parent_theme_lower_level_features', 13 );

/* Remove features added in the parent theme functions.php file. */
function override_parent_theme_main_features() {

	/* e.g. Remove color scheme support. */
	//remove_theme_support( 'color-schemes' );

	/* e.g. Remove Google font support. */
	//remove_theme_support( 'google-fonts' );

	add_filter('excerpt_length', 'pro_child_filter_excerpt');
	function pro_child_filter_excerpt($length) {
		return 25;
	}
}

/* Alter lower level features in the framework itself. */
function override_parent_theme_lower_level_features() {

	/* Add code here.. */
}

add_action('wp_footer', 'slbc_google_analytics');
function slbc_google_analytics() {
	?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-43944868-1', 'myslbc.org');
  ga('send', 'pageview');

</script>
	<?php
}

/* Add other hooks/functions below.. */

?>
