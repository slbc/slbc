/*! http://tinynav.viljamis.com v1.05 by @viljamis */
/* modified - Andy Staines */

(function ($, window, i) {
	$.fn.mobileMenu = function (options) {
		// Default settings
		var settings = $.extend({
			'active' : 'selected', // String: Set the "active" class
			'sclass'  : 'spMobileMenu',
			'header' : '' // String: Specify text for "header" and show header instead of the active item
		}, options);

		return this.each(function () {
			// Used for namespacing
			i++;
			var $nav = $(this);
			// Namespacing
			namespace = 'mobileMenu';
			namespace_i = namespace + i;
			l_namespace_i = '.l_' + namespace_i;
			$select = $('<select/>').addClass(namespace + ' ' + namespace_i + ' ' + settings.sclass);

			if ($nav.is('ul,ol')) {
				if (settings.header !== '') {
					$select.append(
						$('<option/>').text(settings.header)
					);
				}
				// Build options
				var options = '';
				$nav.addClass('l_' + namespace_i).find('a').each(function () {
					options += '<option value="' + $(this).attr('href') + '">';
					var j;
					for (j = 0; j < $(this).parents('ul, ol').length - 1; j++) {
						options += '- ';
					}
					options += $(this).text() + '</option>';
				});
				// Append options into a select
				$select.append(options);
				// Select the active item
				if (!settings.header) {
					$select.find(':eq(' + $(l_namespace_i + ' li').index($(l_namespace_i + ' li.' + settings.active)) + ')').attr('selected', true);
				}
				// Change window location
				$select.change(function () {
					window.location.href = $(this).val();
				});
				// Inject select
				$(l_namespace_i).after($select);
			}
		});
	};
})(jQuery, this, 0);

function spjOpenQL(target, tagId, openIcon, closeIcon) {
    var icon = '';
	var c=jQuery('#'+target).css('display');
	if (c == 'block') {
		jQuery('#'+target).slideUp();
		icon = openIcon;
	} else {
		jQuery('#'+target).slideDown();
		icon = closeIcon;
	}
	jQuery('#'+tagId).html('<img src="'+icon+'" />');
}

function spjResetMobileMenu() {
	jQuery('.mobileMenu :nth-child(1)').prop('selected', true);
}
