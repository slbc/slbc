<?php
/*
Simple:Press
Post Form Rendering
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function sp_render_add_post_form($a) {
	global $spVars, $spThisUser, $spThisTopic, $spGuestCookie, $spGlobals;

	include_once(SF_PLUGIN_DIR.'/forum/content/forms/sp-form-components.php');

	$toolbar = $spGlobals['display']['editor']['toolbar'];

	extract($a, EXTR_SKIP);

	# Check for a failure package in case this is a redirect
	$f = sp_get_transient(1, true);
	if (isset($f['guestname']) ? $guestnameval = esc_attr(stripslashes($f['guestname'])) : $guestnameval = $spGuestCookie->guest_name);
	if (isset($f['guestemail']) ? $guestemailval = esc_attr(stripslashes($f['guestemail'])) : $guestemailval = $spGuestCookie->guest_email);
	if (isset($f['postitem']) ? $postitemval = stripslashes($f['postitem']) : $postitemval = '');
	if (isset($f['message']) ? $failmessage = stripslashes($f['message']) : $failmessage = '');

	$out = '';

	# Grab above editor message if there is one
	$postmsg = sp_get_option('sfpostmsg');

	if ($hide ? $hide = ' style="display:none;"' : $hide = '');
	$out.= '<div id="spPostForm"'.$hide.'>'."\n";

	$out.= "<form class='$tagClass' action='".SFHOMEURL."index.php?sp_ahah=post&amp;sfnonce=".wp_create_nonce('forum-ahah')."' method='post' id='addpost' name='addpost' onsubmit='return spjValidatePostForm(this, $spThisUser->guest, 0, \"".SPTHEMEICONSURL.'sp_Success.png'."\");'>\n";
	$out.= sp_create_nonce('forum-userform_addpost');

	$out.= '<div class="spEditor">'."\n";
	$out = apply_filters('sph_post_editor_top', $out, $spThisTopic, $a);

	$out.= "<fieldset class='$controlFieldset'>\n";
	$out.= "<legend>$labelHeading: ".$spThisTopic->topic_name."</legend>\n";

	$out.= "<input type='hidden' name='action' value='post' />\n";

	$out.= "<input type='hidden' name='forumid' value='$spThisTopic->forum_id' />\n";
	$out.= "<input type='hidden' name='forumslug' value='$spThisTopic->forum_slug' />\n";
	$out.= "<input type='hidden' name='topicid' value='$spThisTopic->topic_id' />\n";
	$out.= "<input type='hidden' name='topicslug' value='$spThisTopic->topic_slug' />\n";

    # plugins can add before the header
	$out = apply_filters('sph_post_before_editor_header', $out, $spThisTopic, $a);

    $tout = '';
	$close = false;
	if (!empty($postmsg['sfpostmsgpost']) || $spThisUser->guest || !sp_get_auth('bypass_moderation', $spThisTopic->forum_id) || !sp_get_auth('bypass_moderation_once', $spThisTopic->forum_id)) {
		$tout.= '<div class="spEditorSection">';
		$close = true;
	}

	# let plugins add stuff at top of editor header
	$tout = apply_filters('sph_post_editor_header_top', $tout, $spThisTopic, $a);

	if (!empty($postmsg['sfpostmsgpost'])) {
		$tout.= '<div class="spEditorMessage">'.sp_filter_text_display($postmsg['sfpostmsgtext']).'</div>'."\n";
	}

	if ($spThisUser->guest) {
		$tout.= '<div class="spEditorSectionLeft">'."\n";
		$tout.= "<div class='spEditorTitle'>$labelGuestName:\n";
		$tout.= "<input type='text' tabindex='100' class='$controlInput' name='guestname' id='guestname' value='$guestnameval' /></div>\n";
		$tout.= '</div>'."\n";
		$sfguests = sp_get_option('sfguests');
		if ($sfguests['reqemail']) {
			$tout.= '<div class="spEditorSectionRight">'."\n";
			$tout.= "<div class='spEditorTitle'>$labelGuestEmail:\n";
			$tout.= "<input type='text' tabindex='101' class='$controlInput' name='guestemail' id='guestemail' value='$guestemailval' /></div>\n";
			$tout.= '</div>'."\n";
		}
		$tout.= '<div class="spClear"></div>'."\n";
	}

	if (!sp_get_auth('bypass_moderation', $spThisTopic->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateAll</p>\n";
	} elseif (!sp_get_auth('bypass_moderation_once', $spThisTopic->forum_id)) {
		$tout.= "<p class='spLabelSmall'>$labelModerateOnce</p>\n";
	}

	# let plugins add stuff at bottom of editor header
	$tout = apply_filters('sph_post_editor_header_bottom', $tout, $spThisTopic, $a);
	if ($close) $tout.= '</div>'."\n";

    # allow plugins to filter just the header
    $out.= apply_filters('sph_post_editor_header', $tout, $spThisTopic, $a);

	# Display the selected editor
    $tout = '';
	$tout.= '<div id="spEditorContent">'."\n";
	$tout.= sp_setup_editor(103, $postitemval);
	$tout.= '</div>'."\n";

    # allow plugins to filter the editor content
    $out.= apply_filters('sph_post_editor_content', $tout, $spThisTopic, $a);

	# define area above toolbar for plugins to add components
    $section = apply_filters('sph_post_editor_above_toolbar', '', $spThisTopic, $a);
    if (!empty($section)) {
        $tout = '';
    	$tout.= '<div class="spEditorSection">';
        $tout.= $section;
    	$tout.= '</div>'."\n";
        $out.= apply_filters('sph_post_editor_above_toolbar_end', $tout, $spThisTopic, $a);
    }

	# DEFINE NEW FAILURE AREA HERE

	# define validation failure notice area
	$out.= "<div class='spClear'></div>\n";
	$out.= "<div id='spPostNotifications'>$failmessage</div>\n";

	# TOOLBAR

	# define toolbar - submit buttons on right, plugin extensions on left
    $toolbarRight = apply_filters('sph_post_editor_toolbar_submit', '', $spThisTopic, $a, 'toolbar');
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

	# START SMILEYS/OPTIONS

	# let plugins add stuff at top of editor footer
    $tout = '';
	$tout = apply_filters('sph_post_editor_footer_top', $tout, $spThisTopic, $a);

	# smileys and options
	$tout = apply_filters('sp_post_editor_inline_footer', $tout, $spThisTopic, $a, 'inline');

	# let plugins add stuff at end of editor footer
	$tout = apply_filters('sph_post_editor_footer_bottom', $tout, $spThisTopic, $a);

    # plugins can remove or adjust whole footer
	$out.= apply_filters('sph_post_editor_footer', $tout, $spThisTopic, $a);

    # allow plugins to insert stuff after editor footer
	$out = apply_filters('sph_post_editor_after_footer', $out, $spThisTopic, $a);

	# START SUBMIT SECTION

	# define submit section of no toolbar in use
	if (!$toolbar) {
		$out.= '<div class="spEditorSubmit">'."\n";
		$out = apply_filters('sph_post_editor_submit_top', $out, $spThisTopic, $a);

	    # let plugins add/remove the controls area
	    $tout = apply_filters('sp_post_editor_inline_submit', '', $spThisTopic, $a, 'inline');

		# let plugins add stuff at end of editor submit bottom
		$out.= apply_filters('sph_post_editor_submit_bottom', $tout, $spThisTopic, $a);
		$out.= '</div>'."\n";
	}

    # close it up
	$out.= '</fieldset>'."\n";
	$out = apply_filters('sph_post_editor_bottom', $out, $spThisTopic, $a);
	$out.= '</div>'."\n";
	$out.= '</form>'."\n";
	$out.= '</div>'."\n";

	# let plugins add stuff beneath the editor
	$out = apply_filters('sph_post_editor_beneath', $out, $spThisTopic, $a);

	return $out;
}

?>