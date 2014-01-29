<?php
/*
Simple:Press
Filters
$LastChangedDate: 2013-09-18 23:24:05 -0700 (Wed, 18 Sep 2013) $
$Rev: 10705 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Data Save and Display Filter Library
#
# ===FILTERS - USEAGE ==============================================
#
#	sp_filter_content_save($content, $action)
#	sp_filter_content_display($content)
#	sp_filter_content_edit($content)
#		Used for main post/message content so includes html, images,
#		profanity etc.
#
#	sp_filter_text_save($content)
#	sp_filter_text_display($content)
#	sp_filter_text_edit($content)
#		Used for larger areas of text where html is allowed like
#		admin defined message areas etc.
#
#	sp_filter_title_save($content)
#	sp_filter_title_display($content)
#		Used for title text where no html allowed such as forum
#		titles, custom labels and links etc.
#
#	sp_filter_name_save($content)
#	sp_filter_name_display($content)
#		Used for user names such as guest name and display name etc.
#
#	sp_filter_email_save($email)
#	sp_filter_email_display($email)
#		Used for email addresses
#
#	sp_filter_url_save($url)
#	sp_filter_url_display($url)
#		Used for URLs
#
#	sp_filter_filename_save($filename)
#		used for all filenames - i.e., custom icons etc
#
#	sp_filter_signature_display($content)
#		special for siganture display
#
#	sp_filter_tooltip_display($content)
#		special for post tooltips
#
#	sp_filter_rss_display($content)
#		Used for post content in rss feed
#
#	sp_filter_table_prefix($content)
#		removes prefix from tablename in searches
#
# ==================================================================

# ===START OF SAVE FILTERS==========================================
# CONTENT - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Forum Post (including Quick Reply)
#		Private Messages
#		?
# $action will be 'new' or 'edit'
function sp_filter_content_save($content, $action, $doEsc=true) {
	global $spGlobals, $spVars;

    #save unedited content
    $original = $content;

	$sffilters = sp_get_option('sffilters');

	# 1: Swap smileys for img tags. Do it early before kses
	# NOTE Was a display filter - still needs to be a
	# display filter for backward compatibility.
	$content = sp_filter_display_smileys($content);

    # 2: prepare edits - editor specific filter
	if ($action == 'edit') {
		if (function_exists('sp_editor_prepare_edit_content')) $content = sp_editor_prepare_edit_content($content, $spGlobals['editor']);
	}

	# 3: convert code tags to our own code display tags and parse for inine bbCode
	$content = sp_filter_save_codetags1($content, $spGlobals['editor'], $action);

	# 4: run it through kses
	$content = sp_filter_save_kses($content);

	# 5: remove nbsp and p/br tags
	$content = sp_filter_save_linebreaks($content);

	# 6: revist code tags in case post edit save
	$content = sp_filter_save_codetags2($content, $spGlobals['editor'], $action);

	# 7: remove 'pre' tags (optional)
	if ($sffilters['sffilterpre']) $content = sp_filter_save_pre($content);

	# 8: deal with single quotes (tinymce encodes them)
	$content = sp_filter_save_quotes($content);

	# 9: balance html tags
	$content = sp_filter_save_balancetags($content);

	# 10: escape it All
	if($doEsc) $content = sp_filter_save_escape($content);

	# 11: strip spoiler shortcode if not allowed
    $fid = (isset($spVars['forumid'])) ? $spVars['forumid'] : '';
	if (!sp_get_auth('use_spoilers', $fid)) $content = sp_filter_save_spoiler($content);

	# 12: Try and determine images widths if not set
	$content = sp_filter_save_images($content);

	# 13: apply any users custom filters
	$content = apply_filters('sph_save_post_content_filter', $content, $original, $action);
	return $content;
}

# ==================================================================
# TEXT - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Profile Description
#		Group Message
#		Forum Message
#		Email Messages
#		Signature Text
#		Sneak Peak Message
#		Admin View Message
#		Custom Editor Messages
#		Registration/Privacy Messages
#		Custom Profile Message
#		Admins Off-Line Message
function sp_filter_text_save($content) {
    #save unedited content
    $original = $content;

	# Decode the entities first that were applied for display
	$content = html_entity_decode($content, ENT_COMPAT, SFCHARSET);

	# 1: run it through kses
	$content = sp_filter_save_kses($content);

	# 2: remove nbsp and p/br tags
	$content = sp_filter_save_linebreaks($content);

	# 3: deal with single quotes (tinymce encodes them)
	$content = sp_filter_save_quotes($content);

	# 4: balance html tags
	$content = sp_filter_save_balancetags($content);

	# 5: escape it All
	$content = sp_filter_save_escape($content);

	# 6: apply any users custom filters
	$content = apply_filters('sph_save_text_filter', $content, $original);

	return $content;
}

# ==================================================================
# TITLE - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Group Title/Description
#		Forum Title/Description
#		Topic Title
#		Message Title
#		Email Subject
#		Custom Meta Description/Keywords
#		Custom Icon Title
#		UserGroup Name/Description
#		Permission Name/Description
#		Profile Form Labels
function sp_filter_title_save($content) {
    #save unedited content
    $original = $content;

	# 1: remove all html
	$content = sp_filter_save_nohtml($content);

	# 2: encode brackets
	$content = sp_filter_save_brackets($content);

	# 3: escape it All
	$content = sp_filter_save_escape($content);

	# 4: apply any users custom filters
	$content = apply_filters('sph_save_title_filter', $content, $original);

	return $content;
}

# ==================================================================
# USER NAMES - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Display Name
#		Guest Name
function sp_filter_name_save($content) {
    #save unedited content
    $original = $content;

	# 1: Remove any html
	$content = sp_filter_save_nohtml($content);

	# 2: Encode
	$content = sp_filter_save_encode($content);

	# 3: escape it
	$content = sp_filter_save_escape($content);

	# 4: apply any users custom filters
	$content = apply_filters('sph_save_name_filter', $content, $original);

	return $content;
}

# ==================================================================
# EMAIL ADDRESS - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Guest posts
#		User profile
function sp_filter_email_save($email) {
    #save unedited content
    $original = $email;

	# 1: Remove any html
	$email = sp_filter_save_nohtml($email);

	# 2: Validate and Sanitize Email
	$email = sp_filter_save_cleanemail($email);

	# 3: escape it
	$email = sp_filter_save_escape($email);

	# 4: apply any users custom filters
	$email = apply_filters('sph_save_email_filter', $email, $original);

	return $email;
}

# ==================================================================
# URL - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used: All URLs
function sp_filter_url_save($url) {
    #save unedited content
    $original = $url;

	# 1: clean up url for database
	$url = sp_filter_save_cleanurl($url);

	# 2: apply any users custom filters
	$url = apply_filters('sph_save_url_filter', $url, $original);

	return $url;
}

# ==================================================================
# FILENAME - SAVE FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Avatar Upload
#		Avatar Pool
#		Signature Image
#		Custom Icons
#		Smileys
#		Editor Stylesheets
#		Registration/Privacy Documents
function sp_filter_filename_save($filename) {
    #save unedited content
    $original = $filename;

	# 1: clean up filename
	$filename = sp_filter_save_filename($filename);

	# 2: apply any users custom filters
	$filename = apply_filters('sph_save_filename_filter', $filename, $original);

	return $filename;
}

# ===START OF SAVE FILTERS==========================================
# ------------------------------------------------------------------
# sp_filter_save_codetags1()
#
# Version: 5.0
# Try and change code tags to our code divs
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_codetags1($content, $editor, $action) {
    #save unedited content
    $original = $content;

	if (function_exists('sp_editor_parse_codetags')) $content = sp_editor_parse_codetags($content, $editor, $action);

	# Parse for inline entered bbCode (popular with spammers)
	$content = sp_parse_inline_bbcode($content);

	# Shouldn't need any of these but there just in case...
	$content = str_replace('<code>', '<div class="sfcode">', $content);
	$content = str_replace('</code>', '</div>', $content);
	$content = str_replace('&lt;code&gt;', '<div class="sfcode">', $content);
	$content = str_replace('&lt;/code&gt;', '</div>', $content);

	$content = apply_filters('sph_save_codetags1_filter', $content, $original, $editor, $action);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_codetags2()
#
# Version: 5.0
# May be post edit save - so remove br's
#	$content:		Unfiltered post content
#	$editor:		Which editor
#	$action:		'new' or 'edit'
# ------------------------------------------------------------------
function sp_filter_save_codetags2($content, $editor, $action) {
    #save unedited content
    $original = $content;

	# check if syntax highlighted - if so not needed
	if (strpos($content, 'class="brush')) return $content;

	# ONLY used for a TintMCE RichText Save 'Edit'
	if ($action == 'edit') {
		if (function_exists('sp_editor_save_codetags')) $content = sp_editor_save_codetags($content, $editor);
	}

	$content = apply_filters('sph_save_codetags2_filter', $content, $original, $editor, $action);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_kses()
#
# Version: 5.0
# Run it through kses - needs to be unescaped first
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_kses($content) {
	global $allowedforumtags, $allowedforumprotocols;

    #save unedited content
    $original = $content;

	if (!isset($allowedforumtags)) {
		sp_kses_array();
		$allowedforumtags = apply_filters('sph_custom_kses', $allowedforumtags);
	}

	$content = wp_kses(stripslashes($content), $allowedforumtags, $allowedforumprotocols);

	$content = apply_filters('sph_save_kses_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_linebreaks()
#
# Version: 5.0
# Swap tinymce constructs with br's
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_linebreaks($content) {
    #save unedited content
    $original = $content;

	$gap ='<p>'.chr(194).chr(160).'</p>'.chr(13).chr(10);
	$end ='<p>'.chr(194).chr(160).'</p>';

	# trim unwanted empty space
	$content = trim($content);

	while (substr($content, 0, 11) == $gap) {
		$content = substr_replace($content, '', 0, 11);
	}

	while (substr($content, (strlen($content)-9), 9) == $end) {
		$content = substr_replace($content, '', (strlen($content)-9), 9);
	}

	while (substr($content, (strlen($content)-11), 11) == $gap) {
		$content = substr_replace($content, '', (strlen($content)-11), 11);
	}

	# On savibng edit a 'br' may have a trailng line break which
	# will display like a paragraph break
	$content = str_replace('<br />'.chr(13).chr(10), "\n", $content);

	# change br's to linebreaks
	$content = str_replace('<br />', "\n", $content);

	# change tiny blank line to a newline
	$content = str_replace($gap.$gap, $gap, $content);

	# same for blank line with p tags
	$content = str_replace('<p></p>', "\n\n", $content);
	$content = str_replace('<p> </p>', "\n\n", $content);
	$content = str_replace('<p>', '', $content);
	$content = str_replace('</p>', chr(13).chr(10), $content);

	$content = apply_filters('sph_save_linebreaks_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_pre()
#
# Version: 5.0
# Remove html 'pre' and '/pre' tags
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_pre($content) {
    #save unedited content
    $original = $content;

	# remove pre tags
	$content = str_replace('<pre>', '', $content);
	$content = str_replace('</pre>', '', $content);

	$content = str_replace('&lt;pre&gt;', '', $content);
	$content = str_replace('&lt;/pre&gt;', '', $content);

	$content = apply_filters('sph_save_pre_filter', $content);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_quotes()
#
# Version: 5.0
# Turn encoded single quote back
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_quotes($content) {
    #save unedited content
    $original = $content;

	# Replace tinymce encoded single quotes with standard quotes
	$content = str_replace('&#39;', "'", $content);
	$content = str_replace('&#039;', "'", $content);

	# Replace those odd 0003 chars we have seen here and there
	$content = str_replace(chr(003), "'", $content);

    # ensure all img tags use double quotes
	$content = preg_replace_callback('/<img([^<>]+)>/', 'sp_filter_img_tags', $content);

	$content = apply_filters('sph_save_quotes_filter', $content, $original);

	return $content;
}

# Version: 5.0
function sp_filter_img_tags($matches) {
	return '<img '.str_replace("'", '"', $matches[1]).'>';
}

# ------------------------------------------------------------------
# sp_filter_save_balancetags()
#
# Version: 5.0
# Tried to balance html tags
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_balancetags($content) {
    #save unedited content
    $original = $content;

	$content = balanceTags($content, true);
	$content = apply_filters('sph_save_balancetags_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_nofollow()
#
# Version: 5.0
# Adds nofollow to links at save post time
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_nofollow($content) {
    #save unedited content
    $original = $content;

	$content = preg_replace_callback('|<a (.+?)>|i', 'sp_nofollow_callback', $content);
	$content = apply_filters('sph_save_nofollow_filter', $content, $original);
	return $content;
}

# Version: 5.0
function sp_nofollow_callback($matches) {
	$text = $matches[1];
	$text = str_replace(array(' rel="nofollow"', " rel='nofollow'", 'rel="nofollow"', "rel='nofollow'"), '', $text);
	return '<a '.$text.' rel="nofollow">';
}

# ------------------------------------------------------------------
# sp_filter_save_target()
#
# Version: 5.0
# Forces target _blank to links at save post time
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_target($content) {
	$content = preg_replace_callback('|<a (.+?)>|i', 'sp_target_callback', $content);
	return $content;
}

# Version: 5.0
function sp_target_callback($matches) {
	$text = $matches[1];
	if (strpos($text, 'javascript:void(0)')) return "<a $text>";
	$text = str_replace(array(' target="_blank"', " target='_blank'", 'target="_blank"', "target='_blank'"), '', $text);
	return '<a '.$text.' target="_blank">';
}

# ------------------------------------------------------------------
# sp_filter_save_links()
#
# Version: 5.0
# Turns urtls in posts to clickable links with shortened text
#	$content:		Unfiltered post content
# Thanks to Peter at http://www.theblog.ca/shorten-urls for this
# ------------------------------------------------------------------
function sp_filter_save_links($content, $charcount) {
    #save unedited content
    $original = $content;

	$content = sp_make_clickable($content);

	# pad it with a space
	$content = ' '.$content;

	# chunk those long urls
	sp_format_links($content, $charcount);

	$content = preg_replace("#(\s)([a-z0-9\-_.]+)@([^,< \n\r]+)#i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $content);

	# Remove our padding..
	$content = substr($content, 1);

	$content = apply_filters('sph_save_links_filter', $content, $original, $charcount);

	return($content);
}

# Version: 5.0
function sp_format_links(&$content, $charcount) {
	$links = explode('<a', $content);
	$countlinks = count($links);
	for ($i = 0; $i < $countlinks; $i++) {
		$link = $links[$i];
		$link = (preg_match('#(.*)(href=")#is', $link)) ? '<a'.$link : $link;
		$begin = strpos($link, '>') + 1;
		$end = strpos($link, '<', $begin);
		$length = $end - $begin;
		$urlname = substr($link, $begin, $length);

		# We chunk urls that are longer than 50 characters. Just change
		# '50' to a value that suits your taste. We are not chunking the link
		# text unless if begins with 'http://', 'ftp://', or 'www.'
		$chunked = (strlen($urlname) > $charcount && preg_match('#^(http://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', ($charcount - 10), -10) : $urlname;
		$content = str_replace('>'.$urlname.'<', '>'.$chunked.'<', $content);
	}
}

# ------------------------------------------------------------------
# sp_filter_save_nohtml()
#
# Version: 5.0
# Remove unwanted html
#	$title:		Unfiltered title content
# ------------------------------------------------------------------
function sp_filter_save_nohtml($content) {
    #save unedited content
    $original = $content;

	$content = wp_kses(stripslashes($content), array());
	$content = apply_filters('sph_save_nohtml_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_brackets()
#
# Version: 5.0
# Remove square brackets from titles
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_brackets($content) {
    #save unedited content
    $original = $content;

	$content = str_replace('[', '&#091;', $content);
	$content = str_replace(']', '&#093;', $content);
	$content = apply_filters('sph_save_brackets_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_escape()
#
# Version: 5.0
# escape content before saving
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_escape($content) {
    #save unedited content
    $original = $content;

	$content = esc_sql($content);
	$content = apply_filters('sph_save_escape_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_filename()
#
# Version: 5.0
# Sanitizes a filename and makes it safe
#	$filename:		Unfiltered file name
# ------------------------------------------------------------------
function sp_filter_save_filename($filename) {
	$filename_raw = $filename;
	$special_chars = array('?', '[', ']', '/', "\\", '=', '<', '>', ':', ';', ',', "'", "\"", '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', chr(0));
	$filename = str_replace($special_chars, '', $filename);
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = trim($filename, '.-_');

	# Split the filename into a base and extension[s]
	$parts = explode('.', $filename);

	# Return if only one extension
	if ( count($parts) <= 2 ) return $filename;

	# Process multiple extensions
	$filename = array_shift($parts);
	$extension = array_pop($parts);
	$mimes =  get_allowed_mime_types();

	# Loop over any intermediate extensions.  Munge them with a trailing underscore if they are a 2 - 5 character
	# long alpha string not in the extension whitelist.
	foreach ( (array) $parts as $part) {
		$filename .= '.'.$part;
		if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
			$allowed = false;
			foreach ($mimes as $ext_preg => $mime_match) {
				$ext_preg = '!(^'.$ext_preg.')$!i';
				if (preg_match($ext_preg, $part)) {
					$allowed = true;
					break;
				}
			}
			if (!$allowed) $filename .= '_';
		}
	}
	$filename = str_replace(' ', '_', $filename);
	$filename.= '.'.$extension;
	return $filename;
}

# ------------------------------------------------------------------
# sp_filter_save_encode()
#
# Version: 5.0
# Encode atributes
#	$content:		usually a display name
# ------------------------------------------------------------------
function sp_filter_save_encode($content) {
    #save unedited content
    $original = $content;

	$content = esc_attr($content);
	$content = apply_filters('sph_save_encode_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_cleanemail()
#
# Version: 5.0
# Sanitizes am email address and makes it safe
#	$filename:		Unfiltered file name
# ------------------------------------------------------------------
function sp_filter_save_cleanemail($email) {
	$email = sanitize_email($email);
	return $email;
}

# ------------------------------------------------------------------
# sp_filter_save_cleanurl()
#
# Version: 5.0
# Sanitizes an url for db  and makes it safe
#	$url:		Unfiltered url
# ------------------------------------------------------------------
function sp_filter_save_cleanurl($url) {
	$url = esc_url_raw($url);
	return $url;
}

# ------------------------------------------------------------------
# sp_filter_save_spoiler() and support functions
#
# Version: 5.0
# Remove spoilers from content if not allowed
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_spoiler($content) {
    #save unedited content
    $original = $content;

    $content = preg_replace('/\[spoiler\][^>]*\[\/spoiler\]/', '' , $content);

	$content = apply_filters('sph_save_spoiler_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_images() and support functions
#
# Version: 5.0
# Set the wiodth of images if possible at save time
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_save_images($content) {
    #save unedited content
    $original = $content;

	return sp_check_save_image_width($content);
	$content = apply_filters('sph_save_images_filter', $content, $original);
}

# Version: 5.0
function sp_check_save_image_width($content) {
	$content = preg_replace_callback('/<img[^>]*>/', 'sp_check_save_width' , $content);
	return $content;
}

# Version: 5.0
function sp_check_save_width($match) {
	global $spPaths;

	$out = '';
	$match[0] = stripslashes($match[0]);

	preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
	preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);
	preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
	preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
	preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
	preg_match('/class\s*=\s*"([^"]*)"|class\s*=\s*\'([^\']*)\'/i', $match[0], $class);

	if(isset($width[1])) return $match[0];
	if (isset($class[1])) return $match[0];

	if ((strpos($src[1], 'plugins/emotions')) || (strpos($src[1], 'images/smilies')) || (strpos($src[1], $spPaths['smileys']))) {
		$out = str_replace('img src', 'img class="spSmiley" src', $match[0]);
		return $out;
	}

	# figure out whether its relative path (same server) or a url
    $parsed = @parse_url($src[1]);
    if($parsed == false) {
    	$srcfile = $src[1];
    } else {
		if (array_key_exists('scheme', $parsed)) {
			$srcfile = $src[1];  # url, so leave it alone
		} else {
			$srcfile = $_SERVER['DOCUMENT_ROOT'].$src[1];  # relative path, so add DOCUMENT_ROOT to path
		}
	}

	global $gis_error;
	$gis_error = '';
	set_error_handler('sp_gis_error');

	if (empty($width[1])) {
		$size = getimagesize(str_replace(' ', '%20', $srcfile));
		restore_error_handler();
		if ($gis_error == '') {
			if ($size[0]) {
				$width[1] = $size[0];
			} else {
				# If NOT using popup image enlargement then we can just return the raw image tag
				# But if not then we need to error as we do need that width
				$sfimage = array();
				$sfimage = sp_get_option('sfimage');
				if ($sfimage['enlarge'] == false) {
					return $match[0];
				} else {
					return '['.sp_text('Image Can Not Be Found').']';
				}
			}
		}
	}

	if (isset($src[1])) 	$thissrc = 		'src="'.$src[1].'" '; 		else $thissrc = '';
	if (isset($title[1])) 	$thistitle = 	'title="'.$title[1].'" '; 	else $thistitle = '';
	if (isset($alt[1])) 	$thisalt = 		'alt="'.$alt[1].'" '; 		else $thisalt = '';
	if (isset($width[1]))	$thiswidth =	'width="'.$width[1].'" ';	else $thiswidth = '';
	if (isset($style[1]))	$thisstyle =	'style="'.$style[1].'" ';	else $thisstyle = '';
	if (isset($class[1]))	$thisclass =	'class="'.$class[1].'" ';	else $thisclass = '';

	$out.= esc_sql('<img '.$thissrc.$thiswidth.$thisstyle.$thisclass.$thistitle.$thisalt.'/>');
	return $out;
}

# ===END OF SAVE FILTERS============================================

# ===START OF EDIT FILTERS==========================================
# CONTENT - EDIT FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Forum Post
function sp_filter_content_edit($content) {
	global $spGlobals;

    #save unedited content
    $original = $content;

	# 1: Convert smiley codes to images
	$content = sp_filter_display_smileys($content);

	# 2: Convert Chars
	$content = sp_filter_display_chars($content);

	# 3: Format the paragraphs (p and br onlt Richtext)
	if (function_exists('sp_editor_save_linebreaks')) {
		$content = sp_editor_save_linebreaks($content, $spGlobals['editor']);
	} else {
		$content = sp_filter_save_linebreaks($content);
	}

	if (function_exists('sp_editor_format_paragraphs_edit')) $content = sp_editor_format_paragraphs_edit($content, $spGlobals['editor']);

	# 4: Parse post into appropriate editor format
	$content = sp_filter_edit_parser($content, $spGlobals['editor']);

	# 5: Turn off shortcode processing to keep from messing up their display
	remove_all_shortcodes();

	$content = apply_filters('sph_edit_content_filter', $content, $original);

	return $content;
}

# ==================================================================
# TEXT - EDIT FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Text Areas
function sp_filter_text_edit($content) {
	global $spGlobals;

    #save unedited content
    $original = $content;

	# 1: Convert Chars
	$content = sp_filter_display_chars($content);

	# 2: Format the paragraphs (p and br)
	$content = sp_filter_display_paragraphs($content);
	$content = sp_filter_save_linebreaks($content);

	# 3: Parse post into appropriate editor format
	$content = sp_filter_edit_parser($content, $spGlobals['editor']);

	# 4: remove escape slashes
	$content = sp_filter_display_stripslashes($content);

	# 5: finally htnl encode it for edit display
	$content = htmlentities($content, ENT_COMPAT, SFCHARSET);

	$content = apply_filters('sph_edit_text_filter', $content, $original);

	return $content;
}

# Version: 5.0
function sp_filter_edit_parser($content, $editor) {
    #save unedited content
    $original = $content;

	if (function_exists('sp_editor_parse_for_edit')) $content = sp_editor_parse_for_edit($content, $editor);

	$content = apply_filters('sph_edit_parser_filter', $content, $original);

	return $content;
}


# ===END OF EDIT FILTERS============================================

# ===START OF DISPLAY FILTERS=======================================
# CONTENT - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Forum Post
#		Private Messages
#		Post Report
#		Template Tag
function sp_filter_content_display($content) {
    global $spVars;

    #save unedited content
    $original = $content;

	# apply any users custom filters for pre-content display processing
	$content = apply_filters('sph_display_post_content_pre_filter', $content);

	# 1: parse it for the wp oEmbed class
	if (get_option('embed_autourls')) $content = sp_filter_display_oEmbed($content);

	# 2: handle media embeds - video and audio urls as FIRST line of post
	# SEE NOTE IN FUNCTION REGARDING REACTIVATING THIS CALL
//	$content = sp_filter_display_media_embeds($content);

	# 3: make links clickable
	$content = sp_filter_display_links($content);

	# 4: format links (optional)
	$sffilters = sp_get_option('sffilters');
	if ($sffilters['sfurlchars']) $content = sp_filter_save_links($content, $sffilters['sfurlchars']);

	# 5: add nofollow to links (optional)
	if ($sffilters['sfnofollow']) $content = sp_filter_save_nofollow($content);

	# 6: add target blank (optional)
	if ($sffilters['sftarget']) $content = sp_filter_save_target($content);

	# 8: Convert smiley codes to images
	$content = sp_filter_display_smileys($content);

	# 9: Convert Chars
	$content = sp_filter_display_chars($content);

	# 10: Format the paragraphs
	$content = sp_filter_display_paragraphs($content);

	# 11: Format the code select Divs.
	$content = sp_filter_display_codeselect($content);

	# 12: Format image tags
	$content = sp_filter_display_images($content);

    # 13: strip shortcodes
    if (sp_get_option('sffiltershortcodes')) $content = sp_filter_display_shortcodes($content);

    # 14: hide links
    $forum_id = (!empty($spVars['forumid'])) ? $spVars['forumid'] : '';
    if (!sp_get_auth('view_links', $forum_id)) $content = sp_filter_display_hidelinks($content);

	# 15: balance html tags
	$content = sp_filter_save_balancetags($content);

	$content = apply_filters('sph_display_post_content_filter', $content, $original);

	return $content;
}

# ==================================================================
# TEXT - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Profile Description
#		Group Message
#		Forum Message
#		Email Messages
#		Signature Text
#		Sneak Peak Message
#		Admin View Message
#		Custom Editor Messages
#		Registration/Privacy Messages
#		Custom Profile Message
#		Admins Off-Line Message
function sp_filter_text_display($content) {
    #save unedited content
    $original = $content;

	$sffilters = sp_get_option('sffilters');

	# 1: format links
	if ($sffilters['sfurlchars']) $content = sp_filter_save_links($content, $sffilters['sfurlchars']);

	# 2: add nofollow to links
	if ($sffilters['sfnofollow']) $content = sp_filter_save_nofollow($content);

	# 3: add target blank
	if ($sffilters['sftarget']) $content = sp_filter_save_target($content);

	# 4: Convert Chars
	$content = sp_filter_display_chars($content);

	# 5: Format the paragraphs
	$content = sp_filter_display_paragraphs($content);

	# 6: remove escape slashes
	$content = sp_filter_display_stripslashes($content);

	$content = apply_filters('sph_display_text_filter', $content, $original);
	return $content;
}

# ==================================================================
# TITLE - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Group Title/Description *
#		Forum Title/Description *
#		Topic Title *
#		Message Title *
#		Email Subject *
#		Custom Meta Description/Keywords *
#		Custom Icon Title *
#		UserGroup Name/Description *
#		Permission Name/Description *
#		Profile Form Labels *
function sp_filter_title_display($content) {
    #save unedited content
    $original = $content;

	# 1: Convert Chars
	$content = sp_filter_display_chars($content);

	# 2: remove escape slashes
	$content = sp_filter_display_stripslashes($content);

	$content = apply_filters('sph_display_title_filter', $content, $original);
	return $content;
}

# ==================================================================
# USER NAMES - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Display Name
#		Guest Name
function sp_filter_name_display($content) {
    #save unedited content
    $original = $content;

	# 1: Convert Chars
	$content = sp_filter_display_chars($content);

	# 2: remove escape slashes
	$content = sp_filter_display_stripslashes($content);

	$content = apply_filters('sph_display_name_filter', $content, $original);
	return $content;
}

# ==================================================================
# EMAIL ADDRESS - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used:	Guest posts
#		User profile
function sp_filter_email_display($email) {
    #save unedited content
    $original = $email;

	# 1: Convert Chars
	$email = sp_filter_display_chars($email);

	# 2: remove escape slashes
	$email = sp_filter_display_stripslashes($email);

	$email = apply_filters('sph_display_email_filter', $email, $original);
	return $email;
}

# ==================================================================
# URL - DISPLAY FILTERS UMBRELLA
#
# Version: 5.0
# Used: All URLs
function sp_filter_url_display($url) {
    #save unedited content
    $original = $url;

	$url = sp_filter_display_cleanurl($url);

	$url = apply_filters('sph_display_url_filter', $url, $original);

	return $url;
}

# ===START OF DISPLAY FILTERS=======================================
# ------------------------------------------------------------------
# sp_filter_display_links()
#
# Version: 5.0
# Makes unanchored links clickable. This is here for backward
# compatibility with older storage of posts that incuded p tags
#
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_links($content) {
    #save unedited content
    $original = $content;

	$content = sp_make_clickable($content);

	$content = apply_filters('sph_display_links_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sf_convert_custom_smileys()
#
# Version: 5.0
# Swaps codes for smileys if using custom images
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_smileys($content) {
	global $spGlobals, $wp_smiliessearch;

    #save unedited content
    $original = $content;

	# Custom
	if ($spGlobals['smileys']['smileys']) {
		foreach ($spGlobals['smileys']['smileys'] as $sname => $sinfo) {
			$content = str_replace($sinfo[1], '<img src="'.SFSMILEYS.$sinfo[0].'" title="'.$sname.'" alt="'.$sname.'" />', $content);
		}
		# and parse it by Wp smley codes as well.
		$output = '';
		if (get_option('use_smilies') && !empty($wp_smiliessearch)) {
			$textarr = preg_split("/(<.*>)/U", $content, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
			$stop = count($textarr);// loop stuff
			if($stop) {
				for ($i = 0; $i < $stop; $i++) {
					$text = $textarr[$i];
					if ((strlen($text) > 0) && ('<' != $text[0])) { // If it's not a tag
						$text = preg_replace_callback($wp_smiliessearch, 'sp_translate_wp_smiley', $text);
					}
					$output .= $text;
				}
				$content = $output;
			}
		}
	}

	$content = apply_filters('sph_display_smileys_filter', $content, $original);
	return $content;
}

function sp_translate_wp_smiley($smiley) {
	global $wpsmiliestrans;
	if (count($smiley) == 0) {
		return '';
	}
	$smiley = trim(reset($smiley));
	$img = $wpsmiliestrans[$smiley];
	$smiley_masked = esc_attr($smiley);
	$srcurl = apply_filters('smilies_src', includes_url("images/smilies/$img"), $img, site_url());
	return ' <img src="'.$srcurl.'" alt="'.$smiley_masked.'" class="spSmiley" /> ';
}

# ------------------------------------------------------------------
# sp_filter_display_chars()
#
# Version: 5.0
# Converts specific chars to entities
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_chars($content) {
    #save unedited content
    $original = $content;

	$content = convert_chars($content);

	# This simply replaces those odd 0003 chars we have seen
	$content = str_replace(chr(003), "'", $content);

	$content = apply_filters('sph_display_chars_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_display_paragraphs()
#
# Version: 5.0
# Breaks up into paragraphs - excluding syntax highlighted blocks
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_paragraphs($content) {
    #save unedited content
    $original = $content;

	# check if syntax hoighlighted
	if (strpos($content, 'class="brush')) {
		$base = explode('<div class="sfcode">', $content);
		if ($base) 		{
			$comp = array();
			foreach ($base as $part) {
				if (substr(trim($part), 0, 18) == '<pre class="brush-') {
					$subparts = explode('</pre>', $part);
					$comp[] = '<div class="sfcode">'.$subparts[0] .'</pre></div>';
					$pos = strpos($subparts[1], '</div>');
					$subparts[1] = substr($subparts[1], ($pos+6));
					$comp[] = wpautop($subparts[1]);
					unset($subparts);
				} else {
					$comp[] = wpautop($part);
				}
			}
			$content = implode($comp);
		}
	} else {
		$content = wpautop($content);
	}

	$content = shortcode_unautop($content);

	$content = apply_filters('sph_display_paragraphs_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_display_codeselect()
#
# Version: 5.0
# Adds the 'Select Code' button to code blocks
# ------------------------------------------------------------------
function sp_filter_display_codeselect($content) {
    #save unedited content
    $original = $content;

	# add the 'select code' button
	$pos = strpos($content, '<div class="sfcode">');
	if ($pos === false) return $content;

	# check if syntax highlighted
	if (strpos($content, 'class="brush')) return $content;

	while ($pos !== false) {
		$id = rand(100, 10000);
		$selector = '#sfcode'.$id;
		$replace = '<p><input type="button" class="sfcodeselect" name="sfselectit'.$id.'" value="'.sp_text('Select Code').'" onclick="spjSelectCode(\'sfcode'.$id.'\');" /></p><div class="sfcode" id="sfcode'.$id.'">';
		$content = substr_replace ($content, $replace, $pos, 20);
		$pos = $pos + 140;
		$pos = strpos($content, '<div class="sfcode">', $pos);
	}

	$content = apply_filters('sph_display_codeselect_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_display_images() and support functions
#
# Version: 5.0
# Change large images to small thumbnails and embed
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_images($content) {
	return sp_check_display_image_width($content);
}

# Version: 5.0
function sp_check_display_image_width($content) {
	$content = preg_replace_callback('/<img[^>]*>/', 'sp_format_display_image' , $content);
	return $content;
}

# Version: 5.0
function sp_format_display_image($match) {
	global $spPaths, $spVars;
	preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);

    $out = '';
    $local = false;
	$sfimage = array();
	$sfimage = sp_get_option('sfimage');

	# is any of this needed?
	if ($sfimage['enlarge'] == false && $sfimage['process'] == false) return $match[0];

	$thumb = $sfimage['thumbsize'];
	if ((empty($thumb)) || ($thumb < 100)) $thumb = 100;

	preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
	preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);
	preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
	preg_match('/height\s*=\s*"([^"]*)"|height\s*=\s*\'([^\']*)\'/i', $match[0], $height);
	preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
	preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
	preg_match('/class\s*=\s*"([^"]*)"|class\s*=\s*\'([^\']*)\'/i', $match[0], $class);

	if (isset($class[1]) && strpos($class[1], 'wp-image') === 0) return $match[0];

	# is this a smiley?
	if ((strpos($src[1], 'plugins/emotions')) || (strpos($src[1], 'images/smilies')) || (strpos($src[1], $spPaths['smileys']))) {
		return str_replace('img src', 'img class="spSmiley" src', $match[0]);
	}

	if (empty($style[1])) {
		if ($sfimage['style'] == 'left' || $sfimage['style']=='right') {
			$style[1] = 'float: '.$sfimage['style'];
		} else {
			$style[1] = 'vertical-align: '.$sfimage['style'];
		}
	}

	# Might be inherited image with wp standard alignleft and alignright in use
	if (isset($class[1])) {
		if(strpos($class[1], 'alignleft') !== false) {
			$style[1] = 'float: left';
		} elseif(strpos($class[1], 'alignright') !== false) {
			$style[1] = 'float: right';
		} elseif(strpos($class[1], 'aligncenter') !== false) {
			$style[1] = 'margin: 0 auto';
		}
	}

	$iclass = '';
	$mclass = 'sfmouseother';
	$mstyle = '';

	switch ($style[1]) {
		case 'float: left':
			$iclass = 'sfimageleft';
			$mclass = 'sfmouseleft';
			break;
		case 'float: right':
			$iclass = 'sfimageright';
			$mclass = 'sfmouseright';
			break;
		case 'margin: 0 auto':
			$iclass = 'sfimagecenter';
			# mouse icon not possible with center unless we can work it out
			$mclass = 'na';
			if ($spVars['postwidth'] > 0) {
				$p = (abs($spVars['postwidth']/2) + abs($thumb/2)-40);
				$mstyle=" style='margin-left:".$p."px; margin-top:-40px;'";
			}
			break;
		case 'vertical-align: baseline':
			$iclass = 'sfimagebaseline';
			break;
		case 'vertical-align: top':
			$iclass = 'sfimagetop';
			break;
		case 'vertical-align: middle':
			$iclass = 'sfimagemiddle';
			break;
		case 'vertical-align: bottom':
			$iclass = 'sfimagebottom';
			break;
		case 'vertical-align: text-top':
			$iclass = 'sfimagetexttop';
			break;
		case 'vertical-align: text-bottom':
			$iclass = 'sfimagetextbottom';
			break;
	}

	# figure out whether its relative path (same server) or a url
    $parsed = parse_url($src[1]);
    if (is_array($parsed) && array_key_exists('scheme', $parsed)) {
    	$srcfile = $src[1];  # url, so leave it alone
    } else {
  		$srcfile = $_SERVER['DOCUMENT_ROOT'].$src[1];  # relative path, so add DOCUMENT_ROOT to path
  		$local = true;
  	}

	global $gis_error;
	$gis_error = '';
	set_error_handler('sp_gis_error');

	if (empty($width[1])) {
		$size = getimagesize(str_replace(' ', '%20', $srcfile));
		restore_error_handler();
		if ($gis_error == '') {
			if ($size[0]) {
				$width[1] = $size[0];
				$height[1] = $size[1];
			} else {
				return '['.sp_text('Image Can Not Be Found').']';
			}
		}
	}

	if (isset($src[1])) $thissrc = 'src="'.$src[1].'" '; else $thissrc = '';
	if (isset($title[1])) $thistitle = 'title="'.$title[1].'" '; else $thistitle = '';
	if (isset($alt[1])) $thisalt = 'alt="'.$alt[1].'" '; else $thisalt = '';

	if ((int) $width[1] > (int)$thumb) { # is width > thumb size
		$thiswidth = 'width="'.$thumb.'" ';
		$anchor = true;
	} else if (!empty($width)) { # width is smaller than thumb, so use the width
		$thiswidth = 'width="'.$width[1].'" ';
		$mclass = '';
		$anchor = false;
	} else { # couldnt determine width, so dont output it
		$thiswidth = '';
		$mclass = '';
		$anchor = false;
	}

	if (!empty($iclass)) {
		$thisformat = ' class="'.$iclass.'" ';
	} else {
		$thisformat = ' style="'.$style[1].'" ';
	}

	if ($anchor) {
		if($width[1] ? $w = $width[1] : $w = 'auto');
		if($height[1] ? $h = $height[1] : $h = 'auto');
		# Use popup or not?
		if ($sfimage['enlarge'] == true) {
			$out = '<a href="javascript:void(null)" class="vtip" title="'.sp_text('Click image to enlarge').'" onclick="spjPopupImage(\''.$src[1].'\', \''.$w.'\', \''.$h.'\', \''.$sfimage['constrain'].'\');" >';
			$out = apply_filters('sph_display_image_popup', $out, $src[1]);
		} else {
			$out = '<a href="'.$src[1].'" '.$thistitle.'>';
		}
	}

	# Is the path to the image in the standard uploads folder?
	# If so can we make use fo the file manager thumbs?
	if ($local) {
		$pos = strrpos($src[1], '/');
		if ($pos !== false) {
			$left = substr($src[1], 0, $pos+1);
			$right = substr($src[1], $pos+1);
			$testsrc = untrailingslashit($_SERVER['DOCUMENT_ROOT']).$left.'_thumbs/_'.$right;
			if (file_exists($testsrc)) {
				$thissrc = 'src="'.$left.'_thumbs/_'.$right.'"';
				$thiswidth = '';
			}
		}
	}

	$out.= '<img '.$thissrc.$thiswidth.$thisformat.$thistitle.$thisalt.'/>';

	if($mclass) {
		$mouse = '<img src="'.SPTHEMEICONSURL.'sp_Mouse.png" class="'.$iclass.' '.$mclass.'" alt="" '.$mstyle.'/>';
		$out.= apply_filters('sph_display_image_mouse', $mouse);
	}

	if ($anchor) $out.= '</a>';

	if($sfimage['forceclear']) $out.='<div style="clear:both"></div>';

	return $out;
}

# ------------------------------------------------------------------
# sp_filter_display_stripslashes()
#
# Version: 5.0
# Remove escaped slashes
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_stripslashes($content) {
	$content = stripslashes($content);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_display_cleanurl()
#
# Version: 5.0
# Cleans up url for display
#	$url:		Unfiltered url
# ------------------------------------------------------------------
function sp_filter_display_cleanurl($url) {
	$url = esc_url($url);
	return $url;
}

# ------------------------------------------------------------------
# sp_filter_display_shortcodes()
#
# Version: 5.0
# Removes non allowed shortcodes
# ------------------------------------------------------------------
function sp_filter_display_shortcodes($content) {
	global $shortcode_tags;

    #save unedited content
    $original = $content;

	# Backup current registered shortcodes
	$orig_shortcode_tags = $shortcode_tags;
	$allowed_shortcodes = explode("\n", stripslashes(sp_get_option('sfshortcodes')));
    if ($allowed_shortcodes) {
        foreach ($allowed_shortcodes as $tag) {
            if (array_key_exists($tag, $orig_shortcode_tags)) unset($shortcode_tags[$tag]);
        }
    }

    # allow our internal shortcodes (letting plugins add others)
    $internal_shortcodes = apply_filters('sph_internal_shortcodes', array('spoiler'));
    foreach ($internal_shortcodes as $shortcode) {
        unset($shortcode_tags[$shortcode]);
    }

    # strip all but allowed shortcodes
    $content = strip_shortcodes($content);

	# Restore registered shortcodes
	$shortcode_tags = $orig_shortcode_tags;

	$content = apply_filters('sph_display_shortcodes_filter', $content, $original);
	return $content;
}

# ------------------------------------------------------------------
# sp_filter_save_oEmbed() and support function
#
# Version: 5.0
# Checks urls against the WP oEmbed class and pulls in the embed
# code if a match is found. Performed before other url checks
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
function sp_filter_display_oEmbed($content) {
	$content = preg_replace_callback('#(?<!=\')(?<!=")(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+\#]*[\w\-\@?^=%&amp;/~\+\#])?#i', 'sp_check_display_oEmbed', $content);
	return $content;
}

# Version: 5.0
function sp_check_display_oEmbed($match) {
	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$url = $match[0];
	$oembed = _wp_oembed_get_object();
	foreach($oembed->providers as $provider => $data) {
		list($providerurl, $regex) = $data;
		# Turn the asterisk-type provider URLs into regex
		if (!$regex) {
			$provider = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $provider ), '#' ) ) . '#i';
		}
		if(preg_match($provider, $url)) {
			$embedUrl = wp_oembed_get($url, array('discover' => false));
			if (empty($embedUrl)) {
				return $url;
			} else {
				return $embedUrl;
			}
		}
	}
	return $url;
}

# ------------------------------------------------------------------
# sp_filter_display_media_embeds()
#
# Version: 5.3
# Checks for media (video/audio) urls as first line of post and
# embeds the media in the post content
#
# NOTE: The first get_embedded_media() call actually finds both video/audio.
#       The autoembed() function sorts it out and just returns any non media urls
#       as urls.  But left it with two calls in case WP ever 'corrects' the media
#       detection to find only the requested type. As is, will short circuit and not
#       make second call.
#
#	$content:		Unfiltered post content
#
#	NOTE: THIS FUNCTION IS REQUIRED FOR THE NEW WP POST FORMATS DEVELOPMENT BUT
#	THIS HAS NOW BEEN REMOVED FROM WP 3.6 SO CALL TO THIS FUNCTION ARE BEING
#	COMMENTED OUT BUT THE CODE IS BEING LEFT IN PLACE FOR WHEN NEEDED
# ------------------------------------------------------------------
function sp_filter_display_media_embeds($content) {
	global $wp_embed;

	$embeds = get_embedded_media('audio', $content, true, 1);
	if (!empty($embeds)) {
		$embed = reset($embeds);
        $content = $wp_embed->autoembed($embed).$content;
        return $content;
    }

	$embeds = get_embedded_media('video', $content, true, 1);
	if (!empty($embeds)) {
		$embed = reset($embeds);
        $content = $wp_embed->autoembed($embed).$content;
        return $content;
    }

    return $content;
}

# ------------------------------------------------------------------
# sp_filter_display_hidelinks()
#
# Version: 5.0
# Option: Removes links from post content
# ------------------------------------------------------------------
function sp_filter_display_hidelinks($content) {
    #save unedited content
    $original = $content;

	$sffilters = sp_get_option('sffilters');
    $string = stripslashes($sffilters['sfnolinksmsg']);
    $content = preg_replace("#(<a.*>).*(</a>)#", $string, $content);

	$content = apply_filters('sph_display_hidelinks_filter', $content, $original);

	return $content;
}

# ==================================================================
# SPECIAL FILTERS - ONE OFFS
# The following filters are specific to one task - usually display
# ------------------------------------------------------------------
# sp_filter_signature_display() and support function
#
# Version: 5.0
# Filters the display of signature images
#	$content:		Unfiltered signature content
# ------------------------------------------------------------------
function sp_filter_signature_display($content) {
    #save unedited content
    $original = $content;

	$sfsigimagesize = sp_get_option('sfsigimagesize');
	if ($sfsigimagesize['sfsigwidth'] > 0 || $sfsigimagesize['sfsigheight'] > 0) $content = preg_replace_callback('/<img[^>]*>/', 'sp_check_sig' , $content);

	$content = apply_filters('sph_display_signature_filter', $content, $original);

	return $content;
}

# Version: 5.0
function sp_check_sig($match) {
	$sfsigimagesize = sp_get_option('sfsigimagesize');

    # get the elements of the img tags
	preg_match('/title\s*=\s*"([^"]*)"|title\s*=\s*\'([^\']*)\'/i', $match[0], $title);
	preg_match('/width\s*=\s*"([^"]*)"|width\s*=\s*\'([^\']*)\'/i', $match[0], $width);
	preg_match('/height\s*=\s*"([^"]*)"|height\s*=\s*\'([^\']*)\'/i', $match[0], $height);
	preg_match('/src\s*=\s*"([^"]*)"|src\s*=\s*\'([^\']*)\'/i', $match[0], $src);
	preg_match('/style\s*=\s*"([^"]*)"|style\s*=\s*\'([^\']*)\'/i', $match[0], $style);
	preg_match('/alt\s*=\s*"([^"]*)"|alt\s*=\s*\'([^\']*)\'/i', $match[0], $alt);

    # check for possible single quote match or double quote
    if (empty($title[1])  && !empty($title[2]))  $title[1]  = $title[2] ;
    if (empty($width[1])  && !empty($width[2]))  $width[1]  = $width[2] ;
    if (empty($height[1]) && !empty($height[2])) $height[1] = $height[2] ;
    if (empty($src[1])    && !empty($src[2]))    $src[1]    = $src[2] ;
    if (empty($style[1])  && !empty($style[2]))  $style[1]  = $style[2] ;
    if (empty($alt[1])    && !empty($alt[2]))    $alt[1]    = $alt[2] ;

    # if user defined heights are valid, just return
	if ((!isset($width[1]) || $width[1] <= $sfsigimagesize['sfsigwidth']) &&
        (!isset($height[1]) || $height[1] <= $sfsigimagesize['sfsigheight'])) {
        return $match[0];
    }

    # insepct the image itself
	global $gis_error;
	$gis_error = '';
	set_error_handler('sp_gis_error');

    $display_width = '';
    $display_height = '';
	$size = getimagesize(str_replace(' ', '%20', $src[1]));
	restore_error_handler();
	if ($gis_error == '') {
        # Did image exist?
		if ($size[0] && $size[1]) {
            # check width
        	if (isset($width[1]) && ($width[1] <= $sfsigimagesize['sfsigwidth'] || $sfsigimagesize['sfsigwidth'] == 0)) {# width specified and less than max allowed
                $display_width = ' width="'.$width[1].'"';
            } elseif ($sfsigimagesize['sfsigwidth'] > 0 && $size[0] > $sfsigimagesize['sfsigwidth']) {
                $display_width = ' width="'.$sfsigimagesize['sfsigwidth'].'"';
            }

            # check the height
        	if (isset($height[1]) && ($height[1] <= $sfsigimagesize['sfsigheight'] || $sfsigimagesize['sfsigheight'] == 0)) { # height specified and less than max allowed
                $display_height = ' height="'.$height[1].'"';
            } elseif ($sfsigimagesize['sfsigheight'] > 0 && $size[1] > $sfsigimagesize['sfsigheight']) {
                $display_height = ' height="'.$sfsigimagesize['sfsigheight'].'"';
            }
		} else {
            # image not found, strip tags
			return '';
		}
	} else {
        # problem checking sizes, so just limit
        $display_width = ' width="'.$sfsigimagesize['sfsigwidth'].'"';
        $display_height = ' height="'.$sfsigimagesize['sfsigheight'].'"';
	}

	#
	return '<img src="'.$src[1].'"'.$display_width.$display_height.' style="'.$style[1].'" title="'.$title[1].'" alt="'.$alt[1].'" />';
}

# ------------------------------------------------------------------
# sp_filter_tooltip_display()
#
# Version: 5.0
# Filters the display of topic linked post 'tooltips'
#	$content:		Unfiltered post content
#	$status:		True if post awaiting moderation
# ------------------------------------------------------------------
function sp_filter_tooltip_display($content, $status) {
	global $spThisUser;

    #save unedited content
    $original = $content;

	# can the current user view this post?
	if (!$spThisUser->moderator && $status == 1) {
		$content = sp_text('Post Awaiting Approval by Forum Administrator');
	} else {
		$content = addslashes($content);
		$content = sp_filter_save_nohtml($content);
        # remove shortcodes to prevent messing up tooltip
        $content = strip_shortcodes($content);
        $length = apply_filters('sph_tooltip_length_chars', 300);
		if (strlen($content) > $length) {
			$pos = strpos($content, ' ', $length);
            if ($pos === false) $pos = $length;
            $content = substr($content, 0, $pos).'...';
    	}
		$content = htmlspecialchars($content, ENT_QUOTES, SFCHARSET);
		$content = str_replace('&amp;', '&', $content);
	}

	$content = apply_filters('sph_display_tooltip_filter', $content, $original, $status);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_rss_display()
#
# Version: 5.0
# Filters the display of post content in rss feed
#	$content:		Unfiltered post content
# ------------------------------------------------------------------
# Used:	RSS Feeds
function sp_filter_rss_display($content) {
    global $spVars;

    #save unedited content
    $original = $content;

	# 1: Backwards compatible make links clickable
	$content = sp_filter_display_links($content);

	# 2: Convert smiley codes to images
	$content = sp_filter_display_smileys($content);

	# 3: Convert Chars
	$content = sp_filter_display_chars($content);

	# 4: Format the paragraphs
	$content = sp_filter_display_paragraphs($content);

    # 5: strip shortcodes
    if (sp_get_option('sffiltershortcodes')) $content = sp_filter_display_shortcodes($content);

    # 6: hide links
    if (!sp_get_auth('view_links', $spVars['forumid'])) $content = sp_filter_display_hidelinks($content);

	# 7: apply any users custom filters
	$content = apply_filters('sph_display_rss_content_filter', $content, $original);

	return $content;
}

# ------------------------------------------------------------------
# sp_filter_table_prefix()
#
# Version: 5.0
# Filters the prefix from table names
#	$content:		Unfiltered content
# ------------------------------------------------------------------
# Used as a filter in search values - aids killing SQL injections
function sp_filter_table_prefix($content) {
$long = array(
	SF_PREFIX.'commentmeta', SF_PREFIX.'comments', SF_PREFIX.'options', SF_PREFIX.'postmeta', SF_PREFIX.'posts',
	SF_PREFIX.'terms', SF_PREFIX.'term_taxonomy', SF_PREFIX.'term_relationships', SF_PREFIX.'users', SF_PREFIX.'usermeta',
	SF_PREFIX.'sfauths', SF_PREFIX.'sfgroups', SF_PREFIX.'sfforums', SF_PREFIX.'sftopics', SF_PREFIX.'sfposts', SF_PREFIX.'sfwaiting',
	SF_PREFIX.'sftrack', SF_PREFIX.'sfusergroups', SF_PREFIX.'sfpermissions', SF_PREFIX.'sfdefpermissions', SF_PREFIX.'sfroles',
	SF_PREFIX.'sfmembers', SF_PREFIX.'sfmemberships', SF_PREFIX.'sfmeta', SF_PREFIX.'sflog', SF_PREFIX.'sfoptions');
$short = array(
	'commentmeta', 'comments', 'options', 'postmeta', 'posts',
	'terms', 'term_taxonomy', 'term_relationships', 'users', 'usermeta',
	'sfauths', 'sfgroups', 'sfforums', 'sftopics', 'sfposts', 'sfwaiting',
	'sftrack', 'sfusergroups', 'sfpermissions', 'sfdefpermissions', 'sfroles',
	'sfmembers', 'sfmemberships', 'sfmeta', 'sflog', 'sfoptions');
	return str_ireplace($long, $short, $content);
}

# ==================================================================
# CONTENT - PAGE LEVEL DISPLAY FILTERS
# The following filters and shortcodes are run at 'page' level after
# the forum page has been generated.

# ------------------------------------------------------------------
# sp_filter_display_spoiler()
#
# Version: 5.0
# Converts the spoiler shortcode to the drop down spoiler div
# ------------------------------------------------------------------
function sp_filter_display_spoiler($atts, $content) {
	global $spoilerID, $spVars;

    #save unedited content
    $original = $content;

	if (!isset($spoilerID)) {
		$spoilerID = 1;
	} else {
		$spoilerID++;
	}

	$out = '';
	$out.= '<div class="spSpoiler">';
	$out.= '<div class="spReveal">';
	$reveal = sp_text('Reveal Spoiler');
	$hide = sp_text('Hide Spoiler');
	$out.= '<a id="spRevealLink'.$spoilerID.'" href="javascript:void(0);" onclick="spjSpoilerToggle(\''.$spoilerID.'\', \''.$reveal.'\', \''.$hide.'\');">'.$reveal.'</a>';
	$out.= '<input type="hidden" id="spSpoilerState'.$spoilerID.'" name="spSpoilerState'.$spoilerID.'" value="0" />';
	$out.= '</div>';
	$out.= '<div class="spSpoilerContent" id="spSpoilerContent'.$spoilerID.'">';
	$out.= '<p>'.$content.'</p>';
	$out.= '</div></div>';

	$out = apply_filters('sph_display_spoiler_filter', $out, $original);

	return $out;
}

# ------------------------------------------------------------------
# sp_wptexturize()
#
# Version: 5.0
# take control of wptexturize to stop it doing really nasty things
# to quotes.
#	$content:		Unfiltered post content
# This code is part of the "jQuery.Syntax" project, and is licensed
# under the GNU AGPLv3. See <jquery.syntax.js> for licensing details.
# Copyright 2010 Samuel Williams. All rights reserved.
# ------------------------------------------------------------------
function sp_wptexturize($content) {
	static $static_setup = false, $default_no_texturize_tags, $default_no_texturize_shortcodes, $static_characters, $static_replacements;
	$output = '';
	$curl = '';
	$textarr = preg_split('/(<.*>|\[.*\])/Us', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
	$stop = count($textarr);

	# No need to setup these variables more than once
	if (!$static_setup) {
		$default_no_texturize_tags = array('pre', 'code', 'kbd', 'style', 'script', 'tt');
		$default_no_texturize_shortcodes = array('code');
		$static_characters = array('---', ' - ', 'xn&#8211;', '...', ' (tm)');
		$static_replacements = array('&#8212;', ' &#8211; ', 'xn--', '&#8230;', ' &#8482;');
		$static_setup = true;
	}

	# Transform into regexp sub-expression used in _wptexturize_pushpop_element
	# Must do this everytime in case plugins use these filters in a context sensitive manner
	$no_texturize_tags = '('.implode('|', apply_filters('no_texturize_tags', $default_no_texturize_tags) ).')';
	$no_texturize_shortcodes = '('.implode('|', apply_filters('no_texturize_shortcodes', $default_no_texturize_shortcodes) ).')';
	$no_texturize_tags_stack = array();
	$no_texturize_shortcodes_stack = array();
	for ($i = 0; $i < $stop; $i++) {
		$curl = $textarr[$i];
		if (!empty($curl) && '<' != $curl{0} && '[' != $curl{0} && empty($no_texturize_shortcodes_stack) && empty($no_texturize_tags_stack)) {
			# This is not a tag, nor is the texturization disabled static strings
			$curl = str_replace($static_characters, $static_replacements, $curl);
			# regular expressions
		} elseif (!empty($curl)) {
			# Only call _wptexturize_pushpop_element if first char is correct tag opening
			if ('<' == $curl{0}) {
				_wptexturize_pushpop_element($curl, $no_texturize_tags_stack, $no_texturize_tags, '<', '>');
			} elseif ('[' == $curl{0}) {
				_wptexturize_pushpop_element($curl, $no_texturize_shortcodes_stack, $no_texturize_shortcodes, '[', ']');
			}
		}

		$curl = preg_replace('/&([^#])(?![a-zA-Z1-4]{1,8};)/', '&#038;$1', $curl);
		$output .= $curl;
	}
	return $output;
}

# ------------------------------------------------------------------
# sp_esc_regex()
#
# Version: 5.0
# escape regular expression matching strings so they can contain
# regex special chars
#	$str:		string to have regex special chars escaped
# ------------------------------------------------------------------
function sp_esc_regex($str) {
    $patterns = array('/\//', '/\^/', '/\./', '/\$/', '/\|/', '/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/', '/\?/', '/\{/', '/\}/', '/\,/');
    $replace = array('\/', '\^', '\.', '\$', '\|', '\(', '\)', '\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,');
    return addslashes(preg_replace($patterns, $replace, $str));
}

# Version: 5.0
function sp_esc_int($checkval) {
	$actual = '';
	if (isset($checkval)) {
		if (is_numeric($checkval)) $actual = $checkval;
		$checklen = strlen(strval($actual));
		if ($checklen != strlen($checkval)) die(sp_text('A Suspect Request has been Rejected'));
	}
	return $actual;
}

# Version: 5.0
function sp_esc_str($string) {
	if (get_magic_quotes_gpc())	$string = stripslashes($string);
   	$string = esc_sql($string);
	$string = wp_kses($string, array());
  	return $string;
}

# ------------------------------------------------------------------
# sp_make_clickable()
#
# Version: 5.0
# make links clickable except in pre tags
#	$content:		string to make links clickable
# ------------------------------------------------------------------
function sp_make_clickable($content) {
	# dont make clickable in pre tags
	$segments = preg_split('/(<\/?pre|\[)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

	# $depth = how many nested pres we're inside of
	$depth = 0;
	foreach ($segments as &$segment) {
	    if ($depth == 0 && ($segment != '<pre' && $segment != '[')) {
	        $segment = make_clickable($segment);
	    } else if ($segment == '<pre' || $segment == '[') {
	        $depth++;
	    } else if ($depth > 0 && ($segment == '</pre' || $segment == ']')) {
	        $depth--;
        }
	}
	$content = implode($segments);

	return $content;
}

# ------------------------------------------------------------------
# sp_parse_inline_bbcode()
#
# Version: 5.0
# parse content for manually entered bbCode
#	$content:		string to make links clickable
# ------------------------------------------------------------------
function sp_parse_inline_bbcode($content) {
	$content = trim($content);

	# BBCode to find...
	$in = array(
		'/\[b\](.*?)\[\/b\]/ms',
		'/\[i\](.*?)\[\/i\]/ms',
		'/\[u\](.*?)\[\/u\]/ms',
		'/\[left\](.*?)\[\/left\]/ms',
		'/\[right\](.*?)\[\/right\]/ms',
		'/\[center\](.*?)\[\/center\]/ms',
		'/\[img\](.*?)\[\/img\]/ms',
		'/\[url\="?(.*?)"?\](.*?)\[\/url\]/is',
		'/\[url\="?(.*?)"?\](.*?)\]/is',
		'/\[url\](.*?)\[\/url\]/is',
		'/\[quote\](.*?)\[\/quote\]/ms',
		'/\[quote\="?(.*?)"?\](.*?)\[\/quote\]/ms',
		'/\[list\=(.*?)\](.*?)\[\/list\]/ms',
		'/\[list\](.*?)\[\/list\]/ms',
		'/\[B\](.*?)\[\/B\]/ms',
		'/\[I\](.*?)\[\/I\]/ms',
		'/\[U\](.*?)\[\/U\]/ms',
		'/\[LEFT\](.*?)\[\/LEFT\]/ms',
		'/\[RIGHT\](.*?)\[\/RIGHT\]/ms',
		'/\[CENTER\](.*?)\[\/CENTER\]/ms',
		'/\[IMG\](.*?)\[\/IMG\]/ms',
		'/\[COLOR=(.*?)](.*?)\[\/COLOR]/is',
		'/\[URL\="?(.*?)"?\](.*?)\[\/URL\]/is',
		'/\[QUOTE\](.*?)\[\/QUOTE\]/ms',
		'/\[QUOTE\="?(.*?)"?\](.*?)\[\/QUOTE\]/ms',
		'/\[LIST\=(.*?)\](.*?)\[\/LIST\]/ms',
		'/\[LIST\](.*?)\[\/LIST\]/ms',
		'/\[\*\]\s?(.*?)\n/ms'
	);

	# And replace them by...
	$out = array(
		'<strong>\1</strong>',
		'<em>\1</em>',
		'<u>\1</u>',
		'<div style="text-align:left">\1</div>',
		'<div style="text-align:right">\1</div>',
		'<div style="text-align:center">\1</div>',
		'<img src="\1" alt="\1" />',
		'<a href="\1">\2</a>',
		'<a href="\1">\2</a>',
		'<a href="\1">\2</a>',
		'<blockquote>\1</blockquote>',
		'<blockquote>\1 said:<br />\2</blockquote>',
		'<ol start="\1">\2</ol>',
		'<ul>\1</ul>',
		'<strong>\1</strong>',
		'<em>\1</em>',
		'<u>\1</u>',
		'<div style="text-align:left">\1</div>',
		'<div style="text-align:right">\1</div>',
		'<div style="text-align:center">\1</div>',
		'<img src="\1" alt="\1" />',
		'<span style="color: \1">\2</span>',
		'<a href="\1">\2</a>',
		'<blockquote>\1</blockquote>',
		'<blockquote>\1 said:<br />\2</blockquote>',
		'<ol start="\1">\2</ol>',
		'<ul>\1</ul>',
		'<li>\1</li>'
	);
	$content = preg_replace($in, $out, $content);

	# special case for nested quotes
	$content = str_replace('[quote]', '<blockquote>', $content);
	$content = str_replace('[/quote]', '</blockquote>', $content);

	return $content;
}

# CONTENT - EMAIL FILTERS UMBRELLA
#
# Version: 5.1.4
function sp_filter_email_content($content) {
    #save unedited content
    $original = $content;

	# apply any users custom filters for pre email content processing
	$content = apply_filters('sph_email_content_pre_filter', $content);

	# 1: Convert Chars
	$content = sp_filter_display_chars($content);

	# 2: Format the paragraphs
	$content = sp_filter_display_paragraphs($content);

    # 3: do shortcodes
    if (sp_get_option('sffiltershortcodes')) $content = sp_filter_display_shortcodes($content);

    # 4: lets fix up spacing for br and p tags
 	$content = sp_filter_save_linebreaks($content);

    # 5: Fix up quotes
	$content = html_entity_decode($content, ENT_QUOTES);

    # 6: change to spaces
	$content = str_replace('&nbsp;', ' ', $content);

    # 7: strip html tags
	$content = strip_tags($content);

	# 8: apply any users custom filters
	$content = apply_filters('sph_email_content_filter', $content, $original);

	return $content;
}

?>