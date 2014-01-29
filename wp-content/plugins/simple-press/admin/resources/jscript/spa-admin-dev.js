/* ---------------------------------
Simple:Press
Admin Javascript
$LastChangedDate: 2010-08-08 14:11:22 -0700 (Sun, 08 Aug 2010) $
$Rev: 4365 $
------------------------------------ */

var sfupload;

/* ----------------------------------*/
/* Admin Form Loader                 */
/* ----------------------------------*/
function spjLoadForm(formID, baseURL, targetDiv, imagePath, id, open, upgradeUrl, admin, save, sform, reload) {
	/* close a dialog (popup help) if one is open */
	if(jQuery().dialog("isOpen")) {
		jQuery().dialog('destroy');
	}

	/* remove any current form unless instructed to leave open */
	if (open === null || open == undefined) {
		for(x=document.forms.length-1;x>=0;x--) {
			if (document.forms[x].id !== '') {
				var tForm = document.getElementById(document.forms[x].id);
				if(tForm !== null) {
					tForm.innerHTML='';
				}
			}
		}
	}

	/* create vars we need */
	var busyDiv = document.getElementById(targetDiv);
	var currentFormBtn = document.getElementById('c'+formID);
	var ahahURL = baseURL + '&loadform=' + formID;

	/* some sort of ID data? */
	if (id) {
		ahahURL = ahahURL + '&id=' + id;
	}

	/* user plugin? */
	if (admin) {
		ahahURL = ahahURL + '&admin=' + admin;
	}
	if (save) {
		ahahURL = ahahURL + '&save=' + save;
	}
	if (sform) {
		ahahURL = ahahURL + '&form=' + sform;
	}
	if (reload) {
		ahahURL = ahahURL + '&reload=' + reload;
	}

	/* add random num to GET param to ensure its not cached */
	ahahURL = ahahURL + '&rnd=' +  new Date().getTime();

	var spfjform = jQuery.noConflict();
	spfjform(document).ready(function() {
		/* fade out the msg area */
		spfjform('#sfmsgspot').fadeOut();

		/* load the busy graphic */
		busyDiv.innerHTML = '<img src="' + imagePath + 'sp_WaitBox.gif' + '" />';

		/*  now load the form - and pretty checkbox and sort if toolbar and uploader if smileys */
		spfjform('#'+targetDiv).load(ahahURL, function(a, b) {
			if(a == 'Upgrade') {
				spfjform('#'+targetDiv).hide();
				window.location = upgradeUrl;
				return;
			}

			if (reload != 'sfreloadpl' && sp_platform_vars.checkboxes) {
				spfjform("input[type=checkbox],input[type=radio]").prettyCheckboxes();
			}
		});
	});
}

/* ----------------------------------*/
/* Setup Ajax Form processing               */
/* ----------------------------------*/
function spjAjaxForm(aForm, reLoad) {
	jQuery(document).ready(function() {
		jQuery('#'+aForm).ajaxForm({
			target: '#sfmsgspot',
			beforeSubmit: function() {
				jQuery('#sfmsgspot').show();
				jQuery('#sfmsgspot').html(pWait);
			},
			success: function() {
				if(reLoad != '') {
					jQuery('#sfmsgspot').hide();
					jQuery('#'+reLoad).click();
				}
				jQuery('#sfmsgspot').fadeIn();
				jQuery('#sfmsgspot').fadeOut(6000);
			}
		});
	});
}

/* ----------------------------------*/
/* Open and Close of hidden divs     */
/* ----------------------------------*/
function spjToggleLayer(whichLayer)
{
	if (document.getElementById) {
		/* this is the way the standards work */
		style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	} else if (document.all) {
		/* this is the way old msie versions work */
		style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	} else if (document.layers) {
		/* this is the way nn4 works */
		style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	var obj = document.getElementById(whichLayer);
	if (whichLayer == 'spPostForm') {
		obj.scrollIntoView(false);
	}
}

/* ----------------------------------*/
/* Admin Option Tools                */
/* ----------------------------------*/
function spjAdminTool(url, target, imageFile) {
	if(imageFile !== '') {
		document.getElementById(target).innerHTML = '<br /><br /><img src="' + imageFile + '" /><br />';
	}
    url = url + '&rnd=' +  new Date().getTime();
	jQuery('#'+target).load(url);
}

/* ----------------------------------*/
/* Admin Show Group Members          */
/* ----------------------------------*/
function spjShowMemberList(url, imageFile, groupID) {
	var memberList = document.getElementById('members-'+groupID);
	var target = 'members-'+groupID;

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	if(memberList.innerHTML === '') {
		if (imageFile !== '') {
			document.getElementById(target).innerHTML = '<img src="' + imageFile + '" />';
		} else {
			document.getElementById(target).innerHTML = '';
		}
		jQuery('#members-'+groupID).load(url);
	} else {
		document.getElementById(target).innerHTML = '';
	}
}

/* ----------------------------------*/
/* Admin Show Multi Select List      */
/* ----------------------------------*/
function spjUpdateMultiSelectList(url, uid) {
	var target = '#mslist-'+uid;

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	jQuery(target).load(url);
}

function spjFilterMultiSelectList(url, uid, imageFile) {
	var target = '#mslist-'+uid;

	document.getElementById('filter-working').innerHTML = '<img src="' + imageFile + '" />';

	filter = document.getElementById('list-filter'+uid);
	url = url + '&filter=' + encodeURIComponent(filter.value);

	/* add random num to GET param to ensure its not cached */
	url = url + '&rnd=' +  new Date().getTime();

	jQuery(target).load(url);
}

function spjTransferSelectList(from, to, msg, exceed, recip) {
	/* can we add more? */
	var newlist = jQuery('#'+from+' option:selected').size();
	var oldlist = jQuery('#'+to+' option').size();
	if((newlist + oldlist) > 400) {
		alert(exceed);
		return false;
	}

	/* remove list empty message */
	jQuery('#'+to+' option[value="-1"]').remove();
	/* move the data from the from box to the to box */
	jQuery('#'+from+' option:selected').remove().appendTo('#'+to);

	jQuery('#selcount').html(jQuery('#'+recip+' option').size());

	/* if the from box is now empty, display message */
	if (!jQuery('#'+from+' option').length)
		jQuery('#'+from).append('<option value="-1">'+msg+'</option>');

	return false;
}

/* delete a row and reload the form */
function spjDelRowReload(url, reload) {
	jQuery('#sfmsgspot').load(url, function() {
		jQuery('#'+reload).click();
	});
}

/* delete a row */
function spjDelRow(url, rowid) {
	jQuery('#'+rowid).css({backgroundColor: '#ffcccc'});
	jQuery('#'+rowid).fadeOut('slow');
	jQuery('#'+rowid).load(url);
}

/* ----------------------------------*/
/* Check/Uncheck box collection      */
/* ----------------------------------*/
function spjCheckAll(container) {
	jQuery(container).find('input[type=checkbox]:not(:checked)').each(function() {
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
	});
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjUnCheckAll(container) {
	jQuery(container).find('input[type=checkbox]:checked').each(function() {
		jQuery('label[for='+jQuery(this).attr('id')+']').trigger('click');
	});
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumOptions(type) {
	if(type == 'forum') {
		jQuery('#forumselect').hide();
		jQuery('#groupselect').show();
	} else {
		jQuery('#groupselect').hide();
		jQuery('#forumselect').show();
	}
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumSequence(action, type, id, url, target) {
	url+='&type='+type+'&id='+id.value+'&action='+action;

	jQuery('#'+target).load(url, function() {
		if (sp_platform_vars.checkboxes) jQuery("input.radiosequence").prettyCheckboxes();
	});

	jQuery('#block1').show('slow');
	jQuery('#block2').show('slow');
}

/* ----------------------------------*/
/* 							         */
/* ----------------------------------*/
function spjSetForumSlug(title, url, target, slugAction) {
	url+='&action=slug&title='+escape(title.value)+'&slugaction='+slugAction;
	jQuery('#'+target).load(url, function(newslug) {
		document.getElementById(target).value = newslug;
		document.getElementById(target).disabled = false;
	});
}

/* ----------------------------------*/
/* Load the help and troubleshooting */
/* ----------------------------------*/
function spjTroubleshooting(site, targetDiv) {
	jQuery('#'+targetDiv).load(site);
}

/* ----------------------------------*/
/* 	Add/Delete members control       */
/* ----------------------------------*/
function spjAddDelMembers(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, source) {
	var totalNum = 0;
	jQuery(source +' option').each(function(i) {
		jQuery(this).attr('selected', 'selected');
		totalNum++;
	});
	spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
	jQuery(source + ' option').remove();
}
