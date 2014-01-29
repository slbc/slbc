/*
$LastChangedDate: 2013-09-19 13:12:36 -0700 (Thu, 19 Sep 2013) $
$Rev: 10709 $
*/
/*--------------------------------------------------------------
spjLoadAhah: Generic ahah call hander
	url:	the url of the ahah php file
	target:	the target element id for displaying the results
	image:	the src url of an optional image file like a spinner
*/

function spjLoadAhah(url, target, image) {
	if(image !== '') {
		document.getElementById(target).innerHTML = '<img src="' + image + '" />';
	}
    url = url + '&rnd=' +  new Date().getTime();
    jQuery('#'+target).show();
	jQuery('#'+target).load(url);
}

/*--------------------------------------------------------------
spjBatch: Generic batch processor
	thisFormID:		id of the form making the call
	url:			url of the php ajax code file
	target:			target dic for final message
	message:		message to show on completion
	startNum:		starting number - usually 0
	batchNum:		how many to process in each batch
	totalNum:		how many in total to be processed
*/

function spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum) {
	if(startNum == 0) {
		url += '&target='+target+'&totalNum='+totalNum+'&'+jQuery('#'+thisFormID).serialize();
		jQuery('#'+target).show();
		jQuery('#'+target).html(startMessage);
		jQuery("#progressbar").progressbar({ value: 0 });
	} else {
		var currentProgress  = ((startNum / totalNum) * 100);
		jQuery("#progressbar").progressbar('option', 'value', currentProgress);
	}

	var thisUrl = url + '&startNum='+startNum+'&batchNum='+batchNum;

	jQuery('#onFinish').load(thisUrl, function(a, b) {
		startNum = (startNum + batchNum);
		if(startNum < totalNum) {
			spjBatch(thisFormID, url, target, startMessage, endMessage, startNum, batchNum, totalNum);
		} else {
			jQuery("#progressbar").hide();
			jQuery('#'+target).show();
			jQuery('#'+target).html(endMessage);
			jQuery('#'+target).fadeOut(6000);
		}
	});

	return false;
}

/*--------------------------------------------------------------
spjDialogAjax: Opens a jQuery UI Dialog popup filled by Ajax
	e:			The button/link object making the call
	url:		The url to the ahah file to populate dialog
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of popup or 0 for auto
	position:	Set to zero to calculate. Or 'center', 'top' etc.
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
*/

function spjDialogAjax(e, url, title, width, height, position, dClass) {
	if(!dClass) dClass = 'spDialogDefault';
	if((sp_platform_vars.device == 'desktop' && sp_platform_vars.focus == 'forum') || (sp_platform_vars.focus == 'admin') || (sp_platform_vars.mobiletheme == false)) {
		// close and remove any existing dialog. remove hdden div and recreate it */
		if(jQuery().dialog("isOpen")) {
			jQuery().dialog('close');
		}
		jQuery('#dialog').remove();
		jQuery("#dialogcontainer").append("<div id='dialog'></div>");
		jQuery('#dialog').load(url, function(ajaxContent) {
			spjDialogPopUp(e, title, width, height, position, dClass, ajaxContent);
		});
	} else {
		var panel = jQuery('#spMobilePanel');
		// grab new position and set up the top
		var t = (window.scrollY + 80);
		var pageTop = jQuery('#spForumTop').offset();
		t = (Math.round(t - pageTop.top));
		if(panel.css('display') == 'block') {
			panel.hide('slide', {direction: 'right'}, 'slow', function() {
				panel.css('display', 'none');
				panel.css('right', '-1px');
				panel.css('top', t+'px');
			});
		}
		spjDialogPanel(e, url, dClass, t);
	}
}

/*--------------------------------------------------------------
spjDialogHtml:	Opens a jQuery UI Dialog popup filled by content
	e:			The button/link object making the call
	content:	the formatted content to be displayed
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of popup or 0 for auto
	position:	Set to zero to calculate. Or 'center', 'top' etc.
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
*/

function spjDialogHtml(e, content, title, width, height, position, dClass) {
	if(!dClass) dClass = 'spDialogDefault';
	// close and remove any existing dialog. remove hdden div and recreate it */
	if(jQuery().dialog("isOpen")) {
		jQuery().dialog('close');
	}
	jQuery('#dialog').remove();
	jQuery("#dialogcontainer").append("<div id='dialog'></div>");
	spjDialogPopUp(e, title, width, height, position, dClass, content);
}

/*--------------------------------------------------------------
spjDialogPopUp: Opens a jQuery UI Dialog popup
	e:			The button/link object making the call
	title:		text for the popup title bar
	width:		Width of the popup or 0 fot auto
	height:		Height of the popup or 0 for auto
	position:	Set to zero to calculate. Or 'center', 'top' etc.
	dClass:		Optional class to apply to overall dialog container (ui-dialog)
	content:	The cntent to be dsplayed
*/

function spjDialogPopUp(e, title, width, height, position, dClass, content) {
	// force content into dialog div
	jQuery('#dialog').html(content);
	if(position === 0) {
		var topPos = 0;
		var p = jQuery("#" + e.id);
		var offset = p.offset();
		var leftPos = (Math.floor(offset.left) - width);
		if (navigator.appName == "Microsoft Internet Explorer") {
			topPos = (Math.floor(offset.top) - document.body.scrollTop);
		} else {
			topPos = (Math.floor(offset.top) - window.pageYOffset);
		}
		if(leftPos < 0) leftPos = 0;
		if(topPos < 20) topPos = 20;
	}
	jQuery('#dialog').dialog({
		zindex: 100000,
		autoOpen: false,
		show: 'fold',
		hide: 'fold',
		width: 'auto',
		height: 'auto',
		maxHeight: 800,
		position: position,
		draggable: true,
		resizable: true,
		title: title,
		closeText: '',
		dialogClass: dClass,
        close: function( event, ui ) {jQuery('#postitem').trigger('closed');}
	});

	if(width > 0) {
		jQuery('#dialog').dialog("option", "width", width);
	}
	if(height > 0) {
		jQuery('#dialog').dialog("option", "height", height);
	}
	if(position === 0) {
		jQuery('#dialog').dialog("option", "position", [leftPos, topPos]);
	}

	jQuery('#dialog').dialog( "option", "zIndex", 100000);

	jQuery('#dialog').dialog('open');
	jQuery(function(jQuery){vtip();});
}

/*--------------------------------------------------------------
spjDialogPanel: Opens a sliding panel filled by Ajax
	e:			The button/link object making the call
	url:		The url to the ahah file to populate dialog
*/

function spjDialogPanel(e, url, dClass, t) {
	var panel = jQuery('#spMobilePanel');
	panel.load(url, function() {
		panel.removeClass();
		panel.addClass(dClass);
		panel.css('top', t+'px');
		panel.show('slide', {direction: 'left'}, 'slow');
		panel.append("<span id='spPanelClose' onclick='jQuery(\"#spMobilePanel\").hide(\"slide\", {direction: \"right\"}, \"slow\"); '></span>");
	});
	// bind the 'mousedown' event to the document so we can close panel
	jQuery('body').bind('mousedown', function() {
		panel.hide('slide', {direction: 'right'}, 'slow');
	});
	// don't close panel when clicking inside it
	panel.bind('mousedown', function(e) {
		e.stopPropagation();
	});
}

/*--------------------------------------------------------------
vtip:  Tooltip enhancer
*/

this.vtip = function() {
	if(sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
		this.xOffset = -10; /*	x distance from mouse */
		this.yOffset =  18; /* y distance from mouse */

		jQuery(".vtip").unbind().hover(
			function(e) {
				if(this.title !== '') {
                    this.t = htmlspecialchars(this.title);
					this.title = '';
					this.top = (e.pageY + yOffset); this.left = (e.pageX + xOffset);
					jQuery('body').append( '<p id="vtip">' + this.t + '</p>' );
					jQuery('p#vtip').css("top", this.top+"px").css("left", this.left+"px").fadeIn("fast");
				}
			},
			function() {
				if(this.t != undefined) {
					this.title = rhtmlspecialchars(this.t);
					jQuery("p#vtip").fadeOut("slow").remove();
				}
			}
		).mousemove(
			function(e) {
				this.top = (e.pageY + yOffset);
				this.left = (e.pageX + xOffset);

				jQuery("p#vtip").css("top", this.top+"px").css("left", this.left+"px");
			}
		);
	}
};

/*--------------------------------------------------------------
htmlspecialchars:  equivalent to php htmlspecialchars() for sanitization
*/
function htmlspecialchars(str) {
    if (typeof(str) == "string") {
        str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
        str = str.replace(/"/g, "&quot;");
        str = str.replace(/'/g, "&#039;");
        str = str.replace(/</g, "&lt;");
        str = str.replace(/>/g, "&gt;");
    }
    return str;
}

/*--------------------------------------------------------------
rhtmlspecialchars:  equivalent to php htmlspecialchars_decocde()
*/
function rhtmlspecialchars(str) {
    if (typeof(str) == "string") {
        str = str.replace(/&gt;/ig, ">");
        str = str.replace(/&lt;/ig, "<");
        str = str.replace(/&#039;/g, "'");
        str = str.replace(/&quot;/ig, '"');
        str = str.replace(/&amp;/ig, '&'); /* must do &amp; last */
    }
    return str;
 }

/*--------------------------------------------------------------
jcookie:  Set/Get a cookie
*/
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie !== '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};