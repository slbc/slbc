/* ---------------------------------
Simple:Press - Version 5.0
Base Front-end Forum Javascript

$LastChangedDate: 2010-08-11 12:22:07 -0700 (Wed, 11 Aug 2010) $
$Rev: 4384 $
------------------------------------ */

var result;

function spjLoadTool(url, target, imageFile) {
	if (imageFile !== '') {
		document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
	jQuery('#'+target).load(url);
}

function spjClearIt(target) {
	document.getElementById(target).innerHTML = '';
}

/* ----------------------------------
Validate the new post form
-------------------------------------*/
function spjValidatePostForm(theForm, guest, topic, img) {
	var reason = '';
	if (guest == 1 && theForm.guestname != 'undefined') reason+= spjValidateThis(theForm.guestname, sp_forum_vars.noguestname);
	if (guest == 1 && theForm.guestemail != 'undefined') reason+= spjValidateThis(theForm.guestemail, sp_forum_vars.noguestemail);
	if (topic == 1 && theForm.newtopicname != 'undefined') reason+= spjValidateThis(theForm.newtopicname, sp_forum_vars.notopictitle);
	reason+= spjEdValidateContent(theForm.postitem, sp_forum_vars.nocontent);
	/* check for pasted content */
	var thisPost = spjEdGetEditorContent(theForm);
	var found = false;
	var checkWords = new Array();
	checkWords[0] = 'MsoPlainText';
	checkWords[1] = 'MsoNormal';
	checkWords[2] = 'mso-layout-grid-align';
	checkWords[3] = 'mso-pagination';
	checkWords[4] = 'white-space:';
	for (i=0; i<checkWords.length; i++) {
		if (thisPost.match(checkWords[i]) !== null) {
			found = true;
		}
	}
	if (found) {
		reason += "<strong>" + sp_forum_vars.rejected + "</strong><br />";
	}
	if (thisPost.match('<iframe') && sp_forum_vars.checkiframe == 'yes') {
		reason += "<strong>" + sp_forum_vars.iframe + "</strong><br />";
	}

	/* any errors */
    var msg = '';
	if (reason !== '') {
		msg = sp_forum_vars.problem + '<br />' + reason;
		jQuery('#spPostNotifications').html(msg);
		jQuery('#spPostNotifications').show('slow');

		return false;
	}

	var saveBtn = document.getElementById('sfsave');
	saveBtn.value = sp_forum_vars.savingpost;
	saveBtn.disabled = 'disabled';

	msg = sp_forum_vars.savingpost + ' - ' + sp_forum_vars.wait;
	spjDisplayNotification(2, msg);
	return true;
}

/* ----------------------------------
Validatation support routines
-------------------------------------*/
function spjValidateThis(theField, errorMsg) {
	var error = '';
	if (theField.value.length === 0) {
		error = '<strong>' + errorMsg + '</strong><br />';
	}
	return error;
}

/* ----------------------------------
Validatate search text has been input
-------------------------------------*/
function spjValidateSearch(btn, subId, c, maxLen) {
	if(btn.id == 'undefined' || btn.id != subId) {
		document.sfsearch.submit();
		return;
	}

	var stopSearch = false;
	var msg = '';
	var s = jQuery('#searchvalue').val();

	if(s === '') {
		msg = sp_forum_vars.nosearch;
		stopSearch = true;
	} else {
		var w = s.split(" ");
		var good = 0;
		var bad = 0;
		for (i=0; i < w.length; i++) {
			if(w[i].length < maxLen) {
			     bad++;
            } else {
                good++;
            }
		}

		if(good === 0) {
			msg = sp_forum_vars.allwordmin + ' ' + maxLen;
			stopSearch = true;
		} else if(bad !== 0) {
			msg = sp_forum_vars.somewordmin + ' ' + maxLen;
		}
	}
	if(msg !== '') {
		spjDisplayNotification(1, msg);
	}

	if(stopSearch === false && c == 'link') {
		document.sfsearch.submit();
		return;
	}
	if(stopSearch === true) {
		return false;
	} else {
		return true;
	}
}

function spjOpenEditor(editorId, formType) {
	jQuery(document).ready(function() {
//		jQuery('#'+editorId).slideDown(function () {
		jQuery('#'+editorId).slideDown();
			location.href= '#'+editorId;
			spjEdOpenEditor(formType);
//		});
	});
}

/* ----------------------------------
Open and Close of hidden divs
-------------------------------------*/
function spjToggleLayer(whichLayer, speed) {
	if (!speed) speed = 'slow';
	jQuery('#'+whichLayer).slideToggle(speed);

	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'spPostForm' || whichLayer == 'sfsearchform') {
		obj.scrollIntoView();
	}
}

/* ----------------------------------
Quote Post insertion
-------------------------------------*/
function spjQuotePost(postid, intro, forumid, quoteUrl) {
	quoteUrl+='&post='+postid+'&forumid='+forumid;

	jQuery('#spPostForm').show('normal', function() {
        spjOpenEditor('spPostForm', 'post');
		jQuery('#postitem').load(quoteUrl, function(content, b) {
			spjEdInsertContent(intro, content);
		});
	});
}

/* ----------------------------------
Enable Save buttons on Math entry
-------------------------------------*/
function spjSetPostButton(result, val1, val2, gbuttontext, bbuttontext) {
	var button = document.addpost.newpost;

	if (result.value == (val1+val2)) {
		button.disabled = false;
		button.value = gbuttontext;
	} else {
		button.disabled = true;
		button.value = bbuttontext;
	}
}

function spjSetTopicButton(result, val1, val2, gbuttontext, bbuttontext) {
	var button = document.addtopic.newtopic;

	if (result.value == (val1+val2)) {
		button.disabled = false;
		button.value = gbuttontext;
	} else {
		button.disabled = true;
		button.value = bbuttontext;
	}
}

/* ----------------------------------
Trigger redirect on drop down
-------------------------------------*/
function spjChangeURL(menuObj) {
	var i = menuObj.selectedIndex;

	if (i > 0) {
 		if (menuObj.options[i].value !== '#') {
			window.location = menuObj.options[i].value;
		}
	}
}

/* ----------------------------------
URL redirect
-------------------------------------*/
function spjReDirect(url) {
	window.location = url;
}

/* ----------------------------------
Error and Success top notification
0=Success 1=Failure 2=Wait
-------------------------------------*/
function spjDisplayNotification(t, m) {
	jQuery(document).ready(function() {
		var h = "<div id='spNotification' ";
		var i = '';
		if(t == 0) i = successImage.src;
		if(t == 1) i = failureImage.src;
		if(t == 2) i = waitImage.src;

		h += "class='spMessageSuccess'><img src='" + i + "' alt='' /><div style='clear:both'></div><p>" + m + "</p></div>";

		var c = document.getElementById('spMainContainer');
		var r = c.getBoundingClientRect();
		var o = new Number(r.left);
		var w = new Number(r.right-r.left);
		var x = new Number(0);
		if(w < 260) {
			x = Math.round((w-20)/2);
		} else {
			x = 150;
		}
		var l = Math.round(((w/2)+o)-x);

		jQuery('#spMainContainer').prepend(h);
		jQuery('#spNotification').css('left', l);
		jQuery('#spNotification').css('width', (x*2));
		jQuery('#spNotification').show();
		jQuery('#spNotification').fadeOut(8000, function() {
			jQuery('#spNotification').remove();
		});
	});
}

/* ----------------------------------
Auto Updates
-------------------------------------*/
function spjAutoUpdate(url, timer) {
	var sfInterval = window.setInterval("spjPerformUpdates('" + url + "')", timer);
}

function spjPerformUpdates(url) {
    updates = url.split('%');
	for (i=0; i < updates.length; i++) {
        up = updates[i].split(',');
        var func = up[0] + "('" + up[1] + "')";
        func = func.replace(/&amp;/gi, '&');
        eval(func);
	}
}

function spjUserUpdate(url) {
	/* still logged in? */
	var targetIDUser = document.getElementById('sfthisuser');
	if (targetIDUser !== null) {
		var userid = targetIDUser.innerHTML;
		if (userid === null || userid === '') {
			userid = '0';
		}
		var userCheckUrl = url + '&target=checkuser&thisuser=' + userid + '&rnd=' +  new Date().getTime();
		jQuery('#sflogininfo').load(userCheckUrl);
	}
}

/* ----------------------------------
Embed a pre syntax highlight codeblock
-------------------------------------*/
function spjSelectCode(codeBlock) {
var e = document.getElementById(codeBlock);
	/* Get ID of code block
	   Not IE */
	if (window.getSelection) {
		s = window.getSelection();
		/* Safari */
		if (s.setBaseAndExtent) {
			s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
		} else {
			/* Firefox and Opera */
			r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	} else if (document.getSelection) {
		/* Some older browsers */
		s = document.getSelection();
		r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	} else if (document.selection) {
		/* IE */
		r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

function spjRemoveAvatar(ahahURL, avatarTarget, spinner) {
	jQuery('#'+avatarTarget).html('<img src="' + spinner + '" />');
	jQuery('#'+avatarTarget).load(ahahURL);
	jQuery('#spDeleteUploadedAvatar').hide();
	return;
}

function spjRemovePool(ahahURL, avatarTarget, spinner) {
	jQuery('#'+avatarTarget).html('<img src="' + spinner + '" />');
	jQuery('#'+avatarTarget).load(ahahURL);
	jQuery('#spDeletePoolAvatar').hide();
	return;
}

function spjRemoveNotice(ahahUrl, noticeId) {
    jQuery('#'+noticeId).slideUp(400, function() {
    	jQuery('#'+noticeId).load(ahahUrl);
        jQuery('#'+noticeId).remove();
        if (!jQuery.trim(jQuery('#spUserNotices').html()).length) {
        	jQuery('#spUserNotices').slideUp();
        }
    });
}

function spjSelAvatar(file, msg) {
	document.getElementById('spPoolAvatar').value = file;
	jQuery('#spPoolStatus').html('<p>' + msg + '</p>');
	return;
}

function spjSpoilerToggle(id, reveal, hide) {
	spjToggleLayer('spSpoilerContent' + id, 'fast');
	cur = jQuery('#spSpoilerState' + id).val();
	if (cur === 0) {
		jQuery('#spSpoilerState' + id).val(1);
		jQuery('#spRevealLink' + id).html(hide);
	} else {
		jQuery('#spSpoilerState' + id).val(0);
		jQuery('#spRevealLink' + id).html(reveal);
	}
}

function spjGetCategories(ahahURL, checked, spinner) {
	if(checked) {
		jQuery('#spCatList').html('<img src="' + spinner + '" />');
		jQuery('#spCatList').show('slide');
		jQuery('#spCatList').load(ahahURL);
	} else {
		jQuery('#spCatList').hide();
	}
}

function spjSetProfileDataHeight() {
	baseHeight = Math.max(jQuery("#spProfileData").outerHeight(true) + 10, jQuery("#spProfileMenu").outerHeight(true));
   	jQuery("#spProfileContent").height(baseHeight + jQuery("#spProfileHeader").outerHeight(true));
}

function spjOpenCloseForums(target, tagId, tagClass, openIcon, closeIcon, toolTipOpen, toolTipClose) {
    var icon = '';
    var tooltip = '';
	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
		tooltip = toolTipOpen;
		jQuery.cookie(target, 'closed', {expires: 30, path: '/'});
	} else {
		jQuery('#'+target).slideDown();
		icon = closeIcon;
		tooltip = toolTipClose;
		jQuery.cookie(target, 'open', {expires: 30, path: '/'});
	}
	jQuery('#'+tagId).html('<img class="'+tagClass+' vtip" src="'+icon+'" title="'+tooltip+'" />');
	vtip();
}

function spjInlineTopics(target, site, spinner, tagId, openIcon, closeIcon) {
    var icon = '';
   	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
	} else {
		if(jQuery('#'+target).html() === '') {
			jQuery('#'+target).html('<img src="' + spinner + '" />');
			jQuery('#'+target).slideDown();
			jQuery('#'+target).load(site, function() {
				jQuery('#'+target).slideDown();
			});
		} else {
			jQuery('#'+target).slideDown();
		}
		icon = closeIcon;
	}
	jQuery('#'+tagId).html('<img src="'+icon+'" />');
	vtip();
}

/*--------------------------------------------------------------
spjPopupImage:  Opens a popup imnage dialog (enlargement)
	source:		The image source path
*/
function spjPopupImage(source, iWidth, iHeight, limitSize) {
	/* we might need to resize it */
	var r = 0;
	var aWidth = (window.innerWidth-75);
	var aHeight = (window.innerHeight-75);

    var autoWidth = iWidth;
    var autoHeight = iHeight;

	if(limitSize) {
		/* width first */
		if(iWidth > aWidth) {
			r = (aWidth / iWidth) * 100;
			iWidth = Math.round(r * iWidth) / 100;
			iHeight = Math.round(r * iHeight) / 100;
		}
		/* now recheck height */
		if(iHeight > aHeight) {
			r = (aHeight / iHeight) * 100;
			iWidth = Math.round(r * iWidth) / 100;
			iHeight = Math.round(r * iHeight) / 100;
		}
	}

    iWidth = (autoWidth == 'auto') ? autoWidth : iWidth;
    iHeight = (autoHeight == 'auto') ? autoHeight : iHeight;

	imgSource = '<div><a href="' + source + '" target="_blank"><img class="spPopupImg" src="' + source + '" width="' + iWidth + '" height="' + iHeight + '" /></a></div>';

	/* add some to container for title bar and border */
	if (iWidth != 'auto') {
		iWidth = (Math.abs(iWidth) + 10);
	}
	if (iHeight != 'auto') {
		iHeight = (Math.abs(iHeight) + 60.8);
	}

    var filename = source.replace(/^.*[\\\/]/, '');
	jQuery(imgSource).dialog({
		show: 'slide',
		hide: 'clip',
		position: 'center',
		draggable: true,
		resizable: false,
		closeText: '',
		modal: true,
		closeOnEscape: true,
		width: iWidth,
		height: iHeight,
		autoOpen: true,
        title: filename
	});
}

/* Opens up sections from editor toolbar */
function spjOpenEditorBox(id) {
	if(id == 'spUploadsBox') {
		if (jQuery('#spUploadsBox').css('display') == 'none') {
			jQuery('#spUploadToggle').hide();
			jQuery('#sp_file_uploader').show();
			jQuery('#sp_uploader_info').show();
		} else {
			jQuery('#sp_file_uploader').hide();
			jQuery('#sp_uploader_info').hide();
		}
	}
	jQuery('#'+id).slideToggle();
}

function spjDeletePost(url, pid, tid) {
    if (confirm(sp_forum_vars.deletepost)) {
        jQuery('#dialog').dialog('close');
        var count = jQuery('#postlist' + tid + ' > div.spTopicPostSection:not([style*="display: none"])').length;
        jQuery.ajax({
            type: 'GET',
            url: url + '&count=' + count,
            cache: false,
            success: function(html) {
                jQuery('#post' + pid).slideUp(function() {
                	spjDisplayNotification(0, sp_forum_vars.postdeleted);
                    if (html != '') window.location = html;
                });
            }
        });
    }
}

function spjDeleteTopic(url, tid, fid) {
    if (confirm(sp_forum_vars.deletetopic)) {
        jQuery('#dialog').dialog('close');
        var count = jQuery('#topiclist' + fid + ' > div.spForumTopicSection:not([style*="display: none"])').length;
        jQuery.ajax({
            type: 'GET',
            url: url + '&count=' + count,
            cache: false,
            success: function(html) {
                jQuery('#topic' + tid).slideUp(function() {
                	spjDisplayNotification(0, sp_forum_vars.topicdeleted);
                    if (html != '') window.location = html;
                });
            }
        });
    }
}

function spjMarkRead(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
            jQuery('#spUnreadCount').html('0');
        	spjDisplayNotification(0, sp_forum_vars.markread);
        }
    });
}

function spjPinPost(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.pinpost);
        }
    });
}

function spjPinTopic(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.pintopic);
        }
    });
}

function spjLockTopic(url) {
    jQuery.ajax({
        type: 'GET',
        url: url,
        cache: false,
        success: function(html) {
        	spjDisplayNotification(0, sp_forum_vars.locktopic);
        }
    });
}