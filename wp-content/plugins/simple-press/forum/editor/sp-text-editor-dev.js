/* ---------------------------------
Simple:Press
Plain Text Editor Javascript
------------------------------------ */

/* ---------------------------------------------
   Open the dropdown editor area
--------------------------------------------- */
function spjEdOpenEditor(formType) {
	if (formType == 'topic') {
		document.addtopic.spTopicTitle.focus();
    } else if (formType == 'post') {
	    document.addpost.postitem.focus();
	}
}

/* ---------------------------------------------
   Cancels editor - removes any content
--------------------------------------------- */
function spjEdCancelEditor() {
	var tx = document.getElementById('postitem');
	tx.value = '';
	jQuery('#spPostNotifications').html('');
	jQuery('#spPostNotifications').hide();
	if (document.getElementById('previewPost') != 'undefined') {
		jQuery('#previewPost').html('');
	}
	spjToggleLayer('spPostForm');
}

/* ---------------------------------------------
   Insert content as in Quoting
--------------------------------------------- */
function spjEdInsertContent(intro, content) {
	document.addpost.postitem.value = '<blockquote><strong>'+intro+'</strong>\r\r'+content+'</blockquote><br />\r\r';
	document.addpost.postitem.focus();
}

/* ---------------------------------------------
   Insert a Smiley
--------------------------------------------- */
function spjEdInsertSmiley(file, title, path, code) {
	var postField = document.getElementById('postitem');

	/* IE support */
	if (document.selection) {
		postField.focus();
		sel = document.selection.createRange();
		sel.text = code;
		postField.focus();
	} 	else if (postField.selectionStart || postField.selectionStart == '0') {
		/* MOZILLA/NETSCAPE support */
		var startPos = postField.selectionStart;
		var endPos = postField.selectionEnd;
		postField.value = postField.value.substring(0, startPos)
				  + code
				  + postField.value.substring(endPos, postField.value.length);
		postField.focus();
		postField.selectionStart = startPos + code.length;
		postField.selectionEnd = startPos + code.length;
	} else {
		postField.value += code;
		postField.focus();
	}
}

/* ---------------------------------------------
   Insert an Attachment
--------------------------------------------- */
function spjEdInsertAttachment(file, title, path, item, width, height) {
	jQuery('#' + item).val(jQuery('#' + item).val()+'<img src="'+path+file+'" title="'+title+'" alt="'+title+'"  width="'+width+'" height="'+height+'" />');
}

function spjEdInsertMediaAttachment(file, path, width, height) {
    ext = file.split('.').pop();
    if (ext == 'swf' || ext == 'flv' || ext == 'fla') {
    	mt = 'application/x-shockwave-flash';
    } else if (ext == 'wma' || ext == 'wmv') {
    	mt = 'application/x-mplayer2';
    } else if (ext == 'rm' || ext == 'rma' || ext == 'ra' || ext == 'rpm') {
    	mt = 'audio/x-pn-realaudio-plugin';
    } else {
    	mt = 'video/quicktime';
	}

	jQuery('#postitem').val(jQuery('#postitem').val() + '<p><object width="' + width + '" height="' + height + '" type="' + mt + '" data="' + path + file + '"><param value="' + path + file + '" name="src"><param value="false" name="autoplay"></object></p>');
}

/* ---------------------------------------------
   Insert text
--------------------------------------------- */
function spjEdInsertText(text) {
	jQuery('#postitem').val(jQuery('#postitem').val() + text);
}

/* ---------------------------------------------
   Get the current content of the editor
--------------------------------------------- */
function spjEdGetEditorContent(theForm) {
	return theForm.postitem.value;
}

/* ---------------------------------------------
   Validate editor content for known failures
--------------------------------------------- */
function spjEdValidateContent(theField, errorMsg) {
	var error = '';
	if (theField.value.length === 0) {
		error = '<strong>' + errorMsg + '</strong><br />';
	}
	return error;
}

/* ---------------------------------------------
   Get the current content of the signature
--------------------------------------------- */
function spjEdGetSignature(a) {
}
