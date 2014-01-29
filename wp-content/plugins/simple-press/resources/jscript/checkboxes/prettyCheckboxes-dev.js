/*
$LastChangedDate: 2013-05-04 07:30:54 -0700 (Sat, 04 May 2013) $
$Rev: 10265 $
*/
/* ------------------------------------------------------------------------
	prettyCheckboxes

	Developped By: Stephane Caron (http://www.no-margin-for-errors.com)
	Inspired By: All the non user friendly custom checkboxes solutions ;)
	Version: 1.1

	Copyright: Feel free to redistribute the script/modify it, as
			   long as you leave my infos at the top.
------------------------------------------------------------------------- */

	jQuery.fn.prettyCheckboxes = function(settings) {
		settings = jQuery.extend({
					checkboxWidth: 20,
					checkboxHeight: 17,
					className : 'prettyCheckbox',
					display: 'list'
				}, settings);

		jQuery(this).each(function(){
			// Find the label
			// SP Added... make sure the cjheckbox has not already been changed
			if(jQuery(this).hasClass('hiddenCheckbox') == false) {
				jQuerylabel = jQuery('label[for="'+jQuery(this).attr('id')+'"]');

				// Add the checkbox holder to the label
				jQuerylabel.prepend("<span class='holderWrap'><span class='holder'></span></span>");

				// If the checkbox is checked, display it as checked
				if(jQuery(this).is(':checked')) { jQuerylabel.addClass('checked'); };

				// Assign the class on the label
				jQuerylabel.addClass(settings.className).addClass(jQuery(this).attr('type')).addClass(settings.display);

				// Assign the dimensions to the checkbox display
				jQuerylabel.find('span.holderWrap').width(settings.checkboxWidth).height(settings.checkboxHeight);
				jQuerylabel.find('span.holder').width(settings.checkboxWidth);

				// Hide the checkbox
				jQuery(this).addClass('hiddenCheckbox');

				// Associate the click event
				jQuerylabel.bind('click',function(){
					jQuery('input#' + jQuery(this).attr('for')).triggerHandler('click');

					if(jQuery('input#' + jQuery(this).attr('for')).is(':checkbox')){
						jQuery(this).toggleClass('checked');
						jQuery('input#' + jQuery(this).attr('for')).checked = true;

						jQuery(this).find('span.holder').css('top',0);
					}else{
						jQuerytoCheck = jQuery('input#' + jQuery(this).attr('for'));

						// Uncheck all radio
						jQuery('input[name="'+jQuerytoCheck.attr('name')+'"]').each(function(){
							jQuery('label[for="' + jQuery(this).attr('id')+'"]').removeClass('checked');
						});

						jQuery(this).addClass('checked');
						jQuerytoCheck.checked = true;
					};
				});
			}
			jQuery('input#' + jQuerylabel.attr('for')).bind('keypress',function(e){
				if(e.keyCode == 32){
					jQuery(this).trigger('click');
					return false;
				};
			});
		});
	};

	checkAllPrettyCheckboxes = function(caller, container){
		if(jQuery(caller).is(':checked')){
			// Find the label corresponding to each checkbox and click it
			jQuery(container).find('input[type=checkbox]:not(:checked)').each(function(){
				jQuery('label[for="'+jQuery(this).attr('id')+'"]').trigger('click');
				jQuery(this).trigger('click');
			});
		}else{
			jQuery(container).find('input[type=checkbox]:checked').each(function(){
				jQuery('label[for="'+jQuery(this).attr('id')+'"]').trigger('click');
				jQuery(this).trigger('click');
			});
		};
	};