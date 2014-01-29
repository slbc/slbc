<?php
/*
Simple:Press
Edit Post Form Rendering
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_edit_post_form($a, $postid, $postcontent) {
	global $spVars, $spThisUser, $spThisTopic, $spGlobals;

	extract($a, EXTR_SKIP);

	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php');

	$toolbar = $spGlobals['display']['editor']['toolbar'];

	$out = '';

	$out.= "<div id='spPostForm'>\n";
	$out.= "<form class='$tagClass' action='".sp_build_url($spThisTopic->forum_slug, $spThisTopic->topic_slug, $spThisTopic->display_page, $postid)."' method='post' name='editpostform'>\n";
	$out.= "<input type='hidden' name='pid' value='$postid' />\n";

	$out.= "<div class='spEditor'>\n";
	$out = apply_filters('sph_post_edit_top', $out, $postid, $a);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend>$labelHeading</legend>\n";

	# Display the selected editor
    $tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(1, str_replace('&', '&amp;', $postcontent));
	$tout.= '</div>'."\n";
    $out.= apply_filters('sph_post_editor_content', $tout, $spThisTopic, $postid, $a);

    # allow plugins to insert stuff before editor footer
	$out = apply_filters('sph_post_before_editor_footer', $out, $spThisTopic, $postid, $a);

	# define area above toolbar for plugins to add components
    $section = apply_filters('sph_post_editor_edit_above_toolbar', '', $spThisTopic, $a);
    if (!empty($section)) {
        $tout = '';
    	$tout.= '<div class="spEditorSection">';
        $tout.= $section;
    	$tout.= '</div>'."\n";
        $out.= apply_filters('sph_post_editor_edit_above_toolbar_end', $tout, $spThisTopic, $a);
    }

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= "<div class='spClear'></div>\n";
	$out.= "<div id='spPostNotifications'>$failmessage</div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
    $toolbarRight = apply_filters('sph_post_editor_edit_toolbar_submit', '', $spThisTopic, $a, 'toolbar');
    $toolbarLeft = apply_filters('sph_post_editor_toolbar_buttons', '', $spThisTopic, $a, 'toolbar');

	if (!empty($toolbarRight) || !empty($toolbarLeft)) {
		# Submit section
		$tout = '';
		$tout.= '<div class="spEditorSection spEditorToolbar">';
		$tout.= $toolbarRight;

	   # toolbar for plugins to add buttons
        $tout.= $toolbarLeft;
        $out.= apply_filters('sph_post_editor_toolbar', $tout, $spThisTopic, $a, 'toolbar');
		$out.= '<div style="clear:both"></div>';
		$out.= '</div>'."\n";
   }

	# let plugins add stuff at top of editor footer
    $tout = '';
	$tout = apply_filters('sph_post_edit_footer_top', $tout, $spThisTopic, $postid, $a);

	# smileys and options
	$tout = apply_filters('sp_post_editor_inline_footer', $tout, $spThisTopic, $a, 'inline');

	# let plugins add stuff at top of editor footer
	$tout = apply_filters('sph_post_edit_footer_bottom', $tout, $postid, $a);

    # plugins can remove or adjust whole footer
	$out.= apply_filters('sph_post_editor_footer', $tout, $spThisTopic, $a);

    # allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_post_after_editor_footer', $out, $spThisTopic, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if(!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_post_edit_submit_top', $out, $spThisTopic, $a);

	    # let plugins add/remove the controls area
	    $tout = apply_filters('sp_post_editor_edit_inline_submit', '', $spThisTopic, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_post_edit_submit_bottom', $tout, $spThisTopic, $a);
		$out.= '</div>'."\n";
	}

	$out.= '</fieldset>'."\n";

	$out = apply_filters('sph_post_edit_bottom', $out, $postid, $a);
	$out.= '</div>'."\n";
	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_post_editor_beneath', $out, $spThisTopic, $a);

	return $out;
}

?>