<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2013-09-27 13:23:37 -0700 (Fri, 27 Sep 2013) $
$Rev: 10753 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM PAGE
#	This file loads for forum pages only - Framework rendering functions
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_load_forum_scripts()
#
# Enqueue's necessary javascript and inline header script
# ------------------------------------------------------------------
function sp_load_forum_scripts() {
	global $spVars, $spThisUser, $spMobile, $spDevice;
	$footer = (sp_get_option('sfscriptfoot')) ? true : false;

	do_action('sph_scripts_start', $footer);

    # TEMPORARY - Use our own jquery.form until wp updates - http://core.trac.wordpress.org/ticket/23944
    wp_deregister_script('jquery-form');
    wp_register_script('jquery-form', SFJSCRIPT.'jquery.form.js', array('jquery'), false, $footer);
    wp_enqueue_script('jquery-form');

    $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'sp-forum-dev.js' : SFJSCRIPT.'sp-forum.js';
	sp_plugin_enqueue_script('spforum', $script, array('jquery', 'jquery-form'), false, $footer);

	$target = (isset($spVars['forumid'])) ? $spVars['forumid'] : 'global';
	$iframe = (sp_get_auth('can_use_iframes', $target, $spThisUser->ID)) ? 'no' : 'yes';

	$strings = array(
		'problem' 		=> sp_text('Unable to save'),
		'noguestname'	=> sp_text('No guest username entered'),
		'noguestemail'	=> sp_text('No guest email Entered'),
		'notopictitle'	=> sp_text('No topic title entered'),
		'nomath'		=> sp_text('Spam math unanswered'),
		'nocontent'		=> sp_text('No post content entered'),
		'rejected'		=> sp_text('This post is rejected because it contains embedded formatting, probably pasted in form MS Word or other WYSIWYG editor'),
		'iframe'		=> sp_text('This post contains an iframe which are disallowed'),
		'savingpost'	=> sp_text('Saving post'),
		'nosearch'		=> sp_text('No search text entered'),
		'allwordmin'	=> sp_text('Minimum number of characters that can be used for a search word is'),
		'somewordmin'	=> sp_text('Not all words can be used for the search as minimum word length is'),
		'wait'			=> sp_text('Please wait'),
        'deletepost'    => sp_text('Are you sure you want to delete this post?'),
        'deletetopic'   => sp_text('Are you sure you want to delete this topic?'),
        'topicdeleted'  => sp_text('Topic deleted'),
        'postdeleted'   => sp_text('Post deleted'),
        'markread'      => sp_text('All posts marked as read'),
        'pinpost'       => sp_text('Post pin status toggled'),
        'pintopic'      => sp_text('Topic pin status toggled'),
        'locktopic'     => sp_text('Topic lock status toggled'),
		'checkiframe'	=> $iframe
	);
	$strings = apply_filters('sph_forum_vars', $strings);

    sp_plugin_localize_script('spforum', 'sp_forum_vars', $strings);

    $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFCJSCRIPT.'sp-common-dev.js' : SFCJSCRIPT.'sp-common.js';
	sp_plugin_enqueue_script('spcommon', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-autocomplete', 'jquery-effects-slide'), false, $footer);

	if ((defined('SP_USE_PRETTY_CBOX') && SP_USE_PRETTY_CBOX == true) || !defined('SP_USE_PRETTY_CBOX')) {
        $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFCJSCRIPT.'checkboxes/prettyCheckboxes-dev.js' : SFCJSCRIPT.'checkboxes/prettyCheckboxes.js';
		sp_plugin_enqueue_script('jquery.checkboxes', $script, array('jquery'), false, $footer);
	}

    $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'print-this/printThis-dev.js' : SFJSCRIPT.'print-this/printThis.js';
	sp_plugin_enqueue_script('sfprintthis', $script, array('jquery'), false, $footer);

	# Dialog boxes and other jQuery UI components
    $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'msdropdown/msdropdown-dev.js' : SFJSCRIPT.'msdropdown/msdropdown.js';
	sp_plugin_enqueue_script('jquery.ui.msdropdown', $script, array('jquery', 'jquery-ui-core', 'jquery-ui-widget'), false, $footer);

	sp_plugin_enqueue_script('jquery-touch-punch', false, array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'), false, $footer);

    $script = (defined('SP_SCRIPTS_DEBUG') && SP_SCRIPTS_DEBUG) ? SFJSCRIPT.'mobile/mobilemenu-dev.js' : SFJSCRIPT.'mobile/mobilemenu.js';
	sp_plugin_enqueue_script('jquery.mobilemenu', $script, array('jquery'), false, $footer);

	sp_plugin_enqueue_script('jquery.tools', SFJSCRIPT.'jquery-tools/jquery.tools.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget'), false, $footer);

    # sp_platform_vars is not static so cannot be in combined js cache and cannote use localize script
    $tooltips = (defined('SP_TOOLTIPS')) ? SP_TOOLTIPS : true;
    $mobtheme = (defined('SP_MOBILE_THEME')) ? SP_MOBILE_THEME : false;
    $checkboxes = (defined('SP_USE_PRETTY_CBOX')) ? SP_USE_PRETTY_CBOX : false;
?>
    <script type='text/javascript'>
    /* <![CDATA[ */
    var sp_platform_vars = {
		"focus":"forum",
    	"mobile":"<?php echo($spMobile); ?>",
    	"device":"<?php echo($spDevice); ?>",
    	"tooltips":"<?php echo($tooltips); ?>",
    	"mobiletheme":"<?php echo($mobtheme); ?>",
    	"checkboxes":"<?php echo($checkboxes); ?>"
    };
    /* ]]> */
    </script>
<?php

    # tell plugins to enqueue their scripts
    do_action('sph_print_plugin_scripts', $footer);

	$combine_js = sp_get_option('combinejs');
    if ($combine_js) { # use compressed scripts
        sp_combine_plugin_script_files();
    } else { # use individual scripts
    	global $sp_plugin_scripts, $wp_scripts;
        if (!empty($sp_plugin_scripts)) {
            foreach ($sp_plugin_scripts->queue as $handle) {
                # enqueue with wp
                $f = (empty($sp_plugin_scripts->registered[$handle]->extra['group']) || $sp_plugin_scripts->registered[$handle]->extra['group']==0) ? false : true;
                $plugin_footer = (is_array($sp_plugin_scripts->registered[$handle]->extra) && $f == 1) ? true : false;
        		wp_enqueue_script($handle, $sp_plugin_scripts->registered[$handle]->src, $sp_plugin_scripts->registered[$handle]->deps, false, $plugin_footer);

                # too late to register script since already formatted - so just set the wp script data equal it our localized data
                $data = $sp_plugin_scripts->get_data($handle, 'data');
                $wp_scripts->registered[$handle]->extra['data'] = $data;
            }
        }
    }

	do_action('sph_scripts_end', $footer);
}

function sp_load_plugin_styles() {
    $curTheme = sp_get_current_sp_theme(); # get optional color variant to pass to stylesheet
    $curTheme = apply_filters('sph_theme', $curTheme);

    # enqueue the main theme css
    $color = (!empty($curTheme['color'])) ? '?color='.esc_attr($curTheme['color']) : '';
    if (sp_is_plugin_active('user-selection/sp-user-selection-plugin.php')) {
        wp_enqueue_style('sp-theme-css', SPTHEMECSS.$color);
    } else {
        sp_plugin_enqueue_style('sp-theme-css', SPTHEMECSS.$color);
    }

    # concat (if needed) and enqueue the plugin css
    do_action('sph_print_plugin_styles');

	$combine_css = sp_get_option('combinecss');
    if ($combine_css) {
        sp_combine_plugin_css_files();
    } else {
    	global $sp_plugin_styles;
        if (!empty($sp_plugin_styles)) {
            foreach ($sp_plugin_styles->queue as $handle) {
        		wp_enqueue_style($handle, $sp_plugin_styles->registered[$handle]->src);
            }
        }
    }

	do_action('sph_styles_end');
}

# ------------------------------------------------------------------
# sp_forum_header()
#
# Constructs the header for the forum - Javascript and CSS
# ------------------------------------------------------------------
function sp_forum_header() {
	global $wp_query, $spGlobals, $spVars, $spStatus, $spMobile;

	do_action('sph_head_start');

	# So - check if it needs to be upgraded...
	if ($spStatus != 'ok') return;

	while ($x = has_filter('the_content', 'wpautop')) {
		remove_filter('the_content', 'wpautop', $x);
	}
	remove_filter('the_content', 'convert_smilies');

	# do meta stuff
	sp_setup_meta_tags();

	# load page specific css
	if ($spVars['pageview'] == 'topic' || $spVars['pageview'] == 'forum') {
		# if setting for post content width apply word-wrap
		if ($spVars['postwidth'] > 0) {
			?>
			<style type="text/css">
			.spPostContent, .spPostContent p, .spPostContent pre, .spPostContent blockquote, .spPostContent table {
				max-width: <?php echo $spVars['postwidth']; ?>px !important;
				text-wrap: normal;
				word-wrap: break-word; }
			<?php do_action('sph_textwrap_css'); ?>
			</style>
			<?php
		}
	}

	do_action('sph_head_end');
}

# ------------------------------------------------------------------
# sp_forum_footer()
#
# Constructs the footer for the forum - Javascript
# ------------------------------------------------------------------

function sp_forum_footer() {
	global $spVars, $spThisUser, $spMobile;

	do_action('sph_footer_start');

	# wait for page load and run JS inits
	?>
	<script type="text/javascript">
		var jspf = jQuery.noConflict();
		jspf(document).ready(function() {
			<?php
			# Quicklinks selects
			?>
			jspf("#spQuickLinksForumSelect, #spQuickLinksTopicSelect").msDropDown();
			jspf('#spQuickLinksForum').show();
			jspf('#spQuickLinksTopic').show();
			<?php
			# Checkboxes/radio buttons and tooltips
			if ((defined('SP_USE_PRETTY_CBOX') && SP_USE_PRETTY_CBOX==true) || !defined('SP_USE_PRETTY_CBOX')) { ?>
				jspf("input[type=checkbox],input[type=radio]").prettyCheckboxes();
			<?php } ?>

            <?php if (!$spMobile) { ?>
                jspf(function(jspf){vtip();})
            <?php } ?>

			<?php
			# Sets cookies with content and paragraph widths
			$docookie = true;
			$sfpostwrap = array();
			$sfpostwrap = sp_get_option('sfpostwrap');
			if ($sfpostwrap['postwrap'] == false) $docookie = false;
			if ($spVars['postwidth'] > 0) $docookie = false;
			if ($spVars['pageview'] != 'topic') $docookie = false;
			if ($spThisUser->admin == false) $docookie = false;

			if ($docookie) { ?>
				var c = jspf(".spPostContent").width();
				var p = jspf(".spPostContent p").width();
				if(c && p) {
					jspf.cookie('c_width', c, { path: '/' });
					jspf.cookie('p_width', p, { path: '/' });
				}
			<?php } ?>

			<?php
			# pre-load 'wait' image
			?>
				waitImage = new Image(32,32);
				waitImage.src = '<?php echo sp_find_icon(SPFIMAGES,'sp_Wait.png'); ?>';
				successImage = new Image(32,32);
				successImage.src = '<?php echo sp_find_icon(SPFIMAGES,'sp_Success.png'); ?>';
				failureImage = new Image(32,32);
				failureImage.src = '<?php echo sp_find_icon(SPFIMAGES,'sp_Failure.png'); ?>';
			<?php

			# check if this is a redirect from a failed save
			if ($spVars['pageview'] == 'topic' || $spVars['pageview'] == 'forum') { ?>
				if(jspf('#spPostNotifications').html() != null) {
					if(jspf('#spPostNotifications').html() != '') {
						jspf('#spPostNotifications').show();
						spjOpenEditor('spPostForm', 'post');
					}
				}
			<?php }

			# turn on auto update of required
			$sfauto = array();
			$sfauto = sp_get_option('sfauto');
			if ($sfauto['sfautoupdate']) {
				$timer = ($sfauto['sfautotime'] * 1000);

				$autoup = sp_get_sfmeta('autoupdate');
				$arg = '';
				foreach ($autoup as $up) {
					$list = implode($up['meta_value'], ',');
					$list .= '&amp;sfnonce='.wp_create_nonce('forum-ahah');
					if ($arg != '') $arg.= '%';
					$arg.= $list;
				}
				?>
				spjAutoUpdate("<?php echo $arg; ?>", "<?php echo $timer; ?>");
			<?php } ?>
			<?php
			?>
		});
	</script>
	<?php

	do_action('sph_footer_end');
}

# ------------------------------------------------------------------
# sp_render_forum()
#
# Central Control of forum rendering
# Called by the_content filter
#	$content:	The page content
# ------------------------------------------------------------------
function sp_render_forum($content) {
	global $spIsForum, $spContentLoaded, $spVars, $spGlobals, $spThisUser, $spStatus;

	# make sure we are at least in the html body before outputting any content
	if (!sp_get_option('sfwpheadbypass') && !did_action('wp_head')) return '';

	if ($spIsForum && !post_password_required(get_post(sp_get_option('sfpage')))) {
       # Limit forum display to within the wp loop?
    	if (sp_get_option('sfinloop') && !in_the_loop()) return $content;

		# Has forum content already been loaded and are we limiting?
		if (!sp_get_option('sfmultiplecontent') && $spContentLoaded) return $content;
		$spContentLoaded = true;

		sp_set_server_timezone();

        # offer a way for forum display to be short circuited
        $message = sp_abort_display_forum();
        if (!empty($message)) return $message;

#-----------------------------------------------

#-----------------------------------------------

		if (isset($_GET['mark'])) sp_remove_from_waiting(true, $spVars['topicid'], 0);
		if (isset($_POST['editpost'])) sp_save_edited_post();
		if (isset($_POST['edittopic'])) sp_save_edited_topic();
		if (isset($_POST['ordertopicpins'])) sp_promote_pinned_topic();
		if (isset($_POST['makepostreassign'])) sp_reassign_post();
		if (isset($_POST['approvepost'])) sp_approve_post(false, sp_esc_int($_POST['approvepost']), $spVars['topicid']);
		if (isset($_POST['unapprovepost'])) sp_unapprove_post(sp_esc_int($_POST['unapprovepost']));
		if (isset($_POST['doqueue'])) sp_remove_waiting_queue();
		if (isset($_POST['notifyuser'])) sp_post_notification(sp_esc_str($_POST['sp_notify_user']), sp_esc_str($_POST['message']), sp_esc_int($_POST['postid']));

		# move a topic and redirect to that topic
		if (isset($_POST['maketopicmove'])) {
            sp_move_topic();
           	$forumslug = spdb_table(SFFORUMS, 'forum_id='.sp_esc_int(sp_esc_int($_POST['forumid'])), 'forum_slug');
           	$topicslug = spdb_table(SFTOPICS, 'topic_id='.sp_esc_int(sp_esc_int($_POST['currenttopicid'])), 'topic_slug');
            $returnURL = sp_build_url($forumslug, $topicslug, 0);
            sp_redirect($returnURL);
        }

		# move a post and redirect to the post
		if (isset($_POST['makepostmove1']) || isset($_POST['makepostmove2']) || isset($_POST['makepostmove3'])) {
            sp_move_post();
            if (isset($_POST['makepostmove1'])) {
	            $returnURL = sp_permalink_from_postid(sp_esc_int($_POST['postid']));
    	        sp_redirect($returnURL);
    	    }
        }
        if (isset($_POST['cancelpostmove'])) {
        	$meta = sp_get_sfmeta('post_move', 'post_move');
        	if($meta) {
        		$id = $meta[0]['meta_id'];
        		sp_delete_sfmeta($id);
        		unset($spGlobals['post_move']);
        	}
        }

		# rebuild the forum and post indexes
		if (isset($_POST['rebuildforum']) || isset($_POST['rebuildtopic'])) {
			sp_build_post_index(sp_esc_int($_POST['topicid']), true);
			sp_build_forum_index(sp_esc_int($_POST['forumid']), false);
		}

		# Set display mode if topic view (for editing posts)
		if ($spVars['pageview'] == 'topic' && isset($_POST['postedit'])) {
			$spVars['displaymode'] = 'edit';
			$spVars['postedit'] = $_POST['postedit'];
		} else {
			$spVars['displaymode'] = 'posts';
		}

#-----------------------------------------------

#-----------------------------------------------

		# let other plugins check for posted actions
		do_action('sph_setup_forum');

		# do we use output buffering?
		$ob = sp_get_option('sfuseob');
		if ($ob) ob_start();

		# set up some stuff before wp page content
		$content.= sp_display_banner();
   		$content = apply_filters('sph_before_wp_page_content', $content);

        # run any other wp filters on page content but exclude ours
		if (!$ob) {
            remove_filter('the_content', 'sp_render_forum', 1);
    		$content = apply_filters('the_content', $content);
            $content = wpautop($content);
            add_filter('the_content', 'sp_render_forum', 1);
        }

        # set up some stuff after wp page content
   		$content = apply_filters('sph_after_wp_page_content', $content);
		$content.= '<div id="dialogcontainer" style="display:none;"></div>';
		$content.= sp_js_check();

        # echo any wp page content
        echo $content;

        # now add our content
		do_action('sph_before_template_processing');
		sp_process_template();
		do_action('sph_after_template_processing');

		# Return if using output buffering
		if ($ob) {
			$forum = ob_get_contents();
			ob_end_clean();
			return $forum;
		}
	}

    # not returning any content since we output it already unless password needed
    if (post_password_required(get_post(sp_get_option('sfpage')))) return $content;
}

?>